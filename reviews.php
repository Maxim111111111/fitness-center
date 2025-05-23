<?php
// Include database configuration
require_once 'database/config.php';

// Вспомогательная функция для склонения слова "отзыв"
function getReviewsCountText($count) {
    $text = "отзывов";
    if ($count % 10 === 1 && $count % 100 !== 11) {
        $text = "отзыв";
    } else if (in_array($count % 10, [2, 3, 4]) && !in_array($count % 100, [12, 13, 14])) {
        $text = "отзыва";
    }
    return $text;
}

// Get approved reviews from database
try {
    $stmt = $pdo->prepare("SELECT * FROM reviews WHERE status = 'approved' ORDER BY created_at DESC");
    $stmt->execute();
    $reviews = $stmt->fetchAll();
} catch (PDOException $e) {
    $reviews = [];
    // Log error but don't show to user
    error_log("Error fetching reviews: " . $e->getMessage());
}
?>
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
    <link rel="stylesheet" href="style/reviews.css">
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
                    <a href="reviews.php" class="nav-button nav-button-active">Отзывы</a>
                </div>
                <div class="services-content">
                    <h1 class="services-title">Отзывы</h1>
                    <p class="services-description">
                        Занятия фитнесом – это микс эффективных упражнений, мотивирующего влияния тренера, духа соревнований и общения с единомышленниками. Члены клуба Мореон Фитнес получают доступ к тренажерному залу, бассейнам, скалодрому и 40 видам групповых занятий для разного уровня подготовки и возраста – от начинающих до продвинутых спортсменов, для детей и взрослых.
                    </p>
                    <a href="#" class="services-button" id="openReviewForm">Оставить отзыв</a>
                </div>
            </div>
        </section>

        <!-- Улучшенный блок отзывов -->
        <section class="reviews-section">
            <div class="container">
                <h2 class="reviews-section__title">Отзывы наших клиентов</h2>
                
                <div class="reviews-stats">
                    <?php 
                    // Расчет средней оценки
                    $avgRating = 0;
                    $totalReviews = count($reviews);
                    
                    if ($totalReviews > 0) {
                        $ratingSum = 0;
                        foreach ($reviews as $review) {
                            $ratingSum += (int)$review['rating'];
                        }
                        $avgRating = round($ratingSum / $totalReviews, 1);
                    }
                    ?>
                    
                    <div class="reviews-stats__summary">
                        <div class="reviews-stats__average">
                            <span class="reviews-stats__score"><?php echo $avgRating; ?></span>
                            <div class="reviews-stats__stars">
                                <?php
                                // Отображение средней оценки звездами
                                $fullStars = floor($avgRating);
                                $halfStar = ($avgRating - $fullStars) >= 0.5;
                                
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $fullStars) {
                                        echo '<span class="star active">★</span>';
                                    } elseif ($halfStar && $i == $fullStars + 1) {
                                        echo '<span class="star half-active">★</span>';
                                    } else {
                                        echo '<span class="star">★</span>';
                                    }
                                }
                                ?>
                            </div>
                            <span class="reviews-stats__count"><?php echo $totalReviews; ?> <?php echo getReviewsCountText($totalReviews); ?></span>
                        </div>
                        
                        <div class="reviews-stats__distribution">
                            <?php
                            // Расчет распределения оценок
                            $ratingDistribution = [0, 0, 0, 0, 0];
                            
                            foreach ($reviews as $review) {
                                $rating = (int)$review['rating'];
                                if ($rating >= 1 && $rating <= 5) {
                                    $ratingDistribution[$rating - 1]++;
                                }
                            }
                            
                            // Отображение распределения оценок
                            for ($i = 5; $i >= 1; $i--) {
                                $count = $ratingDistribution[$i - 1];
                                $percentage = $totalReviews > 0 ? round(($count / $totalReviews) * 100) : 0;
                                ?>
                                <div class="reviews-stats__bar">
                                    <span class="reviews-stats__star-count"><?php echo $i; ?></span>
                                    <div class="reviews-stats__bar-container">
                                        <div class="reviews-stats__bar-fill" style="width: <?php echo $percentage; ?>%"></div>
                                    </div>
                                    <span class="reviews-stats__bar-count"><?php echo $count; ?></span>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                    
                    <div class="reviews-stats__action">
                        <a href="#" class="reviews-stats__button" id="openReviewFormAlt">Оставить отзыв</a>
                    </div>
                </div>

                <div class="reviews-filter">
                    <div class="reviews-filter__label">Сортировать по:</div>
                    <div class="reviews-filter__options">
                        <button class="reviews-filter__option active" data-sort="newest">Новые</button>
                        <button class="reviews-filter__option" data-sort="highest">Высокий рейтинг</button>
                        <button class="reviews-filter__option" data-sort="lowest">Низкий рейтинг</button>
                    </div>
                </div>

                <div class="reviews-list">
                    <?php if (!empty($reviews)): ?>
                        <?php foreach ($reviews as $review): ?>
                            <div class="review-card" data-rating="<?php echo (int)$review['rating']; ?>">
                                <div class="review-card__header">
                                    <div class="review-card__avatar">
                                        <?php echo strtoupper(substr($review['name'], 0, 1)); ?>
                                    </div>
                                    <div class="review-card__info">
                                        <h3 class="review-card__name"><?php echo htmlspecialchars($review['name']); ?></h3>
                                        <div class="review-card__date">
                                            <?php 
                                            $date = new DateTime($review['created_at']);
                                            echo $date->format('d.m.Y'); 
                                            ?>
                                        </div>
                                    </div>
                                    <div class="review-card__rating">
                                        <?php
                                        $rating = (int)$review['rating'];
                                        for ($i = 1; $i <= 5; $i++) {
                                            $activeClass = $i <= $rating ? 'active' : '';
                                            echo '<span class="star ' . $activeClass . '">★</span>';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="review-card__content">
                                    <p class="review-card__text"><?php echo htmlspecialchars($review['text']); ?></p>
                                </div>
                                <div class="review-card__footer">
                                    <button class="review-card__helpful" data-review-id="<?php echo $review['id']; ?>">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M4.5 7V14M4.5 14H3C2.44772 14 2 13.5523 2 13V8C2 7.44772 2.44772 7 3 7H4.5M4.5 14H11.1716C11.702 14 12.1716 13.5523 12.1716 13.0219C12.1716 13.0073 12.1711 12.9928 12.17 12.9782L11.9961 10.9782C11.9653 10.4021 11.4804 9.95573 10.9037 9.96424L8.5 10C8.5 10 9 7 9 5.5C9 4.11929 7.88071 3 6.5 3C6.03587 3 5.75 3.26929 5.75 3.75L5.5 7H4.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <span class="review-card__helpful-text">Полезный отзыв</span>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="reviews-empty">
                            <div class="reviews-empty__icon">
                                <svg width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M32 56C45.2548 56 56 45.2548 56 32C56 18.7452 45.2548 8 32 8C18.7452 8 8 18.7452 8 32C8 45.2548 18.7452 56 32 56Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M32 40V40.01" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M32 32V24" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <h3 class="reviews-empty__title">Отзывов пока нет</h3>
                            <p class="reviews-empty__text">Станьте первым, кто оставит отзыв о нашем фитнес-клубе</p>
                            <a href="#" class="reviews-empty__button" id="openReviewFormEmpty">Оставить отзыв</a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if (count($reviews) > 5): ?>
                <div class="reviews-pagination">
                    <button class="reviews-pagination__button reviews-pagination__button--load-more">
                        Показать еще отзывы
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </section>
    </main>
    
    <!-- Модальное окно с формой отзыва -->
    <div class="modal-overlay" id="modalOverlay"></div>
    <div class="review-modal" id="reviewModal">
        <button class="review-modal__close" id="closeReviewForm"></button>
        <h2 class="review-form__title">Оставить отзыв</h2>
        <p class="review-form__subtitle">Расскажите о своих впечатлениях от посещения нашего фитнес-клуба</p>
        
        <form id="reviewForm" class="review-form">
            <div class="review-form__row">
                <div class="review-form__field">
                    <input type="text" id="review-name" name="name" class="review-form__input" placeholder="Ваше имя" required />
                </div>
                <div class="review-form__field">
                    <input type="email" id="review-email" name="email" class="review-form__input" placeholder="Ваш email" required />
                </div>
            </div>
            
            <div class="review-form__row">
                <div class="review-form__field">
                    <div class="review-form__rating">
                        <span class="review-form__rating-label">Ваша оценка:</span>
                        <div class="rating-stars">
                            <div class="rating-star" data-value="1"></div>
                            <div class="rating-star" data-value="2"></div>
                            <div class="rating-star" data-value="3"></div>
                            <div class="rating-star" data-value="4"></div>
                            <div class="rating-star" data-value="5"></div>
                        </div>
                        <input type="hidden" id="rating" name="rating" value="" required />
                    </div>
                </div>
            </div>
            
            <div class="review-form__row">
                <div class="review-form__field">
                    <textarea id="review-text" name="text" class="review-form__input review-form__textarea" placeholder="Ваш отзыв" required></textarea>
                </div>
            </div>
            
            <div class="review-form__row review-form__row--center">
                <button type="submit" class="review-form__button">Отправить отзыв</button>
            </div>
        </form>
    </div>
    
    <?php include 'footer.php'; ?>
    <script src="js/services.js"></script>
    <script src="js/map.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="js/main.js"></script>
    <script src="js/training-form.js"></script>
    <script src="js/form-styles.js"></script>
    <script src="js/review-form.js"></script>
    <script>
    // Инициализация сортировки отзывов
    document.addEventListener('DOMContentLoaded', function() {
        // Открытие формы отзыва через альтернативные кнопки
        const openReviewFormAlt = document.getElementById('openReviewFormAlt');
        const openReviewFormEmpty = document.getElementById('openReviewFormEmpty');
        const reviewModal = document.getElementById('reviewModal');
        const overlay = document.getElementById('modalOverlay');
        
        if (openReviewFormAlt) {
            openReviewFormAlt.addEventListener('click', function(e) {
                e.preventDefault();
                reviewModal.classList.add('active');
                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            });
        }
        
        if (openReviewFormEmpty) {
            openReviewFormEmpty.addEventListener('click', function(e) {
                e.preventDefault();
                reviewModal.classList.add('active');
                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            });
        }
        
        // Сортировка отзывов
        const filterOptions = document.querySelectorAll('.reviews-filter__option');
        const reviewsList = document.querySelector('.reviews-list');
        const reviewCards = document.querySelectorAll('.review-card');
        
        if (filterOptions.length && reviewsList && reviewCards.length) {
            filterOptions.forEach(option => {
                option.addEventListener('click', function() {
                    // Удаление активного класса у всех опций
                    filterOptions.forEach(opt => opt.classList.remove('active'));
                    // Добавление активного класса к выбранной опции
                    this.classList.add('active');
                    
                    const sortType = this.getAttribute('data-sort');
                    const reviewsArray = Array.from(reviewCards);
                    
                    // Сортировка отзывов
                    switch (sortType) {
                        case 'newest':
                            // По умолчанию отзывы уже отсортированы по дате
                            break;
                        case 'highest':
                            reviewsArray.sort((a, b) => {
                                return parseInt(b.getAttribute('data-rating')) - parseInt(a.getAttribute('data-rating'));
                            });
                            break;
                        case 'lowest':
                            reviewsArray.sort((a, b) => {
                                return parseInt(a.getAttribute('data-rating')) - parseInt(b.getAttribute('data-rating'));
                            });
                            break;
                    }
                    
                    // Очистка списка отзывов
                    reviewsList.innerHTML = '';
                    
                    // Добавление отсортированных отзывов
                    reviewsArray.forEach(review => {
                        reviewsList.appendChild(review);
                    });
                });
            });
        }
        
        // Функционал "Полезный отзыв"
        const helpfulButtons = document.querySelectorAll('.review-card__helpful');
        
        if (helpfulButtons.length) {
            helpfulButtons.forEach(button => {
                button.addEventListener('click', function() {
                    this.classList.toggle('active');
                    const reviewId = this.getAttribute('data-review-id');
                    
                    // Здесь можно добавить AJAX запрос для сохранения информации о полезном отзыве
                    // Пример:
                    /*
                    fetch('mark_helpful.php', {
                        method: 'POST',
                        body: JSON.stringify({ review_id: reviewId }),
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    });
                    */
                });
            });
        }
        
        // Функционал "Показать еще отзывы"
        const loadMoreButton = document.querySelector('.reviews-pagination__button--load-more');
        
        if (loadMoreButton) {
            const reviewsPerPage = 5;
            let currentPage = 1;
            
            // Скрываем все отзывы кроме первых reviewsPerPage
            if (reviewCards.length > reviewsPerPage) {
                for (let i = reviewsPerPage; i < reviewCards.length; i++) {
                    reviewCards[i].style.display = 'none';
                }
            }
            
            loadMoreButton.addEventListener('click', function() {
                currentPage++;
                const startIndex = (currentPage - 1) * reviewsPerPage;
                const endIndex = startIndex + reviewsPerPage;
                
                // Показываем следующую порцию отзывов
                for (let i = startIndex; i < endIndex && i < reviewCards.length; i++) {
                    reviewCards[i].style.display = 'block';
                }
                
                // Скрываем кнопку, если больше нет отзывов для показа
                if (endIndex >= reviewCards.length) {
                    loadMoreButton.style.display = 'none';
                }
            });
        }
    });
    </script>
</body>
</html>