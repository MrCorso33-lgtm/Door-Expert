<?php
/**
 * Product katalog – WooCommerce proizvodi u tekućem termu (+ potomci).
 *
 * Renderuje se SAMO ako ima proizvoda (Faza A: prazno dok se ne dodaju).
 * Koristi WooCommerce NATIVE product loop (WC ima svoj kompletan CSS za kartice) –
 * ne juri prototip .product-card jer taj CSS ne postoji u temi (Manus nedoslednost).
 *
 * TODO (produkcija):
 *  - Filter sidebar (.cat-filters/.cat-sidebar – stilovi postoje u category.css): dinamički
 *    iz WC atributa (brend, anti-slip klasa, dimenzije…) = zaseban funkcionalni feature.
 *  - Custom product-card (brend zastava, dim-pill, ušteda) = override woocommerce/content-product.php
 *    + WC atributi na proizvodima. Za sad WC default kartica.
 *
 * @package DoorExpert
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$term = isset( $args['term'] ) ? $args['term'] : get_queried_object();
if ( ! ( $term instanceof WP_Term ) || ! function_exists( 'wc_get_template_part' ) ) {
	return;
}

$de_products = new WP_Query(
	array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => 12,
		'tax_query'      => array(
			array(
				'taxonomy'         => 'product_cat',
				'field'            => 'term_id',
				'terms'            => $term->term_id,
				'include_children' => true,
			),
		),
	)
);

if ( ! $de_products->have_posts() ) {
	wp_reset_postdata();
	return;
}
$de_count = (int) $de_products->found_posts;
?>
<section class="cat-products">
  <div class="cat-products__inner">

    <div class="cat-toolbar">
      <p class="cat-count"><?php echo esc_html( $de_count . ' ' . _n( 'proizvod', 'proizvoda', $de_count, 'door-expert' ) ); ?></p>
      <div class="cat-sort">
        <label class="cat-sort__label" for="cat-sort-select">Sortiraj:</label>
        <select class="cat-sort__select" id="cat-sort-select">
          <option value="popular">Najpopularniji</option>
          <option value="price-asc">Cijena: niža &rarr; viša</option>
          <option value="price-desc">Cijena: viša &rarr; niža</option>
          <option value="newest">Najnoviji</option>
        </select>
      </div>
    </div>

    <ul class="products columns-3">
      <?php
      while ( $de_products->have_posts() ) {
        $de_products->the_post();
        wc_get_template_part( 'content', 'product' );
      }
      ?>
    </ul>

  </div>
</section>
<?php
wp_reset_postdata();
