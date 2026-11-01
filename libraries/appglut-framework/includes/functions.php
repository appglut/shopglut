<?php
/**
 * Public helper functions.
 *
 * @package AppGlut_Framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'appglut_array_get' ) ) {
	/**
	 * Read a value from a nested array using a dot path ("typography.font-size").
	 */
	function appglut_array_get( $array, $path, $default = null ) {
		if ( ! is_array( $array ) ) {
			return $default;
		}
		if ( array_key_exists( $path, $array ) ) {
			return $array[ $path ];
		}
		foreach ( explode( '.', (string) $path ) as $segment ) {
			if ( ! is_array( $array ) || ! array_key_exists( $segment, $array ) ) {
				return $default;
			}
			$array = $array[ $segment ];
		}
		return $array;
	}
}

if ( ! function_exists( 'appglut_get_option' ) ) {
	/**
	 * Get a saved value (defaults merged) from an AppGlut options panel.
	 *
	 * @param string $unique  The panel ID passed to AppGlut::createOptions().
	 * @param string $key     Field ID or dot path. Empty returns all values.
	 * @param mixed  $default Fallback when the key does not exist.
	 */
	function appglut_get_option( $unique, $key = '', $default = null ) {
		$values = AppGlut::get_values( $unique );
		if ( '' === $key || null === $key ) {
			return $values;
		}
		return appglut_array_get( $values, $key, $default );
	}
}

if ( ! function_exists( 'appglut_get_meta' ) ) {
	/**
	 * Get a value saved by an AppGlut metabox.
	 */
	function appglut_get_meta( $post_id, $unique, $key = '', $default = null ) {
		$container = AppGlut::container( $unique );
		$values    = $container instanceof AppGlut_Metabox ? $container->get_values( $post_id ) : (array) get_post_meta( $post_id, $unique, true );
		if ( '' === $key || null === $key ) {
			return $values;
		}
		return appglut_array_get( $values, $key, $default );
	}
}

if ( ! function_exists( 'appglut_get_term_meta' ) ) {
	/**
	 * Get a value saved by AppGlut taxonomy options.
	 */
	function appglut_get_term_meta( $term_id, $unique, $key = '', $default = null ) {
		$container = AppGlut::container( $unique );
		$values    = $container instanceof AppGlut_Taxonomy ? $container->get_values( $term_id ) : (array) get_term_meta( $term_id, $unique, true );
		if ( '' === $key || null === $key ) {
			return $values;
		}
		return appglut_array_get( $values, $key, $default );
	}
}

if ( ! function_exists( 'appglut_get_user_meta' ) ) {
	/**
	 * Get a value saved by AppGlut profile options.
	 */
	function appglut_get_user_meta( $user_id, $unique, $key = '', $default = null ) {
		$container = AppGlut::container( $unique );
		$values    = $container instanceof AppGlut_Profile ? $container->get_values( $user_id ) : (array) get_user_meta( $user_id, $unique, true );
		if ( '' === $key || null === $key ) {
			return $values;
		}
		return appglut_array_get( $values, $key, $default );
	}
}

if ( ! function_exists( 'appglut_get_comment_meta' ) ) {
	/**
	 * Get a value saved by an AppGlut comment metabox.
	 */
	function appglut_get_comment_meta( $comment_id, $unique, $key = '', $default = null ) {
		$container = AppGlut::container( $unique );
		$values    = $container instanceof AppGlut_Comment ? $container->get_values( $comment_id ) : (array) get_comment_meta( $comment_id, $unique, true );
		if ( '' === $key || null === $key ) {
			return $values;
		}
		return appglut_array_get( $values, $key, $default );
	}
}

if ( ! function_exists( 'appglut_get_menu_item_meta' ) ) {
	/**
	 * Get a value saved by AppGlut nav menu options for a menu item.
	 */
	function appglut_get_menu_item_meta( $item_id, $unique, $key = '', $default = null ) {
		$container = AppGlut::container( $unique );
		$values    = $container instanceof AppGlut_Nav_Menu ? $container->get_values( $item_id ) : (array) get_post_meta( $item_id, $unique, true );
		if ( '' === $key || null === $key ) {
			return $values;
		}
		return appglut_array_get( $values, $key, $default );
	}
}

