<?php
/**
 * Front-end assets.
 *
 * @package Estatein
 */

defined( 'ABSPATH' ) || exit;

/**
 * Return a cache-safe asset version.
 *
 * @param string $relative_path Path relative to the theme root.
 * @return string
 */
function estatein_asset_version( $relative_path ) {
	$path = get_template_directory() . '/' . ltrim( $relative_path, '/' );

	return file_exists( $path ) ? (string) filemtime( $path ) : wp_get_theme()->get( 'Version' );
}

/**
 * Enqueue the design system and interaction module.
 */
function estatein_enqueue_assets() {
	wp_enqueue_style( 'estatein-style', get_stylesheet_uri(), array(), estatein_asset_version( 'style.css' ) );
	wp_enqueue_style(
		'estatein-main',
		get_template_directory_uri() . '/assets/css/main.css',
		array( 'estatein-style' ),
		estatein_asset_version( 'assets/css/main.css' )
	);

	$script_uri = get_template_directory_uri() . '/assets/js/main.js';
	$version    = estatein_asset_version( 'assets/js/main.js' );

	if ( function_exists( 'wp_enqueue_script_module' ) ) {
		wp_enqueue_script_module( 'estatein-main', $script_uri, array(), $version );
	} else {
		wp_enqueue_script( 'estatein-main', $script_uri, array(), $version, true );
	}
}
add_action( 'wp_enqueue_scripts', 'estatein_enqueue_assets' );

/**
 * Preload only the above-the-fold home photograph, selecting the matching crop.
 */
function estatein_preload_home_hero() {
	if ( ! is_front_page() ) {
		return;
	}
	?>
	<link rel="preload" as="image" href="<?php echo esc_url( estatein_asset_uri( 'images/hero/home-mobile.webp' ) ); ?>" media="(max-width: 767px)" fetchpriority="high">
	<link rel="preload" as="image" href="<?php echo esc_url( estatein_asset_uri( 'images/hero/home-desktop.webp' ) ); ?>" media="(min-width: 768px)" fetchpriority="high">
	<?php
}
add_action( 'wp_head', 'estatein_preload_home_hero', 2 );

/**
 * Mark the compatibility script as a module on older supported WordPress versions.
 *
 * @param string $tag    Script tag.
 * @param string $handle Script handle.
 * @return string
 */
function estatein_module_script_tag( $tag, $handle ) {
	if ( 'estatein-main' !== $handle || false !== strpos( $tag, 'type=' ) ) {
		return $tag;
	}

	return str_replace( '<script ', '<script type="module" ', $tag );
}
add_filter( 'script_loader_tag', 'estatein_module_script_tag', 10, 2 );
