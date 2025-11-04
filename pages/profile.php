<?php
session_start();
require_once '../config/database.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Ambil data user dari database
$query = "SELECT * FROM users WHERE id_user = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: dashboard.php');
    exit();
}

$user = $result->fetch_assoc();

// Proses update profil
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    $jenis_kelamin = $_POST['jenis_kelamin'];

    // Handle profile picture upload
    $profile_picture = $user['profile_picture']; // Keep existing picture if not updated

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $target_dir = "../assets/images/profiles/";

        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = time() . '_' . basename($_FILES["profile_picture"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
        if ($check !== false) {
            // Allow certain file formats
            if ($imageFileType == "jpg" || $imageFileType == "png" || $imageFileType == "jpeg" || $imageFileType == "gif") {
                // Delete old profile picture if exists
                if ($user['profile_picture'] && file_exists($target_dir . $user['profile_picture'])) {
                    unlink($target_dir . $user['profile_picture']);
                }

                // Upload file
                if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                    $profile_picture = $file_name;
                }
            }
        }
    }

    // Update data user
    $updateQuery = "UPDATE users SET nama = ?, jenis_kelamin = ?, profile_picture = ? WHERE id_user = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("sssi", $nama, $jenis_kelamin, $profile_picture, $_SESSION['user_id']);

    if ($updateStmt->execute()) {
        // Update session
        $_SESSION['nama'] = $nama;

        $_SESSION['success'] = "Profil berhasil diperbarui!";

        // Refresh data user
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        header('Location: profile.php');
        exit();
    } else {
        $_SESSION['error'] = "Profil gagal diperbarui!";
    }
}

// Data jurusan dengan icon
$jurusan_options = [
    'RPL' => ['name' => 'Rekayasa Perangkat Lunak', 'icon' => 'fa-code'],
    'Teknik' => ['name' => 'Teknik', 'icon' => 'fa-cogs'],
    'TKJ' => ['name' => 'Teknik Komputer dan Jaringan', 'icon' => 'fa-network-wired'],
    'DKV' => ['name' => 'Desain Komunikasi Visual', 'icon' => 'fa-palette'],
    'MP' => ['name' => 'Manajemen Perkantoran', 'icon' => 'fa-briefcase']
];

