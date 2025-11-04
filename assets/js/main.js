// Fungsi untuk menampilkan notifikasi toast
function showToast(message, type = "info", title = "") {
  // Buat container toast jika belum ada
  let toastContainer = document.querySelector(".toast-container");
  if (!toastContainer) {
    toastContainer = document.createElement("div");
    toastContainer.className = "toast-container";
    document.body.appendChild(toastContainer);
  }

  // Buat toast
  const toast = document.createElement("div");
  toast.className = `toast ${type}`;

  // Tentukan ikon berdasarkan tipe
  let icon = "";
  switch (type) {
    case "success":
      icon = '<i class="fas fa-check-circle"></i>';
      break;
    case "error":
      icon = '<i class="fas fa-exclamation-circle"></i>';
      break;
    case "warning":
      icon = '<i class="fas fa-exclamation-triangle"></i>';
      break;
    default:
      icon = '<i class="fas fa-info-circle"></i>';
  }

  toast.innerHTML = `
        <div class="toast-icon">${icon}</div>
        <div class="toast-content">
            ${title ? `<div class="toast-title">${title}</div>` : ""}
            <div class="toast-message">${message}</div>
        </div>
        <button class="toast-close">&times;</button>
    `;

  toastContainer.appendChild(toast);

  // Event listener untuk tombol close
  const closeBtn = toast.querySelector(".toast-close");
  closeBtn.addEventListener("click", () => {
    toast.remove();
  });

  // Hapus otomatis setelah 5 detik
  setTimeout(() => {
    if (toast.parentNode) {
      toast.remove();
    }
  }, 5000);
}

// Fungsi untuk menampilkan alert sederhana
function showAlert(message, type = "info") {
  // Buat element alert
  const alertDiv = document.createElement("div");
  alertDiv.className = `glass-alert glass-alert-${type}`;
  alertDiv.textContent = message;
  alertDiv.style.animation = "slideInRight 0.3s ease";

  // Tambahkan ke body
  document.body.appendChild(alertDiv);

  // Hapus setelah 5 detik
  setTimeout(() => {
    if (alertDiv.parentNode) {
      alertDiv.remove();
    }
  }, 5000);
}

// Fungsi validasi form
function validateForm(formId) {
  const form = document.getElementById(formId);
  const inputs = form.querySelectorAll("input[required], select[required]");

  for (let input of inputs) {
    if (!input.value.trim()) {
      showAlert(
        `Harap isi field ${
          input.previousElementSibling?.textContent || input.name
        }`,
        "error"
      );
      input.focus();
      return false;
    }
  }

  return true;
}

// Fungsi untuk membuat partikel background
function createParticles() {
  const particlesContainer = document.createElement("div");
  particlesContainer.className = "particles";
  document.body.appendChild(particlesContainer);

  // Buat 50 partikel
  for (let i = 0; i < 50; i++) {
    const particle = document.createElement("div");
    particle.className = "particle";

    // Ukuran acak
    const size = Math.random() * 10 + 5;
    particle.style.width = `${size}px`;
    particle.style.height = `${size}px`;

    // Posisi acak
    particle.style.left = `${Math.random() * 100}%`;

    // Delay animasi acak
    particle.style.animationDelay = `${Math.random() * 15}s`;

    // Durasi animasi acak
    particle.style.animationDuration = `${Math.random() * 10 + 15}s`;

    particlesContainer.appendChild(particle);
  }
}

// Fungsi untuk membuat bintang jatuh
function createFallingStars() {
  // Buat 5 bintang jatuh
  for (let i = 0; i < 5; i++) {
    setTimeout(() => {
      const star = document.createElement("div");
      star.className = "falling-star";

      // Posisi acak
      star.style.left = `${Math.random() * 100}%`;

      // Delay animasi acak
      star.style.animationDelay = `${Math.random() * 5}s`;

      document.body.appendChild(star);

      // Hapus setelah animasi selesai
      setTimeout(() => {
        star.remove();
      }, 3000);
    }, i * 2000);
  }

  // Ulangi setiap 10 detik
  setTimeout(createFallingStars, 10000);
}

// Fungsi untuk animasi roket saat register
function animateRocket() {
  const logo = document.querySelector(".auth-logo");
  const authCard = document.querySelector(".auth-card");

  if (logo && authCard) {
    // Buat efek asap
    const smoke = document.createElement("div");
    smoke.className = "rocket-smoke";

    // Buat beberapa partikel asap
    for (let i = 0; i < 10; i++) {
      const particle = document.createElement("div");
      particle.className = "smoke-particle";
      particle.style.left = `${Math.random() * 80 + 10}px`;
      particle.style.top = `${Math.random() * 80 + 10}px`;
      smoke.appendChild(particle);
    }

    // Buat efek cahaya
    const light = document.createElement("div");
    light.className = "rocket-light";

    authCard.appendChild(smoke);
    authCard.appendChild(light);

    // Jalankan animasi
    logo.classList.add("rocket-launch");
    smoke.classList.add("active");
    light.classList.add("active");

    // Hapus efek setelah animasi selesai
    setTimeout(() => {
      smoke.remove();
      light.remove();
    }, 1500);
  }
}

// Fungsi untuk live search
function setupLiveSearch(inputId, tableId, columnIndex) {
  const searchInput = document.getElementById(inputId);
  const table = document.getElementById(tableId);

  if (searchInput && table) {
    searchInput.addEventListener("input", function () {
      const searchTerm = this.value.toLowerCase();
      const rows = table.querySelectorAll("tbody tr");

      rows.forEach((row) => {
        const cell = row.querySelectorAll("td")[columnIndex];
        if (cell) {
          const text = cell.textContent.toLowerCase();
          row.style.display = text.includes(searchTerm) ? "" : "none";
        }
      });
    });
  }
}

// Fungsi untuk konfirmasi hapus
function confirmDelete(message, callback) {
  if (confirm(message)) {
    callback();
  }
}

// Inisialisasi saat halaman dimuat
document.addEventListener("DOMContentLoaded", function () {
  // Buat partikel background
  createParticles();

  // Buat bintang jatuh
  createFallingStars();

  // Setup live search untuk tabel siswa
  setupLiveSearch("search-siswa", "siswa-table", 1);

  // Setup live search untuk tabel guru
  setupLiveSearch("search-guru", "guru-table", 1);
});

// Export fungsi untuk digunakan di file lain
window.animateRocket = animateRocket;
window.showToast = showToast;
window.showAlert = showAlert;
window.validateForm = validateForm;
window.setupLiveSearch = setupLiveSearch;
window.confirmDelete = confirmDelete;
