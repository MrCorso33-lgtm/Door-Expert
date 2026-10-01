/**
 * DOOR EXPERT – Sigurnosna vrata page JS
 * Handles: subcategory tab switching, RC class filter highlight,
 * smooth scroll to catalog, FAQ accordion enhancement
 */

(function() {
  'use strict';

  // ── Subcategory tab switching ──
  const subcatCards = document.querySelectorAll('.cat-subcat-card');
  const productCards = document.querySelectorAll('.prod-card');

  subcatCards.forEach(card => {
    card.addEventListener('click', function(e) {
      e.preventDefault();
      const subcat = this.dataset.subcat;

      // Update active tab
      subcatCards.forEach(c => c.classList.remove('active'));
      this.classList.add('active');

      // Filter products
      productCards.forEach(prod => {
        if (subcat === 'sve') {
          prod.style.display = '';
          prod.style.animation = 'fadeIn 0.3s ease';
        } else {
          const prodSubcat = prod.dataset.namjena || '';
          if (prodSubcat.includes(subcat)) {
            prod.style.display = '';
            prod.style.animation = 'fadeIn 0.3s ease';
          } else {
            prod.style.display = 'none';
          }
        }
      });

      // Update count
      const visibleCount = Array.from(productCards).filter(p => p.style.display !== 'none').length;
      const countEl = document.querySelector('.cat-toolbar__count strong');
      if (countEl) countEl.textContent = visibleCount;

      // Smooth scroll to catalog
      const catalog = document.getElementById('katalog');
      if (catalog) {
        setTimeout(() => {
          catalog.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 100);
      }
    });
  });

  // ── RC class filter from explainer section ──
  const rcCtaLinks = document.querySelectorAll('.rc-card__cta');
  rcCtaLinks.forEach(link => {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      const text = this.textContent.toLowerCase();
      let targetClass = null;
      if (text.includes('rc2')) targetClass = 'rc2';
      if (text.includes('rc3')) targetClass = 'rc3';

      if (targetClass) {
        // Check the corresponding filter checkbox
        const checkbox = document.querySelector(`input[value="${targetClass}"][data-filter-type="klasa"]`);
        if (checkbox) {
          checkbox.checked = true;
          checkbox.dispatchEvent(new Event('change', { bubbles: true }));
        }
        // Scroll to catalog
        const catalog = document.getElementById('katalog');
        if (catalog) {
          catalog.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      }
    });
  });

  // ── Hero "Pogledaj modele" smooth scroll ──
  const heroBtn = document.querySelector('.sec-hero__btn--primary[href="#katalog"]');
  if (heroBtn) {
    heroBtn.addEventListener('click', function(e) {
      e.preventDefault();
      const catalog = document.getElementById('katalog');
      if (catalog) catalog.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  }

  // ── FAQ accordion – close others when one opens ──
  const faqItems = document.querySelectorAll('.sec-faq__item');
  faqItems.forEach(item => {
    item.addEventListener('toggle', function() {
      if (this.open) {
        faqItems.forEach(other => {
          if (other !== this && other.open) {
            other.open = false;
          }
        });
      }
    });
  });

  /*
   * Ovdje je bio demo "dodaj u korpu" iz prototipa (natpis "Dodato" + brojac +1,
   * bez ikakvog upisa u korpu). Kartica sada ima pravo WooCommerce dodavanje
   * (.prod-card__add + ajax_add_to_cart), a brojac osvjezava header.js.
   */

  // Srce na karticama radi assets/js/wishlist.js (ovdje je bio demo toggle iz prototipa).

  // ── Dimension variant selection on product cards ──
  document.querySelectorAll('.prod-card__variant').forEach(btn => {
    btn.addEventListener('click', function() {
      const card = this.closest('.prod-card');
      card.querySelectorAll('.prod-card__variant').forEach(v => v.classList.remove('active'));
      this.classList.add('active');
    });
  });

  // ── Load more (demo) ──
  const loadMoreBtn = document.querySelector('.cat-load-more');
  if (loadMoreBtn) {
    loadMoreBtn.addEventListener('click', function() {
      this.textContent = 'Svi modeli su prikazani';
      this.disabled = true;
      this.style.opacity = '0.5';
      this.style.cursor = 'default';
    });
  }

  // ── Mobile filter drawer ──
  const openFilterBtn = document.getElementById('open-filter-drawer');
  const closeFilterBtn = document.getElementById('close-filter-drawer');
  const filterDrawer = document.getElementById('filter-drawer');
  const filterBackdrop = document.getElementById('filter-backdrop');

  if (openFilterBtn && filterDrawer) {
    openFilterBtn.addEventListener('click', () => {
      filterDrawer.classList.add('open');
      filterBackdrop.classList.add('visible');
      document.body.style.overflow = 'hidden';
    });
    const closeDrawer = () => {
      filterDrawer.classList.remove('open');
      filterBackdrop.classList.remove('visible');
      document.body.style.overflow = '';
    };
    if (closeFilterBtn) closeFilterBtn.addEventListener('click', closeDrawer);
    if (filterBackdrop) filterBackdrop.addEventListener('click', closeDrawer);
    const applyBtn = document.querySelector('.cat-filter-drawer__apply');
    if (applyBtn) applyBtn.addEventListener('click', closeDrawer);
  }

})();
