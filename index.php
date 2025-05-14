<?php
session_start();
require_once 'database/config.php';
require_once 'includes/settings.php';

// Проверка режима обслуживания
if (is_maintenance_mode() && (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin')) {
    // Отображаем страницу режима обслуживания
    include 'maintenance.php';
    exit();
}
?>
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
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"
    />
    <script
      src="https://api-maps.yandex.ru/2.1/?apikey=ваш_API_ключ&lang=ru_RU"
      type="text/javascript"
    ></script>
  </head>
  <body>
    <?php include 'header.php'; ?>
    <main>
      <div class="main__banner">
        <div class="container">
          <div class="main__banner-wrapper">
            <div class="main__banner-content">
              <h1 class="main__banner-title">
                Создай тело мечты вместе
                <span class="main__banner-title-span">с Moreon Fitness</span>
              </h1>
              <div class="main__baner-adventages">
                <div class="main__baner-adventage">
                  <img
                    src="assets/svg/small-rect.svg"
                    alt="small-rect"
                    class="small-rect"
                  />
                  <p class="main__baner-adventage-text">Групповые тренировки</p>
                </div>
                <div class="main__baner-adventage">
                  <img
                    src="assets/svg/small-rect.svg"
                    alt="small-rect"
                    class="small-rect"
                  />
                  <p class="main__baner-adventage-text">
                    Индивидуальные тренировки для каждого клиента
                  </p>
                </div>
                <div class="main__baner-adventage">
                  <img
                    src="assets/svg/small-rect.svg"
                    alt="small-rect"
                    class="small-rect"
                  />
                  <p class="main__baner-adventage-text">
                    Все самые продвинутые программы 2025 года
                  </p>
                </div>
              </div>
              <div class="main__banner-buttons">
                <button type="submit" class="training-form__button">
                  Узнать подробнее
                </button>
                <button
                  type="button"
                  class="main__banner-button main__banner-button--secondary"
                >
                  <span class="main__banner-button-text"
                    >Записаться на тренировку</span
                  >
                </button>
              </div>
            </div>
            <div class="adventage__cards">
              <div class="adventage__card">
                <div class="adventage__card-wrapper">
                  <p class="adventage__card-title">500 000 м2</p>
                  <p class="adventage__card-text">Площадь зала</p>
                </div>
              </div>
              <div class="adventage__card">
                <div class="adventage__card-wrapper">
                  <p class="adventage__card-title">100 500</p>
                  <p class="adventage__card-text">Тренажеров VIP уровня</p>
                </div>
              </div>
              <div class="adventage__card">
                <div class="adventage__card-wrapper">
                  <p class="adventage__card-text">Бассейны и СПА центры</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="promotions">
        <div class="container">
          <h2 class="promotions__title">Акции</h2>

          <div class="promotions__tabs">
            <div class="promotions__tab active" data-tab="fitness">Фитнес</div>
            <div class="promotions__tab" data-tab="spa">СПА</div>
            <div class="promotions__tab" data-tab="aquapark">Аквапарк</div>
            <div class="promotions__tab" data-tab="therms">Термы</div>
            <div class="promotions__tab" data-tab="holidays">Праздникик</div>
            <div class="promotions__tab" data-tab="bowling">Боулинг</div>
          </div>

          <div class="promotions__slider">
            <!-- Slider main container -->
            <div class="swiper promotions__swiper">
              <!-- Additional required wrapper -->
              <div class="swiper-wrapper">
                <!-- Group 1 -->
                <div class="swiper-slide promotions__slide">
                  <div class="loading-overlay"></div>
                  <img
                    src="assets/img/fitness-promo-1.jpg"
                    alt="Fitness promotion 1"
                  />
                </div>
                <div class="swiper-slide promotions__slide">
                  <div class="loading-overlay"></div>
                  <img
                    src="assets/img/fitness-promo-2.jpg"
                    alt="Fitness promotion 2"
                  />
                </div>
                <div class="swiper-slide promotions__slide">
                  <div class="loading-overlay"></div>
                  <img
                    src="assets/img/fitness-promo-3.jpg"
                    alt="Fitness promotion 3"
                  />
                </div>
                <!-- Group 2 -->
                <div class="swiper-slide promotions__slide">
                  <div class="loading-overlay"></div>
                  <img
                    src="assets/img/fitness-promo-2.jpg"
                    alt="Fitness promotion 2"
                  />
                </div>
                <div class="swiper-slide promotions__slide">
                  <div class="loading-overlay"></div>
                  <img
                    src="assets/img/fitness-promo-3.jpg"
                    alt="Fitness promotion 3"
                  />
                </div>
                <div class="swiper-slide promotions__slide">
                  <div class="loading-overlay"></div>
                  <img
                    src="assets/img/fitness-promo-1.jpg"
                    alt="Fitness promotion 1"
                  />
                </div>
                <!-- Group 3 -->
                <div class="swiper-slide promotions__slide">
                  <div class="loading-overlay"></div>
                  <img
                    src="assets/img/fitness-promo-3.jpg"
                    alt="Fitness promotion 3"
                  />
                </div>
                <div class="swiper-slide promotions__slide">
                  <div class="loading-overlay"></div>
                  <img
                    src="assets/img/fitness-promo-1.jpg"
                    alt="Fitness promotion 1"
                  />
                </div>
                <div class="swiper-slide promotions__slide">
                  <div class="loading-overlay"></div>
                  <img
                    src="assets/img/fitness-promo-2.jpg"
                    alt="Fitness promotion 2"
                  />
                </div>
                <!-- Group 4 -->
                <div class="swiper-slide promotions__slide">
                  <div class="loading-overlay"></div>
                  <img
                    src="assets/img/fitness-promo-1.jpg"
                    alt="Fitness promotion 1"
                  />
                </div>
                <div class="swiper-slide promotions__slide">
                  <div class="loading-overlay"></div>
                  <img
                    src="assets/img/fitness-promo-2.jpg"
                    alt="Fitness promotion 2"
                  />
                </div>
                <div class="swiper-slide promotions__slide">
                  <div class="loading-overlay"></div>
                  <img
                    src="assets/img/fitness-promo-3.jpg"
                    alt="Fitness promotion 3"
                  />
                </div>
              </div>
              <!-- Add Pagination -->
              <div class="swiper-pagination promotions__pagination"></div>
            </div>

            <!-- Navigation buttons -->
            <div class="promotions__button-prev">
              <img
                src="assets/svg/Left.svg"
                alt="Previous"
                class="arrow"
                width="50"
                height="50"
              />
            </div>

            <div class="promotions__button-next">
              <img
                src="assets/svg/Left.svg"
                alt="Next"
                class="arrow"
                width="50"
                height="50"
              />
            </div>
          </div>
        </div>
      </div>
      <div class="about-company">
        <div class="container">
          <h2 class="about-company__title">
            <span class="about-company__title-span">Moreon Fitness</span> Яснево
          </h2>
          <div class="about-company__content">
            <p class="about-company__content-text">
              Мореон Фитнес – семейный премиум фитнес-клуб с бассейном, 40
              видами групповых программ, детским клубом, школой единоборств и
              скалодромом.
            </p>
            <p class="about-company__content-text">
              Оборудование тренажерного зала поставляет эксклюзивный партнер
              Олимпийских игр - Technogym, а тренировки проводят призеры
              российских и международных соревнований.
            </p>
            <p class="about-company__content-text">
              Групповые программы учитывают особенности нагрузок для женщин и
              мужчин, инструкторы тренируют детей и подростков, проводят занятия
              для пожилых людей. На сайте Мореон Фитнес описаны все программы,
              их польза, преимущества и расписание.
            </p>
            <p class="about-company__content-text">
              Спортивный бассейн фитнес-центра «Мореон» - база для тренировок и
              соревнований школы спортивного плавания, площадка для водных
              групповых программ, персональных тренировок и свободного плавания.
              В бассейне «Тоник» учатся плавать самые маленькие клиенты –
              посетители студии грудничкового плавания.
            </p>
            <p class="about-company__content-text">
              Мы хотим, чтобы занятия спортом были не только приятными и
              полезными, но и удобными. Перед тренировкой каждый клиент получает
              электронный ключ от индивидуальной ячейки хранения, халат и
              полотенце. В раздевалках работают солярии. Разрядиться, расслабить
              мускулы, отдохнуть душой и телом можно в релакс-зонах «Мореон» -
              банном комплексе «Термы» и пространстве Мореон SPA.
            </p>
            <p class="about-company__content-text-last">
              Мореон Фитнес – лучший фитнес-центр в Ясенево, а также для районов
              Теплый Стан, Беляево, Коньково и Коммунарка и Юго-Западная. Мы
              находимся рядом с метро «Ясенево», с удобным подъездом со стороны
              МКАДа и ТТК (Севастопольский пр-т). Для членов клуба действует
              бесплатная парковка.
            </p>
          </div>
        </div>
      </div>
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
      <div class="trainers">
        <div class="container">
          <h2 class="trainers__title">Тренеры</h2>
          <div class="trainers__grid">
            <div class="trainers__card">
              <div class="trainers__card-overlay"></div>
              <img
                src="assets/img/trainers/trainer-1.jpg"
                alt="Иван Иванов"
                class="trainers__card-image"
              />
              <div class="trainers__card-content">
                <h3 class="trainers__card-name">Иван Иванов</h3>
                <p class="trainers__card-position">
                  Мастер тренер. Элит тренер
                </p>
              </div>
            </div>
            <div class="trainers__card">
              <div class="trainers__card-overlay"></div>
              <img
                src="assets/img/trainers/trainer-2.jpg"
                alt="Иван Иванов"
                class="trainers__card-image"
              />
              <div class="trainers__card-content">
                <h3 class="trainers__card-name">Иван Иванов</h3>
                <p class="trainers__card-position">
                  Мастер тренер. Элит тренер
                </p>
              </div>
            </div>
            <div class="trainers__card">
              <div class="trainers__card-overlay"></div>
              <img
                src="assets/img/trainers/trainer-3.jpg"
                alt="Иван Иванов"
                class="trainers__card-image"
              />
              <div class="trainers__card-content">
                <h3 class="trainers__card-name">Иван Иванов</h3>
                <p class="trainers__card-position">
                  Мастер тренер. Элит тренер
                </p>
              </div>
            </div>
            <div class="trainers__card">
              <div class="trainers__card-overlay"></div>
              <img
                src="assets/img/trainers/trainer-4.jpg"
                alt="Иван Иванов"
                class="trainers__card-image"
              />
              <div class="trainers__card-content">
                <h3 class="trainers__card-name">Иван Иванов</h3>
                <p class="trainers__card-position">
                  Мастер тренер. Элит тренер
                </p>
              </div>
            </div>
            <div class="trainers__card">
              <div class="trainers__card-overlay"></div>
              <img
                src="assets/img/trainers/trainer-5.jpg"
                alt="Иван Иванов"
                class="trainers__card-image"
              />
              <div class="trainers__card-content">
                <h3 class="trainers__card-name">Иван Иванов</h3>
                <p class="trainers__card-position">Мастер тренер</p>
              </div>
            </div>
            <div class="trainers__card">
              <div class="trainers__card-overlay"></div>
              <img
                src="assets/img/trainers/trainer-6.jpg"
                alt="Иван Иванов"
                class="trainers__card-image"
              />
              <div class="trainers__card-content">
                <h3 class="trainers__card-name">Иван Иванов</h3>
                <p class="trainers__card-position">Мастер тренер</p>
              </div>
            </div>
            <div class="trainers__card">
              <div class="trainers__card-overlay"></div>
              <img
                src="assets/img/trainers/trainer-7.jpg"
                alt="Иван Иванов"
                class="trainers__card-image"
              />
              <div class="trainers__card-content">
                <h3 class="trainers__card-name">Иван Иванов</h3>
                <p class="trainers__card-position">Мастер тренер</p>
              </div>
            </div>
            <div class="trainers__card">
              <div class="trainers__card-overlay"></div>
              <img
                src="assets/img/trainers/trainer-8.jpg"
                alt="Иван Иванов"
                class="trainers__card-image"
              />
              <div class="trainers__card-content">
                <h3 class="trainers__card-name">Иван Иванов</h3>
                <p class="trainers__card-position">Мастер тренер</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="promotions reviews">
        <div class="container">
          <h2 class="promotions__title">Отзывы</h2>

          <div class="promotions__slider">
            <!-- Slider main container -->
            <div class="swiper promotions__swiper">
              <!-- Additional required wrapper -->
              <div class="swiper-wrapper">
                <!-- Group 1 -->
                <div class="swiper-slide promotions__slide">
                  <div class="loading-overlay"></div>
                  <div class="reviews__wrapper">
                    <p class="reviews__username">Ольга</p>
                    <p class="reviews__text">
                      Мореон Фитнес – семейный премиум фитнес-клуб с бассейном,
                      40 видами групповых программ, детским клубом, школой
                      единоборств и скалодромом. Оборудование тренажерного зала
                      поставляет эксклюзивный партнер
                    </p>
                    <div class="reviews__stars">
                      <img src="assets/svg/Stars.svg" alt="star" />
                    </div>
                  </div>
                </div>
                <div class="swiper-slide promotions__slide">
                  <div class="loading-overlay"></div>
                  <div class="reviews__wrapper">
                    <p class="reviews__username">Ольга</p>
                    <p class="reviews__text">
                      Мореон Фитнес – семейный премиум фитнес-клуб с бассейном,
                      40 видами групповых программ, детским клубом, школой
                      единоборств и скалодромом. Оборудование тренажерного зала
                      поставляет эксклюзивный партнер
                    </p>
                    <div class="reviews__stars">
                      <img src="assets/svg/Stars.svg" alt="star" />
                    </div>
                  </div>
                </div>
                <div class="swiper-slide promotions__slide">
                  <div class="loading-overlay"></div>
                  <div class="reviews__wrapper">
                    <p class="reviews__username">Ольга</p>
                    <p class="reviews__text">
                      Мореон Фитнес – семейный премиум фитнес-клуб с бассейном,
                      40 видами групповых программ, детским клубом, школой
                      единоборств и скалодромом. Оборудование тренажерного зала
                      поставляет эксклюзивный партнер
                    </p>
                    <div class="reviews__stars">
                      <img src="assets/svg/Stars.svg" alt="star" />
                    </div>
                  </div>
                </div>
                <!-- Group 2 -->
                <div class="swiper-slide promotions__slide">
                  <div class="loading-overlay"></div>
                  <div class="reviews__wrapper">
                    <p class="reviews__username">Ольга</p>
                    <p class="reviews__text">
                      Мореон Фитнес – семейный премиум фитнес-клуб с бассейном,
                      40 видами групповых программ, детским клубом, школой
                      единоборств и скалодромом. Оборудование тренажерного зала
                      поставляет эксклюзивный партнер
                    </p>
                    <div class="reviews__stars">
                      <img src="assets/svg/Stars.svg" alt="star" />
                    </div>
                  </div>
                </div>
                <div class="swiper-slide promotions__slide">
                  <div class="loading-overlay"></div>
                  <div class="reviews__wrapper">
                    <p class="reviews__username">Ольга</p>
                    <p class="reviews__text">
                      Мореон Фитнес – семейный премиум фитнес-клуб с бассейном,
                      40 видами групповых программ, детским клубом, школой
                      единоборств и скалодромом. Оборудование тренажерного зала
                      поставляет эксклюзивный партнер
                    </p>
                    <div class="reviews__stars">
                      <img src="assets/svg/Stars.svg" alt="star" />
                    </div>
                  </div>
                </div>
                <div class="swiper-slide promotions__slide">
                  <div class="loading-overlay"></div>
                  <div class="reviews__wrapper">
                    <p class="reviews__username">Ольга</p>
                    <p class="reviews__text">
                      Мореон Фитнес – семейный премиум фитнес-клуб с бассейном,
                      40 видами групповых программ, детским клубом, школой
                      единоборств и скалодромом. Оборудование тренажерного зала
                      поставляет эксклюзивный партнер
                    </p>
                    <div class="reviews__stars">
                      <img src="assets/svg/Stars.svg" alt="star" />
                    </div>
                  </div>
                </div>
                <!-- Group 3 -->
                <div class="swiper-slide promotions__slide">
                  <div class="loading-overlay"></div>
                  <div class="reviews__wrapper">
                    <p class="reviews__username">Ольга</p>
                    <p class="reviews__text">
                      Мореон Фитнес – семейный премиум фитнес-клуб с бассейном,
                      40 видами групповых программ, детским клубом, школой
                      единоборств и скалодромом. Оборудование тренажерного зала
                      поставляет эксклюзивный партнер
                    </p>
                    <div class="reviews__stars">
                      <img src="assets/svg/Stars.svg" alt="star" />
                    </div>
                  </div>
                </div>
                <div class="swiper-slide promotions__slide">
                  <div class="loading-overlay"></div>
                  <div class="reviews__wrapper">
                    <p class="reviews__username">Ольга</p>
                    <p class="reviews__text">
                      Мореон Фитнес – семейный премиум фитнес-клуб с бассейном,
                      40 видами групповых программ, детским клубом, школой
                      единоборств и скалодромом. Оборудование тренажерного зала
                      поставляет эксклюзивный партнер
                    </p>
                    <div class="reviews__stars">
                      <img src="assets/svg/Stars.svg" alt="star" />
                    </div>
                  </div>
                </div>
                <div class="swiper-slide promotions__slide">
                  <div class="loading-overlay"></div>
                  <div class="reviews__wrapper">
                    <p class="reviews__username">Ольга</p>
                    <p class="reviews__text">
                      Мореон Фитнес – семейный премиум фитнес-клуб с бассейном,
                      40 видами групповых программ, детским клубом, школой
                      единоборств и скалодромом. Оборудование тренажерного зала
                      поставляет эксклюзивный партнер
                    </p>
                    <div class="reviews__stars">
                      <img src="assets/svg/Stars.svg" alt="star" />
                    </div>
                  </div>
                </div>
                <!-- Group 4 -->
                <div class="swiper-slide promotions__slide">
                  <div class="loading-overlay"></div>
                  <div class="reviews__wrapper">
                    <p class="reviews__username">Ольга</p>
                    <p class="reviews__text">
                      Мореон Фитнес – семейный премиум фитнес-клуб с бассейном,
                      40 видами групповых программ, детским клубом, школой
                      единоборств и скалодромом. Оборудование тренажерного зала
                      поставляет эксклюзивный партнер
                    </p>
                    <div class="reviews__stars">
                      <img src="assets/svg/Stars.svg" alt="star" />
                    </div>
                  </div>
                </div>
                <div class="swiper-slide promotions__slide">
                  <div class="loading-overlay"></div>
                  <div class="reviews__wrapper">
                    <p class="reviews__username">Ольга</p>
                    <p class="reviews__text">
                      Мореон Фитнес – семейный премиум фитнес-клуб с бассейном,
                      40 видами групповых программ, детским клубом, школой
                      единоборств и скалодромом. Оборудование тренажерного зала
                      поставляет эксклюзивный партнер
                    </p>
                    <div class="reviews__stars">
                      <img src="assets/svg/Stars.svg" alt="star" />
                    </div>
                  </div>
                </div>
                <div class="swiper-slide promotions__slide">
                  <div class="loading-overlay"></div>
                  <div class="reviews__wrapper">
                    <p class="reviews__username">Ольга</p>
                    <p class="reviews__text">
                      Мореон Фитнес – семейный премиум фитнес-клуб с бассейном,
                      40 видами групповых программ, детским клубом, школой
                      единоборств и скалодромом. Оборудование тренажерного зала
                      поставляет эксклюзивный партнер
                    </p>
                    <div class="reviews__stars">
                      <img src="assets/svg/Stars.svg" alt="star" />
                    </div>
                  </div>
                </div>
              </div>
              <!-- Add Pagination -->
              <div class="swiper-pagination promotions__pagination"></div>
            </div>

            <!-- Navigation buttons -->
            <div class="promotions__button-prev reviews__button-prev">
              <img
                src="assets/svg/Left.svg"
                alt="Previous"
                class="arrow"
                width="50"
                height="50"
              />
            </div>

            <div class="promotions__button-next reviews__button-next">
              <img
                src="assets/svg/Left.svg"
                alt="Next"
                class="arrow"
                width="50"
                height="50"
              />
            </div>
          </div>
        </div>
      </div>
      <div class="training-form">
        <div class="container">
          <div class="training-form__wrapper">
            <div class="training-form__content">
              <h2 class="training-form__title">Запись на тренировку</h2>
              <p class="training-form__subtitle">
                Выберите дату, время и конкретного тренера
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
                      placeholder="Выберите дату"
                    />
                  </div>
                  <div class="training-form__field">
                    <input
                      type="time"
                      id="time"
                      name="time"
                      class="training-form__input"
                      required
                      placeholder="Выберите время"
                    />
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
    </main>
    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="js/main.js"></script>
    <script src="js/training-form.js"></script>
    <script src="js/form-styles.js"></script>
    <script src="js/map.js"></script>
  </body>
</html>
