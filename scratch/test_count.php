<?php
// scratch/test_count.php
require_once __DIR__ . '/../config/Database.php';

Database::useLiveDatabase(true);
$db = Database::getMysqli();

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

echo "Database connected successfully.\n";

$res = $db->query("SELECT COUNT(*) as total FROM products");
$row = $res->fetch_assoc();
echo "Total products in database: " . $row['total'] . "\n";

$res_sig = $db->query("SELECT COUNT(*) as total FROM products WHERE visual_signature IS NOT NULL AND visual_signature != ''");
$row_sig = $res_sig->fetch_assoc();
echo "Total products with visual signature: " . $row_sig['total'] . "\n";

$res_pending = $db->query("SELECT COUNT(*) as total FROM products WHERE visual_signature = 'PENDING_ADMIN'");
$row_pending = $res_pending->fetch_assoc();
echo "Total products PENDING_ADMIN: " . $row_pending['total'] . "\n";

// Show some sample signatures
$res_sample = $db->query("SELECT product_id, product_name, visual_signature FROM products LIMIT 5");
if ($res_sample) {
    echo "\nSample rows:\n";
    while ($row = $res_sample->fetch_assoc()) {
        echo "ID: " . $row['product_id'] . " | Name: " . $row['product_name'] . " | Sig: " . $row['visual_signature'] . "\n";
    }
}
