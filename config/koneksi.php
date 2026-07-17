<?php

// ============================================================
// Konfigurasi Database
// Di cPanel: isi nilai DB_* di Environment Variables (cPanel > Setup PHP App)
// Di Lokal (Laragon): fallback otomatis ke nilai default di bawah
// ============================================================

$db_host = getenv('DB_HOST')     ?: 'localhost';
$db_user = getenv('DB_USER')     ?: 'root';
$db_pass = getenv('DB_PASS')     ?: '';
$db_name = getenv('DB_NAME')     ?: 'topup_game';

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    // Tampilkan pesan error yang lebih informatif (tanpa expose detail ke publik)
    error_log("Koneksi database gagal: " . mysqli_connect_error());
    die(json_encode(['error' => 'Database connection failed. Please contact administrator.']));
}

mysqli_set_charset($conn, "utf8mb4");
?>