<?php
/**
 * Front page (naslovna).
 *
 * Konvertovano iz prototipa header-demo.html (linije 733-2024).
 * Sekcije: hero, trust-bar, categories, featured, promo-banner, room-nav,
 * brand-strip, instagram, pre-footer.
 *
 * CSS/JS za ove sekcije se kondicionalno enqueue-uju u functions.php
 * (is_front_page()). <main> otvara header.php, zatvara footer.php.
 *
 * TODO: slike u sekcijama su Unsplash placeholderi – zameniti WP medijom.
 * TODO: 'pre-footer' se ponavlja na vise stranica (kontakt, korpa, sigurnosna)
 *       -> kandidat za template-parts/pre-footer.php.
 *
 * @package DoorExpert
 */

get_header();
?>
<section class="hero hero--slide-1" aria-label="Istaknuti sadrzaj">

  <!-- Slide track -->
  <div class="hero__track">

    <!-- ══════════════════════════════════════
         SLIDE 1 – Inspirativni / Brand
         Message: premium showroom, 4 categories, stock advantage
         CTA: Pogledaj kolekcije (primary) + Posjetite salon (secondary)
    ══════════════════════════════════════ -->
    <div class="hero__slide hero__slide--1 is-active" aria-label="Slide 1 od 2">

      <!-- Background: warm sunlit interior with door, tiles, basin -->
      <div class="hero__bg" style="background-image: url('https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1800&q=80&auto=format&fit=crop');"></div>

      <!-- Gradient overlay -->
      <div class="hero__overlay" aria-hidden="true"></div>

      <!-- Content -->
      <div class="hero__content">
        <div class="hero__inner">

          <!-- Eyebrow -->
          <span class="hero__eyebrow">Premium showroom, Podgorica</span>

          <!-- Headline -->
          <!-- Research: NN/g – homepage must quickly explain who you are and what you sell -->
          <h1 class="hero__title">
            Vrata, keramika i<br>
            kupatilski elementi<br>
            <em>koji traju</em>
          </h1>

          <!-- Subtext -->
          <!-- Research: UX Research – "uvoznik/distributer" as advantage, not apology -->
          <p class="hero__sub">
            Direktan uvoz iz Spanije i Evrope. Pazljivo odabrani brendovi,
            fizicki salon u Podgorici, formalna ponuda bez obaveze.
          </p>

          <!-- CTA group -->
          <!-- Research: Conversion Strategy – primary "Pogledaj", secondary "Salon" -->
          <div class="hero__cta-group">
            <a href="<?php echo esc_url( home_url( '/prodavnica/' ) ); ?>" class="hero__btn-primary">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
              Prodavnica
            </a>
            <a href="<?php echo esc_url( home_url( '/kontakt/' ) ); ?>" class="hero__btn-secondary">
              Posjetite salon
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
          </div>

          <!-- Trust badge: stock availability -->
          <!-- Research: Trust Signals doc + Business Facts – unclaimed territory -->
          <div class="hero__trust">
            <span class="hero__trust-dot" aria-hidden="true"></span>
            Vrata na stanju – isporuka odmah. Konkurencija ceka 45+ dana.
          </div>

        </div>
      </div>

      <?php // [_shelf] Hero traka sa 4 kategorije sklonjena → _shelf/home-hero-category-strip.md ?>

    </div>
    <!-- /SLIDE 1 -->

    <!-- ══════════════════════════════════════
         SLIDE 2 – Aktuelna ponuda / Promo
         Message: curated selection, real savings, real deadline
         Research: Discounts doc – "kurirane prilike", monetary savings,
                   calm countdown, never reset timer
    ══════════════════════════════════════ -->
    <div class="hero__slide hero__slide--2" aria-label="Slide 2 od 2">

      <!-- Background: warm interior, tile/door combo -->
      <div class="hero__bg" style="background-image: url('https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=1800&q=80&auto=format&fit=crop');"></div>

      <!-- Gradient overlay -->
      <div class="hero__overlay" aria-hidden="true"></div>

      <!-- Right image panel: product detail shot -->
      <div class="hero__image-panel" aria-hidden="true">
        <img
          src="https://images.unsplash.com/photo-1615873968403-89e068629265?w=900&q=80&auto=format&fit=crop"
          alt=""
          loading="lazy"
        />
      </div>

      <!-- Content -->
      <div class="hero__content">
        <div class="hero__inner">

          <!-- Promo badge -->
          <!-- Research: Discounts doc – "kurirane prilike", not "SVE NA POPUSTU" -->
          <div class="hero__promo-badge">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            Izdvojena kolekcija jula
          </div>

          <!-- Headline -->
          <h2 class="hero__title">
            Spanska keramika<br>
            za kupatilo –<br>
            <em>posebna ponuda</em>
          </h2>

          <!-- Subtext -->
          <p class="hero__sub">
            Tau Ceramica i Arcana Ceramica kolekcije za kupatilo.
            Realne cijene, dostupno odmah u salonu.
          </p>

          <!-- Savings highlight -->
          <!-- Research: Discounts doc – monetary amount > percentage for high-ticket items -->
          <div class="hero__savings">
            <span class="hero__savings-amount">do 25%</span>
            <span class="hero__savings-label">popusta na<br>odabrane kolekcije</span>
          </div>

          <!-- CTA group -->
          <div class="hero__cta-group">
            <a href="<?php echo esc_url( door_expert_cat_url( 'keramicke-plocice' ) ); ?>" class="hero__btn-primary">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
              Pogledaj ponudu
            </a>
            <a href="tel:+38269234888" class="hero__btn-secondary">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.67A2 2 0 012 .84h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 8.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
              Pozovite za savjet
            </a>
          </div>

          <!-- Countdown -->
          <!-- Research: Discounts doc – real deadline, calm visual, "Ponuda vazi do" framing -->
          <div class="hero__countdown" role="timer" aria-label="Ponuda istice za">
            <span class="hero__countdown-label">Ponuda vazi jos</span>
            <div class="hero__countdown-units">
              <div class="hero__countdown-unit">
                <span class="hero__countdown-num" id="cd-days">--</span>
                <span class="hero__countdown-unit-label">dana</span>
              </div>
              <span class="hero__countdown-sep" aria-hidden="true">:</span>
              <div class="hero__countdown-unit">
                <span class="hero__countdown-num" id="cd-hours">--</span>
                <span class="hero__countdown-unit-label">sati</span>
              </div>
              <span class="hero__countdown-sep" aria-hidden="true">:</span>
              <div class="hero__countdown-unit">
                <span class="hero__countdown-num" id="cd-minutes">--</span>
                <span class="hero__countdown-unit-label">min</span>
              </div>
            </div>
          </div>

        </div>
      </div>

    </div>
    <!-- /SLIDE 2 -->

  </div>
  <!-- /hero__track -->

  <!-- ── Navigation controls ─────────────────────────────── -->
  <div class="hero__nav" role="group" aria-label="Navigacija slajdera">
    <button class="hero__nav-btn hero__nav-btn--prev" aria-label="Prethodni slajd" disabled>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
    </button>
    <div class="hero__dots" role="tablist" aria-label="Slajdovi">
      <button class="hero__dot is-active" role="tab" aria-selected="true" aria-label="Slajd 1"></button>
      <button class="hero__dot" role="tab" aria-selected="false" aria-label="Slajd 2"></button>
    </div>
    <button class="hero__nav-btn hero__nav-btn--next" aria-label="Sledeci slajd">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
    </button>
  </div>

  <!-- ── Slide counter (vertical, right side) ──────────────── -->
  <div class="hero__counter" aria-hidden="true">
    <span class="hero__counter-num is-current">01</span>
    <div class="hero__counter-line"></div>
    <span class="hero__counter-num">02</span>
  </div>

