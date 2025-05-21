<?php
session_start();
require_once('../database/config.php');
require_once('includes/auth_check.php');

// Check access for settings
checkAccess('settings');

// Обработка сохранения настроек
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'save_settings') {
        // Подготовка данных
        $settingsData = [
            'site_name' => $_POST['site_name'] ?? 'Moreon Fitness',
            'site_description' => $_POST['site_description'] ?? '',
            'contact_email' => $_POST['contact_email'] ?? '',
            'contact_phone' => $_POST['contact_phone'] ?? '',
            'address' => $_POST['address'] ?? '',
            'working_hours' => $_POST['working_hours'] ?? '',
            'facebook_url' => $_POST['facebook_url'] ?? '',
            'instagram_url' => $_POST['instagram_url'] ?? '',
            'vk_url' => $_POST['vk_url'] ?? '',
            'youtube_url' => $_POST['youtube_url'] ?? '',
            'enable_online_booking' => isset($_POST['enable_online_booking']) ? 1 : 0,
            'booking_advance_days' => (int)($_POST['booking_advance_days'] ?? 7),
            'cancellation_hours' => (int)($_POST['cancellation_hours'] ?? 24),
            'maintenance_mode' => isset($_POST['maintenance_mode']) ? 1 : 0,
            'maintenance_message' => $_POST['maintenance_message'] ?? 'Сайт находится на техническом обслуживании. Пожалуйста, зайдите позже.',
        ];
        
        try {
            // Обновление настроек в базе данных
            foreach ($settingsData as $key => $value) {
                $stmt = $pdo->prepare("
                    INSERT INTO settings (setting_key, setting_value, updated_at) 
                    VALUES (?, ?, NOW())
                    ON DUPLICATE KEY UPDATE 
                    setting_value = VALUES(setting_value), 
                    updated_at = VALUES(updated_at)
                ");
                $stmt->execute([$key, $value]);
            }
            
            // Обновление загруженного логотипа, если он был загружен
            if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
                $logoInfo = $_FILES['site_logo'];
                $fileExtension = strtolower(pathinfo($logoInfo['name'], PATHINFO_EXTENSION));
                
                // Проверка допустимого формата
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'svg'];
                if (in_array($fileExtension, $allowedExtensions)) {
                    $targetDir = '../assets/img/';
                    if (!is_dir($targetDir)) {
                        mkdir($targetDir, 0755, true);
                    }
                    
                    $targetFile = $targetDir . 'logo.' . $fileExtension;
                    if (move_uploaded_file($logoInfo['tmp_name'], $targetFile)) {
                        // Сохранение пути к логотипу в настройках
                        $logoPath = 'assets/img/logo.' . $fileExtension;
                        $stmt = $pdo->prepare("
                            INSERT INTO settings (setting_key, setting_value, updated_at) 
                            VALUES ('site_logo', ?, NOW())
                            ON DUPLICATE KEY UPDATE 
                            setting_value = VALUES(setting_value), 
                            updated_at = VALUES(updated_at)
                        ");
                        $stmt->execute([$logoPath]);
                    }
                }
            }
            
            header('Location: settings.php?success=saved');
            exit();
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            header('Location: settings.php?error=save');
            exit();
        }
    }
}

// Получение текущих настроек
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    $settings = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $settings = [];
}

// Установка значений по умолчанию, если настройки отсутствуют
$defaultSettings = [
    'site_name' => 'Moreon Fitness',
    'site_description' => 'Фитнес-центр премиум класса',
    'contact_email' => 'info@moreonfitness.com',
    'contact_phone' => '+7 (800) 123-45-67',
    'address' => 'г. Москва, ул. Примерная, д. 123',
    'working_hours' => 'Пн-Пт: 7:00-23:00, Сб-Вс: 9:00-22:00',
    'facebook_url' => 'https://facebook.com/',
    'instagram_url' => 'https://instagram.com/',
    'vk_url' => 'https://vk.com/',
    'youtube_url' => 'https://youtube.com/',
    'site_logo' => 'assets/img/logo.png',
    'enable_online_booking' => 1,
    'booking_advance_days' => 7,
    'cancellation_hours' => 24,
    'maintenance_mode' => 0,
    'maintenance_message' => 'Сайт находится на техническом обслуживании. Пожалуйста, зайдите позже.'
];

foreach ($defaultSettings as $key => $value) {
    if (!isset($settings[$key])) {
        $settings[$key] = $value;
    }
}

