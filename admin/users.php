<?php
session_start();
require_once('../database/config.php');

// Проверка доступа (только для администраторов и менеджеров)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'manager'])) {
    header('Location: ../login.php');
    exit();
}

// Проверка прав на управление пользователями
if ($_SESSION['user_role'] === 'manager') {
    $stmt = $pdo->prepare("SELECT p.id FROM permissions p 
                         JOIN role_permissions rp ON p.id = rp.permission_id 
                         WHERE rp.role = ? AND p.name = 'manage_users'");
    $stmt->execute([$_SESSION['user_role']]);
    if (!$stmt->fetch()) {
        header('Location: index.php');
        exit();
    }
}

// Обработка действий
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'add':
            // Проверка обязательных полей
            if (empty($_POST['email']) || empty($_POST['password']) || empty($_POST['first_name']) || empty($_POST['last_name'])) {
                header('Location: users.php?error=add');
                exit();
            }
            
            // Проверка, существует ли пользователь с таким email
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $checkStmt->execute([$_POST['email']]);
            if ($checkStmt->fetch()) {
                header('Location: users.php?error=add&message=email_exists');
                exit();
            }
            
            // Хеширование пароля
            $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
            
            // Подготовка данных
            $role = isset($_POST['user_role']) ? $_POST['user_role'] : 'user';
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            $phone = isset($_POST['phone']) ? $_POST['phone'] : null;
            
            // Добавление пользователя
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO users (email, password, first_name, last_name, phone, role, is_active, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([
                    $_POST['email'],
                    $hashedPassword,
                    $_POST['first_name'],
                    $_POST['last_name'],
                    $phone,
                    $role,
                    $isActive
                ]);
                
                header('Location: users.php?success=add');
                exit();
            } catch (PDOException $e) {
                // Логирование ошибки
                error_log("Database error: " . $e->getMessage());
                header('Location: users.php?error=add');
                exit();
            }
            break;
            
        case 'edit':
            // Проверка обязательных полей
            if (empty($_POST['user_id']) || empty($_POST['email']) || empty($_POST['first_name']) || empty($_POST['last_name'])) {
                header('Location: users.php?error=edit');
                exit();
            }
            
            // Проверка, существует ли пользователь с таким email (кроме текущего)
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $checkStmt->execute([$_POST['email'], $_POST['user_id']]);
            if ($checkStmt->fetch()) {
                header('Location: users.php?error=edit&message=email_exists');
                exit();
            }
            
            // Подготовка данных
            $userId = (int)$_POST['user_id'];
            $email = $_POST['email'];
            $firstName = $_POST['first_name'];
            $lastName = $_POST['last_name'];
            $phone = isset($_POST['phone']) ? $_POST['phone'] : null;
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            
            // Массив для параметров SQL запроса
            $params = [$email, $firstName, $lastName, $phone, $isActive];
            
            // SQL запрос
            $sql = "UPDATE users SET email = ?, first_name = ?, last_name = ?, phone = ?, is_active = ?";
            
            // Добавление пароля, если он указан
            if (!empty($_POST['password'])) {
                $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $sql .= ", password = ?";
                $params[] = $hashedPassword;
            }
            
            // Добавление роли, если пользователь имеет права на её изменение
            if ($_SESSION['user_role'] === 'admin' && isset($_POST['user_role'])) {
                $sql .= ", role = ?";
                $params[] = $_POST['user_role'];
            }
            
            // Завершение SQL запроса
            $sql .= " WHERE id = ?";
            $params[] = $userId;
            
            // Обновление пользователя
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                
                header('Location: users.php?success=edit');
                exit();
            } catch (PDOException $e) {
                // Логирование ошибки
                error_log("Database error: " . $e->getMessage());
                header('Location: users.php?error=edit');
                exit();
            }
            break;
            
        case 'delete':
            // Проверка наличия ID
            if (empty($_POST['user_id'])) {
                header('Location: users.php?error=delete');
                exit();
            }
            
            // Проверка прав
            if ($_SESSION['user_role'] !== 'admin') {
                header('Location: users.php?error=access');
                exit();
            }
            
            $userId = (int)$_POST['user_id'];
            
            // Запрет на удаление самого себя
            if ($userId === (int)$_SESSION['user_id']) {
                header('Location: users.php?error=delete&message=self_delete');
                exit();
            }
            
            // Удаление пользователя
            try {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                
                header('Location: users.php?success=delete');
                exit();
            } catch (PDOException $e) {
                // Логирование ошибки
                error_log("Database error: " . $e->getMessage());
                header('Location: users.php?error=delete');
                exit();
            }
            break;
            
        case 'change_status':
            // Обработка AJAX запроса на изменение статуса
            header('Content-Type: application/json');
            
            if (empty($_POST['user_id']) || !isset($_POST['is_active'])) {
                exit(json_encode(['success' => false, 'message' => 'Неверные параметры']));
            }
            
            $userId = (int)$_POST['user_id'];
            $isActive = (int)$_POST['is_active'];
            
            // Запрет на деактивацию самого себя
            if ($userId === (int)$_SESSION['user_id'] && $isActive === 0) {
                exit(json_encode(['success' => false, 'message' => 'Нельзя деактивировать свою учетную запись']));
            }
            
            try {
                $stmt = $pdo->prepare("UPDATE users SET is_active = ? WHERE id = ?");
                $stmt->execute([$isActive, $userId]);
                
                exit(json_encode(['success' => true, 'message' => 'Статус успешно изменен']));
            } catch (PDOException $e) {
                error_log("Database error: " . $e->getMessage());
                exit(json_encode(['success' => false, 'message' => 'Ошибка при изменении статуса']));
            }
            break;
            
        case 'change_role':
            // Обработка AJAX запроса на изменение роли
            header('Content-Type: application/json');
            
            // Проверка прав
            if ($_SESSION['user_role'] !== 'admin') {
                exit(json_encode(['success' => false, 'message' => 'Недостаточно прав']));
            }
            
            if (empty($_POST['user_id']) || empty($_POST['user_role'])) {
                exit(json_encode(['success' => false, 'message' => 'Неверные параметры']));
            }
            
            $userId = (int)$_POST['user_id'];
            $role = $_POST['user_role'];
            
            // Проверка допустимости роли
            if (!in_array($role, ['admin', 'manager', 'user'])) {
                exit(json_encode(['success' => false, 'message' => 'Недопустимая роль']));
            }
            
            // Запрет на понижение своей роли
            if ($userId === (int)$_SESSION['user_id'] && $role !== 'admin') {
                exit(json_encode(['success' => false, 'message' => 'Вы не можете понизить свою роль']));
            }
            
            try {
                $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
                $stmt->execute([$role, $userId]);
                
                exit(json_encode(['success' => true, 'message' => 'Роль успешно изменена']));
            } catch (PDOException $e) {
                error_log("Database error: " . $e->getMessage());
                exit(json_encode(['success' => false, 'message' => 'Ошибка при изменении роли']));
            }
            break;
    }
}

