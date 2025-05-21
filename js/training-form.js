// Форма записи на тренировку
document.addEventListener("DOMContentLoaded", function () {
  const trainingForm = document.getElementById("trainingForm");

  if (trainingForm) {
    // Привязка календаря к полю даты с ограничением по дням недели
    const dateInput = document.getElementById("date");

    // Устанавливаем минимальную дату (сегодня)
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, "0");
    const dd = String(today.getDate()).padStart(2, "0");
    const formattedToday = `${yyyy}-${mm}-${dd}`;
    dateInput.min = formattedToday;

    // Максимальная дата (60 дней вперед)
    const maxDate = new Date();
    maxDate.setDate(maxDate.getDate() + 60);
    const maxYyyy = maxDate.getFullYear();
    const maxMm = String(maxDate.getMonth() + 1).padStart(2, "0");
    const maxDd = String(maxDate.getDate()).padStart(2, "0");
    const formattedMaxDate = `${maxYyyy}-${maxMm}-${maxDd}`;
    dateInput.max = formattedMaxDate;

    // Получаем список тренеров при загрузке страницы
    fetchTrainers();

    // Изменение списка доступных времен при выборе даты
    dateInput.addEventListener("change", function () {
      const selectedDate = this.value;
      if (selectedDate) {
        updateAvailableTimes(selectedDate);
      }
    });

    // Обновление списка тренеров при выборе типа тренировки
    const trainingTypeSelect = document.getElementById("training-type");
    trainingTypeSelect.addEventListener("change", function () {
      const selectedType = this.value;
      if (selectedType) {
        fetchTrainersByType(selectedType);
      }
    });

    // Обработка отправки формы
    trainingForm.addEventListener("submit", function (event) {
      event.preventDefault();

      // Скрыть предыдущие уведомления
      hideNotification();

      if (validateForm()) {
        submitForm();
      }
    });
  }
});

// Функция для получения списка тренеров
function fetchTrainers() {
  // Создаем запрос к серверу
  fetch("trainer_api.php?action=get_trainers")
    .then((response) => {
      if (!response.ok) {
        if (response.status === 401) {
          // Пользователь не авторизован
          showNotification(
            "Необходимо авторизоваться для использования этой функции",
            "error"
          );
          return Promise.reject("Unauthorized");
        }
        throw new Error("Ошибка сети при получении списка тренеров");
      }
      return response.json();
    })
    .then((data) => {
      if (data.success) {
        // Получаем ссылку на элемент select
        const trainerSelect = document.getElementById("trainer");

        // Очищаем текущие опции
        while (trainerSelect.options.length > 1) {
          trainerSelect.remove(1);
        }

        // Добавляем опции из полученных данных
        data.trainers.forEach((trainer) => {
          const option = document.createElement("option");
          option.value = trainer.id;
          option.textContent = trainer.name;
          trainerSelect.appendChild(option);
        });
      } else {
        console.error("Ошибка при загрузке тренеров:", data.message);
      }
    })
    .catch((error) => {
      if (error === "Unauthorized") return; // Уже показали уведомление

      console.error("Ошибка при загрузке тренеров:", error);
      // Используем запасной вариант с демо-данными
      console.log("Используем встроенные демо-данные для тренеров");

      const demoTrainers = [
        { id: 1, name: "Иванов Иван" },
        { id: 2, name: "Петрова Мария" },
        { id: 3, name: "Сидоров Алексей" },
        { id: 4, name: "Козлова Анна" },
      ];

      const trainerSelect = document.getElementById("trainer");

      // Очищаем текущие опции, кроме первой (placeholder)
      while (trainerSelect.options.length > 1) {
        trainerSelect.remove(1);
      }

      // Добавляем опции из демо-данных
      demoTrainers.forEach((trainer) => {
        const option = document.createElement("option");
        option.value = trainer.id;
        option.textContent = trainer.name;
        trainerSelect.appendChild(option);
      });
    });
}

