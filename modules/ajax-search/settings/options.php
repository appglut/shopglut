<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- File-scope variables, included inside a function by the loader.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$SHOPGLUT_AJAX_SEARCH_OPTIONS = 'shopglut_ajax_search_options';
ShopGlut_Schema::createOptions( $SHOPGLUT_AJAX_SEARCH_OPTIONS, array( 'menu_slug' => 'shopglut_ajax_search_options', 'show_bar_menu' => false ) );
ShopGlut_Schema::createSection( $SHOPGLUT_AJAX_SEARCH_OPTIONS, array(
	'title'  => __( 'Settings', 'shopglut' ),
	'fields' => array(
		array( 'id' => 'placeholder', 'type' => 'text', 'title' => __( 'Placeholder', 'shopglut' ), 'default' => 'Search products…' ),
		array( 'id' => 'limit', 'type' => 'number', 'title' => __( 'Results shown', 'shopglut' ), 'default' => 8 ),
		array( 'id' => 'min_chars', 'type' => 'number', 'title' => __( 'Start after this many letters', 'shopglut' ), 'default' => 2 ),
		array( 'id' => 'sku', 'type' => 'switcher', 'title' => __( 'Also search SKUs', 'shopglut' ), 'default' => 1 ),
		array( 'id' => 'categories', 'type' => 'switcher', 'title' => __( 'Show matching categories', 'shopglut' ), 'default' => 1 ),
		array( 'id' => 'replace_form', 'type' => 'switcher', 'title' => __( 'Use it for the theme product search form', 'shopglut' ), 'default' => 1 ),
		array( 'id' => 'help', 'type' => 'textarea', 'title' => __( 'Help', 'shopglut' ), 'default' => '', 'desc' => __( 'Put [shopglut_search] anywhere, or use the Product Search widget / block.', 'shopglut' ) ),
	),
) );
