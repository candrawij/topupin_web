<?php
include "koneksi.php";

echo "=== MEMULAI MIGRASI DATABASE TOKO GAME (WALLET & COMMISSIONS) ===\n";

// 1. Drop tabel jika ada untuk memperbarui struktur (kecuali admin)
mysqli_query($conn, "DROP TABLE IF EXISTS `penarikan_dana`");
mysqli_query($conn, "DROP TABLE IF EXISTS `transaksi`");
mysqli_query($conn, "DROP TABLE IF EXISTS `produk`");
mysqli_query($conn, "DROP TABLE IF EXISTS `user`");

// 2. Buat tabel `user` (dengan kolom saldo)
$create_user = "
CREATE TABLE `user` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `saldo` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
if (mysqli_query($conn, $create_user)) {
    echo "[V] Tabel user berhasil dibuat.\n";
} else {
    echo "[X] Tabel user gagal dibuat: " . mysqli_error($conn) . "\n";
}

// 3. Buat tabel `produk`
$create_produk = "
CREATE TABLE `produk` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
if (mysqli_query($conn, $create_produk)) {
    echo "[V] Tabel produk berhasil dibuat.\n";
} else {
    echo "[X] Tabel produk gagal dibuat: " . mysqli_error($conn) . "\n";
}

// 4. Buat tabel `transaksi` (dengan kolom komisi)
$create_transaksi = "
CREATE TABLE `transaksi` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
if (mysqli_query($conn, $create_transaksi)) {
    echo "[V] Tabel transaksi berhasil dibuat.\n";
} else {
    echo "[X] Tabel transaksi gagal dibuat: " . mysqli_error($conn) . "\n";
}

// 5. Buat tabel `penarikan_dana`
$create_penarikan = "
CREATE TABLE `penarikan_dana` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
if (mysqli_query($conn, $create_penarikan)) {
    echo "[V] Tabel penarikan_dana berhasil dibuat.\n";
} else {
    echo "[X] Tabel penarikan_dana gagal dibuat: " . mysqli_error($conn) . "\n";
}

// 6. Seeding Data Produk
echo "\n--- Mengisi Data Sampel Produk (Seeding) ---\n";

$seed_products = [
    // TOP UP
    ['topup', 'Mobile Legends', '86 Diamonds', 'Paket instan 86 Diamond MLBB', 20000, 86, -1, 'ml_diamond.jpg'],
    ['topup', 'Mobile Legends', '172 Diamonds', 'Paket instan 172 Diamond MLBB', 40000, 172, -1, 'ml_diamond.jpg'],
    ['topup', 'Mobile Legends', '257 Diamonds', 'Paket hemat 257 Diamond MLBB', 60000, 257, -1, 'ml_diamond.jpg'],
    ['topup', 'Free Fire', '140 Diamonds', 'Top Up 140 Diamond Free Fire langsung masuk', 19000, 140, -1, 'ff_diamond.jpg'],
    ['topup', 'Free Fire', '355 Diamonds', 'Top Up 355 Diamond Free Fire hemat 10%', 47000, 355, -1, 'ff_diamond.jpg'],
    ['topup', 'PUBG Mobile', '60 UC', 'Top Up 60 Unknown Cash PUBG Mobile', 15000, 60, -1, 'pubg_uc.jpg'],
    ['topup', 'PUBG Mobile', '325 UC', 'Top Up 325 Unknown Cash PUBG Mobile', 75000, 325, -1, 'pubg_uc.jpg'],
    
    // AKUN GAME
    ['akun', 'Mobile Legends', 'Akun MLBB Mythical Glory | 120 Skins | Gusion KOF & Chou Dragon Boy', 'Spesifikasi Akun:\n- Rank: Mythical Glory (Bintang 52)\n- Hero: 90\n- Skin Total: 120\n- Skin Langka: Gusion KOF (K\'), Chou Dragon Boy, Lancelot Epic Limited, Selena Epic, 12 Skin Special.\n- Emblem: All Max Level 60\n- Platform: Android & iOS\n- Login: Moonton clean bind gmail (data dikirim langsung oleh admin setelah bayar).', 350000, NULL, 1, 'ml_account.jpg'],
    ['akun', 'Genshin Impact', 'Akun GI AR 55 Asia | Raiden Shogun C2 + Nahida + Zhongli + Signature Weapon', 'Spesifikasi Akun:\n- Adventure Rank: 55\n- Server: Asia\n- Karakter Bintang 5: Raiden Shogun C2, Nahida, Zhongli, Kazuha, Diluc C1, Jean, Mona.\n- Senjata Bintang 5: Engulfing Lightning (Signature Raiden), Primordial Jade Winged-Spear.\n- Pity: Event Character 45 (Rate On).\n- Eksplorasi: Sumeru & Fontaine masih fresh.\n- Login: Username mihoyo (Email unbind, aman sentosa).', 320000, NULL, 1, 'genshin_account.jpg'],
    
    // ITEM GAME
    ['item', 'Roblox', '1000 Robux via Group Funds (Proses Cepat)', 'Cara Pembelian:\n1. Cantumkan Username Roblox Anda yang benar di catatan pembelian.\n2. Wajib bergabung dengan Grup Roblox kami (Link grup akan otomatis terlihat di invoice setelah pembayaran sukses).\n3. Robux dikirim maksimal 1x24 jam setelah pembeli bergabung ke grup.', 95000, NULL, 150, 'roblox_robux.jpg'],
    ['item', 'Mobile Legends', 'Gift Skin Epic Shop Mobile Legends (Semua Skin Tersedia)', 'Persyaratan Gift Skin:\n- Harus berteman di game minimal selama 7 hari.\n- Akun pembeli minimal level 20.\n- Cantumkan User ID, Server ID, dan Nama Skin yang diinginkan di kolom Catatan.\n- Admin akan mengirimkan pertemanan setelah transaksi berstatus Success.', 115000, NULL, 50, 'gift_skin.jpg']
];

foreach ($seed_products as $p) {
    $kategori = $p[0];
    $nama_game = mysqli_real_escape_string($conn, $p[1]);
    $nama_produk = mysqli_real_escape_string($conn, $p[2]);
    $deskripsi = mysqli_real_escape_string($conn, $p[3]);
    $harga = $p[4];
    $nominal = is_null($p[5]) ? "NULL" : $p[5];
    $stok = $p[6];
    $gambar = $p[7];
    
    $query = "INSERT INTO `produk` (`id_user`, `kategori`, `nama_game`, `nama_produk`, `deskripsi`, `harga`, `nominal`, `stok`, `gambar`) 
              VALUES (NULL, '$kategori', '$nama_game', '$nama_produk', '$deskripsi', $harga, $nominal, $stok, '$gambar')";
              
    if (mysqli_query($conn, $query)) {
        echo "[V] Seed sukses: $nama_produk\n";
    } else {
        echo "[X] Seed gagal ($nama_produk): " . mysqli_error($conn) . "\n";
    }
}

// 7. Buat Admin Default jika kosong
$check_admin = mysqli_query($conn, "SELECT * FROM `admin` WHERE `username`='admin'");
if (mysqli_num_rows($check_admin) == 0) {
    mysqli_query($conn, "INSERT INTO `admin` (`username`, `password`, `nama_lengkap`) VALUES ('admin', 'admin123', 'Administrator Utama')");
    echo "[V] User admin default ditambahkan.\n";
} else {
    echo "[i] User admin default sudah ada.\n";
}

echo "\n=== MIGRASI SELESAI DENGAN SUKSES ===\n";
?>
