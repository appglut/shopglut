<?php
/**
 * Single product page in a block theme with a ShopGlut Product Page layout: the theme's header and
 * footer template parts around the layout. Rendered first, like WordPress's block template canvas, so
 * block styles are enqueued before wp_head().
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

ob_start();
echo do_blocks( '<!-- wp:template-part {"slug":"header","tagName":"header"} /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- block output.
echo '<main class="wp-block-group shopglut-product-main" style="margin-top:var(--wp--preset--spacing--40, 2rem)"><div class="sgpp-container">';
echo '<div class="woocommerce">';
woocommerce_output_all_notices();
woocommerce_breadcrumb();
while ( have_posts() ) {
	the_post();
	if ( isset( $GLOBALS['shopglut_product_page'] ) ) {
		$GLOBALS['shopglut_product_page']->render();
	}
}
echo '</div></div></main>';
echo do_blocks( '<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- block output.
$shopglut_page = ob_get_clean();
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'woocommerce woocommerce-page' ); ?>>
<?php wp_body_open(); ?>
<div class="wp-site-blocks">
<?php echo $shopglut_page; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rendered above. ?>
</div>
<?php wp_footer(); ?>
</body>
</html>
