<?php
session_start();
require_once('../database/config.php');
require_once('includes/auth_check.php');

// Check access for the dashboard
checkAccess('dashboard');

// Get statistics for dashboard
// Общее количество пользователей
$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$totalUsers = $stmt->fetchColumn();

// Новые пользователи за последние 7 дней
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 7 DAY)");
$newUsers = $stmt->fetchColumn();

// Активные абонементы
$stmt = $pdo->query("SELECT COUNT(*) FROM user_subscriptions WHERE end_date > NOW() OR remaining_sessions > 0");
$activeSubscriptions = $stmt->fetchColumn();

// Предстоящие тренировки
$stmt = $pdo->query("SELECT COUNT(*) FROM training_sessions WHERE CONCAT(session_date, ' ', start_time) > NOW()");
$upcomingSessions = $stmt->fetchColumn();

// Ожидающие отзывы
$stmt = $pdo->query("SELECT COUNT(*) FROM reviews WHERE status = 'pending'");
$pendingReviews = $stmt->fetchColumn();

// Тренировки на сегодня
$stmt = $pdo->query("
    SELECT 
        ts.id, ts.session_date, ts.start_time, ts.end_time, 
        u.first_name as user_first_name, u.last_name as user_last_name,
        t.first_name as trainer_first_name, t.last_name as trainer_last_name,
        s.name as service_name
    FROM training_sessions ts
    JOIN users u ON ts.user_id = u.id
    JOIN trainers tr ON ts.trainer_id = tr.id
    JOIN users t ON tr.user_id = t.id
    LEFT JOIN services s ON ts.service_id = s.id
    WHERE ts.session_date = CURRENT_DATE()
    ORDER BY ts.start_time ASC
    LIMIT 5
");
$todaySessions = $stmt->fetchAll();

// Последние зарегистрированные пользователи
$stmt = $pdo->query("
    SELECT id, first_name, last_name, email, phone, created_at
    FROM users
    ORDER BY created_at DESC
    LIMIT 5
");
$recentUsers = $stmt->fetchAll();

// Последние отзывы
$stmt = $pdo->query("
    SELECT 
        r.id, r.rating, r.text, r.created_at, r.status, r.name
    FROM reviews r
    ORDER BY r.created_at DESC
    LIMIT 5
");
$recentReviews = $stmt->fetchAll();

$pageTitle = 'Панель управления';
include 'includes/header.php';
?>

<div class="admin-container">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-3">
            <h1 class="h4">Панель управления</h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <a href="statistics.php" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-chart-line me-1"></i> Статистика
                </a>
            </div>
        </div>

        <!-- Основные показатели -->
        <div class="quick-stats">
            <!-- Пользователи -->
            <div class="stat-card">
                <h4><i class="fas fa-users me-2"></i>Пользователи</h4>
                <p><?= number_format($totalUsers) ?></p>
                <span class="text-muted">Новых за 7 дней: <?= number_format($newUsers) ?></span>
            </div>
            
            <!-- Абонементы -->
            <div class="stat-card">
                <h4><i class="fas fa-id-card me-2"></i>Абонементы</h4>
                <p><?= number_format($activeSubscriptions) ?></p>
                <span class="text-muted">Активные абонементы</span>
            </div>
            
            <!-- Тренировки -->
            <div class="stat-card">
                <h4><i class="fas fa-calendar-alt me-2"></i>Тренировки</h4>
                <p><?= number_format($upcomingSessions) ?></p>
                <span class="text-muted">Предстоящие</span>
            </div>
            
            <!-- Отзывы -->
            <div class="stat-card">
                <h4><i class="fas fa-comments me-2"></i>Отзывы</h4>
                <p><?= number_format($pendingReviews) ?></p>
                <span class="text-muted">Ожидают модерации</span>
            </div>
        </div>

        <div class="row">
            <!-- Тренировки на сегодня -->
            <div class="col-lg-6 mb-3">
                <div class="card h-100">
                    <div class="card-header py-2">
                        <i class="fas fa-calendar-day me-1"></i>
                        Тренировки на сегодня
                    </div>
                    <div class="card-body">
                        <?php if (empty($todaySessions)): ?>
                            <p class="text-center text-muted small">На сегодня нет запланированных тренировок</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Время</th>
                                            <th>Клиент</th>
                                            <th>Тренер</th>
                                            <th>Услуга</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($todaySessions as $session): ?>
                                        <tr>
                                            <td><?= date('H:i', strtotime($session['start_time'])) ?> - <?= date('H:i', strtotime($session['end_time'])) ?></td>
                                            <td><?= htmlspecialchars(($session['user_first_name'] ?? '') . ' ' . ($session['user_last_name'] ?? '')) ?></td>
                                            <td><?= htmlspecialchars(($session['trainer_first_name'] ?? '') . ' ' . ($session['trainer_last_name'] ?? '')) ?></td>
                                            <td><?= htmlspecialchars($session['service_name'] ?? 'Общая тренировка') ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-end mt-2">
                                <a href="training_sessions.php" class="btn btn-sm btn-primary">Все тренировки</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Последние зарегистрированные пользователи -->
            <div class="col-lg-6 mb-3">
                <div class="card h-100">
                    <div class="card-header py-2">
                        <i class="fas fa-user-plus me-1"></i>
                        Новые пользователи
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentUsers)): ?>
                            <p class="text-center text-muted small">Пока нет зарегистрированных пользователей</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Имя</th>
                                            <th>Email</th>
                                            <th>Телефон</th>
                                            <th>Дата</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentUsers as $user): ?>
                                        <tr>
                                            <td><?= htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?></td>
                                            <td><?= htmlspecialchars($user['email'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($user['phone'] ?? '') ?></td>
                                            <td><?= date('d.m.Y', strtotime($user['created_at'])) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-end mt-2">
                                <a href="users.php" class="btn btn-sm btn-primary">Все пользователи</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Последние отзывы -->
        <div class="row">
            <div class="col-12 mb-3">
                <div class="card">
                    <div class="card-header py-2">
                        <i class="fas fa-comment-dots me-1"></i>
                        Последние отзывы
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentReviews)): ?>
                            <p class="text-center text-muted small">Пока нет отзывов</p>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($recentReviews as $review): ?>
                                <div class="list-group-item list-group-item-action p-2">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1 fs-6"><?= htmlspecialchars($review['name'] ?? '') ?></h6>
                                        <small class="text-muted"><?= date('d.m.Y H:i', strtotime($review['created_at'])) ?></small>
                                    </div>
                                    <div class="mb-1">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?= $i <= ($review['rating'] ?? 0) ? 'text-warning' : 'text-secondary' ?> small"></i>
                                        <?php endfor; ?>
                                        <span class="badge <?= ($review['status'] === 'approved') ? 'bg-success' : ($review['status'] === 'rejected' ? 'bg-danger' : 'bg-warning text-dark') ?> ms-2">
                                            <?= ($review['status'] === 'approved') ? 'Одобрен' : ($review['status'] === 'rejected' ? 'Отклонен' : 'На модерации') ?>
                                        </span>
                                    </div>
                                    <p class="mb-0 small text-truncate"><?= htmlspecialchars($review['text'] ?? '') ?></p>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="text-end mt-2">
                                <a href="reviews.php" class="btn btn-sm btn-primary">Все отзывы</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include 'includes/footer.php'; ?> 