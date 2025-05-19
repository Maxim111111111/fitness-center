<?php
// Include database configuration
require_once 'config.php';

// SQL to drop and recreate the reviews table
$sql = "
DROP TABLE IF EXISTS `reviews`;

CREATE TABLE IF NOT EXISTS `reviews` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `email` varchar(100) NOT NULL,
    `rating` int(1) NOT NULL,
    `text` text NOT NULL,
    `status` enum('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Insert demo data
INSERT INTO `reviews` (`name`, `email`, `rating`, `text`, `status`)
VALUES 
    ('Ольга', 'olga@example.com', 5, 'Мореон Фитнес – семейный премиум фитнес-клуб с бассейном, 40 видами групповых программ, детским клубом, школой единоборств и скалодромом. Оборудование тренажерного зала поставляет эксклюзивный партнер', 'approved'),
    ('Алексей', 'alexey@example.com', 4, 'Отличный фитнес-клуб с прекрасными тренерами. Очень доволен результатами после трех месяцев тренировок.', 'approved'),
    ('Ирина', 'irina@example.com', 5, 'Просторный тренажерный зал, отличный бассейн, множество групповых программ - все что нужно для полноценных тренировок.', 'approved'),
    ('Дмитрий', 'dmitriy@example.com', 3, 'Хороший клуб, но иногда бывает слишком много людей в вечернее время. В целом доволен сервисом.', 'pending');
";

try {
    // Execute the SQL
    $pdo->exec($sql);
    echo '<div style="color: green; font-weight: bold;">Таблица reviews успешно обновлена!</div>';

    // Check if the table structure is correct
    $stmt = $pdo->query("DESCRIBE reviews");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $requiredColumns = ['id', 'name', 'email', 'rating', 'text', 'status', 'created_at', 'updated_at'];
    $missingColumns = array_diff($requiredColumns, $columns);
    
    if (!empty($missingColumns)) {
        echo '<div style="color: orange; font-weight: bold;">Предупреждение: В таблице reviews отсутствуют следующие столбцы: ' . implode(', ', $missingColumns) . '</div>';
    } else {
        echo '<div style="color: green;">Структура таблицы reviews проверена и корректна.</div>';
    }

    // Count reviews
    $stmt = $pdo->query("SELECT COUNT(*) FROM reviews");
    $count = $stmt->fetchColumn();
    echo '<div style="color: blue;">Всего отзывов в базе данных: ' . $count . '</div>';

} catch (PDOException $e) {
    echo '<div style="color: red; font-weight: bold;">Ошибка при обновлении таблицы reviews: ' . htmlspecialchars($e->getMessage()) . '</div>';
}

// Link to return to admin
echo '<p><a href="../admin/reviews.php">Вернуться к управлению отзывами</a></p>';
?> 