</section>
<!-- /hero -->

<!-- ════════════════════════════════════════════════
     TRUST BAR
     Immediately below hero – 4 trust signals
     Research: Trust Signals doc, UX Research (line 28),
               Business Facts (stock advantage = unclaimed territory)
════════════════════════════════════════════════ -->
<section class="trust-bar" aria-label="Zasto Door Expert">
  <div class="trust-bar__inner">

    <!-- Item 1: Stock advantage – #1 priority per Business Facts -->
    <!-- "No local competitor communicates this online – unclaimed territory" -->
    <div class="trust-bar__item trust-bar__item--stock">
      <div class="trust-bar__icon" aria-hidden="true">
        <svg viewBox="0 0 24 24"><path d="M5 12l5 5L20 7"/></svg>
      </div>
      <div class="trust-bar__text">
        <span class="trust-bar__title">
          <span class="trust-bar__stock-dot" aria-hidden="true"></span>
          Vrata odmah na stanju
        </span>
        <span class="trust-bar__sub">Konkurencija ceka 45+ dana – mi isporucujemo odmah</span>
      </div>
    </div>

    <!-- Item 2: Spanish origin – key differentiator for ceramics -->
    <!-- Trust Signals: "španski brendovi kao signal kvaliteta porijekla" -->
    <div class="trust-bar__item">
      <div class="trust-bar__icon" aria-hidden="true">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 010 20M12 2a15.3 15.3 0 000 20"/></svg>
      </div>
      <div class="trust-bar__text">
        <span class="trust-bar__title">Direktan uvoz iz Spanije</span>
        <span class="trust-bar__sub">Tau, Arcana, Bathco – originalni spanski brendovi</span>
      </div>
    </div>

    <!-- Item 3: Physical showroom – Balkan trust essential -->
    <!-- UX Research: "fizička prodavnica, adresa, mapa" kao Balkanski trust signal -->
    <a href="<?php echo esc_url( home_url( '/kontakt/' ) ); ?>" class="trust-bar__item trust-bar__item--link">
      <div class="trust-bar__icon" aria-hidden="true">
        <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
      </div>
      <div class="trust-bar__text">
        <span class="trust-bar__title">Fizicki salon, Podgorica</span>
        <span class="trust-bar__sub">Posjetite nas i vidite proizvode uzivo</span>
      </div>
    </a>

    <!-- Item 4: Process transparency – reduces anxiety, per Trust Signals + UX Research -->
    <!-- "transparentnost oko procesa kupovine gradi povjerenje" -->
    <div class="trust-bar__item">
      <div class="trust-bar__icon" aria-hidden="true">
        <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
      </div>
      <div class="trust-bar__text">
        <span class="trust-bar__title">Formalna ponuda mejlom</span>
        <span class="trust-bar__sub">Predracun bez obaveze – vi odlucujete</span>
      </div>
    </div>

  </div>
</section>
<!-- /trust-bar -->

<!-- ════════════════════════════════════════════════
     SECTION 3 – CATEGORY GATEWAYS
     4 equal-weight cards routing into each product world.

     Research:
     - Visual research (line 91): "curated showroom route:
       hero, category gateways..."
     - Zapisnik (p.1): jednaka vizuelna tezina vrata i keramike
     - UX Research (line 72): "jednaka vizuelna tezina"
     - Visual research (line 83): tile pages exploratory,
       door pages model/performance-led
════════════════════════════════════════════════ -->
<section class="categories" aria-label="Kategorije proizvoda">
  <div class="categories__inner">

    <div class="categories__header">
      <div>
        <p class="categories__eyebrow">Nase kategorije</p>
        <h2 class="categories__title">Sve sto vam treba za dom</h2>
      </div>
      <a href="<?php echo esc_url( door_expert_cat_url( 'sobna-vrata' ) ); ?>" class="categories__view-all">
        Pogledaj katalog
        <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
      </a>
    </div>

    <div class="categories__grid">

      <!-- 1. SOBNA VRATA -->
      <!-- Visual research line 83: door pages lead with model families,
           finishes, opening types – sub-tags reflect this -->
      <a href="<?php echo esc_url( door_expert_cat_url( 'sobna-vrata' ) ); ?>" class="cat-card" aria-label="Sobna vrata – pogledaj kolekciju">
        <img
          class="cat-card__img"
          src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=600&q=80"
          alt="Moderna sobna vrata u elegantnom enterijeru"
          loading="lazy"
        />
        <div class="cat-card__overlay" aria-hidden="true"></div>
        <span class="cat-card__badge">Odmah dostupno</span>
        <div class="cat-card__body">
          <h3 class="cat-card__name">Sobna vrata</h3>
          <ul class="cat-card__subs">
            <li class="cat-card__sub">Klizna vrata</li>
            <li class="cat-card__sub">Staklena vrata</li>
            <li class="cat-card__sub">Kupatilska</li>
          </ul>
          <span class="cat-card__cta">
            Pogledaj modele
            <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </span>
        </div>
      </a>

      <!-- 2. SIGURNOSNA VRATA -->
      <!-- Visual research line 18: "security doors as architectural
           products" – darker badge, trust/authority treatment -->
      <a href="<?php echo esc_url( door_expert_cat_url( 'sigurnosna-vrata' ) ); ?>" class="cat-card cat-card--security" aria-label="Sigurnosna vrata – pogledaj modele">
        <img
          class="cat-card__img"
          src="https://images.unsplash.com/photo-1600585154526-990dced4db0d?w=600&q=80"
          alt="Sigurnosna vrata za stan i kucu"
          loading="lazy"
        />
        <div class="cat-card__overlay" aria-hidden="true"></div>
        <span class="cat-card__badge">Sertifikovana zastita</span>
        <div class="cat-card__body">
          <h3 class="cat-card__name">Sigurnosna vrata</h3>
          <ul class="cat-card__subs">
            <li class="cat-card__sub">Za stan</li>
            <li class="cat-card__sub">Za kucu</li>
            <li class="cat-card__sub">Klase otpornosti</li>
          </ul>
          <span class="cat-card__cta">
            Pogledaj modele
            <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </span>
        </div>
      </a>

      <!-- 3. KERAMICKE PLOCICE -->
      <!-- Visual research line 83: tile pages more exploratory,
           sub-tags show breadth of 7 sub-categories -->
      <a href="<?php echo esc_url( door_expert_cat_url( 'keramicke-plocice' ) ); ?>" class="cat-card" aria-label="Keramicke plocice – pogledaj kolekcije">
        <img
          class="cat-card__img"
          src="https://images.unsplash.com/photo-1615971677499-5467cbab01c0?w=600&q=80"
          alt="Spanske keramicke plocice u modernom kupatilu"
          loading="lazy"
        />
        <div class="cat-card__overlay" aria-hidden="true"></div>
        <span class="cat-card__badge">Spanski brendovi</span>
        <div class="cat-card__body">
          <h3 class="cat-card__name">Keramicke plocice</h3>
          <ul class="cat-card__subs">
            <li class="cat-card__sub">Podne</li>
            <li class="cat-card__sub">Zidne</li>
            <li class="cat-card__sub">Za bazen</li>
            <li class="cat-card__sub">Gaziste</li>
          </ul>
          <span class="cat-card__cta">
            Istrazi kolekcije
            <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </span>
        </div>
      </a>

      <!-- 4. DEKORATIVNI UMIVAONICI -->
      <!-- Visual research line 19: "decorative washbasins more
           emotional and editorial – art-object framing" -->
      <a href="<?php echo esc_url( door_expert_cat_url( 'umivaonici' ) ); ?>" class="cat-card" aria-label="Dekorativni umivaonici – pogledaj kolekciju">
        <img
          class="cat-card__img"
          src="https://images.unsplash.com/photo-1552321554-5fefe8c9ef14?w=600&q=80"
          alt="Dekorativni umivaonik Bathco u kupatilu"
          loading="lazy"
        />
        <div class="cat-card__overlay" aria-hidden="true"></div>
        <span class="cat-card__badge">Bathco Spanija</span>
        <div class="cat-card__body">
          <h3 class="cat-card__name">Dekorativni umivaonici</h3>
          <ul class="cat-card__subs">
            <li class="cat-card__sub">Kameni</li>
            <li class="cat-card__sub">Nadgradni</li>
            <li class="cat-card__sub">Samostojeci</li>
          </ul>
          <span class="cat-card__cta">
            Istrazi kolekcije
            <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </span>
        </div>
      </a>

    </div><!-- /categories__grid -->
  </div><!-- /categories__inner -->
