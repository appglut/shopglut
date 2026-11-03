<?php
/**
 * Wishlist front end.
 *
 * Storage: logged-in customers in user meta (shopglut_wishlist: product ID => time added); guests in a
 * cookie, merged into their account when they log in. Each product keeps a count of wishlists it is in
 * (_shopglut_wishlist_count), used for "popular" products.
 *
 * Output: buttons on single product, shop and archive pages (classic hooks and product blocks), the
 * wishlist page ([shopglut_wishlist]), a menu button / [shopglut_wishlist_counter], popular products
 * ([shopglut_popular_wishlist]), sharing (link, social networks, QR code made in the browser) and print.
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Wishlist class.
 */
class ShopGlut_Wishlist {

	const META   = 'shopglut_wishlist';
	const KEY    = 'shopglut_wishlist_key';
	const COUNT  = '_shopglut_wishlist_count';
	const COOKIE = 'shopglut_wishlist';
	const NONCE  = 'shopglut_wishlist';
	const PAGE   = 'shopglut_wishlist_page_id';

	/**
	 * Module options.
	 *
	 * @var array
	 */
	private $o;

	/**
	 * Module URL.
	 *
	 * @var string
	 */
	private $url;

	/**
	 * Wishlist of this request (product ID => time), loaded once.
	 *
	 * @var array|null
	 */
	private $items = null;

