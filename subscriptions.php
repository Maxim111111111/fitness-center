<?php
session_start();
require_once('database/config.php');

// Получаем список доступных абонементов
$subscriptions = [];
try {
    $stmt = $pdo->prepare("
        SELECT * FROM subscriptions 
        WHERE is_active = 1 
        ORDER BY price ASC
    ");
    $stmt->execute();
    $subscriptions = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching subscriptions: " . $e->getMessage());
}

// Получаем текущий активный абонемент пользователя, если он авторизован
$activeSubscription = null;
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("
            SELECT us.*, s.name, s.description 
            FROM user_subscriptions us
            JOIN subscriptions s ON us.subscription_id = s.id
            WHERE us.user_id = ? AND us.status = 'active'
            AND us.end_date >= CURDATE()
            ORDER BY us.end_date DESC
            LIMIT 1
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $activeSubscription = $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error fetching active subscription: " . $e->getMessage());
    }
}

// Функция для форматирования цены
function formatPrice($price) {
    return number_format($price, 0, '.', ' ') . ' ₽';
}

// Функция для форматирования срока действия абонемента
function formatDuration($days) {
    if ($days % 30 == 0) {
        $months = $days / 30;
        if ($months == 1) return "1 месяц";
        if ($months >= 2 && $months <= 4) return "$months месяца";
        return "$months месяцев";
    } else if ($days % 7 == 0) {
        $weeks = $days / 7;
        if ($weeks == 1) return "1 неделя";
        if ($weeks >= 2 && $weeks <= 4) return "$weeks недели";
        return "$weeks недель";
    } else {
        if ($days == 1) return "1 день";
        if ($days >= 2 && $days <= 4) return "$days дня";
        return "$days дней";
    }
}

// Функция для форматирования количества тренировок
function formatSessions($count) {
    if ($count === null) return "Безлимитно";
    if ($count == 1) return "1 тренировка";
    if ($count >= 2 && $count <= 4) return "$count тренировки";
    return "$count тренировок";
}

// Определяем категории абонементов для группировки
$categories = [
    'standard' => 'Стандартные',
    'premium' => 'Премиум',
    'special' => 'Специальные предложения'
];

