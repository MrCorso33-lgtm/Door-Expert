# Saya Group → Door Expert — architecture and logic (bundle 1 of 2)

Eight documents of the reuse audit, concatenated for easy transfer.

**There is a second bundle.** `SAYA-TO-DOOR-EXPERT-UI-BUNDLE.md` covers presentation: texture
swatches, the ambient-first product card, trust blocks, project hotspots. Read this one first.

**To the agent receiving this:** each original document is delimited below by a
`<!-- ===== FILE: <name> ===== -->` marker. Split it back into eight files under
`DOCS/FOR DOOR EXPERT/` in the Door Expert repo, or just read it straight through.
Reading order is the numeric order of the files.

Source site: Saya Group (ceramic tiles and bathroom fixtures, Serbia), custom WordPress theme.
Target site: Door Expert (doors, Spanish tiles, decorative basins; Podgorica, Montenegro).

The audit was read-only. No file on the source site was modified. Snippets are syntax-checked but
have never run inside Door Expert.

One component travels as working code rather than as a snippet: the filter configurator plugin,
`portable-kit/plugin/wc-filter-configurator/` in the Saya repo. Document `07` explains what to do
with it; copy the folder across alongside this bundle.

---




<!-- ===== FILE: 00-README.md ===== -->

# For Door Expert — reuse package (1 of 2)

Output of the reuse audit requested in [`../REUSE-AUDIT-PROMPT.md`](../REUSE-AUDIT-PROMPT.md).

Door Expert is a second, separate custom WordPress site (doors, Spanish ceramic tiles, decorative
basins; Podgorica, Montenegro). This folder is what an agent working **inside the Door Expert repo**
needs in order to carry proven code across from Saya Group without re-deriving it.

Written in English because it is read by an agent in the other repo. Code comments and user-facing
strings inside the snippets are in ijekavica, with no em dash, per Door Expert's conventions.

---

## Read in this order

| File | What it is |
|---|---|
| [`01-AUDIT-REPORT.md`](01-AUDIT-REPORT.md) | **Start here.** Summary table of every reusable component, portability verdicts, a prioritized recommendation, per-component detail, red flags, and a bonus tier of things outside the original brief. |
| [`02-PORT-quote-cart.md`](02-PORT-quote-cart.md) | WooCommerce with payment removed: checkout redirect, inquiry handler that creates a real order, AJAX cart, price-0 purchasability, cart badge hydration. **Highest value, port first.** |
| [`03-PORT-variations.md`](03-PORT-variations.md) | Variable products: the variation matching engine and the server-side add-to-cart handler that makes a custom pill UI work with WooCommerce at all. |
| [`04-PORT-gallery-lightbox.md`](04-PORT-gallery-lightbox.md) | PhotoSwipe v5 bridge, ES-module enqueue, real image dimensions. |
| [`05-PORT-tile-calculator.md`](05-PORT-tile-calculator.md) | Tile m² calculator plus, more importantly, the per-m² cart pricing correction. |
| [`06-DATA-MODEL-custom-fields.md`](06-DATA-MODEL-custom-fields.md) | Every custom field on a Saya product, verified against the **live** site: which six come from JetEngine, which are plain theme code, and which two look alive in the database but are abandoned. Read before `02`–`05`, which reference these keys. |
| [`07-PLUGIN-filter-configurator.md`](07-PLUGIN-filter-configurator.md) | **Corrects the audit.** The filter configurator is listed there as `ADAPT (heavy)`, but that verdict was written against the Saya-branded plugin; a de-branded standalone already existed. It is a `DROP-IN` that configures the sidebar and leaves your query engine alone. |

Each `PORT-*` document has the same shape: what it does → Saya source with `file:line` →
dependencies and coupling → data-model mapping → **adapted code** → wiring → what to verify.
`07` is the exception: nothing needs extracting there, so it is integration advice instead.

## There is a second package

This one covers **architecture and logic**. A separate package, `10` through `13`
(bundled as `SAYA-TO-DOOR-EXPERT-UI-BUNDLE.md`), covers **presentation**: texture swatches that use
the product photo itself, the ambient-first product card and the `srcset` trap that comes with it,
trust and delivery blocks, per-variation cross-sell, project hotspots. Start with
[`10-UI-README.md`](10-UI-README.md) once you have read this one.

## What was audited

The whole Saya Group theme (`wp-theme/`, 5788-line `functions.php` across ~40 sections, 20 front-end
JS files, 37 stylesheets) and 12 custom plugins under `wp-plugins/`.

## What you can rely on

- Every `file:line` was verified against the working tree when written.
- Every PHP snippet passes `php -l`; every JS snippet passes `node --check` (the ES module against
  `--input-type=module`).
- Verdicts are honest. Where the original is weak, buggy or entangled, the document says so and
  either fixes it on the way over or tells you to leave it behind.

## What you cannot rely on

- **None of the adapted code has ever run inside Door Expert.** That repo was not available during
  the audit. These are reviewed drafts, not tested code. Every document ends with a verification
  checklist for exactly this reason.
- Line numbers drift. Re-check with `grep -n` before trusting an exact number.

## The one thing not to miss

`saya_handle_inquiry_submit()` disables WooCommerce's own emails and delegates notification to an
external n8n instance. Ported verbatim without n8n, **inquiries arrive silently and nobody is
emailed**. `02-PORT-quote-cart.md` inverts this: `wp_mail()` is the default, the webhook is optional.

## Deliberately left out

- No porting was performed into any other project; this is audit and extraction advice only, per the
  brief.
- No Saya files were modified.
- Components you already have (shop archive filtering, product card) are assessed but not rewritten
  for you — the report says compare, not replace. The filter **configurator** is a separate matter
  and does not conflict with that: see [`07-PLUGIN-filter-configurator.md`](07-PLUGIN-filter-configurator.md).



<!-- ===== FILE: 01-AUDIT-REPORT.md ===== -->

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
| 8 | **Filter configurator plugin** (drag & drop admin) | PHP + JS + CSS | own plugin | ~~`ADAPT (heavy)`~~ → `DROP-IN` | **superseded**, see [`07-PLUGIN-filter-configurator.md`](07-PLUGIN-filter-configurator.md) |
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
  This still holds. It concerns the **query engine**, which the filter configurator plugin does not
  touch: that plugin owns only the sidebar. Row 8 above was revised for this reason.
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



<!-- ===== FILE: 02-PORT-quote-cart.md ===== -->

# PORT 02 — Quote cart (WooCommerce without payment)

**Verdict: `ADAPT (light)` · Priority 1**

This is the single most valuable thing in the Saya codebase for Door Expert, because it is the same
business model: WooCommerce runs the catalogue and the cart, but nothing is ever paid online. The
customer fills a short form, a real WC order is created with status `on-hold`, and sales follows up
with a formal offer.

---

## 1. What it does

- Replaces the WooCommerce checkout URL with a custom page (`/upit/` on Saya).
- Renames every "Proceed to checkout" string to an inquiry CTA.
- On submit: validates, creates a real `WC_Order` (so the whole WooCommerce admin, order notes,
  reporting and customer history keep working), sets it `on-hold`, stores GDPR consent proof,
  notifies sales, and empties the cart.
- Suppresses WooCommerce's own transactional emails, because the notification is sent by the
  integration layer instead.
- Cart page updates quantity and removes items over AJAX, no reload.
- Products with price `0` stay purchasable, so "price on request" items can enter the cart.
- The header cart badge is hydrated over AJAX because full-page cache freezes the server-rendered
  number.

## 2. Saya source

| Piece | Location |
|---|---|
| Checkout redirect + button text | `wp-theme/functions.php:3797-3811` |
| `/upit/` CSS enqueue | `wp-theme/functions.php:3813-3824` |
| Inquiry submit AJAX | `wp-theme/functions.php:3826-4066` |
| Rate limit helper | `wp-theme/functions.php:681` (`saya_rate_limit()`, used at `:1506` and `:3843`) |
| Cart qty AJAX | `wp-theme/functions.php:4395-4428` |
| Cart remove AJAX | `wp-theme/functions.php:4430-4452` |
| Price-0 purchasable | `wp-theme/functions.php:4454-4463` |
| Cart badge endpoint | `wp-theme/functions.php:5350-5374` |
| Inquiry form page | `wp-theme/page-upit.php` (462 lines) |
| Cart template | `wp-theme/woocommerce/cart/cart.php` (350 lines) |
| Cart JS | `wp-theme/js/cart.js` (220 lines) |
| Badge hydration JS | `wp-theme/js/main.js:701-830` |
| Styles | `wp-theme/css/cart.css`, `wp-theme/css/upit.css` |

## 3. Dependencies and coupling

| Dependency | Notes |
|---|---|
| WooCommerce | Core. `wc_create_order()`, `WC()->cart`, `wc_price()`, notices. |
| jQuery | **None.** `cart.js` and the badge hydration are vanilla. |
| Page builder / CF7 / Jet* | **None.** Nothing to strip. |
| n8n webhook | **Yes, and this is the one real blocker.** See below. |
| Site constants | `SAYA_N8N_WEBHOOK`, `SAYA_N8N_SECRET` in `wp-config.php`, outside the repo. |
| Saya-only helpers | `saya_pkg_data()`, `saya_price_unit()`, `saya_display_code()`, `saya_brand_hides_sku()`, `saya_consent_form_text()`, `SAYA_CONSENT_FORM_VERSION`. All optional for the core flow; the adapted version below drops or guards every one of them. |

### The n8n coupling

`saya_handle_inquiry_submit()` turns WooCommerce's own emails off at `functions.php:3878-3881` and
posts a JSON payload to an n8n webhook at `:3991-4059`. If you port it verbatim without n8n,
**nobody gets notified** — the order lands in wp-admin silently.

The adapted code below inverts this: `wp_mail()` is the default and always works, the webhook is
optional and fires only when the constant is defined. That is a deliberate improvement, not a
transcription.

## 4. Data-model mapping

Nothing in the core flow depends on Saya's taxonomies. It reads only from `WC()->cart` and standard
product methods. The one mapping to make is the brand taxonomy:

| Saya | Door Expert |
|---|---|
| `product_brand` (`functions.php:3905`) | `product_brand` — same taxonomy, no change |
| `saya_display_code()` (custom SKU display) | plain `$product->get_sku()` |
| `saya_price_unit()` / `saya_pkg_data()` (m² selling unit) | only needed if you sell tiles by m² — see `05-PORT-tile-calculator.md` |

## 5. Adapted code

### `inc/quote-cart.php`

