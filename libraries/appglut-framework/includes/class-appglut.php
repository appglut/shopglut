<?php
/**
 * Core: public API, field registry, rendering, sanitizing and assets.
 *
 * @package AppGlut_Framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class AppGlut {

	public static $version = APPGLUT_VERSION;
	public static $dir     = '';
	public static $url     = '';

	/** @var AppGlut_Container[] Registered containers keyed by unique ID. */
	public static $containers = array();

	/** @var array Sections keyed by container unique ID. */
	public static $sections = array();

	/** @var array Validation errors collected during the current save. */
	public static $errors = array();

	/** @var int Current repeater nesting depth (for clone placeholders). */
	public static $depth = 0;

	/** Field types that render UI only and never store a value. */
	public static $no_value = array( 'heading', 'subheading', 'notice', 'submessage', 'content', 'divider', 'callback', 'button', 'backup' );

	/** Built-in field type => file in /fields. */
	private static $field_map = array(
		'text'         => 'text',
		'textarea'     => 'text',
		'number'       => 'text',
		'hidden'       => 'text',
		'checkbox'     => 'choice',
		'radio'        => 'choice',
		'switcher'     => 'choice',
		'button_set'   => 'choice',
		'image_select' => 'choice',
		'palette'      => 'choice',
		'select'       => 'choice',
		'tags'         => 'choice',
		'rating'       => 'choice',
		'color'        => 'design',
		'color_group'  => 'design',
		'link_color'   => 'design',
		'gradient'     => 'design',
		'box_shadow'   => 'design',
		'border'       => 'design',
		'spacing'      => 'design',
		'dimensions'   => 'design',
		'typography'   => 'design',
		'background'   => 'design',
		'slider'       => 'design',
		'spinner'      => 'design',
		'media'        => 'media',
		'gallery'      => 'media',
		'upload'       => 'media',
		'icon'         => 'media',
		'map'          => 'media',
		'link'         => 'media',
		'date'         => 'media',
		'code_editor'  => 'editor',
		'wp_editor'    => 'editor',
		'heading'      => 'layout',
		'subheading'   => 'layout',
		'notice'       => 'layout',
		'submessage'   => 'layout',
		'content'      => 'layout',
		'divider'      => 'layout',
		'callback'     => 'layout',
		'button'       => 'layout',
		'fieldset'     => 'containers',
		'group'        => 'containers',
		'repeater'     => 'containers',
		'accordion'    => 'containers',
		'tabbed'       => 'containers',
		'sortable'     => 'containers',
		'sorter'       => 'containers',
		'key_value'    => 'containers',
		'backup'       => 'backup',
	);

	/** Custom field types registered by developers: type => array( class, file ). */
	private static $custom_fields = array();

	/** Icon sets: name => array( key => array( 'label' => '', 'svg' => '<svg…>' ) ). */
	private static $icon_sets = array();

	private static $assets_done  = false;
	private static $script_data  = array( 'code' => array() );
	private static $uid          = 0;

	/* ---------------------------------------------------------------------
	 * Bootstrap
	 * ------------------------------------------------------------------- */

	public static function init() {
		self::$dir = wp_normalize_path( APPGLUT_DIR );

		$url = self::detect_url();

		self::$url = trailingslashit( apply_filters( 'appglut_url', $url ) );

		add_action( 'init', array( __CLASS__, 'load_textdomain' ) );
		add_action( 'admin_footer', array( __CLASS__, 'print_script_data' ), 1 );
		add_action( 'customize_controls_print_footer_scripts', array( __CLASS__, 'print_script_data' ), 1 );

		AppGlut_Ajax::init();
		AppGlut_Output::init();

		do_action( 'appglut_loaded' );
	}

	/**
	 * Public URL of the framework folder. Works when it is a plugin, bundled in a
	 * theme or plugin, or symlinked (PHP reports the resolved path of symlinks).
	 */
	private static function detect_url() {
		$file = wp_normalize_path( APPGLUT_FILE );
		$dir  = self::$dir;

		// Installed as a plugin, also via symlink (WordPress maps the real path to the plugin path).
		$base = plugin_basename( APPGLUT_FILE );
		if ( '' !== $base && $base !== ltrim( $file, '/' ) ) {
			return plugins_url( '', APPGLUT_FILE );
		}

		// Bundled: compare against known folders, both as given and with symlinks resolved.
		$roots = array(
			get_stylesheet_directory() => get_stylesheet_directory_uri(),
			get_template_directory()   => get_template_directory_uri(),
			WP_PLUGIN_DIR              => plugins_url(),
			WP_CONTENT_DIR             => content_url(),
			ABSPATH                    => site_url(),
		);
		foreach ( $roots as $path => $root_url ) {
			foreach ( array_unique( array( wp_normalize_path( $path ), wp_normalize_path( (string) realpath( $path ) ) ) ) as $candidate ) {
				$candidate = untrailingslashit( $candidate );
				if ( '' !== $candidate && 0 === strpos( $dir . '/', $candidate . '/' ) ) {
					return untrailingslashit( $root_url ) . substr( $dir, strlen( $candidate ) );
				}
			}
		}

		// Unknown location: use the 'appglut_url' filter to set it.
		return site_url( str_replace( wp_normalize_path( ABSPATH ), '', $dir ) );
	}

	public static function load_textdomain() {
		$locale = function_exists( 'determine_locale' ) ? determine_locale() : get_locale();
		load_textdomain( 'appglut-framework', self::$dir . '/languages/appglut-framework-' . $locale . '.mo' );
	}

	/* ---------------------------------------------------------------------
	 * Public API
	 * ------------------------------------------------------------------- */

	/** Create an admin options panel. */
	public static function createOptions( $unique, $args = array() ) {
		return self::$containers[ $unique ] = new AppGlut_Options( $unique, $args );
	}

	/** Create a post metabox. */
	public static function createMetabox( $unique, $args = array() ) {
		return self::$containers[ $unique ] = new AppGlut_Metabox( $unique, $args );
	}

	/** Create taxonomy term options. */
	public static function createTaxonomyOptions( $unique, $args = array() ) {
		return self::$containers[ $unique ] = new AppGlut_Taxonomy( $unique, $args );
	}

	/** Create user profile options. */
	public static function createProfileOptions( $unique, $args = array() ) {
		return self::$containers[ $unique ] = new AppGlut_Profile( $unique, $args );
	}

	/** Create a classic widget with framework fields. */
	public static function createWidget( $unique, $args = array() ) {
		return self::$containers[ $unique ] = new AppGlut_Widget_Container( $unique, $args );
	}

	/** Create customizer options (Appearance → Customize). */
	public static function createCustomizeOptions( $unique, $args = array() ) {
		return self::$containers[ $unique ] = new AppGlut_Customizer( $unique, $args );
	}

	/** Create a metabox on the comment edit screen. */
	public static function createCommentMetabox( $unique, $args = array() ) {
		return self::$containers[ $unique ] = new AppGlut_Comment( $unique, $args );
	}

	/** Create fields for every nav menu item. */
	public static function createNavMenuOptions( $unique, $args = array() ) {
		return self::$containers[ $unique ] = new AppGlut_Nav_Menu( $unique, $args );
	}

	/** Create a shortcode generator (classic editor button + block). */
	public static function createShortcoder( $unique, $args = array() ) {
		return self::$containers[ $unique ] = new AppGlut_Shortcoder( $unique, $args );
	}

	/** Add a section (with fields) to any container. */
	public static function createSection( $unique, $section = array() ) {
		self::$sections[ $unique ][] = (array) $section;
	}

	/**
	 * Register a custom field type.
	 *
	 * @param string $type  Field type slug used in 'type' => '...'.
	 * @param string $class Class name extending AppGlut_Field.
	 * @param string $file  Optional file to include lazily.
	 */
	public static function registerField( $type, $class, $file = '' ) {
		self::$custom_fields[ $type ] = array( $class, $file );
	}

	/**
	 * Register a set of SVG icons for icon fields: 'icons' => 'my-set'.
	 *
	 * @param string $name  Set name.
	 * @param array  $icons key => SVG markup, or key => array( 'label' => '', 'svg' => '', 'group' => '' ).
	 */
	public static function registerIconSet( $name, $icons ) {
		$set = array();
		foreach ( (array) $icons as $key => $icon ) {
			$svg   = is_array( $icon ) ? ( isset( $icon['svg'] ) ? $icon['svg'] : '' ) : (string) $icon;
			$label = is_array( $icon ) && ! empty( $icon['label'] ) ? $icon['label'] : ucwords( str_replace( array( '-', '_' ), ' ', preg_replace( '/^(fa[bsr]?|i)-/', '', (string) $key ) ) );
			if ( '' !== $svg ) {
				$set[ (string) $key ] = array(
					'label' => $label,
					'svg'   => $svg,
				);
				// Optional group: the picker shows one tab per group.
				if ( is_array( $icon ) && ! empty( $icon['group'] ) ) {
					$set[ (string) $key ]['group'] = (string) $icon['group'];
				}
			}
		}
		self::$icon_sets[ $name ] = $set;
	}

	/** A registered icon set (empty array when unknown). */
	public static function icon_set( $name ) {
		return isset( self::$icon_sets[ $name ] ) ? self::$icon_sets[ $name ] : array();
	}

	/** SVG markup for an icon of a set, or ''. */
	public static function icon_svg( $set, $key ) {
		$icons = self::icon_set( $set );
		return isset( $icons[ $key ] ) ? $icons[ $key ]['svg'] : '';
	}

	/** @return AppGlut_Container|null */
	public static function container( $unique ) {
		return isset( self::$containers[ $unique ] ) ? self::$containers[ $unique ] : null;
	}

	/** Saved values (with defaults) for an options panel. */
	public static function get_values( $unique ) {
		$container = self::container( $unique );
		if ( $container instanceof AppGlut_Options || $container instanceof AppGlut_Customizer ) {
			return $container->get_values();
		}
		return (array) get_option( $unique, array() );
	}

	/* ---------------------------------------------------------------------
	 * Field registry
	 * ------------------------------------------------------------------- */

	/** Resolve (and lazily load) the class for a field type. */
	public static function field_class( $type ) {
		$type = sanitize_key( $type );

		if ( isset( self::$custom_fields[ $type ] ) ) {
			list( $class, $file ) = self::$custom_fields[ $type ];
			if ( ! class_exists( $class, false ) && $file && is_readable( $file ) ) {
				require_once $file;
			}
			return class_exists( $class, false ) ? $class : false;
		}

		$class = 'AppGlut_Field_' . $type;
		if ( ! class_exists( $class, false ) && isset( self::$field_map[ $type ] ) ) {
			require_once self::$dir . '/fields/' . self::$field_map[ $type ] . '.php';
		}

		$class = apply_filters( 'appglut_field_class', $class, $type );
		return class_exists( $class, false ) ? $class : false;
	}

	public static function field_types() {
		return array_merge( array_keys( self::$field_map ), array_keys( self::$custom_fields ) );
	}

	/** Nested field lists of a container field (fields / accordions / tabs). */
	public static function child_lists( $field ) {
		$lists = array();
		if ( ! empty( $field['fields'] ) && is_array( $field['fields'] ) ) {
			$lists[] = $field['fields'];
		}
		foreach ( array( 'accordions', 'tabs' ) as $key ) {
			if ( ! empty( $field[ $key ] ) && is_array( $field[ $key ] ) ) {
				foreach ( $field[ $key ] as $item ) {
					if ( ! empty( $item['fields'] ) ) {
						$lists[] = $item['fields'];
					}
				}
			}
		}
		return $lists;
	}

	/** Visit every field (recursively). */
	public static function walk_fields( $fields, $callback ) {
		foreach ( (array) $fields as $field ) {
			if ( ! is_array( $field ) ) {
				continue;
			}
			call_user_func( $callback, $field );
			foreach ( self::child_lists( $field ) as $list ) {
				self::walk_fields( $list, $callback );
			}
		}
	}

	/** Default values for a list of fields (recursive for containers). */
	public static function defaults( $fields ) {
		$defaults = array();
		foreach ( (array) $fields as $field ) {
			if ( empty( $field['id'] ) || empty( $field['type'] ) || in_array( $field['type'], self::$no_value, true ) ) {
				continue;
			}
			if ( array_key_exists( 'default', $field ) ) {
				$defaults[ $field['id'] ] = $field['default'];
			} elseif ( in_array( $field['type'], array( 'group', 'repeater', 'key_value' ), true ) ) {
				$defaults[ $field['id'] ] = array();
			} elseif ( $lists = self::child_lists( $field ) ) {
				$defaults[ $field['id'] ] = self::defaults( call_user_func_array( 'array_merge', $lists ) );
			}
		}
		return $defaults;
	}

	/**
	 * Sanitize posted data against field definitions. Unknown keys are dropped.
	 *
	 * @param array $fields Field definitions.
	 * @param mixed $posted Raw (unslashed) data.
	 * @param array $old    Previous values, restored when validation fails.
	 */
	public static function sanitize_fields( $fields, $posted, $old = array() ) {
		$clean  = array();
		$posted = is_array( $posted ) ? $posted : array();
		$old    = is_array( $old ) ? $old : array();

		foreach ( (array) $fields as $field ) {
			if ( empty( $field['id'] ) || empty( $field['type'] ) || in_array( $field['type'], self::$no_value, true ) ) {
				continue;
			}

			$class = self::field_class( $field['type'] );
			if ( ! $class ) {
				continue;
			}

			$id  = $field['id'];
			$raw = array_key_exists( $id, $posted ) ? $posted[ $id ] : null;
			$obj = new $class( $field, $raw );

			// Validators see the submitted value (trimmed) so sanitizing cannot hide bad input.
			if ( ! empty( $field['validate'] ) && is_callable( $field['validate'] ) ) {
				$error = call_user_func( $field['validate'], is_string( $raw ) ? trim( $raw ) : $raw, $field );
				if ( ! empty( $error ) ) {
					self::$errors[ $id ] = wp_strip_all_tags( $error );
					$clean[ $id ]        = array_key_exists( $id, $old ) ? $old[ $id ] : ( isset( $field['default'] ) ? $field['default'] : $obj->sanitize( null ) );
					continue;
				}
			}

			if ( ! empty( $field['sanitize'] ) && is_callable( $field['sanitize'] ) ) {
				$clean[ $id ] = call_user_func( $field['sanitize'], $raw, $field );
			} else {
				$clean[ $id ] = $obj->sanitize( $raw );
			}
		}

		return $clean;
	}

	/** Recursively sanitize plain text values. */
	public static function sanitize_deep( $value ) {
		if ( is_array( $value ) ) {
			$out = array();
			foreach ( $value as $k => $v ) {
				$out[ sanitize_text_field( (string) $k ) ] = self::sanitize_deep( $v );
			}
			return $out;
		}
		if ( is_bool( $value ) || is_int( $value ) || is_float( $value ) ) {
			return $value;
		}
		return null === $value ? '' : sanitize_text_field( (string) $value );
	}

	/* ---------------------------------------------------------------------
	 * Rendering
	 * ------------------------------------------------------------------- */

	public static function uid( $prefix = 'agf' ) {
		return $prefix . '-' . ( ++self::$uid );
	}

	/** Render a list of fields. */
	public static function fields( $fields, $values = array(), $prefix = '', $where = '', $parent = null ) {
		$values = is_array( $values ) ? $values : array();
		foreach ( (array) $fields as $field ) {
			if ( ! is_array( $field ) ) {
				continue;
			}
			$id    = isset( $field['id'] ) ? $field['id'] : '';
			$value = ( '' !== $id && array_key_exists( $id, $values ) ) ? $values[ $id ] : null;
			self::field( $field, $value, $prefix, $where, $parent );
		}
	}

	/** Render one field wrapper with its control. */
	public static function field( $field, $value = null, $prefix = '', $where = '', $parent = null ) {
		$type  = ! empty( $field['type'] ) ? $field['type'] : 'text';
		$class = self::field_class( $type );

		if ( ! $class ) {
			echo '<div class="agf-field agf-field-missing"><div class="agf-notice agf-notice-error"><div class="agf-notice-body">';
			/* translators: %s: field type */
			printf( esc_html__( 'Field type "%s" does not exist.', 'shopglut' ), esc_html( $type ) );
			echo '</div></div></div>';
			return;
		}

		if ( null === $value && array_key_exists( 'default', $field ) ) {
			$value = $field['default'];
		}

		$obj    = new $class( $field, $value, $prefix, $where, $parent );
		$layout = ! empty( $field['layout'] ) ? $field['layout'] : $obj->layout();
		$title  = isset( $field['title'] ) ? $field['title'] : '';

		$classes = array( 'agf-field', 'agf-field-' . $type, 'agf-layout-' . $layout );
		if ( '' === $title ) {
			$classes[] = 'agf-no-title';
		}
		if ( ! empty( $field['class'] ) ) {
			$classes[] = $field['class'];
		}

		$atts = ' class="' . esc_attr( implode( ' ', $classes ) ) . '" data-type="' . esc_attr( $type ) . '"';
		if ( ! empty( $field['id'] ) ) {
			$atts .= ' data-field-id="' . esc_attr( $field['id'] ) . '"';
		}
		if ( ! empty( $field['col'] ) ) {
			$atts .= ' style="--agf-col:' . max( 1, min( 12, (int) $field['col'] ) ) . '"';
		}
		$atts .= self::dependency_attrs( $field );

		echo '<div' . $atts . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Attributes escaped in dependency_attrs().

		if ( '' !== $title ) {
			echo '<div class="agf-field-head">';
			echo '<div class="agf-field-title">' . wp_kses_post( $title );
			if ( ! empty( $field['badge'] ) ) {
				echo ' <span class="agf-badge">' . esc_html( $field['badge'] ) . '</span>';
			}
			if ( ! empty( $field['help'] ) ) {
				echo ' <span class="agf-help" tabindex="0" data-tip="' . esc_attr( wp_strip_all_tags( $field['help'] ) ) . '">?</span>';
			}
			echo '</div>';
			if ( ! empty( $field['subtitle'] ) ) {
				echo '<div class="agf-field-subtitle">' . wp_kses_post( $field['subtitle'] ) . '</div>';
			}
			echo '</div>';
		}

		echo '<div class="agf-field-body">';
		$obj->render();
		if ( ! empty( $field['desc'] ) ) {
			echo '<div class="agf-desc">' . wp_kses_post( $field['desc'] ) . '</div>';
		}
		echo '<div class="agf-error-text" hidden></div>';
		echo '</div>';

		echo '</div>';
	}

	/**
	 * Dependency attributes. Accepted shapes:
	 *   'dependency' => array( 'field_id', '==', 'value' )
	 *   'dependency' => array( array( 'a', '==', '1' ), array( 'b', '!=', '' ) )
	 *   'dependency_relation' => 'or'
	 */
	public static function dependency_attrs( $field ) {
		$dep = self::dependency_rules( $field );
		if ( ! $dep ) {
			return '';
		}
		return ' data-dep="' . esc_attr( wp_json_encode( $dep['rules'] ) ) . '" data-dep-rel="' . $dep['relation'] . '"';
	}

	/**
	 * Normalised dependency rules: array( 'rules' => [ { f, o, v } ], 'relation' => and|or ),
	 * or null when the field has none.
	 */
	public static function dependency_rules( $field ) {
		if ( empty( $field['dependency'] ) || ! is_array( $field['dependency'] ) ) {
			return null;
		}

		$rules = is_array( reset( $field['dependency'] ) ) ? $field['dependency'] : array( $field['dependency'] );
		$out   = array();

		foreach ( $rules as $rule ) {
			$rule = array_values( (array) $rule );
			if ( empty( $rule[0] ) ) {
				continue;
			}
			if ( count( $rule ) === 2 ) {
				$rule = array( $rule[0], '==', $rule[1] );
			}
			$out[] = array(
				'f' => (string) $rule[0],
				'o' => isset( $rule[1] ) ? (string) $rule[1] : 'not-empty',
				'v' => isset( $rule[2] ) ? ( is_bool( $rule[2] ) ? ( $rule[2] ? '1' : '0' ) : $rule[2] ) : '',
			);
		}

		if ( ! $out ) {
			return null;
		}

		return array(
			'rules'    => $out,
			'relation' => ( ! empty( $field['dependency_relation'] ) && 'or' === strtolower( $field['dependency_relation'] ) ) ? 'or' : 'and',
		);
	}

	/** Icon markup for Dashicons or any icon-font class string. */
	public static function icon( $icon, $extra = '' ) {
		if ( empty( $icon ) ) {
			return '';
		}
		$classes = array_filter( array_map( 'sanitize_html_class', preg_split( '/\s+/', (string) $icon ) ) );
		if ( preg_grep( '/^dashicons-/', $classes ) && ! in_array( 'dashicons', $classes, true ) ) {
			array_unshift( $classes, 'dashicons' );
		}
		return '<i class="agf-icon ' . esc_attr( trim( implode( ' ', $classes ) . ' ' . $extra ) ) . '" aria-hidden="true"></i>';
	}

	/**
	 * Shortcode attributes → clean values for the given fields, with defaults.
	 * Decodes what the shortcode generator writes: JSON objects in single
	 * quotes, comma lists for multi-value fields and escaped quotes/brackets.
	 *
	 * @param array        $fields Field definitions (the shortcode section's fields).
	 * @param array|string $atts   Raw attributes from the shortcode callback.
	 */
	public static function shortcode_atts( $fields, $atts ) {
		$atts = is_array( $atts ) ? $atts : array();
		$data = array();

		foreach ( (array) $fields as $field ) {
			if ( empty( $field['id'] ) || ! array_key_exists( strtolower( $field['id'] ), $atts ) ) {
				continue;
			}
			// WordPress lowercases attribute names.
			$raw = (string) $atts[ strtolower( $field['id'] ) ];

			if ( isset( $raw[0] ) && ( '{' === $raw[0] || 0 === strpos( $raw, '\u005b' ) ) ) {
				$json = json_decode( str_replace( array( '\u005b', '\u005d', '\u0027' ), array( '[', ']', "'" ), $raw ), true );
				$data[ $field['id'] ] = is_array( $json ) ? $json : array();
				continue;
			}

			$raw = preg_replace( array( '/&quot;|&#0*34;/', '/&#0*91;/', '/&#0*93;/' ), array( '"', '[', ']' ), $raw );

			$multi = in_array( $field['type'], array( 'tags', 'sorter', 'sortable' ), true )
				|| ( in_array( $field['type'], array( 'checkbox', 'button_set' ), true ) && ( ! empty( $field['options'] ) && ( 'checkbox' === $field['type'] || ! empty( $field['multiple'] ) ) ) )
				|| ( 'select' === $field['type'] && ! empty( $field['multiple'] ) )
				|| ( isset( $field['default'] ) && is_array( $field['default'] ) && array_values( $field['default'] ) === $field['default'] );

			$data[ $field['id'] ] = $multi ? array_filter( array_map( 'trim', explode( ',', $raw ) ), 'strlen' ) : $raw;
		}

		$clean = self::sanitize_fields( $fields, $data );
		foreach ( self::defaults( $fields ) as $id => $default ) {
			if ( ! array_key_exists( $id, $data ) ) {
				$clean[ $id ] = $default;
			}
		}
		return $clean;
	}

	/** Signs data passed through the browser (e.g. ajax select queries). */
	public static function sign( $data ) {
		return wp_hash( wp_json_encode( $data ), 'nonce' );
	}

	/* ---------------------------------------------------------------------
	 * Option data sources for select / checkbox / radio
	 * ------------------------------------------------------------------- */

	/**
	 * Resolve an options source to value => label pairs.
	 *
	 * Sources: posts, pages, post_type (args: post_type), categories, tags,
	 * terms (args: taxonomy), menus, users, roles, post_types, taxonomies,
	 * sidebars, menu_locations, image_sizes, or any callable.
	 */
	public static function get_data( $type, $term = '', $args = array() ) {
		$options = array();
		$args    = (array) $args;
		$term    = (string) $term;
		$filter  = false;

		switch ( $type ) {
			case 'post':
			case 'posts':
			case 'page':
			case 'pages':
			case 'post_type':
				$default_type = in_array( $type, array( 'page', 'pages' ), true ) ? 'page' : 'post';
				$query        = new WP_Query(
					wp_parse_args(
						$args,
						array(
							'post_type'           => $default_type,
							'post_status'         => 'publish',
							's'                   => $term,
							'posts_per_page'      => '' !== $term ? 25 : 300,
							'orderby'             => 'title',
							'order'               => 'ASC',
							'no_found_rows'       => true,
							'ignore_sticky_posts' => true,
						)
					)
				);
				foreach ( $query->posts as $post ) {
					/* translators: %d: post ID */
					$options[ $post->ID ] = '' !== $post->post_title ? $post->post_title : sprintf( __( '(no title) #%d', 'shopglut' ), $post->ID );
				}
				break;

			case 'category':
			case 'categories':
			case 'tag':
			case 'tags':
			case 'term':
			case 'terms':
			case 'menu':
			case 'menus':
				$taxonomies = array(
					'category'   => 'category',
					'categories' => 'category',
					'tag'        => 'post_tag',
					'tags'       => 'post_tag',
					'menu'       => 'nav_menu',
					'menus'      => 'nav_menu',
				);
				$taxonomy   = isset( $taxonomies[ $type ] ) ? $taxonomies[ $type ] : ( isset( $args['taxonomy'] ) ? $args['taxonomy'] : 'category' );
				$terms      = get_terms(
					wp_parse_args(
						$args,
						array(
							'taxonomy'   => $taxonomy,
							'hide_empty' => false,
							'search'     => $term,
							'number'     => '' !== $term ? 25 : 0,
						)
					)
				);
				if ( ! is_wp_error( $terms ) ) {
					foreach ( $terms as $item ) {
						$options[ $item->term_id ] = $item->name;
					}
				}
				break;

			case 'user':
			case 'users':
				$query = new WP_User_Query(
					wp_parse_args(
						$args,
						array(
							'search'  => '' !== $term ? '*' . $term . '*' : '',
							'number'  => '' !== $term ? 25 : 300,
							'orderby' => 'display_name',
							'fields'  => array( 'ID', 'display_name' ),
						)
					)
				);
				foreach ( (array) $query->get_results() as $user ) {
					$options[ $user->ID ] = $user->display_name;
				}
				break;

			case 'role':
			case 'roles':
				$filter = true;
				foreach ( wp_roles()->roles as $key => $role ) {
					$options[ $key ] = translate_user_role( $role['name'] );
				}
				break;

			case 'post_types':
				$filter = true;
				foreach ( get_post_types( wp_parse_args( $args, array( 'public' => true ) ), 'objects' ) as $object ) {
					$options[ $object->name ] = $object->labels->name;
				}
				break;

			case 'taxonomies':
				$filter = true;
				foreach ( get_taxonomies( wp_parse_args( $args, array( 'public' => true ) ), 'objects' ) as $object ) {
					$options[ $object->name ] = $object->labels->name;
				}
				break;

			case 'sidebar':
			case 'sidebars':
				$filter = true;
				foreach ( (array) $GLOBALS['wp_registered_sidebars'] as $sidebar ) {
					$options[ $sidebar['id'] ] = $sidebar['name'];
				}
				break;

			case 'menu_locations':
				$filter  = true;
				$options = get_registered_nav_menus();
				break;

			case 'image_sizes':
				$filter = true;
				foreach ( get_intermediate_image_sizes() as $size ) {
					$options[ $size ] = ucwords( str_replace( array( '_', '-' ), ' ', $size ) );
				}
				$options['full'] = __( 'Full', 'shopglut' );
				break;

			default:
				if ( is_callable( $type ) ) {
					$options = (array) call_user_func( $type, $term, $args );
				}
				break;
		}

		if ( $filter && '' !== $term ) {
			$options = array_filter(
				$options,
				function ( $label ) use ( $term ) {
					return false !== stripos( $label, $term );
				}
			);
		}

		return apply_filters( 'appglut_get_data', $options, $type, $term, $args );
	}

	/** Labels for already-selected values of a dynamic source (ajax selects). */
	public static function get_data_titles( $type, $values, $args = array() ) {
		$out = array();
		foreach ( (array) $values as $value ) {
			if ( '' === $value || null === $value ) {
				continue;
			}
			$label = '';
			if ( in_array( $type, array( 'post', 'posts', 'page', 'pages', 'post_type' ), true ) ) {
				$label = get_the_title( $value );
			} elseif ( in_array( $type, array( 'category', 'categories', 'tag', 'tags', 'term', 'terms', 'menu', 'menus' ), true ) ) {
				$item  = get_term( (int) $value );
				$label = ( $item && ! is_wp_error( $item ) ) ? $item->name : '';
			} elseif ( in_array( $type, array( 'user', 'users' ), true ) ) {
				$user  = get_user_by( 'id', (int) $value );
				$label = $user ? $user->display_name : '';
			} else {
				$all   = self::get_data( $type, '', $args );
				$label = isset( $all[ $value ] ) ? $all[ $value ] : '';
			}
			$out[ $value ] = '' !== $label ? $label : '#' . $value;
		}
		return $out;
	}

	/* ---------------------------------------------------------------------
	 * Assets
	 * ------------------------------------------------------------------- */

	/**
	 * Enqueue framework assets plus what the given fields need. Safe to call
	 * multiple times (several containers on the same screen).
	 */
	public static function enqueue( $fields = array() ) {
		if ( ! self::$assets_done ) {
			self::$assets_done = true;

			wp_enqueue_style( 'dashicons' );
			wp_enqueue_style( 'appglut', self::$url . 'assets/css/appglut.css', array(), self::$version );
			wp_enqueue_script( 'appglut', self::$url . 'assets/js/appglut.js', array( 'jquery', 'jquery-ui-sortable' ), self::$version, true );

			wp_localize_script(
				'appglut',
				'appglutL10n',
				array(
					'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
					'queryNonce'    => wp_create_nonce( 'appglut_query' ),
					'saved'         => __( 'Settings saved', 'shopglut' ),
					'saving'        => __( 'Saving…', 'shopglut' ),
					'error'         => __( 'Something went wrong. Please try again.', 'shopglut' ),
					'invalid'       => __( 'Some fields have errors. Please review them.', 'shopglut' ),
					'unsaved'       => __( 'You have unsaved changes. Leave anyway?', 'shopglut' ),
					'resetAll'      => __( 'Reset ALL options to their defaults? This cannot be undone.', 'shopglut' ),
					'resetSection'  => __( 'Reset this section to its defaults?', 'shopglut' ),
					'importConfirm' => __( 'Import will overwrite your current settings. Continue?', 'shopglut' ),
					'removeConfirm' => __( 'Remove this item?', 'shopglut' ),
					'maxItems'      => __( 'Maximum number of items reached.', 'shopglut' ),
					'minItems'      => __( 'Minimum number of items reached.', 'shopglut' ),
					'noResults'     => __( 'No results found', 'shopglut' ),
					'searching'     => __( 'Searching…', 'shopglut' ),
					'typeToSearch'  => __( 'Type to search…', 'shopglut' ),
					'all'           => __( 'All', 'shopglut' ),
					'selectImage'   => __( 'Select image', 'shopglut' ),
					'selectFile'    => __( 'Select file', 'shopglut' ),
					'useThis'       => __( 'Use this', 'shopglut' ),
					'change'        => __( 'Change', 'shopglut' ),
					'addImages'     => __( 'Add to gallery', 'shopglut' ),
					'copied'        => __( 'Copied to clipboard', 'shopglut' ),
					'characters'    => __( 'characters', 'shopglut' ),
					'searchFields'  => __( 'No fields match your search.', 'shopglut' ),
					'invalidJson'   => __( 'The import data is not valid JSON.', 'shopglut' ),
				)
			);
		}

		self::walk_fields(
			$fields,
			function ( $field ) {
				if ( empty( $field['type'] ) ) {
					return;
				}
				$class = AppGlut::field_class( $field['type'] );
				if ( $class ) {
					AppGlut::apply_assets( call_user_func( array( $class, 'assets' ), $field ) );
				}
			}
		);
	}

	/** @internal Applies the asset flags returned by a field class. */
	public static function apply_assets( $assets ) {
		if ( ! empty( $assets['media'] ) && function_exists( 'wp_enqueue_media' ) ) {
			wp_enqueue_media();
		}
		if ( ! empty( $assets['editor'] ) && function_exists( 'wp_enqueue_editor' ) ) {
			wp_enqueue_editor();
		}
		if ( ! empty( $assets['icon_set'] ) && ! isset( self::$script_data['iconSets'][ $assets['icon_set'] ] ) ) {
			$set = array();
			foreach ( self::icon_set( $assets['icon_set'] ) as $key => $icon ) {
				$set[] = isset( $icon['group'] ) ? array( $key, $icon['label'], $icon['svg'], $icon['group'] ) : array( $key, $icon['label'], $icon['svg'] );
			}
			self::$script_data['iconSets'][ $assets['icon_set'] ] = $set;
		}
		if ( ! empty( $assets['icons'] ) && ! isset( self::$script_data['icons'] ) ) {
			self::$script_data['icons'] = include self::$dir . '/includes/data/dashicons.php';
		}
		if ( ! empty( $assets['fonts'] ) && ! isset( self::$script_data['fonts'] ) ) {
			self::$script_data['fonts'] = include self::$dir . '/includes/data/fonts.php';
		}
		if ( ! empty( $assets['code'] ) && ! array_key_exists( $assets['code'], self::$script_data['code'] ) && function_exists( 'wp_enqueue_code_editor' ) ) {
			$mimes = array(
				'css'        => 'text/css',
				'scss'       => 'text/x-scss',
				'less'       => 'text/x-less',
				'javascript' => 'text/javascript',
				'js'         => 'text/javascript',
				'json'       => 'application/json',
				'html'       => 'text/html',
				'xml'        => 'application/xml',
				'php'        => 'application/x-httpd-php',
				'sql'        => 'text/x-sql',
				'markdown'   => 'text/x-markdown',
			);
			$mode = $assets['code'];
			$mime = isset( $mimes[ $mode ] ) ? $mimes[ $mode ] : 'text/plain';

			self::$script_data['code'][ $mode ] = wp_enqueue_code_editor( array( 'type' => $mime ) );
		}
	}

	/** Prints data needed by the scripts (icons, fonts, code editor settings). */
	public static function print_script_data() {
		if ( self::$assets_done ) {
			wp_add_inline_script( 'appglut', 'window.appglutData = ' . wp_json_encode( self::$script_data ) . ';', 'before' );
		}
	}
}
