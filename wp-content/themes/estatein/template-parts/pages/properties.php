<?php
/**
 * Shared property archive/page content.
 *
 * @package Estatein
 */

$property_request = wp_unslash( $_GET ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public read-only filters.
$is_archive       = ! empty( $args['archive'] );
$filters          = array(
	'keyword'                => isset( $property_request['keyword'] ) ? sanitize_text_field( $property_request['keyword'] ) : '',
	'estatein_location'      => isset( $property_request['estatein_location'] ) ? sanitize_key( $property_request['estatein_location'] ) : '',
	'estatein_property_type' => isset( $property_request['estatein_property_type'] ) ? sanitize_key( $property_request['estatein_property_type'] ) : '',
	'price_min'              => isset( $property_request['price_min'] ) ? absint( $property_request['price_min'] ) : '',
	'price_max'              => isset( $property_request['price_max'] ) ? absint( $property_request['price_max'] ) : '',
	'area_min'               => isset( $property_request['area_min'] ) ? absint( $property_request['area_min'] ) : '',
	'area_max'               => isset( $property_request['area_max'] ) ? absint( $property_request['area_max'] ) : '',
);

if ( $is_archive ) {
	$property_loop = $GLOBALS['wp_query'];
} else {
	$property_page = max( 1, get_query_var( 'paged' ), isset( $property_request['paged'] ) ? absint( $property_request['paged'] ) : 1 );
	$query_args    = array(
		'post_type'      => 'estatein_property',
		'post_status'    => 'publish',
		'posts_per_page' => 9,
		'paged'          => $property_page,
		's'              => $filters['keyword'],
	);
	$tax_query     = array();
	$meta_query    = array();

	if ( $filters['estatein_location'] ) {
		$tax_query[] = array(
			'taxonomy' => 'estatein_location',
			'field'    => 'slug',
			'terms'    => $filters['estatein_location'],
		);
	}
	if ( $filters['estatein_property_type'] ) {
		$tax_query[] = array(
			'taxonomy' => 'estatein_property_type',
			'field'    => 'slug',
			'terms'    => $filters['estatein_property_type'],
		);
	}
	if ( $filters['price_min'] || $filters['price_max'] ) {
		$meta_query[] = array(
			'key'     => 'estatein_price',
			'value'   => array( $filters['price_min'] ? $filters['price_min'] : 0, $filters['price_max'] ? $filters['price_max'] : PHP_INT_MAX ),
			'compare' => 'BETWEEN',
			'type'    => 'NUMERIC',
		);
	}
	if ( $filters['area_min'] || $filters['area_max'] ) {
		$meta_query[] = array(
			'key'     => 'estatein_area',
			'value'   => array( $filters['area_min'] ? $filters['area_min'] : 0, $filters['area_max'] ? $filters['area_max'] : PHP_INT_MAX ),
			'compare' => 'BETWEEN',
			'type'    => 'NUMERIC',
		);
	}
	if ( $tax_query ) {
		$query_args['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Required bounded archive filtering.
	}
	if ( $meta_query ) {
		$query_args['meta_query'] = $meta_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Required bounded archive filtering.
	}

	$property_loop = new WP_Query( $query_args );
}

$locations = taxonomy_exists( 'estatein_location' ) ? get_terms(
	array(
		'taxonomy'   => 'estatein_location',
		'hide_empty' => false,
	)
) : array();
$types     = taxonomy_exists( 'estatein_property_type' ) ? get_terms(
	array(
		'taxonomy'   => 'estatein_property_type',
		'hide_empty' => false,
	)
) : array();
?>
<main id="primary" class="site-main properties-page">
	<section class="page-intro" aria-labelledby="properties-title">
		<div class="page-intro__inner site-shell">
			<h1 id="properties-title"><?php esc_html_e( 'Find Your Dream Property', 'estatein' ); ?></h1>
			<p><?php esc_html_e( 'Welcome to Estatein, where your dream property awaits in every corner of our beautiful world. Explore our curated selection of properties, each offering a unique story and a chance to redefine your life.', 'estatein' ); ?></p>
		</div>
	</section>

	<section id="property-search" class="property-search site-shell" aria-labelledby="property-search-title">
		<h2 id="property-search-title" class="screen-reader-text"><?php esc_html_e( 'Search and filter properties', 'estatein' ); ?></h2>
		<form class="property-search__form" action="<?php echo esc_url( estatein_page_url( 'properties' ) ); ?>" method="get">
			<div class="property-search__keyword">
				<label class="screen-reader-text" for="property-keyword"><?php esc_html_e( 'Search properties', 'estatein' ); ?></label>
				<input id="property-keyword" name="keyword" type="search" value="<?php echo esc_attr( $filters['keyword'] ); ?>" placeholder="<?php esc_attr_e( 'Search For A Property', 'estatein' ); ?>">
				<button class="button button--primary" type="submit"><?php estatein_icon( 'search' ); ?><span><?php esc_html_e( 'Find Property', 'estatein' ); ?></span></button>
			</div>

			<div class="property-filters">
				<div class="filter-control">
					<label for="property-location"><?php estatein_icon( 'location' ); ?><span class="screen-reader-text"><?php esc_html_e( 'Location', 'estatein' ); ?></span></label>
					<select id="property-location" name="estatein_location">
						<option value=""><?php esc_html_e( 'Location', 'estatein' ); ?></option>
						<?php if ( ! is_wp_error( $locations ) ) : ?>
							<?php foreach ( $locations as $location ) : ?>
								<option value="<?php echo esc_attr( $location->slug ); ?>" <?php selected( $filters['estatein_location'], $location->slug ); ?>><?php echo esc_html( $location->name ); ?></option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
				</div>
				<div class="filter-control">
					<label for="property-type"><?php estatein_icon( 'building' ); ?><span class="screen-reader-text"><?php esc_html_e( 'Property Type', 'estatein' ); ?></span></label>
					<select id="property-type" name="estatein_property_type">
						<option value=""><?php esc_html_e( 'Property Type', 'estatein' ); ?></option>
						<?php if ( ! is_wp_error( $types ) ) : ?>
							<?php foreach ( $types as $property_type ) : ?>
							<option value="<?php echo esc_attr( $property_type->slug ); ?>" <?php selected( $filters['estatein_property_type'], $property_type->slug ); ?>><?php echo esc_html( $property_type->name ); ?></option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
				</div>
				<div class="filter-control filter-control--range">
					<?php estatein_icon( 'price' ); ?>
					<label for="property-price-min"><?php esc_html_e( 'Min Price', 'estatein' ); ?></label>
					<input id="property-price-min" name="price_min" type="number" inputmode="numeric" min="0" step="50000" value="<?php echo esc_attr( $filters['price_min'] ); ?>" placeholder="0">
					<label for="property-price-max"><?php esc_html_e( 'Max Price', 'estatein' ); ?></label>
					<input id="property-price-max" name="price_max" type="number" inputmode="numeric" min="0" step="50000" value="<?php echo esc_attr( $filters['price_max'] ); ?>" placeholder="No max">
				</div>
				<div class="filter-control filter-control--range">
					<?php estatein_icon( 'area' ); ?>
					<label for="property-area-min"><?php esc_html_e( 'Min Area', 'estatein' ); ?></label>
					<input id="property-area-min" name="area_min" type="number" inputmode="numeric" min="0" step="100" value="<?php echo esc_attr( $filters['area_min'] ); ?>" placeholder="0">
					<label for="property-area-max"><?php esc_html_e( 'Max Area', 'estatein' ); ?></label>
					<input id="property-area-max" name="area_max" type="number" inputmode="numeric" min="0" step="100" value="<?php echo esc_attr( $filters['area_max'] ); ?>" placeholder="No max">
				</div>
			</div>
		</form>
	</section>

	<section class="page-section site-shell" aria-labelledby="property-results-title">
		<?php
		get_template_part(
			'template-parts/components/section-heading',
			null,
			array(
				'id'    => 'property-results-title',
				'title' => 'Discover a World of Possibilities',
				'copy'  => 'Our portfolio of properties is as diverse as your dreams. Explore a range of homes, apartments, villas, and commercial spaces curated to match different lifestyles and investment goals.',
			)
		);
		?>
		<?php
		/* translators: %s: Number of matching properties. */
		$results_label = _n( '%s property found', '%s properties found', (int) $property_loop->found_posts, 'estatein' );
		?>
		<p class="results-count" role="status"><?php printf( esc_html( $results_label ), esc_html( number_format_i18n( $property_loop->found_posts ) ) ); ?></p>
		<?php if ( $property_loop->have_posts() ) : ?>
			<div class="property-grid">
				<?php while ( $property_loop->have_posts() ) : ?>
					<?php $property_loop->the_post(); ?>
					<?php get_template_part( 'template-parts/components/property-card', null, array( 'property' => get_post() ) ); ?>
				<?php endwhile; ?>
			</div>
			<?php get_template_part( 'template-parts/components/pagination', null, array( 'query' => $property_loop ) ); ?>
			<?php if ( ! $is_archive ) : ?>
				<?php wp_reset_postdata(); ?>
			<?php endif; ?>
		<?php else : ?>
			<div class="empty-state">
				<span class="empty-state__icon"><?php estatein_icon( 'search' ); ?></span>
				<h3><?php esc_html_e( 'No properties match those filters yet', 'estatein' ); ?></h3>
				<p><?php esc_html_e( 'Try widening the price or area range, choosing a different location, or clearing the keyword.', 'estatein' ); ?></p>
				<a class="button button--secondary" href="<?php echo esc_url( estatein_page_url( 'properties' ) ); ?>"><?php esc_html_e( 'Clear Filters', 'estatein' ); ?></a>
			</div>
		<?php endif; ?>
	</section>

	<section class="page-section site-shell" aria-labelledby="property-contact-title">
		<?php
		get_template_part(
			'template-parts/components/section-heading',
			null,
			array(
				'id'    => 'property-contact-title',
				'title' => "Let's Make it Happen",
				'copy'  => 'Ready to take the first step toward your dream property? Fill out the form below and our real estate experts will get back to you with the guidance you need.',
			)
		);
			get_template_part( 'template-parts/forms/property-match', null, array( 'form_id' => 'estatein-property-contact' ) );
		?>
	</section>
</main>
