<?php
/**
 * The post and page creation functionality.
 *
 * @link       https://yourwebsite.com
 * @since      1.0.0
 *
 * @package    SitemapXMLToPostsPages
 * @subpackage SitemapXMLToPostsPages/includes
 */

/**
 * The post and page creation functionality.
 *
 * Handles determining suggested post data (title, slug, type) and
 * creating WordPress posts/pages.
 *
 * @since      1.0.0
 * @package    SitemapXMLToPostsPages
 * @subpackage SitemapXMLToPostsPages/includes
 * @author     Your Name/Company <you@example.com>
 */
class Post_Creator {

	/**
	 * Determines suggested post data (title, slug, post type) for a given URL.
	 *
	 * @since 1.0.0
	 * @param string $url                The original URL from the sitemap.
	 * @param string $user_preference_type User's preferred default post type ('page' or 'post').
	 * @return array An associative array with 'title', 'slug', and 'post_type'.
	 */
	public function get_suggested_post_data( $url, $user_preference_type = 'page' ) {
		$parsed_url = wp_parse_url( $url );
		$path = isset( $parsed_url['path'] ) ? trim( $parsed_url['path'], '/' ) : '';

		$suggested_slug = sanitize_title( $path );
		$suggested_title = ucwords( str_replace( array( '-', '_' ), ' ', $suggested_slug ) );

		// If the path is empty (e.g., just domain.com), suggest "Home".
		if ( empty( $path ) ) {
			$suggested_slug  = 'home';
			$suggested_title = esc_html__( 'Home', SXTPP_TEXT_DOMAIN );
		}

		// Intelligent Post Type Suggestion.
		$suggested_post_type = $user_preference_type; // Start with user's preference.

		// Common blog post indicators in URL.
		$blog_indicators = array(
			'blog',
			'category',
			'tag',
			'archive',
			'date', // e.g., /2023/10/
			'post',
		);

		foreach ( $blog_indicators as $indicator ) {
			if ( strpos( $path, $indicator . '/' ) !== false || preg_match( '#/\d{4}/\d{2}/\d{2}/#', $path ) ) { // Matches /YYYY/MM/DD/
				$suggested_post_type = 'post';
				break;
			}
		}

		// If the path looks like a root page (e.g., 'about', 'contact', no subdirectories) and it wasn't marked as post, default to page.
		// This handles cases where a user wants blog posts to be 'posts' but other top-level URLs to be 'pages'.
		if ( 'post' !== $suggested_post_type && strpos( $path, '/' ) === false && ! empty( $path ) ) {
			$suggested_post_type = 'page';
		}


		/**
		 * Filter the suggested post type for a given URL.
		 *
		 * @since 1.0.0
		 * @param string $suggested_post_type The post type ('page' or 'post') suggested by the plugin.
		 * @param string $url                 The original URL from the sitemap.
		 * @param string $user_preference_type The user's preferred default post type.
		 */
		$suggested_post_type = apply_filters( 'sxtpp_determine_post_type', $suggested_post_type, $url, $user_preference_type );

		/**
		 * Filter the suggested title for a given URL.
		 *
		 * @since 1.0.0
		 * @param string $suggested_title The title suggested by the plugin.
		 * @param string $url             The original URL from the sitemap.
		 */
		$suggested_title = apply_filters( 'sxtpp_generate_title', $suggested_title, $url );

		/**
		 * Filter the suggested slug for a given URL.
		 *
		 * @since 1.0.0
		 * @param string $suggested_slug The slug suggested by the plugin.
		 * @param string $url            The original URL from the sitemap.
		 */
		$suggested_slug = apply_filters( 'sxtpp_generate_slug', $suggested_slug, $url );

		return array(
			'title'     => $suggested_title,
			'slug'      => $suggested_slug,
			'post_type' => $suggested_post_type,
		);
	}

	/**
	 * Creates a WordPress post or page from sitemap item data.
	 *
	 * @since 1.0.0
	 * @param array $item_data Associative array containing 'original_url', 'suggested_title', 'suggested_slug', 'suggested_type'.
	 * @return int|WP_Error Post ID on success, WP_Error on failure.
	 */
	public function create_post_from_sitemap_item( $item_data ) {
		$original_url    = isset( $item_data['original_url'] ) ? esc_url_raw( $item_data['original_url'] ) : '';
		$suggested_title = isset( $item_data['suggested_title'] ) ? sanitize_text_field( $item_data['suggested_title'] ) : '';
		$suggested_slug  = isset( $item_data['suggested_slug'] ) ? sanitize_title( $item_data['suggested_slug'] ) : '';
		$suggested_type  = isset( $item_data['suggested_type'] ) ? sanitize_key( $item_data['suggested_type'] ) : 'page';

		if ( empty( $original_url ) || empty( $suggested_title ) || empty( $suggested_slug ) ) {
			return new WP_Error( 'sxtpp_missing_data', esc_html__( 'Missing essential data for post creation.', SXTPP_TEXT_DOMAIN ) );
		}

		// Ensure the post type is valid (e.g., 'page' or 'post').
		if ( ! post_type_exists( $suggested_type ) ) {
			return new WP_Error( 'sxtpp_invalid_post_type', sprintf( esc_html__( 'Invalid post type: %s', SXTPP_TEXT_DOMAIN ), $suggested_type ) );
		}

		// Check if a post with the same slug already exists for the given post type.
		$existing_post = get_page_by_path( $suggested_slug, OBJECT, $suggested_type );
		if ( $existing_post ) {
			return new WP_Error( 'sxtpp_post_exists', sprintf( esc_html__( 'A %1$s with slug "%2$s" already exists.', SXTPP_TEXT_DOMAIN ), $suggested_type, $suggested_slug ), array( 'post_id' => $existing_post->ID ) );
		}

		$post_data = array(
			'post_title'    => $suggested_title,
			'post_name'     => $suggested_slug,
			'post_type'     => $suggested_type,
			'post_status'   => 'draft', // Set as draft for review.
			'post_author'   => get_current_user_id(), // Assign to the current user.
			'meta_input'    => array(
				'_sxtpp_original_url' => $original_url, // Store original URL as custom field.
			),
		);

		/**
		 * Filter the post data before inserting into the database.
		 *
		 * @since 1.0.0
		 * @param array $post_data The array of post data.
		 * @param array $item_data The original sitemap item data.
		 */
		$post_data = apply_filters( 'sxtpp_pre_insert_post_data', $post_data, $item_data );

		$post_id = wp_insert_post( $post_data, true ); // Set true to return WP_Error on failure.

		if ( is_wp_error( $post_id ) ) {
			return new WP_Error( 'sxtpp_insert_error', sprintf( esc_html__( 'Error creating %1$s "%2$s": %3$s', SXTPP_TEXT_DOMAIN ), $suggested_type, $suggested_title, $post_id->get_error_message() ) );
		}

		return $post_id;
	}
}