<?php
// Include database configuration
require_once 'database/config.php';

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
        <div class="promotions reviews">
        <div class="container">
          <h2 class="promotions__title">Отзывы</h2>

          <div class="promotions__slider">
            <!-- Slider main container -->
            <div class="swiper promotions__swiper">
              <!-- Additional required wrapper -->
              <div class="swiper-wrapper">
                <?php if (!empty($reviews)): ?>
                  <?php foreach ($reviews as $review): ?>
                    <div class="swiper-slide promotions__slide">
                      <div class="loading-overlay"></div>
                      <div class="reviews__wrapper">
                        <p class="reviews__username"><?php echo htmlspecialchars($review['name']); ?></p>
                        <p class="reviews__text">
                          <?php echo htmlspecialchars($review['text']); ?>
                        </p>
                        <div class="reviews__stars">
                          <?php
                            // Display stars based on rating
                            $rating = (int)$review['rating'];
                            echo '<div class="star-rating">';
                            for ($i = 1; $i <= 5; $i++) {
                                $activeClass = $i <= $rating ? 'active' : '';
                                echo '<span class="star ' . $activeClass . '">★</span>';
                            }
                            echo '</div>';
                          ?>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <!-- Default review if none in database -->
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
                <?php endif; ?>
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
</body>
</html>