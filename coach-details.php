<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8" />
    <link
        href="https://fonts.googleapis.com/css2?family=Dela+Gothic+One&family=Montserrat:wght@400;500;600;700;800&display=swap"
        rel="stylesheet" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Тренер | Moreon Fitness</title>
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
                        <a href="coach.php" class="coach-details__breadcrumb-link">Подробнее о тренере</a>
                        
                    </div>
                    <div class="coach-details__content">
                        <div class="coach-details__left-column">
                            <div class="coach-details__photo">
                                <img src="images/coach-portrait.jpg" alt="Иванов Иван" class="coach-details__image">
                            </div>
                            <div class="coach-details__tags">
                                <span class="coach-details__tag">#Аэробные программы</span>
                                <span class="coach-details__tag">#Боевые искусства</span>
                                <span class="coach-details__tag">#Аэробно-силовае программы</span>
                                <span class="coach-details__tag">#Танцевальные программы</span>
                            </div>
                        </div>
                        <div class="coach-details__info">
                            <h1 class="coach-details__name">Иванов Иван</h1>
                            <p class="coach-details__position">
                                Премиум тренер<br>
                                Стаж работы: с 2000 года<br>
                                Телефон: +7 (000) 111-22-33<br>
                                Instagram: @Instagram
                            </p>

                            <div class="coach-details__block">
                                <h2 class="coach-details__subtitle">Специализация</h2>
                                <p class="coach-details__text">
                                    Силовой тренинг, коррекция фигуры, уменьшение объема жировой ткани, набор мышечной массы, 
                                    рекомендации по питанию, индивидуальный подбор упражнений и постановка техники выполнения, 
                                    исправление осанки.
                                </p>
                            </div>

                            <div class="coach-details__block">
                                <h2 class="coach-details__subtitle">Образование</h2>
                                <p class="coach-details__text">
                                    - Могилевский Государственный Университет им. А.А.Кулешова, Специальная подготовка / Физическая культура.<br>
                                    - ЭНЕРДЖИМ ФИТНЕС СТУДИО. Персональный тренер.
                                </p>
                            </div>

                            <div class="coach-details__block">
                                <h2 class="coach-details__subtitle">Достижения</h2>
                                <p class="coach-details__text">
                                    Мастер спорта по тяжёлой атлетике, Чемпион Европы 2002г. Призер Чемпионата Европы 2003г.
                                </p>
                            </div>

                            <div class="coach-details__booking">
                                <div class="coach-details__booking-content">
                                    <h2 class="coach-details__booking-title">индивидуальные занятия тренера</h2>
                                    <p class="coach-details__booking-text">
                                        Силовой тренинг, коррекция фигуры, уменьшение объема жировой ткани, набор мышечной массы, 
                                        рекомендации по питанию, индивидуальный подбор упражнений и постановка техники выполнения, 
                                        исправление осанки.
                                    </p>
                                    <a href="#" class="coach-details__booking-button">Записаться на индивидуальное занятие</a>
                                </div>
                            </div>
                        </div>
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
                                <p class="contacts__text">г. Москва м. Ясенево, ул. Голубинская,<br />д. 16</p>
                            </div>
                            <div class="contacts__item">
                                <span class="contacts__label">Телефон:</span>
                                <a href="tel:+74954816060" class="contacts__text">+7 (495) 481-60-60</a>
                            </div>
                            <div class="contacts__item">
                                <span class="contacts__label">E-mail:</span>
                                <a href="mailto:moreon@more-on.ru" class="contacts__text">moreon@more-on.ru</a>
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
    <script src="js/map.js"></script>
    <script src="js/coach.js"></script>
</body>

</html>