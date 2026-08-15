<?php
/**
 * Home page.
 *
 * @package Estatein
 */

get_header();

$feature_cards = array(
	array(
		'title' => 'Find Your Dream Home',
		'icon'  => 'feature-home',
		'url'   => estatein_page_url( 'properties' ),
	),
	array(
		'title' => 'Unlock Property Value',
		'icon'  => 'feature-value',
		'url'   => estatein_page_url( 'services' ) . '#sell',
	),
	array(
		'title' => 'Effortless Property Management',
		'icon'  => 'feature-management',
		'url'   => estatein_page_url( 'services' ) . '#manage',
	),
	array(
		'title' => 'Smart Investments, Informed Decisions',
		'icon'  => 'feature-investment',
		'url'   => estatein_page_url( 'services' ) . '#invest',
	),
);

$testimonials = array(
	array(
		'title'    => 'Exceptional Service!',
		'quote'    => "Our experience with Estatein was outstanding. Their team's dedication and professionalism made finding our dream home a breeze. Highly recommended!",
		'name'     => 'Wade Warren',
		'location' => 'USA, California',
		'avatar'   => 'images/avatars/wade-warren.webp',
	),
	array(
		'title'    => 'Efficient and Reliable',
		'quote'    => "Estatein provided us with top-notch service. They helped us sell our property quickly and at a great price. We couldn't be happier with the results.",
		'name'     => 'Emelie J. Corbin',
		'location' => 'USA, Florida',
		'avatar'   => 'images/avatars/emelie-corbin.webp',
	),
	array(
		'title'    => 'Trusted Advisors',
		'quote'    => 'The Estatein team guided us through the entire buying process. Their knowledge and commitment to our needs were impressive. Thank you for your support!',
		'name'     => 'John Mans',
		'location' => 'USA, Nevada',
		'avatar'   => 'images/avatars/john-mans.webp',
	),
);

