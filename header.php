<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <link
      href="https://fonts.googleapis.com/css2?family=Dela+Gothic+One&family=Montserrat:wght@400;500;600;700&display=swap"
      rel="stylesheet"
    />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Moreon Fitness-ceter</title>
    <link rel="stylesheet" href="style/style.css" />
    <link rel="stylesheet" href="style/reset.css" />
    <link rel="stylesheet" href="style/tailwind.css" />
    <link rel="stylesheet" href="style/animations.css" />
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"
    />
    <script
      src="https://api-maps.yandex.ru/2.1/?apikey=ваш_API_ключ&lang=ru_RU"
      type="text/javascript"
    ></script>
  </head>
  <body class="page-transition">
    <header>
      <div class="header__top">
        <div class="container">
          <div class="header__top-wrapper">
            <div class="header__top-phone">
              <img src="assets/svg/phone.svg" alt="Phone" />
              <a href="tel:+79181234567" class="header__top-phone-link"
                >+7 (918) 123-45-67</a
              >
            </div>
            <ul class="header__top-social">
              <li class="header__top-social-item">
                <a href="#" class="header__top-social-link">
                  <img src="assets/svg/instagram.svg" alt="Instagram" />
                </a>
              </li>
              <li class="header__top-social-item">
                <a href="#" class="header__top-social-link">
                  <img src="assets/svg/facebook.svg" alt="Facebook" />
                </a>
              </li>
              <li class="header__top-social-item">
                <a href="#" class="header__top-social-link">
                  <img src="assets/svg/telegram.svg" alt="Telegram" />
                </a>
              </li>
              <li class="header__top-social-item">
                <a href="#" class="header__top-social-link">
                  <img src="assets/svg/vk.svg" alt="VK" />
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
      <div class="header__bottom">
        <div class="container">
          <div class="header__bottom-wrapper">
            <a href="./index.php" class="header__logo">
              <img src="assets/svg/logo white.svg" alt="Moreon Fitness logo" />
            </a>
            <div class="header__menu">
              <ul class="header__menu-list">
                <li class="header__menu-item">
                  <a href="./about.php" class="header__menu-link">О нас</a>
                </li>
                <li class="header__menu-item">
                  <a href="./services.php" class="header__menu-link">Услуги</a>
                </li>
                <li class="header__menu-item">
                  <a href="./coach.php" class="header__menu-link">Тренеры</a>
                </li>
                <li class="header__menu-item">
                  <a href="./training_session.php" class="header__menu-link"
                    >Запись на тренеровку</a
                  >
                </li>
                <li class="header__menu-item">
                  <a href="./reviews.php" class="header__menu-link">Отзывы</a>
                </li>
                <li class="header__menu-item">
                  <a href="./contacts.php" class="header__menu-link">Контакты</a>
                </li>
              </ul>
            </div>
            <div class="header__auth">
              <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true): ?>
                <a href="./profile.php" class="header__profile-link">
                  <img src="assets/svg/user-menu.svg" alt="Профиль" class="header__auth-icon">
                </a>
              <?php else: ?>
                <img src="assets/svg/user-menu.svg" alt="Профиль" class="header__auth-icon" id="authIcon">
                <div class="header__auth-dropdown" id="authDropdown">
                  <div class="header__auth-menu">
                    <a href="./login.php" class="header__auth-link">
                      <img src="assets/svg/user.svg" alt="Вход" class="header__auth-link-icon">
                      Вход
                    </a>
                    <a href="./register.php" class="header__auth-link header__auth-link--accent">
                      <img src="assets/svg/user-plus.svg" alt="Регистрация" class="header__auth-link-icon">
                      Регистрация
                    </a>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </header> 
    <script src="js/auth.js"></script>
    <script src="js/animations.js"></script>
    <script src="js/active-menu.js"></script> 