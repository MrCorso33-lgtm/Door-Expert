<?php
/**
 * Door Expert – functions.php
 *
 * Prati DOCS/WP_CUSTOM_DEV_BLUEPRINT.md (sekcije 1, 4, 7)
 * i DOCS/CSS_ARHITEKTURA.md (sekcija 2 – kondicionalni enqueue).
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ============================================================
// 0. THEME SETUP
// ============================================================

add_action( 'after_setup_theme', 'door_expert_setup' );
/**
 * Osnovna podrška teme.
 */
function door_expert_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );

	// WooCommerce – prototip ima korpu i single proizvod (korpa.html, product.html).
	add_theme_support( 'woocommerce' );

	register_nav_menus(
		array(
			'primary' => __( 'Glavna navigacija', 'door-expert' ),
			'mobile'  => __( 'Mobilna navigacija', 'door-expert' ),
			'footer'  => __( 'Footer meni', 'door-expert' ),
		)
	);
}

// ============================================================
// 1. ENQUEUE CSS / JS
// ============================================================

/**
 * Verzija asseta = filemtime() -> ?ver se menja na SVAKU izmenu fajla.
 *
 * NIKAD ne koristi wp_get_theme()->get('Version') za asete – ta verzija se
 * menja samo ručnim bump-om, pa izmene fajla ne bustaju keš automatski.
 *
 * @param string $rel Relativna putanja od root-a teme (npr. '/assets/css/header.css').
 * @return string Timestamp poslednje izmene, ili '1.0.0' ako fajl ne postoji.
 */
function door_expert_ver( $rel ) {
	$file = get_template_directory() . $rel;
	return file_exists( $file ) ? (string) filemtime( $file ) : '1.0.0';
}

/**
 * URL WooCommerce product kategorije po slug-u, bezbedno.
 *
 * Tema NAMERNO ne hardkoduje URL kategorija ni bazu (/c/, /product-category/...):
 * WooCommerce sam sklapa pun URL sa trenutno podešenom bazom. Ako se baza promeni
 * (npr. `c` → `.`), tema automatski prati – ništa se ne dira u šablonima.
 *
 * @param string $slug product_cat slug (npr. 'klizna', 'sobna-vrata').
 * @return string URL kategorije, ili home_url('/') kao fallback ako term ne postoji.
 */
function door_expert_cat_url( $slug ) {
	$link = get_term_link( $slug, 'product_cat' );
	return is_wp_error( $link ) ? home_url( '/' ) : $link;
}

/**
 * <img> kategorije iz njenog thumbnaila (Proizvodi → Kategorije → Thumbnail).
 *
 * Slika se tako mijenja iz admina, bez koda i bez FTP-a, dolazi sa našeg servera i
 * dobija srcset, pa telefon ne skida istu sliku kao desktop.
 *
 * Dok kategorija nema postavljenu sliku, ide $fallback_url (placeholder iz
 * prototipa) - da kartica ne ostane prazna dok klijent ne ubaci svoje fotografije.
 *
 * Alt: ako je u Medijima upisan alt za sliku, on pobjeđuje; inače ide $alt.
 *
 * @param string $slug         Slug product_cat kategorije.
 * @param string $fallback_url URL slike kad kategorija nema thumbnail.
 * @param string $alt          Alt tekst kad slika u Medijima nema svoj.
 * @param array  $attr         Dodatni atributi (class, sizes, loading...).
 * @return string HTML.
 */
function door_expert_cat_image( $slug, $fallback_url, $alt, $attr = array() ) {
	$attr = wp_parse_args(
		$attr,
		array(
			'loading' => 'lazy',
		)
	);

	$term     = get_term_by( 'slug', $slug, 'product_cat' );
	$image_id = $term instanceof WP_Term ? (int) get_term_meta( $term->term_id, 'thumbnail_id', true ) : 0;

	if ( $image_id && wp_attachment_is_image( $image_id ) ) {
		$own_alt = trim( (string) get_post_meta( $image_id, '_wp_attachment_image_alt', true ) );

		$attr['alt'] = '' !== $own_alt ? $own_alt : $alt;

		return wp_get_attachment_image( $image_id, 'large', false, $attr );
	}

	$html = '<img src="' . esc_url( $fallback_url ) . '" alt="' . esc_attr( $alt ) . '"';

	foreach ( $attr as $name => $value ) {
		if ( 'sizes' === $name || 'srcset' === $name ) {
			continue; // Placeholder je jedna fiksna slika, sizes bez srcset nema smisla.
		}
		$html .= ' ' . esc_attr( $name ) . '="' . esc_attr( $value ) . '"';
	}

	return $html . ' />';
}

