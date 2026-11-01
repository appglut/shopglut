# ShopGlut (Free) — WooCommerce Builder

ShopGlut 2.0 merges the separate "Glut" plugins into one WooCommerce builder with a single admin. Each
feature is a **module** that can be switched on or off. The admin is a React app modeled on FormGlut, and
options are drawn with the **AppGlut Framework** (bundled in `libraries/appglut-framework`).

Pro templates and Pro-only modules live in **ShopGlut Pro** (see `shopglut-pro/SHOPGLUT-PRO.md`). The free
plugin keeps no locked code (WordPress.org rule): it only *lists* Pro templates as locked teasers.

Full Free + Pro feature specs for every module: `MODULE-FEATURES.md` (index) and `modules/<id>/FEATURES.md`.

---

## Modules

| # | Module | ID | Type | Old plugin(s) it replaces | Free content |
|---|--------|----|------|---------------------------|--------------|
| 1 | Product Page Builder | `product-page` | Builder (layouts) | productpage-glut | Templates 1–7 |
| 2 | Shop & Archive Builder | `shop-archive` | Builder | shop-archivepage-glut | Card templates 1–10 |
| 3 | Cart Page Builder | `cart-page` | Builder | cartpage-glut | Templates 1–3 |
| 4 | Checkout Field Editor | `checkout-fields` | Settings | checkoutglut | Billing / shipping / order field editor |
| 5 | Thank You Page Builder | `order-complete` | Builder | ordercomplete-glut | Template 1 |
| 6 | Variation Swatches | `product-swatches` | Builder | productswatches-glut | Swatch templates 1–10 |
| 7 | Product Badges | `product-badges` | Builder (one badge per layout) | productbadges-glut | Unlimited badges |
| 8 | Shop Filters | `shop-filters` | Builder (one filter panel per layout) | filterglut (newer copy of shopfilter-glut) | All filter types and designs |
| 9 | Wishlist | `wishlist` | Settings | wishglut | Full wishlist options |
| 10 | Side Cart | `side-cart` | Settings | sidercart-glut | Full side cart options |
| 11 | Popups & Banners | `popups` | Builder (one popup per layout) | none (new) | Modal, announcement bar, slide-in, subscribe popup |

Not merged:
- **swatchglut** is a copy of the product page builder with "product" renamed to "swatch". It is not a
  variation swatch plugin, so it is dropped. Variation swatches come from productswatches-glut.
- **shopfilter-glut** is an older copy of filterglut.

### Free features per module

**Product Page Builder**
- 7 templates with demo preview images.
- Gallery, summary, attributes, purchase box, description and reviews tabs, related products. Every part is styled from the layout editor.
- Display rules: all products, chosen categories, or chosen products, with exclusions. When several layouts are active, priority decides which one applies.

**Shop & Archive Builder**
- 10 product card templates.
- Grid and list views, columns per device, toolbar, pagination styles.
- Display rules: shop page, product categories (all or chosen), tags, search results.

**Cart Page Builder**
- 3 templates.
- Product table, +/- quantity, coupon box, order summary, security badges, continue-shopping link.

**Checkout Field Editor**
- Edit core billing, shipping and order fields: label, placeholder, width, required, order, show or hide.
- Add text, textarea, email, phone, number, select, radio, checkbox and date fields.
- Custom fields show in orders and emails. Works with classic and block checkout.

**Thank You Page Builder**
- 1 template: header, order summary, items table, addresses, action buttons.

**Variation Swatches**
- 10 swatch templates: color, image, button and radio swatches, with out-of-stock states.
- Each swatch style can be limited to chosen attributes.

**Product Badges**
- Unlimited badges.
- Conditions: sale (percent or amount, minimum discount), new (X days), out of stock.
- 6 image positions or before the title. Colors, fonts, padding, radius, border and shadow.
- Shown on the shop page, the single product page, categories, tags or chosen products.

**Shop Filters**
- Filters: category, tag, attribute, price, rating, stock, shipping class.
- 20+ designs: checkbox, radio, dropdown, button, color, image, slider, tree, cloud, and more.
- Include or exclude terms, per-term colors and images.

