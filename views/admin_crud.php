<?php
/** @var int $alert_count */
/** @var int $total_products_count */
/** @var int $total_categories */
/** @var int $total_brands */
/** @var int $total_visits */
/** @var string $search_query */
/** @var array $date_options */
/** @var string $selected_date */
/** @var int $total_pages */
/** @var int $page */
/** @var array $analytics_result */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="logo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | SKIN+</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary-color: #5D3EBC; --bg-color: #FAFAFC; --card-bg: #FFFFFF; --text-dark: #2D2543; --text-muted: #756F86; --border-color: #E2E0EB; --cheap-color: #27AE60; --expensive-color: #EB5757; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg-color); color: var(--text-dark); padding: 2rem 8% 5rem 8%; }
        .navbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem; }
        .logo-area h1 { font-size: 1.8rem; font-weight: 700; color: var(--primary-color); line-height: 1.1; }
        .logo-area p { font-size: 0.85rem; color: var(--text-muted); font-weight: 500; }
        .nav-actions { display: flex; gap: 1rem; align-items: center; }
        .back-btn { background-color: var(--primary-color); color: white; padding: 0.6rem 2.2rem; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.9rem; box-shadow: 0 4px 12px rgba(93, 62, 188, 0.15); }
        .analytics-trigger-btn { background-color: #231942; color: white; padding: 0.6rem 1.8rem; border-radius: 8px; border: none; font-weight: 600; font-size: 0.9rem; cursor: pointer; display: flex; align-items: center; gap: 6px; }
        .metrics-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 3rem; }
        .metric-card { background: var(--card-bg); border: 1px solid #EEEEF5; padding: 1.5rem; border-radius: 16px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.02); }
        .metric-card h3 { font-size: 1.05rem; font-weight: 500; color: var(--text-muted); margin-bottom: 0.5rem; }
        .metric-card p { font-size: 2.4rem; font-weight: 700; color: var(--primary-color); }
        
        .controls-wrapper { margin-bottom: 2rem; display: flex; justify-content: flex-end; align-items: center; gap: 1rem; }
        .dropdown-select {
            padding: 0.75rem 2.2rem 0.75rem 1.5rem;
            border: 2px solid var(--primary-color);
            border-radius: 30px;
            font-size: 0.95rem;
            outline: none;
            background: #FFFFFF;
            color: var(--primary-color);
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 8px rgba(93, 62, 188, 0.06);
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%235D3EBC' viewBox='0 0 16 16'><path fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/></svg>");
            background-repeat: no-repeat;
            background-position: calc(100% - 1.2rem) center;
        }
        .dropdown-select:hover {
            background-color: #F8F6FF;
            box-shadow: 0 4px 12px rgba(93, 62, 188, 0.12);
        }
        .dropdown-select:focus {
            background-color: #F1EDFF;
            border-color: #4A2E9F;
        }
        .search-form { display: flex; gap: 0.5rem; width: 100%; max-width: 400px; position: relative; }
        .search-input { width: 100%; padding: 0.75rem 3rem 0.75rem 1.2rem; border: 1px solid var(--border-color); border-radius: 30px; font-size: 0.95rem; outline: none; background: #FFFFFF; transition: all 0.2s; color: var(--text-dark); }
        .search-input:focus { border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(93, 62, 188, 0.08); }
        .search-form i { position: absolute; right: 1.2rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); pointer-events: none; }

        .crud-container { background: white; border-radius: 24px; padding: 2.5rem; border: 1px solid #EEEEF5; box-shadow: 0 4px 24px rgba(0,0,0,0.01); }
        .crud-container h2 { font-size: 2rem; font-weight: 700; margin-bottom: 2rem; color: #1A1230; }
        
        .table-header { display: grid; grid-template-columns: 2fr 1.5fr 0.9fr 0.9fr 0.7fr 0.7fr 1.1fr; padding: 0 1.5rem 0.8rem 1.5rem; color: var(--text-muted); font-weight: 600; font-size: 0.9rem; border-bottom: 1px solid var(--border-color); margin-bottom: 1rem; }
        .product-stack { display: flex; flex-direction: column; gap: 0.85rem; }
        .product-row { display: grid; grid-template-columns: 2fr 1.5fr 0.9fr 0.9fr 0.7fr 0.7fr 1.1fr; align-items: center; padding: 1.1rem 1.5rem; background: #FFFFFF; border-radius: 40px; border: 1px solid #EAE8F2; box-shadow: 0 3px 10px rgba(93, 62, 188, 0.02); transition: transform 0.2s, box-shadow 0.2s; }
        .product-row:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(93, 62, 188, 0.05); }
        
        .cell-name { font-weight: 600; color: #4A435A; padding-right: 1rem; text-align: left; display: -webkit-box; -webkit-line-clamp: 1; line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
        .cell-meta { color: var(--text-muted); font-size: 0.92rem; text-align: left; }
        .cell-min { color: var(--cheap-color); font-weight: 600; font-size: 0.95rem; text-align: left; }
        .cell-max { color: var(--expensive-color); font-weight: 600; font-size: 0.95rem; text-align: left; }
        .cell-sig { font-family: 'Courier New', Courier, monospace; font-size: 0.82rem; font-weight: 700; color: #D35400; background-color: #FDF2E9; padding: 0.3rem 0.6rem; border-radius: 6px; display: inline-block; width: fit-content; max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        
        .action-toolbar { display: flex; gap: 0.5rem; justify-content: flex-start; }
        .btn-tool { background: none; border: none; font-size: 1.05rem; color: #1A1230; cursor: pointer; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 50%; transition: background 0.2s; }
        .btn-tool:hover { background: #F0EDF7; color: var(--primary-color); }
        
        .pagination-container { display: flex; justify-content: center; align-items: center; gap: 1.5rem; margin-top: 3.5rem; font-size: 0.95rem; color: var(--text-muted); font-weight: 500; }
        .page-arrow-btn { width: 44px; height: 44px; border-radius: 50%; border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; text-decoration: none; color: var(--text-dark); font-size: 1rem; transition: all 0.2s; background: #FFFFFF; box-shadow: 0 2px 8px rgba(0,0,0,0.02); }
        .page-arrow-btn:hover { border-color: var(--primary-color); color: var(--primary-color); transform: scale(1.05); }
        .page-arrow-btn.disabled { opacity: 0.35; pointer-events: none; background: #F4F4F6; box-shadow: none; border-color: var(--border-color); }
        .page-counter-label { font-weight: 600; color: var(--text-dark); letter-spacing: 0.3px; }
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(26, 18, 48, 0.4); display: flex; align-items: center; justify-content: center; opacity: 0; pointer-events: none; transition: opacity 0.25s ease; z-index: 1000; }
        .modal-overlay.active { opacity: 1; pointer-events: auto; }
        .modal-window { background: #FAFAFD; border-radius: 32px; width: 100%; max-width: 780px; padding: 2.5rem; box-shadow: 0 12px 40px rgba(0,0,0,0.12); position: relative; border: 1px solid #EAE8F2; max-height: 85vh; overflow-y: auto; }
        .btn-close-modal { position: absolute; top: 1.5rem; right: 1.5rem; background: none; border: none; font-size: 1.6rem; cursor: pointer; color: var(--text-dark); }
        .modal-window h3 { text-align: center; font-size: 1.8rem; font-weight: 700; color: #231942; margin-bottom: 2rem; }
        .modal-form-grid { display: flex; flex-direction: column; gap: 0.8rem; margin-bottom: 1.5rem; }
        .modal-form-row { display: grid; grid-template-columns: 130px 1fr; align-items: flex-start; font-size: 0.95rem; }
        .modal-form-row.align-center { align-items: center; }
        .modal-form-row label { font-weight: 700; color: var(--text-dark); text-align: right; padding-right: 1rem; }
        .modal-value-text { color: var(--text-muted); line-height: 1.4; text-align: left; }
        .modal-input-field { width: 100%; padding: 0.5rem 0.8rem; border-radius: 6px; border: 1px solid var(--border-color); font-size: 0.95rem; background: white; outline: none; }
        .prices-label-header { text-align: left; font-weight: 700; margin: 1.5rem 0 0.8rem 0; font-size: 1.1rem; border-bottom: 2px solid var(--primary-color); padding-bottom: 4px; }
        .matrix-table { width: 100%; border-collapse: collapse; background: white; border: 1px solid #EAE8F2; margin-bottom: 1.2rem; text-align: left; font-size: 0.9rem; }
        .matrix-table th { background: #F5F5FA; padding: 0.75rem; border: 1px solid #EAE8F2; font-weight: 600; color: var(--text-dark); }
        .matrix-table td { padding: 0.75rem; border: 1px solid #EAE8F2; color: var(--text-muted); font-weight: 500; }
        .matrix-input { width: 100%; padding: 0.4rem; border: 1px solid var(--border-color); border-radius: 4px; }
        .price-input-box { max-width: 100px; font-weight: 600; text-align: center; }
        .modal-summary-lbl { text-align: right; font-weight: 700; font-size: 1.05rem; color: var(--text-dark); margin-top: 1rem; }
        .modal-summary-lbl span { font-weight: 700; color: var(--primary-color); margin-left: 0.5rem; }
        .modal-footer-toolbar { display: flex; justify-content: center; gap: 1rem; margin-top: 2rem; }
        .btn-modal { padding: 0.65rem 2.5rem; border-radius: 10px; border: none; font-weight: 600; font-size: 0.95rem; cursor: pointer; transition: all 0.15s ease; }
        .btn-modal-cancel { background-color: #A399C9; color: white; }
        .btn-modal-submit { background-color: #231942; color: white; }
        .btn-modal-delete { background-color: #EB5757; color: white; }

        /* Button Press Active Color & Transform States */
        .back-btn, .analytics-trigger-btn, .btn-modal, .btn-tool, .page-arrow-btn {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .back-btn:hover, .analytics-trigger-btn:hover, .btn-modal:hover {
            filter: brightness(115%);
            box-shadow: 0 4px 15px rgba(0,0,0,0.12);
        }
        .back-btn:active, .analytics-trigger-btn:active, .btn-modal:active {
            filter: brightness(80%) !important;
            transform: scale(0.95) !important;
            box-shadow: 0 2px 5px rgba(0,0,0,0.06);
        }
        .btn-tool:active, .page-arrow-btn:active {
            transform: scale(0.92) !important;
            background-color: #E2DFEE !important;
        }
        .analytics-scroll-box { max-height: 400px; overflow-x: auto; overflow-y: auto; margin-top: 1rem; border: 1px solid var(--border-color); border-radius: 12px; }

        
        @media (max-width: 768px) {
            body { padding: 1.5rem 4% 4rem 4%; }
            .navbar { flex-direction: column; gap: 1.2rem; text-align: center; margin-bottom: 2rem; }
            .logo-area h1 { font-size: 1.5rem; }
            .nav-actions { width: 100%; justify-content: center; flex-wrap: wrap; gap: 0.75rem; }
            .back-btn, .analytics-trigger-btn { flex: 1; min-width: 140px; text-align: center; justify-content: center; padding: 0.65rem 1rem; font-size: 0.85rem; border-radius: 8px; }
            
            .metrics-grid { grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 2rem; }
            .metric-card { padding: 1.2rem 0.8rem; border-radius: 12px; }
            .metric-card h3 { font-size: 0.9rem; }
            .metric-card p { font-size: 1.8rem; }
            
            .controls-wrapper { flex-direction: column; align-items: stretch; gap: 0.8rem; }
            .search-form { max-width: 100%; }
            .dropdown-select { width: 100%; text-align-last: center; }
            
            .crud-container { padding: 1.5rem 1rem; border-radius: 18px; }
            .crud-container h2 { font-size: 1.6rem; margin-bottom: 1.5rem; text-align: center; }
            
            
            .table-header { display: none; }
            
            
            .product-row {
                display: flex;
                flex-direction: column;
                align-items: stretch;
                padding: 1.5rem;
                background: #FFFFFF;
                border-radius: 20px;
                border: 1px solid #EAE8F2;
                box-shadow: 0 4px 12px rgba(93, 62, 188, 0.02);
                gap: 0.5rem;
                position: relative;
            }
            .cell-name {
                font-size: 1.1rem;
                font-weight: 700;
                color: var(--text-dark);
                line-clamp: 2;
                -webkit-line-clamp: 2;
                padding-right: 0;
            }
            .cell-sig {
                font-size: 0.78rem;
                padding: 0.25rem 0.5rem;
                margin-top: 0.1rem;
            }
            
            
            .cell-meta:nth-of-type(1)::before { content: "Category: "; font-weight: 700; color: var(--text-dark); }
            .cell-meta:nth-of-type(2)::before { content: "Brand: "; font-weight: 700; color: var(--text-dark); }
            .cell-min::before { content: "Min Price: "; font-weight: 700; color: var(--text-dark); }
            .cell-max::before { content: "Max Price: "; font-weight: 700; color: var(--text-dark); }
            
            .cell-meta, .cell-min, .cell-max {
                font-size: 0.9rem;
                text-align: left;
                width: 100%;
            }
            .cell-min { color: var(--cheap-color); }
            .cell-max { color: var(--expensive-color); }
            
            .action-toolbar {
                margin-top: 0.8rem;
                padding-top: 0.8rem;
                border-top: 1px solid var(--border-color);
                justify-content: flex-end;
                width: 100%;
            }
            
            
            .modal-window {
                width: 95%;
                padding: 1.8rem 1.2rem;
                border-radius: 24px;
            }
            .modal-window h3 {
                font-size: 1.4rem;
                margin-bottom: 1.5rem;
            }
            .modal-form-row {
                grid-template-columns: 1fr;
                gap: 0.25rem;
            }
            .modal-form-row label {
                text-align: left;
                padding-right: 0;
                font-size: 0.85rem;
            }
            .matrix-table {
                font-size: 0.82rem;
            }
            .matrix-table th, .matrix-table td {
                padding: 0.5rem 0.4rem;
            }
            .modal-footer-toolbar {
                flex-direction: column;
                gap: 0.8rem;
            }
            .btn-modal {
                width: 100%;
                padding: 0.75rem 1.5rem;
            }
        }
        
        @media (max-width: 480px) {
            .metrics-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="logo-area"><h1>SKIN+</h1><p>Smart Price Comparison</p></div>
        <div class="nav-actions">
            <a href="admin_notifications.php" class="analytics-trigger-btn" style="text-decoration: none; background: <?php echo ($alert_count > 0) ? '#EB5757' : '#231942'; ?>;">
                <i class="fa-solid fa-bell"></i> New Approvals 
                <?php if ($alert_count > 0): ?>
                    <span style="background: white; color: #EB5757; padding: 2px 7px; border-radius: 50%; font-size: 0.8rem; font-weight: bold; margin-left: 4px;"><?php echo $alert_count; ?></span>
                <?php endif; ?>
            </a>
            <button class="analytics-trigger-btn" style="background:#D35400;" onclick="document.getElementById('historyModal').classList.add('active')"><i class="fa-solid fa-clock-rotate-left"></i> History Logs</button>
            <button class="analytics-trigger-btn" onclick="triggerAnalytics()"><i class="fa-solid fa-chart-line"></i> View Logs</button>
            <a href="logout.php" class="back-btn">Log Out</a>
        </div>
    </nav>

    <div class="metrics-grid">
        <div class="metric-card"><h3>Total Products</h3><p><?php echo $total_products_count; ?></p></div>
        <div class="metric-card"><h3>Category Profiles</h3><p><?php echo $total_categories; ?></p></div>
        <div class="metric-card"><h3>Brands</h3><p><?php echo $total_brands; ?></p></div>
        <div class="metric-card"><h3>Total Visits</h3><p><?php echo $total_visits; ?></p></div>
    </div>

    <div class="controls-wrapper">
        <form method="GET" action="admin_crud.php" id="filterForm" style="display:flex; gap:1rem; align-items:center; width:100%; justify-content:flex-end;">
            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_query); ?>">
            <select name="scrape_date" class="dropdown-select" onchange="document.getElementById('filterForm').submit();">
                <?php foreach ($date_options as $d_opt): ?>
                    <option value="<?php echo $d_opt; ?>" <?php echo ($selected_date === $d_opt) ? 'selected' : ''; ?>>
                        📅 Batch: <?php echo date("d M Y", strtotime($d_opt)); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <form method="GET" action="admin_crud.php" class="search-form">
            <input type="hidden" name="scrape_date" value="<?php echo htmlspecialchars($selected_date); ?>">
            <input type="text" name="search" class="search-input" placeholder="Search title, brand, category, or signature..." value="<?php echo htmlspecialchars($search_query); ?>" autocomplete="off">
            <i class="fa-solid fa-magnifying-glass"></i>
        </form>
    </div>

    <main class="crud-container">
        <h2>Products Directory (Signature Clustering System)</h2>
        
        <div class="table-header">
            <span>Product Name Group</span>
            <span>Visual Signature</span>
            <span>Category</span>
            <span>Brand</span>
            <span>Min Price</span>
            <span>Max Price</span>
            <span>Actions</span>
        </div>

        <div class="product-stack">
            <?php if (!empty($items)): ?>
                <?php foreach ($items as $group): 
                    $min_p = min($group['prices']);
                    $max_p = max($group['prices']);
                    $group_json = htmlspecialchars(json_encode($group, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8');
                ?>
                    <div class="product-row">
                        <span class="cell-name" title="<?php echo htmlspecialchars($group['product_name']); ?>"><?php echo htmlspecialchars($group['product_name']); ?></span>
                        <div><span class="cell-sig" title="<?php echo htmlspecialchars($group['signature']); ?>"><i class="fa-solid fa-fingerprint"></i> <?php echo htmlspecialchars($group['signature']); ?></span></div>
                        <span class="cell-meta"><?php echo htmlspecialchars($group['product_category']); ?></span>
                        <span class="cell-meta"><?php echo htmlspecialchars($group['product_brand']); ?></span>
                        <span class="cell-min">RM <?php echo number_format($min_p, 2); ?></span>
                        <span class="cell-max">RM <?php echo number_format($max_p, 2); ?></span>
                        <div class="action-toolbar">
                            <button class="btn-tool" onclick="triggerView(<?php echo $group_json; ?>)" title="View Store Level Mappings"><i class="fa-regular fa-eye"></i></button>
                            <button class="btn-tool" onclick="triggerEdit(<?php echo $group_json; ?>)" title="Modify Product Profiles"><i class="fa-regular fa-pen-to-square"></i></button>
                            <button class="btn-tool" onclick="triggerDelete(<?php echo $group_json; ?>)" title="Remove Record Rows"><i class="fa-regular fa-trash-can"></i></button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align:center; color:var(--text-muted); padding:3rem 0;">No active signature matching items found for this batch parameters.</div>
            <?php endif; ?>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="pagination-container">
                <a href="?scrape_date=<?php echo urlencode($selected_date); ?>&search=<?php echo urlencode($search_query); ?>&page=<?php echo ($page - 1); ?>" class="page-arrow-btn <?php echo ($page <= 1) ? 'disabled' : ''; ?>"><i class="fa-solid fa-chevron-left"></i></a>
                <span class="page-counter-label">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
                <a href="?scrape_date=<?php echo urlencode($selected_date); ?>&search=<?php echo urlencode($search_query); ?>&page=<?php echo ($page + 1); ?>" class="page-arrow-btn <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>"><i class="fa-solid fa-chevron-right"></i></a>
            </div>
        <?php endif; ?>
    </main>

    <div class="modal-overlay" id="viewModal">
        <div class="modal-window">
            <button class="btn-close-modal" onclick="dismissModal('viewModal')">×</button>
            <h3>Group Overview Mappings</h3>
            <div class="modal-form-grid">
                <div class="modal-form-row"><label>Brand:</label><span class="modal-value-text" id="v_brand"></span></div>
                <div class="modal-form-row"><label>Category:</label><span class="modal-value-text" id="v_category"></span></div>
                <div class="modal-form-row"><label>Signature:</label><span class="modal-value-text" id="v_sig" style="font-family:monospace; color:#D35400; font-weight:700;"></span></div>
            </div>
            <div class="prices-label-header">Live Store Individual Title Splits</div>
            <table class="matrix-table">
                <thead><tr><th>Store Target</th><th>Scraped Item Title Listing</th><th>Current Price</th></tr></thead>
                <tbody id="v_tb"></tbody>
            </table>
            <div class="modal-summary-lbl">Group Price Average: <span id="v_avg"></span></div>
        </div>
    </div>

    <div class="modal-overlay" id="editModal">
        <div class="modal-window">
            <button class="btn-close-modal" onclick="dismissModal('editModal')">×</button>
            <h3>Modify Unified Product Group</h3>
            <form method="POST" action="admin_crud.php">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="product_id" id="e_id">
                <div class="modal-form-grid">
                    <div class="modal-form-row align-center"><label>Category:</label><input type="text" name="product_category" id="e_category" class="modal-input-field" required></div>
                    <div class="modal-form-row align-center"><label>Brand:</label><input type="text" name="product_brand" id="e_brand" class="modal-input-field" required></div>
                </div>
                <div class="prices-label-header">Individual Store Rows Customization Mappings</div>
                <table class="matrix-table">
                    <thead><tr><th>Store Channel</th><th>Real Scraped Store Item Title</th><th>Store Price (RM)</th></tr></thead>
                    <tbody id="e_tb"></tbody>
                </table>
                <div class="modal-summary-lbl">Calculated Average: <span id="e_avg"></span></div>
                <div class="modal-footer-toolbar">
                    <button type="button" class="btn-modal btn-modal-cancel" onclick="dismissModal('editModal')">Cancel</button>
                    <button type="submit" class="btn-modal btn-modal-submit">Save Multi-Store Changes</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="deleteModal">
        <div class="modal-window" style="max-width:600px;">
            <button class="btn-close-modal" onclick="dismissModal('deleteModal')">×</button>
            <h3>Remove Specific Store Record Target</h3>
            <p class="delete-alert-text" style="margin-bottom:1rem;">Select which specific retailer record you want to erase from this bundle profile:</p>
            <table class="matrix-table">
                <thead><tr><th>Retailer</th><th>Real Scraped Row Item Name</th><th>Action Execution</th></tr></thead>
                <tbody id="d_tb"></tbody>
            </table>
            <div class="modal-footer-toolbar" style="margin-top:1rem;">
                <button type="button" class="btn-modal btn-modal-cancel" onclick="dismissModal('deleteModal')">Close Window</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="analyticsModal">
        <div class="modal-window">
            <button class="btn-close-modal" onclick="dismissModal('analyticsModal')">×</button>
            <h3><i class="fa-solid fa-users-line"></i> Visitor Traffic Monitor Panel</h3>
            <div class="prices-label-header">Recent Access Activity Footprints Log (Total Hits Tracking: <?php echo $total_visits; ?>)</div>
            <div class="analytics-scroll-box">
                <table class="matrix-table" style="margin-bottom:0;">
                    <thead><tr><th>Sequence ID</th><th>User Visitor Hash Tracking Token</th><th>Access Session Timestamps</th></tr></thead>
                    <tbody>
                        <?php if ($analytics_result && $analytics_result->num_rows > 0): $seq = 1; ?>
                            <?php while ($log = $analytics_result->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $seq++; ?></td>
                                    <td style="font-family:monospace; font-weight:600; color:var(--primary-color);"><?php echo htmlspecialchars($log['visitor_id']); ?></td>
                                    <td><?php echo htmlspecialchars($log['time_session']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="3">No viewer traffic footprints recorded in connection log blocks.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="historyModal">
        <div class="modal-window" style="max-width: 920px;">
            <button class="btn-close-modal" onclick="dismissModal('historyModal')">×</button>
            <h3><i class="fa-solid fa-clock-rotate-left"></i> Administrative Mutation History</h3>
            <div class="prices-label-header">Audit Tracking Logs</div>
            <div class="analytics-scroll-box" style="max-height: 450px;">
                <table class="matrix-table" style="margin-bottom:0;">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>Action</th>
                            <th>Target Item</th>
                            <th>Previous Value State</th>
                            <th>Updated Value State</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $history_res = Database::getMysqli()->query("SELECT action_type, target_identifier, old_value, new_value, changed_at FROM history_logs ORDER BY changed_at DESC LIMIT 150");
                        if ($history_res && $history_res->num_rows > 0):
                            while ($h_log = $history_res->fetch_assoc()):
                        ?>
                            <tr>
                                <td style="font-size:0.8rem; white-space:nowrap;"><?php echo $h_log['changed_at']; ?></td>
                                <td>
                                    <span style="font-weight:700; font-size:0.78rem; padding:2px 6px; border-radius:4px; background:<?php echo $h_log['action_type'] === 'DELETE_ROW' ? '#FADBD8; color:#E53935;' : ($h_log['action_type'] === 'ASSIGN_SIGNATURE' ? '#E8F8F5; color:#117A65;' : '#D4EFDF; color:#2E7D32;'); ?>">
                                        <?php echo $h_log['action_type']; ?>
                                    </span>
                                </td>
                                <td style="font-size:0.85rem; font-weight:600;"><?php echo htmlspecialchars($h_log['target_identifier']); ?></td>
                                <td style="font-family:monospace; font-size:0.75rem; color:#756F86; max-width:220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="<?php echo htmlspecialchars($h_log['old_value']); ?>"><?php echo htmlspecialchars($h_log['old_value']); ?></td>
                                <td style="font-family:monospace; font-size:0.75rem; color:var(--primary-color); max-width:220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="<?php echo htmlspecialchars($h_log['new_value']); ?>"><?php echo htmlspecialchars($h_log['new_value']); ?></td>
                            </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                            <tr><td colspan="5" style="text-align:center; padding:2rem; color:var(--text-muted);">No administrative modification entries captured yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function formatStoreNameJS(store) {
            let sL = store.toLowerCase().trim();
            if (sL === 'caring pharmacy' || sL === 'caring') return 'CARiNG PHARMACY';
            if (sL === 'watsons') return 'watsons';
            if (sL === 'guardian') return 'guardian';
            return sL;
        }

        function unpackMatrixNodes(storeNodesArray) {
            if (!storeNodesArray || storeNodesArray.length === 0) return [];
            return storeNodesArray.map(node => {
                let chunks = node.split(':::DataSplitKey:::');
                return { id: chunks[0], store: chunks[1], name: chunks[2], price: parseFloat(chunks[3]) };
            });
        }

        function triggerView(groupData) {
            document.getElementById('v_brand').innerText = groupData.product_brand;
            document.getElementById('v_category').innerText = groupData.product_category;
            document.getElementById('v_sig').innerText = groupData.signature;
            let elements = unpackMatrixNodes(groupData.store_nodes);
            let tbContent = ''; let priceSum = 0;
            elements.forEach(item => {
                priceSum += item.price;
                tbContent += `<tr><td style="font-weight:700; color:var(--primary-color);">${formatStoreNameJS(item.store)}</td><td>${item.name}</td><td style="font-weight:600; color:var(--cheap-color);">RM ${item.price.toFixed(2)}</td></tr>`;
            });
            document.getElementById('v_tb').innerHTML = tbContent;
            document.getElementById('v_avg').innerText = elements.length > 0 ? `RM ${(priceSum / elements.length).toFixed(2)}` : 'RM 0.00';
            document.getElementById('viewModal').classList.add('active');
        }


        function triggerEdit(groupData) {
            document.getElementById('e_id').value = groupData.product_id;
            document.getElementById('e_category').value = groupData.product_category;
            document.getElementById('e_brand').value = groupData.product_brand;
            let elements = unpackMatrixNodes(groupData.store_nodes);
            let tbContent = ''; let priceSum = 0;
            elements.forEach(item => {
                priceSum += item.price;
                tbContent += `<tr><td style="font-weight:700; color:var(--primary-color);">${formatStoreNameJS(item.store)}</td><td><input type="text" name="store_data[${item.id}][name]" value="${item.name.replace(/"/g, '&quot;')}" class="matrix-input" required></td><td>RM <input type="number" step="0.01" name="store_data[${item.id}][price]" value="${item.price.toFixed(2)}" class="matrix-input price-input-box" required></td></tr>`;
            });
            document.getElementById('e_tb').innerHTML = tbContent;
            document.getElementById('e_avg').innerText = elements.length > 0 ? `RM ${(priceSum / elements.length).toFixed(2)}` : 'RM 0.00';
            document.getElementById('editModal').classList.add('active');
        }

        function triggerDelete(groupData) {
            let elements = unpackMatrixNodes(groupData.store_nodes);
            let tbContent = '';
            elements.forEach(item => {
                tbContent += `<tr><td style="font-weight:700; color:var(--primary-color);">${formatStoreNameJS(item.store)}</td><td style="font-size:0.85rem;">${item.name}</td><td><form method="POST" action="admin_crud.php" style="margin:0;" onsubmit="window.isNavigatingInside = true; return confirm('Erase this exact record line from DB?');"><input type="hidden" name="action" value="delete"><input type="hidden" name="product_id" value="${item.id}"><button type="submit" class="btn-modal btn-modal-delete" style="padding:4px 12px; font-size:0.8rem; border-radius:4px;"><i class="fa-regular fa-trash-can"></i> Delete</button></form></td></tr>`;
            });
            document.getElementById('d_tb').innerHTML = tbContent;
            document.getElementById('deleteModal').classList.add('active');
        }

        function triggerAnalytics() { document.getElementById('analyticsModal').classList.add('active'); }
        function dismissModal(id) { document.getElementById(id).classList.remove('active'); }

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
            if (window.isNavigatingInside || performance.navigation.type === 1) {
                return;
            }
            navigator.sendBeacon('logout.php');
        });
    </script>
</body>
</html>
