<?php
/**
 * Filteri – most između teme i plugina "WC Filter Configurator" (wp-plugins/).
 *
 * PODJELA POSLA (plugin sam to zove "the boundary"):
 *   Plugin vlasti SIDEBAR: koji filteri idu na koju kategoriju, kojim redom, sa kojom
 *   labelom, koji su termovi stvarno prisutni u toj kategoriji i koliki su brojevi.
 *   Sve to se podešava iz admina (Settings → Filter Configurator), bez diranja koda.
 *
 *   Tema vlasti UPIT: inc/shop.php prevodi GET parametre u WP_Query preko
 *   woocommerce_product_query, plus sort, paginaciju i hidden inpute. Plugin u to ne dira.
 *
 * ŠTA OVAJ FAJL RADI (plugin namjerno ne isporučuje ništa od ovoga):
 *   0. Whitelistu taksonomija koje upit smije da primi – iz WooCommerce registra,
 *      NE iz fiksne liste, da konfigurator ne može da ponudi filter koji upit ignoriše.
 *   1. Početnu konfiguraciju filtera za Door Expert (wcfc_default_configs), plus
 *      zakrpu za slučaj kad admin snimi jedan kontekst pa 'default' nestane.
 *   2. Paletu za swatch boje (wcfc_swatch_color_map).
 *   3. Sakrivanje grupe po stranici (grupa "Kategorija" na kategorijskoj arhivi).
 *   4. Cjenovnu grupu u našem markup-u, sa name atributima (wcfc_pre_render_filter) –
 *      plugin renderuje slider bez name-a, pa se bez ovoga cijena nikad ne pošalje.
 *   5. Doradu markup-a (wcfc_sidebar_html): plugin ispisuje name="pa_boja" bez "[]" i
 *      bez checked stanja, pa bi se multi-select gubio na submit, a sidebar bi zaboravio
 *      izbor poslije reload-a. Swatch je <button>, a dugme ne šalje vrijednost formom.
 *   6. Grupu "Dostupnost" – stock nije taksonomija, pa je plugin ne poznaje.
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Da li je plugin aktivan. Tema mora da radi i bez njega (sidebar se degradira,
 * grid i sort ostaju), pa se svaki poziv ka plugin funkcijama ovim štiti.
 *
 * @return bool
 */
function door_expert_filters_plugin_active() {
	return function_exists( 'wcfc_render_sidebar' );
}

/**
 * Taksonomije koje filter upit prihvata kao GET parametar.
 *
 * Dinamički iz WooCommerce registra atributa + brend taksonomija. Fiksna lista bi
 * značila da konfigurator može da prikaže filter koji upit tiho ignoriše – najgori
 * mogući ishod, jer sidebar izgleda ispravno a rezultati su nefiltrirani.
 *
 * @return string[] Nazivi taksonomija (npr. product_brand, pa_boja, pa_dimenzije-vrata).
 */
function door_expert_filter_taxonomies() {
	static $cache = null;

	// WC još nije učitan – ne keširaj prazan rezultat.
	if ( ! function_exists( 'wc_get_attribute_taxonomies' ) ) {
		return array();
	}

	if ( null !== $cache ) {
		return $cache;
	}

	$taxonomies = array();

	$brand = function_exists( 'wcfc_brand_taxonomy' ) ? wcfc_brand_taxonomy() : 'product_brand';
	if ( taxonomy_exists( $brand ) ) {
		$taxonomies[] = $brand;
	}

	foreach ( wc_get_attribute_taxonomies() as $attribute ) {
		$name = wc_attribute_taxonomy_name( $attribute->attribute_name );
		if ( taxonomy_exists( $name ) ) {
			$taxonomies[] = $name;
		}
	}

	$cache = array_values( array_unique( $taxonomies ) );

	return $cache;
}

