<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="/favicon.png?v=3">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SKIN+ | Smart Price Comparison</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #5D3EBC;
            --primary-hover: #4A2E9F;
            --bg-color: #FAFAFC;
            --card-bg: #FFFFFF;
            --text-dark: #2D2543;
            --text-muted: #756F86;
            --border-color: #E6E4ED;
            --btn-purple: #9D8FE1;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-dark);
            line-height: 1.6;
        }

        
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 8%;
            background: var(--card-bg);
            border-bottom: 1px solid var(--border-color);
        }

        .logo-area h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
            letter-spacing: -0.5px;
        }

        .logo-area p {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .admin-btn {
            background-color: var(--primary-color);
            color: white;
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: background 0.2s ease;
        }

        .admin-btn:hover {
            background-color: var(--primary-hover);
        }

        
        .hero {
            text-align: center;
            max-width: 800px;
            margin: 4rem auto 3rem auto;
            padding: 0 1rem;
        }

        .hero h2 {
            font-size: 2.6rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
        }

        .hero p {
            color: var(--text-muted);
            font-size: 1.1rem;
            margin-bottom: 2.5rem;
        }

        
        .partner-logos-row {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 2.5rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        .partner-logo-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 110px;
            height: 40px;
        }
        .partner-logo {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
            filter: grayscale(100%);
            opacity: 0.65;
            transition: all 0.3s ease;
        }
        .partner-logo-wrapper:hover .partner-logo {
            filter: grayscale(0%);
            opacity: 1;
            transform: scale(1.05);
        }

        
        .search-container {
            display: flex;
            max-width: 650px;
            margin: 0 auto 1.5rem auto;
            box-shadow: 0 4px 20px rgba(93, 62, 188, 0.06);
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .search-container input {
            flex: 1;
            padding: 1.1rem 1.5rem;
            border: none;
            font-size: 1rem;
            outline: none;
        }

        .search-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0 2.5rem;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .search-btn:hover {
            background-color: var(--primary-hover);
        }

        .image-search-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background-color: var(--card-bg);
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
            padding: 0.7rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            margin-bottom: 1.5rem;
            transition: all 0.2s ease;
        }

        .image-search-btn:hover {
            background-color: var(--primary-color);
            color: white;
        }

        
        .img-modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(45, 37, 67, 0.5);
            backdrop-filter: blur(5px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .img-modal-overlay.active {
            display: flex;
        }
        .img-modal-card {
            background: #FFFFFF;
            padding: 2rem;
            border-radius: 20px;
            width: 90%;
            max-width: 480px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
            position: relative;
        }
        .img-modal-card h4 {
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
        }
        .img-modal-card p {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 1.5rem;
        }
        .upload-dropzone {
            border: 2px dashed var(--btn-purple);
            padding: 2rem;
            border-radius: 12px;
            background: var(--bg-color);
            cursor: pointer;
            margin-bottom: 1rem;
            transition: background 0.2s;
        }
        .upload-dropzone:hover {
            background: #EAE7F2;
        }
        .upload-dropzone i {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        .camera-stream-wrapper {
            display: none;
            width: 100%;
            height: 280px;
            background: #000;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 1rem;
            position: relative;
        }
        #cameraStream {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .modal-buttons-panel {
            display: flex;
            justify-content: center;
            gap: 0.8rem;
        }
        .btn-modal-action {
            padding: 0.65rem 1.5rem;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            font-size: 0.9rem;
        }
        .btn-modal-cancel { background: #E6E4ED; color: var(--text-dark); }
        .btn-modal-primary { background: var(--primary-color); color: white; }
        .btn-modal-primary:hover { background: var(--primary-hover); }

        
        .scanner-loader {
            display: none;
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 1rem;
            font-weight: 600;
            color: var(--primary-color);
            z-index: 5;
        }
        .scanner-loader.active { display: flex; }
        .spinner-ring {
            width: 45px; height: 45px;
            border: 4px solid #E6E4ED;
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            animation: spinWheel 0.8s linear infinite;
        }
        @keyframes spinWheel { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        
        .section-title {
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--text-dark);
        }

        .grid-layout {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.2rem;
            max-width: 1000px;
            margin: 0 auto 4rem auto;
            padding: 0 2rem;
        }

        .grid-item {
            background: var(--btn-purple);
            color: white;
            text-align: center;
            padding: 1.2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.05rem;
            transition: transform 0.2s, background 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 55px;
        }

        .grid-item:hover {
            transform: translateY(-3px);
            background: var(--primary-color);
        }

        
        @media (max-width: 768px) {
            .navbar { padding: 1rem 4%; }
            .logo-area h1 { font-size: 1.5rem; }
            .grid-layout { grid-template-columns: repeat(2, 1fr); }
            .hero h2 { font-size: 2rem; }
            .partner-logos-row {
                gap: 1.2rem;
                margin-bottom: 1rem;
            }
            .partner-logo-wrapper {
                width: 90px;
                height: 30px;
            }
        }
        @media (max-width: 480px) {
            .grid-layout { grid-template-columns: 1fr; padding: 0 1rem; }
            .search-container { flex-direction: column; }
            .search-btn { padding: 1rem; }
        }

        footer {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--text-muted);
            font-size: 0.85rem;
            border-top: 1px solid var(--border-color);
            background: var(--card-bg);
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="logo-area">
            <h1>SKIN+</h1>
            <p>Smart Price Comparison</p>
        </div>
        <a href="admin_login.php" class="admin-btn">Admin</a>
    </nav>

    <main class="hero">
        <h2>Find the Best Skincare Prices</h2>
        <p>Compare prices across watsons, guardian, CARiNG PHARMACY and so much more</p>
        
        <form action="searchByBox_results.php" method="GET">
            <div class="search-container">
                <input type="text" name="query" placeholder="Search for Products (e.g., moisturizer)" required>
                <button type="submit" class="search-btn">Search</button>
            </div>
        </form>
        
        <button class="image-search-btn" onclick="openImageSearchModal()">
            <i class="fa-solid fa-camera"></i> Search By Image
        </button>
    </main>

    <div class="img-modal-overlay" id="imageSearchModal">
        <div class="img-modal-card">
            <div class="scanner-loader" id="modalLoader">
                <div class="spinner-ring"></div>
                <span id="loaderText">Analyzing skincare image details...</span>
            </div>

            <h4>Identify Product via Image</h4>
            <p>Upload a clear photo of your skincare item container label</p>
            
            <div class="upload-dropzone" id="dropzone" onclick="document.getElementById('fileInput').click()">
                <i class="fa-solid fa-cloud-arrow-up"></i>
                <h5>Click to browse image file</h5>
                <span style="font-size:0.8rem; color:var(--text-muted);">Supports PNG, JPG, JPEG</span>
                <input type="file" id="fileInput" accept="image/*" style="display:none;" onchange="handleFileSelection(event)">
            </div>

            <div class="camera-stream-wrapper" id="cameraWrapper">
                <video id="cameraStream" autoplay playsinline muted></video>
            </div>

            <div class="modal-buttons-panel">
                <button type="button" class="btn-modal-action btn-modal-cancel" onclick="closeImageSearchModal()">Cancel</button>
                <button type="button" class="btn-modal-action btn-modal-primary" id="cameraToggleBtn" onclick="toggleCameraPipeline()">Use Camera Instead</button>
                <button type="button" class="btn-modal-action btn-modal-primary" id="snapBtn" onclick="captureCameraSnapshot()" style="display:none;"><i class="fa-solid fa-circle-dot"></i> Capture Frame</button>
            </div>
        </div>
    </div>

    <h3 class="section-title">Shop by Category</h3>
    <section class="grid-layout">
        <a href="searchByBox_results.php?category=Cleanser" class="grid-item">Cleanser</a>
        <a href="searchByBox_results.php?category=Toner" class="grid-item">Toner</a>
        <a href="searchByBox_results.php?category=Serum" class="grid-item">Serum</a>
        <a href="searchByBox_results.php?category=Moisturizer" class="grid-item">Moisturizer</a>
        
        <a href="searchByBox_results.php?category=Sunscreen" class="grid-item">Sunscreen</a>
        <a href="searchByBox_results.php?category=Mask" class="grid-item">Mask</a>
        <a href="searchByBox_results.php?category=Micellar%20Water" class="grid-item">Micellar Water</a>
        <a href="searchByBox_results.php?category=Other" class="grid-item">Other</a>
    </section>

    <h3 class="section-title">Popular Brands</h3>
    <section class="grid-layout">
        <a href="searchByBox_results.php?brand=Skintific" class="grid-item">Skintific</a>
        <a href="searchByBox_results.php?brand=Cetaphil" class="grid-item">Cetaphil</a>
        <a href="searchByBox_results.php?brand=Garnier" class="grid-item">Garnier</a>
        <a href="searchByBox_results.php?brand=Cosrx" class="grid-item">Cosrx</a>
        
        <a href="searchByBox_results.php?brand=Medicube" class="grid-item">Medicube</a>
        <a href="searchByBox_results.php?brand=Glad2Glow" class="grid-item">Glad2Glow</a>
        <a href="searchByBox_results.php?brand=Eucerin" class="grid-item">Eucerin</a>
        <a href="searchByBox_results.php?brand=Aiken" class="grid-item">Aiken</a>
    </section>

    <footer>
        <div class="partner-logos-row">
            <div class="partner-logo-wrapper">
                <img src="guard.png" alt="guardian" class="partner-logo">
            </div>
            <div class="partner-logo-wrapper">
                <img src="caring.png" alt="CARiNG PHARMACY" class="partner-logo">
            </div>
            <div class="partner-logo-wrapper">
                <img src="watsons.png" alt="watsons" class="partner-logo">
            </div>
        </div>
        <p>Comparing prices from watsons, guardian, CARiNG PHARMACY and so much more</p>
        <p style="margin-top: 0.5rem; font-size: 0.75rem;">Data updated everyday via web scraping</p>
    </footer>

    <script>
        let liveVideoStream = null;
        const modalOverlay = document.getElementById('imageSearchModal');
        const modalLoader = document.getElementById('modalLoader');
        const dropzone = document.getElementById('dropzone');
        const cameraWrapper = document.getElementById('cameraWrapper');
        const videoElement = document.getElementById('cameraStream');
        const cameraToggleBtn = document.getElementById('cameraToggleBtn');
        const snapBtn = document.getElementById('snapBtn');

        function openImageSearchModal() {
            modalOverlay.classList.add('active');
        }

        function closeImageSearchModal() {
            killCameraPipeline();
            modalOverlay.classList.remove('active');
            modalLoader.classList.remove('active');
            dropzone.style.display = 'block';
            cameraWrapper.style.display = 'none';
            cameraToggleBtn.style.display = 'inline-block';
            snapBtn.style.display = 'none';
        }

        function handleFileSelection(event) {
            const file = event.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                sendImagePayloadToBackend(e.target.result);
            };
            reader.readAsDataURL(file);
        }

        async function toggleCameraPipeline() {
            try {
                liveVideoStream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: "environment", width: { ideal: 1280 }, height: { ideal: 720 } },
                    audio: false
                });
                videoElement.srcObject = liveVideoStream;
                
                dropzone.style.display = 'none';
                cameraWrapper.style.display = 'block';
                cameraToggleBtn.style.display = 'none';
                snapBtn.style.display = 'inline-block';
            } catch (err) {
                alert("Camera initialization failed. Please authorize permissions or pick an image file instead.");
                console.error("Camera layout trace: ", err);
            }
        }

        function killCameraPipeline() {
            if (liveVideoStream) {
                liveVideoStream.getTracks().forEach(track => track.stop());
                liveVideoStream = null;
            }
            videoElement.srcObject = null;
        }

        function captureCameraSnapshot() {
            const canvasBuffer = document.createElement('canvas');
            canvasBuffer.width = videoElement.videoWidth;
            canvasBuffer.height = videoElement.videoHeight;
            
            const context = canvasBuffer.getContext('2d');
            context.drawImage(videoElement, 0, 0, canvasBuffer.width, canvasBuffer.height);
            
            const compressedBase64 = canvasBuffer.toDataURL('image/jpeg', 0.85);
            killCameraPipeline();
            sendImagePayloadToBackend(compressedBase64);
        }


        function sendImagePayloadToBackend(base64String) {
            modalLoader.classList.add('active');
            
            fetch('image_search_processor.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'image_data=' + encodeURIComponent(base64String)
            })
            .then(res => res.json())
            .then(data => {
                if(data.success && data.product_keyword) {

                    if (data.products && data.products.length > 0) {
                        sessionStorage.setItem('image_search_results', JSON.stringify(data.products));
                    } else {
                        sessionStorage.removeItem('image_search_results');
                    }


                    window.location.href = 'searchByBox_results.php?query=' + encodeURIComponent(data.product_keyword) + '&source=image';
                } else {
                    alert("Analysis Failed: " + (data.error || "Unable to clearly capture label details. Please try again."));
                    closeImageSearchModal();
                }
            })
            .catch(err => {
                console.error(err);
                alert("Error establishing communication connection loop with core server.");
                closeImageSearchModal();
            });
        }
    </script>
</body>
</html>
