// Скрипт для страницы управления записями на тренировки
document.addEventListener("DOMContentLoaded", function () {
  // Обработчик изменения статуса записи
  const statusSelects = document.querySelectorAll(".status-select");
  statusSelects.forEach((select) => {
    select.addEventListener("change", function () {
      const sessionId = this.dataset.sessionId;
      const newStatus = this.value;

      // Подтверждение изменения статуса
      if (
        confirm(
          `Вы уверены, что хотите изменить статус записи на "${getStatusName(
            newStatus
          )}"?`
        )
      ) {
        // AJAX запрос на изменение статуса
        updateSessionStatus(sessionId, newStatus);
      } else {
        // Возвращаем предыдущее значение
        this.value =
          this.getAttribute("data-original-value") || this.options[0].value;
      }
    });

    // Сохраняем оригинальное значение
    select.setAttribute("data-original-value", select.value);
  });

  // Обработчик просмотра деталей записи
  const viewButtons = document.querySelectorAll(".view-session");
  viewButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const sessionId = this.dataset.sessionId;
      loadSessionDetails(sessionId);
    });
  });

  // Обработчик удаления записи
  const deleteButtons = document.querySelectorAll(
    '[data-bs-target="#deleteSessionModal"]'
  );
  deleteButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const sessionId = this.dataset.sessionId;
      document.getElementById("delete-session-id").value = sessionId;
    });
  });

  // Экспорт в CSV
  const exportCsvButton = document.getElementById("export-csv");
  if (exportCsvButton) {
    exportCsvButton.addEventListener("click", exportToCSV);
  }

  // Печать списка
  const printListButton = document.getElementById("print-list");
  if (printListButton) {
    printListButton.addEventListener("click", printSessionsList);
  }
});

// Функция обновления статуса записи
function updateSessionStatus(sessionId, status) {
  // В реальной системе здесь был бы AJAX запрос
  console.log(`Обновление статуса записи ${sessionId} на ${status}`);

  // Формирование данных для отправки
  const formData = new FormData();
  formData.append("action", "change_status");
  formData.append("session_id", sessionId);
  formData.append("status", status);

  // Отправка запроса
  fetch("training_sessions.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Обновляем страницу или показываем уведомление
        showNotification("Статус записи успешно изменен", "success");
      } else {
        showNotification("Ошибка при изменении статуса", "danger");

        // Возвращаем предыдущее значение
        const select = document.querySelector(
          `.status-select[data-session-id="${sessionId}"]`
        );
        if (select) {
          select.value =
            select.getAttribute("data-original-value") ||
            select.options[0].value;
        }
      }
    })
    .catch((error) => {
      console.error("Ошибка:", error);
      showNotification("Произошла ошибка при обработке запроса", "danger");

      // Возвращаем предыдущее значение
      const select = document.querySelector(
        `.status-select[data-session-id="${sessionId}"]`
      );
      if (select) {
        select.value =
          select.getAttribute("data-original-value") || select.options[0].value;
      }
    });
}

