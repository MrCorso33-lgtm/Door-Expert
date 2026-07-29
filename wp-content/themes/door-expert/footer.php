<?php
/**
 * Footer — GLOBALNI elementi (na svakoj stranici): site-footer + mobilni
 * sticky CTA. Zatvara <main> otvoren u header.php.
 *
 * Konvertovano iz prototipa header-demo.html (linije 2034-2165).
 * Napomena: 'pre-footer' NIJE ovde — per-page je (vidi front-page.php).
 *
 * TODO: linkovi korpa/ponuda -> wc_get_cart_url() kad WooCommerce bude aktivan.
 * TODO: linkovi politika/uslovi/kolacici (href="#") -> stvarne WP stranice.
 * TODO: copyright godina hardkodirana (2025) -> date('Y') ili dinamicki.
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<footer class="site-footer" role="contentinfo">
  <div class="site-footer__inner">

    <!-- Brand statement row -->
    <div class="footer-brand-statement">
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="footer-brand__logo" aria-label="Door Expert pocetna">
        <div class="footer-brand__wordmark">Door <span>Expert</span></div>
        <div class="footer-brand__tagline">Uvoznik &amp; distributer · Podgorica</div>
      </a>
      <div class="footer-brand__contact-block">
        <a href="tel:+38269123456" class="footer-brand__phone">+382 69 123 456</a>
        <span class="footer-brand__hours">Pon–Pet 08:00–17:00 · Sub 09:00–14:00</span>
      </div>
    </div>

    <!-- Gold hairline divider -->
    <div class="footer-divider" aria-hidden="true"></div>

    <!-- 4-column grid -->
    <div class="footer-grid">

      <!-- Col 1: Brand desc + social -->
      <div class="footer-col footer-brand">
        <p class="footer-brand__desc">
          Uvoznik i distributer vrata, španske keramike i Bathco umivaonika.
          Roba na stanju — isporuka odmah, bez čekanja.
        </p>
        <div class="footer-social">
          <a href="https://instagram.com/doorexpert.me" target="_blank" rel="noopener" class="footer-social__link" aria-label="Instagram">
            <svg viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>
          </a>
          <a href="https://facebook.com/doorexpert" target="_blank" rel="noopener" class="footer-social__link" aria-label="Facebook">
            <svg viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
          </a>
        </div>
      </div>

      <!-- Col 2: Proizvodi -->
      <div class="footer-col">
        <h3 class="footer-nav__title">Proizvodi</h3>
        <ul class="footer-nav__list">
          <li><a href="<?php echo esc_url( door_expert_cat_url( 'sobna-vrata' ) ); ?>">Sobna vrata</a></li>
          <li class="sub"><a href="<?php echo esc_url( door_expert_cat_url( 'klizna' ) ); ?>">Klizna vrata</a></li>
          <li class="sub"><a href="<?php echo esc_url( door_expert_cat_url( 'staklena-vrata' ) ); ?>">Staklena vrata</a></li>
          <li><a href="<?php echo esc_url( door_expert_cat_url( 'sigurnosna-vrata' ) ); ?>">Sigurnosna vrata</a></li>
          <li class="sub"><a href="<?php echo esc_url( door_expert_cat_url( 'za-stan' ) ); ?>">Za stan</a></li>
          <li class="sub"><a href="<?php echo esc_url( door_expert_cat_url( 'za-kucu' ) ); ?>">Za kuću</a></li>
          <li><a href="<?php echo esc_url( door_expert_cat_url( 'keramicke-plocice' ) ); ?>">Keramičke pločice</a></li>
          <li class="sub"><a href="<?php echo esc_url( door_expert_cat_url( 'podne' ) ); ?>">Podne pločice</a></li>
          <li class="sub"><a href="<?php echo esc_url( door_expert_cat_url( 'zidne' ) ); ?>">Zidne pločice</a></li>
          <li class="sub"><a href="<?php echo esc_url( door_expert_cat_url( 'za-bazen' ) ); ?>">Za bazen</a></li>
          <li><a href="<?php echo esc_url( door_expert_cat_url( 'umivaonici' ) ); ?>">Dekorativni umivaonici</a></li>
          <li class="sub"><a href="<?php echo esc_url( door_expert_cat_url( 'kameni' ) ); ?>">Kameni umivaonici</a></li>
          <li class="sub"><a href="<?php echo esc_url( door_expert_cat_url( 'nadgradni' ) ); ?>">Nadgradni</a></li>
        </ul>
      </div>

      <!-- Col 3: Informacije + Brendovi -->
      <div class="footer-col">
        <h3 class="footer-nav__title">Informacije</h3>
        <ul class="footer-nav__list">
          <li><a href="<?php echo esc_url( home_url( '/o-nama/' ) ); ?>">O nama</a></li>
          <li><a href="<?php echo esc_url( home_url( '/kontakt/' ) ); ?>">Posjetite salon</a></li>
          <li><a href="<?php echo esc_url( home_url( '/akcije/' ) ); ?>">Aktuelne akcije</a></li>
          <li><a href="<?php echo esc_url( home_url( '/montaza/' ) ); ?>">Kako funkcioniše montaža</a></li>
          <li><a href="<?php echo esc_url( home_url( '/b2b/' ) ); ?>">Za investitore i izvođače</a></li>
          <li><a href="<?php echo esc_url( home_url( '/inspiracija/' ) ); ?>">Blog i inspiracija</a></li>
          <li><a href="<?php echo esc_url( home_url( '/brendovi/' ) ); ?>">Naši brendovi</a></li>
          <li><a href="<?php echo esc_url( home_url( '/kontakt/' ) ); ?>">Kontakt</a></li>
        </ul>
        <h3 class="footer-nav__title" style="margin-top:2rem;"><a href="<?php echo esc_url( home_url( '/brendovi/' ) ); ?>" style="color:inherit;text-decoration:none;">Brendovi</a></h3>
        <ul class="footer-nav__list">
          <li><a href="<?php echo esc_url( home_url( '/new-tiles/' ) ); ?>">New Tiles</a></li>
          <li><a href="<?php echo esc_url( home_url( '/tau-ceramica/' ) ); ?>">Tau Ceramica</a></li>
          <li><a href="<?php echo esc_url( home_url( '/arcana-ceramica/' ) ); ?>">Arcana Ceramica</a></li>
          <li><a href="<?php echo esc_url( home_url( '/ribesalbes/' ) ); ?>">Ceramica Ribesalbes</a></li>
          <li><a href="<?php echo esc_url( home_url( '/bathco/' ) ); ?>">Bathco</a></li>
        </ul>
      </div>

      <!-- Col 4: Mapa + radno vrijeme -->
      <div class="footer-col">
        <h3 class="footer-nav__title">Pronađite nas</h3>
        <div class="footer-map">
          <div class="footer-map__placeholder">
            <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            <p>Google Maps<br>Podgorica, Crna Gora</p>
          </div>
        </div>
        <a href="https://maps.google.com" target="_blank" rel="noopener" class="footer-map__link">
          Otvori u Google Maps
          <svg viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
        </a>
      </div>

    </div><!-- /footer-grid -->

    <!-- Installation disclaimer -->
    <div class="footer-disclaimer">
      <p>
        Door Expert je uvoznik i distributer — prodajemo vrata, keramiku i kupatilske elemente.
        Montažu vrše nezavisni majstori koji naplaćuju direktno klijentu.
        <a href="<?php echo esc_url( home_url( '/montaza/' ) ); ?>">Saznajte više o procesu montaže.</a>
      </p>
    </div>

    <div class="footer-bottom">
      <p class="footer-bottom__copy">&copy; 2025 Door Expert. Sva prava zadržana.</p>
      <div class="footer-bottom__links">
        <a href="#">Politika privatnosti</a>
        <a href="#">Uslovi korišćenja</a>
        <a href="#">Kolačići</a>
      </div>
    </div>

  </div><!-- /site-footer__inner -->
</footer>
<!-- /site-footer -->

<!-- Mobile sticky CTA bar -->
<!-- Research: Conversion Strategy — max 2 dugmeta, min 44x44px,
     sticky donja traka na mobilnom povecava konverzije 5-25% -->
<div class="mobile-sticky-cta" role="toolbar" aria-label="Brze akcije">
  <a href="tel:+38269123456" class="mobile-sticky-cta__btn mobile-sticky-cta__btn--call">
    <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.6 1.22h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.8a16 16 0 0 0 6.29 6.29l.95-.95a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
    Pozovite nas
  </a>
  <a href="<?php echo esc_url( home_url( '/korpa/' ) ); ?>" class="mobile-sticky-cta__btn mobile-sticky-cta__btn--quote">
    <svg viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
    Zatrazite ponudu
  </a>
</div>

</main>

<?php wp_footer(); ?>
</body>
</html>
