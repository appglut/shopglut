# Variation Swatches: Feature Spec

- **ID**: `product-swatches`
- **Type**: builder. Each layout is a "swatch style" and can be limited to chosen attributes (`display_fields`).
- **Templates**: Free 1–10, Pro 11–30.
- **Old code [O]**: `productswatches-glut/src/enhancements/ProductSwatches/`
  - template1 (dropdown) and template2 (buttons) are real.
  - `AttributeSwatchesManager.php` handles layout-to-attribute assignment and global settings.
  - `dataManage.php` handles AJAX add to cart and variation data.
- **Benchmarked against**: Variation Swatches for WooCommerce by StorePress (300k), Variation Swatches by
  CartFlows (200k), WCBoost Variation Swatches (50k).

Tags: **[S]** the option exists in the schema · **[N]** new · **[O]** old code can be ported.

---

## 0. Known problems to fix first
1. **Templates 3–30 are not swatch templates.** Their settings files (free `template3–10.php` and Pro
   `template11–30.php`) are copies of the *product page* Template 1 schema (gallery, tabs, related products).
   The old plugin's markup files for 3–30 are placeholders too. Each needs a real swatch design and schema (see 3).
2. **There is no per-term data.** Nothing stores a color, image or label per attribute term. Without it,
   color and image swatches can't work. Build section 1.1 first.

---

## 1. Free: core

### 1.1 Attribute and term setup (foundation)
- Add swatch types to WooCommerce attributes (`product_attributes_type_selector`): **Select**,
  **Color**, **Image**, **Button/Label**, **Radio**. **[N]**
- Term edit screens (`{taxonomy}_add_form_fields` and `_edit_form_fields`), saved with `update_term_meta`:
  - Color: a color picker. **[N]**
  - Image: a media picker. **[N]**
  - Label: a short text, such as "XL". **[N]**
  - Tooltip text. **[N]**
- Swatch preview column in the term list table. **[N]**
- Auto-detect the type: if there's no type, use a button for text and match color names like "Red" to a
  hex value with a local name table (no external API). **[N]** (benchmark: StorePress uses an external
  colors API that has to be switched on; ours stays offline.)
- Values are included in WooCommerce product CSV export and import (term meta). **[N]**

### 1.2 Rendering on the single product page
- Replaces the `<select>` of `woocommerce_dropdown_variation_attribute_options_html`, keeping the hidden
  select so WooCommerce's variation JS keeps working. **[N]**
- **Template 1, dropdown**: styled select with container, typography, focus, hover and arrow styling;
  label position and alignment. **[S]**
- **Template 2, buttons**: label position, default, hover and active button states, padding, min size. **[S]**
- **Templates 3–10**: the remaining free designs (see 3). Suggested set:
  - 3 round color
  - 4 square color
  - 5 image thumbnail
  - 6 pill label
  - 7 radio list
  - 8 color with label
  - 9 image with label
  - 10 underline/minimal

  **[N]**
