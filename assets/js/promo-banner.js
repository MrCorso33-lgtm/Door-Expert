/**
 * DOOR EXPERT — Promo Banner Countdown Timer
 * ============================================================
 * Research: Discounts doc (line 38): "Ako se rok produžava,
 * ne resetuj isti countdown; objavi novu akciju ili novi talas
 * ponude. Resetujući tajmer je najkraći put ka gubitku poverenja."
 *
 * This timer targets a REAL date (end of current month).
 * It never resets. When expired, it hides itself gracefully.
 * ============================================================
 */

(function () {
  'use strict';

  function initPromoBanner() {
    // ── Set deadline to end of current month ───────────────────
    // In production this would come from a CMS field.
    var now = new Date();
    var deadline = new Date(now.getFullYear(), now.getMonth() + 1, 0, 23, 59, 59);

    var daysEl   = document.getElementById('pb-days');
    var hoursEl  = document.getElementById('pb-hours');
    var minsEl   = document.getElementById('pb-mins');
    var secsEl   = document.getElementById('pb-secs');
    var deadlineEl = document.getElementById('pb-deadline-text');

    if (!daysEl || !hoursEl || !minsEl || !secsEl) return;

    // Set human-readable deadline text
    if (deadlineEl) {
      var opts = { day: 'numeric', month: 'long', year: 'numeric' };
      deadlineEl.textContent = 'Ponuda važi do ' + deadline.toLocaleDateString('sr-Latn-ME', opts);
    }

    function pad(n) {
      return String(n).padStart(2, '0');
    }

    var isFirstTick = true;

    function tick() {
      var diff = deadline - new Date();

      if (diff <= 0) {
        // Expired — hide countdown section gracefully
        var section = document.querySelector('.promo-banner');
        if (section) {
          section.style.transition = 'opacity 600ms ease';
          section.style.opacity = '0';
          setTimeout(function() { section.style.display = 'none'; }, 650);
        }
        return;
      }

      var totalSecs = Math.floor(diff / 1000);
      var d = Math.floor(totalSecs / 86400);
      var h = Math.floor((totalSecs % 86400) / 3600);
      var m = Math.floor((totalSecs % 3600) / 60);
      var s = totalSecs % 60;

      if (isFirstTick) {
        // First tick: set values immediately without animation
        daysEl.textContent  = pad(d);
        hoursEl.textContent = pad(h);
        minsEl.textContent  = pad(m);
        secsEl.textContent  = pad(s);
        isFirstTick = false;
      } else {
        // Subsequent ticks: smooth opacity dip on change
        update(daysEl,  d);
        update(hoursEl, h);
        update(minsEl,  m);
        update(secsEl,  s);
      }
    }

    function update(el, val) {
      if (!el) return;
      var formatted = pad(val);
      if (el.textContent !== formatted) {
        el.style.opacity = '0.4';
        setTimeout(function() {
          el.textContent = formatted;
          el.style.opacity = '1';
        }, 80);
      }
    }

    tick();
    setInterval(tick, 1000);
  }

  // Ensure DOM is ready before running
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPromoBanner);
  } else {
    initPromoBanner();
  }

})();
