<?php
/**
 * PDP prikaz – verna konverzija prototipa product.html, dinamički iz WC_Product.
 *
 * Prima $args['product'] (WC_Product) iz single-product.php.
 * Faza A / v1: SIMPLE proizvodi; varijante (atributi) prikazane READ-ONLY (bez menjanja
 * cijene). Nadogradnja na Variable/interaktivne varijacije je izolovana u sekciji "varijante".
 *
 * Data-type toggle iz prototipa NE postoji – server renderuje samo relevantni sadržaj po
 * grupi (door_expert_product_group). Specifikacije = dinamički iz WC atributa (bez placeholdera).
 * FAQ = inc/product.php (odvojeno). Galerija = WC slike. product.js radi galeriju/lightbox/qty/kalkulator.
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$de_product = isset( $args['product'] ) && $args['product'] instanceof WC_Product ? $args['product'] : null;
if ( ! $de_product ) {
	global $product;
	$de_product = $product instanceof WC_Product ? $product : null;
}
if ( ! $de_product ) {
	return;
}

$de_id    = $de_product->get_id();
$de_group = function_exists( 'door_expert_product_group' ) ? door_expert_product_group( $de_id ) : '';

// Kategorija (prva) + brend.
$de_cat_name = '';
$de_cat_link = '';
$de_cats     = get_the_terms( $de_id, 'product_cat' );
if ( is_array( $de_cats ) ) {
	foreach ( $de_cats as $de_c ) {
		if ( 'uncategorized' !== $de_c->slug ) {
			$de_cat_name = $de_c->name;
			$de_link_obj = get_term_link( $de_c );
			$de_cat_link = is_wp_error( $de_link_obj ) ? '' : $de_link_obj;
			break;
		}
	}
}

$de_brand_name = '';
$de_brands     = taxonomy_exists( 'product_brand' ) ? get_the_terms( $de_id, 'product_brand' ) : false;
if ( is_array( $de_brands ) && ! empty( $de_brands ) ) {
	$de_brand_name = $de_brands[0]->name;
}

// Galerija: featured + gallery slike.
$de_img_ids = array();
if ( $de_product->get_image_id() ) {
	$de_img_ids[] = $de_product->get_image_id();
}
$de_img_ids = array_merge( $de_img_ids, $de_product->get_gallery_image_ids() );

// Cijena.
$de_on_sale = $de_product->is_on_sale();
$de_reg     = (float) $de_product->get_regular_price();
$de_sale    = (float) $de_product->get_sale_price();

// Jedinica cijene po grupi.
$de_unit_note = 'PDV uključen.';
if ( 'vrata' === $de_group ) {
	$de_unit_note = 'Cijena po komadu, sa štok-okvirom. PDV uključen.';
} elseif ( 'plocice' === $de_group ) {
	$de_unit_note = 'Cijena po m². PDV uključen.';
} elseif ( 'umivaonik' === $de_group ) {
	$de_unit_note = 'Cijena po komadu. PDV uključen.';
}

// Atributi (za read-only varijante i specifikacije).
$de_attributes = $de_product->get_attributes();

// FAQ.
$de_faq = function_exists( 'door_expert_product_faq' ) ? door_expert_product_faq( $de_group ) : array();

// Related (cross-sell).
$de_related_ids = function_exists( 'wc_get_related_products' ) ? wc_get_related_products( $de_id, 4 ) : array();
?>

<?php // NAPOMENA: <main> je već otvoren u header.php – ovdje koristimo <div> (bez ugnježdenog <main>). ?>
<div class="product-page" data-type="<?php echo esc_attr( $de_group ); ?>" id="product-page-root">

  <!-- BREADCRUMB -->
  <nav class="product-breadcrumb" aria-label="Breadcrumb">
    <div class="product-breadcrumb__inner">
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="product-breadcrumb__link">Početna</a>
      <?php if ( '' !== $de_cat_name && '' !== $de_cat_link ) : ?>
        <span class="product-breadcrumb__sep" aria-hidden="true">›</span>
        <a href="<?php echo esc_url( $de_cat_link ); ?>" class="product-breadcrumb__link"><?php echo esc_html( $de_cat_name ); ?></a>
      <?php endif; ?>
      <span class="product-breadcrumb__sep" aria-hidden="true">›</span>
      <span class="product-breadcrumb__current"><?php echo esc_html( $de_product->get_name() ); ?></span>
    </div>
  </nav>

  <?php
  if ( function_exists( 'wc_print_notices' ) && function_exists( 'wc_notice_count' ) && wc_notice_count() > 0 ) {
    echo '<div class="product-notices" style="max-width:1200px;margin:0 auto;padding:0 24px;">';
    wc_print_notices(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WC generiše bezbjedan markup.
    echo '</div>';
  }
  ?>

  <div class="product-layout">

    <!-- GALERIJA -->
    <div class="product-gallery" id="product-gallery">
      <div class="product-gallery__main" id="gallery-main" role="button" aria-label="Povećaj sliku" tabindex="0">
        <?php
        $de_main_full = ! empty( $de_img_ids ) ? wp_get_attachment_image_url( $de_img_ids[0], 'woocommerce_single' ) : '';
        if ( ! $de_main_full && function_exists( 'wc_placeholder_img_src' ) ) {
          $de_main_full = wc_placeholder_img_src( 'woocommerce_single' );
        }
        ?>
        <img id="gallery-main-img" src="<?php echo esc_url( $de_main_full ); ?>" alt="<?php echo esc_attr( $de_product->get_name() ); ?>" loading="eager" fetchpriority="high" />
        <?php if ( '' !== $de_cat_name ) : ?>
          <span class="product-gallery__badge" id="gallery-badge"><?php echo esc_html( $de_cat_name ); ?></span>
        <?php endif; ?>
        <div class="product-gallery__zoom-hint" aria-hidden="true">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
          Povećaj
        </div>
      </div>

      <?php if ( count( $de_img_ids ) > 1 ) : ?>
        <div class="product-gallery__thumbs" id="gallery-thumbs" role="list" aria-label="Galerija slika">
          <?php foreach ( $de_img_ids as $de_i => $de_iid ) : ?>
            <?php
            $de_t_src = wp_get_attachment_image_url( $de_iid, 'woocommerce_thumbnail' );
            $de_f_src = wp_get_attachment_image_url( $de_iid, 'woocommerce_single' );
            if ( ! $de_t_src ) { continue; }
            ?>
            <button type="button" class="product-gallery__thumb<?php echo 0 === $de_i ? ' is-active' : ''; ?>" role="listitem" data-full="<?php echo esc_url( $de_f_src ); ?>" aria-label="<?php echo esc_attr( sprintf( 'Slika %d', $de_i + 1 ) ); ?>">
              <img src="<?php echo esc_url( $de_t_src ); ?>" alt="" loading="lazy" />
            </button>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div><!-- /product-gallery -->

    <!-- DECISION -->
    <div class="product-decision" id="product-decision">

      <?php if ( '' !== $de_cat_name ) : ?>
        <div class="product-decision__category"><?php echo esc_html( '' !== $de_brand_name ? $de_cat_name . ' · ' . $de_brand_name : $de_cat_name ); ?></div>
      <?php endif; ?>

      <h1 class="product-decision__name"><?php echo esc_html( $de_product->get_name() ); ?></h1>
      <?php
      $de_subname = wp_strip_all_tags( $de_product->get_short_description() );
      if ( '' !== $de_subname ) :
        ?>
        <p class="product-decision__subname"><?php echo esc_html( $de_subname ); ?></p>
      <?php endif; ?>

      <hr class="product-sep">

      <!-- Cijena -->
      <div class="product-price-block">
        <?php if ( $de_on_sale && $de_reg > 0 && $de_sale > 0 ) : ?>
          <span class="product-price-block__current"><?php echo wp_kses_post( wc_price( $de_sale ) ); ?></span>
          <span class="product-price-block__original"><?php echo wp_kses_post( wc_price( $de_reg ) ); ?></span>
          <span class="product-price-block__savings">✓ Uštedite <?php echo wp_kses_post( wc_price( $de_reg - $de_sale ) ); ?></span>
        <?php else : ?>
          <span class="product-price-block__current"><?php echo wp_kses_post( $de_product->get_price_html() ); ?></span>
        <?php endif; ?>
      </div>
      <div class="product-price-block" style="margin-top:2px;">
        <span class="product-price-block__unit"><?php echo esc_html( $de_unit_note ); ?></span>
      </div>

      <!-- Dostupnost -->
      <?php $de_in_stock = $de_product->is_in_stock(); ?>
      <div class="product-availability product-availability--<?php echo $de_in_stock ? 'in-stock' : 'order'; ?>" style="margin-top:12px;">
        <span class="product-availability__dot" aria-hidden="true"></span>
        <span class="product-availability__text"><?php echo $de_in_stock ? 'Na stanju u Podgorici' : 'Po narudžbi'; ?></span>
      </div>
      <div class="product-availability__sub"><?php echo $de_in_stock ? 'Isporuka odmah · Montaža po dogovoru (2–15 dana)' : 'Rok isporuke po dogovoru'; ?></div>

      <hr class="product-sep">

      <!-- Varijante (READ-ONLY iz atributa) -->
      <?php
      foreach ( $de_attributes as $de_attr ) {
        if ( ! $de_attr instanceof WC_Product_Attribute || ! $de_attr->get_visible() ) {
          continue;
        }
        $de_attr_label  = wc_attribute_label( $de_attr->get_name() );
        $de_attr_values = $de_product->get_attribute( $de_attr->get_name() );
        if ( '' === $de_attr_values ) {
          continue;
        }
        $de_vals = array_filter( array_map( 'trim', explode( ',', $de_attr_values ) ) );
        if ( empty( $de_vals ) ) {
          continue;
        }
        ?>
        <div class="product-variants" style="margin-top:16px;">
          <div class="product-variants__label"><?php echo esc_html( $de_attr_label ); ?></div>
          <div class="product-variants__pills" role="list" aria-label="<?php echo esc_attr( $de_attr_label ); ?>">
            <?php foreach ( $de_vals as $de_val ) : ?>
              <span class="product-variant-pill" role="listitem"><?php echo esc_html( $de_val ); ?></span>
            <?php endforeach; ?>
          </div>
        </div>
        <?php
      }
      ?>

      <?php if ( 'plocice' === $de_group ) : ?>
        <!-- m² kalkulator (samo pločice) -->
        <div class="product-calc" id="tile-calculator" data-price="<?php echo esc_attr( (string) $de_product->get_price() ); ?>">
          <div class="product-calc__title">Kalkulator količine</div>
          <div class="product-calc__row">
            <div class="product-calc__field">
              <label for="calc-width">Širina prostorije (m)</label>
              <input type="number" id="calc-width" placeholder="npr. 3.5" min="0.1" step="0.1" />
            </div>
            <div class="product-calc__field">
              <label for="calc-length">Dužina prostorije (m)</label>
              <input type="number" id="calc-length" placeholder="npr. 4.2" min="0.1" step="0.1" />
            </div>
          </div>
          <div class="product-calc__result" id="calc-result">Unesite dimenzije prostorije za izračun</div>
        </div>
      <?php endif; ?>

      <!-- Količina + CTA (WC add-to-cart forma) -->
      <form class="cart product-cta-form" method="post" enctype="multipart/form-data" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $de_product->get_permalink() ) ); ?>">
        <div class="product-quantity">
          <span class="product-quantity__label" id="qty-label"><?php echo 'plocice' === $de_group ? 'Količina (m²)' : 'Količina'; ?></span>
          <div class="product-quantity__controls">
            <button type="button" class="product-quantity__btn" id="qty-minus" aria-label="Smanji količinu">−</button>
            <input class="product-quantity__input" type="number" id="qty-input" name="quantity" value="1" min="1" max="99" aria-label="Količina" />
            <button type="button" class="product-quantity__btn" id="qty-plus" aria-label="Povećaj količinu">+</button>
          </div>
        </div>

        <hr class="product-sep">

        <div class="product-cta-group">
          <button type="submit" name="add-to-cart" value="<?php echo esc_attr( (string) $de_id ); ?>" class="btn-product-primary" id="btn-add-to-cart">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
            Dodaj u ponudu
          </button>
          <a href="tel:+38269234888" class="btn-product-secondary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.67A2 2 0 012 .18h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
            Pozovite salon
          </a>
        </div>
      </form>

      <!-- Šta se dešava nakon klika -->
      <div class="product-next-steps">
        <div class="product-next-steps__title">Šta se dešava nakon klika?</div>
        <ul class="product-next-steps__list">
          <li>Vaš upit prima naš tim – nema automatske naplate</li>
          <li>Šaljemo formalnu ponudu / pro formu emailom</li>
          <li>Po potrebi zovemo radi potvrde dimenzija ili dostupnosti</li>
          <li>Plaćanje: gotovina, kartica, virman ili rate</li>
        </ul>
      </div>

    </div><!-- /product-decision -->

  </div><!-- /product-layout -->

  <div class="product-below">

    <!-- SPECIFIKACIJE -->
    <?php
    $de_spec_rows = array();
    if ( '' !== $de_brand_name ) {
      $de_spec_rows[] = array( 'Brend', $de_brand_name );
    }
    foreach ( $de_attributes as $de_attr ) {
      if ( ! $de_attr instanceof WC_Product_Attribute || ! $de_attr->get_visible() ) {
        continue;
      }
      $de_v = $de_product->get_attribute( $de_attr->get_name() );
      if ( '' !== $de_v ) {
        $de_spec_rows[] = array( wc_attribute_label( $de_attr->get_name() ), $de_v );
      }
    }
    $de_spec_rows[] = array( 'Dostupnost', $de_in_stock ? 'Na stanju u Podgorici' : 'Po narudžbi' );
    ?>
    <?php if ( ! empty( $de_spec_rows ) ) : ?>
      <section class="product-specs" aria-labelledby="specs-title">
        <div class="product-specs__header">
          <h2 class="product-section-title" id="specs-title">Specifikacije</h2>
        </div>
        <table class="product-specs__table" aria-label="Tehničke specifikacije">
          <tbody>
            <?php foreach ( $de_spec_rows as $de_row ) : ?>
              <tr><td><?php echo esc_html( $de_row[0] ); ?></td><td><?php echo esc_html( $de_row[1] ); ?></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </section>
    <?php endif; ?>

    <!-- OPIS -->
    <?php $de_desc = $de_product->get_description(); ?>
    <?php if ( '' !== trim( wp_strip_all_tags( $de_desc ) ) ) : ?>
      <section class="product-description" aria-labelledby="desc-title">
        <div class="product-specs__header">
          <h2 class="product-section-title" id="desc-title">Opis</h2>
        </div>
        <div class="product-description__body"><?php echo wp_kses_post( wpautop( $de_desc ) ); ?></div>
      </section>
    <?php endif; ?>

    <!-- FAQ -->
    <?php if ( ! empty( $de_faq ) ) : ?>
      <section class="product-faq" aria-labelledby="faq-title">
        <h2 class="product-section-title" id="faq-title">Česta pitanja</h2>
        <div class="product-faq__list" id="faq-list">
          <?php foreach ( $de_faq as $de_item ) : ?>
            <div class="product-faq__item">
              <button type="button" class="product-faq__question" aria-expanded="false">
                <span class="product-faq__question-text"><?php echo esc_html( $de_item['q'] ); ?></span>
                <span class="product-faq__icon" aria-hidden="true">+</span>
              </button>
              <div class="product-faq__answer" role="region">
                <div class="product-faq__answer-inner"><?php echo esc_html( $de_item['a'] ); ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <!-- CROSS-SELL / SLIČNO -->
    <?php if ( ! empty( $de_related_ids ) ) : ?>
      <section class="product-crosssell" aria-labelledby="crosssell-title">
        <h2 class="product-section-title" id="crosssell-title">Možda će vas zanimati</h2>
        <div class="product-crosssell__grid">
          <?php foreach ( $de_related_ids as $de_rid ) : ?>
            <?php
            $de_rp = wc_get_product( $de_rid );
            if ( ! $de_rp instanceof WC_Product ) { continue; }
            $de_rcats = get_the_terms( $de_rid, 'product_cat' );
            $de_rcat  = ( is_array( $de_rcats ) && ! empty( $de_rcats ) ) ? $de_rcats[0]->name : '';
            ?>
            <a href="<?php echo esc_url( get_permalink( $de_rid ) ); ?>" class="product-crosssell__card">
              <div class="product-crosssell__card-img">
                <?php echo $de_rp->get_image( 'woocommerce_thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WC escaped. ?>
              </div>
              <?php if ( '' !== $de_rcat ) : ?>
                <div class="product-crosssell__card-cat"><?php echo esc_html( $de_rcat ); ?></div>
              <?php endif; ?>
              <div class="product-crosssell__card-name"><?php echo esc_html( $de_rp->get_name() ); ?></div>
              <div class="product-crosssell__card-price"><?php echo wp_kses_post( $de_rp->get_price_html() ); ?></div>
            </a>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

  </div><!-- /product-below -->

  <!-- LIGHTBOX -->
  <div class="product-lightbox" id="product-lightbox" role="dialog" aria-modal="true" aria-label="Uvećana slika">
    <button type="button" class="product-lightbox__close" id="lightbox-close" aria-label="Zatvori">×</button>
    <img class="product-lightbox__img" id="lightbox-img" src="" alt="" />
  </div>

  <!-- MOBILNA STICKY TRAKA -->
  <div class="product-sticky-mobile" role="toolbar" aria-label="Brze akcije">
    <a href="tel:+38269234888" class="btn-product-secondary" style="flex:1; height:48px;">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.67A2 2 0 012 .18h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
      Pozovi
    </a>
    <a href="<?php echo esc_url( $de_product->add_to_cart_url() ); ?>" class="btn-product-primary" style="flex:2;" rel="nofollow">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
      Dodaj u ponudu
    </a>
  </div>

</div><!-- /product-page -->
