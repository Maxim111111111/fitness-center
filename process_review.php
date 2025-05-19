<?php
// Include database configuration
require_once 'database/config.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'data' => null
];

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get and sanitize form data
        $name = isset($_POST['name']) ? sanitize($_POST['name']) : '';
        $email = isset($_POST['email']) ? sanitize($_POST['email']) : '';
        $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
        $text = isset($_POST['text']) ? sanitize($_POST['text']) : '';

        // Validate data
        if (empty($name)) {
            throw new Exception('Имя обязательно для заполнения');
        }

        if (empty($email) || !isValidEmail($email)) {
            throw new Exception('Введите корректный email');
        }

        if ($rating < 1 || $rating > 5) {
            throw new Exception('Оценка должна быть от 1 до 5');
        }

        if (empty($text)) {
            throw new Exception('Текст отзыва обязателен для заполнения');
        }

        // Prepare and execute SQL statement
        $stmt = $pdo->prepare("
            INSERT INTO reviews (name, email, rating, text, status) 
            VALUES (?, ?, ?, ?, 'pending')
        ");
        
        $stmt->execute([$name, $email, $rating, $text]);

        // Set success response
        $response['success'] = true;
        $response['message'] = 'Спасибо! Ваш отзыв успешно отправлен и будет опубликован после модерации.';
        
    } catch (Exception $e) {
        // Set error response
        $response['message'] = $e->getMessage();
    }
} else {
    // Not a POST request
    $response['message'] = 'Неверный метод запроса';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response); 