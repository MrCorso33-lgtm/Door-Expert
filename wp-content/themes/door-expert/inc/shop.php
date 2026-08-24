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
 *   f_cat[], f_brand[], f_boja[], f_dim_vrata[], f_dim_plocica[], f_stock[], min_price, max_price, orderby
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mapiranje filter-parametra => taksonomija.
 *
 * @return array<string,string>
 */
function door_expert_shop_filter_taxonomies() {
	return array(
		'f_cat'         => 'product_cat',
		'f_brand'       => 'product_brand',
		'f_boja'        => 'pa_boja',
		'f_dim_vrata'   => 'pa_dimenzije-vrata',
		'f_dim_plocica' => 'pa_dimenzije-plocica',
	);
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
 * Ukupan broj proizvoda u grupi kategorija (roditelji + sve njihove potkategorije).
 * Za hero pilule. Sabira term->count (WC brojač) roditelja i potomaka.
 *
 * @param string[] $parent_slugs Slug-ovi roditeljskih product_cat termova.
 * @return int
 */
function door_expert_shop_group_count( $parent_slugs ) {
	$total = 0;

	foreach ( $parent_slugs as $slug ) {
		$term = get_term_by( 'slug', $slug, 'product_cat' );
		if ( ! $term instanceof WP_Term ) {
			continue;
		}

		$total += (int) $term->count;

		$children = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
				'child_of'   => $term->term_id,
			)
		);
		if ( ! is_wp_error( $children ) ) {
			foreach ( $children as $child ) {
				$total += (int) $child->count;
			}
		}
	}

	return $total;
}

/**
 * Svi filter/sort GET parametri koje čuvamo pri submit-u (za mirror hidden inputs).
 *
 * @return string[]
 */
function door_expert_shop_state_params() {
	return array( 'f_cat', 'f_brand', 'f_boja', 'f_dim_vrata', 'f_dim_plocica', 'f_stock', 'min_price', 'max_price', 'orderby' );
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
