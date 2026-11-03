<?php
/**
 * Public helper functions.
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether ShopGlut Pro is active (it defines SHOPGLUT_PRO_VERSION).
 *
 * @return bool
 */
function shopglut_is_pro() {
	/**
	 * Whether the Pro features are available.
	 *
	 * @param bool $enabled Pro is active.
	 */
	return (bool) apply_filters( 'shopglut_is_pro', defined( 'SHOPGLUT_PRO_VERSION' ) );
}

/**
 * Whether a module is registered and switched on.
 *
 * @param string $id Module ID, e.g. product-page.
 * @return bool
 */
function shopglut_module_enabled( $id ) {
	return ShopGlut_Modules::instance()->is_enabled( $id );
}

/**
 * Read a global ShopGlut setting (Global Settings page). Dot paths read nested values.
 *
 * @param string $key     Field ID or dot path.
 * @param mixed  $default Fallback.
 * @return mixed
 */
function shopglut_get_setting( $key, $default = null ) {
	$value = appglut_get_option( ShopGlut_Settings::OPTION, $key );
	return null === $value ? $default : $value;
}

/**
 * Read a module setting (the module's Settings panel). Dot paths read nested values.
 *
 * @param string $module  Module ID.
 * @param string $key     Field ID or dot path.
 * @param mixed  $default Fallback.
 * @return mixed
 */
function shopglut_get_module_setting( $module, $key, $default = null ) {
	$value = appglut_get_option( ShopGlut_Panels::module_option( $module ), $key );
	return null === $value ? $default : $value;
}

/**
 * The layout of a module that applies to the current request (or to a product / term).
 *
 * @param string $module  Module ID.
 * @param array  $context Optional: product_id, term_id, taxonomy.
 * @return array|null Layout row with decoded settings.
 */
function shopglut_get_active_layout( $module, $context = array() ) {
	return ShopGlut_Layouts::resolve( $module, $context );
}

/**
 * Sanitize a template ID. Unlike sanitize_key() it keeps the case (templatePro1).
 *
 * @param string $template Template ID.
 * @return string
 */
function shopglut_sanitize_template( $template ) {
	return (string) preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $template );
}

/**
 * Inline SVG icon for front-end templates (replaces the Font Awesome icons of the old Glut plugins,
 * so no icon font has to load).
 *
 * @param string $name  Icon name (Font Awesome style names: shopping-cart, trash, times…).
 * @param string $class Extra classes.
 * @return string SVG markup (static, safe to echo).
 */
