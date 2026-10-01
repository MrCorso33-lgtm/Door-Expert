<?php
/**
 * Početna – "Odabrani za vas ovog mjeseca".
 *
 * Proizvodi koje je klijent čekirao na samom proizvodu (kvadratić "Početna —
 * Odabrani za vas", inc/home-featured.php). Ranije su ovdje bile četiri izmišljene
 * kartice iz prototipa (nazivi, cijene i Unsplash slike koji ne postoje u katalogu),
 * sa dugmetom koje ništa nije radilo.
 *
 * - Kartica je ISTA kao u prodavnici (template-parts/shop/product-card.php), pa
 *   početna dobija pravu cijenu, "Cijena na upit", istaknute atribute i pravo
 *   dodavanje u upit, bez posebne kopije koja bi se razilazila.
 * - Server renderuje sve (najviše 20 po kategoriji); featured.js prikazuje 4 i na
 *   "Prikaži više" otkriva po još 4. Nema AJAX-a; sakrivene kartice imaju
 *   loading="lazy", pa njihove slike ne skidaju dok se ne pokažu. Obrazac iz Saya
 *   projekta.
 * - Ništa nije čekirano → sekcija se NE prikazuje. Namjerno bez rezerve "najnoviji":
 *   naslov kaže "Odabrani za vas", a tu bi stajali proizvodi koje niko nije odabrao.
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$de_featured = function_exists( 'door_expert_home_featured_data' ) ? door_expert_home_featured_data() : array( 'items' => array() );

if ( empty( $de_featured['items'] ) ) {
	return;
}

$de_featured_visible = 4;
$de_shop_url         = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );

// Kartica radi preko globalnog $product, kao u listingu (inc/shop.php): the_post()
// ga WooCommerce sam postavi. Redoslijed iz door_expert_home_featured_data() se čuva.
$de_featured_query = new WP_Query(
	array(
		'post_type'           => 'product',
		'post__in'            => wp_list_pluck( $de_featured['items'], 'id' ),
		'orderby'             => 'post__in',
		'posts_per_page'      => count( $de_featured['items'] ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);

$de_featured_cats = wp_list_pluck( $de_featured['items'], 'cat', 'id' );
?>
<section class="featured" aria-label="Istaknuti proizvodi" data-visible="<?php echo (int) $de_featured_visible; ?>">
  <div class="featured__inner">

    <div class="featured__header">
      <div>
        <p class="featured__eyebrow">Novo i istaknuto</p>
        <h2 class="featured__title">Odabrani za vas ovog mjeseca</h2>
      </div>
      <a href="<?php echo esc_url( $de_shop_url ); ?>" class="featured__view-all">
        Cijeli katalog
        <svg viewBox="0 0 24 24" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
      </a>
    </div>

    <?php if ( count( $de_featured['tabs'] ) > 1 ) : ?>
      <?php // Jedan jedini tab bi bio isto što i "Sve", pa se tada tabovi ne crtaju. ?>
      <div class="featured__tabs" role="tablist" aria-label="Filter po kategoriji">
        <button type="button" class="featured__tab is-active" data-filter="sve" role="tab" aria-selected="true">Sve</button>
        <?php foreach ( $de_featured['tabs'] as $de_tab ) : ?>
          <button type="button" class="featured__tab" data-filter="<?php echo esc_attr( $de_tab['slug'] ); ?>" role="tab" aria-selected="false"><?php echo esc_html( $de_tab['name'] ); ?></button>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="featured__grid" id="featured-grid">
      <?php while ( $de_featured_query->have_posts() ) : ?>
        <?php
        $de_featured_query->the_post();
        $de_item_cat = isset( $de_featured_cats[ get_the_ID() ] ) ? $de_featured_cats[ get_the_ID() ] : '';
        ?>
        <div class="featured__item" data-cat="<?php echo esc_attr( $de_item_cat ); ?>">
          <?php get_template_part( 'template-parts/shop/product-card' ); ?>
        </div>
      <?php endwhile; ?>
      <?php wp_reset_postdata(); ?>
    </div>

    <div class="featured__more">
      <?php // Skriveno dok JS ne izračuna da ima više od 4; bez JS-a se vidi sve, pa dugme ne treba. ?>
      <button type="button" class="featured__more-btn" id="featured-more" aria-controls="featured-grid" hidden>Prikaži više</button>
    </div>

  </div>
</section>
