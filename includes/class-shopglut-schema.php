<?php
/**
 * Options schema helpers.
 *
 * The template settings of the old Glut plugins were written for their own Codestar-style framework
 * (AGSHOPGLUT::createMetabox / createSection). ShopGlut_Schema offers the same two calls, so those
 * files can be reused as they are (with AGSHOPGLUT renamed to ShopGlut_Schema): capture() runs a
 * settings file and returns what it declared as AppGlut sections. Each metabox becomes one section
 * whose fields sit in a fieldset with the metabox ID, so the saved values keep the old shape:
 * settings[ metabox_id ][ field_id ] … and the old template markup reads them unchanged.
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Schema class.
 */
class ShopGlut_Schema {

	/**
	 * Metaboxes declared while capturing: id => array( args, sections[] ).
	 *
	 * @var array|null
	 */
	private static $boxes = null;

	/**
	 * Old API: declare a metabox.
	 *
	 * @param string $id   Metabox ID.
	 * @param array  $args title, context …
	 * @return void
	 */
	public static function createMetabox( $id, $args = array() ) { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName
		if ( null === self::$boxes ) {
			return;
		}
		self::$boxes[ $id ] = array( 'args' => (array) $args, 'sections' => array() );
	}

	/**
	 * Old API: options pages are declared the same way as metaboxes.
	 *
	 * @param string $id   Panel ID.
	 * @param array  $args Arguments.
	 * @return void
	 */
	public static function createOptions( $id, $args = array() ) { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName
		self::createMetabox( $id, $args );
	}

	/**
	 * Old API: add a section to a metabox.
	 *
	 * @param string $id      Metabox ID.
	 * @param array  $section title, icon, fields.
	 * @return void
	 */
	public static function createSection( $id, $section = array() ) { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName
		if ( null === self::$boxes ) {
			return;
		}
		if ( ! isset( self::$boxes[ $id ] ) ) {
			self::$boxes[ $id ] = array( 'args' => array(), 'sections' => array() );
		}
		self::$boxes[ $id ]['sections'][] = (array) $section;
	}

	/**
	 * Run a settings file (or callable) and return its metaboxes as AppGlut sections.
	 *
	 * @param string|callable $source File path or callable.
	 * @return array AppGlut sections.
	 */
	public static function capture( $source ) {
		$boxes = self::run( $source );

		$out = array();
		foreach ( $boxes as $id => $box ) {
			$fields = array();
			foreach ( $box['sections'] as $section ) {
				if ( ! empty( $section['title'] ) && count( $box['sections'] ) > 1 ) {
					$fields[] = array( 'type' => 'subheading', 'content' => $section['title'] );
				}
				foreach ( (array) ( isset( $section['fields'] ) ? $section['fields'] : array() ) as $field ) {
					$field = self::normalize( $field );
					if ( $field ) {
						$fields[] = $field;
					}
				}
			}
			if ( ! $fields ) {
				continue; // Preview-only boxes.
			}
			$out[] = array(
				'id'     => sanitize_key( $id ),
				'title'  => isset( $box['args']['title'] ) ? wp_strip_all_tags( $box['args']['title'] ) : $id,
				'icon'   => 'dashicons-admin-appearance',
				'fields' => array(
					array(
						'id'     => $id,
						'type'   => 'fieldset',
						'fields' => $fields,
					),
				),
			);
		}
		return $out;
	}

	/**
	 * Run a settings file of an old options page and return its sections as they are
	 * (id, parent, title, fields), for a module Settings panel. Values keep the old flat shape.
	 *
	 * @param string|callable $source File path or callable.
	 * @return array AppGlut sections.
	 */
	public static function capture_sections( $source ) {
		$out = array();
		foreach ( self::run( $source ) as $box ) {
			foreach ( $box['sections'] as $section ) {
				$section['fields'] = array_values( array_filter( array_map( array( __CLASS__, 'normalize' ), (array) ( $section['fields'] ?? array() ) ) ) );
				if ( isset( $section['icon'] ) && 0 !== strpos( (string) $section['icon'], 'dashicons' ) ) {
					unset( $section['icon'] );
				}
				$out[] = $section;
			}
		}
		return $out;
	}

	/**
	 * Collect the metaboxes a file or callable declares.
	 *
	 * @param string|callable $source File path or callable.
	 * @return array
	 */
	private static function run( $source ) {
		self::$boxes = array();
		if ( is_callable( $source ) ) {
			call_user_func( $source );
		} elseif ( is_string( $source ) && is_readable( $source ) ) {
			include $source; // Not include_once: a file can be captured for several layouts in one request.
		}
		$boxes       = self::$boxes;
		self::$boxes = null;
		return $boxes;
	}

