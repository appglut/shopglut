<?php
/**
 * Fields on each menu item (Appearance → Menus).
 *
 *   AppGlut::createNavMenuOptions( 'my_menu_opts' );
 *   AppGlut::createSection( 'my_menu_opts', array( 'fields' => array( ... ) ) );
 *
 * Values are post meta of the menu item. In walkers they are also available
 * as $item->appglut['my_menu_opts'] (defaults merged).
 *
 * @package AppGlut_Framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AppGlut_Nav_Menu extends AppGlut_Container {

	protected function default_args() {
		return array(
			'data_type'  => 'serialize', // serialize | unserialize.
			'attach'     => true,        // Add values to menu item objects ($item->appglut[ $unique ]).
			'capability' => 'edit_theme_options',
			'theme'      => 'light',
			'accent'     => '',
			'class'      => '',
		);
	}

	protected function setup() {
		add_action( 'wp_nav_menu_item_custom_fields', array( $this, 'render' ), 10, 2 );
		add_action( 'wp_update_nav_menu_item', array( $this, 'save' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );

		if ( $this->arg( 'attach' ) ) {
			add_filter( 'wp_setup_nav_menu_item', array( $this, 'attach' ) );
		}
	}

	public function enqueue( $hook ) {
		if ( 'nav-menus.php' === $hook ) {
			AppGlut::enqueue( $this->fields() );
		}
	}

	public function get_values( $item_id ) {
		return $this->load_meta( 'post', $item_id );
	}

	public function attach( $item ) {
		if ( is_object( $item ) && ! empty( $item->ID ) && ! is_admin() ) {
			$values                  = isset( $item->appglut ) && is_array( $item->appglut ) ? $item->appglut : array();
			$values[ $this->unique ] = $this->get_values( $item->ID );
			$item->appglut           = $values;
		}
		return $item;
	}

	public function render( $item_id, $item ) {
		$prefix = $this->unique . '[' . (int) $item_id . ']';

		// One nonce per menu screen is enough.
		static $nonce_done = array();
		if ( empty( $nonce_done[ $this->unique ] ) ) {
			$nonce_done[ $this->unique ] = true;
			wp_nonce_field( 'appglut_menu_' . $this->unique, '_appglut_nonce_' . $this->unique );
		}

		echo '<div' . $this->app_attrs( 'navmenu', array( 'data-item' => (int) $item_id ) ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput
		// Marker so an item whose fields are all empty is still saved (and cleared).
		echo '<input type="hidden" name="' . esc_attr( $prefix . '[__agf]' ) . '" value="1">';

		$values = $this->get_values( $item_id );
		foreach ( $this->sections() as $section ) {
			if ( empty( $section['fields'] ) ) {
				continue;
			}
			if ( $section['title'] ) {
				echo '<div class="agf-navmenu-title">' . AppGlut::icon( $section['icon'] ) . '<span>' . esc_html( $section['title'] ) . '</span></div>'; // phpcs:ignore WordPress.Security.EscapeOutput
			}
			echo '<div class="agf-grid">';
			AppGlut::fields( $section['fields'], $values, $prefix, 'navmenu' );
			echo '</div>';
		}
		echo '</div>';
	}

	public function save( $menu_id, $item_id ) {
		$nonce = '_appglut_nonce_' . $this->unique;

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitized per field below.
		if ( ! isset( $_POST[ $this->unique ][ $item_id ], $_POST[ $nonce ] ) ) {
			return; // Item not edited on this screen (e.g. saved from the customizer).
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ $nonce ] ) ), 'appglut_menu_' . $this->unique ) ) {
			return;
		}
		if ( ! current_user_can( $this->arg( 'capability' ) ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitized per field.
		$data = wp_unslash( $_POST[ $this->unique ][ $item_id ] );
		$this->save_meta( 'post', $item_id, is_array( $data ) ? $data : array() );
	}
}
