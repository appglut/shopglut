<?php
/**
 * Product card in a ShopGlut shop layout (replaces content-product.php inside the layout's loop).
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

global $product;
if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}
if ( isset( $GLOBALS['shopglut_shop_archive'] ) ) {
	$GLOBALS['shopglut_shop_archive']->classic_card();
}
