# Thank You Page Builder: Feature Spec

- **ID**: `order-complete`
- **Type**: builder. Layouts replace the WooCommerce "Order received" page.
- **Templates**: Free 1. Pro adds more (see 3).
- **Old code [O]**: `ordercomplete-glut/src/layouts/orderCompletePage/templates/template1/` and a
  `woocommerce/` override folder.
- **Benchmarked against**: CartFlows (thank-you step), ShopEngine (order confirmation builder), ShopLentor
  Pro (thank-you templates).

Tags: **[S]** the option exists in the schema · **[N]** new · **[O]** old code can be ported.

---

## 1. Free: core

### Replacing the page
- "Override WooCommerce Order Complete Page" switch. **[S]**
- Renders on `is_order_received_page()` after checking the order key (never show an order without a
  valid key). **[N]**
- Falls back to the default WooCommerce view for failed or cancelled orders, or shows the template's
  "payment failed" state with a "Pay again" link. **[N]**

### Sections (template 1)
- **Header**: success icon (colors, size, radius), thank-you heading (text, color, size), success
  description, header background and padding. **[S]**
  - Placeholders in texts: `{first_name}`, `{order_number}`, `{order_total}`, `{email}`. **[N]**
- **Order summary**: order number, status pill (colors), detail rows (date, email, payment method,
  total) with their own styling. **[S]**
- **Order items**: header text, item name, item meta (variations, custom fields), price styling,
  spacing. **[S]**
- **Totals**: subtotal, shipping and tax rows on or off, grand total styling. **[S]**
- **Addresses**: billing and shipping cards, each on or off, with card, header and content styling, and a grid gap. **[S]**
- **Action buttons**: Track Order (goes to My Account → View order, or to a custom URL) and Continue
  Shopping, each with its own colors; shared size, padding, radius and gap. **[S]**
- **Footer**: on or off, two message lines, colors. **[S]**
- **General**: container max width, background, padding, section spacing, font family. **[S]**

### WooCommerce data that must show
- Payment instructions from gateways, such as BACS bank details and cheque instructions
  (`woocommerce_thankyou_{gateway}`). **[N]**
- Downloadable product links. **[N]**
- Customer order note. **[N]**
- Custom checkout fields from the Checkout Field Editor. **[N]**
- The `woocommerce_thankyou` action keeps firing, because analytics and pixel plugins need it. **[N]**

### More free options (found in the CartFlows, ShopEngine and ShopLentor settings code)
- **Thank-you text override** (a short custom line above the order details). **[N]** (CartFlows "Thank You Text")
- **Turn order details on or off** (show only the message). **[N]** (CartFlows)
- **Sections can be reordered and turned off one by one**: order overview, details, customer details, payment. **[N]**
- **Payment methods list** and the **order review** table as separate blocks. **[N]** (ShopEngine)

### Pass 2: found in the Thanko (VillaTheme), Thank You Page for WooCommerce (NeeBPlugins) and CartFlows code
- **Row and column component builder**: click to add rows, then drag in components:
  - header text
  - message text
  - order confirmation
  - order details
  - customer information
  - billing and shipping address
  - coupon
  - products
  - social icons
  - Google Map
  - custom text with shortcodes

  **[N]** (Thanko)
- **Apply only to orders with chosen statuses** (processing, on-hold, completed). **[N]** (Thanko)
- **Personalization shortcodes** in every text: `{order_number}`, `{order_date}`, `{order_status}`,
  `{order_total}`, `{order_subtotal}`, `{payment_method}`, `{shipping_method}`, `{billing_first_name}`,
  `{billing_last_name}`, `{billing_email}`, `{billing_city}`, `{billing_country}`, `{formatted_billing_address}`,
  `{formatted_shipping_address}`, `{items_count}`, `{shop_title}`, `{shop_url}`, `{home_url}`,
  `{my_account_url}`. **[N]** (Thanko, NeeBPlugins)
- **"Order again" button**, and a **Pay button** for unpaid orders. **[N]**
- **Product image in the items list** (on or off, width). **[N]**
- **Social "follow us" icons**: Facebook, Instagram, X, YouTube, LinkedIn, Pinterest, TikTok and others,
  with icon size, alignment and spacing. **[N]** (Thanko)
