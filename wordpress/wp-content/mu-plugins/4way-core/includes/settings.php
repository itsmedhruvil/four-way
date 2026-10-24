<?php
/**
 * Homepage settings screen. The form is generated from the shipped defaults so
 * every headline, CTA and footer link is editable without touching code.
 *
 * @package fourway
 */

defined( 'ABSPATH' ) || exit;

/**
 * True when the array is a sequential list.
 *
 * @param array $value Array.
 * @return bool
 */
function fourway_is_list( array $value ) {
	$keys = array_keys( $value );
	return $keys === range( 0, count( $value ) - 1 );
}

/**
 * Flatten nested settings into path => value pairs.
 *
 * @param array  $source Source array.
 * @param string $prefix Path prefix.
 * @return array
 */
function fourway_flatten_settings( array $source, $prefix = '' ) {
	$flat = array();

	foreach ( $source as $key => $value ) {
		$path = '' === $prefix ? (string) $key : $prefix . '.' . $key;

		if ( is_array( $value ) && ! fourway_is_list( $value ) && ! empty( $value ) ) {
			$flat += fourway_flatten_settings( $value, $path );
			continue;
		}

		$flat[ $path ] = $value;
	}

	return $flat;
}

/**
 * Write a value into a nested array using dot notation.
 *
 * @param array  $target Target array (by reference).
 * @param string $path   Dot separated path.
 * @param mixed  $value  Value.
 */
function fourway_set_nested( array &$target, $path, $value ) {
	$segments = explode( '.', $path );
	$cursor   = &$target;

	foreach ( $segments as $index => $segment ) {
		if ( $index === count( $segments ) - 1 ) {
			$cursor[ $segment ] = $value;
			return;
		}

		if ( ! isset( $cursor[ $segment ] ) || ! is_array( $cursor[ $segment ] ) ) {
			$cursor[ $segment ] = array();
		}
		$cursor = &$cursor[ $segment ];
	}
}

/**
 * Recursively sanitise a decoded JSON structure.
 *
 * @param mixed $value Decoded value.
 * @return mixed
 */
function fourway_sanitize_json_value( $value ) {
	if ( is_array( $value ) ) {
		$clean = array();
		foreach ( $value as $key => $item ) {
			$clean[ is_int( $key ) ? $key : sanitize_text_field( (string) $key ) ] = fourway_sanitize_json_value( $item );
		}
		return $clean;
	}

	if ( is_bool( $value ) ) {
		return $value;
	}

	return sanitize_textarea_field( (string) $value );
}

/**
 * Sanitise the whole settings payload.
 *
 * @param array $input Raw form values.
 * @return array
 */
function fourway_sanitize_settings( $input ) {
	$defaults = fourway_defaults();
	$defaults = isset( $defaults['settings'] ) ? $defaults['settings'] : array();
	$stored   = get_option( 'fourway_settings', array() );
	$base     = fourway_array_merge( $defaults, is_array( $stored ) ? $stored : array() );

	if ( ! is_array( $input ) ) {
		return $base;
	}

	foreach ( fourway_flatten_settings( $defaults ) as $path => $default_value ) {
		if ( ! array_key_exists( $path, $input ) ) {
			continue;
		}

		$value = $input[ $path ];

		if ( is_array( $default_value ) ) {
			if ( is_array( $value ) ) {
				$value = fourway_sanitize_json_value( $value );
			} else {
				$decoded = json_decode( (string) $value, true );
				if ( ! is_array( $decoded ) ) {
					continue; // Keep the previous value when JSON is invalid.
				}
				$value = fourway_sanitize_json_value( $decoded );
			}
		} else {
			$value = sanitize_textarea_field( (string) $value );
		}

		fourway_set_nested( $base, $path, $value );
	}

	return $base;
}

/**
 * Register the option and admin menu.
 */
function fourway_register_settings() {
	register_setting(
		'fourway_settings_group',
		'fourway_settings',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'fourway_sanitize_settings',
			'default'           => array(),
		)
	);
}
add_action( 'admin_init', 'fourway_register_settings' );

/**
 * Add the 4Way admin menu and its screens.
 */
function fourway_register_admin_menu() {
	add_menu_page(
		'4Way Safety',
		'4Way Safety',
		'manage_options',
		'fourway-settings',
		'fourway_render_settings_page',
		'dashicons-shield',
		3
	);

	add_submenu_page(
		'fourway-settings',
		'Homepage Content',
		'Homepage Content',
		'manage_options',
		'fourway-settings',
		'fourway_render_settings_page'
	);

	add_submenu_page(
		'fourway-settings',
		'Quote Requests',
		'Quote Requests',
		'manage_options',
		'fourway-leads',
		'fourway_render_leads_page'
	);

	add_submenu_page(
		'fourway-settings',
		'Subscribers',
		'Subscribers',
		'manage_options',
		'fourway-subscribers',
		'fourway_render_subscribers_page'
	);
}
add_action( 'admin_menu', 'fourway_register_admin_menu' );

