<?php
session_start();
require_once('../database/config.php');

// Проверка доступа (только для администраторов и менеджеров)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'manager'])) {
    exit(json_encode(['success' => false, 'message' => 'Доступ запрещен']));
}

header('Content-Type: application/json');

// Проверка наличия ID пользователя
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    exit(json_encode(['success' => false, 'message' => 'Не указан ID пользователя']));
}

$user_id = (int)$_GET['id'];

try {
    // Получение данных пользователя
    $stmt = $pdo->prepare("
        SELECT id, email, first_name, last_name, phone, role, is_active, created_at, last_login
        FROM users
        WHERE id = ?
    ");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        exit(json_encode(['success' => false, 'message' => 'Пользователь не найден']));
    }
    
    // Успешный ответ
    echo json_encode([
        'success' => true,
        'user' => $user
    ]);
} catch (PDOException $e) {
    // Логирование ошибки
    error_log("Database error: " . $e->getMessage());
    
    // Ответ с ошибкой
    echo json_encode([
        'success' => false,
        'message' => 'Ошибка при получении данных пользователя'
    ]);
} 