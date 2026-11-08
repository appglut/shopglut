# Product Tabs: Feature Spec

- **ID**: `product-tabs` · **Plugin**: ShopGlut · **Code**: `modules/product-tabs/module.php`
- **Benchmarked against**: Custom Product Tabs for WooCommerce (80k), YITH Tab Manager, Product Tabs Manager (10k)

## Built (7 Nov)
- [x] Up to 3 global tabs
- [x] One custom tab per product
- [x] Rename Description, hide Additional information and Reviews

## Free: full
- [ ] Unlimited global tabs (repeater): title, content (editor with shortcodes), priority
- [ ] Unlimited custom tabs per product, in a "Tabs" panel of Product data
- [ ] Saved tabs library: reuse a saved tab on many products
- [ ] Reorder all tabs, including WooCommerce's own (priority numbers + drag in admin)
- [ ] Global tab conditions: all products, categories, tags
- [ ] Rename or hide default tabs per product (overrides the store setting)
- [ ] Tab icon (Font Awesome name or image)
- [ ] Mobile: tabs become an accordion under a chosen width
- [ ] Works in block themes (Product Details block) and with the ShopGlut product page layouts
- [ ] Empty tabs are not shown

## Pro
- [ ] Conditions: product type, stock status, price range, user role, schedule (from / to)
- [ ] Content types: FAQ (with FAQ schema), video, products grid, downloads / files, map, FormGlut form
- [ ] Tab styles: vertical tabs, pills, underline, boxed; colours and fonts
- [ ] Open a tab from a link (`#tab-name`) and scroll to it
- [ ] Bulk add or remove a tab on many products (Products list bulk action)
- [ ] Import / export tabs (JSON)

## Tests
- [ ] 10 global tabs + 5 product tabs, order kept on classic and block themes
- [ ] Accordion on mobile, keyboard navigation (arrow keys between tabs)
