// Fungsi untuk toggle menu cepat
function setupQuickMenu() {
  const menuCards = document.querySelectorAll(".quick-menu-card");

  menuCards.forEach((card) => {
    const header = card.querySelector(".quick-menu-header");

    if (header) {
      header.addEventListener("click", function () {
        card.classList.toggle("expanded");
      });
    }
  });
}

// Fungsi untuk toggle sidebar di mobile
function setupSidebarToggle() {
  const sidebarToggle = document.getElementById("sidebar-toggle");
  const sidebar = document.querySelector(".sidebar");
  const sidebarOverlay = document.querySelector(".sidebar-overlay");

  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener("click", function () {
      sidebar.classList.toggle("active");
      if (sidebarOverlay) sidebarOverlay.classList.toggle("active");
    });
  }

  if (sidebarOverlay) {
    sidebarOverlay.addEventListener("click", function () {
      sidebar.classList.remove("active");
      sidebarOverlay.classList.remove("active");
    });
  }
}

// Fungsi untuk menandai menu aktif
function setActiveMenu() {
  const currentPath = window.location.pathname;
  const menuLinks = document.querySelectorAll(".sidebar-menu a");

  menuLinks.forEach((link) => {
    const href = link.getAttribute("href");
    if (href && currentPath.includes(href)) {
      link.classList.add("active");
    } else {
      link.classList.remove("active");
    }
  });
}

// Fungsi untuk pencarian di mobile
function setupMobileSearch() {
  const mobileSearchIcon = document.getElementById("mobile-search-icon");
  const mobileSearchBox = document.getElementById("mobile-search-box");
  const mobileSearchClose = document.getElementById("mobile-search-close");
  const mobileSearchInput = document.getElementById("mobile-search-input");
  const searchInput = document.getElementById("search-input");

  if (mobileSearchIcon && mobileSearchBox) {
    mobileSearchIcon.addEventListener("click", function () {
      mobileSearchBox.classList.add("active");
      mobileSearchInput.focus();
    });
  }

  if (mobileSearchClose && mobileSearchBox) {
    mobileSearchClose.addEventListener("click", function () {
      mobileSearchBox.classList.remove("active");
    });
  }

  // Sinkronisasi input pencarian desktop & mobile
  if (mobileSearchInput && searchInput) {
    mobileSearchInput.addEventListener("input", function () {
      searchInput.value = this.value;
      searchInput.dispatchEvent(new Event("input"));
    });

    searchInput.addEventListener("input", function () {
      mobileSearchInput.value = this.value;
    });
  }
}

// Fungsi untuk mobile action menu
function setupMobileActionMenu() {
  const mobileActionButtons = document.querySelectorAll(
    ".mobile-action-button"
  );

  mobileActionButtons.forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();

      // Tutup semua action menu lain
      document.querySelectorAll(".mobile-action-menu").forEach((menu) => {
        if (menu !== this.nextElementSibling) {
          menu.classList.remove("active");
        }
      });

      // Toggle menu yang diklik
      const actionMenu = this.nextElementSibling;
      if (actionMenu && actionMenu.classList.contains("mobile-action-menu")) {
        actionMenu.classList.toggle("active");
      }
    });
  });

  // Klik di luar area menutup semua menu
  document.addEventListener("click", function () {
    document.querySelectorAll(".mobile-action-menu").forEach((menu) => {
      menu.classList.remove("active");
    });
  });
}

// Jalankan semua fungsi setelah DOM siap
document.addEventListener("DOMContentLoaded", function () {
  setupQuickMenu();
  setupSidebarToggle();
  setActiveMenu();
  setupMobileSearch();
  setupMobileActionMenu();
});
