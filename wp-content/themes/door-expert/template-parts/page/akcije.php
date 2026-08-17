<?php
/**
 * Stranica: Akcije – verna konverzija prototipa akcije.html (<main>: 647-1371).
 *
 * Sekcije: breadcrumb → hero (kampanja + countdown) → filter tabovi → sort →
 *          grid (12 promo kartica) → urgency (3) → pravna napomena → pre-footer CTA.
 * JS: akcije.js (filter, countdown, add-to-cart demo, progress bar) – vec u temi.
 *
 * NAPOMENE:
 *  - Prototip ima ugnijezden <main> – ovdje su oba pretvorena u <section> (header.php
 *    drzi jedan <main>).
 *  - Promo kartice su DEMO podaci (Faza A). Produkcija: WooCommerce on-sale query
 *    + funkcionalni filteri (odlozeno, kao kategorijski gridovi).
 *  - Kampanjski datum "20. jul 2026" je iz prototipa (demo) – klijent postavlja prave.
 *  - Linkovi na detalje proizvoda vode na "#" dok WC proizvodi ne postoje.
 *  - Dijakritika vracena; em-dash → en-dash; telefon → 234 888.
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!-- BREADCRUMB -->
<nav class="breadcrumb-nav" aria-label="Putanja" style="background:#F7F3EC; border-bottom:1px solid #DDD5C4; padding:10px 0;">
  <div style="max-width:1240px; margin:0 auto; padding:0 24px;">
    <ol style="display:flex; gap:6px; align-items:center; list-style:none; margin:0; padding:0; font-family:'DM Sans',sans-serif; font-size:0.8rem; color:#1E1A16; opacity:0.6;">
      <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>" style="color:inherit; text-decoration:none;">Početna</a></li>
      <li style="opacity:0.4;">/</li>
      <li style="opacity:1; font-weight:600; color:#A07840;">Akcije</li>
    </ol>
  </div>
</nav>

<!-- HERO – CAMPAIGN BANNER -->
<section class="akcije-hero" aria-label="Aktuelna kampanja">
  <div class="akcije-hero__bg" aria-hidden="true"></div>
  <div class="akcije-hero__overlay" aria-hidden="true"></div>
  <div class="akcije-hero__inner">
    <!-- Left: campaign narrative -->
    <div class="akcije-hero__content">
      <div class="akcije-hero__label">
        <span class="akcije-hero__label-dot" aria-hidden="true"></span>
        Aktuelna ponuda – Jul 2026
      </div>
      <h1 class="akcije-hero__title">Izdvojena kolekcija sobnih vrata – Ljetni izbor</h1>
      <p class="akcije-hero__subtitle">
        Odabrani modeli iz našeg salona po posebnim cijenama. Vrata dostupna odmah – bez čekanja, bez narudžbe.
        Formalna ponuda mejlom u roku od 24 sata.
      </p>
      <div class="akcije-hero__cta-group">
        <a href="#akcije-grid" class="akcije-hero__cta-primary">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
          Pogledaj ponudu
        </a>
        <a href="<?php echo esc_url( home_url( '/kontakt/' ) ); ?>" class="akcije-hero__cta-secondary">
          Zatražite ponudu
        </a>
      </div>
    </div>
    <!-- Right: countdown -->
    <div class="akcije-hero__countdown-block" aria-label="Odbrojavanje do kraja akcije">
      <div class="akcije-hero__countdown-label">Ponuda važi još</div>
      <div class="akcije-hero__countdown-deadline" id="hero-deadline-text">do 20. jula 2026.</div>
      <div class="countdown-units" id="hero-countdown" aria-live="polite">
        <div class="countdown-unit">
          <span class="countdown-unit__number" id="cd-days">--</span>
          <span class="countdown-unit__label">Dana</span>
        </div>
        <span class="countdown-separator" aria-hidden="true">:</span>
        <div class="countdown-unit">
          <span class="countdown-unit__number" id="cd-hours">--</span>
          <span class="countdown-unit__label">Sati</span>
        </div>
        <span class="countdown-separator" aria-hidden="true">:</span>
        <div class="countdown-unit">
          <span class="countdown-unit__number" id="cd-mins">--</span>
          <span class="countdown-unit__label">Min</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FILTER TABS -->
<div class="akcije-filters" role="navigation" aria-label="Filter po kategoriji">
  <div class="akcije-filters__inner">
    <button class="akcije-filter-tab is-active" data-filter="sve" aria-pressed="true">
      Sve akcije
      <span class="akcije-filter-tab__count" id="count-sve">12</span>
    </button>
    <button class="akcije-filter-tab" data-filter="sobna-vrata" aria-pressed="false">
      Sobna vrata
      <span class="akcije-filter-tab__count" id="count-sobna">5</span>
    </button>
    <button class="akcije-filter-tab" data-filter="sigurnosna-vrata" aria-pressed="false">
      Sigurnosna vrata
      <span class="akcije-filter-tab__count" id="count-sigurnosna">2</span>
    </button>
    <button class="akcije-filter-tab" data-filter="keramicke-plocice" aria-pressed="false">
      Keramičke pločice
      <span class="akcije-filter-tab__count" id="count-keramika">3</span>
    </button>
    <button class="akcije-filter-tab" data-filter="umivaonici" aria-pressed="false">
      Umivaonici
      <span class="akcije-filter-tab__count" id="count-umivaonici">2</span>
    </button>
  </div>
</div>

<!-- Sort row -->
<div class="akcije-sort-row">
  <p class="akcije-sort-row__results">
    Prikazano <strong id="results-count">12</strong> artikala na akciji
  </p>
  <select class="akcije-sort-select" id="akcije-sort" aria-label="Sortiraj po">
    <option value="usteda-desc">Najveća ušteda</option>
    <option value="cijena-asc">Cijena: niža prema višoj</option>
    <option value="cijena-desc">Cijena: viša prema nižoj</option>
    <option value="istice-asc">Ističe uskoro</option>
  </select>
</div>

<!-- PRODUCT GRID (bio ugnijezden <main> u prototipu → section) -->
<section class="akcije-main" id="akcije-grid">
  <div class="akcije-grid" id="products-grid" role="list" aria-label="Akcijski proizvodi">

    <!-- CARD 1: Sobna vrata – Linea Bijela -->
    <article class="akcije-card" data-category="sobna-vrata" data-price="285" data-savings="85" role="listitem">
      <div class="akcije-card__image-wrap">
        <img src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=600&q=75"
             alt="Sobna vrata Linea – bijela mat" loading="lazy" width="600" height="450">
        <div class="akcije-card__badges">
          <span class="badge-discount">-23%</span>
          <span class="badge-countdown">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            Još 15 dana
          </span>
        </div>
        <button class="akcije-card__wishlist" aria-label="Dodaj u listu želja" data-product="linea-bela">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        </button>
      </div>
      <div class="akcije-card__body">
        <span class="akcije-card__category">Sobna vrata</span>
        <a href="#" class="akcije-card__name">Linea – Bijela mat</a>
        <p class="akcije-card__desc">Glatka bijela površina, moderna linija. Dostupno u 3 standardne dimenzije.</p>
        <div class="akcije-card__price-block">
          <div class="akcije-card__price-row">
            <span class="akcije-card__price-old">370 EUR</span>
            <span class="akcije-card__price-new">285 EUR</span>
          </div>
          <div class="akcije-card__savings">
            <svg class="akcije-card__savings-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
            Uštedite 85 EUR
          </div>
        </div>
      </div>
      <div class="akcije-card__footer">
        <button class="btn-add-to-cart" data-product="linea-bela">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
          Dodaj u ponudu
        </button>
        <a href="#" class="btn-view-product" aria-label="Pogledaj detalje">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        </a>
      </div>
    </article>

    <!-- CARD 2: Sobna vrata – Eleganza Orah -->
    <article class="akcije-card" data-category="sobna-vrata" data-price="320" data-savings="110" role="listitem">
      <div class="akcije-card__image-wrap">
        <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=600&q=75"
             alt="Sobna vrata Eleganza – tamni orah" loading="lazy" width="600" height="450">
        <div class="akcije-card__badges">
          <span class="badge-discount">-26%</span>
          <span class="badge-countdown">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            Još 15 dana
          </span>
        </div>
        <button class="akcije-card__wishlist" aria-label="Dodaj u listu želja" data-product="eleganza-orah">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        </button>
      </div>
      <div class="akcije-card__body">
        <span class="akcije-card__category">Sobna vrata</span>
        <a href="#" class="akcije-card__name">Eleganza – Tamni orah</a>
        <p class="akcije-card__desc">Klasična furnir tekstura, topla boja. Idealno za dnevnu sobu i hodnik.</p>
        <div class="akcije-card__price-block">
          <div class="akcije-card__price-row">
            <span class="akcije-card__price-old">430 EUR</span>
            <span class="akcije-card__price-new">320 EUR</span>
          </div>
          <div class="akcije-card__savings">
            <svg class="akcije-card__savings-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
            Uštedite 110 EUR
          </div>
        </div>
      </div>
      <div class="akcije-card__footer">
        <button class="btn-add-to-cart" data-product="eleganza-orah">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
          Dodaj u ponudu
        </button>
        <a href="#" class="btn-view-product" aria-label="Pogledaj detalje">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        </a>
      </div>
    </article>

    <!-- CARD 3: Klizna vrata – Slide Pro -->
    <article class="akcije-card" data-category="sobna-vrata" data-price="410" data-savings="90" role="listitem">
      <div class="akcije-card__image-wrap">
        <img src="https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=600&q=75"
             alt="Klizna vrata – sivi beton dekor" loading="lazy" width="600" height="450">
        <div class="akcije-card__badges">
          <span class="badge-discount">-18%</span>
          <span class="badge-novo">Novo</span>
        </div>
        <button class="akcije-card__wishlist" aria-label="Dodaj u listu želja" data-product="klizna-siva">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        </button>
      </div>
      <div class="akcije-card__body">
        <span class="akcije-card__category">Klizna vrata</span>
        <a href="#" class="akcije-card__name">Slide Pro – Beton dekor</a>
        <p class="akcije-card__desc">Klizni sistem, industrijski izgled. Štedljivo na prostoru, uvodna ponuda.</p>
        <div class="akcije-card__price-block">
          <div class="akcije-card__price-row">
            <span class="akcije-card__price-old">500 EUR</span>
            <span class="akcije-card__price-new">410 EUR</span>
          </div>
          <div class="akcije-card__savings">
            <svg class="akcije-card__savings-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
            Uštedite 90 EUR
          </div>
        </div>
      </div>
      <div class="akcije-card__footer">
        <button class="btn-add-to-cart" data-product="klizna-siva">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
          Dodaj u ponudu
        </button>
        <a href="#" class="btn-view-product" aria-label="Pogledaj detalje">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        </a>
      </div>
    </article>

    <!-- CARD 4: Staklena vrata – Vetro Frost -->
    <article class="akcije-card" data-category="sobna-vrata" data-price="490" data-savings="130" role="listitem">
      <div class="akcije-card__image-wrap">
        <img src="https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=600&q=75"
             alt="Staklena vrata – frost staklo" loading="lazy" width="600" height="450">
        <div class="akcije-card__badges">
          <span class="badge-discount">-21%</span>
          <span class="badge-countdown">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            Još 15 dana
          </span>
        </div>
        <button class="akcije-card__wishlist" aria-label="Dodaj u listu želja" data-product="staklena-frost">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        </button>
      </div>
      <div class="akcije-card__body">
        <span class="akcije-card__category">Staklena vrata</span>
        <a href="#" class="akcije-card__name">Vetro – Frost staklo</a>
        <p class="akcije-card__desc">Matiran frost efekt, privatnost uz propuštanje svjetla. Idealno za kupatilo.</p>
        <div class="akcije-card__price-block">
          <div class="akcije-card__price-row">
            <span class="akcije-card__price-old">620 EUR</span>
            <span class="akcije-card__price-new">490 EUR</span>
          </div>
          <div class="akcije-card__savings">
            <svg class="akcije-card__savings-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
            Uštedite 130 EUR
          </div>
        </div>
      </div>
      <div class="akcije-card__footer">
        <button class="btn-add-to-cart" data-product="staklena-frost">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
          Dodaj u ponudu
        </button>
        <a href="#" class="btn-view-product" aria-label="Pogledaj detalje">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        </a>
      </div>
    </article>

    <!-- CARD 5: Sobna vrata – Noir Antracit -->
    <article class="akcije-card" data-category="sobna-vrata" data-price="345" data-savings="75" role="listitem">
      <div class="akcije-card__image-wrap">
        <img src="https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=600&q=75"
             alt="Sobna vrata – antracit mat" loading="lazy" width="600" height="450">
        <div class="akcije-card__badges">
          <span class="badge-discount">-18%</span>
        </div>
        <button class="akcije-card__wishlist" aria-label="Dodaj u listu želja" data-product="antracit-mat">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        </button>
      </div>
      <div class="akcije-card__body">
        <span class="akcije-card__category">Sobna vrata</span>
        <a href="#" class="akcije-card__name">Noir – Antracit mat</a>
        <p class="akcije-card__desc">Tamni antracit mat lak, minimalistički dizajn. Savršeno uz bijele zidove.</p>
        <div class="akcije-card__price-block">
          <div class="akcije-card__price-row">
            <span class="akcije-card__price-old">420 EUR</span>
            <span class="akcije-card__price-new">345 EUR</span>
          </div>
          <div class="akcije-card__savings">
            <svg class="akcije-card__savings-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
            Uštedite 75 EUR
          </div>
        </div>
      </div>
      <div class="akcije-card__footer">
        <button class="btn-add-to-cart" data-product="antracit-mat">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
          Dodaj u ponudu
        </button>
        <a href="#" class="btn-view-product" aria-label="Pogledaj detalje">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        </a>
      </div>
    </article>

    <!-- CARD 6: Sigurnosna vrata – Fortis RC3 -->
    <article class="akcije-card" data-category="sigurnosna-vrata" data-price="890" data-savings="160" role="listitem">
      <div class="akcije-card__image-wrap">
        <img src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=600&q=75"
             alt="Sigurnosna vrata RC3 – tamni orah" loading="lazy" width="600" height="450">
        <div class="akcije-card__badges">
          <span class="badge-discount">-15%</span>
          <span class="badge-countdown">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            Još 15 dana
          </span>
        </div>
        <button class="akcije-card__wishlist" aria-label="Dodaj u listu želja" data-product="sigurnosna-rc3">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        </button>
      </div>
      <div class="akcije-card__body">
        <span class="akcije-card__category">Sigurnosna vrata</span>
        <a href="#" class="akcije-card__name">Fortis RC3 – Za stan</a>
        <p class="akcije-card__desc">Klasa otpornosti RC3. Certifikovana zaštita, 5 tačaka zaključavanja. Za stan.</p>
        <div class="akcije-card__price-block">
          <div class="akcije-card__price-row">
            <span class="akcije-card__price-old">1.050 EUR</span>
            <span class="akcije-card__price-new">890 EUR</span>
          </div>
          <div class="akcije-card__savings">
            <svg class="akcije-card__savings-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
            Uštedite 160 EUR
          </div>
        </div>
      </div>
      <div class="akcije-card__footer">
        <button class="btn-add-to-cart" data-product="sigurnosna-rc3">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
          Dodaj u ponudu
        </button>
        <a href="#" class="btn-view-product" aria-label="Pogledaj detalje">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        </a>
      </div>
    </article>

    <!-- CARD 7: Sigurnosna vrata – Fortis RC2 -->
    <article class="akcije-card" data-category="sigurnosna-vrata" data-price="640" data-savings="110" role="listitem">
      <div class="akcije-card__image-wrap">
        <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=600&q=75"
             alt="Sigurnosna vrata RC2 – bijela" loading="lazy" width="600" height="450">
        <div class="akcije-card__badges">
          <span class="badge-discount">-15%</span>
        </div>
        <button class="akcije-card__wishlist" aria-label="Dodaj u listu želja" data-product="sigurnosna-rc2">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        </button>
      </div>
      <div class="akcije-card__body">
        <span class="akcije-card__category">Sigurnosna vrata</span>
        <a href="#" class="akcije-card__name">Fortis RC2 – Bijela</a>
        <p class="akcije-card__desc">Klasa otpornosti RC2. Standardna zaštita za stan, bijela boja, diskretni izgled.</p>
        <div class="akcije-card__price-block">
          <div class="akcije-card__price-row">
            <span class="akcije-card__price-old">750 EUR</span>
            <span class="akcije-card__price-new">640 EUR</span>
          </div>
          <div class="akcije-card__savings">
            <svg class="akcije-card__savings-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
            Uštedite 110 EUR
          </div>
        </div>
      </div>
      <div class="akcije-card__footer">
        <button class="btn-add-to-cart" data-product="sigurnosna-rc2">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
          Dodaj u ponudu
        </button>
        <a href="#" class="btn-view-product" aria-label="Pogledaj detalje">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        </a>
      </div>
    </article>

    <!-- CARD 8: Keramičke pločice – Tau Ceramica -->
    <article class="akcije-card" data-category="keramicke-plocice" data-price="18" data-savings="7" role="listitem">
      <div class="akcije-card__image-wrap">
        <img src="https://images.unsplash.com/photo-1615971677499-5467cbab01c0?w=600&q=75"
             alt="Tau Ceramica – mramor efekt 60x60" loading="lazy" width="600" height="450">
        <div class="akcije-card__badges">
          <span class="badge-discount">-28%</span>
          <span class="badge-countdown">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            Još 15 dana
          </span>
        </div>
        <button class="akcije-card__wishlist" aria-label="Dodaj u listu želja" data-product="tau-mramor">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        </button>
      </div>
      <div class="akcije-card__body">
        <span class="akcije-card__category">Tau Ceramica – Španska keramika</span>
        <a href="#" class="akcije-card__name">Marmol Blanco 60x60</a>
        <p class="akcije-card__desc">Bijeli mramor efekt, mat površina. Za pod i zid. Cijena po m2.</p>
        <div class="akcije-card__price-block">
          <div class="akcije-card__price-row">
            <span class="akcije-card__price-old">25 EUR/m2</span>
            <span class="akcije-card__price-new">18 EUR/m2</span>
          </div>
          <div class="akcije-card__savings">
            <svg class="akcije-card__savings-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
            Uštedite 7 EUR/m2
          </div>
        </div>
      </div>
      <div class="akcije-card__footer">
        <button class="btn-add-to-cart" data-product="tau-mramor">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
          Dodaj u ponudu
        </button>
        <a href="#" class="btn-view-product" aria-label="Pogledaj detalje">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        </a>
      </div>
    </article>

    <!-- CARD 9: Keramičke pločice – Arcana -->
    <article class="akcije-card" data-category="keramicke-plocice" data-price="22" data-savings="8" role="listitem">
      <div class="akcije-card__image-wrap">
        <img src="https://images.unsplash.com/photo-1584622650111-993a426fbf0a?w=600&q=75"
             alt="Arcana Ceramica – terazzo 30x60" loading="lazy" width="600" height="450">
        <div class="akcije-card__badges">
          <span class="badge-discount">-27%</span>
        </div>
        <button class="akcije-card__wishlist" aria-label="Dodaj u listu želja" data-product="arcana-terazzo">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        </button>
      </div>
      <div class="akcije-card__body">
        <span class="akcije-card__category">Arcana Ceramica – Španska keramika</span>
        <a href="#" class="akcije-card__name">Terazzo Gris 30x60</a>
        <p class="akcije-card__desc">Terazzo efekt u sivim tonovima. Zidna pločica za kupatilo i kuhinju.</p>
        <div class="akcije-card__price-block">
          <div class="akcije-card__price-row">
            <span class="akcije-card__price-old">30 EUR/m2</span>
            <span class="akcije-card__price-new">22 EUR/m2</span>
          </div>
          <div class="akcije-card__savings">
            <svg class="akcije-card__savings-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
            Uštedite 8 EUR/m2
          </div>
        </div>
      </div>
      <div class="akcije-card__footer">
        <button class="btn-add-to-cart" data-product="arcana-terazzo">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
          Dodaj u ponudu
        </button>
        <a href="#" class="btn-view-product" aria-label="Pogledaj detalje">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        </a>
      </div>
    </article>

    <!-- CARD 10: Keramičke pločice – New Tiles bazen -->
    <article class="akcije-card" data-category="keramicke-plocice" data-price="28" data-savings="9" role="listitem">
      <div class="akcije-card__image-wrap">
        <img src="https://images.unsplash.com/photo-1571902943202-507ec2618e8f?w=600&q=75"
             alt="New Tiles – pločice za bazen plave" loading="lazy" width="600" height="450">
        <div class="akcije-card__badges">
          <span class="badge-discount">-24%</span>
          <span class="badge-countdown">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            Još 15 dana
          </span>
        </div>
        <button class="akcije-card__wishlist" aria-label="Dodaj u listu želja" data-product="new-tiles-bazen">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        </button>
      </div>
      <div class="akcije-card__body">
        <span class="akcije-card__category">New Tiles – Pločice za bazen</span>
        <a href="#" class="akcije-card__name">Aqua Blue 25x25</a>
        <p class="akcije-card__desc">Specijalne pločice za bazen, antiklizna površina. Španska kvaliteta.</p>
        <div class="akcije-card__price-block">
          <div class="akcije-card__price-row">
            <span class="akcije-card__price-old">37 EUR/m2</span>
            <span class="akcije-card__price-new">28 EUR/m2</span>
          </div>
          <div class="akcije-card__savings">
            <svg class="akcije-card__savings-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
            Uštedite 9 EUR/m2
          </div>
        </div>
      </div>
      <div class="akcije-card__footer">
        <button class="btn-add-to-cart" data-product="new-tiles-bazen">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
          Dodaj u ponudu
        </button>
        <a href="#" class="btn-view-product" aria-label="Pogledaj detalje">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        </a>
      </div>
    </article>

    <!-- CARD 11: Umivaonici – Bathco Oval -->
    <article class="akcije-card" data-category="umivaonici" data-price="195" data-savings="55" role="listitem">
      <div class="akcije-card__image-wrap">
        <img src="https://images.unsplash.com/photo-1552321554-5fefe8c9ef14?w=600&q=75"
             alt="Bathco umivaonik – oval bijeli" loading="lazy" width="600" height="450">
        <div class="akcije-card__badges">
          <span class="badge-discount">-22%</span>
          <span class="badge-countdown">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            Još 15 dana
          </span>
        </div>
        <button class="akcije-card__wishlist" aria-label="Dodaj u listu želja" data-product="bathco-oval">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        </button>
      </div>
      <div class="akcije-card__body">
        <span class="akcije-card__category">Bathco – Španski dizajn</span>
        <a href="#" class="akcije-card__name">Mueble Oval – Bijeli</a>
        <p class="akcije-card__desc">Ovalni nadgradni umivaonik, bijeli sjaj. Španski brend Bathco, keramika.</p>
        <div class="akcije-card__price-block">
          <div class="akcije-card__price-row">
            <span class="akcije-card__price-old">250 EUR</span>
            <span class="akcije-card__price-new">195 EUR</span>
          </div>
          <div class="akcije-card__savings">
            <svg class="akcije-card__savings-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
            Uštedite 55 EUR
          </div>
        </div>
      </div>
      <div class="akcije-card__footer">
        <button class="btn-add-to-cart" data-product="bathco-oval">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
          Dodaj u ponudu
        </button>
        <a href="#" class="btn-view-product" aria-label="Pogledaj detalje">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        </a>
      </div>
    </article>

    <!-- CARD 12: Umivaonici – Bathco Kameni -->
    <article class="akcije-card" data-category="umivaonici" data-price="340" data-savings="85" role="listitem">
      <div class="akcije-card__image-wrap">
        <img src="https://images.unsplash.com/photo-1584622650111-993a426fbf0a?w=600&q=75"
             alt="Bathco kameni umivaonik" loading="lazy" width="600" height="450">
        <div class="akcije-card__badges">
          <span class="badge-discount">-20%</span>
          <span class="badge-novo">Novo</span>
        </div>
        <button class="akcije-card__wishlist" aria-label="Dodaj u listu želja" data-product="bathco-kameni">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        </button>
      </div>
      <div class="akcije-card__body">
        <span class="akcije-card__category">Bathco – Kameni umivaonici</span>
        <a href="#" class="akcije-card__name">Stone Oval – Prirodni kamen</a>
        <p class="akcije-card__desc">Kameni umivaonik, prirodni materijal, jedinstven izgled. Uvodna ponuda.</p>
        <div class="akcije-card__price-block">
          <div class="akcije-card__price-row">
            <span class="akcije-card__price-old">425 EUR</span>
            <span class="akcije-card__price-new">340 EUR</span>
          </div>
          <div class="akcije-card__savings">
            <svg class="akcije-card__savings-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
            Uštedite 85 EUR
          </div>
        </div>
      </div>
      <div class="akcije-card__footer">
        <button class="btn-add-to-cart" data-product="bathco-kameni">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
          Dodaj u ponudu
        </button>
        <a href="#" class="btn-view-product" aria-label="Pogledaj detalje">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        </a>
      </div>
    </article>

  </div><!-- /akcije-grid -->
</section>

<!-- URGENCY SECTION -->
<section class="akcije-urgency" aria-label="Ponude koje uskoro isticu">
  <div class="akcije-urgency__inner">
    <div class="akcije-urgency__header">
      <h2 class="akcije-urgency__title">Ističe uskoro</h2>
      <span class="akcije-urgency__subtitle">Ponuda važi do 20. jula 2026.</span>
    </div>
    <div class="akcije-urgency__grid">

      <!-- Urgency card 1 -->
      <div class="urgency-card">
        <img class="urgency-card__image"
             src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&q=70"
             alt="Linea Bijela mat" loading="lazy" width="80" height="80">
        <div class="urgency-card__info">
          <h3 class="urgency-card__name">Linea – Bijela mat</h3>
          <p class="urgency-card__deadline">Ponuda važi <strong>još 15 dana</strong></p>
          <div class="urgency-card__price-row">
            <span class="urgency-card__price-old">370 EUR</span>
            <span class="urgency-card__price-new">285 EUR</span>
          </div>
          <div class="urgency-card__progress">
            <div class="urgency-card__progress-bar">
              <div class="urgency-card__progress-fill" style="width: 50%"></div>
            </div>
            <span class="urgency-card__progress-label">15 od 30 dana prošlo</span>
          </div>
        </div>
      </div>

      <!-- Urgency card 2 -->
      <div class="urgency-card">
        <img class="urgency-card__image"
             src="https://images.unsplash.com/photo-1615971677499-5467cbab01c0?w=200&q=70"
             alt="Tau Ceramica Marmol Blanco" loading="lazy" width="80" height="80">
        <div class="urgency-card__info">
          <h3 class="urgency-card__name">Marmol Blanco 60x60</h3>
          <p class="urgency-card__deadline">Ponuda važi <strong>još 15 dana</strong></p>
          <div class="urgency-card__price-row">
            <span class="urgency-card__price-old">25 EUR/m2</span>
            <span class="urgency-card__price-new">18 EUR/m2</span>
          </div>
          <div class="urgency-card__progress">
            <div class="urgency-card__progress-bar">
              <div class="urgency-card__progress-fill" style="width: 50%"></div>
            </div>
            <span class="urgency-card__progress-label">15 od 30 dana prošlo</span>
          </div>
        </div>
      </div>

      <!-- Urgency card 3 -->
      <div class="urgency-card">
        <img class="urgency-card__image"
             src="https://images.unsplash.com/photo-1552321554-5fefe8c9ef14?w=200&q=70"
             alt="Bathco Mueble Oval" loading="lazy" width="80" height="80">
        <div class="urgency-card__info">
          <h3 class="urgency-card__name">Mueble Oval – Bijeli</h3>
          <p class="urgency-card__deadline">Ponuda važi <strong>još 15 dana</strong></p>
          <div class="urgency-card__price-row">
            <span class="urgency-card__price-old">250 EUR</span>
            <span class="urgency-card__price-new">195 EUR</span>
          </div>
          <div class="urgency-card__progress">
            <div class="urgency-card__progress-bar">
              <div class="urgency-card__progress-fill" style="width: 50%"></div>
            </div>
            <span class="urgency-card__progress-label">15 od 30 dana prošlo</span>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- TRANSPARENCY / LEGAL NOTE -->
<div class="akcije-legal">
  <div class="akcije-legal__note" role="note">
    <svg class="akcije-legal__icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
    <p class="akcije-legal__text">
      <strong>Napomena o cijenama:</strong> Prethodna cijena prikazana uz svaki artikal predstavlja najnižu cijenu tog artikla u poslednjih 30 dana, u skladu sa Zakonom o zaštiti potrošača Crne Gore. Sve akcijske cijene važe dok traju zalihe ili do isteka navedenog roka. Door Expert zadržava pravo izmjene cijena bez prethodne najave.
    </p>
  </div>
</div>

<!-- PRE-FOOTER CTA STRIP -->
<section class="pre-footer" style="margin-top: 64px;">
  <div class="pre-footer__inner">
    <div class="pre-footer__text">
      <h2 class="pre-footer__title">Posjetite nas u salonu ili zatražite ponudu</h2>
      <p class="pre-footer__subtitle">Vrata i keramika dostupni odmah. Formalna ponuda mejlom bez obaveze.</p>
    </div>
    <div class="pre-footer__actions">
      <a href="<?php echo esc_url( home_url( '/kontakt/' ) ); ?>" class="pre-footer__btn pre-footer__btn--primary">Zatražite ponudu</a>
      <a href="tel:+38269234888" class="pre-footer__btn pre-footer__btn--secondary">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.6 1.22h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.8a16 16 0 0 0 6.29 6.29l.95-.95a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
        +382 69 234 888
      </a>
    </div>
  </div>
</section>
