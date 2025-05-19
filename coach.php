<?php
require_once 'database/config.php';

// Получаем список всех тренеров (ограничиваем 8)
$stmt = $pdo->prepare("
    SELECT t.id, t.photo_url, t.experience_years, t.achievements, 
           u.first_name, u.last_name, u.phone
    FROM trainers t
    JOIN users u ON t.user_id = u.id
    WHERE t.is_active = TRUE
    LIMIT 8
");
$stmt->execute();
$trainers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8" />
    <link
        href="https://fonts.googleapis.com/css2?family=Dela+Gothic+One&family=Montserrat:wght@400;500;600;700&display=swap"
        rel="stylesheet" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Тренеры | Moreon Fitness</title>
    <link rel="stylesheet" href="style/reset.css" />
    <link rel="stylesheet" href="style/style.css" />
    <link rel="stylesheet" href="style/services.css" />
    <link rel="stylesheet" href="style/coach.css" />
</head>

<body>
    <?php include 'header.php'; ?>
    <main class="coach">
    <section class="services-hero">
            <div class="services-background-text">
                Mind Body Силовые и функциональные тренировки Кардиограммы и аэробика Тренажерный зал Водные программы Единоборства Танцы Персональный тренинг Йога Фитнес-тестирование Mind Body Силовые и функциональные тренировки Кардиограммы и аэробика Тренажерный зал Водные программы Единоборства Танцы Персональный тренинг Йога Фитнес-тестирование Mind Body Силовые и функциональные тренировки Кардиограммы и аэробика
            </div>
            <div class="services-container container">
                <div class="nav-buttons">
                    <a href="index.php" class="nav-button">Главная</a>
                    <span class="nav-divider">/</span>
                    <a href="coach.php" class="nav-button nav-button-active">Тренеры</a>
                </div>
                <div class="services-content">
                    <h1 class="services-title">Тренеры</h1>
                    <p class="services-description">
                        Занятия фитнесом – это микс эффективных упражнений, мотивирующего влияния тренера, духа соревнований и общения с единомышленниками. Члены клуба Мореон Фитнес получают доступ к тренажерному залу, бассейнам, скалодрому и 40 видам групповых занятий для разного уровня подготовки и возраста – от начинающих до продвинутых спортсменов, для детей и взрослых.
                    </p>
                    <a href="#" class="services-button">Записаться</a>
                </div>
            </div>
        </section>

        <div class="trainers trainers-page">
        <div class="container">
          <h2 class="trainers__title">Тренеры</h2>
          <div class="trainers__grid">
            <?php foreach ($trainers as $trainer): ?>
            <div class="trainers__card">
              <a href="coach-details.php?id=<?php echo $trainer['id']; ?>" class="trainers__card-link">
                <div class="trainers__card-overlay"></div>
                <img
                  src="<?php echo file_exists($trainer['photo_url']) ? $trainer['photo_url'] : 'images/trainers/default.jpg'; ?>"
                  alt="<?php echo $trainer['first_name'] . ' ' . $trainer['last_name']; ?>"
                  class="trainers__card-image"
                />
                <div class="trainers__card-content">
                  <h3 class="trainers__card-name"><?php echo $trainer['first_name'] . ' ' . $trainer['last_name']; ?></h3>
                  <p class="trainers__card-position">
                    <?php echo ($trainer['experience_years'] > 7) ? 'Мастер тренер' : 'Тренер'; ?>
                    <?php echo ($trainer['experience_years'] > 9) ? '. Элит тренер' : ''; ?>
                  </p>
                </div>
              </a>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </main>
    <?php include 'footer.php'; ?>
    <script src="js/map.js"></script>
    <script src="js/services.js"></script>
    <script src="js/coach.js"></script>
</body>

</html>