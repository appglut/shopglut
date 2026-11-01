<?php
/**
 * Fields: media, gallery, upload, icon, map, link, date.
 *
 * @package AppGlut_Framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media library picker. Value: array( url, id, thumbnail, width, height, alt, title ).
 * Options: library (image|video|audio|application|...), url (show URL input), button_title.
 */
class AppGlut_Field_media extends AppGlut_Field {

	public static function assets( $field ) {
		return array( 'media' => true );
	}

	public function render() {
		$v = wp_parse_args(
			is_array( $this->value ) ? $this->value : array(),
			array(
				'url'       => '',
				'id'        => '',
				'thumbnail' => '',
				'width'     => '',
				'height'    => '',
				'alt'       => '',
				'title'     => '',
			)
		);

		$extras = array();
		foreach ( array( 'thumbnail', 'width', 'height', 'alt', 'title' ) as $key ) {
			$extras[ $key ] = array( $this->name( '[' . $key . ']' ), $v[ $key ] );
		}

		echo self::picker( $this->name( '[url]' ), $this->name( '[id]' ), $v['url'], $v['id'], $this->opt( 'library', 'image' ), $extras, (bool) $this->opt( 'url', false ), $this->opt( 'button_title' ), $v['thumbnail'] ); // phpcs:ignore WordPress.Security.EscapeOutput
	}

	/**
	 * Picker markup shared with the background field.
	 */
	public static function picker( $url_name, $id_name, $url, $id, $library = 'image', $extras = array(), $show_url = false, $button = '', $thumb = '' ) {
		$is_image = $url && preg_match( '/\.(jpe?g|png|gif|webp|avif|svg|bmp|ico)(\?.*)?$/i', $url );
		$preview  = $thumb ? $thumb : $url;
		$button   = $button ? $button : ( 'image' === $library ? __( 'Select image', 'shopglut' ) : __( 'Select file', 'shopglut' ) );

		$html  = '<div class="agf-media" data-library="' . esc_attr( $library ) . '">';
		$html .= '<div class="agf-media-preview' . ( $url ? '' : ' agf-is-empty' ) . '">';
		if ( $url && $is_image ) {
			$html .= '<img src="' . esc_url( $preview ) . '" alt="">';
		} elseif ( $url ) {
			$html .= '<span class="agf-media-file">' . AppGlut::icon( 'dashicons-media-default' ) . '<span>' . esc_html( wp_basename( $url ) ) . '</span></span>';
		} else {
			$html .= '<span class="agf-media-placeholder">' . AppGlut::icon( 'image' === $library ? 'dashicons-format-image' : 'dashicons-media-default' ) . '</span>';
		}
		$html .= '</div>';

		$html .= '<div class="agf-media-actions">';
		$html .= '<input type="' . ( $show_url ? 'url' : 'hidden' ) . '" class="agf-input agf-media-url" name="' . esc_attr( $url_name ) . '" value="' . esc_attr( $url ) . '" placeholder="https://">';
		$html .= '<input type="hidden" class="agf-media-id" name="' . esc_attr( $id_name ) . '" value="' . esc_attr( $id ) . '">';
		foreach ( (array) $extras as $key => $pair ) {
			$html .= '<input type="hidden" class="agf-media-extra" data-key="' . esc_attr( $key ) . '" name="' . esc_attr( $pair[0] ) . '" value="' . esc_attr( $pair[1] ) . '">';
		}
		$html .= '<div class="agf-btn-row">';
		$html .= '<button type="button" class="agf-btn agf-btn-secondary agf-media-select">' . AppGlut::icon( 'dashicons-upload' ) . '<span>' . esc_html( $button ) . '</span></button>';
		$html .= '<button type="button" class="agf-btn agf-btn-ghost agf-media-remove"' . ( $url ? '' : ' hidden' ) . '>' . esc_html__( 'Remove', 'shopglut' ) . '</button>';
		$html .= '</div></div></div>';

		return $html;
	}

	public function sanitize( $value ) {
		return $this->sanitize_map(
			$value,
			array(
				'url'       => 'url',
				'id'        => 'int',
				'thumbnail' => 'url',
				'width'     => 'int',
				'height'    => 'int',
				'alt'       => 'text',
				'title'     => 'text',
			)
		);
	}

	public function css( $value, $rule ) {
		$url = is_array( $value ) && ! empty( $value['url'] ) ? $value['url'] : '';
		return $url ? array( $rule['property'] ? $rule['property'] : 'background-image' => 'url("' . esc_url_raw( $url ) . '")' ) : array();
	}
}

