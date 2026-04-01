# ProductCustomField-Glut Standalone Plugin Design

**Date:** 2026-03-31
**Status:** Design
**Author:** Claude (with user guidance)

## Overview

Create a standalone WooCommerce Product Custom Field plugin following the ProductPage-Glut pattern. The plugin will work independently while seamlessly integrating with ShopGlut when both are active.

## Goals

1. Enable Product Custom Field module to work as a standalone plugin
2. Maintain full compatibility with ShopGlut when both plugins are active
3. Share database tables and resources when ShopGlut is available
4. Follow the established ProductPage-Glut/ProductSwatches-Glut architectural pattern

## Architecture

### Plugin Structure

```
productcustomfield-glut/
├── productcustomfield-glut.php          # Main plugin file
├── autoloader.php                       # PSR-4 autoloader
├── productcustomfield-glut-version.json # Version tracking
├── readme.txt                           # Plugin readme
├── global-assets/                       # Shared CSS/JS assets
│   └── images/
│       └── loading-icon.png
├── src/
│   ├── ProductcustomfieldglutBase.php   # Main plugin class
│   ├── ProductcustomfieldglutDatabase.php      # Database wrapper
│   ├── ProductcustomfieldglutRegisterScripts.php
│   ├── ProductcustomfieldglutRegisterMenu.php
│   ├── WelcomePage.php                 # Welcome page after activation
│   ├── ProductcustomfieldglutIndividualMenu.php # Individual menu management
│   ├── shortcodeglut-integration-settings.php # Integration settings
│   ├── tools/
│   │   └── productCustomField/
│   │       ├── ProductCustomFieldDataManage.php
│   │       ├── ProductCustomFieldHandler.php
│   │       ├── ProductCustomFieldListTable.php
│   │       ├── ProductCustomFieldSettingsPage.php
│   │       ├── ProductCustomFieldEntity.php
│   │       ├── product-custom-field-settings.php
│   │       ├── assets.php
│   │       ├── assets/
│   │       │   ├── js/
│   │       │   │   └── product-custom-field-data-converter.js
│   │       │   └── style.css
│   │       └── templates/
│   │           └── template1/
│   │               ├── template1-settings.php
│   │               ├── template1Markup.php
│   │               └── template1Style.php
│   └── library/
│       └── model/
│           ├── assets/                  # Shared assets
│           └── classes/
│               └── setup.class.php
```

## Core Components

### 1. Main Plugin File (`productcustomfield-glut.php`)

```php
<?php
/**
 * Plugin Name: ProductCustomField Glut
 * Plugin URI: https://glut.com
 * Description: Add custom fields to WooCommerce product pages with beautiful designs
 * Version: 1.0.0
 * Author: Glut
 * Text Domain: productcustomfieldglut
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define constants
define('PRODUCTCUSTOMFIELDGLUT_NAME', 'Productcustomfieldglut');
define('PRODUCTCUSTOMFIELDGLUT_VERSION', '1.0.0');
define('PRODUCTCUSTOMFIELDGLUT_BASENAME', plugin_basename(__FILE__));
define('PRODUCTCUSTOMFIELDGLUT_PATH', plugin_dir_path(__FILE__));
define('PRODUCTCUSTOMFIELDGLUT_URL', plugin_dir_url(__FILE__));

// Load autoloader
require_once PRODUCTCUSTOMFIELDGLUT_PATH . 'autoloader.php';

// Load WelcomePage
if (is_admin()) {
    require_once PRODUCTCUSTOMFIELDGLUT_PATH . 'src/WelcomePage.php';
}

// Hook into WooCommerce
add_action('woocommerce_init', 'productcustomfieldglut_plugin_initialize');

function productcustomfieldglut_plugin_initialize() {
    if (class_exists('WooCommerce')) {
        Productcustomfieldglut\ProductcustomfieldglutBase::get_instance();
    }
}
```

### 2. Autoloader (`autoloader.php`)

```php
<?php
if (!defined('ABSPATH')) {
    exit;
}

spl_autoload_register(function ($class) {
    $prefix = 'Productcustomfieldglut\\';
    $base_dir = PRODUCTCUSTOMFIELDGLUT_PATH . 'src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});
```

### 3. Base Class (`ProductcustomfieldglutBase.php`)

Namespace: `Productcustomfieldglut\ProductcustomfieldglutBase`

