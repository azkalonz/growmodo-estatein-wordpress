<?php
/**
 * Posts index.
 *
 * @package Estatein
 */

get_header();
get_template_part(
	'template-parts/pages/posts',
	null,
	array(
		'title'       => get_option( 'page_for_posts' ) ? get_the_title( get_option( 'page_for_posts' ) ) : __( 'Latest Insights', 'estatein' ),
		'description' => __( 'News, market perspectives, and practical real-estate guidance from Estatein.', 'estatein' ),
	)
);
get_footer();
