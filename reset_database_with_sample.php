<?php
/**
 * Database Reset and Sample Data Loader
 * This script will clear all existing data and load the sample data
 */

require_once 'config/database.php';

try {
    // Read the SQL file
    $sqlFile = 'database/reset_and_load_sample_data.sql';

    if (!file_exists($sqlFile)) {
        throw new Exception("SQL file not found: $sqlFile");
    }

    $sql = file_get_contents($sqlFile);

    if ($sql === false) {
        throw new Exception("Failed to read SQL file");
    }

    // Split the SQL into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function ($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt);
        }
    );

    echo "<h2>HR4 Compensation & Intelligence - Database Reset</h2>";
    echo "<p>Starting database reset and sample data loading...</p>";

    // First, disable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    echo "<p>✓ Foreign key checks disabled</p>";

    // Execute each statement
    $successCount = 0;
    $errorCount = 0;

    foreach ($statements as $statement) {
        try {
            if (!empty(trim($statement))) {
                $pdo->exec($statement);
                $successCount++;
            }
        } catch (PDOException $e) {
            $errorCount++;
            echo "<p style='color: red;'>Error executing statement: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<p style='color: gray;'>Statement: " . htmlspecialchars(substr($statement, 0, 100)) . "...</p>";
        }
    }

    // Re-enable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "<p>✓ Foreign key checks re-enabled</p>";

    echo "<div style='background: #f0f8ff; padding: 15px; border: 1px solid #007acc; margin: 10px 0;'>";
    echo "<h3>Reset Complete!</h3>";
    echo "<p><strong>Successfully executed:</strong> $successCount statements</p>";
    echo "<p><strong>Errors encountered:</strong> $errorCount statements</p>";

    if ($errorCount == 0) {
        echo "<p style='color: green;'><strong>✅ Database has been successfully reset and sample data loaded!</strong></p>";
    } else {
        echo "<p style='color: orange;'><strong>⚠️ Database reset completed with some errors. Please review the errors above.</strong></p>";
    }

    echo "</div>";

    // Display some verification data
    echo "<h3>Verification - Sample Data Loaded:</h3>";

    // Check roles
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM roles");
    $roleCount = $stmt->fetch()['count'];
    echo "<p>• Roles loaded: $roleCount</p>";

    // Check employees
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM employees");
    $empCount = $stmt->fetch()['count'];
    echo "<p>• Employees loaded: $empCount</p>";

    // Check departments
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM departments");
    $deptCount = $stmt->fetch()['count'];
    echo "<p>• Departments loaded: $deptCount</p>";

    // Check payroll periods
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM payroll_periods");
    $periodCount = $stmt->fetch()['count'];
    echo "<p>• Payroll periods loaded: $periodCount</p>";

    echo "<hr>";
    echo "<h3>Test Login Credentials:</h3>";
    echo "<div style='background: #f9f9f9; padding: 10px; border-left: 4px solid #007acc;'>";
    echo "<p><strong>HR Manager:</strong> hr.manager / hr123</p>";
    echo "<p><strong>Compensation Manager:</strong> comp.manager / comp123</p>";
    echo "<p><strong>Benefits Coordinator:</strong> benefits.coord / benefits123</p>";
    echo "<p><strong>Payroll Administrator:</strong> payroll.admin / payroll123</p>";
    echo "<p><strong>Department Head:</strong> dept.head / dept123</p>";
    echo "<p><strong>Employee:</strong> employee / emp123</p>";
    echo "<p><strong>Executive:</strong> executive / exec123</p>";
    echo "</div>";

    echo "<p><a href='index.php' style='background: #007acc; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Application</a></p>";

} catch (Exception $e) {
    echo "<div style='background: #ffe6e6; padding: 15px; border: 1px solid #ff0000; margin: 10px 0;'>";
    echo "<h3 style='color: red;'>Error:</h3>";
    echo "<p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>
