<?php
// Konfigurasi koneksi database
$host = "localhost";
$user = "root"; // Ganti dengan username database Anda
$pass = ""; // Ganti dengan password database Anda
$dbname = "db-rafka";

// Membuat koneksi
$conn = new mysqli($host, $user, $pass, $dbname);

// Memeriksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Set charset ke utf8mb4
$conn->set_charset("utf8mb4");
