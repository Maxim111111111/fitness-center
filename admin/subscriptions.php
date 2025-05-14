<?php
session_start();
require_once('../database/config.php');

// Проверка доступа (только для администраторов и менеджеров)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'manager'])) {
    header('Location: ../login.php');
    exit();
}

// Обработка действий
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'add':
            // Код для добавления абонемента
            if (empty($_POST['name']) || !isset($_POST['price']) || empty($_POST['duration'])) {
                header('Location: subscriptions.php?error=add');
                exit();
            }
            
            // Подготовка данных
            $name = $_POST['name'];
            $description = $_POST['description'] ?? '';
            $price = (float)$_POST['price'];
            $duration = (int)$_POST['duration'];
            $duration_type = $_POST['duration_type'] ?? 'days';
            $visit_limit = !empty($_POST['visit_limit']) ? (int)$_POST['visit_limit'] : null;
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            try {
                // Проверим структуру таблицы, чтобы определить, какие поля использовать
                $tableStructure = $pdo->query("DESCRIBE subscriptions");
                $columns = $tableStructure->fetchAll(PDO::FETCH_COLUMN);
                
                if (in_array('duration', $columns) && in_array('duration_type', $columns)) {
                    // Новая структура с duration и duration_type
                    $stmt = $pdo->prepare("
                        INSERT INTO subscriptions (name, description, price, duration, duration_type, visit_limit, is_active, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                    ");
                    $stmt->execute([
                        $name,
                        $description,
                        $price,
                        $duration,
                        $duration_type,
                        $visit_limit,
                        $is_active
                    ]);
                } else {
                    // Старая структура с duration_days и sessions_count
                    $stmt = $pdo->prepare("
                        INSERT INTO subscriptions (name, description, price, duration_days, sessions_count, is_active) 
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $name,
                        $description,
                        $price,
                        $duration,
                        $visit_limit,
                        $is_active
                    ]);
                }
                
                header('Location: subscriptions.php?success=add');
                exit();
            } catch (PDOException $e) {
                error_log("Database error: " . $e->getMessage());
                header('Location: subscriptions.php?error=add');
                exit();
            }
            break;
            
        case 'edit':
            // Код для редактирования абонемента
            if (empty($_POST['subscription_id']) || empty($_POST['name']) || !isset($_POST['price']) || empty($_POST['duration'])) {
                header('Location: subscriptions.php?error=edit');
                exit();
            }
            
            // Подготовка данных
            $id = (int)$_POST['subscription_id'];
            $name = $_POST['name'];
            $description = $_POST['description'] ?? '';
            $price = (float)$_POST['price'];
            $duration = (int)$_POST['duration'];
            $duration_type = $_POST['duration_type'] ?? 'days';
            $visit_limit = !empty($_POST['visit_limit']) ? (int)$_POST['visit_limit'] : null;
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            try {
                // Проверим структуру таблицы, чтобы определить, какие поля использовать
                $tableStructure = $pdo->query("DESCRIBE subscriptions");
                $columns = $tableStructure->fetchAll(PDO::FETCH_COLUMN);
                
                if (in_array('duration', $columns) && in_array('duration_type', $columns)) {
                    // Новая структура с duration и duration_type
                    $stmt = $pdo->prepare("
                        UPDATE subscriptions 
                        SET name = ?, description = ?, price = ?, duration = ?, 
                            duration_type = ?, visit_limit = ?, is_active = ? 
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $name,
                        $description,
                        $price,
                        $duration,
                        $duration_type,
                        $visit_limit,
                        $is_active,
                        $id
                    ]);
                } else {
                    // Старая структура с duration_days и sessions_count
                    $stmt = $pdo->prepare("
                        UPDATE subscriptions 
                        SET name = ?, description = ?, price = ?, duration_days = ?, 
                            sessions_count = ?, is_active = ? 
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $name,
                        $description,
                        $price,
                        $duration,
                        $visit_limit,
                        $is_active,
                        $id
                    ]);
                }
                
                header('Location: subscriptions.php?success=edit');
                exit();
            } catch (PDOException $e) {
                error_log("Database error: " . $e->getMessage());
                header('Location: subscriptions.php?error=edit');
                exit();
            }
            break;
            
        case 'delete':
            // Код для удаления абонемента
            if (empty($_POST['subscription_id'])) {
                header('Location: subscriptions.php?error=delete');
                exit();
            }
            
            $id = (int)$_POST['subscription_id'];
            
            try {
                // Проверяем, есть ли активные абонементы у пользователей
                $checkStmt = $pdo->prepare("
                    SELECT COUNT(*) FROM user_subscriptions 
                    WHERE subscription_id = ? AND (expires_at > NOW() OR visits_left > 0)
                ");
                $checkStmt->execute([$id]);
                if ($checkStmt->fetchColumn() > 0) {
                    header('Location: subscriptions.php?error=delete&message=active_subscriptions');
                    exit();
                }
                
                $stmt = $pdo->prepare("DELETE FROM subscriptions WHERE id = ?");
                $stmt->execute([$id]);
                
                header('Location: subscriptions.php?success=delete');
                exit();
            } catch (PDOException $e) {
                error_log("Database error: " . $e->getMessage());
                header('Location: subscriptions.php?error=delete');
                exit();
            }
            break;
            
        case 'change_status':
            // Обработка AJAX запроса на изменение статуса
            header('Content-Type: application/json');
            
            if (empty($_POST['subscription_id']) || !isset($_POST['is_active'])) {
                exit(json_encode(['success' => false, 'message' => 'Неверные параметры']));
            }
            
            $id = (int)$_POST['subscription_id'];
            $isActive = (int)$_POST['is_active'];
            
            try {
                $stmt = $pdo->prepare("UPDATE subscriptions SET is_active = ? WHERE id = ?");
                $stmt->execute([$isActive, $id]);
                
                exit(json_encode(['success' => true, 'message' => 'Статус успешно изменен']));
            } catch (PDOException $e) {
                error_log("Database error: " . $e->getMessage());
                exit(json_encode(['success' => false, 'message' => 'Ошибка при изменении статуса']));
            }
            break;
    }
}

