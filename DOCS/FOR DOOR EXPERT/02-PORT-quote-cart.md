
# PORT 02 — Quote cart (WooCommerce without payment)

**Verdict: `ADAPT (light)` · Priority 1**

This is the single most valuable thing in the Saya codebase for Door Expert, because it is the same
business model: WooCommerce runs the catalogue and the cart, but nothing is ever paid online. The
customer fills a short form, a real WC order is created with status `on-hold`, and sales follows up
with a formal offer.

---

## 1. What it does

- Replaces the WooCommerce checkout URL with a custom page (`/upit/` on Saya).
- Renames every "Proceed to checkout" string to an inquiry CTA.
- On submit: validates, creates a real `WC_Order` (so the whole WooCommerce admin, order notes,
  reporting and customer history keep working), sets it `on-hold`, stores GDPR consent proof,
  notifies sales, and empties the cart.
- Suppresses WooCommerce's own transactional emails, because the notification is sent by the
  integration layer instead.
- Cart page updates quantity and removes items over AJAX, no reload.
- Products with price `0` stay purchasable, so "price on request" items can enter the cart.
- The header cart badge is hydrated over AJAX because full-page cache freezes the server-rendered
  number.

## 2. Saya source

| Piece | Location |
|---|---|
| Checkout redirect + button text | `wp-theme/functions.php:3797-3811` |
| `/upit/` CSS enqueue | `wp-theme/functions.php:3813-3824` |
| Inquiry submit AJAX | `wp-theme/functions.php:3826-4066` |
| Rate limit helper | `wp-theme/functions.php:681` (`saya_rate_limit()`, used at `:1506` and `:3843`) |
| Cart qty AJAX | `wp-theme/functions.php:4395-4428` |
| Cart remove AJAX | `wp-theme/functions.php:4430-4452` |
| Price-0 purchasable | `wp-theme/functions.php:4454-4463` |
| Cart badge endpoint | `wp-theme/functions.php:5350-5374` |
| Inquiry form page | `wp-theme/page-upit.php` (462 lines) |
| Cart template | `wp-theme/woocommerce/cart/cart.php` (350 lines) |
| Cart JS | `wp-theme/js/cart.js` (220 lines) |
| Badge hydration JS | `wp-theme/js/main.js:701-830` |
| Styles | `wp-theme/css/cart.css`, `wp-theme/css/upit.css` |

## 3. Dependencies and coupling

| Dependency | Notes |
|---|---|
| WooCommerce | Core. `wc_create_order()`, `WC()->cart`, `wc_price()`, notices. |
| jQuery | **None.** `cart.js` and the badge hydration are vanilla. |
| Page builder / CF7 / Jet* | **None.** Nothing to strip. |
| n8n webhook | **Yes, and this is the one real blocker.** See below. |
| Site constants | `SAYA_N8N_WEBHOOK`, `SAYA_N8N_SECRET` in `wp-config.php`, outside the repo. |
| Saya-only helpers | `saya_pkg_data()`, `saya_price_unit()`, `saya_display_code()`, `saya_brand_hides_sku()`, `saya_consent_form_text()`, `SAYA_CONSENT_FORM_VERSION`. All optional for the core flow; the adapted version below drops or guards every one of them. |

### The n8n coupling

`saya_handle_inquiry_submit()` turns WooCommerce's own emails off at `functions.php:3878-3881` and
posts a JSON payload to an n8n webhook at `:3991-4059`. If you port it verbatim without n8n,
**nobody gets notified** — the order lands in wp-admin silently.

The adapted code below inverts this: `wp_mail()` is the default and always works, the webhook is
optional and fires only when the constant is defined. That is a deliberate improvement, not a
transcription.

## 4. Data-model mapping

Nothing in the core flow depends on Saya's taxonomies. It reads only from `WC()->cart` and standard
product methods. The one mapping to make is the brand taxonomy:

| Saya | Door Expert |
|---|---|
| `product_brand` (`functions.php:3905`) | `product_brand` — same taxonomy, no change |
| `saya_display_code()` (custom SKU display) | plain `$product->get_sku()` |
| `saya_price_unit()` / `saya_pkg_data()` (m² selling unit) | only needed if you sell tiles by m² — see `05-PORT-tile-calculator.md` |

## 5. Adapted code

### `inc/quote-cart.php`

