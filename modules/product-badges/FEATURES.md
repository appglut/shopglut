# Product Badges: Feature Spec

- **ID**: `product-badges`
- **Type**: builder. One badge per layout, unlimited layouts. Schema: `settings/badge.php`.
- **Old code [O]**: `productbadges-glut/`.
- **Benchmarked against**: YITH Badge Management, Advanced Woo Labels, Acowebs Product Labels, BeRocket
  Advanced Product Labels.

Tags: **[S]** the option exists in the schema · **[N]** new · **[O]** old code can be ported.

---

## 1. Free: core

### Badge
- Enable on or off, and "Display badge on" (shop, single product, categories, tags, chosen products). **[S]**
- Badge type: **Sale**, **New**, **Out of Stock**. **[S]**
- Free text badge type: **Custom** (any text, assigned by product, category or tag). **[N]**
  (Every competitor's free version has manual assignment.)

### Conditions per type
- **Sale**: any sale, percentage discount only, fixed discount only, or a minimum discount (percent or
  amount). **[S]**
- **New**: published within X days. **[S]**
- **Out of stock**: all product types, simple only, or variable only (variable counts as out of stock when
  every variation is out of stock). **[S]**
- Extra free conditions: **Featured**, **Low stock (≤ X)**, **On backorder**. **[N]** (YITH free has these.)

### Text variables
- `{discount_percent}`, `{discount_amount}`, `{regular_price}`, `{sale_price}`, `{stock}`, `{days_left}`. **[N]**
  For variable products, use the largest discount among the variations.

### Placement
- Display area: on the product image, or before the title. **[S]**
- Image positions: top left, center or right; bottom left, center or right. **[S]**
- Alignment before the title: left, center, right. **[S]**
- Hooks:
  - archive image: `woocommerce_before_shop_loop_item_title`
  - single image: `woocommerce_product_thumbnails`, or a wrapper over the gallery
  - title: `woocommerce_shop_loop_item_title` / `woocommerce_single_product_summary`

  **[N]**
- Works in WooCommerce product blocks (Product Collection / Product Image block) through the `render_block` filter. **[N]**
- Option to hide WooCommerce's default "Sale!" flash when a ShopGlut badge shows. **[N]**

### Style
- Text color, font size, weight, text transform. **[S]**
- Background, optional gradient. **[S]**
- Vertical and horizontal padding, radius, border width and color. **[S]**
- Shadow with color and blur. **[S]**

### More free options (found in the YITH, BeRocket, Advanced Woo Labels and Acowebs settings code)
- **Badge name** (an internal label, used for the admin list) and **Clone badge**. **[N]**
- **Text before and after** a variable, for example "Save {discount_percent}" written as Before "Save", variable, After "!". **[N]** (BeRocket)
- **Use a minus symbol** for discounts ("-25%"). **[N]**
- **Extra variables**: `{regular_price}`, `{sale_price}`, `{saving_amount}`, `{saving_percent}`,
  `{stock_quantity}`, `{total_sales}`, `{rating}`, `{product_age_days}`. **[N]**
- **More placement hooks on the single page**:
  - before or after the title, price, add to cart, meta, summary or product tabs
  - under the thumbnails
  - before or after related products

  **[N]** (BeRocket)
- **Hide badges in sidebars and widgets**. **[N]** (YITH free)
- **Hide WooCommerce's "On sale" flash**, globally or only where a badge shows. **[N]** (YITH free)
- **Show or hide WooCommerce's default "Out of stock" label**. **[N]** (Advanced Woo Labels)
- **Most badges per product and per position**; the distance between stacked badges; how several badges
  align within one position. **[N]** (Advanced Woo Labels free)
- **Translucent, light and dark presets**; opacity; letter spacing; line height; max width; z-index. **[N]**
- **Tooltip on the badge**: content, open on hover or click, delay, theme. **[N]** (BeRocket)
- **Theme compatibility selectors**: custom product, image and title CSS selectors for themes that skip
  standard hooks; disable all hooks and use JS placement instead. **[N]** (BeRocket, Advanced Woo Labels)
- **Let the shop manager edit badges** (capability). **[N]** (YITH)

### Pass 2: found in the Acowebs, YITH, BeRocket and Merchant code
- **Badge status** on or off, and **duplicate badge**. **[N]**
- **Shortcode / action hook** for placing a badge anywhere (`[shopglut_badge]`, `do_action( 'shopglut_badge' )`). **[N]** (Acowebs)
- **Exact position in pixels** (top or bottom and left or right offsets), rotation, opacity, **flip the text**
  (horizontally or vertically). **[N]** (Acowebs, YITH)
- **Hide the theme's sale badges** as well as WooCommerce's (handles the theme's custom markup). **[N]** (Acowebs)
- **Single product wrapper class** and **jQuery positioning fallback** for themes like Themify. **[N]** (Acowebs)
- **"Products where a custom badge is applied"** admin filter in the product list. **[N]** (YITH)
- **Exclusions**: exclude products, categories, tags and brands from a badge. **[N]** (Merchant)
- **Display for variations**: a badge reacts to the chosen variation (for example its sale %). **[N]** (BeRocket)
- **"Check matching products"** preview in the admin: lists which products a badge's conditions currently match. **[N]** (BeRocket)
- **Copy settings from another badge**. **[N]** (BeRocket)
- **Count-based**: show the badge only when X or fewer are left ("2 left"). **[N]** (Merchant)

## 2. Free: advanced
- Several badges on one product stack neatly (a gap, and a vertical or horizontal stack). **[N]**
- Hide on mobile, and a scale for mobile. **[N]**
- Live preview in the editor. **[S]**
- Badges are reused by Product Page and Shop & Archive templates ("Select Badge Layout"). **[S]**

---

## 3. Pro: core
- **Advanced shapes**: ribbon (corner fold), circle, corner triangle, star burst, tag, bookmark, speech bubble. **[N]**
- **Animations**: pulse, bounce, shake, swing, flash, with speed and repeat options; respect `prefers-reduced-motion`. **[N]**
- **More conditions**: categories, tags, attributes (term in an attribute), shipping class, price range, best seller (top N by sales). **[N]**
- **Priority and limits**: a priority per badge; at most N badges per position; "stop lower badges". **[N]**
- **Scheduling**: start and end date/time; days of the week (for example, only on Black Friday). **[N]**

## 4. Pro: advanced
- **Image badges**: a set of ready-made images (Black Friday, Cyber Monday, Free shipping, BOGO, 2×1,
  Christmas) plus your own upload. **[N]** (benchmarks: YITH Premium has 32, Acowebs Pro has 700+)
- **Complex rules**: AND/OR groups using product data, user data (role, logged in, country, device) and
  cart data (cart total, product in cart). **[N]** (benchmark: Advanced Woo Labels)
- **Sale countdown badge**: counts down to `date_on_sale_to`. **[N]**
- **Badge groups**: groups of badges with the most allowed per group. **[N]**
- **Free-position placement**: drag and drop or exact px/% offsets, opacity, rotation, 3D flip. **[N]**
- **Link on badge** (for example to a sale page). **[N]**
- **Per-product badge picker** on the product edit screen and in Quick Edit. **[N]**
- **Integrations**: show in the wishlist table, compare table and quick view; ACF field values in the text. **[N]**
- **Full condition list** (from the Advanced Woo Labels and BeRocket code). Each condition takes the operators
  equal, not equal, greater, less, contains and in list, combined in AND/OR groups.
  - **Product data**:
    - name, SKU, GTIN/EAN
    - price, sale discount, on sale, sale date
    - featured, stock status, stock quantity
    - product type, visibility, age, rating, total sales, views
    - has an image, has a gallery
    - category, tag, brand, attribute, custom attribute, custom field (meta), shipping class
  - **Page**: page type, template, page ID or URL, archive, language; product loop or single product.
  - **User**:
    - role, logged in, country, language, device
    - has the product in the cart, has bought the product, cart total
    - membership (WooCommerce Memberships)
  - **Date and time**: date, time, day of the week, date range.
  - **Multivendor**: sold by a vendor, vendor rating or sales (Dokan, WCFM, MultiVendorX, WC Vendors).

  **[N]**
- **Badge animation settings**: type, duration, delay, repeat count or infinite, rotation in degrees. **[N]**
- **Quantity-based and cart-based badges**: "Buy 3 get 10% off", or a percentage of the cart total. **[N]** (Acowebs)
- **Product lists**: reusable saved lists of products that badges point to. **[N]** (Acowebs)
- **Dynamic pricing badges**: link a badge to a discount or pricing rule (from Discount Rules or YITH Dynamic Pricing). **[N]** (Acowebs Pro, YITH Premium)
- **Taxonomy combinations**: category AND tag, with relations (IN, NOT IN). **[N]** (Acowebs)
- **Weekday scheduling** and a from/to date and time. **[N]** (Acowebs)
- **CSS template library** and **image template library** (seasonal, Black Friday, shipping, returns). **[N]** (BeRocket, Acowebs)
- **Custom CSS class per inner element** (`div`, `span`, `b`, `i`) for advanced shapes. **[N]** (BeRocket)
- **Has rating ≥ X** condition. **[N]** (BeRocket)
- **Page-builder awareness**: show or hide badges inside Elementor, Bricks, Breakdance and Beaver Builder product widgets. **[N]**

---

## 5. Acceptance checklist
- [ ] Sale percent is right for simple, variable (largest discount), grouped and scheduled sales.
- [ ] "New" respects the site's timezone.
- [ ] Badges show on the shop, categories, tags, the single product, related/upsell loops and block-based grids, as configured.
- [ ] Badges never break the theme's image link or layout, because they sit in an absolutely positioned wrapper.
- [ ] The default sale flash is hidden only when that option is on and one of our badges rendered.
- [ ] With 0 badge layouts, the module outputs nothing and loads no CSS.
