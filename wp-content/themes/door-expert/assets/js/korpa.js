/**
 * korpa.js — Quote Cart / Korpa za ponudu
 * Door Expert · Luxury Minimalist Prototype
 *
 * Features:
 *  - Tab switching (Korpa / Sačuvano)
 *  - Quantity +/− controls with live subtotal & grand total recalculation
 *  - Per-item note toggle
 *  - Remove item (with empty-state detection)
 *  - Form validation + submit → thank-you state
 *  - Wishlist: move to cart, remove
 *  - Share: native share API with copy-link fallback
 *  - Mobile sticky bar sync
 *  - Accessible: aria-live, aria-expanded, focus management
 */

(function () {
  'use strict';

  /* ─────────────────────────────────────────
     STATE
  ───────────────────────────────────────── */
  const cartItems = {
    1: { unit: 245, qty: 5 },
    2: { unit: 38,  qty: 12 },
    3: { unit: 385, qty: 1  },
  };

  let wishlistCount = 2;

  /* ─────────────────────────────────────────
     HELPERS
  ───────────────────────────────────────── */
  function formatEur(amount) {
    return amount.toLocaleString('sr-Latn-ME', {
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    }) + ' €';
  }

  function generateRef() {
    const now = new Date();
    const y = now.getFullYear();
    const n = Math.floor(Math.random() * 9000) + 1000;
    return `DE-${y}-${n}`;
  }

  /* ─────────────────────────────────────────
     TOTALS
  ───────────────────────────────────────── */
  function recalcTotals() {
    let grand = 0;

    Object.keys(cartItems).forEach(function (id) {
      const item = cartItems[id];
      const sub = item.unit * item.qty;
      grand += sub;

      const subtotalEl = document.getElementById('subtotal-' + id);
      const sumEl = document.getElementById('sum-' + id);

      if (subtotalEl) subtotalEl.textContent = formatEur(sub);
      if (sumEl) sumEl.textContent = formatEur(sub);
    });

    const grandEl = document.getElementById('grand-total');
    const mobileEl = document.getElementById('mobile-total');

    if (grandEl) grandEl.textContent = formatEur(grand);
    if (mobileEl) mobileEl.textContent = formatEur(grand);
  }

  /* ─────────────────────────────────────────
     QUANTITY CONTROLS
  ───────────────────────────────────────── */
  function initQtyControls() {
    document.addEventListener('click', function (e) {
      const btn = e.target.closest('[data-action="plus"], [data-action="minus"]');
      if (!btn) return;

      const id = btn.dataset.item;
      const action = btn.dataset.action;
      if (!cartItems[id]) return;

      const input = document.querySelector('.korpa-item__qty-input[data-item="' + id + '"]');
      if (!input) return;

      let qty = parseInt(input.value, 10) || 1;
      if (action === 'plus') qty = Math.min(qty + 1, 99);
      if (action === 'minus') qty = Math.max(qty - 1, 1);

      input.value = qty;
      cartItems[id].qty = qty;
      recalcTotals();
    });

    // Direct input change
    document.addEventListener('change', function (e) {
      const input = e.target.closest('.korpa-item__qty-input');
      if (!input) return;

      const id = input.dataset.item;
      if (!cartItems[id]) return;

      let qty = parseInt(input.value, 10) || 1;
      qty = Math.max(1, Math.min(qty, 99));
      input.value = qty;
      cartItems[id].qty = qty;
      recalcTotals();
    });
  }

  /* ─────────────────────────────────────────
     REMOVE ITEM
  ───────────────────────────────────────── */
  function initRemoveItem() {
    document.addEventListener('click', function (e) {
      const btn = e.target.closest('.korpa-item__remove');
      if (!btn) return;

      const id = btn.dataset.item;
      const row = btn.closest('.korpa-item');
      if (!row) return;

      // Animate out
      row.style.transition = 'opacity 0.25s, transform 0.25s';
      row.style.opacity = '0';
      row.style.transform = 'translateX(-8px)';

      setTimeout(function () {
        row.remove();
        delete cartItems[id];

        // Remove from summary
        const sumEl = document.getElementById('sum-' + id);
        if (sumEl) sumEl.closest('li')?.remove();

        recalcTotals();
        updateCartCount();
        checkEmptyState();
      }, 250);
    });
  }

  /* ─────────────────────────────────────────
     EMPTY STATE CHECK
  ───────────────────────────────────────── */
  function checkEmptyState() {
    const filled = document.getElementById('cart-filled');
    const empty = document.getElementById('cart-empty');
    const count = Object.keys(cartItems).length;

    if (count === 0) {
      if (filled) filled.style.display = 'none';
      if (empty) {
        empty.classList.add('visible');
        empty.style.display = 'block';
      }
      // Hide mobile sticky
      const sticky = document.getElementById('korpa-sticky-mobile');
      if (sticky) sticky.style.display = 'none';
    }
  }

  /* ─────────────────────────────────────────
     CART COUNT BADGE
  ───────────────────────────────────────── */
  function updateCartCount() {
    const count = Object.keys(cartItems).length;
    const badge = document.getElementById('cart-count');
    const tabBadge = document.getElementById('tab-korpa-count');

    if (badge) badge.textContent = count;
    if (tabBadge) tabBadge.textContent = count;
  }

  /* ─────────────────────────────────────────
     NOTE TOGGLE PER ITEM
  ───────────────────────────────────────── */
  function initNoteToggles() {
    document.addEventListener('click', function (e) {
      const btn = e.target.closest('.korpa-item__note-toggle');
      if (!btn) return;

      const id = btn.dataset.item;
      const field = document.getElementById('note-' + id);
      if (!field) return;

      const isOpen = field.classList.contains('open');
      field.classList.toggle('open', !isOpen);
      btn.setAttribute('aria-expanded', String(!isOpen));
      btn.textContent = isOpen ? '+ Dodaj napomenu' : '− Sakrij napomenu';

      if (!isOpen) {
        const textarea = field.querySelector('textarea');
        if (textarea) textarea.focus();
      }
    });
  }

  /* ─────────────────────────────────────────
     TABS
  ───────────────────────────────────────── */
  function initTabs() {
    const tabs = document.querySelectorAll('.korpa-tab');

    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        const target = tab.dataset.tab;

        // Update tab states
        tabs.forEach(function (t) {
          t.classList.remove('active');
          t.setAttribute('aria-selected', 'false');
        });
        tab.classList.add('active');
        tab.setAttribute('aria-selected', 'true');

        // Update panels
        document.querySelectorAll('.korpa-panel').forEach(function (panel) {
          panel.classList.remove('active');
        });
        const panel = document.getElementById('panel-' + target);
        if (panel) panel.classList.add('active');
      });
    });
  }

  /* ─────────────────────────────────────────
     FORM VALIDATION & SUBMIT
  ───────────────────────────────────────── */
  function initForm() {
    const form = document.getElementById('quote-form');
    if (!form) return;

    form.addEventListener('submit', function (e) {
      e.preventDefault();

      // Basic validation
      const ime = form.querySelector('#ime');
      const email = form.querySelector('#email');
      const telefon = form.querySelector('#telefon');
      const consent = form.querySelector('#saglasnost');

      let valid = true;

      [ime, email, telefon].forEach(function (field) {
        if (!field) return;
        const val = field.value.trim();
        if (!val) {
          field.style.borderColor = '#C0392B';
          valid = false;
        } else {
          field.style.borderColor = '';
        }
      });

      if (email) {
        const emailVal = email.value.trim();
        if (emailVal && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal)) {
          email.style.borderColor = '#C0392B';
          valid = false;
        }
      }

      if (consent && !consent.checked) {
        consent.style.outline = '2px solid #C0392B';
        valid = false;
      } else if (consent) {
        consent.style.outline = '';
      }

      if (!valid) {
        // Focus first invalid field
        const firstInvalid = form.querySelector('[style*="C0392B"]');
        if (firstInvalid) firstInvalid.focus();
        return;
      }

      // Show thank-you state
      showThankYou();
    });

    // Mobile submit button triggers form
    const mobileBtn = document.getElementById('mobile-submit-btn');
    if (mobileBtn) {
      mobileBtn.addEventListener('click', function () {
        // Scroll to form on mobile
        const summary = document.querySelector('.korpa-summary');
        if (summary) {
          summary.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        // Small delay then submit
        setTimeout(function () {
          form.dispatchEvent(new Event('submit', { cancelable: true }));
        }, 600);
      });
    }
  }

  function showThankYou() {
    const filled = document.getElementById('cart-filled');
    const thankyou = document.getElementById('cart-thankyou');
    const sticky = document.getElementById('korpa-sticky-mobile');

    if (filled) {
      filled.style.transition = 'opacity 0.3s';
      filled.style.opacity = '0';
      setTimeout(function () {
        filled.style.display = 'none';
        if (thankyou) {
          thankyou.classList.add('visible');
          thankyou.style.display = 'block';
          thankyou.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      }, 300);
    }

    // Set reference number
    const refEl = document.getElementById('thankyou-ref');
    if (refEl) refEl.textContent = 'Referentni broj: ' + generateRef();

    // Hide mobile sticky
    if (sticky) sticky.style.display = 'none';
  }

  /* ─────────────────────────────────────────
     WISHLIST ACTIONS
  ───────────────────────────────────────── */
  function initWishlist() {
    // Move to cart
    document.addEventListener('click', function (e) {
      const btn = e.target.closest('.wishlist-card__move');
      if (!btn) return;

      const wishId = btn.dataset.wish;
      const card = document.querySelector('[data-wish-id="' + wishId + '"]');
      if (!card) return;

      // Animate out
      card.style.transition = 'opacity 0.25s, transform 0.25s';
      card.style.opacity = '0';
      card.style.transform = 'scale(0.95)';

      setTimeout(function () {
        card.remove();
        wishlistCount = Math.max(0, wishlistCount - 1);
        updateWishlistCount();

        // Show toast-like feedback
        showToast('Premješteno u korpu za ponudu');
      }, 250);
    });

    // Remove from wishlist (both buttons)
    document.addEventListener('click', function (e) {
      const btn = e.target.closest('.wishlist-card__remove-wish, .wishlist-card__remove-wish-abs');
      if (!btn) return;

      const wishId = btn.dataset.wish;
      const card = document.querySelector('[data-wish-id="' + wishId + '"]');
      if (!card) return;

      card.style.transition = 'opacity 0.25s';
      card.style.opacity = '0';

      setTimeout(function () {
        card.remove();
        wishlistCount = Math.max(0, wishlistCount - 1);
        updateWishlistCount();
      }, 250);
    });
  }

  function updateWishlistCount() {
    const badge = document.getElementById('tab-sacuvano-count');
    if (badge) badge.textContent = wishlistCount;
  }

  /* ─────────────────────────────────────────
     SHARE WISHLIST
  ───────────────────────────────────────── */
  function initShare() {
    const shareBtn = document.getElementById('share-link-btn');
    if (shareBtn) {
      shareBtn.addEventListener('click', function () {
        const url = window.location.href;

        if (navigator.share) {
          navigator.share({
            title: 'Moja lista — Door Expert',
            text: 'Pogledaj moju listu sačuvanih proizvoda na Door Expert sajtu.',
            url: url,
          }).catch(function () {
            copyToClipboard(url, shareBtn);
          });
        } else {
          copyToClipboard(url, shareBtn);
        }
      });
    }

    const pdfBtn = document.getElementById('share-pdf-btn');
    if (pdfBtn) {
      pdfBtn.addEventListener('click', function () {
        showToast('PDF export — dostupno u WordPress verziji sajta');
      });
    }
  }

  function copyToClipboard(text, btn) {
    if (navigator.clipboard) {
      navigator.clipboard.writeText(text).then(function () {
        const orig = btn.innerHTML;
        btn.textContent = '✓ Link kopiran';
        setTimeout(function () { btn.innerHTML = orig; }, 2000);
      });
    }
  }

  /* ─────────────────────────────────────────
     TOAST NOTIFICATION
  ───────────────────────────────────────── */
  function showToast(message) {
    let toast = document.getElementById('korpa-toast');
    if (!toast) {
      toast = document.createElement('div');
      toast.id = 'korpa-toast';
      toast.setAttribute('role', 'status');
      toast.setAttribute('aria-live', 'polite');
      toast.style.cssText = [
        'position:fixed',
        'bottom:80px',
        'left:50%',
        'transform:translateX(-50%)',
        'background:var(--color-antracit)',
        'color:var(--color-white)',
        'font-family:var(--font-body)',
        'font-size:0.875rem',
        'padding:0.75rem 1.5rem',
        'z-index:9999',
        'opacity:0',
        'transition:opacity 0.25s',
        'pointer-events:none',
        'white-space:nowrap',
      ].join(';');
      document.body.appendChild(toast);
    }

    toast.textContent = message;
    toast.style.opacity = '1';

    clearTimeout(toast._timer);
    toast._timer = setTimeout(function () {
      toast.style.opacity = '0';
    }, 2800);
  }

  /* ─────────────────────────────────────────
     INIT
  ───────────────────────────────────────── */
  function init() {
    initTabs();
    initQtyControls();
    initRemoveItem();
    initNoteToggles();
    initForm();
    initWishlist();
    initShare();
    recalcTotals();
    updateCartCount();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