```php
<?php
/**
 * Quote cart, WooCommerce bez online plaćanja.
 *
 * Korpa radi normalno, ali umjesto plaćanja kupac šalje upit. Kreira se pravi
 * WC_Order sa statusom on-hold, pa cijela WooCommerce administracija,
 * bilješke uz narudžbu i istorija kupca nastavljaju da rade.
 *
 * @package Door_Expert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stranica na koju vodi dugme iz korpe.
 */
function door_expert_quote_page_url() {
	return home_url( '/upit/' );
}

add_filter(
	'woocommerce_get_checkout_url',
	'door_expert_quote_page_url'
);

add_filter(
	'woocommerce_order_button_text',
	function () {
		return __( 'Pošalji upit prodaji', 'door-expert' );
	}
);

/**
 * WooCommerce na više mjesta ispisuje "Proceed to checkout" mimo filtera iznad.
 */
add_filter(
	'gettext',
	function ( $translated, $original ) {
		if ( 'Proceed to checkout' === $original ) {
			return __( 'Pošalji upit prodaji', 'door-expert' );
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
 * @param string $prefix  Ključ transienta.
 * @param int    $limit   Dozvoljen broj zahtjeva.
 * @param int    $window  Prozor u sekundama.
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

add_action( 'wp_ajax_door_expert_submit_inquiry', 'door_expert_handle_inquiry_submit' );
add_action( 'wp_ajax_nopriv_door_expert_submit_inquiry', 'door_expert_handle_inquiry_submit' );

/**
 * Prima formu sa /upit/ stranice, pravi narudžbu i obavještava prodaju.
 */
function door_expert_handle_inquiry_submit() {
	check_ajax_referer( 'door_expert_inquiry_nonce', 'nonce' );

	// Honeypot. Polje je sakriveno u formi, boti ga popunjavaju.
	if ( ! empty( $_POST['website'] ) ) {
		wp_send_json_error( __( 'Greška.', 'door-expert' ) );
	}

	// Saglasnost. Klijentskoj validaciji se ne vjeruje.
	$consent = isset( $_POST['consent'] ) ? (string) wp_unslash( $_POST['consent'] ) : '';
	if ( '1' !== $consent ) {
		wp_send_json_error( __( 'Potrebna je saglasnost za obradu podataka.', 'door-expert' ) );
	}

	if ( ! door_expert_rate_limit( 'de_rl_inquiry_', 5, HOUR_IN_SECONDS ) ) {
		wp_send_json_error( __( 'Previše zahtjeva. Pokušajte ponovo za sat vremena.', 'door-expert' ), 429 );
	}

	$name      = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
	$email     = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$phone     = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
	$city      = sanitize_text_field( wp_unslash( $_POST['city'] ?? '' ) );
	$address   = sanitize_text_field( wp_unslash( $_POST['address'] ?? '' ) );
	$note      = sanitize_textarea_field( wp_unslash( $_POST['note'] ?? '' ) );
	$buyer     = sanitize_key( wp_unslash( $_POST['buyer_type'] ?? 'fizicko' ) );
	$company   = sanitize_text_field( wp_unslash( $_POST['company'] ?? '' ) );
	$pib       = sanitize_text_field( wp_unslash( $_POST['pib'] ?? '' ) );
	$is_b2b    = ( 'b2b' === $buyer );

	if ( ! $name || ! $email || ! is_email( $email ) || ! $phone || ! $city || ! $address ) {
		wp_send_json_error( __( 'Molimo popunite sva obavezna polja.', 'door-expert' ) );
	}

	if ( $is_b2b && ! $company ) {
		wp_send_json_error( __( 'Molimo unesite naziv firme.', 'door-expert' ) );
	}

	if ( ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() ) {
		wp_send_json_error( __( 'Korpa je prazna.', 'door-expert' ) );
	}

	/*
	 * Totali se moraju izračunati PRIJE čitanja cijena. U admin-ajax zahtjevu
	 * woocommerce_before_calculate_totals još nije odrađen, pa get_price()
	 * vraća sirovu cijenu. Ovo popravlja i cijene u mejlu i total narudžbe.
	 */
	WC()->cart->calculate_totals();

	// WooCommerce ne šalje svoje mejlove, notifikacija ide našim putem.
	add_filter( 'woocommerce_email_enabled_new_order', '__return_false' );
	add_filter( 'woocommerce_email_enabled_customer_on_hold_order', '__return_false' );
	add_filter( 'woocommerce_email_enabled_admin_new_order', '__return_false' );

	$products    = door_expert_collect_cart_products();
	$order       = wc_create_order();
	$name_parts  = explode( ' ', $name, 2 );

	foreach ( WC()->cart->get_cart() as $item ) {
		$order->add_product( $item['data'], $item['quantity'] );
	}

	$order->set_billing_first_name( $name_parts[0] );
	$order->set_billing_last_name( $name_parts[1] ?? '' );
	$order->set_billing_email( $email );
	$order->set_billing_phone( $phone );
	$order->set_billing_city( $city );
	$order->set_billing_address_1( $address );

	if ( $is_b2b && $company ) {
		$order->set_billing_company( $company );
	}

	$order->set_payment_method( 'inquiry' );
	$order->set_payment_method_title( __( 'Upit, predračun', 'door-expert' ) );

	$status_note = $is_b2b
		? __( 'B2B upit primljen putem sajta.', 'door-expert' ) . ( $company ? ' Firma: ' . $company : '' ) . ( $pib ? ' PIB: ' . $pib : '' )
		: __( 'Upit primljen putem sajta.', 'door-expert' );

	$order->set_status( 'on-hold', $status_note );

	if ( $note ) {
		$order->add_order_note( __( 'Napomena klijenta: ', 'door-expert' ) . $note, false, false );
	}

	/*
	 * Dokaz o saglasnosti. Čuva se TEKST koji je posjetilac vidio u trenutku
	 * slanja, ne samo "1", jer dokaz ne vrijedi ako se tekst kasnije promijeni.
	 */
	$order->update_meta_data( '_door_expert_consent_text', door_expert_consent_text() );
	$order->update_meta_data( '_door_expert_consent_ts', current_time( 'c' ) );
	$order->update_meta_data( '_door_expert_consent_ip', sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) ) );

	$order->calculate_totals();
	$order->save();

	$payload = array(
		'tip'             => $is_b2b ? 'b2b' : 'fizicko',
		'ime'             => $name,
		'email'           => $email,
		'telefon'         => $phone,
		'grad'            => $city,
		'adresa'          => $address,
		'kompanija'       => $company,
		'pib'             => $pib,
		'poruka'          => $note,
		'proizvodi'       => $products,
		'order_broj'      => $order->get_order_number(),
		'order_admin_url' => admin_url( 'post.php?post=' . $order->get_id() . '&action=edit' ),
		'vrijeme'         => current_time( 'd.m.Y. H:i' ),
	);

	door_expert_notify_inquiry( $payload );

	WC()->cart->empty_cart();

	wp_send_json_success( array( 'order_num' => $order->get_order_number() ) );
}

/**
 * Skuplja stavke korpe u ravan niz pogodan za mejl i webhook.
 *
 * @return array
 */
function door_expert_collect_cart_products() {
	$rows = array();

	foreach ( WC()->cart->get_cart() as $cart_item ) {
		$product = $cart_item['data'];
		$qty     = (int) $cart_item['quantity'];
		$price   = (float) $product->get_price();
		$total   = $price > 0 ? $price * $qty : null;

		$brand  = '';
		$terms  = get_the_terms( $cart_item['product_id'], 'product_brand' );
		if ( $terms && ! is_wp_error( $terms ) ) {
			$brand = $terms[0]->name;
		}

		$attrs = array();
		foreach ( wc_get_product_variation_attributes( $cart_item['variation_id'] ?? 0 ) as $key => $value ) {
			if ( ! $value ) {
				continue;
			}
			$label           = wc_attribute_label( str_replace( 'attribute_', '', $key ) );
			$attrs[ $label ] = $value;
		}

		$rows[] = array(
			'naziv'       => $product->get_name(),
			'brend'       => $brand,
			'sifra'       => $product->get_sku(),
			'url'         => get_permalink( $cart_item['product_id'] ),
			'atributi'    => $attrs,
			'kolicina'    => $qty,
			'cijena'      => null !== $total
				? html_entity_decode( wp_strip_all_tags( wc_price( $price ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' )
				: __( 'Cijena na upit', 'door-expert' ),
			'cijena_ukupno' => null !== $total
				? html_entity_decode( wp_strip_all_tags( wc_price( $total ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' )
				: __( 'Cijena na upit', 'door-expert' ),
		);
	}

	return $rows;
}

/**
 * Tekst saglasnosti koji se čuva uz narudžbu kao dokaz.
 *
 * @return string
 */
function door_expert_consent_text() {
	return __( 'Saglasan sam da Door Expert obrađuje moje podatke radi izrade i slanja ponude.', 'door-expert' );
}

/**
 * Obavještenje prodaji. Mejl je uvijek, webhook samo ako je podešen.
 *
 * @param array $payload Podaci upita.
 */
function door_expert_notify_inquiry( $payload ) {
	$webhook = defined( 'DOOR_EXPERT_WEBHOOK' ) ? DOOR_EXPERT_WEBHOOK : '';
	$secret  = defined( 'DOOR_EXPERT_WEBHOOK_SECRET' ) ? DOOR_EXPERT_WEBHOOK_SECRET : '';

	if ( $webhook ) {
		wp_remote_post(
			$webhook,
			array(
				'headers'     => array(
					'Content-Type'    => 'application/json',
					'X-Door-Expert-Secret' => $secret,
				),
				'body'        => wp_json_encode( $payload ),
				'data_format' => 'body',
				'timeout'     => 5,
				'blocking'    => false,
			)
		);
	}

	$to      = apply_filters( 'door_expert_inquiry_recipient', get_option( 'admin_email' ) );
	$subject = sprintf(
		/* translators: %s: order number */
		__( 'Novi upit sa sajta, narudžba %s', 'door-expert' ),
		$payload['order_broj']
	);

	$lines = array(
		__( 'Ime:', 'door-expert' ) . ' ' . $payload['ime'],
		__( 'Email:', 'door-expert' ) . ' ' . $payload['email'],
		__( 'Telefon:', 'door-expert' ) . ' ' . $payload['telefon'],
		__( 'Grad:', 'door-expert' ) . ' ' . $payload['grad'],
		__( 'Adresa:', 'door-expert' ) . ' ' . $payload['adresa'],
	);

	if ( $payload['kompanija'] ) {
		$lines[] = __( 'Firma:', 'door-expert' ) . ' ' . $payload['kompanija'] . ( $payload['pib'] ? ' (PIB ' . $payload['pib'] . ')' : '' );
	}

	if ( $payload['poruka'] ) {
		$lines[] = '';
		$lines[] = __( 'Napomena:', 'door-expert' ) . ' ' . $payload['poruka'];
	}

	$lines[] = '';
	$lines[] = __( 'Stavke:', 'door-expert' );

	foreach ( $payload['proizvodi'] as $row ) {
		$attr_text = $row['atributi'] ? ' (' . implode( ', ', array_map(
			function ( $k, $v ) {
				return $k . ': ' . $v;
			},
			array_keys( $row['atributi'] ),
			$row['atributi']
		) ) . ')' : '';

		$lines[] = sprintf(
			'- %s%s x%d, %s',
			$row['naziv'],
			$attr_text,
			$row['kolicina'],
			$row['cijena_ukupno']
		);
	}

	$lines[] = '';
	$lines[] = __( 'Narudžba u administraciji:', 'door-expert' ) . ' ' . $payload['order_admin_url'];

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
 * vrijednost iz trenutka keširanja. admin-ajax.php se ne keširа, pa ovaj
 * endpoint vraća stvarno stanje sesije. Uz brojač vraća i svjež nonce, jer
 * nonce iz keširanog HTML-a može isteći.
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
```

