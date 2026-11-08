# AI Abilities (MCP): Feature Spec

- **ID**: `abilities` · **Plugin**: ShopGlut · **Code**: `modules/abilities/module.php`
- **Benchmarked against**: Jetpack AI agents & MCP, WPVibe MCP Server (100k), Easy MCP AI (30k)
- **Rule**: only users who can manage WooCommerce; changing abilities can be turned off.

## Built (7 Nov)
- [x] `shopglut/list-modules`, `shopglut/set-module`, `shopglut/search-products`

## Free: full
- [ ] Settings page listing every ability with an on / off switch, and a "read only" mode
- [ ] `shopglut/list-layouts`, `shopglut/get-layout`, `shopglut/assign-layout` (product page, shop, cart)
- [ ] `shopglut/get-module-settings`, `shopglut/update-module-settings` (validated against the schema)
- [ ] `shopglut/list-badges`, `shopglut/create-badge`
- [ ] `shopglut/wishlist-stats` (most wished products)
- [ ] Log of every change made through an ability (who, when, what), last 200 entries

## Pro
- [ ] `shopglut/build-layout`: make a layout from a description ("luxury watch product page")
- [ ] `shopglut/create-popup`, `shopglut/create-filter`
- [ ] Dry-run mode: ability returns what it would change, owner confirms

## Tests
- [ ] Ability calls through the WordPress MCP adapter as admin and as a customer (refused)