add_action( 'wp_enqueue_scripts', 'door_expert_enqueue_assets' );
/**
 * Kondicionalni enqueue – svaka stranica dobija SAMO CSS koji joj treba.
 *
 * Redosled/zavisnosti: tokens (:root varijable) mora prvi; sve ostalo zavisi
 * od njega da bi varijable bile dostupne. Vidi DOCS/CSS_ARHITEKTURA.md, sekcija 2.
 */
function door_expert_enqueue_assets() {
	$uri = get_template_directory_uri();

	// ── Fontovi ───────────────────────────────────────────────
	// Prototip koristi Google Fonts CDN. Blueprint (sekcija 7.6) preporučuje
	// self-hosting (manje konekcija, GDPR-friendly, bolji LCP).
	// TODO: prebaciti na self-hosted woff2 + preload pre produkcije.
	wp_enqueue_style(
		'door-expert-fonts',
		'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=DM+Sans:wght@300;400;500;600;700&display=swap',
		array(),
		null
	);

	// ── GLOBAL: svuda ─────────────────────────────────────────
	// tokens.css nosi :root varijable – baza za sve ostalo.
	wp_enqueue_style( 'door-expert-tokens', $uri . '/assets/css/tokens.css', array(), door_expert_ver( '/assets/css/tokens.css' ) );
	// base.css: bazni body/html stilovi (background, font, boja, overflow) – mora posle tokens-a.
	wp_enqueue_style( 'door-expert-base', $uri . '/assets/css/base.css', array( 'door-expert-tokens' ), door_expert_ver( '/assets/css/base.css' ) );
	wp_enqueue_style( 'door-expert-header', $uri . '/assets/css/header.css', array( 'door-expert-tokens' ), door_expert_ver( '/assets/css/header.css' ) );
	wp_enqueue_style( 'door-expert-footer', $uri . '/assets/css/footer.css', array( 'door-expert-tokens' ), door_expert_ver( '/assets/css/footer.css' ) );

	wp_enqueue_script( 'door-expert-header-js', $uri . '/assets/js/header.js', array(), door_expert_ver( '/assets/js/header.js' ), true );

	// Kartica proizvoda (.prod-card) – JEDNA za cijeli sajt. Registruje se ovdje, a
	// učitava kao zavisnost svuda gdje se kartica pojavljuje (naslovna, kategorije,
	// prodavnica). Ranije su je stilizovali i category.css i featured.css, pa je
	// pobjeđivao onaj koji se kasnije učita.
	wp_register_style( 'door-expert-product-card', $uri . '/assets/css/product-card.css', array( 'door-expert-tokens' ), door_expert_ver( '/assets/css/product-card.css' ) );

	// ── KONDICIONALNO: naslovna ───────────────────────────────
	// Sekcije sa prototipa (header-demo.html): hero, trust-bar, categories,
	// featured, promo-banner, room-nav, brand-strip, instagram.
	if ( is_front_page() ) {
		wp_enqueue_style( 'door-expert-product-card' ); // "Odabrani za vas" crta istu karticu kao prodavnica.

		$home_styles = array( 'hero', 'trust-bar', 'categories', 'featured', 'promo-banner', 'room-nav', 'brand-strip', 'instagram' );
		foreach ( $home_styles as $handle ) {
			$rel = '/assets/css/' . $handle . '.css';
			wp_enqueue_style( 'door-expert-' . $handle, $uri . $rel, array( 'door-expert-tokens' ), door_expert_ver( $rel ) );
		}

		$home_scripts = array( 'hero', 'featured', 'room-nav' );
		foreach ( $home_scripts as $handle ) {
			$rel = '/assets/js/' . $handle . '.js';
			wp_enqueue_script( 'door-expert-' . $handle . '-js', $uri . $rel, array(), door_expert_ver( $rel ), true );
		}
	}

	// ── KONDICIONALNO: WooCommerce kategorije (product_cat) ────
	// category.css/js se učitava na SVIM kategorijama (sadrži i sve subcat- stilove).
	// Familija-specific (plocice/sigurnosna/umivaonici) SAMO na svom top-level roditelju
	// (potkategorije i sobna-vrata roditelj koriste samo category.css) – kao u prototipu.
	if ( function_exists( 'is_product_category' ) && is_product_category() ) {
		wp_enqueue_style( 'door-expert-category', $uri . '/assets/css/category.css', array( 'door-expert-tokens', 'door-expert-product-card' ), door_expert_ver( '/assets/css/category.css' ) );
		// subcat.css: subcat- stilovi (izvučeni iz inline <style> prototipa) – koristi ih
		// subcategory.php (sve potkategorije + roditelji bez bespoke parta).
		wp_enqueue_style( 'door-expert-subcat', $uri . '/assets/css/subcat.css', array( 'door-expert-category' ), door_expert_ver( '/assets/css/subcat.css' ) );
		wp_enqueue_script( 'door-expert-category-js', $uri . '/assets/js/category.js', array(), door_expert_ver( '/assets/js/category.js' ), true );

		// Kategorijski listing koristi isti .shop-* markup kao prodavnica
		// (template-parts/category/parts/product-grid.php + shop/filters.php),
		// pa mu trebaju i ti stilovi + JS za accordion i mobilni toggle filtera.
		wp_enqueue_style( 'door-expert-prodavnica', $uri . '/assets/css/prodavnica.css', array( 'door-expert-category' ), door_expert_ver( '/assets/css/prodavnica.css' ) );
		wp_enqueue_script( 'door-expert-prodavnica-js', $uri . '/assets/js/prodavnica.js', array(), door_expert_ver( '/assets/js/prodavnica.js' ), true );

		$term = get_queried_object();
		if ( $term instanceof WP_Term && 0 === (int) $term->parent ) {
			$family_assets = array(
				'keramicke-plocice' => 'plocice',
				'sigurnosna-vrata'  => 'sigurnosna',
				'umivaonici'        => 'umivaonici',
			);
			if ( isset( $family_assets[ $term->slug ] ) ) {
				$fam = $family_assets[ $term->slug ];
				$dir = get_template_directory();

				// Familija nema uvijek oba fajla (npr. plocice.js je sklonjen na _shelf
				// zajedno sa kalkulatorom), pa enqueue-ujemo samo ono što postoji.
				if ( file_exists( $dir . '/assets/css/' . $fam . '.css' ) ) {
					wp_enqueue_style( 'door-expert-' . $fam, $uri . '/assets/css/' . $fam . '.css', array( 'door-expert-category' ), door_expert_ver( '/assets/css/' . $fam . '.css' ) );
				}
				if ( file_exists( $dir . '/assets/js/' . $fam . '.js' ) ) {
					wp_enqueue_script( 'door-expert-' . $fam . '-js', $uri . '/assets/js/' . $fam . '.js', array( 'door-expert-category-js' ), door_expert_ver( '/assets/js/' . $fam . '.js' ), true );
				}
			}
		}
	}

	// ── KONDICIONALNO: WP stranice (page.php router) ──────────
	// Mapiranje slug → {css, js}. Enqueue SAMO kad asset postoji u temi;
	// b2b/montaza/brendovi dobijaju CSS tek kad se konvertuju (dodati unos ovdje).
	if ( is_page() ) {
		$page_assets = array(
			'o-nama'      => array( 'css' => array( 'o-nama' ),      'js' => array() ),
			'kontakt'     => array( 'css' => array( 'contact' ),     'js' => array() ),
			'akcije'      => array( 'css' => array( 'akcije' ),      'js' => array( 'akcije' ) ),
			'inspiracija' => array( 'css' => array( 'inspiracija' ), 'js' => array( 'inspiracija' ) ),
			'brendovi'    => array( 'css' => array( 'brendovi' ),    'js' => array() ),
			'montaza'     => array( 'css' => array( 'montaza' ),     'js' => array() ),
			'hvala'       => array( 'css' => array( 'hvala' ),       'js' => array() ),
			'new-tiles'       => array( 'css' => array( 'brand' ), 'js' => array() ),
			'tau-ceramica'    => array( 'css' => array( 'brand' ), 'js' => array() ),
			'arcana-ceramica' => array( 'css' => array( 'brand' ), 'js' => array() ),
			'ceramica-ribesalbes' => array( 'css' => array( 'brand' ), 'js' => array() ),
			'bathco'          => array( 'css' => array( 'brand' ), 'js' => array() ),
		);
		$de_page = get_queried_object();
		$de_slug = ( $de_page instanceof WP_Post ) ? $de_page->post_name : '';
		if ( isset( $page_assets[ $de_slug ] ) ) {
			foreach ( $page_assets[ $de_slug ]['css'] as $handle ) {
				$rel = '/assets/css/' . $handle . '.css';
				wp_enqueue_style( 'door-expert-' . $handle, $uri . $rel, array( 'door-expert-tokens' ), door_expert_ver( $rel ) );
			}
			foreach ( $page_assets[ $de_slug ]['js'] as $handle ) {
				$rel = '/assets/js/' . $handle . '.js';
				wp_enqueue_script( 'door-expert-' . $handle . '-js', $uri . $rel, array(), door_expert_ver( $rel ), true );
			}
		}
	}

	// ── KONDICIONALNO: WooCommerce shop arhiva (Prodavnica) ───
	// Kartica proizvoda stiže iz product-card.css, kao zavisnost category.css.
	if ( function_exists( 'is_shop' ) && is_shop() ) {
		wp_enqueue_style( 'door-expert-category', $uri . '/assets/css/category.css', array( 'door-expert-tokens', 'door-expert-product-card' ), door_expert_ver( '/assets/css/category.css' ) );
		wp_enqueue_style( 'door-expert-prodavnica', $uri . '/assets/css/prodavnica.css', array( 'door-expert-category' ), door_expert_ver( '/assets/css/prodavnica.css' ) );
		wp_enqueue_script( 'door-expert-prodavnica-js', $uri . '/assets/js/prodavnica.js', array(), door_expert_ver( '/assets/js/prodavnica.js' ), true );
	}

	// ── KONDICIONALNO: WooCommerce single proizvod + korpa ────
	if ( function_exists( 'is_product' ) && is_product() ) {
		wp_enqueue_style( 'door-expert-product', $uri . '/assets/css/product.css', array( 'door-expert-tokens' ), door_expert_ver( '/assets/css/product.css' ) );

		/*
		 * Varijabilni proizvodi: single-product.php ne poziva woocommerce_content(), pa WC ne
		 * enqueue-uje svoju varijacijsku skriptu sam. Dodajemo je i kao ZAVISNOST nasoj skripti
		 * da bi WC-ov $(document).ready (init variations_form) isao PRIJE naseg mosta pilula.
		 */
		$deps        = array();
		$de_variable = false;
		if ( function_exists( 'wc_get_product' ) ) {
			$de_queried = wc_get_product( get_queried_object_id() );
			if ( $de_queried instanceof WC_Product && $de_queried->is_type( 'variable' ) ) {
				$de_variable = true;
				wp_enqueue_script( 'wc-add-to-cart-variation' );
				$deps[] = 'wc-add-to-cart-variation';
			}
		}

		wp_enqueue_script( 'door-expert-product-js', $uri . '/assets/js/product.js', $deps, door_expert_ver( '/assets/js/product.js' ), true );

		// function_exists: inc/product.php se na server salje rucno; bez ove zastite
		// izostavljen fajl obori SVAKU stranicu proizvoda kroz wp_head, a ne samo ovaj podatak.
		if ( $de_variable && function_exists( 'door_expert_stock_display' ) ) {
			// Tekstovi zalihe – isti izvor kao PHP prikaz (inc/product.php), da se ne razilaze.
			wp_localize_script(
				'door-expert-product-js',
				'doorExpertStock',
				array(
					'instock'     => door_expert_stock_display( 'instock' ),
					'onbackorder' => door_expert_stock_display( 'onbackorder' ),
					'outofstock'  => door_expert_stock_display( 'outofstock' ),
				)
			);

			// Tekst za varijaciju bez cijene – isti izvor kao PHP prikaz (inc/quote-cart.php).
			if ( function_exists( 'door_expert_price_on_request' ) ) {
				wp_localize_script(
					'door-expert-product-js',
					'doorExpertPrice',
					array( 'onRequest' => door_expert_price_on_request() )
				);
			}
		}
	}
	if ( function_exists( 'is_cart' ) && is_cart() ) {
		// product-card.css: tab "Sačuvano" crta istu karticu kao prodavnica (inc/wishlist.php).
		wp_enqueue_style( 'door-expert-korpa', $uri . '/assets/css/korpa.css', array( 'door-expert-tokens', 'door-expert-product-card' ), door_expert_ver( '/assets/css/korpa.css' ) );
		wp_enqueue_script( 'door-expert-korpa-js', $uri . '/assets/js/korpa.js', array(), door_expert_ver( '/assets/js/korpa.js' ), true );

		// Quote cart AJAX (inc/quote-cart.php): količina, uklanjanje, slanje upita.
		wp_localize_script(
			'door-expert-korpa-js',
			'doorExpert',
			array(
				'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
				'cartNonce'    => wp_create_nonce( 'door_expert_cart' ),
				'inquiryNonce' => wp_create_nonce( 'door_expert_inquiry_nonce' ),
			)
		);
	}

	// ── GLOBAL: lista sačuvanih (inc/wishlist.php) ────────────
	// Svuda, jer je srce sa brojem u headeru na svakoj stranici. Uz to: srce na
	// kartici, dugme na PDP-u, tab "Sačuvano" u korpi. Mali fajlovi (~3 KB gzip).
	if ( function_exists( 'door_expert_cart_url' ) ) {
		wp_enqueue_style( 'door-expert-wishlist', $uri . '/assets/css/wishlist.css', array( 'door-expert-tokens' ), door_expert_ver( '/assets/css/wishlist.css' ) );
		wp_enqueue_script( 'door-expert-wishlist-js', $uri . '/assets/js/wishlist.js', array(), door_expert_ver( '/assets/js/wishlist.js' ), true );
		wp_localize_script(
			'door-expert-wishlist-js',
			'doorExpertWishlistData',
			array(
				'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'door_expert_wishlist' ),
				'savedUrl' => door_expert_cart_url() . '#sacuvano',
				'cartUrl'  => door_expert_cart_url(),
				// Kolačić "upravo dodato u ponudu" (inc/wishlist.php) – JS ga čita i briše
				// sa istom putanjom/domenom kojom ga je WC postavio.
				'added'    => array(
					'cookie' => defined( 'DOOR_EXPERT_WISHLIST_ADDED_COOKIE' ) ? DOOR_EXPERT_WISHLIST_ADDED_COOKIE : '',
					'path'   => COOKIEPATH ? COOKIEPATH : '/',
					'domain' => COOKIE_DOMAIN ? COOKIE_DOMAIN : '',
				),
			)
		);
	}

	// ── KONDICIONALNO: 404 ────────────────────────────────────
	if ( is_404() ) {
		wp_enqueue_style( 'door-expert-404', $uri . '/assets/css/404.css', array( 'door-expert-tokens' ), door_expert_ver( '/assets/css/404.css' ) );
		wp_enqueue_script( 'door-expert-404-js', $uri . '/assets/js/404.js', array(), door_expert_ver( '/assets/js/404.js' ), true );
	}

	// ── AJAX lokalizacija ─────────────────────────────────────
	wp_localize_script(
		'door-expert-header-js',
		'doorExpertData',
		array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'door_expert_nonce' ),
			'homeUrl' => home_url( '/' ),
		)
	);
}

