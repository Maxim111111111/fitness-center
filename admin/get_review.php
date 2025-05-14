<?php
session_start();
require_once('../database/config.php');

// Проверка доступа (только для администраторов и менеджеров)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'manager'])) {
    exit(json_encode(['success' => false, 'message' => 'Доступ запрещен']));
}

header('Content-Type: application/json');

// Проверка наличия ID отзыва
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    exit(json_encode(['success' => false, 'message' => 'Не указан ID отзыва']));
}

$review_id = (int)$_GET['id'];

try {
    // Получение данных отзыва
    $sql = "
        SELECT 
            r.*, 
            u.first_name as user_first_name, 
            u.last_name as user_last_name,
            t.first_name as trainer_first_name, 
            t.last_name as trainer_last_name,
            s.name as service_name,
            m.first_name as moderator_first_name,
            m.last_name as moderator_last_name
        FROM reviews r
        LEFT JOIN users u ON r.user_id = u.id
        LEFT JOIN trainers tr ON r.trainer_id = tr.id
        LEFT JOIN users t ON tr.user_id = t.id
        LEFT JOIN services s ON r.service_id = s.id
        LEFT JOIN users m ON r.moderated_by = m.id
        WHERE r.id = ?
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$review_id]);
    $review = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$review) {
        exit(json_encode(['success' => false, 'message' => 'Отзыв не найден']));
    }
    
    // Успешный ответ
    echo json_encode([
        'success' => true,
        'review' => $review
    ]);
} catch (PDOException $e) {
    // Логирование ошибки
    error_log("Database error: " . $e->getMessage());
    
    // Ответ с ошибкой
    echo json_encode([
        'success' => false,
        'message' => 'Ошибка при получении данных отзыва: ' . $e->getMessage()
    ]);
} 