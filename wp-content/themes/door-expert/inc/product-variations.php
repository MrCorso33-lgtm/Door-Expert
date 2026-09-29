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

/**
 * Razrijesi varijaciju iz poslatih atributa kad `variation_id` nije popunjen.
 *
 * Bez JavaScripta `variation_id` ostaje 0 (WC-ov skriveni input), pa WooCommerce
 * pokusava sam da nadje varijaciju iz poslatih atributa. Na ovoj instalaciji ta
 * pretraga (`find_matching_product_variation()`) vraca 0 iako je atribut poslat
 * ispravno, pa kupac dobije "Please choose product options" iako JESTE izabrao
 * dimenziju. Provjereno POST-om: sa rucno postavljenim `variation_id` proizvod ulazi
 * u korpu, bez njega ne. Ta WC pretraga ide kroz `get_posts()`, pa je moze
 * poremetiti bilo koji plugin koji filtrira upite.
 *
 * Zato varijaciju nalazimo sami, iteracijom po djeci roditelja - bez upita koji neko
 * moze da filtrira. Ne diramo nista drugo: validaciju, zalihu i upis u korpu i dalje
 * radi WooCommerce.
 *
 * Kaci se na `wp_loaded` prioritet 19 jer WC_Form_Handler::add_to_cart_action() ide
 * na istom hooku sa prioritetom 20.
 *
 * Prazna vrijednost u varijaciji je WC-ov dzoker ("Bilo koja vrijednost"), ne
 * vrijednost - zato se preskace pri poredjenju.
 */
function door_expert_resolve_posted_variation() {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- WC-ov add-to-cart nema nonce; ovdje se samo normalizuje ulaz prije njegove validacije.
	if ( empty( $_REQUEST['add-to-cart'] ) || ! empty( $_REQUEST['variation_id'] ) ) {
		return;
	}

	if ( ! function_exists( 'wc_get_product' ) ) {
		return;
	}

	$product = wc_get_product( absint( wp_unslash( $_REQUEST['add-to-cart'] ) ) );

	if ( ! $product instanceof WC_Product || ! $product->is_type( 'variable' ) ) {
		return;
	}

	$posted = array();

	foreach ( $product->get_attributes() as $attribute ) {
		if ( ! $attribute->get_variation() ) {
			continue;
		}

		$key = 'attribute_' . sanitize_title( $attribute->get_name() );

		if ( empty( $_REQUEST[ $key ] ) ) {
			return; // Kupac nije izabrao sve; neka WC ispise svoju poruku.
		}

		$raw = wp_unslash( $_REQUEST[ $key ] );

		$posted[ $key ] = $attribute->is_taxonomy()
			? sanitize_title( $raw )
			: html_entity_decode( wc_clean( $raw ), ENT_QUOTES, get_bloginfo( 'charset' ) );
	}
	// phpcs:enable WordPress.Security.NonceVerification.Recommended

	if ( empty( $posted ) ) {
		return;
	}

	foreach ( $product->get_children() as $child_id ) {
		$variation = wc_get_product( $child_id );

		if ( ! $variation instanceof WC_Product_Variation ) {
			continue;
		}

		$match = true;

		foreach ( $variation->get_variation_attributes() as $key => $value ) {
			if ( '' === $value ) {
				continue; // Dzoker: ovoj varijaciji je svejedno.
			}

			if ( ! isset( $posted[ $key ] ) || $posted[ $key ] !== $value ) {
				$match = false;
				break;
			}
		}

		if ( $match ) {
			// WC cita $_REQUEST, ali ga PHP ne osvjezava sam kad se mijenja $_POST.
			$_POST['variation_id']    = $child_id;
			$_REQUEST['variation_id'] = $child_id;

			return;
		}
	}
}
add_action( 'wp_loaded', 'door_expert_resolve_posted_variation', 19 );

/**
 * Kompaktna mapa varijacija za sloj pilula.
 *
 * WC svoju mapu (`data-product_variations`) izostavlja iznad 30 varijacija i tada
 * prestaje da filtrira opcije u selectima. Kolekcija plocica sa 6 boja i 6 formata je
 * vec preko tog praga, pa pilule moraju imati svoj izvor podataka koji nikad ne
 * izostane.
 *
 * Namjerno NE koristimo get_available_variations(): ona po varijaciji nosi price_html,
 * availability_html, cijeli image objekat sa srcset-om i sizes-om, plus sve dimenzije i
 * tezinu. Za 60 varijacija je to preko 100 KB JSON-a u HTML-u, na stranici ciji je LCP
 * fotografija proizvoda. Ovdje je samo ono sto pilulama treba.
 *
 * Prazan string u `attrs` je WC-ov dzoker ("Bilo koja vrijednost"), ne vrijednost.
 *
 * @param WC_Product $product Roditeljski proizvod.
 * @return array<int,array{id:int,attrs:array<string,string>,price:float,stock:string}>
 */
function door_expert_variation_map( $product ) {
	if ( ! $product instanceof WC_Product || ! $product->is_type( 'variable' ) ) {
		return array();
	}

	$map = array();

	foreach ( $product->get_children() as $child_id ) {
		$variation = wc_get_product( $child_id );

		if ( ! $variation instanceof WC_Product_Variation ) {
			continue;
		}

		/*
		 * Namjerno NE koristimo variation_is_visible(): ona je false i kad je cijena
		 * prazna, pa bi dimenzija kojoj cijena jos nije unesena nestala sa PDP-a. U
		 * quote modelu je "cijena na upit" validno stanje. Nepublikovana varijacija je
		 * druga stvar i tu WC ima pravo.
		 */
		if ( 'publish' !== get_post_status( $child_id ) ) {
			continue;
		}

		$map[] = array(
			'id'    => $variation->get_id(),
			'attrs' => $variation->get_variation_attributes(),
			'price' => (float) $variation->get_price(),
			'stock' => $variation->get_stock_status(),
		);
	}

	return $map;
}

/**
 * Pun spisak opcija jednog varijacijskog atributa, sa labelama.
 *
 * Pilule se do sada grade iz `select.options`, a WC iz tog selecta BRISE opcije koje
 * nisu moguce uz trenutni izbor (ne onemogucava ih - brise). Zato pilula nestane
 * umjesto da posivi, a iznad praga od 30 varijacija se ne desava ni to. Da bi spisak
 * pilula bio stabilan u oba slucaja, pun spisak ide sa servera.
 *
 * Redoslijed se uzima iz same taksonomije (kako je podesen na atributu), ne iz
 * varijacija - isto sto radi i wc_dropdown_variation_attribute_options().
 *
 * @param WC_Product $product  Roditeljski proizvod.
 * @param string     $taxonomy Naziv atributa (npr. `pa_sirina-vrata`).
 * @param array      $options  Vrijednosti koje proizvod stvarno nudi.
 * @return array<int,array{v:string,l:string}>
 */
function door_expert_variation_option_list( $product, $taxonomy, $options ) {
	$list = array();

	if ( ! taxonomy_exists( $taxonomy ) ) {
		// Lokalni (per-product) atribut: vrijednost je i labela.
		foreach ( $options as $option ) {
			$list[] = array(
				'v' => $option,
				'l' => $option,
			);
		}

		return $list;
	}

	$terms = wc_get_product_terms( $product->get_id(), $taxonomy, array( 'fields' => 'all' ) );

	foreach ( $terms as $term ) {
		if ( ! in_array( $term->slug, $options, true ) ) {
			continue;
		}

		$list[] = array(
			'v' => $term->slug,
			'l' => $term->name,
		);
	}

	return $list;
}
