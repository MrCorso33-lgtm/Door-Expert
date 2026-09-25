<?php
/**
 * Istaknuti atributi – admin ekran.
 *
 * Proizvodi → Istaknuti atributi. Namjerno pod "Proizvodi", a ne pod "Podešavanja":
 * klijent ovo traži dok razmišlja o proizvodima, ne dok podešava sajt.
 *
 * Lijevo je spisak konteksta (Podrazumijevano + stablo kategorija), desno forma
 * za izabrani kontekst. Ne ispisujemo sve kategorije odjednom jer bi to bilo
 * 30+ kategorija × 4 reda × 2 padajuća menija na jednoj stranici.
 *
 * Tri stanja konteksta, i to se vidi i u spisku i u formi:
 *   svoje       ima svoje redove
 *   naslijeđeno nema svoje, uzima od najbližeg pretka (ili Podrazumijevano)
 *   prazno      namjerno bez trake, ne nasljeđuje ništa
 *
 * Podatkovni sloj: inc/product-highlights.php.
 *
 * @package DoorExpert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registruje podstranicu pod "Proizvodi".
 */
function door_expert_highlights_menu() {
	add_submenu_page(
		'edit.php?post_type=product',
		'Istaknuti atributi',
		'Istaknuti atributi',
		'manage_options',
		'door-expert-highlights',
		'door_expert_highlights_screen'
	);
}
add_action( 'admin_menu', 'door_expert_highlights_menu' );

/**
 * URL ekrana, sa opcionim dodatnim parametrima.
 *
 * @param array $args Dodatni query parametri.
 * @return string
 */
function door_expert_highlights_admin_url( $args = array() ) {
	return add_query_arg(
		array_merge(
			array(
				'post_type' => 'product',
				'page'      => 'door-expert-highlights',
			),
			$args
		),
		admin_url( 'edit.php' )
	);
}

/**
 * Aseti ekrana.
 *
 * Kroz wp_enqueue_* i sa filemtime() verzijom, kao i aseti teme (CLAUDE.md §3–4).
 * Ranije je sve stajalo kao inline style="" po PHP-u.
 *
 * @param string $hook Trenutni admin ekran.
 */
function door_expert_highlights_admin_assets( $hook ) {
	if ( 'product_page_door-expert-highlights' !== $hook ) {
		return;
	}

	$uri = get_template_directory_uri();

	wp_enqueue_style(
		'door-expert-highlights-admin',
		$uri . '/assets/css/admin-highlights.css',
		array(),
		door_expert_ver( '/assets/css/admin-highlights.css' )
	);

	wp_enqueue_script(
		'door-expert-highlights-admin',
		$uri . '/assets/js/admin-highlights.js',
		array(),
		door_expert_ver( '/assets/js/admin-highlights.js' ),
		true
	);

	// SVG markup ikona ide u JS da bi pregled pored padajućeg menija bio isti
	// glif koji se vidi na sajtu, bez drugog spiska koji se raziđe s ovim.
	$icons = array();
	foreach ( door_expert_highlight_icons() as $key => $icon ) {
		$icons[ $key ] = $icon['svg'];
	}

	wp_localize_script(
		'door-expert-highlights-admin',
		'doorExpertHighlights',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'door_expert_highlights_coverage' ),
			'icons'   => $icons,
			'i18n'    => array(
				'checking' => 'računam…',
				'error'    => 'greška',
			),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'door_expert_highlights_admin_assets' );

/**
 * Izvori grupisani za <optgroup>.
 *
 * @return array<string,array<string,string>> grupa => ( kljuc => labela ).
 */
function door_expert_highlights_grouped_sources() {
	$grouped = array();

	foreach ( door_expert_highlights_sources() as $key => $source ) {
		$grouped[ $source['group'] ][ $key ] = $source['label'];
	}

	return $grouped;
}

/**
 * Spisak konteksta za lijevu kolonu: 'default' + stablo product_cat.
 *
 * @return array<int,array{key:string,name:string,depth:int}>
 */
