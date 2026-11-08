# Brands: Feature Spec

- **ID**: `brands` · **Plugin**: ShopGlut · **Code**: `modules/brands/module.php`, Pro: `shopglut-pro/modules/brands/pro.php`
- **Benchmarked against**: Perfect Brands for WooCommerce (40k), YITH Brands, WooCommerce Brands (core 9.6+), CodeCanyon WooCommerce Brands (3.1k sales)

Tick an item (`- [x]`) when it is built and tested on a classic and a block theme.

## Built (7 Nov)
- [x] Uses the WooCommerce `product_brand` taxonomy, registers it on older WooCommerce
- [x] Brand logo or name on the product page
- [x] Brand name on shop cards
- [x] Logo on the brand archive page
- [x] `[shopglut_brands]` grid (columns, hide empty)

## Free: full
- [ ] Brand fields on the term screen: logo, banner image, website URL, "featured" flag, display order
- [ ] Brand column, filter and quick edit in the Products list
- [ ] Product page position: before title, after title, after price, after meta; logo size
- [ ] Shop cards: logo or name, position (above or below the title)
- [ ] Brand archive header: banner, logo, description, product count
- [ ] Grid options: logos only / logo + name, featured only, order by name / count / custom order, A–Z letter index
- [ ] `[shopglut_brands_slider]` simple logo slider (no library, CSS scroll snap)
- [ ] Brand filter: works as a ShopGlut Shop Filters source, and a plain `[shopglut_brand_filter]` list
- [ ] Brand in product schema (`brand` property) when SEOGlut is not active
- [ ] Convert an attribute (e.g. `pa_brand`) into brands in one click (Tools button, dry run first)
- [ ] Gutenberg blocks: Brands grid, Product brand
- [ ] Theme overrides: `yourtheme/shopglut/brands/*.php`, filters around every output

## Pro
- [ ] Brand carousel with autoplay, arrows, dots, slides per screen
- [ ] Brand page layouts: banner styles, featured products row, sub-brand list
- [ ] Brands A–Z page with live search and letter jump
- [ ] "Follow brand": customers get an email when a followed brand adds a product
- [ ] Brand condition in BusinessGlut discount rules and coupons
- [ ] Sales by brand report (BusinessGlut analytics tab)

## Tests
- [ ] WooCommerce 9.6+ core brands and an older WooCommerce without brands
- [ ] Classic theme (Storefront) and block theme (Twenty Twenty-Five)
- [ ] Attribute → brand conversion on 100 products, dry run shows the same counts
