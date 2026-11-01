<?php
/**
 * Database tables.
 *
 * Every builder module (product page, cart, swatches, badges, filters …) keeps its layouts in one
 * table, told apart by the `module` column. Settings are stored as JSON.
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_DB class.
 */
class ShopGlut_DB {

	/**
	 * Layouts table name.
	 *
	 * @return string
	 */
	public static function layouts_table() {
		global $wpdb;
		return $wpdb->prefix . 'shopglut_layouts';
	}

	/**
	 * Create or update the tables (activation and version change).
	 *
	 * @return void
	 */
	public static function install() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table   = self::layouts_table();
		$charset = $wpdb->get_charset_collate();

		dbDelta(
			"CREATE TABLE {$table} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				module varchar(64) NOT NULL,
				name varchar(255) NOT NULL DEFAULT '',
				template varchar(64) NOT NULL DEFAULT 'template1',
				status varchar(20) NOT NULL DEFAULT 'draft',
				priority int(11) NOT NULL DEFAULT 10,
				settings longtext NULL,
				created_by bigint(20) unsigned NOT NULL DEFAULT 0,
				created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
				updated_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
				PRIMARY KEY  (id),
				KEY module_status (module, status)
			) {$charset};"
		);

		/**
		 * Modules and add-ons create their own tables here (wishlist items, swatch terms …).
		 *
		 * @param string $charset Charset / collation clause.
		 */
		do_action( 'shopglut_install_tables', $charset );

		update_option( 'shopglut_db_version', SHOPGLUT_DB_VERSION );
	}

	/**
	 * Run install() when the stored schema version is older than the plugin's.
	 *
	 * @return void
	 */
	public static function maybe_upgrade() {
		if ( get_option( 'shopglut_db_version' ) !== SHOPGLUT_DB_VERSION ) {
			self::install();
		}
	}
}
