<?php
/**
 * Inquiry administration.
 *
 * @package EstateinCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Configure useful inquiry-list columns.
 *
 * @param array<string, string> $columns Existing columns.
 * @return array<string, string>
 */
function estatein_core_inquiry_columns( $columns ) {
	return array(
		'cb'                => isset( $columns['cb'] ) ? $columns['cb'] : '',
		'title'             => __( 'Submission', 'estatein-core' ),
		'estatein_source'   => __( 'Source', 'estatein-core' ),
		'estatein_sender'   => __( 'Sender', 'estatein-core' ),
		'estatein_property' => __( 'Property', 'estatein-core' ),
		'estatein_mail'     => __( 'Email', 'estatein-core' ),
		'date'              => isset( $columns['date'] ) ? $columns['date'] : __( 'Date', 'estatein-core' ),
	);
}
add_filter( 'manage_estatein_inquiry_posts_columns', 'estatein_core_inquiry_columns' );

/**
 * Render inquiry-list values.
 *
 * @param string $column  Column key.
 * @param int    $post_id Inquiry ID.
 * @return void
 */
function estatein_core_inquiry_column_value( $column, $post_id ) {
	switch ( $column ) {
		case 'estatein_source':
			echo esc_html( ucfirst( (string) get_post_meta( $post_id, '_estatein_source', true ) ) );
			break;
		case 'estatein_sender':
			$name  = trim( get_post_meta( $post_id, '_estatein_first_name', true ) . ' ' . get_post_meta( $post_id, '_estatein_last_name', true ) );
			$email = get_post_meta( $post_id, '_estatein_email', true );
			if ( $name ) {
				echo esc_html( $name ) . '<br>';
			}
			printf( '<a href="mailto:%1$s">%2$s</a>', esc_attr( $email ), esc_html( $email ) );
			break;
		case 'estatein_property':
			$property_id = absint( get_post_meta( $post_id, '_estatein_property_id', true ) );
			if ( $property_id ) {
				printf( '<a href="%1$s">%2$s</a>', esc_url( get_edit_post_link( $property_id ) ), esc_html( get_the_title( $property_id ) ) );
			} else {
				echo '<span aria-hidden="true">—</span><span class="screen-reader-text">' . esc_html__( 'Not property-specific', 'estatein-core' ) . '</span>';
			}
			break;
		case 'estatein_mail':
			$sent = '1' === get_post_meta( $post_id, '_estatein_mail_sent', true );
			echo esc_html( $sent ? __( 'Sent', 'estatein-core' ) : __( 'Saved only', 'estatein-core' ) );
			break;
	}
}
add_action( 'manage_estatein_inquiry_posts_custom_column', 'estatein_core_inquiry_column_value', 10, 2 );

/**
 * Add a readable inquiry detail panel.
 *
 * @return void
 */
function estatein_core_add_inquiry_meta_box() {
	add_meta_box(
		'estatein-inquiry-details',
		__( 'Inquiry Details', 'estatein-core' ),
		'estatein_core_render_inquiry_meta_box',
		'estatein_inquiry',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes_estatein_inquiry', 'estatein_core_add_inquiry_meta_box' );

/**
 * Render an inquiry's stored values as read-only data.
 *
 * @param WP_Post $post Inquiry post.
 * @return void
 */
function estatein_core_render_inquiry_meta_box( $post ) {
	$fields = array(
		'_estatein_source'            => __( 'Source', 'estatein-core' ),
		'_estatein_first_name'        => __( 'First name', 'estatein-core' ),
		'_estatein_last_name'         => __( 'Last name', 'estatein-core' ),
		'_estatein_email'             => __( 'Email', 'estatein-core' ),
		'_estatein_phone'             => __( 'Phone', 'estatein-core' ),
		'_estatein_inquiry_type'      => __( 'Inquiry type', 'estatein-core' ),
		'_estatein_referral_source'   => __( 'Referral source', 'estatein-core' ),
		'_estatein_location'          => __( 'Preferred location', 'estatein-core' ),
		'_estatein_property_type'     => __( 'Property type', 'estatein-core' ),
		'_estatein_bedrooms'          => __( 'Bedrooms', 'estatein-core' ),
		'_estatein_bathrooms'         => __( 'Bathrooms', 'estatein-core' ),
		'_estatein_budget'            => __( 'Budget', 'estatein-core' ),
		'_estatein_preferred_contact' => __( 'Preferred contact', 'estatein-core' ),
		'_estatein_created_at'        => __( 'Submitted (UTC)', 'estatein-core' ),
	);

	echo '<table class="widefat striped"><tbody>';
	foreach ( $fields as $key => $label ) {
		$value = get_post_meta( $post->ID, $key, true );
		printf( '<tr><th scope="row" style="width:180px">%1$s</th><td>%2$s</td></tr>', esc_html( $label ), esc_html( $value ? $value : '—' ) );
	}

	$property_id = absint( get_post_meta( $post->ID, '_estatein_property_id', true ) );
	$property    = $property_id ? get_the_title( $property_id ) : '—';
	printf( '<tr><th scope="row">%1$s</th><td>%2$s</td></tr>', esc_html__( 'Property', 'estatein-core' ), esc_html( $property ) );

	$message = get_post_meta( $post->ID, '_estatein_message', true );
	printf( '<tr><th scope="row">%1$s</th><td style="white-space:pre-wrap">%2$s</td></tr>', esc_html__( 'Message', 'estatein-core' ), esc_html( $message ? $message : '—' ) );
	echo '</tbody></table>';
}

/**
 * Remove irrelevant row actions from immutable form records.
 *
 * @param array<string, string> $actions Row actions.
 * @param WP_Post               $post    Current post.
 * @return array<string, string>
 */
function estatein_core_inquiry_row_actions( $actions, $post ) {
	if ( 'estatein_inquiry' === $post->post_type ) {
		unset( $actions['inline hide-if-no-js'] );
	}

	return $actions;
}
add_filter( 'post_row_actions', 'estatein_core_inquiry_row_actions', 10, 2 );
