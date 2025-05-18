document.addEventListener("DOMContentLoaded", function () {
  // Получаем текущий путь страницы
  const currentPath = window.location.pathname;

  // Находим все ссылки в меню
  const menuLinks = document.querySelectorAll(".header__menu-link");

  // Проходим по всем ссылкам и проверяем, соответствует ли href текущему пути
  menuLinks.forEach((link) => {
    const href = link.getAttribute("href");

    // Если href соответствует текущему пути или если текущий путь заканчивается на href
    if (href === currentPath || currentPath.endsWith(href)) {
      // Добавляем класс active для текущей ссылки
      link.classList.add("active");

      // Добавляем класс для родительского элемента, если нужно
      const menuItem = link.closest(".header__menu-item");
      if (menuItem) {
        menuItem.classList.add("active");
      }
    }
  });

  // Проверяем, находимся ли мы на странице профиля
  if (currentPath.endsWith("profile.php")) {
    const profileLink = document.querySelector(".header__profile-link");
    if (profileLink) {
      profileLink.classList.add("active");
    }
  }

  // Анимация для активного пункта меню
  const activeLink = document.querySelector(".header__menu-link.active");
  if (activeLink) {
    // Добавляем небольшую пульсацию при загрузке страницы
    activeLink.classList.add("pulse-once");

    // Удаляем класс через некоторое время
    setTimeout(() => {
      activeLink.classList.remove("pulse-once");
    }, 1000);
  }
});
