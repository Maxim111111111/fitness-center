<?php
require_once 'database/config.php';

// Проверяем, что ID тренера передан в параметрах URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: coach.php');
    exit;
}

$trainerId = (int)$_GET['id'];

// Получаем информацию о тренере
$stmt = $pdo->prepare("
    SELECT 
        t.id, t.experience_years, t.bio, t.photo_url, t.achievements,
        u.first_name, u.last_name, u.phone, u.email
    FROM trainers t
    JOIN users u ON t.user_id = u.id
    WHERE t.id = ? AND t.is_active = TRUE
");
$stmt->execute([$trainerId]);
$trainer = $stmt->fetch();

// Если тренер не найден, перенаправляем на страницу со всеми тренерами
if (!$trainer) {
    header('Location: coach.php');
    exit;
}

// Получаем специализации тренера
$stmt = $pdo->prepare("
    SELECT s.name
    FROM trainer_specializations ts
    JOIN specializations s ON ts.specialization_id = s.id
    WHERE ts.trainer_id = ?
");
$stmt->execute([$trainerId]);
$specializations = $stmt->fetchAll();

// Получаем образование тренера
$stmt = $pdo->prepare("
    SELECT institution, degree, field_of_study, start_date, end_date
    FROM trainer_education
    WHERE trainer_id = ?
");
$stmt->execute([$trainerId]);
$educations = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8" />
    <link
        href="https://fonts.googleapis.com/css2?family=Dela+Gothic+One&family=Montserrat:wght@400;500;600;700;800&display=swap"
        rel="stylesheet" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $trainer['first_name'] . ' ' . $trainer['last_name']; ?> | Moreon Fitness</title>
    <link rel="stylesheet" href="style/reset.css" />
    <link rel="stylesheet" href="style/style.css" />
    <link rel="stylesheet" href="style/coach.css" />
</head>

<body>
    <?php include 'header.php'; ?>
    <main class="coach">
        <section class="coach-details">
            <div class="coach-details__background">
                <div class="coach-details__overlay"></div>
                <div class="coach-details__circles">
                    <div class="coach-details__circle coach-details__circle--1"></div>
                    <div class="coach-details__circle coach-details__circle--2"></div>
                </div>
                <div class="container coach-details__container">
                    <div class="coach-details__breadcrumbs">
                        <a href="index.php" class="coach-details__breadcrumb-link">Главная</a> / 
                        <a href="coach.php" class="coach-details__breadcrumb-link">Тренеры</a> /
                        <span class="coach-details__breadcrumb-current"><?php echo $trainer['first_name'] . ' ' . $trainer['last_name']; ?></span>
                    </div>
                    <div class="coach-details__content">
                        <div class="coach-details__left-column">
                            <div class="coach-details__photo">
                                <img src="<?php echo file_exists($trainer['photo_url']) ? $trainer['photo_url'] : 'images/trainers/default.jpg'; ?>" alt="<?php echo $trainer['first_name'] . ' ' . $trainer['last_name']; ?>" class="coach-details__image">
                            </div>
                            <div class="coach-details__tags">
                                <?php foreach ($specializations as $spec): ?>
                                <span class="coach-details__tag">#<?php echo $spec['name']; ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="coach-details__info">
                            <h1 class="coach-details__name"><?php echo $trainer['first_name'] . ' ' . $trainer['last_name']; ?></h1>
                            <p class="coach-details__position">
                                <?php echo ($trainer['experience_years'] > 7) ? 'Мастер тренер' : 'Тренер'; ?>
                                <?php echo ($trainer['experience_years'] > 9) ? ' Премиум' : ''; ?><br>
                                Стаж работы: <?php echo $trainer['experience_years']; ?> лет<br>
                                Телефон: <?php echo $trainer['phone']; ?><br>
                                Email: <?php echo $trainer['email']; ?>
                            </p>

                            <div class="coach-details__block">
                                <h2 class="coach-details__subtitle">Специализация</h2>
                                <p class="coach-details__text">
                                    <?php echo $trainer['bio']; ?>
                                </p>
                            </div>

                            <?php if (!empty($educations)): ?>
                            <div class="coach-details__block">
                                <h2 class="coach-details__subtitle">Образование</h2>
                                <p class="coach-details__text">
                                    <?php foreach ($educations as $education): ?>
                                    - <?php echo $education['institution']; ?>, <?php echo $education['degree']; ?> / <?php echo $education['field_of_study']; ?>
                                    <?php if($education['start_date'] && $education['end_date']): ?>
                                    (<?php echo date('Y', strtotime($education['start_date'])); ?> - <?php echo date('Y', strtotime($education['end_date'])); ?>)
                                    <?php endif; ?><br>
                                    <?php endforeach; ?>
                                </p>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($trainer['achievements'])): ?>
                            <div class="coach-details__block">
                                <h2 class="coach-details__subtitle">Достижения</h2>
                                <p class="coach-details__text">
                                    <?php echo $trainer['achievements']; ?>
                                </p>
                            </div>
                            <?php endif; ?>

                            <div class="coach-details__booking">
                                <div class="coach-details__booking-content">
                                    <h2 class="coach-details__booking-title">индивидуальные занятия тренера</h2>
                                    <p class="coach-details__booking-text">
                                        Индивидуальный подход, составление персональной программы тренировок, 
                                        контроль правильности выполнения упражнений, мотивация на результат.
                                    </p>
                                    <a href="training_session.php?trainer_id=<?php echo $trainer['id']; ?>" class="coach-details__booking-button">Записаться на индивидуальное занятие</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        
    </main>
    <?php include 'footer.php'; ?>
    <script src="js/map.js"></script>
    <script src="js/coach.js"></script>
</body>

</html>