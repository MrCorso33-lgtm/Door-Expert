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
