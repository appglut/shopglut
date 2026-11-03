<?php
/**
 * Product Badges front end: shows the active badge layouts on product images or before product titles,
 * in classic themes (WooCommerce hooks) and block themes (Product Collection / single product blocks).
 *
 * Conditions and styling are ported from ProductBadges Glut. Saved settings shape:
 * settings[shopglut_product_badge_settings][ enable_badge | display-locations | badge_type | product_badge-settings[...] ].
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Product_Badges class.
 */
class ShopGlut_Product_Badges {

	const BOX = 'shopglut_product_badge_settings';

	/**
	 * Image positions offered by the settings.
	 *
	 * @var string[]
	 */
	const POSITIONS = array( 'top-left', 'top-center', 'top-right', 'bottom-left', 'bottom-center', 'bottom-right' );

	/**
	 * Enabled badges (layout id => settings box), loaded once.
	 *
	 * @var array|null
	 */
	private $badges = null;

	/**
	 * Hook into WooCommerce.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ), 30 );

		// Classic themes: wrap the loop thumbnail so image badges sit on the image.
		add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'loop_image_open' ), 9 );
		add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'loop_image_close' ), 11 );
		add_action( 'woocommerce_shop_loop_item_title', array( $this, 'before_title' ), 5 );

		// Classic themes: single product.
		add_action( 'woocommerce_before_single_product_summary', array( $this, 'single_image' ), 15 );
		add_action( 'woocommerce_single_product_summary', array( $this, 'before_title' ), 4 );

		// Block themes.
		add_filter( 'render_block_woocommerce/product-image', array( $this, 'block_image' ), 10, 3 );
		add_filter( 'render_block_woocommerce/product-image-gallery', array( $this, 'block_gallery' ), 10, 3 );
		add_filter( 'render_block_core/post-title', array( $this, 'block_title' ), 10, 3 );

		// Product blocks also fire some classic hooks; while they render, the block filters above handle badges.
		add_filter( 'render_block_data', array( $this, 'enter_block' ) );
		add_filter( 'render_block', array( $this, 'leave_block' ), 10, 2 );

		// WooCommerce's own "Sale!" flash is replaced by a sale badge when one is active.
		add_filter( 'woocommerce_sale_flash', array( $this, 'sale_flash' ), 20 );
	}

	/**
	 * Blocks that render products themselves (their classic hooks are ignored).
	 *
	 * @var string[]
	 */
	const PRODUCT_BLOCKS = array( 'woocommerce/product-collection', 'woocommerce/product-template', 'core/post-template', 'woocommerce/single-product', 'woocommerce/product-image-gallery', 'woocommerce/related-products' );

	/**
	 * How many product blocks are rendering now.
	 *
	 * @var int
	 */
	private $block_depth = 0;

	/**
	 * A product block starts rendering.
	 *
	 * @param array $block Parsed block.
	 * @return array
	 */
	public function enter_block( $block ) {
		if ( isset( $block['blockName'] ) && in_array( $block['blockName'], self::PRODUCT_BLOCKS, true ) ) {
			++$this->block_depth;
		}
		if ( isset( $block['blockName'] ) && 'woocommerce/legacy-template' === $block['blockName'] ) {
			++$this->legacy_depth;
		}
		return $block;
	}

	/**
	 * How many legacy (classic PHP) WooCommerce templates are rendering inside a block theme.
	 *
	 * @var int
	 */
	private $legacy_depth = 0;

	/**
	 * Whether the classic WooCommerce hooks should print badges now. In block themes WooCommerce fires
	 * them for compatibility, but the block filters already add the badges.
	 *
	 * @return bool
	 */
	private function classic_hooks() {
		if ( $this->block_depth > 0 ) {
			return false;
		}
		return ! wp_is_block_theme() || $this->legacy_depth > 0;
	}

