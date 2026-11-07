<?php
/**
 * ShopGlut Abilities (WordPress Abilities API + MCP): AI assistants connected through the WordPress MCP
 * adapter can list ShopGlut modules, turn them on or off, and search products. Store managers only.
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Module_Abilities class.
 */
class ShopGlut_Module_Abilities extends ShopGlut_Module {

	public function id() {
		return 'abilities';
	}

	public function info() {
		return array(
			'title'       => __( 'AI Abilities (MCP)', 'shopglut' ),
			'description' => __( 'Lets AI assistants (Claude, ChatGPT…) list and switch ShopGlut modules and search products, through WordPress abilities and MCP.', 'shopglut' ),
			'icon'        => 'faPuzzlePiece',
			'category'    => 'builders',
			'order'       => 900,
			'default_on'  => false,
			'features'    => array(
				__( 'shopglut/list-modules, shopglut/set-module, shopglut/search-products', 'shopglut' ),
				__( 'Store managers only', 'shopglut' ),
			),
		);
	}

	public function boot() {
		add_action( 'wp_abilities_api_categories_init', array( $this, 'category' ) );
		add_action( 'wp_abilities_api_init', array( $this, 'register' ) );
	}

	public function category() {
		if ( function_exists( 'wp_register_ability_category' ) ) {
			wp_register_ability_category( 'shopglut', array( 'label' => __( 'ShopGlut store front', 'shopglut' ), 'description' => __( 'ShopGlut modules and products.', 'shopglut' ) ) );
		}
	}

	private function add( $name, $label, $desc, $props, $required, $run, $readonly, $cap = 'manage_woocommerce' ) {
		wp_register_ability(
			'shopglut/' . $name,
			array(
				'label'               => $label,
				'description'         => $desc,
				'category'            => 'shopglut',
				'input_schema'        => array( 'type' => 'object', 'properties' => $props, 'required' => $required, 'additionalProperties' => false ),
				'output_schema'       => array( 'type' => 'object' ),
				'execute_callback'    => function ( $input = array() ) use ( $run ) {
					return call_user_func( $run, (array) $input );
				},
				'permission_callback' => function () use ( $cap ) {
					return current_user_can( $cap );
				},
				'meta'                => array( 'show_in_rest' => true, 'public' => true, 'annotations' => array( 'readonly' => $readonly, 'destructive' => false, 'idempotent' => true ) ),
			)
		);
	}

	public function register() {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}
		$mods = ShopGlut_Modules::instance();
		$this->add( 'list-modules', __( 'List ShopGlut modules', 'shopglut' ), 'All ShopGlut modules with id, title, description, category and whether each is on.', array(), array(), function () use ( $mods ) {
			$out = array();
			foreach ( $mods->all() as $id => $m ) {
				$info  = $m->get_info();
				$out[] = array( 'id' => $id, 'title' => $info['title'], 'description' => $info['description'], 'category' => $info['category'], 'on' => $mods->is_enabled( $id ) );
			}
			return array( 'modules' => $out );
		}, true );
		$this->add( 'set-module', __( 'Turn a ShopGlut module on or off', 'shopglut' ), 'Switch one ShopGlut module on or off by id (see shopglut/list-modules).', array( 'id' => array( 'type' => 'string' ), 'on' => array( 'type' => 'boolean' ) ), array( 'id', 'on' ), function ( $in ) use ( $mods ) {
			if ( ! $mods->get( $in['id'] ) ) {
				return new WP_Error( 'not_found', 'Unknown module.' );
			}
			if ( 'abilities' === $in['id'] && ! $in['on'] ) {
				return new WP_Error( 'refused', 'Turn this module off in wp-admin.' );
			}
			$mods->set_enabled( sanitize_key( $in['id'] ), (bool) $in['on'] );
			return array( 'id' => $in['id'], 'on' => $mods->is_enabled( $in['id'] ) );
		}, false, 'manage_options' );
		$this->add( 'search-products', __( 'Search products', 'shopglut' ), 'Find products by text and optional price range; returns id, name, price, stock, url.', array( 'q' => array( 'type' => 'string' ), 'min_price' => array( 'type' => 'number' ), 'max_price' => array( 'type' => 'number' ), 'limit' => array( 'type' => 'integer', 'minimum' => 1, 'maximum' => 50 ) ), array( 'q' ), function ( $in ) {
			$args = array( 'status' => 'publish', 'limit' => isset( $in['limit'] ) ? (int) $in['limit'] : 20, 's' => sanitize_text_field( $in['q'] ) );
			$out  = array();
			foreach ( wc_get_products( $args ) as $p ) {
				$price = (float) $p->get_price();
				if ( ( isset( $in['min_price'] ) && $price < $in['min_price'] ) || ( isset( $in['max_price'] ) && $price > $in['max_price'] ) ) {
					continue;
				}
				$out[] = array( 'id' => $p->get_id(), 'name' => $p->get_name(), 'price' => $price, 'currency' => get_woocommerce_currency(), 'in_stock' => $p->is_in_stock(), 'stock' => $p->get_stock_quantity(), 'url' => $p->get_permalink() );
			}
			return array( 'products' => $out );
		}, true );
	}
}

return 'ShopGlut_Module_Abilities';
