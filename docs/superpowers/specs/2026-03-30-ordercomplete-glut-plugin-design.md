# OrderComplete-Glut Plugin Design Document

**Date:** 2026-03-30
**Status:** Approved
**Author:** Claude (with user guidance)

## Overview

Create a standalone WooCommerce Order Complete page builder plugin following the ProductPage-Glut pattern. The plugin will work independently while seamlessly integrating with ShopGlut when both are active.

## Goals

1. Enable OrderComplete module to work as a standalone plugin
2. Maintain full compatibility with ShopGlut when both plugins are active
3. Share database tables and resources when ShopGlut is available
4. Follow the established ProductPage-Glut architectural pattern

## Architecture

### Plugin Structure

```
ordercomplete-glut/
├── ordercomplete-glut.php          # Main plugin file
├── autoloader.php                  # PSR-4 autoloader
├── global-assets/                  # Shared assets (images, CSS, JS)
│   └── images/
├── src/
│   ├── OrdercompleteglutBase.php   # Main plugin class
│   ├── OrdercompleteglutDatabase.php      # Database wrapper
│   ├── OrdercompleteglutRegisterScripts.php
│   ├── OrdercompleteglutRegisterMenu.php
│   ├── layouts/
│   │   ├── AllLayouts.php          # Layout management
│   │   └── orderCompletePage/      # Order Complete module
│   │       ├── dataManage.php
│   │       ├── chooseTemplates.php
│   │       ├── orderCompleteListTable.php
│   │       ├── orderCompletePageEntity.php
│   │       ├── SettingsPage.php
│   │       ├── template-settings.php
│   │       ├── assets.php
│   │       └── templates/
│   │           └── template1/
│   │               ├── template1.php
│   │               ├── template1Markup.php
│   │               ├── template1Style.php
│   │               └── template1-settings.php
│   └── library/
│       └── model/                  # Shared utilities
│           └── classes/
│               └── setup.class.php
```

### Core Components

#### 1. Main Plugin File (`ordercomplete-glut.php`)

- Plugin header with metadata
- Define constants: `ORDERCOMPLETEGLUT_*`
- Include autoloader
- Hook into `woocommerce_init` for initialization
- Register activation hook for database table creation

#### 2. Autoloader (`autoloader.php`)

- PSR-4 compliant autoloader
- Namespace prefix: `Ordercompleteglut\`
- Base directory: `src/`

#### 3. Base Class (`OrdercompleteglutBase.php`)

Namespace: `Ordercompleteglut\OrdercompleteglutBase`

Responsibilities:
- Initialize database (create tables on activation)
- Register scripts and menus
- Initialize AllLayouts
- Load orderCompletePage module (dataManage, chooseTemplates)
- Conditionally load setup.class.php from ShopGlut if available

Key methods:
```php
public function __construct() {
    OrdercompleteglutDatabase::Ordercompleteglut_initialize();
    OrdercompleteglutDatabase::force_create_core_tables();
    OrdercompleteglutRegisterScripts::get_instance();
    OrdercompleteglutRegisterMenu::get_instance();
    AllLayouts::get_instance();

    orderCompletedataManage::get_instance();
    orderCompleteTemplates::get_instance();

    add_action('init', array($this, 'ordercompleteglutInitialFunctions'), 9);
    add_filter('update_footer', array($this, 'ordercompleteglut_admin_footer_version'), 999);
    add_action('admin_init', array($this, 'ordercompleteglut_redirect_after_activation'));
}

public function ordercompleteglutInitialFunctions() {
    // Use ShopGlut's setup class if available, otherwise local copy
    if (defined('SHOPGLUT_VERSION')) {
        require_once SHOPGLUT_PATH . 'src/library/model/classes/setup.class.php';
    } else {
        require_once ORDERCOMPLETEGLUT_PATH . 'src/library/model/classes/setup.class.php';
    }
    require_once ORDERCOMPLETEGLUT_PATH . 'src/layouts/orderCompletePage/template-settings.php';
}
```

#### 4. Database Class (`OrdercompleteglutDatabase.php`)

Namespace: `Ordercompleteglut\OrdercompleteglutDatabase`

- Wraps ShopGlutDatabase when ShopGlut is active
- Provides fallback methods when standalone
- Uses same table name: `shopglut_ordercomplete_layouts`

#### 5. AllLayouts Class (`AllLayouts.php`)

Namespace: `Ordercompleteglut\layouts\AllLayouts`

Responsibilities:
- Render admin pages and menus
- Handle layout CRUD operations
- Display layout templates page
- Manage order complete layout settings

Key methods:
```php
public function renderLayoutsPages() {
    // Route to appropriate page based on query params
    // - ordercomplete_layouts&view=order_complete
    // - ordercomplete_layouts&editor=order_complete&layout_id=X
}

public function settingsPageHeader($active_menu) {
    // Render admin header with logo and navigation
}

