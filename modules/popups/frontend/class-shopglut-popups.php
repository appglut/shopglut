<?php
/**
 * Popups & Banners front end.
 *
 * Every active popup whose Display Rules match the page is printed hidden in the footer with its settings
 * as JSON. The script decides when to open it (trigger, frequency, device, popups per page), so the
 * page can be cached. Subscribe popups save subscribers in {prefix}shopglut_subscribers.
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Popups class.
 */
class ShopGlut_Popups {

	const DB_VERSION = '1';
	const FREE_TYPES = array( 'modal', 'bar', 'slide-in', 'subscribe' );

	/**
	 * Module.
	 *
	 * @var ShopGlut_Module
	 */
	private $module;

	/**
	 * Module options.
	 *
	 * @var array
	 */
	private $o;

	/**
	 * Popups printed on this page.
	 *
	 * @var array
	 */
	private $popups = array();

	/**
	 * Constructor.
	 *
	 * @param ShopGlut_Module $module Module.
	 */
	public function __construct( $module ) {
		$this->module = $module;
		$values       = appglut_get_option( ShopGlut_Panels::module_option( 'popups' ) );
		$this->o      = is_array( $values ) ? $values : array();

		add_action( 'wp', array( $this, 'collect' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ), 30 );
		add_action( 'wp_footer', array( $this, 'render' ), 5 ); // Before the footer scripts (priority 20).
		add_shortcode( 'shopglut_popup_button', array( $this, 'button_shortcode' ) );

		add_action( 'wp_ajax_shopglut_popup_subscribe', array( $this, 'ajax_subscribe' ) );
		add_action( 'wp_ajax_nopriv_shopglut_popup_subscribe', array( $this, 'ajax_subscribe' ) );

		add_filter( 'wp_privacy_personal_data_exporters', array( $this, 'register_exporter' ) );
		add_filter( 'wp_privacy_personal_data_erasers', array( $this, 'register_eraser' ) );

		self::maybe_install();
	}

	/**
	 * Option of the module Settings.
	 *
	 * @param string $key     Key.
	 * @param mixed  $default Default.
	 * @return mixed
	 */
	private function opt( $key, $default ) {
		return array_key_exists( $key, $this->o ) && '' !== $this->o[ $key ] ? $this->o[ $key ] : $default;
	}

	/* ---------------------------------------------------------------------
	 * Subscribers table
	 * ------------------------------------------------------------------- */