// Группируем абонементы по категориям (для примера используем цену)
$groupedSubscriptions = [];
foreach ($subscriptions as $subscription) {
    if ($subscription['price'] < 5000) {
        $groupedSubscriptions['standard'][] = $subscription;
    } elseif ($subscription['price'] < 10000) {
        $groupedSubscriptions['premium'][] = $subscription;
    } else {
        $groupedSubscriptions['special'][] = $subscription;
    }
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Dela+Gothic+One&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <title>Абонементы | Moreon Fitness</title>
    <link rel="stylesheet" href="style/reset.css">
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="style/subscriptions.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main class="subscriptions-page">
        <div class="hero-section">
            <div class="container">
                <div class="hero-content">
                    <h1 class="hero-title">Абонементы Moreon Fitness</h1>
                    <p class="hero-subtitle">Выберите идеальный абонемент для достижения ваших фитнес-целей</p>
                    
                    <?php if (!empty($activeSubscription) && isset($_SESSION['user_id'])): ?>
                    <div class="active-subscription-banner">
                        <div class="active-subscription-info">
                            <div class="active-badge">Активен</div>
                            <h3>У вас действует абонемент "<?= htmlspecialchars($activeSubscription['name']) ?>"</h3>
                            <p>До <?= date('d.m.Y', strtotime($activeSubscription['end_date'])) ?> 
                            <?php if ($activeSubscription['remaining_sessions'] !== null): ?>
                            • Осталось тренировок: <?= $activeSubscription['remaining_sessions'] ?>
                            <?php endif; ?></p>
                        </div>
                        <a href="profile.php#profile-subscription" class="btn-outline">Управление абонементом</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="hero-gradient"></div>
        </div>
        
        <div class="container">
            <div class="subscription-filters">
                <div class="filter-tabs">
                    <button class="filter-tab active" data-filter="all">Все абонементы</button>
                    <?php foreach ($categories as $key => $name): ?>
                        <?php if (!empty($groupedSubscriptions[$key])): ?>
                        <button class="filter-tab" data-filter="<?= $key ?>"><?= $name ?></button>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <?php if (empty($subscriptions)): ?>
            <div class="no-subscriptions">
                <img src="assets/svg/card.svg" alt="Нет абонементов" class="no-data-icon">
                <h3>В данный момент нет доступных абонементов</h3>
                <p>Пожалуйста, свяжитесь с администрацией для получения дополнительной информации</p>
            </div>
            <?php else: ?>
                <?php foreach ($categories as $key => $name): ?>
                    <?php if (!empty($groupedSubscriptions[$key])): ?>
                    <div class="subscription-category" data-category="<?= $key ?>">
                        <h2 class="category-title"><?= $name ?></h2>
                        
                        <div class="subscription-grid">
                            <?php foreach ($groupedSubscriptions[$key] as $subscription): ?>
                            <div class="subscription-card" data-name="<?= strtolower(htmlspecialchars($subscription['name'])) ?>">
                                
                                <div class="subscription-header">
                                    <h3 class="subscription-title"><?= htmlspecialchars($subscription['name']) ?></h3>
                                    <div class="subscription-price">
                                        <span class="price-value"><?= formatPrice($subscription['price']) ?></span>
                                    </div>
                                </div>
                                
                                <div class="subscription-features">
                                    <div class="feature-item">
                                        <div class="feature-icon">
                                            <img src="assets/svg/calendar.svg" alt="Срок">
                                        </div>
                                        <div class="feature-text">
                                            <span class="feature-label">Срок действия</span>
                                            <span class="feature-value"><?= formatDuration($subscription['duration_days']) ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="feature-item">
                                        <div class="feature-icon">
                                            <img src="assets/svg/dumbbell.svg" alt="Тренировки">
                                        </div>
                                        <div class="feature-text">
                                            <span class="feature-label">Количество тренировок</span>
                                            <span class="feature-value"><?= formatSessions($subscription['sessions_count']) ?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="subscription-description">
                                    <?= nl2br(htmlspecialchars($subscription['description'])) ?>
                                </div>
                                
                                <div class="subscription-actions">
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                    <form action="subscription_handler.php" method="post">
                                        <input type="hidden" name="action" value="purchase">
                                        <input type="hidden" name="subscription_id" value="<?= $subscription['id'] ?>">
                                        <button type="submit" class="btn-primary">Приобрести</button>
                                    </form>
                                    <?php else: ?>
                                    <a href="login.php?redirect=subscriptions.php" class="btn-primary">Войти для покупки</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <div class="subscription-faq">
                <h2 class="section-title">Часто задаваемые вопросы</h2>
                
                <div class="faq-grid">
                    <div class="faq-item">
                        <div class="faq-question">Как приобрести абонемент?</div>
                        <div class="faq-answer">Выберите подходящий абонемент и нажмите кнопку "Приобрести". После оплаты абонемент будет активирован автоматически.</div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">Можно ли заморозить абонемент?</div>
                        <div class="faq-answer">Да, абонементы категории "Премиум" можно заморозить на срок до 30 дней. Для этого обратитесь к администратору клуба.</div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">Как продлить абонемент?</div>
                        <div class="faq-answer">Продлить абонемент можно в личном кабинете или у администратора клуба. При продлении действуют специальные условия для постоянных клиентов.</div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">Что входит в абонемент?</div>
                        <div class="faq-answer">В зависимости от типа абонемента, в него могут входить: посещение тренажерного зала, групповые занятия, персональные тренировки, посещение бассейна и другие услуги.</div>
                    </div>
                </div>
            </div>
            
            <div class="subscription-benefits">
                <h2 class="section-title">Преимущества наших абонементов</h2>
                
                <div class="benefits-grid">
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <img src="assets/svg/check-circle.svg" alt="Гибкость">
                        </div>
                        <h3>Гибкие условия</h3>
                        <p>Широкий выбор абонементов для любых целей и бюджета</p>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <img src="assets/svg/clock.svg" alt="Время">
                        </div>
                        <h3>Удобное расписание</h3>
                        <p>Занимайтесь в любое удобное для вас время</p>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <img src="assets/svg/users.svg" alt="Тренеры">
                        </div>
                        <h3>Профессиональные тренеры</h3>
                        <p>Опытные инструкторы помогут достичь ваших целей</p>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <img src="assets/svg/heart.svg" alt="Атмосфера">
                        </div>
                        <h3>Дружелюбная атмосфера</h3>
                        <p>Комфортная обстановка для эффективных тренировок</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'footer.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Анимация появления элементов при загрузке
            const animateElements = document.querySelectorAll('.subscription-card, .benefit-item, .faq-item');
            animateElements.forEach((el, index) => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';
                setTimeout(() => {
                    el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, 100 + (index * 50));
            });
            
            // Фильтрация абонементов
            const filterTabs = document.querySelectorAll('.filter-tab');
            const categories = document.querySelectorAll('.subscription-category');
            const cards = document.querySelectorAll('.subscription-card');
            
            filterTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Удаляем активный класс со всех вкладок
                    filterTabs.forEach(t => t.classList.remove('active'));
                    // Добавляем активный класс текущей вкладке
                    this.classList.add('active');
                    
                    const filter = this.getAttribute('data-filter');
                    
                    // Анимируем карточки
                    cards.forEach(card => {
                        card.style.opacity = '0';
                        card.style.transform = 'translateY(20px)';
                    });
                    
                    setTimeout(() => {
                        if (filter === 'all') {
                            // Показываем все категории
                            categories.forEach(category => {
                                category.style.display = 'block';
                            });
                        } else {
                            // Показываем только выбранную категорию
                            categories.forEach(category => {
                                if (category.getAttribute('data-category') === filter) {
                                    category.style.display = 'block';
                                } else {
                                    category.style.display = 'none';
                                }
                            });
                        }
                        
                        // Показываем карточки с анимацией
                        const visibleCards = document.querySelectorAll('.subscription-category[style="display: block;"] .subscription-card');
                        visibleCards.forEach((card, index) => {
                            setTimeout(() => {
                                card.style.opacity = '1';
                                card.style.transform = 'translateY(0)';
                            }, 50 * index);
                        });
                    }, 300);
                });
            });
            
            // Раскрытие вопросов FAQ с плавной анимацией
            const faqItems = document.querySelectorAll('.faq-item');
            
            faqItems.forEach(item => {
                const question = item.querySelector('.faq-question');
                const answer = item.querySelector('.faq-answer');
                
                // Сохраняем высоту ответа для анимации
                answer.style.height = '0';
                const height = answer.scrollHeight;
                
                question.addEventListener('click', function() {
                    // Закрываем все другие открытые вопросы
                    faqItems.forEach(otherItem => {
                        if (otherItem !== item && otherItem.classList.contains('active')) {
                            const otherAnswer = otherItem.querySelector('.faq-answer');
                            otherItem.classList.remove('active');
                            otherAnswer.style.height = '0';
                            otherAnswer.style.opacity = '0';
                        }
                    });
                    
                    // Открываем/закрываем текущий вопрос
                    if (item.classList.contains('active')) {
                        item.classList.remove('active');
                        answer.style.height = '0';
                        answer.style.opacity = '0';
                    } else {
                        item.classList.add('active');
                        answer.style.height = height + 'px';
                        answer.style.opacity = '1';
                    }
                });
            });
            
            // Эффекты при наведении на карточки
            subscriptionCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    const icons = this.querySelectorAll('.feature-icon');
                    icons.forEach((icon, index) => {
                        setTimeout(() => {
                            icon.style.transform = 'scale(1.1)';
                        }, index * 100);
                    });
                });
                
                card.addEventListener('mouseleave', function() {
                    const icons = this.querySelectorAll('.feature-icon');
                    icons.forEach((icon, index) => {
                        setTimeout(() => {
                            icon.style.transform = '';
                        }, index * 100);
                    });
                });
            });
            
            // Добавляем эффект пульсации для кнопок
            const buttons = document.querySelectorAll('.btn-primary');
            buttons.forEach(btn => {
                btn.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                    this.style.boxShadow = '0 15px 25px rgba(40, 176, 169, 0.5)';
                });
                
                btn.addEventListener('mouseleave', function() {
                    this.style.transform = '';
                    this.style.boxShadow = '';
                });
            });
        });
    </script>
</body>
</html> 