</section>
<!-- /categories -->

<!-- ════════════════════════════════════════════════
     SECTION 4 – FEATURED / NEW PRODUCTS
     Curated highlights, not a full catalogue.
     Research: Visual research (line 87, 91), UX Research (line 71, 83),
     Zapisnik (p.8), Business Facts (real prices, quote cart, discounts)
════════════════════════════════════════════════ -->
<section class="featured" aria-label="Istaknuti proizvodi">
  <div class="featured__inner">

    <div class="featured__header">
      <div>
        <p class="featured__eyebrow">Novo i istaknuto</p>
        <h2 class="featured__title">Odabrani za vas ovog mjeseca</h2>
      </div>
      <a href="<?php echo esc_url( door_expert_cat_url( 'sobna-vrata' ) ); ?>" class="featured__view-all">
        Cijeli katalog
        <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
      </a>
    </div>

    <!-- Tab filter: all / by category -->
    <div class="featured__tabs" role="tablist" aria-label="Filter po kategoriji">
      <button class="featured__tab is-active" data-filter="sve" role="tab" aria-selected="true">Sve</button>
      <button class="featured__tab" data-filter="sobna" role="tab" aria-selected="false">Sobna vrata</button>
      <button class="featured__tab" data-filter="sigurnosna" role="tab" aria-selected="false">Sigurnosna vrata</button>
      <button class="featured__tab" data-filter="keramika" role="tab" aria-selected="false">Keramicke plocice</button>
      <button class="featured__tab" data-filter="umivaonici" role="tab" aria-selected="false">Umivaonici</button>
    </div>

    <div class="featured__grid">

      <!-- CARD 1: Sobna vrata – new model -->
      <article class="prod-card" data-cat="sobna">
        <div class="prod-card__img-wrap">
          <img class="prod-card__img"
            src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=500&q=80"
            alt="Sobna vrata Milano bela mat"
            loading="lazy" />
          <div class="prod-card__badges">
            <span class="prod-badge prod-badge--new">Novo</span>
            <span class="prod-badge prod-badge--stock">Na stanju</span>
          </div>
          <button class="prod-card__wishlist" aria-label="Sacuvaj u listu zelja">
            <svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
          </button>
        </div>
        <div class="prod-card__body">
          <span class="prod-card__cat">Sobna vrata</span>
          <h3 class="prod-card__name">Milano – Bijela mat</h3>
          <p class="prod-card__attrs">2000 × 800 mm &middot; MDF &middot; 3 dimenzije</p>
          <div class="prod-card__price-row">
            <span class="prod-card__price">189 €</span>
          </div>
          <button class="prod-card__add">
            <svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 001.95-1.57l1.65-8.42H6"/></svg>
            Dodaj u korpu za ponudu
          </button>
        </div>
      </article>

      <!-- CARD 2: Sigurnosna vrata – on sale -->
      <article class="prod-card" data-cat="sigurnosna">
        <div class="prod-card__img-wrap">
          <img class="prod-card__img"
            src="https://images.unsplash.com/photo-1600585154526-990dced4db0d?w=500&q=80"
            alt="Sigurnosna vrata Forte antracit"
            loading="lazy" />
          <div class="prod-card__badges">
            <span class="prod-badge prod-badge--sale">-15%</span>
          </div>
          <button class="prod-card__wishlist" aria-label="Sacuvaj u listu zelja">
            <svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
          </button>
        </div>
        <div class="prod-card__body">
          <span class="prod-card__cat">Sigurnosna vrata</span>
          <h3 class="prod-card__name">Forte – Antracit</h3>
          <p class="prod-card__attrs">Za stan &middot; Klasa 3 &middot; RC3 sertifikat</p>
          <div class="prod-card__price-row">
            <span class="prod-card__price">680 €</span>
            <span class="prod-card__price-old">800 €</span>
            <span class="prod-card__discount">-15%</span>
          </div>
          <button class="prod-card__add">
            <svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 001.95-1.57l1.65-8.42H6"/></svg>
            Dodaj u korpu za ponudu
          </button>
        </div>
      </article>

      <!-- CARD 3: Keramicke plocice – new collection -->
      <article class="prod-card" data-cat="keramika">
        <div class="prod-card__img-wrap">
          <img class="prod-card__img"
            src="https://images.unsplash.com/photo-1615971677499-5467cbab01c0?w=500&q=80"
            alt="Tau Ceramica Travertino plocice"
            loading="lazy" />
          <div class="prod-card__badges">
            <span class="prod-badge prod-badge--new">Nova kolekcija</span>
          </div>
          <button class="prod-card__wishlist" aria-label="Sacuvaj u listu zelja">
            <svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
          </button>
        </div>
        <div class="prod-card__body">
          <span class="prod-card__cat">Keramicke plocice &middot; Tau Ceramica</span>
          <h3 class="prod-card__name">Travertino Beige</h3>
          <p class="prod-card__attrs">60 × 120 cm &middot; Podne &middot; Mat</p>
          <div class="prod-card__price-row">
            <span class="prod-card__price">28 €/m²</span>
          </div>
          <button class="prod-card__add">
            <svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 001.95-1.57l1.65-8.42H6"/></svg>
            Dodaj u korpu za ponudu
          </button>
        </div>
      </article>

      <!-- CARD 4: Umivaonik – art-object framing -->
      <article class="prod-card" data-cat="umivaonici">
        <div class="prod-card__img-wrap">
          <img class="prod-card__img"
            src="https://images.unsplash.com/photo-1552321554-5fefe8c9ef14?w=500&q=80"
            alt="Bathco kameni umivaonik"
            loading="lazy" />
          <div class="prod-card__badges">
            <span class="prod-badge prod-badge--new">Novo</span>
          </div>
          <button class="prod-card__wishlist" aria-label="Sacuvaj u listu zelja">
            <svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
          </button>
        </div>
        <div class="prod-card__body">
          <span class="prod-card__cat">Umivaonici &middot; Bathco</span>
          <h3 class="prod-card__name">Mueble Oval – Kameni</h3>
          <p class="prod-card__attrs">Prirodni kamen &middot; Oval oblik &middot; Bijeli</p>
          <div class="prod-card__price-row">
            <span class="prod-card__price">320 €</span>
            <span class="prod-card__price-old">380 €</span>
            <span class="prod-card__discount">-16%</span>
          </div>
          <button class="prod-card__add">
            <svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 001.95-1.57l1.65-8.42H6"/></svg>
            Dodaj u korpu za ponudu
          </button>
        </div>
      </article>

    </div><!-- /featured__grid -->
  </div><!-- /featured__inner -->
