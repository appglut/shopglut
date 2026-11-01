<?php
/**
 * Layouts of the builder modules (one table for every module).
 *
 * A layout is: module, name, template, status (active | draft), priority and settings (JSON). The
 * settings hold the template options keyed the way the layout editor saves them, plus 'display'
 * (where the layout applies).
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Layouts class.
 */
class ShopGlut_Layouts {

	/** Cache group. */
	const CACHE = 'shopglut_layouts';

	/**
	 * Decode a row.
	 *
	 * @param array|null $row Row.
	 * @return array|null
	 */
	private static function prepare( $row ) {
		if ( ! $row ) {
			return null;
		}
		$settings        = json_decode( (string) $row['settings'], true );
		$row['id']       = (int) $row['id'];
		$row['priority'] = (int) $row['priority'];
		$row['settings'] = is_array( $settings ) ? $settings : array();
		return $row;
	}

	/**
	 * One layout.
	 *
	 * @param int $id Layout ID.
	 * @return array|null
	 */
	public static function get( $id ) {
		global $wpdb;
		$id    = absint( $id );
		$cache = wp_cache_get( 'layout_' . $id, self::CACHE );
		if ( false !== $cache ) {
			return $cache;
		}
		$table = ShopGlut_DB::layouts_table();
		// phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Custom table, cached above.
		$row = self::prepare( $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ), ARRAY_A ) );
		wp_cache_set( 'layout_' . $id, $row, self::CACHE );
		return $row;
	}

	/**
	 * Layouts of a module.
	 *
	 * @param string $module Module ID.
	 * @param array  $args   status, search, orderby, order, per_page, page.
	 * @return array { items, total }
	 */
	public static function query( $module, $args = array() ) {
		global $wpdb;
		$args  = wp_parse_args( $args, array( 'status' => '', 'search' => '', 'orderby' => 'id', 'order' => 'DESC', 'per_page' => 20, 'page' => 1 ) );
		$table = ShopGlut_DB::layouts_table();

		$where = $wpdb->prepare( 'module = %s', $module );
		if ( in_array( $args['status'], array( 'active', 'draft' ), true ) ) {
			$where .= $wpdb->prepare( ' AND status = %s', $args['status'] );
		}
		if ( '' !== $args['search'] ) {
			$where .= $wpdb->prepare( ' AND name LIKE %s', '%' . $wpdb->esc_like( $args['search'] ) . '%' );
		}

		$orderby  = in_array( $args['orderby'], array( 'id', 'name', 'template', 'status', 'priority', 'updated_at' ), true ) ? $args['orderby'] : 'id';
		$order    = 'ASC' === strtoupper( $args['order'] ) ? 'ASC' : 'DESC';
		$per_page = max( 1, min( 200, (int) $args['per_page'] ) );
		$offset   = ( max( 1, (int) $args['page'] ) - 1 ) * $per_page;

		// phpcs:disable PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Custom table; $where is prepared above, orderby/order whitelisted.
		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE {$where}" );
		$rows  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE {$where} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d", $per_page, $offset ), ARRAY_A );
		// phpcs:enable

		return array(
			'items' => array_map( array( __CLASS__, 'prepare' ), (array) $rows ),
			'total' => $total,
		);
	}

	/**
	 * Number of layouts of a module.
	 *
	 * @param string $module Module ID.
	 * @param string $status Only this status.
	 * @return int
	 */
	public static function count( $module, $status = '' ) {
		global $wpdb;
		$table = ShopGlut_DB::layouts_table();
		$sql   = $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE module = %s", $module ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		if ( $status ) {
			$sql .= $wpdb->prepare( ' AND status = %s', $status );
		}
		return (int) $wpdb->get_var( $sql ); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared -- Prepared above.
	}

	/**
	 * Create a layout.
	 *
	 * @param string $module Module ID.
	 * @param array  $data   name, template, status, priority, settings.
	 * @return int|false New ID.
	 */
	public static function create( $module, $data ) {
		global $wpdb;
		$now = current_time( 'mysql', true );
		$ok  = $wpdb->insert( // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery
			ShopGlut_DB::layouts_table(),
			array(
				'module'     => $module,
				'name'       => isset( $data['name'] ) ? sanitize_text_field( $data['name'] ) : '',
				'template'   => isset( $data['template'] ) ? shopglut_sanitize_template( $data['template'] ) : 'template1',
				'status'     => isset( $data['status'] ) && 'active' === $data['status'] ? 'active' : 'draft',
				'priority'   => isset( $data['priority'] ) ? (int) $data['priority'] : 10,
				'settings'   => wp_json_encode( isset( $data['settings'] ) ? (array) $data['settings'] : array() ),
				'created_by' => get_current_user_id(),
				'created_at' => $now,
				'updated_at' => $now,
			),
			array( '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%s', '%s' )
		);
		if ( ! $ok ) {
			return false;
		}
		$id = (int) $wpdb->insert_id;
		do_action( 'shopglut_layout_created', $id, $module );
		return $id;
	}

	/**
	 * Update a layout.
	 *
	 * @param int   $id   Layout ID.
	 * @param array $data Any of name, template, status, priority, settings.
	 * @return bool
	 */
	public static function update( $id, $data ) {
		global $wpdb;
		$row  = array();
		$fmt  = array();
		$map  = array(
			'name'     => array( 'sanitize_text_field', '%s' ),
			'template' => array( 'shopglut_sanitize_template', '%s' ),
			'priority' => array( 'intval', '%d' ),
		);
		foreach ( $map as $key => $how ) {
			if ( array_key_exists( $key, $data ) ) {
				$row[ $key ] = call_user_func( $how[0], $data[ $key ] );
				$fmt[]       = $how[1];
			}
		}
		if ( array_key_exists( 'status', $data ) ) {
			$row['status'] = 'active' === $data['status'] ? 'active' : 'draft';
			$fmt[]         = '%s';
		}
		if ( array_key_exists( 'settings', $data ) ) {
			$row['settings'] = wp_json_encode( (array) $data['settings'] );
			$fmt[]           = '%s';
		}
		$row['updated_at'] = current_time( 'mysql', true );
		$fmt[]             = '%s';

		$ok = false !== $wpdb->update( ShopGlut_DB::layouts_table(), $row, array( 'id' => absint( $id ) ), $fmt, array( '%d' ) ); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery
		wp_cache_delete( 'layout_' . absint( $id ), self::CACHE );
		wp_cache_delete( 'active', self::CACHE );
		do_action( 'shopglut_layout_updated', absint( $id ), $data );
		return $ok;
	}

	/**
	 * Delete a layout.
	 *
	 * @param int $id Layout ID.
	 * @return bool
	 */
	public static function delete( $id ) {
		global $wpdb;
		$ok = (bool) $wpdb->delete( ShopGlut_DB::layouts_table(), array( 'id' => absint( $id ) ), array( '%d' ) ); // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery
		wp_cache_delete( 'layout_' . absint( $id ), self::CACHE );
		wp_cache_delete( 'active', self::CACHE );
		do_action( 'shopglut_layout_deleted', absint( $id ) );
		return $ok;
	}

	/**
	 * Copy a layout (as a draft).
	 *
	 * @param int $id Layout ID.
	 * @return int|false New ID.
	 */
	public static function duplicate( $id ) {
		$row = self::get( $id );
		if ( ! $row ) {
			return false;
		}
		return self::create(
			$row['module'],
			array(
				/* translators: %s: layout name */
				'name'     => sprintf( __( '%s (copy)', 'shopglut' ), $row['name'] ),
				'template' => $row['template'],
				'status'   => 'draft',
				'priority' => $row['priority'],
				'settings' => $row['settings'],
			)
		);
	}

	/**
	 * Active layouts of every module, highest priority first (one query per request).
	 *
	 * @param string $module Module ID.
	 * @return array
	 */
	public static function active( $module ) {
		global $wpdb;
		$all = wp_cache_get( 'active', self::CACHE );
		if ( false === $all ) {
			$table = ShopGlut_DB::layouts_table();
			// phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Custom table, cached.
			$rows = (array) $wpdb->get_results( "SELECT * FROM {$table} WHERE status = 'active' ORDER BY priority DESC, id DESC", ARRAY_A );
			$all  = array();
			foreach ( $rows as $row ) {
				$all[ $row['module'] ][] = self::prepare( $row );
			}
			wp_cache_set( 'active', $all, self::CACHE );
		}
		return isset( $all[ $module ] ) ? $all[ $module ] : array();
	}

	/**
	 * The active layout of a module that applies to a context (first match by priority).
	 *
	 * @param string $module  Module ID.
	 * @param array  $context Context.
	 * @return array|null
	 */
	public static function resolve( $module, $context = array() ) {
		$mod = ShopGlut_Modules::instance()->get( $module );
		if ( ! $mod || ! shopglut_module_enabled( $module ) ) {
			return null;
		}
		$templates = $mod->get_templates();
		foreach ( self::active( $module ) as $layout ) {
			// A Pro template stops applying when Pro is switched off.
			if ( isset( $templates[ $layout['template'] ] ) && $templates[ $layout['template'] ]['locked'] ) {
				continue;
			}
			if ( $mod->layout_matches( $layout, $context ) ) {
				return apply_filters( 'shopglut_resolved_layout', $layout, $module, $context );
			}
		}
		return null;
	}
}
