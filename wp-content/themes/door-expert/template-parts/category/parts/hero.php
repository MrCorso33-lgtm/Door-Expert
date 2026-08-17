<?php
/**
 * Kategorijski hero (subcat-hero). Podaci: $args['hero'], fallback $args['term']->name.
 * @package DoorExpert
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hero  = isset( $args['hero'] ) ? $args['hero'] : array();
$term  = isset( $args['term'] ) ? $args['term'] : null;
$title = ! empty( $hero['title'] ) ? $hero['title'] : ( $term ? esc_html( $term->name ) : '' );
?>
<section class="subcat-hero">
  <div class="subcat-hero__inner">
    <div class="subcat-hero__content">
      <?php if ( ! empty( $hero['label'] ) ) : ?>
        <p class="subcat-hero__label">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
          <?php echo esc_html( $hero['label'] ); ?>
        </p>
      <?php endif; ?>
      <h1 class="subcat-hero__title"><?php echo wp_kses( $title, array( 'br' => array() ) ); ?></h1>
      <?php if ( ! empty( $hero['desc'] ) ) : ?>
        <p class="subcat-hero__desc"><?php echo esc_html( $hero['desc'] ); ?></p>
      <?php endif; ?>
      <?php if ( ! empty( $hero['badges'] ) ) : ?>
        <div class="subcat-hero__badges">
          <?php foreach ( $hero['badges'] as $badge ) :
            $cls = 'subcat-hero__badge' . ( ! empty( $badge['accent'] ) ? ' subcat-hero__badge--accent' : '' );
            ?>
            <span class="<?php echo esc_attr( $cls ); ?>"><?php
              if ( ! empty( $badge['dot'] ) ) {
                echo '<span class="dot"></span>';
              }
              echo esc_html( $badge['text'] );
            ?></span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
    <?php if ( ! empty( $hero['img'] ) ) : ?>
      <div class="subcat-hero__visual">
        <img class="subcat-hero__img" src="<?php echo esc_url( $hero['img'] ); ?>" alt="<?php echo esc_attr( isset( $hero['img_alt'] ) ? $hero['img_alt'] : '' ); ?>" loading="eager" fetchpriority="high" />
        <?php if ( ! empty( $hero['img_badge_strong'] ) ) : ?>
          <div class="subcat-hero__img-badge">
            <strong><?php echo esc_html( $hero['img_badge_strong'] ); ?></strong>
            <?php echo esc_html( isset( $hero['img_badge_text'] ) ? $hero['img_badge_text'] : '' ); ?>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
