<?php
/**
 * ShopGlut → Subscribers: people who subscribed through a popup. Search, delete and CSV export.
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Subscribers_Admin class.
 */
class ShopGlut_Subscribers_Admin {

	const SLUG = 'shopglut-subscribers';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'shopglut_admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_post_shopglut_subscribers_export', array( $this, 'export' ) );
		add_action( 'admin_post_shopglut_subscribers_delete', array( $this, 'delete' ) );
	}

	/**
	 * Capability.
	 *
	 * @return string
	 */
	private static function cap() {
		return ShopGlut_Admin::cap( 'module:popups' );
	}

	/**
	 * Add the page.
	 *
	 * @param string $parent Parent slug.
	 * @return void
	 */
	public function menu( $parent ) {
		add_submenu_page( $parent, __( 'Subscribers', 'shopglut' ), __( 'Subscribers', 'shopglut' ), self::cap(), self::SLUG, array( $this, 'page' ) );
	}

	/**
	 * The list.
	 *
	 * @return void
	 */
	public function page() {
		if ( ! current_user_can( self::cap() ) ) {
			return;
		}
		global $wpdb;
		$table = ShopGlut_Popups::table();
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only list filters.
		$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
		$paged  = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
		// phpcs:enable
		$per   = 50;
		$where = $search ? $wpdb->prepare( 'WHERE email LIKE %s OR name LIKE %s', '%' . $wpdb->esc_like( $search ) . '%', '%' . $wpdb->esc_like( $search ) . '%' ) : '';
		// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared -- custom table; $where is prepared above.
		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} {$where}" );
		$rows  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} {$where} ORDER BY id DESC LIMIT %d OFFSET %d", $per, ( $paged - 1 ) * $per ), ARRAY_A );
		// phpcs:enable
		$base = admin_url( 'admin.php?page=' . self::SLUG );
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Subscribers', 'shopglut' ); ?></h1>
			<a class="page-title-action" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=shopglut_subscribers_export' ), 'shopglut_subscribers_export' ) ); ?>"><?php esc_html_e( 'Export CSV', 'shopglut' ); ?></a>
			<p><?php esc_html_e( 'People who subscribed through a ShopGlut subscribe popup.', 'shopglut' ); ?></p>
			<form method="get">
				<input type="hidden" name="page" value="<?php echo esc_attr( self::SLUG ); ?>">
				<p class="search-box"><input type="search" name="s" value="<?php echo esc_attr( $search ); ?>"> <button class="button"><?php esc_html_e( 'Search', 'shopglut' ); ?></button></p>
			</form>
			<table class="widefat striped">
				<thead><tr><th><?php esc_html_e( 'Email', 'shopglut' ); ?></th><th><?php esc_html_e( 'Name', 'shopglut' ); ?></th><th><?php esc_html_e( 'Popup', 'shopglut' ); ?></th><th><?php esc_html_e( 'Consent', 'shopglut' ); ?></th><th><?php esc_html_e( 'Page', 'shopglut' ); ?></th><th><?php esc_html_e( 'Date', 'shopglut' ); ?></th><th></th></tr></thead>
				<tbody>
				<?php if ( ! $rows ) : ?>
					<tr><td colspan="7"><?php esc_html_e( 'No subscribers yet.', 'shopglut' ); ?></td></tr>
				<?php endif; ?>
				<?php
				foreach ( $rows as $row ) :
					$popup = ShopGlut_Layouts::get( (int) $row['popup_id'] );
					?>
					<tr>
						<td><?php echo esc_html( $row['email'] ); ?></td>
						<td><?php echo esc_html( $row['name'] ); ?></td>
						<td><?php echo $popup ? esc_html( $popup['name'] ) : '&ndash;'; ?></td>
						<td><?php echo $row['consent'] ? esc_html__( 'Yes', 'shopglut' ) : esc_html__( 'No', 'shopglut' ); ?></td>
						<td><?php echo $row['page_url'] ? '<a href="' . esc_url( $row['page_url'] ) . '" target="_blank" rel="noopener">' . esc_html( wp_parse_url( $row['page_url'], PHP_URL_PATH ) ) . '</a>' : '&ndash;'; ?></td>
						<td><?php echo esc_html( get_date_from_gmt( $row['created_at'], get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ); ?></td>
						<td><a class="submitdelete" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=shopglut_subscribers_delete&id=' . (int) $row['id'] ), 'shopglut_subscribers_delete_' . (int) $row['id'] ) ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Delete this subscriber?', 'shopglut' ) ); ?>')"><?php esc_html_e( 'Delete', 'shopglut' ); ?></a></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<?php
			$pages = (int) ceil( $total / $per );
			if ( $pages > 1 ) {
				echo '<p>' . wp_kses_post(
					paginate_links(
						array(
							'base'    => add_query_arg( 'paged', '%#%', $base ),
							'current' => $paged,
							'total'   => $pages,
						)
					)
				) . '</p>';
			}
			/* translators: %d: number of subscribers */
			echo '<p>' . esc_html( sprintf( _n( '%d subscriber', '%d subscribers', $total, 'shopglut' ), $total ) ) . '</p>';
			?>
		</div>
		<?php
	}

	/**
	 * CSV download.
	 *
	 * @return void
	 */
	public function export() {
		if ( ! current_user_can( self::cap() ) || ! check_admin_referer( 'shopglut_subscribers_export' ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'shopglut' ) );
		}
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- custom table, no input.
		$rows = $wpdb->get_results( 'SELECT email, name, popup_id, consent, page_url, created_at FROM ' . ShopGlut_Popups::table() . ' ORDER BY id ASC', ARRAY_A );
		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=shopglut-subscribers-' . gmdate( 'Y-m-d' ) . '.csv' );
		$out = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		fputcsv( $out, array( 'email', 'name', 'popup_id', 'consent', 'page_url', 'created_at_utc' ) );
		foreach ( $rows as $row ) {
			// Stop spreadsheet formula injection.
			fputcsv(
				$out,
				array_map(
					static function ( $v ) {
						return preg_match( '/^[=+\-@]/', (string) $v ) ? "'" . $v : $v;
					},
					$row
				)
			);
		}
		fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		exit;
	}

	/**
	 * Delete one subscriber.
	 *
	 * @return void
	 */
	public function delete() {
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		if ( ! $id || ! current_user_can( self::cap() ) || ! check_admin_referer( 'shopglut_subscribers_delete_' . $id ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'shopglut' ) );
		}
		global $wpdb;
		$wpdb->delete( ShopGlut_Popups::table(), array( 'id' => $id ), array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		wp_safe_redirect( admin_url( 'admin.php?page=' . self::SLUG ) );
		exit;
	}
}
