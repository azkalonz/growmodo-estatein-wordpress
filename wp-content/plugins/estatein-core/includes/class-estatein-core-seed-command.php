<?php
/**
 * Idempotent WP-CLI demo fixture.
 *
 * @package EstateinCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Seed the complete Estatein demo dataset.
 */
class Estatein_Core_Seed_Command extends WP_CLI_Command {
	/**
	 * Create or update Estatein pages, properties, team members, terms, menus, and settings.
	 *
	 * ## EXAMPLES
	 *
	 *     wp estatein seed
	 *
	 * @return void
	 */
	public function __invoke() {
		estatein_core_register_content_types();
		$this->remove_untouched_core_defaults();

		$page_ids     = $this->seed_pages();
		$property_ids = $this->seed_properties();
		$team_ids     = $this->seed_team();
		$this->seed_menus( $page_ids );
		$this->seed_settings( $page_ids );
		flush_rewrite_rules();

		WP_CLI::success(
			sprintf(
				/* translators: 1: page count, 2: property count, 3: team member count. */
				__( 'Estatein demo ready: %1$d pages, %2$d properties, and %3$d team members.', 'estatein-core' ),
				count( $page_ids ),
				count( $property_ids ),
				count( $team_ids )
			)
		);
	}

	/**
	 * Remove only pristine content inserted by a fresh English WordPress install.
	 *
	 * Any edit, custom metadata, relationship, taxonomy change, moderation change,
	 * or non-default comment causes the relevant post to be preserved.
	 *
	 * @return void
	 */
	private function remove_untouched_core_defaults() {
		$removed = array();
		$sample  = get_page_by_path( 'sample-page', OBJECT, 'page' );

		if (
			$this->is_untouched_core_post(
				$sample,
				'Sample Page',
				array( 'This is an example page.', 'As a new WordPress user, you should go to', 'to delete this page and create new pages for your content. Have fun!' )
			)
			&& (int) get_option( 'page_on_front' ) !== (int) $sample->ID
			&& ! get_children(
				array(
					'post_parent' => $sample->ID,
					'post_type'   => 'any',
					'post_status' => 'any',
					'numberposts' => 1,
				)
			)
			&& 0 === (int) get_comments(
				array(
					'post_id' => $sample->ID,
					'status'  => 'all',
					'count'   => true,
				)
			)
			&& ! $this->has_menu_reference( $sample->ID )
		) {
			wp_delete_post( $sample->ID, true );
			$removed[] = 'Sample Page';
		}

		$hello                 = get_page_by_path( 'hello-world', OBJECT, 'post' );
		$hello_comments        = $hello
			? get_comments(
				array(
					'post_id' => $hello->ID,
					'status'  => 'all',
					'number'  => 0,
				)
			)
			: array();
		$comments_are_pristine = count( $hello_comments ) <= 1;
		foreach ( $hello_comments as $candidate_comment ) {
			if ( ! $this->is_untouched_core_comment( $candidate_comment ) ) {
				$comments_are_pristine = false;
				break;
			}
		}

		$default_comment = get_comment( 1 );
		if ( $this->is_untouched_core_comment( $default_comment ) ) {
			wp_delete_comment( $default_comment->comment_ID, true );
			$removed[] = 'default comment';
		}

		if (
			$comments_are_pristine
			&& $this->is_untouched_core_post(
				$hello,
				'Hello world!',
				array( 'Welcome to WordPress.', 'Edit or delete it, then start writing!' )
			)
			&& ! is_sticky( $hello->ID )
			&& ! get_children(
				array(
					'post_parent' => $hello->ID,
					'post_type'   => 'any',
					'post_status' => 'any',
					'numberposts' => 1,
				)
			)
			&& ! $this->has_menu_reference( $hello->ID )
			&& $this->has_only_default_post_terms( $hello->ID )
		) {
			wp_delete_post( $hello->ID, true );
			$removed[] = 'Hello world!';
		}

		if ( $removed ) {
			WP_CLI::log( 'Removed untouched WordPress defaults: ' . implode( ', ', $removed ) . '.' );
		}
	}