/* ── 1. Početna konfiguracija ─────────────────────────────────────────
 * Fallback dok se u adminu ništa ne sačuva. Čim admin snimi bilo šta,
 * opcija wcfc_filter_configs pobjeđuje i ovo se više ne koristi za taj kontekst.
 * Smisao: svjež environment (staging, reinstall) odmah ima smislene filtere.
 */

add_filter( 'wcfc_default_configs', 'door_expert_default_filter_configs' );
/**
 * @param array $configs Plugin defaulti.
 * @return array
 */
function door_expert_default_filter_configs( $configs ) {
	// 'global' ostaje prazan namjerno: globalni filteri se dodaju NA VRH svakog
	// konteksta, pa bi cijena preskočila ispred brenda. Redosljed držimo u 'default'.
	$configs['global'] = array();

	$configs['default'] = array(
		array(
			'attr'       => 'product_brand',
			'label'      => 'Brend',
			'type'       => 'checkbox',
			'collapsed'  => false,
			'categories' => array(),
		),
		array(
			'attr'       => 'pa_boja',
			'label'      => 'Boja',
			'type'       => 'swatch',
			'collapsed'  => true,
			'categories' => array(),
		),
		array(
			'attr'       => 'price_slider',
			'label'      => 'Cijena (EUR)',
			'type'       => 'price_slider',
			'collapsed'  => true,
			'categories' => array(),
		),
		array(
			'attr'       => 'pa_dimenzije-vrata',
			'label'      => 'Dimenzije vrata',
			'type'       => 'checkbox',
			'collapsed'  => true,
			'categories' => array( 'sobna-vrata', 'sigurnosna-vrata' ),
		),
		array(
			'attr'       => 'pa_dimenzije-plocica',
			'label'      => 'Dimenzije pločica',
			'type'       => 'checkbox',
			'collapsed'  => true,
			'categories' => array( 'keramicke-plocice' ),
		),
	);

	return $configs;
}

add_filter( 'option_wcfc_filter_configs', 'door_expert_filter_configs_fallback' );
/**
 * Zamka u plugin logici: wcfc_get_configs() koristi wcfc_default_configs() SAMO dok je
 * opcija potpuno prazna. Čim admin snimi jedan jedini kontekst (npr. "Sobna vrata"),
 * opcija prestaje biti prazna, ključ 'default' u njoj ne postoji, i svaka kategorija
 * bez sopstvene konfiguracije ostane bez ijednog filtera.
 *
 * Zato popunjavamo 'default' kad ga NEMA. Ako ga admin ima ali je prazan, to je
 * namjerna odluka ("ovdje bez filtera") i ne diramo je.
 *
 * @param mixed $configs Sadržaj opcije wcfc_filter_configs.
 * @return mixed
 */
function door_expert_filter_configs_fallback( $configs ) {
	if ( ! is_array( $configs ) || empty( $configs ) ) {
		// Plugin će sam pasti na wcfc_default_configs (naš filter iznad).
		return $configs;
	}

	if ( ! array_key_exists( 'default', $configs ) ) {
		$defaults           = door_expert_default_filter_configs( array() );
		$configs['default'] = $defaults['default'];
	}

	return $configs;
}

/* ── 2. Swatch paleta ─────────────────────────────────────────────────
 * Bez mape plugin swatch grupu degradira u obične checkboxove (ne crta prazne
 * krugove), pa je ovo čisto kozmetika – ali za boju je kozmetika poenta.
 */

add_filter( 'wcfc_swatch_color_map', 'door_expert_filter_swatch_colors', 10, 2 );
/**
 * @param array  $map      Slug => hex.
 * @param string $taxonomy Taksonomija za koju se traži paleta.
 * @return array
 */
function door_expert_filter_swatch_colors( $map, $taxonomy ) {
	if ( 'pa_boja' !== $taxonomy ) {
		return $map;
	}

	return array(
		'bijela'   => '#FFFFFF',
		'krema'    => '#F5E6C8',
		'orah'     => '#8B5E3C',
		'wenge'    => '#3D2B1F',
		'hrast'    => '#D4C5A9',
		'antracit' => '#2C2C2C',
		'beige'    => '#C9B896',
		'plava'    => '#6B8E9B',
	);
}

