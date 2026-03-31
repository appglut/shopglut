# ProductSwatches-Glut Standalone Plugin Design

## Overview
Separate the Product Swatches module from ShopGlut into a standalone plugin that can work independently or as part of ShopGlut, following the ProductPage-Glut pattern.

**Status**: Design Approved
**Date**: 2026-03-31
**Plugin Slug**: `productswatches-glut`
**Namespace**: `Productswatchesglut`

## Requirements

1. **Standalone Operation**: Plugin must work without ShopGlut installed
2. **ShopGlut Integration**: When ShopGlut is active, seamlessly integrate
3. **Full Override**: When standalone is active, disable ShopGlut's embedded Product Swatches
4. **Replace Legacy**: Replace the existing Swatches plugin
5. **All Templates Included**: Templates 1-30 move to standalone plugin

## Architecture

### Directory Structure
```
productswatches-glut/
├── productswatches-glut.php          # Main plugin file
├── autoloader.php                     # PSR-4 autoloader
├── productswatches-glut-version.json # Version tracking
├── readme.txt                         # Plugin readme
├── global-assets/                     # Shared CSS/JS assets
├── src/
│   ├── ProductswatchesglutBase.php   # Main plugin class
│   ├── ShopGlutDatabase.php          # Duplicated from ShopGlut
│   ├── ShopGlutRegisterMenu.php      # Duplicated from ShopGlut
│   ├── ShopGlutRegisterScripts.php   # Duplicated from ShopGlut
│   ├── WelcomePage.php               # Welcome page after activation
│   ├── ProductswatchesglutIndividualMenu.php # Individual menu management
│   ├── shortcodeglut-integration-settings.php # Integration settings
│   ├── enhancements/
│   │   └── ProductSwatches/
│   │       ├── chooseTemplates.php
│   │       ├── dataManage.php
│   │       ├── assets.php
│   │       ├── SettingsPage.php
│   │       ├── productSwatches-settings.php
│   │       ├── swatchesLayout-settings.php
│   │       ├── TemplateFrontend.php
│   │       ├── AttributeSwatchesManager.php
│   │       ├── ProductSwatchesEntity.php
│   │       ├── ProductSwatchesListTable.php
│   │       ├── FrontendRenderer.php
│   │       └── templates/
│   │           ├── template1/ through template30/
│   │           │   ├── template*-settings.php
│   │           │   ├── template*Markup.php
│   │           │   ├── template*Style.php
│   │           │   ├── ModuleIntegration.php
│   │           │   └── template*-ajax-handler.php (where applicable)
│   └── library/
│       └── model/
│           ├── assets/               # Shared assets
│           └── classes/
│               └── setup.class.php
```

## Plugin Initialization

### Main Plugin File (`productswatches-glut.php`)
```php
// Define constants
define('PRODUCTSWATCHESGLUT_NAME', 'Productswatchesglut');
define('PRODUCTSWATCHESGLUT_VERSION', '1.0.0');
define('PRODUCTSWATCHESGLUT_BASENAME', plugin_basename(__FILE__));
define('PRODUCTSWATCHESGLUT_PATH', plugin_dir_path(__FILE__));
define('PRODUCTSWATCHESGLUT_URL', plugin_dir_url(__FILE__));

// Load autoloader
require __DIR__ . '/autoloader.php';

// Load WelcomePage
if (is_admin()) {
    require_once PRODUCTSWATCHESGLUT_PATH . 'src/WelcomePage.php';
}

// Hook into WooCommerce
add_action('woocommerce_init', 'productswatchesglut_plugin_initialize');

function productswatchesglut_plugin_initialize() {
    if (class_exists('WooCommerce')) {
        Productswatchesglut\ProductswatchesglutBase::get_instance();
    }
}
```

### Base Class Initialization (`ProductswatchesglutBase.php`)
```php
public function __construct() {
    // Initialize core components
    ShopGlutDatabase::Productswatchesglut_initialize();
    ShopGlutDatabase::force_create_core_tables();
    ShopGlutRegisterScripts::get_instance();
    ShopGlutRegisterMenu::get_instance();

    // Load ProductSwatches components
    chooseTemplates::get_instance();
    dataManage::get_instance();
    AttributeSwatchesManager::get_instance();

    // Add actions
    add_action('init', array($this, 'productswatchesglutInitialFunctions'), 9);
    add_filter('update_footer', array($this, 'productswatchesglut_admin_footer_version'), 999);
    add_action('admin_init', array($this, 'productswatchesglut_redirect_after_activation'));
}
```

