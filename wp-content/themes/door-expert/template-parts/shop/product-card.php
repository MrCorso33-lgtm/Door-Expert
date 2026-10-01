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

// Čipovi ispod naziva, do 3 komada, svaki kao par "Labela: vrijednost".
// Koji atributi – bira se u adminu: Proizvodi → Istaknuti atributi, po kategoriji
// proizvoda (potkategorija bez svog podešavanja nasljeđuje od pretka). Isti izvor
// kao traka na stranici proizvoda, pa listing i PDP govore istu stvar.
//
// ODSTUPANJE OD PROTOTIPA (traženo): prototip ima čipove samo sa vrijednošću
// ("Puno drvo"), ovdje ide i labela ("Materijal: Puno drvo") jer gola vrijednost
// ne kaže o kom atributu je riječ. Stil čipa ostaje isti kao u prototipu.
$de_attrs = function_exists( 'door_expert_product_highlight_chips' )
	? door_expert_product_highlight_chips( $product )
	: array();

// Fallback dok kategorija (ni njeni pretci, ni Podrazumijevano) nema podešavanje,
// ili kad proizvod nema nijednu od izabranih vrijednosti: zatečeno ponašanje –
// dimenzije + boja. Dimenzije su dva odvojena atributa (vrata / pločica) –
// proizvod ima jedan od njih.
if ( empty( $de_attrs ) ) {
	$de_dim_attr = 'pa_dimenzije-vrata';
	$de_dim      = $product->get_attribute( $de_dim_attr );
	if ( '' === $de_dim ) {
		$de_dim_attr = 'pa_dimenzije-plocica';
		$de_dim      = $product->get_attribute( $de_dim_attr );
	}
	// Jedan čip po atributu, sa svim vrijednostima – kao i kod istaknutih atributa.
	if ( '' !== $de_dim ) {
		$de_attrs[] = array(
			'label' => wc_attribute_label( $de_dim_attr ),
			'value' => trim( preg_replace( '/\s*,\s*/', ', ', $de_dim ) ),
		);
	}
	$de_boja = $product->get_attribute( 'pa_boja' );
	if ( '' !== $de_boja ) {
		$de_attrs[] = array(
			'label' => wc_attribute_label( 'pa_boja' ),
			'value' => trim( preg_replace( '/\s*,\s*/', ', ', $de_boja ) ),
		);
	}
	$de_attrs = array_slice( $de_attrs, 0, DOOR_EXPERT_HIGHLIGHTS_CHIPS );
}

// Slika.
// Namjerno 'woocommerce_single' (ne 'woocommerce_thumbnail'): WC thumbnail je hard-crop
// (1:1 po Customizer podešavanju) pa bi proizvod bio odsječen. 'woocommerce_single' je
// skaliran samo po širini => cijela slika. Uklapanje u okvir radi CSS (object-fit: contain).
$de_img_id = $product->get_image_id();
$de_img    = $de_img_id
	? wp_get_attachment_image(
		$de_img_id,
		'woocommerce_single',
		false,
		array(
			'class'   => 'prod-card__img',
			'loading' => 'lazy',
			'alt'     => esc_attr( $product->get_name() ),
		)
	)
	: '<img class="prod-card__img" src="' . esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ) . '" alt="" loading="lazy" />';
?>

<article class="prod-card" data-cat="<?php echo esc_attr( $de_data_cat ); ?>">
  <div class="prod-card__img-wrap">
    <a href="<?php echo esc_url( $de_link ); ?>" aria-label="<?php echo esc_attr( $product->get_name() ); ?>"><?php echo $de_img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image je već escaped. ?></a>
    <?php
    /*
     * Popust i zaliha za bedževe. Popust radi i za varijabilni proizvod (inc/product.php):
     * "-6%" kad je cijeli proizvod snižen isto, "do -6%" kad je snižen samo dio varijacija.
     *
     * Zaliha čita SIROVI status, ne is_in_stock(): inc/product-variations.php namjerno
     * drži vrata uvijek naručljivim, pa je is_in_stock() za vrata uvijek true i kartica
     * je pisala "Na stanju" i za rasprodata. Isto pravilo kao na PDP-u.
     */
    $de_discount       = function_exists( 'door_expert_product_discount' ) ? door_expert_product_discount( $product ) : null;
    $de_discount_label = $de_discount ? ( $de_discount['uniform'] ? '' : 'do ' ) . '-' . $de_discount['pct'] . '%' : '';
    ?>
    <div class="prod-card__badges">
      <?php if ( $de_discount ) : ?>
        <span class="prod-badge prod-badge--sale"><?php echo esc_html( $de_discount_label ); ?></span>
      <?php endif; ?>
      <?php if ( 'instock' === $product->get_stock_status() ) : ?>
        <span class="prod-badge prod-badge--stock"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg> Na stanju</span>
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
      <?php
      /*
       * Atributi jedan ispod drugog, sa labelama ("Materijal: Puno drvo"). Lista, jer to
       * i jeste spisak osobina. Markup u jednom redu po stavci: prelom unutar <li>
       * bi dodao razmak ispred vrijednosti.
       */
      ?>
      <ul class="prod-card__attrs">
        <?php foreach ( $de_attrs as $de_attr ) : ?>
          <li class="prod-card__attr"><?php if ( '' !== $de_attr['label'] ) : ?><span class="prod-card__attr-key"><?php echo esc_html( $de_attr['label'] ); ?>:</span> <?php endif; ?><?php echo esc_html( $de_attr['value'] ); ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
    <div class="prod-card__price-row">
      <?php if ( $de_discount && $de_discount['single'] ) : ?>
        <?php // Jedna cijena za cijeli proizvod: nova, pa precrtana stara, kao u prototipu. ?>
        <span class="prod-card__price"><?php echo wp_kses_post( wc_price( $de_discount['price'] ) ); ?></span>
        <span class="prod-card__price-old"><?php echo wp_kses_post( wc_price( $de_discount['regular'] ) ); ?></span>
      <?php else : ?>
        <?php // Raspon (varijacije sa različitim cijenama) ili cijena bez popusta; "Cijena na upit" rješava filter u inc/quote-cart.php. ?>
        <span class="prod-card__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
      <?php endif; ?>
      <?php if ( $de_discount ) : ?>
        <span class="prod-card__price-save"><?php echo esc_html( $de_discount_label ); ?></span>
      <?php endif; ?>
    </div>
    <?php
    /*
     * Pravo WooCommerce dodavanje (add_to_cart_button + ajax_add_to_cart), izgled sa
     * početne. Varijabilni proizvod AJAX ne podržava, pa ga ovo dugme vodi na PDP gdje
     * se bira dimenzija – to je WC ponašanje add_to_cart_url().
     */
    ?>
    <a href="<?php echo esc_url( $product->add_to_cart_url() ); ?>" data-quantity="1" data-product_id="<?php echo esc_attr( (string) $de_id ); ?>" class="prod-card__add add_to_cart_button<?php echo $product->supports( 'ajax_add_to_cart' ) && $product->is_purchasable() && $product->is_in_stock() ? ' ajax_add_to_cart' : ''; ?>" rel="nofollow">
      <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 001.95-1.57l1.65-8.42H6"/></svg>
      Dodaj u ponudu
    </a>
  </div>
</article>
