<?php
// Include the auth check file
require_once(__DIR__ . '/auth_check.php');
?>
<!-- Sidebar -->
<div class="sidebar">
    <div class="d-flex align-items-center justify-content-center mb-3 px-3">
        
    </div>
    
    <div class="px-3 mb-3">
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border: 1px solid var(--border-color);">
                        
                    </div>
            </div>
            <div class="flex-grow-1 ms-2">
                <h6 class="mb-0 fs-6"><?= htmlspecialchars(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? '')) ?></h6>
                <span class="text-muted small">
                    <?php 
                    $roleLabels = [
                        'admin' => 'Администратор',
                        'manager' => 'Менеджер',
                        'trainer' => 'Тренер',
                        'user' => 'Пользователь'
                    ];
                    echo $roleLabels[$_SESSION['user_role']] ?? 'Гость';
                    ?>
                </span>
            </div>
        </div>
    </div>

    <nav>
        <ul>
            <?php if (hasPermission('dashboard')): ?>
            <li>
                <a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Панель управления</span>
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (hasPermission('users')): ?>
            <li>
                <a href="users.php" class="<?= basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : '' ?>">
                    <i class="fas fa-users"></i>
                    <span>Пользователи</span>
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (hasPermission('trainers')): ?>
            <li>
                <a href="trainers.php" class="<?= basename($_SERVER['PHP_SELF']) === 'trainers.php' ? 'active' : '' ?>">
                    <i class="fas fa-user-tie"></i>
                    <span>Тренеры</span>
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (hasPermission('services')): ?>
            <li>
                <a href="services.php" class="<?= basename($_SERVER['PHP_SELF']) === 'services.php' ? 'active' : '' ?>">
                    <i class="fas fa-dumbbell"></i>
                    <span>Услуги</span>
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (hasPermission('subscriptions')): ?>
            <li>
                <a href="subscriptions.php" class="<?= basename($_SERVER['PHP_SELF']) === 'subscriptions.php' ? 'active' : '' ?>">
                    <i class="fas fa-id-card"></i>
                    <span>Абонементы</span>
                </a>
            </li>
            <?php endif; ?>
            
            
            
            <?php if (hasPermission('training_sessions')): ?>
            <li>
                <a href="training_sessions.php" class="<?= basename($_SERVER['PHP_SELF']) === 'training_sessions.php' ? 'active' : '' ?>">
                    <i class="fas fa-heartbeat"></i>
                    <span>Тренировки</span>
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (hasPermission('reviews')): ?>
            <li>
                <a href="reviews.php" class="<?= basename($_SERVER['PHP_SELF']) === 'reviews.php' ? 'active' : '' ?>">
                    <i class="fas fa-star"></i>
                    <span>Отзывы</span>
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (hasPermission('statistics')): ?>
            <li>
                <a href="statistics.php" class="<?= basename($_SERVER['PHP_SELF']) === 'statistics.php' ? 'active' : '' ?>">
                    <i class="fas fa-chart-line"></i>
                    <span>Статистика</span>
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (hasPermission('settings')): ?>
            <li>
                <a href="settings.php" class="<?= basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : '' ?>">
                    <i class="fas fa-cog"></i>
                    <span>Настройки</span>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>
    
    <div class="mt-auto p-2">
        <div class="sidebar-footer text-center">
            <small class="text-muted d-block mb-1">© <?= date('Y') ?> Moreon Fitness</small>
        </div>
    </div>
</div>
<!-- End Sidebar -->

<!-- JavaScript для работы бокового меню на мобильных устройствах -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });
    }
    
    // Закрывать меню при клике вне его на мобильных устройствах
    document.addEventListener('click', function(event) {
        if (window.innerWidth < 768 && 
            !sidebar.contains(event.target) && 
            event.target !== sidebarToggle && 
            !sidebarToggle.contains(event.target) &&
            sidebar.classList.contains('show')) {
            sidebar.classList.remove('show');
        }
    });
});
</script> 