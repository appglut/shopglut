<?php
/**
 * Order Tracking page: [shopglut_order_tracking] lets customers look up an order with its number and their
 * billing email or phone, and see the status, items, updates and the shipment tracking link (from
 * BusinessGlut's Shipment Tracking / Courier modules when present).
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Module_Order_Tracking class.
 */
class ShopGlut_Module_Order_Tracking extends ShopGlut_Module {

	public function id() {
		return 'order-tracking';
	}

	public function info() {
		return array(
			'title'        => __( 'Order Tracking Page', 'shopglut' ),
			'description'  => __( 'A page where customers look up an order by number and email or phone, and see its status and tracking.', 'shopglut' ),
			'icon'         => 'faCircleCheck',
			'category'     => 'cart',
			'order'        => 130,
			'features'     => array(
				__( 'No login needed: order number + email or phone', 'shopglut' ),
				__( 'Status, items and order updates', 'shopglut' ),
				__( 'Courier tracking link (BusinessGlut)', 'shopglut' ),
				__( 'Works with custom order numbers', 'shopglut' ),
			),
		);
	}

	public function settings_sections() {
		return $this->schema( $this->dir . 'settings/options.php', 'options' );
	}

	private function opt( $key, $default = '' ) {
		return shopglut_get_module_setting( 'order-tracking', $key, $default );
	}

	public function boot() {
		if ( class_exists( 'WooCommerce' ) ) {
			add_shortcode( 'shopglut_order_tracking', array( $this, 'shortcode' ) );
		}
	}

