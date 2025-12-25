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

// Force PDF blocks to appear at the very beginning of content
function cpp_force_pdf_at_top($content) {
    global $cpp_script_added;
    
    if (!is_singular('post') || !in_the_loop() || is_admin()) return $content;
    
    global $post;
    $pdfs = get_post_meta($post->ID, '_cpp_pdf_files', true);
    if (!$pdfs || !is_array($pdfs)) return $content;

    $pdf_output = '';
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

        $pdf_output .= '<div class="cpp-pdf-wrapper cpp-priority-first" style="background-color:' . $bg_color . '; border: 2px solid ' . $border_color . '; padding: 20px; border-radius: 10px; margin: 0 auto 25px auto; max-width: 900px; width: 95%; position: relative; z-index: 9999; clear: both; display: block !important; visibility: visible !important;">';
        $pdf_output .= '<div class="cpp-pdf-item">';
        $pdf_output .= '<div class="cpp-pdf-title" style="font-weight: bold; font-size:' . $title_size . '; font-family:' . $font_family . '; margin-bottom: 15px; text-align: justify; word-wrap: break-word; hyphens: auto; line-height: 1.6; overflow-wrap: break-word; text-align-last: center; direction: rtl; unicode-bidi: embed;">' . $pdf_title . '</div>';
        
        if ($download_disabled) {
            $pdf_output .= '<a class="cpp-download-btn cpp-disabled-download" href="#" onclick="showDownloadMessage(); return false;" style="display: inline-block; padding: 12px 20px; background-color:' . $button_bg . '; color:' . $button_text . '; text-decoration: none; border-radius: 5px; font-size:' . $button_size . '; font-family:' . $font_family . '; font-weight: bold; cursor: pointer;">Download PDF</a>';
        } else {
            $pdf_output .= '<a class="cpp-download-btn cpp-track-download" href="' . $pdf_url . '" target="_blank" download data-post-id="' . $post->ID . '" data-pdf-url="' . $pdf_url . '" data-pdf-title="' . $pdf_title . '" style="display: inline-block; padding: 12px 20px; background-color:' . $button_bg . '; color:' . $button_text . '; text-decoration: none; border-radius: 5px; font-size:' . $button_size . '; font-family:' . $font_family . '; font-weight: bold; cursor: pointer;">Download PDF</a>';
        }
        
        $pdf_output .= '</div>';
        $pdf_output .= '</div>';
    }

    // Return PDF blocks first, then original content
    return $pdf_output . $content;
}

// Hook with maximum priority to ensure PDF blocks appear first
add_filter('the_content', 'cpp_force_pdf_at_top', 1);

// Additional method using template hooks if available
add_action('genesis_entry_content', 'cpp_display_pdfs_genesis', 1);
add_action('tha_entry_content_before', 'cpp_display_pdfs_tha', 1);

function cpp_display_pdfs_genesis() {
    cpp_display_pdfs_hook();
}

function cpp_display_pdfs_tha() {
    cpp_display_pdfs_hook();
}

