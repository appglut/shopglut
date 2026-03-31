# ProductQuickView-Glut Standalone Plugin Design

**Date:** 2026-03-31
**Status:** Draft
**Pattern:** Based on ProductPageGlut/CartPageGlut standalone plugin pattern

## Overview

Create a standalone `productquickview-glut` WordPress plugin that:
1. Works independently as a complete Quick View module for WooCommerce
2. Integrates seamlessly with ShopGlut when both plugins are active
3. Follows the established pattern of ProductPageGlut and CartPageGlut

## Current State

The Quick View module currently exists embedded within ShopGlut at:
- `shopglut/src/enhancements/ProductQuickView/`

It is currently commented out in `ShopGlutBase.php` (lines 140-141):
```php
// QuickViewchooseTemplates::get_instance();
// QuickViewDataManage::get_instance();
```

## Target State

### Standalone Plugin Structure

```
/wp-content/plugins/productquickview-glut/
├── productquickview-glut.php              # Main plugin file
├── autoloader.php                          # PSR-4 autoloader
├── readme.txt                              # Plugin readme
├── src/
│   ├── ProductquickviewglutBase.php       # Main base class
│   ├── ProductquickviewglutDatabase.php   # Database table management
│   ├── ProductquickviewglutRegisterMenu.php
│   ├── ProductquickviewglutRegisterScripts.php
│   ├── enhancements/
│   │   └── ProductQuickView/
│   │       ├── QuickViewchooseTemplates.php
│   │       ├── QuickViewDataManage.php
│   │       ├── QuickViewEntity.php
│   │       ├── QuickViewListTable.php
│   │       ├── QuickViewSettingsPage.php
│   │       ├── assets.php
│   │       ├── template-settings.php
│   │       ├── assets/
│   │       │   ├── admin-script.js
│   │       │   ├── admin-style.css
│   │       │   ├── quickview-frontend.js
│   │       │   └── js/
│   │       │       └── product_quickview-data-converter.js
│   │       └── templates/
│   │           └── template1/
│   │               ├── template1Markup.php
│   │               ├── template1-settings.php
│   │               └── template1Style.php
│   ├── library/
│   │   └── model/                           # Shared library classes
│   └── WelcomePage.php                      # Welcome page after activation
└── global-assets/                           # Global assets (optional)
```

## Key Components

### 1. Main Plugin File (`productquickview-glut.php`)

**Purpose:** Plugin header, constants definition, initialization hooks

```php
<?php
/*
 * Plugin Name: ProductQuickView Glut - Quick View for WooCommerce
 * Description: Beautiful quick view modal for WooCommerce products with customizable templates
 * Version: 1.0.0
 * Author: AppGlut
 * Author URI: https://www.appglut.com
 * Plugin URI: https://www.appglut.com
 * License: GPLv2 or later
 * Text Domain: productquickviewglut
 * Domain Path: /languages
 * Requires Plugins: woocommerce
 */

defined( 'ABSPATH' ) or die;

define( 'PRODUCTQUICKVIEWGLUT_NAME', 'Productquickviewglut' );
define( 'PRODUCTQUICKVIEWGLUT_VERSION', '1.0.0' );
define( 'PRODUCTQUICKVIEWGLUT_BASENAME', plugin_basename( __FILE__ ) );
define( 'PRODUCTQUICKVIEWGLUT_PATH', plugin_dir_path( __FILE__ ) );
define( 'PRODUCTQUICKVIEWGLUT_URL', plugin_dir_url( __FILE__ ) );
define( 'PRODUCTQUICKVIEWGLUT_ADMIN_IMAGES', plugin_dir_url( __FILE__ ) . 'src/library/model/assets/images/' );
define( 'PRODUCTQUICKVIEWGLUT_DIRNAME', dirname( plugin_basename( __FILE__ ) ) );
define( 'PRODUCTQUICKVIEWGLUT_SLUG', dirname( plugin_basename( __FILE__ ) ) );

// Pro upgrade URLs
define( 'PRODUCTQUICKVIEWGLUT_PRICING_URL', 'https://www.appglut.com' );
define( 'PRODUCTQUICKVIEWGLUT_PRO_URL', 'https://www.appglut.com' );
define( 'PRODUCTQUICKVIEWGLUT_UPGRADE_URL', 'https://www.appglut.com' );

// Autoloader
require __DIR__ . '/autoloader.php';

// Welcome page
if ( is_admin() ) {
    require_once PRODUCTQUICKVIEWGLUT_PATH . 'src/WelcomePage.php';
}

// Hook into WooCommerce initialization
add_action( 'woocommerce_init', 'productquickviewglut_plugin_initialize' );

function productquickviewglut_plugin_initialize() {
    if ( class_exists( 'WooCommerce' ) ) {
        Productquickviewglut\ProductquickviewglutBase::get_instance();
    }
}

// Activation/Deactivation hooks
register_activation_hook( __FILE__, 'productquickviewglut_activate' );
register_deactivation_hook( __FILE__, 'productquickviewglut_deactivate' );

function productquickviewglut_activate() {
    \Productquickviewglut\ProductquickviewglutDatabase::force_create_core_tables();
    set_transient( 'productquickviewglut_activation_redirect', true, 30 );
    update_option( 'productquickviewglut_first_activation', get_option( 'productquickviewglut_first_activation', 0 ) + 1 );
}

function productquickviewglut_deactivate() {
    // Cleanup if needed
}

// Welcome page menu
add_action( 'admin_menu', 'productquickviewglut_add_welcome_menu', 99 );

function productquickviewglut_add_welcome_menu() {
    add_submenu_page(
        null,
        esc_html__( 'Welcome', 'productquickviewglut' ),
        esc_html__( 'Welcome', 'productquickviewglut' ),
        'manage_options',
        'productquickviewglut-welcome',
        'productquickviewglut_render_welcome_page'
    );
}

function productquickviewglut_render_welcome_page() {
    $welcome_page = new \Productquickviewglut\WelcomePage();
    $welcome_page->render_welcome_content();
}

// Redirect after activation
add_action( 'admin_init', 'productquickviewglut_redirect_after_activation' );

function productquickviewglut_redirect_after_activation() {
    if ( get_transient( 'productquickviewglut_activation_redirect' ) ) {
        delete_transient( 'productquickviewglut_activation_redirect' );
        if ( isset( $_GET['activate-multi'] ) ) {
            return;
        }
        wp_safe_redirect( admin_url( 'admin.php?page=productquickviewglut-welcome' ) );
        exit;
    }
}
```