	/**
	 * Find an order by its shown number (custom number or id) that matches the email or phone.
	 *
	 * @param string $number  Order number.
	 * @param string $contact Email or phone.
	 * @return WC_Order|null
	 */
	public function find( $number, $contact ) {
		$number  = trim( ltrim( (string) $number, '#' ) );
		$contact = trim( (string) $contact );
		if ( '' === $number || '' === $contact ) {
			return null;
		}
		$found = wc_get_orders( array( 'limit' => 5, 'meta_key' => '_bgl_order_number', 'meta_value' => $number ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
		if ( ! $found && ctype_digit( $number ) ) {
			$o     = wc_get_order( (int) $number );
			$found = ( $o && 'shop_order' === $o->get_type() ) ? array( $o ) : array();
		}
		$digits = substr( preg_replace( '/\D+/', '', $contact ), -10 );
		foreach ( $found as $order ) {
			if ( (string) $order->get_order_number() !== $number ) {
				continue;
			}
			$email_ok = is_email( $contact ) && 0 === strcasecmp( $contact, $order->get_billing_email() );
			$phone_ok = strlen( $digits ) >= 8 && substr( preg_replace( '/\D+/', '', $order->get_billing_phone() ), -10 ) === $digits;
			if ( $email_ok || $phone_ok ) {
				return $order;
			}
		}
		return null;
	}

	/**
	 * [shopglut_order_tracking]
	 *
	 * @return string
	 */
	public function shortcode() {
		$number  = isset( $_POST['sg_track_number'] ) ? sanitize_text_field( wp_unslash( $_POST['sg_track_number'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$contact = isset( $_POST['sg_track_contact'] ) ? sanitize_text_field( wp_unslash( $_POST['sg_track_contact'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$out     = '<div class="sg-track"><h3>' . esc_html( $this->opt( 'title', __( 'Track your order', 'shopglut' ) ) ) . '</h3>';
		$out    .= '<form method="post" class="sg-track-form">' . wp_nonce_field( 'sg_track', 'sg_track_nonce', true, false );
		$out    .= '<p><label>' . esc_html__( 'Order number', 'shopglut' ) . '<br><input type="text" name="sg_track_number" required value="' . esc_attr( $number ) . '"></label></p>';
		$out    .= '<p><label>' . esc_html__( 'Billing email or phone', 'shopglut' ) . '<br><input type="text" name="sg_track_contact" required value="' . esc_attr( $contact ) . '"></label></p>';
		$out    .= '<p><button type="submit" class="button">' . esc_html__( 'Track', 'shopglut' ) . '</button></p></form>';
		if ( '' !== $number ) {
			$valid = isset( $_POST['sg_track_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sg_track_nonce'] ) ), 'sg_track' );
			$order = $valid ? $this->find( $number, $contact ) : null;
			$out  .= $order ? $this->result( $order ) : '<p class="sg-track-error">' . esc_html__( 'No order found with these details. Please check the order number and the email or phone used at checkout.', 'shopglut' ) . '</p>';
		}
		return $out . '</div><style>.sg-track input{width:100%;max-width:420px}.sg-track-result{margin-top:1.2em;padding:16px;border:1px solid rgba(0,0,0,.1);border-radius:10px}.sg-track-status{display:inline-block;padding:2px 10px;border-radius:99px;background:rgba(0,0,0,.07);font-weight:600}.sg-track-error{color:#b42318}.sg-track-result ul{margin:.5em 0 0 1.2em}</style>';
	}

	/**
	 * Result block.
	 *
	 * @param WC_Order $order Order.
	 * @return string
	 */
	private function result( $order ) {
		$out  = '<div class="sg-track-result"><p>' . esc_html__( 'Order', 'shopglut' ) . ' <strong>#' . esc_html( $order->get_order_number() ) . '</strong> · ' . esc_html( wc_format_datetime( $order->get_date_created() ) ) . '</p>';
		$out .= '<p>' . esc_html__( 'Status:', 'shopglut' ) . ' <span class="sg-track-status">' . esc_html( wc_get_order_status_name( $order->get_status() ) ) . '</span>';
		$cs   = (string) $order->get_meta( '_bgl_courier_status' );
		$out .= $cs ? ' · ' . esc_html__( 'Courier:', 'shopglut' ) . ' ' . esc_html( ucwords( str_replace( '_', ' ', $cs ) ) ) : '';
		$out .= '</p>';
		$t = $order->get_meta( '_bgl_tracking' );
		if ( is_array( $t ) && ! empty( $t['number'] ) ) {
			$link = '';
			if ( class_exists( 'BusinessGlut_Modules' ) ) {
				$mod  = BusinessGlut_Modules::get_instance()->instance( 'shipment-tracking' );
				$link = $mod ? $mod->link( $t ) : '';
			}
			$num  = $link ? '<a href="' . esc_url( $link ) . '" target="_blank" rel="noopener">' . esc_html( $t['number'] ) . '</a>' : esc_html( $t['number'] );
			$out .= '<p>' . esc_html__( 'Tracking:', 'shopglut' ) . ' ' . esc_html( ucfirst( (string) $t['courier'] ) ) . ' ' . $num . '</p>';
		}
		if ( $this->opt( 'show_items', 1 ) ) {
			$out .= '<ul>';
			foreach ( $order->get_items() as $item ) {
				$out .= '<li>' . esc_html( $item->get_name() ) . ' × ' . esc_html( $item->get_quantity() ) . '</li>';
			}
			$out .= '</ul>';
		}
		if ( $this->opt( 'show_notes', 1 ) ) {
			$notes = $order->get_customer_order_notes();
			if ( $notes ) {
				$out .= '<p><strong>' . esc_html__( 'Updates', 'shopglut' ) . '</strong></p><ul>';
				foreach ( $notes as $note ) {
					$out .= '<li>' . esc_html( date_i18n( get_option( 'date_format' ), strtotime( $note->comment_date ) ) ) . ': ' . wp_kses_post( wpautop( wptexturize( $note->comment_content ) ) ) . '</li>';
				}
				$out .= '</ul>';
			}
		}
		return $out . '</div>';
	}
}

return 'ShopGlut_Module_Order_Tracking';
