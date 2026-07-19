<?php
// sync_scraper.php
require_once __DIR__ . '/config/Database.php';

ini_set('display_errors', 0);
header('Content-Type: application/json');

// 1. Verify Secret Key/Token
$keys = file_exists(__DIR__ . '/config/Keys.php') ? include __DIR__ . '/config/Keys.php' : [];
$expected_token = $keys['sync_token'] ?? 'plusMin1SecretToken';

$headers = getallheaders();
$provided_token = $headers['X-Sync-Token'] ?? $_GET['token'] ?? '';

if ($provided_token !== $expected_token) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Forbidden: Invalid Sync Token']);
    exit();
}

// 2. Parse JSON Input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
    exit();
}

$action = $data['action'] ?? '';
$db = Database::getMysqli();

if ($action === 'clear') {
    // We no longer clear/truncate products to protect live database changes and admin edits!
    echo json_encode(['success' => true, 'message' => 'Tables cleared successfully (skipped to protect live database)']);
    exit();
}

if ($action === 'sync') {
    $db->query("SET FOREIGN_KEY_CHECKS = 0;");
    $results = [];

    try {
        $db->begin_transaction();
        
        // Sync products batch
        if (isset($data['products']) && is_array($data['products'])) {
            $stmt = $db->prepare("INSERT IGNORE INTO products (product_id, product_name, product_brand, product_price, product_store, product_category, product_image, visual_signature, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $count = 0;
            foreach ($data['products'] as $row) {
                $stmt->bind_param(
                    "issssssss",
                    $row['product_id'],
                    $row['product_name'],
                    $row['product_brand'],
                    $row['product_price'],
                    $row['product_store'],
                    $row['product_category'],
                    $row['product_image'],
                    $row['visual_signature'],
                    $row['created_at']
                );
                $stmt->execute();
                $count++;
            }
            $results['products'] = $count;
        }

        // Sync history_logs batch
        if (isset($data['history_logs']) && is_array($data['history_logs'])) {
            $stmt = $db->prepare("INSERT IGNORE INTO history_logs (log_id, admin_user, action_type, target_identifier, old_value, new_value, changed_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $count = 0;
            foreach ($data['history_logs'] as $row) {
                $stmt->bind_param(
                    "issssss",
                    $row['log_id'],
                    $row['admin_user'],
                    $row['action_type'],
                    $row['target_identifier'],
                    $row['old_value'],
                    $row['new_value'],
                    $row['changed_at']
                );
                $stmt->execute();
                $count++;
            }
            $results['history_logs'] = $count;
        }

        // Auto-assign known signatures on the live database
        $db->query("UPDATE products p
                    INNER JOIN (
                        SELECT product_name, visual_signature 
                        FROM products 
                        WHERE visual_signature IS NOT NULL 
                          AND visual_signature != '' 
                          AND visual_signature != 'PENDING_ADMIN'
                        GROUP BY product_name
                    ) historical ON p.product_name = historical.product_name
                    SET p.visual_signature = historical.visual_signature
                    WHERE p.visual_signature IS NULL");

        // Mark remaining unassigned signatures as PENDING_ADMIN
        $db->query("UPDATE products SET visual_signature = 'PENDING_ADMIN' WHERE visual_signature IS NULL");

        $db->commit();
        $db->query("SET FOREIGN_KEY_CHECKS = 1;");
        echo json_encode(['success' => true, 'synced' => $results]);

    } catch (Exception $e) {
        $db->rollback();
        $db->query("SET FOREIGN_KEY_CHECKS = 1;");
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Sync batch failed: ' . $e->getMessage()]);
    }
    exit();
}

http_response_code(400);
echo json_encode(['success' => false, 'error' => 'Invalid action']);
