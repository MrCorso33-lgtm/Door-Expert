<?php
/**
 * Front-end renderer for the configured filter sidebar.
 *
 * The theme calls one function:
 *
 *   wcfc_render_sidebar();                              // auto-detects the category
 *   wcfc_render_sidebar( [ 'context' => 'my-landing' ] );
 *   $html = wcfc_render_sidebar( [ 'echo' => false ] );
 *
 * Markup is intentionally plain and fully documented in README.md, so a theme
 * can style it directly, or replace any filter's markup through the
 * `wcfc_pre_render_filter` / `wcfc_filter_html` hooks.
 *
 * @package WC_Filter_Configurator
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render the whole sidebar.
 *
 * @param array $args {
 *     @type int|string|null $context Context key. Default: current category, else 'default'.
 *     @type bool            $echo    Echo or return. Default true.
 *     @type bool            $scope   Scope terms and counts to the category's products. Default true.
 * }
 * @return string|void
 */
function wcfc_render_sidebar( array $args = [] ) {
	$args = wp_parse_args( $args, [
		'context' => null,
		'echo'    => true,
		'scope'   => true,
	] );

	if ( null === $args['context'] ) {
		$args['context'] = wcfc_current_context();
	}

	$filters = wcfc_get_filters_for_context( $args['context'] );

	// Product ids used to scope terms and counts.
	//   null = no category context (e.g. /shop/) -> fall back to hide_empty
	//   []   = category exists but is empty      -> render nothing
	$product_ids = null;
	if ( $args['scope'] && is_numeric( $args['context'] ) ) {
		$product_ids = wcfc_get_cat_product_ids( (int) $args['context'] );
	}

	$html = '';
	foreach ( $filters as $filter ) {
		if ( ( $filter['type'] ?? '' ) === 'section' ) {
			$html .= wcfc_render_section( $filter, $product_ids );
			continue;
		}
		$html .= wcfc_render_filter( $filter, $product_ids );
	}

	$html = (string) apply_filters( 'wcfc_sidebar_html', $html, $filters, $args );

	if ( $args['echo'] ) {
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped per filter below.
		return;
	}

	return $html;
}

/**
 * The context key for the current request.
 *
 * @return int|string
 */
function wcfc_current_context() {
	if ( function_exists( 'is_product_category' ) && is_product_category() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			return $term->term_id;
		}
	}
	return 'default';
}

/**
 * Render a section wrapper. Empty sections (everything inside was scoped out or
 * had no terms) are dropped entirely rather than left as a bare heading.
 *
 * @param array      $section
 * @param int[]|null $product_ids
 * @return string
 */
function wcfc_render_section( array $section, $product_ids ): string {
	$body = '';
	foreach ( ( $section['filters'] ?? [] ) as $filter ) {
		$body .= wcfc_render_filter( $filter, $product_ids );
	}

	if ( '' === trim( $body ) ) {
		return '';
	}

	$classes = 'wcfc-section' . ( ! empty( $section['collapsed'] ) ? ' is-collapsed' : '' );

	return sprintf(
		'<div class="%1$s"><h3 class="wcfc-section__title">%2$s<span class="wcfc-arrow" aria-hidden="true"></span></h3><div class="wcfc-section__body">%3$s</div></div>',
		esc_attr( $classes ),
		esc_html( $section['label'] ?? '' ),
		$body
	);
}

/**
 * Render one filter group.
 *
 * @param array      $filter
 * @param int[]|null $product_ids
 * @return string
 */
function wcfc_render_filter( array $filter, $product_ids ): string {
	$attr = $filter['attr'] ?? '';
	if ( '' === $attr ) {
		return '';
	}

	// Full override hook — return a string to replace this filter's markup.
	$pre = apply_filters( 'wcfc_pre_render_filter', null, $filter, $product_ids );
	if ( null !== $pre ) {
		return (string) $pre;
	}

	if ( 'price_slider' === $attr ) {
		$html = wcfc_render_price_filter( $filter );
	} elseif ( 'product_cat' === $attr ) {
		$html = wcfc_render_category_filter( $filter );
	} else {
		$html = wcfc_render_term_filter( $filter, $product_ids );
	}

	return (string) apply_filters( 'wcfc_filter_html', $html, $filter, $product_ids );
}