	/**
	 * Determine whether a post still matches its pristine core-install shape.
	 *
	 * @param WP_Post|false|null $candidate Expected default post.
	 * @param string             $title     Exact default title.
	 * @param array<int, string> $fragments Required default-content fragments.
	 * @return bool
	 */
	private function is_untouched_core_post( $candidate, $title, $fragments ) {
		if (
			! $candidate instanceof WP_Post
			|| $title !== $candidate->post_title
			|| 'publish' !== $candidate->post_status
			|| '' !== $candidate->post_password
			|| '' !== $candidate->post_excerpt
			|| $candidate->post_date_gmt !== $candidate->post_modified_gmt
			|| ! $this->has_only_core_post_meta( $candidate, $title )
		) {
			return false;
		}

		$content = preg_replace( '/\s+/', ' ', wp_strip_all_tags( $candidate->post_content ) );
		foreach ( $fragments as $fragment ) {
			if ( false === strpos( $content, $fragment ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Allow only metadata that core itself adds to a fresh default post.
	 *
	 * @param WP_Post $candidate Expected default post.
	 * @param string  $title     Exact default title.
	 * @return bool
	 */
	private function has_only_core_post_meta( $candidate, $title ) {
		$metadata = get_post_meta( $candidate->ID );
		if ( ! $metadata ) {
			return true;
		}

		return 'Sample Page' === $title
			&& array( '_wp_page_template' ) === array_keys( $metadata )
			&& array( 'default' ) === $metadata['_wp_page_template'];
	}

	/**
	 * Check the exact untouched comment created during core installation.
	 *
	 * @param WP_Comment|false|null $candidate Expected default comment.
	 * @return bool
	 */
	private function is_untouched_core_comment( $candidate ) {
		if (
			! $candidate instanceof WP_Comment
			|| 1 !== (int) $candidate->comment_ID
			|| 'A WordPress Commenter' !== $candidate->comment_author
			|| '1' !== (string) $candidate->comment_approved
			|| ( '' !== $candidate->comment_type && 'comment' !== $candidate->comment_type )
			|| get_comment_meta( $candidate->comment_ID )
		) {
			return false;
		}

		$content = preg_replace( '/\s+/', ' ', wp_strip_all_tags( $candidate->comment_content ) );
		return false !== strpos( $content, 'Hi, this is a comment.' )
			&& false !== strpos( $content, 'Comments screen in the dashboard.' );
	}

	/**
	 * Check whether a post has been deliberately placed in a navigation menu.
	 *
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	private function has_menu_reference( $post_id ) {
		$references = get_posts(
			array(
				'post_type'      => 'nav_menu_item',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => '_menu_item_object_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- One-off fixture safety check.
				'meta_value'     => (string) $post_id, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- One-off fixture safety check.
			)
		);

		return ! empty( $references );
	}

	/**
	 * Ensure Hello World still has only core's default category and no tags.
	 *
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	private function has_only_default_post_terms( $post_id ) {
		$categories = wp_get_post_categories( $post_id );
		$tags       = wp_get_post_tags( $post_id, array( 'fields' => 'ids' ) );

		return ! is_wp_error( $tags )
			&& ! $tags
			&& array( (int) get_option( 'default_category' ) ) === array_map( 'intval', $categories );
	}

	/**
	 * Create or update one post by path.
	 *
	 * @param string               $post_type Post type.
	 * @param string               $slug      Slug.
	 * @param array<string, mixed> $data      Post data.
	 * @return int
	 */
	private function upsert_post( $post_type, $slug, $data ) {
		$existing = get_page_by_path( $slug, OBJECT, $post_type );
		$payload  = array_merge(
			$data,
			array(
				'post_type' => $post_type,
				'post_name' => $slug,
			)
		);

		if ( $existing ) {
			$payload['ID'] = $existing->ID;
		}

		$post_id = wp_insert_post( wp_slash( $payload ), true );
		if ( is_wp_error( $post_id ) ) {
			WP_CLI::error( $post_id->get_error_message() );
		}

		return (int) $post_id;
	}

	/**
	 * Seed public pages.
	 *
	 * @return array<string, int>
	 */
	private function seed_pages() {
		$pages = array(
			'home'                 => array(
				'title'   => 'Home',
				'excerpt' => 'Your journey to finding the perfect property begins here.',
				'content' => '<p>Discover your dream property with Estatein. Our dedicated team is ready to guide you through every step of your real-estate journey.</p>',
			),
			'about-us'             => array(
				'title'   => 'About Us',
				'excerpt' => 'Our journey is a story of growth, excellence, and an unwavering commitment to turning real-estate dreams into reality.',
				'content' => '<p>Our story began with a vision to create a real-estate platform that transcends the ordinary. We assembled a team of passionate professionals who shared our values.</p>',
			),
			'services'             => array(
				'title'   => 'Services',
				'excerpt' => 'Elevate your real-estate experience with Estatein\'s expert services.',
				'content' => '<p>Unlock property value through personalized buying, selling, management, and investment advisory services.</p>',
			),
			'contact'              => array(
				'title'   => 'Contact Us',
				'excerpt' => 'Get in touch with Estatein and take the first step toward your real-estate goals.',
				'content' => '<p>Our experienced team is ready to answer your questions, discuss opportunities, and guide your next move.</p>',
			),
			'terms-of-use'         => array(
				'title'   => 'Terms of Use',
				'excerpt' => 'Terms for using the Estatein demonstration website.',
				'content' => '<p>This website and its property data are provided as a design and development demonstration. Listings, availability, prices, and claims are illustrative and must not be treated as real offers.</p>',
			),
			'privacy-policy'       => array(
				'title'   => 'Privacy Policy',
				'excerpt' => 'How Estatein handles demonstration inquiry data.',
				'content' => '<p>Information submitted through this demonstration is stored in WordPress so the site administrator can respond. Do not submit sensitive personal or financial information. Administrators should configure retention and deletion practices before production use.</p>',
			),
			'terms-and-conditions' => array(
				'title'   => 'Terms & Conditions',
				'excerpt' => 'Conditions for using Estatein demonstration content.',
				'content' => '<p>All real-estate content on this site is fictional demonstration material. No listing, testimonial, metric, award, or client name represents a real commercial claim.</p>',
			),
		);

		$page_ids = array();
		foreach ( $pages as $slug => $page ) {
			$page_ids[ $slug ] = $this->upsert_post(
				'page',
				$slug,
				array(
					'post_status'  => 'publish',
					'post_title'   => $page['title'],
					'post_excerpt' => $page['excerpt'],
					'post_content' => $page['content'],
					'menu_order'   => count( $page_ids ),
				)
			);
		}

		return $page_ids;
	}

	/**
	 * Seed property taxonomy terms.
	 *
	 * @param string $taxonomy Taxonomy.
	 * @param string $name     Term name.
	 * @return int
	 */
	private function upsert_term( $taxonomy, $name ) {
		$term = term_exists( $name, $taxonomy );
		if ( ! $term ) {
			$term = wp_insert_term( $name, $taxonomy );
		}
		if ( is_wp_error( $term ) ) {
			WP_CLI::error( $term->get_error_message() );
		}

		return (int) ( is_array( $term ) ? $term['term_id'] : $term );
	}

	/**
	 * Seed representative Estatein properties.
	 *
	 * @return array<int, int>
	 */
	private function seed_properties() {
		$properties = array(
			array(
				'slug'      => 'seaside-serenity-villa',
				'title'     => 'Seaside Serenity Villa',
				'excerpt'   => 'A stunning 4-bedroom, 3-bathroom villa in a peaceful suburban neighborhood.',
				'content'   => 'Wake up to the soothing melody of waves. This beachfront villa offers stunning ocean views from every room and a private path to the sand.',
				'type'      => 'Villa',
				'location'  => 'Malibu, California',
				'price'     => 1250000,
				'address'   => '123 Seaside Avenue, Malibu, CA',
				'area'      => 2500,
				'bedrooms'  => 4,
				'bathrooms' => 3,
				'year'      => 2023,
				'features'  => "Expansive oceanfront terrace\nGourmet kitchen with premium appliances\nPrivate beach access\nMaster suite with panoramic views\nSmart-home climate and lighting controls",
				'images'    => array(
					'properties/seaside-serenity-villa',
					'properties/seaside/gallery-01',
					'properties/seaside/gallery-06',
					'properties/seaside/gallery-02',
					'properties/seaside/gallery-03',
					'properties/seaside/gallery-04',
					'properties/seaside/gallery-05',
					'properties/seaside/gallery-07',
				),
			),
			array(
				'slug'      => 'metropolitan-haven',
				'title'     => 'Metropolitan Haven',
				'excerpt'   => 'A chic and fully furnished 2-bedroom apartment with panoramic city views.',
				'content'   => 'Immerse yourself in the energy of the city. This modern apartment in the heart of downtown places culture, dining, and business at your doorstep.',
				'type'      => 'Apartment',
				'location'  => 'Manhattan, New York',
				'price'     => 650000,
				'address'   => '456 Skyline Drive, Manhattan, NY',
				'area'      => 1800,
				'bedrooms'  => 2,
				'bathrooms' => 2,
				'year'      => 2022,
				'features'  => "Floor-to-ceiling windows\nResident fitness center\n24-hour concierge\nPrivate balcony\nSecure underground parking",
				'images'    => array( 'properties-raw-02', 'metropolitan', 'apartment' ),
			),
			array(
				'slug'      => 'rustic-retreat-cottage',
				'title'     => 'Rustic Retreat Cottage',
				'excerpt'   => 'An elegant 3-bedroom, 2.5-bathroom townhouse in a gated community.',
				'content'   => 'Find tranquility in the countryside. This charming cottage is nestled among rolling hills, mature trees, and peaceful walking paths.',
				'type'      => 'Cottage',
				'location'  => 'Nashville, Tennessee',
				'price'     => 350000,
				'address'   => '789 Meadow Lane, Nashville, TN',
				'area'      => 2200,
				'bedrooms'  => 3,
				'bathrooms' => 3,
				'year'      => 2018,
				'features'  => "Original stone fireplace\nWraparound porch\nLandscaped cottage garden\nRenovated farmhouse kitchen\nDetached studio workspace",
				'images'    => array( 'properties-raw-05', 'rustic', 'cottage' ),
			),
			array(
				'slug'      => 'urban-oasis-penthouse',
				'title'     => 'Urban Oasis Penthouse',
				'excerpt'   => 'A refined penthouse that pairs open-plan living with skyline views.',
				'content'   => 'Live above the city in a private penthouse with warm modern finishes, expansive entertaining areas, and immediate access to cultural landmarks.',
				'type'      => 'Penthouse',
				'location'  => 'Miami, Florida',
				'price'     => 890000,
				'address'   => '88 Biscayne Boulevard, Miami, FL',
				'area'      => 3200,
				'bedrooms'  => 4,
				'bathrooms' => 4,
				'year'      => 2023,
				'features'  => "Private roof terrace\nPanoramic skyline views\nChef's kitchen\nDirect elevator access\nFull-service resident amenities",
				'images'    => array( 'properties-raw-03', 'penthouse', 'property-raw-17' ),
			),
			array(
				'slug'      => 'garden-grove-townhouse',
				'title'     => 'Garden Grove Townhouse',
				'excerpt'   => 'A bright contemporary townhouse shaped around a private landscaped courtyard.',
				'content'   => 'Enjoy connected neighborhood living without sacrificing privacy. Generous rooms flow into a lush courtyard for easy indoor-outdoor living.',
				'type'      => 'Townhouse',
				'location'  => 'Austin, Texas',
				'price'     => 475000,
				'address'   => '64 Garden Grove, Austin, TX',
				'area'      => 2100,
				'bedrooms'  => 3,
				'bathrooms' => 3,
				'year'      => 2021,
				'features'  => "Private landscaped courtyard\nFlexible home office\nEnergy-efficient construction\nTwo-car garage\nCommunity walking trails",
				'images'    => array( 'properties-raw-04', 'garden', 'townhouse' ),
			),
			array(
				'slug'      => 'mountain-view-retreat',
				'title'     => 'Mountain View Retreat',
				'excerpt'   => 'A secluded modern estate with dramatic mountain views and year-round comfort.',
				'content'   => 'Reconnect with nature in an architectural retreat designed for calm. Quiet material choices frame the mountains from every gathering space.',
				'type'      => 'Estate',
				'location'  => 'Aspen, Colorado',
				'price'     => 1250000,
				'address'   => '18 Summit Trail, Aspen, CO',
				'area'      => 4100,
				'bedrooms'  => 5,
				'bathrooms' => 5,
				'year'      => 2019,
				'features'  => "Mountain-view great room\nHeated outdoor living area\nPrivate sauna\nSki and equipment room\nFour-season access",
				'images'    => array( 'properties-raw-06', 'mountain', 'estate' ),
			),
		);

		$ids = array();
		foreach ( $properties as $property_index => $property ) {
			$post_id = $this->upsert_post(
				'estatein_property',
				$property['slug'],
				array(
					'post_status'  => 'publish',
					'post_title'   => $property['title'],
					'post_excerpt' => $property['excerpt'],
					'post_content' => $property['content'],
					'menu_order'   => $property_index,
				)
			);

			$type_id     = $this->upsert_term( 'estatein_property_type', $property['type'] );
			$location_id = $this->upsert_term( 'estatein_location', $property['location'] );
			wp_set_object_terms( $post_id, array( $type_id ), 'estatein_property_type' );
			wp_set_object_terms( $post_id, array( $location_id ), 'estatein_location' );

			$meta = array(
				'price'                    => $property['price'],
				'address'                  => $property['address'],
				'area'                     => $property['area'],
				'bedrooms'                 => $property['bedrooms'],
				'bathrooms'                => $property['bathrooms'],
				'year_built'               => $property['year'],
				'features'                 => $property['features'],
				'pricing_transfer_tax'     => 'seaside-serenity-villa' === $property['slug'] ? 25000 : round( $property['price'] * 0.045 ),
				'pricing_legal_fees'       => 3000,
				'pricing_inspection'       => 500,
				'pricing_insurance'        => 1200,
				'pricing_property_tax'     => 1250,
				'pricing_monthly_fees'     => 300,
				'pricing_initial_deposit'  => 'seaside-serenity-villa' === $property['slug'] ? 250000 : round( $property['price'] * 0.1 ),
				'pricing_mortgage_payment' => 'seaside-serenity-villa' === $property['slug'] ? 4375 : round( $property['price'] * 0.0045 ),
				'pricing_notes'            => 'Figures are illustrative and may vary based on lender, location, inspection results, and closing date.',
			);
			foreach ( $meta as $key => $value ) {
				update_post_meta( $post_id, 'estatein_' . $key, $value );
			}

			$this->attach_matching_images( $post_id, $property['title'], $property['images'] );
			$ids[] = $post_id;
		}

		return $ids;
	}

	/**
	 * Seed team members.
	 *
	 * @return array<int, int>
	 */
	private function seed_team() {
		$members = array(
			array(
				'slug'  => 'max-mitchell',
				'name'  => 'Max Mitchell',
				'role'  => 'Founder',
				'email' => 'max@estatein.example',
				'image' => 'about-raw-08',
			),
			array(
				'slug'  => 'sarah-johnson',
				'name'  => 'Sarah Johnson',
				'role'  => 'Chief Real Estate Officer',
				'email' => 'sarah@estatein.example',
				'image' => 'about-raw-03',
			),
			array(
				'slug'  => 'david-brown',
				'name'  => 'David Brown',
				'role'  => 'Head of Property Management',
				'email' => 'david@estatein.example',
				'image' => 'about-raw-10',
			),
			array(
				'slug'  => 'michael-turner',
				'name'  => 'Michael Turner',
				'role'  => 'Legal Counsel',
				'email' => 'michael@estatein.example',
				'image' => 'about-raw-07',
			),
		);

		$ids = array();
		foreach ( $members as $member_index => $member ) {
			$post_id = $this->upsert_post(
				'estatein_team_member',
				$member['slug'],
				array(
					'post_status'  => 'publish',
					'post_title'   => $member['name'],
					'post_excerpt' => $member['role'],
					'post_content' => sprintf( '%s helps clients make informed real-estate decisions with clarity and care.', $member['name'] ),
					'menu_order'   => $member_index,
				)
			);
			update_post_meta( $post_id, 'estatein_role', $member['role'] );
			update_post_meta( $post_id, 'estatein_email', $member['email'] );
			$this->attach_matching_images( $post_id, $member['name'], array( $member['image'], $member['slug'], 'team' ) );
			$ids[] = $post_id;
		}

		return $ids;
	}

	/**
	 * Locate images in the active theme and attach the best matches.
	 *
	 * @param int                $post_id  Parent post.
	 * @param string             $alt      Alternative text.
	 * @param array<int, string> $needles  Filename fragments in priority order.
	 * @return void
	 */
	private function attach_matching_images( $post_id, $alt, $needles ) {
		$files = $this->theme_image_files();
		if ( ! $files ) {
			return;
		}

		$matches = array();
		foreach ( $needles as $needle ) {
			foreach ( $files as $file ) {
				$relative_file = str_replace( '\\', '/', ltrim( str_replace( get_stylesheet_directory(), '', $file ), '/\\' ) );
				if ( false !== stripos( $relative_file, $needle ) && ! in_array( $file, $matches, true ) ) {
					$matches[] = $file;
				}
				if ( count( $matches ) >= 8 ) {
					break 2;
				}
			}
		}

		for ( $gallery_index = 1; $gallery_index <= 8; $gallery_index++ ) {
			delete_post_meta( $post_id, 'estatein_gallery_' . $gallery_index );
		}

		foreach ( $matches as $index => $file ) {
			$attachment_id = $this->import_local_image( $file, $post_id, $alt );
			if ( ! $attachment_id ) {
				continue;
			}
			if ( 0 === $index ) {
				set_post_thumbnail( $post_id, $attachment_id );
			}
			if ( $index > 0 && $index <= 8 ) {
				update_post_meta( $post_id, 'estatein_gallery_' . $index, $attachment_id );
			}
		}
	}

	/**
	 * Return image files available in the active theme.
	 *
	 * @return array<int, string>
	 */
	private function theme_image_files() {
		static $files = null;
		if ( null !== $files ) {
			return $files;
		}

		$files = array();
		$root  = get_stylesheet_directory();
		if ( ! is_dir( $root ) ) {
			return $files;
		}

		$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS ) );
		foreach ( $iterator as $file ) {
			if ( $file->isFile() && preg_match( '/\.(?:avif|jpe?g|png|webp)$/i', $file->getFilename() ) ) {
				$files[] = $file->getPathname();
			}
		}

		return $files;
	}

	/**
	 * Import one local theme image without creating duplicates.
	 *
	 * @param string $file    Source file.
	 * @param int    $post_id Parent post.
	 * @param string $alt     Alternative text.
	 * @return int
	 */
	private function import_local_image( $file, $post_id, $alt ) {
		$existing = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => '_estatein_source_path', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- One-off CLI fixture.
				'meta_value'     => $file, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- One-off CLI fixture.
			)
		);
		if ( $existing ) {
			return (int) $existing[0];
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$temp = wp_tempnam( basename( $file ) );
		if ( ! $temp || ! copy( $file, $temp ) ) {
			return 0;
		}

		$attachment_id = media_handle_sideload(
			array(
				'name'     => basename( $file ),
				'tmp_name' => $temp,
			),
			$post_id,
			$alt
		);
		if ( is_wp_error( $attachment_id ) ) {
			wp_delete_file( $temp );
			WP_CLI::warning( $attachment_id->get_error_message() );
			return 0;
		}

		update_post_meta( $attachment_id, '_estatein_source_path', $file );
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt );
		return (int) $attachment_id;
	}

