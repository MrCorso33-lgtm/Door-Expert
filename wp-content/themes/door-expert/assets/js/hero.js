/**
 * DOOR EXPERT — Hero Slider
 * ============================================================
 * Manual slide navigation only (no auto-rotation)
 * Research: NN/g + Baymard — carousels are skipped; manual is better
 *
 * Features:
 * - Manual prev/next + dot navigation
 * - Staggered entrance animations per slide
 * - Real countdown to a fixed end date (Slide 2)
 * - Keyboard accessible (arrow keys when hero is focused)
 * ============================================================
 */

(function () {
  'use strict';

  const hero = document.querySelector('.hero');
  if (!hero) return;

  const track    = hero.querySelector('.hero__track');
  const slides   = hero.querySelectorAll('.hero__slide');
  const dots     = hero.querySelectorAll('.hero__dot');
  const btnPrev  = hero.querySelector('.hero__nav-btn--prev');
  const btnNext  = hero.querySelector('.hero__nav-btn--next');

  let currentSlide = 0;
  const totalSlides = slides.length;

  // ── Activate a slide ──────────────────────────────────────
  function goToSlide(index) {
    if (index < 0 || index >= totalSlides) return;

    // Remove active from current
    slides[currentSlide].classList.remove('is-active');
    dots[currentSlide]?.classList.remove('is-active');

    currentSlide = index;

    // Move track
    if (currentSlide === 0) {
      track.classList.remove('is-slide-2');
    } else {
      track.classList.add('is-slide-2');
    }

    // Update hero state class for counter line
    hero.classList.toggle('hero--slide-1', currentSlide === 0);
    hero.classList.toggle('hero--slide-2', currentSlide === 1);

    // Activate new slide
    slides[currentSlide].classList.add('is-active');
    dots[currentSlide]?.classList.add('is-active');

    // Update nav button states
    if (btnPrev) btnPrev.disabled = currentSlide === 0;
    if (btnNext) btnNext.disabled = currentSlide === totalSlides - 1;
  }

  // ── Init: activate first slide ───────────────────────────
  goToSlide(0);

  // ── Button controls ───────────────────────────────────────
  if (btnPrev) {
    btnPrev.addEventListener('click', () => goToSlide(currentSlide - 1));
  }
  if (btnNext) {
    btnNext.addEventListener('click', () => goToSlide(currentSlide + 1));
  }

  // ── Dot controls ──────────────────────────────────────────
  dots.forEach((dot, i) => {
    dot.addEventListener('click', () => goToSlide(i));
  });

  // ── Keyboard navigation (when hero is focused) ────────────
  hero.setAttribute('tabindex', '0');
  hero.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
      e.preventDefault();
      goToSlide(currentSlide + 1);
    }
    if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
      e.preventDefault();
      goToSlide(currentSlide - 1);
    }
  });

  // ── Touch/swipe support ───────────────────────────────────
  let touchStartX = 0;
  let touchEndX   = 0;

  hero.addEventListener('touchstart', (e) => {
    touchStartX = e.changedTouches[0].screenX;
  }, { passive: true });

  hero.addEventListener('touchend', (e) => {
    touchEndX = e.changedTouches[0].screenX;
    const diff = touchStartX - touchEndX;
    if (Math.abs(diff) > 50) {
      if (diff > 0) goToSlide(currentSlide + 1); // swipe left → next
      else          goToSlide(currentSlide - 1); // swipe right → prev
    }
  }, { passive: true });

  // ── Countdown timer (Slide 2) ─────────────────────────────
  // Research: Discounts doc — only real deadlines; never reset a countdown
  // End date: last day of current month (real, rolling deadline)
  const countdownEls = {
    days:    document.getElementById('cd-days'),
    hours:   document.getElementById('cd-hours'),
    minutes: document.getElementById('cd-minutes'),
  };

  function getPromoEndDate() {
    // End of current month at 23:59:59
    const now = new Date();
    return new Date(now.getFullYear(), now.getMonth() + 1, 0, 23, 59, 59);
  }

  function pad(n) {
    return String(n).padStart(2, '0');
  }

  function updateCountdown() {
    if (!countdownEls.days) return;

    const now  = new Date();
    const end  = getPromoEndDate();
    const diff = end - now;

    if (diff <= 0) {
      // Promotion ended — hide countdown gracefully
      const cdEl = document.querySelector('.hero__countdown');
      if (cdEl) cdEl.style.display = 'none';
      return;
    }

    const totalSeconds = Math.floor(diff / 1000);
    const days    = Math.floor(totalSeconds / 86400);
    const hours   = Math.floor((totalSeconds % 86400) / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);

    countdownEls.days.textContent    = pad(days);
    countdownEls.hours.textContent   = pad(hours);
    countdownEls.minutes.textContent = pad(minutes);
  }

  updateCountdown();
  setInterval(updateCountdown, 30000); // update every 30s (minutes precision is enough)

})();
