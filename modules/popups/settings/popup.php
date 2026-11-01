<?php
/**
 * Layout options of a popup. Included by ShopGlut_Module_Popups::layout_sections(), with
 * $template (modal | bar | slide-in | subscribe) in scope.
 *
 * Values are saved as settings[content|trigger|frequency|behavior|design][field].
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

$shopglut_popup_is_bar       = 'bar' === $template;
$shopglut_popup_is_subscribe = 'subscribe' === $template;

// Content.
$shopglut_popup_content = array(
	array(
		'id'      => 'heading',
		'type'    => 'text',
		'title'   => __( 'Heading', 'shopglut' ),
		'default' => $shopglut_popup_is_subscribe ? __( 'Get 10% off your first order', 'shopglut' ) : __( 'Free shipping on orders over $50', 'shopglut' ),
	),
	array(
		'id'      => 'body',
		'type'    => $shopglut_popup_is_bar ? 'textarea' : 'wp_editor',
		'title'   => $shopglut_popup_is_bar ? __( 'Message', 'shopglut' ) : __( 'Content', 'shopglut' ),
		'desc'    => __( 'Shortcodes work here. Placeholders: {site_name}, {first_name}, {cart_total}.', 'shopglut' ),
		'default' => $shopglut_popup_is_subscribe ? __( 'Join our newsletter for offers and new arrivals.', 'shopglut' ) : '',
	),
);

if ( ! $shopglut_popup_is_bar ) {
	$shopglut_popup_content[] = array(
		'id'      => 'image',
		'type'    => 'media',
		'title'   => __( 'Image', 'shopglut' ),
		'library' => 'image',
	);
	$shopglut_popup_content[] = array(
		'id'      => 'image_position',
		'type'    => 'button_set',
		'title'   => __( 'Image position', 'shopglut' ),
		'options' => array(
			'top'        => __( 'Top', 'shopglut' ),
			'left'       => __( 'Left', 'shopglut' ),
			'right'      => __( 'Right', 'shopglut' ),
			'background' => __( 'Background', 'shopglut' ),
		),
		'default' => 'top',
	);
}

$shopglut_popup_content[] = array(
	'id'      => 'coupon_code',
	'type'    => 'text',
	'title'   => __( 'Coupon code to show', 'shopglut' ),
	'desc'    => __( 'Optional. Shown with a copy button. Create the coupon in WooCommerce → Coupons first.', 'shopglut' ),
	'default' => '',
);

if ( $shopglut_popup_is_subscribe ) {
	$shopglut_popup_content[] = array(
		'type'    => 'subheading',
		'content' => __( 'Subscribe form', 'shopglut' ),
	);
	$shopglut_popup_content[] = array(
		'id'      => 'show_name',
		'type'    => 'switcher',
		'title'   => __( 'Ask for the first name', 'shopglut' ),
		'default' => false,
	);
	$shopglut_popup_content[] = array(
		'id'      => 'email_placeholder',
		'type'    => 'text',
		'title'   => __( 'Email placeholder', 'shopglut' ),
		'default' => __( 'Your email address', 'shopglut' ),
	);
	$shopglut_popup_content[] = array(
		'id'      => 'submit_text',
		'type'    => 'text',
		'title'   => __( 'Button text', 'shopglut' ),
		'default' => __( 'Subscribe', 'shopglut' ),
	);
	$shopglut_popup_content[] = array(
		'id'      => 'consent',
		'type'    => 'button_set',
		'title'   => __( 'Consent checkbox', 'shopglut' ),
		'options' => array(
			'none'     => __( 'None', 'shopglut' ),
			'optional' => __( 'Optional', 'shopglut' ),
			'required' => __( 'Required', 'shopglut' ),
		),
		'default' => 'required',
	);
	$shopglut_popup_content[] = array(
		'id'         => 'consent_text',
		'type'       => 'textarea',
		'title'      => __( 'Consent text', 'shopglut' ),
		'desc'       => __( 'Leave empty to use the text from the module Settings.', 'shopglut' ),
		'dependency' => array( 'consent', '!=', 'none' ),
	);
	$shopglut_popup_content[] = array(
		'id'      => 'success_message',
		'type'    => 'textarea',
		'title'   => __( 'Message after subscribing', 'shopglut' ),
		'default' => __( 'Thanks! Check your inbox.', 'shopglut' ),
	);
	$shopglut_popup_content[] = array(
		'id'      => 'after_subscribe',
		'type'    => 'button_set',
		'title'   => __( 'After subscribing', 'shopglut' ),
		'options' => array(
			'message'  => __( 'Show the message', 'shopglut' ),
			'close'    => __( 'Close after 3 s', 'shopglut' ),
			'redirect' => __( 'Go to a URL', 'shopglut' ),
		),
		'default' => 'message',
	);
	$shopglut_popup_content[] = array(
		'id'         => 'redirect_url',
		'type'       => 'text',
		'title'      => __( 'Redirect URL', 'shopglut' ),
		'dependency' => array( 'after_subscribe', '==', 'redirect' ),
	);
} else {
	$shopglut_popup_content[] = array(
		'type'    => 'subheading',
		'content' => __( 'Button', 'shopglut' ),
	);
	$shopglut_popup_content[] = array(
		'id'      => 'button_text',
		'type'    => 'text',
		'title'   => __( 'Button text', 'shopglut' ),
		'desc'    => __( 'Leave empty for no button.', 'shopglut' ),
		'default' => __( 'Shop now', 'shopglut' ),
	);
	$shopglut_popup_content[] = array(
		'id'      => 'button_action',
		'type'    => 'button_set',
		'title'   => __( 'Button action', 'shopglut' ),
		'options' => array(
			'link'  => __( 'Open a link', 'shopglut' ),
			'close' => __( 'Close the popup', 'shopglut' ),
		),
		'default' => 'link',
	);
	$shopglut_popup_content[] = array(
		'id'         => 'button_url',
		'type'       => 'text',
		'title'      => __( 'Button link', 'shopglut' ),
		'dependency' => array( 'button_action', '==', 'link' ),
	);
	$shopglut_popup_content[] = array(
		'id'         => 'button_new_tab',
		'type'       => 'switcher',
		'title'      => __( 'Open in a new tab', 'shopglut' ),
		'default'    => false,
		'dependency' => array( 'button_action', '==', 'link' ),
	);
}

if ( ! $shopglut_popup_is_bar ) {
	$shopglut_popup_content[] = array(
		'id'      => 'decline_text',
		'type'    => 'text',
		'title'   => __( '"No thanks" link text', 'shopglut' ),
		'desc'    => __( 'A small link under the button that closes the popup. Leave empty to hide it.', 'shopglut' ),
		'default' => __( 'No thanks', 'shopglut' ),
	);
}

// Trigger.
$shopglut_popup_trigger = array(
	array(
		'id'      => 'open_on',
		'type'    => 'button_set',
		'title'   => __( 'Open', 'shopglut' ),
		'options' => array(
			'load'   => __( 'On page load', 'shopglut' ),
			'scroll' => __( 'After scrolling', 'shopglut' ),
			'click'  => __( 'On click', 'shopglut' ),
		),
		'default' => 'load',
	),
	array(
		'id'         => 'delay',
		'type'       => 'spinner',
		'title'      => __( 'Delay', 'shopglut' ),
		'unit'       => __( 'seconds', 'shopglut' ),
		'min'        => 0,
		'max'        => 300,
		'default'    => $shopglut_popup_is_bar ? 0 : 5,
		'dependency' => array( 'open_on', '==', 'load' ),
	),
	array(
		'id'         => 'scroll_percent',
		'type'       => 'slider',
		'title'      => __( 'Scrolled down the page', 'shopglut' ),
		'unit'       => '%',
		'min'        => 5,
		'max'        => 100,
		'default'    => 50,
		'dependency' => array( 'open_on', '==', 'scroll' ),
	),
	array(
		'id'         => 'click_selector',
		'type'       => 'text',
		'title'      => __( 'Elements that open it (CSS selector)', 'shopglut' ),
		'desc'       => __( 'For example .open-offer. Links to #shopglut-popup-{id} always open this popup.', 'shopglut' ),
		'dependency' => array( 'open_on', '==', 'click' ),
	),
);

// Frequency.
$shopglut_popup_frequency = array(
	array(
		'id'      => 'show_again',
		'type'    => 'select',
		'title'   => __( 'Show again', 'shopglut' ),
		'options' => array(
			'always'  => __( 'On every page view', 'shopglut' ),
			'session' => __( 'Once per browser session', 'shopglut' ),
			'days'    => __( 'After a number of days', 'shopglut' ),
			'never'   => __( 'Never, once it has been closed', 'shopglut' ),
		),
		'default' => 'days',
	),
	array(
		'id'         => 'days',
		'type'       => 'spinner',
		'title'      => __( 'Days', 'shopglut' ),
		'min'        => 1,
		'max'        => 365,
		'default'    => 7,
		'dependency' => array( 'show_again', '==', 'days' ),
	),
	array(
		'id'      => 'stop_after_action',
		'type'    => 'switcher',
		'title'   => $shopglut_popup_is_subscribe ? __( 'Never show again after subscribing', 'shopglut' ) : __( 'Never show again after the button is clicked', 'shopglut' ),
		'default' => true,
	),
);

// Behavior.
$shopglut_popup_behavior = array(
	array(
		'id'      => 'close_button',
		'type'    => 'switcher',
		'title'   => __( 'Close (×) button', 'shopglut' ),
		'default' => true,
	),
	array(
		'id'         => 'close_delay',
		'type'       => 'spinner',
		'title'      => __( 'Show the close button after', 'shopglut' ),
		'unit'       => __( 'seconds', 'shopglut' ),
		'min'        => 0,
		'max'        => 30,
		'default'    => 0,
		'dependency' => array( 'close_button', '==', 'true' ),
	),
	array(
		'id'      => 'auto_close',
		'type'    => 'spinner',
		'title'   => __( 'Close by itself after', 'shopglut' ),
		'desc'    => __( '0 keeps it open.', 'shopglut' ),
		'unit'    => __( 'seconds', 'shopglut' ),
		'min'     => 0,
		'max'     => 600,
		'default' => 0,
	),
	array(
		'id'      => 'animation',
		'type'    => 'select',
		'title'   => __( 'Animation', 'shopglut' ),
		'options' => array(
			'fade'       => __( 'Fade', 'shopglut' ),
			'slide-up'   => __( 'Slide up', 'shopglut' ),
			'slide-down' => __( 'Slide down', 'shopglut' ),
			'zoom'       => __( 'Zoom', 'shopglut' ),
			'none'       => __( 'None', 'shopglut' ),
		),
		'default' => $shopglut_popup_is_bar ? 'slide-down' : 'fade',
	),
);

if ( ! $shopglut_popup_is_bar ) {
	$shopglut_popup_behavior[] = array(
		'id'      => 'close_on_overlay',
		'type'    => 'switcher',
		'title'   => __( 'Close when the page behind is clicked', 'shopglut' ),
		'default' => true,
	);
	$shopglut_popup_behavior[] = array(
		'id'      => 'close_on_esc',
		'type'    => 'switcher',
		'title'   => __( 'Close with the Esc key', 'shopglut' ),
		'default' => true,
	);
}
if ( in_array( $template, array( 'modal', 'subscribe' ), true ) ) {
	$shopglut_popup_behavior[] = array(
		'id'      => 'lock_scroll',
		'type'    => 'switcher',
		'title'   => __( 'Stop the page from scrolling while open', 'shopglut' ),
		'default' => true,
	);
}
if ( $shopglut_popup_is_bar ) {
	$shopglut_popup_behavior[] = array(
		'id'      => 'sticky',
		'type'    => 'switcher',
		'title'   => __( 'Stay visible while scrolling', 'shopglut' ),
		'default' => true,
	);
	$shopglut_popup_behavior[] = array(
		'id'      => 'push_content',
		'type'    => 'switcher',
		'title'   => __( 'Push the page down instead of covering it', 'shopglut' ),
		'default' => true,
	);
}

// Design.
$shopglut_popup_positions = $shopglut_popup_is_bar
	? array(
		'top'    => __( 'Top', 'shopglut' ),
		'bottom' => __( 'Bottom', 'shopglut' ),
	)
	: ( 'slide-in' === $template
		? array(
			'bottom-right' => __( 'Bottom right', 'shopglut' ),
			'bottom-left'  => __( 'Bottom left', 'shopglut' ),
			'top-right'    => __( 'Top right', 'shopglut' ),
			'top-left'     => __( 'Top left', 'shopglut' ),
		)
		: array(
			'center' => __( 'Center', 'shopglut' ),
			'top'    => __( 'Top', 'shopglut' ),
			'bottom' => __( 'Bottom', 'shopglut' ),
		) );

$shopglut_popup_design = array(
	array(
		'id'      => 'position',
		'type'    => 'button_set',
		'title'   => __( 'Position', 'shopglut' ),
		'options' => $shopglut_popup_positions,
		'default' => key( $shopglut_popup_positions ),
	),
);
if ( ! $shopglut_popup_is_bar ) {
	$shopglut_popup_design[] = array(
		'id'      => 'width',
		'type'    => 'slider',
		'title'   => __( 'Width', 'shopglut' ),
		'unit'    => 'px',
		'min'     => 260,
		'max'     => 1200,
		'default' => 'slide-in' === $template ? 360 : 520,
	);
}
$shopglut_popup_design = array_merge(
	$shopglut_popup_design,
	array(
		array(
			'id'      => 'background',
			'type'    => 'color',
			'title'   => __( 'Background', 'shopglut' ),
			'default' => $shopglut_popup_is_bar ? '#111827' : '#ffffff',
		),
		array(
			'id'      => 'text_color',
			'type'    => 'color',
			'title'   => __( 'Text color', 'shopglut' ),
			'default' => $shopglut_popup_is_bar ? '#ffffff' : '#1f2937',
		),
		array(
			'id'      => 'heading_size',
			'type'    => 'spinner',
			'title'   => __( 'Heading size', 'shopglut' ),
			'unit'    => 'px',
			'min'     => 12,
			'max'     => 60,
			'default' => $shopglut_popup_is_bar ? 15 : 26,
		),
		array(
			'id'      => 'button_background',
			'type'    => 'color',
			'title'   => __( 'Button background', 'shopglut' ),
			'desc'    => __( 'Leave empty to use the store accent color.', 'shopglut' ),
			'default' => '',
		),
		array(
			'id'      => 'button_color',
			'type'    => 'color',
			'title'   => __( 'Button text color', 'shopglut' ),
			'default' => '#ffffff',
		),
		array(
			'id'      => 'radius',
			'type'    => 'spinner',
			'title'   => __( 'Corner radius', 'shopglut' ),
			'unit'    => 'px',
			'min'     => 0,
			'max'     => 40,
			'default' => $shopglut_popup_is_bar ? 0 : 12,
		),
		array(
			'id'      => 'padding',
			'type'    => 'spinner',
			'title'   => __( 'Inner spacing', 'shopglut' ),
			'unit'    => 'px',
			'min'     => 0,
			'max'     => 80,
			'default' => $shopglut_popup_is_bar ? 10 : 32,
		),
	)
);
if ( ! $shopglut_popup_is_bar && 'slide-in' !== $template ) {
	$shopglut_popup_design[] = array(
		'id'      => 'overlay_color',
		'type'    => 'color',
		'title'   => __( 'Page dimming color', 'shopglut' ),
		'default' => 'rgba(17,24,39,0.6)',
	);
}
$shopglut_popup_design[] = array(
	'id'    => 'css_class',
	'type'  => 'text',
	'title' => __( 'Extra CSS class', 'shopglut' ),
);

$shopglut_popup_section = static function ( $id, $title, $icon, $fields ) {
	return array(
		'id'     => $id,
		'title'  => $title,
		'icon'   => $icon,
		'fields' => array(
			array(
				'id'     => $id,
				'type'   => 'fieldset',
				'fields' => $fields,
			),
		),
	);
};

return array(
	$shopglut_popup_section( 'content', __( 'Content', 'shopglut' ), 'dashicons-edit', $shopglut_popup_content ),
	$shopglut_popup_section( 'trigger', __( 'Trigger', 'shopglut' ), 'dashicons-controls-play', $shopglut_popup_trigger ),
	$shopglut_popup_section( 'frequency', __( 'Frequency', 'shopglut' ), 'dashicons-backup', $shopglut_popup_frequency ),
	$shopglut_popup_section( 'behavior', __( 'Behavior', 'shopglut' ), 'dashicons-admin-generic', $shopglut_popup_behavior ),
	$shopglut_popup_section( 'design', __( 'Design', 'shopglut' ), 'dashicons-admin-appearance', $shopglut_popup_design ),
);