// Настройки пагинации
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Получение параметров фильтрации
$roleFilter = isset($_GET['user_role']) ? $_GET['user_role'] : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$searchFilter = isset($_GET['search']) ? $_GET['search'] : '';

// Построение запроса с учетом фильтров
$whereConditions = [];
$params = [];

if (!empty($roleFilter)) {
    $whereConditions[] = "role = :role";
    $params[':role'] = $roleFilter;
}

if ($statusFilter !== '') {
    $whereConditions[] = "is_active = :status";
    $params[':status'] = (int)$statusFilter;
}

if (!empty($searchFilter)) {
    $whereConditions[] = "(email LIKE :search OR first_name LIKE :search OR last_name LIKE :search OR phone LIKE :search)";
    $params[':search'] = "%{$searchFilter}%";
}

// Формирование условия WHERE
$whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

// Получение общего количества пользователей с учетом фильтров
$countSql = "SELECT COUNT(*) FROM users {$whereClause}";
$countStmt = $pdo->prepare($countSql);
foreach ($params as $key => $value) {
    $countStmt->bindValue($key, $value);
}
$countStmt->execute();
$totalUsers = $countStmt->fetchColumn();
$totalPages = ceil($totalUsers / $perPage);

// Получение списка пользователей с пагинацией и фильтрацией
$sql = "SELECT id, email, first_name, last_name, phone, role, is_active, created_at, last_login 
        FROM users 
        {$whereClause}
        ORDER BY id DESC 
        LIMIT :offset, :perPage";

$usersStmt = $pdo->prepare($sql);
$usersStmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$usersStmt->bindParam(':perPage', $perPage, PDO::PARAM_INT);

foreach ($params as $key => $value) {
    $usersStmt->bindValue($key, $value);
}

$usersStmt->execute();
$users = $usersStmt->fetchAll();

