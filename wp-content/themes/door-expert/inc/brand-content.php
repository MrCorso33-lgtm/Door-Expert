<?php
/**
 * Sadržaj brend stranica – ODVOJEN od prikaza (paralela inc/category-content.php).
 *
 * 5 brendova (new-tiles, tau-ceramica, arcana-ceramica, ribesalbes, bathco) dijele
 * JEDAN uniforman prikaz (template-parts/page/parts/brand.php) i variraju samo po
 * podacima ovdje. FAZA A: hardkodovano. Produkcija: `brand` CPT/taksonomija + get_post_meta().
 *
 * Struktura po brendu: grad_a/grad_b (hero+cta gradijent), hero_img, badge, title, subtitle,
 * about_title, about[] (paragrafi), about_img/alt, facts[] (label/value),
 * collections_title, collections[] (img/alt/name/desc), values_title, values[] (icon/title/desc),
 * cta_title, cta_text, cta_cat (product_cat slug za dugme "Pogledaj proizvode").
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Vrati sadržaj brenda po slug-u.
 *
 * @param string $slug Brend slug (post_name stranice).
 * @return array Prazan array ako brend nije definisan.
 */
function door_expert_brand_content( $slug ) {
	static $data = null;

	if ( null === $data ) {
		$data = door_expert_brand_content_all();
	}

	return isset( $data[ $slug ] ) ? $data[ $slug ] : array();
}

/**
 * Sav hardkodovan sadržaj brendova (Faza A).
 *
 * @return array<string,array>
 */
