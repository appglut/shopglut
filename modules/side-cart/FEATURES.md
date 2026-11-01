# Side Cart: Feature Spec

- **ID**: `side-cart`
- **Type**: settings module (flat options, `settings/options.php`).
- **Old code [O]**: `sidercart-glut/`.
- **Benchmarked against**: XootiX Side Cart (70k), Modern Cart (60k; coupon and free-shipping bar in
  free), Menu Cart (70k), FunnelKit Sliding Cart (30k).

Tags: **[S]** the option exists in the schema · **[N]** new · **[O]** old code can be ported.

---

## 1. Free: core

### Drawer
- Enable the side mini cart. **[S]**
- Position: right or left. **[S]**
- Animation: slide, fade or scale. **[S]**
- Cart width. **[S]**
- Auto-close after X seconds. **[S]**
- Overlay; close on overlay click or Esc; focus trap. **[N]**

### Opening
- Auto-open after add to cart (AJAX add on archives; single product AJAX add through a WooCommerce form
  submit hook). **[S]**
- Open on hover over the icon, with a delay in ms. **[S]**
- Custom trigger CSS class. **[S]**
- Auto-open on the first visit when the cart has items. **[S]**
- Skip the cart page: redirect it to checkout. **[S]**

### Floating icon and menu
- Floating icon on or off; icon style (cart, bag, basket); size, colors, badge colors; bottom and side offsets. **[S]**
- Hide it when the cart is empty. **[S]**
- Badge count: unique items or total quantity. **[S]**
- Add to the navigation menu. **[S]** (choose which menu location **[N]**)
- Shortcode `[shopglut_side_cart_button]`. **[N]**

### Items
- Product images, with thumbnail size and shape (square, rounded, circle). **[S]**
- Title length limit. **[S]**
- Quantity +/- controls; a message when stock runs out ("max quantity" message). **[S]**
- Remove item, with 10-second undo. **[S]**
- "Empty cart" button. **[S]**
- Variation attributes and item meta under the title. **[N]**

### Totals and actions
- Show subtotal or total. **[S]**
- Discount amount, total savings and the savings label. **[S]**
- Detailed order summary. **[S]**
- Shipping calculator. **[S]**
- Continue shopping button, which either closes the cart or goes to the cart page. **[S]**
- Checkout and View cart buttons. **[N]** (styling exists in **[S]**)

### Coupon (free, by decision)
- Coupon field, collapsible or expanded by default, with success and error messages. **[S]**

### Empty cart and notices
- Empty message, "Shop now" and "Return to shop" buttons with their texts. **[S]**
- Toast notifications in 6 positions. **[S]**

### Sharing
- Share the cart by email (a link that rebuilds the cart). **[S]**

### Appearance
- Primary, background, text, border and hover colors; header, item and button colors; custom CSS. **[S]**

### More free options (found in the XootiX, Modern Cart and Menu Cart settings code)
- **Fly-to-cart animation**: the product image flies to the basket icon on add. **[N]** (XootiX)
- **Mode**: slide-out drawer, or a dropdown flyout from the menu item. **[N]** (Menu Cart has a separate mode for desktop and mobile)
- **Header**: heading text and alignment, close icon (style, size, alignment), "Saved for later" tab. **[N]**
- **Sticky footer**: subtotal, savings and buttons stay pinned to the bottom. **[N]** (XootiX)
- **Footer text** under the buttons (for example "Taxes calculated at checkout"); hidden when the cart is empty. **[N]**
- **Button row builder**: drag to reorder and choose which buttons show (Cart, Checkout, Continue);
  leaving a text empty removes that button. **[N]**
- **Show the total in the checkout button** ("Checkout · $120"). **[N]**
- **Quantity update delay** (debounce, in ms), quantity input style (+/- buttons, input only, stepper). **[N]**
- **Product row layout**:
  - choose and order the parts: image, title, variation, price, quantity, line total, delete
  - "one line" or stacked display
  - delete control as an icon or text, with its position

  **[N]**