function cpp_display_pdfs_hook() {
    global $post;
    if (!is_singular('post') || is_admin()) return;
    
    $pdfs = get_post_meta($post->ID, '_cpp_pdf_files', true);
    if (!$pdfs || !is_array($pdfs)) return;
    
    foreach ($pdfs as $index => $pdf) {
        $pdf_title = esc_html($pdf['title']);
        $pdf_title = cpp_fix_arabic_numbers($pdf_title);
        $pdf_url = esc_url($pdf['url']);

        $bg_color = esc_attr($pdf['bg_color'] ?? '#f9f9f9');
        $border_color = esc_attr($pdf['border_color'] ?? '#ccc');
        $button_bg = esc_attr($pdf['button_bg'] ?? '#0073aa');
        $button_text = esc_attr($pdf['button_text'] ?? '#ffffff');
        $download_disabled = isset($pdf['download_disabled']) ? $pdf['download_disabled'] : false;

        echo '<div class="cpp-pdf-wrapper" style="background-color:' . $bg_color . '; border: 2px solid ' . $border_color . '; padding: 20px; border-radius: 10px; margin: 0 auto 25px auto; max-width: 900px; width: 95%; position: relative; z-index: 9999; clear: both; display: block;">';
        echo '<div class="cpp-pdf-item">';
        echo '<div class="cpp-pdf-title" style="font-weight: bold; font-size: 20px; margin-bottom: 15px; text-align: justify; word-wrap: break-word; hyphens: auto; line-height: 1.6; overflow-wrap: break-word; text-align-last: center; direction: rtl; unicode-bidi: embed;">' . $pdf_title . '</div>';
        
        if ($download_disabled) {
            echo '<a class="cpp-download-btn cpp-disabled-download" href="#" onclick="showDownloadMessage(); return false;" style="display: inline-block; padding: 12px 20px; background-color:' . $button_bg . '; color:' . $button_text . '; text-decoration: none; border-radius: 5px; font-weight: bold; cursor: pointer;">Download PDF</a>';
        } else {
            echo '<a class="cpp-download-btn cpp-track-download" href="' . $pdf_url . '" target="_blank" download data-post-id="' . $post->ID . '" data-pdf-url="' . $pdf_url . '" data-pdf-title="' . $pdf_title . '" style="display: inline-block; padding: 12px 20px; background-color:' . $button_bg . '; color:' . $button_text . '; text-decoration: none; border-radius: 5px; font-weight: bold; cursor: pointer;">Download PDF</a>';
        }
        
        echo '</div>';
        echo '</div>';
    }
}

