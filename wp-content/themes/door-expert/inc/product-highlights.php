<?php
/**
 * Istaknuti atributi – podatkovni sloj.
 *
 * Nekoliko najvažnijih osobina proizvoda. Na PDP-u kao ikonica + labela + vrijednost
 * (iznad CTA-a na desktopu, ispod njega na telefonu), a na kartici u listingu kao
 * goli čipovi ispod naziva – jedan izbor u adminu, dva prikaza.
 *
 * KLJUČNO: vrijednosti se NE unose nigdje ponovo. Čitaju se iz postojećih WC
 * atributa koje klijent ionako popunjava zbog filtera (`$product->get_attribute()`).
 * Iz admina se bira samo KOJI atributi se ističu za koju kategoriju i kojom ikonom.
 *
 * Konfiguracija živi u jednoj opciji, po kontekstu:
 *   'default'    fallback kad nijedna kategorija nema svoje
 *   '<term_id>'  jedna product_cat kategorija
 *
 * Kontekst je term ID, a ne slug: ID je trajan, a slug se mijenja čim neko
 * preimenuje kategoriju, što bi tiho obrisalo njeno podešavanje. Isti razlog
 * i isti obrazac koji koristi i wc-filter-configurator.
 *
 * Nasljeđivanje: potkategorija bez svoje konfiguracije preuzima od najbližeg
 * pretka koji je ima. Bez toga bi klijent morao popunjavati svaku potkategoriju.
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const DOOR_EXPERT_HIGHLIGHTS_OPTION = 'door_expert_highlights';
const DOOR_EXPERT_HIGHLIGHTS_MAX    = 4;

// Koliko cipova staje na karticu u listingu. Manje nego na PDP-u: kartica je uska
// i cetvrti cip je u prototipu vec prelamao red ispod naziva.
const DOOR_EXPERT_HIGHLIGHTS_CHIPS = 3;

// Kontekst koji je namjerno prazan. Razlikuje "ova kategorija nema traku" od
// "ova kategorija nije podesena, pa nasljedjuje od pretka" - bez ovoga je jedini
// nacin da se traka ukloni bio da se ukloni i svim pretcima.
const DOOR_EXPERT_HIGHLIGHTS_NONE = 'none';

/**
 * Najvise redova po kontekstu.
 *
 * Filter jer keramika podnosi vise stavki nego uska kolona kod vrata.
 *
 * @return int
 */
function door_expert_highlights_max() {
	$max = (int) apply_filters( 'door_expert_highlights_max', DOOR_EXPERT_HIGHLIGHTS_MAX );

	return max( 1, min( 8, $max ) );
}

/**
 * Izvori vrijednosti koje red moze da istakne.
 *
 * Nije samo `pa_*`: klijent hoce da istakne i brend, i sifru, i tvrdnju koja
 * nigdje ne postoji kao atribut ("Garancija 2 godine"). Kljucevi su birani tako
 * da prezive sanitize_key() (mala slova, cifre, `_`, `-`) i da se ne sudare sa
 * imenima atributskih taksonomija.
 *
 * @return array<string,array{label:string,group:string}>
 */
function door_expert_highlights_sources() {
	$sources = array();

	if ( function_exists( 'wc_get_attribute_taxonomies' ) ) {
		foreach ( wc_get_attribute_taxonomies() as $tax ) {
			$slug = wc_attribute_taxonomy_name( $tax->attribute_name );

			$sources[ $slug ] = array(
				'label' => $tax->attribute_label ? $tax->attribute_label : $tax->attribute_name,
				'group' => 'Atributi',
			);
		}
	}

	if ( taxonomy_exists( 'product_brand' ) ) {
		$sources['product_brand'] = array(
			'label' => 'Brend',
			'group' => 'Ostalo',
		);
	}

	$sources['_de_sku'] = array(
		'label' => 'Šifra proizvoda',
		'group' => 'Ostalo',
	);

	$sources['_de_stock'] = array(
		'label' => 'Dostupnost',
		'group' => 'Ostalo',
	);

	$sources['_de_static'] = array(
		'label' => 'Statičan tekst',
		'group' => 'Ostalo',
	);

	return $sources;
}

/**
 * Labela izvora, kakva stoji u adminu.
 *
 * @param string $key Kljuc izvora.
 * @return string
 */
