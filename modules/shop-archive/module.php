<?php
/**
 * Shop & Archive Builder: layouts for the shop page and product category / tag archives.
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Module_Shop_Archive class.
 */
class ShopGlut_Module_Shop_Archive extends ShopGlut_Module {

	/**
	 * Module ID.
	 *
	 * @return string
	 */
	public function id() {
		return 'shop-archive';
	}

	/**
	 * Module info.
	 *
	 * @return array
	 */
	public function info() {
		return array(
			'title'        => __( 'Shop & Archive Builder', 'shopglut' ),
			'description'  => __( 'Product grids and lists for the shop page, categories and tags, with toolbar, pagination and card styling.', 'shopglut' ),
			'icon'         => 'faStore',
			'category'     => 'builders',
			'order'        => 20,
			'layouts'      => true,
			'layout_label' => __( 'Layout', 'shopglut' ),
			'features'     => array(
				__( '10 free product card templates', 'shopglut' ),
				__( 'Grid / list views, columns per device, pagination styles', 'shopglut' ),
				__( 'Separate layouts for the shop page, categories and tags', 'shopglut' ),
			),
			'pro_features' => array(
				__( '9 more card templates (11 – 19)', 'shopglut' ),
				__( 'Load more and infinite scroll pagination', 'shopglut' ),
			),
		);
	}

	/**
	 * Templates.
	 *
	 * @return array
	 */
	public function templates() {
		return $this->numbered_templates( 19, 10 );
	}

	/**
	 * Every template shares one set of options.
	 *
	 * @param string $template Template ID.
	 * @param array  $layout   Layout row.
	 * @return array
	 */
	public function layout_sections( $template, $layout ) {
		return $this->schema( $this->dir . 'settings/layout.php' );
	}

	/**
	 * Where the layout applies.
	 *
	 * @return array
	 */
	public function display_fields() {
		return array(
			array(
				'id'      => 'pages',
				'type'    => 'checkbox',
				'title'   => __( 'Use this layout on', 'shopglut' ),
				'options' => array(
					'shop'       => __( 'Shop page', 'shopglut' ),
					'categories' => __( 'Product categories', 'shopglut' ),
					'tags'       => __( 'Product tags', 'shopglut' ),
					'search'     => __( 'Product search results', 'shopglut' ),
				),
				'default' => array( 'shop' ),
			),
			array(
				'id'         => 'categories',
				'type'       => 'select',
				'title'      => __( 'Only these categories', 'shopglut' ),
				'desc'       => __( 'Leave empty for every category.', 'shopglut' ),
				'options'    => 'terms',
				'query_args' => array( 'taxonomy' => 'product_cat' ),
				'multiple'   => true,
				'dependency' => array( 'pages', 'any', 'categories' ),
			),
		);
	}

	/**
	 * Whether a layout applies to the current archive.
	 *
	 * @param array $layout  Layout.
	 * @param array $context page (shop | categories | tags | search), term_id.
	 * @return bool
	 */
	public function layout_matches( $layout, $context ) {
		$rules = isset( $layout['settings']['display'] ) ? (array) $layout['settings']['display'] : array();
		$pages = isset( $rules['pages'] ) ? (array) $rules['pages'] : array( 'shop' );
		$page  = isset( $context['page'] ) ? $context['page'] : '';

		if ( ! in_array( $page, $pages, true ) ) {
			return false;
		}
		if ( 'categories' === $page && ! empty( $rules['categories'] ) ) {
			return in_array( absint( $context['term_id'] ?? 0 ), array_map( 'absint', (array) $rules['categories'] ), true );
		}
		return true;
	}
}

return 'ShopGlut_Module_Shop_Archive';
