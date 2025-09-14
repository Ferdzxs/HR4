<?php
// Test authentication system
require_once 'config/auth.php';

echo "<h2>Authentication Test</h2>";

try {
    $auth = new Auth();
    
    // Test credentials
    $test_users = [
        ['username' => 'hr.manager', 'password' => 'hr123'],
        ['username' => 'employee', 'password' => 'emp123'],
        ['username' => 'payroll.admin', 'password' => 'payroll123'],
        ['username' => 'benefits.coord', 'password' => 'benefits123'],
        ['username' => 'comp.manager', 'password' => 'comp123'],
        ['username' => 'dept.head', 'password' => 'dept123'],
        ['username' => 'executive', 'password' => 'exec123']
    ];
    
    echo "<h3>Testing User Authentication:</h3>";
    
    foreach ($test_users as $test) {
        $user = $auth->authenticate($test['username'], $test['password']);
        
        if ($user) {
            echo "<p style='color: green;'>✓ <strong>{$test['username']}</strong> - Authentication successful</p>";
            echo "<p style='margin-left: 20px;'>Role: {$user['role']} | Name: {$user['first_name']} {$user['last_name']}</p>";
        } else {
            echo "<p style='color: red;'>✗ <strong>{$test['username']}</strong> - Authentication failed</p>";
        }
    }
    
    echo "<h3>Testing Invalid Credentials:</h3>";
    
    $invalid_tests = [
        ['username' => 'hr.manager', 'password' => 'wrongpassword'],
        ['username' => 'nonexistent', 'password' => 'emp123']
    ];
    
    foreach ($invalid_tests as $test) {
        $user = $auth->authenticate($test['username'], $test['password']);
        
        if ($user) {
            echo "<p style='color: red;'>✗ <strong>{$test['username']}</strong> - Should have failed but succeeded!</p>";
        } else {
            echo "<p style='color: green;'>✓ <strong>{$test['username']}</strong> - Correctly rejected invalid credentials</p>";
        }
    }
    
    echo "<p><a href='login.php'>Go to Login Page</a></p>";
    
} catch(Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}
?>
