<?php
/**
 * Main fallback template.
 *
 * @package Estatein
 */

get_header();
get_template_part(
	'template-parts/pages/posts',
	null,
	array(
		'title'       => __( 'Latest Insights', 'estatein' ),
		'description' => __( 'News, market perspectives, and practical real-estate guidance from Estatein.', 'estatein' ),
	)
);
get_footer();
