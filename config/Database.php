<?php
// config/Database.php

class Database {
    private static ?string $host = null;
    private static ?string $user = null;
    private static ?string $pass = null;
    private static ?string $dbname = null;
    private static ?mysqli $mysqli = null;
    private static ?PDO $pdo = null;
    private static bool $use_live = false;

    /**
     * Dynamically enables or disables connection to Hostinger live DB.
     */
    public static function useLiveDatabase(bool $enable = true) {
        if (self::$use_live !== $enable) {
            self::$use_live = $enable;
            // Clear existing connection variables to force reconnection
            self::$host = null;
            self::$user = null;
            self::$pass = null;
            self::$dbname = null;
            self::$mysqli = null;
            self::$pdo = null;
        }
    }

    /**
     * Initializes connection parameters from config/Keys.php or defaults.
     */
    private static function init() {
        if (self::$host === null) {
            $keys = file_exists(__DIR__ . '/Keys.php') ? include __DIR__ . '/Keys.php' : [];
            
            // Detect if running on localhost / development environment
            $is_local = false;
            if (isset($_SERVER['HTTP_HOST'])) {
                $host_lower = strtolower($_SERVER['HTTP_HOST']);
                if ($host_lower === 'localhost' || str_starts_with($host_lower, '127.0.0.1') || str_starts_with($host_lower, '192.168.')) {
                    $is_local = true;
                }
            } else {
                $is_local = true; // Fallback for PHP CLI / other scripts running locally
            }

            // If locally requested admin tasks, use the remote live database credentials
            if ($is_local && self::$use_live && isset($keys['live_db_host']) && !empty($keys['live_db_host'])) {
                self::$host = $keys['live_db_host'];
                self::$user = $keys['live_db_user'] ?? '';
                self::$pass = $keys['live_db_pass'] ?? '';
                self::$dbname = $keys['live_db_name'] ?? '';
            } else {
                self::$host = $keys['db_host'] ?? 'localhost';
                self::$user = $keys['db_user'] ?? 'root';
                self::$pass = $keys['db_pass'] ?? '';
                self::$dbname = $keys['db_name'] ?? 'skinplus_db';
            }
        }
    }

    /**
     * Returns a centralized MySQLi connection.
     * @return mysqli
     */
    public static function getMysqli() {
        self::init();
        if (self::$mysqli === null) {
            self::$mysqli = new mysqli(self::$host, self::$user, self::$pass, self::$dbname);
            if (self::$mysqli->connect_error) {
                die("❌ Database connection failed (mysqli): " . self::$mysqli->connect_error);
            }
            self::$mysqli->set_charset("utf8mb4");
            self::$mysqli->query("SET time_zone = '+08:00'");
        }
        return self::$mysqli;
    }

    /**
     * Returns a centralized PDO connection.
     * @return PDO
     */
    public static function getPdo() {
        self::init();
        if (self::$pdo === null) {
            try {
                self::$pdo = new PDO(
                    "mysql:host=" . self::$host . ";dbname=" . self::$dbname . ";charset=utf8mb4",
                    self::$user,
                    self::$pass,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                    ]
                );
                self::$pdo->exec("SET time_zone = '+08:00'");
            } catch (PDOException $e) {
                die(json_encode(['success' => false, 'error' => 'Database connection failed (PDO): ' . $e->getMessage()]));
            }
        }
        return self::$pdo;
    }
}
