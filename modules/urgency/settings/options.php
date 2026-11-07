<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- File-scope variables, included inside a function by the loader.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$SHOPGLUT_URGENCY_OPTIONS = 'shopglut_urgency_options';
ShopGlut_Schema::createOptions( $SHOPGLUT_URGENCY_OPTIONS, array( 'menu_slug' => 'shopglut_urgency_options', 'show_bar_menu' => false ) );
ShopGlut_Schema::createSection( $SHOPGLUT_URGENCY_OPTIONS, array(
	'title'  => __( 'Settings', 'shopglut' ),
	'fields' => array(
		array( 'id' => 'countdown', 'type' => 'switcher', 'title' => __( 'Countdown on products with a scheduled sale end', 'shopglut' ), 'default' => 1 ),
		array( 'id' => 'countdown_text', 'type' => 'text', 'title' => __( 'Countdown text', 'shopglut' ), 'default' => 'Sale ends in' ),
		array( 'id' => 'low_stock', 'type' => 'switcher', 'title' => __( 'Low stock message', 'shopglut' ), 'default' => 1 ),
		array( 'id' => 'low_stock_at', 'type' => 'number', 'title' => __( 'Show it when stock is at or below', 'shopglut' ), 'default' => 5 ),
		array( 'id' => 'low_stock_text', 'type' => 'text', 'title' => __( 'Low stock text', 'shopglut' ), 'default' => 'Only {stock} left in stock, order soon!' ),
		array( 'id' => 'loop_countdown', 'type' => 'switcher', 'title' => __( 'Countdown on shop cards too', 'shopglut' ), 'default' => 0 ),
	),
) );
