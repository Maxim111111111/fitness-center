document.addEventListener("DOMContentLoaded", function () {
  const contactForm = document.querySelector(".training-form__form");

  // Анимация появления элементов при скролле
  const animateOnScroll = function () {
    const elements = document.querySelectorAll(
      ".contacts__item, .training-form__content, .contacts__map-container, .facilities__item"
    );

    elements.forEach((element) => {
      const elementPosition = element.getBoundingClientRect().top;
      const screenPosition = window.innerHeight / 1.2;

      if (elementPosition < screenPosition) {
        element.classList.add("animate-in");
      }
    });
  };

  // Запускаем анимацию при загрузке и скролле
  window.addEventListener("scroll", animateOnScroll);
  animateOnScroll(); // Запускаем один раз при загрузке

  // Обработка формы обратной связи
  if (contactForm) {
    contactForm.addEventListener("submit", function (e) {
      e.preventDefault();

      // Get form fields
      const nameInput = contactForm.querySelector('input[type="text"]');
      const emailInput = contactForm.querySelector('input[type="email"]');
      const phoneInput = contactForm.querySelector('input[type="tel"]');
      const messageInput = contactForm.querySelector("textarea");

      // Validate form
      let isValid = true;

      // Name validation
      if (!nameInput.value.trim()) {
        showError(nameInput, "Пожалуйста, введите ваше имя");
        isValid = false;
      } else {
        removeError(nameInput);
      }

      // Email validation
      if (!validateEmail(emailInput.value)) {
        showError(emailInput, "Пожалуйста, введите корректный email");
        isValid = false;
      } else {
        removeError(emailInput);
      }

      // Phone validation
      if (!validatePhone(phoneInput.value)) {
        showError(phoneInput, "Пожалуйста, введите корректный номер телефона");
        isValid = false;
      } else {
        removeError(phoneInput);
      }

      // If form is valid, submit it
      if (isValid) {
        // Показываем анимацию загрузки
        const submitButton = contactForm.querySelector('button[type="submit"]');
        const originalText = submitButton.textContent;
        submitButton.innerHTML = '<span class="loading-spinner"></span>';
        submitButton.disabled = true;

        // Имитация отправки данных на сервер
        setTimeout(() => {
          // Clear form
          contactForm.reset();

          // Восстанавливаем кнопку
          submitButton.innerHTML = originalText;
          submitButton.disabled = false;

          // Show success message
          const successMessage = document.createElement("div");
          successMessage.className = "form-success-message animate-in";
          successMessage.textContent =
            "Спасибо за ваше сообщение! Мы свяжемся с вами в ближайшее время.";

          // Insert message after form
          contactForm.parentNode.insertBefore(
            successMessage,
            contactForm.nextSibling
          );

          // Remove message after 5 seconds
          setTimeout(() => {
            successMessage.classList.add("animate-out");
            setTimeout(() => {
              successMessage.remove();
            }, 300);
          }, 5000);
        }, 1500);
      }
    });

    // Маска для телефона
    const phoneInput = contactForm.querySelector('input[type="tel"]');
    if (phoneInput) {
      phoneInput.addEventListener("input", function (e) {
        let x = e.target.value
          .replace(/\D/g, "")
          .match(/(\d{0,1})(\d{0,3})(\d{0,3})(\d{0,4})/);
        if (x[1] != "") {
          x[1] = "+" + x[1];
        }
        e.target.value = !x[2]
          ? x[1]
          : x[1] +
            " (" +
            x[2] +
            (x[3] ? ") " + x[3] : "") +
            (x[4] ? "-" + x[4] : "");
      });
    }
  }

  // Helper functions
  function validateEmail(email) {
    const re =
      /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
  }

  function validatePhone(phone) {
    // Проверяем, что телефон содержит достаточное количество цифр
    const digitsOnly = phone.replace(/\D/g, "");
    return digitsOnly.length >= 10;
  }

  function showError(input, message) {
    // Remove any existing error
    removeError(input);

    // Create error message
    const error = document.createElement("div");
    error.className = "form-error-message";
    error.textContent = message;

    // Insert error after input's parent (field div)
    const field = input.closest(".training-form__field");
    field.appendChild(error);

    // Add error class to input
    input.classList.add("input-error");

    // Анимация появления ошибки
    setTimeout(() => {
      error.style.opacity = "1";
      error.style.transform = "translateY(0)";
    }, 10);
  }

  function removeError(input) {
    // Find the field container
    const field = input.closest(".training-form__field");

    // Remove error message if exists
    const error = field.querySelector(".form-error-message");
    if (error) {
      error.style.opacity = "0";
      error.style.transform = "translateY(-10px)";
      setTimeout(() => {
        error.remove();
      }, 300);
    }

    // Remove error class from input
    input.classList.remove("input-error");
  }
});
