<?php
// API для получения информации о записи на тренировку
session_start();

// Проверка авторизации
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'manager', 'trainer'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// Подключение к базе данных
require_once '../database/config.php';

// Проверка наличия ID записи
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Session ID is required'
    ]);
    exit;
}

$session_id = intval($_GET['id']);

// Запрос к базе данных
try {
    // Подготовка запроса для получения данных записи
    $query = "SELECT 
                ts.*, 
                CONCAT(u.first_name, ' ', u.last_name) AS user_name,
                CONCAT(t.first_name, ' ', t.last_name) AS trainer_name,
                s.name AS service_name
              FROM training_sessions ts
              JOIN users u ON ts.user_id = u.id
              JOIN trainers tr ON ts.trainer_id = tr.id
              JOIN users t ON tr.user_id = t.id
              JOIN services s ON ts.service_id = s.id
              WHERE ts.id = ?";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$session_id]);
    $session = $stmt->fetch();
    
    if ($session) {
        // Возвращаем данные в формате JSON
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'session' => $session
        ]);
    } else {
        // Запись не найдена
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Session not found'
        ]);
    }
} catch (PDOException $e) {
    // Логирование ошибки
    error_log('Database error: ' . $e->getMessage());
    
    // Возвращаем ошибку
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
} 