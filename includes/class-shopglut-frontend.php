<?php
/**
 * Front end: shared assets of every module (accent color variable, custom CSS).
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Frontend class.
 */
class ShopGlut_Frontend {

	/**
	 * Single instance.
	 *
	 * @var ShopGlut_Frontend|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return ShopGlut_Frontend
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ), 20 );
	}

	/**
	 * Whether ShopGlut assets load on this request (Global Settings → Load front-end assets).
	 *
	 * @return bool
	 */
	public static function should_load() {
		$load = 'always' === shopglut_get_setting( 'load_assets', 'woo' ) || ( function_exists( 'is_woocommerce' ) && ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) );
		/**
		 * Whether ShopGlut front-end assets load on this request. Modules check it before enqueueing.
		 *
		 * @param bool $load Load.
		 */
		return (bool) apply_filters( 'shopglut_load_frontend_assets', $load );
	}

	/**
	 * Register the shared style handle and print the accent color and custom CSS.
	 *
	 * @return void
	 */
	public function enqueue() {
		wp_register_style( 'shopglut', false, array(), SHOPGLUT_VERSION );
		if ( ! self::should_load() ) {
			return;
		}
		wp_enqueue_style( 'shopglut' );

		$accent = appglut_sanitize_color( (string) shopglut_get_setting( 'brand_color', '#7c3aed' ) );
		$css    = $accent ? ':root{--shopglut-accent:' . $accent . ';}' : '';
		$custom = (string) shopglut_get_setting( 'custom_css', '' );
		if ( '' !== trim( $custom ) ) {
			$css .= wp_strip_all_tags( $custom );
		}
		if ( '' !== $css ) {
			wp_add_inline_style( 'shopglut', $css );
		}
	}
}
