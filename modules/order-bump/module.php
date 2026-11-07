<?php
/**
 * Order Bump: a one-click offer box above "Place order" on the classic checkout ("Yes! Add this to my order"),
 * with an optional checkout-only discount. Ticking it adds the product to the cart and refreshes the totals;
 * unticking removes it. The discounted price only applies to the line added from the bump.
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Module_Order_Bump class.
 */
class ShopGlut_Module_Order_Bump extends ShopGlut_Module {

	public function id() {
		return 'order-bump';
	}

	public function info() {
		return array(
			'title'       => __( 'Order Bump', 'shopglut' ),
			'description' => __( 'A one-click "add this to my order" offer above Place order, with an optional checkout-only discount.', 'shopglut' ),
			'icon'        => 'faCartShopping',
			'category'    => 'cart',
			'order'       => 116,
			'default_on'  => false,
			'features'    => array(
				__( 'One tick adds the offer to the order', 'shopglut' ),
				__( 'Checkout-only discount', 'shopglut' ),
				__( 'Minimum cart total', 'shopglut' ),
				__( 'Classic checkout', 'shopglut' ),
			),
		);
	}

	public function settings_sections() {
		return $this->schema( $this->dir . 'settings/options.php', 'options' );
	}

	private function opt( $key, $default = '' ) {
		return shopglut_get_module_setting( 'order-bump', $key, $default );
	}

	public function boot() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		add_action( 'woocommerce_review_order_before_submit', array( $this, 'box' ), 5 );
		add_action( 'woocommerce_checkout_update_order_review', array( $this, 'apply' ) );
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'price' ), 30 );
	}

	/**
	 * The offer product, or null.
	 *
	 * @return WC_Product|null
	 */
	public function offer() {
		$p = wc_get_product( (int) $this->opt( 'product_id', 0 ) );
		return ( $p && $p->is_type( 'simple' ) && $p->is_purchasable() && $p->is_in_stock() ) ? $p : null;
	}

	/**
	 * Cart key of the bump line, or ''.
	 */
	public function bump_key() {
		foreach ( WC()->cart ? WC()->cart->get_cart() : array() as $key => $item ) {
			if ( ! empty( $item['sg_bump'] ) ) {
				return $key;
			}
		}
		return '';
	}

	public function offer_price( $p ) {
		$d = max( 0, min( 90, (float) $this->opt( 'discount', 0 ) ) );
		return round( (float) $p->get_price() * ( 1 - $d / 100 ), wc_get_price_decimals() );
	}

	public function box() {
		$p = $this->offer();
		if ( ! $p || ( (float) $this->opt( 'min_total', 0 ) > 0 && (float) WC()->cart->get_subtotal() < (float) $this->opt( 'min_total', 0 ) ) ) {
			return;
		}
		$in   = '' !== $this->bump_key();
		$text = strtr( (string) $this->opt( 'text', 'Only at checkout: get {product} for {price}.' ), array( '{product}' => $p->get_name(), '{price}' => wp_strip_all_tags( wc_price( $this->offer_price( $p ) ) ) ) );
		echo '<div class="sg-bump"><label><input type="checkbox" name="sg_bump" value="1"' . checked( $in, true, false ) . ' onchange="jQuery(document.body).trigger(\'update_checkout\')"> <strong>' . esc_html( $this->opt( 'headline', __( 'Yes! Add this to my order', 'shopglut' ) ) ) . '</strong></label>';
		echo '<div class="sg-bump-body">' . wp_kses_post( $p->get_image( 'thumbnail' ) ) . '<p>' . esc_html( $text ) . '</p></div></div>';
		echo '<style>.sg-bump{border:2px dashed #d97706;background:#fffbeb;border-radius:10px;padding:12px;margin:0 0 14px}.sg-bump-body{display:flex;gap:10px;align-items:center;margin-top:6px}.sg-bump-body img{width:54px;height:54px;object-fit:cover}.sg-bump-body p{margin:0}</style>';
	}

	/**
	 * Add or remove the bump when the checkout refreshes.
	 *
	 * @param string $posted Serialized checkout form.
	 */
	public function apply( $posted ) {
		parse_str( (string) $posted, $data );
		$want = ! empty( $data['sg_bump'] );
		$key  = $this->bump_key();
		$p    = $this->offer();
		if ( $want && '' === $key && $p ) {
			WC()->cart->add_to_cart( $p->get_id(), 1, 0, array(), array( 'sg_bump' => 1 ) );
		} elseif ( ! $want && '' !== $key ) {
			WC()->cart->remove_cart_item( $key );
		}
	}

	public function price( $cart ) {
		foreach ( $cart->get_cart() as $item ) {
			if ( ! empty( $item['sg_bump'] ) && $item['data'] instanceof WC_Product ) {
				$item['data']->set_price( $this->offer_price( wc_get_product( $item['data']->get_id() ) ) );
			}
		}
	}
}

return 'ShopGlut_Module_Order_Bump';
