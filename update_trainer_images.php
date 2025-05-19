<?php
require_once 'database/config.php';

echo "<h1>Обновление путей к изображениям тренеров</h1>";

try {
    // Обновляем пути к изображениям
    $stmt = $pdo->prepare("
        UPDATE trainers 
        SET photo_url = REPLACE(photo_url, 'assets/img/trainers/', 'images/trainers/')
        WHERE photo_url LIKE 'assets/img/trainers/%'
    ");
    $stmt->execute();
    
    $count = $stmt->rowCount();
    echo "<p>Обновлено записей: $count</p>";
    
    // Проверяем, что пути обновлены
    $stmt = $pdo->query("SELECT id, photo_url FROM trainers");
    $trainers = $stmt->fetchAll();
    
    echo "<h2>Текущие пути к изображениям:</h2>";
    echo "<ul>";
    foreach ($trainers as $trainer) {
        echo "<li>Тренер ID: " . $trainer['id'] . " - Путь: " . $trainer['photo_url'] . "</li>";
    }
    echo "</ul>";
    
    echo "<p><a href='coach.php'>Вернуться к списку тренеров</a></p>";
    
} catch (PDOException $e) {
    echo "<p>Ошибка: " . $e->getMessage() . "</p>";
}
?> 