<?php
/**
 * Fields: text, textarea, number, hidden.
 *
 * @package AppGlut_Framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Text input.
 *
 * Options: input_type (text|email|url|password|tel|search), prefix, suffix,
 * icon, copy (bool), maxlength + counter (bool), allow_html (bool).
 */
class AppGlut_Field_text extends AppGlut_Field {

	public function input_type() {
		$type = $this->opt( 'input_type', isset( $this->field['attributes']['type'] ) ? $this->field['attributes']['type'] : 'text' );
		return in_array( $type, array( 'text', 'email', 'url', 'password', 'tel', 'search' ), true ) ? $type : 'text';
	}

	public function render() {
		$type   = $this->input_type();
		$extra  = array(
			'type'      => $type,
			'class'     => 'agf-input',
			'name'      => $this->name(),
			'value'     => is_scalar( $this->value ) ? (string) $this->value : '',
			'maxlength' => $this->opt( 'maxlength' ),
		);
		if ( 'password' === $type ) {
			$extra['autocomplete'] = 'new-password';
		}

		echo '<div class="agf-input-group">';
		if ( $this->opt( 'icon' ) ) {
			echo '<span class="agf-addon agf-addon-icon">' . AppGlut::icon( $this->opt( 'icon' ) ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		if ( $this->opt( 'prefix' ) ) {
			echo '<span class="agf-addon">' . esc_html( $this->opt( 'prefix' ) ) . '</span>';
		}
		echo '<input' . $this->attrs( $extra ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput
		if ( $this->opt( 'suffix' ) ) {
			echo '<span class="agf-addon">' . esc_html( $this->opt( 'suffix' ) ) . '</span>';
		}
		if ( 'password' === $type ) {
			echo '<button type="button" class="agf-addon agf-addon-btn agf-reveal" aria-label="' . esc_attr__( 'Show password', 'shopglut' ) . '">' . AppGlut::icon( 'dashicons-visibility' ) . '</button>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		if ( $this->opt( 'copy' ) ) {
			echo '<button type="button" class="agf-addon agf-addon-btn agf-copy" aria-label="' . esc_attr__( 'Copy', 'shopglut' ) . '">' . AppGlut::icon( 'dashicons-admin-page' ) . '</button>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		echo '</div>';

		if ( $this->opt( 'counter' ) ) {
			echo '<div class="agf-counter" data-max="' . esc_attr( (int) $this->opt( 'maxlength', 0 ) ) . '"></div>';
		}
	}

	public function sanitize( $value ) {
		$value = is_scalar( $value ) ? (string) $value : '';
		switch ( $this->input_type() ) {
			case 'email':
				return sanitize_email( $value );
			case 'url':
				return esc_url_raw( $value );
			case 'password':
				return trim( $value );
		}
		return $this->opt( 'allow_html' ) ? wp_kses_post( $value ) : sanitize_text_field( $value );
	}
}

/**
 * Textarea. Options: rows, maxlength + counter, allow_html, autosize.
 */
class AppGlut_Field_textarea extends AppGlut_Field {

	public function render() {
		$extra = array(
			'class'     => 'agf-input agf-textarea' . ( $this->opt( 'autosize' ) ? ' agf-autosize' : '' ),
			'name'      => $this->name(),
			'rows'      => (int) $this->opt( 'rows', 5 ),
			'maxlength' => $this->opt( 'maxlength' ),
		);
		echo '<textarea' . $this->attrs( $extra ) . '>' . esc_textarea( is_scalar( $this->value ) ? (string) $this->value : '' ) . '</textarea>'; // phpcs:ignore WordPress.Security.EscapeOutput
		if ( $this->opt( 'counter' ) ) {
			echo '<div class="agf-counter" data-max="' . esc_attr( (int) $this->opt( 'maxlength', 0 ) ) . '"></div>';
		}
	}

	public function sanitize( $value ) {
		$value = is_scalar( $value ) ? (string) $value : '';
		return $this->opt( 'allow_html' ) ? wp_kses_post( $value ) : sanitize_textarea_field( $value );
	}
}

/**
 * Number. Options: min, max, step, unit (label shown after the input).
 */
class AppGlut_Field_number extends AppGlut_Field {

	public function render() {
		$extra = array(
			'type'  => 'number',
			'class' => 'agf-input agf-input-number',
			'name'  => $this->name(),
			'value' => is_scalar( $this->value ) ? (string) $this->value : '',
			'min'   => $this->opt( 'min' ),
			'max'   => $this->opt( 'max' ),
			'step'  => $this->opt( 'step', 'any' ),
		);
		echo '<div class="agf-input-group agf-input-group-sm">';
		echo '<input' . $this->attrs( $extra ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput
		if ( $this->opt( 'unit' ) ) {
			echo '<span class="agf-addon">' . esc_html( $this->opt( 'unit' ) ) . '</span>';
		}
		echo '</div>';
	}

	public function sanitize( $value ) {
		if ( ! is_numeric( $value ) ) {
			return '';
		}
		$value = $value + 0;
		if ( is_numeric( $this->opt( 'min' ) ) ) {
			$value = max( $this->opt( 'min' ) + 0, $value );
		}
		if ( is_numeric( $this->opt( 'max' ) ) ) {
			$value = min( $this->opt( 'max' ) + 0, $value );
		}
		return $value;
	}
}

/**
 * Hidden input.
 */
class AppGlut_Field_hidden extends AppGlut_Field {

	public function render() {
		echo '<input type="hidden" name="' . esc_attr( $this->name() ) . '" value="' . esc_attr( is_scalar( $this->value ) ? (string) $this->value : '' ) . '">';
	}
}