- **Global thank-you page**: redirect to any page, or to a custom external URL. **[N]** (NeeBPlugins free)
- **Failure redirect URL**: send failed payments to a chosen page. **[N]** (NeeBPlugins)
- **Translated redirect URLs** (WPML and Polylang, one per language), and multisite. **[N]**
- **Sandbox payment gateway** for admins only, so the page can be tested without paying. **[N]** (NeeBPlugins)
- **Preview with a real order** in the editor (an order picker). **[N]** (Thanko)

## 2. Free: advanced
- Guest account prompt: "Create an account to track your order", which sets a password for the order's email. **[N]**
- Print order button. **[N]**
- Social share of purchased products. **[N]** (benchmark: ShopLentor Pro has it in Pro; keep it free only if cheap to build)

---

## 3. Pro: core
- **More templates (2–5)**:
  - minimal receipt
  - two-column with an upsell sidebar
  - celebration (confetti header)
  - digital goods (downloads first)
  - local pickup (map and opening hours)

  **[N]**
- **Thank-you pages per product, category or payment method**: Display Rules on the layout (product in
  the order, category in the order, payment gateway, shipping method, order total range), with
  priority. **[N]**
- **Redirect to a custom page** (any WordPress page) under the same rules, passing the order key so the page
  can use the ShopGlut thank-you blocks or shortcodes. **[N]**
- **Upsell products**: related products, cross-sells of what was bought, or a manual list, with one-click
  add to a new cart. **[N]**
- **Next-order coupon**: generates a unique WooCommerce coupon per order (percent or fixed amount, expiry
  days, minimum spend, limited to the customer's email), shows it with a copy button, and can add it to the
  customer email. **[N]**

## 4. Pro: advanced
- **One-click post-purchase upsell** (charge the saved payment method). This is complex and depends on the
  gateway, so it's a later phase. **[N]** (benchmark: CartFlows Pro)
- **Order tracking timeline**: received → processing → shipped → delivered. It reads the order status and the
  tracking number meta (compatible with common shipment-tracking plugins). **[N]**
- **Review request block**: "Rate your products" stars that link to each product's review form. **[N]**
- **Referral / share-to-earn block**: share a link and get a coupon. **[N]**
- **Thank-you blocks and shortcodes** (order details, items, addresses, coupon) for use in custom pages. **[N]**
- **Rule builder for redirects and layouts**:
  - conditions: product, category, tag, variation, payment method, shipping method, user role, order total, date
  - operators: equals, not equals, contains, greater, less, before, after
  - AND/OR groups with a redirect priority

  **[N]** (NeeBPlugins Pro)
- **Coupon gift details**:
  - a new unique coupon or an existing one
  - code prefix and coupon style
  - type (percent, fixed cart, fixed product), amount, expiry in days, minimum and maximum spend
  - individual use, exclude sale items, include or exclude products and categories, limit to X items
  - usable only by the customer's email; allow free shipping
  - which orders get one (minimum total, chosen products)
  - a **copy button**
  - **send the coupon in its own email** (a template with subject and heading)
  - show it on My Account → Orders

  **[N]** (Thanko)
- **Google or Bing map** of the store or delivery address (API key, marker style, label). **[N]** (Thanko)
- **Thank-you page upsell / dynamic offers**: CartFlows-style accept and decline redirects and downsells. **[N]** (CartFlows Pro)
- **GA4 / GTM purchase tracking and PixelYourSite integration**. **[N]** (NeeBPlugins)
- **Thank-you skins** (CartFlows-style), and a **redirect after purchase** to an external URL. **[N]**
- **Auto-applied coupon** for the next visit (stored in the session, so the next cart applies it automatically). **[N]** (CartFlows Power Coupons)
- **Order bump on the next order**: a reminder link to the offer. **[N]**
- **Conversion tracking settings**: fire the purchase event once per order (a dataLayer push), and avoid
  double counting when the page is reloaded. **[N]**

---

## 5. Acceptance checklist
- [ ] The page only renders with a valid order key, and it never leaks another customer's order.
- [ ] Gateway instructions, downloads and order notes show for each order type.
- [ ] Reloading the page doesn't create a second coupon (Pro) or fire tracking twice.
- [ ] Failed, on-hold, pending and processing orders each show the right state.
- [ ] Works with the classic and the block checkout (both end at `order-received`).
