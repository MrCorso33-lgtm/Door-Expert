/**
 * 404.JS — Door Expert
 * Search, chip suggestions, cart count
 */

document.addEventListener('DOMContentLoaded', () => {

  /* ── SEARCH ──────────────────────────────── */
  const searchInput = document.getElementById('e404SearchInput');
  const searchBtn   = document.getElementById('e404SearchBtn');

  function doSearch() {
    const query = searchInput ? searchInput.value.trim() : '';
    if (!query) return;
    // In production: redirect to search results page
    // For prototype: map keywords to category pages
    const q = query.toLowerCase();
    let dest = 'header-demo.html';

    if (q.includes('sobna') || q.includes('klizna') || q.includes('staklena') || q.includes('vrata') && !q.includes('sigurnosna')) {
      dest = 'sobna-vrata.html';
    } else if (q.includes('sigurnosna') || q.includes('rc2') || q.includes('rc3') || q.includes('blindirana')) {
      dest = 'sigurnosna-vrata.html';
    } else if (q.includes('plo') || q.includes('keramika') || q.includes('bazen') || q.includes('stepenice') || q.includes('tau') || q.includes('arcana')) {
      dest = 'keramicke-plocice.html';
    } else if (q.includes('umivaonik') || q.includes('bathco') || q.includes('kamen') || q.includes('oval') || q.includes('lavabo')) {
      dest = 'umivaonici.html';
    } else if (q.includes('akcija') || q.includes('popust') || q.includes('sniženje')) {
      dest = 'akcije.html';
    }

    window.location.href = dest;
  }

  if (searchBtn) {
    searchBtn.addEventListener('click', doSearch);
  }

  if (searchInput) {
    searchInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') doSearch();
    });
    // Auto-focus on desktop
    if (window.innerWidth > 768) {
      setTimeout(() => searchInput.focus(), 400);
    }
  }

  /* ── CHIP SUGGESTIONS ────────────────────── */
  document.querySelectorAll('.e404-search__chip').forEach(chip => {
    chip.addEventListener('click', () => {
      const query = chip.dataset.query;
      if (searchInput) {
        searchInput.value = query;
        searchInput.focus();
      }
      doSearch();
    });
  });

  /* ── CART COUNT ──────────────────────────── */
  try {
    const cart = JSON.parse(localStorage.getItem('de_cart') || '[]');
    const countEl = document.getElementById('cartCount');
    if (countEl && cart.length > 0) {
      countEl.textContent = cart.length;
      countEl.style.display = 'flex';
    }
  } catch (e) {}

});
