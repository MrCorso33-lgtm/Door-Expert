/**
 * DOOR EXPERT — Category Page JS
 * Handles: subcategory tab switching, filter groups, active filter chips,
 *          mobile filter drawer, sort, wishlist, add-to-cart
 */

document.addEventListener('DOMContentLoaded', () => {

  /* ── Subcategory tab switching ── */
  const subcatCards = document.querySelectorAll('.cat-subcat-card');
  subcatCards.forEach(card => {
    card.addEventListener('click', (e) => {
      e.preventDefault();
      subcatCards.forEach(c => c.classList.remove('active'));
      card.classList.add('active');
      // In production: filter products by subcategory
      const name = card.dataset.subcat;
      filterBySubcat(name);
    });
  });

  function filterBySubcat(subcat) {
    const cards = document.querySelectorAll('.prod-card');
    cards.forEach(card => {
      if (!subcat || subcat === 'sve' || card.dataset.subcat === subcat) {
        card.style.display = '';
      } else {
        card.style.display = 'none';
      }
    });
    updateCount();
  }

  /* ── Filter groups accordion ── */
  const filterToggles = document.querySelectorAll('.filter-group__toggle');
  filterToggles.forEach(toggle => {
    toggle.addEventListener('click', () => {
      const group = toggle.closest('.filter-group');
      group.classList.toggle('open');
    });
  });

  /* ── Checkbox filters ── */
  const filterCheckboxes = document.querySelectorAll('.filter-option input[type="checkbox"]');
  filterCheckboxes.forEach(cb => {
    cb.addEventListener('change', () => {
      applyFilters();
    });
  });

  /* ── Color swatch filters ── */
  const colorSwatches = document.querySelectorAll('.filter-color');
  colorSwatches.forEach(swatch => {
    swatch.addEventListener('click', () => {
      swatch.classList.toggle('active');
      applyFilters();
    });
  });

  /* ── Dimension button filters ── */
  const dimBtns = document.querySelectorAll('.filter-dim-btn');
  dimBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      btn.classList.toggle('active');
      applyFilters();
    });
  });

  /* ── Price inputs ── */
  const priceMin = document.getElementById('price-min');
  const priceMax = document.getElementById('price-max');
  if (priceMin) priceMin.addEventListener('input', debounce(applyFilters, 400));
  if (priceMax) priceMax.addEventListener('input', debounce(applyFilters, 400));

  /* ── Apply filters logic ── */
  function applyFilters() {
    const activeFilters = collectActiveFilters();
    renderActiveChips(activeFilters);
    filterProducts(activeFilters);
    updateCount();

    const clearBtn = document.querySelector('.cat-filters__clear');
    if (clearBtn) {
      clearBtn.classList.toggle('visible', activeFilters.length > 0);
    }
  }

  function collectActiveFilters() {
    const filters = [];

    // Checkboxes
    filterCheckboxes.forEach(cb => {
      if (cb.checked) {
        filters.push({
          type: cb.dataset.filterType,
          value: cb.value,
          label: cb.closest('.filter-option').querySelector('.filter-option__label').textContent.trim()
        });
      }
    });

    // Colors
    colorSwatches.forEach(s => {
      if (s.classList.contains('active')) {
        filters.push({ type: 'boja', value: s.dataset.color, label: s.title });
      }
    });

    // Dimensions
    dimBtns.forEach(b => {
      if (b.classList.contains('active')) {
        filters.push({ type: 'dimenzija', value: b.dataset.dim, label: b.textContent.trim() });
      }
    });

    return filters;
  }

  function filterProducts(filters) {
    const cards = document.querySelectorAll('.prod-card');
    cards.forEach(card => {
      if (filters.length === 0) {
        card.style.display = '';
        return;
      }
      // Simple demo: show all (real filtering needs data attributes)
      card.style.display = '';
    });
  }

  function updateCount() {
    const visible = document.querySelectorAll('.prod-card:not([style*="display: none"])').length;
    const countEl = document.querySelector('.cat-toolbar__count strong');
    if (countEl) countEl.textContent = visible;
  }

  /* ── Active filter chips ── */
  function renderActiveChips(filters) {
    const container = document.querySelector('.cat-active-filters');
    if (!container) return;
    container.innerHTML = '';
    filters.forEach(f => {
      const chip = document.createElement('span');
      chip.className = 'cat-filter-chip';
      chip.innerHTML = `
        ${f.label}
        <button class="cat-filter-chip__remove" aria-label="Ukloni filter">
          <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
            <path d="M1 1l10 10M11 1L1 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
          </svg>
        </button>
      `;
      chip.querySelector('.cat-filter-chip__remove').addEventListener('click', () => {
        // Uncheck/deactivate the corresponding filter
        if (f.type === 'boja') {
          const s = document.querySelector(`.filter-color[data-color="${f.value}"]`);
          if (s) s.classList.remove('active');
        } else if (f.type === 'dimenzija') {
          const b = document.querySelector(`.filter-dim-btn[data-dim="${f.value}"]`);
          if (b) b.classList.remove('active');
        } else {
          const cb = document.querySelector(`input[type="checkbox"][value="${f.value}"]`);
          if (cb) cb.checked = false;
        }
        applyFilters();
      });
      container.appendChild(chip);
    });
  }

  /* ── Clear all filters ── */
  const clearAllBtn = document.querySelector('.cat-filters__clear');
  if (clearAllBtn) {
    clearAllBtn.addEventListener('click', () => {
      filterCheckboxes.forEach(cb => cb.checked = false);
      colorSwatches.forEach(s => s.classList.remove('active'));
      dimBtns.forEach(b => b.classList.remove('active'));
      if (priceMin) priceMin.value = '';
      if (priceMax) priceMax.value = '';
      applyFilters();
    });
  }

  /* ── Sort ── */
  const sortSelect = document.querySelector('.cat-sort__select');
  if (sortSelect) {
    sortSelect.addEventListener('change', () => {
      sortProducts(sortSelect.value);
    });
  }

  function sortProducts(by) {
    const grid = document.querySelector('.cat-grid');
    if (!grid) return;
    const cards = Array.from(grid.querySelectorAll('.prod-card'));
    cards.sort((a, b) => {
      if (by === 'price-asc') {
        return parseFloat(a.dataset.price || 0) - parseFloat(b.dataset.price || 0);
      } else if (by === 'price-desc') {
        return parseFloat(b.dataset.price || 0) - parseFloat(a.dataset.price || 0);
      }
      return 0; // default: keep order
    });
    cards.forEach(c => grid.appendChild(c));
  }

  /* ── Mobile filter drawer ── */
  const filterDrawer = document.querySelector('.cat-filter-drawer');
  const openDrawerBtn = document.querySelector('.cat-filter-btn-mobile');
  const closeDrawerBtn = document.querySelector('.cat-filter-drawer__close');
  const drawerBackdrop = document.querySelector('.cat-filter-drawer__backdrop');

  function openDrawer() {
    if (filterDrawer) {
      filterDrawer.classList.add('open');
      document.body.style.overflow = 'hidden';
    }
  }

  function closeDrawer() {
    if (filterDrawer) {
      filterDrawer.classList.remove('open');
      document.body.style.overflow = '';
    }
  }

  if (openDrawerBtn) openDrawerBtn.addEventListener('click', openDrawer);
  if (closeDrawerBtn) closeDrawerBtn.addEventListener('click', closeDrawer);
  if (drawerBackdrop) drawerBackdrop.addEventListener('click', closeDrawer);

  const applyDrawerBtn = document.querySelector('.cat-filter-drawer__apply');
  if (applyDrawerBtn) {
    applyDrawerBtn.addEventListener('click', () => {
      applyFilters();
      closeDrawer();
    });
  }

  const resetDrawerBtn = document.querySelector('.cat-filter-drawer__reset');
  if (resetDrawerBtn) {
    resetDrawerBtn.addEventListener('click', () => {
      filterCheckboxes.forEach(cb => cb.checked = false);
      colorSwatches.forEach(s => s.classList.remove('active'));
      dimBtns.forEach(b => b.classList.remove('active'));
      applyFilters();
    });
  }

  /* ── Wishlist toggle ── */
  document.querySelectorAll('.prod-card__wishlist').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      btn.classList.toggle('active');
      const isActive = btn.classList.contains('active');
      btn.setAttribute('aria-label', isActive ? 'Ukloni iz liste zelja' : 'Dodaj u listu zelja');
    });
  });

  /* ── Add to cart ── */
  document.querySelectorAll('.prod-card__btn-cart').forEach(btn => {
    btn.addEventListener('click', () => {
      const originalText = btn.innerHTML;
      btn.classList.add('added');
      btn.innerHTML = `
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <polyline points="20 6 9 17 4 12"/>
        </svg>
        Dodano
      `;
      // Update cart badge in header
      const cartBadge = document.querySelector('.header-cart-badge');
      if (cartBadge) {
        const current = parseInt(cartBadge.textContent) || 0;
        cartBadge.textContent = current + 1;
        cartBadge.style.display = 'flex';
      }
      setTimeout(() => {
        btn.classList.remove('added');
        btn.innerHTML = originalText;
      }, 2000);
    });
  });

  /* ── Utility: debounce ── */
  function debounce(fn, delay) {
    let timer;
    return (...args) => {
      clearTimeout(timer);
      timer = setTimeout(() => fn(...args), delay);
    };
  }

  // Open first filter group by default
  const firstGroup = document.querySelector('.filter-group');
  if (firstGroup) firstGroup.classList.add('open');

});
