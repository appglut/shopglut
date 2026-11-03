<?php
/**
 * Thank you page (replaces checkout/thankyou.php while a ShopGlut Thank You layout is active).
 *
 * @package ShopGlut
 * @var WC_Order $order
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="woocommerce-order">
	<?php
	if ( isset( $order ) && $order instanceof WC_Order && isset( $GLOBALS['shopglut_order_complete'] ) ) {
		$GLOBALS['shopglut_order_complete']->classic_output( $order );
	}
	?>
</div>