function shopglut_icon( $name, $class = '' ) {
	$aliases = array(
		'xmark'      => 'times',
		'close'      => 'times',
		'shield'     => 'shield-alt',
		'cart'       => 'shopping-cart',
		'rotate-left' => 'undo',
	);
	$name    = isset( $aliases[ $name ] ) ? $aliases[ $name ] : $name;
	$paths   = array(
		'minus'           => '<path d="M5 12h14"/>',
		'plus'            => '<path d="M12 5v14M5 12h14"/>',
		'trash'           => '<path d="M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14"/>',
		'times'           => '<path d="M6 6l12 12M18 6L6 18"/>',
		'shopping-cart'   => '<circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M2 3h3l2.7 12.2a1.5 1.5 0 0 0 1.5 1.2h8.6a1.5 1.5 0 0 0 1.5-1.1L21 8H6"/>',
		'shopping-bag'    => '<path d="M5 8h14l-1 13H6z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/>',
		'shopping-basket' => '<path d="M3 10h18l-2 10H5z"/><path d="M8 10l4-6 4 6"/>',
		'share-nodes'     => '<circle cx="18" cy="5" r="2.5"/><circle cx="6" cy="12" r="2.5"/><circle cx="18" cy="19" r="2.5"/><path d="M8.2 10.8l7.6-4.6M8.2 13.2l7.6 4.6"/>',
		'chevron-down'    => '<path d="M6 9l6 6 6-6"/>',
		'chevron-up'      => '<path d="M6 15l6-6 6 6"/>',
		'chevron-right'   => '<path d="M9 6l6 6-6 6"/>',
		'tag'             => '<path d="M3 3h8l10 10-8 8L3 11z"/><circle cx="7.5" cy="7.5" r="1.5"/>',
		'envelope'        => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>',
		'check'           => '<path d="M5 12l5 5 9-10"/>',
		'arrow-left'      => '<path d="M19 12H5M11 6l-6 6 6 6"/>',
		'arrow-right'     => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'credit-card'     => '<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20M6 15h4"/>',
		'lock'            => '<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
		'receipt'         => '<path d="M6 2h12v20l-3-2-3 2-3-2-3 2z"/><path d="M9 7h6M9 11h6M9 15h4"/>',
		'shield-alt'      => '<path d="M12 2l8 4v6c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6z"/><path d="M9 12l2 2 4-4"/>',
		'undo'            => '<path d="M3 7v6h6"/><path d="M3 13a9 9 0 1 0 3-7.7L3 7"/>',
		'heart'           => '<path d="M12 21s-7.5-4.6-9.3-9.2C1.4 8.5 3.6 5 7 5c2 0 3.5 1.1 5 3 1.5-1.9 3-3 5-3 3.4 0 5.6 3.5 4.3 6.8C19.5 16.4 12 21 12 21z"/>',
		'eye'             => '<path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
		'search'          => '<circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/>',
		'filter'          => '<path d="M3 5h18l-7 8v6l-4 2v-8z"/>',
		'print'           => '<path d="M6 9V2h12v7"/><rect x="3" y="9" width="18" height="8" rx="2"/><path d="M6 14h12v8H6z"/>',
		'link'            => '<path d="M10 14a5 5 0 0 0 7 0l3-3a5 5 0 0 0-7-7l-1 1"/><path d="M14 10a5 5 0 0 0-7 0l-3 3a5 5 0 0 0 7 7l1-1"/>',
	);
	$path = isset( $paths[ $name ] ) ? $paths[ $name ] : $paths['shopping-cart'];
	return '<span class="shopglut-ic shopglut-ic-' . esc_attr( $name ) . ( $class ? ' ' . esc_attr( $class ) : '' ) . '" aria-hidden="true"><svg viewBox="0 0 24 24" width="1em" height="1em" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $path . '</svg></span>';
}

/**
 * SVG icon from a saved Font Awesome class string ("fas fa-shield-alt"), for settings of the old plugins.
 *
 * @param string $classes Font Awesome classes.
 * @param string $class   Extra classes.
 * @return string
 */
function shopglut_fa_icon( $classes, $class = '' ) {
	$name = preg_match( '/\bfa-(?!solid\b|regular\b|brands\b)([a-z0-9-]+)/', (string) $classes, $m ) ? $m[1] : 'check';
	return shopglut_icon( $name, $class );
}

/**
 * Whether front-end output hooked to classic WooCommerce template hooks should print now.
 *
 * In block themes WooCommerce fires those hooks for compatibility while blocks render, and modules add
 * their output through block filters instead; only legacy (classic PHP) templates rendered inside a
 * block theme still need the classic hooks.
 *
 * @return bool
 */
function shopglut_classic_hooks() {
	return ! wp_is_block_theme() || ShopGlut_Legacy_Template::$depth > 0;
}

/**
 * Tracks WooCommerce legacy-template blocks being rendered (see shopglut_classic_hooks()).
 */
class ShopGlut_Legacy_Template {

	/**
	 * Legacy templates rendering now.
	 *
	 * @var int
	 */
	public static $depth = 0;

	/**
	 * Register the block filters.
	 *
	 * @return void
	 */
	public static function init() {
		add_filter(
			'render_block_data',
			static function ( $block ) {
				if ( isset( $block['blockName'] ) && 'woocommerce/legacy-template' === $block['blockName'] ) {
					++self::$depth;
				}
				return $block;
			}
		);
		add_filter(
			'render_block_woocommerce/legacy-template',
			static function ( $html ) {
				self::$depth = max( 0, self::$depth - 1 );
				return $html;
			}
		);
	}
}
ShopGlut_Legacy_Template::init();