</section>
<!-- /featured -->

<!-- ════════════════════════════════════════════════
     SECTION 5 – PROMO BANNER WITH COUNTDOWN
     Research: Discounts doc (line 36-39, 53, 63)
     Calm, premium urgency. Real deadline. EUR savings shown.
════════════════════════════════════════════════ -->
<section class="promo-banner" aria-label="Aktuelna akcija">
  <div class="promo-banner__inner">

    <!-- Left: content -->
    <div class="promo-banner__content">

      <span class="promo-banner__label">
        <span class="promo-banner__label-dot" aria-hidden="true"></span>
        Aktuelna kolekcijska ponuda
      </span>

      <h2 class="promo-banner__title">
        Julska selekcija:
        <em>spanska keramika i sobna vrata</em>
      </h2>

      <p class="promo-banner__desc">
        Odabrani modeli iz kolekcija Tau Ceramica i Arcana Ceramica,
        uz sobna vrata iz aktuelnog skladišta. Sve na stanju,
        isporuka odmah.
      </p>

      <!-- EUR savings – Discounts research (line 63):
           "amount off" creates stronger purchase intent for
           high-ticket items than percentage alone -->
      <div class="promo-banner__savings">
        <div class="promo-banner__saving-item">
          <span class="promo-banner__saving-amount">do 120 €</span>
          <span class="promo-banner__saving-label">ušteda na vratima</span>
        </div>
        <div class="promo-banner__saving-divider" aria-hidden="true"></div>
        <div class="promo-banner__saving-item">
          <span class="promo-banner__saving-amount">do 8 €/m²</span>
          <span class="promo-banner__saving-label">ušteda na keramici</span>
        </div>
        <div class="promo-banner__saving-divider" aria-hidden="true"></div>
        <div class="promo-banner__saving-item">
          <span class="promo-banner__saving-amount">do 60 €</span>
          <span class="promo-banner__saving-label">ušteda na umivaonicima</span>
        </div>
      </div>

      <div class="promo-banner__actions">
        <a href="<?php echo esc_url( home_url( '/akcije/' ) ); ?>" class="promo-banner__cta-primary">
          Pogledaj ponudu
          <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
        <a href="tel:+38269234888" class="promo-banner__cta-secondary">
          Pozovite za savjet
        </a>
      </div>

    </div><!-- /promo-banner__content -->

    <!-- Right: countdown -->
    <!-- Discounts research: minimal, calm, no blinking, no red alarms.
         Shows days/hours/mins/secs to real end-of-month deadline. -->
    <div class="promo-banner__countdown" aria-label="Odbrojavanje do kraja akcije">
      <p class="promo-banner__countdown-label">Ponuda ističe za</p>

      <div class="countdown-units">

        <div class="countdown-unit">
          <span class="countdown-unit__value" id="pb-days">--</span>
          <span class="countdown-unit__label">dana</span>
        </div>

        <span class="countdown-separator" aria-hidden="true">:</span>

        <div class="countdown-unit">
          <span class="countdown-unit__value" id="pb-hours">--</span>
          <span class="countdown-unit__label">sati</span>
        </div>

        <span class="countdown-separator" aria-hidden="true">:</span>

        <div class="countdown-unit">
          <span class="countdown-unit__value" id="pb-mins">--</span>
          <span class="countdown-unit__label">min</span>
        </div>

        <span class="countdown-separator" aria-hidden="true">:</span>

        <div class="countdown-unit">
          <span class="countdown-unit__value" id="pb-secs">--</span>
          <span class="countdown-unit__label">sek</span>
        </div>

      </div><!-- /countdown-units -->

      <p class="promo-banner__deadline" id="pb-deadline-text"></p>
    </div><!-- /promo-banner__countdown -->

  </div><!-- /promo-banner__inner -->
</section>
<!-- /promo-banner -->

<!-- ════════════════════════════════════════════════
     SECTION 6 – ROOM-BASED NAVIGATION
     Research: UX Navigation (line 7-9), Zapisnik sastanka
     Secondary discovery path: room → atmospheric image + products
     Cross-sell: bathroom = tiles + washbasin + interior door
