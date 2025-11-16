<?php
session_start();
require_once '../config/database.php';

// Jika user sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

// Proses registrasi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $nama = $_POST['nama'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $role = $_POST['role'];

    // Cek apakah username sudah ada
    $checkQuery = "SELECT * FROM users WHERE username = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("s", $username);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $error = "Username sudah digunakan!";
    } else {
        // Insert user baru
        $insertQuery = "INSERT INTO users (username, password, nama, jenis_kelamin, role) VALUES (?, ?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param("sssss", $username, $password, $nama, $jenis_kelamin, $role);

        if ($insertStmt->execute()) {
            $user_id = $insertStmt->insert_id;

            // Jika role adalah siswa, tambahkan data ke tabel siswa
            if ($role == 'siswa') {
                $kelas = $_POST['kelas'];
                $id_guru = !empty($_POST['id_guru']) ? $_POST['id_guru'] : null;
                $jurusan = $_POST['jurusan'];

                $siswaQuery = "INSERT INTO siswa (nama_siswa, kelas, id_guru, jurusan) VALUES (?, ?, ?, ?)";
                $siswaStmt = $conn->prepare($siswaQuery);
                $siswaStmt->bind_param("ssis", $nama, $kelas, $id_guru, $jurusan);
                $siswaStmt->execute();
            }

            // Jika role adalah guru, tambahkan data ke tabel guru
            if ($role == 'guru') {
                $mata_pelajaran = $_POST['mata_pelajaran'];

                $guruQuery = "INSERT INTO guru (nama_guru, mata_pelajaran) VALUES (?, ?)";
                $guruStmt = $conn->prepare($guruQuery);
                $guruStmt->bind_param("ss", $nama, $mata_pelajaran);
                $guruStmt->execute();
            }

            // Redirect ke halaman login dengan pesan sukses
            $_SESSION['success_message'] = "Registrasi berhasil! Silakan login.";
            header('Location: login.php');
            exit();
        } else {
            $error = "Registrasi gagal! Silakan coba lagi.";
        }
    }
}

