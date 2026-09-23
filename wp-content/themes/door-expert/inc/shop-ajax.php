<?php
/**
 * AJAX filtriranje listinga (prodavnica + kategorijske arhive).
 *
 * JEDAN IZVOR ISTINE ZA UPIT. Handler ne gradi svoj tax_query/meta_query, nego
 * hidratiše $_GET iz poslatog query stringa i pozove iste funkcije koje rade i pri
 * običnom učitavanju stranice (door_expert_shop_tax_query / door_expert_shop_meta_query,
 * sortiranje kroz WooCommerce). Isto važi za markup: vraća door_expert_shop_results(),
 * isti onaj koji šabloni ispisuju. Dvije implementacije istog filtriranja neizbježno
 * se raziđu, a razlika se vidi tek kao "AJAX daje druge rezultate nego reload".
 *
 * Bez JavaScripta sve radi kao i do sad: forma je i dalje GET forma, URL-ovi su i dalje
 * dijeljivi, a SEO sloj (inc/filters-seo.php) ima šta da kanonikalizuje.
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Naziv AJAX akcije i nonce-a (jedno mjesto, koristi ga i localize i handler).
 */
function door_expert_shop_ajax_action() {
	return 'door_expert_shop_filter';
}

add_action( 'wp_enqueue_scripts', 'door_expert_shop_ajax_data', 20 );
/**
 * Podaci za prodavnica.js. Kači se na skriptu koja je već enqueue-ovana (prodavnica
 * i kategorije), pa nema duplikata i nema potrebe ponavljati uslove iz functions.php.
 */
function door_expert_shop_ajax_data() {
	if ( ! wp_script_is( 'door-expert-prodavnica-js', 'enqueued' ) ) {
		return;
	}

	$term_id = 0;
	if ( function_exists( 'is_product_category' ) && is_product_category() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$term_id = (int) $term->term_id;
		}
	}

	wp_localize_script(
		'door-expert-prodavnica-js',
		'doorExpertShop',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'action'  => door_expert_shop_ajax_action(),
			'nonce'   => wp_create_nonce( door_expert_shop_ajax_action() ),
			'catId'   => $term_id,
			'base'    => door_expert_listing_base_url(),
		)
	);
}

add_action( 'wp_ajax_door_expert_shop_filter', 'door_expert_shop_ajax_filter' );
add_action( 'wp_ajax_nopriv_door_expert_shop_filter', 'door_expert_shop_ajax_filter' );
/**
 * Vrati filtriran grid, brojač, paginaciju i facete za poslati query string.
 */
function door_expert_shop_ajax_filter() {
	check_ajax_referer( door_expert_shop_ajax_action(), 'nonce' );

	// Limit je namjerno visok: svaka promjena filtera je jedan zahtjev, pa normalan
	// kupac lako napravi nekoliko desetina u minutu. Ovo hvata samo skriptu.
	if ( function_exists( 'door_expert_rate_limit' ) && ! door_expert_rate_limit( 'de_rl_filter_', 90, MINUTE_IN_SECONDS ) ) {
		wp_send_json_error( array( 'message' => 'Previše zahtjeva. Sačekajte trenutak.' ), 429 );
	}

	$query_string = isset( $_POST['query'] ) ? wp_unslash( $_POST['query'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- razlaže se u $_GET, gdje ga sanitizuju door_expert_shop_selected()/_price().
	$cat_id       = isset( $_POST['cat'] ) ? absint( $_POST['cat'] ) : 0;
	$paged        = isset( $_POST['paged'] ) ? max( 1, absint( $_POST['paged'] ) ) : 1;

	$parsed = array();
	parse_str( (string) $query_string, $parsed );

	$original_get = $_GET;
	$_GET         = is_array( $parsed ) ? $parsed : array();

	/*
	 * Kategorijski listing: kategorija NIJE u formi (ta grupa je sakrivena jer smo već
	 * unutar nje), a upit je mora dobiti. Kroz f_cat je dobiju i upit
	 * (door_expert_shop_tax_query, sa include_children) i faceting
	 * (door_expert_filter_facets) – bez posebne grane koda za kategorije.
	 */
	$base_url = door_expert_shop_base_url();

	if ( $cat_id > 0 ) {
		$term = get_term( $cat_id, 'product_cat' );

		if ( $term instanceof WP_Term ) {
			$_GET['f_cat'] = array( $term->slug );

			$link = get_term_link( $term );
			if ( ! is_wp_error( $link ) ) {
				$base_url = $link;
			}
		}
	}

	$tax_query = array();

	$visibility = door_expert_shop_visibility_clause();
	if ( ! empty( $visibility ) ) {
		$tax_query[] = $visibility;
	}

	$args = array(
		'post_type'           => 'product',
		'post_status'         => 'publish',
		'posts_per_page'      => door_expert_shop_per_page(),
		'paged'               => $paged,
		'ignore_sticky_posts' => true,
		'tax_query'           => door_expert_shop_tax_query( $tax_query, null ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- isto kao glavni upit arhive.
		'meta_query'          => door_expert_shop_meta_query( array() ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- isto kao glavni upit arhive.
	);

	// Sortiranje radi WooCommerce, iz istog ?orderby= koji je sad u $_GET.
	if ( function_exists( 'WC' ) && isset( WC()->query ) && method_exists( WC()->query, 'get_catalog_ordering_args' ) ) {
		$args = array_merge( $args, WC()->query->get_catalog_ordering_args() );
	}

	$listing = new WP_Query( $args );

	$payload = array(
		'results'  => door_expert_shop_results( $listing, $base_url ),
		'count'    => door_expert_shop_count_html( $listing->found_posts ),
		// Kontekst eksplicitno: u AJAX-u is_product_category() nije tačno, pa bi facets
		// pali na 'default' i grupa specifična za kategoriju ostala bez svježih brojeva.
		'facets'   => function_exists( 'door_expert_filter_facets' )
			? door_expert_filter_facets( $cat_id > 0 ? $cat_id : 'default' )
			: array(),
		'found'    => (int) $listing->found_posts,
		'maxPages' => (int) $listing->max_num_pages,
		'paged'    => $paged,
	);

	$_GET = $original_get;

	wp_send_json_success( $payload );
}
