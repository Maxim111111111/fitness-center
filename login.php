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

// Handle login form submission
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? sanitize($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $remember = isset($_POST['remember']) ? true : false;
    
    // Validate email and password
    if (empty($email)) {
        $error = 'Пожалуйста, введите email';
    } elseif (empty($password)) {
        $error = 'Пожалуйста, введите пароль';
    } else {
        try {
            // Check if user exists
            $stmt = $pdo->prepare("SELECT id, password_hash, first_name, last_name, email, role FROM users WHERE email = ? AND is_active = 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_logged_in'] = true;
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_role'] = $user['role'];
                
                // Update last_login timestamp
                $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $updateStmt->execute([$user['id']]);
                
                // Create audit log entry
                $auditStmt = $pdo->prepare("
                    INSERT INTO audit_log (user_id, entity_type, entity_id, action, details, ip_address, user_agent, created_at)
                    VALUES (?, 'user', ?, 'login', 'User logged in', ?, ?, NOW())
                ");
                $auditStmt->execute([
                    $user['id'],
                    $user['id'],
                    $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                    $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
                ]);
                
                // If remember me is checked, create longer session token
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
                    
                    $tokenStmt = $pdo->prepare("
                        INSERT INTO api_tokens (user_id, token, created_at, expires_at, is_active)
                        VALUES (?, ?, NOW(), ?, 1)
                    ");
                    $tokenStmt->execute([$user['id'], $token, $expires]);
                    
                    // Set remember me cookie
                    setcookie('remember_token', $token, strtotime('+30 days'), '/', '', false, true);
                }
                
                // Redirect to profile page
                header("Location: profile.php");
                exit();
            } else {
                $error = 'Неверный email или пароль';
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $error = 'Произошла ошибка при входе в систему';
        }
    }
}

// Check for remember me cookie
if (empty($error) && !isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    
    try {
        $stmt = $pdo->prepare("
            SELECT t.user_id, u.id, u.email, u.first_name, u.last_name, u.role
            FROM api_tokens t
            JOIN users u ON t.user_id = u.id
            WHERE t.token = ? AND t.expires_at > NOW() AND t.is_active = 1 AND u.is_active = 1
        ");
        $stmt->execute([$token]);
        $tokenData = $stmt->fetch();
        
        if ($tokenData) {
            // Auto-login successful
            $_SESSION['user_id'] = $tokenData['id'];
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_email'] = $tokenData['email'];
            $_SESSION['user_name'] = $tokenData['first_name'] . ' ' . $tokenData['last_name'];
            $_SESSION['user_role'] = $tokenData['role'];
            
            // Update last_login timestamp
            $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $updateStmt->execute([$tokenData['id']]);
            
            // Update token's last_used_at
            $tokenUpdateStmt = $pdo->prepare("UPDATE api_tokens SET last_used_at = NOW() WHERE token = ?");
            $tokenUpdateStmt->execute([$token]);
            
            // Redirect to profile page
            header("Location: profile.php");
            exit();
        } else {
            // Invalid or expired token, clear the cookie
            setcookie('remember_token', '', time() - 3600, '/', '', false, true);
        }
    } catch (PDOException $e) {
        error_log("Remember me login error: " . $e->getMessage());
        // Clear the cookie in case of error
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Dela+Gothic+One&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <title>Вход в личный кабинет | Moreon Fitness</title>
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
                        <h1 class="auth-title">Вход в личный кабинет</h1>
                        <p class="auth-subtitle">Введите данные для входа в систему</p>
                    </div>
                    
                    <?php if (!empty($error)): ?>
                    <div class="auth-error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success)): ?>
                    <div class="auth-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="login.php" id="loginForm" class="auth-form">
                        <div class="auth-field">
                            <input type="email" id="login-email" name="email" class="auth-input" placeholder="Email" required>
                        </div>
                        
                        <div class="auth-field auth-password-field">
                            <input type="password" id="login-password" name="password" class="auth-input" placeholder="Пароль" required>
                            <button type="button" class="auth-password-toggle">
                                <img src="assets/svg/eye.svg" alt="Показать пароль" width="20" height="20">
                            </button>
                        </div>
                        
                        <div class="auth-checkbox-field">
                            <input type="checkbox" id="remember-me" name="remember" class="auth-checkbox">
                            <label for="remember-me" class="auth-checkbox-label">
                                <span class="auth-checkbox-custom"></span>
                                Запомнить меня
                            </label>
                        </div>
                        
                        <div class="auth-forgot">
                            <a href="forgot_password.php" class="auth-forgot-link">Забыли пароль?</a>
                        </div>
                        
                        <button type="submit" class="auth-button">Войти</button>
                    </form>
                    
                    <div class="auth-divider">или войдите через</div>
                    
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
                        Ещё нет аккаунта? <a href="register.php" class="auth-link">Зарегистрироваться</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'footer.php'; ?>
    
    <script src="js/auth.js"></script>
</body>
</html> 