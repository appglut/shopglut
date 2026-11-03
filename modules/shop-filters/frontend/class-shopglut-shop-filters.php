<?php
/**
 * Shop Filters front end.
 *
 * The active filter panel shows above the products on the shop and product archive pages (classic:
 * woocommerce_before_shop_loop; blocks: before the Product Collection block), or anywhere with
 * [shopglut_filter id="N"]. Choices are sent as URL parameters; WooCommerce's own parameters are used
 * where they exist (min_price / max_price, rating_filter, filter_{attribute} + query_type_{attribute}),
 * the rest (sgf_cat, sgf_tag, sgf_stock, sgf_onsale) are applied to the main product query here.
 *
 * Saved shape: settings[shopg_filter_options_settings][shopglut-filter-settings-main-tab]
 * [shopg-filter-add-new][i][shopg-filter-accordion][shopg-filter-sub-tabbed][field].
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Shop_Filters class.
 */
class ShopGlut_Shop_Filters {

	/**
	 * Module URL.
	 *
	 * @var string
	 */
	private $url;

	/**
	 * Whether the panel was printed on this page.
	 *
	 * @var bool
	 */
	private $printed = false;

	/**
	 * Constructor.
	 *
	 * @param string $url Module URL.
	 */
	public function __construct( $url ) {
		$this->url = $url;
		add_action( 'pre_get_posts', array( $this, 'filter_query' ), 20 );
		add_action( 'woocommerce_before_shop_loop', array( $this, 'classic_panel' ), 5 );
		add_action( 'woocommerce_no_products_found', array( $this, 'classic_panel' ), 5 );
		add_filter( 'render_block_woocommerce/product-collection', array( $this, 'block_panel' ), 10, 2 );
		add_shortcode( 'shopglut_filter', array( $this, 'shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ), 30 );
	}

	/* ---------------------------------------------------------------------
	 * Settings
	 * ------------------------------------------------------------------- */

	/**
	 * The filter panel layout for this page.
	 *
	 * @return array|null
	 */
	private static function layout() {
		return shopglut_get_active_layout( 'shop-filters' );
	}

	/**
	 * Settings of the panel.
	 *
	 * @param array $layout Layout.
	 * @return array
	 */
	private static function settings( $layout ) {
		$s = isset( $layout['settings']['shopg_filter_options_settings']['shopglut-filter-settings-main-tab'] ) ? $layout['settings']['shopg_filter_options_settings']['shopglut-filter-settings-main-tab'] : array();
		return is_array( $s ) ? $s : array();
	}

	/**
	 * Filters of the panel (flat field arrays).
	 *
	 * @param array $layout Layout.
	 * @return array
	 */
	private static function items( $layout ) {
		$s   = self::settings( $layout );
		$out = array();
		foreach ( (array) ( $s['shopg-filter-add-new'] ?? array() ) as $row ) {
			$item = $row['shopg-filter-accordion']['shopg-filter-sub-tabbed'] ?? $row;
			if ( is_array( $item ) && ! empty( $item['filter-type'] ) ) {
				$out[] = $item;
			}
		}
		return $out;
	}

	/**
	 * Whether the panel shows on this page ("Show On Pages").
	 *
	 * @param array $layout Layout.
	 * @return bool
	 */
	private static function shows_here( $layout ) {
		if ( ! function_exists( 'is_shop' ) || ! ( is_shop() || is_product_taxonomy() ) ) {
			return false;
		}
		$pages = (array) ( self::settings( $layout )['filter-show-on-pages'] ?? array() );
		return ! $pages || in_array( 'Woo Shop Page', $pages, true );
	}

	/* ---------------------------------------------------------------------
	 * Query
	 * ------------------------------------------------------------------- */

	/**
	 * Comma list from the URL.
	 *
	 * @param string $key Parameter.
	 * @return string[]
	 */
	private static function param_list( $key ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- public filter parameters.
		$raw = isset( $_GET[ $key ] ) ? sanitize_text_field( wp_unslash( $_GET[ $key ] ) ) : '';
		return array_values( array_filter( array_map( 'sanitize_title', explode( ',', $raw ) ) ) );
	}

	/**
	 * Apply sgf_* parameters to the main product query.
	 *
	 * @param WP_Query $q Query.
	 * @return void
	 */
	public function filter_query( $q ) {
		if ( is_admin() || ! $q->is_main_query() || ! ( $q->is_post_type_archive( 'product' ) || $q->is_tax( get_object_taxonomies( 'product' ) ) ) ) {
			return;
		}
		$tax = (array) $q->get( 'tax_query' );
		foreach ( array( 'sgf_cat' => 'product_cat', 'sgf_tag' => 'product_tag' ) as $param => $taxonomy ) {
			$terms = self::param_list( $param );
			if ( $terms ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$and   = isset( $_GET[ $param . '_type' ] ) && 'and' === $_GET[ $param . '_type' ];
				$tax[] = array(
					'taxonomy' => $taxonomy,
					'field'    => 'slug',
					'terms'    => $terms,
					'operator' => $and ? 'AND' : 'IN',
				);
			}
		}
		if ( count( $tax ) > 0 ) {
			$q->set( 'tax_query', $tax );
		}
		$stock = array_intersect( self::param_list( 'sgf_stock' ), array( 'instock', 'outofstock', 'onbackorder' ) );
		if ( $stock ) {
			$meta   = (array) $q->get( 'meta_query' );
			$meta[] = array(
				'key'     => '_stock_status',
				'value'   => array_values( $stock ),
				'compare' => 'IN',
			);
			$q->set( 'meta_query', $meta );
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_GET['sgf_onsale'] ) ) {
			$ids = wc_get_product_ids_on_sale();
			$in  = (array) $q->get( 'post__in' );
			$q->set( 'post__in', $in ? array_intersect( $in, $ids ) : ( $ids ? $ids : array( 0 ) ) );
		}
	}

	/* ---------------------------------------------------------------------
	 * Output
	 * ------------------------------------------------------------------- */

	/**
	 * Classic themes: above the products.
	 *
	 * @return void
	 */
	public function classic_panel() {
		$layout = self::layout();
		if ( ! $this->printed && shopglut_classic_hooks() && $layout && self::shows_here( $layout ) ) {
			echo $this->panel( $layout ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in panel().
		}
	}

	/**
	 * Block themes: before the shop's Product Collection block.
	 *
	 * @param string $html  Block HTML.
	 * @param array  $block Block.
	 * @return string
	 */
	public function block_panel( $html, $block ) {
		if ( $this->printed || empty( $block['attrs']['query']['inherit'] ) ) {
			return $html;
		}
		$layout = self::layout();
		return ( $layout && self::shows_here( $layout ) ) ? $this->panel( $layout ) . $html : $html;
	}

	/**
	 * [shopglut_filter id="N"].
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	public function shortcode( $atts ) {
		$atts   = shortcode_atts( array( 'id' => 0 ), $atts );
		$layout = $atts['id'] ? ShopGlut_Layouts::get( absint( $atts['id'] ) ) : self::layout();
		return ( $layout && 'shop-filters' === $layout['module'] ) ? $this->panel( $layout ) : '';
	}

	/**
	 * Base URL of the results (the current shop / archive page, without paging and our parameters).
	 *
	 * @return string
	 */
	private static function base_url() {
		if ( is_product_taxonomy() ) {
			$url = get_term_link( get_queried_object() );
		} else {
			$url = wc_get_page_permalink( 'shop' );
		}
		return is_wp_error( $url ) ? home_url( '/' ) : $url;
	}

	/**
	 * The panel.
	 *
	 * @param array $layout Layout.
	 * @return string
	 */
	public function panel( $layout ) {
		$this->printed = true;
		wp_enqueue_style( 'shopglut-filters' );
		wp_enqueue_script( 'shopglut-filters' );
		$items = self::items( $layout );
		if ( ! $items ) {
			return '';
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- keep the sort order.
		$orderby = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : '';
		ob_start();
		?>
		<form class="shopglut-filters" method="get" action="<?php echo esc_url( self::base_url() ); ?>" data-layout="<?php echo esc_attr( $layout['id'] ); ?>">
			<div class="sgf-head">
				<button type="button" class="sgf-toggle" aria-expanded="true"><?php echo shopglut_icon( 'filter' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php esc_html_e( 'Filters', 'shopglut' ); ?></button>
				<?php echo $this->active_chips(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside. ?>
			</div>
			<div class="sgf-groups">
				<?php
				foreach ( $items as $i => $item ) {
					echo $this->group( $item, $i ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside.
				}
				?>
			</div>
			<?php if ( $orderby ) : ?>
				<input type="hidden" name="orderby" value="<?php echo esc_attr( $orderby ); ?>">
			<?php endif; ?>
			<div class="sgf-actions">
				<button type="submit" class="sgf-apply wp-element-button"><?php esc_html_e( 'Apply filters', 'shopglut' ); ?></button>
				<a class="sgf-reset" href="<?php echo esc_url( self::base_url() ); ?>"><?php esc_html_e( 'Reset', 'shopglut' ); ?></a>
			</div>
		</form>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Chips for the filters in use.
	 *
	 * @return string
	 */
	private function active_chips() {
		$chips = array();
		$base  = remove_query_arg( 'paged' );
		foreach ( array( 'sgf_cat' => 'product_cat', 'sgf_tag' => 'product_tag' ) as $param => $taxonomy ) {
			foreach ( self::param_list( $param ) as $slug ) {
				$term = get_term_by( 'slug', $slug, $taxonomy );
				if ( $term ) {
					$rest    = array_diff( self::param_list( $param ), array( $slug ) );
					$chips[] = array( $term->name, $rest ? add_query_arg( $param, implode( ',', $rest ), $base ) : remove_query_arg( $param, $base ) );
				}
			}
		}
		foreach ( wc_get_attribute_taxonomy_names() as $taxonomy ) {
			$param = 'filter_' . wc_attribute_taxonomy_slug( $taxonomy );
			foreach ( self::param_list( $param ) as $slug ) {
				$term = get_term_by( 'slug', $slug, $taxonomy );
				if ( $term ) {
					$rest    = array_diff( self::param_list( $param ), array( $slug ) );
					$chips[] = array( $term->name, $rest ? add_query_arg( $param, implode( ',', $rest ), $base ) : remove_query_arg( array( $param, 'query_type_' . wc_attribute_taxonomy_slug( $taxonomy ) ), $base ) );
				}
			}
		}
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['min_price'] ) || isset( $_GET['max_price'] ) ) {
			$min     = isset( $_GET['min_price'] ) ? (float) $_GET['min_price'] : 0;
			$max     = isset( $_GET['max_price'] ) ? (float) $_GET['max_price'] : 0;
			$chips[] = array( wp_strip_all_tags( wc_price( $min ) ) . ( $max ? ' – ' . wp_strip_all_tags( wc_price( $max ) ) : '+' ), remove_query_arg( array( 'min_price', 'max_price' ), $base ) );
		}
		if ( ! empty( $_GET['rating_filter'] ) ) {
			/* translators: %s: rating */
			$chips[] = array( sprintf( __( 'Rated %s+', 'shopglut' ), min( array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_GET['rating_filter'] ) ) ) ) ) ), remove_query_arg( 'rating_filter', $base ) );
		}
		if ( ! empty( $_GET['sgf_onsale'] ) ) {
			$chips[] = array( __( 'On sale', 'shopglut' ), remove_query_arg( 'sgf_onsale', $base ) );
		}
		// phpcs:enable
		$names = array(
			'instock'     => __( 'In stock', 'shopglut' ),
			'onbackorder' => __( 'On backorder', 'shopglut' ),
			'outofstock'  => __( 'Out of stock', 'shopglut' ),
		);
		foreach ( self::param_list( 'sgf_stock' ) as $s ) {
			if ( isset( $names[ $s ] ) ) {
				$rest    = array_diff( self::param_list( 'sgf_stock' ), array( $s ) );
				$chips[] = array( $names[ $s ], $rest ? add_query_arg( 'sgf_stock', implode( ',', $rest ), $base ) : remove_query_arg( 'sgf_stock', $base ) );
			}
		}
		if ( ! $chips ) {
			return '';
		}
		$html = '<div class="sgf-chips">';
		foreach ( $chips as $chip ) {
			/* translators: %s: filter value */
			$html .= '<a class="sgf-chip" href="' . esc_url( $chip[1] ) . '" aria-label="' . esc_attr( sprintf( __( 'Remove filter: %s', 'shopglut' ), $chip[0] ) ) . '">' . esc_html( $chip[0] ) . ' ' . shopglut_icon( 'times' ) . '</a>';
		}
		return $html . '</div>';
	}

	/**
	 * One filter group.
	 *
	 * @param array $item Filter settings.
	 * @param int   $i    Index.
	 * @return string
	 */
	private function group( $item, $i ) {
		$type  = (string) $item['filter-type'];
		$title = trim( (string) ( $item['accordion-title'] ?? '' ) );
		if ( '' === $title ) {
			$title = self::default_title( $type );
		}
		$body = $this->group_body( $item, $type );
		if ( '' === $body ) {
			return '';
		}
		$collapsed = ! empty( $item['filter-collapsed'] );
		$style     = '';
		$bg        = (string) ( $item['filter-content-bg-color'] ?? '' );
		$fg        = (string) ( $item['filter-content-font-color'] ?? '' );
		if ( preg_match( '/^#[0-9a-fA-F]{3,8}$/', $bg ) ) {
			$style .= '--sgf-bg:' . $bg . ';';
		}
		if ( preg_match( '/^#[0-9a-fA-F]{3,8}$/', $fg ) ) {
			$style .= '--sgf-fg:' . $fg . ';';
		}
		$id   = 'sgf-g-' . $i;
		$html = '<div class="sgf-group sgf-type-' . esc_attr( sanitize_html_class( $type ) ) . '"' . ( $style ? ' style="' . esc_attr( $style ) . '"' : '' ) . '>';
		if ( ! isset( $item['filter-show-title'] ) || $item['filter-show-title'] ) {
			$html .= '<button type="button" class="sgf-title" aria-expanded="' . ( $collapsed ? 'false' : 'true' ) . '" aria-controls="' . esc_attr( $id ) . '">' . esc_html( $title ) . shopglut_icon( 'chevron-down' ) . '</button>';
		}
		$html .= '<div class="sgf-body" id="' . esc_attr( $id ) . '"' . ( $collapsed ? ' hidden' : '' ) . '>' . $body . '</div></div>';
		return $html;
	}

	/**
	 * Title of a filter type.
	 *
	 * @param string $type Type.
	 * @return string
	 */
	private static function default_title( $type ) {
		$titles = array(
			'product-categories' => __( 'Categories', 'shopglut' ),
			'product-tags'       => __( 'Tags', 'shopglut' ),
			'product-price'      => __( 'Price', 'shopglut' ),
			'product-rating'     => __( 'Rating', 'shopglut' ),
			'product-stock'      => __( 'Availability', 'shopglut' ),
			'product-onsale'     => __( 'Deals', 'shopglut' ),
		);
		if ( isset( $titles[ $type ] ) ) {
			return $titles[ $type ];
		}
		return taxonomy_exists( $type ) ? wc_attribute_label( $type ) : $type;
	}

	/**
	 * Inside of a filter group.
	 *
	 * @param array  $item Filter settings.
	 * @param string $type Type.
	 * @return string
	 */
	private function group_body( $item, $type ) {
		$count = ! empty( $item['show-count'] );
		switch ( $type ) {
			case 'product-categories':
				return $this->terms_body( $item, 'product_cat', 'sgf_cat', (string) ( $item['filter-term-appearance'] ?? 'check-list' ), $count, (string) ( $item['filter-category-exclude-include-button'] ?? 'all' ), (array) ( $item['shopg-filter-include-category'] ?? array() ), (array) ( $item['shopg-filter-exclude-category'] ?? array() ) );
			case 'product-tags':
				return $this->terms_body( $item, 'product_tag', 'sgf_tag', (string) ( $item['filter-term-appearance'] ?? 'check-list' ), $count, (string) ( $item['filter-tag-exclude-include-button'] ?? 'all' ), (array) ( $item['shopg-filter-include-tag'] ?? array() ), (array) ( $item['shopg-filter-exclude-tag'] ?? array() ) );
			case 'product-price':
				return $this->price_body( $item );
			case 'product-rating':
				return $this->rating_body( $item );
			case 'product-stock':
				return $this->stock_body( $item );
			case 'product-onsale':
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$on = ! empty( $_GET['sgf_onsale'] );
				return '<label class="sgf-opt"><input type="checkbox" name="sgf_onsale" value="1"' . checked( $on, true, false ) . '> <span>' . esc_html__( 'On sale only', 'shopglut' ) . '</span></label>';
		}
		if ( taxonomy_exists( $type ) && taxonomy_is_product_attribute( $type ) ) {
			$mode = (string) ( $item[ 'filter-' . $type . '-exclude-include-button' ] ?? 'all' );
			return $this->terms_body( $item, $type, 'filter_' . wc_attribute_taxonomy_slug( $type ), (string) ( $item[ 'filter-' . $type . '-appearance' ] ?? 'check-list' ), $count, $mode, (array) ( $item[ 'shopg-filter-include-' . $type ] ?? array() ), (array) ( $item[ 'shopg-filter-exclude-' . $type ] ?? array() ) );
		}
		return '';
	}

	/**
	 * Term choices (categories, tags, attributes).
	 *
	 * @param array  $item     Filter settings.
	 * @param string $taxonomy Taxonomy.
	 * @param string $param    URL parameter.
	 * @param string $design   check-list | radio | dropdown | button | grid | cloud | color | image.
	 * @param bool   $count    Show counts.
	 * @param string $mode     all | include | exclude.
	 * @param array  $include  Term IDs to show.
	 * @param array  $exclude  Term IDs to hide.
	 * @return string
	 */
	private function terms_body( $item, $taxonomy, $param, $design, $count, $mode, $include, $exclude ) {
		$args = array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => true,
		);
		if ( 'include' === $mode && $include ) {
			$args['include'] = array_map( 'absint', $include );
		} elseif ( 'exclude' === $mode && $exclude ) {
			$args['exclude'] = array_map( 'absint', $exclude );
		}
		if ( 'product_cat' === $taxonomy ) {
			$args['exclude']   = array_merge( (array) ( $args['exclude'] ?? array() ), array( (int) get_option( 'default_product_cat' ) ) );
			$args['orderby']   = 'name';
		}
		$terms = get_terms( $args );
		if ( is_wp_error( $terms ) || ! $terms ) {
			return '';
		}
		$chosen    = self::param_list( $param );
		$is_attr   = 0 === strpos( $param, 'filter_' );
		$query_key = $is_attr ? 'query_type_' . substr( $param, 7 ) : $param . '_type';
		$qtype     = ( 'and' === ( $item['filter-query-type'] ?? 'or' ) ) ? 'and' : 'or';
		$colors    = $is_attr ? (array) ( $item[ 'filter-product-' . $taxonomy . '-color' ] ?? array() ) : array();
		$images    = $is_attr ? (array) ( $item[ 'filter-product-' . $taxonomy . '-images' ] ?? array() ) : array();
		$single    = in_array( $design, array( 'radio', 'dropdown' ), true );

		$html = '<div class="sgf-terms sgf-d-' . esc_attr( $design ) . '" data-param="' . esc_attr( $param ) . '"' . ( $single ? ' data-single="1"' : '' ) . '>';
		if ( 'dropdown' === $design ) {
			$html .= '<select class="sgf-select" aria-label="' . esc_attr( self::default_title( $taxonomy ) ) . '"><option value="">' . esc_html__( 'Any', 'shopglut' ) . '</option>';
			foreach ( $terms as $term ) {
				$html .= '<option value="' . esc_attr( $term->slug ) . '"' . selected( in_array( $term->slug, $chosen, true ), true, false ) . '>' . esc_html( $term->name ) . ( $count ? ' (' . (int) $term->count . ')' : '' ) . '</option>';
			}
			$html .= '</select>';
		} else {
			$input = 'radio' === $design ? 'radio' : 'checkbox';
			foreach ( $terms as $term ) {
				$on    = in_array( $term->slug, $chosen, true );
				$swatch = '';
				if ( 'color' === $design ) {
					$c      = (string) ( $colors[ $term->term_id ] ?? '' );
					$swatch = '<span class="sgf-swatch" style="background:' . esc_attr( preg_match( '/^(#[0-9a-fA-F]{3,8}|rgba?\([0-9.,\s%]+\))$/', $c ) ? $c : '#e5e7eb' ) . '"></span>';
				} elseif ( 'image' === $design ) {
					$img    = is_array( $images[ $term->term_id ] ?? null ) ? (string) ( $images[ $term->term_id ]['url'] ?? '' ) : '';
					$swatch = $img ? '<img class="sgf-swatch" src="' . esc_url( $img ) . '" alt="">' : '<span class="sgf-swatch"></span>';
				}
				$html .= '<label class="sgf-opt' . ( $on ? ' is-on' : '' ) . '" title="' . esc_attr( $term->name ) . '"><input type="' . $input . '" name="' . esc_attr( $param . '_' . ( 'radio' === $input ? 'r' : 'c' ) ) . '" value="' . esc_attr( $term->slug ) . '"' . checked( $on, true, false ) . '>'
					. $swatch . '<span class="sgf-name">' . esc_html( $term->name ) . '</span>' . ( $count ? '<span class="sgf-count">' . (int) $term->count . '</span>' : '' ) . '</label>';
			}
		}
		$html .= '<input type="hidden" name="' . esc_attr( $param ) . '" value="' . esc_attr( implode( ',', $chosen ) ) . '">';
		if ( ! $single ) {
			$html .= '<input type="hidden" name="' . esc_attr( $query_key ) . '" value="' . esc_attr( $qtype ) . '">';
		}
		return $html . '</div>';
	}

