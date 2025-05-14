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
            // Код для добавления тренера
            break;
            
        case 'edit':
            // Код для редактирования тренера
            break;
            
        case 'delete':
            // Код для удаления тренера
            break;
            
        case 'change_status':
            // Код для изменения статуса тренера
            break;
    }
}

// Настройки пагинации
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Получение общего количества тренеров
$countStmt = $pdo->query("SELECT COUNT(*) FROM trainers");
$totalTrainers = $countStmt->fetchColumn();
$totalPages = ceil($totalTrainers / $perPage);

// Получение списка тренеров с данными пользователей
$trainersStmt = $pdo->prepare("
    SELECT t.*, u.first_name, u.last_name, u.email 
    FROM trainers t
    LEFT JOIN users u ON t.user_id = u.id
    ORDER BY t.id DESC
    LIMIT :offset, :perPage
");
$trainersStmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$trainersStmt->bindParam(':perPage', $perPage, PDO::PARAM_INT);
$trainersStmt->execute();
$trainers = $trainersStmt->fetchAll();

$pageTitle = 'Тренеры';
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Управление тренерами</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addTrainerModal">
                        <i class="fas fa-plus me-1"></i> Добавить тренера
                    </button>
                </div>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                    switch ($_GET['success']) {
                        case 'add':
                            echo 'Тренер успешно добавлен.';
                            break;
                        case 'edit':
                            echo 'Данные тренера успешно обновлены.';
                            break;
                        case 'delete':
                            echo 'Тренер успешно удален.';
                            break;
                        case 'status':
                            echo 'Статус тренера изменен.';
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
                            echo 'Ошибка при добавлении тренера.';
                            break;
                        case 'edit':
                            echo 'Ошибка при обновлении данных тренера.';
                            break;
                        case 'delete':
                            echo 'Ошибка при удалении тренера.';
                            break;
                    }
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Закрыть"></button>
                </div>
            <?php endif; ?>

            <!-- Фильтр тренеров -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-filter me-1"></i>
                    Фильтр тренеров
                </div>
                <div class="card-body">
                    <form action="" method="get" class="row g-3">
                        <div class="col-md-4">
                            <label for="filter-specialization" class="form-label">Специализация</label>
                            <select id="filter-specialization" name="specialization" class="form-select">
                                <option value="">Все</option>
                                <option value="Персональный тренинг">Персональный тренинг</option>
                                <option value="Групповые занятия">Групповые занятия</option>
                                <option value="Йога">Йога</option>
                                <option value="Функциональный тренинг">Функциональный тренинг</option>
                                <option value="Силовые тренировки">Силовые тренировки</option>
                                <option value="Бассейн">Бассейн</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filter-experience" class="form-label">Опыт работы</label>
                            <select id="filter-experience" name="experience" class="form-select">
                                <option value="">Все</option>
                                <option value="1">От 1 года</option>
                                <option value="3">От 3 лет</option>
                                <option value="5">От 5 лет</option>
                                <option value="10">От 10 лет</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filter-search" class="form-label">Поиск</label>
                            <input type="text" class="form-control" id="filter-search" name="search" placeholder="Имя, Email">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Применить</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Таблица тренеров -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Фото</th>
                            <th>Имя</th>
                            <th>Специализация</th>
                            <th>Опыт</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($trainers as $trainer): ?>
                        <tr>
                            <td><?= htmlspecialchars($trainer['id']) ?></td>
                            <td>
                                <?php if (!empty($trainer['photo_url'])): ?>
                                    <img src="<?= htmlspecialchars($trainer['photo_url']) ?>" alt="Фото тренера" class="img-thumbnail" width="50">
                                <?php else: ?>
                                    <img src="../assets/img/trainers/default.jpg" alt="Фото по умолчанию" class="img-thumbnail" width="50">
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($trainer['first_name'] . ' ' . $trainer['last_name']) ?>
                                <div class="small text-muted"><?= htmlspecialchars($trainer['email']) ?></div>
                            </td>
                            <td><?= htmlspecialchars($trainer['specialization']) ?></td>
                            <td><?= htmlspecialchars($trainer['experience_years']) ?> лет</td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input status-toggle" type="checkbox" 
                                        data-trainer-id="<?= $trainer['id'] ?>" 
                                        <?= $trainer['is_active'] ? 'checked' : '' ?>>
                                </div>
                            </td>
                            <td class="action-buttons">
                                <button type="button" class="btn btn-sm btn-info view-trainer" data-bs-toggle="modal" data-bs-target="#viewTrainerModal" data-trainer-id="<?= $trainer['id'] ?>">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-primary edit-trainer" data-bs-toggle="modal" data-bs-target="#editTrainerModal" data-trainer-id="<?= $trainer['id'] ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteTrainerModal" data-trainer-id="<?= $trainer['id'] ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
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
                        <a class="page-link" href="?page=<?= $page - 1 ?>" aria-label="Предыдущая">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>" aria-label="Следующая">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </main>
    </div>
</div>

<!-- Модальное окно добавления тренера -->
<div class="modal fade" id="addTrainerModal" tabindex="-1" aria-labelledby="addTrainerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTrainerModalLabel">Добавить тренера</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <form id="addTrainerForm" action="trainers.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="add-user-id" class="form-label">Пользователь <span class="text-danger">*</span></label>
                            <select class="form-select" id="add-user-id" name="user_id" required>
                                <option value="">Выберите пользователя</option>
                                <!-- Список пользователей будет загружен с помощью AJAX -->
                            </select>
                            <small class="form-text text-muted">Выберите пользователя, который будет связан с тренером</small>
                        </div>
                        <div class="col-md-6">
                            <label for="add-specialization" class="form-label">Специализация <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="add-specialization" name="specialization" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="add-experience" class="form-label">Опыт работы (лет) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="add-experience" name="experience_years" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label for="add-photo" class="form-label">Фото</label>
                            <input type="file" class="form-control" id="add-photo" name="photo" accept="image/*">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add-bio" class="form-label">Биография</label>
                        <textarea class="form-control" id="add-bio" name="bio" rows="4"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add-education" class="form-label">Образование</label>
                        <textarea class="form-control" id="add-education" name="education" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add-certificates" class="form-label">Сертификаты</label>
                        <textarea class="form-control" id="add-certificates" name="certificates" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add-achievements" class="form-label">Достижения</label>
                        <textarea class="form-control" id="add-achievements" name="achievements" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="add-is-active" name="is_active" value="1" checked>
                        <label class="form-check-label" for="add-is-active">Активный</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="submit" form="addTrainerForm" class="btn btn-primary">Добавить</button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно редактирования тренера -->
<div class="modal fade" id="editTrainerModal" tabindex="-1" aria-labelledby="editTrainerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTrainerModalLabel">Редактировать тренера</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <form id="editTrainerForm" action="trainers.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="trainer_id" id="edit-trainer-id">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit-user-id" class="form-label">Пользователь</label>
                            <select class="form-select" id="edit-user-id" name="user_id">
                                <option value="">Выберите пользователя</option>
                                <!-- Список пользователей будет загружен с помощью AJAX -->
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="edit-specialization" class="form-label">Специализация <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit-specialization" name="specialization" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit-experience" class="form-label">Опыт работы (лет) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="edit-experience" name="experience_years" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit-photo" class="form-label">Фото</label>
                            <input type="file" class="form-control" id="edit-photo" name="photo" accept="image/*">
                            <div class="form-check mt-2">
                                <input type="checkbox" class="form-check-input" id="edit-delete-photo" name="delete_photo" value="1">
                                <label class="form-check-label" for="edit-delete-photo">Удалить текущее фото</label>
                            </div>
                            <div id="current-photo-container" class="mt-2">
                                <!-- Текущее фото будет отображено здесь -->
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit-bio" class="form-label">Биография</label>
                        <textarea class="form-control" id="edit-bio" name="bio" rows="4"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit-education" class="form-label">Образование</label>
                        <textarea class="form-control" id="edit-education" name="education" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit-certificates" class="form-label">Сертификаты</label>
                        <textarea class="form-control" id="edit-certificates" name="certificates" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit-achievements" class="form-label">Достижения</label>
                        <textarea class="form-control" id="edit-achievements" name="achievements" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="edit-is-active" name="is_active" value="1">
                        <label class="form-check-label" for="edit-is-active">Активный</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="submit" form="editTrainerForm" class="btn btn-primary">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно просмотра тренера -->
<div class="modal fade" id="viewTrainerModal" tabindex="-1" aria-labelledby="viewTrainerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewTrainerModalLabel">Информация о тренере</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <div id="view-photo-container" class="mb-3">
                            <!-- Фото тренера будет отображено здесь -->
                        </div>
                    </div>
                    <div class="col-md-8">
                        <h4 id="view-name"></h4>
                        <p id="view-email" class="text-muted"></p>
                        
                        <div class="mb-3">
                            <strong>Специализация:</strong>
                            <span id="view-specialization"></span>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Опыт работы:</strong>
                            <span id="view-experience"></span> лет
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <h5>Биография</h5>
                    <div id="view-bio" class="border p-3 rounded bg-light"></div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <h5>Образование</h5>
                        <div id="view-education" class="border p-3 rounded bg-light"></div>
                    </div>
                    <div class="col-md-4">
                        <h5>Сертификаты</h5>
                        <div id="view-certificates" class="border p-3 rounded bg-light"></div>
                    </div>
                    <div class="col-md-4">
                        <h5>Достижения</h5>
                        <div id="view-achievements" class="border p-3 rounded bg-light"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <h5>Статус</h5>
                    <div id="view-status" class="badge"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary edit-from-view" data-bs-toggle="modal" data-bs-target="#editTrainerModal">Редактировать</button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно удаления тренера -->
<div class="modal fade" id="deleteTrainerModal" tabindex="-1" aria-labelledby="deleteTrainerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteTrainerModalLabel">Удалить тренера</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите удалить этого тренера? Это действие нельзя отменить.</p>
                <form id="deleteTrainerForm" action="trainers.php" method="post">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="trainer_id" id="delete-trainer-id">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="submit" form="deleteTrainerForm" class="btn btn-danger">Удалить</button>
            </div>
        </div>
    </div>
</div>

<script src="js/trainers.js"></script>

<?php include 'includes/footer.php'; ?> 