<?php
/**
 * Shop (prodavnica) – server-side query sloj za WooCommerce arhivu (archive-product.php).
 *
 * Filteri idu kroz glavni WC upit (woocommerce_product_query) => paginacija i brojač
 * ostaju konzistentni. BEZ JetSmartFilters / Jet frontend widgeta (CLAUDE.md §2).
 *
 * Data model (odluka projekta):
 *   - Kategorija => product_cat (postoji).
 *   - Brend      => product_brand (WooCommerce Brands taksonomija).
 *   - Boja       => pa_boja (globalni atribut).
 *   - Dimenzije  => pa_dimenzije-vrata + pa_dimenzije-plocica (dva atributa, razliciti domeni).
 *   - Dostupnost => native WC stock status (_stock_status).
 *   - Cijena     => _price meta.
 *
 * URL parametri (GET, multi-select preko nizova):
 *   f_cat[]      - kategorija (hero pilule na prodavnici)
 *   <taksonomija>[] - po jedan parametar za svaku taksonomiju, imenovan kao ona sama:
 *                  product_brand[], pa_boja[], pa_dimenzije-vrata[] ...
 *   f_stock[], min_price, max_price, orderby
 *
 * Zašto naziv taksonomije umjesto ranijih f_brand / f_boja / f_dim_*: sidebar renderuje
 * plugin WC Filter Configurator (vidi inc/filters.php), a on checkboxu daje name
 * jednak taksonomiji. Prihvatanjem tog oblika izbjegnut je prevodilac između dva
 * imenovanja. Stari f_* nazivi su uklonjeni, ne podržavaju se paralelno.
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mapiranje filter-parametra => taksonomija.
 *
 * Whitelist dolazi iz WooCommerce registra atributa (door_expert_filter_taxonomies),
 * ne iz fiksne liste. Fiksna lista bi značila da konfigurator u adminu može dodati
 * filter koji ovaj upit tiho ignoriše, pa sidebar radi a rezultati su nefiltrirani.
 *
 * @return array<string,string>
 */
function door_expert_shop_filter_taxonomies() {
	// f_cat ostaje po imenu: njega ne renderuje plugin nego hero pilule na prodavnici.
	$map = array( 'f_cat' => 'product_cat' );

	if ( function_exists( 'door_expert_filter_taxonomies' ) ) {
		foreach ( door_expert_filter_taxonomies() as $taxonomy ) {
			$map[ $taxonomy ] = $taxonomy;
		}
	}

	return $map;
}

/**
 * Pročitaj i sanitizuj izabrane vrijednosti jednog filter-parametra iz $_GET.
 *
 * @param string $key Naziv GET parametra (npr. 'f_cat').
 * @return string[] Niz slug-ova (može biti prazan).
 */
