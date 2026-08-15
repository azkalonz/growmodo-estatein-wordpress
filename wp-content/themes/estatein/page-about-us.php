<?php
/**
 * About Us page.
 *
 * @package Estatein
 */

get_header();

$values = array(
	array(
		'title' => 'Trust',
		'copy'  => 'Trust is the cornerstone of every successful real estate transaction.',
		'icon'  => 'value-trust',
	),
	array(
		'title' => 'Excellence',
		'copy'  => 'We set the bar high for ourselves. From the properties we list to the services we provide.',
		'icon'  => 'value-excellence',
	),
	array(
		'title' => 'Client-Centric',
		'copy'  => 'Your dreams and needs are at the center of our universe. We listen, understand, and deliver.',
		'icon'  => 'value-client',
	),
	array(
		'title' => 'Our Commitment',
		'copy'  => 'We are dedicated to providing you with the highest level of service, professionalism, and support.',
		'icon'  => 'value-commitment',
	),
);

$achievements = array(
	array(
		'title' => '3+ Years of Excellence',
		'copy'  => "With over 3 years in the industry, we've amassed a wealth of knowledge and experience.",
	),
	array(
		'title' => 'Happy Clients',
		'copy'  => 'Our greatest achievement is the satisfaction of our clients. Their success stories fuel our passion.',
	),
	array(
		'title' => 'Industry Recognition',
		'copy'  => "We've earned the respect of our peers and industry leaders, with accolades that reflect our commitment to excellence.",
	),
);

$steps = array(
	array(
		'number' => '01',
		'title'  => 'Discover a World of Possibilities',
		'copy'   => 'Your journey begins with exploring our carefully curated property listings. Use our intuitive search tools to filter by the features that matter most.',
	),
	array(
		'number' => '02',
		'title'  => 'Narrowing Down Your Choices',
		'copy'   => 'Save your favorites and compare the homes, locations, and investment opportunities that fit your goals.',
	),
	array(
		'number' => '03',
		'title'  => 'Personalized Guidance',
		'copy'   => 'Our experienced agents take time to understand your needs, answer questions, and shape a property strategy around you.',
	),
	array(
		'number' => '04',
		'title'  => 'See It for Yourself',
		'copy'   => 'Schedule viewings and experience each shortlisted property first-hand, with an Estatein advisor by your side.',
	),
	array(
		'number' => '05',
		'title'  => 'Making Informed Decisions',
		'copy'   => 'Review the facts with confidence. We provide transparent information and practical insight before you move forward.',
	),
	array(
		'number' => '06',
		'title'  => 'Getting the Best Deal',
		'copy'   => 'From negotiation to closing, our team advocates for your interests and keeps every detail moving smoothly.',
	),
);

$demo_team = array(
	array(
		'name'  => 'Max Mitchell',
		'role'  => 'Founder',
		'image' => 'images/team/max-mitchell.webp',
	),
	array(
		'name'  => 'Sarah Johnson',
		'role'  => 'Chief Real Estate Officer',
		'image' => 'images/team/sarah-johnson.webp',
	),
	array(
		'name'  => 'David Brown',
		'role'  => 'Head of Property Management',
		'image' => 'images/team/david-brown.webp',
	),
	array(
		'name'  => 'Michael Turner',
		'role'  => 'Legal Counsel',
		'image' => 'images/team/michael-turner.webp',
	),
);

