<?php
/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://yourwebsite.com
 * @since      1.0.0
 *
 * @package    SitemapXMLToPostsPages
 * @subpackage SitemapXMLToPostsPages/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    SitemapXMLToPostsPages
 * @subpackage SitemapXMLToPostsPages/includes
 * @author     Your Name/Company <you@example.com>
 */
class Sitemap_XML_To_Posts_Pages_i18n {

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			SXTPP_TEXT_DOMAIN, // Defined in main plugin file.
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}
}