<?php
/**
 * Presentation helpers and resilient demo fallbacks.
 *
 * @package Estatein
 */

defined( 'ABSPATH' ) || exit;

/**
 * Build a theme asset URI.
 *
 * @param string $path Relative path under assets.
 * @return string
 */
function estatein_asset_uri( $path ) {
	return get_template_directory_uri() . '/assets/' . ltrim( $path, '/' );
}

/**
 * Render an exported Figma icon.
 *
 * @param string $name       Asset basename without extension.
 * @param string $class_name Optional CSS class.
 * @param string $alt        Alt text; decorative icons use an empty value.
 * @return void
 */
function estatein_icon( $name, $class_name = '', $alt = '' ) {
	$filename = sanitize_file_name( $name ) . '.svg';
	?>
	<img class="estatein-icon <?php echo esc_attr( $class_name ); ?>" src="<?php echo esc_url( estatein_asset_uri( 'icons/' . $filename ) ); ?>" alt="<?php echo esc_attr( $alt ); ?>" width="24" height="24" loading="lazy">
	<?php
}

/**
 * Return a named page URL with a deterministic fallback.
 *
 * @param string $slug Page slug.
 * @return string
 */
function estatein_page_url( $slug ) {
	if ( 'properties' === $slug && post_type_exists( 'estatein_property' ) ) {
		$url = get_post_type_archive_link( 'estatein_property' );
		if ( $url ) {
			return $url;
		}
	}

	$page = get_page_by_path( $slug );

	return $page ? get_permalink( $page ) : home_url( '/' . trim( $slug, '/' ) . '/' );
}

/**
 * Get a property field from the companion plugin, ACF, or native post meta.
 *
 * @param int    $post_id Property ID.
 * @param string $key     Field key.
 * @param mixed  $fallback Fallback value.
 * @return mixed
 */
function estatein_property_field( $post_id, $key, $fallback = '' ) {
	if ( function_exists( 'estatein_core_get_property_field' ) ) {
		return estatein_core_get_property_field( $post_id, $key, $fallback );
	}

	if ( function_exists( 'get_field' ) ) {
		$value = get_field( 'estatein_' . $key, $post_id, false );
		if ( null === $value || false === $value || '' === $value ) {
			$value = get_field( $key, $post_id, false );
		}
		if ( null !== $value && false !== $value && '' !== $value ) {
			return $value;
		}
	}

	$value = get_post_meta( $post_id, 'estatein_' . $key, true );
	if ( '' === $value ) {
		$value = get_post_meta( $post_id, $key, true );
	}

	return '' !== $value ? $value : $fallback;
}

/**
 * Format a stored property price.
 *
 * @param mixed $price Price value.
 * @return string
 */
function estatein_format_price( $price ) {
	if ( function_exists( 'estatein_core_format_price' ) ) {
		return estatein_core_format_price( $price );
	}

	$numeric = (float) preg_replace( '/[^0-9.]/', '', (string) $price );

	return $numeric > 0 ? '$' . number_format_i18n( $numeric, 0 ) : (string) $price;
}

/**
 * Return normalized gallery image view models.
 *
 * @param int $post_id Property ID.
 * @return array<int,array<string,mixed>>
 */
