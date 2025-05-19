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
  const email = document.getElementById("review-email").value;
  const rating = document.getElementById("rating").value;
  const text = document.getElementById("review-text").value;

  if (!name || !email || !rating || !text) {
    alert("Пожалуйста, заполните все обязательные поля и поставьте оценку");
    return false;
  }

  // Проверка email
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email)) {
    alert("Пожалуйста, введите корректный email");
    return false;
  }

  return true;
}

// Функция для отправки формы отзыва
function submitReviewForm() {
  const formData = new FormData(document.getElementById("reviewForm"));

  // Показываем индикатор загрузки или блокируем кнопку отправки
  const submitButton = document.querySelector(".review-form__button");
  if (submitButton) {
    submitButton.disabled = true;
    submitButton.textContent = "Отправка...";
  }

  // Отправляем данные на сервер
  fetch("process_review.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      // Разблокируем кнопку
      if (submitButton) {
        submitButton.disabled = false;
        submitButton.textContent = "Отправить отзыв";
      }

      if (data.success) {
        // Показываем сообщение об успешной отправке
        alert(data.message);

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
      } else {
        // Показываем сообщение об ошибке
        alert(
          data.message ||
            "Произошла ошибка при отправке отзыва. Пожалуйста, попробуйте еще раз."
        );
      }
    })
    .catch((error) => {
      console.error("Ошибка:", error);
      alert(
        "Произошла ошибка при отправке отзыва. Пожалуйста, попробуйте еще раз."
      );

      // Разблокируем кнопку
      if (submitButton) {
        submitButton.disabled = false;
        submitButton.textContent = "Отправить отзыв";
      }
    });
}
