<?php
// model/Notification.php

require_once __DIR__ . '/../config/Database.php';

class Notification {
    /**
     * Gets all pending products grouped by product name.
     * @return array
     */
    public static function getPendingDiscoveries() {
        $db = Database::getMysqli();
        $sql = "SELECT product_name, product_brand, product_category, product_store, product_image 
                FROM products 
                WHERE visual_signature = 'PENDING_ADMIN' OR visual_signature IS NULL
                GROUP BY product_name";
                
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
     * Gets the count of unique pending discoveries.
     * @return int
     */
    public static function getPendingDiscoveriesCount() {
        $db = Database::getMysqli();
        $sql = "SELECT COUNT(DISTINCT product_name) as cnt 
                FROM products 
                WHERE visual_signature = 'PENDING_ADMIN' OR visual_signature IS NULL";
        $result = $db->query($sql);
        if ($result) {
            $row = $result->fetch_assoc();
            return (int)$row['cnt'];
        }
        return 0;
    }
}
