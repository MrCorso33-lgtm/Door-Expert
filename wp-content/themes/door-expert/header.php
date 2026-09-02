<?php
/**
 * Header – <head>, site-header (mega meni) i mobilna navigacija.
 *
 * Konvertovano iz prototipa header-demo.html (linije 106-731).
 * Inline <style> iz prototipa: .demo-* scaffolding je izostavljen (0 upotreba
 * u body-ju), a stvarni bazni body/html stilovi su izvučeni u assets/css/base.css.
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />

	<?php // Fontovi se enqueue-uju u functions.php; preconnect ostaje ovde. ?>
	<link rel="preconnect" href="https://fonts.googleapis.com" />
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php
/*
 * TODO (pre produkcije):
 * 1. Slike u mega meniju su Unsplash placeholderi (18 komada) – zameniti
 *    stvarnim fotografijama proizvoda iz WP medija.
 * 2. Navigacija je hardkodirana prema prototipu (verna konverzija). Ako
 *    klijent treba sam da menja meni, prebaciti na wp_nav_menu() + custom
 *    walker ili ACF-driven mega meni. Vidi CLAUDE.md sekcija 2.
 */
?>
<header class="site-header" role="banner">

  <!-- Top utility bar -->
  <div class="header-top" role="complementary" aria-label="Korisne informacije">
    <div class="header-top__inner">
      <div class="header-top__left">
        <span class="header-top__promo">
          <span class="header-top__promo-dot" aria-hidden="true"></span>
          Vrata odmah dostupna – isporuka bez cekanja
        </span>
        <span class="header-top__hours">Pon–Pet 10:00–18:00 · Sub 10:00–14:00</span>
      </div>
      <div class="header-top__right">
        <a href="<?php echo esc_url( home_url( '/o-nama/' ) ); ?>" class="header-top__link">O nama</a>
        <a href="<?php echo esc_url( home_url( '/kontakt/' ) ); ?>" class="header-top__link">Posjetite salon</a>
        <a href="<?php echo esc_url( home_url( '/b2b/' ) ); ?>" class="header-top__link">Za investitore</a>
      </div>
    </div>
  </div>

  <!-- Main header bar -->
  <div class="header-main">
    <div class="header-main__inner">

      <!-- Logo -->
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="header-logo" aria-label="Door Expert – pocetna stranica">
        <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo.webp' ); ?>" alt="Door Expert" height="44" />
      </a>

      <!-- Primary navigation -->
      <nav class="header-nav" role="navigation" aria-label="Glavna navigacija">
        <ul class="header-nav__list" role="list">

          <!-- 1. Sobna vrata -->
          <li class="header-nav__item">
            <a href="<?php echo esc_url( door_expert_cat_url( 'sobna-vrata' ) ); ?>" class="header-nav__link" aria-haspopup="true" aria-expanded="false">
              Sobna vrata
              <svg class="chevron" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <polyline points="6 9 12 15 18 9"/>
              </svg>
            </a>

            <!-- Mega menu: Sobna vrata -->
            <div class="mega-menu mega-menu--sobna" role="region" aria-label="Sobna vrata podmeni">
              <div class="mega-menu__inner">

                <!-- Hero card -->
                <a href="<?php echo esc_url( door_expert_cat_url( 'sobna-vrata' ) ); ?>" class="mega-menu__hero">
                  <img
                    class="mega-menu__hero-img"
                    src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=600&q=80"
                    alt="Sobna vrata – enterijer"
                    loading="lazy"
                  />
                  <div class="mega-menu__hero-overlay" aria-hidden="true"></div>
                  <div class="mega-menu__hero-content">
                    <span class="mega-menu__hero-label">Kategorija</span>
                    <h3 class="mega-menu__hero-title">Sobna vrata</h3>
                    <span class="mega-menu__hero-cta">
                      Pogledaj sve modele
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </span>
                  </div>
                </a>

                <!-- Subcategories -->
                <div class="mega-menu__subcats">
                  <span class="mega-menu__subcats-label">Potkategorije</span>
                  <div class="mega-menu__grid">

                    <a href="<?php echo esc_url( door_expert_cat_url( 'klizna' ) ); ?>" class="mega-menu__subcat-link">
                      <img
                        class="mega-menu__subcat-thumb"
                        src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=120&q=75"
                        alt="Klizna vrata"
                        loading="lazy"
                      />
                      <div class="mega-menu__subcat-info">
                        <span class="mega-menu__subcat-name">Klizna vrata</span>
                        <span class="mega-menu__subcat-desc">Sina, klizaci i maska ukljuceni</span>
                      </div>
                    </a>

                    <a href="<?php echo esc_url( door_expert_cat_url( 'staklena-vrata' ) ); ?>" class="mega-menu__subcat-link">
                      <img
                        class="mega-menu__subcat-thumb"
                        src="https://images.unsplash.com/photo-1600566753086-00f18fb6b3ea?w=120&q=75"
                        alt="Staklena vrata"
                        loading="lazy"
                      />
                      <div class="mega-menu__subcat-info">
                        <span class="mega-menu__subcat-name">Staklena vrata</span>
                        <span class="mega-menu__subcat-desc">Elegantni dizajn, vise svjetlosti</span>
                      </div>
                    </a>

                  </div>
                </div>

                <!-- Footer strip -->
                <div class="mega-menu__footer">
                  <a href="<?php echo esc_url( door_expert_cat_url( 'sobna-vrata' ) ); ?>" class="mega-menu__view-all">
                    Sva sobna vrata
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                  </a>
                  <span class="mega-menu__trust">
                    <span class="mega-menu__trust-dot" aria-hidden="true"></span>
                    Odmah dostupno – bez cekanja
                  </span>
                </div>

              </div>
            </div>
          </li>

          <!-- 2. Sigurnosna vrata -->
          <li class="header-nav__item">
            <a href="<?php echo esc_url( door_expert_cat_url( 'sigurnosna-vrata' ) ); ?>" class="header-nav__link" aria-haspopup="true" aria-expanded="false">
              Sigurnosna vrata
              <svg class="chevron" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <polyline points="6 9 12 15 18 9"/>
              </svg>
            </a>

            <!-- Mega menu: Sigurnosna vrata -->
            <div class="mega-menu mega-menu--sigurnosna" role="region" aria-label="Sigurnosna vrata podmeni">
              <div class="mega-menu__inner">

                <a href="<?php echo esc_url( door_expert_cat_url( 'sigurnosna-vrata' ) ); ?>" class="mega-menu__hero">
                  <img
                    class="mega-menu__hero-img"
                    src="https://images.unsplash.com/photo-1600585154526-990dced4db0d?w=600&q=80"
                    alt="Sigurnosna vrata"
                    loading="lazy"
                  />
                  <div class="mega-menu__hero-overlay" aria-hidden="true"></div>
                  <div class="mega-menu__hero-content">
                    <span class="mega-menu__hero-label">Kategorija</span>
                    <h3 class="mega-menu__hero-title">Sigurnosna (blind) vrata</h3>
                    <span class="mega-menu__hero-cta">
                      Pogledaj sve modele
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </span>
                  </div>
                </a>

                <div class="mega-menu__subcats">
                  <span class="mega-menu__subcats-label">Potkategorije</span>
                  <div class="mega-menu__grid">

                    <a href="<?php echo esc_url( door_expert_cat_url( 'za-stan' ) ); ?>" class="mega-menu__subcat-link">
                      <img
                        class="mega-menu__subcat-thumb"
                        src="https://images.unsplash.com/photo-1560185893-a55cbc8c57e8?w=120&q=75"
                        alt="Sigurnosna vrata za stan"
                        loading="lazy"
                      />
                      <div class="mega-menu__subcat-info">
                        <span class="mega-menu__subcat-name">Za stan</span>
                        <span class="mega-menu__subcat-desc">Standard, Premium, Deluxe klasa</span>
                      </div>
                    </a>

                    <a href="<?php echo esc_url( door_expert_cat_url( 'za-kucu' ) ); ?>" class="mega-menu__subcat-link">
                      <img
                        class="mega-menu__subcat-thumb"
                        src="https://images.unsplash.com/photo-1600047509807-ba8f99d2cdde?w=120&q=75"
                        alt="Sigurnosna vrata za kucu"
                        loading="lazy"
                      />
                      <div class="mega-menu__subcat-info">
                        <span class="mega-menu__subcat-name">Za kucu</span>
                        <span class="mega-menu__subcat-desc">Deluxe klasa, antracit i wenge</span>
                      </div>
                    </a>

                  </div>
                </div>

                <div class="mega-menu__footer">
                  <a href="<?php echo esc_url( door_expert_cat_url( 'sigurnosna-vrata' ) ); ?>" class="mega-menu__view-all">
                    Sva sigurnosna vrata
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                  </a>
                  <span class="mega-menu__trust">
                    <span class="mega-menu__trust-dot" aria-hidden="true"></span>
                    Cijene od 649 EUR
                  </span>
                </div>

              </div>
            </div>
          </li>

          <!-- 3. Keramicke plocice -->
          <li class="header-nav__item">
            <a href="<?php echo esc_url( door_expert_cat_url( 'keramicke-plocice' ) ); ?>" class="header-nav__link" aria-haspopup="true" aria-expanded="false">
              Keramicke plocice
              <svg class="chevron" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <polyline points="6 9 12 15 18 9"/>
              </svg>
            </a>

            <!-- Mega menu: Keramicke plocice -->
            <div class="mega-menu mega-menu--keramika" role="region" aria-label="Keramicke plocice podmeni">
              <div class="mega-menu__inner">

                <a href="<?php echo esc_url( door_expert_cat_url( 'keramicke-plocice' ) ); ?>" class="mega-menu__hero">
                  <img
                    class="mega-menu__hero-img"
                    src="https://images.unsplash.com/photo-1584622650111-993a426fbf0a?w=600&q=80"
                    alt="Spanske keramicke plocice"
                    loading="lazy"
                  />
                  <div class="mega-menu__hero-overlay" aria-hidden="true"></div>
                  <div class="mega-menu__hero-content">
                    <span class="mega-menu__hero-label">Spanska keramika</span>
                    <h3 class="mega-menu__hero-title">Keramicke plocice</h3>
                    <span class="mega-menu__hero-cta">
                      Pogledaj sve kolekcije
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </span>
                  </div>
                </a>

                <div class="mega-menu__subcats">
                  <span class="mega-menu__subcats-label">Potkategorije</span>
                  <div class="mega-menu__grid">

                    <a href="<?php echo esc_url( door_expert_cat_url( 'podne' ) ); ?>" class="mega-menu__subcat-link">
                      <img class="mega-menu__subcat-thumb" src="https://images.unsplash.com/photo-1615971677499-5467cbab01c0?w=120&q=75" alt="Podne plocice" loading="lazy"/>
                      <div class="mega-menu__subcat-info">
                        <span class="mega-menu__subcat-name">Podne plocice</span>
                        <span class="mega-menu__subcat-desc">Granitna keramika, veliki formati</span>
                      </div>
                    </a>

                    <a href="<?php echo esc_url( door_expert_cat_url( 'zidne' ) ); ?>" class="mega-menu__subcat-link">
                      <img class="mega-menu__subcat-thumb" src="https://images.unsplash.com/photo-1600566752355-35792bedcfea?w=120&q=75" alt="Zidne plocice" loading="lazy"/>
                      <div class="mega-menu__subcat-info">
                        <span class="mega-menu__subcat-name">Zidne plocice</span>
                        <span class="mega-menu__subcat-desc">Dekorativni modeli za svaki prostor</span>
                      </div>
                    </a>

                    <a href="<?php echo esc_url( door_expert_cat_url( 'za-kupatilo' ) ); ?>" class="mega-menu__subcat-link">
                      <img class="mega-menu__subcat-thumb" src="https://images.unsplash.com/photo-1552321554-5fefe8c9ef14?w=120&q=75" alt="Plocice za kupatilo" loading="lazy"/>
                      <div class="mega-menu__subcat-info">
                        <span class="mega-menu__subcat-name">Plocice za kupatilo</span>
                        <span class="mega-menu__subcat-desc">Podne i zidne, mozaici</span>
                      </div>
                    </a>

                    <a href="<?php echo esc_url( door_expert_cat_url( 'za-kuhinju' ) ); ?>" class="mega-menu__subcat-link">
                      <img class="mega-menu__subcat-thumb" src="https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=120&q=75" alt="Plocice za kuhinju" loading="lazy"/>
                      <div class="mega-menu__subcat-info">
                        <span class="mega-menu__subcat-name">Plocice za kuhinju</span>
                        <span class="mega-menu__subcat-desc">Otporne i lake za odrzavanje</span>
                      </div>
                    </a>

                    <a href="<?php echo esc_url( door_expert_cat_url( 'spoljne' ) ); ?>" class="mega-menu__subcat-link">
                      <img class="mega-menu__subcat-thumb" src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=120&q=75" alt="Spoljne plocice" loading="lazy"/>
                      <div class="mega-menu__subcat-info">
                        <span class="mega-menu__subcat-name">Spoljne plocice</span>
                        <span class="mega-menu__subcat-desc">Terase, dvorista, protivklizne</span>
                      </div>
                    </a>

                    <a href="<?php echo esc_url( door_expert_cat_url( 'za-bazen' ) ); ?>" class="mega-menu__subcat-link">
                      <img class="mega-menu__subcat-thumb" src="https://images.unsplash.com/photo-1571902943202-507ec2618e8f?w=120&q=75" alt="Plocice za bazen" loading="lazy"/>
                      <div class="mega-menu__subcat-info">
                        <span class="mega-menu__subcat-name">Plocice za bazen</span>
                        <span class="mega-menu__subcat-desc">Mozaik program, otpornost na hemikalije</span>
                      </div>
                    </a>

                    <a href="<?php echo esc_url( door_expert_cat_url( 'gaziste-za-stepenice' ) ); ?>" class="mega-menu__subcat-link">
                      <img class="mega-menu__subcat-thumb" src="https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=120&q=75" alt="Gaziste za stepenice" loading="lazy"/>
                      <div class="mega-menu__subcat-info">
                        <span class="mega-menu__subcat-name">Gaziste za stepenice</span>
                        <span class="mega-menu__subcat-desc">Granitna gazista i profili</span>
                      </div>
                    </a>

                  </div>
                </div>

                <div class="mega-menu__footer">
                  <a href="<?php echo esc_url( door_expert_cat_url( 'keramicke-plocice' ) ); ?>" class="mega-menu__view-all">
                    Sve keramicke plocice
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                  </a>
                  <span class="mega-menu__trust">
                    <span class="mega-menu__trust-dot" aria-hidden="true"></span>
                    Spanski brendovi: New Tiles, Tau, Arcana, Ribesalbes
                  </span>
                </div>

              </div>
            </div>
          </li>

          <!-- 4. Dekorativni umivaonici -->
          <li class="header-nav__item">
            <a href="<?php echo esc_url( door_expert_cat_url( 'umivaonici' ) ); ?>" class="header-nav__link" aria-haspopup="true" aria-expanded="false">
              Umivaonici
              <svg class="chevron" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <polyline points="6 9 12 15 18 9"/>
              </svg>
            </a>

            <!-- Mega menu: Umivaonici -->
            <div class="mega-menu mega-menu--umivaonici" role="region" aria-label="Umivaonici podmeni">
              <div class="mega-menu__inner">

                <a href="<?php echo esc_url( door_expert_cat_url( 'umivaonici' ) ); ?>" class="mega-menu__hero">
                  <img
                    class="mega-menu__hero-img"
                    src="https://images.unsplash.com/photo-1552321554-5fefe8c9ef14?w=600&q=80"
                    alt="Dekorativni umivaonici Bathco"
                    loading="lazy"
                  />
                  <div class="mega-menu__hero-overlay" aria-hidden="true"></div>
                  <div class="mega-menu__hero-content">
                    <span class="mega-menu__hero-label">Brend Bathco – Spanija</span>
                    <h3 class="mega-menu__hero-title">Dekorativni umivaonici</h3>
                    <span class="mega-menu__hero-cta">
                      Pogledaj sve modele
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </span>
                  </div>
                </a>

                <div class="mega-menu__subcats">
                  <span class="mega-menu__subcats-label">Potkategorije</span>
                  <div class="mega-menu__grid">

                    <a href="<?php echo esc_url( door_expert_cat_url( 'kameni' ) ); ?>" class="mega-menu__subcat-link">
                      <img class="mega-menu__subcat-thumb" src="https://images.unsplash.com/photo-1584622650111-993a426fbf0a?w=120&q=75" alt="Kameni umivaonici" loading="lazy"/>
                      <div class="mega-menu__subcat-info">
                        <span class="mega-menu__subcat-name">Kameni umivaonici</span>
                        <span class="mega-menu__subcat-desc">Prirodni kamen, rucna izrada</span>
                      </div>
                    </a>

                    <a href="<?php echo esc_url( door_expert_cat_url( 'samostojeci' ) ); ?>" class="mega-menu__subcat-link">
                      <img class="mega-menu__subcat-thumb" src="https://images.unsplash.com/photo-1552321554-5fefe8c9ef14?w=120&q=75" alt="Samostojeci umivaonici" loading="lazy"/>
                      <div class="mega-menu__subcat-info">
                        <span class="mega-menu__subcat-name">Samostojeci umivaonici</span>
                        <span class="mega-menu__subcat-desc">Luksuzni, unikatni dizajn</span>
                      </div>
                    </a>

                    <a href="<?php echo esc_url( door_expert_cat_url( 'nadgradni' ) ); ?>" class="mega-menu__subcat-link">
                      <img class="mega-menu__subcat-thumb" src="https://images.unsplash.com/photo-1552321554-5fefe8c9ef14?w=120&q=75" alt="Nadgradni umivaonici" loading="lazy"/>
                      <div class="mega-menu__subcat-info">
                        <span class="mega-menu__subcat-name">Nadgradni umivaonici</span>
                        <span class="mega-menu__subcat-desc">Porcelanski i kameni modeli</span>
                      </div>
                    </a>

                  </div>
                </div>

                <div class="mega-menu__footer">
                  <a href="<?php echo esc_url( door_expert_cat_url( 'umivaonici' ) ); ?>" class="mega-menu__view-all">
                    Svi umivaonici
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                  </a>
                  <span class="mega-menu__trust">
                    <span class="mega-menu__trust-dot" aria-hidden="true"></span>
                    Iskljucivo Bathco – Spanija
                  </span>
                </div>

              </div>
            </div>
          </li>

          <!-- 5. Akcije (no mega menu, gold highlight) -->
          <li class="header-nav__item">
            <a href="<?php echo esc_url( home_url( '/akcije/' ) ); ?>" class="header-nav__link header-nav__link--akcije">
              Akcije
            </a>
          </li>
          <!-- 6. Brendovi -->
          <li class="header-nav__item">
            <a href="<?php echo esc_url( home_url( '/brendovi/' ) ); ?>" class="header-nav__link">
              Brendovi
            </a>
          </li>

        </ul>
      </nav>

      <!-- Actions -->
      <div class="header-actions" role="group" aria-label="Akcije">

        <!-- Search -->
        <button
          class="header-btn"
          data-action="open-search"
          aria-label="Pretraga proizvoda"
          title="Pretraga"
        >
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
        </button>

        <!-- Cart -->
        <a href="<?php echo esc_url( door_expert_cart_url() ); ?>" class="header-btn" aria-label="Korpa za ponudu">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/>
          </svg>
          <span class="header-btn__badge" aria-label="0 stavki u korpi" style="display:none;">0</span>
        </a>

        <!-- Phone CTA (desktop) -->
        <a href="tel:+38269234888" class="header-phone" aria-label="Pozovite nas: +382 69 234 888">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.67A2 2 0 012 .18h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/>
          </svg>
          +382 69 234 888
        </a>

        <!-- Hamburger (mobile) -->
        <button
          class="header-hamburger"
          aria-label="Otvori meni"
          aria-expanded="false"
          aria-controls="mobile-nav"
        >
          <span class="header-hamburger__line" aria-hidden="true"></span>
          <span class="header-hamburger__line" aria-hidden="true"></span>
          <span class="header-hamburger__line" aria-hidden="true"></span>
        </button>

      </div>
    </div>
  </div>

