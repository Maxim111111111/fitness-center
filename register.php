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
                    
                    <form id="registerForm" class="auth-form">
                        <div class="auth-field">
                            <input type="text" id="register-name" class="auth-input" placeholder="Ваше имя" required>
                        </div>
                        
                        <div class="auth-field">
                            <input type="email" id="register-email" class="auth-input" placeholder="Email" required>
                        </div>
                        
                        <div class="auth-field">
                            <input type="tel" id="register-phone" class="auth-input" placeholder="Телефон" required>
                        </div>
                        
                        <div class="auth-field auth-password-field">
                            <input type="password" id="register-password" class="auth-input" placeholder="Пароль" required>
                            <button type="button" class="auth-password-toggle">
                                <img src="assets/svg/eye.svg" alt="Показать пароль" width="20" height="20">
                            </button>
                        </div>
                        
                        <div class="auth-field auth-password-field">
                            <input type="password" id="register-password-confirm" class="auth-input" placeholder="Подтвердите пароль" required>
                            <button type="button" class="auth-password-toggle">
                                <img src="assets/svg/eye.svg" alt="Показать пароль" width="20" height="20">
                            </button>
                        </div>
                        
                        <div class="auth-checkbox-field">
                            <input type="checkbox" id="agree-terms" class="auth-checkbox" required>
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