	/**
	 * Turn an old field definition into an AppGlut one (recursively).
	 *
	 * @param array $field Field.
	 * @return array|null Null drops the field.
	 */
	public static function normalize( $field ) {
		if ( ! is_array( $field ) ) {
			return null;
		}
		$type = isset( $field['type'] ) ? $field['type'] : 'text';

		if ( ! empty( $field['dependency'] ) && is_array( $field['dependency'] ) ) {
			$field['dependency'] = self::dependency( $field['dependency'] );
		}

		switch ( $type ) {
			case 'preview':
			case 'publish':             // The editor has its own Save button.
			case 'select_archive_page': // Replaced by the layout's Display Rules.
				return null;
			case 'min_max':
				$field = array_merge(
					array_intersect_key( $field, array_flip( array( 'id', 'title', 'subtitle', 'desc', 'dependency' ) ) ),
					array(
						'type'   => 'fieldset',
						'fields' => array(
							array( 'id' => 'min', 'type' => 'number', 'title' => __( 'Min', 'shopglut' ), 'default' => $field['min_value'] ?? '', 'col' => 6 ),
							array( 'id' => 'max', 'type' => 'number', 'title' => __( 'Max', 'shopglut' ), 'default' => $field['max_value'] ?? '', 'col' => 6 ),
						),
					)
				);
				break;
			case 'tax_color':
			case 'tax_images':
				// One color (or image) per term of the taxonomy: value[ term_id ].
				$taxonomy = (string) ( $field['product_option'] ?? 'product_cat' );
				$sub      = array();
				foreach ( self::terms( $taxonomy ) as $term ) {
					$sub[] = array(
						'id'    => (string) $term->term_id,
						'type'  => 'tax_color' === $type ? 'color' : 'media',
						'title' => $term->name,
						'col'   => 'tax_color' === $type ? 4 : 6,
					);
				}
				if ( ! $sub ) {
					$sub[] = array( 'type' => 'notice', 'style' => 'info', 'content' => __( 'No terms yet.', 'shopglut' ) );
				}
				$field = array_merge(
					array_intersect_key( $field, array_flip( array( 'id', 'title', 'subtitle', 'desc', 'dependency' ) ) ),
					array( 'type' => 'fieldset', 'fields' => $sub )
				);
				break;
			case 'term_styling':
				// Colors per term of every variation attribute: value[ term_slug ][ bg_color … ].
				$accordions = array();
				if ( function_exists( 'wc_get_attribute_taxonomies' ) ) {
					foreach ( wc_get_attribute_taxonomies() as $tax ) {
						$fields = array();
						foreach ( self::terms( wc_attribute_taxonomy_name( $tax->attribute_name ) ) as $term ) {
							$fields[] = array(
								'id'     => $term->slug,
								'type'   => 'fieldset',
								'title'  => $term->name,
								'fields' => array(
									array( 'id' => 'bg_color', 'type' => 'color', 'title' => __( 'Background', 'shopglut' ), 'col' => 4 ),
									array( 'id' => 'text_color', 'type' => 'color', 'title' => __( 'Text', 'shopglut' ), 'col' => 4 ),
									array( 'id' => 'border_color', 'type' => 'color', 'title' => __( 'Border', 'shopglut' ), 'col' => 4 ),
								),
							);
						}
						if ( $fields ) {
							$accordions[] = array( 'title' => $tax->attribute_label, 'fields' => $fields );
						}
					}
				}
				$field = $accordions
					? array_merge( array_intersect_key( $field, array_flip( array( 'id', 'title', 'desc', 'dependency' ) ) ), array( 'type' => 'accordion', 'accordions' => $accordions ) )
					: array( 'type' => 'notice', 'style' => 'info', 'content' => __( 'Per-term styling appears here once the store has variation attributes with terms.', 'shopglut' ) );
				break;
			case 'select_badge_display':
			case 'select_filter_page':
				$badges           = 'select_badge_display' === $type;
				$field['type']    = 'select';
				$field['options'] = static function () use ( $badges ) {
					$list = array( 'Woo Shop Page' => __( 'Shop page', 'shopglut' ) );
					if ( $badges ) {
						$list += array(
							'Single Product Template1' => __( 'Single product page', 'shopglut' ),
							'All Categories'           => __( 'All category pages', 'shopglut' ),
							'All Tags'                 => __( 'All tag pages', 'shopglut' ),
							'All Products'             => __( 'All products', 'shopglut' ),
						);
						foreach ( self::terms( 'product_cat' ) as $term ) {
							/* translators: %s: category name */
							$list[ 'cat_' . $term->term_id ] = sprintf( __( 'Category: %s', 'shopglut' ), $term->name );
						}
						foreach ( self::terms( 'product_tag' ) as $term ) {
							/* translators: %s: tag name */
							$list[ 'tag_' . $term->term_id ] = sprintf( __( 'Tag: %s', 'shopglut' ), $term->name );
						}
					}
					return $list;
				};
				break;
			case 'space':
				$field = array( 'type' => 'divider' );
				break;
			case 'select_products':
				$field['type']       = 'select';
				$field['options']    = 'post_type';
				$field['query_args'] = array( 'post_type' => 'product' );
				$field['ajax']       = true;
				break;
			case 'select_badge_layouts':
			case 'select_comparison_layouts':
			case 'select_custom_fields':
			case 'select_swatchs':
			case 'select_wishlist_layouts':
			case 'select_quickview_layouts':
				$modules = array(
					'select_badge_layouts'      => 'product-badges',
					'select_swatchs'            => 'product-swatches',
					'select_comparison_layouts' => 'product-comparison',
					'select_custom_fields'      => 'product-custom-fields',
					'select_wishlist_layouts'   => 'wishlist',
					'select_quickview_layouts'  => 'quick-view',
				);
				$module           = $modules[ $type ];
				$field['type']    = 'select';
				$field['options'] = static function () use ( $module ) {
					$list = array( '' => __( '— None —', 'shopglut' ) );
					foreach ( ShopGlut_Layouts::query( $module, array( 'per_page' => 200 ) )['items'] as $row ) {
						$list[ $row['id'] ] = $row['name'];
					}
					return $list;
				};
				break;
		}

		/**
		 * Map an old field type that AppGlut does not know (return the new field, or null to drop it).
		 *
		 * @param array $field Field.
		 */
		if ( ! AppGlut::field_class( $field['type'] ) ) {
			$field = apply_filters( 'shopglut_schema_legacy_field', $field );
			if ( ! $field || ! AppGlut::field_class( $field['type'] ) ) {
				return null;
			}
		}

		// Old files use Font Awesome class names; the panels use Dashicons.
		if ( ! empty( $field['icon'] ) && 'icon' !== $field['type'] && 0 !== strpos( (string) $field['icon'], 'dashicons' ) ) {
			unset( $field['icon'] );
		}

		foreach ( array( 'fields' ) as $key ) {
			if ( ! empty( $field[ $key ] ) && is_array( $field[ $key ] ) ) {
				$field[ $key ] = array_values( array_filter( array_map( array( __CLASS__, 'normalize' ), $field[ $key ] ) ) );
			}
		}
		foreach ( array( 'tabs', 'accordions' ) as $key ) {
			if ( ! empty( $field[ $key ] ) && is_array( $field[ $key ] ) ) {
				foreach ( $field[ $key ] as $i => $item ) {
					if ( isset( $item['icon'] ) && 0 !== strpos( (string) $item['icon'], 'dashicons' ) ) {
						unset( $field[ $key ][ $i ]['icon'] );
					}
					if ( ! empty( $item['fields'] ) ) {
						$field[ $key ][ $i ]['fields'] = array_values( array_filter( array_map( array( __CLASS__, 'normalize' ), $item['fields'] ) ) );
					}
				}
			}
		}
		return $field;
	}

