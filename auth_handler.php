<?php
session_start();
require_once('database/config.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Метод не поддерживается']);
    exit();
}

$response = ['success' => false, 'message' => ''];

$email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'Email и пароль обязательны']);
    exit();
}

try {
    // Обновленный запрос в соответствии с структурой базы данных
    $stmt = $pdo->prepare("SELECT id, first_name, email, password_hash, role FROM users WHERE email = ? AND is_active = TRUE");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // Создаем сессию
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['first_name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];

        // Обновляем время последнего входа
        $updateStmt = $pdo->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
        $updateStmt->execute([$user['id']]);

        $response = [
            'success' => true,
            'message' => 'Успешная авторизация',
            'redirect' => $user['role'] === 'admin' || $user['role'] === 'manager' ? 'admin/index.php' : 'profile.php'
        ];
    } else {
        $response = ['success' => false, 'message' => 'Неверный email или пароль'];
    }
} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    $response = ['success' => false, 'message' => 'Ошибка при авторизации'];
}

echo json_encode($response); 