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

      // Если открыта вкладка абонемента, запускаем обновление данных
      if (tabId === "profile-subscription") {
        updateRemainingSessionsCount();
        // Запускаем периодическое обновление
        clearInterval(updateInterval);
        updateInterval = setInterval(updateRemainingSessionsCount, 30000); // каждые 30 секунд
      } else {
        // Останавливаем обновление для других вкладок
        clearInterval(updateInterval);
      }
    });
  });

  // Проверяем, открыта ли вкладка абонемента при загрузке страницы
  const subscriptionTab = document.getElementById("profile-subscription");
  if (subscriptionTab && subscriptionTab.classList.contains("active")) {
    updateRemainingSessionsCount();
    updateInterval = setInterval(updateRemainingSessionsCount, 30000);
  }

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

      // Показываем индикатор загрузки на кнопке
      const submitButton = profileForm.querySelector('button[type="submit"]');
      const originalButtonText = submitButton.innerHTML;
      submitButton.innerHTML =
        '<span class="loading-spinner"></span> Сохранение...';
      submitButton.disabled = true;

      // Собираем данные формы
      const formData = new FormData();
      formData.append("action", "update_profile");
      formData.append(
        "first_name",
        document.getElementById("profile-name").value
      );
      formData.append(
        "last_name",
        document.getElementById("profile-surname").value
      );
      formData.append("phone", document.getElementById("profile-phone").value);
      formData.append(
        "birth_date",
        document.getElementById("profile-birthdate").value
      );
      formData.append(
        "gender",
        document.getElementById("profile-gender").value
      );

      // Отправляем данные на сервер
      fetch("profile_handler.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // Обновляем имя пользователя в шапке
            const userName = document.getElementById("userName");
            if (userName) {
              userName.textContent = `${
                document.getElementById("profile-name").value
              } ${document.getElementById("profile-surname").value}`;
            }

            // Показываем уведомление об успехе
            showNotification(data.message, "success");
          } else {
            // Показываем уведомление об ошибке
            showNotification(data.message, "error");
          }

          // Деактивируем поля после сохранения
          profileForm.querySelectorAll("input, select").forEach((field) => {
            field.disabled = true;
          });

          // Скрываем кнопки действий
          profileFormActions.style.display = "none";

          // Восстанавливаем кнопку
          submitButton.innerHTML = originalButtonText;
          submitButton.disabled = false;
        })
        .catch((error) => {
          console.error("Error:", error);
          showNotification("Произошла ошибка при сохранении данных", "error");

          // Восстанавливаем кнопку
          submitButton.innerHTML = originalButtonText;
          submitButton.disabled = false;
        });
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

      // Показываем индикатор загрузки на кнопке
      const submitButton = paramsForm.querySelector('button[type="submit"]');
      const originalButtonText = submitButton.innerHTML;
      submitButton.innerHTML =
        '<span class="loading-spinner"></span> Сохранение...';
      submitButton.disabled = true;

      // Собираем данные формы
      const formData = new FormData();
      formData.append("action", "update_params");
      formData.append(
        "height",
        document.getElementById("profile-height").value
      );
      formData.append(
        "weight",
        document.getElementById("profile-weight").value
      );

      // Отправляем данные на сервер
      fetch("profile_handler.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // Показываем уведомление об успехе
            showNotification(data.message, "success");
          } else {
            // Показываем уведомление об ошибке
            showNotification(data.message, "error");
          }

          // Деактивируем поля после сохранения
          paramsForm.querySelectorAll("input").forEach((field) => {
            field.disabled = true;
          });

          // Скрываем кнопки действий
          paramsFormActions.style.display = "none";

          // Восстанавливаем кнопку
          submitButton.innerHTML = originalButtonText;
          submitButton.disabled = false;
        })
        .catch((error) => {
          console.error("Error:", error);
          showNotification("Произошла ошибка при сохранении данных", "error");

          // Восстанавливаем кнопку
          submitButton.innerHTML = originalButtonText;
          submitButton.disabled = false;
        });
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
        showNotification("Пожалуйста, введите текущий пароль", "error");
        return;
      }

      if (!newPassword) {
        showNotification("Пожалуйста, введите новый пароль", "error");
        return;
      }

      if (newPassword.length < 6) {
        showNotification(
          "Новый пароль должен содержать не менее 6 символов",
          "error"
        );
        return;
      }

      if (newPassword !== confirmPassword) {
        showNotification("Пароли не совпадают", "error");
        return;
      }

      // Показываем индикатор загрузки на кнопке
      const submitButton = passwordForm.querySelector('button[type="submit"]');
      const originalButtonText = submitButton.innerHTML;
      submitButton.innerHTML =
        '<span class="loading-spinner"></span> Сохранение...';
      submitButton.disabled = true;

      // Собираем данные формы
      const formData = new FormData();
      formData.append("action", "change_password");
      formData.append("current_password", currentPassword);
      formData.append("new_password", newPassword);
      formData.append("confirm_password", confirmPassword);

      // Отправляем данные на сервер
      fetch("profile_handler.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // Показываем уведомление об успехе
            showNotification(data.message, "success");
            // Очищаем форму
            passwordForm.reset();
          } else {
            // Показываем уведомление об ошибке
            showNotification(data.message, "error");
          }

          // Восстанавливаем кнопку
          submitButton.innerHTML = originalButtonText;
          submitButton.disabled = false;
        })
        .catch((error) => {
          console.error("Error:", error);
          showNotification("Произошла ошибка при смене пароля", "error");

          // Восстанавливаем кнопку
          submitButton.innerHTML = originalButtonText;
          submitButton.disabled = false;
        });
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
          const file = this.files[0];

          // Проверка типа файла
          const allowedTypes = [
            "image/jpeg",
            "image/png",
            "image/gif",
            "image/webp",
          ];
          if (!allowedTypes.includes(file.type)) {
            showNotification(
              "Недопустимый тип файла. Разрешены только изображения (JPEG, PNG, GIF, WebP)",
              "error"
            );
            document.body.removeChild(fileInput);
            return;
          }

          // Проверка размера файла (не более 5 МБ)
          if (file.size > 5 * 1024 * 1024) {
            showNotification(
              "Размер файла превышает допустимый (5 МБ)",
              "error"
            );
            document.body.removeChild(fileInput);
            return;
          }

          // Показываем загруженное изображение
          const reader = new FileReader();
          reader.onload = function (e) {
            userAvatar.src = e.target.result;
          };
          reader.readAsDataURL(file);

          // Загружаем файл на сервер
          const formData = new FormData();
          formData.append("action", "update_avatar");
          formData.append("avatar", file);

          fetch("profile_handler.php", {
            method: "POST",
            body: formData,
          })
            .then((response) => response.json())
            .then((data) => {
              if (data.success) {
                // Обновляем аватар после успешной загрузки
                userAvatar.src = data.avatar_url;
                showNotification(data.message, "success");
              } else {
                // Показываем уведомление об ошибке
                showNotification(data.message, "error");
              }
            })
            .catch((error) => {
              console.error("Error:", error);
              showNotification(
                "Произошла ошибка при загрузке аватара",
                "error"
              );
            });
        }

        // Удаляем временный input
        document.body.removeChild(fileInput);
      });
    });
  }

  // Функция для отображения уведомлений
  function showNotification(message, type) {
    // Проверяем, нет ли уже уведомления
    let notification = document.querySelector(".notification");
    if (!notification) {
      notification = document.createElement("div");
      notification.className = "notification";
      document.body.appendChild(notification);
    }

    // Устанавливаем тип и сообщение
    notification.className = `notification ${type}`;
    notification.textContent = message;

    // Показываем уведомление
    notification.classList.add("show");

    // Скрываем уведомление через 3 секунды
    setTimeout(() => {
      notification.classList.remove("show");
    }, 3000);
  }

  // Обработка выхода из аккаунта
  const logoutBtn = document.getElementById("logoutBtn");
  if (logoutBtn) {
    logoutBtn.addEventListener("click", function (e) {
      e.preventDefault();

      // Подтверждение выхода
      if (confirm("Вы действительно хотите выйти из аккаунта?")) {
        // Перенаправление на страницу выхода
        window.location.href = this.href;
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

  // Обработчик отмены тренировки
  document.addEventListener("DOMContentLoaded", function () {
    // Находим все кнопки отмены тренировки
    const cancelButtons = document.querySelectorAll(".training-cancel");

    cancelButtons.forEach((button) => {
      button.addEventListener("click", function () {
        const trainingId = this.getAttribute("data-id");
        if (confirm("Вы уверены, что хотите отменить эту тренировку?")) {
          cancelTraining(trainingId);
        }
      });
    });
  });

  // Функция для отмены тренировки
  function cancelTraining(trainingId) {
    fetch("training_cancel_handler.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: "training_id=" + trainingId,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          // Показываем сообщение об успехе
          alert(data.message);
          // Перезагружаем страницу для обновления списка тренировок
          window.location.reload();
        } else {
          // Показываем сообщение об ошибке
          alert(data.message || "Произошла ошибка при отмене тренировки");
        }
      })
      .catch((error) => {
        console.error("Ошибка:", error);
        alert("Произошла ошибка при отмене тренировки");
      });
  }

  // Обработка уведомлений для абонементов
  document.addEventListener("DOMContentLoaded", function () {
    // Получаем параметры URL
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get("tab");
    const success = urlParams.get("success");
    const error = urlParams.get("error");
    const message = urlParams.get("message");

    // Если указана вкладка абонемента, переключаемся на нее
    if (tab === "profile-subscription") {
      const subscriptionTab = document.querySelector(
        '.profile-nav-link[data-tab="profile-subscription"]'
      );
      if (subscriptionTab) {
        subscriptionTab.click();
      }
    }

    // Показываем уведомление об успешной операции
    if (success) {
      let notificationMessage = "";
      switch (success) {
        case "purchased":
          notificationMessage = "Абонемент успешно приобретен";
          break;
        case "extended":
          notificationMessage = "Абонемент успешно продлен";
          break;
        case "cancelled":
          notificationMessage = "Абонемент отменен";
          break;
      }
      if (notificationMessage) {
        showNotification(notificationMessage, "success");
      }
    }

    // Показываем уведомление об ошибке
    if (error) {
      let errorMessage = message || "Произошла ошибка";
      switch (error) {
        case "invalid_subscription":
          errorMessage = "Неверный идентификатор абонемента";
          break;
        case "purchase":
          errorMessage = message || "Ошибка при покупке абонемента";
          break;
        case "cancel":
          errorMessage = message || "Ошибка при отмене абонемента";
          break;
      }
      showNotification(errorMessage, "error");
    }

    // Очищаем параметры URL без перезагрузки страницы
    if (success || error) {
      const newUrl = window.location.pathname + (tab ? `?tab=${tab}` : "");
      window.history.replaceState({}, "", newUrl);
    }
  });

  // Функция для обновления количества оставшихся тренировок
  function updateRemainingSessionsCount() {
    // Проверяем, активна ли вкладка с абонементом
    if ($("#profile-subscription").is(":visible")) {
      console.log("Обновляем количество оставшихся тренировок...");

      fetch("get_subscription_data.php")
        .then((response) => response.json())
        .then((data) => {
          console.log("Получены данные:", data);
          if (
            data.success &&
            data.subscription &&
            data.subscription.remaining_sessions !== undefined
          ) {
            $("#remaining-sessions-count").text(
              data.subscription.remaining_sessions
            );
            console.log(
              "Обновлено количество оставшихся тренировок:",
              data.subscription.remaining_sessions
            );
          }
        })
        .catch((error) => {
          console.error("Ошибка при обновлении количества тренировок:", error);
        });
    }
  }

  // Интервал для обновления количества оставшихся тренировок
  let updateInterval;

  // Запускаем обновление при активации вкладки с абонементом
  function startUpdating() {
    if ($("#profile-subscription").is(":visible")) {
      updateRemainingSessionsCount(); // Обновляем сразу
      updateInterval = setInterval(updateRemainingSessionsCount, 30000); // Затем каждые 30 секунд
    }
  }

  // Останавливаем обновление при переключении на другую вкладку
  function stopUpdating() {
    clearInterval(updateInterval);
  }

  // Обработчик переключения вкладок
  $(".profile-nav-link").on("click", function () {
    const tabId = $(this).data("tab");

    // Останавливаем текущий интервал обновления
    stopUpdating();

    // Если переключились на вкладку абонемента, запускаем обновление
    if (tabId === "profile-subscription") {
      startUpdating();
    }
  });

  // Запускаем обновление при загрузке страницы, если активна вкладка абонемента
  $(document).ready(function () {
    startUpdating();
  });
});
