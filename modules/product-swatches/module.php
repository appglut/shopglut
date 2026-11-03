<?php
/**
 * Variation Swatches: color, image, button, radio and bicolor swatches for variation attributes.
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Module_Product_Swatches class.
 */
class ShopGlut_Module_Product_Swatches extends ShopGlut_Module {

	/**
	 * Module ID.
	 *
	 * @return string
	 */
	public function id() {
		return 'product-swatches';
	}

	/**
	 * Module info.
	 *
	 * @return array
	 */
	public function info() {
		return array(
			'title'        => __( 'Variation Swatches', 'shopglut' ),
			'description'  => __( 'Replace variation dropdowns with color, image, label and radio swatches on product and shop pages.', 'shopglut' ),
			'icon'         => 'faPalette',
			'category'     => 'product',
			'order'        => 60,
			'layouts'      => true,
			'layout_label' => __( 'Swatch style', 'shopglut' ),
			'layouts_label' => __( 'Swatch styles', 'shopglut' ),
			'features'     => array(
				__( '10 free swatch templates', 'shopglut' ),
				__( 'Color, image, button and radio swatches', 'shopglut' ),
				__( 'Per-attribute styles, out-of-stock states', 'shopglut' ),
			),
			'pro_features' => array(
				__( '20 more swatch templates (11 – 30)', 'shopglut' ),
				__( 'Swatches on shop and archive cards', 'shopglut' ),
				__( 'Bicolor swatches and image swatch zoom', 'shopglut' ),
			),
		);
	}

	/**
	 * Templates.
	 *
	 * @return array
	 */
	public function templates() {
		return $this->numbered_templates( 30, 10, $this->url . 'images/product-swatches-%s.jpg' );
	}

	/**
	 * Layout options.
	 *
	 * @param string $template Template ID.
	 * @param array  $layout   Layout row.
	 * @return array
	 */
	public function layout_sections( $template, $layout ) {
		return $this->template_sections( $template, $layout );
	}

	/**
	 * Front end: swatches instead of variation dropdowns; swatch fields on attribute terms.
	 *
	 * @return void
	 */
	public function boot() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		require_once $this->dir . 'frontend/class-shopglut-swatches.php';
		new ShopGlut_Swatches( $this->url );
	}

	/**
	 * Which attributes use the style.
	 *
	 * @return array
	 */
	public function display_fields() {
		$attributes = array();
		if ( function_exists( 'wc_get_attribute_taxonomies' ) ) {
			foreach ( wc_get_attribute_taxonomies() as $tax ) {
				$attributes[ wc_attribute_taxonomy_name( $tax->attribute_name ) ] = $tax->attribute_label;
			}
		}
		return array(
			array(
				'id'      => 'attributes',
				'type'    => 'select',
				'title'   => __( 'Use for these attributes', 'shopglut' ),
				'desc'    => __( 'Leave empty for every attribute.', 'shopglut' ),
				'options' => $attributes,
				'multiple' => true,
			),
		);
	}

	/**
	 * Whether a style applies to an attribute.
	 *
	 * @param array $layout  Layout.
	 * @param array $context taxonomy.
	 * @return bool
	 */
	public function layout_matches( $layout, $context ) {
		$only = (array) ( $layout['settings']['display']['attributes'] ?? array() );
		return ! $only || in_array( (string) ( $context['taxonomy'] ?? '' ), $only, true );
	}
}

return 'ShopGlut_Module_Product_Swatches';
