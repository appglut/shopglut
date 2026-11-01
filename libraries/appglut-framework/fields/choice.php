<?php
/**
 * Fields: checkbox, radio, switcher, button_set, image_select, palette, select, tags, rating.
 *
 * @package AppGlut_Framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Checkbox. Without 'options' it is a single on/off box (bool); with options a group (array).
 * Options: label, options, inline (bool), style (default|card).
 */
class AppGlut_Field_checkbox extends AppGlut_Field {

	public function render() {
		$options = $this->options();

		if ( empty( $this->field['options'] ) ) {
			echo '<label class="agf-check">';
			echo '<input type="hidden" name="' . esc_attr( $this->name() ) . '" value="0" data-agf-skip>';
			echo '<input type="checkbox" name="' . esc_attr( $this->name() ) . '" value="1"' . checked( ! empty( $this->value ), true, false ) . $this->attrs() . '>'; // phpcs:ignore WordPress.Security.EscapeOutput
			echo '<span class="agf-check-box" aria-hidden="true"></span>';
			if ( $this->opt( 'label' ) ) {
				echo '<span class="agf-check-label">' . wp_kses_post( $this->opt( 'label' ) ) . '</span>';
			}
			echo '</label>';
			return;
		}

		$current = array_map( 'strval', (array) $this->value );
		$style   = 'card' === $this->opt( 'style' ) ? ' agf-choices-card' : '';

		echo '<div class="agf-choices' . ( $this->opt( 'inline' ) ? ' agf-choices-inline' : '' ) . esc_attr( $style ) . '">';
		foreach ( $options as $key => $label ) {
			echo '<label class="agf-check">';
			echo '<input type="checkbox" name="' . esc_attr( $this->name( '[]' ) ) . '" value="' . esc_attr( $key ) . '"' . checked( in_array( (string) $key, $current, true ), true, false ) . $this->attrs() . '>'; // phpcs:ignore WordPress.Security.EscapeOutput
			echo '<span class="agf-check-box" aria-hidden="true"></span>';
			echo '<span class="agf-check-label">' . wp_kses_post( $label ) . '</span>';
			echo '</label>';
		}
		if ( ! $options ) {
			echo '<span class="agf-muted">' . esc_html__( 'No options available.', 'shopglut' ) . '</span>';
		}
		echo '</div>';
	}

	public function sanitize( $value ) {
		if ( empty( $this->field['options'] ) ) {
			return ! empty( $value ) && '0' !== $value && 'false' !== $value;
		}
		return $this->whitelist( $value, true );
	}
}

/**
 * Radio buttons. Options: options, inline, style (default|card).
 */
class AppGlut_Field_radio extends AppGlut_Field {

	public function render() {
		$style = 'card' === $this->opt( 'style' ) ? ' agf-choices-card' : '';
		echo '<div class="agf-choices' . ( $this->opt( 'inline' ) ? ' agf-choices-inline' : '' ) . esc_attr( $style ) . '">';
		foreach ( $this->options() as $key => $label ) {
			echo '<label class="agf-check agf-radio">';
			echo '<input type="radio" name="' . esc_attr( $this->name() ) . '" value="' . esc_attr( $key ) . '"' . checked( (string) $this->value, (string) $key, false ) . $this->attrs() . '>'; // phpcs:ignore WordPress.Security.EscapeOutput
			echo '<span class="agf-check-box" aria-hidden="true"></span>';
			echo '<span class="agf-check-label">' . wp_kses_post( $label ) . '</span>';
			echo '</label>';
		}
		echo '</div>';
	}

	public function sanitize( $value ) {
		return $this->whitelist( $value );
	}
}

/**
 * Toggle switch (bool). Options: text_on, text_off, label.
 */
class AppGlut_Field_switcher extends AppGlut_Field {

	public function render() {
		$on  = $this->opt( 'text_on', '' );
		$off = $this->opt( 'text_off', '' );

		echo '<label class="agf-switch' . ( ( $on || $off ) ? ' agf-switch-text' : '' ) . '">';
		echo '<input type="hidden" name="' . esc_attr( $this->name() ) . '" value="0" data-agf-skip>';
		echo '<input type="checkbox" class="agf-switch-input" name="' . esc_attr( $this->name() ) . '" value="1"' . checked( ! empty( $this->value ), true, false ) . $this->attrs() . '>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '<span class="agf-switch-track" aria-hidden="true">';
		if ( $on || $off ) {
			echo '<span class="agf-switch-on">' . esc_html( $on ) . '</span><span class="agf-switch-off">' . esc_html( $off ) . '</span>';
		}
		echo '<span class="agf-switch-thumb"></span></span>';
		if ( $this->opt( 'label' ) ) {
			echo '<span class="agf-switch-label">' . wp_kses_post( $this->opt( 'label' ) ) . '</span>';
		}
		echo '</label>';
	}

