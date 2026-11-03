<?php
/**
 * Cart Page front end: the active cart layout replaces WooCommerce's cart (classic cart template or the
 * Cart block). [shopglut_cart id="N"] shows a chosen layout anywhere. AJAX quantity, remove and coupon
 * actions ported from CartPage Glut.
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Cart_Page class.
 */
class ShopGlut_Cart_Page {

	const NONCE = 'shopglut_cart_action';

	/**
	 * Module folder and URL.
	 *
	 * @var string
	 */
	private $dir;

	/**
	 * Module URL.
	 *
	 * @var string
	 */
	private $url;

	/**
	 * Constructor.
	 *
	 * @param string $dir Module folder.
	 * @param string $url Module URL.
	 */
	public function __construct( $dir, $url ) {
		$this->dir = $dir;
		$this->url = $url;

		add_filter( 'wc_get_template', array( $this, 'classic_template' ), 20, 2 );
		add_filter( 'render_block_woocommerce/cart', array( $this, 'block_cart' ), 20 );
		foreach ( array( 'shopglut_cart', 'cartpageglut_cart_page' ) as $tag ) {
			add_shortcode( $tag, array( $this, 'shortcode' ) );
		}

		foreach ( array( 'update_cart_quantity', 'remove_cart_item', 'apply_coupon', 'remove_coupon' ) as $action ) {
			add_action( 'wp_ajax_shopglut_' . $action, array( $this, 'ajax_' . $action ) );
			add_action( 'wp_ajax_nopriv_shopglut_' . $action, array( $this, 'ajax_' . $action ) );
		}
	}

	/**
	 * Active cart layout.
	 *
	 * @return array|null
	 */
	private static function active_layout() {
		return shopglut_get_active_layout( 'cart-page' );
	}

	/**
	 * Template folder of a layout (falls back to template 1).
	 *
	 * @param string $template Template ID.
	 * @return array dir, number
	 */
	private function template_info( $template ) {
		$template = shopglut_sanitize_template( $template );
		$n        = (int) preg_replace( '/\D/', '', $template );
		if ( $n < 1 || ! is_readable( $this->dir . 'frontend/template' . $n . '/class-markup.php' ) ) {
			$n = 1;
		}
		return array( $this->dir . 'frontend/template' . $n . '/', $n );
	}

	/**
	 * Cart HTML of a layout.
	 *
	 * @param array $layout Layout row.
	 * @return string
	 */
	public function render( $layout ) {
		list( $dir, $n ) = $this->template_info( $layout['template'] );
		require_once $dir . 'class-style.php';
		$this->enqueue( $n );

		$style = 'ShopGlut_Cart_Template' . $n . '_Style';
		ob_start();
		( new $style() )->dynamicCss( (int) $layout['id'] );
		return (string) ob_get_clean() . $this->markup( $layout );
	}

	/**
	 * Cart markup only (no styles), also sent back after AJAX changes.
	 *
	 * @param array $layout Layout row.
	 * @return string
	 */
	public function markup( $layout ) {
		list( $dir, $n ) = $this->template_info( $layout['template'] );
		require_once $dir . 'class-markup.php';
		$markup = 'ShopGlut_Cart_Template' . $n . '_Markup';
		ob_start();
		( new $markup() )->layout_render( array( 'layout_id' => (int) $layout['id'] ) );
		return (string) ob_get_clean();
	}

	/**
	 * Load the template's CSS and the cart script.
	 *
	 * @param int $n Template number.
	 * @return void
	 */
	private function enqueue( $n ) {
		wp_enqueue_style( 'shopglut-cart-template' . $n, $this->url . 'frontend/assets/template' . $n . '.css', array(), SHOPGLUT_VERSION );
		wp_enqueue_script( 'shopglut-cart', $this->url . 'frontend/assets/cart.js', array( 'jquery' ), SHOPGLUT_VERSION, true );
		wp_localize_script(
			'shopglut-cart',
			'shopglut_cart_ajax',
			array(
				'ajax_url'     => admin_url( 'admin-ajax.php' ),
				'nonce'        => wp_create_nonce( self::NONCE ),
				'wc_ajax_url'  => class_exists( 'WC_AJAX' ) ? WC_AJAX::get_endpoint( '%%endpoint%%' ) : '',
				'checkout_url' => wc_get_checkout_url(),
				'i18n'         => array(
					'updating'       => __( 'Updating...', 'shopglut' ),
					'removing'       => __( 'Removing...', 'shopglut' ),
					'error'          => __( 'Something went wrong. Please try again.', 'shopglut' ),
					'coupon_applied' => __( 'Coupon applied successfully!', 'shopglut' ),
					'coupon_removed' => __( 'Coupon removed successfully!', 'shopglut' ),
					'invalid_coupon' => __( 'Invalid coupon code.', 'shopglut' ),
					'confirm_remove' => __( 'Are you sure you want to remove this item?', 'shopglut' ),
				),
			)
		);
	}

	/**
	 * Classic themes: cart and empty cart templates.
	 *
	 * @param string $template      Template path.
	 * @param string $template_name Template name.
	 * @return string
	 */
	public function classic_template( $template, $template_name ) {
		if ( ! in_array( $template_name, array( 'cart/cart.php', 'cart/cart-empty.php' ), true ) || ! self::active_layout() ) {
			return $template;
		}
		return $this->dir . 'frontend/cart.php';
	}

