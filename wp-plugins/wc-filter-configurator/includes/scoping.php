<?php
/**
 * Category scoping, term counts and live faceting.
 *
 * This is what stops the sidebar from lying. Three rules:
 *
 *   A. Only products actually visible in the catalogue are counted.
 *   B. For a variable product, an attribute term only counts when a published
 *      variation really uses it (the parent's "Values" list is not proof).
 *   C. Counts are scoped to the current category, never global term counts.
 *
 * Everything is cached in transients keyed by a single global cache version, so
 * one bump invalidates every category at once.
 *
 * @package WC_Filter_Configurator
 */

defined( 'ABSPATH' ) || exit;

/**
 * Current cache version. Every transient key embeds it.
 *
 * @return int
 */
function wcfc_cache_version(): int {
	$version = (int) get_option( WCFC_CACHE_VER_OPTION, 0 );
	if ( $version <= 0 ) {
		$version = 1;
		update_option( WCFC_CACHE_VER_OPTION, $version, false );
	}
	return $version;
}

/**
 * Invalidate every cached category/facet lookup at once.
 */
function wcfc_bump_cache_version() {
	update_option( WCFC_CACHE_VER_OPTION, time(), false );
}

/* ── Invalidation hooks ──────────────────────────────────────────────
 * A per-category invalidator misses the important case: a product being
 * REMOVED from a category. Bumping one global version covers it.
 */

add_action( 'save_post_product', 'wcfc_invalidate_on_product_change' );
add_action( 'before_delete_post', 'wcfc_invalidate_on_product_change' );

/**
 * @param int $post_id
 */
function wcfc_invalidate_on_product_change( $post_id ) {
	if ( 'product' !== get_post_type( $post_id ) ) {
		return;
	}
	wcfc_bump_cache_version();
}

// Catches a product leaving a category, which save_post cannot see.
add_action( 'set_object_terms', function ( $object_id, $terms, $tt_ids, $taxonomy ) {
	if ( 'product_cat' === $taxonomy ) {
		wcfc_bump_cache_version();
	}
}, 10, 4 );

// Editing the category tree changes which products belong where.
add_action( 'created_product_cat', 'wcfc_bump_cache_version' );
add_action( 'edited_product_cat', 'wcfc_bump_cache_version' );
add_action( 'delete_product_cat', 'wcfc_bump_cache_version' );

/**
 * Published, catalogue-visible product ids in a category and its descendants.
 *
 * @param int $term_id
 * @return int[]
 */
function wcfc_get_cat_product_ids( int $term_id ): array {
	$cache_key = 'wcfc_pids_v' . wcfc_cache_version() . '_' . $term_id;
	$cached    = get_transient( $cache_key );
	if ( false !== $cached ) {
		return $cached;
	}

	$cat_ids = array_merge(
		[ $term_id ],
		array_map( 'intval', (array) get_term_children( $term_id, 'product_cat' ) )
	);

	global $wpdb;
	$placeholders = implode( ',', array_fill( 0, count( $cat_ids ), '%d' ) );

	// Exclude products hidden from the catalogue (and out-of-stock ones when the
	// store is configured to hide them), so the filter matches what shoppers see.
	$hidden_tt_ids = [];
	$exclude_term  = get_term_by( 'slug', 'exclude-from-catalog', 'product_visibility' );
	if ( $exclude_term && ! is_wp_error( $exclude_term ) ) {
		$hidden_tt_ids[] = (int) $exclude_term->term_taxonomy_id;
	}
	if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
		$oos_term = get_term_by( 'slug', 'outofstock', 'product_visibility' );
		if ( $oos_term && ! is_wp_error( $oos_term ) ) {
			$hidden_tt_ids[] = (int) $oos_term->term_taxonomy_id;
		}
	}

	$visibility_sql  = '';
	$visibility_args = [];
	if ( ! empty( $hidden_tt_ids ) ) {
		$vis_placeholders = implode( ',', array_fill( 0, count( $hidden_tt_ids ), '%d' ) );
		$visibility_sql   = " AND tr.object_id NOT IN (
			SELECT object_id FROM {$wpdb->term_relationships}
			WHERE term_taxonomy_id IN ($vis_placeholders)
		)";
		$visibility_args  = $hidden_tt_ids;
	}

	// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- placeholders built above.
	$ids = $wpdb->get_col( $wpdb->prepare(
		"SELECT DISTINCT tr.object_id
		   FROM {$wpdb->term_relationships} tr
		   INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
		   INNER JOIN {$wpdb->posts} p ON tr.object_id = p.ID
		  WHERE tt.term_id IN ($placeholders)
		    AND tt.taxonomy = %s
		    AND p.post_type = %s
		    AND p.post_status = %s
		    $visibility_sql",
		...array_merge( $cat_ids, [ 'product_cat', 'product', 'publish' ], $visibility_args )
	) );
	// phpcs:enable

	$ids = array_map( 'intval', $ids ?: [] );
	set_transient( $cache_key, $ids, 6 * HOUR_IN_SECONDS );

	return $ids;
}

