<?php
/**
 * Accessible numbered pagination with persistent filters.
 *
 * @package Estatein
 */

$property_request = wp_unslash( $_GET ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public read-only filters.
$query            = $args['query'] ?? $GLOBALS['wp_query'];
$current          = max( 1, (int) $query->get( 'paged' ), isset( $property_request['paged'] ) ? absint( $property_request['paged'] ) : 1 );
$big              = 999999999;
$links            = paginate_links(
	array(
		'base'      => str_replace( (string) $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
		'current'   => $current,
		'total'     => max( 1, (int) $query->max_num_pages ),
		'type'      => 'array',
		'prev_text' => sprintf( '<span aria-hidden="true">%s</span><span class="screen-reader-text">%s</span>', '&larr;', esc_html__( 'Previous page', 'estatein' ) ),
		'next_text' => sprintf( '<span aria-hidden="true">%s</span><span class="screen-reader-text">%s</span>', '&rarr;', esc_html__( 'Next page', 'estatein' ) ),
		'add_args'  => array_filter(
			array(
				'keyword'                => isset( $property_request['keyword'] ) ? sanitize_text_field( $property_request['keyword'] ) : '',
				'estatein_location'      => isset( $property_request['estatein_location'] ) ? sanitize_key( $property_request['estatein_location'] ) : '',
				'estatein_property_type' => isset( $property_request['estatein_property_type'] ) ? sanitize_key( $property_request['estatein_property_type'] ) : '',
				'price_min'              => isset( $property_request['price_min'] ) ? absint( $property_request['price_min'] ) : '',
				'price_max'              => isset( $property_request['price_max'] ) ? absint( $property_request['price_max'] ) : '',
				'area_min'               => isset( $property_request['area_min'] ) ? absint( $property_request['area_min'] ) : '',
				'area_max'               => isset( $property_request['area_max'] ) ? absint( $property_request['area_max'] ) : '',
			)
		),
	)
);

if ( ! $links ) {
	return;
}
?>
<nav class="pagination" aria-label="<?php esc_attr_e( 'Property pages', 'estatein' ); ?>">
	<ul>
		<?php foreach ( $links as $pagination_link ) : ?>
			<li><?php echo wp_kses_post( $pagination_link ); ?></li>
		<?php endforeach; ?>
	</ul>
</nav>
