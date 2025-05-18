<?php
session_start();
require_once('../database/config.php');

// Проверка доступа (только для администраторов и менеджеров)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'manager'])) {
    header('Location: ../login.php');
    exit();
}

// Получение периода для статистики
$period = $_GET['period'] ?? 'month'; // month, quarter, year, all
$currentDate = date('Y-m-d');

switch ($period) {
    case 'week':
        $startDate = date('Y-m-d', strtotime('-1 week'));
        $periodTitle = 'за неделю';
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
    default: // month
        $startDate = date('Y-m-d', strtotime('-1 month'));
        $periodTitle = 'за месяц';
        $period = 'month';
}

// Статистика по пользователям
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_users,
        SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as new_users,
        SUM(CASE WHEN last_login >= ? THEN 1 ELSE 0 END) as active_users
    FROM users
");
$stmt->execute([$startDate, $startDate]);
$userStats = $stmt->fetch();

// Статистика по тренировкам
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_sessions,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_sessions,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_sessions
    FROM training_sessions
    WHERE session_date >= ?
");
$stmt->execute([$startDate]);
$sessionStats = $stmt->fetch();

// Статистика по абонементам
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_subscriptions,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_subscriptions
    FROM user_subscriptions
    WHERE created_at >= ?
");
$stmt->execute([$startDate]);
$subscriptionStats = $stmt->fetch();

// Популярные тренеры
$stmt = $pdo->prepare("
    SELECT 
        CONCAT(u.first_name, ' ', u.last_name) as trainer_name,
        COUNT(ts.id) as session_count
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

// Статистика по дням недели
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

// Преобразование дней недели в названия
$dayNames = [
    1 => 'Воскресенье',
    2 => 'Понедельник',
    3 => 'Вторник',
    4 => 'Среда',
    5 => 'Четверг',
    6 => 'Пятница',
    7 => 'Суббота'
];

// Статистика по услугам
$stmt = $pdo->prepare("
    SELECT 
        s.name as service_name,
        COUNT(ts.id) as session_count
    FROM services s
    LEFT JOIN training_sessions ts ON s.id = ts.service_id
    WHERE ts.session_date >= ?
    GROUP BY s.id, s.name
    ORDER BY session_count DESC
    LIMIT 5
");
$stmt->execute([$startDate]);
$popularServices = $stmt->fetchAll();

$pageTitle = 'Статистика';
include 'includes/header.php';
?>

<div class="admin-container">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-3">
            <h1 class="h4"><?= $pageTitle ?> <?= $periodTitle ?></h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <div class="btn-group btn-group-sm me-2">
                    <a href="?period=week" class="btn <?= $period === 'week' ? 'btn-primary' : 'btn-outline-primary' ?>">Неделя</a>
                    <a href="?period=month" class="btn <?= $period === 'month' ? 'btn-primary' : 'btn-outline-primary' ?>">Месяц</a>
                    <a href="?period=quarter" class="btn <?= $period === 'quarter' ? 'btn-primary' : 'btn-outline-primary' ?>">Квартал</a>
                    <a href="?period=year" class="btn <?= $period === 'year' ? 'btn-primary' : 'btn-outline-primary' ?>">Год</a>
                    <a href="?period=all" class="btn <?= $period === 'all' ? 'btn-primary' : 'btn-outline-primary' ?>">Все время</a>
                </div>
            </div>
        </div>

        <!-- Основные показатели -->
        <div class="quick-stats">
            <div class="stat-card">
                <h4><i class="fas fa-users me-2"></i>Пользователи</h4>
                <p><?= number_format($userStats['total_users']) ?></p>
                <span class="text-muted">
                    Новых: <?= number_format($userStats['new_users']) ?> | 
                    Активных: <?= number_format($userStats['active_users']) ?>
                </span>
            </div>
            <div class="stat-card">
                <h4><i class="fas fa-dumbbell me-2"></i>Тренировки</h4>
                <p><?= number_format($sessionStats['total_sessions']) ?></p>
                <span class="text-muted">
                    Проведено: <?= number_format($sessionStats['completed_sessions']) ?> | 
                    Отменено: <?= number_format($sessionStats['cancelled_sessions']) ?>
                </span>
            </div>
            <div class="stat-card">
                <h4><i class="fas fa-id-card me-2"></i>Абонементы</h4>
                <p><?= number_format($subscriptionStats['total_subscriptions']) ?></p>
                <span class="text-muted">
                    Активных: <?= number_format($subscriptionStats['active_subscriptions']) ?>
                </span>
            </div>
        </div>

        <div class="row">
            <!-- График загруженности по дням недели -->
            <div class="col-lg-8 mb-3">
                <div class="card h-100">
                    <div class="card-header py-2">
                        <i class="fas fa-calendar-day me-1"></i>
                        Загруженность по дням недели
                    </div>
                    <div class="card-body">
                        <canvas id="weekdayChart" height="250"></canvas>
                    </div>
                </div>
            </div>

            <!-- Популярные тренеры -->
            <div class="col-lg-4 mb-3">
                <div class="card h-100">
                    <div class="card-header py-2">
                        <i class="fas fa-user-tie me-1"></i>
                        Популярные тренеры
                    </div>
                    <div class="card-body">
                        <?php if (empty($popularTrainers)): ?>
                            <p class="text-center text-muted small">Нет данных за выбранный период</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Тренер</th>
                                            <th>Тренировок</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($popularTrainers as $trainer): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($trainer['trainer_name'] ?? '') ?></td>
                                            <td><?= number_format($trainer['session_count']) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Популярные услуги -->
            <div class="col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-header py-2">
                        <i class="fas fa-list me-1"></i>
                        Популярные услуги
                    </div>
                    <div class="card-body">
                        <?php if (empty($popularServices)): ?>
                            <p class="text-center text-muted small">Нет данных за выбранный период</p>
                        <?php else: ?>
                            <canvas id="servicesChart" height="220"></canvas>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Соотношение статусов тренировок -->
            <div class="col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-header py-2">
                        <i class="fas fa-check-circle me-1"></i>
                        Статусы тренировок
                    </div>
                    <div class="card-body">
                        <canvas id="sessionsStatusChart" height="220"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include 'includes/footer.php'; ?>

<script>
// График загруженности по дням недели
const weekdayData = {
    labels: [
        <?php
        $labels = [];
        $data = [];
        for ($i = 1; $i <= 7; $i++) {
            $found = false;
            foreach ($dayStats as $day) {
                if ($day['day_of_week'] == $i) {
                    $labels[] = "'" . $dayNames[$i] . "'";
                    $data[] = $day['session_count'];
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $labels[] = "'" . $dayNames[$i] . "'";
                $data[] = 0;
            }
        }
        echo implode(', ', $labels);
        ?>
    ],
    datasets: [{
        label: 'Количество тренировок',
        data: [<?= implode(', ', $data) ?>],
        backgroundColor: 'rgba(50, 163, 158, 0.2)',
        borderColor: 'rgba(50, 163, 158, 1)',
        borderWidth: 1,
        borderRadius: 3,
        tension: 0.1
    }]
};

const weekdayConfig = {
    type: 'bar',
    data: weekdayData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)'
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
};

// График популярных услуг
const servicesData = {
    labels: [
        <?php
        $serviceLabels = [];
        $serviceData = [];
        foreach ($popularServices as $service) {
            $serviceLabels[] = "'" . addslashes($service['service_name'] ?? 'Неизвестно') . "'";
            $serviceData[] = $service['session_count'];
        }
        echo implode(', ', $serviceLabels);
        ?>
    ],
    datasets: [{
        data: [<?= implode(', ', $serviceData) ?>],
        backgroundColor: [
            'rgba(50, 163, 158, 0.7)',
            'rgba(38, 125, 121, 0.7)',
            'rgba(30, 98, 95, 0.7)',
            'rgba(20, 74, 72, 0.7)',
            'rgba(15, 48, 45, 0.7)'
        ],
        borderColor: 'rgba(255, 255, 255, 0.8)',
        borderWidth: 1
    }]
};

const servicesConfig = {
    type: 'pie',
    data: servicesData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'right',
                labels: {
                    boxWidth: 12,
                    padding: 10
                }
            }
        }
    }
};

