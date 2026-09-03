<?php
/**
 * Roditeljska kategorija: Keramičke pločice – verna konverzija prototipa keramicke-plocice.html.
 *
 * Redosled: plo-hero → plo-brands → subcats-grid → product-grid (filteri + grid) →
 *           plo-calculator → faq → crosssell → cta.
 *
 * Bespoke ovdje (stilovi postoje u plocice.css): plo-hero, plo-brands, plo-calculator.
 * Dijeljeni parts/ dijelovi: subcats-grid, product-grid, faq, crosssell, cta.
 * Sadržaj za faq/crosssell/cta: door_expert_cat_content('keramicke-plocice').
 *
 * ODSTUPANJA od prototipa:
 *   - Breadcrumb NIJE u herou – router (taxonomy-product_cat.php) ga već renderuje iznad.
 *   - plo-brands kartice su bile demo dugmad za klijentsko filtriranje; sada su LINKOVI
 *     na ovu kategoriju filtriranu po brendu (f_brand), a brojevi su stvarni broj
 *     proizvoda iz terma (prototip je imao izmišljeno "18 kolekcija").
 *   - FAQ/cross-sell/CTA: prototip koristi .sec-faq/.sec-crosssell/.pre-footer-cta
 *     klase kojima CSS ne postoji u temi, pa se koriste stilizovani subcat-* dijelovi.
 *   - Hero slika: /manus-storage/ ne postoji → thumbnail terma, fallback Unsplash.
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$de_term    = isset( $args['term'] ) ? $args['term'] : get_queried_object();
$de_content = isset( $args['content'] ) ? $args['content'] : array();
$de_base    = 'template-parts/category/';

$de_company = function_exists( 'door_expert_company_info' ) ? door_expert_company_info() : array();
$de_phone   = ! empty( $de_company['phones'][0] ) ? $de_company['phones'][0] : '+382 69 234 888';
$de_tel     = preg_replace( '/[^0-9+]/', '', $de_phone );

// Hero slika: thumbnail kategorije ako je postavljen, inače Unsplash (konvencija teme).
$de_hero_img = 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=1600&q=70';
if ( $de_term instanceof WP_Term ) {
	$de_thumb_id = (int) get_term_meta( $de_term->term_id, 'thumbnail_id', true );
	if ( $de_thumb_id ) {
		$de_full = wp_get_attachment_image_url( $de_thumb_id, 'full' );
		if ( $de_full ) {
			$de_hero_img = $de_full;
		}
	}
}

// Brend kartice – stvarni product_brand termovi + statični editorijalni opisi.
$de_brand_desc = array(
	'tau-ceramica'        => 'Moderan dizajn, veliki formati',
	'arcana-ceramica'     => 'Luksuzni efekti mramora i kamena',
	'new-tiles'           => 'Dekorativne i rustikalne serije',
	'ceramica-ribesalbes' => 'Ručno rađene, artizanske pločice',
);

$de_brands = taxonomy_exists( 'product_brand' )
	? get_terms( array( 'taxonomy' => 'product_brand', 'hide_empty' => false ) )
	: array();
$de_brands = is_wp_error( $de_brands ) ? array() : $de_brands;

$de_sel_brand = function_exists( 'door_expert_shop_selected' ) ? door_expert_shop_selected( 'f_brand' ) : array();
$de_listing   = function_exists( 'door_expert_listing_base_url' ) ? door_expert_listing_base_url() : '';
?>

<!-- HERO -->
<section class="plo-hero">
  <div class="plo-hero__bg">
    <img src="<?php echo esc_url( $de_hero_img ); ?>" alt="Španske keramičke pločice" class="plo-hero__bg-img" loading="eager" fetchpriority="high" />
    <div class="plo-hero__overlay"></div>
  </div>
  <div class="plo-hero__inner">
    <div class="plo-hero__content">
      <p class="plo-hero__label">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
        Direktan uvoz iz Španije
      </p>
      <h1 class="plo-hero__title">
        Španska keramika<br>
        <em>za svaki prostor</em>
      </h1>
      <p class="plo-hero__desc">
        Tau Ceramica, Arcana, New Tiles, Ceramica Ribesalbes – četiri španska brenda sa tradicijom dizajna i kvaliteta. Pločice za kupatilo, dnevni boravak, bazen i stepenice.
      </p>
      <div class="plo-hero__brands">
        <span class="plo-hero__brand">Tau Ceramica</span>
        <span class="plo-hero__brand-sep">·</span>
        <span class="plo-hero__brand">Arcana</span>
        <span class="plo-hero__brand-sep">·</span>
        <span class="plo-hero__brand">New Tiles</span>
        <span class="plo-hero__brand-sep">·</span>
        <span class="plo-hero__brand">Ribesalbes</span>
      </div>
      <div class="plo-hero__actions">
        <a href="#katalog" class="plo-hero__btn plo-hero__btn--primary">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
          Pogledaj kolekcije
        </a>
        <a href="tel:<?php echo esc_attr( $de_tel ); ?>" class="plo-hero__btn plo-hero__btn--secondary">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.67A2 2 0 012 .18h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
          Savjetovanje
        </a>
      </div>
    </div>

    <div class="plo-hero__trust-card">
      <div class="plo-trust-item">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
        <div>
          <strong>Direktan uvoz iz Španije</strong>
          <span>Bez posrednika – originalni brendovi</span>
        </div>
      </div>
      <div class="plo-trust-item">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        <div>
          <strong>Španski dizajn i kvalitet</strong>
          <span>Španija – lider evropske keramike</span>
        </div>
      </div>
      <div class="plo-trust-item">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
        <div>
          <strong>Na stanju u Podgorici</strong>
          <span>Isporuka odmah – bez čekanja</span>
        </div>
      </div>
      <div class="plo-trust-item">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        <div>
          <strong>Anti-slip klase R9–R11</strong>
          <span>Sertifikovane tehničke karakteristike</span>
        </div>
      </div>
    </div>
  </div>
</section>

<?php if ( ! empty( $de_brands ) && '' !== $de_listing ) : ?>
  <!-- BREND TRAKA (linkuje na ovu kategoriju filtriranu po brendu) -->
  <section class="plo-brands">
    <div class="plo-brands__inner">
      <p class="plo-brands__label">Brendovi koje zastupamo</p>
      <div class="plo-brands__grid">

        <a href="<?php echo esc_url( $de_listing ); ?>" class="plo-brand-card<?php echo empty( $de_sel_brand ) ? ' plo-brand-card--active' : ''; ?>">
          <span class="plo-brand-card__name">Sve kolekcije</span>
        </a>

        <?php foreach ( $de_brands as $de_brand ) : ?>
          <?php
          $de_active = in_array( $de_brand->slug, $de_sel_brand, true );
          $de_url    = door_expert_listing_base_url( array( 'f_brand' => array( $de_brand->slug ) ) );
          $de_desc   = isset( $de_brand_desc[ $de_brand->slug ] ) ? $de_brand_desc[ $de_brand->slug ] : '';
          $de_cnt    = (int) $de_brand->count;
          ?>
          <a href="<?php echo esc_url( $de_url ); ?>" class="plo-brand-card<?php echo $de_active ? ' plo-brand-card--active' : ''; ?>">
            <span class="plo-brand-card__flag">🇪🇸</span>
            <span class="plo-brand-card__name"><?php echo esc_html( $de_brand->name ); ?></span>
            <?php if ( $de_cnt > 0 ) : ?>
              <span class="plo-brand-card__count"><?php echo esc_html( $de_cnt . ' ' . _n( 'proizvod', 'proizvoda', $de_cnt, 'door-expert' ) ); ?></span>
            <?php endif; ?>
            <?php if ( '' !== $de_desc ) : ?>
              <span class="plo-brand-card__desc"><?php echo esc_html( $de_desc ); ?></span>
            <?php endif; ?>
          </a>
        <?php endforeach; ?>

      </div>
    </div>
  </section>
<?php endif; ?>

<?php
// Potkategorije (nosi id="katalog") + listing sa filterima.
get_template_part( $de_base . 'parts/subcats-grid', null, array( 'term' => $de_term, 'label' => 'Izaberite namjenu' ) );
get_template_part( $de_base . 'parts/product-grid', null, array( 'term' => $de_term ) );
?>

<!-- m² KALKULATOR -->
<section class="plo-calculator">
  <div class="plo-calculator__inner">
    <div class="plo-calculator__content">
      <p class="plo-calculator__eyebrow">Besplatni alat</p>
      <h2 class="plo-calculator__title">Kalkulator pločica</h2>
      <p class="plo-calculator__desc">Unesite dimenzije prostorije i automatski izračunajte koliko vam treba, sa preporučenim viškom za rezanje.</p>
      <div class="plo-calc-form">
        <div class="plo-calc-row">
          <div class="plo-calc-field">
            <label for="calc-width">Dužina prostorije (m)</label>
            <input type="number" id="calc-width" placeholder="npr. 4.5" min="0.1" max="100" step="0.1" />
          </div>
          <span class="plo-calc-x">×</span>
          <div class="plo-calc-field">
            <label for="calc-height">Širina prostorije (m)</label>
            <input type="number" id="calc-height" placeholder="npr. 3.2" min="0.1" max="100" step="0.1" />
          </div>
        </div>
        <div class="plo-calc-row plo-calc-row--options">
          <div class="plo-calc-field">
            <label for="calc-waste">Višak za rezanje</label>
            <select id="calc-waste">
              <option value="10">10% (standardno)</option>
              <option value="15">15% (kompleksni oblici)</option>
              <option value="20">20% (dijagonalno polaganje)</option>
            </select>
          </div>
          <div class="plo-calc-field">
            <label for="calc-price">Cijena po m² (EUR)</label>
            <input type="number" id="calc-price" placeholder="npr. 38" min="1" max="500" step="1" />
          </div>
        </div>
        <button type="button" class="plo-calc-btn" id="calc-btn">Izračunaj</button>
      </div>
    </div>
    <div class="plo-calculator__result" id="calc-result" style="display:none;">
      <div class="plo-calc-result-inner">
        <p class="plo-calc-result__label">Rezultat</p>
        <div class="plo-calc-result__grid">
          <div class="plo-calc-result__item">
            <span class="plo-calc-result__value" id="result-sqm">–</span>
            <span class="plo-calc-result__desc">m² prostorije</span>
          </div>
          <div class="plo-calc-result__item">
            <span class="plo-calc-result__value" id="result-total">–</span>
            <span class="plo-calc-result__desc">m² sa viškom</span>
          </div>
          <div class="plo-calc-result__item plo-calc-result__item--highlight">
            <span class="plo-calc-result__value" id="result-price">–</span>
            <span class="plo-calc-result__desc">Procijenjena cijena</span>
          </div>
        </div>
        <p class="plo-calc-result__note">* Konačna cijena zavisi od odabranog modela. Pošaljite upit za formalnu ponudu.</p>
        <a href="<?php echo esc_url( function_exists( 'door_expert_cart_url' ) ? door_expert_cart_url() : home_url( '/korpa/' ) ); ?>" class="plo-calc-result__cta">Zatražite formalnu ponudu</a>
      </div>
    </div>
  </div>
</section>

<?php
// FAQ / cross-sell / CTA – dijeljeni parts, sadržaj iz door_expert_cat_content().
if ( ! empty( $de_content['faq'] ) ) {
	get_template_part( $de_base . 'parts/faq', null, array( 'faq' => $de_content['faq'] ) );
}
if ( ! empty( $de_content['crosssell'] ) ) {
	get_template_part( $de_base . 'parts/crosssell', null, array( 'crosssell' => $de_content['crosssell'] ) );
}
if ( ! empty( $de_content['cta'] ) ) {
	get_template_part( $de_base . 'parts/cta', null, array( 'cta' => $de_content['cta'] ) );
}
