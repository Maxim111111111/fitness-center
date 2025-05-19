<?php
// Страница управления записями на тренировки
session_start();

// Проверка авторизации
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'manager'])) {
    header("Location: login.php");
    exit;
}

// Подключение к базе данных
require_once '../database/config.php';

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
                $stmt = $pdo->prepare("UPDATE training_sessions SET status = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$status, $session_id]);
                
                if ($stmt->rowCount() > 0) {
                    $response = ['success' => true, 'message' => 'Статус успешно обновлен'];
                } else {
                    $response = ['success' => false, 'message' => 'Ошибка при обновлении статуса'];
                }
            } catch (PDOException $e) {
                $response = ['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()];
            }
        }
    }
    
    // Удаление записи
    if ($_POST['action'] === 'delete' && isset($_POST['session_id'])) {
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

$stmt = $pdo->prepare($query);
$all_params = array_merge($params, [$offset, $records_per_page]);
$stmt->execute($all_params);
$result = $stmt->fetchAll();

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
                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteSessionModal" data-session-id="<?php echo $row['id']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
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