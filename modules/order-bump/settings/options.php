<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- File-scope variables, included inside a function by the loader.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$SHOPGLUT_ORDER_BUMP_OPTIONS = 'shopglut_order_bump_options';
ShopGlut_Schema::createOptions( $SHOPGLUT_ORDER_BUMP_OPTIONS, array( 'menu_slug' => 'shopglut_order_bump_options', 'show_bar_menu' => false ) );
ShopGlut_Schema::createSection( $SHOPGLUT_ORDER_BUMP_OPTIONS, array(
	'title'  => __( 'Settings', 'shopglut' ),
	'fields' => array(
		array( 'id' => 'product_id', 'type' => 'number', 'title' => __( 'Offer product ID', 'shopglut' ), 'default' => 0, 'desc' => __( 'A simple product to offer at checkout.', 'shopglut' ) ),
		array( 'id' => 'discount', 'type' => 'number', 'title' => __( 'Discount on the offer (%)', 'shopglut' ), 'default' => 0 ),
		array( 'id' => 'headline', 'type' => 'text', 'title' => __( 'Headline', 'shopglut' ), 'default' => 'Yes! Add this to my order' ),
		array( 'id' => 'text', 'type' => 'textarea', 'title' => __( 'Text', 'shopglut' ), 'default' => 'Only at checkout: get {product} for {price}.' ),
		array( 'id' => 'min_total', 'type' => 'number', 'title' => __( 'Show only when the cart is at least', 'shopglut' ), 'default' => 0 ),
	),
) );
