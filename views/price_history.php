<?php
// views/price_history.php

$color_palette = [
    'watsons' => '#5D3EBC',
    'guardian' => '#27AE60',
    'caring pharmacy' => '#EB5757'
];

$store_raw_prices = [];
foreach ($stores as $store) {
    $last_known_price = null;
    foreach ($ordered_labels as $lbl_arr) {
        // Search matching entry in raw history
        $current_date_price = null;
        // Search by looking at raw date matching day and month
        foreach ($raw_history_rows as $row) {
            if ($row['product_store'] === $store && $row['day_label'] === $lbl_arr[0] && $row['month_label'] === $lbl_arr[1]) {
                $current_date_price = (float)$row['product_price'];
            }
        }
        if ($current_date_price !== null) {
            $last_known_price = $current_date_price;
        }
        $store_raw_prices[$store][] = $last_known_price;
    }
}

$final_json_datasets = [];
foreach ($stores as $store) {
    $color = isset($color_palette[$store]) ? $color_palette[$store] : '#9D8FE1';
    $final_json_datasets[] = [
        'label' => ucwords($store),
        'data' => $store_raw_prices[$store],
        'borderColor' => $color,
        'backgroundColor' => $color . '10',
        'tension' => 0.15,
        'borderWidth' => 3,
        'pointRadius' => 3,
        'hoverRadius' => 6
    ];
}

