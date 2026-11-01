<?php
/**
 * Plugin Name: AppGlut Framework
 * Plugin URI:  https://appglut.com/framework
 * Description: An advanced, modern options framework for WordPress themes and plugins — admin panels, customizer, metaboxes, taxonomy, profile, comment, menu item and widget options, a shortcode generator and 51 field types.
 * Version:     1.0.1
 * Author:      AppGlut
 * Author URI:  https://appglut.com
 * Requires at least: 5.6
 * Requires PHP: 7.2
 * Text Domain: appglut-framework
 * Domain Path: /languages
 * License:     GPL-2.0-or-later
 *
 * The framework can be activated as a plugin or bundled inside a theme/plugin:
 *   require_once get_template_directory() . '/appglut-framework/appglut-framework.php';
 *
 * @package AppGlut_Framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// First loaded copy wins; bundled copies in other themes/plugins step aside.
if ( ! class_exists( 'AppGlut' ) ) {

	define( 'APPGLUT_VERSION', '1.0.1' );
	define( 'APPGLUT_FILE', __FILE__ );
	define( 'APPGLUT_DIR', __DIR__ );

	require_once APPGLUT_DIR . '/includes/functions.php';
	require_once APPGLUT_DIR . '/includes/class-appglut.php';
	require_once APPGLUT_DIR . '/includes/class-field.php';
	require_once APPGLUT_DIR . '/includes/class-container.php';
	require_once APPGLUT_DIR . '/includes/class-options.php';
	require_once APPGLUT_DIR . '/includes/class-metabox.php';
	require_once APPGLUT_DIR . '/includes/class-taxonomy.php';
	require_once APPGLUT_DIR . '/includes/class-profile.php';
	require_once APPGLUT_DIR . '/includes/class-widget.php';
	require_once APPGLUT_DIR . '/includes/class-customizer.php';
	require_once APPGLUT_DIR . '/includes/class-comment.php';
	require_once APPGLUT_DIR . '/includes/class-nav-menu.php';
	require_once APPGLUT_DIR . '/includes/class-shortcoder.php';
	require_once APPGLUT_DIR . '/includes/class-output.php';
	require_once APPGLUT_DIR . '/includes/class-ajax.php';

	AppGlut::init();

	// Demo panels: define( 'APPGLUT_DEMO', true ); in wp-config.php to load them.
	if ( defined( 'APPGLUT_DEMO' ) && APPGLUT_DEMO ) {
		require_once APPGLUT_DIR . '/samples/demo-options.php';
		require_once APPGLUT_DIR . '/samples/demo-metabox.php';
		require_once APPGLUT_DIR . '/samples/demo-others.php';
		require_once APPGLUT_DIR . '/samples/demo-extras.php';
	}
}
