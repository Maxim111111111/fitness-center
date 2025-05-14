<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <link
        href="https://fonts.googleapis.com/css2?family=Dela+Gothic+One&family=Montserrat:wght@400;500;600;700&display=swap"
        rel="stylesheet" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Moreon Fitness-ceter</title>
    <link rel="stylesheet" href="style/style.css" />
    <link rel="stylesheet" href="style/reset.css" />
    <link rel="stylesheet" href="style/coach.css" />
    <link rel="stylesheet" href="style/services.css" />
</head>

<body>
    <?php include 'header.php'; ?>
    <main>
    <section class="services-hero">
            <div class="services-container container">
                <div class="nav-buttons">
                    <a href="index.php" class="nav-button">Главная</a>
                    <span class="nav-divider">/</span>
                    <a href="coach.php" class="nav-button nav-button-active">Запись на тренеровку</a>
                </div>
                <div class="services-content">
                    <h1 class="services-title">Запись на тренировку</h1>
                    <p class="services-description">
                        Занятия фитнесом – это микс эффективных упражнений, мотивирующего влияния тренера, духа соревнований и общения с единомышленниками. Члены клуба Мореон Фитнес получают доступ к тренажерному залу, бассейнам, скалодрому и 40 видам групповых занятий для разного уровня подготовки и возраста – от начинающих до продвинутых спортсменов, для детей и взрослых.
                    </p>
                    <a href="#" class="services-button">Записаться</a>
                </div>
            </div>
        </section>
    </main>
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
    <?php include 'footer.php'; ?>
    <script src="js/coach.js"></script>
    <script src="js/training-form.js"></script>
    <script src="js/form-styles.js"></script>
    <script src="js/map.js"></script>
</body>

</html>