	/**
	 * Classic template body (called from frontend/cart.php).
	 *
	 * @return void
	 */
	public function classic_output() {
		$layout = self::active_layout();
		if ( $layout ) {
			echo $this->render( $layout ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the template.
		}
	}

	/**
	 * Block themes: replace the Cart block.
	 *
	 * @param string $html Block HTML.
	 * @return string
	 */
	public function block_cart( $html ) {
		$layout = self::active_layout();
		return $layout ? $this->render( $layout ) : $html;
	}

	/**
	 * [shopglut_cart id="N"]: a chosen cart layout (the active one when no ID is given).
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	public function shortcode( $atts ) {
		$atts   = shortcode_atts( array( 'id' => 0 ), $atts );
		$layout = $atts['id'] ? ShopGlut_Layouts::get( absint( $atts['id'] ) ) : self::active_layout();
		if ( ! $layout || 'cart-page' !== $layout['module'] || ! WC()->cart ) {
			return '';
		}
		return $this->render( $layout );
	}

	/* ---------------------------------------------------------------------
	 * AJAX (ported from CartPage Glut)
	 * ------------------------------------------------------------------- */

	/**
	 * Verify the request; send an error and stop when it fails.
	 *
	 * @return void
	 */
	private static function verify() {
		if ( ! check_ajax_referer( self::NONCE, 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed. Please reload the page.', 'shopglut' ) ) );
		}
		if ( ! WC()->cart ) {
			wp_send_json_error( array( 'message' => __( 'The cart is not available.', 'shopglut' ) ) );
		}
	}

	/**
	 * Cart totals for the response.
	 *
	 * @return array
	 */
	private function totals() {
		$cart = WC()->cart;
		$cart->calculate_totals();
		$data = array(
			'cart_subtotal' => wc_price( $cart->get_subtotal() ),
			'cart_total'    => $cart->get_total(),
			'cart_hash'     => $cart->get_cart_hash(),
			'cart_count'    => $cart->get_cart_contents_count(),
		);
		// Fresh cart HTML for the layout the request came from.
		$id     = isset( $_POST['layout_id'] ) ? absint( wp_unslash( $_POST['layout_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified in verify().
		$layout = $id ? ShopGlut_Layouts::get( $id ) : null;
		if ( $layout && 'cart-page' === $layout['module'] ) {
			$data['layout_id'] = $id;
			$data['html']      = $this->markup( $layout );
		}
		return $data;
	}

	/**
	 * Change an item's quantity.
	 *
	 * @return void
	 */
	public function ajax_update_cart_quantity() {
		self::verify();
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- verified in verify().
		$key      = isset( $_POST['cart_item_key'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_item_key'] ) ) : '';
		$quantity = isset( $_POST['quantity'] ) ? absint( wp_unslash( $_POST['quantity'] ) ) : 0;
		// phpcs:enable
		if ( '' === $key || $quantity < 1 || ! WC()->cart->get_cart_item( $key ) ) {
			wp_send_json_error( array( 'message' => __( 'Cart item not found.', 'shopglut' ) ) );
		}
		WC()->cart->set_quantity( $key, $quantity );
		$totals = $this->totals();
		$item   = WC()->cart->get_cart_item( $key );
		wp_send_json_success(
			array_merge(
				$totals,
				array(
					'message'    => __( 'Cart updated.', 'shopglut' ),
					'line_total' => $item ? WC()->cart->get_product_subtotal( $item['data'], $item['quantity'] ) : '',
				)
			)
		);
	}

	/**
	 * Remove an item.
	 *
	 * @return void
	 */
	public function ajax_remove_cart_item() {
		self::verify();
		$key = isset( $_POST['cart_item_key'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_item_key'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified in verify().
		if ( '' === $key || ! WC()->cart->remove_cart_item( $key ) ) {
			wp_send_json_error( array( 'message' => __( 'Failed to remove item.', 'shopglut' ) ) );
		}
		wp_send_json_success( array_merge( $this->totals(), array( 'message' => __( 'Item removed.', 'shopglut' ) ) ) );
	}

	/**
	 * Apply a coupon.
	 *
	 * @return void
	 */
	public function ajax_apply_coupon() {
		self::verify();
		$code = isset( $_POST['coupon_code'] ) ? wc_format_coupon_code( sanitize_text_field( wp_unslash( $_POST['coupon_code'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified in verify().
		if ( '' === $code ) {
			wp_send_json_error( array( 'message' => __( 'Please enter a coupon code.', 'shopglut' ) ) );
		}
		if ( ! WC()->cart->apply_coupon( $code ) ) {
			$notices = wc_get_notices( 'error' );
			wc_clear_notices();
			wp_send_json_error( array( 'message' => $notices ? wp_strip_all_tags( $notices[0]['notice'] ) : __( 'Invalid coupon code.', 'shopglut' ) ) );
		}
		wc_clear_notices();
		wp_send_json_success( array_merge( $this->totals(), array( 'message' => __( 'Coupon applied successfully!', 'shopglut' ) ) ) );
	}

	/**
	 * Remove a coupon.
	 *
	 * @return void
	 */
	public function ajax_remove_coupon() {
		self::verify();
		$code = isset( $_POST['coupon_code'] ) ? wc_format_coupon_code( sanitize_text_field( wp_unslash( $_POST['coupon_code'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified in verify().
		if ( '' === $code || ! WC()->cart->remove_coupon( $code ) ) {
			wp_send_json_error( array( 'message' => __( 'Failed to remove coupon.', 'shopglut' ) ) );
		}
		wc_clear_notices();
		wp_send_json_success( array_merge( $this->totals(), array( 'message' => __( 'Coupon removed successfully!', 'shopglut' ) ) ) );
	}
}
