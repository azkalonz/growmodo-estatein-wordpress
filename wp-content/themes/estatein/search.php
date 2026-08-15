<?php
/**
 * Search results.
 *
 * @package Estatein
 */

get_header();
get_template_part(
	'template-parts/pages/posts',
	null,
	array(
		/* translators: %s: Search query. */
		'title'       => sprintf( __( 'Search results for “%s”', 'estatein' ), get_search_query() ),
		'description' => __( 'Browse the matching Estatein content below or refine your search.', 'estatein' ),
		'search'      => true,
	)
);
get_footer();
