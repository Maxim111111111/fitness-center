// Форма отзыва
document.addEventListener("DOMContentLoaded", function () {
  const reviewForm = document.getElementById("reviewForm");
  const reviewModal = document.getElementById("reviewModal");
  const openReviewFormBtn = document.getElementById("openReviewForm");
  const closeReviewFormBtn = document.getElementById("closeReviewForm");
  const overlay = document.getElementById("modalOverlay");

  if (reviewForm && openReviewFormBtn) {
    // Открытие модального окна с формой
    openReviewFormBtn.addEventListener("click", function (e) {
      e.preventDefault();
      reviewModal.classList.add("active");
      overlay.classList.add("active");
      document.body.style.overflow = "hidden";
    });

    // Закрытие модального окна
    if (closeReviewFormBtn) {
      closeReviewFormBtn.addEventListener("click", function () {
        reviewModal.classList.remove("active");
        overlay.classList.remove("active");
        document.body.style.overflow = "";
      });
    }

    // Закрытие по клику на оверлей
    if (overlay) {
      overlay.addEventListener("click", function () {
        reviewModal.classList.remove("active");
        overlay.classList.remove("active");
        document.body.style.overflow = "";
      });
    }

    // Инициализация звездного рейтинга
    const ratingStars = document.querySelectorAll(".rating-star");
    const ratingInput = document.getElementById("rating");

    ratingStars.forEach((star, index) => {
      star.addEventListener("click", () => {
        ratingInput.value = index + 1;

        // Обновление отображения звезд
        ratingStars.forEach((s, i) => {
          if (i <= index) {
            s.classList.add("active");
          } else {
            s.classList.remove("active");
          }
        });
      });
    });

    // Обработка отправки формы
    reviewForm.addEventListener("submit", function (event) {
      event.preventDefault();

      if (validateReviewForm()) {
        submitReviewForm();
      }
    });
  }
});

// Функция для валидации формы отзыва
function validateReviewForm() {
  const name = document.getElementById("review-name").value;
  const rating = document.getElementById("rating").value;
  const text = document.getElementById("review-text").value;

  if (!name || !rating || !text) {
    alert("Пожалуйста, заполните все обязательные поля и поставьте оценку");
    return false;
  }

  return true;
}

// Функция для отправки формы отзыва
function submitReviewForm() {
  const formData = new FormData(document.getElementById("reviewForm"));
  const formObject = {};

  formData.forEach((value, key) => {
    formObject[key] = value;
  });

  console.log("Отправляем данные отзыва:", formObject);

  // Имитация отправки данных на сервер
  setTimeout(() => {
    console.log("Отзыв успешно отправлен");

    // Показываем сообщение об успешной отправке
    alert(
      "Спасибо! Ваш отзыв успешно отправлен и будет опубликован после модерации."
    );

    // Закрываем модальное окно
    const reviewModal = document.getElementById("reviewModal");
    const overlay = document.getElementById("modalOverlay");
    reviewModal.classList.remove("active");
    overlay.classList.remove("active");
    document.body.style.overflow = "";

    // Сбрасываем форму
    document.getElementById("reviewForm").reset();

    // Сбрасываем звезды
    const ratingStars = document.querySelectorAll(".rating-star");
    ratingStars.forEach((star) => star.classList.remove("active"));
  }, 1000);
}
