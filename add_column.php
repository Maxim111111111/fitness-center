<?php
require_once('database/config.php');

try {
    // Проверяем, существует ли столбец updated_at в таблице user_subscriptions
    $stmt = $pdo->prepare("SHOW COLUMNS FROM user_subscriptions LIKE 'updated_at'");
    $stmt->execute();
    $column_exists = $stmt->fetch();
    
    if (!$column_exists) {
        // Добавляем столбец updated_at
        $stmt = $pdo->prepare("ALTER TABLE user_subscriptions ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
        $stmt->execute();
        echo "Столбец updated_at успешно добавлен в таблицу user_subscriptions.<br>";
        
        // Обновляем существующие записи
        $stmt = $pdo->prepare("UPDATE user_subscriptions SET updated_at = NOW()");
        $stmt->execute();
        echo "Значения updated_at установлены для существующих записей.<br>";
    } else {
        echo "Столбец updated_at уже существует в таблице user_subscriptions.<br>";
    }
    
    echo "Операция завершена успешно.";
} catch (PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}
?> 