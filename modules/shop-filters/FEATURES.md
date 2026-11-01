# Shop Filters: Feature Spec

- **ID**: `shop-filters`
- **Type**: builder. Each layout is one filter panel (a repeater of filters). Schema: `settings/filter.php`.
- **Old code [O]**: `filterglut/src/enhancements/Filters/` (the newer copy; `shopfilter-glut` is older).
- **Benchmarked against**:
  - HUSKY / WOOF (80k)
  - YITH Ajax Product Filter (70k): the free version has AJAX, the price slider, attributes and a mobile modal
  - Filter Everything (50k)
  - Product Filter by WBW (50k)

Tags: **[S]** the option exists in the schema · **[N]** new · **[O]** old code can be ported.

---

## 0. Known problems to fix first
- Only **Product Categories** and **Product Tags** are enabled in `$shopglut_shopg_filter_options`.
  These types are commented out, although their appearance options already exist in the schema:
  - Price
  - Rating
  - Search
  - Sort by
  - Author
  - Status (in stock, out of stock, on sale, discounted, purchased)
  - Product type
  - Order direction
  - Product view
  - Shipping class
- The loop that adds **product attributes** (`pa_*`) as filter types is commented out too.
- For Categories and Tags, only the *checkbox* and *radio* designs are enabled. These are commented out:
  color-button, dropdown, grid, button, cloud, color, image, tree-view, icon.
- Per-term colors and images are now stored as `value[term_id]`. The filter markup has to be adapted
  (already noted in `SHOPGLUT-FREE.md`).

---

## 1. Free: core

### Filter types (all free)
| Type | Designs | Notes |
|------|---------|-------|
| Categories | checkbox, radio, dropdown, button, cloud, color, image, tree (hierarchy), icon, grid | include, exclude or all; per-term color and image **[S]** |
| Tags | same as categories | include, exclude or all **[S]** |
| Attributes (each `pa_*`) | checkbox, radio, dropdown, button, color, image, label | uses Variation Swatches term colors and images when present **[N]** |
| Price | range slider (6 skins: flat, big, modern, sharp, round, square), radio ranges, checkbox ranges | step, prefix/postfix, prettify numbers, thousand separator, grid, keyboard, force edges, drag interval, custom ranges **[S]** |
| Rating | stars, checkbox, radio, slider, dropdown | show count **[S]** |
| Stock / status | in stock, out of stock, on sale, discounted, purchased before | **[S]** |
| Shipping class | 8 designs, per-term color and image | **[S]** |
| Product type | simple, variable, grouped, external | **[S]** |
| Author / vendor | 8 designs | **[S]** |
| Search by text | title, content, excerpt and combinations; full or partial word; placeholder | **[S]** |
| Sort by | the chosen orderby options | **[S]** |
| Order direction / product view | asc/desc, grid/list | **[S]** |
| Brand (`product_brand`, WooCommerce 9.6+) | as categories | **[N]** |

### Panel behavior
- Filter modes:
  - Multi-select with a Submit button
  - Multi-select with an Apply button
  - **Select and AJAX filter** (instant)

  **[S]** (AJAX is free; see the decision below.)
- AND/OR logic for multi-select. **[S]**
- Panel position: left sidebar, right sidebar, horizontal above the shop. **[S]**
- Accordion or normal titles, with expand and close icons, title icon, border, colors. **[S]**
- Show product counts per term. **[S]**
- Hide out-of-stock products from the results. **[S]**
- Hide the panel on chosen devices (mobile, tablet, desktop). **[S]**
- Products per page; result pagination by numbers (AJAX). **[S]** Load More and infinite scroll are **Pro** (see the decision below).
- Loading effect: none, image, or text, with a border. **[S]**
- Apply and Reset button texts. **[S]**
- Show on chosen pages (shop, categories, tags, custom pages). **[S]**
- Placement:
  - a widget
  - a Gutenberg block
  - the shortcode `[shopglut_filter id="ID"]`
  - automatic injection on the shop (`woocommerce_before_shop_loop`, or a sidebar position)

  **[N]**

### Query
- Filters change the main WooCommerce product query (`woocommerce_product_query`) with `tax_query`
  and `meta_query`, and price through the `wc_product_meta_lookup` table. **[N]**
- Results replace the product list, result count and pagination, and work with Shop & Archive layouts and
  theme loops. **[N]**
- Filter state lives in the URL as query args (`?filter_pa_color=red,blue&min_price=10`), so pages can be
  shared and the Back button works. **[N]**

