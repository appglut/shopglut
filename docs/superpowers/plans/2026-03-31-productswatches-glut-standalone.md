# ProductSwatches-Glut Standalone Plugin Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Separate the Product Swatches module from ShopGlut into a standalone plugin that works independently and integrates with ShopGlut when both are active.

**Architecture:** Standalone WordPress plugin with duplicated shared classes, conditional loading in ShopGlut, and full template support (1-30).

**Tech Stack:** PHP 7.4+, WordPress 5.8+, WooCommerce 5.0+

---

## File Structure

### New Files to Create
```
/media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/
└── productswatches-glut/
    ├── productswatches-glut.php
    ├── autoloader.php
    ├── productswatches-glut-version.json
    ├── readme.txt
    ├── global-assets/ (empty for now, shared CSS/JS)
    └── src/
        ├── ProductswatchesglutBase.php
        ├── ShopGlutDatabase.php
        ├── ShopGlutRegisterMenu.php
        ├── ShopGlutRegisterScripts.php
        ├── WelcomePage.php
        ├── ProductswatchesglutIndividualMenu.php
        ├── shortcodeglut-integration-settings.php
        ├── library/model/classes/setup.class.php
        └── enhancements/ProductSwatches/
            ├── (all files from ShopGlut src/enhancements/ProductSwatches/)
            └── templates/
                ├── template1/ through template30/
                └── (all template files)
```

### Files to Modify in ShopGlut
- `src/ShopGlutBase.php` - Add `is_productswatchesglut_active()` method and conditional loading

---

## Task 1: Create Plugin Directory and Main Plugin File

**Files:**
- Create: `/productswatches-glut/productswatches-glut.php`

- [ ] **Step 1: Create plugin directory**

```bash
mkdir -p /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/productswatches-glut
```

- [ ] **Step 2: Create main plugin file**

```php
<?php
/*
 * Plugin Name: Product Swatches Glut - Advanced Variation Swatches for WooCommerce
 * Description: Beautiful product variation swatches with 30+ templates including color, image, button, radio, and bicolor swatches for professional WooCommerce stores
 * Version: 1.0.0
 * Author: AppGlut
 * Author URI: https://www.appglut.com
 * Plugin URI: https://www.appglut.com
 * License: GPLv2 or later
 * Text Domain: productswatchesglut
 * Domain Path: /languages
 * Requires Plugins: woocommerce
 */

defined( 'ABSPATH' ) or die;

define( 'PRODUCTSWATCHESGLUT_NAME', 'Productswatchesglut' );
define( 'PRODUCTSWATCHESGLUT_VERSION', '1.0.0' );
define( 'PRODUCTSWATCHESGLUT_BASENAME', plugin_basename( __FILE__ ) );
define( 'PRODUCTSWATCHESGLUT_PATH', plugin_dir_path( __FILE__ ) );
define( 'PRODUCTSWATCHESGLUT_URL', plugin_dir_url( __FILE__ ) );
define( 'PRODUCTSWATCHESGLUT_ADMIN_IMAGES', plugin_dir_url( __FILE__ ) . 'src/library/model/assets/images/' );
define( 'PRODUCTSWATCHESGLUT_DIRNAME', dirname( plugin_basename( __FILE__ ) ) );
define( 'PRODUCTSWATCHESGLUT_SLUG', dirname( plugin_basename( __FILE__ ) ) );

// Pro upgrade URLs
define( 'PRODUCTSWATCHESGLUT_PRICING_URL', 'https://www.appglut.com' );
define( 'PRODUCTSWATCHESGLUT_PRO_URL', 'https://www.appglut.com' );
define( 'PRODUCTSWATCHESGLUT_UPGRADE_URL', 'https://www.appglut.com' );

// Autoloader for class loading
require __DIR__ . '/autoloader.php';

// Load WelcomePage class early (for admin redirect after activation)
if ( is_admin() ) {
    require_once PRODUCTSWATCHESGLUT_PATH . 'src/WelcomePage.php';
}

// Hook into WooCommerce initialization
add_action( 'woocommerce_init', 'productswatchesglut_plugin_initialize' );

function productswatchesglut_plugin_initialize() {
    // Ensure that WooCommerce is loaded before proceeding
    if ( class_exists( 'WooCommerce' ) ) {
        // Run Productswatchesglut initialization
        Productswatchesglut\ProductswatchesglutBase::get_instance();
    }
}

// Add welcome page menu
add_action( 'admin_menu', 'productswatchesglut_add_welcome_menu', 99 );

function productswatchesglut_add_welcome_menu() {
    add_submenu_page(
        null, // Parent slug - null to hide from menu
        esc_html__( 'Welcome', 'productswatchesglut' ),
        esc_html__( 'Welcome', 'productswatchesglut' ),
        'manage_options',
        'productswatchesglut-welcome',
        'productswatchesglut_render_welcome_page'
    );
}

function productswatchesglut_render_welcome_page() {
    $welcome_page = new \Productswatchesglut\WelcomePage();
    $welcome_page->render_welcome_content();
}

// Register activation hook with redirect to welcome page
register_activation_hook( __FILE__, 'productswatchesglut_plugin_activation' );

function productswatchesglut_plugin_activation() {
    // Set transient for redirect
    set_transient( 'productswatchesglut_activation_redirect', true, 30 );

    // Set option for first activation redirect
    update_option( 'productswatchesglut_first_activation', get_option( 'productswatchesglut_first_activation', 0 ) + 1 );
}

// Handle redirect after activation
add_action( 'admin_init', 'productswatchesglut_redirect_after_activation' );

function productswatchesglut_redirect_after_activation() {
    // Check if we need to redirect
    if ( get_transient( 'productswatchesglut_activation_redirect' ) ) {
        delete_transient( 'productswatchesglut_activation_redirect' );

        // Only redirect if it's not a bulk activation
        if ( isset( $_GET['activate-multi'] ) ) {
            return;
        }

        // Redirect to welcome page
        wp_safe_redirect( admin_url( 'admin.php?page=productswatchesglut-welcome' ) );
        exit;
    }
}
```