function door_expert_highlights_source_label( $key ) {
	$sources = door_expert_highlights_sources();

	if ( isset( $sources[ $key ] ) ) {
		return $sources[ $key ]['label'];
	}

	// Atribut je u medjuvremenu obrisan iz WC-a; bar necemo ispisati prazno.
	return function_exists( 'wc_attribute_label' ) ? wc_attribute_label( $key ) : $key;
}

/**
 * Ujednaci razmake oko zareza u vrijednosti sa vise clanova.
 *
 * @param string $value Sirova vrijednost.
 * @return string
 */
function door_expert_highlights_clean_value( $value ) {
	return trim( preg_replace( '/\s*,\s*/', ', ', (string) $value ) );
}

/**
 * Vrijednost jednog izvora za dati proizvod.
 *
 * Prazan string znaci "nema podatka" - red tada pada na svoj rezervni tekst, a
 * ako ni njega nema, red se preskace.
 *
 * @param WC_Product $product Proizvod.
 * @param string     $key     Kljuc izvora.
 * @return string
 */
function door_expert_highlights_value( $product, $key ) {
	if ( ! $product instanceof WC_Product || '' === $key ) {
		return '';
	}

	// Statican red nema sta da cita sa proizvoda; njegov tekst je rezervni tekst.
	if ( '_de_static' === $key ) {
		return '';
	}

	if ( '_de_sku' === $key ) {
		return door_expert_highlights_clean_value( $product->get_sku() );
	}

	if ( '_de_stock' === $key ) {
		return $product->is_in_stock() ? 'Na stanju' : 'Po narudžbi';
	}

	if ( 0 === strpos( $key, 'pa_' ) ) {
		return door_expert_highlights_clean_value( $product->get_attribute( $key ) );
	}

	if ( taxonomy_exists( $key ) ) {
		$terms = get_the_terms( $product->get_id(), $key );

		if ( is_array( $terms ) && ! empty( $terms ) ) {
			return door_expert_highlights_clean_value( implode( ', ', wp_list_pluck( $terms, 'name' ) ) );
		}

		return '';
	}

	// Lokalni (per-product) atribut: get_attribute() ga nalazi po imenu.
	return door_expert_highlights_clean_value( $product->get_attribute( $key ) );
}

/**
 * Set ikonica u temi.
 *
 * Namjerno zatvoren spisak, bez uploada iz admina: različite debljine linija i
 * stilovi na istoj traci razbiju mirnu tipografiju prototipa. Svi su 24x24,
 * stroke=currentColor, kao i ostale ikone na PDP-u.
 *
 * @return array<string,array{label:string,svg:string}>
 */
function door_expert_highlight_icons() {
	return array(
		'layers'      => array(
			'label' => 'Materijal (slojevi)',
			'svg'   => '<polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/>',
		),
		'ruler'       => array(
			'label' => 'Dimenzije (lenjir)',
			'svg'   => '<rect x="2" y="8" width="20" height="8" rx="1"/><line x1="7" y1="8" x2="7" y2="12"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="17" y1="8" x2="17" y2="12"/>',
		),
		'thickness'   => array(
			'label' => 'Debljina (strelice)',
			'svg'   => '<line x1="4" y1="4" x2="4" y2="20"/><line x1="20" y1="4" x2="20" y2="20"/><line x1="4" y1="12" x2="20" y2="12"/><polyline points="8 8 4 12 8 16"/><polyline points="16 8 20 12 16 16"/>',
		),
		'shield'      => array(
			'label' => 'Sigurnost (štit)',
			'svg'   => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
		),
		'warranty'    => array(
			'label' => 'Garancija (štit sa kvačicom)',
			'svg'   => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/>',
		),
		'lock'        => array(
			'label' => 'Brava',
			'svg'   => '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>',
		),
		'door'        => array(
			'label' => 'Vrata / otvaranje',
			'svg'   => '<line x1="3" y1="21" x2="21" y2="21"/><path d="M6 21V4a1 1 0 011-1h10a1 1 0 011 1v17"/><circle cx="15" cy="12" r="1"/>',
		),
		'sound'       => array(
			'label' => 'Zvučna izolacija',
			'svg'   => '<polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M15.5 8.5a5 5 0 010 7"/><path d="M19 5a9 9 0 010 14"/>',
		),
		'thermometer' => array(
			'label' => 'Toplotna izolacija',
			'svg'   => '<path d="M14 14.76V3.5a2.5 2.5 0 00-5 0v11.26a4 4 0 105 0z"/>',
		),
		'droplet'     => array(
			'label' => 'Vodootpornost',
			'svg'   => '<path d="M12 2.7l5.2 5.2a7.35 7.35 0 11-10.4 0L12 2.7z"/>',
		),
		'grid'        => array(
			'label' => 'Format (mreža)',
			'svg'   => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>',
		),
		'shine'       => array(
			'label' => 'Površina / obrada (sjaj)',
			'svg'   => '<path d="M12 3l1.9 4.8L19 9.7l-4 3.4 1.2 5.2L12 15.6 7.8 18.3 9 13.1 5 9.7l5.1-1.9L12 3z"/>',
		),
		'room'        => array(
			'label' => 'Namjena / prostorija',
			'svg'   => '<path d="M3 10.5L12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/>',
		),
		'globe'       => array(
			'label' => 'Porijeklo',
			'svg'   => '<circle cx="12" cy="12" r="9"/><line x1="3" y1="12" x2="21" y2="12"/><path d="M12 3a15 15 0 010 18a15 15 0 010-18z"/>',
		),
		'tool'        => array(
			'label' => 'Montaža (alat)',
			'svg'   => '<path d="M14.7 6.3a4 4 0 00-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 005.4-5.4l-2.8 2.8-2.1-2.1 2.9-2.7z"/>',
		),
		'box'         => array(
			'label' => 'Pakovanje',
			'svg'   => '<path d="M21 16V8l-9-5-9 5v8l9 5 9-5z"/><polyline points="3.3 7.5 12 12.5 20.7 7.5"/><line x1="12" y1="22" x2="12" y2="12.5"/>',
		),
	);
}

