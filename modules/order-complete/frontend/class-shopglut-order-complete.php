<?php
/**
 * Thank You page front end: replaces WooCommerce's order received content with the active layout.
 *
 * Classic themes: the checkout/thankyou.php template. Block themes: the Order Confirmation blocks.
 * Payment gateway instructions (woocommerce_thankyou_{gateway}) still run. Failed orders and orders the
 * visitor may not see keep WooCommerce's own page.
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Order_Complete class.
 */
class ShopGlut_Order_Complete {

	/**
	 * Module folder.
	 *
	 * @var string
	 */
	private $dir;

	/**
	 * Order confirmation blocks that the layout replaces (its own sections show the same data).
	 *
	 * @var string[]
	 */
	const REPLACED_BLOCKS = array(
		'woocommerce/order-confirmation-summary',
		'woocommerce/order-confirmation-totals-wrapper',
		'woocommerce/order-confirmation-totals',
		'woocommerce/order-confirmation-billing-wrapper',
		'woocommerce/order-confirmation-shipping-wrapper',
		'woocommerce/order-confirmation-billing-address',
		'woocommerce/order-confirmation-shipping-address',
	);

	/**
	 * Constructor.
	 *
	 * @param string $dir Module folder.
	 */
	public function __construct( $dir ) {
		$this->dir = $dir;
		add_filter( 'wc_get_template', array( $this, 'classic_template' ), 20, 3 );
		add_filter( 'render_block_woocommerce/order-confirmation-status', array( $this, 'block_status' ), 20 );
		foreach ( self::REPLACED_BLOCKS as $name ) {
			add_filter( 'render_block_' . $name, array( $this, 'block_hide' ), 20 );
		}
	}

	/**
	 * Active layout for an order.
	 *
	 * @param WC_Order $order Order.
	 * @return array|null
	 */
	private static function layout( $order ) {
		if ( ! $order instanceof WC_Order || $order->has_status( 'failed' ) ) {
			return null;
		}
		return shopglut_get_active_layout( 'order-complete', array( 'order_id' => $order->get_id() ) );
	}

	/**
	 * Order of the current order received page, when the visitor may see it.
	 *
	 * @return WC_Order|null
	 */
	private static function current_order() {
		if ( ! function_exists( 'is_wc_endpoint_url' ) || ! is_wc_endpoint_url( 'order-received' ) ) {
			return null;
		}
		$order = wc_get_order( absint( get_query_var( 'order-received' ) ) );
		if ( ! $order ) {
			return null;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- the order key in the URL is the access check, as in WooCommerce.
		$key = isset( $_GET['key'] ) ? wc_clean( wp_unslash( $_GET['key'] ) ) : '';
		if ( ! hash_equals( $order->get_order_key(), (string) $key ) ) {
			return null;
		}
		$customer = $order->get_customer_id();
		if ( $customer && get_current_user_id() !== $customer && ! current_user_can( 'edit_shop_orders' ) ) {
			return null;
		}
		return $order;
	}

	/**
	 * The layout's HTML (styles + markup) for an order.
	 *
	 * @param WC_Order $order  Order.
	 * @param array    $layout Layout.
	 * @return string
	 */
	public function render( $order, $layout ) {
		$template = shopglut_sanitize_template( $layout['template'] );
		$dir      = $this->dir . 'frontend/' . $template . '/';
		if ( ! is_readable( $dir . 'class-markup.php' ) ) {
			$dir = $this->dir . 'frontend/template1/';
		}
		require_once $dir . 'class-markup.php';
		require_once $dir . 'class-style.php';

		ob_start();
		( new ShopGlut_Order_Complete_Template1_Style() )->dynamicCss( (int) $layout['id'] );
		( new ShopGlut_Order_Complete_Template1_Markup() )->layout_render(
			array(
				'layout_id' => (int) $layout['id'],
				'order'     => $order,
			)
		);
		return (string) ob_get_clean();
	}

	/**
	 * Classic themes: use our template for checkout/thankyou.php.
	 *
	 * @param string $template      Template path.
	 * @param string $template_name Template name.
	 * @param array  $args          Template args.
	 * @return string
	 */
	public function classic_template( $template, $template_name, $args = array() ) {
		if ( 'checkout/thankyou.php' !== $template_name ) {
			return $template;
		}
		$order = isset( $args['order'] ) ? $args['order'] : null;
		if ( ! self::layout( $order ) ) {
			return $template;
		}
		return $this->dir . 'frontend/thankyou.php';
	}

	/**
	 * Classic template body (called from frontend/thankyou.php).
	 *
	 * @param WC_Order $order Order.
	 * @return void
	 */
	public function classic_output( $order ) {
		$layout = self::layout( $order );
		if ( ! $layout ) {
			return;
		}
		echo $this->render( $order, $layout ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the template.

		// Gateways print their instructions here (bank details, etc.). The default order details table is
		// already part of the layout, so it is left out.
		remove_action( 'woocommerce_thankyou', 'woocommerce_order_details_table', 10 );
		do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() );
		do_action( 'woocommerce_thankyou', $order->get_id() );
	}

	/**
	 * Block themes: the status block becomes the layout.
	 *
	 * @param string $html Block HTML.
	 * @return string
	 */
	public function block_status( $html ) {
		$order  = self::current_order();
		$layout = $order ? self::layout( $order ) : null;
		return $layout ? $this->render( $order, $layout ) : $html;
	}

	/**
	 * Block themes: hide blocks the layout already covers.
	 *
	 * @param string $html Block HTML.
	 * @return string
	 */
	public function block_hide( $html ) {
		$order = self::current_order();
		return ( $order && self::layout( $order ) ) ? '' : $html;
	}
}
