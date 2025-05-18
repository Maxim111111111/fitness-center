<?php
// Start session
session_start();
require_once('database/config.php');

// Check if user is already logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    // Redirect to profile page
    header("Location: profile.php");
    exit();
}

// Handle direct form submission (non-AJAX)
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    $firstName = isset($_POST['name']) ? sanitize($_POST['name']) : '';
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $phone = isset($_POST['phone']) ? sanitize($_POST['phone']) : '';
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    
    // Validation
    if (empty($firstName)) {
        $error = 'Имя обязательно для заполнения';
    } elseif (!$email) {
        $error = 'Введите корректный email';
    } elseif (empty($phone)) {
        $error = 'Телефон обязателен для заполнения';
    } elseif (strlen($password) < 6) {
        $error = 'Пароль должен содержать минимум 6 символов';
    } elseif ($password !== $passwordConfirm) {
        $error = 'Пароли не совпадают';
    } else {
        try {
            // Check if user with this email already exists
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $checkStmt->execute([$email]);
            
            if ($checkStmt->rowCount() > 0) {
                $error = 'Пользователь с таким email уже существует';
            } else {
                // Hash password
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                
                // Add user to database
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
                
                // Get new user ID
                $userId = $pdo->lastInsertId();
                
                // Create session for automatic login
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_logged_in'] = true;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_name'] = $firstName;
                $_SESSION['user_role'] = 'user';
                
                // Create audit log entry
                $auditStmt = $pdo->prepare("
                    INSERT INTO audit_log (user_id, entity_type, entity_id, action, details, ip_address, user_agent, created_at)
                    VALUES (?, 'user', ?, 'register', 'User registered', ?, ?, NOW())
                ");
                $auditStmt->execute([
                    $userId,
                    $userId,
                    $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                    $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
                ]);
                
                // Redirect to profile page
                header("Location: profile.php");
                exit();
            }
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            $error = 'Произошла ошибка при регистрации.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Dela+Gothic+One&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <title>Регистрация | Moreon Fitness</title>
    <link rel="stylesheet" href="style/reset.css">
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="style/auth.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main class="auth-page">
        <div class="container">
            <div class="auth-container">
                <div class="auth-card">
                    <div class="auth-header">
                        <a href="index.php" class="auth-logo">
                            <img src="assets/svg/logo white.svg" alt="Moreon Fitness logo" width="150">
                        </a>
                        <h1 class="auth-title">Регистрация</h1>
                        <p class="auth-subtitle">Создайте аккаунт для доступа к личному кабинету</p>
                    </div>
                    
                    <?php if (!empty($error)): ?>
                    <div class="auth-error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success)): ?>
                    <div class="auth-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>
                    
                    <form id="registerForm" class="auth-form" method="POST" action="register.php">
                        <div class="auth-field">
                            <input type="text" id="register-name" name="name" class="auth-input" placeholder="Ваше имя" required>
                        </div>
                        
                        <div class="auth-field">
                            <input type="email" id="register-email" name="email" class="auth-input" placeholder="Email" required>
                        </div>
                        
                        <div class="auth-field">
                            <input type="tel" id="register-phone" name="phone" class="auth-input" placeholder="Телефон" required>
                        </div>
                        
                        <div class="auth-field auth-password-field">
                            <input type="password" id="register-password" name="password" class="auth-input" placeholder="Пароль" required>
                            <button type="button" class="auth-password-toggle">
                                <img src="assets/svg/eye.svg" alt="Показать пароль" width="20" height="20">
                            </button>
                        </div>
                        
                        <div class="auth-field auth-password-field">
                            <input type="password" id="register-password-confirm" name="password_confirm" class="auth-input" placeholder="Подтвердите пароль" required>
                            <button type="button" class="auth-password-toggle">
                                <img src="assets/svg/eye.svg" alt="Показать пароль" width="20" height="20">
                            </button>
                        </div>
                        
                        <div class="auth-checkbox-field">
                            <input type="checkbox" id="agree-terms" name="agree" class="auth-checkbox" required>
                            <label for="agree-terms" class="auth-checkbox-label">
                                <span class="auth-checkbox-custom"></span>
                                Я согласен с <a href="#" class="auth-link">политикой конфиденциальности</a>
                            </label>
                        </div>
                        
                        <button type="submit" class="auth-button">Зарегистрироваться</button>
                    </form>
                    
                    <div class="auth-divider">или зарегистрируйтесь через</div>
                    
                    <div class="auth-social">
                        <button type="button" class="auth-social-button">
                            <img src="assets/svg/google.svg" alt="Google" width="24" height="24">
                        </button>
                        <button type="button" class="auth-social-button">
                            <img src="assets/svg/vk.svg" alt="VK" width="24" height="24">
                        </button>
                        <button type="button" class="auth-social-button">
                            <img src="assets/svg/telegram.svg" alt="Telegram" width="24" height="24">
                        </button>
                    </div>
                    
                    <div class="auth-footer">
                        Уже есть аккаунт? <a href="login.php" class="auth-link">Войти</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'footer.php'; ?>
    
    <script src="js/auth.js"></script>
</body>
</html> 