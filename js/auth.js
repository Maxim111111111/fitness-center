// Обработка форм авторизации и регистрации
document.addEventListener("DOMContentLoaded", function () {
  // Выпадающее меню авторизации для неавторизованных пользователей
  const authIcon = document.getElementById("authIcon");
  const authDropdown = document.getElementById("authDropdown");

  if (authIcon && authDropdown) {
    authIcon.addEventListener("click", function (e) {
      e.stopPropagation();
      authDropdown.classList.toggle("active");
    });

    // Закрытие меню при клике вне его
    document.addEventListener("click", function (e) {
      if (!authDropdown.contains(e.target) && e.target !== authIcon) {
        authDropdown.classList.remove("active");
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

  // Валидация формы входа
  const loginForm = document.getElementById("loginForm");
  if (loginForm) {
    loginForm.addEventListener("submit", async function (e) {
      e.preventDefault();

      const email = document.getElementById("login-email").value;
      const password = document.getElementById("login-password").value;

      if (!validateEmail(email)) {
        showError("Пожалуйста, введите корректный email");
        return;
      }

      if (!password) {
        showError("Пожалуйста, введите пароль");
        return;
      }

      try {
        const formData = new FormData();
        formData.append("email", email);
        formData.append("password", password);

        const response = await fetch("auth_handler.php", {
          method: "POST",
          body: formData,
        });

        const result = await response.json();

        if (result.success) {
          window.location.href = result.redirect;
        } else {
          showError(result.message || "Ошибка при входе");
        }
      } catch (error) {
        console.error("Error:", error);
        showError("Произошла ошибка при входе");
      }
    });
  }

  // Валидация формы регистрации
  const registerForm = document.getElementById("registerForm");
  if (registerForm) {
    registerForm.addEventListener("submit", function (e) {
      e.preventDefault();

      const name = document.getElementById("register-name").value;
      const email = document.getElementById("register-email").value;
      const phone = document.getElementById("register-phone").value;
      const password = document.getElementById("register-password").value;
      const passwordConfirm = document.getElementById(
        "register-password-confirm"
      ).value;
      const agreeTerms = document.getElementById("agree-terms").checked;

      if (!name) {
        showError("Пожалуйста, введите ваше имя");
        return;
      }

      if (!validateEmail(email)) {
        showError("Пожалуйста, введите корректный email");
        return;
      }

      if (!validatePhone(phone)) {
        showError("Пожалуйста, введите корректный номер телефона");
        return;
      }

      if (password.length < 6) {
        showError("Пароль должен содержать не менее 6 символов");
        return;
      }

      if (password !== passwordConfirm) {
        showError("Пароли не совпадают");
        return;
      }

      if (!agreeTerms) {
        showError("Необходимо согласиться с условиями");
        return;
      }

      // TODO: Implement registration logic with server
      showError("Функция регистрации находится в разработке");
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
    alert(message);
  }
});