## ShopGlut Integration

### Detection in ShopGlut (`ShopGlutBase.php`)

Add detection method:
```php
private function is_productswatchesglut_active() {
    $active_plugins = apply_filters('active_plugins', get_option('active_plugins', array()));

    if (is_multisite()) {
        $network_active_plugins = get_site_option('active_sitewide_plugins', array());
        $active_plugins = array_merge($active_plugins, array_keys($network_active_plugins));
    }

    foreach ($active_plugins as $plugin) {
        if ($plugin === 'productswatches-glut/productswatches-glut.php') {
            return true;
        }
    }

    return class_exists('Productswatchesglut\\ProductswatchesglutBase');
}
```

Conditional loading in `ShopGlutBase::__construct()`:
```php
// Only load embedded ProductSwatches module if ProductSwatchesGlut is NOT active
if (!$this->is_productswatchesglut_active()) {
    SwatchesTemplates::get_instance();
    ProductSwatchesDataManage::get_instance();
    AttributeSwatchesManager::get_instance();
}
```

Conditional settings loading in `shopglutInitialFunctions()`:
```php
// Only load embedded ProductSwatches settings if ProductSwatchesGlut is NOT active
if (!$this->is_productswatchesglut_active()) {
    require_once SHOPGLUT_PATH . 'src/enhancements/ProductSwatches/productSwatches-settings.php';
}
```

## Database and Settings

### Tables
- Use same structure as ShopGlut's Product Swatches
- Table prefix: `productswatchesglut_` for standalone
- Fallback to `shopglut_` when ShopGlut active (optional enhancement)

### Options
- Main settings: `productswatchesglut_swatches_options`
- Template settings: `productswatchesglut_swatches_layout_{template}`
- Per-attribute settings: `productswatchesglut_attribute_{id}_template`

## Frontend Rendering

### Hooks
- `woocommerce_dropdown_variation_attribute_options_html` - Replace dropdowns with swatches
- `wp_enqueue_scripts` - Load template CSS/JS
- `wp_ajax_*` / `wp_ajax_nopriv_*` - AJAX handlers

### Template Loading
Through `TemplateFrontend.php` and individual template `*Markup.php` classes.

## Admin Interface

### Menu Structure
```
Product Swatches Glut
├── Templates (list all templates with preview)
├── Settings (global settings)
├── Welcome (first activation only)
└── Documentation
```

### Template Editor
- Settings form from `template*-settings.php`
- Live preview (if available)
- Save/reset functionality

## Migration from Legacy Swatches

### Migration Path
1. Detect legacy Swatches plugin activation
2. Offer to migrate settings
3. Copy settings to new format
4. Deactivate legacy plugin

### Settings Migration
```php
// Legacy: agshopglut_swatches_options
// New: productswatchesglut_swatches_options
$legacy_settings = get_option('agshopglut_swatches_options');
if ($legacy_settings) {
    update_option('productswatchesglut_swatches_options', $legacy_settings);
}
```

## Autoloader

Standard PSR-4 autoloader following ProductPage-Glut pattern:
```php
$prefix = 'Productswatchesglut\\';
$base_dir = __DIR__ . '/src/';
```

## Shared Classes

### Classes to Duplicate
1. `ShopGlutDatabase` - Initialize and manage database
2. `ShopGlutRegisterMenu` - Register admin menus
3. `ShopGlutRegisterScripts` - Register CSS/JS

### Modifications Needed
- Update namespace to `Productswatchesglut`
- Replace constants (e.g., `SHOPGLUT_PATH` → `PRODUCTSWATCHESGLUT_PATH`)
- Update method names where appropriate (e.g., `Shopglut_` → `Productswatchesglut_`)

## Testing Checklist

- [ ] Plugin activates without ShopGlut
- [ ] Plugin activates with ShopGlut
- [ ] Templates render correctly on frontend
- [ ] Settings save and load
- [ ] AJAX handlers work
- [ ] ShopGlut's embedded Product Swatches disabled when standalone active
- [ ] Welcome page shows on first activation
- [ ] Migration from legacy Swatches works
- [ ] All 30 templates functional

## Implementation Steps

1. Create plugin directory structure
2. Copy and modify main plugin file
3. Create autoloader
4. Copy and modify ProductSwatches module
5. Copy and modify shared classes
6. Create ShopGlut integration (detection)
7. Update ShopGlut to detect and conditionally load
8. Test standalone operation
9. Test ShopGlut integration
10. Create migration script from legacy Swatches
11. Test all templates
12. Create readme.txt
13. Version tracking setup
