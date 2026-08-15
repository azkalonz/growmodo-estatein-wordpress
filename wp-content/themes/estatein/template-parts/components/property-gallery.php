<?php
/**
 * Property gallery and native dialog lightbox.
 *
 * @package Estatein
 */

$property_id = isset( $args['post_id'] ) ? (int) $args['post_id'] : get_the_ID();
$images      = estatein_property_gallery_images( $property_id );
$first       = $images[0];
?>
<div class="property-gallery" data-property-gallery>
	<div class="property-gallery__stage">
		<?php foreach ( array_slice( $images, 0, 2 ) as $index => $image ) : ?>
			<button class="property-gallery__feature" type="button" data-gallery-open data-gallery-index="<?php echo esc_attr( $index ); ?>">
				<img src="<?php echo esc_url( $image['url'] ); ?>" alt="<?php echo esc_attr( $image['alt'] ); ?>" width="1280" height="860" <?php echo 0 === $index ? 'fetchpriority="high"' : 'loading="lazy"'; ?>>
				<span class="screen-reader-text"><?php esc_html_e( 'Open property gallery', 'estatein' ); ?></span>
			</button>
		<?php endforeach; ?>
	</div>

	<div class="property-gallery__thumbs" aria-label="<?php esc_attr_e( 'Property gallery thumbnails', 'estatein' ); ?>">
		<?php foreach ( $images as $index => $image ) : ?>
			<button type="button" data-gallery-open data-gallery-index="<?php echo esc_attr( $index ); ?>" aria-label="<?php /* translators: %d: Gallery image position. */ printf( esc_attr__( 'Open gallery image %d', 'estatein' ), (int) $index + 1 ); ?>">
				<img src="<?php echo esc_url( $image['thumb'] ); ?>" alt="" width="160" height="118" loading="lazy">
			</button>
		<?php endforeach; ?>
	</div>

	<div class="property-gallery__mobile-controls">
		<button class="icon-button" type="button" data-gallery-strip-previous><span class="screen-reader-text"><?php esc_html_e( 'Previous gallery images', 'estatein' ); ?></span><?php estatein_icon( 'arrow-left' ); ?></button>
		<p><span>01</span> <?php esc_html_e( 'of', 'estatein' ); ?> <?php echo esc_html( str_pad( (string) count( $images ), 2, '0', STR_PAD_LEFT ) ); ?></p>
		<button class="icon-button" type="button" data-gallery-strip-next><span class="screen-reader-text"><?php esc_html_e( 'Next gallery images', 'estatein' ); ?></span><?php estatein_icon( 'arrow-right' ); ?></button>
	</div>

	<dialog class="gallery-dialog" data-gallery-dialog aria-labelledby="gallery-dialog-title">
		<div class="gallery-dialog__header">
			<h2 id="gallery-dialog-title"><?php /* translators: %s: Property title. */ printf( esc_html__( '%s gallery', 'estatein' ), esc_html( get_the_title( $property_id ) ) ); ?></h2>
			<form method="dialog"><button class="icon-button" value="close"><span class="screen-reader-text"><?php esc_html_e( 'Close gallery', 'estatein' ); ?></span><?php estatein_icon( 'close' ); ?></button></form>
		</div>
		<div class="gallery-dialog__stage">
			<img src="<?php echo esc_url( $first['url'] ); ?>" alt="<?php echo esc_attr( $first['alt'] ); ?>" width="1280" height="860" data-gallery-dialog-image>
		</div>
		<div class="gallery-dialog__controls">
			<button class="icon-button" type="button" data-gallery-previous><span class="screen-reader-text"><?php esc_html_e( 'Previous image', 'estatein' ); ?></span><?php estatein_icon( 'arrow-left' ); ?></button>
			<p><span data-gallery-current>01</span> <?php esc_html_e( 'of', 'estatein' ); ?> <?php echo esc_html( str_pad( (string) count( $images ), 2, '0', STR_PAD_LEFT ) ); ?></p>
			<button class="icon-button" type="button" data-gallery-next><span class="screen-reader-text"><?php esc_html_e( 'Next image', 'estatein' ); ?></span><?php estatein_icon( 'arrow-right' ); ?></button>
		</div>
		<script type="application/json" data-gallery-images><?php echo wp_json_encode( array_values( $images ), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); ?></script>
	</dialog>
</div>
