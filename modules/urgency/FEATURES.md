# Sale Countdown & Low Stock: Feature Spec

- **ID**: `urgency` · **Plugin**: ShopGlut · **Code**: `modules/urgency/module.php`
- **Benchmarked against**: Countix Sales Countdown (6k), Merchant (9k), Sales Countdown Timer by VillaTheme, CodeCanyon urgency plugins
- **Rule**: only real data (real sale dates, real stock, real orders). No fake counters.

## Built (7 Nov)
- [x] Countdown to the product's sale end date
- [x] Low stock message with a threshold
- [x] On product pages and shop cards

## Free: full
- [ ] Countdown styles: inline text, boxes (days / hours / min / sec), compact; colours
- [ ] Position: before / after price, before / after add to cart
- [ ] Variable products: countdown of the chosen variation
- [ ] Hide when the sale has ended (no page reload needed)
- [ ] Low stock: text with `{stock}`, stock progress bar ("70% sold") from stock and total sales, colours by level
- [ ] "Sale starts in" countdown for scheduled sales
- [ ] Works with cached pages (end time sent as a timestamp, counted in the browser)

## Pro
- [ ] Campaign countdowns: one end date for chosen categories / products (Black Friday), with a sticky top bar
- [ ] "X sold in the last 24 hours" from real orders
- [ ] "X people are viewing this" from real visits (counted for the last 5 minutes)
- [ ] Cart timer: "Your cart is reserved for 10:00" on cart and checkout (message only, no fake stock)
- [ ] Flash sale schedules that change prices at the start and end (uses BusinessGlut discount rules)
- [ ] More countdown designs (flip, circle)

## Tests
- [ ] Simple and variable products with and without sale dates, timezone check
- [ ] Page cache on: countdown still correct
