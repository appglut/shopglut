# Frequently Bought Together: Feature Spec

- **ID**: `bought-together` · **Plugin**: ShopGlut · **Code**: `modules/bought-together/module.php`
- **Benchmarked against**: WPC Frequently Bought Together (10k), YITH Frequently Bought Together, Merchant (bundle module)

## Built (7 Nov)
- [x] Suggestions from past orders, upsells or cross-sells
- [x] Tick boxes and live total
- [x] One button adds all ticked products

## Free: full
- [ ] Manual list per product (Linked products tab), mixed with automatic sources
- [ ] Co-purchase data built in the background (daily cron, saved per product) so pages stay fast
- [ ] Layouts: images with "+" signs, list, compact
- [ ] Variable products: choose the variation inline
- [ ] Discount when all items are bought together (% or fixed, shown in cart)
- [ ] Hide out-of-stock items; minimum items to show the box
- [ ] Position: after add to cart, after summary, in a tab; shortcode
- [ ] Classic and block cart

## Pro
- [ ] Discount tiers (2 items 5%, 3 items 10%)
- [ ] Show the box in the side cart and on the cart page
- [ ] Rules by category ("for phones, suggest cases")
- [ ] Report: views, bundles added, extra revenue
- [ ] AI recommendations (phase P3)

## Tests
- [ ] Discount stays correct when one item is removed from the cart