### `assets/js/cart.js` — quantity and removal

```js
/**
 * Korpa: promjena količine i uklanjanje stavki bez osvježavanja stranice.
 */
( function () {
	'use strict';

	var root = document.querySelector( '.cart-page' );
	if ( ! root || 'undefined' === typeof doorExpert ) {
		return;
	}

	function post( action, body ) {
		var data = new URLSearchParams( body );
		data.append( 'action', action );
		data.append( 'nonce', doorExpert.cartNonce );

		return fetch( doorExpert.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: data.toString()
		} ).then( function ( res ) {
			return res.json();
		} );
	}

	function paintTotals( data ) {
		document.querySelectorAll( '[data-cart-subtotal]' ).forEach( function ( el ) {
			el.innerHTML = data.cart_subtotal;
		} );
		document.querySelectorAll( '[data-cart-total]' ).forEach( function ( el ) {
			el.textContent = data.cart_total;
		} );
		document.querySelectorAll( '.cart-badge' ).forEach( function ( el ) {
			el.textContent = data.cart_count;
			el.style.display = data.cart_count > 0 ? '' : 'none';
		} );
	}

	root.addEventListener( 'click', function ( e ) {
		var step = e.target.closest( '[data-qty-step]' );
		var kill = e.target.closest( '[data-cart-remove]' );

		if ( step ) {
			var row   = step.closest( '[data-cart-key]' );
			var input = row.querySelector( 'input[type="number"]' );
			var next  = Math.max( 1, parseInt( input.value, 10 ) + parseInt( step.dataset.qtyStep, 10 ) );

			input.value = next;
			row.classList.add( 'is-busy' );

			post( 'door_expert_update_cart_qty', {
				cart_key: row.dataset.cartKey,
				qty: next
			} ).then( function ( res ) {
				row.classList.remove( 'is-busy' );
				if ( ! res.success ) {
					return;
				}
				row.querySelector( '[data-item-subtotal]' ).innerHTML = res.data.item_subtotal;
				paintTotals( res.data );
			} );
		}

		if ( kill ) {
			var delRow = kill.closest( '[data-cart-key]' );
			delRow.classList.add( 'is-busy' );

			post( 'door_expert_remove_cart_item', {
				cart_key: delRow.dataset.cartKey
			} ).then( function ( res ) {
				if ( ! res.success ) {
					delRow.classList.remove( 'is-busy' );
					return;
				}
				delRow.remove();
				paintTotals( res.data );
				if ( res.data.cart_empty ) {
					window.location.reload();
				}
			} );
		}
	} );
}() );
```

