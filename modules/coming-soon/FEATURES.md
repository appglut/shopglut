# Coming Soon & Maintenance: Feature Spec

- **ID**: `coming-soon` · **Plugin**: ShopGlut · **Code**: `modules/coming-soon/module.php`
- **Benchmarked against**: SeedProd Coming Soon (800k), CMP Coming Soon (100k), LightStart / WP Maintenance (100k)

## Built (7 Nov)
- [x] Coming soon or maintenance (503) mode
- [x] Store managers still see the site
- [x] Heading, message, opening date, colours, login link

## Free: full
- [ ] Scope: whole site, or only the shop (shop, products, cart, checkout closed; blog and pages open)
- [ ] 3 page designs; logo, background image / colour / gradient, fonts
- [ ] Countdown to the opening date; switch off automatically at that date (option)
- [ ] Email sign-up box on the page: stored in a list with CSV export, consent checkbox (or a FormGlut form)
- [ ] Social links and contact line
- [ ] Who gets in: chosen roles, IP addresses, secret preview link (sets a cookie for N days)
- [ ] Pages / URLs that stay open (e.g. /contact/, /my-account/)
- [ ] 503 with Retry-After for maintenance; "noindex" choice for coming soon; own SEO title and description
- [ ] Admin bar badge "Coming soon is ON" with a link to switch it off
- [ ] REST API and feeds closed too, except logged-in users and WooCommerce webhooks

## Pro
- [ ] Use any page built with blocks as the coming soon page
- [ ] More designs (6 Pro)
- [ ] Per product / category "coming soon": shows the launch date and a notify box, can't be bought, publishes itself at the date
- [ ] Launch email to everyone who signed up, sent when the store opens
- [ ] Send sign-ups to Mailchimp / Brevo
- [ ] Scheduled maintenance windows (e.g. every Sunday 02:00–03:00)

## Tests
- [ ] Logged-out visitor, customer, shop manager, preview link, open URL
- [ ] Page cache: coming soon page not cached for managers (SpeedGlut bypass)
