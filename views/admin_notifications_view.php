<?php
/** @var int $pending_count */
/** @var array $pending_discoveries */
/** @var string $success_msg */

// Safeguard: Redirect to the controller in the root directory if accessed directly
if (!isset($pending_count) || !isset($pending_discoveries)) {
    header("Location: ../admin_notifications.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SKIN+ | Unassigned Discoveries Desk</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #FAFAFC; color: #2D2543; padding: 3rem 0; }
        .container { max-width: 1100px; margin: 0 auto; padding: 0 2rem; }
        .header-row { display: flex; justify-content: flex-start; align-items: center; gap: 1.5rem; margin-top: 4rem; margin-bottom: 2rem; }
        
        .discovery-group { margin-bottom: 2.5rem; display: flex; flex-direction: column; gap: 0.4rem; }
        
        /* 🖼️ HIGH-VISIBILITY PRODUCT CARD LAYOUT */
        .card { background: white; border: 1px solid #E6E4ED; border-radius: 16px; padding: 1.5rem; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 12px rgba(0,0,0,0.01); width: 100%; position: relative; z-index: 2; }
        .info { display: flex; align-items: center; gap: 1.5rem; flex: 1; }
        
        /* Fixed explicit dimension wrapper locks image parameters safely */
        .product-preview-container { width: 90px; height: 90px; min-width: 90px; min-height: 90px; display: flex; align-items: center; justify-content: center; background: #FFFFFF; border: 1px solid #EAE6F5; border-radius: 12px; padding: 6px; box-shadow: inset 0 2px 6px rgba(0,0,0,0.02); }
        .product-preview-container img { max-width: 100%; max-height: 100%; object-fit: contain; display: block; }
        
        .product-details-area { text-align: left; }
        .meta { font-size: 0.85rem; color: #756F86; margin-top: 0.3rem; }
        .meta span { background: #F0ECFC; color: #5D3EBC; padding: 0.15rem 0.5rem; border-radius: 4px; font-weight: 700; }
        
        .input-box { padding: 0.75rem 1.2rem; border-radius: 8px; border: 1px solid #9D8FE1; font-family: monospace; font-size: 0.95rem; min-width: 280px; outline: none; background: #FFFFFF; transition: border-color 0.2s; }
        .input-box:focus { border-color: #5D3EBC; box-shadow: 0 0 0 3px rgba(93, 62, 188, 0.1); }
        .btn { background: #5D3EBC; color: white; border: none; padding: 0.75rem 1.8rem; border-radius: 8px; font-weight: 600; cursor: pointer; transition: background 0.2s; font-size: 0.95rem; }
        .btn:hover { background: #472BA3; }
 
        /* 💡 LAYOUT DESIGN MATRICES FOR DROPDOWN ACCORDION CHIPS */
        .suggestions-panel-tray { display: flex; flex-direction: column; gap: 4px; width: calc(100% - 2rem); margin-left: auto; margin-right: auto; margin-top: -6px; }
        
        .suggestion-row-item { background: #F4F1FC; border: 1px dashed #C3B3EE; border-radius: 8px; padding: 0.75rem 1.5rem; display: flex; align-items: center; justify-content: space-between; font-size: 0.88rem; color: #4A2E9F; font-weight: 500; box-shadow: 0 2px 6px rgba(0,0,0,0.01); cursor: pointer; transition: all 0.15s ease-in-out; }
        .suggestion-row-item:hover { background: #EBE5F9; transform: translateX(3px); border-color: #5D3EBC; }
        
        .suggestion-content-left { display: flex; align-items: center; gap: 1.2rem; text-align: left; }
        
        .matched-preview-box { width: 48px; height: 48px; min-width: 48px; background: white; border: 1px solid #E1D9F7; border-radius: 6px; display: flex; align-items: center; justify-content: center; padding: 4px; }
        .matched-preview-box img { max-width: 100%; max-height: 100%; object-fit: contain; }
        
        .suggestion-content-left code { font-family: monospace; font-weight: 700; color: #5D3EBC; font-size: 0.95rem; background: #FFFFFF; padding: 2px 6px; border-radius: 4px; border: 1px solid #DDD6F3; }
        .use-sig-btn { background: #5D3EBC; color: white; font-weight: 700; font-size: 0.72rem; padding: 5px 12px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px; border: none; cursor: pointer; transition: background 0.15s; }
        .use-sig-btn:hover { background: #472BA3; }

        /* 🔔 FLOATING TOAST NOTIFICATION */
        .toast-message {
            position: fixed;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            background: #E8F8F5;
            color: #27AE60;
            border: 1px solid #D1F2EB;
            padding: 0.75rem 1.5rem;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.85rem;
            box-shadow: 0 10px 25px rgba(39, 174, 96, 0.15);
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: opacity 0.5s ease, transform 0.5s ease;
        }
        .toast-message.fade-out {
            opacity: 0;
            transform: translate(-50%, 10px);
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="container">
        
        <?php if (!empty($success_msg)): ?>
            <div id="successToast" class="toast-message">
                <?php echo $success_msg; ?>
            </div>
        <?php endif; ?>
 
        <div class="header-row">
            <a href="admin_crud.php" class="back-btn" style="background-color: #5D3EBC; color: white; padding: 0.6rem 2.2rem; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.9rem; box-shadow: 0 4px 12px rgba(93, 62, 188, 0.15); display: inline-flex; align-items: center; justify-content: center; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#472BA3'" onmouseout="this.style.backgroundColor='#5D3EBC'">
                Back
            </a>
            
            <button onclick="window.isNavigatingInside = true; window.location.reload()" class="back-btn" style="background-color: #2D9CDB; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; color: white; padding: 0.6rem 1.8rem; border-radius: 8px; font-weight: 600; font-size: 0.9rem; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#1b82bd'" onmouseout="this.style.backgroundColor='#2D9CDB'">
                <i class="fa-solid fa-rotate"></i> Refresh
            </button>
            
            <?php if ($pending_count > 0): ?>
                <button onclick="runAIAutoMatch()" class="back-btn" id="aiScanBtn" style="background-color: #231942; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; color: white; padding: 0.6rem 1.8rem; border-radius: 8px; font-weight: 600; font-size: 0.9rem;">
                    <i class="fa-solid fa-robot" id="aiRobotIcon"></i> <span id="aiScanText">Run AI Auto-Match Scan</span>
                </button>
            <?php endif; ?>
            
            <h2 style="color: #2D2543; font-size: 2rem; font-weight: 700; margin: 0; padding-left: 0.5rem;">Unassigned Discoveries Desk</h2>
        </div>
        
        <p style="color:#756F86; margin-bottom:2rem; padding-left: 0.2rem;">
            🚨 There are <strong><?php echo $pending_count; ?></strong> new products requiring visual signature tags!
        </p>
        
        <div class="discoveries-stack">
            <?php if ($pending_count > 0): ?>
                <?php foreach ($pending_discoveries as $row): 
                    // Retrieve suggestions using Model
                    $suggestions = Product::getSuggestionsForProduct($row['product_brand'], $row['product_category']);
                ?>
                    <div class="discovery-group">
                        <div class="card">
                            <div class="info">
                                <div class="product-preview-container">
                                    <img src="<?php echo htmlspecialchars($row['product_image']); ?>" onerror="this.src='no_image.png'">
                                </div>
                                <div class="product-details-area">
                                    <h4 style="font-size:1.1rem; font-weight:700; margin:0; color:#2D2543; line-height:1.3;"><?php echo htmlspecialchars($row['product_name']); ?></h4>
                                    <div class="meta">Brand: <span><?php echo htmlspecialchars($row['product_brand']); ?></span> | Category: <span><?php echo htmlspecialchars($row['product_category']); ?></span> | Store: <span><?php echo htmlspecialchars($row['product_store']); ?></span></div>
                                </div>
                            </div>
                            
                            <form method="POST" action="admin_notifications.php" style="display:flex; gap:0.6rem; align-items:center;">
                                <input type="hidden" name="target_name" value="<?php echo htmlspecialchars($row['product_name']); ?>">
                                <input class="input-box" type="text" name="custom_signature" placeholder="brand_name_size_x2" required>
                                <button class="btn" type="submit" name="assign_sig">Commit</button>
                            </form>
                        </div>
                        
                        <div class="suggestions-panel-tray">
                            <?php if (!empty($suggestions)): ?>
                                <?php foreach ($suggestions as $lookup_row): ?>
                                    <div class="suggestion-row-item" onclick="applySuggestion(this, '<?php echo htmlspecialchars($lookup_row['visual_signature']); ?>')">
                                        <div class="suggestion-content-left">
                                            <div class="matched-preview-box" title="Database Snapshot image of verified variant profile">
                                                <img src="<?php echo htmlspecialchars($lookup_row['sample_image']); ?>" onerror="this.src='no_image.png'">
                                            </div>
                                            <div>
                                                <i class="fa-solid fa-layer-group" style="color:#5D3EBC; margin-right:4px;"></i> 
                                                Existing signature option match: <code><?php echo htmlspecialchars($lookup_row['visual_signature']); ?></code>
                                            </div>
                                        </div>
                                        <button type="button" class="use-sig-btn">Select</button>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="suggestion-row-item" style="background:#FAFAFC; border-color:#E6E4ED; color:#756F86; cursor:default;">
                                    <div class="suggestion-content-left">
                                        <i class="fa-solid fa-triangle-exclamation" style="color:#756F86;"></i> 
                                        No products matches found for this specific combination yet.
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align:center; padding:4rem; background:white; border-radius:16px; border:1px solid #E6E4ED; color:#756F86; font-weight:500;">No unassigned discoveries at this time.</div>
            <?php endif; ?>
        </div>
    </div>
 
    <script>
        // Auto-dismiss success toast after 5 seconds
        const toast = document.getElementById('successToast');
        if (toast) {
            setTimeout(() => {
                toast.classList.add('fade-out');
                setTimeout(() => {
                    toast.remove();
                }, 500); // Wait for transition to finish
            }, 5000);
        }

        function applySuggestion(suggestionItemElement, tagValue) {
            const parentGroup = suggestionItemElement.closest('.discovery-group');
            const targetInput = parentGroup.querySelector('.input-box');
            if (targetInput) {
                targetInput.value = tagValue;
                targetInput.focus();
            }
        }
 
        function runAIAutoMatch() {
            window.isNavigatingInside = true; // Lock lifecycle unload handler
            const btn = document.getElementById('aiScanBtn');
            const icon = document.getElementById('aiRobotIcon');
            const text = document.getElementById('aiScanText');
            btn.style.opacity = '0.6';
            btn.style.pointerEvents = 'none';
            icon.className = 'fa-solid fa-spinner fa-spin';
            text.innerText = 'Scanning & Matching Items...';
 
            fetch('ai_image_matcher.php')
                .then(response => response.text())
                .then(data => {
                    alert("AI Image Matching Scan Completed Successfully!");
                    window.location.reload();
                })
                .catch(err => {
                    alert("API Integration Fetch Error: " + err);
                    window.location.reload();
                });
        }
 
        window.isNavigatingInside = false;
 
        document.addEventListener('click', function(e) {
            if (e.target.closest('a') || e.target.closest('button') || e.target.closest('form')) {
                window.isNavigatingInside = true;
            }
        });
 
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', () => { window.isNavigatingInside = true; });
        });
 
        window.addEventListener('beforeunload', function (event) {
            let isReload = false;
            if (window.performance) {
                if (performance.navigation && performance.navigation.type === 1) {
                    isReload = true;
                } else {
                    const navEntries = performance.getEntriesByType('navigation');
                    if (navEntries.length > 0 && navEntries[0].type === 'reload') {
                        isReload = true;
                    }
                }
            }
 
            if (window.isNavigatingInside || isReload) {
                return;
            }
            navigator.sendBeacon('logout.php'); 
        });
    </script>
</body>
</html>