### Badge hydration — put this in the globally loaded script

```js
/**
 * Badž korpe se renderuje server-side, ali full-page keš ga zamrzne.
 * Ovaj poziv vraća živu vrijednost i svjež nonce na svakom učitavanju.
 */
( function () {
	'use strict';

	if ( 'undefined' === typeof doorExpert ) {
		return;
	}

	var badges = document.querySelectorAll( '.cart-badge' );
	if ( ! badges.length ) {
		return;
	}

	fetch( doorExpert.ajaxUrl + '?action=door_expert_get_cart_count', {
		credentials: 'same-origin'
	} ).then( function ( res ) {
		return res.json();
	} ).then( function ( res ) {
		if ( ! res.success ) {
			return;
		}
		doorExpert.nonce = res.data.nonce;
		badges.forEach( function ( el ) {
			el.textContent = res.data.count;
			el.style.display = res.data.count > 0 ? '' : 'none';
		} );
	} ).catch( function () {} );
}() );
```

## 6. Wiring

1. `require_once get_stylesheet_directory() . '/inc/quote-cart.php';` from `functions.php`.
2. Create a WordPress page with slug `upit` and a page template that renders the form. The form
   posts `name, email, phone, city, address, note, buyer_type, company, pib, consent, website`
   (the last one is the honeypot, visually hidden, never `display:none` on the label alone).
