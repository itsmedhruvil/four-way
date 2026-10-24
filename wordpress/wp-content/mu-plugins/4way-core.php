<?php
/**
 * Plugin Name: 4Way Safety Headless Core
 * Description: Content model (custom post types, custom fields, custom tables) and REST API for the 4Way Safety Solutions React frontend.
 * Version: 1.0.0
 * Author: 4Way Safety Solutions LLP
 * Text Domain: fourway
 *
 * This file is an mu-plugin so the content model is always available, even
 * before an administrator activates anything.
 */

defined( 'ABSPATH' ) || exit;

define( 'FOURWAY_CORE_VERSION', '1.0.0' );
define( 'FOURWAY_CORE_DIR', __DIR__ . '/4way-core' );

require_once FOURWAY_CORE_DIR . '/includes/helpers.php';
require_once FOURWAY_CORE_DIR . '/includes/post-types.php';
require_once FOURWAY_CORE_DIR . '/includes/fields.php';
require_once FOURWAY_CORE_DIR . '/includes/settings.php';
require_once FOURWAY_CORE_DIR . '/includes/leads.php';
require_once FOURWAY_CORE_DIR . '/includes/rest.php';
require_once FOURWAY_CORE_DIR . '/includes/headless.php';
require_once FOURWAY_CORE_DIR . '/includes/seed.php';

/**
 * Install the custom database tables on every request where the schema
 * version changed (cheap option lookup, no writes on normal requests).
 */
add_action( 'init', 'fourway_maybe_install_tables' );
