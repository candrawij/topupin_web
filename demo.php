<?php
// c:\laragon\www\TopUpin\demo.php
session_start();
include "config/koneksi.php";
/** @var mysqli $conn */

$error = isset($_GET['error']) ? $_GET['error'] : '';

// Statistik real dari database
$total_produk   = mysqli_num_rows(mysqli_query($conn, "SELECT id_produk FROM produk"));
$total_trx      = mysqli_num_rows(mysqli_query($conn, "SELECT id_trx FROM transaksi WHERE status='Success'"));
$total_users    = mysqli_num_rows(mysqli_query($conn, "SELECT id_user FROM user"));
$total_sellers  = mysqli_num_rows(mysqli_query($conn, "SELECT id_user FROM user WHERE saldo > 0 OR id_user IN (SELECT DISTINCT id_user FROM produk WHERE id_user IS NOT NULL)"));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo & Panduan — TopUpIn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #0b0f19; }
        .glass { background: rgba(17,24,39,0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.06); }
        .glass-card { background: rgba(30,41,59,0.5); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.08); transition: all 0.3s; }
        .glass-card:hover { border-color: rgba(99,102,241,0.4); transform: translateY(-2px); box-shadow: 0 8px 30px rgba(99,102,241,0.15); }
        .step-line::after { content:''; position:absolute; top:50%; left:100%; width:100%; height:2px; background:linear-gradient(to right,#4f46e5,transparent); transform:translateY(-50%); }
        @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-8px)} }
        .float { animation: float 3s ease-in-out infinite; }
        .badge-new { background: linear-gradient(135deg,#4f46e5,#7c3aed); }
        .tab-btn.active { background: #4f46e5; color: white; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
    </style>
</head>
<body class="text-gray-200 min-h-screen">

<!-- TOP BANNER -->
<div class="bg-gradient-to-r from-indigo-900/60 via-purple-900/40 to-indigo-900/60 border-b border-indigo-500/30 py-3 text-center text-xs text-indigo-300">
    <i class="fa-solid fa-graduation-cap mr-2 text-indigo-400"></i>
    <span class="font-semibold text-white">Mode Demo Akademik</span> — Halaman panduan interaktif untuk evaluasi mata kuliah &nbsp;|&nbsp;
    <a href="index.php" class="text-indigo-400 hover:underline font-semibold">← Kembali ke Website</a>
</div>

<!-- NAVBAR -->
<nav class="bg-gray-900/80 border-b border-gray-800 p-4 sticky top-0 z-50 backdrop-blur-md">
    <div class="container mx-auto flex justify-between items-center px-4">
        <a href="index.php" class="flex items-center space-x-2">
            <i class="fa-solid fa-gamepad text-2xl text-indigo-500"></i>
            <span class="text-xl font-extrabold tracking-wider text-white">TopUp<span class="text-indigo-400">In</span></span>
        </a>
        <div class="flex items-center gap-3 text-xs">
            <span class="bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 px-3 py-1 rounded-full">
                <i class="fa-solid fa-flask mr-1"></i>Halaman Demo
            </span>
            <a href="index.php" class="text-gray-400 hover:text-white transition"><i class="fa-solid fa-arrow-left mr-1"></i>Website</a>
        </div>
    </div>
</nav>

<main class="container mx-auto px-4 py-12 max-w-5xl">

    <!-- HEADER HERO -->
    <div class="text-center mb-14">
        <div class="inline-flex items-center gap-2 bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-xs px-4 py-2 rounded-full mb-5 font-semibold">
            <i class="fa-solid fa-star"></i> Platform Top-Up Game — Proyek Mata Kuliah
        </div>
        <h1 class="text-4xl md:text-5xl font-black text-white leading-tight mb-4">
            Panduan Demo<br>
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-purple-400">TopUpIn Platform</span>
        </h1>
        <p class="text-gray-400 max-w-2xl mx-auto text-sm leading-relaxed">
            TopUpIn adalah platform marketplace gaming terintegrasi yang memungkinkan pembelian top-up, jual-beli akun game, 
            dan manajemen seller dengan sistem escrow (rekening bersama), live chat CS real-time, dan integrasi notifikasi Telegram Bot.
        </p>

        <!-- STATISTIK REAL -->
        <div class="flex flex-wrap justify-center gap-8 mt-8">
            <div class="text-center">
                <div class="text-3xl font-black text-indigo-400"><?= $total_produk ?>+</div>
                <div class="text-xs text-gray-500 mt-1">Produk Tersedia</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-black text-emerald-400"><?= $total_trx ?>+</div>
                <div class="text-xs text-gray-500 mt-1">Transaksi Selesai</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-black text-purple-400"><?= $total_users ?>+</div>
                <div class="text-xs text-gray-500 mt-1">Pengguna Terdaftar</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-black text-amber-400">3</div>
                <div class="text-xs text-gray-500 mt-1">Role Pengguna</div>
            </div>
        </div>
    </div>

    <!-- ERROR MESSAGE -->
    <?php if ($error): ?>
    <div class="bg-red-500/10 border border-red-500/30 text-red-400 text-sm p-4 rounded-2xl mb-8 text-center">
        <i class="fa-solid fa-circle-exclamation mr-2"></i>
        <?php if ($error === 'user_not_found'): ?>
            Akun User Demo belum ada. Jalankan <a href="config/seed_demo.php" class="underline font-semibold">seeder data demo</a> terlebih dahulu.
        <?php elseif ($error === 'seller_not_found'): ?>
            Akun Seller Demo belum ada. Jalankan <a href="config/seed_demo.php" class="underline font-semibold">seeder data demo</a> terlebih dahulu.
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- QUICK LOGIN CARDS -->
    <section class="mb-14">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-white">🚀 Login Cepat — Pilih Role</h2>
            <p class="text-gray-500 text-xs mt-2">Klik tombol di bawah untuk langsung masuk tanpa mengetik password</p>
        </div>

        <div class="grid md:grid-cols-3 gap-5">

            <!-- USER CARD -->
            <div class="glass-card rounded-3xl p-6 text-center relative overflow-hidden">
                <div class="absolute top-3 right-3">
                    <span class="badge-new text-white text-[9px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">Pembeli</span>
                </div>
                <div class="w-16 h-16 bg-indigo-500/10 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-4 float">
                    🛒
                </div>
                <h3 class="font-bold text-white text-base mb-1">Akun User Demo</h3>
                <p class="text-xs text-gray-500 mb-4 leading-relaxed">Simulasikan pengalaman berbelanja produk top-up, melihat riwayat transaksi, dan menggunakan fitur live chat CS.</p>
                <div class="bg-gray-900/60 rounded-xl p-3 mb-4 text-left">
                    <div class="flex justify-between text-xs mb-1.5">
                        <span class="text-gray-500">Email</span>
                        <span class="text-indigo-300 font-mono font-semibold">demo@topupin.id</span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-500">Password</span>
                        <span class="text-indigo-300 font-mono font-semibold">demo123</span>
                    </div>
                </div>
                <a href="demo_login.php?role=user"
                   class="w-full block bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl text-xs transition shadow-lg shadow-indigo-600/20 text-center">
                    <i class="fa-solid fa-right-to-bracket mr-1.5"></i>Login sebagai Pembeli
                </a>
                <div class="mt-3 text-[10px] text-gray-600">
                    <i class="fa-solid fa-circle-info mr-1"></i>Sudah ada 2 riwayat transaksi demo
                </div>
            </div>

            <!-- SELLER CARD -->
            <div class="glass-card rounded-3xl p-6 text-center relative overflow-hidden border-emerald-500/20">
                <div class="absolute top-3 right-3">
                    <span class="bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-[9px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">Penjual</span>
                </div>
                <div class="w-16 h-16 bg-emerald-500/10 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-4 float" style="animation-delay:0.5s">
                    🏪
                </div>
                <h3 class="font-bold text-white text-base mb-1">Akun Seller Demo</h3>
                <p class="text-xs text-gray-500 mb-4 leading-relaxed">Kelola produk game, pantau order masuk, dan lihat fitur penarikan saldo dari dompet seller.</p>
                <div class="bg-gray-900/60 rounded-xl p-3 mb-4 text-left">
                    <div class="flex justify-between text-xs mb-1.5">
                        <span class="text-gray-500">Email</span>
                        <span class="text-emerald-300 font-mono font-semibold">seller@topupin.id</span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-500">Password</span>
                        <span class="text-emerald-300 font-mono font-semibold">seller123</span>
                    </div>
                </div>
                <a href="demo_login.php?role=seller"
                   class="w-full block bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 rounded-xl text-xs transition shadow-lg shadow-emerald-600/20 text-center">
                    <i class="fa-solid fa-right-to-bracket mr-1.5"></i>Login sebagai Seller
                </a>
                <div class="mt-3 text-[10px] text-gray-600">
                    <i class="fa-solid fa-circle-info mr-1"></i>Sudah punya 3 produk & saldo Rp 75.000
                </div>
            </div>

            <!-- ADMIN CARD -->
            <div class="glass-card rounded-3xl p-6 text-center relative overflow-hidden border-purple-500/20">
                <div class="absolute top-3 right-3">
                    <span class="bg-purple-500/20 text-purple-400 border border-purple-500/30 text-[9px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">Admin</span>
                </div>
                <div class="w-16 h-16 bg-purple-500/10 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-4 float" style="animation-delay:1s">
                    🛡️
                </div>
                <h3 class="font-bold text-white text-base mb-1">Akun Admin</h3>
                <p class="text-xs text-gray-500 mb-4 leading-relaxed">Kelola seluruh transaksi, produk, verifikasi pembayaran, CS chat inbox, dan pencairan dana seller.</p>
                <div class="bg-gray-900/60 rounded-xl p-3 mb-4 text-left">
                    <div class="flex justify-between text-xs mb-1.5">
                        <span class="text-gray-500">Username</span>
                        <span class="text-purple-300 font-mono font-semibold">admin</span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-500">Password</span>
                        <span class="text-purple-300 font-mono font-semibold">admin123</span>
                    </div>
                </div>
                <a href="demo_login.php?role=admin"
                   class="w-full block bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 rounded-xl text-xs transition shadow-lg shadow-purple-600/20 text-center">
                    <i class="fa-solid fa-right-to-bracket mr-1.5"></i>Login sebagai Admin
                </a>
                <div class="mt-3 text-[10px] text-gray-600">
                    <i class="fa-solid fa-circle-info mr-1"></i>Akses penuh ke semua fitur manajemen
                </div>
            </div>
        </div>
    </section>

    <!-- PANDUAN ALUR FITUR -->
    <section class="mb-14">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-white">📋 Panduan Alur Fitur</h2>
            <p class="text-gray-500 text-xs mt-2">Pilih skenario di bawah untuk melihat langkah-langkahnya</p>
        </div>

        <!-- TABS -->
        <div class="flex flex-wrap gap-2 justify-center mb-6">
            <button onclick="switchTab('pembelian')" id="tab-pembelian" class="tab-btn active text-xs font-semibold px-4 py-2 rounded-xl bg-gray-800 text-gray-400 hover:text-white transition">
                🛒 Alur Pembelian
            </button>
            <button onclick="switchTab('admin')" id="tab-admin" class="tab-btn text-xs font-semibold px-4 py-2 rounded-xl bg-gray-800 text-gray-400 hover:text-white transition">
                🛡️ Alur Admin
            </button>
            <button onclick="switchTab('seller')" id="tab-seller" class="tab-btn text-xs font-semibold px-4 py-2 rounded-xl bg-gray-800 text-gray-400 hover:text-white transition">
                🏪 Alur Seller
            </button>
            <button onclick="switchTab('cs')" id="tab-cs" class="tab-btn text-xs font-semibold px-4 py-2 rounded-xl bg-gray-800 text-gray-400 hover:text-white transition">
                💬 Alur CS Chat
            </button>
            <button onclick="switchTab('bot')" id="tab-bot" class="tab-btn text-xs font-semibold px-4 py-2 rounded-xl bg-gray-800 text-gray-400 hover:text-white transition">
                🤖 Integrasi Telegram
            </button>
        </div>

        <div class="glass rounded-3xl p-6">

            <!-- TAB: PEMBELIAN -->
            <div id="content-pembelian" class="tab-content active">
                <h3 class="font-bold text-white text-sm mb-4">Alur Pembelian Produk (sebagai User)</h3>
                <div class="space-y-3">
                    <?php
                    $steps_pembelian = [
                        ['1', 'fa-magnifying-glass', 'text-indigo-400', 'Buka Halaman Katalog', 'Navigasi ke menu <b>Catalog</b> di navbar, lalu filter produk berdasarkan kategori (Top Up, Akun Game, atau Item Game).', 'catalog.php'],
                        ['2', 'fa-box-open', 'text-purple-400', 'Pilih Produk & Lihat Detail', 'Klik produk untuk melihat detail lengkap termasuk deskripsi, harga, dan form pembelian terintegrasi.', null],
                        ['3', 'fa-cart-shopping', 'text-amber-400', 'Isi Form Checkout', 'Masukkan User ID Game, nomor kontak, metode pembayaran (QRIS/Transfer BCA), lalu klik Beli Sekarang.', null],
                        ['4', 'fa-file-invoice', 'text-emerald-400', 'Halaman Pembayaran', 'Sistem menampilkan invoice lengkap dengan instruksi pembayaran dan tombol pintasan ke CS Telegram.', null],
                        ['5', 'fa-receipt', 'text-indigo-400', 'Lacak Status Pesanan', 'Buka menu <b>Lacak Pesanan</b> untuk memantau status transaksi secara real-time (Pending/Success/Failed).', 'riwayat.php'],
                    ];
                    foreach ($steps_pembelian as $s) {
                        echo "<div class='flex gap-4 items-start bg-gray-900/40 rounded-2xl p-4'>";
                        echo "<div class='w-9 h-9 bg-gray-800 rounded-xl flex items-center justify-center flex-shrink-0 {$s[2]} font-black text-sm'>{$s[0]}</div>";
                        echo "<div class='flex-1'>";
                        echo "<div class='font-semibold text-white text-sm mb-0.5'>{$s[2][5]}. <i class='fa-solid {$s[1]} mr-1 {$s[2]}'></i>{$s[3]}</div>";
                        echo "<div class='text-xs text-gray-400 leading-relaxed'>{$s[4]}</div>";
                        if ($s[5]) echo "<a href='{$s[5]}' class='inline-block mt-2 text-[10px] text-indigo-400 hover:underline font-semibold'><i class='fa-solid fa-arrow-right mr-1'></i>Buka Halaman Ini</a>";
                        echo "</div></div>";
                    }
                    ?>
                </div>
            </div>

            <!-- TAB: ADMIN -->
            <div id="content-admin" class="tab-content">
                <h3 class="font-bold text-white text-sm mb-4">Alur Manajemen Admin</h3>
                <div class="space-y-3">
                    <?php
                    $steps_admin = [
                        ['1', 'fa-chart-line', 'text-purple-400', 'Dashboard Statistik', 'Lihat ringkasan data real-time: total transaksi, pendapatan platform, komisi yang diterima, dan jumlah pengguna.', 'admin/dashboard.php'],
                        ['2', 'fa-receipt', 'text-amber-400', 'Verifikasi Transaksi', 'Buka menu <b>Semua Transaksi</b>. Admin bisa melihat detail pembelian dan mengklik tombol ✅ Proses atau ❌ Tolak untuk setiap transaksi Pending.', 'admin/transaksi.php'],
                        ['3', 'fa-box', 'text-indigo-400', 'Kelola Produk', 'Tambah, edit, atau hapus produk Top-Up, Akun Game, dan Item Game dari halaman ini.', 'admin/produk.php'],
                        ['4', 'fa-comments', 'text-emerald-400', 'CS Chat Inbox', 'Lihat dan balas semua sesi chat dari user. Badge merah muncul di menu jika ada pesan baru yang belum dibaca.', 'admin/chat.php'],
                        ['5', 'fa-money-bill-transfer', 'text-rose-400', 'Approve Penarikan Saldo', 'Seller yang mengajukan pencairan dana akan muncul di halaman ini. Admin bisa approve atau tolak permintaan.', 'admin/penarikan.php'],
                    ];
                    foreach ($steps_admin as $s) {
                        echo "<div class='flex gap-4 items-start bg-gray-900/40 rounded-2xl p-4'>";
                        echo "<div class='w-9 h-9 bg-gray-800 rounded-xl flex items-center justify-center flex-shrink-0 {$s[2]} font-black text-sm'>{$s[0]}</div>";
                        echo "<div class='flex-1'>";
                        echo "<div class='font-semibold text-white text-sm mb-0.5'><i class='fa-solid {$s[1]} mr-1 {$s[2]}'></i>{$s[3]}</div>";
                        echo "<div class='text-xs text-gray-400 leading-relaxed'>{$s[4]}</div>";
                        if ($s[5]) echo "<a href='{$s[5]}' class='inline-block mt-2 text-[10px] text-purple-400 hover:underline font-semibold'><i class='fa-solid fa-arrow-right mr-1'></i>Buka Halaman Ini</a>";
                        echo "</div></div>";
                    }
                    ?>
                </div>
            </div>

            <!-- TAB: SELLER -->
            <div id="content-seller" class="tab-content">
                <h3 class="font-bold text-white text-sm mb-4">Alur Dashboard Seller</h3>
                <div class="space-y-3">
                    <?php
                    $steps_seller = [
                        ['1', 'fa-chart-pie', 'text-emerald-400', 'Dashboard Seller', 'Lihat ringkasan penjualan pribadi: total produk aktif, jumlah order masuk, dan total pendapatan bersih.', 'seller/dashboard.php'],
                        ['2', 'fa-plus', 'text-indigo-400', 'Tambah Produk Baru', 'Seller dapat menambahkan produk kategori <b>Akun Game</b> atau <b>Item Game</b>. Kategori Top-Up hanya untuk admin (official service).', 'seller/produk.php'],
                        ['3', 'fa-bag-shopping', 'text-amber-400', 'Penjualan Masuk', 'Lihat semua order yang masuk untuk produk milik seller. Jika status sudah Success, tombol "Hubungi WA" atau "Email" pembeli akan muncul.', 'seller/transaksi.php'],
                        ['4', 'fa-wallet', 'text-purple-400', 'Dompet & Pencairan Saldo', 'Saldo otomatis bertambah (97% dari harga produk) setiap kali transaksi berhasil diverifikasi admin. Seller bisa ajukan pencairan ke rekening bank.', 'seller/dompet.php'],
                    ];
                    foreach ($steps_seller as $s) {
                        echo "<div class='flex gap-4 items-start bg-gray-900/40 rounded-2xl p-4'>";
                        echo "<div class='w-9 h-9 bg-gray-800 rounded-xl flex items-center justify-center flex-shrink-0 {$s[2]} font-black text-sm'>{$s[0]}</div>";
                        echo "<div class='flex-1'>";
                        echo "<div class='font-semibold text-white text-sm mb-0.5'><i class='fa-solid {$s[1]} mr-1 {$s[2]}'></i>{$s[3]}</div>";
                        echo "<div class='text-xs text-gray-400 leading-relaxed'>{$s[4]}</div>";
                        if ($s[5]) echo "<a href='{$s[5]}' class='inline-block mt-2 text-[10px] text-emerald-400 hover:underline font-semibold'><i class='fa-solid fa-arrow-right mr-1'></i>Buka Halaman Ini</a>";
                        echo "</div></div>";
                    }
                    ?>
                </div>
            </div>

            <!-- TAB: CS CHAT -->
            <div id="content-cs" class="tab-content">
                <h3 class="font-bold text-white text-sm mb-4">Alur Live Chat Customer Service</h3>
                <div class="space-y-3">
                    <div class="bg-gray-900/40 rounded-2xl p-4 text-xs text-gray-400 leading-relaxed border border-indigo-500/10">
                        <b class="text-white block mb-2">Cara Kerja Sistem Chat:</b>
                        Chat menggunakan sistem <b>AJAX Polling</b> — browser user secara otomatis mengirim request ke server setiap 3 detik untuk mengecek apakah ada pesan baru dari admin. Ini memberikan pengalaman real-time tanpa perlu me-refresh halaman.
                    </div>
                    <?php
                    $steps_cs = [
                        ['Sisi User', 'fa-user', 'text-indigo-400', 'Login dan buka website utama. Di pojok kanan bawah, klik tombol 💬 berwarna indigo untuk membuka widget chat. Ketik pesan dan kirim. Status CS online tampil di header widget.'],
                        ['Sisi Admin', 'fa-headset', 'text-purple-400', 'Login ke Admin Panel → buka menu <b>CS Chat Support</b>. Daftar sesi chat dari semua user tampil di kolom kiri. Klik sesi untuk membuka chat, lalu balas pesan di kolom input kanan.'],
                        ['Real-Time', 'fa-bolt', 'text-amber-400', 'Pesan dari user langsung tampil di inbox admin. Pesan balasan admin langsung tampil di widget user (maksimal dalam 3 detik, sesuai interval polling). Tidak perlu refresh halaman!'],
                        ['Badge Notifikasi', 'fa-bell', 'text-emerald-400', 'Titik merah muncul di menu CS Chat di sidebar admin jika ada pesan yang belum dibaca. Badge juga muncul di tombol chat user jika ada balasan baru dari admin.'],
                    ];
                    foreach ($steps_cs as $i => $s) {
                        echo "<div class='flex gap-4 items-start bg-gray-900/40 rounded-2xl p-4'>";
                        echo "<div class='w-9 h-9 bg-gray-800 rounded-xl flex items-center justify-center flex-shrink-0 {$s[2]}'><i class='fa-solid {$s[1]}'></i></div>";
                        echo "<div class='flex-1'><div class='font-semibold text-white text-sm mb-0.5'>{$s[0]}</div>";
                        echo "<div class='text-xs text-gray-400 leading-relaxed'>{$s[3]}</div></div></div>";
                    }
                    ?>
                </div>
            </div>

            <!-- TAB: TELEGRAM BOT -->
            <div id="content-bot" class="tab-content">
                <h3 class="font-bold text-white text-sm mb-4">Integrasi Bot Telegram</h3>
                <div class="bg-gray-900/40 rounded-2xl p-4 text-xs text-gray-400 leading-relaxed mb-4 border border-blue-500/10">
                    <b class="text-white block mb-2">Arsitektur Integrasi:</b>
                    Website PHP berkomunikasi dengan Bot Telegram (Node.js/TypeScript + grammY) melalui API internal di port 3001. Bot menerima update dari Telegram menggunakan mekanisme <b>Webhook</b>.
                </div>
                <div class="space-y-3">
                    <?php
                    $steps_bot = [
                        ['Checkout → Bot Notifikasi', 'fa-arrow-right', 'text-blue-400', 'Setiap kali user berhasil checkout, website PHP mengirimkan data transaksi ke endpoint Bot API (<code>localhost:3001/api/create-transaction</code>). Bot kemudian mengirim pesan notifikasi ke grup Telegram admin secara otomatis.'],
                        ['Tombol Approve di Telegram', 'fa-check-double', 'text-emerald-400', 'Notifikasi di Telegram dilengkapi tombol inline: <b>✅ Proses (Sukses)</b> dan <b>❌ Tolak (Gagal)</b>. Admin grup Telegram bisa langsung klik tanpa perlu buka website.'],
                        ['Bot → Webhook Website', 'fa-webhook', 'text-amber-400', 'Saat admin klik tombol di Telegram, bot mengirim request POST ke <code>/api/webhook_trx.php</code> di website dengan data status transaksi dan secret key keamanan.'],
                        ['Status Update Real-Time', 'fa-rotate', 'text-purple-400', 'Website PHP memverifikasi secret key, lalu memperbarui status transaksi di database MySQL. Perubahan langsung terlihat di halaman riwayat user dan dashboard admin tanpa perlu intervensi manual.'],
                        ['Deep-Link CS Telegram', 'fa-link', 'text-indigo-400', 'Di halaman pembayaran dan riwayat pesanan, terdapat tombol "Tanya CS via Telegram". Tombol ini membuka bot dengan payload terenkripsi (AES-256-ECB) berisi ID transaksi untuk konteks yang spesifik.'],
                    ];
                    foreach ($steps_bot as $i => $s) {
                        echo "<div class='flex gap-4 items-start bg-gray-900/40 rounded-2xl p-4'>";
                        echo "<div class='w-9 h-9 bg-gray-800 rounded-xl flex items-center justify-center flex-shrink-0 {$s[2]}'><i class='fa-solid {$s[1]}'></i></div>";
                        echo "<div class='flex-1'><div class='font-semibold text-white text-sm mb-0.5'>{$s[0]}</div>";
                        echo "<div class='text-xs text-gray-400 leading-relaxed'>{$s[3]}</div></div></div>";
                    }
                    ?>
                </div>
            </div>

        </div>
    </section>

    <!-- TECH STACK -->
    <section class="mb-14">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-white">⚙️ Teknologi yang Digunakan</h2>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <?php
            $techs = [
                ['🐘', 'PHP 8.x', 'Backend utama — native tanpa framework', 'border-indigo-500/20'],
                ['🗄️', 'MySQL / MariaDB', 'Database relasional — 13+ tabel', 'border-blue-500/20'],
                ['🤖', 'Telegram Bot API', 'Notifikasi & approval otomatis via grammY', 'border-sky-500/20'],
                ['📘', 'TypeScript + Node.js', 'Runtime bot dengan Prisma ORM', 'border-emerald-500/20'],
                ['🎨', 'TailwindCSS', 'Styling modern dengan dark glassmorphism', 'border-purple-500/20'],
                ['🔒', 'AES-256-ECB', 'Enkripsi deep-link Telegram payload', 'border-amber-500/20'],
                ['⚡', 'AJAX Polling', 'Real-time chat CS tanpa WebSocket', 'border-rose-500/20'],
                ['🚀', 'cPanel + Git', 'Deployment otomatis via .cpanel.yml', 'border-gray-500/20'],
            ];
            foreach ($techs as $t) {
                echo "<div class='glass-card rounded-2xl p-4 text-center border {$t[3]}'>";
                echo "<div class='text-3xl mb-2'>{$t[0]}</div>";
                echo "<div class='font-bold text-white text-xs mb-1'>{$t[1]}</div>";
                echo "<div class='text-[10px] text-gray-500 leading-relaxed'>{$t[2]}</div>";
                echo "</div>";
            }
            ?>
        </div>
    </section>

    <!-- QUICK LINKS -->
    <section class="mb-8">
        <div class="glass rounded-3xl p-6">
            <h3 class="font-bold text-white text-sm mb-4 text-center">🔗 Tautan Langsung ke Semua Halaman</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-xs">
                <?php
                $links = [
                    ['fa-house', 'text-indigo-400', 'Beranda', 'index.php'],
                    ['fa-store', 'text-purple-400', 'Katalog Produk', 'catalog.php'],
                    ['fa-receipt', 'text-emerald-400', 'Lacak Pesanan', 'riwayat.php'],
                    ['fa-right-to-bracket', 'text-amber-400', 'Login User', 'login.php'],
                    ['fa-user-plus', 'text-sky-400', 'Daftar Akun', 'register.php'],
                    ['fa-user-shield', 'text-rose-400', 'Admin Panel', 'admin/login.php'],
                    ['fa-chart-line', 'text-indigo-400', 'Dashboard Admin', 'admin/dashboard.php'],
                    ['fa-comments', 'text-purple-400', 'CS Chat Admin', 'admin/chat.php'],
                    ['fa-shop', 'text-emerald-400', 'Dashboard Seller', 'seller/dashboard.php'],
                    ['fa-wallet', 'text-amber-400', 'Dompet Seller', 'seller/dompet.php'],
                    ['fa-boxes-stacked', 'text-sky-400', 'Produk Seller', 'seller/produk.php'],
                    ['fa-info-circle', 'text-gray-400', 'Tentang Kami', 'tentang.php'],
                ];
                foreach ($links as $l) {
                    echo "<a href='{$l[3]}' class='flex items-center gap-2 bg-gray-900/40 hover:bg-gray-800/60 border border-gray-800 hover:border-indigo-500/30 rounded-xl p-3 transition'>";
                    echo "<i class='fa-solid {$l[0]} {$l[1]} w-4 text-center'></i>";
                    echo "<span class='text-gray-300'>{$l[2]}</span>";
                    echo "</a>";
                }
                ?>
            </div>
        </div>
    </section>

</main>

<!-- FOOTER -->
<footer class="border-t border-gray-800 py-6 text-center text-xs text-gray-600 mt-4">
    <p>TopUpIn — Platform Marketplace Gaming &amp; Top Up</p>
    <p class="mt-1">Proyek Mata Kuliah © <?= date('Y') ?> &nbsp;|&nbsp; <a href="index.php" class="text-indigo-500 hover:underline">Kembali ke Website</a></p>
</footer>

<?php include "components/chat_widget.php"; ?>

<script>
function switchTab(tabName) {
    // Sembunyikan semua konten tab
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));

    // Tampilkan tab yang dipilih
    document.getElementById('content-' + tabName).classList.add('active');
    document.getElementById('tab-' + tabName).classList.add('active');
}
</script>

</body>
</html>
