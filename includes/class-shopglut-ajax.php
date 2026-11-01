<?php
/**
 * Admin AJAX endpoints of the React app (shopglut_{action}, nonce shopglut_admin).
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Ajax class.
 */
class ShopGlut_Ajax {

	/**
	 * Single instance.
	 *
	 * @var ShopGlut_Ajax|null
	 */
	private static $instance = null;

	/**
	 * Action => capability context ('module' = the module named in the request).
	 *
	 * @var array
	 */
	private $actions = array(
		'get_dashboard'    => 'dashboard',
		'get_modules'      => 'modules',
		'toggle_module'    => 'modules',
		'get_layouts'      => 'module',
		'create_layout'    => 'module',
		'update_layout'    => 'module',
		'delete_layout'    => 'module',
		'duplicate_layout' => 'module',
		'bulk_layouts'     => 'module',
		'export_data'      => 'tools',
		'import_data'      => 'tools',
		'system_status'    => 'tools',
		'reset_data'       => 'tools',
	);

	/**
	 * Get singleton instance.
	 *
	 * @return ShopGlut_Ajax
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
		foreach ( array_keys( $this->actions ) as $action ) {
			add_action( 'wp_ajax_shopglut_' . $action, array( $this, 'dispatch' ) );
		}
	}

	/**
	 * Check nonce and capability, then run the handler.
	 *
	 * @return void
	 */
	public function dispatch() {
		$action = substr( current_action(), strlen( 'wp_ajax_shopglut_' ) );
		if ( ! check_ajax_referer( 'shopglut_admin', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Your session has expired. Reload the page.', 'shopglut' ) ), 403 );
		}

		$context = $this->actions[ $action ];
		if ( 'module' === $context ) {
			$module  = $this->request_module();
			$context = 'module:' . $module->id();
		}
		if ( ! current_user_can( ShopGlut_Admin::cap( $context ) ) ) {
			wp_send_json_error( array( 'message' => __( 'You are not allowed to do this.', 'shopglut' ) ), 403 );
		}

		try {
			wp_send_json_success( call_user_func( array( $this, $action ) ) );
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ), 400 );
		}
	}

	/**
	 * Request parameter.
	 *
	 * @param string $key     Key.
	 * @param mixed  $default Default.
	 * @return mixed Unslashed raw value.
	 */
	private function param( $key, $default = '' ) {
		// phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- Nonce checked in dispatch(); callers sanitize.
		return isset( $_REQUEST[ $key ] ) ? wp_unslash( $_REQUEST[ $key ] ) : $default;
	}

	/**
	 * Module named by `module`, or the module of the layout named by `id` / `ids`.
	 *
	 * @return ShopGlut_Module
	 */
	private function request_module() {
		$id = sanitize_key( $this->param( 'module' ) );
		if ( '' === $id ) {
			$layout_id = absint( $this->param( 'id' ) );
			if ( ! $layout_id ) {
				$ids       = array_filter( array_map( 'absint', explode( ',', (string) $this->param( 'ids' ) ) ) );
				$layout_id = $ids ? reset( $ids ) : 0;
			}
			$layout = $layout_id ? ShopGlut_Layouts::get( $layout_id ) : null;
			$id     = $layout ? $layout['module'] : '';
		}
		$module = ShopGlut_Modules::instance()->get( $id );
		if ( ! $module ) {
			wp_send_json_error( array( 'message' => __( 'Unknown module.', 'shopglut' ) ), 400 );
		}
		return $module;
	}

	/**
	 * A layout of the requested module, or an error.
	 *
	 * @param int $id Layout ID.
	 * @return array
	 */
	private function layout_or_fail( $id ) {
		$layout = ShopGlut_Layouts::get( $id );
		if ( ! $layout || $layout['module'] !== $this->request_module()->id() ) {
			wp_send_json_error( array( 'message' => __( 'This layout does not exist.', 'shopglut' ) ), 404 );
		}
		return $layout;
	}

	/**
	 * Layout row for the app.
	 *
	 * @param array           $row       Row.
	 * @param ShopGlut_Module $module    Module.
	 * @param array           $templates Module templates.
	 * @return array
	 */
	private function layout_to_array( $row, $module, $templates ) {
		$tpl = isset( $templates[ $row['template'] ] ) ? $templates[ $row['template'] ] : null;
		return array(
			'id'             => $row['id'],
			'name'           => $row['name'],
			'template'       => $row['template'],
			'template_title' => $tpl ? $tpl['title'] : $row['template'],
			'template_image' => $tpl ? $tpl['image'] : '',
			'locked'         => $tpl ? $tpl['locked'] : false,
			'status'         => $row['status'],
			'priority'       => $row['priority'],
			'updated_at'     => mysql2date( 'c', $row['updated_at'] . ' UTC' ),
			'edit_url'       => ShopGlut_Admin::url( 'shopglut-layout', array( 'layout_id' => $row['id'] ) ),
			'summary'        => (string) apply_filters( 'shopglut_layout_summary', '', $row, $module ),
		);
	}

	/* ── Dashboard & modules ───────────────────────────────────────────── */

	/**
	 * Dashboard data.
	 *
	 * @return array
	 */
	private function get_dashboard() {
		$registry = ShopGlut_Modules::instance();
		$modules  = array_values( array_map( array( $registry, 'to_array' ), $registry->all() ) );

		$enabled = count( array_filter( wp_list_pluck( $modules, 'enabled' ) ) );
		$layouts = array_sum( wp_list_pluck( $modules, 'layout_count' ) );
		$active  = array_sum( wp_list_pluck( $modules, 'active_count' ) );

		$products = wp_count_posts( 'product' );

		return array(
			'stats'   => array(
				'modules_enabled' => $enabled,
				'modules_total'   => count( $modules ),
				'layouts'         => $layouts,
				'layouts_active'  => $active,
				'products'        => $products ? (int) $products->publish : 0,
			),
			'modules' => $modules,
			'wc'      => array(
				'version'  => defined( 'WC_VERSION' ) ? WC_VERSION : '',
				'currency' => function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : '',
			),
		);
	}

	/**
	 * Modules list.
	 *
	 * @return array
	 */
	private function get_modules() {
		$registry = ShopGlut_Modules::instance();
		return array( 'modules' => array_values( array_map( array( $registry, 'to_array' ), $registry->all() ) ) );
	}

	/**
	 * Switch a module on or off.
	 *
	 * @return array
	 */
	private function toggle_module() {
		$registry = ShopGlut_Modules::instance();
		$id       = sanitize_key( $this->param( 'id' ) );
		$module   = $registry->get( $id );
		if ( ! $module ) {
			throw new Exception( esc_html( __( 'Unknown module.', 'shopglut' ) ) );
		}
		if ( ! $registry->set_enabled( $id, (bool) absint( $this->param( 'enabled' ) ) ) ) {
			throw new Exception( esc_html( __( 'This module needs ShopGlut Pro.', 'shopglut' ) ) );
		}
		return array( 'module' => $registry->to_array( $module ) );
	}

	/* ── Layouts ───────────────────────────────────────────────────────── */

	/**
	 * Layouts of a module, with its templates and status counts.
	 *
	 * @return array
	 */
	private function get_layouts() {
		$module    = $this->request_module();
		$templates = $module->get_templates();
		$result    = ShopGlut_Layouts::query(
			$module->id(),
			array(
				'status'   => sanitize_key( $this->param( 'status' ) ),
				'search'   => sanitize_text_field( $this->param( 'search' ) ),
				'orderby'  => sanitize_key( $this->param( 'orderby', 'id' ) ),
				'order'    => sanitize_key( $this->param( 'order', 'desc' ) ),
				'per_page' => absint( $this->param( 'per_page', 20 ) ),
				'page'     => absint( $this->param( 'page', 1 ) ),
			)
		);

		$items = array();
		foreach ( $result['items'] as $row ) {
			$items[] = $this->layout_to_array( $row, $module, $templates );
		}

		return array(
			'items'     => $items,
			'total'     => $result['total'],
			'counts'    => array(
				'all'    => ShopGlut_Layouts::count( $module->id() ),
				'active' => ShopGlut_Layouts::count( $module->id(), 'active' ),
				'draft'  => ShopGlut_Layouts::count( $module->id(), 'draft' ),
			),
			'templates' => array_values( $templates ),
			'module'    => ShopGlut_Modules::instance()->to_array( $module ),
		);
	}

	/**
	 * New layout from a template.
	 *
	 * @return array
	 */
	private function create_layout() {
		$module    = $this->request_module();
		$templates = $module->get_templates();
		$template  = shopglut_sanitize_template( $this->param( 'template' ) );

		if ( $templates && ! isset( $templates[ $template ] ) ) {
			throw new Exception( esc_html( __( 'Unknown template.', 'shopglut' ) ) );
		}
		if ( $templates && $templates[ $template ]['locked'] ) {
			throw new Exception( esc_html( __( 'This template needs ShopGlut Pro.', 'shopglut' ) ) );
		}

		$name = sanitize_text_field( $this->param( 'name' ) );
		if ( '' === $name ) {
			$info = $module->get_info();
			/* translators: 1: layout label, 2: number */
			$name = sprintf( __( '%1$s #%2$d', 'shopglut' ), $info['layout_label'], ShopGlut_Layouts::count( $module->id() ) + 1 );
		}

		$id = ShopGlut_Layouts::create(
			$module->id(),
			array(
				'name'     => $name,
				'template' => $template ? $template : 'template1',
				'status'   => 'draft',
				'settings' => (array) apply_filters( 'shopglut_new_layout_settings', array(), $module, $template ),
			)
		);
		if ( ! $id ) {
			throw new Exception( esc_html( __( 'Could not create the layout.', 'shopglut' ) ) );
		}
		return array(
			'id'       => $id,
			'edit_url' => ShopGlut_Admin::url( 'shopglut-layout', array( 'layout_id' => $id ) ),
		);
	}

	/**
	 * Rename, activate / deactivate or re-prioritise a layout.
	 *
	 * @return array
	 */
	private function update_layout() {
		$module = $this->request_module();
		$layout = $this->layout_or_fail( absint( $this->param( 'id' ) ) );
		$data   = array();

		if ( '' !== $this->param( 'name' ) ) {
			$data['name'] = sanitize_text_field( $this->param( 'name' ) );
		}
		if ( '' !== $this->param( 'status' ) ) {
			$data['status'] = 'active' === $this->param( 'status' ) ? 'active' : 'draft';
			$templates      = $module->get_templates();
			if ( 'active' === $data['status'] && isset( $templates[ $layout['template'] ] ) && $templates[ $layout['template'] ]['locked'] ) {
				throw new Exception( esc_html( __( 'This layout uses a Pro template. Activate ShopGlut Pro to use it.', 'shopglut' ) ) );
			}
		}
		if ( '' !== $this->param( 'priority' ) ) {
			$data['priority'] = (int) $this->param( 'priority' );
		}
		ShopGlut_Layouts::update( $layout['id'], $data );

		return array( 'layout' => $this->layout_to_array( ShopGlut_Layouts::get( $layout['id'] ), $module, $module->get_templates() ) );
	}

	/**
	 * Delete a layout.
	 *
	 * @return array
	 */
	private function delete_layout() {
		$layout = $this->layout_or_fail( absint( $this->param( 'id' ) ) );
		ShopGlut_Layouts::delete( $layout['id'] );
		return array( 'deleted' => $layout['id'] );
	}

	/**
	 * Copy a layout.
	 *
	 * @return array
	 */
	private function duplicate_layout() {
		$layout = $this->layout_or_fail( absint( $this->param( 'id' ) ) );
		return array( 'id' => ShopGlut_Layouts::duplicate( $layout['id'] ) );
	}

	/**
	 * Bulk activate / deactivate / delete.
	 *
	 * @return array
	 */
	private function bulk_layouts() {
		$module = $this->request_module();
		$action = sanitize_key( $this->param( 'bulk' ) );
		$ids    = array_filter( array_map( 'absint', explode( ',', (string) $this->param( 'ids' ) ) ) );
		$done   = 0;

		foreach ( $ids as $id ) {
			$layout = ShopGlut_Layouts::get( $id );
			if ( ! $layout || $layout['module'] !== $module->id() ) {
				continue;
			}
			if ( 'delete' === $action ) {
				ShopGlut_Layouts::delete( $id );
			} elseif ( in_array( $action, array( 'active', 'draft' ), true ) ) {
				ShopGlut_Layouts::update( $id, array( 'status' => $action ) );
			} else {
				continue;
			}
			++$done;
		}
		return array( 'done' => $done );
	}

	/* ── Tools ─────────────────────────────────────────────────────────── */

	/**
	 * Option names that hold ShopGlut settings.
	 *
	 * @return array
	 */
	private function setting_options() {
		$options = array( ShopGlut_Modules::OPTION, ShopGlut_Settings::OPTION );
		foreach ( array_keys( ShopGlut_Modules::instance()->all() ) as $id ) {
			$options[] = ShopGlut_Panels::module_option( $id );
		}
		return $options;
	}

	/**
	 * Export settings and / or layouts as JSON.
	 *
	 * @return array
	 */
	private function export_data() {
		$what = array_filter( explode( ',', sanitize_text_field( $this->param( 'what', 'settings,layouts' ) ) ) );
		$out  = array(
			'plugin'   => 'shopglut',
			'version'  => SHOPGLUT_VERSION,
			'exported' => gmdate( 'c' ),
		);
		if ( in_array( 'settings', $what, true ) ) {
			$out['settings'] = array();
			foreach ( $this->setting_options() as $option ) {
				$value = get_option( $option, null );
				if ( null !== $value ) {
					$out['settings'][ $option ] = $value;
				}
			}
		}
		if ( in_array( 'layouts', $what, true ) ) {
			$out['layouts'] = array();
			foreach ( array_keys( ShopGlut_Modules::instance()->all() ) as $id ) {
				foreach ( ShopGlut_Layouts::query( $id, array( 'per_page' => 200 ) )['items'] as $row ) {
					$out['layouts'][] = array_intersect_key( $row, array_flip( array( 'module', 'name', 'template', 'status', 'priority', 'settings' ) ) );
				}
			}
		}
		return array(
			'filename' => 'shopglut-export-' . gmdate( 'Y-m-d' ) . '.json',
			'data'     => $out,
		);
	}

	/**
	 * Import a JSON export. Layouts are added as drafts; settings replace the current ones.
	 *
	 * @return array
	 */
	private function import_data() {
		$data = json_decode( (string) $this->param( 'data' ), true );
		if ( ! is_array( $data ) || ( $data['plugin'] ?? '' ) !== 'shopglut' ) {
			throw new Exception( esc_html( __( 'This is not a ShopGlut export file.', 'shopglut' ) ) );
		}

		$settings = 0;
		$allowed  = $this->setting_options();
		foreach ( (array) ( $data['settings'] ?? array() ) as $option => $value ) {
			if ( in_array( $option, $allowed, true ) && is_array( $value ) ) {
				update_option( $option, AppGlut::sanitize_deep( $value ) );
				++$settings;
			}
		}

		$layouts  = 0;
		$registry = ShopGlut_Modules::instance();
		foreach ( (array) ( $data['layouts'] ?? array() ) as $row ) {
			if ( ! is_array( $row ) || ! $registry->get( (string) ( $row['module'] ?? '' ) ) ) {
				continue;
			}
			$row['status']   = 'draft';
			$row['settings'] = AppGlut::sanitize_deep( (array) ( $row['settings'] ?? array() ) );
			if ( ShopGlut_Layouts::create( $row['module'], $row ) ) {
				++$layouts;
			}
		}

		return array(
			'settings' => $settings,
			'layouts'  => $layouts,
		);
	}

	/**
	 * Environment report.
	 *
	 * @return array
	 */
	private function system_status() {
		global $wpdb;
		$table  = ShopGlut_DB::layouts_table();
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table; // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		$theme = wp_get_theme();
		return array(
			'rows' => array(
				array( 'label' => 'ShopGlut', 'value' => SHOPGLUT_VERSION ),
				array( 'label' => 'ShopGlut Pro', 'value' => defined( 'SHOPGLUT_PRO_VERSION' ) ? SHOPGLUT_PRO_VERSION : __( 'Not active', 'shopglut' ) ),
				array( 'label' => 'AppGlut Framework', 'value' => defined( 'APPGLUT_VERSION' ) ? APPGLUT_VERSION : '' ),
				array( 'label' => 'WooCommerce', 'value' => defined( 'WC_VERSION' ) ? WC_VERSION : __( 'Not active', 'shopglut' ), 'ok' => defined( 'WC_VERSION' ) ),
				array( 'label' => 'WordPress', 'value' => get_bloginfo( 'version' ) ),
				array( 'label' => 'PHP', 'value' => PHP_VERSION, 'ok' => version_compare( PHP_VERSION, '7.4', '>=' ) ),
				array( 'label' => 'MySQL', 'value' => $wpdb->db_version() ),
				array( 'label' => __( 'Theme', 'shopglut' ), 'value' => $theme->get( 'Name' ) . ' ' . $theme->get( 'Version' ) ),
				array( 'label' => __( 'Layouts table', 'shopglut' ), 'value' => $exists ? __( 'OK', 'shopglut' ) : __( 'Missing', 'shopglut' ), 'ok' => $exists ),
				array( 'label' => __( 'Database version', 'shopglut' ), 'value' => (string) get_option( 'shopglut_db_version' ) ),
				array( 'label' => __( 'Memory limit', 'shopglut' ), 'value' => WP_MEMORY_LIMIT ),
				array( 'label' => __( 'Debug mode', 'shopglut' ), 'value' => WP_DEBUG ? __( 'On', 'shopglut' ) : __( 'Off', 'shopglut' ) ),
			),
		);
	}

	/**
	 * Reset settings and / or delete all layouts.
	 *
	 * @return array
	 */
	private function reset_data() {
		$what = sanitize_key( $this->param( 'what' ) );
		if ( 'settings' === $what ) {
			foreach ( $this->setting_options() as $option ) {
				delete_option( $option );
			}
		} elseif ( 'layouts' === $what ) {
			global $wpdb;
			$wpdb->query( 'TRUNCATE TABLE ' . ShopGlut_DB::layouts_table() ); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared
			function_exists( 'wp_cache_flush_group' ) && function_exists( 'wp_cache_supports' ) && wp_cache_supports( 'flush_group' ) ? wp_cache_flush_group( ShopGlut_Layouts::CACHE ) : wp_cache_flush();
		} else {
			throw new Exception( esc_html( __( 'Nothing to reset.', 'shopglut' ) ) );
		}
		return array( 'reset' => $what );
	}
}
