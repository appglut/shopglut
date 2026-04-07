=== ShopGlut - Builder for WooCommerce ===
Contributors: appglut
Tags: woocommerce builder, woocommerce bundle, woocommerce
Requires at least: 5.1
Requires PHP: 7.4
Tested up to: 6.9
Stable tag: 1.7.8
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

ShopGlut is now a lightweight integration plugin. WooCommerce product page builder is available in **ProductPage Glut** - a dedicated plugin for creating stunning product pages. Cart page builder is available in **CartPage Glut** plugin.

== Description ==

**ShopGlut** - A lightweight integration foundation for WooCommerce customization.

The WooCommerce Product Page Builder is now available in **ProductPage Glut** - a dedicated standalone plugin for creating conversion-optimized product pages.

The WooCommerce Cart Page Builder is now available in **CartPage Glut** - a dedicated standalone plugin for creating beautiful, conversion-optimized cart pages.

== ProductPage Glut Features ==

🛍️ **Product Page Builder** - Create beautiful, conversion-optimized product pages with visual builder

= KEY FEATURES =

**Product Page Builder:**
* Multiple ready-to-use templates for different product types
* Live preview with demo mode for instant design feedback
* Product gallery with image styling and zoom effects
* Apply layouts to all products or specific products
* Mobile responsive design - perfect on all devices
* Custom layouts with flexible styling options
* Full control over product sections and elements
* Color, typography, and spacing customization
* Product image styling and gallery options
* Add to cart button styling and placement
* Related products section customization
* Product tabs and accordion support
* Social sharing integration
* SEO-friendly product page structure

== CartPage Glut Features ==

🛒 **Cart Page Builder** - Create beautiful, conversion-optimized cart pages with visual builder

= KEY FEATURES =

**Cart Page Builder:**
* Multiple ready-to-use cart page templates
* Live preview with demo mode for instant design feedback
* Product image customization in cart
* Apply layouts to cart page globally
* Mobile responsive design - perfect on all devices
* Custom layouts with flexible styling options
* Full control over cart sections and elements
* Color, typography, and spacing customization
* Quantity selector styling and placement
* Coupon form styling and placement
* Cart totals section customization
* Checkout button styling
* Cross-sells section customization
* Performance-optimized cart rendering

= BENEFITS =

✅ Zero coding required - Visual interface for everyone
✅ Mobile responsive - Perfect on all devices
✅ Lightning fast - Performance-optimized
✅ SEO friendly - Built-in schema markup
✅ Theme compatible - Works with any WordPress theme
✅ WooCommerce 3.0+ compatible

