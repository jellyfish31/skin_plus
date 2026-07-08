<?php
// controllers/AdminController.php

require_once __DIR__ . '/../model/Admin.php';
require_once __DIR__ . '/../model/Product.php';
require_once __DIR__ . '/../model/Notification.php';
require_once __DIR__ . '/../model/HistoryLog.php';

class AdminController {
    /**
     * Handles admin login page and processing credentials.
     */
    public function login() {
        Admin::initSession();
        
        // Auto-redirect if already logged in
        if (Admin::isLoggedIn()) {
            header("Location: admin_crud.php");
            exit();
        }

        $error = "";
        if (isset($_POST['login'])) {
            $username = trim($_POST['username']);
            $password = trim($_POST['password']);

            if (Admin::validateLogin($username, $password)) {
                header("Location: admin_crud.php");
                exit();
            } else {
                $error = "Invalid username or password.";
            }
        }

        include __DIR__ . '/../views/admin_login.php';
    }

    /**
     * Handles admin logout.
     */
    public function logout() {
        Admin::logout();
    }

    /**
     * Admin dashboard product CRUD.
     */
    public function crud() {
        Admin::requireAuth();

        // ─── PROCESS MUTATION ACTIONS ───
        if (isset($_POST['action'])) {
            if ($_POST['action'] === 'delete') {
                $id = intval($_POST['product_id']);
                Product::deleteProduct($id);
            } elseif ($_POST['action'] === 'update') {
                $id = intval(key($_POST['store_data'])); // target id representing the group
                $category = $_POST['product_category'];
                $brand = $_POST['product_brand'];
                $storeData = $_POST['store_data'];

                Product::updateProductGroup($id, $category, $brand, $storeData);
            }
        }

        // Fetch filter params
        $search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
        $date_options = Product::getScrapeDates();
        $selected_date = isset($_GET['scrape_date']) ? trim($_GET['scrape_date']) : (!empty($date_options) ? $date_options[0] : '');

        // Pagination setup
        $limit = 20;
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $limit;

        $results = Product::getAdminProducts($search_query, $selected_date, $offset, $limit);
        $items = $results['items'];
        $total_groups = $results['total_groups'];
        $total_pages = ceil($total_groups / $limit);

        // Fetch portal statistics and visitor analytics
        $db = Database::getMysqli();
        $total_products_count = $db->query("SELECT COUNT(DISTINCT visual_signature) as total FROM products WHERE visual_signature IS NOT NULL AND visual_signature != 'PENDING_ADMIN' AND visual_signature != ''")->fetch_assoc()['total'];
        $total_categories = $db->query("SELECT COUNT(DISTINCT product_category) as total FROM products")->fetch_assoc()['total'];
        $total_brands = $db->query("SELECT COUNT(DISTINCT product_brand) as total FROM products")->fetch_assoc()['total'];
        $total_visits = $db->query("SELECT COUNT(*) as total FROM visitors")->fetch_assoc()['total'];
        $analytics_result = $db->query("SELECT visitor_id, time_session FROM visitors ORDER BY time_session DESC LIMIT 250");
        $alert_count = Notification::getPendingDiscoveriesCount();

        include __DIR__ . '/../views/admin_crud.php';
    }

    /**
     * Admin discoveries desk for unassigned signatures.
     */
    public function notifications() {
        Admin::requireAuth();
        $csv_filename = 'cleaned_signatures.csv';
        $success_msg = "";

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_sig'])) {
            $target_name = trim($_POST['target_name']);
            $custom_signature = strtolower(trim($_POST['custom_signature']));

            if (!empty($custom_signature)) {
                Product::assignSignature($target_name, $custom_signature);

                // Sync and append updates to local spreadsheet records
                if (file_exists($csv_filename)) {
                    $rows = [];
                    if (($handle = fopen($csv_filename, "r")) !== FALSE) {
                        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                            if (isset($data[1]) && trim($data[1]) === $target_name) {
                                $data[8] = $custom_signature; 
                            }
                            $rows[] = $data;
                        }
                        fclose($handle);
                    }

                    $write_handle = fopen($csv_filename, "w");
                    foreach ($rows as $row) { fputcsv($write_handle, $row); }
                    fclose($write_handle);
                }
                $success_msg = "✅ Successfully linked <strong>" . htmlspecialchars($custom_signature) . "</strong> across DB & CSV!";
            }
        }

        // Fetch pending discoveries
        $pending_discoveries = Notification::getPendingDiscoveries();
        $pending_count = count($pending_discoveries);

        include __DIR__ . '/../views/admin_notifications.php';
    }

    /**
     * Scrape sync automation pipeline.
     */
    public function syncPipeline() {
        // Keeps time limit extended
        set_time_limit(600);
        $csv_filename = 'cleaned_signatures.csv';

        // 1. Auto-assign known signatures
        Product::autoAssignKnownSignatures();

        // 2. Discover new items
        $unassigned = Product::getUnassignedProducts();
        $new_discoveries_count = 0;

        if (!empty($unassigned)) {
            // Set status to PENDING_ADMIN temporarily
            Product::markUnassignedAsPending();

            // Append to CSV
            if (file_exists($csv_filename)) {
                $fp = fopen($csv_filename, 'a');
                if ($fp !== FALSE) {
                    foreach ($unassigned as $row) {
                        $new_csv_row = [
                            'NEW_SCRAPE', 
                            $row['product_name'], 
                            $row['product_brand'], 
                            $row['product_category'], 
                            $row['product_image'], 
                            $row['product_price'], 
                            $row['product_store'], 
                            date('Y-m-d H:i:s'), 
                            'PENDING_ADMIN'
                        ];
                        fputcsv($fp, $new_csv_row);
                        $new_discoveries_count++;
                    }
                    fclose($fp);
                }
            }
        }

        // Output formatting matched with presentation layer
        include __DIR__ . '/../views/sync_pipeline.php';
    }
}
