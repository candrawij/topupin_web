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

// Mengambil seluruh data transaksi untuk produk milik seller ini
$data = mysqli_query($conn, "
    SELECT t.*, p.nama_produk, p.nama_game, p.kategori 
    FROM transaksi t
    JOIN produk p ON t.id_produk = p.id_produk 
    WHERE p.id_user = $user_id
    ORDER BY t.tanggal DESC
");

// Hitung pending transaksi untuk badge
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
    <title>Penjualan Saya - Seller Panel</title>
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
            <nav class="space-y-2">
                <a href="dashboard.php" class="block text-gray-400 hover:bg-gray-700 hover:text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition flex items-center">
                    <i class="fa-solid fa-chart-line w-5 mr-2"></i> Dashboard
                </a>
                <a href="produk.php" class="block text-gray-400 hover:bg-gray-700 hover:text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition flex items-center">
                    <i class="fa-solid fa-box w-5 mr-2"></i> Kelola Dagangan
                </a>
                <a href="transaksi.php" class="block bg-indigo-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold flex items-center relative">
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
    <main class="flex-grow p-6 md:p-10 space-y-6">
        <header>
            <h1 class="text-2xl font-bold text-white">Laporan Penjualan Saya</h1>
            <p class="text-gray-400 text-xs mt-1">Daftar pesanan masuk untuk produk dagangan Anda. Serah terima dilakukan setelah transaksi berstatus Sukses.</p>
        </header>

        <!-- Transactions Table -->
        <div class="bg-gray-800 rounded-2xl shadow-xl border border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-750 text-indigo-400 text-[10px] uppercase tracking-wider">
                            <th class="p-4 font-semibold w-16">Invoice</th>
                            <th class="p-4 font-semibold">Produk</th>
                            <th class="p-4 font-semibold">Kontak Pembeli</th>
                            <th class="p-4 font-semibold">Catatan Pembeli</th>
                            <th class="p-4 font-semibold">Total Pendapatan</th>
                            <th class="p-4 font-semibold">Status Bayar</th>
                            <th class="p-4 font-semibold text-center">Aksi Serah Terima</th>
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

                            <!-- Kontak Pembeli (Sensitif jika Pending) -->
                            <td class="p-4">
                                <?php if ($status == 'success') { ?>
                                    <span class="text-white font-bold select-all"><?= htmlspecialchars($row['kontak_pembeli']); ?></span>
                                <?php } else { ?>
                                    <span class="text-gray-500 blur-[3px] select-none pointer-events-none"><?= htmlspecialchars($row['kontak_pembeli']); ?></span>
                                    <span class="block text-[9px] text-gray-500 mt-1 italic">(Menunggu verifikasi admin)</span>
                                <?php } ?>
                            </td>

                            <!-- Catatan Pembeli -->
                            <td class="p-4">
                                <?php if (!empty($row['catatan'])) { ?>
                                    <span class="text-gray-300 italic" title="<?= htmlspecialchars($row['catatan']); ?>">"<?= htmlspecialchars($row['catatan']); ?>"</span>
                                <?php } else { ?>
                                    <span class="text-gray-650 italic">Tidak ada catatan</span>
                                <?php } ?>
                            </td>

                            <!-- Total Pendapatan -->
                            <td class="p-4 font-bold text-emerald-400">
                                Rp <?= number_format($row['total'], 0, ',', '.'); ?>
                            </td>

                            <!-- Status Pembayaran -->
                            <td class="p-4">
                                <span class="px-2.5 py-1 text-[9px] font-bold rounded-full uppercase tracking-wider <?= $badgeColor ?>">
                                    <?= $row['status']; ?>
                                </span>
                                <span class="block text-[9px] text-gray-500 mt-1.5"><?= date('d/m H:i', strtotime($row['tanggal'])); ?></span>
                            </td>

                            <!-- Aksi Serah Terima -->
                            <td class="p-4 text-center">
                                <?php if ($status == 'success') { 
                                    // Buka chat WA jika kontak berupa nomor
                                    $kontak = $row['kontak_pembeli'];
                                    $wa_link = "#";
                                    $is_wa = false;
                                    if (preg_match('/^[0-9\-\+\s]+$/', $kontak)) {
                                        $clean_wa = preg_replace('/[^0-9]/', '', $kontak);
                                        // ubah awalan 0 menjadi 62
                                        if (substr($clean_wa, 0, 1) === '0') {
                                            $clean_wa = '62' . substr($clean_wa, 1);
                                        }
                                        $wa_link = "https://wa.me/" . $clean_wa . "?text=Halo%20saya%20penjual%20di%20TopUpIn.%20Berikut%20detail%20kredensial%20produk%20game%20pembelian%20Anda%20(Invoice%20%23" . $row['id_trx'] . ")";
                                        $is_wa = true;
                                    }
                                ?>
                                    <?php if ($is_wa) { ?>
                                        <a href="<?= $wa_link ?>" target="_blank" 
                                           class="inline-flex items-center bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-3 py-1.5 rounded-lg transition text-[10px]">
                                            <i class="fa-brands fa-whatsapp mr-1 text-xs"></i> Hubungi WA
                                        </a>
                                    <?php } else { ?>
                                        <a href="mailto:<?= htmlspecialchars($kontak) ?>?subject=Detail%2520Akun%2520TopUpIn%2520Invoice%2520%23<?= $row['id_trx'] ?>" 
                                           class="inline-flex items-center bg-indigo-650 hover:bg-indigo-700 text-white font-semibold px-3 py-1.5 rounded-lg transition text-[10px]">
                                            <i class="fa-regular fa-envelope mr-1"></i> Hubungi Email
                                        </a>
                                    <?php } ?>
                                <?php } else { ?>
                                    <span class="text-gray-550 italic text-[10px]">Kunci Terkunci</span>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php 
                            }
                        } else { 
                        ?>
                        <tr>
                            <td colspan="7" class="p-8 text-center text-gray-500">Belum ada order masuk untuk produk Anda.</td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <?php include "../components/chat_widget.php"; ?>
</body>
</html>
