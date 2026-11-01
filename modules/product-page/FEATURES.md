# Product Page Builder: Feature Spec

- **ID**: `product-page`
- **Type**: builder. Each layout uses one template and applies through Display Rules and priority.
- **Templates**: Free 1–7, Pro "Pro 1" to "Pro 10" (`shopglut-pro/modules/product-page/`).
- **Old code [O]**: `productpage-glut/src/layouts/...` (free), `productpage-glut-pro/templates/templatePro*/`.
  Each one has `templateMarkup.php`, `templateStyle.php`, `templateMarkup-frontend.js` and `ModuleIntegration.php`.
- **Benchmarked against**: ShopEngine, ShopLentor, Product Gallery Slider.

Tags: **[S]** the option exists in the schema · **[N]** new · **[O]** old code can be ported. See `../../MODULE-FEATURES.md`.

---

## 1. Free: core

### Layouts and targeting
- Template picker with preview images for templates 1–7. **[S]**
- Display Rules: all products, chosen categories, chosen products, and exclusions. **[S]**
- Priority decides which layout wins when several match. **[S]**
- Active or draft status, duplicate layout, live preview in the editor. **[S]**
- Fallback to the theme's default product page when no layout matches. **[N]**
- Works with classic themes (replaces `woocommerce_single_product_summary` output and the template) and
  block themes (renders through a `single-product` block template). **[N]**
- Supports simple, variable, grouped and external products. **[N]**
- Remove the old "Overwrite All / Specific Products" fields, because Display Rules replace them. **[N]**

### Gallery
- Main image: background, border, radius, padding and bottom margin. **[S]**
- Image fit: cover, contain or fill (T1, T3). **[S]**
- Thumbnail strip: on or off, alignment, size, spacing, border, active and hover border, inactive opacity. **[S]**
- Lightbox on click and cursor style (T1). **[S]**
- Hover zoom with zoom level (T1). **[S]**
- Hover scale or brightness, shimmer, shadow (T1). **[S]**
- Variation image switching: picking a variation changes the main image. **[N]**
- Swipe on touch devices. **[N]**

### Summary
- Title: color, size, weight. **[S]**
- Rating: on or off, star color, text color, size. **[S]**
- Price: current and original price, background, discount badge colors (T3–T7). **[S]**
- Short description: on or off, color, size, line height. **[S]**
- Breadcrumb with separator and colors (T2). **[S]**
- Product meta: SKU, categories, tags and their styling (T2). **[S]**
- Stock status text (in stock, low stock, out of stock, backorder). **[N]**

### Purchase box
- Quantity +/- buttons and their styling. **[S]**
- Add to cart button: colors, hover, radius, size, weight. **[S]**
- Buy Now button that adds the product and goes to checkout, with full styling and a divider (T2). **[S]**
- Attribute display: horizontal, vertical or grid; buttons or dropdown; tooltips; unavailable options
  (opacity); required asterisk (T4–T7). **[S]**
- AJAX add to cart on the single product page, with an option to fall back to a normal form post. **[N]**
- Grouped and external product forms are styled as well. **[N]**

### Content sections
- Product tabs: repeater with icon, title and content; colors for normal, hover and active (T1). **[S]**
  Built-in tab sources: Description, Additional information, Reviews, and custom HTML. **[N]**
- Description section and review section with review cards, avatar, stars and a styled review form (T3). **[S]**
- Features or benefits grid: repeater with icon (Font Awesome or image), title, description and link;
  1–6 columns; full styling (T1). **[S]**
- Related products: on or off, title, products per row (2–4), card styling, quick-add button. **[S]**
- Social share: repeater with icon, label and colors (T2, T3). **[S]**
- Per-template switches to show or hide each element (T3). **[S]**

### Module integration (each option shows only when that module is on)
- Wishlist button with position. **[S]**
- Product Badges: pick a badge layout and its position. **[S]**
- Variation Swatches: replace the dropdowns, or show swatches after them or before add to cart. **[S]**
- Comparison and Custom Fields options appear as locked teasers until Pro and the module are active. **[S]**

### More free options (found in the ShopLentor and ShopEngine settings code)
- **Rename labels**:
  - the Add to Cart text (single page and shop page separately)
  - the Description, Reviews and Additional Information tab titles
  - the default active tab
  - the gap between tabs

  **[N]** (ShopLentor "Rename Label")
- **Hide "View cart"** after an AJAX add; **popup notice after add to cart** (with position, style and icon). **[N]**
- **Change the AJAX variation threshold** (more than 30 variations). **[N]**
- **Disable the variation form for out-of-stock products**. **[N]**
- **Stock text**: custom "In stock" and "Out of stock" texts. **[N]**
- **Image view position** (gallery on the left or right) and a **lightbox zoom icon**. **[N]** (ShopEngine)
- **Product meta block**: SKU, categories, tags and brand, each on or off. **[S]/[N]**
- **Notice block** (a store notice on the product page, with an icon and style). **[N]**


