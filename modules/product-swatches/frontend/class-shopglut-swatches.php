<?php
/**
 * Variation Swatches front end, and the swatch fields of attribute terms.
 *
 * Single product pages: the dropdown of each variation attribute is replaced by the swatches of the
 * style (layout) that matches the attribute. The original <select> stays (hidden) so WooCommerce's
 * variation script keeps working; the swatch script only changes its value.
 *
 * Terms: Products › Attributes › terms get a color, an image and a short label (term meta
 * shopglut_swatch_color / _image / _label). Attributes get the swatch types in their type list.
 *
 * Templates: 1 dropdown (styled select), 2 buttons, 3 round color, 4 square color, 5 image,
 * 6 pill label, 7 radio list, 8 color + label, 9 image + label, 10 underline.
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Swatches class.
 */
class ShopGlut_Swatches {

	const META_COLOR = 'shopglut_swatch_color';
	const META_IMAGE = 'shopglut_swatch_image';
	const META_LABEL = 'shopglut_swatch_label';

	/**
	 * Module URL.
	 *
	 * @var string
	 */
	private $url;

	/**
	 * Styles printed on this page (layout ID => CSS).
	 *
	 * @var array
	 */
	private $css = array();

	/**
	 * Constructor.
	 *
	 * @param string $url Module URL.
	 */
	public function __construct( $url ) {
		$this->url = $url;
		add_filter( 'woocommerce_dropdown_variation_attribute_options_html', array( $this, 'swatches' ), 20, 2 );
		add_action( 'wp_enqueue_scripts', array( $this, 'register' ), 30 );
		add_action( 'wp_footer', array( $this, 'print_css' ), 5 );

		if ( is_admin() ) {
			add_filter( 'product_attributes_type_selector', array( $this, 'attribute_types' ) );
			add_action( 'admin_init', array( $this, 'term_hooks' ) );
		}
	}

	/* ---------------------------------------------------------------------
	 * Terms (admin)
	 * ------------------------------------------------------------------- */

	/**
	 * Swatch types in the attribute "Type" list.
	 *
	 * @param array $types Types.
	 * @return array
	 */
	public function attribute_types( $types ) {
		return $types + array(
			'color'  => __( 'Color swatch', 'shopglut' ),
			'image'  => __( 'Image swatch', 'shopglut' ),
			'button' => __( 'Button / label', 'shopglut' ),
		);
	}

	/**
	 * Add the swatch fields to every attribute taxonomy.
	 *
	 * @return void
	 */
	public function term_hooks() {
		foreach ( wc_get_attribute_taxonomy_names() as $taxonomy ) {
			add_action( $taxonomy . '_add_form_fields', array( $this, 'add_fields' ) );
			add_action( $taxonomy . '_edit_form_fields', array( $this, 'edit_fields' ) );
			add_action( 'created_' . $taxonomy, array( $this, 'save_fields' ) );
			add_action( 'edited_' . $taxonomy, array( $this, 'save_fields' ) );
			add_filter( 'manage_edit-' . $taxonomy . '_columns', array( $this, 'columns' ) );
			add_filter( 'manage_' . $taxonomy . '_custom_column', array( $this, 'column' ), 10, 3 );
		}
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
	}

	/**
	 * Color picker and media library on term screens.
	 *
	 * @param string $hook Screen.
	 * @return void
	 */
	public function admin_assets( $hook ) {
		$screen = get_current_screen();
		if ( ! $screen || ! in_array( $screen->taxonomy, wc_get_attribute_taxonomy_names(), true ) ) {
			return;
		}
		wp_enqueue_style( 'wp-color-picker' );
		wp_add_inline_style( 'wp-color-picker', '.shopglut-swatch-field{margin:0 0 16px}.shopglut-swatch-field>label{display:block;margin:0 0 6px;font-weight:600}.shopglut-swatch-image{display:flex;align-items:center;gap:10px}' );
		wp_enqueue_media();
		wp_enqueue_script( 'shopglut-swatch-terms', $this->url . 'frontend/assets/swatch-terms.js', array( 'jquery', 'wp-color-picker' ), SHOPGLUT_VERSION, true );
		wp_localize_script(
			'shopglut-swatch-terms',
			'shopglutSwatchTerms',
			array(
				'choose' => __( 'Choose an image', 'shopglut' ),
				'use'    => __( 'Use this image', 'shopglut' ),
			)
		);
	}

