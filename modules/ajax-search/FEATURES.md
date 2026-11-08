# Ajax Product Search: Feature Spec

- **ID**: `ajax-search` · **Plugin**: ShopGlut · **Code**: `modules/ajax-search/module.php`
- **Benchmarked against**: FiboSearch (100k), YITH Ajax Search (80k), Advanced Woo Search (100k)

## Built (7 Nov)
- [x] Results while typing, keyboard friendly
- [x] Product name, SKU and category matches
- [x] `[shopglut_search]` and the theme search form

## Free: full
- [ ] Result rows: image, name, price, SKU, stock badge, highlight of the typed words
- [ ] Search in: title, SKU, variation SKU, short description, content, tags, attributes, brands (each on / off)
- [ ] Groups: products, categories, brands, pages / posts (option)
- [ ] "See all results" goes to a search page that uses the same matching
- [ ] Simple typo tolerance (one wrong letter) and plural / singular
- [ ] Exclude out-of-stock, chosen products or categories
- [ ] Mobile: full-screen search overlay
- [ ] Block, shortcode, menu item, and replace the theme search form
- [ ] Results cached per term for 10 minutes

## Pro
- [ ] Search index table for big catalogs (10k+ products), rebuilt in the background
- [ ] Synonyms ("tee" = "t-shirt"), Bangla ↔ English words
- [ ] Search analytics: top terms, terms with no results
- [ ] Add to cart from the results
- [ ] Voice search button
- [ ] AI search by meaning (phase P3)

## Tests
- [ ] 5,000 products: first results under 300 ms with the index
- [ ] Theme search form replaced on Storefront and a block theme
