<?php
/**
 * Istaknuti atributi na PDP-u – podatkovni sloj.
 *
 * Nekoliko najvažnijih osobina proizvoda, kao ikonica + vrijednost, iznad CTA-a
 * na desktopu i ispod njega na telefonu.
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
	$saved = get_option( DOOR_EXPERT_HIGHLIGHTS_OPTION, array() );

	return is_array( $saved ) ? $saved : array();
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
 * Očisti redove prije upisa u bazu.
 *
 * @param mixed $rows Sirovi $_POST podaci.
 * @return array
 */
function door_expert_highlights_sanitize_rows( $rows ) {
	if ( ! is_array( $rows ) ) {
		return array();
	}

	$icons = door_expert_highlight_icons();
	$clean = array();

	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$attr = isset( $row['attr'] ) ? sanitize_key( $row['attr'] ) : '';
		if ( '' === $attr ) {
			continue; // Prazan red = obrisan red.
		}

		$icon = isset( $row['icon'] ) ? sanitize_key( $row['icon'] ) : '';
		if ( ! isset( $icons[ $icon ] ) ) {
			$icon = '';
		}

		$clean[] = array(
			'attr'  => $attr,
			'icon'  => $icon,
			'label' => isset( $row['label'] ) ? sanitize_text_field( $row['label'] ) : '',
		);

		if ( count( $clean ) >= DOOR_EXPERT_HIGHLIGHTS_MAX ) {
			break;
		}
	}

	return $clean;
}

/**
 * Kontekst koji vazi za dati proizvod.
 *
 * Penjemo se od kategorije proizvoda ka pretcima i uzimamo prvu koja ima svoje
 * redove; ako nijedna nema, vraca se 'default'.
 *
 * @param int $product_id ID proizvoda.
 * @return string
 */
function door_expert_highlights_context( $product_id ) {
	$all = door_expert_highlights_all();
	if ( empty( $all ) ) {
		return 'default';
	}

	$terms = get_the_terms( $product_id, 'product_cat' );
	if ( ! is_array( $terms ) ) {
		return 'default';
	}

	foreach ( $terms as $term ) {
		$current = $term;

		while ( $current instanceof WP_Term ) {
			$key = (string) $current->term_id;

			if ( ! empty( $all[ $key ] ) ) {
				return $key;
			}

			if ( 0 === (int) $current->parent ) {
				break;
			}

			$parent  = get_term( $current->parent, 'product_cat' );
			$current = $parent instanceof WP_Term ? $parent : null;
		}
	}

	return 'default';
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
	$items = array();

	foreach ( $rows as $row ) {
		$attr = isset( $row['attr'] ) ? $row['attr'] : '';
		if ( '' === $attr ) {
			continue;
		}

		$value = $product->get_attribute( $attr );
		if ( '' === $value ) {
			continue;
		}

		$icon  = isset( $row['icon'] ) ? $row['icon'] : '';
		$label = isset( $row['label'] ) && '' !== $row['label'] ? $row['label'] : wc_attribute_label( $attr );

		$items[] = array(
			'label' => $label,
			'value' => $value,
			'icon'  => isset( $icons[ $icon ] ) ? $icons[ $icon ]['svg'] : '',
		);

		if ( count( $items ) >= DOOR_EXPERT_HIGHLIGHTS_MAX ) {
			break;
		}
	}

	return $items;
}
