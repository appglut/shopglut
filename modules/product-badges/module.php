<?php
/**
 * Product Badges: sale, new, out-of-stock and custom badges on product images and titles.
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Module_Product_Badges class.
 */
class ShopGlut_Module_Product_Badges extends ShopGlut_Module {

	/**
	 * Module ID.
	 *
	 * @return string
	 */
	public function id() {
		return 'product-badges';
	}

	/**
	 * Module info.
	 *
	 * @return array
	 */
	public function info() {
		return array(
			'title'        => __( 'Product Badges', 'shopglut' ),
			'description'  => __( 'Sale, new, out-of-stock and custom badges with conditions, positions and full styling.', 'shopglut' ),
			'icon'         => 'faTags',
			'category'     => 'product',
			'order'        => 70,
			'layouts'      => true,
			'layout_label' => __( 'Badge', 'shopglut' ),
			'layouts_label' => __( 'Badges', 'shopglut' ),
			'features'     => array(
				__( 'Unlimited badges', 'shopglut' ),
				__( 'Sale (percent / amount), new and out-of-stock conditions', 'shopglut' ),
				__( '6 image positions or before the title', 'shopglut' ),
				__( 'Colors, fonts, padding, radius, border and shadow', 'shopglut' ),
			),
			'pro_features' => array(
				__( 'Advanced shapes (ribbon, circle, corner) and animations', 'shopglut' ),
				__( 'Category, tag and attribute conditions', 'shopglut' ),
				__( 'Badge priority ordering and scheduling', 'shopglut' ),
			),
		);
	}

	/**
	 * One badge "template": the badge editor.
	 *
	 * @return array
	 */
	public function templates() {
		return array(
			'badge' => array(
				'title'       => __( 'Badge', 'shopglut' ),
				'description' => __( 'Text badge with conditions and styling.', 'shopglut' ),
			),
		);
	}

	/**
	 * Layout options.
	 *
	 * @param string $template Template ID.
	 * @param array  $layout   Layout row.
	 * @return array
	 */
	public function layout_sections( $template, $layout ) {
		return $this->schema( $this->dir . 'settings/badge.php' );
	}

	/**
	 * Front end: badges on product images and before titles.
	 *
	 * @return void
	 */
	public function boot() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		require_once $this->dir . 'frontend/class-shopglut-product-badges.php';
		new ShopGlut_Product_Badges();
	}
}

return 'ShopGlut_Module_Product_Badges';
