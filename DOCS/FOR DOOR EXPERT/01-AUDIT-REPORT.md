
# Reusable-code audit — Saya Group → Door Expert

Read-only audit of the Saya Group WordPress site (ceramic tiles and bathroom fixtures, Serbia),
carried out against the brief in `DOCS/REUSE-AUDIT-PROMPT.md`. Nothing in this site was modified.

**Bottom line:** the fit is unusually good. Saya is the same shape of business as Door Expert — a
salon catalogue where the cart is an inquiry, not a checkout — and it was built under the same
constraints: custom theme, no page builder, no Contact Form 7, no JetSmartFilters, vanilla front-end
JS. There is no page-builder debt to strip, and the only front-end jQuery in the whole theme sits in
three admin-only scripts.

Two things need saying up front, because they shape everything below.

**One.** The single biggest asset here is not the gallery or the calculator. It is the **quote cart**:
a WooCommerce install with payment removed, replaced by an inquiry that creates a real order. Door
Expert needs exactly this, and it is already solved here, including the awkward parts (price-0
products staying purchasable, cache-frozen cart badges, GDPR consent proof stored on the order).

**Two.** The notification layer is coupled to an external n8n instance. Ported verbatim, inquiries
would land silently in wp-admin with nobody emailed. Every port doc that touches this replaces the
coupling with `wp_mail()` as the default and leaves the webhook optional.

---

## 1. Summary table

| # | Component | Type | Key dependencies | Verdict | Suggested Door Expert home |
|---|---|---|---|---|---|
| 1 | **Quote cart** (checkout → inquiry, AJAX cart, badge hydration) | PHP + JS | WooCommerce; **n8n webhook** | `ADAPT (light)` | `inc/quote-cart.php`, `assets/js/cart.js` |
| 2 | **Variation matching engine** (availability, auto-select, cascade) | JS | none | `ADAPT (light)` | `assets/js/variations.js` |
| 3 | **Custom variation add-to-cart** | PHP | WooCommerce | `DROP-IN` after renaming | `inc/product-variations.php` |
| 4 | **PhotoSwipe lightbox bridge** | JS + PHP | PhotoSwipe 5.4.4 (MIT, vendored) | `DROP-IN` after renaming | `assets/js/pswp-gallery.js` |
| 5 | **Per-m² cart price correction** | PHP | WooCommerce; `_price_unit`, `_pkg_qty` meta | `ADAPT (light)` | `inc/tile-calculator.php` |
| 6 | **Tile m² calculator** | JS + PHP | same meta; `sr-RS` locale | `ADAPT (light)` | `assets/js/tile-calculator.js` |
| 7 | **Faceted filters** (URL-driven, server-side) | PHP + JS | WooCommerce; config in `wp_options` | `ADAPT (light)` | already exists in `inc/shop.php` — compare, do not replace |
| 8 | **Filter configurator plugin** (drag & drop admin) | PHP + JS + CSS | own plugin | `ADAPT (heavy)` | optional, later |
| 9 | **Wishlist** (usermeta + localStorage) | PHP + JS | WooCommerce | `ADAPT (light)` | `inc/wishlist.php` |
| 10 | **Contact form** (custom AJAX, nonce, rate limit, consent) | PHP + JS | n8n optional | `ADAPT (light)` | `inc/contact.php` |
| 11 | **Quantity stepper** | JS | none | `DROP-IN` | `assets/js/product.js` (already there) |
| 12 | **Sticky mobile CTA** | JS | IntersectionObserver | `DROP-IN` | `assets/js/product.js` |
| 13 | **Share + copy link** | JS | Web Share API | `DROP-IN` | `assets/js/product.js` |
| 14 | **Breadcrumb helper** | PHP | `product_cat` hierarchy | `ADAPT (light)` | `inc/template-tags.php` |
| 15 | **Product card partial** | PHP | Saya meta, ambient images, wishlist | `ADAPT (heavy)` | you already have `template-parts/shop/product-card.php` |
| 16 | **Search, six passes** | PHP + JS | WooCommerce | `ADAPT (heavy)` | `inc/search.php`, later |
| 17 | **Variation permalink helper** | PHP | none | `DROP-IN` | `inc/product.php` |

Nothing in the audited set is `DOES-NOT-FIT`. There is no Elementor, no CF7 and no JetSmartFilters
anywhere in the theme, so the usual porting tax does not apply.

## 2. Prioritized recommendation

Door Expert already has a shop archive with server-side filtering and a v1 simple-product PDP. Given
that, the highest value per hour of work is, in order:

### Port first — the business model

**1. Quote cart** → `02-PORT-quote-cart.md`