3. Localize the script handle that owns `cart.js`:

```php
wp_localize_script(
	'door-expert-cart',
	'doorExpert',
	array(
		'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
		'nonce'        => wp_create_nonce( 'door_expert_nonce' ),
		'cartNonce'    => wp_create_nonce( 'door_expert_cart' ),
		'inquiryNonce' => wp_create_nonce( 'door_expert_inquiry_nonce' ),
	)
);
```

4. Cart row markup contract: each row carries `data-cart-key="<?php echo esc_attr( $cart_item_key ); ?>"`,
   a `<input type="number">`, `[data-qty-step="-1"]` / `[data-qty-step="1"]` buttons,
   `[data-cart-remove]`, and `[data-item-subtotal]`. Totals carry `[data-cart-subtotal]` and
   `[data-cart-total]`.
5. Optional: define `DOOR_EXPERT_WEBHOOK` and `DOOR_EXPERT_WEBHOOK_SECRET` in `wp-config.php` if you
   later add an automation layer. Without them, `wp_mail()` carries the notification.

## 7. Verify after dropping it in

- Add a priced product and a price-0 product to the cart; both must be addable.
- Cart page: `+` / `-` updates the line and the totals without a reload; removing the last item
  reloads to the empty-cart state.
- Submit the form: a new order appears under WooCommerce with status **On hold**, payment method
  "Upit, predračun", and the consent meta on the order.
- Confirm the customer does **not** receive a WooCommerce "order received" email.
- Confirm sales receives the `wp_mail` notification.
- Submit six times in an hour from the same IP; the sixth must be refused with HTTP 429.
- Submit with the honeypot filled; must be refused.
- Submit with `consent` absent; must be refused even if the front end allowed it.


