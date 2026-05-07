<?php
/**
 * Admin View: Settings Page
 *
 * @link       https://yourwebsite.com
 * @since      1.0.0
 *
 * @package    SitemapXMLToPostsPages
 * @subpackage SitemapXMLToPostsPages/admin/views
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get the currently saved default post type, or 'page' if not set.
$sxtpp_default_post_type = get_option( 'sxtpp_default_post_type', 'page' );
?>

<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <div id="sxtpp-messages"></div>

    <form id="sxtpp-sitemap-form" method="post" action="">
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="sxtpp_default_post_type_setting"><?php esc_html_e( 'Default Content Type for New Items:', SXTPP_TEXT_DOMAIN ); ?></label>
                    </th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text"><span><?php esc_html_e( 'Default Content Type for New Items', SXTPP_TEXT_DOMAIN ); ?></span></legend>
                            <label for="sxtpp_default_page_type">
                                <input type="radio" id="sxtpp_default_page_type" name="sxtpp_default_post_type" value="page" <?php checked( 'page', $sxtpp_default_post_type ); ?>>
                                <?php esc_html_e( 'Page', SXTPP_TEXT_DOMAIN ); ?>
                            </label>
                            <br>
                            <label for="sxtpp_default_post_type">
                                <input type="radio" id="sxtpp_default_post_type" name="sxtpp_default_post_type" value="post" <?php checked( 'post', $sxtpp_default_post_type ); ?>>
                                <?php esc_html_e( 'Post', SXTPP_TEXT_DOMAIN ); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e( 'Select the default content type to be created from sitemap URLs. The plugin will attempt to intelligently suggest the type per URL, but this sets the primary preference.', SXTPP_TEXT_DOMAIN ); ?>
                            </p>
                        </fieldset>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="sxtpp_sitemap_url"><?php esc_html_e( 'Sitemap XML URL:', SXTPP_TEXT_DOMAIN ); ?></label>
                    </th>
                    <td>
                        <input type="url" id="sxtpp_sitemap_url" name="sxtpp_sitemap_url" class="regular-text" placeholder="e.g., https://example.com/sitemap.xml" required>
                        <p class="description">
                            <?php esc_html_e( 'Enter the full URL to your sitemap.xml file.', SXTPP_TEXT_DOMAIN ); ?>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>

        <p class="submit">
            <button type="button" id="sxtpp-scan-button" class="button button-primary">
                <?php esc_html_e( 'Scan Sitemap', SXTPP_TEXT_DOMAIN ); ?>
            </button>
            <span class="spinner sxtpp-spinner"></span>
        </p>
    </form>

    <div id="sxtpp-scan-results" style="display:none;">
        <h2><?php esc_html_e( 'Scan Results', SXTPP_TEXT_DOMAIN ); ?> <span id="sxtpp-results-count"></span></h2>
        <p class="description">
            <?php esc_html_e( 'Review the suggested pages/posts below. Select the ones you wish to create.', SXTPP_TEXT_DOMAIN ); ?>
        </p>
        <div class="tablenav top">
            <div class="alignleft actions">
                <button type="button" id="sxtpp-select-all" class="button button-secondary"><?php esc_html_e( 'Select All', SXTPP_TEXT_DOMAIN ); ?></button>
                <button type="button" id="sxtpp-deselect-all" class="button button-secondary"><?php esc_html_e( 'Deselect All', SXTPP_TEXT_DOMAIN ); ?></button>
            </div>
        </div>
        <table class="wp-list-table widefat fixed striped posts">
            <thead>
                <tr>
                    <td id="cb" class="manage-column column-cb check-column"><input type="checkbox" id="sxtpp-master-checkbox"></td>
                    <th scope="col" class="manage-column column-title"><?php esc_html_e( 'Suggested Title', SXTPP_TEXT_DOMAIN ); ?></th>
                    <th scope="col" class="manage-column column-slug"><?php esc_html_e( 'Suggested Slug', SXTPP_TEXT_DOMAIN ); ?></th>
                    <th scope="col" class="manage-column column-type"><?php esc_html_e( 'Suggested Type', SXTPP_TEXT_DOMAIN ); ?></th>
                    <th scope="col" class="manage-column column-url"><?php esc_html_e( 'Original URL', SXTPP_TEXT_DOMAIN ); ?></th>
                </tr>
            </thead>
            <tbody id="sxtpp-results-tbody">
                <tr><td colspan="5"><?php esc_html_e( 'No results yet. Scan a sitemap to see items.', SXTPP_TEXT_DOMAIN ); ?></td></tr>
            </tbody>
            <tfoot>
                <tr>
                    <td class="manage-column column-cb check-column"><input type="checkbox" id="sxtpp-master-checkbox-footer"></td>
                    <th scope="col" class="manage-column column-title"><?php esc_html_e( 'Suggested Title', SXTPP_TEXT_DOMAIN ); ?></th>
                    <th scope="col" class="manage-column column-slug"><?php esc_html_e( 'Suggested Slug', SXTPP_TEXT_DOMAIN ); ?></th>
                    <th scope="col" class="manage-column column-type"><?php esc_html_e( 'Suggested Type', SXTPP_TEXT_DOMAIN ); ?></th>
                    <th scope="col" class="manage-column column-url"><?php esc_html_e( 'Original URL', SXTPP_TEXT_DOMAIN ); ?></th>
                </tr>
            </tfoot>
        </table>
        <div class="tablenav bottom">
            <div class="alignleft actions">
                <button type="button" id="sxtpp-select-all-bottom" class="button button-secondary"><?php esc_html_e( 'Select All', SXTPP_TEXT_DOMAIN ); ?></button>
                <button type="button" id="sxtpp-deselect-all-bottom" class="button button-secondary"><?php esc_html_e( 'Deselect All', SXTPP_TEXT_DOMAIN ); ?></button>
            </div>
            <div class="alignright actions">
                <button type="button" id="sxtpp-create-button" class="button button-primary" style="display:none;">
                    <?php esc_html_e( 'Create Selected Pages/Posts', SXTPP_TEXT_DOMAIN ); ?>
                </button>
            </div>
            <br class="clear">
        </div>
        <span class="spinner sxtpp-create-spinner"></span>
    </div>
</div>