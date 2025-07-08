<?php
// Display each PDF block right before post title with higher priority
function cpp_display_pdf_before_title($title, $id = null) {
    if (!is_singular('post') || !in_the_loop() || is_admin()) return $title;
    if (get_the_ID() !== $id) return $title;

    $pdfs = get_post_meta($id, '_cpp_pdf_files', true);
    if (!$pdfs || !is_array($pdfs)) return $title;

    $output = '';
    foreach ($pdfs as $index => $pdf) {
        $pdf_title = esc_html($pdf['title']);
        $pdf_url = esc_url($pdf['url']);

        $bg_color = esc_attr($pdf['bg_color'] ?? '#f9f9f9');
        $border_color = esc_attr($pdf['border_color'] ?? '#ccc');
        $button_bg = esc_attr($pdf['button_bg'] ?? '#0073aa');
        $button_text = esc_attr($pdf['button_text'] ?? '#ffffff');
        $font_family = esc_attr($pdf['font_family'] ?? 'Arial');
        $title_size = esc_attr($pdf['title_size'] ?? '18px');
        $button_size = esc_attr($pdf['button_size'] ?? '14px');
        $download_disabled = isset($pdf['download_disabled']) ? $pdf['download_disabled'] : false;

        $output .= '<div class="cpp-pdf-wrapper" style="background-color:' . $bg_color . '; border: 2px solid ' . $border_color . '; padding: 15px; border-radius: 10px; margin-bottom: 20px; position: relative; z-index: 999; margin: 0 auto 20px auto; max-width: 700px; display: block;">';
        $output .= '<div class="cpp-pdf-item">';
        $output .= '<div class="cpp-pdf-title" style="font-weight: bold; font-size:' . $title_size . '; font-family:' . $font_family . '; margin-bottom: 10px;">' . $pdf_title . '</div>';
        
        if ($download_disabled) {
            $output .= '<a class="cpp-download-btn cpp-disabled-download" href="#" onclick="showDownloadMessage(); return false;" style="display: inline-block; padding: 8px 15px; background-color:' . $button_bg . '; color:' . $button_text . '; text-decoration: none; border-radius: 5px; font-size:' . $button_size . '; font-family:' . $font_family . ';">تحميل ملف القانون</a>';
        } else {
            $output .= '<a class="cpp-download-btn cpp-track-download" href="' . $pdf_url . '" target="_blank" download data-post-id="' . $id . '" data-pdf-url="' . $pdf_url . '" data-pdf-title="' . $pdf_title . '" style="display: inline-block; padding: 8px 15px; background-color:' . $button_bg . '; color:' . $button_text . '; text-decoration: none; border-radius: 5px; font-size:' . $button_size . '; font-family:' . $font_family . ';">تحميل ملف القانون</a>';
        }
        
        $output .= '</div>';
        $output .= '</div>';
    }

    // Add the tracking and popup message scripts
    $output .= '
    <script>
    function showDownloadMessage() {
        alert("هذا الملف غير متاح للتحميل حاليا");
    }
    
    jQuery(document).ready(function($) {
        $(".cpp-track-download").on("click", function(e) {
            var postId = $(this).data("post-id");
            var pdfUrl = $(this).data("pdf-url");
            var pdfTitle = $(this).data("pdf-title");
            
            // Track the download via AJAX
            $.ajax({
                url: cpp_ajax.ajax_url,
                type: "POST",
                data: {
                    action: "cpp_track_download",
                    post_id: postId,
                    pdf_url: pdfUrl,
                    pdf_title: pdfTitle,
                    nonce: cpp_ajax.nonce
                }
            });
            
            // Let the download proceed normally
            return true;
        });
    });
    </script>';

    return $output . $title;
}

// Multiple hooks with different priorities to ensure PDF shows at top
add_filter('the_title', 'cpp_display_pdf_before_title', 1, 2); // Highest priority
add_filter('the_content', 'cpp_display_pdf_before_content', 1);

