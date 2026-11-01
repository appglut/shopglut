<?php
/**
 * AppGlut panels inside the ShopGlut admin.
 *
 * ShopGlut draws its own pages (React header + menu), so the panels do not register menu pages:
 * ShopGlut_Admin prints them with render_page() under the ShopGlut header. Saving goes through the
 * framework's own AJAX endpoints (appglut_save …), which find the panel by its unique ID.
 *
 * - Module settings: one panel per module, stored in the option shopglut_mod_{module}.
 * - Layout editor: one panel per layout (shopglut_layout_{id}), stored in the layouts table.
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * Options panel without its own menu page.
 */
class ShopGlut_Options_Panel extends AppGlut_Options {

	/**
	 * No menu page or enqueue hook: ShopGlut_Admin draws and enqueues the panel.
	 *
	 * @return void
	 */
	protected function setup() {}

	/**
	 * Load the framework assets for this panel's fields.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		AppGlut::enqueue( $this->fields() );
	}
}

/**
 * Options of one layout, stored in its row of the layouts table.
 */
class ShopGlut_Layout_Panel extends ShopGlut_Options_Panel {

	/**
	 * Layout ID.
	 *
	 * @var int
	 */
	public $layout_id = 0;

	/**
	 * Stored settings.
	 *
	 * @param mixed $default Fallback when the layout has no settings yet.
	 * @return mixed
	 */
	public function read( $default = array() ) {
		$layout = ShopGlut_Layouts::get( $this->layout_id );
		return ( $layout && $layout['settings'] ) ? $layout['settings'] : $default;
	}

	/**
	 * Store settings.
	 *
	 * @param array $values Clean values.
	 * @return void
	 */
	public function write( $values ) {
		ShopGlut_Layouts::update( $this->layout_id, array( 'settings' => $values ) );
	}
}

/**
 * ShopGlut_Panels class.
 */
class ShopGlut_Panels {

	/**
	 * Single instance.
	 *
	 * @var ShopGlut_Panels|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return ShopGlut_Panels
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
		// Module panels are needed on the front end too: appglut_get_option() merges their defaults.
		add_action( 'init', array( $this, 'register_module_panels' ), 20 );
		add_action( 'admin_init', array( $this, 'maybe_register_layout_panel' ), 5 );
	}

	/**
	 * Option name (and panel ID) of a module's settings.
	 *
	 * @param string $module Module ID.
	 * @return string
	 */
	public static function module_option( $module ) {
		return 'shopglut_mod_' . str_replace( '-', '_', sanitize_key( $module ) );
	}

	/**
	 * Panel ID of a layout.
	 *
	 * @param int $layout_id Layout ID.
	 * @return string
	 */
	public static function layout_unique( $layout_id ) {
		return 'shopglut_layout_' . absint( $layout_id );
	}

	/**
	 * Shared panel arguments.
	 *
	 * @param array $args Overrides.
	 * @return array
	 */
	public static function panel_args( $args = array() ) {
		return wp_parse_args(
			$args,
			array(
				'menu_capability'    => ShopGlut_Admin::cap( 'settings' ),
				'framework_title'    => __( 'Settings', 'shopglut' ),
				'theme'              => 'light',
				'accent'             => '#7c3aed',
				'logo_icon'          => 'dashicons-admin-generic',
				'show_theme_toggle'  => false,
				'show_reset_all'     => true,
				'show_reset_section' => true,
				'save_defaults'      => false,
				'footer_text'        => ' ',
				'class'              => 'sg-panel',
			)
		);
	}

