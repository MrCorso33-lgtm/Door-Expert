<?php
/**
 * Sadržaj WP stranica – ODVOJEN od prikaza (paralela inc/category-content.php).
 *
 * OBRAZAC: za razliku od kategorija (koje dijele JEDAN uniforman skelet i variraju
 * samo po podacima), svaka stranica je BESPOKE – svoj layout, svoja proza. Zato:
 *   • Statična proza + struktura stranice žive u template-parts/page/{slug}.php
 *     (verna konverzija prototipa {slug}.html).
 *   • door_expert_page_content($slug) vraća SAMO ono što ima smisla centralizovati
 *     ili što će u produkciji doći iz baze (npr. dijeljeni podaci kompanije).
 *
 * MIGRACIJA (produkcija): unutrašnjost ovih funkcija se prepiše da čita
 * get_post_meta()/get_option() (JetEngine). Router (page.php) i template-parts se NE diraju.
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Vrati centralizovan sadržaj stranice po slug-u.
 *
 * Faza A: bespoke stranice drže markup u svom template-part-u, pa ovdje vraćamo
 * prazan array. Seam postoji radi jednakog ugovora s router-om (page.php) i radi
 * kasnije migracije na meta/JetEngine.
 *
 * @param string $slug post_name stranice.
 * @return array Prazan array ako nema centralizovanog sadržaja.
 */
function door_expert_page_content( $slug ) {
	static $data = null;

	if ( null === $data ) {
		$data = array(
			// Popunjavati po potrebi kad se pojavi stvarno dijeljen/dinamičan sadržaj.
		);
	}

	return isset( $data[ $slug ] ) ? $data[ $slug ] : array();
}

/**
 * Dijeljeni podaci kompanije – jedno mjesto istine (footer, header, o-nama, kontakt).
 *
 * Zvanični brojevi: +382 69 234 888 (primarni) i +382 69 234 889 (sekundarni).
 * Jednokratni cleanup je standardizovao stari placeholder (123 456) na primarni broj
 * svuda (header/footer/CTA); o-nama lokacija prikazuje oba. U produkciji: get_option()/JetEngine Options.
 *
 * @return array<string,string|array>
 */
function door_expert_company_info() {
	return array(
		'name'      => 'DOOR EXPERT DOO',
		'address'   => '4. jul 74/6, 81110 Podgorica, Crna Gora',
		'phones'    => array( '+382 69 234 888', '+382 69 234 889' ),
		'email'     => 'office@doorexpert.me',
		'hours'     => 'Pon–Pet: 10:00–18:00 · Sub: 10:00–14:00 · Ned: Neradni dan',
		'instagram' => 'https://instagram.com/doorexpert.me',
		'facebook'  => 'https://facebook.com/doorexpert',
	);
}
