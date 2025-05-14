// Promotions block slider & tabs logic

document.addEventListener("DOMContentLoaded", function () {
  const tabs = document.querySelectorAll(".promotions__tab");
  const slides = document.querySelectorAll(".promotions__slide");
  const dots = document.querySelectorAll(".promotions__dot");
  const leftArrow = document.querySelector(".promotions__arrow--left");
  const rightArrow = document.querySelector(".promotions__arrow--right");

  let slidesPerView = 3;
  let currentPage = 0;
  let totalPages = 1;

  function updateSlidesPerView() {
    if (window.innerWidth < 600) {
      slidesPerView = 1;
    } else if (window.innerWidth < 900) {
      slidesPerView = 2;
    } else {
      slidesPerView = 3;
    }
    totalPages = Math.ceil(slides.length / slidesPerView);
    // Показать только нужное количество точек
    dots.forEach((dot, i) => {
      dot.style.display = i < totalPages ? "inline-block" : "none";
    });
    if (currentPage > totalPages - 1) currentPage = totalPages - 1;
    updateSlider();
  }

  function updateSlider() {
    slides.forEach((slide, i) => {
      slide.style.display =
        i >= currentPage * slidesPerView &&
        i < (currentPage + 1) * slidesPerView
          ? "flex"
          : "none";
    });
    dots.forEach((dot, i) => {
      dot.classList.toggle("active", i === currentPage);
    });
  }

  function showPage(page) {
    if (page < 0) page = 0;
    if (page > totalPages - 1) page = totalPages - 1;
    currentPage = page;
    updateSlider();
  }

  leftArrow.addEventListener("click", function () {
    showPage(currentPage - 1);
  });
  rightArrow.addEventListener("click", function () {
    showPage(currentPage + 1);
  });
  dots.forEach((dot, i) => {
    dot.addEventListener("click", function () {
      showPage(i);
    });
  });

  tabs.forEach((tab) => {
    tab.addEventListener("click", function () {
      tabs.forEach((t) => t.classList.remove("active"));
      this.classList.add("active");
      // Здесь можно реализовать фильтрацию слайдов по табу, если потребуется
      showPage(0);
    });
  });

  window.addEventListener("resize", updateSlidesPerView);
  updateSlidesPerView();
});
