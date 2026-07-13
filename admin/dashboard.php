<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

include "../config/koneksi.php"; 

/** @var mysqli $conn */

// Hitung data ringkasan
$user_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM user"));
$transaksi_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM transaksi"));

// Total Penjualan Kotor (Semua yang berstatus Success)
$pendapatan_query = mysqli_query($conn, "SELECT SUM(total) as total FROM transaksi WHERE status='Success'");
$pendapatan_row = mysqli_fetch_assoc($pendapatan_query);
$total_pendapatan = isset($pendapatan_row['total']) ? intval($pendapatan_row['total']) : 0;

// Total Komisi Platform (Murni Profit Marketplace)
$komisi_query = mysqli_query($conn, "SELECT SUM(komisi) as total FROM transaksi WHERE status='Success'");
$komisi_row = mysqli_fetch_assoc($komisi_query);
$total_komisi = isset($komisi_row['total']) ? intval($komisi_row['total']) : 0;

// Statistik Kategori Produk
$topup_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM produk WHERE kategori='topup'"));
$akun_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM produk WHERE kategori='akun'"));
$item_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM produk WHERE kategori='item'"));

// Transaksi Pending
$pending_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM transaksi WHERE status='Pending'"));

// Penarikan Pending
$pending_withdrawal = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM penarikan_dana WHERE status='Pending'"));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - TopUpIn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body { font-family: 'Poppins', sans-serif; }</style>
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen flex flex-col md:flex-row">

    <!-- Sidebar -->
    <aside class="w-full md:w-64 bg-gray-800 border-b md:border-b-0 md:border-r border-gray-700 p-6 space-y-6 flex flex-col justify-between">
        <div class="space-y-6">
            <div class="text-xl font-extrabold tracking-wider text-indigo-400 flex items-center">
                <i class="fa-solid fa-user-shield mr-2"></i> ADMIN<span class="text-white">PANEL</span>
            </div>
            <nav class="space-y-2">
                <a href="dashboard.php" class="block bg-indigo-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold flex items-center">
                    <i class="fa-solid fa-chart-line w-5 mr-2"></i> Dashboard
                </a>
                <a href="produk.php" class="block text-gray-400 hover:bg-gray-700 hover:text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition flex items-center">
                    <i class="fa-solid fa-box w-5 mr-2"></i> Kelola Produk
                </a>
                <a href="transaksi.php" class="block text-gray-400 hover:bg-gray-700 hover:text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition flex items-center relative">
                    <i class="fa-solid fa-receipt w-5 mr-2"></i> Semua Transaksi
                    <?php if ($pending_count > 0) { ?>
                        <span class="absolute right-3 bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full animate-pulse">
                            <?= $pending_count ?>
                        </span>
                    <?php } ?>
                </a>
                <a href="penarikan.php" class="block text-gray-400 hover:bg-gray-700 hover:text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition flex items-center relative">
                    <i class="fa-solid fa-money-bill-transfer w-5 mr-2"></i> Tarik Saldo
                    <?php if ($pending_withdrawal > 0) { ?>
                        <span class="absolute right-3 bg-yellow-500 text-gray-900 text-[10px] font-extrabold px-2 py-0.5 rounded-full animate-pulse">
                            <?= $pending_withdrawal ?>
                        </span>
                    <?php } ?>
                </a>
                <a href="../index.php" target="_blank" class="block text-gray-400 hover:bg-gray-700 hover:text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition flex items-center">
                    <i class="fa-solid fa-globe w-5 mr-2"></i> Lihat Website
                </a>
            </nav>
        </div>
        <div>
            <a href="logout.php" onclick="return confirm('Keluar dari panel admin?')" class="block text-red-400 hover:bg-red-500/10 px-4 py-2.5 rounded-xl text-sm font-semibold transition border-t border-gray-700/50 pt-4 flex items-center">
                <i class="fa-solid fa-right-from-bracket w-5 mr-2"></i> Logout
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-grow p-6 md:p-10 space-y-8">
        <!-- Header -->
        <header class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-white">Ringkasan Data Toko</h1>
                <p class="text-gray-400 text-xs mt-1">Selamat datang kembali di panel kendali sistem.</p>
            </div>
            <div class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-2 text-xs text-indigo-400 font-semibold">
                <i class="fa-regular fa-clock mr-1.5"></i> <?= date('d M Y') ?>
            </div>
        </header>

        <!-- Warning Alert Penarikan Pending -->
        <?php if ($pending_withdrawal > 0) { ?>
            <div class="bg-yellow-500/10 border border-yellow-500/30 text-yellow-400 text-sm p-4 rounded-2xl flex justify-between items-center">
                <div>
                    <i class="fa-solid fa-circle-exclamation mr-2"></i>
                    Ada <strong><?= $pending_withdrawal ?> pengajuan tarik saldo</strong> dari seller yang membutuhkan transfer dan verifikasi Anda.
                </div>
                <a href="penarikan.php" class="bg-yellow-500 text-gray-900 text-xs font-bold px-4 py-2 rounded-xl hover:bg-yellow-400 transition">
                    Proses Sekarang
                </a>
            </div>
        <?php } ?>

        <!-- Stats Grid (Row 1) -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-6">
            
            <div class="bg-gray-800 p-6 rounded-2xl border border-gray-700 shadow-lg flex justify-between items-center">
                <div>
                    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Total Pengguna</p>
                    <h3 class="text-2xl font-extrabold text-white mt-2"><?= number_format($user_count); ?> <span class="text-xs font-normal text-gray-500 font-sans">Orang</span></h3>
                </div>
                <div class="w-10 h-10 bg-indigo-500/10 text-indigo-400 rounded-xl flex items-center justify-center text-lg">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>

            <div class="bg-gray-800 p-6 rounded-2xl border border-gray-700 shadow-lg flex justify-between items-center">
                <div>
                    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Total Transaksi</p>
                    <h3 class="text-2xl font-extrabold text-indigo-400 mt-2"><?= number_format($transaksi_count); ?> <span class="text-xs font-normal text-gray-500">Invoice</span></h3>
                </div>
                <div class="w-10 h-10 bg-indigo-500/10 text-indigo-400 rounded-xl flex items-center justify-center text-lg">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                </div>
            </div>

            <div class="bg-gray-800 p-6 rounded-2xl border border-gray-700 shadow-lg flex justify-between items-center">
                <div>
                    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Total Volume Penjualan</p>
                    <h3 class="text-2xl font-extrabold text-white mt-2">Rp <?= number_format($total_pendapatan, 0, ',', '.'); ?></h3>
                </div>
                <div class="w-10 h-10 bg-gray-500/10 text-gray-400 rounded-xl flex items-center justify-center text-lg">
                    <i class="fa-solid fa-cart-shopping"></i>
                </div>
            </div>

            <div class="bg-gray-800 p-6 rounded-2xl border border-emerald-500/30 shadow-lg bg-gradient-to-br from-gray-800 to-indigo-950/20 flex justify-between items-center">
                <div>
                    <p class="text-[10px] font-bold text-emerald-450 uppercase tracking-wider">Komisi Platform (Profit Murni)</p>
                    <h3 class="text-2xl font-extrabold text-emerald-400 mt-2">Rp <?= number_format($total_komisi, 0, ',', '.'); ?></h3>
                </div>
                <div class="w-10 h-10 bg-emerald-500/10 text-emerald-400 rounded-xl flex items-center justify-center text-lg">
                    <i class="fa-solid fa-sack-dollar"></i>
                </div>
            </div>

        </div>

        <!-- Inventory Stats -->
        <div class="bg-gray-800 rounded-2xl p-6 border border-gray-700 shadow-lg space-y-4">
            <h2 class="text-sm font-bold text-white uppercase tracking-wider border-b border-gray-700 pb-3">Statistik Inventori Kategori</h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <!-- Top Up -->
                <div class="bg-gray-900/60 p-4 rounded-xl border border-gray-850 flex items-center space-x-3">
                    <div class="w-10 h-10 bg-yellow-500/10 text-yellow-500 rounded-lg flex items-center justify-center text-lg"><i class="fa-solid fa-bolt"></i></div>
                    <div>
                        <span class="text-[10px] text-gray-500 uppercase block font-semibold">Produk Top Up</span>
                        <span class="text-lg font-bold text-white"><?= $topup_count ?> Varian</span>
                    </div>
                </div>

                <!-- Akun Game -->
                <div class="bg-gray-900/60 p-4 rounded-xl border border-gray-850 flex items-center space-x-3">
                    <div class="w-10 h-10 bg-purple-500/10 text-purple-400 rounded-lg flex items-center justify-center text-lg"><i class="fa-solid fa-shield-halved"></i></div>
                    <div>
                        <span class="text-[10px] text-gray-500 uppercase block font-semibold">Produk Akun Game</span>
                        <span class="text-lg font-bold text-white"><?= $akun_count ?> Akun</span>
                    </div>
                </div>

                <!-- Item Game -->
                <div class="bg-gray-900/60 p-4 rounded-xl border border-gray-850 flex items-center space-x-3">
                    <div class="w-10 h-10 bg-pink-500/10 text-pink-400 rounded-lg flex items-center justify-center text-lg"><i class="fa-solid fa-gift"></i></div>
                    <div>
                        <span class="text-[10px] text-gray-500 uppercase block font-semibold">Item & Voucher</span>
                        <span class="text-lg font-bold text-white"><?= $item_count ?> Item</span>
                    </div>
                </div>
            </div>
        </div>

    </main>

</body>
</html>