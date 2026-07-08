<?php
// controllers/ProductController.php

require_once __DIR__ . '/../model/Product.php';

class ProductController {
    public function __construct() {
        $this->trackVisitor();
    }

    /**
     * Tracks unique visitor sessions anonymously.
     */
    private function trackVisitor() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['tracked_visit'])) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $visitor_id = 'visitor_' . substr(md5($ip . $ua . time() . uniqid()), 0, 16);

            $db = Database::getMysqli();
            $stmt = $db->prepare("INSERT INTO visitors (visitor_id) VALUES (?)");
            if ($stmt) {
                $stmt->bind_param("s", $visitor_id);
                $stmt->execute();
                $stmt->close();
            }

            $_SESSION['tracked_visit'] = $visitor_id;
        }
    }

    /**
     * Renders the home page.
     */
    public function index() {
        // Loads index.php view
        include __DIR__ . '/../views/home.php';
    }

    /**
     * Renders product search results.
     */
    public function search() {
        $category_filter = isset($_GET['category']) ? $_GET['category'] : '';
        $brand_filter = isset($_GET['brand']) ? $_GET['brand'] : '';
        $search_query = isset($_GET['query']) ? trim($_GET['query']) : '';
        $source_route = isset($_GET['source']) ? $_GET['source'] : '';

        // Fetch products through the Model search engine
        $search_data = Product::searchProducts($category_filter, $brand_filter, $search_query, $source_route);
        
        $view_title = $search_data['view_title'];
        $grouped_display_rows = $search_data['products'];

        include __DIR__ . '/../views/search_results.php';
    }

    /**
     * Renders detailed product information and stores pricing comparison.
     */
    public function details() {
        $product_name_raw = isset($_GET['name']) ? trim($_GET['name']) : '';
        $signature_raw = isset($_GET['signature']) ? trim($_GET['signature']) : '';

        if (empty($product_name_raw)) {
            header("Location: searchByBox_results.php");
            exit();
        }

        // Fetch using Model
        $product_info = Product::getProductProfile($product_name_raw, $signature_raw);
        if (!$product_info) {
            die("❌ Product profile not found in database logs.");
        }

        $current_signature = $product_info['normalized_sig'];
        $store_prices = Product::getStorePrices($current_signature);
        $total_stores = count($store_prices);

        // Calculate variance stats
        $price_sum = 0;
        $average_price = 0;
        if ($total_stores > 0) {
            foreach ($store_prices as $item) {
                $price_sum += $item['product_price'];
            }
            $average_price = $price_sum / $total_stores;
        }

        include __DIR__ . '/../views/item_details.php';
    }

    /**
     * Renders the price history details.
     */
    public function priceHistory() {
        $product_name_raw = isset($_GET['name']) ? trim($_GET['name']) : '';
        $signature_raw = isset($_GET['signature']) ? trim($_GET['signature']) : '';

        if (empty($product_name_raw)) {
            die("❌ Missing item context parameters.");
        }

        // Fetch profile
        $product_info = Product::getProductProfile($product_name_raw, $signature_raw);
        if (!$product_info) {
            die("❌ Reference item context missing.");
        }

        $current_signature = $product_info['normalized_sig'];
        
        // Fetch price history data points
        $history_data = Product::getPriceHistory($current_signature);
        $ordered_labels = $history_data['labels'];
        $stores = $history_data['stores'];
        $raw_history_rows = $history_data['history'];

        // Perform analysis calculations
        $store_analysis_metrics = [];
        $store_raw_prices = [];
        $absolute_best_store = '';
        $absolute_best_price = PHP_FLOAT_MAX;
        $absolute_best_date = '';

        foreach ($stores as $store) {
            $highest_price = 0;
            $lowest_price = PHP_FLOAT_MAX;
            $lowest_price_date = '';
            $last_known_price = null;
            
            foreach ($raw_history_rows as $row) {
                if ($row['product_store'] === $store) {
                    $current_val = (float)$row['product_price'];
                    $last_known_price = $current_val;
                    
                    if ($current_val > $highest_price) {
                        $highest_price = $current_val;
                    }
                    if ($current_val < $lowest_price) {
                        $lowest_price = $current_val;
                        $datetime_obj = new DateTime($row['created_at']);
                        $lowest_price_date = $datetime_obj->format('j F Y');
                    }
                }
            }

            // Map store tracking details
            $store_analysis_metrics[$store] = [
                'highest' => $highest_price,
                'lowest' => $lowest_price,
                'lowest_date' => $lowest_price_date,
                'current' => $last_known_price
            ];

            if ($lowest_price < $absolute_best_price) {
                $absolute_best_price = $lowest_price;
                $absolute_best_store = $store;
                $absolute_best_date = $lowest_price_date;
            }
        }

        include __DIR__ . '/../views/price_history.php';
    }
}