/**
 * Dozvoljeni tagovi za inline SVG kroz wp_kses().
 *
 * Markup dolazi iz naseg zatvorenog niza iznad, ali ga svejedno propustamo kroz
 * kses – late escaping vazi i za podatke koje sami pisemo (CLAUDE.md §4).
 *
 * @return array
 */
function door_expert_svg_allowed_html() {
	return array(
		'svg'      => array(
			'width'          => true,
			'height'         => true,
			'viewbox'        => true,
			'fill'           => true,
			'stroke'         => true,
			'stroke-width'   => true,
			'stroke-linecap' => true,
			'stroke-linejoin' => true,
			'aria-hidden'    => true,
			'focusable'      => true,
			'class'          => true,
		),
		'path'     => array( 'd' => true ),
		'circle'   => array(
			'cx' => true,
			'cy' => true,
			'r'  => true,
		),
		'rect'     => array(
			'x'      => true,
			'y'      => true,
			'width'  => true,
			'height' => true,
			'rx'     => true,
		),
		'line'     => array(
			'x1' => true,
			'y1' => true,
			'x2' => true,
			'y2' => true,
		),
		'polyline' => array( 'points' => true ),
		'polygon'  => array( 'points' => true ),
	);
}

/**
 * Cijela sačuvana konfiguracija.
 *
 * @return array<string,array>
 */
function door_expert_highlights_all() {
	static $cache = null;

	// Statički keš jer listing zove ovo jednom po kartici (24+ puta po strani).
	if ( null === $cache ) {
		$saved = get_option( DOOR_EXPERT_HIGHLIGHTS_OPTION, array() );
		$cache = is_array( $saved ) ? $saved : array();
	}

	return $cache;
}

/**
 * Redovi za jedan kontekst, bez nasljeđivanja.
 *
 * @param string $context 'default' ili term ID kao string.
 * @return array
 */
function door_expert_highlights_rows( $context ) {
	$all = door_expert_highlights_all();

	return isset( $all[ $context ] ) && is_array( $all[ $context ] ) ? $all[ $context ] : array();
}

/**
 * Da li je kontekst namjerno prazan (zaustavlja nasljeđivanje).
 *
 * @param string $context 'default' ili term ID kao string.
 * @return bool
 */
function door_expert_highlights_is_none( $context ) {
	$all = door_expert_highlights_all();

	return isset( $all[ $context ] ) && DOOR_EXPERT_HIGHLIGHTS_NONE === $all[ $context ];
}

/**
 * Da li kontekst ima svoje podešavanje (redove ili namjerno prazno).
 *
 * @param string $context 'default' ili term ID kao string.
 * @return bool
 */
