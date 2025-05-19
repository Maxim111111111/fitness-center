<?php
require_once 'database/config.php';

echo "<h1>Testing Trainers DB Connection</h1>";

try {
    // Test database connection
    echo "<p>Database connected successfully!</p>";
    
    // Test trainers query
    $stmt = $pdo->prepare("
        SELECT 
            t.id, t.experience_years, t.bio, t.photo_url, t.achievements,
            u.first_name, u.last_name, u.phone, u.email
        FROM trainers t
        JOIN users u ON t.user_id = u.id
        WHERE t.is_active = TRUE
        LIMIT 8
    ");
    $stmt->execute();
    $trainers = $stmt->fetchAll();
    
    echo "<p>Found " . count($trainers) . " trainers</p>";
    
    echo "<h2>Trainers List:</h2>";
    echo "<ul>";
    foreach ($trainers as $trainer) {
        echo "<li>";
        echo "<strong>" . $trainer['first_name'] . " " . $trainer['last_name'] . "</strong>";
        echo " - " . $trainer['experience_years'] . " years experience";
        echo " - <a href='coach-details.php?id=" . $trainer['id'] . "'>View Details</a>";
        echo "</li>";
    }
    echo "</ul>";
    
    // Test specializations
    $stmt = $pdo->prepare("SELECT * FROM specializations");
    $stmt->execute();
    $specializations = $stmt->fetchAll();
    
    echo "<h2>Specializations:</h2>";
    echo "<ul>";
    foreach ($specializations as $spec) {
        echo "<li>" . $spec['name'] . "</li>";
    }
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?> 