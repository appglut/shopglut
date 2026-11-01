<?php
/**
 * Classic widgets built from framework fields.
 *
 *   AppGlut::createWidget( 'my_widget', array(
 *     'title'    => 'My Widget',
 *     'fields'   => array( ... ),
 *     'callback' => function ( $args, $instance ) { ... },
 *   ) );
 *
 * @package AppGlut_Framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AppGlut_Widget_Container extends AppGlut_Container {

	protected function default_args() {
		return array(
			'title'       => '',
			'classname'   => '',
			'description' => '',
			'width'       => '',
			'callback'    => '',
			'fields'      => array(),
			'theme'       => 'light',
			'accent'      => '',
			'class'       => '',
		);
	}

	protected function setup() {
		add_action( 'widgets_init', array( $this, 'register' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	public function register() {
		register_widget( new AppGlut_Widget_Instance( $this ) );
	}

	public function enqueue( $hook ) {
		if ( 'widgets.php' === $hook || 'customize.php' === $hook ) {
			AppGlut::enqueue( $this->fields() );
		}
	}

	/** Render the form inside a widget. */
	public function render_form( $values, $prefix ) {
		echo '<div' . $this->app_attrs( 'widget' ) . '><div class="agf-grid">'; // phpcs:ignore WordPress.Security.EscapeOutput
		AppGlut::fields( $this->fields(), $values, $prefix, 'widget' );
		echo '</div></div>';
	}
}

class AppGlut_Widget_Instance extends WP_Widget {

	/** @var AppGlut_Widget_Container */
	public $container;

	public function __construct( $container ) {
		$this->container = $container;

		parent::__construct(
			$container->unique,
			$container->arg( 'title' ),
			array(
				'classname'                   => $container->arg( 'classname' ) ? $container->arg( 'classname' ) : $container->unique,
				'description'                 => $container->arg( 'description' ),
				'customize_selective_refresh' => true,
			),
			$container->arg( 'width' ) ? array( 'width' => (int) $container->arg( 'width' ) ) : array()
		);
	}

	public function widget( $args, $instance ) {
		$instance = wp_parse_args( (array) $instance, $this->container->defaults() );
		$callback = $this->container->arg( 'callback' );

		if ( is_callable( $callback ) ) {
			call_user_func( $callback, $args, $instance, $this );
		}
	}

	public function form( $instance ) {
		$values = wp_parse_args( (array) $instance, $this->container->defaults() );
		$this->container->render_form( $values, 'widget-' . $this->id_base . '[' . $this->number . ']' );
		return '';
	}

	public function update( $new_instance, $old_instance ) {
		AppGlut::$errors = array();
		return AppGlut::sanitize_fields( $this->container->fields(), (array) $new_instance, (array) $old_instance );
	}
}