function door_expert_highlights_contexts() {
	$contexts = array(
		array(
			'key'   => 'default',
			'name'  => 'Podrazumijevano (sve ostalo)',
			'depth' => 0,
		),
	);

	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
		)
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return $contexts;
	}

	// Poredaj u stablo da se odnos roditelj/dijete vidi na prvi pogled.
	$by_parent = array();
	foreach ( $terms as $term ) {
		$by_parent[ (int) $term->parent ][] = $term;
	}

	$walk = function ( $parent_id, $depth ) use ( &$walk, $by_parent, &$contexts ) {
		if ( empty( $by_parent[ $parent_id ] ) ) {
			return;
		}

		foreach ( $by_parent[ $parent_id ] as $term ) {
			$contexts[] = array(
				'key'   => (string) $term->term_id,
				'name'  => $term->name,
				'depth' => $depth,
			);
			$walk( (int) $term->term_id, $depth + 1 );
		}
	};

	$walk( 0, 0 );

	return $contexts;
}

/**
 * Ime konteksta za ispis.
 *
 * @param string $context 'default' ili term ID.
 * @return string
 */
function door_expert_highlights_context_name( $context ) {
	if ( 'default' === $context ) {
		return 'Podrazumijevano (sve ostalo)';
	}

	$term = get_term( (int) $context, 'product_cat' );

	return $term instanceof WP_Term ? $term->name : 'Nepoznata kategorija';
}

/**
 * Koliko proizvoda u kontekstu ima popunjen dati izvor.
 *
 * Bez ovoga se u adminu bira atribut koji niko u toj kategoriji nema, traka
 * ostane prazna i to niko ne primijeti dok klijent ne pozove.
 *
 * @param string $context 'default' ili term ID.
 * @param string $attr    Ključ izvora.
 * @return array{covered:int,total:int}|null null kad se ne može prebrojati.
 */
function door_expert_highlights_coverage( $context, $attr ) {
	$base = array(
		'post_type'              => 'product',
		'post_status'            => 'publish',
		'posts_per_page'         => 1,
		'fields'                 => 'ids',
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
	);

	if ( 'default' !== $context ) {
		$base['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- admin, na zahtjev.
			array(
				'taxonomy'         => 'product_cat',
				'field'            => 'term_id',
				'terms'            => (int) $context,
				'include_children' => true,
			),
		);
	}

	$filtered = $base;

	if ( taxonomy_exists( $attr ) ) {
		$filtered['tax_query'][] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- admin, na zahtjev.
			'taxonomy' => $attr,
			'operator' => 'EXISTS',
		);
	} elseif ( '_de_sku' === $attr ) {
		$filtered['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query -- admin, na zahtjev.
			array(
				'key'     => '_sku',
				'value'   => '',
				'compare' => '!=',
			),
		);
	} else {
		// Statičan tekst i dostupnost ima svaki proizvod; lokalni atributi se ne
		// mogu prebrojati upitom. U oba slučaja bedž nema šta da kaže.
		return null;
	}

	$total   = new WP_Query( $base );
	$covered = new WP_Query( $filtered );

	return array(
		'covered' => (int) $covered->found_posts,
		'total'   => (int) $total->found_posts,
	);
}

/**
 * AJAX: pokrivenost jednog reda.
 */
function door_expert_highlights_coverage_ajax() {
	check_ajax_referer( 'door_expert_highlights_coverage', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Nemate ovlašćenje za ovu radnju.' ), 403 );
	}

	$context = isset( $_POST['context'] ) ? sanitize_key( wp_unslash( $_POST['context'] ) ) : 'default';
	$attr    = isset( $_POST['attr'] ) ? sanitize_key( wp_unslash( $_POST['attr'] ) ) : '';

	$sources = door_expert_highlights_sources();

	if ( '' === $attr || ! isset( $sources[ $attr ] ) ) {
		wp_send_json_success( array( 'text' => '' ) );
	}

	$counts = door_expert_highlights_coverage( $context, $attr );

	if ( null === $counts ) {
		wp_send_json_success( array( 'text' => '' ) );
	}

	wp_send_json_success(
		array(
			'text'  => sprintf( '%d od %d proizvoda', $counts['covered'], $counts['total'] ),
			'warn'  => $counts['total'] > 0 && $counts['covered'] < $counts['total'] / 2,
			'empty' => 0 === $counts['covered'],
		)
	);
}
add_action( 'wp_ajax_door_expert_highlights_coverage', 'door_expert_highlights_coverage_ajax' );

/**
 * Prima snimanje forme.
 */
