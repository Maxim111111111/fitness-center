<?php
session_start();
require_once('../database/config.php');

// Проверка авторизации
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'manager'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Обработка запроса
header('Content-Type: application/json');

if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'get_available_users':
            // Получение списка пользователей, которые могут быть тренерами
            // Исключаем тех, кто уже назначен тренером
            $stmt = $pdo->query("
                SELECT u.id, u.first_name, u.last_name, u.email 
                FROM users u
                LEFT JOIN trainers t ON u.id = t.user_id
                WHERE t.id IS NULL
                ORDER BY u.first_name, u.last_name
            ");
            
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'users' => $users
            ]);
            break;
            
        case 'get_user':
            // Получение данных пользователя по ID
            if (isset($_GET['id'])) {
                $userId = (int)$_GET['id'];
                
                $stmt = $pdo->prepare("
                    SELECT id, first_name, last_name, email, role, created_at 
                    FROM users
                    WHERE id = :id
                ");
                $stmt->bindParam(':id', $userId);
                $stmt->execute();
                
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    echo json_encode([
                        'success' => true,
                        'user' => $user
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Пользователь не найден'
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'ID пользователя не указан'
                ]);
            }
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Неизвестное действие'
            ]);
            break;
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Действие не указано'
    ]);
} 