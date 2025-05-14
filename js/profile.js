document.addEventListener("DOMContentLoaded", function () {
  // Переключение вкладок профиля
  const profileTabs = document.querySelectorAll(".profile-nav-link[data-tab]");
  const profileContentTabs = document.querySelectorAll(".profile-tab");

  profileTabs.forEach((tab) => {
    tab.addEventListener("click", function (e) {
      e.preventDefault();

      // Удаляем активный класс со всех табов и добавляем к текущему
      profileTabs.forEach((t) => t.parentElement.classList.remove("active"));
      this.parentElement.classList.add("active");

      // Скрываем все панели контента и показываем только текущую
      const tabId = this.getAttribute("data-tab");
      profileContentTabs.forEach((panel) => panel.classList.remove("active"));
      document.getElementById(tabId).classList.add("active");

      // Обновляем URL без перезагрузки страницы
      history.pushState(null, null, this.getAttribute("href"));
    });
  });

  // Редактирование профиля
  const editProfileBtn = document.getElementById("editProfileBtn");
  const profileForm = document.getElementById("profileForm");
  const profileFormActions = document.getElementById("profileFormActions");
  const cancelProfileBtn = document.getElementById("cancelProfileBtn");

  if (editProfileBtn && profileForm) {
    // Сохраняем исходные значения полей для возможности отмены
    const originalProfileValues = {};

    editProfileBtn.addEventListener("click", function () {
      // Сохраняем текущие значения
      profileForm.querySelectorAll("input, select").forEach((field) => {
        originalProfileValues[field.id] = field.value;
        field.disabled = false;
      });

      // Показываем кнопки действий
      profileFormActions.style.display = "flex";
    });

    // Отмена редактирования
    if (cancelProfileBtn) {
      cancelProfileBtn.addEventListener("click", function () {
        // Возвращаем исходные значения
        for (const [id, value] of Object.entries(originalProfileValues)) {
          document.getElementById(id).value = value;
          document.getElementById(id).disabled = true;
        }

        // Скрываем кнопки действий
        profileFormActions.style.display = "none";
      });
    }

    // Обработка отправки формы
    profileForm.addEventListener("submit", function (e) {
      e.preventDefault();

      // Здесь будет код для отправки данных на сервер
      console.log("Отправка формы профиля");

      // Деактивируем поля после сохранения
      profileForm.querySelectorAll("input, select").forEach((field) => {
        field.disabled = true;
      });

      // Скрываем кнопки действий
      profileFormActions.style.display = "none";

      // Показываем уведомление об успешном сохранении
      alert("Данные профиля успешно сохранены");
    });
  }

  // Редактирование физических параметров
  const editParamsBtn = document.getElementById("editParamsBtn");
  const paramsForm = document.getElementById("paramsForm");
  const paramsFormActions = document.getElementById("paramsFormActions");
  const cancelParamsBtn = document.getElementById("cancelParamsBtn");

  if (editParamsBtn && paramsForm) {
    // Сохраняем исходные значения полей для возможности отмены
    const originalParamsValues = {};

    editParamsBtn.addEventListener("click", function () {
      // Сохраняем текущие значения
      paramsForm.querySelectorAll("input").forEach((field) => {
        originalParamsValues[field.id] = field.value;
        field.disabled = false;
      });

      // Показываем кнопки действий
      paramsFormActions.style.display = "flex";
    });

    // Отмена редактирования
    if (cancelParamsBtn) {
      cancelParamsBtn.addEventListener("click", function () {
        // Возвращаем исходные значения
        for (const [id, value] of Object.entries(originalParamsValues)) {
          document.getElementById(id).value = value;
          document.getElementById(id).disabled = true;
        }

        // Скрываем кнопки действий
        paramsFormActions.style.display = "none";
      });
    }

    // Обработка отправки формы
    paramsForm.addEventListener("submit", function (e) {
      e.preventDefault();

      // Здесь будет код для отправки данных на сервер
      console.log("Отправка формы физических параметров");

      // Деактивируем поля после сохранения
      paramsForm.querySelectorAll("input").forEach((field) => {
        field.disabled = true;
      });

      // Скрываем кнопки действий
      paramsFormActions.style.display = "none";

      // Показываем уведомление об успешном сохранении
      alert("Физические параметры успешно сохранены");
    });
  }

  // Обработка формы смены пароля
  const passwordForm = document.getElementById("passwordForm");
  if (passwordForm) {
    passwordForm.addEventListener("submit", function (e) {
      e.preventDefault();

      const currentPassword = document.getElementById("current-password").value;
      const newPassword = document.getElementById("new-password").value;
      const confirmPassword = document.getElementById("confirm-password").value;

      if (!currentPassword) {
        alert("Пожалуйста, введите текущий пароль");
        return;
      }

      if (!newPassword) {
        alert("Пожалуйста, введите новый пароль");
        return;
      }

      if (newPassword.length < 6) {
        alert("Новый пароль должен содержать не менее 6 символов");
        return;
      }

      if (newPassword !== confirmPassword) {
        alert("Пароли не совпадают");
        return;
      }

      // Здесь будет код для отправки данных на сервер
      console.log("Отправка формы смены пароля");

      // Очищаем поля после отправки
      passwordForm.reset();

      // Показываем уведомление об успешном сохранении
      alert("Пароль успешно изменен");
    });
  }

  // Обработка формы настроек уведомлений
  const notificationForm = document.getElementById("notificationForm");
  if (notificationForm) {
    notificationForm.addEventListener("submit", function (e) {
      e.preventDefault();

      // Здесь будет код для отправки данных на сервер
      console.log("Отправка формы настроек уведомлений");

      // Показываем уведомление об успешном сохранении
      alert("Настройки уведомлений успешно сохранены");
    });
  }

  // Показ/скрытие пароля
  const passwordToggles = document.querySelectorAll(".password-toggle");
  passwordToggles.forEach((toggle) => {
    toggle.addEventListener("click", function () {
      const input = this.parentElement.querySelector("input");
      const icon = this.querySelector("img");

      if (input.type === "password") {
        input.type = "text";
        icon.src = "assets/svg/eye-off.svg";
      } else {
        input.type = "password";
        icon.src = "assets/svg/eye.svg";
      }
    });
  });

  // Загрузка аватара
  const changeAvatarBtn = document.getElementById("changeAvatarBtn");
  const userAvatar = document.getElementById("userAvatar");

  if (changeAvatarBtn && userAvatar) {
    changeAvatarBtn.addEventListener("click", function () {
      // Создаем скрытый input для загрузки файла
      const fileInput = document.createElement("input");
      fileInput.type = "file";
      fileInput.accept = "image/*";
      fileInput.style.display = "none";
      document.body.appendChild(fileInput);

      fileInput.click();

      fileInput.addEventListener("change", function () {
        if (this.files && this.files[0]) {
          const reader = new FileReader();

          reader.onload = function (e) {
            userAvatar.src = e.target.result;

            // Здесь будет код для отправки файла на сервер
            console.log("Загрузка нового аватара");
          };

          reader.readAsDataURL(this.files[0]);
        }

        // Удаляем временный input
        document.body.removeChild(fileInput);
      });
    });
  }

  // Обработка выхода из аккаунта
  const logoutBtn = document.getElementById("logoutBtn");
  if (logoutBtn) {
    logoutBtn.addEventListener("click", function (e) {
      e.preventDefault();

      // Подтверждение выхода
      if (confirm("Вы действительно хотите выйти из аккаунта?")) {
        // Здесь будет код для выхода из аккаунта
        console.log("Выход из аккаунта");

        // Перенаправление на главную страницу
        window.location.href = "index.php";
      }
    });
  }

  // Отмена тренировки
  const cancelTrainingBtns = document.querySelectorAll(".training-cancel");
  cancelTrainingBtns.forEach((button) => {
    button.addEventListener("click", function () {
      // Получаем информацию о тренировке для вывода в сообщении
      const trainingCard = this.closest(".training-card");
      const trainingTitle =
        trainingCard.querySelector(".training-title").textContent;
      const trainingDate =
        trainingCard.querySelector(".training-day").textContent;
      const trainingMonth =
        trainingCard.querySelector(".training-month").textContent;

      // Подтверждение отмены
      if (
        confirm(
          `Вы действительно хотите отменить тренировку "${trainingTitle}" ${trainingDate} ${trainingMonth}?`
        )
      ) {
        // Здесь будет код для отмены тренировки на сервере
        console.log(
          "Отмена тренировки:",
          trainingTitle,
          trainingDate,
          trainingMonth
        );

        // Удаляем карточку тренировки из DOM
        trainingCard.remove();
      }
    });
  });

  // Активация вкладки при загрузке страницы по хеш-фрагменту URL
  function activateTabFromHash() {
    const hash = window.location.hash || "#profile-main";
    const tabLink = document.querySelector(`.profile-nav-link[href="${hash}"]`);

    if (tabLink) {
      tabLink.click();
    }
  }

  // Вызываем при загрузке страницы
  activateTabFromHash();

  // Обработка изменения хеш-фрагмента URL
  window.addEventListener("hashchange", activateTabFromHash);
});
