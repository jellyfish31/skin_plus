<?php
// model/HistoryLog.php

require_once __DIR__ . '/../config/Database.php';

class HistoryLog {
    /**
     * Inserts a record into the history_logs table.
     * @param string $actionType
     * @param string $targetIdentifier
     * @param string $oldValue
     * @param string $newValue
     * @return bool
     */
    public static function addLog($actionType, $targetIdentifier, $oldValue, $newValue) {
        $db = Database::getMysqli();
        
        $escapedAction = $db->real_escape_string($actionType);
        $escapedTarget = $db->real_escape_string($targetIdentifier);
        $escapedOld = $db->real_escape_string($oldValue);
        $escapedNew = $db->real_escape_string($newValue);

        $sql = "INSERT INTO history_logs (action_type, target_identifier, old_value, new_value, admin_user) 
                VALUES ('$escapedAction', '$escapedTarget', '$escapedOld', '$escapedNew', 'admin')";
                
        $result = $db->query($sql);
        if (!$result) {
            file_put_contents(__DIR__ . '/../debug_log.txt', "SQL: " . $sql . "\nError: " . $db->error . "\n\n", FILE_APPEND);
        }
        return $result;
    }
}
