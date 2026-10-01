/**
 * Početna – "Odabrani za vas ovog mjeseca": tabovi + "Prikaži više".
 *
 * Server renderuje sve istaknute proizvode (template-parts/home/featured.php); ovdje
 * se samo odlučuje koji su vidljivi. Obrazac iz Saya projekta
 * (js/homepage-featured-products.js):
 *   - prikazuje se prvih N (data-visible na sekciji, 4),
 *   - "Prikaži više" otkriva još N, a kad je sve prikazano postaje "Prikaži manje"
 *     i vraća na N,
 *   - tab resetuje na N.
 * Bez AJAX-a. Sakrivene kartice imaju loading="lazy", pa slike ne skidaju dok se ne
 * pokažu. Bez JavaScripta se vidi sve, što je ispravno ponašanje.
 *
 * Ranije je ovdje bio demo iz prototipa: lažno "dodaj u korpu" (natpis "Dodano u
 * korpu" + brojač +1, bez upisa u korpu) nad lažnim karticama. Kartice su sada prave
 * i dodavanje radi WooCommerce.
 */
( function () {
  'use strict';

  var section = document.querySelector( '.featured[data-visible]' );
  var grid = document.getElementById( 'featured-grid' );

  if ( ! section || ! grid ) {
    return;
  }

  var items = Array.prototype.slice.call( grid.querySelectorAll( '.featured__item' ) );

  if ( ! items.length ) {
    return;
  }

  var moreBtn = document.getElementById( 'featured-more' );
  var tabs = Array.prototype.slice.call( section.querySelectorAll( '.featured__tab' ) );
  var visible = parseInt( section.getAttribute( 'data-visible' ), 10 ) || 4;
  var filter = 'sve';
  var limit = visible;
  var total = 0;

  function matches( item ) {
    return 'sve' === filter || filter === item.getAttribute( 'data-cat' );
  }

  function render( animate ) {
    var shown = 0;

    total = 0;

    items.forEach( function ( item ) {
      if ( ! matches( item ) ) {
        item.hidden = true;
        return;
      }

      total++;

      if ( shown >= limit ) {
        item.hidden = true;
        return;
      }

      var wasHidden = item.hidden;

      item.hidden = false;

      if ( animate && wasHidden ) {
        item.classList.remove( 'is-entering' );
        void item.offsetWidth; // Restart animacije.
        item.style.animationDelay = ( ( shown % visible ) * 40 ) + 'ms';
        item.classList.add( 'is-entering' );
      }

      shown++;
    } );

    if ( ! moreBtn ) {
      return;
    }

    if ( total <= visible ) {
      moreBtn.hidden = true;
      return;
    }

    var allShown = limit >= total;

    moreBtn.hidden = false;
    moreBtn.textContent = allShown ? 'Prikaži manje' : 'Prikaži više';
    moreBtn.setAttribute( 'aria-expanded', allShown ? 'true' : 'false' );
  }

  tabs.forEach( function ( tab, index ) {
    tab.addEventListener( 'click', function () {
      if ( filter === tab.getAttribute( 'data-filter' ) ) {
        return;
      }

      filter = tab.getAttribute( 'data-filter' );
      limit = visible;

      tabs.forEach( function ( other ) {
        var on = other === tab;

        other.classList.toggle( 'is-active', on );
        other.setAttribute( 'aria-selected', on ? 'true' : 'false' );
      } );

      render( true );
    } );

    // Strelice lijevo/desno mijenjaju tab, kao kod nativnih tablist kontrola.
    tab.addEventListener( 'keydown', function ( e ) {
      if ( 'ArrowRight' !== e.key && 'ArrowLeft' !== e.key ) {
        return;
      }

      e.preventDefault();

      var step = 'ArrowRight' === e.key ? 1 : -1;
      var next = tabs[ ( index + step + tabs.length ) % tabs.length ];

      next.focus();
      next.click();
    } );
  } );

  if ( moreBtn ) {
    moreBtn.addEventListener( 'click', function () {
      if ( limit < total ) {
        limit = Math.min( limit + visible, total );
        render( true );
        return;
      }

      limit = visible;
      render( false );
      // Poslije "Prikaži manje" kupac bi ostao daleko ispod sekcije.
      section.scrollIntoView( { behavior: 'smooth', block: 'start' } );
    } );
  }

  /*
   * Lista želja na karticama. U prodavnici je ovo u category.js, koji se na
   * početnoj ne učitava, pa bi dugme ovdje bilo mrtvo. Isto ponašanje kao tamo.
   */
  grid.addEventListener( 'click', function ( e ) {
    var btn = e.target.closest( '.prod-card__wishlist' );

    if ( ! btn ) {
      return;
    }

    e.preventDefault();
    btn.classList.toggle( 'active' );
    btn.setAttribute( 'aria-label', btn.classList.contains( 'active' ) ? 'Ukloni iz liste želja' : 'Dodaj u listu želja' );
  } );

  render( false );
}() );
