<?php
/**
 * Fields: code_editor, wp_editor.
 *
 * Both are initialised in JavaScript so they also work inside cloned repeater items.
 *
 * @package AppGlut_Framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CodeMirror editor (WordPress core). Options: mode (css|scss|javascript|json|html|xml|php|sql|markdown), height (px).
 */
class AppGlut_Field_code_editor extends AppGlut_Field {

	public function layout() {
		return 'stacked';
	}

	public static function assets( $field ) {
		return array( 'code' => isset( $field['mode'] ) ? $field['mode'] : 'css' );
	}

	public function render() {
		$mode = $this->opt( 'mode', 'css' );
		echo '<div class="agf-code" style="--agf-code-h:' . (int) $this->opt( 'height', 260 ) . 'px">';
		echo '<div class="agf-code-bar"><span class="agf-code-mode">' . esc_html( strtoupper( $mode ) ) . '</span></div>';
		echo '<textarea class="agf-input agf-code-input" data-mode="' . esc_attr( $mode ) . '" name="' . esc_attr( $this->name() ) . '" rows="10" spellcheck="false"' . $this->attrs() . '>' . esc_textarea( is_scalar( $this->value ) ? (string) $this->value : '' ) . '</textarea>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '</div>';
	}

	public function sanitize( $value ) {
		$value = is_scalar( $value ) ? (string) $value : '';

		switch ( $this->opt( 'mode', 'css' ) ) {
			case 'css':
			case 'scss':
			case 'less':
				return wp_strip_all_tags( $value );
			case 'json':
				return ( '' === trim( $value ) || null !== json_decode( $value ) ) ? $value : '';
		}

		// Raw markup / scripts only for users trusted with unfiltered HTML.
		return current_user_can( 'unfiltered_html' ) ? $value : wp_kses_post( $value );
	}
}

/**
 * TinyMCE / Quicktags editor. Options: tinymce (bool), quicktags (bool), media_buttons (bool), height (px).
 */
class AppGlut_Field_wp_editor extends AppGlut_Field {

	public function layout() {
		return 'stacked';
	}

	public static function assets( $field ) {
		return array(
			'editor' => true,
			'media'  => ! isset( $field['media_buttons'] ) || $field['media_buttons'],
		);
	}

	public function render() {
		$settings = array(
			'tinymce'       => (bool) $this->opt( 'tinymce', true ),
			'quicktags'     => (bool) $this->opt( 'quicktags', true ),
			'mediaButtons'  => (bool) $this->opt( 'media_buttons', true ),
			'height'        => (int) $this->opt( 'height', 220 ),
		);
		echo '<div class="agf-wp-editor">';
		echo '<textarea class="agf-input agf-wp-editor-input" name="' . esc_attr( $this->name() ) . '" rows="8" data-editor="' . esc_attr( wp_json_encode( $settings ) ) . '">' . esc_textarea( is_scalar( $this->value ) ? (string) $this->value : '' ) . '</textarea>';
		echo '</div>';
	}

	public function sanitize( $value ) {
		$value = is_scalar( $value ) ? (string) $value : '';
		return current_user_can( 'unfiltered_html' ) ? $value : wp_kses_post( $value );
	}
}
