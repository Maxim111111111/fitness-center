<?php
require_once('config.php');

try {
    // Get a list of all tables
    $result = $pdo->query("SHOW TABLES");
    $tables = $result->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Tables in database:\n";
    foreach ($tables as $table) {
        echo "- " . $table . "\n";
        
        // Get table structure
        $stmt = $pdo->prepare("DESCRIBE " . $table);
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "  Columns:\n";
        foreach ($columns as $column) {
            echo "    " . $column['Field'] . " - " . $column['Type'] . "\n";
        }
        echo "\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
} 