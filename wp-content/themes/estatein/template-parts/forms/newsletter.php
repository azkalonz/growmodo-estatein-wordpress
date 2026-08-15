<?php
/**
 * Footer newsletter form.
 *
 * @package Estatein
 */

$email = function_exists( 'estatein_core_old_input' ) ? estatein_core_old_input( 'subscribe', 'email', '' ) : '';
?>
<div class="newsletter">
	<?php estatein_render_form_status( 'subscribe' ); ?>
	<form class="newsletter__form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
		<input type="hidden" name="action" value="estatein_subscribe">
		<input type="hidden" name="_estatein_redirect" value="<?php echo esc_url( home_url( '/' ) ); ?>">
		<?php wp_nonce_field( 'estatein_subscribe', 'estatein_subscribe_nonce' ); ?>
		<div class="honeypot" aria-hidden="true">
			<label for="estatein-newsletter-website"><?php esc_html_e( 'Website', 'estatein' ); ?></label>
			<input id="estatein-newsletter-website" name="website" type="text" tabindex="-1" autocomplete="off">
		</div>
		<label class="screen-reader-text" for="estatein-newsletter-email"><?php esc_html_e( 'Email address', 'estatein' ); ?></label>
		<span class="newsletter__email-icon" aria-hidden="true"><?php estatein_icon( 'email' ); ?></span>
		<input id="estatein-newsletter-email" name="email" type="email" autocomplete="email" value="<?php echo esc_attr( $email ); ?>" placeholder="<?php esc_attr_e( 'Enter Your Email', 'estatein' ); ?>" required>
		<button class="icon-button" type="submit">
			<span class="screen-reader-text"><?php esc_html_e( 'Subscribe', 'estatein' ); ?></span>
			<?php estatein_icon( 'send' ); ?>
		</button>
	</form>
</div>