public function renderOrderComplete() {
    // Display order complete layouts list table
}
```

#### 6. Module Classes (from orderCompletePage)

All adapted with proper namespacing:

- `Ordercompleteglut\layouts\orderCompletePage\dataManage`
- `Ordercompleteglut\layouts\orderCompletePage\chooseTemplates`
- `Ordercompleteglut\layouts\orderCompletePage\orderCompleteListTable`
- `Ordercompleteglut\layouts\orderCompletePage\orderCompletePageEntity`
- `Ordercompleteglut\layouts\orderCompletePage\SettingsPage`

## ShopGlut Integration

### Detection Logic

In `ShopGlutBase.php`, add:

```php
private function is_ordercompleteglut_active() {
    $active_plugins = apply_filters('active_plugins', get_option('active_plugins', array()));

    if (is_multisite()) {
        $network_active_plugins = get_site_option('active_sitewide_plugins', array());
        $active_plugins = array_merge($active_plugins, array_keys($network_active_plugins));
    }

    foreach ($active_plugins as $plugin) {
        if ($plugin === 'ordercomplete-glut/ordercomplete-glut.php') {
            return true;
        }
    }

    return class_exists('Ordercompleteglut\\OrdercompleteglutBase');
}
```

### Conditional Loading

In `ShopGlutBase::__construct()`:

```php
// Only load embedded orderCompletePage if OrderCompleteGlut is NOT active
if (!$this->is_ordercompleteglut_active()) {
    orderCompletedataManage::get_instance();
    orderCompleteTemplates::get_instance();
}
```

In `ShopGlutBase::shopglutInitialFunctions()`:

```php
// Only load embedded orderComplete settings if OrderCompleteGlut is NOT active
if (!$this->is_ordercompleteglut_active()) {
    require_once SHOPGLUT_PATH . 'src/layouts/orderCompletePage/template-settings.php';
}
```

## Database Schema

Uses existing `shopglut_ordercomplete_layouts` table structure:

```sql
CREATE TABLE {prefix}shopglut_ordercomplete_layouts (
    id int(11) NOT NULL AUTO_INCREMENT,
    layout_name varchar(255) NOT NULL,
    layout_template varchar(100) DEFAULT 'template1',
    layout_settings longtext DEFAULT NULL,
    created_at datetime DEFAULT NULL,
    updated_at datetime DEFAULT NULL,
    PRIMARY KEY (id)
)
```

## Admin Interface

### Menu Structure

**Standalone:**
- OrderComplete Glut (top-level menu)
  - Order Complete Layouts

**With ShopGlut:**
- ShopGlut (managed by ShopGlut)
  - Builder Modules (includes Order Complete)

### Pages

1. **Layouts List** (`admin.php?page=ordercompleteglut_layouts&view=order_complete`)
   - Table of all order complete layouts
   - Create from templates button
   - Edit/delete actions

2. **Layout Editor** (`admin.php?page=ordercompleteglut_layouts&editor=order_complete&layout_id=X`)
   - Layout name input
   - Template settings meta boxes
   - Live preview
   - Save/Reset buttons

3. **Templates Page** (`admin.php?page=ordercompleteglut_layouts&view=order_complete_templates`)
   - Prebuilt template selection
   - Template preview cards

## Frontend Integration

### Override Logic

The plugin overrides WooCommerce thank you page when:
1. A layout has "Override WooCommerce Thank You Page" enabled
2. User is on the thank you page (woocommerce_thankyou hook)
3. Layout settings determine the template

### Hooks Used

- `woocommerce_thankyou` - Render custom thank you content
- `template_redirect` - Override page template
- `template_include` - Custom template inclusion
- `wp_enqueue_scripts` - Enqueue custom styles

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
- [ ] Can create/edit/delete layouts
- [ ] Frontend renders correctly with custom layout
- [ ] Override WooCommerce thank you page works

### Integrated Mode (with ShopGlut)
- [ ] No duplicate menu items
- [ ] ShopGlut recognizes OrderComplete-Glut
- [ ] Layouts accessible from ShopGlut menu
- [ ] Database operations work correctly
- [ ] No conflicts or duplicate functionality

### Edge Cases
- [ ] Deactivating OrderComplete-Glut restores ShopGlut embedded module
- [ ] Reactivating ShopGlut doesn't cause errors
- [ ] Multisite installation works correctly
- [ ] Network activation works

## Implementation Order

1. Create plugin skeleton (main file, autoloader)
2. Create base class with initialization logic
3. Create database wrapper class
4. Create registration classes (menu, scripts)
5. Copy and adapt orderCompletePage module files
6. Create AllLayouts class
7. Add ShopGlut integration detection
8. Test standalone functionality
9. Test integrated functionality

## Dependencies

- WordPress 5.0+
- WooCommerce 5.0+
- PHP 7.4+

## Notes

- Follow ProductPage-Glut naming conventions (namespace: `Ordercompleteglut\`)
- Use `ORDERCOMPLETEGLUT_*` constants for paths and URLs
- Maintain compatibility with existing ShopGlut database structure
- Keep plugin text domain as `ordercompleteglut` for translations
