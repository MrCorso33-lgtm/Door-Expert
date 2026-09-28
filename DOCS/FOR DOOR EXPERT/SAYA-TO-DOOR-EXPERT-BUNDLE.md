# Saya Group → Door Expert — architecture and logic (bundle 1 of 2)

Nine documents of the reuse audit, concatenated for easy transfer.

**There is a second bundle.** `SAYA-TO-DOOR-EXPERT-UI-BUNDLE.md` covers presentation: texture
swatches, the ambient-first product card, trust blocks, project hotspots. Read this one first.

**To the agent receiving this:** each original document is delimited below by a
`<!-- ===== FILE: <name> ===== -->` marker. Split it back into nine files under
`DOCS/FOR DOOR EXPERT/` in the Door Expert repo, or just read it straight through.
Reading order is the numeric order of the files.

Source site: Saya Group (ceramic tiles and bathroom fixtures, Serbia), custom WordPress theme.
Target site: Door Expert (doors, Spanish tiles, decorative basins; Podgorica, Montenegro).

The audit was read-only. No file on the source site was modified. Snippets are syntax-checked but
have never run inside Door Expert.

One component travels as working code rather than as a snippet: the filter configurator plugin,
`portable-kit/plugin/wc-filter-configurator/` in the Saya repo. Document `07` explains what to do
with it; copy the folder across alongside this bundle.

**`08` is not like the others.** Documents `00` through `07` were written before the Door Expert
repo was available, so everything they say about that side is inference. `08` was written after
`07` had been implemented, with the repo in hand: its file names, hooks, DOM classes and URL
parameters were read out of the Door Expert theme, not guessed. Where the two disagree about Door
Expert, `08` wins. It is also where the three remaining gaps are listed, one of which is a live bug.

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
| [`03-PORT-variations.md`](03-PORT-variations.md) | **Rewritten against your real code.** Variable products, as a parity checklist for the pill-over-WooCommerce-select bridge you already built, not a port. It corrects the original on its own headline claim: no custom add-to-cart handler is needed on a form POST. Three of its six items are real bugs, and the biggest only appears once the tile collections go in. |
| [`04-PORT-gallery-lightbox.md`](04-PORT-gallery-lightbox.md) | PhotoSwipe v5 bridge, ES-module enqueue, real image dimensions. |
| [`05-PORT-tile-calculator.md`](05-PORT-tile-calculator.md) | Tile m² calculator plus, more importantly, the per-m² cart pricing correction. |
| [`06-DATA-MODEL-custom-fields.md`](06-DATA-MODEL-custom-fields.md) | Every custom field on a Saya product, verified against the **live** site: which six come from JetEngine, which are plain theme code, and which two look alive in the database but are abandoned. Read before `02`–`05`, which reference these keys. |
| [`07-PLUGIN-filter-configurator.md`](07-PLUGIN-filter-configurator.md) | **Corrects the audit.** The filter configurator is listed there as `ADAPT (heavy)`, but that verdict was written against the Saya-branded plugin; a de-branded standalone already existed. It is a `DROP-IN` that configures the sidebar and leaves your query engine alone. |
| [`08-PARITY-faceting-seo-ajax.md`](08-PARITY-faceting-seo-ajax.md) | **Written against your real code**, after `07` was implemented. Closes the three gaps that are left: live faceting is not wired (a real bug — counts never recompute), filter URLs have no `noindex` or `canonical`, and there is no AJAX. Also confirms your query hook is better than Saya's and should not be replaced. |

Each `PORT-*` document has the same shape: what it does → Saya source with `file:line` →
dependencies and coupling → data-model mapping → **adapted code** → wiring → what to verify.
There are three exceptions. `07`: nothing needs extracting, so it is integration advice instead.
`08`: a parity checklist rather than a port, two of whose three items are closed by calling functions
that already exist in the Door Expert theme. `03`: also a parity checklist now, because the component
it covers was built before this package was read, and differently from Saya. It quotes the Saya code
it carries in full rather than pointing at a repo you cannot open.

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

- **None of the adapted code has ever run inside Door Expert.** These are reviewed drafts, not
  tested code. Every document ends with a verification checklist for exactly this reason.
- **`00`, `01`, `02` and `04` through `07` were written blind.** The Door Expert repo was not
  available during the audit, so every assumption about that side is an assumption. `08` and the
  rewritten `03` are the exceptions: both were written later, with the repo in hand, and their
  references to Door Expert files were read rather than guessed. Where they disagree with the rest
  about Door Expert, they win. `03` says where and why it overrules the audit; so does `08`.
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
  [`08`](08-PARITY-faceting-seo-ajax.md) revisits the archive filtering once, having read it: the
  verdict stays "do not replace", and the work it does propose sits beside your query layer rather
  than on top of it.



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
| 2 | **Variation matching engine** (availability, auto-select, cascade) | JS | none | ~~`ADAPT (light)`~~ → take the **cascade and grey-out rules only** | **revised**, see [`03-PORT-variations.md`](03-PORT-variations.md) |
| 3 | **Custom variation add-to-cart** | PHP | WooCommerce | ~~`DROP-IN` after renaming~~ → **do not port** | **superseded**, see [`03-PORT-variations.md`](03-PORT-variations.md) §2 |
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

> **This item has been revised and the paragraph below is kept only as a record of what it said.**
> Door Expert had already built its variation selector by the time `03` was rewritten, and it built it
> on WooCommerce's own `variations_form` with a pill layer over real `<select>` elements. On that
> architecture the add-to-cart argument below **does not apply**: the trap lives in
> `?wc-ajax=add_to_cart`, and a plain form POST resolves "Any" attributes correctly by itself. Do not
> port the handler. What is still worth taking is the cascade and the grey-out rules, and `03` carries
> them in full, rewritten against Door Expert's DOM.

~~The add-to-cart handler alone justifies the port. WooCommerce's own AJAX endpoint **cannot** add a
variation that has an "Any" attribute from a custom UI; it throws before any filter can intervene.
Saya's handler resolves that server-side. You will hit this the first time a door has an
"Any colour" variation, and the failure mode is a confusing "X is a required field" error.~~

~~Take the ~90-line matching engine, leave the ~850 lines of Saya-specific UI.~~

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
- **Worth stealing specifically:** the SEO handling at `:1052-1170` (the block has since drifted to
  around `:1026`). Filtered URLs get ~~`noindex,follow`~~ **`noindex, nofollow`** and a canonical
  back to the clean category. Without it, every filter combination becomes an indexable
  near-duplicate. This is the part most sites get wrong. **Adapted code is now in
  [`08-PARITY-faceting-seo-ajax.md`](08-PARITY-faceting-seo-ajax.md) §3**, which also covers the
  `robots.txt` ordering trap.
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
- **Adapted code is now in [`03-PORT-variations.md`](03-PORT-variations.md) §8.4**, which also notes
  that Door Expert gets the receiving half for free: `wc_dropdown_variation_attribute_options()`
  reads `attribute_*` straight out of the request, so the link is all that is missing.

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
| **SEO robots + canonical for filtered URLs** | `functions.php:1026+`, `DOCS/SEO_ROBOTS_NOINDEX.md`, **adapted in [`08`](08-PARITY-faceting-seo-ajax.md) §3** | Filter and sort URLs get ~~`noindex,follow`~~ **`noindex, nofollow`** plus a canonical to the clean category. Prevents thousands of near-duplicate URLs. Genuinely the highest-value SEO item in this repo. |
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

# Variable products — parity checklist for the pill bridge you already built

**This document replaces an earlier version of `03` and contradicts it on purpose.** The earlier one
was written blind, before the Door Expert repo was available. It assumed you would build a fully
custom variation selector with its own state and its own AJAX add-to-cart endpoint, the way Saya did.
You built something different and, in most respects, better: the pills are a visual layer over real
WooCommerce `<select>` elements, and WooCommerce itself does the matching.

**Keep that architecture.** Everything below sits on top of it. Nothing here asks you to replace
`variations_form`, and §2 explains why the old document's headline argument does not apply to your
code at all.

Like [`08-PARITY-faceting-seo-ajax.md`](08-PARITY-faceting-seo-ajax.md), this was written **with your
repo in hand**. Every function name, id, CSS class and hook below was read out of
`wp-content/themes/door-expert/` as it stands on 2026-09-26, not guessed.

**You cannot read the Saya repo, so nothing here points at it for substance.** Where Saya's code is
carried over, it is reproduced in full and rewritten against your DOM. The one thing you will not
find below is a line-number reference you are expected to go and look up.

---

## 1. Where you actually are

| File | What it does today |
|---|---|
| `template-parts/product/single.php:249-282` | Renders `.variations_form` with `data-product_variations`, one `.product-variants` row per variation attribute, each holding a real `wc_dropdown_variation_attribute_options()` select plus an empty `.product-variants__pills` container |
| `template-parts/product/single.php:321-328` | `wp.template()` markup templates WC needs, because you do not route through `woocommerce_variable_add_to_cart()` |
| `assets/js/product.js:164-438` | The bridge: builds pills from `select.options`, clicks write to the select and trigger jQuery `change`, WC does the rest |
| `inc/product-variations.php` | Orderability and availability text. **Not** what the old `03` meant by that filename |
| `inc/product.php:66-91` | `door_expert_stock_display()`, one source of truth shared by PHP and JS through `wp_localize_script` |
| `functions.php:210-236` | Enqueues `wc-add-to-cart-variation` as a **dependency** of `product.js`, so WC initialises first |

This is a good design and the comments in it are better than Saya's. Six things are missing, and
three of them are real bugs rather than polish.

| Behaviour | Saya | Door Expert | Verdict |
|---|---|---|---|
| Matching engine owner | hand-written JS | WooCommerce | **Yours is better. Do not change it.** |
| Impossible option is greyed, not removed | ✅ | ❌ **it disappears** | §3 — fix first |
| Works above 30 variations | ✅ | ❌ **silently switches off** | §4 — tiles hit this |
| Shopper can change an earlier pick | ✅ | ❌ dead end | §5 |
| Mobile sticky CTA adds to cart | ✅ | ❌ **does nothing on variable** | §6 |
| CTA reflects "no price yet" | ✅ | ❌ always promises a price | §7 |
| `aria-pressed` on the selected option | ❌ | ✅ | **you are ahead** |
| Per-row "selected" readout | strip at the bottom | ✅ per row | **you are ahead**, see §8.5 |

## 2. Correction: you do not need a custom add-to-cart handler

The old `03` opened with this, and it was the stated reason to port at all:

> The add-to-cart handler alone justifies the port. WooCommerce's own AJAX endpoint **cannot** add a
> variation that has an "Any" attribute from a custom UI; it throws before any filter can intervene.

That is true of the path Saya uses and **false for the path you use.** The trap lives in
`?wc-ajax=add_to_cart`, which loads the variation's own stored attributes and throws
`"<Attribute> is a required field"` when an "Any" attribute comes back as an empty string. Your PDP
does not go anywhere near it: `.product-cta-form` is a real `method="post"` form, so submission lands
in `WC_Form_Handler::add_to_cart_action()` → `add_to_cart_handler_variable()`, which reads
`$_REQUEST['attribute_*']` from your selects and only complains when a value is **absent**. Your
selects always post a value once a pill is active.

So: **do not add `door_expert_add_to_cart_ajax()`. Delete it from your notes.** The old document's
`inc/product-variations.php` snippet, its `.variation-row` markup contract and its
`assets/js/variations.js` are all superseded, and the filename it wanted is already taken by
something unrelated.

Two smaller things the old document proposed that you should also skip:

