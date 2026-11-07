<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- File-scope variables, included inside a function by the loader.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$SHOPGLUT_PHOTO_REVIEWS_OPTIONS = 'shopglut_photo_reviews_options';
ShopGlut_Schema::createOptions( $SHOPGLUT_PHOTO_REVIEWS_OPTIONS, array( 'menu_slug' => 'shopglut_photo_reviews_options', 'show_bar_menu' => false ) );
ShopGlut_Schema::createSection( $SHOPGLUT_PHOTO_REVIEWS_OPTIONS, array(
	'title'  => __( 'Settings', 'shopglut' ),
	'fields' => array(
		array( 'id' => 'max_photos', 'type' => 'number', 'title' => __( 'Most photos per review', 'shopglut' ), 'default' => 3 ),
		array( 'id' => 'max_mb', 'type' => 'number', 'title' => __( 'Largest photo (MB)', 'shopglut' ), 'default' => 4 ),
		array( 'id' => 'verified_only', 'type' => 'switcher', 'title' => __( 'Only verified buyers can add photos', 'shopglut' ), 'default' => 0 ),
		array( 'id' => 'gallery', 'type' => 'switcher', 'title' => __( 'Show a photo strip above the reviews', 'shopglut' ), 'default' => 1 ),
	),
) );