- [ ] **Step 3: Create global-assets directory**

```bash
mkdir -p /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/productswatches-glut/global-assets
```

- [ ] **Step 4: Commit**

```bash
cd /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/productswatches-glut
git init
git add productswatches-glut.php
git commit -m "feat: create main plugin file for ProductSwatches-Glut"
```

---

## Task 2: Create Autoloader

**Files:**
- Create: `/productswatches-glut/autoloader.php`

- [ ] **Step 1: Create PSR-4 autoloader**

```php
<?php
if ( ! defined( 'ABSPATH' ) ) exit;

spl_autoload_register(function ($class) {

    // project-specific namespace prefix
    $prefix = 'Productswatchesglut\\';

    // base directory for the namespace prefix
    $base_dir = __DIR__ . '/src/';

    // does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }

    // get the relative class name
    $relative_class = substr($class, $len);

    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});
```

- [ ] **Step 2: Commit**

```bash
git add autoloader.php
git commit -m "feat: add PSR-4 autoloader for Productswatchesglut namespace"
```

---

## Task 3: Copy ShopGlutDatabase Class

**Files:**
- Create: `/productswatches-glut/src/ShopGlutDatabase.php`
- Source: `/shopglut/src/ShopGlutDatabase.php`

- [ ] **Step 1: Read ShopGlut's ShopGlutDatabase class**

```bash
cp /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/shopglut/src/ShopGlutDatabase.php \
   /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/productswatches-glut/src/ShopGlutDatabase.php
```

- [ ] **Step 2: Modify namespace and constants**

Open the copied file and make these replacements:

1. Add namespace at the top after `<?php`:
```php
namespace Productswatchesglut;
```

2. Replace `SHOPGLUT_` constants with `PRODUCTSWATCHESGLUT_`:
   - `SHOPGLUT_PATH` → `PRODUCTSWATCHESGLUT_PATH`
   - `SHOPGLUT_VERSION` → `PRODUCTSWATCHESGLUT_VERSION`
   - etc.

