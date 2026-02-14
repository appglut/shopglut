<?php

namespace Shopglut;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DataManage {

	public function __construct() {
		// Register AJAX handlers
		add_action( 'wp_ajax_shopglut_delete_all_tables', array( $this, 'ajax_delete_all_tables' ) );
		add_action( 'wp_ajax_shopglut_delete_selected_tables', array( $this, 'ajax_delete_selected_tables' ) );
		add_action( 'wp_ajax_shopglut_reset_module_states', array( $this, 'ajax_reset_module_states' ) );
	}

	/**
	 * Get all ShopGlut table names with descriptions and module mapping
	 */
	public static function get_all_table_names() {
		global $wpdb;
		return array(
			'single_product' => array(
				'table' => $wpdb->prefix . 'shopglut_single_product_layout',
				'name' => 'Single Product Layouts',
				'description' => 'Single Product page builder layouts',
				'module' => 'single_product'
			),
			'cart_page' => array(
				'table' => $wpdb->prefix . 'shopglut_cartpage_layouts',
				'name' => 'Cart Page Layouts',
				'description' => 'Cart page builder layouts',
				'module' => 'cart_page'
			),
			'order_complete' => array(
				'table' => $wpdb->prefix . 'shopglut_ordercomplete_layouts',
				'name' => 'Order Complete Layouts',
				'description' => 'Order complete page layouts',
				'module' => 'orderComplete_page'
			),
			'account_page' => array(
				'table' => $wpdb->prefix . 'shopglut_accountpage_layouts',
				'name' => 'Account Page Layouts',
				'description' => 'My Account page layouts',
				'module' => 'account_page'
			),
			'shop_layouts' => array(
				'table' => $wpdb->prefix . 'shopglut_shop_layouts',
				'name' => 'Shop Page Layouts',
				'description' => 'Shop page builder layouts',
				'module' => 'shop_layouts'
			),
			'archive_layouts' => array(
				'table' => $wpdb->prefix . 'shopglut_archive_layouts',
				'name' => 'Archive Layouts',
				'description' => 'Archive page layouts',
				'module' => 'archive_layouts'
			),
			'product_swatches' => array(
				'table' => $wpdb->prefix . 'shopglut_productswatches_layout',
				'name' => 'Product Swatches',
				'description' => 'Product swatches configurations',
				'module' => 'product_swatches'
			),
			'product_badges' => array(
				'table' => $wpdb->prefix . 'shopglut_product_badge_layouts',
				'name' => 'Product Badges',
				'description' => 'Product badge layouts',
				'module' => 'product_badges'
			),
			'wishlist' => array(
				'table' => $wpdb->prefix . 'shopglut_wishlist',
				'name' => 'Wishlist Data',
				'description' => 'User wishlist items and settings',
				'module' => 'wishlist'
			),
			'shop_filters' => array(
				'table' => $wpdb->prefix . 'shopglut_enhancement_filters',
				'name' => 'Shop Filters',
				'description' => 'Shop filter configurations',
				'module' => 'shop_filters'
			),
			'woo_templates' => array(
				'table' => $wpdb->prefix . 'shopglut_woo_templates',
				'name' => 'Woo Templates',
				'description' => 'Custom WooCommerce templates',
				'module' => 'woo_templates'
			),
			'custom_fields' => array(
				'table' => $wpdb->prefix . 'shopglut_product_custom_field_settings',
				'name' => 'Custom Fields',
				'description' => 'Product custom field settings',
				'module' => 'acf_fields'
			),
			'comparison' => array(
				'table' => $wpdb->prefix . 'shopglut_comparison_layouts',
				'name' => 'Product Comparison',
				'description' => 'Product comparison layouts',
				'module' => 'product_comparison'
			),
			'comparison_data' => array(
				'table' => $wpdb->prefix . 'shopglut_product_comparisons',
				'name' => 'Comparison Data',
				'description' => 'Product comparison settings',
				'module' => 'product_comparison'
			),
			'quickview' => array(
				'table' => $wpdb->prefix . 'shopglut_quickview_layouts',
				'name' => 'Quick View',
				'description' => 'Quick view layouts',
				'module' => 'quick_views'
			),
			'quickview_data' => array(
				'table' => $wpdb->prefix . 'shopglut_product_quickview',
				'name' => 'Quick View Data',
				'description' => 'Quick view configurations',
				'module' => 'quick_views'
			),
			'shortcodes' => array(
				'table' => $wpdb->prefix . 'shopglut_shortcodes_showcase',
				'name' => 'Shortcode Showcase',
				'description' => 'Shortcode configurations',
				'module' => 'shortcode_showcase'
			),
			'mega_menu' => array(
				'table' => $wpdb->prefix . 'shopglut_mega_menus',
				'name' => 'Mega Menu',
				'description' => 'Mega menu configurations',
				'module' => 'mega_menu'
			),
			'user_actions' => array(
				'table' => $wpdb->prefix . 'shopglut_user_actions',
				'name' => 'User Actions',
				'description' => 'User action tracking data',
				'module' => 'core'
			),
		);
	}

