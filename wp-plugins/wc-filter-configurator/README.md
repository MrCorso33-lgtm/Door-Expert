# WC Filter Configurator

Drag & drop configuration of the WooCommerce product archive filter sidebar.

Which filters appear on which category, in what order, with what label, control
type and collapsed state — all edited in the admin and stored in one option, so
the theme never hardcodes an attribute list again.

- **Version:** 1.0.0
- **Requires:** WordPress 5.8+, WooCommerce 6.0+, PHP 7.4+
- **Admin screen:** Settings → Filter Configurator
- **Dependencies:** SortableJS 1.15.2 (bundled, self-hosted). No jQuery on the front end, no build step.

---

## 1. What problem this solves

A filter sidebar hardcoded in `archive-product.php` means every change to the
filter list is a code change and a deploy. Worse, the moment the store has more
than one product type (tiles vs. sanitary ware vs. taps), the template fills up
with `if ( $category === … )` branches.

This plugin splits that in two:

- **Configuration** (which attributes, what order, what labels) lives in an
  option, edited by a non-developer through a drag & drop UI.
- **Rendering and querying** stay generic — no attribute slug is ever hardcoded.

It also fixes the three things that make naive filter sidebars lie:

| Problem | What this does |
|---|---|
| Filter shows a value no product in this category has | Terms are scoped to the category's actual products |
| Count in brackets is the site-wide count | Counts are computed over the category's product set |
| Variable product shows terms with no purchasable variation | Variation meta is the source of truth when the attribute drives variations |

---

## 2. Scope — what is and is not included

**Included:**

- Admin drag & drop configurator (3 views: per category, global, attribute grouping)
- Configuration storage, resolution and sanitisation
- Front-end sidebar renderer (`wcfc_render_sidebar()`)
- Category scoping, correct term lists and correct counts
- Live facet computation with self-exclusion (`wcfc_compute_facets()`)
- Cache layer with automatic invalidation + a manual flush button

**Not included** — this is the boundary, read it before planning integration:

- **No AJAX product filtering engine.** The plugin renders the controls and can
  compute facet counts; wiring clicks to a query and swapping the product grid is
  the theme's job. Section 8 has a minimal no-JS integration that works on its own.
- **No CSS.** The markup is plain and documented (section 7); style it in the theme.
- **No colour palette for swatches.** Provide one through `wcfc_swatch_color_map`.

---

## 3. Installation

1. Copy `wc-filter-configurator/` into `wp-content/plugins/`.
2. Activate it. WooCommerce must be active or the plugin stays inert and says so.
3. Open **Settings → Filter Configurator** and build the filter lists.
4. Call `wcfc_render_sidebar();` where the sidebar belongs, usually in
   `woocommerce/archive-product.php`.

Nothing is written to the database on activation beyond a schema version, so an
un-configured install falls back to the defaults in `wcfc_default_configs()`.

---

## 4. Configuration model

Everything lives in the option `wcfc_filter_configs` (autoload off), an array
keyed by **context**:

```jsonc
{
  "global":  [ /* prepended to every archive */ ],
  "42":      [ /* filters for the category whose term_id is 42 */ ],
  "57":      [ /* … */ ],
  "default": [ /* fallback when no category-specific config matches */ ]
}
```

> Category keys are **term ids**, not slugs. A term id is permanent; a slug
> changes the moment somebody renames the category, which would silently drop
> that category's whole configuration.

### One filter

```jsonc
{
  "attr":       "pa_color",        // taxonomy slug, or a special key (below)
  "label":      "Colour",          // heading shown above the control
  "type":       "swatch",          // "checkbox" | "swatch"
  "collapsed":  false,             // start collapsed on the front end
  "categories": ["floor-tiles"]    // optional: only show in these categories
}
```

`categories` is empty by default, meaning "show everywhere in this context". When
populated, the filter shows in those categories **and their descendants** — so
scoping to a parent covers its children.

### Special attributes

Two `attr` values are not taxonomies. Their control type is fixed, shown as a
read-only badge in the admin, and forced on save:

| `attr` | Renders as | Fixed `type` |
|---|---|---|
| `price_slider` | Dual-range price slider | `price_slider` |
| `product_cat` | Hierarchical category tree (links, with counts on leaves) | `category_tree` |

### Sections

A section groups several filters under one heading. One level only — sections do
not nest.

