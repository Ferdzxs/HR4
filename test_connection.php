<?php
// Test database connection
require_once 'config/database.php';

echo "<h2>HR4 Database Connection Test</h2>";

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        echo "<p style='color: green;'>✓ Database connection successful!</p>";
        
        // Check if using PDO or MySQLi
        $is_pdo = ($conn instanceof PDO);
        
        // Test a simple query
        $query = "SELECT COUNT(*) as user_count FROM users";
        
        if ($is_pdo) {
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $result = $conn->query($query)->fetch_assoc();
        }
        
        echo "<p>✓ Found " . $result['user_count'] . " users in the database</p>";
        
        // Test role query
        $query = "SELECT role_name, COUNT(*) as count FROM users u JOIN roles r ON u.role_id = r.id GROUP BY role_name";
        
        if ($is_pdo) {
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $result = $conn->query($query);
            $roles = [];
            while ($row = $result->fetch_assoc()) {
                $roles[] = $row;
            }
        }
        
        echo "<h3>User Roles in Database:</h3>";
        echo "<ul>";
        foreach ($roles as $role) {
            echo "<li><strong>" . $role['role_name'] . ":</strong> " . $role['count'] . " users</li>";
        }
        echo "</ul>";
        
        echo "<p><a href='fix_passwords.php'>Fix Passwords</a> | <a href='test_auth.php'>Test Authentication</a> | <a href='login.php'>Go to Login Page</a></p>";
        
    } else {
        echo "<p style='color: red;'>✗ Database connection failed</p>";
    }
    
} catch(Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    echo "<p>Please run setup_database.php first to create the database and tables.</p>";
}
?>
