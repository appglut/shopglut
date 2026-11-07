<?php
/**
 * Sale Countdown & Low Stock: a live countdown on products whose sale has an end date, and an "only X left"
 * message when stock is low. Uses the product's real sale dates and stock, nothing made up.
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Module_Urgency class.
 */
class ShopGlut_Module_Urgency extends ShopGlut_Module {

	public function id() {
		return 'urgency';
	}

	public function info() {
		return array(
			'title'        => __( 'Sale Countdown & Low Stock', 'shopglut' ),
			'description'  => __( 'A live countdown for scheduled sales and an "only X left" message, from the real sale dates and stock.', 'shopglut' ),
			'icon'         => 'faBullhorn',
			'category'     => 'engagement',
			'order'        => 110,
			'features'     => array(
				__( 'Countdown to the sale end date', 'shopglut' ),
				__( 'Low stock message with your threshold', 'shopglut' ),
				__( 'On product pages and shop cards', 'shopglut' ),
			),
		);
	}

	public function settings_sections() {
		return $this->schema( $this->dir . 'settings/options.php', 'options' );
	}

	private function opt( $key, $default = '' ) {
		return shopglut_get_module_setting( 'urgency', $key, $default );
	}

	public function boot() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		add_action( 'woocommerce_single_product_summary', array( $this, 'single' ), 11 );
		if ( $this->opt( 'loop_countdown', 0 ) ) {
			add_action( 'woocommerce_after_shop_loop_item_title', array( $this, 'loop' ), 15 );
		}
		add_action( 'wp_footer', array( $this, 'script' ) );
	}

	/**
	 * Sale end timestamp, or 0.
	 *
	 * @param WC_Product $product Product.
	 * @return int
	 */
	public function sale_end( $product ) {
		$ends = array();
		$list = $product->is_type( 'variable' ) ? array_filter( array_map( 'wc_get_product', $product->get_children() ) ) : array( $product );
		foreach ( $list as $p ) {
			$to = $p->get_date_on_sale_to();
			if ( $p->is_on_sale() && $to && $to->getTimestamp() > time() ) {
				$ends[] = $to->getTimestamp();
			}
		}
		return $ends ? min( $ends ) : 0;
	}

	/**
	 * Countdown HTML.
	 *
	 * @param int $end Timestamp.
	 * @return string
	 */
	private function countdown( $end ) {
		return '<p class="sg-countdown" data-end="' . esc_attr( $end ) . '">' . esc_html( $this->opt( 'countdown_text', __( 'Sale ends in', 'shopglut' ) ) ) . ' <strong class="sg-countdown-time"></strong></p>';
	}

	/**
	 * Low stock message, or ''.
	 *
	 * @param WC_Product $product Product.
	 * @return string
	 */
	public function low_stock( $product ) {
		if ( ! $this->opt( 'low_stock', 1 ) || ! $product->managing_stock() || ! $product->is_in_stock() ) {
			return '';
		}
		$stock = (int) $product->get_stock_quantity();
		if ( $stock <= 0 || $stock > (int) $this->opt( 'low_stock_at', 5 ) ) {
			return '';
		}
		$text = str_replace( '{stock}', (string) $stock, (string) $this->opt( 'low_stock_text', __( 'Only {stock} left in stock, order soon!', 'shopglut' ) ) );
		return '<p class="sg-low-stock">' . esc_html( $text ) . '</p>';
	}

	public function single() {
		global $product;
		if ( ! $product ) {
			return;
		}
		$end = $this->opt( 'countdown', 1 ) ? $this->sale_end( $product ) : 0;
		echo wp_kses_post( ( $end ? $this->countdown( $end ) : '' ) . $this->low_stock( $product ) );
	}

	public function loop() {
		global $product;
		$end = $product ? $this->sale_end( $product ) : 0;
		if ( $end ) {
			echo wp_kses_post( $this->countdown( $end ) );
		}
	}

	public function script() {
		if ( ! is_woocommerce() ) {
			return;
		}
		echo '<style>.sg-countdown{color:#b42318;margin:.4em 0}.sg-countdown-time{font-variant-numeric:tabular-nums}.sg-low-stock{color:#b54708;font-weight:600;margin:.4em 0}</style>';
		echo '<script>(function(){var els=document.querySelectorAll(".sg-countdown");if(!els.length)return;function p(n){return n<10?"0"+n:n}function tick(){var now=Date.now()/1000;els.forEach(function(el){var s=Math.max(0,Math.floor(el.dataset.end-now)),d=Math.floor(s/86400),h=Math.floor(s%86400/3600),m=Math.floor(s%3600/60),x=s%60;el.querySelector(".sg-countdown-time").textContent=(d?d+"d ":"")+p(h)+":"+p(m)+":"+p(x);if(!s)el.style.display="none"})}tick();setInterval(tick,1000)})();</script>';
	}
}

return 'ShopGlut_Module_Urgency';
