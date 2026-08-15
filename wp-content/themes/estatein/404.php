<?php
/**
 * Not-found page.
 *
 * @package Estatein
 */

get_header();
?>
<main id="primary" class="site-main error-page">
	<section class="page-section site-shell empty-state empty-state--large" aria-labelledby="not-found-title">
		<p class="error-page__code" aria-hidden="true">404</p>
		<h1 id="not-found-title"><?php esc_html_e( "This address isn't on the map", 'estatein' ); ?></h1>
		<p><?php esc_html_e( 'The page may have moved, or the link may be out of date. Search Estatein or continue with the current property collection.', 'estatein' ); ?></p>
		<?php get_search_form(); ?>
		<div class="button-row"><a class="button button--primary" href="<?php echo esc_url( estatein_page_url( 'properties' ) ); ?>"><?php esc_html_e( 'Browse Properties', 'estatein' ); ?></a><a class="button button--secondary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Return Home', 'estatein' ); ?></a></div>
	</section>
</main>
<?php
get_footer();
