<?php
/**
 * Shop kartica proizvoda – renderuje .prod-card markup (stil iz category.css) iz WC_Product.
 * Koristi globalni $product (postavljen u WC loop-u archive-product.php).
 *
 * NAPOMENA: keramika ima cijenu "po m²" u prototipu; WC cijena je po jedinici mjere,
 * pa se ovdje prikazuje kako je unijeta. Sufiks /m² se dodaje u produkciji preko meta
 * (npr. _de_price_unit) ako zatreba – ne pretpostavljamo ga sada.
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

if ( ! $product instanceof WC_Product ) {
	$product = wc_get_product( get_the_ID() );
}

if ( ! $product instanceof WC_Product ) {
	return;
}

$de_id   = $product->get_id();
$de_link = get_permalink( $de_id );

// Kategorija (prva ne-uncategorized) + brend za labelu i data-atribut.
$de_cat_label = '';
$de_data_cat  = '';
$de_cats      = get_the_terms( $de_id, 'product_cat' );
if ( is_array( $de_cats ) ) {
	foreach ( $de_cats as $de_c ) {
		if ( 'uncategorized' !== $de_c->slug ) {
			$de_cat_label = $de_c->name;
			$de_data_cat  = $de_c->slug;
			break;
		}
	}
}

$de_brand_label = '';
$de_brands      = taxonomy_exists( 'product_brand' ) ? get_the_terms( $de_id, 'product_brand' ) : false;
if ( is_array( $de_brands ) && ! empty( $de_brands ) ) {
	$de_brand_label = $de_brands[0]->name;
}

$de_full_label = $de_cat_label;
if ( '' !== $de_brand_label ) {
	$de_full_label = '' !== $de_cat_label ? $de_cat_label . ' · ' . $de_brand_label : $de_brand_label;
}

// Atributi za prikaz (dimenzije + boja), do 3 čipa.
$de_attrs = array();
$de_dim   = $product->get_attribute( 'pa_dimenzije' );
if ( '' !== $de_dim ) {
	foreach ( array_slice( array_map( 'trim', explode( ',', $de_dim ) ), 0, 2 ) as $de_v ) {
		$de_attrs[] = $de_v;
	}
}
$de_boja = $product->get_attribute( 'pa_boja' );
if ( '' !== $de_boja ) {
	$de_first_boja = trim( current( explode( ',', $de_boja ) ) );
	if ( '' !== $de_first_boja ) {
		$de_attrs[] = $de_first_boja;
	}
}
$de_attrs = array_slice( $de_attrs, 0, 3 );

// Slika.
$de_img_id = $product->get_image_id();
$de_img    = $de_img_id
	? wp_get_attachment_image(
		$de_img_id,
		'woocommerce_thumbnail',
		false,
		array(
			'class'   => 'prod-card__img',
			'loading' => 'lazy',
			'alt'     => esc_attr( $product->get_name() ),
		)
	)
	: '<img class="prod-card__img" src="' . esc_url( wc_placeholder_img_src( 'woocommerce_thumbnail' ) ) . '" alt="" loading="lazy" />';
?>

<article class="prod-card" data-cat="<?php echo esc_attr( $de_data_cat ); ?>">
  <div class="prod-card__img-wrap">
    <a href="<?php echo esc_url( $de_link ); ?>" aria-label="<?php echo esc_attr( $product->get_name() ); ?>"><?php echo $de_img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image je već escaped. ?></a>
    <div class="prod-card__badges">
      <?php if ( $product->is_in_stock() ) : ?>
        <span class="prod-badge prod-badge--stock"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg> Na stanju</span>
      <?php else : ?>
        <span class="prod-badge prod-badge--order">Po narudžbi</span>
      <?php endif; ?>
      <?php if ( $product->is_featured() ) : ?>
        <span class="prod-badge prod-badge--new">Novo</span>
      <?php endif; ?>
    </div>
    <button class="prod-card__wishlist" aria-label="Dodaj u listu želja"><svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg></button>
  </div>
  <div class="prod-card__body">
    <?php if ( '' !== $de_full_label ) : ?>
      <span class="prod-card__cat"><?php echo esc_html( $de_full_label ); ?></span>
    <?php endif; ?>
    <h3 class="prod-card__name"><a href="<?php echo esc_url( $de_link ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h3>
    <?php if ( ! empty( $de_attrs ) ) : ?>
      <div class="prod-card__attrs">
        <?php foreach ( $de_attrs as $de_attr ) : ?>
          <span class="prod-card__attr"><?php echo esc_html( $de_attr ); ?></span>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <div class="prod-card__price-row">
      <?php if ( $product->is_on_sale() && '' !== (string) $product->get_regular_price() && '' !== (string) $product->get_sale_price() ) : ?>
        <?php
        $de_reg  = (float) $product->get_regular_price();
        $de_sale = (float) $product->get_sale_price();
        $de_pct  = ( $de_reg > 0 ) ? (int) round( ( ( $de_reg - $de_sale ) / $de_reg ) * 100 ) : 0;
        ?>
        <span class="prod-card__price-old"><?php echo wp_kses_post( wc_price( $de_reg ) ); ?></span>
        <span class="prod-card__price"><?php echo wp_kses_post( wc_price( $de_sale ) ); ?></span>
        <?php if ( $de_pct > 0 ) : ?>
          <span class="prod-card__price-save">-<?php echo esc_html( (string) $de_pct ); ?>%</span>
        <?php endif; ?>
      <?php else : ?>
        <span class="prod-card__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
      <?php endif; ?>
    </div>
    <div class="prod-card__cta">
      <a href="<?php echo esc_url( $product->add_to_cart_url() ); ?>" data-quantity="1" data-product_id="<?php echo esc_attr( (string) $de_id ); ?>" class="prod-card__btn-cart add_to_cart_button<?php echo $product->supports( 'ajax_add_to_cart' ) && $product->is_purchasable() && $product->is_in_stock() ? ' ajax_add_to_cart' : ''; ?>" rel="nofollow">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
        Dodaj u ponudu
      </a>
      <a href="<?php echo esc_url( $de_link ); ?>" class="prod-card__btn-view" aria-label="Pogledaj <?php echo esc_attr( $product->get_name() ); ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></a>
    </div>
  </div>
</article>