- **`door_expert_hidden_pdp_attributes()`**, a list of variation attributes not offered as a choice.
  Your template already achieves this structurally: variation attributes become pills, everything
  else goes to Specifikacije. A second mechanism would be a second source of truth.
- **`get_available_variations()` emitted as `<script type="application/json">`.** You already emit it
  as `data-product_variations` where WC expects it. §4 adds a *different*, much smaller payload for a
  different reason.

What follows is what Saya's engine genuinely does better, expressed as additions to your files.

---

## 3. Gap 1 — an impossible option vanishes instead of greying out

**Effort: half a day. Highest value of the six. Do this first.**

### 3.1 What happens today

`assets/js/product.js:305-320` builds pills by walking `select.options` and greys a pill when
`opt.disabled` is true:

```js
if ( opt.disabled ) {
  pill.disabled = true;
  pill.classList.add( 'is-disabled' );
  pill.title = 'Nedostupno uz trenutni izbor';
}
```

WooCommerce never sets `disabled` on those options. In `update_variation_values` it rebuilds each
select from a pristine snapshot, tags the options that are reachable given the *other* current
selections, and then throws the rest away:

```js
// Detach unattached.
new_attr_select.find( 'option:not(.attached)' ).remove();
...
// Detach fully disabled options.
new_attr_select.find( 'option.attached:not(.enabled)' ).remove();
```

So that branch is dead code, `.product-variant-pill.is-disabled` in `assets/css/product.css:572-580`
has never rendered, and the actual behaviour is that the pill **disappears**.

**Confirm in 30 seconds.** Open a variable door with two attributes, pick a size, and count the pills
in the colour row before and after. If the count drops, this is your site.

### 3.2 Why it matters commercially

Three separate problems, all from the same cause:

1. **The row reflows under the cursor.** The shopper moves toward a pill and it moves. This is the
   exact behaviour [`08`](08-PARITY-faceting-seo-ajax.md) §5 tells you not to accept on filters
   ("Do not hide zero-result options. Grey them."), and the reasoning is identical here.
2. **The catalogue looks smaller than it is.** A door that exists in walnut but not in walnut at
   90×200 shows no walnut pill once 90×200 is picked. The shopper concludes you do not sell walnut
   doors and leaves. Greyed-and-struck-through says "we have it, not in this size", which is a
   conversation, not a dead end.
3. **It sets up §5.** You cannot click an option that is not in the DOM, so the shopper can never
   change their mind about the first attribute they picked.

### 3.3 The fix in one sentence

Stop building pills from `select.options`. Render the **full** option list server side, compute
availability from your own variation map, and keep the select purely as the selection store and POST
carrier.

Three additions: the option list (§3.4), the map (§4.2), and the rewritten bridge (§9).

### 3.4 The full option list, per row

Add to the end of `inc/product-variations.php`. It mirrors what
`wc_dropdown_variation_attribute_options()` does internally, including term ordering, so the pills
and the select can never disagree about labels or order.

```php
/**
 * Puna lista opcija jednog varijacijskog atributa, u poretku iz admina.
 *
 * Isti podatak koji wc_dropdown_variation_attribute_options() stavlja u <select>,
 * ali kao niz: pilule ga citaju iz data-options i time prestaju da zavise od
 * select.options, iz kojeg WC BRISE nemoguce opcije.
 *
 * @param WC_Product $product        Roditeljski proizvod.
 * @param string     $attribute_name Naziv atributa (npr. `pa_boja`).
 * @param array      $options        Vrijednosti iz get_variation_attributes().
 * @return array<int,array{value:string,label:string}>
 */
function door_expert_variation_option_list( $product, $attribute_name, $options ) {
	$list = array();

	if ( taxonomy_exists( $attribute_name ) ) {
		// wc_get_product_terms drzi poredak iz admina, isti kao u <select>-u.
		$terms = wc_get_product_terms( $product->get_id(), $attribute_name, array( 'fields' => 'all' ) );

		foreach ( $terms as $term ) {
			if ( ! in_array( $term->slug, $options, true ) ) {
				continue;
			}

			$list[] = array(
				'value' => $term->slug,
				'label' => apply_filters( 'woocommerce_variation_option_name', $term->name, $term, $attribute_name, $product ),
			);
		}

		return $list;
	}

	foreach ( $options as $option ) {
		$list[] = array(
			'value' => $option,
			'label' => apply_filters( 'woocommerce_variation_option_name', $option, null, $attribute_name, $product ),
		);
	}

	return $list;
}
```

Then in `template-parts/product/single.php`, extend the row wrapper at line 259:

```php
<div class="product-variants"
	data-attribute="attribute_<?php echo esc_attr( $de_attr_id ); ?>"
	data-options="<?php echo esc_attr( wp_json_encode( door_expert_variation_option_list( $de_product, $de_attr_name, $de_attr_options ) ) ); ?>">
```

Your three variation attributes (`pa_boja`, `pa_dimenzije-vrata`, `pa_dimenzije-plocica`) are all
global taxonomies, so the first branch is the one that runs. The second exists for per-product custom
attributes; if you ever add one, be aware that WooCommerce compares custom attribute values through
`sanitize_title()` in some paths and raw in others, so prefer global attributes.

### 3.5 CSS: a greyed pill is still clickable

Replace `assets/css/product.css:571-580`. The change is `cursor`, and it is not cosmetic: the pill
now does something when clicked (§5), and `not-allowed` on a working button is worse than no signal
at all.

```css
/*
 * Kombinacija nije moguca uz trenutni izbor. Siva i precrtana, ali KLIKABILNA:
 * klik oslobadja izbor koji je blokira (kaskada, vidi product.js). Zato pointer,
 * ne not-allowed.
 */
.product-variant-pill.is-disabled {
  opacity: 0.35;
  cursor: pointer;
  text-decoration: line-through;
}

.product-variant-pill.is-disabled:hover {
  opacity: 0.6;
  border-color: var(--color-sand, #DDD5C4);
}
```

The new bridge also stops setting the `disabled` property and does not set `aria-disabled`, because
the button is genuinely not disabled. Saya sets `aria-disabled="true"` on its unavailable options
while leaving them clickable, which is a contradiction a screen reader user has to work around. Do
not carry that over.

---

## 4. Gap 2 — above 30 variations the whole enhancement switches itself off

**Effort: an hour once §3 is in. This is the one that will bite the tiles catalogue.**

### 4.1 What happens today

`template-parts/product/single.php:93-96` respects WooCommerce's AJAX threshold, correctly:

```php
$de_var_threshold = apply_filters( 'woocommerce_ajax_variation_threshold', 30, $de_product );
$de_var_json      = count( $de_product->get_children() ) <= $de_var_threshold
	? $de_product->get_available_variations()
	: false;
```

Above the threshold the attribute becomes `data-product_variations="false"`, and inside
`wc-add-to-cart-variation.js` that flips one switch:

```js
this.useAjax = false === this.variationData;
```

`onUpdateAttributes` then returns immediately:

```js
if ( form.useAjax ) {
	return;
}
```

Two consequences, both silent:

- **No option filtering at all.** Every option in every row stays present and enabled, whether or not
  a matching variation exists. The shopper picks a combination, waits for an AJAX round trip, and is
  told "Ova kombinacija trenutno nije dostupna." Trial and error, one network request per attempt.
- **`woocommerce_update_variation_values` never fires.** That is the only event your bridge listens
  to (`product.js:368`), so `syncPills()` runs once at page load and then never again. Pills keep
  whatever state they were born with.

**Confirm in 30 seconds.** `view-source` a product with more than 30 variations and search for
`data-product_variations="false"`.

### 4.2 Why tiles cross the threshold and doors do not

A door is two attributes: three sizes times two or three finishes, comfortably under 30. A ceramic
collection is a colour range times a format range, and 6 × 6 = 36. Ribesalbes and Tau collections
routinely go past that. **So this gap does not exist on the product type you built the PDP against,
and appears the day the tiles go in.** That is the worst shape a bug can have.

The cheap fix is to raise the threshold:

```php
add_filter( 'woocommerce_ajax_variation_threshold', function () { return 200; } );
```

Do not do that as the primary fix. `get_available_variations()` is fat: per variation it carries
`price_html`, `availability_html`, a full `image` object with `srcset` and `sizes`, plus every
dimension and weight field. For 60 variations that is comfortably over 100 KB of JSON in the HTML,
on a page whose LCP is a product photo.

Emit a small map instead, sized for exactly what the pills need. Add to `inc/product-variations.php`:

```php
/**
 * Kompaktna mapa varijacija za sloj pilula.
 *
 * WC svoju mapu (data-product_variations) izostavlja iznad 30 varijacija i tada
 * prestaje da filtrira opcije. Kolekcija plocica sa 6 boja i 6 formata je vec
 * preko tog praga, pa pilule moraju imati svoj izvor podataka. Ovdje je samo ono
 * sto pilulama treba: sto manje bajtova, nikad izostavljeno.
 *
 * Prazan string u `attrs` je WC-ov dzoker ("Bilo koja vrijednost"), ne vrijednost.
 *
 * @param WC_Product $product Roditeljski proizvod.
 * @return array<int,array{id:int,attrs:array<string,string>,price:float,stock:string}>
 */
function door_expert_variation_map( $product ) {
	if ( ! $product instanceof WC_Product || ! $product->is_type( 'variable' ) ) {
		return array();
	}

	$map = array();

	foreach ( $product->get_children() as $child_id ) {
		$variation = wc_get_product( $child_id );

		if ( ! $variation instanceof WC_Product_Variation ) {
			continue;
		}

		/*
		 * Namjerno NE koristimo variation_is_visible(): ona je false i kad je cijena
		 * prazna, pa bi dimenzija kojoj cijena jos nije unesena nestala sa PDP-a.
		 * U quote modelu "cijena na upit" je validno stanje. Nepublikovana varijacija
		 * je druga stvar i tu WC ima pravo.
		 */
		if ( 'publish' !== get_post_status( $child_id ) ) {
			continue;
		}

		$map[] = array(
			'id'    => $variation->get_id(),
			'attrs' => $variation->get_variation_attributes(),
			'price' => (float) $variation->get_price(),
			'stock' => $variation->get_stock_status(),
		);
	}

	return $map;
}
```

Emit it once inside the form in `template-parts/product/single.php`, right after the closing `</div>`
of `.variations`:

```php
<?php
/*
 * Inertni podatak, ne asset: sloj pilula racuna dostupnost iz njega kad WC svoju
 * mapu izostavi (iznad 30 varijacija). Isti razlog kao za wp.template() sablone
 * iznad, pa i isto svjesno odstupanje od CLAUDE.md sekcije 4.
 */
?>
<script type="application/json" id="door-expert-variation-map"><?php echo wp_json_encode( door_expert_variation_map( $de_product ), JSON_HEX_TAG ); ?></script>
```

`JSON_HEX_TAG` is there so a `<` that ever reaches a term slug cannot close the script tag early.

`get_variation_attributes()` on a variation returns keys already prefixed (`attribute_pa_boja`), and
an **empty string for an "Any" attribute**. Every comparison in §9 treats that empty string as a
wildcard, which is the single detail Saya's engine gets right and naive matching gets wrong.

### 4.3 What still works above the threshold, so you do not rebuild it

Worth knowing, because it is not obvious: in AJAX mode WC fetches the chosen variation through
`WC_AJAX::get_variation()`, which returns `$product->get_available_variation( $variation_id )` and so
still runs the `woocommerce_available_variation` filter. Your
`door_expert_variation_stock_data()` in `inc/product-variations.php:112-117` therefore keeps
injecting `door_expert_stock_status`, and `show_variation` still arrives with `price_html`, `image`
and `display_price` intact.

