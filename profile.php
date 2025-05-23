<?php
// Start the session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    // Redirect to login page
    header("Location: login.php");
    exit();
}

// Включаем файл с функциями работы с БД
require_once('database/config.php');

// Обработка выхода из системы
if (isset($_GET['logout'])) {
    // Удаляем cookie remember_token
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    
    // Очищаем все данные сессии
    $_SESSION = array();
    
    // Уничтожаем сессию
    session_destroy();
    
    // Перенаправляем на главную страницу
    header("Location: index.php");
    exit();
}

// Получаем данные пользователя
$userId = $_SESSION['user_id'];
$userData = null;

try {
    // Получение основных данных пользователя
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $userData = $stmt->fetch();
} catch (PDOException $e) {
    // В случае ошибки, просто продолжаем, будем использовать значения по умолчанию
    error_log("Error fetching user data: " . $e->getMessage());
}

// Получаем активный абонемент пользователя для отображения в боковой панели
$activeSubscriptionName = "Нет активного абонемента";
try {
    $stmt = $pdo->prepare("
        SELECT s.name
        FROM user_subscriptions us
        JOIN subscriptions s ON us.subscription_id = s.id
        WHERE us.user_id = ? AND us.status = 'active'
        AND us.end_date >= CURDATE()
        ORDER BY us.end_date DESC
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    $subscriptionData = $stmt->fetch();
    if ($subscriptionData) {
        $activeSubscriptionName = $subscriptionData['name'];
    }
} catch (PDOException $e) {
    error_log("Error fetching active subscription name: " . $e->getMessage());
}

// Для полей, которые могут быть NULL, устанавливаем значения по умолчанию
$firstName = $userData['first_name'] ?? 'Пользователь';
$lastName = $userData['last_name'] ?? '';
$email = $userData['email'] ?? '';
$phone = $userData['phone'] ?? '';
$birthDate = $userData['birthdate'] ?? '';
$gender = $userData['gender'] ?? 'male';
$height = $userData['height'] ?? '';
$weight = $userData['weight'] ?? '';
$profileImage = $userData['avatar_url'] ?? 'assets/img/avatar-placeholder.svg';

// Получаем предстоящие тренировки пользователя
$upcomingTrainings = [];
try {
    $stmt = $pdo->prepare("
        SELECT ts.*, t.id as trainer_id, CONCAT(u.first_name, ' ', u.last_name) as trainer_name, 
        s.name as service_name, s.duration
        FROM training_sessions ts 
        LEFT JOIN trainers t ON ts.trainer_id = t.id
        LEFT JOIN users u ON t.user_id = u.id
        LEFT JOIN services s ON ts.service_id = s.id
        WHERE ts.user_id = ? AND ts.session_date >= CURDATE() 
        AND ts.status IN ('pending', 'confirmed')
        ORDER BY ts.session_date ASC, ts.start_time ASC
    ");
    $stmt->execute([$userId]);
    $upcomingTrainings = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching upcoming trainings: " . $e->getMessage());
}

// Получаем историю тренировок пользователя
$pastTrainings = [];
try {
    $stmt = $pdo->prepare("
        SELECT ts.*, t.id as trainer_id, CONCAT(u.first_name, ' ', u.last_name) as trainer_name, 
        s.name as service_name, s.duration
        FROM training_sessions ts 
        LEFT JOIN trainers t ON ts.trainer_id = t.id
        LEFT JOIN users u ON t.user_id = u.id
        LEFT JOIN services s ON ts.service_id = s.id
        WHERE ts.user_id = ? AND (ts.session_date < CURDATE() OR 
        (ts.session_date = CURDATE() AND ts.end_time < CURTIME()) OR
        ts.status = 'completed')
        ORDER BY ts.session_date DESC, ts.start_time DESC
        LIMIT 10
    ");
    $stmt->execute([$userId]);
    $pastTrainings = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching past trainings: " . $e->getMessage());
}

// Функция для форматирования месяца на русском
function getRussianMonth($date) {
    $months = [
        1 => 'Январь', 2 => 'Февраль', 3 => 'Март', 4 => 'Апрель',
        5 => 'Май', 6 => 'Июнь', 7 => 'Июль', 8 => 'Август',
        9 => 'Сентябрь', 10 => 'Октябрь', 11 => 'Ноябрь', 12 => 'Декабрь'
    ];
    
    $monthNum = (int)date('n', strtotime($date));
    return $months[$monthNum];
}

// Функция для форматирования типа тренировки
function getTrainingTitle($serviceId, $serviceName) {
    if (empty($serviceName)) {
        switch ($serviceId) {
            case 1: return 'Персональная тренировка';
            case 2: return 'Групповое занятие: Йога';
            case 3: return 'Силовая тренировка';
            case 4: return 'Кардио и растяжка';
            case 5: return 'Тренировка в бассейне';
            default: return 'Тренировка';
        }
    }
    return $serviceName;
}

// Получаем активный абонемент пользователя
$activeSubscription = null;
try {
    // Запрос для получения активного абонемента
    $stmt = $pdo->prepare("
        SELECT us.*, s.name, s.description, s.price, s.duration_days, s.sessions_count
        FROM user_subscriptions us
        JOIN subscriptions s ON us.subscription_id = s.id
        WHERE us.user_id = ? AND us.status = 'active'
        AND us.end_date >= CURDATE()
        ORDER BY us.end_date DESC
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    $activeSubscription = $stmt->fetch();
    
    if ($activeSubscription) {
        error_log("Найден абонемент: ID {$activeSubscription['id']}, remaining_sessions: " . 
            var_export($activeSubscription['remaining_sessions'], true));
        
        // Проверяем, есть ли завершенные тренировки, которые еще не учтены
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as completed_count, MAX(updated_at) as last_updated
            FROM training_sessions 
            WHERE user_id = ? 
            AND status = 'completed' 
            AND updated_at > ?
        ");
        $stmt->execute([$userId, $activeSubscription['updated_at'] ?? '1970-01-01']);
        $result = $stmt->fetch();
        $completedCount = $result['completed_count'];
        $lastUpdated = $result['last_updated'];
        
        error_log("Количество завершенных тренировок после последнего обновления: $completedCount, последнее обновление: $lastUpdated");
        error_log("Последнее обновление абонемента: {$activeSubscription['updated_at']}");
        
        // Если есть завершенные тренировки после последнего обновления абонемента
        if ($completedCount > 0 && $activeSubscription['remaining_sessions'] !== null) {
            $newRemaining = max(0, $activeSubscription['remaining_sessions'] - $completedCount);
            error_log("Обновляем remaining_sessions с {$activeSubscription['remaining_sessions']} на $newRemaining");
            
            // Обновляем количество оставшихся тренировок
            $updateStmt = $pdo->prepare("
                UPDATE user_subscriptions 
                SET remaining_sessions = ?, 
                    updated_at = NOW()
                WHERE id = ?
            ");
            $updateStmt->execute([$newRemaining, $activeSubscription['id']]);
            
            // Проверяем, действительно ли обновилось значение
            $rowCount = $updateStmt->rowCount();
            error_log("Количество обновленных строк: $rowCount");
            
            if ($rowCount > 0) {
                // Обновляем данные для отображения
                $activeSubscription['remaining_sessions'] = $newRemaining;
                
                // Получаем обновленное время
                $checkStmt = $pdo->prepare("SELECT updated_at FROM user_subscriptions WHERE id = ?");
                $checkStmt->execute([$activeSubscription['id']]);
                $newUpdatedAt = $checkStmt->fetchColumn();
                
                error_log("Данные абонемента обновлены в профиле пользователя, новое время обновления: $newUpdatedAt");
            } else {
                error_log("ОШИБКА: Не удалось обновить значение remaining_sessions в базе данных");
            }
        } else if ($completedCount > 0) {
            error_log("Есть завершенные тренировки, но абонемент безлимитный (remaining_sessions = NULL)");
        } else {
            error_log("Нет новых завершенных тренировок после последнего обновления абонемента");
        }
    } else {
        error_log("Активный абонемент не найден для пользователя ID: $userId");
    }
} catch (PDOException $e) {
    error_log("Error fetching active subscription: " . $e->getMessage());
}

// Получаем историю покупок абонементов
$subscriptionHistory = [];
try {
    $stmt = $pdo->prepare("
        SELECT p.*, s.name, s.price, us.start_date, us.end_date, us.status
        FROM payments p
        JOIN subscriptions s ON p.subscription_id = s.id
        JOIN user_subscriptions us ON us.subscription_id = s.id AND us.user_id = p.user_id
        WHERE p.user_id = ? AND p.status = 'completed'
        ORDER BY p.payment_date DESC
        LIMIT 10
    ");
    $stmt->execute([$userId]);
    $subscriptionHistory = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching subscription history: " . $e->getMessage());
}

?>
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
    <?php include 'header.php'; ?>
    
    <main class="profile-page">
        <div class="container">
            <div class="profile-container">
                <div class="profile-sidebar">
                    <div class="profile-user">
                        <div class="profile-avatar">
                            <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Аватар пользователя" id="userAvatar">
                            <button class="profile-avatar-edit" id="changeAvatarBtn">
                                <img src="assets/svg/edit.svg" alt="Изменить">
                            </button>
                        </div>
                        <div class="profile-user-info">
                            <h2 class="profile-user-name" id="userName"><?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></h2>
                            <p class="profile-user-membership"><?php echo htmlspecialchars($activeSubscriptionName); ?></p>
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
                                        <input type="text" id="profile-name" class="profile-form-input" value="<?php echo htmlspecialchars($firstName); ?>" disabled>
                                    </div>
                                    <div class="profile-form-group">
                                        <label for="profile-surname" class="profile-form-label">Фамилия</label>
                                        <input type="text" id="profile-surname" class="profile-form-input" value="<?php echo htmlspecialchars($lastName); ?>" disabled>
                                    </div>
                                </div>
                                
                                <div class="profile-form-row">
                                    <div class="profile-form-group">
                                        <label for="profile-email" class="profile-form-label">Email</label>
                                        <input type="email" id="profile-email" class="profile-form-input" value="<?php echo htmlspecialchars($email); ?>" disabled>
                                    </div>
                                    <div class="profile-form-group">
                                        <label for="profile-phone" class="profile-form-label">Телефон</label>
                                        <input type="tel" id="profile-phone" class="profile-form-input" value="<?php echo htmlspecialchars($phone); ?>" disabled>
                                    </div>
                                </div>
                                
                                <div class="profile-form-row">
                                    <div class="profile-form-group">
                                        <label for="profile-birthdate" class="profile-form-label">Дата рождения</label>
                                        <input type="date" id="profile-birthdate" class="profile-form-input" value="<?php echo htmlspecialchars($birthDate); ?>" disabled>
                                    </div>
                                    <div class="profile-form-group">
                                        <label for="profile-gender" class="profile-form-label">Пол</label>
                                        <select id="profile-gender" class="profile-form-select" disabled>
                                            <option value="male" <?php echo $gender === 'male' ? 'selected' : ''; ?>>Мужской</option>
                                            <option value="female" <?php echo $gender === 'female' ? 'selected' : ''; ?>>Женский</option>
                                            <option value="other" <?php echo $gender === 'other' ? 'selected' : ''; ?>>Другой</option>
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
                                        <input type="number" id="profile-height" class="profile-form-input" value="<?php echo htmlspecialchars($height); ?>" disabled>
                                    </div>
                                    <div class="profile-form-group">
                                        <label for="profile-weight" class="profile-form-label">Вес (кг)</label>
                                        <input type="number" id="profile-weight" class="profile-form-input" value="<?php echo htmlspecialchars($weight); ?>" disabled>
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
                                <?php if (empty($upcomingTrainings)): ?>
                                <div class="training-empty">
                                    <p>У вас пока нет запланированных тренировок</p>
                                    <a href="training_session.php" class="training-empty-button">Записаться на тренировку</a>
                                </div>
                                <?php else: ?>
                                    <?php foreach ($upcomingTrainings as $training): ?>
                                    <div class="training-card">
                                        <div class="training-info">
                                            <div class="training-date">
                                                <div class="training-day"><?php echo date('d', strtotime($training['session_date'])); ?></div>
                                                <div class="training-month"><?php echo getRussianMonth($training['session_date']); ?></div>
                                            </div>
                                            <div class="training-details">
                                                <h4 class="training-title"><?php echo htmlspecialchars(getTrainingTitle($training['service_id'], $training['service_name'])); ?></h4>
                                                <div class="training-time"><?php echo substr($training['start_time'], 0, 5); ?> - <?php echo substr($training['end_time'], 0, 5); ?></div>
                                                <div class="training-coach">Тренер: <?php echo htmlspecialchars($training['trainer_name'] ?: 'Не назначен'); ?></div>
                                            </div>
                                        </div>
                                        <div class="training-actions">
                                            <button class="training-cancel" data-id="<?php echo $training['id']; ?>">Отменить</button>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="profile-section">
                            <div class="profile-section-header">
                                <h3 class="profile-section-title">История тренировок</h3>
                            </div>
                            
                            <div class="training-list">
                                <?php if (empty($pastTrainings)): ?>
                                <div class="training-empty">
                                    <p>История тренировок пуста</p>
                                </div>
                                <?php else: ?>
                                    <?php foreach ($pastTrainings as $training): ?>
                                    <div class="training-card past">
                                        <div class="training-info">
                                            <div class="training-date">
                                                <div class="training-day"><?php echo date('d', strtotime($training['session_date'])); ?></div>
                                                <div class="training-month"><?php echo getRussianMonth($training['session_date']); ?></div>
                                            </div>
                                            <div class="training-details">
                                                <h4 class="training-title"><?php echo htmlspecialchars(getTrainingTitle($training['service_id'], $training['service_name'])); ?></h4>
                                                <div class="training-time"><?php echo substr($training['start_time'], 0, 5); ?> - <?php echo substr($training['end_time'], 0, 5); ?></div>
                                                <div class="training-coach">Тренер: <?php echo htmlspecialchars($training['trainer_name'] ?: 'Не назначен'); ?></div>
                                            </div>
                                        </div>
                                        <div class="training-actions">
                                            <div class="training-status completed"><?php echo $training['status'] == 'cancelled' ? 'Отменена' : 'Проведена'; ?></div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="profile-tab" id="profile-subscription">
                        <div class="profile-header">
                            <h1 class="profile-title">Мой абонемент</h1>
                            <p class="profile-subtitle">Информация о текущем абонементе</p>
                        </div>
                        
                        <div class="profile-section">
                            <?php if ($activeSubscription): ?>
                            <div class="subscription-card">
                                <div class="subscription-header">
                                    <h3 class="subscription-title"><?= htmlspecialchars($activeSubscription['name']) ?></h3>
                                    <div class="subscription-status active">Активен</div>
                                </div>
                                <div class="subscription-details">
                                    <div class="subscription-info">
                                        <div class="subscription-label">Срок действия:</div>
                                        <div class="subscription-value">до <?= date('d.m.Y', strtotime($activeSubscription['end_date'])) ?></div>
                                    </div>
                                    <div class="subscription-info">
                                        <div class="subscription-label">Осталось дней:</div>
                                        <div class="subscription-value">
                                            <?= ceil((strtotime($activeSubscription['end_date']) - time()) / (60 * 60 * 24)) ?>
                                        </div>
                                    </div>
                                    <?php if ($activeSubscription['remaining_sessions'] !== null): ?>
                                    <div class="subscription-info">
                                        <div class="subscription-label">Осталось тренировок:</div>
                                        <div class="subscription-value" id="remaining-sessions-count"><?= $activeSubscription['remaining_sessions'] ?></div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="subscription-features">
                                    <h4 class="subscription-features-title">В абонемент входит:</h4>
                                    <div class="subscription-description">
                                        <?= nl2br(htmlspecialchars($activeSubscription['description'])) ?>
                                    </div>
                                </div>
                                <div class="subscription-actions">
                                    <form action="subscription_handler.php" method="post">
                                        <input type="hidden" name="action" value="purchase">
                                        <input type="hidden" name="subscription_id" value="<?= $activeSubscription['subscription_id'] ?>">
                                        <button type="submit" class="subscription-renew">Продлить абонемент</button>
                                    </form>
                                    <form action="subscription_handler.php" method="post" onsubmit="return confirm('Вы уверены, что хотите отменить абонемент?');">
                                        <input type="hidden" name="action" value="cancel">
                                        <input type="hidden" name="subscription_id" value="<?= $activeSubscription['id'] ?>">
                                        <button type="submit" class="subscription-cancel-btn">Отменить абонемент</button>
                                    </form>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="no-subscription">
                                <img src="assets/svg/card.svg" alt="Нет активного абонемента" class="no-subscription-icon">
                                <h3>У вас нет активного абонемента</h3>
                                <p>Приобретите абонемент, чтобы начать заниматься в нашем фитнес-центре</p>
                                <a href="subscriptions.php" class="subscription-renew">Выбрать абонемент</a>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($subscriptionHistory)): ?>
                        <div class="profile-section">
                            <div class="profile-section-header">
                                <h3 class="profile-section-title">История покупок</h3>
                            </div>
                            
                            <div class="subscription-history">
                                <?php foreach ($subscriptionHistory as $purchase): ?>
                                <div class="subscription-history-item">
                                    <div class="subscription-history-date">
                                        <?= date('d.m.Y', strtotime($purchase['payment_date'])) ?>
                                    </div>
                                    <div class="subscription-history-name">
                                        <?= htmlspecialchars($purchase['name']) ?>
                                    </div>
                                    <div class="subscription-history-period">
                                        <?= date('d.m.Y', strtotime($purchase['start_date'])) ?> - 
                                        <?= date('d.m.Y', strtotime($purchase['end_date'])) ?>
                                    </div>
                                    <div class="subscription-history-price">
                                        <?= number_format($purchase['amount'], 0, '.', ' ') ?> ₽
                                    </div>
                                    <div class="subscription-history-status <?= $purchase['status'] ?>">
                                        <?php
                                        switch ($purchase['status']) {
                                            case 'active': echo 'Активен'; break;
                                            case 'expired': echo 'Истек'; break;
                                            case 'cancelled': echo 'Отменен'; break;
                                            default: echo 'Неизвестно';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
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