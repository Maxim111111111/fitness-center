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
        case 'approve':
            // Код для одобрения отзыва
            if (!isset($_POST['review_id']) || !is_numeric($_POST['review_id'])) {
                header('Location: reviews.php?error=approve');
                exit();
            }
            
            $review_id = (int)$_POST['review_id'];
            $moderator_id = $_SESSION['user_id'];
            
            try {
                $stmt = $pdo->prepare("
                    UPDATE reviews 
                    SET is_approved = 1, moderated_by = ?, moderated_at = NOW() 
                    WHERE id = ?
                ");
                $stmt->execute([$moderator_id, $review_id]);
                
                header('Location: reviews.php?success=approve');
                exit();
            } catch (PDOException $e) {
                error_log("Database error: " . $e->getMessage());
                header('Location: reviews.php?error=approve');
                exit();
            }
            break;
            
        case 'delete':
            // Код для удаления отзыва
            if (!isset($_POST['review_id']) || !is_numeric($_POST['review_id'])) {
                header('Location: reviews.php?error=delete');
                exit();
            }
            
            $review_id = (int)$_POST['review_id'];
            
            try {
                $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
                $stmt->execute([$review_id]);
                
                header('Location: reviews.php?success=delete');
                exit();
            } catch (PDOException $e) {
                error_log("Database error: " . $e->getMessage());
                header('Location: reviews.php?error=delete');
                exit();
            }
            break;
    }
}

// Параметры фильтрации
$status = isset($_GET['status']) ? $_GET['status'] : '';
$rating = isset($_GET['rating']) ? $_GET['rating'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Настройки пагинации
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Построение запроса с учетом фильтров
$params = [];
$conditions = [];

if ($status === 'approved') {
    $conditions[] = "r.is_approved = 1";
} elseif ($status === 'pending') {
    $conditions[] = "r.is_approved = 0";
}

if (!empty($rating)) {
    $conditions[] = "r.rating = :rating";
    $params[':rating'] = $rating;
}

if (!empty($search)) {
    $conditions[] = "(u.first_name LIKE :search OR u.last_name LIKE :search OR r.comment LIKE :search)";
    $params[':search'] = "%{$search}%";
}

$whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

// Получение общего количества отзывов
$countSql = "
    SELECT COUNT(*)
    FROM reviews r
    LEFT JOIN users u ON r.user_id = u.id
    LEFT JOIN trainers tr ON r.trainer_id = tr.id
    LEFT JOIN users t ON tr.user_id = t.id
    LEFT JOIN services s ON r.service_id = s.id
    {$whereClause}
";

$countStmt = $pdo->prepare($countSql);
foreach ($params as $key => $value) {
    $countStmt->bindValue($key, $value);
}
$countStmt->execute();
$totalReviews = $countStmt->fetchColumn();
$totalPages = ceil($totalReviews / $perPage);

// Получение отзывов с пагинацией и сортировкой
$sql = "
    SELECT 
        r.*, 
        u.first_name as user_first_name, 
        u.last_name as user_last_name,
        t.first_name as trainer_first_name, 
        t.last_name as trainer_last_name,
        s.name as service_name,
        m.first_name as moderator_first_name,
        m.last_name as moderator_last_name
    FROM reviews r
    LEFT JOIN users u ON r.user_id = u.id
    LEFT JOIN trainers tr ON r.trainer_id = tr.id
    LEFT JOIN users t ON tr.user_id = t.id
    LEFT JOIN services s ON r.service_id = s.id
    LEFT JOIN users m ON r.moderated_by = m.id
    {$whereClause}
    ORDER BY r.created_at DESC
    LIMIT :offset, :perPage
";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':perPage', $perPage, PDO::PARAM_INT);

foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

$stmt->execute();
$reviews = $stmt->fetchAll();

$pageTitle = 'Отзывы';
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Управление отзывами</h1>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                    switch ($_GET['success']) {
                        case 'approve':
                            echo 'Отзыв успешно одобрен.';
                            break;
                        case 'delete':
                            echo 'Отзыв успешно удален.';
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
                        case 'approve':
                            echo 'Ошибка при одобрении отзыва.';
                            break;
                        case 'delete':
                            echo 'Ошибка при удалении отзыва.';
                            break;
                    }
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Закрыть"></button>
                </div>
            <?php endif; ?>

            <!-- Фильтр отзывов -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-filter me-1"></i>
                    Фильтр отзывов
                </div>
                <div class="card-body">
                    <form action="" method="get" class="row g-3">
                        <div class="col-md-3">
                            <label for="filter-status" class="form-label">Статус</label>
                            <select id="filter-status" name="status" class="form-select">
                                <option value="">Все</option>
                                <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>Одобренные</option>
                                <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Ожидающие</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filter-rating" class="form-label">Рейтинг</label>
                            <select id="filter-rating" name="rating" class="form-select">
                                <option value="">Все</option>
                                <option value="5" <?= $rating === '5' ? 'selected' : '' ?>>5 звезд</option>
                                <option value="4" <?= $rating === '4' ? 'selected' : '' ?>>4 звезды</option>
                                <option value="3" <?= $rating === '3' ? 'selected' : '' ?>>3 звезды</option>
                                <option value="2" <?= $rating === '2' ? 'selected' : '' ?>>2 звезды</option>
                                <option value="1" <?= $rating === '1' ? 'selected' : '' ?>>1 звезда</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="filter-search" class="form-label">Поиск</label>
                            <input type="text" class="form-control" id="filter-search" name="search" placeholder="Имя, текст отзыва" value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Применить</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Таблица отзывов -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Пользователь</th>
                            <th>Рейтинг</th>
                            <th>Отзыв</th>
                            <th>О чем</th>
                            <th>Дата</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reviews as $review): ?>
                        <tr class="<?= !$review['is_approved'] ? 'table-warning' : '' ?>">
                            <td><?= htmlspecialchars($review['id']) ?></td>
                            <td><?= htmlspecialchars($review['user_first_name'] . ' ' . $review['user_last_name']) ?></td>
                            <td>
                                <div class="rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?= $i <= $review['rating'] ? 'text-warning' : 'text-secondary' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </td>
                            <td>
                                <?php 
                                // Ограничиваем комментарий до 50 символов
                                $comment = $review['comment'];
                                echo htmlspecialchars(strlen($comment) > 50 ? substr($comment, 0, 50) . '...' : $comment);
                                ?>
                            </td>
                            <td>
                                <?php if (!empty($review['trainer_id'])): ?>
                                    <span class="badge bg-info">Тренер: <?= htmlspecialchars($review['trainer_first_name'] . ' ' . $review['trainer_last_name']) ?></span>
                                <?php elseif (!empty($review['service_id'])): ?>
                                    <span class="badge bg-success">Услуга: <?= htmlspecialchars($review['service_name']) ?></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Общий</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d.m.Y H:i', strtotime($review['created_at'])) ?></td>
                            <td>
                                <?php if ($review['is_approved']): ?>
                                    <span class="badge bg-success">Одобрен</span>
                                    <?php if (!empty($review['moderated_by'])): ?>
                                        <div class="small text-muted">
                                            <?= htmlspecialchars($review['moderator_first_name'] . ' ' . $review['moderator_last_name']) ?>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Ожидает</span>
                                <?php endif; ?>
                            </td>
                            <td class="action-buttons">
                                <button type="button" class="btn btn-sm btn-info view-review" data-bs-toggle="modal" data-bs-target="#viewReviewModal" data-review-id="<?= $review['id'] ?>">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php if (!$review['is_approved']): ?>
                                <button type="button" class="btn btn-sm btn-success approve-review" data-review-id="<?= $review['id'] ?>">
                                    <i class="fas fa-check"></i>
                                </button>
                                <?php endif; ?>
                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteReviewModal" data-review-id="<?= $review['id'] ?>">
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
                        <a class="page-link" href="?page=<?= $page - 1 ?>&status=<?= urlencode($status) ?>&rating=<?= urlencode($rating) ?>&search=<?= urlencode($search) ?>" aria-label="Предыдущая">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&status=<?= urlencode($status) ?>&rating=<?= urlencode($rating) ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&status=<?= urlencode($status) ?>&rating=<?= urlencode($rating) ?>&search=<?= urlencode($search) ?>" aria-label="Следующая">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </main>
    </div>