// График статусов тренировок
const sessionsStatusData = {
    labels: ['Проведено', 'Отменено', 'В ожидании'],
    datasets: [{
        data: [
            <?= $sessionStats['completed_sessions'] ?>, 
            <?= $sessionStats['cancelled_sessions'] ?>, 
            <?= $sessionStats['total_sessions'] - $sessionStats['completed_sessions'] - $sessionStats['cancelled_sessions'] ?>
        ],
        backgroundColor: [
            'rgba(40, 167, 69, 0.7)', // Зеленый
            'rgba(220, 53, 69, 0.7)',  // Красный
            'rgba(255, 193, 7, 0.7)'   // Желтый
        ],
        borderColor: 'rgba(255, 255, 255, 0.8)',
        borderWidth: 1
    }]
};

const sessionsStatusConfig = {
    type: 'doughnut',
    data: sessionsStatusData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'right',
                labels: {
                    boxWidth: 12,
                    padding: 10
                }
            }
        }
    }
};

// Создаем графики
document.addEventListener('DOMContentLoaded', function() {
    const weekdayChart = new Chart(
        document.getElementById('weekdayChart'),
        weekdayConfig
    );
    
    <?php if (!empty($popularServices)): ?>
    const servicesChart = new Chart(
        document.getElementById('servicesChart'),
        servicesConfig
    );
    <?php endif; ?>
    
    const sessionsStatusChart = new Chart(
        document.getElementById('sessionsStatusChart'),
        sessionsStatusConfig
    );
});
</script> 