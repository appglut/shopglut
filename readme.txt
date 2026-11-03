=== ShopGlut – Product, Shop, Filter, Swatches, Wishlist & More Builder for WooCommerce ===
Contributors: appglut
Tags: woocommerce, product page builder, wishlist, variation swatches, side cart
Requires at least: 6.2
Requires PHP: 7.4
Tested up to: 7.1
Stable tag: 2.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

WooCommerce product page, shop, cart and checkout builder with swatches, filters, wishlist, side cart, badges and popups.

== Description ==

**ShopGlut** is an all-in-one builder for WooCommerce. Instead of installing a separate plugin for every storefront feature, you manage the product page, shop, cart, checkout, wishlist and more from one modern admin.

= Modules =

Every module can be switched on or off. Modules that are off load no code.

**Product Page Builder**
* 7 layouts: classic, gallery right, sticky summary, centered, summary card, editorial and brand panel.
* Uses WooCommerce's own gallery, add to cart, tabs and related products, so variations and other plugins keep working.
* Colors and sizes for title, price and button; image style, zoom, lightbox; features section and extra product tabs.
* Apply a layout to all products, chosen categories or chosen products.

**Shop & Archive Builder**
* 10 product card designs for the shop, category, tag and search pages.
* Columns, gaps, colors, shown elements, button text and products per page.
* Works with WooCommerce sorting, paging and the Shop Filters module.

**Cart Page Builder**
* 3 cart layouts with quantity buttons, coupons and totals that update without reloading the page.
* Replaces the classic cart or the Cart block, or use the `[shopglut_cart]` shortcode.

**Checkout Field Editor**
* Rename, resize, reorder, hide or require billing, shipping and order fields.
* Add text, textarea, email, phone, number, select, radio, checkbox and date fields.
* New fields are saved on the order and shown in the order admin, customer emails and the account area.
* New fields also appear in the block checkout.

**Thank You Page Builder**
* A designed order received page with order summary, items, addresses and next-step buttons.
* Payment gateway instructions keep showing.

**Variation Swatches**
* 10 styles: dropdown, buttons, round and square colors, images, pills, radio list, color with label, image with label and underline.
* Set a color, image or short label on each attribute term.
* Unavailable combinations are crossed out, faded or hidden. The chosen value shows next to the attribute name.

**Product Badges**
* Sale, new and out-of-stock badges with their own conditions, text, colors and position (6 corners or before the title).
* Show them on all products, the shop, single product pages, chosen categories or tags.

**Shop Filters**
* Filter by category, tag, any attribute, price (slider or ranges), rating, stock status and on sale.
* Checkbox, radio, dropdown, button, grid, cloud, color and image designs.
* Active filter chips and a reset link. Uses WooCommerce's own filter URLs.

**Wishlist**
* Wishlist buttons on product, shop and archive pages, with icon, text and position settings.
* Guest wishlists are kept in the browser and added to the account when the customer logs in.
* Wishlist page, header counter, sharing by link, social networks and QR code, and print.
* `[shopglut_wishlist]`, `[shopglut_wishlist_counter]`, `[shopglut_wishlist_button]`, `[shopglut_popular_wishlist]`.

**Side Cart**
* A cart drawer that opens when a product is added, with a floating cart button.
* Change quantities, remove with undo, apply coupons, share the cart and go to checkout.

**Popups & Banners**
* Modal popups, announcement bars, slide-in boxes and newsletter popups.
* Open after a delay, on scroll or on click; choose how often they show, where and on which devices.
* Subscribers are saved in WordPress with consent, and can be exported as CSV.

= Why ShopGlut =

* One plugin and one admin instead of many single-purpose plugins.
* Works with classic and block themes.
* Compatible with HPOS (High-Performance Order Storage).
* Assets load only on the pages where a module is used.
* Translation ready and RTL friendly.

= Requirements =

WooCommerce must be installed and active.

== Installation ==

1. Upload the plugin to `/wp-content/plugins/` or install it from the Plugins screen.
2. Activate WooCommerce, then activate ShopGlut.
3. Open the ShopGlut menu and enable the modules you need.

== Frequently Asked Questions ==

= Does ShopGlut require WooCommerce? =

Yes. WooCommerce must be installed and active.

= Can I use only some modules? =

Yes. Enable only the modules you need; unused modules load nothing on the front end.

= Does it work with block themes? =

Yes. Every module works with classic themes and block themes (such as Twenty Twenty-Five).

= Does it slow my store down? =

Each module loads its files only on the pages where it shows something, and modules that are off load nothing. Icons are small inline SVGs, so no icon font is loaded.

= Does ShopGlut connect to outside services? =

No. Wishlist QR codes are made in the visitor's browser, and popup subscribers are stored in your own database.

== Screenshots ==

1. Dashboard with module overview, layout counts and quick links to every builder.
2. Modules screen: switch each module on or off and see what is included.
3. Product Page Builder layout list with status, priority and search.
4. Template picker when creating a new layout.
5. Product Page Builder layout with color and button swatches and a wishlist button.
6. Shop page with the filter panel and product cards from the Shop & Archive Builder.
7. Variation Swatches: chosen color and size shown next to the attribute name.
8. Cart Page Builder: quantities, coupon and order summary.
9. Side Cart opened after adding a product.
10. Wishlist page with sharing, QR code and print.
11. Subscribe popup from Popups & Banners.
12. Checkout Field Editor: change, hide or add billing, shipping and order fields.
13. Shop Filters editor: choose the filters and their designs.

== Changelog ==

= 2.1.0 =
* New: every module now works on the store: product page, shop and archive cards, cart, checkout fields, thank you page, swatches, badges, filters, wishlist, side cart and popups.
* New: Product Page Builder with 7 layouts that keep WooCommerce's own add to cart, gallery and tabs.
* New: Shop & Archive Builder with 10 product card designs.
* New: Variation Swatches with 10 styles and color, image and label fields on attribute terms.
* New: Shop Filters for price, rating, stock, on sale and every attribute, with color and image designs.
* New: Popups & Banners with a subscribers list and CSV export.
* New: Wishlist with guest lists, sharing, QR codes made in the browser, and a header counter.
* Improved: block theme support for every module.
* Improved: icons are inline SVGs; no icon font is loaded.

= 2.0.0 =
* Rebuilt admin and modules: product page, shop and archive, cart, checkout fields, thank you page, swatches, badges, filters, wishlist, side cart and popups.