/**
 * Open the standard group wrapper.
 *
 * @param array  $filter
 * @param string $modifier Extra BEM modifier, e.g. 'price'.
 * @return string
 */
function wcfc_group_open( array $filter, string $modifier ): string {
	$classes = 'wcfc-group wcfc-group--' . $modifier;
	if ( ! empty( $filter['collapsed'] ) ) {
		$classes .= ' is-collapsed';
	}

	return sprintf(
		'<div class="%1$s" data-attr="%2$s"><h3 class="wcfc-group__title">%3$s<span class="wcfc-arrow" aria-hidden="true"></span></h3><div class="wcfc-group__options">',
		esc_attr( $classes ),
		esc_attr( $filter['attr'] ?? '' ),
		esc_html( $filter['label'] ?? '' )
	);
}

/**
 * Close the standard group wrapper.
 *
 * @return string
 */
function wcfc_group_close(): string {
	return '</div></div>';
}

/**
 * Dual-range price slider.
 *
 * @param array $filter
 * @return string
 */
function wcfc_render_price_filter( array $filter ): string {
	$max      = wcfc_max_price();
	$step     = (int) apply_filters( 'wcfc_price_step', max( 1, (int) round( $max / 1000 ) ) );
	$currency = function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '';

	$html  = wcfc_group_open( $filter, 'price' );
	$html .= '<div class="wcfc-price">';
	$html .= '<div class="wcfc-price__track"><div class="wcfc-price__fill" id="wcfc-price-fill"></div>';
	$html .= sprintf(
		'<input type="range" id="wcfc-price-min" class="wcfc-price__input wcfc-price__input--min" min="0" max="%1$d" value="0" step="%2$d" aria-label="%3$s">',
		$max,
		$step,
		esc_attr__( 'Minimum price', 'wc-filter-configurator' )
	);
	$html .= sprintf(
		'<input type="range" id="wcfc-price-max" class="wcfc-price__input wcfc-price__input--max" min="0" max="%1$d" value="%1$d" step="%2$d" aria-label="%3$s">',
		$max,
		$step,
		esc_attr__( 'Maximum price', 'wc-filter-configurator' )
	);
	$html .= '</div>';
	$html .= sprintf(
		'<div class="wcfc-price__labels"><span id="wcfc-price-min-label">0 %1$s</span><span id="wcfc-price-max-label">%2$s %1$s</span></div>',
		esc_html( $currency ),
		esc_html( number_format_i18n( $max ) )
	);
	$html .= '</div>';
	$html .= wcfc_group_close();

	return $html;
}

/**
 * Hierarchical category tree. Rendered as links, not checkboxes: navigating to a
 * category is a different action from narrowing within one.
 *
 * @param array $filter
 * @return string
 */
function wcfc_render_category_filter( array $filter ): string {
	$uncategorized = absint( get_option( 'default_product_cat' ) );

	$terms = get_terms( [
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'orderby'    => 'name',
		'order'      => 'ASC',
		'exclude'    => $uncategorized ? [ $uncategorized ] : [],
	] );

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return '';
	}

	$tree = [];
	foreach ( $terms as $term ) {
		$tree[ $term->parent ][] = $term;
	}

	$body = wcfc_render_category_branch( $tree, 0, 0 );
	if ( '' === $body ) {
		return '';
	}

	return wcfc_group_open( $filter, 'category' ) . $body . wcfc_group_close();
}

/**
 * One level of the category tree. Parents omit the count: their number would
 * include children and read as inconsistent next to the child rows.
 *
 * @param array $tree
 * @param int   $parent_id
 * @param int   $depth
 * @return string
 */