```php
<?php
/**
 * Quote cart, WooCommerce bez online plaćanja.
 *
 * Korpa radi normalno, ali umjesto plaćanja kupac šalje upit. Kreira se pravi
 * WC_Order sa statusom on-hold, pa cijela WooCommerce administracija,
 * bilješke uz narudžbu i istorija kupca nastavljaju da rade.
 *
 * @package Door_Expert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stranica na koju vodi dugme iz korpe.
 */
function door_expert_quote_page_url() {
	return home_url( '/upit/' );
}

add_filter(
	'woocommerce_get_checkout_url',
	'door_expert_quote_page_url'
);

add_filter(
	'woocommerce_order_button_text',
	function () {
		return __( 'Pošalji upit prodaji', 'door-expert' );
	}
);

/**
 * WooCommerce na više mjesta ispisuje "Proceed to checkout" mimo filtera iznad.
 */
add_filter(
	'gettext',
	function ( $translated, $original ) {
		if ( 'Proceed to checkout' === $original ) {
			return __( 'Pošalji upit prodaji', 'door-expert' );
		}
		return $translated;
	},
	20,
	2
);

/**
 * Proizvodi bez cijene ostaju kupljivi, jer "cijena na upit" mora u korpu.
 */
add_filter(
	'woocommerce_is_purchasable',
	function ( $purchasable, $product ) {
		if ( ! $purchasable && 0.0 === (float) $product->get_price() ) {
			return true;
		}
		return $purchasable;
	},
	10,
	2
);

/**
 * Ograničenje broja zahtjeva po IP adresi.
 *
 * @param string $prefix  Ključ transienta.
 * @param int    $limit   Dozvoljen broj zahtjeva.
 * @param int    $window  Prozor u sekundama.
 * @return bool True ako je zahtjev dozvoljen.
 */
function door_expert_rate_limit( $prefix, $limit, $window ) {
	$ip = isset( $_SERVER['REMOTE_ADDR'] )
		? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) )
		: '';

	if ( '' === $ip ) {
		return true;
	}

	$key   = $prefix . md5( $ip );
	$count = (int) get_transient( $key );

	if ( $count >= $limit ) {
		return false;
	}

	set_transient( $key, $count + 1, $window );

	return true;
}

add_action( 'wp_ajax_door_expert_submit_inquiry', 'door_expert_handle_inquiry_submit' );
add_action( 'wp_ajax_nopriv_door_expert_submit_inquiry', 'door_expert_handle_inquiry_submit' );

/**
 * Prima formu sa /upit/ stranice, pravi narudžbu i obavještava prodaju.
 */
function door_expert_handle_inquiry_submit() {
	check_ajax_referer( 'door_expert_inquiry_nonce', 'nonce' );

	// Honeypot. Polje je sakriveno u formi, boti ga popunjavaju.
	if ( ! empty( $_POST['website'] ) ) {
		wp_send_json_error( __( 'Greška.', 'door-expert' ) );
	}

	// Saglasnost. Klijentskoj validaciji se ne vjeruje.
	$consent = isset( $_POST['consent'] ) ? (string) wp_unslash( $_POST['consent'] ) : '';
	if ( '1' !== $consent ) {
		wp_send_json_error( __( 'Potrebna je saglasnost za obradu podataka.', 'door-expert' ) );
	}

	if ( ! door_expert_rate_limit( 'de_rl_inquiry_', 5, HOUR_IN_SECONDS ) ) {
		wp_send_json_error( __( 'Previše zahtjeva. Pokušajte ponovo za sat vremena.', 'door-expert' ), 429 );
	}

	$name      = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
	$email     = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$phone     = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
	$city      = sanitize_text_field( wp_unslash( $_POST['city'] ?? '' ) );
	$address   = sanitize_text_field( wp_unslash( $_POST['address'] ?? '' ) );
	$note      = sanitize_textarea_field( wp_unslash( $_POST['note'] ?? '' ) );
	$buyer     = sanitize_key( wp_unslash( $_POST['buyer_type'] ?? 'fizicko' ) );
	$company   = sanitize_text_field( wp_unslash( $_POST['company'] ?? '' ) );
	$pib       = sanitize_text_field( wp_unslash( $_POST['pib'] ?? '' ) );
	$is_b2b    = ( 'b2b' === $buyer );

	if ( ! $name || ! $email || ! is_email( $email ) || ! $phone || ! $city || ! $address ) {
		wp_send_json_error( __( 'Molimo popunite sva obavezna polja.', 'door-expert' ) );
	}

	if ( $is_b2b && ! $company ) {
		wp_send_json_error( __( 'Molimo unesite naziv firme.', 'door-expert' ) );
	}

	if ( ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() ) {
		wp_send_json_error( __( 'Korpa je prazna.', 'door-expert' ) );
	}

	/*
	 * Totali se moraju izračunati PRIJE čitanja cijena. U admin-ajax zahtjevu
	 * woocommerce_before_calculate_totals još nije odrađen, pa get_price()
	 * vraća sirovu cijenu. Ovo popravlja i cijene u mejlu i total narudžbe.
	 */
	WC()->cart->calculate_totals();

	// WooCommerce ne šalje svoje mejlove, notifikacija ide našim putem.
	add_filter( 'woocommerce_email_enabled_new_order', '__return_false' );
	add_filter( 'woocommerce_email_enabled_customer_on_hold_order', '__return_false' );
	add_filter( 'woocommerce_email_enabled_admin_new_order', '__return_false' );

	$products    = door_expert_collect_cart_products();
	$order       = wc_create_order();
	$name_parts  = explode( ' ', $name, 2 );

	foreach ( WC()->cart->get_cart() as $item ) {
		$order->add_product( $item['data'], $item['quantity'] );
	}

	$order->set_billing_first_name( $name_parts[0] );
	$order->set_billing_last_name( $name_parts[1] ?? '' );
	$order->set_billing_email( $email );
	$order->set_billing_phone( $phone );
	$order->set_billing_city( $city );
	$order->set_billing_address_1( $address );

	if ( $is_b2b && $company ) {
		$order->set_billing_company( $company );
	}

	$order->set_payment_method( 'inquiry' );
	$order->set_payment_method_title( __( 'Upit, predračun', 'door-expert' ) );

	$status_note = $is_b2b
		? __( 'B2B upit primljen putem sajta.', 'door-expert' ) . ( $company ? ' Firma: ' . $company : '' ) . ( $pib ? ' PIB: ' . $pib : '' )
		: __( 'Upit primljen putem sajta.', 'door-expert' );

	$order->set_status( 'on-hold', $status_note );

	if ( $note ) {
		$order->add_order_note( __( 'Napomena klijenta: ', 'door-expert' ) . $note, false, false );
	}

	/*
	 * Dokaz o saglasnosti. Čuva se TEKST koji je posjetilac vidio u trenutku
	 * slanja, ne samo "1", jer dokaz ne vrijedi ako se tekst kasnije promijeni.
	 */
	$order->update_meta_data( '_door_expert_consent_text', door_expert_consent_text() );
	$order->update_meta_data( '_door_expert_consent_ts', current_time( 'c' ) );
	$order->update_meta_data( '_door_expert_consent_ip', sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) ) );

	$order->calculate_totals();
	$order->save();

	$payload = array(
		'tip'             => $is_b2b ? 'b2b' : 'fizicko',
		'ime'             => $name,
		'email'           => $email,
		'telefon'         => $phone,
		'grad'            => $city,
		'adresa'          => $address,
		'kompanija'       => $company,
		'pib'             => $pib,
		'poruka'          => $note,
		'proizvodi'       => $products,
		'order_broj'      => $order->get_order_number(),
		'order_admin_url' => admin_url( 'post.php?post=' . $order->get_id() . '&action=edit' ),
		'vrijeme'         => current_time( 'd.m.Y. H:i' ),
	);

	door_expert_notify_inquiry( $payload );

	WC()->cart->empty_cart();

	wp_send_json_success( array( 'order_num' => $order->get_order_number() ) );
}

/**
 * Skuplja stavke korpe u ravan niz pogodan za mejl i webhook.
 *
 * @return array
 */
function door_expert_collect_cart_products() {
	$rows = array();

	foreach ( WC()->cart->get_cart() as $cart_item ) {
		$product = $cart_item['data'];
		$qty     = (int) $cart_item['quantity'];
		$price   = (float) $product->get_price();
		$total   = $price > 0 ? $price * $qty : null;

		$brand  = '';
		$terms  = get_the_terms( $cart_item['product_id'], 'product_brand' );
		if ( $terms && ! is_wp_error( $terms ) ) {
			$brand = $terms[0]->name;
		}

		$attrs = array();
		foreach ( wc_get_product_variation_attributes( $cart_item['variation_id'] ?? 0 ) as $key => $value ) {
			if ( ! $value ) {
				continue;
			}
			$label           = wc_attribute_label( str_replace( 'attribute_', '', $key ) );
			$attrs[ $label ] = $value;
		}

		$rows[] = array(
			'naziv'       => $product->get_name(),
			'brend'       => $brand,
			'sifra'       => $product->get_sku(),
			'url'         => get_permalink( $cart_item['product_id'] ),
			'atributi'    => $attrs,
			'kolicina'    => $qty,
			'cijena'      => null !== $total
				? html_entity_decode( wp_strip_all_tags( wc_price( $price ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' )
				: __( 'Cijena na upit', 'door-expert' ),
			'cijena_ukupno' => null !== $total
				? html_entity_decode( wp_strip_all_tags( wc_price( $total ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' )
				: __( 'Cijena na upit', 'door-expert' ),
		);
	}

	return $rows;
}

/**
 * Tekst saglasnosti koji se čuva uz narudžbu kao dokaz.
 *
 * @return string
 */
function door_expert_consent_text() {
	return __( 'Saglasan sam da Door Expert obrađuje moje podatke radi izrade i slanja ponude.', 'door-expert' );
}

/**
 * Obavještenje prodaji. Mejl je uvijek, webhook samo ako je podešen.
 *
 * @param array $payload Podaci upita.
 */
function door_expert_notify_inquiry( $payload ) {
	$webhook = defined( 'DOOR_EXPERT_WEBHOOK' ) ? DOOR_EXPERT_WEBHOOK : '';
	$secret  = defined( 'DOOR_EXPERT_WEBHOOK_SECRET' ) ? DOOR_EXPERT_WEBHOOK_SECRET : '';

	if ( $webhook ) {
		wp_remote_post(
			$webhook,
			array(
				'headers'     => array(
					'Content-Type'    => 'application/json',
					'X-Door-Expert-Secret' => $secret,
				),
				'body'        => wp_json_encode( $payload ),
				'data_format' => 'body',
				'timeout'     => 5,
				'blocking'    => false,
			)
		);
	}

	$to      = apply_filters( 'door_expert_inquiry_recipient', get_option( 'admin_email' ) );
	$subject = sprintf(
		/* translators: %s: order number */
		__( 'Novi upit sa sajta, narudžba %s', 'door-expert' ),
		$payload['order_broj']
	);

	$lines = array(
		__( 'Ime:', 'door-expert' ) . ' ' . $payload['ime'],
		__( 'Email:', 'door-expert' ) . ' ' . $payload['email'],
		__( 'Telefon:', 'door-expert' ) . ' ' . $payload['telefon'],
		__( 'Grad:', 'door-expert' ) . ' ' . $payload['grad'],
		__( 'Adresa:', 'door-expert' ) . ' ' . $payload['adresa'],
	);

	if ( $payload['kompanija'] ) {
		$lines[] = __( 'Firma:', 'door-expert' ) . ' ' . $payload['kompanija'] . ( $payload['pib'] ? ' (PIB ' . $payload['pib'] . ')' : '' );
	}

	if ( $payload['poruka'] ) {
		$lines[] = '';
		$lines[] = __( 'Napomena:', 'door-expert' ) . ' ' . $payload['poruka'];
	}

	$lines[] = '';
	$lines[] = __( 'Stavke:', 'door-expert' );

	foreach ( $payload['proizvodi'] as $row ) {
		$attr_text = $row['atributi'] ? ' (' . implode( ', ', array_map(
			function ( $k, $v ) {
				return $k . ': ' . $v;
			},
			array_keys( $row['atributi'] ),
			$row['atributi']
		) ) . ')' : '';

		$lines[] = sprintf(
			'- %s%s x%d, %s',
			$row['naziv'],
			$attr_text,
			$row['kolicina'],
			$row['cijena_ukupno']
		);
	}

	$lines[] = '';
	$lines[] = __( 'Narudžba u administraciji:', 'door-expert' ) . ' ' . $payload['order_admin_url'];

	wp_mail( $to, $subject, implode( "\n", $lines ) );
}

add_action( 'wp_ajax_door_expert_update_cart_qty', 'door_expert_update_cart_qty' );
add_action( 'wp_ajax_nopriv_door_expert_update_cart_qty', 'door_expert_update_cart_qty' );

/**
 * Mijenja količinu stavke u korpi bez osvježavanja stranice.
 */
function door_expert_update_cart_qty() {
	check_ajax_referer( 'door_expert_cart', 'nonce' );

	$key = sanitize_text_field( wp_unslash( $_POST['cart_key'] ?? '' ) );
	$qty = absint( wp_unslash( $_POST['qty'] ?? 0 ) );

	if ( ! $key ) {
		wp_send_json_error( 'no_key' );
	}

	WC()->cart->set_quantity( $key, $qty, true );

	$cart          = WC()->cart->get_cart();
	$item_subtotal = '';

	if ( 0 < $qty && isset( $cart[ $key ] ) ) {
		$item          = $cart[ $key ];
		$item_subtotal = wc_price( $item['line_total'] + ( $item['line_tax'] ?? 0 ) );
	}

	wp_send_json_success(
		array(
			'removed'       => 0 === $qty,
			'item_subtotal' => $item_subtotal,
			'cart_subtotal' => WC()->cart->get_cart_subtotal(),
			'cart_total'    => html_entity_decode( wp_strip_all_tags( WC()->cart->get_total() ) ),
			'cart_count'    => WC()->cart->get_cart_contents_count(),
		)
	);
}

add_action( 'wp_ajax_door_expert_remove_cart_item', 'door_expert_remove_cart_item' );
add_action( 'wp_ajax_nopriv_door_expert_remove_cart_item', 'door_expert_remove_cart_item' );

/**
 * Uklanja stavku iz korpe.
 */
function door_expert_remove_cart_item() {
	check_ajax_referer( 'door_expert_cart', 'nonce' );

	$key = sanitize_text_field( wp_unslash( $_POST['cart_key'] ?? '' ) );

	if ( ! $key ) {
		wp_send_json_error( 'no_key' );
	}

	WC()->cart->remove_cart_item( $key );

	wp_send_json_success(
		array(
			'cart_subtotal' => WC()->cart->get_cart_subtotal(),
			'cart_total'    => html_entity_decode( wp_strip_all_tags( WC()->cart->get_total() ) ),
			'cart_count'    => WC()->cart->get_cart_contents_count(),
			'cart_empty'    => WC()->cart->is_empty(),
		)
	);
}

add_action( 'wp_ajax_door_expert_get_cart_count', 'door_expert_get_cart_count' );
add_action( 'wp_ajax_nopriv_door_expert_get_cart_count', 'door_expert_get_cart_count' );

/**
 * Živa vrijednost brojača za hidrataciju badža.
 *
 * Header renderuje badž server-side, ali full-page keš servira zamrznutu
 * vrijednost iz trenutka keširanja. admin-ajax.php se ne keširа, pa ovaj
 * endpoint vraća stvarno stanje sesije. Uz brojač vraća i svjež nonce, jer
 * nonce iz keširanog HTML-a može isteći.
 */
function door_expert_get_cart_count() {
	if ( ! WC()->cart && function_exists( 'wc_load_cart' ) ) {
		wc_load_cart();
	}

	nocache_headers();

	wp_send_json_success(
		array(
			'count' => WC()->cart ? WC()->cart->get_cart_contents_count() : 0,
			'nonce' => wp_create_nonce( 'door_expert_nonce' ),
		)
	);
}
```

### `assets/js/cart.js` — quantity and removal

```js
/**
 * Korpa: promjena količine i uklanjanje stavki bez osvježavanja stranice.
 */
( function () {
	'use strict';

	var root = document.querySelector( '.cart-page' );
	if ( ! root || 'undefined' === typeof doorExpert ) {
		return;
	}

	function post( action, body ) {
		var data = new URLSearchParams( body );
		data.append( 'action', action );
		data.append( 'nonce', doorExpert.cartNonce );

		return fetch( doorExpert.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: data.toString()
		} ).then( function ( res ) {
			return res.json();
		} );
	}

	function paintTotals( data ) {
		document.querySelectorAll( '[data-cart-subtotal]' ).forEach( function ( el ) {
			el.innerHTML = data.cart_subtotal;
		} );
		document.querySelectorAll( '[data-cart-total]' ).forEach( function ( el ) {
			el.textContent = data.cart_total;
		} );
		document.querySelectorAll( '.cart-badge' ).forEach( function ( el ) {
			el.textContent = data.cart_count;
			el.style.display = data.cart_count > 0 ? '' : 'none';
		} );
	}

	root.addEventListener( 'click', function ( e ) {
		var step = e.target.closest( '[data-qty-step]' );
		var kill = e.target.closest( '[data-cart-remove]' );

		if ( step ) {
			var row   = step.closest( '[data-cart-key]' );
			var input = row.querySelector( 'input[type="number"]' );
			var next  = Math.max( 1, parseInt( input.value, 10 ) + parseInt( step.dataset.qtyStep, 10 ) );

			input.value = next;
			row.classList.add( 'is-busy' );

			post( 'door_expert_update_cart_qty', {
				cart_key: row.dataset.cartKey,
				qty: next
			} ).then( function ( res ) {
				row.classList.remove( 'is-busy' );
				if ( ! res.success ) {
					return;
				}
				row.querySelector( '[data-item-subtotal]' ).innerHTML = res.data.item_subtotal;
				paintTotals( res.data );
			} );
		}

		if ( kill ) {
			var delRow = kill.closest( '[data-cart-key]' );
			delRow.classList.add( 'is-busy' );

			post( 'door_expert_remove_cart_item', {
				cart_key: delRow.dataset.cartKey
			} ).then( function ( res ) {
				if ( ! res.success ) {
					delRow.classList.remove( 'is-busy' );
					return;
				}
				delRow.remove();
				paintTotals( res.data );
				if ( res.data.cart_empty ) {
					window.location.reload();
				}
			} );
		}
	} );
}() );
```

