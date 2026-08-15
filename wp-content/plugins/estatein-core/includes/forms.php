<?php
/**
 * Form submission, inquiry persistence, and public form helpers.
 *
 * @package EstateinCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register authenticated and unauthenticated admin-post handlers.
 *
 * @return void
 */
function estatein_core_register_form_handlers() {
	$handlers = array(
		'estatein_submit_contact' => 'estatein_core_handle_contact',
		'estatein_submit_inquiry' => 'estatein_core_handle_inquiry',
		'estatein_subscribe'      => 'estatein_core_handle_subscription',
	);

	foreach ( $handlers as $action => $callback ) {
		add_action( 'admin_post_' . $action, $callback );
		add_action( 'admin_post_nopriv_' . $action, $callback );
	}
}
estatein_core_register_form_handlers();

/**
 * Get a post value from one of several supported field aliases.
 *
 * @param array<string, mixed> $input   Input data.
 * @param array<int, string>   $aliases Accepted keys.
 * @return string
 */
function estatein_core_form_value( $input, $aliases ) {
	foreach ( $aliases as $alias ) {
		if ( isset( $input[ $alias ] ) && ! is_array( $input[ $alias ] ) ) {
			return sanitize_text_field( $input[ $alias ] );
		}
	}

	return '';
}

/**
 * Determine a safe same-site form redirect.
 *
 * @param array<string, mixed> $input Submitted input.
 * @return string
 */
function estatein_core_form_redirect_url( $input ) {
	$referer  = wp_get_referer();
	$fallback = $referer ? $referer : home_url( '/' );
	$target   = isset( $input['_estatein_redirect'] ) && ! is_array( $input['_estatein_redirect'] )
		? esc_url_raw( $input['_estatein_redirect'] )
		: $fallback;

	return wp_validate_redirect( $target, $fallback );
}

/**
 * Redirect after submission and end the request.
 *
 * @param string               $url    Redirect base URL.
 * @param string               $form   Form identifier.
 * @param string               $status Success or error.
 * @param string               $code   Public result code.
 * @param array<string, mixed> $data   Optional invalid input to restore.
 * @return never
 */
function estatein_core_form_redirect( $url, $form, $status, $code, $data = array() ) {
	$args = array(
		'estatein_form'   => sanitize_key( $form ),
		'estatein_status' => 'success' === $status ? 'success' : 'error',
		'estatein_code'   => sanitize_key( $code ),
	);

	if ( $data ) {
		$token = wp_generate_uuid4();
		set_transient(
			'estatein_form_' . $token,
			array(
				'form' => sanitize_key( $form ),
				'data' => $data,
			),
			5 * MINUTE_IN_SECONDS
		);
		$args['estatein_token'] = $token;
	}

	$url = remove_query_arg( array( 'estatein_form', 'estatein_status', 'estatein_code', 'estatein_token' ), $url );
	wp_safe_redirect( add_query_arg( $args, $url ) );
	exit;
}

/**
 * Return a stable, non-reversible client fingerprint for rate limiting.
 *
 * @return string
 */
function estatein_core_client_fingerprint() {
	$address = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
	return hash_hmac( 'sha256', $address, wp_salt( 'nonce' ) );
}

/**
 * Consume one request from a form's rate-limit window.
 *
 * @param string $form  Form name.
 * @param int    $limit Maximum requests per window.
 * @return bool True when the request may proceed.
 */
function estatein_core_rate_limit_allows( $form, $limit = 5 ) {
	$key   = 'estatein_rate_' . md5( $form . '|' . estatein_core_client_fingerprint() );
	$count = (int) get_transient( $key );

	if ( $count >= $limit ) {
		return false;
	}

	set_transient( $key, $count + 1, 10 * MINUTE_IN_SECONDS );
	return true;
}

/**
 * Collect safe values that may be restored after a validation error.
 *
 * @param array<string, mixed> $data Validated submission data.
 * @return array<string, string|int>
 */
