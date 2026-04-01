# Separate Three Modules from ShopGlut Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Create three standalone WordPress plugins (productcustomfield-glut, loginregister-glut, minicart-glut) that work independently and integrate seamlessly with ShopGlut when both are active.

**Architecture:** Each standalone plugin follows the ProductPageGlut pattern: (1) Works as a complete module with WooCommerce, (2) Detects ShopGlut and uses shared resources when available, (3) ShopGlut conditionally loads embedded module only when standalone is inactive, (4) Shows "Ready to Activate!" message in ShopGlut when installed but not active.

**Tech Stack:** PHP 7.4+, WordPress 5.0+, WooCommerce 5.0+, PSR-4 autoloading, namespaced classes

---

## File Structure

### New Plugins to Create:
```
/wp-content/plugins/
├── productcustomfield-glut/        # Complete (partially exists)
│   ├── productcustomfield-glut.php
│   ├── autoloader.php
│   ├── readme.txt
│   ├── global-assets/
│   └── src/
│       ├── ProductcustomfieldglutBase.php
│       ├── ProductcustomfieldglutDatabase.php
│       ├── ProductcustomfieldglutRegisterMenu.php
│       ├── ProductcustomfieldglutRegisterScripts.php
│       ├── WelcomePage.php
│       └── tools/productCustomField/* (from ShopGlut)
├── loginregister-glut/             # New
│   ├── loginregister-glut.php
│   ├── autoloader.php
│   ├── readme.txt
│   ├── global-assets/
│   └── src/
│       ├── LoginregisterglutBase.php
│       ├── LoginregisterglutDatabase.php
│       ├── LoginregisterglutRegisterMenu.php
│       ├── LoginregisterglutRegisterScripts.php
│       ├── WelcomePage.php
│       └── tools/loginRegister/* (from ShopGlut)
└── minicart-glut/                  # New free version
    ├── minicart-glut.php
    ├── autoloader.php
    ├── readme.txt
    ├── global-assets/
    └── src/
        ├── MinicartglutBase.php
        ├── MinicartglutDatabase.php
        ├── MinicartglutRegisterMenu.php
        ├── MinicartglutRegisterScripts.php
        ├── WelcomePage.php
        └── tools/miniCart/* (from ShopGlut)
```

### ShopGlut Files to Modify:
```
shopglut/
├── src/
│   ├── ShopGlutBase.php           # Add is_*_active() methods and conditional loading
│   ├── ShopGlutRegisterMenu.php   # Add "Ready to Activate" notifications
│   └── tools/
│       └── AllTools.php           # Conditional editor loading
```

---

## Task 1: Complete productcustomfield-glut Plugin

**Files:**
- Modify: `/media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/productcustomfield-glut/productcustomfield-glut.php`
- Modify: `/media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/productcustomfield-glut/src/ProductcustomfieldglutBase.php`
- Create: `/media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/productcustomfield-glut/src/tools/productCustomField/*` (copy from ShopGlut)
- Modify: `/media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/shopglut/src/ShopGlutBase.php`

- [ ] **Step 1: Update productcustomfield-glut.php main file**

```php
<?php
/**
 * Plugin Name: ProductCustomField Glut - Custom Fields for WooCommerce
 * Description: Add unlimited custom fields to WooCommerce product pages with beautiful designs and multiple field types
 * Version: 1.0.0
 * Author: AppGlut
 * Author URI: https://www.appglut.com
 * Plugin URI: https://www.appglut.com
 * License: GPLv2 or later
 * Text Domain: productcustomfieldglut
 * Domain Path: /languages
 * Requires Plugins: woocommerce
 */

defined('ABSPATH') or die;

define('PRODUCTCUSTOMFIELDGLUT_NAME', 'Productcustomfieldglut');
define('PRODUCTCUSTOMFIELDGLUT_VERSION', '1.0.0');
define('PRODUCTCUSTOMFIELDGLUT_BASENAME', plugin_basename(__FILE__));
define('PRODUCTCUSTOMFIELDGLUT_PATH', plugin_dir_path(__FILE__));
define('PRODUCTCUSTOMFIELDGLUT_URL', plugin_dir_url(__FILE__));
define('PRODUCTCUSTOMFIELDGLUT_ADMIN_IMAGES', plugin_dir_url(__FILE__) . 'src/library/model/assets/images/');
define('PRODUCTCUSTOMFIELDGLUT_DIRNAME', dirname(plugin_basename(__FILE__)));
define('PRODUCTCUSTOMFIELDGLUT_SLUG', dirname(plugin_basename(__FILE__)));

// Pro upgrade URLs
define('PRODUCTCUSTOMFIELDGLUT_PRICING_URL', 'https://www.appglut.com');
define('PRODUCTCUSTOMFIELDGLUT_PRO_URL', 'https://www.appglut.com');
define('PRODUCTCUSTOMFIELDGLUT_UPGRADE_URL', 'https://www.appglut.com');

// Autoloader
require __DIR__ . '/autoloader.php';

// Welcome page
if (is_admin()) {
    require_once PRODUCTCUSTOMFIELDGLUT_PATH . 'src/WelcomePage.php';
}

// Hook into WooCommerce initialization
add_action('woocommerce_init', 'productcustomfieldglut_plugin_initialize');

function productcustomfieldglut_plugin_initialize() {
    if (class_exists('WooCommerce')) {
        Productcustomfieldglut\ProductcustomfieldglutBase::get_instance();
    }
}

// Activation/Deactivation hooks
register_activation_hook(__FILE__, 'productcustomfieldglut_activate');
register_deactivation_hook(__FILE__, 'productcustomfieldglut_deactivate');

function productcustomfieldglut_activate() {
    Productcustomfieldglut\ProductcustomfieldglutDatabase::force_create_core_tables();
    set_transient('productcustomfieldglut_activation_redirect', true, 30);
    update_option('productcustomfieldglut_first_activation', get_option('productcustomfieldglut_first_activation', 0) + 1);
}

function productcustomfieldglut_deactivate() {
    flush_rewrite_rules();
}

// Welcome page menu
add_action('admin_menu', 'productcustomfieldglut_add_welcome_menu', 99);

function productcustomfieldglut_add_welcome_menu() {
    add_submenu_page(
        null,
        esc_html__('Welcome', 'productcustomfieldglut'),
        esc_html__('Welcome', 'productcustomfieldglut'),
        'manage_options',
        'productcustomfieldglut-welcome',
        'productcustomfieldglut_render_welcome_page'
    );
}

function productcustomfieldglut_render_welcome_page() {
    $welcome_page = new \Productcustomfieldglut\WelcomePage();
    $welcome_page->render_welcome_content();
}

// Redirect after activation
add_action('admin_init', 'productcustomfieldglut_redirect_after_activation');

function productcustomfieldglut_redirect_after_activation() {
    if (get_transient('productcustomfieldglut_activation_redirect')) {
        delete_transient('productcustomfieldglut_activation_redirect');
        if (isset($_GET['activate-multi'])) {
            return;
        }
        wp_safe_redirect(admin_url('admin.php?page=productcustomfieldglut-welcome'));
        exit;
    }
}
```

- [ ] **Step 2: Update ProductcustomfieldglutBase.php**

