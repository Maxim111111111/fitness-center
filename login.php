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
                    
                    <form id="loginForm" class="auth-form">
                        <div class="auth-field">
                            <input type="email" id="login-email" class="auth-input" placeholder="Email" required>
                        </div>
                        
                        <div class="auth-field auth-password-field">
                            <input type="password" id="login-password" class="auth-input" placeholder="Пароль" required>
                            <button type="button" class="auth-password-toggle">
                                <img src="assets/svg/eye.svg" alt="Показать пароль" width="20" height="20">
                            </button>
                        </div>
                        
                        <div class="auth-checkbox-field">
                            <input type="checkbox" id="remember-me" class="auth-checkbox">
                            <label for="remember-me" class="auth-checkbox-label">
                                <span class="auth-checkbox-custom"></span>
                                Запомнить меня
                            </label>
                        </div>
                        
                        <div class="auth-forgot">
                            <a href="#" class="auth-forgot-link">Забыли пароль?</a>
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