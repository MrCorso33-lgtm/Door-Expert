/**
 * Single product (PDP) – UI interakcije.
 *
 * Server (single-product.php + template-parts/product/single.php) renderuje SVE iz WC_Product;
 * ova skripta radi samo klijentski UI:
 *   1. Galerija: thumbnail -> glavna slika + lightbox (zoom)
 *   2. FAQ accordion
 *   3. Količina +/- (WC add-to-cart forma)
 *   4. m² kalkulator (samo pločice)
 *   5. Varijacije: pilule iz prototipa kao vizuelni sloj nad skrivenim WC <select>-ovima
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

  /* ── Mobilna sticky traka: prati stanje glavnog dugmeta ─── */
  /*
   * Sticky dugme submituje istu formu preko form="product-cta-form", pa nema svoje
   * stanje. Kod varijabilnog proizvoda WC glavnom dugmetu dodaje klasu 'disabled'
   * dok izbor nije kompletan; bez ovoga bi sticky traka nudila dodavanje koje WC
   * svakako odbija.
   *
   * MutationObserver, a ne oslanjanje na WC-ove jQuery dogadjaje: isti kod tada radi
   * i kad se klasa promijeni bilo gdje drugdje, i ne zavisi od redosljeda skripti.
   */
  var mainCta = document.getElementById( 'btn-add-to-cart' );
  var stickyCta = document.getElementById( 'btn-sticky-add' );

  if ( mainCta && stickyCta && window.MutationObserver ) {
    var syncSticky = function () {
      var off = mainCta.classList.contains( 'disabled' ) || mainCta.disabled;

      stickyCta.classList.toggle( 'disabled', off );
      stickyCta.disabled = off;
    };

    new window.MutationObserver( syncSticky ).observe( mainCta, {
      attributes: true,
      attributeFilter: [ 'class', 'disabled' ]
    } );

    syncSticky();
  }

  /* ── Varijacije: pilule <-> WC selecti ──────────────────── */
  /*
   * Matching varijacija, cijenu, stanje i variation_id radi WC-ov wc-add-to-cart-variation.js.
   * Mi samo: (a) gradimo pilule iz opcija skrivenog <select>-a, (b) na klik postavljamo
   * vrijednost selecta i okidamo jQuery 'change', (c) preslikavamo cijenu varijacije u
   * .product-price-block__current. WC pri svakom izboru ISPISUJE opcije ostalih selecta
   * (dostupne kombinacije), pa pilule regenerišemo na 'woocommerce_update_variation_values'.
   */
  var variationsForm = document.querySelector( '.product-page .variations_form' );

  if ( variationsForm && window.jQuery ) {
    window.jQuery( function ( $ ) {
      var $form = $( variationsForm );
      var priceEl = document.getElementById( 'product-price-current' );
      var priceDefault = priceEl ? priceEl.innerHTML : '';

      /*
       * WC sam mijenja sliku po varijaciji, ali cilja .woocommerce-product-gallery /
       * .wp-post-image. Nasa galerija je bespoke (#gallery-main-img), pa to radimo rucno.
       */
      var imgDefaultSrc = mainImg ? mainImg.getAttribute( 'src' ) : '';
      var imgDefaultAlt = mainImg ? mainImg.getAttribute( 'alt' ) : '';
      var calcDefaultPrice = calc ? pricePerM2 : 0;

      /*
       * Varijacija bez cijene je u quote modelu validno stanje - korpa za nju vec pise
       * "Cijena na upit" (inc/quote-cart.php). PDP to nije pratio: blok cijene je
       * pokazivao opseg roditelja, dakle broj koji za tu dimenziju ne vazi.
       *
       * Natpis dugmeta se NE mijenja: radnja je ista bez obzira na cijenu, pa i poziv
       * na akciju ostaje "Dodaj u ponudu", kako trazi i DOCS/CRO/CRO - product.md.
       *
       * Cijena 0 i prazna cijena su ovdje isto, isti kriterijum koji koristi i filter
       * woocommerce_is_purchasable u inc/quote-cart.php.
       */
      function applyPriceState( variation ) {
        if ( ! priceEl || ! variation ) {
          return;
        }

        if ( 0 < parseFloat( variation.display_price ) ) {
          // Prazan price_html znaci "sve varijacije istu cijenu" - ostaje cijena roditelja.
          if ( variation.price_html ) {
            priceEl.innerHTML = variation.price_html;
          }

          return;
        }

        priceEl.textContent = ( window.doorExpertPrice && window.doorExpertPrice.onRequest ) || 'Cijena na upit';
      }

      /*
       * Blok dostupnosti gore pokazuje status RODITELJA. Kod varijabilnih to zna biti
       * suprotno od izabrane dimenzije (zeleno "Na stanju", a 70 cm rasprodato), pa ga
       * vezujemo za varijaciju. Tekstovi dolaze iz PHP-a (door_expert_stock_display).
       */
      var availEl = document.getElementById( 'product-availability' );
      var availTextEl = availEl ? availEl.querySelector( '.product-availability__text' ) : null;
      var availSubEl = document.getElementById( 'product-availability-sub' );
      var availNoteEl = document.getElementById( 'product-cta-note' );
      var availDefault = availEl ? availEl.getAttribute( 'data-default-status' ) : 'instock';

      function applyStock( status ) {
        var states = window.doorExpertStock;

        if ( ! availEl || ! states ) {
          return;
        }

        var state = states[ status ] || states.outofstock;

        if ( ! state ) {
          return;
        }

        availEl.classList.remove(
          'product-availability--in-stock',
          'product-availability--backorder',
          'product-availability--out-of-stock'
        );
        availEl.classList.add( 'product-availability--' + state.modifier );

        if ( availTextEl ) {
          availTextEl.textContent = state.label;
        }
        if ( availSubEl ) {
          availSubEl.textContent = state.sub;
        }
        if ( availNoteEl ) {
          availNoteEl.textContent = state.note || '';
          availNoteEl.hidden = ! state.note;
        }
      }

      /*
       * Auto-izbor: kad u nekom drugom redu ostane tacno jedna moguca opcija, biramo je
       * umjesto kupca. Pamtimo STA je izabrala masina (autoPicked) da bismo to pustili cim
       * kupac promijeni drugi atribut - inace red ostane zakljucan na opciji koja je bila
       * jedina samo uz prethodni izbor.
       */
      var autoPicked = {};
      var manualClearKey = null;
      var autoBusy = false;

      function variantWraps() {
        return variationsForm.querySelectorAll( '.product-variants[data-attribute]' );
      }

      /*
       * IZVOR ISTINE ZA DOSTUPNOST.
       *
       * Ranije su se pilule gradile iz select.options i sivjele na opt.disabled. WC
       * medjutim nemoguce opcije BRISE iz selecta umjesto da ih onemoguci, pa je pilula
       * nestajala; iznad 30 varijacija WC prestane i to da radi, pa su pilule ostajale
       * zamrznute kakve su bile pri ucitavanju. Zato pun spisak opcija dolazi iz
       * data-options, a dostupnost se racuna iz mape koju salje server. Oba izvora
       * postoje u oba rezima, pa prag od 30 varijacija vise nista ne mijenja.
       *
       * Prazan string u attrs je WC-ov dzoker ("Bilo koja vrijednost"), ne vrijednost.
       */
      var variationMap = [];

      try {
        var mapEl = document.getElementById( 'door-expert-variation-map' );
        variationMap = mapEl ? JSON.parse( mapEl.textContent || '[]' ) : [];
      } catch ( e ) {
        variationMap = [];
      }

      function optionList( wrap ) {
        var list = [];

        try {
          list = JSON.parse( wrap.getAttribute( 'data-options' ) || '[]' );
        } catch ( e ) {
          list = [];
        }

        if ( list.length ) {
          return list;
        }

        // Stranica iz kesa, bez data-options: bolje stari nacin nego nijedna pilula.
        return Array.prototype.map.call( wrap.querySelectorAll( 'select option' ), function ( opt ) {
          return { v: opt.value, l: opt.textContent };
        } ).filter( function ( option ) {
          return '' !== option.v;
        } );
      }

      // Trenutni izbor svih redova; skipKey izostavlja jedan red iz poredjenja.
      function currentAttrs( skipKey ) {
        var attrs = {};

        variantWraps().forEach( function ( wrap ) {
          var key = wrap.getAttribute( 'data-attribute' );
          var select = wrap.querySelector( 'select' );

          if ( select && select.value && key !== skipKey ) {
            attrs[ key ] = select.value;
          }
        } );

        return attrs;
      }

      function matchesVariation( variation, attrs ) {
        var key;

        for ( key in attrs ) {
          if ( ! Object.prototype.hasOwnProperty.call( attrs, key ) ) {
            continue;
          }

          var value = variation.attrs[ key ];

          if ( undefined !== value && '' !== value && value !== attrs[ key ] ) {
            return false;
          }
        }

        return true;
      }

      function comboPossible( attrs ) {
        // Bez mape ne znamo nista, pa radije ne sivimo nista nego da posivimo sve.
        if ( ! variationMap.length ) {
          return true;
        }

        return variationMap.some( function ( variation ) {
          return matchesVariation( variation, attrs );
        } );
      }

      function optionPossible( key, value ) {
        var test = currentAttrs( key );

        test[ key ] = value;

        return comboPossible( test );
      }

      /*
       * WC iz selecta brise opcije koje nisu moguce uz PRETHODNI izbor. Kad kupac klikne
       * bas takvu pilulu (dozvoljeno, vidi kaskadu u pickValue), jQuery .val() na
       * nepostojecoj opciji tiho ne uradi nista i klik izgleda kao da ne radi. Zato je
       * vracamo prije postavljanja. Bezbjedno je: WC svaki select gradi iz svog
       * netaknutog snimka, nikad iz onoga sto zatekne u DOM-u.
       */
      function ensureOption( select, value, label ) {
        if ( '' === value ) {
          return;
        }

        var exists = Array.prototype.some.call( select.options, function ( opt ) {
          return opt.value === value;
        } );

        if ( exists ) {
          return;
        }

        var opt = document.createElement( 'option' );

        opt.value = value;
        opt.textContent = label || value;
        select.appendChild( opt );
      }

      function releaseAutoPicked( exceptKey ) {
        Object.keys( autoPicked ).forEach( function ( key ) {
          if ( key === exceptKey ) {
            return;
          }
          var wrap = variationsForm.querySelector( '.product-variants[data-attribute="' + key + '"]' );
          var sel = wrap ? wrap.querySelector( 'select' ) : null;
          if ( sel ) {
            sel.value = ''; // Tiho: jedan 'change' okidamo tek posle, za sve odjednom.
          }
          delete autoPicked[ key ];
        } );
      }

      function autoSelectSingles() {
        variantWraps().forEach( function ( wrap ) {
          var key = wrap.getAttribute( 'data-attribute' );
          var select = wrap.querySelector( 'select' );

          if ( ! select || select.value || key === manualClearKey ) {
            return;
          }

          var free = optionList( wrap ).filter( function ( option ) {
            return optionPossible( key, option.v );
          } );

          if ( 1 !== free.length ) {
            return;
          }

          /*
           * Ako nijedna odgovarajuca varijacija ne precizira ovaj atribut (sve imaju
           * dzoker), atribut je nebitan uz trenutni izbor. Auto-izbor bi tada izmislio
           * ogranicenje koje kupac nije izrazio, pa ga preskacemo.
           */
          var attrs = currentAttrs( key );
          var specific = variationMap.some( function ( variation ) {
            return matchesVariation( variation, attrs ) && '' !== ( variation.attrs[ key ] || '' );
          } );

          if ( variationMap.length && ! specific ) {
            return;
          }

          autoPicked[ key ] = true;
          ensureOption( select, free[ 0 ].v, free[ 0 ].l );
          $( select ).val( free[ 0 ].v ).trigger( 'change' );
        } );
      }

      function setMainImage( src, srcset, alt ) {
        if ( ! mainImg || ! src ) {
          return;
        }
        mainImg.src = src;
        if ( srcset ) {
          mainImg.srcset = srcset;
        } else {
          mainImg.removeAttribute( 'srcset' );
        }
        mainImg.alt = alt || imgDefaultAlt;
      }

      /*
       * Klik na pilulu. Nedostupna pilula je NAMJERNO klikabilna: kupac koji je izabrao
       * 90x200 pa hoce orah kojeg u toj dimenziji nema ne smije da dodje do ćorsokaka.
       *
       * Kaskada: zadrzavamo svaki drugi izbor koji je i dalje moguc uz novi, a cistimo
       * samo one koji bi dali nemogucu kombinaciju. Provjera je namjerno u PAROVIMA
       * (novi protiv jednog po jednog), ne protiv cijelog izbora odjednom: kod tri
       * atributa dva postojeca izbora mogu svaki ponaosob biti u redu sa novim, a sva
       * tri zajedno ne - a ciscenje oba kad je dovoljno jedno djeluje kao da se
       * selektor bori sa kupcem.
       */
      function pickValue( wrap, option ) {
        var key = wrap.getAttribute( 'data-attribute' );
        var select = wrap.querySelector( 'select' );
        // Ponovni klik na aktivnu pilulu = poništi izbor (lakše mijenjanje kombinacije).
        var next = option.v === select.value ? '' : option.v;

        /*
         * Čovjek je preuzeo ovaj red, pa više nije mašinski izbor. Mašinske izbore u
         * OSTALIM redovima puštamo da se preracunaju uz novu vrijednost, inace bi red
         * ostao zakljucan na opciji koja je bila jedina prije ove promjene.
         */
        delete autoPicked[ key ];
        releaseAutoPicked( key );

        // Namjerno ponistavanje ne smije odmah biti ponisteno auto-izborom.
        manualClearKey = '' === next ? key : null;

        if ( '' !== next ) {
          variantWraps().forEach( function ( other ) {
            var otherKey = other.getAttribute( 'data-attribute' );
            var otherSelect = other.querySelector( 'select' );

            if ( otherKey === key || ! otherSelect || ! otherSelect.value ) {
              return;
            }

            var pair = {};

            pair[ key ] = next;
            pair[ otherKey ] = otherSelect.value;

            if ( ! comboPossible( pair ) ) {
              otherSelect.value = ''; // Tiho: jedan 'change' okidamo tek na kraju.
              delete autoPicked[ otherKey ];
            }
          } );

          ensureOption( select, option.v, option.l );
        }

        $( select ).val( next ).trigger( 'change' );
      }

      function buildPills( wrap ) {
        var select = wrap.querySelector( 'select' );
        var pills = wrap.querySelector( '.product-variants__pills' );
        var selectedOut = wrap.querySelector( '.product-variants__selected' );

        if ( ! select || ! pills ) {
          return;
        }

        var key = wrap.getAttribute( 'data-attribute' );
        var selectedLabel = '';
        /*
         * Pilule se pri svakoj promjeni grade iznova, sto unistava fokusirani element i
         * baca fokus na <body>. Korisnik tastature bi poslije svakog izbora morao da
         * tabuje kroz cijeli red iznova, pa pamtimo koja je pilula bila fokusirana.
         */
        var focusValue = document.activeElement && pills.contains( document.activeElement )
          ? document.activeElement.getAttribute( 'data-value' )
          : null;

        pills.innerHTML = '';

        optionList( wrap ).forEach( function ( option ) {
          var pill = document.createElement( 'button' );

          pill.type = 'button';
          pill.className = 'product-variant-pill';
          pill.setAttribute( 'data-value', option.v );
          pill.textContent = option.l;

          /*
           * Nedostupna opcija se SIVI, ne uklanja i ne onemogucava. Uklanjanje pomjera
           * red pod misem i katalog izgleda manji nego sto jeste ("nemaju orah"), a
           * onemogucena pilula ne bi mogla da primi klik kojim kupac mijenja izbor.
           */
          if ( ! optionPossible( key, option.v ) ) {
            pill.classList.add( 'is-disabled' );
            pill.title = 'Nije dostupno uz trenutni izbor';
          }

          var isActive = option.v === select.value;

          if ( isActive ) {
            selectedLabel = option.l;
          }

          pill.classList.toggle( 'is-active', isActive );
          pill.setAttribute( 'aria-pressed', isActive ? 'true' : 'false' );

          pill.addEventListener( 'click', function () {
            pickValue( wrap, option );
          } );

          pills.appendChild( pill );

          if ( focusValue === option.v ) {
            pill.focus();
          }
        } );

        if ( selectedOut ) {
          selectedOut.textContent = selectedLabel;
        }

        // Select skrivamo tek kad pilule postoje – bez JS-a ostaje upotrebljiv dropdown.
        wrap.classList.add( 'is-enhanced' );
      }

      function syncPills() {
        variantWraps().forEach( buildPills );
      }

      syncPills();

      /*
       * Osvjezavanje ide na obican 'change' samih selecta, a NE na WC-ov
       * 'woocommerce_update_variation_values'. Taj dogadjaj iznad 30 varijacija nikad ne
       * okine (WC tada iskljuci cijelo filtriranje opcija), pa su pilule ostajale
       * zamrznute - a to je rezim u koji ulazi svaka kolekcija plocica sa 6 boja i 6
       * formata. 'change' postoji u oba rezima i jedini je signal na koji se moze
       * racunati.
       *
       * Prvo prikaz, pa auto-izbor. autoSelectSingles() sam okida 'change', sto nas
       * vraca ovdje - autoBusy siječe tu rekurziju; unutrasnji prolaz je ionako vec
       * osvjezio pilule.
       */
      $form.on( 'change', '.product-variants select', function () {
        syncPills();

        if ( autoBusy ) {
          return;
        }

        autoBusy = true;
        autoSelectSingles();
        autoBusy = false;
      } );

      // Klik na "Poništi izbor": sve kreće ispočetka, pa i mašinski izbori.
      $form.on( 'click', '.reset_variations', function () {
        autoPicked = {};
        manualClearKey = null;
      } );

      $form.on( 'show_variation', function ( event, variation ) {
        applyPriceState( variation );
        if ( variation ) {
          applyStock( variation.door_expert_stock_status || availDefault );
        }
        /*
         * Kalkulator cita data-price iz roditelja, a to je kod varijabilnog proizvoda
         * najniza cijena iz opsega – pogresna cim formati imaju razlicit EUR/m².
         */
        if ( calc && variation && variation.display_price && 'function' === typeof recalc ) {
          pricePerM2 = parseFloat( variation.display_price ) || 0;
          recalc();
        }
        if ( variation && variation.image && variation.image.src ) {
          setMainImage( variation.image.src, variation.image.srcset, variation.image.alt );
          thumbs.forEach( function ( t ) {
            t.classList.remove( 'is-active' );
          } );
        }
        if ( qtyInput && variation ) {
          if ( variation.max_qty ) {
            qtyInput.setAttribute( 'max', variation.max_qty );
          }
          if ( variation.min_qty ) {
            qtyInput.setAttribute( 'min', variation.min_qty );
          }
          qtyInput.value = clampQty( parseInt( qtyInput.value, 10 ) );
        }
        syncPills();
      } );

      $form.on( 'hide_variation reset_data', function () {
        if ( priceEl ) {
          priceEl.innerHTML = priceDefault;
        }
        if ( calc && 'function' === typeof recalc ) {
          pricePerM2 = calcDefaultPrice;
          recalc();
        }
        applyStock( availDefault );
        setMainImage( imgDefaultSrc, '', imgDefaultAlt );
        if ( thumbs.length ) {
          thumbs.forEach( function ( t, i ) {
            t.classList.toggle( 'is-active', 0 === i );
          } );
        }
        syncPills();
      } );
    } );
  }
}() );
