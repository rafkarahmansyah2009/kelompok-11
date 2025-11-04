<?php
session_start();
require_once '../../config/database.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Proses tambah guru
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_guru = trim($_POST['nama_guru']);
    $mata_pelajaran = trim($_POST['mata_pelajaran']);

    if (empty($nama_guru) || empty($mata_pelajaran)) {
        $error = "Semua field wajib diisi!";
    } else {
        $query = "INSERT INTO guru (nama_guru, mata_pelajaran) VALUES (?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $nama_guru, $mata_pelajaran);

        if ($stmt->execute()) {
            $success = "✅ Data guru berhasil ditambahkan!";
            header("refresh:2;url=list.php");
        } else {
            $error = "❌ Data guru gagal ditambahkan!";
        }
    }
}

// Ambil data user
$user_id = $_SESSION['user_id'];
$query_user = "SELECT nama, role, profile_picture FROM users WHERE id_user = ?";
$stmt_user = $conn->prepare($query_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user = $result_user->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Guru - Sistem Manajemen Sekolah</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/animation.css">
    <style>
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
    </style>
</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="../../assets/images/logo_smk5.jpeg" alt="Logo SMKN 5" class="sidebar-logo">
                <h2 class="sidebar-title">SMKN 5</h2>
            </div>

            <ul class="sidebar-menu">
                <li><a href="../dashboard.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
                <li><a href="../siswa/list.php"><i class="fas fa-graduation-cap"></i><span>Data Siswa</span></a></li>
                <li><a href="list.php" class="active"><i class="fas fa-chalkboard-teacher"></i><span>Data Guru</span></a></li>
                <li><a href="../profile.php"><i class="fas fa-user"></i><span>Profil</span></a></li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="content-header">
                <h1 class="page-title">Tambah Guru</h1>
                <div class="user-info">
                    <div class="user-details">
                        <div class="user-name"><?php echo $_SESSION['nama']; ?></div>
                        <div class="user-role"><?php echo ucfirst($_SESSION['role']); ?></div>
                    </div>
                    <img src="<?php echo !empty($user['profile_picture'])
                                    ? '../../assets/images/profiles/' . htmlspecialchars($user['profile_picture'])
                                    : '../../assets/images/default-avatar.png'; ?>"
                        alt="User Avatar" class="user-avatar">
                </div>
            </div>

            <!-- Form Container -->
            <div class="form-container fade-in">
                <h2 class="form-title">Form Tambah Guru</h2>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="post" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nama_guru" class="form-label">Nama Guru</label>
                            <input type="text" id="nama_guru" name="nama_guru" class="form-control" placeholder="Masukkan nama guru" required>
                        </div>

                        <div class="form-group">
                            <label for="mata_pelajaran" class="form-label">Mata Pelajaran</label>
                            <select id="mata_pelajaran" name="mata_pelajaran" class="form-control" required>
                                <option value="">📘 Pilih Mata Pelajaran</option>
                                <option value="Matematika">🧮 Matematika</option>
                                <option value="RPL">💻 Rekayasa Perangkat Lunak (RPL)</option>
                                <option value="Informatika">🧠 Informatika</option>
                                <option value="PJOK">⚽ Pendidikan Jasmani, Olahraga, dan Kesehatan (PJOK)</option>
                                <option value="Bahasa Indonesia">📚 Bahasa Indonesia</option>
                                <option value="Bahasa Inggris">🗣️ Bahasa Inggris</option>
                                <option value="Sejarah Indonesia">🏰 Sejarah Indonesia</option>
                                <option value="PPKN">🦅 Pendidikan Pancasila dan Kewarganegaraan (PPKN)</option>
                                <option value="PKK">💼 Produk Kreatif dan Kewirausahaan (PKK)</option>
                                <option value="Mapel Pilihan">🎯 Mata Pelajaran Pilihan</option>
                                <option value="Agama Islam">🕌 Pendidikan Agama Islam</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="list.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary glow-effect">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>

            <div class="footer">
                Developed by Kelompok 11 — Rafka, Fahri, Sri | © 2025 SMKN 5 Kota Tangerang
            </div>
        </div>
    </div>

    <script src="../../assets/js/main.js"></script>
    <script src="../../assets/js/menu.js"></script>
</body>

</html>