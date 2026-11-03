<?php
/**
 * Cart Page Builder.
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Module_Cart_Page class.
 */
class ShopGlut_Module_Cart_Page extends ShopGlut_Module {

	/**
	 * Module ID.
	 *
	 * @return string
	 */
	public function id() {
		return 'cart-page';
	}

	/**
	 * Module info.
	 *
	 * @return array
	 */
	public function info() {
		return array(
			'title'        => __( 'Cart Page Builder', 'shopglut' ),
			'description'  => __( 'Custom cart page: product table, quantity controls, coupon box, order summary and trust badges.', 'shopglut' ),
			'icon'         => 'faCartShopping',
			'category'     => 'cart',
			'order'        => 30,
			'layouts'      => true,
			'layout_label' => __( 'Layout', 'shopglut' ),
			'features'     => array(
				__( '3 free cart templates', 'shopglut' ),
				__( 'Product table, +/- quantity, discount code and summary styling', 'shopglut' ),
				__( 'Security badges and continue-shopping link', 'shopglut' ),
			),
			'pro_features' => array(
				__( '2 Pro cart templates', 'shopglut' ),
				__( 'Free shipping progress bar and cart upsells', 'shopglut' ),
			),
		);
	}

	/**
	 * Templates.
	 *
	 * @return array
	 */
	public function templates() {
		return $this->numbered_templates( 5, 3, $this->url . 'images/cart-page-%s.jpg' );
	}

	/**
	 * Layout options.
	 *
	 * @param string $template Template ID.
	 * @param array  $layout   Layout row.
	 * @return array
	 */
	public function layout_sections( $template, $layout ) {
		return $this->template_sections( $template, $layout );
	}

	/**
	 * Front end: the active layout replaces the cart; [shopglut_cart] shortcode; AJAX cart actions.
	 *
	 * @return void
	 */
	public function boot() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		require_once $this->dir . 'frontend/class-shopglut-cart-page.php';
		$GLOBALS['shopglut_cart_page'] = new ShopGlut_Cart_Page( $this->dir, $this->url );
	}
}

return 'ShopGlut_Module_Cart_Page';
