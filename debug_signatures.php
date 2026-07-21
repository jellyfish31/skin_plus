<?php
// debug_signatures.php
require_once __DIR__ . '/config/Database.php';

Database::useLiveDatabase(true);
$db = Database::getMysqli();

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

echo "<pre>";
echo "=== DATABASE CONNECTION DETAILS ===\n";
echo "Host: " . $db->host_info . "\n";
echo "Server info: " . $db->server_info . "\n\n";

echo "=== RECENT HISTORY LOG ENTRIES ===\n";
$res_log = $db->query("SELECT * FROM history_logs ORDER BY log_id DESC LIMIT 5");
if ($res_log) {
    while ($row = $res_log->fetch_assoc()) {
        echo "Time: {$row['created_at']} | Action: {$row['action_type']} | Target: {$row['target_identifier']}\n";
        echo "  Old: " . substr($row['old_value'], 0, 100) . "\n";
        echo "  New: " . substr($row['new_value'], 0, 100) . "\n";
    }
} else {
    echo "No history logs found.\n";
}
echo "\n";

echo "=== UNIQUE SIGNATURES IN DATABASE ===\n";
$res_sigs = $db->query("SELECT visual_signature, COUNT(*) as qty FROM products GROUP BY visual_signature ORDER BY qty DESC LIMIT 20");
if ($res_sigs) {
    while ($row = $res_sigs->fetch_assoc()) {
        $sig = $row['visual_signature'] ?? '[NULL]';
        echo " - Signature: '{$sig}' (Count: {$row['qty']})\n";
    }
}
echo "\n";

echo "=== NORMALIZED SIGNATURES (AS SHOWN IN VIEW) ===\n";
$res_norm = $db->query("SELECT LOWER(REPLACE(visual_signature, 'ml', 'g')) as normalized, COUNT(*) as qty FROM products GROUP BY normalized ORDER BY qty DESC LIMIT 20");
if ($res_norm) {
    while ($row = $res_norm->fetch_assoc()) {
        $norm = $row['normalized'] ?? '[NULL]';
        echo " - Normalized: '{$norm}' (Count: {$row['qty']})\n";
    }
}

echo "</pre>";
?>
