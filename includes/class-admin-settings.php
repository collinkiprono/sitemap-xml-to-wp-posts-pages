<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://yourwebsite.com
 * @since      1.0.0
 *
 * @package    SitemapXMLToPostsPages
 * @subpackage SitemapXMLToPostsPages/includes
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks to
 * register the admin-specific stylesheet and JavaScript.
 *
 * @package    SitemapXMLToPostsPages
 * @subpackage SitemapXMLToPostsPages/includes
 * @author     Your Name/Company <you@example.com>
 */
class Admin_Settings {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version           The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the admin menu for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {
		add_management_page(
			esc_html__( 'Sitemap XML to Posts/Pages', SXTPP_TEXT_DOMAIN ), // Page title
			esc_html__( 'Sitemap XML to Posts/Pages', SXTPP_TEXT_DOMAIN ), // Menu title
			'manage_options', // Capability required to access the page
			$this->plugin_name . '_settings', // Menu slug
			array( $this, 'display_settings_page' ) // Callback function to render the page
		);
	}

	/**
	 * Render the settings page for the plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_settings_page() {
		// Include the view file for the settings page.
		require_once SXTPP_PLUGIN_DIR . 'admin/views/settings-page.php';
	}

	/**
	 * Enqueue the admin-specific stylesheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style(
			$this->plugin_name . '-admin-style',
			SXTPP_PLUGIN_URL . 'admin/css/admin-style.css',
			array(),
			$this->version,
			'all'
		);
	}

	/**
	 * Enqueue the admin-specific JavaScript.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			$this->plugin_name . '-admin-script',
			SXTPP_PLUGIN_URL . 'admin/js/admin-script.js', // We will create this file later.
			array( 'jquery' ), // Dependency on jQuery.
			$this->version,
			true // Enqueue in the footer.
		);

		// Localize script to pass PHP variables to JS (e.g., AJAX URL, nonces).
		wp_localize_script(
			$this->plugin_name . '-admin-script',
			'sxtpp_ajax_object',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'scan_nonce' => wp_create_nonce( 'sxtpp_scan_sitemap_nonce' ),
				'create_nonce' => wp_create_nonce( 'sxtpp_create_posts_pages_nonce' ),
				'loading_text' => esc_html__( 'Loading...', SXTPP_TEXT_DOMAIN ),
				'scan_button_text' => esc_html__( 'Scan Sitemap', SXTPP_TEXT_DOMAIN ),
				'create_button_text' => esc_html__( 'Create Selected Pages/Posts', SXTPP_TEXT_DOMAIN ),
				'scan_no_results_message' => esc_html__( 'No URLs found in the sitemap or all URLs were filtered out.', SXTPP_TEXT_DOMAIN ),
				'scan_error_message' => esc_html__( 'Error scanning sitemap.', SXTPP_TEXT_DOMAIN ),
				'create_error_no_selection' => esc_html__( 'Please select at least one item to create.', SXTPP_TEXT_DOMAIN ),
				'create_error_ajax' => esc_html__( 'AJAX Error during creation.', SXTPP_TEXT_DOMAIN ),
			)
		);
	}

	/**
	 * AJAX callback to scan the sitemap.
	 *
	 * @since 1.0.0
	 */
	public function ajax_scan_sitemap() {
		// Verify nonce.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'sxtpp_scan_sitemap_nonce' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Security check failed.', SXTPP_TEXT_DOMAIN ) ) );
		}