**Wishlist**
- Buttons on product, shop and archive pages.
- Guest wishlists (cookie), merged on login.
- Share by link, social networks and QR code. Print the wishlist.
- Menu counter, shortcodes and widgets. Popular products.

**Side Cart**
- Floating cart icon with item count.
- Slide, fade or scale animation, from the left or right. Auto-open on add to cart.
- +/- quantity, product images. Menu cart item, shortcode, share the cart by email.

**Popups & Banners** (new module)
- 4 types: modal popup, announcement bar, slide-in box, subscribe popup.
- Triggers: page load with a delay, scroll %, click. Frequency: every view, per session, every X days, never again.
- Subscribe form with consent, a local subscribers list and CSV export.
- Display rules by page, product, category, visitor and device.

**Shared**
- Dashboard: stats and quick access to every builder.
- Modules page: on/off switches. Modules that are off load no code.
- Global Settings: store accent color (the `--shopglut-accent` CSS variable), where front-end assets load, custom CSS, remove data on uninstall.
- Tools: JSON export and import (settings and/or layouts; imported layouts arrive as drafts), system status, reset.

---

## Status of the merge (read this before building on it)

| Part | Status |
|------|--------|
| Admin app (Dashboard, Modules, layout lists, template picker, layout editor, Settings, Tools, Pro page) | **Done** |
| Module registry, on/off, capabilities, layouts table, display rules, priority | **Done** |
| Option schemas of every module, ported from the old plugins | **Done**: about 45 settings files run through `ShopGlut_Schema` |
| Saved values keep the old shape (`settings[metabox_id][field]`, flat options for wishlist / side cart) | **Done**, so old template markup can read them unchanged |
| **Front-end rendering** (templates, hooks, AJAX, CSS / JS of each module) | **Not ported yet**. The old plugins still hold this code. |
| Migration of data from the old plugins' tables and options | Not started |

### Next: port each module's front end
For each module, move the old `templateN/templateNMarkup.php`, `templateNStyle.php`, `*-frontend.js`,
`ModuleIntegration.php` and hooks into `modules/<id>/` and start them from the module's `boot()`:

1. Rename the namespace to `ShopGlut\Modules\<Name>`, and replace the old constants with `$this->dir` / `$this->url`.
2. Read the layout with `shopglut_get_active_layout( 'product-page', array( 'product_id' => $id ) )` instead of querying the old table. `$layout['settings']` has the same keys as before.
3. Read module options with `shopglut_get_module_setting( 'wishlist', 'wishlist-require-login' )`.
4. Load assets only when `ShopGlut_Frontend::should_load()` is true.
5. Add a `shopglut_layout_preview_url` filter to enable the editor's Preview button.

Suggested order (smallest first): Side Cart → Badges → Thank You → Cart → Checkout Fields → Wishlist →
Filters → Shop & Archive → Swatches → Product Page.

Known items to settle while porting:
- The product page template settings still contain the old "Overwrite all / specific products" fields. They duplicate the new Display Rules section. Remove them once the front end reads Display Rules.
- The per-term colors and images of Shop Filters are now stored as `value[term_id]`. The old shape was `value[tax_color][repeatable_id][term_id]`. Adapt the filter markup to the new shape.

---

## Architecture

```
shopglut/
  shopglut.php                    bootstrap (boots on init; needs WooCommerce)
  uninstall.php                   removes data if Global Settings → Remove all data is on
  libraries/appglut-framework/    options framework (bundled)
  includes/
    class-shopglut-module.php     base class of a module
    class-shopglut-modules.php    registry, on/off state (option shopglut_modules)
    class-shopglut-layouts.php    layouts repository (table {prefix}shopglut_layouts)
    class-shopglut-db.php         schema / upgrades
    class-shopglut-schema.php     bridge: old AGSHOPGLUT-style schema files → AppGlut sections
    class-shopglut-panels.php     AppGlut panels without menus: module Settings + layout editor
    class-shopglut-settings.php   Global Settings panel (option shopglut_settings)
    class-shopglut-admin.php      menus, page shell, assets, data for the app
    class-shopglut-ajax.php       admin AJAX (nonce shopglut_admin)
    class-shopglut-frontend.php   shared front-end assets
  modules/<id>/module.php         one module; settings/*.php are its option schemas
  assets/src/                     React admin source (Vite) → resources/
```

