<?php
// Страница управления записями на тренировки
session_start();
require_once '../database/config.php';
require_once 'includes/auth_check.php';

// Check access for training sessions
checkAccess('training_sessions');

// Отладочная информация о текущем пользователе
error_log("Current user: id=" . $_SESSION['user_id'] . ", role=" . $_SESSION['user_role']);

// Обработка ajax запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $response = ['success' => false, 'message' => 'Неизвестное действие'];
    
    // Изменение статуса записи
    if ($_POST['action'] === 'change_status' && isset($_POST['session_id']) && isset($_POST['status'])) {
        $session_id = intval($_POST['session_id']);
        $status = $_POST['status'];
        
        // Проверка валидности статуса
        $valid_statuses = ['pending', 'confirmed', 'cancelled', 'completed'];
        if (!in_array($status, $valid_statuses)) {
            $response = ['success' => false, 'message' => 'Недопустимый статус'];
        } else {
            try {
                // If trainer role, make sure they can only update their own sessions
                if ($_SESSION['user_role'] === 'trainer') {
                    // Get trainer_id for this user
                    $trainerStmt = $pdo->prepare("SELECT id FROM trainers WHERE user_id = ?");
                    $trainerStmt->execute([$_SESSION['user_id']]);
                    $trainerId = $trainerStmt->fetchColumn();
                    
                    // Отладочная информация
                    error_log("Trainer filter: user_id=" . $_SESSION['user_id'] . ", trainer_id=" . ($trainerId ? $trainerId : 'not_found'));
                    
                    if (!$trainerId) {
                        $response = ['success' => false, 'message' => 'У вас нет прав на изменение этой записи'];
                        error_log("No trainer record found for user_id=" . $_SESSION['user_id']);
                    } else {
                        $stmt = $pdo->prepare("UPDATE training_sessions SET status = ?, updated_at = NOW() WHERE id = ? AND trainer_id = ?");
                        $stmt->execute([$status, $session_id, $trainerId]);
                        
                        if ($stmt->rowCount() > 0) {
                            // Если статус изменен на "completed", обновляем количество оставшихся тренировок
                            if ($status === 'completed') {
                                updateRemainingSessionsCount($pdo, $session_id);
                            }
                            $response = ['success' => true, 'message' => 'Статус успешно обновлен'];
                        } else {
                            $response = ['success' => false, 'message' => 'У вас нет прав на изменение этой записи или запись не найдена'];
                        }
                    }
                } else {
                    // Admin or manager can update any session
                    $stmt = $pdo->prepare("UPDATE training_sessions SET status = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$status, $session_id]);
                    
                    if ($stmt->rowCount() > 0) {
                        // Если статус изменен на "completed", обновляем количество оставшихся тренировок
                        if ($status === 'completed') {
                            updateRemainingSessionsCount($pdo, $session_id);
                        }
                        $response = ['success' => true, 'message' => 'Статус успешно обновлен'];
                    } else {
                        $response = ['success' => false, 'message' => 'Ошибка при обновлении статуса'];
                    }
                }
            } catch (PDOException $e) {
                $response = ['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()];
            }
        }
    }
    
    // Удаление записи (только для admin и manager)
    if ($_POST['action'] === 'delete' && isset($_POST['session_id']) && $_SESSION['user_role'] !== 'trainer') {
        $session_id = intval($_POST['session_id']);
        
        try {
            $stmt = $pdo->prepare("DELETE FROM training_sessions WHERE id = ?");
            $stmt->execute([$session_id]);
            
            if ($stmt->rowCount() > 0) {
                $response = ['success' => true, 'message' => 'Запись успешно удалена'];
            } else {
                $response = ['success' => false, 'message' => 'Ошибка при удалении записи'];
            }
        } catch (PDOException $e) {
            $response = ['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()];
        }
    }
    
    // Возвращаем результат
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

/**
 * Функция для обновления количества оставшихся тренировок в абонементе пользователя
 * @param PDO $pdo - объект PDO для работы с базой данных
 * @param int $session_id - ID тренировки
 */
function updateRemainingSessionsCount($pdo, $session_id) {
    try {
        error_log("===== НАЧАЛО ОБНОВЛЕНИЯ ТРЕНИРОВОК =====");
        error_log("Начало обновления количества оставшихся тренировок для тренировки ID: $session_id");
        
        // Получаем информацию о тренировке и пользователе
        $stmt = $pdo->prepare("
            SELECT ts.user_id, ts.service_id, u.first_name, u.last_name, ts.status, ts.updated_at as session_updated
            FROM training_sessions ts
            JOIN users u ON ts.user_id = u.id
            WHERE ts.id = ?
        ");
        $stmt->execute([$session_id]);
        $session = $stmt->fetch();
        
        if (!$session) {
            error_log("Тренировка с ID $session_id не найдена");
            return;
        }
        
        $user_id = $session['user_id'];
        $user_name = $session['first_name'] . ' ' . $session['last_name'];
        $session_status = $session['status'];
        $session_updated = $session['session_updated'];
        
        error_log("Обработка тренировки для пользователя: $user_name (ID: $user_id), статус тренировки: $session_status, время обновления: $session_updated");
        
        // Проверяем, действительно ли статус "completed"
        if ($session_status !== 'completed') {
            error_log("Статус тренировки не 'completed', а '$session_status'. Обновление счетчика не требуется.");
            return;
        }
        
        // Получаем активный абонемент пользователя
        $stmt = $pdo->prepare("
            SELECT us.id, us.subscription_id, us.remaining_sessions, us.status, us.updated_at as sub_updated, 
                   s.name as subscription_name, s.sessions_count
            FROM user_subscriptions us
            JOIN subscriptions s ON us.subscription_id = s.id
            WHERE us.user_id = ? 
            AND us.status = 'active' 
            AND us.end_date >= CURDATE()
            ORDER BY us.end_date DESC
            LIMIT 1
        ");
        $stmt->execute([$user_id]);
        $subscription = $stmt->fetch();
        
        if (!$subscription) {
            error_log("Активный абонемент для пользователя $user_id ($user_name) не найден");
            return;
        }
        
        error_log("Найден активный абонемент: {$subscription['subscription_name']} (ID: {$subscription['id']})");
        error_log("Текущее значение remaining_sessions: " . var_export($subscription['remaining_sessions'], true));
        error_log("Общее количество тренировок в абонементе: " . var_export($subscription['sessions_count'], true));
        error_log("Статус абонемента: " . $subscription['status']);
        error_log("Время последнего обновления абонемента: " . $subscription['sub_updated']);
        
        // Проверяем, не было ли уже учтено это обновление
        if ($subscription['sub_updated'] > $session_updated) {
            error_log("Абонемент уже был обновлен после изменения статуса тренировки. Пропускаем обновление.");
            error_log("Время обновления абонемента: {$subscription['sub_updated']}, время обновления тренировки: $session_updated");
            return;
        }
        
        // Проверяем, есть ли у абонемента ограничение по количеству тренировок
        if ($subscription['remaining_sessions'] !== null) {
            // Уменьшаем количество оставшихся тренировок на 1, но не меньше 0
            $remaining = max(0, $subscription['remaining_sessions'] - 1);
            
            error_log("Обновление количества тренировок: было {$subscription['remaining_sessions']}, станет $remaining");
            
            // Обновляем количество оставшихся тренировок
            $stmt = $pdo->prepare("
                UPDATE user_subscriptions 
                SET remaining_sessions = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$remaining, $subscription['id']]);
            
            // Проверяем, действительно ли обновилось значение
            $rowCount = $stmt->rowCount();
            error_log("Количество обновленных строк: $rowCount");
            
            if ($rowCount > 0) {
                error_log("Значение remaining_sessions успешно обновлено в базе данных");
                
                // Проверяем значение после обновления
                $stmt = $pdo->prepare("
                    SELECT remaining_sessions, updated_at FROM user_subscriptions WHERE id = ?
                ");
                $stmt->execute([$subscription['id']]);
                $updated = $stmt->fetch();
                error_log("Проверка после обновления: remaining_sessions = {$updated['remaining_sessions']}, updated_at = {$updated['updated_at']}");
                
                // Обновляем кэш сессии, если это нужно
                if (isset($_SESSION['user_subscription']) && $_SESSION['user_subscription']['id'] == $subscription['id']) {
                    $_SESSION['user_subscription']['remaining_sessions'] = $remaining;
                    error_log("Обновлен кэш сессии для абонемента");
                }
            } else {
                error_log("ОШИБКА: Не удалось обновить значение remaining_sessions в базе данных");
            }
            
            // Логируем в системный журнал
            $stmt = $pdo->prepare("
                INSERT INTO audit_log (user_id, action, entity_type, entity_id, details) 
                VALUES (?, 'update_sessions', 'user_subscription', ?, ?)
            ");
            $details = json_encode([
                'previous_value' => $subscription['remaining_sessions'],
                'new_value' => $remaining,
                'training_session_id' => $session_id
            ]);
            $stmt->execute([$_SESSION['user_id'], $subscription['id'], $details]);
            
            error_log("Обновлено количество оставшихся тренировок для абонемента {$subscription['id']}: $remaining");
            
            // Если тренировок не осталось, отправляем уведомление пользователю
            if ($remaining === 0) {
                error_log("У пользователя $user_name закончились тренировки в абонементе");
                
                // Добавляем уведомление для пользователя
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO notifications (user_id, type, title, message, is_read) 
                        VALUES (?, 'subscription', 'Закончились тренировки', 'В вашем абонементе закончились тренировки. Пожалуйста, продлите абонемент для продолжения занятий.', 0)
                    ");
                    $stmt->execute([$user_id]);
                    error_log("Уведомление о закончившихся тренировках отправлено пользователю $user_id");
                } catch (PDOException $e) {
                    error_log("Ошибка при отправке уведомления: " . $e->getMessage());
                }
            }
        } else {
            error_log("Абонемент {$subscription['id']} имеет безлимитное количество тренировок");
        }
        error_log("===== КОНЕЦ ОБНОВЛЕНИЯ ТРЕНИРОВОК =====");
    } catch (PDOException $e) {
        error_log("ОШИБКА при обновлении количества оставшихся тренировок: " . $e->getMessage());
        error_log("Трассировка стека: " . $e->getTraceAsString());
    }
}

// Пагинация
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Поиск и фильтрация
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Формируем SQL запрос с учетом фильтров
$params = [];
$where_conditions = [];

// If user is trainer, restrict to only their sessions
if ($_SESSION['user_role'] === 'trainer') {
    // Get trainer ID for current user
    $trainerStmt = $pdo->prepare("SELECT id FROM trainers WHERE user_id = ?");
    $trainerStmt->execute([$_SESSION['user_id']]);
    $trainerId = $trainerStmt->fetchColumn();
    
    // Отладочная информация
    error_log("Trainer filter: user_id=" . $_SESSION['user_id'] . ", trainer_id=" . ($trainerId ? $trainerId : 'not_found'));
    
    if ($trainerId) {
        $where_conditions[] = "ts.trainer_id = ?";
        $params[] = $trainerId;
    } else {
        // If we can't find trainer ID for this user, show no results
        $where_conditions[] = "1 = 0";
        error_log("No trainer record found for user_id=" . $_SESSION['user_id']);
    }
}

if (!empty($search)) {
    $where_conditions[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR CONCAT(u.first_name, ' ', u.last_name) LIKE ? OR t.first_name LIKE ? OR t.last_name LIKE ? OR CONCAT(t.first_name, ' ', t.last_name) LIKE ? OR s.name LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if (!empty($status_filter)) {
    $where_conditions[] = "ts.status = ?";
    $params[] = $status_filter;
}

if (!empty($date_from)) {
    $where_conditions[] = "ts.session_date >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $where_conditions[] = "ts.session_date <= ?";
    $params[] = $date_to;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Запрос для получения общего количества записей
$count_query = "SELECT COUNT(*) as total 
                FROM training_sessions ts 
                JOIN users u ON ts.user_id = u.id 
                JOIN trainers tr ON ts.trainer_id = tr.id 
                JOIN users t ON tr.user_id = t.id 
                JOIN services s ON ts.service_id = s.id 
                $where_clause";

// Отладочная информация о SQL-запросе и параметрах
error_log("Count query: " . $count_query);
error_log("Params: " . print_r($params, true));

$count_stmt = $pdo->prepare($count_query);
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $records_per_page);

// Запрос для получения данных записей с пагинацией
$query = "SELECT 
            ts.id, 
            CONCAT(u.first_name, ' ', u.last_name) AS user_name,
            CONCAT(t.first_name, ' ', t.last_name) AS trainer_name,
            s.name AS service_name, 
            ts.session_date, 
            ts.start_time,
            ts.end_time,
            ts.status,
            ts.created_at
          FROM training_sessions ts 
          JOIN users u ON ts.user_id = u.id 
          JOIN trainers tr ON ts.trainer_id = tr.id 
          JOIN users t ON tr.user_id = t.id 
          JOIN services s ON ts.service_id = s.id 
          $where_clause
          ORDER BY ts.session_date DESC, ts.start_time ASC
          LIMIT ?, ?";

// Отладочная информация о SQL-запросе
error_log("Query: " . $query);
error_log("All params: " . print_r(array_merge($params, [$offset, $records_per_page]), true));

$stmt = $pdo->prepare($query);
$all_params = array_merge($params, [$offset, $records_per_page]);
$stmt->execute($all_params);
$result = $stmt->fetchAll();

// Отладочная информация о результате запроса
error_log("Result count: " . count($result));

// Данные для выпадающих списков фильтрации
$statuses = ['pending', 'confirmed', 'cancelled', 'completed'];

// Заголовок страницы
$page_title = "Управление записями на тренировки";
include 'includes/header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?php echo $page_title; ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Панель управления</a></li>
        <li class="breadcrumb-item active">Записи на тренировки</li>
    </ol>
    
    <!-- Фильтры -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter me-1"></i>
            Фильтры
        </div>
        <div class="card-body">
            <form action="" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Поиск</label>
                    <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Имя клиента, тренера или услуга">
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Статус</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Все статусы</option>
                        <?php foreach ($statuses as $status): ?>
                            <option value="<?php echo $status; ?>" <?php if ($status_filter === $status) echo 'selected'; ?>>
                                <?php 
                                switch ($status) {
                                    case 'pending': echo 'Ожидает'; break;
                                    case 'confirmed': echo 'Подтверждено'; break;
                                    case 'cancelled': echo 'Отменено'; break;
                                    case 'completed': echo 'Завершено'; break;
                                }
                                ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="date_from" class="form-label">Дата с</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label">Дата по</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Применить</button>
                    <a href="training_sessions.php" class="btn btn-secondary">Сбросить</a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Инструменты -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-tools me-1"></i>
            Инструменты
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <button id="export-csv" class="btn btn-success me-2">
                        <i class="fas fa-file-csv me-1"></i> Экспорт в CSV
                    </button>
                    <button id="print-list" class="btn btn-info">
                        <i class="fas fa-print me-1"></i> Печать списка
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Таблица записей -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-calendar-check me-1"></i>
            Список записей на тренировки
        </div>
        <div class="card-body">
            <?php if (count($result) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="sessions-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Клиент</th>
                                <th>Тренер</th>
                                <th>Услуга</th>
                                <th>Дата</th>
                                <th>Время</th>
                                <th>Статус</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result as $row): ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['trainer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['service_name']); ?></td>
                                    <td><?php echo date('d.m.Y', strtotime($row['session_date'])); ?></td>
                                    <td><?php echo substr($row['start_time'], 0, 5) . ' - ' . substr($row['end_time'], 0, 5); ?></td>
                                    <td>
                                        <select class="form-select form-select-sm status-select" data-session-id="<?php echo $row['id']; ?>">
                                            <?php foreach ($statuses as $status): ?>
                                                <option value="<?php echo $status; ?>" <?php if ($row['status'] === $status) echo 'selected'; ?>>
                                                    <?php 
                                                    switch ($status) {
                                                        case 'pending': echo 'Ожидает'; break;
                                                        case 'confirmed': echo 'Подтверждено'; break;
                                                        case 'cancelled': echo 'Отменено'; break;
                                                        case 'completed': echo 'Завершено'; break;
                                                    }
                                                    ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info view-session" data-bs-toggle="modal" data-bs-target="#viewSessionModal" data-session-id="<?php echo $row['id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if ($_SESSION['user_role'] !== 'trainer'): ?>
                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteSessionModal" data-session-id="<?php echo $row['id']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Пагинация -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Навигация по страницам">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                                <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>">Предыдущая</a>
                            </li>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php if ($page == $i) echo 'active'; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?php if ($page >= $total_pages) echo 'disabled'; ?>">
                                <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>">Следующая</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="alert alert-info">
                    Записи на тренировки не найдены
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Модальное окно для просмотра деталей записи -->
<div class="modal fade" id="viewSessionModal" tabindex="-1" aria-labelledby="viewSessionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewSessionModalLabel">Детали записи</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3 row">
                    <label class="col-4 fw-bold">ID:</label>
                    <div class="col-8" id="view-id"></div>
                </div>
                <div class="mb-3 row">
                    <label class="col-4 fw-bold">Клиент:</label>
                    <div class="col-8" id="view-user"></div>
                </div>
                <div class="mb-3 row">
                    <label class="col-4 fw-bold">Тренер:</label>
                    <div class="col-8" id="view-trainer"></div>
                </div>
                <div class="mb-3 row">
                    <label class="col-4 fw-bold">Услуга:</label>
                    <div class="col-8" id="view-service"></div>
                </div>
                <div class="mb-3 row">
                    <label class="col-4 fw-bold">Дата:</label>
                    <div class="col-8" id="view-date"></div>
                </div>
                <div class="mb-3 row">
                    <label class="col-4 fw-bold">Время:</label>
                    <div class="col-8" id="view-time"></div>
                </div>
                <div class="mb-3 row">
                    <label class="col-4 fw-bold">Статус:</label>
                    <div class="col-8">
                        <span id="view-status" class="badge bg-secondary"></span>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-4 fw-bold">Примечания:</label>
                    <div class="col-8" id="view-notes"></div>
                </div>
                <hr>
                <div class="mb-3 row">
                    <label class="col-4 fw-bold">Создано:</label>
                    <div class="col-8" id="view-created"></div>
                </div>
                <div class="mb-3 row">
                    <label class="col-4 fw-bold">Обновлено:</label>
                    <div class="col-8" id="view-updated"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для подтверждения удаления -->
<div class="modal fade" id="deleteSessionModal" tabindex="-1" aria-labelledby="deleteSessionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSessionModalLabel">Подтверждение удаления</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите удалить эту запись на тренировку?</p>
                <p class="text-danger">Это действие нельзя отменить.</p>
            </div>
            <div class="modal-footer">
                <form action="" method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" id="delete-session-id" name="session_id" value="">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-danger">Удалить</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Подключение JavaScript файла -->
<script src="js/training_sessions.js"></script>

<?php
// PDO не требует явного закрытия соединения
// $stmt, $count_stmt и $pdo будут автоматически очищены в конце скрипта
include 'includes/footer.php';
?> 