<?php

// ============================================================
// Konfigurasi Database Otomatis (Auto-Detect Environment)
// ============================================================

$is_local = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1', '::1']) || strpos($_SERVER['HTTP_HOST'] ?? '', '.test') !== false || strpos($_SERVER['HTTP_HOST'] ?? '', '.local') !== false;

if ($is_local) {
    // 🖥️ Konfigurasi LOKAL (Laragon/XAMPP)
    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = '';
    $db_name = 'topup_game';
} else {
    // 🌍 Konfigurasi SERVER (cPanel)
    // Silakan edit bagian ini HANYA SEKALI sesuai database cPanel Anda
    $db_host = 'localhost'; // Biasanya tetap localhost di cPanel
    $db_user = 'ekovmljg_topup'; // Ganti dengan DB user cPanel Anda
    $db_pass = 'YOUR_CPANEL_DB_PASSWORD'; // Ganti dengan password DB cPanel
    $db_name = 'ekovmljg_topup'; // Ganti dengan nama DB cPanel Anda
}

// Override dengan Environment Variables jika diset (opsional)
$db_host = getenv('DB_HOST') ?: $db_host;
$db_user = getenv('DB_USER') ?: $db_user;
$db_pass = getenv('DB_PASS') ?: $db_pass;
$db_name = getenv('DB_NAME') ?: $db_name;

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    // Tampilkan pesan error yang lebih informatif (tanpa expose detail ke publik)
    error_log("Koneksi database gagal: " . mysqli_connect_error());
    die(json_encode(['error' => 'Database connection failed. Please contact administrator.']));
}

mysqli_set_charset($conn, "utf8mb4");
?>