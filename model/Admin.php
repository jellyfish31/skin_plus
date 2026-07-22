<?php


class Admin {
    


    public static function initSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params(0); 
            session_start();
        }
        
        
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
    }

    





    public static function validateLogin($username, $password) {
        if ($username === 'admin' && $password === 'admin123') {
            $_SESSION['admin_logged_in'] = true;
            return true;
        }
        return false;
    }

    


    public static function requireAuth() {
        self::initSession();
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            header("Location: admin_login.php");
            exit();
        }
    }

    



    public static function isLoggedIn() {
        self::initSession();
        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }

    


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
