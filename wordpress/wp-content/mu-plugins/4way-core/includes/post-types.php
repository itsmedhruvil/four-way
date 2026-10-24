<?php
/**
 * Custom post types and taxonomies that back the homepage sections.
 *
 * @package fourway
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register every editable homepage collection.
 */
function fourway_register_post_types() {
	$types = array(
		'service'      => array(
			'singular' => 'Service',
			'plural'   => 'Services',
			'icon'     => 'dashicons-shield-alt',
		),
		'project'      => array(
			'singular' => 'Project',
			'plural'   => 'Projects',
			'icon'     => 'dashicons-building',
		),
		'testimonial'  => array(
			'singular' => 'Testimonial',
			'plural'   => 'Testimonials',
			'icon'     => 'dashicons-format-quote',
		),
		'faq'          => array(
			'singular' => 'FAQ',
			'plural'   => 'FAQs',
			'icon'     => 'dashicons-editor-help',
		),
		'process_step' => array(
			'singular' => 'Process Step',
			'plural'   => 'Process Steps',
			'icon'     => 'dashicons-list-view',
		),
		'reason'       => array(
			'singular' => 'Why Choose Us Card',
			'plural'   => 'Why Choose Us Cards',
			'icon'     => 'dashicons-awards',
		),
		'trust_badge'  => array(
			'singular' => 'Trust Badge',
			'plural'   => 'Trust Badges',
			'icon'     => 'dashicons-yes-alt',
		),
	);

	foreach ( $types as $slug => $labels ) {
		register_post_type(
			$slug,
			array(
				'labels'        => array(
					'name'          => $labels['plural'],
					'singular_name' => $labels['singular'],
					'add_new_item'  => 'Add New ' . $labels['singular'],
					'edit_item'     => 'Edit ' . $labels['singular'],
					'all_items'     => 'All ' . $labels['plural'],
					'menu_name'     => $labels['plural'],
				),
				'public'        => true,
				'show_in_rest'  => true,
				'rest_base'     => $slug . 's',
				'menu_icon'     => $labels['icon'],
				'menu_position' => 26,
				'has_archive'   => false,
				'supports'      => array( 'title', 'thumbnail', 'page-attributes', 'excerpt' ),
				'rewrite'       => array( 'slug' => $slug . 's' ),
			)
		);
	}
}
add_action( 'init', 'fourway_register_post_types', 5 );

/**
 * Sector taxonomy used to group projects and services (SECTORS navigation).
 */
function fourway_register_taxonomies() {
	register_taxonomy(
		'sector',
		array( 'project', 'service' ),
		array(
			'labels'            => array(
				'name'          => 'Sectors',
				'singular_name' => 'Sector',
			),
			'public'            => true,
			'show_in_rest'      => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
		)
	);
}
add_action( 'init', 'fourway_register_taxonomies', 6 );
