<?php
/**
 * Estatein Core uninstall routine.
 *
 * Content is retained by default. Define ESTATEIN_CORE_DELETE_DATA as true in
 * wp-config.php before uninstalling only when intentional permanent removal is
 * required.
 *
 * @package EstateinCore
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

if ( ! defined( 'ESTATEIN_CORE_DELETE_DATA' ) || true !== ESTATEIN_CORE_DELETE_DATA ) {
	return;
}

$post_ids = get_posts(
	array(
		'post_type'      => array( 'estatein_property', 'estatein_team_member', 'estatein_inquiry' ),
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'fields'         => 'ids',
	)
);

foreach ( $post_ids as $estatein_post_id ) {
	wp_delete_post( $estatein_post_id, true );
}

foreach ( array( 'estatein_property_type', 'estatein_location' ) as $estatein_taxonomy ) {
	$term_ids = get_terms(
		array(
			'taxonomy'   => $estatein_taxonomy,
			'hide_empty' => false,
			'fields'     => 'ids',
		)
	);
	if ( is_wp_error( $term_ids ) ) {
		continue;
	}
	foreach ( $term_ids as $term_id ) {
		wp_delete_term( $term_id, $estatein_taxonomy );
	}
}
