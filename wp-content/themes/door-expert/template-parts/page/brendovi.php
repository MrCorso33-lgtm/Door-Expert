<?php
/**
 * Stranica: Brendovi ("Naši brendovi") – verna konverzija prototipa brendovi.html (<main>: 793-1008).
 *
 * Sekcije: hero → 4 keramička brenda (TAU, Arcana, New Tiles, Ribesalbes) → Bathco (istaknut) →
 *          "Zašto španska keramika" (origin) → CTA.
 * CSS: assets/css/brendovi.css (izvučen iz inline <style>, mobile-first, tokeni mapirani).
 * Header/footer globalni.
 *
 * Popravke: hardkod .html linkovi → home_url(); telefon → 234 888; em-dash → en-dash;
 * inline stilovi → modifier klase (.brands-section--tight, .brand-card--wide).
 * Produkcija: brend stranice mogu postati `brand` taksonomija (linkovi ostaju dinamični).
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!-- BRANDS HERO -->
<section class="brands-hero">
  <div class="brands-hero__content">
    <div class="brands-hero__badge">
      <span class="brands-hero__flag">🇪🇸</span> Direktan uvoz iz Španije
    </div>
    <h1 class="brands-hero__title">Naši brendovi</h1>
    <p class="brands-hero__subtitle">
      Pažljivo odabrani španski proizvođači keramike i kupatilske opreme.
      Svaki brend donosi decenije iskustva, inovacije i prepoznatljiv dizajn.
    </p>
  </div>
</section>

<!-- CERAMIC BRANDS -->
<section class="brands-section">
  <div class="brands-section__header">
    <p class="brands-section__label">Keramičke pločice</p>
    <h2 class="brands-section__title">Četiri španska proizvođača keramike</h2>
  </div>

  <div class="brands-grid">
    <!-- TAU Ceramica -->
    <a href="<?php echo esc_url( home_url( '/tau-ceramica/' ) ); ?>" class="brand-card">
      <div class="brand-card__image">
        <img src="https://images.unsplash.com/photo-1615873968403-89e068629265?w=700&q=80" alt="Tau Ceramica pločice u modernom enterijeru" loading="lazy" />
        <span class="brand-card__flag">🇪🇸 Castellón, Španija</span>
      </div>
      <div class="brand-card__body">
        <span class="brand-card__category">Keramičke pločice</span>
        <h3 class="brand-card__name">TAU Cerámica</h3>
        <p class="brand-card__desc">
          Premium porcelanske pločice za podove i zidove. Mramor, beton, drvo i kamen efekti u velikim formatima. Kvalitet, inovacija i stil od 1967.
        </p>
        <div class="brand-card__meta">
          <span class="brand-card__meta-item">⏱ Od 1967.</span>
          <span class="brand-card__meta-item">🏭 Onda, Castellón</span>
          <span class="brand-card__meta-item">🌍 Globalni izvoz</span>
        </div>
        <span class="brand-card__cta">
          Pogledaj kolekcije
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </span>
      </div>
    </a>

    <!-- Arcana Ceramica -->
    <a href="<?php echo esc_url( home_url( '/arcana-ceramica/' ) ); ?>" class="brand-card">
      <div class="brand-card__image">
        <img src="https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=700&q=80" alt="Arcana Ceramica porcelanski pod" loading="lazy" />
        <span class="brand-card__flag">🇪🇸 L'Alcora, Španija</span>
      </div>
      <div class="brand-card__body">
        <span class="brand-card__category">Keramičke pločice</span>
        <h3 class="brand-card__name">Arcana Cerámica</h3>
        <p class="brand-card__desc">
          Visokokvalitetni porcelanski gres inspirisan prirodnim materijalima i arhitekturom. Kolekcije koje spajaju eleganciju i avangardni dizajn od 1997.
        </p>
        <div class="brand-card__meta">
          <span class="brand-card__meta-item">⏱ Od 1997.</span>
          <span class="brand-card__meta-item">🏭 L'Alcora, Castellón</span>
          <span class="brand-card__meta-item">🌍 5 kontinenata</span>
        </div>
        <span class="brand-card__cta">
          Pogledaj kolekcije
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </span>
      </div>
    </a>

    <!-- New Tiles -->
    <a href="<?php echo esc_url( home_url( '/new-tiles/' ) ); ?>" class="brand-card">
      <div class="brand-card__image">
        <img src="https://images.unsplash.com/photo-1615971677499-5467cbab01c0?w=700&q=80" alt="New Tiles savremene keramičke pločice" loading="lazy" />
        <span class="brand-card__flag">🇪🇸 L'Alcora, Španija</span>
      </div>
      <div class="brand-card__body">
        <span class="brand-card__category">Keramičke pločice</span>
        <h3 class="brand-card__name">New Tiles</h3>
        <p class="brand-card__desc">
          Savremeni dizajn i široka paleta formata za svaki prostor. Mlad brend sa konstantnim ulaganjem u kvalitet, dizajn i servis od 2014.
        </p>
        <div class="brand-card__meta">
          <span class="brand-card__meta-item">⏱ Od 2014.</span>
          <span class="brand-card__meta-item">🏭 L'Alcora, Castellón</span>
          <span class="brand-card__meta-item">🌱 ISO standardi</span>
        </div>
        <span class="brand-card__cta">
          Pogledaj kolekcije
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </span>
      </div>
    </a>

    <!-- Ceramica Ribesalbes -->
    <a href="<?php echo esc_url( home_url( '/ribesalbes/' ) ); ?>" class="brand-card">
      <div class="brand-card__image">
        <img src="https://images.unsplash.com/photo-1584622650111-993a426fbf0a?w=700&q=80" alt="Ceramica Ribesalbes artizanske pločice" loading="lazy" />
        <span class="brand-card__flag">🇪🇸 Onda, Španija</span>
      </div>
      <div class="brand-card__body">
        <span class="brand-card__category">Keramičke pločice</span>
        <h3 class="brand-card__name">Cerámica Ribesalbes</h3>
        <p class="brand-card__desc">
          Artizanska tradicija i savremeni dizajn iz Valencije. Lider u proizvodnji metro pločica, malih formata i hidrauličnih reprodukcija od 1986.
        </p>
        <div class="brand-card__meta">
          <span class="brand-card__meta-item">⏱ Od 1986.</span>
          <span class="brand-card__meta-item">🏭 Onda, Castellón</span>
          <span class="brand-card__meta-item">🎨 Artizanski rad</span>
        </div>
        <span class="brand-card__cta">
          Pogledaj kolekcije
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </span>
      </div>
    </a>
  </div>
</section>

<!-- BATHCO (istaknut) -->
<section class="brands-section brands-section--tight">
  <div class="brands-section__header">
    <p class="brands-section__label">Dekorativni umivaonici</p>
    <h2 class="brands-section__title">Bathco – španski umivaonici sa dušom</h2>
  </div>

  <div class="brands-grid">
    <a href="<?php echo esc_url( home_url( '/bathco/' ) ); ?>" class="brand-card brand-card--wide">
      <div class="brand-card__image">
        <img src="https://images.unsplash.com/photo-1552321554-5fefe8c9ef14?w=900&q=80" alt="Bathco dekorativni umivaonik" loading="lazy" />
        <span class="brand-card__flag">🇪🇸 Santander, Cantabria</span>
      </div>
      <div class="brand-card__body">
        <span class="brand-card__category">Dekorativni umivaonici</span>
        <h3 class="brand-card__name">Bathco – The Bath Collection</h3>
        <p class="brand-card__desc">
          Porodična kompanija sa više od 45 godina iskustva u dizajnu i proizvodnji kupatilske opreme.
          Kameni, nadgradni i samostojeći umivaonici unikatnog dizajna. Prisutni u preko 100 zemalja.
          Atelier radionica za ručno dekorisane unikatne komade. ISO 9001 + ISO 14001 sertifikati. Garancija 5 godina.
        </p>
        <div class="brand-card__meta">
          <span class="brand-card__meta-item">⏱ 45+ godina</span>
          <span class="brand-card__meta-item">🏭 Santander, Cantabria</span>
          <span class="brand-card__meta-item">🌍 100+ zemalja</span>
          <span class="brand-card__meta-item">🎨 Atelier radionica</span>
          <span class="brand-card__meta-item">✅ 5 god. garancija</span>
        </div>
        <span class="brand-card__cta">
          Pogledaj kolekcije umivaonika
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </span>
      </div>
    </a>
  </div>
</section>

<!-- WHY SPANISH ORIGIN -->
<section class="brands-origin">
  <div class="brands-origin__inner">
    <div class="brands-origin__text">
      <h2>Zašto španska keramika?</h2>
      <p>
        Španija je drugi najveći proizvođač keramike na svijetu, odmah iza Kine.
        Region Castellón u Valenciji koncentrisao je stotine fabrika sa višestrukom tradicijom
        koja seže do maurske ere. Španska keramika kombinuje najnoviju tehnologiju sa
        stoljetnim zanatskim znanjem – rezultat su pločice koje su i vizuelno impresivne i
        tehnički superiorne.
      </p>
      <p>
        Door Expert je direktni uvoznik – bez posrednika, sa garancijom autentičnosti i
        konkurentnim cijenama za crnogorsko tržište.
      </p>
      <div class="brands-origin__stats">
        <div class="brands-origin__stat">
          <div class="brands-origin__stat-num">5</div>
          <div class="brands-origin__stat-label">brendova</div>
        </div>
        <div class="brands-origin__stat">
          <div class="brands-origin__stat-num">100+</div>
          <div class="brands-origin__stat-label">kolekcija</div>
        </div>
        <div class="brands-origin__stat">
          <div class="brands-origin__stat-num">57</div>
          <div class="brands-origin__stat-label">god. tradicije</div>
        </div>
      </div>
    </div>
    <div class="brands-origin__map">
      <img src="https://images.unsplash.com/photo-1559386484-97dfc0e15539?w=600&q=80" alt="Mapa Španije – Castellón region" loading="lazy" />
    </div>
  </div>
</section>

<!-- CTA -->
<section class="brands-cta">
  <h2>Pogledajte brendove uživo u našem salonu</h2>
  <p>Svi proizvodi dostupni za pregled u fizičkom salonu u Podgorici. Formalna ponuda bez obaveze.</p>
  <div class="brands-cta__buttons">
    <a href="<?php echo esc_url( home_url( '/kontakt/' ) ); ?>" class="brands-cta__btn brands-cta__btn--primary">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
      Posjetite salon
    </a>
    <a href="tel:+38269234888" class="brands-cta__btn brands-cta__btn--secondary">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
      Pozovite nas
    </a>
  </div>
</section>
