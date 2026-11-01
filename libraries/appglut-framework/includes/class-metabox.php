<?php
/**
 * Post metabox container.
 *
 * @package AppGlut_Framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AppGlut_Metabox extends AppGlut_Container {

	protected function default_args() {
		return array(
			'title'          => __( 'Options', 'shopglut' ),
			'post_type'      => 'post',
			'context'        => 'advanced',   // normal | side | advanced.
			'priority'       => 'default',
			'data_type'      => 'serialize',  // serialize | unserialize (one meta key per field).
			'nav'            => 'horizontal', // horizontal | vertical tabs when there are several sections.
			'page_templates' => array(),      // Only show for these page template slugs.
			'theme'          => 'light',
			'accent'         => '',
			'class'          => '',
			'capability'     => 'edit_post',
		);
	}

	protected function setup() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	public function post_types() {
		return (array) $this->arg( 'post_type' );
	}

	public function add_meta_box( $post_type ) {
		if ( ! in_array( $post_type, $this->post_types(), true ) ) {
			return;
		}

		$templates = (array) $this->arg( 'page_templates' );
		if ( $templates ) {
			$current = get_page_template_slug( get_the_ID() );
			if ( ! in_array( $current ? $current : 'default', $templates, true ) ) {
				return;
			}
		}

		add_meta_box( $this->unique, $this->arg( 'title' ), array( $this, 'render' ), $post_type, $this->arg( 'context' ), $this->arg( 'priority' ) );
		add_filter( 'postbox_classes_' . $post_type . '_' . $this->unique, array( $this, 'postbox_classes' ) );
	}

	public function postbox_classes( $classes ) {
		$classes[] = 'agf-postbox';
		return $classes;
	}

	public function enqueue() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen && 'post' === $screen->base && in_array( $screen->post_type, $this->post_types(), true ) ) {
			AppGlut::enqueue( $this->fields() );
		}
	}

	public function get_values( $post_id ) {
		return $this->load_meta( 'post', $post_id );
	}

	public function render( $post ) {
		$values   = $this->get_values( $post->ID );
		$sections = $this->sections();
		$tabs     = count( array_filter( wp_list_pluck( $sections, 'fields' ) ) ) > 1;
		$active   = $tabs ? $this->first_section_id() : '';
		$nav      = 'vertical' === $this->arg( 'nav' ) ? 'vertical' : 'horizontal';

		wp_nonce_field( 'appglut_metabox_' . $this->unique, '_appglut_nonce_' . $this->unique );

		echo '<div' . $this->app_attrs( 'metabox', array( 'data-active' => $active ) ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '<div class="agf-body agf-tabs-' . esc_attr( $nav ) . ( $tabs ? '' : ' agf-no-nav' ) . '">';

		if ( $tabs ) {
			echo '<nav class="agf-nav"><div class="agf-nav-inner">';
			$this->render_nav( $this->section_tree(), $active );
			echo '</div></nav>';
		}

		echo '<div class="agf-main">';
		$this->render_panels( $values, $active, false );
		echo '</div></div></div>';
	}

	public function save( $post_id, $post ) {
		$nonce = '_appglut_nonce_' . $this->unique;

		if ( ! isset( $_POST[ $nonce ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ $nonce ] ) ), 'appglut_metabox_' . $this->unique ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) ) {
			return;
		}
		if ( ! in_array( $post->post_type, $this->post_types(), true ) || ! current_user_can( $this->arg( 'capability' ), $post_id ) ) {
			return;
		}

		$this->save_meta( 'post', $post_id );
	}
}
