<?php
/**
 * Shortcode generator: a searchable picker + form that builds shortcodes for
 * the classic editor (button above the editor) and the block editor (block).
 *
 *   AppGlut::createShortcoder( 'my_shortcodes', array( 'button_title' => 'Add Shortcode' ) );
 *   AppGlut::createSection( 'my_shortcodes', array(
 *     'title'     => 'Button',
 *     'shortcode' => 'my_button',
 *     'fields'    => array( ... ),            // field 'content' becomes the enclosed content
 *     'group'     => array(                   // optional repeated child shortcodes
 *       'shortcode' => 'my_tab', 'title' => 'Tab', 'fields' => array( ... ),
 *     ),
 *     'callback'  => function ( $atts, $content, $tag ) { ... }, // optional: registers the shortcode
 *   ) );
 *
 * Values that are not plain text (arrays, objects) are written as JSON;
 * AppGlut::shortcode_atts() decodes them and merges the defaults.
 *
 * @package AppGlut_Framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AppGlut_Shortcoder extends AppGlut_Container {

	private $printed = false;

	protected function default_args() {
		return array(
			'button_title'   => __( 'Add Shortcode', 'shopglut' ),
			'select_title'   => __( 'Choose a shortcode', 'shopglut' ),
			'insert_title'   => __( 'Insert Shortcode', 'shopglut' ),
			'show_in_editor' => true,    // Button above classic editors.
			'gutenberg'      => true,    // Block editor block: true or array( 'title', 'description', 'icon', 'category', 'keywords' ).
			'post_types'     => array(), // Limit to these post types (empty: all).
			'screens'        => array(), // Extra admin screen IDs where the picker is available (call AppGlut.shortcoder.open in JS).
			'theme'          => 'light',
			'accent'         => '',
			'class'          => '',
		);
	}

	protected function setup() {
		add_action( 'init', array( $this, 'register_shortcodes' ), 20 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'admin_footer', array( $this, 'print_modal' ) );

		if ( $this->arg( 'show_in_editor' ) ) {
			add_action( 'media_buttons', array( $this, 'media_button' ), 20 );
		}
		if ( $this->arg( 'gutenberg' ) ) {
			add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block' ) );
		}
	}

	/** Sections that define a shortcode. */
	public function shortcodes() {
		return array_filter(
			$this->sections(),
			function ( $section ) {
				return ! empty( $section['shortcode'] );
			}
		);
	}

	/* ---------------------------------------------------------------------
	 * Front end
	 * ------------------------------------------------------------------- */

	public function register_shortcodes() {
		foreach ( $this->shortcodes() as $section ) {
			if ( ! empty( $section['callback'] ) && is_callable( $section['callback'] ) && ! shortcode_exists( $section['shortcode'] ) ) {
				$this->add_shortcode( $section['shortcode'], $section['fields'], $section['callback'] );
			}
			if ( ! empty( $section['group']['shortcode'] ) && ! empty( $section['group']['callback'] ) && is_callable( $section['group']['callback'] ) && ! shortcode_exists( $section['group']['shortcode'] ) ) {
				$this->add_shortcode( $section['group']['shortcode'], (array) $section['group']['fields'], $section['group']['callback'] );
			}
		}
	}

	private function add_shortcode( $tag, $fields, $callback ) {
		add_shortcode(
			$tag,
			function ( $atts, $content = '', $shortcode_tag = '' ) use ( $fields, $callback ) {
				return call_user_func( $callback, AppGlut::shortcode_atts( $fields, $atts ), $content, $shortcode_tag );
			}
		);
	}

	/* ---------------------------------------------------------------------
	 * Admin
	 * ------------------------------------------------------------------- */

	/** True on screens where the picker should load. */
	public function is_screen() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen ) {
			return false;
		}
		if ( in_array( $screen->id, (array) $this->arg( 'screens' ), true ) ) {
			return true;
		}
		if ( 'post' !== $screen->base ) {
			return false;
		}
		$types = (array) $this->arg( 'post_types' );
		return ! $types || in_array( $screen->post_type, $types, true );
	}

	public function enqueue() {
		if ( ! $this->is_screen() || ! $this->shortcodes() ) {
			return;
		}
		$fields = array();
		foreach ( $this->shortcodes() as $section ) {
			$fields = array_merge( $fields, (array) $section['fields'], ! empty( $section['group']['fields'] ) ? (array) $section['group']['fields'] : array() );
		}
		AppGlut::enqueue( $fields );
		wp_enqueue_script( 'appglut-shortcoder', AppGlut::$url . 'assets/js/appglut-shortcoder.js', array( 'appglut' ), AppGlut::$version, true );
	}

	public function enqueue_block() {
		if ( ! $this->is_screen() || ! $this->shortcodes() ) {
			return;
		}

		$block = $this->arg( 'gutenberg' );
		$block = is_array( $block ) ? $block : array();

		wp_enqueue_script( 'appglut-shortcoder-block', AppGlut::$url . 'assets/js/appglut-shortcoder-block.js', array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-data' ), AppGlut::$version, true );
		wp_add_inline_script(
			'appglut-shortcoder-block',
			'window.appglutBlocks = window.appglutBlocks || []; window.appglutBlocks.push(' . wp_json_encode(
				array(
					'unique'      => $this->unique,
					'name'        => 'appglut/' . sanitize_key( str_replace( '_', '-', $this->unique ) ),
					'title'       => isset( $block['title'] ) ? $block['title'] : $this->arg( 'button_title' ),
					'description' => isset( $block['description'] ) ? $block['description'] : $this->arg( 'select_title' ),
					'icon'        => isset( $block['icon'] ) ? $block['icon'] : 'shortcode',
					'category'    => isset( $block['category'] ) ? $block['category'] : 'widgets',
					'keywords'    => isset( $block['keywords'] ) ? (array) $block['keywords'] : array( 'shortcode' ),
					'button'      => $this->arg( 'button_title' ),
				)
			) . ');',
			'before'
		);
	}

	public function media_button( $editor_id ) {
		if ( ! $this->is_screen() || ! $this->shortcodes() ) {
			return;
		}
		printf(
			'<button type="button" class="button agf-sc-open" data-unique="%1$s" data-editor="%2$s"><span class="dashicons dashicons-shortcode" aria-hidden="true" style="vertical-align:text-top;margin-inline-end:4px"></span>%3$s</button>',
			esc_attr( $this->unique ),
			esc_attr( $editor_id ),
			esc_html( $this->arg( 'button_title' ) )
		);
	}

	public function print_modal() {
		if ( $this->printed || ! $this->is_screen() || ! $this->shortcodes() ) {
			return;
		}
		$this->printed = true;

		$theme = 'dark' === $this->arg( 'theme' ) ? 'dark' : 'light';

		echo '<template class="agf-sc-template" data-unique="' . esc_attr( $this->unique ) . '">';
		echo '<div class="agf-ui agf-modal agf-sc-modal" role="dialog" aria-modal="true" data-theme="' . esc_attr( $theme ) . '" data-unique="' . esc_attr( $this->unique ) . '"' . ( $this->arg( 'accent' ) ? ' style="--agf-accent:' . esc_attr( appglut_sanitize_color( $this->arg( 'accent' ) ) ) . '"' : '' ) . '>';
		echo '<div class="agf-modal-backdrop"></div>';
		echo '<div class="agf-modal-dialog agf-modal-lg">';

		// Head.
		echo '<div class="agf-modal-head">';
		echo '<button type="button" class="agf-icon-btn agf-sc-back" hidden aria-label="' . esc_attr__( 'Back', 'shopglut' ) . '">' . AppGlut::icon( 'dashicons-arrow-left-alt2' ) . '</button>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '<h3 class="agf-sc-heading" data-default="' . esc_attr( $this->arg( 'select_title' ) ) . '">' . esc_html( $this->arg( 'select_title' ) ) . '</h3>';
		echo '<button type="button" class="agf-icon-btn agf-modal-close" aria-label="' . esc_attr__( 'Close', 'shopglut' ) . '">' . AppGlut::icon( 'dashicons-no-alt' ) . '</button>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '</div>';

		echo '<div class="agf-modal-body">';

		// Step 1: picker.
		echo '<div class="agf-sc-picker">';
		if ( count( $this->shortcodes() ) > 6 ) {
			echo '<div class="agf-sc-search">' . AppGlut::icon( 'dashicons-search' ) . '<input type="search" class="agf-input agf-sc-filter" placeholder="' . esc_attr__( 'Search shortcodes…', 'shopglut' ) . '"></div>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		echo '<div class="agf-sc-grid">';
		foreach ( $this->shortcodes() as $id => $section ) {
			echo '<button type="button" class="agf-sc-card" data-id="' . esc_attr( $id ) . '">';
			echo '<span class="agf-sc-card-icon">' . AppGlut::icon( $section['icon'] ? $section['icon'] : 'dashicons-shortcode' ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
			echo '<span class="agf-sc-card-text"><span class="agf-sc-card-title">' . esc_html( $section['title'] ? $section['title'] : $section['shortcode'] ) . '</span>';
			if ( $section['description'] ) {
				echo '<span class="agf-sc-card-desc">' . esc_html( wp_strip_all_tags( $section['description'] ) ) . '</span>';
			}
			echo '<code class="agf-sc-card-tag">[' . esc_html( $section['shortcode'] ) . ']</code></span>';
			echo '</button>';
		}
		echo '</div>';
		echo '<div class="agf-sc-empty" hidden>' . esc_html__( 'No shortcodes match your search.', 'shopglut' ) . '</div>';
		echo '</div>';

		// Step 2: one form per shortcode.
		foreach ( $this->shortcodes() as $id => $section ) {
			$this->render_form( $id, $section );
		}

		echo '</div>';

		// Foot: live preview + actions.
		echo '<div class="agf-modal-foot agf-sc-foot" hidden>';
		echo '<code class="agf-sc-preview" title="' . esc_attr__( 'Generated shortcode', 'shopglut' ) . '"></code>';
		echo '<button type="button" class="agf-btn agf-btn-ghost agf-sc-copy">' . AppGlut::icon( 'dashicons-admin-page' ) . '<span>' . esc_html__( 'Copy', 'shopglut' ) . '</span></button>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '<button type="button" class="agf-btn agf-btn-primary agf-sc-insert">' . AppGlut::icon( 'dashicons-yes' ) . '<span>' . esc_html( $this->arg( 'insert_title' ) ) . '</span></button>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '</div>';

		echo '</div></div>';
		echo '</template>';
	}

	private function render_form( $id, $section ) {
		$fields = (array) $section['fields'];
		$group  = ! empty( $section['group']['shortcode'] ) ? (array) $section['group'] : array();

		$has_content = false;
		foreach ( $fields as $field ) {
			if ( isset( $field['id'] ) && 'content' === $field['id'] ) {
				$has_content = true;
			}
		}

		$atts = array(
			'data-id'        => $id,
			'data-tag'       => $section['shortcode'],
			'data-title'     => $section['title'] ? $section['title'] : $section['shortcode'],
			'data-content'   => $has_content ? '1' : '0',
			'data-defaults'  => self::flat_defaults( $fields ),
			'data-group-tag' => $group ? $group['shortcode'] : '',
			'hidden'         => true,
		);
		if ( $group ) {
			$atts['data-group-defaults'] = self::flat_defaults( isset( $group['fields'] ) ? (array) $group['fields'] : array() );
		}

		echo '<div' . $this->app_attrs( 'shortcode', $atts ) . '><div class="agf-grid">'; // phpcs:ignore WordPress.Security.EscapeOutput
		$prefix = 'agfsc_' . $this->unique . '[' . $id . ']';
		AppGlut::fields( $fields, array(), $prefix, 'shortcode' );

		if ( $group ) {
			AppGlut::field(
				array(
					'id'           => '__group',
					'type'         => 'group',
					'title'        => isset( $group['title'] ) ? $group['title'] : __( 'Items', 'shopglut' ),
					'button_title' => isset( $group['button_title'] ) ? $group['button_title'] : __( 'Add item', 'shopglut' ),
					'item_title'   => isset( $group['item_title'] ) ? $group['item_title'] : '',
					'collapsed'    => false,
					'layout'       => 'stacked',
					'fields'       => isset( $group['fields'] ) ? (array) $group['fields'] : array(),
					'default'      => isset( $group['default'] ) ? (array) $group['default'] : array( array() ),
				),
				null,
				$prefix,
				'shortcode'
			);
		}
		echo '</div></div>';
	}

	/** Defaults as strings in the shape the browser serializes, to omit unchanged attributes. */
	private static function flat_defaults( $fields ) {
		$out = array();
		// Empty values first, so fields without a 'default' are also omitted while untouched.
		$values = array_merge( AppGlut::sanitize_fields( $fields, array() ), AppGlut::defaults( $fields ) );
		foreach ( $values as $key => $value ) {
			if ( is_bool( $value ) ) {
				$value = $value ? '1' : '0';
			}
			$out[ $key ] = $value;
		}
		return $out;
	}
}