		// Check user capability.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'You do not have sufficient permissions.', SXTPP_TEXT_DOMAIN ) ) );
		}

		$sitemap_url = isset( $_POST['sitemap_url'] ) ? esc_url_raw( wp_unslash( $_POST['sitemap_url'] ) ) : '';
		$default_post_type = isset( $_POST['default_post_type'] ) ? sanitize_key( wp_unslash( $_POST['default_post_type'] ) ) : 'page';

		if ( empty( $sitemap_url ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Sitemap URL is required.', SXTPP_TEXT_DOMAIN ) ) );
		}

		// Instantiate the Sitemap_Parser.
		$parser = new Sitemap_Parser();
		$urls = $parser->parse_sitemap( $sitemap_url );

		if ( is_wp_error( $urls ) ) {
			wp_send_json_error( array( 'message' => $urls->get_error_message() ) );
		}

		if ( empty( $urls ) ) {
			wp_send_json_success( array(
				'message' => esc_html__( 'No URLs found in the sitemap or all URLs were filtered out.', SXTPP_TEXT_DOMAIN ),
				'items'   => array(),
				'count'   => 0,
			) );
		}

		// Prepare data for display in the results table.
		$prepared_items = array();
		// Instantiate Post_Creator (even if just for suggestions at this point).
		$post_creator = new Post_Creator();

		foreach ( $urls as $url ) {
			// Determine suggested title, slug, and post type.
			$suggested_data = $post_creator->get_suggested_post_data( $url, $default_post_type );

			$prepared_items[] = array(
				'original_url'    => $url,
				'suggested_title' => $suggested_data['title'],
				'suggested_slug'  => $suggested_data['slug'],
				'suggested_type'  => $suggested_data['post_type'],
			);
		}

		wp_send_json_success( array(
			'message' => sprintf( esc_html__( 'Successfully scanned sitemap. Found %d URLs.', SXTPP_TEXT_DOMAIN ), count( $prepared_items ) ),
			'items'   => $prepared_items,
			'count'   => count( $prepared_items ),
		) );
	}

	/**
	 * AJAX callback to create posts/pages.
	 *
	 * @since 1.0.0
	 */
	public function ajax_create_posts_pages() {
		// Verify nonce.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'sxtpp_create_posts_pages_nonce' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Security check failed.', SXTPP_TEXT_DOMAIN ) ) );
		}

		// Check user capability.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'You do not have sufficient permissions.', SXTPP_TEXT_DOMAIN ) ) );
		}

		$items_to_create = isset( $_POST['items'] ) ? wp_unslash( $_POST['items'] ) : array();

		if ( empty( $items_to_create ) || ! is_array( $items_to_create ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'No items selected for creation.', SXTPP_TEXT_DOMAIN ) ) );
		}

		$post_creator = new Post_Creator();
		$results = array(
			'created' => 0,
			'skipped' => 0,
			'errors'  => array(),
			'created_urls' => array(), // To help frontend remove created rows.
		);

		foreach ( $items_to_create as $item_encoded ) {
			// Decode the item data from URL-encoded JSON string.
			$item_data = json_decode( urldecode( $item_encoded ), true );

			if ( ! is_array( $item_data ) || empty( $item_data['original_url'] ) ) {
				$results['errors'][] = esc_html__( 'Invalid item data received.', SXTPP_TEXT_DOMAIN );
				continue;
			}

			$post_id = $post_creator->create_post_from_sitemap_item( $item_data );

			if ( is_wp_error( $post_id ) ) {
				$error_code = $post_id->get_error_code();
				$error_message = $post_id->get_error_message();

				if ( 'sxtpp_post_exists' === $error_code ) {
					$results['skipped']++;
					$results['errors'][] = sprintf( esc_html__( 'Skipped "%1$s": %2$s (ID: %3$s).', SXTPP_TEXT_DOMAIN ), $item_data['suggested_title'], $error_message, $post_id->get_error_data('post_id') );
				} else {
					$results['errors'][] = sprintf( esc_html__( 'Failed to create "%1$s": %2$s', SXTPP_TEXT_DOMAIN ), $item_data['suggested_title'], $error_message );
				}
			} else {
				$results['created']++;
				$results['created_urls'][] = $item_data['original_url']; // Track successfully created URLs.
			}
		}

		$message = sprintf( esc_html__( 'Creation process completed. Created: %1$d, Skipped (already exists): %2$d, Failed: %3$d.', SXTPP_TEXT_DOMAIN ), $results['created'], $results['skipped'], count( $results['errors'] ) );

		if ( ! empty( $results['errors'] ) ) {
			wp_send_json_error( array(
				'message' => $message,
				'details' => $results['errors'],
				'created_urls' => $results['created_urls'],
				'results' => $results, // Provide full results for frontend to parse if needed.
			) );
		} else {
			wp_send_json_success( array(
				'message' => $message,
				'created_urls' => $results['created_urls'],
				'results' => $results,
			) );
		}
	}
}