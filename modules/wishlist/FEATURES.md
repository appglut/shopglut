# Wishlist: Feature Spec

- **ID**: `wishlist`
- **Type**: settings module (flat options, `settings/options.php`).
- **Old code [O]**:
  - free: `wishglut/`
  - Pro: `shopglut-wishlist-pro/includes/class-pro-*.php`. That covers multilist, movelist, account page,
    email manager and scheduler, SMS, PDF, return visitor, subscriptions, counter widgets, social table,
    cart features and table features.
- **Benchmarked against**: YITH Wishlist (400k), TI Wishlist (100k), WPC Smart Wishlist (100k).

Tags: **[S]** the option exists in the schema · **[N]** new · **[O]** old code can be ported.

---

## 1. Free: core

### Storage
- Logged-in users: a database table (`{prefix}shopglut_wishlist_items`: list_id, user_id, product_id,
  variation_id, quantity, date_added, price_at_add). **[O]**
- Guests: a cookie or session key with server-side rows. **[S]** (merge on login, delete guest lists after X days)
- Require login, with a login button text and icon. **[S]**

### Buttons
Each of the product page, shop page and archive pages has its own settings. **[S]**
- Enable.
- Position: before or after add to cart, or after the product meta.
- Style: text and icon, text only, or icon only.
- Texts before and after adding; normal and added icons; icon left or right.
- What happens on a second click: remove, go to the wishlist page, show "already added", or go to checkout.

More:
- Archive: limit to chosen categories or tags. **[S]**
- Hide the button for out-of-stock products. **[S]**
- Variable products: add the chosen variation (or the parent when no variation is chosen). **[N]**
- Shortcode `[shopglut_wishlist_button id=""]` and a block, for custom places. **[N]**

### Notifications
- Off, a side toast (8 positions, 5 effects) or a popup (5 effects). **[S]**
- Added and removed messages. **[S]**

### Wishlist page
- Choose the page, or use the shortcode `[shopglut_wishlist]`. **[S]**
- Table columns: image, name, price, stock, date added, add to cart, remove. **[O]**
- Remove an item when it's added to the cart (from the wishlist page, the account page or everywhere). **[S]**
- "Add all to cart" and "Remove selected" bulk actions. **[N]**
- Print the wishlist. **[S]**
- Let people view other people's wishlists through a share link. **[S]**
- Empty state with a "Return to shop" button. **[N]**

### Sharing
- Facebook, X/Twitter, WhatsApp, Pinterest, LinkedIn, Telegram and email, each with its own text
  options; a section title. **[S]**
- Copy link. **[N]**
- QR code. **[S]** (generate it client-side, with no external service)

### Menu and counter
- Menu button with text, icon and a counter bubble; hide it when empty. **[S]**
- Live count updates through AJAX fragments. **[N]**
- Widgets: counter widget and wishlist widget. **[N]**

### My Account
- Wishlist tab in My Account with a custom name. **[S]**

### Admin
- Popular products: the most-wishlisted products, with counts. **[N]**
- Wishlist count column in the Products list. **[N]**

### Appearance
- Styles for the wishlist page, product, shop and archive buttons, share buttons and the menu counter
  (colors, gaps, hover, transitions). **[S]**

### More free options (found in the YITH, TI and WPC settings code)
- **Button**:
  - extra CSS classes
  - button, link or icon only
  - **theme style vs plugin style**
  - icon library (heart, heart outline, bookmark, plus, and so on) or an uploaded icon, for both normal and added states

  **[N]**
- **More button positions**:
  - above the title
  - above or below add to cart
  - before or after the product image, product info or product buttons
  - a shortcode only

  **[N]** (WPC)
- **Labels**: "Browse wishlist", "Product added", "Already in wishlist", "Removed". **[N]**
- **Popup after adding**: open the wishlist popup, show a message, or redirect to the wishlist page. **[N]** (WPC)
- **Wishlist table**:
  - columns: unit price, stock status, date added, add to cart, checkboxes, actions
  - "Add selected to cart", "Add all to cart", "Empty wishlist" (with a confirm step)
  - a bulk actions dropdown
  - items per page with pagination
  - links to each product
  - "Continue shopping" link

  **[N]** (TI, YITH)
