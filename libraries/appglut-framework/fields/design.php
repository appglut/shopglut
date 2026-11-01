<?php
/**
 * Fields: color, color_group, gradient, box_shadow, border, spacing, dimensions,
 * typography, background, slider, spinner.
 *
 * @package AppGlut_Framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

AppGlut::field_class( 'number' );

/**
 * Color picker with alpha, presets and reset. Options: alpha (default true), palette (array of colors).
 */
class AppGlut_Field_color extends AppGlut_Field {

	public function render() {
		echo self::control( $this->name(), $this->value, $this->field ); // phpcs:ignore WordPress.Security.EscapeOutput
	}

	/** Reusable color control markup. */
	public static function control( $name, $value, $args = array() ) {
		$value   = is_scalar( $value ) ? (string) $value : '';
		$alpha   = ! isset( $args['alpha'] ) || $args['alpha'];
		$default = isset( $args['default'] ) && is_scalar( $args['default'] ) ? (string) $args['default'] : '';

		$html  = '<div class="agf-color" data-alpha="' . ( $alpha ? '1' : '0' ) . '" data-default="' . esc_attr( $default ) . '">';
		$html .= '<label class="agf-color-swatch"><span class="agf-color-chip" style="background:' . esc_attr( $value ) . '"></span>';
		$html .= '<input type="color" class="agf-color-native" tabindex="-1" aria-hidden="true" data-agf-skip></label>';
		$html .= '<input type="text" class="agf-input agf-color-text" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" placeholder="' . esc_attr( $alpha ? 'rgba / #hex' : '#hex' ) . '" spellcheck="false">';
		if ( $alpha ) {
			$html .= '<input type="range" class="agf-color-alpha" min="0" max="100" step="1" value="100" aria-label="' . esc_attr__( 'Opacity', 'shopglut' ) . '" data-agf-skip>';
		}
		$html .= '<button type="button" class="agf-color-reset" title="' . esc_attr__( 'Reset', 'shopglut' ) . '">' . AppGlut::icon( 'dashicons-image-rotate' ) . '</button>';
		if ( ! empty( $args['palette'] ) ) {
			$html .= '<div class="agf-color-presets">';
			foreach ( (array) $args['palette'] as $preset ) {
				$preset = appglut_sanitize_color( $preset );
				$html  .= '<button type="button" class="agf-color-preset" data-color="' . esc_attr( $preset ) . '" style="background:' . esc_attr( $preset ) . '" title="' . esc_attr( $preset ) . '"></button>';
			}
			$html .= '</div>';
		}
		return $html . '</div>';
	}

	public function sanitize( $value ) {
		return appglut_sanitize_color( $value );
	}
}

/**
 * Several named colors. Options: options (key => label), alpha.
 */
class AppGlut_Field_color_group extends AppGlut_Field {