	/**
	 * Terms of a taxonomy (empty when it does not exist).
	 *
	 * @param string $taxonomy Taxonomy.
	 * @return WP_Term[]
	 */
	private static function terms( $taxonomy ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			return array();
		}
		$terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false, 'number' => 200 ) );
		return is_wp_error( $terms ) ? array() : $terms;
	}

	/**
	 * Old dependency syntax → AppGlut rules. Old files chain rules with pipes
	 * ( 'a|b', '==|!=', 'x|y' ) and compare switchers with 'true' / 'false'.
	 *
	 * @param array $dep Dependency.
	 * @return array List of array( id, operator, value ).
	 */
	private static function dependency( $dep ) {
		$rules = is_array( reset( $dep ) ) ? $dep : array( $dep );
		$out   = array();
		foreach ( $rules as $rule ) {
			$rule = array_values( (array) $rule );
			if ( count( $rule ) === 2 ) {
				$rule = array( $rule[0], '==', $rule[1] );
			}
			$ids  = explode( '|', (string) $rule[0] );
			$ops  = explode( '|', isset( $rule[1] ) ? (string) $rule[1] : '==' );
			$vals = is_string( $rule[2] ?? '' ) ? explode( '|', (string) ( $rule[2] ?? '' ) ) : array( $rule[2] );
			foreach ( $ids as $i => $id ) {
				$value = isset( $vals[ $i ] ) ? $vals[ $i ] : end( $vals );
				if ( true === $value || 'true' === $value ) {
					$value = '1';
				} elseif ( false === $value || 'false' === $value ) {
					$value = '0';
				}
				$out[] = array( $id, isset( $ops[ $i ] ) ? $ops[ $i ] : $ops[0], $value );
			}
		}
		return $out;
	}
}