$pageTitle = 'Общие настройки';
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Управление настройками сайта</h1>
            </div>

            <?php if (isset($_GET['success']) && $_GET['success'] === 'saved'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    Настройки успешно сохранены.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Закрыть"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php 
                    switch ($_GET['error']) {
                        case 'save':
                            echo 'Ошибка при сохранении настроек.';
                            break;
                        default:
                            echo 'Произошла ошибка.';
                    }
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Закрыть"></button>
                </div>
            <?php endif; ?>

            <form action="settings.php" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="save_settings">
                
                <!-- Вкладки настроек -->
                <ul class="nav nav-tabs mb-3" id="settingsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab" aria-controls="general" aria-selected="true">Общие</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab" aria-controls="contact" aria-selected="false">Контакты</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="social-tab" data-bs-toggle="tab" data-bs-target="#social" type="button" role="tab" aria-controls="social" aria-selected="false">Соцсети</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="booking-tab" data-bs-toggle="tab" data-bs-target="#booking" type="button" role="tab" aria-controls="booking" aria-selected="false">Бронирование</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="system-tab" data-bs-toggle="tab" data-bs-target="#system" type="button" role="tab" aria-controls="system" aria-selected="false">Система</button>
                    </li>
                </ul>
                
                <div class="tab-content" id="settingsTabsContent">
                    <!-- Общие настройки -->
                    <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-cog me-1"></i>
                                Общие настройки сайта
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="site_name" class="form-label">Название сайта <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="site_name" name="site_name" value="<?= htmlspecialchars($settings['site_name']) ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="site_description" class="form-label">Описание сайта</label>
                                    <textarea class="form-control" id="site_description" name="site_description" rows="3"><?= htmlspecialchars($settings['site_description']) ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="site_logo" class="form-label">Логотип сайта</label>
                                    <input type="file" class="form-control" id="site_logo" name="site_logo" accept=".jpg,.jpeg,.png,.svg">
                                    <?php if (!empty($settings['site_logo'])): ?>
                                    <div class="mt-2">
                                        <label>Текущий логотип:</label>
                                        <img src="../<?= htmlspecialchars($settings['site_logo']) ?>" alt="Логотип" class="img-thumbnail" style="max-height: 100px;">
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Контактная информация -->
                    <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-address-book me-1"></i>
                                Контактная информация
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="contact_email" class="form-label">Email для связи</label>
                                    <input type="email" class="form-control" id="contact_email" name="contact_email" value="<?= htmlspecialchars($settings['contact_email']) ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="contact_phone" class="form-label">Телефон для связи</label>
                                    <input type="text" class="form-control" id="contact_phone" name="contact_phone" value="<?= htmlspecialchars($settings['contact_phone']) ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="address" class="form-label">Адрес</label>
                                    <textarea class="form-control" id="address" name="address" rows="2"><?= htmlspecialchars($settings['address']) ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="working_hours" class="form-label">Часы работы</label>
                                    <textarea class="form-control" id="working_hours" name="working_hours" rows="2"><?= htmlspecialchars($settings['working_hours']) ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Социальные сети -->
                    <div class="tab-pane fade" id="social" role="tabpanel" aria-labelledby="social-tab">
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-share-alt me-1"></i>
                                Социальные сети
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="facebook_url" class="form-label">Facebook URL</label>
                                    <input type="url" class="form-control" id="facebook_url" name="facebook_url" value="<?= htmlspecialchars($settings['facebook_url']) ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="instagram_url" class="form-label">Instagram URL</label>
                                    <input type="url" class="form-control" id="instagram_url" name="instagram_url" value="<?= htmlspecialchars($settings['instagram_url']) ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="vk_url" class="form-label">ВКонтакте URL</label>
                                    <input type="url" class="form-control" id="vk_url" name="vk_url" value="<?= htmlspecialchars($settings['vk_url']) ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="youtube_url" class="form-label">YouTube URL</label>
                                    <input type="url" class="form-control" id="youtube_url" name="youtube_url" value="<?= htmlspecialchars($settings['youtube_url']) ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Настройки бронирования -->
                    <div class="tab-pane fade" id="booking" role="tabpanel" aria-labelledby="booking-tab">
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-calendar-alt me-1"></i>
                                Настройки бронирования
                            </div>
                            <div class="card-body">
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="enable_online_booking" name="enable_online_booking" value="1" <?= $settings['enable_online_booking'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="enable_online_booking">Разрешить онлайн-бронирование</label>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="booking_advance_days" class="form-label">Максимальное количество дней для предварительного бронирования</label>
                                    <input type="number" class="form-control" id="booking_advance_days" name="booking_advance_days" min="1" max="90" value="<?= (int)$settings['booking_advance_days'] ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="cancellation_hours" class="form-label">Время для отмены бронирования (часов)</label>
                                    <input type="number" class="form-control" id="cancellation_hours" name="cancellation_hours" min="0" max="72" value="<?= (int)$settings['cancellation_hours'] ?>">
                                    <small class="text-muted">За сколько часов до начала тренировки пользователь может отменить бронирование</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Системные настройки -->
                    <div class="tab-pane fade" id="system" role="tabpanel" aria-labelledby="system-tab">
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-server me-1"></i>
                                Системные настройки
                            </div>
                            <div class="card-body">
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="maintenance_mode" name="maintenance_mode" value="1" <?= $settings['maintenance_mode'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="maintenance_mode">Режим технического обслуживания</label>
                                    <small class="form-text text-danger d-block">Внимание! Включение этого режима сделает сайт недоступным для обычных пользователей.</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="maintenance_message" class="form-label">Сообщение режима обслуживания</label>
                                    <textarea class="form-control" id="maintenance_message" name="maintenance_message" rows="3"><?= htmlspecialchars($settings['maintenance_message']) ?></textarea>
                                </div>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Системные настройки влияют на работу всего сайта. Пожалуйста, будьте внимательны при их изменении.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-end mt-3 mb-5">
                    <button type="reset" class="btn btn-secondary me-2">Отменить изменения</button>
                    <button type="submit" class="btn btn-primary">Сохранить настройки</button>
                </div>
            </form>
        </main>
    </div>
</div>

<script>
// Валидация формы
(function() {
    'use strict';
    
    var forms = document.querySelectorAll('.needs-validation');
    
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });
})();
</script>

<?php include 'includes/footer.php'; ?> 