### Badge hydration — put this in the globally loaded script

```js
/**
 * Badž korpe se renderuje server-side, ali full-page keš ga zamrzne.
 * Ovaj poziv vraća živu vrijednost i svjež nonce na svakom učitavanju.
 */
( function () {
	'use strict';

	if ( 'undefined' === typeof doorExpert ) {
		return;
	}

	var badges = document.querySelectorAll( '.cart-badge' );
	if ( ! badges.length ) {
		return;
	}

	fetch( doorExpert.ajaxUrl + '?action=door_expert_get_cart_count', {
		credentials: 'same-origin'
	} ).then( function ( res ) {
		return res.json();
	} ).then( function ( res ) {
		if ( ! res.success ) {
			return;
		}
		doorExpert.nonce = res.data.nonce;
		badges.forEach( function ( el ) {
			el.textContent = res.data.count;
			el.style.display = res.data.count > 0 ? '' : 'none';
		} );
	} ).catch( function () {} );
}() );
```

## 6. Wiring

1. `require_once get_stylesheet_directory() . '/inc/quote-cart.php';` from `functions.php`.
2. Create a WordPress page with slug `upit` and a page template that renders the form. The form
   posts `name, email, phone, city, address, note, buyer_type, company, pib, consent, website`
   (the last one is the honeypot, visually hidden, never `display:none` on the label alone).
3. Localize the script handle that owns `cart.js`:

```php
wp_localize_script(
	'door-expert-cart',
	'doorExpert',
	array(
		'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
		'nonce'        => wp_create_nonce( 'door_expert_nonce' ),
		'cartNonce'    => wp_create_nonce( 'door_expert_cart' ),
		'inquiryNonce' => wp_create_nonce( 'door_expert_inquiry_nonce' ),
	)
);
```

4. Cart row markup contract: each row carries `data-cart-key="<?php echo esc_attr( $cart_item_key ); ?>"`,
   a `<input type="number">`, `[data-qty-step="-1"]` / `[data-qty-step="1"]` buttons,
   `[data-cart-remove]`, and `[data-item-subtotal]`. Totals carry `[data-cart-subtotal]` and
   `[data-cart-total]`.
5. Optional: define `DOOR_EXPERT_WEBHOOK` and `DOOR_EXPERT_WEBHOOK_SECRET` in `wp-config.php` if you
   later add an automation layer. Without them, `wp_mail()` carries the notification.

## 7. Verify after dropping it in

- Add a priced product and a price-0 product to the cart; both must be addable.
- Cart page: `+` / `-` updates the line and the totals without a reload; removing the last item
  reloads to the empty-cart state.
- Submit the form: a new order appears under WooCommerce with status **On hold**, payment method
  "Upit, predračun", and the consent meta on the order.
- Confirm the customer does **not** receive a WooCommerce "order received" email.
- Confirm sales receives the `wp_mail` notification.
- Submit six times in an hour from the same IP; the sixth must be refused with HTTP 429.
- Submit with the honeypot filled; must be refused.
- Submit with `consent` absent; must be refused even if the front end allowed it.



<!-- ===== FILE: 03-PORT-variations.md ===== -->

# PORT 03 — Variable products: selector UI + custom add-to-cart

**Verdict: `ADAPT (heavy)` for the full UI, `ADAPT (light)` for the two pieces that matter · Priority 2**

Door Expert's PDP is currently v1: simple products, variants shown read-only from attributes with no
live price. This is the upgrade path. Doors have sizes and finishes, tiles have formats and colours,
so a real variation selector is not optional for long.

The honest split: Saya's variation module is ~950 lines and about half of it is Saya-specific
(colour-name dropdown, slip-resistance map, collection card syncing, per-variation gallery rebuild).
**Do not port the module.** Port the two pieces that are genuinely hard and genuinely general:

1. the **matching engine** (about 90 lines of JS), and
2. the **server-side add-to-cart handler** that makes a custom pill UI work at all with WooCommerce.

Everything else is markup and styling you will write against your own design anyway.

---

## 1. What it does

The PDP renders one row per variation attribute, each row a set of pill buttons (or swatches). As the
user picks values:

- impossible combinations grey out immediately,
- if only one option remains in another row, it is auto-selected,
- picking a value that contradicts an earlier pick clears only the contradicting pick, not everything,
- price, stock, SKU and image update live,
- the CTA switches between "add to inquiry" and "request price" depending on whether the matched
  variation has a price.

The subtlety that makes it correct: WooCommerce stores an **empty string as a wildcard** for "Any
<attribute>" variations. Naive matching treats `''` as a value and never matches. Both functions below
handle it.

## 2. Saya source

| Piece | Location |
|---|---|
| Hidden-attribute list (single source of truth) | `wp-theme/functions.php:5235-5256` |
| Custom add-to-cart AJAX | `wp-theme/functions.php:5273-5347` |
| Variation JSON emitted to the page | `wp-theme/woocommerce/single-product.php:517` (`get_available_variations()`), `:903` (the `<script type="application/json">` tag) |
| Attribute rows markup | `wp-theme/woocommerce/single-product.php:682-790` |
| Matching engine | `wp-theme/js/product-single.js:1291-1324` |
| Auto-select single option | `wp-theme/js/product-single.js:1329-1369` |
| Grey out impossible options | `wp-theme/js/product-single.js:1372-1420` |
| Smart cascade on click | `wp-theme/js/product-single.js:1145-1176` |
| State / price / CTA update | `wp-theme/js/product-single.js:1421-1623` |

Background reading in this repo: `DOCS/ADD_TO_CART_VARIJACIJE.md` and
`DOCS/BITNE FUNKCIONALNOSTI/WC_PROTIVKLIZNOST_VARIATION.md`.

## 3. Dependencies and coupling

| Dependency | Notes |
|---|---|
| WooCommerce | Core. The JSON payload is plain `WC_Product_Variable::get_available_variations()`, so no custom serialisation to port. |
| jQuery | **None.** |
| Page builder / CF7 / Jet* | **None.** |
| Saya-specific | `pa_naziv-boje` primary attribute, `pa_boja` derivation, `saya_protivkliznost` / `saya_pei_klasa` meta, `syncKolekcijaCards()`, per-variation gallery rebuild. **All dropped in the port.** |

### Why the custom add-to-cart handler exists

This is the part worth reading twice, because it is a real WooCommerce trap and the comment at
`functions.php:5258-5272` documents it well:

- Posting `product_id = parent` makes WooCommerce try to add a variable parent, which is not purchasable.
- Posting `product_id = variation` makes WooCommerce load `get_variation_attributes()` itself, and for
  an "Any" attribute that comes back empty, so it **throws** `"<Attribute> is a required field"`
  before any validation filter runs. You cannot catch it with a filter.

The fix is to build the complete `$variation` array server-side and resolve every "Any" attribute to a
real value — either the posted value or the parent's first term. That is what the handler below does.

## 4. Data-model mapping

| Saya | Door Expert |
|---|---|
| `pa_boja`, `pa_naziv-boje` | `pa_boja` — keep, drop the second one unless you introduce named colours |
| `pa_dimenzije-plocice` (note the **e**) | `pa_dimenzije-plocica` (note the **a**) — rename on the way over |
| — | `pa_dimenzije-vrata` — new, behaves exactly like any other variation attribute |
| `pa_zavrsna-obrada`, `pa_protivkliznost`, `pa_pei-klasa-habanja` | probably not needed for doors; keep the hidden-attribute mechanism, empty the list |

## 5. Adapted code

### `inc/product-variations.php`

```php
<?php
/**
 * Varijabilni proizvodi, atributi koji se ne nude kao izbor i custom add-to-cart.
 *
 * @package Door_Expert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Varijacijski atributi koji se NE prikazuju kao izbor na stranici proizvoda.
 *
 * Ostaju u varijacijama i u DOM-u, samo se ne nude korisniku. Add-to-cart ne
 * zavisi od ove liste, handler radi iz podataka same varijacije, pa je ovo
 * čisto pitanje prikaza.
 *
 * @param int $product_id Opciono, za pravila po kategoriji.
 * @return array Lista slugova.
 */
function door_expert_hidden_pdp_attributes( $product_id = 0 ) {
	$hidden = array();

	return apply_filters( 'door_expert_hidden_pdp_attributes', $hidden, $product_id );
}

add_action( 'wp_ajax_door_expert_add_to_cart', 'door_expert_add_to_cart_ajax' );
add_action( 'wp_ajax_nopriv_door_expert_add_to_cart', 'door_expert_add_to_cart_ajax' );

/**
 * Dodavanje varijacije u korpu iz custom pill UI-ja.
 *
 * WooCommerce-ov ?wc-ajax=add_to_cart ovdje ne radi:
 *   - product_id = roditelj  → WC pokušava da doda varijabilni proizvod, nije kupljiv
 *   - product_id = varijacija → WC sam učita get_variation_attributes(), a za "Any"
 *     atribut to je prazan string → baca "X is a required field" PRIJE validacionog
 *     filtera, pa se greška ne može uhvatiti.
 *
 * Rješenje: server-side gradimo kompletan $variation niz i "Any" atribute
 * razrješavamo na stvarnu vrijednost.
 */
function door_expert_add_to_cart_ajax() {
	if ( ! check_ajax_referer( 'door_expert_nonce', 'nonce', false ) ) {
		wp_send_json(
			array(
				'error'   => true,
				'message' => __( 'Sesija je istekla. Osvježite stranicu i pokušajte ponovo.', 'door-expert' ),
			)
		);
	}

	$variation_id = isset( $_POST['variation_id'] ) ? absint( wp_unslash( $_POST['variation_id'] ) ) : 0;
	$quantity     = isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : 1;

	if ( 0 >= $quantity ) {
		$quantity = 1;
	}

	$variation_obj = $variation_id ? wc_get_product( $variation_id ) : null;

	if ( ! $variation_obj || ! $variation_obj->is_type( 'variation' ) ) {
		wp_send_json(
			array(
				'error'   => true,
				'message' => __( 'Nevažeća varijacija.', 'door-expert' ),
			)
		);
	}

	$parent_id = $variation_obj->get_parent_id();
	$parent    = wc_get_product( $parent_id );

	if ( ! $parent ) {
		wp_send_json(
			array(
				'error'   => true,
				'message' => __( 'Proizvod nije pronađen.', 'door-expert' ),
			)
		);
	}

	// Na admin-ajax.php je is_admin() true, pa korpa možda nije učitana.
	if ( ! WC()->cart && function_exists( 'wc_load_cart' ) ) {
		wc_load_cart();
	}

	// Sačuvani slugovi varijacije; prazan string znači "bilo koja vrijednost".
	$stored    = wc_get_product_variation_attributes( $variation_id );
	$variation = array();

	foreach ( $parent->get_attributes() as $attribute ) {
		if ( ! $attribute->get_variation() ) {
			continue;
		}

		$tax_key = 'attribute_' . sanitize_title( $attribute->get_name() );
		$value   = $stored[ $tax_key ] ?? '';

		if ( '' === $value ) {
			if ( ! empty( $_POST[ $tax_key ] ) ) {
				$value = wc_clean( wp_unslash( $_POST[ $tax_key ] ) );
			} else {
				$options = $attribute->get_options();

				if ( ! empty( $options ) ) {
					if ( $attribute->is_taxonomy() ) {
						$term  = get_term( (int) $options[0] );
						$value = ( $term && ! is_wp_error( $term ) ) ? $term->slug : '';
					} else {
						$value = $options[0];
					}
				}
			}
		}

		$variation[ $tax_key ] = $value;
	}

	$passed = WC()->cart->add_to_cart( $parent_id, $quantity, $variation_id, $variation );

	if ( false === $passed ) {
		$errors = wc_get_notices( 'error' );
		wc_clear_notices();

		$message = '';
		foreach ( $errors as $notice ) {
			$message .= ( is_array( $notice ) ? ( $notice['notice'] ?? '' ) : $notice ) . ' ';
		}

		wp_send_json(
			array(
				'error'   => true,
				'message' => trim( $message ) ? trim( $message ) : __( 'Greška pri dodavanju u korpu.', 'door-expert' ),
			)
		);
	}

	wp_send_json(
		array(
			'error'      => false,
			'cart_count' => WC()->cart->get_cart_contents_count(),
		)
	);
}
```

### Emitting the variation data — in `template-parts/product/single.php`

```php
<?php if ( $product->is_type( 'variable' ) ) : ?>
	<script id="doorExpertVariations" type="application/json">
		<?php echo wp_json_encode( $product->get_available_variations() ); ?>
	</script>
<?php endif; ?>
```

`get_available_variations()` already returns `attributes`, `display_price`, `price_html`,
`is_in_stock`, `sku`, `variation_id` and `image`, so there is nothing custom to serialise.

### Row markup contract

