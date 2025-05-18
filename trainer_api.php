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
        // Query to get all active trainers
        $query = "
            SELECT t.id, CONCAT(u.first_name, ' ', u.last_name) as name, 
                   t.specialization, t.experience_years, t.photo_url
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
                ['id' => 1, 'name' => 'Иванов Иван', 'specialization' => 'Силовые тренировки'],
                ['id' => 2, 'name' => 'Петрова Мария', 'specialization' => 'Йога, пилатес'],
                ['id' => 3, 'name' => 'Сидоров Алексей', 'specialization' => 'Кроссфит'],
                ['id' => 4, 'name' => 'Козлова Анна', 'specialization' => 'Плавание']
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
        // Map training type to specialization
        $specialization = '';
        switch ($trainingType) {
            case 'personal':
                $specialization = 'Персональные тренировки';
                break;
            case 'group':
                $specialization = 'Групповые занятия';
                break;
            case 'pool':
                $specialization = 'Плавание';
                break;
            case 'gym':
                $specialization = 'Тренажерный зал';
                break;
        }
        
        // Query to get trainers by specialization
        $query = "
            SELECT t.id, CONCAT(u.first_name, ' ', u.last_name) as name, 
                   t.specialization, t.experience_years, t.photo_url
            FROM trainers t
            JOIN users u ON t.user_id = u.id
            JOIN trainer_specializations ts ON t.id = ts.trainer_id
            JOIN specializations s ON ts.specialization_id = s.id
            WHERE t.is_active = 1 AND s.name LIKE ?
            ORDER BY name
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute(["%$specialization%"]);
        $trainers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($trainers) > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Тренеры по специализации получены успешно',
                'trainers' => $trainers
            ]);
        } else {
            // If no trainers found, return demo data based on training type
            $demoTrainers = [];
            
            switch ($trainingType) {
                case 'personal':
                    $demoTrainers = [
                        ['id' => 1, 'name' => 'Иван Иванов', 'specialization' => 'Персональные тренировки'],
                        ['id' => 2, 'name' => 'Петр Петров', 'specialization' => 'Персональные тренировки']
                    ];
                    break;
                case 'group':
                    $demoTrainers = [
                        ['id' => 3, 'name' => 'Анна Сидорова', 'specialization' => 'Групповые занятия'],
                        ['id' => 4, 'name' => 'Мария Кузнецова', 'specialization' => 'Групповые занятия']
                    ];
                    break;
                case 'pool':
                    $demoTrainers = [
                        ['id' => 5, 'name' => 'Алексей Морозов', 'specialization' => 'Плавание'],
                        ['id' => 6, 'name' => 'Екатерина Волкова', 'specialization' => 'Плавание']
                    ];
                    break;
                case 'gym':
                    $demoTrainers = [
                        ['id' => 7, 'name' => 'Дмитрий Соколов', 'specialization' => 'Тренажерный зал'],
                        ['id' => 8, 'name' => 'Сергей Новиков', 'specialization' => 'Тренажерный зал']
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