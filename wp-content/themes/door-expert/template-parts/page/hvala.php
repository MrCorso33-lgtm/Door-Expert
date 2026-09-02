<?php
/**
 * Stranica: Hvala (potvrda poslatog upita) – verna konverzija prototipa hvala.html.
 *
 * Na nju vodi korpa nakon uspješno poslatog upita:
 * inc/quote-cart.php -> home_url( '/hvala/?upit=<broj narudžbe>' ).
 * Broj upita se prikazuje ako je prisutan u URL-u (potvrda kupcu).
 *
 * CSS: assets/css/hvala.css (izvučen iz inline <style>, mobile-first).
 * Linkovi su dinamički (kategorije preko term link-a, telefon iz company info).
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$de_company = function_exists( 'door_expert_company_info' ) ? door_expert_company_info() : array();
$de_phone   = ! empty( $de_company['phones'][0] ) ? $de_company['phones'][0] : '+382 69 234 888';
$de_tel     = preg_replace( '/[^0-9+]/', '', $de_phone );
$de_hours   = ! empty( $de_company['hours'] ) ? $de_company['hours'] : 'Pon–Pet: 10:00–18:00 · Sub: 10:00–14:00';

// Broj upita iz redirekcije nakon slanja (samo prikaz, bez ikakve akcije).
$de_ref = isset( $_GET['upit'] ) ? sanitize_text_field( wp_unslash( $_GET['upit'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only prikaz.
?>

<section class="thankyou">

  <div class="thankyou__icon">
    <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
  </div>

  <h1 class="thankyou__title">Hvala na upitu!</h1>
  <p class="thankyou__subtitle">
    Vaš zahtjev za ponudu je uspješno poslat. Naš tim će pregledati vašu listu
    i pripremiti formalnu ponudu sa svim detaljima.
  </p>

  <?php if ( '' !== $de_ref ) : ?>
    <p class="thankyou__ref">Broj vašeg upita: <strong><?php echo esc_html( $de_ref ); ?></strong></p>
  <?php endif; ?>

  <!-- Šta se dešava dalje -->
  <div class="thankyou-steps">
    <h2 class="thankyou-steps__title">Šta se dešava dalje?</h2>
    <ol class="thankyou-steps__list">
      <li class="thankyou-steps__item">
        <div class="thankyou-steps__number"></div>
        <div class="thankyou-steps__text">
          <strong>Pregled vaše liste</strong>
          <span>Naš tim provjerava dostupnost i priprema ponudu – obično u roku od 2-4 radna sata.</span>
        </div>
      </li>
      <li class="thankyou-steps__item">
        <div class="thankyou-steps__number"></div>
        <div class="thankyou-steps__text">
          <strong>Formalna ponuda na mejl</strong>
          <span>Dobićete detaljan predračun sa cijenama, dimenzijama i uslovima plaćanja na vašu email adresu.</span>
        </div>
      </li>
      <li class="thankyou-steps__item">
        <div class="thankyou-steps__number"></div>
        <div class="thankyou-steps__text">
          <strong>Vi odlučujete</strong>
          <span>Ponuda je bez obaveze. Možete je prihvatiti, tražiti izmjene, ili nas pozvati za dodatna pitanja.</span>
        </div>
      </li>
      <li class="thankyou-steps__item">
        <div class="thankyou-steps__number"></div>
        <div class="thankyou-steps__text">
          <strong>Isporuka i montaža</strong>
          <span>Po potvrdi, dogovaramo isporuku (vrata su na stanju!) i povezujemo vas sa sertifikovanim montažerima.</span>
        </div>
      </li>
    </ol>
  </div>

  <!-- Info -->
  <div class="thankyou-info">
    <div class="thankyou-info__icon">
      <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
    </div>
    <div class="thankyou-info__text">
      <strong>Želite brži odgovor?</strong> Pozovite nas direktno na
      <a href="tel:<?php echo esc_attr( $de_tel ); ?>" style="color:var(--color-jantar);font-weight:600;"><?php echo esc_html( $de_phone ); ?></a>
      – <?php echo esc_html( $de_hours ); ?>.
    </div>
  </div>

  <!-- Akcije -->
  <div class="thankyou-actions">
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="thankyou-actions__btn thankyou-actions__btn--primary">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
      Nazad na početnu
    </a>
    <a href="<?php echo esc_url( home_url( '/akcije/' ) ); ?>" class="thankyou-actions__btn thankyou-actions__btn--secondary">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
      Pogledaj akcije
    </a>
    <a href="tel:<?php echo esc_attr( $de_tel ); ?>" class="thankyou-actions__btn thankyou-actions__btn--call">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72"></path></svg>
      Pozovite nas
    </a>
  </div>

  <!-- Cross-sell -->
  <div class="thankyou-crosssell">
    <h2 class="thankyou-crosssell__title">Možda vas zanima</h2>
    <p class="thankyou-crosssell__subtitle">Dok čekate ponudu, istražite naše kolekcije</p>

    <div class="thankyou-crosssell__grid">
      <?php
      $de_cards = array(
        array(
          'url'   => function_exists( 'door_expert_cat_url' ) ? door_expert_cat_url( 'sobna-vrata' ) : home_url( '/' ),
          'img'   => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400&q=80&auto=format&fit=crop',
          'alt'   => 'Sobna vrata',
          'title' => 'Sobna vrata',
          'desc'  => 'Klizna, staklena, standardna',
        ),
        array(
          'url'   => function_exists( 'door_expert_cat_url' ) ? door_expert_cat_url( 'keramicke-plocice' ) : home_url( '/' ),
          'img'   => 'https://images.unsplash.com/photo-1615971677499-5467cbab01c0?w=400&q=80&auto=format&fit=crop',
          'alt'   => 'Keramičke pločice',
          'title' => 'Keramičke pločice',
          'desc'  => 'Španski brendovi, 7 kategorija',
        ),
        array(
          'url'   => home_url( '/brendovi/' ),
          'img'   => 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=400&q=80&auto=format&fit=crop',
          'alt'   => 'Naši brendovi',
          'title' => 'Naši brendovi',
          'desc'  => 'Tau, Arcana, Bathco i više',
        ),
        array(
          'url'   => home_url( '/inspiracija/' ),
          'img'   => 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=400&q=80&auto=format&fit=crop',
          'alt'   => 'Inspiracija',
          'title' => 'Inspiracija',
          'desc'  => 'Enterijeri i ideje',
        ),
      );

      foreach ( $de_cards as $de_card ) :
        ?>
        <a href="<?php echo esc_url( $de_card['url'] ); ?>" class="thankyou-crosssell__card">
          <img src="<?php echo esc_url( $de_card['img'] ); ?>" alt="<?php echo esc_attr( $de_card['alt'] ); ?>" class="thankyou-crosssell__card-img" loading="lazy">
          <div class="thankyou-crosssell__card-body">
            <div class="thankyou-crosssell__card-title"><?php echo esc_html( $de_card['title'] ); ?></div>
            <div class="thankyou-crosssell__card-desc"><?php echo esc_html( $de_card['desc'] ); ?></div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>

</section>
