<?php
/**
 * Plugin Name: Custom PDF Posts
 * Description: Add and display PDF downloads in WordPress posts with style. Contact: mhatem1995@yahoo.com
 * Version: 1.6
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