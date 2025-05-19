<?php
session_start();
require_once('../database/config.php');

// Проверка доступа (только для администраторов и менеджеров)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'manager'])) {
    header('Location: ../login.php');
    exit();
}

header('Content-Type: application/json');

// Проверка наличия ID абонемента
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    exit(json_encode(['success' => false, 'message' => 'Не указан ID абонемента']));
}

$subscription_id = (int)$_GET['id'];

try {
    // Проверим структуру таблицы, чтобы определить, какие поля использовать
    $tableStructure = $pdo->query("DESCRIBE subscriptions");
    $columns = $tableStructure->fetchAll(PDO::FETCH_COLUMN);
    
    if (in_array('duration', $columns) && in_array('duration_type', $columns)) {
        // Новая структура с duration и duration_type
        $sql = "
            SELECT id, name, description, price, duration, duration_type, visit_limit, is_active, created_at
            FROM subscriptions
            WHERE id = ?
        ";
    } else {
        // Старая структура с duration_days и sessions_count
        $sql = "
            SELECT id, name, description, price, 
                   duration_days as duration, 
                   'days' as duration_type, 
                   sessions_count as visit_limit, 
                   is_active, created_at
            FROM subscriptions
            WHERE id = ?
        ";
    }
    
    // Получение данных абонемента
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$subscription_id]);
    $subscription = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$subscription) {
        exit(json_encode(['success' => false, 'message' => 'Абонемент не найден']));
    }
    
    // Успешный ответ
    echo json_encode([
        'success' => true,
        'subscription' => $subscription
    ]);
} catch (PDOException $e) {
    // Логирование ошибки
    error_log("Database error: " . $e->getMessage());
    
    // Ответ с ошибкой
    echo json_encode([
        'success' => false,
        'message' => 'Ошибка при получении данных абонемента: ' . $e->getMessage()
    ]);
} 