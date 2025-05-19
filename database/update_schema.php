<?php
// Include database configuration
require_once 'config.php';

// Get current directory
$currentDir = dirname(__FILE__);

// Load SQL file
$sqlFile = file_get_contents($currentDir . '/../database.sql');

// Split into individual statements
$statements = explode(';', $sqlFile);

// Execute each statement
$error = false;
$errorMessages = [];

foreach ($statements as $statement) {
    $statement = trim($statement);
    if (!empty($statement)) {
        try {
            $pdo->exec($statement);
        } catch (PDOException $e) {
            $error = true;
            $errorMessages[] = $e->getMessage();
        }
    }
}

// Output result
if ($error) {
    echo '<div style="color: red; font-weight: bold;">Произошли ошибки при обновлении схемы базы данных:</div>';
    echo '<ul>';
    foreach ($errorMessages as $message) {
        echo '<li>' . htmlspecialchars($message) . '</li>';
    }
    echo '</ul>';
} else {
    echo '<div style="color: green; font-weight: bold;">Схема базы данных успешно обновлена!</div>';
}

// Check if reviews table exists and has the correct structure
try {
    $stmt = $pdo->query("DESCRIBE reviews");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $requiredColumns = ['id', 'name', 'email', 'rating', 'text', 'status', 'created_at', 'updated_at'];
    $missingColumns = array_diff($requiredColumns, $columns);
    
    if (!empty($missingColumns)) {
        echo '<div style="color: orange; font-weight: bold;">Предупреждение: В таблице reviews отсутствуют следующие столбцы: ' . implode(', ', $missingColumns) . '</div>';
    } else {
        echo '<div style="color: green;">Структура таблицы reviews проверена и корректна.</div>';
    }
} catch (PDOException $e) {
    echo '<div style="color: red;">Ошибка при проверке таблицы reviews: ' . htmlspecialchars($e->getMessage()) . '</div>';
}

// Link to return to admin
echo '<p><a href="../admin/reviews.php">Вернуться к управлению отзывами</a></p>';
?> 