if (!function_exists('format_product_title')) {
    function format_product_title(string $name) {
        $name = strtolower($name); $name = ucwords($name);
        $name = preg_replace('/\b(\d+)\s*Ml\b/i', '$1 ML', $name);
        $name = preg_replace('/\b(\d+)\s*G\b/i', '$1 G', $name);
        return $name;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SKIN+ | Historical Price Insights</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --primary-color: #5D3EBC; --bg-color: #FAFAFC; --card-bg: #FFFFFF; --text-dark: #2D2543; --text-muted: #756F86; --border-color: #E6E4ED; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg-color); color: var(--text-dark); }
        .navbar { display: flex; align-items: center; padding: 1.5rem 8%; background: var(--card-bg); border-bottom: 1px solid var(--border-color); }
        .back-nav { display: flex; align-items: center; gap: 1rem; text-decoration: none; color: var(--text-dark); }
        .logo-area h1 { font-size: 1.8rem; font-weight: 700; color: var(--primary-color); }
        .main-container { max-width: 1100px; margin: 3rem auto; padding: 0 2rem; }
        .page-header { text-align: center; margin-bottom: 2.5rem; }
        .page-title { font-size: 2.2rem; font-weight: 700; margin-bottom: 0.2rem; }
        .page-subtitle { color: var(--text-muted); font-size: 1rem; }
        
        .flex-row { display: flex; gap: 2rem; margin-bottom: 3rem; flex-wrap: wrap; align-items: stretch; }
        .cards-column { flex: 2; display: flex; flex-direction: column; gap: 1rem; min-width: 500px; }
        
        /* Redesigned Premium Matrix Row */
        .metric-row-card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 16px; padding: 1.25rem 2rem; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 10px rgba(0,0,0,0.01); }
        .store-brand-title { font-size: 1.1rem; font-weight: 700; text-transform: capitalize; width: 160px; }
        .price-sub-grid { display: flex; gap: 2.5rem; flex: 1; justify-content: flex-end; text-align: right; }
        .price-block-node { display: flex; flex-direction: column; }
        .price-label-text { font-size: 0.78rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
        .price-value { font-size: 1.25rem; font-weight: 700; margin-top: 2px; }
        
        .insight-card { flex: 1.1; background: linear-gradient(135deg, #F9F8FF 0%, #F3EFFF 100%); border: 1px solid #DED6FF; padding: 1.8rem; border-radius: 20px; box-shadow: 0 4px 15px rgba(93,62,188,0.03); display: flex; gap: 1.2rem; align-items: center; text-align: left; min-width: 320px; }
        .insight-icon { background: #5D3EBC; color: #FFFFFF; width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0; }
        .insight-text h3 { font-size: 1.1rem; font-weight: 700; color: #2D2543; margin-bottom: 0.3rem; }
        .insight-text p { font-size: 0.95rem; color: #524A65; line-height: 1.45; }
        .insight-highlight { color: #5D3EBC; font-weight: 700; }

        .chart-container { background: var(--card-bg); border: 1px solid var(--border-color); padding: 2.5rem; border-radius: 24px; box-shadow: 0 6px 20px rgba(0,0,0,0.01); }
        .chart-header { text-align: left; font-size: 1.1rem; font-weight: 700; margin-bottom: 1.5rem; }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="javascript:history.back()" class="back-nav">
            <i class="fa-solid fa-chevron-left" style="font-size: 1.2rem;"></i>
            <div class="logo-area"><h1>SKIN+</h1></div>
        </a>
    </nav>

    <main class="main-container">
        <div class="page-header">
            <h2 class="page-title"><?php echo htmlspecialchars(format_product_title($product_info['product_name'])); ?></h2>
            <p class="page-subtitle">Historical Price Tracking & Analysis Workspace</p>
        </div>

        <div class="flex-row">
            <div class="cards-column">
                <?php foreach ($store_analysis_metrics as $s_name => $metrics): 
                    $brand_color = isset($color_palette[$s_name]) ? $color_palette[$s_name] : '#9D8FE1';
                ?>
                    <div class="metric-row-card" style="border-left: 5px solid <?php echo $brand_color; ?>;">
                        <div class="store-brand-title" style="color: <?php echo $brand_color; ?>;">
                            <?php echo htmlspecialchars($s_name); ?>
                        </div>
                        <div class="price-sub-grid">
                            <div class="price-block-node">
                                <span class="price-label-text">Current Price</span>
                                <span class="price-value" style="color: #2D2543;">RM <?php echo number_format($metrics['current'], 2); ?></span>
                            </div>
                            <div class="price-block-node">
                                <span class="price-label-text">Lowest Ever</span>
                                <span class="price-value" style="color: #27AE60;">RM <?php echo number_format($metrics['lowest'], 2); ?></span>
                            </div>
                            <div class="price-block-node">
                                <span class="price-label-text">Highest Ever</span>
                                <span class="price-value" style="color: #EB5757;">RM <?php echo number_format($metrics['highest'], 2); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="insight-card">
                <div class="insight-icon"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
                <div class="insight-text">
                    <h3>All-Time Best Pricing Record</h3>
                    <p>Historically, the absolute lowest recorded price dropped to <span class="insight-highlight">RM <?php echo number_format($absolute_best_price, 2); ?></span> at <span class="insight-highlight" style="text-transform: capitalize;"><?php echo htmlspecialchars($absolute_best_store); ?></span> tracking index logs. This record bottom deal occurred on <span class="insight-highlight"><?php echo $absolute_best_date; ?></span>.</p>
                </div>
            </div>
        </div>

        <div class="chart-container">
            <div class="chart-header">Historical Price Trend Timeline Tracking</div>
            <canvas id="forecastChart" style="max-height: 440px; width: 100%;"></canvas>
        </div>
    </main>

    <script>
    const ctx = document.getElementById('forecastChart').getContext('2d');
    const labelsTimeline = <?php echo json_encode($ordered_labels); ?>;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labelsTimeline,
            datasets: <?php echo json_encode($final_json_datasets); ?>
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { font: { family: 'Inter', size: 12, weight: '600' }, padding: 25 } },
                tooltip: {
                    padding: 12,
                    backgroundColor: '#2D2543',
                    callbacks: {
                        title: function(context) {
                            const labelData = labelsTimeline[context[0].dataIndex];
                            return labelData[0] + " " + labelData[1];
                        },
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) label += ': ';
                            if (context.parsed.y !== null) label += 'RM ' + context.parsed.y.toFixed(2);
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: { 
                    grid: { color: '#F4F3F7' }, 
                    ticks: { 
                        font: { family: 'Inter', size: 11, weight: '500' }, 
                        color: '#756F86',
                        padding: 10,
                        callback: function(value) { return 'RM ' + Number(value).toFixed(2); } 
                    } 
                },
                x: { 
                    grid: { display: true, color: '#F4F3F7' }, 
                    ticks: { 
                        font: { family: 'Inter', size: 9, weight: '700' },
                        color: '#756F86',
                        autoSkip: false,
                        maxRotation: 0,
                        callback: function(val, index) {
                            const lbl = labelsTimeline[index];
                            if (!lbl) return '';
                            if (index === 0 || labelsTimeline[index - 1][1] !== lbl[1]) {
                                return [lbl[0], '── ' + lbl[1] + ' ──'];
                            }
                            return lbl[0];
                        }
                    } 
                }
            }
        }
    });
    </script>
</body>
</html>
