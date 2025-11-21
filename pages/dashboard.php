<?php
session_start();
require_once '../config/database.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Ambil data statistik
$totalSiswaQuery = "SELECT COUNT(*) as total FROM siswa";
$totalSiswaResult = $conn->query($totalSiswaQuery);
$totalSiswa = $totalSiswaResult->fetch_assoc()['total'];

$totalGuruQuery = "SELECT COUNT(*) as total FROM guru";
$totalGuruResult = $conn->query($totalGuruQuery);
$totalGuru = $totalGuruResult->fetch_assoc()['total'];

$totalUserQuery = "SELECT COUNT(*) as total FROM users";
$totalUserResult = $conn->query($totalUserQuery);
$totalUser = $totalUserResult->fetch_assoc()['total'];

$userRole = $_SESSION['role'];

// Ambil data user untuk profil
$query = "SELECT * FROM users WHERE id_user = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Manajemen Sekolah</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/animation.css">
</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="../assets/images/logo_smk5.jpg" alt="Logo SMKN 5" class="sidebar-logo">
                <h2 class="sidebar-title">SMKN 5</h2>
            </div>

            <ul class="sidebar-menu">
                <li>
                    <a href="dashboard.php" class="active">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="siswa/list.php">
                        <i class="fas fa-graduation-cap"></i>
                        <span>Data Siswa</span>
                    </a>
                </li>
                <li>
                    <a href="guru/list.php">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span>Data Guru</span>
                    </a>
                </li>
                <li>
                    <a href="profile.php">
                        <i class="fas fa-user"></i>
                        <span>Profil</span>
                    </a>
                </li>
                <li>
                    <a href="logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="content-header">
                <h1 class="page-title">Dashboard</h1>
                <div class="user-info">
                    <div class="user-details">
                        <div class="user-name"><?php echo $_SESSION['nama']; ?></div>
                        <div class="user-role"><?php echo ucfirst($_SESSION['role']); ?></div>
                    </div>
                    <!-- ✅ Ganti bagian ini agar foto profil dinamis -->
                    <img
                        src="<?php echo !empty($user['profile_picture'])
                                    ? '../assets/images/profiles/' . htmlspecialchars($user['profile_picture'])
                                    : '../assets/images/default-avatar.png'; ?>"
                        alt="User Avatar"
                        class="user-avatar">
                </div>
            </div>

            <!-- Welcome Message -->
            <div class="glass-card fade-in">
                <h2 class="section-title">
                    <i class="fas fa-hand-wave"></i> Selamat Datang, <?php echo $_SESSION['nama']; ?>!
                </h2>
                <p style="color: rgba(255, 255, 255, 0.8);">
                    Anda login sebagai <strong><?php echo ucfirst($userRole); ?></strong> di Sistem Manajemen Sekolah SMKN 5 Kota Tangerang.
                </p>
            </div>

            <!-- Stats Cards -->
            <div class="stats-container">
                <div class="stat-card slide-down" style="animation-delay: 0.1s;">
                    <div class="stat-icon primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $totalSiswa; ?></h3>
                        <p>Total Siswa</p>
                    </div>
                </div>

                <div class="stat-card slide-down" style="animation-delay: 0.2s;">
                    <div class="stat-icon success">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $totalGuru; ?></h3>
                        <p>Total Guru</p>
                    </div>
                </div>

                <div class="stat-card slide-down" style="animation-delay: 0.3s;">
                    <div class="stat-icon warning">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $totalUser; ?></h3>
                        <p>Total Pengguna</p>
                    </div>
                </div>

                <div class="stat-card slide-down" style="animation-delay: 0.4s;">
                    <div class="stat-icon info">
                        <i class="fas fa-user-tag"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo ucfirst($userRole); ?></h3>
                        <p>Role Anda</p>
                    </div>
                </div>
            </div>

            <!-- Quick Menu -->
            <div class="quick-menu">
                <h2 class="section-title">
                    <i class="fas fa-rocket"></i> Menu Cepat
                </h2>

                <div class="quick-menu-container">
                    <div class="quick-menu-card zoom-in" style="animation-delay: 0.1s;">
                        <div class="quick-menu-header">
                            <div class="quick-menu-title">
                                <i class="fas fa-graduation-cap"></i> Menu Siswa
                            </div>
                            <i class="fas fa-chevron-down quick-menu-toggle"></i>
                        </div>
                        <div class="quick-menu-content">
                            <ul class="quick-menu-list">
                                <li><a href="siswa/list.php"><i class="fas fa-list"></i> Lihat Daftar Siswa</a></li>
                                <li><a href="siswa/add.php"><i class="fas fa-plus"></i> Tambah Siswa</a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="quick-menu-card zoom-in" style="animation-delay: 0.2s;">
                        <div class="quick-menu-header">
                            <div class="quick-menu-title">
                                <i class="fas fa-chalkboard-teacher"></i> Menu Guru
                            </div>
                            <i class="fas fa-chevron-down quick-menu-toggle"></i>
                        </div>
                        <div class="quick-menu-content">
                            <ul class="quick-menu-list">
                                <li><a href="guru/list.php"><i class="fas fa-list"></i> Lihat Daftar Guru</a></li>
                                <li><a href="guru/add.php"><i class="fas fa-plus"></i> Tambah Guru</a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="quick-menu-card zoom-in" style="animation-delay: 0.3s;">
                        <div class="quick-menu-header">
                            <div class="quick-menu-title">
                                <i class="fas fa-user-cog"></i> Menu Akun
                            </div>
                            <i class="fas fa-chevron-down quick-menu-toggle"></i>
                        </div>
                        <div class="quick-menu-content">
                            <ul class="quick-menu-list">
                                <li><a href="profile.php"><i class="fas fa-user"></i> Lihat Profil</a></li>
                                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                Developed by Kelompok 11 — Rafka, Fahri, Sri | © 2025 SMKN 5 Kota Tangerang
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/menu.js"></script>
</body>

</html>