Everything else is a nicety; this is the funnel. It brings the checkout redirect, the inquiry
handler, the AJAX cart, price-0 purchasability and the cart badge fix in one pass. It is also the
piece with the most subtle bugs already discovered and fixed on Saya, notably the
`calculate_totals()` call before reading prices (`functions.php:3876`) without which every price in
the notification email is wrong for m²-priced products.

### Port second — the PDP upgrade you named

**2. Variation matching engine + custom add-to-cart** → `03-PORT-variations.md`

The add-to-cart handler alone justifies the port. WooCommerce's own AJAX endpoint **cannot** add a
variation that has an "Any" attribute from a custom UI; it throws before any filter can intervene.
Saya's handler resolves that server-side. You will hit this the first time a door has an
"Any colour" variation, and the failure mode is a confusing "X is a required field" error.

Take the ~90-line matching engine, leave the ~850 lines of Saya-specific UI.

### Port third — polish that shows

**3. PhotoSwipe bridge** → `04-PORT-gallery-lightbox.md`

Small, self-contained, replaces your basic lightbox with a real one. Two hours of work, visible on
every product page.

### Port fourth — only if you sell tiles by m²

**4. Per-m² pricing + calculator** → `05-PORT-tile-calculator.md`

Port the **pricing correction even if you skip the calculator**. If tile prices are entered per m²
and the cart counts boxes, you undercharge by the box size on every order. That is a revenue bug,
not a UX one.

### Do not port yet

- **Filters.** You already have server-side filtering in `inc/shop.php`. Saya's is the same
  architecture (`pre_get_posts` + `?pa_*` GET params). Read it for the faceting refinements
  described in `DOCS/BITNE FUNKCIONALNOSTI/FILTERI_ATRIBUTI.md`, but do not swap yours out.
- **Product card.** Yours exists and Saya's is entangled with Saya-only meta.
- **Search.** Six-pass search is genuinely good but it is a week of work and Door Expert will
  survive on core search for a while.

## 3. Per-component detail

The four highest-fit components have their own documents with full adapted code. This section covers
the rest.

### 7. Faceted filters (URL-driven, server-side)

- **What it does.** Attribute filters that live in the URL as `?pa_boja=bijela&pa_dimenzije-plocica=60x60`,
  applied through `pre_get_posts` on the main query. Facet counts are scoped to the products actually
  visible in the current category, options that would return zero are greyed out rather than hidden,
  and the filter's own selections are excluded from its counts so the user can widen a choice.
- **Where.** `functions.php:669-1007` (AJAX refresh), `:1008-1051` (`pre_get_posts`),
  `:1052-1170` (robots + canonical for filtered URLs), `:4924+` (`saya_fc_attrs_for_cat()`),
  `js/product-listing.js` (33 KB).
- **Type.** PHP + JS.
- **Dependencies.** WooCommerce. Which attributes appear per category comes from the
  `saya_filter_configs` option, but `functions.php:4926` falls back to a theme-side default, so
  **the plugin is not required** — it is only the admin UI for editing that option.
- **Coupling.** Moderate. The query logic is clean and generic; the markup is Saya's.
- **Data mapping.** Reads any `pa_*` taxonomy from `$_GET`; nothing hardcoded. Works with
  `pa_dimenzije-vrata` unchanged.
- **Verdict.** `ADAPT (light)`, but **you already have this**. Compare rather than replace.
- **Worth stealing specifically:** the SEO handling at `:1052-1170`. Filtered URLs get
  `noindex,follow` and a canonical back to the clean category. Without it, every filter combination
  becomes an indexable near-duplicate. This is the part most sites get wrong.
- **Rework.** Prefix, tabs, escaping.
- **Home.** `inc/shop.php`.

### 9. Wishlist

- **What it does.** "Save for a project" list. Logged-in users get it in `usermeta`, guests in
  `localStorage`, and there is an endpoint to move a saved item straight into the cart without a
  page redirect.
- **Where.** `functions.php:1449-1495` (add/remove/get), `:5377-5437` (wishlist → cart),
  `js/wishlist.js` (22 KB), `page-lista-zelja.php`.
- **Dependencies.** WooCommerce for the cart bridge. No jQuery.
- **Verdict.** `ADAPT (light)`.
- **Honest weak spot.** The merge between the guest `localStorage` list and the user's `usermeta`
  list on login is the fragile part of this feature. If you port it, decide the merge rule
  deliberately (union? server wins? client wins?) rather than inheriting it.
- **Home.** `inc/wishlist.php`.

### 10. Contact form

- **What it does.** Custom AJAX handler replacing Contact Form 7: nonce, honeypot, per-IP rate
  limiting, mandatory consent checkbox with the **text of the consent stored as proof**, then email
  plus optional webhook.
- **Where.** `functions.php:1496-1629`, `js/contact.js`.
- **Verdict.** `ADAPT (light)`. Door Expert forbids CF7, so you need something like this anyway.
- **Worth stealing specifically:** storing the consent *text and version*, not just a boolean
  (`functions.php:1600` and the equivalent on orders at `:3973-3978`). A stored `1` proves nothing if
  the wording changes later. This is the correct GDPR pattern and it costs three lines.
