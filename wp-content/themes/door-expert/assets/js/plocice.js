/**
 * DOOR EXPERT — Keramičke pločice page JS
 * ==========================================
 * - Brand filter (top strip)
 * - Subcategory filter (namjena tabs)
 * - Sidebar checkbox filters
 * - m² Calculator
 * - Color swatch selection
 */

document.addEventListener('DOMContentLoaded', () => {

  /* ─── Brand filter ─── */
  const brandCards = document.querySelectorAll('.plo-brand-card');
  brandCards.forEach(card => {
    card.addEventListener('click', () => {
      brandCards.forEach(c => c.classList.remove('plo-brand-card--active'));
      card.classList.add('plo-brand-card--active');
      const brand = card.dataset.brand;
      filterProducts({ brend: brand === 'sve' ? null : brand });
    });
  });

  /* ─── Subcategory tabs ─── */
  const subcatCards = document.querySelectorAll('.cat-subcat-card');
  subcatCards.forEach(card => {
    card.addEventListener('click', (e) => {
      e.preventDefault();
      subcatCards.forEach(c => c.classList.remove('active'));
      card.classList.add('active');
      const subcat = card.dataset.subcat;
      filterProducts({ namjena: subcat === 'sve' ? null : subcat });
    });
  });

  /* ─── Sidebar filters ─── */
  const filterCheckboxes = document.querySelectorAll('[data-filter-type]');
  filterCheckboxes.forEach(cb => {
    cb.addEventListener('change', applyAllFilters);
  });

  const colorSwatches = document.querySelectorAll('.cat-color-swatch');
  colorSwatches.forEach(swatch => {
    swatch.addEventListener('click', () => {
      swatch.classList.toggle('active');
      applyAllFilters();
    });
  });

  const resetBtn = document.getElementById('reset-filters');
  if (resetBtn) {
    resetBtn.addEventListener('click', () => {
      filterCheckboxes.forEach(cb => cb.checked = false);
      colorSwatches.forEach(s => s.classList.remove('active'));
      subcatCards.forEach(c => c.classList.remove('active'));
      if (subcatCards[0]) subcatCards[0].classList.add('active');
      brandCards.forEach(c => c.classList.remove('plo-brand-card--active'));
      if (brandCards[0]) brandCards[0].classList.add('plo-brand-card--active');
      showAllProducts();
    });
  }

  function applyAllFilters() {
    const activeFilters = {};
    filterCheckboxes.forEach(cb => {
      if (cb.checked) {
        const type = cb.dataset.filterType;
        if (!activeFilters[type]) activeFilters[type] = [];
        activeFilters[type].push(cb.value);
      }
    });
    const activeColors = [...colorSwatches]
      .filter(s => s.classList.contains('active'))
      .map(s => s.dataset.color);
    if (activeColors.length) activeFilters.boja = activeColors;

    const activeBrand = document.querySelector('.plo-brand-card--active');
    if (activeBrand && activeBrand.dataset.brand !== 'sve') {
      activeFilters.brend = [activeBrand.dataset.brand];
    }

    const activeSubcat = document.querySelector('.cat-subcat-card.active');
    if (activeSubcat && activeSubcat.dataset.subcat !== 'sve') {
      activeFilters.namjena = activeSubcat.dataset.subcat;
    }

    filterProducts(activeFilters);
  }

  function filterProducts(filters = {}) {
    const cards = document.querySelectorAll('.prod-card');
    let visible = 0;

    cards.forEach(card => {
      let show = true;

      if (filters.brend) {
        const brands = Array.isArray(filters.brend) ? filters.brend : [filters.brend];
        const cardBrend = card.dataset.brend || '';
        if (!brands.some(b => cardBrend.includes(b))) show = false;
      }

      if (filters.namjena && show) {
        const cardNamjena = card.dataset.namjena || '';
        if (!cardNamjena.includes(filters.namjena)) show = false;
      }

      if (filters.format && show) {
        const formats = Array.isArray(filters.format) ? filters.format : [filters.format];
        const cardFormat = card.dataset.format || '';
        if (!formats.some(f => cardFormat.includes(f))) show = false;
      }

      if (show) {
        card.style.display = '';
        card.style.animation = 'fadeInUp 0.3s ease-out both';
        visible++;
      } else {
        card.style.display = 'none';
      }
    });

    // Update count
    const countEl = document.querySelector('.cat-toolbar__count strong');
    if (countEl) countEl.textContent = visible;
  }

  function showAllProducts() {
    const cards = document.querySelectorAll('.prod-card');
    cards.forEach(card => { card.style.display = ''; });
    const countEl = document.querySelector('.cat-toolbar__count strong');
    if (countEl) countEl.textContent = cards.length;
  }

  /* ─── Filter accordion (sidebar groups) ─── */
  const filterGroupTitles = document.querySelectorAll('.filter-group__toggle');
  filterGroupTitles.forEach(title => {
    title.addEventListener('click', () => {
      const group = title.closest('.filter-group');
      const body = group.querySelector('.filter-group__body');
      const chevron = title.querySelector('.filter-group__chevron');
      if (body) {
        const isOpen = body.style.display !== 'none';
        body.style.display = isOpen ? 'none' : '';
        if (chevron) chevron.style.transform = isOpen ? 'rotate(-90deg)' : '';
      }
    });
  });

  /* ─── Mobile filter drawer ─── */
  const openFilterBtn = document.getElementById('open-filter-drawer');
  const filterSidebar = document.getElementById('filter-sidebar');
  const filterBackdrop = document.getElementById('filter-backdrop');
  const applyMobileBtn = document.getElementById('apply-filters-mobile');

  if (openFilterBtn && filterSidebar) {
    openFilterBtn.addEventListener('click', () => {
      filterSidebar.classList.add('open');
      if (filterBackdrop) filterBackdrop.classList.add('visible');
      document.body.style.overflow = 'hidden';
    });
    const closeDrawer = () => {
      filterSidebar.classList.remove('open');
      if (filterBackdrop) filterBackdrop.classList.remove('visible');
      document.body.style.overflow = '';
    };
    if (filterBackdrop) filterBackdrop.addEventListener('click', closeDrawer);
    if (applyMobileBtn) applyMobileBtn.addEventListener('click', () => {
      applyAllFilters();
      closeDrawer();
    });
  }

  /* ─── Sort ─── */
  const sortSelect = document.getElementById('sort-select');
  if (sortSelect) {
    sortSelect.addEventListener('change', () => {
      const grid = document.getElementById('product-grid');
      if (!grid) return;
      const cards = [...grid.querySelectorAll('.prod-card')];
      const val = sortSelect.value;

      cards.sort((a, b) => {
        const pa = parseFloat(a.dataset.price) || 0;
        const pb = parseFloat(b.dataset.price) || 0;
        if (val === 'price-asc') return pa - pb;
        if (val === 'price-desc') return pb - pa;
        return 0;
      });

      cards.forEach(c => grid.appendChild(c));
    });
  }

  /* ─── Variant pills on cards ─── */
  document.querySelectorAll('.prod-card__variants').forEach(varGroup => {
    varGroup.querySelectorAll('.prod-card__variant').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        varGroup.querySelectorAll('.prod-card__variant').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
      });
    });
  });

  /* ─── Wishlist buttons ─── */
  document.querySelectorAll('.prod-card__wishlist').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      btn.classList.toggle('active');
      btn.setAttribute('aria-label', btn.classList.contains('active') ? 'Ukloni iz liste želja' : 'Dodaj u listu želja');
    });
  });

  /* ─── Add to cart ─── */
  document.querySelectorAll('.prod-card__btn-cart').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const card = btn.closest('.prod-card');
      const name = card?.querySelector('.prod-card__name')?.textContent || 'Pločice';
      btn.textContent = '✓ Dodano';
      btn.style.background = 'var(--color-success, #4CAF50)';
      btn.style.color = '#fff';
      setTimeout(() => {
        btn.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Dodaj u ponudu`;
        btn.style.background = '';
        btn.style.color = '';
      }, 2000);

      // Update cart badge
      const badge = document.querySelector('.header-btn__badge');
      if (badge) {
        const current = parseInt(badge.textContent) || 0;
        badge.textContent = current + 1;
        badge.style.display = 'flex';
      }
    });
  });

  /* ─── m² Calculator ─── */
  const calcBtn = document.getElementById('calc-btn');
  const calcResult = document.getElementById('calc-result');

  if (calcBtn) {
    calcBtn.addEventListener('click', () => {
      const width = parseFloat(document.getElementById('calc-width')?.value) || 0;
      const height = parseFloat(document.getElementById('calc-height')?.value) || 0;
      const wastePercent = parseFloat(document.getElementById('calc-waste')?.value) || 10;
      const pricePerSqm = parseFloat(document.getElementById('calc-price')?.value) || 0;

      if (width <= 0 || height <= 0) {
        // Shake the inputs
        ['calc-width', 'calc-height'].forEach(id => {
          const el = document.getElementById(id);
          if (el && !parseFloat(el.value)) {
            el.style.borderColor = '#E53E3E';
            setTimeout(() => { el.style.borderColor = ''; }, 2000);
          }
        });
        return;
      }

      const sqm = width * height;
      const totalWithWaste = sqm * (1 + wastePercent / 100);
      const totalPrice = pricePerSqm > 0 ? totalWithWaste * pricePerSqm : null;

      document.getElementById('result-sqm').textContent = sqm.toFixed(2) + ' m²';
      document.getElementById('result-total').textContent = totalWithWaste.toFixed(2) + ' m²';
      document.getElementById('result-price').textContent = totalPrice
        ? Math.ceil(totalPrice) + ' EUR'
        : '—';

      if (calcResult) {
        calcResult.style.display = 'block';
        calcResult.style.animation = 'fadeInUp 0.4s ease-out both';
        calcResult.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }
    });
  }

  /* ─── Fade-in animation ─── */
  const style = document.createElement('style');
  style.textContent = `
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(12px); }
      to   { opacity: 1; transform: translateY(0); }
    }
  `;
  document.head.appendChild(style);

});
