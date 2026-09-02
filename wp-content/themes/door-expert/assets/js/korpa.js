/**
 * Korpa za ponudu – prava WooCommerce korpa preko AJAX-a.
 *
 * Zamjenjuje raniji demo simulator (lokalni cartItems objekat, generateRef(),
 * klijentski recalcTotals). Sada:
 *   1. Količina +/- i ručni unos  -> door_expert_update_cart_qty
 *   2. Uklanjanje stavke          -> door_expert_remove_cart_item
 *   3. Napomene po stavci (toggle) -> skupljaju se pri slanju upita
 *   4. Slanje upita               -> door_expert_submit_inquiry -> /hvala/
 *
 * Backend: inc/quote-cart.php. Podaci se dobijaju preko wp_localize_script (doorExpert).
 */
( function () {
  'use strict';

  var root = document.querySelector( '.korpa-page' );
  if ( ! root || 'undefined' === typeof doorExpert ) {
    return;
  }

  /* ── AJAX helper ────────────────────────────────────────── */
  function post( action, body, nonce ) {
    var data = new URLSearchParams( body );
    data.append( 'action', action );
    data.append( 'nonce', nonce );

    return fetch( doorExpert.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: data.toString()
    } ).then( function ( res ) {
      return res.json();
    } );
  }

  /* ── Osvježi totale i badž ──────────────────────────────── */
  function paintTotals( data ) {
    document.querySelectorAll( '[data-cart-total]' ).forEach( function ( el ) {
      el.textContent = data.cart_total;
    } );
    document.querySelectorAll( '[data-cart-subtotal]' ).forEach( function ( el ) {
      el.innerHTML = data.cart_subtotal;
    } );
    document.querySelectorAll( '.header-btn__badge, .cart-badge' ).forEach( function ( el ) {
      el.textContent = data.cart_count;
      el.style.display = data.cart_count > 0 ? '' : 'none';
    } );
  }

  /* ── Količina i uklanjanje ──────────────────────────────── */
  function setQty( row, qty ) {
    row.classList.add( 'is-busy' );

    return post( 'door_expert_update_cart_qty', {
      cart_key: row.dataset.cartKey,
      qty: qty
    }, doorExpert.cartNonce ).then( function ( res ) {
      row.classList.remove( 'is-busy' );
      if ( ! res.success ) {
        return;
      }
      if ( res.data.removed ) {
        row.remove();
      } else {
        var sub = row.querySelector( '[data-item-subtotal]' );
        if ( sub ) {
          sub.innerHTML = res.data.item_subtotal;
        }
      }
      paintTotals( res.data );
      if ( res.data.cart_empty ) {
        window.location.reload();
      }
    } );
  }

  root.addEventListener( 'click', function ( e ) {
    var qtyBtn = e.target.closest( '.korpa-item__qty-btn' );
    var remove = e.target.closest( '[data-cart-remove]' );
    var note   = e.target.closest( '[data-note-toggle]' );

    if ( qtyBtn ) {
      var row   = qtyBtn.closest( '[data-cart-key]' );
      var input = row.querySelector( '.korpa-item__qty-input' );
      var step  = 'plus' === qtyBtn.dataset.action ? 1 : -1;
      var next  = Math.max( 1, parseInt( input.value, 10 ) + step );

      input.value = next;
      setQty( row, next );
      return;
    }

    if ( remove ) {
      var delRow = remove.closest( '[data-cart-key]' );
      delRow.classList.add( 'is-busy' );

      post( 'door_expert_remove_cart_item', {
        cart_key: delRow.dataset.cartKey
      }, doorExpert.cartNonce ).then( function ( res ) {
        if ( ! res.success ) {
          delRow.classList.remove( 'is-busy' );
          return;
        }
        delRow.remove();
        paintTotals( res.data );
        if ( res.data.cart_empty ) {
          window.location.reload();
        }
      } );
      return;
    }

    if ( note ) {
      var field = note.nextElementSibling;
      if ( field ) {
        var open = field.hasAttribute( 'hidden' );
        field.toggleAttribute( 'hidden', ! open );
        note.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
        note.textContent = open ? '− Sakrij napomenu' : '+ Dodaj napomenu';
      }
    }
  } );

  // Ručni unos količine.
  root.addEventListener( 'change', function ( e ) {
    var input = e.target.closest( '.korpa-item__qty-input' );
    if ( ! input ) {
      return;
    }
    var row = input.closest( '[data-cart-key]' );
    var val = Math.max( 1, parseInt( input.value, 10 ) || 1 );
    input.value = val;
    setQty( row, val );
  } );

  /* ── Slanje upita ───────────────────────────────────────── */
  var form = document.getElementById( 'quote-form' );
  if ( ! form ) {
    return;
  }

  var errorBox = document.getElementById( 'quote-error' );

  function showError( msg ) {
    if ( ! errorBox ) {
      return;
    }
    errorBox.textContent = msg;
    errorBox.hidden = false;
  }

  form.addEventListener( 'submit', function ( e ) {
    e.preventDefault();

    if ( errorBox ) {
      errorBox.hidden = true;
    }

    var ime      = form.querySelector( '#ime' ).value.trim();
    var email    = form.querySelector( '#email' ).value.trim();
    var telefon  = form.querySelector( '#telefon' ).value.trim();
    var saglasan = form.querySelector( '#saglasnost' ).checked;

    if ( ! ime || ! email || ! telefon ) {
      showError( 'Molimo popunite ime, email i telefon.' );
      return;
    }
    if ( ! saglasan ) {
      showError( 'Potrebna je saglasnost za obradu podataka.' );
      return;
    }

    var body = {
      ime: ime,
      email: email,
      telefon: telefon,
      grad: form.querySelector( '#grad' ).value.trim(),
      napomena: form.querySelector( '#napomena' ).value.trim(),
      saglasnost: '1',
      website: form.querySelector( '#website' ).value
    };

    // Napomene po stavci žive van <form>, pa ih skupljamo ovdje.
    document.querySelectorAll( '.korpa-item__note-input' ).forEach( function ( ta ) {
      var val = ta.value.trim();
      if ( val ) {
        body[ 'item_note[' + ta.dataset.noteFor + ']' ] = val;
      }
    } );

    var submitBtn = form.querySelector( '.korpa-form__submit' );
    submitBtn.disabled = true;
    submitBtn.classList.add( 'is-busy' );

    post( 'door_expert_submit_inquiry', body, doorExpert.inquiryNonce ).then( function ( res ) {
      if ( ! res.success ) {
        submitBtn.disabled = false;
        submitBtn.classList.remove( 'is-busy' );
        showError( res.data || 'Slanje nije uspjelo. Pokušajte ponovo.' );
        return;
      }
      window.location.href = res.data.redirect;
    } ).catch( function () {
      submitBtn.disabled = false;
      submitBtn.classList.remove( 'is-busy' );
      showError( 'Greška u vezi. Pokušajte ponovo.' );
    } );
  } );
}() );
