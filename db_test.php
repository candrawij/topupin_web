<?php
// Paksa PHP menampilkan error langsung ke layar browser
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>🔍 Diagnostik Koneksi Database</h2>";

// 1. Cek apakah ekstensi mysqli sudah aktif
if (!function_exists('mysqli_connect')) {
    die("<b style='color:red;'>❌ ERROR: Ekstensi PHP 'mysqli' belum diaktifkan di cPanel Anda!</b><br>
         <b>Solusi:</b> Masuk ke cPanel → Select PHP Version → Centang (aktifkan) ekstensi <b>mysqli</b>.");
}
echo "<span style='color:green;'>✅ OK: Ekstensi 'mysqli' aktif.</span><br><br>";

// 2. Cek variabel lingkungan
echo "<b>Environment Variables:</b><br>";
echo "DB_HOST: " . (getenv('DB_HOST') ?: '(kosong/default)') . "<br>";
echo "DB_USER: " . (getenv('DB_USER') ?: '(kosong/default)') . "<br>";
echo "DB_NAME: " . (getenv('DB_NAME') ?: '(kosong/default)') . "<br><br>";

// 3. Coba koneksi dengan parameter manual
// Ganti nilai di bawah ini jika ingin mencoba koneksi manual langsung
$test_host = 'localhost';
$test_user = 'ekovmljg_username_db'; // Ganti dengan user database cPanel Anda
$test_pass = 'password_db_anda';     // Ganti dengan password database cPanel Anda
$test_name = 'ekovmljg_topup_game';

echo "<b>Mencoba menghubungkan ke database...</b><br>";
$conn = @mysqli_connect($test_host, $test_user, $test_pass, $test_name);

if (!$conn) {
    echo "<b style='color:red;'>❌ GAGAL menghubungkan:</b> " . mysqli_connect_error() . "<br>";
} else {
    echo "<b style='color:green;'>✅ BERHASIL terhubung ke database!</b><br>";
    mysqli_close($conn);
}
?>
