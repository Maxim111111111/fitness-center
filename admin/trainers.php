<?php
session_start();
require_once('../database/config.php');

// Проверка доступа (только для администраторов и менеджеров)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'manager'])) {
    header('Location: ../login.php');
    exit();
}

// Обработка действий
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'add':
            // Код для добавления тренера
            if (isset($_POST['user_id'], $_POST['experience_years'])) {
                $user_id = $_POST['user_id'];
                $experience_years = $_POST['experience_years'];
                $bio = $_POST['bio'] ?? '';
                $achievements = $_POST['achievements'] ?? '';
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                
                // Обработка загрузки фото
                $photo_url = '';
                if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
                    $upload_dir = '../assets/img/trainers/';
                    
                    // Проверяем, существует ли директория, если нет - создаем
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    $file_name = 'trainer-' . time() . '-' . basename($_FILES['photo']['name']);
                    $target_file = $upload_dir . $file_name;
                    
                    // Проверка типа файла
                    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                    if (in_array($_FILES['photo']['type'], $allowed_types)) {
                        if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
                            $photo_url = 'assets/img/trainers/' . $file_name;
                        }
                    }
                }
                
                // Транзакция для добавления тренера и связанных данных
                try {
                    $pdo->beginTransaction();
                    
                    // Добавляем тренера
                    $stmt = $pdo->prepare("
                        INSERT INTO trainers (user_id, experience_years, bio, achievements, photo_url, is_active, created_at, updated_at)
                        VALUES (:user_id, :experience_years, :bio, :achievements, :photo_url, :is_active, NOW(), NOW())
                    ");
                    
                    $stmt->bindParam(':user_id', $user_id);
                    $stmt->bindParam(':experience_years', $experience_years);
                    $stmt->bindParam(':bio', $bio);
                    $stmt->bindParam(':achievements', $achievements);
                    $stmt->bindParam(':photo_url', $photo_url);
                    $stmt->bindParam(':is_active', $is_active);
                    
                    $stmt->execute();
                    $trainer_id = $pdo->lastInsertId();
                    
                    // Добавляем связь с специализацией, если указана
                    if (!empty($_POST['specialization_id']) && is_numeric($_POST['specialization_id'])) {
                        $specialization_id = $_POST['specialization_id'];
                        $stmtSpecialization = $pdo->prepare("
                            INSERT INTO trainer_specializations (trainer_id, specialization_id)
                            VALUES (:trainer_id, :specialization_id)
                        ");
                        $stmtSpecialization->bindParam(':trainer_id', $trainer_id);
                        $stmtSpecialization->bindParam(':specialization_id', $specialization_id);
                        $stmtSpecialization->execute();
                    }
                    
                    // Добавляем образование, если указано
                    if (!empty($_POST['education'])) {
                        $education = $_POST['education'];
                        $stmtEducation = $pdo->prepare("
                            INSERT INTO trainer_education (trainer_id, description, created_at)
                            VALUES (:trainer_id, :description, NOW())
                        ");
                        $stmtEducation->bindParam(':trainer_id', $trainer_id);
                        $stmtEducation->bindParam(':description', $education);
                        $stmtEducation->execute();
                    }
                    
                    // Добавляем сертификаты, если указаны
                    if (!empty($_POST['certificates'])) {
                        $certificates = $_POST['certificates'];
                        $stmtCertificates = $pdo->prepare("
                            INSERT INTO trainer_certificates (trainer_id, name, created_at)
                            VALUES (:trainer_id, :name, NOW())
                        ");
                        $stmtCertificates->bindParam(':trainer_id', $trainer_id);
                        $stmtCertificates->bindParam(':name', $certificates);
                        $stmtCertificates->execute();
                    }
                    
                    $pdo->commit();
                    header('Location: trainers.php?success=add');
                } catch (Exception $e) {
                    $pdo->rollBack();
                    error_log("Error adding trainer: " . $e->getMessage());
                    header('Location: trainers.php?error=add');
                }
                exit();
            } else {
                header('Location: trainers.php?error=add');
                exit();
            }
            break;
            
        case 'edit':
            // Код для редактирования тренера
            if (isset($_POST['trainer_id'], $_POST['experience_years'])) {
                $trainer_id = $_POST['trainer_id'];
                $user_id = $_POST['user_id'] ?: null;
                $experience_years = $_POST['experience_years'];
                $bio = $_POST['bio'] ?? '';
                $achievements = $_POST['achievements'] ?? '';
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                
                // Получаем текущее фото
                $currentPhotoStmt = $pdo->prepare("SELECT photo_url FROM trainers WHERE id = :id");
                $currentPhotoStmt->bindParam(':id', $trainer_id);
                $currentPhotoStmt->execute();
                $current_photo = $currentPhotoStmt->fetchColumn();
                
                // Обработка загрузки фото
                $photo_url = $current_photo;
                
                // Если установлен флаг удаления фото
                if (isset($_POST['delete_photo']) && $_POST['delete_photo'] == 1) {
                    // Удаляем файл, если он существует
                    if (!empty($current_photo) && file_exists('../' . $current_photo)) {
                        unlink('../' . $current_photo);
                    }
                    $photo_url = '';
                }
                
                // Если загружается новое фото
                if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
                    $upload_dir = '../assets/img/trainers/';
                    
                    // Проверяем, существует ли директория, если нет - создаем
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    $file_name = 'trainer-' . time() . '-' . basename($_FILES['photo']['name']);
                    $target_file = $upload_dir . $file_name;
                    
                    // Проверка типа файла
                    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                    if (in_array($_FILES['photo']['type'], $allowed_types)) {
                        // Удаляем старое фото, если оно существует
                        if (!empty($current_photo) && file_exists('../' . $current_photo)) {
                            unlink('../' . $current_photo);
                        }
                        
                        if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
                            $photo_url = 'assets/img/trainers/' . $file_name;
                        }
                    }
                }
                
                // Транзакция для обновления тренера и связанных данных
                try {
                    $pdo->beginTransaction();
                    
                    // Обновляем тренера
                    $stmt = $pdo->prepare("
                        UPDATE trainers
                        SET user_id = :user_id,
                            experience_years = :experience_years,
                            bio = :bio,
                            achievements = :achievements,
                            photo_url = :photo_url,
                            is_active = :is_active,
                            updated_at = NOW()
                        WHERE id = :id
                    ");
                    
                    $stmt->bindParam(':id', $trainer_id);
                    $stmt->bindParam(':user_id', $user_id);
                    $stmt->bindParam(':experience_years', $experience_years);
                    $stmt->bindParam(':bio', $bio);
                    $stmt->bindParam(':achievements', $achievements);
                    $stmt->bindParam(':photo_url', $photo_url);
                    $stmt->bindParam(':is_active', $is_active);
                    $stmt->execute();
                    
                    // Обновляем специализацию
                    if (!empty($_POST['specialization_id']) && is_numeric($_POST['specialization_id'])) {
                        $specialization_id = $_POST['specialization_id'];
                        
                        // Удаляем текущие связи
                        $stmtDeleteSpec = $pdo->prepare("DELETE FROM trainer_specializations WHERE trainer_id = :trainer_id");
                        $stmtDeleteSpec->bindParam(':trainer_id', $trainer_id);
                        $stmtDeleteSpec->execute();
                        
                        // Добавляем новую связь
                        $stmtSpecialization = $pdo->prepare("
                            INSERT INTO trainer_specializations (trainer_id, specialization_id)
                            VALUES (:trainer_id, :specialization_id)
                        ");
                        $stmtSpecialization->bindParam(':trainer_id', $trainer_id);
                        $stmtSpecialization->bindParam(':specialization_id', $specialization_id);
                        $stmtSpecialization->execute();
                    }
                    
                    // Обновляем образование
                    if (!empty($_POST['education'])) {
                        $education = $_POST['education'];
                        
                        // Удаляем текущие записи
                        $stmtDeleteEdu = $pdo->prepare("DELETE FROM trainer_education WHERE trainer_id = :trainer_id");
                        $stmtDeleteEdu->bindParam(':trainer_id', $trainer_id);
                        $stmtDeleteEdu->execute();
                        
                        // Добавляем новую запись
                        $stmtEducation = $pdo->prepare("
                            INSERT INTO trainer_education (trainer_id, description, created_at)
                            VALUES (:trainer_id, :description, NOW())
                        ");
                        $stmtEducation->bindParam(':trainer_id', $trainer_id);
                        $stmtEducation->bindParam(':description', $education);
                        $stmtEducation->execute();
                    }
                    
                    // Обновляем сертификаты
                    if (!empty($_POST['certificates'])) {
                        $certificates = $_POST['certificates'];
                        
                        // Удаляем текущие записи
                        $stmtDeleteCert = $pdo->prepare("DELETE FROM trainer_certificates WHERE trainer_id = :trainer_id");
                        $stmtDeleteCert->bindParam(':trainer_id', $trainer_id);
                        $stmtDeleteCert->execute();
                        
                        // Добавляем новую запись
                        $stmtCertificates = $pdo->prepare("
                            INSERT INTO trainer_certificates (trainer_id, name, created_at)
                            VALUES (:trainer_id, :name, NOW())
                        ");
                        $stmtCertificates->bindParam(':trainer_id', $trainer_id);
                        $stmtCertificates->bindParam(':name', $certificates);
                        $stmtCertificates->execute();
                    }
                    
                    $pdo->commit();
                    header('Location: trainers.php?success=edit');
                } catch (Exception $e) {
                    $pdo->rollBack();
                    error_log("Error updating trainer: " . $e->getMessage());
                    header('Location: trainers.php?error=edit');
                }
                exit();
            } else {
                header('Location: trainers.php?error=edit');
                exit();
            }
            break;
            
        case 'delete':
            // Код для удаления тренера
            if (isset($_POST['trainer_id'])) {
                $trainer_id = $_POST['trainer_id'];
                
                // Получаем фото тренера перед удалением
                $photoStmt = $pdo->prepare("SELECT photo_url FROM trainers WHERE id = :id");
                $photoStmt->bindParam(':id', $trainer_id);
                $photoStmt->execute();
                $photo_url = $photoStmt->fetchColumn();
                
                // Удаляем запись из базы данных
                $stmt = $pdo->prepare("DELETE FROM trainers WHERE id = :id");
                $stmt->bindParam(':id', $trainer_id);
                
                if ($stmt->execute()) {
                    // Удаляем файл фото, если он существует
                    if (!empty($photo_url) && file_exists('../' . $photo_url)) {
                        unlink('../' . $photo_url);
                    }
                    header('Location: trainers.php?success=delete');
                } else {
                    header('Location: trainers.php?error=delete');
                }
                exit();
            }
            break;
            
        case 'change_status':
            // Код для изменения статуса тренера
            if (isset($_POST['trainer_id'], $_POST['is_active'])) {
                $trainer_id = $_POST['trainer_id'];
                $is_active = (int)$_POST['is_active'];
                
                $stmt = $pdo->prepare("UPDATE trainers SET is_active = :is_active WHERE id = :id");
                $stmt->bindParam(':id', $trainer_id);
                $stmt->bindParam(':is_active', $is_active);
                
                $response = ['success' => false];
                
                if ($stmt->execute()) {
                    $response['success'] = true;
                }
                
                // Если запрос пришел через AJAX
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    exit();
                }
                
                // Если это обычный POST запрос
                if ($response['success']) {
                    header('Location: trainers.php?success=status');
                } else {
                    header('Location: trainers.php?error=status');
                }
                exit();
            }
            break;
    }
}

// Настройки пагинации
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Подготовка условий фильтрации
$whereConditions = [];
$params = [];

// Фильтр по специализации
if (!empty($_GET['specialization_id']) && is_numeric($_GET['specialization_id'])) {
    $whereConditions[] = "EXISTS (SELECT 1 FROM trainer_specializations tsp WHERE tsp.trainer_id = t.id AND tsp.specialization_id = :specialization_id)";
    $params[':specialization_id'] = $_GET['specialization_id'];
}

// Фильтр по опыту работы
if (!empty($_GET['experience']) && is_numeric($_GET['experience'])) {
    $whereConditions[] = "t.experience_years >= :experience";
    $params[':experience'] = $_GET['experience'];
}

// Фильтр по поиску
if (!empty($_GET['search'])) {
    $whereConditions[] = "(u.first_name LIKE :search OR u.last_name LIKE :search OR u.email LIKE :search)";
    $params[':search'] = '%' . $_GET['search'] . '%';
}

// Формирование WHERE части запроса
$whereClause = '';
if (!empty($whereConditions)) {
    $whereClause = "WHERE " . implode(" AND ", $whereConditions);
}

// Получение общего количества тренеров с учетом фильтров
$countSql = "
    SELECT COUNT(DISTINCT t.id) 
    FROM trainers t
    LEFT JOIN users u ON t.user_id = u.id
    LEFT JOIN trainer_specializations ts ON t.id = ts.trainer_id
    $whereClause
";
$countStmt = $pdo->prepare($countSql);
foreach ($params as $key => $value) {
    $countStmt->bindValue($key, $value);
}
$countStmt->execute();
$totalTrainers = $countStmt->fetchColumn();
$totalPages = ceil($totalTrainers / $perPage);

// Получение списка тренеров с данными пользователей и их специализациями
$trainersSql = "
    SELECT t.*, u.first_name, u.last_name, u.email,
           (SELECT s.name FROM specializations s 
            JOIN trainer_specializations ts ON s.id = ts.specialization_id 
            WHERE ts.trainer_id = t.id LIMIT 1) as specialization_name  
    FROM trainers t
    LEFT JOIN users u ON t.user_id = u.id
    LEFT JOIN trainer_specializations ts ON t.id = ts.trainer_id
    $whereClause
    GROUP BY t.id, u.first_name, u.last_name, u.email
    ORDER BY t.id DESC
    LIMIT :offset, :perPage
";
$trainersStmt = $pdo->prepare($trainersSql);
foreach ($params as $key => $value) {
    $trainersStmt->bindValue($key, $value);
}
$trainersStmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$trainersStmt->bindParam(':perPage', $perPage, PDO::PARAM_INT);
$trainersStmt->execute();
$trainers = $trainersStmt->fetchAll();

// Получение списка специализаций из таблицы специализаций
$categoriesStmt = $pdo->query("
    SELECT id, name FROM specializations 
    ORDER BY name
");
$categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

// Получение списка доступных пользователей для создания тренеров
$availableUsersStmt = $pdo->query("
    SELECT u.id, CONCAT(u.first_name, ' ', u.last_name) as full_name 
    FROM users u 
    LEFT JOIN trainers t ON u.id = t.user_id
    WHERE t.id IS NULL AND u.is_active = 1
    ORDER BY u.first_name, u.last_name
");
$availableUsers = $availableUsersStmt->fetchAll();

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
                            <select id="filter-specialization" name="specialization_id" class="form-select">
                                <option value="">Все</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= htmlspecialchars($category['id']) ?>"><?= htmlspecialchars($category['name']) ?></option>
                                <?php endforeach; ?>
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
                                    <img src="../<?= htmlspecialchars($trainer['photo_url']) ?>" alt="Фото тренера" class="img-thumbnail" width="50">
                                <?php else: ?>
                                    <img src="../assets/img/trainers/default.jpg" alt="Фото по умолчанию" class="img-thumbnail" width="50">
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= htmlspecialchars(($trainer['first_name'] ?? '') . ' ' . ($trainer['last_name'] ?? '')) ?>
                                <div class="small text-muted"><?= htmlspecialchars($trainer['email'] ?? '') ?></div>
                            </td>
                            <td><?= htmlspecialchars($trainer['specialization_name'] ?? 'Не указана') ?></td>
                            <td><?= isset($trainer['experience_years']) ? htmlspecialchars($trainer['experience_years']) . ' лет' : '' ?></td>
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
                                <?php foreach ($availableUsers as $user): ?>
                                    <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['full_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">
                                Если нужного пользователя нет в списке, сначала создайте его в разделе "Пользователи".
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="add-photo" class="form-label">Фото тренера</label>
                            <input type="file" class="form-control" id="add-photo" name="photo" accept="image/*">
                            <div class="image-preview mt-2" id="add-photo-preview">
                                <div class="no-image"><i class="fas fa-user"></i></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="add-specialization" class="form-label">Специализация <span class="text-danger">*</span></label>
                            <select class="form-select" id="add-specialization" name="specialization_id">
                                <option value="">Выберите специализацию</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= htmlspecialchars($category['id']) ?>"><?= htmlspecialchars($category['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="add-experience-years" class="form-label">Опыт работы (лет) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="add-experience-years" name="experience_years" min="0" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add-bio" class="form-label">Биография</label>
                        <textarea class="form-control" id="add-bio" name="bio" rows="3"></textarea>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="add-education" class="form-label">Образование</label>
                            <textarea class="form-control" id="add-education" name="education" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="add-certificates" class="form-label">Сертификаты</label>
                            <textarea class="form-control" id="add-certificates" name="certificates" rows="2"></textarea>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add-achievements" class="form-label">Достижения</label>
                        <textarea class="form-control" id="add-achievements" name="achievements" rows="2"></textarea>
                    </div>
                    
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
                            <select class="form-select" id="edit-specialization" name="specialization_id">
                                <option value="">Выберите специализацию</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= htmlspecialchars($category['id']) ?>"><?= htmlspecialchars($category['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
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

<!-- Toast для уведомлений о статусе -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="statusToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <strong class="me-auto">Уведомление</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Закрыть"></button>
        </div>
        <div class="toast-body" id="toastMessage">
            Статус успешно изменен
        </div>
    </div>
</div>

<script src="js/trainers.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Обработка превью изображения при добавлении тренера
    document.getElementById('add-photo')?.addEventListener('change', function() {
        const file = this.files[0];
        const preview = document.getElementById('add-photo-preview');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" class="img-fluid">`;
            };
            reader.readAsDataURL(file);
        } else {
            preview.innerHTML = `<div class="no-image"><i class="fas fa-user"></i></div>`;
        }
    });
    
    // Управление расписанием при добавлении тренера
    document.querySelectorAll('.schedule-day-toggle').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const day = this.dataset.day;
            const timesContainer = document.getElementById(`add-schedule-${day}-times`);
            
            if (this.checked) {
                timesContainer.style.display = 'block';
            } else {
                timesContainer.style.display = 'none';
            }
        });
    });
    
    // Загрузка детальной информации о тренере через AJAX
    document.querySelectorAll('.view-trainer-details').forEach(button => {
        button.addEventListener('click', function() {
            const trainerId = this.dataset.trainerId;
            const modalBody = document.querySelector('#trainerDetailsModal .modal-body');
            const editBtn = document.getElementById('edit-detail-trainer-btn');
            
            // Устанавливаем ID тренера для кнопки редактирования
            editBtn.href = `trainers.php?action=edit&id=${trainerId}`;
            
            // Очищаем предыдущие данные и показываем спиннер
            modalBody.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Загрузка...</span>
                    </div>
                </div>
            `;
            
            // Загружаем детальную информацию о тренере
            fetch(`get_trainer.php?id=${trainerId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const trainer = data.trainer;
                        const user = data.user;
                        
                        let scheduleHtml = '';
                        if (trainer.schedule) {
                            const schedule = JSON.parse(trainer.schedule);
                            scheduleHtml = `
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <h6>Расписание работы</h6>
                                        <div class="schedule-display">
                            `;
                            
                            const dayNames = {
                                monday: 'Понедельник',
                                tuesday: 'Вторник',
                                wednesday: 'Среда',
                                thursday: 'Четверг',
                                friday: 'Пятница',
                                saturday: 'Суббота',
                                sunday: 'Воскресенье'
                            };
                            
                            Object.keys(schedule).forEach(day => {
                                if (schedule[day].start && schedule[day].end) {
                                    scheduleHtml += `
                                        <div class="schedule-day">
                                            <strong>${dayNames[day]}:</strong> ${schedule[day].start} - ${schedule[day].end}
                                        </div>
                                    `;
                                }
                            });
                            
                            scheduleHtml += `
                                        </div>
                                    </div>
                                </div>
                            `;
                        }
                        
                        let photoHtml = '';
                        if (trainer.photo_url) {
                            photoHtml = `<img src="../${trainer.photo_url}" class="img-fluid rounded trainer-detail-photo">`;
                        } else {
                            photoHtml = `
                                <div class="trainer-detail-photo-placeholder">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                            `;
                        }
                        
                        modalBody.innerHTML = `
                            <div class="row">
                                <div class="col-md-4 text-center mb-3">
                                    ${photoHtml}
                                    <div class="mt-2">
                                        <span class="badge bg-${trainer.is_active ? 'success' : 'danger'}">
                                            ${trainer.is_active ? 'Активен' : 'Не активен'}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <h4>${user.first_name} ${user.last_name}</h4>
                                    <p class="text-muted">${trainer.role}</p>
                                    
                                    <div class="mb-3">
                                        <strong>Опыт работы:</strong> ${trainer.experience_years} ${getPluralForm(trainer.experience_years, 'год', 'года', 'лет')}
                                    </div>
                                    
                                    <div class="mb-3">
                                        <strong>Контакты:</strong><br>
                                        Email: ${user.email}<br>
                                        Телефон: ${user.phone || 'Не указан'}
                                    </div>
                                </div>
                            </div>
                            
                            ${scheduleHtml}
                            
                            <div class="row">
                                <div class="col-12">
                                    <div class="card mb-3">
                                        <div class="card-header">
                                            <h6 class="mb-0">Биография</h6>
                                        </div>
                                        <div class="card-body">
                                            ${trainer.bio || '<em>Информация отсутствует</em>'}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card mb-3">
                                        <div class="card-header">
                                            <h6 class="mb-0">Образование</h6>
                                        </div>
                                        <div class="card-body">
                                            ${trainer.education || '<em>Информация отсутствует</em>'}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card mb-3">
                                        <div class="card-header">
                                            <h6 class="mb-0">Сертификаты</h6>
                                        </div>
                                        <div class="card-body">
                                            ${trainer.certificates || '<em>Информация отсутствует</em>'}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">Достижения</h6>
                                        </div>
                                        <div class="card-body">
                                            ${trainer.achievements || '<em>Информация отсутствует</em>'}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    } else {
                        modalBody.innerHTML = `
                            <div class="alert alert-danger">
                                Ошибка при загрузке данных тренера: ${data.message}
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    modalBody.innerHTML = `
                        <div class="alert alert-danger">
                            Произошла ошибка при загрузке данных тренера
                        </div>
                    `;
                });
        });
    });
    
    // Функция для склонения слов
    function getPluralForm(number, one, two, five) {
        let n = Math.abs(number);
        n %= 100;
        if (n >= 5 && n <= 20) {
            return five;
        }
        n %= 10;
        if (n === 1) {
            return one;
        }
        if (n >= 2 && n <= 4) {
            return two;
        }
        return five;
    }
});
</script>