3. Find and replace table prefixes in method names:
   - `ShopGlut_` → `Productswatchesglut_`
   - `shopglut_` → `productswatchesglut_`

4. Update class name references if needed

- [ ] **Step 3: Update initialization method**

Add this method to the ShopGlutDatabase class:

```php
public static function Productswatchesglut_initialize() {
    global $productswatchesglut_db_version;
    $productswatchesglut_db_version = PRODUCTSWATCHESGLUT_VERSION;

    // Create database tables
    add_action( 'admin_init', array( __CLASS__, 'productswatchesglut_create_tables' ) );
}
```

- [ ] **Step 4: Commit**

```bash
git add src/ShopGlutDatabase.php
git commit -m "feat: add ShopGlutDatabase class with Productswatchesglut namespace"
```

---

## Task 4: Copy ShopGlutRegisterScripts Class

**Files:**
- Create: `/productswatches-glut/src/ShopGlutRegisterScripts.php`
- Source: `/shopglut/src/ShopGlutRegisterScripts.php`

- [ ] **Step 1: Copy ShopGlutRegisterScripts**

```bash
cp /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/shopglut/src/ShopGlutRegisterScripts.php \
   /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/productswatches-glut/src/ShopGlutRegisterScripts.php
```

- [ ] **Step 2: Modify namespace and constants**

Make these replacements in the copied file:

1. Add namespace:
```php
namespace Productswatchesglut;

use Productswatchesglut\enhancements\ProductSwatches\assets;
```

2. Replace constants:
   - `SHOPGLUT_PATH` → `PRODUCTSWATCHESGLUT_PATH`
   - `SHOPGLUT_URL` → `PRODUCTSWATCHESGLUT_URL`
   - `SHOPGLUT_VERSION` → `PRODUCTSWATCHESGLUT_VERSION`

3. Update class references to use Productswatchesglut namespace

4. Update asset paths to use PRODUCTSWATCHESGLUT_URL

- [ ] **Step 3: Commit**

```bash
git add src/ShopGlutRegisterScripts.php
git commit -m "feat: add ShopGlutRegisterScripts class with Productswatchesglut namespace"
```

---

## Task 5: Copy ShopGlutRegisterMenu Class

**Files:**
- Create: `/productswatches-glut/src/ShopGlutRegisterMenu.php`
- Source: `/shopglut/src/ShopGlutRegisterMenu.php`

- [ ] **Step 1: Copy ShopGlutRegisterMenu**

```bash
cp /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/shopglut/src/ShopGlutRegisterMenu.php \
   /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/productswatches-glut/src/ShopGlutRegisterMenu.php
```

- [ ] **Step 2: Modify namespace and constants**

Make these replacements:

1. Add namespace:
```php
namespace Productswatchesglut;
```

2. Replace constants:
   - `SHOPGLUT_` → `PRODUCTSWATCHESGLUT_`

3. Update menu slugs:
   - `shopglut_` → `productswatchesglut_`

4. Update class references

5. Modify menu title to "Product Swatches Glut"

6. Update capability checks if needed

- [ ] **Step 3: Simplify menu structure**

Update the menu registration to focus on Product Swatches only:

```php
public function register_menu() {
    add_menu_page(
        esc_html__( 'Product Swatches Glut', 'productswatchesglut' ),
        esc_html__( 'Product Swatches', 'productswatchesglut' ),
        'manage_options',
        'productswatchesglut-templates',
        array( $this, 'render_templates_page' ),
        'dashicons-swatch', // or appropriate dashicon
        30
    );
}
```

- [ ] **Step 4: Commit**

```bash
git add src/ShopGlutRegisterMenu.php
git commit -m "feat: add ShopGlutRegisterMenu class with Productswatchesglut namespace"
```

---

## Task 6: Create ProductswatchesglutBase Class

