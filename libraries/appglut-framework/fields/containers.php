<?php
/**
 * Container fields: fieldset, repeater, group, key_value, accordion, tabbed, sortable, sorter.
 *
 * @package AppGlut_Framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A fixed set of sub-fields saved as one array. Options: fields.
 */
class AppGlut_Field_fieldset extends AppGlut_Field {

	public function layout() {
		return 'stacked';
	}

	public function render() {
		echo '<div class="agf-fieldset agf-grid">';
		AppGlut::fields( $this->opt( 'fields', array() ), (array) $this->value, $this->name(), $this->where, $this );
		echo '</div>';
	}

	public function sanitize( $value ) {
		return AppGlut::sanitize_fields( $this->opt( 'fields', array() ), $value );
	}
}

/**
 * Cloneable rows of sub-fields (compact layout).
 *
 * Options: fields, button_title, min, max, sortable (bool), clone (bool).
 */
class AppGlut_Field_repeater extends AppGlut_Field {

	protected $style = 'repeater';

	public function layout() {
		return 'stacked';
	}

	protected function item_fields() {
		return (array) $this->opt( 'fields', array() );
	}

	public function render() {
		$items = is_array( $this->value ) ? array_values( array_filter( $this->value, 'is_array' ) ) : array();

		AppGlut::$depth++;
		$placeholder = '__AGF_I' . AppGlut::$depth . '__';
		$base        = $this->name();

		$atts = array(
			'class'            => 'agf-repeater agf-repeater-' . $this->style,
			'data-prefix'      => $base,
			'data-placeholder' => $placeholder,
			'data-min'         => (int) $this->opt( 'min', 0 ),
			'data-max'         => (int) $this->opt( 'max', 0 ),
			'data-sortable'    => false !== $this->opt( 'sortable', true ) ? '1' : '0',
			'data-title-field' => $this->title_field(),
		);
		echo '<div' . self::build_attrs( $atts ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput

		echo '<div class="agf-repeater-items">';
		foreach ( $items as $index => $item ) {
			$this->render_item( $item, $base . '[' . $index . ']', $index );
		}
		echo '</div>';

		echo '<div class="agf-repeater-empty"' . ( $items ? ' hidden' : '' ) . '>' . esc_html( $this->opt( 'empty_text', __( 'No items yet.', 'shopglut' ) ) ) . '</div>';

		echo '<template class="agf-repeater-template">';
		$this->render_item( array(), $base . '[' . $placeholder . ']', $placeholder, true );
		echo '</template>';

		echo '<div class="agf-repeater-footer"><button type="button" class="agf-btn agf-btn-secondary agf-repeater-add">' . AppGlut::icon( 'dashicons-plus-alt2' ) . '<span>' . esc_html( $this->opt( 'button_title', __( 'Add item', 'shopglut' ) ) ) . '</span></button>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '<span class="agf-repeater-count"></span></div>';

		echo '</div>';
		AppGlut::$depth--;
	}

	/** Sub-field whose value is used as the item title (group style). */
	protected function title_field() {
		if ( $this->opt( 'title_field' ) ) {
			return $this->opt( 'title_field' );
		}
		foreach ( $this->item_fields() as $field ) {
			if ( ! empty( $field['id'] ) && isset( $field['type'] ) && 'text' === $field['type'] ) {
				return $field['id'];
			}
		}
		return '';
	}

	protected function render_item( $values, $prefix, $index, $template = false ) {
		$title_field = $this->title_field();
		$title       = ( $title_field && ! empty( $values[ $title_field ] ) && is_scalar( $values[ $title_field ] ) ) ? $values[ $title_field ] : '';
		$collapsed   = 'group' === $this->style && ! $template && $this->opt( 'collapsed', true );

		echo '<div class="agf-repeater-item' . ( $collapsed ? ' agf-collapsed' : '' ) . '" data-index="' . esc_attr( $index ) . '">';

		echo '<div class="agf-repeater-head">';
		if ( false !== $this->opt( 'sortable', true ) ) {
			echo '<span class="agf-repeater-handle" title="' . esc_attr__( 'Drag to reorder', 'shopglut' ) . '" aria-hidden="true"></span>';
		}
		if ( 'group' === $this->style ) {
			echo '<button type="button" class="agf-repeater-toggle"><span class="agf-repeater-num"></span><span class="agf-repeater-title" data-fallback="' . esc_attr( $this->opt( 'item_title', __( 'Item', 'shopglut' ) ) ) . '">' . esc_html( $title ) . '</span></button>';
		}
		echo '<span class="agf-repeater-actions">';
		if ( false !== $this->opt( 'clone', true ) ) {
			echo '<button type="button" class="agf-icon-btn agf-repeater-clone" title="' . esc_attr__( 'Duplicate', 'shopglut' ) . '">' . AppGlut::icon( 'dashicons-admin-page' ) . '</button>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		echo '<button type="button" class="agf-icon-btn agf-repeater-remove" title="' . esc_attr__( 'Remove', 'shopglut' ) . '">' . AppGlut::icon( 'dashicons-trash' ) . '</button>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '</span></div>';

		echo '<div class="agf-repeater-body agf-grid">';
		AppGlut::fields( $this->item_fields(), $values, $prefix, $this->where, $this );
		echo '</div>';

		echo '</div>';
	}

	public function sanitize( $value ) {
		$items = array();
		foreach ( (array) $value as $item ) {
			if ( is_array( $item ) ) {
				$items[] = AppGlut::sanitize_fields( $this->item_fields(), $item );
			}
		}
		$max = (int) $this->opt( 'max', 0 );
		return $max ? array_slice( $items, 0, $max ) : $items;
	}
}

/**
 * Collapsible, titled repeater items. Options as repeater plus: title_field, item_title, collapsed.
 */
class AppGlut_Field_group extends AppGlut_Field_repeater {
	protected $style = 'group';
}

/**
 * Key / value pairs. Options: key_placeholder, value_placeholder, button_title.
 */
class AppGlut_Field_key_value extends AppGlut_Field_repeater {

	protected $style = 'key-value';

	protected function item_fields() {
		return array(
			array(
				'id'          => 'key',
				'type'        => 'text',
				'placeholder' => $this->opt( 'key_placeholder', __( 'Key', 'shopglut' ) ),
			),
			array(
				'id'          => 'value',
				'type'        => 'text',
				'placeholder' => $this->opt( 'value_placeholder', __( 'Value', 'shopglut' ) ),
			),
		);
	}

	protected function title_field() {
		return '';
	}

	public function sanitize( $value ) {
		return array_values(
			array_filter(
				parent::sanitize( $value ),
				function ( $row ) {
					return '' !== $row['key'] || '' !== $row['value'];
				}
			)
		);
	}
}

/**
 * Collapsible panels of fields; values of all panels are saved in one array.
 * Options: accordions => array( array( 'title', 'icon', 'fields', 'open' ) ).
 */
class AppGlut_Field_accordion extends AppGlut_Field {

	public function layout() {
		return 'stacked';
	}

	public function render() {
		echo '<div class="agf-accordion">';
		foreach ( (array) $this->opt( 'accordions', array() ) as $i => $item ) {
			$open = ! empty( $item['open'] ) || ( 0 === $i && $this->opt( 'first_open', true ) );
			echo '<details class="agf-accordion-item"' . ( $open ? ' open' : '' ) . '>';
			echo '<summary class="agf-accordion-head">' . AppGlut::icon( isset( $item['icon'] ) ? $item['icon'] : '' ) . '<span>' . esc_html( isset( $item['title'] ) ? $item['title'] : '' ) . '</span></summary>'; // phpcs:ignore WordPress.Security.EscapeOutput
			echo '<div class="agf-accordion-body agf-grid">';
			AppGlut::fields( isset( $item['fields'] ) ? $item['fields'] : array(), (array) $this->value, $this->name(), $this->where, $this );
			echo '</div></details>';
		}
		echo '</div>';
	}

	public function sanitize( $value ) {
		$lists = AppGlut::child_lists( $this->field );
		return $lists ? AppGlut::sanitize_fields( call_user_func_array( 'array_merge', $lists ), $value ) : array();
	}
}

/**
 * Tabs of fields; values of all tabs are saved in one array.
 * Options: tabs => array( array( 'title', 'icon', 'fields' ) ).
 */
class AppGlut_Field_tabbed extends AppGlut_Field_accordion {

	public function render() {
		$tabs = (array) $this->opt( 'tabs', array() );
		echo '<div class="agf-tabbed">';
		echo '<div class="agf-tabbed-nav" role="tablist">';
		foreach ( $tabs as $i => $tab ) {
			echo '<button type="button" class="agf-tabbed-tab' . ( 0 === $i ? ' agf-active' : '' ) . '" data-tab="' . (int) $i . '" role="tab">' . AppGlut::icon( isset( $tab['icon'] ) ? $tab['icon'] : '' ) . '<span>' . esc_html( isset( $tab['title'] ) ? $tab['title'] : '' ) . '</span></button>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		echo '</div>';
		foreach ( $tabs as $i => $tab ) {
			echo '<div class="agf-tabbed-pane agf-grid" data-tab="' . (int) $i . '" role="tabpanel"' . ( 0 === $i ? '' : ' hidden' ) . '>';
			AppGlut::fields( isset( $tab['fields'] ) ? $tab['fields'] : array(), (array) $this->value, $this->name(), $this->where, $this );
			echo '</div>';
		}
		echo '</div>';
	}
}

/**
 * Fields the user can reorder; value keeps the chosen order. Options: fields.
 */
class AppGlut_Field_sortable extends AppGlut_Field {

	public function layout() {
		return 'stacked';
	}

	private function ordered_fields( $value ) {
		$fields = array();
		foreach ( (array) $this->opt( 'fields', array() ) as $field ) {
			if ( ! empty( $field['id'] ) ) {
				$fields[ $field['id'] ] = $field;
			}
		}
		$ordered = array();
		foreach ( array_keys( (array) $value ) as $key ) {
			if ( isset( $fields[ $key ] ) ) {
				$ordered[] = $fields[ $key ];
				unset( $fields[ $key ] );
			}
		}
		return array_merge( $ordered, array_values( $fields ) );
	}

	public function render() {
		echo '<div class="agf-sortable">';
		foreach ( $this->ordered_fields( $this->value ) as $field ) {
			echo '<div class="agf-sortable-item"><span class="agf-repeater-handle" aria-hidden="true"></span><div class="agf-sortable-content">';
			$id = $field['id'];
			AppGlut::field( $field, is_array( $this->value ) && array_key_exists( $id, $this->value ) ? $this->value[ $id ] : null, $this->name(), $this->where, $this );
			echo '</div></div>';
		}
		echo '</div>';
	}

	public function sanitize( $value ) {
		return AppGlut::sanitize_fields( $this->ordered_fields( $value ), $value );
	}
}

/**
 * Drag items between "enabled" and "disabled" lists.
 * Options: options => array( 'enabled' => array( key => label ), 'disabled' => array( key => label ) ),
 * enabled_title, disabled_title.
 */
class AppGlut_Field_sorter extends AppGlut_Field {

	public function layout() {
		return 'stacked';
	}

	private function labels() {
		$options = (array) $this->opt( 'options', array() );
		return array_merge( isset( $options['enabled'] ) ? (array) $options['enabled'] : array(), isset( $options['disabled'] ) ? (array) $options['disabled'] : array() );
	}

	public function render() {
		$options = (array) $this->opt( 'options', array() );
		$value   = is_array( $this->value ) && ( isset( $this->value['enabled'] ) || isset( $this->value['disabled'] ) ) ? $this->value : $options;
		$labels  = $this->labels();

		echo '<div class="agf-sorter">';
		foreach ( array(
			'enabled'  => $this->opt( 'enabled_title', __( 'Enabled', 'shopglut' ) ),
			'disabled' => $this->opt( 'disabled_title', __( 'Disabled', 'shopglut' ) ),
		) as $list => $title ) {
			echo '<div class="agf-sorter-col agf-sorter-' . esc_attr( $list ) . '"><div class="agf-sorter-title">' . esc_html( $title ) . '</div>';
			echo '<ul class="agf-sorter-list" data-list="' . esc_attr( $list ) . '" data-name="' . esc_attr( $this->name() ) . '">';
			foreach ( array_keys( isset( $value[ $list ] ) ? (array) $value[ $list ] : array() ) as $key ) {
				if ( ! isset( $labels[ $key ] ) ) {
					continue;
				}
				echo '<li data-key="' . esc_attr( $key ) . '"><span class="agf-repeater-handle" aria-hidden="true"></span><span>' . esc_html( $labels[ $key ] ) . '</span>';
				echo '<input type="hidden" name="' . esc_attr( $this->name( '[' . $list . '][' . $key . ']' ) ) . '" value="' . esc_attr( $labels[ $key ] ) . '"></li>';
				unset( $labels[ $key ] );
			}
			// New options added in code after the value was saved land in "disabled".
			if ( 'disabled' === $list ) {
				foreach ( $labels as $key => $label ) {
					echo '<li data-key="' . esc_attr( $key ) . '"><span class="agf-repeater-handle" aria-hidden="true"></span><span>' . esc_html( $label ) . '</span><input type="hidden" name="' . esc_attr( $this->name( '[disabled][' . $key . ']' ) ) . '" value="' . esc_attr( $label ) . '"></li>';
				}
			}
			echo '</ul></div>';
		}
		echo '</div>';
	}

	public function sanitize( $value ) {
		$labels = $this->labels();
		$clean  = array(
			'enabled'  => array(),
			'disabled' => array(),
		);
		foreach ( array_keys( $clean ) as $list ) {
			if ( isset( $value[ $list ] ) && is_array( $value[ $list ] ) ) {
				foreach ( array_keys( $value[ $list ] ) as $key ) {
					if ( isset( $labels[ $key ] ) ) {
						$clean[ $list ][ $key ] = $labels[ $key ];
						unset( $labels[ $key ] );
					}
				}
			}
		}
		if ( null === $value ) {
			return (array) $this->opt( 'default', $this->opt( 'options', $clean ) );
		}
		return $clean;
	}
}
