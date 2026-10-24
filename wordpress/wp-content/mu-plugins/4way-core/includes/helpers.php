<?php
/**
 * Shared helpers: defaults, settings access, meta normalisation.
 *
 * @package fourway
 */

defined( 'ABSPATH' ) || exit;

/**
 * Load the design content that ships with the plugin.
 *
 * The JSON file is generated from frontend/src/data/site-content.json by
 * `npm run wp:defaults`, so React and WordPress always start from the same
 * copy deck.
 *
 * @return array
 */
function fourway_defaults() {
	static $defaults = null;

	if ( null !== $defaults ) {
		return $defaults;
	}

	$path = FOURWAY_CORE_DIR . '/content/defaults.json';
	if ( ! file_exists( $path ) ) {
		$defaults = array();
		return $defaults;
	}

	$raw      = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$decoded  = json_decode( (string) $raw, true );
	$defaults = is_array( $decoded ) ? $decoded : array();

	return $defaults;
}

/**
 * Merge two arrays recursively, keeping scalar values from $override unless
 * they are empty strings (so blank CMS fields never erase the design copy).
 *
 * @param array $base     Defaults.
 * @param array $override Stored values.
 * @return array
 */
function fourway_array_merge( array $base, array $override ) {
	foreach ( $override as $key => $value ) {
		if ( is_array( $value ) && isset( $base[ $key ] ) && is_array( $base[ $key ] ) ) {
			$base[ $key ] = fourway_array_merge( $base[ $key ], $value );
			continue;
		}

		if ( '' === $value || null === $value ) {
			continue;
		}

		$base[ $key ] = $value;
	}

	return $base;
}

/**
 * All homepage settings (WordPress options merged over the shipped defaults).
 *
 * @return array
 */
function fourway_get_settings() {
	$defaults = fourway_defaults();
	$defaults = isset( $defaults['settings'] ) ? $defaults['settings'] : array();
	$stored   = get_option( 'fourway_settings', array() );

	if ( ! is_array( $stored ) ) {
		$stored = array();
	}

	return fourway_array_merge( $defaults, $stored );
}

/**
 * Read a single settings value using dot notation (e.g. header.phoneDisplay).
 *
 * @param string $path    Dot separated path.
 * @param mixed  $default Fallback value.
 * @return mixed
 */
function fourway_setting( $path, $default = '' ) {
	$value = fourway_get_settings();

	foreach ( explode( '.', $path ) as $segment ) {
		if ( ! is_array( $value ) || ! array_key_exists( $segment, $value ) ) {
			return $default;
		}
		$value = $value[ $segment ];
	}

	return $value;
}

/**
 * Read a custom field with an optional default.
 *
 * @param int    $post_id Post ID.
 * @param string $key     Meta key.
 * @param mixed  $default Default value.
 * @return mixed
 */
function fourway_meta( $post_id, $key, $default = '' ) {
	$value = get_post_meta( $post_id, '_fourway_' . $key, true );

	if ( '' === $value || null === $value ) {
		return $default;
	}

	return $value;
}

/**
 * Decode WordPress' HTML-encoded titles/excerpts (e.g. `&#038;`) into the
 * plain text the React components render.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function fourway_plain_text( $value ) {
	return html_entity_decode( wp_strip_all_tags( (string) $value ), ENT_QUOTES, 'UTF-8' );
}

/**
 * Resolve the best image URL for a post: featured image, then the image_url
 * custom field, then an optional hard-coded fallback.
 *
 * @param int    $post_id  Post ID.
 * @param string $fallback Fallback URL.
 * @return string
 */
function fourway_image_url( $post_id, $fallback = '' ) {
	$thumbnail = get_the_post_thumbnail_url( $post_id, 'full' );

	if ( $thumbnail ) {
		return $thumbnail;
	}

	return (string) fourway_meta( $post_id, 'image_url', $fallback );
}

/**
 * Resolve the alt text for a post image.
 *
 * @param int    $post_id  Post ID.
 * @param string $fallback Fallback alt text.
 * @return string
 */
function fourway_image_alt( $post_id, $fallback = '' ) {
	$alt = fourway_meta( $post_id, 'image_alt', '' );

	if ( $alt ) {
		return $alt;
	}

	$thumbnail_id = get_post_thumbnail_id( $post_id );
	if ( $thumbnail_id ) {
		$alt = get_post_meta( $thumbnail_id, '_wp_attachment_image_alt', true );
	}

	return $alt ? $alt : $fallback;
}

/**
 * Title helper that always returns decoded plain text.
 *
 * @param int|WP_Post $post Post.
 * @return string
 */
function fourway_title( $post ) {
	return fourway_plain_text( get_the_title( $post ) );
}

/**
 * Excerpt helper that always returns decoded plain text.
 *
 * @param int|WP_Post $post Post.
 * @return string
 */
function fourway_excerpt( $post ) {
	return fourway_plain_text( get_the_excerpt( $post ) );
}

/**
 * Published items of a custom post type.
 *
 * @param string $post_type Post type slug.
 * @param int    $limit     Max items (-1 for all).
 * @param array  $orderby   Optional WP_Query orderby clause.
 * @return WP_Post[]
 */
function fourway_get_items( $post_type, $limit = -1, $orderby = null ) {
	return get_posts(
		array(
			'post_type'        => $post_type,
			'post_status'      => 'publish',
			'posts_per_page'   => $limit,
			'orderby'          => $orderby ? $orderby : array(
				'menu_order' => 'ASC',
				'date'       => 'ASC',
			),
			'suppress_filters' => false,
		)
	);
}

/**
 * Human readable "August 15, 2024" date used by the design.
 *
 * @param int|WP_Post $post Post.
 * @return string
 */
function fourway_formatted_date( $post ) {
	return get_the_date( 'F d, Y', $post );
}