function door_expert_shop_selected( $key ) {
	if ( ! isset( $_GET[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only filter, nema mutacije.
		return array();
	}

	$raw = wp_unslash( $_GET[ $key ] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$raw = is_array( $raw ) ? $raw : array( $raw );

	$out = array();
	foreach ( $raw as $value ) {
		$slug = sanitize_title( $value );
		if ( '' !== $slug ) {
			$out[] = $slug;
		}
	}

	return array_values( array_unique( $out ) );
}

/**
 * Trenutna cjenovna granica iz $_GET (0 ako nije postavljena).
 *
 * @param string $key 'min_price' ili 'max_price'.
 * @return float
 */
function door_expert_shop_price( $key ) {
	if ( ! isset( $_GET[ $key ] ) || '' === $_GET[ $key ] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return 0.0;
	}
	return (float) wp_unslash( $_GET[ $key ] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
}

add_filter( 'woocommerce_product_query_tax_query', 'door_expert_shop_tax_query', 10, 2 );
/**
 * Dodaj tax filtere (kategorija/brend/boja/dimenzije) u tax_query glavnog shop upita.
 * WC sam merge-uje ove klauzule sa vidljivošću proizvoda (product_visibility).
 *
 * @param array    $tax_query Postojeći tax_query.
 * @param WC_Query $wc_query  WC query objekat (nekorišćen).
 * @return array
 */
function door_expert_shop_tax_query( $tax_query, $wc_query ) {
	foreach ( door_expert_shop_filter_taxonomies() as $param => $taxonomy ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			continue;
		}

		$terms = door_expert_shop_selected( $param );
		if ( empty( $terms ) ) {
			continue;
		}

		$clause = array(
			'taxonomy' => $taxonomy,
			'field'    => 'slug',
			'terms'    => $terms,
			'operator' => 'IN',
		);

		// Kategorija: uključi i potkategorije (roditelj => sva djeca).
		if ( 'product_cat' === $taxonomy ) {
			$clause['include_children'] = true;
		}

		$tax_query[] = $clause;
	}

	return $tax_query;
}

add_filter( 'woocommerce_product_query_meta_query', 'door_expert_shop_meta_query' );
/**
 * Dodaj cjenovni raspon (_price) i dostupnost (_stock_status) u meta_query.
 *
 * @param array $meta_query Postojeći meta_query.
 * @return array
 */
function door_expert_shop_meta_query( $meta_query ) {
	// Cijena.
	$min = door_expert_shop_price( 'min_price' );
	$max = door_expert_shop_price( 'max_price' );

	if ( $min > 0 && $max > 0 ) {
		$meta_query[] = array(
			'key'     => '_price',
			'value'   => array( $min, $max ),
			'type'    => 'DECIMAL(10,2)',
			'compare' => 'BETWEEN',
		);
	} elseif ( $min > 0 ) {
		$meta_query[] = array(
			'key'     => '_price',
			'value'   => $min,
			'type'    => 'DECIMAL(10,2)',
			'compare' => '>=',
		);
	} elseif ( $max > 0 ) {
		$meta_query[] = array(
			'key'     => '_price',
			'value'   => $max,
			'type'    => 'DECIMAL(10,2)',
			'compare' => '<=',
		);
	}

	// Dostupnost: native WC stock status.
	$stock = door_expert_shop_selected( 'f_stock' );
	if ( ! empty( $stock ) ) {
		$map      = array(
			'na-stanju'   => 'instock',
			'po-narudzbi' => 'onbackorder',
		);
		$statuses = array();
		foreach ( $stock as $s ) {
			if ( isset( $map[ $s ] ) ) {
				$statuses[] = $map[ $s ];
			}
		}
		if ( ! empty( $statuses ) ) {
			$meta_query[] = array(
				'key'     => '_stock_status',
				'value'   => $statuses,
				'compare' => 'IN',
			);
		}
	}

	return $meta_query;
}

add_filter( 'loop_shop_per_page', 'door_expert_shop_per_page' );
/**
 * Broj proizvoda po strani na shop arhivi.
 *
 * @return int
 */
function door_expert_shop_per_page() {
	return 12;
}

/**
 * URL trenutne shop stranice bez paginacije, sa datim/izmijenjenim query stringom.
 * Koristi se za "Očisti sve" i kanonske linkove filtera.
 *
 * @param array $args Dodatni/override query parametri (prazan => čist URL).
 * @return string
 */
function door_expert_shop_base_url( $args = array() ) {
	$base = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : '';

	if ( empty( $base ) ) {
		$base = home_url( '/prodavnica/' );
	}

	if ( empty( $args ) ) {
		return $base;
	}

	return add_query_arg( $args, $base );
}

/**
 * tax_query klauzula za vidljivost proizvoda, kao na WooCommerce arhivi.
 *
 * Sakriveno iz kataloga nikad ne ulazi, rasprodato samo ako je prodavnica podešena
 * da ga krije. Gradi se ručno umjesto pozivom WC_Query::get_tax_query(), jer ta
 * metoda na kraju primijeni i filter woocommerce_product_query_tax_query – dakle
 * našu door_expert_shop_tax_query() – pa bi se filteri dodali dvaput.
 *
 * @return array Prazan niz ako nema šta da se isključi.
 */
function door_expert_shop_visibility_clause() {
	if ( ! function_exists( 'wc_get_product_visibility_term_ids' ) ) {
		return array();
	}

	$visibility = wc_get_product_visibility_term_ids();
	$hidden     = array();

	if ( ! empty( $visibility['exclude-from-catalog'] ) ) {
		$hidden[] = (int) $visibility['exclude-from-catalog'];
	}
	if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) && ! empty( $visibility['outofstock'] ) ) {
		$hidden[] = (int) $visibility['outofstock'];
	}

	if ( empty( $hidden ) ) {
		return array();
	}

	return array(
		'taxonomy' => 'product_visibility',
		'field'    => 'term_taxonomy_id',
		'terms'    => $hidden,
		'operator' => 'NOT IN',
	);
}

/**
 * Broj proizvoda u grupi kategorija, za hero pilule. Prazan niz => svi proizvodi.
 *
 * Broji RAZLIČITE proizvode, upitom sa istim uslovima koje klik na pilulu proizvede
 * (include_children + vidljivost u katalogu). Ranije se sabirao term->count roditelja
 * i svakog potomka, pa se proizvod koji je i u "Sobna vrata" i u njenoj potkategoriji
 * brojao dvaput: pilula "Vrata" je pokazivala 4 nad katalogom od 2 proizvoda.
 *
 * Isto važi i za "Sve": ranije wp_count_posts(), koji broji i proizvode sakrivene iz
 * kataloga, pa se nije poklapao sa "Prikazano N proizvoda". Jedan izvor istine za sve
 * četiri pilule.
 *
 * Namjerno NE uzima u obzir ostale aktivne filtere (boja, brend, cijena): pilule su
 * prekidač kategorije, a ne facet. Broj na piluli mora reći koliko ta kategorija ima,
 * a ne koliko je ostalo od tekućeg izbora.
 *
 * @param string[] $parent_slugs Slug-ovi roditeljskih product_cat termova (prazno = svi).
 * @return int
 */
function door_expert_shop_group_count( $parent_slugs ) {
	static $cache = array();

	$parent_slugs = array_values( array_filter( array_map( 'sanitize_title', (array) $parent_slugs ) ) );
	sort( $parent_slugs );

	$cache_key = implode( ',', $parent_slugs );
	if ( isset( $cache[ $cache_key ] ) ) {
		return $cache[ $cache_key ];
	}

	$tax_query = array();

	if ( ! empty( $parent_slugs ) ) {
		$tax_query[] = array(
			'taxonomy'         => 'product_cat',
			'field'            => 'slug',
			'terms'            => $parent_slugs,
			'operator'         => 'IN',
			'include_children' => true,
		);
	}

	$visibility = door_expert_shop_visibility_clause();
	if ( ! empty( $visibility ) ) {
		$tax_query[] = $visibility;
	}

	$args = array(
		'post_type'              => 'product',
		'post_status'            => 'publish',
		'posts_per_page'         => 1,
		'fields'                 => 'ids',
		'ignore_sticky_posts'    => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
	);

	if ( ! empty( $tax_query ) ) {
		$args['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- brojač pilule, bez alternative.
	}

	$query = new WP_Query( $args );

	$cache[ $cache_key ] = (int) $query->found_posts;

	return $cache[ $cache_key ];
}

/**
 * Bazni URL tekućeg listinga – shop arhiva ILI kategorijska arhiva.
 *
 * Filter forme i sort na kategorijskoj stranici moraju slati nazad na TU kategoriju,
 * ne na prodavnicu. Sve ostalo (parametri, hidden inputs) je isto.
 *
 * @param array $args Dodatni query parametri.
 * @return string
 */
function door_expert_listing_base_url( $args = array() ) {
	$base = '';

	if ( is_tax( 'product_cat' ) ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$link = get_term_link( $term );
			if ( ! is_wp_error( $link ) ) {
				$base = $link;
			}
		}
	}

	if ( '' === $base ) {
		$base = door_expert_shop_base_url();
	}

	return empty( $args ) ? $base : add_query_arg( $args, $base );
}

/**
 * Svi filter/sort GET parametri koje čuvamo pri submit-u (za mirror hidden inputs).
 *
 * Taksonomijski dio je dinamičan: koliko atributa WooCommerce ima, toliko parametara.
 *
 * @return string[]
 */
function door_expert_shop_state_params() {
	$taxonomy_params = function_exists( 'door_expert_filter_taxonomies' )
		? door_expert_filter_taxonomies()
		: array();

	return array_merge(
		array( 'f_cat' ),
		$taxonomy_params,
		array( 'f_stock', 'min_price', 'max_price', 'orderby' )
	);
}

/**
 * Ispis <input type="hidden"> za sve trenutne filter parametre osim navedenih.
 * Omogućava da jedna GET forma (npr. sort) ne izgubi stanje drugih filtera.
 *
 * @param array $exclude Parametri koje NE mirror-ujemo (jer ih forma sama posjeduje).
 */
function door_expert_shop_hidden_inputs( $exclude = array() ) {
	foreach ( door_expert_shop_state_params() as $param ) {
		if ( in_array( $param, $exclude, true ) ) {
			continue;
		}

		if ( 'min_price' === $param || 'max_price' === $param ) {
			$val = door_expert_shop_price( $param );
			if ( $val > 0 ) {
				printf( '<input type="hidden" name="%s" value="%s" />', esc_attr( $param ), esc_attr( (string) $val ) );
			}
			continue;
		}

		if ( 'orderby' === $param ) {
			$ordering = door_expert_shop_selected( 'orderby' );
			if ( ! empty( $ordering ) ) {
				printf( '<input type="hidden" name="orderby" value="%s" />', esc_attr( $ordering[0] ) );
			}
			continue;
		}

		foreach ( door_expert_shop_selected( $param ) as $val ) {
			printf( '<input type="hidden" name="%s[]" value="%s" />', esc_attr( $param ), esc_attr( $val ) );
		}
	}
}

/**
 * Tekući filter/sort parametri kao niz (za add_query_arg / paginate_links).
 * Isti izvor kao hidden inputi, samo u obliku niza umjesto markupa.
 *
 * @return array
 */
function door_expert_shop_query_args() {
	$args = array();

	foreach ( door_expert_shop_state_params() as $param ) {
		if ( 'min_price' === $param || 'max_price' === $param ) {
			$val = door_expert_shop_price( $param );
			if ( $val > 0 ) {
				$args[ $param ] = $val;
			}
			continue;
		}

		$values = door_expert_shop_selected( $param );
		if ( empty( $values ) ) {
			continue;
		}

		if ( 'orderby' === $param ) {
			$args['orderby'] = $values[0];
			continue;
		}

		$args[ $param ] = $values;
	}

	return $args;
}

/* ── Renderovanje listinga ────────────────────────────────────────────
 * Jedna definicija markupa za sve tri putanje: shop arhivu, kategorijski
 * listing i AJAX odgovor. Da AJAX renderuje svoju verziju kartice ili
 * paginacije značilo bi dvije implementacije koje se vremenom raziđu.
 */

/**
 * Markup paginacije listinga.
 *
 * @param int    $paged    Tekuća strana.
 * @param int    $pages    Ukupno strana.
 * @param string $base_url Bazni URL listinga. Prazno (serverska putanja) => bazu daje
 *                         get_pagenum_link iz glavnog upita, kao i do sad. U AJAX-u
 *                         glavnog upita nema, pa se baza mora proslijediti.
 * @return string Prazan string kad ima manje od dvije strane.
 */
function door_expert_shop_pagination( $paged, $pages, $base_url = '' ) {
	$paged = max( 1, (int) $paged );
	$pages = (int) $pages;

	if ( $pages < 2 ) {
		return '';
	}

	if ( '' === $base_url ) {
		$big      = 999999999;
		$base     = str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) );
		$format   = '?paged=%#%';
		$add_args = array();
	} else {
		// Projekat zahtijeva /page/N/ URL-ove (nikad ?paged=N) – vidi CLAUDE.md §5.
		$base     = trailingslashit( $base_url ) . 'page/%#%/';
		$format   = '';
		$add_args = door_expert_shop_query_args();
	}

	$links = paginate_links(
		array(
			'base'      => $base,
			'format'    => $format,
			'current'   => $paged,
			'total'     => $pages,
			'add_args'  => $add_args,
			'type'      => 'plain',
			'end_size'  => 1,
			'mid_size'  => 2,
			'prev_text' => '<svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>',
			'next_text' => '<svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>',
		)
	);

	if ( ! $links ) {
		return '';
	}

	return '<nav class="shop-pagination" aria-label="Stranice">' . $links . '</nav>';
}

