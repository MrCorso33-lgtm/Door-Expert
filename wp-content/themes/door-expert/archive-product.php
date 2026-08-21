<?php
/**
 * archive-product.php – WooCommerce shop arhiva (Prodavnica): svi proizvodi.
 *
 * Dizajn iz prototipa prodavnica.html (hero pilule + filter sidebar + toolbar +
 * product grid + paginacija), ali sadržaj = PRAVI WooCommerce loop nad glavnim upitom.
 * Filteri i sort se primjenjuju u inc/shop.php (woocommerce_product_query) => paginacija
 * i brojač su konzistentni. Prikaz kartice: template-parts/shop/product-card.php.
 *
 * Postavi ovu stranicu kao WooCommerce Shop page (Settings → Products → Shop page).
 * <main> je otvoren u header.php, zatvoren u footer.php.
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Hero grupe (brze kategorijske pilule).
$de_groups = array(
	'all'        => array( 'label' => 'Sve',        'slugs' => array() ),
	'vrata'      => array( 'label' => 'Vrata',      'slugs' => array( 'sobna-vrata', 'sigurnosna-vrata' ) ),
	'keramika'   => array( 'label' => 'Keramika',   'slugs' => array( 'keramicke-plocice' ) ),
	'umivaonici' => array( 'label' => 'Umivaonici', 'slugs' => array( 'umivaonici' ) ),
);

$de_group_icons = array(
	'all'        => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>',
	'vrata'      => '<rect x="3" y="2" width="14" height="20" rx="1"/><circle cx="14" cy="12" r="1.5"/>',
	'keramika'   => '<rect x="2" y="2" width="9" height="9"/><rect x="13" y="2" width="9" height="9"/><rect x="2" y="13" width="9" height="9"/><rect x="13" y="13" width="9" height="9"/>',
	'umivaonici' => '<ellipse cx="12" cy="14" rx="9" ry="5"/><path d="M3 14V8a9 5 0 0118 0v6"/>',
);

$de_sel_cat     = door_expert_shop_selected( 'f_cat' );
$de_sel_cat_srt = $de_sel_cat;
sort( $de_sel_cat_srt );

$de_orderby_arr = door_expert_shop_selected( 'orderby' );
$de_orderby     = ! empty( $de_orderby_arr ) ? $de_orderby_arr[0] : 'menu_order';

$de_total = (int) $GLOBALS['wp_query']->found_posts;
?>

<!-- SHOP HERO -->
<section class="shop-hero">
  <div class="shop-hero__eyebrow">Kompletan asortiman</div>
  <h1 class="shop-hero__title">Prodavnica</h1>
  <p class="shop-hero__desc">Pregledajte cijelu ponudu vrata, keramičkih pločica i dekorativnih umivaonika. Filtrirajte po kategoriji, brendu, boji ili dimenzijama.</p>
  <div class="shop-hero__cats">
    <?php
    foreach ( $de_groups as $de_key => $de_group ) {
        $de_group_srt = $de_group['slugs'];
        sort( $de_group_srt );

        if ( 'all' === $de_key ) {
            $de_active = empty( $de_sel_cat );
            $de_count  = (int) wp_count_posts( 'product' )->publish;
            $de_args   = ( 'menu_order' !== $de_orderby ) ? array( 'orderby' => $de_orderby ) : array();
        } else {
            $de_active = ( $de_sel_cat_srt === $de_group_srt );
            $de_count  = door_expert_shop_group_count( $de_group['slugs'] );
            $de_args   = array( 'f_cat' => $de_group['slugs'] );
            if ( 'menu_order' !== $de_orderby ) {
                $de_args['orderby'] = $de_orderby;
            }
        }

        printf(
            '<a href="%1$s" class="shop-hero__cat-btn%2$s"><svg viewBox="0 0 24 24">%3$s</svg> %4$s <span class="shop-hero__count">%5$d</span></a>',
            esc_url( door_expert_shop_base_url( $de_args ) ),
            $de_active ? ' is-active' : '',
            $de_group_icons[ $de_key ], // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- statičan SVG markup.
            esc_html( $de_group['label'] ),
            $de_count
        );
    }
    ?>
  </div>
</section>

<!-- MAIN: FILTERI + GRID -->
<div class="shop-main">

  <!-- Mobilni toggle filtera -->
  <button type="button" class="shop-filters-toggle" id="filterToggle">
    <svg style="width:14px;height:14px;vertical-align:middle;margin-right:6px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="12" x2="14" y2="12"/><line x1="4" y1="18" x2="9" y2="18"/></svg>
    Prikaži filtere
  </button>

  <!-- FILTER SIDEBAR -->
  <?php get_template_part( 'template-parts/shop/filters' ); ?>

  <!-- CONTENT AREA -->
  <div class="shop-content">

    <!-- Toolbar -->
    <div class="shop-toolbar">
      <div class="shop-toolbar__count">Prikazano <strong><?php echo esc_html( (string) $de_total ); ?></strong> <?php echo esc_html( _n( 'proizvod', 'proizvoda', $de_total, 'door-expert' ) ); ?></div>
      <div class="shop-toolbar__actions">
        <form class="shop-toolbar__sort-form" method="get" action="<?php echo esc_url( door_expert_shop_base_url() ); ?>">
          <select class="shop-toolbar__sort" name="orderby" onchange="this.form.submit()">
            <option value="menu_order" <?php selected( 'menu_order', $de_orderby ); ?>>Sortiraj: Preporučeno</option>
            <option value="price" <?php selected( 'price', $de_orderby ); ?>>Cijena: niža prvo</option>
            <option value="price-desc" <?php selected( 'price-desc', $de_orderby ); ?>>Cijena: viša prvo</option>
            <option value="date" <?php selected( 'date', $de_orderby ); ?>>Najnovije</option>
            <option value="popularity" <?php selected( 'popularity', $de_orderby ); ?>>Najpopularnije</option>
          </select>
          <?php door_expert_shop_hidden_inputs( array( 'orderby' ) ); ?>
        </form>
        <div class="shop-toolbar__view-btns">
          <button type="button" class="shop-toolbar__view-btn is-active" title="Grid"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg></button>
          <button type="button" class="shop-toolbar__view-btn" title="Lista"><svg viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg></button>
        </div>
      </div>
    </div>

    <?php if ( have_posts() ) : ?>
      <!-- Product Grid -->
      <div class="shop-grid">
        <?php
        while ( have_posts() ) {
            the_post();
            get_template_part( 'template-parts/shop/product-card' );
        }
        ?>
      </div>

      <!-- Paginacija -->
      <?php
      $de_paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
      $de_pages = (int) $GLOBALS['wp_query']->max_num_pages;

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
        <a class="shop-empty__reset" href="<?php echo esc_url( door_expert_shop_base_url() ); ?>">Očisti filtere</a>
      </div>
    <?php endif; ?>

  </div>
</div>

<?php
get_footer();