	/**
	 * Lowest and highest product price.
	 *
	 * @return float[]
	 */
	private static function price_range() {
		global $wpdb;
		$range = wp_cache_get( 'shopglut_price_range', 'shopglut' );
		if ( false === $range ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- WooCommerce lookup table, cached.
			$row   = $wpdb->get_row( "SELECT MIN(min_price) AS lo, MAX(max_price) AS hi FROM {$wpdb->wc_product_meta_lookup}" );
			$range = array( $row ? floor( (float) $row->lo ) : 0, $row ? ceil( (float) $row->hi ) : 100 );
			wp_cache_set( 'shopglut_price_range', $range, 'shopglut', HOUR_IN_SECONDS );
		}
		return $range;
	}

	/**
	 * Price filter.
	 *
	 * @param array $item Filter settings.
	 * @return string
	 */
	private function price_body( $item ) {
		list( $lo, $hi ) = self::price_range();
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$min = isset( $_GET['min_price'] ) ? (float) $_GET['min_price'] : $lo;
		$max = isset( $_GET['max_price'] ) ? (float) $_GET['max_price'] : $hi;
		// phpcs:enable
		$symbol = get_woocommerce_currency_symbol();
		if ( 'price-range-radio' === ( $item['filter-product-price-appearance'] ?? '' ) ) {
			$lines = preg_split( '/\r\n|\r|\n|\\\\n/', (string) ( $item['filter-price-ranges'] ?? "0-25\n25-50\n50-100\n100-" ) );
			$html  = '<div class="sgf-terms sgf-d-radio sgf-price-ranges">';
			foreach ( $lines as $line ) {
				if ( ! preg_match( '/^\s*(\d+(?:\.\d+)?)\s*-\s*(\d+(?:\.\d+)?)?\s*$/', $line, $m ) ) {
					continue;
				}
				$a     = (float) $m[1];
				$b     = isset( $m[2] ) && '' !== $m[2] ? (float) $m[2] : 0;
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$on    = isset( $_GET['min_price'] ) && (float) $min === $a && ( $b ? (float) $max === $b : ! isset( $_GET['max_price'] ) );
				$label = $b ? wp_strip_all_tags( wc_price( $a ) ) . ' – ' . wp_strip_all_tags( wc_price( $b ) ) : wp_strip_all_tags( wc_price( $a ) ) . '+';
				$html .= '<label class="sgf-opt' . ( $on ? ' is-on' : '' ) . '"><input type="radio" name="sgf_price_range" data-min="' . esc_attr( $a ) . '" data-max="' . esc_attr( $b ? $b : '' ) . '"' . checked( $on, true, false ) . '> <span class="sgf-name">' . esc_html( $label ) . '</span></label>';
			}
			$html .= '<input type="hidden" name="min_price" value="' . ( isset( $_GET['min_price'] ) ? esc_attr( $min ) : '' ) . '" data-empty-off><input type="hidden" name="max_price" value="' . ( isset( $_GET['max_price'] ) ? esc_attr( $max ) : '' ) . '" data-empty-off></div>'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return $html;
		}
		$step = max( 0.01, (float) ( $item['filter-product-price-step'] ?? 1 ) );
		return '<div class="sgf-price" data-lo="' . esc_attr( $lo ) . '" data-hi="' . esc_attr( $hi ) . '">'
			. '<div class="sgf-range"><input type="range" class="sgf-r-min" min="' . esc_attr( $lo ) . '" max="' . esc_attr( $hi ) . '" step="' . esc_attr( $step ) . '" value="' . esc_attr( $min ) . '" aria-label="' . esc_attr__( 'Minimum price', 'shopglut' ) . '">'
			. '<input type="range" class="sgf-r-max" min="' . esc_attr( $lo ) . '" max="' . esc_attr( $hi ) . '" step="' . esc_attr( $step ) . '" value="' . esc_attr( $max ) . '" aria-label="' . esc_attr__( 'Maximum price', 'shopglut' ) . '"></div>'
			. '<div class="sgf-price-fields"><label>' . esc_html( $symbol ) . '<input type="number" name="min_price" value="' . esc_attr( $min ) . '" min="' . esc_attr( $lo ) . '" max="' . esc_attr( $hi ) . '" step="' . esc_attr( $step ) . '" data-default="' . esc_attr( $lo ) . '"></label><span>–</span>'
			. '<label>' . esc_html( $symbol ) . '<input type="number" name="max_price" value="' . esc_attr( $max ) . '" min="' . esc_attr( $lo ) . '" max="' . esc_attr( $hi ) . '" step="' . esc_attr( $step ) . '" data-default="' . esc_attr( $hi ) . '"></label></div></div>';
	}

