<?php
// c:\laragon\www\TopUpin\config\seed_demo.php
// Script seeding data demo untuk presentasi akademik
// Jalankan sekali via browser: /config/seed_demo.php

include "koneksi.php";
/** @var mysqli $conn */

header('Content-Type: text/html; charset=utf-8');
echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Seed Demo</title>";
echo "<style>body{font-family:monospace;background:#0b0f19;color:#e2e8f0;padding:20px;}
.ok{color:#4ade80;}.err{color:#f87171;}.warn{color:#fbbf24;}.info{color:#818cf8;}
pre{background:#1f2937;padding:15px;border-radius:8px;}</style></head><body>";
echo "<h2 style='color:#818cf8;'>🌱 TopUpin — Seeding Data Demo Akademik</h2><pre>";

$errors = 0;

// ── 1. UPDATE TABEL ADMIN (pastikan kolom nama_lengkap ada) ──────────────────
echo "<span class='info'>--- Mempersiapkan Tabel Admin ---</span>\n";
mysqli_query($conn, "ALTER TABLE admin ADD COLUMN IF NOT EXISTS nama_lengkap VARCHAR(100) DEFAULT 'Administrator' AFTER password");
$adminCheck = mysqli_query($conn, "SELECT * FROM admin WHERE username='admin'");
if (mysqli_num_rows($adminCheck) == 0) {
    mysqli_query($conn, "INSERT INTO admin (username, password, nama_lengkap) VALUES ('admin', 'admin123', 'Administrator Utama')");
    echo "<span class='ok'>[✅] Akun admin dibuat.</span>\n";
} else {
    echo "<span class='warn'>[i] Akun admin sudah ada.</span>\n";
}

// ── 2. AKUN USER DEMO ────────────────────────────────────────────────────────
echo "\n<span class='info'>--- Membuat Akun User Demo ---</span>\n";
$checkUser = mysqli_query($conn, "SELECT * FROM user WHERE email='demo@topupin.id'");
if (mysqli_num_rows($checkUser) == 0) {
    $hashed = password_hash('demo123', PASSWORD_DEFAULT);
    $r = mysqli_query($conn, "INSERT INTO user (nama, email, password, role, saldo) VALUES ('Demo User', 'demo@topupin.id', '$hashed', 'user', 0)");
    if ($r) echo "<span class='ok'>[✅] Akun user demo dibuat: demo@topupin.id / demo123</span>\n";
    else { echo "<span class='err'>[❌] Gagal: ".mysqli_error($conn)."</span>\n"; $errors++; }
} else {
    echo "<span class='warn'>[i] Akun user demo sudah ada.</span>\n";
}
$demoUser = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM user WHERE email='demo@topupin.id'"));
$demoUserId = $demoUser ? intval($demoUser['id_user']) : 0;

// ── 3. AKUN SELLER DEMO ──────────────────────────────────────────────────────
echo "\n<span class='info'>--- Membuat Akun Seller Demo ---</span>\n";
$checkSeller = mysqli_query($conn, "SELECT * FROM user WHERE email='seller@topupin.id'");
if (mysqli_num_rows($checkSeller) == 0) {
    $hashed = password_hash('seller123', PASSWORD_DEFAULT);
    $r = mysqli_query($conn, "INSERT INTO user (nama, email, password, role, saldo) VALUES ('GameStore Official', 'seller@topupin.id', '$hashed', 'user', 75000)");
    if ($r) echo "<span class='ok'>[✅] Akun seller demo dibuat: seller@topupin.id / seller123</span>\n";
    else { echo "<span class='err'>[❌] Gagal: ".mysqli_error($conn)."</span>\n"; $errors++; }
} else {
    echo "<span class='warn'>[i] Akun seller demo sudah ada.</span>\n";
}
$demoSeller = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM user WHERE email='seller@topupin.id'"));
$demoSellerId = $demoSeller ? intval($demoSeller['id_user']) : 0;

// ── 4. PRODUK MILIK SELLER DEMO ─────────────────────────────────────────────
echo "\n<span class='info'>--- Membuat Produk Seller Demo ---</span>\n";
if ($demoSellerId > 0) {
    $sellerProducts = [
        [$demoSellerId, 'akun', 'Mobile Legends', 'Akun MLBB Mythical Glory | 80 Skin | Siap Pakai', "Spesifikasi:\n- Rank: Mythical Glory\n- 80 Skin Epic & Special\n- Semua emblem max level\n- Login via Moonton", 285000, null, 1],
        [$demoSellerId, 'item', 'Mobile Legends', 'Gift Skin Epic Shop MLBB (Semua Skin)', "Syarat gift skin:\n- Pertemanan minimal 7 hari\n- Level akun min 20\n- Cantumkan User ID + Nama Skin di catatan", 110000, null, 25],
        [$demoSellerId, 'akun', 'Genshin Impact', 'Akun GI AR 55 Asia | Raiden C2 + Zhongli', "Spesifikasi:\n- Adventure Rank 55 Asia\n- Raiden Shogun C2, Zhongli, Kazuha\n- Primordial Jade + Engulfing Lightning\n- Email unbind, aman", 310000, null, 1],
    ];
    foreach ($sellerProducts as $sp) {
        $chk = mysqli_query($conn, "SELECT id_produk FROM produk WHERE nama_produk='".mysqli_real_escape_string($conn,$sp[2])." ".$sp[3]."' LIMIT 1");
        $chk2 = mysqli_query($conn, "SELECT id_produk FROM produk WHERE id_user=$demoSellerId AND nama_produk='".mysqli_real_escape_string($conn,$sp[3])."' LIMIT 1");
        if (mysqli_num_rows($chk2) > 0) {
            echo "<span class='warn'>[i] Produk '{$sp[3]}' sudah ada, dilewati.</span>\n";
            continue;
        }
        $desc = mysqli_real_escape_string($conn, $sp[4]);
        $nama = mysqli_real_escape_string($conn, $sp[3]);
        $game = mysqli_real_escape_string($conn, $sp[2]);
        $stok = $sp[7];
        $harga = $sp[5];
        $nominal = is_null($sp[6]) ? "NULL" : $sp[6];
        $r = mysqli_query($conn, "INSERT INTO produk (id_user, kategori, nama_game, nama_produk, deskripsi, harga, nominal, stok) VALUES ({$sp[0]}, '{$sp[1]}', '$game', '$nama', '$desc', $harga, $nominal, $stok)");
        if ($r) echo "<span class='ok'>[✅] Produk seller demo: {$sp[3]}</span>\n";
        else echo "<span class='err'>[❌] Gagal: ".mysqli_error($conn)."</span>\n";
    }
}

// ── 5. TRANSAKSI DEMO ────────────────────────────────────────────────────────
echo "\n<span class='info'>--- Membuat Data Transaksi Demo ---</span>\n";
if ($demoUserId > 0) {
    // Ambil produk topup pertama
    $p1 = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM produk WHERE kategori='topup' LIMIT 1"));
    $p2 = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM produk WHERE kategori='topup' LIMIT 1 OFFSET 2"));

    $demoTrx = [
        // [id_user, id_produk, user_game, kontak, total, metode, status, tanggal]
        [$demoUserId, $p1 ? $p1['id_produk'] : 1, '123456789 (1001)', '081234567890', $p1 ? $p1['harga'] : 20000, 'QRIS', 'Success', date('Y-m-d H:i:s', strtotime('-2 days'))],
        [$demoUserId, $p2 ? $p2['id_produk'] : 2, '987654321 (2001)', '081234567890', $p2 ? $p2['harga'] : 40000, 'Transfer BCA', 'Pending', date('Y-m-d H:i:s', strtotime('-30 minutes'))],
    ];
    foreach ($demoTrx as $dt) {
        $chk = mysqli_query($conn, "SELECT id_trx FROM transaksi WHERE id_user={$dt[0]} AND status='{$dt[6]}' AND tanggal='{$dt[7]}' LIMIT 1");
        if (mysqli_num_rows($chk) > 0) {
            echo "<span class='warn'>[i] Transaksi status={$dt[6]} sudah ada.</span>\n";
            continue;
        }
        $r = mysqli_query($conn, "INSERT INTO transaksi (id_user, id_produk, user_game, kontak_pembeli, total, metode_pembayaran, status, tanggal) VALUES ({$dt[0]}, {$dt[1]}, '".mysqli_real_escape_string($conn,$dt[2])."', '{$dt[3]}', {$dt[4]}, '{$dt[5]}', '{$dt[6]}', '{$dt[7]}')");
        if ($r) echo "<span class='ok'>[✅] Transaksi demo dibuat: status={$dt[6]}</span>\n";
        else echo "<span class='err'>[❌] Gagal transaksi: ".mysqli_error($conn)."</span>\n";
    }
}

// ── 6. SESI CHAT DEMO ───────────────────────────────────────────────────────
echo "\n<span class='info'>--- Membuat Sesi Chat CS Demo ---</span>\n";
if ($demoUserId > 0) {
    $chkSess = mysqli_query($conn, "SELECT id_session FROM cs_chat_sessions WHERE id_user=$demoUserId LIMIT 1");
    if (mysqli_num_rows($chkSess) == 0) {
        mysqli_query($conn, "INSERT INTO cs_chat_sessions (id_user, status) VALUES ($demoUserId, 'active')");
        $sessId = intval(mysqli_insert_id($conn));
        $chatMessages = [
            ['user', 'Halo kak, saya tadi sudah bayar QRIS untuk order 86 Diamond MLBB, tapi status masih Pending. Mohon bantuannya 🙏'],
            ['admin', 'Halo kak! Terima kasih sudah menghubungi CS TopUpIn 😊 Kami sudah cek pembayarannya dan sedang dalam proses verifikasi ya kak. Mohon ditunggu maksimal 5 menit.'],
            ['user', 'Oke kak, terima kasih ya! Ditunggu kabarnya 😊'],
            ['admin', 'Pembayaran sudah terverifikasi kak! Diamond MLBB-nya sudah kami proses dan akan masuk ke akun game Anda dalam beberapa menit. Terima kasih sudah berbelanja di TopUpIn! ⭐'],
        ];
        foreach ($chatMessages as $cm) {
            mysqli_query($conn, "INSERT INTO cs_chat_messages (id_session, sender_role, message, is_read) VALUES ($sessId, '{$cm[0]}', '".mysqli_real_escape_string($conn,$cm[1])."', 1)");
        }
        echo "<span class='ok'>[✅] Sesi chat demo dibuat dengan 4 pesan percakapan.</span>\n";
    } else {
        echo "<span class='warn'>[i] Sesi chat demo sudah ada.</span>\n";
    }
}

echo "\n";
if ($errors === 0) {
    echo "<span class='ok'>✅ SEEDING DEMO SELESAI TANPA ERROR!\n\n";
    echo "Akun Demo Siap Digunakan:\n";
    echo "👤 User    : demo@topupin.id / demo123\n";
    echo "🏪 Seller  : seller@topupin.id / seller123\n";
    echo "🛡️ Admin   : admin / admin123\n</span>";
} else {
    echo "<span class='err'>⚠️ Seeding selesai dengan $errors error. Cek log di atas.</span>\n";
}

echo "\n<a href='/demo.php' style='color:#818cf8;'>→ Buka Halaman Demo</a>";
echo "</pre></body></html>";
