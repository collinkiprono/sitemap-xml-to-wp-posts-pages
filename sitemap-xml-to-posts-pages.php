<?php
/**
 * Plugin Name: Sitemap XML to Posts/Pages
 * Plugin URI:  https://glide.apps/sitemap-xml-to-posts-pages
 * Description: Automatically create WordPress pages or posts from a sitemap.xml URL.
 * Version:     1.0.0
 * Author:      Glide Inc.
 * Author URI:  https://glide.apps
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: sitemap_xml_to_posts_pages
 * Domain Path: /languages
 *
 * @package SitemapXMLToPostsPages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Define plugin constants.
 */
define( 'SXTPP_VERSION', '1.0.0' );
define( 'SXTPP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SXTPP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SXTPP_BASENAME', plugin_basename( __FILE__ ) );
define( 'SXTPP_TEXT_DOMAIN', 'sitemap_xml_to_posts_pages' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing hooks.
 */
require_once SXTPP_PLUGIN_DIR . 'includes/class-sitemap-xml-to-posts-pages.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then this function simply calls the 'run' method in the
 * main plugin class.
 *
 * @since 1.0.0
 */
function run_sitemap_xml_to_posts_pages() {

	$plugin = new Sitemap_XML_To_Posts_Pages();
	$plugin->run();

}
run_sitemap_xml_to_posts_pages();