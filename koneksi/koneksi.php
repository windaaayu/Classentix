<?php
// Cek apakah session sudah berjalan, jika belum baru di-start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Konfigurasi Database
$host = "localhost";
$user = "root";
$pass = "";
$db   = "classentix_db";

// Membuat koneksi ke database tanpa strict mode
$koneksi = mysqli_connect($host, $user, $pass, $db);

// Cek apakah koneksi berhasil
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>