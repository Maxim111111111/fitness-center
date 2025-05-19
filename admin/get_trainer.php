<?php
session_start();
require_once('../database/config.php');

// Проверка доступа (только для администраторов и менеджеров)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'manager'])) {
    header('Content-Type: application/json');
    exit(json_encode(['success' => false, 'message' => 'Доступ запрещен']));
}

// Проверка ID тренера
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Content-Type: application/json');
    exit(json_encode(['success' => false, 'message' => 'Неверный ID тренера']));
}

$trainerId = (int)$_GET['id'];

try {
    // Получаем данные тренера
    $stmt = $pdo->prepare("
        SELECT t.*, u.first_name, u.last_name, u.email, u.phone, u.role as user_role
        FROM trainers t
        JOIN users u ON t.user_id = u.id
        WHERE t.id = ?
    ");
    $stmt->execute([$trainerId]);
    $trainerData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$trainerData) {
        header('Content-Type: application/json');
        exit(json_encode(['success' => false, 'message' => 'Тренер не найден']));
    }
    
    // Разделяем данные пользователя и тренера
    $userData = [
        'id' => $trainerData['user_id'],
        'first_name' => $trainerData['first_name'],
        'last_name' => $trainerData['last_name'],
        'email' => $trainerData['email'],
        'phone' => $trainerData['phone'],
        'role' => $trainerData['user_role']
    ];
    
    // Удаляем данные пользователя из данных тренера
    unset($trainerData['first_name']);
    unset($trainerData['last_name']);
    unset($trainerData['email']);
    unset($trainerData['phone']);
    unset($trainerData['user_role']);
    
    // Получаем специализации тренера
    $specializationsStmt = $pdo->prepare("
        SELECT s.id, s.name 
        FROM specializations s
        JOIN trainer_specializations ts ON s.id = ts.specialization_id
        WHERE ts.trainer_id = ?
    ");
    $specializationsStmt->execute([$trainerId]);
    $specializations = $specializationsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Получаем образование тренера
    $educationStmt = $pdo->prepare("
        SELECT id, description 
        FROM trainer_education 
        WHERE trainer_id = ? 
        ORDER BY created_at DESC
    ");
    $educationStmt->execute([$trainerId]);
    $education = $educationStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Получаем сертификаты тренера
    $certificatesStmt = $pdo->prepare("
        SELECT id, name 
        FROM trainer_certificates 
        WHERE trainer_id = ? 
        ORDER BY created_at DESC
    ");
    $certificatesStmt->execute([$trainerId]);
    $certificates = $certificatesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Получаем статистику по тренировкам
    $statsStmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_sessions,
            COUNT(DISTINCT user_id) as unique_clients,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_sessions
        FROM training_sessions 
        WHERE trainer_id = ?
    ");
    $statsStmt->execute([$trainerId]);
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
    
    // Получаем средний рейтинг
    $ratingStmt = $pdo->prepare("
        SELECT AVG(rating) as avg_rating, COUNT(*) as reviews_count
        FROM trainer_reviews
        WHERE trainer_id = ?
    ");
    $ratingStmt->execute([$trainerId]);
    $rating = $ratingStmt->fetch(PDO::FETCH_ASSOC);
    
    // Формируем объект с данными
    $result = [
        'success' => true,
        'trainer' => $trainerData,
        'user' => $userData,
        'specializations' => $specializations,
        'education' => $education,
        'certificates' => $certificates,
        'stats' => $stats,
        'rating' => $rating
    ];
    
    header('Content-Type: application/json');
    exit(json_encode($result));
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    header('Content-Type: application/json');
    exit(json_encode(['success' => false, 'message' => 'Ошибка при получении данных тренера']));
} 