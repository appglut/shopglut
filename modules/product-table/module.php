<?php
/**
 * Product Table: [shopglut_product_table] lists products (and each variation of variable products) in a
 * table with image, SKU, price, stock and a quantity box; customers tick rows and add them all to the cart
 * at once. Good for wholesale / B2B and quick re-ordering.
 *
 * Attributes: category="slug,slug" tag="" ids="1,2" per_page="50" variations="yes" columns="image,name,sku,price,stock,qty".
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Module_Product_Table class.
 */
class ShopGlut_Module_Product_Table extends ShopGlut_Module {

	public function id() {
		return 'product-table';
	}

	public function info() {
		return array(
			'title'       => __( 'Product Table', 'shopglut' ),
			'description' => __( 'A product table with quantities and one "add selected to cart" button, for fast and wholesale ordering.', 'shopglut' ),
			'icon'        => 'faListCheck',
			'category'    => 'cart',
			'order'       => 118,
			'features'    => array(
				__( '[shopglut_product_table] by category, tag or IDs', 'shopglut' ),
				__( 'Each variation as its own row', 'shopglut' ),
				__( 'Quantities and add many to cart at once', 'shopglut' ),
			),
		);
	}

	public function boot() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		add_shortcode( 'shopglut_product_table', array( $this, 'shortcode' ) );
		add_action( 'wp_loaded', array( $this, 'add_to_cart' ), 25 );
	}

	/**
	 * Purchasable rows for the attributes.
	 *
	 * @param array $atts Attributes.
	 * @return WC_Product[]
	 */
	public function rows( $atts ) {
		$args = array( 'status' => 'publish', 'limit' => max( 1, min( 200, (int) $atts['per_page'] ) ), 'orderby' => 'title', 'order' => 'ASC', 'visibility' => 'catalog' );
		if ( '' !== $atts['ids'] ) {
			$args['include'] = array_map( 'absint', explode( ',', $atts['ids'] ) );
		}
		if ( '' !== $atts['category'] ) {
			$args['category'] = array_map( 'sanitize_title', explode( ',', $atts['category'] ) );
		}
		if ( '' !== $atts['tag'] ) {
			$args['tag'] = array_map( 'sanitize_title', explode( ',', $atts['tag'] ) );
		}
		$rows = array();
		foreach ( wc_get_products( $args ) as $p ) {
			if ( $p->is_type( 'variable' ) && 'yes' === $atts['variations'] ) {
				foreach ( $p->get_children() as $vid ) {
					$v = wc_get_product( $vid );
					if ( $v && $v->is_purchasable() && $v->variation_is_visible() ) {
						$rows[] = $v;
					}
				}
			} elseif ( $p->is_type( array( 'simple' ) ) && $p->is_purchasable() ) {
				$rows[] = $p;
			}
		}
		return $rows;
	}

	public function shortcode( $atts ) {
		$atts = shortcode_atts( array( 'category' => '', 'tag' => '', 'ids' => '', 'per_page' => 50, 'variations' => 'yes', 'columns' => 'image,name,sku,price,stock,qty' ), $atts, 'shopglut_product_table' );
		$cols = array_map( 'trim', explode( ',', $atts['columns'] ) );
		$rows = $this->rows( $atts );
		if ( ! $rows ) {
			return '<p>' . esc_html__( 'No products found.', 'shopglut' ) . '</p>';
		}
		$labels = array( 'image' => '', 'name' => __( 'Product', 'shopglut' ), 'sku' => __( 'SKU', 'shopglut' ), 'price' => __( 'Price', 'shopglut' ), 'stock' => __( 'Stock', 'shopglut' ), 'qty' => __( 'Qty', 'shopglut' ) );
		$h      = '<form method="post" class="sg-ptable-form">' . wp_nonce_field( 'sg_ptable', 'sg_ptable_nonce', true, false ) . '<div class="sg-ptable-wrap"><table class="sg-ptable shop_table"><thead><tr><th></th>';
		foreach ( $cols as $c ) {
			$h .= isset( $labels[ $c ] ) ? '<th>' . esc_html( $labels[ $c ] ) . '</th>' : '';
		}
		$h .= '</tr></thead><tbody>';
		foreach ( $rows as $p ) {
			$id  = $p->get_id();
			$max = $p->get_max_purchase_quantity();
			$h  .= '<tr><td><input type="checkbox" name="sg_pt_pick[]" value="' . esc_attr( $id ) . '"' . disabled( ! $p->is_in_stock(), true, false ) . ' aria-label="' . esc_attr( $p->get_name() ) . '"></td>';
			foreach ( $cols as $c ) {
				switch ( $c ) {
					case 'image':
						$h .= '<td class="sg-pt-img">' . wp_kses_post( $p->get_image( 'thumbnail' ) ) . '</td>';
						break;
					case 'name':
						$h .= '<td><a href="' . esc_url( $p->get_permalink() ) . '">' . esc_html( $p->get_name() ) . '</a></td>';
						break;
					case 'sku':
						$h .= '<td>' . esc_html( $p->get_sku() ) . '</td>';
						break;
					case 'price':
						$h .= '<td>' . wp_kses_post( $p->get_price_html() ) . '</td>';
						break;
					case 'stock':
						$h .= '<td>' . esc_html( $p->is_in_stock() ? ( $p->managing_stock() ? (string) $p->get_stock_quantity() : __( 'In stock', 'shopglut' ) ) : __( 'Out of stock', 'shopglut' ) ) . '</td>';
						break;
					case 'qty':
						$h .= '<td><input type="number" class="qty" name="sg_pt_qty[' . esc_attr( $id ) . ']" value="1" min="' . esc_attr( $p->get_min_purchase_quantity() ) . '"' . ( $max > 0 ? ' max="' . esc_attr( $max ) . '"' : '' ) . ' step="1" style="width:70px"></td>';
						break;
				}
			}
			$h .= '</tr>';
		}
		$h .= '</tbody></table></div><p><button type="submit" class="button alt">' . esc_html__( 'Add selected to cart', 'shopglut' ) . '</button></p></form>';
		$h .= '<style>.sg-ptable-wrap{overflow-x:auto}.sg-ptable td,.sg-ptable th{vertical-align:middle}.sg-pt-img img{width:48px;height:48px;object-fit:cover}</style>';
		$h .= '<script>document.querySelectorAll(".sg-ptable-form input.qty").forEach(function(q){q.addEventListener("input",function(){var c=q.closest("tr").querySelector("input[type=checkbox]");if(c&&!c.disabled)c.checked=q.value>0})});</script>';
		return $h;
	}

	/**
	 * Add ticked rows with their quantities.
	 *
	 * @return int Lines added.
	 */
	public function add_to_cart() {
		if ( empty( $_POST['sg_pt_pick'] ) || ! isset( $_POST['sg_ptable_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sg_ptable_nonce'] ) ), 'sg_ptable' ) || ! WC()->cart ) {
			return 0;
		}
		$qtys  = isset( $_POST['sg_pt_qty'] ) && is_array( $_POST['sg_pt_qty'] ) ? array_map( 'wc_stock_amount', wp_unslash( $_POST['sg_pt_qty'] ) ) : array();
		$added = 0;
		foreach ( array_map( 'absint', (array) wp_unslash( $_POST['sg_pt_pick'] ) ) as $id ) {
			$p   = wc_get_product( $id );
			$qty = isset( $qtys[ $id ] ) ? max( 1, $qtys[ $id ] ) : 1;
			if ( ! $p || ! $p->is_purchasable() ) {
				continue;
			}
			$ok = $p->is_type( 'variation' ) ? WC()->cart->add_to_cart( $p->get_parent_id(), $qty, $id, $p->get_variation_attributes() ) : WC()->cart->add_to_cart( $id, $qty );
			$added += $ok ? 1 : 0;
		}
		if ( $added ) {
			/* translators: %d: count */
			wc_add_notice( sprintf( _n( '%d product added to your cart.', '%d products added to your cart.', $added, 'shopglut' ), $added ) );
			if ( ! defined( 'SHOPGLUT_TESTING' ) ) {
				wp_safe_redirect( wc_get_cart_url() );
				exit;
			}
		}
		return $added;
	}
}

return 'ShopGlut_Module_Product_Table';