function door_expert_highlights_save() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html( 'Nemate ovlašćenje za ovu radnju.' ) );
	}

	check_admin_referer( 'door_expert_highlights_save' );

	$context = isset( $_POST['context'] ) ? sanitize_key( wp_unslash( $_POST['context'] ) ) : 'default';
	$none    = isset( $_POST['none'] ) && '1' === $_POST['none'];
	$rows    = isset( $_POST['rows'] ) ? wp_unslash( $_POST['rows'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitizuje door_expert_highlights_sanitize_rows().

	$all   = door_expert_highlights_all();
	$clean = door_expert_highlights_sanitize_rows( $rows );

	if ( $none ) {
		$all[ $context ] = DOOR_EXPERT_HIGHLIGHTS_NONE; // Namjerno prazno, ne nasljeđuje.
	} elseif ( empty( $clean ) ) {
		unset( $all[ $context ] ); // Bez ijednog reda = vrati na nasljeđivanje od pretka.
	} else {
		$all[ $context ] = $clean;
	}

	update_option( DOOR_EXPERT_HIGHLIGHTS_OPTION, $all );

	wp_safe_redirect(
		door_expert_highlights_admin_url(
			array(
				'context' => $context,
				'saved'   => '1',
			)
		)
	);
	exit;
}
add_action( 'admin_post_door_expert_highlights_save', 'door_expert_highlights_save' );

/**
 * Jedan red tabele.
 *
 * @param int    $index    Redni broj (0-based).
 * @param array  $row      Sačuvani red ili prazan niz.
 * @param array  $grouped  Izvori po grupama.
 * @param array  $icons    Set ikonica.
 * @param string $context  Tekući kontekst (za AJAX pokrivenost).
 */
function door_expert_highlights_render_row( $index, $row, $grouped, $icons, $context ) {
	$row_attr     = isset( $row['attr'] ) ? $row['attr'] : '';
	$row_icon     = isset( $row['icon'] ) ? $row['icon'] : '';
	$row_label    = isset( $row['label'] ) ? $row['label'] : '';
	$row_fallback = isset( $row['fallback'] ) ? $row['fallback'] : '';
	$sources      = door_expert_highlights_sources();
	$is_missing   = '' !== $row_attr && ! isset( $sources[ $row_attr ] );
	?>
	<tr class="de-hl-row">
		<td class="de-hl-row__num"><?php echo (int) ( $index + 1 ); ?></td>
		<td class="de-hl-row__move">
			<button type="button" class="button-link de-hl-move" data-dir="up" aria-label="Pomjeri red naviše">▲</button>
			<button type="button" class="button-link de-hl-move" data-dir="down" aria-label="Pomjeri red naniže">▼</button>
		</td>
		<td>
			<select name="rows[<?php echo (int) $index; ?>][attr]" class="de-hl-attr" data-context="<?php echo esc_attr( $context ); ?>">
				<option value="">— prazno —</option>
				<?php foreach ( $grouped as $group => $options ) : ?>
					<optgroup label="<?php echo esc_attr( $group ); ?>">
						<?php foreach ( $options as $slug => $label ) : ?>
							<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $row_attr, $slug ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</optgroup>
				<?php endforeach; ?>
				<?php if ( $is_missing ) : ?>
					<option value="<?php echo esc_attr( $row_attr ); ?>" selected="selected">
						<?php echo esc_html( $row_attr ); ?> (ne postoji više)
					</option>
				<?php endif; ?>
			</select>
			<?php if ( $is_missing ) : ?>
				<p class="de-hl-warning">Ovaj atribut je obrisan iz WooCommerce-a. Red će nestati pri sljedećem čuvanju.</p>
			<?php endif; ?>
			<p class="de-hl-coverage" aria-live="polite"></p>
		</td>
		<td class="de-hl-row__icon">
			<span class="de-hl-preview" aria-hidden="true"></span>
			<select name="rows[<?php echo (int) $index; ?>][icon]" class="de-hl-icon">
				<option value="">— bez ikone —</option>
				<?php foreach ( $icons as $icon_key => $icon ) : ?>
					<option value="<?php echo esc_attr( $icon_key ); ?>" <?php selected( $row_icon, $icon_key ); ?>>
						<?php echo esc_html( $icon['label'] ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</td>
		<td>
			<input type="text" name="rows[<?php echo (int) $index; ?>][label]" value="<?php echo esc_attr( $row_label ); ?>" class="de-hl-input" placeholder="<?php echo esc_attr( 'podrazumijevano: naziv izvora' ); ?>" />
		</td>
		<td>
			<input type="text" name="rows[<?php echo (int) $index; ?>][fallback]" value="<?php echo esc_attr( $row_fallback ); ?>" class="de-hl-input" placeholder="<?php echo esc_attr( 'npr. Garancija 2 godine' ); ?>" />
		</td>
	</tr>
	<?php
}

/**
 * Ispisuje ekran.
 */
function door_expert_highlights_screen() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- samo izbor prikaza, bez izmjene stanja.
	$context = isset( $_GET['context'] ) ? sanitize_key( wp_unslash( $_GET['context'] ) ) : 'default';
	$prefill = isset( $_GET['prefill'] ) && '1' === $_GET['prefill'];
	$saved   = isset( $_GET['saved'] );
	// phpcs:enable WordPress.Security.NonceVerification.Recommended

	$contexts = door_expert_highlights_contexts();
	$grouped  = door_expert_highlights_grouped_sources();
	$icons    = door_expert_highlight_icons();
	$max      = door_expert_highlights_max();

	$is_none   = door_expert_highlights_is_none( $context );
	$has_own   = door_expert_highlights_has_own( $context ) && ! $is_none;
	$source    = door_expert_highlights_source_context( $context );
	$inherited = ( ! $has_own && ! $is_none ) ? door_expert_highlights_rows( $source ) : array();

	// Forma: svoji redovi; kod nasljeđivanja prazna, osim ako je klijent tražio
	// da preuzme naslijeđene kao polaznu tačku ("Preuzmi i izmijeni").
	$rows = $has_own ? door_expert_highlights_rows( $context ) : array();
	if ( $prefill && ! $has_own ) {
		$rows = $inherited;
	}
	?>
	<div class="wrap door-expert-highlights">
		<h1>Istaknuti atributi</h1>

		<?php if ( $saved ) : ?>
			<div class="notice notice-success is-dismissible"><p>Sačuvano.</p></div>
		<?php endif; ?>

		<p class="description de-hl-intro">
			Bira se <strong>koji</strong> podaci se ističu, a ne njihove vrijednosti –
			vrijednosti se čitaju sa samog proizvoda. Prazan izbor u redu briše taj red.
			Kategorija bez ijednog reda nasljeđuje podešavanje od nadređene kategorije,
			a ako ni ona nema, koristi se Podrazumijevano. Najviše
			<?php echo (int) $max; ?> stavke.
		</p>

		<p class="description de-hl-intro">
			Isti izbor se koristi na dva mjesta: <strong>na stranici proizvoda</strong> kao traka
			sa ikonicama, i <strong>na kartici proizvoda</strong> u listinzima kao mali čipovi
			ispod naziva u obliku <em>Labela: vrijednost</em> („Materijal: Puno drvo“), bez ikone,
			i to prva <?php echo (int) DOOR_EXPERT_HIGHLIGHTS_CHIPS; ?> reda koja proizvod ima
			popunjena. Zato se isplati upisati kratku labelu – „Materijal“ umjesto „Materijal vrata“.
		</p>

		<?php if ( empty( $grouped ) ) : ?>
			<div class="notice notice-warning">
				<p>Nema nijednog globalnog atributa. Napravite ih u <em>Proizvodi → Atributi</em>.</p>
			</div>
		<?php endif; ?>

		<div class="de-hl-layout">

			<div class="de-hl-contexts">
				<?php foreach ( $contexts as $item ) : ?>
					<?php
					$item_key     = $item['key'];
					$item_current = $item_key === $context;
					$item_none    = door_expert_highlights_is_none( $item_key );
					$item_own     = door_expert_highlights_has_own( $item_key ) && ! $item_none;
					$item_depth   = min( 3, (int) $item['depth'] );
					$item_class   = 'de-hl-context de-hl-context--d' . $item_depth;

					if ( $item_current ) {
						$item_class .= ' is-current';
					}
					?>
					<a href="<?php echo esc_url( door_expert_highlights_admin_url( array( 'context' => $item_key ) ) ); ?>" class="<?php echo esc_attr( $item_class ); ?>">
						<?php echo esc_html( $item['name'] ); ?>
						<?php if ( $item_own ) : ?>
							<span class="de-hl-dot de-hl-dot--own" title="Ima svoje podešavanje">&bull;</span>
						<?php elseif ( $item_none ) : ?>
							<span class="de-hl-dot de-hl-dot--none" title="Namjerno bez istaknutih atributa">&empty;</span>
						<?php endif; ?>
					</a>
				<?php endforeach; ?>
			</div>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="de-hl-form">
				<?php wp_nonce_field( 'door_expert_highlights_save' ); ?>
				<input type="hidden" name="action" value="door_expert_highlights_save" />
				<input type="hidden" name="context" value="<?php echo esc_attr( $context ); ?>" />

				<h2><?php echo esc_html( door_expert_highlights_context_name( $context ) ); ?></h2>

				<?php if ( $is_none ) : ?>
					<div class="notice notice-info inline">
						<p>Ova kategorija je namjerno <strong>bez istaknutih atributa</strong> i ne nasljeđuje ništa.
						Otkačite kvadratić ispod i sačuvajte da se vrati na nasljeđivanje.</p>
					</div>
				<?php elseif ( ! $has_own ) : ?>
					<div class="notice notice-info inline">
						<p>
							Nema svoje podešavanje – nasljeđuje od
							<strong><?php echo esc_html( door_expert_highlights_context_name( $source ) ); ?></strong>.
							<?php if ( ! empty( $inherited ) && ! $prefill ) : ?>
								<a href="<?php echo esc_url( door_expert_highlights_admin_url( array( 'context' => $context, 'prefill' => '1' ) ) ); ?>">Preuzmi i izmijeni</a>
							<?php endif; ?>
						</p>
						<?php if ( empty( $inherited ) ) : ?>
							<p>Ni taj kontekst nema nijedan red, pa se traka nigdje ne prikazuje.</p>
						<?php else : ?>
							<ul class="de-hl-inherited">
								<?php foreach ( $inherited as $inherited_row ) : ?>
									<li>
										<strong><?php echo esc_html( door_expert_highlights_row_label( $inherited_row ) ); ?></strong>
										<span class="de-hl-inherited__src"><?php echo esc_html( door_expert_highlights_source_label( $inherited_row['attr'] ) ); ?></span>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</div>
					<?php if ( $prefill ) : ?>
						<div class="notice notice-warning inline">
							<p>Polja su popunjena naslijeđenim vrijednostima. Dok ne sačuvate, kategorija i dalje nasljeđuje.</p>
						</div>
					<?php endif; ?>
				<?php endif; ?>

				<p class="de-hl-none">
					<label>
						<input type="checkbox" name="none" value="1" class="de-hl-none__input" <?php checked( $is_none ); ?> />
						Ne prikazuj istaknute atribute (ni naslijeđene) u ovoj kategoriji
					</label>
				</p>

				<table class="widefat striped de-hl-table">
					<thead>
						<tr>
							<th class="de-hl-col-num">#</th>
							<th class="de-hl-col-move"><span class="screen-reader-text">Redoslijed</span></th>
							<th>Izvor</th>
							<th class="de-hl-col-icon">Ikona</th>
							<th>Labela (opciono)</th>
							<th>Rezervni tekst (opciono)</th>
						</tr>
					</thead>
					<tbody class="de-hl-rows">
						<?php for ( $i = 0; $i < $max; $i++ ) : ?>
							<?php
							door_expert_highlights_render_row(
								$i,
								isset( $rows[ $i ] ) ? $rows[ $i ] : array(),
								$grouped,
								$icons,
								$context
							);
							?>
						<?php endfor; ?>
					</tbody>
				</table>

				<p class="description">
					<strong>Rezervni tekst</strong> se prikazuje kad proizvod nema tu vrijednost.
					Uz izvor <em>Statičan tekst</em> on je jedini sadržaj reda, pa tu ide tvrdnja
					koja važi za cijelu kategoriju (npr. „Garancija 2 godine“).
				</p>

				<h3>Ikone u setu</h3>
				<div class="de-hl-iconset">
					<?php foreach ( $icons as $icon_key => $icon ) : ?>
						<span class="de-hl-iconset__item">
							<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
								<?php echo wp_kses( $icon['svg'], door_expert_svg_allowed_html() ); ?>
							</svg>
							<?php echo esc_html( $icon['label'] ); ?>
						</span>
					<?php endforeach; ?>
				</div>

				<?php submit_button( 'Sačuvaj' ); ?>
			</form>
		</div>
	</div>
	<?php
}