════════════════════════════════════════════════ -->
<section class="room-nav" aria-label="Pretraga po prostoriji">
  <div class="room-nav__inner">

    <!-- Section header -->
    <div class="room-nav__header">
      <div class="room-nav__header-left">
        <span class="room-nav__eyebrow">Inspiracija po prostoriji</span>
        <h2 class="room-nav__title">Pronađite proizvode za svaki prostor</h2>
      </div>
      <a href="<?php echo esc_url( home_url( '/inspiracija/' ) ); ?>" class="room-nav__see-all">Sve prostorije &rarr;</a>
    </div>

    <!-- Room tabs -->
    <div class="room-nav__tabs" role="tablist" aria-label="Prostorije">

      <button class="room-tab" data-room="kupatilo" role="tab" aria-selected="true">
        <svg viewBox="0 0 24 24"><path d="M4 12h16M4 12V8a2 2 0 012-2h12a2 2 0 012 2v4M4 12v4a2 2 0 002 2h12a2 2 0 002-2v-4"/><circle cx="9" cy="17" r="1"/><circle cx="15" cy="17" r="1"/></svg>
        Kupatilo
      </button>

      <button class="room-tab" data-room="dnevna" role="tab" aria-selected="false">
        <svg viewBox="0 0 24 24"><rect x="3" y="10" width="18" height="8" rx="1"/><path d="M7 10V7a1 1 0 011-1h8a1 1 0 011 1v3"/><path d="M3 18v2M21 18v2"/></svg>
        Dnevna soba
      </button>

      <button class="room-tab" data-room="hodnik" role="tab" aria-selected="false">
        <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="1"/><path d="M12 3v18M3 12h9"/><circle cx="10" cy="12" r="1"/></svg>
        Hodnik
      </button>

      <button class="room-tab" data-room="kuhinja" role="tab" aria-selected="false">
        <svg viewBox="0 0 24 24"><path d="M3 6h18M3 12h18M3 18h18"/><rect x="3" y="3" width="18" height="18" rx="1"/></svg>
        Kuhinja
      </button>

      <button class="room-tab" data-room="spavaca" role="tab" aria-selected="false">
        <svg viewBox="0 0 24 24"><path d="M2 20v-8a2 2 0 012-2h16a2 2 0 012 2v8"/><path d="M2 15h20"/><path d="M6 10V6a2 2 0 012-2h8a2 2 0 012 2v4"/></svg>
        Spavaća soba
      </button>

    </div><!-- /room-nav__tabs -->

    <!-- ── KUPATILO panel ──────────────────────────────────── -->
    <!-- Cross-sell: tiles + washbasin + bathroom door (Business Facts) -->
    <div class="room-panel" data-room="kupatilo" role="tabpanel">

      <div class="room-panel__hero">
        <img src="https://images.unsplash.com/photo-1552321554-5fefe8c9ef14?w=900&q=80" alt="Moderno kupatilo sa španskom keramikom i kamenim umivaonikom" loading="lazy">
        <div class="room-panel__hero-overlay"></div>
        <div class="room-panel__hero-content">
          <p class="room-panel__hero-label">Kupatilo</p>
          <h3 class="room-panel__hero-title">Keramika, umivaonik i vrata u jednom prostoru</h3>
          <div class="room-panel__hero-tags">
            <a href="<?php echo esc_url( door_expert_cat_url( 'keramicke-plocice' ) ); ?>" class="room-panel__hero-tag">
              <svg viewBox="0 0 24 24" width="10" height="10"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
              Pločice za kupatilo
            </a>
            <a href="<?php echo esc_url( door_expert_cat_url( 'umivaonici' ) ); ?>" class="room-panel__hero-tag">
              <svg viewBox="0 0 24 24" width="10" height="10"><path d="M4 12h16M4 12V8a2 2 0 012-2h12a2 2 0 012 2v4"/></svg>
              Umivaonici
            </a>
            <a href="<?php echo esc_url( door_expert_cat_url( 'sobna-vrata' ) ); ?>" class="room-panel__hero-tag">
              <svg viewBox="0 0 24 24" width="10" height="10"><rect x="3" y="2" width="18" height="20" rx="1"/><circle cx="16" cy="12" r="1"/></svg>
              Kupatilska vrata
            </a>
          </div>
        </div>
      </div>

      <div class="room-panel__products">

        <a href="<?php echo esc_url( door_expert_cat_url( 'keramicke-plocice' ) ); ?>" class="room-product-card">
          <img class="room-product-card__img" src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&q=75" alt="Pločice za kupatilo" loading="lazy">
          <div class="room-product-card__info">
            <p class="room-product-card__category">Tau Ceramica</p>
            <p class="room-product-card__name">Marmol Blanco 60×120</p>
            <p class="room-product-card__attr">Zid i pod &middot; Mat finish</p>
          </div>
          <div class="room-product-card__price">
            <span class="room-product-card__price-current">18 €/m²</span>
            <span class="room-product-card__price-old">24 €/m²</span>
          </div>
        </a>

        <a href="<?php echo esc_url( door_expert_cat_url( 'umivaonici' ) ); ?>" class="room-product-card">
          <img class="room-product-card__img" src="https://images.unsplash.com/photo-1584622650111-993a426fbf0a?w=200&q=75" alt="Kameni umivaonik" loading="lazy">
          <div class="room-product-card__info">
            <p class="room-product-card__category">Bathco Spanija</p>
            <p class="room-product-card__name">Mueble Oval Stone</p>
            <p class="room-product-card__attr">Kameni &middot; Nadgradni</p>
          </div>
          <div class="room-product-card__price">
            <span class="room-product-card__price-current">285 €</span>
          </div>
        </a>

        <a href="<?php echo esc_url( door_expert_cat_url( 'sobna-vrata' ) ); ?>" class="room-product-card">
          <img class="room-product-card__img" src="https://images.unsplash.com/photo-1600585154526-990dced4db0d?w=200&q=75" alt="Kupatilska vrata" loading="lazy">
          <div class="room-product-card__info">
            <p class="room-product-card__category">Sobna vrata</p>
            <p class="room-product-card__name">Bijela mat &middot; 80×200</p>
            <p class="room-product-card__attr">Vodootporna &middot; Na stanju</p>
          </div>
          <div class="room-product-card__price">
            <span class="room-product-card__price-current">145 €</span>
          </div>
        </a>

        <!-- Cross-sell note: bathroom renovation first, then doors -->
        <div class="room-panel__crosssell">
          <p class="room-panel__crosssell-text">
            <strong>Savjet:</strong> Većina kupaca počinje renovacijom kupatila, pa bira vrata. Pogledajte kompletnu ponudu za kupatilo.
          </p>
          <a href="<?php echo esc_url( home_url( '/inspiracija/' ) ); ?>" class="room-panel__crosssell-link">
            Kupatilo komplet
            <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </a>
        </div>

      </div><!-- /room-panel__products -->
    </div><!-- /kupatilo panel -->

    <!-- ── DNEVNA SOBA panel ───────────────────────────────── -->
    <div class="room-panel" data-room="dnevna" role="tabpanel">

      <div class="room-panel__hero">
        <img src="https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?w=900&q=80" alt="Moderna dnevna soba sa elegantnim unutrasnjim vratima" loading="lazy">
        <div class="room-panel__hero-overlay"></div>
        <div class="room-panel__hero-content">
          <p class="room-panel__hero-label">Dnevna soba</p>
          <h3 class="room-panel__hero-title">Unutrašnja vrata koja definisu prostor</h3>
          <div class="room-panel__hero-tags">
            <a href="<?php echo esc_url( door_expert_cat_url( 'sobna-vrata' ) ); ?>" class="room-panel__hero-tag">Sobna vrata</a>
            <a href="<?php echo esc_url( door_expert_cat_url( 'staklena-vrata' ) ); ?>" class="room-panel__hero-tag">Staklena vrata</a>
            <a href="<?php echo esc_url( door_expert_cat_url( 'keramicke-plocice' ) ); ?>" class="room-panel__hero-tag">Podne pločice</a>
          </div>
        </div>
      </div>

      <div class="room-panel__products">
        <a href="<?php echo esc_url( door_expert_cat_url( 'sobna-vrata' ) ); ?>" class="room-product-card">
          <img class="room-product-card__img" src="https://images.unsplash.com/photo-1600585154526-990dced4db0d?w=200&q=75" alt="Staklena unutrasnja vrata" loading="lazy">
          <div class="room-product-card__info">
            <p class="room-product-card__category">Sobna vrata</p>
            <p class="room-product-card__name">Staklena &middot; Crni ram</p>
            <p class="room-product-card__attr">80×200 &middot; Na stanju</p>
          </div>
          <div class="room-product-card__price">
            <span class="room-product-card__price-current">320 €</span>
            <span class="room-product-card__price-old">380 €</span>
          </div>
        </a>
        <a href="<?php echo esc_url( door_expert_cat_url( 'keramicke-plocice' ) ); ?>" class="room-product-card">
          <img class="room-product-card__img" src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&q=75" alt="Podne plocice" loading="lazy">
          <div class="room-product-card__info">
            <p class="room-product-card__category">Arcana Ceramica</p>
            <p class="room-product-card__name">Oak Effect 20×120</p>
            <p class="room-product-card__attr">Podna &middot; Drvo efekat</p>
          </div>
          <div class="room-product-card__price">
            <span class="room-product-card__price-current">22 €/m²</span>
          </div>
        </a>
        <a href="<?php echo esc_url( door_expert_cat_url( 'sobna-vrata' ) ); ?>" class="room-product-card">
          <img class="room-product-card__img" src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=200&q=75" alt="Klizna vrata" loading="lazy">
          <div class="room-product-card__info">
            <p class="room-product-card__category">Sobna vrata</p>
            <p class="room-product-card__name">Klizna &middot; Bijela mat</p>
            <p class="room-product-card__attr">90×210 &middot; Klizni sistem</p>
          </div>
          <div class="room-product-card__price">
            <span class="room-product-card__price-current">275 €</span>
          </div>
        </a>
        <div class="room-panel__crosssell">
          <p class="room-panel__crosssell-text">
            <strong>Pro savjet:</strong> Klizna vrata štede prostor u manjim stanovima. Dostupna odmah, bez čekanja.
          </p>
          <a href="<?php echo esc_url( door_expert_cat_url( 'klizna' ) ); ?>" class="room-panel__crosssell-link">
            Pogledaj klizna vrata
            <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </a>
        </div>
      </div>
    </div><!-- /dnevna panel -->

    <!-- ── HODNIK panel ────────────────────────────────────── -->
    <div class="room-panel" data-room="hodnik" role="tabpanel">

      <div class="room-panel__hero">
        <img src="https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=900&q=80" alt="Hodnik sa sigurnosnim i unutrasnjim vratima" loading="lazy">
        <div class="room-panel__hero-overlay"></div>
        <div class="room-panel__hero-content">
          <p class="room-panel__hero-label">Hodnik</p>
          <h3 class="room-panel__hero-title">Sigurnosna i unutrašnja vrata za hodnik</h3>
          <div class="room-panel__hero-tags">
            <a href="<?php echo esc_url( door_expert_cat_url( 'sigurnosna-vrata' ) ); ?>" class="room-panel__hero-tag">Sigurnosna vrata</a>
            <a href="<?php echo esc_url( door_expert_cat_url( 'sobna-vrata' ) ); ?>" class="room-panel__hero-tag">Unutrašnja vrata</a>
            <a href="<?php echo esc_url( door_expert_cat_url( 'keramicke-plocice' ) ); ?>" class="room-panel__hero-tag">Podne pločice</a>
          </div>
        </div>
      </div>

      <div class="room-panel__products">
        <a href="<?php echo esc_url( door_expert_cat_url( 'sigurnosna-vrata' ) ); ?>" class="room-product-card">
          <img class="room-product-card__img" src="https://images.unsplash.com/photo-1600585154526-990dced4db0d?w=200&q=75" alt="Sigurnosna vrata za stan" loading="lazy">
          <div class="room-product-card__info">
            <p class="room-product-card__category">Sigurnosna vrata</p>
            <p class="room-product-card__name">Premium RC3 &middot; Za stan</p>
            <p class="room-product-card__attr">Klasa 3 &middot; Sertifikovana</p>
          </div>
          <div class="room-product-card__price">
            <span class="room-product-card__price-current">890 €</span>
            <span class="room-product-card__price-old">1.050 €</span>
          </div>
        </a>
        <a href="<?php echo esc_url( door_expert_cat_url( 'keramicke-plocice' ) ); ?>" class="room-product-card">
          <img class="room-product-card__img" src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&q=75" alt="Podne plocice za hodnik" loading="lazy">
          <div class="room-product-card__info">
            <p class="room-product-card__category">Ceramica Ribesalbes</p>
            <p class="room-product-card__name">Concrete Grey 60×60</p>
            <p class="room-product-card__attr">Podna &middot; Antiklizna</p>
          </div>
          <div class="room-product-card__price">
            <span class="room-product-card__price-current">16 €/m²</span>
          </div>
        </a>
        <div class="room-panel__crosssell">
          <p class="room-panel__crosssell-text">
            <strong>Napomena:</strong> Sigurnosna vrata su dostupna odmah. Ugradnja po dogovoru sa preporučenim majstorima.
          </p>
          <a href="<?php echo esc_url( door_expert_cat_url( 'sigurnosna-vrata' ) ); ?>" class="room-panel__crosssell-link">
            Sve o sigurnosnim vratima
            <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </a>
        </div>
      </div>
    </div><!-- /hodnik panel -->

    <!-- ── KUHINJA panel ───────────────────────────────────── -->
    <div class="room-panel" data-room="kuhinja" role="tabpanel">

      <div class="room-panel__hero">
        <img src="https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=900&q=80" alt="Moderna kuhinja sa španskim plocicama" loading="lazy">
        <div class="room-panel__hero-overlay"></div>
        <div class="room-panel__hero-content">
          <p class="room-panel__hero-label">Kuhinja</p>
          <h3 class="room-panel__hero-title">Spanska keramika za kuhinju</h3>
          <div class="room-panel__hero-tags">
            <a href="<?php echo esc_url( door_expert_cat_url( 'keramicke-plocice' ) ); ?>" class="room-panel__hero-tag">Pločice za kuhinju</a>
            <a href="<?php echo esc_url( door_expert_cat_url( 'keramicke-plocice' ) ); ?>" class="room-panel__hero-tag">Zidne pločice</a>
            <a href="<?php echo esc_url( door_expert_cat_url( 'sobna-vrata' ) ); ?>" class="room-panel__hero-tag">Vrata za kuhinju</a>
          </div>
        </div>
      </div>

      <div class="room-panel__products">
        <a href="<?php echo esc_url( door_expert_cat_url( 'keramicke-plocice' ) ); ?>" class="room-product-card">
          <img class="room-product-card__img" src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200&q=75" alt="Plocice za kuhinju" loading="lazy">
          <div class="room-product-card__info">
            <p class="room-product-card__category">New Tiles</p>
            <p class="room-product-card__name">Metro White 10×30</p>
            <p class="room-product-card__attr">Zidna &middot; Sjaj finish</p>
          </div>
          <div class="room-product-card__price">
            <span class="room-product-card__price-current">12 €/m²</span>
          </div>
        </a>
        <a href="<?php echo esc_url( door_expert_cat_url( 'keramicke-plocice' ) ); ?>" class="room-product-card">
          <img class="room-product-card__img" src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=200&q=75" alt="Podne plocice za kuhinju" loading="lazy">
          <div class="room-product-card__info">
            <p class="room-product-card__category">Tau Ceramica</p>
            <p class="room-product-card__name">Stone Beige 60×60</p>
            <p class="room-product-card__attr">Podna &middot; Antiklizna R10</p>
          </div>
          <div class="room-product-card__price">
            <span class="room-product-card__price-current">19 €/m²</span>
            <span class="room-product-card__price-old">24 €/m²</span>
          </div>
        </a>
        <div class="room-panel__crosssell">
          <p class="room-panel__crosssell-text">
            <strong>Savjet:</strong> Za kuhinju preporučujemo antiklizne podne pločice (R10 ili više) i lako čistive zidne.
          </p>
          <a href="<?php echo esc_url( door_expert_cat_url( 'keramicke-plocice' ) ); ?>" class="room-panel__crosssell-link">
            Pločice za kuhinju
            <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </a>
        </div>
      </div>
    </div><!-- /kuhinja panel -->

    <!-- ── SPAVACA SOBA panel ──────────────────────────────── -->
    <div class="room-panel" data-room="spavaca" role="tabpanel">

      <div class="room-panel__hero">
        <img src="https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=900&q=80" alt="Elegantna spavaca soba sa unutrasnjim vratima" loading="lazy">
        <div class="room-panel__hero-overlay"></div>
        <div class="room-panel__hero-content">
          <p class="room-panel__hero-label">Spavaća soba</p>
          <h3 class="room-panel__hero-title">Unutrašnja vrata za spavaću sobu</h3>
          <div class="room-panel__hero-tags">
            <a href="<?php echo esc_url( door_expert_cat_url( 'sobna-vrata' ) ); ?>" class="room-panel__hero-tag">Sobna vrata</a>
            <a href="<?php echo esc_url( door_expert_cat_url( 'klizna' ) ); ?>" class="room-panel__hero-tag">Klizna vrata</a>
            <a href="<?php echo esc_url( door_expert_cat_url( 'keramicke-plocice' ) ); ?>" class="room-panel__hero-tag">Podne pločice</a>
          </div>
        </div>
      </div>

      <div class="room-panel__products">
        <a href="<?php echo esc_url( door_expert_cat_url( 'sobna-vrata' ) ); ?>" class="room-product-card">
          <img class="room-product-card__img" src="https://images.unsplash.com/photo-1600585154526-990dced4db0d?w=200&q=75" alt="Sobna vrata za spavacu sobu" loading="lazy">
          <div class="room-product-card__info">
            <p class="room-product-card__category">Sobna vrata</p>
            <p class="room-product-card__name">Hrast natural &middot; 80×200</p>
            <p class="room-product-card__attr">Folijirana &middot; Na stanju</p>
          </div>
          <div class="room-product-card__price">
            <span class="room-product-card__price-current">165 €</span>
          </div>
        </a>
        <a href="<?php echo esc_url( door_expert_cat_url( 'sobna-vrata' ) ); ?>" class="room-product-card">
          <img class="room-product-card__img" src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=200&q=75" alt="Klizna vrata za spavacu sobu" loading="lazy">
          <div class="room-product-card__info">
            <p class="room-product-card__category">Klizna vrata</p>
            <p class="room-product-card__name">Bijela mat &middot; 90×210</p>
            <p class="room-product-card__attr">Štedi prostor &middot; Na stanju</p>
          </div>
          <div class="room-product-card__price">
            <span class="room-product-card__price-current">275 €</span>
            <span class="room-product-card__price-old">320 €</span>
          </div>
        </a>
        <div class="room-panel__crosssell">
          <p class="room-panel__crosssell-text">
            <strong>Savjet:</strong> Prosječan stan ima 5 unutrašnjih vrata. Pitajte za paketnu cijenu.
          </p>
          <a href="tel:+38269234888" class="room-panel__crosssell-link">
            Pozovite za paketnu cijenu
            <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </a>
        </div>
      </div>
    </div><!-- /spavaca panel -->

  </div><!-- /room-nav__inner -->

  <!-- Bottom CTA strip -->
  <div class="room-nav__bottom">
    <p class="room-nav__bottom-text">
      <strong>Ne znate odakle da krenete?</strong> Posjetite nas u salonu u Podgorici &mdash; pomoćićemo vam da izaberete pravo rješenje za svaki prostor.
    </p>
    <a href="<?php echo esc_url( home_url( '/kontakt/' ) ); ?>" class="room-nav__bottom-cta">
      Posjetite salon
      <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
    </a>
  </div>