- **Add to cart from the wishlist**: redirect to the cart or checkout after adding; remove the item once added. **[S]/[N]**
- **Remove on second click**. **[S]** (an "After added click action" option)
- **Counter**: icon, text, singular and plural form, its position among the menu items, hide a zero value,
  reload the count on page load (cache-safe). **[S]/[N]**
- **Copy the wishlist link to the clipboard**. **[N]** (TI and WPC free)
- **Rename the word "wishlist" everywhere** (for example "Favorites"), plus a custom My Account endpoint slug and a permalink flush. **[N]** (TI)
- **Load cacheable items with AJAX**. **[N]** (YITH)
- **Erase all data on uninstall**. **[S]** (global setting)
- **Header widget / block** with the counter. **[N]**

### Pass 2: found in the WCBoost, MoreConvert and YITH code
- **Guest behavior**:
  - allow guest wishlists
  - **ask whether to merge** with the account list after login ("Yes, merge" / "Keep separate")
  - empty the guest list after merging
  - a guest message
  - **guest user lock** (show a login prompt)

  **[N]** (WCBoost, MoreConvert)
- **Allow variations in the wishlist** (on or off). **[N]** (WCBoost)
- **Auto-removal timing**: never, when added to the cart, or at checkout. **[N]** (WCBoost)
- **Undo after removing** an item. **[N]** (WCBoost)
- **Button display for items already added**: show the remove button, a "view wishlist" link, or hide the button. **[N]** (WCBoost)
- **Button position "on top of the image"** (a heart icon over the product thumbnail), separately for
  standard archives and Gutenberg product blocks. **[N]** (YITH, MoreConvert)
- **Wishlist title and description** on the page; a default wishlist name. **[N]**
- **Endpoints**: My Account → Wishlist, and an "Edit wishlist" endpoint. **[N]** (WCBoost)
- **More share networks**: Reddit, Pocket, Tumblr, Messenger, Digg. **[N]**
- **Mini wishlist dropdown** from the counter (open on hover or click); hide the counter when empty; turn it on for mobile or not. **[N]** (MoreConvert)
- **"Product already in wishlist" alert**; toast notification styles. **[S]/[N]**
- **GDPR consent notice** before guests' data is stored. **[N]** (MoreConvert)

- **Price change notices in the table**: increase, decrease or both. **[N]** (WPC) *(moved from Pro: free in competitors)*
- **"Remove by anyone"** setting for shared lists. **[N]** (TI) *(moved from Pro: free in competitors)*

- **Collaboration**: other users can add to a shared list, with a "collaborative" badge. **[N]** (WPC) *(Free by decision 2026-10-22)*
- **Follow wishlists**: follow other people's public lists. **[N]** (WPC) *(Free by decision 2026-10-22)*

## 2. Free: advanced
- Price change indicator in the table ("was $X when added"). **[N]**
- Works with block themes and the Product Collection block (button injected through `render_block`). **[N]**
- GDPR: personal data exporter and eraser for wishlist rows. **[N]**

---

## 3. Pro: core
- **Multiple wishlists**: create, rename and delete lists; public, shared (link only) or private; a list
  picker popup when adding. **[O]** (`class-pro-multilist.php`)
- **Move items between lists** from the table. **[O]** (`class-pro-movelist.php`)
- **Advanced My Account tab**: manage every list. **[O]** (`class-pro-account-page.php`)
- **Price-drop email** (the price fell below the price when added, or by more than X%) and
  **back-in-stock email**. **[O]** (`class-pro-email.php`, `class-email-manager.php`)
- **Email scheduler**: batched sending by cron, frequency limits, unsubscribe link. **[O]** (`class-email-scheduler.php`)
- **SMS notifications** through a provider (for example Twilio), sent only with consent. **[O]** (`class-pro-sms.php`)

