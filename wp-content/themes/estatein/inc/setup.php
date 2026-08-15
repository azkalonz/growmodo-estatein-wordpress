<?php
/**
 * Theme setup and WordPress integrations.
 *
 * @package Estatein
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register theme support and menus.
 */
function estatein_setup() {
	load_theme_textdomain( 'estatein', get_template_directory() . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 48,
			'width'       => 160,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'script',
			'style',
			'navigation-widgets',
		)
	);

	register_nav_menus(
		array(
			'primary'           => __( 'Primary navigation', 'estatein' ),
			'footer_home'       => __( 'Footer: Home', 'estatein' ),
			'footer_about'      => __( 'Footer: About Us', 'estatein' ),
			'footer_properties' => __( 'Footer: Properties', 'estatein' ),
			'footer_services'   => __( 'Footer: Services', 'estatein' ),
			'footer_contact'    => __( 'Footer: Contact Us', 'estatein' ),
		)
	);

	add_image_size( 'estatein-property-card', 768, 596, true );
	add_image_size( 'estatein-gallery-large', 1280, 860, true );
	add_image_size( 'estatein-team', 600, 512, true );
	add_editor_style( 'assets/css/main.css' );
}
add_action( 'after_setup_theme', 'estatein_setup' );

/**
 * Set the content width used by embeds and images.
 */
function estatein_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'estatein_content_width', 1596 );
}
add_action( 'after_setup_theme', 'estatein_content_width', 0 );

/**
 * Add useful body classes without exposing implementation details in templates.
 *
 * @param string[] $classes Existing classes.
 * @return string[]
 */
function estatein_body_classes( $classes ) {
	$classes[] = 'estatein-site';

	if ( is_singular( 'estatein_property' ) ) {
		$classes[] = 'estatein-property-detail';
	}

	return $classes;
}
add_filter( 'body_class', 'estatein_body_classes' );

/**
 * Use a compact excerpt length for cards.
 *
 * @param int $length Current excerpt length.
 * @return int
 */
function estatein_excerpt_length( $length ) {
	if ( is_admin() ) {
		return $length;
	}

	return 24;
}
add_filter( 'excerpt_length', 'estatein_excerpt_length' );
