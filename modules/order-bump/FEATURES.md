# Order Bump: Feature Spec

- **ID**: `order-bump` · **Plugin**: ShopGlut · **Code**: `modules/order-bump/module.php`
- **Benchmarked against**: CartFlows (200k), FunnelKit Funnel Builder (order bumps), WooCommerce Order Bump plugins

## Built (7 Nov)
- [x] One offer: tick adds the product, checkout-only discount, minimum cart total
- [x] Classic checkout

## Free: full
- [ ] Many bumps (own admin list); show the first that matches, or up to N
- [ ] Conditions: cart contains products / categories, cart total min / max, not already in cart
- [ ] Position: before payment, after order review, before Place order
- [ ] 3 designs (dashed box, card with image, minimal); colours; arrow icon
- [ ] Variable product: choose the variation in the bump
- [ ] Block checkout support
- [ ] Discount removed when the bump is unticked; works with coupons

## Pro
- [ ] One-click upsell after payment on the thank-you page (adds to the paid order or a new order)
- [ ] "Upgrade" bump: replace a cart item with a better one
- [ ] A/B test two bumps
- [ ] Report: shown, accepted, extra revenue
- [ ] Conditions: user role, first order, schedule

## Tests
- [ ] Classic and block checkout, coupon + bump discount together