function estatein_core_public_form_data( $data ) {
	$allowed = array(
		'first_name',
		'last_name',
		'email',
		'phone',
		'inquiry_type',
		'referral_source',
		'form_context',
		'preferred_location',
		'property_type',
		'bedrooms',
		'bathrooms',
		'budget',
		'preferred_contact',
		'message',
		'property_id',
	);
	$output  = array();

	foreach ( $allowed as $key ) {
		if ( isset( $data[ $key ] ) && ! is_array( $data[ $key ] ) ) {
			$output[ $key ] = 'message' === $key ? sanitize_textarea_field( $data[ $key ] ) : sanitize_text_field( $data[ $key ] );
		}
	}

	return $output;
}

/**
 * Verify submission mechanics common to every form.
 *
 * @param array<string, mixed> $input       Input data.
 * @param string               $form        Form identifier.
 * @param string               $nonce_field Nonce field name.
 * @param string               $nonce_action Nonce action.
 * @param int                  $rate_limit  Window limit.
 * @return array{allowed:bool,redirect:string}
 */
function estatein_core_validate_submission_request( $input, $form, $nonce_field, $nonce_action, $rate_limit ) {
	$redirect = estatein_core_form_redirect_url( $input );

	if ( 'POST' !== strtoupper( isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '' ) ) {
		estatein_core_form_redirect( $redirect, $form, 'error', 'invalid_request' );
	}

	$nonce = isset( $input[ $nonce_field ] ) && ! is_array( $input[ $nonce_field ] )
		? sanitize_text_field( $input[ $nonce_field ] )
		: '';
	if ( ! wp_verify_nonce( $nonce, $nonce_action ) ) {
		estatein_core_form_redirect( $redirect, $form, 'error', 'security_check' );
	}

	$honeypot = estatein_core_form_value( $input, array( 'website', 'company_website' ) );
	if ( '' !== $honeypot ) {
		// Deliberately appear successful to automated senders, but do not persist or email.
		estatein_core_form_redirect( $redirect, $form, 'success', 'saved' );
	}

	if ( ! estatein_core_rate_limit_allows( $form, $rate_limit ) ) {
		estatein_core_form_redirect( $redirect, $form, 'error', 'rate_limited' );
	}

	return array(
		'allowed'  => true,
		'redirect' => $redirect,
	);
}

/**
 * Normalize contact-form data.
 *
 * @param array<string, mixed> $input Input data.
 * @return array<string, string|int>
 */
function estatein_core_contact_data( $input ) {
	$message = isset( $input['message'] ) && ! is_array( $input['message'] ) ? sanitize_textarea_field( $input['message'] ) : '';

	return array(
		'first_name'         => estatein_core_form_value( $input, array( 'first_name', 'firstname', 'first-name' ) ),
		'last_name'          => estatein_core_form_value( $input, array( 'last_name', 'lastname', 'last-name' ) ),
		'email'              => sanitize_email( estatein_core_form_value( $input, array( 'email', 'email_address' ) ) ),
		'phone'              => estatein_core_form_value( $input, array( 'phone', 'phone_number' ) ),
		'inquiry_type'       => estatein_core_form_value( $input, array( 'inquiry_type', 'inquiry' ) ),
		'referral_source'    => estatein_core_form_value( $input, array( 'referral_source', 'referral' ) ),
		'form_context'       => estatein_core_form_value( $input, array( 'form_context' ) ),
		'preferred_location' => estatein_core_form_value( $input, array( 'preferred_location', 'location' ) ),
		'property_type'      => estatein_core_form_value( $input, array( 'property_type' ) ),
		'bedrooms'           => estatein_core_form_value( $input, array( 'bedrooms' ) ),
		'bathrooms'          => estatein_core_form_value( $input, array( 'bathrooms' ) ),
		'budget'             => estatein_core_form_value( $input, array( 'budget', 'preferred_budget' ) ),
		'preferred_contact'  => estatein_core_form_value( $input, array( 'preferred_contact' ) ),
		'message'            => $message,
		'property_id'        => 0,
	);
}

/**
 * Check whether the submitted terms checkbox is affirmative.
 *
 * @param array<string, mixed> $input Input data.
 * @return bool
 */