$pageTitle = 'Пользователи';
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Управление пользователями</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="export_users.php?role=<?= urlencode($roleFilter) ?>&status=<?= urlencode($statusFilter) ?>&search=<?= urlencode($searchFilter) ?>" class="btn btn-sm btn-outline-success me-2">
                        <i class="fas fa-file-csv me-1"></i> Экспорт в CSV
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="fas fa-plus me-1"></i> Добавить пользователя
                    </button>
                </div>
            </div>

            <?php
            // Получение статистики по пользователям
            $statsStmt = $pdo->query("
                SELECT 
                    COUNT(*) AS total_users,
                    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) AS active_users,
                    SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) AS admins,
                    SUM(CASE WHEN role = 'manager' THEN 1 ELSE 0 END) AS managers,
                    SUM(CASE WHEN role = 'user' THEN 1 ELSE 0 END) AS normal_users
                FROM users
            ");
            $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
            ?>

            <!-- Статистика пользователей -->
            <div class="row mb-4">
                <div class="col-md">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">Всего пользователей</h6>
                                    <div class="display-4"><?= $stats['total_users'] ?></div>
                                </div>
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">Активные пользователи</h6>
                                    <div class="display-4"><?= $stats['active_users'] ?></div>
                                </div>
                                <i class="fas fa-user-check fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md">
                    <div class="card text-white bg-danger">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">Администраторы</h6>
                                    <div class="display-4"><?= $stats['admins'] ?></div>
                                </div>
                                <i class="fas fa-user-shield fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">Менеджеры</h6>
                                    <div class="display-4"><?= $stats['managers'] ?></div>
                                </div>
                                <i class="fas fa-user-tie fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                    switch ($_GET['success']) {
                        case 'add':
                            echo 'Пользователь успешно добавлен.';
                            break;
                        case 'edit':
                            echo 'Пользователь успешно обновлен.';
                            break;
                        case 'delete':
                            echo 'Пользователь успешно удален.';
                            break;
                        case 'status':
                            echo 'Статус пользователя изменен.';
                            break;
                        case 'role':
                            echo 'Роль пользователя изменена.';
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
                            echo isset($_GET['message']) && $_GET['message'] === 'email_exists' ? 
                                'Пользователь с таким email уже существует.' : 
                                'Ошибка при добавлении пользователя.';
                            break;
                        case 'edit':
                            echo isset($_GET['message']) && $_GET['message'] === 'email_exists' ? 
                                'Пользователь с таким email уже существует.' : 
                                'Ошибка при обновлении пользователя.';
                            break;
                        case 'delete':
                            echo isset($_GET['message']) && $_GET['message'] === 'self_delete' ? 
                                'Вы не можете удалить свою учетную запись.' : 
                                'Ошибка при удалении пользователя.';
                            break;
                        case 'access':
                            echo 'Недостаточно прав для выполнения действия.';
                            break;
                    }
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Закрыть"></button>
                </div>
            <?php endif; ?>

            <!-- Фильтр пользователей -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-filter me-1"></i>
                    Фильтр пользователей
                </div>
                <div class="card-body">
                    <form action="" method="get" class="row g-3">
                        <div class="col-md-3">
                            <label for="filter-role" class="form-label">Роль</label>
                            <select id="filter-role" name="role" class="form-select">
                                <option value="">Все</option>
                                <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>Администратор</option>
                                <option value="manager" <?= $roleFilter === 'manager' ? 'selected' : '' ?>>Менеджер</option>
                                <option value="user" <?= $roleFilter === 'user' ? 'selected' : '' ?>>Пользователь</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filter-status" class="form-label">Статус</label>
                            <select id="filter-status" name="status" class="form-select">
                                <option value="">Все</option>
                                <option value="1" <?= $statusFilter === '1' ? 'selected' : '' ?>>Активный</option>
                                <option value="0" <?= $statusFilter === '0' ? 'selected' : '' ?>>Неактивный</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="filter-search" class="form-label">Поиск</label>
                            <input type="text" class="form-control" id="filter-search" name="search" placeholder="Имя, Email или Телефон" value="<?= htmlspecialchars($searchFilter) ?>">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <div class="btn-group w-100">
                                <button type="submit" class="btn btn-primary">Применить</button>
                                <a href="users.php" class="btn btn-secondary">Сбросить</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Таблица пользователей -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Email</th>
                            <th>Имя</th>
                            <th>Фамилия</th>
                            <th>Роль</th>
                            <th>Статус</th>
                            <th>Дата регистрации</th>
                            <th>Последний вход</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['id']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['first_name']) ?></td>
                            <td><?= htmlspecialchars($user['last_name']) ?></td>
                            <td>
                                <?php if ($_SESSION['user_role'] === 'admin' && $user['id'] != $_SESSION['user_id']): ?>
                                <select class="form-select form-select-sm role-select" data-user-id="<?= $user['id'] ?>">
                                    <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>Пользователь</option>
                                    <option value="manager" <?= $user['role'] === 'manager' ? 'selected' : '' ?>>Менеджер</option>
                                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Администратор</option>
                                </select>
                                <?php else: ?>
                                <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'manager' ? 'warning' : 'primary') ?>">
                                    <?= ucfirst(htmlspecialchars($user['role'])) ?>
                                </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input status-toggle" type="checkbox" 
                                        data-user-id="<?= $user['id'] ?>" 
                                        <?= $user['is_active'] ? 'checked' : '' ?>
                                        <?= $user['id'] == $_SESSION['user_id'] ? 'disabled' : '' ?>>
                                </div>
                            </td>
                            <td><?= date('d.m.Y', strtotime($user['created_at'])) ?></td>
                            <td><?= $user['last_login'] ? date('d.m.Y H:i', strtotime($user['last_login'])) : 'Никогда' ?></td>
                            <td class="action-buttons">
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editUserModal" data-user-id="<?= $user['id'] ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php if ($_SESSION['user_role'] === 'admin' && $user['id'] != $_SESSION['user_id']): ?>
                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteUserModal" data-user-id="<?= $user['id'] ?>">
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
            <?php if ($totalPages > 1): ?>
            <nav aria-label="Навигация по страницам">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page - 1 ?>&role=<?= urlencode($roleFilter) ?>&status=<?= urlencode($statusFilter) ?>&search=<?= urlencode($searchFilter) ?>" aria-label="Предыдущая">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&role=<?= urlencode($roleFilter) ?>&status=<?= urlencode($statusFilter) ?>&search=<?= urlencode($searchFilter) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&role=<?= urlencode($roleFilter) ?>&status=<?= urlencode($statusFilter) ?>&search=<?= urlencode($searchFilter) ?>" aria-label="Следующая">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </main>
    </div>
