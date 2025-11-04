 
<?php
session_start();
require_once '../../config/database.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Ambil ID guru dari URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: list.php');
    exit();
}

$id_guru = $_GET['id'];

// Query untuk menghapus data guru
$query = "DELETE FROM guru WHERE id_guru = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_guru);

if ($stmt->execute()) {
    $_SESSION['success'] = "Data guru berhasil dihapus!";
} else {
    $_SESSION['error'] = "Data guru gagal dihapus!";
}

// Redirect ke halaman list
header('Location: list.php');
exit();
?>