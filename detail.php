<?php
session_start();
include "config/koneksi.php";

/** @var mysqli $conn */

// Periksa apakah request berdasarkan ID produk (Akun/Item) atau Nama Game (Top Up)
$id_produk = isset($_GET['id']) ? intval($_GET['id']) : 0;
$game_name = isset($_GET['game']) ? htmlspecialchars($_GET['game']) : '';

$is_topup = true;
$product_details = null;
$topup_packages = [];

if ($id_produk > 0) {
    // Kategori: Akun atau Item
    $query = mysqli_query($conn, "
        SELECT p.*, u.nama as nama_penjual 
        FROM produk p
        LEFT JOIN user u ON p.id_user = u.id_user
        WHERE p.id_produk = $id_produk
    ");
    $product_details = mysqli_fetch_assoc($query);
    if ($product_details) {
        $is_topup = ($product_details['kategori'] == 'topup');
        $game_name = $product_details['nama_game'];
    } else {
        header("Location: catalog.php");
        exit;
    }
} elseif (!empty($game_name)) {
    // Kategori: Top Up Game
    $is_topup = true;
    $query = mysqli_query($conn, "SELECT * FROM produk WHERE nama_game = '" . mysqli_real_escape_string($conn, $game_name) . "' AND kategori='topup' ORDER BY harga ASC");
    while ($row = mysqli_fetch_assoc($query)) {
        $topup_packages[] = $row;
    }
    
    if (count($topup_packages) == 0) {
        header("Location: catalog.php");
        exit;
    }
} else {
    header("Location: catalog.php");
    exit;
}

// Fallback images
$img_hero = "https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=800&q=80"; // ML
if (stripos($game_name, 'free fire') !== false) {
    $img_hero = "https://images.unsplash.com/photo-1511512578047-dfb367046420?auto=format&fit=crop&w=800&q=80";
} elseif (stripos($game_name, 'pubg') !== false) {
    $img_hero = "https://images.unsplash.com/photo-1552820728-8b83bb6b773f?auto=format&fit=crop&w=800&q=80";
} elseif (stripos($game_name, 'roblox') !== false) {
    $img_hero = "https://images.unsplash.com/photo-1612287230202-1bf1d85d1bdf?auto=format&fit=crop&w=800&q=80";
} elseif (stripos($game_name, 'genshin') !== false) {
    $img_hero = "https://images.unsplash.com/photo-1538481199705-c710c4e965fc?auto=format&fit=crop&w=800&q=80";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beli <?= htmlspecialchars($is_topup ? $game_name : $product_details['nama_produk']) ?> - TopUpIn</title>
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
            <a href="catalog.php" class="text-sm bg-gray-850 hover:bg-gray-700 text-gray-300 px-4 py-2 rounded-xl transition">&larr; Catalog</a>
        </div>
    </nav>

    <!-- Content Container -->
    <main class="container mx-auto px-6 py-10 max-w-5xl flex-grow">
        <form action="checkout.php" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-8">
            
            <!-- KOLOM KIRI (Info Produk/Game & Form Input Game) -->
            <div class="md:col-span-1 space-y-6">
                <!-- Info Game Card -->
                <div class="glass rounded-3xl overflow-hidden border border-gray-800 shadow-xl">
                    <div class="aspect-video w-full relative">
                        <img src="<?= $img_hero ?>" alt="<?= htmlspecialchars($game_name) ?>" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-gray-950/20 to-transparent"></div>
                    </div>
                    <div class="p-6">
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-indigo-400 font-bold uppercase tracking-wider">
                                <?= $is_topup ? 'Instan Top Up' : ucfirst($product_details['kategori']) ?>
                            </span>
                            <?php if (!$is_topup) { ?>
                                <span class="text-[10px] text-gray-500 font-semibold"><i class="fa-solid fa-user-tag mr-0.5"></i> <?= empty($product_details['nama_penjual']) ? 'Official' : htmlspecialchars($product_details['nama_penjual']) ?></span>
                            <?php } ?>
                        </div>
                        <h2 class="text-2xl font-bold text-white mt-1"><?= htmlspecialchars($is_topup ? $game_name : $product_details['nama_produk']) ?></h2>
                        <p class="text-gray-400 text-xs mt-2 leading-relaxed">
                            <?= $is_topup ? 'Proses instan otomatis masuk 24 jam lunas. Masukkan ID Game Anda dengan benar.' : 'Silakan isi data kontak Anda untuk serah terima detail item/akun oleh admin.' ?>
                        </p>
                    </div>
                </div>

                <!-- LANGKAH 1: Data Pengiriman/ID -->
                <div class="glass p-6 rounded-3xl border border-gray-800 shadow-xl space-y-4">
                    <div class="flex items-center space-x-3 mb-2">
                        <span class="w-7 h-7 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-xs shadow-lg shadow-indigo-600/30">1</span>
                        <h3 class="font-bold text-white">Data Pengiriman</h3>
                    </div>
                    
                    <?php if ($is_topup) { ?>
                        <!-- Form Topup Game -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wide">Player ID / ID Game</label>
                            <input type="text" name="user_game" placeholder="Masukkan ID Game Anda" required
                                class="w-full bg-gray-950 border border-gray-850 rounded-2xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                        </div>
                        
                        <!-- Cek Apakah perlu server ID (Untuk MLBB / Genshin) -->
                        <?php if (stripos($game_name, 'Mobile Legends') !== false || stripos($game_name, 'Genshin') !== false || stripos($game_name, 'Honor of Kings') !== false) { ?>
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wide">Server / Zone ID</label>
                            <input type="text" name="server_game" placeholder="Masukkan Server/Zone ID" required
                                class="w-full bg-gray-950 border border-gray-850 rounded-2xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                        </div>
                        <?php } ?>
                    <?php } ?>

                    <!-- Semua Kategori Wajib Kontak (WhatsApp/Email) -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wide">Nomor WhatsApp / Email</label>
                        <input type="text" name="kontak_pembeli" placeholder="Contoh: 081234567890 atau email@domain.com" required
                            class="w-full bg-gray-950 border border-gray-850 rounded-2xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                        <span class="text-[10px] text-gray-505 block mt-1">Kami akan menghubungi Anda di kontak ini untuk proses verifikasi/kirim data.</span>
                    </div>

                    <?php if (!$is_topup) { ?>
                        <!-- Catatan Pembeli untuk Akun / Item -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wide">Catatan Pembelian (Opsional)</label>
                            <textarea name="catatan" rows="3" placeholder="Contoh: Kirim via email ya min, atau catatan spesifik lainnya."
                                class="w-full bg-gray-950 border border-gray-850 rounded-2xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition"></textarea>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <!-- KOLOM KANAN (LANGKAH 2: PILIHAN PRODUK & LANGKAH 3: PEMBAYARAN) -->
            <div class="md:col-span-2 space-y-6">
                
                <!-- LANGKAH 2: Pilih Nominal/Spesifikasi -->
                <div class="glass p-6 rounded-3xl border border-gray-800 shadow-xl">
                    <div class="flex items-center space-x-3 mb-6">
                        <span class="w-7 h-7 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-xs shadow-lg shadow-indigo-600/30">2</span>
                        <h3 class="font-bold text-white"><?= $is_topup ? 'Pilih Nominal Top Up' : 'Spesifikasi Produk' ?></h3>
                    </div>

                    <?php if ($is_topup) { ?>
                        <!-- Grid Nominal Diamond/UC -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <?php 
                            $first = true;
                            foreach ($topup_packages as $package) { 
                                $is_promo = (stripos($package['nama_produk'], 'promo') !== false || stripos($package['nama_produk'], 'hemat') !== false);
                            ?>
                            <label class="relative bg-gray-950 border-2 <?= $first ? 'border-indigo-500' : 'border-gray-900 hover:border-indigo-500/50' ?> rounded-2xl p-5 cursor-pointer hover:bg-gray-900/30 transition flex flex-col justify-between group">
                                <?php if ($is_promo) { ?>
                                    <span class="absolute -top-2.5 -right-2 bg-gradient-to-r from-red-500 to-pink-500 text-[9px] text-white px-2 py-0.5 rounded-full font-bold uppercase tracking-wider shadow">
                                        PROMO
                                    </span>
                                <?php } ?>
                                <input type="radio" name="id_produk" value="<?= $package['id_produk'] ?>" <?= $first ? 'checked' : '' ?> class="absolute top-5 right-5 text-indigo-600 focus:ring-indigo-500 bg-gray-900 border-gray-850">
                                
                                <div>
                                    <h4 class="font-bold text-base text-gray-200 group-hover:text-indigo-400 transition"><?= htmlspecialchars($package['nama_produk']) ?></h4>
                                    <p class="text-xs text-gray-500 mt-1"><?= htmlspecialchars($package['deskripsi']) ?></p>
                                </div>
                                <p class="text-emerald-400 font-bold text-md mt-4">Rp <?= number_format($package['harga'], 0, ',', '.') ?></p>
                            </label>
                            <?php 
                                $first = false;
                            } 
                            ?>
                        </div>
                    <?php } else { ?>
                        <!-- Rincian Spesifikasi Akun/Item -->
                        <div class="bg-gray-950 rounded-2xl p-6 border border-gray-900 space-y-4">
                            <input type="hidden" name="id_produk" value="<?= $product_details['id_produk'] ?>">
                            
                            <!-- Harga & Stok -->
                            <div class="flex justify-between items-center pb-4 border-b border-gray-900">
                                <div>
                                    <span class="text-xs text-gray-500 uppercase">Harga Produk</span>
                                    <h3 class="text-2xl font-bold text-emerald-400 mt-0.5">Rp <?= number_format($product_details['harga'], 0, ',', '.') ?></h3>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs text-gray-500 uppercase">Status Ketersediaan</span>
                                    <h3 class="text-sm font-bold text-white mt-1">Stok: <?= $product_details['stok'] ?> Pcs</h3>
                                </div>
                            </div>

                            <!-- Deskripsi -->
                            <div>
                                <span class="text-xs font-semibold text-gray-400 block mb-2 uppercase tracking-wide">Deskripsi Detail:</span>
                                <div class="text-gray-300 text-sm whitespace-pre-line leading-relaxed">
                                    <?= htmlspecialchars($product_details['deskripsi']) ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>

                <!-- LANGKAH 3: Metode Pembayaran -->
                <div class="glass p-6 rounded-3xl border border-gray-800 shadow-xl space-y-6">
                    <div class="flex items-center space-x-3">
                        <span class="w-7 h-7 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-xs shadow-lg shadow-indigo-600/30">3</span>
                        <h3 class="font-bold text-white">Metode Pembayaran</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <label class="flex flex-col bg-gray-950 border border-gray-850 rounded-2xl p-4 cursor-pointer hover:border-indigo-500 transition group relative">
                            <input type="radio" name="metode_pembayaran" value="QRIS" checked class="absolute top-4 right-4 text-indigo-600 focus:ring-indigo-500 bg-gray-900 border-gray-850">
                            <div class="text-gray-400 group-hover:text-white text-lg mb-2"><i class="fa-solid fa-qrcode text-indigo-400"></i></div>
                            <span class="text-sm font-semibold text-gray-200 group-hover:text-white">QRIS / E-Wallet</span>
                            <span class="text-[9px] text-emerald-400 font-mono mt-1">Verifikasi Otomatis</span>
                        </label>

                        <label class="flex flex-col bg-gray-950 border border-gray-850 rounded-2xl p-4 cursor-pointer hover:border-indigo-500 transition group relative">
                            <input type="radio" name="metode_pembayaran" value="DANA" class="absolute top-4 right-4 text-indigo-600 focus:ring-indigo-500 bg-gray-900 border-gray-850">
                            <div class="text-gray-400 group-hover:text-white text-lg mb-2"><i class="fa-solid fa-wallet text-indigo-400"></i></div>
                            <span class="text-sm font-semibold text-gray-200 group-hover:text-white">DANA</span>
                            <span class="text-[9px] text-emerald-400 font-mono mt-1">Verifikasi Otomatis</span>
                        </label>

                        <label class="flex flex-col bg-gray-950 border border-gray-850 rounded-2xl p-4 cursor-pointer hover:border-indigo-500 transition group relative">
                            <input type="radio" name="metode_pembayaran" value="TRANSFER_BCA" class="absolute top-4 right-4 text-indigo-600 focus:ring-indigo-500 bg-gray-900 border-gray-850">
                            <div class="text-gray-400 group-hover:text-white text-lg mb-2"><i class="fa-solid fa-building-columns text-indigo-400"></i></div>
                            <span class="text-sm font-semibold text-gray-200 group-hover:text-white">BCA Transfer</span>
                            <span class="text-[9px] text-amber-500 font-mono mt-1">Verifikasi Manual</span>
                        </label>
                    </div>

                    <!-- Tombol checkout -->
                    <button type="submit" 
                        class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold py-4 px-6 rounded-2xl shadow-xl shadow-indigo-600/30 transition transform active:scale-[0.98] text-sm tracking-wider uppercase">
                        Beli Sekarang <i class="fa-solid fa-cart-shopping ml-2"></i>
                    </button>
                </div>

            </div>

        </form>
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
