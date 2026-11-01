<?php
/**
 * Taxonomy term options container.
 *
 * @package AppGlut_Framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AppGlut_Taxonomy extends AppGlut_Container {

	protected function default_args() {
		return array(
			'taxonomy'  => 'category',
			'data_type' => 'serialize',
			'theme'     => 'light',
			'accent'    => '',
			'class'     => '',
		);
	}

	protected function setup() {
		foreach ( (array) $this->arg( 'taxonomy' ) as $taxonomy ) {
			add_action( $taxonomy . '_add_form_fields', array( $this, 'render_add' ) );
			add_action( $taxonomy . '_edit_form', array( $this, 'render_edit' ), 10, 2 );
			add_action( 'created_' . $taxonomy, array( $this, 'save' ) );
			add_action( 'edited_' . $taxonomy, array( $this, 'save' ) );
		}
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	public function enqueue() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen && in_array( $screen->base, array( 'edit-tags', 'term' ), true ) && in_array( $screen->taxonomy, (array) $this->arg( 'taxonomy' ), true ) ) {
			AppGlut::enqueue( $this->fields() );
		}
	}

	public function get_values( $term_id ) {
		return $this->load_meta( 'term', $term_id );
	}

	public function render_add( $taxonomy ) {
		$this->render( AppGlut::defaults( $this->fields() ), 'add' );
	}

	public function render_edit( $term, $taxonomy ) {
		$this->render( $this->get_values( $term->term_id ), 'edit' );
	}

	private function render( $values, $context ) {
		wp_nonce_field( 'appglut_term_' . $this->unique, '_appglut_nonce_' . $this->unique );
		echo '<div' . $this->app_attrs( 'simple', array( 'data-context' => 'term-' . $context ) ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput
		$this->render_panels( $values, '' );
		echo '</div>';
	}

	public function save( $term_id ) {
		$nonce = '_appglut_nonce_' . $this->unique;
		if ( ! isset( $_POST[ $nonce ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ $nonce ] ) ), 'appglut_term_' . $this->unique ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_term', $term_id ) ) {
			return;
		}
		$this->save_meta( 'term', $term_id );
	}
}
