# Size Chart: Feature Spec

- **ID**: `size-chart` · **Plugin**: ShopGlut · **Code**: `modules/size-chart/module.php`
- **Benchmarked against**: Product Size Charts for WooCommerce (9k), Size Chart for WooCommerce by Multidots, CodeCanyon size chart plugins

## Built (7 Nov)
- [x] One chart as a text table (rows per line, cells split with `|`)
- [x] For all products or chosen categories
- [x] Own chart per product
- [x] Link opens the chart in a dialog

## Free: full
- [ ] Many charts (own admin list): table editor with add / remove rows and columns, header row
- [ ] Each chart: title, table, image ("how to measure"), notes below the table
- [ ] Assign a chart to categories, tags or products; per-product override or "no chart"
- [ ] Display: link + popup, extra product tab, or inline under add to cart
- [ ] cm / inch switch, values converted automatically
- [ ] Link style: text, button, icon; position (before / after add to cart, after variations)
- [ ] 6 ready charts to start from (shirts, trousers, shoes EU/US/UK, kids, rings, bras)
- [ ] Accessible dialog (focus trap, Esc, ARIA), responsive table with horizontal scroll

## Pro
- [ ] Size finder: customer enters height, weight and fit → suggested size (rules per chart, no AI)
- [ ] AI size advisor (uses Connectors; listed in phase P3)
- [ ] Several charts per product shown as tabs inside the popup
- [ ] Chart by attribute / variation (different chart per colour or material)
- [ ] Chart styles (striped, bordered, highlight column on hover), colours
- [ ] Report: how often each chart is opened, by product

## Tests
- [ ] Chart rules: product override beats category beats store default
- [ ] cm ↔ inch conversion keeps ranges like 86–90