function door_expert_highlights_has_own( $context ) {
	$all = door_expert_highlights_all();

	return ! empty( $all[ $context ] );
}

/**
 * Od koga jedna kategorija preuzima podešavanje.
 *
 * Isti obrazac kao door_expert_highlights_context(), ali polazi od termina, ne od
 * proizvoda - admin mora da pokaže šta kategorija nasljeđuje prije nego što u njoj
 * bude ijedan proizvod.
 *
 * @param string $context 'default' ili term ID kao string.
 * @return string Kontekst koji stvarno daje redove.
 */
function door_expert_highlights_source_context( $context ) {
	if ( 'default' === $context || door_expert_highlights_has_own( $context ) ) {
		return $context;
	}

	$term = get_term( (int) $context, 'product_cat' );

	while ( $term instanceof WP_Term && 0 !== (int) $term->parent ) {
		$parent = get_term( $term->parent, 'product_cat' );

		if ( ! $parent instanceof WP_Term ) {
			break;
		}

		if ( door_expert_highlights_has_own( (string) $parent->term_id ) ) {
			return (string) $parent->term_id;
		}

		$term = $parent;
	}

	return 'default';
}

/**
 * Očisti redove prije upisa u bazu.
 *
 * @param mixed $rows Sirovi $_POST podaci.
 * @return array
 */
function door_expert_highlights_sanitize_rows( $rows ) {
	if ( ! is_array( $rows ) ) {
		return array();
	}

	$icons   = door_expert_highlight_icons();
	$sources = door_expert_highlights_sources();
	$max     = door_expert_highlights_max();
	$clean   = array();
	$seen    = array();

	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$attr = isset( $row['attr'] ) ? sanitize_key( $row['attr'] ) : '';
		if ( '' === $attr ) {
			continue; // Prazan izbor = obrisan red.
		}

		// Izvor mora postojati sada, u trenutku upisa. Atribut obrisan iz WC-a
		// ovdje ispada iz konfiguracije umjesto da tiho ostane kao mrtav red.
		if ( ! isset( $sources[ $attr ] ) ) {
			continue;
		}

		// Isti izvor dva puta daje dvije iste stavke; zadrzavamo prvu.
		// Statican tekst je izuzetak: dva razlicita teksta su dvije razlicite tvrdnje.
		if ( '_de_static' !== $attr && in_array( $attr, $seen, true ) ) {
			continue;
		}

		$icon = isset( $row['icon'] ) ? sanitize_key( $row['icon'] ) : '';
		if ( ! isset( $icons[ $icon ] ) ) {
			$icon = '';
		}

		$fallback = isset( $row['fallback'] ) ? sanitize_text_field( $row['fallback'] ) : '';

		// Statican red bez teksta nema sta da prikaze.
		if ( '_de_static' === $attr && '' === $fallback ) {
			continue;
		}

		$seen[]  = $attr;
		$clean[] = array(
			'attr'     => $attr,
			'icon'     => $icon,
			'label'    => isset( $row['label'] ) ? sanitize_text_field( $row['label'] ) : '',
			'fallback' => $fallback,
		);

		if ( count( $clean ) >= $max ) {
			break;
		}
	}

	return $clean;
}

/**
 * Poredaj kategorije proizvoda tako da izbor konteksta bude predvidiv.
 *
 * get_the_terms() ne garantuje redoslijed, pa je proizvod u dvije kategorije
 * ranije mogao dobiti cas jednu cas drugu traku. Pravilo, redom:
 *   1. Rank Math primarna kategorija - klijent je izricito rekao koja je glavna.
 *   2. Najdublja kategorija - specificnije podesavanje pobjedjuje opstije.
 *   3. Manji term ID - da ista dubina uvijek da isti rezultat.
 *
 * @param int              $product_id ID proizvoda.
 * @param array<int,mixed> $terms      Termovi sa proizvoda.
 * @return array<int,WP_Term>
 */