add_filter( 'wcfc_swatch_light_slugs', 'door_expert_filter_light_swatches', 10, 2 );
/**
 * Svijetle boje dobijaju obrub, inače je bijeli krug nevidljiv na bijelom sidebaru.
 *
 * @param array  $slugs    Postojeći slugovi.
 * @param string $taxonomy Taksonomija.
 * @return array
 */
function door_expert_filter_light_swatches( $slugs, $taxonomy ) {
	if ( 'pa_boja' !== $taxonomy ) {
		return $slugs;
	}

	return array( 'bijela', 'krema', 'hrast', 'beige' );
}

/* ── 3. Sakrivanje grupa po stranici ──────────────────────────────────
 * Konfiguracija je globalna, ali "Kategorija" na kategorijskoj arhivi nema
 * smisla (već smo unutar nje). Šablon to javi prije rendera.
 */

/**
 * Grupe koje se na tekućoj stranici ne renderuju, bez obzira na konfiguraciju.
 *
 * Koristi se za "Kategorija" na kategorijskoj arhivi, gdje je filter po kategoriji
 * redundantan (već smo unutar nje). Getter/setter u jednom: poziv sa nizom postavlja,
 * poziv bez argumenta čita.
 *
 * @param string[]|null $set Nazivi atributa koje treba sakriti, ili null za čitanje.
 * @return string[]
 */
function door_expert_filter_hidden_groups( $set = null ) {
	static $hidden = array();

	if ( null !== $set ) {
		$hidden = array_values( array_filter( (array) $set ) );
	}

	return $hidden;
}

add_filter( 'wcfc_pre_render_filter', 'door_expert_filter_hide_group', 9, 2 );
/**
 * Prazan string (ne null) znači "renderuj ništa" i zaustavlja plugin renderer.
 *
 * @param string|null $pre    Null = pusti plugin da renderuje.
 * @param array       $filter Definicija filtera.
 * @return string|null
 */
function door_expert_filter_hide_group( $pre, $filter ) {
	$attr = isset( $filter['attr'] ) ? $filter['attr'] : '';

	if ( '' !== $attr && in_array( $attr, door_expert_filter_hidden_groups(), true ) ) {
		return '';
	}

	return $pre;
}

/* ── 4. Cjenovna grupa ────────────────────────────────────────────────
 * Plugin renderuje dva <input type="range"> BEZ name atributa, pa se cijena
 * nikad ne pošalje formom. Umjesto krpljenja regexom, preuzimamo cijelu grupu:
 * jedino što nam od plugina treba je gornja granica (wcfc_max_price).
 */

add_filter( 'wcfc_pre_render_filter', 'door_expert_filter_price_group', 10, 2 );
/**
 * @param string|null $pre    Null = pusti plugin da renderuje.
 * @param array       $filter Definicija filtera iz konfiguracije.
 * @return string|null
 */
