<?php
/**
 * Dismissible announcement strip.
 *
 * @package Estatein
 */

?>
<aside class="announcement" aria-label="<?php esc_attr_e( 'Announcement', 'estatein' ); ?>" data-announcement>
	<div class="announcement__inner site-shell">
		<p>
			<span aria-hidden="true">✨</span>
			<?php esc_html_e( 'Discover Your Dream Property with Estatein.', 'estatein' ); ?>
			<a href="<?php echo esc_url( estatein_page_url( 'properties' ) ); ?>"><?php esc_html_e( 'Learn More', 'estatein' ); ?></a>
		</p>
		<button class="icon-button announcement__close" type="button" data-announcement-close>
			<span class="screen-reader-text"><?php esc_html_e( 'Dismiss announcement', 'estatein' ); ?></span>
			<?php estatein_icon( 'close' ); ?>
		</button>
	</div>
</aside>
