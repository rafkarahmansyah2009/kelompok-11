<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$queryUser = "SELECT * FROM users WHERE id_user = ?";
$stmtUser = $conn->prepare($queryUser);
$stmtUser->bind_param("i", $_SESSION['user_id']);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$user = $resultUser->fetch_assoc();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: list.php');
    exit();
}

$id_guru = $_GET['id'];
$query = "SELECT * FROM guru WHERE id_guru = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_guru);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: list.php');
    exit();
}

$guru = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_guru = $_POST['nama_guru'];
    $mata_pelajaran = $_POST['mata_pelajaran'];

    $updateQuery = "UPDATE guru SET nama_guru = ?, mata_pelajaran = ? WHERE id_guru = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ssi", $nama_guru, $mata_pelajaran, $id_guru);

    if ($updateStmt->execute()) {
        $success = "✅ Data guru berhasil diperbarui!";
        header("refresh:2;url=list.php");
    } else {
        $error = "❌ Data guru gagal diperbarui!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Guru - Sistem Manajemen Sekolah</title>
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

        <div class="main-content">
            <div class="content-header">
                <h1 class="page-title">Edit Guru</h1>
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

            <div class="form-container fade-in">
                <h2 class="form-title">Form Edit Guru</h2>

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
                            <input type="text" id="nama_guru" name="nama_guru" class="form-control"
                                value="<?php echo htmlspecialchars($guru['nama_guru']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="mata_pelajaran" class="form-label">Mata Pelajaran</label>
                            <select id="mata_pelajaran" name="mata_pelajaran" class="form-control" required>
                                <?php
                                $mapel = [
                                    "Matematika" => "🧮 Matematika",
                                    "RPL" => "💻 Rekayasa Perangkat Lunak (RPL)",
                                    "Informatika" => "🧠 Informatika",
                                    "PJOK" => "⚽ Pendidikan Jasmani, Olahraga, dan Kesehatan (PJOK)",
                                    "Bahasa Indonesia" => "📚 Bahasa Indonesia",
                                    "Bahasa Inggris" => "🗣️ Bahasa Inggris",
                                    "Sejarah Indonesia" => "🏰 Sejarah Indonesia",
                                    "PPKN" => "🦅 Pendidikan Pancasila dan Kewarganegaraan (PPKN)",
                                    "PKK" => "💼 Produk Kreatif dan Kewirausahaan (PKK)",
                                    "Mapel Pilihan" => "🎯 Mata Pelajaran Pilihan",
                                    "Agama Islam" => "🕌 Pendidikan Agama Islam"
                                ];

                                foreach ($mapel as $value => $label) {
                                    $selected = ($guru['mata_pelajaran'] == $value) ? 'selected' : '';
                                    echo "<option value='$value' $selected>$label</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="list.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                        <button type="submit" class="btn btn-primary glow-effect"><i class="fas fa-save"></i> Simpan Perubahan</button>
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