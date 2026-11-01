<?php
/**
 * Shared behaviour for every container (options page, metabox, taxonomy, ...).
 *
 * @package AppGlut_Framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class AppGlut_Container {

	public $unique = '';
	public $args   = array();

	private $sections_cache = null;
	private $defaults_cache = null;

	public function __construct( $unique, $args = array() ) {
		$this->unique = $unique;
		$this->args   = apply_filters( 'appglut_' . $unique . '_args', wp_parse_args( $args, $this->default_args() ), $this );
		$this->setup();
	}

	abstract protected function default_args();

	/** Register hooks. */
	abstract protected function setup();

	public function arg( $key, $default = null ) {
		return array_key_exists( $key, $this->args ) ? $this->args[ $key ] : $default;
	}

	/* ---------------------------------------------------------------------
	 * Sections & fields
	 * ------------------------------------------------------------------- */

	/** Normalised flat list of sections (id, title, icon, description, fields, parent). */
	public function sections() {
		if ( null !== $this->sections_cache ) {
			return $this->sections_cache;
		}

		$raw = isset( AppGlut::$sections[ $this->unique ] ) ? AppGlut::$sections[ $this->unique ] : array();
		if ( ! empty( $this->args['sections'] ) ) {
			$raw = array_merge( (array) $this->args['sections'], $raw );
		}
		if ( ! empty( $this->args['fields'] ) ) {
			array_unshift( $raw, array( 'id' => 'general', 'fields' => $this->args['fields'] ) );
		}

		$sections = array();
		$used     = array();
		foreach ( $raw as $i => $section ) {
			$section = wp_parse_args(
				$section,
				array(
					'id'          => '',
					'title'       => '',
					'icon'        => '',
					'description' => '',
					'parent'      => '',
					'badge'       => '',
					'fields'      => array(),
				)
			);

			$id = $section['id'] ? sanitize_key( $section['id'] ) : sanitize_title( $section['title'] );
			if ( '' === $id ) {
				$id = 'section-' . ( $i + 1 );
			}
			while ( isset( $used[ $id ] ) ) {
				$id .= '-' . ( $i + 1 );
			}
			$used[ $id ]   = true;
			$section['id'] = $id;

			$sections[ $id ] = $section;
		}

		$sections = apply_filters( 'appglut_' . $this->unique . '_sections', $sections, $this );

		return $this->sections_cache = $sections;
	}

	/** Sections as a tree: top level items get a 'children' list. */
	public function section_tree() {
		$sections = $this->sections();
		$tree     = array();
		foreach ( $sections as $id => $section ) {
			if ( $section['parent'] && isset( $sections[ $section['parent'] ] ) ) {
				continue;
			}
			$section['children'] = array();
			foreach ( $sections as $child ) {
				if ( $child['parent'] === $id ) {
					$section['children'][] = $child;
				}
			}
			$tree[] = $section;
		}
		return $tree;
	}

	/** Every top-level field of every section. */
	public function fields() {
		$fields = array();
		foreach ( $this->sections() as $section ) {
			$fields = array_merge( $fields, (array) $section['fields'] );
		}
		return $fields;
	}

	public function defaults() {
		if ( null === $this->defaults_cache ) {
			$this->defaults_cache = AppGlut::defaults( $this->fields() );
		}
		return $this->defaults_cache;
	}

	/** First section that has fields (the initially active panel). */
	protected function first_section_id() {
		foreach ( $this->sections() as $id => $section ) {
			if ( ! empty( $section['fields'] ) ) {
				return $id;
			}
		}
		return '';
	}

	/* ---------------------------------------------------------------------
	 * Shared markup
	 * ------------------------------------------------------------------- */

	/** Root element attributes shared by all modes. */
	public function app_attrs( $mode, $extra = array() ) {
		$classes = array( 'agf-app', 'agf-mode-' . $mode );
		if ( $this->arg( 'class' ) ) {
			$classes[] = $this->arg( 'class' );
		}

		$theme = $this->arg( 'theme', 'light' );
		$atts  = array(
			'class'       => implode( ' ', $classes ),
			'data-unique' => $this->unique,
			'data-mode'   => $mode,
			'data-theme'  => 'dark' === $theme ? 'dark' : 'light',
			'data-pref'   => in_array( $theme, array( 'light', 'dark', 'auto' ), true ) ? $theme : 'light',
		);

		$accent = appglut_sanitize_color( $this->arg( 'accent', '' ) );
		if ( $accent ) {
			$atts['style'] = '--agf-accent:' . $accent . ';';
		}

		return AppGlut_Field::build_attrs( array_merge( $atts, $extra ) );
	}

	protected function render_nav( $tree, $active ) {
		echo '<ul class="agf-nav-list" role="tablist">';
		foreach ( $tree as $section ) {
			$has_children = ! empty( $section['children'] );
			$target       = ! empty( $section['fields'] ) ? $section['id'] : ( $has_children ? $section['children'][0]['id'] : $section['id'] );
			$open         = $has_children && ( $active === $section['id'] || in_array( $active, wp_list_pluck( $section['children'], 'id' ), true ) );

			echo '<li class="agf-nav-item' . ( $has_children ? ' agf-has-children' : '' ) . ( $open ? ' agf-open' : '' ) . '">';
			$this->nav_link( $section, $target, $active, $has_children );

			if ( $has_children ) {
				echo '<ul class="agf-nav-sub">';
				foreach ( $section['children'] as $child ) {
					echo '<li class="agf-nav-item">';
					$this->nav_link( $child, $child['id'], $active, false );
					echo '</li>';
				}
				echo '</ul>';
			}
			echo '</li>';
		}
		echo '</ul>';
	}

	private function nav_link( $section, $target, $active, $has_children ) {
		$is_active = ( $active === $section['id'] && ! empty( $section['fields'] ) );
		echo '<a href="#tab=' . esc_attr( $target ) . '" class="agf-nav-link' . ( $is_active ? ' agf-active' : '' ) . '" data-section="' . esc_attr( $target ) . '" data-nav-id="' . esc_attr( $section['id'] ) . '" role="tab">';
		echo $section['icon'] ? AppGlut::icon( $section['icon'] ) : '<i class="agf-icon agf-icon-dot" aria-hidden="true"></i>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '<span class="agf-nav-text">' . esc_html( $section['title'] ) . '</span>';
		if ( $section['badge'] ) {
			echo '<span class="agf-nav-badge">' . esc_html( $section['badge'] ) . '</span>';
		}
		if ( $has_children ) {
			echo '<span class="agf-nav-arrow" aria-hidden="true"></span>';
		}
		echo '</a>';
	}

	/**
	 * Render one panel per section that has fields.
	 *
	 * @param array  $values Current values.
	 * @param string $active Active section ID ('' shows all panels).
	 * @param bool   $head   Show panel title/description.
	 */
	protected function render_panels( $values, $active, $head = true ) {
		$sections = $this->sections();
		foreach ( $sections as $section ) {
			if ( empty( $section['fields'] ) ) {
				continue;
			}

			$hidden = ( '' !== $active && $active !== $section['id'] );
			echo '<section class="agf-panel" data-section="' . esc_attr( $section['id'] ) . '" role="tabpanel"' . ( $hidden ? ' hidden' : '' ) . '>';

			if ( $head && ( $section['title'] || $section['description'] ) ) {
				echo '<header class="agf-panel-head">';
				if ( $section['title'] ) {
					$parent = ( $section['parent'] && isset( $sections[ $section['parent'] ] ) ) ? $sections[ $section['parent'] ]['title'] : '';
					if ( $parent ) {
						echo '<div class="agf-panel-crumb">' . esc_html( $parent ) . '</div>';
					}
					echo '<h2 class="agf-panel-title">' . AppGlut::icon( $section['icon'] ) . '<span>' . esc_html( $section['title'] ) . '</span></h2>'; // phpcs:ignore WordPress.Security.EscapeOutput
				}
				if ( $section['description'] ) {
					echo '<div class="agf-panel-desc">' . wp_kses_post( $section['description'] ) . '</div>';
				}
				echo '</header>';
			}

			echo '<div class="agf-panel-body agf-grid">';
			AppGlut::fields( $section['fields'], $values, $this->unique, $this->where() );
			echo '</div>';
			echo '</section>';
		}
	}

	/** Context passed to fields ("options", "metabox", ...). */
	protected function where() {
		return strtolower( str_replace( 'AppGlut_', '', get_class( $this ) ) );
	}

	/** Read the posted array for this container (unslashed). */
	protected function posted() {
		// phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- verified by callers; sanitized per field.
		return isset( $_POST[ $this->unique ] ) && is_array( $_POST[ $this->unique ] ) ? wp_unslash( $_POST[ $this->unique ] ) : array();
	}

	/* ---------------------------------------------------------------------
	 * Meta storage shared by metabox / taxonomy / profile
	 * ------------------------------------------------------------------- */

	/** Load meta for an object, serialized or one key per field. */
	protected function load_meta( $meta_type, $object_id ) {
		if ( 'unserialize' === $this->arg( 'data_type' ) ) {
			$values = array();
			foreach ( $this->fields() as $field ) {
				if ( ! empty( $field['id'] ) && metadata_exists( $meta_type, $object_id, $field['id'] ) ) {
					$values[ $field['id'] ] = get_metadata( $meta_type, $object_id, $field['id'], true );
				}
			}
		} else {
			$values = get_metadata( $meta_type, $object_id, $this->unique, true );
		}

		return wp_parse_args( is_array( $values ) ? $values : array(), $this->defaults() );
	}

	/**
	 * Sanitize posted data and store it as meta.
	 *
	 * @param string     $meta_type post | term | user | comment.
	 * @param int        $object_id Object ID.
	 * @param array|null $data      Unslashed data; defaults to $_POST[ $unique ].
	 */
	protected function save_meta( $meta_type, $object_id, $data = null ) {
		AppGlut::$errors = array();

		$old   = $this->load_meta( $meta_type, $object_id );
		$data  = apply_filters( 'appglut_' . $this->unique . '_save', null === $data ? $this->posted() : (array) $data, $object_id, $this );
		$clean = AppGlut::sanitize_fields( $this->fields(), $data, $old );

		do_action( 'appglut_' . $this->unique . '_save_before', $clean, $object_id, $this );

		if ( 'unserialize' === $this->arg( 'data_type' ) ) {
			foreach ( $clean as $key => $value ) {
				if ( '' === $value || array() === $value || null === $value ) {
					delete_metadata( $meta_type, $object_id, $key );
				} else {
					update_metadata( $meta_type, $object_id, $key, $value );
				}
			}
		} else {
			update_metadata( $meta_type, $object_id, $this->unique, $clean );
		}

		do_action( 'appglut_' . $this->unique . '_saved', $clean, $object_id, $this );
	}
}
