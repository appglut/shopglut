<?php

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

// Create the live preview metabox
ShopGlut_Schema::createMetabox(
	'shopg_filter_live_preview',
	array(
		'title' => __( 'Live Preview', 'shopglut' ),
		'post_type' => 'shopglut_enhancements',
		'context' => 'normal',
	)
);

// Create the live preview section
ShopGlut_Schema::createSection(
	'shopg_filter_live_preview',
	array(
		'fields' => array(
			array(
				'type' => 'preview',
			),
		),
	)
);

$shopglut_shopg_options_settings = "shopg_filter_options_settings";

// Create the layout settings metabox
ShopGlut_Schema::createMetabox(
	$shopglut_shopg_options_settings,
	array(
		'title' => esc_html__( 'Filter Settings', 'shopglut' ),
		'post_type' => 'shopglut_enhancements',
		'context' => 'side',
	)
);

// Fetch all WooCommerce product attributes

// Build options array dynamically for filter types
$shopglut_shopg_filter_options = array(
	'product-categories' => esc_html__( 'Product Categories', 'shopglut' ),
	'product-tags' => esc_html__( 'Product Tags', 'shopglut' ),
	//'product-sortby' => esc_html__( 'Product Sort By', 'shopglut' ),
	// 'product-search' => esc_html__( 'Search By Text', 'shopglut' ),
	// 'product-price' => esc_html__( 'Product Price', 'shopglut' ),
	//'product-rating' => esc_html__( 'Product Rating', 'shopglut' ),
	//'product-author' => esc_html__( 'Author', 'shopglut' ),
	//'product-status' => esc_html__( 'Product Status', 'shopglut' ),
	// 'product-view' => esc_html__( 'Product View', 'shopglut' ),
	//'product-type' => esc_html__( 'Product Type', 'shopglut' ),
	//'product-order-direction' => esc_html__( 'Product Order Direction', 'shopglut' ),
	// 'product-shipping-class' => esc_html__( 'Product Shipping Class', 'shopglut' ),
);


$shopglut_shopg_attribute_taxonomies = wc_get_attribute_taxonomies();

// // Loop through attributes and add them to filter options
// if ( ! empty( $shopglut_shopg_attribute_taxonomies ) ) {
// 	foreach ( $shopglut_shopg_attribute_taxonomies as $shopg_attribute ) {
// 		if ( isset( $shopg_attribute->attribute_name ) ) {
// 			$shopglut_shopg_attribute_name = wc_attribute_taxonomy_name( $shopg_attribute->attribute_name );
// 			/* translators: %s: Attribute label (e.g., 'Color', 'Size') */
// 			$options[ $shopglut_shopg_attribute_name ] = sprintf( esc_html__( 'Product %s', 'shopglut' ), ucfirst( str_replace( '-', ' ', $shopg_attribute->attribute_label ) ) );
// 		}
// 	}
// }




// One filter of the panel. The front end reads these fields (modules/shop-filters/frontend).
$shopglut_filter_types = array(
	'product-categories' => __( 'Product categories', 'shopglut' ),
	'product-tags'       => __( 'Product tags', 'shopglut' ),
	'product-price'      => __( 'Price', 'shopglut' ),
	'product-rating'     => __( 'Rating', 'shopglut' ),
	'product-stock'      => __( 'Stock status', 'shopglut' ),
	'product-onsale'     => __( 'On sale', 'shopglut' ),
);
foreach ( (array) wc_get_attribute_taxonomies() as $shopglut_filter_attr ) {
	/* translators: %s: attribute name, e.g. Color */
	$shopglut_filter_types[ wc_attribute_taxonomy_name( $shopglut_filter_attr->attribute_name ) ] = sprintf( __( 'Attribute: %s', 'shopglut' ), $shopglut_filter_attr->attribute_label );
}
$shopglut_filter_term_types = array_values( array_diff( array_keys( $shopglut_filter_types ), array( 'product-price', 'product-rating', 'product-stock', 'product-onsale' ) ) );
$shopglut_filter_designs    = SHOPGLUT_URL . 'modules/shop-filters/images/filterDesigns/';