function door_expert_filter_price_group( $pre, $filter ) {
	$attr = isset( $filter['attr'] ) ? $filter['attr'] : '';

	if ( 'price_slider' !== $attr ) {
		return $pre;
	}

	$ceiling = function_exists( 'wcfc_max_price' ) ? (int) wcfc_max_price() : 1000;
	if ( $ceiling < 1 ) {
		$ceiling = 1000;
	}

	$step = max( 1, (int) round( $ceiling / 100 ) );

	$min = (int) door_expert_shop_price( 'min_price' );
	$max = (int) door_expert_shop_price( 'max_price' );

	if ( $min < 0 || $min > $ceiling ) {
		$min = 0;
	}
	if ( $max < 1 || $max > $ceiling ) {
		$max = $ceiling;
	}
	if ( $min > $max ) {
		$min = 0;
	}

	$classes = 'wcfc-group wcfc-group--price';
	if ( ! empty( $filter['collapsed'] ) ) {
		$classes .= ' is-collapsed';
	}

	$label = isset( $filter['label'] ) ? $filter['label'] : 'Cijena (EUR)';

	// data-de-default: JS po njima zna da li je korisnik stvarno pomjerio slider.
	// Ako nije, inputi se na submit disable-uju da ne zagade URL punim rasponom.
	return sprintf(
		'<div class="%1$s" data-attr="price_slider">' .
			'<h3 class="wcfc-group__title">%2$s<span class="wcfc-arrow" aria-hidden="true"></span></h3>' .
			'<div class="wcfc-group__options">' .
				'<div class="wcfc-price" data-de-ceiling="%3$d">' .
					'<div class="wcfc-price__track">' .
						'<div class="wcfc-price__fill" id="wcfc-price-fill"></div>' .
						'<input type="range" id="wcfc-price-min" class="wcfc-price__input wcfc-price__input--min" name="min_price" min="0" max="%3$d" value="%4$d" step="%5$d" data-de-default="0" aria-label="Najniža cijena" />' .
						'<input type="range" id="wcfc-price-max" class="wcfc-price__input wcfc-price__input--max" name="max_price" min="0" max="%3$d" value="%6$d" step="%5$d" data-de-default="%3$d" aria-label="Najviša cijena" />' .
					'</div>' .
					'<div class="wcfc-price__labels"><span id="wcfc-price-min-label">%7$s</span><span id="wcfc-price-max-label">%8$s</span></div>' .
				'</div>' .
			'</div>' .
		'</div>',
		esc_attr( $classes ),
		esc_html( $label ),
		$ceiling,
		$min,
		$step,
		$max,
		esc_html( number_format_i18n( $min ) ),
		esc_html( number_format_i18n( $max ) )
	);
}

/* ── 5. Dorada markup-a ───────────────────────────────────────────────
 * Dvije rupe u plugin markupu, obje tihe:
 *   a) name="pa_boja" bez "[]" – više čekiranih polja se pri GET submitu sabije
 *      na posljednje, pa multi-select ne radi.
 *   b) nema checked – poslije submita sidebar zaboravi šta je izabrano.
 * Swatch je dodatno <button>, a dugme ne šalje vrijednost formom; pretvaramo ga
 * u <label> + skriveni checkbox da radi i bez JavaScripta.
 */

add_filter( 'wcfc_sidebar_html', 'door_expert_filter_sidebar_markup' );
/**
 * @param string $html Sirovi markup sidebara iz plugina.
 * @return string
 */
function door_expert_filter_sidebar_markup( $html ) {
	$html = door_expert_filter_fix_checkboxes( $html );
	$html = door_expert_filter_fix_swatches( $html );

	return $html;
}

/**
 * Vrijednost jednog atributa iz HTML taga, dekodirana nazad u sirov oblik
 * (plugin je već escape-ovao, pa bismo bez dekodiranja duplo escape-ovali).
 *
 * @param string $tag  Cijeli tag.
 * @param string $name Naziv atributa.
 * @return string
 */
function door_expert_filter_tag_attr( $tag, $name ) {
	if ( preg_match( '/\b' . preg_quote( $name, '/' ) . '="([^"]*)"/i', $tag, $matches ) ) {
		return wp_specialchars_decode( $matches[1], ENT_QUOTES );
	}

	return '';
}

/**
 * name="pa_boja" => name="pa_boja[]" + checked iz URL-a.
 *
 * @param string $html Markup.
 * @return string
 */
function door_expert_filter_fix_checkboxes( $html ) {
	$result = preg_replace_callback(
		'/<input type="checkbox" name="([a-z0-9_\-]+)" value="([^"]*)"\s*\/?>/i',
		'door_expert_filter_checkbox_tag',
		$html
	);

	return null === $result ? $html : $result;
}

/**
 * @param array $matches Rezultat regexa: [1] taksonomija, [2] slug terma.
 * @return string
 */
