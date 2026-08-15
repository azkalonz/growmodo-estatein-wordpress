<?php
/**
 * Generic archive.
 *
 * @package Estatein
 */

get_header();
get_template_part(
	'template-parts/pages/posts',
	null,
	array(
		'title'       => wp_strip_all_tags( get_the_archive_title() ),
		'description' => wp_strip_all_tags( get_the_archive_description() ),
	)
);
get_footer();
