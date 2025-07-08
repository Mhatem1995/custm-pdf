<?php
defined('ABSPATH') || exit;

// Add admin menu
add_action('admin_menu', 'cpp_add_analytics_menu');

function cpp_add_analytics_menu() {
    add_menu_page(
        'PDF Downloads Analytics',
        'PDF Analytics',
        'manage_options',
        'cpp-analytics',
        'cpp_analytics_page',
        'dashicons-chart-bar',
        25
    );
}

function cpp_analytics_page() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'cpp_pdf_downloads';
    
    // Get total downloads
    $total_downloads = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    
    // Get downloads by date (last 30 days)
    $downloads_by_date = $wpdb->get_results("
        SELECT DATE(download_date) as date, COUNT(*) as count 
        FROM $table_name 
        WHERE download_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(download_date) 
        ORDER BY date DESC
    ");
    
    // Get top downloaded PDFs
    $top_pdfs = $wpdb->get_results("
        SELECT pdf_title, COUNT(*) as download_count 
        FROM $table_name 
        GROUP BY pdf_title 
        ORDER BY download_count DESC 
        LIMIT 10
    ");
    
    // Get recent downloads
    $recent_downloads = $wpdb->get_results("
        SELECT pdf_title, download_date, user_ip 
        FROM $table_name 
        ORDER BY download_date DESC 
        LIMIT 20
    ");
    
    ?>
    <div class="wrap">
        <h1>PDF Downloads Analytics</h1>
        
        <!-- Summary Stats -->
        <div class="cpp-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
            <div class="cpp-stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
                <h3 style="margin: 0 0 10px 0; color: #0073aa;">Total Downloads</h3>
                <p style="font-size: 24px; font-weight: bold; margin: 0; color: #333;"><?php echo number_format($total_downloads); ?></p>
            </div>
            
            <div class="cpp-stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
                <h3 style="margin: 0 0 10px 0; color: #0073aa;">Today's Downloads</h3>
                <?php
                $today_downloads = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE DATE(download_date) = CURDATE()");
                ?>
                <p style="font-size: 24px; font-weight: bold; margin: 0; color: #333;"><?php echo number_format($today_downloads); ?></p>
            </div>
            
            <div class="cpp-stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
                <h3 style="margin: 0 0 10px 0; color: #0073aa;">This Week</h3>
                <?php
                $week_downloads = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE download_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
                ?>
                <p style="font-size: 24px; font-weight: bold; margin: 0; color: #333;"><?php echo number_format($week_downloads); ?></p>
            </div>
            
            <div class="cpp-stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
                <h3 style="margin: 0 0 10px 0; color: #0073aa;">This Month</h3>
                <?php
                $month_downloads = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE download_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
                ?>
                <p style="font-size: 24px; font-weight: bold; margin: 0; color: #333;"><?php echo number_format($month_downloads); ?></p>
            </div>
        </div>
        
        <!-- Downloads by Date Chart -->
        <div class="cpp-chart-container" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; margin: 20px 0;">
            <h3>Downloads Last 30 Days</h3>
            <canvas id="downloadsChart" width="400" height="200"></canvas>
        </div>
        
        <!-- Top Downloaded PDFs -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0;">
            <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
                <h3>Top Downloaded PDFs</h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>PDF Title</th>
                            <th>Downloads</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_pdfs as $pdf): ?>
                        <tr>
                            <td><?php echo esc_html($pdf->pdf_title); ?></td>
                            <td><strong><?php echo number_format($pdf->download_count); ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Recent Downloads -->
            <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
                <h3>Recent Downloads</h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>PDF Title</th>
                            <th>Date</th>
                            <th>IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_downloads as $download): ?>
                        <tr>
                            <td><?php echo esc_html($download->pdf_title); ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($download->download_date)); ?></td>
                            <td><?php echo esc_html($download->user_ip); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Chart.js for analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    const ctx = document.getElementById('downloadsChart').getContext('2d');
    const downloadsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [
                <?php 
                foreach ($downloads_by_date as $data) {
                    echo "'" . $data->date . "',";
                }
                ?>
            ],
            datasets: [{
                label: 'Downloads',
                data: [
                    <?php 
                    foreach ($downloads_by_date as $data) {
                        echo $data->count . ",";
                    }
                    ?>
                ],
                borderColor: '#0073aa',
                backgroundColor: 'rgba(0, 115, 170, 0.1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    </script>
    
    <?php
}