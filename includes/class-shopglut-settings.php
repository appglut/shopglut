<?php
/**
 * Global Settings panel (options shared by every module).
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Settings class.
 */
class ShopGlut_Settings {

	/** Option name and panel ID. */
	const OPTION = 'shopglut_settings';

	/**
	 * Single instance.
	 *
	 * @var ShopGlut_Settings|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return ShopGlut_Settings
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
		add_action( 'init', array( $this, 'register' ), 20 );
	}

	/**
	 * Register the panel.
	 *
	 * @return void
	 */
	public function register() {
		/**
		 * Sections of the Global Settings panel.
		 *
		 * @param array $sections AppGlut sections.
		 */
		$sections = apply_filters( 'shopglut_global_settings_sections', $this->sections() );

		AppGlut::$containers[ self::OPTION ] = new ShopGlut_Options_Panel(
			self::OPTION,
			ShopGlut_Panels::panel_args(
				array(
					'framework_title' => __( 'Global Settings', 'shopglut' ),
					'sections'        => $sections,
				)
			)
		);
	}

	/**
	 * Built-in sections.
	 *
	 * @return array
	 */
	private function sections() {
		return array(
			array(
				'id'     => 'general',
				'title'  => __( 'General', 'shopglut' ),
				'icon'   => 'dashicons-admin-generic',
				'fields' => array(
					array(
						'id'      => 'brand_color',
						'type'    => 'color',
						'title'   => __( 'Store accent color', 'shopglut' ),
						'desc'    => __( 'Default accent of buttons, badges and highlights in every module. Each layout can still set its own colors.', 'shopglut' ),
						'default' => '#7c3aed',
					),
					array(
						'id'      => 'load_assets',
						'type'    => 'button_set',
						'title'   => __( 'Load front-end assets', 'shopglut' ),
						'options' => array(
							'woo'    => __( 'WooCommerce pages only', 'shopglut' ),
							'always' => __( 'Every page', 'shopglut' ),
						),
						'default' => 'woo',
						'desc'    => __( 'Choose "Every page" when you show ShopGlut shortcodes outside WooCommerce pages.', 'shopglut' ),
					),
				),
			),
			array(
				'id'     => 'advanced',
				'title'  => __( 'Advanced', 'shopglut' ),
				'icon'   => 'dashicons-admin-tools',
				'fields' => array(
					array(
						'id'      => 'custom_css',
						'type'    => 'code_editor',
						'title'   => __( 'Custom CSS', 'shopglut' ),
						'mode'    => 'css',
						'default' => '',
					),
					array(
						'id'      => 'remove_data',
						'type'    => 'switcher',
						'title'   => __( 'Remove all data on uninstall', 'shopglut' ),
						'desc'    => __( 'Deletes layouts, settings and module tables when the plugin is deleted.', 'shopglut' ),
						'default' => false,
					),
				),
			),
		);
	}
}
