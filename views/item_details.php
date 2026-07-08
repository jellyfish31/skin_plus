<?php
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
    <title>SKIN+ | Product Breakdown</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary-color: #5D3EBC; --primary-hover: #4A2E9F; --bg-color: #FAFAFC; --card-bg: #FFFFFF; --text-dark: #2D2543; --text-muted: #756F86; --border-color: #E6E4ED; --cheap-color: #27AE60; --normal-color: #F39C12; --expensive-color: #EB5757; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg-color); color: var(--text-dark); line-height: 1.6; }
        .navbar { display: flex; align-items: center; padding: 1.5rem 8%; background: var(--card-bg); border-bottom: 1px solid var(--border-color); }
        .back-nav { display: flex; align-items: center; gap: 1rem; text-decoration: none; color: var(--text-dark); }
        .logo-area h1 { font-size: 1.8rem; font-weight: 700; color: var(--primary-color); }
        
        /* Master Layout Structure */
        .detail-header-section { display: flex; max-width: 1000px; margin: 4rem auto 2rem auto; background: var(--card-bg); border-radius: 24px; padding: 2.5rem; border: 1px solid var(--border-color); gap: 3rem; align-items: flex-start; }
        
        /* Left Column (Image & Under-Buttons) */
        .left-media-column { display: flex; flex-direction: column; align-items: center; width: 280px; flex-shrink: 0; gap: 1.2rem; }
        .img-box { width: 280px; height: 280px; background: #FFFFFF; border-radius: 16px; display: flex; align-items: center; justify-content: center; padding: 1rem; border: 1px solid #F0EFF4; overflow: hidden; }
        .img-box img { max-height: 100%; max-width: 100%; object-fit: contain; }
        
        /* Action Buttons Block */
        .action-button-group { width: 100%; display: flex; flex-direction: column; gap: 0.75rem; }
        .forecast-btn { width: 100%; background-color: var(--primary-color); color: white; border: none; padding: 0.8rem 1.5rem; border-radius: 25px; font-weight: 600; font-size: 0.95rem; cursor: pointer; display: inline-block; text-decoration: none; text-align: center; transition: all 0.2s ease; }
        .forecast-btn:hover { background-color: var(--primary-hover); }
        .forecast-btn.secondary { background-color: #FAFAFC; color: var(--primary-color); border: 2px solid var(--primary-color); }
        .forecast-btn.secondary:hover { background-color: #F5F2FF; }

        /* Right Column (Meta Title & AI Board) */
        .product-meta-details { flex: 1; }
        .product-meta-details h2 { font-size: 2.2rem; font-weight: 700; margin-bottom: 1.2rem; line-height: 1.2; text-align: left; }
        
        /* AI breakdown items */
        .ai-breakdown-card { background: linear-gradient(135deg, #F9F8FF 0%, #F5F1FF 100%); border: 1px solid #DFD7FF; border-radius: 16px; padding: 1.5rem; display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; text-align: left; }
        .ai-grid-item { display: flex; gap: 0.8rem; align-items: flex-start; }
        .ai-icon { background: #5D3EBC; color: #FFFFFF; width: 34px; height: 34px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 0.95rem; flex-shrink: 0; }
        .ai-info h4 { font-size: 0.9rem; font-weight: 700; color: #4A358A; margin-bottom: 0.25rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .ai-info .ai-content-text { font-size: 0.92rem; color: #443B57; font-weight: 500; line-height: 1.45; }
        
        /* Skeleton Pulse Loader */
        .skeleton-text { height: 14px; background: #E2DBF7; border-radius: 4px; margin-top: 6px; animation: pulseLoading 1.4s infinite ease-in-out; }
        @keyframes pulseLoading { 0% { opacity: 0.4; } 50% { opacity: 1; } 100% { opacity: 0.4; } }

        .comparison-wrapper { max-width: 700px; margin: 0 auto 6rem auto; background: var(--card-bg); border-radius: 24px; padding: 3rem 2.5rem; border: 1px solid var(--border-color); }
        .comparison-wrapper h3 { text-align: center; font-size: 2.2rem; font-weight: 700; margin-bottom: 2.5rem; }
        .price-list-stack { display: flex; flex-direction: column; gap: 1.2rem; }
        .pill-row { display: flex; align-items: center; justify-content: space-between; border: 1.5px solid #9D8FE1; padding: 1rem 2rem; border-radius: 40px; background: #FFFFFF; }
        .pill-left { display: flex; align-items: center; gap: 1.5rem; }
        .rank-circle { width: 36px; height: 36px; background: #EAE8F0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; }
        .store-title-block { display: flex; flex-direction: column; }
        .store-display-name { font-size: 1.25rem; font-weight: 600; }
        .status-tag { font-size: 0.78rem; font-weight: 700; text-transform: capitalize; }
        .status-tag.cheap { color: var(--cheap-color); }
        .status-tag.normal { color: var(--normal-color); }
        .status-tag.expensive { color: var(--expensive-color); }
        .pill-right-price { font-size: 1.4rem; font-weight: 700; }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="javascript:history.back()" class="back-nav">
            <i class="fa-solid fa-chevron-left" style="color: var(--text-dark);"></i>
            <div class="logo-area"><h1>SKIN+</h1></div>
        </a>
    </nav>
    
    <section class="detail-header-section">
        <div class="left-media-column">
            <div class="img-box">
                <img src="<?php echo (!empty($product_info['product_image']) && strpos($product_info['product_image'], 'placeholder') === false) ? htmlspecialchars($product_info['product_image']) : 'no_image.png'; ?>" alt="Product">
            </div>
            <div class="action-button-group">
                <a href="price_history.php?signature=<?php echo urlencode($current_signature); ?>&name=<?php echo urlencode($product_info['product_name']); ?>" class="forecast-btn">
                    <i class="fa-solid fa-chart-line"></i> View Price History
                </a>
                <button class="forecast-btn secondary" id="openMoreInfoBtn">
                    <i class="fa-solid fa-circle-info"></i> More Info
                </button>
            </div>
        </div>

        <div class="product-meta-details">
            <h2><?php echo htmlspecialchars(format_product_title($product_info['product_name'])); ?></h2>
            
            <div class="ai-breakdown-card">
                <div class="ai-grid-item">
                    <div class="ai-icon"><i class="fa-solid fa-face-smile"></i></div>
                    <div class="ai-info">
                        <h4>Target Skin Type</h4>
                        <div id="aiSkin" class="ai-content-text"><div class="skeleton-text" style="width:120px;"></div></div>
                    </div>
                </div>
                <div class="ai-grid-item">
                    <div class="ai-icon"><i class="fa-solid fa-clock"></i></div>
                    <div class="ai-info">
                        <h4>When To Apply</h4>
                        <div id="aiWhen" class="ai-content-text"><div class="skeleton-text" style="width:140px;"></div></div>
                    </div>
                </div>
                <div class="ai-grid-item" style="grid-column: span 2;">
                    <div class="ai-icon"><i class="fa-solid fa-sparkles"></i></div>
                    <div class="ai-info">
                        <h4>Key Benefits</h4>
                        <div id="aiBenefits" class="ai-content-text"><div class="skeleton-text" style="width:100%;"></div></div>
                    </div>
                </div>
                <div class="ai-grid-item" style="grid-column: span 2;">
                    <div class="ai-icon"><i class="fa-solid fa-flask"></i></div>
                    <div class="ai-info">
                        <h4>Star Ingredients</h4>
                        <div id="aiIngredients" class="ai-content-text"><div class="skeleton-text" style="width:100%;"></div></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="comparison-wrapper">
        <h3>Price Comparison</h3>
        <div class="price-list-stack">
            <?php if ($total_stores > 0): ?>
                <?php foreach ($store_prices as $index => $item): 
                    $current_price = $item['product_price'];
                    $status = ($current_price < ($average_price * 0.95)) ? "cheap" : (($current_price > ($average_price * 1.05)) ? "expensive" : "normal");
                ?>
                    <div class="pill-row">
                        <div class="pill-left">
                            <div class="rank-circle">#<?php echo ($index + 1); ?></div>
                            <div class="store-title-block">
                                <span class="store-display-name"><?php echo htmlspecialchars($item['product_store']); ?></span>
                                <span class="status-tag <?php echo $status; ?>"><?php echo $status; ?></span>
                            </div>
                        </div>
                        <div class="pill-right-price">RM <?php echo number_format($current_price, 2); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align:center; color:var(--text-muted); padding: 1rem 0;">No matching store details indexed for recent batches.</p>
            <?php endif; ?>
        </div>
    </section>

    <div id="moreInfoModal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background:rgba(45,37,67,0.4); backdrop-filter:blur(4px); align-items:center; justify-content:center;">
        <div style="background:#FFFFFF; padding:2.5rem; border-radius:24px; max-width:600px; width:90%; border:1px solid #E6E4ED; box-shadow:0 10px 30px rgba(0,0,0,0.05); position:relative; text-align:left;">
            <span id="closeMoreInfoModal" style="position:absolute; right:25px; top:20px; font-size:1.5rem; cursor:pointer; color:var(--text-muted);">&times;</span>
            <h3 style="font-size:1.6rem; font-weight:700; margin-bottom:1.5rem; color:var(--primary-color);"><i class="fa-solid fa-wand-magic-sparkles"></i> Deep Science Breakdown</h3>
            <div style="max-height: 400px; overflow-y: auto; padding-right: 10px; display:flex; flex-direction:column; gap:1.2rem; scrollbar-width: thin;">
                <div>
                    <h4 style="font-size:0.95rem; color:#4A358A; text-transform:uppercase; font-weight:700;"><i class="fa-solid fa-bullseye"></i> Targeted Concerns</h4>
                    <p id="detailConcerns" style="font-size:0.95rem; margin-top:3px; color:#443B57;"></p>
                </div>
                <div>
                    <h4 style="font-size:0.95rem; color:#4A358A; text-transform:uppercase; font-weight:700;"><i class="fa-solid fa-droplet"></i> Texture & Finish</h4>
                    <p id="detailTexture" style="font-size:0.95rem; margin-top:3px; color:#443B57;"></p>
                </div>
                <div>
                    <h4 style="font-size:0.95rem; color:#4A358A; text-transform:uppercase; font-weight:700;"><i class="fa-solid fa-layer-group"></i> Routine Compatibility</h4>
                    <p id="detailRoutine" style="font-size:0.95rem; margin-top:3px; color:#443B57;"></p>
                </div>
                <div>
                    <h4 style="font-size:0.95rem; color:#4A358A; text-transform:uppercase; font-weight:700;"><i class="fa-solid fa-triangle-exclamation"></i> Safety Precautions</h4>
                    <p id="detailPrecautions" style="font-size:0.95rem; margin-top:3px; color:#443B57;"></p>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const productName = <?php echo json_encode($product_info['product_name']); ?>;
        const productSig = <?php echo json_encode($current_signature); ?>;
        
        // 1. Fetch Primary Summary Breakdown Data Matrix
        fetch('get_ai_description.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ 'name': productName, 'signature': productSig })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('aiSkin').innerText = data.skin_type;
                document.getElementById('aiWhen').innerText = data.apply_time;
                document.getElementById('aiBenefits').innerText = data.benefits;
                document.getElementById('aiIngredients').innerText = data.ingredients;
            } else {
                fallbackSummary();
            }
        })
        .catch(() => fallbackSummary());

        function fallbackSummary() {
            document.getElementById('aiSkin').innerText = "All Skin Types ✨";
            document.getElementById('aiWhen').innerText = "Morning & Evening Routine ☀️🌙";
            document.getElementById('aiBenefits').innerText = "Deeply hydrates and restores skin vitality.";
            document.getElementById('aiIngredients').innerText = "Hyaluronic Acid, Vitamin E";
        }

        // 2. Fetch Deep Science Breakdown Modal Data on Click Activity
        const modal = document.getElementById('moreInfoModal');
        const openBtn = document.getElementById('openMoreInfoBtn');
        const closeBtn = document.getElementById('closeMoreInfoModal');
        let detailedDataCached = null;

        openBtn.addEventListener("click", function() {
            modal.style.display = "flex";
            
            document.getElementById('detailConcerns').innerText = "Consulting Gemini...";

            fetch('get_ai_detailed_breakdown.php?t=' + Date.now(), {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ 'name': productName, 'visual_signature': productSig }) 
            })
            .then(res => res.json())
            .then(data => {
                detailedDataCached = data;
                renderDetailedModal(data);
            });
        });

        function renderDetailedModal(data) {
            document.getElementById('detailConcerns').innerHTML = data.skin_concerns;
            document.getElementById('detailTexture').innerText = data.texture_feel;
            document.getElementById('detailRoutine').innerText = data.routine_layering;
            document.getElementById('detailPrecautions').innerText = data.precautions;
        }

        closeBtn.addEventListener("click", function() { modal.style.display = "none"; });
        window.addEventListener("click", function(e) { if (e.target === modal) modal.style.display = "none"; });
    });
    </script>
</body>
</html>