// Настройки пагинации
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Получение фильтров
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$searchFilter = isset($_GET['search']) ? $_GET['search'] : '';

// Построение запроса с учетом фильтров
$whereConditions = [];
$params = [];

if ($statusFilter !== '') {
    $whereConditions[] = "is_active = :status";
    $params[':status'] = (int)$statusFilter;
}

if (!empty($searchFilter)) {
    $whereConditions[] = "(name LIKE :search OR description LIKE :search)";
    $params[':search'] = "%{$searchFilter}%";
}

// Формирование условия WHERE
$whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

// Получение общего количества абонементов с учетом фильтров
$countSql = "SELECT COUNT(*) FROM subscriptions {$whereClause}";
$countStmt = $pdo->prepare($countSql);
foreach ($params as $key => $value) {
    $countStmt->bindValue($key, $value);
}
$countStmt->execute();
$totalSubscriptions = $countStmt->fetchColumn();
$totalPages = ceil($totalSubscriptions / $perPage);

// Получение списка абонементов с пагинацией и фильтрацией
$sql = "SELECT * FROM subscriptions 
        {$whereClause}
        ORDER BY id DESC 
        LIMIT :offset, :perPage";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':perPage', $perPage, PDO::PARAM_INT);

foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

$stmt->execute();
$subscriptions = $stmt->fetchAll();

