# ShopGlut Module Feature Specs (Free + Pro)

One spec per module. Each spec lists every feature, normal and advanced, split into Free and Pro, so a
module can be built completely and checked off. Read this page first for the shared rules.

| # | Module | ID | Spec |
|---|--------|----|------|
| 1 | Product Page Builder | `product-page` | [modules/product-page/FEATURES.md](modules/product-page/FEATURES.md) |
| 2 | Shop & Archive Builder | `shop-archive` | [modules/shop-archive/FEATURES.md](modules/shop-archive/FEATURES.md) |
| 3 | Cart Page Builder | `cart-page` | [modules/cart-page/FEATURES.md](modules/cart-page/FEATURES.md) |
| 4 | Checkout Field Editor | `checkout-fields` | [modules/checkout-fields/FEATURES.md](modules/checkout-fields/FEATURES.md) |
| 5 | Thank You Page Builder | `order-complete` | [modules/order-complete/FEATURES.md](modules/order-complete/FEATURES.md) |
| 6 | Variation Swatches | `product-swatches` | [modules/product-swatches/FEATURES.md](modules/product-swatches/FEATURES.md) |
| 7 | Product Badges | `product-badges` | [modules/product-badges/FEATURES.md](modules/product-badges/FEATURES.md) |
| 8 | Shop Filters | `shop-filters` | [modules/shop-filters/FEATURES.md](modules/shop-filters/FEATURES.md) |
| 9 | Wishlist | `wishlist` | [modules/wishlist/FEATURES.md](modules/wishlist/FEATURES.md) |
| 10 | Side Cart | `side-cart` | [modules/side-cart/FEATURES.md](modules/side-cart/FEATURES.md) |
| 11 | Popups & Banners | `popups` | [modules/popups/FEATURES.md](modules/popups/FEATURES.md) |
| 12 | Product Comparison (Pro) | `product-comparison` | [../shopglut-pro/modules/product-comparison/FEATURES.md](../shopglut-pro/modules/product-comparison/FEATURES.md) |
| 13 | Quick View (Pro) | `quick-view` | [../shopglut-pro/modules/quick-view/FEATURES.md](../shopglut-pro/modules/quick-view/FEATURES.md) |
| 14 | Product Custom Fields (Pro) | `product-custom-fields` | [../shopglut-pro/modules/product-custom-fields/FEATURES.md](../shopglut-pro/modules/product-custom-fields/FEATURES.md) |

---

## Tags used in every spec

| Tag | Meaning |
|-----|---------|
| **[S]** | The option already exists in the module's settings schema (`modules/<id>/settings/*.php`). The front end still has to read it. |
| **[N]** | New. Both the option and the code have to be added. |
| **[O]** | Front-end code exists in the old plugin (named in the spec) and can be ported. |

At the time of writing, **no module has a ported front end** (see `SHOPGLUT-FREE.md` → Status). So **[S]**
means "the option exists", not "the feature works".

## How to split Free and Pro

1. **Free must work on its own** (WordPress.org rule). The free plugin has no locked code paths. Pro code
   lives only in `shopglut-pro` and hooks in through the extension points.
2. **Free** includes everything a store needs to use the module properly: the core UI, all basic styling,
   display rules, and the common conditions that competitors also give away for free.
3. **Pro** adds:
   - extra templates
   - conversion and revenue features: upsells, timers, urgency, rewards
   - automation: emails, scheduling, recovery
   - advanced targeting: roles, schedules, complex AND/OR rules
   - analytics
   - multi-entity features: multiple lists, per-product overrides
4. When a competitor's **free** version already has a feature, ShopGlut Free should have it too. Otherwise
   the module can't compete. Each spec says where this decided the split.

## Requirements shared by every module

These apply to every module. Don't repeat them in the module specs; test them for each one.

- **Compatibility**: classic themes, block themes (FSE), WooCommerce blocks (cart, checkout, product
  collection) where they apply, HPOS, multisite, RTL, WPML and Polylang (all strings translatable, options
  registered for translation), PHP 7.4+, latest WooCommerce.
- **Performance**: assets load only on pages where the module renders (`ShopGlut_Frontend::should_load()`).
  No jQuery in new code unless WooCommerce requires it. CSS is scoped to `.shopglut-{module}`. Settings are
  read once per request and cached.
- **Security**: nonces on every AJAX action, capability checks (`shopglut_capability`), sanitizing on save,
  escaping on output, prepared SQL.
- **Accessibility**: keyboard navigation, focus states, ARIA roles for drawers, modals, tabs and swatches,
  contrast that passes WCAG AA with the default colors, `prefers-reduced-motion` respected for animations.
- **Admin**: live preview in the layout editor (`shopglut_layout_preview_url`), reset to defaults, JSON
  export and import through Tools, duplicate layout.
- **Developer**: PHP actions and filters around every rendered block (`shopglut_{module}_before_*`,
  `_after_*`), templates that can be overridden from the theme (`yourtheme/shopglut/{module}/...`),
  shortcodes and Gutenberg blocks where a module renders a standalone element.
