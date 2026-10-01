<?php
/**
 * Single product (PDP) – helperi: grupa proizvoda (po top-level kategoriji) + FAQ sadržaj.
 * Odvojen od prikaza (paralela inc/category-content.php). Prikaz: template-parts/product/single.php.
 *
 * Grupa određuje koje sekcije/FAQ se prikazuju (vrata / plocice / umivaonik).
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Odredi "grupu" proizvoda po top-level product_cat pretku.
 *
 * @param int $product_id ID proizvoda.
 * @return string 'vrata' | 'plocice' | 'umivaonik' | '' (nepoznato).
 */
function door_expert_product_group( $product_id ) {
	$terms = get_the_terms( $product_id, 'product_cat' );
	if ( ! is_array( $terms ) ) {
		return '';
	}

	$map = array(
		'sobna-vrata'       => 'vrata',
		'sigurnosna-vrata'  => 'vrata',
		'keramicke-plocice' => 'plocice',
		'umivaonici'        => 'umivaonik',
	);

	foreach ( $terms as $term ) {
		// Popni se do top-level pretka.
		$top = $term;
		while ( 0 !== (int) $top->parent ) {
			$parent = get_term( $top->parent, 'product_cat' );
			if ( ! $parent instanceof WP_Term ) {
				break;
			}
			$top = $parent;
		}
		if ( isset( $map[ $top->slug ] ) ) {
			return $map[ $top->slug ];
		}
	}

	return '';
}

/**
 * Prikaz zalihe za PDP – jedan izvor istine za PHP i za JS.
 *
 * Tri stanja, ne dva: kod varijabilnih proizvoda blok dostupnosti mora pratiti
 * IZABRANU varijaciju, pa iste stringove kroz wp_localize_script dobija i
 * assets/js/product.js. Ako se mijenja tekst, mijenja se SAMO ovdje.
 *
 * Zaliha NIJE prepreka nego rok: korpa je upit, ne naplata. Zato nijedno stanje
 * nije crveno i nijedan tekst ne odvraća od dugmeta - `note` se ispisuje uz sam
 * CTA i potvrđuje da upit prolazi, tačno tamo gdje kupac okleva.
 *
 * @param string $status WC stock status: instock | onbackorder | outofstock.
 * @return array{modifier:string,label:string,sub:string,note:string}
 */
function door_expert_stock_display( $status ) {
	if ( 'instock' === $status ) {
		return array(
			'modifier' => 'in-stock',
			'label'    => 'Na stanju u Podgorici',
			'sub'      => 'Isporuka odmah · Montaža po dogovoru (2–15 dana)',
			'note'     => '',
		);
	}

	if ( 'onbackorder' === $status ) {
		return array(
			'modifier' => 'backorder',
			'label'    => 'Po narudžbi',
			'sub'      => 'Rok isporuke po dogovoru',
			'note'     => 'Rok isporuke javljamo u ponudi',
		);
	}

	return array(
		'modifier' => 'out-of-stock',
		'label'    => 'Trenutno nije na zalihama',
		'sub'      => 'Upit svejedno prolazi · javljamo rok isporuke',
		'note'     => 'Rok isporuke javljamo u ponudi',
	);
}

/**
 * Popust proizvoda za karticu: procenat i da li važi za cijeli proizvod.
 *
 * Kod običnog proizvoda je jednostavno: redovna i akcijska cijena. Kod varijabilnog
 * roditelj NEMA svoju cijenu (ima je svaka varijacija), pa je ranija provjera nad
 * get_regular_price()/get_sale_price() roditelja uvijek padala i kartica nije
 * pokazivala nikakav popust, čak ni kad je varijacija na akciji.
 *
 * Vraća:
 *   pct      najveći popust u procentima (zaokružen)
 *   uniform  true kad je SVAKA varijacija snižena za isti procenat; inače kartica piše
 *            "do -6%", jer "-6%" bi sugerisalo da je cijeli proizvod jeftiniji, a
 *            snižena je možda samo jedna širina
 *   single   true kad sve varijacije imaju istu redovnu i istu trenutnu cijenu, pa
 *            kartica može da pokaže jednu staru i jednu novu cijenu umjesto raspona
 *   regular  redovna cijena (za prikaz kad je single)
 *   price    trenutna cijena (za prikaz kad je single)
 *
 * Cijene su "za prikaz" (sa ili bez PDV-a, kako je podešeno u WooCommerce-u), isto
 * kao get_price_html(), da se broj na kartici ne razlikuje od broja u rasponu.
 * Varijacije bez cijene ("Cijena na upit") ne ulaze u računicu popusta.
 *
 * @param WC_Product $product Proizvod.
 * @return array{pct:int,uniform:bool,single:bool,regular:float,price:float}|null
 */
