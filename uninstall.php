<?php
/**
 * Uninstall: removes ShopGlut data only when Global Settings → Remove all data on uninstall is on.
 *
 * @package ShopGlut
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$shopglut_settings = get_option( 'shopglut_settings', array() );
if ( empty( $shopglut_settings['remove_data'] ) ) {
	return;
}

global $wpdb;

// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.SchemaChange -- Removing the plugin's own table.
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}shopglut_layouts" );

// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- Removing the plugin's own options.
$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $wpdb->esc_like( 'shopglut_' ) . '%' ) );

/** Modules and add-ons remove their own tables here. */
do_action( 'shopglut_uninstall' );
