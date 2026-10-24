<?php
/**
 * WP-CLI seeder: turns the shipped design content into real WordPress posts,
 * custom fields, a front page and media.
 *
 * Usage: wp 4way seed [--force] [--import-images] [--frontend-url=URL]
 *
 * @package fourway
 */

defined( 'ABSPATH' ) || exit;

/**
 * Import a remote image into the media library when requested.
 *
 * @param string $url     Remote URL.
 * @param int    $post_id Parent post.
 * @param string $title   Attachment title.
 * @return int Attachment ID, or 0 on failure.
 */
function fourway_seed_sideload_image( $url, $post_id, $title = '' ) {
	if ( ! $url || ! wp_http_validate_url( $url ) ) {
		return 0;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$attachment_id = media_sideload_image( $url, $post_id, $title, 'id' );

	if ( is_wp_error( $attachment_id ) ) {
		return 0;
	}

	return (int) $attachment_id;
}

/**
 * Find a seeded post by its stable seed key.
 *
 * @param string $post_type Post type.
 * @param string $seed_key  Stable key.
 * @return int
 */
function fourway_seed_find( $post_type, $seed_key ) {
	$found = get_posts(
		array(
			'post_type'        => $post_type,
			'post_status'      => 'any',
			'posts_per_page'   => 1,
			'fields'           => 'ids',
			'meta_key'         => '_fourway_seed_key', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'       => $seed_key, // phpcs:ignore WordPress.DB.SlowDBQuery
			'suppress_filters' => false,
		)
	);

	return $found ? (int) $found[0] : 0;
}

/**
 * Register the post thumbnail fallback used by blog cards.
 */
function fourway_register_post_image_meta() {
	register_post_meta(
		'post',
		'_fourway_image_url',
		array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'esc_url_raw',
			'auth_callback'     => static function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);
}
add_action( 'init', 'fourway_register_post_image_meta', 20 );

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	/**
	 * Seed and inspect the 4Way Safety content model.
	 */
	class FourWay_SEED_Command {

		/**
		 * Create the front page, settings and all homepage collections.
		 *
		 * ## OPTIONS
		 *
		 * [--force]
		 * : Update existing seeded entries instead of skipping them.
		 *
		 * [--import-images]
		 * : Download images into the media library.
		 *
		 * [--frontend-url=<url>]
		 * : URL of the React application.
		 *
		 * ## EXAMPLES
		 *
		 *     wp 4way seed --import-images --frontend-url=http://localhost:5173
		 *
		 * @param array $args       Positional args.
		 * @param array $assoc_args Flags.
		 */
		public function seed( $args, $assoc_args ) {
			$defaults = fourway_defaults();

			if ( empty( $defaults ) ) {
				WP_CLI::error( 'Missing content/defaults.json — run `npm run wp:defaults` first.' );
			}

			$force         = ! empty( $assoc_args['force'] );
			$import_images = ! empty( $assoc_args['import-images'] );

			$this->remove_default_content();
			$this->seed_front_page();
			$this->seed_settings( $defaults, $force );

			$collections = array(
				'trust_badge'  => isset( $defaults['badges'] ) ? $defaults['badges'] : array(),
				'service'      => isset( $defaults['services'] ) ? $defaults['services'] : array(),
				'reason'       => isset( $defaults['reasons'] ) ? $defaults['reasons'] : array(),
				'process_step' => isset( $defaults['steps'] ) ? $defaults['steps'] : array(),
				'project'      => isset( $defaults['projects'] ) ? $defaults['projects'] : array(),
				'testimonial'  => isset( $defaults['testimonials'] ) ? $defaults['testimonials'] : array(),
				'faq'          => isset( $defaults['faqs'] ) ? $defaults['faqs'] : array(),
			);

			foreach ( $collections as $post_type => $items ) {
				$count = $this->seed_collection( $post_type, $items, $force, $import_images );
				WP_CLI::log( sprintf( '  %-14s %d item(s)', $post_type, $count ) );
			}

			$post_count = $this->seed_posts(
				isset( $defaults['posts'] ) ? $defaults['posts'] : array(),
				$force,
				$import_images
			);
			WP_CLI::log( sprintf( '  %-14s %d item(s)', 'post', $post_count ) );

			if ( ! empty( $assoc_args['frontend-url'] ) ) {
				update_option( 'fourway_frontend_url', esc_url_raw( $assoc_args['frontend-url'] ) );
			}

			if ( function_exists( 'wp_clean_update_cache' ) ) {
				flush_rewrite_rules();
			}

			WP_CLI::success( '4Way Safety content seeded. Homepage is live at ' . fourway_frontend_url() );
		}

		/**
		 * Report what is currently stored.
		 *
		 * @param array $args       Positional args.
		 * @param array $assoc_args Flags.
		 */
		public function status( $args, $assoc_args ) {
			global $wpdb;

			$types = array( 'trust_badge', 'service', 'reason', 'process_step', 'project', 'testimonial', 'faq', 'post', 'page' );

			WP_CLI::log( 'Content:' );
			foreach ( $types as $type ) {
				$count = (int) wp_count_posts( $type )->publish;
				WP_CLI::log( sprintf( '  %-14s %d published', $type, $count ) );
			}

			WP_CLI::log( '' );
			WP_CLI::log( 'Settings:' );
			WP_CLI::log( '  front page      ' . (int) get_option( 'page_on_front' ) . ' (' . get_option( 'show_on_front' ) . ')' );
			WP_CLI::log( '  React app       ' . fourway_frontend_url() );
			WP_CLI::log( '  redirect        ' . ( fourway_headless_redirect_enabled() ? 'enabled' : 'disabled' ) );

			$leads       = fourway_table( 'leads' );
			$subscribers = fourway_table( 'subscribers' );
			// phpcs:disable WordPress.DB
			$lead_count  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$leads}" );
			$sub_count   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$subscribers}" );
			// phpcs:enable WordPress.DB

			WP_CLI::log( '' );
			WP_CLI::log( 'CRM:' );
			WP_CLI::log( '  quote requests  ' . $lead_count );
			WP_CLI::log( '  subscribers     ' . $sub_count );

			WP_CLI::success( 'REST endpoint: ' . rest_url( '4way/v1/home' ) );
		}

		/**
		 * Remove WordPress' demo content so it never reaches the homepage.
		 */
		private function remove_default_content() {
			$hello_world = get_page_by_path( 'hello-world', OBJECT, 'post' );
			if ( $hello_world && 'Hello world!' === $hello_world->post_title ) {
				wp_delete_post( $hello_world->ID, true );
				WP_CLI::log( '  removed         default "Hello world!" post' );
			}

			$sample_page = get_page_by_path( 'sample-page', OBJECT, 'page' );
			if ( $sample_page && 'Sample Page' === $sample_page->post_title ) {
				wp_delete_post( $sample_page->ID, true );
				WP_CLI::log( '  removed         default "Sample Page"' );
			}
		}

		/**
		 * Create the Home page and make it the static front page.
		 */
		private function seed_front_page() {
			update_option( 'permalink_structure', '/%postname%/' );

			$existing = (int) get_option( 'page_on_front' );
			if ( $existing && get_post( $existing ) ) {
				WP_CLI::log( '  front page      already configured (#' . $existing . ')' );
				return;
			}

			$page    = get_page_by_path( 'home' );
			$page_id = $page ? (int) $page->ID : 0;

			if ( ! $page_id ) {
				$page_id = wp_insert_post(
					array(
						'post_title'   => 'Home',
						'post_name'    => 'home',
						'post_type'    => 'page',
						'post_status'  => 'publish',
						'post_content' => '<!-- Rendered by the React application at the URL configured under 4Way Safety → Homepage Content. -->',
					)
				);
			}

			if ( is_wp_error( $page_id ) || ! $page_id ) {
				WP_CLI::warning( 'Could not create the Home page.' );
				return;
			}

			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', (int) $page_id );
			WP_CLI::log( '  front page      created (#' . (int) $page_id . ')' );
		}

		/**
		 * Store the homepage settings option (unless the client customised it).
		 *
		 * @param array $defaults Defaults payload.
		 * @param bool  $force    Overwrite existing values.
		 */
		private function seed_settings( array $defaults, $force ) {
			if ( empty( $defaults['settings'] ) ) {
				return;
			}

			$existing = get_option( 'fourway_settings' );

			if ( $existing && ! $force ) {
				WP_CLI::log( '  settings        already customised (use --force to reset)' );
			} else {
				update_option( 'fourway_settings', $defaults['settings'] );
				WP_CLI::log( '  settings        imported' );
			}

			if ( ! get_option( 'fourway_frontend_url' ) ) {
				$env = getenv( 'FOURWAY_FRONTEND_URL' );
				update_option( 'fourway_frontend_url', esc_url_raw( $env ? $env : 'http://localhost:5173' ) );
			}
		}

		/**
		 * Seed one collection.
		 *
		 * @param string $post_type     Post type.
		 * @param array  $items         Items from defaults.json.
		 * @param bool   $force         Update existing rows.
		 * @param bool   $import_images Sideload images.
		 * @return int Number of items handled.
		 */
		private function seed_collection( $post_type, array $items, $force, $import_images ) {
			$count = 0;

			foreach ( $items as $index => $item ) {
				$title = isset( $item['title'] ) ? $item['title'] : ( isset( $item['name'] ) ? $item['name'] : ( isset( $item['question'] ) ? $item['question'] : '' ) );

				if ( '' === $title ) {
					continue;
				}

				$seed_key = isset( $item['slug'] ) ? $item['slug'] : sanitize_title( $title );
				$post_id  = fourway_seed_find( $post_type, $seed_key );

				if ( $post_id && ! $force ) {
					$count++;
					continue;
				}

				$postarr = array(
					'post_title'  => $title,
					'post_name'   => $seed_key,
					'post_type'   => $post_type,
					'post_status' => 'publish',
					'menu_order'  => $index + 1,
				);

				if ( $post_id ) {
					$postarr['ID'] = $post_id;
				}

				$post_id = wp_insert_post( $postarr, true );

				if ( is_wp_error( $post_id ) ) {
					WP_CLI::warning( $title . ': ' . $post_id->get_error_message() );
					continue;
				}

				update_post_meta( $post_id, '_fourway_seed_key', $seed_key );

				$meta      = $this->collection_meta( $post_type, $item );
				$image_url = '';

				if ( isset( $item['image'] ) ) {
					$image_url = $item['image'];
				} elseif ( isset( $item['avatar'] ) ) {
					$image_url = $item['avatar'];
				}

				foreach ( $meta as $key => $value ) {
					if ( '' === $value || null === $value || false === $value ) {
						delete_post_meta( $post_id, '_fourway_' . $key );
						continue;
					}
					update_post_meta( $post_id, '_fourway_' . $key, $value );
				}

				if ( $import_images && $image_url && 'testimonial' !== $post_type ) {
					$attachment_id = fourway_seed_sideload_image( $image_url, $post_id, $title );
					if ( $attachment_id ) {
						set_post_thumbnail( $post_id, $attachment_id );
					}
				}

				$count++;
			}

			return $count;
		}

		/**
		 * Map a defaults.json item to WordPress meta fields.
		 *
		 * @param string $post_type Post type.
		 * @param array  $item      Item data.
		 * @return array
		 */
		private function collection_meta( $post_type, array $item ) {
			$get = static function ( $key ) use ( $item ) {
				return isset( $item[ $key ] ) ? $item[ $key ] : '';
			};

			switch ( $post_type ) {
				case 'trust_badge':
					return array(
						'icon'        => $get( 'icon' ),
						'description' => $get( 'description' ),
					);
				case 'service':
				case 'project':
					return array(
						'category'    => $get( 'category' ),
						'description' => $get( 'description' ),
						'href'        => $get( 'href' ),
						'image_url'   => $get( 'image' ),
						'image_alt'   => $get( 'imageAlt' ),
					);
				case 'reason':
					return array(
						'icon'        => $get( 'icon' ),
						'description' => $get( 'description' ),
						'column'      => $get( 'column' ),
					);
				case 'process_step':
					return array(
						'number'      => $get( 'number' ),
						'icon'        => $get( 'icon' ),
						'description' => $get( 'description' ),
					);
				case 'testimonial':
					return array(
						'role'       => $get( 'role' ),
						'quote'      => $get( 'quote' ),
						'rating'     => (int) $get( 'rating' ),
						'avatar_url' => $get( 'avatar' ),
					);
				case 'faq':
					return array(
						'answer' => $get( 'answer' ),
						'open'   => (bool) $get( 'open' ),
					);
				default:
					return array();
			}
		}

		/**
		 * Seed the three blog cards as real posts.
		 *
		 * @param array $items         Post items.
		 * @param bool  $force         Update existing rows.
		 * @param bool  $import_images Sideload featured images.
		 * @return int
		 */
		private function seed_posts( array $items, $force, $import_images ) {
			$count = 0;

			foreach ( $items as $item ) {
				if ( empty( $item['title'] ) ) {
					continue;
				}

				$seed_key = 'post:' . ( isset( $item['slug'] ) ? $item['slug'] : sanitize_title( $item['title'] ) );
				$post_id  = fourway_seed_find( 'post', $seed_key );

				if ( $post_id && ! $force ) {
					$count++;
					continue;
				}

				$category_name = isset( $item['category'] ) ? $item['category'] : 'Fire Safety';
				$term          = term_exists( $category_name, 'category' );

				if ( ! $term ) {
					$term = wp_insert_term( $category_name, 'category' );
				}

				$term_id = 0;
				if ( is_array( $term ) && isset( $term['term_id'] ) ) {
					$term_id = (int) $term['term_id'];
				} elseif ( is_int( $term ) ) {
					$term_id = $term;
				}

				$timestamp = ! empty( $item['date'] ) ? strtotime( $item['date'] ) : time();
				$postarr   = array(
					'post_title'    => $item['title'],
					'post_name'     => isset( $item['slug'] ) ? $item['slug'] : sanitize_title( $item['title'] ),
					'post_excerpt'  => isset( $item['excerpt'] ) ? $item['excerpt'] : '',
					'post_content'  => isset( $item['excerpt'] ) ? $item['excerpt'] : '',
					'post_status'   => 'publish',
					'post_type'     => 'post',
					'post_date'     => gmdate( 'Y-m-d H:i:s', $timestamp ? $timestamp : time() ),
					'post_category' => $term_id ? array( $term_id ) : array(),
				);

				if ( $post_id ) {
					$postarr['ID'] = $post_id;
				}

				$post_id = wp_insert_post( $postarr, true );

				if ( is_wp_error( $post_id ) ) {
					WP_CLI::warning( $item['title'] . ': ' . $post_id->get_error_message() );
					continue;
				}

				update_post_meta( $post_id, '_fourway_seed_key', $seed_key );

				if ( ! empty( $item['image'] ) ) {
					update_post_meta( $post_id, '_fourway_image_url', esc_url_raw( $item['image'] ) );

					if ( $import_images ) {
						$attachment_id = fourway_seed_sideload_image( $item['image'], $post_id, $item['title'] );
						if ( $attachment_id ) {
							set_post_thumbnail( $post_id, $attachment_id );
						}
					}
				}

				$count++;
			}

			return $count;
		}
	}

	WP_CLI::add_command( '4way', 'FourWay_SEED_Command' );
}
