<?php
/**
 * Custom fields for every homepage collection: registration, meta boxes,
 * validation and sanitisation.
 *
 * @package fourway
 */

defined( 'ABSPATH' ) || exit;

/**
 * Field schema per post type. Types: text, textarea, url, number, checkbox, select.
 *
 * @return array
 */
function fourway_field_schema() {
	return array(
		'trust_badge'  => array(
			'icon'        => array(
				'label'   => 'Icon class',
				'type'    => 'text',
				'help'    => 'Font Awesome class, e.g. fa-solid fa-shield-halved',
				'default' => 'fa-solid fa-shield-halved',
			),
			'description' => array(
				'label' => 'Description',
				'type'  => 'textarea',
			),
		),
		'service'      => array(
			'description' => array(
				'label' => 'Card description',
				'type'  => 'textarea',
			),
			'href'        => array(
				'label'   => 'Card link',
				'type'    => 'text',
				'default' => '#contact',
			),
			'image_url'   => array(
				'label' => 'Image URL (used when no featured image is set)',
				'type'  => 'text',
				'help'  => 'Paste a media URL or pick a featured image instead.',
			),
			'image_alt'   => array(
				'label' => 'Image alt text',
				'type'  => 'text',
			),
		),
		'project'      => array(
			'category'    => array(
				'label' => 'Category line',
				'type'  => 'text',
				'help'  => 'e.g. Industrial • Gujarat GIDC',
			),
			'description' => array(
				'label' => 'Project summary',
				'type'  => 'textarea',
			),
			'href'        => array(
				'label'   => 'Card link',
				'type'    => 'text',
				'default' => '#contact',
			),
			'image_url'   => array(
				'label' => 'Image URL (used when no featured image is set)',
				'type'  => 'text',
			),
			'image_alt'   => array(
				'label' => 'Image alt text',
				'type'  => 'text',
			),
		),
		'testimonial'  => array(
			'role'       => array(
				'label' => 'Author role / company',
				'type'  => 'text',
			),
			'quote'      => array(
				'label' => 'Quote',
				'type'  => 'textarea',
			),
			'rating'     => array(
				'label'   => 'Star rating (1-5)',
				'type'    => 'number',
				'default' => 5,
			),
			'avatar_url' => array(
				'label' => 'Avatar image URL',
				'type'  => 'text',
			),
		),
		'faq'          => array(
			'answer' => array(
				'label' => 'Answer',
				'type'  => 'textarea',
			),
			'open'   => array(
				'label' => 'Expanded by default',
				'type'  => 'checkbox',
			),
		),
		'process_step' => array(
			'number'      => array(
				'label'   => 'Step number',
				'type'    => 'text',
				'default' => '01',
			),
			'icon'        => array(
				'label'   => 'Icon class',
				'type'    => 'text',
				'default' => 'fa-solid fa-clipboard-list',
			),
			'description' => array(
				'label' => 'Step description',
				'type'  => 'textarea',
			),
		),
		'reason'       => array(
			'icon'        => array(
				'label'   => 'Icon class',
				'type'    => 'text',
				'default' => 'fa-solid fa-user-tie',
			),
			'description' => array(
				'label' => 'Description',
				'type'  => 'textarea',
			),
			'column'      => array(
				'label'   => 'Layout column',
				'type'    => 'select',
				'options' => array(
					'left'  => 'Left of image',
					'right' => 'Right of image',
				),
				'default' => 'left',
			),
		),
	);
}

/**
 * Register every custom field with the REST API.
 */
