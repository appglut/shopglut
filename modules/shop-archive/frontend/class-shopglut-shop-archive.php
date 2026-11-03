<?php
/**
 * Shop & Archive front end.
 *
 * The active layout (Display Rules: shop page, categories, tags, search) draws the product cards of the
 * page's own product query, so sorting, filters and paging keep working:
 * - classic themes: the loop start/end and the content-product template part,
 * - block themes: the inherited Product Collection block.
 *
 * Card templates 1–10 share one renderer and differ in markup details and CSS (frontend/assets/cards.css).
 * Wishlist and Product Badges modules plug into the cards when they are on.
 *
 * Saved shape: settings[shopg_options_settings][shopg_settings_options][accordion][field]; values are
 * looked up by key at any depth.
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Shop_Archive class.
 */
class ShopGlut_Shop_Archive {

	/**
	 * Module URL.
	 *
	 * @var string
	 */
	private $url;

	/**
	 * Layout for this page (false = not looked up yet).
	 *
	 * @var array|null|false
	 */
	private $layout = false;

	/**
	 * Inside our classic loop wrapper.
	 *
	 * @var bool
	 */
	private $in_loop = false;

	/**
	 * Constructor.
	 *
	 * @param string $url Module URL.
	 */
	public function __construct( $url ) {
		$this->url = $url;
		add_action( 'pre_get_posts', array( $this, 'query' ), 15 );
		add_filter( 'woocommerce_product_loop_start', array( $this, 'loop_start' ), 50 );
		add_filter( 'woocommerce_product_loop_end', array( $this, 'loop_end' ), 50 );
		add_filter( 'wc_get_template_part', array( $this, 'template_part' ), 50, 3 );
		add_filter( 'render_block_woocommerce/product-collection', array( $this, 'block' ), 5, 2 );
		add_action( 'wp_enqueue_scripts', array( $this, 'register' ), 30 );
		// Block themes render templates before wp_enqueue_scripts, where WooCommerce's Product Button block
		// removes the classic add-to-cart script; our cards need it, so it is added back afterwards.
		add_action( 'wp_enqueue_scripts', array( $this, 'restore_add_to_cart' ), 100 );
	}

	/**
	 * Whether cards were printed on this page.
	 *
	 * @var bool
	 */
	private $used = false;

	/**
	 * Re-add WooCommerce's AJAX add to cart script when cards are on the page.
	 *
	 * @return void
	 */
	public function restore_add_to_cart() {
		if ( $this->used && 'yes' === get_option( 'woocommerce_enable_ajax_add_to_cart' ) ) {
			wp_enqueue_script( 'wc-add-to-cart' );
		}
	}

	/* ---------------------------------------------------------------------
	 * Layout and settings
	 * ------------------------------------------------------------------- */

	/**
	 * Context for the Display Rules.
	 *
	 * @return array
	 */
	private static function context() {
		if ( is_search() && 'product' === get_query_var( 'post_type' ) ) {
			return array( 'page' => 'search' );
		}
		if ( is_product_category() ) {
			return array(
				'page'    => 'categories',
				'term_id' => (int) get_queried_object_id(),
			);
		}
		if ( is_product_tag() ) {
			return array( 'page' => 'tags' );
		}
		if ( is_shop() ) {
			return array( 'page' => 'shop' );
		}
		return array( 'page' => '' );
	}

	/**
	 * The layout of this page.
	 *
	 * @return array|null
	 */
	private function layout() {
		if ( false === $this->layout ) {
			$ctx          = self::context();
			$this->layout = $ctx['page'] ? shopglut_get_active_layout( 'shop-archive', $ctx ) : null;
		}
		return $this->layout;
	}

