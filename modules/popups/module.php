<?php
/**
 * Popups & Banners: modal popups, announcement bars, slide-ins and subscribe popups.
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Module_Popups class.
 */
class ShopGlut_Module_Popups extends ShopGlut_Module {

	/**
	 * Module ID.
	 *
	 * @return string
	 */
	public function id() {
		return 'popups';
	}

	/**
	 * Module info.
	 *
	 * @return array
	 */
	public function info() {
		return array(
			'title'         => __( 'Popups & Banners', 'shopglut' ),
			'description'   => __( 'Modal popups, announcement bars, slide-ins and subscribe popups with triggers, frequency and display rules.', 'shopglut' ),
			'icon'          => 'faBullhorn',
			'category'      => 'engagement',
			'order'         => 110,
			'layouts'       => true,
			'layout_label'  => __( 'Popup', 'shopglut' ),
			'layouts_label' => __( 'Popups', 'shopglut' ),
			'features'      => array(
				__( 'Unlimited modal popups, announcement bars, slide-ins and subscribe popups', 'shopglut' ),
				__( 'Open on page load, after a delay, on scroll or on click', 'shopglut' ),
				__( 'Show again: every visit, once per session or every X days', 'shopglut' ),
				__( 'Subscribe form with consent, subscribers list and CSV export', 'shopglut' ),
				__( 'Display rules by page, product, category, device and login state', 'shopglut' ),
			),
			'pro_features'  => array(
				__( 'Exit intent, inactivity, page-view count and add-to-cart triggers', 'shopglut' ),
				__( 'Full-screen, spin-to-win, sales notification, age gate and content lock types', 'shopglut' ),
				__( 'Unique coupons, countdown timers and email service integrations', 'shopglut' ),
				__( 'Targeting by cart, role, referrer, country, purchase history and schedule', 'shopglut' ),
				__( 'A/B testing and conversion analytics', 'shopglut' ),
			),
		);
	}

	/**
	 * Popup types. The ones marked Pro are teasers: their code ships with ShopGlut Pro.
	 *
	 * @return array
	 */
	public function templates() {
		return array(
			'modal'              => array(
				'title'       => __( 'Modal popup', 'shopglut' ),
				'description' => __( 'A centered box over a dimmed page, for offers and information.', 'shopglut' ),
			),
			'bar'                => array(
				'title'       => __( 'Announcement bar', 'shopglut' ),
				'description' => __( 'A thin banner at the top or bottom of the page.', 'shopglut' ),
			),
			'slide-in'           => array(
				'title'       => __( 'Slide-in box', 'shopglut' ),
				'description' => __( 'A small box that slides in from a corner without blocking the page.', 'shopglut' ),
			),
			'subscribe'          => array(
				'title'       => __( 'Subscribe popup', 'shopglut' ),
				'description' => __( 'A newsletter sign-up form with an email field and consent.', 'shopglut' ),
			),
			'fullscreen'         => array(
				'title'       => __( 'Full-screen overlay', 'shopglut' ),
				'description' => __( 'Covers the whole screen, for big launches and welcome mats.', 'shopglut' ),
				'pro'         => true,
			),
			'spin-wheel'         => array(
				'title'       => __( 'Spin to win', 'shopglut' ),
				'description' => __( 'A prize wheel that gives a coupon in exchange for an email.', 'shopglut' ),
				'pro'         => true,
			),
			'sales-notification' => array(
				'title'       => __( 'Sales notification', 'shopglut' ),
				'description' => __( 'Small "Someone in Paris bought…" toasts from real orders.', 'shopglut' ),
				'pro'         => true,
			),
			'age-gate'           => array(
				'title'       => __( 'Age verification', 'shopglut' ),
				'description' => __( 'A yes / no gate that blocks the site until the visitor confirms.', 'shopglut' ),
				'pro'         => true,
			),
			'content-lock'       => array(
				'title'       => __( 'Content lock', 'shopglut' ),
				'description' => __( 'Hides part of a page until the visitor subscribes.', 'shopglut' ),
				'pro'         => true,
			),
		);
	}

