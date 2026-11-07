<?php
/**
 * Plugin Name:       MNM Foundation Core
 * Plugin URI:        https://www.facebook.com/MNMFOUNDATIONINDIA
 * Description:       Prototype companion plugin for MNM Foundation: content types (projects, programmes, events, articles), gallery collections, demonstration marathon registrations, fundraising campaigns, settings and sample data. Keep it active when changing themes.
 * Version:           1.0.0
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * Author:            MNM Foundation
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       mnm-foundation-core
 * Domain Path:       /languages
 *
 * @package MNM_Foundation_Core
 */

defined( 'ABSPATH' ) || exit;

define( 'MNM_CORE_VERSION', '1.0.0' );
define( 'MNM_CORE_DB_VERSION', '1' );
define( 'MNM_CORE_FILE', __FILE__ );
define( 'MNM_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'MNM_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once MNM_CORE_DIR . 'includes/helpers.php';
require_once MNM_CORE_DIR . 'includes/post-types.php';
require_once MNM_CORE_DIR . 'includes/fields.php';
require_once MNM_CORE_DIR . 'includes/registration.php';
require_once MNM_CORE_DIR . 'includes/frontend.php';
require_once MNM_CORE_DIR . 'includes/demo-data.php';

if ( is_admin() ) {
	require_once MNM_CORE_DIR . 'includes/admin.php';
}

// Rewrite rules need flushing once when the plugin is activated.
register_activation_hook(
	__FILE__,
	function () {
		mnm_register_post_types();
		flush_rewrite_rules();
	}
);
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );

/**
 * Load translations.
 */
function mnm_core_load_textdomain() {
	load_plugin_textdomain( 'mnm-foundation-core', false, dirname( plugin_basename( MNM_CORE_FILE ) ) . '/languages' );
}
add_action( 'init', 'mnm_core_load_textdomain' );
