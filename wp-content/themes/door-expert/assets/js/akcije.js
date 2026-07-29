/**
 * DOOR EXPERT — AKCIJE PAGE SCRIPTS
 * ============================================================
 * Handles:
 *   1. Hero countdown timer (real deadline, never resets)
 *   2. Filter tabs (category filtering)
 *   3. Sort functionality
 *   4. Wishlist toggle
 *   5. Add to cart (quote inquiry model)
 *
 * Research basis: Discounts and Urgency Psychology.md
 *   - Real deadlines only — never reset a countdown
 *   - Countdown must NOT be the most prominent element
 *   - Monetary savings as primary signal
 * ============================================================
 */

(function () {
  'use strict';

  /* ============================================================
     1. HERO COUNTDOWN TIMER
     Research: "Real deadlines only — never reset a countdown"
     Deadline: July 20, 2026 at 23:59:59 CET
     ============================================================ */

  const CAMPAIGN_DEADLINE = new Date('2026-07-20T23:59:59+02:00');

  function updateCountdown() {
    const now = new Date();
    const diff = CAMPAIGN_DEADLINE - now;

    const daysEl  = document.getElementById('cd-days');
    const hoursEl = document.getElementById('cd-hours');
    const minsEl  = document.getElementById('cd-mins');

    if (!daysEl || !hoursEl || !minsEl) return;

    if (diff <= 0) {
      // Campaign has ended — hide countdown block gracefully
      const block = document.querySelector('.akcije-hero__countdown-block');
      if (block) {
        block.style.opacity = '0';
        block.style.pointerEvents = 'none';
      }
      return;
    }

    const totalSeconds = Math.floor(diff / 1000);
    const days  = Math.floor(totalSeconds / 86400);
    const hours = Math.floor((totalSeconds % 86400) / 3600);
    const mins  = Math.floor((totalSeconds % 3600) / 60);

    daysEl.textContent  = String(days).padStart(2, '0');
    hoursEl.textContent = String(hours).padStart(2, '0');
    minsEl.textContent  = String(mins).padStart(2, '0');
  }

  // Run immediately, then every 30 seconds (no need for per-second updates
  // on a premium site — per-second ticking feels aggressive)
  updateCountdown();
  setInterval(updateCountdown, 30000);

  /* ============================================================
     2. FILTER TABS
     Research: Baymard — users actively look for "Sales" list;
     filter-based category helps reach relevant items faster
     ============================================================ */

  const filterTabs = document.querySelectorAll('.akcije-filter-tab');
  const productCards = document.querySelectorAll('.akcije-card');
  const resultsCountEl = document.getElementById('results-count');

  function filterProducts(category) {
    let visibleCount = 0;

    productCards.forEach(function (card) {
      const cardCategory = card.getAttribute('data-category');
      const shouldShow = category === 'sve' || cardCategory === category;

      if (shouldShow) {
        card.removeAttribute('data-hidden');
        card.style.display = '';
        visibleCount++;
      } else {
        card.setAttribute('data-hidden', 'true');
        card.style.display = 'none';
      }
    });

    // Update results count
    if (resultsCountEl) {
      resultsCountEl.textContent = visibleCount;
    }

    return visibleCount;
  }

  filterTabs.forEach(function (tab) {
    tab.addEventListener('click', function () {
      const filter = this.getAttribute('data-filter');

      // Update active state
      filterTabs.forEach(function (t) {
        t.classList.remove('is-active');
        t.setAttribute('aria-pressed', 'false');
      });
      this.classList.add('is-active');
      this.setAttribute('aria-pressed', 'true');

      // Filter products
      filterProducts(filter);

      // Smooth scroll to grid
      const grid = document.getElementById('akcije-grid');
      if (grid) {
        const offset = 130; // account for sticky header + filter bar
        const top = grid.getBoundingClientRect().top + window.pageYOffset - offset;
        window.scrollTo({ top: top, behavior: 'smooth' });
      }
    });
  });

  /* ============================================================
     3. SORT FUNCTIONALITY
     ============================================================ */

  const sortSelect = document.getElementById('akcije-sort');
  const productsGrid = document.getElementById('products-grid');

  function sortProducts(sortValue) {
    if (!productsGrid) return;

    const cards = Array.from(productsGrid.querySelectorAll('.akcije-card'));

    cards.sort(function (a, b) {
      const priceA   = parseFloat(a.getAttribute('data-price')) || 0;
      const priceB   = parseFloat(b.getAttribute('data-price')) || 0;
      const savingsA = parseFloat(a.getAttribute('data-savings')) || 0;
      const savingsB = parseFloat(b.getAttribute('data-savings')) || 0;

      switch (sortValue) {
        case 'usteda-desc':
          return savingsB - savingsA;
        case 'cijena-asc':
          return priceA - priceB;
        case 'cijena-desc':
          return priceB - priceA;
        case 'istice-asc':
          // All cards have same deadline in this prototype
          // In production: read data-deadline attribute
          return 0;
        default:
          return 0;
      }
    });

    // Re-append in sorted order with a subtle animation
    cards.forEach(function (card, index) {
      card.style.opacity = '0';
      card.style.transform = 'translateY(8px)';
      productsGrid.appendChild(card);

      // Stagger reveal: 30-80ms per item (Animation Guide)
      setTimeout(function () {
        card.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
        card.style.opacity = '';
        card.style.transform = '';
        setTimeout(function () {
          card.style.transition = '';
        }, 300);
      }, index * 50);
    });
  }

  if (sortSelect) {
    sortSelect.addEventListener('change', function () {
      sortProducts(this.value);
    });
  }

  /* ============================================================
     4. WISHLIST TOGGLE
     Research: WhatsApp/Viber only as optional sharing option
     Wishlist = user-to-user sharing, not primary CTA
     ============================================================ */

  const wishlistButtons = document.querySelectorAll('.akcije-card__wishlist');

  // Load saved wishlist from localStorage
  let wishlist = [];
  try {
    wishlist = JSON.parse(localStorage.getItem('de_wishlist') || '[]');
  } catch (e) {
    wishlist = [];
  }

  // Apply saved state on page load
  wishlistButtons.forEach(function (btn) {
    const productId = btn.getAttribute('data-product');
    if (wishlist.indexOf(productId) !== -1) {
      btn.classList.add('is-active');
      btn.setAttribute('aria-label', 'Ukloni iz liste zelja');
    }
  });

  wishlistButtons.forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      const productId = this.getAttribute('data-product');
      const isActive = this.classList.contains('is-active');

      if (isActive) {
        this.classList.remove('is-active');
        this.setAttribute('aria-label', 'Dodaj u listu zelja');
        wishlist = wishlist.filter(function (id) { return id !== productId; });
      } else {
        this.classList.add('is-active');
        this.setAttribute('aria-label', 'Ukloni iz liste zelja');
        if (wishlist.indexOf(productId) === -1) {
          wishlist.push(productId);
        }
      }

      try {
        localStorage.setItem('de_wishlist', JSON.stringify(wishlist));
      } catch (e) {
        // localStorage not available
      }

      // Button press feedback (Animation Guide: scale 0.97 on active)
      this.style.transform = 'scale(0.9)';
      setTimeout(function () {
        btn.style.transform = '';
      }, 160);
    });
  });

  /* ============================================================
     5. ADD TO CART (QUOTE MODEL)
     Research: Quote cart model — no online payment
     CTA: "Dodaj u ponudu" → adds to inquiry cart
     ============================================================ */

  const cartButtons = document.querySelectorAll('.btn-add-to-cart');
  const cartCountEl = document.querySelector('.header-utils__cart-count');

  // Load cart from localStorage
  let cart = [];
  try {
    cart = JSON.parse(localStorage.getItem('de_cart') || '[]');
  } catch (e) {
    cart = [];
  }

  // Update cart badge
  function updateCartBadge() {
    if (cartCountEl) {
      cartCountEl.textContent = cart.length;
      cartCountEl.style.display = cart.length > 0 ? '' : '';
    }
  }

  updateCartBadge();

  cartButtons.forEach(function (btn) {
    btn.addEventListener('click', function () {
      const productId = this.getAttribute('data-product');
      const card = this.closest('.akcije-card');

      // Get product name from card
      const nameEl = card ? card.querySelector('.akcije-card__name') : null;
      const productName = nameEl ? nameEl.textContent.trim() : productId;

      // Add to cart if not already there
      const alreadyInCart = cart.some(function (item) {
        return item.id === productId;
      });

      if (!alreadyInCart) {
        cart.push({ id: productId, name: productName });
        try {
          localStorage.setItem('de_cart', JSON.stringify(cart));
        } catch (e) {
          // localStorage not available
        }
        updateCartBadge();
      }

      // Visual feedback — button state
      const originalText = this.innerHTML;
      this.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg> Dodano';
      this.style.background = '#2D6A4F';

      const self = this;
      setTimeout(function () {
        self.innerHTML = originalText;
        self.style.background = '';
      }, 1800);

      // Button press animation
      this.style.transform = 'scale(0.97)';
      setTimeout(function () {
        btn.style.transform = '';
      }, 160);
    });
  });

  /* ============================================================
     6. CARD ENTRANCE ANIMATION
     Stagger grouped entrances by 30-80ms per item (Animation Guide)
     ============================================================ */

  function animateCardsOnLoad() {
    const cards = document.querySelectorAll('.akcije-card');
    cards.forEach(function (card, index) {
      card.style.opacity = '0';
      card.style.transform = 'translateY(16px)';

      setTimeout(function () {
        card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
        card.style.opacity = '1';
        card.style.transform = 'translateY(0)';

        setTimeout(function () {
          card.style.transition = '';
          card.style.opacity = '';
          card.style.transform = '';
        }, 350);
      }, 80 + index * 60); // stagger 60ms per card
    });
  }

  // Run entrance animation after a brief delay
  setTimeout(animateCardsOnLoad, 100);

  /* ============================================================
     7. URGENCY PROGRESS BARS — animate on scroll into view
     ============================================================ */

  function animateProgressBars() {
    const progressFills = document.querySelectorAll('.urgency-card__progress-fill');
    progressFills.forEach(function (fill) {
      const targetWidth = fill.style.width;
      fill.style.width = '0%';
      fill.style.transition = 'width 0.8s ease';

      setTimeout(function () {
        fill.style.width = targetWidth;
      }, 200);
    });
  }

  // Use IntersectionObserver if available
  if ('IntersectionObserver' in window) {
    const urgencySection = document.querySelector('.akcije-urgency');
    if (urgencySection) {
      const observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            animateProgressBars();
            observer.unobserve(entry.target);
          }
        });
      }, { threshold: 0.2 });

      observer.observe(urgencySection);
    }
  } else {
    // Fallback: animate immediately
    animateProgressBars();
  }

})();
