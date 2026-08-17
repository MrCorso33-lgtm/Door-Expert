<?php
/**
 * cat-subcats – grid potkategorija (deca tekućeg terma). Koriste roditeljske kategorije.
 *
 * Napomena: prototip koristi ove kartice kao in-page filter (data-subcat + category.js).
 * U WP-u su potkategorije PRAVE stranice (SEO hijerarhija), pa kartice LINKUJU na njih
 * (get_term_link) – ista markup struktura (category.css ih stilizuje), ispravno ponašanje.
 *
 * @package DoorExpert
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$term = isset( $args['term'] ) ? $args['term'] : get_queried_object();
if ( ! ( $term instanceof WP_Term ) ) {
	return;
}
$children = get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'parent'     => $term->term_id,
		'hide_empty' => false,
	)
);
if ( empty( $children ) || is_wp_error( $children ) ) {
	return;
}
$label = isset( $args['label'] ) ? $args['label'] : 'Izaberite namjenu';
?>
<section class="cat-subcats" id="katalog">
  <div class="cat-subcats__inner">
    <p class="cat-subcats__label"><?php echo esc_html( $label ); ?></p>
    <div class="cat-subcats__grid">
      <?php
      foreach ( $children as $child ) :
        $link = get_term_link( $child );
        if ( is_wp_error( $link ) ) {
          continue;
        }
        $thumb = get_term_meta( $child->term_id, 'thumbnail_id', true );
        ?>
        <a href="<?php echo esc_url( $link ); ?>" class="cat-subcat-card">
          <?php
          if ( $thumb ) {
            echo wp_get_attachment_image( $thumb, 'medium', false, array( 'class' => 'cat-subcat-card__img', 'loading' => 'lazy', 'alt' => esc_attr( $child->name ) ) );
          }
          ?>
          <div class="cat-subcat-card__overlay"></div>
          <div class="cat-subcat-card__body">
            <p class="cat-subcat-card__name"><?php echo esc_html( $child->name ); ?></p>
            <?php if ( $child->count > 0 ) : ?>
              <p class="cat-subcat-card__count"><?php echo esc_html( $child->count . ' ' . _n( 'proizvod', 'proizvoda', $child->count, 'door-expert' ) ); ?></p>
            <?php endif; ?>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