## 2. Free: advanced
- **Adaptive filtering**: terms that would return 0 products are hidden or disabled, and counts are
  recalculated for the current selection. **[N]** (YITH free hides them)
- A "Reset all" link, and a reset per filter. **[N]**
- Scroll to the top of the results after filtering (on or off). **[N]**
- Variation-aware stock filter: a variable product matches only when a matching variation is in stock. **[N]**
- Term ordering: by name, count, ID or manual order. **[N]**
- Collapsed or open by default for each filter. **[N]**
- Tooltip on terms. **[N]**

### More free options (found in the HUSKY, WBW, Filter Everything and YITH settings code)
- **Theme container selectors**: CSS selectors for the product container, result count, pagination and
  "scroll to top" anchor, so AJAX works with any theme. **[N]** (YITH)
- **"Try to AJAXify the shop"** fallback: a full-page fetch that swaps the selectors. **[N]** (HUSKY)
- **Autosubmit** on change, as opposed to an Apply button. Free mode also has **instant recount in the browser** for Apply-button mode. **[N]** (Filter Everything)
- **Reset button**: its position (above the products, or above or below the filters) and what it does
  (reset everything or only this panel). **[N]** (YITH, HUSKY)
- **Term display**:
  - show hierarchy: expanded, collapsible, or children only after the parent is picked
  - hide children
  - checked terms move to the top
  - "More/Less" after N terms
  - a search box inside long term lists
  - display terms in a row or in N columns

  **[N]**
- **Term sorting**: by name, slug, count, ID or manual order, ascending or descending. **[N]**
- **Taxonomy custom label** and **slug alias** used in URLs (`?color=` rather than `?filter_pa_color=`). **[N]** (HUSKY, Filter Everything "Var Name for URL")
- **Filter section max height** with its own scroll. **[N]**
- **Price filter**:
  - slider text inputs (type a min and max)
  - range list
  - include tax in the slider
  - a prettified thousands separator

  **[N]**
- **Rating filter**: an "All stars and up" mode. **[N]** (Filter Everything)
- **Search filter**:
  - autocomplete with product suggestions and counts
  - a minimum number of characters
  - search by SKU, inside variations and in variation descriptions
  - full-word-only option

  **[N]** (HUSKY)
- **Date filter** (published date with a date picker). **[N]** (HUSKY)
- **Mobile**: move the sidebar above the products; collapse the widget behind a "Filters" button. **[N]** (Filter Everything and WBW free)
- **Wait cursor and overlay** (dark or light) while AJAX runs; smart auto-scroll. **[N]**
- **Hide WooCommerce's default ordering dropdown** when a Sort By filter is present. **[N]**
- **"No products found" message** setting. **[N]**
- **WCAG mode**: accessible markup (fieldset and legend, labelled inputs). **[N]** (WBW)
- **SEO basics in free**: add `nofollow` to filter links; robots meta (noindex) on filtered pages; add
  Disallow rules for filter URLs to `robots.txt`. **[N]** (YITH, Filter Everything, WBW)
- **Duplicate / clone a filter panel**; import and export. **[S]/[N]**
- **Clear filter transients** button and **debug mode**. **[N]**

### Pass 2: found in the BeRocket AJAX Filters, WCAPF and WBW code
- **Setup wizard**: pick a store type and create a starter filter set in one click. **[N]** (BeRocket, WBW)
- **Auto-detect selectors** ("Run auto-selector") for the theme's product list, pagination and count. **[N]** (BeRocket)
- **Filter groups**: several filters placed together, with a separate **mobile group** and its own location
  (above the products on mobile vs the sidebar on desktop). **[N]** (BeRocket)
- **Visibility per filter**: always visible, collapsed on page load, always hidden (still applied), shown on
  desktop only, mobile only, or both. **[N]** (BeRocket, WCAPF)
- **Widget tooltip / description** and an extra CSS class for each filter. **[N]**
- **Options**:
  - "All items" option with a label
  - a search field inside the options list
  - "+ Show more" after N
  - count shown inline or in a tooltip
  - **checked items in bold** and **moved to the top**
  - **single select only** option

  **[N]** (WCAPF, WBW)
- **Number and range settings**: auto-detect the min and max, decimal places, decimal separator, step,
  gap between ranges, how slider values show; meta value type (number, decimal, date, datetime). **[N]** (WCAPF)
- **Manual options** for custom-field filters: key, operator (=, !=, >, >=, <, <=) and value. **[N]** (WCAPF)
- **Taxonomy scope**: child of a term, direct children only, child terms only; **always show every term**
  regardless of the current category. **[N]** (WCAPF, WBW)