function door_expert_brand_content_all() {
	return array(

		// ── NEW TILES ────────────────────────────────────────────
		'new-tiles' => array(
			'grad_a'   => '#1e3a2f',
			'grad_b'   => '#0d1a14',
			'hero_img' => 'https://images.unsplash.com/photo-1615971677499-5467cbab01c0?w=1400&q=60',
			'badge'    => "🇪🇸 L'Alcora, Castellón – Španija",
			'title'    => 'New Tiles',
			'subtitle' => 'Mlad brend sa velikim ambicijama. Savremeni dizajn, široka paleta formata i konstantno ulaganje u kvalitet – od 2014. godine.',
			'about_title' => 'Energija novog pristupa',
			'about'    => array(
				"New Tiles je osnovan 2014. godine u L'Alcori, u samom srcu španske keramičke industrije. Iako je najmlađi brend u našem portfoliju, njihov rast je impresivan – zahvaljujući konstantnom ulaganju u dizajn, kvalitet i servis.",
				'Kompanija proizvodi keramičke pločice u bijeloj masi, glaziranom porcelanu i zidnim oblogama sa širokom paletom završnih obrada. Posvećeni su ekološkoj odgovornosti i implementiraju evropske standarde kvaliteta (UNE EN 14411).',
			),
			'about_img'     => 'https://images.unsplash.com/photo-1615971677499-5467cbab01c0?w=700&q=80',
			'about_img_alt' => 'New Tiles proizvodi',
			'facts'    => array(
				array( 'label' => 'Osnovano', 'value' => '2014. godina' ),
				array( 'label' => 'Sjedište', 'value' => "L'Alcora, Castellón" ),
				array( 'label' => 'Specijalizacija', 'value' => 'Savremeni dizajn, široki formati' ),
				array( 'label' => 'Standardi', 'value' => 'ISO, UNE EN 14411' ),
			),
			'collections_title' => 'Popularne New Tiles kolekcije',
			'collections' => array(
				array( 'img' => 'https://images.unsplash.com/photo-1600566752229-250ed79470f8?w=500&q=80', 'alt' => 'Concrete kolekcija', 'name' => 'Concrete', 'desc' => 'Industrijski betonski izgled za savremene enterijere. Različiti formati i nijanse sive.' ),
				array( 'img' => 'https://images.unsplash.com/photo-1600607687920-4e2a09cf159d?w=500&q=80', 'alt' => 'Marmi kolekcija', 'name' => 'Marmi', 'desc' => 'Elegancija mramora u keramičkom formatu. Bijeli, sivi i crni mramorni uzorci.' ),
				array( 'img' => 'https://images.unsplash.com/photo-1600585154363-67eb9e2e2099?w=500&q=80', 'alt' => 'Montblanc kolekcija', 'name' => 'Montblanc', 'desc' => 'Inspirisana planinom – prirodni kamen efekti za podove i fasade. Izdržljivost za eksterijer.' ),
				array( 'img' => 'https://images.unsplash.com/photo-1600573472591-ee6b68d14c68?w=500&q=80', 'alt' => 'Etna kolekcija', 'name' => 'Etna', 'desc' => 'Vulkanski kamen u porcelanu – tamne, dramatične površine za statement enterijere.' ),
			),
			'values_title' => 'Zašto New Tiles?',
			'values'   => array(
				array( 'icon' => '🌱', 'title' => 'Ekološka svijest', 'desc' => 'Posvećenost životnoj sredini – implementacija evropskih ekoloških standarda u proizvodnji.' ),
				array( 'icon' => '📈', 'title' => 'Konstantan rast', 'desc' => 'Od 2014. neprekidno ulaganje u dizajn, kvalitet i ljudske resurse. Čvrsta pozicija u sektoru.' ),
				array( 'icon' => '🎨', 'title' => 'Savremeni dizajn', 'desc' => 'Posebna pažnja posvećena dizajnu – svaka kolekcija prati najnovije trendove u enterijeru.' ),
				array( 'icon' => '✅', 'title' => 'ISO standardi', 'desc' => 'Sertifikovana proizvodnja prema evropskim normama kvaliteta i sigurnosti.' ),
			),
			'cta_title' => 'Pogledajte New Tiles kolekcije',
			'cta_text'  => 'Concrete, Marmi, Montblanc i Etna – sve dostupno u našem salonu za pregled uživo.',
			'cta_cat'   => 'keramicke-plocice',
		),

		// ── TAU CERÁMICA ─────────────────────────────────────────
		'tau-ceramica' => array(
			'grad_a'   => '#2c1810',
			'grad_b'   => '#1a1a1a',
			'hero_img' => 'https://images.unsplash.com/photo-1615873968403-89e068629265?w=1400&q=60',
			'badge'    => '🇪🇸 Onda, Castellón – Španija',
			'title'    => 'TAU Cerámica',
			'subtitle' => 'Keramika postaje umjetnost kada se dizajn i inovacija sretnu. TAU stvara površine koje definišu jedinstvene prostore – od 1967. godine.',
			'about_title' => 'Više od pola vijeka inovacija',
			'about'    => array(
				'TAU Cerámica je osnovana 1967. godine u Castellónu de la Plana kao Taullel S.A. – udruženje pet kompanija sa dubokim korijenima u svijetu keramike. Danas je dio Pamesa grupe i jedan od vodećih evropskih proizvođača premium porcelanskih pločica.',
				'Njihova filozofija "Feel&Mix" znači da keramika ne pokriva samo površine – ona transformiše prostore i priča priče. Svaka kolekcija je rezultat spoja najnovije tehnologije i stoljetnog zanatskog znanja iz regiona Castellón.',
			),
			'about_img'     => 'https://images.unsplash.com/photo-1615971677499-5467cbab01c0?w=700&q=80',
			'about_img_alt' => 'TAU Cerámica proizvodi',
			'facts'    => array(
				array( 'label' => 'Osnovano', 'value' => '1967. godina' ),
				array( 'label' => 'Sjedište', 'value' => 'Onda, Castellón' ),
				array( 'label' => 'Specijalizacija', 'value' => 'Porcelanski gres, veliki formati' ),
				array( 'label' => 'Grupa', 'value' => 'Pamesa Group' ),
			),
			'collections_title' => 'Popularne TAU kolekcije u našem salonu',
			'collections' => array(
				array( 'img' => 'https://images.unsplash.com/photo-1600566752355-35792bedcfea?w=500&q=80', 'alt' => 'Evolve kolekcija', 'name' => 'Evolve', 'desc' => 'Najnovija kolekcija – savremeni dizajn sa bezvremenskom elegancijom. Veliki formati za otvorene prostore.' ),
				array( 'img' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=500&q=80', 'alt' => 'Marble efekat', 'name' => 'Marble efekti', 'desc' => 'Porcelanske pločice sa realističnim mramornim uzorcima. Luksuz prirodnog kamena bez održavanja.' ),
				array( 'img' => 'https://images.unsplash.com/photo-1600573472591-ee6b68d14c68?w=500&q=80', 'alt' => 'Wood efekat', 'name' => 'Wood efekti', 'desc' => 'Toplina drveta u izdržljivosti porcelana. Idealno za podove u dnevnim sobama i spavaćim sobama.' ),
				array( 'img' => 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=500&q=80', 'alt' => 'Cement efekat', 'name' => 'Cement efekti', 'desc' => 'Industrijski šik za savremene enterijere. Betonski izgled sa svim prednostima keramike.' ),
			),
			'values_title' => 'Zašto TAU Cerámica?',
			'values'   => array(
				array( 'icon' => '🏭', 'title' => '57 godina iskustva', 'desc' => 'Od 1967. godine, TAU je sinonim za kvalitet i inovaciju u keramičkoj industriji.' ),
				array( 'icon' => '📐', 'title' => 'Veliki formati', 'desc' => 'Pločice do 120x260cm – manje fuga, čistiji izgled, lakše održavanje.' ),
				array( 'icon' => '🎨', 'title' => '8 stilskih efekata', 'desc' => 'Mramor, drvo, beton, kamen, metal, tekstil, glina i basic – za svaki enterijer.' ),
				array( 'icon' => '🌍', 'title' => 'Globalni standard', 'desc' => 'Prisutni na svim kontinentima. Učesnik World Design Capital Valencia 2022.' ),
			),
			'cta_title' => 'Pogledajte TAU kolekcije u našem salonu',
			'cta_text'  => 'Svi TAU proizvodi dostupni za pregled uživo. Besplatna ponuda i savjetovanje.',
			'cta_cat'   => 'keramicke-plocice',
		),

		// ── ARCANA CERÁMICA ──────────────────────────────────────
		'arcana-ceramica' => array(
			'grad_a'   => '#1a2332',
			'grad_b'   => '#0d1117',
			'hero_img' => 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=1400&q=60',
			'badge'    => "🇪🇸 L'Alcora, Castellón – Španija",
			'title'    => 'Arcana Cerámica',
			'subtitle' => 'Visokokvalitetni porcelanski gres sa high-end dizajnom. Kolekcije koje spajaju eleganciju i avangardni pristup – od 1997. godine.',
			'about_title' => 'Dizajn bez kompromisa',
			'about'    => array(
				"Arcana Cerámica je osnovana 1997. godine u L'Alcori, u srcu keramičkog regiona Castellón. Od prvog dana, misija je jasna: ponuditi high-end proizvod sa high-end dizajnom koji se razlikuje od svega drugog na tržištu.",
				'Zahvaljujući najsavremenijim proizvodnim tehnikama i visoko kvalifikovanom timu, Arcana konstantno pomjera granice u dizajnu, formatima i završnim obradama svojih keramičkih proizvoda. Prisutni su na svih pet kontinenata.',
			),
			'about_img'     => 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=700&q=80',
			'about_img_alt' => 'Arcana Cerámica proizvodi',
			'facts'    => array(
				array( 'label' => 'Osnovano', 'value' => '1997. godina' ),
				array( 'label' => 'Sjedište', 'value' => "L'Alcora, Castellón" ),
				array( 'label' => 'Specijalizacija', 'value' => 'Porcelanski gres, high-end dizajn' ),
				array( 'label' => 'Izvoz', 'value' => '5 kontinenata' ),
			),
			'collections_title' => 'Popularne Arcana kolekcije u našem salonu',
			'collections' => array(
				array( 'img' => 'https://images.unsplash.com/photo-1600566753086-00f18fb6b3ea?w=500&q=80', 'alt' => 'Stracciatella kolekcija', 'name' => 'Stracciatella', 'desc' => 'Najpopularnija kolekcija – estetika teraca i encaustičnih pločica u savremenom ključu. Više formata i završnih obrada.' ),
				array( 'img' => 'https://images.unsplash.com/photo-1600585154526-990dced4db0d?w=500&q=80', 'alt' => 'Croccante kolekcija', 'name' => 'Croccante', 'desc' => 'Mramorne čipke u savremenom ključu – porcelanski gres koji interpretira estetiku mramornih komadića.' ),
				array( 'img' => 'https://images.unsplash.com/photo-1600210492493-0946911123ea?w=500&q=80', 'alt' => 'Black and Cream kolekcija', 'name' => 'Black & Cream', 'desc' => 'Elegantni, avangardni elementi koji donose modernost rijetko viđenu u keramičkom dizajnu enterijera.' ),
			),
			'values_title' => 'Zašto Arcana Cerámica?',
			'values'   => array(
				array( 'icon' => '💎', 'title' => 'Premium pozicioniranje', 'desc' => 'High-end proizvod sa high-end dizajnom – Arcana se svjesno razlikuje od masovne proizvodnje.' ),
				array( 'icon' => '🔬', 'title' => 'Inovativna tehnologija', 'desc' => 'Najsavremenije proizvodne tehnike za konstantan napredak u dizajnu i završnim obradama.' ),
				array( 'icon' => '🌍', 'title' => 'Globalno prisustvo', 'desc' => 'Izvoz na svih 5 kontinenata – međunarodno priznat kvalitet i dizajn.' ),
				array( 'icon' => '🤝', 'title' => 'Posvećenost klijentu', 'desc' => 'Brza i efikasna usluga uz garantovana rješenja za svaki projekat.' ),
			),
			'cta_title' => 'Pogledajte Arcana kolekcije uživo',
			'cta_text'  => 'Stracciatella, Croccante i druge kolekcije dostupne u našem salonu. Besplatno savjetovanje.',
			'cta_cat'   => 'keramicke-plocice',
		),

		// ── CERÁMICA RIBESALBES ──────────────────────────────────
		'ribesalbes' => array(
			'grad_a'   => '#2d1f3d',
			'grad_b'   => '#1a1020',
			'hero_img' => 'https://images.unsplash.com/photo-1584622650111-993a426fbf0a?w=1400&q=60',
			'badge'    => '🇪🇸 Onda, Castellón – Španija',
			'title'    => 'Cerámica Ribesalbes',
			'subtitle' => 'Spoj artizanske tradicije i savremene tehnologije. Lider u proizvodnji metro pločica i malih formata sa maurskim naslijeđem – od 1986. godine.',
			'about_title' => 'Zanatska tradicija iz Valencije',
			'about'    => array(
				'Cerámica Ribesalbes je osnovana 1986. godine u Ondi, u provinciji Castellón. Sa skoro četiri decenije iskustva, kompanija je postala lider u proizvodnji dekorativnih keramičkih pločica za zidove i podove.',
				'Njihova filozofija spaja tradicionalno zanatstvo sa modernom tehnologijom. Specijalizovani su za male formate, metro pločice, sokl pločice i reprodukcije hidrauličnog cementa – sa dizajnom koji čuva maursko naslijeđe Valencije i Andaluzije.',
			),
			'about_img'     => 'https://images.unsplash.com/photo-1584622650111-993a426fbf0a?w=700&q=80',
			'about_img_alt' => 'Cerámica Ribesalbes proizvodi',
			'facts'    => array(
				array( 'label' => 'Osnovano', 'value' => '1986. godina' ),
				array( 'label' => 'Sjedište', 'value' => 'Onda, Castellón' ),
				array( 'label' => 'Specijalizacija', 'value' => 'Metro pločice, mali formati' ),
				array( 'label' => 'Sajmovi', 'value' => 'Cersaie 2026 izlagač' ),
			),
			'collections_title' => 'Popularne Ribesalbes kolekcije',
			'collections' => array(
				array( 'img' => 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=500&q=80', 'alt' => 'Vibe kolekcija', 'name' => 'Vibe', 'desc' => 'Boje koje vas transportuju u ugodne prostore. Šest različitih boja i formata za kreativne kombinacije.' ),
				array( 'img' => 'https://images.unsplash.com/photo-1600210491369-e753d80a41f3?w=500&q=80', 'alt' => 'Santorini kolekcija', 'name' => 'Santorini', 'desc' => 'Porcelanska kolekcija inspirisana mediteranskim ostrvom. Za kupatila, kuhinje i dnevne sobe.' ),
				array( 'img' => 'https://images.unsplash.com/photo-1600607687920-4e2a09cf159d?w=500&q=80', 'alt' => 'Classic Marbles kolekcija', 'name' => 'Classic Marbles', 'desc' => 'Bezvremenski mramorni izgled u malim formatima. Tri veličine za svestrane primjene.' ),
				array( 'img' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=500&q=80', 'alt' => 'Maurske kolekcije', 'name' => 'Maurske serije', 'desc' => 'Cádiz, Granada, Córdoba, Triana – dizajni koji evociraju andaluzijske patije i maursku arhitekturu.' ),
			),
			'values_title' => 'Zašto Cerámica Ribesalbes?',
			'values'   => array(
				array( 'icon' => '🎨', 'title' => 'Artizanski pristup', 'desc' => 'Spoj ručnog zanatstva i moderne tehnologije – svaka pločica nosi duh tradicije.' ),
				array( 'icon' => '📏', 'title' => 'Mali formati', 'desc' => 'Specijalizacija za metro pločice i male formate koji daju karakter svakom prostoru.' ),
				array( 'icon' => '🕌', 'title' => 'Maursko naslijeđe', 'desc' => 'Dizajni inspirisani bogatom maurskom tradicijom Valencije i Andaluzije.' ),
				array( 'icon' => '🏆', 'title' => '38 godina kvaliteta', 'desc' => 'Od 1986. – dokazana pouzdanost i konstantno prisustvo na vodećim svjetskim sajmovima.' ),
			),
			'cta_title' => 'Pogledajte Ribesalbes kolekcije',
			'cta_text'  => 'Metro pločice, maurski dizajni i porcelanske kolekcije – sve dostupno u našem salonu.',
			'cta_cat'   => 'keramicke-plocice',
		),

		// ── BATHCO ───────────────────────────────────────────────
		'bathco' => array(
			'grad_a'   => '#1a2f3d',
			'grad_b'   => '#0d1820',
			'hero_img' => 'https://images.unsplash.com/photo-1552321554-5fefe8c9ef14?w=1400&q=60',
			'badge'    => '🇪🇸 Santander, Cantabria – Španija',
			'title'    => 'Bathco',
			'subtitle' => 'Porodična kompanija sa dušom. Više od 45 godina dizajna, inovacije i iskustva u stvaranju kupatilskih komada koji transformišu svakodnevicu u nešto posebno.',
			'about_title' => 'Iz srca Cantabrije, u 100+ zemalja',
			'about'    => array(
				'Bathco (The Bath Collection) je porodična kompanija iz Santandera sa više od 45 godina iskustva u dizajnu i proizvodnji kupatilske opreme. Danas su prisutni u preko 100 zemalja svijeta.',
				'Njihov nemirni duh ih vodi ka saradnji sa umjetnicima, istraživanju novih materijala i razvoju kolekcija koje kombinuju funkcionalnost, dizajn i emociju. U Bathco-u ne prave samo proizvode – stvaraju iskustva koja povezuju ljude i pretvaraju kupatilo u prostor blagostanja.',
				'Posebna vrijednost je njihov Atelier – kreativni prostor osnovan 2015. gdje multidisciplinarni umjetnici ručno dekorišu unikatne komade za najzahtjevnije klijente.',
			),
			'about_img'     => 'https://images.unsplash.com/photo-1552321554-5fefe8c9ef14?w=700&q=80',
			'about_img_alt' => 'Bathco proizvodi',
			'facts'    => array(
				array( 'label' => 'Iskustvo', 'value' => '45+ godina' ),
				array( 'label' => 'Sjedište', 'value' => 'Santander, Cantabria' ),
				array( 'label' => 'Prisustvo', 'value' => '100+ zemalja' ),
				array( 'label' => 'Garancija', 'value' => '5 godina + ISO 9001' ),
			),
			'collections_title' => 'Bathco kolekcije u našem salonu',
			'collections' => array(
				array( 'img' => 'https://images.unsplash.com/photo-1600566752355-35792bedcfea?w=500&q=80', 'alt' => 'Materia kolekcija', 'name' => 'Materia', 'desc' => 'Prirodni materijali – kameni umivaonici koji donose organsku toplinu u svako kupatilo.' ),
				array( 'img' => 'https://images.unsplash.com/photo-1600210491369-e753d80a41f3?w=500&q=80', 'alt' => 'Texture kolekcija', 'name' => 'Texture', 'desc' => 'Reljefne površine koje stimulišu oko i dodir. Tehnika i zanatstvo u službi dizajna.' ),
				array( 'img' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=500&q=80', 'alt' => 'Atelier kolekcija', 'name' => 'Atelier', 'desc' => 'Ručno dekorisani unikatni komadi iz Bathco radionice. Svaki umivaonik je umjetničko djelo.' ),
				array( 'img' => 'https://images.unsplash.com/photo-1600573472591-ee6b68d14c68?w=500&q=80', 'alt' => 'Colors kolekcija', 'name' => 'Colors Collection', 'desc' => 'Boja kao alat za dizajn – umivaonici u živim nijansama koji daju karakter kupatilu.' ),
			),
			'values_title' => 'Zašto Bathco?',
			'values'   => array(
				array( 'icon' => '👨‍👩‍👧‍👦', 'title' => 'Porodična kompanija', 'desc' => 'Više od 45 godina porodične tradicije – svaki detalj je pažljivo osmišljen sa ljubavlju.' ),
				array( 'icon' => '🎨', 'title' => 'Atelier radionica', 'desc' => 'Kreativni prostor sa multidisciplinarnim umjetnicima za ručno dekorisane unikatne komade.' ),
				array( 'icon' => '✅', 'title' => '5 godina garancije', 'desc' => 'ISO 9001 + ISO 14001 sertifikati. Proizvodi dizajnirani da traju čitav život.' ),
				array( 'icon' => '🌍', 'title' => '100+ zemalja', 'desc' => 'Globalno prisustvo potvrđuje kvalitet – od Santandera do Podgorice, isti standard.' ),
			),
			'cta_title' => 'Pogledajte Bathco umivaonik uživo',
			'cta_text'  => 'Kameni, nadgradni i dekorativni umivaonici – svi dostupni za pregled u našem salonu.',
			'cta_cat'   => 'umivaonici',
		),

	);
}
