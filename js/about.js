document.addEventListener("DOMContentLoaded", function () {
  // Initialize all facilities sliders
  const facilitiesSliders = document.querySelectorAll(
    ".facilities__slider .swiper"
  );
  facilitiesSliders.forEach((slider, index) => {
    new Swiper(slider, {
      slidesPerView: 1,
      loop: true,
      speed: 700,
      autoplay: {
        delay: 5000,
        disableOnInteraction: false,
      },
      navigation: {
        nextEl: slider.parentElement.querySelector(".facilities__next"),
        prevEl: slider.parentElement.querySelector(".facilities__prev"),
      },
      pagination: {
        el: slider.querySelector(".swiper-pagination"),
        clickable: true,
        type: "bullets",
        bulletClass: "swiper-pagination-bullet",
        bulletActiveClass: "swiper-pagination-bullet-active",
      },
    });
  });
});
