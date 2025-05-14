$(document).ready(function () {
  // Обработка полей select при загрузке страницы
  $(".training-form__select").each(function () {
    if ($(this).val() !== "" && $(this).val() !== null) {
      $(this).css("color", "#ffffff");
    } else {
      $(this).css("color", "rgba(255, 255, 255, 0.5)");
    }
  });

  // Обработка полей select при изменении значения
  $(".training-form__select").change(function () {
    if ($(this).val() !== "" && $(this).val() !== null) {
      $(this).css("color", "#ffffff");
    } else {
      $(this).css("color", "rgba(255, 255, 255, 0.5)");
    }
  });

  // Принудительное обновление цвета при фокусе
  $(".training-form__select").focus(function () {
    setTimeout(
      function () {
        if ($(this).val() !== "" && $(this).val() !== null) {
          $(this).css("color", "#ffffff");
        }
      }.bind(this),
      100
    );
  });

  // Обработка формы при отправке
  $("#trainingForm").submit(function (e) {
    e.preventDefault();
    alert("Заявка на тренировку успешно отправлена!");
    this.reset();
    $(".training-form__select").css("color", "rgba(255, 255, 255, 0.5)");
  });
});
