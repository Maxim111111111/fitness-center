<?php
session_start();
require_once('database/config.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Метод не поддерживается']);
    exit();
}

$response = ['success' => false, 'message' => ''];

// Получение данных из POST-запроса
$firstName = sanitize($_POST['name'] ?? '');
$email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
$phone = sanitize($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';
$passwordConfirm = $_POST['password_confirm'] ?? '';

// Базовая валидация
if (empty($firstName)) {
    echo json_encode(['success' => false, 'message' => 'Имя обязательно для заполнения']);
    exit();
}

if (!$email) {
    echo json_encode(['success' => false, 'message' => 'Введите корректный email']);
    exit();
}

if (empty($phone)) {
    echo json_encode(['success' => false, 'message' => 'Телефон обязателен для заполнения']);
    exit();
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Пароль должен содержать минимум 6 символов']);
    exit();
}

if ($password !== $passwordConfirm) {
    echo json_encode(['success' => false, 'message' => 'Пароли не совпадают']);
    exit();
}

try {
    // Проверка, существует ли пользователь с таким email
    $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $checkStmt->execute([$email]);
    
    if ($checkStmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Пользователь с таким email уже существует']);
        exit();
    }
    
    // Хеширование пароля
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    // Добавление пользователя в базу данных - обновлено для соответствия структуре database.sql
    $stmt = $pdo->prepare("
        INSERT INTO users (
            email,
            password_hash,
            first_name,
            last_name,
            phone,
            role,
            is_active,
            last_login,
            created_at
        ) VALUES (?, ?, ?, '', ?, 'user', TRUE, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
    ");
    
    $stmt->execute([
        $email,
        $passwordHash,
        $firstName,
        $phone
    ]);
    
    // Получение ID нового пользователя
    $userId = $pdo->lastInsertId();
    
    // Создаем сессию для автоматического входа
    $_SESSION['user_id'] = $userId;
    $_SESSION['first_name'] = $firstName;
    $_SESSION['username'] = $firstName; // Для совместимости
    $_SESSION['email'] = $email;
    $_SESSION['role'] = 'user';
    $_SESSION['user_logged_in'] = true;
    
    $response = [
        'success' => true,
        'message' => 'Регистрация прошла успешно',
        'redirect' => 'profile.php'
    ];
    
} catch (PDOException $e) {
    error_log("Registration error: " . $e->getMessage());
    $response = ['success' => false, 'message' => 'Ошибка при регистрации: ' . $e->getMessage()];
}

echo json_encode($response); 