function estatein_core_terms_accepted( $input ) {
	foreach ( array( 'terms', 'agree', 'privacy' ) as $key ) {
		if ( isset( $input[ $key ] ) && ! is_array( $input[ $key ] ) && in_array( strtolower( sanitize_text_field( $input[ $key ] ) ), array( '1', 'yes', 'on', 'true' ), true ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Handle the general contact form.
 *
 * @return never
 */
function estatein_core_handle_contact() {
	$input   = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified below.
	$request = estatein_core_validate_submission_request( $input, 'contact', 'estatein_contact_nonce', 'estatein_submit_contact', 5 );
	$data    = estatein_core_contact_data( $input );

	if ( '' === $data['first_name'] || '' === $data['last_name'] || '' === $data['email'] || '' === $data['phone'] || '' === $data['inquiry_type'] || '' === $data['message'] ) {
		estatein_core_form_redirect( $request['redirect'], 'contact', 'error', 'required_fields', estatein_core_public_form_data( $data ) );
	}
	if ( ! is_email( $data['email'] ) ) {
		estatein_core_form_redirect( $request['redirect'], 'contact', 'error', 'invalid_email', estatein_core_public_form_data( $data ) );
	}
	if ( ! in_array( $data['inquiry_type'], array( 'buying', 'selling', 'management', 'investment', 'other' ), true ) ) {
		estatein_core_form_redirect( $request['redirect'], 'contact', 'error', 'invalid_inquiry_type', estatein_core_public_form_data( $data ) );
	}
	if ( ! in_array( $data['form_context'], array( '', 'property_match' ), true ) ) {
		estatein_core_form_redirect( $request['redirect'], 'contact', 'error', 'invalid_preferences', estatein_core_public_form_data( $data ) );
	}
	if ( 'property_match' === $data['form_context'] ) {
		$required_preferences = array( 'preferred_location', 'property_type', 'bedrooms', 'bathrooms', 'budget', 'preferred_contact' );
		foreach ( $required_preferences as $preference ) {
			if ( '' === $data[ $preference ] ) {
				estatein_core_form_redirect( $request['redirect'], 'contact', 'error', 'required_fields', estatein_core_public_form_data( $data ) );
			}
		}

		$valid_counts  = array( '1', '2', '3', '4', '5-plus' );
		$valid_budgets = array( 'under-250k', '250k-500k', '500k-750k', '750k-1m', '1m-plus' );
		if (
			! term_exists( $data['preferred_location'], 'estatein_location' )
			|| ! term_exists( $data['property_type'], 'estatein_property_type' )
			|| ! in_array( $data['bedrooms'], $valid_counts, true )
			|| ! in_array( $data['bathrooms'], $valid_counts, true )
			|| ! in_array( $data['budget'], $valid_budgets, true )
			|| ! in_array( $data['preferred_contact'], array( 'phone', 'email' ), true )
		) {
			estatein_core_form_redirect( $request['redirect'], 'contact', 'error', 'invalid_preferences', estatein_core_public_form_data( $data ) );
		}
	}
	if ( strlen( $data['message'] ) > 5000 ) {
		estatein_core_form_redirect( $request['redirect'], 'contact', 'error', 'message_too_long', estatein_core_public_form_data( $data ) );
	}
	if ( ! estatein_core_terms_accepted( $input ) ) {
		estatein_core_form_redirect( $request['redirect'], 'contact', 'error', 'terms_required', estatein_core_public_form_data( $data ) );
	}

	estatein_core_persist_inquiry( 'contact', $data, $request['redirect'] );
}

/**
 * Handle a property-specific inquiry form.
 *
 * @return never
 */
function estatein_core_handle_inquiry() {
	$input               = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified below.
	$request             = estatein_core_validate_submission_request( $input, 'inquiry', 'estatein_inquiry_nonce', 'estatein_submit_inquiry', 5 );
	$data                = estatein_core_contact_data( $input );
	$data['property_id'] = isset( $input['property_id'] ) && ! is_array( $input['property_id'] ) ? absint( $input['property_id'] ) : 0;

	if ( '' === $data['first_name'] || '' === $data['last_name'] || '' === $data['email'] || '' === $data['phone'] || '' === $data['message'] ) {
		estatein_core_form_redirect( $request['redirect'], 'inquiry', 'error', 'required_fields', estatein_core_public_form_data( $data ) );
	}
	if ( ! is_email( $data['email'] ) ) {
		estatein_core_form_redirect( $request['redirect'], 'inquiry', 'error', 'invalid_email', estatein_core_public_form_data( $data ) );
	}
	if ( ! $data['property_id'] || 'estatein_property' !== get_post_type( $data['property_id'] ) || 'publish' !== get_post_status( $data['property_id'] ) ) {
		estatein_core_form_redirect( $request['redirect'], 'inquiry', 'error', 'invalid_property', estatein_core_public_form_data( $data ) );
	}
	if ( strlen( $data['message'] ) > 5000 ) {
		estatein_core_form_redirect( $request['redirect'], 'inquiry', 'error', 'message_too_long', estatein_core_public_form_data( $data ) );
	}
	if ( ! estatein_core_terms_accepted( $input ) ) {
		estatein_core_form_redirect( $request['redirect'], 'inquiry', 'error', 'terms_required', estatein_core_public_form_data( $data ) );
	}

	estatein_core_persist_inquiry( 'property', $data, $request['redirect'] );
}

/**
 * Handle a footer newsletter subscription.
 *
 * @return never
 */
function estatein_core_handle_subscription() {
	$input   = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified below.
	$request = estatein_core_validate_submission_request( $input, 'subscribe', 'estatein_subscribe_nonce', 'estatein_subscribe', 8 );
	$email   = sanitize_email( estatein_core_form_value( $input, array( 'email', 'newsletter_email' ) ) );
	$data    = array(
		'first_name'      => '',
		'last_name'       => '',
		'email'           => $email,
		'phone'           => '',
		'inquiry_type'    => 'newsletter',
		'referral_source' => '',
		'budget'          => '',
		'message'         => __( 'Newsletter subscription request.', 'estatein-core' ),
		'property_id'     => 0,
	);

	if ( ! is_email( $email ) ) {
		estatein_core_form_redirect( $request['redirect'], 'subscribe', 'error', 'invalid_email', array( 'email' => $email ) );
	}

	estatein_core_persist_inquiry( 'newsletter', $data, $request['redirect'] );
}

/**
 * Save an inquiry, then attempt administrator mail.
 *
 * @param string               $source   Contact, property, or newsletter.
 * @param array<string, mixed> $data     Validated inquiry data.
 * @param string               $redirect Redirect URL.
 * @return never
 */
function estatein_core_persist_inquiry( $source, $data, $redirect ) {
	$form          = 'property' === $source ? 'inquiry' : ( 'newsletter' === $source ? 'subscribe' : $source );
	$duplicate_key = 'estatein_dup_' . md5(
		implode(
			'|',
			array(
				$source,
				strtolower( $data['email'] ),
				$data['message'],
				(string) $data['property_id'],
				wp_json_encode(
					array(
						isset( $data['preferred_location'] ) ? $data['preferred_location'] : '',
						isset( $data['property_type'] ) ? $data['property_type'] : '',
						isset( $data['bedrooms'] ) ? $data['bedrooms'] : '',
						isset( $data['bathrooms'] ) ? $data['bathrooms'] : '',
						isset( $data['budget'] ) ? $data['budget'] : '',
						isset( $data['preferred_contact'] ) ? $data['preferred_contact'] : '',
					)
				),
			)
		)
	);

	if ( get_transient( $duplicate_key ) ) {
		estatein_core_form_redirect( $redirect, $form, 'success', 'duplicate' );
	}

	$display_name = trim( $data['first_name'] . ' ' . $data['last_name'] );
	if ( '' === $display_name ) {
		$display_name = $data['email'];
	}

	$post_id = wp_insert_post(
		array(
			'post_type'    => 'estatein_inquiry',
			'post_status'  => 'private',
			'post_title'   => sprintf(
				/* translators: 1: inquiry source, 2: sender name, 3: date and time. */
				__( '%1$s — %2$s — %3$s', 'estatein-core' ),
				ucfirst( $source ),
				$display_name,
				wp_date( 'Y-m-d H:i' )
			),
			'post_content' => $data['message'],
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		estatein_core_form_redirect( $redirect, $form, 'error', 'save_failed', estatein_core_public_form_data( $data ) );
	}

	$meta = array(
		'_estatein_source'            => $source,
		'_estatein_first_name'        => $data['first_name'],
		'_estatein_last_name'         => $data['last_name'],
		'_estatein_email'             => $data['email'],
		'_estatein_phone'             => $data['phone'],
		'_estatein_inquiry_type'      => $data['inquiry_type'],
		'_estatein_referral_source'   => isset( $data['referral_source'] ) ? $data['referral_source'] : '',
		'_estatein_form_context'      => isset( $data['form_context'] ) ? $data['form_context'] : '',
		'_estatein_location'          => isset( $data['preferred_location'] ) ? $data['preferred_location'] : '',
		'_estatein_property_type'     => isset( $data['property_type'] ) ? $data['property_type'] : '',
		'_estatein_bedrooms'          => isset( $data['bedrooms'] ) ? $data['bedrooms'] : '',
		'_estatein_bathrooms'         => isset( $data['bathrooms'] ) ? $data['bathrooms'] : '',
		'_estatein_budget'            => $data['budget'],
		'_estatein_preferred_contact' => isset( $data['preferred_contact'] ) ? $data['preferred_contact'] : '',
		'_estatein_message'           => $data['message'],
		'_estatein_property_id'       => absint( $data['property_id'] ),
		'_estatein_client_hash'       => estatein_core_client_fingerprint(),
		'_estatein_created_at'        => current_time( 'mysql', true ),
	);

	foreach ( $meta as $key => $value ) {
		update_post_meta( $post_id, $key, $value );
	}

	set_transient( $duplicate_key, $post_id, 10 * MINUTE_IN_SECONDS );

	$property_title = $data['property_id'] ? get_the_title( $data['property_id'] ) : '';
	$subject        = sprintf(
		/* translators: %s: form source. */
		__( '[Estatein] New %s submission', 'estatein-core' ),
		$source
	);
	$body    = array(
		sprintf( /* translators: %s: sender name. */ __( 'Name: %s', 'estatein-core' ), $display_name ),
		sprintf( /* translators: %s: email address. */ __( 'Email: %s', 'estatein-core' ), $data['email'] ),
		sprintf( /* translators: %s: phone number. */ __( 'Phone: %s', 'estatein-core' ), $data['phone'] ? $data['phone'] : '—' ),
		sprintf( /* translators: %s: inquiry category. */ __( 'Inquiry type: %s', 'estatein-core' ), $data['inquiry_type'] ? $data['inquiry_type'] : $source ),
		sprintf( /* translators: %s: preferred location. */ __( 'Preferred location: %s', 'estatein-core' ), ! empty( $data['preferred_location'] ) ? $data['preferred_location'] : '—' ),
		sprintf( /* translators: %s: property type. */ __( 'Property type: %s', 'estatein-core' ), ! empty( $data['property_type'] ) ? $data['property_type'] : '—' ),
		sprintf( /* translators: %s: bedroom count. */ __( 'Bedrooms: %s', 'estatein-core' ), ! empty( $data['bedrooms'] ) ? $data['bedrooms'] : '—' ),
		sprintf( /* translators: %s: bathroom count. */ __( 'Bathrooms: %s', 'estatein-core' ), ! empty( $data['bathrooms'] ) ? $data['bathrooms'] : '—' ),
		sprintf( /* translators: %s: budget. */ __( 'Budget: %s', 'estatein-core' ), $data['budget'] ? $data['budget'] : '—' ),
		sprintf( /* translators: %s: preferred contact method. */ __( 'Preferred contact: %s', 'estatein-core' ), ! empty( $data['preferred_contact'] ) ? $data['preferred_contact'] : '—' ),
		sprintf( /* translators: %s: property title. */ __( 'Property: %s', 'estatein-core' ), $property_title ? $property_title : '—' ),
		'',
		$data['message'],
		'',
		sprintf( /* translators: %d: saved inquiry ID. */ __( 'Saved inquiry ID: %d', 'estatein-core' ), $post_id ),
	);
	$headers = array();
	if ( is_email( $data['email'] ) ) {
		$headers[] = 'Reply-To: ' . $display_name . ' <' . $data['email'] . '>';
	}

	$mail_sent = wp_mail( get_option( 'admin_email' ), $subject, implode( "\n", $body ), $headers );
	update_post_meta( $post_id, '_estatein_mail_sent', $mail_sent ? '1' : '0' );

	/**
	 * Fires after an Estatein inquiry has been persisted and mail attempted.
	 *
	 * @param int                  $post_id   Inquiry ID.
	 * @param string               $source    Submission source.
	 * @param array<string, mixed> $data      Validated data.
	 * @param bool                 $mail_sent Whether wp_mail reported success.
	 */
	do_action( 'estatein_core_inquiry_saved', $post_id, $source, $data, $mail_sent );

	estatein_core_form_redirect( $redirect, $form, 'success', 'saved' );
}

/**
 * Get a contextual result notice for a form.
 *
 * @param string $expected_form Optional form scope.
 * @return array{status:string,code:string,message:string}|null
 */
function estatein_core_get_form_notice( $expected_form = '' ) {
	$raw    = wp_unslash( $_GET ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only PRG status.
	$form   = isset( $raw['estatein_form'] ) && ! is_array( $raw['estatein_form'] ) ? sanitize_key( $raw['estatein_form'] ) : '';
	$status = isset( $raw['estatein_status'] ) && ! is_array( $raw['estatein_status'] ) && 'success' === $raw['estatein_status'] ? 'success' : 'error';
	$code   = isset( $raw['estatein_code'] ) && ! is_array( $raw['estatein_code'] ) ? sanitize_key( $raw['estatein_code'] ) : '';

	if ( '' === $form || '' === $code || ( $expected_form && $expected_form !== $form ) ) {
		return null;
	}

	$messages = array(
		'saved'                => __( 'Thank you. Your request has been saved and our team will be in touch soon.', 'estatein-core' ),
		'duplicate'            => __( 'We already received this request. Our team will be in touch soon.', 'estatein-core' ),
		'required_fields'      => __( 'Please complete all required fields.', 'estatein-core' ),
		'invalid_email'        => __( 'Enter a valid email address.', 'estatein-core' ),
		'invalid_inquiry_type' => __( 'Choose a valid inquiry type.', 'estatein-core' ),
		'invalid_preferences'  => __( 'Review the selected property preferences and try again.', 'estatein-core' ),
		'message_too_long'     => __( 'Keep your message under 5,000 characters.', 'estatein-core' ),
		'terms_required'       => __( 'Please agree to the Terms of Use and Privacy Policy.', 'estatein-core' ),
		'invalid_property'     => __( 'That property could not be found. Refresh the page and try again.', 'estatein-core' ),
		'security_check'       => __( 'Your session expired. Refresh the page and try again.', 'estatein-core' ),
		'rate_limited'         => __( 'Too many requests were received. Please wait ten minutes and try again.', 'estatein-core' ),
		'save_failed'          => __( 'We could not save your request. Please try again shortly.', 'estatein-core' ),
		'invalid_request'      => __( 'That request could not be processed.', 'estatein-core' ),
	);

	return array(
		'status'  => $status,
		'code'    => $code,
		'message' => isset( $messages[ $code ] ) ? $messages[ $code ] : __( 'Your request has been processed.', 'estatein-core' ),
	);
}

/**
 * Restore one previously submitted field after PRG validation failure.
 *
 * @param string $form    Form identifier.
 * @param string $key     Field key.
 * @param string $fallback Default value.
 * @return string
 */
function estatein_core_old_input( $form, $key, $fallback = '' ) {
	$raw   = wp_unslash( $_GET ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Random short-lived restore token.
	$token = isset( $raw['estatein_token'] ) && ! is_array( $raw['estatein_token'] ) ? sanitize_text_field( $raw['estatein_token'] ) : '';

	if ( ! $token || ! preg_match( '/^[a-f0-9-]{36}$/i', $token ) ) {
		return $fallback;
	}

	$stored = get_transient( 'estatein_form_' . $token );
	if ( ! is_array( $stored ) || ! isset( $stored['form'], $stored['data'] ) || $form !== $stored['form'] ) {
		return $fallback;
	}

	return isset( $stored['data'][ $key ] ) && ! is_array( $stored['data'][ $key ] )
		? (string) $stored['data'][ $key ]
		: $fallback;
}