// ============================================================
// 1.1 – Ukloni WP version meta tagove iz <head>
// ============================================================

remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );

// ============================================================
// 1.2 – ?ver= cache-busting – NE SKIDATI sa tema aseta!
// ============================================================
//
// ⚠️ NAJČEŠĆA GREŠKA (izgubljen dan na Saya projektu).
// filemtime() upisuje verziju baš u ?ver=. Ako je skineš, URL fajla se nikad
// ne menja pri izmeni -> browser/CDN drži stari CSS/JS mesecima -> "promenio
// sam kod, deploy prošao, a na sajtu se ništa ne menja".
//
// Ovde SVESNO ne registrujemo nikakav style_loader_src / script_loader_src
// filter koji briše ?ver. WordPress default je ispravan.
//
// Ako neki plugin doda takav filter, izuzmi temu:
//   if ( strpos( $src, '/wp-content/themes/' ) !== false ) { return $src; }
//
// LiteSpeed Cache: Page Optimization -> HTML Settings -> "Remove Query Strings" = OFF.
// Detalji: DOCS/WP_CUSTOM_DEV_BLUEPRINT.md sekcije 1.2 i 14.

// ============================================================
// 2. SECURITY HEADERS
// ============================================================

add_action( 'send_headers', 'door_expert_security_headers' );
/**
 * Osnovni HTTP security headeri.
 *
 * HSTS namerno NIJE ovde – ide u .htaccess (Apache nivo), jer se PHP ne
 * izvršava za keširane stranice. Vidi blueprint sekcija 5.
 */