### 2. Autoloader (`autoloader.php`)

```php
<?php
/**
 * PSR-4 Autoloader for ProductQuickViewGlut
 */

spl_autoload_register(function ($class) {
    // Project-specific namespace prefix
    $prefix = 'Productquickviewglut\\';

    // Base directory for the namespace prefix
    $base_dir = __DIR__ . '/src/';

    // Does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Get the relative class name
    $relative_class = substr($class, $len);

    // Replace namespace separators with directory separators
    // and append with .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // If the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});
```

### 3. Base Class (`ProductquickviewglutBase.php`)

```php
<?php
namespace Productquickviewglut;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Productquickviewglut\enhancements\ProductQuickView\QuickViewchooseTemplates;
use Productquickviewglut\enhancements\ProductQuickView\QuickViewDataManage;

class ProductquickviewglutBase {

    public $menu_slug;

    public function __construct() {
        // Initialize core components
        ProductquickviewglutDatabase::Productquickviewglut_initialize();
        ProductquickviewglutDatabase::force_create_core_tables();
        ProductquickviewglutRegisterScripts::get_instance();
        ProductquickviewglutRegisterMenu::get_instance();

        // Initialize Quick View module
        QuickViewDataManage::get_instance();
        QuickViewchooseTemplates::get_instance();

        // Add actions
        add_action( 'init', array( $this, 'productquickviewglutInitialFunctions' ), 9 );
        add_filter( 'update_footer', array( $this, 'productquickviewglut_admin_footer_version' ), 999 );
        add_action( 'admin_init', array( $this, 'productquickviewglut_redirect_after_activation' ) );
    }

    public function productquickviewglut_redirect_after_activation() {
        if ( ! get_option( 'productquickviewglut_plugin_first_activation_redirect' ) ) {
            update_option( 'productquickviewglut_plugin_first_activation_redirect', true );
            wp_safe_redirect( admin_url( 'admin.php?page=productquickviewglut-welcome' ) );
            exit;
        }
    }

    public function productquickviewglutInitialFunctions() {
        // Load setup class from ShopGlut if active, otherwise from productquickview-glut
        if ( defined( 'SHOPGLUT_VERSION' ) ) {
            require_once SHOPGLUT_PATH . 'src/library/model/classes/setup.class.php';
        } else {
            require_once PRODUCTQUICKVIEWGLUT_PATH . 'src/library/model/classes/setup.class.php';
        }

        // Load Quick View settings
        require_once PRODUCTQUICKVIEWGLUT_PATH . 'src/enhancements/ProductQuickView/template-settings.php';
    }

    public function productquickviewglut_admin_footer_version() {
        return '<span id="productquickviewglut-footer-version" style="display: none;">ProductQuickViewGlut ' . PRODUCTQUICKVIEWGLUT_VERSION . '</span>';
    }

    public static function get_instance() {
        static $instance;
        if ( is_null( $instance ) ) {
            $instance = new self();
        }
        return $instance;
    }
}
```