	public function sanitize( $value ) {
		return ! empty( $value ) && '0' !== $value && 'false' !== $value;
	}
}

/**
 * Segmented buttons. Options: options (key => label, label may include an icon class via array( 'label', 'icon' )), multiple.
 */
class AppGlut_Field_button_set extends AppGlut_Field {

	public function render() {
		$multiple = (bool) $this->opt( 'multiple' );
		$current  = array_map( 'strval', (array) $this->value );
		$type     = $multiple ? 'checkbox' : 'radio';
		$name     = $multiple ? $this->name( '[]' ) : $this->name();

		echo '<div class="agf-button-set" role="group">';
		foreach ( $this->options() as $key => $label ) {
			$icon = '';
			if ( is_array( $label ) ) {
				$icon  = isset( $label['icon'] ) ? $label['icon'] : '';
				$label = isset( $label['label'] ) ? $label['label'] : '';
			}
			echo '<label class="agf-button-set-item">';
			echo '<input type="' . esc_attr( $type ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $key ) . '"' . checked( in_array( (string) $key, $current, true ), true, false ) . '>';
			echo '<span>' . AppGlut::icon( $icon ) . ( '' !== $label ? '<span>' . esc_html( $label ) . '</span>' : '' ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
			echo '</label>';
		}
		echo '</div>';
	}

	public function sanitize( $value ) {
		return $this->whitelist( $value, (bool) $this->opt( 'multiple' ) );
	}
}

/**
 * Image choices. Options: options (key => image URL or array( 'image', 'label' )), multiple, width (px).
 */
class AppGlut_Field_image_select extends AppGlut_Field {

	public function render() {
		$multiple = (bool) $this->opt( 'multiple' );
		$current  = array_map( 'strval', (array) $this->value );
		$width    = (int) $this->opt( 'width', 0 );

		echo '<div class="agf-image-select"' . ( $width ? ' style="--agf-img-w:' . esc_attr( $width ) . 'px"' : '' ) . '>';
		foreach ( $this->options() as $key => $option ) {
			$image = is_array( $option ) ? ( isset( $option['image'] ) ? $option['image'] : '' ) : $option;
			$label = is_array( $option ) && isset( $option['label'] ) ? $option['label'] : '';

			echo '<label class="agf-image-select-item">';
			echo '<input type="' . ( $multiple ? 'checkbox' : 'radio' ) . '" name="' . esc_attr( $multiple ? $this->name( '[]' ) : $this->name() ) . '" value="' . esc_attr( $key ) . '"' . checked( in_array( (string) $key, $current, true ), true, false ) . '>';
			echo '<span class="agf-image-select-frame"><img src="' . esc_url( $image ) . '" alt="' . esc_attr( $label ? $label : $key ) . '" loading="lazy"><span class="agf-image-select-check" aria-hidden="true"></span></span>';
			if ( $label ) {
				echo '<span class="agf-image-select-label">' . esc_html( $label ) . '</span>';
			}
			echo '</label>';
		}
		echo '</div>';
	}

	public function sanitize( $value ) {
		return $this->whitelist( $value, (bool) $this->opt( 'multiple' ) );
	}
}

/**
 * Color palettes. Options: options (key => array of colors).
 */
class AppGlut_Field_palette extends AppGlut_Field {

	public function render() {
		echo '<div class="agf-palette">';
		foreach ( $this->options() as $key => $colors ) {
			echo '<label class="agf-palette-item" title="' . esc_attr( $key ) . '">';
			echo '<input type="radio" name="' . esc_attr( $this->name() ) . '" value="' . esc_attr( $key ) . '"' . checked( (string) $this->value, (string) $key, false ) . '>';
			echo '<span class="agf-palette-swatches">';
			foreach ( (array) $colors as $color ) {
				echo '<span style="background:' . esc_attr( appglut_sanitize_color( $color ) ) . '"></span>';
			}
			echo '</span></label>';
		}
		echo '</div>';
	}

	public function sanitize( $value ) {
		return $this->whitelist( $value );
	}
}

/**
 * Select. Options: options (array, optgroups, data source string or callable),
 * placeholder, multiple, chosen (searchable UI), ajax (remote search), query_args.
 */
class AppGlut_Field_select extends AppGlut_Field {

