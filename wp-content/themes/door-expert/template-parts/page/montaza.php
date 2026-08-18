<?php
/**
 * Stranica: Montaza ("Sta je ukljuceno u cijenu") - verna konverzija prototipa montaza.html.
 *
 * Sekcije: hero -> clarity (ukljuceno/nije) -> process (6 koraka) -> installers (3 majstora) ->
 *          pricing tabela -> FAQ -> reviews -> CTA -> pre-footer.
 * CSS: assets/css/montaza.css (izvucen iz inline <style>, mobile-first). .pre-footer iz footer.css.
 *
 * Popravke: korpa.html -> home_url('/korpa/'); company telefon -> 234 888 (majstori ostaju
 * placeholder brojevi); em-dash -> en-dash; tabela u .mont-price-wrap (mobilni scroll).
 * NAPOMENA: majstori i cijene su placeholder (demo). Prave info idu kroz produkciju.
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!-- HERO -->
<section class="mont-hero">
  <div class="mont-hero__inner">
    <p class="mont-hero__eyebrow">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      Transparentnost je naša politika
    </p>
    <h1 class="mont-hero__title">
      Šta je uključeno<br>
      u cijenu – i <em>šta nije</em>
    </h1>
    <p class="mont-hero__lead">
      Door Expert je uvoznik i distributer vrata, keramike i umivaonika. Prodajemo proizvode – montažu vrše nezavisni majstori. Ova stranica postoji da ne bude nikakve zabune.
    </p>
    <div class="mont-hero__notice">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
      <p><strong>Zašto ova stranica postoji?</strong> Neke Google recenzije kritikuju montažu – ali montažeri nisu naši zaposlenici. Ovo je naš odgovor: jasno, unaprijed, bez sitnog slova.</p>
    </div>
  </div>
</section>

<!-- CLARITY -->
<section class="mont-clarity">
  <div class="mont-clarity__inner">
    <div class="mont-clarity__header">
      <p class="mont-clarity__eyebrow">Transparentnost cijena</p>
      <h2 class="mont-clarity__title">Šta dobijate za cijenu na sajtu</h2>
    </div>
    <div class="mont-clarity__grid">
      <!-- Uključeno -->
      <div class="mont-clarity-card mont-clarity-card--yes">
        <div class="mont-clarity-card__icon">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <h3 class="mont-clarity-card__title">✓ Cijena uključuje</h3>
        <ul class="mont-clarity-card__list">
          <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Vrata sa štok-okvirom (komplet)</li>
          <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Kvaka i šarke po standardnoj specifikaciji</li>
          <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Roba na stanju – odmah dostupna</li>
          <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Formalna ponuda mejlom sa PDV-om</li>
          <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Savjet pri odabiru modela i dimenzija</li>
          <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Garancija na proizvod (2 godine)</li>
          <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Preporuka provjerenih majstora</li>
        </ul>
      </div>
      <!-- Nije uključeno -->
      <div class="mont-clarity-card mont-clarity-card--no">
        <div class="mont-clarity-card__icon">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </div>
        <h3 class="mont-clarity-card__title">✗ Cijena ne uključuje</h3>
        <ul class="mont-clarity-card__list">
          <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> Montaža vrata (plaća se majstoru direktno)</li>
          <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> Transport do objekta</li>
          <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> Adaptacija zidnog otvora (ako je potrebna)</li>
          <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> Demontaža starih vrata</li>
          <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> Gletovanje i farbanje zidova oko okvira</li>
          <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> Dodatna brava ili nestandardna kvaka</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- PROCESS FLOW -->
<section class="mont-process">
  <div class="mont-process__inner">
    <div class="mont-process__header">
      <p class="mont-process__eyebrow">Korak po korak</p>
      <h2 class="mont-process__title">Kako teče kupovina i montaža</h2>
      <p class="mont-process__desc">Od prvog klika do ugrađenih vrata – svaki korak je jasan, i svaki korak znate unaprijed.</p>
    </div>

    <div class="mont-steps">
      <div class="mont-step">
        <div class="mont-step__num">1</div>
        <div class="mont-step__body">
          <span class="mont-step__who mont-step__who--kupac">Vi</span>
          <h3 class="mont-step__title">Odaberete model i dodate u ponudu</h3>
          <p class="mont-step__desc">Pregledate katalog, odaberete model, dimenziju i boju. Kliknete "Dodaj u ponudu" – bez obaveze, bez plaćanja. Možete dodati više proizvoda odjednom.</p>
        </div>
      </div>

      <div class="mont-step">
        <div class="mont-step__num">2</div>
        <div class="mont-step__body">
          <span class="mont-step__who mont-step__who--kupac">Vi</span>
          <h3 class="mont-step__title">Pošaljete upit sa kontakt podacima</h3>
          <p class="mont-step__desc">Unesete ime, email i telefon. Možete dodati napomenu (npr. "trebam za stan u izgradnji, useljenje u martu"). Kliknete "Pošalji upit za ponudu".</p>
          <div class="mont-step__note">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Ovo nije narudžba. Nema plaćanja. Samo šaljete zahtjev za formalnu ponudu.
          </div>
        </div>
      </div>

      <div class="mont-step mont-step--highlight">
        <div class="mont-step__num">3</div>
        <div class="mont-step__body">
          <span class="mont-step__who mont-step__who--de">Door Expert</span>
          <h3 class="mont-step__title">Šaljemo formalnu ponudu mejlom</h3>
          <p class="mont-step__desc">U roku od 24 sata (radnim danom) dobijate mejl sa predračunom. Cijena u mejlu je konačna cijena proizvoda – bez skrivenih stavki. Ponuda važi 7 dana.</p>
          <div class="mont-step__note">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Ponuda ne uključuje montažu – to je posebna stavka koju dogovarate direktno sa majstorom.
          </div>
        </div>
      </div>

      <div class="mont-step">
        <div class="mont-step__num">4</div>
        <div class="mont-step__body">
          <span class="mont-step__who mont-step__who--kupac">Vi</span>
          <h3 class="mont-step__title">Potvrdite i platite robu</h3>
          <p class="mont-step__desc">Prihvatate ponudu i plaćate robu (gotovina, kartica, virman ili rate). Roba je na stanju – odmah rezervisana za vas. Nema čekanja 45 dana kao kod konkurencije.</p>
        </div>
      </div>

      <div class="mont-step">
        <div class="mont-step__num">5</div>
        <div class="mont-step__body">
          <span class="mont-step__who mont-step__who--kupac">Vi</span>
          <h3 class="mont-step__title">Angažujete majstora za montažu</h3>
          <p class="mont-step__desc">Kontaktirate jednog od naših preporučenih majstora (ili svog). Dogovarate termin direktno sa njim. Montaža traje 2–15 dana zavisno od broja vrata i dostupnosti majstora.</p>
          <div class="mont-step__note">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            Majstor naplaćuje direktno vama. Door Expert nije posrednik u plaćanju montaže.
          </div>
        </div>
      </div>

      <div class="mont-step">
        <div class="mont-step__num">6</div>
        <div class="mont-step__body">
          <span class="mont-step__who mont-step__who--majstor">Majstor</span>
          <h3 class="mont-step__title">Montaža i preuzimanje</h3>
          <p class="mont-step__desc">Majstor preuzima robu iz salona ili dostavlja na adresu, montira vrata i predaje vam gotov posao. Ako nešto nije u redu sa robom – tu smo mi. Ako nešto nije u redu sa montažom – to je između vas i majstora.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- INSTALLERS -->
<section class="mont-installers">
  <div class="mont-installers__inner">
    <div class="mont-installers__header">
      <p class="mont-installers__eyebrow">Preporučeni majstori</p>
      <h2 class="mont-installers__title">Majstori koje poznajemo</h2>
      <p class="mont-installers__desc">Ovo su majstori sa kojima naši kupci imaju dobre iskustvo. Cijene su fiksne i unaprijed poznate. Angažovanje je direktno između vas i majstora – Door Expert nije strana u tom ugovoru.</p>
    </div>

    <div class="mont-installers__grid">
      <!-- Majstor 1 -->
      <div class="mont-installer-card">
        <div class="mont-installer-card__avatar">M</div>
        <div class="mont-installer-card__name">Marko Vujović</div>
        <div class="mont-installer-card__spec">Montaža sobnih vrata</div>
        <ul class="mont-installer-card__prices">
          <li><span>Standardna vrata (kom)</span><strong>25–35 EUR</strong></li>
          <li><span>Klizna vrata (kom)</span><strong>35–50 EUR</strong></li>
          <li><span>Demontaža starih vrata</span><strong>10 EUR/kom</strong></li>
          <li><span>Transport (Podgorica)</span><strong>10–20 EUR</strong></li>
        </ul>
        <p class="mont-installer-card__disclaimer">* Cijene su orijentacione. Konačna cijena zavisi od stanja otvora i broja vrata.</p>
        <a href="tel:+38269000001" class="mont-installer-card__contact">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.67A2 2 0 012 .18h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
          +382 69 000 001
        </a>
      </div>

      <!-- Majstor 2 -->
      <div class="mont-installer-card">
        <div class="mont-installer-card__avatar">N</div>
        <div class="mont-installer-card__name">Nikola Perović</div>
        <div class="mont-installer-card__spec">Montaža sigurnosnih vrata</div>
        <ul class="mont-installer-card__prices">
          <li><span>Sigurnosna vrata (kom)</span><strong>60–80 EUR</strong></li>
          <li><span>Adaptacija otvora</span><strong>po dogovoru</strong></li>
          <li><span>Demontaža starih vrata</span><strong>15 EUR/kom</strong></li>
          <li><span>Transport (Podgorica)</span><strong>15–25 EUR</strong></li>
        </ul>
        <p class="mont-installer-card__disclaimer">* Specijalizovan za RC2 i RC3 sigurnosna vrata. Iskustvo 12+ godina.</p>
        <a href="tel:+38269000002" class="mont-installer-card__contact">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.67A2 2 0 012 .18h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
          +382 69 000 002
        </a>
      </div>

      <!-- Majstor 3 -->
      <div class="mont-installer-card">
        <div class="mont-installer-card__avatar">D</div>
        <div class="mont-installer-card__name">Dragan Milić</div>
        <div class="mont-installer-card__spec">Montaža keramike i umivaonika</div>
        <ul class="mont-installer-card__prices">
          <li><span>Polaganje keramike (m²)</span><strong>8–12 EUR/m²</strong></li>
          <li><span>Ugradnja umivaonika</span><strong>30–50 EUR</strong></li>
          <li><span>Fugovanje (m²)</span><strong>3–5 EUR/m²</strong></li>
          <li><span>Transport (Podgorica)</span><strong>10–20 EUR</strong></li>
        </ul>
        <p class="mont-installer-card__disclaimer">* Specijalizovan za Bathco nadgradne i kamene umivaonike.</p>
        <a href="tel:+38269000003" class="mont-installer-card__contact">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.67A2 2 0 012 .18h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
          +382 69 000 003
        </a>
      </div>
    </div>

    <div class="mont-installers__legal">
      <p><strong>Pravna napomena:</strong> Majstori navedeni na ovoj stranici su nezavisni izvođači radova. Door Expert daje preporuku na osnovu iskustva kupaca, ali nije strana u ugovoru između kupca i majstora. Sve reklamacije vezane za montažu rješavaju se direktno između kupca i majstora. Door Expert ne naplaćuje proviziju za preporuku.</p>
    </div>
  </div>
</section>

<!-- PRICING TABLE -->
<section class="mont-pricing">
  <div class="mont-pricing__inner">
    <div class="mont-pricing__header">
      <p class="mont-pricing__eyebrow">Orijentacione cijene</p>
      <h2 class="mont-pricing__title">Ukupni troškovi – primjer za stan</h2>
      <p class="mont-pricing__desc">Primjer: stan od 60m², 5 sobnih vrata + 1 sigurnosno. Sve cifre su orijentacione.</p>
    </div>

    <div class="mont-price-wrap">
      <table class="mont-price-table">
        <thead>
          <tr>
            <th>Stavka</th>
            <th>Ko naplaćuje</th>
            <th>Orijentaciona cijena</th>
            <th>Napomena</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>5× sobna vrata (model Orah)</td>
            <td>Door Expert</td>
            <td><strong>5 × 330 = 1.650 EUR</strong></td>
            <td>Cijena sa sajta, sa PDV-om</td>
          </tr>
          <tr>
            <td>1× sigurnosna vrata RC2</td>
            <td>Door Expert</td>
            <td><strong>559 EUR</strong></td>
            <td>Cijena sa sajta, sa PDV-om</td>
          </tr>
          <tr>
            <td>Montaža 5× sobnih vrata</td>
            <td>Majstor direktno</td>
            <td><strong>5 × 30 = 150 EUR</strong></td>
            <td>Plaćate majstoru, ne nama</td>
          </tr>
          <tr>
            <td>Montaža sigurnosnih vrata</td>
            <td>Majstor direktno</td>
            <td><strong>70 EUR</strong></td>
            <td>Plaćate majstoru, ne nama</td>
          </tr>
          <tr>
            <td>Transport (Podgorica)</td>
            <td>Majstor direktno</td>
            <td><strong>15 EUR</strong></td>
            <td>Plaćate majstoru, ne nama</td>
          </tr>
          <tr style="background: var(--color-pearl);">
            <td><strong>Ukupno (roba + montaža)</strong></td>
            <td>–</td>
            <td><strong>≈ 2.444 EUR</strong></td>
            <td>Orijentaciono, bez adaptacija</td>
          </tr>
        </tbody>
      </table>
    </div>
    <p class="mont-price-note">* Cijene su orijentacione i mogu varirati. Konačnu cijenu robe dobijate mejlom nakon upita. Konačnu cijenu montaže dogovarate direktno sa majstorom.</p>
  </div>
</section>

<!-- FAQ -->
<section class="mont-faq">
  <div class="mont-faq__inner">
    <div class="mont-faq__header">
      <p class="mont-faq__eyebrow">Česta pitanja</p>
      <h2 class="mont-faq__title">Pitanja o montaži</h2>
    </div>

    <div class="mont-faq__list">
      <details class="mont-faq__item">
        <summary>Je li montaža uključena u cijenu?<span class="mont-faq__icon">+</span></summary>
        <div class="mont-faq__answer">
          <p>Ne. Cijena na sajtu uključuje samo robu (vrata, keramiku, umivaonik). Montaža se plaća posebno, direktno majstoru koji je izvodi. Ovo je standardna praksa u svim prodavnicama građevinskog materijala.</p>
          <p>Razlog: montaža je zanat koji zahtijeva fizičku procjenu otvora, stanja zidova i specifičnih uslova na licu mjesta. Cijena montaže ne može biti fiksna jer zavisi od faktora koje ne možemo znati unaprijed.</p>
        </div>
      </details>

      <details class="mont-faq__item">
        <summary>Zašto negativne recenzije pominju montažu?<span class="mont-faq__icon">+</span></summary>
        <div class="mont-faq__answer">
          <p>Dio kupaca nije bio svjestan da montažeri nisu naši zaposlenici. Kad bi nešto pošlo naopako sa montažom, recenzija bi bila upućena nama – iako smo mi samo prodali robu.</p>
          <p>Ovo je naš odgovor na taj problem: ova stranica. Cilj je da svaki kupac zna tačno šta kupuje i od koga, prije nego što donese odluku.</p>
        </div>
      </details>

      <details class="mont-faq__item">
        <summary>Koliko traje montaža?<span class="mont-faq__icon">+</span></summary>
        <div class="mont-faq__answer">
          <p>Sama montaža jednih vrata traje 1–3 sata. Za stan od 5 vrata, iskusan majstor završi za jedan radni dan. Termin zavisi od dostupnosti majstora – obično 2–15 dana od narudžbe.</p>
          <p>Roba je na stanju odmah – jedino što čekate je termin majstora.</p>
        </div>
      </details>

      <details class="mont-faq__item">
        <summary>Mogu li koristiti svog majstora?<span class="mont-faq__icon">+</span></summary>
        <div class="mont-faq__answer">
          <p>Apsolutno. Naši preporučeni majstori su samo prijedlog – niste obavezni da ih koristite. Možete angažovati bilo kojeg majstora po vašem izboru.</p>
          <p>Jedino što preporučujemo: provjerite da li majstor ima iskustvo sa montažom sobnih/sigurnosnih vrata, jer pogrešna montaža može poništiti garanciju.</p>
        </div>
      </details>

      <details class="mont-faq__item">
        <summary>Šta ako su vrata oštećena pri isporuci?<span class="mont-faq__icon">+</span></summary>
        <div class="mont-faq__answer">
          <p>Sve reklamacije na robu rješavamo mi – Door Expert. Ako pri preuzimanju ili isporuci primijetite oštećenje, fotografišite i odmah nas kontaktirajte. Zamijenićemo robu bez troškova.</p>
          <p>Garancija na robu je 2 godine od datuma kupovine. Garancija ne pokriva oštećenja nastala nepravilnom montažom.</p>
        </div>
      </details>

      <details class="mont-faq__item">
        <summary>Šta ako majstor napravi štetu tokom montaže?<span class="mont-faq__icon">+</span></summary>
        <div class="mont-faq__answer">
          <p>Šteta nastala tokom montaže je odgovornost majstora, ne Door Experta. Preporučujemo da sa majstorom napravite pisani dogovor o obimu radova i cijeni prije početka montaže.</p>
          <p>Naši preporučeni majstori imaju dobru reputaciju i iskustvo – ali nismo u mogućnosti da garantujemo za njihov rad jer nisu naši zaposlenici.</p>
        </div>
      </details>

      <details class="mont-faq__item">
        <summary>Mogu li dobiti ponudu za montažu zajedno sa robom?<span class="mont-faq__icon">+</span></summary>
        <div class="mont-faq__answer">
          <p>Možemo vas uputiti na preporučenog majstora koji će vam dati svoju ponudu za montažu. Ove dvije ponude (naša za robu, majstorova za montažu) su odvojene i plaćaju se odvojeno.</p>
          <p>U napomeni pri slanju upita napišite "trebam i preporuku za montažu" – mi ćemo vas spojiti sa odgovarajućim majstorom.</p>
        </div>
      </details>
    </div>
  </div>
</section>

<!-- REVIEWS -->
<section class="mont-reviews">
  <div class="mont-reviews__inner">
    <div>
      <p class="mont-reviews__eyebrow">Šta kupci kažu</p>
      <h2 class="mont-reviews__title">Iskustva naših kupaca</h2>
      <p class="mont-reviews__desc">Naša prosječna ocjena na Google-u je 4.7★ na osnovu 80+ recenzija. Negativne recenzije uglavnom se odnose na montažu – upravo zato postoji ova stranica.</p>
      <a href="https://g.page/door-expert-podgorica" target="_blank" rel="noopener" class="mont-reviews__cta">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
        Pogledajte sve Google recenzije
      </a>
    </div>
    <div class="mont-review-card">
      <div class="mont-review-card__stars">★★★★★</div>
      <p class="mont-review-card__text">"Vrata su odlična, tačno onakva kakva su prikazana na sajtu. Majstor kojeg su preporučili je bio profesionalan i završio sve za jedan dan. Preporučujem."</p>
      <div class="mont-review-card__author">M.K. – Podgorica</div>
      <div class="mont-review-card__source">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
        Google recenzija
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="mont-cta">
  <div class="mont-cta__inner">
    <div class="mont-cta__text">
      <h2>Imate još pitanja?</h2>
      <p>Pozovite nas ili pošaljite upit – odgovaramo u roku od 24h.</p>
    </div>
    <div class="mont-cta__actions">
      <a href="<?php echo esc_url( home_url( '/korpa/' ) ); ?>" class="mont-cta__btn mont-cta__btn--primary">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
        Zatražite ponudu
      </a>
      <a href="tel:+38269234888" class="mont-cta__btn mont-cta__btn--ghost">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.67A2 2 0 012 .18h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
        +382 69 234 888
      </a>
    </div>
  </div>
</section>

<!-- PRE-FOOTER -->
<section class="pre-footer" aria-label="Poziv na akciju">
  <div class="pre-footer__inner">
    <div class="pre-footer__text">
      <span class="pre-footer__eyebrow">Door Expert · Podgorica</span>
      <h2 class="pre-footer__title">Posjetite nas ili zatražite ponudu</h2>
      <p class="pre-footer__subtitle">Roba na stanju. Formalna ponuda mejlom – bez obaveze.</p>
    </div>
    <div class="pre-footer__actions">
      <a href="<?php echo esc_url( home_url( '/korpa/' ) ); ?>" class="pre-footer__btn pre-footer__btn--primary">
        Zatražite ponudu
      </a>
      <a href="tel:+38269234888" class="pre-footer__btn pre-footer__btn--secondary">
        <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.6 1.22h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.8a16 16 0 0 0 6.29 6.29l.95-.95a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
        Pozovite nas
      </a>
    </div>
  </div>
</section>