	/**
	 * A product block finished rendering.
	 *
	 * @param string $html  Block HTML.
	 * @param array  $block Parsed block.
	 * @return string
	 */
	public function leave_block( $html, $block ) {
		if ( isset( $block['blockName'] ) && in_array( $block['blockName'], self::PRODUCT_BLOCKS, true ) && $this->block_depth > 0 ) {
			--$this->block_depth;
		}
		if ( isset( $block['blockName'] ) && 'woocommerce/legacy-template' === $block['blockName'] && $this->legacy_depth > 0 ) {
			--$this->legacy_depth;
		}
		return $html;
	}

	/* ---------------------------------------------------------------------
	 * Data
	 * ------------------------------------------------------------------- */

	/**
	 * Enabled badge layouts.
	 *
	 * @return array id => settings box
	 */
	private function badges() {
		if ( null === $this->badges ) {
			$this->badges = array();
			foreach ( ShopGlut_Layouts::active( 'product-badges' ) as $layout ) {
				$box = isset( $layout['settings'][ self::BOX ] ) && is_array( $layout['settings'][ self::BOX ] ) ? $layout['settings'][ self::BOX ] : array();
				if ( empty( $box['enable_badge'] ) || empty( $box['badge_type'] ) || ! is_array( $box['badge_type'] ) ) {
					continue;
				}
				$this->badges[ (int) $layout['id'] ] = $box;
			}
		}
		return $this->badges;
	}

	/**
	 * Settings of the badge configuration tabs.
	 *
	 * @param array $box Badge settings box.
	 * @return array
	 */
	private static function conf( $box ) {
		return isset( $box['product_badge-settings'] ) && is_array( $box['product_badge-settings'] ) ? $box['product_badge-settings'] : array();
	}

	/**
	 * A setting of one badge type (sale, new, out_of_stock).
	 *
	 * @param array  $conf    Configuration.
	 * @param string $key     Key.
	 * @param mixed  $default Default.
	 * @return mixed
	 */
	private static function val( $conf, $key, $default ) {
		if ( ! isset( $conf[ $key ] ) || '' === $conf[ $key ] ) {
			return $default;
		}
		$value = $conf[ $key ];
		// Old saves wrapped some numbers: array( key => value ).
		if ( is_array( $value ) && isset( $value[ $key ] ) ) {
			$value = $value[ $key ];
		}
		return $value;
	}

	/**
	 * Whether any enabled badge uses a badge type.
	 *
	 * @param string $type Badge type.
	 * @return bool
	 */
	private function uses_type( $type ) {
		foreach ( $this->badges() as $box ) {
			if ( in_array( $type, $box['badge_type'], true ) ) {
				return true;
			}
		}
		return false;
	}

	/* ---------------------------------------------------------------------
	 * Conditions
	 * ------------------------------------------------------------------- */

	/**
	 * Whether a badge applies on this page to this product ("Display Badge On").
	 *
	 * @param array      $box     Badge settings box.
	 * @param WC_Product $product Product.
	 * @return bool
	 */
	private function location_matches( $box, $product ) {
		$where = isset( $box['display-locations'] ) ? (array) $box['display-locations'] : array();
		if ( ! $where ) {
			return false;
		}
		$id = $product->get_id();
		foreach ( $where as $place ) {
			$place = (string) $place;
			switch ( $place ) {
				case 'All Products':
					return true;
				case 'Woo Shop Page':
					if ( function_exists( 'is_shop' ) && is_shop() ) {
						return true;
					}
					break;
				case 'Single Product Template1':
					if ( is_product() && (int) get_queried_object_id() === $id ) {
						return true;
					}
					break;
				case 'All Categories':
					if ( is_product_category() ) {
						return true;
					}
					break;
				case 'All Tags':
					if ( is_product_tag() ) {
						return true;
					}
					break;
				default:
					if ( 0 === strpos( $place, 'cat_' ) && has_term( (int) substr( $place, 4 ), 'product_cat', $id ) ) {
						return true;
					}
					if ( 0 === strpos( $place, 'tag_' ) && has_term( (int) substr( $place, 4 ), 'product_tag', $id ) ) {
						return true;
					}
					if ( 0 === strpos( $place, 'product_' ) && (int) substr( $place, 8 ) === $id ) {
						return true;
					}
			}
		}
		return false;
	}

