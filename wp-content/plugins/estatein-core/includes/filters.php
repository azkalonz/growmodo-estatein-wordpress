<?php
/**
 * Property archive filtering.
 *
 * @package EstateinCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Keep Estatein's curated property order unless a caller requests another sort.
 *
 * @param WP_Query $query Query instance.
 * @return void
 */
function estatein_core_default_property_order( $query ) {
	if ( is_admin() || 'estatein_property' !== $query->get( 'post_type' ) || $query->get( 'orderby' ) ) {
		return;
	}

	$query->set(
		'orderby',
		array(
			'menu_order' => 'ASC',
			'date'       => 'DESC',
		)
	);
}
add_action( 'pre_get_posts', 'estatein_core_default_property_order', 5 );

/**
 * Read and sanitize supported property filter parameters.
 *
 * @return array<string, string|float|int>
 */
function estatein_core_get_property_filters() {
	$raw          = wp_unslash( $_GET ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public, read-only archive filters.
	$paged        = get_query_var( 'paged' );
	$current_page = max( 1, absint( $paged ? $paged : get_query_var( 'page' ) ) );

	$filters = array(
		'keyword'                => isset( $raw['keyword'] ) && ! is_array( $raw['keyword'] ) ? sanitize_text_field( $raw['keyword'] ) : '',
		'estatein_location'      => isset( $raw['estatein_location'] ) && ! is_array( $raw['estatein_location'] ) ? sanitize_title( $raw['estatein_location'] ) : '',
		'estatein_property_type' => isset( $raw['estatein_property_type'] ) && ! is_array( $raw['estatein_property_type'] ) ? sanitize_title( $raw['estatein_property_type'] ) : '',
		'price_min'              => isset( $raw['price_min'] ) && is_numeric( $raw['price_min'] ) ? max( 0, (float) $raw['price_min'] ) : '',
		'price_max'              => isset( $raw['price_max'] ) && is_numeric( $raw['price_max'] ) ? max( 0, (float) $raw['price_max'] ) : '',
		'area_min'               => isset( $raw['area_min'] ) && is_numeric( $raw['area_min'] ) ? max( 0, (float) $raw['area_min'] ) : '',
		'area_max'               => isset( $raw['area_max'] ) && is_numeric( $raw['area_max'] ) ? max( 0, (float) $raw['area_max'] ) : '',
		'paged'                  => isset( $raw['paged'] ) && ! is_array( $raw['paged'] ) ? max( 1, absint( $raw['paged'] ) ) : $current_page,
	);

	if ( '' !== $filters['price_min'] && '' !== $filters['price_max'] && $filters['price_min'] > $filters['price_max'] ) {
		$temp                 = $filters['price_min'];
		$filters['price_min'] = $filters['price_max'];
		$filters['price_max'] = $temp;
	}

	if ( '' !== $filters['area_min'] && '' !== $filters['area_max'] && $filters['area_min'] > $filters['area_max'] ) {
		$temp                = $filters['area_min'];
		$filters['area_min'] = $filters['area_max'];
		$filters['area_max'] = $temp;
	}

	return $filters;
}

/**
 * Apply validated property filters to the public archive Loop.
 *
 * @param WP_Query $query Query instance.
 * @return void
 */
function estatein_core_filter_property_archive( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive( 'estatein_property' ) ) {
		return;
	}

	$filters = estatein_core_get_property_filters();
	$query->set( 'posts_per_page', 6 );
	$query->set( 'paged', $filters['paged'] );

	if ( '' !== $filters['keyword'] ) {
		$query->set( 's', $filters['keyword'] );
	}

	$tax_query = array();
	if ( '' !== $filters['estatein_location'] ) {
		$tax_query[] = array(
			'taxonomy' => 'estatein_location',
			'field'    => 'slug',
			'terms'    => $filters['estatein_location'],
		);
	}
	if ( '' !== $filters['estatein_property_type'] ) {
		$tax_query[] = array(
			'taxonomy' => 'estatein_property_type',
			'field'    => 'slug',
			'terms'    => $filters['estatein_property_type'],
		);
	}
	if ( $tax_query ) {
		$query->set( 'tax_query', $tax_query );
	}

	$meta_query = array();
	if ( '' !== $filters['price_min'] ) {
		$meta_query[] = array(
			'key'     => 'estatein_price',
			'value'   => $filters['price_min'],
			'compare' => '>=',
			'type'    => 'NUMERIC',
		);
	}
	if ( '' !== $filters['price_max'] ) {
		$meta_query[] = array(
			'key'     => 'estatein_price',
			'value'   => $filters['price_max'],
			'compare' => '<=',
			'type'    => 'NUMERIC',
		);
	}
	if ( '' !== $filters['area_min'] ) {
		$meta_query[] = array(
			'key'     => 'estatein_area',
			'value'   => $filters['area_min'],
			'compare' => '>=',
			'type'    => 'NUMERIC',
		);
	}
	if ( '' !== $filters['area_max'] ) {
		$meta_query[] = array(
			'key'     => 'estatein_area',
			'value'   => $filters['area_max'],
			'compare' => '<=',
			'type'    => 'NUMERIC',
		);
	}
	if ( $meta_query ) {
		$query->set( 'meta_query', $meta_query );
	}
}
add_action( 'pre_get_posts', 'estatein_core_filter_property_archive' );

/**
 * Return active filters suitable for pagination links.
 *
 * @return array<string, string|float>
 */
function estatein_core_property_filter_query_args() {
	$filters = estatein_core_get_property_filters();
	unset( $filters['paged'] );

	return array_filter(
		$filters,
		static function ( $value ) {
			return '' !== $value;
		}
	);
}
