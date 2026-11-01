<?php
/**
 * Admin options panel.
 *
 * @package AppGlut_Framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AppGlut_Options extends AppGlut_Container {

	public $page_hook = '';

	private $values_cache = null;

	protected function default_args() {
		return array(
			// Menu.
			'menu_title'         => 'AppGlut',
			'menu_slug'          => 'appglut-options',
			'menu_type'          => 'menu',     // menu | submenu.
			'menu_parent'        => '',         // e.g. themes.php, options-general.php.
			'menu_capability'    => 'manage_options',
			'menu_icon'          => 'dashicons-admin-generic',
			'menu_position'      => null,
			'menu_hidden'        => false,
			'admin_bar'          => false,      // Adds a toolbar shortcut.
			'page_title'         => '',

			// Header.
			'framework_title'    => 'AppGlut Framework',
			'framework_subtitle' => '',
			'logo'               => '',         // Image URL; falls back to an icon tile.
			'logo_icon'          => 'dashicons-superhero-alt',
			'version'            => '',
			'header_links'       => array(),    // array( array( 'title' => '', 'url' => '', 'icon' => '' ) ).
			'footer_text'        => '',

			// Appearance.
			'theme'              => 'light',    // light | dark | auto.
			'accent'             => '',         // Any CSS color. Default indigo.
			'class'              => '',
			'show_search'        => true,
			'show_theme_toggle'  => true,
			'show_reset_all'     => true,
			'show_reset_section' => true,
			'show_footer'        => true,
			'sticky_header'      => true,

			// Storage.
			'database'           => 'option',   // option | theme_mod | network.
			'autoload'           => true,
			'save_defaults'      => true,       // Store defaults the first time the page loads.

			// Front end.
			'output_css'         => true,
			'enqueue_webfont'    => true,
		);
	}

	protected function setup() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'network_admin_menu', array( $this, 'add_network_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );

		if ( $this->arg( 'admin_bar' ) ) {
			add_action( 'admin_bar_menu', array( $this, 'admin_bar' ), 80 );
		}
	}

	/* ---------------------------------------------------------------------
	 * Menu
	 * ------------------------------------------------------------------- */

	public function add_menu() {
		if ( 'network' === $this->arg( 'database' ) ) {
			return;
		}
		$this->register_page();
	}

	public function add_network_menu() {
		if ( 'network' === $this->arg( 'database' ) ) {
			$this->register_page();
		}
	}

	private function register_page() {
		$title    = $this->arg( 'page_title' ) ? $this->arg( 'page_title' ) : $this->arg( 'menu_title' );
		$cap      = $this->arg( 'menu_capability' );
		$slug     = $this->arg( 'menu_slug' );
		$callback = array( $this, 'render_page' );

		if ( 'submenu' === $this->arg( 'menu_type' ) && $this->arg( 'menu_parent' ) ) {
			$this->page_hook = add_submenu_page( $this->arg( 'menu_parent' ), $title, $this->arg( 'menu_title' ), $cap, $slug, $callback, $this->arg( 'menu_position' ) );
		} else {
			$this->page_hook = add_menu_page( $title, $this->arg( 'menu_title' ), $cap, $slug, $callback, $this->arg( 'menu_icon' ), $this->arg( 'menu_position' ) );
		}

		if ( $this->arg( 'menu_hidden' ) ) {
			remove_menu_page( $slug );
			if ( $this->arg( 'menu_parent' ) ) {
				remove_submenu_page( $this->arg( 'menu_parent' ), $slug );
			}
		}

		add_action( 'load-' . $this->page_hook, array( $this, 'on_load' ) );
	}

	public function admin_bar( $bar ) {
		if ( ! current_user_can( $this->arg( 'menu_capability' ) ) ) {
			return;
		}
		$bar->add_node(
			array(
				'id'    => 'appglut-' . $this->unique,
				'title' => '<span class="ab-icon dashicons ' . esc_attr( $this->arg( 'menu_icon' ) ) . '" style="margin-top:2px"></span>' . esc_html( $this->arg( 'menu_title' ) ),
				'href'  => $this->page_url(),
			)
		);
	}

	public function page_url() {
		if ( 'submenu' === $this->arg( 'menu_type' ) && $this->arg( 'menu_parent' ) && false !== strpos( $this->arg( 'menu_parent' ), '.php' ) ) {
			return add_query_arg( 'page', $this->arg( 'menu_slug' ), admin_url( $this->arg( 'menu_parent' ) ) );
		}
		return add_query_arg( 'page', $this->arg( 'menu_slug' ), 'network' === $this->arg( 'database' ) ? network_admin_url( 'admin.php' ) : admin_url( 'admin.php' ) );
	}

	/** Runs when our page loads: store defaults on first visit. */
	public function on_load() {
		if ( $this->arg( 'save_defaults' ) && null === $this->read( null ) ) {
			$this->write( AppGlut::sanitize_fields( $this->fields(), $this->defaults() ) );
		}
	}

	public function enqueue( $hook ) {
		if ( $hook && $hook === $this->page_hook ) {
			AppGlut::enqueue( $this->fields() );
		}
	}

	/* ---------------------------------------------------------------------
	 * Storage
	 * ------------------------------------------------------------------- */

	/** Raw stored value or $default when nothing is stored yet. */
	public function read( $default = array() ) {
		switch ( $this->arg( 'database' ) ) {
			case 'theme_mod':
				$value = get_theme_mod( $this->unique, $default );
				break;
			case 'network':
				$value = get_site_option( $this->unique, $default );
				break;
			default:
				$value = get_option( $this->unique, $default );
		}
		return $value;
	}

	public function write( $values ) {
		$this->values_cache = null;
		switch ( $this->arg( 'database' ) ) {
			case 'theme_mod':
				set_theme_mod( $this->unique, $values );
				break;
			case 'network':
				update_site_option( $this->unique, $values );
				break;
			default:
				update_option( $this->unique, $values, (bool) $this->arg( 'autoload' ) );
		}
	}

	/** Saved values merged with defaults. */
	public function get_values() {
		if ( null === $this->values_cache ) {
			$saved              = $this->read( array() );
			$this->values_cache = wp_parse_args( is_array( $saved ) ? $saved : array(), $this->defaults() );
		}
		return $this->values_cache;
	}

	/**
	 * Sanitize and store submitted data.
	 *
	 * @param array $data  Unslashed data keyed by field ID.
	 * @param bool  $merge Keep values for fields missing from $data (import).
	 * @return array Clean values.
	 */
	public function save( $data, $merge = false ) {
		AppGlut::$errors = array();

		$old  = $this->get_values();
		$data = is_array( $data ) ? $data : array();
		if ( $merge ) {
			$data = array_merge( $old, $data );
		}

		$data  = apply_filters( 'appglut_' . $this->unique . '_save', $data, $this );
		$clean = AppGlut::sanitize_fields( $this->fields(), $data, $old );

		do_action( 'appglut_' . $this->unique . '_save_before', $clean, $this );
		$this->write( $clean );
		do_action( 'appglut_' . $this->unique . '_saved', $clean, $this );

		return $clean;
	}

	/** Reset everything, or only the fields of one section. */
	public function reset( $section_id = '' ) {
		$sections = $this->sections();

		if ( $section_id && isset( $sections[ $section_id ] ) ) {
			$values = array_merge( $this->get_values(), AppGlut::defaults( $sections[ $section_id ]['fields'] ) );
		} else {
			$values = $this->defaults();
		}

		$this->write( AppGlut::sanitize_fields( $this->fields(), $values ) );
		do_action( 'appglut_' . $this->unique . '_reset', $section_id, $this );
	}

	/* ---------------------------------------------------------------------
	 * Page
	 * ------------------------------------------------------------------- */

	public function render_page() {
		if ( ! current_user_can( $this->arg( 'menu_capability' ) ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'shopglut' ) );
		}

		$values  = $this->get_values();
		$tree    = $this->section_tree();
		$active  = $this->first_section_id();
		$title   = $this->arg( 'framework_title' );
		$classes = $this->arg( 'sticky_header' ) ? 'agf-sticky' : '';

		echo '<div class="wrap agf-wrap">';
		echo '<h1 class="screen-reader-text">' . esc_html( $title ) . '</h1><hr class="wp-header-end">';

		echo '<div' . $this->app_attrs( // phpcs:ignore WordPress.Security.EscapeOutput
			'options',
			array(
				'data-nonce'  => wp_create_nonce( 'appglut_' . $this->unique ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in app_attrs().
				'data-active' => $active, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in app_attrs().
			)
		) . '>';
		$this->theme_bootstrap();

		echo '<form class="agf-form ' . esc_attr( $classes ) . '" method="post" autocomplete="off" novalidate>';

		$this->render_header( $title );

		$has_nav = count( $this->sections() ) > 1;
		echo '<div class="agf-body' . ( $has_nav ? '' : ' agf-no-nav' ) . '">';
		if ( $has_nav ) {
			echo '<aside class="agf-nav"><div class="agf-nav-inner">';
			$this->render_nav( $tree, $active );
			echo '</div></aside>';
		}

		echo '<main class="agf-main">';
		echo '<div class="agf-search-empty" hidden>' . AppGlut::icon( 'dashicons-search' ) . '<p>' . esc_html__( 'No fields match your search.', 'shopglut' ) . '</p></div>'; // phpcs:ignore WordPress.Security.EscapeOutput
		$this->render_panels( $values, $active );
		echo '</main>';
		echo '</div>';

		if ( $this->arg( 'show_footer' ) ) {
			$this->render_footer();
		}

		echo '</form>';
		echo '<div class="agf-toasts" aria-live="polite"></div>';
		echo '</div></div>';
	}

	/** Applies the stored light/dark preference before paint to avoid a flash. */
	private function theme_bootstrap() {
		?>
		<script>
		(function(r){try{var p=localStorage.getItem('agf-theme-'+r.getAttribute('data-unique'))||r.getAttribute('data-pref');if(p==='auto'){p=window.matchMedia&&window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light';}r.setAttribute('data-theme',p==='dark'?'dark':'light');}catch(e){}})(document.currentScript.parentNode);
		</script>
		<?php
	}

	private function render_header( $title ) {
		echo '<header class="agf-header">';

		echo '<button type="button" class="agf-icon-btn agf-nav-toggle" aria-label="' . esc_attr__( 'Toggle navigation', 'shopglut' ) . '">' . AppGlut::icon( 'dashicons-menu-alt' ) . '</button>'; // phpcs:ignore WordPress.Security.EscapeOutput

		echo '<div class="agf-brand">';
		if ( $this->arg( 'logo' ) ) {
			echo '<img class="agf-logo" src="' . esc_url( $this->arg( 'logo' ) ) . '" alt="">';
		} else {
			echo '<span class="agf-logo agf-logo-tile">' . AppGlut::icon( $this->arg( 'logo_icon' ) ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		echo '<div class="agf-brand-text"><div class="agf-brand-title">' . esc_html( $title );
		if ( $this->arg( 'version' ) ) {
			echo ' <span class="agf-version">' . esc_html( $this->arg( 'version' ) ) . '</span>';
		}
		echo '</div>';
		if ( $this->arg( 'framework_subtitle' ) ) {
			echo '<div class="agf-brand-sub">' . esc_html( $this->arg( 'framework_subtitle' ) ) . '</div>';
		}
		echo '</div></div>';

		if ( $this->arg( 'show_search' ) ) {
			echo '<div class="agf-search">' . AppGlut::icon( 'dashicons-search' ); // phpcs:ignore WordPress.Security.EscapeOutput
			echo '<input type="search" class="agf-search-input" placeholder="' . esc_attr__( 'Search settings…', 'shopglut' ) . '" aria-label="' . esc_attr__( 'Search settings', 'shopglut' ) . '">';
			echo '<kbd class="agf-kbd">/</kbd></div>';
		}

		echo '<div class="agf-header-actions">';
		foreach ( (array) $this->arg( 'header_links', array() ) as $link ) {
			if ( empty( $link['url'] ) ) {
				continue;
			}
			echo '<a class="agf-header-link" href="' . esc_url( $link['url'] ) . '" target="_blank" rel="noopener noreferrer">' . AppGlut::icon( isset( $link['icon'] ) ? $link['icon'] : '' ) . '<span>' . esc_html( isset( $link['title'] ) ? $link['title'] : '' ) . '</span></a>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		if ( $this->arg( 'show_theme_toggle' ) ) {
			echo '<button type="button" class="agf-icon-btn agf-theme-toggle" aria-label="' . esc_attr__( 'Toggle dark mode', 'shopglut' ) . '"><span class="agf-theme-icon" aria-hidden="true"></span></button>';
		}
		echo '<span class="agf-status" data-state="clean"><span class="agf-status-dot"></span><span class="agf-status-text">' . esc_html__( 'Unsaved changes', 'shopglut' ) . '</span></span>';
		echo '<button type="submit" class="agf-btn agf-btn-primary agf-save">' . AppGlut::icon( 'dashicons-saved' ) . '<span>' . esc_html__( 'Save Changes', 'shopglut' ) . '</span></button>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '</div>';

		echo '</header>';
	}

	private function render_footer() {
		echo '<footer class="agf-footer">';
		echo '<div class="agf-footer-text">';
		if ( $this->arg( 'footer_text' ) ) {
			echo wp_kses_post( $this->arg( 'footer_text' ) );
		} else {
			/* translators: %s: framework version */
			printf( esc_html__( 'Powered by AppGlut Framework %s', 'shopglut' ), esc_html( AppGlut::$version ) );
		}
		echo ' <span class="agf-shortcut-hint">' . esc_html__( 'Press Ctrl/⌘ + S to save', 'shopglut' ) . '</span>';
		echo '</div><div class="agf-footer-actions">';
		if ( $this->arg( 'show_reset_section' ) ) {
			echo '<button type="button" class="agf-btn agf-btn-ghost agf-reset-section">' . AppGlut::icon( 'dashicons-image-rotate' ) . '<span>' . esc_html__( 'Reset Section', 'shopglut' ) . '</span></button>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		if ( $this->arg( 'show_reset_all' ) ) {
			echo '<button type="button" class="agf-btn agf-btn-ghost agf-btn-danger agf-reset-all">' . AppGlut::icon( 'dashicons-warning' ) . '<span>' . esc_html__( 'Reset All', 'shopglut' ) . '</span></button>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		echo '<button type="submit" class="agf-btn agf-btn-primary agf-save">' . AppGlut::icon( 'dashicons-saved' ) . '<span>' . esc_html__( 'Save Changes', 'shopglut' ) . '</span></button>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '</div></footer>';
	}
}