$pageTitle = 'Абонементы';
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Управление абонементами</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addSubscriptionModal">
                        <i class="fas fa-plus me-1"></i> Добавить абонемент
                    </button>
                </div>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                    switch ($_GET['success']) {
                        case 'add':
                            echo 'Абонемент успешно добавлен.';
                            break;
                        case 'edit':
                            echo 'Абонемент успешно обновлен.';
                            break;
                        case 'delete':
                            echo 'Абонемент успешно удален.';
                            break;
                        case 'status':
                            echo 'Статус абонемента изменен.';
                            break;
                    }
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Закрыть"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php 
                    switch ($_GET['error']) {
                        case 'add':
                            echo 'Ошибка при добавлении абонемента.';
                            break;
                        case 'edit':
                            echo 'Ошибка при обновлении абонемента.';
                            break;
                        case 'delete':
                            echo isset($_GET['message']) && $_GET['message'] === 'active_subscriptions' ? 
                                'Невозможно удалить абонемент, так как есть пользователи с активными абонементами этого типа.' : 
                                'Ошибка при удалении абонемента.';
                            break;
                    }
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Закрыть"></button>
                </div>
            <?php endif; ?>

            <!-- Фильтр абонементов -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-filter me-1"></i>
                    Фильтр абонементов
                </div>
                <div class="card-body">
                    <form action="" method="get" class="row g-3">
                        <div class="col-md-3">
                            <label for="filter-status" class="form-label">Статус</label>
                            <select id="filter-status" name="status" class="form-select">
                                <option value="">Все</option>
                                <option value="1" <?= $statusFilter === '1' ? 'selected' : '' ?>>Активный</option>
                                <option value="0" <?= $statusFilter === '0' ? 'selected' : '' ?>>Неактивный</option>
                            </select>
                        </div>
                        <div class="col-md-7">
                            <label for="filter-search" class="form-label">Поиск</label>
                            <input type="text" class="form-control" id="filter-search" name="search" placeholder="Название или описание" value="<?= htmlspecialchars($searchFilter) ?>">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <div class="btn-group w-100">
                                <button type="submit" class="btn btn-primary">Применить</button>
                                <a href="subscriptions.php" class="btn btn-secondary">Сбросить</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Таблица абонементов -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Название</th>
                            <th>Описание</th>
                            <th>Стоимость</th>
                            <th>Срок действия</th>
                            <th>Лимит посещений</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subscriptions as $subscription): ?>
                        <tr>
                            <td><?= htmlspecialchars($subscription['id']) ?></td>
                            <td><?= htmlspecialchars($subscription['name']) ?></td>
                            <td>
                                <?php 
                                // Ограничиваем описание до 50 символов
                                $description = $subscription['description'];
                                echo htmlspecialchars(strlen($description) > 50 ? substr($description, 0, 50) . '...' : $description);
                                ?>
                            </td>
                            <td><?= number_format($subscription['price'], 2, ',', ' ') ?> ₽</td>
                            <td>
                                <?php if (isset($subscription['duration'])): ?>
                                    <?= htmlspecialchars($subscription['duration']) ?> 
                                    <?php if (isset($subscription['duration_type'])): ?>
                                        <?= get_duration_name($subscription['duration_type'], $subscription['duration']) ?>
                                    <?php else: ?>
                                        <?= plural_form($subscription['duration'], 'день', 'дня', 'дней') ?>
                                    <?php endif; ?>
                                <?php elseif (isset($subscription['duration_days'])): ?>
                                    <?= htmlspecialchars($subscription['duration_days']) ?> 
                                    <?= plural_form($subscription['duration_days'], 'день', 'дня', 'дней') ?>
                                <?php else: ?>
                                    Не указан
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (isset($subscription['visit_limit'])): ?>
                                    <?= $subscription['visit_limit'] ? htmlspecialchars($subscription['visit_limit']) . ' посещений' : 'Без ограничений' ?>
                                <?php elseif (isset($subscription['sessions_count'])): ?>
                                    <?= $subscription['sessions_count'] ? htmlspecialchars($subscription['sessions_count']) . ' посещений' : 'Без ограничений' ?>
                                <?php else: ?>
                                    Не указан
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input status-toggle" type="checkbox" 
                                        data-subscription-id="<?= $subscription['id'] ?>" 
                                        <?= $subscription['is_active'] ? 'checked' : '' ?>>
                                </div>
                            </td>
                            <td class="action-buttons">
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editSubscriptionModal" data-subscription-id="<?= $subscription['id'] ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteSubscriptionModal" data-subscription-id="<?= $subscription['id'] ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($subscriptions)): ?>
                        <tr>
                            <td colspan="8" class="text-center">Нет доступных абонементов</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Пагинация -->
            <?php if ($totalPages > 1): ?>
            <nav aria-label="Навигация по страницам">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page - 1 ?>&status=<?= urlencode($statusFilter) ?>&search=<?= urlencode($searchFilter) ?>" aria-label="Предыдущая">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&status=<?= urlencode($statusFilter) ?>&search=<?= urlencode($searchFilter) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&status=<?= urlencode($statusFilter) ?>&search=<?= urlencode($searchFilter) ?>" aria-label="Следующая">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </main>
    </div>
</div>