function fourway_register_meta() {
	foreach ( fourway_field_schema() as $post_type => $fields ) {
		foreach ( $fields as $key => $field ) {
			$type = 'string';
			if ( 'number' === $field['type'] ) {
				$type = 'integer';
			} elseif ( 'checkbox' === $field['type'] ) {
				$type = 'boolean';
			}

			register_post_meta(
				$post_type,
				'_fourway_' . $key,
				array(
					'type'              => $type,
					'single'            => true,
					'show_in_rest'      => true,
					'sanitize_callback' => static function ( $value ) use ( $field ) {
						return fourway_sanitize_field_value( $value, $field['type'] );
					},
					'auth_callback'     => static function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);
		}
	}
}
add_action( 'init', 'fourway_register_meta', 20 );

/**
 * Sanitise a single field value based on its declared type.
 *
 * @param mixed  $value Raw value.
 * @param string $type  Field type.
 * @return mixed
 */
function fourway_sanitize_field_value( $value, $type ) {
	switch ( $type ) {
		case 'textarea':
			return sanitize_textarea_field( (string) $value );
		case 'url':
			return esc_url_raw( (string) $value );
		case 'number':
			$number = (int) $value;
			return max( 0, min( 5, $number ) );
		case 'checkbox':
			return (bool) $value;
		case 'select':
			return sanitize_key( (string) $value );
		default:
			return sanitize_text_field( (string) $value );
	}
}

/**
 * Register a meta box for each collection.
 */
function fourway_add_meta_boxes() {
	foreach ( array_keys( fourway_field_schema() ) as $post_type ) {
		add_meta_box(
			'fourway-fields-' . $post_type,
			'4Way content fields',
			'fourway_render_meta_box',
			$post_type,
			'normal',
			'high'
		);
	}
}
add_action( 'add_meta_boxes', 'fourway_add_meta_boxes' );

/**
 * Render the shared meta box.
 *
 * @param WP_Post $post Current post.
 */
function fourway_render_meta_box( $post ) {
	$schema = fourway_field_schema();
	if ( ! isset( $schema[ $post->post_type ] ) ) {
		return;
	}

	wp_nonce_field( 'fourway_save_meta_' . $post->ID, 'fourway_meta_nonce' );

	echo '<table class="form-table" role="presentation"><tbody>';

	foreach ( $schema[ $post->post_type ] as $key => $field ) {
		$value = get_post_meta( $post->ID, '_fourway_' . $key, true );
		if ( '' === $value && isset( $field['default'] ) ) {
			$value = $field['default'];
		}
		$id = 'fourway-' . $key;

		echo '<tr><th scope="row"><label for="' . esc_attr( $id ) . '">' . esc_html( $field['label'] ) . '</label></th><td>';

		if ( 'textarea' === $field['type'] ) {
			printf(
				'<textarea class="large-text" id="%1$s" name="fourway[%2$s]" rows="4">%3$s</textarea>',
				esc_attr( $id ),
				esc_attr( $key ),
				esc_textarea( (string) $value )
			);
		} elseif ( 'checkbox' === $field['type'] ) {
			printf(
				'<input id="%1$s" name="fourway[%2$s]" type="checkbox" value="1" %3$s />',
				esc_attr( $id ),
				esc_attr( $key ),
				checked( (bool) $value, true, false )
			);
		} elseif ( 'select' === $field['type'] ) {
			printf( '<select id="%1$s" name="fourway[%2$s]">', esc_attr( $id ), esc_attr( $key ) );
			foreach ( $field['options'] as $option_value => $option_label ) {
				printf(
					'<option value="%1$s" %2$s>%3$s</option>',
					esc_attr( $option_value ),
					selected( (string) $value, (string) $option_value, false ),
					esc_html( $option_label )
				);
			}
			echo '</select>';
		} else {
			printf(
				'<input class="regular-text" id="%1$s" name="fourway[%2$s]" type="%3$s" value="%4$s" />',
				esc_attr( $id ),
				esc_attr( $key ),
				'number' === $field['type'] ? 'number' : 'text',
				esc_attr( (string) $value )
			);
		}

		if ( ! empty( $field['help'] ) ) {
			echo '<p class="description">' . esc_html( $field['help'] ) . '</p>';
		}

		echo '</td></tr>';
	}

	echo '</tbody></table>';
}

/**
 * Persist meta box values.
 *
 * @param int $post_id Post ID.
 */
function fourway_save_meta( $post_id ) {
	if ( ! isset( $_POST['fourway_meta_nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['fourway_meta_nonce'] ) ), 'fourway_save_meta_' . $post_id ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$schema = fourway_field_schema();
	$type   = get_post_type( $post_id );

	if ( ! isset( $schema[ $type ] ) ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified above.
	$raw = isset( $_POST['fourway'] ) ? wp_unslash( (array) $_POST['fourway'] ) : array();

	foreach ( $schema[ $type ] as $key => $field ) {
		$value = isset( $raw[ $key ] ) ? $raw[ $key ] : ( 'checkbox' === $field['type'] ? false : '' );
		$value = fourway_sanitize_field_value( $value, $field['type'] );

		if ( '' === $value || false === $value ) {
			delete_post_meta( $post_id, '_fourway_' . $key );
			continue;
		}

		update_post_meta( $post_id, '_fourway_' . $key, $value );
	}
}
add_action( 'save_post', 'fourway_save_meta' );

