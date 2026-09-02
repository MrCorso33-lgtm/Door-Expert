# Prompt: Reusable-code audit (other ceramica-salon WP site → Door Expert)

> Copy everything below the line and send it to a Claude agent that is running **inside the other
> custom WordPress site** (the other ceramic-tile / bathroom salon you built). It will NOT have
> access to the Door Expert repo — all Door Expert context it needs is embedded in the prompt.
>
> Goal: get a precise, honest inventory of code/logic on that site that we can port into Door Expert,
> with a portability assessment against our conventions — not a code dump.

---

## Role

You are a senior WordPress/WooCommerce engineer. You are working **inside an existing custom WordPress
site** (a ceramic-tile / bathroom-fixtures salon) that I built earlier. I am now building a **second,
separate** custom WordPress site — "Door Expert" (doors, Spanish ceramic tiles, decorative basins;
Podgorica, Montenegro) — and I want to reuse proven code and logic from THIS site instead of rewriting it.

Your job is **read-only analysis + extraction advice**. Do **not** modify this site. Produce a
structured report (details under "Deliverable").

## What I want to reuse (from your codebase on this site)

Primary targets — find these if they exist here, and anything adjacent:

1. **Image gallery + lightbox / zoom** (product gallery, thumbnails, fullscreen zoom).
2. **Product variations logic** — variable products: attribute/variation selectors (pills/swatches),
   live price/stock/SKU/image switching, "add selected variation to cart".
3. **Cart / quote flow** — especially anything that turns WooCommerce into a **quote/inquiry cart**
   (no online payment), AJAX add-to-cart, mini-cart / cart drawer, cart badge count, "request a quote".
4. **Tile m² calculator** — room-area → boxes/m² needed (+ waste %), price estimate, feeding qty into cart.
5. Anything else salon-relevant: faceted filters, quantity steppers, wishlist/"save for project",
   inquiry/contact forms, sticky mobile action bar, breadcrumb, related/cross-sell logic, structured data.

## Target project context (Door Expert) — judge fit against THIS

**Stack & allowed plugins (hard constraints):**
- Custom WordPress theme, **no page builder** (no Elementor), **no Contact Form 7**.
- **WooCommerce** — products + `product_cat`. Cart is a **quote model**: no online payment; ~70% of
  conversions happen by phone. CTA is "Dodaj u ponudu" (add to inquiry); a formal offer is emailed.