</div>

<!-- Модальное окно просмотра отзыва -->
<div class="modal fade" id="viewReviewModal" tabindex="-1" aria-labelledby="viewReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewReviewModalLabel">Детали отзыва</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>ID отзыва:</strong>
                    <span id="view-id"></span>
                </div>
                
                <div class="mb-3">
                    <strong>Пользователь:</strong>
                    <span id="view-user"></span>
                </div>
                
                <div class="mb-3">
                    <strong>Рейтинг:</strong>
                    <div id="view-rating" class="rating"></div>
                </div>
                
                <div class="mb-3">
                    <strong>Отзыв:</strong>
                    <div id="view-comment" class="border p-2 rounded bg-light"></div>
                </div>
                
                <div class="mb-3">
                    <strong>Тип отзыва:</strong>
                    <div id="view-type"></div>
                </div>
                
                <div class="mb-3">
                    <strong>Дата создания:</strong>
                    <span id="view-created"></span>
                </div>
                
                <div class="mb-3">
                    <strong>Статус:</strong>
                    <span id="view-status" class="badge"></span>
                </div>
                
                <div class="mb-3" id="view-moderator-container">
                    <strong>Одобрен:</strong>
                    <span id="view-moderator"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-success approve-from-modal" id="modal-approve-btn">Одобрить</button>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteReviewModal" id="modal-delete-btn">Удалить</button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно удаления отзыва -->
<div class="modal fade" id="deleteReviewModal" tabindex="-1" aria-labelledby="deleteReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteReviewModalLabel">Удалить отзыв</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите удалить этот отзыв? Это действие нельзя отменить.</p>
                <form id="deleteReviewForm" action="reviews.php" method="post">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="review_id" id="delete-review-id">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="submit" form="deleteReviewForm" class="btn btn-danger">Удалить</button>
            </div>
        </div>
    </div>
</div>

<!-- Форма одобрения отзыва -->
<form id="approveReviewForm" action="reviews.php" method="post" style="display: none;">
    <input type="hidden" name="action" value="approve">
    <input type="hidden" name="review_id" id="approve-review-id">
</form>

<script src="js/reviews.js"></script>

<?php include 'includes/footer.php'; ?> 