// Get jurusan name from code
function getJurusanName($code, $jurusan_options)
{
    return isset($jurusan_options[$code]) ? $jurusan_options[$code]['name'] : $code;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - Sistem Manajemen Sekolah</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/animation.css">
    <style>
        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--accent-color);
            margin-bottom: 1rem;
        }

        .profile-upload {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .profile-preview {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .profile-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-preview i {
            font-size: 2rem;
            color: rgba(255, 255, 255, 0.6);
        }

        .info-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .info-card h3 {
            color: white;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-item {
            display: flex;
            margin-bottom: 0.75rem;
        }

        .info-label {
            width: 150px;
            color: rgba(255, 255, 255, 0.7);
            font-weight: 500;
        }

        .info-value {
            color: white;
            flex: 1;
        }

        .jurusan-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(3, 218, 198, 0.2);
            border: 1px solid rgba(3, 218, 198, 0.3);
            border-radius: 20px;
            padding: 0.25rem 0.75rem;
            color: white;
            font-size: 0.9rem;
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
    </style>
</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="../assets/images/logo_smk5.jpeg" alt="Logo SMKN 5" class="sidebar-logo">
                <h2 class="sidebar-title">SMKN 5</h2>
            </div>

            <ul class="sidebar-menu">
                <li>
                    <a href="dashboard.php">
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
                    <a href="profile.php" class="active">
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
                <h1 class="page-title">Profil Saya</h1>
                <div class="user-info">
                    <div class="user-details">
                        <div class="user-name"><?php echo $_SESSION['nama']; ?></div>
                        <div class="user-role"><?php echo ucfirst($_SESSION['role']); ?></div>
                    </div>
                    <img src="<?php echo $user['profile_picture'] ? '../assets/images/profiles/' . $user['profile_picture'] : '../assets/images/default-avatar.png'; ?>" alt="User Avatar" class="user-avatar">
                </div>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="glass-alert glass-alert-danger">
                    <?php echo $_SESSION['error'];
                    unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="glass-alert glass-alert-success">
                    <?php echo $_SESSION['success'];
                    unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <!-- Profile Info -->
            <div class="info-card fade-in">
                <h3><i class="fas fa-user"></i> Informasi Profil</h3>

                <div class="info-item">
                    <div class="info-label">Username:</div>
                    <div class="info-value"><?php echo $user['username']; ?></div>
                </div>

                <div class="info-item">
                    <div class="info-label">Nama Lengkap:</div>
                    <div class="info-value"><?php echo $user['nama']; ?></div>
                </div>

                <div class="info-item">
                    <div class="info-label">Jenis Kelamin:</div>
                    <div class="info-value"><?php echo $user['jenis_kelamin']; ?></div>
                </div>

                <div class="info-item">
                    <div class="info-label">Role:</div>
                    <div class="info-value"><?php echo ucfirst($user['role']); ?></div>
                </div>
            </div>

            <!-- Additional Info based on role -->
            <?php if ($user['role'] == 'siswa'): ?>
                <?php
                // Get siswa data
                $siswaQuery = "SELECT s.*, g.nama_guru FROM siswa s LEFT JOIN guru g ON s.id_guru = g.id_guru WHERE s.nama_siswa = ?";
                $siswaStmt = $conn->prepare($siswaQuery);
                $siswaStmt->bind_param("s", $user['nama']);
                $siswaStmt->execute();
                $siswaResult = $siswaStmt->get_result();

                if ($siswaResult->num_rows > 0):
                    $siswa = $siswaResult->fetch_assoc();
                ?>
                    <div class="info-card fade-in" style="animation-delay: 0.1s;">
                        <h3><i class="fas fa-graduation-cap"></i> Informasi Siswa</h3>

                        <div class="info-item">
                            <div class="info-label">Kelas:</div>
                            <div class="info-value"><?php echo $siswa['kelas'] ? $siswa['kelas'] : '-'; ?></div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Wali Kelas:</div>
                            <div class="info-value"><?php echo $siswa['nama_guru'] ? $siswa['nama_guru'] : '-'; ?></div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Jurusan:</div>
                            <div class="info-value">
                                <?php if ($siswa['jurusan']): ?>
                                    <div class="jurusan-badge">
                                        <i class="fas <?php echo $jurusan_options[$siswa['jurusan']]['icon']; ?>"></i>
                                        <?php echo getJurusanName($siswa['jurusan'], $jurusan_options); ?>
                                    </div>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($user['role'] == 'guru'): ?>
                <?php
                // Get guru data
                $guruQuery = "SELECT * FROM guru WHERE nama_guru = ?";
                $guruStmt = $conn->prepare($guruQuery);
                $guruStmt->bind_param("s", $user['nama']);
                $guruStmt->execute();
                $guruResult = $guruStmt->get_result();

                if ($guruResult->num_rows > 0):
                    $guru = $guruResult->fetch_assoc();
                ?>
                    <div class="info-card fade-in" style="animation-delay: 0.1s;">
                        <h3><i class="fas fa-chalkboard-teacher"></i> Informasi Guru</h3>

                        <div class="info-item">
                            <div class="info-label">Mata Pelajaran:</div>
                            <div class="info-value"><?php echo $guru['mata_pelajaran']; ?></div>
                        </div>

                        <?php
                        // Get siswa count for this guru
                        $siswaCountQuery = "SELECT COUNT(*) as count FROM siswa WHERE id_guru = ?";
                        $siswaCountStmt = $conn->prepare($siswaCountQuery);
                        $siswaCountStmt->bind_param("i", $guru['id_guru']);
                        $siswaCountStmt->execute();
                        $siswaCountResult = $siswaCountStmt->get_result();
                        $siswaCount = $siswaCountResult->fetch_assoc()['count'];
                        ?>

                        <div class="info-item">
                            <div class="info-label">Jumlah Siswa:</div>
                            <div class="info-value"><?php echo $siswaCount; ?> siswa</div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Edit Profile Form -->
            <div class="form-container fade-in" style="animation-delay: 0.2s;">
                <h2 class="form-title">Edit Profil</h2>

                <form method="post" action="" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nama" class="form-label">Nama Lengkap</label>
                            <input type="text" id="nama" name="nama" class="form-control" value="<?php echo $user['nama']; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                            <select id="jenis_kelamin" name="jenis_kelamin" class="form-control" required>
                                <option value="Pria" <?php echo ($user['jenis_kelamin'] == 'Pria') ? 'selected' : ''; ?>>Pria</option>
                                <option value="Wanita" <?php echo ($user['jenis_kelamin'] == 'Wanita') ? 'selected' : ''; ?>>Wanita</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Foto Profil</label>
                        <div class="profile-upload">
                            <div class="profile-preview" id="profile-preview">
                                <?php if ($user['profile_picture']): ?>
                                    <img src="../assets/images/profiles/<?php echo $user['profile_picture']; ?>" alt="Profile Picture">
                                <?php else: ?>
                                    <i class="fas fa-user"></i>
                                <?php endif; ?>
                            </div>
                            <div>
                                <input type="file" id="profile_picture" name="profile_picture" accept="image/*" style="display: none;">
                                <button type="button" class="btn btn-secondary" onclick="document.getElementById('profile_picture').click()">
                                    <i class="fas fa-upload"></i> Ganti Foto
                                </button>
                                <p style="font-size: 0.8rem; margin-top: 0.5rem; color: rgba(255, 255, 255, 0.6);">
                                    Format: JPG, PNG, JPEG, GIF (Maks. 2MB)
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="dashboard.php" class="btn btn-secondary">
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

    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/menu.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Profile picture preview
            const profileInput = document.getElementById('profile_picture');
            const profilePreview = document.getElementById('profile-preview');

            profileInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        profilePreview.innerHTML = `<img src="${e.target.result}" alt="Profile Picture">`;
                    }

                    reader.readAsDataURL(file);
                }
            });
        });
    </script>
</body>

</html>