<?php
/**
 * Structured fields and portable meta accessors.
 *
 * @package EstateinCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Property field definitions.
 *
 * @return array<string, array<string, mixed>>
 */
function estatein_core_property_fields() {
	return array(
		'price'                    => array(
			'default' => 550000,
			'type'    => 'number',
		),
		'address'                  => array(
			'default' => '',
			'type'    => 'string',
		),
		'area'                     => array(
			'default' => 2500,
			'type'    => 'number',
		),
		'bedrooms'                 => array(
			'default' => 4,
			'type'    => 'integer',
		),
		'bathrooms'                => array(
			'default' => 3,
			'type'    => 'integer',
		),
		'year_built'               => array(
			'default' => 2020,
			'type'    => 'integer',
		),
		'features'                 => array(
			'default' => '',
			'type'    => 'string',
		),
		'pricing_transfer_tax'     => array(
			'default' => 25000,
			'type'    => 'number',
		),
		'pricing_legal_fees'       => array(
			'default' => 3000,
			'type'    => 'number',
		),
		'pricing_inspection'       => array(
			'default' => 500,
			'type'    => 'number',
		),
		'pricing_insurance'        => array(
			'default' => 1200,
			'type'    => 'number',
		),
		'pricing_property_tax'     => array(
			'default' => 1250,
			'type'    => 'number',
		),
		'pricing_monthly_fees'     => array(
			'default' => 300,
			'type'    => 'number',
		),
		'pricing_initial_deposit'  => array(
			'default' => 55000,
			'type'    => 'number',
		),
		'pricing_mortgage_payment' => array(
			'default' => 2500,
			'type'    => 'number',
		),
		'pricing_notes'            => array(
			'default' => '',
			'type'    => 'string',
		),
		'gallery_1'                => array(
			'default' => 0,
			'type'    => 'integer',
		),
		'gallery_2'                => array(
			'default' => 0,
			'type'    => 'integer',
		),
		'gallery_3'                => array(
			'default' => 0,
			'type'    => 'integer',
		),
		'gallery_4'                => array(
			'default' => 0,
			'type'    => 'integer',
		),
		'gallery_5'                => array(
			'default' => 0,
			'type'    => 'integer',
		),
		'gallery_6'                => array(
			'default' => 0,
			'type'    => 'integer',
		),
		'gallery_7'                => array(
			'default' => 0,
			'type'    => 'integer',
		),
		'gallery_8'                => array(
			'default' => 0,
			'type'    => 'integer',
		),
	);
}

/**
 * Team field definitions.
 *
 * @return array<string, array<string, mixed>>
 */
function estatein_core_team_fields() {
	return array(
		'role'     => array(
			'default' => '',
			'type'    => 'string',
		),
		'email'    => array(
			'default' => '',
			'type'    => 'string',
		),
		'phone'    => array(
			'default' => '',
			'type'    => 'string',
		),
		'linkedin' => array(
			'default' => '',
			'type'    => 'string',
		),
		'twitter'  => array(
			'default' => '',
			'type'    => 'string',
		),
	);
}

/**
 * Sanitize a decimal field without losing zero.
 *
 * @param mixed $value Incoming value.
 * @return float
 */
function estatein_core_sanitize_number( $value ) {
	return is_numeric( $value ) ? (float) $value : 0.0;
}

/**
 * Register native post-meta schemas. ACF reads and writes the same keys.
 *
 * @return void
 */
