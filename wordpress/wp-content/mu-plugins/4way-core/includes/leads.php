<?php
/**
 * Custom database tables for enquiries and newsletter subscribers, plus their
 * WordPress admin screens.
 *
 * @package fourway
 */

defined( 'ABSPATH' ) || exit;

const FOURWAY_DB_VERSION = '1.0.0';

/**
 * Table name helper.
 *
 * @param string $suffix Table suffix (leads|subscribers).
 * @return string
 */
function fourway_table( $suffix ) {
	global $wpdb;
	return $wpdb->prefix . '4way_' . $suffix;
}

/**
 * Create or upgrade the custom tables when the schema version changes.
 */
function fourway_maybe_install_tables() {
	if ( ! is_blog_installed() ) {
		return; // WordPress core tables do not exist yet (fresh install).
	}

	if ( get_option( 'fourway_db_version' ) === FOURWAY_DB_VERSION ) {
		return;
	}

	fourway_install_tables();
}

/**
 * Install the leads and subscribers tables with dbDelta().
 */
function fourway_install_tables() {
	global $wpdb;

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$charset_collate = $wpdb->get_charset_collate();
	$leads           = fourway_table( 'leads' );
	$subscribers     = fourway_table( 'subscribers' );

	$sql_leads = "CREATE TABLE {$leads} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		created_at datetime NOT NULL,
		name varchar(190) NOT NULL DEFAULT '',
		email varchar(190) NOT NULL DEFAULT '',
		phone varchar(60) NOT NULL DEFAULT '',
		facility varchar(190) NOT NULL DEFAULT '',
		message text NOT NULL,
		source varchar(100) NOT NULL DEFAULT '',
		status varchar(20) NOT NULL DEFAULT 'new',
		ip_hash char(64) NOT NULL DEFAULT '',
		PRIMARY KEY  (id),
		KEY created_at (created_at),
		KEY email (email)
	) {$charset_collate};";

	$sql_subscribers = "CREATE TABLE {$subscribers} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		email varchar(190) NOT NULL,
		source varchar(100) NOT NULL DEFAULT '',
		created_at datetime NOT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY email (email)
	) {$charset_collate};";

	dbDelta( $sql_leads );
	dbDelta( $sql_subscribers );

	update_option( 'fourway_db_version', FOURWAY_DB_VERSION );
}

/**
 * Persist a quote request.
 *
 * @param array $data Sanitised lead data.
 * @return int|false Inserted row ID or false.
 */
function fourway_insert_lead( array $data ) {
	global $wpdb;

	$row = array(
		'created_at' => current_time( 'mysql' ),
		'name'       => (string) $data['name'],
		'email'      => (string) $data['email'],
		'phone'      => isset( $data['phone'] ) ? (string) $data['phone'] : '',
		'facility'   => isset( $data['facility'] ) ? (string) $data['facility'] : '',
		'message'    => (string) $data['message'],
		'source'     => isset( $data['source'] ) ? (string) $data['source'] : 'homepage',
		'status'     => 'new',
		'ip_hash'    => isset( $data['ip_hash'] ) ? (string) $data['ip_hash'] : '',
	);

	$inserted = $wpdb->insert( fourway_table( 'leads' ), $row ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

	return $inserted ? (int) $wpdb->insert_id : false;
}

/**
 * Store a newsletter subscriber (idempotent per email address).
 *
 * @param string $email  Email address.
 * @param string $source Source label.
 * @return bool
 */
function fourway_insert_subscriber( $email, $source = 'homepage-newsletter' ) {
	global $wpdb;

	$table = fourway_table( 'subscribers' );

	$result = $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->prepare(
			"INSERT INTO {$table} (email, source, created_at) VALUES (%s, %s, %s)
			 ON DUPLICATE KEY UPDATE source = VALUES(source)",
			$email,
			$source,
			current_time( 'mysql' )
		)
	);

	return false !== $result;
}

/**
 * Admin notice helper for the CRM screens.
 */
function fourway_render_admin_notice() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( empty( $_GET['fourway_notice'] ) ) {
		return;
	}

	$messages = array(
		'deleted'   => 'Entry deleted.',
		'contacted' => 'Quote request marked as contacted.',
	);

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$notice = sanitize_key( wp_unslash( $_GET['fourway_notice'] ) );

	if ( isset( $messages[ $notice ] ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $messages[ $notice ] ) . '</p></div>';
	}
}

/**
 * Quote requests screen.
 */
