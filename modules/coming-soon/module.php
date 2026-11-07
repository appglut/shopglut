<?php
/**
 * Coming Soon: show visitors a coming-soon or maintenance page while store managers keep working on the site.
 *
 * Logged-in users who can manage WooCommerce see the normal site. wp-admin, login, REST, AJAX and cron keep
 * working. Maintenance mode sends 503 + Retry-After so search engines come back later.
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Module_Coming_Soon class.
 */
class ShopGlut_Module_Coming_Soon extends ShopGlut_Module {

	public function id() {
		return 'coming-soon';
	}

	public function info() {
		return array(
			'title'        => __( 'Coming Soon & Maintenance', 'shopglut' ),
			'description'  => __( 'A coming-soon or maintenance page for visitors while you build or fix the store.', 'shopglut' ),
			'icon'         => 'faBullhorn',
			'category'     => 'engagement',
			'order'        => 120,
			'default_on'   => false,
			'features'     => array(
				__( 'Coming soon or maintenance (503) mode', 'shopglut' ),
				__( 'Store managers still see the site', 'shopglut' ),
				__( 'Heading, message, opening date and colours', 'shopglut' ),
			),
		);
	}

	public function settings_sections() {
		return $this->schema( $this->dir . 'settings/options.php', 'options' );
	}

	private function opt( $key, $default = '' ) {
		return shopglut_get_module_setting( 'coming-soon', $key, $default );
	}

	public function boot() {
		if ( 'off' !== $this->opt( 'mode', 'off' ) ) {
			add_action( 'template_redirect', array( $this, 'maybe_show' ), 1 );
		}
	}

	/**
	 * Whether this request gets the page.
	 *
	 * @return bool
	 */
	public function applies() {
		if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || is_user_logged_in() && current_user_can( 'manage_woocommerce' ) ) {
			return false;
		}
		$path = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_parse_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH ) : '';
		return ! preg_match( '#/(wp-login\.php|wp-admin|wp-json|robots\.txt|sitemap[^/]*\.xml)#', $path );
	}

	public function maybe_show() {
		if ( ! apply_filters( 'shopglut_coming_soon_applies', $this->applies() ) ) {
			return;
		}
		$maintenance = 'maintenance' === $this->opt( 'mode', 'off' );
		status_header( $maintenance ? 503 : 200 );
		if ( $maintenance ) {
			header( 'Retry-After: 3600' );
		}
		nocache_headers();
		echo $this->page(); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in page().
		exit;
	}

	/**
	 * The page HTML.
	 *
	 * @return string
	 */
	public function page() {
		$bg    = sanitize_hex_color( $this->opt( 'background', '#111827' ) ) ?: '#111827';
		$fg    = sanitize_hex_color( $this->opt( 'text_color', '#ffffff' ) ) ?: '#ffffff';
		$date  = trim( (string) $this->opt( 'date' ) );
		$name  = get_bloginfo( 'name' );
		$logo  = get_theme_mod( 'custom_logo' ) ? wp_get_attachment_image_url( get_theme_mod( 'custom_logo' ), 'medium' ) : '';
		$html  = '<!doctype html><html ' . get_language_attributes() . '><head><meta charset="' . esc_attr( get_bloginfo( 'charset' ) ) . '"><meta name="viewport" content="width=device-width,initial-scale=1">';
		$html .= '<title>' . esc_html( $this->opt( 'heading', '' ) . ' – ' . $name ) . '</title>';
		$html .= '<style>body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;background:' . $bg . ';color:' . $fg . ';font:16px/1.6 system-ui,-apple-system,Segoe UI,Roboto,sans-serif;text-align:center}main{max-width:560px;padding:32px 20px}h1{font-size:clamp(28px,6vw,44px);margin:.4em 0}img{max-height:80px;width:auto}.date{opacity:.8;font-weight:600}a{color:inherit;opacity:.7;font-size:14px}</style></head><body><main>';
		$html .= $logo ? '<img src="' . esc_url( $logo ) . '" alt="' . esc_attr( $name ) . '">' : '<strong>' . esc_html( $name ) . '</strong>';
		$html .= '<h1>' . esc_html( $this->opt( 'heading', __( 'We are opening soon', 'shopglut' ) ) ) . '</h1>';
		$html .= wpautop( esc_html( $this->opt( 'message', '' ) ) );
		$html .= $date ? '<p class="date">' . esc_html( $date ) . '</p>' : '';
		$html .= $this->opt( 'show_login', 1 ) ? '<p><a href="' . esc_url( wp_login_url() ) . '">' . esc_html__( 'Log in', 'shopglut' ) . '</a></p>' : '';
		return $html . '</main></body></html>';
	}
}

return 'ShopGlut_Module_Coming_Soon';
