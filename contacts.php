<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <link
      href="https://fonts.googleapis.com/css2?family=Dela+Gothic+One&family=Montserrat:wght@400;500;600;700&display=swap"
      rel="stylesheet"
    />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Контакты - Moreon Fitness-center</title>
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
      <!-- Contacts Banner Section -->
      <div class="about-club">
        <div class="about-club__bg">
          <img
            src="assets/img/bg.png"
            alt="Moreon Fitness contacts"
            class="about-club__bg-img"
          />
          <div class="about-club__bg-gradient"></div>
          <div class="about-club__bg-radial"></div>
        </div>
        <div class="about-club__content">
          <h1 class="about-club__title">
            <span class="about-club__title-span">Контакты</span><br />
            Moreon Fitness
          </h1>
          <div class="about-club__buttons">
            <a href="#contact-form" class="training-form__button">
              Написать нам
            </a>
            <a href="#map-section" class="main__banner-button main__banner-button--secondary">
              <span class="main__banner-button-text">Показать на карте</span>
            </a>
          </div>
        </div>
      </div>

      <!-- Contacts Section -->
      <section class="contacts">
        <div class="container">
          <div class="contacts__wrapper">
            <div class="contacts__content">
              <h2 class="contacts__title">Наши <span class="about-company__title-span">контакты</span></h2>
              <div class="contacts__info">
                <div class="contacts__item">
                  <div class="contacts__label">Телефон:</div>
                  <a href="tel:+74958590370" class="contacts__text">+7 (495) 859-03-70</a>
                </div>
                <div class="contacts__item">
                  <div class="contacts__label">Email:</div>
                  <a href="mailto:info@moreon-fitness.ru" class="contacts__text">info@moreon-fitness.ru</a>
                </div>
                <div class="contacts__item">
                  <div class="contacts__label">Адрес:</div>
                  <a href="https://yandex.ru/maps/-/CCUZYMh7cC" target="_blank" class="contacts__text">г. Ижевск, ул. Пушкинская д. 16</a>
                </div>
                <div class="contacts__item">
                  <div class="contacts__label">Время работы:</div>
                  <div class="contacts__text">
                    <div class="contacts__time-row"><span>Будни:</span> <span>07:00 - 23:00</span></div>
                    <div class="contacts__time-row"><span>Выходные:</span> <span>09:00 - 23:00</span></div>
                    <div class="contacts__time-row"><span>Бассейн:</span> <span>07:00 - 23:00</span></div>
                  </div>
                </div>
                <div class="contacts__item">
                  <div class="contacts__label">Мы в соцсетях:</div>
                  <div class="contacts__social">
                    <a href="#" class="contacts__social-link">
                      <img src="assets/svg/instagram.svg" alt="Instagram" />
                    </a>
                    <a href="#" class="contacts__social-link">
                      <img src="assets/svg/facebook.svg" alt="Facebook" />
                    </a>
                    <a href="#" class="contacts__social-link">
                      <img src="assets/svg/telegram.svg" alt="Telegram" />
                    </a>
                    <a href="#" class="contacts__social-link">
                      <img src="assets/svg/vk.svg" alt="VK" />
                    </a>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Contact Form -->
            <div class="training-form__content" id="contact-form">
              <div class="contact-form__icon">
                <img src="assets/svg/mail.svg" alt="Напишите нам" />
              </div>
              <h3 class="training-form__title">Напишите нам</h3>
              <p class="training-form__subtitle">Заполните форму, и мы свяжемся с вами в ближайшее время</p>
              <form class="training-form__form">
                <div class="training-form__row">
                  <div class="training-form__field">
                    <input type="text" class="training-form__input" placeholder="Ваше имя *" required>
                  </div>
                </div>
                <div class="training-form__row">
                  <div class="training-form__field">
                    <input type="email" class="training-form__input" placeholder="Email *" required>
                  </div>
                </div>
                <div class="training-form__row">
                  <div class="training-form__field">
                    <input type="tel" class="training-form__input" placeholder="Телефон *" required>
                  </div>
                </div>
                <div class="training-form__row">
                  <div class="training-form__field">
                    <textarea class="training-form__input training-form__textarea" placeholder="Ваше сообщение" rows="4"></textarea>
                  </div>
                </div>
                <div class="training-form__row training-form__row--center">
                  <button type="submit" class="training-form__button">Отправить</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </section>

      <!-- Map Section -->
      <section class="contacts__map" id="map-section">
        <div class="container">
          <h2 class="contacts__title">Как <span class="about-company__title-span">нас найти</span></h2>
          <div class="contacts__map-container" id="map">
            <!-- Map will be inserted by JavaScript -->
            <img src="assets/img/map.jpg" alt="Карта проезда" class="contacts__map-img" id="map-placeholder">
            <div class="contacts__map-overlay">
              <div class="contacts__map-card">
                <h3 class="contacts__map-title">Moreon Fitness</h3>
                <p class="contacts__map-address">г. Ижевск, ул. Пушкинская д. 16</p>
                <a href="tel:+74958590370" class="contacts__map-phone">+7 (495) 859-03-70</a>
                <a href="https://yandex.ru/maps/-/CCUZYMh7cC" target="_blank" class="contacts__map-link">Построить маршрут</a>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- How to get to us Section -->
      <section class="facilities">
        <div class="container">
          <h2 class="facilities__title">
            Как <span class="facilities__title-span">добраться до нас</span>
          </h2>
          <p class="facilities__subtitle">
            Мы находимся в удобном месте с хорошей транспортной доступностью
          </p>

          <!-- На общественном транспорте -->
          <div class="facilities__item">
            <div class="facilities__slider">
              <div class="swiper facilities__swiper">
                <div class="swiper-wrapper">
                  <div class="swiper-slide">
                    <img src="assets/img/slide-1.jpg" alt="Общественный транспорт" />
                  </div>
                </div>
              </div>
              <div class="facilities__nav">
                <button class="facilities__prev">
                  <img src="assets/svg/Left.svg" width="50" height="50" alt="Previous" />
                </button>
                <button class="facilities__next">
                  <img src="assets/svg/Left.svg" width="50" height="50" alt="Next" />
                </button>
              </div>
            </div>
            <div class="facilities__content">
              <div class="facilities__content-bg">ОБЩЕСТВЕННЫЙ ТРАНСПОРТ</div>
              <h3 class="facilities__content-title">ОБЩЕСТВЕННЫЙ ТРАНСПОРТ</h3>
              <div class="facilities__content-text">
                <p>
                  От станции метро «Ясенево» - 5 минут пешком по указателям.
                </p>
                <p>
                  Автобусы №№ 144, 202, 261, 781 - остановка «Спортивный комплекс Мореон».
                </p>
              </div>
            </div>
          </div>

          <!-- На автомобиле -->
          <div class="facilities__item facilities__item--reverse">
            <div class="facilities__content">
              <div class="facilities__content-bg">НА АВТОМОБИЛЕ</div>
              <h3 class="facilities__content-title">НА АВТОМОБИЛЕ</h3>
              <div class="facilities__content-text">
                <p>
                  Удобный подъезд со стороны МКАД и ТТК (Севастопольский пр-т).
                </p>
                <p>
                  Для членов клуба действует бесплатная парковка на территории комплекса.
                </p>
              </div>
            </div>
            <div class="facilities__slider">
              <div class="swiper facilities__swiper">
                <div class="swiper-wrapper">
                  <div class="swiper-slide">
                    <img src="assets/img/slide-2.jpg" alt="На автомобиле" />
                  </div>
                </div>
              </div>
              <div class="facilities__nav">
                <button class="facilities__prev">
                  <img src="assets/svg/Left.svg" width="50" height="50" alt="Previous" />
                </button>
                <button class="facilities__next">
                  <img src="assets/svg/Left.svg" width="50" height="50" alt="Next" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Call to Action Section -->
      <section class="about-club" style="min-height: 400px;">
        <div class="about-club__bg">
          <img
            src="assets/img/Bg3.png"
            alt="Moreon Fitness background"
            class="about-club__bg-img"
          />
          <div class="about-club__bg-gradient"></div>
          <div class="about-club__bg-radial"></div>
        </div>
        <div class="about-club__content">
          <h2 class="about-club__title" style="font-size: 40px;">
            Готовы начать тренировки в <span class="about-club__title-span">Moreon Fitness</span>?
          </h2>
          <div class="about-club__buttons">
            <a href="./training_session.php" class="training-form__button">
              Записаться на тренировку
            </a>
            <a href="tel:+74958590370" class="main__banner-button main__banner-button--secondary">
              <span class="main__banner-button-text">Позвонить нам</span>
            </a>
          </div>
        </div>
      </section>
    </main>
    <?php include 'footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="js/auth.js"></script>
    <script src="js/contacts.js"></script>
    <script>
      // Smooth scroll for anchor links
      document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
          e.preventDefault();
          
          const targetId = this.getAttribute('href');
          const targetElement = document.querySelector(targetId);
          
          if (targetElement) {
            window.scrollTo({
              top: targetElement.offsetTop - 100,
              behavior: 'smooth'
            });
          }
        });
      });
      
      // Initialize Yandex Map
      ymaps.ready(function() {
        // Hide placeholder image
        document.getElementById('map-placeholder').style.display = 'none';
        
        // Create map
        var myMap = new ymaps.Map('map', {
          center: [56.8519, 53.2044], // Координаты Ижевска
          zoom: 16
        });
        
        // Add marker
        var myPlacemark = new ymaps.Placemark([56.8519, 53.2044], {
          hintContent: 'Moreon Fitness',
          balloonContent: 'Moreon Fitness<br>г. Ижевск, ул. Пушкинская д. 16'
        }, {
          iconLayout: 'default#image',
          iconImageHref: 'assets/img/map-marker.png',
          iconImageSize: [40, 40],
          iconImageOffset: [-20, -40]
        });
        
        myMap.geoObjects.add(myPlacemark);
        myMap.controls.remove('geolocationControl');
        myMap.controls.remove('searchControl');
        myMap.controls.remove('trafficControl');
        myMap.controls.remove('typeSelector');
        myMap.controls.remove('fullscreenControl');
        myMap.controls.remove('rulerControl');
        myMap.behaviors.disable(['scrollZoom']);
      });
      
      // Initialize Swiper
      document.addEventListener('DOMContentLoaded', function() {
        new Swiper('.facilities__swiper', {
          slidesPerView: 1,
          spaceBetween: 30,
          loop: true,
          pagination: {
            el: '.swiper-pagination',
            clickable: true,
          },
          navigation: {
            nextEl: '.facilities__next',
            prevEl: '.facilities__prev',
          }
        });
      });
    </script>
  </body>
</html> 