	/**
	 * Seed the primary navigation without duplicate items.
	 *
	 * @param array<string, int> $page_ids Seeded pages.
	 * @return void
	 */
	private function seed_menus( $page_ids ) {
		$menu = wp_get_nav_menu_object( 'Primary Navigation' );
		if ( ! $menu ) {
			$menu_id = wp_create_nav_menu( 'Primary Navigation' );
			if ( is_wp_error( $menu_id ) ) {
				WP_CLI::warning( $menu_id->get_error_message() );
				return;
			}
		} else {
			$menu_id = $menu->term_id;
		}

		$menu_items = wp_get_nav_menu_items( $menu_id );
		$items      = $menu_items ? $menu_items : array();
		$existing   = array();
		foreach ( $items as $item ) {
			$existing[ $item->title ] = $item->ID;
		}
		// Contact is rendered as the dedicated Figma-style action beside the menu.
		if ( isset( $existing['Contact Us'] ) ) {
			wp_delete_post( (int) $existing['Contact Us'], true );
			unset( $existing['Contact Us'] );
		}
		$links = array(
			'Home'       => array(
				'object_id' => $page_ids['home'],
				'type'      => 'post_type',
				'object'    => 'page',
			),
			'About Us'   => array(
				'object_id' => $page_ids['about-us'],
				'type'      => 'post_type',
				'object'    => 'page',
			),
			'Properties' => array(
				'url'    => get_post_type_archive_link( 'estatein_property' ),
				'type'   => 'custom',
				'object' => 'custom',
			),
			'Services'   => array(
				'object_id' => $page_ids['services'],
				'type'      => 'post_type',
				'object'    => 'page',
			),
		);

		foreach ( $links as $title => $link ) {
			$args = array(
				'menu-item-title'  => $title,
				'menu-item-status' => 'publish',
				'menu-item-type'   => $link['type'],
				'menu-item-object' => $link['object'],
			);
			if ( isset( $link['object_id'] ) ) {
				$args['menu-item-object-id'] = $link['object_id'];
			} else {
				$args['menu-item-url'] = $link['url'];
			}
			$item_id = isset( $existing[ $title ] ) ? $existing[ $title ] : 0;
			wp_update_nav_menu_item( $menu_id, $item_id, $args );
		}

		$locations            = get_theme_mod( 'nav_menu_locations', array() );
		$locations['primary'] = $menu_id;

		$footer_menus = array(
			'footer_home'       => array(
				'Hero Section' => home_url( '/#hero' ),
				'Features'     => home_url( '/#features' ),
				'Properties'   => home_url( '/#properties' ),
				'Testimonials' => home_url( '/#testimonials' ),
				"FAQ's"        => home_url( '/#faqs' ),
			),
			'footer_about'      => array(
				'Our Story'    => get_permalink( $page_ids['about-us'] ) . '#journey',
				'Our Works'    => get_permalink( $page_ids['about-us'] ) . '#values',
				'How It Works' => get_permalink( $page_ids['about-us'] ) . '#process',
				'Our Team'     => get_permalink( $page_ids['about-us'] ) . '#team',
				'Our Clients'  => get_permalink( $page_ids['about-us'] ) . '#clients',
			),
			'footer_properties' => array(
				'Portfolio'  => get_post_type_archive_link( 'estatein_property' ),
				'Categories' => get_post_type_archive_link( 'estatein_property' ) . '#property-search',
			),
			'footer_services'   => array(
				'Valuation Mastery'    => get_permalink( $page_ids['services'] ) . '#sell',
				'Strategic Marketing'  => get_permalink( $page_ids['services'] ) . '#sell',
				'Negotiation Wizardry' => get_permalink( $page_ids['services'] ) . '#sell',
				'Closing Success'      => get_permalink( $page_ids['services'] ) . '#sell',
				'Property Management'  => get_permalink( $page_ids['services'] ) . '#manage',
			),
			'footer_contact'    => array(
				'Contact Form' => get_permalink( $page_ids['contact'] ) . '#contact-form',
				'Our Offices'  => get_permalink( $page_ids['contact'] ) . '#offices',
			),
		);

		foreach ( $footer_menus as $location => $footer_links ) {
			$locations[ $location ] = $this->upsert_custom_menu( ucwords( str_replace( '_', ' ', $location ) ), $footer_links );
		}
		set_theme_mod( 'nav_menu_locations', $locations );
	}

