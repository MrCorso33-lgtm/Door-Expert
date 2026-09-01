/**
 * Single product (PDP) – UI interakcije.
 *
 * Server (single-product.php + template-parts/product/single.php) renderuje SVE iz WC_Product;
 * ova skripta radi samo klijentski UI:
 *   1. Galerija: thumbnail -> glavna slika + lightbox (zoom)
 *   2. FAQ accordion
 *   3. Količina +/- (WC add-to-cart forma)
 *   4. m² kalkulator (samo pločice)
 *
 * NAPOMENA: raniji product.js je bio demo simulator (PRODUCT_DATA + data-type toggle +
 * klijentsko menjanje cijene po varijanti). To je zamijenjeno – podaci sad dolaze iz WooCommerce-a.
 * Header scroll = globalni header.js.
 */
( function () {
  'use strict';

  /* ── Galerija: thumbnail -> glavna slika ────────────────── */
  var mainImg = document.getElementById( 'gallery-main-img' );
  var thumbs = document.querySelectorAll( '.product-gallery__thumb' );

  thumbs.forEach( function ( thumb ) {
    thumb.addEventListener( 'click', function () {
      var full = thumb.getAttribute( 'data-full' );
      if ( full && mainImg ) {
        mainImg.src = full;
      }
      thumbs.forEach( function ( t ) {
        t.classList.remove( 'is-active' );
      } );
      thumb.classList.add( 'is-active' );
    } );
  } );

  /* ── Lightbox (zoom glavne slike) ───────────────────────── */
  var galleryMain = document.getElementById( 'gallery-main' );
  var lightbox = document.getElementById( 'product-lightbox' );
  var lightboxImg = document.getElementById( 'lightbox-img' );
  var lightboxClose = document.getElementById( 'lightbox-close' );

  function openLightbox() {
    if ( ! mainImg || ! lightbox || ! lightboxImg ) {
      return;
    }
    lightboxImg.src = mainImg.src;
    lightboxImg.alt = mainImg.alt;
    lightbox.classList.add( 'is-open' );
    document.body.style.overflow = 'hidden';
  }

  function closeLightbox() {
    if ( ! lightbox ) {
      return;
    }
    lightbox.classList.remove( 'is-open' );
    document.body.style.overflow = '';
    if ( galleryMain ) {
      galleryMain.focus();
    }
  }

  if ( galleryMain ) {
    galleryMain.addEventListener( 'click', openLightbox );
    galleryMain.addEventListener( 'keydown', function ( e ) {
      if ( 'Enter' === e.key || ' ' === e.key ) {
        e.preventDefault();
        openLightbox();
      }
    } );
  }
  if ( lightboxClose ) {
    lightboxClose.addEventListener( 'click', closeLightbox );
  }
  if ( lightbox ) {
    lightbox.addEventListener( 'click', function ( e ) {
      if ( e.target === lightbox ) {
        closeLightbox();
      }
    } );
  }
  document.addEventListener( 'keydown', function ( e ) {
    if ( 'Escape' === e.key && lightbox && lightbox.classList.contains( 'is-open' ) ) {
      closeLightbox();
    }
  } );

  /* ── FAQ accordion ──────────────────────────────────────── */
  document.querySelectorAll( '.product-faq__question' ).forEach( function ( btn ) {
    btn.addEventListener( 'click', function () {
      var item = btn.closest( '.product-faq__item' );
      if ( ! item ) {
        return;
      }
      var isOpen = item.classList.toggle( 'is-open' );
      btn.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
    } );
  } );

  /* ── Količina +/- ───────────────────────────────────────── */
  var qtyInput = document.getElementById( 'qty-input' );
  var qtyMinus = document.getElementById( 'qty-minus' );
  var qtyPlus = document.getElementById( 'qty-plus' );

  function clampQty( val ) {
    var min = parseInt( qtyInput.getAttribute( 'min' ) || '1', 10 );
    var max = parseInt( qtyInput.getAttribute( 'max' ) || '99', 10 );
    if ( isNaN( val ) || val < min ) {
      return min;
    }
    if ( val > max ) {
      return max;
    }
    return val;
  }

  if ( qtyInput && qtyMinus && qtyPlus ) {
    qtyMinus.addEventListener( 'click', function () {
      qtyInput.value = clampQty( parseInt( qtyInput.value, 10 ) - 1 );
    } );
    qtyPlus.addEventListener( 'click', function () {
      qtyInput.value = clampQty( parseInt( qtyInput.value, 10 ) + 1 );
    } );
    qtyInput.addEventListener( 'change', function () {
      qtyInput.value = clampQty( parseInt( qtyInput.value, 10 ) );
    } );
  }

  /* ── m² kalkulator (samo pločice) ───────────────────────── */
  var calc = document.getElementById( 'tile-calculator' );
  if ( calc ) {
    var calcW = document.getElementById( 'calc-width' );
    var calcL = document.getElementById( 'calc-length' );
    var calcResult = document.getElementById( 'calc-result' );
    var pricePerM2 = parseFloat( calc.getAttribute( 'data-price' ) || '0' );

    function recalc() {
      var w = parseFloat( calcW.value );
      var l = parseFloat( calcL.value );
      if ( isNaN( w ) || isNaN( l ) || w <= 0 || l <= 0 ) {
        calcResult.innerHTML = 'Unesite dimenzije prostorije za izračun';
        return;
      }
      var area = w * l;
      var withReserve = area * 1.1; // +10% rezerve za rezanje
      var html = 'Površina: <strong>' + area.toFixed( 2 ) + ' m²</strong> · sa 10% rezerve: <strong>' + withReserve.toFixed( 2 ) + ' m²</strong>';
      if ( pricePerM2 > 0 ) {
        var total = withReserve * pricePerM2;
        html += ' · procjena: <strong>' + total.toFixed( 2 ) + ' EUR</strong>';
      }
      // Predlozi upis količine (m²) u polje za upit.
      if ( qtyInput ) {
        qtyInput.value = Math.ceil( withReserve );
      }
      calcResult.innerHTML = html;
    }

    if ( calcW && calcL && calcResult ) {
      calcW.addEventListener( 'input', recalc );
      calcL.addEventListener( 'input', recalc );
    }
  }
}() );