	/**
	 * Register the Settings panel of every enabled module that has one.
	 *
	 * @return void
	 */
	public function register_module_panels() {
		$registry = ShopGlut_Modules::instance();
		foreach ( $registry->all() as $id => $module ) {
			if ( ! $registry->is_enabled( $id ) ) {
				continue;
			}
			$sections = $module->settings_sections();
			if ( ! $sections ) {
				continue;
			}
			$unique = self::module_option( $id );
			$info   = $module->get_info();
			/**
			 * Sections of a module's Settings panel. ShopGlut Pro adds its options here.
			 *
			 * @param array           $sections AppGlut sections.
			 * @param ShopGlut_Module $module   Module.
			 */
			$sections = apply_filters( 'shopglut_module_settings_sections', $sections, $module );
			$sections = apply_filters( 'shopglut_' . $id . '_settings_sections', $sections, $module );

			AppGlut::$containers[ $unique ] = new ShopGlut_Options_Panel(
				$unique,
				self::panel_args(
					array(
						/* translators: %s: module name */
						'framework_title' => sprintf( __( '%s Settings', 'shopglut' ), $info['title'] ),
						'sections'        => $sections,
						'menu_capability' => ShopGlut_Admin::cap( 'module:' . $id ),
					)
				)
			);
		}
	}

	/**
	 * Register the panel of the layout being edited (editor page) or saved (AppGlut AJAX).
	 *
	 * @return void
	 */
	public function maybe_register_layout_panel() {
		// phpcs:disable WordPress.Security.NonceVerification -- Only picks which panel to build; AppGlut checks the nonce on save.
		$layout_id = 0;
		if ( isset( $_GET['page'], $_GET['layout_id'] ) && 'shopglut-layout' === $_GET['page'] ) {
			$layout_id = absint( $_GET['layout_id'] );
		} elseif ( wp_doing_ajax() && isset( $_REQUEST['unique'] ) && preg_match( '/^shopglut_layout_(\d+)$/', sanitize_key( wp_unslash( $_REQUEST['unique'] ) ), $m ) ) {
			$layout_id = absint( $m[1] );
		}
		// phpcs:enable
		if ( $layout_id ) {
			self::layout_panel( $layout_id );
		}
	}

	/**
	 * Build (once) the panel of a layout.
	 *
	 * @param int $layout_id Layout ID.
	 * @return ShopGlut_Layout_Panel|null
	 */
	public static function layout_panel( $layout_id ) {
		$unique = self::layout_unique( $layout_id );
		if ( isset( AppGlut::$containers[ $unique ] ) ) {
			return AppGlut::$containers[ $unique ];
		}

		$layout = ShopGlut_Layouts::get( $layout_id );
		$module = $layout ? ShopGlut_Modules::instance()->get( $layout['module'] ) : null;
		if ( ! $module ) {
			return null;
		}

		$sections = $module->layout_sections( $layout['template'], $layout );
		$display  = $module->display_fields();
		if ( $display ) {
			$sections[] = array(
				'id'     => 'display',
				'title'  => __( 'Display Rules', 'shopglut' ),
				'icon'   => 'dashicons-visibility',
				'fields' => array(
					array(
						'id'     => 'display',
						'type'   => 'fieldset',
						'fields' => $display,
					),
				),
			);
		}

		/**
		 * Sections of the layout editor.
		 *
		 * @param array           $sections AppGlut sections.
		 * @param array           $layout   Layout row.
		 * @param ShopGlut_Module $module   Module.
		 */
		$sections = apply_filters( 'shopglut_layout_sections', $sections, $layout, $module );

		if ( ! $sections ) {
			$sections = array(
				array(
					'id'     => 'general',
					'title'  => __( 'General', 'shopglut' ),
					'fields' => array(
						array(
							'type'    => 'notice',
							'style'   => 'info',
							'content' => __( 'This template has no options yet.', 'shopglut' ),
						),
					),
				),
			);
		}

		$panel            = new ShopGlut_Layout_Panel(
			$unique,
			self::panel_args(
				array(
					'framework_title' => $layout['name'],
					'sections'        => $sections,
					'menu_capability' => ShopGlut_Admin::cap( 'module:' . $layout['module'] ),
				)
			)
		);
		$panel->layout_id = (int) $layout_id;

		AppGlut::$containers[ $unique ] = $panel;
		return $panel;
	}
}