// Query untuk mendapatkan data guru (untuk wali kelas)
$guruQuery = "SELECT * FROM guru ORDER BY nama_guru";
$guruResult = $conn->query($guruQuery);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Sistem Manajemen Sekolah</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/animation.css">
    <link rel="stylesheet" href="../assets/css/responsive-auth.css">
    <style>
        .form-section {
            display: none;
            animation: fadeIn 0.5s ease-in-out;
        }

        .form-section.active {
            display: block;
        }

        /* Password toggle */
        .password-wrapper {
            position: relative;
        }

        .password-wrapper input {
            width: 100%;
            padding-right: 2.5rem;
        }

        .password-toggle {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: rgba(255, 255, 255, 0.6);
            transition: color 0.3s ease, transform 0.2s ease;
        }

        .password-toggle:hover {
            color: var(--accent-color);
            transform: translateY(-50%) scale(1.15);
        }

        .password-toggle i {
            font-size: 1.1rem;
        }

        /* Perbaiki tampilan dropdown list (khusus Chrome/Edge) */
        select.form-control,
        .custom-select select {
            background-color: rgba(255, 255, 255, 0.15);
            color: #fff;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
        }

        /* Warna saat list dropdown dibuka */
        .custom-select select option {
            background-color: rgba(58, 43, 131, 0.95);
            /* ungu gelap */
            color: #fff;
        }

        /* Hover pada opsi dropdown */
        .custom-select select option:hover {
            background-color: rgba(100, 80, 200, 0.95);
            color: #fff;
        }


        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .role-toggle {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .role-option {
            flex: 1;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .role-option:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-5px);
        }

        .role-option.selected {
            background: rgba(3, 218, 198, 0.2);
            border-color: var(--accent-color);
            box-shadow: 0 0 10px rgba(3, 218, 198, 0.3);
        }

        .role-option i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            display: block;
        }

        .role-option span {
            display: block;
            font-weight: 600;
        }

        /* Style dropdown (select) agar senada dengan tema */
        select.form-control {
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            padding: 0.6rem;
            font-size: 0.95rem;
            width: 100%;
            transition: all 0.3s ease;
        }

        /* Saat fokus (klik dropdown) */
        select.form-control:focus {
            outline: none;
            border-color: var(--accent-color);
            background-color: rgba(255, 255, 255, 0.15);
            box-shadow: 0 0 8px rgba(3, 218, 198, 0.3);
        }

        /* Warna teks option di dalam dropdown */
        select.form-control option {
            background-color: #3a2b83;
            /* ungu gelap biar kontras */
            color: #fff;
        }

        /* Custom select dengan icon */
        .custom-select {
            position: relative;
            display: block;
            width: 100%;
        }

        .custom-select select {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            color: white;
            font-size: 1rem;
            transition: all 0.3s ease;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
        }

        .custom-select select:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(3, 218, 198, 0.2);
        }

        .custom-select select::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .custom-select option {
            background: #333;
            color: white;
        }

        .custom-select i {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.6);
            pointer-events: none;
        }

        .select-arrow {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.6);
            pointer-events: none;
        }

        /* Glassmorphism Alert */
        .glass-alert {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 1rem 1.5rem;
            margin-bottom: 1rem;
            position: relative;
            animation: slideInRight 0.3s ease;
        }

        .glass-alert-success {
            background: rgba(76, 175, 80, 0.2);
            border-color: rgba(76, 175, 80, 0.3);
        }

        .glass-alert-danger {
            background: rgba(244, 67, 54, 0.2);
            border-color: rgba(244, 67, 54, 0.3);
        }

        .glass-alert-warning {
            background: rgba(255, 152, 0, 0.2);
            border-color: rgba(255, 152, 0, 0.3);
        }

        .glass-alert-info {
            background: rgba(33, 150, 243, 0.2);
            border-color: rgba(33, 150, 243, 0.3);
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>
</head>

<body>
    <div class="auth-container">
        <div class="auth-card glass-card">
            <div class="auth-header">
                <img src="../assets/images/logo_smk5.jpeg" alt="Logo SMKN 5" class="auth-logo" id="rocket-logo">
                <h1 class="auth-title">Buat Akun Baru</h1>
                <p class="auth-subtitle">SMKN 5 Kota Tangerang</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="glass-alert glass-alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="post" action="" id="register-form">
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username" required>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" required>
                        <span class="password-toggle" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>


                <div class="form-group">
                    <label for="nama" class="form-label">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama" class="form-control" placeholder="Masukkan nama lengkap" required>
                </div>

                <div class="form-group">
                    <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                    <div class="custom-select">
                        <i class="fas fa-venus-mars"></i>
                        <select id="jenis_kelamin" name="jenis_kelamin" required>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="Pria">Pria</option>
                            <option value="Wanita">Wanita</option>
                        </select>
                        <i class="fas fa-chevron-down select-arrow"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Pilih Role</label>
                    <div class="role-toggle">
                        <div class="role-option" data-role="siswa">
                            <i class="fas fa-graduation-cap"></i>
                            <span>Siswa</span>
                        </div>
                        <div class="role-option" data-role="guru">
                            <i class="fas fa-chalkboard-teacher"></i>
                            <span>Guru</span>
                        </div>
                    </div>
                    <input type="hidden" id="role" name="role" required>
                </div>

                <!-- Form Siswa -->
                <div id="siswa-form" class="form-section">
                    <div class="form-group">
                        <label for="kelas" class="form-label">Kelas</label>
                        <input type="text" id="kelas" name="kelas" class="form-control" placeholder="Masukkan kelas">
                    </div>

                    <div class="form-group">
                        <label for="id_guru" class="form-label">Wali Kelas (Opsional)</label>
                        <div class="custom-select">
                            <i class="fas fa-user-tie"></i>
                            <select id="id_guru" name="id_guru">
                                <option value="">Pilih Wali Kelas</option>
                                <?php
                                if ($guruResult->num_rows > 0) {
                                    while ($row = $guruResult->fetch_assoc()) {
                                        echo "<option value='" . $row['id_guru'] . "'>" . $row['nama_guru'] . " (" . $row['mata_pelajaran'] . ")</option>";
                                    }
                                }
                                ?>
                            </select>
                            <i class="fas fa-chevron-down select-arrow"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="jurusan" class="form-label">Jurusan</label>
                        <div class="custom-select">
                            <i class="fas fa-school"></i>
                            <select id="jurusan" name="jurusan">
                                <option value="">Pilih Jurusan</option>
                                <option value="RPL">🖥️ Rekayasa Perangkat Lunak</option>
                                <option value="TKJ">🌐 Teknik Komputer dan Jaringan</option>
                                <option value="DKV">🎨 Desain Komunikasi Visual</option>
                                <option value="MP"> 🏢 Manajemen Perkantoran</option>

                            </select>
                            <i class="fas fa-chevron-down select-arrow"></i>
                        </div>
                    </div>
                </div>

                <!-- Form Guru -->
                <div id="guru-form" class="form-section">
                    <div class="form-group">
                        <label for="mata_pelajaran" class="form-label">Mata Pelajaran</label>
                        <div class="custom-select">
                            <i class="fas fa-book-open"></i>
                            <select id="mata_pelajaran" name="mata_pelajaran" required>
                                <option value="">📘 Pilih Mata Pelajaran</option>
                                <option value="RPL">💻 Rekayasa Perangkat Lunak (RPL)</option>
                                <option value="TKJ">🌐 Teknik Komputer dan Jaringan</option>
                                <option value="DKV">🎨 Desain Komunikasi Visual</option>
                                <option value="MP"> 🏢 Manajemen Perkantoran</option>
                                <option value="Matematika">🧮 Matematika</option>
                                <option value="Informatika">🧠 Informatika</option>
                                <option value="PJOK">⚽ Pendidikan Jasmani, Olahraga, dan Kesehatan</option>
                                <option value="Bahasa Indonesia">📚 Bahasa Indonesia</option>
                                <option value="Bahasa Inggris">🗣️ Bahasa Inggris</option>
                                <option value="Sejarah Indonesia">🏰 Sejarah Indonesia</option>
                                <option value="PPKN">🦅 Pendidikan Pancasila dan Kewarganegaraan</option>
                                <option value="PKK">💼 Produk Kreatif dan Kewirausahaan (PKK)</option>
                                <option value="Mapel Pilihan">🎯 Mata Pelajaran Pilihan</option>
                                <option value="Agama Islam">🕌 Pendidikan Agama Islam</option>
                            </select>
                            <i class="fas fa-chevron-down select-arrow"></i>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" id="register-btn">
                    <i class="fas fa-user-plus"></i> Register
                </button>
            </form>

            <div class="auth-footer">
                Sudah punya akun? <a href="login.php">Login di sini</a>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Role selection
            const roleOptions = document.querySelectorAll('.role-option');
            const roleInput = document.getElementById('role');
            const siswaForm = document.getElementById('siswa-form');
            const guruForm = document.getElementById('guru-form');
            const registerBtn = document.getElementById('register-btn');

            roleOptions.forEach(option => {
                option.addEventListener('click', function() {
                    // Remove selected class from all options
                    roleOptions.forEach(opt => opt.classList.remove('selected'));

                    // Add selected class to clicked option
                    this.classList.add('selected');

                    // Set role value
                    const role = this.getAttribute('data-role');
                    roleInput.value = role;

                    // Show/hide forms based on role
                    if (role === 'siswa') {
                        siswaForm.classList.add('active');
                        guruForm.classList.remove('active');

                        // Set required fields for siswa
                        document.getElementById('kelas').required = true;
                        document.getElementById('jurusan').required = true;
                        document.getElementById('mata_pelajaran').required = false;
                    } else if (role === 'guru') {
                        siswaForm.classList.remove('active');
                        guruForm.classList.add('active');

                        // Set required fields for guru
                        document.getElementById('kelas').required = false;
                        document.getElementById('jurusan').required = false;
                        document.getElementById('mata_pelajaran').required = true;
                    }
                });
            });

            // Form submission dengan validasi
            const registerForm = document.getElementById('register-form');

            registerForm.addEventListener('submit', function(e) {
                // Validasi role dipilih
                if (!roleInput.value) {
                    e.preventDefault();
                    showAlert('Silakan pilih role terlebih dahulu!', 'error');
                    return;
                }

                // Validasi berdasarkan role
                if (roleInput.value === 'siswa') {
                    const kelas = document.getElementById('kelas').value;
                    const jurusan = document.getElementById('jurusan').value;

                    if (!kelas || !jurusan) {
                        e.preventDefault();
                        showAlert('Harap lengkapi semua field untuk siswa!', 'error');
                        return;
                    }
                }

                if (roleInput.value === 'guru') {
                    const mataPelajaran = document.getElementById('mata_pelajaran').value;

                    if (!mataPelajaran) {
                        e.preventDefault();
                        showAlert('Harap isi mata pelajaran!', 'error');
                        return;
                    }
                }

                // Validasi form utama
                const username = document.getElementById('username').value;
                const password = document.getElementById('password').value;
                const nama = document.getElementById('nama').value;
                const jenisKelamin = document.getElementById('jenis_kelamin').value;

                if (!username || !password || !nama || !jenisKelamin) {
                    e.preventDefault();
                    showAlert('Harap lengkapi semua field!', 'error');
                    return;
                }

                // Disable button dan show loading
                registerBtn.disabled = true;
                registerBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mendaftar...';

                // Animasi roket jika fungsi tersedia
                if (typeof animateRocket === 'function') {
                    animateRocket();
                }
            });
        });
        document.addEventListener('DOMContentLoaded', function() {
            const toggle = document.getElementById('togglePassword');
            const password = document.getElementById('password');

            toggle.addEventListener('click', function() {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);

                // Ganti ikon mata
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
        });
    </script>
</body>

</html>