function door_expert_highlights_order_terms( $product_id, $terms ) {
	$ordered = array();

	foreach ( $terms as $term ) {
		if ( $term instanceof WP_Term ) {
			$ordered[] = $term;
		}
	}

	if ( count( $ordered ) < 2 ) {
		return $ordered;
	}

	$depths = array();
	foreach ( $ordered as $term ) {
		$depths[ $term->term_id ] = count( get_ancestors( $term->term_id, 'product_cat', 'taxonomy' ) );
	}

	usort(
		$ordered,
		function ( $a, $b ) use ( $depths ) {
			if ( $depths[ $a->term_id ] !== $depths[ $b->term_id ] ) {
				return $depths[ $b->term_id ] - $depths[ $a->term_id ];
			}

			return $a->term_id - $b->term_id;
		}
	);

	$primary = (int) get_post_meta( $product_id, 'rank_math_primary_product_cat', true );

	if ( $primary > 0 ) {
		foreach ( $ordered as $index => $term ) {
			if ( $primary === (int) $term->term_id ) {
				unset( $ordered[ $index ] );
				array_unshift( $ordered, $term );
				break;
			}
		}
	}

	return array_values( $ordered );
}

/**
 * Kontekst koji vazi za dati proizvod.
 *
 * Penjemo se od kategorije proizvoda ka pretcima i uzimamo prvu koja ima svoje
 * redove; ako nijedna nema, vraca se 'default'.
 *
 * Rezultat se pamti po proizvodu: na listingu se zove po kartici, a od verzije sa
 * cipovima na kartici i po dva puta za isti proizvod (kartica + PDP crosssell).
 *
 * @param int $product_id ID proizvoda.
 * @return string
 */
function door_expert_highlights_context( $product_id ) {
	static $cache = array();

	$product_id = (int) $product_id;

	if ( isset( $cache[ $product_id ] ) ) {
		return $cache[ $product_id ];
	}

	$context = 'default';
	$all     = door_expert_highlights_all();
	$terms   = empty( $all ) ? false : get_the_terms( $product_id, 'product_cat' );

	if ( is_array( $terms ) ) {
		$terms = door_expert_highlights_order_terms( $product_id, $terms );

		foreach ( $terms as $term ) {
			$current = $term;

			while ( $current instanceof WP_Term ) {
				$key = (string) $current->term_id;

				if ( ! empty( $all[ $key ] ) ) {
					$context = $key;
					break 2;
				}

				if ( 0 === (int) $current->parent ) {
					break;
				}

				$parent  = get_term( $current->parent, 'product_cat' );
				$current = $parent instanceof WP_Term ? $parent : null;
			}
		}
	}

	$cache[ $product_id ] = $context;

	return $context;
}

/**
 * Gotove stavke za prikaz na PDP-u.
 *
 * Red bez vrijednosti se preskace – prazna pločica je gora od jedne manje.
 *
 * @param WC_Product $product Proizvod.
 * @return array<int,array{label:string,value:string,icon:string}>
 */
function door_expert_product_highlights( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return array();
	}

	$rows = door_expert_highlights_rows( door_expert_highlights_context( $product->get_id() ) );
	if ( empty( $rows ) ) {
		return array();
	}

	$icons = door_expert_highlight_icons();
	$max   = door_expert_highlights_max();
	$items = array();

	foreach ( $rows as $row ) {
		$attr = isset( $row['attr'] ) ? $row['attr'] : '';
		if ( '' === $attr ) {
			continue;
		}

		$value = door_expert_highlights_row_value( $product, $row );
		if ( '' === $value ) {
			continue;
		}

		$icon = isset( $row['icon'] ) ? $row['icon'] : '';

		$items[] = array(
			'label' => door_expert_highlights_row_label( $row ),
			'value' => $value,
			'icon'  => isset( $icons[ $icon ] ) ? $icons[ $icon ]['svg'] : '',
		);

		if ( count( $items ) >= $max ) {
			break;
		}
	}

	return $items;
}

/**
 * Vrijednost jednog reda, sa rezervnim tekstom.
 *
 * Rezervni tekst rjesava dvije stvari odjednom: tvrdnju koja vazi za cijelu
 * kategoriju a ne postoji kao atribut ("Garancija 2 godine"), i proizvod kojem
 * atribut nije popunjen a traka ne bi smjela da ostane krnja.
 *
 * @param WC_Product $product Proizvod.
 * @param array      $row     Red konfiguracije.
 * @return string
 */
function door_expert_highlights_row_value( $product, $row ) {
	$attr  = isset( $row['attr'] ) ? $row['attr'] : '';
	$value = door_expert_highlights_value( $product, $attr );

	if ( '' !== $value ) {
		return $value;
	}

	return isset( $row['fallback'] ) ? (string) $row['fallback'] : '';
}

