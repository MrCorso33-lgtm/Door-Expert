# m2 kalkulator na kategoriji (Keramicke plocice)

- **Naziv:** Kalkulator plocica na kategorijskoj stranici (`.plo-calculator`)
- **Vraca se u:** `wp-content/themes/door-expert/template-parts/category/parent/keramicke-plocice.php`
  kao zasebna sekcija **izmedju** `parts/product-grid` i FAQ bloka: odmah poslije
  `get_template_part( $de_base . 'parts/product-grid', ... );` a prije komentara
  `// FAQ / cross-sell / CTA`.
- **CSS:** `assets/css/plocice.css` glavni blok se vraca odmah poslije `.prod-badge--antislip`
  pravila; responsive fragmenti se vracaju u **postojece** `@media (max-width: 1024px)` i
  `@media (max-width: 768px)` upite na dnu fajla.
- **JS:** `assets/js/plocice.js` **cijeli fajl je obrisan**, jer je kalkulator bio jedina
  preostala funkcija u njemu. Vraca se kao nov fajl. Enqueue u `functions.php`
  (`$family_assets` blok) je guardovan sa `file_exists()`, pa ce se JS ponovo ucitati sam
  cim fajl postoji **ne treba mijenjati functions.php**.
- **Sta predstavlja:** tamna sekcija sa formom (duzina x sirina prostorije, procenat viska za
  rezanje, rucni unos cijene po m2) i rezultatom u tri polja: m2 prostorije, m2 sa viskom,
  procijenjena cijena. CTA vodi na korpu.
- **Razlog sklanjanja:** kalkulator ostaje samo na **single product** stranici. Tamo je bolji
  jer zna cijenu proizvoda i **automatski upisuje izracunate m2 u polje kolicine** za upit
  (to trazi i `DOCS/ISTRAZIVACKA OSNOVA/Istrazivacka-osnova - keramicke-plocice.md:97`).
  Na kategoriji nema izabranog proizvoda, pa je korisnik morao **rucno unijeti cijenu**
  procjena je bila onoliko tacna koliko i njegova pretpostavka.
- **Datum sklanjanja:** 2026-09-04

> **Prije vracanja procitati:** `DOCS/CRO/CRO - keramicke-plocice.md:78` trazi kalkulator
> "uz svaki proizvod **i na samoj stranici kategorije**" (prioritet: visok), a `:103`
> predlaze A/B test pozicije (iznad grida vs. unutar grida). Ako se vraca, razmotriti
> **izbacivanje polja za cijenu** i prikaz samo kolicine (`CRO:53` trazi rezultat u obliku
> "Potrebno vam je X kutija"), cime nestaje izmisljeni broj.

> **Ne mijesati sa PDP kalkulatorom.** Single product koristi DRUGE klase (`.product-calc*`
> u `product.css`) i drugi JS (`product.js`). Ovaj kod je nezavisan uklanjanje odavde
> nije diralo PDP.

---

## HTML (PHP) ide u `template-parts/category/parent/keramicke-plocice.php`

```php
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
```

---

## CSS glavni blok, ide u `assets/css/plocice.css`

```css
/* ── m² Calculator ── */
.plo-calculator {
  background: var(--color-antracit);
  padding: var(--space-20) 0;
}
.plo-calculator__inner {
  max-width: var(--container-max);
  margin: 0 auto;
  padding: 0 var(--space-8);
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--space-16);
  align-items: start;
}
.plo-calculator__eyebrow {
  font-family: var(--font-ui);
  font-size: var(--text-xs);
  font-weight: 600;
  letter-spacing: 0.15em;
  text-transform: uppercase;
  color: var(--color-sampanjac);
  margin-bottom: var(--space-3);
}
.plo-calculator__title {
  font-family: var(--font-display);
  font-size: var(--text-3xl);
  font-weight: 400;
  color: #FFFFFF;
  margin-bottom: var(--space-4);
}
.plo-calculator__desc {
  font-family: var(--font-body);
  font-size: var(--text-base);
  color: rgba(255,255,255,0.65);
  line-height: 1.65;
  margin-bottom: var(--space-8);
}
.plo-calc-form {
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
}
.plo-calc-row {
  display: grid;
  grid-template-columns: 1fr auto 1fr;
  gap: var(--space-4);
  align-items: end;
}
.plo-calc-row--options {
  grid-template-columns: 1fr 1fr;
}
.plo-calc-field {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
}
.plo-calc-field label {
  font-family: var(--font-ui);
  font-size: var(--text-xs);
  font-weight: 500;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: rgba(255,255,255,0.5);
}
.plo-calc-field input,
.plo-calc-field select {
  background: rgba(255,255,255,0.08);
  border: 1px solid rgba(255,255,255,0.15);
  color: #FFFFFF;
  padding: var(--space-3) var(--space-4);
  font-family: var(--font-body);
  font-size: var(--text-base);
  outline: none;
  transition: border-color var(--duration-fast) var(--ease-out);
}
.plo-calc-field input:focus,
.plo-calc-field select:focus {
  border-color: var(--color-sampanjac);
}
.plo-calc-field input::placeholder { color: rgba(255,255,255,0.3); }
.plo-calc-field select option { background: var(--color-antracit); color: #FFFFFF; }
.plo-calc-x {
  font-family: var(--font-display);
  font-size: 1.5rem;
  color: var(--color-sampanjac);
  padding-bottom: var(--space-3);
  text-align: center;
}
.plo-calc-btn {
  padding: var(--space-4) var(--space-8);
  background: var(--color-jantar);
  color: #FFFFFF;
  border: none;
  font-family: var(--font-ui);
  font-size: var(--text-sm);
  font-weight: 600;
  letter-spacing: 0.04em;
  cursor: pointer;
  transition: background var(--duration-base) var(--ease-out);
  align-self: flex-start;
}
.plo-calc-btn:hover { background: var(--color-accent-hover); }

/* Calculator result */
.plo-calculator__result {
  background: rgba(247, 243, 236, 0.06);
  border: 1px solid rgba(201, 169, 110, 0.3);
  padding: var(--space-8);
}
.plo-calc-result__label {
  font-family: var(--font-ui);
  font-size: var(--text-xs);
  font-weight: 600;
  letter-spacing: 0.15em;
  text-transform: uppercase;
  color: var(--color-sampanjac);
  margin-bottom: var(--space-6);
}
.plo-calc-result__grid {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: var(--space-4);
  margin-bottom: var(--space-6);
}
.plo-calc-result__item {
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
  padding: var(--space-4);
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
}
.plo-calc-result__item--highlight {
  background: rgba(201, 169, 110, 0.12);
  border-color: rgba(201, 169, 110, 0.3);
}
.plo-calc-result__value {
  font-family: var(--font-display);
  font-size: var(--text-2xl);
  font-weight: 500;
  color: #FFFFFF;
  line-height: 1;
}
.plo-calc-result__item--highlight .plo-calc-result__value {
  color: var(--color-sampanjac);
}
.plo-calc-result__desc {
  font-family: var(--font-ui);
  font-size: var(--text-xs);
  color: rgba(255,255,255,0.5);
}
.plo-calc-result__note {
  font-family: var(--font-ui);
  font-size: var(--text-xs);
  color: rgba(255,255,255,0.4);
  font-style: italic;
  margin-bottom: var(--space-5);
  line-height: 1.5;
}
.plo-calc-result__cta {
  display: block;
  text-align: center;
  padding: var(--space-4);
  background: var(--color-jantar);
  color: #FFFFFF;
  font-family: var(--font-ui);
  font-size: var(--text-sm);
  font-weight: 600;
  text-decoration: none;
  transition: background var(--duration-base) var(--ease-out);
}
.plo-calc-result__cta:hover { background: var(--color-accent-hover); }
```

