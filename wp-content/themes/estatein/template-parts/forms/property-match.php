<?php
/**
 * Property-preference form used on the Properties page.
 *
 * @package Estatein
 */

$form_id       = $args['form_id'] ?? 'estatein-property-match';
$old           = static function ( $key, $fallback = '' ) {
	return function_exists( 'estatein_core_old_input' ) ? estatein_core_old_input( 'contact', $key, $fallback ) : $fallback;
};
$form_redirect = get_permalink();
$form_redirect = $form_redirect ? $form_redirect : home_url( '/properties/' );
$privacy_url   = get_privacy_policy_url();
$privacy_url   = $privacy_url ? $privacy_url : home_url( '/privacy-policy/' );
$locations     = get_terms(
	array(
		'taxonomy'   => 'estatein_location',
		'hide_empty' => false,
	)
);
$types         = get_terms(
	array(
		'taxonomy'   => 'estatein_property_type',
		'hide_empty' => false,
	)
);
$locations     = is_wp_error( $locations ) ? array() : $locations;
$types         = is_wp_error( $types ) ? array() : $types;
?>
<?php estatein_render_form_status( 'contact' ); ?>
<form id="<?php echo esc_attr( $form_id ); ?>" class="estatein-form property-match-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" novalidate data-validate-form>
	<input type="hidden" name="action" value="estatein_submit_contact">
	<input type="hidden" name="form_context" value="property_match">
	<input type="hidden" name="inquiry_type" value="buying">
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
			<label for="<?php echo esc_attr( $form_id ); ?>-location"><?php esc_html_e( 'Preferred Location', 'estatein' ); ?></label>
			<select id="<?php echo esc_attr( $form_id ); ?>-location" name="preferred_location" required>
				<option value=""><?php esc_html_e( 'Select Location', 'estatein' ); ?></option>
				<?php foreach ( $locations as $location ) : ?>
					<option value="<?php echo esc_attr( $location->slug ); ?>" <?php selected( $old( 'preferred_location' ), $location->slug ); ?>><?php echo esc_html( $location->name ); ?></option>
				<?php endforeach; ?>
			</select>
			<span class="field__error" aria-live="polite"></span>
		</div>
		<div class="field">
			<label for="<?php echo esc_attr( $form_id ); ?>-type"><?php esc_html_e( 'Property Type', 'estatein' ); ?></label>
			<select id="<?php echo esc_attr( $form_id ); ?>-type" name="property_type" required>
				<option value=""><?php esc_html_e( 'Select Property Type', 'estatein' ); ?></option>
				<?php foreach ( $types as $property_type_option ) : ?>
					<option value="<?php echo esc_attr( $property_type_option->slug ); ?>" <?php selected( $old( 'property_type' ), $property_type_option->slug ); ?>><?php echo esc_html( $property_type_option->name ); ?></option>
				<?php endforeach; ?>
			</select>
			<span class="field__error" aria-live="polite"></span>
		</div>
		<div class="field">
			<label for="<?php echo esc_attr( $form_id ); ?>-bathrooms"><?php esc_html_e( 'No. of Bathrooms', 'estatein' ); ?></label>
			<select id="<?php echo esc_attr( $form_id ); ?>-bathrooms" name="bathrooms" required>
				<option value=""><?php esc_html_e( 'Select no. of Bathrooms', 'estatein' ); ?></option>
				<?php foreach ( array( '1', '2', '3', '4', '5-plus' ) as $count ) : ?>
					<option value="<?php echo esc_attr( $count ); ?>" <?php selected( $old( 'bathrooms' ), $count ); ?>><?php echo esc_html( '5-plus' === $count ? '5+' : $count ); ?></option>
				<?php endforeach; ?>
			</select>
			<span class="field__error" aria-live="polite"></span>
		</div>
		<div class="field">
			<label for="<?php echo esc_attr( $form_id ); ?>-bedrooms"><?php esc_html_e( 'No. of Bedrooms', 'estatein' ); ?></label>
			<select id="<?php echo esc_attr( $form_id ); ?>-bedrooms" name="bedrooms" required>
				<option value=""><?php esc_html_e( 'Select no. of Bedrooms', 'estatein' ); ?></option>
				<?php foreach ( array( '1', '2', '3', '4', '5-plus' ) as $count ) : ?>
					<option value="<?php echo esc_attr( $count ); ?>" <?php selected( $old( 'bedrooms' ), $count ); ?>><?php echo esc_html( '5-plus' === $count ? '5+' : $count ); ?></option>
				<?php endforeach; ?>
			</select>
			<span class="field__error" aria-live="polite"></span>
		</div>
		<div class="field field--wide">
			<label for="<?php echo esc_attr( $form_id ); ?>-budget"><?php esc_html_e( 'Budget', 'estatein' ); ?></label>
			<select id="<?php echo esc_attr( $form_id ); ?>-budget" name="budget" required>
				<option value=""><?php esc_html_e( 'Select Budget', 'estatein' ); ?></option>
				<option value="under-250k" <?php selected( $old( 'budget' ), 'under-250k' ); ?>><?php esc_html_e( 'Under $250,000', 'estatein' ); ?></option>
				<option value="250k-500k" <?php selected( $old( 'budget' ), '250k-500k' ); ?>><?php esc_html_e( '$250,000 – $500,000', 'estatein' ); ?></option>
				<option value="500k-750k" <?php selected( $old( 'budget' ), '500k-750k' ); ?>><?php esc_html_e( '$500,000 – $750,000', 'estatein' ); ?></option>
				<option value="750k-1m" <?php selected( $old( 'budget' ), '750k-1m' ); ?>><?php esc_html_e( '$750,000 – $1,000,000', 'estatein' ); ?></option>
				<option value="1m-plus" <?php selected( $old( 'budget' ), '1m-plus' ); ?>><?php esc_html_e( '$1,000,000+', 'estatein' ); ?></option>
			</select>
			<span class="field__error" aria-live="polite"></span>
		</div>
		<div class="field field--wide">
			<fieldset class="contact-methods">
				<legend><?php esc_html_e( 'Preferred Contact Method', 'estatein' ); ?></legend>
				<label class="contact-method">
					<span><?php esc_html_e( 'Phone', 'estatein' ); ?></span>
					<input name="preferred_contact" type="radio" value="phone" <?php checked( $old( 'preferred_contact', 'phone' ), 'phone' ); ?> required>
				</label>
				<label class="contact-method">
					<span><?php esc_html_e( 'Email', 'estatein' ); ?></span>
					<input name="preferred_contact" type="radio" value="email" <?php checked( $old( 'preferred_contact', 'phone' ), 'email' ); ?> required>
				</label>
			</fieldset>
			<span class="field__error" aria-live="polite"></span>
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
