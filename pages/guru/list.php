<?php
session_start();
require_once '../../config/database.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Ambil data user untuk profil
$queryUser = "SELECT * FROM users WHERE id_user = ?";
$stmt = $conn->prepare($queryUser);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$resultUser = $stmt->get_result();
$user = $resultUser->fetch_assoc();

// Query data guru
$query = "SELECT * FROM guru ORDER BY nama_guru";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Guru - Sistem Manajemen Sekolah</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/animation.css">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <style>
        /* === Animasi + Styling Modern Sesuai Tema Dashboard === */
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
                <h1 class="page-title">Data Guru</h1>
                <div class="user-info">
                    <div class="user-details">
                        <div class="user-name"><?php echo $_SESSION['nama']; ?></div>
                        <div class="user-role"><?php echo ucfirst($_SESSION['role']); ?></div>
                    </div>
                    <img src="<?php echo !empty($user['profile_picture'])
                                    ? '../../assets/images/profiles/' . htmlspecialchars($user['profile_picture'])
                                    : '../../assets/images/default-avatar.png'; ?>" alt="User Avatar" class="user-avatar">
                </div>
            </div>

            <!-- Table -->
            <div class="data-table-container fade-in">
                <div class="table-header">
                    <h2 class="table-title">Daftar Guru</h2>
                    <div class="table-actions">
                        <div class="search-box">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" id="search-guru" class="search-input" placeholder="Cari guru...">
                        </div>
                        <a href="add.php" class="btn btn-primary glow-effect">
                            <i class="fas fa-plus"></i> Tambah Guru
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="data-table" id="guru-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Guru</th>
                                <th>Mata Pelajaran</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . $no++ . "</td>";
                                    echo "<td>" . htmlspecialchars($row['nama_guru']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['mata_pelajaran']) . "</td>";
                                    echo "<td>
                <div class='action-buttons'>
                    <a href='edit.php?id=" . $row['id_guru'] . "' class='btn btn-info btn-sm'>
                        <i class='fas fa-edit'></i> Edit
                    </a>
                    <button class='btn btn-danger btn-sm delete-btn' data-id='" . $row['id_guru'] . "'>
                        <i class='fas fa-trash'></i> Hapus
                    </button>
                </div>
              </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4' style='text-align:center;color:rgba(255,255,255,0.6);'>Tidak ada data guru</td></tr>";
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

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.querySelectorAll(".delete-btn").forEach(btn => {
            btn.addEventListener("click", () => {
                const id = btn.getAttribute("data-id");

                Swal.fire({
                    title: "Apakah Anda yakin?",
                    text: "Data guru ini akan dihapus secara permanen!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Ya, Hapus",
                    cancelButtonText: "Batal",
                    reverseButtons: true,
                    background: "rgba(30, 30, 60, 0.95)",
                    color: "#fff",
                    backdrop: `
        rgba(0, 0, 0, 0.7)
        left top
        no-repeat
      `,
                    customClass: {
                        popup: 'animated-popup',
                        confirmButton: 'swal2-confirm-btn',
                        cancelButton: 'swal2-cancel-btn'
                    },
                    allowOutsideClick: () => !Swal.isLoading(),
                    allowEscapeKey: true,
                    allowEnterKey: true,
                    showClass: {
                        popup: 'swal2-show-custom'
                    },
                    hideClass: {
                        popup: 'swal2-hide-custom'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Arahkan ke delete.php jika dikonfirmasi
                        window.location.href = `delete.php?id=${id}`;
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        Swal.close(); // Tutup popup dengan aman
                    }
                });
            });
        });
    </script>
</body>

</html>