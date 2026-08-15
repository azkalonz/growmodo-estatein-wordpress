<?php
/**
 * Inquiry administration.
 *
 * @package EstateinCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add native Media Library controls when ACF is not available.
 *
 * @return void
 */
function estatein_core_add_property_gallery_meta_box() {
	if ( function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	add_meta_box(
		'estatein-property-gallery',
		__( 'Additional Property Images', 'estatein-core' ),
		'estatein_core_render_property_gallery_meta_box',
		'estatein_property',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes_estatein_property', 'estatein_core_add_property_gallery_meta_box' );

/**
 * Render eight optional property gallery image selectors.
 *
 * @param WP_Post $post Property being edited.
 * @return void
 */
function estatein_core_render_property_gallery_meta_box( $post ) {
	wp_nonce_field( 'estatein_core_save_property_gallery', 'estatein_core_property_gallery_nonce' );
	?>
	<p class="description">
		<?php esc_html_e( 'Choose up to eight additional images from the Media Library. The Property Image in the editor sidebar is used on listing cards.', 'estatein-core' ); ?>
	</p>
	<table class="widefat striped" data-estatein-property-gallery>
		<tbody>
			<?php for ( $index = 1; $index <= 8; $index++ ) : ?>
				<?php
				$meta_key      = 'estatein_gallery_' . $index;
				$image_id      = absint( get_post_meta( $post->ID, $meta_key, true ) );
				$image_preview = $image_id ? wp_get_attachment_image( $image_id, 'thumbnail' ) : '';
				?>
				<tr data-estatein-gallery-slot>
					<th scope="row">
						<label for="<?php echo esc_attr( $meta_key ); ?>">
							<?php /* translators: %d: Additional image position. */ ?>
							<?php printf( esc_html__( 'Additional image %d', 'estatein-core' ), (int) $index ); ?>
						</label>
					</th>
					<td>
						<input id="<?php echo esc_attr( $meta_key ); ?>" type="hidden" name="<?php echo esc_attr( $meta_key ); ?>" value="<?php echo esc_attr( $image_id ); ?>" data-estatein-gallery-input>
						<div aria-live="polite" data-estatein-gallery-preview>
							<?php if ( $image_preview ) : ?>
								<?php echo wp_kses_post( $image_preview ); ?>
							<?php elseif ( $image_id ) : ?>
								<span class="description"><?php esc_html_e( 'The imported image is unavailable. Choose a replacement.', 'estatein-core' ); ?></span>
							<?php else : ?>
								<span class="description"><?php esc_html_e( 'No image selected.', 'estatein-core' ); ?></span>
							<?php endif; ?>
						</div>
						<p>
							<button class="button" type="button" data-estatein-gallery-select><?php esc_html_e( 'Select image', 'estatein-core' ); ?></button>
							<button class="button-link-delete" type="button" data-estatein-gallery-remove<?php echo esc_attr( $image_id ? '' : ' hidden' ); ?>><?php esc_html_e( 'Remove image', 'estatein-core' ); ?></button>
						</p>
					</td>
				</tr>
			<?php endfor; ?>
		</tbody>
	</table>
	<?php
}

/**
 * Load the native WordPress media picker on property edit screens.
 *
 * @param string $hook_suffix Current admin page hook.
 * @return void
 */
function estatein_core_enqueue_property_gallery_assets( $hook_suffix ) {
	if ( function_exists( 'acf_add_local_field_group' ) || ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || 'estatein_property' !== $screen->post_type ) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_script(
		'estatein-core-property-gallery',
		plugins_url( 'assets/js/property-gallery.js', ESTATEIN_CORE_FILE ),
		array( 'media-editor' ),
		ESTATEIN_CORE_VERSION,
		true
	);
	wp_localize_script(
		'estatein-core-property-gallery',
		'estateinPropertyGallery',
		array(
			'dialogTitle' => __( 'Select a property image', 'estatein-core' ),
			'buttonText'  => __( 'Use this image', 'estatein-core' ),
			'emptyText'   => __( 'No image selected.', 'estatein-core' ),
			'imageAlt'    => __( 'Selected property image', 'estatein-core' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'estatein_core_enqueue_property_gallery_assets' );

/**
 * Save native property gallery image selections.
 *
 * @param int $post_id Property ID.
 * @return void
 */
function estatein_core_save_property_gallery( $post_id ) {
	if ( ! isset( $_POST['estatein_core_property_gallery_nonce'] ) ) {
		return;
	}

	$nonce = sanitize_text_field( wp_unslash( $_POST['estatein_core_property_gallery_nonce'] ) );
	if ( ! wp_verify_nonce( $nonce, 'estatein_core_save_property_gallery' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	for ( $index = 1; $index <= 8; $index++ ) {
		$meta_key = 'estatein_gallery_' . $index;
		$image_id = isset( $_POST[ $meta_key ] ) ? absint( wp_unslash( $_POST[ $meta_key ] ) ) : 0;

		if ( $image_id && wp_attachment_is_image( $image_id ) ) {
			update_post_meta( $post_id, $meta_key, $image_id );
		} else {
			delete_post_meta( $post_id, $meta_key );
		}
	}
}
add_action( 'save_post_estatein_property', 'estatein_core_save_property_gallery' );

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
