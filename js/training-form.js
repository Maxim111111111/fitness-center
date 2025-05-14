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

      if (validateForm()) {
        submitForm();
      }
    });
  }
});

// Функция для получения списка тренеров
function fetchTrainers() {
  // В реальном проекте здесь был бы запрос к API
  // Но сейчас просто имитируем загрузку данных
  console.log("Загружаем список тренеров...");

  // Имитация запроса к серверу
  setTimeout(() => {
    console.log("Список тренеров загружен");
  }, 500);
}

// Функция для получения списка тренеров по типу тренировки
function fetchTrainersByType(trainingType) {
  // В реальном проекте здесь был бы запрос к API
  console.log(`Загружаем тренеров для типа: ${trainingType}`);

  // Имитация запроса к серверу
  setTimeout(() => {
    const trainerSelect = document.getElementById("trainer");

    // Очищаем текущие опции, кроме первой (placeholder)
    while (trainerSelect.options.length > 1) {
      trainerSelect.remove(1);
    }

    // Добавляем новые опции в зависимости от типа тренировки
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

    console.log("Список тренеров обновлен");
  }, 300);
}

// Функция для обновления доступных времен
function updateAvailableTimes(date) {
  // В реальном проекте здесь был бы запрос к API
  console.log(`Загружаем доступное время на: ${date}`);

  // Имитация запроса к серверу
  setTimeout(() => {
    const timeInput = document.getElementById("time");

    // Ограничиваем время рабочими часами (8:00 - 22:00)
    timeInput.min = "08:00";
    timeInput.max = "22:00";

    console.log("Доступное время обновлено");
  }, 300);
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
    alert("Пожалуйста, заполните все обязательные поля");
    return false;
  }

  // Валидация email
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email)) {
    alert("Пожалуйста, введите корректный email");
    return false;
  }

  // Валидация телефона
  const phoneRegex = /^\+?[0-9\s-()]{10,17}$/;
  if (!phoneRegex.test(phone)) {
    alert("Пожалуйста, введите корректный номер телефона");
    return false;
  }

  return true;
}

// Функция для отправки формы
function submitForm() {
  const formData = new FormData(document.getElementById("trainingForm"));
  const formObject = {};

  formData.forEach((value, key) => {
    formObject[key] = value;
  });

  console.log("Отправляем данные формы:", formObject);

  // Имитация отправки данных на сервер
  setTimeout(() => {
    console.log("Форма успешно отправлена");

    // Показываем сообщение об успешной отправке
    alert(
      "Ваша заявка на тренировку успешно отправлена! Мы свяжемся с вами в ближайшее время."
    );

    // Сбрасываем форму
    document.getElementById("trainingForm").reset();
  }, 1000);
}
