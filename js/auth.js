// Обработка форм авторизации и регистрации
document.addEventListener("DOMContentLoaded", function () {
  // Выпадающее меню авторизации для неавторизованных пользователей
  const authIcon = document.getElementById("authIcon");
  const authDropdown = document.getElementById("authDropdown");
  const authTrigger = document.querySelector(".header__auth-trigger");

  if (authIcon && authDropdown) {
    authIcon.addEventListener("click", function (e) {
      e.stopPropagation();
      authDropdown.classList.toggle("active");
    });

    // Закрытие меню при клике вне его
    document.addEventListener("click", function (e) {
      if (
        !authTrigger ||
        (!authDropdown.contains(e.target) && e.target !== authIcon)
      ) {
        if (authDropdown.classList.contains("active")) {
          authDropdown.classList.remove("active");
        }
      }
    });
  }

  // Переключатель видимости пароля
  const passwordToggles = document.querySelectorAll(".auth-password-toggle");

  passwordToggles.forEach((toggle) => {
    toggle.addEventListener("click", function () {
      const input = this.previousElementSibling;
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

  // Базовая клиентская валидация формы входа (основная валидация на сервере)
  const loginForm = document.getElementById("loginForm");
  if (loginForm) {
    loginForm.addEventListener("submit", function (e) {
      const email = document.getElementById("login-email").value;
      const password = document.getElementById("login-password").value;

      if (!validateEmail(email)) {
        e.preventDefault();
        showError("Пожалуйста, введите корректный email");
        return false;
      }

      if (!password) {
        e.preventDefault();
        showError("Пожалуйста, введите пароль");
        return false;
      }

      return true;
    });
  }

  // Валидация формы регистрации
  const registerForm = document.getElementById("registerForm");
  if (registerForm) {
    registerForm.addEventListener("submit", async function (e) {
      e.preventDefault();

      // Сбрасываем стили ошибок
      clearErrors();

      const name = document.getElementById("register-name").value;
      const email = document.getElementById("register-email").value;
      const phone = document.getElementById("register-phone").value;
      const password = document.getElementById("register-password").value;
      const passwordConfirm = document.getElementById(
        "register-password-confirm"
      ).value;
      const agreeTerms = document.getElementById("agree-terms").checked;

      // Валидация полей
      let hasErrors = false;

      if (!name) {
        showInputError("register-name", "Пожалуйста, введите ваше имя");
        hasErrors = true;
      }

      if (!validateEmail(email)) {
        showInputError(
          "register-email",
          "Пожалуйста, введите корректный email"
        );
        hasErrors = true;
      }

      if (!validatePhone(phone)) {
        showInputError(
          "register-phone",
          "Пожалуйста, введите корректный номер телефона"
        );
        hasErrors = true;
      }

      if (password.length < 6) {
        showInputError(
          "register-password",
          "Пароль должен содержать не менее 6 символов"
        );
        hasErrors = true;
      }

      if (password !== passwordConfirm) {
        showInputError("register-password-confirm", "Пароли не совпадают");
        hasErrors = true;
      }

      if (!agreeTerms) {
        showCheckboxError("agree-terms", "Необходимо согласиться с условиями");
        hasErrors = true;
      }

      if (hasErrors) {
        return;
      }

      // Показываем индикатор загрузки
      const submitButton = registerForm.querySelector('button[type="submit"]');
      const originalButtonText = submitButton.innerHTML;
      submitButton.innerHTML =
        '<span class="loading-spinner"></span> Регистрация...';
      submitButton.disabled = true;

      try {
        const formData = new FormData();
        formData.append("name", name);
        formData.append("email", email);
        formData.append("phone", phone);
        formData.append("password", password);
        formData.append("password_confirm", passwordConfirm);

        const response = await fetch("register_handler.php", {
          method: "POST",
          body: formData,
        });

        const result = await response.json();

        if (result.success) {
          // Создаем и показываем сообщение об успехе
          const successMessage = document.createElement("div");
          successMessage.className = "form-success-message animate-in";
          successMessage.textContent =
            result.message || "Регистрация прошла успешно!";

          registerForm.appendChild(successMessage);

          // Перенаправляем через 2 секунды
          setTimeout(() => {
            window.location.href = result.redirect || "profile.php";
          }, 2000);
        } else {
          showError(result.message || "Ошибка при регистрации");
          // Восстанавливаем кнопку
          submitButton.innerHTML = originalButtonText;
          submitButton.disabled = false;
        }
      } catch (error) {
        console.error("Error:", error);
        showError("Произошла ошибка при регистрации");
        // Восстанавливаем кнопку
        submitButton.innerHTML = originalButtonText;
        submitButton.disabled = false;
      }
    });
  }

  // Функция валидации email
  function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
  }

  // Функция валидации телефона
  function validatePhone(phone) {
    const re = /^\+?[0-9\s-()]{10,17}$/;
    return re.test(phone);
  }

  // Функция отображения ошибки
  function showError(message) {
    // Проверяем, есть ли уже элемент ошибки
    let errorElement = document.querySelector(".auth-error");

    // Если нет, создаем новый
    if (!errorElement) {
      errorElement = document.createElement("div");
      errorElement.className = "auth-error";

      // Находим форму
      const form = document.querySelector(".auth-form");
      if (form) {
        // Вставляем перед формой
        form.parentNode.insertBefore(errorElement, form);
      } else {
        // Запасной вариант - просто показываем алерт
        alert(message);
        return;
      }
    }

    // Устанавливаем сообщение
    errorElement.textContent = message;

    // Прокручиваем страницу к сообщению об ошибке
    errorElement.scrollIntoView({ behavior: "smooth", block: "center" });
  }

  // Функция отображения ошибки поля ввода
  function showInputError(inputId, message) {
    const input = document.getElementById(inputId);
    if (input) {
      input.classList.add("input-error");

      // Создаем сообщение об ошибке, если его еще нет
      let errorElement = input.parentElement.querySelector(
        ".form-error-message"
      );
      if (!errorElement) {
        errorElement = document.createElement("div");
        errorElement.className = "form-error-message";
        input.parentElement.appendChild(errorElement);
      }

      errorElement.textContent = message;
      errorElement.style.opacity = "1";
      errorElement.style.transform = "translateY(0)";

      // Удаляем класс ошибки через некоторое время
      setTimeout(() => {
        input.classList.remove("input-error");
      }, 2000);
    }
  }

  // Функция отображения ошибки чекбокса
  function showCheckboxError(checkboxId, message) {
    const checkbox = document.getElementById(checkboxId);
    if (checkbox) {
      const label = checkbox.parentElement.querySelector("label");
      if (label) {
        label.classList.add("checkbox-error");

        // Создаем сообщение об ошибке, если его еще нет
        let errorElement = checkbox.parentElement.querySelector(
          ".form-error-message"
        );
        if (!errorElement) {
          errorElement = document.createElement("div");
          errorElement.className = "form-error-message checkbox-error-message";
          checkbox.parentElement.appendChild(errorElement);
        }

        errorElement.textContent = message;
        errorElement.style.opacity = "1";
        errorElement.style.maxHeight = "50px";

        // Удаляем класс ошибки через некоторое время
        setTimeout(() => {
          label.classList.remove("checkbox-error");
          errorElement.style.opacity = "0";
          errorElement.style.maxHeight = "0";
        }, 3000);
      }
    }
  }

  // Функция очистки всех ошибок на форме
  function clearErrors() {
    // Удаляем все сообщения об ошибках
    const errorMessages = document.querySelectorAll(".form-error-message");
    errorMessages.forEach((el) => {
      el.textContent = "";
      el.style.opacity = "0";
      el.style.transform = "translateY(-10px)";
    });

    // Удаляем классы ошибок с инпутов
    const errorInputs = document.querySelectorAll(".input-error");
    errorInputs.forEach((el) => {
      el.classList.remove("input-error");
    });

    // Удаляем классы ошибок с чекбоксов
    const errorCheckboxes = document.querySelectorAll(".checkbox-error");
    errorCheckboxes.forEach((el) => {
      el.classList.remove("checkbox-error");
    });
  }
});