/**
 * Labela jednog reda: rucno upisana ili naziv izvora.
 *
 * @param array $row Red konfiguracije.
 * @return string
 */
function door_expert_highlights_row_label( $row ) {
	if ( isset( $row['label'] ) && '' !== $row['label'] ) {
		return $row['label'];
	}

	return door_expert_highlights_source_label( isset( $row['attr'] ) ? $row['attr'] : '' );
}

/**
 * Isti izbor atributa, ali kao cipovi za karticu proizvoda.
 *
 * Cip nosi par labela + vrijednost ("Materijal: Puno drvo"), bez ikone: gola
 * vrijednost ("Puno drvo") ne kaze cija je, pa dvije kartice sa razlicitim
 * atributima izgledaju kao da porede istu stvar. Ikona ostaje samo na PDP-u,
 * gdje ima mjesta za traku.
 *
 * Izvor je namjerno ista konfiguracija kao na PDP-u: sta klijent istakne za
 * "Sobna vrata" vidi se i u listingu i na stranici proizvoda, bez drugog ekrana
 * u adminu. Labela je ista koju je upisao u polje "Labela (opciono)", a ako nije
 * upisao nista - naziv atributa.
 *
 * Kontekst se racuna iz kategorija SAMOG proizvoda, ne iz kategorije koja se
 * trenutno gleda, pa ista kartica nosi iste cipove u prodavnici, na kategoriji,
 * u pretrazi i u crosssell-u.
 *
 * Atribut sa vise vrijednosti daje jedan cip sa SVIM vrijednostima
 * ("Sirina: 70 cm, 80 cm, 90 cm"), isto sto pise i na PDP-u. Ranije je isla samo
 * prva, sto je kod proizvoda sa varijacijama tiho krilo ostale sirine.
 *
 * @param WC_Product $product Proizvod.
 * @param int        $limit   Najvise cipova.
 * @return array<int,array{label:string,value:string}>
 */
function door_expert_product_highlight_chips( $product, $limit = DOOR_EXPERT_HIGHLIGHTS_CHIPS ) {
	if ( ! $product instanceof WC_Product ) {
		return array();
	}

	$rows = door_expert_highlights_rows( door_expert_highlights_context( $product->get_id() ) );
	if ( empty( $rows ) ) {
		return array();
	}

	$limit = max( 1, (int) $limit );
	$chips = array();
	$seen  = array();

	foreach ( $rows as $row ) {
		$attr = isset( $row['attr'] ) ? $row['attr'] : '';
		if ( '' === $attr ) {
			continue;
		}

		$value = door_expert_highlights_row_value( $product, $row );

		// Dva izvora mogu dati istu vrijednost (npr. "Bijela"); ne dupliramo cip.
		if ( '' === $value || in_array( $value, $seen, true ) ) {
			continue;
		}

		$seen[]  = $value;
		$chips[] = array(
			'label' => door_expert_highlights_row_label( $row ),
			'value' => $value,
		);

		if ( count( $chips ) >= $limit ) {
			break;
		}
	}

	return $chips;
}

add_filter( 'rank_math/snippet/rich_snippet_product_entity', 'door_expert_highlights_schema' );
/**
 * Dopuni Rank Math Product schemu istaknutim atributima.
 *
 * Kacimo se na postojeci Rank Math entitet umjesto da ispisujemo svoj JSON-LD:
 * dva Product cvora na istoj stranici su za Google konkurentni podaci, a ne dopuna.
 * Ako Rank Math nije aktivan, filter se nikad ne pozove i nema sta da se cisti.
 *
 * @param array $entity Product entitet.
 * @return array
 */
function door_expert_highlights_schema( $entity ) {
	if ( ! is_array( $entity ) || ! function_exists( 'is_product' ) || ! is_product() ) {
		return $entity;
	}

	$product = wc_get_product( get_the_ID() );
	$items   = door_expert_product_highlights( $product );

	if ( empty( $items ) ) {
		return $entity;
	}

	$props = array();

	foreach ( $items as $item ) {
		$props[] = array(
			'@type' => 'PropertyValue',
			'name'  => $item['label'],
			'value' => $item['value'],
		);
	}

	$entity['additionalProperty'] = isset( $entity['additionalProperty'] ) && is_array( $entity['additionalProperty'] )
		? array_merge( $entity['additionalProperty'], $props )
		: $props;

	return $entity;
}
