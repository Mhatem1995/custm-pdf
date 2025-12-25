<?php
defined('ABSPATH') || exit;

// Add AJAX handler for tracking downloads
add_action('wp_ajax_cpp_track_download', 'cpp_track_download');
add_action('wp_ajax_nopriv_cpp_track_download', 'cpp_track_download');

function cpp_track_download() {
    global $wpdb;
    
    // Add debugging
    error_log('CPP: Track download called with data: ' . print_r($_POST, true));
    
    // Check if required POST data exists
    if (!isset($_POST['nonce']) || !isset($_POST['post_id']) || !isset($_POST['pdf_url']) || !isset($_POST['pdf_title'])) {
        error_log('CPP: Missing required data');
        wp_die('Missing required data');
    }
    
    // Verify nonce for security
    if (!wp_verify_nonce($_POST['nonce'], 'cpp_track_download')) {
        error_log('CPP: Security check failed');
        wp_die('Security check failed');
    }
    
    $post_id = intval($_POST['post_id']);
    $pdf_url = sanitize_url($_POST['pdf_url']);
    $pdf_title = sanitize_text_field($_POST['pdf_title']);
    $user_ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    $table_name = $wpdb->prefix . 'cpp_pdf_downloads';
    
    $result = $wpdb->insert(
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
    
    if ($result === false) {
        error_log('CPP: Database insert failed: ' . $wpdb->last_error);
        wp_die('Database error');
    }
    
    error_log('CPP: Download tracked successfully for post ' . $post_id);
    wp_die('success'); // Return success message
}

// Enqueue JavaScript for tracking
add_action('wp_enqueue_scripts', 'cpp_enqueue_tracking_script');

function cpp_enqueue_tracking_script() {
    if (is_singular('post')) {
        wp_enqueue_script('jquery');
        
        // Enqueue the tracking script with jQuery dependency
        wp_enqueue_script(
            'cpp-tracking', 
            plugin_dir_url(__FILE__) . '../assets/tracking.js', 
            array('jquery'), 
            '1.0', 
            true
        );
        
        wp_localize_script('cpp-tracking', 'cpp_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cpp_track_download')
        ));
    }
}

// Add debug function to check if AJAX is properly set up
add_action('wp_footer', 'cpp_debug_ajax');
function cpp_debug_ajax() {
    if (is_singular('post')) {
        echo '<script>
        console.log("CPP Debug:", {
            ajax_url: typeof cpp_ajax !== "undefined" ? cpp_ajax.ajax_url : "undefined",
            nonce: typeof cpp_ajax !== "undefined" ? cpp_ajax.nonce : "undefined",
            jquery_loaded: typeof jQuery !== "undefined"
        });
        </script>';
    }
}
