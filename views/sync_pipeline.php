<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SKIN+ | Automation Pipeline Sync</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #FAFAFC; color: #2D2543; padding: 3rem; }
        .card { background: white; border: 1px solid #E6E4ED; border-radius: 16px; padding: 2.5rem; max-width: 680px; margin: 0 auto; box-shadow: 0 4px 15px rgba(0,0,0,0.02); }
        h2 { color: #5D3EBC; font-size: 1.6rem; margin-bottom: 1.5rem; }
        .log-line { margin-bottom: 1rem; font-size: 1.05rem; }
        .btn-back { background: #5D3EBC; color: white; border: none; padding: 0.75rem 2rem; border-radius: 8px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; margin-top: 1.5rem; }
        .btn-back:hover { background: #472BA3; }
    </style>
</head>
<body>
    <div class="card">
        <h2>🔄 SKIN+ Post-Scrape Automation Pipeline</h2>
        <div class="log-line">Scanning database for newly inserted products...</div>
        
        <div class="log-line">✅ Successfully auto-assigned signatures to known products.</div>
        
        <?php if ($new_discoveries_count > 0): ?>
            <div class="log-line" style="margin-top:1.5rem; border-top:1px solid #E6E4ED; padding-top:1.5rem;">
                🚨 Discovered <strong><?php echo $new_discoveries_count; ?></strong> brand-new items! They have been added to your admin panel and appended to <strong>cleaned_signatures.csv</strong>.
            </div>
        <?php else: ?>
            <div class="log-line" style="margin-top:1.5rem; border-top:1px solid #E6E4ED; padding-top:1.5rem; color:#27AE60; font-weight:600;">
                🎉 No unmapped items found. All freshly scraped products are perfectly aligned!
            </div>
        <?php endif; ?>

        <a href="admin_crud.php" class="btn-back">Go to Dashboard</a>
    </div>
</body>
</html>
