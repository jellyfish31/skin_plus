<?php
// scratch/run_php_test.php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/Product.php';
require_once __DIR__ . '/../model/HistoryLog.php';

Database::useLiveDatabase(true);
$db = Database::getMysqli();

$id_a = 1307490;
echo "Running Product::updateProductGroup for ID $id_a to new_test_sig_10g\n";
$result = Product::updateProductGroup($id_a, 'Skincare', 'Test Brand', 'new_test_sig_10g');

echo "Result returned: " . ($result ? 'true' : 'false') . "\n";
if ($db->error) {
    echo "MySQL Error: " . $db->error . "\n";
}
?>
