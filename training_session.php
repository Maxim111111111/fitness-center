<?php
// Start session
session_start();
require_once('database/config.php');

// Check if user is logged in
$isLoggedIn = isLoggedIn();
$userData = null;

// If logged in, get user data
if ($isLoggedIn) {
    try {
        // Get user data
        $userId = $_SESSION['user_id'];
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $userData = $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error fetching user data: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Dela+Gothic+One&family=Montserrat:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    />
    <link rel="stylesheet" href="style/reset.css" />
    <link rel="stylesheet" href="style/style.css" />
    <link rel="stylesheet" href="style/coach.css" />
    <link rel="stylesheet" href="style/services.css" />
    <title>Запись на тренировку - Moreon Fitness</title>
</head>

<body>
    <?php include 'header.php'; ?>
    <main>
    <section class="services-hero">
            <div class="services-container container">
                <div class="nav-buttons">
                    <a href="index.php" class="nav-button">Главная</a>
                    <span class="nav-divider">/</span>
                    <a href="training_session.php" class="nav-button nav-button-active">Запись на тренировку</a>
                </div>
                <div class="services-content">
                    <h1 class="services-title">Запись на тренировку</h1>
                    <p class="services-description">
                        Занятия фитнесом – это микс эффективных упражнений, мотивирующего влияния тренера, духа соревнований и общения с единомышленниками. Члены клуба Мореон Фитнес получают доступ к тренажерному залу, бассейнам, скалодрому и 40 видам групповых занятий для разного уровня подготовки и возраста – от начинающих до продвинутых спортсменов, для детей и взрослых.
                    </p>
                    <a href="#training-form" class="services-button">Записаться</a>
                </div>
            </div>
        </section>
    </main>
    <div id="training-form" class="training-form">
        <div class="container">
          <div class="training-form__wrapper">
            <?php if (!$isLoggedIn): ?>
            <div class="training-form__login-required">
              <h2 class="training-form__title">Требуется авторизация</h2>
              <p class="training-form__subtitle">
                Для записи на тренировку необходимо авторизоваться в системе
              </p>
              <div class="training-form__actions">
                <a href="login.php" class="training-form__button">Войти в аккаунт</a>
                <a href="register.php" class="training-form__link">Зарегистрироваться</a>
              </div>
            </div>
            <?php else: ?>
            <div class="training-form__content">
              <h2 class="training-form__title">Запись на тренировку</h2>
              <p class="training-form__subtitle">
                Выберите дату, время и конкретного тренера для вашей тренировки
              </p>
              <form id="trainingForm" class="training-form__form">
                <div class="training-form__row">
                  <div class="training-form__field">
                    <input
                      type="text"
                      id="name"
                      name="name"
                      class="training-form__input"
                      placeholder="Ваше имя"
                      value="<?php echo htmlspecialchars($userData['first_name'] ?? ''); ?>"
                      required
                    />
                  </div>
                  <div class="training-form__field">
                    <input
                      type="tel"
                      id="phone"
                      name="phone"
                      class="training-form__input"
                      placeholder="Ваш телефон"
                      value="<?php echo htmlspecialchars($userData['phone'] ?? ''); ?>"
                      required
                    />
                  </div>
                </div>
                <div class="training-form__row">
                  <div class="training-form__field">
                    <input
                      type="email"
                      id="email"
                      name="email"
                      class="training-form__input"
                      placeholder="Ваш email"
                      value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>"
                      required
                    />
                  </div>
                  <div class="training-form__field select-wrapper">
                    <select
                      id="training-type"
                      name="training-type"
                      class="training-form__input training-form__select"
                      required
                    >
                      <option value="" disabled selected>
                        Выберите тип тренировки
                      </option>
                      <option value="personal">Персональная тренировка</option>
                      <option value="group">Групповая тренировка</option>
                      <option value="pool">Тренировка в бассейне</option>
                      <option value="gym">Тренажерный зал</option>
                    </select>
                  </div>
                </div>
                <div class="training-form__row">
                  <div class="training-form__field">
                    <input
                      type="date"
                      id="date"
                      name="date"
                      class="training-form__input"
                      required
                    />
                  </div>
                  <div class="training-form__field select-wrapper">
                    <select
                      id="time"
                      name="time"
                      class="training-form__input training-form__select"
                      required
                      disabled
                    >
                      <option value="" disabled selected>Сначала выберите дату</option>
                    </select>
                  </div>
                </div>
                <div class="training-form__row">
                  <div class="training-form__field select-wrapper">
                    <select
                      id="trainer"
                      name="trainer"
                      class="training-form__input training-form__select"
                      required
                    >
                      <option value="" disabled selected>Выберите тренера</option>
                    </select>
                  </div>
                </div>
                <div class="training-form__row">
                  <div class="training-form__field">
                    <textarea
                      id="comment"
                      name="comment"
                      class="training-form__input training-form__textarea"
                      placeholder="Ваш комментарий (необязательно)"
                    ></textarea>
                  </div>
                </div>
                <div class="training-form__row training-form__row--center">
                  <button type="submit" class="training-form__button">
                    Записаться на тренировку
                  </button>
                </div>
              </form>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <section class="contacts">
        <div class="container">
          <div class="contacts__wrapper">
            <div class="contacts__content">
              <h2 class="contacts__title">Контакты</h2>
              <div class="contacts__info">
                <div class="contacts__item">
                  <span class="contacts__label">Адрес:</span>
                  <p class="contacts__text">
                    г. Москва м. Ясенево, ул. Голубинская,<br />д. 16
                  </p>
                </div>
                <div class="contacts__item">
                  <span class="contacts__label">Телефон:</span>
                  <a href="tel:+74954816060" class="contacts__text"
                    >+7 (495) 481-60-60</a
                  >
                </div>
                <div class="contacts__item">
                  <span class="contacts__label">E-mail:</span>
                  <a href="mailto:moreon@more-on.ru" class="contacts__text"
                    >moreon@more-on.ru</a
                  >
                </div>
              </div>
            </div>
            <div class="contacts__map">
              <div id="map" class="contacts__map-container"></div>
            </div>
          </div>
        </div>
      </section>
    <?php include 'footer.php'; ?>
    <script src="js/coach.js"></script>
    <?php if ($isLoggedIn): ?>
    <script src="js/training-form.js"></script>
    <?php endif; ?>
    <script src="js/form-styles.js"></script>
    <script src="js/map.js"></script>
</body>

</html>