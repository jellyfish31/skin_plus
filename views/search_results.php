<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="/favicon.png?v=3">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SKIN+ | Price Comparison</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary-color: #5D3EBC; --bg-color: #FAFAFC; --card-bg: #FFFFFF; --text-dark: #2D2543; --text-muted: #756F86; --border-color: #E6E4ED; --store-badge-bg: #F0ECFC; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg-color); color: var(--text-dark); }
        .navbar { display: flex; justify-content: space-between; align-items: center; padding: 1.5rem 8%; background: var(--card-bg); border-bottom: 1px solid var(--border-color); }
        .back-nav { display: flex; align-items: center; gap: 1rem; text-decoration: none; color: var(--text-dark); }
        .logo-area h1 { font-size: 1.8rem; font-weight: 700; color: var(--primary-color); }
        .content-header { max-width: 1100px; margin: 4rem auto 2rem auto; padding: 0 2rem; }
        .content-header h2 { font-size: 2.5rem; font-weight: 700; }
        .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 2.5rem; max-width: 1100px; margin: 0 auto 5rem auto; padding: 0 2rem; }
        .product-card { background: var(--card-bg); border-radius: 16px; padding: 1.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.01); border: 1px solid var(--border-color); display: flex; flex-direction: column; justify-content: space-between; text-decoration: none; color: inherit; transition: transform 0.2s; }
        .product-card:hover { transform: translateY(-5px); }
        .slider-viewport { background: #FFFFFF; border-radius: 12px; margin-bottom: 1.2rem; height: 220px; position: relative; overflow: hidden; }
        .slides-container { display: flex; width: 100%; height: 100%; transition: transform 0.3s; }
        .slide-frame { min-width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; padding: 1rem; }
        .slide-frame img { max-height: 100%; max-width: 100%; object-fit: contain; }
        .slider-arrow { position: absolute; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.9); border: 1px solid var(--border-color); width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; cursor: pointer; z-index: 10; }
        .arrow-left { left: 8px; } .arrow-right { right: 8px; }
        .slider-dots { position: absolute; bottom: 8px; left: 50%; transform: translateX(-50%); display: flex; gap: 4px; }
        .dot { width: 6px; height: 6px; background: #D0CADF; border-radius: 50%; }
        .dot.active { background: var(--primary-color); width: 12px; border-radius: 4px; }
        .prod-title { font-size: 1.1rem; font-weight: 700; margin-bottom: 0.8rem; min-height: 52px; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-align: left; }
        .store-badge { font-size: 0.8rem; color: var(--primary-color); background-color: var(--store-badge-bg); padding: 0.4rem 0.8rem; border-radius: 6px; text-align: left; margin-bottom: 1.5rem; font-weight: 600; display: inline-block; width: 100%; }
        .price-range { font-size: 0.95rem; font-weight: 600; color: var(--text-muted); text-align: left; border-top: 1px solid var(--border-color); padding-top: 1rem; }
        .price-range span { color: var(--text-dark); font-weight: 700; display: block; font-size: 1.1rem; }

        
        @media (max-width: 768px) {
            body { font-size: 14px; }
            .navbar { padding: 1rem 6%; }
            .logo-area h1 { font-size: 1.5rem; }
            
            .content-header { margin: 2rem auto 1.5rem auto; padding: 0 1rem; }
            .content-header h2 { font-size: 1.8rem; text-align: center; }
            #productCount { text-align: center; }
            
            .products-grid { gap: 1.5rem; padding: 0 1rem; margin-bottom: 3rem; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="back-nav">
            <i class="fa-solid fa-chevron-left"></i>
            <div class="logo-area"><h1>SKIN+</h1></div>
        </a>
    </nav>
    <header class="content-header">
        <h2 id="pageViewTitle"><?php echo $view_title; ?></h2>
        <p id="productCount">0 unique items isolated</p>
    </header>
    
    <main class="products-grid" id="mainProductsGrid">
    </main>

    <script>
    function formatStoreNameJS(storesStr) {
        if (!storesStr) return '';
        return storesStr.split(', ').map(s => {
            const sL = s.toLowerCase().trim();
            if (sL === 'caring pharmacy' || sL === 'caring') return 'CARiNG PHARMACY';
            if (sL === 'watsons') return 'watsons';
            if (sL === 'guardian') return 'guardian';
            return sL;
        }).join(', ');
    }

    function moveSlider(element, direction, event) {
        event.preventDefault(); event.stopPropagation();
        const viewport = element.parentElement;
        const container = viewport.querySelector('.slides-container');
        const dots = viewport.querySelectorAll('.dot');
        let currentIndex = parseInt(viewport.getAttribute('data-index')) + direction;
        const totalImages = parseInt(viewport.getAttribute('data-total'));
        if (currentIndex >= totalImages) currentIndex = 0;
        if (currentIndex < 0) currentIndex = totalImages - 1;
        container.style.transform = `translateX(-${currentIndex * 100}%)`;
        viewport.setAttribute('data-index', currentIndex);
        dots.forEach((dot, idx) => dot.classList.toggle('active', idx === currentIndex));
    }

    document.addEventListener("DOMContentLoaded", function() {
        const urlParams = new URLSearchParams(window.location.search);
        const gridContainer = document.getElementById('mainProductsGrid');
        const countBadge = document.getElementById('productCount');
        const viewTitle = document.getElementById('pageViewTitle');

        let displayGroup = [];

        if (urlParams.get('source') === 'image') {
            if (viewTitle) viewTitle.innerHTML = "Image Search Match Results";
            const stored = sessionStorage.getItem('image_search_results');
            if (stored) {
                const rawItems = JSON.parse(stored);
                let clientGrouped = {};
                
                rawItems.forEach(i => {
                    let sig = (i.visual_signature || i.name.toLowerCase().replace(/[^a-z0-9]/g, '_')).replace('ml', 'g');
                    if (!clientGrouped[sig]) {
                        clientGrouped[sig] = {
                            name: i.name, brand: i.brand, category: i.category || '',
                            prices: [], stores: [], images: [], signature: i.visual_signature || sig
                        };
                    }
                    if (!clientGrouped[sig].prices.includes(parseFloat(i.price))) clientGrouped[sig].prices.push(parseFloat(i.price));
                    if (!clientGrouped[sig].stores.includes(i.store)) clientGrouped[sig].stores.push(i.store);
                    let img = i.image || 'no_image.png';
                    if (!clientGrouped[sig].images.includes(img)) clientGrouped[sig].images.push(img);
                });

                displayGroup = Object.values(clientGrouped).map(g => ({
                    name: g.name, brand: g.brand, category: g.category,
                    min_price: Math.min(...g.prices), max_price: Math.max(...g.prices),
                    stores: g.stores.join(', '), images: g.images, signature: g.signature
                }));
            }
        } else {
            const serverData = <?php echo json_encode($grouped_display_rows); ?>;
            displayGroup = serverData.map(s => ({
                name: s.name, brand: s.brand, category: s.category,
                min_price: s.min_price, max_price: s.max_price,
                stores: s.stores, images: s.images, signature: s.signature
            }));
        }

        if (countBadge) countBadge.innerText = `${displayGroup.length} unique items isolated`;

        if (gridContainer && displayGroup.length > 0) {
            gridContainer.innerHTML = '';
            displayGroup.forEach(group => {
                const detailLink = `item_details.php?signature=${encodeURIComponent(group.signature || '')}&name=${encodeURIComponent(group.name)}`;
                const priceDisplay = (group.min_price === group.max_price) 
                    ? `RM ${group.min_price.toFixed(2)}` 
                    : `RM ${group.min_price.toFixed(2)} - RM ${group.max_price.toFixed(2)}`;

                let dotsHTML = '';
                if (group.images.length > 1) {
                    dotsHTML = `<div class="slider-arrow arrow-left" onclick="moveSlider(this, -1, event)"><i class="fa-solid fa-chevron-left"></i></div>
                                <div class="slider-arrow arrow-right" onclick="moveSlider(this, 1, event)"><i class="fa-solid fa-chevron-right"></i></div>
                                <div class="slider-dots">` + group.images.map((_, idx) => `<div class="dot ${idx === 0 ? 'active' : ''}"></div>`).join('') + `</div>`;
                }

                gridContainer.innerHTML += `
                    <div class="product-card">
                        <div>
                            <div class="slider-viewport" data-index="0" data-total="${group.images.length}">
                                ${dotsHTML}
                                <div class="slides-container">${group.images.map(img => `<div class="slide-frame"><img src="${img}" alt="Frame"></div>`).join('')}</div>
                            </div>
                            <a href="${detailLink}" style="text-decoration: none; color: inherit;">
                                <h4 class="prod-title">${group.name}</h4>
                                <div class="store-badge"><i class="fa-solid fa-store"></i> Store: ${formatStoreNameJS(group.stores)}</div>
                            </a>
                        </div>
                        <div class="price-range">Price: <span>${priceDisplay}</span></div>
                    </div>
                `;
            });
        }
    });
    </script>
</body>
</html>
