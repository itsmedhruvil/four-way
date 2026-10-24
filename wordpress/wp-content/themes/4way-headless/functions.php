<?php
/**
 * Theme bootstrap for the headless shell.
 *
 * @package fourway-headless
 */

defined( 'ABSPATH' ) || exit;

/**
 * Basic theme supports.
 */
function fourway_headless_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption' ) );
}
add_action( 'after_setup_theme', 'fourway_headless_setup' );

/**
 * Keep the WordPress shell lean: the React app owns the front end.
 */
function fourway_headless_cleanup() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_head', 'wp_generator' );
}
add_action( 'init', 'fourway_headless_cleanup' );

/**
 * The React application URL used by the fallback template.
 *
 * @return string
 */
function fourway_headless_app_url() {
	return function_exists( 'fourway_frontend_url' ) ? fourway_frontend_url() : home_url( '/' );
}
