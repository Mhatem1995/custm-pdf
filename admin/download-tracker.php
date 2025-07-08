<?php
defined('ABSPATH') || exit;

// Add AJAX handler for tracking downloads
add_action('wp_ajax_cpp_track_download', 'cpp_track_download');
add_action('wp_ajax_nopriv_cpp_track_download', 'cpp_track_download');

function cpp_track_download() {
    global $wpdb;
    
    // Verify nonce for security
    if (!wp_verify_nonce($_POST['nonce'], 'cpp_track_download')) {
        wp_die('Security check failed');
    }
    
    $post_id = intval($_POST['post_id']);
    $pdf_url = sanitize_url($_POST['pdf_url']);
    $pdf_title = sanitize_text_field($_POST['pdf_title']);
    $user_ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    $table_name = $wpdb->prefix . 'cpp_pdf_downloads';
    
    $wpdb->insert(
        $table_name,
        array(
            'post_id' => $post_id,
            'pdf_url' => $pdf_url,
            'pdf_title' => $pdf_title,
            'user_ip' => $user_ip,
            'user_agent' => $user_agent
        ),
        array(
            '%d',
            '%s',
            '%s',
            '%s',
            '%s'
        )
    );
    
    wp_die(); // This is required to terminate immediately and return a proper response
}

// Enqueue JavaScript for tracking
add_action('wp_enqueue_scripts', 'cpp_enqueue_tracking_script');

function cpp_enqueue_tracking_script() {
    if (is_singular('post')) {
        wp_enqueue_script('jquery');
        wp_localize_script('jquery', 'cpp_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cpp_track_download')
        ));
    }
}