<?php
session_start();
require_once('../database/config.php');


require_once('includes/auth_check.php');

// Check access for statistics
checkAccess('statistics');

// Получение периода для статистики
$period = $_GET['period'] ?? 'month';
$currentDate = date('Y-m-d');

switch ($period) {
    case 'week':
        $startDate = date('Y-m-d', strtotime('-1 week'));
        $periodTitle = 'за неделю';
        break;
    case 'month':
        $startDate = date('Y-m-d', strtotime('-1 month'));
        $periodTitle = 'за месяц';
        break;
    case 'quarter':
        $startDate = date('Y-m-d', strtotime('-3 months'));
        $periodTitle = 'за квартал';
        break;
    case 'year':
        $startDate = date('Y-m-d', strtotime('-1 year'));
        $periodTitle = 'за год';
        break;
    case 'all':
        $startDate = '1970-01-01';
        $periodTitle = 'за все время';
        break;
    default:
        $startDate = date('Y-m-d', strtotime('-1 month'));
        $periodTitle = 'за месяц';
        $period = 'month';
}

// Статистика по пользователям
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_users,
        SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as new_users,
        SUM(CASE WHEN last_login >= ? THEN 1 ELSE 0 END) as active_users,
        SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admins,
        SUM(CASE WHEN role = 'manager' THEN 1 ELSE 0 END) as managers,
        SUM(CASE WHEN role = 'user' THEN 1 ELSE 0 END) as regular_users
    FROM users
");
$stmt->execute([$startDate, $startDate]);
$userStats = $stmt->fetch();

// Статистика по тренировкам
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_sessions,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_sessions,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_sessions,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_sessions,
        AVG(CASE WHEN status = 'completed' THEN TIMESTAMPDIFF(MINUTE, start_time, end_time) ELSE NULL END) as avg_duration
    FROM training_sessions
    WHERE session_date >= ?
");
$stmt->execute([$startDate]);
$sessionStats = $stmt->fetch();

