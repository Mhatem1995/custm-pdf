<?php
// Global flag to prevent duplicate script inclusion
$cpp_script_added = false;

// Function to fix Arabic text with numbers
function cpp_fix_arabic_numbers($text) {
    // Add Left-to-Right Mark (LRM) after numbers to prevent reordering
    $text = preg_replace('/(\d+)/u', '$1‎', $text);
    
    // Add Right-to-Left Mark (RLM) before Arabic text to ensure proper direction
    if (preg_match('/[\x{0600}-\x{06FF}]/u', $text)) {
        $text = '‏' . $text;
    }
    
    return $text;
}

// Display each PDF block right before post title with higher priority
function cpp_display_pdf_before_title($title, $id = null) {
    global $cpp_script_added;
    
    if (!is_singular('post') || !in_the_loop() || is_admin()) return $title;
    if (get_the_ID() !== $id) return $title;

    $pdfs = get_post_meta($id, '_cpp_pdf_files', true);
    if (!$pdfs || !is_array($pdfs)) return $title;

    $output = '';
    foreach ($pdfs as $index => $pdf) {
        $pdf_title = esc_html($pdf['title']);
        // Fix Arabic numbers display
        $pdf_title = cpp_fix_arabic_numbers($pdf_title);
        $pdf_url = esc_url($pdf['url']);

        $bg_color = esc_attr($pdf['bg_color'] ?? '#f9f9f9');
        $border_color = esc_attr($pdf['border_color'] ?? '#ccc');
        $button_bg = esc_attr($pdf['button_bg'] ?? '#0073aa');
        $button_text = esc_attr($pdf['button_text'] ?? '#ffffff');
        $font_family = esc_attr($pdf['font_family'] ?? 'Arial');
        $title_size = esc_attr($pdf['title_size'] ?? '20px');
        $button_size = esc_attr($pdf['button_size'] ?? '16px');
        $download_disabled = isset($pdf['download_disabled']) ? $pdf['download_disabled'] : false;

        $output .= '<div class="cpp-pdf-wrapper" style="background-color:' . $bg_color . '; border: 2px solid ' . $border_color . '; padding: 20px; border-radius: 10px; margin-bottom: 25px; position: relative; z-index: 1000; margin: 0 auto 25px auto; max-width: 900px; width: 95%; display: block;">';
        $output .= '<div class="cpp-pdf-item">';
        $output .= '<div class="cpp-pdf-title" style="font-weight: bold; font-size:' . $title_size . '; font-family:' . $font_family . '; margin-bottom: 15px; text-align: justify; word-wrap: break-word; hyphens: auto; line-height: 1.6; overflow-wrap: break-word; text-align-last: center; direction: rtl; unicode-bidi: embed;">' . $pdf_title . '</div>';
        
        if ($download_disabled) {
            $output .= '<a class="cpp-download-btn cpp-disabled-download" href="#" onclick="showDownloadMessage(); return false;" style="display: inline-block; padding: 12px 20px; background-color:' . $button_bg . '; color:' . $button_text . '; text-decoration: none; border-radius: 5px; font-size:' . $button_size . '; font-family:' . $font_family . '; font-weight: bold; cursor: pointer;">Download PDF</a>';
        } else {
            $output .= '<a class="cpp-download-btn cpp-track-download" href="' . $pdf_url . '" target="_blank" download data-post-id="' . $id . '" data-pdf-url="' . $pdf_url . '" data-pdf-title="' . $pdf_title . '" style="display: inline-block; padding: 12px 20px; background-color:' . $button_bg . '; color:' . $button_text . '; text-decoration: none; border-radius: 5px; font-size:' . $button_size . '; font-family:' . $font_family . '; font-weight: bold; cursor: pointer;">Download PDF</a>';
        }
        
        $output .= '</div>';
        $output .= '</div>';
    }

    return $output . $title;
}

// Multiple hooks with different priorities to ensure PDF shows at top
add_filter('the_title', 'cpp_display_pdf_before_title', 1, 2); // Highest priority
add_filter('the_content', 'cpp_display_pdf_before_content', 1);

