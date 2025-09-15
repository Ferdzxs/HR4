<?php
// Simple DB connectivity check
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

header('Content-Type: text/plain; charset=utf-8');
if ($conn) {
    echo "OK: Connected using " . ($conn instanceof PDO ? 'PDO' : 'MySQLi') . "\n";
    if ($conn instanceof PDO) {
        $stmt = $conn->query('SELECT DATABASE() as db');
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo 'Database: ' . ($row['db'] ?? '(unknown)') . "\n";
    } else {
        $res = $conn->query('SELECT DATABASE() as db');
        $row = $res ? $res->fetch_assoc() : [];
        echo 'Database: ' . ($row['db'] ?? '(unknown)') . "\n";
    }
} else {
    echo "ERROR: Failed to connect. Check config/database.php\n";
}
?>