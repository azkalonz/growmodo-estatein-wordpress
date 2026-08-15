<?php
/**
 * Property card.
 *
 * @package Estatein
 */

$property = estatein_property_view_model( $args['property'] ?? array() );
?>
<article class="property-card rail-card">
	<a class="property-card__image-link" href="<?php echo esc_url( $property['url'] ); ?>" tabindex="-1" aria-hidden="true">
		<?php if ( ! empty( $property['image_id'] ) ) : ?>
			<?php
			echo wp_get_attachment_image(
				(int) $property['image_id'],
				'estatein-property-card',
				false,
				array(
					'class'   => 'property-card__image',
					'loading' => 'lazy',
					'alt'     => '',
				)
			);
			?>
		<?php else : ?>
			<img class="property-card__image" src="<?php echo esc_url( estatein_asset_uri( $property['image'] ) ); ?>" alt="" width="768" height="596" loading="lazy">
		<?php endif; ?>
	</a>
	<div class="property-card__body">
		<h3><a href="<?php echo esc_url( $property['url'] ); ?>"><?php echo esc_html( $property['title'] ); ?></a></h3>
		<p><?php echo esc_html( $property['excerpt'] ); ?> <a href="<?php echo esc_url( $property['url'] ); ?>"><?php esc_html_e( 'Read More', 'estatein' ); ?></a></p>
		<ul class="property-tags" aria-label="<?php esc_attr_e( 'Property features', 'estatein' ); ?>">
			<li><?php estatein_icon( 'bed' ); ?><span><?php echo esc_html( $property['bedrooms'] ); ?></span></li>
			<li><?php estatein_icon( 'bath' ); ?><span><?php echo esc_html( $property['bathrooms'] ); ?></span></li>
			<li><?php estatein_icon( 'building' ); ?><span><?php echo esc_html( $property['property_type'] ); ?></span></li>
		</ul>
		<div class="property-card__footer">
			<p class="property-price"><span><?php esc_html_e( 'Price', 'estatein' ); ?></span><strong><?php echo esc_html( estatein_format_price( $property['price'] ) ); ?></strong></p>
			<a class="button button--primary" href="<?php echo esc_url( $property['url'] ); ?>"><?php esc_html_e( 'View Property Details', 'estatein' ); ?></a>
		</div>
	</div>
</article>
