<?php
/**
 * 404.php – stranica nije pronađena. Verna konverzija prototipa 404.html (linije 647-908
 * plus mobilna sticky traka 1046-1056).
 *
 * Root template: sam zove get_header()/get_footer() i drži markup direktno (presedan:
 * archive-product.php). <main> je otvoren u header.php, zatvoren u footer.php, pa je
 * prototipski <main class="page-404"> ovdje <div> (klasa nosi layout iz 404.css).
 *
 * ODSTUPANJA od prototipa (svjesna):
 *   - Kolona "Popularne pretrage" IZBAČENA: bili su izmišljeni upiti bez analitike, a svih
 *     7 je vodilo na 4 generičke stranice. Vraća se kad bude logovanja pretraga.
 *     Zbog toga .e404-links__inner dobija --2col modifikator (grid je bio 1fr 1fr 320px).
 *   - Search UI ostaje, ali je NAMJERNO neaktivan – pretraga u temi ne postoji. Žiči se kad
 *     se portuje Saya komponenta #16 (vidi DOCS/FOR DOOR EXPERT/01-AUDIT-REPORT.md:181).
 *   - Slike kartica: iz product_cat thumbnail-a (prototip je pokazivao na /manus-storage/
 *     koji ne postoji). Hero pozadinska slika izostavljena iz istog razloga.
 *   - Naziv kartice dolazi iz terma, pa je "Dekorativni umivaonici" umjesto "Umivaonici Bathco".
 *   - Telefon i radno vrijeme iz door_expert_company_info() (prototip je imao stari 123 456).
 *   - Svi .html linkovi -> home_url() / door_expert_cat_url().
 *
 * TODO (sadržaj, provjeriti sa klijentom): tvrdnje u trust traci, naročito
 * "AAA bonitetna ocjena 2025" (nosi godinu), i badge-ovi "Na stanju" / "EN 1627".
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$de_company = function_exists( 'door_expert_company_info' ) ? door_expert_company_info() : array();
$de_phone   = ! empty( $de_company['phones'][0] ) ? $de_company['phones'][0] : '+382 69 234 888';
$de_tel     = preg_replace( '/[^0-9+]/', '', $de_phone );
$de_hours   = ! empty( $de_company['hours'] ) ? $de_company['hours'] : 'Pon–Pet: 10:00–18:00 · Sub: 10:00–14:00';

// Kategorijske kartice – naziv i slika dolaze iz product_cat terma, ostalo je statično.
$de_cards = array(
	array(
		'slug'  => 'sobna-vrata',
		'badge' => 'Na stanju',
		'mod'   => '',
		'meta'  => 'Klizna · Staklena · Punih krila',
		'icon'  => '<svg class="e404-cat-card__icon" width="18" height="18" viewBox="0 0 40 40" fill="none"><rect x="8" y="4" width="18" height="32" rx="1.5" stroke="currentColor" stroke-width="2.2"/><circle cx="23" cy="20" r="1.8" fill="currentColor"/></svg>',
	),
	array(
		'slug'  => 'sigurnosna-vrata',
		'badge' => 'EN 1627',
		'mod'   => '',
		'meta'  => 'RC2 · RC3 · Višetačkasto zaključavanje',
		'icon'  => '<svg class="e404-cat-card__icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
	),
	array(
		'slug'  => 'keramicke-plocice',
		'badge' => '🇪🇸 Španija',
		'mod'   => ' e404-cat-card__badge--spain',
		'meta'  => 'Tau · Arcana · New Tiles · Ribesalbes',
		'icon'  => '<svg class="e404-cat-card__icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="8" height="8"/><rect x="13" y="3" width="8" height="8"/><rect x="3" y="13" width="8" height="8"/><rect x="13" y="13" width="8" height="8"/></svg>',
	),
	array(
		'slug'  => 'umivaonici',
		'badge' => '🇪🇸 Bathco',
		'mod'   => ' e404-cat-card__badge--spain',
		'meta'  => 'Oval · Okrugli · Kameni · Samostojeći',
		'icon'  => '<svg class="e404-cat-card__icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12h16M4 12c0 4.4 3.6 8 8 8s8-3.6 8-8"/><path d="M8 12V7a4 4 0 018 0v5"/></svg>',
	),
);

// Brzi linkovi. inspiracija/b2b još nisu konvertovani – vode na 404 dok se ne naprave.
$de_quick = array(
	array(
		'url'   => home_url( '/' ),
		'label' => 'Početna stranica',
		'icon'  => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>',
	),
	array(
		'url'   => home_url( '/akcije/' ),
		'label' => 'Akcije i popusti',
		'icon'  => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>',
	),
	array(
		'url'   => home_url( '/inspiracija/' ),
		'label' => 'Inspiracija i projekti',
		'icon'  => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
	),
	array(
		'url'   => home_url( '/b2b/' ),
		'label' => 'Za investitore i firme',
		'icon'  => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>',
	),
	array(
		'url'   => home_url( '/o-nama/' ),
		'label' => 'O nama',
		'icon'  => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
	),
	array(
		'url'   => home_url( '/montaza/' ),
		'label' => 'Montaža – šta je uključeno',
		'icon'  => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg>',
	),
	array(
		'url'   => home_url( '/kontakt/' ),
		'label' => 'Kontakt i lokacija',
		'icon'  => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>',
	),
);
?>

<div class="page-404">

  <!-- HERO -->
  <section class="e404-hero">
    <div class="e404-hero__bg">
      <?php // Pozadinska fotografija se dodaje kad bude prave slike (prototipska ne postoji). ?>
      <div class="e404-hero__grain"></div>
    </div>

    <div class="e404-hero__content">
      <div class="e404-hero__left">
        <p class="e404-hero__eyebrow">Greška 404</p>
        <h1 class="e404-hero__title">
          Ova stranica<br>
          <em>nije pronađena</em>
        </h1>
        <p class="e404-hero__desc">
          Stranica koju tražite je možda premještena, obrisana ili nikada nije postojala.
          Pregledajte naše kategorije ili se vratite na početnu.
        </p>

        <?php // Search UI stoji, ali je neaktivan dok se ne portuje pretraga (vidi docblock). ?>
        <div class="e404-search">
          <div class="e404-search__wrap">
            <svg class="e404-search__icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input
              type="search"
              class="e404-search__input"
              id="e404SearchInput"
              placeholder="Pretražite vrata, pločice, umivaonike..."
              autocomplete="off"
            >
            <button type="button" class="e404-search__btn" id="e404SearchBtn">Pretraži</button>
          </div>
          <div class="e404-search__suggestions">
            <span class="e404-search__suggestions-label">Popularno:</span>
            <button type="button" class="e404-search__chip" data-query="sobna vrata">Sobna vrata</button>
            <button type="button" class="e404-search__chip" data-query="sigurnosna vrata RC3">Sigurnosna RC3</button>
            <button type="button" class="e404-search__chip" data-query="keramičke pločice">Pločice</button>
            <button type="button" class="e404-search__chip" data-query="umivaonici Bathco">Bathco</button>
          </div>
        </div>

        <div class="e404-hero__ctas">
          <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="e404-btn e404-btn--primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Početna stranica
          </a>
          <a href="tel:<?php echo esc_attr( $de_tel ); ?>" class="e404-btn e404-btn--ghost">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.68A2 2 0 012 .84h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 8.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
            Pozovite nas
          </a>
        </div>
      </div>

      <div class="e404-hero__right">
        <div class="e404-big-number" aria-hidden="true">404</div>
      </div>
    </div>
  </section>

  <!-- KATEGORIJE -->
  <section class="e404-categories">
    <div class="e404-categories__inner">
      <div class="e404-categories__header">
        <p class="e404-categories__eyebrow">Možda tražite</p>
        <h2 class="e404-categories__title">Naše kategorije</h2>
      </div>

      <div class="e404-categories__grid">
        <?php foreach ( $de_cards as $de_card ) : ?>
          <?php
          $de_term = get_term_by( 'slug', $de_card['slug'], 'product_cat' );
          if ( ! $de_term instanceof WP_Term ) {
            continue; // Bez terma nema ni linka – ne crtamo mrtvu karticu.
          }

          $de_url      = function_exists( 'door_expert_cat_url' ) ? door_expert_cat_url( $de_card['slug'] ) : home_url( '/' );
          $de_thumb_id = (int) get_term_meta( $de_term->term_id, 'thumbnail_id', true );
          ?>
          <a href="<?php echo esc_url( $de_url ); ?>" class="e404-cat-card">
            <div class="e404-cat-card__img-wrap">
              <?php
              if ( $de_thumb_id ) {
                echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image je escaped.
                  $de_thumb_id,
                  'woocommerce_thumbnail',
                  false,
                  array(
                    'class'   => 'e404-cat-card__img',
                    'alt'     => esc_attr( $de_term->name ),
                    'loading' => 'lazy',
                  )
                );
              } elseif ( function_exists( 'wc_placeholder_img_src' ) ) {
                printf(
                  '<img src="%s" alt="" class="e404-cat-card__img" loading="lazy" />',
                  esc_url( wc_placeholder_img_src( 'woocommerce_thumbnail' ) )
                );
              }
              ?>
              <div class="e404-cat-card__overlay"></div>
            </div>
            <span class="e404-cat-card__badge<?php echo esc_attr( $de_card['mod'] ); ?>"><?php echo esc_html( $de_card['badge'] ); ?></span>
            <div class="e404-cat-card__body">
              <?php echo $de_card['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- statičan SVG iz ovog fajla. ?>
              <h3 class="e404-cat-card__name"><?php echo esc_html( $de_term->name ); ?></h3>
              <p class="e404-cat-card__meta"><?php echo esc_html( $de_card['meta'] ); ?></p>
            </div>
            <svg class="e404-cat-card__arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- BRZI LINKOVI + KONTAKT -->
  <section class="e404-links">
    <div class="e404-links__inner e404-links__inner--2col">

      <div class="e404-links__col">
        <h3 class="e404-links__col-title">Brzi linkovi</h3>
        <ul class="e404-links__list">
          <?php foreach ( $de_quick as $de_link ) : ?>
            <li><a href="<?php echo esc_url( $de_link['url'] ); ?>" class="e404-links__item">
              <?php echo $de_link['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- statičan SVG iz ovog fajla. ?>
              <?php echo esc_html( $de_link['label'] ); ?>
            </a></li>
          <?php endforeach; ?>
        </ul>
      </div>

      <div class="e404-links__contact">
        <p class="e404-links__contact-eyebrow">Ne možete naći?</p>
        <h3 class="e404-links__contact-title">Nazovite nas direktno</h3>
        <p class="e404-links__contact-desc">
          Naš tim u showroomu može da vam pomogne pronaći tačno ono što tražite.
        </p>
        <a href="tel:<?php echo esc_attr( $de_tel ); ?>" class="e404-links__contact-phone">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.68A2 2 0 012 .84h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 8.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
          <?php echo esc_html( $de_phone ); ?>
        </a>
        <p class="e404-links__contact-hours"><?php echo esc_html( $de_hours ); ?></p>
        <a href="<?php echo esc_url( home_url( '/kontakt/' ) ); ?>" class="e404-links__contact-link">
          Posjetite showroom
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
      </div>

    </div>
  </section>

  <!-- TRUST TRAKA -->
  <section class="e404-trust">
    <div class="e404-trust__inner">
      <div class="e404-trust__item">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
        <span>Isporuka odmah – na stanju</span>
      </div>
      <div class="e404-trust__divider"></div>
      <div class="e404-trust__item">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        <span>AAA bonitetna ocjena 2025</span>
      </div>
      <div class="e404-trust__divider"></div>
      <div class="e404-trust__item">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        <span>Odgovor u roku od 24h</span>
      </div>
      <div class="e404-trust__divider"></div>
      <div class="e404-trust__item">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
        <span>Showroom Podgorica</span>
      </div>
    </div>
  </section>

</div><!-- /page-404 -->

<!-- Mobilna sticky traka (404-specifična) -->
<div class="e404-mobile-sticky">
  <a href="tel:<?php echo esc_attr( $de_tel ); ?>" class="e404-mobile-sticky__call">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.68A2 2 0 012 .84h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 8.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
    Pozovite
  </a>
  <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="e404-mobile-sticky__home">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
    Početna
  </a>
</div>

<?php
get_footer();
