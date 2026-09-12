<?php
/**
 * The pool of attributes an archive filter can be built from, plus the one-off
 * slug normalisation migration.
 *
 * @package WC_Filter_Configurator
 */

defined( 'ABSPATH' ) || exit;

/**
 * Every attribute available to the configurator: the special filters first,
 * then all registered WooCommerce product attributes, then the brand taxonomy.
 *
 * @return array<string, string> slug => human label
 */
function wcfc_get_all_attributes(): array {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}

	$attrs = [
		'price_slider' => __( 'Price (slider)', 'wc-filter-configurator' ),
		'product_cat'  => __( 'Category (tree)', 'wc-filter-configurator' ),
	];

	if ( function_exists( 'wc_get_attribute_taxonomies' ) ) {
		foreach ( wc_get_attribute_taxonomies() as $attr ) {
			$slug           = wc_attribute_taxonomy_name( $attr->attribute_name );
			$attrs[ $slug ] = $attr->attribute_label ?: $attr->attribute_name;
		}
	}

	$brand_tax = wcfc_brand_taxonomy();
	if ( $brand_tax && taxonomy_exists( $brand_tax ) ) {
		$attrs[ $brand_tax ] = __( 'Brand', 'wc-filter-configurator' );
	}

	$cache = (array) apply_filters( 'wcfc_all_attributes', $attrs );
	return $cache;
}

/**
 * Brand taxonomy name. WooCommerce 9.4+ ships `product_brand`; older stores use
 * a plugin taxonomy such as `pwb-brand` or `yith_product_brand`.
 *
 * @return string
 */
function wcfc_brand_taxonomy(): string {
	return (string) apply_filters( 'wcfc_brand_taxonomy', 'product_brand' );
}

/**
 * Categories offered in the per-filter scope modal for one context: the context
 * category itself plus its whole descendant tree, flattened with a depth marker.
 *
 * @return array<string, array<int, array{slug: string, name: string, depth: int}>>
 */
function wcfc_get_context_category_tree(): array {
	$result = [];

	foreach ( wcfc_get_contexts() as $key => $name ) {
		if ( ! is_numeric( $key ) ) {
			continue;   // 'default' and custom contexts have no category tree.
		}

		$parent = get_term( (int) $key, 'product_cat' );
		if ( ! $parent || is_wp_error( $parent ) ) {
			continue;
		}

		$items = [ [ 'slug' => $parent->slug, 'name' => $parent->name, 'depth' => 0 ] ];

		$children = get_terms( [
			'taxonomy'   => 'product_cat',
			'child_of'   => $parent->term_id,
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
		] );

		if ( ! is_wp_error( $children ) && ! empty( $children ) ) {
			$by_parent = [];
			foreach ( $children as $child ) {
				$by_parent[ $child->parent ][] = $child;
			}
			$walk = function ( $parent_id, $depth ) use ( &$walk, &$by_parent, &$items ) {
				if ( empty( $by_parent[ $parent_id ] ) ) {
					return;
				}
				foreach ( $by_parent[ $parent_id ] as $child ) {
					$items[] = [ 'slug' => $child->slug, 'name' => $child->name, 'depth' => $depth ];
					$walk( $child->term_id, $depth + 1 );
				}
			};
			$walk( $parent->term_id, 1 );
		}

		$result[ $key ] = $items;
	}

	return $result;
}

/**
 * Normalise attribute slugs that were written with underscores where
 * WooCommerce actually registered a dash.
 *
 * WooCommerce slugifies a multi-word attribute name with dashes: "surface
 * finish" becomes pa_surface-finish, not pa_surface_finish. Hand-written config
 * (or config imported from another store) routinely gets this wrong, and the
 * filter then silently renders nothing. This rewrites any attr that does not
 * exist to its dashed variant when that one does.
 *
 * Runs once per schema version, not on every request.
 */
function wcfc_maybe_normalize_slugs() {
	if ( (int) get_option( 'wcfc_db_ver', 0 ) >= WCFC_DB_VERSION ) {
		return;
	}

	$all = wcfc_get_all_attributes();
	if ( empty( $all ) ) {
		return;   // WooCommerce not ready yet; try again next request.
	}

	$configs = get_option( WCFC_OPTION, null );
	if ( is_array( $configs ) ) {
		$changed = false;
		foreach ( $configs as &$filters ) {
			if ( ! is_array( $filters ) ) {
				continue;
			}
			foreach ( $filters as &$f ) {
				if ( ( $f['type'] ?? '' ) === 'section' ) {
					foreach ( ( $f['filters'] ?? [] ) as &$sf ) {
						if ( wcfc_normalize_attr_ref( $sf['attr'], $all ) ) {
							$changed = true;
						}
					}
					unset( $sf );
					continue;
				}
				if ( wcfc_normalize_attr_ref( $f['attr'], $all ) ) {
					$changed = true;
				}
			}
			unset( $f );
		}
		unset( $filters );

		if ( $changed ) {
			update_option( WCFC_OPTION, $configs, false );
		}
	}

	$groups = get_option( WCFC_GROUPS_OPTION, [] );
	if ( is_array( $groups ) && ! empty( $groups ) ) {
		$changed = false;
		foreach ( $groups as &$group ) {
			$attrs = (array) ( $group['attrs'] ?? [] );
			foreach ( $attrs as &$attr ) {
				if ( wcfc_normalize_attr_ref( $attr, $all ) ) {
					$changed = true;
				}
			}
			unset( $attr );
			$group['attrs'] = $attrs;
		}
		unset( $group );

		if ( $changed ) {
			update_option( WCFC_GROUPS_OPTION, $groups, false );
		}
	}

	update_option( 'wcfc_db_ver', WCFC_DB_VERSION, false );
}
add_action( 'admin_init', 'wcfc_maybe_normalize_slugs', 20 );

/**
 * Rewrite one attribute reference in place if a dashed variant exists.
 *
 * @param string|null $attr Passed by reference.
 * @param array       $all  Known attributes.
 * @return bool Whether it was rewritten.
 */
function wcfc_normalize_attr_ref( &$attr, array $all ): bool {
	if ( empty( $attr ) || isset( $all[ $attr ] ) ) {
		return false;
	}
	if ( 0 !== strncmp( $attr, 'pa_', 3 ) ) {
		return false;
	}

	$dashed = 'pa_' . str_replace( '_', '-', substr( $attr, 3 ) );
	if ( isset( $all[ $dashed ] ) ) {
		$attr = $dashed;
		return true;
	}

	return false;
}