function door_expert_filter_checkbox_tag( $matches ) {
	$taxonomy = $matches[1];
	$slug     = wp_specialchars_decode( $matches[2], ENT_QUOTES );
	$selected = door_expert_shop_selected( $taxonomy );

	return sprintf(
		'<input type="checkbox" name="%1$s[]" value="%2$s"%3$s />',
		esc_attr( $taxonomy ),
		esc_attr( $slug ),
		in_array( $slug, $selected, true ) ? ' checked="checked"' : ''
	);
}

/**
 * <button class="wcfc-swatch"> => <label> + checkbox (radi bez JS-a).
 *
 * @param string $html Markup.
 * @return string
 */
function door_expert_filter_fix_swatches( $html ) {
	$result = preg_replace_callback(
		'/<button\b[^>]*\bclass="(wcfc-swatch[^"]*)"[^>]*><\/button>/i',
		'door_expert_filter_swatch_tag',
		$html
	);

	return null === $result ? $html : $result;
}

/**
 * @param array $matches Rezultat regexa: [0] cijeli tag, [1] klase.
 * @return string
 */
function door_expert_filter_swatch_tag( $matches ) {
	$tag      = $matches[0];
	$classes  = $matches[1];
	$taxonomy = door_expert_filter_tag_attr( $tag, 'data-attr' );
	$slug     = door_expert_filter_tag_attr( $tag, 'data-value' );

	if ( '' === $taxonomy || '' === $slug ) {
		return '';
	}

	$count   = (int) door_expert_filter_tag_attr( $tag, 'data-count' );
	$style   = door_expert_filter_tag_attr( $tag, 'style' );
	$title   = door_expert_filter_tag_attr( $tag, 'title' );
	$checked = in_array( $slug, door_expert_shop_selected( $taxonomy ), true );

	return sprintf(
		'<label class="%1$s%2$s" style="%3$s" title="%4$s" data-count="%5$d">' .
			'<input type="checkbox" name="%6$s[]" value="%7$s"%8$s />' .
			'<span class="wcfc-sr">%9$s</span>' .
		'</label>',
		esc_attr( $classes ),
		$checked ? ' is-active' : '',
		esc_attr( $style ),
		esc_attr( $title ),
		$count,
		esc_attr( $taxonomy ),
		esc_attr( $slug ),
		$checked ? ' checked="checked"' : '',
		esc_html( $title )
	);
}

/* ── 6. Dostupnost ────────────────────────────────────────────────────
 * Stock nije taksonomija, pa ga konfigurator ne poznaje (specijalni su mu samo
 * price_slider i product_cat). Renderujemo ga sami, u istim wcfc- klasama da
 * vizuelno ne odskače. Posljedica: redosljed mu je fiksan (uvijek na dnu).
 */

/**
 * Markup grupe "Dostupnost".
 *
 * @return string
 */
function door_expert_filter_stock_group() {
	$selected = door_expert_shop_selected( 'f_stock' );

	$options = array(
		'na-stanju'   => 'Na stanju',
		'po-narudzbi' => 'Po narudžbi',
	);

	$rows = '';
	foreach ( $options as $value => $label ) {
		$rows .= sprintf(
			'<label class="wcfc-option"><input type="checkbox" name="f_stock[]" value="%1$s"%2$s /><span>%3$s</span></label>',
			esc_attr( $value ),
			in_array( $value, $selected, true ) ? ' checked="checked"' : '',
			esc_html( $label )
		);
	}

	return sprintf(
		'<div class="wcfc-group wcfc-group--checkbox%1$s" data-attr="f_stock">' .
			'<h3 class="wcfc-group__title">Dostupnost<span class="wcfc-arrow" aria-hidden="true"></span></h3>' .
			'<div class="wcfc-group__options">%2$s</div>' .
		'</div>',
		empty( $selected ) ? ' is-collapsed' : '',
		$rows
	);
}
