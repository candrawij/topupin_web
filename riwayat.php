<?php
include "config/koneksi.php";
include "config/telegram_helper.php";

/** @var mysqli $conn */

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$transactions = null;

if (!empty($search)) {
    // Cari berdasarkan kontak pembeli ATAU invoice ID
    $query_str = "
        SELECT t.*, p.nama_produk, p.nama_game
        FROM transaksi t
        JOIN produk p ON t.id_produk = p.id_produk
        WHERE t.kontak_pembeli = '$search' OR t.id_trx = '" . intval($search) . "'
        ORDER BY t.tanggal DESC
    ";
    $transactions = mysqli_query($conn, $query_str);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lacak Pesanan - TopUpIn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            font-family: 'Poppins', sans-serif;
            background-color: #0b0f19;
        }
        .glass {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
    </style>
</head>
<body class="text-gray-200 min-h-screen flex flex-col">

    <!-- Navbar -->
    <nav class="bg-gray-900/80 border-b border-gray-800 p-4 sticky top-0 z-50 backdrop-blur-md">
        <div class="container mx-auto flex justify-between items-center px-4">
            <a href="index.php" class="flex items-center space-x-2">
                <i class="fa-solid fa-gamepad text-2xl text-indigo-500"></i>
                <span class="text-xl font-extrabold tracking-wider text-white">TopUp<span class="text-indigo-400">In</span></span>
            </a>
            
            <div class="flex items-center space-x-6">
                <a href="index.php" class="text-gray-450 font-medium hover:text-indigo-400 transition"><i class="fa-solid fa-house mr-1"></i> Home</a>
                <a href="catalog.php" class="text-gray-450 font-medium hover:text-indigo-400 transition"><i class="fa-solid fa-store mr-1"></i> Catalog</a>
                <a href="riwayat.php" class="text-white font-medium hover:text-indigo-400 transition"><i class="fa-solid fa-receipt mr-1"></i> Lacak Pesanan</a>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="container mx-auto px-6 py-10 max-w-4xl flex-grow space-y-8">
        
        <!-- Search bar layout -->
        <div class="glass p-8 rounded-3xl border border-gray-800 shadow-xl text-center space-y-6">
            <div>
                <span class="bg-indigo-500/10 border border-indigo-500/30 text-indigo-400 text-xs font-bold px-4 py-1.5 rounded-full uppercase tracking-wider mb-2 inline-block">
                    🔍 Status Pembelian
                </span>
                <h2 class="text-2xl font-bold text-white">Lacak & Cari Pesanan</h2>
                <p class="text-xs text-gray-500 max-w-md mx-auto mt-1">Masukkan Nomor WhatsApp, Email, atau ID Invoice pembelian Anda untuk melacak status pesanan secara real-time.</p>
            </div>

            <form action="riwayat.php" method="GET" class="flex flex-col sm:flex-row gap-3 max-w-lg mx-auto">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Masukkan No. WhatsApp / Email / Invoice ID..." required
                    class="w-full bg-gray-950 border border-gray-850 rounded-2xl px-5 py-3.5 text-sm text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition text-center sm:text-left">
                <button type="submit" class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-3.5 rounded-2xl transition shadow-lg shadow-indigo-600/20">
                    Cari Pesanan
                </button>
            </form>
        </div>

        <?php if (!empty($search)) { ?>
            <!-- Results Section -->
            <div class="glass rounded-3xl border border-gray-800 shadow-xl overflow-hidden">
                <div class="p-6 border-b border-gray-850 flex justify-between items-center bg-gray-900/30">
                    <h3 class="font-bold text-white text-sm"><i class="fa-solid fa-list-check text-indigo-400 mr-1.5"></i> Hasil Pencarian Riwayat</h3>
                    <span class="text-xs text-gray-500">Kata Kunci: "<?= htmlspecialchars($search) ?>"</span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-950/60 text-indigo-400 text-[10px] uppercase tracking-wider">
                                <th class="p-4 font-semibold">Invoice</th>
                                <th class="p-4 font-semibold">Game / Item</th>
                                <th class="p-4 font-semibold">Tujuan / Catatan</th>
                                <th class="p-4 font-semibold">Total</th>
                                <th class="p-4 font-semibold">Status</th>
                                <th class="p-4 font-semibold">Tanggal</th>
                                <th class="p-4 font-semibold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-850 text-xs">
                            <?php 
                            if ($transactions && mysqli_num_rows($transactions) > 0) {
                                while ($row = mysqli_fetch_assoc($transactions)) {
                                    $status = strtolower($row['status']);
                                    $status_class = "bg-yellow-500/10 text-yellow-500 border border-yellow-500/20";
                                    if ($status == 'success') $status_class = "bg-green-500/10 text-green-500 border border-green-500/20";
                                    if ($status == 'failed') $status_class = "bg-red-500/10 text-red-500 border border-red-500/20";
                            ?>
                            <tr class="hover:bg-gray-850/30 transition">
                                <td class="p-4 font-mono text-gray-500">#<?= $row['id_trx'] ?></td>
                                <td class="p-4">
                                    <span class="font-semibold text-white"><?= htmlspecialchars($row['nama_game']) ?></span>
                                    <span class="block text-[10px] text-gray-400 mt-0.5"><?= htmlspecialchars($row['nama_produk']) ?></span>
                                </td>
                                <td class="p-4 text-gray-300 max-w-[180px] truncate">
                                    <?php if (!empty($row['user_game'])) { ?>
                                        <span class="font-bold text-indigo-400 font-mono"><?= htmlspecialchars($row['user_game']) ?></span>
                                        <?= !empty($row['server_game']) ? "<span class='text-[10px] text-gray-500'>(".htmlspecialchars($row['server_game']).")</span>" : "" ?>
                                    <?php } else { ?>
                                        <span class="italic text-[10px] text-gray-500">Kirim ke Kontak</span>
                                    <?php } ?>
                                </td>
                                <td class="p-4 font-semibold text-emerald-400">Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                                <td class="p-4">
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider <?= $status_class ?>">
                                        <?= $row['status'] ?>
                                    </span>
                                </td>
                                <td class="p-4 text-[10px] text-gray-500"><?= $row['tanggal'] ?></td>
                                <td class="p-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a href="pembayaran.php?invoice=<?= $row['id_trx'] ?>" 
                                           class="bg-indigo-600/10 hover:bg-indigo-600 text-indigo-400 hover:text-white px-3 py-1.5 rounded-lg transition font-semibold text-[10px]">
                                            Detail
                                        </a>
                                        <a href="<?= buildTrxTelegramLink('TRX-' . $row['id_trx']) ?>" 
                                           target="_blank"
                                           title="Tanya CS di Telegram"
                                           class="bg-sky-600/10 hover:bg-sky-650 text-sky-400 hover:text-white p-1.5 rounded-lg transition text-[10px]">
                                            <i class="fa-brands fa-telegram text-sm"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                                }
                            } else {
                            ?>
                            <tr>
                                <td colspan="7" class="p-8 text-center text-gray-500">
                                    <i class="fa-solid fa-folder-open text-3xl text-gray-700 mb-2"></i>
                                    <p class="text-sm">Tidak ada transaksi ditemukan untuk pencarian ini.</p>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php } ?>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-950 border-t border-gray-800 py-6 mt-12">
        <div class="container mx-auto px-6 text-center text-xs text-gray-600">
            &copy; <?= date('Y') ?> TopUpIn. All rights reserved.
        </div>
    </footer>

    <?php include "components/chat_widget.php"; ?>
</body>
</html>