</header>

<!-- ════════════════════════════════════════════════
     SEARCH OVERLAY
════════════════════════════════════════════════ -->
<div class="search-overlay" role="dialog" aria-modal="true" aria-label="Pretraga">
  <div class="search-overlay__box">
    <div class="search-overlay__inner">
      <span class="search-overlay__label">Pretrazite proizvode</span>
      <form class="search-overlay__form" action="/pretraga/" method="get" role="search">
        <input
          class="search-overlay__input"
          type="search"
          name="q"
          placeholder="Npr. klizna vrata, bijele plocice, 60x120, kameni lavabo..."
          autocomplete="off"
          spellcheck="false"
          aria-label="Unesite pojam za pretragu"
        />
        <button class="search-overlay__submit" type="submit" aria-label="Pretrazi">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
        </button>
      </form>
      <div class="search-overlay__hints" aria-label="Popularni pojmovi">
        <span class="search-overlay__hint-label">Popularno:</span>
        <span class="search-overlay__hint" role="button" tabindex="0">klizna vrata</span>
        <span class="search-overlay__hint" role="button" tabindex="0">bijela vrata</span>
        <span class="search-overlay__hint" role="button" tabindex="0">plocice za kupatilo</span>
        <span class="search-overlay__hint" role="button" tabindex="0">kameni lavabo</span>
        <span class="search-overlay__hint" role="button" tabindex="0">sigurnosna vrata za stan</span>
        <span class="search-overlay__hint" role="button" tabindex="0">60x120</span>
      </div>
    </div>
    <button class="search-overlay__close" aria-label="Zatvori pretragu">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
      </svg>
    </button>
  </div>