	/**
	 * Whether a badge type's condition is met by a product.
	 *
	 * @param array      $conf    Configuration.
	 * @param string     $type    sale | new | out_of_stock.
	 * @param WC_Product $product Product.
	 * @return bool
	 */
	private function condition_met( $conf, $type, $product ) {
		switch ( $type ) {
			case 'sale':
				if ( ! $product->is_on_sale() ) {
					return false;
				}
				$condition = self::val( $conf, 'sale_condition', 'any_sale' );
				if ( 'any_sale' === $condition ) {
					return true;
				}
				list( $percent, $amount ) = self::best_discount( $product );
				$min_percent = (float) self::val( $conf, 'min_discount_percentage', 10 );
				$min_amount  = (float) self::val( $conf, 'min_discount_amount', 5 );
				if ( 'percentage_sale' === $condition ) {
					return $percent >= $min_percent;
				}
				if ( 'fixed_sale' === $condition ) {
					return $amount >= $min_amount;
				}
				return $percent >= $min_percent || $amount >= $min_amount; // min_discount.
			case 'new':
				$created = $product->get_date_created();
				if ( ! $created ) {
					return false;
				}
				$days = (int) self::val( $conf, 'new_product_days', 7 );
				return ( time() - $created->getTimestamp() ) / DAY_IN_SECONDS <= $days;
			case 'out_of_stock':
				if ( $product->is_in_stock() ) {
					return false;
				}
				$types = self::val( $conf, 'out_of_stock_types', 'all' );
				if ( 'simple_only' === $types ) {
					return $product->is_type( 'simple' );
				}
				if ( 'variable_only' === $types ) {
					return $product->is_type( 'variable' );
				}
				return true;
		}
		return false;
	}

	/**
	 * Largest discount of a product (or of its variations).
	 *
	 * @param WC_Product $product Product.
	 * @return float[] percent, amount
	 */
	private static function best_discount( $product ) {
		$pairs = array();
		if ( $product->is_type( 'variable' ) ) {
			$prices = $product->get_variation_prices();
			foreach ( $prices['regular_price'] as $vid => $regular ) {
				$pairs[] = array( (float) $regular, isset( $prices['sale_price'][ $vid ] ) ? (float) $prices['sale_price'][ $vid ] : (float) $regular );
			}
		} else {
			$pairs[] = array( (float) $product->get_regular_price(), (float) $product->get_sale_price() );
		}
		$percent = 0;
		$amount  = 0;
		foreach ( $pairs as $pair ) {
			list( $regular, $sale ) = $pair;
			if ( $regular > 0 && $sale > 0 && $sale < $regular ) {
				$percent = max( $percent, ( $regular - $sale ) / $regular * 100 );
				$amount  = max( $amount, $regular - $sale );
			}
		}
		return array( $percent, $amount );
	}

	/**
	 * Badges to show for a product in an area.
	 *
	 * @param WC_Product $product Product.
	 * @param string     $area    product_image | before_product_title.
	 * @return array List of array( id, type, text, position ).
	 */
	private function badges_for( $product, $area ) {
		$out = array();
		foreach ( $this->badges() as $id => $box ) {
			if ( ! $this->location_matches( $box, $product ) ) {
				continue;
			}
			$conf = self::conf( $box );
			foreach ( $box['badge_type'] as $type ) {
				$type = sanitize_key( $type );
				if ( self::val( $conf, $type . '_display_area', 'product_image' ) !== $area ) {
					continue;
				}
				if ( ! $this->condition_met( $conf, $type, $product ) ) {
					continue;
				}
				$defaults = array(
					'sale'         => __( 'SALE!', 'shopglut' ),
					'new'          => __( 'NEW!', 'shopglut' ),
					'out_of_stock' => __( 'OUT OF STOCK', 'shopglut' ),
				);
				$position = 'product_image' === $area
					? self::val( $conf, $type . '_position_image', 'top-left' )
					: self::val( $conf, $type . '_position_inline', 'left' );
				$out[]    = array(
					'id'       => $id,
					'type'     => $type,
					'text'     => (string) self::val( $conf, $type . '_badge_text', isset( $defaults[ $type ] ) ? $defaults[ $type ] : strtoupper( $type ) ),
					'position' => sanitize_key( $position ),
				);
			}
		}
		return $out;
	}

