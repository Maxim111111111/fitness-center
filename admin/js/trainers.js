// Загрузка списка пользователей для выбора в форме добавления/редактирования тренера
function loadUsers(selectId, selectedUserId = null) {
  fetch("../api/users.php?action=get_available_users")
    .then((response) => response.json())
    .then((data) => {
      const select = document.getElementById(selectId);
      select.innerHTML = '<option value="">Выберите пользователя</option>';

      if (data.users && data.users.length > 0) {
        data.users.forEach((user) => {
          const option = document.createElement("option");
          option.value = user.id;
          option.textContent = `${user.first_name} ${user.last_name} (${user.email})`;

          if (selectedUserId && user.id == selectedUserId) {
            option.selected = true;
          }

          select.appendChild(option);
        });
      }
    })
    .catch((error) => console.error("Ошибка загрузки пользователей:", error));
}

// Получение данных тренера по ID
function getTrainerData(trainerId, callback) {
  fetch(`../api/trainers.php?action=get_trainer&id=${trainerId}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.trainer) {
        callback(data.trainer);
      }
    })
    .catch((error) => console.error("Ошибка загрузки данных тренера:", error));
}

// Обработка изменения статуса тренера
document.addEventListener("DOMContentLoaded", function () {
  // Загрузка списка пользователей при открытии модального окна добавления тренера
  const addTrainerModal = document.getElementById("addTrainerModal");
  if (addTrainerModal) {
    addTrainerModal.addEventListener("show.bs.modal", function () {
      loadUsers("add-user-id");
    });
  }

  // Обработка нажатия на кнопку "Редактировать" для открытия модального окна редактирования
  const editButtons = document.querySelectorAll(".edit-trainer");
  editButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const trainerId = this.getAttribute("data-trainer-id");

      // Получаем данные тренера и заполняем форму
      getTrainerData(trainerId, function (trainer) {
        document.getElementById("edit-trainer-id").value = trainer.id;
        loadUsers("edit-user-id", trainer.user_id);

        document.getElementById("edit-specialization").value =
          trainer.specialization || "";
        document.getElementById("edit-experience").value =
          trainer.experience_years || "";
        document.getElementById("edit-bio").value = trainer.bio || "";
        document.getElementById("edit-education").value =
          trainer.education || "";
        document.getElementById("edit-certificates").value =
          trainer.certificates || "";
        document.getElementById("edit-achievements").value =
          trainer.achievements || "";
        document.getElementById("edit-is-active").checked =
          trainer.is_active == 1;

        // Отображение текущего фото
        const photoContainer = document.getElementById(
          "current-photo-container"
        );
        if (trainer.photo_url) {
          photoContainer.innerHTML = `<img src="../${trainer.photo_url}" alt="Текущее фото" class="img-thumbnail" style="max-width: 150px;">`;
        } else {
          photoContainer.innerHTML =
            '<p class="text-muted">Фото не загружено</p>';
        }
      });
    });
  });

  // Обработка нажатия на кнопку "Просмотр" для открытия модального окна просмотра
  const viewButtons = document.querySelectorAll(".view-trainer");
  viewButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const trainerId = this.getAttribute("data-trainer-id");

      // Получаем данные тренера и заполняем форму просмотра
      getTrainerData(trainerId, function (trainer) {
        document.getElementById(
          "view-name"
        ).textContent = `${trainer.first_name} ${trainer.last_name}`;
        document.getElementById("view-email").textContent = trainer.email || "";
        document.getElementById("view-specialization").textContent =
          trainer.specialization || "";
        document.getElementById("view-experience").textContent =
          trainer.experience_years || "0";

        document.getElementById("view-bio").innerHTML = trainer.bio
          ? trainer.bio.replace(/\n/g, "<br>")
          : "Не указано";
        document.getElementById("view-education").innerHTML = trainer.education
          ? trainer.education.replace(/\n/g, "<br>")
          : "Не указано";
        document.getElementById("view-certificates").innerHTML =
          trainer.certificates
            ? trainer.certificates.replace(/\n/g, "<br>")
            : "Не указано";
        document.getElementById("view-achievements").innerHTML =
          trainer.achievements
            ? trainer.achievements.replace(/\n/g, "<br>")
            : "Не указано";

        // Отображение фото
        const photoContainer = document.getElementById("view-photo-container");
        if (trainer.photo_url) {
          photoContainer.innerHTML = `<img src="../${trainer.photo_url}" alt="Фото тренера" class="img-fluid rounded">`;
        } else {
          photoContainer.innerHTML = `<img src="../assets/img/trainers/default.jpg" alt="Фото по умолчанию" class="img-fluid rounded">`;
        }

        // Отображение статуса
        const statusContainer = document.getElementById("view-status");
        if (trainer.is_active == 1) {
          statusContainer.textContent = "Активный";
          statusContainer.className = "badge bg-success";
        } else {
          statusContainer.textContent = "Неактивный";
          statusContainer.className = "badge bg-danger";
        }

        // Настраиваем кнопку редактирования для передачи ID тренера
        const editButton = document.querySelector(".edit-from-view");
        editButton.setAttribute("data-trainer-id", trainer.id);
      });
    });
  });

  // Обработка кнопки редактирования из окна просмотра
  const editFromViewButton = document.querySelector(".edit-from-view");
  if (editFromViewButton) {
    editFromViewButton.addEventListener("click", function () {
      const trainerId = this.getAttribute("data-trainer-id");
      // Находим кнопку редактирования с этим ID и программно кликаем на неё
      document
        .querySelector(`.edit-trainer[data-trainer-id="${trainerId}"]`)
        .click();
    });
  }

  // Обработка нажатия на кнопку удаления для установки ID тренера в форме удаления
  const deleteModal = document.getElementById("deleteTrainerModal");
  if (deleteModal) {
    deleteModal.addEventListener("show.bs.modal", function (event) {
      const button = event.relatedTarget;
      const trainerId = button.getAttribute("data-trainer-id");
      document.getElementById("delete-trainer-id").value = trainerId;
    });
  }

  // Обработка изменения статуса тренера (переключатель)
  const statusToggles = document.querySelectorAll(".status-toggle");
  statusToggles.forEach((toggle) => {
    toggle.addEventListener("change", function () {
      const trainerId = this.getAttribute("data-trainer-id");
      const isActive = this.checked ? 1 : 0;

      const formData = new FormData();
      formData.append("action", "change_status");
      formData.append("trainer_id", trainerId);
      formData.append("is_active", isActive);

      fetch("trainers.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // Успешная смена статуса
            const toast = new bootstrap.Toast(
              document.getElementById("statusToast")
            );
            document.getElementById("toastMessage").textContent =
              "Статус тренера успешно изменен";
            toast.show();
          }
        })
        .catch((error) => console.error("Ошибка изменения статуса:", error));
    });
  });

  // Предпросмотр фото при загрузке
  const photoInputs = document.querySelectorAll(
    'input[type="file"][accept*="image"]'
  );
  photoInputs.forEach((input) => {
    input.addEventListener("change", function () {
      const file = this.files[0];
      if (file) {
        const reader = new FileReader();
        const previewContainerId =
          this.id === "add-photo" ? "add-photo-preview" : "edit-photo-preview";

        // Создаем контейнер для предпросмотра, если его еще нет
        let previewContainer = document.getElementById(previewContainerId);
        if (!previewContainer) {
          previewContainer = document.createElement("div");
          previewContainer.id = previewContainerId;
          previewContainer.className = "mt-2";
          this.parentNode.appendChild(previewContainer);
        }

        reader.onload = function (e) {
          previewContainer.innerHTML = `<img src="${e.target.result}" alt="Предпросмотр" class="img-thumbnail" style="max-width: 150px;">`;
        };

        reader.readAsDataURL(file);
      }
    });
  });
});
