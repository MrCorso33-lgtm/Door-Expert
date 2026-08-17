<?php
/**
 * Brand single – uniforman prikaz brend stranice (svih 5 brendova).
 *
 * Podatke prima preko $args (door_expert_brand_content($slug)). Prazan → ne renderuje.
 * Hero/CTA gradijent i hero slika idu kao CSS custom properties na .brand-page wrapperu.
 * CSS: assets/css/brand.css (mobile-first).
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$b = is_array( $args ) ? $args : array();
if ( empty( $b['title'] ) ) {
	return;
}

$de_style = sprintf(
	"--brand-grad-a:%s;--brand-grad-b:%s;--brand-hero-img:url('%s');",
	esc_attr( $b['grad_a'] ),
	esc_attr( $b['grad_b'] ),
	esc_url( $b['hero_img'] )
);
?>

<div class="brand-page" style="<?php echo $de_style; // phpcs:ignore WordPress.Security.EscapeOutput -- vrijednosti već escape-ovane iznad. ?>">

  <!-- HERO -->
  <section class="brand-hero">
    <div class="brand-hero__content">
      <nav class="brand-hero__breadcrumb">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Početna</a> <span>/</span>
        <a href="<?php echo esc_url( home_url( '/brendovi/' ) ); ?>">Brendovi</a> <span>/</span>
        <span style="color:#fff;"><?php echo esc_html( $b['title'] ); ?></span>
      </nav>
      <div class="brand-hero__badge"><?php echo esc_html( $b['badge'] ); ?></div>
      <h1 class="brand-hero__title"><?php echo esc_html( $b['title'] ); ?></h1>
      <p class="brand-hero__subtitle"><?php echo esc_html( $b['subtitle'] ); ?></p>
    </div>
  </section>

  <!-- ABOUT -->
  <section class="brand-about">
    <div class="brand-about__grid">
      <div class="brand-about__text">
        <h2><?php echo esc_html( $b['about_title'] ); ?></h2>
        <?php foreach ( $b['about'] as $de_para ) : ?>
          <p><?php echo esc_html( $de_para ); ?></p>
        <?php endforeach; ?>
        <div class="brand-about__facts">
          <?php foreach ( $b['facts'] as $de_fact ) : ?>
            <div class="brand-about__fact"><div class="brand-about__fact-label"><?php echo esc_html( $de_fact['label'] ); ?></div><div class="brand-about__fact-value"><?php echo esc_html( $de_fact['value'] ); ?></div></div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="brand-about__image">
        <img src="<?php echo esc_url( $b['about_img'] ); ?>" alt="<?php echo esc_attr( $b['about_img_alt'] ); ?>" loading="lazy" />
      </div>
    </div>
  </section>

  <!-- COLLECTIONS -->
  <section class="brand-collections">
    <div class="brand-collections__inner">
      <div class="brand-collections__header">
        <p class="brand-collections__label">Kolekcije</p>
        <h2 class="brand-collections__title"><?php echo esc_html( $b['collections_title'] ); ?></h2>
      </div>
      <div class="brand-collections__grid">
        <?php foreach ( $b['collections'] as $de_col ) : ?>
          <div class="collection-card">
            <div class="collection-card__image"><img src="<?php echo esc_url( $de_col['img'] ); ?>" alt="<?php echo esc_attr( $de_col['alt'] ); ?>" loading="lazy" /></div>
            <div class="collection-card__body"><h3 class="collection-card__name"><?php echo esc_html( $de_col['name'] ); ?></h3><p class="collection-card__desc"><?php echo esc_html( $de_col['desc'] ); ?></p></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- VALUES -->
  <section class="brand-values">
    <div class="brand-values__header">
      <h2 class="brand-values__title"><?php echo esc_html( $b['values_title'] ); ?></h2>
    </div>
    <div class="brand-values__grid">
      <?php foreach ( $b['values'] as $de_val ) : ?>
        <div class="value-card"><div class="value-card__icon"><?php echo esc_html( $de_val['icon'] ); ?></div><h3 class="value-card__title"><?php echo esc_html( $de_val['title'] ); ?></h3><p class="value-card__desc"><?php echo esc_html( $de_val['desc'] ); ?></p></div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- PRODUCTS CTA -->
  <section class="brand-products-cta">
    <h2><?php echo esc_html( $b['cta_title'] ); ?></h2>
    <p><?php echo esc_html( $b['cta_text'] ); ?></p>
    <div class="brand-products-cta__buttons">
      <a href="<?php echo esc_url( door_expert_cat_url( $b['cta_cat'] ) ); ?>" class="brand-products-cta__btn brand-products-cta__btn--primary">
        Pogledaj proizvode
      </a>
      <a href="<?php echo esc_url( home_url( '/kontakt/' ) ); ?>" class="brand-products-cta__btn brand-products-cta__btn--secondary">
        Posjetite salon
      </a>
    </div>
  </section>

</div>
