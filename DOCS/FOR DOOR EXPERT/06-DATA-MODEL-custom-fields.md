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
