<?php
session_start();
require_once '../../config/database.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Ambil ID siswa dari URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: list.php');
    exit();
}

$id_siswa = $_GET['id'];

// Query untuk mendapatkan data siswa
$query = "SELECT * FROM siswa WHERE id_siswa = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_siswa);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: list.php');
    exit();
}

$siswa = $result->fetch_assoc();

// Data jurusan dengan icon
$jurusan_options = [
    'RPL' => ['name' => 'Rekayasa Perangkat Lunak', 'icon' => 'fa-code'],
    'TKJ' => ['name' => 'Teknik Komputer dan Jaringan', 'icon' => 'fa-network-wired'],
    'DKV' => ['name' => 'Desain Komunikasi Visual', 'icon' => 'fa-palette'],
    'MP' => ['name' => 'Manajemen Perkantoran', 'icon' => 'fa-briefcase']
];

// Proses edit siswa
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_siswa = $_POST['nama_siswa'];
    $kelas = $_POST['kelas'];
    $id_guru = !empty($_POST['id_guru']) ? $_POST['id_guru'] : null;
    $jurusan = $_POST['jurusan'];

    // Update data siswa
    $updateQuery = "UPDATE siswa SET nama_siswa = ?, kelas = ?, id_guru = ?, jurusan = ? WHERE id_siswa = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ssisi", $nama_siswa, $kelas, $id_guru, $jurusan, $id_siswa);

    if ($updateStmt->execute()) {
        $_SESSION['success'] = "Data siswa berhasil diperbarui!";
        header('Location: list.php');
        exit();
    } else {
        $error = "Data siswa gagal diperbarui!";
    }
}
// ✅ Ambil data user (biar foto profil muncul)
$user_id = $_SESSION['user_id'];
$query_user = "SELECT nama, role, profile_picture FROM users WHERE id_user = ?";
$stmt_user = $conn->prepare($query_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user = $result_user->fetch_assoc();

// Query untuk mendapatkan data guru
$guruQuery = "SELECT * FROM guru ORDER BY nama_guru";
$guruResult = $conn->query($guruQuery);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Siswa - Sistem Manajemen Sekolah</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/animation.css">
    <style>
        .jurusan-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .jurusan-option {
            padding: 1rem;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .jurusan-option:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-5px);
        }

        .jurusan-option.selected {
            background: rgba(3, 218, 198, 0.2);
            border-color: var(--accent-color);
            box-shadow: 0 0 10px rgba(3, 218, 198, 0.3);
        }

        .jurusan-option i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            display: block;
        }

        .jurusan-option span {
            display: block;
            font-weight: 600;
            font-size: 0.9rem;
        }

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
                <img src="../../assets/images/logo_smk5.jpg" alt="Logo SMKN 5" class="sidebar-logo">
                <h2 class="sidebar-title">SMKN 5</h2>
            </div>

            <ul class="sidebar-menu">
                <li>
                    <a href="../dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="list.php" class="active">
                        <i class="fas fa-graduation-cap"></i>
                        <span>Data Siswa</span>
                    </a>
                </li>
                <li>
                    <a href="../guru/list.php">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span>Data Guru</span>
                    </a>
                </li>
                <li>
                    <a href="../profile.php">
                        <i class="fas fa-user"></i>
                        <span>Profil</span>
                    </a>
                </li>
                <li>
                    <a href="../logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="content-header">
                <h1 class="page-title">Edit Siswa</h1>
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
                <h2 class="form-title">Form Edit Siswa</h2>

                <?php if (isset($error)): ?>
                    <div class="glass-alert glass-alert-danger">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="glass-alert glass-alert-success">
                        <?php echo $_SESSION['success'];
                        unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nama_siswa" class="form-label">Nama Siswa</label>
                            <input type="text" id="nama_siswa" name="nama_siswa" class="form-control" placeholder="Masukkan nama siswa" value="<?php echo $siswa['nama_siswa']; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="kelas" class="form-label">Kelas</label>
                            <input type="text" id="kelas" name="kelas" class="form-control" placeholder="Masukkan kelas" value="<?php echo $siswa['kelas']; ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="id_guru" class="form-label">Wali Kelas</label>
                            <select id="id_guru" name="id_guru" class="form-control">
                                <option value="">Pilih Wali Kelas</option>
                                <?php
                                if ($guruResult->num_rows > 0) {
                                    while ($row = $guruResult->fetch_assoc()) {
                                        $selected = ($row['id_guru'] == $siswa['id_guru']) ? 'selected' : '';
                                        echo "<option value='" . $row['id_guru'] . "' $selected>" . $row['nama_guru'] . " (" . $row['mata_pelajaran'] . ")</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Jurusan</label>
                        <div class="jurusan-options">
                            <?php foreach ($jurusan_options as $code => $jurusan): ?>
                                <div class="jurusan-option <?php echo ($siswa['jurusan'] == $code) ? 'selected' : ''; ?>" data-jurusan="<?php echo $code; ?>">
                                    <i class="fas <?php echo $jurusan['icon']; ?>"></i>
                                    <span><?php echo $jurusan['name']; ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" id="jurusan" name="jurusan" value="<?php echo $siswa['jurusan']; ?>" required>
                    </div>

                    <div class="form-actions">
                        <a href="list.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary glow-effect">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="footer">
                Developed by Kelompok 11 — Rafka, Fahri, Sri | © 2025 SMKN 5 Kota Tangerang
            </div>
        </div>
    </div>

    <script src="../../assets/js/main.js"></script>
    <script src="../../assets/js/menu.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Jurusan selection
            const jurusanOptions = document.querySelectorAll('.jurusan-option');
            const jurusanInput = document.getElementById('jurusan');

            jurusanOptions.forEach(option => {
                option.addEventListener('click', function() {
                    // Remove selected class from all options
                    jurusanOptions.forEach(opt => opt.classList.remove('selected'));

                    // Add selected class to clicked option
                    this.classList.add('selected');

                    // Set jurusan value
                    const jurusan = this.getAttribute('data-jurusan');
                    jurusanInput.value = jurusan;
                });
            });
        });
    </script>
</body>

</html>