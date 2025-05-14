<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= $pageTitle === 'Панель управления' ? 'active' : '' ?>" href="index.php">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Главная
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $pageTitle === 'Пользователи' ? 'active' : '' ?>" href="users.php">
                    <i class="fas fa-users me-2"></i>
                    Пользователи
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $pageTitle === 'Тренеры' ? 'active' : '' ?>" href="trainers.php">
                    <i class="fas fa-user-tie me-2"></i>
                    Тренеры
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $pageTitle === 'Услуги' ? 'active' : '' ?>" href="services.php">
                    <i class="fas fa-dumbbell me-2"></i>
                    Услуги
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $pageTitle === 'Записи на тренировки' ? 'active' : '' ?>" href="training_sessions.php">
                    <i class="fas fa-calendar-alt me-2"></i>
                    Записи на тренировки
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $pageTitle === 'Отзывы' ? 'active' : '' ?>" href="reviews.php">
                    <i class="fas fa-comments me-2"></i>
                    Отзывы
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $pageTitle === 'Абонементы' ? 'active' : '' ?>" href="subscriptions.php">
                    <i class="fas fa-id-card me-2"></i>
                    Абонементы
                </a>
            </li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Настройки</span>
            <a class="link-secondary" href="#" aria-label="Добавить новую настройку">
                <i class="fas fa-plus-circle"></i>
            </a>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link <?= $pageTitle === 'Общие настройки' ? 'active' : '' ?>" href="settings.php">
                    <i class="fas fa-cog me-2"></i>
                    Общие настройки
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $pageTitle === 'Статистика' ? 'active' : '' ?>" href="statistics.php">
                    <i class="fas fa-chart-bar me-2"></i>
                    Статистика
                </a>
            </li>
            <?php if ($_SESSION['role'] === 'admin') : ?>
            <li class="nav-item">
                <a class="nav-link <?= $pageTitle === 'Управление правами' ? 'active' : '' ?>" href="permissions.php">
                    <i class="fas fa-lock me-2"></i>
                    Управление правами
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </div>
</nav> 