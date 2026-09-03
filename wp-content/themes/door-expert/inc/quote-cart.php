<?php
/**
 * Quote cart – WooCommerce bez online plaćanja.
 *
 * Korpa radi normalno, ali umjesto plaćanja kupac šalje upit. Kreira se pravi
 * WC_Order sa statusom on-hold, pa cijela WooCommerce administracija, bilješke
 * uz narudžbu i istorija kupca nastavljaju da rade.
 *
 * Portovano iz Saya Group (DOCS/FOR DOOR EXPERT/02-PORT-quote-cart.md) uz izmjene:
 *   - Forma upita je U KORPI (prototip korpa.html), ne na zasebnoj /upit/ stranici.
 *   - Polja su naša: ime, email, telefon, grad, napomena, saglasnost (bez B2B/PIB).
 *   - wp_mail() je podrazumijevana notifikacija; webhook je opcion (Saya je zavisila
 *     od n8n-a, pa bi upiti stizali tiho). Vidi red flag #1 u 01-AUDIT-REPORT.md.
 *   - Dodate napomene po stavci (item_note[cart_key]) kao meta na stavci narudžbe.
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URL korpe – nikad hardkodovan (WooCommerce sam sklapa; fallback za slučaj bez WC-a).
 *
 * @return string
 */
function door_expert_cart_url() {
	return function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/korpa/' );
}

/**
 * Checkout vodi nazad na korpu – forma upita je tamo, plaćanja nema.
 *
 * @return string
 */
function door_expert_quote_checkout_url() {
	return door_expert_cart_url();
}
add_filter( 'woocommerce_get_checkout_url', 'door_expert_quote_checkout_url' );

add_filter(
	'woocommerce_order_button_text',
	function () {
		return 'Pošalji upit za ponudu';
	}
);

add_action( 'template_redirect', 'door_expert_block_checkout' );
/**
 * Direktan pristup /checkout/ vraća na korpu.
 *
 * Linkovi su već preusmereni (woocommerce_get_checkout_url), ali ko ukuca URL
 * ručno, dođe sa starog linka ili bookmarka dobio bi praznu checkout stranicu
 * bez ijednog načina plaćanja. U quote modelu plaćanja nema, pa je jedino
 * smisleno odredište korpa, gdje je forma upita.
 *
 * Izuzeci: order-received i order-pay endpointi ostaju dostupni (WooCommerce ih
 * tretira kao checkout, a mogu zatrebati za ručno poslate linkove iz administracije).
 */
function door_expert_block_checkout() {
	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
		return;
	}

	if ( is_wc_endpoint_url( 'order-received' ) || is_wc_endpoint_url( 'order-pay' ) ) {
		return;
	}

	wp_safe_redirect( door_expert_cart_url(), 302 );
	exit;
}

/**
 * WooCommerce na više mjesta ispisuje "Proceed to checkout" mimo filtera iznad.
 */
add_filter(
	'gettext',
	function ( $translated, $original ) {
		if ( 'Proceed to checkout' === $original ) {
			return 'Pošalji upit za ponudu';
		}
		return $translated;
	},
	20,
	2
);

/**
 * Proizvodi bez cijene ostaju kupljivi, jer "cijena na upit" mora u korpu.
 */
add_filter(
	'woocommerce_is_purchasable',
	function ( $purchasable, $product ) {
		if ( ! $purchasable && 0.0 === (float) $product->get_price() ) {
			return true;
		}
		return $purchasable;
	},
	10,
	2
);

/**
 * Ograničenje broja zahtjeva po IP adresi.
 *
 * @param string $prefix Ključ transienta.
 * @param int    $limit  Dozvoljen broj zahtjeva.
 * @param int    $window Prozor u sekundama.
 * @return bool True ako je zahtjev dozvoljen.
 */
function door_expert_rate_limit( $prefix, $limit, $window ) {
	$ip = isset( $_SERVER['REMOTE_ADDR'] )
		? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) )
		: '';

	if ( '' === $ip ) {
		return true;
	}

	$key   = $prefix . md5( $ip );
	$count = (int) get_transient( $key );

	if ( $count >= $limit ) {
		return false;
	}

	set_transient( $key, $count + 1, $window );

	return true;
}

