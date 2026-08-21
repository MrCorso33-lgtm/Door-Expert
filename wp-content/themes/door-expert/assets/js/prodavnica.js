/**
 * Prodavnica (WooCommerce shop arhiva) – UI interakcije.
 *
 * Filtriranje/sortiranje/paginacija su SERVER-SIDE (GET forma => WP_Query, inc/shop.php).
 * Ova skripta radi samo UI: accordion grupa, mobilni toggle sidebara, vizuelni feedback
 * swatch-a boje (fallback za pregledače bez CSS :has). Header scroll = globalni header.js.
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

  // Boja – vizuelni feedback (checkbox je unutar labela; CSS :has pokriva moderne pregledače)
  document.querySelectorAll( '.shop-filter-color' ).forEach( function ( label ) {
    var input = label.querySelector( 'input[type="checkbox"]' );
    if ( ! input ) {
      return;
    }
    input.addEventListener( 'change', function () {
      label.classList.toggle( 'is-active', input.checked );
    } );
  } );
}() );
