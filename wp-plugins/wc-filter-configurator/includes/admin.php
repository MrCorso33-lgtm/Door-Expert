<?php
/**
 * Admin screen: Settings -> Filter Configurator.
 *
 * Three views, switched by ?wcfc_view=
 *   (none)   filters per category  — the main drag & drop editor
 *   global   filters shown on every archive
 *   groups   attribute grouping for the left-hand pool (admin cosmetics only)
 *
 * @package WC_Filter_Configurator
 */

defined( 'ABSPATH' ) || exit;

const WCFC_ADMIN_SLUG = 'wc-filter-configurator';

/**
 * Register the settings submenu.
 */
add_action( 'admin_menu', function () {
	add_submenu_page(
		'options-general.php',
		__( 'Filter Configurator', 'wc-filter-configurator' ),
		__( 'Filter Configurator', 'wc-filter-configurator' ),
		'manage_options',
		WCFC_ADMIN_SLUG,
		'wcfc_render_admin_page'
	);
} );

/**
 * Assets, only on our own screen.
 *
 * @param string $hook
 */
add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( 'settings_page_' . WCFC_ADMIN_SLUG !== $hook ) {
		return;
	}

	wp_enqueue_script(
		'wcfc-sortable',
		WCFC_URL . 'assets/vendor/Sortable.min.js',
		[],
		'1.15.2',
		true
	);

	wp_enqueue_script(
		'wcfc-admin',
		WCFC_URL . 'assets/admin.js',
		[ 'wcfc-sortable' ],
		(string) filemtime( WCFC_DIR . 'assets/admin.js' ),
		true
	);

	wp_enqueue_style(
		'wcfc-admin',
		WCFC_URL . 'assets/admin.css',
		[],
		(string) filemtime( WCFC_DIR . 'assets/admin.css' )
	);

	wp_localize_script( 'wcfc-admin', 'wcfcData', [
		'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
		'nonce'       => wp_create_nonce( 'wcfc_nonce' ),
		'groupsUrl'   => add_query_arg( 'wcfc_view', 'groups', wcfc_admin_url() ),
		'childCats'   => wcfc_get_context_category_tree(),
		'allAttrs'    => wcfc_get_all_attributes(),
		'lockedTypes' => array_keys( wcfc_special_attrs() ),
		'i18n'        => wcfc_admin_i18n(),
	] );
} );

/**
 * Base URL of the admin screen.
 *
 * @return string
 */
function wcfc_admin_url(): string {
	return admin_url( 'options-general.php?page=' . WCFC_ADMIN_SLUG );
}

/**
 * Strings handed to admin.js. Keys match the t() calls there.
 *
 * @return array<string, string>
 */
