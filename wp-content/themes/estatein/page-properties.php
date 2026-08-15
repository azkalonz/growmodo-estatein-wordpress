<?php
/**
 * Properties page fallback when the CPT archive rewrite is unavailable.
 *
 * @package Estatein
 */

get_header();
get_template_part( 'template-parts/pages/properties', null, array( 'archive' => false ) );
get_footer();
