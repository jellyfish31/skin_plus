<?php
// model/Product.php

require_once __DIR__ . '/../config/Database.php';

class Product {

    /**
     * Search products based on filter options, with spellcheck fallback if search query returns no results.
     */
    public static function searchProducts($category = '', $brand = '', $query = '', $source = '') {
        $db = Database::getMysqli();

        // 🚀 CORE FIXED QUERY: Locks down calculations to ONLY rows matching the latest live scrape date, skipping historical dummies
        // 🚀 UPGRADED CORE QUERY: Locks down the latest scrape date for EACH unique store individually
        $base_sql = "SELECT MAX(p.product_name) as display_name, 
                            MAX(p.product_brand) as display_brand, 
                            MAX(p.product_category) as display_category, 
                            LOWER(REPLACE(p.visual_signature, 'ml', 'g')) as normalized_signature,
                            MIN(p.product_price) as min_price, 
                            MAX(p.product_price) as max_price,
                            GROUP_CONCAT(DISTINCT p.product_store SEPARATOR ', ') as combined_stores,
                            GROUP_CONCAT(DISTINCT p.product_image SEPARATOR '|') as combined_images
                     FROM products p
                     INNER JOIN (
                         /* Locates the newest timestamp for EACH signature per store, ignoring historical dummies */
                         SELECT LOWER(REPLACE(b.visual_signature, 'ml', 'g')) as inner_sig, 
                                b.product_store as inner_store,
                                MAX(b.created_at) as max_date
                         FROM products b 
                         WHERE b.visual_signature IS NOT NULL 
                           AND b.visual_signature != 'PENDING_ADMIN' 
                           AND b.visual_signature != ''
                           AND b.product_brand != 'Historical Brand' 
                         GROUP BY inner_sig, inner_store
                     ) latest_batch ON LOWER(REPLACE(p.visual_signature, 'ml', 'g')) = latest_batch.inner_sig 
                                   AND p.product_store = latest_batch.inner_store
                                   AND p.created_at = latest_batch.max_date
                     WHERE LOWER(p.product_store) NOT LIKE '%shopee%' 
                       AND LOWER(p.product_store) NOT LIKE '%lazada%'
                       AND LOWER(p.product_name) NOT LIKE '%baby%'";

        $sql_clauses = "";
        $view_title = "All Products";

        if (!empty($category)) {
            if (strtolower($category) === 'other') {
                $sql_clauses .= " AND LOWER(p.product_category) NOT IN ('cleanser', 'toner', 'serum', 'moisturizer', 'sunscreen', 'mask', 'micellar water')";
                $view_title = "Other Skincare Products";
            } else {
                $escaped_cat = $db->real_escape_string(strtolower($category));
                $sql_clauses .= " AND LOWER(p.product_category) LIKE '%$escaped_cat%'";
                $view_title = ucfirst($category);
            }
        } elseif (!empty($brand)) {
            $escaped_brand = $db->real_escape_string(strtolower($brand));
            $sql_clauses .= " AND LOWER(p.product_brand) = '$escaped_brand'";
            $view_title = ucfirst($brand);
        } elseif (!empty($query) && $source !== 'image') {
            $escaped_search = $db->real_escape_string(strtolower($query));
            $sql_clauses .= " AND (LOWER(p.product_name) LIKE '%$escaped_search%' OR LOWER(p.product_brand) LIKE '%$escaped_search%')";
            $view_title = "Search Results for '" . htmlspecialchars($query) . "'";
        }

        $final_sql = $base_sql . $sql_clauses . " GROUP BY normalized_signature ORDER BY display_name ASC";
        $result = $db->query($final_sql);

        // Spellcheck Fallback engine loop
        if ((!$result || $result->num_rows === 0) && !empty($query) && $source !== 'image') {
            $valid_brands = ['skintific', 'cetaphil', 'garnier', 'cosrx', 'medicube', 'glad2glow', 'eucerin', 'aiken'];
            $category_map = [
                'mask' => 'Mask', 'clay' => 'Mask', 'sheet' => 'Mask', 'sunscreen' => 'Sunscreen',
                'micellar' => 'Micellar Water', 'cleanser' => 'Cleanser', 'wash' => 'Cleanser',
                'toner' => 'Toner', 'serum' => 'Serum', 'moisturizer' => 'Moisturizer'
            ];

            $search_words = explode(' ', strtolower($query));
            $corrected_brand = '';
            $corrected_category = '';

            foreach ($search_words as $word) {
                $word = trim($word);
                if (strlen($word) < 3) continue;
                foreach ($valid_brands as $b) {
                    if (levenshtein($word, $b) <= 2 || $word === $b) {
                        $corrected_brand = $b;
                        break;
                    }
                }
                foreach ($category_map as $key => $formal) {
                    if (levenshtein($word, $key) <= 1 || $word === $key) {
                        $corrected_category = $formal;
                        break;
                    }
                }
            }

            if (!empty($corrected_brand) || !empty($corrected_category)) {
                $sql_fallback_clauses = "";
                if (!empty($corrected_brand)) {
                    $escaped_cb = $db->real_escape_string($corrected_brand);
                    $sql_fallback_clauses .= " AND LOWER(p.product_brand) = '$escaped_cb'";
                }
                if (!empty($corrected_category)) {
                    $escaped_cc = $db->real_escape_string(strtolower($corrected_category));
                    $sql_fallback_clauses .= " AND LOWER(p.product_category) LIKE '%$escaped_cc%'";
                }

                $final_sql = $base_sql . $sql_fallback_clauses . " GROUP BY normalized_signature ORDER BY display_name ASC";
                $result = $db->query($final_sql);
                $display_correction = trim(($corrected_brand ? ucfirst($corrected_brand) : '') . ' ' . ($corrected_category ? ucfirst($corrected_category) : ''));
                $view_title = "Showing results for fuzzy match: '<strong>" . htmlspecialchars($display_correction) . "</strong>'";
            }
        }

        $grouped_display_rows = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $img_list = array_filter(explode('|', $row['combined_images']));
                if (empty($img_list)) {
                    $img_list = ['no_image.png'];
                }

                $grouped_display_rows[] = [
                    'name' => $row['display_name'],
                    'brand' => $row['display_brand'],
                    'category' => $row['display_category'],
                    'min_price' => (float)$row['min_price'],
                    'max_price' => (float)$row['max_price'],
                    'stores' => $row['combined_stores'],
                    'images' => array_values($img_list),
                    'signature' => $row['normalized_signature']
                ];
            }
        }

        return [
            'view_title' => $view_title,
            'products' => $grouped_display_rows
        ];
    }

    /**
     * Gets base profile info of a product by signature or name.
     */
    public static function getProductProfile(string $name, string $signature) {
        $db = Database::getMysqli();
        $escaped_name = $db->real_escape_string($name);
        $escaped_sig = $db->real_escape_string($signature);

        if (!empty($escaped_sig)) {
            $sql = "SELECT product_name, product_brand, product_category, product_image, LOWER(REPLACE(visual_signature, 'ml', 'g')) as normalized_sig 
                    FROM products 
                    WHERE LOWER(REPLACE(visual_signature, 'ml', 'g')) = LOWER(REPLACE('$escaped_sig', 'ml', 'g')) 
                    ORDER BY created_at DESC LIMIT 1";
        } else {
            $sql = "SELECT product_name, product_brand, product_category, product_image, LOWER(REPLACE(visual_signature, 'ml', 'g')) as normalized_sig 
                    FROM products 
                    WHERE product_name = '$escaped_name' 
                    ORDER BY created_at DESC LIMIT 1";
        }

        $result = $db->query($sql);
        return $result ? $result->fetch_assoc() : null;
    }

    /**
     * Gets store prices for a normalized signature.
     */
    public static function getStorePrices(string $signature) {
        $db = Database::getMysqli();
        $escaped_sig = $db->real_escape_string($signature);
        $store_prices = [];

        if (!empty($escaped_sig)) {
            $sql = "SELECT p.product_store, p.product_price, p.product_name 
                    FROM products p
                    INNER JOIN (
                        SELECT b.product_store, MAX(b.product_id) as latest_id
                        FROM products b
                        WHERE LOWER(REPLACE(b.visual_signature, 'ml', 'g')) = '$escaped_sig'
                        GROUP BY b.product_store
                    ) latest ON p.product_id = latest.latest_id
                    WHERE LOWER(REPLACE(p.visual_signature, 'ml', 'g')) = '$escaped_sig'
                      AND LOWER(p.product_store) NOT LIKE '%shopee%' 
                      AND LOWER(p.product_store) NOT LIKE '%lazada%'";

            $result = $db->query($sql);
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $store_key = $row['product_store'];
                    $store_prices[$store_key] = [
                        'product_store' => $row['product_store'],
                        'product_price' => (float)$row['product_price']
                    ];
                }
            }
        }

        $store_prices = array_values($store_prices);
        usort($store_prices, function($a, $b) {
            return $a['product_price'] <=> $b['product_price'];
        });

        return $store_prices;
    }

    /**
     * Fetch historical timelines for line charts.
     */
    public static function getPriceHistory(string $signature) {
        $db = Database::getMysqli();
        $escaped_sig = $db->real_escape_string($signature);
        $timeline_js_labels = [];
        $raw_history_rows = [];
        $stores_found = [];

        if (!empty($escaped_sig)) {
            $sql = "SELECT product_name, LOWER(TRIM(product_store)) as product_store, product_price, 
                           created_at,
                           DATE_FORMAT(created_at, '%d') as day_label,
                           DATE_FORMAT(created_at, '%M %Y') as month_label,
                           DATE(created_at) as raw_date
                    FROM (
                        SELECT product_name, product_store, product_price, created_at, visual_signature, product_brand
                        FROM products
                        UNION ALL
                        SELECT product_name, product_store, product_price, created_at, visual_signature, product_brand
                        FROM data_history
                    ) combined
                    WHERE LOWER(REPLACE(visual_signature, 'ml', 'g')) = '$escaped_sig'
                      AND LOWER(product_store) NOT LIKE '%shopee%'
                      AND LOWER(product_store) NOT LIKE '%lazada%'
                      AND product_brand != 'Historical Brand'
                    ORDER BY DATE(created_at) ASC, created_at ASC";

            $result = $db->query($sql);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $unique_key = $row['raw_date'];
                    if (!isset($timeline_js_labels[$unique_key])) {
                        $timeline_js_labels[$unique_key] = [$row['day_label'], $row['month_label']];
                    }
                    $raw_history_rows[] = $row;
                    $stores_found[$row['product_store']] = true;
                }
            }
        }

        return [
            'labels' => array_values($timeline_js_labels),
            'stores' => array_keys($stores_found),
            'history' => $raw_history_rows
        ];
    }

    /**
     * Delete product by ID.
     */
    public static function deleteProduct(int $id) {
        $db = Database::getMysqli();
        $id = intval($id);

        $snap = $db->query("SELECT product_name, product_store, product_price FROM products WHERE product_id = $id")->fetch_assoc();
        if ($snap) {
            $log_old = json_encode($snap);
            HistoryLog::addLog('DELETE_ROW', $snap['product_name'], $log_old, 'DELETED');
        }

        return $db->query("DELETE FROM products WHERE product_id = $id");
    }

    /**
     * Updates properties for an entire visual signature group and individual store prices.
     */
    public static function updateProductGroup(int $id, string $category, string $brand, array $storeData) {
        $db = Database::getMysqli();
        $id = intval($id);
        $escaped_category = $db->real_escape_string($category);
        $escaped_brand = $db->real_escape_string($brand);

        if (is_array($storeData) && !empty($storeData)) {
            $first_item = reset($storeData);
            $master_name = $db->real_escape_string($first_item['name']);

            // Isolate visual_signature map pointer for mass update execution
            $sig_res = $db->query("SELECT visual_signature FROM products WHERE product_id = $id");
            $sig_row = $sig_res->fetch_assoc();

            if ($sig_row && !empty($sig_row['visual_signature'])) {
                $target_sig = $db->real_escape_string($sig_row['visual_signature']);

                // Fetch previous data snapshot for logging
                $prev = $db->query("SELECT product_name, product_category, product_brand FROM products WHERE product_id = $id")->fetch_assoc();
                if ($prev) {
                    $old_json = json_encode($prev);
                    $new_json = json_encode([
                        'product_name' => $master_name,
                        'product_category' => $category,
                        'product_brand' => $brand
                    ]);
                    HistoryLog::addLog('UPDATE_GROUP', $master_name, $old_json, $new_json);
                }

                // Global group update
                $db->query("UPDATE products SET 
                                product_name = '$master_name',
                                product_category = '$escaped_category',
                                product_brand = '$escaped_brand'
                              WHERE visual_signature = '$target_sig'");
            }

            // Update individual prices
            foreach ($storeData as $sub_id => $data) {
                $individual_id = intval($sub_id);
                $individual_price = floatval($data['price']);
                $db->query("UPDATE products SET product_price = $individual_price WHERE product_id = $individual_id");
            }
            return true;
        }
        return false;
    }

    /**
     * Get distinct scrape dates.
     */
    public static function getScrapeDates() {
        $db = Database::getMysqli();
        $res = $db->query("SELECT DISTINCT DATE(created_at) as scrape_date FROM products ORDER BY scrape_date DESC");
        $dates = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $dates[] = $row['scrape_date'];
            }
        }
        return $dates;
    }

    /**
     * Get products for admin CRUD panel.
     */
    public static function getAdminProducts($search = '', $date = '', $offset = 0, $limit = 20) {
        $db = Database::getMysqli();
        $escaped_search = $db->real_escape_string(strtolower($search));
        $escaped_date = $db->real_escape_string($date);

        $where_clause = " WHERE LOWER(p.product_store) NOT LIKE '%shopee%' AND LOWER(p.product_store) NOT LIKE '%lazada%' ";
        if (!empty($escaped_date)) {
            $where_clause .= " AND DATE(p.created_at) = '$escaped_date' ";
        }
        if (!empty($escaped_search)) {
            $where_clause .= " AND (LOWER(p.product_name) LIKE '%$escaped_search%' OR LOWER(p.product_brand) LIKE '%$escaped_search%' OR LOWER(p.visual_signature) LIKE '%$escaped_search%') ";
        }

        // Count total unique groups for pagination
        $count_sql = "SELECT COUNT(DISTINCT LOWER(REPLACE(p.visual_signature, 'ml', 'g'))) as total_groups
                      FROM products p 
                      $where_clause 
                        AND p.visual_signature IS NOT NULL 
                        AND p.visual_signature != '' 
                        AND p.visual_signature != 'PENDING_ADMIN'
                        AND p.product_brand != 'Historical Brand'";
        $count_res = $db->query($count_sql);
        $total_groups = 0;
        if ($count_res) {
            $c_row = $count_res->fetch_assoc();
            $total_groups = (int)$c_row['total_groups'];
        }

        // Fetch grouped products with pagination
        $sql = "SELECT LOWER(REPLACE(p.visual_signature, 'ml', 'g')) as normalized_signature,
                       MAX(p.product_id) as product_id,
                       MAX(p.product_name) as master_product_name,
                       MAX(p.product_brand) as master_product_brand,
                       MAX(p.product_category) as master_product_category,
                       DATE(MAX(p.created_at)) as scrape_date,
                       GROUP_CONCAT(CONCAT(p.product_id, ':::DataSplitKey:::', p.product_store, ':::DataSplitKey:::', p.product_name, ':::DataSplitKey:::', p.product_price) SEPARATOR '|||') as store_nodes_string
                FROM products p
                $where_clause
                  AND p.visual_signature IS NOT NULL 
                  AND p.visual_signature != '' 
                  AND p.visual_signature != 'PENDING_ADMIN'
                  AND p.product_brand != 'Historical Brand'
                GROUP BY normalized_signature
                ORDER BY master_product_name ASC
                LIMIT $limit OFFSET $offset";

        $result = $db->query($sql);
        $grouped_products = [];

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $nodes_array = explode('|||', $row['store_nodes_string']);
                $prices = [];
                foreach ($nodes_array as $node) {
                    $chunks = explode(':::DataSplitKey:::', $node);
                    if (isset($chunks[3])) { $prices[] = (float)$chunks[3]; }
                }

                $grouped_products[] = [
                    'product_id' => $row['product_id'],
                    'product_name' => $row['master_product_name'],
                    'product_brand' => $row['master_product_brand'],
                    'product_category' => $row['master_product_category'],
                    'scrape_date' => $row['scrape_date'],
                    'prices' => $prices,
                    'signature' => $row['normalized_signature'],
                    'store_nodes' => $nodes_array
                ];
            }
        }

        return [
            'items' => $grouped_products,
            'total_groups' => $total_groups
        ];
    }

    /**
     * Auto-assign known signatures during scraper post-processing.
     */
    public static function autoAssignKnownSignatures() {
        $db = Database::getMysqli();
        $sql = "UPDATE products p
                INNER JOIN (
                    SELECT product_name, visual_signature 
                    FROM products 
                    WHERE visual_signature IS NOT NULL 
                      AND visual_signature != '' 
                      AND visual_signature != 'PENDING_ADMIN'
                    GROUP BY product_name
                ) historical ON p.product_name = historical.product_name
                SET p.visual_signature = historical.visual_signature
                WHERE p.visual_signature IS NULL";
        return $db->query($sql);
    }

    /**
     * Retrieves products with unassigned signatures (NULL).
     */
    public static function getUnassignedProducts() {
        $db = Database::getMysqli();
        $sql = "SELECT product_name, product_brand, product_category, product_image, product_price, product_store 
                FROM products 
                WHERE visual_signature IS NULL";
        $result = $db->query($sql);
        $items = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
        }
        return $items;
    }

    /**
     * Updates unassigned signatures to 'PENDING_ADMIN'.
     */
    public static function markUnassignedAsPending() {
        $db = Database::getMysqli();
        return $db->query("UPDATE products SET visual_signature = 'PENDING_ADMIN' WHERE visual_signature IS NULL");
    }

    /**
     * Assigns a signature to a specific product name.
     */
    public static function assignSignature(string $product_name, string $signature) {
        $db = Database::getMysqli();
        $escaped_name = $db->real_escape_string($product_name);
        $escaped_sig = $db->real_escape_string($signature);

        $db->query("UPDATE products SET visual_signature = '$escaped_sig' WHERE product_name = '$escaped_name'");
        HistoryLog::addLog('ASSIGN_SIGNATURE', $escaped_name, 'PENDING_ADMIN', $escaped_sig);
        return true;
    }

    /**
     * Fetches candidates for Vision API matching.
     */
    public static function fetchVisionCandidates(string $brand, string $category, string $size_query) {
        $db = Database::getMysqli();
        $escaped_brand = $db->real_escape_string($brand);
        $escaped_category = $db->real_escape_string($category);

        $sql = "SELECT DISTINCT visual_signature, product_image FROM products 
                WHERE product_brand = '$escaped_brand' 
                  AND product_category = '$escaped_category' 
                  AND visual_signature IS NOT NULL 
                  AND visual_signature != 'PENDING_ADMIN' 
                  AND visual_signature != '' 
                  $size_query LIMIT 5";
        $result = $db->query($sql);
        $candidates = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $candidates[] = [
                    'signature' => $row['visual_signature'],
                    'image' => $row['product_image']
                ];
            }
        }
        return $candidates;
    }

    /**
     * Updates signature and logs AI auto match.
     */
    public static function updateAiMatchedSignature(string $product_name, string $signature) {
        $db = Database::getMysqli();
        $escaped_sig = $db->real_escape_string($signature);
        $escaped_name = $db->real_escape_string($product_name);
        
        $db->query("UPDATE products SET visual_signature = '$escaped_sig' WHERE product_name = '$escaped_name'");
        HistoryLog::addLog('AI_AUTO_MATCH', $escaped_name, 'PENDING_ADMIN', $escaped_sig);
    }

    /**
     * Fetches details of all products for similarity matching.
     */
    public static function fetchAllProductsForMatching() {
        $db = Database::getMysqli();
        $sql = "SELECT product_name, visual_signature, product_price, product_store, product_brand, product_category, product_image 
                FROM products 
                WHERE visual_signature IS NOT NULL 
                  AND visual_signature != 'PENDING_ADMIN' 
                  AND visual_signature != ''";
        $result = $db->query($sql);
        $products = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }
        return $products;
    }



    /**
     * Gets the latest products for a brand, filtering out Shopee/Lazada and ignoring old history.
     */
    public static function getLatestProductsByBrand(string $brand) {
        $db = Database::getMysqli();
        $escaped_brand = $db->real_escape_string(strtolower($brand));
        
        $sql = "SELECT p.product_id, p.product_name, p.product_brand, p.product_category, p.product_price, p.product_store, p.product_image, p.visual_signature 
                FROM products p
                WHERE p.product_id = (
                    SELECT b.product_id 
                    FROM products b 
                    WHERE b.product_name = p.product_name 
                      AND b.product_store = p.product_store 
                    ORDER BY b.created_at DESC, b.product_id DESC 
                    LIMIT 1
                )
                AND LOWER(p.product_brand) = ?
                AND LOWER(p.product_store) NOT LIKE '%shopee%' 
                AND LOWER(p.product_store) NOT LIKE '%lazada%'";
                
        $stmt = $db->prepare($sql);
        $stmt->bind_param("s", $escaped_brand);
        $stmt->execute();
        $res = $stmt->get_result();
        
        $items = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $items[] = $row;
            }
        }
        return $items;
    }

    /**
     * Retrieves existing signature suggestions for a brand/category combination.
     */
    public static function getSuggestionsForProduct(string $brand, string $category) {
        $db = Database::getMysqli();
        $escaped_brand = $db->real_escape_string($brand);
        $escaped_category = $db->real_escape_string($category);

        $sql = "SELECT visual_signature, MIN(product_image) as sample_image 
                FROM products 
                WHERE product_brand = '$escaped_brand' 
                  AND product_category = '$escaped_category' 
                  AND visual_signature IS NOT NULL 
                  AND visual_signature != 'PENDING_ADMIN' 
                  AND visual_signature != ''
                GROUP BY visual_signature
                ORDER BY product_id DESC LIMIT 3";

        $result = $db->query($sql);
        $suggestions = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $suggestions[] = $row;
            }
        }
        return $suggestions;
    }
}
