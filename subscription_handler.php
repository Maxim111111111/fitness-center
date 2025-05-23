<?php
session_start();
require_once('database/config.php');

// Проверка авторизации
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php?redirect=subscriptions.php");
    exit();
}

// Определяем тип запроса
$action = $_POST['action'] ?? '';
$userId = $_SESSION['user_id'];

switch ($action) {
    case 'purchase':
        // Обработка покупки абонемента
        if (empty($_POST['subscription_id'])) {
            header("Location: subscriptions.php?error=invalid_subscription");
            exit();
        }
        
        $subscriptionId = (int)$_POST['subscription_id'];
        
        try {
            // Начинаем транзакцию
            $pdo->beginTransaction();
            
            // Получаем данные выбранного абонемента
            $stmt = $pdo->prepare("SELECT * FROM subscriptions WHERE id = ? AND is_active = 1");
            $stmt->execute([$subscriptionId]);
            $subscription = $stmt->fetch();
            
            if (!$subscription) {
                throw new Exception("Выбранный абонемент не существует или недоступен");
            }
            
            // Проверяем, есть ли у пользователя активный абонемент того же типа
            $checkStmt = $pdo->prepare("
                SELECT * FROM user_subscriptions 
                WHERE user_id = ? AND subscription_id = ? AND status = 'active'
                AND end_date >= CURDATE()
            ");
            $checkStmt->execute([$userId, $subscriptionId]);
            $existingSubscription = $checkStmt->fetch();
            
            // Если есть активный абонемент, продлеваем его
            if ($existingSubscription) {
                // Продлеваем срок действия
                $newEndDate = date('Y-m-d', strtotime($existingSubscription['end_date'] . ' + ' . $subscription['duration_days'] . ' days'));
                
                // Обновляем количество оставшихся тренировок, если они ограничены
                $remainingSessions = $existingSubscription['remaining_sessions'];
                if ($subscription['sessions_count'] !== null && $remainingSessions !== null) {
                    $remainingSessions += $subscription['sessions_count'];
                } elseif ($subscription['sessions_count'] !== null) {
                    $remainingSessions = $subscription['sessions_count'];
                }
                
                // Обновляем абонемент
                $updateStmt = $pdo->prepare("
                    UPDATE user_subscriptions 
                    SET end_date = ?, remaining_sessions = ?
                    WHERE id = ?
                ");
                $updateStmt->execute([$newEndDate, $remainingSessions, $existingSubscription['id']]);
                
                $subscriptionAction = 'extended';
            } else {
                // Создаем новый абонемент
                $startDate = date('Y-m-d'); // Сегодня
                $endDate = date('Y-m-d', strtotime($startDate . ' + ' . $subscription['duration_days'] . ' days'));
                
                // Логирование для отладки
                error_log("Создание нового абонемента для пользователя ID: $userId");
                error_log("Тип абонемента: {$subscription['name']} (ID: {$subscription['id']})");
                error_log("sessions_count из таблицы subscriptions: " . var_export($subscription['sessions_count'], true));
                
                $insertStmt = $pdo->prepare("
                    INSERT INTO user_subscriptions (
                        user_id, subscription_id, start_date, end_date, 
                        remaining_sessions, status, created_at
                    ) VALUES (?, ?, ?, ?, ?, 'active', NOW())
                ");
                $insertStmt->execute([
                    $userId, 
                    $subscriptionId, 
                    $startDate, 
                    $endDate, 
                    $subscription['sessions_count']
                ]);
                
                // Получаем ID созданного абонемента
                $newSubscriptionId = $pdo->lastInsertId();
                
                // Проверяем, что значение remaining_sessions установлено корректно
                $checkStmt = $pdo->prepare("SELECT remaining_sessions FROM user_subscriptions WHERE id = ?");
                $checkStmt->execute([$newSubscriptionId]);
                $remainingSessionsValue = $checkStmt->fetchColumn();
                
                error_log("Новый абонемент создан с ID: $newSubscriptionId");
                error_log("Установленное значение remaining_sessions: " . var_export($remainingSessionsValue, true));
                
                $subscriptionAction = 'purchased';
            }
            
            // Создаем запись о платеже
            $paymentStmt = $pdo->prepare("
                INSERT INTO payments (
                    user_id, subscription_id, amount, payment_method, 
                    status, payment_date, notes
                ) VALUES (?, ?, ?, ?, 'completed', NOW(), ?)
            ");
            $paymentStmt->execute([
                $userId,
                $subscriptionId,
                $subscription['price'],
                'card', // По умолчанию оплата картой, можно изменить
                'Оплата абонемента ' . $subscription['name']
            ]);
            
            // Создаем уведомление для пользователя
            $notificationTitle = $subscriptionAction === 'purchased' 
                ? 'Абонемент успешно приобретен' 
                : 'Абонемент успешно продлен';
                
            $notificationMessage = $subscriptionAction === 'purchased'
                ? 'Вы успешно приобрели абонемент "' . $subscription['name'] . '". Срок действия: до ' . date('d.m.Y', strtotime($endDate)) . '.'
                : 'Вы успешно продлили абонемент "' . $subscription['name'] . '". Новый срок действия: до ' . date('d.m.Y', strtotime($newEndDate)) . '.';
            
            $notificationStmt = $pdo->prepare("
                INSERT INTO notifications (
                    user_id, title, message, type, created_at
                ) VALUES (?, ?, ?, 'system', NOW())
            ");
            $notificationStmt->execute([
                $userId,
                $notificationTitle,
                $notificationMessage
            ]);
            
            // Завершаем транзакцию
            $pdo->commit();
            
            // Перенаправляем на страницу профиля с сообщением об успехе
            header("Location: profile.php?tab=profile-subscription&success=" . $subscriptionAction);
            exit();
            
        } catch (Exception $e) {
            // Откатываем транзакцию в случае ошибки
            $pdo->rollBack();
            error_log("Subscription purchase error: " . $e->getMessage());
            header("Location: subscriptions.php?error=purchase&message=" . urlencode($e->getMessage()));
            exit();
        }
        break;
        
    case 'cancel':
        // Обработка отмены абонемента
        if (empty($_POST['subscription_id'])) {
            header("Location: profile.php?tab=profile-subscription&error=invalid_subscription");
            exit();
        }
        
        $userSubscriptionId = (int)$_POST['subscription_id'];
        
        try {
            // Проверяем, принадлежит ли абонемент пользователю
            $stmt = $pdo->prepare("
                SELECT * FROM user_subscriptions 
                WHERE id = ? AND user_id = ? AND status = 'active'
            ");
            $stmt->execute([$userSubscriptionId, $userId]);
            $userSubscription = $stmt->fetch();
            
            if (!$userSubscription) {
                throw new Exception("Абонемент не найден или не может быть отменен");
            }
            
            // Обновляем статус абонемента
            $updateStmt = $pdo->prepare("
                UPDATE user_subscriptions 
                SET status = 'cancelled' 
                WHERE id = ?
            ");
            $updateStmt->execute([$userSubscriptionId]);
            
            // Создаем уведомление для пользователя
            $notificationStmt = $pdo->prepare("
                INSERT INTO notifications (
                    user_id, title, message, type, created_at
                ) VALUES (?, ?, ?, 'system', NOW())
            ");
            $notificationStmt->execute([
                $userId,
                'Абонемент отменен',
                'Ваш абонемент был отменен. Если у вас есть вопросы, пожалуйста, обратитесь к администратору.'
            ]);
            
            // Перенаправляем на страницу профиля с сообщением об успехе
            header("Location: profile.php?tab=profile-subscription&success=cancelled");
            exit();
            
        } catch (Exception $e) {
            error_log("Subscription cancellation error: " . $e->getMessage());
            header("Location: profile.php?tab=profile-subscription&error=cancel&message=" . urlencode($e->getMessage()));
            exit();
        }
        break;
        
    default:
        // Неизвестное действие
        header("Location: subscriptions.php");
        exit();
}
?> 