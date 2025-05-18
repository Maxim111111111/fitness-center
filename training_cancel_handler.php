<?php
session_start();
require_once('database/config.php');

// Проверяем, авторизован ли пользователь
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Необходимо авторизоваться']);
    exit;
}

// Убедимся, что это POST-запрос и id тренировки указан
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['training_id'])) {
    echo json_encode(['success' => false, 'message' => 'Неверный запрос']);
    exit;
}

// Получаем ID тренировки и ID пользователя
$trainingId = (int)$_POST['training_id'];
$userId = $_SESSION['user_id'];

try {
    // Начинаем транзакцию
    $pdo->beginTransaction();
    
    // Проверяем, существует ли тренировка и принадлежит ли она данному пользователю
    $stmt = $pdo->prepare("SELECT * FROM training_sessions WHERE id = ? AND user_id = ?");
    $stmt->execute([$trainingId, $userId]);
    $training = $stmt->fetch();
    
    if (!$training) {
        echo json_encode(['success' => false, 'message' => 'Тренировка не найдена или не принадлежит вам']);
        exit;
    }
    
    // Проверяем, что тренировка еще не отменена и не завершена
    if ($training['status'] === 'cancelled') {
        echo json_encode(['success' => false, 'message' => 'Тренировка уже отменена']);
        exit;
    }
    
    if ($training['status'] === 'completed') {
        echo json_encode(['success' => false, 'message' => 'Нельзя отменить завершенную тренировку']);
        exit;
    }
    
    // Обновляем статус тренировки
    $stmt = $pdo->prepare("UPDATE training_sessions SET status = 'cancelled', updated_at = NOW() WHERE id = ?");
    $stmt->execute([$trainingId]);
    
    // Записываем в лог операцию
    $stmt = $pdo->prepare("
        INSERT INTO audit_log (user_id, entity_type, entity_id, action, details, ip_address, user_agent) 
        VALUES (?, 'training', ?, 'update', 'Training session cancelled', ?, ?)
    ");
    $stmt->execute([
        $userId, 
        $trainingId, 
        $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
        $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
    ]);
    
    // Отправляем уведомление пользователю
    $stmt = $pdo->prepare("
        INSERT INTO notifications (user_id, title, message, type, created_at) 
        VALUES (?, 'Тренировка отменена', 'Ваша тренировка была успешно отменена', 'booking', NOW())
    ");
    $stmt->execute([$userId]);
    
    // Если всё прошло успешно - фиксируем транзакцию
    $pdo->commit();
    
    echo json_encode(['success' => true, 'message' => 'Тренировка успешно отменена']);
    
} catch (PDOException $e) {
    // В случае ошибки откатываем транзакцию
    $pdo->rollBack();
    error_log("Error cancelling training: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Произошла ошибка при отмене тренировки']);
} 