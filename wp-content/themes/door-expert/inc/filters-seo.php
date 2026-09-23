<?php
/**
 * SEO za filtrirane arhive – meta robots i canonical.
 *
 * Svaka kombinacija filtera je zaseban URL (?pa_boja[]=…, ?product_brand[]=…, ?orderby=…)
 * sa skoro istim sadržajem kao čista kategorija. Bez ovoga ih Google indeksira i one se
 * takmiče sa stranicom iz koje su izvedene.
 *
 *   Filter URL      => noindex, nofollow + canonical na čist listing (bez parametara).
 *   Paginacija      => index, follow + canonical na samu sebe: strana 2 nije duplikat
 *                      strane 1 nego nastavak liste.
 *   Čist listing    => ne diramo robots; canonical na sebe (ako nema SEO plugina).
 *
 * Radi sa Rank Math-om i bez njega. Bez plugina WordPress na arhivama canonical ne
 * ispisuje uopšte (rel_canonical() pokriva samo singular), pa ga ispisujemo sami.
 *
 * NE dodavati Disallow za filter parametre u robots.txt prije nego što Google pročita
 * noindex: URL koji ne smije da crawl-uje je URL čiji noindex nikad ne vidi, pa ono što
 * je već indeksirano ostaje indeksirano (vidi DOCS/FOR DOOR EXPERT/08-PARITY…, §3.5).
 *
 * Zavisi od inc/shop.php i inc/filters.php – u functions.php se učitava POSLIJE njih.
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Da li je aktivan SEO plugin koji sam piše robots i canonical.
 *
 * Projekat koristi Rank Math (njegovi filteri su povezani niže). Yoast se samo
 * prepoznaje da ne bismo ispisali drugi canonical pored njegovog – njegovi filteri
 * NISU povezani, pa sa Yoast-om filter URL-ovi ne bi dobili noindex.
 *
 * @return bool
 */
function door_expert_seo_plugin_active() {
	return defined( 'RANK_MATH_VERSION' ) || class_exists( 'RankMath', false ) || defined( 'WPSEO_VERSION' );
}

/**
 * Da li je canonical u ovom zahtjevu već obrađen od strane SEO plugina.
 *
 * Ne oslanja se samo na konstantu: naziv konstante plugin može da promijeni, a ako
 * pogriješimo dobijemo dva rel=canonical taga – sa različitim vrijednostima Google
 * ignoriše oba. Pouzdan signal je da je Rank Math-ov canonical filter stvarno prošao
 * kroz naš callback, pa fallback ide na kasnom wp_head prioritetu.
 *
 * @return bool
 */
function door_expert_seo_canonical_handled() {
	if ( ! empty( $GLOBALS['door_expert_seo_canonical_done'] ) ) {
		return true;
	}

	return door_expert_seo_plugin_active();
}

/**
 * Query ključevi koji od arhive prave "filter" URL.
 *
 * Taksonomijski dio je iz istog izvora kao upit (door_expert_filter_taxonomies), da se
 * ne desi da neko doda atribut u konfigurator, on počne da filtrira, a SEO sloj za
 * njega ne zna i pusti ga u indeks.
 *
 * orderby je tu namjerno: ne mijenja skup proizvoda, ali ?orderby=price i
 * ?orderby=date su tačni duplikati kategorije, a to je gore od skoro-duplikata.
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
 * Da li tekući URL nosi filter parametre (ili je pretraga).
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

	foreach ( array_keys( $_GET ) as $raw_key ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- samo čitanje ključeva, bez mutacije.
		$key = sanitize_key( $raw_key );

		if ( 0 === strpos( $key, 'pa_' ) || in_array( $key, $keys, true ) ) {
			return true;
		}
	}

	return false;
}

/* ── Robots ───────────────────────────────────────────────────────────── */

add_filter( 'wp_robots', 'door_expert_filter_wp_robots' );
/**
 * Bez SEO plugina: noindex, nofollow kroz WordPress-ov wp_robots API.
 *
 * Core od 5.7 sam ispisuje JEDAN <meta name="robots"> (npr. max-image-preview:large),
 * pa dodajemo direktive u taj tag umjesto da ispisujemo drugi.
 *
 * @param array $robots Direktive (naziv => true ili vrijednost).
 * @return array
 */