/**
 * Attribute term slugs that products in this set genuinely offer.
 *
 * Which source is authoritative depends on how the attribute is used:
 *
 *   - Used for variations (is_variation = 1): the published variations' meta is
 *     the truth. This drops terms the parent lists under "Values" but for which
 *     no variation exists (or whose variation was deleted) — the classic ghost
 *     value in a filter.
 *   - Product-level attribute (is_variation = 0) and simple products: the
 *     parent's term relationships are the truth.
 *   - An "Any" variation (empty meta) covers every parent term, so those
 *     parents fall back to relationships.
 *
 * @param int[]  $product_ids
 * @param string $taxonomy
 * @return string[]
 */
function wcfc_used_attr_slugs( array $product_ids, string $taxonomy ): array {
	$product_ids = array_values( array_unique( array_map( 'intval', $product_ids ) ) );
	if ( empty( $product_ids ) ) {
		return [];
	}

	static $cache = [];
	$cache_key = md5( $taxonomy . '|' . implode( ',', $product_ids ) );
	if ( isset( $cache[ $cache_key ] ) ) {
		return $cache[ $cache_key ];
	}

	global $wpdb;
	$placeholders = implode( ',', array_fill( 0, count( $product_ids ), '%d' ) );

	// (A) Every variation meta value for this attribute (empty = "Any").
	// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$variation_rows = $wpdb->get_results( $wpdb->prepare(
		"SELECT v.post_parent AS parent, pm.meta_value AS val
		   FROM {$wpdb->postmeta} pm
		   INNER JOIN {$wpdb->posts} v ON pm.post_id = v.ID
		  WHERE pm.meta_key = %s
		    AND v.post_type = 'product_variation'
		    AND v.post_status = 'publish'
		    AND v.post_parent IN ($placeholders)",
		...array_merge( [ 'attribute_' . $taxonomy ], $product_ids )
	) );

	$variation_slugs = [];
	$any_parents     = [];
	foreach ( $variation_rows as $row ) {
		if ( '' === $row->val || null === $row->val ) {
			$any_parents[ (int) $row->parent ] = true;
		} else {
			$variation_slugs[] = (string) $row->val;
		}
	}

	// Parents where THIS attribute drives variations.
	$attribute_rows = $wpdb->get_results( $wpdb->prepare(
		"SELECT post_id, meta_value
		   FROM {$wpdb->postmeta}
		  WHERE meta_key = '_product_attributes'
		    AND post_id IN ($placeholders)",
		...$product_ids
	) );
	// phpcs:enable

	$variation_attr_parents = [];
	foreach ( $attribute_rows as $row ) {
		$attrs = maybe_unserialize( $row->meta_value );
		if ( ! is_array( $attrs ) || ! isset( $attrs[ $taxonomy ] ) ) {
			continue;
		}
		if ( ! empty( $attrs[ $taxonomy ]['is_variation'] ) ) {
			$variation_attr_parents[] = (int) $row->post_id;
		}
	}

	// "Any" parents still use relationships.
	$exclude_ids = array_diff( $variation_attr_parents, array_keys( $any_parents ) );

	// (B) Relationship slugs for everything else.
	$relationship_ids   = array_values( array_diff( $product_ids, $exclude_ids ) );
	$relationship_slugs = [];
	if ( ! empty( $relationship_ids ) ) {
		$rel_placeholders = implode( ',', array_fill( 0, count( $relationship_ids ), '%d' ) );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$relationship_slugs = $wpdb->get_col( $wpdb->prepare(
			"SELECT DISTINCT t.slug
			   FROM {$wpdb->term_relationships} tr
			   INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
			   INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
			  WHERE tt.taxonomy = %s
			    AND tr.object_id IN ($rel_placeholders)",
			...array_merge( [ $taxonomy ], $relationship_ids )
		) );
	}

	$slugs = array_values( array_unique( array_merge(
		array_map( 'strval', $variation_slugs ),
		array_map( 'strval', $relationship_slugs ?: [] )
	) ) );

	$cache[ $cache_key ] = $slugs;
	return $slugs;
}

