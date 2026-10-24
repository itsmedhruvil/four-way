<?php
/**
 * Headless behaviour: front-end redirect, CORS for the React origin, and the
 * WordPress admin settings that control them.
 *
 * @package fourway
 */

defined( 'ABSPATH' ) || exit;

/**
 * Resolve the public URL of the React application.
 *
 * Priority: FOURWAY_FRONTEND_URL environment variable (Docker), then the
 * saved option, then the local Vite dev server.
 *
 * @return string
 */
function fourway_frontend_url() {
	$env = getenv( 'FOURWAY_FRONTEND_URL' );
	$url = $env ? $env : get_option( 'fourway_frontend_url', 'http://localhost:5173' );

	return untrailingslashit( esc_url_raw( (string) $url ) );
}

/**
 * Should WordPress hand every public page over to the React app?
 *
 * @return bool
 */
function fourway_headless_redirect_enabled() {
	return (bool) get_option( 'fourway_headless_redirect', 1 );
}

/**
 * Extra origins allowed to call the REST API from the browser.
 *
 * @return string[]
 */
function fourway_allowed_origins() {
	$raw   = (string) get_option( 'fourway_allowed_origins', '' );
	$lines = preg_split( '/\r\n|\r|\n/', $raw );
	$urls  = array_filter( array_map( 'trim', (array) $lines ) );
	$urls[] = fourway_frontend_url();
	$urls[] = 'http://localhost:5173';
	$urls[] = 'http://127.0.0.1:5173';
	$urls[] = 'http://localhost:4173';

	return array_values( array_unique( array_filter( $urls ) ) );
}

/**
 * Allow the React origin through WordPress' REST CORS checks.
 *
 * @param string[] $origins Registered origins.
 * @return string[]
 */
function fourway_filter_allowed_origins( $origins ) {
	return array_values( array_unique( array_merge( (array) $origins, fourway_allowed_origins() ) ) );
}
add_filter( 'allowed_http_origins', 'fourway_filter_allowed_origins' );

/**
 * Redirect public WordPress front-end requests to the React app.
 */
function fourway_maybe_redirect_frontend() {
	if ( ! fourway_headless_redirect_enabled() ) {
		return;
	}

	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
		return;
	}

	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return;
	}

	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		return;
	}

	// Let previewers, sitemaps, robots.txt and feeds stay on WordPress.
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['fourway_preview'] ) ) {
		return;
	}

	$path = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ) : '';
	if ( $path && preg_match( '#/(robots\.txt|sitemap[^/]*\.xml|wp-sitemap\.xml|feed)/?$#', $path ) ) {
		return;
	}

	wp_redirect( fourway_frontend_url() . '/', 302 ); // phpcs:ignore WordPress.Security.SafeRedirect
	exit;
}
add_action( 'template_redirect', 'fourway_maybe_redirect_frontend' );

/**
 * Register the headless options.
 */
function fourway_register_headless_settings() {
	register_setting(
		'fourway_headless_group',
		'fourway_frontend_url',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'default'           => 'http://localhost:5173',
		)
	);

	register_setting(
		'fourway_headless_group',
		'fourway_headless_redirect',
		array(
			'type'              => 'string',
			'sanitize_callback' => static function ( $value ) {
				return $value ? '1' : '0';
			},
			'default'           => '1',
		)
	);

	register_setting(
		'fourway_headless_group',
		'fourway_allowed_origins',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
			'default'           => '',
		)
	);
}
add_action( 'admin_init', 'fourway_register_headless_settings' );

/**
 * Render the headless settings card under the homepage settings form.
 */
function fourway_render_headless_settings() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$redirect = fourway_headless_redirect_enabled();
	$origins  = (string) get_option( 'fourway_allowed_origins', '' );
	?>
	<hr />
	<h2>Headless front end</h2>
	<form method="post" action="options.php">
		<?php settings_fields( 'fourway_headless_group' ); ?>
		<table class="form-table" role="presentation"><tbody>
			<tr>
				<th scope="row"><label for="fourway-frontend-url">React app URL</label></th>
				<td>
					<input class="regular-text" id="fourway-frontend-url" name="fourway_frontend_url" type="url" value="<?php echo esc_attr( fourway_frontend_url() ); ?>" />
					<p class="description">Where visitors are sent. Set by <code>FOURWAY_FRONTEND_URL</code> in Docker when present.</p>
				</td>
			</tr>
			<tr>
				<th scope="row">Front-end redirect</th>
				<td>
					<input name="fourway_headless_redirect" type="hidden" value="0" />
					<label>
						<input name="fourway_headless_redirect" type="checkbox" value="1" <?php checked( $redirect ); ?> />
						Redirect all WordPress page views to the React app (recommended for headless).
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fourway-allowed-origins">Extra CORS origins</label></th>
				<td>
					<textarea class="large-text code" id="fourway-allowed-origins" name="fourway_allowed_origins" rows="3"><?php echo esc_textarea( $origins ); ?></textarea>
					<p class="description">One origin per line, e.g. <code>https://4waysafety.com</code>. The React app URL is always allowed.</p>
				</td>
			</tr>
		</tbody></table>
		<?php submit_button( 'Save headless settings' ); ?>
	</form>
	<?php
}
add_action( 'fourway_after_settings_form', 'fourway_render_headless_settings' );

/**
 * Small hardening: the React site never uses XML-RPC.
 */
add_filter( 'xmlrpc_enabled', '__return_false' );
