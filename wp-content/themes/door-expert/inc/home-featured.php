<?php
/**
 * Početna – "Odabrani za vas ovog mjeseca": ručno isticanje proizvoda.
 *
 * Klijent na proizvodu čekira kvadratić "Početna — Odabrani za vas" i proizvod se
 * pojavljuje u toj sekciji, pod tabom svoje glavne kategorije. Prikaz:
 * template-parts/home/featured.php, JS: assets/js/featured.js.
 *
 * NAMJERNO odvojeno od WooCommerce "Featured" zvjezdice: nju kartica proizvoda
 * koristi za bedž "Novo" (template-parts/shop/product-card.php), pa bi dijeljenje
 * pravilo sudar – svaki istaknuti proizvod dobio bi "Novo" i obrnuto. Isto rješenje
 * i isti razlog kao u Saya projektu (_saya_home_featured).
 *
 * Običan meta box u kodu, a ne JetEngine polje: JetEngine podešavanja žive u bazi i
 * morala bi se ručno praviti na svakom okruženju (staging, produkcija). Ovo dolazi
 * sa temom.
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const DOOR_EXPERT_HOME_FEATURED_META = '_door_expert_home_featured';

// Najviše renderovanih kartica po kategoriji. JS prikazuje 4 pa po +4, pa bi
// neograničen broj punio HTML karticama koje niko neće otvoriti.
const DOOR_EXPERT_HOME_FEATURED_PER_CAT = 20;

/**
 * Registruje kvadratić u desnoj koloni stranice proizvoda.
 */
function door_expert_home_featured_meta_box() {
	add_meta_box(
		'door-expert-home-featured',
		'Početna — Odabrani za vas',
		'door_expert_home_featured_meta_box_render',
		'product',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes_product', 'door_expert_home_featured_meta_box' );

/**
 * Sadržaj meta boxa.
 *
 * @param WP_Post $post Proizvod.
 */
function door_expert_home_featured_meta_box_render( $post ) {
	$on = '1' === get_post_meta( $post->ID, DOOR_EXPERT_HOME_FEATURED_META, true );

	wp_nonce_field( 'door_expert_home_featured_save', 'door_expert_home_featured_nonce' );
	?>
	<label>
		<input type="checkbox" name="door_expert_home_featured" value="1" <?php checked( $on ); ?> />
		<span>Istakni u sekciji <strong>„Odabrani za vas ovog mjeseca"</strong> na početnoj. Proizvod sam ide pod tab svoje kategorije; najnovije istaknuti su prvi.</span>
	</label>
	<?php
}

/**
 * Čuva kvadratić.
 *
 * @param int $post_id ID proizvoda.
 */
function door_expert_home_featured_save( $post_id ) {
	if ( ! isset( $_POST['door_expert_home_featured_nonce'] ) ) {
		return;
	}

	$nonce = sanitize_text_field( wp_unslash( $_POST['door_expert_home_featured_nonce'] ) );

	if ( ! wp_verify_nonce( $nonce, 'door_expert_home_featured_save' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_POST['door_expert_home_featured'] ) && '1' === sanitize_key( wp_unslash( $_POST['door_expert_home_featured'] ) ) ) {
		update_post_meta( $post_id, DOOR_EXPERT_HOME_FEATURED_META, '1' );
	} else {
		delete_post_meta( $post_id, DOOR_EXPERT_HOME_FEATURED_META );
	}
}
add_action( 'save_post_product', 'door_expert_home_featured_save' );

/**
 * Glavna (top-level) kategorija proizvoda – po njoj ide tab.
 *
 * Polazi od iste kategorije koju biraju i istaknuti atributi
 * (door_expert_highlights_order_terms: Rank Math primarna, pa najdublja), pa se
 * penje do korijena. Bez toga bi proizvod u dvije kategorije mogao da skače iz taba
 * u tab od učitavanja do učitavanja.
 *
 * @param int $product_id ID proizvoda.
 * @return WP_Term|null
 */
function door_expert_product_top_cat( $product_id ) {
	$terms = get_the_terms( $product_id, 'product_cat' );

	if ( ! is_array( $terms ) || empty( $terms ) ) {
		return null;
	}

	if ( function_exists( 'door_expert_highlights_order_terms' ) ) {
		$terms = door_expert_highlights_order_terms( $product_id, $terms );
	}

	foreach ( $terms as $term ) {
		if ( ! $term instanceof WP_Term || 'uncategorized' === $term->slug ) {
			continue;
		}

		$ancestors = get_ancestors( $term->term_id, 'product_cat', 'taxonomy' );
		$top_id    = empty( $ancestors ) ? $term->term_id : (int) end( $ancestors );
		$top       = get_term( $top_id, 'product_cat' );

		return $top instanceof WP_Term ? $top : null;
	}

	return null;
}

/**
 * Istaknuti proizvodi za početnu, sa tabovima.
 *
 * Tabovi se ne pišu ručno: prave se od glavnih kategorija u kojima ima bar jedan
 * istaknut proizvod, redom kao u meniju. Kategorija bez istaknutih nema tab.
 *
 * @return array{items:array<int,array{id:int,cat:string}>,tabs:array<int,array{slug:string,name:string}>}
 */
function door_expert_home_featured_data() {
	$empty = array(
		'items' => array(),
		'tabs'  => array(),
	);

	if ( ! function_exists( 'wc_get_product' ) ) {
		return $empty;
	}

	$ids = get_posts(
		array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'fields'         => 'ids',
			'posts_per_page' => 200,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => true,
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- jedan ključ, samo naslovna.
				array(
					'key'   => DOOR_EXPERT_HOME_FEATURED_META,
					'value' => '1',
				),
			),
		)
	);

	if ( empty( $ids ) ) {
		return $empty;
	}

	$items   = array();
	$counts  = array();
	$present = array();

	foreach ( $ids as $id ) {
		$product = wc_get_product( $id );

		// Proizvod sakriven iz kataloga ne smije iskočiti na naslovnoj.
		if ( ! $product instanceof WC_Product || ! $product->is_visible() ) {
			continue;
		}

		$top  = door_expert_product_top_cat( $id );
		$slug = $top ? $top->slug : '';

		if ( '' !== $slug ) {
			$counts[ $slug ] = isset( $counts[ $slug ] ) ? $counts[ $slug ] + 1 : 1;

			if ( $counts[ $slug ] > DOOR_EXPERT_HOME_FEATURED_PER_CAT ) {
				continue;
			}

			$present[ $top->term_id ] = true;
		}

		$items[] = array(
			'id'  => (int) $id,
			'cat' => $slug,
		);
	}

	$tabs = array();

	if ( ! empty( $present ) ) {
		$ordered = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'parent'     => 0,
				'orderby'    => 'menu_order',
				'hide_empty' => false,
			)
		);

		if ( ! is_wp_error( $ordered ) ) {
			foreach ( $ordered as $term ) {
				if ( isset( $present[ $term->term_id ] ) ) {
					$tabs[] = array(
						'slug' => $term->slug,
						'name' => $term->name,
					);
				}
			}
		}
	}

	return array(
		'items' => $items,
		'tabs'  => $tabs,
	);
}