	/**
	 * A setting at any depth of the layout settings.
	 *
	 * @param array  $layout  Layout.
	 * @param string $key     Key.
	 * @param mixed  $default Default.
	 * @return mixed
	 */
	private static function get( $layout, $key, $default ) {
		$found = self::find( isset( $layout['settings']['shopg_options_settings'] ) ? (array) $layout['settings']['shopg_options_settings'] : array(), $key );
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
			if ( is_array( $v ) ) {
				$f = self::find( $v, $key );
				if ( null !== $f ) {
					return $f;
				}
			}
		}
		return null;
	}

	/**
	 * Template number 1–10 (Pro templates fall back to 1 when their code is missing).
	 *
	 * @param array $layout Layout.
	 * @return int
	 */
	private static function number( $layout ) {
		$n = (int) preg_replace( '/\D/', '', (string) $layout['template'] );
		return ( $n >= 1 && $n <= 10 ) ? $n : 1;
	}

	/* ---------------------------------------------------------------------
	 * Query
	 * ------------------------------------------------------------------- */

	/**
	 * Products per page and include / exclude rules on the main product query.
	 *
	 * @param WP_Query $q Query.
	 * @return void
	 */
	public function query( $q ) {
		if ( is_admin() || ! $q->is_main_query() || ! ( $q->is_post_type_archive( 'product' ) || $q->is_tax( array( 'product_cat', 'product_tag' ) ) || ( $q->is_search() && 'product' === $q->get( 'post_type' ) ) ) ) {
			return;
		}
		// Display Rules need the queried object, which exists once the query is parsed.
		$ctx = array( 'page' => '' );
		if ( $q->is_search() ) {
			$ctx['page'] = 'search';
		} elseif ( $q->is_tax( 'product_cat' ) ) {
			$term = get_term_by( 'slug', (string) $q->get( 'product_cat' ), 'product_cat' );
			$ctx  = array(
				'page'    => 'categories',
				'term_id' => $term ? (int) $term->term_id : 0,
			);
		} elseif ( $q->is_tax( 'product_tag' ) ) {
			$ctx['page'] = 'tags';
		} else {
			$ctx['page'] = 'shop';
		}
		$layout = shopglut_get_active_layout( 'shop-archive', $ctx );
		if ( ! $layout ) {
			return;
		}
		$per = absint( self::get( $layout, 'pagination-product-no', 0 ) );
		if ( $per ) {
			$q->set( 'posts_per_page', min( 100, $per ) );
		}
		$exclude = array_filter( array_map( 'absint', (array) self::get( $layout, 'shopg-layouts-exclude-products', array() ) ) );
		if ( $exclude ) {
			$q->set( 'post__not_in', array_merge( (array) $q->get( 'post__not_in' ), $exclude ) );
		}
		$tax = (array) $q->get( 'tax_query' );
		foreach ( array( 'shopg-layouts-exclude-categories' => 'product_cat', 'shopg-layouts-exclude-tags' => 'product_tag' ) as $key => $taxonomy ) {
			$ids = array_filter( array_map( 'absint', (array) self::get( $layout, $key, array() ) ) );
			if ( $ids ) {
				$tax[] = array(
					'taxonomy' => $taxonomy,
					'field'    => 'term_id',
					'terms'    => $ids,
					'operator' => 'NOT IN',
				);
			}
		}
		if ( $tax ) {
			$q->set( 'tax_query', $tax );
		}
	}

	/* ---------------------------------------------------------------------
	 * Classic themes
	 * ------------------------------------------------------------------- */

	/**
	 * Loop start: our grid instead of <ul class="products">.
	 *
	 * @param string $html HTML.
	 * @return string
	 */
	public function loop_start( $html ) {
		$layout = $this->layout();
		if ( ! $layout || wc_get_loop_prop( 'is_shortcode' ) || ( wc_get_loop_prop( 'name' ) && 'products' !== wc_get_loop_prop( 'name' ) ) ) {
			return $html;
		}
		$this->in_loop = true;
		$this->enqueue();
		return $this->grid_open( $layout );
	}

	/**
	 * Loop end.
	 *
	 * @param string $html HTML.
	 * @return string
	 */
	public function loop_end( $html ) {
		if ( ! $this->in_loop ) {
			return $html;
		}
		$this->in_loop = false;
		return '</div>' . $this->styles( $this->layout() );
	}

	/**
	 * Card template for each product of our loop.
	 *
	 * @param string $template Template path.
	 * @param string $slug     Slug.
	 * @param string $name     Name.
	 * @return string
	 */
	public function template_part( $template, $slug, $name ) {
		if ( $this->in_loop && 'content' === $slug && 'product' === $name ) {
			return __DIR__ . '/content-product.php';
		}
		return $template;
	}

	/**
	 * Print the card of the current product (from content-product.php).
	 *
	 * @return void
	 */
	public function classic_card() {
		global $product;
		$layout = $this->layout();
		if ( $layout && $product instanceof WC_Product ) {
			echo $this->card( $product, $layout ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in card().
		}
	}

	/* ---------------------------------------------------------------------
	 * Block themes
	 * ------------------------------------------------------------------- */

	/**
	 * Replace the inherited Product Collection with our grid of the main query.
	 *
	 * @param string $html  Block HTML.
	 * @param array  $block Block.
	 * @return string
	 */
	public function block( $html, $block ) {
		if ( empty( $block['attrs']['query']['inherit'] ) ) {
			return $html;
		}
		$layout = $this->layout();
		if ( ! $layout ) {
			return $html;
		}
		global $wp_query;
		$this->enqueue();
		$out = $this->grid_open( $layout );
		if ( $wp_query->have_posts() ) {
			while ( $wp_query->have_posts() ) {
				$wp_query->the_post();
				$product = wc_get_product( get_the_ID() );
				if ( $product ) {
					$out .= $this->card( $product, $layout );
				}
			}
			wp_reset_postdata();
			$wp_query->rewind_posts();
		} else {
			$out .= '<p class="sgsa-empty">' . esc_html__( 'No products were found matching your selection.', 'shopglut' ) . '</p>';
		}
		$out  .= '</div>';
		$links = paginate_links(
			array(
				'total'     => (int) $wp_query->max_num_pages,
				'current'   => max( 1, (int) get_query_var( 'paged' ) ),
				'type'      => 'list',
				'prev_text' => '&larr;',
				'next_text' => '&rarr;',
			)
		);
		if ( $links ) {
			$out .= '<nav class="sgsa-pagination woocommerce-pagination" aria-label="' . esc_attr__( 'Products pagination', 'shopglut' ) . '">' . $links . '</nav>';
		}
		$align = ! empty( $block['attrs']['align'] ) ? ' align' . sanitize_html_class( $block['attrs']['align'] ) : '';
		return '<div class="sgsa-wrap wp-block-woocommerce-product-collection' . esc_attr( $align ) . '">' . $out . $this->styles( $layout ) . '</div>';
	}

	/* ---------------------------------------------------------------------
	 * Cards
	 * ------------------------------------------------------------------- */

	/**
	 * Grid opening tag.
	 *
	 * @param array $layout Layout.
	 * @return string
	 */
	private function grid_open( $layout ) {
		$cols = (int) preg_replace( '/\D/', '', (string) self::get( $layout, 'shopg-column-grid', 'col-3' ) );
		$cols = $cols >= 1 && $cols <= 6 ? $cols : 3;
		$n    = self::number( $layout );
		if ( in_array( $n, array( 5, 6 ), true ) ) {
			$cols = min( $cols, 2 ); // List designs.
		}
		return '<div class="sgsa sgsa-t' . $n . ' sgsa-l' . (int) $layout['id'] . ' products" style="--sgsa-cols:' . $cols . '">';
	}

	/**
	 * One product card.
	 *
	 * @param WC_Product $product Product.
	 * @param array      $layout  Layout.
	 * @return string
	 */
	public function card( $product, $layout ) {
		$n     = self::number( $layout );
		$id    = $product->get_id();
		$link  = $product->get_permalink();
		$on    = function ( $key ) use ( $layout ) {
			return (bool) self::get( $layout, $key, true );
		};
		$img   = $product->get_image( 'woocommerce_thumbnail', array( 'class' => 'sgsa-img' ) );
		$gal   = $product->get_gallery_image_ids();
		$hover = ( in_array( $n, array( 1, 3, 8 ), true ) && $gal ) ? wp_get_attachment_image( $gal[0], 'woocommerce_thumbnail', false, array( 'class' => 'sgsa-img sgsa-img-2' ) ) : '';
		$new_tab = (bool) self::get( $layout, 'image-product-open-newtab', false );
		$link_img = 'disable' !== self::get( $layout, 'image-link-option', 'enable' );

		// Badges: the Product Badges module when it is on, otherwise simple built-in ones.
		$badges = '';
		if ( $on( 'shopg-show-badge' ) ) {
			if ( isset( $GLOBALS['shopglut_product_badges'] ) ) {
				$badges = $GLOBALS['shopglut_product_badges']->image_html( $product );
			} else {
				$items = array();
				if ( $product->is_on_sale() ) {
					$regular = (float) $product->get_regular_price();
					$sale    = (float) $product->get_sale_price();
					$items[] = '<span class="sgsa-badge sgsa-badge--sale">' . ( $regular > 0 && $sale > 0 ? '-' . round( ( $regular - $sale ) / $regular * 100 ) . '%' : esc_html__( 'Sale', 'shopglut' ) ) . '</span>';
				}
				if ( ! $product->is_in_stock() ) {
					$items[] = '<span class="sgsa-badge sgsa-badge--out">' . esc_html__( 'Sold out', 'shopglut' ) . '</span>';
				} elseif ( $product->is_featured() ) {
					$items[] = '<span class="sgsa-badge sgsa-badge--featured">' . esc_html__( 'Featured', 'shopglut' ) . '</span>';
				}
				$badges = $items ? '<div class="sgsa-badges">' . implode( '', $items ) . '</div>' : '';
			}
		}

		$wishlist = '';
		if ( isset( $GLOBALS['shopglut_wishlist'] ) && (bool) self::get( $layout, 'enable_wishlist', true ) ) {
			$wishlist = str_replace( 'shopglut-wl-btn--', 'sgsa-wl shopglut-wl-btn--', $GLOBALS['shopglut_wishlist']->button( $id, 'shop' ) );
		}

		$category = '';
		if ( $on( 'shopg-show-category' ) ) {
			$terms = get_the_terms( $id, 'product_cat' );
			if ( $terms && ! is_wp_error( $terms ) ) {
				$category = '<a class="sgsa-cat" href="' . esc_url( get_term_link( $terms[0] ) ) . '">' . esc_html( $terms[0]->name ) . '</a>';
			}
		}

		$rating = '';
		if ( $on( 'shopg-show-review' ) && wc_review_ratings_enabled() ) {
			$avg    = (float) $product->get_average_rating();
			/* translators: %s: rating */
			$rating = '<div class="sgsa-rating" role="img" aria-label="' . esc_attr( sprintf( __( 'Rated %s out of 5', 'shopglut' ), $avg ) ) . '"><span style="--sgsa-r:' . ( $avg / 5 * 100 ) . '%"></span>' . ( $product->get_review_count() ? '<em>(' . (int) $product->get_review_count() . ')</em>' : '' ) . '</div>';
		}

		$button = '';
		if ( $on( 'show-add-to-cart' ) ) {
			$text = $product->add_to_cart_text();
			if ( $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock() ) {
				$custom = trim( (string) self::get( $layout, 'shopg-change-cart-text', '' ) );
				$text   = '' !== $custom ? $custom : $text;
			}
			$classes = array_filter(
				array(
					'button',
					'sgsa-btn',
					'product_type_' . $product->get_type(),
					$product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
					$product->supports( 'ajax_add_to_cart' ) && $product->is_purchasable() && $product->is_in_stock() ? 'ajax_add_to_cart' : '',
				)
			);
			$button  = sprintf(
				'<a href="%1$s" data-quantity="1" data-product_id="%2$d" data-product_sku="%3$s" class="%4$s" aria-label="%5$s" rel="nofollow">%6$s<span>%7$s</span></a>',
				esc_url( $product->add_to_cart_url() ),
				$id,
				esc_attr( $product->get_sku() ),
				esc_attr( implode( ' ', $classes ) ),
				esc_attr( $product->add_to_cart_description() ),
				in_array( $n, array( 1, 4, 8, 10 ), true ) ? shopglut_icon( 'shopping-bag' ) : '',
				esc_html( $text )
			);
		}

		$title = $on( 'shopg-show-title' ) ? '<h2 class="sgsa-title woocommerce-loop-product__title"><a href="' . esc_url( $link ) . '">' . esc_html( $product->get_name() ) . '</a></h2>' : '';
		$price = $on( 'shopg-show-price' ) ? '<div class="sgsa-price price">' . wp_kses_post( $product->get_price_html() ) . '</div>' : '';
		$excerpt = ( 5 === $n ) ? '<div class="sgsa-excerpt">' . wp_kses_post( wp_trim_words( $product->get_short_description(), 22 ) ) . '</div>' : '';

		$media = '';
		if ( $on( 'shopg-show-image' ) ) {
			$inner = $img . $hover;
			$media = '<div class="sgsa-media">' . ( $link_img ? '<a class="sgsa-media-link" href="' . esc_url( $link ) . '"' . ( $new_tab ? ' target="_blank" rel="noopener"' : '' ) . '>' . $inner . '</a>' : $inner ) . $badges . ( $wishlist ? '<div class="sgsa-actions">' . $wishlist . '</div>' : '' ) . ( 8 === $n ? '<div class="sgsa-reveal">' . $button . '</div>' : '' ) . '</div>';
		}

		$body = '<div class="sgsa-body">' . $category . $title . $rating . $excerpt . '<div class="sgsa-foot">' . $price . ( 8 === $n ? '' : $button ) . '</div></div>';

		return '<div class="sgsa-card product type-product post-' . $id . ( $product->is_on_sale() ? ' sale' : '' ) . ( $product->is_in_stock() ? ' instock' : ' outofstock' ) . '" data-product-id="' . $id . '">' . $media . $body . '</div>';
	}

	/* ---------------------------------------------------------------------
	 * Styles
	 * ------------------------------------------------------------------- */

	/**
	 * Layout CSS from the Style settings.
	 *
	 * @param array|null $layout Layout.
	 * @return string
	 */
	private function styles( $layout ) {
		static $done = array();
		if ( ! $layout || isset( $done[ $layout['id'] ] ) ) {
			return '';
		}
		$done[ $layout['id'] ] = true;
		$c   = function ( $key, $default ) use ( $layout ) {
			$v = self::get( $layout, $key, $default );
			if ( is_array( $v ) ) {
				$v = $v['color'] ?? ( $v['background-color'] ?? $default );
			}
			$v = (string) $v;
			return preg_match( '/^(#[0-9a-fA-F]{3,8}|rgba?\([0-9.,\s%]+\)|transparent)$/', $v ) ? $v : $default;
		};
		$num = function ( $key, $default ) use ( $layout ) {
			$v = self::get( $layout, $key, $default );
			if ( is_array( $v ) ) {
				$v = $v['slider_value_desktop'] ?? ( $v['top'] ?? ( $v[ $key ] ?? reset( $v ) ) );
			}
			return is_numeric( $v ) ? (float) $v : (float) $default;
		};
		$title = self::get( $layout, 'product-title', array() );
		$price = self::get( $layout, 'product-price', array() );
		$sel   = '.sgsa-l' . (int) $layout['id'];
		$css   = $sel . '{--sgsa-gap-x:' . $num( 'shopg-column-gap', 24 ) . 'px;--sgsa-gap-y:' . $num( 'shopg-row-gap', 32 ) . 'px'
			. ';--sgsa-card-bg:' . $c( 'product-normal-background', 'var(--sgsa-card-bg-d)' )
			. ';--sgsa-card-hover:' . $c( 'product-hover-background', 'var(--sgsa-card-bg-d)' )
			. ';--sgsa-radius:' . $num( 'product-radius', -1 ) . 'px'
			. ';--sgsa-btn-bg:' . $c( 'add-to-cart-bg-color', 'var(--sgsa-btn-bg-d)' )
			. ';--sgsa-btn-color:' . $c( 'add-to-cart-font-color', 'var(--sgsa-btn-color-d)' )
			. ';--sgsa-btn-hover:' . $c( 'add-to-cart-hover-bg-color', 'var(--sgsa-btn-hover-d)' )
			. ';--sgsa-btn-hover-color:' . $c( 'add-to-cart-hover-font-color', 'var(--sgsa-btn-color-d)' );
		if ( is_array( $title ) && ! empty( $title['color'] ) && preg_match( '/^#[0-9a-fA-F]{3,8}$/', $title['color'] ) ) {
			$css .= ';--sgsa-title-color:' . $title['color'];
		}
		if ( is_array( $price ) && ! empty( $price['color'] ) && preg_match( '/^#[0-9a-fA-F]{3,8}$/', $price['color'] ) ) {
			$css .= ';--sgsa-price-color:' . $price['color'];
		}
		$css .= '}';
		// A negative radius means "not set": keep the template's own.
		$css = str_replace( ';--sgsa-radius:-1px', '', $css );
		return '<style>' . wp_strip_all_tags( $css ) . '</style>';
	}

	/**
	 * Register assets.
	 *
	 * @return void
	 */
	public function register() {
		wp_register_style( 'shopglut-cards', $this->url . 'frontend/assets/cards.css', array(), SHOPGLUT_VERSION );
	}

	/**
	 * Load the card CSS and WooCommerce's AJAX add to cart.
	 *
	 * @return void
	 */
	private function enqueue() {
		$this->used = true;
		wp_enqueue_style( 'shopglut-cards' );
		if ( 'yes' === get_option( 'woocommerce_enable_ajax_add_to_cart' ) ) {
			wp_enqueue_script( 'wc-add-to-cart' );
		}
	}
}
