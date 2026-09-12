<?php
/**
 * Configuration storage, context resolution and sanitisation.
 *
 * The whole configuration lives in one option, keyed by "context":
 *
 *   'global'     filters prepended to every archive
 *   '<term_id>'  filters for one product category (numeric term id, as a string)
 *   'default'    fallback when no category-specific config matches
 *
 * Category keys are term ids on purpose: a term id is permanent, while a slug
 * changes the moment somebody renames the category in the admin, which would
 * silently drop that category's configuration.
 *
 * @package WC_Filter_Configurator
 */

defined( 'ABSPATH' ) || exit;

/**
 * Default configuration, used until something is saved through the admin UI.
 *
 * Deliberately minimal and store-agnostic. Override it for your project:
 *
 *   add_filter( 'wcfc_default_configs', function ( $configs ) {
 *       $configs['default'] = [
 *           [ 'attr' => 'pa_color', 'label' => 'Colour', 'type' => 'swatch', 'collapsed' => false ],
 *       ];
 *       return $configs;
 *   } );
 *
 * @return array<string, array>
 */
function wcfc_default_configs(): array {
	$configs = [
		'global'  => [
			[
				'attr'      => 'price_slider',
				'label'     => __( 'Price', 'wc-filter-configurator' ),
				'type'      => 'price_slider',
				'collapsed' => false,
			],
		],
		'default' => [
			[
				'attr'      => 'product_brand',
				'label'     => __( 'Brand', 'wc-filter-configurator' ),
				'type'      => 'checkbox',
				'collapsed' => false,
			],
		],
	];

	return (array) apply_filters( 'wcfc_default_configs', $configs );
}

/**
 * The full stored configuration, falling back to the defaults.
 *
 * @return array<string, array>
 */
function wcfc_get_configs(): array {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}

	$configs = get_option( WCFC_OPTION, [] );
	if ( ! is_array( $configs ) || empty( $configs ) ) {
		$configs = wcfc_default_configs();
	}

	$cache = $configs;
	return $cache;
}

/**
 * Contexts offered as tabs in the admin UI.
 *
 * By default: every top-level product category, plus 'default'. Add your own
 * (for a custom landing page, say) with the filter, then render that page with
 * wcfc_render_sidebar( [ 'context' => 'my-landing' ] ).
 *
 * @return array<string, string> context key => display name
 */
function wcfc_get_contexts(): array {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}

	$contexts = [];

	$tops = get_terms( [
		'taxonomy'   => 'product_cat',
		'parent'     => 0,
		'hide_empty' => false,
		'orderby'    => 'name',
		'order'      => 'ASC',
	] );

	if ( ! is_wp_error( $tops ) ) {
		$uncategorized = absint( get_option( 'default_product_cat' ) );
		foreach ( $tops as $term ) {
			if ( $uncategorized && (int) $term->term_id === $uncategorized ) {
				continue;
			}
			$contexts[ (string) $term->term_id ] = $term->name;
		}
	}

	$contexts['default'] = __( 'Default (fallback)', 'wc-filter-configurator' );

	$cache = (array) apply_filters( 'wcfc_config_contexts', $contexts );
	return $cache;
}

/**
 * Resolve which filter list applies to a context.
 *
 * Resolution order:
 *   1. exact context key (term id, or a custom key)
 *   2. nearest ancestor category that has a config
 *   3. 'default'
 *   4. per-filter category scoping is applied (category contexts only)
 *   5. 'global' filters are prepended, skipping attributes already present
 *
 * @param int|string|null $context Term id, custom context key, or null for the fallback.
 * @return array
 */
function wcfc_get_filters_for_context( $context = null ): array {
	$configs = wcfc_get_configs();
	$filters = null;
	$term    = null;

	if ( is_numeric( $context ) ) {
		$term = get_term( (int) $context, 'product_cat' );
		if ( is_wp_error( $term ) ) {
			$term = null;
		}
	}

	// 1. Exact key.
	if ( null !== $context && isset( $configs[ (string) $context ] ) ) {
		$filters = $configs[ (string) $context ];
	} elseif ( $term ) {
		// 2. Nearest configured ancestor.
		foreach ( get_ancestors( $term->term_id, 'product_cat', 'taxonomy' ) as $ancestor_id ) {
			if ( isset( $configs[ (string) $ancestor_id ] ) ) {
				$filters = $configs[ (string) $ancestor_id ];
				break;
			}
		}
	}

	// 3. Fallback.
	if ( null === $filters ) {
		$filters = $configs['default'] ?? [];
	}

	// 4. Per-filter category scoping — only meaningful on a real category.
	if ( $term ) {
		$filters = wcfc_apply_category_scope( $filters, $term->slug );
	}

	// 5. Prepend globals, skipping duplicates.
	$global = $configs['global'] ?? [];
	if ( ! empty( $global ) && 'global' !== (string) $context ) {
		$present   = array_flip( array_filter( array_column( $filters, 'attr' ) ) );
		$to_prepend = [];
		foreach ( $global as $g ) {
			$attr = $g['attr'] ?? '';
			if ( $attr && isset( $present[ $attr ] ) ) {
				continue;
			}
			$to_prepend[] = $g;
		}
		$filters = array_merge( $to_prepend, $filters );
	}

	return (array) apply_filters( 'wcfc_filters_for_context', $filters, $context );
}

/**
 * Drop filters whose category scope excludes the current category.
 * Sections are filtered recursively and removed when they end up empty.
 *
 * @param array  $filters
 * @param string $cat_slug
 * @return array
 */
