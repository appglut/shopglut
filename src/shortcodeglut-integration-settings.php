<?php

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

// Set a unique slug-like ID
$SHOPGLUT_INTEGRATION_SETTINGS = 'shopglut_integration_settings';

// Create options
AGSHOPGLUT::createOptions( $SHOPGLUT_INTEGRATION_SETTINGS, array(
	// menu settings
	'menu_title' => esc_html__( 'Global Settings', 'shopglut' ),
	'show_bar_menu' => false,
	'hide_menu' => false,
	'menu_slug' => 'shopglut_integration_settings',
	'menu_parent' => 'shopglut_layouts',
	'menu_type' => 'submenu',
	'menu_capability' => 'manage_options',
	'framework_title' => esc_html__( 'Integration Settings', 'shopglut' ),
	'show_reset_section' => true,
	'framework_class' => 'shopglut_integration_settings',
	'footer_credit' => __( "ShopGlut", 'shopglut' ),
	'menu_position' => 5,
) );

/*
// Create a top-tab
AGSHOPGLUT::createSection( $SHOPGLUT_INTEGRATION_SETTINGS, array(
	'id' => 'shortcodes_tab',
	'title' => __( 'ShortcodeGlut', 'shopglut' ),
	'icon' => 'fa fa-plug',
) );

// Create a sub-tab
AGSHOPGLUT::createSection( $SHOPGLUT_INTEGRATION_SETTINGS, array(
	'parent' => 'shortcodes_tab',
	'title' => __( 'General', 'shopglut' ),
	'fields' => array(

		array(
			'id' => 'shortcodeglut-show-menu',
			'type' => 'switcher',
			'title' => __( 'Show ShortcodeGlut Menu', 'shopglut' ),
			'desc' => __( 'When enabled, ShortcodeGlut will show its own admin menu even when ShopGlut is active.', 'shopglut' ),
			'text_on' => __( 'Yes', 'shopglut' ),
			'text_off' => __( 'No', 'shopglut' ),
			'default' => 1,
		),

	)
) );

// Create WishGlut tab
AGSHOPGLUT::createSection( $SHOPGLUT_INTEGRATION_SETTINGS, array(
	'id' => 'wishglut_tab',
	'title' => __( 'WishGlut', 'shopglut' ),
	'icon' => 'fa fa-heart',
) );

// Create WishGlut sub-tab
AGSHOPGLUT::createSection( $SHOPGLUT_INTEGRATION_SETTINGS, array(
	'parent' => 'wishglut_tab',
	'title' => __( 'General', 'shopglut' ),
	'fields' => array(

		array(
			'id' => 'wishglut-show-menu',
			'type' => 'switcher',
			'title' => __( 'Show WishGlut Menu', 'shopglut' ),
			'desc' => __( 'When enabled, WishGlut will show its own admin menu even when ShopGlut is active.', 'shopglut' ),
			'text_on' => __( 'Yes', 'shopglut' ),
			'text_off' => __( 'No', 'shopglut' ),
			'default' => 1,
		),

	)
) );

// Create CheckoutGlut tab
AGSHOPGLUT::createSection( $SHOPGLUT_INTEGRATION_SETTINGS, array(
	'id' => 'checkoutglut_tab',
	'title' => __( 'CheckoutGlut', 'shopglut' ),
	'icon' => 'fa fa-check-square',
) );

// Create CheckoutGlut sub-tab
AGSHOPGLUT::createSection( $SHOPGLUT_INTEGRATION_SETTINGS, array(
	'parent' => 'checkoutglut_tab',
	'title' => __( 'General', 'shopglut' ),
	'fields' => array(

		array(
			'id' => 'checkoutglut-show-menu',
			'type' => 'switcher',
			'title' => __( 'Show CheckoutGlut Menu', 'shopglut' ),
			'desc' => __( 'When enabled, CheckoutGlut will show its own admin menu even when ShopGlut is active.', 'shopglut' ),
			'text_on' => __( 'Yes', 'shopglut' ),
			'text_off' => __( 'No', 'shopglut' ),
			'default' => 1,
		),

	)
) );

// Create Data Management tab
AGSHOPGLUT::createSection( $SHOPGLUT_INTEGRATION_SETTINGS, array(
	'id' => 'data_management_tab',
	'title' => __( 'Data Management', 'shopglut' ),
	'icon' => 'fa fa-database',
) );

// Create Data Management sub-tab
AGSHOPGLUT::createSection( $SHOPGLUT_INTEGRATION_SETTINGS, array(
	'parent' => 'data_management_tab',
	'title' => __( 'Database Tables', 'shopglut' ),
	'description' => __( '<div style="background: #fff3cd; border: 1px solid #ffc107; border-left: 4px solid #ffc107; padding: 15px; margin-bottom: 20px; border-radius: 4px;"><strong>⚠️ Warning:</strong> These actions are destructive and cannot be undone. Always backup your database before performing these operations.</div>', 'shopglut' ),
	'fields' => array(

		array(
			'id' => 'shopglut_db_tables_manager',
			'type' => 'database_manager'
		)

	)
) );

// Create Related Plugins tab
AGSHOPGLUT::createSection( $SHOPGLUT_INTEGRATION_SETTINGS, array(
	'id' => 'related_plugins_tab',
	'title' => __( 'More Plugins', 'shopglut' ),
	'icon' => 'fa fa-cubes',
	'description' => __( 'Discover more plugins by AppGlut to enhance your WooCommerce store.', 'shopglut' ),
) );

// Create Related Plugins sub-tab
AGSHOPGLUT::createSection( $SHOPGLUT_INTEGRATION_SETTINGS, array(
	'parent' => 'related_plugins_tab',
	'title' => __( 'Available Plugins', 'shopglut' ),
	'fields' => array(

		array(
			'id' => 'shopglut_related_plugins_list',
			'type' => 'related_plugins'
		),

	)
) );
*/

// Create ProductPageGlut tab
AGSHOPGLUT::createSection( $SHOPGLUT_INTEGRATION_SETTINGS, array(
	'id' => 'productpageglut_tab',
	'title' => __( 'ProductPageGlut', 'shopglut' ),
	'icon' => 'fa fa-box-open',
) );

// Create ProductPageGlut sub-tab
AGSHOPGLUT::createSection( $SHOPGLUT_INTEGRATION_SETTINGS, array(
	'parent' => 'productpageglut_tab',
	'title' => __( 'General', 'shopglut' ),
	'fields' => array(

		array(
			'id' => 'productpageglut-show-menu',
			'type' => 'switcher',
			'title' => __( 'Show ProductPageGlut Menu', 'shopglut' ),
			'desc' => __( 'When enabled, ProductPageGlut will show its own admin menu even when ShopGlut is active.', 'shopglut' ),
			'text_on' => __( 'Yes', 'shopglut' ),
			'text_off' => __( 'No', 'shopglut' ),
			'default' => 1,
		),

	)
) );

// Allow other plugins to add settings
do_action( 'shopglut_integration_settings', $SHOPGLUT_INTEGRATION_SETTINGS );