	/**
	 * Delete selected database tables and disable corresponding modules
	 */
	public static function delete_selected_tables( $selected_tables ) {
		global $wpdb;

		$all_tables = self::get_all_table_names();
		$deleted = array();
		$errors = array();
		$not_found = array();
		$modules_disabled = array();

		// Get current list of intentionally deleted tables
		$deleted_tables_list = get_option( 'shopglut_intentionally_deleted_tables', array() );

		foreach ( $selected_tables as $table_key ) {
			if ( ! isset( $all_tables[ $table_key ] ) ) {
				$errors[] = "Invalid table key: {$table_key}";
				continue;
			}

			$table_name = $all_tables[ $table_key ]['table'];
			$module_key = $all_tables[ $table_key ]['module'];

			// Check if table exists
			$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) === $table_name;

			if ( ! $table_exists ) {
				$not_found[] = $table_name;
			}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- DROP TABLE requires direct query
			$result = $wpdb->query( "DROP TABLE IF EXISTS `{$table_name}`" );

			if ( $result === false ) {
				$errors[] = $table_name;
			} else {
				$deleted[] = array(
					'key' => $table_key,
					'table' => $table_name,
					'name' => $all_tables[ $table_key ]['name']
				);

				// Mark this table as intentionally deleted (prevent recreation)
				if ( ! in_array( $table_key, $deleted_tables_list, true ) ) {
					$deleted_tables_list[] = $table_key;
				}

				// Disable the corresponding module
				$option_name = 'shopglut_module_' . $module_key . '_enabled';
				if ( delete_option( $option_name ) ) {
					$modules_disabled[] = $module_key;
				}
			}
		}

		// Update the intentionally deleted tables list
		update_option( 'shopglut_intentionally_deleted_tables', $deleted_tables_list );

