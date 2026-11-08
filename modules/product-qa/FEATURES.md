# Product Q&A: Feature Spec

- **ID**: `product-qa` · **Plugin**: ShopGlut · **Code**: `modules/product-qa/module.php`
- **Benchmarked against**: YITH WooCommerce Questions and Answers, Product Questions & Answers for WooCommerce (Webby), Dokan / Amazon Q&A pattern

## Built (7 Nov)
- [x] Questions & Answers tab on product pages
- [x] Answer from Comments; moderation and spam checks
- [x] Email to the shop for new questions

## Free: full
- [ ] Question list with search and "load more"; count in the tab title
- [ ] Ask form: logged-in only or guests with name + email; honeypot + rate limit
- [ ] Admin screen "Questions": unanswered first, answer inline, approve / delete
- [ ] Email to the customer when their question is answered
- [ ] "Helpful" votes on answers, sorted by votes
- [ ] "Answered by the store" badge
- [ ] Question schema (QAPage) when SEOGlut is not active
- [ ] Shortcode `[shopglut_product_qa id=""]` for custom layouts

## Pro
- [ ] Verified buyers can answer; "Ask previous buyers" email to customers who bought the product
- [ ] AI draft answer for the admin from product facts (owner sends)
- [ ] Question count on shop cards
- [ ] Import / export questions (CSV)
- [ ] Report: questions per product, average answer time

## Tests
- [ ] Guest question with moderation on, answer email received, spam blocked
