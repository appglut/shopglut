<?php

namespace Shopglut;

class ShopGlutDatabase {
	
	private static $initialized = false;

	public static function table_showcase_filters() {
		global $wpdb;
		return $wpdb->prefix . 'shopglut_enhancement_filters';
	}

	public static function table_user_actions() {
		global $wpdb;
		return $wpdb->prefix . 'shopglut_user_actions';
	}

	public static function table_shop_layouts() {
		global $wpdb;
		return $wpdb->prefix . 'shopglut_shop_layouts';
	}

	public static function table_archive_layouts() {
		global $wpdb;
		return $wpdb->prefix . 'shopglut_archive_layouts';
	}

	public static function table_shopg_wishlist() {
		global $wpdb;
		return $wpdb->prefix . 'shopglut_wishlist';
	}

	public static function table_single_layouts() {
		global $wpdb;
		return $wpdb->prefix . 'shopglut_single_product_layout';
	}

	public static function table_cartpage_layouts() {
		global $wpdb;
		return $wpdb->prefix . 'shopglut_cartpage_layouts';
	}

	public static function table_ordercomplete_layouts() {
		global $wpdb;
		return $wpdb->prefix . 'shopglut_ordercomplete_layouts';
	}

	public static function table_accountpage_layouts() {
		global $wpdb;
		return $wpdb->prefix . 'shopglut_accountpage_layouts';
	}

	public static function table_shortcodes_showcase() {
		global $wpdb;
		return $wpdb->prefix . 'shopglut_shortcodes_showcase';
	}

	public static function table_gallery_shortcode() {
		global $wpdb;
		return $wpdb->prefix . 'shopglut_gallery_shortcode';
	}

	public static function table_tabs_showcase() {
		global $wpdb;
		return $wpdb->prefix . 'shopglut_tabs_showcase';
	}

	public static function table_badges_showcase() {
		global $wpdb;
		return $wpdb->prefix . 'shopglut_badges_showcase';
	}
    
	public static function table_banners_showcase() {
		global $wpdb;
		return $wpdb->prefix . 'shopglut_banners_showcase';
	}

	public static function table_shop_banner_layouts() {
		global $wpdb;
		return $wpdb->prefix . 'shopglut_shopbanner_layouts';
	}

	public static function table_slider_layouts() {
		global $wpdb;
		return $wpdb->prefix . 'shopglut_slider_layouts';
	}

	public static function table_tab_layouts() {
		global $wpdb;
		return $wpdb->prefix . 'shopglut_tab_layouts';
	}

	public static function table_accordion_layouts() {
		global $wpdb;
		return $wpdb->prefix . 'shopglut_accordion_layouts';
	}

	public static function table_gallery_layouts() {
		global $wpdb;
		return $wpdb->prefix . 'shopglut_gallery_layouts';
	}

	public static function table_mega_menu_showcase() {
		global $wpdb;
		return $wpdb->prefix . 'shopglut_mega_menus';
	}

	public static function table_quickview_enhancement() {
		global $wpdb;
		return $wpdb->prefix . 'shopglut_quickview_layouts';
	}

	public static function table_comparison_enhancement() {
		global $wpdb;
		return $wpdb->prefix . 'shopglut_comparison_layouts';
	}

	public static function table_lock_settings() {
		global $wpdb;
		return $wpdb->prefix . 'shopglut_lock_settings';
	}

	public static function table_woo_templates() {
		global $wpdb;
		return $wpdb->prefix . 'shopglut_woo_templates';
	}

	public static function table_product_custom_field_settings() {
		global $wpdb;
		return $wpdb->prefix . 'shopglut_product_custom_field_settings';
	}

	public static function table_product_badges() {
		global $wpdb;
		return $wpdb->prefix . 'shopglut_product_badge_layouts';
	}

	public static function table_product_swatches() {
		global $wpdb;
		return $wpdb->prefix . 'shopglut_productswatches_layout';
	}


	public static function table_showcase_filters1() {
		global $wpdb;
		return $wpdb->prefix . 'shopglut_enhancements_filters';
	}


	/**
	 * Check if table exists
	 */
	private static function table_exists( $table_name ) {
		global $wpdb;

		// Check cache first
		$cache_key = 'shopglut_table_exists_' . md5( $table_name );
		$cached_result = wp_cache_get( $cache_key, 'shopglut_db_schema' );

		if ( false !== $cached_result ) {
			return (bool) $cached_result;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table existence check, safe table name from internal function
		$exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) === $table_name;

		// Cache for 1 hour (schema checks don't change frequently)
		wp_cache_set( $cache_key, $exists, 'shopglut_db_schema', HOUR_IN_SECONDS );

		return $exists;
	}

	/**
	 * Check if column exists in table
	 */
	private static function column_exists( $table_name, $column_name ) {
		global $wpdb;

		// Check cache first
		$cache_key = 'shopglut_column_exists_' . md5( $table_name . '_' . $column_name );
		$cached_result = wp_cache_get( $cache_key, 'shopglut_db_schema' );

		if ( false !== $cached_result ) {
			return (bool) $cached_result;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder -- Column existence check, safe table and column names from internal function
        $results = $wpdb->get_results( $wpdb->prepare( "SHOW COLUMNS FROM `%1s` LIKE %s", $table_name, $column_name ) );
		$exists = ! empty( $results );

		// Cache for 1 hour (schema checks don't change frequently)
		wp_cache_set( $cache_key, $exists, 'shopglut_db_schema', HOUR_IN_SECONDS );

		return $exists;
	}

	public static function create_user_actions() {
		global $wpdb;

		$table_name = self::table_user_actions();
		if ( self::table_exists( $table_name ) ) {
			return; // Table already exists
		}

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id mediumint(9) NOT NULL,
            product_id mediumint(9) NOT NULL,
            action_type varchar(255) NOT NULL,
            timestamp datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
         ) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- dbDelta for table creation, safe SQL with placeholders
		dbDelta( $sql );
	}

