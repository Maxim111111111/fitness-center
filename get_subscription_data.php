<?php
// Файл для получения актуальных данных о количестве оставшихся тренировок
session_start();
require_once('database/config.php');

// Проверка авторизации
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Требуется авторизация']);
    exit();
}

$userId = $_SESSION['user_id'];
$response = ['success' => false, 'message' => 'Данные не найдены'];

try {
    // Логируем запрос
    error_log("Запрос данных абонемента для пользователя ID: $userId");
    
    // Получаем активный абонемент пользователя
    $stmt = $pdo->prepare("
        SELECT us.*, s.name, s.description, s.price, s.duration_days, s.sessions_count
        FROM user_subscriptions us
        JOIN subscriptions s ON us.subscription_id = s.id
        WHERE us.user_id = ? AND us.status = 'active'
        AND us.end_date >= CURDATE()
        ORDER BY us.end_date DESC
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    $activeSubscription = $stmt->fetch();
    
    if ($activeSubscription) {
        error_log("Найден активный абонемент ID: {$activeSubscription['id']}, remaining_sessions: {$activeSubscription['remaining_sessions']}");
        
        // Проверяем, есть ли завершенные тренировки, которые еще не учтены
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as completed_count, MAX(updated_at) as last_updated
            FROM training_sessions 
            WHERE user_id = ? 
            AND status = 'completed' 
            AND updated_at > ?
        ");
        $stmt->execute([$userId, $activeSubscription['updated_at'] ?? '1970-01-01']);
        $result = $stmt->fetch();
        $completedCount = $result['completed_count'];
        $lastUpdated = $result['last_updated'];
        
        error_log("Количество завершенных тренировок после последнего обновления: $completedCount, последнее обновление: $lastUpdated");
        error_log("Последнее обновление абонемента: {$activeSubscription['updated_at']}");
        
        // Если есть завершенные тренировки после последнего обновления абонемента
        if ($completedCount > 0 && $activeSubscription['remaining_sessions'] !== null) {
            $newRemaining = max(0, $activeSubscription['remaining_sessions'] - $completedCount);
            error_log("Обновляем remaining_sessions с {$activeSubscription['remaining_sessions']} на $newRemaining");
            
            // Обновляем количество оставшихся тренировок
            $updateStmt = $pdo->prepare("
                UPDATE user_subscriptions 
                SET remaining_sessions = ?, 
                    updated_at = NOW()
                WHERE id = ?
            ");
            $updateStmt->execute([$newRemaining, $activeSubscription['id']]);
            
            // Проверяем, действительно ли обновилось значение
            $rowCount = $updateStmt->rowCount();
            error_log("Количество обновленных строк: $rowCount");
            
            if ($rowCount > 0) {
                // Обновляем данные для отображения
                $activeSubscription['remaining_sessions'] = $newRemaining;
                
                // Получаем обновленное время
                $checkStmt = $pdo->prepare("SELECT updated_at FROM user_subscriptions WHERE id = ?");
                $checkStmt->execute([$activeSubscription['id']]);
                $newUpdatedAt = $checkStmt->fetchColumn();
                
                error_log("Данные абонемента обновлены, новое время обновления: $newUpdatedAt");
            } else {
                error_log("ОШИБКА: Не удалось обновить значение remaining_sessions в базе данных");
            }
        } else if ($completedCount > 0) {
            error_log("Есть завершенные тренировки, но абонемент безлимитный (remaining_sessions = NULL)");
        } else {
            error_log("Нет новых завершенных тренировок после последнего обновления абонемента");
        }
        
        $response = [
            'success' => true,
            'subscription' => [
                'id' => $activeSubscription['id'],
                'name' => $activeSubscription['name'],
                'end_date' => $activeSubscription['end_date'],
                'remaining_sessions' => $activeSubscription['remaining_sessions'],
                'days_left' => ceil((strtotime($activeSubscription['end_date']) - time()) / (60 * 60 * 24)),
                'last_updated' => $activeSubscription['updated_at']
            ]
        ];
    } else {
        error_log("Активный абонемент не найден для пользователя ID: $userId");
    }
} catch (PDOException $e) {
    error_log("Ошибка при получении данных абонемента: " . $e->getMessage());
    $response = ['success' => false, 'message' => 'Ошибка при получении данных'];
}

// Возвращаем результат
header('Content-Type: application/json');
echo json_encode($response);
exit();
?> 