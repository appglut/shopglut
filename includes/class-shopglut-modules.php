<?php
/**
 * Module registry: loads modules/<id>/module.php, keeps the on/off state and boots enabled modules.
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Modules class.
 */
class ShopGlut_Modules {

	/** Option that stores the on/off state: array( id => bool ). */
	const OPTION = 'shopglut_modules';

	/**
	 * Single instance.
	 *
	 * @var ShopGlut_Modules|null
	 */
	private static $instance = null;

	/**
	 * Registered modules.
	 *
	 * @var ShopGlut_Module[]
	 */
	private $modules = array();

	/**
	 * Get singleton instance.
	 *
	 * @return ShopGlut_Modules
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Load the built-in modules, let add-ons register theirs, then boot the enabled ones.
	 *
	 * @return void
	 */
	public function load() {
		foreach ( (array) glob( SHOPGLUT_PATH . 'modules/*/module.php' ) as $file ) {
			$class = require_once $file;
			if ( is_string( $class ) && class_exists( $class ) ) {
				$this->register( new $class(), dirname( $file ) );
			}
		}

		/**
		 * Register more modules: $modules->register( new My_Module(), __DIR__ ).
		 *
		 * @param ShopGlut_Modules $modules Registry.
		 */
		do_action( 'shopglut_register_modules', $this );

		uasort(
			$this->modules,
			static function ( $a, $b ) {
				return $a->get_info()['order'] <=> $b->get_info()['order'];
			}
		);

		foreach ( $this->modules as $id => $module ) {
			if ( $this->is_enabled( $id ) ) {
				$module->boot();
				/**
				 * A module has booted.
				 *
				 * @param ShopGlut_Module $module Module.
				 */
				do_action( 'shopglut_module_booted_' . $id, $module );
			}
		}
	}

	/**
	 * Register a module.
	 *
	 * @param ShopGlut_Module $module Module.
	 * @param string          $dir    Module directory.
	 * @return void
	 */
	public function register( ShopGlut_Module $module, $dir = '' ) {
		if ( $dir ) {
			$module->dir = trailingslashit( wp_normalize_path( $dir ) );
			$module->url = trailingslashit( plugins_url( '', trailingslashit( $dir ) . 'module.php' ) );
		}
		$this->modules[ $module->id() ] = $module;
	}

	/**
	 * A module by ID.
	 *
	 * @param string $id Module ID.
	 * @return ShopGlut_Module|null
	 */
	public function get( $id ) {
		return isset( $this->modules[ $id ] ) ? $this->modules[ $id ] : null;
	}

	/**
	 * All modules.
	 *
	 * @return ShopGlut_Module[]
	 */
	public function all() {
		return $this->modules;
	}

	/**
	 * Saved on/off state.
	 *
	 * @return array
	 */
	private function states() {
		$states = get_option( self::OPTION, array() );
		return is_array( $states ) ? $states : array();
	}

	/**
	 * Whether a module can be switched on (Pro modules need ShopGlut Pro, coming-soon ones cannot).
	 *
	 * @param string $id Module ID.
	 * @return bool
	 */
	public function is_available( $id ) {
		$module = $this->get( $id );
		if ( ! $module ) {
			return false;
		}
		$info = $module->get_info();
		return 'coming_soon' !== $info['status'] && ( ! $info['pro'] || shopglut_is_pro() );
	}

	/**
	 * Whether a module is on.
	 *
	 * @param string $id Module ID.
	 * @return bool
	 */
	public function is_enabled( $id ) {
		if ( ! $this->is_available( $id ) ) {
			return false;
		}
		$states = $this->states();
		$on     = array_key_exists( $id, $states ) ? (bool) $states[ $id ] : (bool) $this->get( $id )->get_info()['default_on'];

		/**
		 * Whether a module is on.
		 *
		 * @param bool   $on Module is on.
		 * @param string $id Module ID.
		 */
		return (bool) apply_filters( 'shopglut_module_enabled', $on, $id );
	}

	/**
	 * Switch a module on or off.
	 *
	 * @param string $id Module ID.
	 * @param bool   $on New state.
	 * @return bool False when the module cannot be switched on.
	 */
	public function set_enabled( $id, $on ) {
		if ( $on && ! $this->is_available( $id ) ) {
			return false;
		}
		$states        = $this->states();
		$states[ $id ] = (bool) $on;
		update_option( self::OPTION, $states );
		do_action( 'shopglut_module_toggled', $id, (bool) $on );
		return true;
	}

	/**
	 * Module data for the admin app.
	 *
	 * @param ShopGlut_Module $module Module.
	 * @return array
	 */
	public function to_array( ShopGlut_Module $module ) {
		$id   = $module->id();
		$info = $module->get_info();

		return array(
			'id'           => $id,
			'title'        => $info['title'],
			'description'  => $info['description'],
			'icon'         => $info['icon'],
			'category'     => $info['category'],
			'layouts'      => (bool) $info['layouts'],
			'layout_label' => $info['layout_label'],
			'layouts_label' => $info['layouts_label'],
			'pro'          => (bool) $info['pro'],
			'status'       => $info['status'],
			'features'     => array_values( (array) $info['features'] ),
			'pro_features' => array_values( (array) $info['pro_features'] ),
			'available'    => $this->is_available( $id ),
			'enabled'      => $this->is_enabled( $id ),
			'has_settings' => ! empty( $module->settings_sections() ),
			'layout_count' => $info['layouts'] ? ShopGlut_Layouts::count( $id ) : 0,
			'active_count' => $info['layouts'] ? ShopGlut_Layouts::count( $id, 'active' ) : 0,
		);
	}
}