function wcfc_admin_i18n(): array {
	return [
		'categories'         => __( 'Categories', 'wc-filter-configurator' ),
		'clearAll'           => __( 'Clear all', 'wc-filter-configurator' ),
		'apply'              => __( 'Apply', 'wc-filter-configurator' ),
		'allCats'            => __( 'All cats', 'wc-filter-configurator' ),
		/* translators: %d: number of selected categories. */
		'nCats'              => __( '%d cats', 'wc-filter-configurator' ),
		'label'              => __( 'Label', 'wc-filter-configurator' ),
		'typeSwatch'         => __( 'Swatch', 'wc-filter-configurator' ),
		'typeCheckbox'       => __( 'Checkbox', 'wc-filter-configurator' ),
		'typeLocked'         => __( 'This filter has a fixed control type', 'wc-filter-configurator' ),
		'dragReorder'        => __( 'Drag to reorder', 'wc-filter-configurator' ),
		'collapsed'          => __( 'Collapsed', 'wc-filter-configurator' ),
		'collapsedHint'      => __( 'Collapsed on page load', 'wc-filter-configurator' ),
		'collapsedFrontHint' => __( 'Collapsed on the front end', 'wc-filter-configurator' ),
		'remove'             => __( 'Remove', 'wc-filter-configurator' ),
		'section'            => __( 'Section', 'wc-filter-configurator' ),
		'newSection'         => __( 'New section', 'wc-filter-configurator' ),
		'sectionName'        => __( 'Section name', 'wc-filter-configurator' ),
		'dragSection'        => __( 'Drag section', 'wc-filter-configurator' ),
		'expandCollapse'     => __( 'Expand / collapse', 'wc-filter-configurator' ),
		'deleteSection'      => __( 'Delete section', 'wc-filter-configurator' ),
		'group'              => __( 'Group', 'wc-filter-configurator' ),
		'groupName'          => __( 'Group name', 'wc-filter-configurator' ),
		'groupNamePrompt'    => __( 'Group name:', 'wc-filter-configurator' ),
		'dragGroup'          => __( 'Drag group', 'wc-filter-configurator' ),
		'deleteGroup'        => __( 'Delete group', 'wc-filter-configurator' ),
		'confirmResetGroups' => __( 'Delete all groups? The attributes go back to Ungrouped.', 'wc-filter-configurator' ),
		'saving'             => __( 'Saving…', 'wc-filter-configurator' ),
		'flushing'           => __( 'Flushing…', 'wc-filter-configurator' ),
		/* translators: %d: number of saved filters. */
		'savedConfig'        => __( 'Configuration saved (%d filters)', 'wc-filter-configurator' ),
		/* translators: %d: number of saved groups. */
		'savedGroups'        => __( 'Groups saved (%d)', 'wc-filter-configurator' ),
		'groupOrderSaved'    => __( 'Group order saved', 'wc-filter-configurator' ),
		/* translators: 1: group name, 2: number of attributes. */
		'groupSaved'         => __( '"%s" saved (%d attributes) — click to open Grouping', 'wc-filter-configurator' ),
		'noActiveFilters'    => __( 'No active filters to save', 'wc-filter-configurator' ),
		'saveFailed'         => __( 'Save failed', 'wc-filter-configurator' ),
		'cacheFlushed'       => __( 'Filter cache flushed', 'wc-filter-configurator' ),
		'cacheFlushFailed'   => __( 'Cache flush failed', 'wc-filter-configurator' ),
		/* translators: %s: error message. */
		'errorPrefix'        => __( 'Error: %s', 'wc-filter-configurator' ),
		'unknownError'       => __( 'unknown error', 'wc-filter-configurator' ),
	];
}

/**
 * One draggable chip in the left-hand pool.
 *
 * @param string $slug
 * @param string $label
 * @param bool   $disabled Already used in this context.
 * @return string
 */
function wcfc_render_chip( string $slug, string $label, bool $disabled = false ): string {
	$specials = wcfc_special_attrs();

	return sprintf(
		'<li class="wcfc-chip%1$s" data-attr="%2$s" data-default-label="%3$s" data-default-type="%4$s">
			<span class="wcfc-chip__label">%3$s</span>
			<span class="wcfc-chip__slug">(%2$s)</span>
		</li>',
		$disabled ? ' is-disabled' : '',
		esc_attr( $slug ),
		esc_attr( $label ),
		esc_attr( $specials[ $slug ] ?? 'checkbox' )
	);
}

/**
 * The attribute pool for one context: flat when no groups are defined,
 * accordion when they are.
 *
 * @param string $context_key
 * @param array  $available    slug => label, not yet used in this context.
 * @param array  $active_slugs Slugs already in the active list.
 * @param array  $all_attrs    Every attribute, slug => label.
 */