// Alternative method: Hook into content with highest priority
function cpp_display_pdf_before_content($content) {
    global $cpp_script_added;
    
    if (!is_singular('post') || !in_the_loop() || is_admin()) return $content;
    
    global $post;
    $pdfs = get_post_meta($post->ID, '_cpp_pdf_files', true);
    if (!$pdfs || !is_array($pdfs)) return $content;

    $output = '';
    foreach ($pdfs as $index => $pdf) {
        $pdf_title = esc_html($pdf['title']);
        // Fix Arabic numbers display
        $pdf_title = cpp_fix_arabic_numbers($pdf_title);
        $pdf_url = esc_url($pdf['url']);

        $bg_color = esc_attr($pdf['bg_color'] ?? '#f9f9f9');
        $border_color = esc_attr($pdf['border_color'] ?? '#ccc');
        $button_bg = esc_attr($pdf['button_bg'] ?? '#0073aa');
        $button_text = esc_attr($pdf['button_text'] ?? '#ffffff');
        $font_family = esc_attr($pdf['font_family'] ?? 'Arial');
        $title_size = esc_attr($pdf['title_size'] ?? '20px');
        $button_size = esc_attr($pdf['button_size'] ?? '16px');
        $download_disabled = isset($pdf['download_disabled']) ? $pdf['download_disabled'] : false;

        $output .= '<div class="cpp-pdf-wrapper cpp-priority-block" style="background-color:' . $bg_color . '; border: 2px solid ' . $border_color . '; padding: 20px; border-radius: 10px; margin: 0 auto 25px auto; max-width: 900px; width: 95%; position: relative; z-index: 1000; clear: both; display: block;">';
        $output .= '<div class="cpp-pdf-item">';
        $output .= '<div class="cpp-pdf-title" style="font-weight: bold; font-size:' . $title_size . '; font-family:' . $font_family . '; margin-bottom: 15px; text-align: justify; word-wrap: break-word; hyphens: auto; line-height: 1.6; overflow-wrap: break-word; text-align-last: center; direction: rtl; unicode-bidi: embed;">' . $pdf_title . '</div>';
        
        if ($download_disabled) {
            $output .= '<a class="cpp-download-btn cpp-disabled-download" href="#" onclick="showDownloadMessage(); return false;" style="display: inline-block; padding: 12px 20px; background-color:' . $button_bg . '; color:' . $button_text . '; text-decoration: none; border-radius: 5px; font-size:' . $button_size . '; font-family:' . $font_family . '; font-weight: bold; cursor: pointer;">Download PDF</a>';
        } else {
            $output .= '<a class="cpp-download-btn cpp-track-download" href="' . $pdf_url . '" target="_blank" download data-post-id="' . $post->ID . '" data-pdf-url="' . $pdf_url . '" data-pdf-title="' . $pdf_title . '" style="display: inline-block; padding: 12px 20px; background-color:' . $button_bg . '; color:' . $button_text . '; text-decoration: none; border-radius: 5px; font-size:' . $button_size . '; font-family:' . $font_family . '; font-weight: bold; cursor: pointer;">Download PDF</a>';
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
        /* Force PDF blocks to appear at top with wider width */
        .cpp-pdf-wrapper {
            position: relative !important;
            z-index: 1000 !important;
            clear: both !important;
            width: 95% !important;
            max-width: 900px !important;
            margin: 0 auto 25px auto !important;
            box-sizing: border-box !important;
            display: block !important;
            padding: 20px !important;
        }
        
        .cpp-priority-block {
            order: -999 !important;
            margin-top: 0 !important;
        }
        
        /* Make PDF title with proper text wrapping and BiDi support */
        .cpp-pdf-title {
            font-weight: bold !important;
            font-size: 20px !important;
            text-align: justify !important;
            text-align-last: center !important;
            margin-bottom: 15px !important;
            word-wrap: break-word !important;
            overflow-wrap: break-word !important;
            hyphens: auto !important;
            line-height: 1.6 !important;
            max-width: 100% !important;
            display: block !important;
            white-space: normal !important;
            direction: rtl !important;
            unicode-bidi: embed !important;
            text-rendering: optimizeLegibility !important;
            -webkit-font-feature-settings: "liga", "kern" !important;
            font-feature-settings: "liga", "kern" !important;
        }
        
        /* Enhanced Arabic text support with proper number handling */
        .cpp-pdf-title {
            writing-mode: horizontal-tb !important;
            text-orientation: mixed !important;
            font-variant-numeric: lining-nums !important;
        }
        
        .cpp-download-btn {
            font-weight: bold !important;
            font-size: 16px !important;
            padding: 12px 20px !important;
            display: inline-block !important;
            text-align: center !important;
            cursor: pointer !important;
            direction: ltr !important;
        }
        
        /* Hide empty Elementor widgets that might interfere */
        .elementor-widget-wrap.elementor-element-populated:empty {
            display: none !important;
        }
        
        /* Force PDF blocks to appear before any other content including post-modified-info */
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
            z-index: 1000 !important;
            margin: 0 auto 25px auto !important;
            max-width: 900px !important;
            width: 95% !important;
        }
        
        /* Center the PDF block content */
        .cpp-pdf-item {
            text-align: center !important;
        }
        
        /* Hide any elements that might appear above PDF blocks */
        .cpp-pdf-wrapper ~ * {
            position: relative !important;
            z-index: 999 !important;
        }
        
        /* Force positioning to be above everything */
        .cpp-pdf-wrapper {
            position: relative !important;
            z-index: 1001 !important;
        }
        
        /* Ensure PDF blocks are the first visible elements */
        .entry-content .cpp-pdf-wrapper,
        .post-content .cpp-pdf-wrapper,
        article .cpp-pdf-wrapper {
            position: relative !important;
            z-index: 1001 !important;
            order: -999 !important;
        }
        
        /* Better text wrapping for Arabic text with numbers */
        .cpp-pdf-title {
            -webkit-hyphens: auto !important;
            -moz-hyphens: auto !important;
            -ms-hyphens: auto !important;
            hyphens: auto !important;
            word-break: keep-all !important;
            overflow-wrap: break-word !important;
            text-justify: inter-word !important;
            bidi-override: none !important;
        }
        
        /* Ensure proper number display in RTL context */
        .cpp-pdf-title {
            -webkit-text-security: none !important;
            text-security: none !important;
            font-variant-numeric: normal !important;
        }
        </style>';
    }
}

