/**
 * DOOR EXPERT — Featured Products: Tab Filter + Wishlist
 * ============================================================
 */

(function () {
  'use strict';

  // ── Tab filter ──────────────────────────────────────────────
  const tabs = document.querySelectorAll('.featured__tab');
  const cards = document.querySelectorAll('.prod-card[data-cat]');

  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      tabs.forEach(t => t.classList.remove('is-active'));
      tab.classList.add('is-active');

      const filter = tab.dataset.filter;

      cards.forEach(card => {
        if (filter === 'sve' || card.dataset.cat === filter) {
          card.style.display = '';
          // small entrance animation
          card.style.opacity = '0';
          card.style.transform = 'translateY(8px)';
          requestAnimationFrame(() => {
            card.style.transition = 'opacity 220ms ease, transform 220ms cubic-bezier(0.23,1,0.32,1)';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
          });
        } else {
          card.style.display = 'none';
        }
      });
    });
  });

  // ── Wishlist toggle ─────────────────────────────────────────
  const wishlistBtns = document.querySelectorAll('.prod-card__wishlist');

  wishlistBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      btn.classList.toggle('is-saved');
      const saved = btn.classList.contains('is-saved');
      btn.setAttribute('aria-label', saved ? 'Ukloni iz liste zelja' : 'Sacuvaj u listu zelja');

      // Micro-bounce
      btn.style.transform = 'scale(1.25)';
      setTimeout(() => { btn.style.transform = ''; }, 160);
    });
  });

  // ── Add to cart ─────────────────────────────────────────────
  const addBtns = document.querySelectorAll('.prod-card__add');

  addBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();

      const originalText = btn.innerHTML;
      btn.innerHTML = `
        <svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round">
          <polyline points="20 6 9 17 4 12"/>
        </svg>
        Dodano u korpu
      `;
      btn.style.background = 'var(--color-antracit)';
      btn.style.color = '#fff';

      // Update cart badge count (demo)
      const badge = document.querySelector('.header__cart-badge');
      if (badge) {
        const current = parseInt(badge.textContent) || 0;
        badge.textContent = current + 1;
        badge.style.display = 'flex';
        badge.style.transform = 'scale(1.4)';
        setTimeout(() => { badge.style.transform = ''; }, 200);
      }

      setTimeout(() => {
        btn.innerHTML = originalText;
        btn.style.background = '';
        btn.style.color = '';
      }, 2000);
    });
  });

})();
