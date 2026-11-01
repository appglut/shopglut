<?php
/**
 * Front-end CSS generated from fields with an 'output' rule, plus Google Fonts.
 *
 *   'output' => '.site-title'                         // property inferred by type
 *   'output' => array( 'selector' => 'a', 'property' => 'color', 'important' => true )
 *   'output' => array( array( 'selector' => '.btn', 'property' => 'background-color' ),
 *                      array( 'selector' => '.btn', 'property' => 'border-color', 'media' => '(min-width: 768px)' ) )
 *
 * @package AppGlut_Framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AppGlut_Output {

	private static $built = null;

	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_fonts' ), 20 );
		add_action( 'wp_head', array( __CLASS__, 'print_css' ), 99 );
	}

	/** Containers that write front-end CSS. */
	public static function supports( $container ) {
		return ( $container instanceof AppGlut_Options || $container instanceof AppGlut_Customizer ) && $container->arg( 'output_css' );
	}

	/** Build CSS (per container) and font requests once per request. */
	public static function build() {
		if ( null !== self::$built ) {
			return self::$built;
		}

		$styles = array();
		$fonts  = array();

		foreach ( AppGlut::$containers as $unique => $container ) {
			if ( self::supports( $container ) ) {
				$css = array();
				self::collect( $container->fields(), $container->get_values(), $css, $fonts, $container->arg( 'enqueue_webfont' ) );
				$styles[ $unique ] = self::stringify( $css );
			}
		}

		self::$built = array(
			'css'   => $styles,
			'fonts' => $fonts,
		);
		return self::$built;
	}

	/**
	 * CSS for one container, computed fresh (used by the customizer live preview).
	 *
	 * @param AppGlut_Container $container Options or customizer container.
	 * @param bool              $imports   Prefix an @import for the Google Fonts in use.
	 */
	public static function container_css( $container, $imports = false ) {
		$css   = array();
		$fonts = array();
		self::collect( $container->fields(), $container->get_values(), $css, $fonts, $container->arg( 'enqueue_webfont' ) );

		$out = self::stringify( $css );
		if ( $imports && $fonts ) {
			$out = '@import url("' . esc_url_raw( self::fonts_url( $fonts ) ) . '");' . $out;
		}
		return wp_strip_all_tags( $out );
	}

	private static function collect( $fields, $values, &$css, &$fonts, $webfonts ) {
		foreach ( (array) $fields as $field ) {
			if ( empty( $field['id'] ) || empty( $field['type'] ) || ! array_key_exists( $field['id'], (array) $values ) ) {
				continue;
			}
			$value = $values[ $field['id'] ];

			// Non-repeating containers keep nested values.
			if ( in_array( $field['type'], array( 'fieldset', 'accordion', 'tabbed' ), true ) && is_array( $value ) ) {
				foreach ( AppGlut::child_lists( $field ) as $list ) {
					self::collect( $list, $value, $css, $fonts, $webfonts );
				}
			}

			if ( 'typography' === $field['type'] && $webfonts && is_array( $value ) ) {
				self::add_font( $value, $fonts );
			}

			if ( empty( $field['output'] ) ) {
				continue;
			}

			$class = AppGlut::field_class( $field['type'] );
			if ( ! $class ) {
				continue;
			}
			$obj = new $class( $field, $value );

			foreach ( self::rules( $field ) as $rule ) {
				$declarations = $obj->css( $value, $rule );
				if ( ! $declarations ) {
					continue;
				}
				foreach ( $declarations as $property => $val ) {
					// A nested array is a block for a pseudo-class/suffix, e.g. ':hover' => array( 'color' => '#f00' ).
					if ( is_array( $val ) ) {
						$selector = self::suffix_selector( $rule['selector'], $property );
						foreach ( $val as $prop => $v ) {
							if ( '' !== $v && null !== $v ) {
								$css[ $rule['media'] ][ $selector ][ $prop ] = $v . ( $rule['important'] ? ' !important' : '' );
							}
						}
						continue;
					}
					if ( '' === $val || null === $val ) {
						continue;
					}
					$css[ $rule['media'] ][ $rule['selector'] ][ $property ] = $val . ( $rule['important'] ? ' !important' : '' );
				}
			}
		}
	}

	/** Append a suffix (":hover") to every part of a selector list ("a, .link"). */
	public static function suffix_selector( $selector, $suffix ) {
		$parts = array_map( 'trim', explode( ',', $selector ) );
		return implode(
			', ',
			array_map(
				function ( $part ) use ( $suffix ) {
					return $part . $suffix;
				},
				$parts
			)
		);
	}

	/** Normalise a field's 'output' config into a list of rules. */
	public static function rules( $field ) {
		$output = $field['output'];
		if ( is_string( $output ) || isset( $output['selector'] ) ) {
			$output = array( $output );
		}

		$rules = array();
		foreach ( (array) $output as $rule ) {
			if ( is_string( $rule ) ) {
				$rule = array( 'selector' => $rule );
			}
			if ( empty( $rule['selector'] ) ) {
				continue;
			}
			$rules[] = wp_parse_args(
				$rule,
				array(
					'property'  => isset( $field['output_property'] ) ? $field['output_property'] : ( in_array( $field['type'], array( 'color' ), true ) ? 'color' : '' ),
					'unit'      => isset( $field['output_unit'] ) ? $field['output_unit'] : ( isset( $field['unit'] ) ? $field['unit'] : '' ),
					'important' => ! empty( $field['output_important'] ),
					'media'     => '',
				)
			);
		}
		return $rules;
	}

	private static function stringify( $css ) {
		$out = '';
		ksort( $css ); // '' (no media query) sorts first.
		foreach ( $css as $media => $selectors ) {
			$block = '';
			foreach ( $selectors as $selector => $props ) {
				$decl = '';
				foreach ( $props as $prop => $val ) {
					$decl .= $prop . ':' . $val . ';';
				}
				$block .= $selector . '{' . $decl . '}';
			}
			$out .= $media ? '@media ' . $media . '{' . $block . '}' : $block;
		}
		return $out;
	}

	private static function add_font( $value, &$fonts ) {
		$family = isset( $value['font-family'] ) ? trim( $value['font-family'] ) : '';
		if ( '' === $family ) {
			return;
		}

		static $google = null;
		if ( null === $google ) {
			$data   = include AppGlut::$dir . '/includes/data/fonts.php';
			$google = $data['google'];
		}
		if ( ! isset( $google[ $family ] ) ) {
			return;
		}

		$weight = ! empty( $value['font-weight'] ) ? (int) $value['font-weight'] : 400;
		$italic = ( isset( $value['font-style'] ) && 'italic' === $value['font-style'] ) ? 1 : 0;

		$fonts[ $family ][ $italic . ',' . $weight ] = true;
	}

	/** Google Fonts CSS2 URL for collected families/variants. */
	public static function fonts_url( $fonts ) {
		$families = array();
		foreach ( $fonts as $family => $variants ) {
			$variants = array_keys( $variants );
			sort( $variants );
			$families[] = 'family=' . str_replace( ' ', '+', $family ) . ':ital,wght@' . implode( ';', $variants );
		}
		return 'https://fonts.googleapis.com/css2?' . implode( '&', $families ) . '&display=swap';
	}

	public static function enqueue_fonts() {
		$fonts = self::build()['fonts'];
		if ( $fonts ) {
			wp_enqueue_style( 'appglut-google-fonts', self::fonts_url( $fonts ), array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
		}
	}

	public static function print_css() {
		$preview = is_customize_preview();
		foreach ( self::build()['css'] as $unique => $css ) {
			// The customizer preview needs the tag even when empty, so live CSS has a target.
			if ( '' !== $css || ( $preview && AppGlut::container( $unique ) instanceof AppGlut_Customizer ) ) {
				echo '<style id="appglut-output-' . esc_attr( $unique ) . '">' . wp_strip_all_tags( $css ) . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
			}
		}
	}
}
