<?php
session_start();
require_once '../config/database.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Proses update profil
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_user = $_POST['id_user'];
    $nama = $_POST['nama'];
    $password = $_POST['password'];

    // Cek apakah user memiliki akses untuk mengubah profil ini
    if ($id_user != $_SESSION['user_id']) {
        $response = array(
            'success' => false,
            'message' => 'Anda tidak memiliki izin untuk mengubah profil ini!'
        );
        echo json_encode($response);
        exit();
    }

    // Handle profile picture upload
    $profile_picture = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $file_name = $_FILES['profile_picture']['name'];
        $file_tmp = $_FILES['profile_picture']['tmp_name'];
        $file_size = $_FILES['profile_picture']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Allowed file extensions
        $allowed_exts = array('jpg', 'jpeg', 'png', 'gif');

        if (in_array($file_ext, $allowed_exts) && $file_size < 2097152) { // 2MB max
            // Generate unique file name
            $new_file_name = 'profile_' . $id_user . '_' . time() . '.' . $file_ext;
            $upload_path = '../assets/images/' . $new_file_name;

            if (move_uploaded_file($file_tmp, $upload_path)) {
                $profile_picture = $new_file_name;

                // Update database with new profile picture
                $updatePicQuery = "UPDATE users SET profile_picture = ? WHERE id_user = ?";
                $updatePicStmt = $conn->prepare($updatePicQuery);
                $updatePicStmt->bind_param("si", $profile_picture, $id_user);
                $updatePicStmt->execute();
            }
        }
    }

    // Update user data
    if (!empty($password)) {
        // Update with new password
        $updateQuery = "UPDATE users SET nama = ?, password = ? WHERE id_user = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("ssi", $nama, $password, $id_user);
    } else {
        // Update without changing password
        $updateQuery = "UPDATE users SET nama = ? WHERE id_user = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("si", $nama, $id_user);
    }

    if ($updateStmt->execute()) {
        // Update session
        $_SESSION['nama'] = $nama;

        $response = array(
            'success' => true,
            'message' => 'Profil berhasil diperbarui!',
            'nama' => $nama,
            'profile_picture' => $profile_picture
        );
    } else {
        $response = array(
            'success' => false,
            'message' => 'Profil gagal diperbarui!'
        );
    }

    echo json_encode($response);
    exit();
}