function wcfc_render_pool( string $context_key, array $available, array $active_slugs, array $all_attrs ) {
	$groups = get_option( WCFC_GROUPS_OPTION, [] );
	if ( ! is_array( $groups ) ) {
		$groups = [];
	}

	// Flat pool.
	if ( empty( $groups ) ) {
		echo '<ul class="wcfc-pool wcfc-pool--flat" id="pool-' . esc_attr( $context_key ) . '">';
		foreach ( $available as $slug => $label ) {
			echo wcfc_render_chip( (string) $slug, (string) $label ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside.
		}
		echo '</ul>';
		return;
	}

	// Grouped pool. Active attributes stay visible but disabled, so you can see
	// which group an already-used attribute belongs to.
	$active_flip = array_flip( $active_slugs );

	$attr_to_group = [];
	foreach ( $groups as $group ) {
		foreach ( (array) ( $group['attrs'] ?? [] ) as $attr ) {
			$attr_to_group[ $attr ] = $group['name'];
		}
	}

	$grouped = [];
	foreach ( $all_attrs as $slug => $label ) {
		$group_name                    = $attr_to_group[ $slug ] ?? '__ungrouped__';
		$grouped[ $group_name ][ $slug ] = $label;
	}

	echo '<ul class="wcfc-pool" id="pool-' . esc_attr( $context_key ) . '">';

	foreach ( $groups as $group ) {
		$name  = $group['name'];
		$attrs = $grouped[ $name ] ?? [];
		if ( empty( $attrs ) ) {
			continue;
		}

		$available_count = 0;
		foreach ( $attrs as $slug => $label ) {
			if ( ! isset( $active_flip[ $slug ] ) ) {
				$available_count++;
			}
		}
		?>
		<li class="wcfc-pool-group<?php echo 0 === $available_count ? ' is-empty' : ''; ?>"
			data-group-id="<?php echo esc_attr( $group['id'] ?? '' ); ?>"
			data-group-name="<?php echo esc_attr( $name ); ?>">
			<button type="button" class="wcfc-pool-group__hd">
				<span class="wcfc-pool-group__drag-handle" title="<?php esc_attr_e( 'Drag group', 'wc-filter-configurator' ); ?>">&#x2807;</span>
				<span class="wcfc-pool-group__arrow">&#x25B6;</span>
				<?php echo esc_html( $name ); ?>
				<span class="wcfc-pool-group__count"><?php echo (int) $available_count; ?></span>
			</button>
			<ul class="wcfc-pool-group__list">
				<?php
				foreach ( $attrs as $slug => $label ) {
					echo wcfc_render_chip( (string) $slug, (string) $label, isset( $active_flip[ $slug ] ) ); // phpcs:ignore WordPress.Security.EscapeOutput
				}
				?>
			</ul>
		</li>
		<?php
	}

	// Ungrouped bucket — available attributes only.
	$ungrouped = [];
	foreach ( $grouped['__ungrouped__'] ?? [] as $slug => $label ) {
		if ( ! isset( $active_flip[ $slug ] ) ) {
			$ungrouped[ $slug ] = $label;
		}
	}

	if ( ! empty( $ungrouped ) ) :
		?>
		<li class="wcfc-pool-group">
			<button type="button" class="wcfc-pool-group__hd">
				<span class="wcfc-pool-group__arrow">&#x25B6;</span>
				<?php esc_html_e( 'Ungrouped', 'wc-filter-configurator' ); ?>
				<span class="wcfc-pool-group__count"><?php echo count( $ungrouped ); ?></span>
			</button>
			<ul class="wcfc-pool-group__list">
				<?php
				foreach ( $ungrouped as $slug => $label ) {
					echo wcfc_render_chip( (string) $slug, (string) $label ); // phpcs:ignore WordPress.Security.EscapeOutput
				}
				?>
			</ul>
		</li>
		<?php
	endif;

	echo '</ul>';
}

/**
 * One row in the active list.
 *
 * @param array $filter
 * @param array $all_attrs
 */
function wcfc_render_active_item( array $filter, array $all_attrs ) {
	$attr      = $filter['attr'] ?? '';
	$specials  = wcfc_special_attrs();
	$is_locked = isset( $specials[ $attr ] );
	$type      = $is_locked
		? $specials[ $attr ]
		: ( in_array( $filter['type'] ?? '', wcfc_allowed_types(), true ) ? $filter['type'] : 'checkbox' );
	$cats      = isset( $filter['categories'] ) && is_array( $filter['categories'] ) ? $filter['categories'] : [];
	?>
	<li class="wcfc-item"
		data-attr="<?php echo esc_attr( $attr ); ?>"
		data-default-label="<?php echo esc_attr( $all_attrs[ $attr ] ?? $attr ); ?>"
		data-type="<?php echo esc_attr( $type ); ?>"
		data-categories="<?php echo esc_attr( wp_json_encode( $cats ) ); ?>">

		<span class="wcfc-item__handle" title="<?php esc_attr_e( 'Drag to reorder', 'wc-filter-configurator' ); ?>">&#x2807;</span>
		<span class="wcfc-item__attr"><?php echo esc_html( $attr ); ?></span>

		<input type="text" class="wcfc-item__label"
			value="<?php echo esc_attr( $filter['label'] ?? $attr ); ?>"
			placeholder="<?php esc_attr_e( 'Label', 'wc-filter-configurator' ); ?>">

		<?php if ( $is_locked ) : ?>
			<?php /* Special filters have one rendering; a <select> here would silently rewrite the stored type on save. */ ?>
			<span class="wcfc-item__type wcfc-item__type--locked"
				title="<?php esc_attr_e( 'This filter has a fixed control type', 'wc-filter-configurator' ); ?>">
				<?php echo esc_html( $type ); ?>
			</span>
		<?php else : ?>
			<select class="wcfc-item__type">
				<option value="swatch" <?php selected( $type, 'swatch' ); ?>><?php esc_html_e( 'Swatch', 'wc-filter-configurator' ); ?></option>
				<option value="checkbox" <?php selected( $type, 'checkbox' ); ?>><?php esc_html_e( 'Checkbox', 'wc-filter-configurator' ); ?></option>
			</select>
		<?php endif; ?>

		<label class="wcfc-item__collapsed" title="<?php esc_attr_e( 'Collapsed on page load', 'wc-filter-configurator' ); ?>">
			<input type="checkbox" class="wcfc-item__collapsed-cb" <?php checked( ! empty( $filter['collapsed'] ) ); ?>>
			<span><?php esc_html_e( 'Collapsed', 'wc-filter-configurator' ); ?></span>
		</label>

		<button type="button" class="wcfc-item__remove" title="<?php esc_attr_e( 'Remove', 'wc-filter-configurator' ); ?>">&#x2715;</button>
	</li>
	<?php
}

/**
 * The active list for one context, including sections.
 *
 * @param string $context_key
 * @param array  $filters
 * @param array  $all_attrs
 */
function wcfc_render_active_list( string $context_key, array $filters, array $all_attrs ) {
	echo '<ul class="wcfc-active" id="active-' . esc_attr( $context_key ) . '">';

	foreach ( $filters as $filter ) {
		if ( ( $filter['type'] ?? '' ) === 'section' ) {
			$section_id = 'sec-' . substr( md5( $context_key . ( $filter['label'] ?? '' ) . uniqid() ), 0, 8 );
			?>
			<li class="wcfc-section is-open" data-type="section">
				<div class="wcfc-section__hd">
					<span class="wcfc-section__drag" title="<?php esc_attr_e( 'Drag section', 'wc-filter-configurator' ); ?>">&#x2807;</span>
					<input type="text" class="wcfc-section__label"
						value="<?php echo esc_attr( $filter['label'] ?? '' ); ?>"
						placeholder="<?php esc_attr_e( 'Section name', 'wc-filter-configurator' ); ?>">
					<label class="wcfc-section__collapsed-wrap" title="<?php esc_attr_e( 'Collapsed on the front end', 'wc-filter-configurator' ); ?>">
						<input type="checkbox" class="wcfc-section__collapsed-cb" <?php checked( ! empty( $filter['collapsed'] ) ); ?>>
						<span><?php esc_html_e( 'Collapsed', 'wc-filter-configurator' ); ?></span>
					</label>
					<button type="button" class="wcfc-section__toggle" title="<?php esc_attr_e( 'Expand / collapse', 'wc-filter-configurator' ); ?>">&#x25B2;</button>
					<button type="button" class="wcfc-section__remove" title="<?php esc_attr_e( 'Delete section', 'wc-filter-configurator' ); ?>">&#x2715;</button>
				</div>
				<ul class="wcfc-section__list" id="<?php echo esc_attr( $section_id ); ?>">
					<?php
					foreach ( ( $filter['filters'] ?? [] ) as $sub ) {
						wcfc_render_active_item( $sub, $all_attrs );
					}
					?>
				</ul>
			</li>
			<?php
			continue;
		}

		wcfc_render_active_item( $filter, $all_attrs );
	}

	echo '</ul>';
}

/**
 * Render the settings page.
 */
function wcfc_render_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$contexts  = wcfc_get_contexts();
	$all_attrs = wcfc_get_all_attributes();

	$configs = get_option( WCFC_OPTION, [] );
	if ( ! is_array( $configs ) || empty( $configs ) ) {
		$configs = wcfc_default_configs();
	}

	$groups = get_option( WCFC_GROUPS_OPTION, [] );
	if ( ! is_array( $groups ) ) {
		$groups = [];
	}

	$view = isset( $_GET['wcfc_view'] ) ? sanitize_key( wp_unslash( $_GET['wcfc_view'] ) ) : 'filters';
	$tab  = isset( $_GET['wcfc_tab'] ) ? sanitize_key( wp_unslash( $_GET['wcfc_tab'] ) ) : (string) array_key_first( $contexts );
	if ( ! isset( $contexts[ $tab ] ) ) {
		$tab = (string) array_key_first( $contexts );
	}

	$base_url    = wcfc_admin_url();
	$filters_url = $base_url;
	$global_url  = add_query_arg( 'wcfc_view', 'global', $base_url );
	$groups_url  = add_query_arg( 'wcfc_view', 'groups', $base_url );
	?>
	<div class="wrap wcfc-wrap">
		<h1 class="wcfc-h1">
			<?php esc_html_e( 'Filter Configurator', 'wc-filter-configurator' ); ?>
			<button type="button" class="button wcfc-flush-cache" id="wcfcFlushCache"
				title="<?php esc_attr_e( 'Rebuild the cached category product lists. Use after a bulk import or a direct database change, when filters still show stale attributes.', 'wc-filter-configurator' ); ?>">
				&#x21bb; <?php esc_html_e( 'Flush filter cache', 'wc-filter-configurator' ); ?>
			</button>
		</h1>

		<nav class="wcfc-topnav">
			<a href="<?php echo esc_url( $filters_url ); ?>"
				class="wcfc-topnav__item <?php echo ! in_array( $view, [ 'groups', 'global' ], true ) ? 'is-active' : ''; ?>">
				<?php esc_html_e( 'Filters per category', 'wc-filter-configurator' ); ?>
			</a>
			<a href="<?php echo esc_url( $global_url ); ?>"
				class="wcfc-topnav__item <?php echo 'global' === $view ? 'is-active' : ''; ?>">
				<?php esc_html_e( 'Global filters', 'wc-filter-configurator' ); ?>
			</a>
			<a href="<?php echo esc_url( $groups_url ); ?>"
				class="wcfc-topnav__item <?php echo 'groups' === $view ? 'is-active' : ''; ?>">
				<?php esc_html_e( 'Attribute grouping', 'wc-filter-configurator' ); ?>
			</a>
		</nav>

		<?php if ( 'groups' === $view ) : ?>

			<div class="wcfc-groups-wrap">
				<p class="wcfc-desc">
					<?php
					printf(
						/* translators: %s: "does not affect the front end", emphasised. */
						esc_html__( 'Organise attributes into groups so the left-hand pool stays navigable. This %s — it only changes the admin view.', 'wc-filter-configurator' ),
						'<strong>' . esc_html__( 'does not affect the front end', 'wc-filter-configurator' ) . '</strong>'
					);
					?>
				</p>

				<div class="wcfc-groups-layout">
					<div class="wcfc-groups-ungrouped">
						<h3><?php esc_html_e( 'Ungrouped attributes', 'wc-filter-configurator' ); ?></h3>
						<p class="wcfc-col-hint"><?php esc_html_e( 'Drag into a group on the right', 'wc-filter-configurator' ); ?></p>
						<?php
						$in_groups = [];
						foreach ( $groups as $group ) {
							foreach ( (array) ( $group['attrs'] ?? [] ) as $attr ) {
								$in_groups[] = $attr;
							}
						}
						$ungrouped = array_diff_key( $all_attrs, array_flip( $in_groups ) );
						?>
						<ul class="wcfc-ungrouped-pool" id="wcfcUngroupedPool">
							<?php
							foreach ( $ungrouped as $slug => $label ) {
								echo wcfc_render_chip( (string) $slug, (string) $label ); // phpcs:ignore WordPress.Security.EscapeOutput
							}
							?>
						</ul>
					</div>

					<div class="wcfc-groups-editor">
						<div class="wcfc-groups-head">
							<h3><?php esc_html_e( 'Groups', 'wc-filter-configurator' ); ?></h3>
							<div style="display:flex;gap:8px;">
								<button type="button" class="button" id="wcfcAddGroup">+ <?php esc_html_e( 'Add group', 'wc-filter-configurator' ); ?></button>
								<button type="button" class="button wcfc-btn-danger" id="wcfcResetGroups"><?php esc_html_e( 'Delete all', 'wc-filter-configurator' ); ?></button>
								<button type="button" class="button button-primary" id="wcfcSaveGroups"><?php esc_html_e( 'Save groups', 'wc-filter-configurator' ); ?></button>
							</div>
						</div>
						<p class="wcfc-col-hint"><?php esc_html_e( 'Drag a group to reorder it. Drag an attribute between groups, or back to Ungrouped.', 'wc-filter-configurator' ); ?></p>

						<div class="wcfc-group-list" id="wcfcGroupList">
							<?php foreach ( $groups as $group ) : $gid = $group['id'] ?? uniqid( 'g' ); ?>
							<div class="wcfc-group" data-id="<?php echo esc_attr( $gid ); ?>">
								<div class="wcfc-group__hd">
									<span class="wcfc-group__drag" title="<?php esc_attr_e( 'Drag group', 'wc-filter-configurator' ); ?>">&#x2807;</span>
									<input type="text" class="wcfc-group__name"
										value="<?php echo esc_attr( $group['name'] ?? '' ); ?>"
										placeholder="<?php esc_attr_e( 'Group name', 'wc-filter-configurator' ); ?>">
									<button type="button" class="wcfc-group__delete" title="<?php esc_attr_e( 'Delete group', 'wc-filter-configurator' ); ?>">&#x2715;</button>
								</div>
								<ul class="wcfc-group__items" id="grp-<?php echo esc_attr( $gid ); ?>">
									<?php
									foreach ( (array) ( $group['attrs'] ?? [] ) as $attr ) {
										echo wcfc_render_chip( (string) $attr, (string) ( $all_attrs[ $attr ] ?? $attr ) ); // phpcs:ignore WordPress.Security.EscapeOutput
									}
									?>
								</ul>
							</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</div>

		<?php elseif ( 'global' === $view ) : ?>

			<?php
			$global_filters = $configs['global'] ?? [];
			$global_slugs   = array_filter( array_column( $global_filters, 'attr' ) );
			$global_avail   = array_diff_key( $all_attrs, array_flip( $global_slugs ) );
			?>
			<p class="wcfc-desc">
				<?php esc_html_e( 'Filters shown on every archive, whatever the category. They appear before the category-specific ones.', 'wc-filter-configurator' ); ?>
			</p>

			<div class="wcfc-layout">
				<div class="wcfc-pools-wrap">
					<div class="wcfc-left-header">
						<h3><?php esc_html_e( 'Available attributes', 'wc-filter-configurator' ); ?></h3>
						<p class="wcfc-col-hint"><?php esc_html_e( 'Drag into the right-hand column', 'wc-filter-configurator' ); ?></p>
					</div>
					<div class="wcfc-pool-panel is-active" data-cat="global">
						<ul class="wcfc-pool wcfc-pool--flat" id="pool-global">
							<?php
							foreach ( $global_avail as $slug => $label ) {
								echo wcfc_render_chip( (string) $slug, (string) $label ); // phpcs:ignore WordPress.Security.EscapeOutput
							}
							?>
						</ul>
					</div>
				</div>

				<div class="wcfc-right-sticky">
					<div class="wcfc-active-panel is-active" data-cat="global">
						<div class="wcfc-active-head">
							<div>
								<div class="wcfc-active-title"><?php esc_html_e( 'Active global filters', 'wc-filter-configurator' ); ?></div>
								<p class="wcfc-col-hint"><?php esc_html_e( 'Drag to reorder. Click ✕ to remove.', 'wc-filter-configurator' ); ?></p>
							</div>
							<div class="wcfc-active-btns">
								<button type="button" class="button wcfc-add-section" data-cat="global">
									+ <?php esc_html_e( 'Section', 'wc-filter-configurator' ); ?>
								</button>
								<button type="button" class="button button-primary wcfc-save" data-cat="global">
									<?php esc_html_e( 'Save global filters', 'wc-filter-configurator' ); ?>
								</button>
							</div>
						</div>
						<?php wcfc_render_active_list( 'global', $global_filters, $all_attrs ); ?>
					</div>
				</div>
			</div>

		<?php else : ?>

			<p class="wcfc-desc">
				<?php esc_html_e( 'Choose which filters appear on which category page. Drag an attribute from the left column into the right one to add it. Subcategories inherit their parent unless you configure them separately.', 'wc-filter-configurator' ); ?>
			</p>

			<div class="wcfc-layout">
				<div class="wcfc-pools-wrap">
					<div class="wcfc-left-header">
						<h3><?php esc_html_e( 'Available attributes', 'wc-filter-configurator' ); ?></h3>
						<p class="wcfc-col-hint"><?php esc_html_e( 'Drag into the right-hand column', 'wc-filter-configurator' ); ?></p>
					</div>
					<?php
					foreach ( $contexts as $key => $name ) :
						$active       = $configs[ $key ] ?? [];
						$active_slugs = array_filter( array_column( $active, 'attr' ) );
						$available    = array_diff_key( $all_attrs, array_flip( $active_slugs ) );
						?>
						<div class="wcfc-pool-panel <?php echo $key === $tab ? 'is-active' : ''; ?>" data-cat="<?php echo esc_attr( $key ); ?>">
							<?php wcfc_render_pool( (string) $key, $available, $active_slugs, $all_attrs ); ?>
						</div>
					<?php endforeach; ?>
				</div>

				<div class="wcfc-right-sticky">
					<nav class="wcfc-tabs">
						<?php foreach ( $contexts as $key => $name ) : ?>
							<a href="<?php echo esc_url( add_query_arg( 'wcfc_tab', $key, $filters_url ) ); ?>"
								class="wcfc-tab <?php echo $key === $tab ? 'is-active' : ''; ?>"
								data-cat="<?php echo esc_attr( $key ); ?>">
								<?php echo esc_html( $name ); ?>
							</a>
						<?php endforeach; ?>
					</nav>

					<?php foreach ( $contexts as $key => $name ) : ?>
						<div class="wcfc-active-panel <?php echo $key === $tab ? 'is-active' : ''; ?>" data-cat="<?php echo esc_attr( $key ); ?>">
							<div class="wcfc-active-head">
								<div>
									<div class="wcfc-active-title"><?php esc_html_e( 'Active filters', 'wc-filter-configurator' ); ?></div>
									<p class="wcfc-col-hint"><?php esc_html_e( 'Drag to reorder. Click ✕ to remove.', 'wc-filter-configurator' ); ?></p>
								</div>
								<div class="wcfc-active-btns">
									<button type="button" class="button wcfc-add-section" data-cat="<?php echo esc_attr( $key ); ?>">
										+ <?php esc_html_e( 'Section', 'wc-filter-configurator' ); ?>
									</button>
									<button type="button" class="button wcfc-save-as-group"
										data-cat="<?php echo esc_attr( $key ); ?>"
										data-catname="<?php echo esc_attr( $name ); ?>"
										title="<?php esc_attr_e( 'Save these active filters as an attribute group', 'wc-filter-configurator' ); ?>">
										&#x2193; <?php esc_html_e( 'Save as group', 'wc-filter-configurator' ); ?>
									</button>
									<button type="button" class="button button-primary wcfc-save" data-cat="<?php echo esc_attr( $key ); ?>">
										<?php esc_html_e( 'Save configuration', 'wc-filter-configurator' ); ?>
									</button>
								</div>
							</div>
							<?php wcfc_render_active_list( (string) $key, $configs[ $key ] ?? [], $all_attrs ); ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

		<?php endif; ?>

		<div class="wcfc-toast" id="wcfcToast"></div>
	</div>
	<?php
}
