 
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

// Query untuk menghapus data siswa
$query = "DELETE FROM siswa WHERE id_siswa = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_siswa);

if ($stmt->execute()) {
    $_SESSION['success'] = "Data siswa berhasil dihapus!";
} else {
    $_SESSION['error'] = "Data siswa gagal dihapus!";
}

// Redirect ke halaman list
header('Location: list.php');
exit();
?>