	/**
	 * Create or update a custom-link menu.
	 *
	 * @param string                $name  Menu name.
	 * @param array<string, string> $links Link label and URL pairs.
	 * @return int
	 */
	private function upsert_custom_menu( $name, $links ) {
		$menu = wp_get_nav_menu_object( $name );
		if ( ! $menu ) {
			$menu_id = wp_create_nav_menu( $name );
			if ( is_wp_error( $menu_id ) ) {
				WP_CLI::warning( $menu_id->get_error_message() );
				return 0;
			}
		} else {
			$menu_id = $menu->term_id;
		}

		$items    = wp_get_nav_menu_items( $menu_id );
		$existing = array();
		foreach ( $items ? $items : array() as $item ) {
			$existing[ $item->title ] = $item->ID;
		}

		foreach ( $links as $title => $url ) {
			$item_id = isset( $existing[ $title ] ) ? $existing[ $title ] : 0;
			wp_update_nav_menu_item(
				$menu_id,
				$item_id,
				array(
					'menu-item-title'  => $title,
					'menu-item-status' => 'publish',
					'menu-item-type'   => 'custom',
					'menu-item-object' => 'custom',
					'menu-item-url'    => $url,
				)
			);
		}

		return (int) $menu_id;
	}

	/**
	 * Seed core site settings.
	 *
	 * @param array<string, int> $page_ids Seeded pages.
	 * @return void
	 */
	private function seed_settings( $page_ids ) {
		update_option( 'blogname', 'Estatein' );
		update_option( 'blogdescription', 'Your Journey to Finding the Perfect Property Begins Here' );
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $page_ids['home'] );
		update_option( 'wp_page_for_privacy_policy', $page_ids['privacy-policy'] );
		update_option( 'permalink_structure', '/%postname%/' );
		update_option( 'posts_per_page', 6 );
	}
}

WP_CLI::add_command( 'estatein seed', 'Estatein_Core_Seed_Command' );
