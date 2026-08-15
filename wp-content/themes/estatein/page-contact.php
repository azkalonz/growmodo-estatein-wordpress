<?php
/**
 * Contact page.
 *
 * @package Estatein
 */

get_header();

$contact_methods = array(
	array(
		'label' => 'info@estatein.com',
		'url'   => 'mailto:info@estatein.com',
		'icon'  => 'contact-email',
	),
	array(
		'label' => '+1 (123) 456-7890',
		'url'   => 'tel:+11234567890',
		'icon'  => 'contact-phone',
	),
	array(
		'label' => 'Main Headquarters',
		'url'   => '#offices',
		'icon'  => 'contact-location',
	),
	array(
		'label' => 'Instagram / LinkedIn / Facebook',
		'url'   => '#social',
		'icon'  => 'contact-social',
	),
);

$offices = array(
	array(
		'type'    => 'international',
		'eyebrow' => 'Main Headquarters',
		'title'   => '123 Estatein Plaza, City Center, Metropolis',
		'copy'    => 'Our main headquarters serve as the heart of Estatein. Located in the bustling city center, this is where our core team of experts operates.',
		'email'   => 'info@estatein.com',
		'phone'   => '+1 (123) 456-7890',
		'city'    => 'Metropolis',
		'map'     => 'https://www.google.com/maps/search/?api=1&query=Metropolis',
	),
	array(
		'type'    => 'regional',
		'eyebrow' => 'Regional Offices',
		'title'   => '456 Urban Avenue, Downtown District, Metropolis',
		'copy'    => 'Estatein has a presence in multiple regions, each office staffed by local experts who understand their markets and communities.',
		'email'   => 'info@estatein.com',
		'phone'   => '+1 (123) 628-7890',
		'city'    => 'Metropolis',
		'map'     => 'https://www.google.com/maps/search/?api=1&query=Downtown+District+Metropolis',
	),
);

