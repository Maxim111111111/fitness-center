<?php
session_start();
require_once('../database/config.php');


require_once('includes/auth_check.php');

// Check access for schedule
checkAccess('schedule');

// Обработка операций
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            // Добавление нового расписания для тренера
            case 'add_schedule':
                $trainer_id = isset($_POST['trainer_id']) ? intval($_POST['trainer_id']) : 0;
                $day_of_week = isset($_POST['day_of_week']) ? intval($_POST['day_of_week']) : 0;
                $start_time = isset($_POST['start_time']) ? $_POST['start_time'] : '';
                $end_time = isset($_POST['end_time']) ? $_POST['end_time'] : '';
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                
                if ($trainer_id && $day_of_week && $start_time && $end_time) {
                    try {
                        $stmt = $pdo->prepare("
                            INSERT INTO trainer_schedule (trainer_id, day_of_week, start_time, end_time, is_active)
                            VALUES (?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([$trainer_id, $day_of_week, $start_time, $end_time, $is_active]);
                        header('Location: schedule.php?success=add');
                        exit();
                    } catch (PDOException $e) {
                        $error = 'Ошибка при добавлении расписания: ' . $e->getMessage();
                    }
                } else {
                    $error = 'Все поля должны быть заполнены';
                }
                break;
                
            // Редактирование расписания
            case 'edit_schedule':
                $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
                $trainer_id = isset($_POST['trainer_id']) ? intval($_POST['trainer_id']) : 0;
                $day_of_week = isset($_POST['day_of_week']) ? intval($_POST['day_of_week']) : 0;
                $start_time = isset($_POST['start_time']) ? $_POST['start_time'] : '';
                $end_time = isset($_POST['end_time']) ? $_POST['end_time'] : '';
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                
                if ($id && $trainer_id && $day_of_week && $start_time && $end_time) {
                    try {
                        $stmt = $pdo->prepare("
                            UPDATE trainer_schedule 
                            SET trainer_id = ?, day_of_week = ?, start_time = ?, end_time = ?, is_active = ?
                            WHERE id = ?
                        ");
                        $stmt->execute([$trainer_id, $day_of_week, $start_time, $end_time, $is_active, $id]);
                        header('Location: schedule.php?success=edit');
                        exit();
                    } catch (PDOException $e) {
                        $error = 'Ошибка при редактировании расписания: ' . $e->getMessage();
                    }
                } else {
                    $error = 'Все поля должны быть заполнены';
                }
                break;
                
            // Удаление расписания
            case 'delete_schedule':
                $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
                
                if ($id) {
                    try {
                        $stmt = $pdo->prepare("DELETE FROM trainer_schedule WHERE id = ?");
                        $stmt->execute([$id]);
                        header('Location: schedule.php?success=delete');
                        exit();
                    } catch (PDOException $e) {
                        $error = 'Ошибка при удалении расписания: ' . $e->getMessage();
                    }
                } else {
                    $error = 'Некорректный ID расписания';
                }
                break;
        }
    }
}

