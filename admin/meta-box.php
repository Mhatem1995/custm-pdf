<?php
function cpp_add_pdf_meta_box() {
    add_meta_box(
        'cpp_pdf_meta',
        'Attach PDF Files',
        'cpp_pdf_meta_box_callback',
        'post',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'cpp_add_pdf_meta_box');

function cpp_pdf_meta_box_callback($post) {
    wp_nonce_field('cpp_save_pdf_meta', 'cpp_pdf_meta_nonce');
    $pdfs = get_post_meta($post->ID, '_cpp_pdf_files', true) ?: [];

    echo '<div id="cpp-pdf-fields">';
    foreach ($pdfs as $index => $pdf) {
        $title = esc_attr($pdf['title']);
        $url = esc_url($pdf['url']);
        
        // Auto-apply special colors for first PDF (index 0)
        if ($index === 0) {
            $bg_color = esc_attr($pdf['bg_color'] ?? '#e7e4e4'); // Lighter shade of #282e3f
            $border_color = esc_attr($pdf['border_color'] ?? '#282e3f');
        } else {
            $bg_color = esc_attr($pdf['bg_color'] ?? '#f9f9f9');
            $border_color = esc_attr($pdf['border_color'] ?? '#ccc');
        }
        
        $button_bg = esc_attr($pdf['button_bg'] ?? '#0073aa');
        $button_text = esc_attr($pdf['button_text'] ?? '#ffffff');
        $download_disabled = isset($pdf['download_disabled']) ? $pdf['download_disabled'] : false;

        echo '<div class="cpp-pdf-row">';
        echo "<input type='text' class='cpp-title' name='cpp_pdf_files[{$index}][title]' placeholder='PDF Title' value='{$title}' />";
        echo "<input type='hidden' class='cpp-url' name='cpp_pdf_files[{$index}][url]' value='{$url}' />";
        echo "<button type='button' class='button cpp-upload-button'>Upload PDF</button>";
        echo '<span class="cpp-file-name">' . basename($url) . '</span>';
        echo '<button type="button" class="button-link cpp-remove-pdf" style="color:red; margin-left:10px;">Remove</button>';

        echo '<div style="margin-top:10px;">';
        echo "<input type='checkbox' name='cpp_pdf_files[{$index}][download_disabled]' value='1' " . checked($download_disabled, true, false) . " />";
        echo ' <label>Disable Download (Show message instead)</label>';
        echo '</div>';

        echo '<fieldset style="margin-top:10px; padding:10px; border:1px solid #ddd;">';
        echo '<legend><strong>Box Styles</strong></legend>';
        if ($index === 0) {
            echo '<p style="color: #0073aa; font-weight: bold; margin-bottom: 10px;">⭐ First PDF - Special Styling Applied</p>';
        }
        echo "Box Background: <input type='color' name='cpp_pdf_files[{$index}][bg_color]' value='{$bg_color}' /><br>";
        echo "Border Color: <input type='color' name='cpp_pdf_files[{$index}][border_color]' value='{$border_color}' /><br>";
        echo "Button Background: <input type='color' name='cpp_pdf_files[{$index}][button_bg]' value='{$button_bg}' /><br>";
        echo "Button Text Color: <input type='color' name='cpp_pdf_files[{$index}][button_text]' value='{$button_text}' />";
        echo '</fieldset>';

        echo '</div>';
    }
    echo '</div>';
    echo '<button type="button" id="add-pdf-field" class="button">Add PDF</button>';
    ?>

    <script>
    jQuery(document).ready(function($) {
        let mediaUploader;

        function createPDFRow(index) {
            // Auto-apply special colors for first PDF (index 0)
            let bgColor = '#f9f9f9';
            let borderColor = '#ccc';
            let specialNote = '';
            
            if (index === 0) {
                bgColor = '#e7e4e4'; // Lighter shade of #282e3f
                borderColor = '#282e3f';
                specialNote = '<p style="color: #0073aa; font-weight: bold; margin-bottom: 10px;">⭐ First PDF - Special Styling Applied</p>';
            }
            
            return `
                <div class="cpp-pdf-row">
                    <input type="text" class="cpp-title" name="cpp_pdf_files[${index}][title]" placeholder="PDF Title" />
                    <input type="hidden" class="cpp-url" name="cpp_pdf_files[${index}][url]" />
                    <button type="button" class="button cpp-upload-button">Upload PDF</button>
                    <span class="cpp-file-name"></span>
                    <button type="button" class="button-link cpp-remove-pdf" style="color:red; margin-left:10px;">Remove</button>

                    <div style="margin-top:10px;">
                        <input type="checkbox" name="cpp_pdf_files[${index}][download_disabled]" value="1" />
                        <label>Disable Download (Show message instead)</label>
                    </div>

                    <fieldset style="margin-top:10px; padding:10px; border:1px solid #ddd;">
                        <legend><strong>Box Styles</strong></legend>
                        ${specialNote}
                        Box Background: <input type="color" name="cpp_pdf_files[${index}][bg_color]" value="${bgColor}" /><br>
                        Border Color: <input type="color" name="cpp_pdf_files[${index}][border_color]" value="${borderColor}" /><br>
                        Button Background: <input type="color" name="cpp_pdf_files[${index}][button_bg]" value="#0073aa" /><br>
                        Button Text Color: <input type="color" name="cpp_pdf_files[${index}][button_text]" value="#ffffff" />
                    </fieldset>
                </div>`;
        }

        $('#add-pdf-field').on('click', function() {
            const index = $('.cpp-pdf-row').length;
            $('#cpp-pdf-fields').append(createPDFRow(index));
        });

        // Function to update special styling indicators when rows are reordered
        function updateSpecialStyling() {
            $('.cpp-pdf-row').each(function(index) {
                const $row = $(this);
                const $fieldset = $row.find('fieldset');
                const $legend = $fieldset.find('legend');
                const $bgInput = $row.find('input[name*="[bg_color]"]');
                const $borderInput = $row.find('input[name*="[border_color]"]');
                
                // Remove existing special note
                $fieldset.find('p').remove();
                
                if (index === 0) {
                    // Add special note and update colors if they're still default
                    $legend.after('<p style="color: #0073aa; font-weight: bold; margin-bottom: 10px;">⭐ First PDF - Special Styling Applied</p>');
                    
                    // Only update colors if they're still default values
                    if ($bgInput.val() === '#f9f9f9') {
                        $bgInput.val('#e7e4e4');
                    }
                    if ($borderInput.val() === '#ccc') {
                        $borderInput.val('#282e3f');
                    }
                } else {
                    // Revert to default colors if they're still special colors
                    if ($bgInput.val() === '#e7e4e4') {
                        $bgInput.val('#f9f9f9');
                    }
                    if ($borderInput.val() === '#282e3f') {
                        $borderInput.val('#ccc');
                    }
                }
                
                // Update input names to reflect new index
                $row.find('input, button').each(function() {
                    const $input = $(this);
                    const name = $input.attr('name');
                    if (name && name.includes('[')) {
                        const newName = name.replace(/\[\d+\]/, `[${index}]`);
                        $input.attr('name', newName);
                    }
                });
            });
        }

        $(document).on('click', '.cpp-upload-button', function(e) {
            e.preventDefault();
            const button = $(this);
            const row = button.closest('.cpp-pdf-row');
            const input = row.find('.cpp-url');
            const fileNameDisplay = row.find('.cpp-file-name');

            // Create new media uploader instance each time
            mediaUploader = wp.media({
                title: 'Select PDF File',
                button: { text: 'Use this PDF' },
                library: { 
                    type: ['application/pdf'],
                    uploadedTo: null // Allow all PDFs, not just those uploaded to this post
                },
                multiple: false
            });

            mediaUploader.on('select', function() {
                const attachment = mediaUploader.state().get('selection').first().toJSON();
                
                // Validate that it's actually a PDF
                if (attachment.mime !== 'application/pdf' && attachment.subtype !== 'pdf') {
                    alert('Please select a valid PDF file only.');
                    return;
                }
                
                // Additional validation for file extension
                const fileName = attachment.filename || attachment.url.split('/').pop();
                const fileExtension = fileName.split('.').pop().toLowerCase();
                
                if (fileExtension !== 'pdf') {
                    alert('Please select a file with .pdf extension only.');
                    return;
                }
                
                // Validate file size (optional - adjust as needed)
                const maxSizeInMB = 50; // 50MB limit
                const fileSizeInMB = attachment.filesizeInBytes / (1024 * 1024);
                
                if (fileSizeInMB > maxSizeInMB) {
                    alert(`File size is too large. Maximum allowed size is ${maxSizeInMB}MB.`);
                    return;
                }
                
                console.log('Selected PDF:', {
                    filename: fileName,
                    url: attachment.url,
                    mime: attachment.mime,
                    size: fileSizeInMB.toFixed(2) + 'MB'
                });
                
                input.val(attachment.url);
                fileNameDisplay.text(fileName);
                
                // Auto-fill title if empty
                const titleInput = row.find('.cpp-title');
                if (!titleInput.val()) {
                    const titleFromFilename = fileName.replace('.pdf', '').replace(/[-_]/g, ' ');
                    titleInput.val(titleFromFilename);
                }
            });

            mediaUploader.on('open', function() {
                // Filter to show only PDFs
                const selection = mediaUploader.state().get('selection');
                const library = mediaUploader.state().get('library');
                
                // Clear any existing selection
                selection.reset();
                
                // Add filter for PDFs only
                library.props.set({
                    type: 'application/pdf',
                    uploadedTo: null
                });
            });

            mediaUploader.open();
        });

        $(document).on('click', '.cpp-remove-pdf', function() {
            if (confirm('هل انت متاكد من حذف ال pdf ?')) {
                $(this).closest('.cpp-pdf-row').remove();
                // Update special styling after removal
                updateSpecialStyling();
            }
        });
    });
    </script>

    <style>
        .cpp-pdf-row {
            margin-bottom: 15px;
            padding: 10px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .cpp-pdf-row input.cpp-title {
            width: 300px;
        }
        .cpp-file-name {
            font-style: italic;
            color: #555;
            margin-left: 10px;
        }
        .cpp-file-name:before {
            content: "Selected: ";
            color: #0073aa;
            font-weight: bold;
        }
        .cpp-remove-pdf {
            background: none;
            border: none;
            color: red;
            cursor: pointer;
            font-weight: bold;
        }
        .cpp-remove-pdf:hover {
            text-decoration: underline;
        }
        .cpp-upload-button {
            margin-right: 10px;
        }
    </style>

    <?php
}

function cpp_save_pdf_meta($post_id) {
    if (!isset($_POST['cpp_pdf_meta_nonce']) || !wp_verify_nonce($_POST['cpp_pdf_meta_nonce'], 'cpp_save_pdf_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $pdfs = $_POST['cpp_pdf_files'] ?? [];
    
    // Clean and validate PDF data before saving
    $clean_pdfs = [];
    foreach ($pdfs as $index => $pdf) {
        // Skip completely empty entries (no title AND no URL)
        if (empty($pdf['title']) && empty($pdf['url'])) continue;
        
        // Validate URL is actually a PDF only if URL is provided
        if (!empty($pdf['url'])) {
            $file_extension = pathinfo($pdf['url'], PATHINFO_EXTENSION);
            if (strtolower($file_extension) !== 'pdf') continue;
        }
        
        // Auto-apply special colors for first PDF during save
        if ($index === 0) {
            $default_bg = '#e7e4e4'; // Lighter shade of #282e3f
            $default_border = '#282e3f';
        } else {
            $default_bg = '#f9f9f9';
            $default_border = '#ccc';
        }
        
        // Sanitize data
        $clean_pdf = [
            'title' => sanitize_text_field($pdf['title']),
            'url' => !empty($pdf['url']) ? esc_url_raw($pdf['url']) : '', // Allow empty URL
            'bg_color' => sanitize_hex_color($pdf['bg_color'] ?? $default_bg),
            'border_color' => sanitize_hex_color($pdf['border_color'] ?? $default_border),
            'button_bg' => sanitize_hex_color($pdf['button_bg'] ?? '#0073aa'),
            'button_text' => sanitize_hex_color($pdf['button_text'] ?? '#ffffff'),
            'download_disabled' => isset($pdf['download_disabled']) ? true : false
        ];
        
        $clean_pdfs[] = $clean_pdf;
    }
    
    update_post_meta($post_id, '_cpp_pdf_files', $clean_pdfs);
}
add_action('save_post', 'cpp_save_pdf_meta');

// Add additional MIME type support for PDFs
add_filter('upload_mimes', 'cpp_add_pdf_mime_types');
function cpp_add_pdf_mime_types($mimes) {
    $mimes['pdf'] = 'application/pdf';
    return $mimes;
}

// Add file validation during upload
add_filter('wp_handle_upload_prefilter', 'cpp_validate_pdf_upload');
function cpp_validate_pdf_upload($file) {
    if (isset($_POST['action']) && $_POST['action'] === 'upload-attachment') {
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        
        if (strtolower($file_extension) === 'pdf') {
            // Additional validation for PDF files
            $file_info = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($file_info, $file['tmp_name']);
            finfo_close($file_info);
            
            if ($mime_type !== 'application/pdf') {
                $file['error'] = 'This file is not a valid PDF. Please upload a proper PDF file.';
            }
        }
    }
    
    return $file;
}
