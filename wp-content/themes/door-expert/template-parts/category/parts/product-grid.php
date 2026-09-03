<?php
/**
 * Kategorijski listing – filteri + toolbar + product grid + paginacija.
 *
 * Koristi GLAVNI upit (na product_cat arhivi on već sadrži proizvode tog terma i
 * potomaka), pa se filteri i sort iz inc/shop.php primjenjuju automatski preko
 * woocommerce_product_query => brojač i paginacija su konzistentni.
 *
 * Ranije (Faza A): zaseban WP_Query, WooCommerce default kartice, sort bez funkcije,
 * bez filtera. TODO iz tog fajla je riješen ponovnom upotrebom onoga što je već
 * napravljeno za prodavnicu:
 *   - template-parts/shop/filters.php    (grupa "Kategorija" sakrivena – redundantna ovdje)
 *   - template-parts/shop/product-card.php
 *   - inc/shop.php                       (query sloj, sort, hidden inputs)
 *
 * ODSTUPANJE od prototipa: prototip kategorije koristi .cat-filters/.cat-products
 * markup, ovdje se koristi .shop-* (isti kao prodavnica) da postoji JEDNA
 * implementacija filtera i grida umjesto dvije. Zato se prodavnica.css učitava i
 * na kategorijama (functions.php).
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'door_expert_listing_base_url' ) ) {
	return;
}

$de_base  = door_expert_listing_base_url();
$de_total = isset( $GLOBALS['wp_query'] ) ? (int) $GLOBALS['wp_query']->found_posts : 0;

$de_orderby_arr = door_expert_shop_selected( 'orderby' );
$de_orderby     = ! empty( $de_orderby_arr ) ? $de_orderby_arr[0] : 'menu_order';
?>

<div class="shop-main" id="katalog">

  <!-- Mobilni toggle filtera -->
  <button type="button" class="shop-filters-toggle" id="filterToggle">
    <svg style="width:14px;height:14px;vertical-align:middle;margin-right:6px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="12" x2="14" y2="12"/><line x1="4" y1="18" x2="9" y2="18"/></svg>
    Prikaži filtere
  </button>

  <?php
  get_template_part(
    'template-parts/shop/filters',
    null,
    array(
      'base_url' => $de_base,
      'hide'     => array( 'cat' ), // Već smo unutar kategorije.
    )
  );
  ?>

  <div class="shop-content">

    <div class="shop-toolbar">
      <div class="shop-toolbar__count">Prikazano <strong><?php echo esc_html( (string) $de_total ); ?></strong> <?php echo esc_html( _n( 'proizvod', 'proizvoda', $de_total, 'door-expert' ) ); ?></div>
      <div class="shop-toolbar__actions">
        <form class="shop-toolbar__sort-form" method="get" action="<?php echo esc_url( $de_base ); ?>">
          <select class="shop-toolbar__sort" name="orderby" onchange="this.form.submit()">
            <option value="menu_order" <?php selected( 'menu_order', $de_orderby ); ?>>Sortiraj: Preporučeno</option>
            <option value="price" <?php selected( 'price', $de_orderby ); ?>>Cijena: niža prvo</option>
            <option value="price-desc" <?php selected( 'price-desc', $de_orderby ); ?>>Cijena: viša prvo</option>
            <option value="date" <?php selected( 'date', $de_orderby ); ?>>Najnovije</option>
            <option value="popularity" <?php selected( 'popularity', $de_orderby ); ?>>Najpopularnije</option>
          </select>
          <?php door_expert_shop_hidden_inputs( array( 'orderby' ) ); ?>
        </form>
      </div>
    </div>

    <?php if ( have_posts() ) : ?>

      <div class="shop-grid">
        <?php
        while ( have_posts() ) {
            the_post();
            get_template_part( 'template-parts/shop/product-card' );
        }
        ?>
      </div>

      <?php
      $de_paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
      $de_pages = isset( $GLOBALS['wp_query'] ) ? (int) $GLOBALS['wp_query']->max_num_pages : 0;

      if ( $de_pages > 1 ) {
          $de_big   = 999999999;
          $de_links = paginate_links(
              array(
                  'base'      => str_replace( $de_big, '%#%', esc_url( get_pagenum_link( $de_big ) ) ),
                  'format'    => '?paged=%#%',
                  'current'   => $de_paged,
                  'total'     => $de_pages,
                  'type'      => 'plain',
                  'end_size'  => 1,
                  'mid_size'  => 2,
                  'prev_text' => '<svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>',
                  'next_text' => '<svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>',
              )
          );

          if ( $de_links ) {
              echo '<nav class="shop-pagination" aria-label="Stranice">' . $de_links . '</nav>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- paginate_links vraća bezbjedan markup.
          }
      }
      ?>

    <?php else : ?>
      <div class="shop-empty">
        <p class="shop-empty__title">Nema proizvoda za izabrane filtere.</p>
        <a class="shop-empty__reset" href="<?php echo esc_url( $de_base ); ?>">Očisti filtere</a>
      </div>
    <?php endif; ?>

  </div>
</div>

<?php
// Loop je iscrpljen (glavni upit) – vrati ga za slučaj da neki part ispod očekuje post podatke.
wp_reset_postdata();