// Получение списка тренеров для селекта
$trainers_stmt = $pdo->query("
    SELECT t.id, CONCAT(u.first_name, ' ', u.last_name) as trainer_name
    FROM trainers t
    JOIN users u ON t.user_id = u.id
    WHERE t.is_active = 1
    ORDER BY trainer_name
");
$trainers = $trainers_stmt->fetchAll();

// Фильтры
$trainer_filter = isset($_GET['trainer']) ? intval($_GET['trainer']) : 0;
$day_filter = isset($_GET['day']) ? intval($_GET['day']) : 0;

// Параметры фильтрации
$where_conditions = [];
$params = [];

if ($trainer_filter) {
    $where_conditions[] = "ts.trainer_id = ?";
    $params[] = $trainer_filter;
}

if ($day_filter) {
    $where_conditions[] = "ts.day_of_week = ?";
    $params[] = $day_filter;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Получение данных расписания
$stmt = $pdo->prepare("
    SELECT 
        ts.id,
        ts.day_of_week,
        ts.start_time,
        ts.end_time,
        ts.is_active,
        CONCAT(u.first_name, ' ', u.last_name) as trainer_name,
        t.id as trainer_id
    FROM trainer_schedule ts
    JOIN trainers t ON ts.trainer_id = t.id
    JOIN users u ON t.user_id = u.id
    $where_clause
    ORDER BY ts.day_of_week ASC, ts.start_time ASC
");
$stmt->execute($params);
$schedules = $stmt->fetchAll();

// Названия дней недели
$days_of_week = [
    1 => 'Понедельник',
    2 => 'Вторник',
    3 => 'Среда',
    4 => 'Четверг',
    5 => 'Пятница',
    6 => 'Суббота',
    7 => 'Воскресенье'
];

$pageTitle = 'Расписание тренеров';
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Расписание тренеров</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
                        <i class="fas fa-plus me-1"></i> Добавить расписание
                    </button>
                </div>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                        switch ($_GET['success']) {
                            case 'add': echo 'Расписание успешно добавлено.'; break;
                            case 'edit': echo 'Расписание успешно обновлено.'; break;
                            case 'delete': echo 'Расписание успешно удалено.'; break;
                        }
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <!-- Фильтры -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-filter me-1"></i> Фильтры
                </div>
                <div class="card-body">
                    <form method="get" action="schedule.php" class="row g-3">
                        <div class="col-md-4">
                            <label for="trainer" class="form-label">Тренер</label>
                            <select name="trainer" id="trainer" class="form-select">
                                <option value="">Все тренеры</option>
                                <?php foreach ($trainers as $trainer): ?>
                                    <option value="<?php echo $trainer['id']; ?>" <?php if ($trainer_filter == $trainer['id']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($trainer['trainer_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="day" class="form-label">День недели</label>
                            <select name="day" id="day" class="form-select">
                                <option value="">Все дни</option>
                                <?php foreach ($days_of_week as $day_id => $day_name): ?>
                                    <option value="<?php echo $day_id; ?>" <?php if ($day_filter == $day_id) echo 'selected'; ?>>
                                        <?php echo $day_name; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">Применить</button>
                            <a href="schedule.php" class="btn btn-outline-secondary">Сбросить</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Таблица расписания -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-calendar-alt me-1"></i> Расписание тренеров
                </div>
                <div class="card-body">
                    <?php if (count($schedules) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Тренер</th>
                                        <th>День недели</th>
                                        <th>Время начала</th>
                                        <th>Время окончания</th>
                                        <th>Статус</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($schedules as $schedule): ?>
                                        <tr>
                                            <td><?php echo $schedule['id']; ?></td>
                                            <td><?php echo htmlspecialchars($schedule['trainer_name']); ?></td>
                                            <td><?php echo $days_of_week[$schedule['day_of_week']]; ?></td>
                                            <td><?php echo substr($schedule['start_time'], 0, 5); ?></td>
                                            <td><?php echo substr($schedule['end_time'], 0, 5); ?></td>
                                            <td>
                                                <?php if ($schedule['is_active']): ?>
                                                    <span class="badge bg-success">Активно</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Неактивно</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-primary edit-schedule-btn" 
                                                        data-id="<?php echo $schedule['id']; ?>"
                                                        data-trainer-id="<?php echo $schedule['trainer_id']; ?>"
                                                        data-day="<?php echo $schedule['day_of_week']; ?>"
                                                        data-start="<?php echo $schedule['start_time']; ?>"
                                                        data-end="<?php echo $schedule['end_time']; ?>"
                                                        data-active="<?php echo $schedule['is_active']; ?>"
                                                        data-bs-toggle="modal" data-bs-target="#editScheduleModal">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger delete-schedule-btn"
                                                        data-id="<?php echo $schedule['id']; ?>"
                                                        data-bs-toggle="modal" data-bs-target="#deleteScheduleModal">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            Расписание не найдено. Создайте новое расписание.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Визуализация расписания по дням недели -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-table me-1"></i> Сетка расписания
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th></th>
                                    <?php foreach ($days_of_week as $day_id => $day_name): ?>
                                        <th><?php echo $day_name; ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                // Группировка расписания по тренерам
                                $trainers_schedule = [];
                                foreach ($schedules as $schedule) {
                                    if (!isset($trainers_schedule[$schedule['trainer_id']])) {
                                        $trainers_schedule[$schedule['trainer_id']] = [
                                            'name' => $schedule['trainer_name'],
                                            'schedule' => []
                                        ];
                                    }
                                    if (!isset($trainers_schedule[$schedule['trainer_id']]['schedule'][$schedule['day_of_week']])) {
                                        $trainers_schedule[$schedule['trainer_id']]['schedule'][$schedule['day_of_week']] = [];
                                    }
                                    $trainers_schedule[$schedule['trainer_id']]['schedule'][$schedule['day_of_week']][] = [
                                        'start' => $schedule['start_time'],
                                        'end' => $schedule['end_time'],
                                        'active' => $schedule['is_active']
                                    ];
                                }
                                
                                foreach ($trainers_schedule as $trainer_id => $trainer_data): 
                                ?>
                                    <tr>
                                        <td class="fw-bold"><?php echo htmlspecialchars($trainer_data['name']); ?></td>
                                        <?php for ($day = 1; $day <= 7; $day++): ?>
                                            <td>
                                                <?php 
                                                if (isset($trainer_data['schedule'][$day])) {
                                                    foreach ($trainer_data['schedule'][$day] as $slot) {
                                                        $status_class = $slot['active'] ? 'success' : 'secondary';
                                                        echo '<div class="badge bg-' . $status_class . ' mb-1 d-block">' . 
                                                             substr($slot['start'], 0, 5) . ' - ' . substr($slot['end'], 0, 5) . 
                                                             '</div>';
                                                    }
                                                } else {
                                                    echo '<span class="text-muted">-</span>';
                                                }
                                                ?>
                                            </td>
                                        <?php endfor; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Модальное окно добавления расписания -->
<div class="modal fade" id="addScheduleModal" tabindex="-1" aria-labelledby="addScheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addScheduleModalLabel">Добавить расписание</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="schedule.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_schedule">
                    
                    <div class="mb-3">
                        <label for="add_trainer_id" class="form-label">Тренер</label>
                        <select class="form-select" id="add_trainer_id" name="trainer_id" required>
                            <option value="">Выберите тренера</option>
                            <?php foreach ($trainers as $trainer): ?>
                                <option value="<?php echo $trainer['id']; ?>">
                                    <?php echo htmlspecialchars($trainer['trainer_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add_day_of_week" class="form-label">День недели</label>
                        <select class="form-select" id="add_day_of_week" name="day_of_week" required>
                            <option value="">Выберите день</option>
                            <?php foreach ($days_of_week as $day_id => $day_name): ?>
                                <option value="<?php echo $day_id; ?>"><?php echo $day_name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add_start_time" class="form-label">Время начала</label>
                        <input type="time" class="form-control" id="add_start_time" name="start_time" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add_end_time" class="form-label">Время окончания</label>
                        <input type="time" class="form-control" id="add_end_time" name="end_time" required>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="add_is_active" name="is_active" checked>
                        <label class="form-check-label" for="add_is_active">Активно</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Модальное окно редактирования расписания -->
<div class="modal fade" id="editScheduleModal" tabindex="-1" aria-labelledby="editScheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editScheduleModalLabel">Редактировать расписание</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="schedule.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit_schedule">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="mb-3">
                        <label for="edit_trainer_id" class="form-label">Тренер</label>
                        <select class="form-select" id="edit_trainer_id" name="trainer_id" required>
                            <option value="">Выберите тренера</option>
                            <?php foreach ($trainers as $trainer): ?>
                                <option value="<?php echo $trainer['id']; ?>">
                                    <?php echo htmlspecialchars($trainer['trainer_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_day_of_week" class="form-label">День недели</label>
                        <select class="form-select" id="edit_day_of_week" name="day_of_week" required>
                            <option value="">Выберите день</option>
                            <?php foreach ($days_of_week as $day_id => $day_name): ?>
                                <option value="<?php echo $day_id; ?>"><?php echo $day_name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_start_time" class="form-label">Время начала</label>
                        <input type="time" class="form-control" id="edit_start_time" name="start_time" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_end_time" class="form-label">Время окончания</label>
                        <input type="time" class="form-control" id="edit_end_time" name="end_time" required>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="edit_is_active" name="is_active">
                        <label class="form-check-label" for="edit_is_active">Активно</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Модальное окно удаления расписания -->
<div class="modal fade" id="deleteScheduleModal" tabindex="-1" aria-labelledby="deleteScheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteScheduleModalLabel">Подтверждение удаления</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите удалить это расписание?</p>
            </div>
            <div class="modal-footer">
                <form method="post" action="schedule.php">
                    <input type="hidden" name="action" value="delete_schedule">
                    <input type="hidden" name="id" id="delete_id">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-danger">Удалить</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Обработка кнопок редактирования
    const editButtons = document.querySelectorAll('.edit-schedule-btn');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const trainerId = this.getAttribute('data-trainer-id');
            const day = this.getAttribute('data-day');
            const startTime = this.getAttribute('data-start');
            const endTime = this.getAttribute('data-end');
            const isActive = this.getAttribute('data-active') === '1';
            
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_trainer_id').value = trainerId;
            document.getElementById('edit_day_of_week').value = day;
            document.getElementById('edit_start_time').value = startTime.substring(0, 5);
            document.getElementById('edit_end_time').value = endTime.substring(0, 5);
            document.getElementById('edit_is_active').checked = isActive;
        });
    });
    
    // Обработка кнопок удаления
    const deleteButtons = document.querySelectorAll('.delete-schedule-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            document.getElementById('delete_id').value = id;
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?> 