	/**
	 * Fields HTML.
	 *
	 * @param WP_Term|null $term Term.
	 * @return string
	 */
	private function fields_html( $term = null ) {
		$color = $term ? (string) get_term_meta( $term->term_id, self::META_COLOR, true ) : '';
		$image = $term ? (int) get_term_meta( $term->term_id, self::META_IMAGE, true ) : 0;
		$label = $term ? (string) get_term_meta( $term->term_id, self::META_LABEL, true ) : '';
		$src   = $image ? wp_get_attachment_image_url( $image, 'thumbnail' ) : '';
		ob_start();
		wp_nonce_field( 'shopglut_swatch_term', 'shopglut_swatch_nonce' );
		?>
		<div class="shopglut-swatch-field">
			<label for="shopglut-swatch-color"><?php esc_html_e( 'Swatch color', 'shopglut' ); ?></label>
			<input type="text" id="shopglut-swatch-color" name="shopglut_swatch_color" class="shopglut-color-field" value="<?php echo esc_attr( $color ); ?>">
		</div>
		<div class="shopglut-swatch-field">
			<label><?php esc_html_e( 'Swatch image', 'shopglut' ); ?></label>
			<div class="shopglut-swatch-image">
				<img src="<?php echo esc_url( $src ); ?>" alt="" style="width:60px;height:60px;object-fit:cover;border-radius:6px;<?php echo $src ? '' : 'display:none;'; ?>">
				<input type="hidden" name="shopglut_swatch_image" value="<?php echo esc_attr( $image ? $image : '' ); ?>">
				<button type="button" class="button shopglut-swatch-pick"><?php esc_html_e( 'Choose image', 'shopglut' ); ?></button>
				<button type="button" class="button-link shopglut-swatch-remove"<?php echo $src ? '' : ' style="display:none"'; ?>><?php esc_html_e( 'Remove', 'shopglut' ); ?></button>
			</div>
		</div>
		<div class="shopglut-swatch-field">
			<label for="shopglut-swatch-label"><?php esc_html_e( 'Short label', 'shopglut' ); ?></label>
			<input type="text" id="shopglut-swatch-label" name="shopglut_swatch_label" value="<?php echo esc_attr( $label ); ?>" maxlength="20">
			<p class="description"><?php esc_html_e( 'Shown on button swatches instead of the name, e.g. "XL".', 'shopglut' ); ?></p>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Add term screen.
	 *
	 * @return void
	 */
	public function add_fields() {
		echo '<div class="form-field">' . $this->fields_html() . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in fields_html().
	}

	/**
	 * Edit term screen.
	 *
	 * @param WP_Term $term Term.
	 * @return void
	 */
	public function edit_fields( $term ) {
		echo '<tr class="form-field"><th scope="row">' . esc_html__( 'Swatch', 'shopglut' ) . '</th><td>' . $this->fields_html( $term ) . '</td></tr>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in fields_html().
	}

	/**
	 * Save term fields.
	 *
	 * @param int $term_id Term.
	 * @return void
	 */
	public function save_fields( $term_id ) {
		if ( ! isset( $_POST['shopglut_swatch_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['shopglut_swatch_nonce'] ) ), 'shopglut_swatch_term' ) || ! current_user_can( 'manage_product_terms' ) ) {
			return;
		}
		$color = isset( $_POST['shopglut_swatch_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['shopglut_swatch_color'] ) ) : '';
		$image = isset( $_POST['shopglut_swatch_image'] ) ? absint( $_POST['shopglut_swatch_image'] ) : 0;
		$label = isset( $_POST['shopglut_swatch_label'] ) ? sanitize_text_field( wp_unslash( $_POST['shopglut_swatch_label'] ) ) : '';
		foreach ( array( self::META_COLOR => $color, self::META_IMAGE => $image, self::META_LABEL => $label ) as $key => $value ) {
			if ( $value ) {
				update_term_meta( $term_id, $key, $value );
			} else {
				delete_term_meta( $term_id, $key );
			}
		}
	}

	/**
	 * Swatch column in the term list.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public function columns( $columns ) {
		$new = array();
		foreach ( $columns as $key => $label ) {
			if ( 'name' === $key ) {
				$new['shopglut_swatch'] = __( 'Swatch', 'shopglut' );
			}
			$new[ $key ] = $label;
		}
		return $new;
	}

	/**
	 * Swatch column content.
	 *
	 * @param string $out     Output.
	 * @param string $column  Column.
	 * @param int    $term_id Term.
	 * @return string
	 */
	public function column( $out, $column, $term_id ) {
		if ( 'shopglut_swatch' !== $column ) {
			return $out;
		}
		$term = get_term( $term_id );
		$data = self::term_data( $term );
		if ( $data['image'] ) {
			return '<img src="' . esc_url( $data['image'] ) . '" alt="" style="width:28px;height:28px;border-radius:50%;object-fit:cover">';
		}
		if ( $data['color'] ) {
			return '<span style="display:inline-block;width:28px;height:28px;border-radius:50%;border:1px solid #ddd;background:' . esc_attr( $data['color'] ) . '"></span>';
		}
		return $data['label'] ? esc_html( $data['label'] ) : '&ndash;';
	}

	/* ---------------------------------------------------------------------
	 * Data
	 * ------------------------------------------------------------------- */

	/**
	 * CSS color names the browser understands (used when a color term has no color set).
	 *
	 * @var string[]
	 */
	const NAMED = array( 'black', 'white', 'red', 'green', 'blue', 'yellow', 'orange', 'purple', 'pink', 'brown', 'gray', 'grey', 'silver', 'gold', 'navy', 'teal', 'maroon', 'olive', 'lime', 'aqua', 'cyan', 'magenta', 'beige', 'ivory', 'khaki', 'coral', 'salmon', 'tan', 'violet', 'indigo', 'turquoise', 'lavender', 'mint', 'plum', 'orchid', 'crimson', 'chocolate', 'tomato', 'skyblue', 'royalblue', 'darkgreen', 'darkblue', 'darkred' );

	/**
	 * Swatch data of a term.
	 *
	 * @param WP_Term $term Term.
	 * @return array color, image, label
	 */
	private static function term_data( $term ) {
		if ( ! $term instanceof WP_Term ) {
			return array(
				'color' => '',
				'image' => '',
				'label' => '',
			);
		}
		$color = (string) get_term_meta( $term->term_id, self::META_COLOR, true );
		if ( ! $color ) {
			$slug  = strtolower( preg_replace( '/[^a-z]/i', '', $term->slug ) );
			$color = 'mint' === $slug ? '#98ff98' : ( in_array( $slug, self::NAMED, true ) ? $slug : '' );
		}
		$image = (int) get_term_meta( $term->term_id, self::META_IMAGE, true );
		return array(
			'color' => $color,
			'image' => $image ? (string) wp_get_attachment_image_url( $image, 'woocommerce_gallery_thumbnail' ) : '',
			'label' => (string) get_term_meta( $term->term_id, self::META_LABEL, true ),
		);
	}

	/**
	 * Settings box of a layout.
	 *
	 * @param array $layout Layout.
	 * @return array
	 */
	private static function box( $layout ) {
		$key = 'shopg_product_swatches_settings_' . shopglut_sanitize_template( $layout['template'] );
		return isset( $layout['settings'][ $key ] ) && is_array( $layout['settings'][ $key ] ) ? $layout['settings'][ $key ] : array();
	}

	/**
	 * A value from a settings fieldset.
	 *
	 * @param array  $box     Box.
	 * @param string $group   Fieldset ID ('' for top level).
	 * @param string $key     Key.
	 * @param mixed  $default Default.
	 * @return mixed
	 */
	private static function v( $box, $group, $key, $default ) {
		$src   = ( $group && isset( $box[ $group ] ) && is_array( $box[ $group ] ) ) ? $box[ $group ] : $box;
		$value = array_key_exists( $key, $src ) ? $src[ $key ] : self::find( $box, $key );
		if ( '' === $value || null === $value ) {
			return $default;
		}
		return ( is_array( $value ) && isset( $value[ $key ] ) ) ? $value[ $key ] : $value;
	}

	/**
	 * Find a key at any depth (settings files nest fieldsets inside tabs).
	 *
	 * @param array  $arr Values.
	 * @param string $key Key.
	 * @return mixed|null
	 */
	private static function find( $arr, $key ) {
		foreach ( $arr as $k => $v ) {
			if ( $k === $key ) {
				return $v;
			}
			if ( is_array( $v ) && 'per_term_styling' !== $k ) {
				$found = self::find( $v, $key );
				if ( null !== $found ) {
					return $found;
				}
			}
		}
		return null;
	}

	/* ---------------------------------------------------------------------
	 * Front end
	 * ------------------------------------------------------------------- */

	/**
	 * Replace a variation dropdown with swatches.
	 *
	 * @param string $html Dropdown HTML.
	 * @param array  $args Arguments of wc_dropdown_variation_attribute_options().
	 * @return string
	 */
	public function swatches( $html, $args ) {
		$attribute = isset( $args['attribute'] ) ? (string) $args['attribute'] : '';
		$product   = isset( $args['product'] ) ? $args['product'] : null;
		if ( ! $attribute || ! $product instanceof WC_Product ) {
			return $html;
		}
		$layout = shopglut_get_active_layout( 'product-swatches', array( 'taxonomy' => $attribute ) );
		if ( ! $layout ) {
			return $html;
		}
		$box = self::box( $layout );
		if ( ! self::v( $box, 'product-swatches-settings', 'enable_variation_overwrite', true ) ) {
			return $html;
		}
		$n = (int) preg_replace( '/\D/', '', $layout['template'] );
		$n = $n >= 1 && $n <= 10 ? $n : 2;
		wp_enqueue_style( 'shopglut-swatches' );
		wp_enqueue_script( 'shopglut-swatches' );
		$this->css[ (int) $layout['id'] ] = $this->layout_css( $layout, $box, $n );

		$name  = 'attribute_' . sanitize_title( $attribute );
		$label = wc_attribute_label( $attribute, $product );

		if ( 1 === $n ) {
			return '<div class="sgs sgs-t1 sgs-l' . (int) $layout['id'] . '" data-attribute="' . esc_attr( $name ) . '">' . $html . '</div>';
		}

		$options  = isset( $args['options'] ) ? (array) $args['options'] : array();
		$selected = isset( $args['selected'] ) ? (string) $args['selected'] : '';
		$terms    = array();
		if ( taxonomy_exists( $attribute ) ) {
			foreach ( wc_get_product_terms( $product->get_id(), $attribute, array( 'fields' => 'all' ) ) as $term ) {
				if ( in_array( $term->slug, $options, true ) ) {
					$terms[] = array( $term->slug, $term->name, self::term_data( $term ), $term );
				}
			}
		} else {
			foreach ( $options as $option ) {
				$terms[] = array( $option, $option, self::term_data( null ), null );
			}
		}
		if ( ! $terms ) {
			return $html;
		}

		$per_term   = (array) self::v( $box, '', 'per_term_styling', array() );
		$tooltip    = (bool) self::v( $box, 'swatch_behavior', 'tooltip', true );
		$show_sel   = (bool) self::v( $box, 'swatch_behavior', 'show_selected', true );
		$unavail    = sanitize_key( (string) self::v( $box, 'swatch_behavior', 'unavailable', 'cross' ) );
		$deselect   = (bool) self::v( $box, 'swatch_behavior', 'deselect', true );
		$kind       = array( 2 => 'button', 3 => 'color', 4 => 'color', 5 => 'image', 6 => 'button', 7 => 'radio', 8 => 'color-label', 9 => 'image-label', 10 => 'underline' )[ $n ];
		$selected_n = '';

		$items = '';
		foreach ( $terms as $t ) {
			list( $value, $text, $data, $term ) = $t;
			$on    = $selected === $value;
			$short = $data['label'] ? $data['label'] : $text;
			if ( $on ) {
				$selected_n = $text;
			}
			$style = '';
			if ( isset( $per_term[ $value ] ) && is_array( $per_term[ $value ] ) ) {
				foreach ( array( 'bg_color' => '--sgs-term-bg', 'text_color' => '--sgs-term-text', 'border_color' => '--sgs-term-border' ) as $k => $var ) {
					$c = (string) ( $per_term[ $value ][ $k ] ?? '' );
					if ( preg_match( '/^#[0-9a-fA-F]{3,8}$/', $c ) ) {
						$style .= $var . ':' . $c . ';';
					}
				}
			}
			$visual = '';
			switch ( $kind ) {
				case 'color':
				case 'color-label':
					$visual = $data['image']
						? '<img class="sgs-swatch" src="' . esc_url( $data['image'] ) . '" alt="">'
						: ( $data['color'] ? '<span class="sgs-swatch" style="background:' . esc_attr( $data['color'] ) . '"></span>' : '' );
					break;
				case 'image':
				case 'image-label':
					$visual = $data['image']
						? '<img class="sgs-swatch" src="' . esc_url( $data['image'] ) . '" alt="">'
						: ( $data['color'] ? '<span class="sgs-swatch" style="background:' . esc_attr( $data['color'] ) . '"></span>' : '' );
					break;
				case 'radio':
					$visual = '<span class="sgs-dot"></span>';
					break;
			}
			// Colors / images without data fall back to the name.
			$show_text = in_array( $kind, array( 'button', 'radio', 'color-label', 'image-label', 'underline' ), true ) || '' === $visual;
			$items    .= sprintf(
				'<button type="button" class="sgs-item%1$s%2$s" data-value="%3$s" data-name="%4$s" aria-pressed="%5$s"%6$s%7$s>%8$s%9$s</button>',
				$on ? ' is-on' : '',
				'' === $visual && in_array( $kind, array( 'color', 'image' ), true ) ? ' is-text' : '',
				esc_attr( $value ),
				esc_attr( $text ),
				$on ? 'true' : 'false',
				$tooltip ? ' data-tip="' . esc_attr( $text ) . '"' : '',
				$style ? ' style="' . esc_attr( $style ) . '"' : '',
				$visual,
				$show_text ? '<span class="sgs-text">' . esc_html( 'button' === $kind || 'underline' === $kind ? $short : $text ) . '</span>' : '<span class="screen-reader-text">' . esc_html( $text ) . '</span>'
			);
		}

		$head = $show_sel ? '<div class="sgs-chosen" aria-live="polite"><span class="sgs-chosen-label">' . esc_html( $label ) . ':</span> <span class="sgs-chosen-value">' . esc_html( $selected_n ) . '</span></div>' : '';
		return '<div class="sgs sgs-t' . $n . ' sgs-' . esc_attr( $kind ) . ' sgs-l' . (int) $layout['id'] . ' sgs-un-' . esc_attr( $unavail ) . '" data-attribute="' . esc_attr( $name ) . '" data-deselect="' . ( $deselect ? '1' : '0' ) . '">'
			. $head
			. '<div class="sgs-items" role="group" aria-label="' . esc_attr( $label ) . '">' . $items . '</div>'
			. '<div class="sgs-select">' . $html . '</div></div>';
	}

	/**
	 * CSS of a style.
	 *
	 * @param array $layout Layout.
	 * @param array $box    Settings.
	 * @param int   $n      Template.
	 * @return string
	 */
	private function layout_css( $layout, $box, $n ) {
		$c   = function ( $v, $default ) {
			$v = (string) $v;
			return preg_match( '/^(#[0-9a-fA-F]{3,8}|rgba?\([0-9.,\s%]+\))$/', $v ) ? $v : $default;
		};
		$sel = '.sgs-l' . (int) $layout['id'];
		if ( 1 === $n ) {
			$p = 'swatch_dropdown_';
			return $sel . ' select{'
				. 'background-color:' . $c( self::v( $box, 'swatch_dropdown_container_section', $p . 'background', '#ffffff' ), '#ffffff' ) . ';'
				. 'border:' . absint( self::v( $box, 'swatch_dropdown_container_section', $p . 'border_width', 1 ) ) . 'px solid ' . $c( self::v( $box, 'swatch_dropdown_container_section', $p . 'border_color', '#d1d5db' ), '#d1d5db' ) . ';'
				. 'border-radius:' . absint( self::v( $box, 'swatch_dropdown_container_section', $p . 'border_radius', 8 ) ) . 'px;'
				. 'color:' . $c( self::v( $box, 'swatch_dropdown_typography_section', $p . 'text_color', '#1f2937' ), '#1f2937' ) . ';'
				. 'font-size:' . absint( self::v( $box, 'swatch_dropdown_typography_section', $p . 'font_size', 15 ) ) . 'px;'
				. 'min-height:' . absint( self::v( $box, 'swatch_dropdown_container_section', $p . 'min_height', 44 ) ) . 'px;padding:6px 36px 6px 12px;min-width:200px}'
				. $sel . ' select:focus{outline:none;border-color:' . $c( self::v( $box, 'swatch_dropdown_states_section', $p . 'focus_border_color', '#7c3aed' ), '#7c3aed' ) . '}';
		}
		if ( 2 === $n ) {
			$d = 'button_default_section';
			$h = 'button_hover_section';
			$a = 'button_active_section';
			return $sel . '{--sgs-bg:' . $c( self::v( $box, $d, 'button_default_background', '#ffffff' ), '#ffffff' )
				. ';--sgs-text:' . $c( self::v( $box, $d, 'button_default_text_color', '#2d3748' ), '#2d3748' )
				. ';--sgs-border:' . $c( self::v( $box, $d, 'button_default_border_color', '#e2e8f0' ), '#e2e8f0' )
				. ';--sgs-bw:' . absint( self::v( $box, $d, 'button_default_border_width', 2 ) ) . 'px'
				. ';--sgs-radius:' . absint( self::v( $box, $d, 'button_default_border_radius', 8 ) ) . 'px'
				. ';--sgs-px:' . absint( self::v( $box, $d, 'button_default_padding_x', 16 ) ) . 'px'
				. ';--sgs-py:' . absint( self::v( $box, $d, 'button_default_padding_y', 8 ) ) . 'px'
				. ';--sgs-fs:' . absint( self::v( $box, $d, 'button_default_font_size', 14 ) ) . 'px'
				. ';--sgs-min:' . absint( self::v( $box, $d, 'button_default_min_width', 44 ) ) . 'px'
				. ';--sgs-hover-bg:' . $c( self::v( $box, $h, 'button_hover_background', '#f7fafc' ), '#f7fafc' )
				. ';--sgs-hover-border:' . $c( self::v( $box, $h, 'button_hover_border_color', '#667eea' ), '#667eea' )
				. ';--sgs-active-bg:' . $c( self::v( $box, $a, 'button_active_background', '#667eea' ), '#667eea' )
				. ';--sgs-active-text:' . $c( self::v( $box, $a, 'button_active_text_color', '#ffffff' ), '#ffffff' )
				. ';--sgs-active:' . $c( self::v( $box, $a, 'button_active_border_color', '#667eea' ), '#667eea' ) . '}';
		}
		$s      = 'swatch_style';
		$shape  = sanitize_key( (string) self::v( $box, $s, 'shape', 'circle' ) );
		$radius = array(
			'circle'  => '50%',
			'rounded' => '8px',
			'square'  => '2px',
			'pill'    => '999px',
		);
		return $sel . '{--sgs-size:' . absint( self::v( $box, $s, 'size', 32 ) ) . 'px'
			. ';--sgs-gap:' . absint( self::v( $box, $s, 'gap', 8 ) ) . 'px'
			. ';--sgs-radius:' . ( $radius[ $shape ] ?? '50%' )
			. ';--sgs-bg:' . $c( self::v( $box, $s, 'background', '#ffffff' ), '#ffffff' )
			. ';--sgs-text:' . $c( self::v( $box, $s, 'text_color', '#1f2937' ), '#1f2937' )
			. ';--sgs-border:' . $c( self::v( $box, $s, 'border_color', '#d1d5db' ), '#d1d5db' )
			. ';--sgs-active:' . $c( self::v( $box, $s, 'active_color', '' ), 'var(--shopglut-accent, #7c3aed)' ) . '}';
	}

	/**
	 * Register assets.
	 *
	 * @return void
	 */
	public function register() {
		wp_register_style( 'shopglut-swatches', $this->url . 'frontend/assets/swatches.css', array(), SHOPGLUT_VERSION );
		wp_register_script( 'shopglut-swatches', $this->url . 'frontend/assets/swatches.js', array( 'jquery', 'wc-add-to-cart-variation' ), SHOPGLUT_VERSION, true );
	}

	/**
	 * Print the styles of the swatch styles used on the page.
	 *
	 * @return void
	 */
	public function print_css() {
		if ( $this->css ) {
			echo '<style id="shopglut-swatches-css">' . wp_strip_all_tags( implode( '', $this->css ) ) . '</style>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from sanitized values.
		}
	}
}