**Files:**
- Create: `/productswatches-glut/src/ProductswatchesglutBase.php`
- Reference: `/productpage-glut/src/ProductpageglutBase.php`

- [ ] **Step 1: Create main base class**

```php
<?php
namespace Productswatchesglut;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Productswatchesglut\enhancements\ProductSwatches\chooseTemplates as SwatchesTemplates;
use Productswatchesglut\enhancements\ProductSwatches\dataManage as SwatchesDataManage;
use Productswatchesglut\enhancements\ProductSwatches\AttributeSwatchesManager;

class ProductswatchesglutBase {

    // Declare properties to fix PHP 8.2+ deprecation warnings
    public $menu_slug;

    public function __construct() {

        // Initialize core components
        ShopGlutDatabase::Productswatchesglut_initialize();
        ShopGlutDatabase::force_create_core_tables();
        ShopGlutRegisterScripts::get_instance();
        ShopGlutRegisterMenu::get_instance();

        // Load Product Swatches components
        SwatchesTemplates::get_instance();
        SwatchesDataManage::get_instance();
        AttributeSwatchesManager::get_instance();

        // Add actions
        add_action( 'init', array( $this, 'productswatchesglutInitialFunctions' ), 9 );
        add_filter( 'update_footer', array( $this, 'productswatchesglut_admin_footer_version' ), 999 );
        add_action( 'admin_init', array( $this, 'productswatchesglut_redirect_after_activation' ) );
    }

    public function productswatchesglut_redirect_after_activation() {
        if ( ! get_option( 'productswatchesglut_plugin_first_activation_redirect' ) ) {
            // Set the option to ensure this runs only once
            update_option( 'productswatchesglut_plugin_first_activation_redirect', true );

            // Redirect to the welcome page after activation
            wp_safe_redirect( admin_url( 'admin.php?page=productswatchesglut-welcome' ) );
            exit;
        }
    }

    public function productswatchesglutInitialFunctions() {
        // Load setup class
        if ( defined( 'SHOPGLUT_VERSION' ) && SHOPGLUT_VERSION ) {
            // Use ShopGlut's setup class if available
            require_once SHOPGLUT_PATH . 'src/library/model/classes/setup.class.php';
        } else {
            // Use standalone setup class
            require_once PRODUCTSWATCHESGLUT_PATH . 'src/library/model/classes/setup.class.php';
        }

        // Load Product Swatches settings
        require_once PRODUCTSWATCHESGLUT_PATH . 'src/enhancements/ProductSwatches/productSwatches-settings.php';
    }

    public function productswatchesglut_admin_footer_version() {
        return '<span id="productswatchesglut-footer-version" style="display: none;">Product Swatches Glut ' . PRODUCTSWATCHESGLUT_VERSION . '</span>';
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

- [ ] **Step 2: Commit**

```bash
git add src/ProductswatchesglutBase.php
git commit -m "feat: add ProductswatchesglutBase main class"
```

---

## Task 7: Create WelcomePage Class

**Files:**
- Create: `/productswatches-glut/src/WelcomePage.php`
- Source: `/productpage-glut/src/WelcomePage.php` (as reference)

- [ ] **Step 1: Copy and modify WelcomePage**

```bash
cp /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/productpage-glut/src/WelcomePage.php \
   /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/productswatches-glut/src/WelcomePage.php
```

- [ ] **Step 2: Modify namespace and constants**

Replace in the copied file:
1. Namespace: `Productpageglut` → `Productswatchesglut`
2. Constants: `PRODUCTPAGEGLUT_` → `PRODUCTSWATCHESGLUT_`
3. Update welcome page content for Product Swatches
4. Update plugin references

- [ ] **Step 3: Commit**

```bash
git add src/WelcomePage.php
git commit -m "feat: add WelcomePage class for first activation"
```

---

## Task 8: Create ProductswatchesglutIndividualMenu Class

**Files:**
- Create: `/productswatches-glut/src/ProductswatchesglutIndividualMenu.php`
- Reference: `/productpage-glut/src/ProductpageglutIndividualMenu.php`

- [ ] **Step 1: Create individual menu class**

```bash
cp /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/productpage-glut/src/ProductpageglutIndividualMenu.php \
   /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/productswatches-glut/src/ProductswatchesglutIndividualMenu.php
