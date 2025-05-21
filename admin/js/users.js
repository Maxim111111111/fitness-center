// Скрипт для страницы управления пользователями
document.addEventListener("DOMContentLoaded", function () {
  // Обработчик изменения статуса пользователя
  const statusToggles = document.querySelectorAll(".status-toggle");
  statusToggles.forEach((toggle) => {
    toggle.addEventListener("change", function () {
      const userId = this.dataset.userId;
      const isActive = this.checked ? 1 : 0;

      // AJAX запрос на изменение статуса
      updateUserStatus(userId, isActive);
    });
  });

  // Обработчик изменения роли пользователя
  const roleSelects = document.querySelectorAll(".role-select");
  if (roleSelects.length > 0) {
    roleSelects.forEach((select) => {
      // Сохраняем исходное значение
      select.dataset.originalValue = select.value;

      select.addEventListener("change", function () {
        const userId = this.dataset.userId;
        const newRole = this.value;
        const originalRole = this.dataset.originalValue;

        // Подтверждение изменения роли
        if (
          confirm(
            `Вы уверены, что хотите изменить роль пользователя на "${getRoleName(
              newRole
            )}"?`
          )
        ) {
          updateUserRole(userId, newRole);
        } else {
          // Возвращаем предыдущее значение при отмене
          this.value = originalRole;
        }
      });
    });
  }

  // Обработка открытия модального окна редактирования
  const editUserModal = document.getElementById("editUserModal");
  if (editUserModal) {
    editUserModal.addEventListener("show.bs.modal", function (event) {
      const button = event.relatedTarget;
      const userId = button.getAttribute("data-user-id");

      // Загрузка данных пользователя
      loadUserData(userId);
    });
  }

  // Обработка открытия модального окна удаления
  const deleteUserModal = document.getElementById("deleteUserModal");
  if (deleteUserModal) {
    deleteUserModal.addEventListener("show.bs.modal", function (event) {
      const button = event.relatedTarget;
      const userId = button.getAttribute("data-user-id");

      document.getElementById("delete-user-id").value = userId;
    });
  }

  // Валидация формы добавления пользователя
  const addUserForm = document.getElementById("addUserForm");
  if (addUserForm) {
    addUserForm.addEventListener("submit", function (event) {
      if (!validateUserForm("add")) {
        event.preventDefault();
      }
    });
  }

  // Валидация формы редактирования пользователя
  const editUserForm = document.getElementById("editUserForm");
  if (editUserForm) {
    editUserForm.addEventListener("submit", function (event) {
      if (!validateUserForm("edit")) {
        event.preventDefault();
      }
    });
  }
});

// Функция обновления роли пользователя
function updateUserRole(userId, role) {
  // Формирование данных для отправки
  const formData = new FormData();
  formData.append("action", "change_role");
  formData.append("user_id", userId);
  formData.append("user_role", role);

  fetch("users.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showNotification("Роль пользователя успешно изменена", "success");
        // Обновляем сохраненное исходное значение
        const select = document.querySelector(
          `.role-select[data-user-id="${userId}"]`
        );
        if (select) {
          select.dataset.originalValue = role;
        }
      } else {
        showNotification(data.message || "Ошибка при изменении роли", "danger");
        // Возвращаем переключатель в исходное состояние
        const select = document.querySelector(
          `.role-select[data-user-id="${userId}"]`
        );
        if (select) {
          select.value = select.dataset.originalValue;
        }
      }
    })
    .catch((error) => {
      console.error("Ошибка:", error);
      showNotification("Произошла ошибка при обработке запроса", "danger");
      // Возвращаем переключатель в исходное состояние
      const select = document.querySelector(
        `.role-select[data-user-id="${userId}"]`
      );
      if (select) {
        select.value = select.dataset.originalValue;
      }
    });
}

// Функция обновления статуса пользователя
function updateUserStatus(userId, isActive) {
  // Формирование данных для отправки
  const formData = new FormData();
  formData.append("action", "change_status");
  formData.append("user_id", userId);
  formData.append("is_active", isActive);

  fetch("users.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showNotification("Статус пользователя успешно изменен", "success");
      } else {
        showNotification(
          data.message || "Ошибка при изменении статуса",
          "danger"
        );
        // Возвращаем переключатель в исходное состояние
        const toggle = document.querySelector(
          `.status-toggle[data-user-id="${userId}"]`
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
        `.status-toggle[data-user-id="${userId}"]`
      );
      if (toggle) {
        toggle.checked = !isActive;
      }
    });
}

// Вспомогательная функция для получения названия роли
function getRoleName(role) {
  switch (role) {
    case "admin":
      return "Администратор";
    case "manager":
      return "Менеджер";
    case "trainer":
      return "Тренер";
    case "user":
      return "Пользователь";
    default:
      return role;
  }
}

// Функция загрузки данных пользователя для редактирования
function loadUserData(userId) {
  // Устанавливаем ID пользователя в форме
  document.getElementById("edit-user-id").value = userId;

  // Запрос данных с сервера
  fetch(`get_user.php?id=${userId}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        const user = data.user;

        // Заполняем форму данными пользователя
        document.getElementById("edit-email").value = user.email;
        document.getElementById("edit-first-name").value = user.first_name;
        document.getElementById("edit-last-name").value = user.last_name;
        document.getElementById("edit-phone").value = user.phone || "";

        // Если доступен выбор роли
        const roleSelect = document.getElementById("edit-role");
        if (roleSelect) {
          roleSelect.value = user.role;
        }

        // Статус пользователя
        document.getElementById("edit-is-active").checked = user.is_active == 1;
      } else {
        showNotification("Не удалось загрузить данные пользователя", "danger");
      }
    })
    .catch((error) => {
      console.error("Ошибка при загрузке данных пользователя:", error);
      showNotification("Не удалось загрузить данные пользователя", "danger");
    });
}

// Функция валидации формы пользователя
function validateUserForm(formType) {
  const prefix = formType === "add" ? "add-" : "edit-";

  // Получаем значения полей
  const email = document.getElementById(`${prefix}email`).value.trim();
  const password = document.getElementById(`${prefix}password`).value;
  const firstName = document.getElementById(`${prefix}first-name`).value.trim();
  const lastName = document.getElementById(`${prefix}last-name`).value.trim();

  // Валидация email
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email)) {
    showNotification("Пожалуйста, введите корректный email", "danger");
    return false;
  }

  // При добавлении пользователя пароль обязателен
  if (formType === "add" && password.length < 6) {
    showNotification("Пароль должен содержать минимум 6 символов", "danger");
    return false;
  }

  // Проверка имени и фамилии
  if (firstName.length < 2) {
    showNotification("Имя должно содержать минимум 2 символа", "danger");
    return false;
  }

  if (lastName.length < 2) {
    showNotification("Фамилия должна содержать минимум 2 символа", "danger");
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
