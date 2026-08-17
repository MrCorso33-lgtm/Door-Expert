<?php
/**
 * FAQ akordeon (subcat-faq). Podaci: $args['faq'] = [ eyebrow, title, items[ [q, a] ] ].
 * JS toggle je u category.js.
 * @package DoorExpert
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$faq = isset( $args['faq'] ) ? $args['faq'] : array();
if ( empty( $faq['items'] ) ) {
	return;
}
?>
<section class="subcat-faq">
  <div class="subcat-faq__inner">
    <div class="subcat-faq__header">
      <?php if ( ! empty( $faq['eyebrow'] ) ) : ?>
        <p class="subcat-faq__eyebrow"><?php echo esc_html( $faq['eyebrow'] ); ?></p>
      <?php endif; ?>
      <?php if ( ! empty( $faq['title'] ) ) : ?>
        <h2 class="subcat-faq__title"><?php echo esc_html( $faq['title'] ); ?></h2>
      <?php endif; ?>
    </div>
    <div class="faq-list">
      <?php foreach ( $faq['items'] as $item ) : ?>
        <div class="faq-item">
          <button class="faq-item__toggle" aria-expanded="false">
            <span><?php echo esc_html( $item['q'] ); ?></span>
            <svg class="faq-item__chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
          </button>
          <div class="faq-item__body">
            <p><?php echo esc_html( $item['a'] ); ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="subcat-faq__cta">
      <p>Imate dodatnih pitanja?</p>
      <a href="tel:+38269234888" class="btn btn--outline">Pozovite nas</a>
    </div>
  </div>
</section>
