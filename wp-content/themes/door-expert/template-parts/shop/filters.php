<?php
/**
 * Filter sidebar – GET forma oko sidebara koji renderuje plugin WC Filter Configurator.
 * Koristi se na shop arhivi I na kategorijskim stranicama (vidi $args ispod).
 *
 * PODJELA POSLA:
 *   Plugin  – koje grupe, kojim redom, koje labele, koji termovi i koji brojevi
 *             (Settings → Filter Configurator, bez diranja koda).
 *   Tema    – forma oko toga, grupa "Dostupnost" (stock nije taksonomija pa je plugin
 *             ne poznaje), hidden inputi koji čuvaju f_cat/orderby, dugme Primijeni.
 *   inc/filters.php – dorada plugin markupa (multi-select, checked stanje, cijena).
 *   inc/shop.php    – prevođenje GET parametara u WP_Query.
 *
 * Ranije je ovaj fajl sam gradio sve grupe iz get_terms() bez ikakvog opsega, pa je
 * na svakoj stranici nudio termove sa cijelog sajta (keramički brendovi na sobnim
 * vratima, brojevi iz cijele baze). Plugin to rješava opsegom po kategoriji.
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Koristi se i na shop arhivi i na kategorijskim stranicama.
 *   $args['base_url'] – gdje forma šalje (default: tekući listing).
 *   $args['hide']     – grupe koje se ne prikazuju, npr. array( 'cat' ) na kategoriji
 *                       gdje je filter po kategoriji redundantan.
 */
$de_action = isset( $args['base_url'] ) ? $args['base_url'] : door_expert_listing_base_url();
$de_hide   = isset( $args['hide'] ) && is_array( $args['hide'] ) ? $args['hide'] : array();

// 'cat' je skraćenica šablona; plugin grupu zove po atributu koji renderuje.
door_expert_filter_hidden_groups(
	in_array( 'cat', $de_hide, true ) ? array( 'product_cat' ) : array()
);

/*
 * Parametri koje forma sama posjeduje preko svojih polja, pa ih NE dupliramo kao
 * hidden input. Sve ostalo (f_cat sa hero pilula, orderby iz toolbara) se mirroruje
 * da se ne izgubi pri submitu.
 *
 * Taksonomija koja nije u sidebaru namjerno ispada iz URL-a: nevidljiv filter koji
 * i dalje sužava rezultate je gori od izgubljenog filtera.
 */
$de_owned = array_merge(
	door_expert_filter_taxonomies(),
	array( 'f_stock', 'min_price', 'max_price' )
);
?>

<form class="shop-filters" id="shopFilters" method="get" action="<?php echo esc_url( $de_action ); ?>">
  <div class="shop-filters__header">
    <h2 class="shop-filters__title">Filteri</h2>
    <a class="shop-filters__clear" href="<?php echo esc_url( $de_action ); ?>">Očisti sve</a>
  </div>

  <?php
  if ( door_expert_filters_plugin_active() ) {
      // Plugin sam escape-uje svoj markup; inc/filters.php doradu takođe escape-uje.
      wcfc_render_sidebar();
  } elseif ( current_user_can( 'manage_options' ) ) {
      echo '<p class="shop-filters__notice">Plugin <strong>WC Filter Configurator</strong> nije aktivan, pa se prikazuje samo dostupnost. Aktivirajte ga u Dodaci.</p>';
  }
  ?>

  <?php
  echo door_expert_filter_stock_group(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markup sklopljen i escape-ovan u inc/filters.php.
  ?>

  <?php door_expert_shop_hidden_inputs( $de_owned ); ?>
  <button type="submit" class="shop-filters__apply">Primijeni filtere</button>
</form>