function estatein_property_gallery_images( $post_id ) {
	$gallery = function_exists( 'estatein_core_get_property_gallery' ) ? estatein_core_get_property_gallery( $post_id ) : estatein_property_field( $post_id, 'gallery', array() );
	$images  = array();
	if ( ! $gallery ) {
		$gallery = array();
		for ( $slot = 1; $slot <= 8; $slot++ ) {
			$image = estatein_property_field( $post_id, 'gallery_' . $slot, 0 );
			if ( $image ) {
				$gallery[] = $image;
			}
		}
	}

	if ( is_array( $gallery ) ) {
		foreach ( $gallery as $image ) {
			$image_id = is_array( $image ) ? (int) ( $image['ID'] ?? $image['id'] ?? 0 ) : ( is_numeric( $image ) ? (int) $image : 0 );
			$url      = is_array( $image ) ? ( $image['url'] ?? '' ) : ( is_string( $image ) && ! is_numeric( $image ) ? $image : '' );
			$alt      = is_array( $image ) ? (string) ( $image['alt'] ?? '' ) : '';
			if ( $image_id ) {
				$large_url = wp_get_attachment_image_url( $image_id, 'estatein-gallery-large' );
				$url       = $large_url ? $large_url : wp_get_attachment_url( $image_id );
			}
			if ( ! $url ) {
				continue;
			}
			$thumbnail_url  = $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '';
			$thumbnail_url  = $thumbnail_url ? $thumbnail_url : $url;
			$attachment_alt = $image_id ? get_post_meta( $image_id, '_wp_attachment_image_alt', true ) : '';
			$resolved_alt   = $alt ? $alt : $attachment_alt;
			$images[]       = array(
				'id'    => $image_id,
				'url'   => $url,
				'thumb' => $thumbnail_url,
				'alt'   => $resolved_alt,
			);
		}
	}

	if ( ! $images && has_post_thumbnail( $post_id ) ) {
		$image_id = get_post_thumbnail_id( $post_id );
		$images[] = array(
			'id'    => $image_id,
			'url'   => wp_get_attachment_image_url( $image_id, 'estatein-gallery-large' ),
			'thumb' => wp_get_attachment_image_url( $image_id, 'thumbnail' ),
			'alt'   => get_post_meta( $image_id, '_wp_attachment_image_alt', true ),
		);
	}

	if ( ! $images ) {
		for ( $index = 1; $index <= 10; $index++ ) {
			$url      = estatein_asset_uri( 'images/properties/seaside/gallery-' . str_pad( (string) $index, 2, '0', STR_PAD_LEFT ) . '.webp' );
			$images[] = array(
				'id'    => 0,
				'url'   => $url,
				'thumb' => $url,
				'alt'   => __( 'View of Seaside Serenity Villa', 'estatein' ),
			);
		}
	}

	return $images;
}

/**
 * Demo properties shown if fixtures are not installed yet.
 *
 * @return array<int,array<string,mixed>>
 */
function estatein_demo_properties() {
	return array(
		array(
			'title'         => 'Seaside Serenity Villa',
			'excerpt'       => 'A stunning 4-bedroom, 3-bathroom villa in a peaceful coastal neighborhood.',
			'price'         => 1250000,
			'bedrooms'      => '4-Bedroom',
			'bathrooms'     => '3-Bathroom',
			'property_type' => 'Villa',
			'location'      => 'Malibu, California',
			'image'         => 'images/properties/seaside-serenity-villa.webp',
			'url'           => estatein_page_url( 'properties' ),
		),
		array(
			'title'         => 'Metropolitan Haven',
			'excerpt'       => 'A chic and fully-furnished 2-bedroom apartment with panoramic city views.',
			'price'         => 650000,
			'bedrooms'      => '2-Bedroom',
			'bathrooms'     => '2-Bathroom',
			'property_type' => 'Villa',
			'location'      => 'Manhattan, New York',
			'image'         => 'images/properties/metropolitan-haven.webp',
			'url'           => estatein_page_url( 'properties' ),
		),
		array(
			'title'         => 'Rustic Retreat Cottage',
			'excerpt'       => 'An elegant 3-bedroom cottage set amid a calm, private garden landscape.',
			'price'         => 550000,
			'bedrooms'      => '3-Bedroom',
			'bathrooms'     => '3-Bathroom',
			'property_type' => 'Villa',
			'location'      => 'Sedona, Arizona',
			'image'         => 'images/properties/rustic-retreat-cottage.webp',
			'url'           => estatein_page_url( 'properties' ),
		),
	);
}

/**
 * Convert a post or fallback array into a consistent property view model.
 *
 * @param WP_Post|array<string,mixed> $property Property source.
 * @return array<string,mixed>
 */
