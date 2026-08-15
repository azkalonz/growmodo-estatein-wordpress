<?php
/**
 * Content type registration.
 *
 * @package EstateinCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register Estatein post types and taxonomies.
 *
 * @return void
 */
function estatein_core_register_content_types() {
	register_post_type(
		'estatein_property',
		array(
			'labels'             => array(
				'name'                  => __( 'Properties', 'estatein-core' ),
				'singular_name'         => __( 'Property', 'estatein-core' ),
				'add_new_item'          => __( 'Add New Property', 'estatein-core' ),
				'edit_item'             => __( 'Edit Property', 'estatein-core' ),
				'new_item'              => __( 'New Property', 'estatein-core' ),
				'view_item'             => __( 'View Property', 'estatein-core' ),
				'view_items'            => __( 'View Properties', 'estatein-core' ),
				'search_items'          => __( 'Search Properties', 'estatein-core' ),
				'not_found'             => __( 'No properties found.', 'estatein-core' ),
				'not_found_in_trash'    => __( 'No properties found in Trash.', 'estatein-core' ),
				'all_items'             => __( 'All Properties', 'estatein-core' ),
				'archives'              => __( 'Property Archives', 'estatein-core' ),
				'attributes'            => __( 'Property Attributes', 'estatein-core' ),
				'featured_image'        => __( 'Property Image', 'estatein-core' ),
				'set_featured_image'    => __( 'Set property image', 'estatein-core' ),
				'remove_featured_image' => __( 'Remove property image', 'estatein-core' ),
			),
			'public'             => true,
			'publicly_queryable' => true,
			'show_in_rest'       => true,
			'has_archive'        => 'properties',
			'rewrite'            => array(
				'slug'       => 'properties',
				'with_front' => false,
			),
			'menu_icon'          => 'dashicons-building',
			'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'revisions' ),
			'taxonomies'         => array( 'estatein_property_type', 'estatein_location' ),
			'menu_position'      => 20,
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
		),
	);

	register_post_type(
		'estatein_team_member',
		array(
			'labels'             => array(
				'name'               => __( 'Team Members', 'estatein-core' ),
				'singular_name'      => __( 'Team Member', 'estatein-core' ),
				'add_new_item'       => __( 'Add New Team Member', 'estatein-core' ),
				'edit_item'          => __( 'Edit Team Member', 'estatein-core' ),
				'view_item'          => __( 'View Team Member', 'estatein-core' ),
				'search_items'       => __( 'Search Team Members', 'estatein-core' ),
				'not_found'          => __( 'No team members found.', 'estatein-core' ),
				'not_found_in_trash' => __( 'No team members found in Trash.', 'estatein-core' ),
				'all_items'          => __( 'All Team Members', 'estatein-core' ),
			),
			'public'             => true,
			'publicly_queryable' => true,
			'show_in_rest'       => true,
			'has_archive'        => false,
			'rewrite'            => array(
				'slug'       => 'team',
				'with_front' => false,
			),
			'menu_icon'          => 'dashicons-groups',
			'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'revisions' ),
			'menu_position'      => 21,
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
		),
	);

	register_post_type(
		'estatein_inquiry',
		array(
			'labels'              => array(
				'name'               => __( 'Inquiries', 'estatein-core' ),
				'singular_name'      => __( 'Inquiry', 'estatein-core' ),
				'edit_item'          => __( 'View Inquiry', 'estatein-core' ),
				'view_item'          => __( 'View Inquiry', 'estatein-core' ),
				'search_items'       => __( 'Search Inquiries', 'estatein-core' ),
				'not_found'          => __( 'No inquiries found.', 'estatein-core' ),
				'not_found_in_trash' => __( 'No inquiries found in Trash.', 'estatein-core' ),
				'all_items'          => __( 'All Inquiries', 'estatein-core' ),
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => false,
			'query_var'           => false,
			'rewrite'             => false,
			'menu_icon'           => 'dashicons-email-alt',
			'supports'            => array( 'title' ),
			'menu_position'       => 22,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'capabilities'        => array(
				'create_posts' => 'do_not_allow',
			),
		),
	);

	register_taxonomy(
		'estatein_property_type',
		array( 'estatein_property' ),
		array(
			'labels'            => array(
				'name'          => __( 'Property Types', 'estatein-core' ),
				'singular_name' => __( 'Property Type', 'estatein-core' ),
				'search_items'  => __( 'Search Property Types', 'estatein-core' ),
				'all_items'     => __( 'All Property Types', 'estatein-core' ),
				'edit_item'     => __( 'Edit Property Type', 'estatein-core' ),
				'update_item'   => __( 'Update Property Type', 'estatein-core' ),
				'add_new_item'  => __( 'Add New Property Type', 'estatein-core' ),
				'new_item_name' => __( 'New Property Type Name', 'estatein-core' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array(
				'slug'       => 'property-type',
				'with_front' => false,
			),
		),
	);

	register_taxonomy(
		'estatein_location',
		array( 'estatein_property' ),
		array(
			'labels'            => array(
				'name'          => __( 'Locations', 'estatein-core' ),
				'singular_name' => __( 'Location', 'estatein-core' ),
				'search_items'  => __( 'Search Locations', 'estatein-core' ),
				'all_items'     => __( 'All Locations', 'estatein-core' ),
				'edit_item'     => __( 'Edit Location', 'estatein-core' ),
				'update_item'   => __( 'Update Location', 'estatein-core' ),
				'add_new_item'  => __( 'Add New Location', 'estatein-core' ),
				'new_item_name' => __( 'New Location Name', 'estatein-core' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array(
				'slug'       => 'location',
				'with_front' => false,
			),
		),
	);
}
add_action( 'init', 'estatein_core_register_content_types' );
