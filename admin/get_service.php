<?php
session_start();
require_once('../database/config.php');

// Проверка доступа (только для администраторов и менеджеров)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'manager'])) {
    header('Content-Type: application/json');
    exit(json_encode(['success' => false, 'message' => 'Доступ запрещен']));
}

// Проверка ID услуги
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Content-Type: application/json');
    exit(json_encode(['success' => false, 'message' => 'Неверный ID услуги']));
}

$serviceId = (int)$_GET['id'];

try {
    // Получение данных услуги
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$serviceId]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$service) {
        header('Content-Type: application/json');
        exit(json_encode(['success' => false, 'message' => 'Услуга не найдена']));
    }
    
    header('Content-Type: application/json');
    exit(json_encode(['success' => true, 'service' => $service]));
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    header('Content-Type: application/json');
    exit(json_encode(['success' => false, 'message' => 'Ошибка при получении данных услуги']));
} 