```php
<div class="variation-row<?php echo $is_hidden ? ' variation-row--hidden' : ''; ?>"
	data-attr-key="attribute_<?php echo esc_attr( $taxonomy ); ?>">
	<span class="variation-row__label"><?php echo esc_html( wc_attribute_label( $taxonomy ) ); ?></span>
	<div class="variation-options" role="group" aria-label="<?php echo esc_attr( wc_attribute_label( $taxonomy ) ); ?>">
		<?php foreach ( $terms as $term ) : ?>
			<button type="button"
				class="variation-opt"
				data-attr-key="attribute_<?php echo esc_attr( $taxonomy ); ?>"
				data-value="<?php echo esc_attr( $term->slug ); ?>">
				<?php echo esc_html( $term->name ); ?>
			</button>
		<?php endforeach; ?>
	</div>
</div>
```

### `assets/js/variations.js` — the matching engine

This is the part that is genuinely worth copying. It is written against the markup contract above and
carries no Door Expert or Saya specifics.

```js
/**
 * Varijabilni proizvod: izbor atributa, dostupnost, živa cijena.
 *
 * WooCommerce čuva prazan string kao "bilo koja vrijednost" (Any). Zato se
 * svugdje ispod prazan string tretira kao džoker, a ne kao vrijednost.
 */
( function () {
	'use strict';

	var dataEl = document.getElementById( 'doorExpertVariations' );
	if ( ! dataEl ) {
		return;
	}

	var variations = JSON.parse( dataEl.textContent || dataEl.innerHTML );
	var rows       = document.querySelectorAll( '.variation-row[data-attr-key]' );
	var selected   = {};
	var autoPicked = new Set();

	var priceEl  = document.getElementById( 'variationPrice' );
	var skuEl    = document.getElementById( 'variationSku' );
	var stockEl  = document.getElementById( 'variationStock' );
	var ctaEl    = document.getElementById( 'variationCta' );
	var imageEl  = document.getElementById( 'variationImage' );

	function visibleRows() {
		return Array.prototype.filter.call( rows, function ( row ) {
			return ! row.classList.contains( 'variation-row--hidden' );
		} );
	}

	/**
	 * Prva varijacija koja odgovara zadatim atributima, ili null.
	 */
	function findVariation( attrs ) {
		for ( var i = 0; i < variations.length; i++ ) {
			var v     = variations[ i ];
			var match = true;

			for ( var key in attrs ) {
				if ( ! attrs.hasOwnProperty( key ) ) {
					continue;
				}
				if ( '' !== v.attributes[ key ] && v.attributes[ key ] !== attrs[ key ] ) {
					match = false;
					break;
				}
			}

			if ( match ) {
				return v;
			}
		}

		return null;
	}

	/**
	 * Da li djelimičan izbor uopšte vodi ka nekoj varijaciji.
	 */
	function isComboAvailable( testAttrs ) {
		return null !== findVariation( testAttrs );
	}

	/**
	 * Sivi opcije koje se ne mogu kombinovati sa trenutnim izborom.
	 */
	function updateAvailability() {
		visibleRows().forEach( function ( row ) {
			var rowKey = row.dataset.attrKey;

			row.querySelectorAll( '.variation-opt' ).forEach( function ( btn ) {
				var test = {};

				for ( var k in selected ) {
					if ( ! selected.hasOwnProperty( k ) || k === rowKey ) {
						continue;
					}
					var kRow = document.querySelector( '.variation-row[data-attr-key="' + k + '"]' );
					if ( ! kRow || kRow.classList.contains( 'variation-row--hidden' ) ) {
						continue;
					}
					test[ k ] = selected[ k ];
				}

				test[ rowKey ] = btn.dataset.value;
				btn.classList.toggle( 'is-unavailable', ! isComboAvailable( test ) );
			} );
		} );
	}

	/**
	 * Ako je u nekom drugom redu ostala samo jedna moguća opcija, izaberi je.
	 * Vraća true ako je nešto izabrano, da pozivalac zna da osvježi stanje.
	 */
	function autoSelectSingle( changedKey ) {
		var changed = false;

		visibleRows().forEach( function ( row ) {
			var rowKey = row.dataset.attrKey;

			if ( rowKey === changedKey || selected[ rowKey ] ) {
				return;
			}

			var opts = Array.prototype.slice.call( row.querySelectorAll( '.variation-opt' ) );
			var free = opts.filter( function ( b ) {
				return ! b.classList.contains( 'is-unavailable' );
			} );

			if ( 1 !== free.length ) {
				return;
			}

			/*
			 * Ako nijedna odgovarajuća varijacija ne precizira ovaj atribut
			 * (sve imaju džoker), atribut je nebitan za trenutni izbor pa se
			 * ne bira automatski.
			 */
			var specific = variations.some( function ( v ) {
				for ( var k in selected ) {
					if ( ! selected.hasOwnProperty( k ) || k === rowKey || autoPicked.has( k ) ) {
						continue;
					}
					if ( '' !== v.attributes[ k ] && v.attributes[ k ] !== selected[ k ] ) {
						return false;
					}
				}
				return '' !== v.attributes[ rowKey ];
			} );

			if ( ! specific ) {
				return;
			}

			opts.forEach( function ( b ) {
				b.classList.remove( 'active' );
			} );
			free[ 0 ].classList.add( 'active' );
			selected[ rowKey ] = free[ 0 ].dataset.value;
			autoPicked.add( rowKey );
			changed = true;
		} );

		return changed;
	}

	function money( variation ) {
		return variation.price_html || '';
	}

	function updateState() {
		updateAvailability();

		var complete = visibleRows().every( function ( row ) {
			return !! selected[ row.dataset.attrKey ];
		} );

		var match = complete ? findVariation( selected ) : null;

		if ( priceEl ) {
			priceEl.innerHTML = match ? money( match ) : '';
		}
		if ( skuEl ) {
			skuEl.textContent = match && match.sku ? match.sku : '';
		}
		if ( stockEl ) {
			stockEl.textContent = match
				? ( match.is_in_stock ? 'Na stanju' : 'Na upit' )
				: '';
		}
		if ( imageEl && match && match.image && match.image.src ) {
			imageEl.src = match.image.src;
			if ( match.image.srcset ) {
				imageEl.srcset = match.image.srcset;
			}
		}
		if ( ctaEl ) {
			ctaEl.disabled = ! match;
			ctaEl.dataset.variationId = match ? match.variation_id : '';
			ctaEl.textContent = match && 0 < parseFloat( match.display_price )
				? 'Dodaj u ponudu'
				: 'Zatraži cijenu';
		}
	}

	document.addEventListener( 'click', function ( e ) {
		var btn = e.target.closest( '.variation-opt' );
		if ( ! btn ) {
			return;
		}

		var key = btn.dataset.attrKey;
		var val = btn.dataset.value;
		var row = document.querySelector( '.variation-row[data-attr-key="' + key + '"]' );

		if ( row ) {
			row.querySelectorAll( '.variation-opt' ).forEach( function ( b ) {
				b.classList.remove( 'active' );
			} );
		}

		if ( selected[ key ] === val ) {
			delete selected[ key ];
			autoPicked.delete( key );
		} else {
			/*
			 * Zadrži svaki drugi izbor koji je i dalje moguć uz novi, a očisti
			 * samo one koji bi napravili nemoguću kombinaciju.
			 */
			visibleRows().forEach( function ( other ) {
				var otherKey = other.dataset.attrKey;

				if ( otherKey === key || ! selected[ otherKey ] ) {
					return;
				}

				var test = {};
				test[ key ]      = val;
				test[ otherKey ] = selected[ otherKey ];

				if ( ! isComboAvailable( test ) ) {
					delete selected[ otherKey ];
					autoPicked.delete( otherKey );
					other.querySelectorAll( '.variation-opt' ).forEach( function ( b ) {
						b.classList.remove( 'active' );
					} );
				}
			} );

			autoPicked.delete( key );
			selected[ key ] = val;
			btn.classList.add( 'active' );
		}

		updateState();

		if ( autoSelectSingle( key ) ) {
			updateState();
		}
	} );

	updateState();
}() );
```

### Add to cart from the selector

```js
ctaEl.addEventListener( 'click', function () {
	var body = new URLSearchParams( {
		action: 'door_expert_add_to_cart',
		nonce: doorExpert.nonce,
		variation_id: ctaEl.dataset.variationId,
		quantity: document.getElementById( 'qtyInput' ).value
	} );

	fetch( doorExpert.ajaxUrl, {
		method: 'POST',
		credentials: 'same-origin',
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
		body: body.toString()
	} )
		.then( function ( res ) {
			return res.json();
		} )
		.then( function ( res ) {
			if ( res.error ) {
				window.alert( res.message );
				return;
			}
			document.querySelectorAll( '.cart-badge' ).forEach( function ( el ) {
				el.textContent = res.cart_count;
				el.style.display = '';
			} );
		} );
} );
```

## 6. What was deliberately left behind

| Saya feature | Why it is not here |
|---|---|
| Colour-name dropdown (`naziv-dropdown`) | A second attribute layered on top of `pa_boja`; Door Expert has no equivalent. |
| Per-variation gallery rebuild (`product-single.js:130-186`) | Tightly bound to Saya's gallery DOM. If you want it later, the hook is `updateState()` — swap `imageEl.src` for a full rebuild. |
| Slip-resistance / PEI maps | Tile-specific technical attributes shown as specs, not choices. |
| `syncKolekcijaCards()` (`:1946`) | Syncs colour choice into collection cards elsewhere on the page. Saya-only feature. |
| Confirm strip (`applyConfirmStrip`, `:1624`) | Mobile summary bar. Nice, but pure UI you will design yourself. |

## 7. Verify after dropping it in

- A variable product with two attributes: picking one greys out impossible values in the other.
- A product where one attribute has a single valid option after the first pick: that option must
  auto-select.
- A product with an "Any <attribute>" variation: it must still add to cart, with no
  "<Attribute> is a required field" error. **This is the case that breaks with stock WooCommerce
  AJAX and the reason the custom handler exists.**
- Clicking an already-selected pill deselects it and re-enables everything.
- Price, SKU and stock text change on every complete selection.
- A variation priced at 0 shows "Zatraži cijenu" and still adds to the cart.



<!-- ===== FILE: 04-PORT-gallery-lightbox.md ===== -->

# PORT 04 — Product gallery + PhotoSwipe lightbox

**Verdict: `ADAPT (light)` · Priority 3**

Door Expert already has thumb switching and a basic lightbox in `assets/js/product.js`. This is the
upgrade: a real zoom lightbox with pinch, pan, wheel-to-zoom and keyboard, plus the two bits of glue
that make it correct on a variable product.

---

## 1. What it does

- Thumbnail strip switches the main image, with keyboard support.
- Desktop hover zoom on the main image.
- Touch drag / swipe on the gallery track, with pagination dots.
- Click or the zoom button opens **PhotoSwipe v5** at the exact slide the user was on.
- The lightbox source is rebuilt from the **currently visible** slides, so on a variable product the
  zoom always shows the selected variation's images, not the parent's.
- Real image dimensions are handed to PhotoSwipe up front, so it never has to guess and never
  flashes a wrongly sized frame.

## 2. Saya source

| Piece | Location |
|---|---|
| Gallery: track, thumbs, chevrons, dots, drag, autoplay | `wp-theme/js/product-single.js:21-372` |
| Per-variation gallery rebuild | `wp-theme/js/product-single.js:130-186` |
| Hover zoom (desktop) | `wp-theme/js/product-single.js:187-247` |
| PhotoSwipe bridge | `wp-theme/js/pswp-gallery.js` (72 lines) |
| Fallback custom lightbox with pinch-zoom | `wp-theme/js/product-single.js:416-648` |
| Vendored library | `wp-theme/js/vendor/photoswipe/` — PhotoSwipe **5.4.4**, MIT, © 2024 Dmytro Semenov |
| `type="module"` filter | `wp-theme/functions.php:273-280` |
| Enqueue | `wp-theme/functions.php:232-233`, `:347-351` |
| Dimensions JSON | `wp-theme/woocommerce/single-product.php:334` (built), `:347` (emitted) |

Background reading in this repo: `DOCS/BITNE FUNKCIONALNOSTI/PDP_GALERIJA_LIGHTBOX.md`.

## 3. Dependencies and coupling

| Dependency | Notes |
|---|---|
| PhotoSwipe 5.4.4 | **MIT**, safe to reuse. Vendored, not from a CDN, so no third-party request at runtime. Keep the licence header in the file. |
| jQuery | **None.** |
| Page builder / CF7 / Jet* | **None.** |
| ES modules | PhotoSwipe v5 ships as ESM. The `<script>` tag needs `type="module"`, which WordPress will not add for you. The filter is included below. |
| Coupling | The bridge (`pswp-gallery.js`) is fully self-contained: it reads the DOM and exposes one global. The surrounding gallery code in `product-single.js` is entangled with Saya's markup and is **not** worth porting wholesale. |

**Recommendation:** port `pswp-gallery.js` and the dimensions plumbing as-is. Keep your existing
`assets/js/product.js` gallery, and just replace its lightbox with a call to the bridge.

## 4. Data-model mapping

None. The gallery reads images from the DOM and dimensions from a JSON blob. Nothing depends on
`product_cat`, `product_brand` or any `pa_*` attribute.

## 5. Adapted code

### `assets/js/pswp-gallery.js`

