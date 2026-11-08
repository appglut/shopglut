# Store Locator: Feature Spec

- **ID**: `store-locator` · **Plugin**: ShopGlut · **Code**: `modules/store-locator/module.php`
- **Benchmarked against**: Store Locator for WordPress with Google Maps (CodeCanyon 15.4k), WP Store Locator (60k), Agile Store Locator

## Built (7 Nov)
- [x] Stores with address, phone and hours (text list in settings)
- [x] Search by area or name
- [x] OpenStreetMap map and directions link

## Free: full
- [ ] Stores as their own admin list: name, address, city, postcode, country, phone, email, website, image, opening hours per day
- [ ] Map position from the address (OpenStreetMap Nominatim, with attribution) or set by hand
- [ ] Map with markers and clustering (Leaflet), list next to or under the map
- [ ] "Near me" (browser location) and search by address with a distance limit; results sorted by distance
- [ ] "Open now" badge from the opening hours
- [ ] Store tags (services: pickup, repair…) as filters
- [ ] Single store page with map
- [ ] Import / export stores (CSV)
- [ ] Shortcode and Gutenberg block

## Pro
- [ ] Google Maps option (own API key)
- [ ] Custom marker icons per tag
- [ ] Local pickup at a chosen store at checkout (with BusinessGlut shipping)
- [ ] Stock per store ("In stock at Dhanmondi")
- [ ] Report: searches by area

## Tests
- [ ] 200 stores, clustering, distance sort, mobile layout
