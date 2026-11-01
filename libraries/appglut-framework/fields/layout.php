<?php
/**
 * UI-only fields (no stored value): heading, subheading, notice, content, divider, callback, button.
 *
 * @package AppGlut_Framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Large heading. Options: content (text), icon.
 */
class AppGlut_Field_heading extends AppGlut_Field {

	public function layout() {
		return 'full';
	}

	public function render() {
		echo '<h3 class="agf-heading">' . AppGlut::icon( $this->opt( 'icon' ) ) . '<span>' . wp_kses_post( $this->opt( 'content', '' ) ) . '</span></h3>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
}

/**
 * Small uppercase subheading. Options: content.
 */
class AppGlut_Field_subheading extends AppGlut_Field {

	public function layout() {
		return 'full';
	}

	public function render() {
		echo '<div class="agf-subheading">' . wp_kses_post( $this->opt( 'content', '' ) ) . '</div>';
	}
}

/**
 * Callout box. Options: style (info|success|warning|danger|accent), content, icon, dismissible.
 */
class AppGlut_Field_notice extends AppGlut_Field {

	public function layout() {
		return 'full';
	}

	public function render() {
		$style = $this->opt( 'style', 'info' );
		$icons = array(
			'info'    => 'dashicons-info-outline',
			'success' => 'dashicons-yes-alt',
			'warning' => 'dashicons-warning',
			'danger'  => 'dashicons-dismiss',
			'accent'  => 'dashicons-lightbulb',
		);
		$style = isset( $icons[ $style ] ) ? $style : 'info';
		$icon  = $this->opt( 'icon', $icons[ $style ] );

		echo '<div class="agf-notice agf-notice-' . esc_attr( $style ) . '">';
		echo AppGlut::icon( $icon ); // phpcs:ignore WordPress.Security.EscapeOutput
		echo '<div class="agf-notice-body">' . wp_kses_post( $this->opt( 'content', '' ) ) . '</div>';
		if ( $this->opt( 'dismissible' ) ) {
			echo '<button type="button" class="agf-notice-close" aria-label="' . esc_attr__( 'Dismiss', 'shopglut' ) . '">&times;</button>';
		}
		echo '</div>';
	}
}

/**
 * Compact one-line message (Codestar compatible). Options: content,
 * style (normal / info / success / warning / danger / accent).
 */
class AppGlut_Field_submessage extends AppGlut_Field {

	public function layout() {
		return 'full';
	}

	public function render() {
		$style = $this->opt( 'style', 'normal' );
		$style = in_array( $style, array( 'normal', 'info', 'success', 'warning', 'danger', 'accent' ), true ) ? $style : 'normal';
		echo '<div class="agf-submessage agf-submessage-' . esc_attr( $style ) . '">' . wp_kses_post( $this->opt( 'content', '' ) ) . '</div>';
	}
}

/**
 * Free HTML content (passed through wp_kses_post). Options: content.
 */
class AppGlut_Field_content extends AppGlut_Field {

	public function render() {
		echo '<div class="agf-content">' . wp_kses_post( $this->opt( 'content', '' ) ) . '</div>';
	}
}

/**
 * Horizontal separator. Options: content (optional centered label).
 */
class AppGlut_Field_divider extends AppGlut_Field {

	public function layout() {
		return 'full';
	}

	public function render() {
		$label = $this->opt( 'content', '' );
		echo '<div class="agf-divider' . ( $label ? ' agf-divider-label' : '' ) . '">' . ( $label ? '<span>' . esc_html( $label ) . '</span>' : '' ) . '</div>';
	}
}

/**
 * Developer callback. Options: function (callable), args (array).
 */
class AppGlut_Field_callback extends AppGlut_Field {

	public function render() {
		$callback = $this->opt( 'function' );
		if ( is_callable( $callback ) ) {
			call_user_func( $callback, $this->opt( 'args', array() ), $this->field );
		}
	}
}

/**
 * Link styled as a button. Options: label, url, target, style (primary|secondary|ghost), icon.
 */
class AppGlut_Field_button extends AppGlut_Field {

	public function render() {
		$style = in_array( $this->opt( 'style' ), array( 'primary', 'secondary', 'ghost' ), true ) ? $this->opt( 'style' ) : 'secondary';
		echo '<a class="agf-btn agf-btn-' . esc_attr( $style ) . '" href="' . esc_url( $this->opt( 'url', '#' ) ) . '"' . ( $this->opt( 'target' ) ? ' target="_blank" rel="noopener noreferrer"' : '' ) . $this->attrs() . '>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo AppGlut::icon( $this->opt( 'icon' ) ) . '<span>' . esc_html( $this->opt( 'label', __( 'Open', 'shopglut' ) ) ) . '</span></a>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
}