```js
/**
 * PhotoSwipe v5 most za PDP galeriju.
 *
 * Lightbox se otvara programski, sa nizom koji se gradi iz trenutno vidljivih
 * slajdova. Kod varijabilnih proizvoda se ti slajdovi mijenjaju sa izborom
 * varijacije, pa zoom uvijek prikazuje ono što je korisnik izabrao.
 *
 * Dimenzije slika dolaze iz #doorExpertPswpDims (puni URL → [w, h]).
 * Bez njih PhotoSwipe pogađa veličinu i zna da trepne pogrešnim okvirom.
 *
 * ES modul: relativni importi se razrješavaju u odnosu na ovaj fajl,
 * dakle /wp-content/themes/<tema>/assets/js/vendor/photoswipe/.
 */

import PhotoSwipeLightbox from './vendor/photoswipe/photoswipe-lightbox.esm.min.js';

( function () {
	'use strict';

	var dimsEl = document.getElementById( 'doorExpertPswpDims' );
	var dims   = {};

	if ( dimsEl ) {
		try {
			dims = JSON.parse( dimsEl.textContent || dimsEl.innerHTML || '{}' ) || {};
		} catch ( e ) {
			dims = {};
		}
	}

	var lightbox = new PhotoSwipeLightbox( {
		pswpModule: function () {
			return import( './vendor/photoswipe/photoswipe.esm.min.js' );
		},
		bgOpacity: 1,
		showHideAnimationType: 'fade',
		wheelToZoom: true
	} );

	lightbox.init();

	/**
	 * Gradi izvor podataka iz vidljivih slajdova.
	 *
	 * Vraća i mapu originalnih indeksa, da bi se lightbox otvorio tačno na
	 * slici na koju je korisnik kliknuo, a ne na prvoj.
	 */
	function buildDataSource() {
		var imgs = Array.prototype.slice.call(
			document.querySelectorAll( '#lightboxTrack .pdp-lightbox__slide' )
		);

		var ds  = [];
		var map = [];

		imgs.forEach( function ( img, i ) {
			var src = img.getAttribute( 'src' );

			if ( ! src || 'none' === img.style.display ) {
				return;
			}

			var d = dims[ src ];

			ds.push( {
				src: src,
				width: d ? d[ 0 ] : ( img.naturalWidth || 1600 ),
				height: d ? d[ 1 ] : ( img.naturalHeight || 1600 ),
				alt: img.getAttribute( 'alt' ) || ''
			} );

			map.push( i );
		} );

		return { ds: ds, map: map };
	}

	/**
	 * Otvara lightbox na zadatom indeksu galerije.
	 * Poziva se iz product.js, sa dugmeta za zoom ili klika na sliku.
	 */
	window.doorExpertOpenPswp = function ( slideIndex ) {
		var built = buildDataSource();

		if ( ! built.ds.length ) {
			return;
		}

		var open = built.map.indexOf( slideIndex );

		if ( 0 > open ) {
			open = 0;
		}

		lightbox.loadAndOpen( open, built.ds );
	};
}() );
```

### Enqueue and the `type="module"` filter

```php
/**
 * PhotoSwipe v5 se isporučuje kao ES modul, pa njegov <script> tag mora
 * imati type="module". WordPress to sam ne radi.
 *
 * @param string $tag    HTML tag skripte.
 * @param string $handle Registrovana oznaka skripte.
 * @param string $src    URL skripte.
 * @return string
 */
function door_expert_module_script_tag( $tag, $handle, $src ) {
	$modules = array( 'door-expert-pswp-gallery' );

	if ( in_array( $handle, $modules, true ) ) {
		return '<script type="module" src="' . esc_url( $src ) . '" id="' . esc_attr( $handle ) . '-js"></script>' . "\n";
	}

	return $tag;
}
add_filter( 'script_loader_tag', 'door_expert_module_script_tag', 10, 3 );

/**
 * Galerija se učitava samo na stranici proizvoda.
 */
function door_expert_enqueue_gallery() {
	if ( ! is_product() ) {
		return;
	}

	$theme_dir = get_stylesheet_directory();
	$theme_uri = get_stylesheet_directory_uri();
	$pswp_css  = $theme_dir . '/assets/js/vendor/photoswipe/photoswipe.css';
	$pswp_js   = $theme_dir . '/assets/js/pswp-gallery.js';

	wp_enqueue_style(
		'door-expert-photoswipe',
		$theme_uri . '/assets/js/vendor/photoswipe/photoswipe.css',
		array(),
		file_exists( $pswp_css ) ? filemtime( $pswp_css ) : '1.0'
	);

	wp_enqueue_script(
		'door-expert-pswp-gallery',
		$theme_uri . '/assets/js/pswp-gallery.js',
		array(),
		file_exists( $pswp_js ) ? filemtime( $pswp_js ) : '1.0',
		true
	);
}
add_action( 'wp_enqueue_scripts', 'door_expert_enqueue_gallery' );
```

### Emitting real image dimensions

Put this next to the gallery markup in `template-parts/product/single.php`.

```php
<?php
$door_expert_pswp_dims = array();
$door_expert_image_ids = array_merge(
	array( $product->get_image_id() ),
	$product->get_gallery_image_ids()
);

foreach ( array_filter( $door_expert_image_ids ) as $door_expert_image_id ) {
	$door_expert_src = wp_get_attachment_image_src( $door_expert_image_id, 'full' );

	if ( $door_expert_src ) {
		$door_expert_pswp_dims[ $door_expert_src[0] ] = array(
			(int) $door_expert_src[1],
			(int) $door_expert_src[2],
		);
	}
}
?>
<script id="doorExpertPswpDims" type="application/json">
	<?php echo wp_json_encode( $door_expert_pswp_dims ); ?>
</script>
```

For a variable product, add each variation's image the same way so the map covers every image the
lightbox can ever show.

### Hooking it up from your existing gallery code

```js
var zoomBtn = document.getElementById( 'galleryZoom' );

if ( zoomBtn ) {
	zoomBtn.addEventListener( 'click', function () {
		if ( 'function' === typeof window.doorExpertOpenPswp ) {
			window.doorExpertOpenPswp( currentIndex );
		}
	} );
}
```

`currentIndex` is whatever your gallery already tracks. If PhotoSwipe fails to load, the guard means
nothing happens rather than a thrown error — keep a plain "open full size in a new tab" fallback if
you want a hard floor.

### Markup contract

The bridge needs a hidden list of full-size images it can read:

```php
<div id="lightboxTrack" hidden>
	<?php foreach ( $gallery_images as $image ) : ?>
		<img class="pdp-lightbox__slide"
			src="<?php echo esc_url( $image['full'] ); ?>"
			alt="<?php echo esc_attr( $image['alt'] ); ?>">
	<?php endforeach; ?>
</div>
```

On a variable product, hide the slides that do not belong to the selected variation with
`style="display:none"` — `buildDataSource()` skips exactly those.

## 6. What was deliberately left behind

| Saya feature | Why it is not here |
|---|---|
| Custom pinch-zoom lightbox (`product-single.js:450-648`) | Written before PhotoSwipe was adopted, ~200 lines, now dead weight. PhotoSwipe does pinch, pan and double-tap better. |
| Gallery autoplay on mobile (`:256-287`) | Debatable UX on a PDP; port only if you want it. |
| Drag / swipe implementation (`:310-371`) | Solid, but bound to Saya's track markup. Your `product.js` already has thumb switching; extend that rather than transplant this. |

## 7. Verify after dropping it in

- Click the third thumbnail, then the zoom button: PhotoSwipe must open **on the third image**.
- Pinch to zoom on a phone, wheel to zoom on desktop, arrow keys to move, Esc to close.
- On a variable product, pick a variation, then zoom: only that variation's images appear.
- Check the network tab: no request to photoswipe.com or any CDN. The library must load from your
  own theme.
- View source: the `pswp-gallery.js` tag carries `type="module"`. Without it the browser throws
  "Cannot use import statement outside a module" and the lightbox silently never opens.



<!-- ===== FILE: 05-PORT-tile-calculator.md ===== -->

# PORT 05 — Tile m² calculator and per-m² cart pricing

**Verdict: `ADAPT (light)` · Priority 4**

Door Expert already has "a basic tile m² calculator" in `assets/js/product.js`. The gap is not the
arithmetic, it is everything around it: the packaging meta model, feeding the result into the cart,
and the pricing correction that stops the cart charging a fraction of the real price.

**The pricing correction is the part you cannot skip.** If you sell tiles priced per m² but the cart
counts boxes, WooCommerce multiplies `boxes × price_per_m²` and undercharges by a factor of the box
size. Saya hit this and fixed it at `functions.php:4492-4512`.

---

## 1. What it does

**On the PDP:** room length × width, plus a waste percentage, produces area, area with waste, number
of boxes needed (rounded up), coverage, total price, tile count and package weight. "Primeni" writes
the quantity into the add-to-cart form and stashes the calculation as hidden fields.

**In the cart:** the calculation travels with the line item as cart item meta, so sales sees
"18,40 m², +10% otpad, 5 kutija" on the order instead of a bare quantity.

**At price time:** a `woocommerce_before_calculate_totals` hook rewrites the line price from
"per m²" to "per box".

## 2. Saya source

| Piece | Location |
|---|---|
| Calculator UI + arithmetic | `wp-theme/js/product-single.js:774-1001` |
| Packaging data read from `data-*` | `wp-theme/js/product-single.js:733-739` |
| Quantity stepper it drives | `wp-theme/js/product-single.js:725-772` |
| Calc meta → cart item | `wp-theme/functions.php:4468-4479` |
| **Per-m² price correction** | `wp-theme/functions.php:4492-4512` |
| Meta helpers | `wp-theme/functions.php:2731` (`saya_price_unit`), `:2813` (`saya_pkg_data`), `:2826` (`saya_pkg_kom`), `:2835` (`saya_pkg_kg`) |

Background reading in this repo: `DOCS/KALKULATOR_PLOCICA.md` and `DOCS/NOVI_KALKULATOR_PLOCICA.md`.

## 3. Dependencies and coupling

| Dependency | Notes |
|---|---|
| WooCommerce | Core hooks only: `woocommerce_add_cart_item_data`, `woocommerce_before_calculate_totals`. |
| jQuery | **None.** |
| Page builder / CF7 / Jet* | **None.** |
| Product meta | `_price_unit`, `_pkg_qty`, `_pkg_label`, `_pkg_kom`, `_pkg_kg`. Plain post meta, so JetEngine as a data layer fits Door Expert's rules exactly. |
| Locale | `toLocaleString( 'sr-RS' )` and a hardcoded `rsd` suffix. Change to `'sr-ME'` / `EUR` for Montenegro. |
| Coupling | Low. The JS reads everything from one element's `data-*` attributes; the PHP reads post meta. Nothing touches Saya's taxonomies. |

## 4. Data model to create in Door Expert

Five meta fields on the product (JetEngine meta box is fine, it is data-layer only):

| Meta key | Meaning | Example |
|---|---|---|
| `_price_unit` | Unit the price is entered in. `m²` triggers the correction; anything else leaves the price alone. | `m²` |
| `_pkg_qty` | How much one package covers, in the price unit. | `1.44` |
| `_pkg_label` | Word for one package. | `kutija` |
| `_pkg_kom` | Pieces per package. Optional. | `8` |
| `_pkg_kg` | Package weight in kg. Optional. | `24.5` |

Doors will not use any of this; leave `_price_unit` empty and every hook below no-ops.

## 5. Adapted code

### `inc/tile-calculator.php`

```php
<?php
/**
 * Kalkulator pločica: podaci pakovanja, prenos u korpu i korekcija cijene.
 *
 * @package Door_Expert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Jedinica u kojoj je unijeta cijena. Prazno znači "po komadu".
 *
 * @param int $product_id ID proizvoda.
 * @return string
 */
function door_expert_price_unit( $product_id ) {
	$unit = get_post_meta( $product_id, '_price_unit', true );

	return $unit ? (string) $unit : '';
}

/**
 * Podaci o pakovanju.
 *
 * @param int $product_id ID proizvoda.
 * @return array {
 *     @type float  $qty   Koliko jedno pakovanje pokriva.
 *     @type string $label Naziv pakovanja.
 *     @type int    $kom   Komada u pakovanju, 0 ako nije poznato.
 *     @type float  $kg    Težina pakovanja, 0 ako nije poznata.
 * }
 */
function door_expert_pkg_data( $product_id ) {
	return array(
		'qty'   => (float) str_replace( ',', '.', (string) get_post_meta( $product_id, '_pkg_qty', true ) ),
		'label' => get_post_meta( $product_id, '_pkg_label', true ) ? (string) get_post_meta( $product_id, '_pkg_label', true ) : 'pakovanje',
		'kom'   => (int) get_post_meta( $product_id, '_pkg_kom', true ),
		'kg'    => (float) str_replace( ',', '.', (string) get_post_meta( $product_id, '_pkg_kg', true ) ),
	);
}

/**
 * Rezultat kalkulatora putuje uz stavku korpe, da ga prodaja vidi na narudžbi.
 */
add_filter(
	'woocommerce_add_cart_item_data',
	function ( $cart_item_data, $product_id, $variation_id ) {
		if ( empty( $_POST['de_calc_povrsina'] ) ) {
			return $cart_item_data;
		}

		$cart_item_data['door_expert_calc'] = array(
			'povrsina' => sanitize_text_field( wp_unslash( $_POST['de_calc_povrsina'] ) ),
			'otpad'    => absint( wp_unslash( $_POST['de_calc_otpad'] ?? 0 ) ),
			'kolicina' => absint( wp_unslash( $_POST['de_calc_kolicina'] ?? 0 ) ),
			'label'    => sanitize_text_field( wp_unslash( $_POST['de_calc_label'] ?? 'pakovanje' ) ),
			'kom'      => absint( wp_unslash( $_POST['de_calc_kom'] ?? 0 ) ),
		);

		return $cart_item_data;
	},
	10,
	3
);

/**
 * Prikaz kalkulatora ispod naziva stavke u korpi i na narudžbi.
 */
add_filter(
	'woocommerce_get_item_data',
	function ( $item_data, $cart_item ) {
		if ( empty( $cart_item['door_expert_calc'] ) ) {
			return $item_data;
		}

		$calc  = $cart_item['door_expert_calc'];
		$parts = array( str_replace( '.', ',', $calc['povrsina'] ) . ' m²' );

		if ( ! empty( $calc['otpad'] ) ) {
			$parts[] = '+' . $calc['otpad'] . '% otpad';
		}

		if ( ! empty( $calc['kolicina'] ) ) {
			$parts[] = $calc['kolicina'] . ' ' . $calc['label'];
		}

		$item_data[] = array(
			'key'   => __( 'Proračun', 'door-expert' ),
			'value' => implode( ' · ', $parts ),
		);

		return $item_data;
	},
	10,
	2
);

/**
 * Korekcija cijene za proizvode koji se prodaju PO m².
 *
 * Cijena se unosi po m² (npr. 24,90/m²), a količina u korpi je broj PAKOVANJA.
 * Bez korekcije korpa naplaćuje broj_kutija × cijena_po_m², dakle znatno manje
 * nego što treba, jer jedna kutija pokriva _pkg_qty m².
 *
 * Baza se čita iz _price mete, ne iz objekta korpe, da višestruki poziv hooka
 * ne bi množio cijenu više puta. Cijena po kutiji se NE zaokružuje; WooCommerce
 * zaokružuje tek ukupan iznos, pa je total tačno
 * broj_kutija × pkg_qty × cijena_po_m².
 *
 * Proizvodi koji se prodaju po komadu ili setu se ne diraju.
 */
add_action(
	'woocommerce_before_calculate_totals',
	function ( $cart ) {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return;
		}

		if ( ! $cart instanceof WC_Cart ) {
			return;
		}

		foreach ( $cart->get_cart() as $cart_item ) {
			$product_id = $cart_item['product_id'];

			if ( 'm²' !== door_expert_price_unit( $product_id ) ) {
				continue;
			}

			$pkg = door_expert_pkg_data( $product_id );

			if ( 0 >= $pkg['qty'] ) {
				continue;
			}

			$source_id = ! empty( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : $product_id;
			$base_m2   = (float) get_post_meta( $source_id, '_price', true );

			if ( 0 >= $base_m2 ) {
				continue;
			}

			$cart_item['data']->set_price( $base_m2 * $pkg['qty'] );
		}
	},
	20
);
```

