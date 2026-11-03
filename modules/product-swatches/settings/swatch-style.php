<?php
/**
 * Settings of swatch templates 3–10 (one file, defaults per template). Included by templateN.php with
 * $shopglut_swatch_template set to N.
 *
 * Saved as settings[shopg_product_swatches_settings_templateN][field].
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- file-scope variables, included inside a function by the loader.
$shopglut_swatch_designs = array(
	3  => array( __( 'Round color', 'shopglut' ), 'circle', 32 ),
	4  => array( __( 'Square color', 'shopglut' ), 'rounded', 34 ),
	5  => array( __( 'Image thumbnail', 'shopglut' ), 'rounded', 56 ),
	6  => array( __( 'Pill label', 'shopglut' ), 'pill', 36 ),
	7  => array( __( 'Radio list', 'shopglut' ), 'square', 18 ),
	8  => array( __( 'Color with label', 'shopglut' ), 'circle', 22 ),
	9  => array( __( 'Image with label', 'shopglut' ), 'rounded', 44 ),
	10 => array( __( 'Underline', 'shopglut' ), 'square', 34 ),
);
$shopglut_swatch_n      = isset( $shopglut_swatch_template ) ? (int) $shopglut_swatch_template : 3;
$shopglut_swatch_design = $shopglut_swatch_designs[ $shopglut_swatch_n ] ?? $shopglut_swatch_designs[3];
$shopglut_swatch_box    = 'shopg_product_swatches_settings_template' . $shopglut_swatch_n;

ShopGlut_Schema::createMetabox(
	$shopglut_swatch_box,
	array(
		/* translators: %s: swatch design name */
		'title'     => sprintf( __( 'Swatch style: %s', 'shopglut' ), $shopglut_swatch_design[0] ),
		'post_type' => 'product_swatches',
		'context'   => 'side',
	)
);

ShopGlut_Schema::createSection(
	$shopglut_swatch_box,
	array(
		'fields' => array(
			array(
				'id'     => 'product-swatches-settings',
				'type'   => 'fieldset',
				'title'  => __( 'WooCommerce integration', 'shopglut' ),
				'fields' => array(
					array(
						'id'      => 'enable_variation_overwrite',
						'type'    => 'switcher',
						'title'   => __( 'Replace the variation dropdowns', 'shopglut' ),
						'default' => true,
					),
				),
			),
			array(
				'type'    => 'notice',
				'style'   => 'info',
				'content' => __( 'Colors, images and short labels come from Products › Attributes › (attribute) › terms. Terms without a color or image show their name.', 'shopglut' ),
			),
			array(
				'id'     => 'swatch_style',
				'type'   => 'fieldset',
				'title'  => __( 'Swatches', 'shopglut' ),
				'fields' => array(
					array(
						'id'      => 'size',
						'type'    => 'slider',
						'title'   => __( 'Size', 'shopglut' ),
						'unit'    => 'px',
						'min'     => 14,
						'max'     => 90,
						'default' => $shopglut_swatch_design[2],
					),
					array(
						'id'      => 'shape',
						'type'    => 'button_set',
						'title'   => __( 'Shape', 'shopglut' ),
						'options' => array(
							'circle'  => __( 'Circle', 'shopglut' ),
							'rounded' => __( 'Rounded', 'shopglut' ),
							'square'  => __( 'Square', 'shopglut' ),
							'pill'    => __( 'Pill', 'shopglut' ),
						),
						'default' => $shopglut_swatch_design[1],
					),
					array(
						'id'      => 'gap',
						'type'    => 'slider',
						'title'   => __( 'Space between', 'shopglut' ),
						'unit'    => 'px',
						'min'     => 0,
						'max'     => 30,
						'default' => 8,
					),
					array(
						'id'      => 'background',
						'type'    => 'color',
						'title'   => __( 'Background (labels)', 'shopglut' ),
						'default' => '#ffffff',
					),
					array(
						'id'      => 'text_color',
						'type'    => 'color',
						'title'   => __( 'Text color', 'shopglut' ),
						'default' => '#1f2937',
					),
					array(
						'id'      => 'border_color',
						'type'    => 'color',
						'title'   => __( 'Border', 'shopglut' ),
						'default' => '#d1d5db',
					),
					array(
						'id'      => 'active_color',
						'type'    => 'color',
						'title'   => __( 'Selected', 'shopglut' ),
						'desc'    => __( 'Empty uses the store accent color.', 'shopglut' ),
						'default' => '',
					),
				),
			),
			array(
				'id'     => 'swatch_behavior',
				'type'   => 'fieldset',
				'title'  => __( 'Behavior', 'shopglut' ),
				'fields' => array(
					array(
						'id'      => 'label_position',
						'type'    => 'button_set',
						'title'   => __( 'Attribute name', 'shopglut' ),
						'options' => array(
							'inline'  => __( 'Beside', 'shopglut' ),
							'stacked' => __( 'Above', 'shopglut' ),
						),
						'default' => 'stacked',
					),
					array(
						'id'      => 'show_selected',
						'type'    => 'switcher',
						'title'   => __( 'Show the chosen value ("Color: Red")', 'shopglut' ),
						'default' => true,
					),
					array(
						'id'      => 'tooltip',
						'type'    => 'switcher',
						'title'   => __( 'Tooltip with the name', 'shopglut' ),
						'default' => true,
					),
					array(
						'id'      => 'unavailable',
						'type'    => 'button_set',
						'title'   => __( 'Unavailable choices', 'shopglut' ),
						'options' => array(
							'cross' => __( 'Cross out', 'shopglut' ),
							'blur'  => __( 'Fade', 'shopglut' ),
							'hide'  => __( 'Hide', 'shopglut' ),
						),
						'default' => 'cross',
					),
					array(
						'id'      => 'deselect',
						'type'    => 'switcher',
						'title'   => __( 'Click again to deselect', 'shopglut' ),
						'default' => true,
					),
				),
			),
			array(
				'id'   => 'per_term_styling',
				'type' => 'term_styling',
			),
		),
	)
);
