document.addEventListener("DOMContentLoaded", function () {
  // Существующий код инициализации слайдера
  const promotionsSwiper = new Swiper(".promotions__swiper", {
    slidesPerView: 3,
    slidesPerGroup: 1,
    spaceBetween: 20,
    centeredSlides: false,
    initialSlide: 0,
    loop: true,
    speed: 700,
    autoplay: {
      delay: 5000,
      disableOnInteraction: false,
    },
    // Enable navigation arrows
    navigation: {
      nextEl: ".promotions__button-next",
      prevEl: ".promotions__button-prev",
    },
    // Enable pagination
    pagination: {
      el: ".swiper-pagination",
      clickable: true,
      type: "bullets",
      bulletClass: "promotions__bullet",
      bulletActiveClass: "promotions__bullet--active",
    },
    // Responsive breakpoints
    breakpoints: {
      320: {
        slidesPerView: 1,
        slidesPerGroup: 1,
        spaceBetween: 10,
        centeredSlides: true,
      },
      768: {
        slidesPerView: 2,
        slidesPerGroup: 1,
        spaceBetween: 15,
      },
      1024: {
        slidesPerView: 3,
        slidesPerGroup: 1,
        spaceBetween: 20,
      },
    },
  });

  // Tab switching
  const tabs = document.querySelectorAll(".promotions__tab");

  // Имитация различных наборов слайдов для каждого таба
  const slideSets = {
    fitness: [
      "assets/img/fitness-promo-1.jpg",
      "assets/img/fitness-promo-2.jpg",
      "assets/img/fitness-promo-3.jpg",
    ],
    spa: [
      "assets/img/fitness-promo-3.jpg",
      "assets/img/fitness-promo-1.jpg",
      "assets/img/fitness-promo-2.jpg",
    ],
    aquapark: [
      "assets/img/fitness-promo-2.jpg",
      "assets/img/fitness-promo-3.jpg",
      "assets/img/fitness-promo-1.jpg",
    ],
    therms: [
      "assets/img/fitness-promo-1.jpg",
      "assets/img/fitness-promo-3.jpg",
      "assets/img/fitness-promo-2.jpg",
    ],
    holidays: [
      "assets/img/fitness-promo-3.jpg",
      "assets/img/fitness-promo-2.jpg",
      "assets/img/fitness-promo-1.jpg",
    ],
    bowling: [
      "assets/img/fitness-promo-2.jpg",
      "assets/img/fitness-promo-1.jpg",
      "assets/img/fitness-promo-3.jpg",
    ],
  };

  // Функция для обновления слайдов
  function updateSlides(tabType) {
    // Получаем все слайды
    const slides = document.querySelectorAll(".promotions__slide");
    const slideImages = slideSets[tabType];

    // Добавляем класс загрузки ко всем слайдам
    slides.forEach((slide) => slide.classList.add("loading"));

    // Обновляем все слайды
    slides.forEach((slide, index) => {
      const img = slide.querySelector("img");
      if (img) {
        // Добавляем анимацию смены изображения
        img.style.opacity = "0";

        setTimeout(() => {
          // Используем остаток от деления для зацикливания изображений
          const imageIndex = index % slideImages.length;
          img.src = slideImages[imageIndex];
          img.onload = function () {
            img.style.opacity = "1";
            slide.classList.remove("loading");
          };
        }, 300);
      }
    });

    // Сбрасываем слайдер на первый слайд с анимацией
    promotionsSwiper.slideTo(0, 500);
  }

  tabs.forEach((tab) => {
    tab.addEventListener("click", function () {
      // Remove active class from all tabs
      tabs.forEach((t) => t.classList.remove("active"));

      // Add active class to clicked tab
      this.classList.add("active");

      // Получаем тип таба и переключаем слайды
      const tabType = this.getAttribute("data-tab");
      updateSlides(tabType);

      console.log("Switched to tab:", tabType);
    });
  });
});