function door_expert_security_headers() {
	header( 'X-Frame-Options: SAMEORIGIN' );
	header( 'X-Content-Type-Options: nosniff' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
}

// ============================================================
// 3. ISKLJUČI WP BLOAT (frontend)
// ============================================================

// Emoji skripte/stilovi – ~10KB + extra request, nepotrebno.
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );

// oEmbed discovery – ne ugrađujemo tuđe postove.
remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );

add_action( 'wp_footer', 'door_expert_dequeue_embed' );
/**
 * Skini wp-embed.js sa frontenda.
 */
function door_expert_dequeue_embed() {
	wp_dequeue_script( 'wp-embed' );
}

add_action( 'wp_enqueue_scripts', 'door_expert_dequeue_bloat', 100 );
/**
 * Dashicons i blok-editor CSS na frontendu.
 *
 * Tema ne koristi Gutenberg blokove u sadržaju (sve je custom template),
 * pa wp-block-library nije potreban.
 */
function door_expert_dequeue_bloat() {
	if ( ! is_user_logged_in() ) {
		wp_dequeue_style( 'dashicons' );
	}
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'global-styles' );
}

// ============================================================
// 4. WOOCOMMERCE INTEGRACIJA
// ============================================================

// Sadržaj kategorijskih stranica – odvojen od prikaza (Faza A: hardkodovano;
// migracija: get_term_meta). Vidi inc/category-content.php i plan migracije na JetEngine.
require_once get_template_directory() . '/inc/category-content.php';
// Sadržaj/dijeljeni podaci WP stranica (page.php router) – isti obrazac razdvajanja.
require_once get_template_directory() . '/inc/page-content.php';
// Single proizvod (PDP) – grupa proizvoda + FAQ sadržaj (odvojeno od prikaza).
require_once get_template_directory() . '/inc/product.php';
// Varijacije – vrata naručljiva i van lagera (quote model) + tekst dostupnosti.
require_once get_template_directory() . '/inc/product-variations.php';
// Istaknuti atributi (PDP traka + čipovi na kartici) – podaci iz postojećih WC
// atributa, izbor iz admina.
require_once get_template_directory() . '/inc/product-highlights.php';
// Početna "Odabrani za vas" – kvadratić na proizvodu + upit (posle product-highlights: koristi njegov redoslijed kategorija).
require_once get_template_directory() . '/inc/home-featured.php';
if ( is_admin() ) {
	require_once get_template_directory() . '/inc/product-highlights-admin.php';
}
// Kontakt forma – custom AJAX handler (nonce + sanitizacija + rate limit + wp_mail).
require_once get_template_directory() . '/inc/contact-form.php';
// Sadržaj brend stranica (5 brendova, uniforman prikaz) – ODVOJEN od prikaza.
require_once get_template_directory() . '/inc/brand-content.php';
// Shop (Prodavnica) – WooCommerce arhiva: server-side filteri/sort za archive-product.php.
require_once get_template_directory() . '/inc/shop.php';
// Filteri – most ka pluginu WC Filter Configurator (sidebar iz admina + opseg po kategoriji).
require_once get_template_directory() . '/inc/filters.php';
// SEO filtriranih arhiva – noindex + canonical. POSLIJE shop.php i filters.php (koristi oba).
require_once get_template_directory() . '/inc/filters-seo.php';
// AJAX filtriranje listinga – isti upit i isti markup kao serverska putanja.
require_once get_template_directory() . '/inc/shop-ajax.php';
// Quote cart – WooCommerce bez plaćanja: upit pravi WC_Order (on-hold) + AJAX korpa.
require_once get_template_directory() . '/inc/quote-cart.php';
// Lista sačuvanih ("Sačuvaj za projekat") – tab "Sačuvano" u korpi. POSLIJE quote-cart.php.
require_once get_template_directory() . '/inc/wishlist.php';

// Otkači default WC wrappere – naš <main> (header.php/footer.php) kontroliše layout,
// a taxonomy-product_cat.php sam renderuje sekcije. Bez ovoga WC ubacuje svoj
// <div class="woocommerce"> wrapper i duplira strukturu stranice.
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