	/**
	 * Layout options of a popup.
	 *
	 * @param string $template Template ID.
	 * @param array  $layout   Layout row.
	 * @return array
	 */
	public function layout_sections( $template, $layout ) {
		$file = $this->dir . 'settings/popup.php';
		return is_readable( $file ) ? (array) include $file : array();
	}

	/**
	 * Where the popup shows.
	 *
	 * @return array
	 */
	public function display_fields() {
		return array(
			array(
				'id'      => 'pages',
				'type'    => 'checkbox',
				'title'   => __( 'Show on', 'shopglut' ),
				'options' => array(
					'all'      => __( 'Entire site', 'shopglut' ),
					'home'     => __( 'Home page', 'shopglut' ),
					'shop'     => __( 'Shop and product archives', 'shopglut' ),
					'product'  => __( 'Product pages', 'shopglut' ),
					'cart'     => __( 'Cart page', 'shopglut' ),
					'checkout' => __( 'Checkout page', 'shopglut' ),
					'account'  => __( 'My Account', 'shopglut' ),
					'pages'    => __( 'Chosen pages', 'shopglut' ),
				),
				'default' => array( 'all' ),
			),
			array(
				'id'         => 'page_ids',
				'type'       => 'select',
				'title'      => __( 'Pages', 'shopglut' ),
				'options'    => 'pages',
				'multiple'   => true,
				'chosen'     => true,
				'dependency' => array( 'pages', 'any', 'pages' ),
			),
			array(
				'id'         => 'categories',
				'type'       => 'select',
				'title'      => __( 'Only products in these categories', 'shopglut' ),
				'desc'       => __( 'Leave empty for every product.', 'shopglut' ),
				'options'    => 'terms',
				'query_args' => array( 'taxonomy' => 'product_cat' ),
				'multiple'   => true,
				'dependency' => array( 'pages', 'any', 'product,shop' ),
			),
			array(
				'id'         => 'products',
				'type'       => 'select',
				'title'      => __( 'Only these products', 'shopglut' ),
				'options'    => 'post_type',
				'query_args' => array( 'post_type' => 'product' ),
				'multiple'   => true,
				'ajax'       => true,
				'dependency' => array( 'pages', 'any', 'product' ),
			),
			array(
				'id'       => 'exclude_pages',
				'type'     => 'select',
				'title'    => __( 'Never on these pages', 'shopglut' ),
				'options'  => 'pages',
				'multiple' => true,
				'chosen'   => true,
			),
			array(
				'id'      => 'users',
				'type'    => 'button_set',
				'title'   => __( 'Visitors', 'shopglut' ),
				'options' => array(
					'all'    => __( 'Everyone', 'shopglut' ),
					'guests' => __( 'Guests only', 'shopglut' ),
					'users'  => __( 'Logged-in only', 'shopglut' ),
				),
				'default' => 'all',
			),
			array(
				'id'      => 'devices',
				'type'    => 'checkbox',
				'title'   => __( 'Devices', 'shopglut' ),
				'options' => array(
					'desktop' => __( 'Desktop', 'shopglut' ),
					'tablet'  => __( 'Tablet', 'shopglut' ),
					'mobile'  => __( 'Mobile', 'shopglut' ),
				),
				'default' => array( 'desktop', 'tablet', 'mobile' ),
				'inline'  => true,
			),
		);
	}

