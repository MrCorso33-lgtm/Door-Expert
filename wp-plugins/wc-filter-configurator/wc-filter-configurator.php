<?php
/**
 * Plugin Name:       WC Filter Configurator
 * Plugin URI:        https://example.com/wc-filter-configurator
 * Description:       Drag & drop configuration of the product archive filter sidebar: which filters appear on which category, in what order, with what label and control type. Stores everything in an option so the theme stays free of hardcoded attribute lists.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Text Domain:       wc-filter-configurator
 * Domain Path:       /languages
 * License:           GPL-2.0-or-later
 *
 * @package WC_Filter_Configurator
 */

defined( 'ABSPATH' ) || exit;

define( 'WCFC_VERSION', '1.0.0' );
define( 'WCFC_DIR', plugin_dir_path( __FILE__ ) );
define( 'WCFC_URL', plugin_dir_url( __FILE__ ) );

/** Option holding the filter configuration, keyed by context. */
define( 'WCFC_OPTION', 'wcfc_filter_configs' );

/** Option holding admin-side attribute grouping (cosmetic; does not affect the front end). */
define( 'WCFC_GROUPS_OPTION', 'wcfc_attr_groups' );

/** Option holding the global cache version for category -> product id lookups. */
define( 'WCFC_CACHE_VER_OPTION', 'wcfc_cat_pids_ver' );

/** Schema version, used to gate one-off migrations. */
define( 'WCFC_DB_VERSION', 1 );

require_once WCFC_DIR . 'includes/config.php';
require_once WCFC_DIR . 'includes/attributes.php';
require_once WCFC_DIR . 'includes/scoping.php';
require_once WCFC_DIR . 'includes/render.php';
require_once WCFC_DIR . 'includes/ajax.php';

if ( is_admin() ) {
	require_once WCFC_DIR . 'includes/admin.php';
}

/**
 * WooCommerce is a hard dependency: the attribute pool, the term queries and the
 * price filter all rely on it. Without it the plugin stays inert and says so.
 */
function wcfc_has_woocommerce(): bool {
	return class_exists( 'WooCommerce' ) && function_exists( 'wc_get_attribute_taxonomies' );
}

add_action( 'admin_notices', function () {
	if ( wcfc_has_woocommerce() || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	echo '<div class="notice notice-error"><p>';
	echo esc_html__( 'WC Filter Configurator requires WooCommerce to be installed and active.', 'wc-filter-configurator' );
	echo '</p></div>';
} );

add_action( 'plugins_loaded', function () {
	load_plugin_textdomain( 'wc-filter-configurator', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
} );

/**
 * On activation, seed the schema version so migrations do not run against a
 * fresh install, and make sure a cache version exists.
 */
register_activation_hook( __FILE__, function () {
	if ( false === get_option( 'wcfc_db_ver' ) ) {
		update_option( 'wcfc_db_ver', WCFC_DB_VERSION, false );
	}
	wcfc_cache_version();
} );