function estatein_core_register_meta() {
	foreach ( estatein_core_property_fields() as $key => $definition ) {
		$type     = $definition['type'];
		$sanitize = 'sanitize_text_field';

		if ( 'number' === $type ) {
			$sanitize = 'estatein_core_sanitize_number';
		} elseif ( 'integer' === $type ) {
			$sanitize = 'absint';
		}

		register_post_meta(
			'estatein_property',
			'estatein_' . $key,
			array(
				'single'            => true,
				'type'              => $type,
				'show_in_rest'      => true,
				'sanitize_callback' => $sanitize,
				'auth_callback'     => static function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}

	foreach ( estatein_core_team_fields() as $key => $definition ) {
		register_post_meta(
			'estatein_team_member',
			'estatein_' . $key,
			array(
				'single'            => true,
				'type'              => $definition['type'],
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => static function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}
}
add_action( 'init', 'estatein_core_register_meta', 20 );

/**
 * Add local ACF Free-compatible field groups.
 *
 * Gallery slots use individual image fields because ACF's Gallery field is Pro-only.
 *
 * @return void
 */
function estatein_core_register_acf_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$property_fields = array(
		array(
			'key'           => 'field_estatein_price',
			'label'         => __( 'Price (USD)', 'estatein-core' ),
			'name'          => 'estatein_price',
			'type'          => 'number',
			'required'      => 1,
			'min'           => 0,
			'prepend'       => '$',
			'default_value' => 550000,
		),
		array(
			'key'      => 'field_estatein_address',
			'label'    => __( 'Address', 'estatein-core' ),
			'name'     => 'estatein_address',
			'type'     => 'text',
			'required' => 1,
		),
		array(
			'key'           => 'field_estatein_area',
			'label'         => __( 'Area (sq ft)', 'estatein-core' ),
			'name'          => 'estatein_area',
			'type'          => 'number',
			'min'           => 0,
			'default_value' => 2500,
		),
		array(
			'key'           => 'field_estatein_bedrooms',
			'label'         => __( 'Bedrooms', 'estatein-core' ),
			'name'          => 'estatein_bedrooms',
			'type'          => 'number',
			'min'           => 0,
			'default_value' => 4,
		),
		array(
			'key'           => 'field_estatein_bathrooms',
			'label'         => __( 'Bathrooms', 'estatein-core' ),
			'name'          => 'estatein_bathrooms',
			'type'          => 'number',
			'min'           => 0,
			'default_value' => 3,
		),
		array(
			'key'           => 'field_estatein_year_built',
			'label'         => __( 'Year Built', 'estatein-core' ),
			'name'          => 'estatein_year_built',
			'type'          => 'number',
			'min'           => 1800,
			'max'           => 2200,
			'default_value' => 2020,
		),
		array(
			'key'          => 'field_estatein_features',
			'label'        => __( 'Key Features', 'estatein-core' ),
			'name'         => 'estatein_features',
			'type'         => 'textarea',
			'instructions' => __( 'Enter one feature per line.', 'estatein-core' ),
			'new_lines'    => '',
		),
		array(
			'key'   => 'field_estatein_gallery_tab',
			'label' => __( 'Gallery', 'estatein-core' ),
			'name'  => '',
			'type'  => 'tab',
		),
	);

	for ( $index = 1; $index <= 8; $index++ ) {
		$property_fields[] = array(
			'key'           => 'field_estatein_gallery_' . $index,
			'label'         => sprintf( /* translators: %d: image position. */ __( 'Gallery Image %d', 'estatein-core' ), $index ),
			'name'          => 'estatein_gallery_' . $index,
			'type'          => 'image',
			'return_format' => 'id',
			'preview_size'  => 'medium',
			'library'       => 'all',
		);
	}

	$property_fields = array_merge(
		$property_fields,
		array(
			array(
				'key'   => 'field_estatein_pricing_tab',
				'label' => __( 'Pricing Details', 'estatein-core' ),
				'name'  => '',
				'type'  => 'tab',
			),
			array(
				'key'     => 'field_estatein_transfer_tax',
				'label'   => __( 'Transfer Tax', 'estatein-core' ),
				'name'    => 'estatein_pricing_transfer_tax',
				'type'    => 'number',
				'min'     => 0,
				'prepend' => '$',
			),
			array(
				'key'     => 'field_estatein_legal_fees',
				'label'   => __( 'Legal Fees', 'estatein-core' ),
				'name'    => 'estatein_pricing_legal_fees',
				'type'    => 'number',
				'min'     => 0,
				'prepend' => '$',
			),
			array(
				'key'     => 'field_estatein_inspection',
				'label'   => __( 'Home Inspection', 'estatein-core' ),
				'name'    => 'estatein_pricing_inspection',
				'type'    => 'number',
				'min'     => 0,
				'prepend' => '$',
			),
			array(
				'key'     => 'field_estatein_insurance',
				'label'   => __( 'Property Insurance', 'estatein-core' ),
				'name'    => 'estatein_pricing_insurance',
				'type'    => 'number',
				'min'     => 0,
				'prepend' => '$',
			),
			array(
				'key'     => 'field_estatein_property_tax',
				'label'   => __( 'Monthly Property Tax', 'estatein-core' ),
				'name'    => 'estatein_pricing_property_tax',
				'type'    => 'number',
				'min'     => 0,
				'prepend' => '$',
			),
			array(
				'key'     => 'field_estatein_monthly_fees',
				'label'   => __( 'Monthly Fees', 'estatein-core' ),
				'name'    => 'estatein_pricing_monthly_fees',
				'type'    => 'number',
				'min'     => 0,
				'prepend' => '$',
			),
			array(
				'key'     => 'field_estatein_initial_deposit',
				'label'   => __( 'Initial Deposit', 'estatein-core' ),
				'name'    => 'estatein_pricing_initial_deposit',
				'type'    => 'number',
				'min'     => 0,
				'prepend' => '$',
			),
			array(
				'key'     => 'field_estatein_mortgage_payment',
				'label'   => __( 'Monthly Mortgage Payment', 'estatein-core' ),
				'name'    => 'estatein_pricing_mortgage_payment',
				'type'    => 'number',
				'min'     => 0,
				'prepend' => '$',
			),
			array(
				'key'       => 'field_estatein_pricing_notes',
				'label'     => __( 'Pricing Notes', 'estatein-core' ),
				'name'      => 'estatein_pricing_notes',
				'type'      => 'textarea',
				'new_lines' => '',
			),
		)
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_estatein_property_details',
			'title'    => __( 'Property Details', 'estatein-core' ),
			'fields'   => $property_fields,
			'location' => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'estatein_property',
					),
				),
			),
			'position' => 'normal',
			'style'    => 'default',
		)
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_estatein_team_details',
			'title'    => __( 'Team Details', 'estatein-core' ),
			'fields'   => array(
				array(
					'key'      => 'field_estatein_role',
					'label'    => __( 'Role', 'estatein-core' ),
					'name'     => 'estatein_role',
					'type'     => 'text',
					'required' => 1,
				),
				array(
					'key'   => 'field_estatein_email',
					'label' => __( 'Email', 'estatein-core' ),
					'name'  => 'estatein_email',
					'type'  => 'email',
				),
				array(
					'key'   => 'field_estatein_phone',
					'label' => __( 'Phone', 'estatein-core' ),
					'name'  => 'estatein_phone',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_estatein_linkedin',
					'label' => __( 'LinkedIn URL', 'estatein-core' ),
					'name'  => 'estatein_linkedin',
					'type'  => 'url',
				),
				array(
					'key'   => 'field_estatein_twitter',
					'label' => __( 'X / Twitter URL', 'estatein-core' ),
					'name'  => 'estatein_twitter',
					'type'  => 'url',
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'estatein_team_member',
					),
				),
			),
		)
	);
}
add_action( 'acf/init', 'estatein_core_register_acf_fields' );