/**
 * Image gallery stored as comma separated attachment IDs ("12,15,18").
 */
class AppGlut_Field_gallery extends AppGlut_Field {

	public function layout() {
		return 'stacked';
	}

	public static function assets( $field ) {
		return array( 'media' => true );
	}

	public function render() {
		$ids = array_filter( array_map( 'absint', explode( ',', is_scalar( $this->value ) ? (string) $this->value : '' ) ) );

		echo '<div class="agf-gallery">';
		echo '<ul class="agf-gallery-list' . ( $ids ? '' : ' agf-is-empty' ) . '">';
		foreach ( $ids as $id ) {
			$src = wp_get_attachment_image_url( $id, 'thumbnail' );
			if ( $src ) {
				echo '<li data-id="' . esc_attr( $id ) . '"><img src="' . esc_url( $src ) . '" alt=""><button type="button" class="agf-gallery-remove" aria-label="' . esc_attr__( 'Remove', 'shopglut' ) . '">&times;</button></li>';
			}
		}
		echo '</ul>';
		echo '<input type="hidden" class="agf-gallery-ids" name="' . esc_attr( $this->name() ) . '" value="' . esc_attr( implode( ',', $ids ) ) . '">';
		echo '<div class="agf-btn-row">';
		echo '<button type="button" class="agf-btn agf-btn-secondary agf-gallery-add">' . AppGlut::icon( 'dashicons-format-gallery' ) . '<span>' . esc_html( $this->opt( 'add_title', __( 'Add images', 'shopglut' ) ) ) . '</span></button>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '<button type="button" class="agf-btn agf-btn-ghost agf-gallery-clear"' . ( $ids ? '' : ' hidden' ) . '>' . esc_html__( 'Clear', 'shopglut' ) . '</button>';
		echo '</div></div>';
	}

	public function sanitize( $value ) {
		$ids = is_array( $value ) ? $value : explode( ',', (string) $value );
		return implode( ',', array_filter( array_map( 'absint', $ids ) ) );
	}
}

/**
 * URL input with a media library button. Value: URL string. Options: library, button_title.
 */
class AppGlut_Field_upload extends AppGlut_Field {

	public static function assets( $field ) {
		return array( 'media' => true );
	}

	public function render() {
		echo '<div class="agf-upload agf-input-group" data-library="' . esc_attr( $this->opt( 'library', '' ) ) . '">';
		echo '<input type="url" class="agf-input agf-upload-url" name="' . esc_attr( $this->name() ) . '" value="' . esc_attr( is_scalar( $this->value ) ? $this->value : '' ) . '"' . $this->attrs( array( 'placeholder' => 'https://' ) ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '<button type="button" class="agf-addon agf-addon-btn agf-upload-btn">' . AppGlut::icon( 'dashicons-upload' ) . '<span>' . esc_html( $this->opt( 'button_title', __( 'Upload', 'shopglut' ) ) ) . '</span></button>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '</div>';
	}

	public function sanitize( $value ) {
		return esc_url_raw( is_scalar( $value ) ? (string) $value : '' );
	}
}

/**
 * Icon picker (Dashicons by default). Options: icons (array of class strings to offer instead).
 */
class AppGlut_Field_icon extends AppGlut_Field {

	/** Name of a registered SVG icon set, or ''. */
	private static function set_name( $field ) {
		return ( isset( $field['icons'] ) && is_string( $field['icons'] ) && AppGlut::icon_set( $field['icons'] ) ) ? $field['icons'] : '';
	}

	public static function assets( $field ) {
		$set = self::set_name( $field );
		if ( $set ) {
			return array( 'icon_set' => $set );
		}
		return empty( $field['icons'] ) ? array( 'icons' => true ) : array();
	}

