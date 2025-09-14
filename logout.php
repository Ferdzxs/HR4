<?php
// HR4 Logout
session_start();
include_once 'config/auth.php';

// Destroy database session if exists
if (isset($_SESSION['user']['session_token'])) {
    $auth = new Auth();
    $auth->destroySession($_SESSION['user']['session_token']);
}

// Clear remember me cookie
if (isset($_COOKIE['hr4_remember_token'])) {
    setcookie('hr4_remember_token', '', time() - 3600, '/', '', true, true);
}

// Clear all session data
$_SESSION = array();

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy PHP session
session_destroy();

// Redirect to login with success message
header('Location: login.php?logged_out=1');
exit;
?>
