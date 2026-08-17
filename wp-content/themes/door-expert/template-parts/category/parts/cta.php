<?php
/**
 * CTA baner (subcat-cta-banner). Podaci: $args['cta'] = [ title, desc, phone ].
 * @package DoorExpert
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$cta = isset( $args['cta'] ) ? $args['cta'] : array();
if ( empty( $cta['title'] ) ) {
	return;
}
$phone = isset( $cta['phone'] ) ? $cta['phone'] : 'tel:+38269234888';
?>
<section class="subcat-cta-banner">
  <div class="subcat-cta-banner__inner">
    <h2 class="subcat-cta-banner__title"><?php echo esc_html( $cta['title'] ); ?></h2>
    <?php if ( ! empty( $cta['desc'] ) ) : ?>
      <p class="subcat-cta-banner__desc"><?php echo esc_html( $cta['desc'] ); ?></p>
    <?php endif; ?>
    <div class="subcat-cta-banner__btns">
      <a href="<?php echo esc_attr( $phone ); ?>" class="btn btn--white btn--lg">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.6 1.22h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.8a16 16 0 0 0 6.29 6.29l.95-.95a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
        Pozovite nas
      </a>
      <a href="<?php echo esc_url( home_url( '/kontakt/' ) ); ?>" class="btn btn--outline-white btn--lg">Posjetite salon</a>
    </div>
  </div>
</section>
