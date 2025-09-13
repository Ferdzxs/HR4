<?php
// Database setup script for HR4 Compensation & Intelligence System
require_once __DIR__ . '/config/database.php';

echo "<h1>HR4 Database Setup</h1>";

try {
    // Read and execute schema
    $schema = file_get_contents(__DIR__ . '/database/schema_fixed.sql');
    $statements = explode(';', $schema);

    echo "<h2>Creating Database Schema...</h2>";
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            $pdo->exec($statement);
            echo "<p>✓ Executed: " . substr($statement, 0, 50) . "...</p>";
        }
    }

    // Read and execute sample data
    $sampleData = file_get_contents(__DIR__ . '/database/sample_data.sql');
    $dataStatements = explode(';', $sampleData);

    echo "<h2>Inserting Sample Data...</h2>";
    foreach ($dataStatements as $statement) {
        $statement = trim($statement);
        if (!empty($statement) && !preg_match('/^--/', $statement)) {
            $pdo->exec($statement);
            echo "<p>✓ Inserted data</p>";
        }
    }

    echo "<h2>✅ Database setup completed successfully!</h2>";
    echo "<p><a href='index.php'>Go to HR4 System</a></p>";

} catch (Exception $e) {
    echo "<h2>❌ Error during setup:</h2>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
}
?>