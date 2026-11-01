<?php
/**
 * AJAX endpoints: save, reset, import, export and remote select search.
 *
 * Saves are sent as a single JSON payload, so large panels are never cut off
 * by PHP's max_input_vars limit.
 *
 * @package AppGlut_Framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AppGlut_Ajax {

	public static function init() {
		foreach ( array( 'save', 'reset', 'import', 'export', 'query' ) as $action ) {
			add_action( 'wp_ajax_appglut_' . $action, array( __CLASS__, $action ) );
		}
	}

	/** Validate nonce + capability and return the options container. */
	private static function options_container() {
		$unique    = isset( $_REQUEST['unique'] ) ? sanitize_key( wp_unslash( $_REQUEST['unique'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$container = AppGlut::container( $unique );

		if ( ! $container instanceof AppGlut_Options ) {
			wp_send_json_error( array( 'message' => __( 'Unknown options panel.', 'shopglut' ) ), 400 );
		}
		if ( ! check_ajax_referer( 'appglut_' . $unique, 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Your session has expired. Please reload the page.', 'shopglut' ) ), 403 );
		}
		if ( ! current_user_can( $container->arg( 'menu_capability' ) ) ) {
			wp_send_json_error( array( 'message' => __( 'You are not allowed to do this.', 'shopglut' ) ), 403 );
		}

		return $container;
	}

	private static function json_param( $key ) {
		// phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput -- decoded then sanitized per field.
		$raw  = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';
		$data = json_decode( (string) $raw, true );
		return is_array( $data ) ? $data : null;
	}

	public static function save() {
		$container = self::options_container();
		$data      = self::json_param( 'data' );

		if ( null === $data ) {
			wp_send_json_error( array( 'message' => __( 'Invalid data received.', 'shopglut' ) ), 400 );
		}

		$data = isset( $data[ $container->unique ] ) ? $data[ $container->unique ] : array();
		$container->save( $data );

		wp_send_json_success(
			array(
				'message' => AppGlut::$errors ? __( 'Saved, but some fields were invalid and kept their previous value.', 'shopglut' ) : __( 'Settings saved', 'shopglut' ),
				'errors'  => AppGlut::$errors,
			)
		);
	}

	public static function reset() {
		$container = self::options_container();
		$section   = isset( $_POST['section'] ) ? sanitize_key( wp_unslash( $_POST['section'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

		$container->reset( $section );
		wp_send_json_success( array( 'message' => __( 'Defaults restored', 'shopglut' ) ) );
	}

	public static function import() {
		$container = self::options_container();
		$data      = self::json_param( 'data' );

		if ( null === $data ) {
			wp_send_json_error( array( 'message' => __( 'The import data is not valid JSON.', 'shopglut' ) ), 400 );
		}

		$container->save( $data, true );
		wp_send_json_success( array( 'message' => __( 'Settings imported', 'shopglut' ) ) );
	}

	public static function export() {
		$container = self::options_container();

		wp_send_json_success(
			array(
				'filename' => $container->unique . '-' . gmdate( 'Y-m-d' ) . '.json',
				'data'     => $container->get_values(),
			)
		);
	}

	/** Remote search for selects with 'ajax' => true. Query args are signed server-side. */
	public static function query() {
		check_ajax_referer( 'appglut_query', 'nonce' );

		$query = self::json_param( 'query' );
		$sig   = isset( $_POST['sig'] ) ? sanitize_text_field( wp_unslash( $_POST['sig'] ) ) : '';
		$term  = isset( $_POST['term'] ) ? sanitize_text_field( wp_unslash( $_POST['term'] ) ) : '';

		if ( ! $query || ! hash_equals( AppGlut::sign( $query ), $sig ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid query.', 'shopglut' ) ), 400 );
		}

		$type = isset( $query['type'] ) ? $query['type'] : '';
		$cap  = in_array( $type, array( 'user', 'users' ), true ) ? 'list_users' : 'edit_posts';
		if ( ! current_user_can( $cap ) ) {
			wp_send_json_error( array( 'message' => __( 'You are not allowed to do this.', 'shopglut' ) ), 403 );
		}

		$results = array();
		foreach ( AppGlut::get_data( $type, $term, isset( $query['args'] ) ? (array) $query['args'] : array() ) as $value => $label ) {
			$results[] = array(
				'value' => (string) $value,
				'label' => html_entity_decode( wp_strip_all_tags( (string) $label ), ENT_QUOTES, 'UTF-8' ),
			);
		}

		wp_send_json_success( $results );
	}
}