/**
 * Grid + paginacija (ili prazno stanje) za dati upit.
 *
 * Sadržaj #shopResults kontejnera: AJAX vraća tačno ovo i njime zamijeni kontejner,
 * pa su serverski i AJAX prikaz po definiciji isti.
 *
 * @param WP_Query|null $query    Upit; null = glavni upit stranice.
 * @param string        $base_url Bazni URL listinga (paginacija, "Očisti filtere").
 * @return string
 */
function door_expert_shop_results( $query, $base_url ) {
	$listing = $query instanceof WP_Query ? $query : $GLOBALS['wp_query'];

	ob_start();

	if ( $listing->have_posts() ) {
		$paged = max( 1, (int) $listing->get( 'paged' ), (int) $listing->get( 'page' ) );

		// Na glavnom upitu bazu paginacije daje get_pagenum_link (dokazano ponašanje);
		// eksplicitna baza je potrebna samo u AJAX-u, gdje glavnog upita nema.
		$pagination_base = ( $query instanceof WP_Query ) ? $base_url : '';

		echo '<div class="shop-grid">';
		while ( $listing->have_posts() ) {
			$listing->the_post();
			get_template_part( 'template-parts/shop/product-card' );
		}
		echo '</div>';

		// paginate_links vraća bezbjedan markup (escape-ovani URL-ovi + naš statičan SVG).
		echo door_expert_shop_pagination( $paged, $listing->max_num_pages, $pagination_base ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	} else {
		printf(
			'<div class="shop-empty"><p class="shop-empty__title">Nema proizvoda za izabrane filtere.</p><a class="shop-empty__reset" href="%s">Očisti filtere</a></div>',
			esc_url( $base_url )
		);
	}

	wp_reset_postdata();

	return ob_get_clean();
}

/**
 * Tekst brojača u toolbaru ("Prikazano 7 proizvoda"), bez omotača.
 *
 * @param int $found Broj pronađenih proizvoda.
 * @return string
 */
function door_expert_shop_count_html( $found ) {
	$found = (int) $found;

	return sprintf(
		'Prikazano <strong>%1$d</strong> %2$s',
		$found,
		esc_html( _n( 'proizvod', 'proizvoda', $found, 'door-expert' ) )
	);
}
