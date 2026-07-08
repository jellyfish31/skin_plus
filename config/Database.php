<?php
// config/Database.php

class Database {
    private static ?string $host = null;
    private static ?string $user = null;
    private static ?string $pass = null;
    private static ?string $dbname = null;
    private static ?mysqli $mysqli = null;
    private static ?PDO $pdo = null;

    /**
     * Initializes connection parameters from config/Keys.php or defaults.
     */
    private static function init() {
        if (self::$host === null) {
            $keys = file_exists(__DIR__ . '/Keys.php') ? include __DIR__ . '/Keys.php' : [];
            self::$host = $keys['db_host'] ?? 'localhost';
            self::$user = $keys['db_user'] ?? 'root';
            self::$pass = $keys['db_pass'] ?? '';
            self::$dbname = $keys['db_name'] ?? 'skinplus_db';
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
            } catch (PDOException $e) {
                die(json_encode(['success' => false, 'error' => 'Database connection failed (PDO): ' . $e->getMessage()]));
            }
        }
        return self::$pdo;
    }
}
