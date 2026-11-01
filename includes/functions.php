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
