# Order Tracking Page: Feature Spec

- **ID**: `order-tracking` · **Plugin**: ShopGlut · **Code**: `modules/order-tracking/module.php`
- **Benchmarked against**: Advanced Shipment Tracking (70k, tracking page in Pro), TrackShip (5k), YITH Order Tracking

## Built (7 Nov)
- [x] `[shopglut_order_tracking]`: order number + email or phone, no login
- [x] Status, items and order updates
- [x] Courier tracking link from BusinessGlut Shipment Tracking
- [x] Works with BusinessGlut order numbers

## Free: full
- [ ] Status timeline: placed → paid → processing → shipped → out for delivery → delivered (custom statuses mapped to steps)
- [ ] Items with images, quantities and totals; shipping address shown partly hidden
- [ ] Several tracking numbers per order, each with courier logo and link
- [ ] "Track" button on My Account → Orders and in order emails, opens the page already filled in
- [ ] Brute-force guard: 10 tries per IP per hour, same error message for wrong number or wrong email
- [ ] Create the tracking page on first switch-on (choice), Gutenberg block
- [ ] Design: colours, step icons, compact or full layout
- [ ] Customer can print the order summary

## Pro
- [ ] Live courier events (Steadfast now, Pathao / RedX later) through BusinessGlut Courier, cached 30 minutes
- [ ] Estimated delivery date (by shipping zone and days to ship)
- [ ] Upsell products on the tracking page (related to the ordered items)
- [ ] "Request cancel" (while processing) and "Request return" (link to FrontGlut returns)
- [ ] Tracking link in SMS / WhatsApp messages (BusinessGlut SMS)
- [ ] 3 Pro page designs

## Tests
- [ ] Guest order, customer order, order with custom number, wrong email
- [ ] HPOS on and off