```jsonc
{
  "type":      "section",
  "label":     "Technical specs",
  "collapsed": true,
  "filters":   [ { "attr": "pa_thickness", "label": "Thickness", "type": "checkbox", "collapsed": false } ]
}
```

A section whose filters all get scoped out (or produce no terms) is dropped
entirely rather than rendered as an empty heading.

### Attribute grouping — `wcfc_attr_groups`

A second option holds the admin-side grouping of the attribute pool:

```jsonc
[ { "id": "g1712345678", "name": "Technical", "attrs": ["pa_thickness", "pa_pei_class"] } ]
```

This is **purely cosmetic** — it turns the left-hand pool into an accordion so a
catalogue with 60+ attributes stays navigable. It has no front-end effect.

### Resolution order

When rendering, the context resolves like this:

```
1. exact context key                  configs["<term_id>"]
2. nearest configured ancestor        configs["<ancestor_term_id>"]
3. fallback                           configs["default"]
4. apply each filter's `categories` scope   (category contexts only)
5. prepend configs["global"], skipping attributes already present
```

Step 2 is why **subcategories inherit their parent** and you do not have to
configure 40 of them. Fine-tuning per subcategory is what `categories` is for.

---

## 5. Public API

Everything is a plain function; no classes, no instantiation.

### Rendering

```php
wcfc_render_sidebar();                                  // auto-detects the current category
wcfc_render_sidebar( [ 'context' => 'my-landing' ] );   // a custom context
$html = wcfc_render_sidebar( [ 'echo' => false ] );      // return instead of echo
wcfc_render_sidebar( [ 'scope' => false ] );             // skip category scoping (site-wide terms)
```

### Configuration

| Function | Returns |
|---|---|
| `wcfc_get_configs()` | The whole stored configuration |
| `wcfc_get_filters_for_context( $context )` | Resolved filter list for a context |
| `wcfc_attrs_for_context( $context )` | Flat taxonomy slug list, sections flattened, specials skipped — feed this to faceting |
| `wcfc_get_contexts()` | Context key => name, as shown in the admin tabs |
| `wcfc_get_all_attributes()` | Every available attribute, slug => label |

### Scoping, counts, faceting

| Function | Returns |
|---|---|
| `wcfc_get_cat_product_ids( int $term_id )` | Published, catalogue-visible product ids in a category and its descendants |
| `wcfc_term_counts( array $ids, string $tax )` | `[ slug => count ]` scoped to those products |
| `wcfc_used_attr_slugs( array $ids, string $tax )` | Term slugs genuinely offered (variation-aware) |
| `wcfc_compute_facets( int $term_id, array $selected, $min, $max, array $attrs )` | `[ tax => [ slug => count ] ]` with self-exclusion |
| `wcfc_max_price()` | Ceiling for the price slider |
| `wcfc_bump_cache_version()` | Invalidate every cached lookup |

**Self-exclusion** is what keeps multi-select usable: each facet is counted over
the products satisfying every *other* active filter but not itself, so ticking
"red" does not zero out "blue" in the same colour facet.

---

## 6. Hooks

### Filters you will probably use

| Filter | Default | Purpose |
|---|---|---|
| `wcfc_default_configs` | minimal price + brand | Ship your own starting configuration |
| `wcfc_config_contexts` | top-level categories + `default` | Add or remove admin tabs / custom contexts |
| `wcfc_brand_taxonomy` | `product_brand` | Brand taxonomy name (`pwb-brand`, `yith_product_brand`, …) |
| `wcfc_swatch_color_map` | `[]` | `[ slug => '#hex' ]` per taxonomy; **required** for swatches |
| `wcfc_swatch_light_slugs` | `[]` | Slugs that need an outline (white, cream, …) |
| `wcfc_max_price` | computed | Override the slider ceiling |
| `wcfc_price_step` | max / 1000 | Slider step |

### Filters for changing markup

| Filter | Purpose |
|---|---|
| `wcfc_pre_render_filter` | Return a string to replace one filter's markup entirely |
| `wcfc_filter_html` | Post-process one filter's markup |
| `wcfc_sidebar_html` | Post-process the whole sidebar |
| `wcfc_all_attributes` | Add or remove attributes from the pool |
| `wcfc_filters_for_context` | Last word on the resolved filter list |

Example — swatch colours for a tile store:

