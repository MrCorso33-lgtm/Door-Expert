<?php
/**
 * Benefiti (subcat-benefits) – kartice sa ikonom, naslovom, tekstom.
 * Podaci: $args['benefits'] = [ eyebrow, title, items[ [icon(svg-inner), title, text] ] ].
 * @package DoorExpert
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$b = isset( $args['benefits'] ) ? $args['benefits'] : array();
if ( empty( $b['items'] ) ) {
	return;
}
?>
<section class="subcat-benefits">
  <div class="subcat-benefits__inner">
    <div class="subcat-benefits__header">
      <?php if ( ! empty( $b['eyebrow'] ) ) : ?>
        <p class="subcat-benefits__eyebrow"><?php echo esc_html( $b['eyebrow'] ); ?></p>
      <?php endif; ?>
      <?php if ( ! empty( $b['title'] ) ) : ?>
        <h2 class="subcat-benefits__title"><?php echo esc_html( $b['title'] ); ?></h2>
      <?php endif; ?>
    </div>
    <div class="subcat-benefits__grid">
      <?php foreach ( $b['items'] as $item ) : ?>
        <div class="benefit-card">
          <div class="benefit-card__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><?php
              // icon = trusted inline SVG putanje (hardkodovano, ne user input).
              echo isset( $item['icon'] ) ? $item['icon'] : ''; // phpcs:ignore WordPress.Security.EscapeOutput
            ?></svg>
          </div>
          <h3 class="benefit-card__title"><?php echo esc_html( $item['title'] ); ?></h3>
          <p class="benefit-card__text"><?php echo esc_html( $item['text'] ); ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
