<?php
session_start();
require_once('../database/config.php');

// If user is not logged in, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$pageTitle = 'Доступ запрещен';
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-exclamation-triangle text-danger"></i> Доступ запрещен</h1>
            </div>
            
            <div class="alert alert-danger">
                <h4 class="alert-heading">Недостаточно прав для доступа к странице!</h4>
                <p>У вас нет необходимых прав для просмотра запрашиваемой страницы.</p>
                <hr>
                <p class="mb-0">
                    <?php
                    switch ($_SESSION['user_role']) {
                        case 'trainer':
                            echo 'В роли "Тренер" вы имеете доступ только к управлению записями на тренировки.';
                            break;
                        case 'manager':
                            echo 'В роли "Менеджер" у вас нет доступа к управлению записями на тренировки и настройками сайта.';
                            break;
                        default:
                            echo 'Пожалуйста, обратитесь к администратору для получения необходимых прав доступа.';
                    }
                    ?>
                </p>
            </div>
            
            <div class="d-flex justify-content-center mt-4">
                <?php if ($_SESSION['user_role'] === 'trainer'): ?>
                <a href="training_sessions.php" class="btn btn-primary">
                    <i class="fas fa-heartbeat me-2"></i>Перейти к управлению тренировками
                </a>
                <?php elseif ($_SESSION['user_role'] === 'manager'): ?>
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-tachometer-alt me-2"></i>Вернуться на панель управления
                </a>
                <?php else: ?>
                <a href="../index.php" class="btn btn-primary">
                    <i class="fas fa-home me-2"></i>Вернуться на главную
                </a>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 