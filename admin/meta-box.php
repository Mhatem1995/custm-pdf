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
        $bg_color = esc_attr($pdf['bg_color'] ?? '#f9f9f9');
        $border_color = esc_attr($pdf['border_color'] ?? '#ccc');
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
                        Box Background: <input type="color" name="cpp_pdf_files[${index}][bg_color]" value="#f9f9f9" /><br>
                        Border Color: <input type="color" name="cpp_pdf_files[${index}][border_color]" value="#ccc" /><br>
                        Button Background: <input type="color" name="cpp_pdf_files[${index}][button_bg]" value="#0073aa" /><br>
                        Button Text Color: <input type="color" name="cpp_pdf_files[${index}][button_text]" value="#ffffff" />
                    </fieldset>
                </div>`;
        }

        $('#add-pdf-field').on('click', function() {
            const index = $('.cpp-pdf-row').length;
            $('#cpp-pdf-fields').append(createPDFRow(index));
        });

        $(document).on('click', '.cpp-upload-button', function(e) {
            e.preventDefault();
            const button = $(this);
            const row = button.closest('.cpp-pdf-row');
            const input = row.find('.cpp-url');
            const fileNameDisplay = row.find('.cpp-file-name');

            if (mediaUploader) {
                mediaUploader.open();
                return;
            }

            mediaUploader = wp.media({
                title: 'Select PDF',
                button: { text: 'Use this PDF' },
                library: { type: 'application/pdf' },
                multiple: false
            });

            mediaUploader.on('select', function() {
                const attachment = mediaUploader.state().get('selection').first().toJSON();
                input.val(attachment.url);
                fileNameDisplay.text(attachment.filename);
            });

            mediaUploader.open();
        });

        $(document).on('click', '.cpp-remove-pdf', function() {
            if (confirm('هل انت متاكد من حذف ال pdf ?')) {
                $(this).closest('.cpp-pdf-row').remove();
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
    </style>

    <?php
}

function cpp_save_pdf_meta($post_id) {
    if (!isset($_POST['cpp_pdf_meta_nonce']) || !wp_verify_nonce($_POST['cpp_pdf_meta_nonce'], 'cpp_save_pdf_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $pdfs = $_POST['cpp_pdf_files'] ?? [];
    update_post_meta($post_id, '_cpp_pdf_files', array_values($pdfs));
}
add_action('save_post', 'cpp_save_pdf_meta');