function estatein_property_view_model( $property ) {
	if ( is_array( $property ) ) {
		return $property;
	}

	$post_id = $property instanceof WP_Post ? $property->ID : (int) $property;
	$terms   = wp_get_post_terms( $post_id, 'estatein_property_type', array( 'fields' => 'names' ) );
	$types   = is_wp_error( $terms ) ? array() : $terms;

	return array(
		'title'         => get_the_title( $post_id ),
		'excerpt'       => get_the_excerpt( $post_id ),
		'price'         => estatein_property_field( $post_id, 'price', 0 ),
		'bedrooms'      => estatein_property_field( $post_id, 'bedrooms', 4 ) . '-Bedroom',
		'bathrooms'     => estatein_property_field( $post_id, 'bathrooms', 3 ) . '-Bathroom',
		'property_type' => ! empty( $types ) ? $types[0] : estatein_property_field( $post_id, 'property_type', 'Villa' ),
		'location'      => estatein_property_field( $post_id, 'address', '' ),
		'image_id'      => get_post_thumbnail_id( $post_id ),
		'image'         => 'images/properties/seaside-serenity-villa.webp',
		'url'           => get_permalink( $post_id ),
	);
}

/**
 * Render a simple named menu or its seeded fallback links.
 *
 * @param string                          $location Menu location.
 * @param array<int,array<string,string>> $fallback Fallback label/url pairs.
 * @return void
 */
function estatein_menu_or_fallback( $location, $fallback ) {
	if ( has_nav_menu( $location ) ) {
		wp_nav_menu(
			array(
				'theme_location' => $location,
				'container'      => false,
				'menu_class'     => 'estatein-menu estatein-menu--' . sanitize_html_class( $location ),
				'depth'          => 1,
			)
		);
		return;
	}

	echo '<ul class="estatein-menu estatein-menu--' . esc_attr( sanitize_html_class( $location ) ) . '">';
	foreach ( $fallback as $item ) {
		echo '<li><a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['label'] ) . '</a></li>';
	}
	echo '</ul>';
}

/**
 * Return status copy sent by the PRG form flow.
 *
 * @param string $form Expected form identifier.
 * @return array<string,string>|null
 */
function estatein_form_status( $form ) {
	if ( function_exists( 'estatein_core_get_form_notice' ) ) {
		$notice = estatein_core_get_form_notice( $form );
		if ( is_array( $notice ) && ! empty( $notice['message'] ) ) {
			return array(
				'type' => 'success' === ( $notice['status'] ?? '' ) ? 'success' : 'error',
				'text' => wp_strip_all_tags( (string) $notice['message'] ),
			);
		}
	}

	$form_request = wp_unslash( $_GET ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only PRG status.
	if ( empty( $form_request['estatein_form'] ) || sanitize_key( $form_request['estatein_form'] ) !== $form ) {
		return null;
	}

	$status = isset( $form_request['estatein_status'] ) ? sanitize_key( $form_request['estatein_status'] ) : '';
	$code   = isset( $form_request['estatein_code'] ) ? sanitize_key( $form_request['estatein_code'] ) : '';

	if ( 'success' === $status ) {
		return array(
			'type' => 'success',
			'text' => 'subscribe' === $form
				? __( 'Thanks — you are on the Estatein list.', 'estatein' )
				: __( 'Thanks. Your message was saved and our team will be in touch.', 'estatein' ),
		);
	}

	$messages = array(
		'validation' => __( 'Please review the highlighted fields and try again.', 'estatein' ),
		'nonce'      => __( 'Your session expired. Please refresh the page and try again.', 'estatein' ),
		'rate_limit' => __( 'Please wait a moment before sending another message.', 'estatein' ),
		'spam'       => __( 'We could not accept that submission.', 'estatein' ),
		'duplicate'  => __( 'We already received this message. There is no need to send it again.', 'estatein' ),
	);

	return array(
		'type' => 'error',
		'text' => $messages[ $code ] ?? __( 'Something went wrong. Please try again.', 'estatein' ),
	);
}

/**
 * Render an announced form status message.
 *
 * @param string $form Form identifier.
 * @return void
 */
function estatein_render_form_status( $form ) {
	$status = estatein_form_status( $form );
	if ( ! $status ) {
		return;
	}
	?>
	<div class="form-status form-status--<?php echo esc_attr( $status['type'] ); ?>" role="status" tabindex="-1" data-form-status>
		<?php echo esc_html( $status['text'] ); ?>
	</div>
	<?php
}

/**
 * Fall back to a useful title if an archive has not been configured yet.
 *
 * @param string $fallback Title fallback.
 * @return string
 */
function estatein_context_title( $fallback ) {
	$title = get_the_archive_title();

	return $title ? wp_strip_all_tags( $title ) : $fallback;
}