	/* ---------------------------------------------------------------------
	 * Output
	 * ------------------------------------------------------------------- */

	/**
	 * One badge.
	 *
	 * @param array $badge Badge.
	 * @return string
	 */
	private static function badge_html( $badge ) {
		return sprintf(
			'<span class="shopglut-badge shopglut-badge-%1$d shopglut-badge--%2$s">%3$s</span>',
			(int) $badge['id'],
			esc_attr( str_replace( '_', '-', $badge['type'] ) ),
			esc_html( $badge['text'] )
		);
	}

	/**
	 * Image badges of a product, grouped by corner.
	 *
	 * @param WC_Product|null $product Product.
	 * @return string
	 */
	public function image_html( $product ) {
		if ( ! $product instanceof WC_Product ) {
			return '';
		}
		$groups = array();
		foreach ( $this->badges_for( $product, 'product_image' ) as $badge ) {
			$pos              = in_array( $badge['position'], self::POSITIONS, true ) ? $badge['position'] : 'top-left';
			$groups[ $pos ][] = self::badge_html( $badge );
		}
		$html = '';
		foreach ( $groups as $pos => $items ) {
			$html .= '<div class="shopglut-badges shopglut-badges--' . esc_attr( $pos ) . '">' . implode( '', $items ) . '</div>';
		}
		return $html;
	}

	/**
	 * Badges shown before the product title.
	 *
	 * @param WC_Product|null $product Product.
	 * @return string
	 */
	public function title_html( $product ) {
		if ( ! $product instanceof WC_Product ) {
			return '';
		}
		$badges = $this->badges_for( $product, 'before_product_title' );
		if ( ! $badges ) {
			return '';
		}
		$align = in_array( $badges[0]['position'], array( 'left', 'center', 'right' ), true ) ? $badges[0]['position'] : 'left';
		return '<div class="shopglut-badges-inline shopglut-badges-inline--' . esc_attr( $align ) . '">' . implode( '', array_map( array( __CLASS__, 'badge_html' ), $badges ) ) . '</div>';
	}

	/**
	 * Classic loop: open the image wrapper.
	 *
	 * @return void
	 */
	public function loop_image_open() {
		if ( ! $this->classic_hooks() ) {
			return;
		}
		global $product;
		$badges = $this->image_html( $product );
		if ( '' === $badges ) {
			return;
		}
		echo '<div class="shopglut-badge-host">' . $badges; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in badge_html().
		$this->open = true;
	}

	/**
	 * Whether loop_image_open() opened a wrapper for the current product.
	 *
	 * @var bool
	 */
	private $open = false;

	/**
	 * Classic loop: close the image wrapper.
	 *
	 * @return void
	 */
	public function loop_image_close() {
		if ( $this->open ) {
			echo '</div>';
			$this->open = false;
		}
	}

	/**
	 * Classic single product: badges over the gallery (moved into it by a small script).
	 *
	 * @return void
	 */
	public function single_image() {
		if ( ! $this->classic_hooks() ) {
			return;
		}
		global $product;
		$badges = $this->image_html( $product );
		if ( '' !== $badges ) {
			echo '<div class="shopglut-badges-single" hidden>' . $badges . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in badge_html().
		}
	}