if ( ! function_exists( 'appglut_sanitize_color' ) ) {
	/**
	 * Accepts hex (3/4/6/8), rgb(a), hsl(a), "transparent" and CSS custom properties.
	 */
	function appglut_sanitize_color( $color, $default = '' ) {
		$color = strtolower( trim( (string) $color ) );

		if ( '' === $color ) {
			return '';
		}
		if ( 'transparent' === $color || 'currentcolor' === $color ) {
			return $color;
		}
		if ( preg_match( '/^#([0-9a-f]{3}|[0-9a-f]{4}|[0-9a-f]{6}|[0-9a-f]{8})$/', $color ) ) {
			return $color;
		}
		if ( preg_match( '/^var\(--[a-z0-9_-]+\)$/', $color ) ) {
			return $color;
		}

		$num   = '\s*(-?\d{1,3}(?:\.\d+)?%?)\s*';
		$alpha = '(?:[,\/]\s*(0|1|0?\.\d+|\d{1,3}%)\s*)?';
		if ( preg_match( '/^(rgba?|hsla?)\(' . $num . ',?' . $num . ',?' . $num . $alpha . '\)$/', $color, $m ) ) {
			$fn    = rtrim( $m[1], 'a' );
			$parts = array( $m[2], $m[3], $m[4] );
			if ( isset( $m[5] ) && '' !== $m[5] ) {
				$parts[] = $m[5];
				return $fn . 'a(' . implode( ', ', $parts ) . ')';
			}
			return $fn . '(' . implode( ', ', $parts ) . ')';
		}

		return $default;
	}
}

if ( ! function_exists( 'appglut_gradient_css' ) ) {
	/**
	 * Build a CSS gradient string from a gradient field value.
	 */
	function appglut_gradient_css( $value ) {
		if ( empty( $value['start'] ) && empty( $value['end'] ) ) {
			return '';
		}
		$num = function ( $key, $fallback ) use ( $value ) {
			return isset( $value[ $key ] ) && is_numeric( $value[ $key ] ) ? (int) $value[ $key ] : $fallback;
		};

		$start = ! empty( $value['start'] ) ? $value['start'] : 'transparent';
		$end   = ! empty( $value['end'] ) ? $value['end'] : 'transparent';
		$stops = $start . ' ' . $num( 'start_pos', 0 ) . '%, ' . $end . ' ' . $num( 'end_pos', 100 ) . '%';

		if ( 'radial' === appglut_array_get( $value, 'type' ) ) {
			return 'radial-gradient(circle, ' . $stops . ')';
		}
		return 'linear-gradient(' . $num( 'angle', 135 ) . 'deg, ' . $stops . ')';
	}
}

if ( ! function_exists( 'appglut_box_shadow_css' ) ) {
	/**
	 * Build a CSS box-shadow string from a box_shadow field value.
	 */
	function appglut_box_shadow_css( $value ) {
		if ( empty( $value['color'] ) ) {
			return '';
		}
		$unit = ! empty( $value['unit'] ) ? $value['unit'] : 'px';
		$css  = ! empty( $value['inset'] ) ? 'inset ' : '';
		foreach ( array( 'x', 'y', 'blur', 'spread' ) as $key ) {
			$css .= (float) appglut_array_get( $value, $key, 0 ) . $unit . ' ';
		}
		return $css . $value['color'];
	}
}

if ( ! function_exists( 'appglut_image_url' ) ) {
	/**
	 * Resolve a media field value (array or attachment ID) to a URL at the given size.
	 */
	function appglut_image_url( $value, $size = 'full' ) {
		$id = is_array( $value ) ? (int) appglut_array_get( $value, 'id', 0 ) : (int) $value;
		if ( $id ) {
			$src = wp_get_attachment_image_url( $id, $size );
			if ( $src ) {
				return $src;
			}
		}
		return is_array( $value ) ? (string) appglut_array_get( $value, 'url', '' ) : '';
	}
}
