<?php
session_start();
// Proteksi: Wajib login
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: ../login.php");
    exit;
}

include "../config/koneksi.php";

/** @var mysqli $conn */
$user_id = intval($_SESSION['user_id']);

// Hitung data ringkasan penjual
$produk_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM produk WHERE id_user = $user_id"));

$transaksi_count_query = mysqli_query($conn, "
    SELECT COUNT(*) as total 
    FROM transaksi t 
    JOIN produk p ON t.id_produk = p.id_produk 
    WHERE p.id_user = $user_id
");
$transaksi_row = mysqli_fetch_assoc($transaksi_count_query);
$total_transaksi = isset($transaksi_row['total']) ? intval($transaksi_row['total']) : 0;

$pendapatan_query = mysqli_query($conn, "
    SELECT SUM(t.total) as total 
    FROM transaksi t 
    JOIN produk p ON t.id_produk = p.id_produk 
    WHERE p.id_user = $user_id AND t.status='Success'
");
$pendapatan_row = mysqli_fetch_assoc($pendapatan_query);
$total_pendapatan = isset($pendapatan_row['total']) ? intval($pendapatan_row['total']) : 0;

// Hitung transaksi pending untuk notifikasi
$pending_query = mysqli_query($conn, "
    SELECT COUNT(*) as total 
    FROM transaksi t 
    JOIN produk p ON t.id_produk = p.id_produk 
    WHERE p.id_user = $user_id AND t.status='Pending'
");
$pending_row = mysqli_fetch_assoc($pending_query);
$pending_count = isset($pending_row['total']) ? intval($pending_row['total']) : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Panel - TopUpIn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body { font-family: 'Poppins', sans-serif; }</style>
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen flex flex-col md:flex-row">

    <!-- Sidebar Penjual -->
    <aside class="w-full md:w-64 bg-gray-800 border-b md:border-b-0 md:border-r border-gray-700 p-6 space-y-6 flex flex-col justify-between">
        <div class="space-y-6">
            <div class="text-xl font-extrabold tracking-wider text-indigo-400 flex items-center">
                <i class="fa-solid fa-store mr-2 text-indigo-500"></i> SELLER<span class="text-white">PANEL</span>
            </div>
            
            <div class="bg-gray-900/50 p-4 rounded-2xl border border-gray-750 text-center">
                <div class="w-12 h-12 bg-indigo-600/20 text-indigo-400 rounded-full flex items-center justify-center mx-auto text-lg font-bold mb-2">
                    <?= strtoupper(substr($_SESSION['user_nama'], 0, 1)) ?>
                </div>
                <h4 class="font-bold text-sm text-white"><?= htmlspecialchars($_SESSION['user_nama']) ?></h4>
                <span class="text-[10px] text-gray-500 block mt-0.5"><?= htmlspecialchars($_SESSION['user_email']) ?></span>
            </div>

            <nav class="space-y-2">
                <a href="dashboard.php" class="block bg-indigo-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold flex items-center">
                    <i class="fa-solid fa-chart-line w-5 mr-2"></i> Dashboard
                </a>
                <a href="produk.php" class="block text-gray-400 hover:bg-gray-700 hover:text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition flex items-center">
                    <i class="fa-solid fa-box w-5 mr-2"></i> Kelola Dagangan
                </a>
                <a href="transaksi.php" class="block text-gray-400 hover:bg-gray-700 hover:text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition flex items-center relative">
                    <i class="fa-solid fa-receipt w-5 mr-2"></i> Penjualan Saya
                    <?php if ($pending_count > 0) { ?>
                        <span class="absolute right-3 bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full animate-pulse">
                            <?= $pending_count ?>
                        </span>
                    <?php } ?>
                </a>
                <a href="dompet.php" class="block text-gray-400 hover:bg-gray-700 hover:text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition flex items-center">
                    <i class="fa-solid fa-wallet w-5 mr-2"></i> Dompet & Saldo
                </a>
                <a href="../index.php" class="block text-gray-400 hover:bg-gray-700 hover:text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition flex items-center">
                    <i class="fa-solid fa-house w-5 mr-2"></i> Kembali Ke Toko
                </a>
            </nav>
        </div>
        <div>
            <a href="../logout.php" onclick="return confirm('Keluar dari panel seller?')" class="block text-red-400 hover:bg-red-500/10 px-4 py-2.5 rounded-xl text-sm font-semibold transition border-t border-gray-700/50 pt-4 flex items-center">
                <i class="fa-solid fa-right-from-bracket w-5 mr-2"></i> Logout
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-grow p-6 md:p-10 space-y-8">
        <!-- Header -->
        <header class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-white">Seller Dashboard</h1>
                <p class="text-gray-400 text-xs mt-1">Pantau performa penjualan game dan pengelolaan produk dagangan Anda.</p>
            </div>
            <div class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-2 text-xs text-indigo-400 font-semibold">
                <i class="fa-solid fa-shop mr-1.5"></i> Seller Akun & Item
            </div>
        </header>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            
            <div class="bg-gray-800 p-6 rounded-2xl border border-gray-700 shadow-lg flex justify-between items-center">
                <div>
                    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Produk Terdaftar</p>
                    <h3 class="text-3xl font-extrabold text-white mt-2"><?= number_format($produk_count); ?> <span class="text-xs font-normal text-gray-500">Items</span></h3>
                </div>
                <div class="w-12 h-12 bg-indigo-500/10 text-indigo-400 rounded-xl flex items-center justify-center text-xl">
                    <i class="fa-solid fa-box"></i>
                </div>
            </div>

            <div class="bg-gray-800 p-6 rounded-2xl border border-gray-700 shadow-lg flex justify-between items-center">
                <div>
                    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Transaksi Dagangan Anda</p>
                    <h3 class="text-3xl font-extrabold text-indigo-400 mt-2"><?= number_format($total_transaksi); ?> <span class="text-xs font-normal text-gray-500">Orders</span></h3>
                </div>
                <div class="w-12 h-12 bg-indigo-500/10 text-indigo-400 rounded-xl flex items-center justify-center text-xl">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                </div>
            </div>

            <div class="bg-gray-800 p-6 rounded-2xl border border-indigo-500/30 shadow-lg bg-gradient-to-br from-gray-800 to-indigo-950/20 flex justify-between items-center">
                <div>
                    <p class="text-[10px] font-bold text-emerald-400 uppercase tracking-wider">Pendapatan Bersih (Berhasil)</p>
                    <h3 class="text-3xl font-extrabold text-emerald-400 mt-2">Rp <?= number_format($total_pendapatan, 0, ',', '.'); ?></h3>
                </div>
                <div class="w-12 h-12 bg-emerald-500/10 text-emerald-400 rounded-xl flex items-center justify-center text-xl">
                    <i class="fa-solid fa-sack-dollar"></i>
                </div>
            </div>

        </div>

        <!-- Seller Tips / Info -->
        <div class="bg-gray-800 rounded-2xl p-6 border border-gray-700 shadow-lg space-y-4">
            <h2 class="text-sm font-bold text-white uppercase tracking-wider border-b border-gray-700 pb-3"><i class="fa-solid fa-circle-info text-indigo-400 mr-1"></i> Informasi Panduan Penjual</h2>
            <div class="text-xs text-gray-400 space-y-2 leading-relaxed">
                <p>1. <strong>Top Up Eksklusif:</strong> Penjual pihak ketiga hanya diizinkan menjual kategori <strong>Akun Game</strong> dan <strong>Item Game</strong>. Kategori top-up adalah official service.</p>
                <p>2. <strong>Sistem Rekening Bersama (Escrow):</strong> Pembeli akan mentransfer dana ke rekening resmi platform (Admin). Setelah pembayaran diverifikasi oleh Admin, status transaksi berubah menjadi <strong>Success</strong>.</p>
                <p>3. <strong>Proses Serah Terima:</strong> Jika transaksi sudah berstatus <strong>Success</strong>, Anda dapat melihat informasi kontak pembeli di menu <em>Penjualan Saya</em> untuk segera menyerahkan kredensial akun atau melakukan gift item.</p>
            </div>
        </div>

    </main>

    <?php include "../components/chat_widget.php"; ?>
</body>
</html>
