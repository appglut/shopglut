<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- File-scope variables, included inside a function by the loader.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$SHOPGLUT_PRODUCT_VIDEO_OPTIONS = 'shopglut_product_video_options';
ShopGlut_Schema::createOptions( $SHOPGLUT_PRODUCT_VIDEO_OPTIONS, array( 'menu_slug' => 'shopglut_product_video_options', 'show_bar_menu' => false ) );
ShopGlut_Schema::createSection( $SHOPGLUT_PRODUCT_VIDEO_OPTIONS, array(
	'title'  => __( 'Settings', 'shopglut' ),
	'fields' => array(
		array( 'id' => 'position', 'type' => 'select', 'title' => __( 'Show the video', 'shopglut' ), 'options' => array( 'after_gallery' => __( 'Under the product images', 'shopglut' ), 'tab' => __( 'In a Video tab', 'shopglut' ), 'summary' => __( 'Under the add to cart button', 'shopglut' ) ), 'default' => 'after_gallery' ),
		array( 'id' => 'tab_title', 'type' => 'text', 'title' => __( 'Video tab title', 'shopglut' ), 'default' => 'Video' ),
		array( 'id' => 'help', 'type' => 'textarea', 'title' => __( 'Help', 'shopglut' ), 'default' => '', 'desc' => __( 'Add the video on each product: Product data → General → Product video URL (YouTube, Vimeo or an .mp4 file).', 'shopglut' ) ),
	),
) );