function door_expert_filter_wp_robots( $robots ) {
	if ( door_expert_seo_plugin_active() || ! door_expert_is_filter_url() ) {
		return $robots;
	}

	unset( $robots['index'], $robots['follow'], $robots['max-image-preview'] );

	$robots['noindex']  = true;
	$robots['nofollow'] = true;

	return $robots;
}

add_filter( 'rank_math/frontend/robots', 'door_expert_filter_rank_math_robots', 99 );
/**
 * Sa Rank Math-om.
 *
 * Na čistoj strani 1 kategorije namjerno propuštamo Rank Math-ovu vrijednost: tu robots
 * kontroliše ko god radi SEO (per kategorija). Zato globalni default u Rank Math-u
 * (Titles & Meta → Product Categories → Robots Meta) MORA biti index, follow – inače
 * sve čiste kategorije odu u noindex, a filter URL-ovi izgledaju ispravno pa to niko
 * ne primijeti.
 *
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

/* ── Canonical ────────────────────────────────────────────────────────── */

/**
 * Čist URL tekuće arhive, bez ikakvih parametara.
 *
 * NE koristi door_expert_listing_base_url() za sve: ona zna samo za product_cat, a za
 * svaku drugu taksonomiju (brend, atribut) vraća prodavnicu. Canonical brend arhive na
 * /prodavnica/ bi Google-u rekao da je stranica brenda duplikat prodavnice.
 *
 * @return string
 */
function door_expert_filter_canonical_base() {
	if ( is_product_taxonomy() ) {
		$term = get_queried_object();

		if ( $term instanceof WP_Term ) {
			$link = get_term_link( $term );

			if ( ! is_wp_error( $link ) ) {
				return $link;
			}
		}
	}

	return door_expert_listing_base_url();
}

/**
 * Kanonski URL tekuće arhive.
 *
 * Filter URL (i na strani 2+) => čist listing: filtrirana lista nema svoju paginaciju
 * koju bi vrijedilo indeksirati. Paginacija bez filtera => sama sebi, bez query
 * stringa (utm_*, fbclid i sl. ne ulaze u canonical).
 *
 * @return string Prazan string kad nema šta da se kanonikalizuje.
 */
function door_expert_filter_canonical_url() {
	if ( ! ( is_shop() || is_product_taxonomy() ) ) {
		return '';
	}

	if ( door_expert_is_filter_url() ) {
		return door_expert_filter_canonical_base();
	}

	$paged = (int) get_query_var( 'paged' );
	if ( $paged >= 2 ) {
		$url = get_pagenum_link( $paged, false );
		$pos = strpos( $url, '?' );

		return false === $pos ? $url : substr( $url, 0, $pos );
	}

	return '';
}

add_filter( 'rank_math/frontend/canonical', 'door_expert_filter_rank_math_canonical' );
/**
 * @param string $canonical Rank Math canonical.
 * @return string
 */
function door_expert_filter_rank_math_canonical( $canonical ) {
	// Filter se okine samo ako Rank Math stvarno piše head – pouzdaniji signal od konstante.
	$GLOBALS['door_expert_seo_canonical_done'] = true;

	$url = door_expert_filter_canonical_url();

	return '' !== $url ? $url : $canonical;
}

add_action( 'wp_head', 'door_expert_filter_canonical_tag', 999 );
/**
 * Canonical kad nema SEO plugina (WordPress ga na arhivama ne ispisuje sam).
 *
 * Kasan prioritet je namjeran: do 999 je svaki SEO plugin već ispisao svoj canonical
 * i okinuo svoj filter, pa detekcija ne mora da pogađa.
 */
function door_expert_filter_canonical_tag() {
	if ( door_expert_seo_canonical_handled() ) {
		return;
	}

	$url = door_expert_filter_canonical_url();

	// Čista arhiva bez filtera: canonical je ona sama.
	if ( '' === $url && ( is_shop() || is_product_taxonomy() ) ) {
		$url = door_expert_filter_canonical_base();
	}

	if ( '' === $url ) {
		return;
	}

	printf( '<link rel="canonical" href="%s" />' . "\n", esc_url( $url ) );
}
