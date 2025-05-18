<?php
session_start();
require_once('database/config.php');

header('Content-Type: application/json');

// Проверка авторизации
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Требуется авторизация']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Метод не поддерживается']);
    exit();
}

$response = ['success' => false, 'message' => ''];
$userId = $_SESSION['user_id'];

// Определяем тип запроса
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'update_profile':
        // Получение данных из POST-запроса
        $firstName = sanitize($_POST['first_name'] ?? '');
        $lastName = sanitize($_POST['last_name'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $birthDate = sanitize($_POST['birth_date'] ?? '');
        $gender = sanitize($_POST['gender'] ?? '');

        // Базовая валидация
        if (empty($firstName)) {
            echo json_encode(['success' => false, 'message' => 'Имя обязательно для заполнения']);
            exit();
        }

        try {
            // Обновление данных пользователя напрямую в таблицу users
            $userStmt = $pdo->prepare("
                UPDATE users 
                SET first_name = ?, 
                    last_name = ?, 
                    phone = ?,
                    birthdate = ?,
                    gender = ?,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            
            $userStmt->execute([
                $firstName, 
                $lastName, 
                $phone, 
                $birthDate ?: null, 
                $gender ?: null, 
                $userId
            ]);
            
            // Обновляем сессию
            $_SESSION['first_name'] = $firstName;
            $_SESSION['last_name'] = $lastName;
            
            $response = [
                'success' => true,
                'message' => 'Профиль успешно обновлен'
            ];
        } catch (PDOException $e) {
            error_log("Profile update error: " . $e->getMessage());
            $response = ['success' => false, 'message' => 'Ошибка при обновлении профиля: ' . $e->getMessage()];
        }
        break;
        
    case 'update_params':
        // Получение данных о физических параметрах
        $height = filter_var($_POST['height'] ?? '', FILTER_VALIDATE_FLOAT);
        $weight = filter_var($_POST['weight'] ?? '', FILTER_VALIDATE_FLOAT);
        
        try {
            // Обновляем физические параметры напрямую в таблице users
            $profileStmt = $pdo->prepare("
                UPDATE users 
                SET height = ?, 
                    weight = ?,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            
            $profileStmt->execute([
                $height ?: null, 
                $weight ?: null, 
                $userId
            ]);
            
            $response = [
                'success' => true,
                'message' => 'Физические параметры успешно обновлены'
            ];
        } catch (PDOException $e) {
            error_log("Physical params update error: " . $e->getMessage());
            $response = ['success' => false, 'message' => 'Ошибка при обновлении физических параметров: ' . $e->getMessage()];
        }
        break;

    case 'update_avatar':
        // Обработка загрузки аватара
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'Ошибка при загрузке файла']);
            exit();
        }
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($fileInfo, $_FILES['avatar']['tmp_name']);
        finfo_close($fileInfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            echo json_encode(['success' => false, 'message' => 'Недопустимый тип файла. Разрешены только изображения (JPEG, PNG, GIF, WebP)']);
            exit();
        }
        
        $maxFileSize = 5 * 1024 * 1024; // 5 МБ
        if ($_FILES['avatar']['size'] > $maxFileSize) {
            echo json_encode(['success' => false, 'message' => 'Размер файла превышает допустимый (5 МБ)']);
            exit();
        }
        
        // Создаем директорию для хранения аватаров, если её нет
        $uploadDir = 'uploads/avatars/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Генерируем уникальное имя файла
        $fileExtension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $newFileName = 'avatar_' . $userId . '_' . time() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $newFileName;
        
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadPath)) {
            try {
                // Получаем текущий аватар пользователя для удаления
                $stmt = $pdo->prepare("SELECT avatar_url FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch();
                $oldAvatar = $user['avatar_url'] ?? '';
                
                // Обновляем путь к аватару в базе данных
                $updateStmt = $pdo->prepare("UPDATE users SET avatar_url = ? WHERE id = ?");
                $updateStmt->execute([$uploadPath, $userId]);
                
                // Удаляем старый аватар, если он существует и не является дефолтным
                if (!empty($oldAvatar) && file_exists($oldAvatar) && !strstr($oldAvatar, 'avatar-placeholder')) {
                    unlink($oldAvatar);
                }
                
                $response = [
                    'success' => true,
                    'message' => 'Аватар успешно обновлен',
                    'avatar_url' => $uploadPath
                ];
            } catch (PDOException $e) {
                error_log("Avatar update error: " . $e->getMessage());
                $response = ['success' => false, 'message' => 'Ошибка при обновлении аватара в базе данных: ' . $e->getMessage()];
                
                // Удаляем загруженный файл, если произошла ошибка в базе
                if (file_exists($uploadPath)) {
                    unlink($uploadPath);
                }
            }
        } else {
            $response = ['success' => false, 'message' => 'Ошибка при сохранении файла'];
        }
        break;
        
    case 'change_password':
        // Получение данных из POST-запроса
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Базовая валидация
        if (empty($currentPassword)) {
            echo json_encode(['success' => false, 'message' => 'Текущий пароль обязателен']);
            exit();
        }
        
        if (empty($newPassword)) {
            echo json_encode(['success' => false, 'message' => 'Новый пароль обязателен']);
            exit();
        }
        
        if (strlen($newPassword) < 6) {
            echo json_encode(['success' => false, 'message' => 'Новый пароль должен содержать минимум 6 символов']);
            exit();
        }
        
        if ($newPassword !== $confirmPassword) {
            echo json_encode(['success' => false, 'message' => 'Пароли не совпадают']);
            exit();
        }
        
        try {
            // Получаем текущий хеш пароля пользователя
            $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            if (!$user) {
                echo json_encode(['success' => false, 'message' => 'Пользователь не найден']);
                exit();
            }
            
            // Проверяем текущий пароль
            if (!password_verify($currentPassword, $user['password_hash'])) {
                echo json_encode(['success' => false, 'message' => 'Текущий пароль неверен']);
                exit();
            }
            
            // Хешируем новый пароль
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Обновляем пароль
            $updateStmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $updateStmt->execute([$newPasswordHash, $userId]);
            
            $response = [
                'success' => true,
                'message' => 'Пароль успешно изменен'
            ];
        } catch (PDOException $e) {
            error_log("Password change error: " . $e->getMessage());
            $response = ['success' => false, 'message' => 'Ошибка при изменении пароля: ' . $e->getMessage()];
        }
        break;
        
    default:
        $response = ['success' => false, 'message' => 'Неизвестное действие'];
        break;
}

echo json_encode($response); 