```php
<?php
namespace Productcustomfieldglut;

if (!defined('ABSPATH')) {
    exit;
}

class ProductcustomfieldglutBase {

    public function __construct() {
        // Initialize core components
        ProductcustomfieldglutDatabase::Productcustomfieldglut_initialize();
        ProductcustomfieldglutDatabase::force_create_core_tables();
        ProductcustomfieldglutRegisterScripts::get_instance();
        ProductcustomfieldglutRegisterMenu::get_instance();

        // Load ProductCustomField components
        use Productcustomfieldglut\tools\productCustomField\ProductCustomFieldDataManage;
        use Productcustomfieldglut\tools\productCustomField\ProductCustomFieldHandler;

        ProductCustomFieldDataManage::get_instance();
        ProductCustomFieldHandler::get_instance();

        // Add actions
        add_action('init', array($this, 'productcustomfieldglutInitialFunctions'), 9);
        add_filter('update_footer', array($this, 'productcustomfieldglut_admin_footer_version'), 999);
        add_action('admin_init', array($this, 'productcustomfieldglut_redirect_after_activation'));
    }

    public function productcustomfieldglutInitialFunctions() {
        // Use ShopGlut's setup class if available, otherwise local copy
        if (defined('SHOPGLUT_VERSION')) {
            require_once SHOPGLUT_PATH . 'src/library/model/classes/setup.class.php';
        } else {
            require_once PRODUCTCUSTOMFIELDGLUT_PATH . 'src/library/model/classes/setup.class.php';
        }
        require_once PRODUCTCUSTOMFIELDGLUT_PATH . 'src/tools/productCustomField/product-custom-field-settings.php';
    }

    public function productcustomfieldglut_redirect_after_activation() {
        if (!get_option('productcustomfieldglut_plugin_first_activation_redirect')) {
            update_option('productcustomfieldglut_plugin_first_activation_redirect', true);
            wp_safe_redirect(admin_url('admin.php?page=productcustomfieldglut-welcome'));
            exit;
        }
    }

    public function productcustomfieldglut_admin_footer_version() {
        return '<span id="productcustomfieldglut-footer-version" style="display: none;">ProductCustomField Glut ' . PRODUCTCUSTOMFIELDGLUT_VERSION . '</span>';
    }

    public static function get_instance() {
        static $instance;
        if (is_null($instance)) {
            $instance = new self();
        }
        return $instance;
    }
}
```

### 4. Database Class (`ProductcustomfieldglutDatabase.php`)

Namespace: `Productcustomfieldglut\ProductcustomfieldglutDatabase`

- Wraps ShopGlutDatabase when ShopGlut is active
- Provides fallback methods when standalone
- Uses same table name: `shopglut_product_custom_field_settings`

```php
<?php
namespace Productcustomfieldglut;

if (!defined('ABSPATH')) {
    exit;
}

class ProductcustomfieldglutDatabase {

    public static function Productcustomfieldglut_initialize() {
        // Check if ShopGlut is active and use its database
        if (defined('SHOPGLUT_VERSION') && class_exists('Shopglut\ShopGlutDatabase')) {
            // ShopGlut handles table creation
            return;
        }

        // Create tables in standalone mode
        add_action('init', array(__CLASS__, 'create_custom_field_tables'));
    }

    public static function force_create_core_tables() {
        if (defined('SHOPGLUT_VERSION')) {
            return; // ShopGlut handles this
        }
        self::create_custom_field_tables();
    }

    public static function create_custom_field_tables() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'shopglut_product_custom_field_settings';

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS `{$table_name}` (
            id int(11) NOT NULL AUTO_INCREMENT,
            field_name varchar(255) NOT NULL,
            field_settings longtext DEFAULT NULL,
            created_at datetime DEFAULT NULL,
            updated_at datetime DEFAULT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public static function table_product_custom_field_settings() {
        global $wpdb;
        return $wpdb->prefix . 'shopglut_product_custom_field_settings';
    }
}
```

### 5. Module Classes (from productCustomField)

All adapted with proper namespacing to `Productcustomfieldglut\tools\productCustomField\`:

- `Productcustomfieldglut\tools\productCustomField\ProductCustomFieldDataManage`
- `Productcustomfieldglut\tools\productCustomField\ProductCustomFieldHandler`
- `Productcustomfieldglut\tools\productCustomField\ProductCustomFieldListTable`
- `Productcustomfieldglut\tools\productCustomField\ProductCustomFieldSettingsPage`
- `Productcustomfieldglut\tools\productCustomField\ProductCustomFieldEntity`

## ShopGlut Integration

### Detection Logic

In `ShopGlutBase.php`, add:

```php
/**
 * Check if ProductCustomFieldGlut plugin is active
 *
 * @return bool True if ProductCustomFieldGlut is active
 */
