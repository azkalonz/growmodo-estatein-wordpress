<?php
/**
 * Lightweight metadata and property structured data.
 *
 * @package EstateinCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Determine whether a dedicated SEO plugin already owns social metadata.
 *
 * @return bool
 */
function estatein_core_has_seo_plugin() {
	return defined( 'WPSEO_VERSION' )
		|| defined( 'RANK_MATH_VERSION' )
		|| defined( 'AIOSEO_VERSION' )
		|| defined( 'SEOPRESS_VERSION' )
		|| defined( 'THE_SEO_FRAMEWORK_VERSION' )
		|| class_exists( 'WPSEO_Frontend' );
}

/**
 * Create the current page description from native content.
 *
 * @return string
 */
function estatein_core_meta_description() {
	$description = '';

	if ( is_singular() ) {
		$post_id     = get_queried_object_id();
		$description = get_the_excerpt( $post_id );
		if ( '' === trim( $description ) ) {
			$description = get_post_field( 'post_content', $post_id );
		}
	} elseif ( is_post_type_archive( 'estatein_property' ) ) {
		$description = __( 'Explore Estatein properties by location, type, price, and area, then connect with our real-estate team.', 'estatein-core' );
	} elseif ( is_front_page() ) {
		$description = get_bloginfo( 'description' );
	} elseif ( is_archive() ) {
		$description = get_the_archive_description();
	}

	$description = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( strip_shortcodes( (string) $description ) ) ) );
	if ( function_exists( 'mb_substr' ) ) {
		return mb_substr( $description, 0, 160 );
	}

	return substr( $description, 0, 160 );
}

/**
 * Resolve the canonical current URL without outputting an extra canonical tag.
 *
 * @return string
 */
function estatein_core_current_url() {
	if ( is_singular() ) {
		return get_permalink( get_queried_object_id() );
	}
	if ( is_post_type_archive( 'estatein_property' ) ) {
		return get_post_type_archive_link( 'estatein_property' );
	}
	if ( is_front_page() ) {
		return home_url( '/' );
	}

	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
	return home_url( $request_uri );
}

/**
 * Output description and Open Graph metadata when no SEO plugin owns it.
 *
 * Core's rel=canonical and XML sitemap output are intentionally left untouched.
 *
 * @return void
 */
function estatein_core_output_meta_tags() {
	if ( is_admin() || is_feed() || estatein_core_has_seo_plugin() ) {
		return;
	}

	$description = estatein_core_meta_description();
	$title       = wp_get_document_title();
	$url         = estatein_core_current_url();
	$image       = '';

	if ( is_singular() && has_post_thumbnail( get_queried_object_id() ) ) {
		$image = get_the_post_thumbnail_url( get_queried_object_id(), 'full' );
	}
	if ( ! $image ) {
		$image = get_site_icon_url( 512 );
	}

	if ( $description ) {
		printf( "\n<meta name=\"description\" content=\"%s\">", esc_attr( $description ) );
		printf( "\n<meta property=\"og:description\" content=\"%s\">", esc_attr( $description ) );
	}
	printf( "\n<meta property=\"og:title\" content=\"%s\">", esc_attr( $title ) );
	printf( "\n<meta property=\"og:type\" content=\"%s\">", esc_attr( is_singular() ? 'article' : 'website' ) );
	printf( "\n<meta property=\"og:url\" content=\"%s\">", esc_url( $url ) );
	printf( "\n<meta property=\"og:site_name\" content=\"%s\">", esc_attr( get_bloginfo( 'name' ) ) );
	if ( $image ) {
		printf( "\n<meta property=\"og:image\" content=\"%s\">", esc_url( $image ) );
	}
	echo "\n";
}
add_action( 'wp_head', 'estatein_core_output_meta_tags', 4 );

/**
 * Output JSON-LD for property detail pages.
 *
 * @return void
 */
function estatein_core_output_property_schema() {
	if ( ! is_singular( 'estatein_property' ) ) {
		return;
	}

	$post_id     = get_queried_object_id();
	$price       = (float) estatein_core_get_property_field( $post_id, 'price', 0 );
	$address     = estatein_core_get_property_field( $post_id, 'address', '' );
	$location    = wp_get_post_terms( $post_id, 'estatein_location', array( 'fields' => 'names' ) );
	$gallery     = estatein_core_get_property_gallery( $post_id );
	$description = get_the_excerpt( $post_id );
	if ( ! $description ) {
		$description = wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ), 35 );
	}

	$schema = array(
		'@context'               => 'https://schema.org',
		'@type'                  => 'RealEstateListing',
		'name'                   => get_the_title( $post_id ),
		'url'                    => get_permalink( $post_id ),
		'description'            => $description,
		'datePosted'             => get_the_date( DATE_W3C, $post_id ),
		'numberOfRooms'          => (int) estatein_core_get_property_field( $post_id, 'bedrooms', 0 ),
		'numberOfBathroomsTotal' => (int) estatein_core_get_property_field( $post_id, 'bathrooms', 0 ),
		'yearBuilt'              => (int) estatein_core_get_property_field( $post_id, 'year_built', 0 ),
		'floorSize'              => array(
			'@type'    => 'QuantitativeValue',
			'value'    => (float) estatein_core_get_property_field( $post_id, 'area', 0 ),
			'unitCode' => 'FTK',
		),
		'address'                => array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => $address,
			'addressLocality' => ! is_wp_error( $location ) && $location ? $location[0] : '',
		),
		'offers'                 => array(
			'@type'         => 'Offer',
			'price'         => $price,
			'priceCurrency' => 'USD',
			'availability'  => 'https://schema.org/InStock',
			'url'           => get_permalink( $post_id ),
		),
	);

	if ( $gallery ) {
		$schema['image'] = wp_list_pluck( $gallery, 'url' );
	}

	$schema = array_filter(
		$schema,
		static function ( $value ) {
			return '' !== $value && null !== $value;
		}
	);

	printf(
		"\n<script type=\"application/ld+json\">%s</script>\n",
		wp_json_encode( $schema, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE )
	);
}
add_action( 'wp_head', 'estatein_core_output_property_schema', 30 );
