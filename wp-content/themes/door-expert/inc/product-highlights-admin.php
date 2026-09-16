<?php
/**
 * Istaknuti atributi na PDP-u – admin ekran.
 *
 * Proizvodi → Istaknuti atributi. Namjerno pod "Proizvodi", a ne pod "Podešavanja":
 * klijent ovo traži dok razmišlja o proizvodima, ne dok podešava sajt.
 *
 * Lijevo je spisak konteksta (Podrazumijevano + stablo kategorija), desno forma
 * za izabrani kontekst. Ne ispisujemo sve kategorije odjednom jer bi to bilo
 * 30+ kategorija × 4 reda × 2 padajuća menija na jednoj stranici.
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
 * Globalni atributi kao opcije padajućeg menija.
 *
 * Samo globalni (`pa_*`): lokalni atributi postoje po proizvodu i ne mogu se
 * ponuditi kao zajednički izbor za cijelu kategoriju.
 *
 * @return array<string,string> slug => labela.
 */
function door_expert_highlights_attribute_options() {
	$options = array();

	if ( ! function_exists( 'wc_get_attribute_taxonomies' ) ) {
		return $options;
	}

	foreach ( wc_get_attribute_taxonomies() as $tax ) {
		$slug             = wc_attribute_taxonomy_name( $tax->attribute_name );
		$options[ $slug ] = $tax->attribute_label ? $tax->attribute_label : $tax->attribute_name;
	}

	return $options;
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
 * Prima snimanje forme.
 */
function door_expert_highlights_save() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html( 'Nemate ovlašćenje za ovu radnju.' ) );
	}

	check_admin_referer( 'door_expert_highlights_save' );

	$context = isset( $_POST['context'] ) ? sanitize_key( wp_unslash( $_POST['context'] ) ) : 'default';
	$rows    = isset( $_POST['rows'] ) ? wp_unslash( $_POST['rows'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitizuje door_expert_highlights_sanitize_rows().

	$all   = door_expert_highlights_all();
	$clean = door_expert_highlights_sanitize_rows( $rows );

	if ( empty( $clean ) ) {
		unset( $all[ $context ] ); // Prazno = vrati na nasljeđivanje od pretka.
	} else {
		$all[ $context ] = $clean;
	}

	update_option( DOOR_EXPERT_HIGHLIGHTS_OPTION, $all );

	wp_safe_redirect(
		add_query_arg(
			array(
				'post_type' => 'product',
				'page'      => 'door-expert-highlights',
				'context'   => $context,
				'saved'     => '1',
			),
			admin_url( 'edit.php' )
		)
	);
	exit;
}
add_action( 'admin_post_door_expert_highlights_save', 'door_expert_highlights_save' );

/**
 * Ispisuje ekran.
 */
function door_expert_highlights_screen() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- samo izbor konteksta za prikaz, bez izmjene stanja.
	$context = isset( $_GET['context'] ) ? sanitize_key( wp_unslash( $_GET['context'] ) ) : 'default';

	$contexts   = door_expert_highlights_contexts();
	$attributes = door_expert_highlights_attribute_options();
	$icons      = door_expert_highlight_icons();
	$all        = door_expert_highlights_all();
	$rows       = door_expert_highlights_rows( $context );

	$current_name = 'Podrazumijevano';
	foreach ( $contexts as $item ) {
		if ( $item['key'] === $context ) {
			$current_name = $item['name'];
			break;
		}
	}
	?>
	<div class="wrap door-expert-highlights">
		<h1>Istaknuti atributi na stranici proizvoda</h1>

		<?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- samo prikaz poruke poslije redirekcije. ?>
		<?php if ( isset( $_GET['saved'] ) ) : ?>
			<div class="notice notice-success is-dismissible"><p>Sačuvano.</p></div>
		<?php endif; ?>

		<p class="description" style="max-width:820px;">
			Bira se <strong>koji</strong> postojeći atributi se ističu, a ne njihove vrijednosti –
			vrijednosti se čitaju sa samog proizvoda. Prazan izbor u redu briše taj red.
			Kategorija bez ijednog reda nasljeđuje podešavanje od nadređene kategorije,
			a ako ni ona nema, koristi se Podrazumijevano. Najviše
			<?php echo (int) DOOR_EXPERT_HIGHLIGHTS_MAX; ?> stavke.
		</p>

		<?php if ( empty( $attributes ) ) : ?>
			<div class="notice notice-warning">
				<p>Nema nijednog globalnog atributa. Napravite ih u <em>Proizvodi → Atributi</em>.</p>
			</div>
		<?php endif; ?>

		<div style="display:flex; gap:28px; align-items:flex-start; margin-top:18px; flex-wrap:wrap;">

			<div style="flex:0 0 260px; background:#fff; border:1px solid #c3c4c7; padding:10px 0;">
				<?php foreach ( $contexts as $item ) : ?>
					<?php
					$is_current  = $item['key'] === $context;
					$has_own     = ! empty( $all[ $item['key'] ] );
					$item_url    = add_query_arg(
						array(
							'post_type' => 'product',
							'page'      => 'door-expert-highlights',
							'context'   => $item['key'],
						),
						admin_url( 'edit.php' )
					);
					$item_indent = 12 + ( (int) $item['depth'] * 14 );
					?>
					<a href="<?php echo esc_url( $item_url ); ?>"
						style="display:block; padding:6px 12px 6px <?php echo (int) $item_indent; ?>px; text-decoration:none; <?php echo $is_current ? 'background:#f0f0f1; font-weight:600;' : ''; ?>">
						<?php echo esc_html( $item['name'] ); ?>
						<?php if ( $has_own ) : ?>
							<span title="Ima svoje podešavanje" style="color:#2271b1;">&bull;</span>
						<?php endif; ?>
					</a>
				<?php endforeach; ?>
			</div>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="flex:1 1 520px; background:#fff; border:1px solid #c3c4c7; padding:18px 20px;">
				<?php wp_nonce_field( 'door_expert_highlights_save' ); ?>
				<input type="hidden" name="action" value="door_expert_highlights_save" />
				<input type="hidden" name="context" value="<?php echo esc_attr( $context ); ?>" />

				<h2 style="margin-top:0;"><?php echo esc_html( $current_name ); ?></h2>

				<table class="widefat striped">
					<thead>
						<tr>
							<th style="width:34px;">#</th>
							<th>Atribut</th>
							<th>Ikona</th>
							<th>Labela (opciono)</th>
						</tr>
					</thead>
					<tbody>
						<?php for ( $i = 0; $i < DOOR_EXPERT_HIGHLIGHTS_MAX; $i++ ) : ?>
							<?php
							$row       = isset( $rows[ $i ] ) ? $rows[ $i ] : array();
							$row_attr  = isset( $row['attr'] ) ? $row['attr'] : '';
							$row_icon  = isset( $row['icon'] ) ? $row['icon'] : '';
							$row_label = isset( $row['label'] ) ? $row['label'] : '';
							?>
							<tr>
								<td><?php echo (int) ( $i + 1 ); ?></td>
								<td>
									<select name="rows[<?php echo (int) $i; ?>][attr]" style="width:100%;">
										<option value="">— prazno —</option>
										<?php foreach ( $attributes as $slug => $label ) : ?>
											<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $row_attr, $slug ); ?>>
												<?php echo esc_html( $label ); ?>
											</option>
										<?php endforeach; ?>
									</select>
								</td>
								<td>
									<select name="rows[<?php echo (int) $i; ?>][icon]" style="width:100%;">
										<option value="">— bez ikone —</option>
										<?php foreach ( $icons as $icon_key => $icon ) : ?>
											<option value="<?php echo esc_attr( $icon_key ); ?>" <?php selected( $row_icon, $icon_key ); ?>>
												<?php echo esc_html( $icon['label'] ); ?>
											</option>
										<?php endforeach; ?>
									</select>
								</td>
								<td>
									<input type="text" name="rows[<?php echo (int) $i; ?>][label]" value="<?php echo esc_attr( $row_label ); ?>" style="width:100%;" placeholder="<?php echo esc_attr( 'podrazumijevano: naziv atributa' ); ?>" />
								</td>
							</tr>
						<?php endfor; ?>
					</tbody>
				</table>

				<h3>Ikone u setu</h3>
				<div style="display:flex; flex-wrap:wrap; gap:14px; margin-bottom:16px;">
					<?php foreach ( $icons as $icon_key => $icon ) : ?>
						<span style="display:inline-flex; flex-direction:column; align-items:center; width:92px; text-align:center; font-size:11px; color:#50575e;">
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