</section>
<!-- /room-nav -->

<!-- ════════════════════════════════════════════════
     SECTION 7 – BRAND STRIP
     Research: Business Facts (Spanish origin = biggest advantage)
     Trust Signals doc: brendovi kao signal kvaliteta porijekla
     Brands: New Tiles, Arcana Ceramica, Tau Ceramica,
             Ceramica Ribesalbes (plocice) + Bathco (umivaonici)
     Door brands NOT shown (Russia/Ukraine factories)
════════════════════════════════════════════════ -->
<section class="brand-strip" aria-label="Brendovi koje zastupamo">
  <div class="brand-strip__inner">

    <div class="brand-strip__header">
      <p class="brand-strip__eyebrow">Direktan uvoz</p>
      <h2 class="brand-strip__title">Originalni <em>spanski brendovi</em> u nasem salonu</h2>
    </div>

    <div class="brand-strip__grid">

      <!-- 1. New Tiles -->
      <a href="<?php echo esc_url( door_expert_cat_url( 'keramicke-plocice' ) ); ?>" class="brand-card">
        <span class="brand-card__spain-badge">🇪🇸 Spanija</span>
        <div class="brand-card__logo-wrap">
          <span class="brand-card__logo-text">NEW TILES</span>
        </div>
        <div class="brand-card__divider"></div>
        <span class="brand-card__category">Keramicke plocice</span>
        <p class="brand-card__desc">Savremeni dizajn i siroka paleta formata za svaki prostor</p>
      </a>

      <!-- 2. Arcana Ceramica -->
      <a href="<?php echo esc_url( door_expert_cat_url( 'keramicke-plocice' ) ); ?>" class="brand-card">
        <span class="brand-card__spain-badge">🇪🇸 Spanija</span>
        <div class="brand-card__logo-wrap">
          <span class="brand-card__logo-text">ARCANA<br>CERAMICA</span>
        </div>
        <div class="brand-card__divider"></div>
        <span class="brand-card__category">Keramicke plocice</span>
        <p class="brand-card__desc">Kolekcije inspirisane prirodnim materijalima i arhitekturom</p>
      </a>

      <!-- 3. Tau Ceramica -->
      <a href="<?php echo esc_url( door_expert_cat_url( 'keramicke-plocice' ) ); ?>" class="brand-card">
        <span class="brand-card__spain-badge">🇪🇸 Spanija</span>
        <div class="brand-card__logo-wrap">
          <span class="brand-card__logo-text">TAU<br>CERAMICA</span>
        </div>
        <div class="brand-card__divider"></div>
        <span class="brand-card__category">Keramicke plocice</span>
        <p class="brand-card__desc">Premium plocice za podove i zidove, mramor i beton efekti</p>
      </a>

      <!-- 4. Ceramica Ribesalbes -->
      <a href="<?php echo esc_url( door_expert_cat_url( 'keramicke-plocice' ) ); ?>" class="brand-card">
        <span class="brand-card__spain-badge">🇪🇸 Spanija</span>
        <div class="brand-card__logo-wrap">
          <span class="brand-card__logo-text">CERAMICA<br>RIBESALBES</span>
        </div>
        <div class="brand-card__divider"></div>
        <span class="brand-card__category">Keramicke plocice</span>
        <p class="brand-card__desc">Artizanska tradicija i savremeni dizajn iz Valencije</p>
      </a>

      <!-- 5. Bathco -->
      <a href="<?php echo esc_url( door_expert_cat_url( 'umivaonici' ) ); ?>" class="brand-card">
        <span class="brand-card__spain-badge">🇪🇸 Spanija</span>
        <div class="brand-card__logo-wrap">
          <span class="brand-card__logo-text">BATHCO</span>
        </div>
        <div class="brand-card__divider"></div>
        <span class="brand-card__category">Dekorativni umivaonici</span>
        <p class="brand-card__desc">Kameni, nadgradni i samostojeci umivaonici, unikatni dizajn</p>
      </a>

    </div><!-- /brand-strip__grid -->

    <div class="brand-strip__note">
      <p class="brand-strip__note-text">Svi brendovi su originalni spanski uvoz &mdash; dostupni u nasem salonu u Podgorici.</p>
      <a href="<?php echo esc_url( door_expert_cat_url( 'keramicke-plocice' ) ); ?>" class="brand-strip__note-link">
        Pogledaj sve kolekcije
        <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
      </a>
    </div>

  </div><!-- /brand-strip__inner -->