	/**
	 * Whether a popup applies to the current request.
	 *
	 * Device and frequency checks run in the browser (they depend on the screen and cookies).
	 *
	 * @param array $layout  Layout.
	 * @param array $context page (home | shop | product | cart | checkout | account | page), page_id, product_id, logged_in.
	 * @return bool
	 */
	public function layout_matches( $layout, $context ) {
		$rules   = isset( $layout['settings']['display'] ) ? (array) $layout['settings']['display'] : array();
		$pages   = isset( $rules['pages'] ) ? (array) $rules['pages'] : array( 'all' );
		$page    = isset( $context['page'] ) ? $context['page'] : '';
		$page_id = isset( $context['page_id'] ) ? absint( $context['page_id'] ) : 0;

		if ( $page_id && in_array( $page_id, array_map( 'absint', (array) ( $rules['exclude_pages'] ?? array() ) ), true ) ) {
			return false;
		}

		$users     = isset( $rules['users'] ) ? $rules['users'] : 'all';
		$logged_in = ! empty( $context['logged_in'] );
		if ( ( 'guests' === $users && $logged_in ) || ( 'users' === $users && ! $logged_in ) ) {
			return false;
		}

		if ( in_array( 'all', $pages, true ) ) {
			return true;
		}
		if ( 'page' === $page ) {
			return in_array( 'pages', $pages, true ) && in_array( $page_id, array_map( 'absint', (array) ( $rules['page_ids'] ?? array() ) ), true );
		}
		if ( ! in_array( $page, $pages, true ) ) {
			return false;
		}
		if ( 'product' === $page ) {
			$product_id = isset( $context['product_id'] ) ? absint( $context['product_id'] ) : 0;
			$only       = array_map( 'absint', (array) ( $rules['products'] ?? array() ) );
			if ( $only && ! in_array( $product_id, $only, true ) ) {
				return false;
			}
			$cats = array_map( 'absint', (array) ( $rules['categories'] ?? array() ) );
			if ( $cats && ! has_term( $cats, 'product_cat', $product_id ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Settings panel: options shared by every popup.
	 *
	 * @return array
	 */
	public function settings_sections() {
		return array(
			array(
				'id'     => 'general',
				'title'  => __( 'General', 'shopglut' ),
				'icon'   => 'dashicons-admin-settings',
				'fields' => array(
					array(
						'id'      => 'max_per_page',
						'type'    => 'spinner',
						'title'   => __( 'Popups per page view', 'shopglut' ),
						'desc'    => __( 'The most popups that may open on one page view. Bars do not count.', 'shopglut' ),
						'min'     => 1,
						'max'     => 5,
						'default' => 1,
					),
					array(
						'id'      => 'hide_for_admins',
						'type'    => 'switcher',
						'title'   => __( 'Hide popups from administrators', 'shopglut' ),
						'desc'    => __( 'Useful while you build. Use the editor Preview to see a popup.', 'shopglut' ),
						'default' => false,
					),
					array(
						'id'      => 'z_index',
						'type'    => 'number',
						'title'   => __( 'Stacking order (z-index)', 'shopglut' ),
						'desc'    => __( 'Raise it if a theme header or chat widget covers the popups.', 'shopglut' ),
						'default' => 99999,
					),
				),
			),
			array(
				'id'     => 'subscribers',
				'title'  => __( 'Subscribers', 'shopglut' ),
				'icon'   => 'dashicons-email-alt',
				'fields' => array(
					array(
						'id'      => 'store_subscribers',
						'type'    => 'switcher',
						'title'   => __( 'Save subscribers in WordPress', 'shopglut' ),
						'desc'    => __( 'Emails from subscribe popups are kept in a list you can export as CSV.', 'shopglut' ),
						'default' => true,
					),
					array(
						'id'      => 'notify_admin',
						'type'    => 'switcher',
						'title'   => __( 'Email me about new subscribers', 'shopglut' ),
						'default' => false,
					),
					array(
						'id'         => 'notify_email',
						'type'       => 'text',
						'title'      => __( 'Send to', 'shopglut' ),
						'attributes' => array( 'placeholder' => get_option( 'admin_email' ) ),
						'dependency' => array( 'notify_admin', '==', 'true' ),
					),
					array(
						'id'      => 'consent_text',
						'type'    => 'textarea',
						'title'   => __( 'Default consent text', 'shopglut' ),
						'default' => __( 'I agree to receive emails and accept the privacy policy.', 'shopglut' ),
					),
				),
			),
		);
	}

	/**
	 * Front end: popups in the footer, subscribe form, Subscribers admin page.
	 *
	 * @return void
	 */
	public function boot() {
		require_once $this->dir . 'frontend/class-shopglut-popups.php';
		new ShopGlut_Popups( $this );
		if ( is_admin() ) {
			require_once $this->dir . 'frontend/class-shopglut-subscribers-admin.php';
			new ShopGlut_Subscribers_Admin();
		}
	}
}

return 'ShopGlut_Module_Popups';
