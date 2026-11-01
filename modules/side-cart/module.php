<?php
/**
 * Side Cart: sliding mini cart with a floating cart button.
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Module_Side_Cart class.
 */
class ShopGlut_Module_Side_Cart extends ShopGlut_Module {

	/**
	 * Module ID.
	 *
	 * @return string
	 */
	public function id() {
		return 'side-cart';
	}

	/**
	 * Module info.
	 *
	 * @return array
	 */
	public function info() {
		return array(
			'title'        => __( 'Side Cart', 'shopglut' ),
			'description'  => __( 'AJAX side cart that slides in on add to cart, with a floating cart button and quantity controls.', 'shopglut' ),
			'icon'         => 'faBagShopping',
			'category'     => 'cart',
			'order'        => 100,
			'features'     => array(
				__( 'Floating cart icon with item count', 'shopglut' ),
				__( 'Slide, fade or scale animation from left or right', 'shopglut' ),
				__( 'Auto-open on add to cart, +/- quantity, product images', 'shopglut' ),
				__( 'Menu cart item, shortcode and cart sharing by email', 'shopglut' ),
			),
			'pro_features' => array(
				__( 'Free shipping progress bar', 'shopglut' ),
				__( 'Recommended products and upsells in the cart', 'shopglut' ),
				__( 'More cart templates, icons and animations', 'shopglut' ),
				__( 'Cart abandonment recovery', 'shopglut' ),
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
}

return 'ShopGlut_Module_Side_Cart';