### PDP markup contract

Everything the JS needs sits on one element, so there is no second source of truth:

```php
<?php
$door_expert_pkg  = door_expert_pkg_data( $product->get_id() );
$door_expert_unit = door_expert_price_unit( $product->get_id() );

if ( 'm²' === $door_expert_unit && 0 < $door_expert_pkg['qty'] ) :
	?>
	<section class="tile-calc"
		id="tileCalc"
		data-unit="<?php echo esc_attr( $door_expert_unit ); ?>"
		data-pkg-qty="<?php echo esc_attr( $door_expert_pkg['qty'] ); ?>"
		data-pkg-label="<?php echo esc_attr( $door_expert_pkg['label'] ); ?>"
		data-pkg-kom="<?php echo esc_attr( $door_expert_pkg['kom'] ); ?>"
		data-pkg-kg="<?php echo esc_attr( $door_expert_pkg['kg'] ); ?>"
		data-price="<?php echo esc_attr( $product->get_price() ); ?>">

		<button type="button" class="tile-calc__toggle" id="tileCalcToggle" aria-expanded="false">
			<?php esc_html_e( 'Izračunaj koliko ti treba', 'door-expert' ); ?>
		</button>

		<div class="tile-calc__body" id="tileCalcBody" hidden>
			<label for="calcDuzina"><?php esc_html_e( 'Dužina, m', 'door-expert' ); ?></label>
			<input type="number" id="calcDuzina" min="0" step="0.01" inputmode="decimal">

			<label for="calcSirina"><?php esc_html_e( 'Širina, m', 'door-expert' ); ?></label>
			<input type="number" id="calcSirina" min="0" step="0.01" inputmode="decimal">

			<label for="calcOtpad"><?php esc_html_e( 'Otpad', 'door-expert' ); ?> <span id="calcOtpadVal">10%</span></label>
			<input type="range" id="calcOtpad" min="0" max="25" step="1" value="10">

			<div class="tile-calc__results" id="calcResults"></div>

			<button type="button" class="tile-calc__apply" id="tileCalcApply">
				<?php esc_html_e( 'Primijeni', 'door-expert' ); ?>
				<span id="tileCalcApplyCount">0</span>
			</button>
		</div>
	</section>
<?php endif; ?>
```

### `assets/js/tile-calculator.js`

```js
/**
 * Kalkulator pločica: površina prostorije → broj pakovanja i procjena cijene.
 *
 * Sve ulazne podatke čita sa #tileCalc data atributa, pa nema drugog izvora
 * istine osim onoga što je PHP ispisao.
 */
( function () {
	'use strict';

	var root = document.getElementById( 'tileCalc' );
	if ( ! root ) {
		return;
	}

	var pkgQty   = parseFloat( root.dataset.pkgQty ) || 0;
	var pkgLabel = root.dataset.pkgLabel || 'pakovanje';
	var pkgKom   = parseInt( root.dataset.pkgKom, 10 ) || 0;
	var pkgKg    = parseFloat( root.dataset.pkgKg ) || 0;
	var price    = parseFloat( root.dataset.price ) || 0;

	if ( ! pkgQty ) {
		return;
	}

	var dEl      = document.getElementById( 'calcDuzina' );
	var sEl      = document.getElementById( 'calcSirina' );
	var oEl      = document.getElementById( 'calcOtpad' );
	var oValEl   = document.getElementById( 'calcOtpadVal' );
	var resEl    = document.getElementById( 'calcResults' );
	var applyEl  = document.getElementById( 'tileCalcApply' );
	var countEl  = document.getElementById( 'tileCalcApplyCount' );
	var qtyInput = document.getElementById( 'qtyInput' );
	var wcQty    = document.getElementById( 'wcQty' );

	var result = null;

	function num( value, decimals ) {
		return value.toLocaleString( 'sr-ME', {
			minimumFractionDigits: undefined === decimals ? 2 : decimals,
			maximumFractionDigits: undefined === decimals ? 2 : decimals
		} );
	}

	/**
	 * Množina naziva pakovanja. Prilagoditi ako se uvede novi naziv.
	 */
	function pkgWord( n ) {
		if ( 'kutija' === pkgLabel ) {
			return 1 === n ? 'kutija' : ( 5 > n ? 'kutije' : 'kutija' );
		}
		if ( 'set' === pkgLabel ) {
			return 1 === n ? 'set' : ( 5 > n ? 'seta' : 'setova' );
		}
		return pkgLabel;
	}

	function calculate() {
		var d = parseFloat( dEl.value ) || 0;
		var s = parseFloat( sEl.value ) || 0;
		var o = parseInt( oEl ? oEl.value : 10, 10 ) || 0;

		if ( oValEl ) {
			oValEl.textContent = o + '%';
		}

		if ( 0 >= d || 0 >= s ) {
			resEl.innerHTML = '';
			if ( countEl ) {
				countEl.textContent = '0';
			}
			result = null;
			return;
		}

		var area      = d * s;
		var withWaste = area * ( 1 + o / 100 );
		var packages  = Math.ceil( withWaste / pkgQty );
		var covered   = packages * pkgQty;
		var total     = 0 < price ? packages * pkgQty * price : 0;

		var html = ''
			+ '<div class="cr"><span class="cr-lbl">Površina</span>'
			+ '<strong class="cr-val">' + num( area ) + '</strong><span class="cr-sub">m²</span></div>'
			+ '<div class="cr"><span class="cr-lbl">Sa otpadom</span>'
			+ '<strong class="cr-val">' + num( withWaste ) + '</strong><span class="cr-sub">m²</span></div>'
			+ '<div class="cr cr--highlight"><span class="cr-lbl">' + pkgWord( packages ) + '</span>'
			+ '<strong class="cr-val">' + packages + '</strong><span class="cr-sub">' + num( covered ) + ' m²</span></div>';

		if ( pkgKom ) {
			html += '<div class="cr"><span class="cr-lbl">Broj pločica</span>'
				+ '<strong class="cr-val">' + ( packages * pkgKom ) + '</strong><span class="cr-sub">kom</span></div>';
		}

		if ( pkgKg ) {
			html += '<div class="cr"><span class="cr-lbl">Težina</span>'
				+ '<strong class="cr-val">' + num( packages * pkgKg ) + '</strong><span class="cr-sub">kg</span></div>';
		}

		if ( 0 < price ) {
			html += '<div class="cr cr--highlight"><span class="cr-lbl">Ukupno</span>'
				+ '<strong class="cr-val">' + num( total ) + '</strong><span class="cr-sub">EUR</span></div>';
		}

		resEl.innerHTML = html;

		if ( countEl ) {
			countEl.textContent = packages;
		}

		result = {
			povrsina: area.toFixed( 2 ),
			otpad: o,
			kolicina: packages,
			label: pkgLabel,
			kom: pkgKom ? packages * pkgKom : 0
		};
	}

	function setHidden( form, name, value ) {
		var input = form.querySelector( 'input[name="' + name + '"]' );

		if ( ! input ) {
			input = document.createElement( 'input' );
			input.type = 'hidden';
			input.name = name;
			form.appendChild( input );
		}

		input.value = value;
	}

	if ( applyEl ) {
		applyEl.addEventListener( 'click', function () {
			if ( ! result ) {
				return;
			}

			if ( qtyInput ) {
				qtyInput.value = result.kolicina;
			}
			if ( wcQty ) {
				wcQty.value = result.kolicina;
			}

			var form = document.querySelector( '.pdp-cart-form' );

			if ( form ) {
				setHidden( form, 'de_calc_povrsina', result.povrsina );
				setHidden( form, 'de_calc_otpad', result.otpad );
				setHidden( form, 'de_calc_kolicina', result.kolicina );
				setHidden( form, 'de_calc_label', result.label );
				setHidden( form, 'de_calc_kom', result.kom );
			}

			var body   = document.getElementById( 'tileCalcBody' );
			var toggle = document.getElementById( 'tileCalcToggle' );

			if ( body ) {
				body.hidden = true;
			}
			if ( toggle ) {
				toggle.setAttribute( 'aria-expanded', 'false' );
			}
		} );
	}

	[ dEl, sEl, oEl ].forEach( function ( el ) {
		if ( el ) {
			el.addEventListener( 'input', calculate );
		}
	} );

	var toggleEl = document.getElementById( 'tileCalcToggle' );
	var bodyEl   = document.getElementById( 'tileCalcBody' );

	if ( toggleEl && bodyEl ) {
		toggleEl.addEventListener( 'click', function () {
			var isOpen = ! bodyEl.hidden;
			bodyEl.hidden = isOpen;
			toggleEl.setAttribute( 'aria-expanded', isOpen ? 'false' : 'true' );
		} );
	}
}() );
```

**Note on the variable-product path.** The hidden inputs above only reach the server if the product
submits a real form. When add-to-cart goes through the AJAX handler in `03-PORT-variations.md`,
append the same `de_calc_*` keys to the request body; `woocommerce_add_cart_item_data` picks them up
from `$_POST` either way.

## 7. Verify after dropping it in

- A product with `_price_unit = m²`, `_pkg_qty = 1.44`, price `24.90`: one box in the cart must cost
  **35,86**, not 24,90. Getting 24,90 means the correction hook is not firing.
- Two boxes must be exactly double. Refresh the cart page three times: the price must not creep
  upward — that is the double-multiplication bug the `_price` read protects against.
- Enter 4 × 3 m with 10% waste on a 1.44 m² box: 12 m² → 13,2 m² → **10 boxes**.
- "Primijeni" writes the box count into the quantity field.
- The cart line shows "Proračun: 12,00 m² · +10% otpad · 10 kutija".
- A door product with empty `_price_unit`: no calculator renders and its cart price is untouched.



<!-- ===== FILE: 06-DATA-MODEL-custom-fields.md ===== -->

# 06 — Data model: every custom field on a Saya product

What custom fields exist on `product` and `product_variation` on the **live** Saya Group site,
which of them come from JetEngine, which are plain theme code, and which are dead weight you
should not carry into Door Expert.

Read this before `02`–`05`: several of those port documents read these meta keys, and two of the
keys they mention are no longer the source of truth.

---

## How this was established

Not from the repo alone. Three independent live sources, cross-checked:

| Source | What it proves | Caveat |
|---|---|---|
| `GET https://sayagroup.rs/wp-json/wp/v2/product?_fields=meta` (323 published products, unauthenticated) | The exact set of meta keys JetEngine registers on `product` with **Show in REST** on | Only shows REST-exposed fields |
| `DOCS/wc-product-export-8-7-2026-1783523232149.csv` — full WooCommerce export, 1891 rows (61 simple, 211 variable, 1619 variations) | Every meta key that actually holds a value, with per-type counts | Snapshot of 2026-07-08 |
| Live PDP HTML (`https://sayagroup.rs/p/tavola-30x60/`) | Which fields still render on the front end | One product |

Two facts fall straight out of the code and settle the JetEngine question:

- `grep -rn "register_post_meta\|register_meta" wp-theme wp-plugins` → **zero hits**.
- `grep -rn "register_post_type" wp-theme wp-plugins` → **zero hits**.

So anything visible in `wp/v2/product`'s `meta` object was registered by JetEngine, and all four
CPTs (`brendovi`, `projekti`, `inspiracija`, `kolekcije`) are JetEngine too.

---

## A. JetEngine fields on `product` — 6 of them

Confirmed live on all 323 products returned by REST. Parent-level only; JetEngine puts nothing
on variations.

| meta key | Type | Purpose | Live values | Set on (simple / variable) |
|---|---|---|---|---|
| `_price_unit` | text | Unit rendered next to the price, PLP and PDP: "5.990 RSD / m²" | `m²` (232), `kom` (17) | 61 / 188 |
| `_pkg_label` | text | What one package *is*, shown in the calculator input label | `kutija` (226), `komad` (13) | 61 / 178 |
| `_pkg_qty` | decimal | m² per package. Decimal **point**, not comma | `1.44`, `1.04` | 53 / 89 |
| `_pkg_kom` | integer | Tiles per package | `8`, `25`, `52` | 53 / 102 |
| `_pkg_kg` | decimal | Package weight in kg | `29.44`, `18.52` | 53 / 102 |
| `_collection` | text | Collection name, matched against the **exact title** of a `kolekcije` CPT post, case-sensitive | `Tavola`, `Squares`, `Bright` | 61 / 211 |

