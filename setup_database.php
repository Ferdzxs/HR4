<?php
// Database setup script for HR4 system
// Run this script once to set up the database

echo "<h2>HR4 Database Setup</h2>";

// Try PDO first, fallback to MySQLi
$use_pdo = false;
$conn = null;

if (extension_loaded('pdo_mysql')) {
    try {
        $conn = new PDO("mysql:host=localhost;charset=utf8", "root", "");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $use_pdo = true;
        echo "<p>✓ Connected to MySQL server using PDO</p>";
    } catch(PDOException $e) {
        echo "<p>⚠ PDO connection failed: " . $e->getMessage() . "</p>";
    }
}

if (!$use_pdo && extension_loaded('mysqli')) {
    $conn = new mysqli("localhost", "root", "");
    if ($conn->connect_error) {
        echo "<p>✗ MySQLi connection failed: " . $conn->connect_error . "</p>";
        exit;
    }
    echo "<p>✓ Connected to MySQL server using MySQLi</p>";
}

if (!$conn) {
    echo "<p>✗ No MySQL driver available. Please enable PDO_MySQL or MySQLi extension.</p>";
    exit;
}

try {
    // Read and execute schema.sql
    $schema = file_get_contents('schema.sql');
    if ($schema) {
        if ($use_pdo) {
            $conn->exec($schema);
        } else {
            // Split schema into individual statements for MySQLi
            $statements = explode(';', $schema);
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    $conn->query($statement);
                }
            }
        }
        echo "<p>✓ Database schema created successfully</p>";
    } else {
        echo "<p>✗ Error: Could not read schema.sql file</p>";
        exit;
    }
    
    // Read and execute sample_data.sql
    $sample_data = file_get_contents('sample_data.sql');
    if ($sample_data) {
        if ($use_pdo) {
            $conn->exec($sample_data);
        } else {
            // Split sample data into individual statements for MySQLi
            $statements = explode(';', $sample_data);
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    $conn->query($statement);
                }
            }
        }
        echo "<p>✓ Sample data inserted successfully</p>";
    } else {
        echo "<p>✗ Error: Could not read sample_data.sql file</p>";
        exit;
    }
    
    echo "<h3>Database Setup Complete!</h3>";
    echo "<p>You can now use the HR4 system with the following demo accounts:</p>";
    echo "<ul>";
    echo "<li><strong>HR Manager:</strong> hr.manager / hr123</li>";
    echo "<li><strong>Employee:</strong> employee / emp123</li>";
    echo "<li><strong>Payroll Admin:</strong> payroll.admin / payroll123</li>";
    echo "<li><strong>Benefits Coord:</strong> benefits.coord / benefits123</li>";
    echo "<li><strong>Compensation Manager:</strong> comp.manager / comp123</li>";
    echo "<li><strong>Department Head:</strong> dept.head / dept123</li>";
    echo "<li><strong>Executive:</strong> executive / exec123</li>";
    echo "</ul>";
    echo "<p><a href='fix_passwords.php'>Fix Passwords</a> | <a href='test_connection.php'>Test Connection</a> | <a href='login.php'>Go to Login</a></p>";
    
} catch(Exception $e) {
    echo "<p>✗ Database error: " . $e->getMessage() . "</p>";
    echo "<p>Please make sure MySQL is running and the root user has no password (or update config/database.php)</p>";
}
?>
