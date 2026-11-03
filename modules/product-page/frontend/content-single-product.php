<?php
/**
 * Single product content in a ShopGlut Product Page layout (classic themes).
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

if ( isset( $GLOBALS['shopglut_product_page'] ) ) {
	$GLOBALS['shopglut_product_page']->render();
}
