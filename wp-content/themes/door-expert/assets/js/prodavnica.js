/**
 * Prodavnica + kategorijski listing – UI interakcije filter sidebara.
 *
 * Filtriranje/sortiranje/paginacija su SERVER-SIDE (GET forma => WP_Query, inc/shop.php).
 * Sidebar markup dolazi iz plugina WC Filter Configurator (klase .wcfc-*), dorađen u
 * inc/filters.php. Ova skripta radi samo ono što plugin ne isporučuje: otvaranje grupa,
 * mobilni toggle, dupli cjenovni slider i vizuelni feedback swatch-a.
 *
 * Sve je progressive enhancement – bez JS-a checkboxovi i swatch i dalje šalju formu,
 * grupe ostaju otvorene, a slider šalje svoje granice jer ima name atribute.
 */
( function () {
  'use strict';

  var form = document.getElementById( 'shopFilters' );

  /* ── Accordion grupa ───────────────────────────────────────
   * Plugin renderuje naslov kao <h3>, a on sam po sebi nije fokusabilan,
   * pa mu ovdje dodajemo ulogu dugmeta i tastaturu.
   */
  document.querySelectorAll( '.wcfc-group__title, .wcfc-section__title' ).forEach( function ( title ) {
    var group = title.parentElement;
    if ( ! group ) {
      return;
    }

    function setState() {
      title.setAttribute( 'aria-expanded', group.classList.contains( 'is-collapsed' ) ? 'false' : 'true' );
    }

    function toggle() {
      group.classList.toggle( 'is-collapsed' );
      setState();
    }

    title.setAttribute( 'role', 'button' );
    title.setAttribute( 'tabindex', '0' );
    setState();

    title.addEventListener( 'click', toggle );
    title.addEventListener( 'keydown', function ( event ) {
      if ( 'Enter' === event.key || ' ' === event.key || 'Spacebar' === event.key ) {
        event.preventDefault();
        toggle();
      }
    } );
  } );

  /* ── Mobilni toggle sidebara ───────────────────────────────── */
  var filterToggle = document.getElementById( 'filterToggle' );
  if ( filterToggle && form ) {
    filterToggle.setAttribute( 'aria-expanded', 'false' );
    filterToggle.setAttribute( 'aria-controls', 'shopFilters' );

    filterToggle.addEventListener( 'click', function () {
      var open = form.classList.toggle( 'is-open' );
      filterToggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
      filterToggle.textContent = open ? 'Sakrij filtere' : 'Prikaži filtere';
    } );
  }

  /* ── Cjenovni raspon (dva preklopljena range inputa) ───────── */
  var priceMin  = document.getElementById( 'wcfc-price-min' );
  var priceMax  = document.getElementById( 'wcfc-price-max' );
  var priceFill = document.getElementById( 'wcfc-price-fill' );
  var minLabel  = document.getElementById( 'wcfc-price-min-label' );
  var maxLabel  = document.getElementById( 'wcfc-price-max-label' );

  if ( priceMin && priceMax && priceFill ) {
    var ceiling = parseInt( priceMax.getAttribute( 'max' ), 10 ) || 0;
    var step    = parseInt( priceMax.getAttribute( 'step' ), 10 ) || 1;

    // Tačka kao razdjelnik hiljada (1.250), bez oslanjanja na toLocaleString.
    var format = function ( value ) {
      return String( value ).replace( /\B(?=(\d{3})+(?!\d))/g, '.' );
    };

    var sync = function () {
      var low  = parseInt( priceMin.value, 10 ) || 0;
      var high = parseInt( priceMax.value, 10 ) || 0;

      // Palci ne smiju da se preskoče; gura se onaj koji korisnik NE drži.
      if ( low > high - step ) {
        if ( document.activeElement === priceMin ) {
          low = Math.max( 0, high - step );
          priceMin.value = low;
        } else {
          high = Math.min( ceiling, low + step );
          priceMax.value = high;
        }
      }

      var left  = ceiling ? ( low / ceiling ) * 100 : 0;
      var right = ceiling ? ( high / ceiling ) * 100 : 100;

      priceFill.style.left  = left + '%';
      priceFill.style.width = Math.max( 0, right - left ) + '%';

      if ( minLabel ) {
        minLabel.textContent = format( low );
      }
      if ( maxLabel ) {
        maxLabel.textContent = format( high );
      }
    };

    priceMin.addEventListener( 'input', sync );
    priceMax.addEventListener( 'input', sync );
    sync();
  }

  /* ── Submit: ne šalji cijenu ako nije dirana ────────────────
   * Range input uvijek ima vrijednost, pa bi pun raspon (0 do max) zauvijek
   * visio u URL-u i "Očisti sve" bi izgledalo kao da nije odradilo posao.
   * Disable-ovana kontrola se ne šalje, a odmah je vraćamo za slučaj da
   * pregledač otkaže navigaciju (Escape, sporo mrežno okruženje).
   */
  if ( form ) {
    form.addEventListener( 'submit', function () {
      var untouched = [ priceMin, priceMax ].filter( function ( input ) {
        if ( ! input ) {
          return false;
        }
        return input.value === input.getAttribute( 'data-de-default' );
      } );

      untouched.forEach( function ( input ) {
        input.disabled = true;
      } );

      window.setTimeout( function () {
        untouched.forEach( function ( input ) {
          input.disabled = false;
        } );
      }, 0 );
    } );
  }

  /* ── Swatch boje – fallback za pregledače bez CSS :has ─────── */
  document.querySelectorAll( '.wcfc-swatch' ).forEach( function ( label ) {
    var input = label.querySelector( 'input[type="checkbox"]' );
    if ( ! input ) {
      return;
    }

    input.addEventListener( 'change', function () {
      label.classList.toggle( 'is-active', input.checked );
    } );
  } );
}() );