// Функция загрузки деталей записи
function loadSessionDetails(sessionId) {
  // В реальной системе здесь был бы AJAX запрос
  console.log(`Загрузка деталей записи ${sessionId}`);

  // Имитация запроса к API
  fetch(`get_session.php?id=${sessionId}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        const session = data.session;

        // Заполняем модальное окно данными
        document.getElementById("view-id").textContent = session.id;
        document.getElementById("view-user").textContent = session.user_name;
        document.getElementById("view-trainer").textContent =
          session.trainer_name;
        document.getElementById("view-service").textContent =
          session.service_name;
        document.getElementById("view-date").textContent = formatDate(
          session.session_date
        );
        document.getElementById(
          "view-time"
        ).textContent = `${session.start_time} - ${session.end_time}`;

        // Статус
        const statusElement = document.getElementById("view-status");
        statusElement.textContent = getStatusName(session.status);
        statusElement.className = `badge bg-${getStatusClass(session.status)}`;

        // Примечания
        document.getElementById("view-notes").textContent =
          session.notes || "Нет примечаний";

        // Время создания и обновления
        document.getElementById("view-created").textContent = formatDateTime(
          session.created_at
        );
        document.getElementById("view-updated").textContent = formatDateTime(
          session.updated_at
        );
      } else {
        showNotification("Ошибка при загрузке данных", "danger");
      }
    })
    .catch((error) => {
      console.error("Ошибка:", error);
      showNotification("Произошла ошибка при загрузке данных", "danger");
    });
}

// Функция экспорта в CSV
function exportToCSV() {
  // Получаем таблицу
  const table = document.getElementById("sessions-table");
  const rows = table.querySelectorAll("tbody tr");

  if (rows.length === 0) {
    showNotification("Нет данных для экспорта", "warning");
    return;
  }

  // Заголовок CSV
  let csvContent = "ID,Клиент,Тренер,Услуга,Дата,Время,Статус\n";

  // Добавляем данные
  rows.forEach((row) => {
    const cells = row.querySelectorAll("td");
    const id = cells[0].textContent.trim();
    const client = cells[1].textContent.trim();
    const trainer = cells[2].textContent.trim();
    const service = cells[3].textContent.trim();
    const date = cells[4].textContent.trim();
    const time = cells[5].textContent.trim();
    const statusSelect = cells[6].querySelector("select");
    const status = getStatusName(statusSelect.value);

    // Экранируем запятые и кавычки
    csvContent += `"${id}","${client}","${trainer}","${service}","${date}","${time}","${status}"\n`;
  });

  // Создаем ссылку для скачивания
  const encodedUri = encodeURI("data:text/csv;charset=utf-8," + csvContent);
  const link = document.createElement("a");
  link.setAttribute("href", encodedUri);
  link.setAttribute(
    "download",
    `training_sessions_${formatDateForFilename(new Date())}.csv`
  );
  document.body.appendChild(link);

  // Имитируем клик
  link.click();

  // Удаляем ссылку
  document.body.removeChild(link);

  showNotification("Файл успешно скачан", "success");
}

// Функция печати списка
function printSessionsList() {
  // Подготовка контента для печати
  const table = document.getElementById("sessions-table");

  if (!table) {
    showNotification("Таблица не найдена", "warning");
    return;
  }

  // Создаем новое окно для печати
  const printWindow = window.open("", "_blank");
  printWindow.document.write(`
        <html>
        <head>
            <title>Список записей на тренировки</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                body { padding: 20px; }
                h1 { text-align: center; margin-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; }
                th, td { padding: 8px; border: 1px solid #ddd; }
                th { background-color: #f2f2f2; }
                .print-header { margin-bottom: 20px; }
                .print-footer { margin-top: 20px; text-align: center; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class="print-header">
                <h1>Список записей на тренировки</h1>
                <p>Дата: ${formatDate(new Date())}</p>
            </div>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Клиент</th>
                        <th>Тренер</th>
                        <th>Услуга</th>
                        <th>Дата</th>
                        <th>Время</th>
                        <th>Статус</th>
                    </tr>
                </thead>
                <tbody>
                    ${Array.from(table.querySelectorAll("tbody tr"))
                      .map((row) => {
                        const cells = row.querySelectorAll("td");
                        const statusSelect = cells[6].querySelector("select");
                        const status = getStatusName(statusSelect.value);

                        return `
                            <tr>
                                <td>${cells[0].textContent}</td>
                                <td>${cells[1].textContent}</td>
                                <td>${cells[2].textContent}</td>
                                <td>${cells[3].textContent}</td>
                                <td>${cells[4].textContent}</td>
                                <td>${cells[5].textContent}</td>
                                <td>${status}</td>
                            </tr>
                        `;
                      })
                      .join("")}
                </tbody>
            </table>
            <div class="print-footer">
                <p>Отчет сгенерирован системой управления фитнес-центром Moreon Fitness</p>
            </div>
        </body>
        </html>
    `);

  // Загружаем стили
  printWindow.document.close();

  // Печать после загрузки
  printWindow.onload = function () {
    printWindow.print();
    // printWindow.close(); // Закомментируем, чтобы пользователь сам закрыл окно
  };
}

// Вспомогательные функции
function getStatusName(status) {
  switch (status) {
    case "pending":
      return "Ожидает";
    case "confirmed":
      return "Подтверждено";
    case "cancelled":
      return "Отменено";
    case "completed":
      return "Завершено";
    default:
      return "Неизвестно";
  }
}

function getStatusClass(status) {
  switch (status) {
    case "pending":
      return "warning";
    case "confirmed":
      return "success";
    case "cancelled":
      return "danger";
    case "completed":
      return "secondary";
    default:
      return "info";
  }
}

function formatDate(dateString) {
  const options = { year: "numeric", month: "numeric", day: "numeric" };
  const date = new Date(dateString);
  return date.toLocaleDateString("ru-RU", options);
}

function formatDateTime(dateTimeString) {
  const options = {
    year: "numeric",
    month: "numeric",
    day: "numeric",
    hour: "numeric",
    minute: "numeric",
  };
  const date = new Date(dateTimeString);
  return date.toLocaleDateString("ru-RU", options);
}

function formatDateForFilename(date) {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const day = String(date.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
}

function showNotification(message, type = "info") {
  // Создаем элемент уведомления
  const notification = document.createElement("div");
  notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
  notification.role = "alert";
  notification.style.top = "20px";
  notification.style.right = "20px";
  notification.style.zIndex = "1050";
  notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Закрыть"></button>
    `;

  // Вставляем в DOM
  document.body.appendChild(notification);

  // Автоматически скрываем через 3 секунды
  setTimeout(() => {
    notification.classList.remove("show");
    setTimeout(() => {
      notification.remove();
    }, 300);
  }, 3000);
}
