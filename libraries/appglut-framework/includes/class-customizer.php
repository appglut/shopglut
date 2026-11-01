<?php
/**
 * Customizer container: framework fields inside Appearance → Customize.
 *
 *   AppGlut::createCustomizeOptions( 'my_theme', array( 'database' => 'theme_mod' ) );
 *   AppGlut::createSection( 'my_theme', array( 'title' => 'Header', 'fields' => array( ... ) ) );
 *
 * Every field becomes its own setting "{unique}[{id}]", so values are stored
 * in one array exactly like an options panel. A top-level section with
 * sub-sections becomes a panel. Fields with an 'output' rule preview live
 * (no page reload): only the generated stylesheet is re-rendered.
 *
 * @package AppGlut_Framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AppGlut_Customizer extends AppGlut_Container {

	protected function default_args() {
		return array(
			'database'        => 'option',  // option | theme_mod.
			'capability'      => 'edit_theme_options',
			'transport'       => 'refresh', // Default transport for fields without an 'output' rule.
			'priority'        => 160,       // Priority of the first section/panel.
			'panel'           => '',        // Wrap every section in one panel: a title string or array( 'title', 'description', 'priority' ).
			'output_css'      => true,
			'enqueue_webfont' => true,
			'live_css'        => true,      // Live preview for fields with 'output'.
			'theme'           => 'light',
			'accent'          => '',
			'class'           => '',
		);
	}

	protected function setup() {
		add_action( 'customize_register', array( $this, 'register' ) );
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'customize_save_after', array( $this, 'saved' ) );
	}

	/* ---------------------------------------------------------------------
	 * Storage
	 * ------------------------------------------------------------------- */

	/** Saved values merged with defaults (preview-aware: the customizer filters these reads). */
	public function get_values() {
		$saved = 'theme_mod' === $this->arg( 'database' ) ? get_theme_mod( $this->unique, array() ) : get_option( $this->unique, array() );
		return wp_parse_args( is_array( $saved ) ? $saved : array(), $this->defaults() );
	}

	public function setting_id( $field_id ) {
		return $this->unique . '[' . $field_id . ']';
	}

	public function saved( $manager ) {
		do_action( 'appglut_' . $this->unique . '_saved', $this->get_values(), $this, $manager );
	}

	public function enqueue() {
		AppGlut::enqueue( $this->fields() );
		wp_enqueue_script( 'appglut-customize', AppGlut::$url . 'assets/js/appglut-customize.js', array( 'appglut', 'customize-controls' ), AppGlut::$version, true );
	}

	/* ---------------------------------------------------------------------
	 * Registration
	 * ------------------------------------------------------------------- */

	public function register( $manager ) {
		require_once AppGlut::$dir . '/includes/customize-controls.php';
		// Not registered with register_control_type(): these controls are rendered in PHP,
		// and a registered type would be redrawn from its (empty) JS template.

		$priority = (int) $this->arg( 'priority' );
		$wrapper  = $this->wrapper_panel( $manager, $priority );
		$live     = array();

		foreach ( $this->section_tree() as $section ) {
			$priority += 5;

			if ( ! empty( $section['children'] ) && ! $wrapper ) {
				$panel_id = $this->unique . '_' . $section['id'];
				$manager->add_panel(
					$panel_id,
					array(
						'title'       => $section['title'],
						'description' => $section['description'],
						'priority'    => isset( $section['priority'] ) ? (int) $section['priority'] : $priority,
						'capability'  => $this->arg( 'capability' ),
					)
				);

				// A parent that has fields of its own gets a first section inside its panel.
				if ( ! empty( $section['fields'] ) ) {
					$this->add_section( $manager, $section, $panel_id, 1, $live );
				}
				foreach ( $section['children'] as $i => $child ) {
					$this->add_section( $manager, $child, $panel_id, 10 + $i, $live );
				}
				continue;
			}

			$this->add_section( $manager, $section, $wrapper, $priority, $live );
			foreach ( $section['children'] as $i => $child ) {
				$this->add_section( $manager, $child, $wrapper, $priority + 1 + $i, $live );
			}
		}

		// Live CSS: re-render only the generated stylesheet when an 'output' field changes.
		if ( $live && isset( $manager->selective_refresh ) ) {
			$manager->selective_refresh->add_partial(
				'appglut_css_' . $this->unique,
				array(
					'selector'            => '#appglut-output-' . $this->unique,
					'settings'            => $live,
					'container_inclusive' => false,
					'fallback_refresh'    => false,
					'render_callback'     => array( $this, 'render_live_css' ),
				)
			);
		}
	}

	/** Optional single panel that wraps every section. */
	private function wrapper_panel( $manager, $priority ) {
		$panel = $this->arg( 'panel' );
		if ( empty( $panel ) ) {
			return '';
		}
		$panel = is_array( $panel ) ? $panel : array( 'title' => (string) $panel );
		$id    = $this->unique . '_panel';

		$manager->add_panel(
			$id,
			array(
				'title'       => isset( $panel['title'] ) ? $panel['title'] : '',
				'description' => isset( $panel['description'] ) ? $panel['description'] : '',
				'priority'    => isset( $panel['priority'] ) ? (int) $panel['priority'] : $priority,
				'capability'  => $this->arg( 'capability' ),
			)
		);
		return $id;
	}

	private function add_section( $manager, $section, $panel_id, $priority, &$live ) {
		if ( empty( $section['fields'] ) ) {
			return;
		}

		// 'assign' => 'title_tagline' puts the fields into an existing section.
		if ( ! empty( $section['assign'] ) ) {
			$section_id = $section['assign'];
		} else {
			$section_id = $this->unique . '_' . $section['id'];
			$manager->add_section(
				$section_id,
				array(
					'title'       => $section['title'],
					'description' => $section['description'],
					'priority'    => isset( $section['priority'] ) ? (int) $section['priority'] : $priority,
					'panel'       => $panel_id,
					'capability'  => $this->arg( 'capability' ),
				)
			);
		}

		foreach ( (array) $section['fields'] as $index => $field ) {
			if ( ! is_array( $field ) || empty( $field['type'] ) ) {
				continue;
			}
			$this->add_field( $manager, $field, $section_id, $index, $live );
		}
	}

	private function add_field( $manager, $field, $section_id, $index, &$live ) {
		$stores = ! empty( $field['id'] ) && ! in_array( $field['type'], AppGlut::$no_value, true );
		$control_args = array(
			'section'   => $section_id,
			'priority'  => isset( $field['priority'] ) ? (int) $field['priority'] : 10 + $index,
			'agf_container' => $this,
			'field'     => $field,
		);

		if ( ! $stores ) {
			$control_id = $section_id . '_ui_' . $index;
			$manager->add_control( new AppGlut_Customize_Control( $manager, $control_id, array_merge( $control_args, array( 'settings' => array() ) ) ) );
			return;
		}

		$setting_id = $this->setting_id( $field['id'] );
		$has_output = $this->arg( 'output_css' ) && $this->arg( 'live_css' ) && ! empty( $field['output'] );
		$partial    = ! empty( $field['selective_refresh'] ) && isset( $manager->selective_refresh );

		if ( isset( $field['transport'] ) ) {
			$transport = $field['transport'];
		} elseif ( $has_output || $partial ) {
			$transport = 'postMessage';
		} else {
			$transport = $this->arg( 'transport' );
		}

		if ( $has_output && 'postMessage' === $transport && ! $partial ) {
			$live[] = $setting_id;
		}

		$manager->add_setting(
			$setting_id,
			wp_parse_args(
				isset( $field['setting_args'] ) ? (array) $field['setting_args'] : array(),
				array(
					'type'              => 'theme_mod' === $this->arg( 'database' ) ? 'theme_mod' : 'option',
					'default'           => array_key_exists( 'default', $field ) ? $field['default'] : '',
					'capability'        => $this->arg( 'capability' ),
					'transport'         => $transport,
					'sanitize_callback' => function ( $value ) use ( $field ) {
						return AppGlut_Customizer::sanitize_value( $field, $value );
					},
					'validate_callback' => function ( $validity, $value ) use ( $field ) {
						return AppGlut_Customizer::validate_value( $field, $validity, $value );
					},
				)
			)
		);

		$manager->add_control(
			new AppGlut_Customize_Control(
				$manager,
				$setting_id,
				wp_parse_args(
					isset( $field['control_args'] ) ? (array) $field['control_args'] : array(),
					array_merge( $control_args, array( 'settings' => $setting_id ) )
				)
			)
		);

		if ( $partial ) {
			$manager->selective_refresh->add_partial( $setting_id, wp_parse_args( (array) $field['selective_refresh'], array( 'settings' => array( $setting_id ) ) ) );
		}
	}

	/** @internal Sanitize one setting with the field's own sanitizer. */
	public static function sanitize_value( $field, $value ) {
		// The control posts the same shape as a form; booleans/numbers may arrive from JS.
		if ( is_bool( $value ) ) {
			$value = $value ? '1' : '';
		}
		$clean = AppGlut::sanitize_fields( array( $field ), array( $field['id'] => $value ) );
		return array_key_exists( $field['id'], $clean ) ? $clean[ $field['id'] ] : null;
	}

	/** @internal Run the field's 'validate' callback; errors show under the control. */
	public static function validate_value( $field, $validity, $value ) {
		if ( ! empty( $field['validate'] ) && is_callable( $field['validate'] ) ) {
			$error = call_user_func( $field['validate'], is_string( $value ) ? trim( $value ) : $value, $field );
			if ( ! empty( $error ) ) {
				$validity->add( 'appglut_invalid', wp_strip_all_tags( $error ) );
			}
		}
		return $validity;
	}

	/** Selective-refresh callback for the live stylesheet. */
	public function render_live_css() {
		return AppGlut_Output::container_css( $this, true );
	}
}