**So price, stock, image and quantity clamping already work in both modes.** Only option filtering
and the `woocommerce_update_variation_values` event are lost, and §9 stops depending on both by
listening to the selects' own `change` event instead.

### 4.4 Two WooCommerce settings that will delete pills behind your back

Both interact with variable products in a quote model, and both are easy to get wrong once and never
notice.

**A variation with a blank price is hidden by WooCommerce.** `variation_is_visible()` requires
`'' !== $this->get_price()`, and `get_available_variations()` drops anything it hides. Note this is
about a **blank** price, not a zero: `0` passes, `''` does not. Your
`woocommerce_is_purchasable` filter in `inc/quote-cart.php:92-102` already treats both as
purchasable, so the two halves disagree, and the symptom is the worst kind: the pill renders from
`data-options`, the shopper clicks it, and the button never enables.

The working rule for admin is "type 0, never leave it blank". Belt and braces, so a blank price is
never fatal:

```php
/**
 * Varijacija bez cijene ostaje u ponudi.
 *
 * WC je krije jer variation_is_visible() trazi da cijena nije prazna, pa bi
 * dimenzija bez unesene cijene ispala i iz get_available_variations(). Tada je
 * pilula tu, a dugme se ne otvara - najgori moguci ishod.
 *
 * Cijena 0 i PRAZNA cijena nisu isto: sa 0 WC svejedno prikazuje varijaciju i ovaj
 * filter nije potreban. Radno pravilo za admin ostaje "upisi 0, ne ostavljaj prazno".
 *
 * @param bool $visible      Zatecena vrijednost.
 * @param int  $variation_id ID varijacije.
 * @return bool
 */
function door_expert_variation_visible_without_price( $visible, $variation_id ) {
	if ( $visible ) {
		return $visible;
	}

	return 'publish' === get_post_status( $variation_id );
}
add_filter( 'woocommerce_variation_is_visible', 'door_expert_variation_visible_without_price', 10, 2 );
```

Filter `woocommerce_variation_is_visible`, not `woocommerce_hide_invisible_variations`. The latter
looks like the obvious switch and also un-hides **unpublished** variations, which is not what you
want.

