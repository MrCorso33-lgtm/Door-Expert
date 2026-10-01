<?php
/**
 * Lista sačuvanih proizvoda ("Sačuvaj za projekat") – tab "Sačuvano" u korpi.
 *
 * Portovano po uzoru na Saya Group (DOCS/FOR DOOR EXPERT/01-AUDIT-REPORT.md, komponenta 9),
 * uz izmjene:
 *   - Pregledač čuva SAMO ID-jeve proizvoda (assets/js/wishlist.js). Naziv, sliku i cijenu
 *     crta server, pa sačuvana stavka nikad ne pokazuje zastarjelu cijenu ili obrisan
 *     proizvod. Saya je čuvala kopiju podataka iz trenutka klika.
 *   - Nema spajanja sa korisničkim nalogom (usermeta): kupci se kod nas ne prijavljuju,
 *     a to spajanje je najkrhkiji dio Sayinog koda.
 *
 *   - Tab "Sačuvano" crta istu karticu kao prodavnica, sa pravim WC "Dodaj u ponudu",
 *     umjesto prototipske .wishlist-card sa "Premjesti u ponudu" (traženo). Proizvod
 *     ide u ponudu kao i svuda; sa liste ga skida srce ili tekstualni "Ukloni"
 *     ispod kartice (samo u tabu).
 *   - Dodat u ponudu (bilo odakle) = skinut sa liste. Vidi door_expert_wishlist_mark_added().
 *
 * AJAX se koristi samo na stranici korpe, koju keš ne servira, pa je nonce svjež.
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Najviše sačuvanih proizvoda – ista granica važi i u wishlist.js.
 */
define( 'DOOR_EXPERT_WISHLIST_MAX', 100 );

/**
 * Kolačić sa ID-jevima upravo dodatim u ponudu – wishlist.js ih skida sa liste.
 */
define( 'DOOR_EXPERT_WISHLIST_ADDED_COOKIE', 'door_expert_wishlist_added' );

add_action( 'woocommerce_add_to_cart', 'door_expert_wishlist_mark_added', 10, 2 );

/**
 * Proizvod dodat u ponudu više ne stoji na listi sačuvanih (traženo).
 *
 * Lista živi u pregledaču, pa server samo javi KOJI proizvod je dodat: kratak
 * kolačić koji wishlist.js pročita i obriše. Jedan hook pokriva sve puteve –
 * formu na PDP-u (reload), AJAX dugme na kartici i mobilnu sticky traku.
 * $product_id je roditelj i kod varijacije, isto što lista čuva.
 *
 * @param string $cart_item_key Ključ stavke (ne koristi se).
 * @param int    $product_id    ID proizvoda (roditelja).
 */
function door_expert_wishlist_mark_added( $cart_item_key, $product_id ) {
	if ( headers_sent() || ! function_exists( 'wc_setcookie' ) ) {
		return;
	}

	$raw = isset( $_COOKIE[ DOOR_EXPERT_WISHLIST_ADDED_COOKIE ] )
		? sanitize_text_field( wp_unslash( $_COOKIE[ DOOR_EXPERT_WISHLIST_ADDED_COOKIE ] ) )
		: '';
	$ids = array_filter( array_map( 'absint', explode( ',', $raw ) ) );

	$ids[] = absint( $product_id );
	$ids   = array_slice( array_unique( $ids ), -20 );

	// httponly = false: čita ga JS. Kratko traje – pokupi se na sljedećem učitavanju.
	wc_setcookie( DOOR_EXPERT_WISHLIST_ADDED_COOKIE, implode( ',', $ids ), time() + 10 * MINUTE_IN_SECONDS, is_ssl(), false );
	$_COOKIE[ DOOR_EXPERT_WISHLIST_ADDED_COOKIE ] = implode( ',', $ids );
}

/**
 * Proizvod koji smije na listu: postoji i objavljen je.
 *
 * @param int $product_id ID proizvoda.
 * @return WC_Product|null
 */
function door_expert_wishlist_product( $product_id ) {
	$product = $product_id ? wc_get_product( $product_id ) : null;

	if ( ! $product instanceof WC_Product || 'publish' !== $product->get_status() ) {
		return null;
	}

	return $product;
}

add_action( 'wp_ajax_door_expert_wishlist_cards', 'door_expert_wishlist_cards' );
add_action( 'wp_ajax_nopriv_door_expert_wishlist_cards', 'door_expert_wishlist_cards' );

/**
 * Kartice za tab "Sačuvano" iz liste ID-jeva.
 *
 * Vraća i ID-jeve koji su prošli, da JS izbaci obrisane proizvode iz memorije
 * pregledača (inače bi brojač u tabu zauvijek brojao i nepostojeće).
 */
function door_expert_wishlist_cards() {
	check_ajax_referer( 'door_expert_wishlist', 'nonce' );

	$raw = sanitize_text_field( wp_unslash( $_POST['ids'] ?? '' ) );
	$ids = array_slice( array_unique( array_filter( array_map( 'absint', explode( ',', $raw ) ) ) ), 0, DOOR_EXPERT_WISHLIST_MAX );

	/*
	 * ISTA kartica kao u prodavnici i na početnoj (template-parts/shop/product-card.php):
	 * srce (ovdje aktivno, klik uklanja sa liste), bedževi, atributi, cijena i pravo
	 * WC "Dodaj u ponudu". Kartica čita globalni $product, kao u WC petlji.
	 */
	$valid    = array();
	$previous = $GLOBALS['product'] ?? null;
	ob_start();
	foreach ( $ids as $id ) {
		$item = door_expert_wishlist_product( $id );
		if ( ! $item ) {
			continue;
		}
		$valid[]            = $id;
		$GLOBALS['product'] = $item; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- kartica očekuje WC globalni $product.

		/*
		 * Omotač + tekstualni "Ukloni" ispod kartice (traženo): puno srce kao
		 * "ukloni" nije dovoljno očigledno. Kartica sama ostaje netaknuta.
		 */
		echo '<div class="wishlist-item">';
		get_template_part( 'template-parts/shop/product-card' );
		printf(
			'<button type="button" class="wishlist-item__remove" data-wishlist-remove="%1$s" aria-label="%2$s">Ukloni</button>',
			esc_attr( (string) $id ),
			esc_attr( 'Ukloni ' . $item->get_name() . ' iz sačuvanih' )
		);
		echo '</div>';
	}
	$html               = (string) ob_get_clean();
	$GLOBALS['product'] = $previous; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

	wp_send_json_success(
		array(
			'html' => $html,
			'ids'  => $valid,
		)
	);
}