</section>
<!-- /brand-strip -->

<!-- ════════════════════════════════════════════════
     SECTION 8 – INSTAGRAM FEED
     Research: Zapisnik sastanka (dno stranice, iznad footera)
     Website UX Research: "Instagram donosi 70% kupaca"
     Conversion Strategy: nikad na vrhu (ne slati korisnike sa sajta)
     PRODUKCIJA: Zamijeniti placeholder sa Behold.so widgetom
════════════════════════════════════════════════ -->
<section class="instagram-section" aria-label="Instagram feed">
  <div class="instagram-section__inner">

    <div class="instagram-section__header">
      <div class="instagram-section__header-left">
        <p class="instagram-section__eyebrow">
          <svg viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>
          Pratite nas
        </p>
        <h2 class="instagram-section__title">
          Inspiracija na <em class="instagram-section__handle">@doorexpert.me</em>
        </h2>
      </div>
      <a href="https://instagram.com/doorexpert.me" target="_blank" rel="noopener" class="instagram-section__follow-link">
        <svg viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>
        Pratite na Instagramu
      </a>
    </div>

    <!-- 
      PRODUKCIJA: Zamijeniti ovaj grid sa Behold.so widgetom:
      1. Registrovati se na https://behold.so
      2. Povezati Instagram profil @doorexpert.me
      3. Zamijeniti .instagram-grid div sa:
         <div id="behold-widget-XXXX"></div>
         <script src="https://w.behold.so/widget.js" type="module"></script>
    -->
    <div class="instagram-grid">

      <!-- Featured post (2x2) -->
      <a href="https://instagram.com/doorexpert.me" target="_blank" rel="noopener" class="instagram-item instagram-item--featured">
        <img src="https://images.unsplash.com/photo-1552321554-5fefe8c9ef14?w=600&q=80" alt="Door Expert Instagram - moderno kupatilo" loading="lazy">
        <div class="instagram-item__overlay">
          <svg class="instagram-item__overlay-icon" viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="white" stroke="none"/></svg>
          <span class="instagram-item__overlay-text">Pogledaj post</span>
        </div>
      </a>

      <!-- Post 2 -->
      <a href="https://instagram.com/doorexpert.me" target="_blank" rel="noopener" class="instagram-item">
        <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=400&q=75" alt="Door Expert Instagram" loading="lazy">
        <div class="instagram-item__overlay">
          <svg class="instagram-item__overlay-icon" viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="white" stroke="none"/></svg>
          <span class="instagram-item__overlay-text">Pogledaj post</span>
        </div>
      </a>

      <!-- Post 3 -->
      <a href="https://instagram.com/doorexpert.me" target="_blank" rel="noopener" class="instagram-item">
        <img src="https://images.unsplash.com/photo-1600585154526-990dced4db0d?w=400&q=75" alt="Door Expert Instagram" loading="lazy">
        <div class="instagram-item__overlay">
          <svg class="instagram-item__overlay-icon" viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="white" stroke="none"/></svg>
          <span class="instagram-item__overlay-text">Pogledaj post</span>
        </div>
      </a>

      <!-- Post 4 -->
      <a href="https://instagram.com/doorexpert.me" target="_blank" rel="noopener" class="instagram-item">
        <img src="https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?w=400&q=75" alt="Door Expert Instagram" loading="lazy">
        <div class="instagram-item__overlay">
          <svg class="instagram-item__overlay-icon" viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="white" stroke="none"/></svg>
          <span class="instagram-item__overlay-text">Pogledaj post</span>
        </div>
      </a>

      <!-- Post 5 -->
      <a href="https://instagram.com/doorexpert.me" target="_blank" rel="noopener" class="instagram-item">
        <img src="https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=400&q=75" alt="Door Expert Instagram" loading="lazy">
        <div class="instagram-item__overlay">
          <svg class="instagram-item__overlay-icon" viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="white" stroke="none"/></svg>
          <span class="instagram-item__overlay-text">Pogledaj post</span>
        </div>
      </a>

      <!-- Post 6 -->
      <a href="https://instagram.com/doorexpert.me" target="_blank" rel="noopener" class="instagram-item">
        <img src="https://images.unsplash.com/photo-1584622650111-993a426fbf0a?w=400&q=75" alt="Door Expert Instagram" loading="lazy">
        <div class="instagram-item__overlay">
          <svg class="instagram-item__overlay-icon" viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="white" stroke="none"/></svg>
          <span class="instagram-item__overlay-text">Pogledaj post</span>
        </div>
      </a>

      <!-- Post 7 -->
      <a href="https://instagram.com/doorexpert.me" target="_blank" rel="noopener" class="instagram-item">
        <img src="https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=400&q=75" alt="Door Expert Instagram" loading="lazy">
        <div class="instagram-item__overlay">
          <svg class="instagram-item__overlay-icon" viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="white" stroke="none"/></svg>
          <span class="instagram-item__overlay-text">Pogledaj post</span>
        </div>
      </a>

      <!-- Post 8 -->
      <a href="https://instagram.com/doorexpert.me" target="_blank" rel="noopener" class="instagram-item">
        <img src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400&q=75" alt="Door Expert Instagram" loading="lazy">
        <div class="instagram-item__overlay">
          <svg class="instagram-item__overlay-icon" viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="white" stroke="none"/></svg>
          <span class="instagram-item__overlay-text">Pogledaj post</span>
        </div>
      </a>

    </div><!-- /instagram-grid -->

    <!-- Developer handoff note -->
    <div class="instagram-dev-note">
      <strong>Napomena za developera:</strong> Ovaj grid je placeholder. U produkciji zamijeniti sa 
      <a href="https://behold.so" target="_blank">Behold.so</a> widgetom koji automatski vuce najnovije postove 
      sa @doorexpert.me Instagram profila. Besplatni plan pokriva potrebe. 
      Ubaciti: <code>&lt;div id="behold-widget-XXXX"&gt;&lt;/div&gt;</code> i odgovarajuci script tag.
    </div>

  </div><!-- /instagram-section__inner -->
</section>
<!-- /instagram-section -->

<!-- ════════════════════════════════════════════════
     PRE-FOOTER CTA STRIP
     Research: Conversion Strategy – conversion priority:
     (1) quote inquiry, (2) phone call, (3) showroom visit
════════════════════════════════════════════════ -->
<section class="pre-footer" aria-label="Poziv na akciju">
  <div class="pre-footer__inner">
    <div class="pre-footer__text">
      <span class="pre-footer__eyebrow">Door Expert · Podgorica</span>
      <h2 class="pre-footer__title">Posjetite nas ili zatražite ponudu</h2>
      <p class="pre-footer__subtitle">Roba na stanju. Formalna ponuda mejlom – bez obaveze.</p>
    </div>
    <div class="pre-footer__actions">
      <a href="<?php echo esc_url( home_url( '/korpa/' ) ); ?>" class="pre-footer__btn pre-footer__btn--primary">
        <svg viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
        Zatražite ponudu
      </a>
      <a href="tel:+38269234888" class="pre-footer__btn pre-footer__btn--secondary">
        <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.6 1.22h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.8a16 16 0 0 0 6.29 6.29l.95-.95a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
        Pozovite nas
      </a>
    </div>
  </div>
</section>

<?php
get_footer();
