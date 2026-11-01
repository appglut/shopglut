<?php
/**
 * Shop Filters: product filters (categories, tags, attributes, price, rating, stock) for the shop.
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Module_Shop_Filters class.
 */
class ShopGlut_Module_Shop_Filters extends ShopGlut_Module {

	/**
	 * Module ID.
	 *
	 * @return string
	 */
	public function id() {
		return 'shop-filters';
	}

	/**
	 * Module info.
	 *
	 * @return array
	 */
	public function info() {
		return array(
			'title'        => __( 'Shop Filters', 'shopglut' ),
			'description'  => __( 'Filter panels for the shop: categories, tags, attributes, price, rating and stock, in 20+ designs.', 'shopglut' ),
			'icon'         => 'faFilter',
			'category'     => 'product',
			'order'        => 80,
			'layouts'      => true,
			'layout_label' => __( 'Filter', 'shopglut' ),
			'layouts_label' => __( 'Filters', 'shopglut' ),
			'features'     => array(
				__( 'Category, tag, attribute, price, rating and stock filters', 'shopglut' ),
				__( 'Checkbox, radio, dropdown, button, color, image, slider and tree designs', 'shopglut' ),
				__( 'Include / exclude terms per filter', 'shopglut' ),
			),
			'pro_features' => array(
				__( 'Load more and infinite scroll results', 'shopglut' ),
				__( 'Off-canvas mobile filter drawer', 'shopglut' ),
				__( 'Active filter chips and SEO-friendly filter URLs', 'shopglut' ),
			),
		);
	}

	/**
	 * One "template": the filter editor.
	 *
	 * @return array
	 */
	public function templates() {
		return array(
			'filter' => array(
				'title'       => __( 'Filter panel', 'shopglut' ),
				'description' => __( 'A set of filters with their designs.', 'shopglut' ),
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
		return $this->schema( $this->dir . 'settings/filter.php' );
	}
}

return 'ShopGlut_Module_Shop_Filters';
