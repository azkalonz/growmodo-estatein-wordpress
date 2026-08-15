<?php
/**
 * Services page.
 *
 * @package Estatein
 */

get_header();

$shortcuts = array(
	array(
		'title' => 'Find Your Dream Home',
		'icon'  => 'feature-home',
		'url'   => estatein_page_url( 'properties' ),
	),
	array(
		'title' => 'Unlock Property Value',
		'icon'  => 'feature-value',
		'url'   => '#sell',
	),
	array(
		'title' => 'Effortless Property Management',
		'icon'  => 'feature-management',
		'url'   => '#manage',
	),
	array(
		'title' => 'Smart Investments, Informed Decisions',
		'icon'  => 'feature-investment',
		'url'   => '#invest',
	),
);

$selling_services = array(
	array(
		'title' => 'Valuation Mastery',
		'copy'  => 'Discover the true worth of your property with our expert valuation services.',
		'icon'  => 'service-valuation',
	),
	array(
		'title' => 'Strategic Marketing',
		'copy'  => 'Selling a property requires more than a listing; it demands a tailored marketing strategy.',
		'icon'  => 'service-marketing',
	),
	array(
		'title' => 'Negotiation Wizardry',
		'copy'  => 'Negotiating the best deal is an art, and our negotiation experts are masters of it.',
		'icon'  => 'service-negotiation',
	),
	array(
		'title' => 'Closing Success',
		'copy'  => 'A successful sale is not complete until the closing. We guide you through every detail.',
		'icon'  => 'service-closing',
	),
);

$management_services = array(
	array(
		'title' => 'Tenant Harmony',
		'copy'  => 'Our tenant management services ensure smooth relationships and reliable occupancy.',
		'icon'  => 'service-tenant',
	),
	array(
		'title' => 'Maintenance Ease',
		'copy'  => 'We coordinate routine care and urgent repairs so your property stays in top condition.',
		'icon'  => 'service-maintenance',
	),
	array(
		'title' => 'Financial Peace of Mind',
		'copy'  => 'Clear reporting, rent collection, and expense oversight keep the numbers transparent.',
		'icon'  => 'service-finance',
	),
	array(
		'title' => 'Legal Guardian',
		'copy'  => 'We help keep your property aligned with relevant regulations and lease obligations.',
		'icon'  => 'service-legal',
	),
);

