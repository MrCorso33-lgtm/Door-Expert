<?php
/**
 * Korpa za ponudu – verna konverzija prototipa korpa.html, iz stvarne WC korpe.
 *
 * Quote model: nema plaćanja. Forma upita je U KORPI (kao u prototipu), šalje se
 * AJAX-om (inc/quote-cart.php) i pravi WC_Order sa statusom on-hold.
 *
 * ODSTUPANJE od prototipa: tabovi "Korpa / Sačuvano" su izostavljeni jer wishlist
 * još ne postoji – prazan tab bi obmanjivao. Vraćaju se kad se portuje wishlist
 * (vidi DOCS/FOR DOOR EXPERT/01-AUDIT-REPORT.md, komponenta 9).
 *
 * Napomene po stavci se ne šalju kao polja forme (nisu unutar <form>), nego ih
 * korpa.js pokupi pri slanju i doda u payload kao item_note[cart_key].
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
	return;
}

$de_cart     = WC()->cart->get_cart();
$de_is_empty = WC()->cart->is_empty();
$de_company  = function_exists( 'door_expert_company_info' ) ? door_expert_company_info() : array();
$de_phone    = ! empty( $de_company['phones'][0] ) ? $de_company['phones'][0] : '+382 69 234 888';
$de_tel      = preg_replace( '/[^0-9+]/', '', $de_phone );
?>

<div class="korpa-page">
  <div class="korpa-inner">

    <nav class="korpa-breadcrumb" aria-label="Breadcrumb">
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Početna</a>
      <span aria-hidden="true">›</span>
      <span>Korpa</span>
    </nav>

    <h1 class="korpa-heading">Korpa za <em>ponudu</em></h1>
    <p class="korpa-subheading">Pregledajte odabrane stavke i pošaljite upit – formalna ponuda stiže mejlom u roku od 24h.</p>

    <?php if ( function_exists( 'wc_print_notices' ) && function_exists( 'wc_notice_count' ) && wc_notice_count() > 0 ) : ?>
      <div class="korpa-notices"><?php wc_print_notices(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WC generiše bezbjedan markup. ?></div>
    <?php endif; ?>

    <?php if ( ! $de_is_empty ) : ?>

      <div class="korpa-panel active" id="panel-korpa">
        <div class="korpa-layout">

          <!-- STAVKE -->
          <div class="korpa-items-col">
            <div class="korpa-items__header" aria-hidden="true">
              <span>Proizvod</span>
              <span>Cijena</span>
              <span>Količina</span>
              <span>Ukupno</span>
            </div>

            <ul class="korpa-items" id="cart-items-list" aria-label="Stavke u korpi">
              <?php foreach ( $de_cart as $de_key => $de_item ) : ?>
                <?php
                $de_product = $de_item['data'];
                if ( ! $de_product instanceof WC_Product ) {
                  continue;
                }
                $de_pid      = $de_item['product_id'];
                $de_qty      = (int) $de_item['quantity'];
                $de_link     = get_permalink( $de_pid );
                $de_subtotal = wc_price( $de_item['line_total'] + ( $de_item['line_tax'] ?? 0 ) );

                // Varijanta / atributi u jednom redu.
                $de_variant = array();
                if ( ! empty( $de_item['variation_id'] ) ) {
                  foreach ( wc_get_product_variation_attributes( $de_item['variation_id'] ) as $de_ak => $de_av ) {
                    if ( $de_av ) {
                      $de_variant[] = wc_attribute_label( str_replace( 'attribute_', '', $de_ak ) ) . ': ' . $de_av;
                    }
                  }
                }
                ?>
                <li class="korpa-item" data-cart-key="<?php echo esc_attr( $de_key ); ?>">
                  <a href="<?php echo esc_url( $de_link ); ?>" class="korpa-item__thumb-link" aria-label="<?php echo esc_attr( $de_product->get_name() ); ?>">
                    <?php echo $de_product->get_image( 'woocommerce_thumbnail', array( 'class' => 'korpa-item__thumb' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WC escaped. ?>
                  </a>

                  <div class="korpa-item__info">
                    <div class="korpa-item__name"><a href="<?php echo esc_url( $de_link ); ?>"><?php echo esc_html( $de_product->get_name() ); ?></a></div>
                    <?php if ( ! empty( $de_variant ) ) : ?>
                      <div class="korpa-item__variant"><?php echo esc_html( implode( ' · ', $de_variant ) ); ?></div>
                    <?php endif; ?>

                    <button type="button" class="korpa-item__note-toggle" aria-expanded="false" data-note-toggle>+ Dodaj napomenu</button>
                    <div class="korpa-item__note-field" hidden>
                      <textarea
                        class="korpa-item__note-input"
                        data-note-for="<?php echo esc_attr( $de_key ); ?>"
                        rows="2"
                        placeholder="npr. lijeva vrata, potrebna dimenzija..."></textarea>
                    </div>
                  </div>

                  <div class="korpa-item__price"><?php echo wp_kses_post( $de_product->get_price_html() ); ?></div>

                  <div class="korpa-item__qty" role="group" aria-label="Količina">
                    <button type="button" class="korpa-item__qty-btn" data-action="minus" aria-label="Smanji količinu">−</button>
                    <input class="korpa-item__qty-input" type="number" value="<?php echo esc_attr( (string) $de_qty ); ?>" min="1" max="999" aria-label="Količina">
                    <button type="button" class="korpa-item__qty-btn" data-action="plus" aria-label="Povećaj količinu">+</button>
                  </div>

                  <div class="korpa-item__subtotal" data-item-subtotal><?php echo wp_kses_post( $de_subtotal ); ?></div>

                  <button type="button" class="korpa-item__remove" data-cart-remove aria-label="<?php echo esc_attr( 'Ukloni ' . $de_product->get_name() . ' iz korpe' ); ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                  </button>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>

          <!-- PREGLED + FORMA -->
          <aside class="korpa-summary" aria-label="Pregled ponude i forma za slanje">

            <h2 class="korpa-summary__title">Pregled ponude</h2>

            <ul class="korpa-summary__lines" id="summary-lines" aria-label="Pregled stavki">
              <?php foreach ( $de_cart as $de_item ) : ?>
                <?php if ( ! $de_item['data'] instanceof WC_Product ) { continue; } ?>
                <li class="korpa-summary__line">
                  <span><?php echo esc_html( $de_item['data']->get_name() . ' × ' . (int) $de_item['quantity'] ); ?></span>
                  <span><?php echo wp_kses_post( wc_price( $de_item['line_total'] + ( $de_item['line_tax'] ?? 0 ) ) ); ?></span>
                </li>
              <?php endforeach; ?>
              <li class="korpa-summary__line korpa-summary__line--total">
                <span class="korpa-summary__total-label">Procijenjena vrijednost</span>
                <span class="korpa-summary__total-value" id="grand-total" data-cart-total><?php echo esc_html( html_entity_decode( wp_strip_all_tags( WC()->cart->get_total() ) ) ); ?></span>
              </li>
            </ul>

            <p class="korpa-summary__disclaimer">
              Konačna formalna ponuda (pro forma) stiže mejlom nakon provjere zalihe, dimenzija i uslova isporuke. Cijena može biti korigovana u vašu korist.
            </p>

            <form class="korpa-form" id="quote-form" novalidate>
              <div class="korpa-form__title">Vaši kontakt podaci</div>

              <div class="korpa-form__field">
                <label class="korpa-form__label" for="ime">Ime i prezime <span>*</span></label>
                <input class="korpa-form__input" type="text" id="ime" name="ime" autocomplete="name" placeholder="Vaše ime i prezime" required>
              </div>

              <div class="korpa-form__field">
                <label class="korpa-form__label" for="email">Email adresa <span>*</span></label>
                <input class="korpa-form__input" type="email" id="email" name="email" autocomplete="email" placeholder="vasa@email.com" required>
              </div>

              <div class="korpa-form__field">
                <label class="korpa-form__label" for="telefon">Telefon <span>*</span></label>
                <input class="korpa-form__input" type="tel" id="telefon" name="telefon" autocomplete="tel" placeholder="+382 6X XXX XXX" required>
                <p class="korpa-form__hint">Koristimo broj samo ako je potrebna brza potvrda dimenzije ili dostupnosti.</p>
              </div>

              <div class="korpa-form__field">
                <label class="korpa-form__label" for="grad">Grad / Adresa objekta</label>
                <input class="korpa-form__input" type="text" id="grad" name="grad" autocomplete="address-level2" placeholder="npr. Podgorica, Budva...">
              </div>

              <div class="korpa-form__field">
                <label class="korpa-form__label" for="napomena">Napomena</label>
                <textarea class="korpa-form__textarea" id="napomena" name="napomena" placeholder="Posebni zahtjevi, pitanja o dimenzijama, željeni termin..."></textarea>
              </div>

              <?php // Honeypot – vizuelno sakriveno, boti ga popunjavaju. ?>
              <div class="korpa-form__hp" aria-hidden="true" style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden;">
                <label for="website">Ne popunjavajte</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
              </div>

              <div class="korpa-form__consent">
                <input class="korpa-form__checkbox" type="checkbox" id="saglasnost" name="saglasnost" required>
                <label class="korpa-form__consent-text" for="saglasnost">
                  Slažem se sa <a href="<?php echo esc_url( home_url( '/politika-privatnosti/' ) ); ?>">politikom privatnosti</a> i prihvatam da Door Expert kontaktira mene radi slanja formalne ponude.
                </label>
              </div>

              <p class="korpa-form__error" id="quote-error" role="alert" hidden></p>

              <button class="korpa-form__submit" type="submit">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                Pošalji upit za ponudu
              </button>

              <a href="tel:<?php echo esc_attr( $de_tel ); ?>" class="korpa-form__call">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.6 1.22h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.8a16 16 0 0 0 6.29 6.29l.95-.95a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                Ili nas pozovite: <?php echo esc_html( $de_phone ); ?>
              </a>
            </form>

            <div class="korpa-next-steps">
              <div class="korpa-next-steps__title">Šta se dešava nakon slanja</div>
              <ol class="korpa-next-steps__list">
                <li class="korpa-next-steps__item">
                  <span class="korpa-next-steps__num">1</span>
                  <span class="korpa-next-steps__text"><strong>Potvrda prijema</strong> stiže odmah na vaš email sa sažetkom stavki.</span>
                </li>
                <li class="korpa-next-steps__item">
                  <span class="korpa-next-steps__num">2</span>
                  <span class="korpa-next-steps__text"><strong>Provjera zalihe</strong> – naš tim provjerava dostupnost i dimenzije (ovo je naša usluga, ne kašnjenje).</span>
                </li>
                <li class="korpa-next-steps__item">
                  <span class="korpa-next-steps__num">3</span>
                  <span class="korpa-next-steps__text"><strong>Formalna ponuda</strong> (pro forma) stiže mejlom u roku od 24h radnim danima.</span>
                </li>
                <li class="korpa-next-steps__item">
                  <span class="korpa-next-steps__num">4</span>
                  <span class="korpa-next-steps__text"><strong>Plaćanje i isporuka</strong> – gotovina, kartica, uplatnica ili rate. Vrata na stanju, isporuka odmah.</span>
                </li>
              </ol>
            </div>

          </aside>

        </div>
      </div>

    <?php else : ?>

      <!-- PRAZNA KORPA -->
      <div class="korpa-empty" id="cart-empty" aria-live="polite">
        <div class="korpa-empty__icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
            <line x1="3" y1="6" x2="21" y2="6"/>
            <path d="M16 10a4 4 0 0 1-8 0"/>
          </svg>
        </div>
        <h2 class="korpa-empty__title">Korpa je prazna</h2>
        <p class="korpa-empty__text">Dodajte proizvode iz kataloga i pošaljite nam upit za formalnu ponudu.</p>

        <div class="korpa-empty__cats" role="list" aria-label="Kategorije proizvoda">
          <?php
          $de_empty_cats = array( 'sobna-vrata', 'sigurnosna-vrata', 'keramicke-plocice', 'umivaonici' );
          foreach ( $de_empty_cats as $de_slug ) :
            $de_term = get_term_by( 'slug', $de_slug, 'product_cat' );
            if ( ! $de_term instanceof WP_Term ) {
              continue;
            }
            $de_url = get_term_link( $de_term );
            if ( is_wp_error( $de_url ) ) {
              continue;
            }
            $de_thumb_id = get_term_meta( $de_term->term_id, 'thumbnail_id', true );
            ?>
            <a href="<?php echo esc_url( $de_url ); ?>" class="korpa-empty__cat" role="listitem">
              <?php if ( $de_thumb_id ) : ?>
                <?php echo wp_get_attachment_image( $de_thumb_id, 'woocommerce_thumbnail', false, array( 'class' => 'korpa-empty__cat-img', 'alt' => esc_attr( $de_term->name ), 'loading' => 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped. ?>
              <?php endif; ?>
              <span class="korpa-empty__cat-name"><?php echo esc_html( $de_term->name ); ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>

    <?php endif; ?>

  </div>
</div>
