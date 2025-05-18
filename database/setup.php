<?php
// Database setup script
// Run this file manually once to set up the database

// Load the database configuration
require_once('config.php');

// Function to execute SQL from a file
function executeSqlFile($pdo, $filePath) {
    if (!file_exists($filePath)) {
        die("SQL file not found: $filePath");
    }
    
    $sql = file_get_contents($filePath);
    
    // Split SQL file into individual queries
    $queries = explode(';', $sql);
    
    // Execute each query
    foreach ($queries as $query) {
        $query = trim($query);
        if (empty($query)) continue;
        
        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            echo "Query executed successfully.<br>";
        } catch (PDOException $e) {
            echo "Error executing query: " . $e->getMessage() . "<br>";
            echo "Query: $query<br><br>";
        }
    }
}

// Check if connection is successful
echo "<h2>Database Setup</h2>";
echo "Connected to database " . DB_NAME . " successfully.<br><br>";

// Execute the schema SQL file
echo "<h3>Creating Tables</h3>";
executeSqlFile($pdo, __DIR__ . '/../database.sql');

echo "<br><h3>Setup Complete!</h3>";
echo "The database has been set up successfully.<br>";
echo "<a href='../index.php'>Return to Home Page</a>";
?> 