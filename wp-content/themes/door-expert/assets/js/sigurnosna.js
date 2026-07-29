/**
 * DOOR EXPERT — Sigurnosna vrata page JS
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

  // ── FAQ accordion — close others when one opens ──
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

  // ── Cart button interactions (reuse from category.js pattern) ──
  document.querySelectorAll('.prod-card__btn-cart').forEach(btn => {
    btn.addEventListener('click', function() {
      const card = this.closest('.prod-card');
      const name = card.querySelector('.prod-card__name')?.textContent || 'Proizvod';
      const price = card.querySelector('.prod-card__price')?.textContent || '';

      // Visual feedback
      const original = this.innerHTML;
      this.innerHTML = `
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <polyline points="20 6 9 17 4 12"/>
        </svg>
        Dodato
      `;
      this.style.background = 'var(--color-success, #2E7D32)';
      this.style.borderColor = 'var(--color-success, #2E7D32)';
      this.style.color = '#fff';

      // Update cart badge
      const badge = document.querySelector('.header-btn__badge');
      if (badge) {
        const current = parseInt(badge.textContent) || 0;
        badge.textContent = current + 1;
        badge.style.display = 'flex';
      }

      setTimeout(() => {
        this.innerHTML = original;
        this.style.background = '';
        this.style.borderColor = '';
        this.style.color = '';
      }, 2000);
    });
  });

  // ── Wishlist toggle ──
  document.querySelectorAll('.prod-card__wishlist').forEach(btn => {
    btn.addEventListener('click', function() {
      this.classList.toggle('active');
      const isActive = this.classList.contains('active');
      this.style.color = isActive ? 'var(--color-jantar)' : '';
      this.style.fill = isActive ? 'var(--color-jantar)' : '';
      const svg = this.querySelector('path');
      if (svg) {
        svg.style.fill = isActive ? 'var(--color-jantar)' : 'none';
        svg.style.stroke = isActive ? 'var(--color-jantar)' : 'currentColor';
      }
    });
  });

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