- **"Check page category / tag"**: on a category page, filter only within it. **[N]** (WBW)
- **Rating filter texts**: "and up", plus extra texts for 1–4 and for 5 stars. **[N]** (WBW)
- **Rename stock status labels**. **[N]** (WBW)
- **Alphabetical index** (A–Z jump) for long lists such as brands. **[N]** (WBW)
- **Size presets** for color and image swatch filters (1em to 5em). **[N]** (BeRocket)
- **Slider skins**: big, circle, compact. **[N]** (WBW)
- **Disable individual scripts** (range slider, tooltip) to avoid conflicts. **[N]** (WCAPF)
- **"Empty filter" text** when a filter has no options. **[N]**
- **Sort-by dropdown as a ComboBox** with "then sort by" (a secondary sort). **[N]** (WCAPF)
- **Author filter**: include or exclude authors, author roles. **[S]/[N]**

### Load More / infinite scroll (for the result pagination): PRO, by decision
- Modes: load more button, infinite scroll, or load more plus AJAX pagination; an optional previous button. **[N]** (BeRocket Load More)
- **Buffer pixels** (how far from the bottom loading starts), "No more products" text, update the URL or not. **[N]**
- Selectors found automatically: products, item, pagination and next page. **[N]**

---

## 3. Pro: core
- **Off-canvas mobile filter drawer**: a "Filter" button opens a full-height drawer with sticky
  Apply and Reset, and a result count on the Apply button. **[N]**
- **Active filter chips**: the chosen terms appear as removable chips above the products (or in the
  panel), with "Clear all". **[N]**
- **SEO-friendly filter URLs**:
  - pretty permalinks such as `/shop/color-red/size-xl/`
  - indexable landing pages for chosen combinations, with a custom title, meta description, H1 and text
  - canonical and `noindex` rules for everything else

  **[N]** (benchmark: Filter Everything Pro)

## 4. Pro: advanced
- **Unlimited filter sets per page and context** (different panels for different categories), with priority. **[N]**
- **Horizontal filter bar with dropdown panels** (top bar style). **[N]**
- **Step-by-step filtering** (a guided wizard: pick category → size → color). **[N]**
- **Keep unavailable terms visible but not clickable**, rather than hiding them. **[N]**
- **Filter by custom fields / meta** (ACF, `_` meta, numeric ranges). **[N]**
- **Filter by sale date, new arrivals (X days), featured**. **[N]**
- **Lazy-load filter terms** and AJAX paging of long term lists. **[N]**
- **Filter analytics**: the most used filters and terms, and searches with no results. **[N]**
- **Conditional filters**: show a filter only after another one has a value (for example, show Size after a Category is picked). **[N]** (HUSKY "Conditionals", Filter Everything "Parent filter")
- **Filter by role**: different panels for different user roles. **[N]**
- **Redirect mode**: filtering from any page sends the shopper to the shop with the filters applied. **[N]**
- **Set filters automatically on category pages**: the current category is pre-selected. **[N]**
- **Filter by vendor** (Dokan, WCFM) and by **B2B prices**. **[N]**
- **Index schedule**: rebuild the index on a schedule, turn off reindexing on product save for bulk imports,
  stock-status indexing for grouped and bundle products, and clear the WP Rocket cache after reindexing. **[N]** (WBW)
- **Pre-computed index table** for large catalogs: a `{prefix}shopglut_filter_index` table rebuilt by cron and on product save. **[N]**

---

## 5. Free vs Pro decision
**Decided (2026-10-22):**
- **Free**: AJAX filtering (the "Select And Ajax Filter" mode), and number pagination of the results.
- **Pro**: **Load More and infinite scroll** of the filter results, the mobile drawer, chips, SEO URLs and everything in section 4.

To do in code:
- Remove `loadmore` and `infinite` from the free schema's "Filter Result Pagination" options (`settings/filter.php`).
- Add them back from Pro through `shopglut_layout_sections`.
- An existing layout that has Load More or infinite selected falls back to numbers when Pro is inactive.

`pro_features` is updated.

## 6. Acceptance checklist
- [ ] Each type returns the same products as an equivalent WP_Query (test AND/OR and several filters together).
- [ ] The price range respects tax display settings and works with the lookup table on 10k+ products in under 300 ms.
- [ ] URLs reproduce the state after a reload, and the Back/Forward buttons restore it.
- [ ] Works with Shop & Archive layouts, theme loops and WooCommerce's Product Collection block.
- [ ] Counts update for the current selection when adaptive filtering is on.
- [ ] No N+1 queries: term counts are batched and cached as transients, keyed by the query hash.
