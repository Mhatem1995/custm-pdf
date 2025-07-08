<?php
// Display each PDF block right before post title
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

        $output .= '<div class="cpp-pdf-wrapper" style="background-color:' . $bg_color . '; border: 2px solid ' . $border_color . '; padding: 15px; border-radius: 10px; margin-bottom: 20px;">';
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
add_filter('the_title', 'cpp_display_pdf_before_title', 10, 2);