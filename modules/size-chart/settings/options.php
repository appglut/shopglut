<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- File-scope variables, included inside a function by the loader.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$SHOPGLUT_SIZE_CHART_OPTIONS = 'shopglut_size_chart_options';
ShopGlut_Schema::createOptions( $SHOPGLUT_SIZE_CHART_OPTIONS, array( 'menu_slug' => 'shopglut_size_chart_options', 'show_bar_menu' => false ) );
ShopGlut_Schema::createSection( $SHOPGLUT_SIZE_CHART_OPTIONS, array(
	'title'  => __( 'Settings', 'shopglut' ),
	'fields' => array(
		array( 'id' => 'link_text', 'type' => 'text', 'title' => __( 'Link text', 'shopglut' ), 'default' => 'Size chart' ),
		array( 'id' => 'title', 'type' => 'text', 'title' => __( 'Chart title', 'shopglut' ), 'default' => 'Size chart' ),
		array( 'id' => 'chart', 'type' => 'textarea', 'title' => __( 'Chart', 'shopglut' ), 'default' => 'Size | Chest (in) | Waist (in)\nS | 36 | 30\nM | 38 | 32\nL | 40 | 34\nXL | 42 | 36', 'desc' => __( 'One row per line, cells split with |. The first row is the header.', 'shopglut' ) ),
		array( 'id' => 'categories', 'type' => 'text', 'title' => __( 'Show for product categories (slugs, comma-separated; empty = all)', 'shopglut' ), 'default' => '' ),
		array( 'id' => 'note', 'type' => 'textarea', 'title' => __( 'Note under the chart', 'shopglut' ), 'default' => '' ),
	),
) );