- **Variation data** in the title or as a list, with a separator format. **[N]**
- **Highlight recently added items** (scroll to them and flash them). **[N]**
- **Remove WooCommerce's "View cart" link** after an AJAX add. **[N]**
- **Refresh the cart fragments on add** (a fix for themes that cache fragments). **[N]**
- **AJAX add to cart on single product pages**, excluding categories or products by ID. **[N]**
- **Visibility**: show on mobile or not, do not show on chosen pages, show or hide on the cart and checkout pages, entire site vs WooCommerce pages only. **[N]**
- **Basket count position** on the icon; basket shadow; hide the theme's own cart icon. **[N]**
- **Empty cart image**. **[N]**
- **Login prompt on checkout click** for guests (opens the login popup or slider). **[N]**
- **Express checkout buttons** in the footer (renders the gateways' payment request buttons), with their position. **[N]** (Modern Cart free)
- **Coupon list**: show the available public coupons with an "Apply" button for each; the most coupons to show. **[N]** (XootiX)
- **Free shipping bar**: decided **Pro** (2026-10-22), including the basic one-goal bar. See section 3.

### Pass 2: found in the FunnelKit Cart, WPC Fly Cart and Modern Cart code
- **Confirm before removing or emptying** (on or off), alongside undo. **[N]** (WPC)
- **Reverse item order** (newest first). **[N]**
- **Per-item extras**:
  - item data (attributes, meta)
  - price or subtotal under the title
  - **estimated delivery date** per item
  - a link to the product

  **[N]** (WPC)
- **Content hook points** for custom text or shortcodes: before or after the cart, the items, each item,
  the item name and info, the buttons, and the suggested products. **[N]** (WPC)
- **Subtotal row above the checkout button**; a cart icon on the checkout button. **[N]** (FunnelKit)
- **Savings text**, for example "You saved {percent}%". **[N]** (Modern Cart)
- **Separate desktop width (px) and mobile width (%)**. **[N]** (FunnelKit)
- **Open on normal (non-AJAX) add to cart**, not only on AJAX add. **[N]** (WPC)
- **Rounded style** switch; a background image for the drawer. **[N]**
- **Empty cart texts**: title, description and button text, plus "Redirect to shop". **[N]**
- **Multilingual texts** (WPML, Polylang, TranslatePress, Weglot). **[N]**
- **Effects on the cart icon after adding**: shake horizontally or vertically, rotate, zoom, flip, roll, or none.
  Icon size on hover; an uploaded icon image. **[N]** (CartSuite)
- **Loader styles** while the cart updates: spinner, dual ring, ripple, balls, roller. **[N]** (CartSuite)
- **Which total the menu cart shows**: items only, price only, or both; subtotal vs cart total (with
  discounts) vs checkout total (with fees and shipping). **[N]** (Menu Cart)
- **Menu cart action on click**: open the drawer, go to the cart page, or go to a chosen page. Show it even when empty. **[N]**
- **Display rules using WordPress and WooCommerce conditional tags** (for example `!is_page(...)`), and screens (desktop only or mobile only). **[N]** (CartSuite, Menu Cart)
- **"Select variation" popup**, so variable products can be added from archives straight into the side cart. **[N]** (CartSuite)
- **Theme conflict detection**: warn when the theme already has its own mini cart, and offer to hide it. **[N]** (Menu Cart)

- **Product cards layout**: show items as cards in a grid, with details on the back of the card and a flip animation. **[N]** (XootiX) *(moved from Pro: free in competitors)*
- **Confetti when a goal is reached** or checkout is clicked. **[N]** (WPC) *(moved from Pro: free in competitors)*

## 2. Free: advanced
- Cart contents are fetched by AJAX after the page loads, so it works with page caching (no cart HTML in cached pages). **[N]**
- Keeps the WooCommerce `added_to_cart` and `wc_fragments_refreshed` events in sync, so other mini carts update too. **[N]**
- Excluded pages (don't load on checkout, or on chosen pages). **[N]**
- Works with the block-based Mini-Cart (optionally replaces it, or hides it to avoid having two). **[N]**

---

## 3. Pro: core
- **Free shipping progress bar** inside the drawer, using the same engine as the Cart Page Pro bar (zone
  minimum or a custom amount, messages, colors). **[N]**
- **Recommended products / upsells**: sources are cross-sells, related products, the same category,
  best sellers or a manual list; shown as a carousel; quick add, including variations in a mini popup. **[N]**
- **More cart templates**:
  - compact
  - card
  - full-screen on mobile
  - popup (centered modal) instead of a drawer

  **[N]** (benchmark: Modern Cart popup mode)
- **More icons and animations**: an icon library, uploaded icons, a bounce on the badge after adding. **[N]**

## 4. Pro: advanced
- **Cart abandonment recovery** (the shared engine; Cart Page Pro reuses it):
  - Capture the email at checkout, or from logged-in users.
  - Mark a cart abandoned after X minutes.
  - Send up to 3 reminder emails, each with a delay, an optional unique coupon, and a link that restores the cart.
  - A dashboard with recoverable, recovered and lost carts plus revenue.
  - GDPR consent text and an opt-out.

  **[N]** (benchmark: Cart Abandonment Recovery)

  More recovery options (found in the Cart Abandonment Recovery settings code):
  - **Abandoned cut-off time** (in minutes) and **lost time** (when an abandoned cart is counted as lost).
  - **Tracking**: turn tracking off for chosen roles or order statuses; exclude products; a blacklist of emails or domains.
  - **Email follow-ups** (each a template):
    - subject and body
    - send delay (minutes, hours or days)
    - From name and address, Reply-To, copy to the admin as CC or BCC
    - WooCommerce email styling
    - abandoned-products table: choose its columns, image size, prices with tax
  - **Coupon per follow-up**:
    - type (percent, fixed cart, free shipping)
    - amount, expiry, individual use only
    - apply it automatically from the recovery link
  - **Email capture popup**: asks guests for their email when they leave or click checkout.
  - **On-site reminder banner** when the shopper comes back.
  - **Webhook** (sends abandoned cart data to Zapier, n8n and similar), plus **SMS and WhatsApp follow-ups**.
  - **Notifications**:
    - notify the admin when a cart is recovered
    - send a weekly recovery report email
    - warn when abandoned products go out of stock
  - **Reports**:
    - recoverable, recovered and lost orders and revenue
    - recovery rate
    - activity log per cart
    - scheduled jobs (Action Scheduler)
  - **A/B split testing** of templates.
  - **Dynamic conditions** on templates (cart total, products, country).
- **Reward milestones** (free shipping → discount → free gift) on one bar. **[N]** (benchmark: FunnelKit, Modern Cart)
  - Settings for each goal:
    - type (free shipping, % or fixed discount, free gift product)
    - amount
    - whether it counts the subtotal or the quantity
    - title and icon
  - Bar settings:
    - divided or continuous bar
    - bar height
    - celebration animation (confetti) when a goal is reached
  - Gifts: shown as normal cart items or as a separate block; the customer may remove a gift or not;
    gift quantities left out of the basket count. **[N]** (XootiX `rewards.php`)
- **Save for later**: move an item to a "Saved" tab in the drawer and back again, with an icon. **[N]** (XootiX)
- **Suggested products block**: a slider or rows, the number of products, products per column; if
  there are no cross-sells, fall back to random products. **[N]**
- **Button themes**: reusable button styles shared by every button in the drawer. **[N]**
- **Instant checkout in the drawer**: the checkout form opens inside the side cart, optionally right away. **[N]** (WPC Premium)
- **Cart add-ons as checkboxes**: shipping protection, gift wrapping, an extended warranty. Each adds a
  product or fee in one click. **[N]** (FunnelKit "Special Add-ons")
- **In-cart variant selection** for upsell products, with a "Buy it with" heading. **[N]** (FunnelKit)
- **Suggested product sources**:
  - related, cross-sells, upsells
  - featured, on sale, recent, random
  - **suggestions for an empty cart**
  - a maximum number of upsells
  - carousel or list

  **[N]** (WPC, FunnelKit)
- **Reward thresholds**: rewards calculated on the subtotal or the total, a message for each reward, and a "Congrats, you've unlocked all rewards" message. **[N]**
- **A/B testing** of cart layouts and offers, and **pixel tracking** of cart events. **[N]** (FunnelKit Pro)
- **Cart sharing**: social networks, copy link and QR code. **[N]**
- **Order bump in the cart**: a checkbox offer such as "Add gift wrap for $3". **[N]**
- **Cart reserve timer**. **[N]**
- **Variation editing in the cart**: change size or color without removing the item. **[N]**
- **Analytics**: drawer opens, upsell clicks and conversions. **[N]**

---

## 5. Free vs Pro decision
**Decided (2026-10-22):**
- The **coupon field is Free**. It's already in the free schema.
- The **free shipping progress bar is Pro**, the basic bar included.

`pro_features` is updated to "Free shipping progress bar".

## 6. Acceptance checklist
- [ ] Opens after an AJAX add on archives, after a single-product add (with or without AJAX), and through hover and trigger classes.
- [ ] Quantity, remove, undo, coupon and the shipping calculator update the totals and every cart fragment on the page.
- [ ] Works with full-page caching (the cart is fetched after load) and on the block-based cart and checkout pages.
- [ ] Keyboard: the drawer traps focus, Esc closes it, and focus returns to the trigger.
- [ ] Doesn't load on checkout unless enabled there.
