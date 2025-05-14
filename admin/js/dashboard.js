// Скрипт для страницы дашборда
document.addEventListener("DOMContentLoaded", function () {
  // Загрузка счетчиков для карточек
  loadCounters();

  // Загрузка последних записей на тренировки
  loadRecentBookings();

  // Загрузка данных для графиков
  loadCharts();

  // Обновление данных каждые 5 минут
  setInterval(function () {
    loadCounters();
    loadRecentBookings();
  }, 300000);
});

// Загрузка счетчиков для карточек статистики
function loadCounters() {
  // В реальной системе здесь был бы AJAX запрос к API
  // Имитация загрузки данных
  setTimeout(() => {
    document.getElementById("usersCount").textContent = "356";
    document.getElementById("sessionsCount").textContent = "127";
    document.getElementById("trainersCount").textContent = "18";
    document.getElementById("reviewsCount").textContent = "42";
  }, 500);
}

// Загрузка последних записей на тренировки
function loadRecentBookings() {
  // В реальной системе здесь был бы AJAX запрос к API
  // Имитация загрузки данных
  setTimeout(() => {
    const bookingsTableBody = document.getElementById("recentBookings");

    // Очистка текущих данных
    bookingsTableBody.innerHTML = "";

    // Демо-данные
    const bookings = [
      {
        id: 1245,
        user: "Иван Петров",
        trainer: "Алексей Морозов",
        service: "Персональная тренировка",
        date: "2023-06-15",
        time: "10:00",
        status: "confirmed",
      },
      {
        id: 1244,
        user: "Мария Сидорова",
        trainer: "Екатерина Волкова",
        service: "Групповое занятие йогой",
        date: "2023-06-15",
        time: "12:00",
        status: "confirmed",
      },
      {
        id: 1243,
        user: "Алексей Иванов",
        trainer: "Дмитрий Соколов",
        service: "Силовая тренировка",
        date: "2023-06-15",
        time: "14:00",
        status: "pending",
      },
      {
        id: 1242,
        user: "Елена Кузнецова",
        trainer: "Анна Сидорова",
        service: "Аквааэробика",
        date: "2023-06-14",
        time: "17:00",
        status: "completed",
      },
      {
        id: 1241,
        user: "Сергей Смирнов",
        trainer: "Иван Иванов",
        service: "Тренажерный зал",
        date: "2023-06-14",
        time: "19:00",
        status: "cancelled",
      },
    ];

    // Заполнение таблицы данными
    bookings.forEach((booking) => {
      const row = document.createElement("tr");

      // ID
      const idCell = document.createElement("td");
      idCell.textContent = booking.id;
      row.appendChild(idCell);

      // Пользователь
      const userCell = document.createElement("td");
      userCell.textContent = booking.user;
      row.appendChild(userCell);

      // Тренер
      const trainerCell = document.createElement("td");
      trainerCell.textContent = booking.trainer;
      row.appendChild(trainerCell);

      // Услуга
      const serviceCell = document.createElement("td");
      serviceCell.textContent = booking.service;
      row.appendChild(serviceCell);

      // Дата
      const dateCell = document.createElement("td");
      dateCell.textContent = formatDate(booking.date);
      row.appendChild(dateCell);

      // Время
      const timeCell = document.createElement("td");
      timeCell.textContent = booking.time;
      row.appendChild(timeCell);

      // Статус
      const statusCell = document.createElement("td");
      const statusSpan = document.createElement("span");
      statusSpan.className = `status-badge status-${booking.status}`;

      switch (booking.status) {
        case "pending":
          statusSpan.textContent = "Ожидает";
          break;
        case "confirmed":
          statusSpan.textContent = "Подтверждено";
          break;
        case "cancelled":
          statusSpan.textContent = "Отменено";
          break;
        case "completed":
          statusSpan.textContent = "Завершено";
          break;
      }

      statusCell.appendChild(statusSpan);
      row.appendChild(statusCell);

      bookingsTableBody.appendChild(row);
    });
  }, 500);
}

// Загрузка данных для графиков
function loadCharts() {
  // График записей на тренировки по дням
  const bookingsCtx = document.getElementById("bookingsChart").getContext("2d");
  new Chart(bookingsCtx, {
    type: "bar",
    data: {
      labels: ["Пн", "Вт", "Ср", "Чт", "Пт", "Сб", "Вс"],
      datasets: [
        {
          label: "Количество записей",
          data: [12, 19, 15, 17, 28, 24, 7],
          backgroundColor: "rgba(54, 162, 235, 0.5)",
          borderColor: "rgba(54, 162, 235, 1)",
          borderWidth: 1,
        },
      ],
    },
    options: {
      scales: {
        y: {
          beginAtZero: true,
        },
      },
      plugins: {
        title: {
          display: true,
          text: "Записи на тренировки по дням недели",
        },
      },
    },
  });

  // График популярности услуг
  const servicesCtx = document.getElementById("servicesChart").getContext("2d");
  new Chart(servicesCtx, {
    type: "pie",
    data: {
      labels: [
        "Персональные тренировки",
        "Групповые занятия",
        "Тренажерный зал",
        "Аквааэробика",
        "Йога",
      ],
      datasets: [
        {
          label: "Популярность услуг",
          data: [30, 25, 20, 15, 10],
          backgroundColor: [
            "rgba(255, 99, 132, 0.5)",
            "rgba(54, 162, 235, 0.5)",
            "rgba(255, 206, 86, 0.5)",
            "rgba(75, 192, 192, 0.5)",
            "rgba(153, 102, 255, 0.5)",
          ],
          borderColor: [
            "rgba(255, 99, 132, 1)",
            "rgba(54, 162, 235, 1)",
            "rgba(255, 206, 86, 1)",
            "rgba(75, 192, 192, 1)",
            "rgba(153, 102, 255, 1)",
          ],
          borderWidth: 1,
        },
      ],
    },
    options: {
      plugins: {
        title: {
          display: true,
          text: "Популярность услуг",
        },
      },
    },
  });
}

// Функция форматирования даты
function formatDate(dateString) {
  const options = { year: "numeric", month: "numeric", day: "numeric" };
  const date = new Date(dateString);
  return date.toLocaleDateString("ru-RU", options);
}
