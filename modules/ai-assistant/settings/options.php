<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- File-scope variables, included inside a function by the loader.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$SHOPGLUT_AI_ASSISTANT_OPTIONS = 'shopglut_ai_assistant_options';
ShopGlut_Schema::createOptions( $SHOPGLUT_AI_ASSISTANT_OPTIONS, array( 'menu_slug' => 'shopglut_ai_assistant_options', 'show_bar_menu' => false ) );
ShopGlut_Schema::createSection( $SHOPGLUT_AI_ASSISTANT_OPTIONS, array(
	'title'  => __( 'Settings', 'shopglut' ),
	'fields' => array(
		array( 'id' => 'title', 'type' => 'text', 'title' => __( 'Chat title', 'shopglut' ), 'default' => 'Shopping assistant' ),
		array( 'id' => 'greeting', 'type' => 'text', 'title' => __( 'Greeting', 'shopglut' ), 'default' => 'Hi! Tell me what you are looking for, and your budget.' ),
		array( 'id' => 'where', 'type' => 'select', 'title' => __( 'Show the chat button', 'shopglut' ), 'options' => array( 'shop' => __( 'Shop, category and product pages', 'shopglut' ), 'all' => __( 'Every page', 'shopglut' ) ), 'default' => 'shop' ),
		array( 'id' => 'per_hour', 'type' => 'number', 'title' => __( 'Messages per visitor per hour', 'shopglut' ), 'default' => 20 ),
		array( 'id' => 'store_notes', 'type' => 'textarea', 'title' => __( 'Store facts the assistant may use', 'shopglut' ), 'default' => '', 'desc' => __( 'Delivery times, return policy, payment methods… Leave empty if unsure. Needs an AI provider in Settings → Connectors; without one, the chat shows search results.', 'shopglut' ) ),
	),
) );
