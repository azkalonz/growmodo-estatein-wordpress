<?php
/**
 * Search form.
 *
 * @package Estatein
 */

$search_id = wp_unique_id( 'site-search-' );
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label for="<?php echo esc_attr( $search_id ); ?>">
		<span class="screen-reader-text"><?php esc_html_e( 'Search for:', 'estatein' ); ?></span>
		<input id="<?php echo esc_attr( $search_id ); ?>" type="search" class="search-field" placeholder="<?php esc_attr_e( 'Search Estatein…', 'estatein' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s">
	</label>
	<button class="button button--primary" type="submit"><?php estatein_icon( 'search' ); ?><span><?php esc_html_e( 'Search', 'estatein' ); ?></span></button>
</form>
