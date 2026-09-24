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
