// Скрипт для страницы управления отзывами
document.addEventListener("DOMContentLoaded", function () {
  // Обработчик просмотра деталей отзыва
  const viewButtons = document.querySelectorAll(".view-review");
  viewButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const reviewId = this.getAttribute("data-review-id");
      loadReviewDetails(reviewId);
    });
  });

  // Обработчик одобрения отзыва
  const approveButtons = document.querySelectorAll(".approve-review");
  approveButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const reviewId = this.getAttribute("data-review-id");
      document.getElementById("approve-review-id").value = reviewId;
      document.getElementById("approveReviewForm").submit();
    });
  });

  // Обработчик одобрения из модального окна
  document
    .querySelector(".approve-from-modal")
    .addEventListener("click", function () {
      const reviewId = document.getElementById("view-id").textContent;
      document.getElementById("approve-review-id").value = reviewId;
      document.getElementById("approveReviewForm").submit();
    });

  // Обработчик открытия модального окна удаления
  const deleteModal = document.getElementById("deleteReviewModal");
  deleteModal.addEventListener("show.bs.modal", function (event) {
    const button = event.relatedTarget;
    const reviewId = button.getAttribute("data-review-id");
    document.getElementById("delete-review-id").value = reviewId;
  });

  // Кнопка удаления из модального окна просмотра
  document
    .getElementById("modal-delete-btn")
    .addEventListener("click", function () {
      const reviewId = document.getElementById("view-id").textContent;
      document.getElementById("delete-review-id").value = reviewId;
    });
});

/**
 * Загружает детали отзыва по ID и отображает их в модальном окне
 * @param {number} reviewId ID отзыва
 */
function loadReviewDetails(reviewId) {
  // Запрос к серверу для получения деталей отзыва
  fetch(`get_review.php?id=${reviewId}`)
    .then((response) => {
      if (!response.ok) {
        throw new Error("Ошибка при получении данных отзыва");
      }
      return response.json();
    })
    .then((data) => {
      if (data.success) {
        displayReviewDetails(data.review);
      } else {
        alert(data.message || "Ошибка при получении данных отзыва");
      }
    })
    .catch((error) => {
      console.error("Ошибка:", error);
      alert("Произошла ошибка при получении данных отзыва");
    });
}

/**
 * Отображает детали отзыва в модальном окне
 * @param {Object} review Объект с данными отзыва
 */
function displayReviewDetails(review) {
  // Заполняем поля модального окна
  document.getElementById("view-id").textContent = review.id;
  document.getElementById("view-user").textContent =
    review.user_first_name + " " + review.user_last_name;

  // Отображение рейтинга звездами
  const ratingContainer = document.getElementById("view-rating");
  ratingContainer.innerHTML = "";
  for (let i = 1; i <= 5; i++) {
    const star = document.createElement("i");
    star.className =
      "fas fa-star " + (i <= review.rating ? "text-warning" : "text-secondary");
    ratingContainer.appendChild(star);
  }

  // Отображение текста отзыва
  document.getElementById("view-comment").textContent = review.comment;

  // Тип отзыва
  let typeHtml = "";
  if (review.trainer_id) {
    typeHtml = `<span class="badge bg-info">Тренер: ${review.trainer_first_name} ${review.trainer_last_name}</span>`;
  } else if (review.service_id) {
    typeHtml = `<span class="badge bg-success">Услуга: ${review.service_name}</span>`;
  } else {
    typeHtml = '<span class="badge bg-secondary">Общий</span>';
  }
  document.getElementById("view-type").innerHTML = typeHtml;

  // Дата создания
  const createdDate = new Date(review.created_at);
  document.getElementById("view-created").textContent =
    createdDate.toLocaleString("ru-RU");

  // Статус
  const statusElement = document.getElementById("view-status");
  if (review.is_approved) {
    statusElement.className = "badge bg-success";
    statusElement.textContent = "Одобрен";
  } else {
    statusElement.className = "badge bg-warning text-dark";
    statusElement.textContent = "Ожидает одобрения";
  }

  // Информация о модераторе
  const moderatorContainer = document.getElementById(
    "view-moderator-container"
  );
  if (review.is_approved && review.moderator_first_name) {
    moderatorContainer.style.display = "block";
    document.getElementById(
      "view-moderator"
    ).textContent = `${review.moderator_first_name} ${review.moderator_last_name}`;
  } else {
    moderatorContainer.style.display = "none";
  }

  // Управление кнопками в зависимости от статуса
  const approveButton = document.getElementById("modal-approve-btn");
  if (review.is_approved) {
    approveButton.style.display = "none";
  } else {
    approveButton.style.display = "inline-block";
  }
}