	public function render() {
		$multiple = (bool) $this->opt( 'multiple' );
		$ajax     = (bool) $this->opt( 'ajax' ) && is_string( $this->opt( 'options' ) );
		$enhance  = $ajax || $this->opt( 'chosen' ) || $multiple;
		$current  = array_map( 'strval', (array) $this->value );
		$options  = $this->options();

		$extra = array(
			'name'             => $multiple ? $this->name( '[]' ) : $this->name(),
			'class'            => 'agf-select' . ( $enhance ? ' agf-select-enhance' : '' ),
			'multiple'         => $multiple,
			'data-placeholder' => $this->opt( 'placeholder', '' ),
			'data-search'      => ( $ajax || $this->opt( 'chosen' ) ) ? '1' : false,
		);

		if ( $ajax ) {
			$query               = array(
				'type' => $this->opt( 'options' ),
				'args' => (array) $this->opt( 'query_args', array() ),
			);
			$extra['data-query'] = wp_json_encode( $query );
			$extra['data-sig']   = AppGlut::sign( $query );
		}

		// Placeholder is passed separately so attrs() does not set it on <select>.
		$field_placeholder = $this->opt( 'placeholder' );
		unset( $this->field['placeholder'] );

		echo '<select' . $this->attrs( $extra ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput
		if ( ! $multiple && null !== $field_placeholder ) {
			echo '<option value="">' . esc_html( $field_placeholder ) . '</option>';
		}
		foreach ( $options as $key => $label ) {
			if ( is_array( $label ) ) {
				echo '<optgroup label="' . esc_attr( $key ) . '">';
				foreach ( $label as $sub_key => $sub_label ) {
					echo '<option value="' . esc_attr( $sub_key ) . '"' . selected( in_array( (string) $sub_key, $current, true ), true, false ) . '>' . esc_html( $sub_label ) . '</option>';
				}
				echo '</optgroup>';
			} else {
				echo '<option value="' . esc_attr( $key ) . '"' . selected( in_array( (string) $key, $current, true ), true, false ) . '>' . esc_html( $label ) . '</option>';
			}
		}
		echo '</select>';

		$this->field['placeholder'] = $field_placeholder;
	}

	public function sanitize( $value ) {
		return $this->whitelist( $value, (bool) $this->opt( 'multiple' ) );
	}
}

/**
 * Tag / token input stored as an array of strings. Options: placeholder, max, suggestions (array).
 */
class AppGlut_Field_tags extends AppGlut_Field {

	public function render() {
		$list = '';
		if ( $this->opt( 'suggestions' ) ) {
			$list = AppGlut::uid( 'agf-list' );
			echo '<datalist id="' . esc_attr( $list ) . '">';
			foreach ( (array) $this->opt( 'suggestions' ) as $suggestion ) {
				echo '<option value="' . esc_attr( $suggestion ) . '"></option>';
			}
			echo '</datalist>';
		}

		echo '<div class="agf-tags" data-name="' . esc_attr( $this->name( '[]' ) ) . '" data-max="' . esc_attr( (int) $this->opt( 'max', 0 ) ) . '">';
		foreach ( array_filter( (array) $this->value, 'strlen' ) as $tag ) {
			echo '<span class="agf-tag"><span>' . esc_html( $tag ) . '</span><input type="hidden" name="' . esc_attr( $this->name( '[]' ) ) . '" value="' . esc_attr( $tag ) . '"><button type="button" class="agf-tag-remove" aria-label="' . esc_attr__( 'Remove', 'shopglut' ) . '">&times;</button></span>';
		}
		echo '<input type="text" class="agf-tags-input" placeholder="' . esc_attr( $this->opt( 'placeholder', __( 'Add and press Enter…', 'shopglut' ) ) ) . '"' . ( $list ? ' list="' . esc_attr( $list ) . '"' : '' ) . '>';
		echo '</div>';
	}

	public function sanitize( $value ) {
		$tags = array_values( array_unique( array_filter( array_map( 'sanitize_text_field', array_map( 'strval', (array) $value ) ), 'strlen' ) ) );
		$max  = (int) $this->opt( 'max', 0 );
		return $max ? array_slice( $tags, 0, $max ) : $tags;
	}
}

/**
 * Star rating (int). Options: max (default 5).
 */
class AppGlut_Field_rating extends AppGlut_Field {

	public function render() {
		$max = max( 1, (int) $this->opt( 'max', 5 ) );
		echo '<div class="agf-rating" role="radiogroup">';
		echo '<input type="radio" class="agf-rating-none" name="' . esc_attr( $this->name() ) . '" value="0"' . checked( (int) $this->value, 0, false ) . '>';
		for ( $i = $max; $i >= 1; $i-- ) {
			/* translators: %d: number of stars */
			echo '<label title="' . esc_attr( sprintf( _n( '%d star', '%d stars', $i, 'shopglut' ), $i ) ) . '"><input type="radio" name="' . esc_attr( $this->name() ) . '" value="' . esc_attr( $i ) . '"' . checked( (int) $this->value, $i, false ) . '>' . AppGlut::icon( 'dashicons-star-filled' ) . '</label>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		echo '</div>';
	}

	public function sanitize( $value ) {
		return max( 0, min( (int) $this->opt( 'max', 5 ), (int) $value ) );
	}
}
