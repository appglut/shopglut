<?php
/**
 * Cart (replaces cart/cart.php and cart/cart-empty.php while a ShopGlut cart layout is active).
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_cart' );

if ( isset( $GLOBALS['shopglut_cart_page'] ) ) {
	$GLOBALS['shopglut_cart_page']->classic_output();
}

do_action( 'woocommerce_after_cart' );
