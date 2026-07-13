<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

include "../config/koneksi.php"; 

/** @var mysqli $conn */

// Proses Aksi Admin (Setujui / Tolak Transaksi)
if (isset($_GET['id']) && isset($_GET['action'])) {
    $id_action = intval($_GET['id']);
    $action = $_GET['action'];
    
    // Ambil status transaksi saat ini demi mencegah double processing
    $trx_check_q = mysqli_query($conn, "
        SELECT t.*, p.id_user as seller_id 
        FROM transaksi t 
        JOIN produk p ON t.id_produk = p.id_produk 
        WHERE t.id_trx = $id_action
    ");
    $trx_check = mysqli_fetch_assoc($trx_check_q);
    
    if ($trx_check && $trx_check['status'] == 'Pending') {
        if ($action == 'success') {
            $status_baru = 'Success';
            
            // Jika itu produk milik seller, hitung pembagian hasil
            $seller_id = intval($trx_check['seller_id']);
            if ($seller_id > 0) {
                $total = intval($trx_check['total']);
                $komisi = round($total * 0.03); // 3% Komisi Platform
                $bersih = $total - $komisi;      // 97% Bersih Seller
                
                // 1. Catat komisi platform ke invoice transaksi
                mysqli_query($conn, "UPDATE transaksi SET komisi = $komisi WHERE id_trx = $id_action");
                
                // 2. Tambah saldo ke dompet penjual
                mysqli_query($conn, "UPDATE user SET saldo = saldo + $bersih WHERE id_user = $seller_id");
            }
        } elseif ($action == 'failed') {
            $status_baru = 'Failed';
        } else {
            $status_baru = '';
        }
        
        if (!empty($status_baru)) {
            // Update status transaksi
            mysqli_query($conn, "UPDATE transaksi SET status = '$status_baru' WHERE id_trx = $id_action");
            header("Location: transaksi.php?msg=updated");
            exit;
        }
    }
}

// Mengambil seluruh data transaksi
$data = mysqli_query($conn, "
    SELECT t.*, p.nama_produk, p.nama_game, p.kategori 
    FROM transaksi t
    JOIN produk p ON t.id_produk = p.id_produk 
    ORDER BY t.tanggal DESC
");

// Hitung pending transaction untuk badge navbar
$pending_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM transaksi WHERE status='Pending'"));

// Hitung pending withdrawal request untuk badge navbar
$pending_withdrawal = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM penarikan_dana WHERE status='Pending'"));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Invoice - Admin Panel</title>
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
                <a href="dashboard.php" class="block text-gray-400 hover:bg-gray-700 hover:text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition flex items-center">
                    <i class="fa-solid fa-chart-line w-5 mr-2"></i> Dashboard
                </a>
                <a href="produk.php" class="block text-gray-400 hover:bg-gray-700 hover:text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition flex items-center">
                    <i class="fa-solid fa-box w-5 mr-2"></i> Kelola Produk
                </a>
                <a href="transaksi.php" class="block bg-indigo-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold flex items-center relative">
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
    <main class="flex-grow p-6 md:p-10 space-y-6">
        <header>
            <h1 class="text-2xl font-bold text-white">Data Penjualan Keseluruhan</h1>
            <p class="text-gray-400 text-xs mt-1">Kelola transaksi masuk, verifikasi bukti pembayaran, dan ubah status pesanan.</p>
        </header>

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'updated') { ?>
            <div class="bg-green-500/10 border border-green-500/20 text-green-400 text-xs p-3.5 rounded-xl text-center">
                <i class="fa-solid fa-circle-check mr-1.5"></i> Status transaksi berhasil diperbarui!
            </div>
        <?php } ?>

        <!-- Transactions Table -->
        <div class="bg-gray-800 rounded-2xl shadow-xl border border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-750 text-indigo-400 text-[10px] uppercase tracking-wider">
                            <th class="p-4 font-semibold w-16">Invoice</th>
                            <th class="p-4 font-semibold">Produk</th>
                            <th class="p-4 font-semibold">Tujuan Game ID</th>
                            <th class="p-4 font-semibold">Kontak Pembeli</th>
                            <th class="p-4 font-semibold">Total & Metode</th>
                            <th class="p-4 font-semibold">Bukti Bayar</th>
                            <th class="p-4 font-semibold">Status</th>
                            <th class="p-4 font-semibold text-center">Aksi Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700 text-xs">
                        <?php 
                        if(mysqli_num_rows($data) > 0) {
                            while($row = mysqli_fetch_assoc($data)){ 
                                $status = strtolower($row['status']);
                                $badgeColor = "bg-yellow-500/10 text-yellow-500 border border-yellow-500/20";
                                if($status == 'success') $badgeColor = "bg-green-500/10 text-green-500 border border-green-500/20";
                                if($status == 'failed') $badgeColor = "bg-red-500/10 text-red-500 border border-red-500/20";
                        ?>
                        <tr class="hover:bg-gray-750/30 transition">
                            <!-- Invoice ID -->
                            <td class="p-4 font-mono text-gray-500">#<?= $row['id_trx']; ?></td>
                            
                            <!-- Produk Info -->
                            <td class="p-4">
                                <span class="font-semibold text-white"><?= htmlspecialchars($row['nama_game']); ?></span>
                                <span class="block text-[10px] text-gray-400 mt-0.5"><?= htmlspecialchars($row['nama_produk']); ?></span>
                            </td>

                            <!-- Tujuan Game ID -->
                            <td class="p-4 font-medium text-gray-300 font-mono">
                                <?php if (!empty($row['user_game'])) { ?>
                                    <?= htmlspecialchars($row['user_game']); ?>
                                    <?= !empty($row['server_game']) ? "<span class='text-[10px] text-gray-500'>(".htmlspecialchars($row['server_game']).")</span>" : "" ?>
                                <?php } else { ?>
                                    <span class="text-gray-500 italic">Marketplace Akun/Item</span>
                                <?php } ?>
                            </td>

                            <!-- Kontak Pembeli -->
                            <td class="p-4">
                                <span class="text-white font-medium"><?= htmlspecialchars($row['kontak_pembeli']); ?></span>
                                <?php if (!empty($row['catatan'])) { ?>
                                    <span class="block text-[10px] text-indigo-400 truncate max-w-[150px] mt-0.5" title="<?= htmlspecialchars($row['catatan']); ?>">Catatan: "<?= htmlspecialchars($row['catatan']); ?>"</span>
                                <?php } ?>
                            </td>

                            <!-- Total & Pembayaran -->
                            <td class="p-4">
                                <span class="font-bold text-emerald-400 block">Rp <?= number_format($row['total'], 0, ',', '.'); ?></span>
                                <span class="text-[9px] text-gray-500 uppercase font-mono mt-0.5"><?= $row['metode_pembayaran']; ?></span>
                            </td>

                            <!-- Bukti Bayar -->
                            <td class="p-4">
                                <?php if (!empty($row['bukti_bayar'])) { ?>
                                    <a href="../<?= $row['bukti_bayar']; ?>" target="_blank" class="inline-flex items-center text-[10px] text-indigo-400 hover:underline bg-indigo-500/10 px-2 py-1 rounded-md">
                                        <i class="fa-regular fa-image mr-1"></i> Lihat Bukti
                                    </a>
                                <?php } else { ?>
                                    <span class="text-gray-600 italic">Belum diunggah</span>
                                <?php } ?>
                            </td>

                            <!-- Status -->
                            <td class="p-4">
                                <span class="px-2.5 py-1 text-[9px] font-bold rounded-full uppercase tracking-wider <?= $badgeColor ?>">
                                    <?= $row['status']; ?>
                                </span>
                                <span class="block text-[9px] text-gray-500 mt-1.5"><?= date('d/m/y H:i', strtotime($row['tanggal'])); ?></span>
                            </td>

                            <!-- Aksi Management -->
                            <td class="p-4 text-center">
                                <?php if ($status == 'pending') { ?>
                                    <div class="flex justify-center space-x-1.5">
                                        <a href="transaksi.php?id=<?= $row['id_trx']; ?>&action=success" 
                                           class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-2.5 py-1.5 rounded-lg transition text-[10px] flex items-center">
                                            <i class="fa-solid fa-check mr-1"></i> Setuju
                                        </a>
                                        <a href="transaksi.php?id=<?= $row['id_trx']; ?>&action=failed" 
                                           class="bg-red-600 hover:bg-red-700 text-white font-bold px-2.5 py-1.5 rounded-lg transition text-[10px] flex items-center">
                                            <i class="fa-solid fa-xmark mr-1"></i> Tolak
                                        </a>
                                    </div>
                                <?php } else { ?>
                                    <span class="text-gray-550 italic text-[10px]">Verifikasi Selesai</span>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php 
                            }
                        } else { 
                        ?>
                        <tr>
                            <td colspan="8" class="p-8 text-center text-gray-500">Belum ada invoice pembelian terdaftar.</td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

</body>
</html>