- **Rework.** The rate limiter (`door_expert_rate_limit()`) is already extracted in
  `02-PORT-quote-cart.md`; reuse it rather than duplicating.
- **Home.** `inc/contact.php`.

### 11-13. PDP micro-interactions

Quantity stepper (`js/product-single.js:725-772`), sticky mobile CTA driven by an
`IntersectionObserver` on the add-to-cart button (`:1004-1027`), and share with a clipboard fallback
(`:1028-1080`). All three are short, dependency-free and `DROP-IN`. Your `assets/js/product.js`
already has a stepper; the other two are worth lifting as-is.

### 14. Breadcrumb helper

- **Where.** `functions.php:2579-2840`.
- Builds a `product_cat` breadcrumb trail with schema markup, handling the hierarchical case.
- **Verdict.** `ADAPT (light)`. Rank Math can emit breadcrumbs too; check what you already get from
  it before porting.

### 15. Product card partial

- **Where.** `template-parts/product-card.php` (481 lines), `css/product-card.css`.
- **Verdict.** `ADAPT (heavy)`. Depends on Saya's ambient-image sizes, wishlist state, brand SKU
  rules and collection meta. You have your own card.
- **Worth reading:** `DOCS/BITNE FUNKCIONALNOSTI/AMBIJENT_SLIKE_U_GRIDU.md` documents a real bug
  worth knowing about — a landscape image in a square `object-fit: cover` frame renders blurry on
  desktop because `srcset` picks by width while the square crop is decided by height. If Door Expert
  uses square product frames with non-square sources, you will hit the same thing.

### 16. Search, six passes

- **Where.** `functions.php:1630-2133` (REST endpoint for autocomplete), `:2134-2417` (search page
  ID collection), `:2418-2547` (category matching), `search.php`, `js/search-page.js`.
- Six ordered passes with a de-duplicated union: category → title + short description → brand →
  brand + remainder → attribute → SKU.
- **Verdict.** `ADAPT (heavy)`. Excellent but large.
- **Documented limits, quoting `DOCS/BITNE FUNKCIONALNOSTI/PRETRAGA.md`:** no fuzzy matching, no
  synonyms, no Cyrillic. Also, the long description is deliberately **not** searched, because
  cross-sell sentences inside it produced wrong hits. That decision is worth inheriting.

### 17. Variation permalink helper

- **Where.** `functions.php:2762` (`saya_variation_permalink()`), doc block from `:2736`.
- Returns a parent URL with every variation attribute as a query parameter, so a card linking to a
  specific colour lands on the PDP with that colour preselected. Deliberately generic: it iterates
  whatever variation attributes exist rather than hardcoding one.
- **Verdict.** `DROP-IN`. Small, useful, no dependencies.
- **Worth reading the comment at `:2743-2748`** — it documents the bug that motivated it (a card
  hardcoded one attribute, so preselection silently failed for every other attribute the client
  later added).

## 4. Red flags

| # | Flag | Impact | What to do |
|---|---|---|---|
| 1 | **n8n coupling.** `functions.php:3878-3881` disables WooCommerce's own emails; `:3991-4059` posts to a webhook. Same pattern in the contact form at `:1608-1620`. | Ported verbatim, **nobody is notified of an inquiry**. | Use the `wp_mail()` default in `02-PORT-quote-cart.md`; treat the webhook as optional. |
| 2 | **Config lives outside the repo.** `SAYA_N8N_WEBHOOK`, `SAYA_N8N_SECRET`, `SAYA_CONSENT_FORM_VERSION` are `wp-config.php` constants. | Snippets referencing them fail silently if undefined. | Every adapted snippet guards with `defined()`. Keep that. |
| 3 | **`functions.php` is 5788 lines, ~40 sections.** | Copying wholesale would import the anti-pattern. | Port by section into separate `inc/*.php` files, which is what Door Expert already does. |
| 4 | **GSAP + Lenis load from CDN** (`functions.php:396-398`, cdnjs and unpkg). | Third-party runtime dependency and a privacy consideration. | Not needed by anything on the port list. If you ever want the scroll effects, vendor them locally like PhotoSwipe already is. |
| 5 | **`?ver=` stripping is a live footgun.** `functions.php:4085` (`saya_remove_version_strings()`) strips version strings for security, with an explicit exception for this theme's own assets. | Remove the exception and every CSS/JS change needs a manual cache purge. | If you port the hardening, port the exception with it. The comment explaining why is at `:4081-4083`. |
| 6 | **Taxonomy slug differs.** Saya uses `pa_dimenzije-plocice`, Door Expert uses `pa_dimenzije-plocica`. | Silent no-match, filters return nothing. | Search and replace on the way over. |
| 7 | **Cart badge needs JS hydration** because LiteSpeed caches the header (`functions.php:5350-5359`). | If Door Expert also runs full-page cache, a server-rendered badge will be wrong. | Ship the hydration script from `02-PORT-quote-cart.md`. |
| 8 | **`gettext` filter on every string** (`functions.php:3806-3811`) to rename one button. | Runs on every translation call site-wide. | Acceptable, but prefer the WooCommerce-specific filters where they exist. |
| 9 | **The inquiry handler is a 240-line function.** | Hard to test or extend. | Already split into three functions in the port. |
| 10 | **Wishlist localStorage / usermeta merge** is the weak point of that feature. | Silent data loss on login. | Decide the merge rule explicitly if you port it. |

