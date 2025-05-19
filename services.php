<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Dela+Gothic+One&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <title>Услуги | Moreon Fitness</title>
    <link rel="stylesheet" href="style/reset.css">
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="style/services.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <main class="services">
        <section class="services-hero">
            <div class="services-background-text">
                Mind Body Силовые и функциональные тренировки Кардиограммы и аэробика Тренажерный зал Водные программы Единоборства Танцы Персональный тренинг Йога Фитнес-тестирование Mind Body Силовые и функциональные тренировки Кардиограммы и аэробика Тренажерный зал Водные программы Единоборства Танцы Персональный тренинг Йога Фитнес-тестирование Mind Body Силовые и функциональные тренировки Кардиограммы и аэробика
            </div>
            <div class="services-container container">
                <div class="nav-buttons">
                    <a href="index.php" class="nav-button">Главная</a>
                    <span class="nav-divider">/</span>
                    <a href="services.php" class="nav-button nav-button-active">Услуги</a>
                </div>
                <div class="services-content">
                    <h1 class="services-title">Услуги</h1>
                    <p class="services-description">
                        Занятия фитнесом – это микс эффективных упражнений, мотивирующего влияния тренера, духа соревнований и общения с единомышленниками. Члены клуба Мореон Фитнес получают доступ к тренажерному залу, бассейнам, скалодрому и 40 видам групповых занятий для разного уровня подготовки и возраста – от начинающих до продвинутых спортсменов, для детей и взрослых.
                    </p>
                    <a href="#" class="services-button">Записаться</a>
                </div>
            </div>
        </section>
        
        <div class="directions">
        <div class="container">
          <h2 class="directions__title">
            Направления <span class="directions__title-span">занятий</span>
          </h2>
          <div class="directions__grid">
            <div class="directions__card">
              <div class="directions__card-overlay"></div>
              <img
                src="assets/img/directions/mind-body.jpg"
                alt="Mind Body"
                class="directions__card-image"
              />
              <h3 class="directions__card-title">Mind Body</h3>
            </div>
            <div class="directions__card">
              <div class="directions__card-overlay"></div>
              <img
                src="assets/img/directions/gym.jpg"
                alt="Тренажерный зал"
                class="directions__card-image"
              />
              <h3 class="directions__card-title">Тренажерный зал</h3>
            </div>
            <div class="directions__card">
              <div class="directions__card-overlay"></div>
              <img
                src="assets/img/directions/dance.jpg"
                alt="Танцы"
                class="directions__card-image"
              />
              <h3 class="directions__card-title">Танцы</h3>
            </div>
            <div class="directions__card">
              <div class="directions__card-overlay"></div>
              <img
                src="assets/img/directions/fitness-testing.jpg"
                alt="Фитнес-тестирование"
                class="directions__card-image"
              />
              <h3 class="directions__card-title">Фитнес-тестирование</h3>
            </div>
            <div class="directions__card">
              <div class="directions__card-overlay"></div>
              <img
                src="assets/img/directions/strength.jpg"
                alt="Силовые и функциональные тренировки"
                class="directions__card-image"
              />
              <h3 class="directions__card-title">
                Силовые и функциональные тренировки
              </h3>
            </div>
            <div class="directions__card">
              <div class="directions__card-overlay"></div>
              <img
                src="assets/img/directions/water.jpg"
                alt="Водные программы"
                class="directions__card-image"
              />
              <h3 class="directions__card-title">Водные программы</h3>
            </div>
            <div class="directions__card">
              <div class="directions__card-overlay"></div>
              <img
                src="assets/img/directions/personal.jpg"
                alt="Персональный тренинг"
                class="directions__card-image"
              />
              <h3 class="directions__card-title">Персональный тренинг</h3>
            </div>
            <div class="directions__card">
              <div class="directions__card-overlay"></div>
              <img
                src="assets/img/directions/cardio.jpg"
                alt="Кардиограммы и аэробика"
                class="directions__card-image"
              />
              <h3 class="directions__card-title">Кардиограммы и аэробика</h3>
            </div>
            <div class="directions__card">
              <div class="directions__card-overlay"></div>
              <img
                src="assets/img/directions/martial-arts.jpg"
                alt="Единоборства"
                class="directions__card-image"
              />
              <h3 class="directions__card-title">Единоборства</h3>
            </div>
            <div class="directions__card">
              <div class="directions__card-overlay"></div>
              <img
                src="assets/img/directions/yoga.jpg"
                alt="Йога"
                class="directions__card-image"
              />
              <h3 class="directions__card-title">Йога</h3>
            </div>
          </div>
        </div>
      </div>
    </main>
    <?php include 'footer.php'; ?>
    <script src="js/services.js"></script>
    <script src="js/map.js"></script>
</body>
</html>