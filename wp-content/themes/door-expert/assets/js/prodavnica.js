/**
 * Prodavnica (shop) – interakcije filtera i grida.
 * Izvučeno iz inline <script> prototipa prodavnica.html.
 *
 * NAPOMENA (Faza A / demo): filtriranje je klijentsko (display toggle po kategoriji)
 * nad statičnim karticama. U produkciji listing/filtriranje ide kroz WP_Query /
 * WooCommerce (bez JetSmartFilters), pa se ova skripta zamjenjuje/proširuje.
 *
 * Header scroll efekat NIJE ovdje – rješava ga globalni assets/js/header.js.
 */
( function () {
  'use strict';

  // Filter grupa – toggle (accordion)
  document.querySelectorAll( '.shop-filter-group__toggle' ).forEach( function ( btn ) {
    btn.addEventListener( 'click', function () {
      btn.closest( '.shop-filter-group' ).classList.toggle( 'is-open' );
    } );
  } );

  // Mobilni toggle sidebara filtera
  var filterToggle = document.getElementById( 'filterToggle' );
  var shopFilters = document.getElementById( 'shopFilters' );
  if ( filterToggle && shopFilters ) {
    filterToggle.addEventListener( 'click', function () {
      shopFilters.classList.toggle( 'is-open' );
      this.textContent = shopFilters.classList.contains( 'is-open' ) ? 'Sakrij filtere' : 'Prikazi filtere';
    } );
  }

  // Hero kategorijske pilule – filtriraj kartice po kategoriji
  var heroCatBtns = document.querySelectorAll( '.shop-hero__cat-btn' );
  var shopCards = document.querySelectorAll( '.shop-grid .prod-card' );
  var countEl = document.querySelector( '.shop-toolbar__count strong' );

  heroCatBtns.forEach( function ( btn ) {
    btn.addEventListener( 'click', function () {
      heroCatBtns.forEach( function ( b ) {
        b.classList.remove( 'is-active' );
      } );
      btn.classList.add( 'is-active' );

      var cat = btn.dataset.cat;
      var visible = 0;
      shopCards.forEach( function ( card ) {
        if ( 'all' === cat || card.dataset.cat === cat ) {
          card.style.display = '';
          visible++;
        } else {
          card.style.display = 'none';
        }
      } );

      if ( countEl ) {
        countEl.textContent = visible;
      }
    } );
  } );

  // Boja – toggle aktivnog swatch-a
  document.querySelectorAll( '.shop-filter-color' ).forEach( function ( swatch ) {
    swatch.addEventListener( 'click', function () {
      swatch.classList.toggle( 'is-active' );
    } );
  } );
}() );