- Selected-value name shown next to the attribute label ("Color: Red"). **[N]**
- Tooltip on hover (text, or the term's image). **[N]**
- **"Overwrite WooCommerce Variations"** switch per style. **[S]**
- **Out-of-stock and unavailable combinations**: hide, blur, cross out or disable, reacting to choices
  already made in other attributes. **[N]**
- Clear/reset selection link, with its own styling. **[O]** (old global `clear_button_*` settings)
- Selected variation price shown under the swatches, with its own styling. **[O]** (old global `price_*` settings)
- Works with custom (non-taxonomy) product attributes: they fall back to button style. **[N]**

### 1.3 Assignment
- A style applies to chosen attributes. Leave it empty to apply to all (`layout_matches`). **[S]**
- When several styles match, priority decides; when none match, the default WooCommerce dropdown shows. **[S]**

### More free options (found in the StorePress, CartFlows and WCBoost settings code)
- **Global settings**:
  - **Enable swatches**; **enable the default stylesheet** (so themes can style swatches themselves).
  - **Convert every dropdown to buttons**; **convert to images** when the variation has an image.
  - **Shape**: circle, rounded (3px), square, or custom width and height.
  - **Default size** (width, height, font size) for single product pages.
  - **Tooltip** on or off.
  - **Disabled / out-of-stock style**: blur, blur with a cross, or hide; the cross color; out-of-stock items
    clickable or not.
  - **Selected indicator**: the tick color and indicator color.
  - **Clear on reselect**: clicking the selected swatch again deselects it.
  - **Show the selected attribute name** next to the label, with a separator.
  - **Alignment** on the single product page.
  - **Show the variation's stock** under the swatches.
  - **Preloader** on or off.
  - **Clear the swatch transient cache** (a tools button).

  **[N]**
- **Per-attribute settings** on the attribute edit screen: type, shape, size and width/height, used instead
  of the global settings. **[N]** (CartFlows)
- **Invalid combinations**: hide, blur or show them. **[N]** (WCBoost)

### Pass 2: found in the Woosuite, WPC, CartFlows, StorePress and WCBoost code
- **Swatch image ratio**: 1:1, 4:3, 3:4, 16:9, 9:16, 2:3, 3:2, 4:5, 5:4. **[N]** (Woosuite)
- **Label inside the swatch**, an attribute name and description shown above it, and a label position (inline or stacked). **[N]**
- **Item styling by state**: normal, hover and selected (border, background, scale). **[N]**
- **Tooltip settings**: width, maximum width, line height, position (top, bottom, left, right), image
  preview inside the tooltip, and a choice of tooltip library (CSS-only or JS). **[N]** (Woosuite, WPC)
- **Unavailable styles**: blur, blur with a cross, blur with a line through, blur with a slash, hide, or ignore.
  Also an option to **turn off the availability check** for speed. **[N]** (WCBoost, Woosuite)
- **Second click undoes the selection**. **[N]** (WPC)
- **Layout on the single product page**: table (default WooCommerce), stacked, or inline. **[N]** (WPC)
- **Restore attribute types** when the plugin is turned off: a tool that sets every attribute back to "select". **[N]** (Woosuite, WCBoost)
- **Choose which attributes use image or radio types** in bulk. **[N]** (Woosuite)
- **Regenerate thumbnails** reminder when the swatch image size changes. **[N]** (StorePress)

- **Swatches for non-variable products** (display only, from attributes). **[N]** (WPC) *(moved from Pro: free in competitors)*

## 2. Free: advanced
- Swatch size, shape (circle, rounded, square), border, selected ring, spacing. **[N]**
- Keyboard and screen reader support: the swatch group is a `radiogroup`, each swatch a `radio`, with arrow-key navigation. **[N]**
- Works inside Quick View popups and in the Product Page Builder's "Swatches position" option. **[S]**
- AJAX variation loading for products with more than 30 variations (WooCommerce's `woocommerce_ajax_variation_threshold`). **[N]**

---

## 3. Pro: core
- **Swatch templates 11–30**: real designs. For example: bicolor diagonal, image with zoom, large
  image cards, color with stock count, pills with price delta, square with checkmark, gradient, pattern,
  outline, emoji/icon, chips with an "×" to clear, vertical list with thumbnails, and more. Each has its
  own schema (sizes, shapes, states, label, tooltip). **[N]**
- **Swatches on shop and archive cards**:
  - Choose which attribute(s) to show.
  - Limit how many show, with a "+N more" link.
  - Hovering or clicking a swatch changes the card image.
  - Optional add-to-cart from the card once every attribute is chosen.

  **[N]** (benchmarks: StorePress Pro, WCBoost Pro)
- **Bicolor / multicolor swatches**: a second color per term, drawn as a diagonal split. **[N]**
- **Image swatch zoom**: a larger preview on hover. **[N]**

## 4. Pro: advanced
- **Per-product overrides**: a "Swatches" tab in the product edit screen changes the type, color, image and
  shape per product and per term. **[N]** (benchmark: StorePress Pro)
- **Variation gallery change**: picking an attribute (for example Color) changes the gallery before every
  attribute is chosen. **[N]**
- **Direct variation links**: `?attribute_pa_color=red` preselects the swatch, and the URL updates on selection. **[N]**
- **Remaining stock display** per swatch ("Only 2 left"). **[N]**
- **Highlighted attribute** shown at a larger size. **[N]**
- **Swatch filter widget**: shares term colors and images with Shop Filters' color and image designs. **[N]**
- **Image tooltip** on archive swatches. **[N]**
- **Archive settings**:
  - show swatches on the shop page
  - **show a single attribute** only, or a chosen one ("catalog mode attribute")
  - **attribute display limit** with a "+N more" link
  - **show the default selected swatch**
  - archive alignment and size
  - **preloader**

  **[N]** (StorePress Pro)
- **Large first attribute** on the single page; **show the attribute image on the first attribute**. **[N]** (StorePress Pro)
- **Switch the variation image when a single attribute is selected**. **[N]**
- **Generate a variation URL** that can be shared. **[N]**
- **Swatches in the filter widget** (shared with Shop Filters). **[N]**
- **Archive swatch position**: before or after the title, before or after the price, before or after add to
  cart; alignment; a label on the shop page. **[N]** (CartFlows, Woosuite)
- **Change the product link** on archive cards when a term is chosen (it links to the preselected variation). **[N]** (WPC)
- **Term groups**: group terms under headings, for example "Warm colors" and "Cool colors". **[N]** (WPC, StorePress Pro)
- **Per-product swatch settings override**, alongside the global and per-attribute settings. **[N]**
- **Mixed / special attribute types**: an image swatch whose label is a color, and similar combinations. **[N]** (StorePress Pro)
- **Buy Now from archive swatches**. **[N]** (StorePress Pro)

---

## 5. Acceptance checklist
- [ ] Color, image, label and radio types can be set on attribute terms, and they survive export and import.
- [ ] The hidden select stays in sync: add to cart, price, stock and image update exactly as with the default dropdowns.
- [ ] Unavailable combinations update after each choice, and "Clear" resets everything.
- [ ] Products with more than 30 variations still work with AJAX variation lookup.
- [ ] Without any style assigned, the default dropdowns render (no broken markup).
- [ ] Archive swatches (Pro) work with Shop & Archive templates, Shop Filters' AJAX results and infinite scroll.
