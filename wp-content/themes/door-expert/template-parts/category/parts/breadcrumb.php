<?php
/**
 * Breadcrumb (subcat-breadcrumb) – dinamički iz hijerarhije terma.
 * Početna → [roditelji] → tekući term. Linkovi preko get_term_link.
 * @package DoorExpert
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$term = isset( $args['term'] ) ? $args['term'] : get_queried_object();
if ( ! ( $term instanceof WP_Term ) ) {
	return;
}
$ancestors = array_reverse( get_ancestors( $term->term_id, 'product_cat' ) );
?>
<nav class="subcat-breadcrumb" aria-label="Navigacioni put">
  <div class="subcat-breadcrumb__inner">
    <ol class="subcat-breadcrumb__list">
      <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Početna</a></li>
      <?php
      foreach ( $ancestors as $aid ) :
        $anc = get_term( $aid, 'product_cat' );
        if ( ! $anc || is_wp_error( $anc ) ) {
          continue;
        }
        $anc_link = get_term_link( $anc );
        if ( is_wp_error( $anc_link ) ) {
          continue;
        }
        ?>
        <li class="subcat-breadcrumb__sep" aria-hidden="true">/</li>
        <li><a href="<?php echo esc_url( $anc_link ); ?>"><?php echo esc_html( $anc->name ); ?></a></li>
      <?php endforeach; ?>
      <li class="subcat-breadcrumb__sep" aria-hidden="true">/</li>
      <li class="subcat-breadcrumb__current" aria-current="page"><?php echo esc_html( $term->name ); ?></li>
    </ol>
  </div>
</nav>
