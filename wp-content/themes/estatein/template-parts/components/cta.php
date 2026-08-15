<?php
/**
 * Site-wide real-estate journey call to action.
 *
 * @package Estatein
 */

?>
<aside class="journey-cta">
	<img class="journey-cta__decoration journey-cta__decoration--left" src="<?php echo esc_url( estatein_asset_uri( 'icons/figma/about-03.svg' ) ); ?>" alt="" width="566" height="308" loading="lazy">
	<div class="journey-cta__inner site-shell">
		<div class="journey-cta__content">
			<h2><?php esc_html_e( 'Start Your Real Estate Journey Today', 'estatein' ); ?></h2>
			<p><?php esc_html_e( "Your dream property is just a click away. Whether you're looking for a new home, a strategic investment, or expert real estate advice, Estatein is here to assist you every step of the way. Take the first step towards your real estate goals and explore our available properties or get in touch with our team for personalized assistance.", 'estatein' ); ?></p>
		</div>
		<a class="button button--primary" href="<?php echo esc_url( estatein_page_url( 'properties' ) ); ?>"><?php esc_html_e( 'Explore Properties', 'estatein' ); ?></a>
	</div>
	<img class="journey-cta__decoration journey-cta__decoration--right" src="<?php echo esc_url( estatein_asset_uri( 'icons/figma/about-11.svg' ) ); ?>" alt="" width="725" height="394" loading="lazy">
</aside>
