<?php
/**
 * Shop filter sidebar – GET forma, termovi iz taksonomija, checked stanje iz URL-a.
 * Submit ide na shop arhivu; door_expert_shop_filter_query() prevodi parametre u WP_Query.
 *
 * Grupe: Kategorija (product_cat, top-level) · Brend (product_brand) · Boja (pa_boja) ·
 *        Cijena (_price) · Dimenzije vrata/pločica (pa_dimenzije-vrata / -plocica) · Dostupnost (stock status).
 * Prazne grupe (nepostojeća taksonomija / bez termova) se preskaču.
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$de_sel_cat      = door_expert_shop_selected( 'f_cat' );
$de_sel_brand    = door_expert_shop_selected( 'f_brand' );
$de_sel_boja     = door_expert_shop_selected( 'f_boja' );
$de_sel_dim_vr   = door_expert_shop_selected( 'f_dim_vrata' );
$de_sel_dim_pl   = door_expert_shop_selected( 'f_dim_plocica' );
$de_sel_stock    = door_expert_shop_selected( 'f_stock' );
$de_min       = door_expert_shop_price( 'min_price' );
$de_max       = door_expert_shop_price( 'max_price' );

// Termovi.
$de_cats = get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => false,
		'parent'     => 0,
	)
);
$de_cats = is_wp_error( $de_cats ) ? array() : $de_cats;

$de_brands = taxonomy_exists( 'product_brand' )
	? get_terms( array( 'taxonomy' => 'product_brand', 'hide_empty' => false ) )
	: array();
$de_brands = is_wp_error( $de_brands ) ? array() : $de_brands;

$de_bojas = taxonomy_exists( 'pa_boja' )
	? get_terms( array( 'taxonomy' => 'pa_boja', 'hide_empty' => false ) )
	: array();
$de_bojas = is_wp_error( $de_bojas ) ? array() : $de_bojas;

$de_dims_vr = taxonomy_exists( 'pa_dimenzije-vrata' )
	? get_terms( array( 'taxonomy' => 'pa_dimenzije-vrata', 'hide_empty' => false ) )
	: array();
$de_dims_vr = is_wp_error( $de_dims_vr ) ? array() : $de_dims_vr;

$de_dims_pl = taxonomy_exists( 'pa_dimenzije-plocica' )
	? get_terms( array( 'taxonomy' => 'pa_dimenzije-plocica', 'hide_empty' => false ) )
	: array();
$de_dims_pl = is_wp_error( $de_dims_pl ) ? array() : $de_dims_pl;

// Mapa slug => hex za swatch (fallback neutralna siva).
$de_boja_hex = array(
	'bijela'   => '#FFFFFF',
	'krema'    => '#F5E6C8',
	'orah'     => '#8B5E3C',
	'wenge'    => '#3D2B1F',
	'hrast'    => '#D4C5A9',
	'antracit' => '#2C2C2C',
	'beige'    => '#C9B896',
	'plava'    => '#6B8E9B',
);
?>

<form class="shop-filters" id="shopFilters" method="get" action="<?php echo esc_url( door_expert_shop_base_url() ); ?>">
  <div class="shop-filters__header">
    <h2 class="shop-filters__title">Filteri</h2>
    <a class="shop-filters__clear" href="<?php echo esc_url( door_expert_shop_base_url() ); ?>">Očisti sve</a>
  </div>

  <?php if ( ! empty( $de_cats ) ) : ?>
    <!-- Kategorija -->
    <div class="shop-filter-group is-open">
      <button type="button" class="shop-filter-group__toggle">
        Kategorija
        <svg viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
      </button>
      <div class="shop-filter-group__body">
        <?php foreach ( $de_cats as $de_term ) : ?>
          <?php if ( 'uncategorized' === $de_term->slug ) { continue; } ?>
          <label class="shop-filter-option">
            <input type="checkbox" name="f_cat[]" value="<?php echo esc_attr( $de_term->slug ); ?>" <?php checked( in_array( $de_term->slug, $de_sel_cat, true ) ); ?> />
            <?php echo esc_html( $de_term->name ); ?>
            <span class="shop-filter-option__count"><?php echo (int) $de_term->count; ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <?php if ( ! empty( $de_brands ) ) : ?>
    <!-- Brend -->
    <div class="shop-filter-group is-open">
      <button type="button" class="shop-filter-group__toggle">
        Brend
        <svg viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
      </button>
      <div class="shop-filter-group__body">
        <?php foreach ( $de_brands as $de_term ) : ?>
          <label class="shop-filter-option">
            <input type="checkbox" name="f_brand[]" value="<?php echo esc_attr( $de_term->slug ); ?>" <?php checked( in_array( $de_term->slug, $de_sel_brand, true ) ); ?> />
            <?php echo esc_html( $de_term->name ); ?>
            <span class="shop-filter-option__count"><?php echo (int) $de_term->count; ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <?php if ( ! empty( $de_bojas ) ) : ?>
    <!-- Boja -->
    <div class="shop-filter-group">
      <button type="button" class="shop-filter-group__toggle">
        Boja
        <svg viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
      </button>
      <div class="shop-filter-group__body">
        <div class="shop-filter-colors">
          <?php foreach ( $de_bojas as $de_term ) : ?>
            <?php $de_hex = isset( $de_boja_hex[ $de_term->slug ] ) ? $de_boja_hex[ $de_term->slug ] : '#CCCCCC'; ?>
            <label class="shop-filter-color<?php echo in_array( $de_term->slug, $de_sel_boja, true ) ? ' is-active' : ''; ?>" style="background:<?php echo esc_attr( $de_hex ); ?>;" title="<?php echo esc_attr( $de_term->name ); ?>">
              <input type="checkbox" name="f_boja[]" value="<?php echo esc_attr( $de_term->slug ); ?>" <?php checked( in_array( $de_term->slug, $de_sel_boja, true ) ); ?> hidden />
            </label>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- Cijena -->
  <div class="shop-filter-group">
    <button type="button" class="shop-filter-group__toggle">
      Cijena (EUR)
      <svg viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
    </button>
    <div class="shop-filter-group__body">
      <div class="shop-filter-price">
        <input type="number" name="min_price" placeholder="Od" min="0" value="<?php echo $de_min > 0 ? esc_attr( (string) $de_min ) : ''; ?>" />
        <span>-</span>
        <input type="number" name="max_price" placeholder="Do" min="0" value="<?php echo $de_max > 0 ? esc_attr( (string) $de_max ) : ''; ?>" />
        <span>EUR</span>
      </div>
    </div>
  </div>

  <?php if ( ! empty( $de_dims_vr ) ) : ?>
    <!-- Dimenzije vrata -->
    <div class="shop-filter-group">
      <button type="button" class="shop-filter-group__toggle">
        Dimenzije vrata
        <svg viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
      </button>
      <div class="shop-filter-group__body">
        <?php foreach ( $de_dims_vr as $de_term ) : ?>
          <label class="shop-filter-option">
            <input type="checkbox" name="f_dim_vrata[]" value="<?php echo esc_attr( $de_term->slug ); ?>" <?php checked( in_array( $de_term->slug, $de_sel_dim_vr, true ) ); ?> />
            <?php echo esc_html( $de_term->name ); ?>
            <span class="shop-filter-option__count"><?php echo (int) $de_term->count; ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <?php if ( ! empty( $de_dims_pl ) ) : ?>
    <!-- Dimenzije pločica -->
    <div class="shop-filter-group">
      <button type="button" class="shop-filter-group__toggle">
        Dimenzije pločica
        <svg viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
      </button>
      <div class="shop-filter-group__body">
        <?php foreach ( $de_dims_pl as $de_term ) : ?>
          <label class="shop-filter-option">
            <input type="checkbox" name="f_dim_plocica[]" value="<?php echo esc_attr( $de_term->slug ); ?>" <?php checked( in_array( $de_term->slug, $de_sel_dim_pl, true ) ); ?> />
            <?php echo esc_html( $de_term->name ); ?>
            <span class="shop-filter-option__count"><?php echo (int) $de_term->count; ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- Dostupnost -->
  <div class="shop-filter-group">
    <button type="button" class="shop-filter-group__toggle">
      Dostupnost
      <svg viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
    </button>
    <div class="shop-filter-group__body">
      <label class="shop-filter-option">
        <input type="checkbox" name="f_stock[]" value="na-stanju" <?php checked( in_array( 'na-stanju', $de_sel_stock, true ) ); ?> />
        Na stanju
      </label>
      <label class="shop-filter-option">
        <input type="checkbox" name="f_stock[]" value="po-narudzbi" <?php checked( in_array( 'po-narudzbi', $de_sel_stock, true ) ); ?> />
        Po narudžbi
      </label>
    </div>
  </div>

  <?php door_expert_shop_hidden_inputs( array( 'f_cat', 'f_brand', 'f_boja', 'f_dim_vrata', 'f_dim_plocica', 'f_stock', 'min_price', 'max_price' ) ); ?>
  <button type="submit" class="shop-filters__apply">Primijeni filtere</button>
</form>
