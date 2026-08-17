<?php
/**
 * Kontakt forma – custom AJAX handler (quote model, bez CF7).
 *
 * Sigurnost (CLAUDE.md sekcija 4): nonce (check_ajax_referer) + wp_unslash/sanitize_*
 * + honeypot + rate limit (transient po IP). WP je „vratar": validira i sanitizuje,
 * pa ISPORUKA ide na n8n webhook (wp_remote_post). Fallback na wp_mail dok webhook
 * nije podešen.
 *
 * Webhook URL se drži u wp-config.php konstanti (nikad u git):
 *   define( 'DOOR_EXPERT_N8N_WEBHOOK', 'https://n8n.primjer.me/webhook/...' );
 * ili preko filtera door_expert_contact_webhook.
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', 'door_expert_contact_assets', 20 );
/**
 * Enqueue contact.js samo na kontakt stranici (nonce + ajaxurl preko localize).
 * CSS (contact.css) ide kroz generalni page_assets u functions.php.
 */
function door_expert_contact_assets() {
	if ( ! is_page( 'kontakt' ) ) {
		return;
	}
	$uri = get_template_directory_uri();
	wp_enqueue_script(
		'door-expert-contact-js',
		$uri . '/assets/js/contact.js',
		array( 'door-expert-header-js' ),
		door_expert_ver( '/assets/js/contact.js' ),
		true
	);
	wp_localize_script(
		'door-expert-contact-js',
		'doorExpertContact',
		array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'door_expert_contact' ),
		)
	);
}

/**
 * n8n webhook URL (wp-config.php konstanta ili filter). Prazan string = nije podešen.
 *
 * @return string
 */
function door_expert_contact_webhook_url() {
	$url = defined( 'DOOR_EXPERT_N8N_WEBHOOK' ) ? DOOR_EXPERT_N8N_WEBHOOK : '';
	$url = apply_filters( 'door_expert_contact_webhook', $url );
	return esc_url_raw( (string) $url );
}

add_action( 'wp_ajax_nopriv_door_expert_contact', 'door_expert_contact_submit' );
add_action( 'wp_ajax_door_expert_contact', 'door_expert_contact_submit' );
/**
 * Obradi slanje kontakt forme (AJAX). Vraca wp_send_json_success/error;
 * JS (contact.js) prikazuje stanje.
 */
function door_expert_contact_submit() {
	// 1) Nonce – bail rano ako padne.
	check_ajax_referer( 'door_expert_contact', 'nonce' );

	// 2) Rate limit: max 5 slanja / 10 min po IP.
	$ip   = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
	$key  = 'de_contact_' . md5( $ip );
	$hits = (int) get_transient( $key );
	if ( $hits >= 5 ) {
		wp_send_json_error( array( 'message' => 'Previše pokušaja. Pokušajte ponovo za nekoliko minuta.' ), 429 );
	}

	// 3) Honeypot – botovi popune skriveno polje; tiho „uspjeh".
	$hp = isset( $_POST['website'] ) ? trim( (string) wp_unslash( $_POST['website'] ) ) : '';
	if ( '' !== $hp ) {
		wp_send_json_success( array( 'message' => 'Upit je primljen!' ) );
	}

	// 4) Sanitizacija (wp_unslash PA sanitize_*).
	$ime     = sanitize_text_field( wp_unslash( $_POST['ime'] ?? '' ) );
	$email   = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$telefon = sanitize_text_field( wp_unslash( $_POST['telefon'] ?? '' ) );
	$tema    = sanitize_text_field( wp_unslash( $_POST['tema'] ?? '' ) );
	$poruka  = sanitize_textarea_field( wp_unslash( $_POST['poruka'] ?? '' ) );

	// 5) Validacija obaveznih polja.
	$errors = array();
	if ( '' === $ime ) {
		$errors['ime'] = 'Unesite ime i prezime.';
	}
	if ( ! is_email( $email ) ) {
		$errors['email'] = 'Unesite ispravnu email adresu.';
	}
	if ( ! empty( $errors ) ) {
		wp_send_json_error(
			array(
				'message' => 'Provjerite obavezna polja.',
				'fields'  => $errors,
			),
			422
		);
	}

	// 6) Isporuka: n8n webhook (ako je podešen) ili wp_mail fallback.
	$payload = array(
		'ime'     => $ime,
		'email'   => $email,
		'telefon' => $telefon,
		'tema'    => $tema,
		'poruka'  => $poruka,
		'izvor'   => home_url( '/kontakt/' ),
		'vrijeme' => current_time( 'mysql' ),
	);

	$webhook   = door_expert_contact_webhook_url();
	$delivered = false;

	if ( '' !== $webhook ) {
		$resp = wp_remote_post(
			$webhook,
			array(
				'timeout' => 15,
				'headers' => array( 'Content-Type' => 'application/json; charset=utf-8' ),
				'body'    => wp_json_encode( $payload ),
			)
		);
		if ( ! is_wp_error( $resp ) ) {
			$code      = (int) wp_remote_retrieve_response_code( $resp );
			$delivered = ( $code >= 200 && $code < 300 );
		}
	} else {
		// Fallback dok n8n webhook nije podešen: mejl na zvaničnu adresu firme.
		$de_company = function_exists( 'door_expert_company_info' ) ? door_expert_company_info() : array();
		$de_default = ! empty( $de_company['email'] ) ? $de_company['email'] : get_option( 'admin_email' );
		$to         = apply_filters( 'door_expert_contact_recipient', $de_default );
		$subject = 'Novi upit sa sajta: ' . ( '' !== $tema ? $tema : 'Kontakt' );
		$body    = implode(
			"\n",
			array(
				'Ime i prezime: ' . $ime,
				'Email: ' . $email,
				'Telefon: ' . ( '' !== $telefon ? $telefon : '(nije unijet)' ),
				'Tema: ' . ( '' !== $tema ? $tema : '(nije odabrana)' ),
				'',
				'Poruka:',
				'' !== $poruka ? $poruka : '(bez poruke)',
			)
		);
		$headers   = array( 'Reply-To: ' . $ime . ' <' . $email . '>' );
		$delivered = wp_mail( $to, $subject, $body, $headers );
	}

	// 7) Uvecaj rate-limit brojac tek nakon obrade.
	set_transient( $key, $hits + 1, 10 * MINUTE_IN_SECONDS );

	if ( $delivered ) {
		wp_send_json_success( array( 'message' => 'Upit je primljen! Odgovaramo u roku od 24h.' ) );
	}
	wp_send_json_error( array( 'message' => 'Slanje trenutno nije moguće. Molimo pozovite nas telefonom.' ), 500 );
}
