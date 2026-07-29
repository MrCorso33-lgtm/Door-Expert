/**
 * DOOR EXPERT — Room Navigation Tab Switcher
 * ============================================================
 * Handles animated tab switching for the room-based navigation.
 * Research: UX Research Navigation (line 7-9) — room nav as
 * secondary discovery path, atmospheric image + products.
 * ============================================================
 */

(function () {
  'use strict';

  const tabs = document.querySelectorAll('.room-tab');
  const panels = document.querySelectorAll('.room-panel');

  if (!tabs.length || !panels.length) return;

  function activateTab(tabEl) {
    const target = tabEl.dataset.room;

    // Update tab states
    tabs.forEach(t => t.classList.remove('active'));
    tabEl.classList.add('active');

    // Animate panel transition
    panels.forEach(panel => {
      if (panel.dataset.room === target) {
        panel.classList.add('animating');
        panel.style.display = 'grid';

        // Force reflow then animate in
        requestAnimationFrame(() => {
          requestAnimationFrame(() => {
            panel.classList.add('active');
            panel.classList.remove('animating');
          });
        });
      } else {
        panel.classList.remove('active', 'animating');
        // Hide after transition
        panel.addEventListener('transitionend', function hide() {
          if (!panel.classList.contains('active')) {
            panel.style.display = 'none';
          }
          panel.removeEventListener('transitionend', hide);
        }, { once: true });
        // Fallback hide
        setTimeout(() => {
          if (!panel.classList.contains('active')) {
            panel.style.display = 'none';
          }
        }, 280);
      }
    });
  }

  // Init: show first tab
  if (tabs[0]) {
    tabs[0].classList.add('active');
    const firstPanel = document.querySelector('.room-panel[data-room="' + tabs[0].dataset.room + '"]');
    if (firstPanel) {
      firstPanel.classList.add('active');
      firstPanel.style.display = 'grid';
    }
  }

  // Bind click events
  tabs.forEach(tab => {
    tab.addEventListener('click', () => activateTab(tab));
  });

})();
