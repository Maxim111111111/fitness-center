document.addEventListener("DOMContentLoaded", function () {
  // Анимация появления элементов при скролле
  const animateOnScroll = function () {
    const elements = document.querySelectorAll(".animate-on-scroll");

    elements.forEach((element) => {
      const elementPosition = element.getBoundingClientRect().top;
      const screenPosition = window.innerHeight / 1.2;

      if (elementPosition < screenPosition) {
        element.classList.add("animate-in");
      }
    });
  };

  // Добавление класса animate-on-scroll ко всем элементам, которые нужно анимировать
  const elementsToAnimate = [
    // Общие элементы для всех страниц
    ".about-club__content",
    ".about-company__content",
    ".training-form__content",
    ".contacts__map-container",
    ".contacts__content",
    ".contacts__item",

    // Секции и карточки
    ".facilities__item",
    ".directions__card",
    ".trainers__card",
    ".club-card-section__card",
    ".benefits__card",
    ".promotions__slide",
    ".reviews__wrapper",

    // Заголовки и подзаголовки
    ".about-company__title",
    ".facilities__title",
    ".directions__title",
    ".trainers__title",
    ".club-card-section__title",
    ".benefits__title",

    // Другие элементы
    ".main__banner-content",
    ".main__banner-buttons",
    ".about-club__buttons",
    ".promotions__tabs",
  ];

  // Применяем класс animate-on-scroll ко всем выбранным элементам
  elementsToAnimate.forEach((selector) => {
    document.querySelectorAll(selector).forEach((element) => {
      element.classList.add("animate-on-scroll");
    });
  });

  // Добавляем последовательную задержку для элементов в одной группе
  document.querySelectorAll(".animate-on-scroll").forEach((element, index) => {
    // Находим все элементы одного типа в одном родителе
    const parent = element.parentElement;
    const siblings = Array.from(parent.children).filter(
      (child) =>
        child.classList.contains("animate-on-scroll") &&
        child.tagName === element.tagName
    );

    // Если это часть группы похожих элементов, добавляем задержку
    if (siblings.length > 1) {
      const siblingIndex = siblings.indexOf(element);
      if (siblingIndex !== -1) {
        element.style.transitionDelay = `${siblingIndex * 0.1}s`;
      }
    }
  });

  // Запускаем анимацию при загрузке и скролле
  window.addEventListener("scroll", animateOnScroll);
  animateOnScroll(); // Запускаем один раз при загрузке

  // Плавная прокрутка для якорных ссылок
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      const targetId = this.getAttribute("href");

      // Проверяем, что это не пустая ссылка и не просто "#"
      if (targetId && targetId !== "#") {
        e.preventDefault();

        const targetElement = document.querySelector(targetId);

        if (targetElement) {
          window.scrollTo({
            top: targetElement.offsetTop - 100,
            behavior: "smooth",
          });
        }
      }
    });
  });

  // Анимация для кнопок при наведении
  const buttons = document.querySelectorAll(
    ".training-form__button, .main__banner-button, .about-club__btn"
  );

  buttons.forEach((button) => {
    button.addEventListener("mouseenter", function () {
      this.classList.add("button-hover");
    });

    button.addEventListener("mouseleave", function () {
      this.classList.remove("button-hover");
    });
  });

  // Анимация для карточек при наведении
  const cards = document.querySelectorAll(
    ".directions__card, .trainers__card, .club-card-section__card, .benefits__card"
  );

  cards.forEach((card) => {
    card.addEventListener("mouseenter", function () {
      this.classList.add("card-hover");
    });

    card.addEventListener("mouseleave", function () {
      this.classList.remove("card-hover");
    });
  });
});
