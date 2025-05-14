document.addEventListener("DOMContentLoaded", function () {
  // Get the background text element
  const backgroundText = document.querySelector(".services-background-text");

  if (!backgroundText) return;

  // Create a static text effect
  let text = backgroundText.textContent.trim();
  backgroundText.textContent = "";

  // Repeat the text to fill the background
  const repeatCount = 50;
  let fullText = "";

  for (let i = 0; i < repeatCount; i++) {
    fullText += text + "  ";
  }

  backgroundText.textContent = fullText;

  // Adjust text container height to match the hero section height
  function adjustTextHeight() {
    const heroSection = document.querySelector(".services-hero");
    if (heroSection) {
      backgroundText.style.height = heroSection.offsetHeight + "px";
    }
  }

  // Initial adjustment
  adjustTextHeight();

  // Adjust on window resize
  window.addEventListener("resize", adjustTextHeight);
});
