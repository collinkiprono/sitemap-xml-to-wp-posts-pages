# Sitemap XML to WordPress Posts/Pages
The "Sitemap XML to Posts/Pages" plugin is a free, open-source WordPress plugin designed to significantly streamline the process of migrating or creating content within a WordPress site. It allows users to effortlessly import URLs from any standard sitemap.xml file and automatically generate corresponding WordPress posts or pages as drafts.
## Why It Was Developed
Manually creating dozens, hundreds, or even thousands of pages or posts in WordPress can be an incredibly time-consuming and tedious task. Whether you're moving a site to WordPress, integrating a static site's content, or simply need to pre-populate a WordPress instance with a predefined URL structure, the repetitive process of copying URLs, deriving titles and slugs, and setting post types can be a major bottleneck.
### Sitemap Fetching and Parsing
- **URL Input**: Users provide the URL of their sitemap.xml file (e.g., https://example.com/sitemap.xml).
- **Robust Fetching**: Utilizes WordPress's wp_remote_get to securely fetch the sitemap content, with a configurable timeout for large files.
- **Recursive Parsing**: Intelligently handles both standard sitemap.xml files (containing <url> entries) and sitemap index files (containing <sitemap> entries), recursively fetching and parsing all linked sitemaps to gather a comprehensive list of URLs.
- **Error Handling**: Provides clear error messages for invalid sitemap URLs, fetching issues, or XML parsing errors.
### Installation
- Download the plugin ZIP file.
- Upload it via the WordPress admin dashboard (Plugins > Add New > Upload Plugin).
- Activate the plugin.
- Navigate to Tools > Sitemap XML to Posts/Pages in your WordPress admin menu