/**
 * Get a property field from ACF or native post meta, then use a seeded default.
 *
 * @param int        $post_id Property ID.
 * @param string     $key     Unprefixed field key.
 * @param mixed|null $fallback Optional explicit fallback.
 * @return mixed
 */
function estatein_core_get_property_field( $post_id, $key, $fallback = null ) {
	$definitions = estatein_core_property_fields();

	if ( ! isset( $definitions[ $key ] ) ) {
		return $fallback;
	}

	$meta_key = 'estatein_' . $key;
	$value    = null;

	if ( function_exists( 'get_field' ) ) {
		$value = get_field( $meta_key, $post_id, false );
	}

	if ( false === $value || null === $value || '' === $value ) {
		$value = get_post_meta( $post_id, $meta_key, true );
	}

	if ( '' === $value || null === $value || false === $value ) {
		$value = null !== $fallback ? $fallback : $definitions[ $key ]['default'];
	}

	return $value;
}

/**
 * Get a team field from ACF or native post meta.
 *
 * @param int        $post_id Team member ID.
 * @param string     $key     Unprefixed field key.
 * @param mixed|null $fallback Optional explicit fallback.
 * @return mixed
 */
function estatein_core_get_team_field( $post_id, $key, $fallback = null ) {
	$definitions = estatein_core_team_fields();

	if ( ! isset( $definitions[ $key ] ) ) {
		return $fallback;
	}

	$meta_key = 'estatein_' . $key;
	$value    = function_exists( 'get_field' ) ? get_field( $meta_key, $post_id, false ) : null;

	if ( false === $value || null === $value || '' === $value ) {
		$value = get_post_meta( $post_id, $meta_key, true );
	}

	return ( '' === $value || null === $value || false === $value )
		? ( null !== $fallback ? $fallback : $definitions[ $key ]['default'] )
		: $value;
}

/**
 * Return normalized property gallery image data.
 *
 * @param int $post_id Property ID.
 * @return array<int, array{id:int,url:string,alt:string}>
 */
function estatein_core_get_property_gallery( $post_id ) {
	$image_ids = array();
	$thumbnail = get_post_thumbnail_id( $post_id );

	for ( $index = 1; $index <= 8; $index++ ) {
		$image = estatein_core_get_property_field( $post_id, 'gallery_' . $index, 0 );
		if ( is_array( $image ) && isset( $image['ID'] ) ) {
			$image = $image['ID'];
		}
		if ( is_numeric( $image ) ) {
			$image_ids[] = absint( $image );
		}
	}

	if ( $thumbnail ) {
		$image_ids[] = $thumbnail;
	}

	$image_ids = array_values( array_unique( array_filter( $image_ids ) ) );
	$gallery   = array();

	foreach ( $image_ids as $image_id ) {
		$url = wp_get_attachment_image_url( $image_id, 'full' );
		if ( ! $url ) {
			continue;
		}

		$alt       = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
		$gallery[] = array(
			'id'  => $image_id,
			'url' => $url,
			'alt' => $alt ? $alt : get_the_title( $post_id ),
		);
	}

	return $gallery;
}

/**
 * Format a numeric USD value for the Estatein UI.
 *
 * @param mixed $value Price.
 * @return string
 */
function estatein_core_format_price( $value ) {
	$value = is_numeric( $value ) ? (float) $value : 0;
	return '$' . number_format_i18n( $value, 0 );
}
