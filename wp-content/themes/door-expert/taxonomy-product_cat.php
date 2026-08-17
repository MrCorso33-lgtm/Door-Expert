<?php
/**
 * taxonomy-product_cat.php – kategorijski šablon (WooCommerce product_cat).
 *
 * ROUTER: bira prikaz prema tipu terma.
 *   • top-level (roditelj) → bespoke part po slug-u:
 *       template-parts/category/parent/{slug}.php
 *     (fallback na generički subcategory.php ako bespoke part ne postoji)
 *   • dete (potkategorija) → uniforman template-parts/category/subcategory.php
 *
 * Sadržaj svake kategorije dolazi iz door_expert_cat_content($slug) – ODVOJEN od prikaza.
 * Migracija na JetEngine menja samo tu funkciju (get_term_meta), ne ove šablone.
 *
 * WC default wrapperi su otkačeni u functions.php – naš <main> (header.php) drži layout.
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$de_term = get_queried_object();

if ( $de_term instanceof WP_Term ) {

	$de_args = array(
		'term'    => $de_term,
		'content' => function_exists( 'door_expert_cat_content' ) ? door_expert_cat_content( $de_term->slug ) : array(),
	);

	// Globalno na svim kategorijama (roditelj + potkategorija), pre sadržaja:
	// mobilna traka za poziv + breadcrumb.
	get_template_part( 'template-parts/category/parts/mobile-phone-bar' );
	get_template_part( 'template-parts/category/parts/breadcrumb', null, $de_args );

	$de_parent_tpl = 'template-parts/category/parent/' . $de_term->slug . '.php';

	if ( 0 === (int) $de_term->parent && '' !== locate_template( $de_parent_tpl ) ) {
		// Bespoke roditeljski part (npr. keramika sa plo-calculator).
		get_template_part( 'template-parts/category/parent/' . $de_term->slug, null, $de_args );
	} else {
		// Potkategorija, ili roditelj bez bespoke parta → generički skelet.
		get_template_part( 'template-parts/category/subcategory', null, $de_args );
	}
}

get_footer();
