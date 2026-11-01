<?php
/**
 * Base class every field type extends.
 *
 * A field receives its config, current value and the name prefix of its
 * parent ("my_options" or "my_options[repeater][0]"). Its input names are
 * "{prefix}[{id}]" plus an optional suffix.
 *
 * @package AppGlut_Framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class AppGlut_Field {

	public $field  = array();
	public $value  = null;
	public $prefix = '';
	public $where  = '';
	public $parent = null;

	public function __construct( $field = array(), $value = null, $prefix = '', $where = '', $parent = null ) {
		$this->field  = (array) $field;
		$this->value  = $value;
		$this->prefix = (string) $prefix;
		$this->where  = $where;
		$this->parent = $parent;
	}

	/** Output the control markup. */
	abstract public function render();

	/** 'inline' (label left, control right) or 'stacked' (label above, full width). */
	public function layout() {
		return 'inline';
	}

	/**
	 * Assets this field needs. Keys: media, editor, icons, fonts, code (mode).
	 *
	 * @param array $field Field config.
	 */
	public static function assets( $field ) {
		return array();
	}

	/** Clean a raw submitted value. Override per type. */
	public function sanitize( $value ) {
		return AppGlut::sanitize_deep( $value );
	}

	/**
	 * CSS declarations for the "output" feature.
	 *
	 * @param mixed $value    Saved value.
	 * @param array $property Normalised output rule (selector, property, unit, important).
	 * @return array property => value
	 */
	public function css( $value, $rule ) {
		if ( is_array( $value ) || '' === (string) $value || empty( $rule['property'] ) ) {
			return array();
		}
		$unit = isset( $rule['unit'] ) ? $rule['unit'] : '';
		return array( $rule['property'] => $value . ( is_numeric( $value ) ? $unit : '' ) );
	}

	/* ---------------------------------------------------------------------
	 * Helpers for subclasses
	 * ------------------------------------------------------------------- */

	/** Field config value with fallback. */
	public function opt( $key, $default = null ) {
		return array_key_exists( $key, $this->field ) ? $this->field[ $key ] : $default;
	}

	/** Input name, optionally with a suffix like "[width]" or "[]". */
	public function name( $suffix = '' ) {
		if ( ! empty( $this->field['name'] ) ) {
			return $this->field['name'] . $suffix;
		}
		$id = isset( $this->field['id'] ) ? $this->field['id'] : '';
		return ( '' !== $this->prefix ? $this->prefix . '[' . $id . ']' : $id ) . $suffix;
	}

	/** Name prefix to hand to child fields. */
	public function child_prefix( $suffix = '' ) {
		return $this->name( $suffix );
	}

	/** Build an attribute string from field 'attributes' merged with $extra. */
	public function attrs( $extra = array() ) {
		$attributes = array_merge( (array) $extra, (array) $this->opt( 'attributes', array() ) );

		if ( null !== $this->opt( 'placeholder' ) && ! isset( $attributes['placeholder'] ) ) {
			$attributes['placeholder'] = $this->opt( 'placeholder' );
		}

		if ( $this->opt( 'class_input' ) ) {
			$attributes['class'] = trim( ( isset( $attributes['class'] ) ? $attributes['class'] : '' ) . ' ' . $this->opt( 'class_input' ) );
		}

		return self::build_attrs( $attributes );
	}

	public static function build_attrs( $attributes ) {
		$out = '';
		foreach ( (array) $attributes as $key => $value ) {
			if ( false === $value || null === $value ) {
				continue;
			}
			if ( true === $value ) {
				$out .= ' ' . esc_attr( $key );
				continue;
			}
			if ( is_array( $value ) ) {
				$value = wp_json_encode( $value );
			}
			$out .= ' ' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
		}
		return $out;
	}

	/**
	 * Resolve 'options' into value => label pairs (static array, data source or callable).
	 */
	public function options() {
		$options = $this->opt( 'options', array() );

		if ( is_string( $options ) ) {
			if ( $this->opt( 'ajax' ) ) {
				return AppGlut::get_data_titles( $options, (array) $this->value, (array) $this->opt( 'query_args', array() ) );
			}
			return AppGlut::get_data( $options, '', (array) $this->opt( 'query_args', array() ) );
		}

		if ( is_callable( $options ) && ! is_array( $options ) ) {
			return (array) call_user_func( $options, $this->field );
		}

		return (array) $options;
	}

	/** Allowed option keys; select optgroups are flattened. */
	public function option_keys() {
		$keys      = array();
		$optgroups = isset( $this->field['type'] ) && 'select' === $this->field['type'];
		foreach ( $this->options() as $key => $label ) {
			if ( $optgroups && is_array( $label ) ) {
				$keys = array_merge( $keys, array_map( 'strval', array_keys( $label ) ) );
			} else {
				$keys[] = (string) $key;
			}
		}
		return $keys;
	}

	/** True when the options are a fixed array (so submitted values can be whitelisted). */
	public function has_static_options() {
		$options = $this->opt( 'options', array() );
		return is_array( $options ) && ! empty( $options );
	}

	/** Keep only allowed choice keys. */
	public function whitelist( $value, $multiple = false ) {
		$static = $this->has_static_options();
		$keys   = $static ? $this->option_keys() : array();

		if ( $multiple ) {
			$out = array();
			foreach ( (array) $value as $item ) {
				$item = sanitize_text_field( (string) $item );
				if ( '' !== $item && ( ! $static || in_array( $item, $keys, true ) ) ) {
					$out[] = $item;
				}
			}
			return array_values( array_unique( $out ) );
		}

		$value = is_array( $value ) ? '' : sanitize_text_field( (string) $value );
		return ( ! $static || in_array( $value, $keys, true ) ) ? $value : '';
	}

	/** Sanitize an associative value against a key => type map. */
	public function sanitize_map( $value, $map ) {
		$value = is_array( $value ) ? $value : array();
		$out   = array();
		foreach ( $map as $key => $type ) {
			$raw = isset( $value[ $key ] ) ? $value[ $key ] : '';
			switch ( $type ) {
				case 'color':
					$out[ $key ] = appglut_sanitize_color( $raw );
					break;
				case 'number':
					$out[ $key ] = is_numeric( $raw ) ? $raw + 0 : '';
					break;
				case 'int':
					$out[ $key ] = '' === $raw ? '' : (int) $raw;
					break;
				case 'url':
					$out[ $key ] = esc_url_raw( (string) $raw );
					break;
				case 'bool':
					$out[ $key ] = ! empty( $raw ) && 'false' !== $raw && '0' !== $raw;
					break;
				case 'key':
					$out[ $key ] = preg_replace( '/[^a-z0-9_%\-\s,.]/i', '', (string) $raw );
					break;
				default:
					$out[ $key ] = sanitize_text_field( (string) $raw );
			}
		}
		return $out;
	}

	/** <select> markup for a small set of options. */
	public static function select_html( $name, $options, $current, $extra = array() ) {
		$html = '<select name="' . esc_attr( $name ) . '"' . self::build_attrs( $extra ) . '>';
		foreach ( $options as $key => $label ) {
			$html .= '<option value="' . esc_attr( $key ) . '"' . selected( (string) $current, (string) $key, false ) . '>' . esc_html( $label ) . '</option>';
		}
		return $html . '</select>';
	}

	/** Units dropdown or a static unit badge. */
	public function unit_html( $name, $units, $current ) {
		$units = array_values( (array) $units );
		if ( count( $units ) > 1 ) {
			return self::select_html( $name, array_combine( $units, $units ), $current, array( 'class' => 'agf-unit-select' ) );
		}
		$unit = isset( $units[0] ) ? $units[0] : '';
		if ( '' === $unit ) {
			return '';
		}
		return '<span class="agf-unit">' . esc_html( $unit ) . '</span><input type="hidden" name="' . esc_attr( $name ) . '" value="' . esc_attr( $unit ) . '">';
	}
}
