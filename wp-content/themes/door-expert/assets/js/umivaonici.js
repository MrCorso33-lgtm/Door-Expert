/**
 * umivaonici.js – Dekorativni umivaonici Bathco
 * Handles: shape navigation, product filters, color swatches, cart
 */

(function () {
  'use strict';

  // ─── Shape pill navigation ──────────────────────────────────
  const shapePills = document.querySelectorAll('.umi-shape-pill[data-shape]');

  shapePills.forEach(pill => {
    pill.addEventListener('click', function (e) {
      shapePills.forEach(p => p.classList.remove('umi-shape-pill--active'));
      this.classList.add('umi-shape-pill--active');

      const shape = this.dataset.shape;
      // Activate corresponding filter checkbox
      const checkbox = document.querySelector(`input[data-filter-type="oblik"][value="${shape}"]`);
      if (checkbox) {
        // Uncheck all shape filters first
        document.querySelectorAll('input[data-filter-type="oblik"]').forEach(cb => {
          cb.checked = false;
        });
        checkbox.checked = true;
        applyFilters();
      }
    });
  });

  // ─── Color swatches on product cards ──────────────────────────
  document.querySelectorAll('.umi-card__colors').forEach(colorGroup => {
    colorGroup.querySelectorAll('.umi-color-dot').forEach(dot => {
      dot.addEventListener('click', function () {
        colorGroup.querySelectorAll('.umi-color-dot').forEach(d => d.classList.remove('active'));
        this.classList.add('active');
      });
    });
  });

  // ─── Filter logic (reuse from category.js pattern) ──────────
  function applyFilters() {
    const activeFilters = {};
    document.querySelectorAll('.filter-option input:checked').forEach(cb => {
      const type = cb.dataset.filterType;
      if (!activeFilters[type]) activeFilters[type] = [];
      activeFilters[type].push(cb.value);
    });

    const cards = document.querySelectorAll('#product-grid .prod-card');
    let visibleCount = 0;

    cards.forEach(card => {
      let show = true;

      if (activeFilters.oblik && activeFilters.oblik.length > 0) {
        if (!activeFilters.oblik.includes(card.dataset.oblik)) show = false;
      }
      if (activeFilters.materijal && activeFilters.materijal.length > 0) {
        if (!activeFilters.materijal.includes(card.dataset.materijal)) show = false;
      }
      if (activeFilters.ugradnja && activeFilters.ugradnja.length > 0) {
        if (!activeFilters.ugradnja.includes(card.dataset.ugradnja)) show = false;
      }
      if (activeFilters.akcija) {
        const hasPromo = card.querySelector('.prod-badge--promo');
        if (!hasPromo) show = false;
      }

      card.style.display = show ? '' : 'none';
      if (show) visibleCount++;
    });

    // Update count
    const countEl = document.querySelector('.cat-toolbar__count strong');
    if (countEl) countEl.textContent = visibleCount;
  }

  // Attach filter change listeners
  document.querySelectorAll('.filter-option input').forEach(cb => {
    cb.addEventListener('change', applyFilters);
  });

  // Reset filters
  const resetBtn = document.getElementById('reset-filters');
  if (resetBtn) {
    resetBtn.addEventListener('click', function () {
      document.querySelectorAll('.filter-option input').forEach(cb => {
        cb.checked = false;
      });
      document.querySelectorAll('.cat-color-swatch').forEach(s => s.classList.remove('active'));
      shapePills.forEach(p => p.classList.remove('umi-shape-pill--active'));
      applyFilters();
    });
  }

  // ─── Sort ────────────────────────────────────────────────────
  const sortSelect = document.getElementById('sort-select');
  if (sortSelect) {
    sortSelect.addEventListener('change', function () {
      const grid = document.getElementById('product-grid');
      const cards = Array.from(grid.querySelectorAll('.prod-card'));

      cards.sort((a, b) => {
        const priceA = parseFloat(a.dataset.price) || 0;
        const priceB = parseFloat(b.dataset.price) || 0;

        if (this.value === 'price-asc') return priceA - priceB;
        if (this.value === 'price-desc') return priceB - priceA;
        return 0;
      });

      cards.forEach(card => grid.appendChild(card));
    });
  }

  /*
   * Ovdje je bio demo "dodaj u korpu" iz prototipa (natpis "Dodato" + brojac +1,
   * bez ikakvog upisa u korpu). Kartica sada ima pravo WooCommerce dodavanje
   * (.prod-card__add + ajax_add_to_cart), a brojac osvjezava header.js.
   */

  // ─── Wishlist ────────────────────────────────────────────────
  document.querySelectorAll('.prod-card__wishlist').forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      this.classList.toggle('active');
      const svg = this.querySelector('svg');
      if (this.classList.contains('active')) {
        svg.style.fill = 'var(--color-jantar)';
        svg.style.stroke = 'var(--color-jantar)';
      } else {
        svg.style.fill = 'none';
        svg.style.stroke = 'currentColor';
      }
    });
  });

  // ─── Mobile filter drawer ────────────────────────────────────
  const openFilterBtn = document.getElementById('open-filter-drawer');
  const filterSidebar = document.getElementById('filter-sidebar');
  const filterBackdrop = document.getElementById('filter-backdrop');
  const applyMobileBtn = document.getElementById('apply-filters-mobile');

  if (openFilterBtn && filterSidebar) {
    openFilterBtn.addEventListener('click', () => {
      filterSidebar.classList.add('cat-filters--open');
      filterBackdrop?.classList.add('cat-filter-backdrop--visible');
      document.body.style.overflow = 'hidden';
    });

    const closeDrawer = () => {
      filterSidebar.classList.remove('cat-filters--open');
      filterBackdrop?.classList.remove('cat-filter-backdrop--visible');
      document.body.style.overflow = '';
    };

    filterBackdrop?.addEventListener('click', closeDrawer);
    applyMobileBtn?.addEventListener('click', () => {
      applyFilters();
      closeDrawer();
    });
  }

  // ─── Filter group toggles ────────────────────────────────────
  document.querySelectorAll('.filter-group__toggle').forEach(title => {
    title.addEventListener('click', function () {
      const group = this.closest('.filter-group');
      const body = group.querySelector('.filter-group__body');
      const chevron = this.querySelector('.filter-group__chevron');

      if (body) {
        const isOpen = body.style.display !== 'none';
        body.style.display = isOpen ? 'none' : '';
        if (chevron) chevron.style.transform = isOpen ? 'rotate(-90deg)' : '';
      }
    });
  });

})();