function wcfc_render_category_branch( array $tree, int $parent_id, int $depth ): string {
	if ( empty( $tree[ $parent_id ] ) ) {
		return '';
	}

	$html = '';
	foreach ( $tree[ $parent_id ] as $term ) {
		$has_children = ! empty( $tree[ $term->term_id ] );
		$indent       = $depth > 0 ? sprintf( ' style="padding-left:%dpx"', $depth * 14 ) : '';

		$html .= sprintf(
			'<a class="wcfc-option wcfc-option--link" href="%1$s"%2$s><span>%3$s</span>%4$s</a>',
			esc_url( get_term_link( $term ) ),
			$indent,
			esc_html( $term->name ),
			$has_children ? '' : sprintf( '<small class="wcfc-count">(%d)</small>', absint( $term->count ) )
		);

		$html .= wcfc_render_category_branch( $tree, $term->term_id, $depth + 1 );
	}

	return $html;
}

/**
 * Taxonomy filter: checkboxes, or colour swatches when type is 'swatch'.
 *
 * @param array      $filter
 * @param int[]|null $product_ids
 * @return string
 */
function wcfc_render_term_filter( array $filter, $product_ids ): string {
	$taxonomy = $filter['attr'];

	if ( ! taxonomy_exists( $taxonomy ) ) {
		return '';
	}

	// Category exists but holds no products — nothing to offer.
	if ( is_array( $product_ids ) && empty( $product_ids ) ) {
		return '';
	}

	$term_args = [
		'taxonomy' => $taxonomy,
		'orderby'  => 'name',
		'order'    => 'ASC',
	];
	if ( is_array( $product_ids ) ) {
		$term_args['object_ids'] = $product_ids;
	} else {
		$term_args['hide_empty'] = true;
	}

	$terms = get_terms( $term_args );
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return '';
	}

	// get_terms( object_ids ) also returns terms a variable product's parent
	// carries but no published variation actually uses. Drop those.
	if ( is_array( $product_ids ) && 0 === strpos( $taxonomy, 'pa_' ) ) {
		$used  = wcfc_used_attr_slugs( $product_ids, $taxonomy );
		$terms = array_values( array_filter( $terms, function ( $term ) use ( $used ) {
			return in_array( $term->slug, $used, true );
		} ) );
		if ( empty( $terms ) ) {
			return '';
		}
	}

	$counts = is_array( $product_ids ) ? wcfc_term_counts( $product_ids, $taxonomy ) : [];

	$is_swatch = ( 'swatch' === ( $filter['type'] ?? '' ) );
	$color_map = $is_swatch ? (array) apply_filters( 'wcfc_swatch_color_map', [], $taxonomy ) : [];

	// A swatch without colours would render as blank circles, so fall back to
	// checkboxes instead of showing something unusable.
	if ( $is_swatch && empty( $color_map ) ) {
		$is_swatch = false;
	}

	$html = wcfc_group_open( $filter, $is_swatch ? 'swatch' : 'checkbox' );

	if ( $is_swatch ) {
		$light_slugs = (array) apply_filters( 'wcfc_swatch_light_slugs', [], $taxonomy );
		$html       .= '<div class="wcfc-swatches">';
		foreach ( $terms as $term ) {
			$hex   = $color_map[ $term->slug ] ?? '#cccccc';
			$count = $counts[ $term->slug ] ?? $term->count;
			$html .= sprintf(
				'<button type="button" class="wcfc-swatch%1$s" data-attr="%2$s" data-value="%3$s" data-count="%4$d" style="--wcfc-swatch-color:%5$s" title="%6$s" aria-label="%6$s"></button>',
				in_array( $term->slug, $light_slugs, true ) ? ' wcfc-swatch--light' : '',
				esc_attr( $taxonomy ),
				esc_attr( $term->slug ),
				absint( $count ),
				esc_attr( $hex ),
				esc_attr( $term->name )
			);
		}
		$html .= '</div>';
	} else {
		foreach ( $terms as $term ) {
			$count = $counts[ $term->slug ] ?? $term->count;
			$html .= sprintf(
				'<label class="wcfc-option"><input type="checkbox" name="%1$s" value="%2$s"><span>%3$s</span><small class="wcfc-count">(%4$d)</small></label>',
				esc_attr( $taxonomy ),
				esc_attr( $term->slug ),
				esc_html( $term->name ),
				absint( $count )
			);
		}
	}

	$html .= wcfc_group_close();

	return $html;
}