[View full documentation](https://documentation.appglut.com/productpageglut/)

== Installation ==

**Step 1: Install ProductPage Glut**
The Product Page Builder is available in the **ProductPage Glut** plugin.

1. Go to your WordPress admin → Plugins → Add New
2. Search for "ProductPage Glut" or download from [WordPress.org](https://wordpress.org/plugins/productpageglut/)
3. Click "Install Now" and then "Activate"
4. Navigate to ProductPage Glut → Product Page Layouts to start designing

**Step 2: Install CartPage Glut (Optional)**
The Cart Page Builder is available in the **CartPage Glut** plugin.

1. Go to your WordPress admin → Plugins → Add New
2. Search for "CartPage Glut" or download from [WordPress.org](https://wordpress.org/plugins/cartpage-glut/)
3. Click "Install Now" and then "Activate"
4. Navigate to CartPage Glut → Cart Page Layouts to start designing

**Step 3: ShopGlut Integration (Optional)**
ShopGlut provides integration support for ProductPage Glut, CartPage Glut, and related modules.

**Requirements:**
* WordPress 5.1+ (WordPress 6.0+ recommended)
* WooCommerce 3.0+ (latest version recommended)
* PHP 7.4+ (PHP 8.0+ recommended for better performance)
* MySQL 5.6+ or MariaDB 10.0+
* Memory limit: 128MB minimum (256MB+ recommended)
* Modern web browser with JavaScript enabled

== Frequently Asked Questions ==

= What is ProductPage Glut? =
ProductPage Glut is a dedicated plugin for building custom WooCommerce product pages. It provides a visual page builder with multiple templates and styling options.

= What is CartPage Glut? =
CartPage Glut is a dedicated plugin for building custom WooCommerce cart pages. It provides a visual cart page builder with multiple templates and styling options for creating conversion-optimized cart experiences.

= Where did all the ShopGlut modules go? =
ShopGlut has been restructured as a lightweight integration plugin. The Product Page Builder is now available in the standalone **ProductPage Glut** plugin. The Cart Page Builder is now available in the standalone **CartPage Glut** plugin.

= Is ProductPage Glut free? =
Yes! ProductPage Glut is available for free on WordPress.org.

= Is CartPage Glut free? =
Yes! CartPage Glut is available for free on WordPress.org.

= How do I access the Product Page Builder? =
After activating ProductPage Glut, go to **ProductPage Glut** → **Product Page Layouts** in your WordPress admin menu.

= How do I access the Cart Page Builder? =
After activating CartPage Glut, go to **CartPage Glut** → **Cart Page Layouts** in your WordPress admin menu.

= Will I lose my existing product page layouts? =
No! Your existing product page layouts and settings are preserved. After activating ProductPage Glut, all your previous configurations will be available.

= Will I lose my existing cart page layouts? =
No! Your existing cart page layouts and settings are preserved. After activating CartPage Glut, all your previous configurations will be available.

= Do I need to keep ShopGlut installed? =
ShopGlut provides integration support and may be required for compatibility with certain add-ons. We recommend keeping it installed for the best experience.

= Is coding knowledge required? =
No! The Product Page Builder is designed for users of all skill levels. The intuitive interface makes it easy to create professional product pages without any coding knowledge.

= Will the plugin slow down my website? =
No. ProductPage Glut is performance-optimized with conditional loading and efficient code. The plugin only loads necessary scripts when needed.

= Does the builder work with all WooCommerce product types? =
Absolutely! The plugin fully supports all WooCommerce product types including simple products, variable products (with attributes), grouped products, and external/affiliate products.

= Can I use this plugin on multiple websites? =
Yes! The free version can be used on unlimited websites.

= Where can I get support? =
For ProductPage Glut features, visit the [support forum](https://wordpress.org/support/plugin/productpageglut/). For ShopGlut integration issues, use the [ShopGlut support forum](https://wordpress.org/support/plugin/shopglut/).

== Screenshots ==

1. **ProductPage Glut Admin Dashboard**
2. **Product Page Builder Interface**
3. **Product Page Layout Templates**
4. **Live Preview Mode**
5. **CartPage Glut Admin Dashboard**
6. **Cart Page Builder Interface**
7. **Cart Page Layout Templates**

== Changelog ==

= 1.7.8 =

**Module Restructure**
* Product Page Builder moved to ProductPage Glut standalone plugin
* Cart Page Builder moved to CartPage Glut standalone plugin
* ShopGlut now serves as lightweight integration foundation
* Fixed ProductCustomFieldListTable class instantiation check
* Improved compatibility with standalone module plugins
* Enhanced integration detection for ProductCustomFieldGlut, ProductDetailsGlut, CartPageGlut, and LoginRegisterGlut

**Previous Changes**
See ProductPage Glut changelog for updates: [ProductPage Glut Changelog](https://wordpress.org/plugins/productpageglut/developers/)

= 1.7.7 =

**Fixed: Cart Module Template1 Settings**
* Fixed cart page template1 settings not applying correctly
* Fixed array merge order in cart layout settings
* Enhanced settings flattening for proper user customization
* Fixed database table existence checks across multiple modules
* Improved error handling for disabled modules

[Full Changelog Archive](https://github.com/appglut/shopglut/blob/master/CHANGELOG.md)

== Support & Resources ==

**ProductPage Glut:**
* **Plugin Download:** [WordPress.org](https://wordpress.org/plugins/productpageglut/)
* **Support Forum:** [ProductPage Glut Support](https://wordpress.org/support/plugin/productpageglut/)
* **Documentation:** [ProductPage Glut Docs](https://documentation.appglut.com/productpageglut/)

**CartPage Glut:**
* **Plugin Download:** [WordPress.org](https://wordpress.org/plugins/cartpage-glut/)
* **Support Forum:** [CartPage Glut Support](https://wordpress.org/support/plugin/cartpage-glut/)
* **Documentation:** [CartPage Glut Docs](https://documentation.appglut.com/cartpageglut/)

**ShopGlut Integration:**
* **Free Community Support:** [WordPress.org Support Forum](https://wordpress.org/support/plugin/shopglut/)
* **Complete Documentation:** [User Guide & Tutorials](https://documentation.appglut.com/shopglut/)
* **Video Tutorials:** [YouTube Channel](https://youtube.com/appglut)

**Pro Version & Upgrades:**
* **ProductPage Glut Pro:** [ProductPage Glut Pro Features](https://www.appglut.com/plugin/productpageglut)
* **CartPage Glut Pro:** [CartPage Glut Pro Features](https://www.appglut.com/plugin/cartpageglut)
* **Live Demo:** [See Pro Features in Action](https://demo.appglut.com)

**Stay Connected:**
* **Official Website:** [AppGlut.com](https://www.appglut.com)
* **Twitter:** [@AppGlut](https://x.com/AppGlutApp) - Follow for updates and tips
* **Facebook:** [AppGlut Community](https://facebook.com/appglut) - Join our user community


---

**ShopGlut provides integration support for ProductPage Glut - The WooCommerce Product Page Builder, and CartPage Glut - The WooCommerce Cart Page Builder!**

*Install ProductPage Glut to build stunning product pages with visual builder - no coding required!*

*Install CartPage Glut to build beautiful cart pages with visual builder - no coding required!*
