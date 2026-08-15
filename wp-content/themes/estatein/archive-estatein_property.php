<?php
/**
 * Property archive.
 *
 * @package Estatein
 */

get_header();
get_template_part( 'template-parts/pages/properties', null, array( 'archive' => true ) );
get_footer();