</div>

<!-- Модальное окно добавления пользователя -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Добавить пользователя</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <form id="addUserForm" action="users.php" method="post">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label for="add-email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="add-email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add-password" class="form-label">Пароль <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="add-password" name="password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add-first-name" class="form-label">Имя <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="add-first-name" name="first_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add-last-name" class="form-label">Фамилия <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="add-last-name" name="last_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add-phone" class="form-label">Телефон</label>
                        <input type="tel" class="form-control" id="add-phone" name="phone">
                    </div>
                    
                    <div class="mb-3">
                        <label for="add-role" class="form-label">Роль <span class="text-danger">*</span></label>
                        <select class="form-select" id="add-role" name="role" required>
                            <option value="user">Пользователь</option>
                            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                            <option value="manager">Менеджер</option>
                            <option value="admin">Администратор</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="add-is-active" name="is_active" value="1" checked>
                        <label class="form-check-label" for="add-is-active">Активный</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="submit" form="addUserForm" class="btn btn-primary">Добавить</button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно редактирования пользователя -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Редактировать пользователя</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm" action="users.php" method="post">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="user_id" id="edit-user-id">
                    
                    <div class="mb-3">
                        <label for="edit-email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="edit-email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit-password" class="form-label">Новый пароль</label>
                        <input type="password" class="form-control" id="edit-password" name="password">
                        <small class="form-text text-muted">Оставьте пустым, чтобы не менять пароль</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit-first-name" class="form-label">Имя <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit-first-name" name="first_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit-last-name" class="form-label">Фамилия <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit-last-name" name="last_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit-phone" class="form-label">Телефон</label>
                        <input type="tel" class="form-control" id="edit-phone" name="phone">
                    </div>
                    
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <div class="mb-3">
                        <label for="edit-role" class="form-label">Роль <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit-role" name="role" required>
                            <option value="user">Пользователь</option>
                            <option value="manager">Менеджер</option>
                            <option value="admin">Администратор</option>
                        </select>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="edit-is-active" name="is_active" value="1">
                        <label class="form-check-label" for="edit-is-active">Активный</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="submit" form="editUserForm" class="btn btn-primary">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно удаления пользователя -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteUserModalLabel">Удалить пользователя</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите удалить этого пользователя? Это действие нельзя отменить.</p>
                <form id="deleteUserForm" action="users.php" method="post">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="user_id" id="delete-user-id">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="submit" form="deleteUserForm" class="btn btn-danger">Удалить</button>
            </div>
        </div>
    </div>
</div>

<script src="js/users.js"></script>

<?php include 'includes/footer.php'; ?>