// Force remove empty Elementor elements and fix Arabic text rendering
add_action('wp_footer', 'cpp_remove_empty_elements_and_fix_text');
function cpp_remove_empty_elements_and_fix_text() {
    if (is_singular('post')) {
        echo '<script>
        jQuery(document).ready(function($) {
            // Remove empty Elementor widgets
            $(".elementor-widget-wrap.elementor-element-populated").each(function() {
                if ($(this).is(":empty") || $(this).html().trim() === "") {
                    $(this).remove();
                }
            });
            
            // Fix Arabic text with numbers
            function fixArabicNumbers(text) {
                // Add Left-to-Right Mark (LRM) after numbers
                text = text.replace(/(\d+)/g, "$1‎");
                
                // Add Right-to-Left Mark (RLM) at the beginning if Arabic text is detected
                if (/[\u0600-\u06FF]/.test(text)) {
                    text = "‏" + text;
                }
                
                return text;
            }
            
            // Force PDF blocks to be the first element and fix text
            $(".cpp-pdf-wrapper").each(function() {
                var $this = $(this);
                var $parent = $this.closest(".entry-content, .post-content, article, .elementor-widget-container");
                
                if ($parent.length) {
                    $this.prependTo($parent);
                }
                
                // Ensure proper styling
                $this.css({
                    "margin": "0 auto 25px auto",
                    "max-width": "900px",
                    "width": "95%",
                    "display": "block",
                    "position": "relative",
                    "z-index": "1001",
                    "clear": "both"
                });
                
                // Fix Arabic text rendering in PDF titles
                var $title = $this.find(".cpp-pdf-title");
                var titleText = $title.text();
                
                if (/[\u0600-\u06FF]/.test(titleText)) {
                    var fixedText = fixArabicNumbers(titleText);
                    $title.html(fixedText);
                    
                    $title.css({
                        "direction": "rtl",
                        "unicode-bidi": "embed",
                        "text-align": "justify",
                        "text-align-last": "center",
                        "word-wrap": "break-word",
                        "overflow-wrap": "break-word",
                        "line-height": "1.6",
                        "white-space": "normal",
                        "font-variant-numeric": "lining-nums",
                        "text-rendering": "optimizeLegibility"
                    });
                }
            });
            
            // Hide or move post-modified-info and other elements below PDF blocks
            $(".post-modified-info").each(function() {
                $(this).css({
                    "order": "1",
                    "z-index": "999"
                });
            });
            
            // Ensure proper text direction for mixed content
            $(".cpp-pdf-title").each(function() {
                var $title = $(this);
                var text = $title.text();
                
                // Enhanced Arabic text handling
                if (/[\u0600-\u06FF]/.test(text)) {
                    // Force RTL direction with proper BiDi handling
                    $title.attr({
                        "dir": "rtl",
                        "lang": "ar"
                    });
                    
                    // Apply BiDi isolation for numbers
                    var processedText = text.replace(/(\d+)/g, function(match) {
                        return "<bdi dir=\"ltr\">" + match + "</bdi>";
                    });
                    
                    $title.html(processedText);
                } else {
                    $title.attr("dir", "ltr");
                }
            });
        });
        </script>';
    }
}
