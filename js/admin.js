document.addEventListener("DOMContentLoaded", function () {
  // Mobile menu toggle
  const menuToggle = document.createElement("button");
  menuToggle.className = "menu-toggle";
  menuToggle.innerHTML = "<span></span><span></span><span></span>";
  document.querySelector(".admin-container").prepend(menuToggle);

  menuToggle.addEventListener("click", function () {
    document.querySelector(".sidebar").classList.toggle("active");
  });

  // Close sidebar when clicking outside on mobile
  document.addEventListener("click", function (e) {
    const sidebar = document.querySelector(".sidebar");
    const menuBtn = document.querySelector(".menu-toggle");

    if (!sidebar.contains(e.target) && !menuBtn.contains(e.target)) {
      sidebar.classList.remove("active");
    }
  });

  // Handle form submissions with AJAX
  document.querySelectorAll(".admin-form").forEach((form) => {
    form.addEventListener("submit", async function (e) {
      e.preventDefault();

      try {
        const formData = new FormData(this);
        const response = await fetch(this.action, {
          method: "POST",
          body: formData,
        });

        const result = await response.json();

        if (result.success) {
          showNotification("success", result.message);
          if (result.reload) {
            setTimeout(() => window.location.reload(), 1500);
          }
        } else {
          showNotification("error", result.message || "Произошла ошибка");
        }
      } catch (error) {
        showNotification("error", "Произошла ошибка при отправке формы");
        console.error("Form submission error:", error);
      }
    });
  });

  // Notification system
  function showNotification(type, message) {
    const notification = document.createElement("div");
    notification.className = `notification ${type}`;
    notification.textContent = message;

    document.body.appendChild(notification);

    setTimeout(() => {
      notification.classList.add("show");
    }, 100);

    setTimeout(() => {
      notification.classList.remove("show");
      setTimeout(() => notification.remove(), 300);
    }, 3000);
  }

  // Table sorting
  document.querySelectorAll(".admin-table th[data-sort]").forEach((header) => {
    header.addEventListener("click", function () {
      const table = this.closest("table");
      const tbody = table.querySelector("tbody");
      const rows = Array.from(tbody.querySelectorAll("tr"));
      const index = Array.from(this.parentNode.children).indexOf(this);
      const direction = this.classList.contains("asc") ? -1 : 1;

      // Clear all headers
      table.querySelectorAll("th").forEach((th) => {
        th.classList.remove("asc", "desc");
      });

      // Set current header
      this.classList.toggle("asc", direction === 1);
      this.classList.toggle("desc", direction === -1);

      // Sort rows
      rows.sort((a, b) => {
        const aValue = a.children[index].textContent;
        const bValue = b.children[index].textContent;
        return aValue.localeCompare(bValue) * direction;
      });

      // Reorder table
      tbody.append(...rows);
    });
  });

  // Confirm deletions
  document.querySelectorAll("[data-confirm]").forEach((element) => {
    element.addEventListener("click", function (e) {
      if (!confirm(this.dataset.confirm || "Вы уверены?")) {
        e.preventDefault();
      }
    });
  });
});