$shopglut_filter_item_fields = array(
	array(
		'id'    => 'accordion-title',
		'type'  => 'text',
		'title' => __( 'Filter title', 'shopglut' ),
	),
	array(
		'id'      => 'filter-type',
		'type'    => 'select',
		'title'   => __( 'Filter type', 'shopglut' ),
		'options' => $shopglut_filter_types,
		'default' => 'product-categories',
	),
	array(
		'id'         => 'filter-term-appearance',
		'type'       => 'image_select',
		'title'      => __( 'Design', 'shopglut' ),
		'options'    => array(
			'check-list' => $shopglut_filter_designs . 'checkbox.png',
			'radio'      => $shopglut_filter_designs . 'radio.png',
			'dropdown'   => $shopglut_filter_designs . 'dropdown.png',
			'button'     => $shopglut_filter_designs . 'button.png',
		),
		'default'    => 'check-list',
		'dependency' => array( 'filter-type', 'any', 'product-categories,product-tags' ),
	),
	array(
		'id'         => 'filter-query-type',
		'type'       => 'button_set',
		'title'      => __( 'Several choices match', 'shopglut' ),
		'options'    => array(
			'or'  => __( 'Any of them', 'shopglut' ),
			'and' => __( 'All of them', 'shopglut' ),
		),
		'default'    => 'or',
		'dependency' => array( 'filter-type', 'any', implode( ',', $shopglut_filter_term_types ) ),
	),
	array(
		'id'         => 'filter-category-exclude-include-button',
		'type'       => 'button_set',
		'title'      => __( 'Categories', 'shopglut' ),
		'options'    => array(
			'all'     => __( 'All', 'shopglut' ),
			'include' => __( 'Only these', 'shopglut' ),
			'exclude' => __( 'All except', 'shopglut' ),
		),
		'default'    => 'all',
		'dependency' => array( 'filter-type', '==', 'product-categories' ),
	),
	array(
		'id'         => 'shopg-filter-include-category',
		'type'       => 'select',
		'title'      => __( 'Show only', 'shopglut' ),
		'options'    => 'categories',
		'query_args' => array( 'taxonomy' => 'product_cat' ),
		'multiple'   => true,
		'chosen'     => true,
		'dependency' => array( 'filter-type|filter-category-exclude-include-button', '==|==', 'product-categories|include' ),
	),
	array(
		'id'         => 'shopg-filter-exclude-category',
		'type'       => 'select',
		'title'      => __( 'Hide', 'shopglut' ),
		'options'    => 'categories',
		'query_args' => array( 'taxonomy' => 'product_cat' ),
		'multiple'   => true,
		'chosen'     => true,
		'dependency' => array( 'filter-type|filter-category-exclude-include-button', '==|==', 'product-categories|exclude' ),
	),
	array(
		'id'         => 'filter-tag-exclude-include-button',
		'type'       => 'button_set',
		'title'      => __( 'Tags', 'shopglut' ),
		'options'    => array(
			'all'     => __( 'All', 'shopglut' ),
			'include' => __( 'Only these', 'shopglut' ),
			'exclude' => __( 'All except', 'shopglut' ),
		),
		'default'    => 'all',
		'dependency' => array( 'filter-type', '==', 'product-tags' ),
	),
	array(
		'id'         => 'shopg-filter-include-tag',
		'type'       => 'select',
		'title'      => __( 'Show only', 'shopglut' ),
		'options'    => 'tags',
		'query_args' => array( 'taxonomy' => 'product_tag' ),
		'multiple'   => true,
		'chosen'     => true,
		'dependency' => array( 'filter-type|filter-tag-exclude-include-button', '==|==', 'product-tags|include' ),
	),
	array(
		'id'         => 'shopg-filter-exclude-tag',
		'type'       => 'select',
		'title'      => __( 'Hide', 'shopglut' ),
		'options'    => 'tags',
		'query_args' => array( 'taxonomy' => 'product_tag' ),
		'multiple'   => true,
		'chosen'     => true,
		'dependency' => array( 'filter-type|filter-tag-exclude-include-button', '==|==', 'product-tags|exclude' ),
	),
	array(
		'id'         => 'filter-product-price-appearance',
		'type'       => 'image_select',
		'title'      => __( 'Design', 'shopglut' ),
		'options'    => array(
			'price-range-slider' => $shopglut_filter_designs . 'normal-slider.png',
			'price-range-radio'  => $shopglut_filter_designs . 'radio-slider.png',
		),
		'default'    => 'price-range-slider',
		'dependency' => array( 'filter-type', '==', 'product-price' ),
	),
	array(
		'id'         => 'filter-product-price-step',
		'type'       => 'number',
		'title'      => __( 'Slider step', 'shopglut' ),
		'default'    => 1,
		'dependency' => array( 'filter-type|filter-product-price-appearance', '==|==', 'product-price|price-range-slider' ),
	),
	array(
		'id'         => 'filter-price-ranges',
		'type'       => 'textarea',
		'title'      => __( 'Price ranges', 'shopglut' ),
		'desc'       => __( 'One per line: min-max, e.g. 0-25. Leave the max empty for "and above": 100-', 'shopglut' ),
		'default'    => "0-25\n25-50\n50-100\n100-",
		'dependency' => array( 'filter-type|filter-product-price-appearance', '==|==', 'product-price|price-range-radio' ),
	),
	array(
		'id'         => 'filter-product-rating-appearance',
		'type'       => 'image_select',
		'title'      => __( 'Design', 'shopglut' ),
		'options'    => array(
			'star-rating'     => $shopglut_filter_designs . 'star-rating.png',
			'checkbox-rating' => $shopglut_filter_designs . 'checkbox-rating.png',
		),
		'default'    => 'star-rating',
		'dependency' => array( 'filter-type', '==', 'product-rating' ),
	),
	array(
		'id'         => 'filter-stock-options',
		'type'       => 'checkbox',
		'title'      => __( 'Choices', 'shopglut' ),
		'options'    => array(
			'instock'     => __( 'In stock', 'shopglut' ),
			'onbackorder' => __( 'On backorder', 'shopglut' ),
			'outofstock'  => __( 'Out of stock', 'shopglut' ),
		),
		'default'    => array( 'instock', 'onbackorder', 'outofstock' ),
		'dependency' => array( 'filter-type', '==', 'product-stock' ),
	),
	array(
		'id'      => 'filter-collapsed',
		'type'    => 'switcher',
		'title'   => __( 'Start collapsed', 'shopglut' ),
		'default' => false,
	),
);

