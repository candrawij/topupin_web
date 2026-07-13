<?php
session_start();
include "config/koneksi.php";

/** @var mysqli $conn */

// Tangkap filter
$cat_filter = isset($_GET['cat']) ? mysqli_real_escape_string($conn, $_GET['cat']) : '';
$search_query = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : '';
$game_filter = isset($_GET['game']) ? mysqli_real_escape_string($conn, $_GET['game']) : '';

// Bangun query
$where_clauses = [];
if (!empty($cat_filter)) {
    $where_clauses[] = "p.kategori = '$cat_filter'";
}
if (!empty($search_query)) {
    $where_clauses[] = "(p.nama_produk LIKE '%$search_query%' OR p.nama_game LIKE '%$search_query%')";
}
if (!empty($game_filter)) {
    $where_clauses[] = "p.nama_game = '$game_filter'";
}

$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
}

// Eksekusi query produk dengan JOIN ke tabel user untuk info penjual
$products = mysqli_query($conn, "
    SELECT p.*, u.nama as nama_penjual 
    FROM produk p 
    LEFT JOIN user u ON p.id_user = u.id_user
    $where_sql 
    ORDER BY p.id_produk DESC
");

// Ambil semua daftar game unik untuk filter sidebar/dropdown
$all_games = mysqli_query($conn, "SELECT DISTINCT nama_game FROM produk ORDER BY nama_game ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalog Store - TopUpIn</title>
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
            
            <div class="hidden md:flex items-center space-x-6">
                <a href="index.php" class="text-gray-400 font-medium hover:text-indigo-400 transition"><i class="fa-solid fa-house mr-1"></i> Home</a>
                <a href="catalog.php" class="text-white font-medium hover:text-indigo-400 transition"><i class="fa-solid fa-store mr-1"></i> Catalog</a>
                <a href="riwayat.php" class="text-gray-400 font-medium hover:text-indigo-400 transition"><i class="fa-solid fa-receipt mr-1"></i> Lacak Pesanan</a>
            </div>

            <div class="flex items-center space-x-3">
                <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) { ?>
                    <!-- Dropdown/Link ke Dashboard Penjual -->
                    <a href="seller/dashboard.php" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs md:text-sm font-semibold px-4 py-2 rounded-xl transition flex items-center shadow-lg shadow-indigo-600/20">
                        <i class="fa-solid fa-shop mr-1.5"></i> Seller Panel
                    </a>
                <?php } else { ?>
                    <a href="login.php" class="text-xs md:text-sm text-gray-300 hover:text-white font-semibold px-3 py-2 rounded-xl transition">
                        Login
                    </a>
                <?php } ?>
            </div>
        </div>
    </nav>

    <!-- Header & Search -->
    <section class="py-8 bg-gray-900/40 border-b border-gray-800/50">
        <div class="container mx-auto px-6 max-w-6xl">
            <h1 class="text-2xl md:text-3xl font-bold text-white mb-2"><i class="fa-solid fa-store text-indigo-400 mr-2"></i> Catalog Store</h1>
            <p class="text-xs text-gray-400 mb-6">Temukan Diamond, Akun, dan Item Game favoritmu dengan harga terbaik.</p>
            
            <!-- Search & Filter Form -->
            <form action="catalog.php" method="GET" class="flex flex-col md:flex-row gap-4 items-center">
                <!-- Search input -->
                <div class="relative w-full md:flex-1">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-500">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" name="q" value="<?= htmlspecialchars($search_query) ?>" placeholder="Cari Game, Akun atau Item..."
                        class="w-full bg-gray-950 border border-gray-880 rounded-2xl pl-11 pr-4 py-3.5 text-sm text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                </div>
                
                <!-- Game select filter -->
                <div class="w-full md:w-64">
                    <select name="game" class="w-full bg-gray-950 border border-gray-880 rounded-2xl px-4 py-3.5 text-sm text-gray-300 focus:outline-none focus:border-indigo-500 transition">
                        <option value="">Semua Game</option>
                        <?php while ($g = mysqli_fetch_assoc($all_games)) { ?>
                        <option value="<?= htmlspecialchars($g['nama_game']) ?>" <?= $game_filter == $g['nama_game'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($g['nama_game']) ?>
                        </option>
                        <?php } ?>
                    </select>
                </div>

                <!-- Hidden category preserved if set in header links -->
                <?php if (!empty($cat_filter)) { ?>
                    <input type="hidden" name="cat" value="<?= htmlspecialchars($cat_filter) ?>">
                <?php } ?>

                <button type="submit" class="w-full md:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-3.5 rounded-2xl transition">
                    Cari
                </button>
                <?php if (!empty($search_query) || !empty($game_filter) || !empty($cat_filter)) { ?>
                    <a href="catalog.php" class="w-full md:w-auto text-center border border-gray-880 hover:bg-gray-850 px-6 py-3.5 rounded-2xl text-xs font-semibold text-gray-400 hover:text-white transition">
                        Reset
                    </a>
                <?php } ?>
            </form>
        </div>
    </section>

    <!-- Catalog Grid -->
    <main class="container mx-auto px-6 py-10 max-w-6xl flex-grow">
        <!-- Tabs Kategori -->
        <div class="flex border-b border-gray-800 mb-8 overflow-x-auto space-x-2 pb-2">
            <a href="catalog.php?cat=<?= isset($_GET['game']) ? '&game='.urlencode($game_filter) : '' ?><?= isset($_GET['q']) ? '&q='.urlencode($search_query) : '' ?>" 
               class="px-5 py-2.5 rounded-xl text-sm font-semibold transition <?= empty($cat_filter) ? 'bg-indigo-600 text-white shadow-lg' : 'text-gray-400 hover:text-white hover:bg-gray-900/30' ?>">
                Semua Kategori
            </a>
            <a href="catalog.php?cat=topup<?= isset($_GET['game']) ? '&game='.urlencode($game_filter) : '' ?><?= isset($_GET['q']) ? '&q='.urlencode($search_query) : '' ?>" 
               class="px-5 py-2.5 rounded-xl text-sm font-semibold transition <?= $cat_filter == 'topup' ? 'bg-indigo-600 text-white shadow-lg' : 'text-gray-400 hover:text-white hover:bg-gray-900/30' ?>">
                <i class="fa-solid fa-bolt text-yellow-500 mr-1.5"></i> Top Up
            </a>
            <a href="catalog.php?cat=akun<?= isset($_GET['game']) ? '&game='.urlencode($game_filter) : '' ?><?= isset($_GET['q']) ? '&q='.urlencode($search_query) : '' ?>" 
               class="px-5 py-2.5 rounded-xl text-sm font-semibold transition <?= $cat_filter == 'akun' ? 'bg-indigo-600 text-white shadow-lg' : 'text-gray-400 hover:text-white hover:bg-gray-900/30' ?>">
                <i class="fa-solid fa-shield-halved text-purple-400 mr-1.5"></i> Akun Game
            </a>
            <a href="catalog.php?cat=item<?= isset($_GET['game']) ? '&game='.urlencode($game_filter) : '' ?><?= isset($_GET['q']) ? '&q='.urlencode($search_query) : '' ?>" 
               class="px-5 py-2.5 rounded-xl text-sm font-semibold transition <?= $cat_filter == 'item' ? 'bg-indigo-600 text-white shadow-lg' : 'text-gray-400 hover:text-white hover:bg-gray-900/30' ?>">
                <i class="fa-solid fa-gift text-pink-400 mr-1.5"></i> Item & Voucher
            </a>
        </div>

        <!-- Products Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
            <?php 
            if (mysqli_num_rows($products) > 0) {
                while ($row = mysqli_fetch_assoc($products)) {
                    $badge = "Top Up";
                    $badge_class = "bg-indigo-600/90 text-white";
                    
                    $img = "https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=400&q=80"; // ML
                    
                    if (stripos($row['nama_game'], 'free fire') !== false) {
                        $img = "https://images.unsplash.com/photo-1511512578047-dfb367046420?auto=format&fit=crop&w=400&q=80";
                    } else if (stripos($row['nama_game'], 'pubg') !== false) {
                        $img = "https://images.unsplash.com/photo-1552820728-8b83bb6b773f?auto=format&fit=crop&w=400&q=80";
                    } else if (stripos($row['nama_game'], 'roblox') !== false) {
                        $img = "https://images.unsplash.com/photo-1612287230202-1bf1d85d1bdf?auto=format&fit=crop&w=400&q=80";
                    } else if (stripos($row['nama_game'], 'genshin') !== false) {
                        $img = "https://images.unsplash.com/photo-1538481199705-c710c4e965fc?auto=format&fit=crop&w=400&q=80";
                    }

                    if ($row['kategori'] == 'akun') {
                        $badge = "Akun";
                        $badge_class = "bg-purple-600/90 text-white";
                    } elseif ($row['kategori'] == 'item') {
                        $badge = "Item/Voucher";
                        $badge_class = "bg-pink-600/90 text-white";
                    }
            ?>
            
            <?php 
            $detail_link = "detail.php?id=" . $row['id_produk'];
            if ($row['kategori'] == 'topup') {
                $detail_link = "detail.php?game=" . urlencode($row['nama_game']);
            }
            ?>
            <div class="group glass rounded-2xl overflow-hidden border border-gray-800 hover:border-indigo-500/50 transition duration-300 flex flex-col justify-between">
                <div class="relative aspect-video bg-gray-800 overflow-hidden">
                    <img src="<?= $img ?>" alt="Gambar Produk" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                    <span class="absolute top-3 left-3 text-[10px] font-extrabold px-2.5 py-1 rounded-full uppercase tracking-wider <?= $badge_class ?>">
                        <?= $badge ?>
                    </span>
                    <?php if ($row['kategori'] != 'topup' && $row['stok'] >= 0) { ?>
                        <span class="absolute bottom-3 right-3 bg-gray-900/80 text-[10px] text-gray-300 px-2 py-0.5 rounded border border-gray-800">
                            Stok: <?= $row['stok'] ?>
                        </span>
                    <?php } ?>
                </div>
                
                <div class="p-4 flex-grow flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-center text-[10px] text-indigo-400 uppercase font-semibold">
                            <span><?= htmlspecialchars($row['nama_game']) ?></span>
                            <span class="text-gray-500 lowercase"><i class="fa-solid fa-user-tag mr-0.5"></i> <?= empty($row['nama_penjual']) ? 'official' : htmlspecialchars($row['nama_penjual']) ?></span>
                        </div>
                        <h4 class="font-bold text-sm text-gray-200 mt-1 line-clamp-2 min-h-[40px] group-hover:text-indigo-300 transition"><?= htmlspecialchars($row['nama_produk']) ?></h4>
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-gray-800 flex justify-between items-center">
                        <div>
                            <span class="block text-[10px] text-gray-500 uppercase font-medium">Harga</span>
                            <span class="font-bold text-sm text-emerald-400">Rp <?= number_format($row['harga'], 0, ',', '.') ?></span>
                        </div>
                        <a href="<?= $detail_link ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold px-4 py-2 rounded-xl transition">
                            <?= $row['kategori'] == 'topup' ? 'Top Up' : 'Detail' ?>
                        </a>
                    </div>
                </div>
            </div>
            <?php 
                }
            } else {
            ?>
            <div class="col-span-full py-16 text-center glass rounded-2xl border-dashed border-2 border-gray-850">
                <i class="fa-solid fa-box-open text-4xl text-gray-600 mb-3"></i>
                <p class="text-gray-400">Produk yang Anda cari tidak dapat ditemukan.</p>
            </div>
            <?php } ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-950 border-t border-gray-800 py-6 mt-auto">
        <div class="container mx-auto px-6 text-center text-xs text-gray-600">
            &copy; <?= date('Y') ?> TopUpIn. All rights reserved.
        </div>
    </footer>

    <?php include "components/chat_widget.php"; ?>
</body>
</html>