	/**
	 * Rating filter ("4 stars & up").
	 *
	 * @param array $item Filter settings.
	 * @return string
	 */
	private function rating_body( $item ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$current = isset( $_GET['rating_filter'] ) ? min( array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_GET['rating_filter'] ) ) ) ) ) : 0;
		$check   = 'checkbox-rating' === ( $item['filter-product-rating-appearance'] ?? '' );
		$html    = '<div class="sgf-terms sgf-rating">';
		foreach ( array( 4, 3, 2, 1 ) as $n ) {
			$stars = str_repeat( '★', $n ) . '<span class="sgf-star-off">' . str_repeat( '★', 5 - $n ) . '</span>';
			/* translators: %d: number of stars */
			$html .= '<label class="sgf-opt' . ( $current === $n ? ' is-on' : '' ) . '"><input type="' . ( $check ? 'checkbox' : 'radio' ) . '" name="sgf_rating" value="' . esc_attr( implode( ',', range( $n, 5 ) ) ) . '"' . checked( $current, $n, false ) . '> <span class="sgf-stars" aria-label="' . esc_attr( sprintf( __( '%d stars and up', 'shopglut' ), $n ) ) . '">' . $stars . '</span> <span class="sgf-name">' . esc_html__( '& up', 'shopglut' ) . '</span></label>';
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$html .= '<input type="hidden" name="rating_filter" value="' . esc_attr( isset( $_GET['rating_filter'] ) ? sanitize_text_field( wp_unslash( $_GET['rating_filter'] ) ) : '' ) . '" data-empty-off></div>';
		return $html;
	}

	/**
	 * Stock status filter.
	 *
	 * @param array $item Filter settings.
	 * @return string
	 */
	private function stock_body( $item ) {
		$names  = array(
			'instock'     => __( 'In stock', 'shopglut' ),
			'onbackorder' => __( 'On backorder', 'shopglut' ),
			'outofstock'  => __( 'Out of stock', 'shopglut' ),
		);
		$show   = array_intersect_key( $names, array_flip( (array) ( $item['filter-stock-options'] ?? array_keys( $names ) ) ) );
		$chosen = self::param_list( 'sgf_stock' );
		$html   = '<div class="sgf-terms sgf-d-check-list" data-param="sgf_stock">';
		foreach ( $show as $key => $label ) {
			$on    = in_array( $key, $chosen, true );
			$html .= '<label class="sgf-opt' . ( $on ? ' is-on' : '' ) . '"><input type="checkbox" name="sgf_stock_c" value="' . esc_attr( $key ) . '"' . checked( $on, true, false ) . '> <span class="sgf-name">' . esc_html( $label ) . '</span></label>';
		}
		return $html . '<input type="hidden" name="sgf_stock" value="' . esc_attr( implode( ',', $chosen ) ) . '"></div>';
	}

	/**
	 * Register assets (enqueued when a panel prints).
	 *
	 * @return void
	 */
	public function enqueue() {
		wp_register_style( 'shopglut-filters', $this->url . 'frontend/assets/filters.css', array(), SHOPGLUT_VERSION );
		wp_register_script( 'shopglut-filters', $this->url . 'frontend/assets/filters.js', array(), SHOPGLUT_VERSION, true );
	}
}