Consumed in the theme at:

- [`functions.php:3038`](../../wp-theme/functions.php#L3038) `saya_price_unit()`
- [`functions.php:3120`](../../wp-theme/functions.php#L3120) `saya_pkg_data()` — `_pkg_qty` + `_pkg_label`
- [`functions.php:3133`](../../wp-theme/functions.php#L3133) `saya_pkg_kom()`
- [`functions.php:3142`](../../wp-theme/functions.php#L3142) `saya_pkg_kg()`
- [`woocommerce/single-product.php:~91`](../../wp-theme/woocommerce/single-product.php) reads `_collection` to link the PDP to its collection page

The live PDP emits them as data attributes the calculator JS reads:
`data-pkg-qty="1.44" data-pkg-kom="8" data-pkg-kg="29.44" data-pkg-label="kutija"`.

Note `saya_num()` ([`functions.php:3103`](../../wp-theme/functions.php#L3103)) parses these
tolerantly, accepting a Serbian decimal comma, because the client naturally types `0,533` in
admin and a raw `(float)` cast would silently truncate that to `0`. Port that helper with the
fields.

### The one gap in this list

REST only exposes meta whose registration has **Show in REST** ticked. A JetEngine field with
that box unticked would be invisible to everything above. Cross-checking against the full export
found no such field carrying data, so this list is complete *in practice*. To confirm it against
JetEngine's own definitions, including field labels and widget types, run
[`saya-dump-meta-fields.php`](../../saya-dump-meta-fields.php): upload to the WordPress root,
open as admin with `?run=now`, copy the Markdown, and the script deletes itself.

---

## B. Custom fields with no JetEngine — plain theme code

These are registered with native `add_meta_box` / `woocommerce_product_after_variable_attributes`
and saved by hand. **This is the tier Door Expert should copy**, because it needs no license.

| meta key | Where it lives | Registered at | Storage |
|---|---|---|---|
| `_display_name` | variation | [`functions.php:3333`](../../wp-theme/functions.php#L3333) field, [`:3348`](../../wp-theme/functions.php#L3348) save | plain text |
| `sifra_proizvoda` | variation **and** simple | [`:3405`](../../wp-theme/functions.php#L3405)/[`:3419`](../../wp-theme/functions.php#L3419) variation, [`:3427`](../../wp-theme/functions.php#L3427)/[`:3448`](../../wp-theme/functions.php#L3448) meta box | plain text |
| `kombinuje_se_sa` | product **and** variation | [`:3602`](../../wp-theme/functions.php#L3602) box, [`:3984`](../../wp-theme/functions.php#L3984) save, [`:4008`](../../wp-theme/functions.php#L4008)/[`:4025`](../../wp-theme/functions.php#L4025) variation | `wp_json_encode()` array of IDs |
| `_saya_home_featured` | product, side box | [`:3613`](../../wp-theme/functions.php#L3613)–[`:3638`](../../wp-theme/functions.php#L3638) | `'1'` or the row is deleted |

Live counts from the export:

| meta key | simple | variable | variation |
|---|---|---|---|
| `_display_name` | 0 | 0 | **1615** |
| `sifra_proizvoda` | 15 | 2 | **732** |
| `kombinuje_se_sa` | 34 | 30 | 207 |
| `_saya_home_featured` | added 2026-07-31 (`90777c1`), after this export | | |

Three details worth carrying over:

1. **`_display_name` is the whole variation-naming system.** 1615 of 1619 variations have one.
   Empty means "fall back to the parent title". It is injected into the WooCommerce variation
   payload at [`functions.php:3494`](../../wp-theme/functions.php#L3494) via
   `woocommerce_available_variation`, which is why the front end can show a per-variation name
   without a second request. The live PDP contains `display_name` inside that JSON blob.
2. **The `sifra_proizvoda` meta box hides itself on variable products**
   ([`:3427`](../../wp-theme/functions.php#L3427)–[`:3431`](../../wp-theme/functions.php#L3431)),
   because variable products get the per-variation field instead. Same key, two UIs, mutually
   exclusive. Copy that guard or you get two conflicting inputs on the same screen.
3. **`_saya_home_featured` is deliberately *not* WooCommerce's own "Featured" star.** The theme
   already uses that star to drive the "NOVO" badge in
   [`template-parts/product-card.php`](../../wp-theme/template-parts/product-card.php); sharing
   one flag would make every featured product get a NOVO badge and vice versa. Keep them separate.

---

## C. Do not port these — dead or superseded

### `saya_protivkliznost` and `saya_pei_klasa` — abandoned, data still in the DB

This is the trap. The export shows 1556 and 697 populated variation rows, so they look alive.
They are not:

- The admin fields are inside a `/* _shelf … */` comment block,
  [`functions.php:3355`](../../wp-theme/functions.php#L3355)–[`:3403`](../../wp-theme/functions.php#L3403).
- The PDP map that fed them to JS is commented out too,
  [`single-product.php:519`](../../wp-theme/woocommerce/single-product.php#L519)–[`:532`](../../wp-theme/woocommerce/single-product.php#L532).
- Live PDP confirms the replacement: `data-attr-key="attribute_pa_protivkliznost"`, a standard
  WooCommerce variation attribute.

History and the reason for the round trip are in
[`../BITNE FUNKCIONALNOSTI/WC_PROTIVKLIZNOST_VARIATION.md`](../BITNE%20FUNKCIONALNOSTI/WC_PROTIVKLIZNOST_VARIATION.md).
Short version: `attribute_pa_protivkliznost` → custom `saya_` meta (2026-06-06) → back to a plain
WC variation attribute (2026-06-25). **For Door Expert, model slip rating and PEI class as
WooCommerce global attributes from day one and skip both detours.**

One hard-won rule from that document, worth keeping whatever you do: flipping `is_variation` to
false on a global attribute cannot be done through the WooCommerce object model, because
`$product->save()` reads back from the object cache and overwrites you. It takes a direct
`$wpdb->update()` on the serialized `_product_attributes` meta.

### Fields the theme reads that nobody ever set

Present in code, zero values in the 1891-row export, absent from live REST:

| meta key | Read at | Reality |
|---|---|---|
| `_delivery_text` | [`functions.php:3158`](../../wp-theme/functions.php#L3158) | Always falls through to option `saya_delivery_text` |
| `_trust_ssl`, `_trust_warranty` | [`:3182`](../../wp-theme/functions.php#L3182), [`:3187`](../../wp-theme/functions.php#L3187) | Always fall through to `saya_trust_*` options |
| `_return_policy` | [`:3198`](../../wp-theme/functions.php#L3198) | Always falls through to `saya_return_policy` |
| `gallery_blend_mode` | [`single-product.php:91`](../../wp-theme/woocommerce/single-product.php#L91) | Added 2026-04-15 (`32b3f39`), never populated |

These are per-product overrides on top of site-wide options. The pattern is sound and cheap, so
keep the option fallback in Door Expert; just do not build admin UI for the per-product override
until someone asks. Saya has gone months without needing it.

### `_brand_import` — transient by design, not dead

Worth separating from the list above, because the export's **0 values** is the *correct* result
rather than a sign of neglect. It is a CSV-import scratch field: the importer writes a brand name
into it, [`functions.php:2970`](../../wp-theme/functions.php#L2970) converts that name into a
`product_brand` taxonomy term, and [`:2984`](../../wp-theme/functions.php#L2984) deletes the meta
row again. It is empty precisely because it works.

Port the *technique* if Door Expert imports products by CSV. WooCommerce's importer cannot assign
a custom taxonomy directly, so routing it through a throwaway meta column is the standard way
around that, and it keeps the CSV human-readable.

---

## D. Parent vs variation, at a glance

The split matters because the PDP has to merge both levels.

```
product (parent)                    product_variation
────────────────                    ─────────────────
_price_unit      (JetEngine)        _display_name       (theme)
_pkg_label       (JetEngine)        sifra_proizvoda     (theme)
_pkg_qty         (JetEngine)        kombinuje_se_sa     (theme)
_pkg_kom         (JetEngine)
_pkg_kg          (JetEngine)        saya_protivkliznost (DEAD)
_collection      (JetEngine)        saya_pei_klasa      (DEAD)

sifra_proizvoda      (theme, simple products only)
kombinuje_se_sa      (theme)
_saya_home_featured  (theme)
```

Packaging and pricing-unit data lives **only on the parent**, so the calculator reads the parent
even while the user is switching variations. Identity data (`_display_name`, `sifra_proizvoda`)
lives on the variation. Cross-sell (`kombinuje_se_sa`) exists at both levels, variation winning
when set — see [`single-product.php`](../../wp-theme/woocommerce/single-product.php), which reads
the variation key first and falls back to the parent.

---

## E. Appendix: JetEngine fields on the four CPTs

Not products, but Door Expert will hit these the moment it ports project hotspots
(`13-UI-PDP-AND-PROJECTS.md`). All four CPTs are JetEngine; their meta boxes have **Show in REST
off**, so REST returns no `meta` for them and this list comes from theme reads, not from live.
Treat it as complete-as-used, not as JetEngine's own definition.

| CPT | JetEngine fields | Theme-owned fields |
|---|---|---|
| `brendovi` | `brand_logo`, `brand_country`, `brand_country_flag`, `brand_badge`, `brand_catalog_url_*`, `brand_category_tag` | `brand_country`, `brand_country_flag`, `brand_badge` are also written by [`inc/saya-import-brendovi.php:250`](../../wp-theme/inc/saya-import-brendovi.php#L250) |
| `projekti` | `project_type`, `project_location`, `project_room`, `project_style`, `project_brands`, `project_area`, `project_gallery`, `project_hero_image_*` | `project_hotspots` ([`functions.php:3718`](../../wp-theme/functions.php#L3718)), `project_products_count` ([`:3722`](../../wp-theme/functions.php#L3722)) |
| `inspiracija` | `inspiracija_opis`, `inspiracija_stil`, `inspiracija_prostorija`, `inspiracija_slika` | `inspiracija_hotspoti` ([`functions.php:3783`](../../wp-theme/functions.php#L3783)) |
| `kolekcije` | `collection_subtitle`, `collection_short_description`, `collection_long_description`, `collection_product_cat`, `collection_brand`, `collection_image`, `collection_images`, `collection_featured` | — |

Two JetEngine storage traps you will hit here, both documented at
[`../_NOVI-PROJEKTI/tips-and-tricks/jetengine-gotchas.md`](../_NOVI-PROJEKTI/tips-and-tricks/jetengine-gotchas.md):

- **Checkbox fields store a serialized array**, `a:1:{i:0;s:4:"true";}`, never `'1'`. A
  `meta_query` with `'compare' => '='` never matches. Use `'value' => '"true"', 'compare' => 'LIKE'`.
- **Image fields store an ID only if the field type is Image**; type Text stores a URL, and
  `wp_get_attachment_image()` then silently produces nothing. Guard with
  `is_numeric( $val ) ? (int) $val : get_post_thumbnail_id( $post_id )`.

Neither trap exists if you register the fields natively, which is the recommendation below.

And one Saya-specific wart, so you do not read it as a typo in this document:
`brand_catalog_url_` and `project_hero_image_` **really do end in an underscore**. Someone left a
trailing `_` in the JetEngine field name, and once data exists the key is frozen. See
[`single-brendovi.php:48`](../../wp-theme/single-brendovi.php#L48) and
[`single-projekti.php:22`](../../wp-theme/single-projekti.php#L22). Do not reproduce it.

---

## F. Recommendation for Door Expert

**Do not take a JetEngine dependency for six text fields.** Saya's split is historical, not
designed: the calculator fields arrived through JetEngine early on, and everything added since
(`_display_name`, `sifra_proizvoda`, `kombinuje_se_sa`, `_saya_home_featured`, both hotspot
editors) was written as a plain meta box, because that turned out to be less friction.

Concretely:

1. Register all six product fields as one native `add_meta_box` on `product`. Keep the **same
   meta keys** so every snippet in `02`–`05` works unchanged, and so a CSV exported from Saya
   imports into Door Expert without remapping.
2. Copy `saya_num()` alongside them. The comma-decimal problem is real and silent.
3. Model slip rating and PEI class as WooCommerce global attributes. Never as custom meta.
4. Keep the option-fallback pattern for delivery, trust and returns text; skip the per-product
   override UI.
5. Skip `_brand_import` entirely.

That leaves JetEngine needed only for the four CPTs, and if Door Expert has no `projekti` or
`kolekcije` equivalent, not needed at all.

---

## What to verify after porting

- [ ] `wp-json/wp/v2/product?_fields=meta` on Door Expert returns the six keys (add
      `'show_in_rest' => true` to `register_post_meta` if you want parity with Saya).
- [ ] A decimal typed as `1,44` in admin survives a save and reaches the calculator as `1.44`.
- [ ] A variation with an empty `_display_name` falls back to the parent title on the PDP, in
      search results, and on collection archives — all three, they are separate code paths.
- [ ] The `sifra_proizvoda` meta box is absent on variable products and present on simple ones.
- [ ] `_saya_home_featured` and WooCommerce's Featured star drive visibly different things.
- [ ] `kombinuje_se_sa` round-trips as valid JSON, and a variation value overrides its parent's.



<!-- ===== FILE: 07-PLUGIN-filter-configurator.md ===== -->

# Filter Configurator — a drop-in plugin, not a port

This document is shaped differently from `02` through `05`. Those extract code out of the Saya
theme and hand you an adapted version. This one does not: the component already exists as a
finished, de-branded, standalone plugin. Nothing needs extracting. What follows is integration
advice and the one compatibility check that decides how much work it is.

Source: `portable-kit/plugin/wc-filter-configurator/` in the Saya repo. It carries its own 454-line
`README.md`, which is the reference manual. This document only covers what is specific to Door
Expert.

---

## 1. A correction to the audit

[`01-AUDIT-REPORT.md`](01-AUDIT-REPORT.md) lists this component as:

> | 8 | **Filter configurator plugin** (drag & drop admin) | PHP + JS + CSS | own plugin | `ADAPT (heavy)` | optional, later |

**That verdict is stale.** It was written against `wp-plugins/saya-filter-config/`, the Saya-branded
version, which is genuinely heavy to adapt: hardcoded Saya `term_id`s, a Saya default config of
tile attributes, a CDN dependency, and five helper functions it expects to find in the theme.

A de-branded standalone version was committed a week before the audit (`78ea535`, 2026-08-24) and
the audit did not account for it. In that version:

- categories are read dynamically, no hardcoded term ids
- the default configuration is a price slider and a brand filter, nothing store-specific
- SortableJS is bundled and self-hosted
- scoping, counts and faceting moved **out of the theme and into the plugin**, so there are no
  theme-side helpers left to supply

For this component the honest verdict is **`DROP-IN`**, with theme-side CSS as the only real work.

Audit item 7 is a separate matter and **still stands**:

> **Filters.** You already have server-side filtering in `inc/shop.php`. [...] do not swap yours out.

That is not in conflict with this document. Section 2 explains why.

---

## 2. The boundary

The plugin owns the sidebar. It does not own the query.

**Included:**

| | |
|---|---|
| Admin drag & drop configurator | three views: per category, global, attribute grouping |
| Configuration storage and resolution | option `wcfc_filter_configs`, keyed by term id, with inheritance |
| Front-end sidebar renderer | `wcfc_render_sidebar()` |
| Category scoping | term lists restricted to products actually in the category and its descendants |
| Correct counts | computed over that product set, not site-wide |
| Live faceting | `wcfc_compute_facets()`, with self-exclusion |
| Cache layer | transients keyed by a global version, with automatic invalidation and a manual flush |

**Not included:**

| | |
|---|---|
| No query engine | applying `?pa_*` to the main query is the theme's job |
| No AJAX product filtering | wiring clicks to a request and swapping the grid is the theme's job |
| No CSS | the markup is plain and documented; style it yourself |
| No swatch palette | supply one through `wcfc_swatch_color_map` |

So the two systems are complementary, not competing. You keep `inc/shop.php` exactly as it is and
replace only the part that is currently hardcoded: which filters appear on which category, in what
order, with what label, and what the numbers in brackets say.

---

## 3. The compatibility hinge — check this before copying anything

The renderer outputs this:

```html
<label class="wcfc-option">
  <input type="checkbox" name="pa_color" value="white">
  <span>White</span>
  <small class="wcfc-count">(12)</small>
</label>
```

The input `name` is the **taxonomy slug** and the `value` is the **term slug**. A serialised form is
therefore already a valid filter query: `?pa_color[]=white&pa_color[]=grey`.

[`01-AUDIT-REPORT.md`](01-AUDIT-REPORT.md) states that Door Expert uses the same architecture,
`pre_get_posts` plus `?pa_*` GET parameters. If that is accurate, the new sidebar feeds your
existing engine with **no change to the engine at all**.

**Verify it first.** In the Door Expert repo:

```bash
grep -n "pre_get_posts" inc/shop.php
grep -n "pa_\|tax_query\|\$_GET" inc/shop.php | head -40
```

What you are looking for is the shape your handler expects:

| What you find | What it means |
|---|---|
| Reads `$_GET['pa_something']` as an array of term slugs | Match. Nothing to do. |
| Reads a single comma-joined string (`?pa_color=white,grey`) | Near match. Either split on commas in your handler, or post-process the markup with `wcfc_filter_html`. |
| Uses its own parameter names (`?filter_color=`, `?f[]=`) | Mismatch. Cheapest fix is to accept the plugin's shape in your handler as a second accepted form, rather than rewriting the markup. |

If your handler turns out to whitelist a fixed list of attributes, drop that list and whitelist
against `wc_get_attribute_taxonomies()` instead. Otherwise the configurator can offer an attribute
that your query silently ignores, which is the worst failure mode here: the sidebar looks like it
works and returns unfiltered results.

---

## 4. Installation

1. Copy `wc-filter-configurator/` into `wp-content/plugins/`.
2. Activate. Without WooCommerce active the plugin stays inert and says so.
3. Open **Settings → Filter Configurator** and build the lists.

Nothing is written to the database on activation beyond a schema version. An unconfigured install
falls back to `wcfc_default_configs()`, which is a price slider globally and a brand filter as the
default context.

Confirm `assets/vendor/Sortable.min.js` actually arrived. It is the one file whose absence produces
a silent failure: the admin loads, drag and drop simply does nothing, and the console says
`Sortable is not defined`.

---

## 5. Wiring

### 5.1 Render the sidebar

In `woocommerce/archive-product.php`, where your current sidebar markup is:

```php
<aside class="shop-filters">
    <?php wcfc_render_sidebar(); ?>
</aside>
```

The context is auto-detected from the current category. Other forms:

```php
wcfc_render_sidebar( [ 'context' => 'svi-proizvodi' ] ); // custom landing page
$html = wcfc_render_sidebar( [ 'echo' => false ] );       // return instead of echo
wcfc_render_sidebar( [ 'scope' => false ] );              // site-wide terms, no category scoping
```

Do not use `scope => false` on a category archive. It is what re-introduces ghost values on
variable products, because it bypasses `wcfc_used_attr_slugs()`.

### 5.2 Brand taxonomy

The default is `product_brand`. If Door Expert uses a plugin taxonomy, say so once:

```php
add_filter( 'wcfc_brand_taxonomy', function () {
    return 'pwb-brand';
} );
```

### 5.3 Swatch colours

A `swatch` filter with no colour map falls back to checkboxes rather than rendering blank circles,
so nothing breaks if you skip this. For doors, the decor attribute is the obvious candidate.
Replace the slugs with the real ones:

```php
add_filter( 'wcfc_swatch_color_map', function ( $map, $taxonomy ) {
    if ( 'pa_dekor' !== $taxonomy ) {
        return $map;
    }
    return [
        'bijeli'     => '#f4f1ec',
        'hrast'      => '#c9a267',
        'orah'       => '#6b4a2f',
        'antracit'   => '#3a3d42',
    ];
}, 10, 2 );

add_filter( 'wcfc_swatch_light_slugs', function ( $slugs, $taxonomy ) {
    return 'pa_dekor' === $taxonomy ? [ 'bijeli' ] : $slugs;
}, 10, 2 );
```

`wcfc_swatch_light_slugs` gives light colours an outline so a white swatch is not invisible on a
white sidebar.

### 5.4 A starting configuration

Rather than building it by hand in the admin on every environment, ship one:

```php
add_filter( 'wcfc_default_configs', function ( $configs ) {
    $configs['default'] = [
        [
            'attr'      => 'price_slider',
            'label'     => 'Cijena',
            'type'      => 'price_slider',
            'collapsed' => false,
        ],
        [
            'attr'      => 'pa_dekor',
            'label'     => 'Dekor',
            'type'      => 'swatch',
            'collapsed' => false,
        ],
        [
            'attr'      => 'pa_dimenzije',
            'label'     => 'Dimenzije',
            'type'      => 'checkbox',
            'collapsed' => true,
        ],
    ];
    return $configs;
} );
```

This is only a fallback. The moment somebody saves anything in the admin, the stored option wins.

---

## 6. What you have to supply

### 6.1 CSS

The plugin ships none, deliberately. Your existing sidebar CSS will not match anything, because the
markup uses `wcfc-` classes. Two ways out:

**Style against the plugin's classes.** The full DOM contract is in the plugin README section 7.
The short version:

| Element | Selector |
|---|---|
| Filter group | `.wcfc-group`, plus `.wcfc-group--checkbox` / `--swatch` / `--price` / `--category`, `data-attr` |
| Collapsed state | `.is-collapsed` |
| Heading | `.wcfc-group__title` with `.wcfc-arrow` |
| Options wrapper | `.wcfc-group__options` |
| Checkbox row | `.wcfc-option` containing `input[name][value]` |
| Count | `.wcfc-count` |
| Swatch | `button.wcfc-swatch[data-attr][data-value][data-count]`, colour via `--wcfc-swatch-color`, light variant `.wcfc-swatch--light` |
| Category link | `a.wcfc-option.wcfc-option--link` |
| Price slider | `#wcfc-price-min`, `#wcfc-price-max`, `#wcfc-price-fill`, labels `#wcfc-price-min-label` / `#wcfc-price-max-label` |
| Section | `.wcfc-section`, `.wcfc-section__title`, `.wcfc-section__body` |

**Or keep your own classes** by post-processing the markup, if your existing CSS is large enough to
be worth preserving:

```php
add_filter( 'wcfc_sidebar_html', function ( $html ) {
    return str_replace( 'wcfc-group__title', 'filter-group__title', $html );
} );
```

The first option is cleaner. The second is faster on day one and accumulates debt, so use it only
if the sidebar CSS is substantial.

### 6.2 Your sidebar JavaScript

Whatever currently binds to your sidebar has to be re-pointed at the selectors above. The checkbox
contract is the same idea as yours (`input` with a taxonomy name and a term-slug value), so this is
usually a selector change rather than a rewrite.

Collapsing is not wired for you. `.is-collapsed` is rendered as an initial state; toggling it on
click is three lines in your own script.

---

## 7. Counts and greying out zero results

The numbers rendered in `.wcfc-count` are correct on page load: scoped to the category, and
variation-aware, so a variable product does not advertise a term that has no purchasable variation.

To keep them correct **while** filtering, recompute and compare:

```php
$facets = wcfc_compute_facets(
    $term_id,          // current category
    $selected,         // [ 'pa_dekor' => [ 'hrast' ], ... ]
    $min_price,
    $max_price,
    wcfc_attrs_for_context( (string) $term_id )
);
```

The return is `[ taxonomy => [ slug => count ] ]`. Anything at zero gets greyed out rather than
hidden, which is the behaviour that keeps a facet list from shifting under the cursor.

Self-exclusion is the part worth understanding: each facet is counted over the products satisfying
every **other** active filter but not itself. Without it, ticking one decor zeroes out every other
decor in the same group and multi-select becomes unusable.

---

## 8. Caching

Category product lists and facet counts sit in transients whose key embeds one global version
number (option `wcfc_cat_pids_ver`). Bumping it invalidates everything at once. That is deliberate:
a per-category invalidator misses the case that matters, a product being *removed* from a category.

Automatic invalidation fires on `save_post_product`, `before_delete_post`, `set_object_terms` for
`product_cat`, and category create, edit and delete.

The **Flush filter cache** button covers what bypasses WordPress entirely. If Door Expert imports
products with direct SQL, or with a CLI importer that skips hooks, that button is not optional
housekeeping, it is the only thing that will fix the numbers.

---

## 9. What this buys you

Without it, every change to the filter list is a code change and a deploy, and the archive template
accumulates `if ( $category === ... )` branches as soon as the catalogue has more than one product
type. Door Expert has three at launch: doors, tiles, basins. Those want genuinely different filter
sets, and doors want a decor filter that means nothing on a basin.

The other half is correctness. A hardcoded sidebar tends to lie in three specific ways, and each
one is fixed here:

| Lie | Fix |
|---|---|
| A filter offers a value no product in this category has | Terms scoped to the category's actual products |
| The count in brackets is the site-wide count | Counts computed over the category's product set |
| A variable product advertises a term with no purchasable variation | Variation meta is the source of truth |

---

## 10. Known limits

| Limit | Detail |
|---|---|
| Sections do not nest | One level, deliberately |
| Admin tabs are top-level categories | A subcategory inherits the nearest configured ancestor. For full control, give it its own context key or use per-filter `categories` |
| No import or export | Configuration moves between environments through `wp_options` only. Staging to production is a manual re-entry or a database copy |
| `categories` scoping is inert on global filters | The global view has no child-category context |
| Multi-word attribute slugs | WooCommerce slugifies with dashes, `pa_surface-finish` not `pa_surface_finish`. The plugin auto-corrects a mismatched slug once, on the first admin load |

---

## 11. Verification

Same standing caveat as the rest of this package: none of this has run inside Door Expert.

- [ ] `grep` confirms `inc/shop.php` accepts `?pa_<taxonomy>[]=<term-slug>`, or the handler was
      extended to accept it
- [ ] the attribute whitelist in your handler comes from `wc_get_attribute_taxonomies()`, not a
      fixed list
- [ ] plugin activates, **Settings → Filter Configurator** lists Door Expert's real categories as tabs
- [ ] `assets/vendor/Sortable.min.js` present, drag and drop works
- [ ] drag an attribute in, save, reload: it persists, its chip is greyed out in the pool
- [ ] reorder, save, open a category: the sidebar follows that order
- [ ] a term count in brackets matches the result count after clicking it
- [ ] a subcategory with no configuration of its own shows the parent's set
- [ ] a global filter appears first on every archive, with no duplicate where the category already
      has it
- [ ] a variable door whose variations share a decor does not advertise a decor with no
      purchasable variation
- [ ] move a product out of a category, reload: counts update
- [ ] logged out, POST to `wcfc_save_config`: it fails

---

## 12. Source and further reading

| What | Where |
|---|---|
| The plugin | `portable-kit/plugin/wc-filter-configurator/` |
| Its reference manual | `portable-kit/plugin/wc-filter-configurator/README.md` (14 sections, full API, hooks, DOM contract, troubleshooting) |
| The wider portable module | `portable-kit/README.md` — gallery, variation image swap, an AJAX filter engine, product card, cart AJAX. Relevant if you ever want the query layer too |
| Analysis of the original Saya plugin | `DOCS/FILTER_KONFIGURATOR_HANDOFF.md` — read only if you need the history or the list of bugs that were fixed on the way to the standalone version |
| What the filters actually show and count | `DOCS/BITNE FUNKCIONALNOSTI/FILTERI_ATRIBUTI.md` — the reasoning behind scoping, variation awareness and the cache, in Serbian |
