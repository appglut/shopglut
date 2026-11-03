<?php
/**
 * Product Page front end.
 *
 * The layout that applies to the product (Display Rules: all, categories, chosen products) arranges
 * WooCommerce's own single product parts, so variations, swatches, wishlist, badges, gateways and
 * other plugins hooked into the product page keep working:
 *   gallery  = woocommerce_before_single_product_summary
 *   summary  = woocommerce_single_product_summary
 *   below    = woocommerce_after_single_product_summary (tabs, upsells, related)
 * Templates 1–7 differ in arrangement and styling. The settings add colors, sizes, show/hide options,
 * a features section and extra tabs.
 *
 * Classic themes: content-single-product.php. Block themes: a page template with the theme's header and
 * footer parts around the layout.
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Product_Page class.
 */
class ShopGlut_Product_Page {

	/**
	 * Module URL.
	 *
	 * @var string
	 */
	private $url;

	/**
	 * Layout of the current product (false = not looked up).
	 *
	 * @var array|null|false
	 */
	private $layout = false;

	/**
	 * Constructor.
	 *
	 * @param string $url Module URL.
	 */
	public function __construct( $url ) {
		$this->url = $url;
		add_filter( 'wc_get_template_part', array( $this, 'template_part' ), 50, 3 );
		add_filter( 'template_include', array( $this, 'block_theme_template' ), 50 );
		add_filter( 'woocommerce_product_tabs', array( $this, 'tabs' ), 50 );
		add_filter( 'woocommerce_output_related_products_args', array( $this, 'related_args' ), 50 );
		add_action( 'wp', array( $this, 'adjust_hooks' ), 20 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ), 30 );
		add_action( 'wp_enqueue_scripts', array( $this, 'gallery_scripts' ), 999 );
	}

	/**
	 * Gallery, tabs and variation scripts. WooCommerce leaves them out (and dequeues wc-single-product) on
	 * block themes; its Classic Template block adds them back the same way.
	 *
	 * @return void
	 */
	public function gallery_scripts() {
		if ( ! $this->layout() || ! wp_is_block_theme() ) {
			return;
		}
		if ( current_theme_supports( 'wc-product-gallery-zoom' ) ) {
			wp_enqueue_script( 'wc-zoom' );
		}
		if ( current_theme_supports( 'wc-product-gallery-slider' ) ) {
			wp_enqueue_script( 'wc-flexslider' );
		}
		if ( current_theme_supports( 'wc-product-gallery-lightbox' ) ) {
			wp_enqueue_script( 'wc-photoswipe-ui-default' );
			wp_enqueue_style( 'photoswipe-default-skin' );
			if ( false === has_action( 'wp_footer', 'woocommerce_photoswipe' ) ) {
				add_action( 'wp_footer', 'woocommerce_photoswipe' );
			}
		}
		wp_enqueue_script( 'wc-single-product' );
	}

	/* ---------------------------------------------------------------------
	 * Layout and settings
	 * ------------------------------------------------------------------- */

	/**
	 * Layout of the main product.
	 *
	 * @return array|null
	 */
	private function layout() {
		if ( false === $this->layout ) {
			$this->layout = null;
			if ( function_exists( 'is_product' ) && is_product() ) {
				$id           = (int) get_queried_object_id();
				$this->layout = shopglut_get_active_layout( 'product-page', array( 'product_id' => $id ) );
				// Pro templates without ShopGlut Pro's front end fall back to template 1.
			}
		}
		return $this->layout;
	}

	/**
	 * A setting at any depth.
	 *
	 * @param string $key     Key.
	 * @param mixed  $default Default.
	 * @return mixed
	 */
	private function get( $key, $default ) {
		$layout = $this->layout();
		if ( ! $layout ) {
			return $default;
		}
		$found = self::find( (array) $layout['settings'], $key );
		if ( is_array( $found ) && isset( $found[ $key ] ) ) {
			$found = $found[ $key ];
		}
		return ( null === $found || '' === $found ) ? $default : $found;
	}

	/**
	 * Find a key in nested settings.
	 *
	 * @param array  $arr Settings.
	 * @param string $key Key.
	 * @return mixed|null
	 */
	private static function find( $arr, $key ) {
		foreach ( $arr as $k => $v ) {
			if ( $k === $key ) {
				return $v;
			}
			if ( is_array( $v ) && 'display' !== $k ) {
				$f = self::find( $v, $key );
				if ( null !== $f ) {
					return $f;
				}
			}
		}
		return null;
	}

	/**
	 * Template number 1–7.
	 *
	 * @return int
	 */
	private function number() {
		$layout = $this->layout();
		$n      = $layout ? (int) preg_replace( '/\D/', '', (string) $layout['template'] ) : 1;
		return ( 0 === strpos( (string) ( $layout['template'] ?? '' ), 'template' ) && $n >= 1 && $n <= 7 ) ? $n : 1;
	}

	/* ---------------------------------------------------------------------
	 * Output
	 * ------------------------------------------------------------------- */

	/**
	 * Classic themes: our content-single-product.
	 *
	 * @param string $template Template.
	 * @param string $slug     Slug.
	 * @param string $name     Name.
	 * @return string
	 */
	public function template_part( $template, $slug, $name ) {
		if ( 'content' === $slug && 'single-product' === $name && $this->layout() ) {
			return __DIR__ . '/content-single-product.php';
		}
		return $template;
	}

	/**
	 * Block themes: our page template (theme header and footer around the layout).
	 *
	 * @param string $template Template.
	 * @return string
	 */
	public function block_theme_template( $template ) {
		if ( wp_is_block_theme() && $this->layout() ) {
			// WooCommerce's block-template compatibility layer removes the default product hooks whenever a
			// block renders; this page renders WooCommerce's PHP parts itself, so the layer is turned off.
			add_filter( 'woocommerce_disable_compatibility_layer', '__return_true' );
			return __DIR__ . '/block-template.php';
		}
		return $template;
	}

	/**
	 * Show / hide parts of the summary and gallery.
	 *
	 * @return void
	 */
	public function adjust_hooks() {
		if ( ! $this->layout() ) {
			return;
		}
		// The gallery slider, zoom and lightbox (block themes do not declare them).
		if ( ! did_action( 'wp_enqueue_scripts' ) ) {
			add_theme_support( 'wc-product-gallery-slider' );
			if ( $this->get( 'enable_image_hover_zoom', true ) ) {
				add_theme_support( 'wc-product-gallery-zoom' );
			}
			if ( $this->get( 'enable_image_lightbox', true ) ) {
				add_theme_support( 'wc-product-gallery-lightbox' );
			}
		}
		if ( ! $this->get( 'show_rating', true ) ) {
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
		}
		if ( ! $this->get( 'show_description', true ) ) {
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
		}
		if ( ! $this->get( 'show_related_products', true ) ) {
			remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
		}
		if ( $this->get( 'show_features_section', false ) && false === has_action( 'woocommerce_single_product_summary', array( $this, 'features' ) ) ) {
			add_action( 'woocommerce_single_product_summary', array( $this, 'features' ), 45 );
		}
	}

	/**
	 * The layout (used by both templates).
	 *
	 * @return void
	 */
	public function render() {
		global $product;
		$layout = $this->layout();
		if ( ! $layout || ! $product instanceof WC_Product ) {
			return;
		}
		$n = $this->number();
		$this->ensure_hooks();
		// Badges and other modules print through the classic WooCommerce hooks here.
		++ShopGlut_Legacy_Template::$depth;

		do_action( 'woocommerce_before_single_product' );
		if ( post_password_required() ) {
			echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core form.
			--ShopGlut_Legacy_Template::$depth;
			return;
		}
		?>
		<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'sgpp sgpp-t' . $n . ' sgpp-l' . (int) $layout['id'], $product ); ?>>
			<div class="sgpp-top">
				<div class="sgpp-gallery">
					<?php do_action( 'woocommerce_before_single_product_summary' ); ?>
				</div>
				<div class="sgpp-summary summary entry-summary">
					<div class="sgpp-summary-inner">
						<?php do_action( 'woocommerce_single_product_summary' ); ?>
					</div>
				</div>
			</div>
			<div class="sgpp-below">
				<?php do_action( 'woocommerce_after_single_product_summary' ); ?>
			</div>
		</div>
		<?php
		do_action( 'woocommerce_after_single_product' );
		--ShopGlut_Legacy_Template::$depth;
		echo $this->css(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from sanitized values.
	}

	/**
	 * WooCommerce's default single product callbacks. In block themes WooCommerce removes them (blocks
	 * render those parts); the layout needs them back. Show / hide settings are applied afterwards.
	 *
	 * @return void
	 */
	private function ensure_hooks() {
		$defaults = array(
			array( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 ),
			array( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 ),
			array( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 ),
			array( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 ),
			array( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 ),
			array( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 ),
			array( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 ),
			array( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 ),
			array( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 ),
			array( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 ),
			array( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 ),
			array( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 ),
			array( 'woocommerce_simple_add_to_cart', 'woocommerce_simple_add_to_cart', 30 ),
			array( 'woocommerce_grouped_add_to_cart', 'woocommerce_grouped_add_to_cart', 30 ),
			array( 'woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 30 ),
			array( 'woocommerce_external_add_to_cart', 'woocommerce_external_add_to_cart', 30 ),
			array( 'woocommerce_single_variation', 'woocommerce_single_variation', 10 ),
			array( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20 ),
		);
		if ( wp_is_block_theme() ) {
			foreach ( $defaults as $d ) {
				if ( false === has_action( $d[0], $d[1] ) ) {
					add_action( $d[0], $d[1], $d[2] );
				}
			}
		}
		$this->adjust_hooks();
	}

	/**
	 * Features section in the summary.
	 *
	 * @return void
	 */
	public function features() {
		$items = (array) $this->get( 'product_features', $this->get( 'features_list', array() ) );
		$rows  = array();
		foreach ( $items as $item ) {
			if ( is_array( $item ) && ( ! empty( $item['feature_title'] ) || ! empty( $item['feature_description'] ) ) ) {
				$rows[] = $item;
			}
		}
		if ( ! $rows ) {
			return;
		}
		$title = (string) $this->get( 'features_section_title', '' );
		echo '<div class="sgpp-features">';
		if ( '' !== $title && $this->get( 'show_features_section_title', true ) ) {
			echo '<h3 class="sgpp-features-title">' . esc_html( $title ) . '</h3>';
		}
		echo '<div class="sgpp-features-grid" style="--sgpp-fcols:' . esc_attr( max( 1, min( 4, (int) $this->get( 'features_grid_columns', 2 ) ) ) ) . '">';
		foreach ( $rows as $item ) {
			$icon = '';
			if ( ! empty( $item['feature_custom_image']['url'] ) ) {
				$icon = '<img src="' . esc_url( $item['feature_custom_image']['url'] ) . '" alt="">';
			} elseif ( ! empty( $item['feature_fontawesome_icon'] ) ) {
				$icon = shopglut_fa_icon( (string) $item['feature_fontawesome_icon'] );
			}
			$title = esc_html( (string) ( $item['feature_title'] ?? '' ) );
			if ( ! empty( $item['feature_link_enabled'] ) && ! empty( $item['feature_link_url'] ) ) {
				$title = '<a href="' . esc_url( $item['feature_link_url'] ) . '"' . ( '_blank' === ( $item['feature_link_target'] ?? '' ) ? ' target="_blank" rel="noopener"' : '' ) . '>' . $title . '</a>';
			}
			echo '<div class="sgpp-feature">' . ( $icon ? '<span class="sgpp-feature-icon">' . $icon . '</span>' : '' ) . '<div><strong>' . $title . '</strong>' . ( ! empty( $item['feature_description'] ) ? '<p>' . esc_html( $item['feature_description'] ) . '</p>' : '' ) . '</div></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
		}
		echo '</div></div>';
	}

	/**
	 * Extra tabs from the layout ("Product tabs").
	 *
	 * @param array $tabs Tabs.
	 * @return array
	 */
	public function tabs( $tabs ) {
		if ( ! $this->layout() ) {
			return $tabs;
		}
		$i = 0;
		foreach ( (array) $this->get( 'product_tabs_list', array() ) as $row ) {
			if ( ! is_array( $row ) || empty( $row['tab_title'] ) || empty( $row['tab_content'] ) ) {
				continue;
			}
			++$i;
			$content                   = (string) $row['tab_content'];
			$tabs[ 'shopglut_tab_' . $i ] = array(
				'title'    => (string) $row['tab_title'],
				'priority' => 40 + $i,
				'callback' => static function () use ( $content ) {
					echo wp_kses_post( wpautop( do_shortcode( $content ) ) );
				},
			);
		}
		return $tabs;
	}

	/**
	 * Related products per row.
	 *
	 * @param array $args Args.
	 * @return array
	 */
	public function related_args( $args ) {
		if ( $this->layout() ) {
			$cols                   = max( 2, min( 6, (int) $this->get( 'related_products_per_row', 4 ) ) );
			$args['columns']        = $cols;
			$args['posts_per_page'] = $cols;
		}
		return $args;
	}

	/**
	 * Styles from the settings.
	 *
	 * @return string
	 */
	private function css() {
		$layout = $this->layout();
		$c      = function ( $key, $default ) {
			$v = (string) $this->get( $key, '' );
			return preg_match( '/^(#[0-9a-fA-F]{3,8}|rgba?\([0-9.,\s%]+\))$/', $v ) ? $v : $default;
		};
		$num    = function ( $key, $default ) {
			$v = $this->get( $key, $default );
			if ( is_array( $v ) ) {
				$v = reset( $v );
			}
			return is_numeric( $v ) ? (float) $v : (float) $default;
		};
		$vars   = array(
			'--sgpp-title'       => $c( 'product_title_color', '' ),
			'--sgpp-title-size'  => $this->get( 'product_title_font_size', '' ) ? $num( 'product_title_font_size', 32 ) . 'px' : '',
			'--sgpp-price'       => $c( 'current_price_color', '' ),
			'--sgpp-price-size'  => $this->get( 'current_price_font_size', '' ) ? $num( 'current_price_font_size', 24 ) . 'px' : '',
			'--sgpp-old-price'   => $c( 'original_price_color', '' ),
			'--sgpp-star'        => $c( 'star_color', '' ),
			'--sgpp-desc'        => $c( 'description_color', '' ),
			'--sgpp-btn-bg'      => $c( 'cart_button_background', '' ),
			'--sgpp-btn-color'   => $c( 'cart_button_text_color', '' ),
			'--sgpp-btn-hover'   => $c( 'cart_button_hover_background', '' ),
			'--sgpp-btn-radius'  => $this->get( 'cart_button_border_radius', '' ) !== '' ? $num( 'cart_button_border_radius', 8 ) . 'px' : '',
			'--sgpp-img-bg'      => $c( 'main_image_background', '' ),
			'--sgpp-img-radius'  => $this->get( 'main_image_border_radius', '' ) !== '' ? $num( 'main_image_border_radius', 12 ) . 'px' : '',
			'--sgpp-tab'         => $c( 'tab_title_color', '' ),
			'--sgpp-tab-active'  => $c( 'tab_title_active_color', '' ),
			'--sgpp-feature-bg'  => $c( 'features_background_color', '' ),
		);
		$css    = '';
		foreach ( $vars as $var => $value ) {
			if ( '' !== $value ) {
				$css .= $var . ':' . $value . ';';
			}
		}
		$out = $css ? '.sgpp-l' . (int) $layout['id'] . '{' . $css . '}' : '';
		if ( ! $this->get( 'show_thumbnails', true ) ) {
			$out .= '.sgpp-l' . (int) $layout['id'] . ' .flex-control-thumbs{display:none}';
		}
		return $out ? '<style>' . wp_strip_all_tags( $out ) . '</style>' : '';
	}

	/**
	 * Styles of the layouts.
	 *
	 * @return void
	 */
	public function enqueue() {
		if ( $this->layout() ) {
			wp_enqueue_style( 'shopglut-product-page', $this->url . 'frontend/assets/product-page.css', array(), SHOPGLUT_VERSION );
		}
	}
}