```php
<?php
namespace Productcustomfieldglut;

if (!defined('ABSPATH')) {
    exit;
}

use Productcustomfieldglut\tools\productCustomField\ProductCustomFieldDataManage;
use Productcustomfieldglut\tools\productCustomField\ProductCustomFieldHandler;

class ProductcustomfieldglutBase {

    public $menu_slug;

    public function __construct() {
        // Initialize core components
        ProductcustomfieldglutDatabase::Productcustomfieldglut_initialize();
        ProductcustomfieldglutDatabase::force_create_core_tables();
        ProductcustomfieldglutRegisterScripts::get_instance();
        ProductcustomfieldglutRegisterMenu::get_instance();

        // Initialize ProductCustomField module
        ProductCustomFieldDataManage::get_instance();
        ProductCustomFieldHandler::get_instance();

        // Add actions
        add_action('init', array($this, 'productcustomfieldglutInitialFunctions'), 9);
        add_filter('update_footer', array($this, 'productcustomfieldglut_admin_footer_version'), 999);
        add_action('admin_init', array($this, 'productcustomfieldglut_redirect_after_activation'));
    }

    public function productcustomfieldglut_redirect_after_activation() {
        if (!get_option('productcustomfieldglut_plugin_first_activation_redirect')) {
            update_option('productcustomfieldglut_plugin_first_activation_redirect', true);
            wp_safe_redirect(admin_url('admin.php?page=productcustomfieldglut-welcome'));
            exit;
        }
    }

    public function productcustomfieldglutInitialFunctions() {
        // Load setup class from ShopGlut if active, otherwise from productcustomfield-glut
        if (defined('SHOPGLUT_VERSION')) {
            require_once SHOPGLUT_PATH . 'src/library/model/classes/setup.class.php';
        } else {
            require_once PRODUCTCUSTOMFIELDGLUT_PATH . 'src/library/model/classes/setup.class.php';
        }

        // Load ProductCustomField settings
        require_once PRODUCTCUSTOMFIELDGLUT_PATH . 'src/tools/productCustomField/product-custom-field-settings.php';
    }

    public function productcustomfieldglut_admin_footer_version() {
        return '<span id="productcustomfieldglut-footer-version" style="display: none;">ProductCustomFieldGlut ' . PRODUCTCUSTOMFIELDGLUT_VERSION . '</span>';
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

- [ ] **Step 3: Copy ProductCustomField module files from ShopGlut**

Run: `cp -r /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/shopglut/src/tools/productCustomField/* /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/productcustomfield-glut/src/tools/productCustomField/`

- [ ] **Step 4: Update namespaces in copied ProductCustomField files**

For each file in `/media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/productcustomfield-glut/src/tools/productCustomField/`:

Find and replace:
- `namespace Shopglut\tools\productCustomField;` → `namespace Productcustomfieldglut\tools\productCustomField;`
- `use Shopglut\` → `use Productcustomfieldglut\`
- `SHOPGLUT_PATH` → `PRODUCTCUSTOMFIELDGLUT_PATH`
- `SHOPGLUT_URL` → `PRODUCTCUSTOMFIELDGLUT_URL`
- `SHOPGLUT_VERSION` → `PRODUCTCUSTOMFIELDGLUT_VERSION`
- `'shopglut'` text domain → `'productcustomfieldglut'`

- [ ] **Step 5: Create WelcomePage.php for productcustomfield-glut**

```php
<?php
/**
 * Welcome Page for ProductCustomFieldGlut
 */

namespace Productcustomfieldglut;

if (!defined('ABSPATH')) {
    exit;
}

class WelcomePage {