## CSS responsive fragmenti

Vracaju se u **postojece** media upite na dnu `plocice.css`:

```css
  .plo-calculator__inner { grid-template-columns: 1fr; gap: var(--space-10); }
  .plo-calc-row { grid-template-columns: 1fr; }
  .plo-calc-x { display: none; }
  .plo-calc-row--options { grid-template-columns: 1fr; }
  .plo-calc-result__grid { grid-template-columns: 1fr; }
  .plo-calculator__inner { padding: 0 var(--space-5); }
```

---

## JS cio fajl `assets/js/plocice.js`

```js
/**
 * Keramičke pločice – m² kalkulator.
 *
 * Raniji sadržaj (292 linije) bio je prototipski simulator: klijentsko filtriranje
 * .prod-card kartica po brendu/potkategoriji/atributima, klijentski sort, mobilni
 * filter drawer, wishlist i "add to cart" preko localStorage. Sve to sada radi
 * server-side:
 *   - filteri i sort  -> inc/shop.php (woocommerce_product_query)
 *   - accordion i mobilni toggle filtera -> assets/js/prodavnica.js
 *   - korpa i badž    -> inc/quote-cart.php + assets/js/header.js
 *
 * Zadržan je samo kalkulator, jer je stvarna funkcionalnost bez servera.
 * VAŽNO: stari kod je hvatao .prod-card i .plo-brand-card i skrivao kartice
 * klijentski – uz server-renderovane rezultate to bi sakrivalo prave proizvode.
 */
( function () {
  'use strict';

  var calcBtn = document.getElementById( 'calc-btn' );
  if ( ! calcBtn ) {
    return;
  }

  var calcResult = document.getElementById( 'calc-result' );

  function val( id ) {
    var el = document.getElementById( id );
    return el ? parseFloat( el.value ) : 0;
  }

  function shake( ids ) {
    ids.forEach( function ( id ) {
      var el = document.getElementById( id );
      if ( ! el ) {
        return;
      }
      el.style.borderColor = '#c0392b';
      setTimeout( function () {
        el.style.borderColor = '';
      }, 1200 );
    } );
  }

  calcBtn.addEventListener( 'click', function () {
    var width  = val( 'calc-width' ) || 0;
    var height = val( 'calc-height' ) || 0;
    var waste  = val( 'calc-waste' ) || 10;
    var price  = val( 'calc-price' ) || 0;

    if ( width <= 0 || height <= 0 ) {
      shake( [ 'calc-width', 'calc-height' ] );
      return;
    }

    var sqm   = width * height;
    var total = sqm * ( 1 + waste / 100 );

    var elSqm   = document.getElementById( 'result-sqm' );
    var elTotal = document.getElementById( 'result-total' );
    var elPrice = document.getElementById( 'result-price' );

    if ( elSqm ) {
      elSqm.textContent = sqm.toFixed( 2 ) + ' m²';
    }
    if ( elTotal ) {
      elTotal.textContent = total.toFixed( 2 ) + ' m²';
    }
    if ( elPrice ) {
      elPrice.textContent = price > 0 ? ( total * price ).toFixed( 2 ) + ' EUR' : '–';
    }

    if ( calcResult ) {
      calcResult.style.display = '';
      calcResult.scrollIntoView( { behavior: 'smooth', block: 'nearest' } );
    }
  } );
}() );
```
