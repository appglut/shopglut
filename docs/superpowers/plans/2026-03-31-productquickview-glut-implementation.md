# ProductQuickView-Glut Standalone Plugin Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Create a standalone ProductQuickView-Glut plugin that works independently and integrates with ShopGlut when both are active

**Architecture:** Full code copy from ShopGlut's embedded QuickView module into standalone plugin, with conditional loading in ShopGlut that detects standalone plugin and skips embedded version

**Tech Stack:** WordPress, WooCommerce, PHP 7.4+, PSR-4 autoloading

---

## File Structure

**New Files (productquickview-glut plugin):**
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
│   │   └── model/
│   │       └── classes/
│   │           └── setup.class.php
│   └── WelcomePage.php
```

**Modified Files (ShopGlut integration):**
```
/wp-content/plugins/shopglut/
├── src/ShopGlutBase.php                    # Add is_productquickviewglut_active() and conditional loading
├── src/enhancements/AllEnhancements.php    # Add conditional QuickView editor loading
```

---

## Phase 1: Create Standalone Plugin Foundation

### Task 1: Create Plugin Directory and Main File

**Files:**
- Create: `/wp-content/plugins/productquickview-glut/productquickview-glut.php`

- [ ] **Step 1: Create the main plugin file with headers**

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

define( 'PRODUCTQUICKVIEWGLUT_PRICING_URL', 'https://www.appglut.com' );
define( 'PRODUCTQUICKVIEWGLUT_PRO_URL', 'https://www.appglut.com' );
define( 'PRODUCTQUICKVIEWGLUT_UPGRADE_URL', 'https://www.appglut.com' );

require __DIR__ . '/autoloader.php';

if ( is_admin() ) {
    require_once PRODUCTQUICKVIEWGLUT_PATH . 'src/WelcomePage.php';
}

add_action( 'woocommerce_init', 'productquickviewglut_plugin_initialize' );

function productquickviewglut_plugin_initialize() {
    if ( class_exists( 'WooCommerce' ) ) {
        Productquickviewglut\ProductquickviewglutBase::get_instance();
    }
}

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

- [ ] **Step 2: Commit**

```bash
git add wp-content/plugins/productquickview-glut/productquickview-glut.php
git commit -m "feat: create productquickview-glut main plugin file"
```

---

### Task 2: Create PSR-4 Autoloader

**Files:**
- Create: `/wp-content/plugins/productquickview-glut/autoloader.php`

- [ ] **Step 1: Create autoloader.php**

```php
<?php
spl_autoload_register(function ($class) {
    $prefix = 'Productquickviewglut\\';
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

- [ ] **Step 2: Commit**

```bash
git add wp-content/plugins/productquickview-glut/autoloader.php
git commit -m "feat: add PSR-4 autoloader"
```

---

### Task 3: Create Database Class

**Files:**
- Create: `/wp-content/plugins/productquickview-glut/src/ProductquickviewglutDatabase.php`
- Reference: `/wp-content/plugins/shopglut/src/ShopGlutDatabase.php` (use similar pattern)

- [ ] **Step 1: Create database class**

```php
<?php
namespace Productquickviewglut;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ProductquickviewglutDatabase {

    private static $table_version = '1.0.0';

    public static function Productquickviewglut_initialize() {
        // Check if we need to create tables
        $current_version = get_option( 'productquickviewglut_db_version', '0' );

        if ( version_compare( $current_version, self::$table_version, '<' ) ) {
            self::create_tables();
            update_option( 'productquickviewglut_db_version', self::$table_version );
        }
    }

    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $table_name = $wpdb->prefix . 'productquickview_layouts';

        $sql = "CREATE TABLE IF NOT EXISTS `{$table_name}` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `title` varchar(255) DEFAULT NULL,
            `layout_name` varchar(255) DEFAULT NULL,
            `layout_template` varchar(100) DEFAULT 'template1',
            `layout_settings` longtext DEFAULT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    public static function force_create_core_tables() {
        self::create_tables();
    }

    public static function drop_tables() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'productquickview_layouts';
        $wpdb->query( "DROP TABLE IF EXISTS `{$table_name}`" );
        delete_option( 'productquickviewglut_db_version' );
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add wp-content/plugins/productquickview-glut/src/ProductquickviewglutDatabase.php
git commit -m "feat: add database class"
```

---

### Task 4: Create Register Menu Class

**Files:**
- Create: `/wp-content/plugins/productquickview-glut/src/ProductquickviewglutRegisterMenu.php`
- Reference: `/wp-content/plugins/cartpage-glut/src/CartpageglutRegisterMenu.php`

- [ ] **Step 1: Create register menu class**

```php
<?php
namespace Productquickviewglut;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ProductquickviewglutRegisterMenu {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
    }

    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            esc_html__( 'Product Quick View', 'productquickviewglut' ),
            esc_html__( 'Quick View', 'productquickviewglut' ),
            'manage_options',
            'productquickviewglut',
            array( $this, 'render_dashboard' ),
            'dashicons-search',
            30
        );

        // All Layouts submenu
        add_submenu_page(
            'productquickviewglut',
            esc_html__( 'All Quick View Layouts', 'productquickviewglut' ),
            esc_html__( 'All Layouts', 'productquickviewglut' ),
            'manage_options',
            'productquickviewglut',
            array( $this, 'render_dashboard' )
        );

        // Add New submenu
        add_submenu_page(
            'productquickviewglut',
            esc_html__( 'Add New Layout', 'productquickviewglut' ),
            esc_html__( 'Add New', 'productquickviewglut' ),
            'manage_options',
            'productquickviewglut-add-new',
            array( $this, 'render_add_new' )
        );
    }

    public function render_dashboard() {
        // List table will be rendered here
        require_once PRODUCTQUICKVIEWGLUT_PATH . 'src/enhancements/ProductQuickView/QuickViewListTable.php';
        $list_table = new \Productquickviewglut\enhancements\ProductQuickView\QuickViewListTable();
        $list_table->prepare_items();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Product Quick View Layouts', 'productquickviewglut' ); ?></h1>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=productquickviewglut-add-new' ) ); ?>" class="page-title-action">
                <?php esc_html_e( 'Add New', 'productquickviewglut' ); ?>
            </a>
            <?php $list_table->display(); ?>
        </div>
        <?php
    }

    public function render_add_new() {
        // Template selection page
        require_once PRODUCTQUICKVIEWGLUT_PATH . 'src/enhancements/ProductQuickView/QuickViewchooseTemplates.php';
        $templates = new \Productquickviewglut\enhancements\ProductQuickView\QuickViewchooseTemplates();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Add New Quick View Layout', 'productquickviewglut' ); ?></h1>
            <?php $templates->loadProductQuickviewTemplates(); ?>
        </div>
        <?php
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
git add wp-content/plugins/productquickview-glut/src/ProductquickviewglutRegisterMenu.php
git commit -m "feat: add register menu class"
```

---

### Task 5: Create Register Scripts Class

**Files:**
- Create: `/wp/content/plugins/productquickview-glut/src/ProductquickviewglutRegisterScripts.php`
- Reference: `/wp-content/plugins/cartpage-glut/src/CartpageglutRegisterScripts.php`

- [ ] **Step 1: Create register scripts class**

```php
<?php
namespace Productquickviewglut;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ProductquickviewglutRegisterScripts {

    private static $scripts_loaded = false;

    public function __construct() {
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
    }

    public function enqueue_admin_scripts( $hook ) {
        // Only load on our admin pages
        if ( ! $this->is_our_admin_page( $hook ) ) {
            return;
        }

        if ( self::$scripts_loaded ) {
            return;
        }

        // Main admin script
        wp_enqueue_script(
            'productquickviewglut-admin',
            PRODUCTQUICKVIEWGLUT_URL . 'src/enhancements/ProductQuickView/assets/admin-script.js',
            array( 'jquery' ),
            PRODUCTQUICKVIEWGLUT_VERSION,
            true
        );

        // Localize script
        wp_localize_script( 'productquickviewglut-admin', 'productquickviewglutAdmin', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'productquickviewglut_admin_nonce' ),
            'strings' => array(
                'saveSuccess' => esc_html__( 'Settings saved successfully', 'productquickviewglut' ),
                'saveError' => esc_html__( 'Error saving settings', 'productquickviewglut' ),
            )
        ) );

        self::$scripts_loaded = true;
    }

    public function enqueue_admin_styles( $hook ) {
        if ( ! $this->is_our_admin_page( $hook ) ) {
            return;
        }

        wp_enqueue_style(
            'productquickviewglut-admin-style',
            PRODUCTQUICKVIEWGLUT_URL . 'src/enhancements/ProductQuickView/assets/admin-style.css',
            array(),
            PRODUCTQUICKVIEWGLUT_VERSION
        );
    }

    private function is_our_admin_page( $hook ) {
        $screen = get_current_screen();
        if ( ! $screen ) {
            return false;
        }

        return strpos( $screen->id, 'productquickviewglut' ) !== false;
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
git add wp-content/plugins/productquickview-glut/src/ProductquickviewglutRegisterScripts.php
git commit -m "feat: add register scripts class"
```

---

### Task 6: Create Welcome Page Class

**Files:**
- Create: `/wp-content/plugins/productquickview-glut/src/WelcomePage.php`
- Reference: `/wp-content/plugins/productpage-glut/src/WelcomePage.php`

- [ ] **Step 1: Create welcome page class**

```php
<?php
namespace Productquickviewglut;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WelcomePage {

    public function render_welcome_content() {
        ?>
        <div class="wrap productquickviewglut-welcome">
            <div class="productquickviewglut-welcome-header">
                <h1><?php esc_html_e( 'Welcome to ProductQuickView Glut!', 'productquickviewglut' ); ?></h1>
                <p><?php esc_html_e( 'Thank you for installing ProductQuickView Glut. Let\'s get started.', 'productquickviewglut' ); ?></p>
            </div>

            <div class="productquickviewglut-welcome-content">
                <div class="productquickviewglut-card">
                    <h2><?php esc_html_e( 'Create Your First Layout', 'productquickviewglut' ); ?></h2>
                    <p><?php esc_html_e( 'Start by creating a beautiful Quick View layout for your products.', 'productquickviewglut' ); ?></p>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=productquickviewglut-add-new' ) ); ?>" class="button button-primary">
                        <?php esc_html_e( 'Create Layout', 'productquickviewglut' ); ?>
                    </a>
                </div>

                <div class="productquickviewglut-card">
                    <h2><?php esc_html_e( 'View All Layouts', 'productquickviewglut' ); ?></h2>
                    <p><?php esc_html_e( 'Manage all your Quick View layouts from one place.', 'productquickviewglut' ); ?></p>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=productquickviewglut' ) ); ?>" class="button">
                        <?php esc_html_e( 'View Layouts', 'productquickviewglut' ); ?>
                    </a>
                </div>
            </div>

            <div class="productquickviewglut-welcome-footer">
                <p>
                    <?php
                    printf(
                        esc_html__( 'Need help? Check our %1$sdocumentation%2$s or visit %3$sAppGlut%4$s', 'productquickviewglut' ),
                        '<a href="https://www.appglut.com/docs" target="_blank">',
                        '</a>',
                        '<a href="https://www.appglut.com" target="_blank">',
                        '</a>'
                    );
                    ?>
                </p>
            </div>
        </div>

        <style>
            .productquickviewglut-welcome-header {
                text-align: center;
                margin: 40px 0;
            }
            .productquickviewglut-welcome-content {
                display: flex;
                gap: 20px;
                margin: 40px 0;
            }
            .productquickviewglut-card {
                flex: 1;
                background: #fff;
                padding: 30px;
                border: 1px solid #ddd;
                border-radius: 5px;
                text-align: center;
            }
            .productquickviewglut-card h2 {
                margin-top: 0;
            }
            .productquickviewglut-welcome-footer {
                text-align: center;
                margin: 40px 0;
                color: #666;
            }
        </style>
        <?php
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add wp-content/plugins/productquickview-glut/src/WelcomePage.php
git commit -m "feat: add welcome page class"
```

---

### Task 7: Create Main Base Class

**Files:**
- Create: `/wp-content/plugins/productquickview-glut/src/ProductquickviewglutBase.php`

- [ ] **Step 1: Create base class**

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
        ProductquickviewglutDatabase::Productquickviewglut_initialize();
        ProductquickviewglutDatabase::force_create_core_tables();
        ProductquickviewglutRegisterScripts::get_instance();
        ProductquickviewglutRegisterMenu::get_instance();

        QuickViewDataManage::get_instance();
        QuickViewchooseTemplates::get_instance();

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
        if ( defined( 'SHOPGLUT_VERSION' ) ) {
            require_once SHOPGLUT_PATH . 'src/library/model/classes/setup.class.php';
        } else {
            require_once PRODUCTQUICKVIEWGLUT_PATH . 'src/library/model/classes/setup.class.php';
        }

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

- [ ] **Step 2: Commit**

```bash
git add wp-content/plugins/productquickview-glut/src/ProductquickviewglutBase.php
git commit -m "feat: add main base class"
```

---

## Phase 2: Copy and Adapt QuickView Module

### Task 8: Copy QuickViewDataManage with Namespace Changes

**Files:**
- Source: `/wp-content/plugins/shopglut/src/enhancements/ProductQuickView/QuickViewDataManage.php`
- Create: `/wp-content/plugins/productquickview-glut/src/enhancements/ProductQuickView/QuickViewDataManage.php`

- [ ] **Step 1: Copy file and update namespace**

Copy the source file and make these replacements:
- `namespace Shopglut\enhancements\ProductQuickView;` → `namespace Productquickviewglut\enhancements\ProductQuickView;`
- `SHOPGLUT_PATH` → `PRODUCTQUICKVIEWGLUT_PATH`
- `SHOPGLUT_URL` → `PRODUCTQUICKVIEWGLUT_URL`
- `'shopglut'` text domain → `'productquickviewglut'`

- [ ] **Step 2: Commit**

```bash
git add wp-content/plugins/productquickview-glut/src/enhancements/ProductQuickView/QuickViewDataManage.php
git commit -m "feat: add QuickViewDataManage class"
```

---

### Task 9: Copy QuickViewchooseTemplates with Namespace Changes

**Files:**
- Source: `/wp-content/plugins/shopglut/src/enhancements/ProductQuickView/QuickViewchooseTemplates.php`
- Create: `/wp-content/plugins/productquickview-glut/src/enhancements/ProductQuickView/QuickViewchooseTemplates.php`

- [ ] **Step 1: Copy file and update namespace**

Copy the source file and make these replacements:
- `namespace Shopglut\enhancements\ProductQuickView;` → `namespace Productquickviewglut\enhancements\ProductQuickView;`
- `SHOPGLUT_PATH` → `PRODUCTQUICKVIEWGLUT_PATH`
- `SHOPGLUT_URL` → `PRODUCTQUICKVIEWGLUT_URL`
- `'shopglut'` text domain → `'productquickviewglut'`

- [ ] **Step 2: Commit**

```bash
git add wp-content/plugins/productquickview-glut/src/enhancements/ProductQuickView/QuickViewchooseTemplates.php
git commit -m "feat: add QuickViewchooseTemplates class"
```

---

### Task 10: Copy QuickViewEntity with Namespace Changes

**Files:**
- Source: `/wp-content/plugins/shopglut/src/enhancements/ProductQuickView/QuickViewEntity.php`
- Create: `/wp-content/plugins/productquickview-glut/src/enhancements/ProductQuickView/QuickViewEntity.php`

- [ ] **Step 1: Copy file and update namespace**

Copy the source file and make these replacements:
- `namespace Shopglut\enhancements\ProductQuickView;` → `namespace Productquickviewglut\enhancements\ProductQuickView;`
- `SHOPGLUT_PATH` → `PRODUCTQUICKVIEWGLUT_PATH`
- `'shopglut'` text domain → `'productquickviewglut'`

- [ ] **Step 2: Commit**

```bash
git add wp-content/plugins/productquickview-glut/src/enhancements/ProductQuickView/QuickViewEntity.php
git commit -m "feat: add QuickViewEntity class"
```

---

### Task 11: Copy QuickViewListTable with Namespace Changes

**Files:**
- Source: `/wp-content/plugins/shopglut/src/enhancements/ProductQuickView/QuickViewListTable.php`
- Create: `/wp-content/plugins/productquickview-glut/src/enhancements/ProductQuickView/QuickViewListTable.php`

- [ ] **Step 1: Copy file and update namespace**

Copy the source file and make these replacements:
- `namespace Shopglut\enhancements\ProductQuickView;` → `namespace Productquickviewglut\enhancements\ProductQuickView;`
- `SHOPGLUT_PATH` → `PRODUCTQUICKVIEWGLUT_PATH`
- `SHOPGLUT_URL` → `PRODUCTQUICKVIEWGLUT_URL`
- `'shopglut'` text domain → `'productquickviewglut'`
- Update table name: `wp_shopglut_productquickview_layouts` → `wp_productquickview_layouts`

- [ ] **Step 2: Commit**

```bash
git add wp-content/plugins/productquickview-glut/src/enhancements/ProductQuickView/QuickViewListTable.php
git commit -m "feat: add QuickViewListTable class"
```

---

### Task 12: Copy QuickViewSettingsPage with Namespace Changes

**Files:**
- Source: `/wp-content/plugins/shopglut/src/enhancements/ProductQuickView/QuickViewSettingsPage.php`
- Create: `/wp-content/plugins/productquickview-glut/src/enhancements/ProductQuickView/QuickViewSettingsPage.php`

- [ ] **Step 1: Copy file and update namespace**

Copy the source file and make these replacements:
- `namespace Shopglut\enhancements\ProductQuickView;` → `namespace Productquickviewglut\enhancements\ProductQuickView;`
- `SHOPGLUT_PATH` → `PRODUCTQUICKVIEWGLUT_PATH`
- `SHOPGLUT_URL` → `PRODUCTQUICKVIEWGLUT_URL`
- `'shopglut'` text domain → `'productquickviewglut'`

- [ ] **Step 2: Commit**

```bash
git add wp-content/plugins/productquickview-glut/src/enhancements/ProductQuickView/QuickViewSettingsPage.php
git commit -m "feat: add QuickViewSettingsPage class"
```

---

### Task 13: Copy assets.php with Namespace Changes

**Files:**
- Source: `/wp-content/plugins/shopglut/src/enhancements/ProductQuickView/assets.php`
- Create: `/wp-content/plugins/productquickview-glut/src/enhancements/ProductQuickView/assets.php`

- [ ] **Step 1: Copy file and update namespace and constants**

```php
<?php
namespace Productquickviewglut\enhancements\ProductQuickView;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class assets {

    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
    }

    public function enqueue_frontend_assets() {
        // Enqueue frontend CSS
        wp_enqueue_style(
            'productquickviewglut-frontend',
            PRODUCTQUICKVIEWGLUT_URL . 'src/enhancements/ProductQuickView/assets/quickview-frontend.css',
            array(),
            PRODUCTQUICKVIEWGLUT_VERSION
        );

        // Enqueue frontend JS
        wp_enqueue_script(
            'productquickviewglut-quickview-js',
            PRODUCTQUICKVIEWGLUT_URL . 'src/enhancements/ProductQuickView/assets/quickview-frontend.js',
            array( 'jquery' ),
            PRODUCTQUICKVIEWGLUT_VERSION,
            true
        );

        // Localize script
        wp_localize_script( 'productquickviewglut-quickview-js', 'productquickviewglut', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'productquickviewglut_nonce' ),
            'loadingText' => esc_html__( 'Loading...', 'productquickviewglut' ),
        ) );
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
git add wp-content/plugins/productquickview-glut/src/enhancements/ProductQuickView/assets.php
git commit -m "feat: add assets class"
```

---

### Task 14: Copy template-settings.php with Namespace Changes

**Files:**
- Create: `/wp-content/plugins/productquickview-glut/src/enhancements/ProductQuickView/template-settings.php`

- [ ] **Step 1: Create template-settings.php**

```php
<?php
namespace Productquickviewglut\enhancements\ProductQuickView;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once PRODUCTQUICKVIEWGLUT_PATH . 'src/enhancements/ProductQuickView/templates/template1/template1-settings.php';
```

- [ ] **Step 2: Commit**

```bash
git add wp-content/plugins/productquickview-glut/src/enhancements/ProductQuickView/template-settings.php
git commit -m "feat: add template-settings loader"
```

---

### Task 15: Copy Template Files

**Files:**
- Source: `/wp-content/plugins/shopglut/src/enhancements/ProductQuickView/templates/template1/*`
- Create: `/wp-content/plugins/productquickview-glut/src/enhancements/ProductQuickView/templates/template1/*`

- [ ] **Step 1: Copy template1Markup.php with namespace updates**

Copy source file and make replacements:
- `namespace Shopglut\enhancements\ProductQuickView;` → `namespace Productquickviewglut\enhancements\ProductQuickView;`
- `SHOPGLUT_PATH` → `PRODUCTQUICKVIEWGLUT_PATH`
- `SHOPGLUT_URL` → `PRODUCTQUICKVIEWGLUT_URL`

- [ ] **Step 2: Copy template1Style.php with namespace updates**

Copy source file and make replacements:
- `namespace Shopglut\enhancements\ProductQuickView;` → `namespace Productquickviewglut\enhancements\ProductQuickView;`
- `SHOPGLUT_PATH` → `PRODUCTQUICKVIEWGLUT_PATH`
- `SHOPGLUT_URL` → `PRODUCTQUICKVIEWGLUT_URL`

- [ ] **Step 3: Copy template1-settings.php with namespace updates**

Copy source file and make replacements:
- `SHOPGLUT_PATH` → `PRODUCTQUICKVIEWGLUT_PATH`
- `SHOPGLUT_URL` → `PRODUCTQUICKVIEWGLUT_URL`
- `'shopglut'` text domain → `'productquickviewglut'`

- [ ] **Step 4: Commit**

```bash
git add wp-content/plugins/productquickview-glut/src/enhancements/ProductQuickView/templates/
git commit -m "feat: add template1 files"
```

---

### Task 16: Copy Asset Files (JS and CSS)

**Files:**
- Source: `/wp-content/plugins/shopglut/src/enhancements/ProductQuickView/assets/*`
- Create: `/wp-content/plugins/productquickview-glut/src/enhancements/ProductQuickView/assets/*`

- [ ] **Step 1: Copy all asset files**

```bash
cp -r /wp-content/plugins/shopglut/src/enhancements/ProductQuickView/assets/* \
     /wp-content/plugins/productquickview-glut/src/enhancements/ProductQuickView/assets/
```

- [ ] **Step 2: Update any path references in JS files**

In `quickview-frontend.js` and `admin-script.js`, update any hardcoded paths or URLs if present.

- [ ] **Step 3: Commit**

```bash
git add wp-content/plugins/productquickview-glut/src/enhancements/ProductQuickView/assets/
git commit -m "feat: add asset files"
```

---

### Task 17: Copy Library Classes

**Files:**
- Source: `/wp-content/plugins/shopglut/src/library/model/classes/setup.class.php`
- Create: `/wp-content/plugins/productquickview-glut/src/library/model/classes/setup.class.php`

- [ ] **Step 1: Copy setup.class.php**

```bash
mkdir -p /wp-content/plugins/productquickview-glut/src/library/model/classes/
cp /wp-content/plugins/shopglut/src/library/model/classes/setup.class.php \
   /wp-content/plugins/productquickview-glut/src/library/model/classes/setup.class.php
```

- [ ] **Step 2: Commit**

```bash
git add wp-content/plugins/productquickview-glut/src/library/
git commit -m "feat: add library setup class"
```

---

### Task 18: Create Readme File

**Files:**
- Create: `/wp-content/plugins/productquickview-glut/readme.txt`

- [ ] **Step 1: Create readme.txt**

```txt
=== ProductQuickView Glut - Quick View for WooCommerce ===
Contributors: appglut
Tags: woocommerce, quick view, product quick view, ajax popup, product modal
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Beautiful quick view modal for WooCommerce products with customizable templates.

== Description ==

ProductQuickView Glut adds a beautiful quick view modal to your WooCommerce store,
allowing customers to view product details without leaving the current page.

= Key Features =

* Beautiful, responsive quick view modal
* Customizable templates
* Ajax-powered product loading
* Mobile-friendly design
* Works with any WooCommerce theme
* Integrates seamlessly with ShopGlut plugin

= Integration with ShopGlut =

ProductQuickView Glut works as a standalone plugin but also integrates with
ShopGlut when both plugins are active. When ShopGlut is active, ProductQuickView
Glut uses ShopGlut's shared resources for better performance.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/productquickview-glut` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to Quick View > Add New to create your first layout

== Changelog ==

= 1.0.0 =
* Initial release

- [ ] **Step 2: Commit**

```bash
git add wp-content/plugins/productquickview-glut/readme.txt
git commit -m "docs: add readme file"
```

---

## Phase 3: ShopGlut Integration

### Task 19: Add is_productquickviewglut_active() Check to ShopGlutBase

**Files:**
- Modify: `/wp-content/plugins/shopglut/src/ShopGlutBase.php`

- [ ] **Step 1: Add the detection method after is_shopfilterglut_active()**

```php
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
```

- [ ] **Step 2: Uncomment and update QuickView loading in __construct()**

Find the commented lines (around line 140-141):
```php
// QuickViewchooseTemplates::get_instance();
// QuickViewDataManage::get_instance();
```

Replace with:
```php
// Only load embedded QuickView module if ProductQuickViewGlut is NOT active
if ( ! $this->is_productquickviewglut_active() ) {
    QuickViewchooseTemplates::get_instance();
    QuickViewDataManage::get_instance();
}
```

- [ ] **Step 3: Add conditional settings loading in shopglutInitialFunctions()**

Find the commented line (around line 234):
```php
//require_once SHOPGLUT_PATH . 'src/enhancements/ProductQuickView/template-settings.php';
```

Replace with:
```php
// Only load embedded QuickView settings if ProductQuickViewGlut is NOT active
if ( ! $this->is_productquickviewglut_active() ) {
    require_once SHOPGLUT_PATH . 'src/enhancements/ProductQuickView/template-settings.php';
}
```

- [ ] **Step 4: Commit**

```bash
git add wp-content/plugins/shopglut/src/ShopGlutBase.php
git commit -m "feat: add ProductQuickViewGlut integration check"
```

---

### Task 20: Update AllEnhancements for Conditional QuickView Editor

**Files:**
- Modify: `/wp-content/plugins/shopglut/src/enhancements/AllEnhancements.php`

- [ ] **Step 1: Find the QuickView editor loading section**

Search for QuickView-related code around line 27-33.

- [ ] **Step 2: Add conditional check before loading QuickView editor**

The editor loading should check if standalone plugin is active. Modify the relevant methods to skip QuickView editor when standalone is active:

```php
private function loadProductQuickviewEditor() {
    // Only load if standalone plugin is NOT active
    if ( class_exists( 'Productquickviewglut\\ProductquickviewglutBase' ) ) {
        return; // Standalone plugin handles its own editor
    }

    // Load embedded QuickView editor
    if ( class_exists( 'Shopglut\\enhancements\\ProductQuickView\\QuickViewSettingsPage' ) ) {
        $quickview_editor = new \Shopglut\enhancements\ProductQuickView\QuickViewSettingsPage();
        $quickview_editor->loadQuickViewEditor();
    }
}
```

- [ ] **Step 3: Commit**

```bash
git add wp-content/plugins/shopglut/src/enhancements/AllEnhancements.php
git commit -m "feat: add conditional QuickView editor loading"
```

---

## Phase 4: Testing and Verification

### Task 21: Test Standalone Plugin Activation

**Files:** None (testing)

- [ ] **Step 1: Activate standalone plugin only**

1. Ensure ShopGlut embedded QuickView is disabled
2. Activate productquickview-glut plugin
3. Verify database tables are created: `wp_productquickview_layouts`
4. Verify admin menu appears: "Quick View"

- [ ] **Step 2: Test creating a layout**

1. Navigate to Quick View > Add New
2. Select template1
3. Customize settings
4. Save layout
5. Verify it appears in All Layouts list

- [ ] **Step 3: Test frontend QuickView**

1. Create a test product
2. Visit shop page
3. Click QuickView button
4. Verify modal opens with product data

- [ ] **Step 4: Record results**

Create a note with test results:
- [ ] Standalone plugin activates successfully
- [ ] Database tables created
- [ ] Admin menu appears
- [ ] Layout creation works
- [ ] Frontend QuickView works

---

### Task 22: Test ShopGlut Integration (Both Active)

**Files:** None (testing)

- [ ] **Step 1: Activate both plugins**

1. Activate ShopGlut
2. Activate productquickview-glut
3. Verify no conflicts occur

- [ ] **Step 2: Verify standalone takes precedence**

1. Check that standalone QuickView is used
2. Check that embedded QuickView is NOT loaded
3. Verify admin menu shows standalone plugin's menu

- [ ] **Step 3: Test database compatibility**

1. Create a layout using standalone plugin
2. Verify it saves to correct table
3. Deactivate standalone
4. Verify ShopGlut embedded can create its own layouts (different table)

- [ ] **Step 4: Record results**

Create a note with test results:
- [ ] Both plugins active without conflicts
- [ ] Standalone takes precedence
- [ ] Database tables are separate
- [ ] No duplicate menus or conflicts

---

### Task 23: Test ShopGlut Embedded Mode

**Files:** None (testing)

- [ ] **Step 1: Deactivate standalone, activate only ShopGlut**

1. Deactivate productquickview-glut
2. Ensure ShopGlut is active
3. Verify embedded QuickView loads

- [ ] **Step 2: Test embedded QuickView**

1. Create layout using ShopGlut's QuickView
2. Verify it saves to ShopGlut's table
3. Test frontend functionality

- [ ] **Step 3: Reactivate standalone**

1. Activate productquickview-glut again
2. Verify standalone takes over
3. Verify embedded is disabled

- [ ] **Step 4: Record results**

Create a note with test results:
- [ ] Embedded mode works when standalone inactive
- [ ] Reactivating standalone switches back correctly
- [ ] No data loss or corruption

---

### Task 24: Create Version File

**Files:**
- Create: `/wp-content/plugins/productquickview-glut/productquickview-glut-version.json`

- [ ] **Step 1: Create version JSON file**

```json
{
    "version": "1.0.0",
    "name": "ProductQuickView Glut",
    "slug": "productquickview-glut",
    "author": "AppGlut",
    "requires_wp": "5.0",
    "requires_php": "7.4",
    "requires_woo": "5.0"
}
```

- [ ] **Step 2: Commit**

```bash
git add wp-content/plugins/productquickview-glut/productquickview-glut-version.json
git commit -m "feat: add version file"
```

---

### Task 25: Create GitHub Actions CI (Optional)

**Files:**
- Create: `/wp-content/plugins/productquickview-glut/.github/workflows/ci.yml`

- [ ] **Step 1: Create CI workflow**

```yaml
name: CI

on:
  push:
    branches: [ main ]
  pull_request:
    branches: [ main ]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '7.4'
    
    - name: Validate composer.json
      run: composer validate --no-check-all --strict
```

- [ ] **Step 2: Commit**

```bash
git add wp-content/plugins/productquickview-glut/.github/workflows/ci.yml
git commit -m "ci: add GitHub Actions workflow"
```

---

## Completion Checklist

- [ ] All tasks completed
- [ ] Standalone plugin works independently
- [ ] ShopGlut integration works correctly
- [ ] No conflicts when both active
- [ ] Embedded mode works when standalone inactive
- [ ] All tests pass
- [ ] Documentation complete
- [ ] Ready for release
