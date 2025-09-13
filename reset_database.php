<?php
// Database reset script for HR4 Compensation & Intelligence System
require_once __DIR__ . '/config/database.php';

echo "<h1>HR4 Database Reset</h1>";

try {
    // Drop database if exists
    $pdo->exec("DROP DATABASE IF EXISTS hr4_compensation_intelli");
    echo "<p>✓ Dropped existing database</p>";
    
    // Create database
    $pdo->exec("CREATE DATABASE hr4_compensation_intelli");
    echo "<p>✓ Created new database</p>";
    
    // Use the database
    $pdo->exec("USE hr4_compensation_intelli");
    echo "<p>✓ Selected database</p>";
    
    echo "<h2>✅ Database reset completed successfully!</h2>";
    echo "<p><a href='setup.php'>Run Setup to Create Tables and Data</a></p>";
    echo "<p><a href='index.php'>Go to HR4 System</a></p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error during reset:</h2>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
}
?>