### Sticky add-to-cart bar (Free, by decision 2026-10-22)
- **Sticky add-to-cart bar**: appears once the main button scrolls out of view. Shows image, title, price,
  variation picker and quantity. Choice of top or bottom, background color, show on mobile or desktop. **[S]** (Pro 1–3) / **[N]** (others)
- **Sticky add-to-cart details** (WPC, all free):
  - top or bottom position
  - **offset from the top** before it shows, and **offset from the bottom** where it hides
  - show the price and quantity
  - **show Buy Now, Wishlist, Compare and Quick View buttons** in the bar
  - variable products: a full variations form in the bar, or scroll up to the form
  - turn it off for chosen product types or for products that can't be bought

  **[N]**

## 2. Free: advanced
- Built-in text badges such as New, Trending, Best Seller, Hot, Sale and Limited, with their own text and
  colors (T4–T7). **[S]**
  - Rule: "New" uses the date the product was published (X days), "Best Seller" uses `total_sales` rank, "Sale" uses `is_on_sale`. **[N]**
- Schema.org Product markup stays intact: don't strip WooCommerce's structured data. **[N]**
- Responsive controls: the layout stacks under a breakpoint, and the gallery moves above the summary. **[N]**
- Theme override path: `yourtheme/shopglut/product-page/{template}/markup.php`. **[N]**

---

## 3. Pro: core

### Pro templates
| Template | Style or focus (from the old markup) | Distinctive parts |
|----------|--------------------------------------|-------------------|
| Pro 1 | Premium general | Zoom, lightbox, thumbnails on the left/right/bottom, animated badges |
| Pro 2 | Electronics, long-form | Highlights list, payment-method icons, shipping and returns tabs, Ask a Question, Buy Now, countdown |
| Pro 3 | Featured deal | "Featured deal" banner, review highlights, "Discover more" carousel |
| Pro 4 | Accessory-rich | Key features, trust row (warranty, returns, free shipping), delivery times, accessory list |
| Pro 5 | Support-focused | Tabs for support, returns, warranty and shipping; Ask a Question; Buy Now |
| Pro 6 | Slider gallery | Autoplay image slider (start, stop, go to slide), additional-info tab, Buy Now |
| Pro 7 | Books and media | Book details table, about the author, related books |
| Pro 8 | Conversion | Slide gallery, "Why choose us", Buy Now and direct checkout, quick view of related products |
| Pro 9 | Bundle | Frequently bought together (bundle add to cart), color and size selectors, recently viewed, help box |
| Pro 10 | Media | Product video playback in the gallery, zoom, quick view of related products |

- Port each template's markup, style and JS. **[O]**
- Bring the Pro 4–10 schemas up to the parts each template actually renders (tabs, reviews, related
  products, extra blocks). **[N]**

### Pro features (available in every template through layout sections added by Pro)
- **Countdown timer**: uses the sale end date (`date_on_sale_to`) or a custom date. Position before price,
  after price or before add to cart. Hides itself when it ends. **[S]**
- **Stock progress bar**: "Only X left" with a bar based on stock compared with a set starting amount,
  with bar color. **[S]**
- **Advanced gallery**: thumbnails on the left, right or bottom, zoom level, lightbox, badge animation
  (pulse, bounce, shake). **[S]**
- Wishlist and compare buttons with four positions each. **[S]**

## 4. Pro: advanced
- **Product video in the gallery**: a YouTube, Vimeo or MP4 URL per product, shown as the first or last slide. **[O]** (Pro 10)
- **Frequently bought together / bundle**: pick products per product (or use upsells), choose them with
  checkboxes, show a combined price, add all to cart with one AJAX request. **[O]** (Pro 9)
- **Ask a Question**: a form that emails the store (product link included), with optional storage as a
  private comment. **[O]** (Pro 2, 5)
- **Recently viewed products**: list kept in a cookie or local storage, shown as a carousel. **[O]** (Pro 9)
- **Size chart**: a popup per product or per category, built from a table editor. **[N]** (benchmark: ShopLentor)
- **Delivery estimate**: "Order within X hours to get it by DATE", with cut-off time and days off. **[N]**
- **Sales urgency**: "X people are viewing" and "Y sold in the last 24 hours" (real sales data, with an
  option to turn it off). **[N]**
- **Trust badges and payment icons** block. **[O]** (Pro 2, 4)
- **Variation gallery**: several images per variation (stored in variation meta). **[N]** (benchmark: Product Gallery Slider)
- **360° spin** image set. **[N]**
- **Per-product layout override**: a meta box on the product edit screen to pick a layout. **[N]**
- **Countdown details**: the end date or time (the sale end date or a custom one), a title, a message when
  it ends, a style, and an "offer price" countdown. **[N]** (ShopLentor)
