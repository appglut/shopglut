<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- File-scope template variables, included inside a function scope by the loader.

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

// Set a unique slug-like ID
$SIDERCART_GLUT_MINICART_OPTIONS = 'sidercart_glut_minicart_options';

// Create SiderCart options
ShopGlut_Schema::createOptions( $SIDERCART_GLUT_MINICART_OPTIONS, array(
	// menu settings
	'menu_title' => esc_html__( 'SiderCart', 'shopglut' ),
	'show_bar_menu' => false,
	'menu_slug' => 'sidercart_glut_settings',
	'menu_type' => 'menu',
	'menu_capability' => 'manage_options',
	'framework_title' => esc_html__( 'SiderCart Glut Settings', 'shopglut' ),
	'show_reset_section' => true,
	'framework_class' => 'sidercart_glut_settings',
	'footer_credit' => __( "SiderCart Glut", 'shopglut' ),
	'menu_position' => 30
) );

//
// Create a top-tab
ShopGlut_Schema::createSection( $SIDERCART_GLUT_MINICART_OPTIONS, array(
	'id' => 'primary_tab', // Set a unique slug-like ID
	'title' => __( 'Settings', 'shopglut' ),
	'icon' => 'fa fa-cog',
) );

// Create a sub-tab - General Settings
ShopGlut_Schema::createSection( $SIDERCART_GLUT_MINICART_OPTIONS, array(
	'parent' => 'primary_tab',
	'title' => __( 'General', 'shopglut' ),
	'fields' => array(

		array(
			'id' => 'enable-mini-cart',
			'type' => 'switcher',
			'title' => __( 'Enable Side Mini Cart', 'shopglut' ),
			'text_on' => __( 'Yes', 'shopglut' ),
			'text_off' => __( 'No', 'shopglut' ),
			'default' => 1,
		),

		array(
			'id' => 'show-floating-cart',
			'type' => 'switcher',
			'title' => __( 'Show Floating Cart Icon', 'shopglut' ),
			'text_on' => __( 'Yes', 'shopglut' ),
			'text_off' => __( 'No', 'shopglut' ),
			'default' => 1,
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'show-in-menu',
			'type' => 'switcher',
			'title' => __( 'Add to Navigation Menu', 'shopglut' ),
			'text_on' => __( 'Yes', 'shopglut' ),
			'text_off' => __( 'No', 'shopglut' ),
			'default' => 1,
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'auto-open-on-add',
			'type' => 'switcher',
			'title' => __( 'Auto-open on Product Add', 'shopglut' ),
			'text_on' => __( 'Yes', 'shopglut' ),
			'text_off' => __( 'No', 'shopglut' ),
			'default' => 1,
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'show-product-images',
			'type' => 'switcher',
			'title' => __( 'Show Product Images', 'shopglut' ),
			'text_on' => __( 'Yes', 'shopglut' ),
			'text_off' => __( 'No', 'shopglut' ),
			'default' => 1,
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'enable-quantity-controls',
			'type' => 'switcher',
			'title' => __( 'Quantity +/- Controls', 'shopglut' ),
			'text_on' => __( 'Yes', 'shopglut' ),
			'text_off' => __( 'No', 'shopglut' ),
			'default' => 1,
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'show-shipping-calculator',
			'type' => 'switcher',
			'title' => __( 'Shipping Calculator', 'shopglut' ),
			'text_on' => __( 'Yes', 'shopglut' ),
			'text_off' => __( 'No', 'shopglut' ),
			'default' => 0,
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'enable-cart-share',
			'type' => 'switcher',
			'title' => __( 'Enable Cart Sharing', 'shopglut' ),
			'text_on' => __( 'Yes', 'shopglut' ),
			'text_off' => __( 'No', 'shopglut' ),
			'default' => 1,
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'show-continue-shopping',
			'type' => 'switcher',
			'title' => __( 'Continue Shopping Button', 'shopglut' ),
			'text_on' => __( 'Yes', 'shopglut' ),
			'text_off' => __( 'No', 'shopglut' ),
			'default' => 1,
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'cart-position',
			'type' => 'button_set',
			'title' => __( 'Cart Position', 'shopglut' ),
			'options' => array(
				'right' => __( 'Right Side', 'shopglut' ),
				'left' => __( 'Left Side', 'shopglut' ),
			),
			'default' => 'right',
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'cart-icon-style',
			'type' => 'button_set',
			'title' => __( 'Icon Style', 'shopglut' ),
			'options' => array(
				'cart' => __( 'Shopping Cart', 'shopglut' ),
				'bag' => __( 'Shopping Bag', 'shopglut' ),
				'basket' => __( 'Basket', 'shopglut' ),
			),
			'default' => 'cart',
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'animation-style',
			'type' => 'select',
			'title' => __( 'Animation Style', 'shopglut' ),
			'options' => array(
				'slide' => __( 'Slide', 'shopglut' ),
				'fade' => __( 'Fade', 'shopglut' ),
				'scale' => __( 'Scale', 'shopglut' ),
			),
			'default' => 'slide',
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'cart-width',
			'type' => 'spinner',
			'title' => __( 'Cart Width', 'shopglut' ),
			'unit' => 'px',
			'default' => 400,
			'min' => 300,
			'max' => 600,
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'auto-close-time',
			'type' => 'spinner',
			'title' => __( 'Auto Close Time', 'shopglut' ),
			'subtitle' => __( 'Auto close after (seconds, 0=disabled)', 'shopglut' ),
			'unit' => 'seconds',
			'default' => 5,
			'min' => 0,
			'max' => 30,
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'basket-count-type',
			'type' => 'button_set',
			'title' => __( 'Basket Count Type', 'shopglut' ),
			'subtitle' => __( 'Choose how to count items in the cart badge', 'shopglut' ),
			'options' => array(
				'items' => __( 'Items (Unique Products)', 'shopglut' ),
				'quantity' => __( 'Quantity (Total Items)', 'shopglut' ),
			),
			'default' => 'items',
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'show-empty-cart-button',
			'type' => 'switcher',
			'title' => __( 'Show Empty Cart Button', 'shopglut' ),
			'subtitle' => __( 'Display a button to empty the entire cart', 'shopglut' ),
			'text_on' => __( 'Yes', 'shopglut' ),
			'text_off' => __( 'No', 'shopglut' ),
			'default' => 1,
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'enable-undo-removal',
			'type' => 'switcher',
			'title' => __( 'Enable Undo Removal', 'shopglut' ),
			'subtitle' => __( 'Show undo option after removing an item (10 seconds)', 'shopglut' ),
			'text_on' => __( 'Yes', 'shopglut' ),
			'text_off' => __( 'No', 'shopglut' ),
			'default' => 1,
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'max-quantity-message',
			'type' => 'text',
			'title' => __( 'Max Quantity Message', 'shopglut' ),
			'subtitle' => __( 'Message shown when users try to add more than available', 'shopglut' ),
			'default' => __( 'Maximum quantity reached for this product.', 'shopglut' ),
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'group-navigation',
			'type' => 'heading',
			'title' => __( 'Navigation & Triggers', 'shopglut' ),
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'skip-cart-page',
			'type' => 'switcher',
			'title' => __( 'Skip Cart Page', 'shopglut' ),
			'subtitle' => __( 'Redirect cart page directly to checkout', 'shopglut' ),
			'text_on' => __( 'Yes', 'shopglut' ),
			'text_off' => __( 'No', 'shopglut' ),
			'default' => 0,
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'trigger-mouse-over',
			'type' => 'switcher',
			'title' => __( 'Trigger on Mouse Over', 'shopglut' ),
			'subtitle' => __( 'Open cart when hovering over the icon', 'shopglut' ),
			'text_on' => __( 'Yes', 'shopglut' ),
			'text_off' => __( 'No', 'shopglut' ),
			'default' => 0,
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'mouse-over-delay',
			'type' => 'spinner',
			'title' => __( 'Mouse Over Delay', 'shopglut' ),
			'subtitle' => __( 'Delay before opening on hover (milliseconds)', 'shopglut' ),
			'unit' => 'ms',
			'default' => 300,
			'min' => 0,
			'max' => 2000,
			'step' => 50,
			'dependency' => array( 'trigger-mouse-over', '==', 'true' ),
		),

		array(
			'id' => 'custom-trigger-class',
			'type' => 'text',
			'title' => __( 'Custom Trigger Class', 'shopglut' ),
			'subtitle' => __( 'Elements with this CSS class will open the cart', 'shopglut' ),
			'default' => '',
			'placeholder' => 'e.g., open-cart-trigger',
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'auto-open-toggle',
			'type' => 'switcher',
			'title' => __( 'Auto Open on First Visit', 'shopglut' ),
			'subtitle' => __( 'Automatically open cart when page loads if cart has items', 'shopglut' ),
			'text_on' => __( 'Yes', 'shopglut' ),
			'text_off' => __( 'No', 'shopglut' ),
			'default' => 0,
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'hide-toggle-empty',
			'type' => 'switcher',
			'title' => __( 'Hide When Cart is Empty', 'shopglut' ),
			'subtitle' => __( 'Hide floating cart icon when cart has no items', 'shopglut' ),
			'text_on' => __( 'Yes', 'shopglut' ),
			'text_off' => __( 'No', 'shopglut' ),
			'default' => 0,
			'dependency' => array( 'show-floating-cart', '==', 'true' ),
		),

		array(
			'id' => 'group-coupon',
			'type' => 'heading',
			'title' => __( 'Coupon System', 'shopglut' ),
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'enable-coupons',
			'type' => 'switcher',
			'title' => __( 'Enable Coupon System', 'shopglut' ),
			'subtitle' => __( 'Allow users to apply coupon codes in the side cart', 'shopglut' ),
			'text_on' => __( 'Yes', 'shopglut' ),
			'text_off' => __( 'No', 'shopglut' ),
			'default' => 1,
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'coupon-collapsible',
			'type' => 'switcher',
			'title' => __( 'Collapsible Coupon Field', 'shopglut' ),
			'subtitle' => __( 'Show coupon field as collapsible/expandable', 'shopglut' ),
			'text_on' => __( 'Yes', 'shopglut' ),
			'text_off' => __( 'No', 'shopglut' ),
			'default' => 1,
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'coupon-default-expanded',
			'type' => 'switcher',
			'title' => __( 'Coupon Field Default Expanded', 'shopglut' ),
			'subtitle' => __( 'Show coupon field expanded by default', 'shopglut' ),
			'text_on' => __( 'Yes', 'shopglut' ),
			'text_off' => __( 'No', 'shopglut' ),
			'default' => 0,
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'coupon-success-message',
			'type' => 'text',
			'title' => __( 'Coupon Success Message', 'shopglut' ),
			'subtitle' => __( 'Message shown when coupon is applied successfully', 'shopglut' ),
			'default' => __( 'Coupon applied successfully!', 'shopglut' ),
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'coupon-error-message',
			'type' => 'text',
			'title' => __( 'Coupon Error Message', 'shopglut' ),
			'subtitle' => __( 'Default message for invalid coupons', 'shopglut' ),
			'default' => __( 'Invalid coupon code.', 'shopglut' ),
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'group-cart-totals',
			'type' => 'heading',
			'title' => __( 'Cart Totals & Order Summary', 'shopglut' ),
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'show-discount-amount',
			'type' => 'switcher',
			'title' => __( 'Show Discount Amount', 'shopglut' ),
			'subtitle' => __( 'Display applied coupon discount amount', 'shopglut' ),
			'text_on' => __( 'Yes', 'shopglut' ),
			'text_off' => __( 'No', 'shopglut' ),
			'default' => 1,
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'show-savings-amount',
			'type' => 'switcher',
			'title' => __( 'Show Total Savings', 'shopglut' ),
			'subtitle' => __( 'Display total savings from discounts', 'shopglut' ),
			'text_on' => __( 'Yes', 'shopglut' ),
			'text_off' => __( 'No', 'shopglut' ),
			'default' => 1,
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'savings-label-text',
			'type' => 'text',
			'title' => __( 'Savings Label Text', 'shopglut' ),
			'subtitle' => __( 'Custom label for savings display', 'shopglut' ),
			'default' => __( 'You Save:', 'shopglut' ),
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'show-order-summary',
			'type' => 'switcher',
			'title' => __( 'Show Order Summary', 'shopglut' ),
			'subtitle' => __( 'Display detailed order summary section', 'shopglut' ),
			'text_on' => __( 'Yes', 'shopglut' ),
			'text_off' => __( 'No', 'shopglut' ),
			'default' => 0,
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'cart-footer-action',
			'type' => 'button_set',
			'title' => __( 'Close Button Action', 'shopglut' ),
			'subtitle' => __( 'What happens when clicking continue shopping', 'shopglut' ),
			'options' => array(
				'close' => __( 'Close Cart', 'shopglut' ),
				'cart' => __( 'Go to Cart Page', 'shopglut' ),
			),
			'default' => 'close',
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'display-total-option',
			'type' => 'button_set',
			'title' => __( 'Display Total Option', 'shopglut' ),
			'subtitle' => __( 'Choose to show subtotal or total', 'shopglut' ),
			'options' => array(
				'subtotal' => __( 'Subtotal', 'shopglut' ),
				'total' => __( 'Total', 'shopglut' ),
			),
			'default' => 'subtotal',
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'group-empty-cart',
			'type' => 'heading',
			'title' => __( 'Empty Cart & Notifications', 'shopglut' ),
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'show-shop-now-button',
			'type' => 'switcher',
			'title' => __( 'Show Shop Now Button', 'shopglut' ),
			'subtitle' => __( 'Display button when cart is empty', 'shopglut' ),
			'text_on' => __( 'Yes', 'shopglut' ),
			'text_off' => __( 'No', 'shopglut' ),
			'default' => 1,
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'shop-now-button-text',
			'type' => 'text',
			'title' => __( 'Shop Now Button Text', 'shopglut' ),
			'default' => __( 'Shop Now', 'shopglut' ),
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'show-return-to-shop-button',
			'type' => 'switcher',
			'title' => __( 'Show Return to Shop Button', 'shopglut' ),
			'subtitle' => __( 'Additional button when cart is empty', 'shopglut' ),
			'text_on' => __( 'Yes', 'shopglut' ),
			'text_off' => __( 'No', 'shopglut' ),
			'default' => 0,
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'return-to-shop-button-text',
			'type' => 'text',
			'title' => __( 'Return to Shop Button Text', 'shopglut' ),
			'default' => __( 'Return to Shop', 'shopglut' ),
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'empty-cart-message',
			'type' => 'textarea',
			'title' => __( 'Empty Cart Message', 'shopglut' ),
			'subtitle' => __( 'Message shown when cart is empty', 'shopglut' ),
			'default' => __( 'Your cart is currently empty.', 'shopglut' ),
			'settings' => array(
				'rows' => 2,
			),
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'enable-toast-notifications',
			'type' => 'switcher',
			'title' => __( 'Toast Notifications', 'shopglut' ),
			'subtitle' => __( 'Show toast notifications for cart actions', 'shopglut' ),
			'text_on' => __( 'Yes', 'shopglut' ),
			'text_off' => __( 'No', 'shopglut' ),
			'default' => 1,
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'notification-position',
			'type' => 'button_set',
			'title' => __( 'Notification Position', 'shopglut' ),
			'options' => array(
				'top-right' => __( 'Top Right', 'shopglut' ),
				'top-center' => __( 'Top Center', 'shopglut' ),
				'top-left' => __( 'Top Left', 'shopglut' ),
				'bottom-right' => __( 'Bottom Right', 'shopglut' ),
				'bottom-center' => __( 'Bottom Center', 'shopglut' ),
				'bottom-left' => __( 'Bottom Left', 'shopglut' ),
			),
			'default' => 'top-right',
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'product-thumbnail-size',
			'type' => 'spinner',
			'title' => __( 'Product Thumbnail Size', 'shopglut' ),
			'subtitle' => __( 'Size in pixels', 'shopglut' ),
			'unit' => 'px',
			'default' => 60,
			'min' => 40,
			'max' => 120,
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'product-thumbnail-shape',
			'type' => 'button_set',
			'title' => __( 'Thumbnail Shape', 'shopglut' ),
			'options' => array(
				'square' => __( 'Square', 'shopglut' ),
				'rounded' => __( 'Rounded', 'shopglut' ),
				'circle' => __( 'Circle', 'shopglut' ),
			),
			'default' => 'rounded',
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

		array(
			'id' => 'product-title-max-length',
			'type' => 'spinner',
			'title' => __( 'Product Title Max Length', 'shopglut' ),
			'subtitle' => __( '0 = no limit', 'shopglut' ),
			'unit' => 'chars',
			'default' => 0,
			'min' => 0,
			'max' => 200,
			'dependency' => array( 'enable-mini-cart', '==', 'true' ),
		),

	)
) );

// Create a top-tab for Appearance
ShopGlut_Schema::createSection( $SIDERCART_GLUT_MINICART_OPTIONS, array(
	'id' => 'appearance_tab',
	'title' => __( 'Appearance', 'shopglut' ),
	'icon' => 'fa fa-palette',
) );

// Create a sub-tab - Colors
ShopGlut_Schema::createSection( $SIDERCART_GLUT_MINICART_OPTIONS, array(
	'parent' => 'appearance_tab',
	'title' => __( 'Colors', 'shopglut' ),
	'fields' => array(

		array(
			'id' => 'primary-color',
			'type' => 'color',
			'title' => __( 'Primary Color', 'shopglut' ),
			'default' => '#2271b1',
		),

		array(
			'id' => 'background-color',
			'type' => 'color',
			'title' => __( 'Background Color', 'shopglut' ),
			'default' => '#ffffff',
		),

		array(
			'id' => 'text-color',
			'type' => 'color',
			'title' => __( 'Text Color', 'shopglut' ),
			'default' => '#333333',
		),

		array(
			'id' => 'border-color',
			'type' => 'color',
			'title' => __( 'Border Color', 'shopglut' ),
			'default' => '#e0e0e0',
		),

		array(
			'id' => 'button-hover-color',
			'type' => 'color',
			'title' => __( 'Button Hover Color', 'shopglut' ),
			'default' => '#135e96',
		),

	)
) );

// Create a sub-tab - Floating Icon
ShopGlut_Schema::createSection( $SIDERCART_GLUT_MINICART_OPTIONS, array(
	'parent' => 'appearance_tab',
	'title' => __( 'Floating Icon', 'shopglut' ),
	'fields' => array(

		array(
			'id' => 'floating-icon-size',
			'type' => 'slider',
			'title' => __( 'Icon Size', 'shopglut' ),
			'default' => 24,
			'min' => 16,
			'max' => 48,
			'step' => 1,
			'unit' => 'px',
		),

		array(
			'id' => 'floating-icon-bg-color',
			'type' => 'color',
			'title' => __( 'Icon Background Color', 'shopglut' ),
			'default' => '#2271b1',
		),

		array(
			'id' => 'floating-icon-color',
			'type' => 'color',
			'title' => __( 'Icon Color', 'shopglut' ),
			'default' => '#ffffff',
		),

		array(
			'id' => 'floating-icon-badge-color',
			'type' => 'color',
			'title' => __( 'Badge Background Color', 'shopglut' ),
			'default' => '#ff4444',
		),

		array(
			'id' => 'floating-icon-badge-text-color',
			'type' => 'color',
			'title' => __( 'Badge Text Color', 'shopglut' ),
			'default' => '#ffffff',
		),

		array(
			'id' => 'floating-icon-position-bottom',
			'type' => 'slider',
			'title' => __( 'Position from Bottom', 'shopglut' ),
			'default' => 30,
			'min' => 0,
			'max' => 200,
			'step' => 5,
			'unit' => 'px',
		),

		array(
			'id' => 'floating-icon-position-side',
			'type' => 'slider',
			'title' => __( 'Position from Side', 'shopglut' ),
			'default' => 30,
			'min' => 0,
			'max' => 200,
			'step' => 5,
			'unit' => 'px',
		),

	)
) );

// Create a sub-tab - Cart Sidebar
ShopGlut_Schema::createSection( $SIDERCART_GLUT_MINICART_OPTIONS, array(
	'parent' => 'appearance_tab',
	'title' => __( 'Cart Sidebar', 'shopglut' ),
	'fields' => array(

		array(
			'id' => 'sidebar-header-bg',
			'type' => 'color',
			'title' => __( 'Header Background', 'shopglut' ),
			'default' => '#2271b1',
		),

		array(
			'id' => 'sidebar-header-text',
			'type' => 'color',
			'title' => __( 'Header Text Color', 'shopglut' ),
			'default' => '#ffffff',
		),

		array(
			'id' => 'cart-item-border',
			'type' => 'color',
			'title' => __( 'Cart Item Border', 'shopglut' ),
			'default' => '#e0e0e0',
		),

		array(
			'id' => 'cart-item-hover-bg',
			'type' => 'color',
			'title' => __( 'Cart Item Hover Background', 'shopglut' ),
			'default' => '#f9f9f9',
		),

		array(
			'id' => 'remove-button-color',
			'type' => 'color',
			'title' => __( 'Remove Button Color', 'shopglut' ),
			'default' => '#ff4444',
		),

		array(
			'id' => 'checkout-button-bg',
			'type' => 'color',
			'title' => __( 'Checkout Button Background', 'shopglut' ),
			'default' => '#2271b1',
		),

		array(
			'id' => 'checkout-button-text',
			'type' => 'color',
			'title' => __( 'Checkout Button Text', 'shopglut' ),
			'default' => '#ffffff',
		),

	)
) );

// Create a sub-tab - Custom CSS
ShopGlut_Schema::createSection( $SIDERCART_GLUT_MINICART_OPTIONS, array(
	'parent' => 'appearance_tab',
	'title' => __( 'Custom CSS', 'shopglut' ),
	'fields' => array(

		array(
			'id' => 'custom-css',
			'type' => 'textarea',
			'title' => __( 'Custom CSS', 'shopglut' ),
			'subtitle' => __( 'Add your custom CSS here', 'shopglut' ),
			'default' => '/* Add your custom CSS here */',
			'attributes' => array(
				'rows' => 10,
				'style' => 'font-family: monospace;'
			),
		),

	)
) );

// Allow pro plugin to add settings
do_action( 'sidercart_glut_pro_settings', $SIDERCART_GLUT_MINICART_OPTIONS );  // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