	/**
	 * Constructor.
	 *
	 * @param string $url Module URL.
	 */
	public function __construct( $url ) {
		$this->url = $url;
		$values    = appglut_get_option( ShopGlut_Panels::module_option( 'wishlist' ) );
		$this->o   = is_array( $values ) ? $values : array();

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ), 30 );
		add_action( 'wp_ajax_shopglut_wishlist_toggle', array( $this, 'ajax_toggle' ) );
		add_action( 'wp_ajax_nopriv_shopglut_wishlist_toggle', array( $this, 'ajax_toggle' ) );
		add_action( 'wp_login', array( $this, 'merge_on_login' ), 10, 2 );
		add_action( 'admin_init', array( $this, 'ensure_page' ) );

		add_shortcode( 'shopglut_wishlist', array( $this, 'page_shortcode' ) );
		add_shortcode( 'shopglut_wishlist_counter', array( $this, 'counter_shortcode' ) );
		add_shortcode( 'shopglut_wishlist_button', array( $this, 'button_shortcode' ) );
		add_shortcode( 'shopglut_popular_wishlist', array( $this, 'popular_shortcode' ) );
		add_shortcode( 'wishglut_wishlist', array( $this, 'page_shortcode' ) ); // Old WishGlut shortcode.

		$this->hook_buttons();

		if ( $this->on( 'wishlist-enable-menu-btn', true ) ) {
			add_filter( 'wp_nav_menu_items', array( $this, 'classic_menu_item' ), 20, 2 );
			add_filter( 'render_block_core/navigation', array( $this, 'block_menu_item' ), 20 );
		}
		if ( $this->on( 'wishlist-remove-if-add-to-cart', false ) ) {
			add_action( 'woocommerce_add_to_cart', array( $this, 'remove_after_add_to_cart' ), 20, 2 );
		}
	}

	/* ---------------------------------------------------------------------
	 * Options
	 * ------------------------------------------------------------------- */

	/**
	 * An option.
	 *
	 * @param string $key     Key.
	 * @param mixed  $default Default.
	 * @return mixed
	 */
	private function opt( $key, $default = '' ) {
		if ( ! array_key_exists( $key, $this->o ) || '' === $this->o[ $key ] || null === $this->o[ $key ] ) {
			return $default;
		}
		return $this->o[ $key ];
	}

	/**
	 * A switch option.
	 *
	 * @param string $key     Key.
	 * @param bool   $default Default.
	 * @return bool
	 */
	private function on( $key, $default ) {
		return array_key_exists( $key, $this->o ) ? (bool) $this->o[ $key ] : $default;
	}

	/**
	 * Wishlist page ID (the chosen page, or the one ShopGlut created).
	 *
	 * @return int
	 */
	private function page_id() {
		$id = (int) $this->opt( 'wishlist-general-page', 0 );
		return $id ? $id : (int) get_option( self::PAGE, 0 );
	}

	/**
	 * Wishlist page URL.
	 *
	 * @return string
	 */
	public function page_url() {
		$id = $this->page_id();
		return $id ? get_permalink( $id ) : home_url( '/' );
	}

	/**
	 * Create a Wishlist page once, when no page is chosen.
	 *
	 * @return void
	 */
	public function ensure_page() {
		if ( $this->page_id() && 'publish' === get_post_status( $this->page_id() ) ) {
			return;
		}
		if ( ! current_user_can( 'publish_pages' ) ) {
			return;
		}
		$id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => __( 'Wishlist', 'shopglut' ),
				'post_content' => '<!-- wp:shortcode -->[shopglut_wishlist]<!-- /wp:shortcode -->',
			)
		);
		if ( $id && ! is_wp_error( $id ) ) {
			update_option( self::PAGE, (int) $id, false );
		}
	}

	/* ---------------------------------------------------------------------
	 * Storage
	 * ------------------------------------------------------------------- */

	/**
	 * Items of the current visitor.
	 *
	 * @return array product ID => time
	 */
	public function items() {
		if ( null !== $this->items ) {
			return $this->items;
		}
		if ( is_user_logged_in() ) {
			$items = get_user_meta( get_current_user_id(), self::META, true );
		} else {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- decoded and cast to integers below.
			$raw   = isset( $_COOKIE[ self::COOKIE ] ) ? json_decode( wp_unslash( $_COOKIE[ self::COOKIE ] ), true ) : array();
			$items = is_array( $raw ) ? $raw : array();
		}
		$clean = array();
		foreach ( (array) $items as $id => $time ) {
			if ( absint( $id ) ) {
				$clean[ absint( $id ) ] = absint( $time );
			}
		}
		$this->items = $clean;
		return $this->items;
	}

	/**
	 * Store the current visitor's items.
	 *
	 * @param array $items product ID => time.
	 * @return void
	 */
	private function store( $items ) {
		$this->items = $items;
		if ( is_user_logged_in() ) {
			update_user_meta( get_current_user_id(), self::META, $items );
			return;
		}
		$days = max( 1, (int) $this->opt( 'wishlist-guestlist-deletetime', 15 ) );
		$this->set_cookie( wp_json_encode( $items ), time() + $days * DAY_IN_SECONDS );
	}

	/**
	 * Set or clear the guest cookie.
	 *
	 * @param string $value   Value.
	 * @param int    $expires Expiry time.
	 * @return void
	 */
	private function set_cookie( $value, $expires ) {
		if ( headers_sent() ) {
			return;
		}
		setcookie(
			self::COOKIE,
			$value,
			array(
				'expires'  => $expires,
				'path'     => COOKIEPATH ? COOKIEPATH : '/',
				'domain'   => COOKIE_DOMAIN,
				'secure'   => is_ssl(),
				'httponly' => true,
				'samesite' => 'Lax',
			)
		);
		$_COOKIE[ self::COOKIE ] = $value;
	}

	/**
	 * Change a product's wishlist count.
	 *
	 * @param int $product_id Product.
	 * @param int $delta      +1 / -1.
	 * @return void
	 */
	private static function bump( $product_id, $delta ) {
		$count = max( 0, (int) get_post_meta( $product_id, self::COUNT, true ) + $delta );
		update_post_meta( $product_id, self::COUNT, $count );
	}

	/**
	 * Whether a product is in the wishlist.
	 *
	 * @param int $product_id Product.
	 * @return bool
	 */
	public function has( $product_id ) {
		return isset( $this->items()[ (int) $product_id ] );
	}

	/**
	 * Merge a guest list into the account on login.
	 *
	 * @param string  $login User login.
	 * @param WP_User $user  User.
	 * @return void
	 */
	public function merge_on_login( $login, $user ) {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- decoded and cast below.
		$guest = isset( $_COOKIE[ self::COOKIE ] ) ? json_decode( wp_unslash( $_COOKIE[ self::COOKIE ] ), true ) : array();
		if ( ! is_array( $guest ) || ! $guest || ! $this->on( 'wishlist-merge-guestlist', true ) ) {
			return;
		}
		$items = get_user_meta( $user->ID, self::META, true );
		$items = is_array( $items ) ? $items : array();
		foreach ( $guest as $id => $time ) {
			if ( absint( $id ) && ! isset( $items[ absint( $id ) ] ) ) {
				$items[ absint( $id ) ] = absint( $time );
			}
		}
		update_user_meta( $user->ID, self::META, $items );
		$this->set_cookie( '', time() - HOUR_IN_SECONDS );
	}

	/**
	 * Remove a product when it is added to the cart (option).
	 *
	 * @param string $key        Cart item key.
	 * @param int    $product_id Product.
	 * @return void
	 */
	public function remove_after_add_to_cart( $key, $product_id ) {
		$items = $this->items();
		if ( isset( $items[ $product_id ] ) ) {
			unset( $items[ $product_id ] );
			$this->store( $items );
			self::bump( $product_id, -1 );
		}
	}

	/**
	 * AJAX: add or remove a product.
	 *
	 * @return void
	 */
	public function ajax_toggle() {
		// Logged-in customers: verify the nonce. Guests only change their own cookie, and their nonce changes
		// when WooCommerce starts a cart session, so it is not checked for them.
		if ( is_user_logged_in() && ! check_ajax_referer( self::NONCE, 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Please reload the page and try again.', 'shopglut' ) ) );
		}
		if ( ! is_user_logged_in() && $this->on( 'wishlist-require-login', false ) ) {
			wp_send_json_error(
				array(
					'login'   => wc_get_page_permalink( 'myaccount' ),
					'message' => __( 'Please log in to use the wishlist.', 'shopglut' ),
				)
			);
		}
		$product_id = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- see above.
		$mode       = isset( $_POST['mode'] ) ? sanitize_key( wp_unslash( $_POST['mode'] ) ) : 'toggle'; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- see above.
		$product    = $product_id ? wc_get_product( $product_id ) : null;
		if ( ! $product || 'publish' !== get_post_status( $product->get_parent_id() ? $product->get_parent_id() : $product_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Product not found.', 'shopglut' ) ) );
		}
		$items = $this->items();
		$added = ! isset( $items[ $product_id ] );
		if ( 'remove' === $mode ) {
			$added = false;
		}
		if ( $added ) {
			$items[ $product_id ] = time();
			self::bump( $product_id, 1 );
		} elseif ( isset( $items[ $product_id ] ) ) {
			unset( $items[ $product_id ] );
			self::bump( $product_id, -1 );
		}
		$this->store( $items );
		wp_send_json_success(
			array(
				'added'   => $added,
				'count'   => count( $items ),
				'message' => $added
					? (string) $this->opt( 'wishlist-product-added-notification-text', __( 'Product added to your wishlist.', 'shopglut' ) )
					: (string) $this->opt( 'wishlist-product-removed-notification-text', __( 'Product removed from your wishlist.', 'shopglut' ) ),
			)
		);
	}

	/* ---------------------------------------------------------------------
	 * Buttons
	 * ------------------------------------------------------------------- */

	/**
	 * Page context of a button: product (single product page), shop or archive.
	 *
	 * @param bool $loop In a product list.
	 * @return string
	 */
	private static function context( $loop ) {
		if ( ! $loop ) {
			return 'product';
		}
		return ( is_product_taxonomy() ) ? 'archive' : 'shop';
	}

	/**
	 * Whether buttons show in a context (archive pages can be limited to categories or tags).
	 *
	 * @param string $context product | shop | archive.
	 * @return bool
	 */
	private function context_enabled( $context ) {
		if ( ! $this->on( 'wishlist-enable-' . ( 'product' === $context ? 'product' : $context ) . '-page', true ) ) {
			return false;
		}
		if ( 'archive' !== $context ) {
			return true;
		}
		$which = $this->opt( 'wishlist-archive-select-cat-option', 'all-categories' );
		$term  = get_queried_object();
		if ( 'select-category' === $which ) {
			return $term instanceof WP_Term && in_array( (string) $term->term_id, array_map( 'strval', (array) $this->opt( 'wishlist-archive-select-category', array() ) ), true );
		}
		if ( 'select-tag' === $which ) {
			return $term instanceof WP_Term && in_array( (string) $term->term_id, array_map( 'strval', (array) $this->opt( 'wishlist-archive-select-tag', array() ) ), true );
		}
		return true;
	}

	/**
	 * Hook the buttons where the settings put them.
	 *
	 * @return void
	 */
	private function hook_buttons() {
		$single = $this->opt( 'wishlist-product-position', 'after-cart' );
		$map    = array(
			'before-cart'        => array( 'woocommerce_before_add_to_cart_form', 10 ),
			'after-cart'         => array( 'woocommerce_after_add_to_cart_form', 10 ),
			'after-product-meta' => array( 'woocommerce_product_meta_end', 10 ),
		);
		$hook   = isset( $map[ $single ] ) ? $map[ $single ] : $map['after-cart'];
		add_action( $hook[0], array( $this, 'single_button' ), $hook[1] );
		if ( 'after-product-meta' === $single ) {
			add_filter( 'render_block_woocommerce/product-meta', array( $this, 'block_product_meta' ), 10, 3 );
		}

		add_filter( 'render_block_data', array( $this, 'enter_block' ) );
		add_filter( 'render_block', array( $this, 'leave_block' ), 10, 2 );
		add_action( 'woocommerce_after_shop_loop_item', array( $this, 'loop_button_before' ), 9 );
		add_action( 'woocommerce_after_shop_loop_item', array( $this, 'loop_button_after' ), 11 );
		add_filter( 'render_block_woocommerce/product-button', array( $this, 'block_loop_button' ), 10, 3 );
	}

	/**
	 * Legacy (classic PHP) WooCommerce templates rendering inside a block theme.
	 *
	 * @var int
	 */
	private $legacy = 0;

	/**
	 * Block starts rendering.
	 *
	 * @param array $block Block.
	 * @return array
	 */
	public function enter_block( $block ) {
		if ( isset( $block['blockName'] ) && 'woocommerce/legacy-template' === $block['blockName'] ) {
			++$this->legacy;
		}
		return $block;
	}

	/**
	 * Block finished rendering.
	 *
	 * @param string $html  HTML.
	 * @param array  $block Block.
	 * @return string
	 */
	public function leave_block( $html, $block ) {
		if ( isset( $block['blockName'] ) && 'woocommerce/legacy-template' === $block['blockName'] && $this->legacy > 0 ) {
			--$this->legacy;
		}
		return $html;
	}

	/**
	 * Whether classic product-list hooks should print (block themes use the product blocks instead).
	 *
	 * @return bool
	 */
	private function classic_loop() {
		return ! wp_is_block_theme() || $this->legacy > 0;
	}

	/**
	 * Single product: print the button.
	 *
	 * @return void
	 */
	public function single_button() {
		global $product;
		if ( $product instanceof WC_Product && $this->context_enabled( 'product' ) ) {
			echo $this->button( $product->get_id(), 'product' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in button().
		}
	}

	/**
	 * Single product in a block theme with the "after product meta" position.
	 *
	 * @param string   $html     Block HTML.
	 * @param array    $block    Block.
	 * @param WP_Block $instance Instance.
	 * @return string
	 */
	public function block_product_meta( $html, $block, $instance = null ) {
		$id = ( $instance && isset( $instance->context['postId'] ) ) ? (int) $instance->context['postId'] : get_the_ID();
		return ( $id && $this->context_enabled( 'product' ) ) ? $html . $this->button( $id, 'product' ) : $html;
	}

	/**
	 * Product lists (classic): before the add to cart button.
	 *
	 * @return void
	 */
	public function loop_button_before() {
		$this->loop_button( 'before-cart' );
	}

	/**
	 * Product lists (classic): after the add to cart button.
	 *
	 * @return void
	 */
	public function loop_button_after() {
		$this->loop_button( 'after-cart' );
	}

	/**
	 * Product lists (classic).
	 *
	 * @param string $where before-cart | after-cart.
	 * @return void
	 */
	private function loop_button( $where ) {
		global $product;
		$context = self::context( true );
		$pos     = $this->opt( 'wishlist-' . $context . '-position', 'after-cart' );
		if ( 'after-product-meta' === $pos ) {
			$pos = 'after-cart';
		}
		if ( $pos !== $where || ! $product instanceof WC_Product || ! $this->classic_loop() || ! $this->context_enabled( $context ) ) {
			return;
		}
		echo $this->button( $product->get_id(), $context ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in button().
	}

	/**
	 * Product lists (blocks): next to the Add to Cart button block.
	 *
	 * @param string   $html     Block HTML.
	 * @param array    $block    Block.
	 * @param WP_Block $instance Instance.
	 * @return string
	 */
	public function block_loop_button( $html, $block, $instance = null ) {
		$id = ( $instance && isset( $instance->context['postId'] ) ) ? (int) $instance->context['postId'] : 0;
		if ( ! $id ) {
			return $html;
		}
		$context = self::context( true );
		if ( ! $this->context_enabled( $context ) ) {
			return $html;
		}
		$button = $this->button( $id, $context );
		return 'before-cart' === $this->opt( 'wishlist-' . $context . '-position', 'after-cart' ) ? $button . $html : $html . $button;
	}

	/**
	 * Button HTML.
	 *
	 * @param int    $product_id Product.
	 * @param string $context    product | shop | archive.
	 * @return string
	 */
	public function button( $product_id, $context = 'product' ) {
		$p        = 'wishlist-' . $context . '-';
		$in       = $this->has( $product_id );
		$style    = $this->opt( $p . 'option', 'button-with-icon' );
		$text     = (string) $this->opt( $p . 'button-text', __( 'Add to wishlist', 'shopglut' ) );
		$text_in  = (string) $this->opt( $p . 'button-text-after-added', __( 'In your wishlist', 'shopglut' ) );
		$icon     = shopglut_fa_icon( (string) $this->opt( $p . 'icon', 'fa-regular fa-heart' ), 'shopglut-wl-ic shopglut-wl-ic--off' );
		$icon_in  = shopglut_fa_icon( (string) $this->opt( $p . 'added-icon', 'fa-solid fa-heart' ), 'shopglut-wl-ic shopglut-wl-ic--on' );
		$icon_pos = $this->opt( $p . 'icon-position', 'text-left' );
		$second   = $this->opt( $p . 'second-click', 'remove-wishlist' );

		$label = '<span class="shopglut-wl-text" data-off="' . esc_attr( $text ) . '" data-on="' . esc_attr( $text_in ) . '">' . esc_html( $in ? $text_in : $text ) . '</span>';
		$icons = '<span class="shopglut-wl-icons">' . $icon . $icon_in . '</span>';
		if ( 'only-icon' === $style ) {
			$inner = $icons . '<span class="screen-reader-text">' . esc_html( $text ) . '</span>';
		} elseif ( 'only-button' === $style ) {
			$inner = $label;
		} else {
			$inner = 'text-right' === $icon_pos ? $label . $icons : $icons . $label;
		}
		return sprintf(
			'<button type="button" class="shopglut-wl-btn shopglut-wl-btn--%1$s shopglut-wl-btn--%2$s%3$s" data-product="%4$d" data-second="%5$s" aria-pressed="%6$s">%7$s</button>',
			esc_attr( $context ),
			esc_attr( $style ),
			$in ? ' is-added' : '',
			(int) $product_id,
			esc_attr( $second ),
			$in ? 'true' : 'false',
			$inner
		);
	}

	/**
	 * [shopglut_wishlist_button id="123"].
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	public function button_shortcode( $atts ) {
		$atts = shortcode_atts( array( 'id' => 0 ), $atts );
		$id   = $atts['id'] ? absint( $atts['id'] ) : get_the_ID();
		return $id && 'product' === get_post_type( $id ) ? $this->button( $id, 'product' ) : '';
	}

	/* ---------------------------------------------------------------------
	 * Counter / menu
	 * ------------------------------------------------------------------- */

	/**
	 * Counter link HTML.
	 *
	 * @param string $class Extra class.
	 * @return string
	 */
	private function counter( $class = '' ) {
		$count     = count( $this->items() );
		$show_text = $this->on( 'wishlist-page-menu-button-show-text', false );
		$text      = (string) $this->opt( 'wishlist-page-menu-button-text', $this->opt( 'wishlist-menu-btn-text', __( 'Wishlist', 'shopglut' ) ) );
		$hide      = $this->on( 'wishlist-page-menu-button-hide-empty-counter', false ) && ! $count;
		$bubble    = $this->on( 'wishlist-page-menu-button-show-counter', true )
			? '<span class="shopglut-wl-count"' . ( $this->on( 'wishlist-page-menu-button-hide-empty-counter', false ) ? ' data-hide-empty' : '' ) . ( $hide ? ' hidden' : '' ) . '>' . (int) $count . '</span>'
			: '';
		return '<a class="shopglut-wl-counter ' . esc_attr( $class ) . '" href="' . esc_url( $this->page_url() ) . '">'
			. shopglut_fa_icon( (string) $this->opt( 'wishlist-menu-btn-icon', 'fa-solid fa-heart' ) )
			. ( $show_text ? '<span class="shopglut-wl-counter-text">' . esc_html( $text ) . '</span>' : '<span class="screen-reader-text">' . esc_html( $text ) . '</span>' )
			. $bubble . '</a>';
	}

	/**
	 * [shopglut_wishlist_counter].
	 *
	 * @return string
	 */
	public function counter_shortcode() {
		return $this->counter();
	}

	/**
	 * Classic menus: add the counter to the primary (first) menu location.
	 *
	 * @param string   $items HTML.
	 * @param stdClass $args  Menu args.
	 * @return string
	 */
	public function classic_menu_item( $items, $args ) {
		$locations = array_keys( get_nav_menu_locations() );
		if ( empty( $args->theme_location ) || ! $locations || $args->theme_location !== $locations[0] ) {
			return $items;
		}
		return $items . '<li class="menu-item shopglut-wl-menu-item">' . $this->counter() . '</li>';
	}

	/**
	 * Block themes: add the counter to the first Navigation block of the header.
	 *
	 * @param string $html Block HTML.
	 * @return string
	 */
	public function block_menu_item( $html ) {
		static $done = false;
		if ( $done || false === strpos( $html, 'wp-block-navigation__container' ) ) {
			return $html;
		}
		$pos = strrpos( $html, '</ul>' );
		if ( false === $pos ) {
			return $html;
		}
		$done = true;
		return substr( $html, 0, $pos ) . '<li class="wp-block-navigation-item shopglut-wl-menu-item">' . $this->counter( 'wp-block-navigation-item__content' ) . '</li>' . substr( $html, $pos );
	}

	/* ---------------------------------------------------------------------
	 * Wishlist page
	 * ------------------------------------------------------------------- */

	/**
	 * Items shown on the page: the visitor's own, or a shared list (?wishlist=KEY or ?wishlist_items=1,2).
	 *
	 * @return array [ items, shared ]
	 */
	private function page_items() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only view of a shared list.
		if ( ! empty( $_GET['wishlist'] ) ) {
			$key   = sanitize_key( wp_unslash( $_GET['wishlist'] ) );
			$users = get_users(
				array(
					'meta_key'   => self::KEY, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					'meta_value' => $key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
					'number'     => 1,
					'fields'     => 'ID',
				)
			);
			if ( $users && (int) $users[0] !== get_current_user_id() ) {
				$items = get_user_meta( (int) $users[0], self::META, true );
				return array( is_array( $items ) ? $items : array(), true );
			}
		}
		if ( ! empty( $_GET['wishlist_items'] ) ) {
			$ids = array_filter( array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_GET['wishlist_items'] ) ) ) ) );
			$own = array_keys( $this->items() );
			sort( $own );
			$cmp = $ids;
			sort( $cmp );
			if ( $cmp !== $own ) {
				return array( array_fill_keys( array_slice( $ids, 0, 100 ), 0 ), true );
			}
		}
		// phpcs:enable
		return array( $this->items(), false );
	}

	/**
	 * Share URL of the visitor's list.
	 *
	 * @return string
	 */
	private function share_url() {
		if ( is_user_logged_in() ) {
			$key = get_user_meta( get_current_user_id(), self::KEY, true );
			if ( ! $key ) {
				$key = strtolower( wp_generate_password( 16, false ) );
				update_user_meta( get_current_user_id(), self::KEY, $key );
			}
			return add_query_arg( 'wishlist', $key, $this->page_url() );
		}
		return add_query_arg( 'wishlist_items', implode( ',', array_keys( $this->items() ) ), $this->page_url() );
	}

	/**
	 * [shopglut_wishlist]: the wishlist page.
	 *
	 * @return string
	 */
	public function page_shortcode() {
		if ( ! is_user_logged_in() && $this->on( 'wishlist-require-login', false ) ) {
			return '<div class="shopglut-wl-locked"><p>' . esc_html__( 'Please log in to see your wishlist.', 'shopglut' ) . '</p><a class="button" href="' . esc_url( wc_get_page_permalink( 'myaccount' ) ) . '">' . esc_html( (string) $this->opt( 'wishlist-require-login-btn-text', __( 'Log in', 'shopglut' ) ) ) . '</a></div>';
		}
		list( $items, $shared ) = $this->page_items();
		arsort( $items );
		ob_start();
		echo '<div class="shopglut-wl-page' . ( $shared ? ' is-shared' : '' ) . '">';
		if ( $shared ) {
			echo '<p class="shopglut-wl-shared-note">' . esc_html__( 'You are viewing a shared wishlist.', 'shopglut' ) . '</p>';
		}
		$rows = array();
		foreach ( $items as $id => $time ) {
			$product = wc_get_product( $id );
			if ( $product && $product->is_visible() ) {
				$rows[] = array( $product, $time );
			}
		}
		if ( ! $rows ) {
			echo '<div class="shopglut-wl-empty">' . shopglut_icon( 'heart' ) . '<p>' . esc_html__( 'Your wishlist is empty.', 'shopglut' ) . '</p><a class="button" href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">' . esc_html__( 'Return to shop', 'shopglut' ) . '</a></div></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG.
			return (string) ob_get_clean();
		}
		$show_stock = $this->on( 'wishlist-general-outofstock', true );
		?>
		<table class="shopglut-wl-table">
			<thead><tr>
				<th class="col-image"><span class="screen-reader-text"><?php esc_html_e( 'Image', 'shopglut' ); ?></span></th>
				<th class="col-name"><?php esc_html_e( 'Product', 'shopglut' ); ?></th>
				<th class="col-price"><?php esc_html_e( 'Price', 'shopglut' ); ?></th>
				<?php if ( $show_stock ) : ?><th class="col-stock"><?php esc_html_e( 'Stock', 'shopglut' ); ?></th><?php endif; ?>
				<th class="col-date"><?php esc_html_e( 'Added', 'shopglut' ); ?></th>
				<th class="col-actions"><span class="screen-reader-text"><?php esc_html_e( 'Actions', 'shopglut' ); ?></span></th>
			</tr></thead>
			<tbody>
			<?php
			foreach ( $rows as $row ) :
				list( $product, $time ) = $row;
				$id                     = $product->get_id();
				?>
				<tr data-product="<?php echo esc_attr( $id ); ?>">
					<td class="col-image"><a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo wp_kses_post( $product->get_image( 'woocommerce_gallery_thumbnail' ) ); ?></a></td>
					<td class="col-name"><a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></td>
					<td class="col-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></td>
					<?php if ( $show_stock ) : ?>
						<td class="col-stock"><span class="shopglut-wl-stock <?php echo $product->is_in_stock() ? 'in' : 'out'; ?>"><?php echo $product->is_in_stock() ? esc_html__( 'In stock', 'shopglut' ) : esc_html__( 'Out of stock', 'shopglut' ); ?></span></td>
					<?php endif; ?>
					<td class="col-date"><?php echo $time ? esc_html( date_i18n( get_option( 'date_format' ), $time ) ) : '&ndash;'; ?></td>
					<td class="col-actions">
						<?php
						if ( $product->is_purchasable() && $product->is_in_stock() ) {
							$ajax = $product->supports( 'ajax_add_to_cart' ) ? ' ajax_add_to_cart add_to_cart_button' : '';
							printf(
								'<a href="%1$s" data-quantity="1" data-product_id="%2$d" class="button shopglut-wl-cart%3$s" rel="nofollow">%4$s</a>',
								esc_url( $product->add_to_cart_url() ),
								(int) $id,
								esc_attr( $ajax ),
								esc_html( $product->add_to_cart_text() )
							);
						}
						if ( ! $shared ) {
							echo '<button type="button" class="shopglut-wl-remove" data-product="' . esc_attr( $id ) . '" aria-label="' . esc_attr__( 'Remove from wishlist', 'shopglut' ) . '">' . shopglut_icon( 'times' ) . '</button>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG.
						}
						?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php
		if ( ! $shared ) {
			$this->share_bar();
		}
		echo '</div>';
		return (string) ob_get_clean();
	}

	/**
	 * Share links, QR code and print button.
	 *
	 * @return void
	 */
	private function share_bar() {
		$url    = $this->share_url();
		$title  = get_bloginfo( 'name' ) . ' – ' . __( 'My wishlist', 'shopglut' );
		$social = array();
		if ( $this->on( 'enable-social-share', true ) ) {
			$nets = array(
				'facebook'  => array( 'Facebook', 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $url ) ),
				'twitter'   => array( 'X', 'https://twitter.com/intent/tweet?url=' . rawurlencode( $url ) . '&text=' . rawurlencode( (string) $this->opt( 'twitter-share-text', $title ) ) ),
				'whatsapp'  => array( 'WhatsApp', 'https://wa.me/?text=' . rawurlencode( $this->opt( 'whatsapp-share-text', $title ) . ' ' . $url ) ),
				'pinterest' => array( 'Pinterest', 'https://pinterest.com/pin/create/button/?url=' . rawurlencode( $url ) . '&description=' . rawurlencode( $title ) ),
				'linkedin'  => array( 'LinkedIn', 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode( $url ) ),
				'telegram'  => array( 'Telegram', 'https://t.me/share/url?url=' . rawurlencode( $url ) . '&text=' . rawurlencode( (string) $this->opt( 'telegram-share-text', $title ) ) ),
				'email'     => array( __( 'Email', 'shopglut' ), 'mailto:?subject=' . rawurlencode( (string) $this->opt( 'email-share-subject', $title ) ) . '&body=' . rawurlencode( $this->opt( 'email-share-body', __( 'Have a look at my wishlist:', 'shopglut' ) ) . ' ' . $url ) ),
			);
			foreach ( $nets as $key => $net ) {
				if ( $this->on( 'enable-' . $key . '-share', in_array( $key, array( 'facebook', 'twitter', 'whatsapp', 'email' ), true ) ) ) {
					$social[ $key ] = $net;
				}
			}
		}
		?>
		<div class="shopglut-wl-share">
			<span class="shopglut-wl-share-title"><?php echo esc_html( (string) $this->opt( 'social-share-title', __( 'Share your wishlist', 'shopglut' ) ) ); ?></span>
			<div class="shopglut-wl-share-links">
				<?php foreach ( $social as $key => $net ) : ?>
					<a class="shopglut-wl-share-<?php echo esc_attr( $key ); ?>" href="<?php echo esc_url( $net[1] ); ?>" target="_blank" rel="noopener nofollow"><?php echo esc_html( $net[0] ); ?></a>
				<?php endforeach; ?>
				<button type="button" class="shopglut-wl-copy" data-url="<?php echo esc_url( $url ); ?>"><?php echo shopglut_icon( 'link' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php esc_html_e( 'Copy link', 'shopglut' ); ?></button>
				<?php if ( $this->on( 'wishlist-enable-share-qr', true ) ) : ?>
					<button type="button" class="shopglut-wl-qr-btn" data-url="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'QR code', 'shopglut' ); ?></button>
				<?php endif; ?>
				<?php if ( $this->on( 'wishlist-enable-print-wish', true ) ) : ?>
					<button type="button" class="shopglut-wl-print"><?php echo shopglut_icon( 'print' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php esc_html_e( 'Print', 'shopglut' ); ?></button>
				<?php endif; ?>
			</div>
			<div class="shopglut-wl-qr" hidden></div>
		</div>
		<?php
	}

	/* ---------------------------------------------------------------------
	 * Popular products
	 * ------------------------------------------------------------------- */

	/**
	 * [shopglut_popular_wishlist limit="4" columns="4"]: products in the most wishlists.
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	public function popular_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'limit'   => 4,
				'columns' => 4,
			),
			$atts
		);
		$ids  = get_posts(
			array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => max( 1, min( 24, absint( $atts['limit'] ) ) ),
				'meta_key'       => self::COUNT, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'orderby'        => 'meta_value_num',
				'order'          => 'DESC',
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => self::COUNT,
						'value'   => 0,
						'compare' => '>',
						'type'    => 'NUMERIC',
					),
				),
				'fields'         => 'ids',
			)
		);
		if ( ! $ids ) {
			return '';
		}
		return do_shortcode( '[products ids="' . implode( ',', array_map( 'absint', $ids ) ) . '" orderby="post__in" columns="' . absint( $atts['columns'] ) . '"]' );
	}

	/* ---------------------------------------------------------------------
	 * Assets
	 * ------------------------------------------------------------------- */

	/**
	 * Styles and script.
	 *
	 * @return void
	 */
	public function enqueue() {
		wp_enqueue_style( 'shopglut-wishlist', $this->url . 'frontend/assets/wishlist.css', array(), SHOPGLUT_VERSION );
		wp_add_inline_style( 'shopglut-wishlist', $this->css() );
		$deps = array();
		if ( is_page( $this->page_id() ) && $this->on( 'wishlist-enable-share-qr', true ) ) {
			wp_register_script( 'shopglut-qrcode', $this->url . 'frontend/assets/qrcode-generator.js', array(), '1.4.4', true );
			$deps[] = 'shopglut-qrcode';
		}
		wp_enqueue_script( 'shopglut-wishlist', $this->url . 'frontend/assets/wishlist.js', $deps, SHOPGLUT_VERSION, true );
		wp_localize_script(
			'shopglut-wishlist',
			'shopglutWishlist',
			array(
				'ajax'         => admin_url( 'admin-ajax.php' ),
				'nonce'        => wp_create_nonce( self::NONCE ),
				'page'         => $this->page_url(),
				'checkout'     => wc_get_checkout_url(),
				'notification' => $this->opt( 'wishlist-general-notification', 'side-notification' ),
				'position'     => $this->opt( 'wishlist-side-notification-appear', 'bottom-left' ),
				'i18n'         => array(
					'already' => __( 'This product is already in your wishlist.', 'shopglut' ),
					'copied'  => __( 'Link copied.', 'shopglut' ),
					'view'    => __( 'View wishlist', 'shopglut' ),
				),
			)
		);
	}

	/**
	 * Colors from the Appearance settings.
	 *
	 * @return string
	 */
	private function css() {
		$c   = function ( $key, $default ) {
			$v = (string) $this->opt( $key, $default );
			return preg_match( '/^(#[0-9a-fA-F]{3,8}|rgba?\([0-9.,\s%]+\)|transparent)$/', $v ) ? $v : $default;
		};
		$css = ':root{'
			. '--sgwl-btn-bg:' . $c( 'wishlist-product-button-color', '#ffffff' ) . ';'
			. '--sgwl-btn-color:' . $c( 'wishlist-product-button-font-color', '#1f2937' ) . ';'
			. '--sgwl-icon:' . $c( 'wishlist-product-icon-color', '#e11d48' ) . ';'
			. '--sgwl-shop-bg:' . $c( 'wishlist-shop-button-color', '#ffffff' ) . ';'
			. '--sgwl-shop-color:' . $c( 'wishlist-shop-button-text-color', '#1f2937' ) . ';'
			. '--sgwl-shop-icon:' . $c( 'wishlist-shop-icon-color', '#e11d48' ) . ';'
			. '--sgwl-archive-bg:' . $c( 'wishlist-archive-button-color', '#ffffff' ) . ';'
			. '--sgwl-archive-color:' . $c( 'wishlist-archive-button-text-color', '#1f2937' ) . ';'
			. '--sgwl-archive-icon:' . $c( 'wishlist-archive-icon-color', '#e11d48' ) . ';'
			. '--sgwl-added-bg:' . $c( 'wishlist-notification-added-bg-color', '#16a34a' ) . ';'
			. '--sgwl-removed-bg:' . $c( 'wishlist-notification-removed-bg-color', '#475569' ) . ';'
			. '--sgwl-note-color:' . $c( 'wishlist-notification-font-color', '#ffffff' ) . ';'
			. '--sgwl-th-bg:' . $c( 'wishlist-page-table-header-color', '#f3f4f6' ) . ';'
			. '--sgwl-th-color:' . $c( 'wishlist-page-table-head-font-color', '#111827' ) . ';'
			. '--sgwl-cart-bg:' . $c( 'wishlist-page-addtocart-button-color', '#111827' ) . ';'
			. '--sgwl-cart-color:' . $c( 'wishlist-page-addtocart-button-font-color', '#ffffff' ) . ';'
			. '--sgwl-menu-bubble:' . $c( 'wishlist-page-menu-counter-bubble-bg-color', '#e11d48' ) . ';'
			. '--sgwl-menu-bubble-text:' . $c( 'wishlist-page-menu-counter-bubble-text-color', '#ffffff' ) . ';'
			. '}';
		return $css;
	}
}