<!-- Модальное окно добавления абонемента -->
<div class="modal fade" id="addSubscriptionModal" tabindex="-1" aria-labelledby="addSubscriptionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addSubscriptionModalLabel">Добавить абонемент</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <form id="addSubscriptionForm" action="subscriptions.php" method="post">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label for="add-name" class="form-label">Название <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="add-name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add-description" class="form-label">Описание</label>
                        <textarea class="form-control" id="add-description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add-price" class="form-label">Стоимость (₽) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="add-price" name="price" min="0" step="0.01" required>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="add-duration" class="form-label">Срок действия <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="add-duration" name="duration" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label for="add-duration-type" class="form-label">Тип срока <span class="text-danger">*</span></label>
                            <select class="form-select" id="add-duration-type" name="duration_type" required>
                                <option value="days">Дней</option>
                                <option value="months">Месяцев</option>
                                <option value="years">Лет</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add-visit-limit" class="form-label">Лимит посещений</label>
                        <input type="number" class="form-control" id="add-visit-limit" name="visit_limit" min="0">
                        <small class="form-text text-muted">Оставьте пустым для безлимитного абонемента</small>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="add-is-active" name="is_active" value="1" checked>
                        <label class="form-check-label" for="add-is-active">Активный</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="submit" form="addSubscriptionForm" class="btn btn-primary">Добавить</button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно редактирования абонемента -->
<div class="modal fade" id="editSubscriptionModal" tabindex="-1" aria-labelledby="editSubscriptionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSubscriptionModalLabel">Редактировать абонемент</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <form id="editSubscriptionForm" action="subscriptions.php" method="post">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="subscription_id" id="edit-subscription-id">
                    
                    <div class="mb-3">
                        <label for="edit-name" class="form-label">Название <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit-name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit-description" class="form-label">Описание</label>
                        <textarea class="form-control" id="edit-description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit-price" class="form-label">Стоимость (₽) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="edit-price" name="price" min="0" step="0.01" required>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit-duration" class="form-label">Срок действия <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="edit-duration" name="duration" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit-duration-type" class="form-label">Тип срока <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit-duration-type" name="duration_type" required>
                                <option value="days">Дней</option>
                                <option value="months">Месяцев</option>
                                <option value="years">Лет</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit-visit-limit" class="form-label">Лимит посещений</label>
                        <input type="number" class="form-control" id="edit-visit-limit" name="visit_limit" min="0">
                        <small class="form-text text-muted">Оставьте пустым для безлимитного абонемента</small>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="edit-is-active" name="is_active" value="1">
                        <label class="form-check-label" for="edit-is-active">Активный</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="submit" form="editSubscriptionForm" class="btn btn-primary">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно удаления абонемента -->
<div class="modal fade" id="deleteSubscriptionModal" tabindex="-1" aria-labelledby="deleteSubscriptionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSubscriptionModalLabel">Удалить абонемент</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите удалить этот абонемент? Это действие нельзя отменить.</p>
                <p class="text-danger">Удаление будет возможно только если нет пользователей с активными абонементами этого типа.</p>
                <form id="deleteSubscriptionForm" action="subscriptions.php" method="post">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="subscription_id" id="delete-subscription-id">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="submit" form="deleteSubscriptionForm" class="btn btn-danger">Удалить</button>
            </div>
        </div>
    </div>
</div>

<script src="js/subscriptions.js"></script>

<?php
// Вспомогательная функция для отображения срока действия
function get_duration_name($type, $value) {
    if ($type === null || $value === null) {
        return '';
    }
    
    switch ($type) {
        case 'days':
        case 'day':
            return plural_form($value, 'день', 'дня', 'дней');
        case 'months':
        case 'month':
            return plural_form($value, 'месяц', 'месяца', 'месяцев');
        case 'years':
        case 'year':
            return plural_form($value, 'год', 'года', 'лет');
        default:
            return '';
    }
}

// Функция для склонения существительных в зависимости от числа
function plural_form($n, $form1, $form2, $form5) {
    $n = abs($n) % 100;
    $n1 = $n % 10;
    
    if ($n > 10 && $n < 20) {
        return $form5;
    }
    
    if ($n1 > 1 && $n1 < 5) {
        return $form2;
    }
    
    if ($n1 == 1) {
        return $form1;
    }
    
    return $form5;
}

include 'includes/footer.php';
?> 