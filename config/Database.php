<?php
// config/Database.php

class Database {
    private static $host = "localhost";
    private static $user = "root";
    private static $pass = "";
    private static $dbname = "skinplus_db";
    private static $mysqli = null;
    private static $pdo = null;

    /**
     * Returns a centralized MySQLi connection.
     * @return mysqli
     */
    public static function getMysqli() {
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
