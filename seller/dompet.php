<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: ../login.php");
    exit;
}

include "../config/koneksi.php";

/** @var mysqli $conn */
$user_id = intval($_SESSION['user_id']);

// Ambil info saldo penjual saat ini
$user_query = mysqli_query($conn, "SELECT saldo FROM user WHERE id_user = $user_id");
$user_data = mysqli_fetch_assoc($user_query);
$saldo = isset($user_data['saldo']) ? intval($user_data['saldo']) : 0;

// Hitung saldo yang tertahan (senggang waktu 30 hari khusus produk kategori 'akun')
$locked_query = mysqli_query($conn, "
    SELECT SUM(t.total - t.komisi) as total_locked 
    FROM transaksi t
    JOIN produk p ON t.id_produk = p.id_produk
    WHERE p.id_user = $user_id 
      AND p.kategori = 'akun' 
      AND t.status = 'Success' 
      AND t.tanggal >= NOW() - INTERVAL 30 DAY
");
$locked_row = mysqli_fetch_assoc($locked_query);
$locked_balance = isset($locked_row['total_locked']) ? intval($locked_row['total_locked']) : 0;

// Saldo yang aman ditarik (tersedia)
$available_balance = max(0, $saldo - $locked_balance);

// Ambil riwayat penarikan dana
$riwayat = mysqli_query($conn, "SELECT * FROM penarikan_dana WHERE id_user = $user_id ORDER BY tanggal DESC");

// Hitung pending transaksi untuk badge di navbar
$pending_query = mysqli_query($conn, "
    SELECT COUNT(*) as total 
    FROM transaksi t 
    JOIN produk p ON t.id_produk = p.id_produk 
    WHERE p.id_user = $user_id AND t.status='Pending'
");
$pending_row = mysqli_fetch_assoc($pending_query);
$pending_count = isset($pending_row['total']) ? intval($pending_row['total']) : 0;

$message = isset($_GET['success']) ? "Permintaan penarikan dana berhasil dikirim! Menunggu verifikasi admin." : "";
$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : "";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dompet & Penarikan Saldo - Seller Panel</title>
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
                <a href="transaksi.php" class="block text-gray-400 hover:bg-gray-700 hover:text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition flex items-center relative">
                    <i class="fa-solid fa-receipt w-5 mr-2"></i> Penjualan Saya
                    <?php if ($pending_count > 0) { ?>
                        <span class="absolute right-3 bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full animate-pulse">
                            <?= $pending_count ?>
                        </span>
                    <?php } ?>
                </a>
                <a href="dompet.php" class="block bg-indigo-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold flex items-center">
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
                <h1 class="text-2xl font-bold text-white">Dompet & Tarik Saldo</h1>
                <p class="text-gray-400 text-xs mt-1">Kelola pendapatan toko game Anda dan ajukan penarikan dana ke rekening Anda.</p>
            </div>
            <a href="dashboard.php" class="bg-gray-800 hover:bg-gray-700 border border-gray-700 px-4 py-2 rounded-xl text-xs font-semibold text-gray-300 transition">
                &larr; Dashboard
            </a>
        </header>

        <!-- Message Alerts -->
        <?php if (!empty($message)) { ?>
            <div class="bg-green-500/10 border border-green-500/20 text-green-400 text-xs p-4 rounded-2xl text-center">
                <i class="fa-solid fa-check-double mr-1.5"></i> <?= $message ?>
            </div>
        <?php } ?>
        <?php if (!empty($error)) { ?>
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 text-xs p-4 rounded-2xl text-center">
                <i class="fa-solid fa-circle-exclamation mr-1.5"></i> <?= $error ?>
            </div>
        <?php } ?>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-8">
            
            <!-- KOLOM KIRI (Informasi Saldo & Formulir Penarikan) -->
            <div class="md:col-span-2 space-y-6">
                <!-- Info Saldo -->
                <div class="bg-gradient-to-br from-indigo-900/40 to-purple-950/20 p-6 rounded-3xl border border-indigo-500/20 shadow-xl text-center relative overflow-hidden space-y-4">
                    <div class="absolute -top-10 -right-10 w-32 h-32 bg-indigo-500/10 rounded-full blur-2xl"></div>
                    <div>
                        <span class="text-[10px] text-indigo-400 uppercase tracking-widest font-bold block mb-1">Saldo Dompet Saya</span>
                        <h2 class="text-3xl font-black text-white">Rp <?= number_format($saldo, 0, ',', '.') ?></h2>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 pt-4 border-t border-indigo-500/15">
                        <div class="text-left">
                            <span class="text-[9px] text-gray-500 uppercase block font-semibold">Tersedia Ditarik</span>
                            <span class="text-sm font-bold text-emerald-450">Rp <?= number_format($available_balance, 0, ',', '.') ?></span>
                        </div>
                        <div class="text-right">
                            <span class="text-[9px] text-gray-500 uppercase block font-semibold">Tertahan (Akun 30 Hr)</span>
                            <span class="text-sm font-bold text-amber-500">Rp <?= number_format($locked_balance, 0, ',', '.') ?></span>
                        </div>
                    </div>
                    
                    <span class="text-[9px] text-gray-500 block pt-1 leading-normal text-left">*Dana penjualan kategori 'Akun Game' ditahan 30 hari sejak disetujui untuk mencegah penipuan / hackback sepihak.</span>
                </div>

                <!-- Form Penarikan -->
                <div class="bg-gray-800 p-6 rounded-3xl border border-gray-700 shadow-xl space-y-4">
                    <h3 class="font-bold text-white text-sm border-b border-gray-700 pb-3"><i class="fa-solid fa-money-bill-transfer text-indigo-400 mr-1.5"></i> Ajukan Pencairan Dana</h3>
                    
                    <form action="proses_tarik.php" method="POST" class="space-y-4">
                        <!-- Nominal Pencairan -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase">Jumlah Penarikan (Rp)</label>
                            <input type="number" name="jumlah" placeholder="Minimal Rp 10.000" min="10000" max="<?= $available_balance ?>" required
                                class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
                        </div>

                        <!-- Bank Tujuan -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase">Bank / E-Wallet Tujuan</label>
                            <select name="bank_tujuan" required
                                class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
                                <option value="Bank BCA">Bank BCA</option>
                                <option value="Bank Mandiri">Bank Mandiri</option>
                                <option value="Bank BRI">Bank BRI</option>
                                <option value="Bank BNI">Bank BNI</option>
                                <option value="DANA">E-Wallet DANA</option>
                                <option value="GoPay">E-Wallet GoPay</option>
                                <option value="OVO">E-Wallet OVO</option>
                            </select>
                        </div>

                        <!-- No Rekening -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase">Nomor Rekening / No HP Akun</label>
                            <input type="text" name="no_rekening" placeholder="Masukkan No. Rekening atau No HP E-wallet" required
                                class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
                        </div>

                        <!-- Atas Nama -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase">Atas Nama Penerima</label>
                            <input type="text" name="atas_nama" placeholder="Atas nama di buku tabungan/akun" required
                                class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
                        </div>

                        <button type="submit" <?= $available_balance < 10000 ? 'disabled' : '' ?>
                                class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-700 disabled:text-gray-500 text-white font-bold py-3.5 rounded-xl text-xs transition uppercase shadow-lg shadow-indigo-600/20">
                            <?= $available_balance < 10000 ? 'Saldo Tersedia Kurang (Min. Rp 10rb)' : 'Ajukan Tarik Saldo' ?>
                        </button>
                    </form>
                </div>
            </div>

            <!-- KOLOM KANAN (Riwayat Penarikan Dana) -->
            <div class="md:col-span-3">
                <div class="bg-gray-800 rounded-3xl border border-gray-700 shadow-xl overflow-hidden h-full flex flex-col">
                    <div class="p-6 border-b border-gray-700 bg-gray-900/20">
                        <h3 class="font-bold text-white text-sm"><i class="fa-solid fa-list-check text-indigo-400 mr-1.5"></i> Riwayat Pencairan Dana</h3>
                    </div>

                    <div class="overflow-x-auto flex-grow">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-905 text-indigo-400 text-[10px] uppercase tracking-wider">
                                    <th class="p-4 font-semibold">Tujuan Transfer</th>
                                    <th class="p-4 font-semibold">Jumlah</th>
                                    <th class="p-4 font-semibold text-center">Status</th>
                                    <th class="p-4 font-semibold text-right">Tanggal Pengajuan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700 text-xs">
                                <?php 
                                if (mysqli_num_rows($riwayat) > 0) {
                                    while ($row = mysqli_fetch_assoc($riwayat)) {
                                        $status = strtolower($row['status']);
                                        $status_class = "bg-yellow-500/10 text-yellow-500 border border-yellow-500/20";
                                        if ($status == 'success') $status_class = "bg-green-500/10 text-green-500 border border-green-500/20";
                                        if ($status == 'failed') $status_class = "bg-red-500/10 text-red-500 border border-red-500/20";
                                ?>
                                <tr class="hover:bg-gray-750/30 transition">
                                    <td class="p-4">
                                        <span class="font-semibold text-white block"><?= htmlspecialchars($row['bank_tujuan']) ?></span>
                                        <span class="text-[10px] text-gray-400 block mt-0.5"><?= htmlspecialchars($row['no_rekening']) ?> a/n <?= htmlspecialchars($row['atas_nama']) ?></span>
                                    </td>
                                    <td class="p-4 font-bold text-emerald-450">
                                        Rp <?= number_format($row['jumlah'], 0, ',', '.') ?>
                                    </td>
                                    <td class="p-4 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider <?= $status_class ?>">
                                            <?= $row['status'] ?>
                                        </span>
                                    </td>
                                    <td class="p-4 text-right text-gray-500 font-mono text-[10px]">
                                        <?= date('d M Y H:i', strtotime($row['tanggal'])) ?>
                                    </td>
                                </tr>
                                <?php 
                                    }
                                } else {
                                ?>
                                <tr>
                                    <td colspan="4" class="p-12 text-center text-gray-600">
                                        <i class="fa-solid fa-money-bill-wave text-3xl mb-2 text-gray-700"></i>
                                        <p class="text-sm">Belum ada riwayat penarikan dana.</p>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

    </main>

    <?php include "../components/chat_widget.php"; ?>
</body>
</html>