```

- [ ] **Step 2: Modify for Product Swatches**

Replace:
1. Namespace: `Productpageglut` → `Productswatchesglut`
2. Constants
3. Menu slugs
4. Update menu items to focus on Product Swatches

- [ ] **Step 3: Commit**

```bash
git add src/ProductswatchesglutIndividualMenu.php
git commit -m "feat: add ProductswatchesglutIndividualMenu class"
```

---

## Task 9: Create Setup Class

**Files:**
- Create: `/productswatches-glut/src/library/model/classes/setup.class.php`

- [ ] **Step 1: Create directory structure**

```bash
mkdir -p /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/productswatches-glut/src/library/model/classes
```

- [ ] **Step 2: Copy setup class from ShopGlut**

```bash
cp /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/shopglut/src/library/model/classes/setup.class.php \
   /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/productswatches-glut/src/library/model/classes/setup.class.php
```

- [ ] **Step 3: Modify constants**

Replace `SHOPGLUT_` with `PRODUCTSWATCHESGLUT_` throughout

- [ ] **Step 4: Commit**

```bash
git add src/library/model/classes/setup.class.php
git commit -m "feat: add setup class"
```

---

## Task 10: Copy Integration Settings File

**Files:**
- Create: `/productswatches-glut/src/shortcodeglut-integration-settings.php`

- [ ] **Step 1: Copy integration settings**

```bash
cp /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/productpage-glut/src/shortcodeglut-integration-settings.php \
   /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/productswatches-glut/src/shortcodeglut-integration-settings.php
