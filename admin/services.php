<?php
session_start();
require_once('../database/config.php');


require_once('includes/auth_check.php');

// Check access for services
checkAccess('services');

// Обработка действий
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'add':
            // Код для добавления услуги
            if (empty($_POST['name']) || !isset($_POST['price']) || empty($_POST['duration'])) {
                header('Location: services.php?error=add');
                exit();
            }
            
            // Подготовка данных
            $name = $_POST['name'];
            $description = $_POST['description'] ?? '';
            $price = (float)$_POST['price'];
            $duration = (int)$_POST['duration'];
            $max_participants = !empty($_POST['max_participants']) ? (int)$_POST['max_participants'] : null;
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            // Обработка загрузки изображения
            $image_url = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $upload_dir = '../assets/img/services/';
                
                // Проверяем, существует ли директория, если нет - создаем
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_name = 'service-' . time() . '-' . basename($_FILES['image']['name']);
                $target_file = $upload_dir . $file_name;
                
                // Проверка типа файла
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (in_array($_FILES['image']['type'], $allowed_types)) {
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                        $image_url = 'assets/img/services/' . $file_name;
                    }
                }
            }
            
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO services (name, description, duration, price, max_participants, is_active, image_url, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([
                    $name,
                    $description,
                    $duration,
                    $price,
                    $max_participants,
                    $is_active,
                    $image_url
                ]);
                
                header('Location: services.php?success=add');
                exit();
            } catch (PDOException $e) {
                error_log("Database error: " . $e->getMessage());
                header('Location: services.php?error=add');
                exit();
            }
            break;
            
        case 'edit':
            // Код для редактирования услуги
            if (empty($_POST['service_id']) || empty($_POST['name']) || !isset($_POST['price']) || empty($_POST['duration'])) {
                header('Location: services.php?error=edit');
                exit();
            }
            
            // Подготовка данных
            $id = (int)$_POST['service_id'];
            $name = $_POST['name'];
            $description = $_POST['description'] ?? '';
            $price = (float)$_POST['price'];
            $duration = (int)$_POST['duration'];
            $max_participants = !empty($_POST['max_participants']) ? (int)$_POST['max_participants'] : null;
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            // Получаем текущее изображение
            $currentImageStmt = $pdo->prepare("SELECT image_url FROM services WHERE id = ?");
            $currentImageStmt->execute([$id]);
            $current_image = $currentImageStmt->fetchColumn();
            
            // Обработка изображения
            $image_url = $current_image;
            
            // Если установлен флаг удаления изображения
            if (isset($_POST['delete_image']) && $_POST['delete_image'] == 1) {
                // Удаляем файл, если он существует
                if (!empty($current_image) && file_exists('../' . $current_image)) {
                    unlink('../' . $current_image);
                }
                $image_url = '';
            }
            
            // Если загружается новое изображение
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $upload_dir = '../assets/img/services/';
                
                // Проверяем, существует ли директория, если нет - создаем
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_name = 'service-' . time() . '-' . basename($_FILES['image']['name']);
                $target_file = $upload_dir . $file_name;
                
                // Проверка типа файла
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (in_array($_FILES['image']['type'], $allowed_types)) {
                    // Удаляем старое изображение, если оно существует
                    if (!empty($current_image) && file_exists('../' . $current_image)) {
                        unlink('../' . $current_image);
                    }
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                        $image_url = 'assets/img/services/' . $file_name;
                    }
                }
            }
            
            try {
                $stmt = $pdo->prepare("
                    UPDATE services 
                    SET name = ?, description = ?, duration = ?, price = ?, 
                        max_participants = ?, is_active = ?, image_url = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([
                    $name,
                    $description,
                    $duration,
                    $price,
                    $max_participants,
                    $is_active,
                    $image_url,
                    $id
                ]);
                
                header('Location: services.php?success=edit');
                exit();
            } catch (PDOException $e) {
                error_log("Database error: " . $e->getMessage());
                header('Location: services.php?error=edit');
                exit();
            }
            break;
            
        case 'delete':
            // Код для удаления услуги
            if (empty($_POST['service_id'])) {
                header('Location: services.php?error=delete');
                exit();
            }
            
            $id = (int)$_POST['service_id'];
            
            try {
                // Получаем информацию об изображении
                $imageStmt = $pdo->prepare("SELECT image_url FROM services WHERE id = ?");
                $imageStmt->execute([$id]);
                $image_url = $imageStmt->fetchColumn();
                
                // Проверяем, используется ли услуга в тренировках
                $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM training_sessions WHERE service_id = ?");
                $checkStmt->execute([$id]);
                if ($checkStmt->fetchColumn() > 0) {
                    header('Location: services.php?error=delete&message=in_use');
                    exit();
                }
                
                $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
                $stmt->execute([$id]);
                
                // Удаляем изображение, если оно существует
                if (!empty($image_url) && file_exists('../' . $image_url)) {
                    unlink('../' . $image_url);
                }
                
                header('Location: services.php?success=delete');
                exit();
            } catch (PDOException $e) {
                error_log("Database error: " . $e->getMessage());
                header('Location: services.php?error=delete');
                exit();
            }
            break;
            
        case 'change_status':
            // Обработка AJAX запроса на изменение статуса
            header('Content-Type: application/json');
            
            if (empty($_POST['service_id']) || !isset($_POST['is_active'])) {
                exit(json_encode(['success' => false, 'message' => 'Неверные параметры']));
            }
            
            $id = (int)$_POST['service_id'];
            $isActive = (int)$_POST['is_active'];
            
            try {
                $stmt = $pdo->prepare("UPDATE services SET is_active = ? WHERE id = ?");
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

// Получение общего количества услуг
$countStmt = $pdo->query("SELECT COUNT(*) FROM services");
$totalServices = $countStmt->fetchColumn();
$totalPages = ceil($totalServices / $perPage);

// Получение списка услуг
$servicesStmt = $pdo->prepare("
    SELECT * FROM services
    ORDER BY id DESC
    LIMIT :offset, :perPage
");
$servicesStmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$servicesStmt->bindParam(':perPage', $perPage, PDO::PARAM_INT);
$servicesStmt->execute();
$services = $servicesStmt->fetchAll();

$pageTitle = 'Услуги';
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Управление услугами</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                        <i class="fas fa-plus me-1"></i> Добавить услугу
                    </button>
                </div>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                    switch ($_GET['success']) {
                        case 'add':
                            echo 'Услуга успешно добавлена.';
                            break;
                        case 'edit':
                            echo 'Услуга успешно обновлена.';
                            break;
                        case 'delete':
                            echo 'Услуга успешно удалена.';
                            break;
                        case 'status':
                            echo 'Статус услуги изменен.';
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
                            echo 'Ошибка при добавлении услуги.';
                            break;
                        case 'edit':
                            echo 'Ошибка при обновлении услуги.';
                            break;
                        case 'delete':
                            echo 'Ошибка при удалении услуги.';
                            break;
                    }
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Закрыть"></button>
                </div>
            <?php endif; ?>

            <!-- Таблица услуг -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Изображение</th>
                            <th>Название</th>
                            <th>Описание</th>
                            <th>Длительность</th>
                            <th>Цена</th>
                            <th>Макс. участников</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($services as $service): ?>
                        <tr>
                            <td><?= htmlspecialchars($service['id']) ?></td>
                            <td>
                                <?php if(!empty($service['image_url'])): ?>
                                    <img src="../<?= htmlspecialchars($service['image_url']) ?>" alt="<?= htmlspecialchars($service['name']) ?>" class="service-thumbnail">
                                <?php else: ?>
                                    <div class="service-thumbnail no-image">
                                        <i class="fas fa-dumbbell"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($service['name']) ?></td>
                            <td>
                                <?php 
                                // Ограничиваем описание до 50 символов
                                $description = $service['description'] ?? '';
                                echo htmlspecialchars(strlen($description) > 50 ? substr($description, 0, 50) . '...' : $description);
                                ?>
                            </td>
                            <td><?= $service['duration'] ?> мин.</td>
                            <td><?= number_format($service['price'], 2, '.', ' ') ?> руб.</td>
                            <td><?= $service['max_participants'] ?: 'Не ограничено' ?></td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input status-toggle" type="checkbox" 
                                        data-service-id="<?= $service['id'] ?>" 
                                        <?= $service['is_active'] ? 'checked' : '' ?>>
                                </div>
                            </td>
                            <td class="action-buttons">
                                <button type="button" class="btn btn-sm btn-primary edit-service" data-bs-toggle="modal" data-bs-target="#editServiceModal" data-service-id="<?= $service['id'] ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteServiceModal" data-service-id="<?= $service['id'] ?>">
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

<!-- Модальное окно добавления услуги -->
<div class="modal fade" id="addServiceModal" tabindex="-1" aria-labelledby="addServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addServiceModalLabel">Добавить услугу</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <form id="addServiceForm" action="services.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label for="add-name" class="form-label">Название <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="add-name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add-description" class="form-label">Описание</label>
                        <textarea class="form-control" id="add-description" name="description" rows="4"></textarea>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="add-duration" class="form-label">Длительность (мин.) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="add-duration" name="duration" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label for="add-price" class="form-label">Цена (руб.) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="add-price" name="price" min="0" step="0.01" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add-max-participants" class="form-label">Максимальное количество участников</label>
                        <input type="number" class="form-control" id="add-max-participants" name="max_participants" min="1">
                        <small class="form-text text-muted">Оставьте пустым для неограниченного количества участников</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add-image" class="form-label">Изображение</label>
                        <input type="file" class="form-control" id="add-image" name="image" accept="image/*">
                        <div class="image-preview mt-2" id="add-image-preview">
                            <div class="no-image"><i class="fas fa-image"></i></div>
                        </div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="add-is-active" name="is_active" value="1" checked>
                        <label class="form-check-label" for="add-is-active">Активная</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="submit" form="addServiceForm" class="btn btn-primary">Добавить</button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно редактирования услуги -->
<div class="modal fade" id="editServiceModal" tabindex="-1" aria-labelledby="editServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editServiceModalLabel">Редактировать услугу</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <form id="editServiceForm" action="services.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="service_id" id="edit-service-id">
                    
                    <div class="mb-3">
                        <label for="edit-name" class="form-label">Название <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit-name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit-description" class="form-label">Описание</label>
                        <textarea class="form-control" id="edit-description" name="description" rows="4"></textarea>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit-duration" class="form-label">Длительность (мин.) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="edit-duration" name="duration" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit-price" class="form-label">Цена (руб.) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="edit-price" name="price" min="0" step="0.01" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit-max-participants" class="form-label">Максимальное количество участников</label>
                        <input type="number" class="form-control" id="edit-max-participants" name="max_participants" min="1">
                        <small class="form-text text-muted">Оставьте пустым для неограниченного количества участников</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit-image" class="form-label">Изображение</label>
                        <input type="file" class="form-control" id="edit-image" name="image" accept="image/*">
                        
                        <div class="image-preview mt-2" id="edit-image-preview">
                            <div class="no-image"><i class="fas fa-image"></i></div>
                        </div>
                        
                        <div class="form-check mt-2" id="edit-delete-image-container" style="display: none;">
                            <input type="checkbox" class="form-check-input" id="edit-delete-image" name="delete_image" value="1">
                            <label class="form-check-label" for="edit-delete-image">Удалить текущее изображение</label>
                        </div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="edit-is-active" name="is_active" value="1">
                        <label class="form-check-label" for="edit-is-active">Активная</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="submit" form="editServiceForm" class="btn btn-primary">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно удаления услуги -->
<div class="modal fade" id="deleteServiceModal" tabindex="-1" aria-labelledby="deleteServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteServiceModalLabel">Подтверждение удаления</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите удалить эту услугу? Это действие нельзя отменить.</p>
            </div>
            <div class="modal-footer">
                <form action="services.php" method="post">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="service_id" id="delete-service-id">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-danger">Удалить</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Обработка переключения статуса
    document.querySelectorAll('.status-toggle').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const serviceId = this.dataset.serviceId;
            const isActive = this.checked ? 1 : 0;
            
            fetch('services.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=change_status&service_id=${serviceId}&is_active=${isActive}`
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert(data.message);
                    this.checked = !this.checked; // Возвращаем состояние переключателя
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Произошла ошибка при изменении статуса');
                this.checked = !this.checked; // Возвращаем состояние переключателя
            });
        });
    });
    
    // Обработка предпросмотра изображения при добавлении
    document.getElementById('add-image').addEventListener('change', function() {
        const file = this.files[0];
        const preview = document.getElementById('add-image-preview');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" class="img-fluid">`;
            };
            reader.readAsDataURL(file);
        } else {
            preview.innerHTML = `<div class="no-image"><i class="fas fa-image"></i></div>`;
        }
    });
    
    // Обработка загрузки данных в форму редактирования
    document.querySelectorAll('.edit-service').forEach(function(button) {
        button.addEventListener('click', function() {
            const serviceId = this.dataset.serviceId;
            
            fetch(`get_service.php?id=${serviceId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const service = data.service;
                    
                    // Заполняем поля формы
                    document.getElementById('edit-service-id').value = service.id;
                    document.getElementById('edit-name').value = service.name;
                    document.getElementById('edit-description').value = service.description || '';
                    document.getElementById('edit-duration').value = service.duration;
                    document.getElementById('edit-price').value = service.price;
                    document.getElementById('edit-max-participants').value = service.max_participants || '';
                    document.getElementById('edit-is-active').checked = service.is_active == 1;
                    
                    // Отображаем текущее изображение
                    const imagePreview = document.getElementById('edit-image-preview');
                    const deleteImageContainer = document.getElementById('edit-delete-image-container');
                    
                    if (service.image_url) {
                        imagePreview.innerHTML = `<img src="../${service.image_url}" class="img-fluid">`;
                        deleteImageContainer.style.display = 'block';
                    } else {
                        imagePreview.innerHTML = `<div class="no-image"><i class="fas fa-image"></i></div>`;
                        deleteImageContainer.style.display = 'none';
                    }
                    
                    // Сбрасываем поле выбора файла и чекбокс удаления
                    document.getElementById('edit-image').value = '';
                    document.getElementById('edit-delete-image').checked = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ошибка при загрузке данных услуги');
            });
        });
    });
    
    // Обработка предпросмотра изображения при редактировании
    document.getElementById('edit-image').addEventListener('change', function() {
        const file = this.files[0];
        const preview = document.getElementById('edit-image-preview');
        const deleteImageContainer = document.getElementById('edit-delete-image-container');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" class="img-fluid">`;
            };
            reader.readAsDataURL(file);
            
            // Проверяем, было ли изображение раньше
            if (deleteImageContainer.style.display === 'block') {
                document.getElementById('edit-delete-image').checked = false;
            }
        }
    });
    
    // Обработка удаления услуги
    document.querySelectorAll('[data-bs-target="#deleteServiceModal"]').forEach(function(button) {
        button.addEventListener('click', function() {
            const serviceId = this.dataset.serviceId;
            document.getElementById('delete-service-id').value = serviceId;
        });
    });
});
</script>

<style>
.service-thumbnail {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 4px;
}

.service-thumbnail.no-image {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    font-size: 1.5rem;
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

.action-buttons .btn {
    margin-right: 5px;
}
</style>

<?php include 'includes/footer.php'; ?> 