// Main fields array
$shopglut_shopg_fields = array(
	array(
		'id' => 'shopglut-filter-settings-main-tab',
		'type' => 'tabbed',
		'tabs' => array(
			
			array(
				'class' => "shopglut-filter-settings-main-tab",
				'title' => __( 'Filter', 'shopglut' ),
				'icon' => 'fa-solid fa-filter',
				'fields' => array(
					array(
						'id' => 'shopg-filter-add-new',
						'type' => 'repeater',
						'button_title' => __( "Add New", 'shopglut' ),
						'fields' => array(

							array(
								'id' => 'shopg-filter-accordion',
								'type' => 'accordion',
								'accordions' => array(
									array(
										'title' => __( 'Title', 'shopglut' ),
										'fields' => array(
											array(
												'id' => 'shopg-filter-sub-tabbed',
												'type' => 'tabbed',
												'tabs' => array(
													array(
														'class' => 'general-tab',
														'title' => __( 'General', 'shopglut' ),
														'icon' => 'fa-solid fa-gears',
														'fields' => $shopglut_filter_item_fields,
													),
													array(
														'class' => 'appearance-tab',
														'title' => __( 'Appearance', 'shopglut' ),
														'icon' => 'fa-solid fa-vest',
														'fields' => array(

															array(
																'id' => 'filter-show-title',
																'type' => 'switcher',
																'title' => __( "Show Filter Title", 'shopglut' ),
																'text_on' => __( 'Yes', 'shopglut' ),
																'text_off' => __( 'No', 'shopglut' ),
																'default' => true,
															),

															array(
																'id' => 'filter-content-bg-color',
																'type' => 'color',
																'title' => __( "Content Background Color", 'shopglut' ),
																'default' => '#fff',
															),

															array(
																'id' => 'filter-content-font-color',
																'type' => 'color',
																'title' => __( "Content Font Color", 'shopglut' ),
																'default' => '#000',
															),

															array(
																'id' => 'show-count',
																'type' => 'switcher',
																'desc' => __( 'Show count of product items', 'shopglut' ),
																'title' => __( "Show Count", 'shopglut' ),
																'text_on' => __( 'Yes', 'shopglut' ),
																'text_off' => __( 'No', 'shopglut' ),
																'default' => false,
															),

															// Tooltip settings removed as requested





														),
													),


												),
											),
										),
									),
								),
							),
						),
					),
				),
			),	
			array(
				'class' => "shopglut-filter-settings-main-tab2",
				'title' => __( 'Settings', 'shopglut' ),
				'icon' => 'fa fa-gear',
				'fields' => array(

					array(
						'id' => 'filter-show-on-pages',
						'type' => 'select_filter_page',
						'title' => __( 'Show On Pages', 'shopglut' ),
						'chosen' => true,
						'multiple' => true,
						'options' => 'select_filter_page'
					),

					array(
						'id' => 'filter-option',
						'type' => 'select',
						'title' => __( 'Filter Option', 'shopglut' ),
						'desc' => __( 'Choose How filter will work', 'shopglut' ),
						'options' => array(
							//'select-submit-filter' => esc_html__( 'Multi Select and Submit Filter', 'shopglut' ),
							'select-apply-filter' => esc_html__( 'Multi Select and Apply Filter', 'shopglut' ),
							//'select-ajax-filter' => esc_html__( 'Select And Ajax Filter', 'shopglut' ),
						),
						'default' => 'select-apply-filter',
					),

					// array(
					// 	'id' => 'filter-multi-select-condition',
					// 	'type' => 'button_set',
					// 	'title' => __( 'Multi Select Condition', 'shopglut' ),
					// 	'options' => array(
					// 		'and' => __( 'AND', 'shopglut' ),
					// 		'or' => __( 'OR', 'shopglut' ),
					// 	),
					// 	'default' => 'and',
					// 	'dependency' => array( 'filter-option', 'any', "select-submit-filter,select-apply-filter,select-ajax-filter" ),

					// ),

					array(
						'id' => 'filter-title-appearance',
						'type' => 'image_select',
						'title' => __( 'Filter Title Appearance', 'shopglut' ),
						'desc' => __( 'First is Accordion Title, Second is Normal Title', 'shopglut' ),
						'options' => array(
							'accordion-design' => SHOPGLUT_URL . 'modules/shop-filters/images/filterDesigns/title1.png',
							'normal-design' => SHOPGLUT_URL . 'modules/shop-filters/images/filterDesigns/title2.png',
						),
						'default' => 'accordion-design'
					),

					array(
						'id' => 'filter-title-group',
						'type' => 'fieldset',
						'title' => __( 'Choose Filter Title Options', 'shopglut' ),
						'fields' => array(

							array(
								'id' => 'filter-title-color-groups',
								'type' => 'color_group',
								'title' => __( 'Filter Title Color Options ', 'shopglut' ),
								'options' => array(
									'filter-title-bg-color' => __( 'Title Background Color ', 'shopglut' ),
									'filter-title-color' => __( 'Title Color ', 'shopglut' ),
									'filter-title-icon-color' => __( 'Title Icon Color ', 'shopglut' ),
								)
							),

							array(
								'id' => 'filter-title-close-icon',
								'type' => 'icon',
								'title' => __( 'Filter Accordion Close Icon', 'shopglut' ),
								'dependency' => array( 'filter-title-appearance', '==', "accordion-design", 'true' ),
								'default' => 'fa fa-angle-down',
							),

							array(
								'id' => 'filter-title-expand-icon',
								'type' => 'icon',
								'title' => __( 'Filter Accordion Expand Icon', 'shopglut' ),
								'dependency' => array( 'filter-title-appearance', '==', "accordion-design", 'true' ),
								'default' => 'fa fa-angle-right',
							),

							array(
								'id' => 'filter-title-icon',
								'type' => 'icon',
								'title' => __( 'Filter Title Icon', 'shopglut' ),
								'dependency' => array( 'filter-title-appearance', '==', "normal-design", 'true' ),
							),


							array(
								'id' => 'filter-title-border1',
								'type' => 'border',
								'title' => __( 'Filter Title Small Border', 'shopglut' ),
								'right' => false,
								'left' => false,
								'top' => false,
								'default' => array(
									'bottom' => '1',
									'style' => 'solid',
									'color' => '#1e73be',
									'unit' => 'px',
								),
								'dependency' => array( 'filter-title-appearance', '==', "normal-design", 'true' ),
							),

							

							array(
								'id' => 'filter-title-normal-design-hide',
								'type' => 'checkbox',
								'title' => __( 'Hide and Show ', 'shopglut' ),
								'options' => array(
									'hide-icon' => __( 'Hide Icon', 'shopglut' ),
									'hide-small-border' => __( 'Hide Border', 'shopglut' ),
								),
								'dependency' => array( 'filter-title-appearance', '==', "normal-design", 'true' ),
							),


						),
					),


					// array(
					// 	'id' => 'filter-position',
					// 	'type' => 'button_set',
					// 	'title' => __( 'Select Filter Position', 'shopglut' ),
					// 	'options' => array(
					// 		'left-side' => __( 'Left Side', 'shopglut' ),
					// 		'right-side' => __( 'Right Side', 'shopglut' ),
					// 		'top-position' => __( 'Horizontal Above Shop', 'shopglut' ),
					// 	),
					// 	'default' => 'left-side',
					// ),

					// array(
					// 	'id' => 'filter-hide-stock-out',
					// 	'type' => 'switcher',
					// 	'title' => __( "Hide Stock Out Products", 'shopglut' ),
					// 	'text_on' => __( 'Yes', 'shopglut' ),
					// 	'text_off' => __( 'No', 'shopglut' ),
					// 	'default' => false,
					// ),

					// array(
					// 	'id' => 'filter-hide-on-devices',
					// 	'type' => 'button_set',
					// 	'title' => __( 'Hide Filters on Devices', 'shopglut' ),
					// 	'options' => array(
					// 		'no-device' => __( 'No Device', 'shopglut' ),
					// 		'mobile' => __( 'Mobile', 'shopglut' ),
					// 		'tablet' => __( 'Tablet', 'shopglut' ),
					// 		'Desktop' => __( 'Desktop', 'shopglut' ),


					// 	),
					// 	'default' => 'no-device',
					// ),

					array(
						'id' => 'filter-number-products-show',
						'type' => 'number',
						'title' => __( "Number of Products to show", 'shopglut' ),
						'default' => 20,
					),

					// array(
					// 	'id' => 'filter-pagination-style',
					// 	'type' => 'select',
					// 	'title' => __( 'Filter Result Pagination', 'shopglut' ),
					// 	'options' => array(
					// 		'numbers' => __( 'Number Pagination (Default)', 'shopglut' ),
					// 		'loadmore' => __( 'Load More Button', 'shopglut' ),
					// 		'infinite' => __( 'Infinite Scroll', 'shopglut' ),
					// 	),
					// 	'default' => 'numbers',
					// ),

					// array(
					// 	'id' => 'filter-loading-content-show-options',
					// 	'type' => 'button_set',
					// 	'title' => __( 'Show Loading', 'shopglut' ),
					// 	'options' => array(
					// 		'no-loading' => __( 'No Loading Effect', 'shopglut' ),
					// 		'image-loading' => __( 'Loading with Image Effect ', 'shopglut' ),
					// 		'text-loading' => __( 'Loading with Text Effect ', 'shopglut' ),
					// 	),
					// 	'default' => 'no-loading'
					// ),

					// array(
					// 	'id' => 'filter-loading-image',
					// 	'type' => 'media',
					// 	'title' => __( "Loading Image", 'shopglut' ),
					// 	'preview' => true,
					// 	'dependency' => array( 'filter-loading-content-show-options', '==', "image-loading" ),
					// ),


					// array(
					// 	'id' => 'filter-loading-text',
					// 	'type' => 'text',
					// 	'title' => __( "Loading Text", 'shopglut' ),
					// 	'dependency' => array( 'filter-loading-content-show-options', '==', "text-loading" ),
					// ),

					// array(
					// 	'id' => 'filter-loading-image-border',
					// 	'type' => 'border',
					// 	'title' => __( "Loading Image Border", 'shopglut' ),
					// 	'dependency' => array( 'filter-loading-content-show-options', '==', "image-loading" ),
					// ),

					// array(
					// 	'id' => 'filter-loading-text-border',
					// 	'type' => 'border',
					// 	'title' => __( "Loading Text Border", 'shopglut' ),
					// 	'dependency' => array( 'filter-loading-content-show-options', '==', "text-loading" ),
					// ),

					array(
						'id' => 'filter-apply-button-text',
						'type' => 'text',
						'title' => __( "Apply Filter Button Text", 'shopglut' ),
						'dependency' => array( 'filter-option', 'any', "select-submit-filter,select-apply-filter" ),
						'default' => __( "Apply Filter", 'shopglut' ),
					),

					array(
						'id' => 'filter-reset-button-text',
						'type' => 'text',
						'title' => __( "Reset Button Text", 'shopglut' ),
						'dependency' => array( 'filter-option', 'any', "select-submit-filter,select-apply-filter" ),
						'default' => __( " Reset Filter", 'shopglut' ),
					),

				),
			)
			
			
		),
	),

	array(
		'id' => 'save-filter-settings',
		'button_text' => __( 'Save Filter', 'shopglut' ),
		'type' => 'publish',
	),

);


