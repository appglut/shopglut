# AI Review Summary: Feature Spec

- **ID**: `review-summary` · **Plugin**: ShopGlut · **Code**: `modules/review-summary/module.php`
- **Benchmarked against**: Amazon "Customers say", Judge.me AI summaries

## Built (7 Nov)
- [x] Pros and cons customers mention most
- [x] Made in the background, saved on the product
- [x] Updated after new reviews

## Free: full
- [ ] Minimum number of reviews before a summary is made (default 5)
- [ ] Box design and position (above reviews, in summary, in a tab)
- [ ] Owner can edit or hide the summary on the product screen; "Regenerate" button
- [ ] Debounce: at most one update per product per day
- [ ] Language follows the site language
- [ ] Clear note "Summary made by AI from customer reviews"

## Pro
- [ ] Topic scores (quality, fit, delivery, value) as bars
- [ ] Summary line on shop cards
- [ ] Bulk generate for the whole catalog with a cost estimate first
- [ ] Click a topic to filter reviews that mention it

## Tests
- [ ] No AI connected: nothing shown, no errors
