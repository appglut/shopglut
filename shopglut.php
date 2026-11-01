<?php
/**
 * Plugin Name: ShopGlut – Product, Shop, Filter, Swatches, Wishlist & More Builder for WooCommerce
 * Plugin URI:  https://wordpress.org/plugins/shopglut/
 * Description: All-in-one WooCommerce builder: product page, shop & archive, cart, checkout fields, thank you page, swatches, badges, filters, wishlist and side cart, managed from one modern admin.
 * Version:     2.0.0
 * Author:      AppGlut
 * Author URI:  https://www.appglut.com
 * License:     GPLv2 or later
 * Text Domain: shopglut
 * Domain Path: /languages
 * Requires at least: 6.1
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

define( 'SHOPGLUT_VERSION', '2.0.0' );
define( 'SHOPGLUT_FILE', __FILE__ );
define( 'SHOPGLUT_BASENAME', plugin_basename( __FILE__ ) );
define( 'SHOPGLUT_PATH', plugin_dir_path( __FILE__ ) );
define( 'SHOPGLUT_URL', plugin_dir_url( __FILE__ ) );
define( 'SHOPGLUT_DB_VERSION', '1' );

/** Version of the admin extension API (window.shopglut) that ShopGlut Pro and other add-ons build on. */
define( 'SHOPGLUT_EXTEND_API', 1 );

define( 'SHOPGLUT_UPGRADE_URL', 'https://www.appglut.com/shopglut-pro/' );

// Options framework: the first loaded copy of AppGlut wins, so a newer copy in another plugin is fine too.
require_once SHOPGLUT_PATH . 'libraries/appglut-framework/appglut-framework.php';

require_once SHOPGLUT_PATH . 'includes/functions.php';
require_once SHOPGLUT_PATH . 'includes/class-shopglut-db.php';
require_once SHOPGLUT_PATH . 'includes/class-shopglut-module.php';
require_once SHOPGLUT_PATH . 'includes/class-shopglut-modules.php';
require_once SHOPGLUT_PATH . 'includes/class-shopglut-layouts.php';
require_once SHOPGLUT_PATH . 'includes/class-shopglut-schema.php';
require_once SHOPGLUT_PATH . 'includes/class-shopglut-panels.php';
require_once SHOPGLUT_PATH . 'includes/class-shopglut-settings.php';
require_once SHOPGLUT_PATH . 'includes/class-shopglut-frontend.php';
require_once SHOPGLUT_PATH . 'includes/class-shopglut-admin.php';
require_once SHOPGLUT_PATH . 'includes/class-shopglut-ajax.php';

register_activation_hook( __FILE__, array( 'ShopGlut_DB', 'install' ) );

// On init: modules use translated strings, which WordPress loads from init on.
add_action( 'init', 'shopglut_init', 1 );

/**
 * Start ShopGlut. Modules register on 'shopglut_register_modules' (ShopGlut Pro adds its own there).
 *
 * @return void
 */
function shopglut_init() {
	ShopGlut_DB::maybe_upgrade();

	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'shopglut_woocommerce_missing_notice' );
		return;
	}

	ShopGlut_Modules::instance()->load();
	ShopGlut_Settings::instance();
	ShopGlut_Panels::instance();
	ShopGlut_Frontend::instance();

	if ( is_admin() ) {
		ShopGlut_Admin::instance();
		ShopGlut_Ajax::instance();
	}

	/**
	 * ShopGlut and its enabled modules are loaded.
	 *
	 * @param ShopGlut_Modules $modules Module registry.
	 */
	do_action( 'shopglut_loaded', ShopGlut_Modules::instance() );
}

/**
 * Admin notice when WooCommerce is not active.
 *
 * @return void
 */
function shopglut_woocommerce_missing_notice() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	echo '<div class="notice notice-error"><p>' . esc_html__( 'ShopGlut needs WooCommerce. Install and activate WooCommerce to use it.', 'shopglut' ) . '</p></div>';
}