```

- [ ] **Step 2: Modify constants and namespace**

Replace:
1. `PRODUCTPAGEGLUT_` → `PRODUCTSWATCHESGLUT_`
2. Update integration messages for Product Swatches

- [ ] **Step 3: Commit**

```bash
git add src/shortcodeglut-integration-settings.php
git commit -m "feat: add shortcodeglut integration settings"
```

---

## Task 11: Copy ProductSwatches Module - Core Files

**Files:**
- Create: `/productswatches-glut/src/enhancements/ProductSwatches/*.php`
- Source: `/shopglut/src/enhancements/ProductSwatches/*.php`

- [ ] **Step 1: Create ProductSwatches directory**

```bash
mkdir -p /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/productswatches-glut/src/enhancements/ProductSwatches
```

- [ ] **Step 2: Copy core module files**

```bash
cd /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/shopglut/src/enhancements/ProductSwatches

# Copy all PHP files except templates directory
cp *.php \
   /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/productswatches-glut/src/enhancements/ProductSwatches/
```

- [ ] **Step 3: Update namespace in all files**

For each copied file, add/update namespace:
```php
namespace Productswatchesglut\enhancements\ProductSwatches;
```

Update `use` statements to reference `Productswatchesglut` namespace.

- [ ] **Step 4: Update constants**

Replace `SHOPGLUT_` with `PRODUCTSWATCHESGLUT_` in all files.

- [ ] **Step 5: Commit**

```bash
cd /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/productswatches-glut
git add src/enhancements/ProductSwatches/*.php
git commit -m "feat: add ProductSwatches core module files"
```

---

## Task 12: Copy ProductSwatches Templates

**Files:**
- Create: `/productswatches-glut/src/enhancements/ProductSwatches/templates/template*/**/*.php`

- [ ] **Step 1: Copy all templates**

```bash
cd /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/shopglut/src/enhancements/ProductSwatches/templates

# Copy entire templates directory
cp -r template* /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/productswatches-glut/src/enhancements/ProductSwatches/templates/
```

- [ ] **Step 2: Update namespaces in all template files**

Create a script to update all PHP files in templates:

```bash
find /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/productswatches-glut/src/enhancements/ProductSwatches/templates -name "*.php" -exec sed -i 's/namespace Shopglut\\/namespace Productswatchesglut\\/g' {} \;
find /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/productswatches-glut/src/enhancements/ProductSwatches/templates -name "*.php" -exec sed -i 's/use Shopglut\\/use Productswatchesglut\\/g' {} \;
find /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/productswatches-glut/src/enhancements/ProductSwatches/templates -name "*.php" -exec sed -i 's/SHOPGLUT_/PRODUCTSWATCHESGLUT_/g' {} \;
```

- [ ] **Step 3: Commit**

```bash
cd /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/productswatches-glut
git add src/enhancements/ProductSwatches/templates/
git commit -m "feat: add all ProductSwatches templates (template1-30)"
```

---

## Task 13: Create Version Tracking File

**Files:**
- Create: `/productswatches-glut/productswatches-glut-version.json`

- [ ] **Step 1: Create version JSON**

```json
{
    "version": "1.0.0",
    "name": "Product Swatches Glut",
    "author": "AppGlut",
    "requires_wp": "5.8",
    "requires_php": "7.4",
    "tested_up_to": "6.4"
}
```

- [ ] **Step 2: Commit**

```bash
git add productswatches-glut-version.json
git commit -m "feat: add version tracking file"
```

---

## Task 14: Create Readme File

**Files:**
- Create: `/productswatches-glut/readme.txt`

- [ ] **Step 1: Create readme.txt**

```txt
=== Product Swatches Glut ===
Contributors: appglut
Tags: woocommerce, swatches, variation swatches, product variations, color swatches
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
Requires Plugins: woocommerce
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Beautiful product variation swatches with 30+ templates for WooCommerce.

== Description ==

Transform your WooCommerce product variations into beautiful, interactive swatches with Product Swatches Glut.

= Features =

* 30+ professional swatch templates
* Color, image, button, radio, and bicolor swatches
* Shop page swatches support
* Quick view compatibility
* Fully responsive design
* Easy customization
* Works with any WooCommerce theme
* Standalone plugin - no additional plugins required

= Templates Included =

* Template 1: Color Round Swatches
* Template 2: Image Square Swatches
* Template 3: Button Style Swatches
* Template 4: Radio Button Swatches
* Template 5: Bicolor Swatches
* And 25+ more templates...

== Installation ==

1. Upload the plugin files to the /wp-content/plugins/productswatches-glut directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Settings->Product Swatches Glut screen to configure the plugin
4. Assign templates to your product attributes

== Frequently Asked Questions ==

= Does this require ShopGlut? =

No, Product Swatches Glut works as a standalone plugin and does not require ShopGlut.

= Can I use this with ShopGlut? =

Yes! When both plugins are active, Product Swatches Glut integrates seamlessly with ShopGlut.

= How do I customize the swatches? =

Go to Product Swatches -> Templates and customize each template's appearance, including colors, sizes, and labels.

== Changelog ==

= 1.0.0 =
* Initial release
* 30+ swatch templates
* Standalone operation
* ShopGlut integration
```

- [ ] **Step 2: Commit**

```bash
git add readme.txt
git commit -m "docs: add readme.txt"
```

---

## Task 15: Update ShopGlut to Detect ProductSwatches-Glut

**Files:**
- Modify: `/shopglut/src/ShopGlutBase.php`

- [ ] **Step 1: Add is_productswatchesglut_active() method**

Add this method to ShopGlutBase class (after the other is_*_active() methods around line 420):

```php
/**
 * Check if ProductSwatchesGlut plugin is active
 *
 * @return bool True if ProductSwatchesGlut is active
 */
