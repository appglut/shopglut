<?php
/**
 * Thank You Page Builder (order received page).
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Module_Order_Complete class.
 */
class ShopGlut_Module_Order_Complete extends ShopGlut_Module {

	/**
	 * Module ID.
	 *
	 * @return string
	 */
	public function id() {
		return 'order-complete';
	}

	/**
	 * Module info.
	 *
	 * @return array
	 */
	public function info() {
		return array(
			'title'        => __( 'Thank You Page Builder', 'shopglut' ),
			'description'  => __( 'Design the order received page: header, order summary, items, addresses and next-step buttons.', 'shopglut' ),
			'icon'         => 'faCircleCheck',
			'category'     => 'cart',
			'order'        => 50,
			'layouts'      => true,
			'layout_label' => __( 'Layout', 'shopglut' ),
			'features'     => array(
				__( 'Thank you template with full styling', 'shopglut' ),
				__( 'Order summary, items table, addresses and action buttons', 'shopglut' ),
			),
			'pro_features' => array(
				__( 'More thank you templates', 'shopglut' ),
				__( 'Per-product and per-payment-method thank you pages', 'shopglut' ),
				__( 'Upsell products and coupon for the next order', 'shopglut' ),
			),
		);
	}

	/**
	 * Templates.
	 *
	 * @return array
	 */
	public function templates() {
		return $this->numbered_templates( 1, 1 );
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
	 * Front end: the order received page uses the active layout.
	 *
	 * @return void
	 */
	public function boot() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		require_once $this->dir . 'frontend/class-shopglut-order-complete.php';
		$GLOBALS['shopglut_order_complete'] = new ShopGlut_Order_Complete( $this->dir );
	}
}

return 'ShopGlut_Module_Order_Complete';