**Storage**
- `shopglut_modules`: module on/off.
- `shopglut_settings`: Global Settings.
- `shopglut_mod_<module>`: a module's Settings, e.g. `shopglut_mod_side_cart`.
- `{prefix}shopglut_layouts`: `id, module, name, template, status (active|draft), priority, settings (JSON)`.

**Admin pages**
- `shopglut`: Dashboard.
- `shopglut-modules`: Modules.
- `shopglut-<module>`: the module's layouts, or its Settings. `&tab=settings` opens Settings.
- `shopglut-layout&layout_id=N`: layout editor.
- `shopglut-settings`: Global Settings. `shopglut-tools`: Tools. `shopglut-pro`: what Pro adds.

On option pages, React draws only the header. The AppGlut panel under it is printed by PHP and saves
through AppGlut's own AJAX (`appglut_save`).

### Writing a module

```php
// modules/my-module/module.php
class ShopGlut_Module_My_Module extends ShopGlut_Module {
	public function id() { return 'my-module'; }
	public function info() {
		return array( 'title' => __( 'My Module', 'shopglut' ), 'icon' => 'faStore', 'category' => 'product', 'layouts' => true );
	}
	public function templates() { return $this->numbered_templates( 3, 3 ); }          // template1..3, all free
	public function layout_sections( $template, $layout ) { return $this->template_sections( $template, $layout ); } // settings/{template}.php
	public function settings_sections() { return array( /* AppGlut sections */ ); }
	public function boot() { /* front-end hooks */ }
}
return 'ShopGlut_Module_My_Module';
```

Add-ons register modules on `shopglut_register_modules`: `$registry->register( new My_Module(), __DIR__ )`.

### Extension points

PHP filters and actions:
- `shopglut_register_modules`, `shopglut_loaded`, `shopglut_module_booted_{id}`, `shopglut_module_toggled`.
- `shopglut_module_templates`, `shopglut_{id}_templates`, `shopglut_template_settings_file`.
- `shopglut_module_settings_sections`, `shopglut_{id}_settings_sections`, `shopglut_layout_sections`, `shopglut_global_settings_sections`.
- `shopglut_resolved_layout`, `shopglut_layout_summary`, `shopglut_layout_preview_url`, `shopglut_new_layout_settings`.
- `shopglut_layout_created` / `_updated` / `_deleted`.
- `shopglut_capability` (default `manage_woocommerce`; contexts: menu, dashboard, modules, settings, tools, `module:{id}`).
- `shopglut_admin_menu`, `shopglut_admin_entry_map`, `shopglut_admin_localize_data`, `shopglut_admin_enqueue_scripts`, `shopglut_module_script_handles`.
- `shopglut_is_pro`, `shopglut_load_frontend_assets`, `shopglut_install_tables`, `shopglut_uninstall`, `shopglut_schema_legacy_field`.

JS filters (`window.shopglut.hooks`, or `wp.hooks`):
- Pages and navigation: `shopglut.adminPages`, `shopglut.navMenu.items`, `shopglut.header.items`, `shopglut.module.tabs`, `shopglut.tools.tabs`.
- Content: `shopglut.dashboard.cards`, `shopglut.modules.categories`.
- Layouts: `shopglut.layouts.columns`, `shopglut.layouts.params`, `shopglut.layoutEditor.actions`.
- Look: `shopglut.icons`, `shopglut.theme`.
- Action `shopglut.ready`.

`window.shopglut` exposes `React`, `ReactDOM`, `antd`, `FontAwesomeIcon`, `i18n`, `components` (`Header`,
`ModuleHeader`) and `utils` (`api`, `moduleIcon`). Add-on components must use this React, not `wp.element`.

### Build

```
npm install
npm run build     # assets/src → resources/ (PHP reads resources/.vite/manifest.json for CSS)
```
