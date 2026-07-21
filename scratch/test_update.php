<?php
// scratch/test_update.php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/Product.php';
require_once __DIR__ . '/../model/HistoryLog.php';

// Force live or local DB connection based on what the admin controller uses
Database::useLiveDatabase(true);

$db = Database::getMysqli();
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

echo "Database connected successfully.\n";

// Fetch one product with a visual signature to test with
$res = $db->query("SELECT product_id, visual_signature, product_category, product_brand FROM products WHERE visual_signature IS NOT NULL AND visual_signature != '' LIMIT 1");
$row = $res->fetch_assoc();

if (!$row) {
    die("No products found with a visual signature to test.\n");
}

echo "Testing with Product ID: " . $row['product_id'] . "\n";
echo "Current Category: " . $row['product_category'] . "\n";
echo "Current Signature: " . $row['visual_signature'] . "\n";

$new_sig = $row['visual_signature'] . "_test";
$new_cat = $row['product_category'] . " Test";

echo "Attempting to update to Signature: '$new_sig', Category: '$new_cat'\n";

$result = Product::updateProductGroup($row['product_id'], $new_cat, $row['product_brand'], $new_sig);

if ($result) {
    echo "Update returned true!\n";
    // Fetch again to verify
    $verify_res = $db->query("SELECT visual_signature, product_category FROM products WHERE product_id = " . $row['product_id']);
    $verify_row = $verify_res->fetch_assoc();
    echo "Verified in DB - Signature: '" . $verify_row['visual_signature'] . "', Category: '" . $verify_row['product_category'] . "'\n";
    
    // Revert changes
    echo "Reverting changes...\n";
    Product::updateProductGroup($row['product_id'], $row['product_category'], $row['product_brand'], $row['visual_signature']);
    echo "Reverted.\n";
} else {
    echo "Update returned false!\n";
}
