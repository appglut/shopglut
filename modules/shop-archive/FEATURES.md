# Shop & Archive Builder: Feature Spec

- **ID**: `shop-archive`
- **Type**: builder. Each layout uses one card template. All templates share `settings/layout.php`.
- **Templates**: Free card templates 1–10, Pro 11–19.
- **Old code [O]**: `shop-archivepage-glut/src/layouts/shopLayout/templates/template1–19.php`, `pagination.php`.
- **Benchmarked against**: ShopLentor (Product Grid Modern/Editorial, archive builder), ShopEngine, Product
  Table for WooCommerce, YITH Infinite Scrolling.

Tags: **[S]** the option exists in the schema · **[N]** new · **[O]** old code can be ported.

---

## 1. Free: core

### Where it applies
- Shop page, product categories (all or chosen), product tags, search results (`display_fields`). **[S]**
- "Overwrite WooCommerce shop" switch, archive page selection. **[S]**
- Product attribute archives and brand taxonomy (`product_brand`) archives. **[N]**
- Shortcode `[shopglut_products layout="ID"]` and a Gutenberg block, so a grid can be placed on any page. **[N]**

### Product query
- Include or exclude products, categories and tags. **[S]**
- Product set: all, best selling, recent, featured, top rated, on sale, in stock, out of stock. **[S]**
  (Fix the label "Out Of Products" to "Out of Stock Products".)
- Sorting: title, slug, ID, author, SKU, sales, price low to high or high to low, date, last modified,
  rating, featured, stock, review count, random. Ascending or descending. **[S]**
- On WooCommerce archives the main query is used, so filters and the theme's ordering keep working.
  Custom query arguments apply only to shortcodes and blocks. **[N]**

### Grid
- Columns 1–12, column and row gap, margin, padding, typography, background, border. **[S]**
- Columns per device (desktop, tablet, mobile). **[N]** (listed in module info, missing from the schema)
- Grid and list view toggle in a toolbar. **[N]** (listed in module info, missing from the schema)
- Toolbar with result count, the ordering dropdown, the view toggle and a per-page selector. **[N]**

### Product card
- Show or hide the title, price, image, category, rating and badge. **[S]**
- Card body: margin, padding, background, hover background, border, radius. **[S]**
- Caption background and hover; title, category, price, old-price and rating colors. **[S]**
- Image: link on or off, open in a new tab, padding, background, hover background. **[S]**
- Second image on hover (the first gallery image). **[N]**
- Add to cart: on or off, text, icon, added icon, shown always or on hover, full styling. **[S]**
- AJAX add to cart from archives (simple products), with "View cart" after adding. Variable products
  link to the product page. **[N]**
- Built-in badges: New, Out of Stock, Featured, Discount (%), each with full styling and a left, middle
  or right position. **[S]**

### Module integration
- Wishlist button: icons, colors, shown always or on hover. **[S]**
- Product Badges module: pick a badge layout. **[S]**
- Comparison and Quick View buttons: locked teasers unless Pro and the module are on. **[S]**

### Pagination
- Products per page. **[S]**
- Numbers or previous/next. **[S]** Load More (AJAX) and infinite scroll are **Pro** (see the decision below).

### More free options (found in the ShopEngine, ShopLentor and YITH Infinite Scrolling settings code)
- **Archive parts**: archive title, description, result count, view mode (grid or list), ordering
  dropdown, breadcrumbs. Each one can be turned on or off. **[N]**
- **List view settings**: its own layout (image style, content style), which parts show (category,
  rating, price, description, sale badge, add to cart, action buttons), and the image and content widths. **[N]** (ShopEngine)
- **Order of card parts**: drag to reorder the title, price, rating, category and buttons, and the order of the add-to-cart button and icons. **[N]** (ShopEngine "Product Info Order")
- **Out-of-stock visibility**: show, hide, or move to the end. **[N]**
- **Custom ordering labels** (rename the "Sort by" options). **[N]**
- **Shop page add-to-cart text** (different from the single page). **[N]**
- **A layout per category**: a different archive layout for each category. **[S]** (Display Rules)
- **Default view**: grid or list, and remember the visitor's choice (cookie). **[N]** (Grid/List Toggle)
- **Card hover effects**: lift, zoom, shadow, overlay color, border change. **[N]** (ShopLentor)
- **Quick-action icons**: wishlist, compare and quick view on the image, separately for grid and list views. **[N]** (ShopLentor)
- **"In stock" dot** with stock text on cards. **[N]** (ShopLentor)
- **Hide out-of-stock items from the grid**, and hide ratings. **[N]**
- **Subcategory display**: category, then subcategories (shop page only). **[N]** (ShopLentor)
- **Card hook point**: custom content at a chosen WooCommerce loop hook (for example `woocommerce_after_shop_loop_item_title`). **[N]** (ShopLentor)