$clients = array(
	array(
		'since'    => 'Since 2019',
		'name'     => 'ABC Corporation',
		'domain'   => 'Commercial Real Estate',
		'category' => 'Luxury Home Development',
		'quote'    => "Estatein's expertise in finding the perfect office space for our expanding operations was invaluable. They truly understand our business needs.",
	),
	array(
		'since'    => 'Since 2018',
		'name'     => 'GreenTech Enterprises',
		'domain'   => 'Commercial Real Estate',
		'category' => 'Retail Space',
		'quote'    => "Estatein's ability to identify prime retail locations helped us expand our brand presence. They are a trusted partner in our growth.",
	),
);
?>
<main id="primary" class="site-main about-page">
	<section id="journey" class="about-hero site-shell" aria-labelledby="about-title">
		<div class="about-hero__content">
			<img class="section-heading__spark" src="<?php echo esc_url( estatein_asset_uri( 'images/decor/spark.svg' ) ); ?>" alt="" width="55" height="24">
			<h1 id="about-title"><?php esc_html_e( 'Our Journey', 'estatein' ); ?></h1>
			<p><?php esc_html_e( "Our story is one of continuous growth and evolution. We started as a small team with big dreams, determined to create a real estate platform that transcended the ordinary. Over the years, we've expanded our reach, forged valuable partnerships, and gained the trust of countless clients.", 'estatein' ); ?></p>
			<ul class="metrics" aria-label="<?php esc_attr_e( 'Estatein at a glance', 'estatein' ); ?>">
				<li><strong>200+</strong><span><?php esc_html_e( 'Happy Customers', 'estatein' ); ?></span></li>
				<li><strong>10k+</strong><span><?php esc_html_e( 'Properties For Clients', 'estatein' ); ?></span></li>
				<li><strong>16+</strong><span><?php esc_html_e( 'Years of Experience', 'estatein' ); ?></span></li>
			</ul>
		</div>
		<div class="about-hero__media">
			<img src="<?php echo esc_url( estatein_asset_uri( 'images/about/journey.webp' ) ); ?>" alt="A hand presenting a detailed model of a contemporary home" width="1570" height="1092" fetchpriority="high">
		</div>
	</section>

	<section id="values" class="page-section site-shell split-section values-section" aria-labelledby="values-title">
		<?php
		get_template_part(
			'template-parts/components/section-heading',
			null,
			array(
				'id'    => 'values-title',
				'title' => 'Our Values',
				'copy'  => 'Our story is one of continuous growth and evolution. We started as a small team with big dreams, determined to create a real estate platform that transcended the ordinary.',
			)
		);
		?>
		<div class="values-grid">
			<?php foreach ( $values as $value ) : ?>
				<article class="value-card">
					<header><span class="value-card__icon"><?php estatein_icon( $value['icon'] ); ?></span><h3><?php echo esc_html( $value['title'] ); ?></h3></header>
					<p><?php echo esc_html( $value['copy'] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</section>

	<section id="achievements" class="page-section site-shell" aria-labelledby="achievements-title">
		<?php
		get_template_part(
			'template-parts/components/section-heading',
			null,
			array(
				'id'    => 'achievements-title',
				'title' => 'Our Achievements',
				'copy'  => 'Our story is marked by milestones of success. Here are some of our notable achievements along the way.',
			)
		);
		?>
		<div class="achievement-grid">
			<?php foreach ( $achievements as $achievement ) : ?>
				<article class="achievement-card">
					<h3><?php echo esc_html( $achievement['title'] ); ?></h3>
					<p><?php echo esc_html( $achievement['copy'] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</section>

	<section id="process" class="page-section site-shell" aria-labelledby="process-title">
		<?php
		get_template_part(
			'template-parts/components/section-heading',
			null,
			array(
				'id'    => 'process-title',
				'title' => 'Navigating the Estatein Experience',
				'copy'  => "At Estatein, we've designed a straightforward process to help you find and secure the right property with confidence.",
			)
		);
		?>
		<ol class="process-grid">
			<?php foreach ( $steps as $step ) : ?>
				<li class="process-card">
					<p class="process-card__number"><?php echo esc_html( 'Step ' . $step['number'] ); ?></p>
					<div class="process-card__body">
						<h3><?php echo esc_html( $step['title'] ); ?></h3>
						<p><?php echo esc_html( $step['copy'] ); ?></p>
					</div>
				</li>
			<?php endforeach; ?>
		</ol>
	</section>

	<section id="team" class="page-section site-shell" aria-labelledby="team-title">
		<?php
		get_template_part(
			'template-parts/components/section-heading',
			null,
			array(
				'id'    => 'team-title',
				'title' => 'Meet the Estatein Team',
				'copy'  => 'Meet the people whose experience, care, and attention to detail turn real-estate goals into successful outcomes.',
			)
		);

		$team_query = new WP_Query(
			array(
				'post_type'      => 'estatein_team_member',
				'posts_per_page' => 4,
				'post_status'    => 'publish',
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
				'no_found_rows'  => true,
			)
		);
		?>
		<div class="team-grid">
			<?php if ( $team_query->have_posts() ) : ?>
				<?php while ( $team_query->have_posts() ) : ?>
					<?php
					$team_query->the_post();
					$member_role   = function_exists( 'estatein_core_get_team_field' ) ? estatein_core_get_team_field( get_the_ID(), 'role', 'Estatein Advisor' ) : get_post_meta( get_the_ID(), 'estatein_role', true );
					$member_social = function_exists( 'estatein_core_get_team_field' ) ? estatein_core_get_team_field( get_the_ID(), 'twitter', 'https://x.com/' ) : get_post_meta( get_the_ID(), 'estatein_twitter', true );
					$member_email  = function_exists( 'estatein_core_get_team_field' ) ? estatein_core_get_team_field( get_the_ID(), 'email', 'info@estatein.com' ) : get_post_meta( get_the_ID(), 'estatein_email', true );
					?>
					<article class="team-card">
						<?php if ( has_post_thumbnail() ) : ?>
							<?php
							the_post_thumbnail(
								'full',
								array(
									'class'   => 'team-card__image',
									'loading' => 'lazy',
									'alt'     => '',
								)
							);
							?>
						<?php endif; ?>
						<a class="team-card__social" href="<?php echo esc_url( $member_social ? $member_social : 'https://x.com/' ); ?>" target="_blank" rel="noopener noreferrer"><span class="screen-reader-text"><?php /* translators: %s: Team member name. */ printf( esc_html__( '%s on X', 'estatein' ), esc_html( get_the_title() ) ); ?></span><?php estatein_icon( 'x' ); ?></a>
						<h3><?php the_title(); ?></h3>
						<p><?php echo esc_html( $member_role ? $member_role : __( 'Estatein Advisor', 'estatein' ) ); ?></p>
						<a class="team-card__message" href="mailto:<?php echo esc_attr( sanitize_email( $member_email ? $member_email : 'info@estatein.com' ) ); ?>"><span><?php esc_html_e( 'Say Hello 👋', 'estatein' ); ?></span><?php estatein_icon( 'send' ); ?></a>
					</article>
				<?php endwhile; ?>
				<?php wp_reset_postdata(); ?>
			<?php else : ?>
				<?php foreach ( $demo_team as $person ) : ?>
					<article class="team-card">
						<img class="team-card__image" src="<?php echo esc_url( estatein_asset_uri( $person['image'] ) ); ?>" alt="<?php echo esc_attr( $person['name'] ); ?>" width="600" height="512" loading="lazy">
						<a class="team-card__social" href="https://x.com/" target="_blank" rel="noopener noreferrer"><span class="screen-reader-text"><?php echo esc_html( $person['name'] . ' on X' ); ?></span><?php estatein_icon( 'x' ); ?></a>
						<h3><?php echo esc_html( $person['name'] ); ?></h3>
						<p><?php echo esc_html( $person['role'] ); ?></p>
						<a class="team-card__message" href="mailto:info@estatein.com"><span><?php esc_html_e( 'Say Hello 👋', 'estatein' ); ?></span><?php estatein_icon( 'send' ); ?></a>
					</article>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</section>

	<section id="clients" class="page-section site-shell" aria-labelledby="clients-title">
		<?php
		get_template_part(
			'template-parts/components/section-heading',
			null,
			array(
				'id'    => 'clients-title',
				'title' => 'Our Valued Clients',
				'copy'  => 'At Estatein, we have had the privilege of working with a diverse range of clients across industries. Here are some of the people and organizations we value.',
			)
		);
		?>
		<div id="client-rail" class="card-rail client-rail" data-rail>
			<?php foreach ( $clients as $client ) : ?>
				<article class="client-card rail-card">
					<header><div><p><?php echo esc_html( $client['since'] ); ?></p><h3><?php echo esc_html( $client['name'] ); ?></h3></div><a class="button button--secondary" href="<?php echo esc_url( estatein_page_url( 'contact' ) ); ?>"><?php esc_html_e( 'Visit Website', 'estatein' ); ?></a></header>
					<dl class="client-card__meta">
						<div><dt><?php estatein_icon( 'domain' ); ?><?php esc_html_e( 'Domain', 'estatein' ); ?></dt><dd><?php echo esc_html( $client['domain'] ); ?></dd></div>
						<div><dt><?php estatein_icon( 'category' ); ?><?php esc_html_e( 'Category', 'estatein' ); ?></dt><dd><?php echo esc_html( $client['category'] ); ?></dd></div>
					</dl>
					<blockquote><p><?php esc_html_e( 'What They Said', 'estatein' ); ?></p><q><?php echo esc_html( $client['quote'] ); ?></q></blockquote>
				</article>
			<?php endforeach; ?>
		</div>
		<?php
		get_template_part(
			'template-parts/components/rail-controls',
			null,
			array(
				'target' => 'client-rail',
				'label'  => 'clients',
				'total'  => '02',
			)
		);
		?>
	</section>
</main>
<?php
get_footer();
