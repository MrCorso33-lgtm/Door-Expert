<?php
/**
 * Stranica: Kontakt – verna konverzija prototipa kontakt.html (<main>: linije 643-1034).
 *
 * Sekcije: hero → kanali (tel/mejl/salon) → forma + info panel → FAQ.
 * Forma je AJAX (contact.js + inc/contact-form.php): nonce, sanitizacija, rate limit,
 * honeypot; isporuka na n8n webhook (wp_mail fallback). Header/footer globalni.
 *
 * Popravke: hardkod .html linkovi → home_url(); telefon → 234 888 (zvanicni);
 * dijakritika vracena (prototip je bio bez nje).
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Jedno mjesto istine za podatke firme (adresa, telefon, mejl, radno vrijeme).
$de_company = function_exists( 'door_expert_company_info' ) ? door_expert_company_info() : array();
$de_email   = isset( $de_company['email'] ) ? $de_company['email'] : 'office@doorexpert.me';
$de_address = isset( $de_company['address'] ) ? $de_company['address'] : '';
$de_hours   = isset( $de_company['hours'] ) ? $de_company['hours'] : '';
?>

<!-- PAGE HERO -->
<section class="contact-hero">
  <div class="contact-hero__inner">
    <div class="contact-hero__breadcrumb">
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Početna</a>
      <span>/</span>
      <span>Kontakt</span>
    </div>
    <h1 class="contact-hero__title">Posjetite nas ili<br><em>zatražite ponudu</em></h1>
    <p class="contact-hero__subtitle">
      Fizički salon u Podgorici. Vrata, keramika i kupatilski elementi dostupni odmah na lageru.
      Formalna ponuda mejlom bez obaveze.
    </p>
  </div>
</section>

<!-- CONTACT CHANNELS -->
<section class="contact-channels">
  <div class="contact-channels__inner">

    <!-- Channel 1: Phone (primary) -->
    <a href="tel:+38269234888" class="contact-channel contact-channel--primary">
      <div class="contact-channel__icon">
        <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.6 1.22h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.8a16 16 0 0 0 6.29 6.29l.95-.95a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
      </div>
      <div class="contact-channel__body">
        <p class="contact-channel__label">Pozovite nas</p>
        <p class="contact-channel__value">+382 69 234 888</p>
        <p class="contact-channel__note">Pon-Pet 10-18, Sub 10-14 &middot; Odgovaramo odmah</p>
      </div>
      <div class="contact-channel__badge">Najbrže</div>
    </a>

    <!-- Channel 2: Email -->
    <a href="mailto:<?php echo esc_attr( $de_email ); ?>" class="contact-channel">
      <div class="contact-channel__icon">
        <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
      </div>
      <div class="contact-channel__body">
        <p class="contact-channel__label">Pošaljite mejl</p>
        <p class="contact-channel__value"><?php echo esc_html( $de_email ); ?></p>
        <p class="contact-channel__note">Odgovaramo u roku od 24h radnim danom</p>
      </div>
    </a>

    <!-- Channel 3: Showroom visit -->
    <a href="https://maps.google.com" target="_blank" rel="noopener" class="contact-channel">
      <div class="contact-channel__icon">
        <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
      </div>
      <div class="contact-channel__body">
        <p class="contact-channel__label">Posjetite salon</p>
        <p class="contact-channel__value">4. jul 74/6</p>
        <p class="contact-channel__note">Podgorica &middot; Parking dostupan</p>
      </div>
    </a>

  </div>
</section>

<!-- MAIN CONTENT: FORM + INFO -->
<section class="contact-main">
  <div class="contact-main__inner">

    <!-- LEFT: Contact form -->
    <div class="contact-form-col">
      <div class="contact-form-header">
        <h2 class="contact-form-header__title">Pošaljite upit</h2>
        <p class="contact-form-header__subtitle">
          Odgovaramo mejlom u roku od 24h. Ponuda je bez obaveze.
        </p>
      </div>

      <div class="contact-process">
        <div class="contact-process__step">
          <span class="contact-process__num">1</span>
          <span class="contact-process__text">Pošaljete upit</span>
        </div>
        <div class="contact-process__arrow">
          <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
        </div>
        <div class="contact-process__step">
          <span class="contact-process__num">2</span>
          <span class="contact-process__text">Šaljemo ponudu mejlom</span>
        </div>
        <div class="contact-process__arrow">
          <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
        </div>
        <div class="contact-process__step">
          <span class="contact-process__num">3</span>
          <span class="contact-process__text">Dogovaramo detalje</span>
        </div>
      </div>

      <form class="contact-form" id="contactForm" method="post" novalidate>

        <!-- Honeypot (anti-spam) – skriveno; botovi popune, ljudi ne. -->
        <div class="contact-form__hp" aria-hidden="true" style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden;">
          <label for="website">Ne popunjavajte ovo polje</label>
          <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
        </div>

        <!-- Ime – obavezno -->
        <div class="form-field">
          <label class="form-field__label" for="ime">
            Ime i prezime <span class="form-field__required">*</span>
          </label>
          <input
            class="form-field__input"
            type="text"
            id="ime"
            name="ime"
            placeholder="Npr. Marko Petrović"
            autocomplete="name"
            required
          >
          <span class="form-field__error" id="ime-error"></span>
        </div>

        <!-- Email – obavezno -->
        <div class="form-field">
          <label class="form-field__label" for="email">
            Email adresa <span class="form-field__required">*</span>
          </label>
          <input
            class="form-field__input"
            type="email"
            id="email"
            name="email"
            placeholder="vas@email.com"
            autocomplete="email"
            required
          >
          <span class="form-field__hint">Na ovu adresu šaljemo formalnu ponudu.</span>
          <span class="form-field__error" id="email-error"></span>
        </div>

        <!-- Telefon – opcionalno -->
        <div class="form-field">
          <label class="form-field__label" for="telefon">
            Broj telefona
            <span class="form-field__optional">(za brzi odgovor)</span>
          </label>
          <input
            class="form-field__input"
            type="tel"
            id="telefon"
            name="telefon"
            placeholder="+382 69 ..."
            autocomplete="tel"
            inputmode="tel"
          >
        </div>

        <!-- Tema upita -->
        <div class="form-field">
          <label class="form-field__label" for="tema">
            Šta vas zanima?
          </label>
          <select class="form-field__select" id="tema" name="tema">
            <option value="" disabled selected>Odaberite kategoriju</option>
            <option value="sobna-vrata">Sobna vrata</option>
            <option value="sigurnosna-vrata">Sigurnosna vrata</option>
            <option value="keramicke-plocice">Keramičke pločice</option>
            <option value="umivaonici">Dekorativni umivaonici</option>
            <option value="vise-kategorija">Više kategorija</option>
            <option value="b2b">B2B / Investitori</option>
            <option value="ostalo">Ostalo</option>
          </select>
        </div>

        <!-- Poruka -->
        <div class="form-field">
          <label class="form-field__label" for="poruka">
            Poruka
            <span class="form-field__optional">(opciono)</span>
          </label>
          <textarea
            class="form-field__textarea"
            id="poruka"
            name="poruka"
            rows="4"
            placeholder="Opišite šta tražite, dimenzije, boje, količine..."
          ></textarea>
        </div>

        <!-- Privacy note -->
        <p class="contact-form__privacy">
          Vaši podaci se koriste isključivo za odgovor na upit i ne dijele se sa trećim stranama.
        </p>

        <!-- Submit -->
        <button type="submit" class="contact-form__submit" id="submitBtn">
          <svg viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
          Pošalji upit
        </button>

      </form>

      <!-- Success state (hidden by default) -->
      <div class="contact-success" id="contactSuccess" hidden>
        <div class="contact-success__icon">
          <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <h3 class="contact-success__title">Upit je primljen!</h3>
        <p class="contact-success__text">
          Poslaćemo vam formalnu ponudu mejlom u roku od 24 sata radnim danom.
          Ako želite brzi odgovor, slobodno nas pozovite.
        </p>
        <a href="tel:+38269234888" class="contact-success__call">
          <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.6 1.22h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.8a16 16 0 0 0 6.29 6.29l.95-.95a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
          +382 69 234 888
        </a>
      </div>

    </div><!-- /contact-form-col -->

    <!-- RIGHT: Info panel -->
    <div class="contact-info-col">

      <!-- Salon info -->
      <div class="contact-info-card">
        <h3 class="contact-info-card__title">Izložbeni salon</h3>

        <!-- Map placeholder -->
        <div class="contact-map">
          <div class="contact-map__placeholder">
            <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            <p>Google Maps</p>
            <span>Podgorica, Crna Gora</span>
          </div>
          <?php // PRODUKCIJA: zamijeniti sa <iframe> Google Maps embed (loading="lazy"). ?>
        </div>

        <a href="https://maps.google.com" target="_blank" rel="noopener" class="contact-map__link">
          <svg viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
          Otvori u Google Maps
        </a>

        <ul class="contact-info-list">
          <li class="contact-info-list__item">
            <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            <div>
              <strong>Adresa</strong>
              <span><?php echo esc_html( $de_address ); ?></span>
            </div>
          </li>
          <li class="contact-info-list__item">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <div>
              <strong>Radno vrijeme</strong>
              <span><?php echo esc_html( $de_hours ); ?></span>
            </div>
          </li>
          <li class="contact-info-list__item">
            <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.6 1.22h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.8a16 16 0 0 0 6.29 6.29l.95-.95a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            <div>
              <strong>Telefon</strong>
              <a href="tel:+38269234888">+382 69 234 888</a>
            </div>
          </li>
          <li class="contact-info-list__item">
            <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            <div>
              <strong>Email</strong>
              <a href="mailto:<?php echo esc_attr( $de_email ); ?>"><?php echo esc_html( $de_email ); ?></a>
            </div>
          </li>
        </ul>
      </div>

      <!-- Trust signals -->
      <div class="contact-trust">
        <div class="contact-trust__item">
          <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
          <span>Vrata odmah na lageru, isporuka bez čekanja</span>
        </div>
        <div class="contact-trust__item">
          <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
          <span>Formalna ponuda mejlom u roku od 24h</span>
        </div>
        <div class="contact-trust__item">
          <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
          <span>Ponuda bez obaveze kupovine</span>
        </div>
        <div class="contact-trust__item">
          <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
          <span>Direktan uvoz iz Španije i Evrope</span>
        </div>
      </div>

      <!-- Installation disclaimer -->
      <div class="contact-disclaimer">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <p>
          <strong>Napomena o montaži:</strong> Door Expert prodaje vrata i keramiku.
          Montažu vrše nezavisni majstori koji naplaćuju direktno klijentu.
          <a href="<?php echo esc_url( home_url( '/montaza/' ) ); ?>">Saznajte više o procesu montaže.</a>
        </p>
      </div>

    </div><!-- /contact-info-col -->

  </div><!-- /contact-main__inner -->
</section>

<!-- FAQ -->
<section class="contact-faq">
  <div class="contact-faq__inner">
    <h2 class="contact-faq__title">Česta pitanja</h2>

    <div class="faq-list">

      <details class="faq-item">
        <summary class="faq-item__question">
          Da li cijena uključuje montažu?
          <svg class="faq-item__icon" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
        </summary>
        <div class="faq-item__answer">
          <p>Ne. Cijena proizvoda uključuje samo vrata ili keramiku sa ramom/fugom (dostupno odmah na lageru). Montažu vrše nezavisni majstori koji naplaćuju direktno vama. Door Expert može preporučiti provjerene majstore sa fiksnim cjenovnikom.</p>
        </div>
      </details>

      <details class="faq-item">
        <summary class="faq-item__question">
          Koliko dugo čekam na isporuku?
          <svg class="faq-item__icon" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
        </summary>
        <div class="faq-item__answer">
          <p>Vrata su odmah dostupna na lageru. Konkurencija ima rok čekanja od 45 i više dana, mi isporučujemo odmah. Jedino što zahtijeva dogovor je termin montaže sa majstorom (2-15 dana u zavisnosti od rasporeda).</p>
        </div>
      </details>

      <details class="faq-item">
        <summary class="faq-item__question">
          Kako izgleda proces kupovine?
          <svg class="faq-item__icon" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
        </summary>
        <div class="faq-item__answer">
          <p>Pošaljete upit ili dodajete proizvode u korpu za ponudu. Mi vam šaljemo formalnu ponudu (predračun) mejlom sa svim detaljima i cijenama. Ako prihvatite, plaćanje je gotovinom, karticom ili bankovnom uplatnicom. Montažu dogovarate sa nezavisnim majstorom.</p>
        </div>
      </details>

      <details class="faq-item">
        <summary class="faq-item__question">
          Da li radite B2B i veće narudžbe?
          <svg class="faq-item__icon" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
        </summary>
        <div class="faq-item__answer">
          <p>Da. Radimo sa investitorima i izvođačima za veće projekte (20-40 vrata i više). Za B2B upite pozovite nas direktno ili posjetite našu stranicu za investitore.</p>
        </div>
      </details>

      <details class="faq-item">
        <summary class="faq-item__question">
          Da li mogu da vidim proizvode uživo?
          <svg class="faq-item__icon" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
        </summary>
        <div class="faq-item__answer">
          <p>Da, u našem salonu u Podgorici možete vidjeti i opipati sve modele vrata, keramike i umivaonika. Radimo pon-pet 10-18, subotom 10-14. Preporučujemo da dođete sa mjerama prostorije za preciznu ponudu.</p>
        </div>
      </details>

    </div><!-- /faq-list -->
  </div>
</section>
