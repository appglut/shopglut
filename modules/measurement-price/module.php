<?php
/**
 * Measurement Price: products sold by area (m², ft²) or length (m, ft). The customer enters width × length
 * (or length); the price is the price per unit × the measurement (with a minimum), set on the cart line, so
 * it works with classic and block cart / checkout. Set it on the product: Product data → General.
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Module_Measurement_Price class.
 */
class ShopGlut_Module_Measurement_Price extends ShopGlut_Module {

	public function id() {
		return 'measurement-price';
	}

	public function info() {
		return array(
			'title'       => __( 'Measurement Price Calculator', 'shopglut' ),
			'description' => __( 'Sell by area or length: customers enter the size and the price is calculated (tiles, fabric, glass, flooring).', 'shopglut' ),
			'icon'        => 'faScaleBalanced',
			'category'    => 'product',
			'order'       => 82,
			'features'    => array(
				__( 'Area (width × length) or length', 'shopglut' ),
				__( 'Price per m², ft², m or ft, with a minimum', 'shopglut' ),
				__( 'Live price on the product page', 'shopglut' ),
				__( 'Size saved on the order line', 'shopglut' ),
			),
		);
	}

	public function boot() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		add_action( 'woocommerce_product_options_pricing', array( $this, 'fields' ) );
		add_action( 'woocommerce_admin_process_product_object', array( $this, 'save' ) );
		add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'input' ) );
		add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'validate' ), 20, 2 );
		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'cart_data' ), 20, 2 );
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'set_prices' ), 25 );
		add_filter( 'woocommerce_get_item_data', array( $this, 'item_data' ), 20, 2 );
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'order_meta' ), 20, 3 );
		add_filter( 'woocommerce_get_price_html', array( $this, 'price_html' ), 20, 2 );
		add_filter( 'woocommerce_product_add_to_cart_url', array( $this, 'loop_url' ), 20, 2 );
		add_filter( 'woocommerce_product_supports', array( $this, 'no_ajax' ), 20, 3 );
	}

	public function mode( $p ) {
		$m = $p instanceof WC_Product && $p->is_type( 'simple' ) ? (string) $p->get_meta( '_sg_measure' ) : '';
		return in_array( $m, array( 'area', 'length' ), true ) ? $m : '';
	}

	private function unit( $p ) {
		$u = (string) $p->get_meta( '_sg_measure_unit' );
		return in_array( $u, array( 'm', 'ft', 'cm', 'in' ), true ) ? $u : 'm';
	}

	public function fields() {
		echo '<div class="options_group">';
		woocommerce_wp_select( array( 'id' => '_sg_measure', 'label' => __( 'Sell by measurement', 'shopglut' ), 'options' => array( '' => __( 'No', 'shopglut' ), 'area' => __( 'Area (width × length)', 'shopglut' ), 'length' => __( 'Length', 'shopglut' ) ) ) );
		woocommerce_wp_select( array( 'id' => '_sg_measure_unit', 'label' => __( 'Unit', 'shopglut' ), 'options' => array( 'm' => 'm', 'ft' => 'ft', 'cm' => 'cm', 'in' => 'in' ) ) );
		woocommerce_wp_text_input( array( 'id' => '_sg_measure_min', 'label' => __( 'Minimum (m² / m)', 'shopglut' ), 'data_type' => 'decimal', 'description' => __( 'The regular price is the price per square unit (area) or per unit (length).', 'shopglut' ), 'desc_tip' => true ) );
		echo '</div>';
	}

	public function save( $product ) {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- WooCommerce checks the product save nonce.
		foreach ( array( '_sg_measure', '_sg_measure_unit' ) as $k ) {
			if ( isset( $_POST[ $k ] ) ) {
				$product->update_meta_data( $k, sanitize_key( wp_unslash( $_POST[ $k ] ) ) );
			}
		}
		if ( isset( $_POST['_sg_measure_min'] ) ) {
			$product->update_meta_data( '_sg_measure_min', wc_format_decimal( wp_unslash( $_POST['_sg_measure_min'] ) ) );
		}
		// phpcs:enable
	}

	/**
	 * Measurement from the request: array( width, length, amount ) or null.
	 */
	private function posted( $p ) {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- add to cart form.
		$l = isset( $_REQUEST['sg_m_length'] ) ? (float) wc_format_decimal( sanitize_text_field( wp_unslash( $_REQUEST['sg_m_length'] ) ) ) : 0;
		$w = isset( $_REQUEST['sg_m_width'] ) ? (float) wc_format_decimal( sanitize_text_field( wp_unslash( $_REQUEST['sg_m_width'] ) ) ) : 0;
		// phpcs:enable
		if ( $l <= 0 || ( 'area' === $this->mode( $p ) && $w <= 0 ) ) {
			return null;
		}
		$amount = 'area' === $this->mode( $p ) ? $w * $l : $l;
		return array( 'width' => $w, 'length' => $l, 'amount' => max( $amount, (float) $p->get_meta( '_sg_measure_min' ) ) );
	}

	public function input() {
		global $product;
		$mode = $this->mode( $product );
		if ( ! $mode ) {
			return;
		}
		$u    = $this->unit( $product );
		$rate = (float) wc_get_price_to_display( $product );
		echo '<div class="sg-measure" data-rate="' . esc_attr( $rate ) . '" data-min="' . esc_attr( (float) $product->get_meta( '_sg_measure_min' ) ) . '" data-mode="' . esc_attr( $mode ) . '">';
		if ( 'area' === $mode ) {
			echo '<label>' . esc_html__( 'Width', 'shopglut' ) . ' (' . esc_html( $u ) . ') <input type="number" step="any" min="0" name="sg_m_width" required></label> ';
		}
		echo '<label>' . esc_html__( 'Length', 'shopglut' ) . ' (' . esc_html( $u ) . ') <input type="number" step="any" min="0" name="sg_m_length" required></label>';
		echo '<p class="sg-measure-total">' . esc_html__( 'Price:', 'shopglut' ) . ' <strong data-cur="' . esc_attr( html_entity_decode( get_woocommerce_currency_symbol() ) ) . '">—</strong></p></div>';
		echo '<style>.sg-measure{margin:0 0 1em}.sg-measure label{display:inline-block;margin:0 10px 6px 0}.sg-measure input{width:110px}</style>';
		echo '<script>(function(){var b=document.querySelector(".sg-measure");if(!b)return;var r=+b.dataset.rate,mn=+b.dataset.min,o=b.querySelector(".sg-measure-total strong");function u(){var l=+(b.querySelector("[name=sg_m_length]").value||0),wEl=b.querySelector("[name=sg_m_width]"),w=wEl?+(wEl.value||0):1;var a=b.dataset.mode==="area"?w*l:l;if(!(a>0)){o.textContent="—";return}a=Math.max(a,mn);o.textContent=o.dataset.cur+(a*r).toFixed(2)+" ("+a.toFixed(2)+(b.dataset.mode==="area"?" sq":"")+")"}b.addEventListener("input",u)})();</script>';
	}

	public function validate( $ok, $product_id ) {
		$p = wc_get_product( $product_id );
		if ( $this->mode( $p ) && ! $this->posted( $p ) ) {
			wc_add_notice( __( 'Please enter the size.', 'shopglut' ), 'error' );
			return false;
		}
		return $ok;
	}

	public function cart_data( $data, $product_id ) {
		$p = wc_get_product( $product_id );
		$m = $this->mode( $p ) ? $this->posted( $p ) : null;
		if ( $m ) {
			$data['sg_measure'] = $m + array( 'unit' => $this->unit( $p ), 'mode' => $this->mode( $p ) );
		}
		return $data;
	}

	public function set_prices( $cart ) {
		foreach ( $cart->get_cart() as $item ) {
			if ( isset( $item['sg_measure'] ) && $item['data'] instanceof WC_Product ) {
				$base = (float) wc_get_product( $item['data']->get_id() )->get_price();
				$item['data']->set_price( round( $base * (float) $item['sg_measure']['amount'], wc_get_price_decimals() ) );
			}
		}
	}

	private function label( $m ) {
		$sq = 'area' === $m['mode'] ? '²' : '';
		return 'area' === $m['mode'] ? $m['width'] . ' × ' . $m['length'] . ' ' . $m['unit'] . ' = ' . round( $m['amount'], 3 ) . ' ' . $m['unit'] . $sq : round( $m['amount'], 3 ) . ' ' . $m['unit'];
	}

	public function item_data( $data, $item ) {
		if ( isset( $item['sg_measure'] ) ) {
			$data[] = array( 'key' => __( 'Size', 'shopglut' ), 'value' => esc_html( $this->label( $item['sg_measure'] ) ) );
		}
		return $data;
	}

	public function order_meta( $line, $key, $values ) {
		if ( isset( $values['sg_measure'] ) ) {
			$line->add_meta_data( __( 'Size', 'shopglut' ), $this->label( $values['sg_measure'] ) );
		}
	}

	public function price_html( $html, $p ) {
		if ( ! $this->mode( $p ) ) {
			return $html;
		}
		$sq = 'area' === $this->mode( $p ) ? '²' : '';
		return $html . ' <small>/ ' . esc_html( $this->unit( $p ) . $sq ) . '</small>';
	}

	public function loop_url( $url, $p ) {
		return $this->mode( $p ) ? $p->get_permalink() : $url;
	}

	public function no_ajax( $supports, $feature, $p ) {
		return ( 'ajax_add_to_cart' === $feature && $this->mode( $p ) ) ? false : $supports;
	}
}

return 'ShopGlut_Module_Measurement_Price';
