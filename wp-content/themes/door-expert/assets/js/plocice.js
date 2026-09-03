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
