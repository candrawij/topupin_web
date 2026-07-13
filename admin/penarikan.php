<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

include "../config/koneksi.php"; 

/** @var mysqli $conn */

$message = "";
$error = "";

// Proses Aksi Admin (Setujui / Tolak Penarikan Dana)
if (isset($_GET['id']) && isset($_GET['action'])) {
    $id_action = intval($_GET['id']);
    $action = $_GET['action'];
    
    // Ambil data penarikan terlebih dahulu untuk memastikan statusnya masih Pending
    $query_check = mysqli_query($conn, "SELECT * FROM penarikan_dana WHERE id_penarikan = $id_action");
    $wd = mysqli_fetch_assoc($query_check);
    
    if ($wd && $wd['status'] == 'Pending') {
        $seller_id = intval($wd['id_user']);
        $jumlah = intval($wd['jumlah']);
        
        mysqli_begin_transaction($conn);
        
        try {
            if ($action == 'approve') {
                // Setujui penarikan
                mysqli_query($conn, "UPDATE penarikan_dana SET status = 'Success' WHERE id_penarikan = $id_action");
                $message = "Penarikan dana berhasil disetujui!";
            } elseif ($action == 'reject') {
                // Tolak penarikan & kembalikan (refund) saldo ke dompet penjual
                mysqli_query($conn, "UPDATE user SET saldo = saldo + $jumlah WHERE id_user = $seller_id");
                mysqli_query($conn, "UPDATE penarikan_dana SET status = 'Failed' WHERE id_penarikan = $id_action");
                $message = "Penarikan dana ditolak. Saldo dikembalikan ke dompet penjual.";
            }
            
            mysqli_commit($conn);
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = "Gagal memperbarui status penarikan.";
        }
    }
}

// Mengambil seluruh riwayat penarikan dana beserta nama pemohon (seller)
$data = mysqli_query($conn, "
    SELECT p.*, u.nama, u.email 
    FROM penarikan_dana p 
    JOIN user u ON p.id_user = u.id_user 
    ORDER BY p.tanggal DESC
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
    <title>Manajemen Penarikan Dana - Admin Panel</title>
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
                <a href="transaksi.php" class="block text-gray-400 hover:bg-gray-700 hover:text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition flex items-center relative">
                    <i class="fa-solid fa-receipt w-5 mr-2"></i> Semua Transaksi
                    <?php if ($pending_count > 0) { ?>
                        <span class="absolute right-3 bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full animate-pulse">
                            <?= $pending_count ?>
                        </span>
                    <?php } ?>
                </a>
                <a href="penarikan.php" class="block bg-indigo-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold flex items-center relative">
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
            <h1 class="text-2xl font-bold text-white">Kelola Penarikan Dana Seller</h1>
            <p class="text-gray-400 text-xs mt-1">Verifikasi permintaan penarikan saldo dompet dari penjual pihak ketiga.</p>
        </header>

        <!-- Message Alerts -->
        <?php if (!empty($message)) { ?>
            <div class="bg-green-500/10 border border-green-500/20 text-green-400 text-xs p-3.5 rounded-xl text-center">
                <i class="fa-solid fa-circle-check mr-1.5"></i> <?= $message ?>
            </div>
        <?php } ?>
        <?php if (!empty($error)) { ?>
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 text-xs p-3.5 rounded-xl text-center">
                <i class="fa-solid fa-circle-exclamation mr-1.5"></i> <?= $error ?>
            </div>
        <?php } ?>

        <!-- Withdrawal Requests Table -->
        <div class="bg-gray-800 rounded-2xl shadow-xl border border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-750 text-indigo-400 text-[10px] uppercase tracking-wider">
                            <th class="p-4 font-semibold">Nama Pemohon (Seller)</th>
                            <th class="p-4 font-semibold">Tujuan Rekening</th>
                            <th class="p-4 font-semibold">Jumlah Pencairan</th>
                            <th class="p-4 font-semibold">Status</th>
                            <th class="p-4 font-semibold">Tanggal Pengajuan</th>
                            <th class="p-4 font-semibold text-center font-bold">Verifikasi Transfer</th>
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
                            <!-- Nama Seller -->
                            <td class="p-4">
                                <span class="font-semibold text-white block"><?= htmlspecialchars($row['nama']); ?></span>
                                <span class="text-[10px] text-gray-500 block mt-0.5"><?= htmlspecialchars($row['email']); ?></span>
                            </td>

                            <!-- Rekening Tujuan -->
                            <td class="p-4 font-semibold text-gray-300">
                                <span class="block text-white"><?= htmlspecialchars($row['bank_tujuan']); ?></span>
                                <span class="text-[10px] text-gray-400 block font-mono mt-0.5">Rek: <?= htmlspecialchars($row['no_rekening']); ?> a/n <?= htmlspecialchars($row['atas_nama']); ?></span>
                            </td>

                            <!-- Jumlah -->
                            <td class="p-4 font-bold text-emerald-400">
                                Rp <?= number_format($row['jumlah'], 0, ',', '.'); ?>
                            </td>

                            <!-- Status -->
                            <td class="p-4">
                                <span class="px-2.5 py-1 text-[9px] font-bold rounded-full uppercase tracking-wider <?= $badgeColor ?>">
                                    <?= $row['status']; ?>
                                </span>
                            </td>

                            <!-- Tanggal -->
                            <td class="p-4 text-gray-550 font-mono text-[10px]">
                                <?= date('d M Y H:i', strtotime($row['tanggal'])); ?>
                            </td>

                            <!-- Aksi Approval -->
                            <td class="p-4 text-center">
                                <?php if ($status == 'pending') { ?>
                                    <div class="flex justify-center space-x-1.5">
                                        <a href="penarikan.php?id=<?= $row['id_penarikan']; ?>&action=approve" 
                                           onclick="return confirm('Apakah Anda yakin sudah mentransfer dana pencairan sebesar Rp <?= number_format($row['jumlah'], 0, ',', '.') ?> ke <?= htmlspecialchars($row['nama']) ?>?')"
                                           class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-2.5 py-1.5 rounded-lg transition text-[10px] flex items-center">
                                            <i class="fa-solid fa-paper-plane mr-1"></i> Setuju (Sudah Transfer)
                                        </a>
                                        <a href="penarikan.php?id=<?= $row['id_penarikan']; ?>&action=reject" 
                                           onclick="return confirm('Apakah Anda yakin menolak pencairan ini? Saldo akan dikembalikan otomatis ke dompet seller.')"
                                           class="bg-red-600 hover:bg-red-700 text-white font-bold px-2.5 py-1.5 rounded-lg transition text-[10px] flex items-center">
                                            <i class="fa-solid fa-rotate-left mr-1"></i> Tolak & Refund
                                        </a>
                                    </div>
                                <?php } else { ?>
                                    <span class="text-gray-550 italic text-[10px]"><i class="fa-solid fa-circle-check text-green-500 mr-1"></i> Selesai Diproses</span>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php 
                            }
                        } else { 
                        ?>
                        <tr>
                            <td colspan="6" class="p-8 text-center text-gray-500">Belum ada pengajuan penarikan dana dari seller.</td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

</body>
</html>
