<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

include "../config/koneksi.php"; 

/** @var mysqli $conn */

// Mengambil semua produk beserta nama pemilik (seller)
$data = mysqli_query($conn, "
    SELECT p.*, u.nama as nama_pemilik 
    FROM produk p 
    LEFT JOIN user u ON p.id_user = u.id_user 
    ORDER BY p.kategori ASC, p.nama_game ASC
");

// Hitung pending transaction untuk badge navbar
$pending_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM transaksi WHERE status='Pending'"));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - Admin Panel</title>
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
                <a href="produk.php" class="block bg-indigo-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold flex items-center">
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
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
            <div>
                <h1 class="text-2xl font-bold text-white">Daftar Produk Game</h1>
                <p class="text-gray-400 text-xs mt-1">Kelola Top-Up, Akun, dan Item Game beserta spesifikasi & harga jual.</p>
            </div>
            <a href="tambah_produk.php" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs md:text-sm font-bold px-5 py-3 rounded-xl shadow-lg shadow-indigo-600/20 transition flex items-center">
                <i class="fa-solid fa-plus mr-2"></i> Tambah Produk Baru
            </a>
        </div>

        <!-- Table Container -->
        <div class="bg-gray-800 rounded-2xl shadow-xl border border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-750 text-indigo-400 text-[10px] uppercase tracking-wider">
                            <th class="p-4 font-semibold text-center w-16">Gambar</th>
                            <th class="p-4 font-semibold">Kategori</th>
                            <th class="p-4 font-semibold">Pemilik / Seller</th>
                            <th class="p-4 font-semibold">Nama Game</th>
                            <th class="p-4 font-semibold">Nama Varian / Produk</th>
                            <th class="p-4 font-semibold">Harga Jual</th>
                            <th class="p-4 font-semibold">Stok</th>
                            <th class="p-4 font-semibold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700 text-xs">
                        <?php 
                        if (mysqli_num_rows($data) > 0) {
                            while($row = mysqli_fetch_assoc($data)){ 
                                $badge_class = "bg-indigo-500/10 text-indigo-400 border border-indigo-500/20";
                                if ($row['kategori'] == 'akun') $badge_class = "bg-purple-500/10 text-purple-400 border border-purple-500/20";
                                if ($row['kategori'] == 'item') $badge_class = "bg-pink-500/10 text-pink-400 border border-pink-500/20";

                                $img_src = "../uploads/" . $row['gambar'];
                                if (empty($row['gambar'])) {
                                    $img_src = "https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=100&q=80";
                                } elseif (filter_var($row['gambar'], FILTER_VALIDATE_URL) || stripos($row['gambar'], 'http') === 0) {
                                    $img_src = $row['gambar'];
                                }
                        ?>
                        <tr class="hover:bg-gray-750/30 transition">
                            <td class="p-3 text-center">
                                <img src="<?= $img_src ?>" alt="Prod Img" class="w-12 h-8 object-cover rounded-lg border border-gray-700 mx-auto">
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 text-[9px] font-bold rounded-full uppercase tracking-wider <?= $badge_class ?>">
                                    <?= $row['kategori'] ?>
                                </span>
                            </td>
                            <!-- Pemilik / Seller -->
                            <td class="p-4 text-gray-300 font-semibold">
                                <?= empty($row['nama_pemilik']) ? '<span class="text-indigo-400 font-bold uppercase tracking-wider"><i class="fa-solid fa-circle-check"></i> Official</span>' : htmlspecialchars($row['nama_pemilik']); ?>
                            </td>
                            <td class="p-4 font-semibold text-white"><?= htmlspecialchars($row['nama_game']); ?></td>
                            <td class="p-4 font-medium text-gray-300">
                                <?= htmlspecialchars($row['nama_produk']); ?>
                                <?php if ($row['kategori'] == 'topup' && $row['nominal'] > 0) { ?>
                                    <span class="text-[10px] text-gray-500 block font-mono mt-0.5">(Varian Nominal: <?= number_format($row['nominal']) ?>)</span>
                                <?php } ?>
                            </td>
                            <td class="p-4 font-bold text-emerald-400">Rp <?= number_format($row['harga'], 0, ',', '.'); ?></td>
                            <td class="p-4 font-medium text-gray-400">
                                <?= $row['stok'] == -1 ? '<span class="text-gray-500">Unlimited</span>' : number_format($row['stok']) ?>
                            </td>
                            <td class="p-4 text-center space-x-2">
                                <a href="edit_produk.php?id=<?= $row['id_produk']; ?>" class="inline-block text-[10px] bg-gray-700 hover:bg-gray-600 text-indigo-400 font-semibold px-3 py-1.5 rounded-lg transition">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </a>
                                <a href="hapus_produk.php?id=<?= $row['id_produk']; ?>" onclick="return confirm('Yakin ingin menghapus produk ini?')" class="inline-block text-[10px] bg-red-500/10 hover:bg-red-500 text-red-400 hover:text-white font-semibold px-3 py-1.5 rounded-lg transition">
                                    <i class="fa-solid fa-trash"></i> Hapus
                                </a>
                            </td>
                        </tr>
                        <?php 
                            }
                        } else {
                        ?>
                        <tr>
                            <td colspan="8" class="p-8 text-center text-gray-500">Belum ada rekaman produk dalam database.</td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

</body>
</html>