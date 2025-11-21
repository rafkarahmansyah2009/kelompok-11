<?php
session_start();
require_once '../../config/database.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Ambil data user (untuk foto profil)
$user_id = $_SESSION['user_id'];
$query_user = "SELECT nama, role, profile_picture FROM users WHERE id_user = ?";
$stmt_user = $conn->prepare($query_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user = $result_user->fetch_assoc();

// Query untuk mendapatkan data siswa dan wali kelas
$query = "SELECT s.*, g.nama_guru 
          FROM siswa s 
          LEFT JOIN guru g ON s.id_guru = g.id_guru 
          ORDER BY s.nama_siswa";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Siswa - Sistem Manajemen Sekolah</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/animation.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* === Animasi + Styling Modern SweetAlert === */
        .animated-popup {
            border-radius: 16px !important;
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 0 25px rgba(130, 80, 255, 0.3);
            backdrop-filter: blur(10px);
            animation: scaleIn 0.25s ease forwards;
        }

        .swal2-show-custom {
            animation: fadeInUp 0.3s ease both;
        }

        .swal2-hide-custom {
            animation: fadeOutDown 0.25s ease both;
        }

        .swal2-confirm-btn {
            background: linear-gradient(90deg, #7b2ff7, #f107a3) !important;
            border: none !important;
            box-shadow: 0 0 10px rgba(130, 80, 255, 0.5);
            border-radius: 10px !important;
            transition: all 0.3s ease;
        }

        .swal2-confirm-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(241, 7, 163, 0.6);
        }

        .swal2-cancel-btn {
            background: rgba(255, 255, 255, 0.08) !important;
            color: #fff !important;
            border: 1px solid rgba(255, 255, 255, 0.25) !important;
            border-radius: 10px !important;
            transition: all 0.3s ease;
        }

        .swal2-cancel-btn:hover {
            background: rgba(255, 255, 255, 0.2) !important;
            transform: scale(1.05);
        }

        @keyframes scaleIn {
            from {
                transform: scale(0.85);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        @keyframes fadeInUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes fadeOutDown {
            from {
                transform: translateY(0);
                opacity: 1;
            }

            to {
                transform: translateY(20px);
                opacity: 0;
            }
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
                <li><a href="../dashboard.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
                <li><a href="list.php" class="active"><i class="fas fa-graduation-cap"></i><span>Data Siswa</span></a></li>
                <li><a href="../guru/list.php"><i class="fas fa-chalkboard-teacher"></i><span>Data Guru</span></a></li>
                <li><a href="../profile.php"><i class="fas fa-user"></i><span>Profil</span></a></li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="content-header">
                <h1 class="page-title">Data Siswa</h1>
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

            <!-- Table -->
            <div class="data-table-container fade-in">
                <div class="table-header">
                    <h2 class="table-title">Daftar Siswa</h2>
                    <div class="table-actions">
                        <div class="search-box">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" id="search-siswa" class="search-input" placeholder="Cari siswa...">
                        </div>
                        <a href="add.php" class="btn btn-primary glow-effect">
                            <i class="fas fa-plus"></i> Tambah Siswa
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="data-table" id="siswa-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Jurusan</th>
                                <th>Wali Kelas</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>{$no}</td>";
                                    echo "<td>" . htmlspecialchars($row['nama_siswa']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['kelas']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['jurusan']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['nama_guru'] ?? '-') . "</td>";
                                    echo "<td>
                                            <div class='action-buttons'>
                                                <a href='edit.php?id=" . $row['id_siswa'] . "' class='btn btn-info btn-sm'>
                                                    <i class='fas fa-edit'></i> Edit
                                                </a>
                                                <button class='btn btn-danger btn-sm delete-btn' data-id='" . $row['id_siswa'] . "'>
                                                    <i class='fas fa-trash'></i> Hapus
                                                </button>
                                            </div>
                                          </td>";
                                    echo "</tr>";
                                    $no++;
                                }
                            } else {
                                echo "<tr><td colspan='6' style='text-align:center;color:rgba(255,255,255,0.6);'>Tidak ada data siswa</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
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
        document.querySelectorAll(".delete-btn").forEach(btn => {
            btn.addEventListener("click", () => {
                const id = btn.getAttribute("data-id");

                Swal.fire({
                    title: "Apakah Anda yakin?",
                    text: "Data siswa ini akan dihapus secara permanen!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Ya, Hapus",
                    cancelButtonText: "Batal",
                    reverseButtons: true,
                    background: "rgba(30, 30, 60, 0.95)",
                    color: "#fff",
                    customClass: {
                        popup: 'animated-popup',
                        confirmButton: 'swal2-confirm-btn',
                        cancelButton: 'swal2-cancel-btn'
                    },
                    showClass: {
                        popup: 'swal2-show-custom'
                    },
                    hideClass: {
                        popup: 'swal2-hide-custom'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = `delete.php?id=${id}`;
                    }
                });
            });
        });
    </script>
</body>

</html>