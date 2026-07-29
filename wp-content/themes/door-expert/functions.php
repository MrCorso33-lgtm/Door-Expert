<?php
/**
 * Door Expert — functions.php
 *
 * Prati DOCS/WP_CUSTOM_DEV_BLUEPRINT.md (sekcije 1, 4, 7)
 * i DOCS/CSS_ARHITEKTURA.md (sekcija 2 — kondicionalni enqueue).
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

	// WooCommerce — prototip ima korpu i single proizvod (korpa.html, product.html).
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
 * NIKAD ne koristi wp_get_theme()->get('Version') za asete — ta verzija se
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
 * (npr. `c` → `.`), tema automatski prati — ništa se ne dira u šablonima.
 *
 * @param string $slug product_cat slug (npr. 'klizna', 'sobna-vrata').
 * @return string URL kategorije, ili home_url('/') kao fallback ako term ne postoji.
 */
function door_expert_cat_url( $slug ) {
	$link = get_term_link( $slug, 'product_cat' );
	return is_wp_error( $link ) ? home_url( '/' ) : $link;
}

add_action( 'wp_enqueue_scripts', 'door_expert_enqueue_assets' );
/**
 * Kondicionalni enqueue — svaka stranica dobija SAMO CSS koji joj treba.
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
	// tokens.css nosi :root varijable — baza za sve ostalo.
	wp_enqueue_style( 'door-expert-tokens', $uri . '/assets/css/tokens.css', array(), door_expert_ver( '/assets/css/tokens.css' ) );
	// base.css: bazni body/html stilovi (background, font, boja, overflow) — mora posle tokens-a.
	wp_enqueue_style( 'door-expert-base', $uri . '/assets/css/base.css', array( 'door-expert-tokens' ), door_expert_ver( '/assets/css/base.css' ) );
	wp_enqueue_style( 'door-expert-header', $uri . '/assets/css/header.css', array( 'door-expert-tokens' ), door_expert_ver( '/assets/css/header.css' ) );
	wp_enqueue_style( 'door-expert-footer', $uri . '/assets/css/footer.css', array( 'door-expert-tokens' ), door_expert_ver( '/assets/css/footer.css' ) );

	wp_enqueue_script( 'door-expert-header-js', $uri . '/assets/js/header.js', array(), door_expert_ver( '/assets/js/header.js' ), true );

	// ── KONDICIONALNO: naslovna ───────────────────────────────
	// Sekcije sa prototipa (header-demo.html): hero, trust-bar, categories,
	// featured, promo-banner, room-nav, brand-strip, instagram.
	if ( is_front_page() ) {
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

	// ── TODO: ostale stranice ─────────────────────────────────
	// Dodavati kako se konvertuju, po istom obrascu. Mapiranje prototip -> uslov:
	//   product.html          -> is_product()          : product.css / product.js
	//   korpa.html            -> is_cart()             : korpa.css / korpa.js
	//   kontakt.html          -> is_page('kontakt')    : contact.css
	//   o-nama.html           -> is_page('o-nama')     : o-nama.css
	//   akcije.html           -> is_page('akcije')     : akcije.css / akcije.js
	//   inspiracija.html      -> is_page('inspiracija'): inspiracija.css / inspiracija.js
	//   sigurnosna-vrata.html -> is_tax(...)           : sigurnosna.css / sigurnosna.js
	//   umivaonici.html       -> is_tax(...)           : umivaonici.css / umivaonici.js
	//   plocice-*.html        -> is_tax(...)           : plocice.css / plocice.js
	//   404.html              -> is_404()              : 404.css / 404.js
	// Kategorijske/arhive stranice: category.css / category.js.

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
// 1.1 — Ukloni WP version meta tagove iz <head>
// ============================================================

remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );

// ============================================================
// 1.2 — ?ver= cache-busting — NE SKIDATI sa tema aseta!
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
 * HSTS namerno NIJE ovde — ide u .htaccess (Apache nivo), jer se PHP ne
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

// Emoji skripte/stilovi — ~10KB + extra request, nepotrebno.
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );

// oEmbed discovery — ne ugrađujemo tuđe postove.
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
