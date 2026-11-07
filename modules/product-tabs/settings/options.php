<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- File-scope variables, included inside a function by the loader.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$SHOPGLUT_PRODUCT_TABS_OPTIONS = 'shopglut_product_tabs_options';
ShopGlut_Schema::createOptions( $SHOPGLUT_PRODUCT_TABS_OPTIONS, array( 'menu_slug' => 'shopglut_product_tabs_options', 'show_bar_menu' => false ) );
ShopGlut_Schema::createSection( $SHOPGLUT_PRODUCT_TABS_OPTIONS, array(
	'title'  => __( 'Settings', 'shopglut' ),
	'fields' => array(
		array( 'id' => 'tab1_title', 'type' => 'text', 'title' => __( 'Tab 1 title', 'shopglut' ), 'default' => 'Shipping & Returns' ),
		array( 'id' => 'tab1_content', 'type' => 'textarea', 'title' => __( 'Tab 1 content (HTML allowed)', 'shopglut' ), 'default' => '' ),
		array( 'id' => 'tab2_title', 'type' => 'text', 'title' => __( 'Tab 2 title', 'shopglut' ), 'default' => '' ),
		array( 'id' => 'tab2_content', 'type' => 'textarea', 'title' => __( 'Tab 2 content', 'shopglut' ), 'default' => '' ),
		array( 'id' => 'tab3_title', 'type' => 'text', 'title' => __( 'Tab 3 title', 'shopglut' ), 'default' => '' ),
		array( 'id' => 'tab3_content', 'type' => 'textarea', 'title' => __( 'Tab 3 content', 'shopglut' ), 'default' => '', 'desc' => __( 'Tabs with an empty title or content are not shown. Each product can also have its own tab (Product data → ShopGlut tab).', 'shopglut' ) ),
		array( 'id' => 'rename_description', 'type' => 'text', 'title' => __( 'Rename the Description tab (empty = keep)', 'shopglut' ), 'default' => '' ),
		array( 'id' => 'hide_additional', 'type' => 'switcher', 'title' => __( 'Hide the Additional information tab', 'shopglut' ), 'default' => 0 ),
		array( 'id' => 'hide_reviews', 'type' => 'switcher', 'title' => __( 'Hide the Reviews tab', 'shopglut' ), 'default' => 0 ),
	),
) );
