<?php
/**
 * Sadržaj kategorijskih stranica – ODVOJEN od prikaza.
 *
 * FAZA A (sad): hardkodovan array po slug-u (verno prototipu, za prezentaciju klijentu).
 * MIGRACIJA (produkcija): unutrašnjost door_expert_cat_content() se prepiše da čita
 * get_term_meta() (JetEngine Meta Box na product_cat). Render parts i router se NE diraju.
 *
 * Struktura koju vraća (sve opciono – render parts hendlaju prazno):
 *   hero      => [ label, title, desc, img, img_badge_strong, img_badge_text, badges[] ]
 *   benefits  => [ eyebrow, title, items[ [icon(svg), title, text] ] ]
 *   faq       => [ eyebrow, title, items[ [q, a] ] ]
 *   crosssell => [ title, items[ [url, img, alt, title, desc] ] ]
 *   cta       => [ title, desc, phone ]
 *   body_part => slug bespoke srednjeg dela (template-parts/category/body/{slug}.php)
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Vrati sadržaj kategorije po slug-u.
 *
 * @param string $slug product_cat slug.
 * @return array Prazan array ako nema definisanog sadržaja (parts hendlaju prazno).
 */
function door_expert_cat_content( $slug ) {
	static $data = null;

	if ( null === $data ) {
		$data = door_expert_cat_content_all();
	}

	return isset( $data[ $slug ] ) ? $data[ $slug ] : array();
}

/**
 * Sav hardkodovan sadržaj (Faza A). Migracija zamenjuje ovu funkciju čitanjem meta-a.
 *
 * @return array<string,array>
 */
