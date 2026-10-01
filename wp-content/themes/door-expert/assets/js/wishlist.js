/**
 * Lista sačuvanih proizvoda ("Sačuvaj za projekat").
 *
 *   1. Srce na kartici proizvoda (.prod-card__wishlist) i dugme na PDP-u
 *      (.btn-product-wishlist) – oba nose data-wishlist="<ID proizvoda>".
 *   2. Srce sa brojem u headeru ([data-wishlist-count]).
 *   3. Tab "Sačuvano" u korpi – ista kartica kao u prodavnici (product-card.php);
 *      srce na njoj skida proizvod sa liste, "Dodaj u ponudu" je pravo WC dodavanje.
 *
 * Lista živi samo u pregledaču (localStorage) i sadrži SAMO ID-jeve. Kartice za tab
 * crta server (inc/wishlist.php), pa cijena i slika su uvijek trenutne.
 * Po uzoru na Saya wishlist.js. Prebacivanje tabova je u korpa.js.
 */
( function () {
  'use strict';

  var KEY  = 'door_expert_wishlist';
  var MAX  = 100; // Ista granica kao DOOR_EXPERT_WISHLIST_MAX u inc/wishlist.php.
  var cfg  = window.doorExpertWishlistData || {};
  var SELECTOR = '[data-wishlist]';

  /* ── Memorija ───────────────────────────────────────────── */
  function read() {
    try {
      var list = JSON.parse( window.localStorage.getItem( KEY ) || '[]' );
      return Array.isArray( list ) ? list.map( Number ).filter( function ( id ) {
        return id > 0;
      } ) : [];
    } catch ( e ) {
      return [];
    }
  }

  function write( list ) {
    try {
      window.localStorage.setItem( KEY, JSON.stringify( list.slice( -MAX ) ) );
    } catch ( e ) {
      // Privatni prozor ili pun localStorage – srce i dalje reaguje do osvježavanja.
    }
    paintSavedCount();
  }

  function has( id ) {
    return -1 !== read().indexOf( id );
  }

  function add( id ) {
    var list = read();
    if ( -1 === list.indexOf( id ) ) {
      list.push( id );
      write( list );
    }
  }

  function remove( id ) {
    write( read().filter( function ( x ) {
      return x !== id;
    } ) );
  }

  /* ── Obavještenje ───────────────────────────────────────── */
  var toastEl    = null;
  var toastTimer = null;

  function toast( message, link ) {
    if ( ! toastEl ) {
      toastEl = document.createElement( 'div' );
      toastEl.className = 'de-toast';
      toastEl.setAttribute( 'role', 'status' );
      toastEl.setAttribute( 'aria-live', 'polite' );
      document.body.appendChild( toastEl );
    }

    toastEl.textContent = message;
    if ( link ) {
      var a = document.createElement( 'a' );
      a.className = 'de-toast__link';
      a.href = link.url;
      a.textContent = link.text;
      toastEl.appendChild( a );
    }

    toastEl.classList.add( 'is-visible' );
    window.clearTimeout( toastTimer );
    toastTimer = window.setTimeout( function () {
      toastEl.classList.remove( 'is-visible' );
    }, link ? 4000 : 2800 );
  }

  /* ── Srce / "Sačuvaj za projekat" ───────────────────────── */
  function paintButton( btn, on ) {
    // Kartica i PDP imaju različitu klasu za aktivno stanje (product-card.css / product.css).
    btn.classList.toggle( btn.classList.contains( 'btn-product-wishlist' ) ? 'is-active' : 'active', on );
    btn.setAttribute( 'aria-pressed', on ? 'true' : 'false' );
    if ( btn.hasAttribute( 'aria-label' ) ) {
      btn.setAttribute( 'aria-label', on ? 'Ukloni iz sačuvanih' : 'Sačuvaj za projekat' );
    }
  }

  function syncButtons() {
    var list = read();
    document.querySelectorAll( SELECTOR ).forEach( function ( btn ) {
      paintButton( btn, -1 !== list.indexOf( Number( btn.dataset.wishlist ) ) );
    } );
  }

  document.addEventListener( 'click', function ( e ) {
    var btn = e.target.closest( SELECTOR );
    if ( ! btn ) {
      return;
    }

    e.preventDefault();
    var id = Number( btn.dataset.wishlist );
    if ( ! id ) {
      return;
    }

    var on = ! has( id );
    if ( on ) {
      add( id );
    } else {
      remove( id );
    }

    // Isti proizvod može biti na stranici više puta (npr. "Odabrani za vas" i listing).
    syncButtons();

    var savedCard = ! on && grid && grid.contains( btn ) ? btn.closest( '.wishlist-item' ) : null;
    if ( savedCard ) {
      dropCard( savedCard );
    }

    // Lista se vidi samo u korpi, pa obavještenje kaže gdje da je kupac nađe.
    toast(
      on ? 'Sačuvano za projekat.' : 'Uklonjeno iz sačuvanih.',
      on && cfg.savedUrl ? { url: cfg.savedUrl, text: 'Pogledaj listu' } : null
    );
  } );

  /*
   * Prodavnica i kategorije mijenjaju grid AJAX-om (prodavnica.js), a naslovna
   * tabovima (featured.js) – nove kartice moraju dobiti stanje srca.
   */
  var syncQueued = false;
  if ( window.MutationObserver ) {
    new window.MutationObserver( function () {
      if ( syncQueued ) {
        return;
      }
      syncQueued = true;
      window.requestAnimationFrame( function () {
        syncQueued = false;
        syncButtons();
      } );
    } ).observe( document.body, { childList: true, subtree: true } );
  }

  /*
   * Dodato u ponudu = skinuto sa liste. Server poslije svakog uspješnog dodavanja
   * (PDP forma, AJAX kartica, sticky traka) upiše ID u kratak kolačić
   * (inc/wishlist.php); ovdje ga pokupimo i obrišemo.
   */
  function consumeAdded() {
    var c = cfg.added || {};
    if ( ! c.cookie ) {
      return;
    }

    var match = document.cookie.match( new RegExp( '(?:^|; )' + c.cookie + '=([^;]*)' ) );
    if ( ! match ) {
      return;
    }

    var added = decodeURIComponent( match[1] ).split( ',' ).map( Number ).filter( function ( id ) {
      return id > 0;
    } );

    document.cookie = c.cookie + '=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=' + c.path + ( c.domain ? '; domain=' + c.domain : '' );

    if ( ! added.length ) {
      return;
    }

    /*
     * Potvrda dodavanja – umjesto WC okvira na engleskom (inc/quote-cart.php ga gasi).
     * U samoj korpi link ka korpi nema smisla; tamo raste broj u tabu.
     */
    toast( 'Dodato u ponudu.', ! panel && cfg.cartUrl ? { url: cfg.cartUrl, text: 'Pogledaj ponudu' } : null );

    write( read().filter( function ( id ) {
      return -1 === added.indexOf( id );
    } ) );
    syncButtons();

    // Ako je dodato iz taba "Sačuvano", kartica odmah odlazi.
    if ( grid ) {
      added.forEach( function ( id ) {
        var btn = grid.querySelector( '[data-wishlist-remove="' + id + '"]' );
        if ( btn ) {
          dropCard( btn.closest( '.wishlist-item' ) );
        }
      } );
    }
  }

  // AJAX dodavanje sa kartice (WC jQuery događaj) – kolačić je stigao u odgovoru.
  // Tek na DOMContentLoaded: jQuery se u footeru može učitati poslije ovog fajla.
  document.addEventListener( 'DOMContentLoaded', function () {
    if ( window.jQuery ) {
      window.jQuery( document.body ).on( 'added_to_cart', consumeAdded );
    }
  } );

  /* ── Tab "Sačuvano" u korpi ─────────────────────────────── */
  var panel = document.getElementById( 'panel-sacuvano' );
  var grid  = document.getElementById( 'wishlist-grid' );
  var empty = document.getElementById( 'wishlist-empty' );

  function paintSavedCount() {
    var count = read().length;

    var el = document.getElementById( 'tab-sacuvano-count' );
    if ( el ) {
      el.textContent = count;
    }

    // Srce u headeru: broj kao kod korpe, sakriven na nuli.
    document.querySelectorAll( '[data-wishlist-count]' ).forEach( function ( badge ) {
      badge.textContent = count > 99 ? '99+' : count;
      badge.style.display = count > 0 ? 'flex' : 'none';
    } );
  }

  // Lista promijenjena u drugom tabu pregledača – srca i brojač prate.
  window.addEventListener( 'storage', function ( e ) {
    if ( KEY === e.key ) {
      syncButtons();
      paintSavedCount();
    }
  } );

  function toggleEmpty() {
    if ( ! grid || ! empty ) {
      return;
    }
    var none = ! grid.querySelector( '.prod-card' );
    grid.hidden = none;
    empty.classList.toggle( 'visible', none );
  }

  function post( action, body ) {
    var data = new URLSearchParams( body );
    data.append( 'action', action );
    data.append( 'nonce', cfg.nonce || '' );

    return window.fetch( cfg.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: data.toString()
    } ).then( function ( res ) {
      return res.json();
    } );
  }

  function loadSaved() {
    var list = read();
    paintSavedCount();

    if ( ! list.length ) {
      grid.removeAttribute( 'aria-busy' );
      toggleEmpty();
      return;
    }

    // Najnovije sačuvano prvo.
    post( 'door_expert_wishlist_cards', { ids: list.slice().reverse().join( ',' ) } ).then( function ( res ) {
      grid.removeAttribute( 'aria-busy' );
      if ( ! res || ! res.success ) {
        return;
      }

      // Obrisani ili skriveni proizvodi više ne broje se u tabu.
      var valid = res.data.ids.map( Number );
      write( list.filter( function ( id ) {
        return -1 !== valid.indexOf( id );
      } ) );

      grid.innerHTML = res.data.html;
      toggleEmpty();
    } ).catch( function () {
      grid.removeAttribute( 'aria-busy' );
    } );
  }

  // Srce isključeno ili "Ukloni" u tabu "Sačuvano": kartica (sa omotačem) odlazi sa liste.
  function dropCard( card ) {
    if ( ! card ) {
      return;
    }

    card.style.transition = 'opacity 0.25s, transform 0.25s';
    card.style.opacity = '0';
    card.style.transform = 'scale(0.95)';

    window.setTimeout( function () {
      card.remove();
      toggleEmpty();
    }, 250 );
  }

  // Prvo skini ono što je upravo dodato u ponudu (PDP forma se vraća učitavanjem), pa crtaj.
  consumeAdded();
  syncButtons();

  if ( panel && grid && cfg.ajaxUrl ) {
    // Tekstualni "Ukloni" ispod kartice – isto što i klik na puno srce.
    grid.addEventListener( 'click', function ( e ) {
      var btn = e.target.closest( '[data-wishlist-remove]' );
      if ( ! btn ) {
        return;
      }

      remove( Number( btn.dataset.wishlistRemove ) );
      syncButtons();
      dropCard( btn.closest( '.wishlist-item' ) );
      toast( 'Uklonjeno iz sačuvanih.' );
    } );

    loadSaved();
  } else {
    paintSavedCount();
  }
}() );