	/**
	 * Subscribers table name.
	 *
	 * @return string
	 */
	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'shopglut_subscribers';
	}

	/**
	 * Create or update the subscribers table.
	 *
	 * @return void
	 */
	public static function maybe_install() {
		if ( get_option( 'shopglut_subscribers_db' ) === self::DB_VERSION ) {
			return;
		}
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$table = self::table();
		dbDelta(
			"CREATE TABLE {$table} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				email varchar(190) NOT NULL,
				name varchar(190) NOT NULL DEFAULT '',
				popup_id bigint(20) unsigned NOT NULL DEFAULT 0,
				consent tinyint(1) NOT NULL DEFAULT 0,
				page_url varchar(255) NOT NULL DEFAULT '',
				ip_hash char(64) NOT NULL DEFAULT '',
				created_at datetime NOT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY email (email),
				KEY popup_id (popup_id)
			) {$wpdb->get_charset_collate()};"
		);
		update_option( 'shopglut_subscribers_db', self::DB_VERSION, false );
	}

	/* ---------------------------------------------------------------------
	 * Which popups show
	 * ------------------------------------------------------------------- */

	/**
	 * Context of this page for the Display Rules.
	 *
	 * @return array
	 */
	private static function context() {
		$ctx = array(
			'page'      => '',
			'page_id'   => (int) get_queried_object_id(),
			'logged_in' => is_user_logged_in(),
		);
		if ( is_front_page() || is_home() ) {
			$ctx['page'] = 'home';
		} elseif ( function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() ) ) {
			$ctx['page'] = 'shop';
		} elseif ( function_exists( 'is_product' ) && is_product() ) {
			$ctx['page']       = 'product';
			$ctx['product_id'] = (int) get_queried_object_id();
		} elseif ( function_exists( 'is_cart' ) && is_cart() ) {
			$ctx['page'] = 'cart';
		} elseif ( function_exists( 'is_checkout' ) && is_checkout() ) {
			$ctx['page'] = 'checkout';
		} elseif ( function_exists( 'is_account_page' ) && is_account_page() ) {
			$ctx['page'] = 'account';
		} elseif ( is_page() ) {
			$ctx['page'] = 'page';
		}
		return $ctx;
	}

	/**
	 * Find the popups of this page.
	 *
	 * @return void
	 */
	public function collect() {
		if ( is_admin() || wp_doing_ajax() || is_feed() ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- preview link from the editor, read-only.
		$preview = isset( $_GET['shopglut_popup_preview'] ) && current_user_can( ShopGlut_Admin::cap( 'module:popups' ) ) ? absint( $_GET['shopglut_popup_preview'] ) : 0;
		if ( $preview ) {
			$layout = ShopGlut_Layouts::get( $preview );
			if ( $layout && 'popups' === $layout['module'] ) {
				$this->popups[] = array( $layout, true );
			}
			return;
		}
		if ( $this->opt( 'hide_for_admins', false ) && current_user_can( 'manage_options' ) ) {
			return;
		}
		$types = shopglut_is_pro() ? null : self::FREE_TYPES;
		$ctx   = self::context();
		foreach ( ShopGlut_Layouts::active( 'popups' ) as $layout ) {
			if ( $types && ! in_array( $layout['template'], $types, true ) ) {
				continue;
			}
			if ( ! in_array( $layout['template'], self::FREE_TYPES, true ) && ! has_action( 'shopglut_popup_render_' . $layout['template'] ) ) {
				continue; // A Pro type without its front end.
			}
			if ( $this->module->layout_matches( $layout, $ctx ) ) {
				$this->popups[] = array( $layout, false );
			}
		}
	}

	/* ---------------------------------------------------------------------
	 * Output
	 * ------------------------------------------------------------------- */

	/**
	 * Section of a popup's settings.
	 *
	 * @param array  $layout  Layout.
	 * @param string $section content | trigger | frequency | behavior | design | display.
	 * @return array
	 */
	private static function section( $layout, $section ) {
		return isset( $layout['settings'][ $section ] ) && is_array( $layout['settings'][ $section ] ) ? $layout['settings'][ $section ] : array();
	}

	/**
	 * A value with a default.
	 *
	 * @param array  $arr     Values.
	 * @param string $key     Key.
	 * @param mixed  $default Default.
	 * @return mixed
	 */
	private static function v( $arr, $key, $default ) {
		return array_key_exists( $key, $arr ) && '' !== $arr[ $key ] && null !== $arr[ $key ] ? $arr[ $key ] : $default;
	}

	/**
	 * Replace {site_name}, {first_name}, {cart_total}.
	 *
	 * @param string $text Text.
	 * @return string
	 */
	private static function placeholders( $text ) {
		$user  = wp_get_current_user();
		$total = ( function_exists( 'WC' ) && WC()->cart ) ? wp_strip_all_tags( WC()->cart->get_cart_total() ) : '';
		return strtr(
			(string) $text,
			array(
				'{site_name}'  => get_bloginfo( 'name' ),
				'{first_name}' => $user && $user->exists() ? ( $user->first_name ? $user->first_name : $user->display_name ) : '',
				'{cart_total}' => $total,
			)
		);
	}

	/**
	 * Print the popups.
	 *
	 * @return void
	 */
	public function render() {
		if ( ! $this->popups ) {
			return;
		}
		$z = absint( $this->opt( 'z_index', 99999 ) );
		echo '<div class="shopglut-popups" data-max="' . esc_attr( absint( $this->opt( 'max_per_page', 1 ) ) ) . '" style="--sgp-z:' . esc_attr( $z ) . '">';
		foreach ( $this->popups as $item ) {
			list( $layout, $preview ) = $item;
			if ( ! in_array( $layout['template'], self::FREE_TYPES, true ) ) {
				do_action( 'shopglut_popup_render_' . $layout['template'], $layout, $preview );
				continue;
			}
			$this->render_popup( $layout, $preview );
		}
		echo '</div>';
	}

	/**
	 * One popup.
	 *
	 * @param array $layout  Layout.
	 * @param bool  $preview Editor preview (ignores frequency and devices).
	 * @return void
	 */
	private function render_popup( $layout, $preview ) {
		$id   = (int) $layout['id'];
		$type = $layout['template'];
		$bar  = 'bar' === $type;
		$c    = self::section( $layout, 'content' );
		$t    = self::section( $layout, 'trigger' );
		$f    = self::section( $layout, 'frequency' );
		$b    = self::section( $layout, 'behavior' );
		$d    = self::section( $layout, 'design' );
		$disp = self::section( $layout, 'display' );

		$default_pos = $bar ? 'top' : ( 'slide-in' === $type ? 'bottom-right' : 'center' );
		$config      = array(
			'id'          => $id,
			'type'        => $type,
			'preview'     => $preview,
			'openOn'      => self::v( $t, 'open_on', 'load' ),
			'delay'       => (int) self::v( $t, 'delay', $bar ? 0 : 5 ),
			'scroll'      => (int) self::v( $t, 'scroll_percent', 50 ),
			'selector'    => (string) self::v( $t, 'click_selector', '' ),
			'showAgain'   => self::v( $f, 'show_again', 'days' ),
			'days'        => (int) self::v( $f, 'days', 7 ),
			'stopAfter'   => (bool) self::v( $f, 'stop_after_action', true ),
			'closeDelay'  => (int) self::v( $b, 'close_delay', 0 ),
			'autoClose'   => (int) self::v( $b, 'auto_close', 0 ),
			'overlay'     => ! $bar && 'slide-in' !== $type && (bool) self::v( $b, 'close_on_overlay', true ),
			'esc'         => ! $bar && (bool) self::v( $b, 'close_on_esc', true ),
			'lockScroll'  => in_array( $type, array( 'modal', 'subscribe' ), true ) && (bool) self::v( $b, 'lock_scroll', true ),
			'push'        => $bar && (bool) self::v( $b, 'push_content', true ),
			'devices'     => array_values( (array) self::v( $disp, 'devices', array( 'desktop', 'tablet', 'mobile' ) ) ),
			'after'       => self::v( $c, 'after_subscribe', 'message' ),
			'redirect'    => esc_url_raw( (string) self::v( $c, 'redirect_url', '' ) ),
		);
		$position    = sanitize_key( (string) self::v( $d, 'position', $default_pos ) );
		$animation   = sanitize_key( (string) self::v( $b, 'animation', $bar ? 'slide-down' : 'fade' ) );
		$color       = function ( $key, $default ) use ( $d ) {
			$v = (string) self::v( $d, $key, $default );
			return preg_match( '/^(#[0-9a-fA-F]{3,8}|rgba?\([0-9.,\s%]+\))$/', $v ) ? $v : $default;
		};
		$style       = '--sgp-bg:' . $color( 'background', $bar ? '#111827' : '#ffffff' )
			. ';--sgp-text:' . $color( 'text_color', $bar ? '#ffffff' : '#1f2937' )
			. ';--sgp-btn-bg:' . $color( 'button_background', 'var(--shopglut-accent, #7c3aed)' )
			. ';--sgp-btn:' . $color( 'button_color', '#ffffff' )
			. ';--sgp-radius:' . absint( self::v( $d, 'radius', $bar ? 0 : 12 ) ) . 'px'
			. ';--sgp-pad:' . absint( self::v( $d, 'padding', $bar ? 10 : 32 ) ) . 'px'
			. ';--sgp-h:' . absint( self::v( $d, 'heading_size', $bar ? 15 : 26 ) ) . 'px'
			. ';--sgp-overlay:' . $color( 'overlay_color', 'rgba(17,24,39,0.6)' );
		if ( ! $bar ) {
			$style .= ';--sgp-width:' . absint( self::v( $d, 'width', 'slide-in' === $type ? 360 : 520 ) ) . 'px';
		}

		$heading = self::placeholders( (string) self::v( $c, 'heading', '' ) );
		$body    = self::placeholders( (string) self::v( $c, 'body', '' ) );
		$image   = self::v( $c, 'image', array() );
		$img_url = is_array( $image ) && ! empty( $image['url'] ) ? $image['url'] : ( is_string( $image ) ? $image : '' );
		$img_pos = sanitize_key( (string) self::v( $c, 'image_position', 'top' ) );
		$coupon  = (string) self::v( $c, 'coupon_code', '' );
		$title   = 'shopglut-popup-title-' . $id;

		$classes = array( 'shopglut-popup', 'shopglut-popup--' . $type, 'pos-' . $position, 'anim-' . $animation );
		if ( $img_url && ! $bar ) {
			$classes[] = 'has-image img-' . $img_pos;
		}
		$extra = sanitize_html_class( (string) self::v( $d, 'css_class', '' ) );
		if ( $extra ) {
			$classes[] = $extra;
		}
		$role = $bar ? 'role="region" aria-label="' . esc_attr( $heading ? wp_strip_all_tags( $heading ) : __( 'Announcement', 'shopglut' ) ) . '"' : 'role="dialog" aria-modal="' . ( 'slide-in' === $type ? 'false' : 'true' ) . '" aria-labelledby="' . esc_attr( $title ) . '"';
		?>
		<div id="shopglut-popup-<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" style="<?php echo esc_attr( $style ); ?>" data-config="<?php echo esc_attr( wp_json_encode( $config ) ); ?>" hidden>
			<?php if ( ! $bar && 'slide-in' !== $type ) : ?>
				<div class="shopglut-popup-overlay"></div>
			<?php endif; ?>
			<div class="shopglut-popup-box" <?php echo $role; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?> tabindex="-1"<?php echo ( $img_url && 'background' === $img_pos && ! $bar ) ? ' style="background-image:url(' . esc_url( $img_url ) . ')"' : ''; ?>>
				<?php if ( self::v( $b, 'close_button', true ) ) : ?>
					<button type="button" class="shopglut-popup-close" data-action="close" aria-label="<?php esc_attr_e( 'Close', 'shopglut' ); ?>"><?php echo shopglut_icon( 'times' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
				<?php endif; ?>
				<?php if ( $img_url && ! $bar && 'background' !== $img_pos ) : ?>
					<div class="shopglut-popup-image"><img src="<?php echo esc_url( $img_url ); ?>" alt="" loading="lazy"></div>
				<?php endif; ?>
				<div class="shopglut-popup-content">
					<?php if ( '' !== $heading ) : ?>
						<<?php echo $bar ? 'strong' : 'h2'; ?> id="<?php echo esc_attr( $title ); ?>" class="shopglut-popup-heading"><?php echo esc_html( $heading ); ?></<?php echo $bar ? 'strong' : 'h2'; ?>>
					<?php endif; ?>
					<?php if ( '' !== trim( wp_strip_all_tags( $body ) ) ) : ?>
						<div class="shopglut-popup-body"><?php echo wp_kses_post( $bar ? $body : wpautop( do_shortcode( $body ) ) ); ?></div>
					<?php endif; ?>
					<?php if ( '' !== $coupon ) : ?>
						<div class="shopglut-popup-coupon"><code><?php echo esc_html( $coupon ); ?></code><button type="button" data-action="copy" data-code="<?php echo esc_attr( $coupon ); ?>"><?php esc_html_e( 'Copy', 'shopglut' ); ?></button></div>
					<?php endif; ?>
					<?php
					if ( 'subscribe' === $type ) {
						$this->render_form( $layout, $c );
					} else {
						$text = (string) self::v( $c, 'button_text', '' );
						if ( '' !== $text ) {
							if ( 'close' === self::v( $c, 'button_action', 'link' ) ) {
								echo '<button type="button" class="shopglut-popup-btn" data-action="close" data-done="1">' . esc_html( $text ) . '</button>';
							} else {
								$new = self::v( $c, 'button_new_tab', false );
								echo '<a class="shopglut-popup-btn" data-action="link" href="' . esc_url( (string) self::v( $c, 'button_url', wc_get_page_permalink( 'shop' ) ) ) . '"' . ( $new ? ' target="_blank" rel="noopener"' : '' ) . '>' . esc_html( $text ) . '</a>';
							}
						}
					}
					$decline = (string) self::v( $c, 'decline_text', $bar ? '' : __( 'No thanks', 'shopglut' ) );
					if ( ! $bar && '' !== $decline ) {
						echo '<button type="button" class="shopglut-popup-decline" data-action="close">' . esc_html( $decline ) . '</button>';
					}
					?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Subscribe form.
	 *
	 * @param array $layout Layout.
	 * @param array $c      Content settings.
	 * @return void
	 */
	private function render_form( $layout, $c ) {
		$consent      = self::v( $c, 'consent', 'none' );
		$consent_text = (string) self::v( $c, 'consent_text', $this->opt( 'consent_text', __( 'I agree to receive emails and accept the privacy policy.', 'shopglut' ) ) );
		?>
		<form class="shopglut-popup-form" novalidate>
			<input type="hidden" name="popup_id" value="<?php echo esc_attr( $layout['id'] ); ?>">
			<div class="shopglut-popup-hp" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
			<?php if ( self::v( $c, 'show_name', false ) ) : ?>
				<input type="text" name="name" placeholder="<?php esc_attr_e( 'First name', 'shopglut' ); ?>" autocomplete="given-name">
			<?php endif; ?>
			<input type="email" name="email" required placeholder="<?php echo esc_attr( (string) self::v( $c, 'email_placeholder', __( 'Your email address', 'shopglut' ) ) ); ?>" autocomplete="email">
			<?php if ( 'none' !== $consent ) : ?>
				<label class="shopglut-popup-consent"><input type="checkbox" name="consent" value="1"<?php echo 'required' === $consent ? ' required' : ''; ?>> <span><?php echo wp_kses_post( $consent_text ); ?></span></label>
			<?php endif; ?>
			<button type="submit" class="shopglut-popup-btn"><?php echo esc_html( (string) self::v( $c, 'submit_text', __( 'Subscribe', 'shopglut' ) ) ); ?></button>
			<p class="shopglut-popup-msg" role="status" data-success="<?php echo esc_attr( (string) self::v( $c, 'success_message', __( 'Thanks! Check your inbox.', 'shopglut' ) ) ); ?>"></p>
		</form>
		<?php
	}

	/**
	 * [shopglut_popup_button id="" text=""].
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	public function button_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'id'   => 0,
				'text' => __( 'Open', 'shopglut' ),
			),
			$atts
		);
		return '<a class="shopglut-popup-open wp-element-button" href="#shopglut-popup-' . absint( $atts['id'] ) . '">' . esc_html( $atts['text'] ) . '</a>';
	}

	/**
	 * Styles and script (only when a popup is on the page).
	 *
	 * @return void
	 */
	public function enqueue() {
		if ( ! $this->popups ) {
			return;
		}
		wp_enqueue_style( 'shopglut-popups', $this->module->url . 'frontend/assets/popups.css', array(), SHOPGLUT_VERSION );
		wp_enqueue_script( 'shopglut-popups', $this->module->url . 'frontend/assets/popups.js', array(), SHOPGLUT_VERSION, true );
		wp_localize_script(
			'shopglut-popups',
			'shopglutPopups',
			array(
				'ajax' => admin_url( 'admin-ajax.php' ),
				'i18n' => array(
					'email'   => __( 'Please enter a valid email address.', 'shopglut' ),
					'consent' => __( 'Please accept the consent checkbox.', 'shopglut' ),
					'copied'  => __( 'Copied!', 'shopglut' ),
					'error'   => __( 'Something went wrong. Please try again.', 'shopglut' ),
				),
			)
		);
	}

	/* ---------------------------------------------------------------------
	 * Subscribe
	 * ------------------------------------------------------------------- */

	/**
	 * AJAX: save a subscriber. No nonce, so cached pages keep working: a honeypot field, email
	 * validation and a per-IP rate limit protect it instead.
	 *
	 * @return void
	 */
	public function ajax_subscribe() {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- public form on cacheable pages, see above.
		if ( ! empty( $_POST['website'] ) ) {
			wp_send_json_success( array( 'message' => '' ) ); // Bot: pretend it worked.
		}
		$email    = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$name     = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$popup_id = isset( $_POST['popup_id'] ) ? absint( $_POST['popup_id'] ) : 0;
		$consent  = ! empty( $_POST['consent'] );
		$page     = isset( $_POST['page'] ) ? esc_url_raw( wp_unslash( $_POST['page'] ) ) : '';
		// phpcs:enable
		if ( ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'shopglut' ) ) );
		}
		$layout = $popup_id ? ShopGlut_Layouts::get( $popup_id ) : null;
		if ( ! $layout || 'popups' !== $layout['module'] || 'subscribe' !== $layout['template'] ) {
			wp_send_json_error( array( 'message' => __( 'This form is no longer available.', 'shopglut' ) ) );
		}
		$c = self::section( $layout, 'content' );
		if ( 'required' === self::v( $c, 'consent', 'none' ) && ! $consent ) {
			wp_send_json_error( array( 'message' => __( 'Please accept the consent checkbox.', 'shopglut' ) ) );
		}

		$ip      = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$ip_hash = hash( 'sha256', $ip . wp_salt( 'auth' ) );
		$rate    = 'shopglut_sub_' . substr( $ip_hash, 0, 20 );
		$tries   = (int) get_transient( $rate );
		if ( $tries >= 10 ) {
			wp_send_json_error( array( 'message' => __( 'Too many attempts. Please try again later.', 'shopglut' ) ) );
		}
		set_transient( $rate, $tries + 1, HOUR_IN_SECONDS );

		if ( $this->opt( 'store_subscribers', true ) ) {
			global $wpdb;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table.
			$exists = $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . self::table() . ' WHERE email = %s', $email ) );
			if ( $exists ) {
				wp_send_json_success( array( 'message' => __( 'You are already subscribed. Thank you!', 'shopglut' ) ) );
			}
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- custom table.
			$wpdb->insert(
				self::table(),
				array(
					'email'      => $email,
					'name'       => $name,
					'popup_id'   => $popup_id,
					'consent'    => $consent ? 1 : 0,
					'page_url'   => substr( $page, 0, 255 ),
					'ip_hash'    => $ip_hash,
					'created_at' => current_time( 'mysql', true ),
				),
				array( '%s', '%s', '%d', '%d', '%s', '%s', '%s' )
			);
		}
		if ( $this->opt( 'notify_admin', false ) ) {
			$to = sanitize_email( (string) $this->opt( 'notify_email', get_option( 'admin_email' ) ) );
			/* translators: %s: site name */
			wp_mail( $to ? $to : get_option( 'admin_email' ), sprintf( __( 'New subscriber on %s', 'shopglut' ), get_bloginfo( 'name' ) ), sprintf( "%s\n%s\n%s", $email, $name, $page ) );
		}
		/**
		 * A visitor subscribed through a popup (email services can hook here).
		 *
		 * @param string $email    Email.
		 * @param string $name     Name.
		 * @param array  $layout   Popup.
		 * @param bool   $consent  Consent given.
		 */
		do_action( 'shopglut_popup_subscribed', $email, $name, $layout, $consent );
		wp_send_json_success( array( 'message' => (string) self::v( $c, 'success_message', __( 'Thanks! Check your inbox.', 'shopglut' ) ) ) );
	}

	/* ---------------------------------------------------------------------
	 * Privacy
	 * ------------------------------------------------------------------- */

	/**
	 * Register the personal data exporter.
	 *
	 * @param array $exporters Exporters.
	 * @return array
	 */
	public function register_exporter( $exporters ) {
		$exporters['shopglut-subscribers'] = array(
			'exporter_friendly_name' => __( 'ShopGlut popup subscribers', 'shopglut' ),
			'callback'               => array( __CLASS__, 'export_personal_data' ),
		);
		return $exporters;
	}

	/**
	 * Register the personal data eraser.
	 *
	 * @param array $erasers Erasers.
	 * @return array
	 */
	public function register_eraser( $erasers ) {
		$erasers['shopglut-subscribers'] = array(
			'eraser_friendly_name' => __( 'ShopGlut popup subscribers', 'shopglut' ),
			'callback'             => array( __CLASS__, 'erase_personal_data' ),
		);
		return $erasers;
	}

	/**
	 * Export a subscriber row.
	 *
	 * @param string $email Email.
	 * @return array
	 */
	public static function export_personal_data( $email ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table.
		$row  = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE email = %s', $email ), ARRAY_A );
		$data = array();
		if ( $row ) {
			$data[] = array(
				'group_id'    => 'shopglut-subscribers',
				'group_label' => __( 'Newsletter subscriptions', 'shopglut' ),
				'item_id'     => 'subscriber-' . $row['id'],
				'data'        => array(
					array(
						'name'  => __( 'Email', 'shopglut' ),
						'value' => $row['email'],
					),
					array(
						'name'  => __( 'Name', 'shopglut' ),
						'value' => $row['name'],
					),
					array(
						'name'  => __( 'Subscribed on', 'shopglut' ),
						'value' => $row['created_at'],
					),
					array(
						'name'  => __( 'Page', 'shopglut' ),
						'value' => $row['page_url'],
					),
				),
			);
		}
		return array(
			'data' => $data,
			'done' => true,
		);
	}

	/**
	 * Erase a subscriber row.
	 *
	 * @param string $email Email.
	 * @return array
	 */
	public static function erase_personal_data( $email ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table.
		$removed = (bool) $wpdb->delete( self::table(), array( 'email' => $email ), array( '%s' ) );
		return array(
			'items_removed'  => $removed,
			'items_retained' => false,
			'messages'       => array(),
			'done'           => true,
		);
	}
}
