jQuery(document).ready(function($) {
    // Only bind once to prevent duplicate handlers
    if (window.cpp_tracking_initialized) return;
    window.cpp_tracking_initialized = true;
    
    console.log('CPP: Tracking script initialized');
    
    $(document).on("click", ".cpp-track-download", function(e) {
        var $this = $(this);
        var postId = $this.data("post-id");
        var pdfUrl = $this.data("pdf-url");
        var pdfTitle = $this.data("pdf-title");
        
        console.log('CPP: Download clicked', {
            postId: postId,
            pdfUrl: pdfUrl,
            pdfTitle: pdfTitle
        });
        
        // Validate data exists
        if (!postId || !pdfUrl || !pdfTitle) {
            console.log('CPP: Missing tracking data');
            return true;
        }
        
        // Check if cpp_ajax is available
        if (typeof cpp_ajax === 'undefined') {
            console.log('CPP: cpp_ajax object not found');
            return true;
        }
        
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
            },
            success: function(response) {
                console.log('CPP: Download tracked successfully', response);
            },
            error: function(xhr, status, error) {
                console.log('CPP: Tracking failed', {
                    status: status,
                    error: error,
                    response: xhr.responseText
                });
            }
        });
        
        // Let the download proceed normally
        return true;
    });
});

// Function for disabled downloads
function showDownloadMessage() {
    alert("هذا الملف غير متاح للتحميل حاليا");
}
