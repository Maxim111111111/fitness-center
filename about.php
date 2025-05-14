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
      <div class="about-club">
        <div class="about-club__bg">
          <img
            src="assets/img/about-bg-image.png"
            alt="Moreon Fitness background"
            class="about-club__bg-img"
          />
          <div class="about-club__bg-gradient"></div>
          <div class="about-club__bg-radial"></div>
        </div>
        <div class="about-club__content">
          <h1 class="about-club__title">
            <span class="about-club__title-span">Moreon Fitness</span><br />
            откроет для вас<br />новые возможности
          </h1>
          <div class="about-club__buttons">
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
      </div>
      <div class="about-company">
        <div class="container">
          <h2 class="about-company__title">
            <span class="about-company__title-span">Moreon Fitness</span>
            Яснево
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
      <!-- Benefits Section -->

      <!-- Club Card Section -->
      <section class="club-card-section">
        <div class="container">
          <h2 class="club-card-section__title">
            В каждом <span class="club-card-section__title-span">клубe</span>
          </h2>
          <div class="club-card-section__grid">
            <div class="club-card-section__card">
              <div class="club-card-section__icon">
                <img src="assets/svg/club1.svg" alt="Тренажерный зал" />
              </div>
              <div class="club-card-section__text">Тренажерный зал</div>
            </div>
            <div class="club-card-section__card">
              <div class="club-card-section__icon">
                <img src="assets/svg/club2.svg" alt="Групповые программы" />
              </div>
              <div class="club-card-section__text">Групповые программы</div>
            </div>
            <div class="club-card-section__card">
              <div class="club-card-section__icon">
                <img src="assets/svg/club3.svg" alt="Бассейн" />
              </div>
              <div class="club-card-section__text">Бассейн</div>
            </div>
            <div class="club-card-section__card">
              <div class="club-card-section__icon">
                <img src="assets/svg/club4.svg" alt="Вводные тренировки" />
              </div>
              <div class="club-card-section__text">Вводные тренировки</div>
            </div>
            <div class="club-card-section__card">
              <div class="club-card-section__icon">
                <img src="assets/svg/club5.svg" alt="Единоборства" />
              </div>
              <div class="club-card-section__text">Единоборства</div>
            </div>
            <div class="club-card-section__card">
              <div class="club-card-section__icon">
                <img src="assets/svg/club6.svg" alt="Бесплатная парковка" />
              </div>
              <div class="club-card-section__text">Бесплатная парковка</div>
            </div>
            <div class="club-card-section__card">
              <div class="club-card-section__icon">
                <img src="assets/svg/club7.svg" alt="Клубная жизнь" />
              </div>
              <div class="club-card-section__text">Клубная жизнь</div>
            </div>
            <div class="club-card-section__card">
              <div class="club-card-section__icon">
                <img src="assets/svg/club8.svg" alt="Фитнес диагностика" />
              </div>
              <div class="club-card-section__text">Фитнес диагностика</div>
            </div>
          </div>
        </div>
      </section>

      <!-- Все, что нужно для эффективных тренировок -->
      <section class="facilities">
        <div class="container">
          <h2 class="facilities__title">
            Все, что нужно для
            <span class="facilities__title-span">эффективных тренировок</span>
          </h2>
          <p class="facilities__subtitle">
            Многофункциональный отдых включает в себя тренировки, отдых и
            развлечения
          </p>

          <!-- СПА-центр -->
          <div class="facilities__item">
            <div class="facilities__slider">
              <div class="swiper facilities__swiper">
                <div class="swiper-wrapper">
                  <div class="swiper-slide">
                    <img src="assets/images/about-img1.png" alt="СПА-центр" />
                  </div>
                  <div class="swiper-slide">
                    <img src="assets/images/about-img1.png" alt="СПА-центр" />
                  </div>
                  <div class="swiper-slide">
                    <img src="assets/images/about-img1.png" alt="СПА-центр" />
                  </div>
                </div>
                <div class="swiper-pagination">
                  <span
                    class="promotions__bullet promotions__bullet--active"
                  ></span>
                  <span class="promotions__bullet"></span>
                  <span class="promotions__bullet"></span>
                </div>
              </div>
              <div class="facilities__nav">
                <button class="facilities__prev">
                  <img
                    src="assets/svg/Left.svg"
                    width="50"
                    height="50"
                    alt="Previous"
                  />
                </button>
                <button class="facilities__next">
                  <img
                    src="assets/svg/Left.svg"
                    width="50"
                    height="50"
                    alt="Next"
                  />
                </button>
              </div>
            </div>
            <div class="facilities__content">
              <div class="facilities__content-bg">СПА-ЦЕНТР</div>
              <h3 class="facilities__content-title">СПА-ЦЕНТР</h3>
              <div class="facilities__content-text">
                <p>
                  «Moreon SPA» — уникальный банный спа-комплекс на полуострова
                  Сантонини. Это самое большое спа пространство в Москве, где
                  можно сходить в баню и попариться.
                </p>
                <p>
                  Здесь можно посетить бани и сауны, подышать в соляной пещере,
                  поплавать в бассейне, полежать в джакузи, а также
                  воспользоваться спа-процедурами.
                </p>
              </div>
            </div>
          </div>

          <!-- Кафе и рестораны -->
          <div class="facilities__item">
            <div class="facilities__content">
              <div class="facilities__content-bg">КАФЕ И РЕСТОРАНЫ</div>
              <h3 class="facilities__content-title">КАФЕ И РЕСТОРАНЫ</h3>
              <div class="facilities__content-text">
                <p>
                  После тренировки можно вкусно перекусить и отдохнуть в
                  ресторанах и кафе. В фитнесе находится собственная барная зона
                  с разнообразным меню. Также в холле комплекса расположены
                  рестораны «Порт» и «Остров».
                </p>
              </div>
            </div>
            <div class="facilities__slider">
              <div class="swiper facilities__swiper">
                <div class="swiper-wrapper">
                  <div class="swiper-slide">
                    <img
                      src="assets/images/about-img2.png"
                      alt="Кафе и рестораны"
                    />
                  </div>
                  <div class="swiper-slide">
                    <img
                      src="assets/images/about-img2.png"
                      alt="Кафе и рестораны"
                    />
                  </div>
                  <div class="swiper-slide">
                    <img
                      src="assets/images/about-img2.png"
                      alt="Кафе и рестораны"
                    />
                  </div>
                </div>
                <div class="swiper-pagination">
                  <span
                    class="promotions__bullet promotions__bullet--active"
                  ></span>
                  <span class="promotions__bullet"></span>
                  <span class="promotions__bullet"></span>
                </div>
              </div>
              <div class="facilities__nav">
                <button class="facilities__prev">
                  <img src="assets/svg/Left.svg" alt="Previous" />
                </button>
                <button class="facilities__next">
                  <img src="assets/svg/Left.svg" alt="Next" />
                </button>
              </div>
            </div>
          </div>

          <!-- Ресепшн -->
          <div class="facilities__item">
            <div class="facilities__slider">
              <div class="swiper facilities__swiper">
                <div class="swiper-wrapper">
                  <div class="swiper-slide">
                    <img src="assets/images/about-img3.png" alt="Ресепшн" />
                  </div>
                  <div class="swiper-slide">
                    <img src="assets/images/about-img3.png" alt="Ресепшн" />
                  </div>
                  <div class="swiper-slide">
                    <img src="assets/images/about-img3.png" alt="Ресепшн" />
                  </div>
                </div>
                <div class="swiper-pagination">
                  <span
                    class="promotions__bullet promotions__bullet--active"
                  ></span>
                  <span class="promotions__bullet"></span>
                  <span class="promotions__bullet"></span>
                </div>
              </div>
              <div class="facilities__nav">
                <button class="facilities__prev">
                  <img src="assets/svg/Left.svg" alt="Previous" />
                </button>
                <button class="facilities__next">
                  <img src="assets/svg/Left.svg" alt="Next" />
                </button>
              </div>
            </div>
            <div class="facilities__content">
              <div class="facilities__content-bg">РЕСЕПШН</div>
              <h3 class="facilities__content-title">РЕСЕПШН</h3>
              <div class="facilities__content-text">
                <p>
                  При входе в Moreon Fitness Вас встретит и поможет с любым
                  вопросом дружелюбный коллектив фитнес-центра проведет
                  экскурсию по комплексу. Также вы можете связаться с нами по
                  телефону +7 (495) 859-03-70
                </p>
              </div>
            </div>
          </div>

          <!-- Отдел продаж -->
          <div class="facilities__item">
            <div class="facilities__content">
              <div class="facilities__content-bg">ОТДЕЛ ПРОДАЖ</div>
              <h3 class="facilities__content-title">ОТДЕЛ ПРОДАЖ</h3>
              <div class="facilities__content-text">
                <p>
                  По вопросам оформления карты и всем расценкам обращайтесь в
                  отдел продаж.
                </p>
              </div>
            </div>
            <div class="facilities__slider">
              <div class="swiper facilities__swiper">
                <div class="swiper-wrapper">
                  <div class="swiper-slide">
                    <img
                      src="assets/images/about-img4.png"
                      alt="Отдел продаж"
                    />
                  </div>
                  <div class="swiper-slide">
                    <img
                      src="assets/images/about-img4.png"
                      alt="Отдел продаж"
                    />
                  </div>
                  <div class="swiper-slide">
                    <img
                      src="assets/images/about-img4.png"
                      alt="Отдел продаж"
                    />
                  </div>
                </div>
                <div class="swiper-pagination">
                  <span
                    class="promotions__bullet promotions__bullet--active"
                  ></span>
                  <span class="promotions__bullet"></span>
                  <span class="promotions__bullet"></span>
                </div>
              </div>
              <div class="facilities__nav">
                <button class="facilities__prev">
                  <img src="assets/svg/Left.svg" alt="Previous" />
                </button>
                <button class="facilities__next">
                  <img src="assets/svg/Left.svg" alt="Next" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </section>
      <section class="benefits">
        <div class="container">
          <h2 class="benefits__title">
            Выгоды посещения <span>фитнес клуба</span>
          </h2>
          <div class="benefits__grid">
            <div class="benefits__card">
              <div class="benefits__card-icon">
                <img src="assets/svg/svg/body.svg" alt="Красивая фигура" />
              </div>
              <h3 class="benefits__card-title">Красивая фигура</h3>
              <p class="benefits__card-text">
                Мы выбираем быть красивыми, здоровыми и гармоничными
              </p>
            </div>
            <div class="benefits__card">
              <div class="benefits__card-icon">
                <img src="assets/svg/svg/hands-flower.svg" alt="Спа" />
              </div>
              <h3 class="benefits__card-title">Спа</h3>
              <p class="benefits__card-text">
                В клубную карту входит посещенние СПА и бассейна
              </p>
            </div>
            <div class="benefits__card">
              <div class="benefits__card-icon">
                <img src="assets/svg/svg/treadmill.svg" alt="Подходит всем" />
              </div>
              <h3 class="benefits__card-title">Подходит всем</h3>
              <p class="benefits__card-text">
                Фитнес программы для всех уровней подготовки
              </p>
            </div>
            <div class="benefits__card">
              <div class="benefits__card-icon">
                <img src="assets/svg/svg/trainer.svg" alt="Опытные тренеры" />
              </div>
              <h3 class="benefits__card-title">Опытные тренеры</h3>
              <p class="benefits__card-text">
                Фитнес программы для всех уровней подготовки
              </p>
            </div>
          </div>
        </div>
      </section>
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
    <script src="js/about.js"></script>
    <script src="js/map.js"></script>
  </body>
</html>
