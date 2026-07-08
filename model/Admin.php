<?php
// model/Admin.php

class Admin {
    /**
     * Initializes session security parameters and starts session.
     */
    public static function initSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params(0); // Destroys session cookie when browser closes
            session_start();
        }
        
        // Disable page caching for security
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
    }

    /**
     * Validates admin login credentials.
     * @param string $username
     * @param string $password
     * @return bool
     */
    public static function validateLogin($username, $password) {
        if ($username === 'admin' && $password === 'admin123') {
            $_SESSION['admin_logged_in'] = true;
            return true;
        }
        return false;
    }

    /**
     * Checks if the admin is logged in. If not, redirects to login page.
     */
    public static function requireAuth() {
        self::initSession();
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            header("Location: admin_login.php");
            exit();
        }
    }

    /**
     * Checks if the admin is logged in without redirecting.
     * @return bool
     */
    public static function isLoggedIn() {
        self::initSession();
        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }

    /**
     * Logs out the admin by destroying the session and cookies.
     */
    public static function logout() {
        self::initSession();
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        header("Location: admin_login.php");
        exit();
    }
}
