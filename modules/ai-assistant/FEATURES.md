# AI Shopping Assistant: Feature Spec

- **ID**: `ai-assistant` · **Plugin**: ShopGlut · **Code**: `modules/ai-assistant/module.php`
- **Benchmarked against**: Tidio Lyro, YourGPT, StoreAgent, WooCommerce Claude commerce agent
- **Rules**: AI only through WordPress Connectors; works as a smart search when no AI is connected; only real products.

## Built (7 Nov)
- [x] Understands needs and budgets
- [x] Recommends only real products, with add to cart
- [x] Any AI provider from Settings → Connectors; smart search fallback; rate limit

## Free: full
- [ ] Chat window design: position, colours, avatar, launcher icon, greeting, quick-reply chips
- [ ] Product cards in the chat: image, price, rating, add to cart, view
- [ ] Remembers the conversation during the visit (session)
- [ ] Uses the store notes (shipping, returns, payment) for policy questions
- [ ] "Talk to a person" link: WhatsApp, phone, contact page
- [ ] Show on all pages, product pages only, or with a shortcode
- [ ] Privacy line under the chat; no logs kept by default

## Pro
- [ ] "Where is my order?": order number + email check, then status and tracking
- [ ] Chat logs and report: top questions, products shown, added to cart
- [ ] "Ask about this product" on the product page (answers from that product's data and reviews)
- [ ] Learns from chosen pages (FAQ, policies)
- [ ] Offers a coupon when the shopper hesitates (owner-defined)
- [ ] Shared engine with the ChatGlut support agent

## Tests
- [ ] With no AI provider: search fallback answers; with a provider: budget filter respected
