<?php
/**
 * Plugin Name: Custom PDF Posts
 * Description: Add and display PDF downloads in WordPress posts with style. Contact: mhatem1995@yahoo.com
 * Version: 1.7
 * Author: Marwan hatem
 * Author URI: https://www.linkedin.com/in/marwan-hatem-713269211/
 */

defined('ABSPATH') || exit;

// Enqueue frontend styles
function cpp_enqueue_styles() {
    wp_enqueue_style('cpp-style', plugin_dir_url(__FILE__) . 'assets/style.css');
}
add_action('wp_enqueue_scripts', 'cpp_enqueue_styles');

// Include meta box for admin
require_once plugin_dir_path(__FILE__) . 'admin/meta-box.php';

// Include frontend display
require_once plugin_dir_path(__FILE__) . 'frontend/display-pdfs.php';

// Include download tracking
require_once plugin_dir_path(__FILE__) . 'admin/download-tracker.php';

// Include analytics dashboard
require_once plugin_dir_path(__FILE__) . 'admin/analytics-dashboard.php';

// Create database table on plugin activation
register_activation_hook(__FILE__, 'cpp_create_download_table');

function cpp_create_download_table() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'cpp_pdf_downloads';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        post_id bigint(20) NOT NULL,
        pdf_url varchar(500) NOT NULL,
        pdf_title varchar(255) NOT NULL,
        download_date datetime DEFAULT CURRENT_TIMESTAMP,
        user_ip varchar(45) NOT NULL,
        user_agent text,
        PRIMARY KEY (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Add admin notice for upload issues
add_action('admin_notices', 'cpp_upload_notices');
function cpp_upload_notices() {
    $screen = get_current_screen();
    if ($screen->id !== 'post') return;
    
    if (isset($_GET['cpp_upload_error'])) {
        $error = sanitize_text_field($_GET['cpp_upload_error']);
        echo '<div class="notice notice-error is-dismissible">';
        echo '<p><strong>PDF Upload Error:</strong> ' . esc_html($error) . '</p>';
        echo '</div>';
    }
}

// Increase upload limits for PDFs if needed
add_filter('upload_size_limit', 'cpp_increase_upload_size');
function cpp_increase_upload_size($bytes) {
    return 52428800; // 50MB in bytes
}

// Add media library column to show file types
add_filter('manage_media_columns', 'cpp_add_media_column');
function cpp_add_media_column($columns) {
    $columns['file_type'] = 'File Type';
    return $columns;
}

add_action('manage_media_custom_column', 'cpp_media_column_content', 10, 2);
function cpp_media_column_content($column, $post_id) {
    if ($column === 'file_type') {
        $file_type = get_post_mime_type($post_id);
        $file_url = wp_get_attachment_url($post_id);
        $file_extension = pathinfo($file_url, PATHINFO_EXTENSION);
        
        if ($file_type === 'application/pdf') {
            echo '<span style="color: #0073aa; font-weight: bold;">PDF</span>';
        } else {
            echo '<span style="color: #666;">' . strtoupper($file_extension) . '</span>';
        }
    }
}

// Add debug information for troubleshooting
add_action('wp_ajax_cpp_debug_info', 'cpp_debug_info');
function cpp_debug_info() {
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions');
    }
    
    $debug_info = [
        'php_version' => PHP_VERSION,
        'wordpress_version' => get_bloginfo('version'),
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
        'max_execution_time' => ini_get('max_execution_time'),
        'memory_limit' => ini_get('memory_limit'),
        'plugin_version' => '1.7',
        'uploads_dir' => wp_upload_dir(),
        'mime_types' => get_allowed_mime_types()
    ];
    
    wp_send_json($debug_info);
}

// Add settings page for plugin configuration
add_action('admin_menu', 'cpp_add_settings_page');
function cpp_add_settings_page() {
    add_options_page(
        'PDF Plugin Settings',
        'PDF Plugin',
        'manage_options',
        'cpp-settings',
        'cpp_settings_page'
    );
}

function cpp_settings_page() {
    ?>
    <div class="wrap">
        <h1>PDF Plugin Settings</h1>
        
        <h2>Upload Information</h2>
        <table class="form-table">
            <tr>
                <th>Max Upload Size</th>
                <td><?php echo size_format(wp_max_upload_size()); ?></td>
            </tr>
            <tr>
                <th>Allowed File Types</th>
                <td>
                    <?php
                    $allowed_types = get_allowed_mime_types();
                    if (isset($allowed_types['pdf'])) {
                        echo '<span style="color: green;">✓ PDF files are allowed</span>';
                    } else {
                        echo '<span style="color: red;">✗ PDF files are NOT allowed</span>';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th>Upload Directory</th>
                <td>
                    <?php
                    $upload_dir = wp_upload_dir();
                    echo $upload_dir['basedir'];
                    if (is_writable($upload_dir['basedir'])) {
                        echo ' <span style="color: green;">(Writable)</span>';
                    } else {
                        echo ' <span style="color: red;">(Not Writable)</span>';
                    }
                    ?>
                </td>
            </tr>
        </table>
        
        <h2>Troubleshooting</h2>
        <p>If you're having issues uploading PDFs:</p>
        <ul>
            <li>Make sure your PDF files are not corrupted</li>
            <li>Try uploading files smaller than <?php echo size_format(wp_max_upload_size()); ?></li>
            <li>Check that your file has a .pdf extension</li>
            <li>Ensure the file is actually a PDF (not renamed .htm or other file)</li>
        </ul>
        
        <h2>Debug Information</h2>
        <button type="button" id="get-debug-info" class="button">Get Debug Info</button>
        <div id="debug-output" style="margin-top: 10px;"></div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#get-debug-info').click(function() {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'cpp_debug_info'
                    },
                    success: function(response) {
                        $('#debug-output').html('<pre>' + JSON.stringify(response, null, 2) + '</pre>');
                    }
                });
            });
        });
        </script>
    </div>
    <?php
}