$gallery = array(
	array(
		'file' => 'images/contact/gallery-01.webp',
		'alt'  => 'Estatein colleagues collaborating in a bright office',
	),
	array(
		'file' => 'images/contact/gallery-02.webp',
		'alt'  => 'A team discussion around a shared workspace',
	),
	array(
		'file' => 'images/contact/gallery-03.webp',
		'alt'  => 'Estatein advisors reviewing a property plan',
	),
	array(
		'file' => 'images/contact/gallery-04.webp',
		'alt'  => 'A welcoming contemporary Estatein office interior',
	),
	array(
		'file' => 'images/contact/gallery-05.webp',
		'alt'  => 'Two Estatein team members in conversation',
	),
	array(
		'file' => 'images/contact/gallery-06.webp',
		'alt'  => 'The Estatein team gathered together',
	),
);
?>
<main id="primary" class="site-main contact-page">
	<section class="page-intro" aria-labelledby="contact-title">
		<div class="page-intro__inner site-shell">
			<h1 id="contact-title"><?php esc_html_e( 'Get in Touch with Estatein', 'estatein' ); ?></h1>
			<p><?php esc_html_e( "Welcome to Estatein's Contact Us page. We're here to assist you with any inquiries, requests, or feedback you may have. Whether you're looking to buy or sell a property, explore investment opportunities, or simply want to connect, we're just a message away.", 'estatein' ); ?></p>
		</div>
	</section>

	<nav class="feature-shortcuts contact-shortcuts" aria-label="<?php esc_attr_e( 'Contact Estatein', 'estatein' ); ?>">
		<ul>
			<?php foreach ( $contact_methods as $method ) : ?>
				<li>
					<a href="<?php echo esc_url( $method['url'] ); ?>">
						<span class="feature-shortcuts__icon"><?php estatein_icon( $method['icon'] ); ?></span>
						<span><?php echo esc_html( $method['label'] ); ?></span>
						<?php estatein_icon( 'arrow-up-right', 'feature-shortcuts__arrow' ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</nav>

	<section id="contact-form" class="page-section site-shell" aria-labelledby="connect-title">
		<?php
		get_template_part(
			'template-parts/components/section-heading',
			null,
			array(
				'id'    => 'connect-title',
				'title' => "Let's Connect",
				'copy'  => "We're excited to connect with you and learn more about your real estate goals. Whether you're a prospective client, partner, or simply curious about our services, we're here to answer your questions.",
			)
		);
		get_template_part( 'template-parts/forms/contact', null, array( 'form_id' => 'estatein-main-contact' ) );
		?>
	</section>

	<section id="offices" class="page-section site-shell" aria-labelledby="offices-title">
		<?php
		get_template_part(
			'template-parts/components/section-heading',
			null,
			array(
				'id'    => 'offices-title',
				'title' => 'Discover Our Office Locations',
				'copy'  => 'Estatein is here to serve you across multiple locations. Explore the categories below to find the Estatein office nearest to you.',
			)
		);
		?>
		<div class="office-tabs" role="toolbar" aria-label="<?php esc_attr_e( 'Filter offices', 'estatein' ); ?>" data-office-filters>
			<button class="button is-active" type="button" aria-pressed="true" data-office-filter="all"><?php esc_html_e( 'All', 'estatein' ); ?></button>
			<button class="button" type="button" aria-pressed="false" data-office-filter="regional"><?php esc_html_e( 'Regional', 'estatein' ); ?></button>
			<button class="button" type="button" aria-pressed="false" data-office-filter="international"><?php esc_html_e( 'International', 'estatein' ); ?></button>
		</div>
		<div class="office-grid" data-office-grid>
			<?php foreach ( $offices as $office ) : ?>
				<article class="office-card" data-office-type="<?php echo esc_attr( $office['type'] ); ?>">
					<p class="office-card__eyebrow"><?php echo esc_html( $office['eyebrow'] ); ?></p>
					<h3><?php echo esc_html( $office['title'] ); ?></h3>
					<p><?php echo esc_html( $office['copy'] ); ?></p>
					<ul class="office-card__details">
						<li><a href="mailto:<?php echo esc_attr( $office['email'] ); ?>"><?php estatein_icon( 'email' ); ?><?php echo esc_html( $office['email'] ); ?></a></li>
						<li><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $office['phone'] ) ); ?>"><?php estatein_icon( 'phone' ); ?><?php echo esc_html( $office['phone'] ); ?></a></li>
						<li><?php estatein_icon( 'location' ); ?><span><?php echo esc_html( $office['city'] ); ?></span></li>
					</ul>
					<a class="button button--primary" href="<?php echo esc_url( $office['map'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Get Direction', 'estatein' ); ?></a>
				</article>
			<?php endforeach; ?>
		</div>
	</section>

	<section class="page-section site-shell world-gallery" aria-labelledby="world-title">
		<div class="world-gallery__grid">
			<?php foreach ( $gallery as $index => $image ) : ?>
				<figure class="world-gallery__image world-gallery__image--<?php echo esc_attr( $index + 1 ); ?>">
					<img src="<?php echo esc_url( estatein_asset_uri( $image['file'] ) ); ?>" alt="<?php echo esc_attr( $image['alt'] ); ?>" width="900" height="600" loading="lazy">
				</figure>
			<?php endforeach; ?>
			<div class="world-gallery__intro">
				<img class="section-heading__spark" src="<?php echo esc_url( estatein_asset_uri( 'images/decor/spark.svg' ) ); ?>" alt="" width="68" height="30" loading="lazy">
				<h2 id="world-title"><?php esc_html_e( "Explore Estatein's World", 'estatein' ); ?></h2>
				<p><?php esc_html_e( 'Step inside the world of Estatein, where professionalism meets warmth and expertise meets passion. Our gallery offers a glimpse into our team and workspaces, inviting you to get to know us better.', 'estatein' ); ?></p>
			</div>
		</div>
	</section>
</main>
<?php
get_footer();
