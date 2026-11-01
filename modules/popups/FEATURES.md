# Popups & Banners: Feature Spec

- **ID**: `popups`
- **Type**: builder. Each layout is one popup; its template is the popup *type*. Category: Engagement.
- **Code**:
  - `module.php`: info, types, Display Rules, `layout_matches()`, the module Settings panel
  - `settings/popup.php`: the editor options; fields change per type, and values are saved as
    `settings[content|trigger|frequency|behavior|design][field]`
- **Old code**: none. This is a new module; there's no old plugin to port.
- **Benchmarked against**:
  - Popup Maker (600k)
  - Popup Builder (200k)
  - MailOptin (10k)
  - Icegram Engage (10k)
  - Notivo Notification for WooCommerce (5k): sales notifications
  - MC4WP (1M): subscribe forms

  Their string lists are in `_competitors/_option-lists/`.

Tags: **[S]** the option exists in the schema · **[N]** new. See `../../MODULE-FEATURES.md`.

---

## 1. Free: core

### Popup types (templates)
| Type | ID | What it is |
|------|----|-----------|
| Modal popup | `modal` | A centered box over a dimmed page, for offers and information. **[S]** |
| Announcement bar | `bar` | A thin banner at the top or bottom of the page. **[S]** |
| Slide-in box | `slide-in` | A small corner box that doesn't block the page. **[S]** |
| Subscribe popup | `subscribe` | A newsletter form with an email field and consent. **[S]** |

### Content
- Heading. **[S]**
- Rich content (the WordPress editor; a plain message for bars), with shortcodes and placeholders `{site_name}`,
  `{first_name}`, `{cart_total}`. **[S]** (the placeholders still need code **[N]**)
- Image, with its position at the top, left, right or as a background (not for bars). **[S]**
- **Coupon code to show**, with a copy button: a static code made in WooCommerce → Coupons. **[S]**
- Button: text, action (open a link or close), link, open in a new tab. **[S]**
- "No thanks" link that closes the popup (not for bars). **[S]**

### Subscribe form (subscribe type)
- Optional first name, email placeholder, button text. **[S]**
- Consent checkbox: none, optional or required, with its own text or the module default. **[S]**
- After subscribing: show a message, close after 3 s, or go to a URL. **[S]**
- **Subscribers saved in WordPress** (a `{prefix}shopglut_subscribers` table: email, name, popup ID,
  consent, IP hash, page URL, date) with an admin list, search, delete and **CSV export**. **[S]** (setting) / **[N]** (table, list)
- Email validation, an "already subscribed" message, and a honeypot field against bots. **[N]**
- Admin email notification for new subscribers. **[S]**
- WordPress privacy exporter and eraser for subscriber rows. **[N]**

### Triggers
- On page load, after a delay in seconds. **[S]**
- After scrolling X % of the page. **[S]**
- On click of a CSS selector. Links to `#shopglut-popup-{id}` always open the popup. **[S]**
- Shortcode `[shopglut_popup_button id="" text=""]`. **[N]**

### Frequency (cookies or localStorage, no personal data)
- Show again: on every page view, once per session, after X days, or never once it has been closed. **[S]**
- Never show again after subscribing, or after the button is clicked. **[S]**

### Behavior
- Close (×) button, and a delay before it appears. **[S]**
- Close by itself after X seconds. **[S]**
- Animations: fade, slide up, slide down, zoom, none. **[S]**
- Close on an overlay click and on Esc (not for bars). **[S]**
- Lock page scrolling while open (modal, subscribe). **[S]**
- Bar: stays visible while scrolling; push the page down instead of covering it. **[S]**

### Design
- Position:
  - modal: center, top or bottom
  - slide-in: one of the 4 corners
  - bar: top or bottom

  **[S]**
- Width (not bars), background, text color, heading size. **[S]**
- Button colors (empty uses the store accent color `--shopglut-accent`). **[S]**
- Corner radius, inner spacing, page dimming color, an extra CSS class. **[S]**