    public function render_welcome_content() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'productcustomfieldglut'));
        }
        ?>
        <div class="wrap productcustomfieldglut-welcome-page">
            <style>
                .productcustomfieldglut-welcome-page > h1 { display: none; }
                .pcfg-welcome-wrapper {
                    max-width: 1000px;
                    margin: 20px auto;
                    background: #ffffff;
                    border-radius: 12px;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                    overflow: hidden;
                }
                .pcfg-welcome-header {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    padding: 50px 40px;
                    text-align: center;
                    color: #ffffff;
                }
                .pcfg-welcome-header h1 {
                    font-size: 36px;
                    font-weight: 700;
                    margin: 0 0 10px 0;
                }
                .pcfg-welcome-content { padding: 40px; }
                .pcfg-welcome-thank-you { text-align: center; margin-bottom: 40px; }
                .pcfg-welcome-thank-you h2 { font-size: 24px; margin: 0 0 12px 0; }
                .pcfg-welcome-actions {
                    display: flex;
                    gap: 16px;
                    justify-content: center;
                }
                .pcfg-welcome-btn {
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                    padding: 12px 24px;
                    font-size: 14px;
                    font-weight: 600;
                    border-radius: 8px;
                    text-decoration: none;
                }
                .pcfg-welcome-btn--primary {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: #ffffff;
                }
            </style>

            <div class="pcfg-welcome-wrapper">
                <div class="pcfg-welcome-header">
                    <h1><?php esc_html_e('Welcome to ProductCustomFieldGlut', 'productcustomfieldglut'); ?></h1>
                    <p><?php esc_html_e('Complete WooCommerce Product Custom Field Solution', 'productcustomfieldglut'); ?></p>
                </div>

                <div class="pcfg-welcome-content">
                    <div class="pcfg-welcome-thank-you">
                        <h2><?php esc_html_e('Thank you for installing ProductCustomFieldGlut!', 'productcustomfieldglut'); ?></h2>
                        <p><?php esc_html_e('Add unlimited custom fields to your WooCommerce products with beautiful designs.', 'productcustomfieldglut'); ?></p>
                    </div>

                    <div class="pcfg-welcome-actions">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=productcustomfieldglut_fields')); ?>" class="pcfg-welcome-btn pcfg-welcome-btn--primary">
                            <?php esc_html_e('Get Started', 'productcustomfieldglut'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php
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

- [ ] **Step 6: Update ProductcustomfieldglutDatabase.php**

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
            return;
        }
        add_action('init', array(__CLASS__, 'create_custom_field_tables'));
    }

    public static function force_create_core_tables() {
        if (defined('SHOPGLUT_VERSION')) {
            return;
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

- [ ] **Step 7: Add is_productcustomfieldglut_active() to ShopGlutBase.php**

Edit: `/media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/shopglut/src/ShopGlutBase.php`

Add this method after the other `is_*_active()` methods:

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

- [ ] **Step 8: Update ShopGlutBase.php to conditionally load ProductCustomField**

In `ShopGlutBase::__construct()`, replace the ProductCustomField loading:

Find (around lines 163-164):
```php
ProductCustomFieldDataManage::get_instance();
ProductCustomFieldHandler::get_instance();
```

Replace with:
```php
// Only load embedded ProductCustomField module if ProductCustomFieldGlut is NOT active
if (!$this->is_productcustomfieldglut_active()) {
    ProductCustomFieldDataManage::get_instance();
    ProductCustomFieldHandler::get_instance();
}
```

In `ShopGlutBase::shopglutInitialFunctions()`, find:
```php
require_once SHOPGLUT_PATH . 'src/tools/productCustomField/product-custom-field-settings.php';
```

Replace with:
```php
// Only load embedded ProductCustomField settings if ProductCustomFieldGlut is NOT active
if (!$this->is_productcustomfieldglut_active()) {
    require_once SHOPGLUT_PATH . 'src/tools/productCustomField/product-custom-field-settings.php';
}
```

- [ ] **Step 9: Add "Ready to Activate" notification in ShopGlutRegisterMenu.php**

Edit: `/media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/shopglut/src/ShopGlutRegisterMenu.php`

Add this method after the other notification methods:

```php
/**
 * Render ProductCustomFieldGlut integration page
 */
public function renderProductCustomFieldIntegration() {
    $plugin_slug = 'productcustomfield-glut/productcustomfield-glut.php';
    $active_plugins = get_option('active_plugins', array());
    $is_active = in_array($plugin_slug, $active_plugins);
    $plugin_exists = file_exists(WP_PLUGIN_DIR . '/' . $plugin_slug);

    $activate_url = wp_nonce_url(admin_url('plugins.php?action=activate&plugin=' . $plugin_slug), 'activate-plugin_' . $plugin_slug);
    $github_url = 'https://github.com/appglut/productcustomfield-glut';
    ?>
    <div class="wrap productcustomfieldglut-integration">
        <style>
            .productcustomfieldglut-integration {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                padding: 40px 20px;
                margin: -20px -20px 0 -20px;
            }
            .productcustomfieldglut-integration .productcustomfieldglut-header {
                text-align: center;
                color: #ffffff;
                margin-bottom: 30px;
            }
            .productcustomfieldglut-integration .productcustomfieldglut-header h1 {
                color: #ffffff;
                margin: 0 0 10px 0;
                font-size: 32px;
            }
            .productcustomfieldglut-integration .productcustomfieldglut-content {
                max-width: 800px;
                margin: 0 auto;
            }
            .productcustomfieldglut-integration .productcustomfieldglut-card {
                background: #fff;
                border-radius: 12px;
                padding: 40px;
                text-align: center;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            }
            .productcustomfieldglut-integration .productcustomfieldglut-icon {
                font-size: 64px;
                margin-bottom: 20px;
            }
            .productcustomfieldglut-integration .productcustomfieldglut-card h2 {
                color: #2c3e50;
                font-size: 24px;
                margin: 0 0 8px 0;
            }
            .productcustomfieldglut-integration .productcustomfieldglut-notice {
                background: #e8f5e9;
                border-left: 4px solid #4caf50;
                padding: 20px;
                margin-bottom: 25px;
                border-radius: 4px;
            }
        </style>

        <div class="productcustomfieldglut-header">
            <h1>📝 <?php esc_html_e('ProductCustomFieldGlut Integration', 'shopglut'); ?></h1>
            <p><?php esc_html_e('Beautiful custom fields for WooCommerce products', 'shopglut'); ?></p>
        </div>

        <div class="productcustomfieldglut-content">
            <?php if ($plugin_exists && !$is_active): ?>
                <div class="productcustomfieldglut-card">
                    <div class="productcustomfieldglut-icon">📝</div>
                    <h2><?php esc_html_e('ProductCustomFieldGlut is Ready to Activate!', 'shopglut'); ?></h2>
                    <p><?php esc_html_e('Complete WooCommerce custom field solution with unlimited fields and beautiful designs.', 'shopglut'); ?></p>
                    <div class="productcustomfieldglut-notice">
                        <p><?php esc_html_e('ProductCustomFieldGlut is already installed on your site. Just activate it to unlock all features.', 'shopglut'); ?></p>
                        <a href="<?php echo esc_url($activate_url); ?>" class="button button-primary button-hero">
                            <?php esc_html_e('Activate ProductCustomFieldGlut', 'shopglut'); ?>
                        </a>
                    </div>
                </div>
            <?php elseif (!$plugin_exists && !$is_active): ?>
                <div class="productcustomfieldglut-card">
                    <div class="productcustomfieldglut-icon">📥</div>
                    <h2><?php esc_html_e('Get ProductCustomFieldGlut - Free Custom Field Plugin', 'shopglut'); ?></h2>
                    <p><?php esc_html_e('Complete WooCommerce custom field solution with unlimited fields and beautiful designs.', 'shopglut'); ?></p>
                    <div class="productcustomfieldglut-notice">
                        <a href="<?php echo esc_url($github_url); ?>" target="_blank" class="button button-primary button-hero">
                            <?php esc_html_e('Download ProductCustomFieldGlut', 'shopglut'); ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}
```

- [ ] **Step 10: Create readme.txt for productcustomfield-glut**

```markdown
=== ProductCustomField Glut ===
Contributors: appglut
Tags: woocommerce, custom fields, product fields, product custom fields
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add unlimited custom fields to WooCommerce product pages with beautiful designs.

== Description ==
ProductCustomField Glut is a complete custom field solution for WooCommerce products.

= Features =
* Unlimited custom fields for products
* Multiple field types: text, textarea, radio, buttons, cards, toggle
* Beautiful pre-built designs
* Position fields anywhere on product page
* Works with any WooCommerce theme
* Integrates seamlessly with ShopGlut

== Installation ==
1. Upload the plugin files to the /wp-content/plugins/productcustomfield-glut directory
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to ProductCustomField to create your first custom field

== Changelog ==
= 1.0.0 =
* Initial release
```

- [ ] **Step 11: Commit productcustomfield-glut changes**

```bash
git add /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/productcustomfield-glut/
git add /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/shopglut/src/ShopGlutBase.php
git add /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/shopglut/src/ShopGlutRegisterMenu.php
git commit -m "feat: complete productcustomfield-glut standalone plugin with ShopGlut integration"
```

---

## Task 2: Create loginregister-glut Plugin

**Files:**
- Create: `/media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/loginregister-glut/loginregister-glut.php`
- Create: `/media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/loginregister-glut/autoloader.php`
- Create: `/media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/loginregister-glut/src/LoginregisterglutBase.php`
- Create: `/media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/loginregister-glut/src/LoginregisterglutDatabase.php`
- Create: `/media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/loginregister-glut/src/LoginregisterglutRegisterMenu.php`
- Create: `/media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/loginregister-glut/src/LoginregisterglutRegisterScripts.php`
- Create: `/media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/loginregister-glut/src/WelcomePage.php`
- Create: `/media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/loginregister-glut/src/tools/loginRegister/*` (copy from ShopGlut)
- Create: `/media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/loginregister-glut/readme.txt`
- Modify: `/media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/shopglut/src/ShopGlutBase.php`
- Modify: `/media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/shopglut/src/ShopGlutRegisterMenu.php`

- [ ] **Step 1: Create loginregister-glut.php main file**

```php
<?php
/**
 * Plugin Name: LoginRegister Glut - Beautiful Login/Register for WordPress
 * Description: Override default WordPress login and registration with beautiful custom templates
 * Version: 1.0.0
 * Author: AppGlut
 * Author URI: https://www.appglut.com
 * Plugin URI: https://www.appglut.com
 * License: GPLv2 or later
 * Text Domain: loginregisterglut
 * Domain Path: /languages
 */

defined('ABSPATH') or die;

define('LOGINREGISTERGLUT_NAME', 'Loginregisterglut');
define('LOGINREGISTERGLUT_VERSION', '1.0.0');
define('LOGINREGISTERGLUT_BASENAME', plugin_basename(__FILE__));
define('LOGINREGISTERGLUT_PATH', plugin_dir_path(__FILE__));
define('LOGINREGISTERGLUT_URL', plugin_dir_url(__FILE__));
define('LOGINREGISTERGLUT_ADMIN_IMAGES', plugin_dir_url(__FILE__) . 'src/library/model/assets/images/');
define('LOGINREGISTERGLUT_DIRNAME', dirname(plugin_basename(__FILE__)));
define('LOGINREGISTERGLUT_SLUG', dirname(plugin_basename(__FILE__)));

// Pro upgrade URLs
define('LOGINREGISTERGLUT_PRICING_URL', 'https://www.appglut.com');
define('LOGINREGISTERGLUT_PRO_URL', 'https://www.appglut.com');
define('LOGINREGISTERGLUT_UPGRADE_URL', 'https://www.appglut.com');

// Autoloader
require __DIR__ . '/autoloader.php';

// Welcome page
if (is_admin()) {
    require_once LOGINREGISTERGLUT_PATH . 'src/WelcomePage.php';
}

// Hook into plugins_loaded (runs before WooCommerce)
add_action('plugins_loaded', 'loginregisterglut_plugin_initialize');

function loginregisterglut_plugin_initialize() {
    Loginregisterglut\LoginregisterglutBase::get_instance();
}

// Activation/Deactivation hooks
register_activation_hook(__FILE__, 'loginregisterglut_activate');
register_deactivation_hook(__FILE__, 'loginregisterglut_deactivate');

function loginregisterglut_activate() {
    Loginregisterglut\LoginregisterglutDatabase::force_create_core_tables();
    set_transient('loginregisterglut_activation_redirect', true, 30);
    update_option('loginregisterglut_first_activation', get_option('loginregisterglut_first_activation', 0) + 1);
}

function loginregisterglut_deactivate() {
    flush_rewrite_rules();
}

// Welcome page menu
add_action('admin_menu', 'loginregisterglut_add_welcome_menu', 99);

function loginregisterglut_add_welcome_menu() {
    add_submenu_page(
        null,
        esc_html__('Welcome', 'loginregisterglut'),
        esc_html__('Welcome', 'loginregisterglut'),
        'manage_options',
        'loginregisterglut-welcome',
        'loginregisterglut_render_welcome_page'
    );
}

function loginregisterglut_render_welcome_page() {
    $welcome_page = new \Loginregisterglut\WelcomePage();
    $welcome_page->render_welcome_content();
}

// Redirect after activation
add_action('admin_init', 'loginregisterglut_redirect_after_activation');

function loginregisterglut_redirect_after_activation() {
    if (get_transient('loginregisterglut_activation_redirect')) {
        delete_transient('loginregisterglut_activation_redirect');
        if (isset($_GET['activate-multi'])) {
            return;
        }
        wp_safe_redirect(admin_url('admin.php?page=loginregisterglut-welcome'));
        exit;
    }
}
```

- [ ] **Step 2: Create autoloader.php**

```php
<?php
/**
 * PSR-4 Autoloader for LoginRegisterGlut
 */

spl_autoload_register(function ($class) {
    $prefix = 'Loginregisterglut\\';
    $base_dir = __DIR__ . '/src/';

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

- [ ] **Step 3: Create LoginregisterglutBase.php**

```php
<?php
namespace Loginregisterglut;

if (!defined('ABSPATH')) {
    exit;
}

use Loginregisterglut\tools\loginRegister\LoginRegister;

class LoginregisterglutBase {

    public $menu_slug;

    public function __construct() {
        // Initialize core components
        LoginregisterglutDatabase::Loginregisterglut_initialize();
        LoginregisterglutDatabase::force_create_core_tables();
        LoginregisterglutRegisterScripts::get_instance();
        LoginregisterglutRegisterMenu::get_instance();

        // Initialize LoginRegister module
        LoginRegister::get_instance();

        // Add actions
        add_action('init', array($this, 'loginregisterglutInitialFunctions'), 9);
        add_filter('update_footer', array($this, 'loginregisterglut_admin_footer_version'), 999);
        add_action('admin_init', array($this, 'loginregisterglut_redirect_after_activation'));
    }

    public function loginregisterglut_redirect_after_activation() {
        if (!get_option('loginregisterglut_plugin_first_activation_redirect')) {
            update_option('loginregisterglut_plugin_first_activation_redirect', true);
            wp_safe_redirect(admin_url('admin.php?page=loginregisterglut-welcome'));
            exit;
        }
    }

    public function loginregisterglutInitialFunctions() {
        // Load setup class from ShopGlut if active, otherwise from loginregister-glut
        if (defined('SHOPGLUT_VERSION')) {
            require_once SHOPGLUT_PATH . 'src/library/model/classes/setup.class.php';
        } else {
            require_once LOGINREGISTERGLUT_PATH . 'src/library/model/classes/setup.class.php';
        }

        // Load LoginRegister settings
        require_once LOGINREGISTERGLUT_PATH . 'src/tools/loginRegister/login-register-settings.php';
    }

    public function loginregisterglut_admin_footer_version() {
        return '<span id="loginregisterglut-footer-version" style="display: none;">LoginRegisterGlut ' . LOGINREGISTERGLUT_VERSION . '</span>';
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

- [ ] **Step 4: Create LoginregisterglutDatabase.php**

```php
<?php
namespace Loginregisterglut;

if (!defined('ABSPATH')) {
    exit;
}

class LoginregisterglutDatabase {

    public static function Loginregisterglut_initialize() {
        // Check if ShopGlut is active and use its database
        if (defined('SHOPGLUT_VERSION') && class_exists('Shopglut\ShopGlutDatabase')) {
            return;
        }
        add_action('init', array(__CLASS__, 'create_login_register_tables'));
    }

    public static function force_create_core_tables() {
        if (defined('SHOPGLUT_VERSION')) {
            return;
        }
        self::create_login_register_tables();
    }

    public static function create_login_register_tables() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'shopglut_login_register_settings';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS `{$table_name}` (
            id int(11) NOT NULL AUTO_INCREMENT,
            setting_key varchar(255) NOT NULL,
            setting_value longtext DEFAULT NULL,
            created_at datetime DEFAULT NULL,
            updated_at datetime DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY setting_key (setting_key)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public static function table_login_register_settings() {
        global $wpdb;
        return $wpdb->prefix . 'shopglut_login_register_settings';
    }
}
```

- [ ] **Step 5: Copy LoginRegister module files from ShopGlut**

Run: `mkdir -p /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/loginregister-glut/src/tools/loginRegister && cp /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/shopglut/src/tools/loginRegister/* /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/loginregister-glut/src/tools/loginRegister/`

- [ ] **Step 6: Update namespaces in copied LoginRegister files**

For each file in `/media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/loginregister-glut/src/tools/loginRegister/`:

Find and replace:
- `namespace Shopglut\tools\loginRegister;` → `namespace Loginregisterglut\tools\loginRegister;`
- `use Shopglut\` → `use Loginregisterglut\`
- `SHOPGLUT_PATH` → `LOGINREGISTERGLUT_PATH`
- `SHOPGLUT_URL` → `LOGINREGISTERGLUT_URL`
- `'shopglut'` text domain → `'loginregisterglut'`

- [ ] **Step 7: Create WelcomePage.php for loginregister-glut**

```php
<?php
/**
 * Welcome Page for LoginRegisterGlut
 */

namespace Loginregisterglut;

if (!defined('ABSPATH')) {
    exit;
}

class WelcomePage {

    public function render_welcome_content() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'loginregisterglut'));
        }
        ?>
        <div class="wrap loginregisterglut-welcome-page">
            <style>
                .loginregisterglut-welcome-page > h1 { display: none; }
                .lrg-welcome-wrapper {
                    max-width: 1000px;
                    margin: 20px auto;
                    background: #ffffff;
                    border-radius: 12px;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                    overflow: hidden;
                }
                .lrg-welcome-header {
                    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
                    padding: 50px 40px;
                    text-align: center;
                    color: #ffffff;
                }
                .lrg-welcome-header h1 {
                    font-size: 36px;
                    font-weight: 700;
                    margin: 0 0 10px 0;
                }
                .lrg-welcome-content { padding: 40px; }
                .lrg-welcome-thank-you { text-align: center; margin-bottom: 40px; }
                .lrg-welcome-thank-you h2 { font-size: 24px; margin: 0 0 12px 0; }
            </style>

            <div class="lrg-welcome-wrapper">
                <div class="lrg-welcome-header">
                    <h1><?php esc_html_e('Welcome to LoginRegisterGlut', 'loginregisterglut'); ?></h1>
                    <p><?php esc_html_e('Beautiful Login & Register for WordPress', 'loginregisterglut'); ?></p>
                </div>

                <div class="lrg-welcome-content">
                    <div class="lrg-welcome-thank-you">
                        <h2><?php esc_html_e('Thank you for installing LoginRegisterGlut!', 'loginregisterglut'); ?></h2>
                        <p><?php esc_html_e('Override default WordPress login and registration with beautiful custom templates.', 'loginregisterglut'); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php
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

- [ ] **Step 8: Add is_loginregisterglut_active() to ShopGlutBase.php**

Edit: `/media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/shopglut/src/ShopGlutBase.php`

Add this method:

```php
/**
 * Check if LoginRegisterGlut plugin is active
 *
 * @return bool True if LoginRegisterGlut is active
 */
private function is_loginregisterglut_active() {
    $active_plugins = apply_filters('active_plugins', get_option('active_plugins', array()));

    if (is_multisite()) {
        $network_active_plugins = get_site_option('active_sitewide_plugins', array());
        $active_plugins = array_merge($active_plugins, array_keys($network_active_plugins));
    }

    foreach ($active_plugins as $plugin) {
        if ($plugin === 'loginregister-glut/loginregister-glut.php') {
            return true;
        }
    }

    return class_exists('Loginregisterglut\\LoginregisterglutBase');
}
```

- [ ] **Step 9: Update ShopGlutBase.php to conditionally load LoginRegister**

In `ShopGlutBase::__construct()`, find (around line 173):
```php
// LoginRegister::get_instance();
```

Replace with:
```php
// Only load embedded LoginRegister module if LoginRegisterGlut is NOT active
if (!$this->is_loginregisterglut_active()) {
    LoginRegister::get_instance();
}
```

In `ShopGlutBase::shopglutInitialFunctions()`, add:
```php
// Only load embedded LoginRegister settings if LoginRegisterGlut is NOT active
if (!$this->is_loginregisterglut_active()) {
    require_once SHOPGLUT_PATH . 'src/tools/loginRegister/login-register-settings.php';
}
```

- [ ] **Step 10: Add "Ready to Activate" notification in ShopGlutRegisterMenu.php**

Edit: `/media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/shopglut/src/ShopGlutRegisterMenu.php`

Add this method:

```php
/**
 * Render LoginRegisterGlut integration page
 */
public function renderLoginRegisterIntegration() {
    $plugin_slug = 'loginregister-glut/loginregister-glut.php';
    $active_plugins = get_option('active_plugins', array());
    $is_active = in_array($plugin_slug, $active_plugins);
    $plugin_exists = file_exists(WP_PLUGIN_DIR . '/' . $plugin_slug);

    $activate_url = wp_nonce_url(admin_url('plugins.php?action=activate&plugin=' . $plugin_slug), 'activate-plugin_' . $plugin_slug);
    $github_url = 'https://github.com/appglut/loginregister-glut';
    ?>
    <div class="wrap loginregisterglut-integration">
        <style>
            .loginregisterglut-integration {
                background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
                padding: 40px 20px;
                margin: -20px -20px 0 -20px;
            }
            .loginregisterglut-integration .loginregisterglut-header {
                text-align: center;
                color: #ffffff;
                margin-bottom: 30px;
            }
            .loginregisterglut-integration .loginregisterglut-header h1 {
                color: #ffffff;
                margin: 0 0 10px 0;
                font-size: 32px;
            }
            .loginregisterglut-integration .loginregisterglut-content {
                max-width: 800px;
                margin: 0 auto;
            }
            .loginregisterglut-integration .loginregisterglut-card {
                background: #fff;
                border-radius: 12px;
                padding: 40px;
                text-align: center;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            }
            .loginregisterglut-integration .loginregisterglut-icon {
                font-size: 64px;
                margin-bottom: 20px;
            }
            .loginregisterglut-integration .loginregisterglut-card h2 {
                color: #2c3e50;
                font-size: 24px;
                margin: 0 0 8px 0;
            }
            .loginregisterglut-integration .loginregisterglut-notice {
                background: #e8f5e9;
                border-left: 4px solid #4caf50;
                padding: 20px;
                margin-bottom: 25px;
                border-radius: 4px;
            }
        </style>

        <div class="loginregisterglut-header">
            <h1>🔐 <?php esc_html_e('LoginRegisterGlut Integration', 'shopglut'); ?></h1>
            <p><?php esc_html_e('Beautiful login and registration for WordPress', 'shopglut'); ?></p>
        </div>

        <div class="loginregisterglut-content">
            <?php if ($plugin_exists && !$is_active): ?>
                <div class="loginregisterglut-card">
                    <div class="loginregisterglut-icon">🔐</div>
                    <h2><?php esc_html_e('LoginRegisterGlut is Ready to Activate!', 'shopglut'); ?></h2>
                    <p><?php esc_html_e('Beautiful custom login and registration templates for WordPress.', 'shopglut'); ?></p>
                    <div class="loginregisterglut-notice">
                        <p><?php esc_html_e('LoginRegisterGlut is already installed on your site. Just activate it to unlock all features.', 'shopglut'); ?></p>
                        <a href="<?php echo esc_url($activate_url); ?>" class="button button-primary button-hero">
                            <?php esc_html_e('Activate LoginRegisterGlut', 'shopglut'); ?>
                        </a>
                    </div>
                </div>
            <?php elseif (!$plugin_exists && !$is_active): ?>
                <div class="loginregisterglut-card">
                    <div class="loginregisterglut-icon">📥</div>
                    <h2><?php esc_html_e('Get LoginRegisterGlut - Free Login/Register Plugin', 'shopglut'); ?></h2>
                    <p><?php esc_html_e('Beautiful custom login and registration templates for WordPress.', 'shopglut'); ?></p>
                    <div class="loginregisterglut-notice">
                        <a href="<?php echo esc_url($github_url); ?>" target="_blank" class="button button-primary button-hero">
                            <?php esc_html_e('Download LoginRegisterGlut', 'shopglut'); ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}
```

- [ ] **Step 11: Create readme.txt for loginregister-glut**

```markdown
=== LoginRegister Glut ===
Contributors: appglut
Tags: login, register, authentication, user management
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later

Beautiful login and registration templates for WordPress.

== Description ==
LoginRegister Glut replaces default WordPress login and registration with beautiful custom templates.

= Features =
* Beautiful pre-built templates
* Custom redirect options
* Social login integration ready
* Integrates seamlessly with ShopGlut

== Installation ==
1. Upload the plugin files to the /wp-content/plugins/loginregister-glut directory
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to LoginRegister to configure settings

== Changelog ==
= 1.0.0 =
* Initial release
```

- [ ] **Step 12: Commit loginregister-glut changes**

```bash
git add /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/loginregister-glut/
git add /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/shopglut/src/ShopGlutBase.php
git add /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/shopglut/src/ShopGlutRegisterMenu.php
git commit -m "feat: create loginregister-glut standalone plugin with ShopGlut integration"
```

---

## Task 3: Create minicart-glut Plugin

**Files:**
- Create: `/media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/minicart-glut/minicart-glut.php`
- Create: `/media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/minicart-glut/autoloader.php`
- Create: `/media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/minicart-glut/src/MinicartglutBase.php`
- Create: `/media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/minicart-glut/src/MinicartglutDatabase.php`
- Create: `/media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/minicart-glut/src/MinicartglutRegisterMenu.php`
- Create: `/media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/minicart-glut/src/MinicartglutRegisterScripts.php`
- Create: `/media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/minicart-glut/src/WelcomePage.php`
- Create: `/media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/minicart-glut/src/tools/miniCart/*` (copy from ShopGlut)
- Create: `/media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/minicart-glut/readme.txt`
- Modify: `/media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/shopglut/src/ShopGlutBase.php`
- Modify: `/media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/shopglut/src/ShopGlutRegisterMenu.php`

- [ ] **Step 1: Create minicart-glut.php main file**

```php
<?php
/**
 * Plugin Name: MiniCart Glut - Beautiful Mini Cart for WooCommerce
 * Description: Add a beautiful mini cart to your WooCommerce store with slide-out panel and cart sharing
 * Version: 1.0.0
 * Author: AppGlut
 * Author URI: https://www.appglut.com
 * Plugin URI: https://www.appglut.com
 * License: GPLv2 or later
 * Text Domain: minicartglut
 * Domain Path: /languages
 * Requires Plugins: woocommerce
 */

defined('ABSPATH') or die;

define('MINICARTGLUT_NAME', 'Minicartglut');
define('MINICARTGLUT_VERSION', '1.0.0');
define('MINICARTGLUT_BASENAME', plugin_basename(__FILE__));
define('MINICARTGLUT_PATH', plugin_dir_path(__FILE__));
define('MINICARTGLUT_URL', plugin_dir_url(__FILE__));
define('MINICARTGLUT_ADMIN_IMAGES', plugin_dir_url(__FILE__) . 'src/library/model/assets/images/');
define('MINICARTGLUT_DIRNAME', dirname(plugin_basename(__FILE__)));
define('MINICARTGLUT_SLUG', dirname(plugin_basename(__FILE__)));

// Pro upgrade URLs
define('MINICARTGLUT_PRICING_URL', 'https://www.appglut.com');
define('MINICARTGLUT_PRO_URL', 'https://www.appglut.com');
define('MINICARTGLUT_UPGRADE_URL', 'https://www.appglut.com');

// Autoloader
require __DIR__ . '/autoloader.php';

// Welcome page
if (is_admin()) {
    require_once MINICARTGLUT_PATH . 'src/WelcomePage.php';
}

// Hook into WooCommerce initialization
add_action('woocommerce_init', 'minicartglut_plugin_initialize');

function minicartglut_plugin_initialize() {
    if (class_exists('WooCommerce')) {
        Minicartglut\MinicartglutBase::get_instance();
    }
}

// Activation/Deactivation hooks
register_activation_hook(__FILE__, 'minicartglut_activate');
register_deactivation_hook(__FILE__, 'minicartglut_deactivate');

function minicartglut_activate() {
    Minicartglut\MinicartglutDatabase::force_create_core_tables();
    set_transient('minicartglut_activation_redirect', true, 30);
    update_option('minicartglut_first_activation', get_option('minicartglut_first_activation', 0) + 1);
}

function minicartglut_deactivate() {
    flush_rewrite_rules();
}

// Welcome page menu
add_action('admin_menu', 'minicartglut_add_welcome_menu', 99);

function minicartglut_add_welcome_menu() {
    add_submenu_page(
        null,
        esc_html__('Welcome', 'minicartglut'),
        esc_html__('Welcome', 'minicartglut'),
        'manage_options',
        'minicartglut-welcome',
        'minicartglut_render_welcome_page'
    );
}

function minicartglut_render_welcome_page() {
    $welcome_page = new \Minicartglut\WelcomePage();
    $welcome_page->render_welcome_content();
}

// Redirect after activation
add_action('admin_init', 'minicartglut_redirect_after_activation');

function minicartglut_redirect_after_activation() {
    if (get_transient('minicartglut_activation_redirect')) {
        delete_transient('minicartglut_activation_redirect');
        if (isset($_GET['activate-multi'])) {
            return;
        }
        wp_safe_redirect(admin_url('admin.php?page=minicartglut-welcome'));
        exit;
    }
}
```

- [ ] **Step 2: Create autoloader.php**

```php
<?php
/**
 * PSR-4 Autoloader for MiniCartGlut
 */

spl_autoload_register(function ($class) {
    $prefix = 'Minicartglut\\';
    $base_dir = __DIR__ . '/src/';

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

- [ ] **Step 3: Create MinicartglutBase.php**

```php
<?php
namespace Minicartglut;

if (!defined('ABSPATH')) {
    exit;
}

use Minicartglut\tools\miniCart\MiniCart;

class MinicartglutBase {

    public $menu_slug;

    public function __construct() {
        // Initialize core components
        MinicartglutDatabase::Minicartglut_initialize();
        MinicartglutDatabase::force_create_core_tables();
        MinicartglutRegisterScripts::get_instance();
        MinicartglutRegisterMenu::get_instance();

        // Initialize MiniCart module
        MiniCart::get_instance();

        // Add actions
        add_action('init', array($this, 'minicartglutInitialFunctions'), 9);
        add_filter('update_footer', array($this, 'minicartglut_admin_footer_version'), 999);
        add_action('admin_init', array($this, 'minicartglut_redirect_after_activation'));
    }

    public function minicartglut_redirect_after_activation() {
        if (!get_option('minicartglut_plugin_first_activation_redirect')) {
            update_option('minicartglut_plugin_first_activation_redirect', true);
            wp_safe_redirect(admin_url('admin.php?page=minicartglut-welcome'));
            exit;
        }
    }

    public function minicartglutInitialFunctions() {
        // Load setup class from ShopGlut if active, otherwise from minicart-glut
        if (defined('SHOPGLUT_VERSION')) {
            require_once SHOPGLUT_PATH . 'src/library/model/classes/setup.class.php';
        } else {
            require_once MINICARTGLUT_PATH . 'src/library/model/classes/setup.class.php';
        }

        // Load MiniCart settings
        require_once MINICARTGLUT_PATH . 'src/tools/miniCart/mini-cart-settings.php';
    }

    public function minicartglut_admin_footer_version() {
        return '<span id="minicartglut-footer-version" style="display: none;">MiniCartGlut ' . MINICARTGLUT_VERSION . '</span>';
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

- [ ] **Step 4: Create MinicartglutDatabase.php**

```php
<?php
namespace Minicartglut;

if (!defined('ABSPATH')) {
    exit;
}

class MinicartglutDatabase {

    public static function Minicartglut_initialize() {
        // Check if ShopGlut is active and use its database
        if (defined('SHOPGLUT_VERSION') && class_exists('Shopglut\ShopGlutDatabase')) {
            return;
        }
        add_action('init', array(__CLASS__, 'create_mini_cart_tables'));
    }

    public static function force_create_core_tables() {
        if (defined('SHOPGLUT_VERSION')) {
            return;
        }
        self::create_mini_cart_tables();
    }

    public static function create_mini_cart_tables() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'shopglut_mini_cart_settings';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS `{$table_name}` (
            id int(11) NOT NULL AUTO_INCREMENT,
            setting_key varchar(255) NOT NULL,
            setting_value longtext DEFAULT NULL,
            created_at datetime DEFAULT NULL,
            updated_at datetime DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY setting_key (setting_key)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public static function table_mini_cart_settings() {
        global $wpdb;
        return $wpdb->prefix . 'shopglut_mini_cart_settings';
    }
}
```

- [ ] **Step 5: Copy MiniCart module files from ShopGlut**

Run: `mkdir -p /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/minicart-glut/src/tools/miniCart && cp -r /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/shopglut/src/tools/miniCart/* /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/minicart-glut/src/tools/miniCart/`

- [ ] **Step 6: Update namespaces in copied MiniCart files**

For each file in `/media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/minicart-glut/src/tools/miniCart/`:

Find and replace:
- `namespace Shopglut\tools\miniCart;` → `namespace Minicartglut\tools\miniCart;`
- `use Shopglut\` → `use Minicartglut\`
- `SHOPGLUT_PATH` → `MINICARTGLUT_PATH`
- `SHOPGLUT_URL` → `MINICARTGLUT_URL`
- `'shopglut'` text domain → `'minicartglut'`

- [ ] **Step 7: Create WelcomePage.php for minicart-glut**

```php
<?php
/**
 * Welcome Page for MiniCartGlut
 */

namespace Minicartglut;

if (!defined('ABSPATH')) {
    exit;
}

class WelcomePage {

    public function render_welcome_content() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'minicartglut'));
        }
        ?>
        <div class="wrap minicartglut-welcome-page">
            <style>
                .minicartglut-welcome-page > h1 { display: none; }
                .mcg-welcome-wrapper {
                    max-width: 1000px;
                    margin: 20px auto;
                    background: #ffffff;
                    border-radius: 12px;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                    overflow: hidden;
                }
                .mcg-welcome-header {
                    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
                    padding: 50px 40px;
                    text-align: center;
                    color: #ffffff;
                }
                .mcg-welcome-header h1 {
                    font-size: 36px;
                    font-weight: 700;
                    margin: 0 0 10px 0;
                }
                .mcg-welcome-content { padding: 40px; }
                .mcg-welcome-thank-you { text-align: center; margin-bottom: 40px; }
                .mcg-welcome-thank-you h2 { font-size: 24px; margin: 0 0 12px 0; }
            </style>

            <div class="mcg-welcome-wrapper">
                <div class="mcg-welcome-header">
                    <h1><?php esc_html_e('Welcome to MiniCartGlut', 'minicartglut'); ?></h1>
                    <p><?php esc_html_e('Beautiful Mini Cart for WooCommerce', 'minicartglut'); ?></p>
                </div>

                <div class="mcg-welcome-content">
                    <div class="mcg-welcome-thank-you">
                        <h2><?php esc_html_e('Thank you for installing MiniCartGlut!', 'minicartglut'); ?></h2>
                        <p><?php esc_html_e('Add a beautiful mini cart to your WooCommerce store with slide-out panel.', 'minicartglut'); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php
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

- [ ] **Step 8: Add is_minicartglut_active() to ShopGlutBase.php**

Edit: `/media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/shopglut/src/ShopGlutBase.php`

Add this method:

```php
/**
 * Check if MiniCartGlut plugin is active
 *
 * @return bool True if MiniCartGlut is active
 */
private function is_minicartglut_active() {
    $active_plugins = apply_filters('active_plugins', get_option('active_plugins', array()));

    if (is_multisite()) {
        $network_active_plugins = get_site_option('active_sitewide_plugins', array());
        $active_plugins = array_merge($active_plugins, array_keys($network_active_plugins));
    }

    foreach ($active_plugins as $plugin) {
        if ($plugin === 'minicart-glut/minicart-glut.php') {
            return true;
        }
    }

    return class_exists('Minicartglut\\MinicartglutBase');
}
```

- [ ] **Step 9: Update ShopGlutBase.php to conditionally load MiniCart**

In `ShopGlutBase::__construct()`, find (around line 175):
```php
// MiniCart::get_instance();
```

Replace with:
```php
// Only load embedded MiniCart module if MiniCartGlut is NOT active
if (!$this->is_minicartglut_active()) {
    MiniCart::get_instance();
}
```

In `ShopGlutBase::shopglutInitialFunctions()`, find:
```php
require_once SHOPGLUT_PATH . 'src/tools/miniCart/mini-cart-settings.php';
```

Replace with:
```php
// Only load embedded MiniCart settings if MiniCartGlut is NOT active
if (!$this->is_minicartglut_active()) {
    require_once SHOPGLUT_PATH . 'src/tools/miniCart/mini-cart-settings.php';
}
```

- [ ] **Step 10: Add "Ready to Activate" notification in ShopGlutRegisterMenu.php**

Edit: `/media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/shopglut/src/ShopGlutRegisterMenu.php`

Add this method:

```php
/**
 * Render MiniCartGlut integration page
 */
public function renderMiniCartIntegration() {
    $plugin_slug = 'minicart-glut/minicart-glut.php';
    $active_plugins = get_option('active_plugins', array());
    $is_active = in_array($plugin_slug, $active_plugins);
    $plugin_exists = file_exists(WP_PLUGIN_DIR . '/' . $plugin_slug);

    $activate_url = wp_nonce_url(admin_url('plugins.php?action=activate&plugin=' . $plugin_slug), 'activate-plugin_' . $plugin_slug);
    $github_url = 'https://github.com/appglut/minicart-glut';
    ?>
    <div class="wrap minicartglut-integration">
        <style>
            .minicartglut-integration {
                background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
                padding: 40px 20px;
                margin: -20px -20px 0 -20px;
            }
            .minicartglut-integration .minicartglut-header {
                text-align: center;
                color: #ffffff;
                margin-bottom: 30px;
            }
            .minicartglut-integration .minicartglut-header h1 {
                color: #ffffff;
                margin: 0 0 10px 0;
                font-size: 32px;
            }
            .minicartglut-integration .minicartglut-content {
                max-width: 800px;
                margin: 0 auto;
            }
            .minicartglut-integration .minicartglut-card {
                background: #fff;
                border-radius: 12px;
                padding: 40px;
                text-align: center;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            }
            .minicartglut-integration .minicartglut-icon {
                font-size: 64px;
                margin-bottom: 20px;
            }
            .minicartglut-integration .minicartglut-card h2 {
                color: #2c3e50;
                font-size: 24px;
                margin: 0 0 8px 0;
            }
            .minicartglut-integration .minicartglut-notice {
                background: #e8f5e9;
                border-left: 4px solid #4caf50;
                padding: 20px;
                margin-bottom: 25px;
                border-radius: 4px;
            }
        </style>

        <div class="minicartglut-header">
            <h1>🛒 <?php esc_html_e('MiniCartGlut Integration', 'shopglut'); ?></h1>
            <p><?php esc_html_e('Beautiful mini cart for WooCommerce', 'shopglut'); ?></p>
        </div>

        <div class="minicartglut-content">
            <?php if ($plugin_exists && !$is_active): ?>
                <div class="minicartglut-card">
                    <div class="minicartglut-icon">🛒</div>
                    <h2><?php esc_html_e('MiniCartGlut is Ready to Activate!', 'shopglut'); ?></h2>
                    <p><?php esc_html_e('Beautiful mini cart with slide-out panel and cart sharing for WooCommerce.', 'shopglut'); ?></p>
                    <div class="minicartglut-notice">
                        <p><?php esc_html_e('MiniCartGlut is already installed on your site. Just activate it to unlock all features.', 'shopglut'); ?></p>
                        <a href="<?php echo esc_url($activate_url); ?>" class="button button-primary button-hero">
                            <?php esc_html_e('Activate MiniCartGlut', 'shopglut'); ?>
                        </a>
                    </div>
                </div>
            <?php elseif (!$plugin_exists && !$is_active): ?>
                <div class="minicartglut-card">
                    <div class="minicartglut-icon">📥</div>
                    <h2><?php esc_html_e('Get MiniCartGlut - Free Mini Cart Plugin', 'shopglut'); ?></h2>
                    <p><?php esc_html_e('Beautiful mini cart with slide-out panel and cart sharing for WooCommerce.', 'shopglut'); ?></p>
                    <div class="minicartglut-notice">
                        <a href="<?php echo esc_url($github_url); ?>" target="_blank" class="button button-primary button-hero">
                            <?php esc_html_e('Download MiniCartGlut', 'shopglut'); ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}
```

- [ ] **Step 11: Create readme.txt for minicart-glut**

```markdown
=== MiniCart Glut ===
Contributors: appglut
Tags: woocommerce, cart, mini cart, slide-out cart
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later

Beautiful mini cart for WooCommerce with slide-out panel.

== Description ==
MiniCart Glut adds a beautiful slide-out mini cart to your WooCommerce store.

= Features =
* Beautiful slide-out panel
* Cart sharing via email
* Customizable design
* Works with any theme
* Integrates seamlessly with ShopGlut

== Installation ==
1. Upload the plugin files to the /wp-content/plugins/minicart-glut directory
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to MiniCart to configure settings

== Changelog ==
= 1.0.0 =
* Initial release
```

- [ ] **Step 12: Commit minicart-glut changes**

```bash
git add /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/minicart-glut/
git add /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/shopglut/src/ShopGlutBase.php
git add /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/shopglut/src/ShopGlutRegisterMenu.php
git commit -m "feat: create minicart-glut standalone plugin with ShopGlut integration"
```

---

## Task 4: Create Shared Library Files for Each Plugin

Each standalone plugin needs copies of shared library files from ShopGlut.

- [ ] **Step 1: Copy library files to productcustomfield-glut**

Run:
```bash
mkdir -p /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/productcustomfield-glut/src/library/model/classes
cp /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/shopglut/src/library/model/classes/setup.class.php /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/productcustomfield-glut/src/library/model/classes/
```

- [ ] **Step 2: Copy library files to loginregister-glut**

Run:
```bash
mkdir -p /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/loginregister-glut/src/library/model/classes
cp /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/shopglut/src/library/model/classes/setup.class.php /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/loginregister-glut/src/library/model/classes/
```

- [ ] **Step 3: Copy library files to minicart-glut**

Run:
```bash
mkdir -p /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/minicart-glut/src/library/model/classes
cp /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/shopglut/src/library/model/classes/setup.class.php /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/minicart-glut/src/library/model/classes/
```

- [ ] **Step 4: Create RegisterScripts files for each plugin**

Create `/media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/productcustomfield-glut/src/ProductcustomfieldglutRegisterScripts.php`:

```php
<?php
namespace Productcustomfieldglut;

if (!defined('ABSPATH')) {
    exit;
}

class ProductcustomfieldglutRegisterScripts {

    public function __construct() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
    }

    public function enqueue_admin_scripts($hook) {
        // Load admin scripts
    }

    public function enqueue_frontend_scripts() {
        // Load frontend scripts
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

Create similar files for `loginregister-glut` and `minicart-glut`.

- [ ] **Step 5: Create RegisterMenu files for each plugin**

Create `/media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/productcustomfield-glut/src/ProductcustomfieldglutRegisterMenu.php`:

```php
<?php
namespace Productcustomfieldglut;

if (!defined('ABSPATH')) {
    exit;
}

class ProductcustomfieldglutRegisterMenu {

    public function __construct() {
        add_action('admin_menu', array($this, 'add_menu_pages'));
    }

    public function add_menu_pages() {
        add_menu_page(
            'ProductCustomField Glut',
            'ProductCustomField',
            'manage_options',
            'productcustomfieldglut_fields',
            array($this, 'render_fields_page'),
            'dashicons-list',
            30
        );
    }

    public function render_fields_page() {
        // Load list table
        $list_table = new \Productcustomfieldglut\tools\productCustomField\ProductCustomFieldListTable();
        $list_table->prepare_items();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Product Custom Fields', 'productcustomfieldglut'); ?></h1>
            <?php $list_table->display(); ?>
        </div>
        <?php
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

Create similar files for `loginregister-glut` and `minicart-glut`.

- [ ] **Step 6: Commit shared library files**

```bash
git add /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/productcustomfield-glut/src/library/
git add /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/productcustomfield-glut/src/ProductcustomfieldglutRegisterScripts.php
git add /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/productcustomfield-glut/src/ProductcustomfieldglutRegisterMenu.php
git add /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/loginregister-glut/src/library/
git add /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/loginregister-glut/src/LoginregisterglutRegisterScripts.php
git add /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/loginregister-glut/src/LoginregisterglutRegisterMenu.php
git add /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/minicart-glut/src/library/
git add /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/minicart-glut/src/MinicartglutRegisterScripts.php
git add /media/books-audio/wordpress-sites/shopglutpro/wp-content/plugins/minicart-glut/src/MinicartglutRegisterMenu.php
git commit -m "feat: add shared library files and register classes for standalone plugins"
```

---

## Task 5: Test All Three Plugins

- [ ] **Step 1: Test productcustomfield-glut standalone**

1. Deactivate ShopGlut
2. Activate productcustomfield-glut
3. Verify: Plugin activates without errors
4. Verify: Admin menu appears
5. Verify: Can create custom fields
6. Verify: Frontend displays fields

- [ ] **Step 2: Test loginregister-glut standalone**

1. Deactivate ShopGlut
2. Activate loginregister-glut
3. Verify: Plugin activates without errors
4. Verify: Admin menu appears
5. Verify: Settings page loads

- [ ] **Step 3: Test minicart-glut standalone**

1. Deactivate ShopGlut
2. Activate minicart-glut
3. Verify: Plugin activates without errors
4. Verify: Admin menu appears
5. Verify: Mini cart displays on frontend

- [ ] **Step 4: Test ShopGlut integration**

1. Activate ShopGlut
2. Activate all three standalone plugins
3. Verify: No duplicate menu items
4. Verify: No conflicts
5. Verify: "Ready to Activate" messages show correctly when plugins are installed but inactive
6. Verify: Deactivating standalone plugins restores ShopGlut embedded modules

- [ ] **Step 5: Final commit**

```bash
git add -A
git commit -m "test: complete separation of three modules from ShopGlut

- productcustomfield-glut: Standalone custom field plugin
- loginregister-glut: Standalone login/register plugin
- minicart-glut: Standalone mini cart plugin

All plugins work independently and integrate with ShopGlut when active."
```

---

## Testing Checklist

### productcustomfield-glut
- [ ] Activates without errors
- [ ] Creates database table on activation
- [ ] Admin menu displays correctly
- [ ] Can create/edit/delete custom fields
- [ ] Frontend renders fields on product pages
- [ ] Works standalone without ShopGlut
- [ ] Integrates with ShopGlut when both active
- [ ] "Ready to Activate" message shows in ShopGlut

### loginregister-glut
- [ ] Activates without errors
- [ ] Creates database table on activation
- [ ] Admin menu displays correctly
- [ ] Settings page loads and saves
- [ ] Frontend login/register override works
- [ ] Works standalone without ShopGlut
- [ ] Integrates with ShopGlut when both active
- [ ] "Ready to Activate" message shows in ShopGlut

### minicart-glut
- [ ] Activates without errors
- [ ] Creates database table on activation
- [ ] Admin menu displays correctly
- [ ] Settings page loads and saves
- [ ] Frontend mini cart displays
- [ ] Cart sharing functionality works
- [ ] Works standalone without ShopGlut
- [ ] Integrates with ShopGlut when both active
- [ ] "Ready to Activate" message shows in ShopGlut

### Integration
- [ ] All three plugins active simultaneously without conflicts
- [ ] ShopGlut recognizes all three and skips embedded modules
- [ ] Deactivating standalone plugins restores ShopGlut embedded functionality
- [ ] Database tables use same naming convention
- [ ] No duplicate menu items
- [ ] Welcome pages display correctly
