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

  /* ── Mobilni toggle sidebara ─────────────────────────────────
   * Otvaranje, zatvaranje i natpis radi CSS preko :checked (da radi i bez JS-a).
   * Ovdje se samo dodaje aria-expanded, koji CSS ne može.
   */
  var filterSwitch = document.getElementById( 'filterToggle' );
  if ( filterSwitch && 'checkbox' === filterSwitch.type ) {
    var filterLabel = document.querySelector( 'label[for="filterToggle"]' );

    if ( filterLabel ) {
      filterLabel.setAttribute( 'aria-controls', 'shopFilters' );

      var syncToggleState = function () {
        filterLabel.setAttribute( 'aria-expanded', filterSwitch.checked ? 'true' : 'false' );
      };

      filterSwitch.addEventListener( 'change', syncToggleState );
      syncToggleState();
    }
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

  /* ══ TRENUTNO FILTRIRANJE (AJAX) ═══════════════════════════════
   * Svaka promjena filtera odmah mijenja grid, bez ponovnog učitavanja stranice;
   * dugme "Primijeni filtere" se sakriva (CSS: .shop-filters.is-live).
   *
   * Sve ispod je progressive enhancement. Ako nema JS-a, localize podataka ili
   * fetch/URLSearchParams podrške, forma ostaje obična GET forma sa dugmetom i
   * ponaša se kao do sad. Serverski odgovor nosi gotov markup (isti onaj koji
   * ispisuje šablon), pa ovdje nema gradnje kartica – samo zamjena sadržaja.
   */
  var shop       = window.doorExpertShop;
  var results    = document.getElementById( 'shopResults' );
  var countBox   = document.getElementById( 'shopCount' );
  var content    = document.querySelector( '.shop-content' );
  var sortSelect = document.querySelector( '.shop-toolbar__sort' );

  var canLive = shop && form && results &&
    window.fetch && window.URLSearchParams && window.history && window.history.replaceState;

  if ( canLive ) {
    form.classList.add( 'is-live' );

    // Sort je do sad slao svoju formu preko inline onchange – sad ga vodi AJAX.
    if ( sortSelect ) {
      sortSelect.removeAttribute( 'onchange' );
    }

    // Redni broj zahtjeva: mrežni odgovori umiju da stignu van redosljeda, pa
    // zastarjeli odgovor ne smije da pregazi noviji.
    var seq   = 0;
    var timer = null;

    /**
     * Query string iz stanja forme. Prazne i nedirnute kontrole ne ulaze u URL.
     */
    var buildParams = function () {
      var params = new window.URLSearchParams();

      Array.prototype.forEach.call( form.elements, function ( el ) {
        if ( ! el.name || el.disabled || 'submit' === el.type || 'button' === el.type ) {
          return;
        }

        if ( 'checkbox' === el.type || 'radio' === el.type ) {
          if ( el.checked ) {
            params.append( el.name, el.value );
          }
          return;
        }

        // Nedirnut slider ne ide u URL (isto pravilo kao kod submita).
        if ( 'range' === el.type && el.value === el.getAttribute( 'data-de-default' ) ) {
          return;
        }

        if ( '' !== el.value ) {
          params.append( el.name, el.value );
        }
      } );

      // Sort živi u drugoj formi, pa hidden input u ovoj zna da bude zastario.
      params.delete( 'orderby' );
      if ( sortSelect && sortSelect.value && 'menu_order' !== sortSelect.value ) {
        params.append( 'orderby', sortSelect.value );
      }

      return params;
    };

    /**
     * URL u adresnoj traci prati filtere, da se stanje može podijeliti i osvježiti.
     * replaceState (ne pushState): štikliranje filtera ne smije da napuni Nazad dugme.
     */
    var syncUrl = function ( qs, paged ) {
      var url = shop.base;

      if ( paged > 1 ) {
        url = url.replace( /\/?$/, '/' ) + 'page/' + paged + '/';
      }
      if ( qs ) {
        url += '?' + qs;
      }

      window.history.replaceState( null, '', url );
    };

    /**
     * Brojevi uz opcije prate izbor (isti faceti kao server-side, inc/filters.php).
     * Nula se sivi i onemogućava, ali štiklirana opcija nikad – mora moći da se skine.
     */
    var updateFacets = function ( facets ) {
      if ( ! facets ) {
        return;
      }

      Object.keys( facets ).forEach( function ( taxonomy ) {
        var counts = facets[ taxonomy ] || {};

        form.querySelectorAll( 'input[type="checkbox"][name="' + taxonomy + '[]"]' ).forEach( function ( input ) {
          var label = input.closest( '.wcfc-option, .wcfc-swatch' );
          if ( ! label ) {
            return;
          }

          var count = Object.prototype.hasOwnProperty.call( counts, input.value ) ? counts[ input.value ] : 0;
          var small = label.querySelector( '.wcfc-count' );
          var dead  = ( 0 === count && ! input.checked );

          if ( small ) {
            small.textContent = '(' + count + ')';
          }

          label.setAttribute( 'data-count', count );
          label.classList.toggle( 'is-disabled', dead );
          input.disabled = dead;
        } );
      } );
    };

    var pageFromHref = function ( href ) {
      var match = href.match( /\/page\/(\d+)/ );
      if ( match ) {
        return parseInt( match[ 1 ], 10 );
      }

      match = href.match( /[?&]paged=(\d+)/ );

      return match ? parseInt( match[ 1 ], 10 ) : 1;
    };

    var apply = function ( paged, scroll ) {
      var params = buildParams();
      var qs     = params.toString();
      var mine   = ++seq;

      var body = new window.URLSearchParams();
      body.append( 'action', shop.action );
      body.append( 'nonce', shop.nonce );
      body.append( 'query', qs );
      body.append( 'cat', shop.catId );
      body.append( 'paged', paged );

      if ( content ) {
        content.classList.add( 'is-loading' );
      }
      results.setAttribute( 'aria-busy', 'true' );

      window.fetch( shop.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: body
      } ).then( function ( response ) {
        return response.json();
      } ).then( function ( payload ) {
        if ( mine !== seq ) {
          return; // Stigao je stariji odgovor; noviji je već u letu.
        }
        if ( ! payload || ! payload.success || ! payload.data ) {
          throw new Error( 'filter' );
        }

        results.innerHTML = payload.data.results;
        if ( countBox ) {
          countBox.innerHTML = payload.data.count;
        }
        updateFacets( payload.data.facets );
        syncUrl( qs, paged );

        if ( scroll ) {
          results.scrollIntoView( { behavior: 'smooth', block: 'start' } );
        }

        if ( content ) {
          content.classList.remove( 'is-loading' );
        }
        results.removeAttribute( 'aria-busy' );
      } ).catch( function () {
        if ( mine !== seq ) {
          return;
        }

        // Radije puno učitavanje nego zaglavljen ekran: korisnik svakako
        // dobije tačan rezultat, samo sporije.
        window.location.href = shop.base + ( qs ? '?' + qs : '' );
      } );
    };

    var schedule = function ( delay ) {
      window.clearTimeout( timer );
      timer = window.setTimeout( function () {
        apply( 1, false );
      }, delay );
    };

    form.addEventListener( 'change', function ( event ) {
      if ( ! event.target || ! event.target.name ) {
        return;
      }

      // Slider okida change tek na otpuštanje, pa mu treba manje čekanja nego
      // nizu brzih klikova po checkboxovima.
      schedule( 'range' === event.target.type ? 150 : 250 );
    } );

    // Enter u polju ili submit bez JS-a pokrivenog dugmeta: ne napuštaj stranicu.
    form.addEventListener( 'submit', function ( event ) {
      event.preventDefault();
      schedule( 0 );
    } );

    if ( sortSelect ) {
      sortSelect.addEventListener( 'change', function () {
        schedule( 0 );
      } );
    }

    // Paginacija: linkovi ostaju pravi URL-ovi (dijeljivi, radi i bez JS-a),
    // ali klik ide kroz AJAX.
    results.addEventListener( 'click', function ( event ) {
      var link = event.target.closest ? event.target.closest( '.shop-pagination a' ) : null;
      if ( ! link ) {
        return;
      }

      event.preventDefault();
      apply( pageFromHref( link.href ), true );
    } );
  }
}() );
