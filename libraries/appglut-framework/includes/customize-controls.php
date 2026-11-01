<?php
/**
 * Customizer control that renders any framework field.
 * Loaded on 'customize_register', when WP_Customize_Control exists.
 *
 * @package AppGlut_Framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'AppGlut_Customize_Control' ) ) {

	class AppGlut_Customize_Control extends WP_Customize_Control {

		public $type = 'appglut';

		/** @var array Field config. */
		public $field = array();

		/** @var AppGlut_Customizer */
		public $agf_container = null;

		public function to_json() {
			parent::to_json();

			$field = $this->field;
			$this->json['unique']  = $this->agf_container ? $this->agf_container->unique : '';
			$this->json['fieldId'] = isset( $field['id'] ) ? $field['id'] : '';

			// Dependencies are evaluated across controls in JS (appglut-customize.js).
			$dep = AppGlut::dependency_rules( $field );
			if ( $dep ) {
				$this->json['dep']    = $dep['rules'];
				$this->json['depRel'] = $dep['relation'];
			}
		}


		protected function render_content() {
			$field = $this->field;
			unset( $field['dependency'] ); // Handled per control, see to_json().

			$value = null;
			if ( ! empty( $field['id'] ) && ! empty( $this->settings ) ) {
				$value = $this->value();
			}

			$unique = $this->agf_container ? $this->agf_container->unique : '';
			$attrs  = $this->agf_container ? $this->agf_container->app_attrs( 'customize' ) : ' class="agf-app agf-mode-customize"';

			echo '<div' . $attrs . '><div class="agf-grid">'; // phpcs:ignore WordPress.Security.EscapeOutput
			AppGlut::field( $field, $value, $unique, 'customize' );
			echo '</div></div>';
		}
	}
}