	public function render() {
		$value = is_scalar( $this->value ) ? (string) $this->value : '';
		$icons = $this->opt( 'icons' );
		$set   = self::set_name( $this->field );
		$attr  = '';

		if ( $set ) {
			$attr    = ' data-set="' . esc_attr( $set ) . '"';
			$preview = AppGlut::icon_svg( $set, $value );
			$label   = '';
			foreach ( AppGlut::icon_set( $set ) as $key => $icon ) {
				if ( $key === $value ) {
					$label = $icon['label'];
				}
			}
		} else {
			if ( $icons ) {
				$attr = ' data-icons="' . esc_attr( wp_json_encode( array_values( (array) $icons ) ) ) . '"';
			}
			$preview = AppGlut::icon( $value );
			$label   = $value ? preg_replace( '/^dashicons-/', '', $value ) : '';
		}

		$attr .= ' data-title="' . esc_attr( $this->opt( 'button_title', __( 'Choose icon', 'shopglut' ) ) ) . '"';
		$size  = 'lg' === $this->opt( 'size' ) ? ' agf-icon-picker--lg' : '';
		echo '<div class="agf-icon-picker' . $size . '"' . $attr . '>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '<button type="button" class="agf-icon-preview agf-icon-select' . ( $value ? '' : ' agf-is-empty' ) . '" aria-label="' . esc_attr( $this->opt( 'button_title', __( 'Choose icon', 'shopglut' ) ) ) . '">' . $preview . '</button>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '<input type="hidden" class="agf-icon-value" name="' . esc_attr( $this->name() ) . '" value="' . esc_attr( $value ) . '">';
		echo '<div class="agf-icon-meta"><span class="agf-icon-name">' . esc_html( $label ) . '</span><span class="agf-btn-row">';
		echo '<button type="button" class="agf-btn agf-btn-secondary agf-btn-sm agf-icon-select">' . esc_html( $value ? __( 'Change', 'shopglut' ) : $this->opt( 'button_title', __( 'Choose icon', 'shopglut' ) ) ) . '</button>';
		echo '<button type="button" class="agf-btn agf-btn-ghost agf-btn-sm agf-icon-remove"' . ( $value ? '' : ' hidden' ) . '>' . esc_html__( 'Remove', 'shopglut' ) . '</button>';
		echo '</span></div></div>';
	}

	public function sanitize( $value ) {
		$value = is_scalar( $value ) ? trim( (string) $value ) : '';
		$set   = self::set_name( $this->field );
		if ( $set ) {
			return array_key_exists( $value, AppGlut::icon_set( $set ) ) ? $value : '';
		}
		$classes = array_filter( array_map( 'sanitize_html_class', preg_split( '/\s+/', $value ) ) );
		return implode( ' ', $classes );
	}
}

/**
 * Location (lat / lng / zoom) with an OpenStreetMap preview. Options: height (px).
 */
class AppGlut_Field_map extends AppGlut_Field {

	public function layout() {
		return 'stacked';
	}

	public function render() {
		$v = wp_parse_args(
			(array) $this->value,
			array(
				'address' => '',
				'lat'     => '',
				'lng'     => '',
				'zoom'    => 12,
			)
		);

		echo '<div class="agf-map">';
		echo '<div class="agf-typography-grid">';
		echo '<label class="agf-mini agf-span-2"><span class="agf-mini-label">' . esc_html__( 'Label / address', 'shopglut' ) . '</span><input type="text" class="agf-input" name="' . esc_attr( $this->name( '[address]' ) ) . '" value="' . esc_attr( $v['address'] ) . '"></label>';
		foreach ( array(
			'lat'  => array( __( 'Latitude', 'shopglut' ), -90, 90, 'any' ),
			'lng'  => array( __( 'Longitude', 'shopglut' ), -180, 180, 'any' ),
			'zoom' => array( __( 'Zoom', 'shopglut' ), 1, 19, 1 ),
		) as $key => $conf ) {
			echo '<label class="agf-mini"><span class="agf-mini-label">' . esc_html( $conf[0] ) . '</span><input type="number" class="agf-input agf-map-' . esc_attr( $key ) . '" name="' . esc_attr( $this->name( '[' . $key . ']' ) ) . '" min="' . esc_attr( $conf[1] ) . '" max="' . esc_attr( $conf[2] ) . '" step="' . esc_attr( $conf[3] ) . '" value="' . esc_attr( $v[ $key ] ) . '"></label>';
		}
		echo '</div>';
		echo '<div class="agf-map-frame" style="height:' . (int) $this->opt( 'height', 260 ) . 'px"><div class="agf-map-empty">' . AppGlut::icon( 'dashicons-location-alt' ) . '<span>' . esc_html__( 'Enter coordinates to preview the location', 'shopglut' ) . '</span></div></div>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '</div>';
	}

	public function sanitize( $value ) {
		$clean = $this->sanitize_map(
			$value,
			array(
				'address' => 'text',
				'lat'     => 'number',
				'lng'     => 'number',
				'zoom'    => 'int',
			)
		);
		if ( '' !== $clean['lat'] ) {
			$clean['lat'] = max( -90, min( 90, $clean['lat'] ) );
		}
		if ( '' !== $clean['lng'] ) {
			$clean['lng'] = max( -180, min( 180, $clean['lng'] ) );
		}
		$clean['zoom'] = max( 1, min( 19, (int) $clean['zoom'] ) );
		return $clean;
	}
}

/**
 * Link: url, text, target (new tab), nofollow.
 */
class AppGlut_Field_link extends AppGlut_Field {

