<?php
/**
 * Property-specific inquiry form.
 *
 * @package Estatein
 */

$property_id   = isset( $args['post_id'] ) ? (int) $args['post_id'] : get_the_ID();
$property_name = $property_id ? get_the_title( $property_id ) : __( 'Seaside Serenity Villa', 'estatein' );
$form_id       = 'property-inquiry-' . max( 1, $property_id );
$old           = static function ( $key, $fallback = '' ) {
	return function_exists( 'estatein_core_old_input' ) ? estatein_core_old_input( 'inquiry', $key, $fallback ) : $fallback;
};
$form_redirect = get_permalink( $property_id );
$form_redirect = $form_redirect ? $form_redirect : home_url( '/' );
$privacy_url   = get_privacy_policy_url();
$privacy_url   = $privacy_url ? $privacy_url : home_url( '/privacy-policy/' );
/* translators: %s: Property title. */
$default_message = sprintf( __( 'I am interested in %s.', 'estatein' ), $property_name );
?>
<?php estatein_render_form_status( 'inquiry' ); ?>
<form id="<?php echo esc_attr( $form_id ); ?>" class="estatein-form inquiry-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" novalidate data-validate-form>
	<input type="hidden" name="action" value="estatein_submit_inquiry">
	<input type="hidden" name="property_id" value="<?php echo esc_attr( $property_id ); ?>">
	<input type="hidden" name="property_name" value="<?php echo esc_attr( $property_name ); ?>">
	<input type="hidden" name="_estatein_redirect" value="<?php echo esc_url( $form_redirect ); ?>">
	<?php wp_nonce_field( 'estatein_submit_inquiry', 'estatein_inquiry_nonce' ); ?>
	<div class="honeypot" aria-hidden="true">
		<label for="<?php echo esc_attr( $form_id ); ?>-website"><?php esc_html_e( 'Website', 'estatein' ); ?></label>
		<input id="<?php echo esc_attr( $form_id ); ?>-website" name="website" type="text" tabindex="-1" autocomplete="off">
	</div>

	<div class="form-grid">
		<div class="field">
			<label for="<?php echo esc_attr( $form_id ); ?>-first-name"><?php esc_html_e( 'First Name', 'estatein' ); ?></label>
			<input id="<?php echo esc_attr( $form_id ); ?>-first-name" name="first_name" type="text" autocomplete="given-name" value="<?php echo esc_attr( $old( 'first_name' ) ); ?>" placeholder="<?php esc_attr_e( 'Enter First Name', 'estatein' ); ?>" required>
			<span class="field__error" aria-live="polite"></span>
		</div>
		<div class="field">
			<label for="<?php echo esc_attr( $form_id ); ?>-last-name"><?php esc_html_e( 'Last Name', 'estatein' ); ?></label>
			<input id="<?php echo esc_attr( $form_id ); ?>-last-name" name="last_name" type="text" autocomplete="family-name" value="<?php echo esc_attr( $old( 'last_name' ) ); ?>" placeholder="<?php esc_attr_e( 'Enter Last Name', 'estatein' ); ?>" required>
			<span class="field__error" aria-live="polite"></span>
		</div>
		<div class="field">
			<label for="<?php echo esc_attr( $form_id ); ?>-email"><?php esc_html_e( 'Email', 'estatein' ); ?></label>
			<input id="<?php echo esc_attr( $form_id ); ?>-email" name="email" type="email" autocomplete="email" value="<?php echo esc_attr( $old( 'email' ) ); ?>" placeholder="<?php esc_attr_e( 'Enter your Email', 'estatein' ); ?>" required>
			<span class="field__error" aria-live="polite"></span>
		</div>
		<div class="field">
			<label for="<?php echo esc_attr( $form_id ); ?>-phone"><?php esc_html_e( 'Phone', 'estatein' ); ?></label>
			<input id="<?php echo esc_attr( $form_id ); ?>-phone" name="phone" type="tel" autocomplete="tel" value="<?php echo esc_attr( $old( 'phone' ) ); ?>" placeholder="<?php esc_attr_e( 'Enter Phone Number', 'estatein' ); ?>" required>
			<span class="field__error" aria-live="polite"></span>
		</div>
		<div class="field field--full inquiry-form__property">
			<label for="<?php echo esc_attr( $form_id ); ?>-property"><?php esc_html_e( 'Selected Property', 'estatein' ); ?></label>
			<div class="selected-property-control">
				<input id="<?php echo esc_attr( $form_id ); ?>-property" type="text" value="<?php echo esc_attr( $property_name ); ?>" readonly>
				<?php estatein_icon( 'location' ); ?>
			</div>
		</div>
		<div class="field field--full">
			<label for="<?php echo esc_attr( $form_id ); ?>-message"><?php esc_html_e( 'Message', 'estatein' ); ?></label>
			<textarea id="<?php echo esc_attr( $form_id ); ?>-message" name="message" rows="5" placeholder="<?php esc_attr_e( 'Enter your Message here...', 'estatein' ); ?>" required><?php echo esc_textarea( $old( 'message', $default_message ) ); ?></textarea>
			<span class="field__error" aria-live="polite"></span>
		</div>
	</div>

	<div class="form-footer">
		<label class="checkbox-field" for="<?php echo esc_attr( $form_id ); ?>-terms">
			<input id="<?php echo esc_attr( $form_id ); ?>-terms" name="terms" type="checkbox" value="1" required>
			<span><?php esc_html_e( 'I agree with', 'estatein' ); ?> <a href="<?php echo esc_url( home_url( '/terms-of-use/' ) ); ?>"><?php esc_html_e( 'Terms of Use', 'estatein' ); ?></a> <?php esc_html_e( 'and', 'estatein' ); ?> <a href="<?php echo esc_url( $privacy_url ); ?>"><?php esc_html_e( 'Privacy Policy', 'estatein' ); ?></a>.</span>
		</label>
		<button class="button button--primary" type="submit"><?php esc_html_e( 'Send Your Message', 'estatein' ); ?></button>
	</div>
</form>