/**
 * Product count per attribute term, scoped to a product set.
 *
 * @param int[]  $product_ids
 * @param string $taxonomy
 * @return array<string, int> slug => count
 */
function wcfc_term_counts( array $product_ids, string $taxonomy ): array {
	$product_ids = array_values( array_unique( array_map( 'intval', $product_ids ) ) );
	if ( empty( $product_ids ) ) {
		return [];
	}

	static $cache = [];
	$cache_key = md5( $taxonomy . '|' . implode( ',', $product_ids ) );
	if ( isset( $cache[ $cache_key ] ) ) {
		return $cache[ $cache_key ];
	}

	global $wpdb;
	$placeholders = implode( ',', array_fill( 0, count( $product_ids ), '%d' ) );

	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$rows = $wpdb->get_results( $wpdb->prepare(
		"SELECT t.slug AS slug, COUNT(DISTINCT tr.object_id) AS cnt
		   FROM {$wpdb->term_relationships} tr
		   INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
		   INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
		  WHERE tt.taxonomy = %s
		    AND tr.object_id IN ($placeholders)
		  GROUP BY t.term_id",
		...array_merge( [ $taxonomy ], $product_ids )
	), ARRAY_A );

	$map = [];
	foreach ( (array) $rows as $row ) {
		$map[ $row['slug'] ] = (int) $row['cnt'];
	}

	$cache[ $cache_key ] = $map;
	return $map;
}

/**
 * Product ids from $base_ids linked to ANY of $slugs (OR within one attribute).
 *
 * @param int[]    $base_ids
 * @param string   $taxonomy
 * @param string[] $slugs
 * @return int[]
 */
function wcfc_ids_matching_terms( array $base_ids, string $taxonomy, array $slugs ): array {
	$base_ids = array_values( array_unique( array_map( 'intval', $base_ids ) ) );
	$slugs    = array_values( array_filter( array_map( 'strval', $slugs ) ) );
	if ( empty( $base_ids ) || empty( $slugs ) ) {
		return [];
	}

	static $cache = [];
	$cache_key = md5( $taxonomy . '|' . implode( ',', $slugs ) . '|' . implode( ',', $base_ids ) );
	if ( isset( $cache[ $cache_key ] ) ) {
		return $cache[ $cache_key ];
	}

	global $wpdb;
	$id_placeholders   = implode( ',', array_fill( 0, count( $base_ids ), '%d' ) );
	$slug_placeholders = implode( ',', array_fill( 0, count( $slugs ), '%s' ) );

	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$ids = $wpdb->get_col( $wpdb->prepare(
		"SELECT DISTINCT tr.object_id
		   FROM {$wpdb->term_relationships} tr
		   INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
		   INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
		  WHERE tt.taxonomy = %s
		    AND t.slug IN ($slug_placeholders)
		    AND tr.object_id IN ($id_placeholders)",
		...array_merge( [ $taxonomy ], $slugs, $base_ids )
	) );

	$ids = array_map( 'intval', $ids ?: [] );
	$cache[ $cache_key ] = $ids;
	return $ids;
}

/**
 * Product ids from $base_ids whose _price falls in a range.
 *
 * @param int[]      $base_ids
 * @param float|null $min
 * @param float|null $max
 * @return int[]
 */
