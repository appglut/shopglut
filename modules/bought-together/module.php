<?php
/**
 * Frequently Bought Together: on the product page, the product plus a few products that were bought with it
 * in past orders (or its upsells / cross-sells), with tick boxes, the total price and one button that adds
 * the ticked products to the cart.
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Module_Bought_Together class.
 */
class ShopGlut_Module_Bought_Together extends ShopGlut_Module {

	public function id() {
		return 'bought-together';
	}

	public function info() {
		return array(
			'title'       => __( 'Frequently Bought Together', 'shopglut' ),
			'description' => __( 'Show products often bought with this one and add them all to the cart with one click.', 'shopglut' ),
			'icon'        => 'faCartShopping',
			'category'    => 'cart',
			'order'       => 115,
			'features'    => array(
				__( 'Suggestions from real past orders, upsells or cross-sells', 'shopglut' ),
				__( 'Tick boxes and live total', 'shopglut' ),
				__( 'One button adds all ticked products', 'shopglut' ),
			),
		);
	}

	public function settings_sections() {
		return $this->schema( $this->dir . 'settings/options.php', 'options' );
	}

	private function opt( $key, $default = '' ) {
		return shopglut_get_module_setting( 'bought-together', $key, $default );
	}

	public function boot() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		add_action( 'woocommerce_after_single_product_summary', array( $this, 'show' ), 8 );
		add_action( 'wp_loaded', array( $this, 'add_to_cart' ), 25 );
	}

	/**
	 * Products bought in the same orders as this one (cached a day).
	 *
	 * @param int $product_id Product id.
	 * @param int $limit      Count.
	 * @return int[]
	 */
	public function from_orders( $product_id, $limit ) {
		$key = 'sg_fbt_' . $product_id;
		$ids = get_transient( $key );
		if ( false === $ids ) {
			global $wpdb;
			$lookup = $wpdb->prefix . 'wc_order_product_lookup';
			// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from $wpdb->prefix.
			$ids = $wpdb->get_col( $wpdb->prepare( "SELECT b.product_id FROM {$lookup} a JOIN {$lookup} b ON a.order_id = b.order_id AND b.product_id <> a.product_id WHERE a.product_id = %d GROUP BY b.product_id ORDER BY COUNT(*) DESC LIMIT 10", $product_id ) );
			// phpcs:enable
			$ids = array_map( 'intval', (array) $ids );
			set_transient( $key, $ids, DAY_IN_SECONDS );
		}
		return array_slice( $ids, 0, $limit );
	}

	/**
	 * Suggested products that can be bought (simple, in stock, visible).
	 *
	 * @param WC_Product $product Product.
	 * @return WC_Product[]
	 */
	public function suggestions( $product ) {
		$limit  = max( 1, min( 5, (int) $this->opt( 'count', 2 ) ) );
		$source = $this->opt( 'source', 'orders' );
		$ids    = array();
		if ( 'orders' === $source ) {
			$ids = $this->from_orders( $product->get_id(), 10 );
		}
		if ( 'cross' === $source ) {
			$ids = $product->get_cross_sell_ids();
		}
		$ids = array_merge( $ids, $product->get_upsell_ids() );
		$out = array();
		foreach ( array_unique( $ids ) as $id ) {
			$p = wc_get_product( $id );
			if ( $p && $p->is_type( 'simple' ) && $p->is_purchasable() && $p->is_in_stock() && $p->is_visible() && $p->get_id() !== $product->get_id() ) {
				$out[] = $p;
			}
			if ( count( $out ) >= $limit ) {
				break;
			}
		}
		return $out;
	}

	public function show() {
		global $product;
		if ( ! $product || ! $product->is_type( 'simple' ) || ! $product->is_purchasable() ) {
			return;
		}
		$items = $this->suggestions( $product );
		if ( ! $items ) {
			return;
		}
		array_unshift( $items, $product );
		$checked = $this->opt( 'checked', 1 ) ? ' checked' : '';
		echo '<section class="sg-fbt"><h2>' . esc_html( $this->opt( 'title', __( 'Frequently bought together', 'shopglut' ) ) ) . '</h2><form method="post" class="sg-fbt-form">' . wp_nonce_field( 'sg_fbt', 'sg_fbt_nonce', true, false ) . '<ul>';
		foreach ( $items as $i => $p ) {
			$price = (float) wc_get_price_to_display( $p );
			echo '<li><label><input type="checkbox" name="sg_fbt[]" value="' . esc_attr( $p->get_id() ) . '" data-price="' . esc_attr( $price ) . '"' . ( 0 === $i ? ' checked' : $checked ) . '> ' . wp_kses_post( $p->get_image( 'thumbnail' ) ) . ' <span>' . ( 0 === $i ? '<strong>' . esc_html__( 'This item:', 'shopglut' ) . '</strong> ' : '' ) . esc_html( $p->get_name() ) . '</span> <span class="sg-fbt-price">' . wp_kses_post( $p->get_price_html() ) . '</span></label></li>';
		}
		$fmt = array( 'symbol' => html_entity_decode( get_woocommerce_currency_symbol() ), 'pos' => get_option( 'woocommerce_currency_pos' ), 'dec' => wc_get_price_decimals() );
		echo '</ul><p class="sg-fbt-total">' . esc_html__( 'Total:', 'shopglut' ) . ' <strong data-fmt="' . esc_attr( wp_json_encode( $fmt ) ) . '"></strong></p><button type="submit" class="button alt">' . esc_html( $this->opt( 'button', __( 'Add selected to cart', 'shopglut' ) ) ) . '</button></form></section>';
		echo '<style>.sg-fbt{margin:2em 0;clear:both}.sg-fbt ul{list-style:none;margin:0 0 1em;padding:0}.sg-fbt li{padding:6px 0}.sg-fbt label{display:flex;align-items:center;gap:10px;cursor:pointer}.sg-fbt img{width:48px;height:48px;object-fit:cover}.sg-fbt-price{margin-left:auto}</style>';
		echo '<script>(function(){var f=document.querySelector(".sg-fbt-form");if(!f)return;var t=f.querySelector(".sg-fbt-total strong"),c=JSON.parse(t.dataset.fmt);function u(){var s=0;f.querySelectorAll("input[type=checkbox]:checked").forEach(function(i){s+=parseFloat(i.dataset.price)||0});var n=s.toFixed(c.dec);t.textContent=c.pos.indexOf("left")===0?c.symbol+(c.pos==="left_space"?" ":"")+n:n+(c.pos==="right_space"?" ":"")+c.symbol}f.addEventListener("change",u);u()})();</script>';
	}

	/**
	 * Add the ticked products to the cart, then go to the cart.
	 *
	 * @return int Added count.
	 */
	public function add_to_cart() {
		if ( empty( $_POST['sg_fbt'] ) || ! isset( $_POST['sg_fbt_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sg_fbt_nonce'] ) ), 'sg_fbt' ) || ! WC()->cart ) {
			return 0;
		}
		$added = 0;
		foreach ( array_map( 'absint', (array) wp_unslash( $_POST['sg_fbt'] ) ) as $id ) {
			$p = wc_get_product( $id );
			if ( $p && $p->is_type( 'simple' ) && $p->is_purchasable() && WC()->cart->add_to_cart( $id, 1 ) ) {
				++$added;
			}
		}
		if ( $added && ! defined( 'SHOPGLUT_TESTING' ) ) {
			wp_safe_redirect( wc_get_cart_url() );
			exit;
		}
		return $added;
	}
}

return 'ShopGlut_Module_Bought_Together';