function door_expert_product_discount( $product ) {
	if ( ! $product instanceof WC_Product || ! $product->is_on_sale() ) {
		return null;
	}

	$pairs = array();

	if ( $product->is_type( 'variable' ) ) {
		$prices = $product->get_variation_prices( true );

		foreach ( (array) $prices['price'] as $id => $price ) {
			$regular = isset( $prices['regular_price'][ $id ] ) ? (float) $prices['regular_price'][ $id ] : 0.0;
			$pairs[] = array( $regular, (float) $price );
		}
	} else {
		$pairs[] = array(
			(float) wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) ),
			(float) wc_get_price_to_display( $product ),
		);
	}

	$pcts           = array();
	$all_discounted = true;

	foreach ( $pairs as $pair ) {
		list( $regular, $price ) = $pair;

		if ( 0.0 < $regular && 0.0 < $price && $price < $regular ) {
			$pct = (int) round( ( ( $regular - $price ) / $regular ) * 100 );

			if ( 0 < $pct ) {
				$pcts[] = $pct;
				continue;
			}
		}

		$all_discounted = false;
	}

	if ( empty( $pcts ) ) {
		return null;
	}

	$first  = $pairs[0];
	$single = true;

	foreach ( $pairs as $pair ) {
		if ( $pair[0] !== $first[0] || $pair[1] !== $first[1] ) {
			$single = false;
			break;
		}
	}

	return array(
		'pct'     => max( $pcts ),
		'uniform' => $all_discounted && 1 === count( array_unique( $pcts ) ),
		'single'  => $single,
		'regular' => $first[0],
		'price'   => $first[1],
	);
}

/**
 * FAQ stavke za PDP – dijeljene + po grupi. Tekst vjeran prototipu product.html.
 *
 * @param string $group 'vrata' | 'plocice' | 'umivaonik' | ''.
 * @return array<int,array{q:string,a:string}>
 */
function door_expert_product_faq( $group ) {
	$shared = array(
		array(
			'q' => 'Šta se dešava nakon što pošaljem upit?',
			'a' => 'Vaš upit prima naš tim u Podgorici. U roku od 24 sata radnim danom šaljemo vam formalnu ponudu / pro formu fakturom emailom, sa svim detaljima: cijenom, dimenzijama, rokom isporuke i uslovima plaćanja. Nema automatske naplate – ponuda je bez obaveze.',
		),
	);

	$by_group = array(
		'vrata'     => array(
			array(
				'q' => 'Da li je montaža uključena u cijenu?',
				'a' => 'Montaža nije uključena u cijenu vrata. Door Expert je uvoznik i distributer – prodajemo vrata, keramiku i kupatilske elemente. Montažu vrše nezavisni majstori koje možemo preporučiti; oni naplaćuju direktno klijentu. Rok montaže je 2–15 dana od isporuke, po dogovoru sa majstorom.',
			),
			array(
				'q' => 'Šta je uključeno u cijenu – samo krilo ili i okvir?',
				'a' => 'Cijena uključuje vrata krilo i štok-okvir (prilagodljiva širina zida). Kvaka, brava i šarke nisu uključene, ali su dostupne u našem salonu. Navesti ćemo sve detalje u formalnoj ponudi.',
			),
			array(
				'q' => 'Da li postoje nestandardne dimenzije?',
				'a' => 'Standardne dimenzije su 200×80, 200×90 i 210×90 cm. Za nestandardne dimenzije (npr. niski otvori ili veće dimenzije) kontaktirajte nas direktno – za B2B/investitorske projekte sa 20+ vrata moguće je dogovoriti posebne dimenzije.',
			),
			array(
				'q' => 'Koliko je rok isporuke?',
				'a' => 'Vrata su na stanju u našem skladištu u Podgorici – isporuka je odmah, bez čekanja. Transport do vašeg objekta dogovaramo posebno.',
			),
		),
		'plocice'   => array(
			array(
				'q' => 'Koliko paketa trebam za moju prostoriju?',
				'a' => 'Koristite kalkulator iznad – unesite dimenzije prostorije i automatski ćete dobiti potrebnu površinu sa 10% rezervom za rezanje. Tačan broj paketa potvrđujemo u ponudi (zavisi od pakovanja kolekcije).',
			),
			array(
				'q' => 'Da li su pločice pogodne za pod i za zid?',
				'a' => 'Zavisi od kolekcije – većina naših porcelanskih pločica je certificirana za pod i zid u unutrašnjim prostorima. Mat verzije obično imaju R9 anti-slip certifikat, što je pogodno za kupatila i hodnike. Detalji su u specifikaciji proizvoda.',
			),
			array(
				'q' => 'Zašto španske pločice?',
				'a' => 'Španija je jedan od vodećih svjetskih proizvođača keramike – Tau Ceramica, Arcana, New Tiles i Ceramica Ribesalbes su brendovi s dugom tradicijom i strogim standardima kvaliteta. Španska keramika se odlikuje konzistentnom bojom, niskom apsorpcijom vode i dugotrajnošću.',
			),
		),
		'umivaonik' => array(
			array(
				'q' => 'Da li slavina dolazi uz umivaonik?',
				'a' => 'Slavina nije uključena u cijenu umivaonika. Dostupne su kompatibilne slavine u našem salonu – preporučujemo modele koji vizuelno i tehnički odgovaraju ovom umivaoniku.',
			),
			array(
				'q' => 'Koja je visina umivaonika od poda?',
				'a' => 'Nadgradni umivaonik postavlja se na ormarić – ukupna visina zavisi od visine vašeg kupaonskog ormarića. Standardna visina ormarića je 80–85 cm. Preporučujemo da izmjerite prostor i navedete dimenzije u upitu.',
			),
			array(
				'q' => 'Zašto Bathco?',
				'a' => 'Bathco je španski brend s više od 30 godina tradicije u dizajnu kupaonskih elemenata. Svaki umivaonik prolazi kroz rigoroznu kontrolu kvaliteta. Door Expert je ovlašteni distributer Bathco proizvoda u Crnoj Gori.',
			),
		),
	);

	$items = $shared;
	if ( isset( $by_group[ $group ] ) ) {
		$items = array_merge( $items, $by_group[ $group ] );
	}

	return $items;
}