```php
add_filter( 'wcfc_swatch_color_map', function ( $map, $taxonomy ) {
    if ( 'pa_color' !== $taxonomy ) {
        return $map;
    }
    return [
        'white' => '#ffffff',
        'beige' => '#e8dcc8',
        'grey'  => '#9a9a9a',
        'black' => '#1a1a1a',
    ];
}, 10, 2 );

add_filter( 'wcfc_swatch_light_slugs', function ( $slugs, $taxonomy ) {
    return 'pa_color' === $taxonomy ? [ 'white', 'beige' ] : $slugs;
}, 10, 2 );
```

A `swatch` filter with no colour map falls back to checkboxes rather than
rendering a row of blank circles.

---

## 7. DOM contract

What `wcfc_render_sidebar()` outputs. Style and script against these.

```html
<div class="wcfc-group wcfc-group--checkbox is-collapsed" data-attr="pa_color">
  <h3 class="wcfc-group__title">Colour<span class="wcfc-arrow"></span></h3>
  <div class="wcfc-group__options">
    <label class="wcfc-option">
      <input type="checkbox" name="pa_color" value="white">
      <span>White</span>
      <small class="wcfc-count">(12)</small>
    </label>
  </div>
</div>
```

| Element | Class / id |
|---|---|
| Filter group | `.wcfc-group`, `.wcfc-group--{checkbox,swatch,price,category}`, `data-attr` |
| Collapsed state | `.is-collapsed` on the group or section |
| Group heading | `.wcfc-group__title` + `.wcfc-arrow` |
| Options wrapper | `.wcfc-group__options` |
| Checkbox row | `.wcfc-option` with `input[name="<taxonomy>"][value="<slug>"]` |
| Count | `.wcfc-count` |
| Swatch | `button.wcfc-swatch[data-attr][data-value][data-count]`, colour via `--wcfc-swatch-color`, light variant `.wcfc-swatch--light` |
| Category link | `a.wcfc-option.wcfc-option--link` |
| Price slider | `#wcfc-price-min`, `#wcfc-price-max`, `#wcfc-price-fill`, labels `#wcfc-price-min-label` / `#wcfc-price-max-label` |
| Section | `.wcfc-section`, `.wcfc-section__title`, `.wcfc-section__body` |

The checkbox `name` is the taxonomy slug and the `value` is the term slug, so a
serialised form is already a valid filter query.

---

## 8. Minimal integration (works without JavaScript)

Put the sidebar in the archive template:

```php
<aside class="filters">
    <?php wcfc_render_sidebar(); ?>
</aside>
```

Then make bare filter URLs (`?pa_color[]=white&pa_color[]=grey`) actually filter,
so the sidebar is functional before any AJAX exists:

```php
add_action( 'pre_get_posts', function ( $query ) {
    if ( is_admin() || ! $query->is_main_query() ) {
        return;
    }
    if ( ! $query->is_post_type_archive( 'product' ) && ! $query->is_tax( 'product_cat' ) ) {
        return;
    }

    // Only accept registered product attributes — never trust a raw query key.
    $known = [];
    foreach ( wc_get_attribute_taxonomies() as $attr ) {
        $known[] = wc_attribute_taxonomy_name( $attr->attribute_name );
    }
    $known[] = wcfc_brand_taxonomy();

    $tax_query = [];
    foreach ( $_GET as $key => $raw ) {
        $key = sanitize_key( $key );
        if ( ! in_array( $key, $known, true ) ) {
            continue;
        }
        $values = array_filter( array_map( 'sanitize_title', (array) $raw ) );
        if ( $values ) {
            $tax_query[] = [
                'taxonomy' => $key,
                'field'    => 'slug',
                'terms'    => $values,
                'operator' => 'IN',   // OR within one attribute
            ];
        }
    }

    if ( $tax_query ) {
        $existing = (array) $query->get( 'tax_query' );
        $query->set( 'tax_query', array_merge( $existing, $tax_query, [ 'relation' => 'AND' ] ) );
    }
} );
```

That gives you: OR within an attribute, AND between attributes — the standard
faceted behaviour. Layer AJAX on top later; the URL shape stays the same.

To grey out zero-result options while filtering, feed the current selection to
`wcfc_compute_facets()` and compare against the rendered `data-count` / `.wcfc-count`.

---

## 9. Caching

Category product lists and facet counts are cached in transients whose key
embeds one global version number (option `wcfc_cat_pids_ver`). Bumping that
version invalidates everything at once — deliberately, because a per-category
invalidator misses the important case: a product being *removed* from a category.