- **Stock progress details**: the starting stock amount the bar is measured against, and "sold vs available" labels. **[N]**
- **Backorder limit** and **pre-order** labels and availability date. **[N]** (ShopLentor Backorder, ShopEngine Pre-Order)
- **Partial payment / deposit**: pay a percentage now. This is complex, so it's a later phase. **[N]** (ShopLentor Pro)
- **Product FAQ** accordion block. **[N]** (ShopLentor)
- **QR code** for the product URL. **[N]** (ShopLentor)
- **Social share** with a copy-link button. **[N]**

### Pass 2: found in the dedicated feature plugins (WPC Sticky, WPC Buy Now, WPC FBT, Custom Product Tabs, Countix, Size Charts)
- **Buy Now details** (WPC):
  - position on the single page and on archives
  - **what happens**: go to the cart, the checkout, a custom page, an instant checkout popup or the side cart
  - **reset the cart first** (buy only this product)
  - **hide the normal add-to-cart button**
  - limit to chosen categories

  **[S]/[N]**
- **Frequently bought together details** (WPC FBT):
  - products set per product, or by rules (same category, cross-sells, conditions)
  - layouts: list, compact, grid, carousel with 2–4 columns
  - checked by default, a default quantity per item, "same product can be added again"
  - **extra discount** when bought together
  - text above and below
  - a counter on the add button
  - **add all at once or separately**; price and image change as items are ticked
  - a separate mobile layout

  **[N]**
- **Saved / global tabs** (Custom Product Tabs): reusable tabs that can be added to many products, overridden
  per product, assigned by category or tag (Pro), added in bulk edit, with a `the_content` filter toggle. **[N]**
- **Countdown details** (Countix):
  - time formats: `01:02:03:04`, `01d 02h`, long or short units, separators
  - **evergreen countdown** (resets per visitor)
  - action when it ends
  - a **progress bar** of the quantity sold against an initial quantity
  - **step discount** (the discount changes every X hours)
  - sticky top or bottom banner
  - a checkout countdown offer
  - flip animation (Pro)

  **[N]**
- **Size chart details** (Size Charts):
  - a chart table editor (rows and columns) with table styles
  - assign by product, category, tag or **attribute**
  - show as a popup button or an **additional tab**, with a label position and popup size
  - a "How to measure" section with an image
  - templates (clothing, shoes)
  - **country-based charts** (US, EU, UK)
  - clone a chart

  **[N]**
- **Gallery slider** (Product Gallery Slider): slide direction, zoom icon, custom image sizes, extra images per variation. **[N]**
- **Pre-orders** (Merchant):
  - a pre-order button text
  - an availability date and where the info shows
  - an optional discount for pre-ordering
  - allow only pre-orders in a cart, and create a **separate order for each pre-order product**
  - show it on backorders
  - exclude products, categories, tags or brands

  **[N]**
- **Back-in-stock signup** on out-of-stock products ("Email me when this item is back in stock"). **[N]** (Merchant)
- **Animated add-to-cart button** (shake or pulse after X seconds, or on hover). **[N]** (Merchant)
- **Out-of-stock add-to-cart label** (change the button text when the product is out of stock). **[N]** (Merchant)
- **Product bundles**:
  - a title and a "Bundle and save" price
  - show bundled thumbnails, descriptions and quantities
  - a price range for variable bundles
  - hide bundled products in the cart, mini cart and email

  **[N]** (Merchant)
- **Trust badges / payment logos** under add to cart (with a display condition: all products or chosen ones). **[N]** (Merchant)
- **Auto external links**: external products open in a new tab. **[N]** (Merchant)
- **Display conditions** for each conversion block (countdown, stock bar, trust badges): all products,
  chosen products or categories, discounted products only. **[N]** (Merchant)

---

## 5. Acceptance checklist
- [ ] Each free template renders the same on the front end as its admin preview, for simple, variable,
      grouped, external and out-of-stock products.
- [ ] Display Rules and priority pick the correct layout; with no match, the theme's own page shows.
- [ ] Variable products: swatches, the price, the variation image and stock all update when a variation is picked.
- [ ] Add to cart and Buy Now work with AJAX on and off, and WooCommerce notices still show.
- [ ] Reviews, structured data and WooCommerce hooks (`woocommerce_before_single_product`, and so on) still fire.
- [ ] Turning a Pro feature on with Pro inactive does nothing (no PHP errors, no output).
- [ ] Mobile: the layout stacks, the gallery can be swiped, the sticky bar doesn't cover the cookie or chat area.
