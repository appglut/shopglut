<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- File-scope variables, included inside a function by the loader.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$SHOPGLUT_COMING_SOON_OPTIONS = 'shopglut_coming_soon_options';
ShopGlut_Schema::createOptions( $SHOPGLUT_COMING_SOON_OPTIONS, array( 'menu_slug' => 'shopglut_coming_soon_options', 'show_bar_menu' => false ) );
ShopGlut_Schema::createSection( $SHOPGLUT_COMING_SOON_OPTIONS, array(
	'title'  => __( 'Settings', 'shopglut' ),
	'fields' => array(
		array( 'id' => 'mode', 'type' => 'select', 'title' => __( 'Store mode', 'shopglut' ), 'options' => array( 'off' => __( 'Off (store open)', 'shopglut' ), 'coming_soon' => __( 'Coming soon', 'shopglut' ), 'maintenance' => __( 'Maintenance (503, search engines come back later)', 'shopglut' ) ), 'default' => 'off' ),
		array( 'id' => 'heading', 'type' => 'text', 'title' => __( 'Heading', 'shopglut' ), 'default' => 'We are opening soon' ),
		array( 'id' => 'message', 'type' => 'textarea', 'title' => __( 'Message', 'shopglut' ), 'default' => 'Our new store is almost ready. Please come back soon.' ),
		array( 'id' => 'date', 'type' => 'text', 'title' => __( 'Opening date / time shown (optional, e.g. 2026-12-01 10:00)', 'shopglut' ), 'default' => '' ),
		array( 'id' => 'background', 'type' => 'text', 'title' => __( 'Background colour', 'shopglut' ), 'default' => '#111827' ),
		array( 'id' => 'text_color', 'type' => 'text', 'title' => __( 'Text colour', 'shopglut' ), 'default' => '#ffffff' ),
		array( 'id' => 'show_login', 'type' => 'switcher', 'title' => __( 'Show a login link', 'shopglut' ), 'default' => 1 ),
	),
) );
