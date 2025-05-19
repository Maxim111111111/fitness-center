<?php
session_start();
require_once 'database/config.php';
require_once 'includes/settings.php';

// Проверка режима обслуживания
if (is_maintenance_mode() && (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin')) {
    // Отображаем страницу режима обслуживания
    include 'maintenance.php';
    exit();
}

// Set current page for active menu
$currentPage = 'team_tasks';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Распределение задач | Moreon Fitness</title>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main>
        <section class="team-tasks">
            <h1 class="team-tasks__title">Распределение задач <span class="team-tasks__title-span">команды</span></h1>
            <p class="team-tasks__description">План разработки проекта Moreon Fitness на 12 дней с распределением задач между тремя разработчиками</p>
            
            <!-- Максим - ведущий разработчик -->
            <div class="team-member team-member--lead">
                <h2 class="team-member__name">
                    <span class="team-member__icon">М</span>
                    Максим
                </h2>
                <p class="team-member__role">Ведущий разработчик (16 основных задач + админ-панель)</p>
                
                <div class="task-category">
                    <h3 class="task-category__title">Основные задачи (16)</h3>
                    <div class="tasks-list">
                        <div class="task-item">
                            <div class="task-item__title">Проектирование и создание базы данных</div>
                            <div class="task-item__days">Дни: 1-2</div>
                        </div>
                        <div class="task-item">
                            <div class="task-item__title">Разработка архитектуры приложения</div>
                            <div class="task-item__days">Дни: 1-2</div>
                        </div>
                        <div class="task-item">
                            <div class="task-item__title">Система авторизации и регистрации</div>
                            <div class="task-item__days">Дни: 2-3</div>
                        </div>
                        <div class="task-item">
                            <div class="task-item__title">Система ролей и прав доступа</div>
                            <div class="task-item__days">Дни: 3-4</div>
                        </div>
                        <div class="task-item">
                            <div class="task-item__title">API для тренеров и пользователей</div>
                            <div class="task-item__days">Дни: 4-5</div>
                        </div>
                        <div class="task-item">
                            <div class="task-item__title">Система бронирования тренировок</div>
                            <div class="task-item__days">Дни: 5-6</div>
                        </div>
                        <div class="task-item">
                            <div class="task-item__title">Обработка платежей и абонементов</div>
                            <div class="task-item__days">Дни: 6-7</div>
                        </div>
                        <div class="task-item">
                            <div class="task-item__title">Интеграция с внешними сервисами</div>
                            <div class="task-item__days">Дни: 7-8</div>
                        </div>
                        <div class="task-item">
                            <div class="task-item__title">Система уведомлений</div>
                            <div class="task-item__days">Дни: 8</div>
                        </div>
                        <div class="task-item">
                            <div class="task-item__title">Модуль экспорта данных</div>
                            <div class="task-item__days">Дни: 9</div>
                        </div>
                        <div class="task-item">
                            <div class="task-item__title">Настройка кеширования</div>
                            <div class="task-item__days">Дни: 9</div>
                        </div>
                        <div class="task-item">
                            <div class="task-item__title">Система логирования</div>
                            <div class="task-item__days">Дни: 10</div>
                        </div>
                        <div class="task-item">
                            <div class="task-item__title">Резервное копирование</div>
                            <div class="task-item__days">Дни: 10</div>
                        </div>
                        <div class="task-item">
                            <div class="task-item__title">Оптимизация SQL-запросов</div>
                            <div class="task-item__days">Дни: 11</div>
                        </div>
                        <div class="task-item">
                            <div class="task-item__title">Тестирование безопасности</div>
                            <div class="task-item__days">Дни: 11</div>
                        </div>
                        <div class="task-item">
                            <div class="task-item__title">Финальное тестирование и исправление</div>
                            <div class="task-item__days">Дни: 12</div>
                        </div>
                    </div>
                </div>
                
                <div class="task-category">
                    <h3 class="task-category__title">Админ-панель</h3>
                    <div class="tasks-list">
                        <div class="task-item">
                            <div class="task-item__title">Разработка админ-панели</div>
                            <div class="task-item__days">Дни: 4-5</div>
                        </div>
                        <div class="task-item">
                            <div class="task-item__title">Управление пользователями</div>
                            <div class="task-item__days">Дни: 6-7</div>
                        </div>
                        <div class="task-item">
                            <div class="task-item__title">Управление тренерами</div>
                            <div class="task-item__days">Дни: 7-8</div>
                        </div>
                        <div class="task-item">
                            <div class="task-item__title">Управление расписанием</div>
                            <div class="task-item__days">Дни: 8-9</div>
                        </div>
                        <div class="task-item">
                            <div class="task-item__title">Статистика и отчеты</div>
                            <div class="task-item__days">Дни: 9-10</div>
                        </div>
                        <div class="task-item">
                            <div class="task-item__title">Модерация отзывов</div>
                            <div class="task-item__days">Дни: 10-11</div>
                        </div>
                        <div class="task-item">
                            <div class="task-item__title">Настройки системы</div>
                            <div class="task-item__days">Дни: 11-12</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Павел - фронтенд-разработчик -->
            <div class="team-member team-member--frontend">
                <h2 class="team-member__name">
                    <span class="team-member__icon">П</span>
                    Павел
                </h2>
                <p class="team-member__role">Фронтенд-разработчик (8 задач)</p>
                
                <div class="task-category">
                    <h3 class="task-category__title">Фронтенд разработка</h3>
                    <div class="tasks-list">
                        <div class="task-item">
                            <div class="task-item__title">Верстка главной страницы</div>
                            <div class="task-item__days">Дни: 1-2</div>
                        </div>
                        <div class="task-item">
                            <div class="task-item__title">Верстка страницы услуг</div>
                            <div class="task-item__days">Дни: 3-4</div>
                        </div>
                        <div class="task-item">
                            <div class="task-item__title">Верстка страницы тренеров</div>
                            <div class="task-item__days">Дни: 5-6</div>
                        </div>
                        <div class="task-item">
                            <div class="task-item__title">Верстка страницы профиля</div>
                            <div class="task-item__days">Дни: 7-8</div>
                        </div>
                        <div class="task-item">
                            <div class="task-item__title">JavaScript для слайдеров</div>
                            <div class="task-item__days">Дни: 8-9</div>
                        </div>
                        <div class="task-item">
                            <div class="task-item__title">Валидация форм</div>
                            <div class="task-item__days">Дни: 9-10</div>
                        </div>
                        <div class="task-item">
                            <div class="task-item__title">Анимации и эффекты</div>
                            <div class="task-item__days">Дни: 10-11</div>
                        </div>
                        <div class="task-item">
                            <div class="task-item__title">Адаптивная верстка и тестирование</div>
                            <div class="task-item__days">Дни: 11-12</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Айдар - backend-разработчик -->
            <div class="team-member team-member--backend">
                <h2 class="team-member__name">
                    <span class="team-member__icon">А</span>
                    Айдар
                </h2>
                <p class="team-member__role">Backend-разработчик (8 задач)</p>
                
                <div class="task-category">
                    <h3 class="task-category__title">Backend разработка</h3>
                    <div class="tasks-list">
                        <div class="task-item">
                            <div class="task-item__title">Разработка модуля услуг</div>
                            <div class="task-item__days">Дни: 1-2</div>
                        </div>
                        <div class="task-item">
                            <div class="task-item__title">Разработка модуля отзывов</div>
                            <div class="task-item__days">Дни: 3-4</div>
                        </div>
                        <div class="task-item">
                            <div class="task-item__title">Интеграция с Яндекс.Картами</div>
                            <div class="task-item__days">Дни: 5-6</div>
                        </div>
                        <div class="task-item">
                            <div class="task-item__title">Система обработки изображений</div>
                            <div class="task-item__days">Дни: 6-7</div>
                        </div>
                        <div class="task-item">
                            <div class="task-item__title">Личный кабинет пользователя</div>
                            <div class="task-item__days">Дни: 7-8</div>
                        </div>
                        <div class="task-item">
                            <div class="task-item__title">Страница о нас</div>
                            <div class="task-item__days">Дни: 9-10</div>
                        </div>
                        <div class="task-item">
                            <div class="task-item__title">Страница контактов</div>
                            <div class="task-item__days">Дни: 10-11</div>
                        </div>
                        <div class="task-item">
                            <div class="task-item__title">Поддержка и документация</div>
                            <div class="task-item__days">Дни: 11-12</div>
                        </div>
                    </div>
                </div>
            </div>
            
        
        </section>
    </main>
    
    <?php include 'footer.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Добавление анимации при прокрутке
            const taskItems = document.querySelectorAll('.task-item');
            const teamMembers = document.querySelectorAll('.team-member');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = 1;
                        entry.target.style.transform = entry.target.classList.contains('team-member') 
                            ? 'translateY(0)' 
                            : 'translateY(0)';
                    }
                });
            }, {
                threshold: 0.1
            });
            
            taskItems.forEach(item => {
                item.style.opacity = 0;
                item.style.transform = 'translateY(20px)';
                item.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                observer.observe(item);
            });
            
            teamMembers.forEach((member, index) => {
                member.style.opacity = 0;
                member.style.transform = 'translateY(30px)';
                member.style.transition = `opacity 0.6s ease ${index * 0.2}s, transform 0.6s ease ${index * 0.2}s`;
                observer.observe(member);
            });
        });
    </script>
</body>
</html> 