### Display Rules
- Show on:
  - the entire site
  - home
  - shop and archives
  - product pages (optionally only some categories or products)
  - cart, checkout, My Account
  - chosen pages

  **[S]**
- Never on chosen pages. **[S]**
- Visitors: everyone, guests or logged-in users. **[S]**
- Devices: desktop, tablet, mobile (checked in the browser). **[S]**
- Priority and active/draft status come from the layout list. **[S]**

### Module Settings (all popups)
- The most popups per page view (bars don't count). **[S]**
- Hide popups from administrators. **[S]**
- z-index. **[S]**
- Subscribers: store them, notify the admin, the default consent text. **[S]**

## 2. Free: advanced
- **Cache-safe**: popups render in the page footer, and every rule that depends on the visitor (cookies,
  device, logged in) is checked in JS, so full-page caching doesn't break them. **[N]**
- **Accessible**:
  - `role="dialog"`, `aria-modal`, a labelled heading
  - focus moves into the popup and is trapped there; Esc closes it; focus returns afterwards
  - bars use `role="region"`

  **[N]**
- **No layout shift**: pushing bars reserve their height; animations respect `prefers-reduced-motion`. **[N]**
- **Preview** in the editor (opens the popup on the site, ignoring frequency). **[N]**
- **Basic stats per popup**: views, closes, button clicks and subscribes, shown as counters in the layout
  list. These are aggregate counts, with no visitor data. **[N]**
- **Theme override path**: `yourtheme/shopglut/popups/{type}.php`. **[N]**

---

## 3. Pro: core

### More types
| Type | ID | What it is |
|------|----|-----------|
| Full-screen overlay | `fullscreen` | Covers the whole screen, for welcome mats and launches. **[N]** |
| Spin to win | `spin-wheel` | A prize wheel: email in, coupon out, with chances per segment. **[N]** |
| Sales notification | `sales-notification` | Toasts such as "Someone in Paris bought X, 5 minutes ago". **[N]** |
| Age verification | `age-gate` | A yes/no gate, an optional date of birth, and a redirect when they say no. **[N]** |
| Content lock | `content-lock` | Hides part of a page (a shortcode around content) until the visitor subscribes. **[N]** |

These are registered as Pro teasers in free (`templates()`). ShopGlut Pro supplies their options and front
end. Shared sections (Trigger, Frequency, Behavior, Design) come from the free file; type-specific
sections are added through `shopglut_layout_sections`.

### Pro triggers
- **Exit intent**: the mouse leaves toward the top on desktop; on mobile, a fast scroll up or the back button. With sensitivity. **[N]** (Popup Maker Pro has 6 methods)
- **Inactivity** for X seconds. **[N]**
- **After X page views** in the session, or **time on site**. **[N]**
- **WooCommerce events**:
  - after add to cart
  - when the cart total reaches X
  - on the cart page with item Y
  - after a coupon is applied
  - after checkout leaves an error

  **[N]**
- **Several triggers per popup** (any one opens it). **[N]**

### Pro targeting (AND/OR rule groups)
- **Cart**: total, item count, a product or category in the cart. **[N]**
- **Customer**:
  - user role
  - has purchased before, or a product
  - lifetime spend
  - order count
  - first-time visitor or returning visitor

  **[N]** (Popup Maker Pro)
- **Traffic**:
  - referrer (search, social, a URL)
  - UTM parameters
  - URL contains
  - query string
  - country (geo IP)
  - browser language

  **[N]**
- **Schedule**: a date range, days of the week and hours, in the store's timezone. **[N]**
- **Product data**: on sale, in stock, price range, tag, attribute. **[N]**

### Pro offers
- **Unique coupon per subscriber**:
  - auto-generated (percent, fixed or free shipping)
  - expiry, minimum spend
  - limited to that email
  - shown in the popup and sent by email

  **[N]**
- **Countdown timer** in popups and bars: a fixed end date, or **evergreen** (X minutes per visitor). Hides or replaces itself when it ends. **[N]**
- **Free-shipping progress text in bars** ("Spend $12 more for free shipping"). Uses the Pro free-shipping engine shared with Cart Page and Side Cart. **[N]**

## 4. Pro: advanced
- **Email service integrations** for subscribe forms: Mailchimp, Brevo, Klaviyo, MailerLite, ConvertKit
  (Kit), ActiveCampaign, FluentCRM, MailPoet, a webhook. Includes list or tag selection, field mapping and
  double opt-in. **[N]** (MailOptin, Popup Maker Pro)
- **Form integrations**: open, or close after submitting, for Contact Form 7, WPForms, Fluent Forms and Gravity Forms forms placed inside a popup. **[N]**
- **A/B split testing**:
  - variants of one popup
  - traffic split
  - choose the winner by conversion rate

  **[N]** (Popup Maker Pro, MailOptin, Icegram)
- **Analytics dashboard**:
  - views, conversions and conversion rate per popup over time
  - revenue from popup coupons (orders that used them)
  - CSV export

  **[N]**
- **Multi-step popups**: a yes/no question first, then the form (two-step opt-in). **[N]**
- **Sound on open**; **video popups** (YouTube or Vimeo, autoplay muted). **[N]** (Popup Builder)
- **More animations** (bounce, flip, slide from the side) and **close animations**. **[N]**
- **Template library**:
  - ready-made designs for Black Friday, welcome, exit discount, free shipping, newsletter and cookie notice
  - one-click import

  **[N]**
- **Sales notification details**:
  - sources: real recent orders (name shortened to "John D." or hidden), or chosen products
  - delay between toasts, how many per page, position, product image size
  - "X minutes ago"
  - exclude products or out-of-stock items
  - hide on the cart and checkout

  **[N]** (Notivo)
- **Gamification**: spin wheel segments (label, coupon, chance, color), "try again" segments, one spin per email. **[N]** (Popup Builder)
- **Popup sequences**: after one popup closes, open another; or limit to one per visit across all popups. **[N]** (Icegram)
- **Content lock modes**: subscribe to unlock, or log in to unlock; a blurred preview of the locked content. **[N]** (Icegram, MailOptin)
- **Global popup cookie reset**: a button that shows a popup again to everyone after a change. **[N]**

---

## 5. Free vs Pro reasoning
- Free includes **page load, delay, scroll and click triggers**, **frequency cookies**, **4 types**, a
  **subscribe form with local storage and CSV**, and **page, device and visitor rules**. Popup Maker and
  Popup Builder both give these away for free, so a Free popup module without them wouldn't compete.
- Pro includes **exit intent, WooCommerce triggers, advanced targeting, unique coupons, countdowns, email
  service integrations, A/B tests and analytics**. Popup Maker, Popup Builder and MailOptin all keep these
  in paid tiers, and they're what drives conversions.

## 6. Acceptance checklist
- [ ] Each free type opens with each trigger, respects frequency after reloads, and doesn't open again after it closes or a conversion.
- [ ] Works with full-page caching (WP Rocket, LiteSpeed): rules that depend on the visitor run in JS.
- [ ] The subscribe form validates, stores the subscriber (no duplicates), respects consent, and exports to CSV. The nonce and honeypot block spam.
- [ ] Keyboard and screen reader: focus is trapped, Esc closes it, focus returns, and the heading is announced.
- [ ] A bar set to push the page doesn't overlap a sticky theme header, and leaves no gap after it closes.
- [ ] "Popups per page view" is respected, and higher-priority popups win.
- [ ] Nothing loads (no CSS or JS) on pages where no popup matches.
- [ ] Pro types can't be created without Pro. An existing Pro popup stops rendering, without errors, when Pro is deactivated.