**"Hide out of stock items from the catalog" removes out-of-stock variations too.** In
WooCommerce → Settings → Products → Inventory. Leave it **off**. Your
`inc/product-variations.php` already argues the case for doors ("zaliha je informacija, ne
prepreka"), and that argument holds for tiles as well. With it on, an out-of-stock format silently
leaves `get_available_variations()` and the shopper cannot ask about it.

---

## 5. Gap 3 — the shopper cannot change their mind

**Effort: included in §9. This is the behaviour worth taking from Saya.**

With §3 in place, every option is always on screen. The question is what a click on a greyed one
should do. WooCommerce has no answer, because in its model that option does not exist.

Saya's answer, and it is the right one for a salon catalogue: **keep every pick that is still
possible alongside the new one, and clear only the picks that contradict it.** Concretely, the
shopper picks 90×200, then decides on walnut, which does not come in 90×200. Instead of a dead end,
the size clears, walnut is selected, and the size row reopens with the sizes walnut does come in.

Saya's original, which is what §9 re-implements against your selects:

```js
// Smart cascade: keep any other selection that is still compatible with
// the new key=val. Clear anything that would make an impossible combo,
// regardless of whether it was auto-selected or explicitly chosen.
attrRows.forEach( function ( otherRow ) {
    if ( otherRow.classList.contains( 'variation-row--hidden' ) ) return;
    var otherKey = otherRow.dataset.attrKey;
    if ( otherKey === key ) return;
    if ( ! selectedAttrs[ otherKey ] ) return;

    var testAttrs = {};
    testAttrs[ key ]      = val;
    testAttrs[ otherKey ] = selectedAttrs[ otherKey ];
    if ( ! isComboAvailable( testAttrs ) ) {
        delete selectedAttrs[ otherKey ];
        autoSelectedKeys.delete( otherKey );
        otherRow.querySelectorAll( '.variation-opt' ).forEach( function ( b ) { b.classList.remove( 'active' ); } );
    }
} );
```

The test is deliberately **pairwise**, new value against one other value at a time, rather than
against the whole selection at once. Testing the whole selection would clear more than necessary:
with three attributes, two existing picks can each be fine with the new value while the three
together are impossible, and clearing both when clearing one would do is the kind of thing that makes
a selector feel like it is fighting you.

One coupling to know about when this runs against real `<select>` elements rather than Saya's own
state object: WooCommerce may already have removed the `<option>` you are about to select, because it
was impossible under the *previous* selection. `jQuery.val()` on a missing option fails silently and
the click appears to do nothing. §9 handles it with `ensureOption()`, which re-appends the option
before setting it. That is safe, because WC rebuilds each select from its own pristine snapshot on
the next pass and never reads the pruned DOM back.

---

## 6. Gap 4 — the mobile sticky CTA does nothing on a variable product

**Effort: 15 minutes. Mobile is your primary conversion channel.**

`template-parts/product/single.php:504` renders the sticky bar's primary button as:

```php
<a href="<?php echo esc_url( $de_product->add_to_cart_url() ); ?>" class="btn-product-primary" style="flex:2;" rel="nofollow">
```

`WC_Product_Simple` overrides `add_to_cart_url()` to produce `?add-to-cart=<id>`. `WC_Product_Variable`
does not override it, so it inherits the base implementation, which returns the plain permalink.

That gives you two distinct problems:

| Product type | What the button does | What it should do |
|---|---|---|
| Variable | links to the page it is already on: **nothing happens** | add the chosen variation |
| Simple | `?add-to-cart=<id>`, ignoring `#qty-input`: **always adds 1** | add the chosen quantity |

The second one is quietly expensive on tiles, where the calculator has just written the required m²
into the quantity field and the shopper taps the big button at the bottom of the screen.

**Confirm in 30 seconds.** Open a variable product on a phone viewport and look at the sticky
button's `href`. If it equals the page URL, this is your site.

The fix uses the HTML5 `form` attribute, so one `<button>` outside the form can submit it. This keeps
the no-JS path alive for both product types, which a JS proxy click would not.

Three edits in `template-parts/product/single.php`:

**1.** Give the form an id (line 249):

```php
<form id="product-cta-form" class="cart product-cta-form<?php echo $de_is_variable ? ' variations_form' : ''; ?>" method="post" ...>
```

**2.** Move `add-to-cart` off the main button and into a hidden input, so that *any* submit button in
the form works. Variable products already have this input at line 365; add it for simple ones, and
drop `name`/`value` from the button (line 348):

```php
<?php if ( ! $de_is_variable ) : ?>
	<input type="hidden" name="add-to-cart" value="<?php echo absint( $de_id ); ?>" />
<?php endif; ?>

<div class="product-cta-group">
	<button type="submit" class="btn-product-primary<?php echo $de_is_variable ? ' single_add_to_cart_button' : ''; ?>" id="btn-add-to-cart">
		<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
		<span class="btn-product-primary__label">Dodaj u ponudu</span>
	</button>
	...
</div>
```

The `<span>` matters for §7: the button also holds an SVG, so swapping `textContent` on the button
itself would delete the icon.

**3.** Turn the sticky link into a submit button for that form (line 504):

```php
<?php
/*
 * Submit iste forme, ne link: add_to_cart_url() za varijabilni proizvod vraca
 * permalink (WC_Product_Variable ne prepisuje baznu metodu), pa je dugme vodilo
 * na stranicu na kojoj kupac vec jeste. Uz form="" ide i izabrana kolicina, koju
 * je kod plocica upravo upisao kalkulator.
 */
?>
<button type="submit" form="product-cta-form" class="btn-product-primary" id="btn-sticky-add" style="flex:2;">
	<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
	Dodaj u ponudu
</button>
```

`syncSticky()` in §9 mirrors the main button's disabled state onto it, so the sticky bar cannot offer
an add that WooCommerce is going to refuse. Add the matching style:

```css
.product-sticky-mobile .btn-product-primary.disabled {
  opacity: 0.4;
  cursor: not-allowed;
}
```

The same `add_to_cart_url()` behaviour is on the listing card
(`template-parts/shop/product-card.php:159`), where it is *defensible*: for a variable product the
link goes to the PDP, which is where options get chosen. But the label says "Dodaj u ponudu" and it
does not add anything, so either relabel it for variable products or send it somewhere useful, which
§8.4 covers.

---

## 7. Gap 5 — the CTA promises a price that may not exist

**Effort: two lines, inside §9.**

`inc/quote-cart.php:92-102` keeps price-0 products purchasable, and the cart renders "Cijena na upit"
for them. The PDP does not join in: `#btn-add-to-cart` always reads "Dodaj u ponudu", and the price
block shows whatever `get_price_html()` returned for the parent.

Saya switches the CTA per matched variation:

```js
ctaEl.textContent = match && 0 < parseFloat( match.display_price )
	? 'Dodaj u ponudu'
	: 'Zatraži cijenu';
```

§9 does the same through `.btn-product-primary__label`, using the `display_price` that already
arrives on `show_variation` in both normal and AJAX mode. It is a small thing that prevents a
specific bad moment: a shopper who taps "Dodaj u ponudu" expecting a number, gets a line with no
price, and now distrusts the rest of the cart.

---

## 8. Smaller items

### 8.1 Keyboard focus is lost on every pill rebuild

`syncPills()` does `pills.innerHTML = ''`, which destroys the focused element. A keyboard or screen
reader user tabs to a pill, presses Enter, and focus lands on `<body>`. They then have to tab back
through the whole row every single time.

§9 fixes it by noting `document.activeElement`'s `data-value` before the rebuild and calling
`focus()` on the pill that inherits it. Four lines, and it makes the selector usable without a mouse.

### 8.2 The auto-select needs the wildcard guard

Your `autoSelectSingles()` (`product.js:259-279`) fires when a row has exactly one option left. With
an "Any" variation in the mix, "one option left" can mean "this attribute is irrelevant to the
current selection", and auto-picking then invents a constraint the shopper never expressed.

Saya guards it by requiring at least one **matching** variation to actually name a value for that
attribute:

```js
// Skip auto-select if no matching variation actually specifies this attr
// (all have empty = wildcard → attr is irrelevant for current selection)
```

Carried into §9 as the `specific` check. Also note `autoSelectSingles()` is dead above the 30
variation threshold today, for the same reason as everything else in §4: it reads `select.options`,
which nobody is pruning.

### 8.3 What to leave alone

- **`price_html` being empty when all variations cost the same.** Your comment at `product.js:387`
  documents this and the guard is correct. It looks like a bug and is not.
- **`door_expert_stock_display()` as the single source of truth** for PHP and JS. Saya has no
  equivalent and its stock text drifted between the two. Keep yours.
- **The real `<select>` in the DOM.** It is the no-JS path, the POST carrier and WC's state. The
  `.is-enhanced` trick that only visually hides it once pills exist is exactly right.
- **`wc-add-to-cart-variation` as a script dependency** rather than an enqueue plus a prayer about
  ordering. §9 depends on WC having initialised first.

### 8.4 Preselecting a variation from the listing

Free, and worth taking. `wc_dropdown_variation_attribute_options()` reads
`$_REQUEST['attribute_<taxonomy>']` when deciding what is selected, so
`/proizvod/tavola/?attribute_pa_boja=hrast` lands on the PDP with hrast already active, no JS and no
extra handler. WC applies `get_variation_default_attribute()` the same way, so admin defaults work
too.

What is missing is the link. Today `template-parts/shop/product-card.php:28` uses a bare
`get_permalink()`, so filtering the shop by colour and clicking through loses the colour, and the
grid's per-card image does not match the PDP the shopper arrives at. For tiles that reads as broken
filtering.

```php
/**
 * Link na PDP sa pretodabranim varijacijskim atributima.
 *
 * WC sam cita $_REQUEST['attribute_*'] u wc_dropdown_variation_attribute_options(),
 * pa je za pretodabir dovoljan query parametar - nema JS-a ni dodatnog handlera.
 * Bez ovoga kupac koji filtrira plocice po boji "hrast" dolazi na PDP na kojem
 * nije izabrano nista, i to izgleda kao da filter nije radio.
 *
 * @param WC_Product                 $product   Proizvod sa kartice.
 * @param array<string,string|array> $selection Aktivni filteri: `pa_boja => hrast`.
 * @return string
 */
function door_expert_product_link_with_selection( $product, $selection = array() ) {
	$url = get_permalink( $product->get_id() );

	if ( ! $product instanceof WC_Product || ! $product->is_type( 'variable' ) || empty( $selection ) ) {
		return $url;
	}

	$args = array();

	foreach ( $product->get_variation_attributes() as $taxonomy => $options ) {
		if ( empty( $selection[ $taxonomy ] ) ) {
			continue;
		}

		foreach ( (array) $selection[ $taxonomy ] as $value ) {
			if ( in_array( $value, $options, true ) ) {
				// Prvi koji ovaj proizvod stvarno ima; dvije vrijednosti istog
				// atributa ne mogu biti izabrane na PDP-u.
				$args[ 'attribute_' . sanitize_title( $taxonomy ) ] = $value;
				break;
			}
		}
	}

	return empty( $args ) ? $url : add_query_arg( $args, $url );
}
```

Feed `$selection` from whatever `inc/shop.php` already parsed out of the request, so the sidebar, the
query and the link cannot drift apart. Deliberately generic: it iterates whatever variation
attributes the product has rather than hardcoding `pa_boja`, because on Saya a card that hardcoded
one attribute silently stopped preselecting for every attribute added later.

**On SEO:** this puts `?attribute_*` on PDP links. That is safe as things stand.
`door_expert_is_filter_url()` in `inc/filters-seo.php:88-97` is scoped to `is_shop() ||
is_product_taxonomy()`, so it will not touch a product page, and WordPress's own `rel_canonical`
emits the clean permalink on singular views. Verify once with
`curl -s '<pdp>?attribute_pa_boja=hrast' | grep canonical` and move on. If you ever extend
`filters-seo.php` to products, add these keys there rather than dropping the preselection.

### 8.5 Two things you have that Saya's documents recommend building. Do not build them twice.

- [`13-UI-PDP-AND-PROJECTS.md`](13-UI-PDP-AND-PROJECTS.md) §3 proposes a "selection confirmation
  strip" spelling out the choice in words near the CTA, because on mobile the swatches scroll out of
  view. Your `.product-variants__selected` readout in each row's label does the same job and does it
  better, since it says which attribute each value belongs to. Skip the strip.
- [`11-UI-SWATCHES.md`](11-UI-SWATCHES.md) is still worth reading for tiles, and only for tiles. A
  pill reading "Bijela" is fine for a door finish and useless for a ceramic texture. You already have
  `.product-swatch` CSS at `assets/css/product.css:675-700`, unused, from the prototype. When you
  wire it, render swatches for `pa_boja` and pills for everything else: both are the same
  `data-options` loop with a different element, and both drive the same select.

### 8.6 Out of scope here, but adjacent

`#qty-input` has `max="99"` hardcoded (`single.php:340`), and the tile calculator writes
`Math.ceil( withReserve )` into it (`product.js:153`). A 120 m² job clamps to 99 with no message.
That belongs to [`05-PORT-tile-calculator.md`](05-PORT-tile-calculator.md), but it is one attribute
and you are in the file anyway.

---

## 9. The variations block of `product.js`, complete

Replaces `assets/js/product.js:164-438` in full. Everything above is in here; nothing else in the
file changes. It reuses your existing outer-scope variables (`mainImg`, `thumbs`, `qtyInput`, `calc`,
`pricePerM2`, `clampQty`, `recalc`), so it drops in where the current block sits.

Syntax-checked with `node --check`. Never executed inside Door Expert.

```js
  /* ── Varijacije: pilule nad WC selectima ────────────────── */
  /*
   * Podjela posla, nepromijenjena: skriveni WC <select>-ovi su izvor istine za
   * IZBOR i za POST forme, wc-add-to-cart-variation.js radi poklapanje varijacije,
   * cijenu i variation_id. Pilule su prikaz.
   *
   * Sta se mijenja: pilule se vise ne grade iz select.options. WC iz selecta BRISE
   * opcije koje nisu moguce uz trenutni izbor (update_variation_values, "Detach
   * unattached"), pa je pilula nestajala umjesto da posivi, red se prelamao pod
   * kursorom, a `.is-disabled` stil nikad nije bio upotrijebljen. Opcije sada
   * dolaze iz data-options, a dostupnost racunamo iz sopstvene mape varijacija.
   */
  var variationsForm = document.querySelector( '.product-page .variations_form' );
  var variationMapEl = document.getElementById( 'door-expert-variation-map' );

  if ( variationsForm && window.jQuery ) {
    window.jQuery( function ( $ ) {
      var $form = $( variationsForm );
      var priceEl = document.getElementById( 'product-price-current' );
      var priceDefault = priceEl ? priceEl.innerHTML : '';

      /*
       * Nasa mapa, ne WC-ova. WC svoju izostavlja iznad 30 varijacija i tada preskace
       * cijelo filtriranje opcija (useAjax), pa bi na kolekciji plocica sa 36 varijacija
       * svaka opcija zauvijek izgledala dostupno. Ova je uvijek tu.
       */
      var vmap = [];

      if ( variationMapEl ) {
        try {
          vmap = JSON.parse( variationMapEl.textContent || variationMapEl.innerHTML ) || [];
        } catch ( e ) {
          vmap = [];
        }
      }

      var imgDefaultSrc = mainImg ? mainImg.getAttribute( 'src' ) : '';
      var imgDefaultAlt = mainImg ? mainImg.getAttribute( 'alt' ) : '';
      var calcDefaultPrice = calc ? pricePerM2 : 0;

      var availEl = document.getElementById( 'product-availability' );
      var availTextEl = availEl ? availEl.querySelector( '.product-availability__text' ) : null;
      var availSubEl = document.getElementById( 'product-availability-sub' );
      var availNoteEl = document.getElementById( 'product-cta-note' );
      var availDefault = availEl ? availEl.getAttribute( 'data-default-status' ) : 'instock';

      var ctaEl = document.getElementById( 'btn-add-to-cart' );
      var ctaLabelEl = ctaEl ? ctaEl.querySelector( '.btn-product-primary__label' ) : null;
      var ctaLabelDefault = ctaLabelEl ? ctaLabelEl.textContent : '';
      var stickyEl = document.getElementById( 'btn-sticky-add' );

      var autoPicked = {};
      var manualClearKey = null;
      var autoBusy = false;

      function applyStock( status ) {
        var states = window.doorExpertStock;

        if ( ! availEl || ! states ) {
          return;
        }

        var state = states[ status ] || states.outofstock;

        if ( ! state ) {
          return;
        }

        availEl.classList.remove(
          'product-availability--in-stock',
          'product-availability--backorder',
          'product-availability--out-of-stock'
        );
        availEl.classList.add( 'product-availability--' + state.modifier );

        if ( availTextEl ) {
          availTextEl.textContent = state.label;
        }
        if ( availSubEl ) {
          availSubEl.textContent = state.sub;
        }
        if ( availNoteEl ) {
          availNoteEl.textContent = state.note || '';
          availNoteEl.hidden = ! state.note;
        }
      }

      function variantWraps() {
        return variationsForm.querySelectorAll( '.product-variants[data-attribute]' );
      }

      function wrapFor( key ) {
        return variationsForm.querySelector( '.product-variants[data-attribute="' + key + '"]' );
      }

      function selectFor( key ) {
        var wrap = wrapFor( key );
        return wrap ? wrap.querySelector( 'select' ) : null;
      }

      /** Trenutni izbor citamo iz selecta - oni su izvor istine, ne nase stanje. */
      function chosen() {
        var out = {};

        Array.prototype.forEach.call( variantWraps(), function ( wrap ) {
          var select = wrap.querySelector( 'select' );

          if ( select && select.value ) {
            out[ wrap.getAttribute( 'data-attribute' ) ] = select.value;
          }
        } );

        return out;
      }

      /**
       * Prva varijacija koja odgovara zadatim atributima, ili null.
       * Prazan string u varijaciji je WC-ov dzoker ("Bilo koja"), ne vrijednost -
       * naivno poklapanje ga tretira kao vrijednost i nikad ne nadje varijaciju.
       */
      function findVariation( attrs ) {
        for ( var i = 0; i < vmap.length; i++ ) {
          var v = vmap[ i ];
          var ok = true;

          for ( var key in attrs ) {
            if ( ! attrs.hasOwnProperty( key ) ) {
              continue;
            }

            var have = v.attrs[ key ];

            if ( undefined !== have && '' !== have && have !== attrs[ key ] ) {
              ok = false;
              break;
            }
          }

          if ( ok ) {
            return v;
          }
        }

        return null;
      }

      function comboPossible( attrs ) {
        return null !== findVariation( attrs );
      }

      /** Da li je `value` moguca u redu `key` uz sve OSTALE izbore. */
      function optionPossible( key, value, current ) {
        var test = {};

        for ( var k in current ) {
          if ( current.hasOwnProperty( k ) && k !== key ) {
            test[ k ] = current[ k ];
          }
        }

        test[ key ] = value;

        return comboPossible( test );
      }

      function optionsFor( wrap ) {
        var raw = wrap.getAttribute( 'data-options' );

        if ( ! raw ) {
          return null;
        }

        try {
          return JSON.parse( raw );
        } catch ( e ) {
          return null;
        }
      }

      /* Ako sablon jos nema data-options, radimo kao prije: bolje osiromaseno nego prazno. */
      function fallbackOptions( select ) {
        return Array.prototype.filter.call( select.options, function ( o ) {
          return '' !== o.value;
        } ).map( function ( o ) {
          return { value: o.value, label: o.textContent };
        } );
      }

      function hasOption( select, value ) {
        return Array.prototype.some.call( select.options, function ( o ) {
          return o.value === value;
        } );
      }

      function ensureOption( select, value, label ) {
        if ( hasOption( select, value ) ) {
          return;
        }

        /*
         * WC je ovu <option> izbrisao jer nije bila moguca uz PRETHODNI izbor.
         * Vracamo je, inace jQuery .val() tiho ne uradi nista i klik na posivjelu
         * pilulu ne radi. WC pri svom sljedecem prolazu svejedno gradi listu iz
         * sopstvenog snapshota, ne odavde, pa mu ovim ne kvarimo stanje.
         */
        var option = document.createElement( 'option' );
        option.value = value;
        option.textContent = label || value;
        select.appendChild( option );
      }

      function buildPills( wrap ) {
        var key = wrap.getAttribute( 'data-attribute' );
        var select = wrap.querySelector( 'select' );
        var pills = wrap.querySelector( '.product-variants__pills' );
        var selectedOut = wrap.querySelector( '.product-variants__selected' );

        if ( ! select || ! pills ) {
          return;
        }

        var opts = optionsFor( wrap ) || fallbackOptions( select );
        var current = chosen();
        var active = select.value;

        /* Pilule se rusi i gradi iznova, pa bi fokus tastature otisao na <body>. */
        var focused = document.activeElement;
        var refocus = focused && pills.contains( focused ) ? focused.getAttribute( 'data-value' ) : null;

        pills.innerHTML = '';

        opts.forEach( function ( opt ) {
          var pill = document.createElement( 'button' );
          pill.type = 'button';
          pill.className = 'product-variant-pill';
          pill.setAttribute( 'data-value', opt.value );
          pill.textContent = opt.label;

          var isActive = opt.value === active;
          pill.classList.toggle( 'is-active', isActive );
          pill.setAttribute( 'aria-pressed', isActive ? 'true' : 'false' );

          /*
           * Nemoguca kombinacija je siva, ali KLIKABILNA, i zato nema disabled ni
           * aria-disabled. Klik oslobadja izbor koji je blokira. Bez toga kupac koji
           * se predomislio ("ipak hrast") nema gdje da klikne i zaklucuje da hrast
           * ne postoji.
           */
          if ( ! isActive && ! optionPossible( key, opt.value, current ) ) {
            pill.classList.add( 'is-disabled' );
            pill.title = 'Nije dostupno uz trenutni izbor. Klik mijenja izbor.';
          }

          pill.addEventListener( 'click', function () {
            pick( key, opt.value === select.value ? '' : opt.value, opt.label );
          } );

          pills.appendChild( pill );

          if ( refocus && opt.value === refocus ) {
            pill.focus();
          }
        } );

        if ( selectedOut ) {
          var label = '';

          opts.forEach( function ( opt ) {
            if ( opt.value === active ) {
              label = opt.label;
            }
          } );

          selectedOut.textContent = label;
        }

        // Select sklanjamo tek kad pilule postoje - bez JS-a ostaje upotrebljiv dropdown.
        wrap.classList.add( 'is-enhanced' );
      }

      function syncPills() {
        Array.prototype.forEach.call( variantWraps(), buildPills );
      }

      function releaseAutoPicked( exceptKey ) {
        Object.keys( autoPicked ).forEach( function ( key ) {
          if ( key === exceptKey ) {
            return;
          }

          var select = selectFor( key );

          if ( select ) {
            select.value = ''; // Tiho: jedan 'change' okidamo na kraju, za sve odjednom.
          }

          delete autoPicked[ key ];
        } );
      }

      /**
       * Upisi vrijednost jednog atributa u njegov <select>.
       *
       * Razlika prema WC-u: prije upisa oslobadjamo izbore u DRUGIM redovima koji bi
       * ovaj ucinili nemogucim, umjesto da nemogucu opciju sklonimo. Tako svaki klik
       * uvijek nesto uradi, a ostaje sve sto je i dalje moguce.
       */
      function pick( key, value, label ) {
        var select = selectFor( key );

        if ( ! select ) {
          return;
        }

        delete autoPicked[ key ];
        releaseAutoPicked( key );

        // Namjerno ponistavanje ne smije odmah biti ponisteno auto-izborom.
        manualClearKey = '' === value ? key : null;

        if ( '' !== value ) {
          Array.prototype.forEach.call( variantWraps(), function ( wrap ) {
            var otherKey = wrap.getAttribute( 'data-attribute' );
            var otherSelect = wrap.querySelector( 'select' );

            if ( otherKey === key || ! otherSelect || ! otherSelect.value ) {
              return;
            }

            var test = {};
            test[ key ] = value;
            test[ otherKey ] = otherSelect.value;

            if ( ! comboPossible( test ) ) {
              otherSelect.value = '';
              delete autoPicked[ otherKey ];
            }
          } );

          ensureOption( select, value, label );
        }

        $( select ).val( value ).trigger( 'change' );
      }

      /**
       * Kad u nekom drugom redu ostane tacno jedna moguca opcija, biramo je umjesto
       * kupca. Pamtimo sta je izabrala masina (autoPicked) da bismo to pustili cim
       * kupac promijeni drugi atribut.
       */
      function autoSelectSingles() {
        Array.prototype.forEach.call( variantWraps(), function ( wrap ) {
          var key = wrap.getAttribute( 'data-attribute' );
          var select = wrap.querySelector( 'select' );

          if ( ! select || select.value || key === manualClearKey ) {
            return;
          }

          var current = chosen();
          var opts = optionsFor( wrap ) || fallbackOptions( select );
          var free = opts.filter( function ( opt ) {
            return optionPossible( key, opt.value, current );
          } );

          if ( 1 !== free.length ) {
            return;
          }

          /*
           * Ako nijedna varijacija koja odgovara trenutnom izboru ne precizira ovaj
           * atribut (sve imaju dzoker), atribut je nebitan i ne biramo ga umjesto kupca.
           */
          var specific = vmap.some( function ( v ) {
            for ( var k in current ) {
              if ( ! current.hasOwnProperty( k ) || k === key || autoPicked[ k ] ) {
                continue;
              }

              var have = v.attrs[ k ];

              if ( undefined !== have && '' !== have && have !== current[ k ] ) {
                return false;
              }
            }

            var mine = v.attrs[ key ];

            return undefined !== mine && '' !== mine;
          } );

          if ( ! specific ) {
            return;
          }

          autoPicked[ key ] = true;
          ensureOption( select, free[ 0 ].value, free[ 0 ].label );
          $( select ).val( free[ 0 ].value ).trigger( 'change' );
        } );
      }

      function setMainImage( src, srcset, alt ) {
        if ( ! mainImg || ! src ) {
          return;
        }

        mainImg.src = src;

        if ( srcset ) {
          mainImg.srcset = srcset;
        } else {
          mainImg.removeAttribute( 'srcset' );
        }

        mainImg.alt = alt || imgDefaultAlt;
      }

      /* Mobilna traka je submit iste forme, pa mora dijeliti i stanje dugmeta. */
      function syncSticky() {
        if ( ! stickyEl || ! ctaEl ) {
          return;
        }

        stickyEl.classList.toggle(
          'disabled',
          ctaEl.disabled || ctaEl.classList.contains( 'disabled' )
        );
      }

      syncPills();
      syncSticky();

      /*
       * Vezujemo se na 'change' selecta, ne na WC-ov 'woocommerce_update_variation_values':
       * taj event iznad 30 varijacija nikad ne dodje, jer WC tada preskoci
       * onUpdateAttributes. Nasa dostupnost ne zavisi od WC-ovog rezima.
       */
      $form.on( 'change', '.variations select', function () {
        syncPills();
        syncSticky();

        if ( autoBusy ) {
          return;
        }

        autoBusy = true;
        autoSelectSingles();
        autoBusy = false;
      } );

      // Klik na "Ponisti izbor": sve krece ispocetka, pa i masinski izbori.
      $form.on( 'click', '.reset_variations', function () {
        autoPicked = {};
        manualClearKey = null;
      } );

      $form.on( 'show_variation', function ( event, variation ) {
        // price_html je prazan kad su sve varijacije iste cijene - tad ostaje cijena roditelja.
        if ( priceEl && variation && variation.price_html ) {
          priceEl.innerHTML = variation.price_html;
        }

        if ( variation ) {
          applyStock( variation.door_expert_stock_status || availDefault );
        }

        /* Bez cijene dugme ne smije obecavati ponudu sa cijenom. */
        if ( ctaLabelEl && variation ) {
          ctaLabelEl.textContent = 0 < parseFloat( variation.display_price )
            ? ctaLabelDefault
            : 'Zatrazite cijenu';
        }

        /*
         * Kalkulator cita data-price iz roditelja, a to je kod varijabilnog proizvoda
         * najniza cijena iz opsega - pogresna cim formati imaju razlicit EUR/m².
         */
        if ( calc && variation && variation.display_price ) {
          pricePerM2 = parseFloat( variation.display_price ) || 0;
          recalc();
        }

        if ( variation && variation.image && variation.image.src ) {
          setMainImage( variation.image.src, variation.image.srcset, variation.image.alt );
          thumbs.forEach( function ( t ) {
            t.classList.remove( 'is-active' );
          } );
        }

        if ( qtyInput && variation ) {
          if ( variation.max_qty ) {
            qtyInput.setAttribute( 'max', variation.max_qty );
          }
          if ( variation.min_qty ) {
            qtyInput.setAttribute( 'min', variation.min_qty );
          }
          qtyInput.value = clampQty( parseInt( qtyInput.value, 10 ) );
        }

        syncPills();
        syncSticky();
      } );

      $form.on( 'hide_variation reset_data', function () {
        if ( priceEl ) {
          priceEl.innerHTML = priceDefault;
        }

        if ( ctaLabelEl ) {
          ctaLabelEl.textContent = ctaLabelDefault;
        }

        if ( calc ) {
          pricePerM2 = calcDefaultPrice;
          recalc();
        }

        applyStock( availDefault );
        setMainImage( imgDefaultSrc, '', imgDefaultAlt );

        if ( thumbs.length ) {
          thumbs.forEach( function ( t, i ) {
            t.classList.toggle( 'is-active', 0 === i );
          } );
        }

        syncPills();
        syncSticky();
      } );
    } );
  }
```

Two notes on how this interacts with WooCommerce, since both look like accidents and are not:

- **`pick()` clears other selects silently and triggers `change` only once, on the target.** WC's
  `onUpdateAttributes` reads every select through `getChosenAttributes()`, so one event is enough for
  it to see all of them. Your existing `releaseAutoPicked()` already relied on this; it is preserved.
- **Binding to `change` on the selects rather than to `woocommerce_update_variation_values`.** Our
  handler runs after WC's, because WC binds at `$(document).ready` and `wc-add-to-cart-variation` is
  declared as a dependency of `product.js`. By the time we recompute, WC has finished its own pass.

## 10. Do not do these

- **Do not replace `variations_form` with a hand-written engine.** You would inherit Saya's problem:
  matching logic in the theme, drifting from WooCommerce's, with two definitions of "available".
- **Do not add a custom AJAX add-to-cart endpoint.** §2. The form POST path handles "Any" attributes
  correctly and needs no nonce plumbing, no rate limiter and no error-notice marshalling.
- **Do not remove the real `<select>`** or hide it before the pills exist. It is the no-JS path and
  WC's state.
- **Do not hide unavailable options.** Grey them. §3.2.
- **Do not raise `woocommerce_ajax_variation_threshold` as the fix for §4** without measuring the
  HTML weight first.
- **Do not set `disabled` or `aria-disabled` on a greyed pill.** It is clickable by design.

## 11. Verify

Variation UI, on a variable door with two attributes:

1. Pick a size. Options in the colour row that have no variation with that size are **greyed and
   struck through, still present, still clickable**. Count the pills before and after: the count must
   not change.
2. Click a greyed colour. The colour becomes active and the **size clears**, rather than nothing
   happening.
3. Click a greyed colour whose size is still compatible with a *third* attribute's pick. That third
   pick must survive. Only genuinely contradicting picks clear.
4. Click the active pill again. It deselects and every option returns to normal.
5. Pick one attribute such that exactly one option remains possible in another row. It auto-selects.
6. "Poništi izbor" clears everything, including machine picks, and the price returns to the parent's
   range.
7. Tab to a pill, press Enter, check focus is still in the row and not on `<body>`.

Above the threshold, on a tile collection with more than 30 variations:

8. `view-source` shows `data-product_variations="false"` **and** a populated
   `#door-expert-variation-map`.
9. Everything in 1 to 7 behaves identically to the door. This is the whole point of §4.
10. Price, availability text, gallery image and the m² price all still update per variation, served
    by WC's AJAX round trip.

Add to cart:

11. On a variable product, submit with a full selection: a cart line for the **variation**, not the
    parent, with the chosen quantity.
12. On a variable product with an "Any <attribute>" variation, add to cart. No
    `"<Attribute> is a required field"` notice. If you see one, the select for that attribute posted
    an empty value, which means a pill did not write to it.
13. Phone viewport, variable product, full selection, tap the sticky bar button: the item lands in
    the cart. Before this work it reloaded the same page.
14. Phone viewport, tile, set quantity to 24, tap the sticky button: the line shows **24**, not 1.
15. With no selection made, the sticky button is visibly disabled alongside the main one.
16. A variation priced `0` shows "Zatražite cijenu" and **still adds to the cart**.
17. A variation with a **blank** price renders its pill and can be added. If the pill is missing,
    `door_expert_variation_visible_without_price()` is not loaded.

Without JavaScript:

18. Disable JS. The selects render as normal dropdowns, choosing values and submitting adds the right
    variation. The sticky button still submits, because it is a real submit button.

## 12. File map

| File | Change |
|---|---|
| `inc/product-variations.php` | add `door_expert_variation_map()`, `door_expert_variation_option_list()`, `door_expert_variation_visible_without_price()`, `door_expert_product_link_with_selection()`. Existing orderability and availability code **unchanged** |
| `template-parts/product/single.php` | `data-options` on `.product-variants`; `#door-expert-variation-map` script; `id="product-cta-form"`; hidden `add-to-cart` input for simple products; `.btn-product-primary__label` span; sticky link becomes a submit button |
| `assets/js/product.js` | replace the variations block (lines 164 to 438) with §9 |
| `assets/css/product.css` | `.product-variant-pill.is-disabled` cursor and hover; `.product-sticky-mobile .btn-product-primary.disabled` |
| `template-parts/shop/product-card.php` | optional, §8.4: use `door_expert_product_link_with_selection()` for the card links |
| `inc/product.php` | **unchanged** |
| `inc/quote-cart.php` | **unchanged** |
| WooCommerce → Settings → Products → Inventory | confirm "Hide out of stock items from the catalog" is **off**, §4.4 |

## 13. Standing caveats

- Written against the Door Expert theme as of 2026-09-26. Line numbers drift; re-check with `grep -n`
  before trusting one.
- The PHP passes `php -l` and the JS passes `node --check`. **Neither has run inside Door Expert.**
  Reviewed drafts, not tested code, which is what §11 is for.
- Three statements about WooCommerce's internals drive the whole document: that unavailable options
  are removed rather than disabled (§3.1), that the AJAX threshold disables option filtering
  entirely (§4.1), and that `add_to_cart_url()` on a variable product is just the permalink (§6).
  Each comes with a check that takes under a minute. Run all three before doing the work, because if
  any is false on your WooCommerce version the corresponding section is unnecessary.



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



<!-- ===== FILE: 08-PARITY-faceting-seo-ajax.md ===== -->

# Filters — parity checklist, not a port

`07-PLUGIN-filter-configurator.md` handed over the sidebar. This document closes the three gaps
that are left between Saya's filtering and Door Expert's, now that the filters are actually wired up
in your repo.

**This document is different from `02` through `07` in one important way.** Those were written
blind: the Door Expert repo was not available, so every snippet was a reviewed draft against
assumptions. This one was written against your real files. Every function name, hook, DOM class and
URL parameter below was read out of
`wp-content/themes/door-expert/` as it stands.

That changes the shape of the advice. This is not "here is Saya's code, adapt it." Two of the three
gaps are closed with code that calls functions **you already have**, and one of them is a design
decision you should make before anyone writes a line.

---

## 1. Where you actually are

You did more than `07` asked for. The plugin is installed, `inc/filters.php` is a proper bridge, and
`inc/shop.php` translates the URL into the main WooCommerce query. Scoping works: the sidebar on a
door category no longer offers ceramic brands.

Three things are missing. One of them is a real bug.

| Behaviour | Saya | Door Expert | Verdict |
|---|---|---|---|
| URL to query | `pre_get_posts` | `woocommerce_product_query_tax_query` | **Yours is better. Do not change it.** |
| Term scoping + counts on page load | ✅ | ✅ | parity |
| Live faceting (recount + grey out zeros) | ✅ | ❌ **not wired** | §2 — fix first |
| `noindex` + canonical on filter URLs | ✅ | ❌ absent | §3 |
| AJAX grid swap + URL sync | ✅ | ❌ absent | §4 — decide before building |

### 1.1 On the query hook, specifically

The audit and `07` both told you not to replace your query engine. That still holds, and now that
the code is readable the reason is stronger than "you already have one."

`woocommerce_product_query_tax_query` only fires on the WooCommerce archive query, and it hands you
a `tax_query` that WooCommerce then merges with `product_visibility` itself. Saya's `pre_get_posts`
fires on **every** query on the site — menus, widgets, related products, admin list tables — and
therefore needs `is_admin()`, `is_main_query()` and `is_shop() || is_product_category()` guards that
you simply do not need, plus a manual `array_merge` onto an existing `tax_query` that can clobber
WooCommerce's own visibility clause if you get it wrong.

Saya's version is correct because its guards are correct, not because the approach is better. If
anyone proposes moving Door Expert onto `pre_get_posts` "to match Saya", that is a downgrade.
Refuse it.

### 1.2 The one place Saya's code is worse, and you should not copy it

Saya's AJAX filter handler rebuilds the whole query from `$_POST` in a second, separate code path
from `pre_get_posts`. Two query builders, one source of truth between them, free to drift. §4 tells
you how to avoid inheriting that mistake.

---

## 2. Gap 1 — live faceting

**Effort: a few hours. Value: highest of the three. Do this first.**

### 2.1 What is wrong today

`inc/filters.php:41` is the only place the theme touches the plugin, and it only checks for
`wcfc_render_sidebar`. `wcfc_compute_facets()` is never called.

The consequence is not cosmetic. Counts rendered by `wcfc_render_sidebar()` are correct for the
category, but they are **static**: they describe the unfiltered category and never change as the
shopper narrows down. So the shopper ticks *Hrast*, sees *Bijela (7)* still sitting there, ticks it,
and gets an empty grid. `DOCS/BITNE FUNKCIONALNOSTI/FILTERI_ATRIBUTI.md` in the Saya repo opens with
exactly this as the thing the design exists to prevent.

This is worth fixing **even though you have no AJAX**. Your form does a full page reload on
*Primijeni filtere*, and the counts are recomputed server-side on that reload. Faceting and AJAX are
independent.

### 2.2 The approach

You are already rewriting the plugin's markup through the `wcfc_sidebar_html` filter, in
`door_expert_filter_sidebar_markup()`. The facet counts belong in that same pass: compute the facets
once per request, then let the existing checkbox and swatch callbacks consult them.

Three additions to `inc/filters.php`, all self-contained.

### 2.3 Compute the facets once

```php
/**
 * Kategorija u čijem kontekstu se računaju faceti.
 *
 * Na kategorijskoj arhivi to je tekući term. Na prodavnici faceting ima smisla samo
 * kad je hero pilulom izabrana tačno jedna kategorija; za nula ili više njih nema
 * jednog opsega nad kojim bi se brojalo, pa se faceting preskače i ostaju brojevi
 * koje je plugin izrenderovao.
 *
 * @return WP_Term|null
 */
function door_expert_filter_facet_term() {
	if ( is_tax( 'product_cat' ) ) {
		$term = get_queried_object();

		return $term instanceof WP_Term ? $term : null;
	}

	$cats = door_expert_shop_selected( 'f_cat' );
	if ( 1 !== count( $cats ) ) {
		return null;
	}

	$term = get_term_by( 'slug', $cats[0], 'product_cat' );

	return $term instanceof WP_Term ? $term : null;
}

/**
 * Facet brojevi za tekući izbor: [ taksonomija => [ slug => broj ] ].
 *
 * Self-exclusion je u pluginu: svaki facet se broji nad proizvodima koji zadovoljavaju
 * sve DRUGE aktivne filtere, ali ne i sebe. Bez toga bi prvi štiklirani dekor ponulio
 * sve ostale dekore u istoj grupi i multi-select bi prestao da radi.
 *
 * Prazan niz znači "nema facetinga" – pozivalac tada zadržava plugin-ov broj.
 *
 * @return array<string,array<string,int>>
 */
function door_expert_filter_facets() {
	static $facets = null;

	if ( null !== $facets ) {
		return $facets;
	}

	$facets = array();

	if ( ! function_exists( 'wcfc_compute_facets' ) || ! function_exists( 'wcfc_attrs_for_context' ) ) {
		return $facets;
	}

	$term = door_expert_filter_facet_term();
	if ( ! $term instanceof WP_Term ) {
		return $facets;
	}

	$attrs = wcfc_attrs_for_context( (string) $term->term_id );
	if ( empty( $attrs ) ) {
		return $facets;
	}

	$selected = array();
	foreach ( door_expert_filter_taxonomies() as $taxonomy ) {
		$terms = door_expert_shop_selected( $taxonomy );
		if ( ! empty( $terms ) ) {
			$selected[ $taxonomy ] = $terms;
		}
	}

	$min = door_expert_shop_price( 'min_price' );
	$max = door_expert_shop_price( 'max_price' );

	// door_expert_shop_price() vraća 0.0 kad granica nije postavljena, a plugin
	// razlikuje "nije postavljeno" (null) od "postavljeno na nulu".
	$facets = wcfc_compute_facets(
		(int) $term->term_id,
		$selected,
		$min > 0 ? $min : null,
		$max > 0 ? $max : null,
		$attrs
	);

	return $facets;
}
```

### 2.4 Apply them to the checkboxes

Your current `door_expert_filter_fix_checkboxes()` rewrites the `<input>` alone. The count lives in
a sibling `<small class="wcfc-count">`, outside that tag, so the rewrite has to move up to the whole
`<label>`.

That is safe: the plugin emits options from a single fixed `sprintf` (`includes/render.php:360`), so
the label markup is as predictable as the input markup you are already matching. Keep your existing
input-level pass as a safety net underneath it, so that if the plugin ever changes its label markup
you lose the facet counts but **not** the `[]` and `checked` fixes that multi-select depends on.

```php
/**
 * <label class="wcfc-option"> => multi-select name, checked stanje, facet broj.
 *
 * Nula rezultata se SIVI, ne sakriva: lista koja se prekraja pod kursorom je gora od
 * onemogućene opcije. Već štiklirana opcija se nikad ne onemogući, inače je korisnik
 * ne bi mogao odštiklirati.
 *
 * @param string $html Markup.
 * @return string
 */
function door_expert_filter_fix_checkboxes( $html ) {
	$result = preg_replace_callback(
		'/<label class="wcfc-option"><input type="checkbox" name="([a-z0-9_\-]+)" value="([^"]*)"\s*\/?><span>(.*?)<\/span><small class="wcfc-count">\((\d+)\)<\/small><\/label>/i',
		'door_expert_filter_option_tag',
		$html
	);

	if ( null === $result ) {
		$result = $html;
	}

	// Sigurnosna mreža: ako plugin promijeni markup labele, gornji regex ne uhvati
	// ništa, pa ovaj pass i dalje obezbijedi name="...[]" i checked.
	$fallback = preg_replace_callback(
		'/<input type="checkbox" name="([a-z0-9_\-]+)" value="([^"]*)"\s*\/?>/i',
		'door_expert_filter_checkbox_tag',
		$result
	);

	return null === $fallback ? $result : $fallback;
}

/**
 * @param array $matches [1] taksonomija, [2] slug, [3] labela, [4] plugin-ov broj.
 * @return string
 */
function door_expert_filter_option_tag( $matches ) {
	$taxonomy = $matches[1];
	$slug     = wp_specialchars_decode( $matches[2], ENT_QUOTES );
	$label    = $matches[3]; // Plugin ga je već escape-ovao.
	$count    = (int) $matches[4];

	$checked = in_array( $slug, door_expert_shop_selected( $taxonomy ), true );

	$facets = door_expert_filter_facets();
	if ( isset( $facets[ $taxonomy ] ) ) {
		$count = isset( $facets[ $taxonomy ][ $slug ] ) ? (int) $facets[ $taxonomy ][ $slug ] : 0;
	}

	$dead = ( 0 === $count && ! $checked );

	return sprintf(
		'<label class="wcfc-option%1$s"><input type="checkbox" name="%2$s[]" value="%3$s"%4$s%5$s /><span>%6$s</span><small class="wcfc-count">(%7$d)</small></label>',
		$dead ? ' is-disabled' : '',
		esc_attr( $taxonomy ),
		esc_attr( $slug ),
		$checked ? ' checked="checked"' : '',
		$dead ? ' disabled="disabled"' : '',
		$label,
		$count
	);
}
```

`door_expert_filter_checkbox_tag()` stays exactly as it is. It now only runs on anything the label
pass missed, which should be nothing.

### 2.5 Apply them to the swatches

`door_expert_filter_swatch_tag()` already reads `data-count` off the plugin's `<button>`. Two edits:
override that count from the facets, and carry a disabled state through.

```php
	$count = (int) door_expert_filter_tag_attr( $tag, 'data-count' );

	$facets = door_expert_filter_facets();
	if ( isset( $facets[ $taxonomy ] ) ) {
		$count = isset( $facets[ $taxonomy ][ $slug ] ) ? (int) $facets[ $taxonomy ][ $slug ] : 0;
	}

	$style   = door_expert_filter_tag_attr( $tag, 'style' );
	$title   = door_expert_filter_tag_attr( $tag, 'title' );
	$checked = in_array( $slug, door_expert_shop_selected( $taxonomy ), true );
	$dead    = ( 0 === $count && ! $checked );

	return sprintf(
		'<label class="%1$s%2$s%3$s" style="%4$s" title="%5$s" data-count="%6$d">' .
			'<input type="checkbox" name="%7$s[]" value="%8$s"%9$s%10$s />' .
			'<span class="wcfc-sr">%11$s</span>' .
		'</label>',
		esc_attr( $classes ),
		$checked ? ' is-active' : '',
		$dead ? ' is-disabled' : '',
		esc_attr( $style ),
		esc_attr( $title ),
		$count,
		esc_attr( $taxonomy ),
		esc_attr( $slug ),
		$checked ? ' checked="checked"' : '',
		$dead ? ' disabled="disabled"' : '',
		esc_html( $title )
	);
```

### 2.6 CSS

`.wcfc-option.is-disabled` and `.wcfc-swatch.is-disabled` need styling or the whole feature is
invisible. Roughly: `opacity: .4; cursor: not-allowed;` and, for the swatch, no hover lift. The
count stays readable — `(0)` is information, not noise.

### 2.7 What this does not cover — read before you test

Four honest limits, so nobody files these as bugs:

1. **The category group gets no facets.** `wcfc_attrs_for_context()` deliberately skips
   `product_cat` and `price_slider` (`includes/config.php:368`). The category tree keeps its own
   counts.
2. **Availability is not a taxonomy.** `f_stock` narrows the grid through `_stock_status` meta, but
   the plugin knows nothing about it, so facet counts ignore it. With a stock filter active the
   counts can legitimately exceed the number of products shown.
3. **The shop archive with zero or several `f_cat` pills gets no faceting**, per §2.3. Counts fall
   back to the plugin's.
4. **Counts are catalogue-scoped, not query-scoped.** The plugin counts over
   `wcfc_get_cat_product_ids()`, which excludes products hidden from the catalogue and, when
   WooCommerce is set to hide them, out-of-stock products. That matches your grid because your grid
   runs through the main WooCommerce query. If you ever add a filter outside the plugin's knowledge,
   the two drift apart again.

---

## 3. Gap 2 — SEO on filtered URLs

**Effort: under a day. Zero conflict with anything you have. Highest value per hour in this
document.**

### 3.1 Why this matters more than it sounds

There is no `noindex` and no `canonical` anywhere in `wp-content` or `wp-plugins` today. Every
combination of every filter is a crawlable, indexable URL that returns near-identical content:

```
/prodavnica/?pa_boja[]=bijela
/prodavnica/?pa_boja[]=bijela&product_brand[]=x
/prodavnica/?pa_boja[]=bijela&product_brand[]=x&orderby=price
...
```

With a handful of attributes this is thousands of URLs competing with the category page they are
derived from. The audit called this the highest-value SEO item in the Saya repo and it was right;
what it got wrong is the directive, see §3.4.

### 3.2 The file

New file, `wp-content/themes/door-expert/inc/filters-seo.php`, required from `functions.php`
**after** `inc/shop.php` and `inc/filters.php` — it calls functions from both.

```php
<?php
/**
 * SEO za filtrirane arhive – meta robots i canonical.
 *
 * Filtrirani URL (?pa_boja[]=..., ?product_brand[]=..., ?orderby=...) je noindex,nofollow
 * i kanonikalizuje se na čistu kategoriju/prodavnicu. Paginirane strane ostaju
 * index,follow sa self-canonical-om: one nisu duplikati nego nastavak liste.
 *
 * Radi sa SEO pluginom i bez njega. Bez plugina WordPress na arhivama ne ispisuje
 * canonical uopšte (rel_canonical() pokriva samo singular), pa ga ispisujemo sami.
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Da li je neki SEO plugin već ispisao canonical u ovom zahtjevu.
 *
 * Namjerno se NE oslanja samo na konstantu plugina: naziv konstante je nešto što
 * plugin može da promijeni, a ako pogriješimo dobijemo dva rel=canonical taga.
 * Dva canonical-a sa različitim vrijednostima Google ignoriše oba, pa bi tiha
 * greška u detekciji ubila upravo ono zbog čega ovaj fajl postoji.
 *
 * Pouzdan signal je da je canonical filter plugina stvarno otišao kroz naš
 * callback (vidi door_expert_filter_rank_math_canonical). Zato fallback ide na
 * kasnom wp_head prioritetu, kad je taj filter već odradio svoje.
 *
 * @return bool
 */
function door_expert_seo_canonical_handled() {
	if ( ! empty( $GLOBALS['door_expert_seo_canonical_done'] ) ) {
		return true;
	}

	return defined( 'RANK_MATH_VERSION' ) || class_exists( 'RankMath', false ) || defined( 'WPSEO_VERSION' );
}

/**
 * Query ključevi koji od arhive prave "filter" URL.
 *
 * Taksonomijski dio je dinamičan iz istog izvora kao upit (door_expert_filter_taxonomies),
 * da se ne desi da neko doda atribut u konfigurator, on počne da filtrira, a SEO sloj
 * za njega ne zna i pusti ga u indeks.
 *
 * @return string[]
 */
function door_expert_filter_noindex_keys() {
	$keys = array( 'f_cat', 'f_stock', 'min_price', 'max_price', 'orderby' );

	if ( function_exists( 'door_expert_filter_taxonomies' ) ) {
		$keys = array_merge( $keys, door_expert_filter_taxonomies() );
	}

	return array_values( array_unique( $keys ) );
}

/**
 * Da li tekući URL nosi filter parametre.
 *
 * @return bool
 */
function door_expert_is_filter_url() {
	if ( is_search() ) {
		return true;
	}

	if ( ! ( is_shop() || is_product_taxonomy() ) ) {
		return false;
	}

	$keys = door_expert_filter_noindex_keys();

	foreach ( array_keys( $_GET ) as $raw_key ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only.
		$key = sanitize_key( $raw_key );

		if ( 0 === strpos( $key, 'pa_' ) || in_array( $key, $keys, true ) ) {
			return true;
		}
	}

	return false;
}

add_action( 'wp_head', 'door_expert_filter_robots_tag', 1 );
/**
 * Meta robots za filtrirane arhive i pretragu.
 *
 * Ide na prioritetu 1 da bude iznad ostalog head-a. Ako je SEO plugin aktivan ispisaće
 * i on svoj tag; Google poštuje najrestriktivniju direktivu, pa je dupliranje bezopasno.
 */
function door_expert_filter_robots_tag() {
	if ( door_expert_is_filter_url() ) {
		echo '<meta name="robots" content="noindex, nofollow">' . "\n";
	}
}

add_filter( 'rank_math/frontend/robots', 'door_expert_filter_rank_math_robots', 99 );
/**
 * @param array $robots Rank Math robots niz.
 * @return array
 */
function door_expert_filter_rank_math_robots( $robots ) {
	if ( door_expert_is_filter_url() ) {
		$robots['index']  = 'noindex';
		$robots['follow'] = 'nofollow';

		return $robots;
	}

	// Paginacija nije duplikat: strana 2 ima druge proizvode od strane 1.
	if ( (int) get_query_var( 'paged' ) > 1 ) {
		$robots['index']  = 'index';
		$robots['follow'] = 'follow';
	}

	return $robots;
}

/**
 * Kanonski URL tekuće arhive.
 *
 * Filter URL => čist listing (door_expert_listing_base_url već zna da razlikuje
 * kategorijsku arhivu od prodavnice). Paginacija => sama sebi.
 *
 * @return string Prazan string kad nema šta da se kanonikalizuje.
 */
function door_expert_filter_canonical_url() {
	if ( ! ( is_shop() || is_product_taxonomy() ) ) {
		return '';
	}

	$paged = (int) get_query_var( 'paged' );
	if ( $paged >= 2 ) {
		return (string) get_pagenum_link( $paged );
	}

	if ( door_expert_is_filter_url() && function_exists( 'door_expert_listing_base_url' ) ) {
		return door_expert_listing_base_url();
	}

	return '';
}

add_filter( 'rank_math/frontend/canonical', 'door_expert_filter_rank_math_canonical' );
/**
 * @param string $canonical Rank Math canonical.
 * @return string
 */
function door_expert_filter_rank_math_canonical( $canonical ) {
	// Ovaj filter se okine samo ako Rank Math stvarno obrađuje head, pa je to
	// pouzdaniji signal od bilo koje konstante.
	$GLOBALS['door_expert_seo_canonical_done'] = true;

	$url = door_expert_filter_canonical_url();

	return '' !== $url ? $url : $canonical;
}

add_action( 'wp_head', 'door_expert_filter_canonical_tag', 999 );
/**
 * Canonical kad nema SEO plugina. WordPress ga na arhivama ne ispisuje sam
 * (rel_canonical() pokriva samo singular).
 *
 * Kasan prioritet je namjeran: do 999 je svaki SEO plugin već ispisao svoj
 * canonical i okinuo svoj filter, pa detekcija ne mora da pogađa.
 */
function door_expert_filter_canonical_tag() {
	if ( door_expert_seo_canonical_handled() ) {
		return;
	}

	$url = door_expert_filter_canonical_url();

	// Na čistoj arhivi bez filtera canonical je sam taj URL.
	if ( '' === $url && ( is_shop() || is_product_taxonomy() ) ) {
		$url = door_expert_listing_base_url();
	}

	if ( '' === $url ) {
		return;
	}

	printf( '<link rel="canonical" href="%s" />' . "\n", esc_url( $url ) );
}
```

### 3.3 Why `?orderby=` is noindex too

It is tempting to leave sorting indexable because it does not change the result *set*. Leave it and
you get `?orderby=price`, `?orderby=price-desc`, `?orderby=date` as three more copies of every
category, each with the same products in a different order. Exact duplicates are worse than
near-duplicates. It goes in the list.

### 3.4 Correction to the audit

`01-AUDIT-REPORT.md` described Saya's handling as `noindex,follow` in two places. That was wrong:
Saya live emits `noindex, nofollow` (`wp-theme/functions.php:1052`), and so does the portable kit.
The line references it gave (`functions.php:1052-1170`) had also drifted; the block now starts
around `:1026`.

Both have been corrected in place in `01`, so this is recorded rather than outstanding. It is called
out here because anyone who read that document before the correction, or who works from a copy of
it, would ship the opposite of the intended directive — `follow` on a page you have just told Google
not to index keeps the crawler walking the whole facet graph, which is most of what the noindex was
meant to stop.

### 3.5 The robots.txt trap — order matters

Saya also blocks faceted parameters in `robots.txt` for crawl budget. **Do not do that yet.**

`robots.txt` stops the crawl. A URL Google cannot crawl is a URL whose `noindex` Google can never
read, so anything already indexed stays indexed, indefinitely. The sequence is:

1. Ship the `noindex` from §3.2.
2. Leave the URLs crawlable until Search Console shows them dropping out of the index.
3. Only then add `Disallow` rules for the filter parameters, to stop the crawl budget being spent
   re-checking them.

Since Door Expert has nothing indexed under these parameters yet, you can also just do step 1 and
stop — the crawl-budget problem is one you do not have. `DOCS/SEO_ROBOTS_NOINDEX.md` in the Saya
repo has the full three-layer picture if you want it later.

### 3.6 When Rank Math goes in

Rank Math is planned for this site but not installed yet. §3.2 is written for both states and needs
no edit when it arrives: the `rank_math/*` filters are inert until the plugin exists, and the
`wp_head` fallback stands down on its own once Rank Math's canonical filter has run.

Four things to check on the day it is activated.

**Do not let it noindex your clean category pages.** This is the one that can actually hurt. Rank
Math ships a global robots default at *Titles & Meta → Products → Product Categories → Robots Meta*.
Our filter only forces `index, follow` on paginated pages; on page 1 of an unfiltered category it
deliberately passes Rank Math's value straight through, because overriding it would take the
per-category robots control away from whoever is doing SEO. So if that global is set to `noindex`,
every clean category goes `noindex` and nobody will see it in the markup review, because the filter
URLs look correct. Set it to `index, follow` and control exceptions per category.

**Pick one robots.txt.** Rank Math has an editor at *Settings → General → Edit robots.txt*, which
writes a virtual file that wins over a physical one. Two sources of truth is how Saya ended up with
a `robots.txt` in the repo that did not match the live site. Decide which one is authoritative and
write it down. Either is fine; both is not.

**Confirm there is exactly one canonical.** The whole point of §3.2's late-priority fallback is that
it retires itself. Verify rather than assume:

```bash
curl -s "https://<host>/prodavnica/?pa_boja%5B%5D=bijela" | grep -c 'rel="canonical"'
# mora biti 1
```

If that returns `2`, the detection in `door_expert_seo_canonical_handled()` did not fire and both
tags printed. Re-run the robots and canonical checks from §6 after activation — Rank Math changes
who writes those tags, so the earlier results do not carry over.

**Staging.** If a staging copy exists, make sure it is `noindex` at the server or Rank Math level
before the plugin starts generating sitemaps for it.

---

## 4. Gap 3 — AJAX filtering

**Effort: real. This is the only item here that is a project rather than a fix.**

### 4.1 Decide the UX first

Your filter sidebar is a GET form with an explicit *Primijeni filtere* button
(`template-parts/shop/filters.php:73`). The shopper ticks several boxes, then applies. Saya fires a
request on every single change and has no apply button at all.

Those are different products, not different implementations. Going AJAX means deleting that button
and committing to instant feedback. It is the better experience for a catalogue this size, but it is
a decision for the owner, not a refactor to do quietly on the way past. Settle it before writing
code.

If you keep the apply button, **§2 and §3 still give you full parity on everything that matters for
correctness and SEO.** AJAX is polish.

### 4.2 Do not copy Saya's handler

Saya's `kit_filter_products()` (`portable-kit/theme/inc/filters.php:56-189`) builds its own
`tax_query`, its own `meta_query` and its own orderby mapping from `$_POST`, entirely separate from
the `pre_get_posts` path that serves the same filters on a cold load. Two builders, and nothing
keeps them in step.

You can avoid that outright, because your query logic is already in two reusable functions that read
from `$_GET`. Have the JS post the query string, hydrate `$_GET` from it, and call them:

```php
	// JS šalje isti query string koji bi inače otišao u URL.
	$query_string = isset( $_POST['query'] ) ? wp_unslash( $_POST['query'] ) : '';
	parse_str( (string) $query_string, $parsed );

	$original_get = $_GET;
	$_GET         = is_array( $parsed ) ? $parsed : array();

	// WC_Query nam daje bazu koja već sadrži product_visibility; naše funkcije
	// dodaju filtere na nju, tačno kao na normalnoj arhivi.
	$tax_query  = door_expert_shop_tax_query( WC_Query::get_tax_query(), null );
	$meta_query = door_expert_shop_meta_query( WC_Query::get_meta_query() );

	$_GET = $original_get;
```

One source of truth for what a filter means. The handler then runs `WP_Query`, renders
`template-parts/shop/product-card.php` per result into an output buffer, and returns
`html`, `found`, `post_count`, `max_pages` plus `facets` from `door_expert_filter_facets()`.

Requirements that are easy to forget: `check_ajax_referer()`, a per-IP transient rate limit
(`02-PORT-quote-cart.md` already gave you `door_expert_rate_limit()`), and the category context,
which on a category page comes from the main query but in an AJAX request has to be posted
explicitly.

### 4.3 Return rendered HTML, not JSON rows

The handler returns `product-card.php` output, not product data for the JS to template. One card
template, server-side, used by both paths. The moment the client templates a card you have two card
implementations that will diverge, and the AJAX one will quietly lose schema markup, lazy-loading
attributes and `srcset`.

### 4.4 The DOM contract is not Saya's — this will bite you

`portable-kit/theme/js/filters.js` is the reference for *behaviour*. Its selectors are wrong for your
markup in three ways, and each one fails **silently**:

| Kit selector | Why it matches nothing here |
|---|---|
| `input[type="checkbox"][name="pa_boja"]` | `inc/filters.php` rewrites every name to `pa_boja[]` |
| `.kit-color-swatch[data-attr][data-value]` | your swatches became `<label class="wcfc-swatch">` wrapping a checkbox; `data-attr` and `data-value` are not carried over |
| `#kit-products-grid`, `#kit-pagination` | you have `.shop-grid` and `.shop-pagination`, with no ids |

The swatch conversion is a gift, though: because both a plain option and a swatch now contain a real
checkbox named after its taxonomy, **one selector covers both**:

```js
document.querySelectorAll( '.shop-filters input[type="checkbox"][name="' + tax + '[]"]' )
```

Facet updates, active-filter pills and the URL sync can all key off that. Write the counts into the
sibling `small.wcfc-count`, and toggle `is-disabled` on the closest `label` plus `disabled` on the
input — the same two states §2 already renders server-side, so the CSS is written once.

Also worth taking from the kit: `history.replaceState` rather than `pushState` (filter twiddling
should not fill the back button with dead ends), a debounce around checkbox changes, and a longer
one on the price slider.

### 4.5 Keep the no-JS path alive

Whatever you build, the form must still submit as GET and the server must still render the filtered
grid. That is what makes the filter URLs shareable, and it is the reason §3 has something to
canonicalise in the first place. Test it with JS disabled before calling the work done.

---

## 5. Do not do these

- **Do not move the query onto `pre_get_posts`.** §1.1.
- **Do not rename URL parameters to match Saya** (`f_brand`, `f_boja`). Yours are named after their
  taxonomies, which is why you needed no translator between the plugin's markup and the query.
  `inc/shop.php:22-25` explains the decision; it is a good one. Just keep
  `door_expert_filter_noindex_keys()` fed from the same source so SEO cannot fall behind.
- **Do not hide zero-result options.** Grey them. A list that reflows under the cursor is worse than
  a disabled row.
- **Do not add `Disallow` for filter parameters before the `noindex` has been crawled.** §3.5.

---

## 6. Verify

Faceting, on a category with at least two attributes:

1. Tick one term. Other groups' counts change; nothing in the ticked group goes to `(0)` because of
   your own choice (that is self-exclusion working).
2. Tick a second term in the *same* group. The grid widens, not narrows (OR within an attribute).
3. Find a `(0)` option. It is greyed, still visible, and cannot be ticked.
4. Untick everything. Counts return to the values the plugin rendered on a cold load.
5. Edit a product's attribute in admin, reload the archive. Counts reflect it — the cache version
   bumped.

SEO, with `curl`:

```bash
curl -s "https://<host>/prodavnica/?pa_boja%5B%5D=bijela" | grep -i -E 'name="robots"|rel="canonical"'
# noindex, nofollow  +  canonical na /prodavnica/

curl -s "https://<host>/prodavnica/" | grep -i -E 'name="robots"|rel="canonical"'
# nema noindex  +  canonical na samu sebe

curl -s "https://<host>/prodavnica/page/2/" | grep -i -E 'name="robots"|rel="canonical"'
# index,follow  +  canonical na /prodavnica/page/2/
```

One thing to check that is specific to your parameter naming: `product_brand` is a registered
WooCommerce taxonomy, so it may also be a **public query var**. Saya deliberately uses `f_brand` in
the URL to dodge that collision (`portable-kit/theme/inc/filters.php:219`). Load
`/prodavnica/?product_brand[]=<slug>` and confirm you get the filtered shop archive and not a brand
archive, a 404 or a double-applied filter. If it misbehaves, the fix is a URL alias in
`door_expert_shop_filter_taxonomies()`, not a rename of the sidebar inputs.

---

## 7. File map

| File | Change |
|---|---|
| `wp-content/themes/door-expert/inc/filters.php` | add `door_expert_filter_facet_term()`, `door_expert_filter_facets()`, `door_expert_filter_option_tag()`; replace `door_expert_filter_fix_checkboxes()`; edit `door_expert_filter_swatch_tag()` |
| `wp-content/themes/door-expert/inc/filters-seo.php` | **new**, §3.2 |
| `wp-content/themes/door-expert/functions.php` | require `inc/filters-seo.php` after `inc/shop.php` and `inc/filters.php` |
| theme CSS | `.wcfc-option.is-disabled`, `.wcfc-swatch.is-disabled` |
| `inc/shop.php` | **unchanged** |

### Reference, in the Saya repo

| What | Where |
|---|---|
| Faceting reasoning, in Serbian | `DOCS/BITNE FUNKCIONALNOSTI/FILTERI_ATRIBUTI.md` |
| Robots / noindex / canonical, three layers, in Serbian | `DOCS/SEO_ROBOTS_NOINDEX.md` |
| Reference AJAX behaviour (not its selectors) | `portable-kit/theme/js/filters.js` |
| Reference SEO implementation | `portable-kit/theme/inc/filters-seo.php` |
| Plugin API: `wcfc_compute_facets`, `wcfc_attrs_for_context` | `portable-kit/plugin/wc-filter-configurator/README.md` |
