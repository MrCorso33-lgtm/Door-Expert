<?php
/**
 * Cross-sell kartice (subcat-crosssell). Podaci: $args['crosssell'].
 * items[]: 'cat' (product_cat slug) -> link preko door_expert_cat_url(), + img/alt/title/desc.
 * @package DoorExpert
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$cs = isset( $args['crosssell'] ) ? $args['crosssell'] : array();
if ( empty( $cs['items'] ) ) {
	return;
}
?>
<section class="subcat-crosssell">
  <div class="subcat-crosssell__inner">
    <?php if ( ! empty( $cs['title'] ) ) : ?>
      <div class="subcat-crosssell__header">
        <h2 class="subcat-crosssell__title"><?php echo esc_html( $cs['title'] ); ?></h2>
      </div>
    <?php endif; ?>
    <div class="crosssell-grid">
      <?php foreach ( $cs['items'] as $item ) :
        $url = ! empty( $item['cat'] ) && function_exists( 'door_expert_cat_url' )
          ? door_expert_cat_url( $item['cat'] )
          : ( isset( $item['url'] ) ? $item['url'] : '#' );
        ?>
        <a href="<?php echo esc_url( $url ); ?>" class="crosssell-card">
          <div class="crosssell-card__img-wrap">
            <img src="<?php echo esc_url( $item['img'] ); ?>" alt="<?php echo esc_attr( isset( $item['alt'] ) ? $item['alt'] : '' ); ?>" loading="lazy" />
          </div>
          <div class="crosssell-card__body">
            <h3 class="crosssell-card__title"><?php echo esc_html( $item['title'] ); ?></h3>
            <p class="crosssell-card__desc"><?php echo esc_html( $item['desc'] ); ?></p>
            <span class="crosssell-card__link">Pogledaj &rarr;</span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