/**
 * Tekst saglasnosti koji se čuva uz narudžbu kao dokaz.
 *
 * Čuva se TEKST koji je posjetilac vidio, ne samo "1" – dokaz ne vrijedi ako se
 * tekst kasnije promijeni.
 *
 * @return string
 */
function door_expert_consent_text() {
	return 'Slažem se sa politikom privatnosti i prihvatam da Door Expert kontaktira mene radi slanja formalne ponude.';
}

add_action( 'wp_ajax_door_expert_submit_inquiry', 'door_expert_handle_inquiry_submit' );
add_action( 'wp_ajax_nopriv_door_expert_submit_inquiry', 'door_expert_handle_inquiry_submit' );

/**
 * Prima formu iz korpe, pravi narudžbu (on-hold) i obavještava prodaju.
 */
function door_expert_handle_inquiry_submit() {
	check_ajax_referer( 'door_expert_inquiry_nonce', 'nonce' );

	// Honeypot – polje je sakriveno u formi, boti ga popunjavaju.
	if ( ! empty( $_POST['website'] ) ) {
		wp_send_json_error( 'Greška.' );
	}

	// Saglasnost – klijentskoj validaciji se ne vjeruje.
	$consent = isset( $_POST['saglasnost'] ) ? (string) wp_unslash( $_POST['saglasnost'] ) : '';
	if ( '1' !== $consent ) {
		wp_send_json_error( 'Potrebna je saglasnost za obradu podataka.' );
	}

	if ( ! door_expert_rate_limit( 'de_rl_inquiry_', 5, HOUR_IN_SECONDS ) ) {
		wp_send_json_error( 'Previše zahtjeva. Pokušajte ponovo za sat vremena.', 429 );
	}

	$name  = sanitize_text_field( wp_unslash( $_POST['ime'] ?? '' ) );
	$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$phone = sanitize_text_field( wp_unslash( $_POST['telefon'] ?? '' ) );
	$city  = sanitize_text_field( wp_unslash( $_POST['grad'] ?? '' ) );
	$note  = sanitize_textarea_field( wp_unslash( $_POST['napomena'] ?? '' ) );

	if ( ! $name || ! $email || ! is_email( $email ) || ! $phone ) {
		wp_send_json_error( 'Molimo popunite ime, email i telefon.' );
	}

	if ( ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() ) {
		wp_send_json_error( 'Korpa je prazna.' );
	}

	// Napomene po stavci (opciono), ključ = cart_item_key.
	$item_notes = array();
	if ( isset( $_POST['item_note'] ) && is_array( $_POST['item_note'] ) ) {
		foreach ( wp_unslash( $_POST['item_note'] ) as $ikey => $ival ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitizuje se ispod.
			$ikey = sanitize_text_field( $ikey );
			$ival = sanitize_textarea_field( $ival );
			if ( '' !== $ikey && '' !== $ival ) {
				$item_notes[ $ikey ] = $ival;
			}
		}
	}

	/*
	 * Totali se moraju izračunati PRIJE čitanja cijena. U admin-ajax zahtjevu
	 * woocommerce_before_calculate_totals još nije odrađen, pa get_price()
	 * vraća sirovu cijenu. Ovo popravlja i cijene u mejlu i total narudžbe.
	 */
	WC()->cart->calculate_totals();

	// WooCommerce ne šalje svoje mejlove – notifikacija ide našim putem.
	add_filter( 'woocommerce_email_enabled_new_order', '__return_false' );
	add_filter( 'woocommerce_email_enabled_customer_on_hold_order', '__return_false' );
	add_filter( 'woocommerce_email_enabled_admin_new_order', '__return_false' );

	$products   = door_expert_collect_cart_products( $item_notes );
	$order      = wc_create_order();
	$name_parts = explode( ' ', $name, 2 );

	foreach ( WC()->cart->get_cart() as $cart_key => $item ) {
		$item_id = $order->add_product( $item['data'], $item['quantity'] );

		if ( $item_id && isset( $item_notes[ $cart_key ] ) ) {
			wc_add_order_item_meta( $item_id, 'Napomena', $item_notes[ $cart_key ] );
		}
	}

	$order->set_billing_first_name( $name_parts[0] );
	$order->set_billing_last_name( $name_parts[1] ?? '' );
	$order->set_billing_email( $email );
	$order->set_billing_phone( $phone );

	if ( $city ) {
		$order->set_billing_city( $city );
	}

	$order->set_payment_method( 'inquiry' );
	$order->set_payment_method_title( 'Upit, predračun' );
	$order->set_status( 'on-hold', 'Upit primljen putem sajta.' );

	if ( $note ) {
		$order->add_order_note( 'Napomena klijenta: ' . $note, false, false );
	}

	$order->update_meta_data( '_door_expert_consent_text', door_expert_consent_text() );
	$order->update_meta_data( '_door_expert_consent_ts', current_time( 'c' ) );
	$order->update_meta_data( '_door_expert_consent_ip', sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) ) );

	$order->calculate_totals();
	$order->save();

	/*
	 * Payload za n8n. Šalje se kao JSON; n8n iz njega gradi personalizovane
	 * šablone (potvrda kupcu + obavještenje prodaji). Ako se doda novo polje,
	 * uskladiti n8n workflow.
	 */
	$payload = array(
		'ime'             => $name,
		'email'           => $email,
		'telefon'         => $phone,
		'grad'            => $city,
		'poruka'          => $note,
		'proizvodi'       => $products,
		'ukupno'          => html_entity_decode( wp_strip_all_tags( wc_price( $order->get_total() ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ),
		'ukupno_broj'     => (float) $order->get_total(),
		'valuta'          => $order->get_currency(),
		'order_id'        => $order->get_id(),
		'order_broj'      => $order->get_order_number(),
		'order_admin_url' => admin_url( 'post.php?post=' . $order->get_id() . '&action=edit' ),
		'saglasnost_tekst' => door_expert_consent_text(),
		'vrijeme'         => current_time( 'd.m.Y. H:i' ),
	);

	door_expert_notify_inquiry( $payload );

	WC()->cart->empty_cart();

	wp_send_json_success(
		array(
			'order_num' => $order->get_order_number(),
			'redirect'  => home_url( '/hvala/?upit=' . rawurlencode( $order->get_order_number() ) ),
		)
	);
}

/**
 * Skuplja stavke korpe u ravan niz pogodan za mejl i webhook.
 *
 * @param array $item_notes Napomene po cart_item_key.
 * @return array
 */
function door_expert_collect_cart_products( $item_notes = array() ) {
	$rows = array();

	foreach ( WC()->cart->get_cart() as $cart_key => $cart_item ) {
		$product = $cart_item['data'];
		$qty     = (int) $cart_item['quantity'];
		$price   = (float) $product->get_price();
		$total   = $price > 0 ? $price * $qty : null;

		$brand = '';
		$terms = taxonomy_exists( 'product_brand' ) ? get_the_terms( $cart_item['product_id'], 'product_brand' ) : false;
		if ( $terms && ! is_wp_error( $terms ) ) {
			$brand = $terms[0]->name;
		}

		$attrs = array();
		if ( ! empty( $cart_item['variation_id'] ) ) {
			foreach ( wc_get_product_variation_attributes( $cart_item['variation_id'] ) as $key => $value ) {
				if ( ! $value ) {
					continue;
				}
				$label           = wc_attribute_label( str_replace( 'attribute_', '', $key ) );
				$attrs[ $label ] = $value;
			}
		}

		$rows[] = array(
			'naziv'         => $product->get_name(),
			'brend'         => $brand,
			'sifra'         => $product->get_sku(),
			'url'           => get_permalink( $cart_item['product_id'] ),
			'atributi'      => $attrs,
			'napomena'      => isset( $item_notes[ $cart_key ] ) ? $item_notes[ $cart_key ] : '',
			'kolicina'      => $qty,
			'cijena'        => null !== $total
				? html_entity_decode( wp_strip_all_tags( wc_price( $price ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' )
				: 'Cijena na upit',
			'cijena_ukupno' => null !== $total
				? html_entity_decode( wp_strip_all_tags( wc_price( $total ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' )
				: 'Cijena na upit',
		);
	}

	return $rows;
}

/**
 * Obavještenje o upitu.
 *
 * PRIMARNO: n8n webhook – tamo su personalizovani šabloni za kupca i za prodaju.
 * FALLBACK: wp_mail(), samo ako webhook nije podešen ILI ako poziv padne. Time
 * upit nikad ne nestane tiho (to je bila mana originala – vidi red flag #1 u
 * DOCS/FOR DOOR EXPERT/01-AUDIT-REPORT.md), a u normalnom radu nema duplih mejlova.
 *
 * Poziv je namjerno blokirajući: bez odgovora ne možemo znati da li je n8n primio
 * upit, pa ni da li fallback treba. Timeout je kratak.
 *
 * Podešavanje u wp-config.php:
 *   define( 'DOOR_EXPERT_WEBHOOK', 'https://n8n.primjer.com/webhook/door-expert-upit' );
 *   define( 'DOOR_EXPERT_WEBHOOK_SECRET', 'tajna' );
 *
 * @param array $payload Podaci upita (vidi door_expert_collect_cart_products()).
 */
function door_expert_notify_inquiry( $payload ) {
	$webhook = defined( 'DOOR_EXPERT_WEBHOOK' ) ? DOOR_EXPERT_WEBHOOK : '';
	$secret  = defined( 'DOOR_EXPERT_WEBHOOK_SECRET' ) ? DOOR_EXPERT_WEBHOOK_SECRET : '';

	if ( $webhook ) {
		$response = wp_remote_post(
			$webhook,
			array(
				'headers'     => array(
					'Content-Type'         => 'application/json',
					'X-Door-Expert-Secret' => $secret,
				),
				'body'        => wp_json_encode( $payload ),
				'data_format' => 'body',
				'timeout'     => 8,
				'blocking'    => true,
			)
		);

		$code = is_wp_error( $response ) ? 0 : (int) wp_remote_retrieve_response_code( $response );

		if ( $code >= 200 && $code < 300 ) {
			// n8n je preuzeo notifikaciju (kupac + prodaja). Nema wp_mail-a.
			return;
		}

		$reason = is_wp_error( $response ) ? $response->get_error_message() : 'HTTP ' . $code;

		/**
		 * Webhook nije uspio – upit ide na wp_mail fallback.
		 *
		 * @param string $reason  Razlog neuspjeha.
		 * @param array  $payload Podaci upita.
		 */
		do_action( 'door_expert_inquiry_webhook_failed', $reason, $payload );

		$payload['webhook_greska'] = $reason;
	}

	$to      = apply_filters( 'door_expert_inquiry_recipient', get_option( 'admin_email' ) );
	$subject = sprintf( 'Novi upit sa sajta, narudžba %s', $payload['order_broj'] );

	$lines = array();

	if ( ! empty( $payload['webhook_greska'] ) ) {
		// Rezervni mejl – n8n nije primio upit, pa kupcu NIJE stigla potvrda.
		$subject = '[WEBHOOK PAO] ' . $subject;
		$lines[] = 'PAŽNJA: slanje na n8n nije uspjelo (' . $payload['webhook_greska'] . ').';
		$lines[] = 'Kupcu vjerovatno NIJE stigla automatska potvrda – kontaktirajte ga ručno.';
		$lines[] = '';
	}

	$lines[] = 'Ime: ' . $payload['ime'];
	$lines[] = 'Email: ' . $payload['email'];
	$lines[] = 'Telefon: ' . $payload['telefon'];

	if ( $payload['grad'] ) {
		$lines[] = 'Grad / adresa objekta: ' . $payload['grad'];
	}

	if ( $payload['poruka'] ) {
		$lines[] = '';
		$lines[] = 'Napomena: ' . $payload['poruka'];
	}

	$lines[] = '';
	$lines[] = 'Stavke:';

	foreach ( $payload['proizvodi'] as $row ) {
		$attr_text = $row['atributi'] ? ' (' . implode(
			', ',
			array_map(
				function ( $k, $v ) {
					return $k . ': ' . $v;
				},
				array_keys( $row['atributi'] ),
				$row['atributi']
			)
		) . ')' : '';

		$lines[] = sprintf(
			'- %s%s x%d, %s',
			$row['naziv'],
			$attr_text,
			$row['kolicina'],
			$row['cijena_ukupno']
		);

		if ( $row['napomena'] ) {
			$lines[] = '  Napomena uz stavku: ' . $row['napomena'];
		}
	}

	$lines[] = '';
	$lines[] = 'Narudžba u administraciji: ' . $payload['order_admin_url'];

	wp_mail( $to, $subject, implode( "\n", $lines ) );
}

add_action( 'wp_ajax_door_expert_update_cart_qty', 'door_expert_update_cart_qty' );
add_action( 'wp_ajax_nopriv_door_expert_update_cart_qty', 'door_expert_update_cart_qty' );

/**
 * Mijenja količinu stavke u korpi bez osvježavanja stranice.
 */
function door_expert_update_cart_qty() {
	check_ajax_referer( 'door_expert_cart', 'nonce' );

	$key = sanitize_text_field( wp_unslash( $_POST['cart_key'] ?? '' ) );
	$qty = absint( wp_unslash( $_POST['qty'] ?? 0 ) );

	if ( ! $key ) {
		wp_send_json_error( 'no_key' );
	}

	WC()->cart->set_quantity( $key, $qty, true );

	$cart          = WC()->cart->get_cart();
	$item_subtotal = '';

	if ( 0 < $qty && isset( $cart[ $key ] ) ) {
		$item          = $cart[ $key ];
		$item_subtotal = wc_price( $item['line_total'] + ( $item['line_tax'] ?? 0 ) );
	}

	wp_send_json_success(
		array(
			'removed'       => 0 === $qty,
			'item_subtotal' => $item_subtotal,
			'cart_subtotal' => WC()->cart->get_cart_subtotal(),
			'cart_total'    => html_entity_decode( wp_strip_all_tags( WC()->cart->get_total() ) ),
			'cart_count'    => WC()->cart->get_cart_contents_count(),
			'cart_empty'    => WC()->cart->is_empty(),
		)
	);
}

add_action( 'wp_ajax_door_expert_remove_cart_item', 'door_expert_remove_cart_item' );
add_action( 'wp_ajax_nopriv_door_expert_remove_cart_item', 'door_expert_remove_cart_item' );

/**
 * Uklanja stavku iz korpe.
 */
function door_expert_remove_cart_item() {
	check_ajax_referer( 'door_expert_cart', 'nonce' );

	$key = sanitize_text_field( wp_unslash( $_POST['cart_key'] ?? '' ) );

	if ( ! $key ) {
		wp_send_json_error( 'no_key' );
	}

	WC()->cart->remove_cart_item( $key );

	wp_send_json_success(
		array(
			'cart_subtotal' => WC()->cart->get_cart_subtotal(),
			'cart_total'    => html_entity_decode( wp_strip_all_tags( WC()->cart->get_total() ) ),
			'cart_count'    => WC()->cart->get_cart_contents_count(),
			'cart_empty'    => WC()->cart->is_empty(),
		)
	);
}

add_action( 'wp_ajax_door_expert_get_cart_count', 'door_expert_get_cart_count' );
add_action( 'wp_ajax_nopriv_door_expert_get_cart_count', 'door_expert_get_cart_count' );

/**
 * Živa vrijednost brojača za hidrataciju badža.
 *
 * Header renderuje badž server-side, ali full-page keš servira zamrznutu
 * vrijednost iz trenutka keširanja. admin-ajax.php se ne kešira, pa ovaj
 * endpoint vraća stvarno stanje sesije.
 */
function door_expert_get_cart_count() {
	if ( ! WC()->cart && function_exists( 'wc_load_cart' ) ) {
		wc_load_cart();
	}

	nocache_headers();

	wp_send_json_success(
		array(
			'count' => WC()->cart ? WC()->cart->get_cart_contents_count() : 0,
			'nonce' => wp_create_nonce( 'door_expert_nonce' ),
		)
	);
}
