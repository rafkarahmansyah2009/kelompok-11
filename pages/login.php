<?php
session_start();
require_once '../config/database.php';

// Jika user sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

// Proses login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query untuk mendapatkan user
    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verifikasi password
        if ($password == $user['password']) {
            // Set session
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['role'] = $user['role'];

            // Berikan respons JSON untuk AJAX
            $response = array(
                'success' => true,
                'message' => 'Login berhasil!'
            );
            echo json_encode($response);
            exit();
        } else {
            $error = "Password salah!";

            // Berikan respons JSON untuk AJAX
            $response = array(
                'success' => false,
                'message' => $error
            );
            echo json_encode($response);
            exit();
        }
    } else {
        $error = "Username tidak ditemukan!";

        // Berikan respons JSON untuk AJAX
        $response = array(
            'success' => false,
            'message' => $error
        );
        echo json_encode($response);
        exit();
    }
}

// Ambil pesan sukses dari session
$success_message = '';
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Manajemen Sekolah</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/animation.css">
    <link rel="stylesheet" href="../assets/css/responsive-auth.css">
    <style>
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

        /* Progress Bar */
        .progress-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 3000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .progress-container.active {
            opacity: 1;
            visibility: visible;
        }

        .progress-content {
            text-align: center;
            color: white;
        }

        .progress-logo {
            width: 100px;
            height: 100px;
            margin-bottom: 2rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }

        .progress-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .progress-bar-container {
            width: 300px;
            height: 6px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
            overflow: hidden;
            margin-bottom: 1rem;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            border-radius: 3px;
            width: 0%;
            transition: width 0.3s ease;
        }

        .progress-text {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        /* Loading Animation */
        .loading-dots {
            display: inline-block;
        }

        .loading-dots::after {
            content: '';
            animation: dots 1.5s steps(4, end) infinite;
        }

        @keyframes dots {

            0%,
            20% {
                content: '';
            }

            40% {
                content: '.';
            }

            60% {
                content: '..';
            }

            80%,
            100% {
                content: '...';
            }
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
                <img src="../assets/images/logo_smk5.jpg" alt="Logo SMKN 5" class="auth-logo">
                <h1 class="auth-title">Sistem Manajemen Sekolah</h1>
                <p class="auth-subtitle">SMKN 5 Kota Tangerang</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="glass-alert glass-alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="glass-alert glass-alert-success">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <form method="post" action="" id="login-form">
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

                <button type="submit" class="btn btn-primary" id="login-btn">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>

            <div class="auth-footer">
                Belum punya akun? <a href="register.php">Daftar sekarang</a>
            </div>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="progress-container" id="progress-container">
        <div class="progress-content">
            <img src="../assets/images/bg-sekolah.jpg" alt="Logo SMKN 5" class="progress-logo">
            <h2 class="progress-title">Sedang Masuk<span class="loading-dots"></span></h2>
            <div class="progress-bar-container">
                <div class="progress-bar" id="progress-bar"></div>
            </div>
            <p class="progress-text">Mohon tunggu sebentar</p>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Form submission with AJAX
            const loginForm = document.getElementById('login-form');
            const loginBtn = document.getElementById('login-btn');
            const progressContainer = document.getElementById('progress-container');
            const progressBar = document.getElementById('progress-bar');

            loginForm.addEventListener('submit', function(e) {
                e.preventDefault();

                // Validasi form
                if (!loginForm.checkValidity()) {
                    loginForm.reportValidity();
                    return;
                }

                // Disable button and show loading
                loginBtn.disabled = true;
                loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

                // Show progress container
                progressContainer.classList.add('active');

                // Simulate progress
                let progress = 0;
                const progressInterval = setInterval(function() {
                    progress += 10;
                    progressBar.style.width = progress + '%';

                    if (progress >= 100) {
                        clearInterval(progressInterval);
                    }
                }, 100);

                // Create form data
                const formData = new FormData(loginForm);

                // Send AJAX request
                const xhr = new XMLHttpRequest();
                xhr.open('POST', '', true);

                xhr.onload = function() {
                    // Enable button
                    loginBtn.disabled = false;
                    loginBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Login';

                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);

                            if (response.success) {
                                // Complete progress bar
                                progressBar.style.width = '100%';

                                // Redirect to dashboard after a short delay
                                setTimeout(function() {
                                    window.location.href = 'dashboard.php';
                                }, 500);
                            } else {
                                // Hide progress container
                                progressContainer.classList.remove('active');
                                progressBar.style.width = '0%';

                                // Show error message
                                alert(response.message);
                            }
                        } catch (e) {
                            // Hide progress container
                            progressContainer.classList.remove('active');
                            progressBar.style.width = '0%';

                            alert('Terjadi kesalahan dalam memproses respons server.');
                        }
                    } else {
                        // Hide progress container
                        progressContainer.classList.remove('active');
                        progressBar.style.width = '0%';

                        alert('Terjadi kesalahan. Silakan coba lagi.');
                    }
                };

                xhr.onerror = function() {
                    // Enable button
                    loginBtn.disabled = false;
                    loginBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Login';

                    // Hide progress container
                    progressContainer.classList.remove('active');
                    progressBar.style.width = '0%';

                    alert('Terjadi kesalahan koneksi. Silakan coba lagi.');
                };

                xhr.send(formData);
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