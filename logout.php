<?php
// HR4 Logout
session_start();
include_once 'config/auth.php';

// Destroy database session if exists
if (isset($_SESSION['user']['session_token'])) {
    $auth = new Auth();
    $auth->destroySession($_SESSION['user']['session_token']);
}

// Destroy PHP session
session_destroy();
header('Location: login.php');
exit;
?>
