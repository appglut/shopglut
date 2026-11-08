# Measurement Price Calculator: Feature Spec

- **ID**: `measurement-price` · **Plugin**: ShopGlut · **Code**: `modules/measurement-price/module.php`
- **Benchmarked against**: WooCommerce Measurement Price Calculator (official), CodeCanyon Measurement Price Calculator (2.7k)

## Built (7 Nov)
- [x] Area (width × length) or length
- [x] Price per m², ft², m or ft, with a minimum
- [x] Live price on the product page; size saved on the order line

## Free: full
- [ ] Modes: area, length, volume (L × W × H), weight, perimeter
- [ ] Min / max and step per dimension; default values
- [ ] Ready sizes dropdown (e.g. 120×180) next to custom size
- [ ] Shows the calculated area and price; quantity multiplies it
- [ ] Variable products: price per unit per variation
- [ ] Size shown in cart, checkout, order, emails and invoices
- [ ] Classic and block cart / checkout
- [ ] Clear error messages for values outside the limits

## Pro
- [ ] Price tables: different price per unit by size range
- [ ] Extra fees: cutting fee, fixed setup fee
- [ ] Tiles / flooring calculator: boxes needed with waste %
- [ ] Stock in units (m²) reduced by the bought area
- [ ] Own formula with variables

## Tests
- [ ] 2.5 × 3.2 m at 450/m² with minimum 1000, quantity 2: cart total matches
