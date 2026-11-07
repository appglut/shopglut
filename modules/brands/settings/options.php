<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- File-scope variables, included inside a function by the loader.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$SHOPGLUT_BRANDS_OPTIONS = 'shopglut_brands_options';
ShopGlut_Schema::createOptions( $SHOPGLUT_BRANDS_OPTIONS, array( 'menu_slug' => 'shopglut_brands_options', 'show_bar_menu' => false ) );
ShopGlut_Schema::createSection( $SHOPGLUT_BRANDS_OPTIONS, array(
	'title'  => __( 'Settings', 'shopglut' ),
	'fields' => array(
		array( 'id' => 'show_single', 'type' => 'switcher', 'title' => __( 'Show the brand on the product page', 'shopglut' ), 'default' => 1 ),
		array( 'id' => 'single_display', 'type' => 'select', 'title' => __( 'Show it as', 'shopglut' ), 'options' => array( 'logo' => __( 'Logo (name if no logo)', 'shopglut' ), 'name' => __( 'Name', 'shopglut' ), 'both' => __( 'Logo and name', 'shopglut' ) ), 'default' => 'logo' ),
		array( 'id' => 'show_loop', 'type' => 'switcher', 'title' => __( 'Show the brand name on shop cards', 'shopglut' ), 'default' => 0 ),
		array( 'id' => 'archive_header', 'type' => 'switcher', 'title' => __( 'Show logo and description on brand pages', 'shopglut' ), 'default' => 1 ),
		array( 'id' => 'note', 'type' => 'textarea', 'title' => __( 'Brands page', 'shopglut' ), 'default' => '', 'desc' => __( 'Put the [shopglut_brands] shortcode on any page for a grid of all brands (columns="4" hide_empty="1").', 'shopglut' ) ),
	),
) );
