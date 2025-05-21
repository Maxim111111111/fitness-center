<?php
session_start();
require_once('database/config.php');

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Необходимо авторизоваться для записи на тренировку']);
    exit();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Метод не поддерживается']);
    exit();
}

// Get user ID from session
$userId = $_SESSION['user_id'];

// Get form data
$name = isset($_POST['name']) ? sanitize($_POST['name']) : '';
$phone = isset($_POST['phone']) ? sanitize($_POST['phone']) : '';
$email = isset($_POST['email']) ? sanitize($_POST['email']) : '';
$trainingType = isset($_POST['training-type']) ? sanitize($_POST['training-type']) : '';
$date = isset($_POST['date']) ? sanitize($_POST['date']) : '';
$time = isset($_POST['time']) ? sanitize($_POST['time']) : '';
$trainerId = isset($_POST['trainer']) ? (int)$_POST['trainer'] : 0;
$comment = isset($_POST['comment']) ? sanitize($_POST['comment']) : '';

// Validate required fields
if (empty($name) || empty($phone) || empty($email) || empty($trainingType) || empty($date) || empty($time) || empty($trainerId)) {
    echo json_encode(['success' => false, 'message' => 'Пожалуйста, заполните все обязательные поля']);
    exit();
}

// Validate email format
if (!isValidEmail($email)) {
    echo json_encode(['success' => false, 'message' => 'Пожалуйста, введите корректный email-адрес']);
    exit();
}

// Validate date (must be in the future)
$currentDate = date('Y-m-d');
if ($date < $currentDate) {
    echo json_encode(['success' => false, 'message' => 'Дата тренировки должна быть не ранее завтрашнего дня']);
    exit();
}

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Check if the trainer exists
    $checkTrainer = $pdo->prepare("SELECT COUNT(*) FROM trainers WHERE id = ?");
    $checkTrainer->execute([$trainerId]);
    $trainerExists = (int)$checkTrainer->fetchColumn() > 0;
    
    if (!$trainerExists) {
        // If the trainer doesn't exist in the database, use a default trainer or fallback
        // Get the first available trainer from the database
        $getDefaultTrainer = $pdo->query("SELECT id FROM trainers LIMIT 1");
        $defaultTrainer = $getDefaultTrainer->fetch();
        
        if ($defaultTrainer) {
            $trainerId = $defaultTrainer['id'];
        } else {
            // No trainers in the database, cannot proceed
            throw new Exception("Не найдено ни одного тренера в базе данных");
        }
    }
    
    // Check if the selected time slot is available
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM training_sessions 
        WHERE session_date = ? AND start_time = ? AND trainer_id = ? AND status != 'cancelled'
    ");
    $stmt->execute([$date, $time, $trainerId]);
    $result = $stmt->fetch();
    
    if ($result['count'] > 0) {
        echo json_encode(['success' => false, 'message' => 'Выбранное время уже занято. Пожалуйста, выберите другое время']);
        exit();
    }
    
    // Calculate end time (assuming 1 hour sessions)
    $endTime = date('H:i:s', strtotime($time) + 60*60);
    
    // Check if user already exists (based on email)
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    // If user record doesn't exist, create it
    if (!$user) {
        // We already have the user ID from session
        // Update user information if needed
        $stmt = $pdo->prepare("
            UPDATE users SET 
            phone = ?,
            first_name = ?
            WHERE id = ?
        ");
        $stmt->execute([$phone, $name, $userId]);
    }
    
    // Generate booking ID (random string)
    $bookingId = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
    
    // Insert training session
    $stmt = $pdo->prepare("
        INSERT INTO training_sessions 
        (user_id, trainer_id, service_id, session_date, start_time, end_time, status, notes, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, NOW(), NOW())
    ");
    
    // Map training type to service_id
    $serviceId = 1; // Default: Personal training
    switch ($trainingType) {
        case 'personal': $serviceId = 1; break;
        case 'group': $serviceId = 2; break;
        case 'pool': $serviceId = 3; break;
        case 'gym': $serviceId = 4; break;
    }
    
    $stmt->execute([$userId, $trainerId, $serviceId, $date, $time, $endTime, $comment]);
    $trainingId = $pdo->lastInsertId();
    
    // Add notification for the user
    $stmt = $pdo->prepare("
        INSERT INTO notifications 
        (user_id, title, message, type, created_at, is_read) 
        VALUES (?, 'Запись на тренировку', ?, 'booking', NOW(), 0)
    ");
    
    $message = "Вы успешно записались на тренировку " . date('d.m.Y', strtotime($date)) . " в " . substr($time, 0, 5) . ". Номер брони: " . $bookingId;
    $stmt->execute([$userId, $message]);
    
    // Log the booking action
    $stmt = $pdo->prepare("
        INSERT INTO audit_log 
        (user_id, entity_type, entity_id, action, details, ip_address, user_agent, created_at) 
        VALUES (?, 'training', ?, 'create', ?, ?, ?, NOW())
    ");
    
    $details = "Training session booked: " . $trainingType . " on " . $date . " at " . $time;
    $stmt->execute([
        $userId, 
        $trainingId, 
        $details, 
        $_SERVER['REMOTE_ADDR'] ?? 'unknown', 
        $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ]);
    
    // Commit transaction
    $pdo->commit();
    
    // Return success response
    echo json_encode([
        'success' => true, 
        'message' => 'Вы успешно записались на тренировку!', 
        'bookingId' => $bookingId
    ]);
    
} catch (PDOException $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Произошла ошибка при обработке запроса. Пожалуйста, попробуйте позже.']);
} 