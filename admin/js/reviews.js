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

  // Обработчик отклонения отзыва
  const rejectButtons = document.querySelectorAll(".reject-review");
  rejectButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const reviewId = this.getAttribute("data-review-id");
      document.getElementById("reject-review-id").value = reviewId;
      document.getElementById("rejectReviewForm").submit();
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

  // Обработчик отклонения из модального окна
  document
    .querySelector(".reject-from-modal")
    .addEventListener("click", function () {
      const reviewId = document.getElementById("view-id").textContent;
      document.getElementById("reject-review-id").value = reviewId;
      document.getElementById("rejectReviewForm").submit();
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
  document.getElementById("view-name").textContent = review.name;
  document.getElementById("view-email").textContent = review.email;

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
  document.getElementById("view-text").textContent = review.text;

  // Дата создания
  const createdDate = new Date(review.created_at);
  document.getElementById("view-created").textContent =
    createdDate.toLocaleString("ru-RU");

  // Отображение даты обновления, если есть
  const updatedContainer = document.getElementById("view-updated-container");
  if (review.updated_at && review.updated_at !== review.created_at) {
    updatedContainer.style.display = "block";
    const updatedDate = new Date(review.updated_at);
    document.getElementById("view-updated").textContent =
      updatedDate.toLocaleString("ru-RU");
  } else {
    updatedContainer.style.display = "none";
  }

  // Статус
  const statusElement = document.getElementById("view-status");
  if (review.status === "approved") {
    statusElement.className = "badge bg-success";
    statusElement.textContent = "Одобрен";
  } else if (review.status === "rejected") {
    statusElement.className = "badge bg-danger";
    statusElement.textContent = "Отклонен";
  } else {
    statusElement.className = "badge bg-warning text-dark";
    statusElement.textContent = "Ожидает проверки";
  }

  // Управление кнопками в зависимости от статуса
  const approveButton = document.getElementById("modal-approve-btn");
  const rejectButton = document.getElementById("modal-reject-btn");

  if (review.status === "pending") {
    approveButton.style.display = "inline-block";
    rejectButton.style.display = "inline-block";
  } else {
    approveButton.style.display = "none";
    rejectButton.style.display = "none";
  }
}
