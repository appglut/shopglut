<?php
/**
 * Brands: product brands with logos, shown on product pages, shop cards and a brands page.
 *
 * WooCommerce 9.6+ has its own product_brand taxonomy; ShopGlut uses it, and only registers the same
 * taxonomy on older WooCommerce versions.
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Module_Brands class.
 */
class ShopGlut_Module_Brands extends ShopGlut_Module {

	const TAX = 'product_brand';

	public function id() {
		return 'brands';
	}

	public function info() {
		return array(
			'title'        => __( 'Brands', 'shopglut' ),
			'description'  => __( 'Product brands with logos on product pages, shop cards, brand pages and a brands grid.', 'shopglut' ),
			'icon'         => 'faStore',
			'category'     => 'product',
			'order'        => 75,
			'features'     => array(
				__( 'Uses WooCommerce brands (adds them on older WooCommerce)', 'shopglut' ),
				__( 'Brand logo or name on the product page', 'shopglut' ),
				__( 'Brand name on shop cards', 'shopglut' ),
				__( '[shopglut_brands] grid for a brands page', 'shopglut' ),
			),
		);
	}

	public function settings_sections() {
		return $this->schema( $this->dir . 'settings/options.php', 'options' );
	}

	private function opt( $key, $default ) {
		return shopglut_get_module_setting( 'brands', $key, $default );
	}

	public function boot() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		add_action( 'init', array( $this, 'register' ), 20 );
		add_shortcode( 'shopglut_brands', array( $this, 'grid' ) );
		if ( $this->opt( 'show_single', 1 ) ) {
			add_action( 'woocommerce_single_product_summary', array( $this, 'single' ), 6 );
		}
		if ( $this->opt( 'show_loop', 0 ) ) {
			add_action( 'woocommerce_shop_loop_item_title', array( $this, 'loop' ), 5 );
		}
		if ( $this->opt( 'archive_header', 1 ) ) {
			add_action( 'woocommerce_archive_description', array( $this, 'archive' ), 5 );
		}
		add_action( 'wp_head', array( $this, 'css' ) );
	}

	/**
	 * Register the taxonomy when WooCommerce doesn't.
	 */
	public function register() {
		if ( taxonomy_exists( self::TAX ) ) {
			return;
		}
		register_taxonomy(
			self::TAX,
			array( 'product' ),
			array(
				'labels'            => array(
					'name'          => __( 'Brands', 'shopglut' ),
					'singular_name' => __( 'Brand', 'shopglut' ),
					'add_new_item'  => __( 'Add new brand', 'shopglut' ),
				),
				'hierarchical'      => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rewrite'           => array( 'slug' => 'brand' ),
			)
		);
	}

	/**
	 * Brands of a product.
	 *
	 * @param int $product_id Product id.
	 * @return WP_Term[]
	 */
	public function brands( $product_id ) {
		$terms = taxonomy_exists( self::TAX ) ? get_the_terms( $product_id, self::TAX ) : array();
		return is_array( $terms ) ? $terms : array();
	}

	/**
	 * Logo URL of a brand.
	 *
	 * @param WP_Term $term Brand.
	 * @param string  $size Image size.
	 * @return string
	 */
	public function logo( $term, $size = 'medium' ) {
		$id = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
		return $id ? (string) wp_get_attachment_image_url( $id, $size ) : '';
	}

	public function single() {
		global $product;
		if ( ! $product ) {
			return;
		}
		$how = $this->opt( 'single_display', 'logo' );
		$out = array();
		foreach ( $this->brands( $product->get_id() ) as $term ) {
			$logo  = 'name' !== $how ? $this->logo( $term, 'thumbnail' ) : '';
			$inner = $logo ? '<img src="' . esc_url( $logo ) . '" alt="' . esc_attr( $term->name ) . '">' : '';
			if ( ! $logo || 'both' === $how || 'name' === $how ) {
				$inner .= '<span>' . esc_html( $term->name ) . '</span>';
			}
			$out[] = '<a href="' . esc_url( get_term_link( $term ) ) . '">' . $inner . '</a>';
		}
		if ( $out ) {
			echo '<div class="sg-brand">' . wp_kses_post( implode( ' ', $out ) ) . '</div>';
		}
	}

	public function loop() {
		global $product;
		$terms = $product ? $this->brands( $product->get_id() ) : array();
		if ( $terms ) {
			echo '<span class="sg-brand-loop">' . esc_html( implode( ', ', wp_list_pluck( $terms, 'name' ) ) ) . '</span>';
		}
	}

	public function archive() {
		if ( ! is_tax( self::TAX ) ) {
			return;
		}
		$term = get_queried_object();
		$logo = $term ? $this->logo( $term ) : '';
		if ( $logo ) {
			echo '<div class="sg-brand-header"><img src="' . esc_url( $logo ) . '" alt="' . esc_attr( $term->name ) . '"></div>';
		}
	}

	/**
	 * [shopglut_brands columns="4" hide_empty="1"]
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	public function grid( $atts ) {
		$atts  = shortcode_atts( array( 'columns' => 4, 'hide_empty' => 1 ), $atts, 'shopglut_brands' );
		$terms = taxonomy_exists( self::TAX ) ? get_terms( array( 'taxonomy' => self::TAX, 'hide_empty' => (bool) $atts['hide_empty'] ) ) : array();
		if ( is_wp_error( $terms ) || ! $terms ) {
			return '';
		}
		$cols = max( 1, min( 8, (int) $atts['columns'] ) );
		$html = '<div class="sg-brands" style="--sg-brand-cols:' . $cols . '">';
		foreach ( $terms as $term ) {
			$logo  = $this->logo( $term );
			$html .= '<a class="sg-brands-item" href="' . esc_url( get_term_link( $term ) ) . '">';
			$html .= $logo ? '<img src="' . esc_url( $logo ) . '" alt="" loading="lazy">' : '';
			$html .= '<span>' . esc_html( $term->name ) . '</span><small>' . (int) $term->count . '</small></a>';
		}
		return $html . '</div>';
	}

	public function css() {
		echo '<style id="sg-brands-css">.sg-brand{margin:0 0 .6em}.sg-brand a{display:inline-flex;align-items:center;gap:6px;text-decoration:none}.sg-brand img{max-height:40px;width:auto}.sg-brand-loop{display:block;font-size:.85em;opacity:.7}.sg-brand-header img{max-height:90px;width:auto;margin:0 0 1em}.sg-brands{display:grid;grid-template-columns:repeat(var(--sg-brand-cols),minmax(0,1fr));gap:16px}@media(max-width:600px){.sg-brands{grid-template-columns:repeat(2,minmax(0,1fr))}}.sg-brands-item{display:flex;flex-direction:column;align-items:center;gap:6px;padding:16px;border:1px solid rgba(0,0,0,.1);border-radius:10px;text-decoration:none;text-align:center}.sg-brands-item img{max-height:70px;width:auto}.sg-brands-item small{opacity:.6}</style>';
	}
}

return 'ShopGlut_Module_Brands';