/**
 * Human label for a settings group.
 *
 * @param string $group Group key.
 * @return string
 */
function fourway_settings_group_label( $group ) {
	$labels = array(
		'brand'               => 'Brand',
		'navigation'          => 'Navigation',
		'header'              => 'Header',
		'hero'                => 'Hero section',
		'about'               => 'Who we are',
		'servicesSection'     => 'Services heading',
		'reasonsSection'      => 'Why choose us',
		'processSection'      => 'Process heading',
		'projectsSection'     => 'Projects heading',
		'testimonialsSection' => 'Testimonials heading',
		'faqSection'          => 'FAQ heading',
		'ctaSection'          => 'Dark CTA banner',
		'blogSection'         => 'Blog heading',
		'newsletter'          => 'Newsletter bar',
		'footer'              => 'Footer',
		'seo'                 => 'SEO',
	);

	return isset( $labels[ $group ] ) ? $labels[ $group ] : ucfirst( $group );
}

/**
 * Render the settings screen.
 */
function fourway_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$defaults = fourway_defaults();
	$defaults = isset( $defaults['settings'] ) ? $defaults['settings'] : array();
	$current  = fourway_get_settings();

	echo '<div class="wrap"><h1>4Way Safety — Homepage Content</h1>';
	echo '<p>Every field below powers the React homepage. Collections (services, projects, testimonials, FAQs, process steps and badges) are edited from their own menus in the sidebar.</p>';
	echo '<p><strong>Tip:</strong> list fields accept JSON arrays. Keep the structure intact and save.</p>';
	echo '<form method="post" action="options.php">';
	settings_fields( 'fourway_settings_group' );

	foreach ( $defaults as $group => $group_value ) {
		echo '<div class="card" style="max-width:960px;padding:8px 20px 20px;margin-bottom:20px;">';
		echo '<h2>' . esc_html( fourway_settings_group_label( $group ) ) . '</h2>';
		echo '<table class="form-table" role="presentation"><tbody>';

		if ( is_array( $group_value ) && ! fourway_is_list( $group_value ) ) {
			foreach ( fourway_flatten_settings( $group_value, $group ) as $path => $default_value ) {
				fourway_render_settings_field( $path, $default_value, $current );
			}
		} else {
			fourway_render_settings_field( $group, $group_value, $current );
		}

		echo '</tbody></table></div>';
	}

	submit_button( 'Save homepage content' );
	echo '</form>';

	/**
	 * Lets the headless module append its own settings form.
	 */
	do_action( 'fourway_after_settings_form' );

	echo '</div>';
}

/**
 * Render a single settings row.
 *
 * @param string $path          Dot path.
 * @param mixed  $default_value Default value.
 * @param array  $current       Current merged settings.
 */
function fourway_render_settings_field( $path, $default_value, array $current ) {
	$segments = explode( '.', $path );
	$value    = $current;
	foreach ( $segments as $segment ) {
		if ( ! is_array( $value ) || ! array_key_exists( $segment, $value ) ) {
			$value = $default_value;
			break;
		}
		$value = $value[ $segment ];
	}

	$id    = 'fourway-settings-' . str_replace( '.', '-', $path );
	$name  = 'fourway_settings[' . $path . ']';
	$label = ucwords( preg_replace( '/(?<!^)[A-Z]/', ' $0', end( $segments ) ) );

	echo '<tr><th scope="row"><label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label>';
	echo '<p class="description" style="font-family:monospace;">' . esc_html( $path ) . '</p></th><td>';

	if ( is_array( $value ) ) {
		printf(
			'<textarea class="large-text code" id="%1$s" name="%2$s" rows="%3$d">%4$s</textarea>',
			esc_attr( $id ),
			esc_attr( $name ),
			(int) min( 14, max( 4, substr_count( (string) wp_json_encode( $value ), "\n" ) + 4 ) ),
			esc_textarea( (string) wp_json_encode( $value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) )
		);
	} elseif ( strlen( (string) $default_value ) > 90 || false !== strpos( (string) $default_value, "\n" ) ) {
		printf(
			'<textarea class="large-text" id="%1$s" name="%2$s" rows="4">%3$s</textarea>',
			esc_attr( $id ),
			esc_attr( $name ),
			esc_textarea( (string) $value )
		);
	} else {
		printf(
			'<input class="regular-text" id="%1$s" name="%2$s" type="text" value="%3$s" />',
			esc_attr( $id ),
			esc_attr( $name ),
			esc_attr( (string) $value )
		);
	}

	echo '</td></tr>';
}

