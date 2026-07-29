/**
 * DOOR EXPERT — Header Interactions
 * ============================================================
 * - Scroll-aware sticky header (adds .scrolled class)
 * - Mega menu: hover on desktop, keyboard accessible
 * - Mobile nav: accordion + backdrop
 * - Search overlay: open/close + focus trap
 * - Cart badge counter (reads from localStorage)
 * ============================================================
 */

(function () {
  'use strict';

  /* ── Scroll-aware header ── */
  const siteHeader = document.querySelector('.site-header');
  let lastScroll = 0;

  function onScroll() {
    const y = window.scrollY;
    if (y > 10) {
      siteHeader.classList.add('scrolled');
    } else {
      siteHeader.classList.remove('scrolled');
    }
    lastScroll = y;
  }

  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll(); // run once on load

  /* ── Mega menu: keep open when moving from nav link to panel ── */
  // (handled entirely via CSS :hover + pointer-events, JS only needed
  //  for keyboard navigation below)

  /* ── Mega menu keyboard navigation ── */
  const navItems = document.querySelectorAll('.header-nav__item');

  navItems.forEach(function (item) {
    const link = item.querySelector('.header-nav__link');
    const menu = item.querySelector('.mega-menu');
    if (!menu) return;

    // Open on Enter/Space
    link.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        const isOpen = menu.style.visibility === 'visible';
        closeAllMegaMenus();
        if (!isOpen) openMegaMenu(menu);
      }
      if (e.key === 'Escape') closeAllMegaMenus();
    });

    // Close on Escape anywhere inside menu
    menu.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        closeAllMegaMenus();
        link.focus();
      }
    });
  });

  function openMegaMenu(menu) {
    menu.style.opacity = '1';
    menu.style.visibility = 'visible';
    menu.style.transform = 'translateY(0)';
    menu.style.pointerEvents = 'auto';
  }

  function closeAllMegaMenus() {
    document.querySelectorAll('.mega-menu').forEach(function (m) {
      m.style.opacity = '';
      m.style.visibility = '';
      m.style.transform = '';
      m.style.pointerEvents = '';
    });
  }

  // Close mega menu when clicking outside
  document.addEventListener('click', function (e) {
    if (!e.target.closest('.header-nav__item')) {
      closeAllMegaMenus();
    }
  });

  /* ── Mobile navigation ── */
  const hamburger   = document.querySelector('.header-hamburger');
  const mobileNav   = document.querySelector('.mobile-nav');
  const mobileBack  = document.querySelector('.mobile-nav__backdrop');
  const mobileClose = document.querySelector('.mobile-nav__close');

  function openMobileNav() {
    mobileNav.classList.add('open');
    mobileBack.classList.add('open');
    hamburger.classList.add('open');
    hamburger.setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';
    // Focus first link
    const firstLink = mobileNav.querySelector('button, a');
    if (firstLink) firstLink.focus();
  }

  function closeMobileNav() {
    mobileNav.classList.remove('open');
    mobileBack.classList.remove('open');
    hamburger.classList.remove('open');
    hamburger.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
    hamburger.focus();
  }

  if (hamburger) hamburger.addEventListener('click', openMobileNav);
  if (mobileClose) mobileClose.addEventListener('click', closeMobileNav);
  if (mobileBack) mobileBack.addEventListener('click', closeMobileNav);

  // Escape key closes mobile nav
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      if (mobileNav && mobileNav.classList.contains('open')) closeMobileNav();
      if (searchOverlay && searchOverlay.classList.contains('open')) closeSearch();
    }
  });

  /* ── Mobile accordion ── */
  const catBtns = document.querySelectorAll('.mobile-nav__cat-btn');

  catBtns.forEach(function (btn) {
    btn.addEventListener('click', function () {
      const cat = btn.closest('.mobile-nav__cat');
      const isOpen = cat.classList.contains('open');

      // Close all
      document.querySelectorAll('.mobile-nav__cat').forEach(function (c) {
        c.classList.remove('open');
        c.querySelector('.mobile-nav__cat-btn').setAttribute('aria-expanded', 'false');
      });

      // Toggle clicked
      if (!isOpen) {
        cat.classList.add('open');
        btn.setAttribute('aria-expanded', 'true');
      }
    });
  });

  /* ── Search overlay ── */
  const searchOverlay = document.querySelector('.search-overlay');
  const searchBtns    = document.querySelectorAll('[data-action="open-search"]');
  const searchClose   = document.querySelector('.search-overlay__close');
  const searchInput   = document.querySelector('.search-overlay__input');

  function openSearch() {
    searchOverlay.classList.add('open');
    document.body.style.overflow = 'hidden';
    setTimeout(function () {
      if (searchInput) searchInput.focus();
    }, 200);
  }

  function closeSearch() {
    searchOverlay.classList.remove('open');
    document.body.style.overflow = '';
  }

  searchBtns.forEach(function (btn) {
    btn.addEventListener('click', openSearch);
  });

  if (searchClose) searchClose.addEventListener('click', closeSearch);

  // Close when clicking backdrop
  if (searchOverlay) {
    searchOverlay.addEventListener('click', function (e) {
      if (e.target === searchOverlay) closeSearch();
    });
  }

  // Search hint chips
  document.querySelectorAll('.search-overlay__hint').forEach(function (chip) {
    chip.addEventListener('click', function () {
      if (searchInput) {
        searchInput.value = chip.textContent.trim();
        searchInput.focus();
      }
    });
  });

  /* ── Cart badge: read from localStorage ── */
  function updateCartBadge() {
    const badge = document.querySelector('.header-btn__badge');
    if (!badge) return;
    try {
      const cart = JSON.parse(localStorage.getItem('de_cart') || '[]');
      const count = cart.reduce(function (sum, item) {
        return sum + (item.qty || 1);
      }, 0);
      badge.textContent = count > 99 ? '99+' : count;
      badge.style.display = count > 0 ? 'flex' : 'none';
    } catch (e) {
      badge.style.display = 'none';
    }
  }

  updateCartBadge();
  window.addEventListener('storage', updateCartBadge);
  document.addEventListener('de:cart:updated', updateCartBadge);

})();