	public function render() {
		$value = (array) $this->value;
		echo '<div class="agf-color-group">';
		foreach ( (array) $this->opt( 'options', array() ) as $key => $label ) {
			echo '<div class="agf-color-group-item"><div class="agf-mini-label">' . esc_html( $label ) . '</div>';
			echo AppGlut_Field_color::control( $this->name( '[' . $key . ']' ), isset( $value[ $key ] ) ? $value[ $key ] : '', array( 'alpha' => $this->opt( 'alpha', true ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput
			echo '</div>';
		}
		echo '</div>';
	}

	public function sanitize( $value ) {
		return $this->sanitize_map( $value, array_fill_keys( array_keys( (array) $this->opt( 'options', array() ) ), 'color' ) );
	}

	public function css( $value, $rule ) {
		// 'output' => array( 'selector' => '.x', 'property' => 'color', 'key' => 'normal' ).
		$key = isset( $rule['key'] ) ? $rule['key'] : '';
		return ( $key && ! empty( $value[ $key ] ) && $rule['property'] ) ? array( $rule['property'] => $value[ $key ] ) : array();
	}
}

/**
 * Link colors: normal, hover, active, visited, focus. Turn states on/off with
 * 'color' / 'hover' (default on) and 'active' / 'visited' / 'focus' (default off).
 * Output writes one rule per state: a{color} a:hover{color} ...
 */
class AppGlut_Field_link_color extends AppGlut_Field {

	public function states() {
		$labels = array(
			'color'   => __( 'Normal', 'shopglut' ),
			'hover'   => __( 'Hover', 'shopglut' ),
			'active'  => __( 'Active', 'shopglut' ),
			'visited' => __( 'Visited', 'shopglut' ),
			'focus'   => __( 'Focus', 'shopglut' ),
		);
		$on = array(
			'color'   => true,
			'hover'   => true,
			'active'  => false,
			'visited' => false,
			'focus'   => false,
		);
		$out = array();
		foreach ( $labels as $key => $label ) {
			if ( $this->opt( $key, $on[ $key ] ) ) {
				$out[ $key ] = $label;
			}
		}
		return $out;
	}

	// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- Helpers return pre-escaped markup.
	public function render() {
		$value    = (array) $this->value;
		$defaults = (array) $this->opt( 'default', array() );
		echo '<div class="agf-color-group agf-link-color">';
		foreach ( $this->states() as $key => $label ) {
			echo '<div class="agf-color-group-item"><div class="agf-mini-label">' . esc_html( $label ) . '</div>';
			echo AppGlut_Field_color::control( // phpcs:ignore WordPress.Security.EscapeOutput
				$this->name( '[' . $key . ']' ),
				isset( $value[ $key ] ) ? $value[ $key ] : '',
				array(
					'alpha'   => $this->opt( 'alpha', true ),
					'default' => isset( $defaults[ $key ] ) ? $defaults[ $key ] : '',
				)
			);
			echo '</div>';
		}
		echo '</div>';
	}
	// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped

	public function sanitize( $value ) {
		return $this->sanitize_map( $value, array_fill_keys( array_keys( $this->states() ), 'color' ) );
	}

	public function css( $value, $rule ) {
		$value    = (array) $value;
		$property = ! empty( $rule['property'] ) ? $rule['property'] : 'color';
		$out      = array();
		if ( ! empty( $value['color'] ) ) {
			$out[ $property ] = $value['color'];
		}
		foreach ( array( 'hover', 'active', 'visited', 'focus' ) as $state ) {
			if ( ! empty( $value[ $state ] ) ) {
				$out[ ':' . $state ] = array( $property => $value[ $state ] );
			}
		}
		return $out;
	}
}

/**
 * Two-stop gradient with live preview.
 */
class AppGlut_Field_gradient extends AppGlut_Field {

	public function layout() {
		return 'stacked';
	}

	public function render() {
		$v = wp_parse_args(
			(array) $this->value,
			array(
				'type'      => 'linear',
				'angle'     => 135,
				'start'     => '',
				'end'       => '',
				'start_pos' => 0,
				'end_pos'   => 100,
			)
		);

		echo '<div class="agf-gradient">';
		echo '<div class="agf-gradient-preview" style="background-image:' . esc_attr( appglut_gradient_css( $v ) ) . '"></div>';
		echo '<div class="agf-inline-controls">';
		echo '<div class="agf-mini"><span class="agf-mini-label">' . esc_html__( 'Start', 'shopglut' ) . '</span>' . AppGlut_Field_color::control( $this->name( '[start]' ), $v['start'] ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '<div class="agf-mini"><span class="agf-mini-label">' . esc_html__( 'End', 'shopglut' ) . '</span>' . AppGlut_Field_color::control( $this->name( '[end]' ), $v['end'] ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '<div class="agf-mini"><span class="agf-mini-label">' . esc_html__( 'Type', 'shopglut' ) . '</span>' . self::select_html( $this->name( '[type]' ), array( 'linear' => __( 'Linear', 'shopglut' ), 'radial' => __( 'Radial', 'shopglut' ) ), $v['type'], array( 'class' => 'agf-gradient-type' ) ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '</div><div class="agf-inline-controls">';
		foreach ( array(
			'angle'     => array( __( 'Angle', 'shopglut' ), 0, 360, '°' ),
			'start_pos' => array( __( 'Start position', 'shopglut' ), 0, 100, '%' ),
			'end_pos'   => array( __( 'End position', 'shopglut' ), 0, 100, '%' ),
		) as $key => $conf ) {
			echo '<label class="agf-mini agf-mini-range"><span class="agf-mini-label">' . esc_html( $conf[0] ) . ' <output>' . esc_html( $v[ $key ] . $conf[3] ) . '</output></span>';
			echo '<input type="range" class="agf-range" data-key="' . esc_attr( $key ) . '" data-suffix="' . esc_attr( $conf[3] ) . '" name="' . esc_attr( $this->name( '[' . $key . ']' ) ) . '" min="' . (int) $conf[1] . '" max="' . (int) $conf[2] . '" value="' . esc_attr( (int) $v[ $key ] ) . '"></label>';
		}
		echo '</div></div>';
	}

	public function sanitize( $value ) {
		$clean         = $this->sanitize_map(
			$value,
			array(
				'type'      => 'key',
				'angle'     => 'int',
				'start'     => 'color',
				'end'       => 'color',
				'start_pos' => 'int',
				'end_pos'   => 'int',
			)
		);
		$clean['type'] = 'radial' === $clean['type'] ? 'radial' : 'linear';
		foreach ( array( 'angle' => 135, 'start_pos' => 0, 'end_pos' => 100 ) as $key => $fallback ) {
			$clean[ $key ] = '' === $clean[ $key ] ? $fallback : max( 0, min( 'angle' === $key ? 360 : 100, $clean[ $key ] ) );
		}
		return $clean;
	}

	public function css( $value, $rule ) {
		$css = appglut_gradient_css( (array) $value );
		return $css ? array( $rule['property'] ? $rule['property'] : 'background-image' => $css ) : array();
	}
}

/**
 * Box shadow with preview.
 */
class AppGlut_Field_box_shadow extends AppGlut_Field {

	public function render() {
		$v = wp_parse_args(
			(array) $this->value,
			array(
				'x'      => 0,
				'y'      => 4,
				'blur'   => 12,
				'spread' => 0,
				'color'  => '',
				'inset'  => false,
			)
		);

		echo '<div class="agf-box-shadow">';
		echo '<div class="agf-box-shadow-stage"><div class="agf-box-shadow-preview" style="box-shadow:' . esc_attr( appglut_box_shadow_css( $v ) ) . '"></div></div>';
		echo '<div class="agf-box-shadow-controls"><div class="agf-inline-controls">';
		foreach ( array(
			'x'      => __( 'X', 'shopglut' ),
			'y'      => __( 'Y', 'shopglut' ),
			'blur'   => __( 'Blur', 'shopglut' ),
			'spread' => __( 'Spread', 'shopglut' ),
		) as $key => $label ) {
			echo '<label class="agf-mini"><span class="agf-mini-label">' . esc_html( $label ) . '</span><span class="agf-input-group agf-input-group-xs"><input type="number" class="agf-input" data-key="' . esc_attr( $key ) . '" name="' . esc_attr( $this->name( '[' . $key . ']' ) ) . '" value="' . esc_attr( $v[ $key ] ) . '"><span class="agf-addon">px</span></span></label>';
		}
		echo '</div><div class="agf-inline-controls">';
		echo '<div class="agf-mini">' . AppGlut_Field_color::control( $this->name( '[color]' ), $v['color'] ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '<label class="agf-check"><input type="hidden" name="' . esc_attr( $this->name( '[inset]' ) ) . '" value="0" data-agf-skip><input type="checkbox" data-key="inset" name="' . esc_attr( $this->name( '[inset]' ) ) . '" value="1"' . checked( ! empty( $v['inset'] ), true, false ) . '><span class="agf-check-box"></span><span class="agf-check-label">' . esc_html__( 'Inset', 'shopglut' ) . '</span></label>';
		echo '</div></div></div>';
	}

	public function sanitize( $value ) {
		return $this->sanitize_map(
			$value,
			array(
				'x'      => 'number',
				'y'      => 'number',
				'blur'   => 'number',
				'spread' => 'number',
				'color'  => 'color',
				'inset'  => 'bool',
			)
		);
	}

	public function css( $value, $rule ) {
		$css = appglut_box_shadow_css( (array) $value );
		return $css ? array( $rule['property'] ? $rule['property'] : 'box-shadow' => $css ) : array();
	}
}

/**
 * Shared base for side-based inputs (spacing / border).
 */
abstract class AppGlut_Field_sides extends AppGlut_Field {

	protected function sides() {
		$labels = array(
			'top'    => __( 'Top', 'shopglut' ),
			'right'  => __( 'Right', 'shopglut' ),
			'bottom' => __( 'Bottom', 'shopglut' ),
			'left'   => __( 'Left', 'shopglut' ),
		);
		if ( $this->opt( 'all' ) ) {
			return array( 'all' => __( 'All', 'shopglut' ) );
		}
		$enabled = array();
		foreach ( $labels as $key => $label ) {
			if ( false !== $this->opt( $key, true ) ) {
				$enabled[ $key ] = $label;
			}
		}
		return $enabled;
	}

	protected function render_sides( $value, $units ) {
		$sides = $this->sides();
		echo '<div class="agf-sides' . ( count( $sides ) === 4 ? ' agf-sides-linkable' : '' ) . '">';
		foreach ( $sides as $key => $label ) {
			echo '<label class="agf-mini"><span class="agf-mini-label">' . esc_html( $label ) . '</span><input type="number" step="any" class="agf-input agf-side-input" name="' . esc_attr( $this->name( '[' . $key . ']' ) ) . '" value="' . esc_attr( isset( $value[ $key ] ) ? $value[ $key ] : '' ) . '"></label>';
		}
		if ( count( $sides ) === 4 ) {
			echo '<button type="button" class="agf-icon-btn agf-link-sides" aria-pressed="false" title="' . esc_attr__( 'Link values', 'shopglut' ) . '">' . AppGlut::icon( 'dashicons-admin-links' ) . '</button>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		if ( $units ) {
			echo '<div class="agf-mini"><span class="agf-mini-label">' . esc_html__( 'Unit', 'shopglut' ) . '</span>' . $this->unit_html( $this->name( '[unit]' ), $units, isset( $value['unit'] ) ? $value['unit'] : '' ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		echo '</div>';
	}

	protected function sides_map() {
		$map = array( 'unit' => 'key' );
		foreach ( array_keys( $this->sides() ) as $side ) {
			$map[ $side ] = 'number';
		}
		return $map;
	}
}

/**
 * Padding / margin. Options: units, all (single input), top/right/bottom/left (bool).
 * Output property defaults to "padding".
 */
class AppGlut_Field_spacing extends AppGlut_Field_sides {

	public function render() {
		$this->render_sides( (array) $this->value, $this->opt( 'units', array( 'px', 'em', 'rem', '%' ) ) );
	}

	public function sanitize( $value ) {
		return $this->sanitize_map( $value, $this->sides_map() );
	}

	public function css( $value, $rule ) {
		$value = (array) $value;
		$prop  = $rule['property'] ? $rule['property'] : 'padding';
		$unit  = ! empty( $value['unit'] ) ? $value['unit'] : 'px';
		$out   = array();
		if ( isset( $value['all'] ) && '' !== $value['all'] ) {
			$out[ $prop ] = $value['all'] . $unit;
		}
		foreach ( array( 'top', 'right', 'bottom', 'left' ) as $side ) {
			if ( isset( $value[ $side ] ) && '' !== $value[ $side ] ) {
				$out[ $prop . '-' . $side ] = $value[ $side ] . $unit;
			}
		}
		return $out;
	}
}

/**
 * Border. Options: all (one width), style (bool), color (bool), radius (bool), units.
 */
class AppGlut_Field_border extends AppGlut_Field_sides {

	public function layout() {
		return 'inline';
	}

	public function render() {
		$value = (array) $this->value;
		echo '<div class="agf-border">';
		$this->render_sides( $value, $this->opt( 'units', array( 'px' ) ) );
		echo '<div class="agf-inline-controls">';
		if ( false !== $this->opt( 'style', true ) ) {
			$styles = array(
				'solid'  => __( 'Solid', 'shopglut' ),
				'dashed' => __( 'Dashed', 'shopglut' ),
				'dotted' => __( 'Dotted', 'shopglut' ),
				'double' => __( 'Double', 'shopglut' ),
				'groove' => __( 'Groove', 'shopglut' ),
				'ridge'  => __( 'Ridge', 'shopglut' ),
				'inset'  => __( 'Inset', 'shopglut' ),
				'outset' => __( 'Outset', 'shopglut' ),
				'none'   => __( 'None', 'shopglut' ),
			);
			echo '<div class="agf-mini"><span class="agf-mini-label">' . esc_html__( 'Style', 'shopglut' ) . '</span>' . self::select_html( $this->name( '[style]' ), $styles, isset( $value['style'] ) ? $value['style'] : 'solid' ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		if ( false !== $this->opt( 'color', true ) ) {
			echo '<div class="agf-mini"><span class="agf-mini-label">' . esc_html__( 'Color', 'shopglut' ) . '</span>' . AppGlut_Field_color::control( $this->name( '[color]' ), isset( $value['color'] ) ? $value['color'] : '' ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		if ( $this->opt( 'radius' ) ) {
			echo '<label class="agf-mini"><span class="agf-mini-label">' . esc_html__( 'Radius', 'shopglut' ) . '</span><span class="agf-input-group agf-input-group-xs"><input type="number" class="agf-input" min="0" name="' . esc_attr( $this->name( '[radius]' ) ) . '" value="' . esc_attr( isset( $value['radius'] ) ? $value['radius'] : '' ) . '"><span class="agf-addon">px</span></span></label>';
		}
		echo '</div></div>';
	}

	public function sanitize( $value ) {
		$clean = $this->sanitize_map( $value, array_merge( $this->sides_map(), array( 'style' => 'key', 'color' => 'color', 'radius' => 'number' ) ) );
		if ( ! in_array( $clean['style'], array( 'solid', 'dashed', 'dotted', 'double', 'groove', 'ridge', 'inset', 'outset', 'none' ), true ) ) {
			$clean['style'] = 'solid';
		}
		return $clean;
	}

	public function css( $value, $rule ) {
		$value = (array) $value;
		$unit  = ! empty( $value['unit'] ) ? $value['unit'] : 'px';
		$out   = array();
		if ( isset( $value['all'] ) && '' !== $value['all'] ) {
			$out['border-width'] = $value['all'] . $unit;
		}
		foreach ( array( 'top', 'right', 'bottom', 'left' ) as $side ) {
			if ( isset( $value[ $side ] ) && '' !== $value[ $side ] ) {
				$out[ 'border-' . $side . '-width' ] = $value[ $side ] . $unit;
			}
		}
		if ( $out && ! empty( $value['style'] ) ) {
			$out['border-style'] = $value['style'];
		}
		if ( ! empty( $value['color'] ) ) {
			$out['border-color'] = $value['color'];
		}
		if ( isset( $value['radius'] ) && '' !== $value['radius'] ) {
			$out['border-radius'] = $value['radius'] . 'px';
		}
		return $out;
	}
}

/**
 * Width / height. Options: width (bool), height (bool), units.
 */
class AppGlut_Field_dimensions extends AppGlut_Field {

	public function render() {
		$value = (array) $this->value;
		echo '<div class="agf-sides">';
		foreach ( array(
			'width'  => __( 'Width', 'shopglut' ),
			'height' => __( 'Height', 'shopglut' ),
		) as $key => $label ) {
			if ( false === $this->opt( $key, true ) ) {
				continue;
			}
			echo '<label class="agf-mini"><span class="agf-mini-label">' . esc_html( $label ) . '</span><input type="number" step="any" min="0" class="agf-input" name="' . esc_attr( $this->name( '[' . $key . ']' ) ) . '" value="' . esc_attr( isset( $value[ $key ] ) ? $value[ $key ] : '' ) . '"></label>';
		}
		echo '<div class="agf-mini"><span class="agf-mini-label">' . esc_html__( 'Unit', 'shopglut' ) . '</span>' . $this->unit_html( $this->name( '[unit]' ), $this->opt( 'units', array( 'px', '%', 'em', 'rem', 'vw', 'vh' ) ), isset( $value['unit'] ) ? $value['unit'] : '' ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '</div>';
	}

	public function sanitize( $value ) {
		return $this->sanitize_map(
			$value,
			array(
				'width'  => 'number',
				'height' => 'number',
				'unit'   => 'key',
			)
		);
	}

	public function css( $value, $rule ) {
		$value = (array) $value;
		$unit  = ! empty( $value['unit'] ) ? $value['unit'] : 'px';
		$out   = array();
		foreach ( array( 'width', 'height' ) as $key ) {
			if ( isset( $value[ $key ] ) && '' !== $value[ $key ] ) {
				$out[ ( $rule['property'] ? $rule['property'] . '-' : '' ) . $key ] = $value[ $key ] . $unit;
			}
		}
		return $out;
	}
}

/**
 * Typography with Google Fonts and live preview.
 *
 * Toggle parts with: font_family, font_weight, font_style, font_size, line_height,
 * letter_spacing, text_align, text_transform, text_decoration, color, preview (all bool),
 * preview_text (string).
 */
class AppGlut_Field_typography extends AppGlut_Field {

	public function layout() {
		return 'stacked';
	}

	public static function assets( $field ) {
		return array( 'fonts' => true );
	}

	private function on( $part, $default = true ) {
		return (bool) $this->opt( $part, $default );
	}

	// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- Helpers return pre-escaped markup.
	public function render() {
		$v = wp_parse_args(
			(array) $this->value,
			array(
				'font-family'         => '',
				'font-weight'         => '',
				'font-style'          => '',
				'font-size'           => '',
				'unit'                => 'px',
				'line-height'         => '',
				'line-height-unit'    => '',
				'letter-spacing'      => '',
				'letter-spacing-unit' => 'px',
				'text-align'          => '',
				'text-transform'      => '',
				'text-decoration'     => '',
				'color'               => '',
			)
		);

		$weights = array( '' => __( 'Default', 'shopglut' ) );
		foreach ( range( 100, 900, 100 ) as $w ) {
			$weights[ $w ] = (string) $w;
		}

		echo '<div class="agf-typography">';
		echo '<div class="agf-typography-grid">';

		if ( $this->on( 'font_family' ) ) {
			echo '<label class="agf-mini agf-span-2"><span class="agf-mini-label">' . esc_html__( 'Font family', 'shopglut' ) . '</span>';
			echo '<input type="text" class="agf-input agf-font-family" list="agf-fonts-list" name="' . esc_attr( $this->name( '[font-family]' ) ) . '" value="' . esc_attr( $v['font-family'] ) . '" placeholder="' . esc_attr__( 'Search fonts…', 'shopglut' ) . '" autocomplete="off"></label>';
		}
		if ( $this->on( 'font_weight' ) ) {
			echo '<div class="agf-mini"><span class="agf-mini-label">' . esc_html__( 'Weight', 'shopglut' ) . '</span>' . self::select_html( $this->name( '[font-weight]' ), $weights, $v['font-weight'], array( 'class' => 'agf-font-weight' ) ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		if ( $this->on( 'font_style' ) ) {
			echo '<div class="agf-mini"><span class="agf-mini-label">' . esc_html__( 'Style', 'shopglut' ) . '</span>' . self::select_html( // phpcs:ignore WordPress.Security.EscapeOutput
				$this->name( '[font-style]' ),
				array(
					''       => __( 'Default', 'shopglut' ),
					'normal' => __( 'Normal', 'shopglut' ),
					'italic' => __( 'Italic', 'shopglut' ),
				),
				$v['font-style']
			) . '</div>';
		}
		if ( $this->on( 'font_size' ) ) {
			echo '<div class="agf-mini"><span class="agf-mini-label">' . esc_html__( 'Size', 'shopglut' ) . '</span><span class="agf-input-group agf-input-group-xs"><input type="number" step="any" min="0" class="agf-input" name="' . esc_attr( $this->name( '[font-size]' ) ) . '" value="' . esc_attr( $v['font-size'] ) . '">';
			echo self::select_html( $this->name( '[unit]' ), array( 'px' => 'px', 'em' => 'em', 'rem' => 'rem', '%' => '%', 'vw' => 'vw' ), $v['unit'], array( 'class' => 'agf-unit-select' ) ) . '</span></div>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		if ( $this->on( 'line_height' ) ) {
			echo '<div class="agf-mini"><span class="agf-mini-label">' . esc_html__( 'Line height', 'shopglut' ) . '</span><span class="agf-input-group agf-input-group-xs"><input type="number" step="any" min="0" class="agf-input" name="' . esc_attr( $this->name( '[line-height]' ) ) . '" value="' . esc_attr( $v['line-height'] ) . '">';
			echo self::select_html( $this->name( '[line-height-unit]' ), array( '' => '—', 'px' => 'px', 'em' => 'em' ), $v['line-height-unit'], array( 'class' => 'agf-unit-select' ) ) . '</span></div>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		if ( $this->on( 'letter_spacing' ) ) {
			echo '<div class="agf-mini"><span class="agf-mini-label">' . esc_html__( 'Letter spacing', 'shopglut' ) . '</span><span class="agf-input-group agf-input-group-xs"><input type="number" step="any" class="agf-input" name="' . esc_attr( $this->name( '[letter-spacing]' ) ) . '" value="' . esc_attr( $v['letter-spacing'] ) . '">';
			echo self::select_html( $this->name( '[letter-spacing-unit]' ), array( 'px' => 'px', 'em' => 'em' ), $v['letter-spacing-unit'], array( 'class' => 'agf-unit-select' ) ) . '</span></div>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		if ( $this->on( 'text_align' ) ) {
			echo '<div class="agf-mini"><span class="agf-mini-label">' . esc_html__( 'Align', 'shopglut' ) . '</span>' . self::select_html( // phpcs:ignore WordPress.Security.EscapeOutput
				$this->name( '[text-align]' ),
				array(
					''        => __( 'Default', 'shopglut' ),
					'left'    => __( 'Left', 'shopglut' ),
					'center'  => __( 'Center', 'shopglut' ),
					'right'   => __( 'Right', 'shopglut' ),
					'justify' => __( 'Justify', 'shopglut' ),
				),
				$v['text-align']
			) . '</div>';
		}
		if ( $this->on( 'text_transform' ) ) {
			echo '<div class="agf-mini"><span class="agf-mini-label">' . esc_html__( 'Transform', 'shopglut' ) . '</span>' . self::select_html( // phpcs:ignore WordPress.Security.EscapeOutput
				$this->name( '[text-transform]' ),
				array(
					''           => __( 'Default', 'shopglut' ),
					'none'       => __( 'None', 'shopglut' ),
					'uppercase'  => __( 'Uppercase', 'shopglut' ),
					'lowercase'  => __( 'Lowercase', 'shopglut' ),
					'capitalize' => __( 'Capitalize', 'shopglut' ),
				),
				$v['text-transform']
			) . '</div>';
		}
		if ( $this->on( 'text_decoration', false ) ) {
			echo '<div class="agf-mini"><span class="agf-mini-label">' . esc_html__( 'Decoration', 'shopglut' ) . '</span>' . self::select_html( // phpcs:ignore WordPress.Security.EscapeOutput
				$this->name( '[text-decoration]' ),
				array(
					''             => __( 'Default', 'shopglut' ),
					'none'         => __( 'None', 'shopglut' ),
					'underline'    => __( 'Underline', 'shopglut' ),
					'line-through' => __( 'Line through', 'shopglut' ),
				),
				$v['text-decoration']
			) . '</div>';
		}
		if ( $this->on( 'color' ) ) {
			echo '<div class="agf-mini agf-span-2"><span class="agf-mini-label">' . esc_html__( 'Color', 'shopglut' ) . '</span>' . AppGlut_Field_color::control( $this->name( '[color]' ), $v['color'] ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}

		echo '</div>';

		if ( $this->on( 'preview' ) ) {
			$text = $this->opt( 'preview_text', __( 'The quick brown fox jumps over the lazy dog. 0123456789', 'shopglut' ) );
			echo '<div class="agf-typography-preview" contenteditable="true" spellcheck="false">' . esc_html( $text ) . '</div>';
		}

		echo '</div>';
	}
	// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped

	public function sanitize( $value ) {
		$clean = $this->sanitize_map(
			$value,
			array(
				'font-family'         => 'text',
				'font-weight'         => 'int',
				'font-style'          => 'key',
				'font-size'           => 'number',
				'unit'                => 'key',
				'line-height'         => 'number',
				'line-height-unit'    => 'key',
				'letter-spacing'      => 'number',
				'letter-spacing-unit' => 'key',
				'text-align'          => 'key',
				'text-transform'      => 'key',
				'text-decoration'     => 'key',
				'color'               => 'color',
			)
		);

		$clean['font-family'] = trim( str_replace( array( '"', "'", ';', '{', '}' ), '', $clean['font-family'] ) );
		if ( $clean['font-weight'] && ( $clean['font-weight'] < 100 || $clean['font-weight'] > 900 ) ) {
			$clean['font-weight'] = '';
		}
		return $clean;
	}

	public function css( $value, $rule ) {
		$v   = (array) $value;
		$out = array();

		if ( ! empty( $v['font-family'] ) ) {
			$fonts  = include AppGlut::$dir . '/includes/data/fonts.php';
			$family = $v['font-family'];
			if ( isset( $fonts['google'][ $family ] ) ) {
				$out['font-family'] = '"' . $family . '", ' . $fonts['google'][ $family ]['category'];
			} elseif ( isset( $fonts['websafe'][ $family ] ) ) {
				$out['font-family'] = ( 'system-ui' === $family ? 'system-ui' : '"' . $family . '"' ) . ', ' . $fonts['websafe'][ $family ];
			} else {
				$out['font-family'] = $family;
			}
		}

		$map = array(
			'font-weight'     => '',
			'font-style'      => '',
			'text-align'      => '',
			'text-transform'  => '',
			'text-decoration' => '',
			'color'           => '',
			'font-size'       => 'unit',
			'line-height'     => 'line-height-unit',
			'letter-spacing'  => 'letter-spacing-unit',
		);
		foreach ( $map as $prop => $unit_key ) {
			if ( ! isset( $v[ $prop ] ) || '' === $v[ $prop ] ) {
				continue;
			}
			$unit = $unit_key && ! empty( $v[ $unit_key ] ) ? $v[ $unit_key ] : '';
			// Sizes need a unit to be valid CSS; line-height may stay unitless.
			if ( '' === $unit && 'line-height' !== $prop && $unit_key && is_numeric( $v[ $prop ] ) && 0 != $v[ $prop ] ) { // phpcs:ignore Universal.Operators.StrictComparisons
				$unit = 'px';
			}
			$out[ $prop ] = $v[ $prop ] . $unit;
		}

		return $out;
	}
}

/**
 * Background. Toggle parts with: background_color, background_image, background_position,
 * background_repeat, background_size, background_attachment (bool).
 */
class AppGlut_Field_background extends AppGlut_Field {

	public function layout() {
		return 'stacked';
	}

	public static function assets( $field ) {
		return array( 'media' => true );
	}

	public function render() {
		AppGlut::field_class( 'media' );

		$v = wp_parse_args(
			(array) $this->value,
			array(
				'color'      => '',
				'image'      => '',
				'image_id'   => '',
				'position'   => '',
				'repeat'     => '',
				'size'       => '',
				'attachment' => '',
			)
		);

		echo '<div class="agf-background">';
		if ( false !== $this->opt( 'background_image', true ) ) {
			echo '<div class="agf-background-image">';
			echo AppGlut_Field_media::picker( $this->name( '[image]' ), $this->name( '[image_id]' ), $v['image'], $v['image_id'], 'image' ); // phpcs:ignore WordPress.Security.EscapeOutput
			echo '</div>';
		}
		echo '<div class="agf-typography-grid">';
		if ( false !== $this->opt( 'background_color', true ) ) {
			echo '<div class="agf-mini agf-span-2"><span class="agf-mini-label">' . esc_html__( 'Color', 'shopglut' ) . '</span>' . AppGlut_Field_color::control( $this->name( '[color]' ), $v['color'] ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}

		$selects = array(
			'position'   => array(
				__( 'Position', 'shopglut' ),
				array(
					''              => __( 'Default', 'shopglut' ),
					'left top'      => __( 'Left Top', 'shopglut' ),
					'center top'    => __( 'Center Top', 'shopglut' ),
					'right top'     => __( 'Right Top', 'shopglut' ),
					'left center'   => __( 'Left Center', 'shopglut' ),
					'center center' => __( 'Center', 'shopglut' ),
					'right center'  => __( 'Right Center', 'shopglut' ),
					'left bottom'   => __( 'Left Bottom', 'shopglut' ),
					'center bottom' => __( 'Center Bottom', 'shopglut' ),
					'right bottom'  => __( 'Right Bottom', 'shopglut' ),
				),
			),
			'repeat'     => array(
				__( 'Repeat', 'shopglut' ),
				array(
					''          => __( 'Default', 'shopglut' ),
					'no-repeat' => __( 'No repeat', 'shopglut' ),
					'repeat'    => __( 'Repeat', 'shopglut' ),
					'repeat-x'  => __( 'Repeat X', 'shopglut' ),
					'repeat-y'  => __( 'Repeat Y', 'shopglut' ),
				),
			),
			'size'       => array(
				__( 'Size', 'shopglut' ),
				array(
					''        => __( 'Default', 'shopglut' ),
					'cover'   => __( 'Cover', 'shopglut' ),
					'contain' => __( 'Contain', 'shopglut' ),
					'auto'    => __( 'Auto', 'shopglut' ),
				),
			),
			'attachment' => array(
				__( 'Attachment', 'shopglut' ),
				array(
					''       => __( 'Default', 'shopglut' ),
					'scroll' => __( 'Scroll', 'shopglut' ),
					'fixed'  => __( 'Fixed', 'shopglut' ),
				),
			),
		);
		foreach ( $selects as $key => $conf ) {
			if ( false === $this->opt( 'background_' . $key, true ) ) {
				continue;
			}
			echo '<div class="agf-mini"><span class="agf-mini-label">' . esc_html( $conf[0] ) . '</span>' . self::select_html( $this->name( '[' . $key . ']' ), $conf[1], $v[ $key ] ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		echo '</div></div>';
	}

	public function sanitize( $value ) {
		return $this->sanitize_map(
			$value,
			array(
				'color'      => 'color',
				'image'      => 'url',
				'image_id'   => 'int',
				'position'   => 'key',
				'repeat'     => 'key',
				'size'       => 'key',
				'attachment' => 'key',
			)
		);
	}

	public function css( $value, $rule ) {
		$v   = (array) $value;
		$out = array();
		if ( ! empty( $v['color'] ) ) {
			$out['background-color'] = $v['color'];
		}
		if ( ! empty( $v['image'] ) ) {
			$out['background-image'] = 'url("' . esc_url_raw( $v['image'] ) . '")';
		}
		foreach ( array( 'position', 'repeat', 'size', 'attachment' ) as $key ) {
			if ( ! empty( $v[ $key ] ) ) {
				$out[ 'background-' . $key ] = $v[ $key ];
			}
		}
		return $out;
	}
}

/**
 * Range slider with a synced number input. Options: min, max, step, unit.
 */
class AppGlut_Field_slider extends AppGlut_Field_number {

	public function render() {
		$min  = $this->opt( 'min', 0 );
		$max  = $this->opt( 'max', 100 );
		$step = $this->opt( 'step', 1 );
		$val  = is_numeric( $this->value ) ? $this->value : $min;

		echo '<div class="agf-slider">';
		echo '<input type="range" class="agf-range" min="' . esc_attr( $min ) . '" max="' . esc_attr( $max ) . '" step="' . esc_attr( $step ) . '" value="' . esc_attr( $val ) . '" data-agf-skip>';
		echo '<span class="agf-input-group agf-input-group-xs"><input type="number" class="agf-input" name="' . esc_attr( $this->name() ) . '" min="' . esc_attr( $min ) . '" max="' . esc_attr( $max ) . '" step="' . esc_attr( $step ) . '" value="' . esc_attr( is_scalar( $this->value ) ? $this->value : '' ) . '"' . $this->attrs() . '>'; // phpcs:ignore WordPress.Security.EscapeOutput
		if ( $this->opt( 'unit' ) ) {
			echo '<span class="agf-addon">' . esc_html( $this->opt( 'unit' ) ) . '</span>';
		}
		echo '</span></div>';
	}
}

/**
 * Number with − / + buttons. Options: min, max, step, unit.
 */
class AppGlut_Field_spinner extends AppGlut_Field_number {

	public function render() {
		echo '<div class="agf-spinner">';
		echo '<button type="button" class="agf-spinner-btn" data-dir="-1" aria-label="' . esc_attr__( 'Decrease', 'shopglut' ) . '">&minus;</button>';
		echo '<input type="number" class="agf-input" name="' . esc_attr( $this->name() ) . '" value="' . esc_attr( is_scalar( $this->value ) ? $this->value : '' ) . '" min="' . esc_attr( $this->opt( 'min', '' ) ) . '" max="' . esc_attr( $this->opt( 'max', '' ) ) . '" step="' . esc_attr( $this->opt( 'step', 1 ) ) . '"' . $this->attrs() . '>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '<button type="button" class="agf-spinner-btn" data-dir="1" aria-label="' . esc_attr__( 'Increase', 'shopglut' ) . '">+</button>';
		if ( $this->opt( 'unit' ) ) {
			echo '<span class="agf-spinner-unit">' . esc_html( $this->opt( 'unit' ) ) . '</span>';
		}
		echo '</div>';
	}
}