private function is_productcustomfieldglut_active() {
    $active_plugins = apply_filters('active_plugins', get_option('active_plugins', array()));

    if (is_multisite()) {
        $network_active_plugins = get_site_option('active_sitewide_plugins', array());
        $active_plugins = array_merge($active_plugins, array_keys($network_active_plugins));
    }

    foreach ($active_plugins as $plugin) {
        if ($plugin === 'productcustomfield-glut/productcustomfield-glut.php') {
            return true;
        }
    }

    return class_exists('Productcustomfieldglut\\ProductcustomfieldglutBase');
}
```

### Conditional Loading in ShopGlut

In `ShopGlutBase::__construct()`:

```php
// Only load embedded ProductCustomField module if ProductCustomFieldGlut is NOT active
if (!$this->is_productcustomfieldglut_active()) {
    ProductCustomFieldDataManage::get_instance();
    ProductCustomFieldHandler::get_instance();
}
```

In `ShopGlutBase::shopglutInitialFunctions()`:

```php
// Only load embedded ProductCustomField settings if ProductCustomFieldGlut is NOT active
if (!$this->is_productcustomfieldglut_active()) {
    require_once SHOPGLUT_PATH . 'src/tools/productCustomField/product-custom-field-settings.php';
}
```

## Database Schema

Uses existing `shopglut_product_custom_field_settings` table structure:

```sql
CREATE TABLE {prefix}shopglut_product_custom_field_settings (
    id int(11) NOT NULL AUTO_INCREMENT,
    field_name varchar(255) NOT NULL,
    field_settings longtext DEFAULT NULL,
    created_at datetime DEFAULT NULL,
    updated_at datetime DEFAULT NULL,
    PRIMARY KEY (id)
)
```

## Admin Interface

### Menu Structure

**Standalone:**
- ProductCustomField Glut (top-level menu)
  - Custom Fields

**With ShopGlut:**
- ShopGlut (managed by ShopGlut)
  - Tools (includes Custom Fields)

### Pages

1. **Fields List** (`admin.php?page=productcustomfieldglut_fields`)
   - Table of all custom fields
   - Add new field button
   - Edit/delete actions

2. **Field Editor** (`admin.php?page=productcustomfieldglut_fields&field_id=X`)
   - Field name input
   - Custom field settings meta boxes
   - Save/Reset buttons

## Frontend Integration

### Hook Positions

The plugin displays custom fields at various positions on product pages:

- `before_title` / `after_title`
- `before_price` / `after_price`
- `before_description` / `after_description`
- `before_add_to_cart` / `after_add_to_cart`
- `before_meta` / `after_meta`

### Theme Support

- **WooCommerce default**: Uses `woocommerce_single_product_summary` hooks
- **Astra theme**: Uses `astra_woo_single_*` hooks

### Field Types

- **Textarea/Design fields**:
  - Simple text
  - Simple list
  - Bullet points
  - Numbered list
  - Paragraphs
  - Cards
  - Features grid
  - Info boxes
  - Tags
  - Timeline

- **Radio/Button fields**:
  - Basic radio
  - Button group
  - Card selection
  - Toggle switch

## Error Handling

1. **Table existence check**: Verify database table exists before querying
2. **WooCommerce dependency**: Check if WooCommerce is active before initialization
3. **Graceful degradation**: If ShopGlut resources unavailable, use local copies
4. **Nonce verification**: All admin actions require valid nonces

## Testing Checklist

### Standalone Mode
- [ ] Plugin activates without errors
- [ ] Database tables created on activation
- [ ] Admin menu appears correctly
- [ ] Can create/edit/delete custom fields
- [ ] Frontend renders custom fields correctly
- [ ] All design types display properly
- [ ] Position hooks work correctly

### Integrated Mode (with ShopGlut)
- [ ] No duplicate menu items
- [ ] ShopGlut recognizes ProductCustomField-Glut
- [ ] Fields accessible from ShopGlut menu
- [ ] Database operations work correctly
- [ ] No conflicts or duplicate functionality

### Edge Cases
- [ ] Deactivating ProductCustomField-Glut restores ShopGlut embedded module
- [ ] Reactivating ShopGlut doesn't cause errors
- [ ] Multisite installation works correctly
- [ ] Network activation works
- [ ] Different themes (Astra, Storefront, etc.) work correctly

## Implementation Order

1. Create plugin skeleton (main file, autoloader)
2. Create base class with initialization logic
3. Create database wrapper class
4. Create registration classes (menu, scripts)
5. Copy and adapt productCustomField module files
6. Update namespaces and constants
7. Add ShopGlut integration detection
8. Update ShopGlut to detect and conditionally load
9. Test standalone functionality
10. Test integrated functionality

## Dependencies

- WordPress 5.0+
- WooCommerce 5.0+
- PHP 7.4+

## Notes

- Follow ProductPage-Glut naming conventions (namespace: `Productcustomfieldglut\`)
- Use `PRODUCTCUSTOMFIELDGLUT_*` constants for paths and URLs
- Maintain compatibility with existing ShopGlut database structure
- Keep plugin text domain as `productcustomfieldglut` for translations