## 2. Free: advanced
- Empty-state message and a "Continue shopping" link when nothing matches. **[N]**
- Category description and image header on category archives (on or off). **[N]**
- Subcategory tiles above products (on or off, columns). **[N]**
- Lazy-loaded images and `srcset` sizes per column count. **[N]**
- Theme override path: `yourtheme/shopglut/shop-archive/templateN.php`. **[N]**

---

## 3. Pro: core
- **Card templates 11–19**. **[O]**
- **Load More button** (AJAX), with button text, a "No more products" text, and an optional URL update. **[S]** (moves from the free schema to Pro)
- **Infinite scroll**: loads the next page when the user nears the bottom, shows a spinner, has an
  optional "Load more" fallback after N pages, and updates the URL with the History API. **[N]**
- **Separate layouts per category**: different layouts for chosen categories, decided by priority. **[S]**
  (Display Rules already support this. In Pro it becomes unlimited layouts per taxonomy; free can keep it
  too. Decide and document.)

## 4. Pro: advanced
- **Product table / list layout**: a table view with columns (image, name, SKU, attributes, price, stock,
  quantity, add to cart), bulk add to cart with checkboxes, and inline variation choice. **[N]** (benchmark: Product Table for WooCommerce)
- **Inline quantity and variation choice on cards**: swatches on the card (needs Variation Swatches Pro)
  and add variable products to cart directly. **[N]**
- **Stock indicator on cards**: "Only 3 left" text or bar. **[N]** (benchmark: ShopLentor Grid Modern)
- **Countdown on sale cards**. **[N]**
- **Promo / banner tiles**: insert a banner into the grid at position N. **[N]**
- **Image ratio control** (1:1, 3:4, 4:3, 16:9, auto) and image hover effects (zoom, swap, fade). **[N]**
- **Sticky toolbar** while scrolling. **[N]**
- **Infinite scroll settings**:
  - content, item, navigation and next-link selectors, for theme compatibility
  - loading effect and an uploaded loader image
  - on or off on mobile

  **[N]** (YITH Infinite Scrolling)
- **Product table options**:
  - choose and order the columns
  - column labels
  - instant search box and a mini filter
  - "Add selected to cart" with its texts
  - footer or floating cart
  - AJAX pagination
  - mobile behavior: automatic responsive, manual per column, or none; hide columns on mobile
  - export button

  **[N]** (Product Table for WooCommerce)
- **Ad / campaign banners** in the archive: before the shop loop or at a grid position; a title, subtitle,
  description, image, overlay, badge ("Flash sale!") and link; banner templates (hero, campaign). **[N]** (ShopLentor)
- **Countdown on archive cards** (the sale end date or a custom date and time; an "ended" message; position). **[N]** (ShopLentor)
- **Sequential product number** in the grid ("01, 02…", for editorial layouts). **[N]** (ShopLentor)
- **Editorial list view**: a magazine-style list layout. **[N]** (ShopLentor)
- **Category list / filterable product tabs** above the grid (click a category to switch the grid through AJAX). **[N]** (ShopEngine "Filterable Product List")

---

## 5. Free vs Pro decision
**Decided (2026-10-22): Load More and infinite scroll are both Pro.** Free keeps number and previous/next pagination.

To do in code:
- Remove "LoadMore Style" from the free schema's "Choose Pagination Style" (`settings/layout.php`).
- Add it back from Pro through `shopglut_layout_sections`.
- Layouts that use it fall back to numbers without Pro.

`pro_features` already says "Load more and infinite scroll pagination".

## 6. Acceptance checklist
- [ ] A layout replaces the WooCommerce loop on the shop, category, tag and search pages it targets, and nowhere else.
- [ ] The main query is respected: Shop Filters, `?orderby=` and the theme's per-page setting all work with the layout.
- [ ] Load More and infinite scroll work together with Shop Filters' AJAX results, and filter state is kept.
- [ ] AJAX add to cart updates the WooCommerce mini cart fragments and the Side Cart.
- [ ] Columns per device are correct at 375, 768 and 1280 px.
- [ ] The WooCommerce block "Product Collection" is untouched when the shortcode or block is not used.
