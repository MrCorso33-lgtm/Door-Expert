<?php
/**
 * Stranica: Prodavnica (arhiva svih proizvoda) - verna konverzija prototipa prodavnica.html.
 *
 * Sekcije: shop-hero (kategorijske pilule) -> shop-main (filter sidebar + toolbar + product grid + paginacija).
 * CSS: assets/css/prodavnica.css (izvučen iz inline <style>, mobile-first) + category.css (prod-card, zavisnost).
 * JS:  assets/js/prodavnica.js (izvučen iz inline <script>).
 *
 * Popravke prototipa (Manus artefakti - NE prenosimo):
 *   - product.html (btn-view) -> "#" placeholder (single-proizvod još ne postoji).
 *   - Snižena cijena: prod-card__price--old/prod-card__discount -> prod-card__price-old/prod-card__price-save
 *     (stvarna konvencija iz category.css; prototipske klase nisu stilizovane).
 *   - ASCII tekst -> ijekavica sa dijakriticima (standard sajta, kao montaza.php).
 *
 * NAPOMENA (Faza A / demo): product grid, brojači i filteri su STATIČNI placeholder (86 proizvoda,
 * Unsplash slike). U produkciji: grid = WP_Query/WooCommerce loop nad product_cat, filteri kroz
 * WP_Query (bez JetSmartFilters). Router (page.php) i ovaj part ostaju isti - mijenja se izvor kartica.
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!-- SHOP HERO -->
<section class="shop-hero">
  <div class="shop-hero__eyebrow">Kompletan asortiman</div>
  <h1 class="shop-hero__title">Prodavnica</h1>
  <p class="shop-hero__desc">Pregledajte cijelu ponudu vrata, keramičkih pločica i dekorativnih umivaonika. Filtrirajte po kategoriji, brendu, boji ili dimenzijama.</p>
  <div class="shop-hero__cats">
    <button class="shop-hero__cat-btn is-active" data-cat="all">
      <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
      Sve <span class="shop-hero__count">86</span>
    </button>
    <button class="shop-hero__cat-btn" data-cat="vrata">
      <svg viewBox="0 0 24 24"><rect x="3" y="2" width="14" height="20" rx="1"/><circle cx="14" cy="12" r="1.5"/></svg>
      Vrata <span class="shop-hero__count">42</span>
    </button>
    <button class="shop-hero__cat-btn" data-cat="keramika">
      <svg viewBox="0 0 24 24"><rect x="2" y="2" width="9" height="9"/><rect x="13" y="2" width="9" height="9"/><rect x="2" y="13" width="9" height="9"/><rect x="13" y="13" width="9" height="9"/></svg>
      Keramika <span class="shop-hero__count">32</span>
    </button>
    <button class="shop-hero__cat-btn" data-cat="umivaonici">
      <svg viewBox="0 0 24 24"><ellipse cx="12" cy="14" rx="9" ry="5"/><path d="M3 14V8a9 5 0 0118 0v6"/></svg>
      Umivaonici <span class="shop-hero__count">12</span>
    </button>
  </div>
</section>

<!-- MAIN: FILTERI + GRID -->
<div class="shop-main">

  <!-- Mobilni toggle filtera -->
  <button class="shop-filters-toggle" id="filterToggle">
    <svg style="width:14px;height:14px;vertical-align:middle;margin-right:6px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="12" x2="14" y2="12"/><line x1="4" y1="18" x2="9" y2="18"/></svg>
    Prikaži filtere
  </button>

  <!-- FILTER SIDEBAR -->
  <aside class="shop-filters" id="shopFilters">
    <div class="shop-filters__header">
      <h2 class="shop-filters__title">Filteri</h2>
      <button class="shop-filters__clear">Očisti sve</button>
    </div>

    <!-- Kategorija -->
    <div class="shop-filter-group is-open">
      <button class="shop-filter-group__toggle">
        Kategorija
        <svg viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
      </button>
      <div class="shop-filter-group__body">
        <label class="shop-filter-option">
          <input type="checkbox" data-filter="cat" value="sobna-vrata" />
          Sobna vrata
          <span class="shop-filter-option__count">24</span>
        </label>
        <label class="shop-filter-option">
          <input type="checkbox" data-filter="cat" value="sigurnosna-vrata" />
          Sigurnosna vrata
          <span class="shop-filter-option__count">18</span>
        </label>
        <label class="shop-filter-option">
          <input type="checkbox" data-filter="cat" value="keramicke-plocice" />
          Keramičke pločice
          <span class="shop-filter-option__count">32</span>
        </label>
        <label class="shop-filter-option">
          <input type="checkbox" data-filter="cat" value="umivaonici" />
          Dekorativni umivaonici
          <span class="shop-filter-option__count">12</span>
        </label>
      </div>
    </div>

    <!-- Brend -->
    <div class="shop-filter-group is-open">
      <button class="shop-filter-group__toggle">
        Brend
        <svg viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
      </button>
      <div class="shop-filter-group__body">
        <label class="shop-filter-option">
          <input type="checkbox" data-filter="brand" value="tau" />
          Tau Ceramica
          <span class="shop-filter-option__count">18</span>
        </label>
        <label class="shop-filter-option">
          <input type="checkbox" data-filter="brand" value="arcana" />
          Arcana Ceramica
          <span class="shop-filter-option__count">8</span>
        </label>
        <label class="shop-filter-option">
          <input type="checkbox" data-filter="brand" value="ribesalbes" />
          Ceramica Ribesalbes
          <span class="shop-filter-option__count">4</span>
        </label>
        <label class="shop-filter-option">
          <input type="checkbox" data-filter="brand" value="newtiles" />
          New Tiles
          <span class="shop-filter-option__count">6</span>
        </label>
        <label class="shop-filter-option">
          <input type="checkbox" data-filter="brand" value="bathco" />
          Bathco
          <span class="shop-filter-option__count">12</span>
        </label>
      </div>
    </div>

    <!-- Boja -->
    <div class="shop-filter-group">
      <button class="shop-filter-group__toggle">
        Boja
        <svg viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
      </button>
      <div class="shop-filter-group__body">
        <div class="shop-filter-colors">
          <button class="shop-filter-color" style="background:#FFFFFF;" title="Bijela" data-color="bijela"></button>
          <button class="shop-filter-color" style="background:#F5E6C8;" title="Krema" data-color="krema"></button>
          <button class="shop-filter-color" style="background:#8B5E3C;" title="Orah" data-color="orah"></button>
          <button class="shop-filter-color" style="background:#3D2B1F;" title="Wenge" data-color="wenge"></button>
          <button class="shop-filter-color" style="background:#D4C5A9;" title="Hrast" data-color="hrast"></button>
          <button class="shop-filter-color" style="background:#2C2C2C;" title="Antracit" data-color="antracit"></button>
          <button class="shop-filter-color" style="background:#C9B896;" title="Beige" data-color="beige"></button>
          <button class="shop-filter-color" style="background:#6B8E9B;" title="Plava" data-color="plava"></button>
        </div>
      </div>
    </div>

    <!-- Cijena -->
    <div class="shop-filter-group">
      <button class="shop-filter-group__toggle">
        Cijena (EUR)
        <svg viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
      </button>
      <div class="shop-filter-group__body">
        <div class="shop-filter-price">
          <input type="number" placeholder="Od" min="0" />
          <span>-</span>
          <input type="number" placeholder="Do" min="0" />
          <span>EUR</span>
        </div>
      </div>
    </div>

    <!-- Dimenzije -->
    <div class="shop-filter-group">
      <button class="shop-filter-group__toggle">
        Dimenzije
        <svg viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
      </button>
      <div class="shop-filter-group__body">
        <label class="shop-filter-option">
          <input type="checkbox" data-filter="dim" value="60x200" />
          60 x 200 cm
        </label>
        <label class="shop-filter-option">
          <input type="checkbox" data-filter="dim" value="70x200" />
          70 x 200 cm
        </label>
        <label class="shop-filter-option">
          <input type="checkbox" data-filter="dim" value="80x200" />
          80 x 200 cm
        </label>
        <label class="shop-filter-option">
          <input type="checkbox" data-filter="dim" value="30x60" />
          30 x 60 cm (pločice)
        </label>
        <label class="shop-filter-option">
          <input type="checkbox" data-filter="dim" value="60x120" />
          60 x 120 cm (pločice)
        </label>
      </div>
    </div>

    <!-- Dostupnost -->
    <div class="shop-filter-group">
      <button class="shop-filter-group__toggle">
        Dostupnost
        <svg viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
      </button>
      <div class="shop-filter-group__body">
        <label class="shop-filter-option">
          <input type="checkbox" data-filter="stock" value="na-stanju" />
          Na stanju
          <span class="shop-filter-option__count">68</span>
        </label>
        <label class="shop-filter-option">
          <input type="checkbox" data-filter="stock" value="po-narudzbi" />
          Po narudžbi
          <span class="shop-filter-option__count">18</span>
        </label>
      </div>
    </div>

  </aside>

  <!-- CONTENT AREA -->
  <div class="shop-content">

    <!-- Toolbar -->
    <div class="shop-toolbar">
      <div class="shop-toolbar__count">Prikazano <strong>86</strong> proizvoda</div>
      <div class="shop-toolbar__actions">
        <select class="shop-toolbar__sort">
          <option>Sortiraj: Preporučeno</option>
          <option>Cijena: niža prvo</option>
          <option>Cijena: viša prvo</option>
          <option>Najnovije</option>
          <option>Najpopularnije</option>
        </select>
        <div class="shop-toolbar__view-btns">
          <button class="shop-toolbar__view-btn is-active" title="Grid"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg></button>
          <button class="shop-toolbar__view-btn" title="Lista"><svg viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg></button>
        </div>
      </div>
    </div>

    <!-- Product Grid -->
    <div class="shop-grid">

      <!-- VRATA: Snijeg bijela -->
      <article class="prod-card" data-cat="vrata" data-brand="" data-color="bijela" data-price="330">
        <div class="prod-card__img-wrap">
          <img class="prod-card__img" src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=500&q=80" alt="Snijeg bijela" loading="lazy" />
          <div class="prod-card__badges">
            <span class="prod-badge prod-badge--stock"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg> Na stanju</span>
          </div>
          <button class="prod-card__wishlist" aria-label="Dodaj u listu želja"><svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg></button>
        </div>
        <div class="prod-card__body">
          <span class="prod-card__cat">Sobna vrata</span>
          <h3 class="prod-card__name">Snijeg bijela</h3>
          <div class="prod-card__attrs"><span class="prod-card__attr">Medijapan 8mm</span><span class="prod-card__attr">Bijela</span></div>
          <div class="prod-card__price-row"><span class="prod-card__price">330 &#8364;</span></div>
          <div class="prod-card__cta"><button class="prod-card__btn-cart"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg> Dodaj u ponudu</button><a href="#" class="prod-card__btn-view" aria-label="Pogledaj"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></a></div>
        </div>
      </article>

      <!-- VRATA: Orah -->
      <article class="prod-card" data-cat="vrata" data-brand="" data-color="orah" data-price="330">
        <div class="prod-card__img-wrap">
          <img class="prod-card__img" src="https://images.unsplash.com/photo-1600566753086-00f18fb6b3ea?w=500&q=80" alt="Orah" loading="lazy" />
          <div class="prod-card__badges">
            <span class="prod-badge prod-badge--stock"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg> Na stanju</span>
          </div>
          <button class="prod-card__wishlist" aria-label="Dodaj u listu želja"><svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg></button>
        </div>
        <div class="prod-card__body">
          <span class="prod-card__cat">Sobna vrata</span>
          <h3 class="prod-card__name">Orah</h3>
          <div class="prod-card__attrs"><span class="prod-card__attr">Medijapan 8mm</span><span class="prod-card__attr">Orah</span></div>
          <div class="prod-card__price-row"><span class="prod-card__price">330 &#8364;</span></div>
          <div class="prod-card__cta"><button class="prod-card__btn-cart"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg> Dodaj u ponudu</button><a href="#" class="prod-card__btn-view" aria-label="Pogledaj"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></a></div>
        </div>
      </article>

      <!-- KERAMIKA: Travertino Beige -->
      <article class="prod-card" data-cat="keramika" data-brand="tau" data-color="beige" data-price="28">
        <div class="prod-card__img-wrap">
          <img class="prod-card__img" src="https://images.unsplash.com/photo-1600210492493-0946911123ea?w=500&q=80" alt="Travertino Beige" loading="lazy" />
          <div class="prod-card__badges">
            <span class="prod-badge prod-badge--stock"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg> Na stanju</span>
            <span class="prod-badge prod-badge--new">Novo</span>
          </div>
          <button class="prod-card__wishlist" aria-label="Dodaj u listu želja"><svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg></button>
        </div>
        <div class="prod-card__body">
          <span class="prod-card__cat">Keramičke pločice · Tau Ceramica</span>
          <h3 class="prod-card__name">Travertino Beige</h3>
          <div class="prod-card__attrs"><span class="prod-card__attr">60 x 120 cm</span><span class="prod-card__attr">Podne</span><span class="prod-card__attr">Mat</span></div>
          <div class="prod-card__price-row"><span class="prod-card__price">28 &#8364;/m&sup2;</span></div>
          <div class="prod-card__cta"><button class="prod-card__btn-cart"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg> Dodaj u ponudu</button><a href="#" class="prod-card__btn-view" aria-label="Pogledaj"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></a></div>
        </div>
      </article>

      <!-- SIGURNOSNA: Standard -->
      <article class="prod-card" data-cat="vrata" data-brand="" data-color="antracit" data-price="559">
        <div class="prod-card__img-wrap">
          <img class="prod-card__img" src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=500&q=80" alt="Standard sigurnosna vrata" loading="lazy" />
          <div class="prod-card__badges">
            <span class="prod-badge prod-badge--stock"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg> Na stanju</span>
          </div>
          <button class="prod-card__wishlist" aria-label="Dodaj u listu želja"><svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg></button>
        </div>
        <div class="prod-card__body">
          <span class="prod-card__cat">Sigurnosna vrata</span>
          <h3 class="prod-card__name">Standard</h3>
          <div class="prod-card__attrs"><span class="prod-card__attr">Klasa 3</span><span class="prod-card__attr">RC3 sertifikat</span><span class="prod-card__attr">Antracit</span></div>
          <div class="prod-card__price-row"><span class="prod-card__price">559 &#8364;</span></div>
          <div class="prod-card__cta"><button class="prod-card__btn-cart"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg> Dodaj u ponudu</button><a href="#" class="prod-card__btn-view" aria-label="Pogledaj"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></a></div>
        </div>
      </article>

      <!-- UMIVAONIK: Mueble Oval -->
      <article class="prod-card" data-cat="umivaonici" data-brand="bathco" data-color="bijela" data-price="320">
        <div class="prod-card__img-wrap">
          <img class="prod-card__img" src="https://images.unsplash.com/photo-1552321554-5fefe8c9ef14?w=500&q=80" alt="Mueble Oval" loading="lazy" />
          <div class="prod-card__badges">
            <span class="prod-badge prod-badge--new">Novo</span>
          </div>
          <button class="prod-card__wishlist" aria-label="Dodaj u listu želja"><svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg></button>
        </div>
        <div class="prod-card__body">
          <span class="prod-card__cat">Umivaonici · Bathco</span>
          <h3 class="prod-card__name">Mueble Oval</h3>
          <div class="prod-card__attrs"><span class="prod-card__attr">Prirodni kamen</span><span class="prod-card__attr">Oval</span><span class="prod-card__attr">Bijeli</span></div>
          <div class="prod-card__price-row"><span class="prod-card__price-old">380 &#8364;</span><span class="prod-card__price">320 &#8364;</span><span class="prod-card__price-save">-16%</span></div>
          <div class="prod-card__cta"><button class="prod-card__btn-cart"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg> Dodaj u ponudu</button><a href="#" class="prod-card__btn-view" aria-label="Pogledaj"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></a></div>
        </div>
      </article>

      <!-- KERAMIKA: Aqua Pool -->
      <article class="prod-card" data-cat="keramika" data-brand="newtiles" data-color="plava" data-price="35">
        <div class="prod-card__img-wrap">
          <img class="prod-card__img" src="https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=500&q=80" alt="Aqua Pool" loading="lazy" />
          <div class="prod-card__badges">
            <span class="prod-badge prod-badge--stock"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg> Na stanju</span>
          </div>
          <button class="prod-card__wishlist" aria-label="Dodaj u listu želja"><svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg></button>
        </div>
        <div class="prod-card__body">
          <span class="prod-card__cat">Keramičke pločice · New Tiles</span>
          <h3 class="prod-card__name">Aqua Pool</h3>
          <div class="prod-card__attrs"><span class="prod-card__attr">15 x 15 cm</span><span class="prod-card__attr">Bazenske</span><span class="prod-card__attr">Sjaj</span></div>
          <div class="prod-card__price-row"><span class="prod-card__price">35 &#8364;/m&sup2;</span></div>
          <div class="prod-card__cta"><button class="prod-card__btn-cart"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg> Dodaj u ponudu</button><a href="#" class="prod-card__btn-view" aria-label="Pogledaj"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></a></div>
        </div>
      </article>

      <!-- VRATA: Klizna Krema -->
      <article class="prod-card" data-cat="vrata" data-brand="" data-color="krema" data-price="380">
        <div class="prod-card__img-wrap">
          <img class="prod-card__img" src="https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=500&q=80" alt="Klizna Krema" loading="lazy" />
          <div class="prod-card__badges">
            <span class="prod-badge prod-badge--stock"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg> Na stanju</span>
          </div>
          <button class="prod-card__wishlist" aria-label="Dodaj u listu želja"><svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg></button>
        </div>
        <div class="prod-card__body">
          <span class="prod-card__cat">Klizna vrata</span>
          <h3 class="prod-card__name">Klizna Krema</h3>
          <div class="prod-card__attrs"><span class="prod-card__attr">Medijapan 8mm</span><span class="prod-card__attr">Krema</span><span class="prod-card__attr">Klizni sistem</span></div>
          <div class="prod-card__price-row"><span class="prod-card__price">380 &#8364;</span></div>
          <div class="prod-card__cta"><button class="prod-card__btn-cart"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg> Dodaj u ponudu</button><a href="#" class="prod-card__btn-view" aria-label="Pogledaj"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></a></div>
        </div>
      </article>

      <!-- KERAMIKA: Marble Luxe -->
      <article class="prod-card" data-cat="keramika" data-brand="arcana" data-color="bijela" data-price="42">
        <div class="prod-card__img-wrap">
          <img class="prod-card__img" src="https://images.unsplash.com/photo-1600607687644-c7171b42498f?w=500&q=80" alt="Marble Luxe Bianco" loading="lazy" />
          <div class="prod-card__badges">
            <span class="prod-badge prod-badge--new">Novo</span>
          </div>
          <button class="prod-card__wishlist" aria-label="Dodaj u listu želja"><svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg></button>
        </div>
        <div class="prod-card__body">
          <span class="prod-card__cat">Keramičke pločice · Arcana Ceramica</span>
          <h3 class="prod-card__name">Marble Luxe Bianco</h3>
          <div class="prod-card__attrs"><span class="prod-card__attr">60 x 120 cm</span><span class="prod-card__attr">Zidne</span><span class="prod-card__attr">Sjaj</span></div>
          <div class="prod-card__price-row"><span class="prod-card__price">42 &#8364;/m&sup2;</span></div>
          <div class="prod-card__cta"><button class="prod-card__btn-cart"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg> Dodaj u ponudu</button><a href="#" class="prod-card__btn-view" aria-label="Pogledaj"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></a></div>
        </div>
      </article>

      <!-- VRATA: Elena -->
      <article class="prod-card" data-cat="vrata" data-brand="" data-color="hrast" data-price="285">
        <div class="prod-card__img-wrap">
          <img class="prod-card__img" src="https://images.unsplash.com/photo-1600585154526-990dced4db0d?w=500&q=80" alt="Elena" loading="lazy" />
          <div class="prod-card__badges">
            <span class="prod-badge prod-badge--stock"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg> Na stanju</span>
          </div>
          <button class="prod-card__wishlist" aria-label="Dodaj u listu želja"><svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg></button>
        </div>
        <div class="prod-card__body">
          <span class="prod-card__cat">Sobna vrata</span>
          <h3 class="prod-card__name">Elena</h3>
          <div class="prod-card__attrs"><span class="prod-card__attr">Medijapan 8mm</span><span class="prod-card__attr">Stilski hrast</span></div>
          <div class="prod-card__price-row"><span class="prod-card__price">285 &#8364;</span></div>
          <div class="prod-card__cta"><button class="prod-card__btn-cart"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg> Dodaj u ponudu</button><a href="#" class="prod-card__btn-view" aria-label="Pogledaj"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></a></div>
        </div>
      </article>

    </div>

    <!-- Paginacija -->
    <nav class="shop-pagination" aria-label="Stranice">
      <button class="shop-pagination__btn shop-pagination__btn--arrow" aria-label="Prethodna"><svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg></button>
      <button class="shop-pagination__btn is-active">1</button>
      <button class="shop-pagination__btn">2</button>
      <button class="shop-pagination__btn">3</button>
      <button class="shop-pagination__btn">4</button>
      <button class="shop-pagination__btn">...</button>
      <button class="shop-pagination__btn">9</button>
      <button class="shop-pagination__btn shop-pagination__btn--arrow" aria-label="Sljedeća"><svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></button>
    </nav>

  </div>
</div>
