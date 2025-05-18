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
    case 'get_available_times':
        getAvailableTimes();
        break;
    
    case 'get_trainers':
        getTrainers();
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Неизвестное действие'
        ]);
        break;
}

/**
 * Get available time slots for a specific date
 */
function getAvailableTimes() {
    global $pdo;
    
    // Get date from request
    $date = isset($_GET['date']) ? sanitize($_GET['date']) : null;
    
    // Validate date
    if (!$date) {
        echo json_encode([
            'success' => false,
            'message' => 'Дата не указана'
        ]);
        return;
    }
    
    // Validate that it's a future date
    $today = date('Y-m-d');
    if ($date < $today) {
        echo json_encode([
            'success' => false,
            'message' => 'Выберите дату начиная с сегодняшнего дня'
        ]);
        return;
    }
    
    try {
        // Get list of already booked times
        $bookedTimes = [];
        $query = "SELECT start_time FROM training_sessions WHERE session_date = ? AND status != 'cancelled'";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$date]);
        $bookedSessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($bookedSessions as $session) {
            // Extract only the whole hour (8:00, 9:00, etc.)
            $bookedTimes[] = substr($session['start_time'], 0, 5);
        }
        
        // Generate all available time slots (8:00 to 22:00, whole hours only)
        $allTimeSlots = [];
        for ($hour = 8; $hour < 22; $hour++) {
            $timeSlot = sprintf('%02d:00', $hour);
            $allTimeSlots[] = $timeSlot;
        }
        
        // Filter out booked times
        $availableTimes = array_diff($allTimeSlots, $bookedTimes);
        
        // If today, filter out past times
        if ($date == $today) {
            $currentHour = (int)date('H');
            $availableTimes = array_filter($availableTimes, function($time) use ($currentHour) {
                $timeHour = (int)substr($time, 0, 2);
                return $timeHour > $currentHour;
            });
        }
        
        // Re-index array
        $availableTimes = array_values($availableTimes);
        
        // Sort times in ascending order
        sort($availableTimes);
        
        // Return response
        if (count($availableTimes) > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Доступное время получено успешно',
                'times' => $availableTimes
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'На выбранную дату нет свободного времени'
            ]);
        }
    } catch (Exception $e) {
        error_log("Error in getAvailableTimes: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Ошибка при получении доступного времени'
        ]);
    }
}

/**
 * Get all trainers
 */
function getTrainers() {
    global $pdo;
    
    try {
        // Get all active trainers
        $query = "SELECT t.id, CONCAT(u.first_name, ' ', u.last_name) as name, t.specialization 
                 FROM trainers t 
                 JOIN users u ON t.user_id = u.id 
                 WHERE t.is_active = 1 
                 ORDER BY name";
        $stmt = $pdo->query($query);
        
        if ($stmt) {
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
        } else {
            throw new Exception("Query failed");
        }
    } catch (Exception $e) {
        error_log("Error in getTrainers: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Ошибка при получении тренеров'
        ]);
    }
}

// Функция для отправки JSON ответа
function sendResponse($success, $message, $data = []) {
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if (!empty($data)) {
        $response = array_merge($response, $data);
    }
    
    echo json_encode($response);
    exit;
} 