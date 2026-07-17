<?php
session_start();
include "config/koneksi.php";

/** @var mysqli $conn */

// Ambil game unik untuk kategori Top Up
$games_topup = mysqli_query($conn, "SELECT DISTINCT nama_game FROM produk WHERE kategori='topup'");

// Ambil akun terbaru yang ready (stok > 0)
$accounts_latest = mysqli_query($conn, "
    SELECT p.*, u.nama as nama_penjual 
    FROM produk p 
    LEFT JOIN user u ON p.id_user = u.id_user 
    WHERE p.kategori='akun' AND p.stok > 0 
    ORDER BY p.id_produk DESC 
    LIMIT 4
");

// Ambil item terbaru (stok > 0)
$items_latest = mysqli_query($conn, "
    SELECT p.*, u.nama as nama_penjual 
    FROM produk p 
    LEFT JOIN user u ON p.id_user = u.id_user 
    WHERE p.kategori='item' AND p.stok > 0 
    ORDER BY p.id_produk DESC 
    LIMIT 4
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TopUpIn - Gaming Marketplace & Top Up Terbaik</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Font Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { 
            font-family: 'Poppins', sans-serif;
            background-color: #0b0f19;
        }
        .glass {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .neon-border:hover {
            box-shadow: 0 0 15px rgba(99, 102, 241, 0.25);
            border-color: rgba(99, 102, 241, 0.5);
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
                <a href="index.php" class="text-white font-medium hover:text-indigo-400 transition"><i class="fa-solid fa-house mr-1"></i> Home</a>
                <a href="catalog.php" class="text-gray-400 font-medium hover:text-indigo-400 transition"><i class="fa-solid fa-store mr-1"></i> Catalog</a>
                <a href="riwayat.php" class="text-gray-400 font-medium hover:text-indigo-400 transition"><i class="fa-solid fa-receipt mr-1"></i> Lacak Pesanan</a>
                <a href="tentang.php" class="text-gray-400 font-medium hover:text-indigo-400 transition"><i class="fa-solid fa-circle-info mr-1"></i> Tentang</a>
                <a href="demo.php" class="flex items-center gap-1.5 bg-indigo-600/20 hover:bg-indigo-600/40 text-indigo-300 font-semibold text-xs px-3 py-1.5 rounded-full border border-indigo-500/30 transition">
                    <i class="fa-solid fa-flask text-xs"></i> Demo Guide
                </a>
            </div>

            <div class="flex items-center space-x-3">
                <a href="riwayat.php" class="md:hidden text-gray-300 hover:text-white px-2 py-1"><i class="fa-solid fa-receipt text-lg"></i></a>
                
                <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) { ?>
                    <!-- Dropdown/Link ke Dashboard Penjual -->
                    <a href="seller/dashboard.php" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs md:text-sm font-semibold px-4 py-2 rounded-xl transition flex items-center shadow-lg shadow-indigo-600/20">
                        <i class="fa-solid fa-shop mr-1.5"></i> Seller Panel
                    </a>
                    <a href="logout.php" class="text-xs md:text-sm text-red-400 hover:underline font-semibold px-2 py-1">
                        Logout
                    </a>
                <?php } else { ?>
                    <a href="login.php" class="text-xs md:text-sm text-gray-300 hover:text-white font-semibold px-3 py-2 rounded-xl transition">
                        Login
                    </a>
                    <a href="register.php" class="bg-indigo-650 hover:bg-indigo-700 text-white text-xs md:text-sm font-semibold px-4 py-2 rounded-xl transition">
                        Register
                    </a>
                <?php } ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="relative overflow-hidden py-16 md:py-24 bg-gradient-to-b from-indigo-950/20 via-gray-900/10 to-transparent">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-indigo-500/10 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="container mx-auto px-6 text-center relative z-10">
            <span class="bg-indigo-500/10 border border-indigo-500/30 text-indigo-400 text-xs font-bold px-4 py-1.5 rounded-full uppercase tracking-wider mb-6 inline-block">
                🎮 Gaming Store & Multi-Vendor Marketplace
            </span>
            <h1 class="text-4xl md:text-6xl font-extrabold text-white mb-6 leading-tight tracking-tight">
                TOP UP GAME & MARKETPLACE <br>
                <span class="bg-clip-text text-transparent bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400">ITEM & AKUN GAME</span>
            </h1>
            <p class="text-gray-400 text-base md:text-lg max-w-xl mx-auto mb-8 font-light">
                Dapatkan Diamond instan otomatis 24 jam, atau beli akun & item game langka langsung dari gamer lain dengan aman.
            </p>
            
            <div class="flex flex-col sm:flex-row justify-center items-center gap-4 max-w-lg mx-auto">
                <a href="catalog.php" class="w-full sm:w-auto bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold px-8 py-3.5 rounded-2xl shadow-xl shadow-indigo-500/25 transition transform active:scale-95 text-center">
                    <i class="fa-solid fa-magnifying-glass mr-2"></i> Jelajahi Catalog
                </a>
                <?php if (!isset($_SESSION['user_logged_in'])) { ?>
                    <a href="register.php" class="w-full sm:w-auto glass hover:bg-gray-800 text-gray-200 hover:text-white font-semibold px-8 py-3.5 rounded-2xl border border-gray-700 transition text-center">
                        <i class="fa-solid fa-shop mr-2"></i> Mulai Jualan
                    </a>
                <?php } else { ?>
                    <a href="seller/dashboard.php" class="w-full sm:w-auto glass hover:bg-gray-800 text-gray-200 hover:text-white font-semibold px-8 py-3.5 rounded-2xl border border-gray-700 transition text-center">
                        <i class="fa-solid fa-box mr-2"></i> Jual Akun/Item
                    </a>
                <?php } ?>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-6 pb-20 max-w-6xl space-y-20 flex-grow">

        <!-- ===== SECTION: FITUR UNGGULAN ===== -->
        <section class="pt-4">
            <div class="text-center mb-10">
                <span class="text-xs font-semibold text-indigo-400 uppercase tracking-widest bg-indigo-500/10 px-4 py-1.5 rounded-full border border-indigo-500/20">Mengapa TopUpIn?</span>
                <h2 class="text-2xl font-bold text-white mt-3">Platform Game Terlengkap & Terpercaya</h2>
                <p class="text-gray-500 text-sm mt-2 max-w-lg mx-auto">Dari top-up resmi, jual-beli akun, hingga item game — semua tersedia dalam satu platform.</p>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <?php
                $features = [
                    ['🔒', 'Rekening Bersama', 'Dana pembeli ditahan platform hingga transaksi diverifikasi. 100% aman dari penipuan.', 'border-indigo-500/20', 'text-indigo-400'],
                    ['⚡', 'Top Up Instan', 'Proses top-up diamond, UC, dan mata uang game lainnya langsung tanpa antri.', 'border-amber-500/20', 'text-amber-400'],
                    ['💬', 'CS 24/7 Real-Time', 'Live chat langsung dengan Customer Service kami kapanpun Anda butuh bantuan.', 'border-emerald-500/20', 'text-emerald-400'],
                    ['🤝', 'Marketplace Seller', 'Jual akun & item game dengan mudah. Saldo otomatis masuk ke dompet seller.', 'border-purple-500/20', 'text-purple-400'],
                ];
                foreach ($features as $f) {
                    echo "<div class='glass rounded-2xl p-5 border {$f[3]} hover:translate-y-[-3px] transition-all duration-300'>";
                    echo "<div class='text-3xl mb-3'>{$f[0]}</div>";
                    echo "<h3 class='font-bold text-white text-sm mb-2'>{$f[1]}</h3>";
                    echo "<p class='text-gray-500 text-xs leading-relaxed'>{$f[2]}</p>";
                    echo "</div>";
                }
                ?>
            </div>
        </section>

        <!-- ===== SECTION: CARA KERJA ===== -->
        <section>
            <div class="text-center mb-10">
                <span class="text-xs font-semibold text-purple-400 uppercase tracking-widest bg-purple-500/10 px-4 py-1.5 rounded-full border border-purple-500/20">Mudah & Cepat</span>
                <h2 class="text-2xl font-bold text-white mt-3">Cara Beli di TopUpIn</h2>
            </div>
            <div class="grid sm:grid-cols-3 gap-4">
                <?php
                $how = [
                    ['01', '🔍', 'Pilih Produk', 'Jelajahi katalog lengkap Top-Up, Akun Game, dan Item Game dari berbagai game populer.'],
                    ['02', '💳', 'Bayar & Checkout', 'Pilih metode pembayaran (QRIS / Transfer BCA) dan selesaikan pembayaran Anda.'],
                    ['03', '✅', 'Pesanan Diproses', 'Tim admin kami memverifikasi dan memproses pesanan Anda. Notifikasi dikirim otomatis!'],
                ];
                foreach ($how as $i => $h) {
                    echo "<div class='glass rounded-2xl p-6 text-center border border-gray-800 relative'>";
                    echo "<div class='absolute top-4 left-4 text-xs font-black text-gray-700'>{$h[0]}</div>";
                    echo "<div class='text-4xl mb-3'>{$h[1]}</div>";
                    echo "<h3 class='font-bold text-white text-sm mb-2'>{$h[2]}</h3>";
                    echo "<p class='text-gray-500 text-xs leading-relaxed'>{$h[3]}</p>";
                    echo "</div>";
                }
                ?>
            </div>
        </section>

        <section>
            <div class="flex justify-between items-end mb-8">
                <div>
                    <h2 class="text-2xl font-bold text-white tracking-wide"><i class="fa-solid fa-bolt text-yellow-500 mr-2"></i> Top Up Instan</h2>
                    <p class="text-gray-400 text-sm mt-1">Isi ulang saldo game favoritmu dalam hitungan detik.</p>
                </div>
                <a href="catalog.php?cat=topup" class="text-indigo-400 hover:text-indigo-300 text-sm font-semibold transition">
                    Lihat Semua &rarr;
                </a>
            </div>
            
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-6">
                <?php 
                if (mysqli_num_rows($games_topup) > 0) {
                    while ($game = mysqli_fetch_assoc($games_topup)) {
                        $game_name = $game['nama_game'];
                        $img_src = "https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=300&q=80"; // ML
                        if (stripos($game_name, 'free fire') !== false) {
                            $img_src = "https://images.unsplash.com/photo-1511512578047-dfb367046420?auto=format&fit=crop&w=300&q=80"; // FF
                        } else if (stripos($game_name, 'pubg') !== false) {
                            $img_src = "https://images.unsplash.com/photo-1552820728-8b83bb6b773f?auto=format&fit=crop&w=300&q=80"; // PUBG
                        } else if (stripos($game_name, 'genshin') !== false) {
                            $img_src = "https://images.unsplash.com/photo-1538481199705-c710c4e965fc?auto=format&fit=crop&w=300&q=80"; // Genshin
                        }
                ?>
                <a href="detail.php?game=<?= urlencode($game_name) ?>" class="group glass rounded-2xl overflow-hidden border border-gray-800 neon-border transition-all duration-300 transform hover:-translate-y-1 shadow-lg flex flex-col">
                    <div class="aspect-[4/5] bg-gray-800 relative overflow-hidden">
                        <img src="<?= $img_src ?>" alt="<?= htmlspecialchars($game_name) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-transparent to-transparent opacity-80"></div>
                        <span class="absolute bottom-3 left-3 bg-indigo-600/90 text-[10px] text-white font-bold px-2 py-0.5 rounded uppercase tracking-wider">Top Up</span>
                    </div>
                    <div class="p-4 text-center">
                        <h4 class="font-bold text-sm text-gray-200 group-hover:text-indigo-400 transition line-clamp-1"><?= htmlspecialchars($game_name) ?></h4>
                    </div>
                </a>
                <?php 
                    }
                }
                ?>
            </div>
        </section>

        <!-- KATEGORI 2: AKUN GAME -->
        <section>
            <div class="flex justify-between items-end mb-8">
                <div>
                    <h2 class="text-2xl font-bold text-white tracking-wide"><i class="fa-solid fa-shield-halved text-indigo-400 mr-2"></i> Marketplace Akun Game</h2>
                    <p class="text-gray-400 text-sm mt-1">Dapatkan akun premium idamanmu langsung dari penjual.</p>
                </div>
                <a href="catalog.php?cat=akun" class="text-indigo-400 hover:text-indigo-300 text-sm font-semibold transition">
                    Lihat Semua &rarr;
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                <?php 
                if (mysqli_num_rows($accounts_latest) > 0) {
                    while ($row = mysqli_fetch_assoc($accounts_latest)) {
                        $img = "https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=400&q=80"; // ML
                        if (stripos($row['nama_game'], 'genshin') !== false) {
                            $img = "https://images.unsplash.com/photo-1538481199705-c710c4e965fc?auto=format&fit=crop&w=400&q=80";
                        }
                ?>
                <div class="group glass rounded-2xl overflow-hidden border border-gray-800 hover:border-indigo-500/50 transition duration-300 flex flex-col justify-between">
                    <div class="relative aspect-video bg-gray-800 overflow-hidden">
                        <img src="<?= $img ?>" alt="Akun Game" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        <span class="absolute top-3 left-3 bg-purple-600/90 text-[10px] text-white font-extrabold px-2.5 py-1 rounded-full uppercase tracking-wider">Akun Game</span>
                    </div>
                    
                    <div class="p-4 flex-grow flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-center text-[10px] text-indigo-450 uppercase font-semibold">
                                <span><?= htmlspecialchars($row['nama_game']) ?></span>
                                <span class="text-gray-500"><i class="fa-solid fa-user-tag mr-0.5"></i> <?= empty($row['nama_penjual']) ? 'Official' : htmlspecialchars($row['nama_penjual']) ?></span>
                            </div>
                            <h4 class="font-bold text-sm text-gray-200 mt-1 line-clamp-2 min-h-[40px] group-hover:text-indigo-300 transition"><?= htmlspecialchars($row['nama_produk']) ?></h4>
                        </div>
                        
                        <div class="mt-4 pt-4 border-t border-gray-850 flex justify-between items-center">
                            <div>
                                <span class="block text-[10px] text-gray-500 uppercase font-medium">Harga Akun</span>
                                <span class="font-bold text-sm text-emerald-400">Rp <?= number_format($row['harga'], 0, ',', '.') ?></span>
                            </div>
                            <a href="detail.php?id=<?= $row['id_produk'] ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold px-3 py-2 rounded-xl transition">
                                Detail &rarr;
                            </a>
                        </div>
                    </div>
                </div>
                <?php 
                    }
                } else {
                    echo "<div class='col-span-full py-8 text-center text-gray-500 glass rounded-2xl'>Belum ada akun game yang terdaftar.</div>";
                }
                ?>
            </div>
        </section>

        <!-- KATEGORI 3: ITEM & VOUCHER -->
        <section>
            <div class="flex justify-between items-end mb-8">
                <div>
                    <h2 class="text-2xl font-bold text-white tracking-wide"><i class="fa-solid fa-gift text-pink-500 mr-2"></i> Item Game & Voucher</h2>
                    <p class="text-gray-400 text-sm mt-1">Robux, Gift skin, atau voucher game dari seller terpercaya.</p>
                </div>
                <a href="catalog.php?cat=item" class="text-indigo-400 hover:text-indigo-300 text-sm font-semibold transition">
                    Lihat Semua &rarr;
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                <?php 
                if (mysqli_num_rows($items_latest) > 0) {
                    while ($row = mysqli_fetch_assoc($items_latest)) {
                        $img = "https://images.unsplash.com/photo-1612287230202-1bf1d85d1bdf?auto=format&fit=crop&w=400&q=80"; // Roblox
                        if (stripos($row['nama_game'], 'mobile legends') !== false) {
                            $img = "https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=400&q=80";
                        }
                ?>
                <div class="group glass rounded-2xl overflow-hidden border border-gray-800 hover:border-indigo-500/50 transition duration-300 flex flex-col justify-between">
                    <div class="relative aspect-video bg-gray-800 overflow-hidden">
                        <img src="<?= $img ?>" alt="Item Game" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        <span class="absolute top-3 left-3 bg-pink-600/90 text-[10px] text-white font-extrabold px-2.5 py-1 rounded-full uppercase tracking-wider">Item / Voucher</span>
                    </div>
                    
                    <div class="p-4 flex-grow flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-center text-[10px] text-indigo-455 uppercase font-semibold">
                                <span><?= htmlspecialchars($row['nama_game']) ?></span>
                                <span class="text-gray-500"><i class="fa-solid fa-user-tag mr-0.5"></i> <?= empty($row['nama_penjual']) ? 'Official' : htmlspecialchars($row['nama_penjual']) ?></span>
                            </div>
                            <h4 class="font-bold text-sm text-gray-200 mt-1 line-clamp-2 min-h-[40px] group-hover:text-indigo-300 transition"><?= htmlspecialchars($row['nama_produk']) ?></h4>
                        </div>
                        
                        <div class="mt-4 pt-4 border-t border-gray-850 flex justify-between items-center">
                            <div>
                                <span class="block text-[10px] text-gray-500 uppercase font-medium">Harga</span>
                                <span class="font-bold text-sm text-emerald-400">Rp <?= number_format($row['harga'], 0, ',', '.') ?></span>
                            </div>
                            <a href="detail.php?id=<?= $row['id_produk'] ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold px-3 py-2 rounded-xl transition">
                                Beli &rarr;
                            </a>
                        </div>
                    </div>
                </div>
                <?php 
                    }
                } else {
                    echo "<div class='col-span-full py-8 text-center text-gray-500 glass rounded-2xl'>Belum ada item game yang terdaftar.</div>";
                }
                ?>
            </div>
        </section>

    </main>

    <!-- Footer -->
    <footer class="bg-gray-950 border-t border-gray-800 py-12 mt-auto">
        <div class="container mx-auto px-6 max-w-6xl">
            <div class="border-t border-gray-900 pt-8 text-center text-xs text-gray-600">
                &copy; <?= date('Y') ?> TopUpIn. All rights reserved.
            </div>
        </div>
    </footer>

    <?php include "components/chat_widget.php"; ?>
</body>
</html>