	public static function create_shop_layouts() {
		global $wpdb;

		$table_name = self::table_shop_layouts();
		if ( self::table_exists( $table_name ) ) {
			return; // Table already exists
		}

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            layout_name varchar(255) NOT NULL DEFAULT 'Layout One',
            layout_template varchar(255) NOT NULL DEFAULT 'template1',
            status varchar(50) NOT NULL DEFAULT 'not-active',
			layout_settings longtext,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- dbDelta for table creation, safe SQL with placeholders
		dbDelta( $sql );
	}

	public static function create_archive_layouts() {
		global $wpdb;

		$table_name = self::table_archive_layouts();
		if ( self::table_exists( $table_name ) ) {
			return; // Table already exists
		}

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            arlayout_name varchar(255) NOT NULL DEFAULT 'Layout One',
            arlayout_template varchar(255) NOT NULL DEFAULT 'template1',
			arlayout_settings longtext,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- dbDelta for table creation, safe SQL with placeholders
		dbDelta( $sql );
	}

	public static function create_single_layouts() {
		global $wpdb;

		$table_name = self::table_single_layouts();
		if ( self::table_exists( $table_name ) ) {
			return; // Table already exists
		}

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
             id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
             layout_name varchar(255) NOT NULL,
             layout_template varchar(255) NOT NULL,
             layout_settings text NOT NULL,
			 created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
             updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
             PRIMARY KEY (id)
        ) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- dbDelta for table creation, safe SQL with placeholders
		dbDelta( $sql );
	}

    public static function create_cartpage_layouts() {
		global $wpdb;

		$table_name = self::table_cartpage_layouts();
		if ( self::table_exists( $table_name ) ) {
			return; // Table already exists
		}

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
             id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
             layout_name varchar(255) NOT NULL,
             layout_template varchar(255) NOT NULL,
             layout_settings text NOT NULL,
			 created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
             updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
             PRIMARY KEY (id)
        ) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- dbDelta for table creation, safe SQL with placeholders
		dbDelta( $sql );
	}

	 public static function create_ordercomplete_layouts() {
		global $wpdb;

		$table_name = self::table_ordercomplete_layouts();
		if ( self::table_exists( $table_name ) ) {
			return; // Table already exists
		}

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
             id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
             layout_name varchar(255) NOT NULL,
             layout_template varchar(255) NOT NULL,
             layout_settings text NOT NULL,
			 created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
             updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
             PRIMARY KEY (id)
        ) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- dbDelta for table creation, safe SQL with placeholders
		dbDelta( $sql );
	}
	
	public static function create_accountpage_layouts() {
		global $wpdb;

		$table_name = self::table_accountpage_layouts();
		if ( self::table_exists( $table_name ) ) {
			return; // Table already exists
		}

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
             id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
             layout_name varchar(255) NOT NULL,
             layout_template varchar(255) NOT NULL,
             layout_settings text NOT NULL,
			 created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
             updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
             PRIMARY KEY (id)
        ) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- dbDelta for table creation, safe SQL with placeholders
		dbDelta( $sql );
	}

	public static function create_showcase_filters() {
		global $wpdb;

		$table_name = self::table_showcase_filters();
		if ( self::table_exists( $table_name ) ) {
			return;
		}

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            filter_name varchar(255) NOT NULL,
            filter_settings longtext,
            PRIMARY KEY  (id)
        ) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- dbDelta for table creation, safe SQL with placeholders
		dbDelta( $sql );

	}

	public static function create_showcase_badges() {
		global $wpdb;

		$table_name = self::table_badges_showcase();
		if ( self::table_exists( $table_name ) ) {
			return;
		}

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            badge_name varchar(255) NOT NULL,
            badge_settings longtext,
            PRIMARY KEY  (id)
        ) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- dbDelta for table creation, safe SQL with placeholders
		dbDelta( $sql );
	}

	public static function create_showcase_banners() {
		global $wpdb;

		$table_name = self::table_banners_showcase();
		if ( self::table_exists( $table_name ) ) {
			return;
		}

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			banner_name varchar(255) NOT NULL,
			banner_template varchar(255) NOT NULL,
			banner_settings longtext,
			PRIMARY KEY  (id)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- dbDelta for table creation, safe SQL with placeholders
		dbDelta( $sql );
	}

	public static function create_mega_menu_showcase() {
		global $wpdb;

		$table_name = self::table_mega_menu_showcase();
		if ( self::table_exists( $table_name ) ) {
			return;
		}

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			menu_name varchar(255) NOT NULL,
			menu_template varchar(255) NOT NULL,
			menu_settings longtext,
			PRIMARY KEY  (id)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- dbDelta for table creation, safe SQL with placeholders
		dbDelta( $sql );
	}

	public static function create_shortcodes_showcase() {
		global $wpdb;

		$table_name = self::table_shortcodes_showcase();
		if ( self::table_exists( $table_name ) ) {
			return;
		}

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            template_name varchar(255) NOT NULL,
            template_id varchar(255) NOT NULL,
            template_html longtext,
            template_css longtext,
            PRIMARY KEY  (id)
        ) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- dbDelta for table creation, safe SQL with placeholders
		dbDelta( $sql );
	}

	public static function create_gallery_shortcode() {
		// Create gallery shortcode tables
		if (class_exists('\Shopglut\galleryShortcode\GalleryDataTables')) {
			\Shopglut\galleryShortcode\GalleryDataTables::create_tables();
		}
	}

	public static function create_showcase_quickview() {
		global $wpdb;

		$table_name = self::table_quickview_enhancement();
		if ( self::table_exists( $table_name ) ) {
			return;
		}

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			layout_name varchar(255) NOT NULL,
			layout_template varchar(255) NOT NULL,
			layout_settings longtext,
			PRIMARY KEY  (id)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- dbDelta for table creation, safe SQL with placeholders
		dbDelta( $sql );
	}

	public static function create_showcase_comparison() {
		global $wpdb;

		$table_name = self::table_comparison_enhancement();
		if ( self::table_exists( $table_name ) ) {
			return;
		}

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			layout_name varchar(255) NOT NULL,
			layout_template varchar(255) NOT NULL,
			layout_settings longtext,
			PRIMARY KEY  (id)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- dbDelta for table creation, safe SQL with placeholders
		dbDelta( $sql );
	}

	public static function create_tabs_showcase() {
		global $wpdb;

		$table_name = self::table_tabs_showcase();
		if ( self::table_exists( $table_name ) ) {
			return;
		}

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            tab_name varchar(255) NOT NULL,
            tab_settings longtext,
            PRIMARY KEY  (id)
        ) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- dbDelta for table creation, safe SQL with placeholders
		dbDelta( $sql );
	}

	public static function create_wishlist_table() {
			global $wpdb;

			$table_name = self::table_shopg_wishlist();
			if ( self::table_exists( $table_name ) ) {
				return;
			}

			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE {$table_name} (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				wish_user_id varchar(255) NOT NULL,
				username varchar(255) NOT NULL,
				useremail varchar(255) NOT NULL,
				product_ids text NOT NULL,
				product_meta longtext DEFAULT NULL,
				wishlist_notifications text NOT NULL,
				product_added_time datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
				product_individual_dates longtext DEFAULT NULL,
				share_data text DEFAULT NULL,
				PRIMARY KEY (id)
			) {$charset_collate};";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- dbDelta for table creation, safe SQL with placeholders
			dbDelta( $sql );

			// Clear cache after table creation
			wp_cache_delete( 'shopglut_table_exists_' . md5( $table_name ) );
    }
	public static function create_lock_settings() {
		global $wpdb;

		$table_name = self::table_lock_settings();
		if ( self::table_exists( $table_name ) ) {
			return;
		}

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned DEFAULT NULL,
			email_subscribe varchar(255) NOT NULL,
			name_subscribe varchar(255) NOT NULL,
			subscription_status varchar(50) DEFAULT 'pending',
			lock_type varchar(50) DEFAULT 'email',
			expiry_date datetime DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY user_id (user_id),
			KEY email_subscribe (email_subscribe),
			KEY subscription_status (subscription_status)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- dbDelta for table creation, safe SQL with placeholders
		dbDelta( $sql );
	}

	public static function shopglut_woo_subscription_table() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = array();

		// Subscription Tables
		$sql[] = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}shopglut_woo_subscriptions (
            subscription_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            order_id bigint(20) unsigned NOT NULL,
            product_id bigint(20) unsigned NOT NULL,
            variation_id bigint(20) unsigned DEFAULT NULL,
            user_id bigint(20) unsigned NOT NULL,
            status varchar(50) NOT NULL DEFAULT 'pending',
            billing_period varchar(20) NOT NULL,
            billing_interval int(11) NOT NULL DEFAULT 1,
            trial_period varchar(20) DEFAULT NULL,
            trial_interval int(11) DEFAULT NULL,
            initial_amount decimal(19,4) NOT NULL DEFAULT 0,
            recurring_amount decimal(19,4) NOT NULL DEFAULT 0,
            start_date datetime NOT NULL,
            trial_end_date datetime DEFAULT NULL,
            next_payment_date datetime DEFAULT NULL,
            end_date datetime DEFAULT NULL,
            last_payment_date datetime DEFAULT NULL,
            payment_method varchar(100) DEFAULT NULL,
            payment_method_title varchar(100) DEFAULT NULL,
            total_payments int(11) DEFAULT 0,
            completed_payments int(11) DEFAULT 0,
            failed_payments int(11) DEFAULT 0,
            suspension_count int(11) DEFAULT 0,
            cancelled_date datetime DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (subscription_id),
            KEY order_id (order_id),
            KEY product_id (product_id),
            KEY user_id (user_id),
            KEY status (status),
            KEY next_payment_date (next_payment_date)
        ) {$charset_collate};";

		$sql[] = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}shopglut_subscription_items (
            item_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            subscription_id bigint(20) unsigned NOT NULL,
            order_item_id bigint(20) unsigned NOT NULL,
            product_id bigint(20) unsigned NOT NULL,
            variation_id bigint(20) unsigned DEFAULT NULL,
            quantity int(11) NOT NULL DEFAULT 1,
            subtotal decimal(19,4) NOT NULL DEFAULT 0,
            subtotal_tax decimal(19,4) NOT NULL DEFAULT 0,
            total decimal(19,4) NOT NULL DEFAULT 0,
            total_tax decimal(19,4) NOT NULL DEFAULT 0,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (item_id),
            KEY subscription_id (subscription_id),
            KEY product_id (product_id)
        ) {$charset_collate};";

		$sql[] = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}shopglut_subscription_meta (
            meta_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            subscription_id bigint(20) unsigned NOT NULL,
            meta_key varchar(255) DEFAULT NULL,
            meta_value longtext,
            PRIMARY KEY (meta_id),
            KEY subscription_id (subscription_id),
            KEY meta_key (meta_key(191))
        ) {$charset_collate};";

		$sql[] = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}shopglut_subscription_schedule (
            schedule_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            subscription_id bigint(20) unsigned NOT NULL,
            action varchar(50) NOT NULL,
            scheduled_date datetime NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'pending',
            last_attempt datetime DEFAULT NULL,
            completed_date datetime DEFAULT NULL,
            args longtext,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (schedule_id),
            KEY subscription_id (subscription_id),
            KEY action (action),
            KEY scheduled_date (scheduled_date),
            KEY status (status)
        ) {$charset_collate};";

		foreach ( $sql as $query ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- dbDelta for table creation, safe SQL with placeholders
			dbDelta( $query );
		}
	}

	public static function shopglut_create_slider_table() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'shopglut_sliders';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            slider_name varchar(255) NOT NULL,
            slider_template varchar(255) NOT NULL,
            slider_settings longtext,
            date_created datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) {$charset_collate};";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- dbDelta for table creation, safe SQL with placeholders
		dbDelta( $sql );
	}

	public static function shopglut_create_tab_table() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'shopglut_tabs';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            tab_name varchar(255) NOT NULL,
            tab_template varchar(255) NOT NULL,
            tab_settings longtext,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) {$charset_collate};";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- dbDelta for table creation, safe SQL with placeholders
		dbDelta( $sql );
	}

	public static function shopglut_create_accordion_table() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'shopglut_accordions';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            accordion_name varchar(255) NOT NULL,
            accordion_template varchar(255) NOT NULL,
            accordion_settings longtext,
            date_created datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) {$charset_collate};";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- dbDelta for table creation, safe SQL with placeholders
		dbDelta( $sql );
	}

	public static function shopglut_create_gallery_table() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'shopglut_gallerys';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            gallery_name varchar(255) NOT NULL,
            gallery_template varchar(255) NOT NULL,
            gallery_settings longtext,
            date_created datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) {$charset_collate};";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- dbDelta for table creation, safe SQL with placeholders
		dbDelta( $sql );
	}

	public static function create_woo_templates() {
		global $wpdb;

		$table_name = self::table_woo_templates();

		// Check if column exists, if not add it (for existing installations)
		$was_existing = self::table_exists( $table_name );
		$added_column = false;
		if ( $was_existing && ! self::column_exists( $table_name, 'is_default' ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- ALTER TABLE for adding column to existing table
			$wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN is_default tinyint(1) DEFAULT 0 AFTER template_tags" );
			$added_column = true;
		}

		if ( self::table_exists( $table_name ) && ! $added_column ) {
			// Table exists and we didn't just add the column - check if we need to insert defaults
			$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
			if ( $count == 0 ) {
				// Table is empty, insert default templates if class exists
				if ( class_exists( '\Shortcodeglut\wooTemplates\WooTemplatesEntity' ) ) {
					\Shortcodeglut\wooTemplates\WooTemplatesEntity::insert_default_templates();
				}
			}
			return;
		}

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            template_name varchar(255) NOT NULL,
            template_id varchar(255) NOT NULL,
            template_html longtext,
            template_css longtext,
            template_tags longtext,
            is_default tinyint(1) DEFAULT 0,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY template_id (template_id)
        ) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- dbDelta for table creation, safe SQL with placeholders
		dbDelta( $sql );

		// Insert default templates after table creation if class exists
		if ( class_exists( '\Shortcodeglut\wooTemplates\WooTemplatesEntity' ) ) {
			\Shortcodeglut\wooTemplates\WooTemplatesEntity::insert_default_templates();
		}
	}

	public static function create_product_custom_field_settings() {
		global $wpdb;

		$table_name = self::table_product_custom_field_settings();
		if ( self::table_exists( $table_name ) ) {
			return;
		}

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            field_name varchar(255) NOT NULL,
            field_settings longtext,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- dbDelta for table creation, safe SQL with placeholders
		dbDelta( $sql );

	}

	public static function create_product_badges() {
		global $wpdb;

		$table_name = self::table_product_badges();
		$charset_collate = $wpdb->get_charset_collate();

		// First, check if table exists and needs migration from old structure

		$sql = "CREATE TABLE {$table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            layout_name varchar(255) NOT NULL,
            layout_template varchar(255) NOT NULL DEFAULT 'template1',
            layout_settings longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- dbDelta for table creation, safe SQL with placeholders
		dbDelta( $sql );

		// Clear cache after table creation
	}

	public static function create_product_swatches() {
		global $wpdb;

		$table_name = self::table_product_swatches();
		$table_existed = self::table_exists( $table_name );

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            layout_name varchar(255) NOT NULL,
            layout_template varchar(255) NOT NULL DEFAULT 'template1',
            layout_settings longtext,
            assigned_attributes text DEFAULT NULL,
            assignment_type varchar(20) DEFAULT 'legacy',
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY assignment_type (assignment_type)
        ) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- dbDelta for table creation, safe SQL with placeholders
		dbDelta( $sql );

		// Add new columns to existing tables for attribute-based assignment
		if ( $table_existed ) {
			self::migrate_product_swatches_table();
		}
	}

	/**
	 * Migrate existing product swatches table to support attribute-based assignment
	 */
	public static function migrate_product_swatches_table() {
		global $wpdb;

		$table_name = self::table_product_swatches();

		// Check if assigned_attributes column exists
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Schema migration check
		$column_exists = $wpdb->get_var(
			"SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
			WHERE TABLE_SCHEMA = DATABASE()
			AND TABLE_NAME = '" . esc_sql( $table_name ) . "'
			AND COLUMN_NAME = 'assigned_attributes'"
		);

		if ( ! $column_exists ) {
			// Add assigned_attributes column
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Schema migration, safe SQL
			$wpdb->query(
				"ALTER TABLE `" . esc_sql( $table_name ) . "`
				ADD COLUMN assigned_attributes text DEFAULT NULL AFTER layout_settings"
			);
		}

		// Check if assignment_type column exists
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Schema migration check
		$column_exists = $wpdb->get_var(
			"SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
			WHERE TABLE_SCHEMA = DATABASE()
			AND TABLE_NAME = '" . esc_sql( $table_name ) . "'
			AND COLUMN_NAME = 'assignment_type'"
		);

		if ( ! $column_exists ) {
			// Add assignment_type column
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Schema migration, safe SQL
			$wpdb->query(
				"ALTER TABLE `" . esc_sql( $table_name ) . "`
				ADD COLUMN assignment_type varchar(20) DEFAULT 'legacy' AFTER assigned_attributes"
			);

			// Add index for assignment_type
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Schema migration, safe SQL
			$wpdb->query(
				"ALTER TABLE `" . esc_sql( $table_name ) . "`
				ADD KEY assignment_type (assignment_type)"
			);
		}
	}

	// REMOVED: create_product_comparisons() - Duplicate table, not used by code
	// The correct table is shopglut_comparison_layouts (used by ProductComparisonDataManage)

	// REMOVED: create_product_quickview() - Duplicate table, not used by code
	// The correct table is shopglut_quickview_layouts (used by QuickViewDataManage)

	// Add this function to define the table name
	public static function table_checkout_fields() {
		global $wpdb;
		return $wpdb->prefix . 'shopglut_checkout_fields';
	}

	public static function create_checkout_fields() {
		global $wpdb;

		$table_name = self::table_checkout_fields();
		if ( self::table_exists( $table_name ) ) {
			return;
		}

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			field_name varchar(255) NOT NULL,
			field_settings longtext,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- dbDelta for table creation, safe SQL with placeholders
		dbDelta( $sql );
	}


	public static function create_showcase_filters1() {
		global $wpdb;

		$table_name = self::table_showcase_filters1();
		if ( self::table_exists( $table_name ) ) {
			return;
		}

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            filter_name varchar(255) NOT NULL,
            filter_settings longtext,
            PRIMARY KEY  (id)
        ) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}


	public static function create_shop_banner_layouts() {
		global $wpdb;
		$table_name = self::table_shop_banner_layouts();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            layout_name varchar(255) NOT NULL,
            layout_template varchar(255) NOT NULL,
            layout_settings longtext,
            status varchar(20) DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) {$charset_collate};";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- dbDelta for table creation, safe SQL with placeholders
		dbDelta( $sql );
	}

	public static function create_slider_layouts() {
		global $wpdb;
		$table_name = self::table_slider_layouts();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            layout_name varchar(255) NOT NULL,
            layout_template varchar(255) NOT NULL,
            layout_settings longtext,
            status varchar(20) DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) {$charset_collate};";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- dbDelta for table creation, safe SQL with placeholders
		dbDelta( $sql );
	}

	public static function create_tab_layouts() {
		global $wpdb;
		$table_name = self::table_tab_layouts();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            layout_name varchar(255) NOT NULL,
            layout_template varchar(255) NOT NULL,
            layout_settings longtext,
            status varchar(20) DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) {$charset_collate};";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- dbDelta for table creation, safe SQL with placeholders
		dbDelta( $sql );
	}

	public static function create_accordion_layouts() {
		global $wpdb;
		$table_name = self::table_accordion_layouts();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            layout_name varchar(255) NOT NULL,
            layout_template varchar(255) NOT NULL,
            layout_settings longtext,
            status varchar(20) DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) {$charset_collate};";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- dbDelta for table creation, safe SQL with placeholders
		dbDelta( $sql );
	}

	public static function create_gallery_layouts() {
		global $wpdb;
		$table_name = self::table_gallery_layouts();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            layout_name varchar(255) NOT NULL,
            layout_template varchar(255) NOT NULL,
            layout_settings longtext,
            status varchar(20) DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) {$charset_collate};";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- dbDelta for table creation, safe SQL with placeholders
		dbDelta( $sql );
	}

	public static function ShopGlut_initialize() {
		if ( self::$initialized ) {
			return;
		}

		// Get list of intentionally deleted tables (do not recreate these)
		$deleted_tables = get_option( 'shopglut_intentionally_deleted_tables', array() );

		// Get ModuleManager instance to check which modules are enabled
		$module_manager = \Shopglut\ModuleManager::get_instance();

		// Always create core tables (unless intentionally deleted)
		if ( ! in_array( 'user_actions', $deleted_tables, true ) ) {
			self::create_user_actions();
		}

		// Only create tables for enabled modules (unless intentionally deleted)
		if ($module_manager->is_module_enabled('shop_layouts') && ! in_array( 'shop_layouts', $deleted_tables, true )) {
			self::create_shop_layouts();
		}
		if ($module_manager->is_module_enabled('archive_layouts') && ! in_array( 'archive_layouts', $deleted_tables, true )) {
			self::create_archive_layouts();
		}
		if ($module_manager->is_module_enabled('single_product') && ! in_array( 'single_product', $deleted_tables, true )) {
			self::create_single_layouts();
		}
		if ($module_manager->is_module_enabled('cart_page') && ! in_array( 'cart_page', $deleted_tables, true )) {
			self::create_cartpage_layouts();
		}
		if ($module_manager->is_module_enabled('orderComplete_page') && ! in_array( 'order_complete', $deleted_tables, true )) {
			self::create_ordercomplete_layouts();
		}
		if ($module_manager->is_module_enabled('account_page') && ! in_array( 'account_page', $deleted_tables, true )) {
			self::create_accountpage_layouts();
		}
		if ($module_manager->is_module_enabled('checkout_field_editor') && ! in_array( 'checkout_field_editor', $deleted_tables, true )) {
			self::create_checkout_fields();
		}
		if ($module_manager->is_module_enabled('shop_filters') && ! in_array( 'shop_filters', $deleted_tables, true )) {
			self::create_showcase_filters();
		}
		if ($module_manager->is_module_enabled('wishlist') && ! in_array( 'wishlist', $deleted_tables, true )) {
			self::create_wishlist_table();
		}
		if ($module_manager->is_module_enabled('shortcode_showcase') && ! in_array( 'shortcodes', $deleted_tables, true )) {
			self::create_shortcodes_showcase();
		}
		if ($module_manager->is_module_enabled('product_badges') && ! in_array( 'product_badges', $deleted_tables, true )) {
			self::create_product_badges();
		}
		if ($module_manager->is_module_enabled('shop_banner')) {
			self::create_showcase_banners();
			self::create_shop_banner_layouts();
		}
		if ($module_manager->is_module_enabled('slider')) {
			self::create_slider_layouts();
		}
		if ($module_manager->is_module_enabled('tabs')) {
			self::create_tabs_showcase();
			self::create_tab_layouts();
		}
		if ($module_manager->is_module_enabled('accordion')) {
			self::create_accordion_layouts();
		}
		if ($module_manager->is_module_enabled('gallery')) {
			self::create_gallery_layouts();
		}

		// Always create mega menu table for showcases (unless intentionally deleted)
		if ( ! in_array( 'mega_menu', $deleted_tables, true ) ) {
			self::create_mega_menu_showcase();
		}
		if ($module_manager->is_module_enabled('quick_views')) {
			self::create_showcase_quickview();
		}
		if ($module_manager->is_module_enabled('product_comparison')) {
			self::create_showcase_comparison();
		}
		if ($module_manager->is_module_enabled('product_swatches')) {
			self::create_product_swatches();
		}

		// Business solution modules
		self::shopglut_woo_subscription_table(); // Keep for existing functionality
		self::create_lock_settings(); // Keep for existing functionality
		
		if ($module_manager->is_module_enabled('woo_templates')) {
			self::create_woo_templates();
		}
		if ($module_manager->is_module_enabled('acf_fields')) {
			self::create_product_custom_field_settings();
		}
		if ($module_manager->is_module_enabled('sliders')) {
			self::shopglut_create_slider_table();
		}
		if ($module_manager->is_module_enabled('tabs')) {
			self::shopglut_create_tab_table();
		}
		if ($module_manager->is_module_enabled('accordions')) {
			self::shopglut_create_accordion_table();
		}
		if ($module_manager->is_module_enabled('gallerys')) {
			self::shopglut_create_gallery_table();
		}


		self::create_showcase_filters1();

		self::$initialized = true;
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
			'quickview' => array(
				'table' => $wpdb->prefix . 'shopglut_quickview_layouts',
				'name' => 'Quick View',
				'description' => 'Quick view layouts',
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
	 * Delete selected database tables
	 */
	public static function delete_selected_tables( $selected_tables ) {
		global $wpdb;

		$all_tables = self::get_all_table_names();
		$deleted = array();
		$errors = array();
		$not_found = array();

		// Get current list of intentionally deleted tables
		$deleted_tables_list = get_option( 'shopglut_intentionally_deleted_tables', array() );

		foreach ( $selected_tables as $table_key ) {
			if ( ! isset( $all_tables[ $table_key ] ) ) {
				$errors[] = "Invalid table key: {$table_key}";
				continue;
			}

			$table_name = $all_tables[ $table_key ]['table'];

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
			}
		}

		// Update the intentionally deleted tables list
		update_option( 'shopglut_intentionally_deleted_tables', $deleted_tables_list );

		return array(
			'deleted' => $deleted,
			'errors' => $errors,
			'not_found' => $not_found,
			'deleted_count' => count( $deleted ),
			'error_count' => count( $errors )
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
	 * Reset all module states to disabled
	 */
	public static function reset_module_states() {
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

		// Clear the initialization flag
		self::$initialized = false;

		return array(
			'reset_count' => $reset_count,
			'message' => sprintf(
				/* translators: %d is the number of modules reset */
				__( 'Successfully reset %d module states to disabled.', 'shopglut' ),
				$reset_count
			)
		);
	}

	/**
	 * Force create core tables - used for existing installations where tables are missing
	 * This bypasses module enabled status to ensure essential tables exist
	 */
	public static function force_create_core_tables() {
		$created_tables = array();

		// Core layout tables that are always needed
		$core_tables = array(
			'create_single_layouts',
			'create_cartpage_layouts',
			'create_ordercomplete_layouts',
			'create_product_badges',
			'create_product_swatches',
			'create_product_custom_field_settings',
		);

		foreach ($core_tables as $method) {
			if (method_exists(__CLASS__, $method)) {
				try {
					call_user_func(array(__CLASS__, $method));
					$created_tables[] = $method;
				} catch (Exception $e) {
					// Log error but continue with other tables
					error_log('ShopGlut: Error creating table via ' . $method . ': ' . $e->getMessage());
				}
			}
		}

		return $created_tables;
	}
}

/**
 * Callback function to render database manager UI
 */
function shopglut_render_database_manager() {
	global $wpdb;

	// Get all table definitions
	$all_tables = \Shopglut\ShopGlutDatabase::get_all_table_names();

	// Check which tables exist
	$tables_status = array();
	foreach ( $all_tables as $key => $table_info ) {
		$table_name = $table_info['table'];
		$exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) === $table_name;

		// Get row count if table exists
		$row_count = 0;
		if ( $exists ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Getting row count
			$row_count = $wpdb->get_var( "SELECT COUNT(*) FROM `{$table_name}`" );
		}

		$tables_status[ $key ] = array(
			'info' => $table_info,
			'exists' => $exists,
			'row_count' => $row_count
		);
	}
	?>
	<div class="shopglut-table-manager" style="margin-top: 10px;">
		<!-- Table Selection -->
		<div class="shopglut-table-list" style="background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px; padding: 15px; margin-bottom: 15px;">
			<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
				<strong><?php esc_html_e( 'Select tables to delete:', 'shopglut' ); ?></strong>
				<label style="cursor: pointer;">
					<input type="checkbox" id="shopglut-select-all-tables" style="margin-right: 5px;">
					<?php esc_html_e( 'Select All', 'shopglut' ); ?>
				</label>
			</div>

			<table class="widefat" style="border: none;">
				<thead>
					<tr>
						<th style="width: 40px; padding: 8px;"></th>
						<th style="padding: 8px;"><?php esc_html_e( 'Table Name', 'shopglut' ); ?></th>
						<th style="padding: 8px;"><?php esc_html_e( 'Description', 'shopglut' ); ?></th>
						<th style="padding: 8px; width: 100px;"><?php esc_html_e( 'Status', 'shopglut' ); ?></th>
						<th style="padding: 8px; width: 80px;"><?php esc_html_e( 'Records', 'shopglut' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $tables_status as $key => $data ) : ?>
						<tr>
							<td style="padding: 8px;">
								<?php if ( $data['exists'] ) : ?>
									<input type="checkbox" class="shopglut-table-checkbox" name="shopglut_tables[]" value="<?php echo esc_attr( $key ); ?>">
								<?php endif; ?>
							</td>
							<td style="padding: 8px;">
								<code><?php echo esc_html( $data['info']['name'] ); ?></code>
							</td>
							<td style="padding: 8px; color: #666;">
								<?php echo esc_html( $data['info']['description'] ); ?>
							</td>
							<td style="padding: 8px;">
								<?php if ( $data['exists'] ) : ?>
									<span style="color: #46b450;">● <?php esc_html_e( 'Exists', 'shopglut' ); ?></span>
								<?php else : ?>
									<span style="color: #999;">○ <?php esc_html_e( 'Not Created', 'shopglut' ); ?></span>
								<?php endif; ?>
							</td>
							<td style="padding: 8px; text-align: center;">
								<?php if ( $data['exists'] ) : ?>
									<span style="background: #e5e5e5; padding: 2px 8px; border-radius: 10px; font-size: 12px;">
										<?php echo esc_html( $data['row_count'] ); ?>
									</span>
								<?php else : ?>
									<span style="color: #999;">—</span>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>

		<!-- Action Buttons -->
		<div class="shopglut-action-buttons" style="display: flex; gap: 10px; align-items: center;">
			<button type="button" id="shopglut-delete-selected-tables" class="button" style="background: #dc3545; color: #fff; border-color: #dc3545; padding: 8px 16px;">
				<span class="dashicons dashicons-trash" style="margin-top: 3px;"></span>
				<?php esc_html_e( 'Delete Selected Tables', 'shopglut' ); ?>
			</button>
			<button type="button" id="shopglut-delete-all-tables" class="button button-link-delete" style="padding: 8px 16px;">
				<span class="dashicons dashicons-warning" style="margin-top: 3px;"></span>
				<?php esc_html_e( 'Delete ALL Tables', 'shopglut' ); ?>
			</button>
			<span class="spinner" id="shopglut-delete-spinner" style="float: none; margin-left: 10px;"></span>
		</div>

		<p class="description" style="margin-top: 10px; color: #dc3545;">
			<strong><?php esc_html_e( 'Warning: Deleting tables is irreversible. Always backup your database first!', 'shopglut' ); ?></strong>
		</p>
	</div>

	<!-- Confirmation Modal -->
	<div id="shopglut-delete-modal" style="display: none;">
		<div class="shopglut-modal-overlay" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 100000;"></div>
		<div class="shopglut-modal-content" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: #fff; padding: 30px; border-radius: 8px; max-width: 500px; width: 90%; z-index: 100001; box-shadow: 0 5px 30px rgba(0,0,0,0.3);">
			<h2 style="margin-top: 0; color: #dc3545;">
				<span class="dashicons dashicons-warning" style="font-size: 24px; margin-right: 10px;"></span>
				<span id="shopglut-modal-title"><?php esc_html_e( 'Confirm Deletion', 'shopglut' ); ?></span>
			</h2>
			<p id="shopglut-modal-message" style="font-size: 14px; line-height: 1.6;"></p>
			<div id="shopglut-modal-tables" style="max-height: 200px; overflow-y: auto; margin: 15px 0; padding: 10px; background: #f9f9f9; border-radius: 4px;"></div>
			<p style="font-weight: bold; color: #dc3545; background: #fff3cd; padding: 10px; border-radius: 4px;">
				<?php esc_html_e( 'This action is IRREVERSIBLE. Please confirm you have a backup.', 'shopglut' ); ?>
			</p>
			<div style="margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end;">
				<button type="button" id="shopglut-cancel-delete" class="button button-secondary">
					<?php esc_html_e( 'Cancel', 'shopglut' ); ?>
				</button>
				<button type="button" id="shopglut-confirm-delete" class="button" style="background: #dc3545; color: #fff; border-color: #dc3545;">
					<span class="dashicons dashicons-trash" style="margin-top: 3px;"></span>
					<span id="shopglut-confirm-btn-text"><?php esc_html_e( 'Yes, Delete', 'shopglut' ); ?></span>
				</button>
			</div>
		</div>
	</div>

	<script type="text/javascript">
	jQuery(document).ready(function($) {
		var selectedTables = [];
		var deleteAll = false;

		// Select/Deselect all checkboxes
		$('#shopglut-select-all-tables').on('change', function() {
			$('.shopglut-table-checkbox').prop('checked', $(this).prop('checked'));
		});

		// Update "Select All" when individual checkboxes change
		$(document).on('change', '.shopglut-table-checkbox', function() {
			var total = $('.shopglut-table-checkbox').length;
			var checked = $('.shopglut-table-checkbox:checked').length;
			$('#shopglut-select-all-tables').prop('checked', total === checked);
		});

		// Delete selected tables
		$('#shopglut-delete-selected-tables').on('click', function() {
			selectedTables = [];
			$('.shopglut-table-checkbox:checked').each(function() {
				selectedTables.push($(this).val());
			});

			if (selectedTables.length === 0) {
				alert('<?php echo esc_js( __( 'Please select at least one table to delete.', 'shopglut' ) ); ?>');
				return;
			}

			deleteAll = false;
			showModal(selectedTables);
		});

		// Delete all tables
		$('#shopglut-delete-all-tables').on('click', function() {
			var allTables = [];
			$('.shopglut-table-checkbox').each(function() {
				allTables.push($(this).val());
			});

			if (allTables.length === 0) {
				alert('<?php echo esc_js( __( 'No tables exist to delete.', 'shopglut' ) ); ?>');
				return;
			}

			deleteAll = true;
			selectedTables = allTables;
			showModal(allTables, true);
		});

		// Show modal with selected tables
		function showModal(tables, isAll) {
			var $modalTables = $('#shopglut-modal-tables');
			$modalTables.empty();

			tables.forEach(function(key) {
				var name = $('input[value="' + key + '"]').closest('tr').find('code').text();
				$modalTables.append('<div style="padding: 5px 0; border-bottom: 1px solid #eee;"><span class="dashicons dashicons-database" style="color: #666; margin-right: 5px;"></span>' + name + '</div>');
			});

			if (isAll) {
				$('#shopglut-modal-title').text('<?php echo esc_js( __( 'Delete ALL Tables', 'shopglut' ) ); ?>');
				$('#shopglut-modal-message').text('<?php echo esc_js( __( 'You are about to permanently delete ALL existing ShopGlut database tables:', 'shopglut' ) ); ?>');
				$('#shopglut-confirm-btn-text').text('<?php echo esc_js( __( 'Yes, Delete ALL Tables', 'shopglut' ) ); ?>');
			} else {
				$('#shopglut-modal-title').text('<?php echo esc_js( __( 'Delete Selected Tables', 'shopglut' ) ); ?>');
				$('#shopglut-modal-message').text('<?php echo esc_js( __( 'You are about to permanently delete the following tables:', 'shopglut' ) ); ?>');
				$('#shopglut-confirm-btn-text').text('<?php echo esc_js( __( 'Yes, Delete Selected', 'shopglut' ) ); ?>');
			}

			$('#shopglut-delete-modal').show();
		}

		// Hide modal on cancel
		$('#shopglut-cancel-delete, .shopglut-modal-overlay').on('click', function() {
			$('#shopglut-delete-modal').hide();
		});

		// Confirm delete
		$('#shopglut-confirm-delete').on('click', function() {
			var $btn = $(this);
			var $spinner = $('#shopglut-delete-spinner');

			$btn.prop('disabled', true);
			$spinner.addClass('is-active');

			$.ajax({
				url: ajaxurl,
				method: 'POST',
				data: {
					action: 'shopglut_delete_selected_tables',
					tables: selectedTables,
					nonce: '<?php echo esc_attr( wp_create_nonce( 'shopglut_data_management' ) ); ?>'
				},
				success: function(response) {
					$spinner.removeClass('is-active');
					$btn.prop('disabled', false);
					$('#shopglut-delete-modal').hide();

					if (response.success) {
						alert('✅ ' + response.data.message);
						location.reload();
					} else {
						alert('❌ ' + response.data.message);
					}
				},
				error: function() {
					$spinner.removeClass('is-active');
					$btn.prop('disabled', false);
					alert('❌ <?php echo esc_js( __( 'An error occurred. Please try again.', 'shopglut' ) ); ?>');
				}
			});
		});
	});
	</script>
	<?php
}

/**
 * Callback function to render reset modules button
 */
function shopglut_render_reset_modules_button() {
	?>
	<div class="shopglut-data-action">
		<button type="button" id="shopglut-reset-modules" class="button button-secondary" style="padding: 8px 16px;">
			<span class="dashicons dashicons-update" style="margin-top: 3px;"></span>
			<?php esc_html_e( 'Reset All Module States', 'shopglut' ); ?>
		</button>
		<span class="spinner" id="shopglut-reset-spinner" style="float: none; margin-left: 10px;"></span>
	</div>

	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#shopglut-reset-modules').on('click', function() {
			if (!confirm('<?php echo esc_js( __( 'Are you sure you want to reset all module states to disabled? Your data will be preserved.', 'shopglut' ) ); ?>')) {
				return;
			}

			var $btn = $(this);
			var $spinner = $('#shopglut-reset-spinner');

			$btn.prop('disabled', true);
			$spinner.addClass('is-active');

			$.ajax({
				url: ajaxurl,
				method: 'POST',
				data: {
					action: 'shopglut_reset_module_states',
					nonce: '<?php echo esc_attr( wp_create_nonce( 'shopglut_data_management' ) ); ?>'
				},
				success: function(response) {
					$spinner.removeClass('is-active');
					$btn.prop('disabled', false);

					if (response.success) {
						alert('✅ ' + response.data.message);
						location.reload();
					} else {
						alert('❌ ' + response.data.message);
					}
				},
				error: function() {
					$spinner.removeClass('is-active');
					$btn.prop('disabled', false);
					alert('❌ <?php echo esc_js( __( 'An error occurred. Please try again.', 'shopglut' ) ); ?>');
				}
			});
		});
	});
	</script>
	<?php
}