Automatic invalidation fires on `save_post_product`, `before_delete_post`,
`set_object_terms` (for `product_cat`), and category create/edit/delete.

The **Flush filter cache** button exists for changes that bypass WordPress
entirely — a direct SQL import, for instance — which never fire those hooks.

---

## 10. Localisation

Admin UI strings are English, wrapped in `__()` with text domain
`wc-filter-configurator`. JavaScript strings are passed in from PHP
(`wcfc_admin_i18n()`), so a single `.po/.mo` in `languages/` translates both.

To change the admin accent colour, override the CSS variables:

```php
add_action( 'admin_enqueue_scripts', function () {
    wp_add_inline_style( 'wcfc-admin', ':root{--wcfc-accent:#7f54b3;--wcfc-accent-rgb:127,84,179;}' );
}, 20 );
```

---

## 11. Files

```
wc-filter-configurator/
  wc-filter-configurator.php     bootstrap, constants, dependency check
  includes/
    config.php                   option access, context resolution, sanitisation
    attributes.php               attribute pool, category tree, slug normalisation
    scoping.php                  cache, category product ids, counts, faceting
    render.php                   front-end sidebar renderer
    admin.php                    admin screen (3 views) + asset enqueue
    ajax.php                     4 admin-ajax endpoints
  assets/
    admin.js                     drag & drop editor
    admin.css                    admin styling (accent via CSS variables)
    vendor/Sortable.min.js       SortableJS 1.15.2, self-hosted
  README.md
```

### AJAX endpoints

All require the `wcfc_nonce` and `manage_options`.

| Action | Purpose |
|---|---|
| `wcfc_save_config` | Save one context's filter list (JSON payload) |
| `wcfc_save_groups` | Replace the attribute grouping |
| `wcfc_add_group` | Add or update one group by name |
| `wcfc_flush_cache` | Bump the cache version |

---

## 12. Admin usage

| Action | How |
|---|---|
| Add a filter | Drag a chip from the left pool into the right column |
| Reorder | Drag by the ⠇ handle; order = render order |
| Remove | ✕ — the chip returns to the pool |
| Rename | Type in the label field |
| Change control | The `swatch` / `checkbox` select (special filters show a locked badge) |
| Collapse by default | Tick "Collapsed" |
| Group filters | "+ Section", then drag filters into it. Deleting a section returns its filters to the main list |
| Limit to subcategories | Click "All cats" and pick from the tree |
| Save | "Save configuration", or **Ctrl/Cmd + S** |

Tabs switch without a page reload. An attribute already in use appears greyed
out in the pool rather than hidden, so you can still see which group it is in.

---

## 13. Troubleshooting

| Symptom | Cause / fix |
|---|---|
| A filter renders nothing | The attribute has no terms among the category's products, or the slug does not exist. WooCommerce slugifies multi-word attribute names with **dashes** (`pa_surface-finish`, not `pa_surface_finish`); the plugin auto-corrects this once, on the first admin load after an upgrade |
| Counts look stale after an import | Click **Flush filter cache** — direct SQL bypasses the invalidation hooks |
| Swatches render as grey circles | No `wcfc_swatch_color_map` for that taxonomy; without one it falls back to checkboxes |
| A ghost value shows for a variable product | Only happens if `wcfc_used_attr_slugs()` is bypassed — that is, `scope => false` or a non-category context |
| Subcategory shows the wrong filters | It inherits the nearest configured ancestor. Give it its own context key, or use per-filter `categories` |
| Brand filter is empty | `wcfc_brand_taxonomy` does not match your store's taxonomy |
| Drag & drop dead, console error about `Sortable` | `assets/vendor/Sortable.min.js` missing from the upload |

---

## 14. Smoke test

1. Drag an attribute in, save, reload — it persists and its chip is greyed out in the pool.
2. Reorder, save, open a category page — the sidebar follows that order.
3. Change a label and tick "Collapsed" — both are reflected on the front end.
4. Create a section with two filters — it renders as one titled block.
5. Scope a filter to one subcategory — it appears there and in its children, not in a sibling.
6. Open a subcategory with no configuration of its own — it shows the parent's set.
7. Add a global filter — it appears first on every archive, with no duplicate where the category already had it.
8. Compare a term count in brackets against the result count after clicking it — they must match.
9. Move a product out of a category — counts update on the next load.
10. Log out and POST to `wcfc_save_config` — it must fail.
