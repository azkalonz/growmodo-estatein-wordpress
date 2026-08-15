<?php
/**
 * Site footer.
 *
 * @package Estatein
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'template-parts/components/cta' );
?>
<footer class="site-footer">
	<div class="site-footer__main site-shell">
		<div class="site-footer__brand">
			<a class="site-brand site-brand--footer" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
				<img class="site-brand__mark" src="<?php echo esc_url( estatein_asset_uri( 'images/logo-mark.svg' ) ); ?>" alt="" width="48" height="48" loading="lazy">
				<img class="site-brand__wordmark" src="<?php echo esc_url( estatein_asset_uri( 'images/logo-wordmark.svg' ) ); ?>" alt="Estatein" width="113" height="26" loading="lazy">
			</a>
			<?php get_template_part( 'template-parts/forms/newsletter' ); ?>
		</div>

		<div class="footer-nav-grid">
			<section class="footer-nav" aria-labelledby="footer-home-title">
				<h2 id="footer-home-title"><?php esc_html_e( 'Home', 'estatein' ); ?></h2>
				<?php
				estatein_menu_or_fallback(
					'footer_home',
					array(
						array(
							'label' => 'Hero Section',
							'url'   => home_url( '/#hero' ),
						),
						array(
							'label' => 'Features',
							'url'   => home_url( '/#features' ),
						),
						array(
							'label' => 'Properties',
							'url'   => home_url( '/#properties' ),
						),
						array(
							'label' => 'Testimonials',
							'url'   => home_url( '/#testimonials' ),
						),
						array(
							'label' => "FAQ's",
							'url'   => home_url( '/#faqs' ),
						),
					)
				);
				?>
			</section>
			<section class="footer-nav" aria-labelledby="footer-about-title">
				<h2 id="footer-about-title"><?php esc_html_e( 'About Us', 'estatein' ); ?></h2>
				<?php
				estatein_menu_or_fallback(
					'footer_about',
					array(
						array(
							'label' => 'Our Story',
							'url'   => estatein_page_url( 'about-us' ) . '#journey',
						),
						array(
							'label' => 'Our Works',
							'url'   => estatein_page_url( 'about-us' ) . '#values',
						),
						array(
							'label' => 'How It Works',
							'url'   => estatein_page_url( 'about-us' ) . '#process',
						),
						array(
							'label' => 'Our Team',
							'url'   => estatein_page_url( 'about-us' ) . '#team',
						),
						array(
							'label' => 'Our Clients',
							'url'   => estatein_page_url( 'about-us' ) . '#clients',
						),
					)
				);
				?>
			</section>
			<section class="footer-nav" aria-labelledby="footer-properties-title">
				<h2 id="footer-properties-title"><?php esc_html_e( 'Properties', 'estatein' ); ?></h2>
				<?php
				estatein_menu_or_fallback(
					'footer_properties',
					array(
						array(
							'label' => 'Portfolio',
							'url'   => estatein_page_url( 'properties' ),
						),
						array(
							'label' => 'Categories',
							'url'   => estatein_page_url( 'properties' ) . '#property-search',
						),
					)
				);
				?>
			</section>
			<section class="footer-nav" aria-labelledby="footer-services-title">
				<h2 id="footer-services-title"><?php esc_html_e( 'Services', 'estatein' ); ?></h2>
				<?php
				estatein_menu_or_fallback(
					'footer_services',
					array(
						array(
							'label' => 'Valuation Mastery',
							'url'   => estatein_page_url( 'services' ) . '#sell',
						),
						array(
							'label' => 'Strategic Marketing',
							'url'   => estatein_page_url( 'services' ) . '#sell',
						),
						array(
							'label' => 'Negotiation Wizardry',
							'url'   => estatein_page_url( 'services' ) . '#sell',
						),
						array(
							'label' => 'Closing Success',
							'url'   => estatein_page_url( 'services' ) . '#sell',
						),
						array(
							'label' => 'Property Management',
							'url'   => estatein_page_url( 'services' ) . '#manage',
						),
					)
				);
				?>
			</section>
			<section class="footer-nav" aria-labelledby="footer-contact-title">
				<h2 id="footer-contact-title"><?php esc_html_e( 'Contact Us', 'estatein' ); ?></h2>
				<?php
				estatein_menu_or_fallback(
					'footer_contact',
					array(
						array(
							'label' => 'Contact Form',
							'url'   => estatein_page_url( 'contact' ) . '#contact-form',
						),
						array(
							'label' => 'Our Offices',
							'url'   => estatein_page_url( 'contact' ) . '#offices',
						),
					)
				);
				?>
			</section>
		</div>
	</div>

	<div class="site-footer__legal">
		<div class="site-shell site-footer__legal-inner">
			<p>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> Estatein. <?php esc_html_e( 'All rights reserved.', 'estatein' ); ?></p>
			<a href="<?php echo esc_url( home_url( '/terms-and-conditions/' ) ); ?>"><?php esc_html_e( 'Terms & Conditions', 'estatein' ); ?></a>
			<nav id="social" class="social-links" aria-label="<?php esc_attr_e( 'Social media', 'estatein' ); ?>">
				<a href="https://www.facebook.com/" rel="noopener noreferrer" target="_blank"><span class="screen-reader-text">Facebook</span><?php estatein_icon( 'facebook' ); ?></a>
				<a href="https://www.linkedin.com/" rel="noopener noreferrer" target="_blank"><span class="screen-reader-text">LinkedIn</span><?php estatein_icon( 'linkedin' ); ?></a>
				<a href="https://x.com/" rel="noopener noreferrer" target="_blank"><span class="screen-reader-text">X</span><?php estatein_icon( 'x' ); ?></a>
				<a href="https://www.youtube.com/" rel="noopener noreferrer" target="_blank"><span class="screen-reader-text">YouTube</span><?php estatein_icon( 'youtube' ); ?></a>
			</nav>
		</div>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