function fourway_render_leads_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	global $wpdb;
	$table = fourway_table( 'leads' );
	$rows  = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY created_at DESC LIMIT 200" ); // phpcs:ignore WordPress.DB

	echo '<div class="wrap"><h1>Quote Requests</h1>';
	fourway_render_admin_notice();
	echo '<p>Every submission from the React quote form is stored here in the <code>' . esc_html( $table ) . '</code> table. Total: <strong>' . (int) count( $rows ) . '</strong> (latest 200).</p>';
	echo '<table class="widefat striped"><thead><tr>';
	echo '<th>Received</th><th>Name</th><th>Contact</th><th>Facility</th><th>Requirement</th><th>Status</th><th>Actions</th>';
	echo '</tr></thead><tbody>';

	if ( empty( $rows ) ) {
		echo '<tr><td colspan="7">No quote requests yet.</td></tr>';
	}

	foreach ( $rows as $row ) {
		$delete_url = wp_nonce_url(
			add_query_arg(
				array(
					'action' => 'fourway_delete_lead',
					'lead'   => (int) $row->id,
				),
				admin_url( 'admin-post.php' )
			),
			'fourway_delete_lead_' . (int) $row->id
		);

		$contact_url = wp_nonce_url(
			add_query_arg(
				array(
					'action' => 'fourway_mark_lead_contacted',
					'lead'   => (int) $row->id,
				),
				admin_url( 'admin-post.php' )
			),
			'fourway_mark_lead_' . (int) $row->id
		);

		echo '<tr>';
		echo '<td>' . esc_html( $row->created_at ) . '</td>';
		echo '<td><strong>' . esc_html( $row->name ) . '</strong></td>';
		echo '<td><a href="mailto:' . esc_attr( $row->email ) . '">' . esc_html( $row->email ) . '</a><br>' . esc_html( $row->phone ) . '</td>';
		echo '<td>' . esc_html( $row->facility ) . '</td>';
		echo '<td>' . esc_html( $row->message ) . '</td>';
		echo '<td>' . esc_html( $row->status ) . '</td>';
		echo '<td><a href="' . esc_url( $contact_url ) . '">Mark contacted</a> | <a href="' . esc_url( $delete_url ) . '" onclick="return confirm(\'Delete this entry?\');">Delete</a></td>';
		echo '</tr>';
	}

	echo '</tbody></table></div>';
}

/**
 * Delete a lead.
 */
function fourway_handle_delete_lead() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'fourway' ) );
	}

	$id = isset( $_GET['lead'] ) ? absint( $_GET['lead'] ) : 0;
	check_admin_referer( 'fourway_delete_lead_' . $id );

	if ( $id ) {
		global $wpdb;
		$wpdb->delete( fourway_table( 'leads' ), array( 'id' => $id ), array( '%d' ) ); // phpcs:ignore WordPress.DB
	}

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'           => 'fourway-leads',
				'fourway_notice' => 'deleted',
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
}
add_action( 'admin_post_fourway_delete_lead', 'fourway_handle_delete_lead' );

/**
 * Mark a lead as contacted.
 */
function fourway_handle_mark_lead_contacted() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'fourway' ) );
	}

	$id = isset( $_GET['lead'] ) ? absint( $_GET['lead'] ) : 0;
	check_admin_referer( 'fourway_mark_lead_' . $id );

	if ( $id ) {
		global $wpdb;
		$wpdb->update( fourway_table( 'leads' ), array( 'status' => 'contacted' ), array( 'id' => $id ), array( '%s' ), array( '%d' ) ); // phpcs:ignore WordPress.DB
	}

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'           => 'fourway-leads',
				'fourway_notice' => 'contacted',
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
}
add_action( 'admin_post_fourway_mark_lead_contacted', 'fourway_handle_mark_lead_contacted' );


/**
 * Subscribers screen.
 */
function fourway_render_subscribers_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	global $wpdb;
	$table = fourway_table( 'subscribers' );
	$rows  = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY created_at DESC LIMIT 500" ); // phpcs:ignore WordPress.DB

	$export_url = wp_nonce_url(
		add_query_arg( 'action', 'fourway_export_subscribers', admin_url( 'admin-post.php' ) ),
		'fourway_export_subscribers'
	);

	echo '<div class="wrap"><h1>Newsletter Subscribers</h1>';
	echo '<p>Stored in <code>' . esc_html( $table ) . '</code>. Total: <strong>' . (int) count( $rows ) . '</strong> (latest 500).</p>';
	echo '<p><a class="button button-primary" href="' . esc_url( $export_url ) . '">Export CSV</a></p>';
	echo '<table class="widefat striped"><thead><tr><th>Email</th><th>Source</th><th>Subscribed at</th></tr></thead><tbody>';

	if ( empty( $rows ) ) {
		echo '<tr><td colspan="3">No subscribers yet.</td></tr>';
	}

	foreach ( $rows as $row ) {
		echo '<tr><td>' . esc_html( $row->email ) . '</td><td>' . esc_html( $row->source ) . '</td><td>' . esc_html( $row->created_at ) . '</td></tr>';
	}

	echo '</tbody></table></div>';
}

/**
 * Export subscribers as CSV.
 */
function fourway_export_subscribers() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'fourway' ) );
	}

	check_admin_referer( 'fourway_export_subscribers' );

	global $wpdb;
	$table = fourway_table( 'subscribers' );
	$rows  = $wpdb->get_results( "SELECT email, source, created_at FROM {$table} ORDER BY created_at DESC", ARRAY_A ); // phpcs:ignore WordPress.DB

	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=4way-subscribers.csv' );

	$output = fopen( 'php://output', 'w' );
	fputcsv( $output, array( 'Email', 'Source', 'Subscribed at' ) );
	foreach ( $rows as $row ) {
		fputcsv( $output, array( $row['email'], $row['source'], $row['created_at'] ) );
	}
	fclose( $output );
	exit;
}
add_action( 'admin_post_fourway_export_subscribers', 'fourway_export_subscribers' );