private function is_productswatchesglut_active() {
    // Check by active plugins list
    $active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) );

    if ( is_multisite() ) {
        // Get network active plugins
        $network_active_plugins = get_site_option( 'active_sitewide_plugins', array() );
        $active_plugins = array_merge( $active_plugins, array_keys( $network_active_plugins ) );
    }

    // Check for productswatches-glut/productswatches-glut.php plugin
    foreach ( $active_plugins as $plugin ) {
        if ( $plugin === 'productswatches-glut/productswatches-glut.php' ) {
            return true;
        }
    }

    // Also check if the main class exists
    return class_exists( 'Productswatchesglut\\ProductswatchesglutBase' );
}
```

- [ ] **Step 2: Add conditional loading in __construct()**

Find the ProductSwatches loading section (around line 152-154) and wrap it:

```php
// Only load embedded ProductSwatches module if ProductSwatchesGlut is NOT active
if ( ! $this->is_productswatchesglut_active() ) {
    SwatchesTemplates::get_instance();
    ProductSwatchesDataManage::get_instance();
    AttributeSwatchesManager::get_instance();
}
```

- [ ] **Step 3: Add conditional settings loading**

Find the ProductSwatches settings loading (around line 229) and wrap it:

```php
// Only load embedded ProductSwatches settings if ProductSwatchesGlut is NOT active
if ( ! $this->is_productswatchesglut_active() ) {
    require_once SHOPGLUT_PATH . 'src/enhancements/ProductSwatches/productSwatches-settings.php';
}
```

- [ ] **Step 4: Commit**

```bash
cd /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/shopglut
git add src/ShopGlutBase.php
git commit -m "feat: add ProductSwatchesGlut detection and conditional loading"
```

---

## Task 16: Test Standalone Plugin Activation

**Test:**
- Manual WordPress admin test

- [ ] **Step 1: Upload plugin to WordPress**

```bash
# Create zip for upload
cd /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins
zip -r productswatches-glut.zip productswatches-glut/
```

- [ ] **Step 2: Activate in WordPress admin**

1. Go to WordPress Admin -> Plugins
2. Find "Product Swatches Glut"
3. Click "Activate"
4. Verify welcome page shows on first activation

- [ ] **Step 3: Verify no PHP errors**

Check PHP error log:
```bash
tail -f /var/log/apache2/error.log
# or WordPress debug log
tail -f /media/books-audio/wordpress-sites/shopglutpro/wp-content/debug.log
```

Expected: No fatal errors

- [ ] **Step 4: Verify database tables created**

```bash
mysql -u root -p -e "USE your_database; SHOW TABLES LIKE '%productswatchesglut%';"
```

Expected: Tables created with `productswatchesglut_` prefix

- [ ] **Step 5: Commit notes**

```bash
cd /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/productswatches-glut
git commit --allow-empty -m "test: standalone plugin activates successfully"
```

---

## Task 17: Test Admin Interface

**Test:**
- Manual WordPress admin test

- [ ] **Step 1: Access Product Swatches menu**

1. Go to WordPress Admin
2. Look for "Product Swatches" menu item
3. Verify menu items appear correctly

- [ ] **Step 2: Check templates page**

1. Go to Product Swatches -> Templates
2. Verify all 30 templates show
3. Verify preview images load

- [ ] **Step 3: Check settings page**

1. Go to Product Swatches -> Settings
2. Verify settings form loads
3. Save settings and verify they persist

- [ ] **Step 4: Verify no JavaScript errors**

Open browser console and check for errors.

- [ ] **Step 5: Commit notes**

```bash
git commit --allow-empty -m "test: admin interface works correctly"
```

---

## Task 18: Test Frontend Rendering

**Test:**
- Manual frontend test

- [ ] **Step 1: Create test product**

1. Create a variable product in WooCommerce
2. Add color/size attributes
3. Assign a swatch template to attributes

- [ ] **Step 2: Test on product page**

1. View product on frontend
2. Verify swatches replace dropdowns
3. Click swatches and verify variation changes
4. Verify image updates if applicable

- [ ] **Step 3: Test on shop page (if enabled)**

1. Go to shop page
2. Verify swatches appear on loop items
3. Test add to cart with variations

- [ ] **Step 4: Test add to cart**

1. Select variations
2. Add to cart
3. Verify correct variation added

- [ ] **Step 5: Commit notes**

```bash
git commit --allow-empty -m "test: frontend rendering works correctly"
```

---

## Task 19: Test ShopGlut Integration

**Test:**
- Both plugins active test

- [ ] **Step 1: Activate both plugins**

1. Activate ShopGlut (if not active)
2. Activate ProductSwatches-Glut
3. Verify no conflicts

- [ ] **Step 2: Verify ShopGlut doesn't load embedded ProductSwatches**

Check that only standalone plugin is active by adding this test:
```php
// Add temporarily to verify
add_action('admin_init', function() {
    error_log('ShopGlut ProductSwatches loaded: ' . (class_exists('Shopglut\\enhancements\\ProductSwatches\\dataManage') ? 'yes' : 'no'));
    error_log('Standalone ProductSwatches loaded: ' . (class_exists('Productswatchesglut\\enhancements\\ProductSwatches\\dataManage') ? 'yes' : 'no'));
});
```

Expected: Only standalone shows 'yes'

- [ ] **Step 3: Test functionality with both active**

1. Create/edit product with swatches
2. Test on frontend
3. Verify works same as standalone

- [ ] **Step 4: Deactivate standalone and verify ShopGlut's embedded loads**

1. Deactivate ProductSwatches-Glut
2. Verify ShopGlut's Product Swatches still works
3. Reactivate standalone

- [ ] **Step 5: Commit notes**

```bash
cd /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/shopglut
git commit --allow-empty -m "test: ShopGlut integration works correctly"
```

---

## Task 20: Final Testing and Cleanup

**Test:**
- Comprehensive testing

- [ ] **Step 1: Test all templates**

For each template (1-30):
1. Assign to attribute
2. Test on product page
3. Verify styles load correctly
4. Test interaction

- [ ] **Step 2: Test AJAX functionality**

1. Test template switching in admin
2. Test saving settings
3. Test reset functionality
4. Test add to cart AJAX

- [ ] **Step 3: Test with different product types**

1. Simple products (should not show swatches)
2. Variable products
3. Grouped products
4. External products

- [ ] **Step 4: Test with different themes**

1. Test with default WordPress theme
2. Test with Storefront theme
3. Test with your active theme

- [ ] **Step 5: Performance check**

1. Check page load speed with plugin
2. Verify no excessive database queries
3. Check assets are properly enqueued

- [ ] **Step 6: Code cleanup**

1. Remove any debug code
2. Verify all namespaces correct
3. Check for TODO comments
4. Verify all constants defined

- [ ] **Step 7: Final commit**

```bash
cd /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/productswatches-glut
git add .
git commit -m "feat: complete ProductSwatches-Glut standalone plugin

- 30+ swatch templates
- Standalone operation
- ShopGlut integration with conditional loading
- Full admin interface
- Frontend rendering with AJAX
- Tested with WooCommerce"
```

---

## Task 21: Create Distribution Zip

**Files:**
- Create: `/productswatches-glut.zip`

- [ ] **Step 1: Create distribution zip**

```bash
cd /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins
zip -r productswatches-glut-1.0.0.zip productswatches-glut/ -x "*.git*" "*.DS_Store*"
```

- [ ] **Step 2: Verify zip contents**

```bash
unzip -l productswatches-glut-1.0.0.zip | head -50
```

Expected: All necessary files included, no .git files

- [ ] **Step 3: Test zip installation**

1. Upload zip to fresh WordPress install
2. Activate plugin
3. Verify everything works

- [ ] **Step 4: Commit final version**

```bash
cd /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/productswatches-glut
git tag v1.0.0
git push origin main --tags
```

---

## Summary

This plan creates a complete standalone Product Swatches plugin that:

1. Works independently without ShopGlut
2. Integrates seamlessly with ShopGlut when both are active
3. Includes all 30+ swatch templates
4. Provides full admin interface
5. Replaces the legacy Swatches plugin

Total estimated time: 4-6 hours for implementation.
