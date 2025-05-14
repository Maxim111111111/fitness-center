// Скрипт для страницы управления абонементами
document.addEventListener("DOMContentLoaded", function () {
  // Обработчик изменения статуса абонемента
  const statusToggles = document.querySelectorAll(".status-toggle");
  statusToggles.forEach((toggle) => {
    toggle.addEventListener("change", function () {
      const subscriptionId = this.dataset.subscriptionId;
      const isActive = this.checked ? 1 : 0;

      // AJAX запрос на изменение статуса
      updateSubscriptionStatus(subscriptionId, isActive);
    });
  });

  // Обработка открытия модального окна редактирования
  const editSubscriptionModal = document.getElementById(
    "editSubscriptionModal"
  );
  if (editSubscriptionModal) {
    editSubscriptionModal.addEventListener("show.bs.modal", function (event) {
      const button = event.relatedTarget;
      const subscriptionId = button.getAttribute("data-subscription-id");

      // Загрузка данных абонемента
      loadSubscriptionData(subscriptionId);
    });
  }

  // Обработка открытия модального окна удаления
  const deleteSubscriptionModal = document.getElementById(
    "deleteSubscriptionModal"
  );
  if (deleteSubscriptionModal) {
    deleteSubscriptionModal.addEventListener("show.bs.modal", function (event) {
      const button = event.relatedTarget;
      const subscriptionId = button.getAttribute("data-subscription-id");

      document.getElementById("delete-subscription-id").value = subscriptionId;
    });
  }

  // Валидация формы добавления абонемента
  const addSubscriptionForm = document.getElementById("addSubscriptionForm");
  if (addSubscriptionForm) {
    addSubscriptionForm.addEventListener("submit", function (event) {
      if (!validateSubscriptionForm("add")) {
        event.preventDefault();
      }
    });
  }

  // Валидация формы редактирования абонемента
  const editSubscriptionForm = document.getElementById("editSubscriptionForm");
  if (editSubscriptionForm) {
    editSubscriptionForm.addEventListener("submit", function (event) {
      if (!validateSubscriptionForm("edit")) {
        event.preventDefault();
      }
    });
  }
});

// Функция обновления статуса абонемента
function updateSubscriptionStatus(subscriptionId, isActive) {
  // Формирование данных для отправки
  const formData = new FormData();
  formData.append("action", "change_status");
  formData.append("subscription_id", subscriptionId);
  formData.append("is_active", isActive);

  fetch("subscriptions.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showNotification("Статус абонемента успешно изменен", "success");
      } else {
        showNotification(
          data.message || "Ошибка при изменении статуса",
          "danger"
        );
        // Возвращаем переключатель в исходное состояние
        const toggle = document.querySelector(
          `.status-toggle[data-subscription-id="${subscriptionId}"]`
        );
        if (toggle) {
          toggle.checked = !isActive;
        }
      }
    })
    .catch((error) => {
      console.error("Ошибка:", error);
      showNotification("Произошла ошибка при обработке запроса", "danger");
      // Возвращаем переключатель в исходное состояние
      const toggle = document.querySelector(
        `.status-toggle[data-subscription-id="${subscriptionId}"]`
      );
      if (toggle) {
        toggle.checked = !isActive;
      }
    });
}

// Функция загрузки данных абонемента для редактирования
function loadSubscriptionData(subscriptionId) {
  // Устанавливаем ID абонемента в форме
  document.getElementById("edit-subscription-id").value = subscriptionId;

  // Запрос данных с сервера
  fetch(`get_subscription.php?id=${subscriptionId}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        const subscription = data.subscription;

        // Заполняем форму данными абонемента
        document.getElementById("edit-name").value = subscription.name;
        document.getElementById("edit-description").value =
          subscription.description || "";
        document.getElementById("edit-price").value = subscription.price;
        document.getElementById("edit-duration").value = subscription.duration;
        document.getElementById("edit-duration-type").value =
          subscription.duration_type;
        document.getElementById("edit-visit-limit").value =
          subscription.visit_limit || "";
        document.getElementById("edit-is-active").checked =
          subscription.is_active == 1;
      } else {
        showNotification("Не удалось загрузить данные абонемента", "danger");
      }
    })
    .catch((error) => {
      console.error("Ошибка при загрузке данных абонемента:", error);
      showNotification("Не удалось загрузить данные абонемента", "danger");
    });
}

// Функция валидации формы абонемента
function validateSubscriptionForm(formType) {
  const prefix = formType === "add" ? "add-" : "edit-";

  // Получаем значения полей
  const name = document.getElementById(`${prefix}name`).value.trim();
  const price = document.getElementById(`${prefix}price`).value;
  const duration = document.getElementById(`${prefix}duration`).value;

  // Проверка названия
  if (name.length < 3) {
    showNotification("Название должно содержать минимум 3 символа", "danger");
    return false;
  }

  // Проверка цены
  if (parseFloat(price) < 0) {
    showNotification("Цена не может быть отрицательной", "danger");
    return false;
  }

  // Проверка срока действия
  if (parseInt(duration) < 1) {
    showNotification("Срок действия должен быть не менее 1", "danger");
    return false;
  }

  return true;
}

// Функция отображения уведомления
function showNotification(message, type = "info") {
  // Создаем элемент уведомления
  const notification = document.createElement("div");
  notification.className = `alert alert-${type} alert-dismissible fade show`;
  notification.role = "alert";
  notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Закрыть"></button>
    `;

  // Вставляем в начало страницы
  const mainElement = document.querySelector("main");
  if (mainElement) {
    mainElement.insertBefore(notification, mainElement.firstChild);
  }

  // Автоматически скрываем через 5 секунд
  setTimeout(() => {
    notification.classList.remove("show");
    setTimeout(() => {
      notification.remove();
    }, 300);
  }, 5000);
}
