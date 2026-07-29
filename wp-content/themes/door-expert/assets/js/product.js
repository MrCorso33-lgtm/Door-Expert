/**
 * DOOR EXPERT — Product Page JS
 * ============================================================
 * Handles:
 *  1. URL param ?tip= to switch between vrata / plocice / umivaonik
 *  2. Gallery: thumbnail switching + lightbox
 *  3. Variant pills: dimension, finish, format, shape, color
 *  4. m² calculator (tiles only)
 *  5. Quantity stepper
 *  6. Add to cart (quote cart) with toast feedback
 *  7. Wishlist toggle
 *  8. FAQ accordion
 *
 * Research basis:
 *  - Quote Cart Model: "Dodaj u ponudu" = add to inquiry list, no payment
 *  - UX Research: "showroom estetika + industrijska preciznost"
 *  - Visual Research: "editorial at the top, neutral in the body"
 * ============================================================
 */

(function () {
  'use strict';

  /* ============================================================
     PRODUCT DATA — one object per variant
     ============================================================ */
  const PRODUCTS = {
    vrata: {
      category: 'Sobna vrata',
      name: 'Linea',
      subname: 'Bijela mat — minimalistička linija',
      badge: 'Sobna vrata',
      bcCategory: 'Sobna vrata',
      bcCategoryHref: 'sobna-vrata.html',
      bcProduct: 'Linea — Bijela mat',
      priceUnit: 'Cijena po komadu, sa štok-okvirom. PDV uključen.',
      qtyLabel: 'Količina',
      images: [
        {
          src: 'https://3000-i4xviitzy7pxssh3g4nur-1bd8757e.us1.manus.computer/manus-storage/product-door-main_c9f69a73.png',
          alt: 'Linea — Bijela mat, frontalni prikaz'
        },
        {
          src: 'https://3000-i4xviitzy7pxssh3g4nur-1bd8757e.us1.manus.computer/manus-storage/product-door-lifestyle_2446a5fe.png',
          alt: 'Linea — Bijela mat u enterijeru'
        },
        {
          src: 'https://3000-i4xviitzy7pxssh3g4nur-1bd8757e.us1.manus.computer/manus-storage/product-door-detail_ce9d4153.png',
          alt: 'Detalj ručke i površine'
        },
        {
          src: 'https://3000-i4xviitzy7pxssh3g4nur-1bd8757e.us1.manus.computer/manus-storage/product-door-white_b2dfc901.png',
          alt: 'Bijela mat varijanta — bočni prikaz'
        }
      ],
      defaultPrice: '285 EUR',
      defaultOriginal: '370 EUR',
      defaultSavings: '✓ Ustedite 85 EUR'
    },
    plocice: {
      category: 'Keramičke pločice · Tau Ceramica',
      name: 'Marmo Blanco',
      subname: 'Travertin efekt — bijele nijanse, suptilne žile',
      badge: 'Keramičke pločice',
      bcCategory: 'Keramičke pločice',
      bcCategoryHref: '/keramicke-plocice/',
      bcProduct: 'Marmo Blanco 60×120',
      priceUnit: 'Cijena po m². Pakovanje: 1.44 m² (2 kom). PDV uključen.',
      qtyLabel: 'Paketa',
      images: [
        {
          src: 'https://3000-i4xviitzy7pxssh3g4nur-1bd8757e.us1.manus.computer/manus-storage/product-tile-main_0b3bb677.png',
          alt: 'Marmo Blanco — prikaz pločica'
        },
        {
          src: 'https://3000-i4xviitzy7pxssh3g4nur-1bd8757e.us1.manus.computer/manus-storage/product-tile-lifestyle_a127d64f.png',
          alt: 'Marmo Blanco u kupatilu'
        },
        {
          src: 'https://3000-i4xviitzy7pxssh3g4nur-1bd8757e.us1.manus.computer/manus-storage/product-tile-detail_a59223dc.png',
          alt: 'Detalj površine i ivice'
        }
      ],
      defaultPrice: '18 EUR/m²',
      defaultOriginal: '24 EUR/m²',
      defaultSavings: '✓ Ustedite 6 EUR/m²'
    },
    umivaonik: {
      category: 'Umivaonici · Bathco Španija',
      name: 'Mueble Oval',
      subname: 'Nadgradni porcelanski umivaonik — oval forma',
      badge: 'Bathco · Španija',
      bcCategory: 'Umivaonici',
      bcCategoryHref: '/umivaonici/',
      bcProduct: 'Mueble Oval — Bijeli',
      priceUnit: 'Cijena po komadu. PDV uključen. Slavina nije uključena.',
      qtyLabel: 'Količina',
      images: [
        {
          src: 'https://3000-i4xviitzy7pxssh3g4nur-1bd8757e.us1.manus.computer/manus-storage/product-basin-main_bc4d382e.png',
          alt: 'Mueble Oval — bijeli mat, prikaz'
        },
        {
          src: 'https://3000-i4xviitzy7pxssh3g4nur-1bd8757e.us1.manus.computer/manus-storage/product-basin-lifestyle_9b466068.png',
          alt: 'Mueble Oval u kupatilu'
        },
        {
          src: 'https://3000-i4xviitzy7pxssh3g4nur-1bd8757e.us1.manus.computer/manus-storage/product-basin-detail_fc82ad20.png',
          alt: 'Detalj površine umivaonika'
        }
      ],
      defaultPrice: '195 EUR',
      defaultOriginal: '245 EUR',
      defaultSavings: '✓ Ustedite 50 EUR'
    }
  };

  /* ============================================================
     INIT — read URL param and set page state
     ============================================================ */
  function init() {
    const params = new URLSearchParams(window.location.search);
    const tip = params.get('tip') || 'vrata';
    const validTypes = ['vrata', 'plocice', 'umivaonik'];
    const type = validTypes.includes(tip) ? tip : 'vrata';

    const root = document.getElementById('product-page-root');
    if (!root) return;

    root.setAttribute('data-type', type);

    applyProductData(type);
    buildGallery(type);
    initVariantPills();
    initColorSwatches();
    initQuantity();
    initCart(type);
    initWishlist();
    initFAQ();
    initLightbox();
    initTileCalculator();
  }

  /* ============================================================
     APPLY PRODUCT DATA
     ============================================================ */
  function applyProductData(type) {
    const p = PRODUCTS[type];
    if (!p) return;

    setText('decision-category', p.category);
    setText('decision-name', p.name);
    setText('decision-subname', p.subname);
    setText('gallery-badge', p.badge);
    setText('bc-product', p.bcProduct);
    setText('price-current', p.defaultPrice);
    setText('price-original', p.defaultOriginal);
    setText('price-savings', p.defaultSavings);
    setText('price-unit', p.priceUnit);
    setText('qty-label', p.qtyLabel);

    // Breadcrumb category link
    const bcCat = document.getElementById('bc-category');
    if (bcCat) {
      bcCat.textContent = p.bcCategory;
      bcCat.href = p.bcCategoryHref;
    }

    // Page title
    document.title = `${p.name} — ${p.subname.split('—')[0].trim()} | Door Expert Podgorica`;
  }

  /* ============================================================
     GALLERY
     ============================================================ */
  function buildGallery(type) {
    const p = PRODUCTS[type];
    if (!p) return;

    const mainImg = document.getElementById('gallery-main-img');
    const thumbsContainer = document.getElementById('gallery-thumbs');
    if (!mainImg || !thumbsContainer) return;

    // Set main image
    mainImg.src = p.images[0].src;
    mainImg.alt = p.images[0].alt;

    // Build thumbnails
    thumbsContainer.innerHTML = '';
    p.images.forEach((img, i) => {
      const thumb = document.createElement('div');
      thumb.className = 'product-gallery__thumb' + (i === 0 ? ' is-active' : '');
      thumb.setAttribute('role', 'listitem');
      thumb.setAttribute('tabindex', '0');
      thumb.setAttribute('aria-label', img.alt);

      const imgEl = document.createElement('img');
      imgEl.src = img.src;
      imgEl.alt = img.alt;
      imgEl.loading = 'lazy';

      thumb.appendChild(imgEl);
      thumbsContainer.appendChild(thumb);

      // Click to switch main image
      thumb.addEventListener('click', () => switchMainImage(img, thumb));
      thumb.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          switchMainImage(img, thumb);
        }
      });
    });
  }

  function switchMainImage(img, activeThumb) {
    const mainImg = document.getElementById('gallery-main-img');
    if (!mainImg) return;

    // Fade transition
    mainImg.style.opacity = '0';
    setTimeout(() => {
      mainImg.src = img.src;
      mainImg.alt = img.alt;
      mainImg.style.opacity = '1';
    }, 120);

    // Update active thumb
    document.querySelectorAll('.product-gallery__thumb').forEach(t => t.classList.remove('is-active'));
    activeThumb.classList.add('is-active');

    // Store current src for lightbox
    mainImg.dataset.fullSrc = img.src;
  }

  /* ============================================================
     LIGHTBOX
     ============================================================ */
  function initLightbox() {
    const galleryMain = document.getElementById('gallery-main');
    const lightbox = document.getElementById('product-lightbox');
    const lightboxImg = document.getElementById('lightbox-img');
    const closeBtn = document.getElementById('lightbox-close');

    if (!galleryMain || !lightbox || !lightboxImg) return;

    function openLightbox() {
      const mainImg = document.getElementById('gallery-main-img');
      if (!mainImg) return;
      lightboxImg.src = mainImg.src;
      lightboxImg.alt = mainImg.alt;
      lightbox.classList.add('is-open');
      document.body.style.overflow = 'hidden';
      closeBtn.focus();
    }

    function closeLightbox() {
      lightbox.classList.remove('is-open');
      document.body.style.overflow = '';
      galleryMain.focus();
    }

    galleryMain.addEventListener('click', openLightbox);
    galleryMain.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        openLightbox();
      }
    });

    if (closeBtn) closeBtn.addEventListener('click', closeLightbox);

    lightbox.addEventListener('click', (e) => {
      if (e.target === lightbox) closeLightbox();
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && lightbox.classList.contains('is-open')) {
        closeLightbox();
      }
    });
  }

  /* ============================================================
     VARIANT PILLS (dimensions, finish, format, tile-finish)
     ============================================================ */
  function initVariantPills() {
    // Dimension pills (vrata)
    document.querySelectorAll('[data-dim]').forEach(pill => {
      pill.addEventListener('click', () => {
        setActivePill(pill, '[data-dim]');
        const dim = pill.dataset.dim.replace('x', ' × ');
        setText('dim-selected', dim + ' cm');
        // Update price
        const price = pill.dataset.price;
        if (price) updatePrice(price + ' EUR');
        pill.setAttribute('aria-pressed', 'true');
      });
    });

    // Finish pills (vrata)
    document.querySelectorAll('[data-finish]').forEach(pill => {
      pill.addEventListener('click', () => {
        setActivePill(pill, '[data-finish]');
        const finishMap = {
          'bijela-mat': 'Bijela mat',
          'tamni-orah': 'Tamni orah',
          'antracit': 'Antracit',
          'hrast': 'Hrast natural'
        };
        const label = finishMap[pill.dataset.finish] || pill.dataset.finish;
        setText('finish-selected', label);
        setText('spec-finish', label + ' (CPL folija)');
      });
    });

    // Format pills (plocice)
    document.querySelectorAll('[data-format]').forEach(pill => {
      pill.addEventListener('click', () => {
        setActivePill(pill, '[data-format]');
        const fmt = pill.dataset.format.replace('x', ' × ');
        setText('format-selected', fmt + ' cm');
        setText('spec-format', fmt + ' cm');
        const price = pill.dataset.price;
        if (price) updatePrice(price + ' EUR/m²');
      });
    });

    // Tile finish pills
    document.querySelectorAll('[data-tfinish]').forEach(pill => {
      pill.addEventListener('click', () => {
        setActivePill(pill, '[data-tfinish]');
        const finishMap = {
          'mat': 'Mat (R9 anti-slip)',
          'polished': 'Polished',
          'lappato': 'Lappato'
        };
        const label = pill.dataset.tfinish;
        const specLabel = finishMap[label] || label;
        setText('tile-finish-selected', label.charAt(0).toUpperCase() + label.slice(1));
        setText('spec-tfinish', specLabel);
      });
    });

    // Shape pills (umivaonik)
    document.querySelectorAll('[data-shape]').forEach(pill => {
      pill.addEventListener('click', () => {
        setActivePill(pill, '[data-shape]');
        const shapeMap = {
          'oval': 'Oval',
          'pravougaoni': 'Pravougaoni',
          'okrugli': 'Okrugli'
        };
        const label = shapeMap[pill.dataset.shape] || pill.dataset.shape;
        setText('shape-selected', label);
        setText('spec-shape', label);
      });
    });
  }

  /* ============================================================
     COLOR SWATCHES (umivaonik)
     ============================================================ */
  function initColorSwatches() {
    document.querySelectorAll('.product-swatch').forEach(swatch => {
      swatch.addEventListener('click', () => {
        document.querySelectorAll('.product-swatch').forEach(s => s.classList.remove('is-active'));
        swatch.classList.add('is-active');
        const colorMap = {
          'bijela-mat': 'Bijela mat',
          'crna-mat': 'Crna mat',
          'siva-mat': 'Siva mat',
          'kamen': 'Kamen'
        };
        const label = colorMap[swatch.dataset.color] || swatch.dataset.color;
        setText('color-selected', label);
        setText('spec-color', label);
      });
    });
  }

  /* ============================================================
     QUANTITY STEPPER
     ============================================================ */
  function initQuantity() {
    const input = document.getElementById('qty-input');
    const minus = document.getElementById('qty-minus');
    const plus = document.getElementById('qty-plus');
    if (!input || !minus || !plus) return;

    minus.addEventListener('click', () => {
      const val = parseInt(input.value) || 1;
      if (val > 1) input.value = val - 1;
    });

    plus.addEventListener('click', () => {
      const val = parseInt(input.value) || 1;
      if (val < 99) input.value = val + 1;
    });

    input.addEventListener('change', () => {
      let val = parseInt(input.value);
      if (isNaN(val) || val < 1) val = 1;
      if (val > 99) val = 99;
      input.value = val;
    });
  }

  /* ============================================================
     M² TILE CALCULATOR
     ============================================================ */
  function initTileCalculator() {
    const widthInput = document.getElementById('calc-width');
    const lengthInput = document.getElementById('calc-length');
    const result = document.getElementById('calc-result');
    const qtyInput = document.getElementById('qty-input');

    if (!widthInput || !lengthInput || !result) return;

    function calculate() {
      const w = parseFloat(widthInput.value);
      const l = parseFloat(lengthInput.value);

      if (!w || !l || w <= 0 || l <= 0) {
        result.innerHTML = 'Unesite dimenzije prostorije za izračun';
        return;
      }

      const area = w * l;
      const areaWithWaste = area * 1.10; // +10% for cuts
      const packSize = 1.44; // m² per box (2 tiles 60x120)
      const packs = Math.ceil(areaWithWaste / packSize);

      // Get current price per m²
      const priceEl = document.getElementById('price-current');
      let pricePerM2 = 18;
      if (priceEl) {
        const match = priceEl.textContent.match(/(\d+)/);
        if (match) pricePerM2 = parseInt(match[1]);
      }

      const totalCost = Math.round(areaWithWaste * pricePerM2);

      result.innerHTML = `
        Površina: <strong>${area.toFixed(2)} m²</strong> +10% rezerva = ${areaWithWaste.toFixed(2)} m²<br>
        Potrebno: <strong>${packs} paketa</strong> (${(packs * packSize).toFixed(2)} m²)<br>
        Okvirna cijena: <strong>${totalCost} EUR</strong>
      `;

      // Auto-fill quantity
      if (qtyInput) qtyInput.value = packs;
    }

    widthInput.addEventListener('input', calculate);
    lengthInput.addEventListener('input', calculate);
  }

  /* ============================================================
     ADD TO CART (quote cart)
     ============================================================ */
  function initCart(type) {
    const btnDesktop = document.getElementById('btn-add-to-cart');
    const btnMobile = document.getElementById('btn-add-mobile');

    function handleAdd() {
      const p = PRODUCTS[type];
      const qty = parseInt(document.getElementById('qty-input')?.value) || 1;
      const price = document.getElementById('price-current')?.textContent || '';

      // Get selected variant info
      let variantInfo = '';
      if (type === 'vrata') {
        const activeDim = document.querySelector('[data-dim].is-active');
        const activeFinish = document.querySelector('[data-finish].is-active');
        variantInfo = [
          activeDim ? activeDim.dataset.dim.replace('x', '×') + ' cm' : '',
          activeFinish ? activeFinish.dataset.finish.replace(/-/g, ' ') : ''
        ].filter(Boolean).join(' · ');
      } else if (type === 'plocice') {
        const activeFmt = document.querySelector('[data-format].is-active');
        const activeTF = document.querySelector('[data-tfinish].is-active');
        variantInfo = [
          activeFmt ? activeFmt.dataset.format.replace('x', '×') + ' cm' : '',
          activeTF ? activeTF.dataset.tfinish : ''
        ].filter(Boolean).join(' · ');
      } else if (type === 'umivaonik') {
        const activeShape = document.querySelector('[data-shape].is-active');
        const activeColor = document.querySelector('.product-swatch.is-active');
        variantInfo = [
          activeShape ? activeShape.dataset.shape : '',
          activeColor ? activeColor.dataset.color.replace(/-/g, ' ') : ''
        ].filter(Boolean).join(' · ');
      }

      // Add to local cart
      addToLocalCart({
        id: `${type}-${Date.now()}`,
        type,
        name: p.name,
        variant: variantInfo,
        price,
        qty
      });

      // Visual feedback
      [btnDesktop, btnMobile].forEach(btn => {
        if (!btn) return;
        const original = btn.innerHTML;
        btn.classList.add('is-added');
        btn.innerHTML = `
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
          Dodato u ponudu
        `;
        setTimeout(() => {
          btn.classList.remove('is-added');
          btn.innerHTML = original;
        }, 2500);
      });

      showToast(`${p.name} dodato u ponudu`, 'success');
      updateCartBadge();
    }

    if (btnDesktop) btnDesktop.addEventListener('click', handleAdd);
    if (btnMobile) btnMobile.addEventListener('click', handleAdd);
  }

  function addToLocalCart(item) {
    try {
      const cart = JSON.parse(localStorage.getItem('de_cart') || '[]');
      cart.push(item);
      localStorage.setItem('de_cart', JSON.stringify(cart));
    } catch (e) {
      // localStorage not available
    }
  }

  function updateCartBadge() {
    try {
      const cart = JSON.parse(localStorage.getItem('de_cart') || '[]');
      const badge = document.querySelector('.header-btn__badge');
      if (badge) {
        badge.textContent = cart.length;
        badge.style.display = cart.length > 0 ? 'flex' : 'none';
        badge.setAttribute('aria-label', `${cart.length} stavki u korpi`);
      }
    } catch (e) {}
  }

  /* ============================================================
     WISHLIST
     ============================================================ */
  function initWishlist() {
    const btn = document.getElementById('btn-wishlist');
    if (!btn) return;

    btn.addEventListener('click', () => {
      const isActive = btn.classList.toggle('is-active');
      if (isActive) {
        showToast('Sačuvano za projekat', 'info');
      } else {
        showToast('Uklonjeno iz liste', 'info');
      }
    });
  }

  /* ============================================================
     FAQ ACCORDION
     ============================================================ */
  function initFAQ() {
    document.querySelectorAll('.product-faq__question').forEach(btn => {
      btn.addEventListener('click', () => {
        const item = btn.closest('.product-faq__item');
        if (!item) return;

        const isOpen = item.classList.contains('is-open');

        // Close all others
        document.querySelectorAll('.product-faq__item.is-open').forEach(openItem => {
          openItem.classList.remove('is-open');
          openItem.querySelector('.product-faq__question')?.setAttribute('aria-expanded', 'false');
        });

        // Toggle current
        if (!isOpen) {
          item.classList.add('is-open');
          btn.setAttribute('aria-expanded', 'true');
        }
      });
    });
  }

  /* ============================================================
     TOAST NOTIFICATION
     ============================================================ */
  function showToast(message, type = 'success') {
    // Remove existing toast
    const existing = document.getElementById('product-toast');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.id = 'product-toast';
    toast.setAttribute('role', 'status');
    toast.setAttribute('aria-live', 'polite');

    const colors = {
      success: { bg: '#2D6A4F', text: '#fff' },
      info: { bg: '#1E1A16', text: '#fff' },
      error: { bg: '#8B2020', text: '#fff' }
    };
    const c = colors[type] || colors.info;

    Object.assign(toast.style, {
      position: 'fixed',
      bottom: '80px',
      left: '50%',
      transform: 'translateX(-50%) translateY(20px)',
      background: c.bg,
      color: c.text,
      fontFamily: 'var(--font-body, DM Sans, sans-serif)',
      fontSize: '13px',
      fontWeight: '500',
      letterSpacing: '0.04em',
      padding: '12px 24px',
      zIndex: '9998',
      opacity: '0',
      transition: 'opacity 200ms ease, transform 200ms ease',
      whiteSpace: 'nowrap',
      pointerEvents: 'none'
    });

    toast.textContent = message;
    document.body.appendChild(toast);

    // Animate in
    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(-50%) translateY(0)';
      });
    });

    // Animate out
    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateX(-50%) translateY(10px)';
      setTimeout(() => toast.remove(), 250);
    }, 3000);
  }

  /* ============================================================
     HELPERS
     ============================================================ */
  function setText(id, text) {
    const el = document.getElementById(id);
    if (el) el.textContent = text;
  }

  function setActivePill(activePill, selector) {
    document.querySelectorAll(selector).forEach(p => {
      p.classList.remove('is-active');
      p.setAttribute('aria-pressed', 'false');
    });
    activePill.classList.add('is-active');
    activePill.setAttribute('aria-pressed', 'true');
  }

  function updatePrice(newPrice) {
    const el = document.getElementById('price-current');
    if (el) {
      el.style.opacity = '0';
      setTimeout(() => {
        el.textContent = newPrice;
        el.style.opacity = '1';
      }, 100);
    }
  }

  /* ============================================================
     MAIN IMAGE TRANSITION STYLE
     ============================================================ */
  (function addTransitionStyle() {
    const style = document.createElement('style');
    style.textContent = `
      #gallery-main-img {
        transition: opacity 120ms ease;
      }
    `;
    document.head.appendChild(style);
  })();

  /* ============================================================
     INIT on DOM ready
     ============================================================ */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // Also update cart badge on load
  document.addEventListener('DOMContentLoaded', updateCartBadge);

})();
