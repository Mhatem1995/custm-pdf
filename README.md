=== Custom PDF Plugin ===
Contributors: Marwan hatem mohamed
Tags: pdf, download, legal, documents, custom
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A powerful WordPress plugin for managing and displaying PDF documents with custom styling and download tracking.

== Description ==

Custom PDF Plugin allows you to easily attach PDF documents to your WordPress posts with customizable styling and comprehensive download tracking. Perfect for legal documents, reports, guides, and any downloadable content.

**Key Features:**

* **Easy PDF Management**: Add multiple PDF files to any post with a simple interface
* **Custom Styling**: Customize colors, fonts, and sizes for each PDF block
* **Download Tracking**: Track PDF downloads with detailed analytics including IP addresses, user agents, and timestamps
* **Responsive Design**: PDF blocks are fully responsive and work on all devices
* **Download Control**: Option to disable downloads and show custom messages
* **RTL Support**: Full support for right-to-left languages (Arabic, Hebrew, etc.)
* **Professional Display**: Clean, modern PDF blocks that integrate seamlessly with your theme

**Perfect For:**

* Legal websites displaying laws and regulations
* Corporate sites with reports and documents
* Educational platforms with study materials
* Government websites with official documents
* Any site requiring professional document presentation

**Admin Features:**

* Bulk PDF management in WordPress admin
* Download statistics and analytics
* User-friendly metabox interface
* Export download data functionality
* Customizable PDF block appearance

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/custom-pdf-plugin` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Navigate to any post edit screen to start adding PDF files.
4. Use the "PDF Files" metabox to add and customize your PDF documents.
5. Visit "PDF Downloads" in the admin menu to view download statistics.

== Frequently Asked Questions ==

= How do I add PDF files to a post? =

Edit any post and look for the "PDF Files" metabox below the content editor. Click "Add PDF" to upload files and customize their appearance.

= Can I track who downloads my PDFs? =

Yes! The plugin tracks all downloads including IP addresses, timestamps, user agents, and referrer information. View statistics in the "PDF Downloads" admin menu.

= Can I customize the appearance of PDF blocks? =

Absolutely! Each PDF block can have custom background colors, border colors, button colors, fonts, and sizes. Configure these in the PDF metabox when editing a post.

= Does the plugin work with my theme? =

Yes! The plugin is designed to work with any WordPress theme. It uses CSS that adapts to your theme's styling while maintaining its professional appearance.

= Can I disable downloads for certain PDFs? =

Yes! You can disable downloads for any PDF and show a custom message instead. This is useful for preview documents or when you want to collect user information first.

= Does the plugin support RTL languages? =

Yes! The plugin fully supports right-to-left languages like Arabic and Hebrew, with proper text direction and layout.

= Can I export download data? =

Yes! You can export download statistics to CSV format for further analysis or reporting.


== Changelog ==

= 1.0.0 =
* Initial release
* PDF file management with custom styling
* Download tracking and analytics
* Responsive design
* RTL language support
* Admin dashboard for download statistics
* Bulk PDF management
* Export functionality

== Upgrade Notice ==

= 1.0.0 =
Initial release of Custom PDF Plugin with full PDF management and tracking capabilities.

== Support ==

For support, feature requests, or bug reports, please visit our support forum or contact us directly.

== Technical Requirements ==

* WordPress 5.0 or higher
* PHP 7.4 or higher
* MySQL 5.6 or higher
* Modern web browser with JavaScript enabled

== Security ==

This plugin follows WordPress security best practices:
* All user inputs are sanitized and validated
* Database queries use prepared statements
* File uploads are properly validated
* Nonce verification for all forms
* Capability checks for admin functions

== Performance ==

The plugin is optimized for performance:
* Minimal database queries
* Efficient asset loading
* Lightweight CSS and JavaScript
* Proper caching support
* Optimized for large numbers of downloads

== Developers ==

The plugin includes hooks and filters for developers:
* `cpp_before_pdf_display` - Action before PDF block display
* `cpp_after_pdf_display` - Action after PDF block display
* `cpp_pdf_download_tracked` - Action when download is tracked
* `cpp_pdf_block_html` - Filter for PDF block HTML output
* `cpp_download_button_text` - Filter for download button text

== Privacy ==

This plugin collects the following information for download tracking:
* IP addresses of users downloading PDFs
* User agent strings
* Timestamp of downloads
* Referrer information

This data is stored in your WordPress database and is not shared with third parties. You can delete this data at any time from the plugin settings.

== Multisite ==

The plugin is compatible with WordPress Multisite installations and can be network activated or activated on individual sites.

== Translations ==

The plugin is translation-ready and includes:
* English (default)
* Arabic support
* RTL language support

Translation contributions are welcome!
