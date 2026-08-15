<?php
/**
 * Property detail page.
 *
 * @package Estatein
 */

get_header();

while ( have_posts() ) :
	the_post();
	$property_id      = get_the_ID();
	$address          = estatein_property_field( $property_id, 'address', 'Malibu, California' );
	$price            = estatein_property_field( $property_id, 'price', 1250000 );
	$bedrooms         = estatein_property_field( $property_id, 'bedrooms', 4 );
	$bathrooms        = estatein_property_field( $property_id, 'bathrooms', 3 );
	$area             = estatein_property_field( $property_id, 'area', 2500 );
	$property_content = get_the_content();
	$description      = $property_content ? $property_content : __( 'Discover your own piece of paradise with the Seaside Serenity Villa. With an open floor plan, breathtaking ocean views from every room, and direct access to a pristine sandy beach, this property is the epitome of coastal living.', 'estatein' );
	$raw_features     = estatein_property_field(
		$property_id,
		'features',
		array(
			'Expansive oceanfront terrace for outdoor entertaining',
			'Gourmet kitchen with top-of-the-line appliances',
			'Private beach access for morning strolls and sunset views',
			'Master suite with a spa-inspired bathroom and ocean-facing balcony',
			'Private garage and ample storage space',
		)
	);
	$features         = is_array( $raw_features ) ? $raw_features : preg_split( '/\r\n|\r|\n/', (string) $raw_features );
	$transfer_tax     = estatein_property_field( $property_id, 'pricing_transfer_tax', 25000 );
	$legal_fees       = estatein_property_field( $property_id, 'pricing_legal_fees', 3000 );
	$inspection       = estatein_property_field( $property_id, 'pricing_inspection', 500 );
	$insurance        = estatein_property_field( $property_id, 'pricing_insurance', 1200 );
	$monthly_fees     = estatein_property_field( $property_id, 'pricing_monthly_fees', 300 );
	$deposit          = estatein_property_field( $property_id, 'pricing_initial_deposit', 250000 );
	$mortgage         = estatein_property_field( $property_id, 'pricing_mortgage_payment', 0 );
	$pricing_note     = estatein_property_field( $property_id, 'pricing_notes', '' );

	$pricing_groups = array(
		array(
			'title' => 'Additional Fees',
			'items' => array(
				array(
					'label' => 'Property Transfer Tax',
					'value' => estatein_format_price( $transfer_tax ),
					'note'  => 'Based on the sale price and local regulations',
				),
				array(
					'label' => 'Legal Fees',
					'value' => estatein_format_price( $legal_fees ),
					'note'  => 'Approximate cost for legal services, including title transfer',
				),
				array(
					'label' => 'Home Inspection',
					'value' => estatein_format_price( $inspection ),
					'note'  => 'Recommended for due diligence',
				),
				array(
					'label' => 'Property Insurance',
					'value' => estatein_format_price( $insurance ),
					'note'  => 'Annual cost for comprehensive property insurance',
				),
				array(
					'label' => 'Mortgage Fees',
					'value' => 'Varies',
					'note'  => 'If applicable, consult with your lender',
				),
			),
		),
		array(
			'title' => 'Monthly Costs',
			'items' => array(
				array(
					'label' => 'Property Taxes',
					'value' => '$1,250',
					'note'  => 'Approximate monthly property tax based on the sale price',
				),
				array(
					'label' => "Homeowners' Association Fee",
					'value' => estatein_format_price( $monthly_fees ),
					'note'  => 'Monthly fee for common-area maintenance and amenities',
				),
			),
		),
		array(
			'title' => 'Total Initial Costs',
			'items' => array(
				array(
					'label' => 'Listing Price',
					'value' => estatein_format_price( $price ),
					'note'  => '',
				),
				array(
					'label' => 'Additional Fees',
					'value' => estatein_format_price( (float) $transfer_tax + (float) $legal_fees + (float) $inspection + (float) $insurance ),
					'note'  => 'Property transfer tax, legal fees, inspection, insurance',
				),
				array(
					'label' => 'Down Payment',
					'value' => estatein_format_price( $deposit ),
					'note'  => 'Initial deposit',
				),
				array(
					'label' => 'Mortgage Amount',
					'value' => estatein_format_price( max( 0, (float) $price - (float) $deposit ) ),
					'note'  => 'If applicable',
				),
			),
		),
		array(
			'title' => 'Monthly Expenses',
			'items' => array(
				array(
					'label' => 'Property Taxes',
					'value' => '$1,250',
					'note'  => '',
				),
				array(
					'label' => "Homeowners' Association Fee",
					'value' => estatein_format_price( $monthly_fees ),
					'note'  => '',
				),
				array(
					'label' => 'Mortgage Payment',
					'value' => $mortgage ? estatein_format_price( $mortgage ) : 'Varies',
					'note'  => 'Based on terms and interest rate',
				),
				array(
					'label' => 'Property Insurance',
					'value' => estatein_format_price( round( (float) $insurance / 12 ) ),
					'note'  => 'Approximate monthly equivalent',
				),
			),
		),
	);
	$faqs           = array(
		array(
			'question' => 'What is included in the property price?',
			'answer'   => 'The listing price covers the property itself. Taxes, legal work, inspections, insurance, financing, and applicable association fees are shown separately in the pricing breakdown.',
		),
		array(
			'question' => 'Can I schedule a private viewing?',
			'answer'   => 'Yes. Send the inquiry form with your preferred timing and an Estatein advisor will contact you to coordinate a private viewing.',
		),
		array(
			'question' => 'Is financing available for this property?',
			'answer'   => 'Financing depends on your circumstances and lender approval. We can help connect you with qualified lending partners and provide the property information they require.',
		),
	);
	?>
	<main id="primary" class="site-main property-detail-page">
		<article <?php post_class( 'property-detail' ); ?>>
			<header class="property-detail__header site-shell">
				<div class="property-detail__identity">
					<h1><?php the_title(); ?></h1>
					<p><?php estatein_icon( 'location' ); ?><span><?php echo esc_html( $address ); ?></span></p>
				</div>
				<p class="property-detail__price"><span><?php esc_html_e( 'Price', 'estatein' ); ?></span><strong><?php echo esc_html( estatein_format_price( $price ) ); ?></strong></p>
			</header>

			<section class="site-shell property-gallery-section" aria-label="<?php esc_attr_e( 'Property gallery', 'estatein' ); ?>">
				<?php get_template_part( 'template-parts/components/property-gallery', null, array( 'post_id' => $property_id ) ); ?>
			</section>

			<section class="page-section site-shell property-overview" aria-label="<?php esc_attr_e( 'Property details', 'estatein' ); ?>">
				<div class="property-description">
					<h2><?php esc_html_e( 'Description', 'estatein' ); ?></h2>
					<div class="property-description__copy"><?php echo wp_kses_post( wpautop( $description ) ); ?></div>
					<dl class="property-facts">
						<div><dt><?php estatein_icon( 'bed' ); ?><?php esc_html_e( 'Bedrooms', 'estatein' ); ?></dt><dd><?php echo esc_html( str_pad( (string) $bedrooms, 2, '0', STR_PAD_LEFT ) ); ?></dd></div>
						<div><dt><?php estatein_icon( 'bath' ); ?><?php esc_html_e( 'Bathrooms', 'estatein' ); ?></dt><dd><?php echo esc_html( str_pad( (string) $bathrooms, 2, '0', STR_PAD_LEFT ) ); ?></dd></div>
						<div><dt><?php estatein_icon( 'area' ); ?><?php esc_html_e( 'Area', 'estatein' ); ?></dt><dd><?php echo esc_html( number_format_i18n( (float) $area ) . ' Square Feet' ); ?></dd></div>
					</dl>
				</div>
				<div class="property-features">
					<h2><?php esc_html_e( 'Key Features and Amenities', 'estatein' ); ?></h2>
					<ul>
						<?php foreach ( array_filter( $features ) as $feature ) : ?>
							<li><?php estatein_icon( 'spark-feature' ); ?><span><?php echo esc_html( is_array( $feature ) ? ( $feature['feature'] ?? reset( $feature ) ) : $feature ); ?></span></li>
						<?php endforeach; ?>
					</ul>
				</div>
			</section>

			<section class="page-section site-shell property-inquiry-section" aria-labelledby="inquiry-title">
				<?php
				get_template_part(
					'template-parts/components/section-heading',
					null,
					array(
						'id'       => 'inquiry-title',
						/* translators: %s: Property title. */
						'title'    => sprintf( __( 'Inquire About %s', 'estatein' ), get_the_title() ),
						'copy'     => 'Interested in this property? Complete the form and our real estate experts will contact you with the details and next steps.',
						'modifier' => 'compact',
					)
				);
				get_template_part( 'template-parts/forms/property-inquiry', null, array( 'post_id' => $property_id ) );
				?>
			</section>

			<section class="page-section site-shell pricing-section" aria-labelledby="pricing-title">
				<?php
				get_template_part(
					'template-parts/components/section-heading',
					null,
					array(
						'id'    => 'pricing-title',
						'title' => 'Comprehensive Pricing Details',
						'copy'  => 'At Estatein, transparency is key. We want you to have a clear understanding of all costs associated with your property investment.',
					)
				);
				?>
				<aside class="pricing-note"><strong><?php esc_html_e( 'Note', 'estatein' ); ?></strong><p><?php echo esc_html( $pricing_note ? $pricing_note : __( 'The figures provided above are estimates and may vary depending on the property, location, lender, and circumstances.', 'estatein' ) ); ?></p></aside>
				<div class="pricing-layout">
					<aside class="listing-price"><p><?php esc_html_e( 'Listing Price', 'estatein' ); ?></p><strong><?php echo esc_html( estatein_format_price( $price ) ); ?></strong></aside>
					<div class="pricing-groups">
						<?php foreach ( $pricing_groups as $group ) : ?>
							<section class="pricing-card">
								<header><h3><?php echo esc_html( $group['title'] ?? '' ); ?></h3><a class="button button--secondary" href="<?php echo esc_url( estatein_page_url( 'contact' ) ); ?>"><?php esc_html_e( 'Learn More', 'estatein' ); ?></a></header>
								<dl>
									<?php foreach ( $group['items'] ?? array() as $item ) : ?>
										<div><dt><?php echo esc_html( $item['label'] ?? '' ); ?></dt><dd><strong><?php echo esc_html( $item['value'] ?? '' ); ?></strong>
										<?php
										if ( ! empty( $item['note'] ) ) :
											?>
											<span><?php echo esc_html( $item['note'] ); ?></span><?php endif; ?></dd></div>
									<?php endforeach; ?>
								</dl>
							</section>
						<?php endforeach; ?>
					</div>
				</div>
			</section>

			<section class="page-section site-shell" aria-labelledby="property-faq-title">
				<?php
				get_template_part(
					'template-parts/components/section-heading',
					null,
					array(
						'id'    => 'property-faq-title',
						'title' => 'Frequently Asked Questions',
						'copy'  => 'Review common questions about this listing, viewings, pricing, and financing. Contact us if you need anything else.',
					)
				);
				get_template_part(
					'template-parts/components/faq-list',
					null,
					array(
						'items' => $faqs,
						'id'    => 'property-faq-list',
					)
				);
				?>
			</section>
		</article>
	</main>
	<?php
endwhile;

get_footer();