function wcfc_apply_category_scope( array $filters, string $cat_slug ): array {
	$result = [];

	foreach ( $filters as $f ) {
		if ( ( $f['type'] ?? '' ) === 'section' ) {
			$inner = [];
			foreach ( ( $f['filters'] ?? [] ) as $sf ) {
				if ( wcfc_category_matches( $cat_slug, $sf['categories'] ?? [] ) ) {
					$inner[] = $sf;
				}
			}
			if ( ! empty( $inner ) ) {
				$f['filters'] = $inner;
				$result[]     = $f;
			}
			continue;
		}

		if ( wcfc_category_matches( $cat_slug, $f['categories'] ?? [] ) ) {
			$result[] = $f;
		}
	}

	return $result;
}

/**
 * Does the current category satisfy a filter's category scope?
 * An empty scope means "show everywhere". A category also matches when one of
 * its ancestors is listed, so scoping a filter to a parent covers its children.
 *
 * @param string $current_slug
 * @param array  $allowed
 * @return bool
 */
function wcfc_category_matches( string $current_slug, $allowed ): bool {
	$allowed = (array) $allowed;
	if ( empty( $allowed ) ) {
		return true;
	}
	if ( in_array( $current_slug, $allowed, true ) ) {
		return true;
	}

	$term = get_term_by( 'slug', $current_slug, 'product_cat' );
	if ( ! $term || is_wp_error( $term ) ) {
		return false;
	}

	foreach ( get_ancestors( $term->term_id, 'product_cat', 'taxonomy' ) as $ancestor_id ) {
		$ancestor = get_term( $ancestor_id, 'product_cat' );
		if ( $ancestor && ! is_wp_error( $ancestor ) && in_array( $ancestor->slug, $allowed, true ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Flat list of taxonomy slugs rendered for a context, for faceting.
 * Flattens sections and skips the special non-taxonomy filters.
 *
 * @param int|string|null $context
 * @return string[]
 */
function wcfc_attrs_for_context( $context = null ): array {
	$filters = wcfc_get_filters_for_context( $context );
	$skip    = wcfc_special_attrs();
	$attrs   = [];

	foreach ( $filters as $f ) {
		if ( ( $f['type'] ?? '' ) === 'section' ) {
			foreach ( ( $f['filters'] ?? [] ) as $sf ) {
				$a = $sf['attr'] ?? '';
				if ( $a && ! isset( $skip[ $a ] ) ) {
					$attrs[ $a ] = true;
				}
			}
			continue;
		}
		$a = $f['attr'] ?? '';
		if ( $a && ! isset( $skip[ $a ] ) ) {
			$attrs[ $a ] = true;
		}
	}

	return array_keys( $attrs );
}

/**
 * Recursively sanitise a filter list coming from the admin UI.
 *
 * @param mixed $filters
 * @return array
 */
function wcfc_sanitize_filters( $filters ): array {
	if ( ! is_array( $filters ) ) {
		return [];
	}

	$allowed_types = wcfc_allowed_types();
	$specials      = wcfc_special_attrs();
	$clean         = [];

	foreach ( $filters as $f ) {
		if ( ! is_array( $f ) ) {
			continue;
		}

		// Section.
		if ( ( $f['type'] ?? '' ) === 'section' ) {
			$clean[] = [
				'type'      => 'section',
				'label'     => sanitize_text_field( $f['label'] ?? __( 'Section', 'wc-filter-configurator' ) ),
				'collapsed' => wcfc_to_bool( $f['collapsed'] ?? false ),
				'filters'   => wcfc_sanitize_filters( $f['filters'] ?? [] ),
			];
			continue;
		}

		$attr = sanitize_key( $f['attr'] ?? '' );
		if ( '' === $attr ) {
			continue;
		}

		// Special attributes have exactly one rendering; force it regardless of
		// what the browser posted.
		if ( isset( $specials[ $attr ] ) ) {
			$type = $specials[ $attr ];
		} else {
			$type = in_array( $f['type'] ?? '', $allowed_types, true ) ? $f['type'] : 'checkbox';
		}

		$cats = array_values( array_filter( array_map( 'sanitize_key', (array) ( $f['categories'] ?? [] ) ) ) );

		$clean[] = [
			'attr'       => $attr,
			'label'      => sanitize_text_field( $f['label'] ?? $attr ),
			'type'       => $type,
			'collapsed'  => wcfc_to_bool( $f['collapsed'] ?? false ),
			'categories' => $cats,
		];
	}

	return $clean;
}

/**
 * Booleanise a value that may arrive as the string "false" from JSON/FormData.
 *
 * @param mixed $value
 * @return bool
 */
function wcfc_to_bool( $value ): bool {
	if ( is_string( $value ) ) {
		return ! in_array( strtolower( $value ), [ '', '0', 'false', 'no' ], true );
	}
	return (bool) $value;
}

/**
 * Control types a normal attribute filter may use.
 *
 * @return string[]
 */
function wcfc_allowed_types(): array {
	return [ 'swatch', 'checkbox' ];
}

/**
 * Non-taxonomy filters, mapped to their fixed control type.
 * Their type is not editable in the UI and is forced on save.
 *
 * @return array<string, string> attr => type
 */
function wcfc_special_attrs(): array {
	return [
		'price_slider' => 'price_slider',
		'product_cat'  => 'category_tree',
	];
}