$faqs = array(
	array(
		'question' => 'How do I search for properties on Estatein?',
		'answer'   => 'Use our property search and filters to narrow listings by keyword, location, property type, price, and area, then open any card for complete details.',
	),
	array(
		'question' => 'What documents do I need to sell my property through Estatein?',
		'answer'   => 'Requirements vary by location, but commonly include proof of ownership, identification, current tax records, disclosures, and relevant inspection or improvement records.',
	),
	array(
		'question' => 'How can I contact an Estatein agent?',
		'answer'   => 'Send the inquiry form on any property, use our Contact page, call our team, or email info@estatein.com. Your request is saved before we send a notification.',
	),
);
?>
<main id="primary" class="site-main home-page">
	<section id="hero" class="home-hero" aria-labelledby="home-title">
		<div class="home-hero__media">
			<picture>
				<source media="(max-width: 767px)" srcset="<?php echo esc_url( estatein_asset_uri( 'images/hero/home-mobile.webp' ) ); ?>">
					<img src="<?php echo esc_url( estatein_asset_uri( 'images/hero/home-desktop.webp' ) ); ?>" alt="Blue-lit modern high-rise towers against a dark architectural backdrop" width="1920" height="1696" fetchpriority="high" loading="eager">
			</picture>
		</div>
		<div class="home-hero__content">
			<div class="home-hero__copy">
				<h1 id="home-title"><?php esc_html_e( 'Discover Your Dream Property with Estatein', 'estatein' ); ?></h1>
				<p><?php esc_html_e( 'Your journey to finding the perfect property begins here. Explore our listings to find the home that matches your dreams.', 'estatein' ); ?></p>
				<div class="button-row">
					<a class="button button--secondary" href="<?php echo esc_url( estatein_page_url( 'about-us' ) ); ?>"><?php esc_html_e( 'Learn More', 'estatein' ); ?></a>
					<a class="button button--primary" href="<?php echo esc_url( estatein_page_url( 'properties' ) ); ?>"><?php esc_html_e( 'Browse Properties', 'estatein' ); ?></a>
				</div>
			</div>
			<ul class="metrics" aria-label="<?php esc_attr_e( 'Estatein at a glance', 'estatein' ); ?>">
				<li><strong>200+</strong><span><?php esc_html_e( 'Happy Customers', 'estatein' ); ?></span></li>
				<li><strong>10k+</strong><span><?php esc_html_e( 'Properties For Clients', 'estatein' ); ?></span></li>
				<li><strong>16+</strong><span><?php esc_html_e( 'Years of Experience', 'estatein' ); ?></span></li>
			</ul>
		</div>
			<a class="home-hero__round-link" href="<?php echo esc_url( estatein_page_url( 'properties' ) ); ?>">
				<svg class="home-hero__round-copy" viewBox="0 0 140 140" aria-hidden="true" focusable="false">
					<defs>
						<path id="estatein-roundel-path" d="M 70,70 m -51,0 a 51,51 0 1,1 102,0 a 51,51 0 1,1 -102,0" />
					</defs>
					<text class="home-hero__round-text" transform="rotate(-45 70 70)">
						<textPath href="#estatein-roundel-path" startOffset="0" textLength="250" lengthAdjust="spacing">Discover Your Dream Property</textPath>
					</text>
				</svg>
				<span class="home-hero__round-sparkle" aria-hidden="true">✨</span>
				<span class="home-hero__round-center" aria-hidden="true">
					<img src="<?php echo esc_url( estatein_asset_uri( 'icons/hero-discover.svg' ) ); ?>" alt="" width="34" height="34">
				</span>
				<span class="screen-reader-text"><?php esc_html_e( 'Discover your dream property', 'estatein' ); ?></span>
			</a>
	</section>

	<nav id="features" class="feature-shortcuts" aria-label="<?php esc_attr_e( 'Property services', 'estatein' ); ?>">
		<ul>
			<?php foreach ( $feature_cards as $feature ) : ?>
				<li>
					<a href="<?php echo esc_url( $feature['url'] ); ?>">
						<span class="feature-shortcuts__icon"><?php estatein_icon( $feature['icon'] ); ?></span>
						<span><?php echo esc_html( $feature['title'] ); ?></span>
						<?php estatein_icon( 'arrow-up-right', 'feature-shortcuts__arrow' ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</nav>

	<section id="properties" class="page-section site-shell" aria-labelledby="featured-properties-title">
		<?php
		get_template_part(
			'template-parts/components/section-heading',
			null,
			array(
				'id'           => 'featured-properties-title',
				'title'        => 'Featured Properties',
				'copy'         => 'Explore our handpicked selection of featured properties. Each listing offers a glimpse into exceptional homes and investments available through Estatein.',
				'button_label' => 'View All Properties',
				'button_url'   => estatein_page_url( 'properties' ),
			)
		);

		$property_query = new WP_Query(
			array(
				'post_type'      => 'estatein_property',
				'posts_per_page' => 3,
				'post_status'    => 'publish',
				'no_found_rows'  => true,
			)
		);
		?>
		<div id="featured-property-rail" class="card-rail property-rail" data-rail>
			<?php if ( $property_query->have_posts() ) : ?>
				<?php while ( $property_query->have_posts() ) : ?>
					<?php $property_query->the_post(); ?>
					<?php get_template_part( 'template-parts/components/property-card', null, array( 'property' => get_post() ) ); ?>
				<?php endwhile; ?>
				<?php wp_reset_postdata(); ?>
			<?php else : ?>
				<?php foreach ( estatein_demo_properties() as $property ) : ?>
					<?php get_template_part( 'template-parts/components/property-card', null, array( 'property' => $property ) ); ?>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
		<?php
		get_template_part(
			'template-parts/components/rail-controls',
			null,
			array(
				'target' => 'featured-property-rail',
				'label'  => 'properties',
				'total'  => '60',
			)
		);
		?>
	</section>

	<section id="testimonials" class="page-section site-shell" aria-labelledby="testimonials-title">
		<?php
		get_template_part(
			'template-parts/components/section-heading',
			null,
			array(
				'id'           => 'testimonials-title',
				'title'        => 'What Our Clients Say',
				'copy'         => 'Read the experiences of people who found, sold, and managed property with the Estatein team.',
				'button_label' => 'View All Testimonials',
				'button_url'   => '#testimonial-rail',
			)
		);
		?>
		<div id="testimonial-rail" class="card-rail testimonial-rail" data-rail>
			<?php foreach ( $testimonials as $testimonial ) : ?>
				<figure class="testimonial-card rail-card">
					<div class="star-rating" role="img" aria-label="<?php esc_attr_e( '5 out of 5 stars', 'estatein' ); ?>">
						<?php for ( $star = 0; $star < 5; $star++ ) : ?>
							<?php estatein_icon( 'star' ); ?>
						<?php endfor; ?>
					</div>
					<blockquote>
						<h3><?php echo esc_html( $testimonial['title'] ); ?></h3>
						<p><?php echo esc_html( $testimonial['quote'] ); ?></p>
					</blockquote>
					<figcaption>
						<img src="<?php echo esc_url( estatein_asset_uri( $testimonial['avatar'] ) ); ?>" alt="" width="60" height="60" loading="lazy">
						<span><strong><?php echo esc_html( $testimonial['name'] ); ?></strong><small><?php echo esc_html( $testimonial['location'] ); ?></small></span>
					</figcaption>
				</figure>
			<?php endforeach; ?>
		</div>
		<?php
		get_template_part(
			'template-parts/components/rail-controls',
			null,
			array(
				'target' => 'testimonial-rail',
				'label'  => 'testimonials',
				'total'  => '03',
			)
		);
		?>
	</section>

	<section id="faqs" class="page-section site-shell" aria-labelledby="home-faq-title">
		<?php
		get_template_part(
			'template-parts/components/section-heading',
			null,
			array(
				'id'           => 'home-faq-title',
				'title'        => 'Frequently Asked Questions',
				'copy'         => "Find answers to common questions about Estatein's services, property listings, and the real estate process. We're here to provide clarity every step of the way.",
				'button_label' => 'View All FAQs',
				'button_url'   => '#home-faq-list',
			)
		);
		get_template_part(
			'template-parts/components/faq-list',
			null,
			array(
				'items' => $faqs,
				'id'    => 'home-faq-list',
			)
		);
		?>
	</section>
</main>
<?php
get_footer();
