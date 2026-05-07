<?php
/**
 * The sitemap parsing functionality.
 *
 * @link       https://yourwebsite.com
 * @since      1.0.0
 *
 * @package    SitemapXMLToPostsPages
 * @subpackage SitemapXMLToPostsPages/includes
 */

/**
 * The sitemap parsing functionality.
 *
 * Handles fetching, parsing, and extracting URLs from sitemap XML files.
 *
 * @since      1.0.0
 * @package    SitemapXMLToPostsPages
 * @subpackage SitemapXMLToPostsPages/includes
 * @author     Your Name/Company <you@example.com>
 */
class Sitemap_Parser {

	/**
	 * Fetches and parses a sitemap XML URL.
	 *
	 * @since 1.0.0
	 * @param string $sitemap_url The URL of the sitemap.xml or sitemap index file.
	 * @return array|WP_Error An array of parsed URLs, or WP_Error on failure.
	 */
	public function parse_sitemap( $sitemap_url ) {
		$sitemap_url = esc_url_raw( $sitemap_url );

		// Validate URL.
		if ( ! filter_var( $sitemap_url, FILTER_VALIDATE_URL ) ) {
			return new WP_Error( 'sxtpp_invalid_url', esc_html__( 'Invalid sitemap URL provided.', SXTPP_TEXT_DOMAIN ) );
		}

		$response = wp_remote_get( $sitemap_url, array(
			'timeout' => 30, // Increase timeout for potentially large sitemaps.
			'sslverify' => false, // Set to true in production if SSL cert is valid.
			'user-agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' ),
		) );

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'sxtpp_fetch_error', sprintf( esc_html__( 'Could not fetch sitemap: %s', SXTPP_TEXT_DOMAIN ), $response->get_error_message() ) );
		}

		$body = wp_remote_retrieve_body( $response );

		if ( empty( $body ) ) {
			return new WP_Error( 'sxtpp_empty_sitemap', esc_html__( 'Sitemap content is empty.', SXTPP_TEXT_DOMAIN ) );
		}

		return $this->process_xml_content( $body );
	}

	/**
	 * Processes the XML content to extract URLs.
	 * Handles both sitemap and sitemap index formats recursively.
	 *
	 * @since 1.0.0
	 * @access private
	 * @param string $xml_content The raw XML content of the sitemap.
	 * @return array|WP_Error An array of URLs, or WP_Error on failure.
	 */
	private function process_xml_content( $xml_content ) {
		libxml_use_internal_errors( true ); // Enable internal error handling for XML.
		$xml = simplexml_load_string( $xml_content );

		if ( false === $xml ) {
			$errors = libxml_get_errors();
			libxml_clear_errors();
			$error_message = esc_html__( 'Failed to parse XML:', SXTPP_TEXT_DOMAIN );
			foreach ( $errors as $error ) {
				$error_message .= ' ' . $error->message;
			}
			return new WP_Error( 'sxtpp_xml_parse_error', $error_message );
		}

		$urls = array();

		// Check if it's a sitemap index (contains <sitemap> tags).
		if ( isset( $xml->sitemap ) ) {
			foreach ( $xml->sitemap as $sitemap ) {
				if ( isset( $sitemap->loc ) ) {
					$sub_sitemap_url = (string) $sitemap->loc;
					$sub_urls = $this->parse_sitemap( $sub_sitemap_url ); // Recursive call.
					if ( is_wp_error( $sub_urls ) ) {
						// Log or handle error for sub-sitemap, but try to continue.
						error_log( sprintf( 'Sitemap XML to Posts/Pages: Error parsing sub-sitemap %s - %s', $sub_sitemap_url, $sub_urls->get_error_message() ) );
						continue;
					}
					$urls = array_merge( $urls, $sub_urls );
				}
			}
		} elseif ( isset( $xml->url ) ) { // It's a regular sitemap (contains <url> tags).
			foreach ( $xml->url as $url_element ) {
				if ( isset( $url_element->loc ) ) {
					$loc = (string) $url_element->loc;
					// Apply filter to allow customization of URLs.
					$filtered_loc = apply_filters( 'sxtpp_filter_sitemap_url', $loc, $url_element );

					if ( false !== $filtered_loc && ! empty( $filtered_loc ) ) { // Allow filter to remove URLs by returning false or empty.
						$urls[] = $filtered_loc;
					}
				}
			}
		} else {
			return new WP_Error( 'sxtpp_unknown_sitemap_format', esc_html__( 'Sitemap format not recognized. Missing <sitemap> or <url> tags.', SXTPP_TEXT_DOMAIN ) );
		}

		// Ensure unique URLs.
		$urls = array_unique( $urls );

		// Apply final filter to the entire list of URLs before returning.
		$urls = apply_filters( 'sxtpp_final_filtered_sitemap_urls', $urls, $sitemap_url );

		return $urls;
	}
}