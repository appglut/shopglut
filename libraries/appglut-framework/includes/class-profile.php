<?php
/**
 * User profile options container.
 *
 * @package AppGlut_Framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AppGlut_Profile extends AppGlut_Container {

	protected function default_args() {
		return array(
			'title'     => '',
			'data_type' => 'serialize',
			'theme'     => 'light',
			'accent'    => '',
			'class'     => '',
		);
	}

	protected function setup() {
		add_action( 'show_user_profile', array( $this, 'render' ) );
		add_action( 'edit_user_profile', array( $this, 'render' ) );
		add_action( 'personal_options_update', array( $this, 'save' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	public function enqueue( $hook ) {
		if ( in_array( $hook, array( 'profile.php', 'user-edit.php' ), true ) ) {
			AppGlut::enqueue( $this->fields() );
		}
	}

	public function get_values( $user_id ) {
		return $this->load_meta( 'user', $user_id );
	}

	public function render( $user ) {
		if ( $this->arg( 'title' ) ) {
			echo '<h2>' . esc_html( $this->arg( 'title' ) ) . '</h2>';
		}
		wp_nonce_field( 'appglut_profile_' . $this->unique, '_appglut_nonce_' . $this->unique );
		echo '<div' . $this->app_attrs( 'simple' ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput
		$this->render_panels( $this->get_values( $user->ID ), '' );
		echo '</div>';
	}

	public function save( $user_id ) {
		$nonce = '_appglut_nonce_' . $this->unique;
		if ( ! isset( $_POST[ $nonce ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ $nonce ] ) ), 'appglut_profile_' . $this->unique ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return;
		}
		$this->save_meta( 'user', $user_id );
	}
}
