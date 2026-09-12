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
