<?php
/**
 * The file that defines the core plugin class.
 *
 * A class definition that holds the Sitemap XML To Posts/Pages plugin's
 * core functionality.
 *
 * @link       https://yourwebsite.com
 * @since      1.0.0
 *
 * @package    SitemapXMLToPostsPages
 * @subpackage SitemapXMLToPostsPages/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks,
 * and public-facing hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    SitemapXMLToPostsPages
 * @subpackage SitemapXMLToPostsPages/includes
 * @author     Your Name/Company <you@example.com>
 */
class Sitemap_XML_To_Posts_Pages {

	/**
	 * The loader that's responsible for maintaining and registering all hooks.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Sitemap_XML_To_Posts_Pages_Loader    $loader    Maintains and registers all hooks.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->plugin_name = 'sitemap-xml-to-posts-pages';
		$this->version = SXTPP_VERSION; // Use constant from main plugin file.

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		// $this->define_public_hooks(); // Not needed for this plugin.
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Sitemap_XML_To_Posts_Pages_Loader. Orchestrates the hooks of the plugin.
	 * - Sitemap_XML_To_Posts_Pages_i18n. Defines everything that specifies the plugin's
	 * internationalization and localization.
	 * - Sitemap_XML_To_Posts_Pages_Admin. Defines all hooks for the admin area.
	 * - Sitemap_XML_To_Posts_Pages_Sitemap_Parser. Handles sitemap fetching and parsing.
	 * - Sitemap_XML_To_Posts_Pages_Post_Creator. Handles post/page creation.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once SXTPP_PLUGIN_DIR . 'includes/class-sitemap-xml-to-posts-pages-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once SXTPP_PLUGIN_DIR . 'includes/class-sitemap-xml-to-posts-pages-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once SXTPP_PLUGIN_DIR . 'includes/class-admin-settings.php'; // Renamed for clarity.

		/**
		 * The class responsible for fetching and parsing sitemap XML.
		 */
		require_once SXTPP_PLUGIN_DIR . 'includes/class-sitemap-parser.php';

		/**
		 * The class responsible for creating WordPress posts/pages.
		 */
		require_once SXTPP_PLUGIN_DIR . 'includes/class-post-creator.php';

		$this->loader = new Sitemap_XML_To_Posts_Pages_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Sitemap_XML_To_Posts_Pages_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new Sitemap_XML_To_Posts_Pages_i18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$admin_settings = new Admin_Settings( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_menu', $admin_settings, 'add_plugin_admin_menu' );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin_settings, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin_settings, 'enqueue_scripts' );

		// AJAX hooks.
		$this->loader->add_action( 'wp_ajax_sxtpp_scan_sitemap', $admin_settings, 'ajax_scan_sitemap' );
		$this->loader->add_action( 'wp_ajax_sxtpp_create_posts_pages', $admin_settings, 'ajax_create_posts_pages' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Sitemap_XML_To_Posts_Pages_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}