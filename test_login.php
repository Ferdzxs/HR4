<?php
/**
 * Login Test Script
 * HR4 Compensation & Intelligence System
 * 
 * This script tests the login functionality
 */

require_once 'config/database.php';
require_once 'includes/auth.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    echo "Testing database connection...\n";
    echo "✓ Database connected successfully\n\n";

    // Test if tables exist
    $tables = ['users', 'roles', 'employees', 'departments'];
    foreach ($tables as $table) {
        $result = $db->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->rowCount() > 0) {
            echo "✓ Table '$table' exists\n";
        } else {
            echo "✗ Table '$table' missing\n";
        }
    }

    echo "\n";

    // Test if users exist
    $stmt = $db->prepare("SELECT COUNT(*) FROM users");
    $stmt->execute();
    $userCount = $stmt->fetchColumn();
    echo "Users in database: $userCount\n";

    if ($userCount > 0) {
        // List all users
        $stmt = $db->prepare("
            SELECT u.username, r.role_name, e.first_name, e.last_name 
            FROM users u 
            JOIN roles r ON u.role_id = r.id 
            LEFT JOIN employees e ON u.employee_id = e.id
        ");
        $stmt->execute();
        $users = $stmt->fetchAll();

        echo "\nDemo Users:\n";
        echo "===========\n";
        foreach ($users as $user) {
            echo "- {$user['username']} ({$user['role_name']}) - {$user['first_name']} {$user['last_name']}\n";
        }

        // Test login
        echo "\nTesting login...\n";
        $auth = new Auth($db);

        $testCredentials = [
            ['hr.manager', 'manager123'],
            ['comp.manager', 'comp123'],
            ['employee', 'emp123']
        ];

        foreach ($testCredentials as $cred) {
            $username = $cred[0];
            $password = $cred[1];

            if ($auth->login($username, $password)) {
                $user = $auth->getCurrentUser();
                echo "✓ Login successful: {$user['username']} ({$user['role']})\n";
            } else {
                echo "✗ Login failed: $username\n";
            }
        }
    } else {
        echo "\nNo users found. Please run setup_demo_users.php first.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
