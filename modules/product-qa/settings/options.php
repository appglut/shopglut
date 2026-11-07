<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- File-scope variables, included inside a function by the loader.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$SHOPGLUT_PRODUCT_QA_OPTIONS = 'shopglut_product_qa_options';
ShopGlut_Schema::createOptions( $SHOPGLUT_PRODUCT_QA_OPTIONS, array( 'menu_slug' => 'shopglut_product_qa_options', 'show_bar_menu' => false ) );
ShopGlut_Schema::createSection( $SHOPGLUT_PRODUCT_QA_OPTIONS, array(
	'title'  => __( 'Settings', 'shopglut' ),
	'fields' => array(
		array( 'id' => 'tab_title', 'type' => 'text', 'title' => __( 'Tab title', 'shopglut' ), 'default' => 'Questions & Answers' ),
		array( 'id' => 'guests', 'type' => 'switcher', 'title' => __( 'Guests can ask (name and email needed)', 'shopglut' ), 'default' => 1 ),
		array( 'id' => 'moderate', 'type' => 'switcher', 'title' => __( 'Hold new questions until approved', 'shopglut' ), 'default' => 1 ),
		array( 'id' => 'notify', 'type' => 'switcher', 'title' => __( 'Email the shop about new questions', 'shopglut' ), 'default' => 1 ),
		array( 'id' => 'help', 'type' => 'textarea', 'title' => __( 'Help', 'shopglut' ), 'default' => '', 'desc' => __( 'Answer a question by replying to it in Comments (type: Product question). Only questions with an answer, or approved ones, are shown.', 'shopglut' ) ),
	),
) );
