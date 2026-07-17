<?php
// ============================================================
// CPANEL SETUP & DIAGNOSTIC TOOL — TopUpin
// FILE INI HARUS DIHAPUS SETELAH DIGUNAKAN!
// Akses via: https://topupinweb.my.id/cpanel_setup.php
// ============================================================

// === KONFIGURASI MANUAL — GANTI SESUAI cPanel ANDA ===
$db_host = 'localhost';
$db_user = 'ekovmljg_dbuser';   // Ganti: username_namadb di cPanel
$db_pass = 'GANTI_PASSWORD_DB'; // Ganti: password database cPanel Anda
$db_name = 'ekovmljg_topup_game'; // Ganti: nama database cPanel Anda
// ======================================================

$step = isset($_GET['step']) ? $_GET['step'] : 'check';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TopUpin — cPanel Setup Tool</title>
    <style>
        body { font-family: monospace; background: #0b0f19; color: #e2e8f0; padding: 20px; }
        h1 { color: #818cf8; }
        h2 { color: #6ee7b7; border-bottom: 1px solid #374151; padding-bottom: 6px; }
        .ok { color: #4ade80; } .err { color: #f87171; } .warn { color: #fbbf24; }
        pre { background: #1f2937; padding: 15px; border-radius: 8px; overflow-x: auto; }
        .btn { display: inline-block; background: #4f46e5; color: white; padding: 10px 20px; 
               border-radius: 8px; text-decoration: none; margin: 5px; font-size: 14px; }
        .btn-red { background: #dc2626; }
        .btn-green { background: #16a34a; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        td, th { padding: 8px 12px; border: 1px solid #374151; text-align: left; font-size: 13px; }
        th { background: #1f2937; color: #818cf8; }
    </style>
</head>
<body>

<h1>🛠️ TopUpin — cPanel Setup & Diagnostic Tool</h1>
<p class="warn">⚠️ HAPUS FILE INI SEGERA SETELAH SETUP SELESAI!</p>

<nav style="margin: 20px 0;">
    <a href="?step=check" class="btn">1. Cek Koneksi DB</a>
    <a href="?step=migrate" class="btn btn-green">2. Jalankan Migrasi</a>
    <a href="?step=phpinfo" class="btn">3. Info PHP</a>
</nav>

<hr style="border-color: #374151; margin: 20px 0;">

<?php

// ============================================================
// STEP: CEK KONEKSI
// ============================================================
if ($step === 'check') {
    echo "<h2>🔍 Langkah 1: Cek Koneksi Database</h2>";

    // Test koneksi
    $conn_test = @mysqli_connect($db_host, $db_user, $db_pass, $db_name);
    
    echo "<table>";
    echo "<tr><th>Parameter</th><th>Nilai</th><th>Status</th></tr>";
    echo "<tr><td>Host</td><td>$db_host</td><td>—</td></tr>";
    echo "<tr><td>User</td><td>$db_user</td><td>—</td></tr>";
    echo "<tr><td>Database</td><td>$db_name</td><td>—</td></tr>";
    
    if ($conn_test) {
        echo "<tr><td>Koneksi</td><td>Berhasil</td><td class='ok'>✅ OK</td></tr>";
        
        // Cek tabel yang sudah ada
        $tables_result = mysqli_query($conn_test, "SHOW TABLES");
        $existing_tables = [];
        while ($row = mysqli_fetch_row($tables_result)) {
            $existing_tables[] = $row[0];
        }
        
        $required_tables = ['admin', 'user', 'produk', 'transaksi', 'penarikan_dana', 'cs_chat_sessions', 'cs_chat_messages'];
        
        echo "</table>";
        echo "<h2>📋 Status Tabel Database</h2>";
        echo "<table><tr><th>Nama Tabel</th><th>Status</th><th>Keterangan</th></tr>";
        
        foreach ($required_tables as $tbl) {
            if (in_array($tbl, $existing_tables)) {
                $count = mysqli_fetch_row(mysqli_query($conn_test, "SELECT COUNT(*) FROM `$tbl`"))[0];
                echo "<tr><td>$tbl</td><td class='ok'>✅ Ada</td><td>$count baris data</td></tr>";
            } else {
                echo "<tr><td>$tbl</td><td class='err'>❌ Belum Ada</td><td>Perlu migrasi</td></tr>";
            }
        }
        echo "</table>";
        
        // Cek apakah perlu migrasi
        if (!in_array('produk', $existing_tables)) {
            echo "<div style='background:#7f1d1d;padding:15px;border-radius:8px;margin:15px 0;'>";
            echo "<b class='err'>⚠️ Tabel belum ada!</b><br>Klik tombol 'Jalankan Migrasi' di atas untuk membuat semua tabel dan mengisi data sampel.";
            echo "</div>";
        } else {
            echo "<div style='background:#14532d;padding:15px;border-radius:8px;margin:15px 0;'>";
            echo "<b class='ok'>✅ Semua tabel sudah ada!</b> Website siap digunakan.";
            echo "</div>";
        }
        
        mysqli_close($conn_test);
    } else {
        echo "<tr><td>Koneksi</td><td>" . mysqli_connect_error() . "</td><td class='err'>❌ GAGAL</td></tr>";
        echo "</table>";
        echo "<div style='background:#7f1d1d;padding:15px;border-radius:8px;margin:15px 0;'>";
        echo "<b class='err'>❌ Koneksi Database Gagal!</b><br>";
        echo "Error: " . mysqli_connect_error() . "<br><br>";
        echo "<b>Solusi:</b><ul>";
        echo "<li>Buka file ini dan ubah nilai <code>\$db_user</code>, <code>\$db_pass</code>, <code>\$db_name</code> sesuai database cPanel Anda</li>";
        echo "<li>Pastikan database sudah dibuat di cPanel → MySQL Databases</li>";
        echo "<li>Pastikan user sudah di-assign ke database dengan <b>ALL PRIVILEGES</b></li>";
        echo "</ul>";
        echo "</div>";
    }
}

// ============================================================
// STEP: MIGRASI DATABASE
// ============================================================
elseif ($step === 'migrate') {
    echo "<h2>⚙️ Langkah 2: Migrasi Database</h2>";
    
    $conn = @mysqli_connect($db_host, $db_user, $db_pass, $db_name);
    if (!$conn) {
        echo "<div style='background:#7f1d1d;padding:15px;border-radius:8px;'>";
        echo "<b class='err'>❌ Tidak bisa konek ke database!</b> Kembali ke Langkah 1 dan perbaiki konfigurasi dulu.";
        echo "</div>";
        echo "</body></html>";
        exit;
    }
    
    mysqli_set_charset($conn, 'utf8mb4');
    
    $queries = [
        // ── Tabel admin ──
        "Tabel admin" => "CREATE TABLE IF NOT EXISTS `admin` (
            `id_admin` int(11) NOT NULL AUTO_INCREMENT,
            `username` varchar(50) NOT NULL UNIQUE,
            `password` varchar(255) NOT NULL,
            `nama_lengkap` varchar(100) NOT NULL,
            PRIMARY KEY (`id_admin`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        // ── Tabel user ──
        "Tabel user" => "CREATE TABLE IF NOT EXISTS `user` (
            `id_user` int(11) NOT NULL AUTO_INCREMENT,
            `nama` varchar(100) NOT NULL,
            `email` varchar(100) NOT NULL UNIQUE,
            `password` varchar(255) NOT NULL,
            `role` enum('admin','user') DEFAULT 'user',
            `saldo` int(11) NOT NULL DEFAULT 0,
            PRIMARY KEY (`id_user`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        // ── Tabel produk ──
        "Tabel produk" => "CREATE TABLE IF NOT EXISTS `produk` (
            `id_produk` int(11) NOT NULL AUTO_INCREMENT,
            `id_user` int(11) DEFAULT NULL,
            `kategori` enum('topup','akun','item') NOT NULL DEFAULT 'topup',
            `nama_game` varchar(100) NOT NULL,
            `nama_produk` varchar(255) NOT NULL,
            `deskripsi` text DEFAULT NULL,
            `harga` int(11) NOT NULL,
            `nominal` int(11) DEFAULT NULL,
            `stok` int(11) NOT NULL DEFAULT -1,
            `gambar` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id_produk`),
            FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        // ── Tabel transaksi ──
        "Tabel transaksi" => "CREATE TABLE IF NOT EXISTS `transaksi` (
            `id_trx` int(11) NOT NULL AUTO_INCREMENT,
            `id_user` int(11) DEFAULT NULL,
            `id_produk` int(11) NOT NULL,
            `user_game` varchar(100) DEFAULT NULL,
            `server_game` varchar(100) DEFAULT NULL,
            `kontak_pembeli` varchar(100) NOT NULL,
            `catatan` text DEFAULT NULL,
            `total` int(11) NOT NULL,
            `komisi` int(11) NOT NULL DEFAULT 0,
            `metode_pembayaran` varchar(50) NOT NULL,
            `bukti_bayar` varchar(255) DEFAULT NULL,
            `status` varchar(20) NOT NULL DEFAULT 'Pending',
            `tanggal` datetime NOT NULL,
            PRIMARY KEY (`id_trx`),
            FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        // ── Tabel penarikan_dana ──
        "Tabel penarikan_dana" => "CREATE TABLE IF NOT EXISTS `penarikan_dana` (
            `id_penarikan` int(11) NOT NULL AUTO_INCREMENT,
            `id_user` int(11) NOT NULL,
            `jumlah` int(11) NOT NULL,
            `bank_tujuan` varchar(100) NOT NULL,
            `no_rekening` varchar(50) NOT NULL,
            `atas_nama` varchar(100) NOT NULL,
            `status` enum('Pending','Success','Failed') DEFAULT 'Pending',
            `tanggal` datetime NOT NULL,
            PRIMARY KEY (`id_penarikan`),
            FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        // ── Tabel cs_chat_sessions ──
        "Tabel cs_chat_sessions" => "CREATE TABLE IF NOT EXISTS `cs_chat_sessions` (
            `id_session` INT AUTO_INCREMENT PRIMARY KEY,
            `id_user` INT NOT NULL,
            `status` ENUM('active','closed') DEFAULT 'active',
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        // ── Tabel cs_chat_messages ──
        "Tabel cs_chat_messages" => "CREATE TABLE IF NOT EXISTS `cs_chat_messages` (
            `id_message` INT AUTO_INCREMENT PRIMARY KEY,
            `id_session` INT NOT NULL,
            `sender_role` ENUM('user','admin') NOT NULL,
            `message` TEXT NOT NULL,
            `is_read` TINYINT(1) DEFAULT 0,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`id_session`) REFERENCES `cs_chat_sessions` (`id_session`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    ];

    echo "<pre>";
    foreach ($queries as $label => $sql) {
        if (mysqli_query($conn, $sql)) {
            echo "<span class='ok'>[✅ OK]</span> $label berhasil dibuat/diverifikasi.\n";
        } else {
            echo "<span class='err'>[❌ GAGAL]</span> $label: " . mysqli_error($conn) . "\n";
        }
    }

    // Seeding produk sampel
    echo "\n--- Seeding Data Produk Sampel ---\n";
    $seed_products = [
        ['topup', 'Mobile Legends', '86 Diamonds', 'Paket instan 86 Diamond MLBB', 20000, 86, -1],
        ['topup', 'Mobile Legends', '172 Diamonds', 'Paket instan 172 Diamond MLBB', 40000, 172, -1],
        ['topup', 'Free Fire', '140 Diamonds', 'Top Up 140 Diamond Free Fire', 19000, 140, -1],
        ['topup', 'PUBG Mobile', '60 UC', 'Top Up 60 UC PUBG Mobile', 15000, 60, -1],
    ];
    
    foreach ($seed_products as $p) {
        $check = mysqli_query($conn, "SELECT id_produk FROM produk WHERE nama_produk = '" . mysqli_real_escape_string($conn, $p[2]) . "' AND nama_game = '" . mysqli_real_escape_string($conn, $p[1]) . "' LIMIT 1");
        if (mysqli_num_rows($check) > 0) {
            echo "<span class='warn'>[i SKIP]</span> Produk '{$p[2]}' sudah ada, dilewati.\n";
            continue;
        }
        $q = "INSERT INTO produk (id_user, kategori, nama_game, nama_produk, deskripsi, harga, nominal, stok) VALUES (NULL, '{$p[0]}', '" . mysqli_real_escape_string($conn, $p[1]) . "', '" . mysqli_real_escape_string($conn, $p[2]) . "', '" . mysqli_real_escape_string($conn, $p[3]) . "', {$p[4]}, {$p[5]}, {$p[6]})";
        if (mysqli_query($conn, $q)) {
            echo "<span class='ok'>[✅ OK]</span> Produk sampel ditambahkan: {$p[2]}\n";
        } else {
            echo "<span class='err'>[❌]</span> Gagal seed '{$p[2]}': " . mysqli_error($conn) . "\n";
        }
    }

    // Buat admin default
    echo "\n--- Setup Akun Admin ---\n";
    $check_admin = mysqli_query($conn, "SELECT * FROM admin WHERE username = 'admin'");
    if (mysqli_num_rows($check_admin) == 0) {
        mysqli_query($conn, "INSERT INTO admin (username, password, nama_lengkap) VALUES ('admin', 'admin123', 'Administrator Utama')");
        echo "<span class='ok'>[✅ OK]</span> Akun admin default dibuat: admin / admin123\n";
    } else {
        echo "<span class='warn'>[i]</span> Akun admin sudah ada.\n";
    }

    echo "\n<span class='ok'>✅ MIGRASI SELESAI!</span>\n";
    echo "</pre>";

    echo "<div style='background:#14532d;padding:15px;border-radius:8px;margin:15px 0;'>";
    echo "<b class='ok'>✅ Migrasi berhasil!</b> Lakukan langkah berikut:<br><br>";
    echo "<b>1.</b> Buka cPanel → Setup PHP App → tambahkan Environment Variables:<br>";
    echo "<code>DB_HOST=localhost | DB_USER={$db_user} | DB_PASS=password_anda | DB_NAME={$db_name}</code><br><br>";
    echo "<b>2.</b> <span class='err'>HAPUS FILE INI SEGERA</span> dari server untuk keamanan!<br>";
    echo "<b>3.</b> Akses website: <a href='/' style='color:#818cf8;'>Klik di sini</a>";
    echo "</div>";
    
    mysqli_close($conn);
}

// ============================================================
// STEP: PHP INFO
// ============================================================
elseif ($step === 'phpinfo') {
    echo "<h2>ℹ️ Informasi PHP Server</h2>";
    echo "<table>";
    $checks = [
        'PHP Version'   => PHP_VERSION,
        'mysqli'        => extension_loaded('mysqli') ? '✅ Aktif' : '❌ Tidak ada',
        'openssl'       => extension_loaded('openssl') ? '✅ Aktif' : '❌ Tidak ada',
        'curl'          => extension_loaded('curl') ? '✅ Aktif' : '❌ Tidak ada',
        'mbstring'      => extension_loaded('mbstring') ? '✅ Aktif' : '❌ Tidak ada',
        'Server Software' => $_SERVER['SERVER_SOFTWARE'] ?? '-',
        'Document Root' => $_SERVER['DOCUMENT_ROOT'] ?? '-',
        'DB_HOST env'   => getenv('DB_HOST') ?: '(belum diset)',
        'DB_NAME env'   => getenv('DB_NAME') ?: '(belum diset)',
        'DB_USER env'   => getenv('DB_USER') ?: '(belum diset)',
    ];
    echo "<tr><th>Item</th><th>Nilai</th></tr>";
    foreach ($checks as $k => $v) {
        echo "<tr><td>$k</td><td>$v</td></tr>";
    }
    echo "</table>";
}

?>

<hr style="border-color: #374151; margin: 20px 0;">
<p class="err" style="font-size:12px;">⚠️ PERINGATAN KEAMANAN: Hapus file <code>cpanel_setup.php</code> dari server setelah setup selesai!</p>

</body>
</html>