// Статистика по абонементам
$stmt = $pdo->prepare("
    SELECT 
        COUNT(us.id) as total_subscriptions,
        SUM(CASE WHEN us.status = 'active' AND (us.end_date > CURRENT_DATE() OR us.remaining_sessions > 0) THEN 1 ELSE 0 END) as active_subscriptions,
        SUM(CASE WHEN us.created_at >= ? THEN 1 ELSE 0 END) as new_subscriptions,
        AVG(s.price) as avg_subscription_price
    FROM user_subscriptions us
    JOIN subscriptions s ON us.subscription_id = s.id
    WHERE us.created_at >= ?
");
$stmt->execute([$startDate, $startDate]);
$subscriptionStats = $stmt->fetch();

// Статистика по доходам
$stmt = $pdo->prepare("
    SELECT 
        SUM(amount) as total_revenue,
        COUNT(*) as total_transactions,
        AVG(amount) as avg_transaction
    FROM payments
    WHERE payment_date >= ?
    AND status = 'completed'
");
$stmt->execute([$startDate]);
$revenueStats = $stmt->fetch();

// Популярные тренеры
$stmt = $pdo->prepare("
    SELECT 
        CONCAT(u.first_name, ' ', u.last_name) as trainer_name,
        COUNT(ts.id) as session_count,
        COUNT(DISTINCT ts.user_id) as unique_clients,
        0 as avg_rating
    FROM trainers t
    JOIN users u ON t.user_id = u.id
    LEFT JOIN training_sessions ts ON t.id = ts.trainer_id
    WHERE ts.session_date >= ?
    GROUP BY t.id, u.first_name, u.last_name
    ORDER BY session_count DESC
    LIMIT 5
");
$stmt->execute([$startDate]);
$popularTrainers = $stmt->fetchAll();

// Статистика по услугам
$stmt = $pdo->prepare("
    SELECT 
        s.name as service_name,
        COUNT(ts.id) as session_count,
        COUNT(DISTINCT ts.user_id) as unique_clients,
        SUM(s.price) as total_revenue
    FROM services s
    LEFT JOIN training_sessions ts ON s.id = ts.service_id
    WHERE ts.session_date >= ?
    GROUP BY s.id, s.name
    ORDER BY session_count DESC
    LIMIT 5
");
$stmt->execute([$startDate]);
$popularServices = $stmt->fetchAll();

// График активности по дням недели
$stmt = $pdo->prepare("
    SELECT 
        DAYOFWEEK(session_date) as day_of_week,
        COUNT(*) as session_count
    FROM training_sessions
    WHERE session_date >= ?
    GROUP BY DAYOFWEEK(session_date)
    ORDER BY day_of_week
");
$stmt->execute([$startDate]);
$dayStats = $stmt->fetchAll();

// График активности по часам
$stmt = $pdo->prepare("
    SELECT 
        HOUR(start_time) as hour_of_day,
        COUNT(*) as session_count
    FROM training_sessions
    WHERE session_date >= ?
    GROUP BY HOUR(start_time)
    ORDER BY hour_of_day
");
$stmt->execute([$startDate]);
$hourStats = $stmt->fetchAll();

$pageTitle = 'Статистика';
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
                <h1 class="h2">Статистика <?= $periodTitle ?></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="?period=week" class="btn <?= $period === 'week' ? 'btn-primary' : 'btn-outline-primary' ?>">Неделя</a>
                        <a href="?period=month" class="btn <?= $period === 'month' ? 'btn-primary' : 'btn-outline-primary' ?>">Месяц</a>
                        <a href="?period=quarter" class="btn <?= $period === 'quarter' ? 'btn-primary' : 'btn-outline-primary' ?>">Квартал</a>
                        <a href="?period=year" class="btn <?= $period === 'year' ? 'btn-primary' : 'btn-outline-primary' ?>">Год</a>
                        <a href="?period=all" class="btn <?= $period === 'all' ? 'btn-primary' : 'btn-outline-primary' ?>">Все время</a>
                    </div>
                    <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> Печать
                    </button>
                </div>
            </div>

            <!-- Основные показатели -->
            <div class="row mb-4">
                <!-- Пользователи -->
                <div class="col-md-3 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h6 class="card-subtitle text-muted">Пользователи</h6>
                                    <h2 class="card-title mb-0"><?= number_format($userStats['total_users'] ?? 0) ?></h2>
                                </div>
                                <div class="icon-shape bg-light text-primary rounded p-3">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                            <div class="small">
                                <span class="text-success me-2">
                                    <i class="fas fa-arrow-up"></i>
                                    <?= number_format($userStats['new_users'] ?? 0) ?>
                                </span>
                                <span class="text-muted">новых пользователей</span>
                            </div>
                            <div class="mt-3 small">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Активные</span>
                                    <span class="text-success"><?= number_format($userStats['active_users'] ?? 0) ?></span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-success" style="width: <?= ($userStats['active_users'] / $userStats['total_users'] * 100) ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Тренировки -->
                <div class="col-md-3 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h6 class="card-subtitle text-muted">Тренировки</h6>
                                    <h2 class="card-title mb-0"><?= number_format($sessionStats['total_sessions'] ?? 0) ?></h2>
                                </div>
                                <div class="icon-shape bg-light text-success rounded p-3">
                                    <i class="fas fa-dumbbell"></i>
                                </div>
                            </div>
                            <div class="small">
                                <span class="text-success me-2">
                                    <?= number_format(($sessionStats['completed_sessions'] / ($sessionStats['total_sessions'] ?: 1)) * 100, 1) ?>%
                                </span>
                                <span class="text-muted">завершенных тренировок</span>
                            </div>
                            <div class="mt-3 small">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span>Статусы</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-success" style="width: <?= ($sessionStats['completed_sessions'] / ($sessionStats['total_sessions'] ?: 1) * 100) ?>%" title="Завершено"></div>
                                    <div class="progress-bar bg-warning" style="width: <?= ($sessionStats['pending_sessions'] / ($sessionStats['total_sessions'] ?: 1) * 100) ?>%" title="В ожидании"></div>
                                    <div class="progress-bar bg-danger" style="width: <?= ($sessionStats['cancelled_sessions'] / ($sessionStats['total_sessions'] ?: 1) * 100) ?>%" title="Отменено"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Абонементы -->
                <div class="col-md-3 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h6 class="card-subtitle text-muted">Абонементы</h6>
                                    <h2 class="card-title mb-0"><?= number_format($subscriptionStats['total_subscriptions'] ?? 0) ?></h2>
                                </div>
                                <div class="icon-shape bg-light text-info rounded p-3">
                                    <i class="fas fa-ticket-alt"></i>
                                </div>
                            </div>
                            <div class="small">
                                <span class="text-success me-2">
                                    <i class="fas fa-arrow-up"></i>
                                    <?= number_format($subscriptionStats['new_subscriptions'] ?? 0) ?>
                                </span>
                                <span class="text-muted">новых абонементов</span>
                            </div>
                            <div class="mt-3 small">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Активные</span>
                                    <span class="text-success"><?= number_format($subscriptionStats['active_subscriptions'] ?? 0) ?></span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-info" style="width: <?= ($subscriptionStats['active_subscriptions'] / ($subscriptionStats['total_subscriptions'] ?: 1) * 100) ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Доход -->
                <div class="col-md-3 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h6 class="card-subtitle text-muted">Доход</h6>
                                    <h2 class="card-title mb-0"><?= number_format($revenueStats['total_revenue'] ?? 0) ?> ₽</h2>
                                </div>
                                <div class="icon-shape bg-light text-warning rounded p-3">
                                    <i class="fas fa-coins"></i>
                                </div>
                            </div>
                            <div class="small">
                                <span class="text-success me-2">
                                    <?= number_format($revenueStats['avg_transaction'] ?? 0) ?> ₽
                                </span>
                                <span class="text-muted">средний чек</span>
                            </div>
                            <div class="mt-3 small">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Транзакции</span>
                                    <span class="text-success"><?= number_format($revenueStats['total_transactions'] ?? 0) ?></span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-warning" style="width: 100%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- График активности по дням недели -->
                <div class="col-lg-8 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-0">
                            <h5 class="card-title mb-0">Активность по дням недели</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="weekdayChart" height="300"></canvas>
                        </div>
                    </div>
                </div>

                <!-- График активности по часам -->
                <div class="col-lg-4 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-0">
                            <h5 class="card-title mb-0">Активность по часам</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="hourlyChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Популярные тренеры -->
                <div class="col-md-6 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-0">
                            <h5 class="card-title mb-0">Популярные тренеры</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Тренер</th>
                                            <th>Тренировки</th>
                                            <th>Клиенты</th>
                                            <th>Рейтинг</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($popularTrainers as $trainer): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($trainer['trainer_name']) ?></td>
                                            <td><?= number_format($trainer['session_count'] ?? 0) ?></td>
                                            <td><?= number_format($trainer['unique_clients'] ?? 0) ?></td>
                                            <td>
                                                <?php if ($trainer['avg_rating']): ?>
                                                    <div class="text-warning">
                                                        <?php
                                                        $rating = round($trainer['avg_rating'] ?? 0);
                                                        for ($i = 1; $i <= 5; $i++) {
                                                            echo $i <= $rating ? '★' : '☆';
                                                        }
                                                        ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">Нет оценок</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Популярные услуги -->
                <div class="col-md-6 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-0">
                            <h5 class="card-title mb-0">Популярные услуги</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Услуга</th>
                                            <th>Тренировки</th>
                                            <th>Клиенты</th>
                                            <th>Доход</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($popularServices as $service): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($service['service_name']) ?></td>
                                            <td><?= number_format($service['session_count'] ?? 0) ?></td>
                                            <td><?= number_format($service['unique_clients'] ?? 0) ?></td>
                                            <td><?= number_format($service['total_revenue'] ?? 0) ?> ₽</td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
// Настройка графиков
document.addEventListener('DOMContentLoaded', function() {
    // График активности по дням недели
    const weekdayData = {
        labels: ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'],
        datasets: [{
            label: 'Количество тренировок',
            data: [
                <?php
                $weekdayData = array_fill(0, 7, 0);
                foreach ($dayStats as $day) {
                    $weekdayData[$day['day_of_week'] - 1] = $day['session_count'];
                }
                echo implode(', ', $weekdayData);
                ?>
            ],
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1,
            borderRadius: 5,
            tension: 0.1
        }]
    };

    new Chart(document.getElementById('weekdayChart'), {
        type: 'bar',
        data: weekdayData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // График активности по часам
    const hourlyData = {
        labels: Array.from({length: 24}, (_, i) => `${i}:00`),
        datasets: [{
            label: 'Количество тренировок',
            data: [
                <?php
                $hourlyData = array_fill(0, 24, 0);
                foreach ($hourStats as $hour) {
                    $hourlyData[$hour['hour_of_day']] = $hour['session_count'];
                }
                echo implode(', ', $hourlyData);
                ?>
            ],
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1,
            tension: 0.4,
            fill: true
        }]
    };

    new Chart(document.getElementById('hourlyChart'), {
        type: 'line',
        data: hourlyData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            }
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?> 