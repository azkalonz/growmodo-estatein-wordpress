<?php
/**
 * General contact form used on the Contact page.
 *
 * @package Estatein
 */

$form_id       = $args['form_id'] ?? 'estatein-contact-form';
$old           = static function ( $key, $fallback = '' ) {
	return function_exists( 'estatein_core_old_input' ) ? estatein_core_old_input( 'contact', $key, $fallback ) : $fallback;
};
$form_redirect = get_permalink();
$form_redirect = $form_redirect ? $form_redirect : home_url( '/' );
$privacy_url   = get_privacy_policy_url();
$privacy_url   = $privacy_url ? $privacy_url : home_url( '/privacy-policy/' );
?>
<?php estatein_render_form_status( 'contact' ); ?>
<form id="<?php echo esc_attr( $form_id ); ?>" class="estatein-form contact-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" novalidate data-validate-form>
	<input type="hidden" name="action" value="estatein_submit_contact">
	<input type="hidden" name="_estatein_redirect" value="<?php echo esc_url( $form_redirect ); ?>">
	<?php wp_nonce_field( 'estatein_submit_contact', 'estatein_contact_nonce' ); ?>
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
		<div class="field">
			<label for="<?php echo esc_attr( $form_id ); ?>-inquiry-type"><?php esc_html_e( 'Inquiry Type', 'estatein' ); ?></label>
			<select id="<?php echo esc_attr( $form_id ); ?>-inquiry-type" name="inquiry_type" required>
				<option value=""><?php esc_html_e( 'Select Inquiry Type', 'estatein' ); ?></option>
				<option value="buying" <?php selected( $old( 'inquiry_type' ), 'buying' ); ?>><?php esc_html_e( 'Buying a Property', 'estatein' ); ?></option>
				<option value="selling" <?php selected( $old( 'inquiry_type' ), 'selling' ); ?>><?php esc_html_e( 'Selling a Property', 'estatein' ); ?></option>
				<option value="management" <?php selected( $old( 'inquiry_type' ), 'management' ); ?>><?php esc_html_e( 'Property Management', 'estatein' ); ?></option>
				<option value="investment" <?php selected( $old( 'inquiry_type' ), 'investment' ); ?>><?php esc_html_e( 'Investment Advice', 'estatein' ); ?></option>
				<option value="other" <?php selected( $old( 'inquiry_type' ), 'other' ); ?>><?php esc_html_e( 'Other', 'estatein' ); ?></option>
			</select>
			<span class="field__error" aria-live="polite"></span>
		</div>
		<div class="field">
			<label for="<?php echo esc_attr( $form_id ); ?>-referral"><?php esc_html_e( 'How Did You Hear About Us?', 'estatein' ); ?></label>
			<select id="<?php echo esc_attr( $form_id ); ?>-referral" name="referral_source">
				<option value=""><?php esc_html_e( 'Select', 'estatein' ); ?></option>
				<option value="search" <?php selected( $old( 'referral_source' ), 'search' ); ?>><?php esc_html_e( 'Search Engine', 'estatein' ); ?></option>
				<option value="social" <?php selected( $old( 'referral_source' ), 'social' ); ?>><?php esc_html_e( 'Social Media', 'estatein' ); ?></option>
				<option value="friend" <?php selected( $old( 'referral_source' ), 'friend' ); ?>><?php esc_html_e( 'Friend or Colleague', 'estatein' ); ?></option>
				<option value="advertisement" <?php selected( $old( 'referral_source' ), 'advertisement' ); ?>><?php esc_html_e( 'Advertisement', 'estatein' ); ?></option>
			</select>
		</div>
		<div class="field field--full">
			<label for="<?php echo esc_attr( $form_id ); ?>-message"><?php esc_html_e( 'Message', 'estatein' ); ?></label>
			<textarea id="<?php echo esc_attr( $form_id ); ?>-message" name="message" rows="5" placeholder="<?php esc_attr_e( 'Enter your Message here...', 'estatein' ); ?>" required><?php echo esc_textarea( $old( 'message' ) ); ?></textarea>
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
