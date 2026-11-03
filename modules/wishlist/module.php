<?php
/**
 * Wishlist.
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Module_Wishlist class.
 */
class ShopGlut_Module_Wishlist extends ShopGlut_Module {

	/**
	 * Module ID.
	 *
	 * @return string
	 */
	public function id() {
		return 'wishlist';
	}

	/**
	 * Module info.
	 *
	 * @return array
	 */
	public function info() {
		return array(
			'title'        => __( 'Wishlist', 'shopglut' ),
			'description'  => __( 'Add-to-wishlist buttons, wishlist page, guest lists, sharing, counter and popular products.', 'shopglut' ),
			'icon'         => 'faHeart',
			'category'     => 'engagement',
			'order'        => 90,
			'features'     => array(
				__( 'Buttons on product, shop and archive pages', 'shopglut' ),
				__( 'Guest wishlists (cookie) merged on login', 'shopglut' ),
				__( 'Share by link, social networks and QR code', 'shopglut' ),
				__( 'Menu counter, shortcodes and widgets', 'shopglut' ),
			),
			'pro_features' => array(
				__( 'Multiple wishlists and move between lists', 'shopglut' ),
				__( 'My Account wishlist tab', 'shopglut' ),
				__( 'Price-drop and back-in-stock emails, SMS', 'shopglut' ),
				__( 'Wishlist PDF export and analytics', 'shopglut' ),
			),
		);
	}

	/**
	 * Settings panel.
	 *
	 * @return array
	 */
	public function settings_sections() {
		return $this->schema( $this->dir . 'settings/options.php', 'options' );
	}

	/**
	 * Front end: buttons, wishlist page, counter, sharing.
	 *
	 * @return void
	 */
	public function boot() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		require_once $this->dir . 'frontend/class-shopglut-wishlist.php';
		$GLOBALS['shopglut_wishlist'] = new ShopGlut_Wishlist( $this->url );
	}
}

return 'ShopGlut_Module_Wishlist';
