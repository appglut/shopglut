# Cart Page Builder: Feature Spec

- **ID**: `cart-page`
- **Type**: builder. One active layout replaces the WooCommerce cart page.
- **Templates**: Free 1–3, Pro 4–5. All five share the same schema.
- **Old code [O]**: `cartpage-glut/src/layouts/cartPage/template1–5/` (markup, style, assets).
  - Template 2 has a panel note (order note).
  - Template 3 has a shipping method selector.
- **Benchmarked against**: ShopLentor cart widgets (cart table, cross-sell, reserve timer), FunnelKit
  Sliding Cart, Freetop Free Shipping Bar, Cart Abandonment Recovery.

Tags: **[S]** the option exists in the schema · **[N]** new · **[O]** old code can be ported.

---

## 1. Free: core

### Replacing the cart
- Replaces the `[woocommerce_cart]` shortcode output. If the cart page uses the Cart block, offer to switch
  the page back to the shortcode, or render inside the block's place. **[N]**
- Empty cart state: message, "Return to shop" button, optional product suggestions (free: recent products). **[O]** (T1 has "Your cart is empty")

### Cart table
- Header row: on or off, background, text color, weight, cell padding. **[S]**
- Product image: size, background, radius, border. **[S]**
- Product title: color, size, weight, linked to the product or not. **[S]**
- Product meta (variation attributes, SKU, category): on or off, color, size. **[S]**
- Product badges (Sale, Featured) in the row: on or off, colors. **[S]**
- Quantity +/-: button colors, hover, input background, border, radius. **[S]**
  The quantity updates the cart by AJAX without an "Update cart" button, with a debounce. **[N]**
- Remove item with an undo notice. **[N]**
- Price and line total: colors, size, weight, highlighted total. **[S]**
- Table: background, border, radius, row padding, row hover. **[S]**
- Mobile: rows become stacked cards. **[N]**

### Order summary
- Section: on or off, background, border, radius, padding. **[S]**
- Header: on or off, title text, color, size, icon. **[S]**
- Rows for subtotal, shipping, tax and discount, each on or off, with label, value and spacing styling. **[S]**
- Total row: colors, size, weight, separator. **[S]**
- Shipping calculator and shipping method choice inside the summary. **[O]** (T3 shipping-select)
- Checkout button: text, colors, hover, size, padding, radius, icon. **[S]**

### Coupon box
- Section: on or off, background, border, padding, title with icon. **[S]**
- Input placeholder and styling; Apply button text and styling. **[S]**
- Success and error messages: on or off, colors, size. **[S]**
- Applied coupons listed, each with a remove (×) link. **[O]** (T1 "Applied Coupons")

### Trust and navigation
- Security badges for SSL, safe payment and returns: text, icon, colors, size; horizontal, vertical or
  grid layout. **[S]**
- Continue shopping link: text, target (shop, home, previous page, custom URL), icon, colors. **[S]**

## 2. Free: advanced
- Order note field on the cart, saved to the checkout's `order_comments`. **[O]** (T2 panel-note)
- WooCommerce cross-sells block below the table: on or off, columns, title. **[N]**
- All WooCommerce cart hooks keep firing (`woocommerce_before_cart_table`, `woocommerce_cart_collaterals`, and so on), so
  other plugins (gift cards, fees) still work. **[N]**

---

## 3. Pro: core
- **Cart templates 4–5**. **[O]**
- **Free shipping progress bar**: reads the free-shipping minimum from the WooCommerce shipping zone (or
  a custom amount), with messages for "spend {remaining} more", "you've unlocked free shipping" and
  "before the coupon". Colors, bar effect (plain, striped, animated), position (above the table or in the
  summary). **[N]** (benchmark: Freetop)
  More bar options (from the Freetop settings code):
  - **Minimum cart value before the bar shows**.
  - **Exclude shipping classes** from the subtotal used for the bar.
  - **Message texts**:
    - the announcement shown before anything is added
    - after a product is added
    - once free shipping is reached
    - when the minimum is 0
    - a version of each message per language
  - **Placeholders**: `{min_amount}`, `{total_amounts}`, `{missing_amount}`, `{total_quantity}`.
  - **Where it shows**: the cart page, the checkout page, below add to cart on single products, in the menu
    cart, or as a site-wide top or bottom bar.
  - **Display**: an initial delay; disappears after X seconds; a gift icon; links to the cart, checkout or shop.
  - **Targeting**: pages, conditional tags, mobile on or off.
  - **Cache-safe**: the message is loaded with AJAX.
  - **Detect the country by IP** to choose the right shipping zone's minimum.

  **[N]**
- **Cart upsells / cross-sells**: sources are WooCommerce cross-sells, products in the same category,
  best sellers, or a manual list. Shown as a carousel or grid with AJAX add. **[N]**

## 4. Pro: advanced
- **Reward tiers (milestones)**: several goals (free shipping, X% off, free gift) on one progress bar. Gifts
  are added to the cart automatically and removed when the total drops below the goal. **[N]** (benchmark: FunnelKit, Modern Cart)
- **Cart reserve timer**: "Your cart is reserved for 10:00", with the action when it ends (message only, or
  clear the cart). **[N]** (benchmark: ShopLentor)
- **Save cart for later / share cart**: link or email. Share this with Side Cart, which has it in free. **[N]**
- **Gift wrap / gift message** option that adds a fee. **[N]**
- **Cart abandonment capture**: logged-in user or email captured at checkout; reminder emails (see the
  Side Cart Pro spec, which owns the shared recovery engine). **[N]**
- **Free gift campaigns**: a gift product given when spending reaches X, with cart texts ("Free", "Spend
  {amount} more to get…"). **[N]** (Merchant)
- **Clear cart button** with a confirm dialog; **clear the cart after inactivity**. **[N]** (Merchant)
- **Cart reserved timer**: messages for "reserved" and "more than 1 min", and an icon. **[N]** (Merchant)
- **Show wishlist products on the cart page** ("Saved for later"). **[N]** (Merchant, MoreConvert)
- **Progress bar locations**: the cart page, checkout, mini cart, side cart, single product, and top or bottom bar. Rotating messages. **[N]** (Merchant)
- **Express checkout buttons** area (shows the Stripe or PayPal buttons the gateways already render). **[N]**

---

## 5. Acceptance checklist
- [ ] Changing the quantity, removing an item, applying or removing a coupon and changing shipping all update the totals with AJAX.
      The fragments update the header and side cart.
- [ ] Taxes, fees, multiple coupons and shipping packages show correctly (compare with the default cart).
- [ ] The cart page still works when JavaScript is off (a normal form post).
- [ ] Coupon errors from WooCommerce show inside the template's message area.
- [ ] Templates 4–5 can't be selected without Pro. An existing Pro layout falls back to template 1 when Pro is deactivated.
