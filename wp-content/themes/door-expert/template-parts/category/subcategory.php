<?php
/**
 * subcategory.php – uniforman skelet potkategorije (i roditelja bez bespoke parta).
 *
 * Redosled: hero → benefits → [bespoke body per-slug] → product-grid (WC) →
 *           faq → crosssell → cta.
 * Podatke dobija preko $args (term + content). Svaki part hendlaje prazno.
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$de_term    = isset( $args['term'] ) ? $args['term'] : get_queried_object();
$de_content = isset( $args['content'] ) ? $args['content'] : array();
$de_base    = 'template-parts/category/';

// Hero (fallback na naziv terma ako nema definisanog sadržaja).
get_template_part(
	$de_base . 'parts/hero',
	null,
	array( 'hero' => isset( $de_content['hero'] ) ? $de_content['hero'] : array(), 'term' => $de_term )
);

// Benefiti.
if ( ! empty( $de_content['benefits'] ) ) {
	get_template_part( $de_base . 'parts/benefits', null, array( 'benefits' => $de_content['benefits'] ) );
}

// Bespoke srednji deo (rooms / antislip / types) – per-slug part ako postoji.
$de_body = ! empty( $de_content['body_part'] ) ? $de_content['body_part'] : $de_term->slug;
if ( '' !== locate_template( $de_base . 'body/' . $de_body . '.php' ) ) {
	get_template_part( $de_base . 'body/' . $de_body, null, array( 'term' => $de_term, 'content' => $de_content ) );
}

// Product grid (WC loop) – renderuje se samo ako ima proizvoda.
get_template_part( $de_base . 'parts/product-grid', null, array( 'term' => $de_term ) );

// FAQ.
if ( ! empty( $de_content['faq'] ) ) {
	get_template_part( $de_base . 'parts/faq', null, array( 'faq' => $de_content['faq'] ) );
}

// Cross-sell.
if ( ! empty( $de_content['crosssell'] ) ) {
	get_template_part( $de_base . 'parts/crosssell', null, array( 'crosssell' => $de_content['crosssell'] ) );
}

// CTA banner.
if ( ! empty( $de_content['cta'] ) ) {
	get_template_part( $de_base . 'parts/cta', null, array( 'cta' => $de_content['cta'] ) );
}
