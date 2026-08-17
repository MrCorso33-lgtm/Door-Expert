<?php
/**
 * Stranica: O nama – verna konverzija prototipa o-nama.html (<main>: linije 655–1170).
 *
 * Sekcije: breadcrumb → hero (split) → priča/timeline → Bologna break → vrijednosti →
 *          brendovi → trust → pravni podaci → tim → lokacija/mapa → pre-footer CTA.
 * Header/footer su globalni (header.php/footer.php); pre-footer CTA je per-page (ovdje).
 *
 * Popravke u odnosu na prototip:
 *  - Linkovi: hardkod .html → home_url()/door_expert_cat_url() (dinamički, prate WP baze).
 *  - Slike: /manus-storage/*.png placeholderi ne postoje → Unsplash fallback kao src.
 *    TODO: zamijeniti stvarnim fotografijama showrooma/sajma iz WP medija.
 *  - ?brend= filter na kategoriji nije implementiran → link vodi na kategoriju (TODO filter).
 *
 * $args: post_id, content (za sad prazno – proza je statična ovdje).
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!-- BREADCRUMB -->
<nav class="onama-breadcrumb" aria-label="Breadcrumb">
  <div class="onama-breadcrumb__inner">
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="onama-breadcrumb__link">Početna</a>
    <span class="onama-breadcrumb__sep">–</span>
    <span class="onama-breadcrumb__current">O nama</span>
  </div>
</nav>

<!-- HERO – SPLIT SCREEN -->
<section class="onama-hero">
  <div class="onama-hero__image-col">
    <img
      src="https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=1200&q=80"
      alt="Door Expert showroom u Podgorici"
      class="onama-hero__img"
    />
    <div class="onama-hero__image-overlay"></div>
    <!-- Floating stat card -->
    <div class="onama-hero__stat-card">
      <div class="onama-hero__stat">
        <span class="onama-hero__stat-value">AAA</span>
        <span class="onama-hero__stat-label">Bonitetna ocjena 2025</span>
      </div>
      <div class="onama-hero__stat-divider"></div>
      <div class="onama-hero__stat">
        <span class="onama-hero__stat-value">11K+</span>
        <span class="onama-hero__stat-label">Pratilaca na Instagramu</span>
      </div>
    </div>
  </div>
  <div class="onama-hero__content-col">
    <p class="onama-hero__eyebrow">Door Expert · Podgorica, Crna Gora</p>
    <h1 class="onama-hero__title">Uvoznik i distributer<br/><em>premijum vrata i keramike</em></h1>
    <p class="onama-hero__lead">
      Nismo fabrika. Nismo posrednici. Door Expert je direktni uvoznik i distributer –
      što znači da između vas i proizvođača stoji samo naš showroom u Podgorici.
    </p>
    <div class="onama-hero__stats-row">
      <div class="onama-hero__stat-item">
        <span class="onama-hero__stat-num">4</span>
        <span class="onama-hero__stat-desc">kategorije proizvoda</span>
      </div>
      <div class="onama-hero__stat-item">
        <span class="onama-hero__stat-num">0</span>
        <span class="onama-hero__stat-desc">dana čekanja na isporuku</span>
      </div>
      <div class="onama-hero__stat-item">
        <span class="onama-hero__stat-num">3</span>
        <span class="onama-hero__stat-desc">zaposlena stručnjaka</span>
      </div>
    </div>
    <div class="onama-hero__actions">
      <a href="<?php echo esc_url( home_url( '/kontakt/' ) ); ?>" class="onama-btn onama-btn--primary">Posjetite showroom</a>
      <a href="<?php echo esc_url( home_url( '/korpa/' ) ); ?>" class="onama-btn onama-btn--secondary">Zatražite ponudu</a>
    </div>
  </div>
</section>

<!-- NAŠA PRIČA -->
<section class="onama-story">
  <div class="onama-story__inner">
    <div class="onama-story__text-col">
      <p class="onama-section-eyebrow">Naša priča</p>
      <h2 class="onama-section-title">Pokrenuti iz uvjerenja da Crna Gora zaslužuje bolji izbor</h2>
      <p class="onama-story__para">
        Door Expert je osnovan u avgustu 2023. godine sa jednom jasnom idejom: kupci u Crnoj Gori
        ne bi trebalo da čekaju 45 dana na vrata koja su dostupna odmah – niti da biraju između
        malog broja modela koji su slučajno stigli u luku.
      </p>
      <p class="onama-story__para">
        Kao direktni uvoznik, sami biramo šta ulazi u naš asortiman. Svaki model sobnih i
        sigurnosnih vrata je odabran po kriterijumima dizajna, kvaliteta izrade i tražnje
        crnogorskog tržišta. Svaki španski brend keramike i svaki Bathco umivaonik prošao je
        ličnu selekciju na sajmu <strong>Cersaie u Bolonji</strong> – najvećem svjetskom sajmu
        keramike i kupatilske opreme.
      </p>
      <p class="onama-story__para">
        Ono što nas razlikuje nije samo asortiman – to je <strong>roba na stanju</strong>.
        Dok konkurencija naručuje po zahtjevu i isporučuje za 45 dana, naš showroom u
        Podgorici ima sve modele fizički dostupne za pregled i odmah za isporuku.
      </p>
    </div>
    <div class="onama-story__visual-col">
      <div class="onama-story__timeline">
        <div class="onama-story__timeline-item">
          <div class="onama-story__timeline-dot"></div>
          <div class="onama-story__timeline-content">
            <span class="onama-story__timeline-year">August 2023</span>
            <h3 class="onama-story__timeline-title">Osnivanje Door Expert DOO</h3>
            <p class="onama-story__timeline-desc">Registracija kompanije, otvaranje showrooma na adresi 4. jul 74/6, Podgorica.</p>
          </div>
        </div>
        <div class="onama-story__timeline-item">
          <div class="onama-story__timeline-dot"></div>
          <div class="onama-story__timeline-content">
            <span class="onama-story__timeline-year">Oktobar 2023</span>
            <h3 class="onama-story__timeline-title">Prva isporuka i PDV registracija</h3>
            <p class="onama-story__timeline-desc">Otvaranje bankovnih računa, PDV registracija, prve isporuke kupcima.</p>
          </div>
        </div>
        <div class="onama-story__timeline-item">
          <div class="onama-story__timeline-dot"></div>
          <div class="onama-story__timeline-content">
            <span class="onama-story__timeline-year">2024</span>
            <h3 class="onama-story__timeline-title">Cersaie Bologna – direktan kontakt s dobavljačima</h3>
            <p class="onama-story__timeline-desc">Prisustvo na najvećem svjetskom sajmu keramike. Proširenje asortimana španskih brendova i Bathco umivaonika.</p>
          </div>
        </div>
        <div class="onama-story__timeline-item">
          <div class="onama-story__timeline-dot onama-story__timeline-dot--active"></div>
          <div class="onama-story__timeline-content">
            <span class="onama-story__timeline-year">2025</span>
            <h3 class="onama-story__timeline-title">AAA bonitetna ocjena · Cersaie Bologna 2025</h3>
            <p class="onama-story__timeline-desc">Kompanija dobija AAA bonitetnu ocjenu – maksimalnu sigurnost poslovanja. Drugi put na Cersaie sajmu.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- BOLOGNA SAJAM – VISUAL BREAK -->
<section class="onama-bologna">
  <div class="onama-bologna__image-wrap">
    <img
      src="https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=1400&q=80"
      alt="Cersaie Bologna 2024 – Door Expert na sajmu"
      class="onama-bologna__img"
    />
    <div class="onama-bologna__overlay"></div>
    <div class="onama-bologna__content">
      <p class="onama-bologna__eyebrow">Cersaie · Bologna, Italija</p>
      <h2 class="onama-bologna__title">Direktno sa najvećeg<br/>sajma keramike na svijetu</h2>
      <p class="onama-bologna__desc">
        Svake godine posjećujemo Cersaie – međunarodni sajam keramike i kupatilske opreme u Bolonji.
        Tamo biramo kolekcije, pregovaramo direktno s proizvođačima i donosimo u Crnu Goru
        ono što je aktualno u evropskom dizajnu.
      </p>
      <div class="onama-bologna__badges">
        <span class="onama-bologna__badge">Bologna 2024 ✓</span>
        <span class="onama-bologna__badge">Bologna 2025 ✓</span>
      </div>
    </div>
  </div>
</section>

<!-- ŠTA NAS ČINI DRUGAČIJIM -->
<section class="onama-values">
  <div class="onama-values__inner">
    <div class="onama-values__header">
      <p class="onama-section-eyebrow">Naše vrijednosti</p>
      <h2 class="onama-section-title">Šta nas čini drugačijim</h2>
    </div>
    <div class="onama-values__grid">
      <div class="onama-values__card">
        <div class="onama-values__icon">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
        </div>
        <h3 class="onama-values__title">Isporuka odmah</h3>
        <p class="onama-values__desc">
          Sva vrata su na stanju u Podgorici. Nema čekanja 45 dana kao kod konkurencije.
          Kupite danas – dogovorite isporuku sutra.
        </p>
      </div>
      <div class="onama-values__card">
        <div class="onama-values__icon">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
        </div>
        <h3 class="onama-values__title">Direktni uvoznik</h3>
        <p class="onama-values__desc">
          Nismo posrednici. Uvozimo direktno od proizvođača – što znači bolje cijene,
          potpunu dokumentaciju i direktan kontakt za reklamacije.
        </p>
      </div>
      <div class="onama-values__card">
        <div class="onama-values__icon">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        </div>
        <h3 class="onama-values__title">AAA bonitetna ocjena</h3>
        <p class="onama-values__desc">
          Bonitetna ocjena AAA za 2025. godinu – maksimalna sigurnost poslovanja.
          Bez blokade računa, bez poreskog duga, transparentno finansijsko poslovanje.
        </p>
      </div>
      <div class="onama-values__card">
        <div class="onama-values__icon">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
        </div>
        <h3 class="onama-values__title">Lični pristup</h3>
        <p class="onama-values__desc">
          Tim od 3 stručnjaka. Svaki kupac dobija individualan savjet – od izbora modela
          do koordinacije montaže. Nema call centra, nema čekanja.
        </p>
      </div>
      <div class="onama-values__card">
        <div class="onama-values__icon">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
        </div>
        <h3 class="onama-values__title">Showroom za pregled</h3>
        <p class="onama-values__desc">
          Sve što prodajemo možete fizički vidjeti i dotaknuti u showroomu.
          Uzorci keramike, modeli vrata, Bathco umivaonici – sve na jednom mjestu.
        </p>
      </div>
      <div class="onama-values__card">
        <div class="onama-values__icon">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        </div>
        <h3 class="onama-values__title">Ekskluzivni distributer</h3>
        <p class="onama-values__desc">
          Jedini ovlašćeni distributer Bathco umivaonika za Crnu Goru.
          Direktna veza s brendom, originalni proizvodi, garancija.
        </p>
      </div>
    </div>
  </div>
</section>

<!-- BRENDOVI KOJE ZASTUPAMO -->
<section class="onama-brands">
  <div class="onama-brands__inner">
    <div class="onama-brands__header">
      <p class="onama-section-eyebrow">Naši partneri</p>
      <h2 class="onama-section-title">Brendovi koje zastupamo</h2>
      <p class="onama-brands__subtitle">Pažljivo odabrani portfolio španskih brendova – direktan uvoz, bez posrednika.</p>
    </div>
    <div class="onama-brands__grid">
      <div class="onama-brands__card">
        <div class="onama-brands__card-flag">🇪🇸</div>
        <h3 class="onama-brands__card-name">Tau Ceramica</h3>
        <p class="onama-brands__card-origin">Španija · od 1975.</p>
        <p class="onama-brands__card-desc">Jedan od vodećih španskih proizvođača keramike. Specijalizovani za veliki format i premium kolekcije za podove i zidove.</p>
        <a href="<?php echo esc_url( door_expert_cat_url( 'keramicke-plocice' ) ); ?>" class="onama-brands__card-link">Pogledaj kolekciju →</a>
      </div>
      <div class="onama-brands__card">
        <div class="onama-brands__card-flag">🇪🇸</div>
        <h3 class="onama-brands__card-name">Arcana Ceramica</h3>
        <p class="onama-brands__card-origin">Španija · Castellón</p>
        <p class="onama-brands__card-desc">Dizajnerska keramika iz srca španskog keramičkog pojasa. Poznati po inovativnim dekoru i premium kolekcijama za kupatila.</p>
        <a href="<?php echo esc_url( door_expert_cat_url( 'keramicke-plocice' ) ); ?>" class="onama-brands__card-link">Pogledaj kolekciju →</a>
      </div>
      <div class="onama-brands__card">
        <div class="onama-brands__card-flag">🇪🇸</div>
        <h3 class="onama-brands__card-name">New Tiles</h3>
        <p class="onama-brands__card-origin">Španija · Valencia</p>
        <p class="onama-brands__card-desc">Moderna španska keramika sa naglaskom na savremeni dizajn i funkcionalnost. Idealni za kupatila, kuhinje i dnevne prostore.</p>
        <a href="<?php echo esc_url( door_expert_cat_url( 'keramicke-plocice' ) ); ?>" class="onama-brands__card-link">Pogledaj kolekciju →</a>
      </div>
      <div class="onama-brands__card">
        <div class="onama-brands__card-flag">🇪🇸</div>
        <h3 class="onama-brands__card-name">Ceramica Ribesalbes</h3>
        <p class="onama-brands__card-origin">Španija · Ribesalbes</p>
        <p class="onama-brands__card-desc">Artizanska keramika iz mjesta koje je sinonim za kvalitetnu keramičku tradiciju. Ručno rađeni dekorativni elementi i premium kolekcije.</p>
        <a href="<?php echo esc_url( door_expert_cat_url( 'keramicke-plocice' ) ); ?>" class="onama-brands__card-link">Pogledaj kolekciju →</a>
      </div>
      <div class="onama-brands__card onama-brands__card--featured">
        <div class="onama-brands__card-flag">🇪🇸</div>
        <div class="onama-brands__card-exclusive">Ekskluzivni distributer za CG</div>
        <h3 class="onama-brands__card-name">Bathco</h3>
        <p class="onama-brands__card-origin">Španija · premium umivaonici</p>
        <p class="onama-brands__card-desc">Jedini ovlašćeni distributer Bathco umivaonika za Crnu Goru. Dekorativni umivaonici od porculana, kompozita i prirodnog kamena.</p>
        <a href="<?php echo esc_url( door_expert_cat_url( 'umivaonici' ) ); ?>" class="onama-brands__card-link">Pogledaj kolekciju →</a>
      </div>
    </div>
  </div>
</section>

<!-- TRUST / KREDIBILITET -->
<section class="onama-trust">
  <div class="onama-trust__inner">
    <div class="onama-trust__header">
      <p class="onama-section-eyebrow">Kredibilitet</p>
      <h2 class="onama-section-title">Zašto nam vjeruju</h2>
      <p class="onama-trust__subtitle">Za B2B partnere, arhitekte i investitore – zvanični podaci o kompaniji.</p>
    </div>
    <div class="onama-trust__cards">
      <div class="onama-trust__card onama-trust__card--aaa">
        <div class="onama-trust__card-badge">AAA</div>
        <h3 class="onama-trust__card-title">Bonitet AAA · 2025</h3>
        <p class="onama-trust__card-desc">Sertifikat bonitetne izvrsnosti potvrđuje maksimalnu finansijsku stabilnost i nizak poslovni rizik. <strong>Prime ocjena</strong> – najviša kategorija.</p>
      </div>
      <div class="onama-trust__card">
        <div class="onama-trust__card-icon">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
        </div>
        <h3 class="onama-trust__card-title">Registrovani uvoznik</h3>
        <p class="onama-trust__card-desc">Direktan uvoz od evropskih proizvođača, bez posrednika, sa kompletnom carinskom dokumentacijom i PDV računima.</p>
      </div>
      <div class="onama-trust__card">
        <div class="onama-trust__card-icon">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
        </div>
        <h3 class="onama-trust__card-title">Aktivni bankovni računi</h3>
        <p class="onama-trust__card-desc">3 aktivna bankovska računa od oktobra 2023. Transparentno finansijsko poslovanje – bez blokada i poreskog duga.</p>
      </div>
      <div class="onama-trust__card">
        <div class="onama-trust__card-icon">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <h3 class="onama-trust__card-title">PDV obveznik</h3>
        <p class="onama-trust__card-desc">Registrovani PDV obveznik od septembra 2023. Svi računi sa PDV-om, kompletna dokumentacija za B2B partnere.</p>
      </div>
    </div>
  </div>
</section>

<!-- PRAVNI PODACI – TABELA -->
<section class="onama-legal">
  <div class="onama-legal__inner">
    <div class="onama-legal__header">
      <p class="onama-section-eyebrow">Pravne informacije</p>
      <h2 class="onama-section-title">Podaci o kompaniji</h2>
      <p class="onama-legal__subtitle">Zvanični podaci za poslovne partnere, investitore i B2B klijente koji zahtijevaju dokumentaciju za ugovaranje saradnje.</p>
    </div>
    <div class="onama-legal__tables">
      <div class="onama-legal__table-block">
        <h3 class="onama-legal__table-title">Registarski podaci</h3>
        <table class="onama-legal__table">
          <tbody>
            <tr>
              <td class="onama-legal__table-key">Puno ime</td>
              <td class="onama-legal__table-val">DOOR EXPERT DOO</td>
            </tr>
            <tr>
              <td class="onama-legal__table-key">PIB</td>
              <td class="onama-legal__table-val">03593371</td>
            </tr>
            <tr>
              <td class="onama-legal__table-key">Registracioni broj</td>
              <td class="onama-legal__table-val">5-1172064/001</td>
            </tr>
            <tr>
              <td class="onama-legal__table-key">Datum osnivanja</td>
              <td class="onama-legal__table-val">25. august 2023.</td>
            </tr>
            <tr>
              <td class="onama-legal__table-key">Status</td>
              <td class="onama-legal__table-val"><span class="onama-legal__status">Aktivan</span></td>
            </tr>
            <tr>
              <td class="onama-legal__table-key">Bonitet 2025</td>
              <td class="onama-legal__table-val"><span class="onama-legal__aaa">AAA – Maksimalna sigurnost</span></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="onama-legal__table-block">
        <h3 class="onama-legal__table-title">Poslovni podaci</h3>
        <table class="onama-legal__table">
          <tbody>
            <tr>
              <td class="onama-legal__table-key">Delatnost</td>
              <td class="onama-legal__table-val">4759 – Trgovina na malo namještajem i opremom za domaćinstvo</td>
            </tr>
            <tr>
              <td class="onama-legal__table-key">Adresa showrooma</td>
              <td class="onama-legal__table-val">4. jul 74/6, 81110 Podgorica, Crna Gora</td>
            </tr>
            <tr>
              <td class="onama-legal__table-key">Direktor</td>
              <td class="onama-legal__table-val">Sanja Bubanja</td>
            </tr>
            <tr>
              <td class="onama-legal__table-key">PDV registracija</td>
              <td class="onama-legal__table-val">DA · 30/31-26548-6 (od 25.09.2023)</td>
            </tr>
            <tr>
              <td class="onama-legal__table-key">Broj zaposlenih</td>
              <td class="onama-legal__table-val">3</td>
            </tr>
            <tr>
              <td class="onama-legal__table-key">Blokada računa</td>
              <td class="onama-legal__table-val"><span class="onama-legal__status">Nije blokirana</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>

<!-- TIM -->
<section class="onama-team">
  <div class="onama-team__inner">
    <div class="onama-team__header">
      <p class="onama-section-eyebrow">Naš tim</p>
      <h2 class="onama-section-title">Tri osobe. Jedan showroom.<br/><em>Sva četiri kategorije.</em></h2>
      <p class="onama-team__subtitle">
        Nismo korporacija sa call centrom. Svaki kupac razgovara direktno sa osobom koja
        poznaje asortiman i može dati konkretan savjet.
      </p>
    </div>
    <div class="onama-team__grid">
      <div class="onama-team__card">
        <div class="onama-team__avatar">
          <div class="onama-team__avatar-placeholder">PB</div>
        </div>
        <h3 class="onama-team__name">Predrag Bubanja</h3>
        <p class="onama-team__role">Suvlasnik · Nabavka i logistika</p>
        <p class="onama-team__desc">Odgovoran za direktne odnose s dobavljačima, uvoz i upravljanje zalihama. Prisustvo na Cersaie sajmu u Bolonji.</p>
      </div>
      <div class="onama-team__card">
        <div class="onama-team__avatar">
          <div class="onama-team__avatar-placeholder">AB</div>
        </div>
        <h3 class="onama-team__name">Aleksandra Bubanja</h3>
        <p class="onama-team__role">Suvlasnica · Marketing i prodaja</p>
        <p class="onama-team__desc">Vodi Instagram profil (@door_expert_) i direktnu komunikaciju s kupcima. 11.200+ pratilaca, 343 objave o asortimanu i inspiraciji.</p>
      </div>
      <div class="onama-team__card">
        <div class="onama-team__avatar">
          <div class="onama-team__avatar-placeholder">SB</div>
        </div>
        <h3 class="onama-team__name">Sanja Bubanja</h3>
        <p class="onama-team__role">Direktorica · Showroom i savjetovanje</p>
        <p class="onama-team__desc">Vodi showroom i savjetuje kupce pri odabiru. Stručnjak za kombinovanje vrata, keramike i umivaonika u jedinstven enterijer.</p>
      </div>
    </div>
  </div>
</section>

<!-- SHOWROOM / LOKACIJA -->
<section class="onama-location">
  <div class="onama-location__inner">
    <div class="onama-location__content">
      <p class="onama-section-eyebrow">Posjetite nas</p>
      <h2 class="onama-section-title">Showroom u Podgorici</h2>
      <p class="onama-location__desc">
        Sve što prodajemo možete fizički vidjeti i dotaknuti. Uzorci svih keramičkih kolekcija,
        modeli vrata u prirodnoj veličini, Bathco umivaonici – sve na jednom mjestu.
      </p>
      <div class="onama-location__details">
        <div class="onama-location__detail-item">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
          <div>
            <strong>Adresa</strong>
            <span>4. jul 74/6, 81110 Podgorica, Crna Gora</span>
          </div>
        </div>
        <div class="onama-location__detail-item">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          <div>
            <strong>Radno vrijeme</strong>
            <span>Pon–Pet: 10:00–18:00 · Sub: 10:00–14:00 · Ned: Neradni dan</span>
          </div>
        </div>
        <div class="onama-location__detail-item">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.67A2 2 0 012 .84h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 8.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
          <div>
            <strong>Telefon</strong>
            <a href="tel:+38269234888">+382 69 234 888</a> · <a href="tel:+38269234889">+382 69 234 889</a>
          </div>
        </div>
        <div class="onama-location__detail-item">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          <div>
            <strong>Email</strong>
            <a href="mailto:office@doorexpert.me">office@doorexpert.me</a>
          </div>
        </div>
      </div>
      <div class="onama-location__actions">
        <a href="<?php echo esc_url( home_url( '/kontakt/' ) ); ?>" class="onama-btn onama-btn--primary">Pogledaj na mapi</a>
        <a href="tel:+38269234888" class="onama-btn onama-btn--outline">Pozovite nas</a>
      </div>
    </div>
    <div class="onama-location__map">
      <div class="onama-location__map-placeholder">
        <div class="onama-location__map-grid"></div>
        <div class="onama-location__map-pin">
          <svg width="32" height="40" viewBox="0 0 32 40" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M16 0C7.16 0 0 7.16 0 16c0 11.31 16 24 16 24s16-12.69 16-24C32 7.16 24.84 0 16 0z" fill="#A07840"/>
            <circle cx="16" cy="16" r="6" fill="white"/>
          </svg>
          <span class="onama-location__map-label">Door Expert</span>
        </div>
        <p class="onama-location__map-note">4. jul 74/6, Podgorica</p>
      </div>
    </div>
  </div>
</section>

<!-- PRE-FOOTER CTA -->
<section class="pre-footer-cta">
  <div class="pre-footer-cta__inner">
    <p class="pre-footer-cta__eyebrow">Door Expert · Podgorica</p>
    <h2 class="pre-footer-cta__title">Posjetite nas ili <em>zatražite ponudu</em></h2>
    <p class="pre-footer-cta__desc">Showroom u Podgorici. Uzorci svih kolekcija dostupni za pregled. Savjetovanje bez obaveze.</p>
    <div class="pre-footer-cta__actions">
      <a href="<?php echo esc_url( home_url( '/korpa/' ) ); ?>" class="pre-footer-cta__btn pre-footer-cta__btn--primary">Zatražite ponudu</a>
      <a href="<?php echo esc_url( home_url( '/kontakt/' ) ); ?>" class="pre-footer-cta__btn pre-footer-cta__btn--secondary">Lokacija i radno vrijeme</a>
    </div>
  </div>
</section>