// Функция для получения списка тренеров по типу тренировки
function fetchTrainersByType(trainingType) {
  // Показываем индикатор загрузки
  const trainerSelect = document.getElementById("trainer");
  trainerSelect.disabled = true;

  // В реальном проекте это был бы запрос к API
  fetch(`trainer_api.php?action=get_trainers_by_type&type=${trainingType}`)
    .then((response) => {
      if (!response.ok) {
        if (response.status === 401) {
          // Пользователь не авторизован
          showNotification(
            "Необходимо авторизоваться для использования этой функции",
            "error"
          );
          return Promise.reject("Unauthorized");
        }
        throw new Error("Ошибка сети при получении тренеров");
      }
      return response.json();
    })
    .then((data) => {
      // Очищаем текущие опции, кроме первой (placeholder)
      while (trainerSelect.options.length > 1) {
        trainerSelect.remove(1);
      }

      if (data.success && data.trainers) {
        // Добавляем опции из полученных данных
        data.trainers.forEach((trainer) => {
          const option = document.createElement("option");
          option.value = trainer.id;
          option.textContent = trainer.name;
          trainerSelect.appendChild(option);
        });
      } else {
        // Используем демо-данные, если API недоступно
        let trainers = [];

        switch (trainingType) {
          case "personal":
            trainers = [
              { id: 1, name: "Иван Иванов" },
              { id: 2, name: "Петр Петров" },
            ];
            break;
          case "group":
            trainers = [
              { id: 3, name: "Анна Сидорова" },
              { id: 4, name: "Мария Кузнецова" },
            ];
            break;
          case "pool":
            trainers = [
              { id: 5, name: "Алексей Морозов" },
              { id: 6, name: "Екатерина Волкова" },
            ];
            break;
          case "gym":
            trainers = [
              { id: 7, name: "Дмитрий Соколов" },
              { id: 8, name: "Сергей Новиков" },
            ];
            break;
        }

        // Добавляем опции в выпадающий список
        trainers.forEach((trainer) => {
          const option = document.createElement("option");
          option.value = trainer.id;
          option.textContent = trainer.name;
          trainerSelect.appendChild(option);
        });
      }
    })
    .catch((error) => {
      if (error === "Unauthorized") return; // Уже показали уведомление

      console.error("Ошибка при загрузке тренеров:", error);
      // Используем обработку ошибки и демо-данные (код из предыдущей версии)
      let trainers = [];

      switch (trainingType) {
        case "personal":
          trainers = [
            { id: 1, name: "Иван Иванов" },
            { id: 2, name: "Петр Петров" },
          ];
          break;
        case "group":
          trainers = [
            { id: 3, name: "Анна Сидорова" },
            { id: 4, name: "Мария Кузнецова" },
          ];
          break;
        case "pool":
          trainers = [
            { id: 5, name: "Алексей Морозов" },
            { id: 6, name: "Екатерина Волкова" },
          ];
          break;
        case "gym":
          trainers = [
            { id: 7, name: "Дмитрий Соколов" },
            { id: 8, name: "Сергей Новиков" },
          ];
          break;
      }

      // Добавляем опции в выпадающий список
      trainers.forEach((trainer) => {
        const option = document.createElement("option");
        option.value = trainer.id;
        option.textContent = trainer.name;
        trainerSelect.appendChild(option);
      });
    })
    .finally(() => {
      trainerSelect.disabled = false;
    });
}

// Функция для обновления доступных времен
function updateAvailableTimes(date) {
  const timeSelect = document.getElementById("time");
  timeSelect.disabled = true;

  // Очищаем все опции, кроме первой (placeholder)
  while (timeSelect.options.length > 1) {
    timeSelect.remove(1);
  }

  // Показываем сообщение о загрузке
  timeSelect.options[0].text = "Загрузка доступного времени...";

  // Запрос к API для получения свободных слотов
  fetch(`training_api.php?action=get_available_times&date=${date}`)
    .then((response) => {
      if (!response.ok) {
        if (response.status === 401) {
          // Пользователь не авторизован
          showNotification(
            "Необходимо авторизоваться для использования этой функции",
            "error"
          );
          return Promise.reject("Unauthorized");
        }
        throw new Error("Ошибка сети при получении доступного времени");
      }
      return response.json();
    })
    .then((data) => {
      if (data.success && data.times) {
        // Добавляем доступные часы из API
        populateTimeSelect(data.times);
      } else {
        // Генерируем стандартные рабочие часы (с 8:00 до 22:00)
        const availableTimes = [];
        for (let hour = 8; hour <= 21; hour++) {
          availableTimes.push(`${hour.toString().padStart(2, "0")}:00`);
        }
        populateTimeSelect(availableTimes);
      }
    })
    .catch((error) => {
      if (error === "Unauthorized") return; // Уже показали уведомление

      console.error("Ошибка при получении доступного времени:", error);

      // В случае ошибки, генерируем стандартные рабочие часы
      const availableTimes = [];
      for (let hour = 8; hour <= 21; hour++) {
        availableTimes.push(`${hour.toString().padStart(2, "0")}:00`);
      }
      populateTimeSelect(availableTimes);
    })
    .finally(() => {
      // Активируем селект
      timeSelect.disabled = false;
    });
}

// Функция для заполнения селекта времени
function populateTimeSelect(times) {
  const timeSelect = document.getElementById("time");

  // Очищаем все опции
  while (timeSelect.options.length > 0) {
    timeSelect.remove(0);
  }

  // Добавляем placeholder
  const placeholderOption = document.createElement("option");
  placeholderOption.value = "";
  placeholderOption.text = "Выберите время";
  placeholderOption.disabled = true;
  placeholderOption.selected = true;
  timeSelect.add(placeholderOption);

  if (times && times.length > 0) {
    // Добавляем доступные времена
    times.forEach((time) => {
      const option = document.createElement("option");
      option.value = time;
      option.text = time;
      timeSelect.add(option);
    });
  } else {
    // Если нет доступных времен
    const noTimeOption = document.createElement("option");
    noTimeOption.value = "";
    noTimeOption.text = "Нет доступного времени";
    noTimeOption.disabled = true;
    timeSelect.add(noTimeOption);
  }
}

