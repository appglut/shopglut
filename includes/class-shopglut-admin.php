<?php
/**
 * Admin pages.
 *
 * Every ShopGlut screen is a WordPress admin page drawn by a React app (resources/<entry>.js)
 * under a shared header, with the WordPress menu, toolbar and notices hidden. Option screens
 * (Global Settings, module Settings, the layout editor) print an AppGlut panel from PHP and the
 * React app only draws the header above it.
 *
 * Pages:
 *   shopglut                     Dashboard        (dashboard)
 *   shopglut-modules             Modules          (modules)
 *   shopglut-{module}            Module: layouts list, or its Settings panel (layouts | panel)
 *   shopglut-{module}&tab=settings  Module Settings panel                   (panel)
 *   shopglut-layout&layout_id=N  Layout editor    (panel)
 *   shopglut-settings            Global Settings  (panel)
 *   shopglut-tools               Tools            (tools)
 *   shopglut-pro                 Pro features     (app)
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Admin class.
 */
class ShopGlut_Admin {

	/** Top-level menu slug. */
	const MENU = 'shopglut';

	/**
	 * Single instance.
	 *
	 * @var ShopGlut_Admin|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return ShopGlut_Admin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menus' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_head', array( $this, 'menu_icon_styles' ) );
		add_filter( 'admin_body_class', array( $this, 'body_class' ) );
		add_filter( 'script_loader_tag', array( $this, 'module_attribute' ), 10, 2 );
		add_filter( 'plugin_action_links_' . SHOPGLUT_BASENAME, array( $this, 'action_links' ) );
		add_action( 'current_screen', array( $this, 'hidden_page_title' ) );
	}

	/**
	 * Hidden pages have no menu title, which WordPress uses for <title>: give the layout editor one.
	 *
	 * @return void
	 */
	public function hidden_page_title() {
		if ( 'shopglut-layout' === $this->page() ) {
			$GLOBALS['title'] = __( 'Edit Layout', 'shopglut' ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Read by admin-header.php.
		}
	}

	/**
	 * Capability for a part of ShopGlut.
	 *
	 * @param string $context menu | dashboard | modules | settings | tools | module:{id}.
	 * @return string
	 */
	public static function cap( $context ) {
		/**
		 * Capability required for a part of ShopGlut.
		 *
		 * @param string $cap     Capability (manage_woocommerce).
		 * @param string $context menu | dashboard | modules | settings | tools | module:{id}.
		 */
		return (string) apply_filters( 'shopglut_capability', 'manage_woocommerce', (string) $context );
	}

	/**
	 * Current page slug.
	 *
	 * @return string
	 */
	private function page() {
		return isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	}

	/**
	 * Admin URL of a ShopGlut page.
	 *
	 * @param string $slug Page slug.
	 * @param array  $args Query args.
	 * @return string
	 */
	public static function url( $slug, $args = array() ) {
		return add_query_arg( array_merge( array( 'page' => $slug ), $args ), admin_url( 'admin.php' ) );
	}

	/**
	 * Page slug of a module.
	 *
	 * @param string $module Module ID.
	 * @return string
	 */
	public static function module_slug( $module ) {
		return 'shopglut-' . $module;
	}

	/**
	 * The module a page belongs to.
	 *
	 * @param string $page Page slug.
	 * @return ShopGlut_Module|null
	 */
	private function page_module( $page ) {
		if ( 0 !== strpos( $page, 'shopglut-' ) ) {
			return null;
		}
		return ShopGlut_Modules::instance()->get( substr( $page, strlen( 'shopglut-' ) ) );
	}

	/**
	 * Register menu pages.
	 *
	 * @return void
	 */
	public function register_menus() {
		$registry = ShopGlut_Modules::instance();

		add_menu_page( __( 'ShopGlut', 'shopglut' ), __( 'ShopGlut', 'shopglut' ), self::cap( 'menu' ), self::MENU, array( $this, 'render_app' ), SHOPGLUT_URL . 'global-assets/images/logo-small.png', 56 );
		add_submenu_page( self::MENU, __( 'Dashboard', 'shopglut' ), __( 'Dashboard', 'shopglut' ), self::cap( 'dashboard' ), self::MENU, array( $this, 'render_app' ) );
		add_submenu_page( self::MENU, __( 'Modules', 'shopglut' ), __( 'Modules', 'shopglut' ), self::cap( 'modules' ), 'shopglut-modules', array( $this, 'render_app' ) );

		foreach ( $registry->all() as $id => $module ) {
			if ( ! $registry->is_enabled( $id ) ) {
				continue;
			}
			$info = $module->get_info();
			if ( ! $info['layouts'] && ! $module->settings_sections() ) {
				continue;
			}
			add_submenu_page( self::MENU, $info['title'], $info['title'], self::cap( 'module:' . $id ), self::module_slug( $id ), array( $this, 'render_module' ) );
		}

		/**
		 * Add-ons add their pages here (add_submenu_page with this parent). A page drawn by the ShopGlut
		 * app is listed in shopglut_admin_entry_map as 'app' and uses render_app() as its callback.
		 *
		 * @param string         $parent Parent slug.
		 * @param ShopGlut_Admin $admin  This class.
		 */
		do_action( 'shopglut_admin_menu', self::MENU, $this );

		add_submenu_page( self::MENU, __( 'Global Settings', 'shopglut' ), __( 'Global Settings', 'shopglut' ), self::cap( 'settings' ), 'shopglut-settings', array( $this, 'render_settings' ) );
		add_submenu_page( self::MENU, __( 'Tools', 'shopglut' ), __( 'Tools', 'shopglut' ), self::cap( 'tools' ), 'shopglut-tools', array( $this, 'render_app' ) );

		if ( apply_filters( 'shopglut_show_pro_menu', ! shopglut_is_pro() ) ) {
			add_submenu_page( self::MENU, __( 'ShopGlut Pro', 'shopglut' ), '<span style="color:#fbbf24">' . esc_html__( '⭐ Go Pro', 'shopglut' ) . '</span>', self::cap( 'menu' ), 'shopglut-pro', array( $this, 'render_app' ) );
		}

		// Hidden: layout editor.
		add_submenu_page( '', __( 'Edit Layout', 'shopglut' ), '', self::cap( 'menu' ), 'shopglut-layout', array( $this, 'render_layout_editor' ) );
	}

	/**
	 * Icon size in the WordPress menu.
	 *
	 * @return void
	 */
	public function menu_icon_styles() {
		echo '<style>#toplevel_page_shopglut .wp-menu-image img{width:20px;height:20px;padding:7px 0 0;opacity:1}</style>';
	}

	/**
	 * Settings link on the Plugins screen.
	 *
	 * @param array $links Links.
	 * @return array
	 */
	public function action_links( $links ) {
		array_unshift( $links, '<a href="' . esc_url( self::url( self::MENU ) ) . '">' . esc_html__( 'Dashboard', 'shopglut' ) . '</a>' );
		if ( ! shopglut_is_pro() ) {
			$links[] = '<a href="' . esc_url( SHOPGLUT_UPGRADE_URL ) . '" target="_blank" rel="noopener" style="color:#7c3aed;font-weight:600">' . esc_html__( 'Go Pro', 'shopglut' ) . '</a>';
		}
		return $links;
	}

	/**
	 * Whether this is a ShopGlut admin page.
	 *
	 * @return bool
	 */
	private function is_shopglut_page() {
		$page = $this->page();
		return self::MENU === $page || 0 === strpos( $page, 'shopglut-' );
	}

	/**
	 * Body class on ShopGlut pages.
	 *
	 * @param string $classes Classes.
	 * @return string
	 */
	public function body_class( $classes ) {
		return $this->is_shopglut_page() ? $classes . ' shopglut-admin-page' : $classes;
	}

	/**
	 * App entry (resources/<entry>.js) of the current page.
	 *
	 * @return string
	 */
	private function entry() {
		$page = $this->page();
		$map  = array(
			self::MENU          => 'dashboard',
			'shopglut-modules'  => 'modules',
			'shopglut-settings' => 'panel',
			'shopglut-layout'   => 'panel',
			'shopglut-tools'    => 'tools',
			'shopglut-pro'      => 'app',
		);
		$module = $this->page_module( $page );
		if ( $module ) {
			$map[ $page ] = $this->module_shows_layouts( $module ) ? 'layouts' : 'panel';
		}

		/**
		 * Admin pages drawn by the ShopGlut app. Add-on pages use 'app', which draws the component
		 * registered with the JS filter shopglut.adminPages.
		 *
		 * @param array $map Page slug => entry.
		 */
		$map = apply_filters( 'shopglut_admin_entry_map', $map );
		return isset( $map[ $page ] ) ? $map[ $page ] : '';
	}

	/**
	 * Whether a module page shows its layouts list (not its Settings panel).
	 *
	 * @param ShopGlut_Module $module Module.
	 * @return bool
	 */
	private function module_shows_layouts( $module ) {
		$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		return $module->get_info()['layouts'] && 'settings' !== $tab;
	}

	/**
	 * The AppGlut panel shown on the current page, if any.
	 *
	 * @return ShopGlut_Options_Panel|null
	 */
	private function current_panel() {
		$page = $this->page();
		if ( 'shopglut-settings' === $page ) {
			return AppGlut::container( ShopGlut_Settings::OPTION );
		}
		if ( 'shopglut-layout' === $page ) {
			$id = isset( $_GET['layout_id'] ) ? absint( $_GET['layout_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
			return $id ? ShopGlut_Panels::layout_panel( $id ) : null;
		}
		$module = $this->page_module( $page );
		if ( $module && ! $this->module_shows_layouts( $module ) ) {
			return AppGlut::container( ShopGlut_Panels::module_option( $module->id() ) );
		}
		return null;
	}

	/**
	 * Enqueue the app of the current page and the data it reads (window.shopglut_admin).
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		if ( ! $this->is_shopglut_page() ) {
			return;
		}
		$page  = $this->page();
		$entry = $this->entry();
		if ( '' === $entry ) {
			return;
		}

		$handle = 'shopglut-' . $entry;
		$file   = SHOPGLUT_PATH . 'resources/' . $entry . '.js';

		wp_enqueue_script( 'wp-i18n' );
		wp_enqueue_script( 'wp-hooks' );
		if ( file_exists( $file ) ) {
			wp_enqueue_script( $handle, SHOPGLUT_URL . 'resources/' . $entry . '.js', array( 'wp-i18n', 'wp-hooks' ), SHOPGLUT_VERSION, true );
			wp_set_script_translations( $handle, 'shopglut', SHOPGLUT_PATH . 'languages' );
		}
		foreach ( $this->entry_css( $entry ) as $css ) {
			wp_enqueue_style( 'shopglut-' . sanitize_key( pathinfo( $css, PATHINFO_FILENAME ) ), SHOPGLUT_URL . 'resources/' . $css, array(), SHOPGLUT_VERSION );
		}

		$panel = $this->current_panel();
		if ( $panel ) {
			wp_enqueue_media();
			$panel->enqueue_assets();
		}

		wp_localize_script( file_exists( $file ) ? $handle : 'wp-i18n', 'shopglut_admin', apply_filters( 'shopglut_admin_localize_data', $this->localize_data( $page ), $page ) );

		/**
		 * Enqueue add-on scripts for a ShopGlut page. Make them depend on $handle: they run after the
		 * app has set up window.shopglut and before it draws the page. Module scripts must be listed
		 * in the shopglut_module_script_handles filter.
		 *
		 * @param string $page   Page slug.
		 * @param string $handle App script handle.
		 * @param string $entry  App entry.
		 */
		do_action( 'shopglut_admin_enqueue_scripts', $page, $handle, $entry );
	}

	/**
	 * CSS files of an app entry and the chunks it imports, from the Vite manifest.
	 *
	 * @param string $entry App entry.
	 * @return string[] Paths relative to resources/.
	 */
	private function entry_css( $entry ) {
		$file     = SHOPGLUT_PATH . 'resources/.vite/manifest.json';
		$manifest = is_readable( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local file.
		if ( ! is_array( $manifest ) ) {
			return array();
		}
		$css  = array();
		$seen = array();
		$walk = function ( $key ) use ( &$walk, &$css, &$seen, $manifest ) {
			if ( isset( $seen[ $key ] ) || ! isset( $manifest[ $key ] ) ) {
				return;
			}
			$seen[ $key ] = true;
			foreach ( (array) ( $manifest[ $key ]['imports'] ?? array() ) as $import ) {
				$walk( $import );
			}
			foreach ( (array) ( $manifest[ $key ]['css'] ?? array() ) as $path ) {
				$css[ $path ] = $path;
			}
		};
		$walk( 'assets/src/pages/index-' . $entry . '.jsx' );
		return array_values( $css );
	}

	/**
	 * Data for the admin app.
	 *
	 * @param string $page Page slug.
	 * @return array
	 */
	private function localize_data( $page ) {
		$registry = ShopGlut_Modules::instance();
		$modules  = array();
		foreach ( $registry->all() as $id => $module ) {
			$info           = $module->get_info();
			$modules[ $id ] = array(
				'id'           => $id,
				'title'        => $info['title'],
				'icon'         => $info['icon'],
				'category'     => $info['category'],
				'layouts'      => (bool) $info['layouts'],
				'layout_label' => $info['layout_label'],
				'layouts_label' => $info['layouts_label'],
				'pro'          => (bool) $info['pro'],
				'enabled'      => $registry->is_enabled( $id ),
				'has_settings' => ! empty( $module->settings_sections() ),
				'url'          => self::url( self::module_slug( $id ) ),
				'settings_url' => self::url( self::module_slug( $id ), array( 'tab' => 'settings' ) ),
			);
		}

		$data = array(
			'ajax_url'      => admin_url( 'admin-ajax.php' ),
			'nonce'         => wp_create_nonce( 'shopglut_admin' ),
			'version'       => SHOPGLUT_VERSION,
			'api_version'   => SHOPGLUT_EXTEND_API,
			'pro_enabled'   => shopglut_is_pro(),
			'pro_url'       => SHOPGLUT_UPGRADE_URL,
			'plugin_url'    => SHOPGLUT_URL,
			'dashboard_url' => admin_url( 'index.php' ),
			'page'          => $page,
			'pages'         => array(
				'dashboard' => self::url( self::MENU ),
				'modules'   => self::url( 'shopglut-modules' ),
				'settings'  => self::url( 'shopglut-settings' ),
				'tools'     => self::url( 'shopglut-tools' ),
				'pro'       => shopglut_is_pro() ? '' : self::url( 'shopglut-pro' ),
				'layout'    => self::url( 'shopglut-layout' ),
			),
			'modules'       => $modules,
			'module'        => '',
			'tab'           => isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification
		);

		$module = $this->page_module( $page );
		if ( $module ) {
			$data['module'] = $module->id();
		}

		if ( 'shopglut-layout' === $page ) {
			$layout = ShopGlut_Layouts::get( isset( $_GET['layout_id'] ) ? absint( $_GET['layout_id'] ) : 0 ); // phpcs:ignore WordPress.Security.NonceVerification
			if ( $layout ) {
				$mod            = $registry->get( $layout['module'] );
				$templates      = $mod ? $mod->get_templates() : array();
				$data['module'] = $layout['module'];
				$data['layout'] = array(
					'id'             => $layout['id'],
					'name'           => $layout['name'],
					'template'       => $layout['template'],
					'template_title' => isset( $templates[ $layout['template'] ] ) ? $templates[ $layout['template'] ]['title'] : $layout['template'],
					'status'         => $layout['status'],
					'priority'       => $layout['priority'],
					'preview_url'    => $mod ? (string) apply_filters( 'shopglut_layout_preview_url', '', $layout, $mod ) : '',
				);
			}
		}
		return $data;
	}

	/**
	 * Load app scripts as ES modules.
	 *
	 * @param string $tag    Script tag.
	 * @param string $handle Handle.
	 * @return string
	 */
	public function module_attribute( $tag, $handle ) {
		$handles = apply_filters( 'shopglut_module_script_handles', array( 'shopglut-dashboard', 'shopglut-modules', 'shopglut-layouts', 'shopglut-panel', 'shopglut-tools', 'shopglut-app' ) );
		if ( in_array( $handle, $handles, true ) && false === strpos( $tag, 'type="module"' ) ) {
			$tag = str_replace( '<script ', '<script type="module" ', $tag );
		}
		return $tag;
	}

	/* ── Page renderers ─────────────────────────────────────────────────── */

	/**
	 * Check the capability of the current page.
	 *
	 * @param string $context Capability context.
	 * @return void
	 */
	private function guard( $context ) {
		if ( ! current_user_can( self::cap( $context ) ) ) {
			wp_die( esc_html__( 'You are not allowed to view this page.', 'shopglut' ) );
		}
	}

	/**
	 * React page: root with a loading skeleton.
	 *
	 * @return void
	 */
	public function render_app() {
		$this->print_chrome_css();
		echo '<div id="shopglut-root" class="sg-root">';
		echo '<div class="sg-skel-header"></div><div class="sg-skel-body">';
		echo '<div class="sg-skel sg-skel-title"></div><div class="sg-skel sg-skel-sub"></div>';
		echo '<div class="sg-skel-grid">';
		for ( $i = 0; $i < 6; $i++ ) {
			echo '<div class="sg-skel sg-skel-card"></div>';
		}
		echo '</div></div></div>';
	}

	/**
	 * Module page: layouts list (React) or the module's Settings panel.
	 *
	 * @return void
	 */
	public function render_module() {
		$module = $this->page_module( $this->page() );
		if ( ! $module ) {
			wp_die( esc_html__( 'Unknown module.', 'shopglut' ) );
		}
		$this->guard( 'module:' . $module->id() );
		if ( $this->module_shows_layouts( $module ) ) {
			$this->render_app();
			return;
		}
		$this->render_panel_page( $this->current_panel() );
	}

	/**
	 * Global Settings page.
	 *
	 * @return void
	 */
	public function render_settings() {
		$this->guard( 'settings' );
		$this->render_panel_page( $this->current_panel() );
	}

	/**
	 * Layout editor page.
	 *
	 * @return void
	 */
	public function render_layout_editor() {
		$panel = $this->current_panel();
		if ( ! $panel instanceof ShopGlut_Layout_Panel ) {
			wp_die( esc_html__( 'This layout does not exist.', 'shopglut' ) );
		}
		$layout = ShopGlut_Layouts::get( $panel->layout_id );
		$this->guard( 'module:' . $layout['module'] );
		$this->render_panel_page( $panel );
	}

	/**
	 * Header root (React) and an AppGlut panel under it.
	 *
	 * @param ShopGlut_Options_Panel|null $panel Panel.
	 * @return void
	 */
	private function render_panel_page( $panel ) {
		$this->print_chrome_css();
		echo '<div class="sg-panel-page' . ( $panel instanceof ShopGlut_Layout_Panel ? ' is-layout' : '' ) . '">';
		echo '<div id="shopglut-root" class="sg-root"><div class="sg-skel-header"></div></div>';
		echo '<div class="sg-panel-wrap">';
		if ( $panel ) {
			$panel->render_page();
		} else {
			echo '<div class="notice notice-info inline"><p>' . esc_html__( 'Nothing to configure here.', 'shopglut' ) . '</p></div>';
		}
		echo '</div></div>';
	}

	/**
	 * Hide the WordPress menu, toolbar and notices on ShopGlut pages, and the skeleton styles.
	 *
	 * @return void
	 */
	private function print_chrome_css() {
		?>
		<style>
			#adminmenumain, #wpadminbar, #wpfooter, .update-nag, #wpbody-content > .notice, #wpbody-content > .error, #wpbody-content > .updated { display: none !important; }
			html.wp-toolbar { padding-top: 0 !important; }
			#wpcontent { margin-left: 0 !important; padding-left: 0 !important; }
			#wpbody-content { padding-bottom: 0 !important; }
			body.shopglut-admin-page { background: #f4f5fa; }
			.sg-skel-header { height: 60px; background: linear-gradient(135deg, #12101f 0%, #1e1638 55%, #2a1d52 100%); }
			.sg-skel-body { padding: 28px 28px 0; }
			.sg-skel { background: linear-gradient(90deg, #ececf3 25%, #e2e2ec 50%, #ececf3 75%); background-size: 200% 100%; animation: sg-shimmer 1.4s infinite; border-radius: 8px; }
			.sg-skel-title { width: 220px; height: 30px; margin-bottom: 10px; }
			.sg-skel-sub { width: 360px; height: 16px; margin-bottom: 28px; }
			.sg-skel-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 18px; }
			.sg-skel-card { height: 150px; border-radius: 14px; }
			@keyframes sg-shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
		</style>
		<?php
	}
}
