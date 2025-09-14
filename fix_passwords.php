<?php
// Fix password hashes in the database
require_once 'config/database.php';

echo "<h2>Fixing Password Hashes</h2>";

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        echo "<p style='color: red;'>✗ Database connection failed</p>";
        exit;
    }
    
    echo "<p>✓ Database connected successfully</p>";
    
    // Check if using PDO or MySQLi
    $is_pdo = ($conn instanceof PDO);
    
    // Define correct passwords for each user
    $user_passwords = [
        'hr.manager' => 'hr123',
        'comp.manager' => 'comp123', 
        'benefits.coord' => 'benefits123',
        'payroll.admin' => 'payroll123',
        'dept.head' => 'dept123',
        'employee' => 'emp123',
        'executive' => 'exec123',
        'john.smith' => 'emp123',
        'sarah.johnson' => 'emp123',
        'michael.brown' => 'emp123',
        'emily.davis' => 'emp123',
        'david.wilson' => 'emp123',
        'lisa.anderson' => 'emp123',
        'robert.taylor' => 'emp123',
        'jennifer.thomas' => 'emp123',
        'william.jackson' => 'emp123',
        'maria.white' => 'emp123',
        'james.harris' => 'emp123',
        'patricia.martin' => 'emp123',
        'richard.garcia' => 'emp123',
        'linda.martinez' => 'emp123',
        'charles.robinson' => 'hr123',
        'barbara.clark' => 'payroll123',
        'joseph.rodriguez' => 'benefits123',
        'susan.lewis' => 'comp123',
        'thomas.lee' => 'exec123',
        'jessica.walker' => 'dept123'
    ];
    
    $updated_count = 0;
    
    foreach ($user_passwords as $username => $password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        if ($is_pdo) {
            $query = "UPDATE users SET password_hash = ? WHERE username = ?";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(1, $hashed_password);
            $stmt->bindParam(2, $username);
            
            if ($stmt->execute()) {
                echo "<p>✓ Updated password for: $username</p>";
                $updated_count++;
            } else {
                echo "<p style='color: orange;'>⚠ Could not update: $username</p>";
            }
        } else {
            // MySQLi
            $query = "UPDATE users SET password_hash = ? WHERE username = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ss", $hashed_password, $username);
            
            if ($stmt->execute()) {
                echo "<p>✓ Updated password for: $username</p>";
                $updated_count++;
            } else {
                echo "<p style='color: orange;'>⚠ Could not update: $username</p>";
            }
        }
    }
    
    echo "<h3>Password Update Complete!</h3>";
    echo "<p>Updated $updated_count user passwords</p>";
    echo "<p><a href='test_connection.php'>Test Connection</a> | <a href='test_auth.php'>Test Authentication</a> | <a href='login.php'>Go to Login</a></p>";
    
} catch(Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}
?>
