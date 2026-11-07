<?php
/**
 * Size Chart: a "Size chart" link on product pages that opens a size table. One chart for the store (or for
 * chosen categories), and each product can have its own.
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Module_Size_Chart class.
 */
class ShopGlut_Module_Size_Chart extends ShopGlut_Module {

	public function id() {
		return 'size-chart';
	}

	public function info() {
		return array(
			'title'        => __( 'Size Chart', 'shopglut' ),
			'description'  => __( 'A size chart link on product pages that opens a size table, per store, category or product.', 'shopglut' ),
			'icon'         => 'faShirt',
			'category'     => 'product',
			'order'        => 74,
			'features'     => array(
				__( 'Simple table: one row per line, cells split with |', 'shopglut' ),
				__( 'For all products or chosen categories', 'shopglut' ),
				__( 'Own chart per product', 'shopglut' ),
			),
		);
	}

	public function settings_sections() {
		return $this->schema( $this->dir . 'settings/options.php', 'options' );
	}

	private function opt( $key, $default = '' ) {
		return shopglut_get_module_setting( 'size-chart', $key, $default );
	}

	public function boot() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		add_action( 'woocommerce_before_add_to_cart_form', array( $this, 'show' ), 20 );
		add_action( 'woocommerce_product_options_general_product_data', array( $this, 'product_field' ) );
		add_action( 'woocommerce_admin_process_product_object', array( $this, 'save_product' ) );
	}

	/**
	 * Chart text for a product, or ''.
	 *
	 * @param WC_Product $product Product.
	 * @return string
	 */
	public function chart_for( $product ) {
		$own = trim( (string) $product->get_meta( '_sg_size_chart' ) );
		if ( '' !== $own ) {
			return '-' === $own ? '' : $own;
		}
		$cats = array_filter( array_map( 'trim', explode( ',', (string) $this->opt( 'categories' ) ) ) );
		if ( $cats && ! has_term( $cats, 'product_cat', $product->get_id() ) ) {
			return '';
		}
		return str_replace( '\n', "\n", (string) $this->opt( 'chart' ) );
	}

	/**
	 * Build the table.
	 *
	 * @param string $text Rows / cells.
	 * @return string
	 */
	public function table( $text ) {
		$rows = array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $text ) ) ) );
		if ( ! $rows ) {
			return '';
		}
		$html = '<table class="sg-size-table">';
		foreach ( $rows as $i => $row ) {
			$tag   = 0 === $i ? 'th' : 'td';
			$html .= '<tr>';
			foreach ( explode( '|', $row ) as $cell ) {
				$html .= '<' . $tag . '>' . esc_html( trim( $cell ) ) . '</' . $tag . '>';
			}
			$html .= '</tr>';
		}
		return $html . '</table>';
	}

	public function show() {
		global $product;
		$table = $product ? $this->table( $this->chart_for( $product ) ) : '';
		if ( '' === $table ) {
			return;
		}
		$note = trim( (string) $this->opt( 'note' ) );
		echo '<p class="sg-size-link"><button type="button" class="button-link" onclick="this.parentNode.nextElementSibling.showModal()">' . esc_html( $this->opt( 'link_text', __( 'Size chart', 'shopglut' ) ) ) . '</button></p>';
		echo '<dialog class="sg-size-dialog"><form method="dialog"><button class="sg-size-close" aria-label="' . esc_attr__( 'Close', 'shopglut' ) . '">×</button></form><h3>' . esc_html( $this->opt( 'title', __( 'Size chart', 'shopglut' ) ) ) . '</h3>' . wp_kses_post( $table ) . ( $note ? '<p>' . esc_html( $note ) . '</p>' : '' ) . '</dialog>';
		echo '<style>.sg-size-link button{background:none;border:0;padding:0;text-decoration:underline;cursor:pointer}.sg-size-dialog{max-width:640px;width:92vw;border:0;border-radius:12px;padding:20px}.sg-size-dialog::backdrop{background:rgba(0,0,0,.45)}.sg-size-close{float:right;background:none;border:0;font-size:24px;cursor:pointer}.sg-size-table{width:100%;border-collapse:collapse}.sg-size-table th,.sg-size-table td{border:1px solid rgba(0,0,0,.12);padding:6px 10px;text-align:center}</style>';
	}

	public function product_field() {
		woocommerce_wp_textarea_input( array( 'id' => '_sg_size_chart', 'label' => __( 'Size chart', 'shopglut' ), 'description' => __( 'Own size chart for this product (one row per line, cells split with |). Empty = store chart, "-" = no chart.', 'shopglut' ), 'desc_tip' => true ) );
	}

	/**
	 * Save the product chart (nonce checked by WooCommerce's product save).
	 *
	 * @param WC_Product $product Product.
	 */
	public function save_product( $product ) {
		if ( isset( $_POST['_sg_size_chart'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$product->update_meta_data( '_sg_size_chart', sanitize_textarea_field( wp_unslash( $_POST['_sg_size_chart'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}
	}
}

return 'ShopGlut_Module_Size_Chart';