### 4. ShopGlut Integration

#### Add to `ShopGlutBase.php`:

```php
// Add near other similar checks (after is_shopfilterglut_active())
/**
 * Check if ProductQuickViewGlut plugin is active
 *
 * @return bool True if ProductQuickViewGlut is active
 */
private function is_productquickviewglut_active() {
    $active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) );

    if ( is_multisite() ) {
        $network_active_plugins = get_site_option( 'active_sitewide_plugins', array() );
        $active_plugins = array_merge( $active_plugins, array_keys( $network_active_plugins ) );
    }

    foreach ( $active_plugins as $plugin ) {
        if ( $plugin === 'productquickview-glut/productquickview-glut.php' ) {
            return true;
        }
    }

    return class_exists( 'Productquickviewglut\\ProductquickviewglutBase' );
}

// In __construct(), uncomment and modify QuickView loading:
// Only load embedded QuickView module if ProductQuickViewGlut is NOT active
if ( ! $this->is_productquickviewglut_active() ) {
    QuickViewchooseTemplates::get_instance();
    QuickViewDataManage::get_instance();
}

// In shopglutInitialFunctions(), add conditional loading:
// Only load embedded QuickView settings if ProductQuickViewGlut is NOT active
if ( ! $this->is_productquickviewglut_active() ) {
    require_once SHOPGLUT_PATH . 'src/enhancements/ProductQuickView/template-settings.php';
}
```

#### Modify `AllEnhancements.php`:

```php
// Add conditional check for QuickView editor
public function loadProductQuickviewEditor() {
    // Only load if standalone plugin is NOT active
    if (class_exists('Productquickviewglut\\ProductquickviewglutBase')) {
        return; // Standalone plugin handles its own editor
    }

    // Load embedded QuickView editor
    $quickview_editor = new \Shopglut\enhancements\ProductQuickView\QuickViewSettingsPage();
    $quickview_editor->loadQuickViewEditor();
}
```

## Database Tables

The standalone plugin will create the same database table structure as ShopGlut's embedded QuickView module:

- Table prefix: `wp_productquickview_layouts` (or with blog prefix in multisite)

## Namespace Changes

When copying from ShopGlut, update namespaces:

| From | To |
|------|-----|
| `Shopglut\enhancements\ProductQuickView` | `Productquickviewglut\enhancements\ProductQuickView` |
| `SHOPGLUT_PATH` | `PRODUCTQUICKVIEWGLUT_PATH` |
| `SHOPGLUT_URL` | `PRODUCTQUICKVIEWGLUT_URL` |
| `SHOPGLUT_VERSION` | `PRODUCTQUICKVIEWGLUT_VERSION` |

## Text Domain

- Use `productquickviewglut` for standalone plugin
- Keep `shopglut` references when integrated with ShopGlut

## Implementation Checklist

1. [ ] Create standalone plugin directory structure
2. [ ] Create main plugin file with headers
3. [ ] Create autoloader.php
4. [ ] Create ProductquickviewglutBase.php
5. [ ] Create ProductquickviewglutDatabase.php
6. [ ] Create ProductquickviewglutRegisterMenu.php
7. [ ] Create ProductquickviewglutRegisterScripts.php
8. [ ] Copy QuickView module files with namespace updates
9. [ ] Create WelcomePage.php
10. [ ] Create readme.txt
11. [ ] Update ShopGlutBase.php with integration check
12. [ ] Update AllEnhancements.php with conditional editor loading
13. [ ] Test standalone functionality
14. [ ] Test ShopGlut integration (both active)
15. [ ] Test ShopGlut embedded mode (standalone inactive)

## Testing Scenarios

1. **Standalone Only:** Only productquickview-glut active - Quick View should work
2. **ShopGlut Only:** Only shopglut active - Embedded Quick View should work
3. **Both Active:** Both plugins active - Standalone Quick View takes precedence, ShopGlut embedded is skipped
4. **Multisite:** Test in multisite environment with network activation
5. **Deactivation:** Deactivating standalone should allow ShopGlut embedded to work

## Dependencies

- WordPress 5.0+
- WooCommerce 5.0+
- PHP 7.4+

## Compatibility

- Works independently as a standalone plugin
- Integrates with ShopGlut when both are active
- Compatible with ProductPageGlut, CartPageGlut, and other standalone glut plugins