- **JetEngine** — allowed as a **DATA LAYER ONLY** (meta boxes, `get_term_meta()`/`get_post_meta()`).
  **No** Jet frontend widgets/listings/**JetSmartFilters**. Presentation is always custom PHP.
- **Rank Math** — SEO only.
- So: anything depending on a page builder, CF7, JetSmartFilters, or a Jet frontend widget **does not
  fit** and must be flagged (logic may still be reusable if decoupled from those plugins).

**Data model already set up in Door Expert:**
- Categories: `product_cat` (hierarchical).
- Brand: `product_brand` (WooCommerce Brands taxonomy).
- Attributes: `pa_boja` (color), `pa_dimenzije-vrata` (door sizes), `pa_dimenzije-plocica` (tile formats).
- Price: WooCommerce `_price`. Stock: `_stock_status`.

**What Door Expert already has built (so tell me what to ADD/UPGRADE, not duplicate):**
- Shop archive: `archive-product.php` + `template-parts/shop/{product-card,filters}.php` +
  `inc/shop.php` (server-side filtering/sort/pagination via `woocommerce_product_query`,
  `tax_query`/`meta_query`, GET params; **no JetSmartFilters**).
- PDP (single product): `single-product.php` + `template-parts/product/single.php` + `inc/product.php`.
  Currently **v1 = simple products**, variants shown **read-only** from attributes (no live price).
- Front-end JS is **vanilla** (IIFE modules, no jQuery of our own): `assets/js/product.js` currently does
  gallery-thumb switching, a lightbox, quantity stepper, and a basic tile m² calculator.
- We WANT to upgrade toward: interactive **variable-product** variation UI + price switching, a more
  robust gallery/lightbox, a real **quote-cart** experience (AJAX add-to-cart, cart drawer/badge),
  and a stronger tile calculator.

**Coding conventions Door Expert enforces (so assess how much rework porting needs):**
- WordPress PHP Coding Standards. **Tabs** for PHP indentation, `array()` (not `[]`), **Yoda**
  conditions, full `<?php` tags, no closing `?>` in PHP-only files, `if ( ! defined( 'ABSPATH' ) ) { exit; }`
  guard, all functions/hooks prefixed `door_expert_`.
- **Security:** sanitize on input (`wp_unslash()` then `sanitize_*`), late-escape on output
  (`esc_html`/`esc_attr`/`esc_url`/`wp_kses_post`/`wp_json_encode`), nonces + `current_user_can()`,
  `$wpdb->prepare()`. Custom AJAX (not CF7).
- **Assets:** enqueued via `wp_enqueue_*`, versioned with `filemtime()` cache-busting (never strip `?ver`),
  conditionally enqueued per page. **Mobile-first CSS** (base = mobile, `@media (min-width: …)` up).
- **Content language:** ijekavica (Montenegrin). **No em dash** ("—") on the front end. UTF-8 without BOM.
- Prefer **vanilla JS**. If a component depends on jQuery, say so (WooCommerce core ships jQuery, so
  jQuery-based add-to-cart is acceptable, but note it).

## Your task on THIS repo

1. Locate the theme and any custom plugins/mu-plugins. Identify which files implement each target above.
2. For every reusable piece, read enough of the code to understand its real dependencies and coupling.
3. Assess portability to Door Expert given the constraints and conventions above.
4. Where fit is high, extract the core logic and adapt it to our conventions (renamed/prefixed,
   escaped, tabs, no external deps we disallow) as copy-pasteable snippets.

## For EACH reusable component, report

- **Name & what it does** (one paragraph).
- **Where it lives:** exact files and `file:line` ranges (theme vs plugin vs mu-plugin).
- **Type:** PHP (template/hook/class), JS, CSS, or mixed.
- **Dependencies:** jQuery? WooCommerce hooks? a page builder / CF7 / Jet* plugin? external libraries
  (name + version)? custom post types / taxonomies / meta keys it assumes? AJAX endpoints/nonces?
- **Coupling / how self-contained** (can it be lifted cleanly, or is it entangled with that site's theme?).
- **Data-model mapping:** how its attributes/meta map onto our model (`product_cat`, `product_brand`,
  `pa_boja`, `pa_dimenzije-vrata`, `pa_dimenzije-plocica`, `_price`, `_stock_status`).
- **Portability verdict:** `DROP-IN` / `ADAPT (light)` / `ADAPT (heavy)` / `DOES-NOT-FIT` — with the reason.
- **Rework needed** to meet our conventions (escaping, prefixing, tabs, remove disallowed deps, mobile-first).
- **Suggested Door Expert home:** which of our files it would land in (e.g. `assets/js/product.js`,
  `template-parts/product/single.php`, `inc/product.php`, `inc/shop.php`, a new `inc/quote-cart.php`, etc.).
- For high-fit items: an **adapted snippet** (or a tight diff-style outline) ready to drop into Door Expert.

## Rules

- **Read-only.** Do not edit, refactor, or run destructive commands on this site.
- Cite real `file:line` for every claim; do not invent APIs or files — verify before asserting.
- Confirm the code is **mine/original** (or otherwise safe to reuse) and flag anything third-party,
  GPL-incompatible, or licensed in a way that blocks reuse.
- Be honest about weak spots: if a piece is buggy, insecure (unescaped output, missing nonce), or
  jQuery-spaghetti, say so and note what must be fixed on the way over.
- Prefer vanilla JS in recommendations; flag jQuery dependencies explicitly.

## Deliverable

A single Markdown report with:
1. **Summary table** — component · type · dependencies · portability verdict · suggested Door Expert home.
2. **Prioritized recommendation** — what to port first for the most value, given Door Expert already has
   a shop archive + a v1 PDP and now needs interactive variations, a real quote-cart, and a stronger
   tile calculator.
3. **Per-component detail** in the format above, with adapted snippets for the high-fit items.
4. **Red flags** — anything that depends on plugins/builders Door Expert does not allow, or that would
   need to be rewritten rather than ported.

Do not start porting anything into another project — just audit, assess, and produce the report so I can
decide what to carry over.
