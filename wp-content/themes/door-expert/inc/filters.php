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
 *   7. Živi faceting: brojevi u sidebaru prate tekući izbor, opcije sa nula
 *      rezultata se sive i onemogućavaju (plugin ima računicu, ali je ne poziva sam).
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
 * <label class="wcfc-option"> => name="pa_boja[]", checked iz URL-a i facet broj.
 *
 * Radi nad cijelom labelom, ne samo nad <input>-om, jer broj živi u susjednom
 * <small class="wcfc-count">. Plugin opcije pravi iz jednog fiksnog sprintf-a
 * (render.php, wcfc_render_term_filter), pa je markup labele predvidljiv koliko
 * i markup inputa.
 *
 * Drugi pass (samo <input>) je sigurnosna mreža: ako plugin promijeni markup labele,
 * prvi regex ne uhvati ništa – izgube se facet brojevi, ali NE i "[]" i checked,
 * od kojih zavisi multi-select. Već obrađene inpute preskače jer name="…[]" ne
 * prolazi njegov regex.
 *
 * @param string $html Markup.
 * @return string
 */
function door_expert_filter_fix_checkboxes( $html ) {
	$result = preg_replace_callback(
		'/<label class="wcfc-option"><input type="checkbox" name="([a-z0-9_\-]+)" value="([^"]*)"\s*\/?><span>(.*?)<\/span><small class="wcfc-count">\((\d+)\)<\/small><\/label>/i',
		'door_expert_filter_option_tag',
		$html
	);

	if ( null === $result ) {
		$result = $html;
	}

	$fallback = preg_replace_callback(
		'/<input type="checkbox" name="([a-z0-9_\-]+)" value="([^"]*)"\s*\/?>/i',
		'door_expert_filter_checkbox_tag',
		$result
	);

	return null === $fallback ? $result : $fallback;
}

/**
 * Jedna opcija sa facet brojem.
 *
 * Nula rezultata se SIVI, ne sakriva: lista koja se prekraja pod kursorom je gora
 * od onemogućene opcije. Već štiklirana opcija se nikad ne onemogućava, inače je
 * kupac ne bi mogao odštiklirati.
 *
 * @param array $matches [1] taksonomija, [2] slug, [3] labela (plugin je escape-ovao), [4] plugin-ov broj.
 * @return string
 */
function door_expert_filter_option_tag( $matches ) {
	$taxonomy = $matches[1];
	$slug     = wp_specialchars_decode( $matches[2], ENT_QUOTES );
	$label    = wp_specialchars_decode( $matches[3], ENT_QUOTES );
	$checked  = in_array( $slug, door_expert_shop_selected( $taxonomy ), true );
	$count    = door_expert_filter_facet_count( $taxonomy, $slug, (int) $matches[4] );
	$dead     = ( 0 === $count && ! $checked );

	return sprintf(
		'<label class="wcfc-option%1$s"><input type="checkbox" name="%2$s[]" value="%3$s"%4$s%5$s /><span>%6$s</span><small class="wcfc-count">(%7$d)</small></label>',
		$dead ? ' is-disabled' : '',
		esc_attr( $taxonomy ),
		esc_attr( $slug ),
		$checked ? ' checked="checked"' : '',
		$dead ? ' disabled="disabled"' : '',
		esc_html( $label ),
		$count
	);
}

/**
 * Sigurnosna mreža za inpute koje door_expert_filter_option_tag() nije uhvatio.
 *
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

	$style   = door_expert_filter_tag_attr( $tag, 'style' );
	$title   = door_expert_filter_tag_attr( $tag, 'title' );
	$checked = in_array( $slug, door_expert_shop_selected( $taxonomy ), true );
	$count   = door_expert_filter_facet_count( $taxonomy, $slug, (int) door_expert_filter_tag_attr( $tag, 'data-count' ) );
	$dead    = ( 0 === $count && ! $checked );

	return sprintf(
		'<label class="%1$s%2$s%3$s" style="%4$s" title="%5$s" data-count="%6$d">' .
			'<input type="checkbox" name="%7$s[]" value="%8$s"%9$s%10$s />' .
			'<span class="wcfc-sr">%11$s</span>' .
		'</label>',
		esc_attr( $classes ),
		$checked ? ' is-active' : '',
		$dead ? ' is-disabled' : '',
		esc_attr( $style ),
		esc_attr( $title ),
		$count,
		esc_attr( $taxonomy ),
		esc_attr( $slug ),
		$checked ? ' checked="checked"' : '',
		$dead ? ' disabled="disabled"' : '',
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

/* ── 7. Živi faceting ─────────────────────────────────────────────────
 * Plugin renderuje brojeve za NEFILTRIRANU kategoriju i oni stoje dok kupac sužava
 * izbor: štiklira "Hrast", i dalje vidi "Bijela (7)", štiklira i nju i dobije prazan
 * grid. Ovdje se brojevi preračunavaju za tekući izbor (računica je u pluginu,
 * wcfc_compute_facets), a sekcija 5 ih upisuje u markup i sivi nule.
 *
 * Ne zavisi od AJAX-a: forma ide GET-om, pa se na svakom reload-u računa iznova.
 *
 * Poznata ograničenja (nisu bugovi):
 *   - Grupa "Kategorija" i cijena nemaju facete (plugin ih preskače, wcfc_special_attrs).
 *   - Dostupnost (f_stock) nije taksonomija i plugin je ne poznaje: sa aktivnim
 *     filterom dostupnosti brojevi mogu biti veći od broja prikazanih proizvoda.
 *   - Prodavnica bez hero pilule ili sa više njih ("Vrata" = sobna + sigurnosna)
 *     nema jedan opseg nad kojim bi se brojalo, pa ostaju plugin-ovi brojevi.
 */

