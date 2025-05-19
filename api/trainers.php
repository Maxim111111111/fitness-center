<?php
session_start();
require_once('../database/config.php');

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Обработка запроса
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'get_trainer':
            // Получение данных тренера по ID
            if (isset($_GET['id'])) {
                $trainerId = (int)$_GET['id'];
                
                $stmt = $pdo->prepare("
                    SELECT t.*, u.first_name, u.last_name, u.email 
                    FROM trainers t
                    LEFT JOIN users u ON t.user_id = u.id
                    WHERE t.id = :id
                ");
                $stmt->bindParam(':id', $trainerId);
                $stmt->execute();
                
                $trainer = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($trainer) {
                    echo json_encode([
                        'success' => true,
                        'trainer' => $trainer
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Тренер не найден'
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'ID тренера не указан'
                ]);
            }
            break;
            
        case 'get_trainers':
            // Получение списка всех тренеров
            $stmt = $pdo->query("
                SELECT t.*, u.first_name, u.last_name, u.email 
                FROM trainers t
                LEFT JOIN users u ON t.user_id = u.id
                ORDER BY t.id DESC
            ");
            
            $trainers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'trainers' => $trainers
            ]);
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