$investment_services = array(
	array(
		'title' => 'Market Insight',
		'copy'  => 'Stay ahead of market trends with our expert market analysis. We provide in-depth insights into real estate market conditions.',
		'icon'  => 'service-market',
	),
	array(
		'title' => 'ROI Assessment',
		'copy'  => 'Make investment decisions with confidence. We evaluate each opportunity for potential returns and long-term fit.',
		'icon'  => 'service-roi',
	),
	array(
		'title' => 'Customized Strategies',
		'copy'  => 'Every investor is unique. We develop strategies aligned with your financial goals and risk profile.',
		'icon'  => 'service-strategy',
	),
	array(
		'title' => 'Diversification Mastery',
		'copy'  => 'Build a resilient portfolio by balancing property types, locations, and investment horizons.',
		'icon'  => 'service-diversification',
	),
);
?>
<main id="primary" class="site-main services-page">
	<section class="page-intro" aria-labelledby="services-title">
		<div class="page-intro__inner site-shell">
			<h1 id="services-title"><?php esc_html_e( 'Elevate Your Real Estate Experience', 'estatein' ); ?></h1>
			<p><?php esc_html_e( "Welcome to Estatein, where your real estate aspirations meet expert guidance. Explore our comprehensive range of services, each designed to elevate your property journey, whether you're buying, selling, managing, or investing.", 'estatein' ); ?></p>
		</div>
	</section>

	<nav class="feature-shortcuts" aria-label="<?php esc_attr_e( 'Property services', 'estatein' ); ?>">
		<ul>
			<?php foreach ( $shortcuts as $shortcut ) : ?>
				<li>
					<a href="<?php echo esc_url( $shortcut['url'] ); ?>">
						<span class="feature-shortcuts__icon"><?php estatein_icon( $shortcut['icon'] ); ?></span>
						<span><?php echo esc_html( $shortcut['title'] ); ?></span>
						<?php estatein_icon( 'arrow-up-right', 'feature-shortcuts__arrow' ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</nav>

	<section id="sell" class="page-section site-shell service-section" aria-labelledby="sell-title">
		<?php
		get_template_part(
			'template-parts/components/section-heading',
			null,
			array(
				'id'    => 'sell-title',
				'title' => 'Unlock Property Value',
				'copy'  => 'Selling a property should be a rewarding experience, and at Estatein, we make sure it is. Our Property Selling Service is designed to maximize the value of your property and secure the best possible outcome.',
			)
		);
		?>
		<div class="service-grid">
			<?php foreach ( $selling_services as $service ) : ?>
				<article class="service-card">
					<header><span class="service-card__icon"><?php estatein_icon( $service['icon'] ); ?></span><h3><?php echo esc_html( $service['title'] ); ?></h3></header>
					<p><?php echo esc_html( $service['copy'] ); ?></p>
				</article>
			<?php endforeach; ?>
			<aside class="service-promo service-promo--wide">
				<div><h3><?php esc_html_e( 'Unlock the Value of Your Property Today', 'estatein' ); ?></h3><a class="button button--secondary" href="<?php echo esc_url( estatein_page_url( 'contact' ) ); ?>"><?php esc_html_e( 'Learn More', 'estatein' ); ?></a></div>
				<p><?php esc_html_e( 'Ready to unlock the true value of your property? Explore our Property Selling Service and let us help you achieve the best possible deal.', 'estatein' ); ?></p>
			</aside>
		</div>
	</section>

	<section id="manage" class="page-section site-shell service-section" aria-labelledby="manage-title">
		<?php
		get_template_part(
			'template-parts/components/section-heading',
			null,
			array(
				'id'    => 'manage-title',
				'title' => 'Effortless Property Management',
				'copy'  => "Owning a property should be a pleasure, not a hassle. Estatein's Property Management Service takes care of every detail, from tenant management to maintenance and reporting.",
			)
		);
		?>
		<div class="service-grid">
			<?php foreach ( $management_services as $service ) : ?>
				<article class="service-card">
					<header><span class="service-card__icon"><?php estatein_icon( $service['icon'] ); ?></span><h3><?php echo esc_html( $service['title'] ); ?></h3></header>
					<p><?php echo esc_html( $service['copy'] ); ?></p>
				</article>
			<?php endforeach; ?>
			<aside class="service-promo service-promo--wide">
				<div><h3><?php esc_html_e( 'Experience Effortless Property Management', 'estatein' ); ?></h3><a class="button button--secondary" href="<?php echo esc_url( estatein_page_url( 'contact' ) ); ?>"><?php esc_html_e( 'Learn More', 'estatein' ); ?></a></div>
				<p><?php esc_html_e( 'Ready to experience hassle-free property management? Explore our service and let us handle the complexities while you enjoy the benefits of property ownership.', 'estatein' ); ?></p>
			</aside>
		</div>
	</section>

	<section id="invest" class="page-section site-shell investment-section" aria-labelledby="invest-title">
		<div class="investment-section__intro">
			<?php
			get_template_part(
				'template-parts/components/section-heading',
				null,
				array(
					'id'    => 'invest-title',
					'title' => 'Smart Investments, Informed Decisions',
					'copy'  => "Building a real estate portfolio requires a strategic approach. Estatein's Investment Advisory Service empowers you to make smart investments and informed decisions.",
				)
			);
			?>
			<aside class="service-promo service-promo--stacked">
				<h3><?php esc_html_e( 'Unlock Your Investment Potential', 'estatein' ); ?></h3>
				<p><?php esc_html_e( 'Explore our Property Management Service categories and let us handle the complexities while you enjoy the benefits of property ownership.', 'estatein' ); ?></p>
				<a class="button button--secondary" href="<?php echo esc_url( estatein_page_url( 'contact' ) ); ?>"><?php esc_html_e( 'Learn More', 'estatein' ); ?></a>
			</aside>
		</div>
		<div class="investment-grid">
			<?php foreach ( $investment_services as $service ) : ?>
				<article class="service-card">
					<header><span class="service-card__icon"><?php estatein_icon( $service['icon'] ); ?></span><h3><?php echo esc_html( $service['title'] ); ?></h3></header>
					<p><?php echo esc_html( $service['copy'] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</section>
</main>
<?php
get_footer();