		return array(
			'deleted' => $deleted,
			'errors' => $errors,
			'not_found' => $not_found,
			'deleted_count' => count( $deleted ),
			'error_count' => count( $errors ),
			'modules_disabled' => $modules_disabled,
			'modules_disabled_count' => count( $modules_disabled )
		);
	}

	/**
	 * Delete all ShopGlut database tables
	 */
	public static function delete_all_tables() {
		$all_tables = self::get_all_table_names();
		return self::delete_selected_tables( array_keys( $all_tables ) );
	}

	/**
	 * Reset all module states to disabled and truncate table data
	 */
	public static function reset_module_states() {
		global $wpdb;

		$module_manager = \Shopglut\ModuleManager::get_instance();
		$modules = $module_manager->get_modules();

		$reset_count = 0;
		foreach ( array_keys( $modules ) as $module_key ) {
			$option_name = 'shopglut_module_' . $module_key . '_enabled';
			if ( delete_option( $option_name ) ) {
				$reset_count++;
			}
		}

		// Clear the intentionally deleted tables list (allow tables to be recreated)
		delete_option( 'shopglut_intentionally_deleted_tables' );

		// Truncate all ShopGlut tables (delete data but keep tables)
		$all_tables = self::get_all_table_names();
		$truncated = array();
		$truncate_errors = array();

		foreach ( $all_tables as $table_key => $table_info ) {
			$table_name = $table_info['table'];

			// Check if table exists
			$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) === $table_name;

			if ( $table_exists ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- TRUNCATE requires direct query
				$result = $wpdb->query( "TRUNCATE TABLE `{$table_name}`" );

				if ( $result === false ) {
					$truncate_errors[] = $table_name;
				} else {
					$truncated[] = $table_name;
				}
			}
		}

		return array(
			'reset_count' => $reset_count,
			'truncated_count' => count( $truncated ),
			'truncated' => $truncated,
			'truncate_errors' => $truncate_errors,
			'message' => sprintf(
				/* translators: %1$d is the number of modules reset, %2$d is the number of tables truncated */
				__( 'Successfully reset %1$d module states and cleared data from %2$d tables.', 'shopglut' ),
				$reset_count,
				count( $truncated )
			)
		);
	}

	/**
	 * AJAX handler for deleting selected tables
	 */
	public function ajax_delete_selected_tables() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'shopglut_data_management' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'shopglut' ) ) );
			return;
		}

		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'shopglut' ) ) );
			return;
		}

		// Get selected tables
		$selected_tables = isset( $_POST['tables'] ) && is_array( $_POST['tables'] )
			? array_map( 'sanitize_text_field', wp_unslash( $_POST['tables'] ) )
			: array();

		if ( empty( $selected_tables ) ) {
			wp_send_json_error( array( 'message' => __( 'No tables selected for deletion.', 'shopglut' ) ) );
			return;
		}

		$result = self::delete_selected_tables( $selected_tables );

		if ( $result['deleted_count'] > 0 ) {
			$deleted_names = wp_list_pluck( $result['deleted'], 'name' );

			// Build message
			$message = sprintf(
				/* translators: %1$d is count, %2$s is list of tables, %3$d is modules disabled count */
				__( 'Deleted %1$d tables: %2$s. Disabled %3$d corresponding modules.', 'shopglut' ),
				$result['deleted_count'],
				implode( ', ', $deleted_names ),
				$result['modules_disabled_count']
			);

			if ( $result['error_count'] > 0 ) {
				$message .= ' ' . sprintf(
					/* translators: %d is error count */
					__( 'Failed to delete %d tables.', 'shopglut' ),
					$result['error_count']
				);
			}

			wp_send_json_success( array(
				'message' => $message,
				'details' => $result
			) );
		} else {
			wp_send_json_error( array(
				'message' => __( 'No tables were deleted. They may not exist or an error occurred.', 'shopglut' ),
				'details' => $result
			) );
		}
	}

	/**
	 * AJAX handler for deleting all tables
	 */
	public function ajax_delete_all_tables() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'shopglut_data_management' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'shopglut' ) ) );
			return;
		}

		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'shopglut' ) ) );
			return;
		}

		$result = self::delete_all_tables();

		// Build message
		$message = sprintf(
			/* translators: %1$d is deleted count, %2$d is modules disabled count */
			__( 'Deleted %1$d tables and disabled %2$d corresponding modules.', 'shopglut' ),
			$result['deleted_count'],
			$result['modules_disabled_count']
		);

		if ( $result['error_count'] > 0 ) {
			$message .= ' ' . sprintf(
				/* translators: %d is error count */
				__( 'Failed to delete %d tables.', 'shopglut' ),
				$result['error_count']
			);
			wp_send_json_error( array(
				'message' => $message,
				'details' => $result
			) );
		} else {
			wp_send_json_success( array(
				'message' => $message,
				'details' => $result
			) );
		}
	}

	/**
	 * AJAX handler for resetting module states
	 */
	public function ajax_reset_module_states() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'shopglut_data_management' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'shopglut' ) ) );
			return;
		}

		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'shopglut' ) ) );
			return;
		}

		$result = self::reset_module_states();

		wp_send_json_success( $result );
	}

	public static function get_instance() {
		static $instance;
		if ( is_null( $instance ) ) {
			$instance = new self();
		}
		return $instance;
	}
}
