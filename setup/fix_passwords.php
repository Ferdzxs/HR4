<?php
// One-time utility: reset demo user passwords to known values
// Visit this file in the browser once: /HR4_COMPEN&INTELLI/setup/fix_passwords.php

require_once __DIR__ . '/../config/database.php';

function respond($ok, $msg)
{
    http_response_code($ok ? 200 : 500);
    echo '<!doctype html><meta charset="utf-8"><body style="font-family:system-ui;margin:24px">';
    echo $ok ? '<h2>✓ Success</h2>' : '<h2>⚠ Error</h2>';
    echo '<pre>' . htmlspecialchars($msg) . '</pre>';
    echo '<p><a href="../routing/login.php">Go to Login</a></p>';
    echo '</body>';
    exit;
}

$db = new Database();
$conn = $db->getConnection();
if (!$conn) {
    respond(false, 'Database connection failed. Check config/database.php');
}

// Map of username => plain password
$resets = [
    'hr.manager' => 'hr123',
    'employee' => 'emp123',
    'payroll.admin' => 'payroll123',
    'benefits.coord' => 'benefits123',
    'comp.manager' => 'comp123',
    'dept.head' => 'dept123',
    'executive' => 'exec123',
];

$updated = [];
if ($conn instanceof PDO) {
    foreach ($resets as $username => $plain) {
        $hash = password_hash($plain, PASSWORD_BCRYPT);
        $stmt = $conn->prepare('UPDATE users SET password_hash = :hash WHERE username = :u');
        $stmt->execute([':hash' => $hash, ':u' => $username]);
        $updated[] = $username . ' => ' . $plain;
    }
} else {
    foreach ($resets as $username => $plain) {
        $hash = password_hash($plain, PASSWORD_BCRYPT);
        $stmt = $conn->prepare('UPDATE users SET password_hash = ? WHERE username = ?');
        $stmt->bind_param('ss', $hash, $username);
        $stmt->execute();
        $updated[] = $username . ' => ' . $plain;
    }
}

respond(true, "Updated passwords for:\n" . implode("\n", $updated));
?>