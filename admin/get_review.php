<?php
session_start();
require_once('../database/config.php');

// Проверка доступа (только для администраторов и менеджеров)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'manager'])) {
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
        SELECT * FROM reviews WHERE id = ?
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