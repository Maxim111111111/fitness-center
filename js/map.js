// Yandex Maps initialization
function initMap() {
  const myMap = new ymaps.Map("map", {
    center: [55.606777, 37.535924], // Coordinates for Moreon Fitness
    zoom: 15,
    controls: ["zoomControl"],
  });

  const myPlacemark = new ymaps.Placemark(
    [55.606777, 37.535924],
    {
      balloonContent: "Moreon Fitness<br>ул. Голубинская, д. 16",
      hintContent: "Moreon Fitness - фитнес клуб",
    },
    {
      iconLayout: "default#image",
      iconImageHref: "assets/svg/location.svg",
      iconImageSize: [40, 40],
      iconImageOffset: [-20, -40],
    }
  );

  myMap.geoObjects.add(myPlacemark);
  myMap.behaviors.disable("scrollZoom");

  // Enable scroll zoom on map click
  myMap.events.add("click", function () {
    myMap.behaviors.enable("scrollZoom");
  });
}

// Initialize map when Yandex Maps API is ready
document.addEventListener("DOMContentLoaded", function () {
  ymaps.ready(initMap);
});
