<?php
/**
 * PDP – traka sa istaknutim atributima (ikona + labela + vrijednost).
 *
 * Prima $args['items'] iz door_expert_product_highlights() (inc/product-highlights.php).
 * Koji atributi se prikazuju bira se u adminu: Proizvodi → Istaknuti atributi.
 *
 * <dl> jer je ovo niz parova pojam/vrijednost, a ne dekoracija. Ikona je
 * aria-hidden: nosi je labela pored, pa bi je čitač ekrana samo duplirao.
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$de_items = isset( $args['items'] ) && is_array( $args['items'] ) ? $args['items'] : array();

if ( empty( $de_items ) ) {
	return;
}
?>

<section class="product-highlights" aria-label="Ključne osobine">
  <dl class="product-highlights__grid">
    <?php foreach ( $de_items as $de_item ) : ?>
      <div class="product-highlights__item">
        <?php if ( '' !== $de_item['icon'] ) : ?>
          <span class="product-highlights__icon" aria-hidden="true">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" focusable="false">
              <?php echo wp_kses( $de_item['icon'], door_expert_svg_allowed_html() ); ?>
            </svg>
          </span>
        <?php endif; ?>
        <div class="product-highlights__text">
          <dt class="product-highlights__label"><?php echo esc_html( $de_item['label'] ); ?></dt>
          <dd class="product-highlights__value"><?php echo esc_html( $de_item['value'] ); ?></dd>
        </div>
      </div>
    <?php endforeach; ?>
  </dl>
</section>
