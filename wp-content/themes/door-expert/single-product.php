<?php
/**
 * single-product.php – WooCommerce single proizvod (PDP).
 *
 * Bespoke template (verna konverzija prototipa product.html) – NE koristi
 * woocommerce_content(); naš <main> (header.php/footer.php) drži layout, a
 * template-parts/product/single.php renderuje PDP iz WC_Product.
 *
 * Sadržaj (FAQ) + grupa proizvoda: inc/product.php (ODVOJENO od prikaza).
 * CSS/JS: assets/css/product.css + assets/js/product.js (enqueue na is_product()).
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) {
	the_post();

	$de_product = function_exists( 'wc_get_product' ) ? wc_get_product( get_the_ID() ) : null;

	if ( $de_product instanceof WC_Product ) {
		get_template_part(
			'template-parts/product/single',
			null,
			array( 'product' => $de_product )
		);
	}
}

get_footer();