<style>
.trainer-card {
    height: 100%;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.trainer-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.trainer-photo {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-top-left-radius: calc(0.25rem - 1px);
    border-top-right-radius: calc(0.25rem - 1px);
}

.trainer-photo-placeholder {
    width: 100%;
    height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f5f5f5;
    color: #adb5bd;
    font-size: 4rem;
    border-top-left-radius: calc(0.25rem - 1px);
    border-top-right-radius: calc(0.25rem - 1px);
}

.trainer-detail-photo {
    max-width: 100%;
    max-height: 250px;
    border-radius: 5px;
}

.trainer-detail-photo-placeholder {
    width: 100%;
    height: 250px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f5f5f5;
    color: #adb5bd;
    font-size: 6rem;
    border-radius: 5px;
}

.image-preview {
    width: 100%;
    height: 150px;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}

.image-preview img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.image-preview .no-image {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f8f9fa;
    color: #6c757d;
    font-size: 2rem;
}

.schedule-display .schedule-day {
    margin-bottom: 5px;
    padding: 5px 10px;
    background-color: #f8f9fa;
    border-radius: 4px;
}

.action-buttons .btn {
    margin-right: 5px;
}

.specialization-badge {
    margin-right: 5px;
    margin-bottom: 5px;
}
</style>

<?php include 'includes/footer.php'; ?> 