	public function render() {
		$v = wp_parse_args(
			(array) $this->value,
			array(
				'url'      => '',
				'text'     => '',
				'target'   => false,
				'nofollow' => false,
			)
		);

		echo '<div class="agf-link">';
		echo '<div class="agf-input-group"><span class="agf-addon agf-addon-icon">' . AppGlut::icon( 'dashicons-admin-links' ) . '</span><input type="url" class="agf-input" name="' . esc_attr( $this->name( '[url]' ) ) . '" value="' . esc_attr( $v['url'] ) . '" placeholder="https://"></div>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '<input type="text" class="agf-input" name="' . esc_attr( $this->name( '[text]' ) ) . '" value="' . esc_attr( $v['text'] ) . '" placeholder="' . esc_attr__( 'Link text', 'shopglut' ) . '">';
		echo '<div class="agf-choices agf-choices-inline">';
		foreach ( array(
			'target'   => __( 'Open in new tab', 'shopglut' ),
			'nofollow' => __( 'Add rel="nofollow"', 'shopglut' ),
		) as $key => $label ) {
			echo '<label class="agf-check"><input type="hidden" name="' . esc_attr( $this->name( '[' . $key . ']' ) ) . '" value="0" data-agf-skip><input type="checkbox" name="' . esc_attr( $this->name( '[' . $key . ']' ) ) . '" value="1"' . checked( ! empty( $v[ $key ] ), true, false ) . '><span class="agf-check-box"></span><span class="agf-check-label">' . esc_html( $label ) . '</span></label>';
		}
		echo '</div></div>';
	}

	public function sanitize( $value ) {
		return $this->sanitize_map(
			$value,
			array(
				'url'      => 'url',
				'text'     => 'text',
				'target'   => 'bool',
				'nofollow' => 'bool',
			)
		);
	}
}

/**
 * Date / time using native pickers. Options: mode (date|time|datetime|month|week), range (bool), min, max.
 * Value: string, or array( from, to ) when range is on.
 */
class AppGlut_Field_date extends AppGlut_Field {

	private function mode() {
		$mode = $this->opt( 'mode', 'date' );
		return in_array( $mode, array( 'date', 'time', 'datetime', 'month', 'week' ), true ) ? $mode : 'date';
	}

	public function render() {
		$type = 'datetime' === $this->mode() ? 'datetime-local' : $this->mode();
		$base = array(
			'type'  => $type,
			'class' => 'agf-input agf-date',
			'min'   => $this->opt( 'min' ),
			'max'   => $this->opt( 'max' ),
		);

		if ( $this->opt( 'range' ) ) {
			$v = wp_parse_args(
				(array) $this->value,
				array(
					'from' => '',
					'to'   => '',
				)
			);
			echo '<div class="agf-date-range">';
			echo '<input' . $this->attrs( array_merge( $base, array( 'name' => $this->name( '[from]' ), 'value' => $v['from'], 'aria-label' => __( 'From', 'shopglut' ) ) ) ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput
			echo '<span class="agf-date-sep">&rarr;</span>';
			echo '<input' . $this->attrs( array_merge( $base, array( 'name' => $this->name( '[to]' ), 'value' => $v['to'], 'aria-label' => __( 'To', 'shopglut' ) ) ) ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput
			echo '</div>';
			return;
		}

		echo '<input' . $this->attrs( array_merge( $base, array( 'name' => $this->name(), 'value' => is_scalar( $this->value ) ? $this->value : '' ) ) ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}

	private function clean_one( $value ) {
		$patterns = array(
			'date'     => '/^\d{4}-\d{2}-\d{2}$/',
			'time'     => '/^\d{2}:\d{2}(:\d{2})?$/',
			'datetime' => '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}(:\d{2})?$/',
			'month'    => '/^\d{4}-\d{2}$/',
			'week'     => '/^\d{4}-W\d{2}$/',
		);
		$value    = is_scalar( $value ) ? trim( (string) $value ) : '';
		return preg_match( $patterns[ $this->mode() ], $value ) ? $value : '';
	}

	public function sanitize( $value ) {
		if ( $this->opt( 'range' ) ) {
			$value = (array) $value;
			return array(
				'from' => $this->clean_one( isset( $value['from'] ) ? $value['from'] : '' ),
				'to'   => $this->clean_one( isset( $value['to'] ) ? $value['to'] : '' ),
			);
		}
		return $this->clean_one( $value );
	}
}
