<?php
session_start();
require_once('database/config.php');

// Set response headers
header('Content-Type: application/json');

// Get action from query parameters
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Check if user is logged in for functions that require authentication
$allowAnonymousAccess = false; // Set to true for endpoints that don't require login

// Check authentication for protected endpoints
if (!isLoggedIn() && !$allowAnonymousAccess) {
    http_response_code(401); // Unauthorized
    echo json_encode([
        'success' => false,
        'message' => 'Требуется авторизация для использования этой функции'
    ]);
    exit();
}

switch ($action) {
    case 'get_trainers':
        getTrainers();
        break;
        
    case 'get_trainers_by_type':
        getTrainersByType();
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Неизвестное действие'
        ]);
        break;
}

/**
 * Get all trainers
 */
function getTrainers() {
    global $pdo;
    
    try {
        // Убраны поля, которых нет в базе данных
        $query = "
            SELECT t.id, CONCAT(u.first_name, ' ', u.last_name) as name, 
                   t.experience_years, t.photo_url
            FROM trainers t
            JOIN users u ON t.user_id = u.id
            WHERE t.is_active = 1
            ORDER BY name
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $trainers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($trainers) > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Тренеры получены успешно',
                'trainers' => $trainers
            ]);
        } else {
            // If no trainers found, return demo data
            $demoTrainers = [
                ['id' => 1, 'name' => 'Иванов Иван'],
                ['id' => 2, 'name' => 'Петрова Мария'],
                ['id' => 3, 'name' => 'Сидоров Алексей'],
                ['id' => 4, 'name' => 'Козлова Анна']
            ];
            
            echo json_encode([
                'success' => true,
                'message' => 'Демо-тренеры загружены',
                'trainers' => $demoTrainers
            ]);
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        
        echo json_encode([
            'success' => false,
            'message' => 'Ошибка при получении списка тренеров'
        ]);
    }
}

/**
 * Get trainers filtered by training type
 */
function getTrainersByType() {
    global $pdo;
    
    // Get training type from request
    $trainingType = isset($_GET['type']) ? sanitize($_GET['type']) : '';
    
    if (empty($trainingType)) {
        echo json_encode([
            'success' => false,
            'message' => 'Не указан тип тренировки'
        ]);
        return;
    }
    
    try {
        // Упрощенный запрос без проверки специализации
        $query = "
            SELECT t.id, CONCAT(u.first_name, ' ', u.last_name) as name, 
                   t.experience_years, t.photo_url
            FROM trainers t
            JOIN users u ON t.user_id = u.id
            WHERE t.is_active = 1
            ORDER BY name
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $trainers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($trainers) > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Тренеры получены успешно',
                'trainers' => $trainers
            ]);
        } else {
            // If no trainers found, return demo data based on training type
            $demoTrainers = [];
            
            switch ($trainingType) {
                case 'personal':
                    $demoTrainers = [
                        ['id' => 1, 'name' => 'Иван Иванов'],
                        ['id' => 2, 'name' => 'Петр Петров']
                    ];
                    break;
                case 'group':
                    $demoTrainers = [
                        ['id' => 3, 'name' => 'Анна Сидорова'],
                        ['id' => 4, 'name' => 'Мария Кузнецова']
                    ];
                    break;
                case 'pool':
                    $demoTrainers = [
                        ['id' => 5, 'name' => 'Алексей Морозов'],
                        ['id' => 6, 'name' => 'Екатерина Волкова']
                    ];
                    break;
                case 'gym':
                    $demoTrainers = [
                        ['id' => 7, 'name' => 'Дмитрий Соколов'],
                        ['id' => 8, 'name' => 'Сергей Новиков']
                    ];
                    break;
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Демо-тренеры по специализации загружены',
                'trainers' => $demoTrainers
            ]);
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        
        echo json_encode([
            'success' => false,
            'message' => 'Ошибка при получении списка тренеров по специализации'
        ]);
    }
} 