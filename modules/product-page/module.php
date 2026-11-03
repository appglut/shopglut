<?php
/**
 * Product Page Builder: layouts for the single product page.
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Module_Product_Page class.
 */
class ShopGlut_Module_Product_Page extends ShopGlut_Module {

	/**
	 * Module ID.
	 *
	 * @return string
	 */
	public function id() {
		return 'product-page';
	}

	/**
	 * Module info.
	 *
	 * @return array
	 */
	public function info() {
		return array(
			'title'        => __( 'Product Page Builder', 'shopglut' ),
			'description'  => __( 'Design the single product page with ready templates: gallery, summary, tabs, related products and more.', 'shopglut' ),
			'icon'         => 'faShirt',
			'category'     => 'builders',
			'order'        => 10,
			'layouts'      => true,
			'layout_label' => __( 'Layout', 'shopglut' ),
			'features'     => array(
				__( '7 free templates with live demo preview', 'shopglut' ),
				__( 'Gallery, summary, attributes, tabs and related products styling', 'shopglut' ),
				__( 'Apply to all products, categories or chosen products', 'shopglut' ),
				__( 'Works with classic and block themes', 'shopglut' ),
			),
			'pro_features' => array(
				__( '10 Pro templates (Pro 1 – Pro 10)', 'shopglut' ),
				__( 'Countdown, stock progress bar and advanced gallery effects', 'shopglut' ),
			),
		);
	}

	/**
	 * Templates.
	 *
	 * @return array
	 */
	public function templates() {
		$templates = $this->numbered_templates( 7, 7, $this->url . 'images/product-page-%s.jpg' );
		for ( $i = 1; $i <= 10; $i++ ) {
			$templates[ 'templatePro' . $i ] = array(
				/* translators: %d: template number */
				'title' => sprintf( __( 'Pro Template %d', 'shopglut' ), $i ),
				'image' => '',
				'pro'   => true,
			);
		}
		return $templates;
	}

	/**
	 * Layout options: the template's settings.
	 *
	 * @param string $template Template ID.
	 * @param array  $layout   Layout row.
	 * @return array
	 */
	public function layout_sections( $template, $layout ) {
		return $this->template_sections( $template, $layout );
	}

	/**
	 * Where the layout applies.
	 *
	 * @return array
	 */
	public function display_fields() {
		return array(
			array(
				'id'      => 'apply_to',
				'type'    => 'button_set',
				'title'   => __( 'Use this layout for', 'shopglut' ),
				'options' => array(
					'all'        => __( 'All products', 'shopglut' ),
					'categories' => __( 'Categories', 'shopglut' ),
					'products'   => __( 'Chosen products', 'shopglut' ),
				),
				'default' => 'all',
			),
			array(
				'id'         => 'categories',
				'type'       => 'select',
				'title'      => __( 'Categories', 'shopglut' ),
				'options'    => 'terms',
				'query_args' => array( 'taxonomy' => 'product_cat' ),
				'multiple'   => true,
				'dependency' => array( 'apply_to', '==', 'categories' ),
			),
			array(
				'id'         => 'products',
				'type'       => 'select',
				'title'      => __( 'Products', 'shopglut' ),
				'options'    => 'post_type',
				'query_args' => array( 'post_type' => 'product' ),
				'multiple'   => true,
				'ajax'       => true,
				'dependency' => array( 'apply_to', '==', 'products' ),
			),
			array(
				'id'         => 'exclude',
				'type'       => 'select',
				'title'      => __( 'Except these products', 'shopglut' ),
				'options'    => 'post_type',
				'query_args' => array( 'post_type' => 'product' ),
				'multiple'   => true,
				'ajax'       => true,
			),
		);
	}

	/**
	 * Whether a layout applies to a product.
	 *
	 * @param array $layout  Layout.
	 * @param array $context product_id.
	 * @return bool
	 */
	/**
	 * Front end: the single product page uses the matching layout.
	 *
	 * @return void
	 */
	public function boot() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		require_once $this->dir . 'frontend/class-shopglut-product-page.php';
		$GLOBALS['shopglut_product_page'] = new ShopGlut_Product_Page( $this->url );
	}

	public function layout_matches( $layout, $context ) {
		$rules      = isset( $layout['settings']['display'] ) ? (array) $layout['settings']['display'] : array();
		$product_id = isset( $context['product_id'] ) ? absint( $context['product_id'] ) : 0;
		$apply      = isset( $rules['apply_to'] ) ? $rules['apply_to'] : 'all';

		if ( $product_id && in_array( $product_id, array_map( 'absint', (array) ( $rules['exclude'] ?? array() ) ), true ) ) {
			return false;
		}
		if ( 'products' === $apply ) {
			return $product_id && in_array( $product_id, array_map( 'absint', (array) ( $rules['products'] ?? array() ) ), true );
		}
		if ( 'categories' === $apply ) {
			return $product_id && has_term( array_map( 'absint', (array) ( $rules['categories'] ?? array() ) ), 'product_cat', $product_id );
		}
		return true;
	}
}

return 'ShopGlut_Module_Product_Page';
