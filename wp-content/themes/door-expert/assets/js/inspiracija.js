/**
 * INSPIRACIJA.JS — Door Expert
 * Room filter tabs, grid category filter, load more, hotspot tags
 */

document.addEventListener('DOMContentLoaded', () => {

  /* ── ROOM TABS (hero) ─────────────────────── */
  const roomTabs = document.querySelectorAll('.inspo-room-tab');
  const inspoCards = document.querySelectorAll('.inspo-card');

  roomTabs.forEach(tab => {
    tab.addEventListener('click', () => {
      roomTabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');

      const room = tab.dataset.room;
      inspoCards.forEach(card => {
        if (room === 'sve' || card.dataset.room === room) {
          card.classList.remove('hidden');
        } else {
          card.classList.add('hidden');
        }
      });
    });
  });

  /* ── GRID FILTER BUTTONS ─────────────────── */
  const filterBtns = document.querySelectorAll('.inspo-filter-btn');

  filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      filterBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');

      const filter = btn.dataset.filter;
      inspoCards.forEach(card => {
        const cats = card.dataset.cats || '';
        if (filter === 'sve' || cats.includes(filter)) {
          card.classList.remove('hidden');
        } else {
          card.classList.add('hidden');
        }
      });
    });
  });

  /* ── LOAD MORE ───────────────────────────── */
  const loadMoreBtn = document.getElementById('loadMoreBtn');
  if (loadMoreBtn) {
    loadMoreBtn.addEventListener('click', () => {
      // In production, this would fetch more items from the server
      // For prototype: show a toast message
      showToast('Učitavanje dodatnih projekata — funkcija dostupna u WordPress verziji');
    });
  }

  /* ── HOTSPOT ACCESSIBILITY ───────────────── */
  // Close tooltips on Escape
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      document.querySelectorAll('.inspo-hotspot').forEach(h => h.blur());
    }
  });

  /* ── SMOOTH SCROLL FOR HERO CTA ──────────── */
  const heroCtaLink = document.querySelector('a[href="#projekti"]');
  if (heroCtaLink) {
    heroCtaLink.addEventListener('click', (e) => {
      e.preventDefault();
      const target = document.getElementById('projekti');
      if (target) {
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  }

  /* ── CART COUNT FROM LOCALSTORAGE ────────── */
  try {
    const cart = JSON.parse(localStorage.getItem('de_cart') || '[]');
    const countEl = document.getElementById('cartCount');
    if (countEl && cart.length > 0) {
      countEl.textContent = cart.length;
      countEl.style.display = 'flex';
    }
  } catch (e) {}

  /* ── TOAST HELPER ────────────────────────── */
  function showToast(msg) {
    const existing = document.querySelector('.inspo-toast');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.className = 'inspo-toast';
    toast.textContent = msg;
    toast.style.cssText = `
      position: fixed;
      bottom: 5rem;
      left: 50%;
      transform: translateX(-50%);
      background: #3D2B1F;
      color: white;
      font-family: 'DM Sans', sans-serif;
      font-size: 0.875rem;
      padding: 0.85rem 1.5rem;
      z-index: 9999;
      border-left: 3px solid #A07840;
      max-width: 90vw;
      text-align: center;
      animation: toastIn 0.3s cubic-bezier(0.23,1,0.32,1);
    `;

    const style = document.createElement('style');
    style.textContent = `@keyframes toastIn { from { opacity:0; transform: translateX(-50%) translateY(8px); } to { opacity:1; transform: translateX(-50%) translateY(0); } }`;
    document.head.appendChild(style);

    document.body.appendChild(toast);
    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transition = 'opacity 0.3s';
      setTimeout(() => toast.remove(), 300);
    }, 3500);
  }

});