// Loop through attributes and add include/exclude fields for each attribute
if ( ! empty( $shopglut_shopg_attribute_taxonomies ) ) {
	foreach ( $shopglut_shopg_attribute_taxonomies as $shopglut_shopg_attribute ) {
		if ( isset( $shopglut_shopg_attribute->attribute_name ) ) {
			$shopglut_shopg_attribute_name = wc_attribute_taxonomy_name( $shopglut_shopg_attribute->attribute_name );
			$shopglut_shopg_attribute_label = ucfirst( str_replace( '-', ' ', $shopglut_shopg_attribute->attribute_label ) );

			$shopglut_shopg_fields[0]['tabs'][0]['fields'][0]['fields'][0]['accordions'][0]['fields'][0]['tabs'][0]['fields'][] = array(
				'id' => 'filter-' . $shopglut_shopg_attribute_name . '-appearance',
				'type' => 'image_select',
				/* translators: %s: Attribute label (e.g., 'Color', 'Size') */
				'title' => sprintf( __( '%s Appearance', 'shopglut' ), $shopglut_shopg_attribute_label ),
				'options' => array(
					'check-list' => SHOPGLUT_URL . 'modules/shop-filters/images/filterDesigns/checkbox.png',
					'radio' => SHOPGLUT_URL . 'modules/shop-filters/images/filterDesigns/radio.png',
					'dropdown' => SHOPGLUT_URL . 'modules/shop-filters/images/filterDesigns/dropdown.png',
					'grid' => SHOPGLUT_URL . 'modules/shop-filters/images/filterDesigns/grid.png',
					'button' => SHOPGLUT_URL . 'modules/shop-filters/images/filterDesigns/button.png',
					'cloud' => SHOPGLUT_URL . 'modules/shop-filters/images/filterDesigns/cloud.png',
					'color' => SHOPGLUT_URL . 'modules/shop-filters/images/filterDesigns/color.png',
					'image' => SHOPGLUT_URL . 'modules/shop-filters/images/filterDesigns/image.png'
				),
				'default' => 'check-list',
				'dependency' => array( 'filter-type', '==', $shopglut_shopg_attribute_name ),
			);
			// Add include/exclude button sets and select fields
			$shopglut_shopg_fields[0]['tabs'][0]['fields'][0]['fields'][0]['accordions'][0]['fields'][0]['tabs'][0]['fields'][] = array(

				'id' => 'filter-product-' . $shopglut_shopg_attribute_name . '-images',
				'type' => 'tax_images',
				/* translators: %s: Attribute label (e.g., 'Color', 'Size') */
				'title' => sprintf( __( 'Product %s Images', 'shopglut' ), $shopglut_shopg_attribute_label ),
				'product_option' => $shopglut_shopg_attribute_name,
				'dependency' => array(
					array( 'filter-type', '==', $shopglut_shopg_attribute_name ),
					array( 'filter-' . $shopglut_shopg_attribute_name . '-appearance', '==', 'image' ),
				),
			);

			$shopglut_shopg_fields[0]['tabs'][0]['fields'][0]['fields'][0]['accordions'][0]['fields'][0]['tabs'][0]['fields'][] = array(

				'id' => 'filter-product-' . $shopglut_shopg_attribute_name . '-color',
				'type' => 'tax_color',
				/* translators: %s: Attribute label (e.g., 'Color', 'Size') */
				'title' => sprintf( __( 'Product %s Colors', 'shopglut' ), $shopglut_shopg_attribute_label ),
				'product_option' => $shopglut_shopg_attribute_name,
				'dependency' => array(
					array( 'filter-type', '==', $shopglut_shopg_attribute_name ),
					array( 'filter-' . $shopglut_shopg_attribute_name . '-appearance', '==', 'color' ),
				),
			);

			$shopglut_shopg_fields[0]['tabs'][0]['fields'][0]['fields'][0]['accordions'][0]['fields'][0]['tabs'][0]['fields'][] = array(
				'id' => 'filter-' . $shopglut_shopg_attribute_name . '-exclude-include-button',
				'type' => 'button_set',
				'title' => __( 'Choose Options', 'shopglut' ),
				'options' => array(
					/* translators: %s: Attribute label (e.g., 'Color', 'Size') */
					'all-' . $shopglut_shopg_attribute_name => sprintf( __( 'All %s', 'shopglut' ), $shopglut_shopg_attribute_label ),
					// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- This is a configuration option, not a query parameter
					'exclude' => __( 'Exclude', 'shopglut' ),
					'include' => __( 'Include', 'shopglut' ),
				),
				'default' => 'all-' . $shopglut_shopg_attribute_name,
				'dependency' => array( 'filter-type', '==', $shopglut_shopg_attribute_name ),
			);

			$shopglut_shopg_fields[0]['tabs'][0]['fields'][0]['fields'][0]['accordions'][0]['fields'][0]['tabs'][0]['fields'][] = array(
				'id' => 'shopg-filter-include-' . $shopglut_shopg_attribute_name,
				'type' => 'select',
				/* translators: %s: Attribute label (e.g., 'Color', 'Size') */
				'title' => sprintf( esc_html__( 'Include %s', 'shopglut' ), $shopglut_shopg_attribute_label ),
				'chosen' => true,
				'multiple' => true,
				/* translators: %s: Attribute label (e.g., 'Color', 'Size') */
				'placeholder' => sprintf( esc_html__( 'Choose %s', 'shopglut' ), $shopglut_shopg_attribute_label ),
				'options' => 'categories',
				'query_args' => array(
					'taxonomy' => $shopglut_shopg_attribute_name,
				),
				'dependency' => array( 'filter-' . $shopglut_shopg_attribute_name . '-exclude-include-button|filter-type', '==|==', "include|$shopglut_shopg_attribute_name" ),
			);

			$shopglut_shopg_fields[0]['tabs'][0]['fields'][0]['fields'][0]['accordions'][0]['fields'][0]['tabs'][0]['fields'][] = array(
				'id' => 'shopg-filter-exclude-' . $shopglut_shopg_attribute_name,
				'type' => 'select',
				/* translators: %s: Attribute label (e.g., 'Color', 'Size') */
				'title' => sprintf( esc_html__( 'Exclude %s', 'shopglut' ), $shopglut_shopg_attribute_label ),
				'chosen' => true,
				'multiple' => true,
				/* translators: %s: Attribute label (e.g., 'Color', 'Size') */
				'placeholder' => sprintf( esc_html__( 'Choose %s', 'shopglut' ), $shopglut_shopg_attribute_label ),
				'options' => 'categories',
				'query_args' => array(
					'taxonomy' => $shopglut_shopg_attribute_name,
				),
				'dependency' => array( 'filter-' . $shopglut_shopg_attribute_name . '-exclude-include-button|filter-type', '==|==', "exclude|$shopglut_shopg_attribute_name" ),
			);

		}
	}
}



// Final section creation
ShopGlut_Schema::createSection(
	$shopglut_shopg_options_settings,
	array(
		'fields' => $shopglut_shopg_fields, // Use the $shopglut_shopg_fields array here
	)
);