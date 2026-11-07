<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- File-scope variables, included inside a function by the loader.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$SHOPGLUT_BOUGHT_TOGETHER_OPTIONS = 'shopglut_bought_together_options';
ShopGlut_Schema::createOptions( $SHOPGLUT_BOUGHT_TOGETHER_OPTIONS, array( 'menu_slug' => 'shopglut_bought_together_options', 'show_bar_menu' => false ) );
ShopGlut_Schema::createSection( $SHOPGLUT_BOUGHT_TOGETHER_OPTIONS, array(
	'title'  => __( 'Settings', 'shopglut' ),
	'fields' => array(
		array( 'id' => 'title', 'type' => 'text', 'title' => __( 'Heading', 'shopglut' ), 'default' => 'Frequently bought together' ),
		array( 'id' => 'source', 'type' => 'select', 'title' => __( 'Products to suggest', 'shopglut' ), 'options' => array( 'orders' => __( 'Bought together in past orders (then upsells)', 'shopglut' ), 'upsells' => __( 'Upsells', 'shopglut' ), 'cross' => __( 'Cross-sells', 'shopglut' ) ), 'default' => 'orders' ),
		array( 'id' => 'count', 'type' => 'number', 'title' => __( 'How many products', 'shopglut' ), 'default' => 2 ),
		array( 'id' => 'checked', 'type' => 'switcher', 'title' => __( 'Ticked by default', 'shopglut' ), 'default' => 1 ),
		array( 'id' => 'button', 'type' => 'text', 'title' => __( 'Button text', 'shopglut' ), 'default' => 'Add selected to cart' ),
	),
) );