function wcfc_ids_in_price_range( array $base_ids, $min, $max ): array {
	$base_ids = array_values( array_unique( array_map( 'intval', $base_ids ) ) );
	if ( empty( $base_ids ) ) {
		return [];
	}

	$min = null !== $min ? (float) $min : 0;
	$max = null !== $max ? (float) $max : PHP_INT_MAX;

	global $wpdb;
	$placeholders = implode( ',', array_fill( 0, count( $base_ids ), '%d' ) );

	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$ids = $wpdb->get_col( $wpdb->prepare(
		"SELECT post_id FROM {$wpdb->postmeta}
		  WHERE meta_key = '_price'
		    AND CAST(meta_value AS DECIMAL(15,4)) BETWEEN %f AND %f
		    AND post_id IN ($placeholders)",
		...array_merge( [ $min, $max ], $base_ids )
	) );

	return array_map( 'intval', $ids ?: [] );
}

/**
 * Live facet counts using self-exclusion.
 *
 * Each facet is counted over the products satisfying every OTHER active filter
 * but not itself. That is what keeps multi-select usable: ticking "red" must not
 * zero out "blue" in the same colour facet.
 *
 * @param int        $cat_term_id Category providing the base set.
 * @param array      $selected    [ taxonomy => [ slugs ] ] currently active.
 * @param float|null $min_price
 * @param float|null $max_price
 * @param string[]   $facet_attrs Attributes rendered in the sidebar.
 * @return array<string, array<string, int>> taxonomy => [ slug => count ]
 */
function wcfc_compute_facets( int $cat_term_id, array $selected, $min_price, $max_price, array $facet_attrs ): array {
	$base = wcfc_get_cat_product_ids( $cat_term_id );
	if ( empty( $base ) ) {
		return [];
	}

	$signature = wp_json_encode( [ $selected, $min_price, $max_price, $facet_attrs ] );
	$cache_key = 'wcfc_facets_v' . wcfc_cache_version() . '_' . md5( $cat_term_id . '|' . $signature );
	$cached    = get_transient( $cache_key );
	if ( false !== $cached ) {
		return $cached;
	}

	$match_sets = [];
	foreach ( $selected as $taxonomy => $slugs ) {
		$match_sets[ $taxonomy ] = wcfc_ids_matching_terms( $base, $taxonomy, (array) $slugs );
	}

	$price_active = ( null !== $min_price || null !== $max_price );
	$price_set    = $price_active ? wcfc_ids_in_price_range( $base, $min_price, $max_price ) : null;

	$facets = [];
	foreach ( $facet_attrs as $facet ) {
		$ids = $base;
		foreach ( $match_sets as $taxonomy => $set ) {
			if ( $taxonomy === $facet ) {
				continue;   // self-exclusion
			}
			$ids = array_intersect( $ids, $set );
			if ( empty( $ids ) ) {
				break;
			}
		}
		if ( ! empty( $ids ) && null !== $price_set ) {
			$ids = array_intersect( $ids, $price_set );
		}
		$facets[ $facet ] = empty( $ids ) ? [] : wcfc_term_counts( array_values( $ids ), $facet );
	}

	set_transient( $cache_key, $facets, 10 * MINUTE_IN_SECONDS );
	return $facets;
}

/**
 * Highest product price in the store, rounded up — the price slider's ceiling.
 *
 * @return int
 */
function wcfc_max_price(): int {
	$cache_key = 'wcfc_max_price_v' . wcfc_cache_version();
	$cached    = get_transient( $cache_key );
	if ( false !== $cached ) {
		return (int) $cached;
	}

	global $wpdb;
	$max = (int) $wpdb->get_var( $wpdb->prepare(
		"SELECT MAX(CAST(meta_value AS UNSIGNED)) FROM {$wpdb->postmeta}
		  WHERE meta_key = %s AND meta_value != %s",
		'_price',
		''
	) );

	$rounded = $max > 0 ? (int) ceil( $max / 1000 ) * 1000 : 1000;
	$rounded = (int) apply_filters( 'wcfc_max_price', $rounded, $max );

	set_transient( $cache_key, $rounded, 6 * HOUR_IN_SECONDS );
	return $rounded;
}
