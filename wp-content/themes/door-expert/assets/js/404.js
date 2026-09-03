/**
 * 404 stranica – UI interakcije.
 *
 * PRETRAGA JOŠ NE POSTOJI U TEMI. Search box na 404 stranici je vizuelno prisutan i spreman,
 * ali namjerno ne radi ništa: nema search.php, a header overlay šalje na nepostojeći /pretraga/
 * sa pogrešnim parametrom (name="q" umjesto WP-ovog name="s").
 *
 * Žiči se kad se portuje Saya komponenta #16 "Search, six passes"
 * (DOCS/FOR DOOR EXPERT/01-AUDIT-REPORT.md:181). Tada ovdje ide pravi submit, a u istom
 * prolazu se popravlja i pretraga u headeru.
 *
 * Raniji sadržaj ovog fajla bio je prototipski simulator: doSearch() je mapirao ključne riječi
 * na statične .html fajlove (sobna-vrata.html itd.) i radio window.location.href. To bi na
 * WordPressu vodilo u novi 404, pa je uklonjeno. Uklonjen je i blok koji je čitao
 * localStorage('de_cart') – badž korpe sada radi header.js preko WooCommerce sesije.
 */
( function () {
  'use strict';

  var input = document.getElementById( 'e404SearchInput' );
  if ( ! input ) {
    return;
  }

  // Chip-ovi samo popunjavaju polje. Bez redirekcije dok pretraga ne proradi.
  document.querySelectorAll( '.e404-search__chip' ).forEach( function ( chip ) {
    chip.addEventListener( 'click', function () {
      input.value = chip.dataset.query || '';
      input.focus();
    } );
  } );
}() );