// Alternative method: Hook into content with highest priority
function cpp_display_pdf_before_content($content) {
    if (!is_singular('post') || !in_the_loop() || is_admin()) return $content;
    
    global $post;
    $pdfs = get_post_meta($post->ID, '_cpp_pdf_files', true);
    if (!$pdfs || !is_array($pdfs)) return $content;

    $output = '';
    foreach ($pdfs as $index => $pdf) {
        $pdf_title = esc_html($pdf['title']);
        $pdf_url = esc_url($pdf['url']);

        $bg_color = esc_attr($pdf['bg_color'] ?? '#f9f9f9');
        $border_color = esc_attr($pdf['border_color'] ?? '#ccc');
        $button_bg = esc_attr($pdf['button_bg'] ?? '#0073aa');
        $button_text = esc_attr($pdf['button_text'] ?? '#ffffff');
        $font_family = esc_attr($pdf['font_family'] ?? 'Arial');
        $title_size = esc_attr($pdf['title_size'] ?? '18px');
        $button_size = esc_attr($pdf['button_size'] ?? '14px');
        $download_disabled = isset($pdf['download_disabled']) ? $pdf['download_disabled'] : false;

        $output .= '<div class="cpp-pdf-wrapper cpp-priority-block" style="background-color:' . $bg_color . '; border: 2px solid ' . $border_color . '; padding: 15px; border-radius: 10px; margin: 0 auto 20px auto; max-width: 700px; position: relative; z-index: 999; clear: both; display: block;">';
        $output .= '<div class="cpp-pdf-item">';
        $output .= '<div class="cpp-pdf-title" style="font-weight: bold; font-size:' . $title_size . '; font-family:' . $font_family . '; margin-bottom: 10px;">' . $pdf_title . '</div>';
        
        if ($download_disabled) {
            $output .= '<a class="cpp-download-btn cpp-disabled-download" href="#" onclick="showDownloadMessage(); return false;" style="display: inline-block; padding: 8px 15px; background-color:' . $button_bg . '; color:' . $button_text . '; text-decoration: none; border-radius: 5px; font-size:' . $button_size . '; font-family:' . $font_family . ';">تحميل ملف القانون</a>';
        } else {
            $output .= '<a class="cpp-download-btn cpp-track-download" href="' . $pdf_url . '" target="_blank" download data-post-id="' . $post->ID . '" data-pdf-url="' . $pdf_url . '" data-pdf-title="' . $pdf_title . '" style="display: inline-block; padding: 8px 15px; background-color:' . $button_bg . '; color:' . $button_text . '; text-decoration: none; border-radius: 5px; font-size:' . $button_size . '; font-family:' . $font_family . ';">تحميل ملف القانون</a>';
        }
        
        $output .= '</div>';
        $output .= '</div>';
    }

    return $output . $content;
}

// Additional CSS to ensure priority and hide empty Elementor elements
add_action('wp_head', 'cpp_priority_styles');
function cpp_priority_styles() {
    if (is_singular('post')) {
        echo '<style>
        /* Force PDF blocks to appear at top */
        .cpp-pdf-wrapper {
            position: relative !important;
            z-index: 999 !important;
            clear: both !important;
            width: 100% !important;
            max-width: 700px !important;
            margin: 0 auto 20px auto !important;
            box-sizing: border-box !important;
            display: block !important;
        }
        
        .cpp-priority-block {
            order: -1 !important;
            margin-top: 0 !important;
        }
        
        /* Hide empty Elementor widgets that might interfere */
        .elementor-widget-wrap.elementor-element-populated:empty {
            display: none !important;
        }
        
        /* Hide post-modified-info or move it below PDF blocks */
        .post-modified-info {
            order: 1 !important;
            margin-top: 10px !important;
        }
        
        /* Ensure PDF blocks appear before any other content */
        .entry-content > .cpp-pdf-wrapper:first-child {
            margin-top: 0 !important;
        }
        
        /* Additional specificity for stubborn themes */
        body.single-post .cpp-pdf-wrapper {
            position: relative !important;
            z-index: 999 !important;
            margin: 0 auto 20px auto !important;
            max-width: 700px !important;
        }
        
        /* Center the PDF block content */
        .cpp-pdf-item {
            text-align: center !important;
        }
        
        .cpp-pdf-title {
            text-align: center !important;
        }
        
        .cpp-download-btn {
            display: inline-block !important;
            text-align: center !important;
        }
        </style>';
    }
}

// Force remove empty Elementor elements using JavaScript as backup
add_action('wp_footer', 'cpp_remove_empty_elements');
function cpp_remove_empty_elements() {
    if (is_singular('post')) {
        echo '<script>
        jQuery(document).ready(function($) {
            // Remove empty Elementor widgets
            $(".elementor-widget-wrap.elementor-element-populated").each(function() {
                if ($(this).is(":empty") || $(this).html().trim() === "") {
                    $(this).remove();
                }
            });
            
            // Hide or move post-modified-info below PDF blocks
            $(".post-modified-info").each(function() {
                $(this).css("order", "1");
            });
            
            // Ensure PDF blocks are at the top and centered
            $(".cpp-pdf-wrapper").each(function() {
                $(this).prependTo($(this).closest(".entry-content, .post-content, article"));
                $(this).css({
                    "margin": "0 auto 20px auto",
                    "max-width": "700px",
                    "display": "block"
                });
            });
        });
        </script>';
    }
}
