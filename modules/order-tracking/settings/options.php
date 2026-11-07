<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- File-scope variables, included inside a function by the loader.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$SHOPGLUT_ORDER_TRACKING_OPTIONS = 'shopglut_order_tracking_options';
ShopGlut_Schema::createOptions( $SHOPGLUT_ORDER_TRACKING_OPTIONS, array( 'menu_slug' => 'shopglut_order_tracking_options', 'show_bar_menu' => false ) );
ShopGlut_Schema::createSection( $SHOPGLUT_ORDER_TRACKING_OPTIONS, array(
	'title'  => __( 'Settings', 'shopglut' ),
	'fields' => array(
		array( 'id' => 'title', 'type' => 'text', 'title' => __( 'Form heading', 'shopglut' ), 'default' => 'Track your order' ),
		array( 'id' => 'show_items', 'type' => 'switcher', 'title' => __( 'Show the ordered items', 'shopglut' ), 'default' => 1 ),
		array( 'id' => 'show_notes', 'type' => 'switcher', 'title' => __( 'Show order updates (customer notes)', 'shopglut' ), 'default' => 1 ),
		array( 'id' => 'help', 'type' => 'textarea', 'title' => __( 'Help', 'shopglut' ), 'default' => '', 'desc' => __( 'Put [shopglut_order_tracking] on any page. Customers enter the order number and the billing email or phone.', 'shopglut' ) ),
	),
) );