## 4. Pro: advanced
- **Wishlist PDF export** (products, prices, links). **[O]** (`class-pro-products-pdf.php`)
- **Return-visitor reminder modal**: shown when someone comes back after a minimum time away; how often
  it shows, a cooldown after it's dismissed, its title and message, delay and auto-show, the most items to
  show, prices and add-to-cart buttons, mobile on or off, excluded pages, analytics. **[O]** (`class-pro-return-visitor.php`)
- **Cart features**: "Save for later" on cart items (remove from the cart when saved), "Save whole cart"
  with button text, position, clear option and redirect, and saved quantities. **[O]** (`class-pro-cart-features.php`)
- **Wishlist totals**: subtotal, tax and an estimated shipping (flat rate). **[O]** (`class-pro-settings.php`)
- **Cross-sell products** under the wishlist, picked from a set of sources. **[O]**
- **Estimate / quote request**: the customer sends the list to the admin with notes. **[O]** (benchmark: YITH Premium)
- **Customer notes per item** and **drag-and-drop ordering**. **[O]**
- **Subscriptions**: follow a product for updates. **[O]** (`class-pro-subscriptions.php`)
- **Advanced table and counter widgets**: a dropdown counter with a mini list. **[O]** (`class-pro-table-features.php`, `class-pro-counter*.php`)
- **Admin promotional email** to everyone who has wishlisted a product. **[N]** (benchmark: YITH)
- **Analytics dashboard**: adds over time, conversion to purchase, top products. **[N]**
  Product stats (how many people wishlisted it) for the last 7 days, the last 30 days or a custom range;
  optionally show those stats on the front end. **[N]** (WPC, TI)
- **Primary / default wishlist**, a default list name, the most lists per user, and choosing a list when adding. **[O]/[N]**
- **Search inside a wishlist** by name or note; **search public wishlists**. **[N]**
- **Linked-products slider** (related products and cross-sells) on the wishlist page. **[N]** (YITH)
- **Pinterest image** setting for sharing. **[N]**
- **Waitlist**: a separate list type with alerts for back in stock, low stock, on sale and price changes,
  plus a "flash sale waitlist", a waitlist counter in the header, and back-in-stock alerts for similar products. **[N]** (MoreConvert Pro)
- **Save for Later list** (a list type tied to the cart) and a **"Next purchase" cart button** on the cart page. **[N]** (MoreConvert)
- **Email automations**:
  - trigger-based sequences (added to the wishlist, price drop, back in stock, abandoned wishlist), each with delays
  - **one-shot campaigns** to everyone who wishlisted a product
  - an automation or campaign on/off state
  - send through the site's SMTP
  - CC recipients

  **[N]** (MoreConvert Pro)
- **Coupon in wishlist emails**:
  - amount and expiry in days
  - usable only by the customer's account email
  - free shipping
  - delete the coupon after it expires
  - a `{coupon_amount}` placeholder

  **[N]** (MoreConvert)
- **Copy all to my list**: a visitor copies a shared wishlist into their own. **[N]** (MoreConvert)
- **Live wishlist sharing** (collaborative, updates in real time). **[N]**
- **Reports**: sales and conversion per list, "bought through the list" and "bought through the coupon"
  attribution, filters and export. **[N]** (MoreConvert Pro)
- **Ask for an estimate**: a form with custom fields that emails the list to the admin. **[N]** (YITH Premium, MoreConvert)
- **Compatibility switch**: turn off another plugin's wishlist (for example ShopEngine's) to avoid having two. **[N]**

---

## 5. Acceptance checklist
- [ ] A guest adds an item, logs in, and the lists merge without duplicates.
- [ ] Button state is right on cached pages (fetched through AJAX after the page loads, not printed in the PHP output).
- [ ] Variable product variations are stored and added to the cart with their attributes.
- [ ] Share links open a read-only view. Private lists (Pro) are never readable by other people.
- [ ] Emails (Pro) respect unsubscribe and frequency limits, and are sent by cron in batches.
- [ ] Personal data is exported and erased through WordPress's privacy tools.
