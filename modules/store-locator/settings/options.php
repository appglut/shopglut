<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- File-scope variables, included inside a function by the loader.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$SHOPGLUT_STORE_LOCATOR_OPTIONS = 'shopglut_store_locator_options';
ShopGlut_Schema::createOptions( $SHOPGLUT_STORE_LOCATOR_OPTIONS, array( 'menu_slug' => 'shopglut_store_locator_options', 'show_bar_menu' => false ) );
ShopGlut_Schema::createSection( $SHOPGLUT_STORE_LOCATOR_OPTIONS, array(
	'title'  => __( 'Settings', 'shopglut' ),
	'fields' => array(
		array( 'id' => 'stores', 'type' => 'textarea', 'title' => __( 'Stores', 'shopglut' ), 'default' => '', 'desc' => __( 'One store per line: Name | Address | Phone | Opening hours | latitude,longitude. Example: Gulshan Branch | House 12, Road 45, Gulshan 2, Dhaka | 01711000000 | Sat–Thu 10am–9pm | 23.7925,90.4078', 'shopglut' ) ),
		array( 'id' => 'heading', 'type' => 'text', 'title' => __( 'Heading', 'shopglut' ), 'default' => 'Find a store' ),
		array( 'id' => 'show_map', 'type' => 'switcher', 'title' => __( 'Show a map (OpenStreetMap)', 'shopglut' ), 'default' => 1 ),
		array( 'id' => 'help', 'type' => 'textarea', 'title' => __( 'Help', 'shopglut' ), 'default' => '', 'desc' => __( 'Put [shopglut_stores] on any page.', 'shopglut' ) ),
	),
) );
