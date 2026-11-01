<?php
/**
 * Base class of a ShopGlut module.
 *
 * A module is one feature of the builder (product page, cart page, wishlist …). It describes
 * itself (info), may offer a Settings panel (settings_sections) and, when it is a builder, a list
 * of templates and the options of each one (templates, layout_sections). boot() runs only while
 * the module is switched on.
 *
 * Settings panels and layout options are AppGlut sections: array( 'id', 'title', 'icon', 'fields' ).
 *
 * @package ShopGlut
 */

defined( 'ABSPATH' ) || exit;

/**
 * ShopGlut_Module class.
 */
abstract class ShopGlut_Module {

	/**
	 * Module directory (with trailing slash), set by the registry.
	 *
	 * @var string
	 */
	public $dir = '';

	/**
	 * Module URL (with trailing slash), set by the registry.
	 *
	 * @var string
	 */
	public $url = '';

	/**
	 * Module ID, e.g. product-page.
	 *
	 * @return string
	 */
	abstract public function id();

	/**
	 * Description of the module.
	 *
	 * @return array {
	 *     @type string $title       Name.
	 *     @type string $description One line.
	 *     @type string $icon        Font Awesome icon name for the admin (faShirt …).
	 *     @type string $category    builders | product | cart | engagement.
	 *     @type int    $order       Position in lists (lower first).
	 *     @type bool   $layouts     Builder module: has layouts made from templates.
	 *     @type bool   $pro         Needs ShopGlut Pro.
	 *     @type string $status     stable | beta | coming_soon.
	 *     @type array  $features    Short feature list shown on the Modules page.
	 *     @type array  $pro_features What ShopGlut Pro adds to this module.
	 *     @type string $layout_label Singular name of a layout (Layout, Badge, Filter …).
	 *     @type string $layouts_label Plural name of layouts (Layouts, Badges, Filters …).
	 * }
	 */
	abstract public function info();

	/**
	 * Hooks of the module. Runs on every request while the module is on.
	 *
	 * @return void
	 */
	public function boot() {}

	/**
	 * Templates a new layout can start from.
	 *
	 * @return array id => array( title, image, pro, description )
	 */
	public function templates() {
		return array();
	}

	/**
	 * Options of the module's Settings panel. Empty: the module has no Settings page.
	 *
	 * @return array AppGlut sections.
	 */
	public function settings_sections() {
		return array();
	}

	/**
	 * Options of a layout in the layout editor.
	 *
	 * @param string $template Template ID.
	 * @param array  $layout   Layout row.
	 * @return array AppGlut sections.
	 */
	public function layout_sections( $template, $layout ) {
		return array();
	}

	/**
	 * "Where to show" options of a layout, saved under settings['display'].
	 *
	 * @return array AppGlut fields.
	 */
	public function display_fields() {
		return array();
	}

	/**
	 * Whether a layout applies to a request context (product_id, term_id, taxonomy, page …).
	 * Default: every context. Modules with display rules override this.
	 *
	 * @param array $layout  Layout row with decoded settings.
	 * @param array $context Context.
	 * @return bool
	 */
	public function layout_matches( $layout, $context ) {
		return true;
	}

	/**
	 * Settings file of a template: settings/{template}.php in the module, or the file an add-on
	 * (ShopGlut Pro) supplies for its own templates.
	 *
	 * @param string $template Template ID.
	 * @return string Path ('' when there is none).
	 */
	public function template_settings_file( $template ) {
		$file = $this->dir . 'settings/' . sanitize_file_name( $template ) . '.php';
		/**
		 * Settings file of a module template.
		 *
		 * @param string          $file     Path.
		 * @param string          $template Template ID.
		 * @param ShopGlut_Module $module   Module.
		 */
		$file = (string) apply_filters( 'shopglut_template_settings_file', is_readable( $file ) ? $file : '', $template, $this );
		return is_readable( $file ) ? $file : '';
	}

	/**
	 * Sections declared by a settings file (old-style schema), cached per request.
	 *
	 * @param string $file Path.
	 * @param string $mode layout (one fieldset per metabox) | options (sections as they are).
	 * @return array AppGlut sections.
	 */
	protected function schema( $file, $mode = 'layout' ) {
		static $cache = array();
		if ( '' === $file ) {
			return array();
		}
		$key = $mode . '|' . $file;
		if ( ! isset( $cache[ $key ] ) ) {
			$cache[ $key ] = 'options' === $mode ? ShopGlut_Schema::capture_sections( $file ) : ShopGlut_Schema::capture( $file );
		}
		return $cache[ $key ];
	}

	/**
	 * Default layout options: the template's own settings file.
	 *
	 * @param string $template Template ID.
	 * @param array  $layout   Layout row.
	 * @return array AppGlut sections.
	 */
	protected function template_sections( $template, $layout ) {
		return $this->schema( $this->template_settings_file( $template ) );
	}

	/**
	 * Numbered templates: template1 … templateN, the ones above $free marked Pro (teasers only:
	 * their code ships with ShopGlut Pro).
	 *
	 * @param int    $count Number of templates.
	 * @param int    $free  How many are free.
	 * @param string $image Image URL pattern with %s for the template ID ('' for none).
	 * @return array
	 */
	protected function numbered_templates( $count, $free, $image = '' ) {
		$out = array();
		for ( $i = 1; $i <= $count; $i++ ) {
			$id         = 'template' . $i;
			$out[ $id ] = array(
				/* translators: %d: template number */
				'title' => sprintf( __( 'Template %d', 'shopglut' ), $i ),
				'image' => $image ? sprintf( $image, $id ) : '',
				'pro'   => $i > $free,
			);
		}
		return $out;
	}

	/**
	 * Info merged with defaults.
	 *
	 * @return array
	 */
	public function get_info() {
		return wp_parse_args(
			$this->info(),
			array(
				'title'        => $this->id(),
				'description'  => '',
				'icon'         => 'faPuzzlePiece',
				'category'     => 'builders',
				'layouts'      => false,
				'pro'          => false,
				'status'       => 'stable',
				'features'     => array(),
				'pro_features' => array(),
				'layout_label' => __( 'Layout', 'shopglut' ),
				'layouts_label' => __( 'Layouts', 'shopglut' ),
				'default_on'   => true,
				'order'        => 500,
			)
		);
	}

	/**
	 * Templates with defaults, and the Pro ones marked locked when Pro is not active.
	 *
	 * @return array
	 */
	public function get_templates() {
		/**
		 * Templates of a module. ShopGlut Pro adds its templates here.
		 *
		 * @param array           $templates id => array( title, image, pro, description ).
		 * @param ShopGlut_Module $module    Module.
		 */
		$templates = apply_filters( 'shopglut_module_templates', $this->templates(), $this );
		$templates = apply_filters( 'shopglut_' . $this->id() . '_templates', $templates, $this );

		$out = array();
		foreach ( $templates as $id => $tpl ) {
			$tpl          = wp_parse_args( $tpl, array( 'title' => $id, 'image' => '', 'pro' => false, 'description' => '' ) );
			$tpl['id']    = (string) $id;
			$tpl['locked'] = $tpl['pro'] && ! shopglut_is_pro();
			$out[ $id ]   = $tpl;
		}
		return $out;
	}
}