// Функция для валидации формы
function validateForm() {
  const name = document.getElementById("name").value;
  const phone = document.getElementById("phone").value;
  const email = document.getElementById("email").value;
  const trainingType = document.getElementById("training-type").value;
  const date = document.getElementById("date").value;
  const time = document.getElementById("time").value;
  const trainer = document.getElementById("trainer").value;

  if (
    !name ||
    !phone ||
    !email ||
    !trainingType ||
    !date ||
    !time ||
    !trainer
  ) {
    showNotification("Пожалуйста, заполните все обязательные поля", "error");
    return false;
  }

  // Валидация email
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email)) {
    showNotification("Пожалуйста, введите корректный email", "error");
    return false;
  }

  // Валидация телефона
  const phoneRegex = /^\+?[0-9\s-()]{10,17}$/;
  if (!phoneRegex.test(phone)) {
    showNotification("Пожалуйста, введите корректный номер телефона", "error");
    return false;
  }

  return true;
}

// Функция для отправки формы
function submitForm() {
  const form = document.getElementById("trainingForm");
  const formData = new FormData(form);

  // Получаем кнопку отправки для изменения её состояния
  const submitButton = form.querySelector('button[type="submit"]');
  const originalButtonText = submitButton.textContent;

  // Делаем кнопку неактивной и меняем текст
  submitButton.disabled = true;
  submitButton.innerHTML = '<span class="loading-spinner"></span> Отправка...';

  // Отправляем данные на сервер
  fetch("training_session_handler.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      console.log("Response status:", response.status);
      if (!response.ok) {
        if (response.status === 401) {
          throw new Error("Необходимо авторизоваться для записи на тренировку");
        }
        return response.json().then((data) => {
          throw new Error(data.message || "Ошибка сети при отправке формы");
        });
      }
      return response.json();
    })
    .then((data) => {
      console.log("Server response:", data);
      if (data.success) {
        // Успешная запись на тренировку
        showNotification(data.message, "success");

        // Сбрасываем форму
        form.reset();

        // Скрываем форму и показываем сообщение об успехе
        const formWrapper = document.querySelector(".training-form__wrapper");
        const successMessage = document.createElement("div");
        successMessage.className = "training-form__success";
        successMessage.innerHTML = `
          <h2 class="training-form__title">Запись успешно создана!</h2>
          <p class="training-form__subtitle">
            Мы получили вашу заявку и свяжемся с вами в ближайшее время для подтверждения.
          </p>
          <p class="training-form__booking-id">Номер вашей записи: ${data.bookingId}</p>
          <a href="index.php" class="training-form__home-button">Вернуться на главную</a>
        `;

        formWrapper.innerHTML = "";
        formWrapper.appendChild(successMessage);
      } else {
        // Ошибка при записи
        showNotification(
          data.message || "Произошла ошибка при отправке формы",
          "error"
        );
      }
    })
    .catch((error) => {
      console.error("Ошибка:", error);
      showNotification(
        error.message ||
          "Произошла ошибка при отправке формы. Пожалуйста, попробуйте позже.",
        "error"
      );
    })
    .finally(() => {
      // Восстанавливаем кнопку
      submitButton.disabled = false;
      submitButton.textContent = originalButtonText;
    });
}

// Функция для отображения уведомлений
function showNotification(message, type) {
  hideNotification(); // Скрываем предыдущие уведомления

  // Создаем элемент уведомления
  const notification = document.createElement("div");
  notification.className = `notification notification-${type}`;
  notification.id = "form-notification";
  notification.innerHTML = `
    <div class="notification-icon">
      ${
        type === "success"
          ? '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/></svg>'
          : '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/></svg>'
      }
    </div>
    <div class="notification-content">
      <div class="notification-message">${message}</div>
    </div>
    <button class="notification-close" onclick="hideNotification()">
      <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M13 1L1 13M1 1L13 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </button>
  `;

  // Добавляем уведомление в DOM
  document.body.appendChild(notification);

  // Показываем уведомление (с анимацией)
  setTimeout(() => {
    notification.classList.add("show");
  }, 10);

  // Автоматически скрываем через 5 секунд
  if (type === "success") {
    setTimeout(hideNotification, 5000);
  }
}

// Функция для скрытия уведомления
function hideNotification() {
  const notification = document.getElementById("form-notification");
  if (notification) {
    notification.classList.remove("show");
    setTimeout(() => {
      notification.remove();
    }, 300); // Время анимации скрытия
  }
}

// Добавляем функцию в глобальную область видимости для кнопки закрытия
window.hideNotification = hideNotification;