</div>

<!-- ════════════════════════════════════════════════
     MOBILE NAVIGATION
════════════════════════════════════════════════ -->
<div class="mobile-nav__backdrop" aria-hidden="true"></div>

<nav class="mobile-nav" id="mobile-nav" role="navigation" aria-label="Mobilna navigacija">
  <div class="mobile-nav__header">
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="mobile-nav__logo" aria-label="Door Expert – pocetna">
      <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo.webp' ); ?>" alt="Door Expert" height="36" />
    </a>
    <button class="mobile-nav__close" aria-label="Zatvori meni">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
      </svg>
    </button>
  </div>

  <div class="mobile-nav__body">

    <!-- Sobna vrata -->
    <div class="mobile-nav__cat">
      <button class="mobile-nav__cat-btn" aria-expanded="false">
        Sobna vrata
        <svg class="chevron" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <polyline points="6 9 12 15 18 9"/>
        </svg>
      </button>
      <div class="mobile-nav__subcats">
        <a href="<?php echo esc_url( door_expert_cat_url( 'sobna-vrata' ) ); ?>" class="mobile-nav__subcat-link">Sva sobna vrata</a>
        <a href="<?php echo esc_url( door_expert_cat_url( 'klizna' ) ); ?>" class="mobile-nav__subcat-link">Klizna vrata</a>
        <a href="<?php echo esc_url( door_expert_cat_url( 'staklena-vrata' ) ); ?>" class="mobile-nav__subcat-link">Staklena vrata</a>
      </div>
    </div>

    <!-- Sigurnosna vrata -->
    <div class="mobile-nav__cat">
      <button class="mobile-nav__cat-btn" aria-expanded="false">
        Sigurnosna vrata
        <svg class="chevron" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <polyline points="6 9 12 15 18 9"/>
        </svg>
      </button>
      <div class="mobile-nav__subcats">
        <a href="<?php echo esc_url( door_expert_cat_url( 'sigurnosna-vrata' ) ); ?>" class="mobile-nav__subcat-link">Sva sigurnosna vrata</a>
        <a href="<?php echo esc_url( door_expert_cat_url( 'za-stan' ) ); ?>" class="mobile-nav__subcat-link">Za stan</a>
        <a href="<?php echo esc_url( door_expert_cat_url( 'za-kucu' ) ); ?>" class="mobile-nav__subcat-link">Za kucu</a>
      </div>
    </div>

    <!-- Keramicke plocice -->
    <div class="mobile-nav__cat">
      <button class="mobile-nav__cat-btn" aria-expanded="false">
        Keramicke plocice
        <svg class="chevron" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <polyline points="6 9 12 15 18 9"/>
        </svg>
      </button>
      <div class="mobile-nav__subcats">
        <a href="<?php echo esc_url( door_expert_cat_url( 'keramicke-plocice' ) ); ?>" class="mobile-nav__subcat-link">Sve keramicke plocice</a>
        <a href="<?php echo esc_url( door_expert_cat_url( 'podne' ) ); ?>" class="mobile-nav__subcat-link">Podne plocice</a>
        <a href="<?php echo esc_url( door_expert_cat_url( 'zidne' ) ); ?>" class="mobile-nav__subcat-link">Zidne plocice</a>
        <a href="<?php echo esc_url( door_expert_cat_url( 'za-kupatilo' ) ); ?>" class="mobile-nav__subcat-link">Plocice za kupatilo</a>
        <a href="<?php echo esc_url( door_expert_cat_url( 'za-kuhinju' ) ); ?>" class="mobile-nav__subcat-link">Plocice za kuhinju</a>
        <a href="<?php echo esc_url( door_expert_cat_url( 'spoljne' ) ); ?>" class="mobile-nav__subcat-link">Spoljne plocice</a>
        <a href="<?php echo esc_url( door_expert_cat_url( 'za-bazen' ) ); ?>" class="mobile-nav__subcat-link">Plocice za bazen</a>
        <a href="<?php echo esc_url( door_expert_cat_url( 'gaziste-za-stepenice' ) ); ?>" class="mobile-nav__subcat-link">Gaziste za stepenice</a>
      </div>
    </div>

    <!-- Umivaonici -->
    <div class="mobile-nav__cat">
      <button class="mobile-nav__cat-btn" aria-expanded="false">
        Umivaonici
        <svg class="chevron" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <polyline points="6 9 12 15 18 9"/>
        </svg>
      </button>
      <div class="mobile-nav__subcats">
        <a href="<?php echo esc_url( door_expert_cat_url( 'umivaonici' ) ); ?>" class="mobile-nav__subcat-link">Svi umivaonici</a>
        <a href="<?php echo esc_url( door_expert_cat_url( 'kameni' ) ); ?>" class="mobile-nav__subcat-link">Kameni umivaonici</a>
        <a href="<?php echo esc_url( door_expert_cat_url( 'samostojeci' ) ); ?>" class="mobile-nav__subcat-link">Samostojeci umivaonici</a>
        <a href="<?php echo esc_url( door_expert_cat_url( 'nadgradni' ) ); ?>" class="mobile-nav__subcat-link">Nadgradni umivaonici</a>
      </div>
    </div>

    <!-- Utility links -->
    <div class="mobile-nav__utils">
      <a href="<?php echo esc_url( home_url( '/akcije/' ) ); ?>" class="mobile-nav__util-link" style="color: var(--color-jantar); font-weight: 700;">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
        Akcije i popusti
      </a>
      <a href="<?php echo esc_url( home_url( '/o-nama/' ) ); ?>" class="mobile-nav__util-link">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        O nama
      </a>
      <a href="<?php echo esc_url( home_url( '/kontakt/' ) ); ?>" class="mobile-nav__util-link">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
        Posjetite salon
      </a>
      <a href="<?php echo esc_url( home_url( '/b2b/' ) ); ?>" class="mobile-nav__util-link">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
        Za investitore i izvodjace
      </a>
      <a href="<?php echo esc_url( door_expert_cart_url() ); ?>" class="mobile-nav__util-link">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
        Korpa za ponudu
      </a>
    </div>

  </div>

  <div class="mobile-nav__footer">
    <a href="tel:+38269234888" class="mobile-nav__phone">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.67A2 2 0 012 .18h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/>
      </svg>
      Pozovite: +382 69 234 888
    </a>
  </div>
</nav>

<!-- Sticky mobile phone bar -->
<div class="mobile-phone-bar" aria-hidden="true">
  <a href="tel:+38269234888" class="mobile-phone-bar__link">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
      <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.67A2 2 0 012 .18h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/>
    </svg>
    +382 69 234 888 – Pozovite odmah
  </a>
</div>

<!-- ════════════════════════════════════════════════
     HERO SLIDER
     Slide 1: Inspirational brand statement + category routing
     Slide 2: Aktuelna ponuda + calm countdown
     Research: UX Research for Navigation (manual slider, no auto-rotate)
              Visual research (layered image, warm minimalism)
              Conversion Strategy (CTA copy, phone, stock messaging)
              Discounts doc (real deadline, calm countdown)
════════════════════════════════════════════════ -->

<main>