// Enhanced CSS to hide ACF blocks and force PDF positioning
add_action('wp_head', 'cpp_enhanced_priority_styles');
function cpp_enhanced_priority_styles() {
    if (is_singular('post')) {
        echo '<style>
        /* Hide empty ACF blocks and other plugin blocks that create empty divs */
        .acf-block-preview:empty,
        .acf-block-component:empty,
        .wp-block-acf:empty,
        .wp-block:empty,
        .elementor-widget-wrap:empty,
        .elementor-element-populated:empty,
        .block-editor-block-list__block:empty,
        [class*="acf"]:empty,
        [class*="block"]:empty,
        .wp-block-group:empty,
        .wp-block-columns:empty {
            display: none !important;
            visibility: hidden !important;
            height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        /* Hide ACF blocks that only contain whitespace or non-breaking spaces */
        .acf-block-preview:not(:has(*)),
        .wp-block-acf:not(:has(*)) {
            display: none !important;
        }
        
        /* Force PDF blocks to appear at the very top with highest priority */
        .cpp-pdf-wrapper {
            position: relative !important;
            z-index: 9999 !important;
            clear: both !important;
            width: 95% !important;
            max-width: 900px !important;
            margin: 0 auto 25px auto !important;
            box-sizing: border-box !important;
            display: block !important;
            padding: 20px !important;
            order: -9999 !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        
        /* Force PDF blocks to be the absolute first content */
        .cpp-priority-first {
            position: relative !important;
            z-index: 10000 !important;
            order: -10000 !important;
            margin-top: 0 !important;
        }
        
        /* PDF title styling with enhanced Arabic support */
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
        
        .cpp-download-btn {
            font-weight: bold !important;
            font-size: 16px !important;
            padding: 12px 20px !important;
            display: inline-block !important;
            text-align: center !important;
            cursor: pointer !important;
            direction: ltr !important;
        }
        
        /* Force the ahmad circle text to appear below PDF blocks */
        .elementor-text-editor p,
        .entry-content > p,
        .post-content > p {
            position: relative !important;
            z-index: 1000 !important;
            order: 1000 !important;
        }
        
        /* Hide any empty containers that might interfere */
        .elementor-container:empty,
        .elementor-row:empty,
        .elementor-column:empty,
        .elementor-widget-container:empty {
            display: none !important;
            height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        /* Ensure proper content flow */
        .entry-content,
        .post-content,
        article {
            display: flex !important;
            flex-direction: column !important;
        }
        
        .entry-content .cpp-pdf-wrapper,
        .post-content .cpp-pdf-wrapper,
        article .cpp-pdf-wrapper {
            order: -9999 !important;
            flex-shrink: 0 !important;
        }
        
        /* Force everything else to appear after PDF blocks */
        .entry-content > *:not(.cpp-pdf-wrapper),
        .post-content > *:not(.cpp-pdf-wrapper),
        article > *:not(.cpp-pdf-wrapper) {
            order: 1 !important;
        }
        
        /* Specific targeting for ahmad circle text */
        p:contains("ahmad"),
        div:contains("ahmad"),
        span:contains("ahmad") {
            order: 2000 !important;
            margin-top: 20px !important;
        }
        
        /* Additional ACF block hiding */
        [data-type="acf"]:empty,
        [data-block="acf"]:empty,
        .wp-block[data-type="acf/custom-block"]:empty {
            display: none !important;
            visibility: hidden !important;
        }
        
        /* Hide blocks that only contain invisible content */
        .wp-block:not(:has(img)):not(:has(video)):not(:has(audio)):not(:has(iframe)):not(:has(canvas)):not(:has(svg)):not(:has(button)):not(:has(input)):not(:has(select)):not(:has(textarea)):not(:has(.cpp-pdf-wrapper)):not(:has(a)):not(:has(span)):not(:has(strong)):not(:has(em)):not(:has(p:not(:empty))) {
            display: none !important;
        }
        
        /* Force visibility for our PDF blocks */
        .cpp-pdf-wrapper,
        .cpp-pdf-wrapper * {
            visibility: visible !important;
            display: block !important;
            opacity: 1 !important;
        }
        
        .cpp-download-btn {
            display: inline-block !important;
        }
        </style>';
    }
}

// JavaScript to forcibly remove empty blocks and reorder content
add_action('wp_footer', 'cpp_aggressive_cleanup_and_reorder');
function cpp_aggressive_cleanup_and_reorder() {
    if (is_singular('post')) {
        echo '<script>
        jQuery(document).ready(function($) {
            // Function to check if element is truly empty
            function isElementEmpty($el) {
                var content = $el.html().trim();
                
                // Check for completely empty
                if (!content || content === "") return true;
                
                // Check for only whitespace and non-breaking spaces
                if (/^\s*(&nbsp;|\s)*$/.test(content)) return true;
                
                // Check for only HTML comments
                if (/^<!--[\s\S]*-->$/.test(content)) return true;
                
                // Check if it only contains empty child elements
                var hasContent = false;
                $el.find("*").each(function() {
                    if ($(this).text().trim() || $(this).find("img, video, audio, iframe, canvas, svg").length > 0) {
                        hasContent = true;
                        return false;
                    }
                });
                
                return !hasContent && !$el.text().trim();
            }
            
            // Aggressive removal of empty elements
            function removeEmptyElements() {
                // Remove ACF and other plugin empty blocks
                $("[class*=acf], [class*=block], [data-type], [data-block]").each(function() {
                    if (isElementEmpty($(this))) {
                        $(this).remove();
                    }
                });
                
                // Remove empty WordPress blocks
                $(".wp-block, .wp-block-group, .wp-block-columns, .elementor-widget-wrap, .elementor-element").each(function() {
                    if (isElementEmpty($(this))) {
                        $(this).remove();
                    }
                });
                
                // Remove any remaining empty divs, sections, etc.
                $("div, section, article, aside, header, footer, main").each(function() {
                    var $this = $(this);
                    
                    // Skip if it contains our PDF wrapper
                    if ($this.find(".cpp-pdf-wrapper").length > 0) return;
                    
                    if (isElementEmpty($this) && !$this.hasClass("cpp-pdf-wrapper")) {
                        $this.remove();
                    }
                });
            }
            
            // Run cleanup multiple times to catch nested empty elements
            removeEmptyElements();
            setTimeout(removeEmptyElements, 100);
            setTimeout(removeEmptyElements, 500);
            
            // Force PDF blocks to top of content
            function movePDFsToTop() {
                $(".cpp-pdf-wrapper").each(function() {
                    var $pdf = $(this);
                    var $container = $pdf.closest(".entry-content, .post-content, article, main, .elementor-widget-container, .wp-block-group");
                    
                    if ($container.length) {
                        // Detach and prepend to ensure it goes to the very top
                        $pdf.detach().prependTo($container);
                        
                        // Force styling
                        $pdf.css({
                            "position": "relative",
                            "z-index": "10000",
                            "margin": "0 auto 25px auto",
                            "max-width": "900px",
                            "width": "95%",
                            "display": "block",
                            "clear": "both",
                            "order": "-9999",
                            "visibility": "visible",
                            "opacity": "1"
                        });
                    }
                });
            }
            
            movePDFsToTop();
            
            // Fix Arabic text with numbers
            function fixArabicNumbers() {
                $(".cpp-pdf-title").each(function() {
                    var $title = $(this);
                    var text = $title.text();
                    
                    if (/[\u0600-\u06FF]/.test(text)) {
                        // Process text to fix number display
                        var fixedText = text.replace(/(\d+)/g, function(match) {
                            return match + "‎"; // Add LRM after numbers
                        });
                        
                        // Add RLM at beginning for Arabic text
                        fixedText = "‏" + fixedText;
                        
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
            }
            
            fixArabicNumbers();
            
            // Force container to use flexbox layout for proper ordering
            $(".entry-content, .post-content, article").each(function() {
                var $container = $(this);
                if ($container.find(".cpp-pdf-wrapper").length > 0) {
                    $container.css({
                        "display": "flex",
                        "flex-direction": "column"
                    });
                    
                    // Force PDF wrappers to order -9999
                    $container.find(".cpp-pdf-wrapper").css("order", "-9999");
                    
                    // Force everything else to order 1
                    $container.children().not(".cpp-pdf-wrapper").css("order", "1");
                }
            });
            
            // Run cleanup and reordering again after a delay to catch dynamically loaded content
            setTimeout(function() {
                removeEmptyElements();
                movePDFsToTop();
                fixArabicNumbers();
            }, 1000);
        });
        </script>';
    }
}

// Additional hook to catch early content rendering
add_action('template_redirect', 'cpp_early_content_filter');
function cpp_early_content_filter() {
    if (is_singular('post')) {
        ob_start('cpp_filter_entire_page_output');
    }
}

function cpp_filter_entire_page_output($buffer) {
    // Remove empty ACF blocks from the entire page output
    $patterns = array(
        // Remove empty ACF blocks
        '/<div[^>]*class="[^"]*acf[^"]*"[^>]*>\s*<\/div>/i',
        '/<div[^>]*data-type="acf[^"]*"[^>]*>\s*<\/div>/i',
        '/<div[^>]*data-block="acf[^"]*"[^>]*>\s*<\/div>/i',
        
        // Remove empty WordPress blocks
        '/<div[^>]*class="[^"]*wp-block[^"]*"[^>]*>\s*<\/div>/i',
        '/<div[^>]*class="[^"]*block-editor[^"]*"[^>]*>\s*<\/div>/i',
        
        // Remove empty divs that only contain whitespace/nbsp
        '/<div[^>]*>\s*(&nbsp;|\s)*\s*<\/div>/i',
    );
    
    foreach ($patterns as $pattern) {
        $buffer = preg_replace($pattern, '', $buffer);
    }
    
    return $buffer;
}
