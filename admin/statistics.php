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

$pageTitle = 'Статистика';
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Статистика <?= $periodTitle ?></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="?period=week" class="btn btn-sm btn-outline-secondary <?= $period === 'week' ? 'active' : '' ?>">Неделя</a>
                        <a href="?period=month" class="btn btn-sm btn-outline-secondary <?= $period === 'month' ? 'active' : '' ?>">Месяц</a>
                        <a href="?period=quarter" class="btn btn-sm btn-outline-secondary <?= $period === 'quarter' ? 'active' : '' ?>">Квартал</a>
                        <a href="?period=year" class="btn btn-sm btn-outline-secondary <?= $period === 'year' ? 'active' : '' ?>">Год</a>
                        <a href="?period=all" class="btn btn-sm btn-outline-secondary <?= $period === 'all' ? 'active' : '' ?>">Все время</a>
                    </div>
                </div>
            </div>

            <!-- Основные показатели -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Пользователи</h5>
                            <p class="card-text">
                                Всего: <?= number_format($userStats['total_users']) ?><br>
                                Новых: <?= number_format($userStats['new_users']) ?><br>
                                Активных: <?= number_format($userStats['active_users']) ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Тренировки</h5>
                            <p class="card-text">
                                Всего: <?= number_format($sessionStats['total_sessions']) ?><br>
                                Проведено: <?= number_format($sessionStats['completed_sessions']) ?><br>
                                Отменено: <?= number_format($sessionStats['cancelled_sessions']) ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Абонементы</h5>
                            <p class="card-text">
                                Всего: <?= number_format($subscriptionStats['total_subscriptions']) ?><br>
                                Активных: <?= number_format($subscriptionStats['active_subscriptions']) ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <!-- График загруженности по дням недели -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            Загруженность по дням недели
                        </div>
                        <div class="card-body">
                            <canvas id="weekdayChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Популярные тренеры -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            Популярные тренеры
                        </div>
                        <div class="card-body">
                            <?php if (empty($popularTrainers)): ?>
                                <p class="text-center text-muted">Нет данных за выбранный период</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Тренер</th>
                                                <th>Тренировок</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($popularTrainers as $trainer): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($trainer['trainer_name']) ?></td>
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
        </main>
    </div>
</div>

<!-- Подключаем Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
        backgroundColor: 'rgba(54, 162, 235, 0.2)',
        borderColor: 'rgba(54, 162, 235, 1)',
        borderWidth: 1
    }]
};

const weekdayConfig = {
    type: 'bar',
    data: weekdayData,
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
};

// Создаем график
const weekdayChart = new Chart(
    document.getElementById('weekdayChart'),
    weekdayConfig
);
</script>

<?php include 'includes/footer.php'; ?> 