/**
 * Kategorija čiji proizvodi su osnova za brojanje.
 *
 * Na kategorijskoj arhivi to je tekući term. Na prodavnici samo kad je hero pilulom
 * izabrana tačno jedna kategorija; za nula ili više njih nema jednog opsega.
 *
 * @return WP_Term|null
 */
function door_expert_filter_facet_term() {
	if ( is_tax( 'product_cat' ) ) {
		$term = get_queried_object();

		return $term instanceof WP_Term ? $term : null;
	}

	$cats = door_expert_shop_selected( 'f_cat' );
	if ( 1 !== count( $cats ) ) {
		return null;
	}

	$term = get_term_by( 'slug', $cats[0], 'product_cat' );

	return $term instanceof WP_Term ? $term : null;
}

/**
 * Facet brojevi za tekući izbor: [ taksonomija => [ slug => broj ] ].
 *
 * Self-exclusion radi plugin: svaka grupa se broji nad proizvodima koji zadovoljavaju
 * sve DRUGE aktivne filtere, ali ne i nju samu. Bez toga bi prvi štiklirani term
 * ponulio sve ostale u istoj grupi i multi-select bi prestao da radi.
 *
 * Broji se za grupe koje sidebar STVARNO renderuje (isti kontekst kao
 * wcfc_render_sidebar), ne za grupe konteksta izabrane kategorije. Razlika je bitna
 * na prodavnici: sidebar je tamo u 'default' kontekstu i prikazuje npr. "Dimenzije
 * vrata"; sa pilulom "Keramika" te dimenzije moraju da padnu na (0) i posive se,
 * a ne da zadrže globalni broj i odvedu kupca u prazan grid.
 *
 * Prazan niz znači "nema facetinga" – pozivalac tada zadržava plugin-ov broj.
 *
 * @return array<string,array<string,int>>
 */
function door_expert_filter_facets() {
	static $facets = null;

	if ( null !== $facets ) {
		return $facets;
	}

	$facets = array();

	if ( ! function_exists( 'wcfc_compute_facets' ) || ! function_exists( 'wcfc_attrs_for_context' ) || ! function_exists( 'wcfc_current_context' ) ) {
		return $facets;
	}

	$term = door_expert_filter_facet_term();
	if ( ! $term instanceof WP_Term ) {
		return $facets;
	}

	$attrs = wcfc_attrs_for_context( wcfc_current_context() );
	if ( empty( $attrs ) ) {
		return $facets;
	}

	$selected = array();
	foreach ( door_expert_filter_taxonomies() as $taxonomy ) {
		$terms = door_expert_shop_selected( $taxonomy );
		if ( ! empty( $terms ) ) {
			$selected[ $taxonomy ] = $terms;
		}
	}

	// door_expert_shop_price() vraća 0.0 kad granica nije postavljena, a plugin
	// razlikuje "nije postavljeno" (null) od nule. Isto tumačenje kao upit u inc/shop.php.
	$min = door_expert_shop_price( 'min_price' );
	$max = door_expert_shop_price( 'max_price' );

	$facets = wcfc_compute_facets(
		(int) $term->term_id,
		$selected,
		$min > 0 ? $min : null,
		$max > 0 ? $max : null,
		$attrs
	);

	return $facets;
}

/**
 * Broj za jednu opciju: facet ako postoji za tu grupu, inače plugin-ov.
 *
 * Grupa koja jeste u facetima, a term u njoj nije, ima 0 (nijedan proizvod iz
 * tekućeg izbora ga nema) – to NIJE isto što i grupa bez faceta.
 *
 * @param string $taxonomy Taksonomija.
 * @param string $slug     Slug terma.
 * @param int    $fallback Broj koji je plugin izrenderovao.
 * @return int
 */
function door_expert_filter_facet_count( $taxonomy, $slug, $fallback ) {
	$facets = door_expert_filter_facets();

	if ( ! isset( $facets[ $taxonomy ] ) ) {
		return (int) $fallback;
	}

	return isset( $facets[ $taxonomy ][ $slug ] ) ? (int) $facets[ $taxonomy ][ $slug ] : 0;
}
