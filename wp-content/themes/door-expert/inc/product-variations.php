<?php
/**
 * Varijabilni proizvodi: naručljivost i tekst dostupnosti.
 *
 * Quote model: korpa je UPIT, ne naplata. Za vrata je zaliha informacija
 * ("Na stanju u Podgorici" / "Po narudžbi"), a ne prepreka - kupac koji pita za
 * vrata kojih trenutno nema i dalje je kupac.
 *
 * WC blokira rasprodato u WC_Cart::add_to_cart() bacanjem izuzetka na
 * `! $product_data->is_in_stock()`, i to PRIJE nego što se pozove filter
 * `woocommerce_add_to_cart_validation`. Zato se taj filter ovdje ne može
 * koristiti; jedina tačka koja radi je sam `is_in_stock()`.
 *
 * Posljedica: `is_in_stock()` za vrata više NIJE izvor istine za prikaz. Prikaz
 * (template-parts/product/single.php) čita sirovi `get_stock_status()`.
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Grupa proizvoda za bilo koji WC objekat, uključujući varijaciju.
 *
 * Varijacije nemaju svoje `product_cat` termove, pa se penjemo na roditelja.
 *
 * @param WC_Product $product Proizvod ili varijacija.
 * @return string Grupa (`vrata`, `plocice`, `umivaonik`) ili prazan string.
 */
function door_expert_group_for_product( $product ) {
	static $cache = array();

	if ( ! $product instanceof WC_Product || ! function_exists( 'door_expert_product_group' ) ) {
		return '';
	}

	$parent_id = $product->get_parent_id();
	$id        = $parent_id ? $parent_id : $product->get_id();

	if ( ! isset( $cache[ $id ] ) ) {
		$cache[ $id ] = door_expert_product_group( $id );
	}

	return $cache[ $id ];
}

/**
 * Vrata su uvijek naručljiva, bez obzira na zalihu.
 *
 * Rano izlazimo kad je proizvod ionako na stanju, pa se skupo računanje grupe
 * (termovi + penjanje do pretka) dešava samo za rasprodate proizvode.
 *
 * @param bool       $is_in_stock Zatečena vrijednost.
 * @param WC_Product $product     Proizvod ili varijacija.
 * @return bool
 */
function door_expert_doors_always_orderable( $is_in_stock, $product ) {
	if ( $is_in_stock ) {
		return $is_in_stock;
	}

	return 'vrata' === door_expert_group_for_product( $product ) ? true : $is_in_stock;
}
add_filter( 'woocommerce_product_is_in_stock', 'door_expert_doors_always_orderable', 10, 2 );

/**
 * Tekst dostupnosti prati SIROVI stock status, ne filtriranu naručljivost.
 *
 * Bez ovoga bi WC za rasprodata vrata ispisao prazno (= ima na stanju), jer mu
 * `is_in_stock()` filterom iznad vraća true.
 *
 * @param array      $availability Niz sa `availability` i `class`.
 * @param WC_Product $product      Proizvod ili varijacija.
 * @return array
 */
function door_expert_availability_text( $availability, $product ) {
	if ( ! $product instanceof WC_Product ) {
		return $availability;
	}

	if ( 'instock' === $product->get_stock_status() ) {
		return $availability;
	}

	if ( 'vrata' !== door_expert_group_for_product( $product ) ) {
		return $availability;
	}

	$display = door_expert_stock_display( $product->get_stock_status() );

	$availability['availability'] = $display['label'];
	$availability['class']        = 'available-on-backorder';

	return $availability;
}
add_filter( 'woocommerce_get_availability', 'door_expert_availability_text', 10, 2 );

/**
 * Sirovi stock status svake varijacije u JSON koji ide na stranicu.
 *
 * Nuzno jer `is_in_stock` u tom JSON-u prolazi kroz nas filter iznad i za vrata je
 * uvijek true. Bez ovoga JS ne moze razlikovati rasprodatu varijaciju od dostupne,
 * pa bi blok dostupnosti tvrdio "Na stanju" i za rasprodatu dimenziju.
 *
 * @param array                $data      Podaci varijacije za JSON.
 * @param WC_Product_Variable  $product   Roditeljski proizvod.
 * @param WC_Product_Variation $variation Varijacija.
 * @return array
 */
function door_expert_variation_stock_data( $data, $product, $variation ) {
	$data['door_expert_stock_status'] = $variation->get_stock_status();

	return $data;
}
add_filter( 'woocommerce_available_variation', 'door_expert_variation_stock_data', 10, 3 );