function door_expert_cat_content_all() {
	$tel = 'tel:+38269234888';

	return array(

		// ── POTKATEGORIJA: Podne pločice ─────────────────────────
		'podne' => array(
			'hero' => array(
				'label'            => 'Španske keramičke pločice · Direktan uvoz',
				'title'            => 'Podne pločice<br>za svaki prostor',
				'desc'             => 'Granitna keramika, veliki formati i anti-slip klase R10/R11 – direktno iz Španije, odmah dostupno u Podgorici. Tau Ceramica, Arcana i New Tiles za dnevne sobe, kupatila, terase i hodnike.',
				'img'              => 'https://images.unsplash.com/photo-1615971677499-5467cbab01c0?w=800&q=85',
				'img_alt'          => 'Španske podne pločice – veliki format, mat finish',
				'img_badge_strong' => '18 – 65 €',
				'img_badge_text'   => 'po m² · Na stanju',
				'badges'           => array(
					array( 'text' => 'Na stanju odmah', 'dot' => true ),
					array( 'text' => '🇪🇸 Direktan uvoz iz Španije' ),
					array( 'text' => 'Od 18 €/m²', 'accent' => true ),
					array( 'text' => 'R10 / R11 anti-slip' ),
				),
			),
			'benefits' => array(
				'eyebrow' => 'Zašto Door Expert',
				'title'   => 'Španska keramika – odmah u Podgorici',
				'items'   => array(
					array(
						'icon'  => '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>',
						'title' => 'Na stanju odmah',
						'text'  => 'Dok konkurencija naručuje sa rokom od 45 dana, mi isporučujemo odmah. Sve kolekcije su fizički na stanju u Podgorici.',
					),
					array(
						'icon'  => '<circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/>',
						'title' => 'Direktan uvoz iz Španije',
						'text'  => 'Uvozimo direktno od Tau Ceramica, Arcana Ceramica i New Tiles – bez posrednika, što znači bolju cijenu i garantovanu originalnost.',
					),
					array(
						'icon'  => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
						'title' => 'Certifikovane anti-slip klase',
						'text'  => 'Sve podne pločice dolaze sa jasno navedenom R-klasom protivkliznosti. R10 za standardne prostore, R11 za mokre zone i terase.',
					),
					array(
						'icon'  => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>',
						'title' => 'Koordinirane kolekcije',
						'text'  => 'Svaka podna kolekcija dolazi sa koordiniranom zidnom varijantom – isti dizajn, različita tekstura. Jedinstven izgled bez kompromisa.',
					),
				),
			),
			'body_part' => 'podne-plocice',
			'faq' => array(
				'eyebrow' => 'Česta pitanja',
				'title'   => 'Sve što trebate znati o podnim pločicama',
				'items'   => array(
					array( 'q' => 'Da li je polaganje pločica uključeno u cijenu?', 'a' => 'Ne – polaganje vrše nezavisni majstori koji naplaćuju direktno klijentu. Okvirna cijena polaganja podnih pločica je 8–15 €/m² zavisno od formata i složenosti prostora. Možemo preporučiti provjerene majstore u Podgorici.' ),
					array( 'q' => 'Koliko pločica trebam naručiti?', 'a' => 'Izmjerite površinu prostorije i dodajte 10–15% za škart (rezanje, lomljenje, greške pri polaganju). Za dijagonalno polaganje dodajte 15–20%. Uvijek je bolje imati višak – ako vam zatreba naknadno, ista serija možda neće biti dostupna. Naš tim vam može pomoći sa izračunom ako donesete dimenzije.' ),
					array( 'q' => 'Koja anti-slip klasa je obavezna za kupatilo?', 'a' => 'Za kupatilski pod preporučujemo minimum R10 klasu (DIN 51130 standard). Za tuš kabine bez postolja, mokre zone i terase preporučujemo R11. R9 je dovoljna samo za suhe prostore kao što su dnevna soba i spavaća soba.' ),
					array( 'q' => 'Koji format pločice je pravi za moj prostor?', 'a' => 'Veći formati (60×60, 60×120) vizuelno povećavaju prostor i imaju manje fuga – idealni za dnevne sobe i veće hodnike. Manji formati (30×30, 30×60) bolje prate nagib prema slivniku u kupatilu i lakše se polažu u nepravilnim prostorima. Drvo imitacije (20×120, 15×90) daju topli izgled bez skupog održavanja drveta.' ),
					array( 'q' => 'Mogu li vidjeti pločice uživo u salonu?', 'a' => 'Da – sve kolekcije su izložene u našem salonu u Podgorici. Preporučujemo posjetu jer boja i tekstura na ekranu nisu uvijek identične stvarnom izgledu. Donesete dimenzije prostorije i pomažemo vam da odaberete pravi format, boju i anti-slip klasu.' ),
					array( 'q' => 'Da li se podne i zidne pločice mogu kombinovati iz iste kolekcije?', 'a' => 'Da – sve naše kolekcije dolaze u koordiniranim podnim i zidnim varijantama. Podna verzija je deblja i ima anti-slip finish, zidna je tanja i glatka. Isti dizajn, različita tehnika – savršen koordinirani izgled bez kompromisa.' ),
					array( 'q' => 'Kako se čiste i održavaju podne pločice?', 'a' => 'Keramičke pločice su izuzetno lake za održavanje – redovno brisanje vlažnom krpom je dovoljno. Za fugne preporučujemo fugnu masu otpornu na plijesan i povremeno čišćenje fugnim čistačem. Poliran finish zahtijeva nešto pažljivije čišćenje jer pokazuje tragove. Mat i teksturiran finish su praktičniji za svakodnevnu upotrebu.' ),
				),
			),
			'crosssell' => array(
				'title' => 'Upotpunite renovaciju',
				'items' => array(
					array( 'cat' => 'zidne', 'img' => 'https://images.unsplash.com/photo-1600566752355-35792bedcfea?w=400&q=80', 'alt' => 'Zidne pločice', 'title' => 'Zidne pločice', 'desc' => 'Koordinirane zidne varijante iz istih kolekcija – jedinstven izgled od poda do plafona.' ),
					array( 'cat' => 'umivaonici', 'img' => 'https://images.unsplash.com/photo-1584622650111-993a426fbf0a?w=400&q=80', 'alt' => 'Dekorativni umivaonici Bathco', 'title' => 'Dekorativni umivaonici', 'desc' => 'Bathco umivaonici iz Španije – isti izvor, isti standard. Završite kupatilo u stilu.' ),
					array( 'cat' => 'staklena-vrata', 'img' => 'https://images.unsplash.com/photo-1600585154526-990dced4db0d?w=400&q=80', 'alt' => 'Staklena vrata za kupatilo', 'title' => 'Staklena vrata', 'desc' => 'Staklena vrata za kupatilo – satinato staklo za privatnost i svjetlost. Savršen završetak renovacije.' ),
				),
			),
			'cta' => array(
				'title' => 'Vidite pločice uživo – boja na ekranu laže',
				'desc'  => 'Donesete dimenzije prostorije i pomažemo vam da odaberete pravi format, boju i anti-slip klasu. Sve kolekcije su izložene u salonu u Podgorici.',
				'phone' => $tel,
			),
		),

	);
}
