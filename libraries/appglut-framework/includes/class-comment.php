<?php
/**
 * Comment metabox container (Comments → Edit comment screen).
 *
 *   AppGlut::createCommentMetabox( 'my_comment_opts', array( 'title' => 'Extra' ) );
 *
 * @package AppGlut_Framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AppGlut_Comment extends AppGlut_Container {

	protected function default_args() {
		return array(
			'title'     => __( 'Options', 'shopglut' ),
			'priority'  => 'default',
			'data_type' => 'serialize', // serialize | unserialize.
			'nav'       => 'horizontal',
			'theme'     => 'light',
			'accent'    => '',
			'class'     => '',
		);
	}

	protected function setup() {
		add_action( 'add_meta_boxes_comment', array( $this, 'add_meta_box' ) );
		add_action( 'edit_comment', array( $this, 'save' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	public function add_meta_box() {
		add_meta_box( $this->unique, $this->arg( 'title' ), array( $this, 'render' ), 'comment', 'normal', $this->arg( 'priority' ) );
		add_filter( 'postbox_classes_comment_' . $this->unique, array( $this, 'postbox_classes' ) );
	}

	public function postbox_classes( $classes ) {
		$classes[] = 'agf-postbox';
		return $classes;
	}

	public function enqueue( $hook ) {
		if ( 'comment.php' === $hook ) {
			AppGlut::enqueue( $this->fields() );
		}
	}

	public function get_values( $comment_id ) {
		return $this->load_meta( 'comment', $comment_id );
	}

	public function render( $comment ) {
		$sections = $this->sections();
		$tabs     = count( array_filter( wp_list_pluck( $sections, 'fields' ) ) ) > 1;
		$active   = $tabs ? $this->first_section_id() : '';
		$nav      = 'vertical' === $this->arg( 'nav' ) ? 'vertical' : 'horizontal';

		wp_nonce_field( 'appglut_comment_' . $this->unique, '_appglut_nonce_' . $this->unique );

		// Same shell as post metaboxes, so tabs work identically.
		echo '<div' . $this->app_attrs( 'metabox', array( 'data-active' => $active ) ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '<div class="agf-body agf-tabs-' . esc_attr( $nav ) . ( $tabs ? '' : ' agf-no-nav' ) . '">';
		if ( $tabs ) {
			echo '<nav class="agf-nav"><div class="agf-nav-inner">';
			$this->render_nav( $this->section_tree(), $active );
			echo '</div></nav>';
		}
		echo '<div class="agf-main">';
		$this->render_panels( $this->get_values( $comment->comment_ID ), $active, false );
		echo '</div></div></div>';
	}

	public function save( $comment_id ) {
		$nonce = '_appglut_nonce_' . $this->unique;
		if ( ! isset( $_POST[ $nonce ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ $nonce ] ) ), 'appglut_comment_' . $this->unique ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_comment', $comment_id ) ) {
			return;
		}
		$this->save_meta( 'comment', $comment_id );
	}
}