	/**
	 * Classic themes: badges before the product title.
	 *
	 * @return void
	 */
	public function before_title() {
		if ( ! $this->classic_hooks() ) {
			return;
		}
		global $product;
		echo $this->title_html( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in badge_html().
	}

	/**
	 * Product of a block (from its context).
	 *
	 * @param WP_Block|null $instance Block.
	 * @return WC_Product|null
	 */
	private static function block_product( $instance ) {
		$id = ( $instance && isset( $instance->context['postId'] ) ) ? (int) $instance->context['postId'] : 0;
		if ( ! $id || 'product' !== get_post_type( $id ) ) {
			return null;
		}
		$product = wc_get_product( $id );
		return $product ? $product : null;
	}

	/**
	 * Block themes: Product Image block (Product Collection, related products…).
	 *
	 * @param string   $html     Block HTML.
	 * @param array    $block    Parsed block.
	 * @param WP_Block $instance Block instance.
	 * @return string
	 */
	public function block_image( $html, $block, $instance = null ) {
		$badges = $this->image_html( self::block_product( $instance ) );
		return '' === $badges ? $html : '<div class="shopglut-badge-host">' . $badges . $html . '</div>';
	}

	/**
	 * Block themes: single product gallery block.
	 *
	 * @param string   $html     Block HTML.
	 * @param array    $block    Parsed block.
	 * @param WP_Block $instance Block instance.
	 * @return string
	 */
	public function block_gallery( $html, $block, $instance = null ) {
		$product = self::block_product( $instance );
		if ( ! $product ) {
			global $product;
		}
		$badges = $this->image_html( $product );
		return '' === $badges ? $html : '<div class="shopglut-badge-host">' . $badges . $html . '</div>';
	}

	/**
	 * Block themes: badges before a product's title.
	 *
	 * @param string   $html     Block HTML.
	 * @param array    $block    Parsed block.
	 * @param WP_Block $instance Block instance.
	 * @return string
	 */
	public function block_title( $html, $block, $instance = null ) {
		return $this->title_html( self::block_product( $instance ) ) . $html;
	}

	/**
	 * Hide WooCommerce's "Sale!" flash when a sale badge replaces it.
	 *
	 * @param string $html Flash HTML.
	 * @return string
	 */
	public function sale_flash( $html ) {
		return $this->uses_type( 'sale' ) ? '' : $html;
	}

	/* ---------------------------------------------------------------------
	 * Styles
	 * ------------------------------------------------------------------- */

	/**
	 * Print the badge styles (only when badges exist).
	 *
	 * @return void
	 */
	public function enqueue() {
		if ( ! $this->badges() ) {
			return;
		}
		wp_register_style( 'shopglut-badges', false, array(), SHOPGLUT_VERSION );
		wp_enqueue_style( 'shopglut-badges' );
		wp_add_inline_style( 'shopglut-badges', $this->css() );

		// Classic single product: put the badges inside the gallery so they sit on the image.
		wp_register_script( 'shopglut-badges', false, array(), SHOPGLUT_VERSION, true );
		wp_enqueue_script( 'shopglut-badges' );
		wp_add_inline_script( 'shopglut-badges', "document.querySelectorAll('.shopglut-badges-single').forEach(function(b){var g=b.parentNode.querySelector('.woocommerce-product-gallery');if(g){g.classList.add('shopglut-badge-host');while(b.firstChild){g.appendChild(b.firstChild);}}b.remove();});" );
	}

	/**
	 * Badge CSS: layout plus one rule per badge and type.
	 *
	 * @return string
	 */
	private function css() {
		$css  = '.shopglut-badge-host{position:relative}';
		$css .= '.shopglut-badges{position:absolute;z-index:9;display:flex;flex-direction:column;gap:6px;pointer-events:none;max-width:calc(100% - 20px)}';
		$css .= '.shopglut-badges--top-left{top:10px;left:10px;align-items:flex-start}.shopglut-badges--top-right{top:10px;right:10px;align-items:flex-end}';
		$css .= '.shopglut-badges--top-center{top:10px;left:50%;transform:translateX(-50%);align-items:center}';
		$css .= '.shopglut-badges--bottom-left{bottom:10px;left:10px;align-items:flex-start}.shopglut-badges--bottom-right{bottom:10px;right:10px;align-items:flex-end}';
		$css .= '.shopglut-badges--bottom-center{bottom:10px;left:50%;transform:translateX(-50%);align-items:center}';
		$css .= '.shopglut-badges-inline{display:flex;flex-wrap:wrap;gap:6px;margin:0 0 8px}.shopglut-badges-inline--center{justify-content:center}.shopglut-badges-inline--right{justify-content:flex-end}';
		$css .= '.shopglut-badge{display:inline-block;line-height:1.2;text-align:center;white-space:nowrap;box-sizing:border-box}';
		$css .= '@media(max-width:768px){.shopglut-badges .shopglut-badge{font-size:10px!important;padding:3px 6px!important}}';
		if ( $this->uses_type( 'sale' ) ) {
			$css .= '.wc-block-components-product-sale-badge,.wp-block-woocommerce-product-sale-badge,.woocommerce span.onsale,.ast-onsale-card,.ast-on-card-button.ast-onsale-card{display:none!important}';
		}

		foreach ( $this->badges() as $id => $box ) {
			$conf = self::conf( $box );
			foreach ( $box['badge_type'] as $type ) {
				$type = sanitize_key( $type );
				$p    = $type . '_badge_';
				$bg   = sanitize_hex_color( (string) self::val( $conf, $p . 'bg_color', '#ff0000' ) );
				$css .= sprintf( '.shopglut-badge-%d.shopglut-badge--%s{', (int) $id, esc_attr( str_replace( '_', '-', $type ) ) );
				$css .= 'color:' . sanitize_hex_color( (string) self::val( $conf, $p . 'text_color', '#ffffff' ) ) . ';';
				$css .= 'font-size:' . absint( self::val( $conf, $p . 'font_size', 12 ) ) . 'px;';
				$css .= 'font-weight:' . absint( self::val( $conf, $p . 'font_weight', 700 ) ) . ';';
				$css .= 'text-transform:' . sanitize_key( (string) self::val( $conf, $p . 'text_transform', 'uppercase' ) ) . ';';
				$css .= 'padding:' . absint( self::val( $conf, $p . 'padding_v', 5 ) ) . 'px ' . absint( self::val( $conf, $p . 'padding_h', 10 ) ) . 'px;';
				$css .= 'border-radius:' . absint( self::val( $conf, $p . 'border_radius', 3 ) ) . 'px;';
				$gradient = sanitize_hex_color( (string) self::val( $conf, $p . 'gradient_color', '' ) );
				$css     .= ( self::val( $conf, $p . 'enable_gradient', false ) && $gradient )
					? 'background:linear-gradient(135deg,' . $bg . ',' . $gradient . ');'
					: 'background:' . $bg . ';';
				$border = absint( self::val( $conf, $p . 'border_width', 0 ) );
				if ( $border ) {
					$css .= 'border:' . $border . 'px solid ' . sanitize_hex_color( (string) self::val( $conf, $p . 'border_color', '#000000' ) ) . ';';
				}
				if ( self::val( $conf, $p . 'enable_shadow', false ) ) {
					$shadow = preg_replace( '/[^a-zA-Z0-9#(),.\s%]/', '', (string) self::val( $conf, $p . 'shadow_color', 'rgba(0,0,0,.2)' ) );
					$css   .= 'box-shadow:0 2px ' . absint( self::val( $conf, $p . 'shadow_blur', 4 ) ) . 'px ' . $shadow . ';';
				}
				$css .= '}';
			}
		}
		return $css;
	}
}
