# Checkout Field Editor: Feature Spec

- **ID**: `checkout-fields`
- **Type**: settings module (no layouts). Billing, Shipping and Order field lists plus Options, defined in `module.php`.
- **Old code [O]**: `checkoutglut/`.
- **Benchmarked against**: Checkout Field Editor by ThemeHigh (400k), Flexible Checkout Fields (80k),
  Checkout Field Manager by QuadLayers (80k).

Tags: **[S]** the option exists in the schema · **[N]** new · **[O]** old code can be ported.

---

## 1. Free: core

### Field lists
- Three sections: Billing, Shipping, Additional order fields. Drag to reorder. **[S]**
- **Core fields**: add a row with a core name (for example `billing_company`) to change it: label,
  placeholder, width, required, order, show or hide. **[S]**
- Separate "apply" switches control which kinds of changes reach core fields (label, placeholder,
  width/class, order, required). **[S]**
- **Custom fields**: label, name (meta key), type, width (full, half left, half right), placeholder,
  default value, options (`value | Label` per line), required, show on checkout, show in order and
  emails, extra CSS class. **[S]**
- Free field types: text, textarea, email, phone, number, select, radio, checkbox, date. **[S]**
- Extra free types, because the competitors' free versions have them: **URL, hidden, heading,
  paragraph (static HTML text), password**. **[N]**
- Reset each section to WooCommerce's defaults. **[N]**

### Where values show
- Saved to order meta (HPOS-safe, with `$order->update_meta_data()`). **[N]**
- Shown in admin order details (in the billing, shipping or their own box) and in the customer's
  My Account order view. **[N]**
- Shown in emails: admin new order and customer emails, with a switch for each email. **[N]**
- Shown on the Thank You page. This works with the Thank You Page Builder module. **[N]**
- Included in the order REST API response and in WooCommerce order exports. **[N]**

### Validation
- Required check plus WooCommerce validation types (email, phone, postcode). **[N]**
- Number min, max and step; text min and max length. **[N]**

### Checkout types
- **Classic checkout** (shortcode) through `woocommerce_checkout_fields` and `woocommerce_billing_fields`. **[N]**
- **Block checkout** through the Additional Checkout Fields API (`woocommerce_register_additional_checkout_field`).
  It supports text, select and checkbox in the `contact`, `address` and `order` locations. Map the other
  types to the closest supported type, and show a notice for types that block checkout can't render. **[N]**
- Same field sets on the My Account address edit forms (on or off). **[N]**

### More free options (found in the ThemeHigh, Flexible Checkout Fields and QuadLayers settings code)
- **Display switches per field**:
  - display in emails
  - display on order detail pages (admin and My Account)
  - display on the Thank You page
  - display on My Account → address
  - hide on invoices

  **[N]**
- **Field description / help text** under the input. **[N]**
- **Show the label or not**. **[N]**
- **Address formatting**: put the field on a new line, or after a comma, in formatted addresses; show
  state abbreviations. **[N]** (Flexible Checkout Fields)
- **Checkbox "checked by default"**; opt-in and opt-out values stored as readable text. **[N]**
- **Autocomplete attribute** (HTML `autocomplete`), for browser autofill. **[N]**
- **Select2 enhanced select** (searchable options, allow clear). **[N]**
- **Date picker options**:
  - date format
  - minimum and maximum date (fixed dates, days from today, or between two dates)
  - disabled weekdays

  **[N]**
- **Time picker options**: start and end hour, minute interval, 24-hour format. **[N]**
- **Number**: min, max and step. **[S]/[N]**
- **Reset a section** to WooCommerce's defaults. **[N]**
- **Backup and import of settings** (in ShopGlut Tools). **[S]**
- **Option to drop jQuery UI CSS** on the front end (conflict fix). **[N]**
- **Separate editors for block and classic checkout**, with a first-run screen ("Which checkout do you use?")
  that detects the checkout page's type. **[N]** (ThemeHigh)
- **Duplicate field**; **"(optional)" suffix** shown on fields that aren't required (on or off). **[N]**
- **Several validation rules per field**: email, phone, number, postcode, and in Pro a custom regex. **[N]**
- **Confirm email field** (it must match the billing email). **[N]** (QuadLayers: "Email mismatch")

## 2. Free: advanced
- Field position relative to core fields (before or after a given field). **[S]** (handled by list order)
- Country-specific address locale is respected: don't overwrite WooCommerce's per-country required and
  label rules unless "apply" is on. **[N]**
- Import and export of field sets through ShopGlut Tools. **[S]** (shared Tools)

---

## 3. Pro: core
- **Conditional logic**: show or hide a field (or make it required) based on:
  - another field's value (equals, not equals, contains, empty, not empty)
  - cart contents (product, category, cart total or quantity range)
  - user role and logged-in state
  - shipping method, payment method, country

  Conditions can be combined with AND/OR groups. **[N]**
- **More field types**: file upload (allowed types, max size, stored in protected uploads, linked in the
  order), color picker, time, date range, multi-select, checkbox group, radio with images, radio with
  colors, country/state selectors. **[N]**
- **Fields with a fee**: a fixed or percentage fee per option or per checked checkbox, added with
  `WC()->cart->add_fee()`, taxable or not. **[N]**

## 4. Pro: advanced
- **Custom sections**: new field groups placed at chosen checkout hooks (before billing, after the order
  notes, before payment, and so on), each with a title. **[N]**
- **Multi-step checkout** (steps for contact, address, shipping and payment, with a progress bar). **[N]** (benchmark: CartFlows)
- **Custom validation**: a regex per field with a custom error message. **[N]**
- **Field pricing rules**: a fee per character or quantity. **[N]**
- **Per-product fields**: show a field only when a given product is in the cart. **[N]** (comes from the conditions)
- **Save to the customer profile**: fill the field in on the next checkout. **[N]**
- **Order list column**: show a custom field as a sortable column in the WooCommerce orders table. **[N]**
  Make it sortable and filterable on the orders list, and listable or filterable on the customer's
  View Orders screen. **[N]** (QuadLayers)
- **Show or hide a field by product, category, product type or subtype (virtual or downloadable), or role**,
  with "apply even when the cart has more than one product". **[N]** (QuadLayers)
- **Parent / child conditions**: show a field when a parent field has a given value. **[N]**
- **Message / notice field**: an alert box in the checkout (info, warning or success style), with content. **[N]**
- **Button / link field** that goes to a URL. **[N]**
- **More field types**:
  - **hidden**
  - **HTML**
  - **image** (a static image)
  - **country**
  - **state**
  - **password**
  - **datetime-local**
  - **month**
  - **week**
  - **checkbox group**

  **[N]** (ThemeHigh: 20 types; Flexible Checkout Fields: 22)
- **File upload settings**: allowed types, maximum size in KB, maximum number of files; uploads listed on
  the order and on the customer profile. **[N]**
- **Fee settings**: a fee amount or percentage per field or option, taxable or not, with a tax class. **[N]**
- **Remove account fields** (account username and password at checkout). **[N]**

---

## 5. Acceptance checklist
- [ ] Hiding a required core field (for example `billing_phone`) doesn't block checkout.
- [ ] Custom fields save, show in admin, emails, My Account and Thank You, and survive the switch to HPOS.
- [ ] The same configuration works on the classic and the block checkout pages. Fields block checkout can't render are flagged in the admin.
- [ ] Field names are sanitized to `[a-z0-9_]`, and duplicates are rejected.
- [ ] Uploaded files (Pro) can't be listed publicly (`.htaccess` deny or a protected path) and are deleted with the order.