### Licensing

Everything in `wp-theme/` and `wp-plugins/` is original work for this project and safe to reuse. The
one third-party dependency in the port set is **PhotoSwipe 5.4.4**, vendored at
`wp-theme/js/vendor/photoswipe/`, **MIT licensed**, © 2024 Dmytro Semenov. MIT is GPL-compatible;
keep the licence header in the file. GSAP and Lenis are loaded from CDN and are **not** part of any
recommended port — worth noting because GSAP's licence is not MIT and would need review if you ever
did vendor it.

## 5. Bonus tier — valuable, outside the original brief

These were not on the list but are the kind of thing that costs a week to get right on a second site.
No code here; treat this as a pointer list.

| Component | Where | Why it matters for Door Expert |
|---|---|---|
| **Cookie consent + Google Consent Mode v2** | `functions.php:5595+`, `js/cookie-consent.js`, `css/cookie-consent.css`, `DOCS/BITNE FUNKCIONALNOSTI/COOKIE_CONSENT.md` | Three categories, Consent Mode wired by hand with no GTM. Gating is deliberately **client-side** because full-page cache makes server-side gating unreliable. Montenegro follows GDPR-style rules; this is a solved problem sitting here. |
| **Security hardening** | `functions.php:4069-4150` | Removes version disclosure, RSD/WLW links, hardens XML-RPC. Read note 5 in the red flags before copying. |
| **SEO robots + canonical for filtered URLs** | `functions.php:1052-1170`, `DOCS/SEO_ROBOTS_NOINDEX.md` | Filter and sort URLs get `noindex,follow` plus a canonical to the clean category. Prevents thousands of near-duplicate URLs. Genuinely the highest-value SEO item in this repo. |
| **Rate limiter** | `functions.php` `saya_rate_limit()` | Transient-based per-IP throttle. Already extracted into `02-PORT-quote-cart.md`. |
| **Consent proof pattern** | `functions.php:1600`, `:3973-3978` | Stores the consent text and version, not a boolean. |
| **Cron without crontab** | `wp-plugins/saya-cron-runner/`, `DOCS/BITNE FUNKCIONALNOSTI/CRON_RUNNER.md` | Shared hosting with no SSH, no WP-CLI and no crontab: an external pinger hits a token-protected endpoint that drains Action Scheduler and WP-Cron. If Door Expert is on similar hosting, this is the answer. |
| **sRGB image pipeline** | `wp-plugins/saya-srgb/`, `DOCS/to_srgb.py` | Fixes washed-out colours when ImageMagick has no ICC support. Relevant for any catalogue where colour fidelity sells the product, which is exactly tiles and doors. |
| **Ambient image sizing** | `DOCS/BITNE FUNKCIONALNOSTI/AMBIJENT_SLIKE_U_GRIDU.md` | The `srcset`-picks-by-width blur bug described in §15. |
| **Performance pass** | `DOCS/BITNE FUNKCIONALNOSTI/PERFORMANSE.md` | Which WooCommerce and block styles can be dequeued for guests, what LiteSpeed minify settings are safe, and which ones broke things. |
| **Structured data** | `DOCS/Claude_Code_Instrukcije_Product_Schema.md`, `DOCS/QA_Schema_Izvestaj.md` | Product schema decisions and the QA report against them. Door Expert uses Rank Math too. |

## 6. Method and honesty notes

- Every `file:line` in this report was verified against the working tree at the time of writing.
  They will drift as this site changes; re-check with `grep -n` before relying on an exact number.
- The adapted snippets in `02` through `05` were syntax-checked (`php -l`, `node --check`) but
  **have never been run inside Door Expert**, which is not present here. Treat them as reviewed
  drafts, not tested code.
- Where the original is weak, the port fixes it and says so rather than transcribing the weakness.
  The three deliberate improvements are: `wp_mail()` instead of a hard n8n dependency, the inquiry
  handler split into three functions, and `findVariation()` reused inside `isComboAvailable()`
  instead of the duplicated loop at `product-single.js:1309-1324`.