- **Cross-module**: a module that offers an option to embed another module (for example "Enable Wishlist
  Button" in a product page template) shows that option only when the other module is on. Pro-only modules
  appear as locked teasers in free.

## Benchmark plugins

The top wordpress.org plugins in each area were downloaded to `wp-content/plugins/_competitors/<slug>/` for
reference. They sit two folders deep, so WordPress does not load them. Each spec has a "Benchmarked against"
line naming the plugins it was compared with.

How the specs were researched:
1. Read each plugin's readme description.
2. Extracted every option label from the plugins' settings code (PHP option arrays, admin screens and JS settings apps).
3. Compared those labels with each spec. Features that were missing were added under "More free options (found in ... settings code)" or in the Pro sections.

Pass 2 added 22 more plugins (58 in total) and extracted *every* translatable string from them (settings, front-end templates and compiled JS builds), not only setting labels. Those findings are under "Pass 2" headings in each spec.

The raw string lists, one file per plugin, are in `wp-content/plugins/_competitors/_option-lists/<slug>.txt`.

| Module | Benchmarks (active installs) |
|--------|------------------------------|
| Product page | ShopEngine (100k), ShopLentor (80k), Custom Product Tabs (80k), Product Gallery Slider (20k), Merchant (9k), WPC Sticky Add To Cart, WPC Buy Now (10k), WPC Frequently Bought Together (10k), Countix Sales Countdown (6k), Product Size Charts (9k) |
| Shop & archive | ShopLentor, ShopEngine, Product Table (5k), YITH Infinite Scrolling (9k), BeRocket Load More (20k), Grid/List Toggle (10k) |
| Cart page | FunnelKit Cart (30k), Freetop Free Shipping Bar (2k), Cart Abandonment Recovery (300k), CartSuite (5k), Merchant, ShopLentor (cart widgets) |
| Checkout fields | Checkout Field Editor by ThemeHigh (400k), Flexible Checkout Fields (80k), Checkout Field Manager by QuadLayers (80k) |
| Thank you page | CartFlows (200k), Thank You Page for WooCommerce by NeeBPlugins (10k), Thanko by VillaTheme (3k), ShopEngine, ShopLentor |
| Variation swatches | StorePress (300k), CartFlows (200k), Woosuite (50k), WCBoost (50k), WPC Variation Swatches (7k) |
| Product badges | YITH Badge Management (10k), Advanced Woo Labels (10k), Acowebs (10k), BeRocket (10k), Merchant (labels module) |
| Shop filters | HUSKY (80k), YITH Ajax Product Filter (70k), Filter Everything (50k), WBW (50k), BeRocket AJAX Filters (50k), WCAPF (8k) |
| Wishlist | YITH (400k), TI (100k), WPC (100k), WCBoost (40k), MoreConvert (8k) |
| Side cart | XootiX (70k), Menu Cart (70k), Modern Cart (60k), FunnelKit Cart (30k), WPC Fly Cart (10k), CartSuite (5k) |
| Popups & banners | Popup Maker (600k), Popup Builder (200k), MailOptin (10k), Icegram Engage (10k), Notivo (5k), MC4WP (1M) |
| Comparison | YITH Compare (100k), WPC Smart Compare (70k) |
| Quick view | YITH (80k), WPC (80k), QODE (10k), Merchant |
| Product custom fields | Advanced Product Fields (50k), Acowebs WCPA (30k), PPOM (20k), YITH Product Add-Ons (20k) |

## Problems found while writing the specs

Each one is described in detail in its module spec.

1. **Variation Swatches**: templates 3–30 (free 3–10 and Pro 11–30) contain a copy of the *product page*
   Template 1 schema, not swatch options. The old plugin's templates are only placeholders too. Only
   template 1 (dropdown) and template 2 (button) are real. Nothing stores per-term colors or images (there
   is no term meta).
2. **Shop Filters**: only the Category and Tag filter types are enabled. Price, attribute, rating, stock,
   search and the others are commented out in `settings/filter.php`, as they were in filterglut. Most
   designs for categories and tags are commented out as well.
3. **Free vs Pro: decided 2026-10-22.**
   - Free: side cart coupon field, AJAX filtering, the sticky add-to-cart bar, wishlist collaboration and following.
   - Pro: Load More and infinite scroll (shop archive and filter results), the free-shipping bar (including
     the basic bar), swatches on shop cards, the next-order coupon.

   `pro_features` in `module.php` and `SHOPGLUT-PRO.md` are updated.
   **Still to do in code:** the free schemas still offer Load More and infinite scroll
   (`shop-archive/settings/layout.php`, `shop-filters/settings/filter.php`). Move those options to Pro.
4. **Product Page Pro 4–10**: the schemas are thin (gallery and colors only), although the old templates
   have many more parts (tabs, reviews, related products, Q&A, bundles, recently viewed).
5. **Free shipping bar**: decided Pro (the basic bar included), even though Modern Cart and XootiX give a basic
   one free. See item 3.
6. **Product Page**: the old "Overwrite all / specific products" fields duplicate Display Rules (already
   noted in `SHOPGLUT-FREE.md`).
