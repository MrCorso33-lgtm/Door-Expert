<?php
/**
 * Admin AJAX endpoints. All four require the wcfc_nonce and manage_options.
 *
 * @package WC_Filter_Configurator
 */

defined( 'ABSPATH' ) || exit;

/**
 * Shared guard: valid nonce + sufficient capability, or bail.
 */
function wcfc_ajax_guard() {
	check_ajax_referer( 'wcfc_nonce', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( __( 'Insufficient permissions', 'wc-filter-configurator' ), 403 );
	}
}

/**
 * Save one context's filter list.
 */
add_action( 'wp_ajax_wcfc_save_config', function () {
	wcfc_ajax_guard();

	$context = sanitize_key( wp_unslash( $_POST['cat'] ?? '' ) );
	if ( '' === $context ) {
		wp_send_json_error( __( 'Missing context', 'wc-filter-configurator' ) );
	}

	$raw = wp_unslash( $_POST['filters_json'] ?? '' );
	if ( '' === $raw ) {
		wp_send_json_error( __( 'Missing payload', 'wc-filter-configurator' ) );
	}

	$filters = json_decode( $raw, true );
	if ( ! is_array( $filters ) ) {
		wp_send_json_error( __( 'Malformed JSON', 'wc-filter-configurator' ) );
	}

	$clean = wcfc_sanitize_filters( $filters );

	$configs = get_option( WCFC_OPTION, [] );
	if ( ! is_array( $configs ) ) {
		$configs = [];
	}
	$configs[ $context ] = $clean;

	update_option( WCFC_OPTION, $configs, false );

	wp_send_json_success( [
		'message' => __( 'Configuration saved', 'wc-filter-configurator' ),
		'count'   => count( $clean ),
	] );
} );

/**
 * Replace the whole attribute grouping.
 */
add_action( 'wp_ajax_wcfc_save_groups', function () {
	wcfc_ajax_guard();

	$raw = wp_unslash( $_POST['groups'] ?? [] );
	if ( ! is_array( $raw ) ) {
		wp_send_json_error( __( 'Malformed payload', 'wc-filter-configurator' ) );
	}

	$clean = [];
	foreach ( $raw as $group ) {
		if ( ! is_array( $group ) ) {
			continue;
		}
		$name = sanitize_text_field( $group['name'] ?? '' );
		if ( '' === trim( $name ) ) {
			continue;
		}
		$clean[] = [
			'id'    => sanitize_key( $group['id'] ?? uniqid( 'g' ) ),
			'name'  => $name,
			'attrs' => array_values( array_filter( array_map( 'sanitize_key', (array) ( $group['attrs'] ?? [] ) ) ) ),
		];
	}

	update_option( WCFC_GROUPS_OPTION, $clean, false );

	wp_send_json_success( [
		'message' => __( 'Groups saved', 'wc-filter-configurator' ),
		'count'   => count( $clean ),
	] );
} );

/**
 * Add (or update, by name) a single attribute group.
 */
add_action( 'wp_ajax_wcfc_add_group', function () {
	wcfc_ajax_guard();

	$name = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
	if ( '' === trim( $name ) ) {
		wp_send_json_error( __( 'A group name is required', 'wc-filter-configurator' ) );
	}

	$attrs = wp_unslash( $_POST['attrs'] ?? [] );
	$attrs = array_values( array_filter( array_map( 'sanitize_key', (array) $attrs ) ) );

	$groups = get_option( WCFC_GROUPS_OPTION, [] );
	if ( ! is_array( $groups ) ) {
		$groups = [];
	}

	// Same name means update, not a duplicate.
	$updated = false;
	foreach ( $groups as &$group ) {
		if ( strtolower( trim( $group['name'] ?? '' ) ) === strtolower( trim( $name ) ) ) {
			$group['attrs'] = $attrs;
			$updated        = true;
			break;
		}
	}
	unset( $group );

	if ( ! $updated ) {
		$groups[] = [
			'id'    => 'g' . time(),
			'name'  => $name,
			'attrs' => $attrs,
		];
	}

	update_option( WCFC_GROUPS_OPTION, $groups, false );

	wp_send_json_success( [
		'count'        => count( $attrs ),
		'total_groups' => count( $groups ),
		'updated'      => $updated,
	] );
} );

/**
 * Flush the category/facet caches.
 *
 * Needed when category assignment changes outside WordPress (a direct SQL
 * import, for instance), which never fires the invalidation hooks.
 */
add_action( 'wp_ajax_wcfc_flush_cache', function () {
	wcfc_ajax_guard();
	wcfc_bump_cache_version();
	wp_send_json_success( [ 'message' => __( 'Filter cache flushed', 'wc-filter-configurator' ) ] );
} );
