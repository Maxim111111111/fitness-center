<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Dela+Gothic+One&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <title>Личный кабинет | Moreon Fitness</title>
    <link rel="stylesheet" href="style/reset.css">
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="style/auth.css">
    <link rel="stylesheet" href="style/profile.css">
</head>
<body>
    <?php
    // Добавляем обработку выхода из системы
    if (isset($_GET['logout'])) {
        // Уничтожаем сессию
        session_start();
        session_destroy();
        
        // Перенаправляем на главную страницу
        header("Location: index.php");
        exit();
    }

    include 'header.php';
    ?>
    
    <main class="profile-page">
        <div class="container">
            <div class="profile-container">
                <div class="profile-sidebar">
                    <div class="profile-user">
                        <div class="profile-avatar">
                            <img src="assets/img/avatar-placeholder.svg" alt="Аватар пользователя" id="userAvatar">
                            <button class="profile-avatar-edit" id="changeAvatarBtn">
                                <img src="assets/svg/edit.svg" alt="Изменить">
                            </button>
                        </div>
                        <div class="profile-user-info">
                            <h2 class="profile-user-name" id="userName">Иван Иванов</h2>
                            <p class="profile-user-membership">Премиум абонемент</p>
                        </div>
                    </div>
                    <nav class="profile-nav">
                        <ul class="profile-nav-list">
                            <li class="profile-nav-item active">
                                <a href="#profile-main" class="profile-nav-link" data-tab="profile-main">
                                    <img src="assets/svg/user.svg" alt="" class="profile-nav-icon">
                                    <span>Мой профиль</span>
                                </a>
                            </li>
                            <li class="profile-nav-item">
                                <a href="#profile-trainings" class="profile-nav-link" data-tab="profile-trainings">
                                    <img src="assets/svg/calendar.svg" alt="" class="profile-nav-icon">
                                    <span>Мои тренировки</span>
                                </a>
                            </li>
                            <li class="profile-nav-item">
                                <a href="#profile-subscription" class="profile-nav-link" data-tab="profile-subscription">
                                    <img src="assets/svg/card.svg" alt="" class="profile-nav-icon">
                                    <span>Абонемент</span>
                                </a>
                            </li>
                            <li class="profile-nav-item">
                                <a href="#profile-settings" class="profile-nav-link" data-tab="profile-settings">
                                    <img src="assets/svg/settings.svg" alt="" class="profile-nav-icon">
                                    <span>Настройки</span>
                                </a>
                            </li>
                        </ul>
                        <div class="profile-nav-logout">
                            <a href="profile.php?logout=1" class="profile-nav-link logout-link" id="logoutBtn">
                                <img src="assets/svg/logout.svg" alt="" class="profile-nav-icon">
                                <span>Выйти</span>
                            </a>
                        </div>
                    </nav>
                </div>
                
                <div class="profile-content">
                    <div class="profile-tab active" id="profile-main">
                        <div class="profile-header">
                            <h1 class="profile-title">Личный профиль</h1>
                            <p class="profile-subtitle">Управление персональными данными</p>
                        </div>
                        
                        <div class="profile-section">
                            <div class="profile-section-header">
                                <h3 class="profile-section-title">Персональные данные</h3>
                                <button class="profile-section-edit" id="editProfileBtn">
                                    <img src="assets/svg/edit.svg" alt="Редактировать">
                                    <span>Редактировать</span>
                                </button>
                            </div>
                            
                            <form id="profileForm" class="profile-form">
                                <div class="profile-form-row">
                                    <div class="profile-form-group">
                                        <label for="profile-name" class="profile-form-label">Имя</label>
                                        <input type="text" id="profile-name" class="profile-form-input" value="Иван" disabled>
                                    </div>
                                    <div class="profile-form-group">
                                        <label for="profile-surname" class="profile-form-label">Фамилия</label>
                                        <input type="text" id="profile-surname" class="profile-form-input" value="Иванов" disabled>
                                    </div>
                                </div>
                                
                                <div class="profile-form-row">
                                    <div class="profile-form-group">
                                        <label for="profile-email" class="profile-form-label">Email</label>
                                        <input type="email" id="profile-email" class="profile-form-input" value="ivan@example.com" disabled>
                                    </div>
                                    <div class="profile-form-group">
                                        <label for="profile-phone" class="profile-form-label">Телефон</label>
                                        <input type="tel" id="profile-phone" class="profile-form-input" value="+7 (999) 123-45-67" disabled>
                                    </div>
                                </div>
                                
                                <div class="profile-form-row">
                                    <div class="profile-form-group">
                                        <label for="profile-birthdate" class="profile-form-label">Дата рождения</label>
                                        <input type="date" id="profile-birthdate" class="profile-form-input" value="1990-01-01" disabled>
                                    </div>
                                    <div class="profile-form-group">
                                        <label for="profile-gender" class="profile-form-label">Пол</label>
                                        <select id="profile-gender" class="profile-form-select" disabled>
                                            <option value="male" selected>Мужской</option>
                                            <option value="female">Женский</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="profile-form-actions" id="profileFormActions" style="display: none;">
                                    <button type="button" class="profile-form-cancel" id="cancelProfileBtn">Отмена</button>
                                    <button type="submit" class="profile-form-submit">Сохранить</button>
                                </div>
                            </form>
                        </div>
                        
                        <div class="profile-section">
                            <div class="profile-section-header">
                                <h3 class="profile-section-title">Физические параметры</h3>
                                <button class="profile-section-edit" id="editParamsBtn">
                                    <img src="assets/svg/edit.svg" alt="Редактировать">
                                    <span>Редактировать</span>
                                </button>
                            </div>
                            
                            <form id="paramsForm" class="profile-form">
                                <div class="profile-form-row">
                                    <div class="profile-form-group">
                                        <label for="profile-height" class="profile-form-label">Рост (см)</label>
                                        <input type="number" id="profile-height" class="profile-form-input" value="178" disabled>
                                    </div>
                                    <div class="profile-form-group">
                                        <label for="profile-weight" class="profile-form-label">Вес (кг)</label>
                                        <input type="number" id="profile-weight" class="profile-form-input" value="75" disabled>
                                    </div>
                                </div>
                                
                                <div class="profile-form-actions" id="paramsFormActions" style="display: none;">
                                    <button type="button" class="profile-form-cancel" id="cancelParamsBtn">Отмена</button>
                                    <button type="submit" class="profile-form-submit">Сохранить</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="profile-tab" id="profile-trainings">
                        <div class="profile-header">
                            <h1 class="profile-title">Мои тренировки</h1>
                            <p class="profile-subtitle">История и запланированные тренировки</p>
                        </div>
                        
                        <div class="profile-section">
                            <div class="profile-section-header">
                                <h3 class="profile-section-title">Предстоящие тренировки</h3>
                                <a href="training_session.php" class="profile-section-add">
                                    <img src="assets/svg/plus.svg" alt="Добавить">
                                    <span>Записаться</span>
                                </a>
                            </div>
                            
                            <div class="training-list">
                                <div class="training-card">
                                    <div class="training-info">
                                        <div class="training-date">
                                            <div class="training-day">15</div>
                                            <div class="training-month">Июль</div>
                                        </div>
                                        <div class="training-details">
                                            <h4 class="training-title">Силовая тренировка</h4>
                                            <div class="training-time">18:00 - 19:30</div>
                                            <div class="training-coach">Тренер: Алексей Петров</div>
                                        </div>
                                    </div>
                                    <div class="training-actions">
                                        <button class="training-cancel">Отменить</button>
                                    </div>
                                </div>
                                
                                <div class="training-card">
                                    <div class="training-info">
                                        <div class="training-date">
                                            <div class="training-day">17</div>
                                            <div class="training-month">Июль</div>
                                        </div>
                                        <div class="training-details">
                                            <h4 class="training-title">Кардио и растяжка</h4>
                                            <div class="training-time">19:00 - 20:00</div>
                                            <div class="training-coach">Тренер: Мария Сидорова</div>
                                        </div>
                                    </div>
                                    <div class="training-actions">
                                        <button class="training-cancel">Отменить</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="profile-section">
                            <div class="profile-section-header">
                                <h3 class="profile-section-title">История тренировок</h3>
                            </div>
                            
                            <div class="training-list">
                                <div class="training-card past">
                                    <div class="training-info">
                                        <div class="training-date">
                                            <div class="training-day">10</div>
                                            <div class="training-month">Июль</div>
                                        </div>
                                        <div class="training-details">
                                            <h4 class="training-title">Групповое занятие: Йога</h4>
                                            <div class="training-time">18:00 - 19:00</div>
                                            <div class="training-coach">Тренер: Ольга Смирнова</div>
                                        </div>
                                    </div>
                                    <div class="training-actions">
                                        <div class="training-status completed">Проведена</div>
                                    </div>
                                </div>
                                
                                <div class="training-card past">
                                    <div class="training-info">
                                        <div class="training-date">
                                            <div class="training-day">8</div>
                                            <div class="training-month">Июль</div>
                                        </div>
                                        <div class="training-details">
                                            <h4 class="training-title">Силовая тренировка</h4>
                                            <div class="training-time">17:30 - 19:00</div>
                                            <div class="training-coach">Тренер: Алексей Петров</div>
                                        </div>
                                    </div>
                                    <div class="training-actions">
                                        <div class="training-status completed">Проведена</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="profile-tab" id="profile-subscription">
                        <div class="profile-header">
                            <h1 class="profile-title">Мой абонемент</h1>
                            <p class="profile-subtitle">Информация о текущем абонементе</p>
                        </div>
                        
                        <div class="profile-section">
                            <div class="subscription-card">
                                <div class="subscription-header">
                                    <h3 class="subscription-title">Премиум</h3>
                                    <div class="subscription-status active">Активен</div>
                                </div>
                                <div class="subscription-details">
                                    <div class="subscription-info">
                                        <div class="subscription-label">Срок действия:</div>
                                        <div class="subscription-value">до 15.09.2023</div>
                                    </div>
                                    <div class="subscription-info">
                                        <div class="subscription-label">Осталось дней:</div>
                                        <div class="subscription-value">45</div>
                                    </div>
                                    <div class="subscription-info">
                                        <div class="subscription-label">Осталось тренировок:</div>
                                        <div class="subscription-value">12</div>
                                    </div>
                                </div>
                                <div class="subscription-features">
                                    <h4 class="subscription-features-title">В абонемент входит:</h4>
                                    <ul class="subscription-features-list">
                                        <li class="subscription-feature-item">Посещение тренажерного зала</li>
                                        <li class="subscription-feature-item">Групповые занятия</li>
                                        <li class="subscription-feature-item">Персональный тренер (2 раза в неделю)</li>
                                        <li class="subscription-feature-item">Посещение спа-зоны</li>
                                    </ul>
                                </div>
                                <div class="subscription-actions">
                                    <button class="subscription-renew">Продлить абонемент</button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="profile-section">
                            <div class="profile-section-header">
                                <h3 class="profile-section-title">История покупок</h3>
                            </div>
                            
                            <div class="subscription-history">
                                <div class="subscription-history-item">
                                    <div class="subscription-history-date">15.06.2023</div>
                                    <div class="subscription-history-name">Абонемент "Премиум"</div>
                                    <div class="subscription-history-period">3 месяца</div>
                                    <div class="subscription-history-price">15 000 ₽</div>
                                </div>
                                <div class="subscription-history-item">
                                    <div class="subscription-history-date">15.03.2023</div>
                                    <div class="subscription-history-name">Абонемент "Стандарт"</div>
                                    <div class="subscription-history-period">3 месяца</div>
                                    <div class="subscription-history-price">9 000 ₽</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="profile-tab" id="profile-settings">
                        <div class="profile-header">
                            <h1 class="profile-title">Настройки аккаунта</h1>
                            <p class="profile-subtitle">Управление безопасностью</p>
                        </div>
                        
                        <div class="profile-section">
                            <div class="profile-section-header">
                                <h3 class="profile-section-title">Изменить пароль</h3>
                            </div>
                            
                            <form id="passwordForm" class="profile-form">
                                <div class="profile-form-group">
                                    <label for="current-password" class="profile-form-label">Текущий пароль</label>
                                    <div class="password-field">
                                        <input type="password" id="current-password" class="profile-form-input">
                                        <button type="button" class="password-toggle">
                                            <img src="assets/svg/eye.svg" alt="Показать пароль">
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="profile-form-group">
                                    <label for="new-password" class="profile-form-label">Новый пароль</label>
                                    <div class="password-field">
                                        <input type="password" id="new-password" class="profile-form-input">
                                        <button type="button" class="password-toggle">
                                            <img src="assets/svg/eye.svg" alt="Показать пароль">
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="profile-form-group">
                                    <label for="confirm-password" class="profile-form-label">Повторите новый пароль</label>
                                    <div class="password-field">
                                        <input type="password" id="confirm-password" class="profile-form-input">
                                        <button type="button" class="password-toggle">
                                            <img src="assets/svg/eye.svg" alt="Показать пароль">
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="profile-form-actions">
                                    <button type="submit" class="profile-form-submit">Изменить пароль</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'footer.php'; ?>
    
    <script src="js/profile.js"></script>
</body>
</html> 