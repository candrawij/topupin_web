<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

include "../config/koneksi.php";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk Baru - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body { font-family: 'Poppins', sans-serif; }</style>
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen flex items-center justify-center p-6">

    <div class="w-full max-w-lg bg-gray-800 border border-gray-700 rounded-3xl p-8 shadow-2xl space-y-6">
        <div class="flex justify-between items-center border-b border-gray-700 pb-4">
            <div>
                <h2 class="text-xl font-bold text-white flex items-center"><i class="fa-solid fa-plus-circle text-indigo-400 mr-2"></i> Tambah Produk</h2>
                <p class="text-[11px] text-gray-400 mt-1">Masukkan spesifikasi dan harga dengan lengkap.</p>
            </div>
            <a href="produk.php" class="text-xs text-gray-400 hover:text-white transition"><i class="fa-solid fa-circle-xmark text-lg"></i></a>
        </div>

        <form action="simpan_produk.php" method="POST" enctype="multipart/form-data" class="space-y-4">
            <!-- Kategori -->
            <div>
                <label class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wide">Kategori Produk</label>
                <select name="kategori" required onchange="toggleFields(this.value)"
                    class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
                    <option value="topup">Top Up (Diamond / UC)</option>
                    <option value="akun">Akun Game (Marketplace Akun)</option>
                    <option value="item">Item Game / Voucher</option>
                </select>
            </div>

            <!-- Nama Game -->
            <div>
                <label class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wide">Nama Game</label>
                <select name="nama_game" required
                    class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
                    <?php
                    $list_game = ["Mobile Legends", "Free Fire", "PUBG Mobile", "Genshin Impact", "Roblox", "Valorant", "Honor of Kings", "Point Blank", "Lain-lain"];
                    foreach ($list_game as $game) {
                        echo "<option value='$game'>$game</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- Nama Produk -->
            <div>
                <label class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wide">Nama Produk</label>
                <input type="text" name="nama_produk" placeholder="Contoh: 86 Diamonds, Akun MLBB High Tier, 1000 Robux" required
                    class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
            </div>

            <!-- Deskripsi -->
            <div>
                <label class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wide">Deskripsi Detail</label>
                <textarea name="deskripsi" rows="3" placeholder="Masukkan spesifikasi produk, detail akun, atau cara pengiriman item..." required
                    class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500 transition"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <!-- Harga -->
                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wide">Harga Jual (Rp)</label>
                    <input type="number" name="harga" placeholder="Contoh: 20000" required
                        class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
                </div>

                <!-- Nominal (Hanya relevan untuk Top Up) -->
                <div id="field_nominal">
                    <label class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wide">Nominal Varian</label>
                    <input type="number" name="nominal" placeholder="Contoh: 86" value=""
                        class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
                </div>

                <!-- Stok (Hanya relevan untuk Akun / Item) -->
                <div id="field_stok" class="hidden">
                    <label class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wide">Jumlah Stok</label>
                    <input type="number" name="stok" placeholder="Stok item" value="-1"
                        class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
                </div>
            </div>

            <!-- Upload Gambar -->
            <div>
                <label class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wide">Gambar / Thumbnail Produk</label>
                <input type="file" name="gambar" accept="image/*"
                    class="w-full text-xs text-gray-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-600/10 file:text-indigo-400 hover:file:bg-indigo-600/20">
            </div>

            <!-- Tombol Aksi -->
            <div class="flex items-center space-x-3 pt-4">
                <a href="produk.php" class="w-1/3 text-center bg-gray-700 hover:bg-gray-600 text-gray-300 font-semibold py-3.5 rounded-xl text-sm transition">Batal</a>
                <button type="submit" class="w-2/3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 rounded-xl text-sm shadow-lg shadow-indigo-600/20 transition">
                    Simpan Produk
                </button>
            </div>
        </form>
    </div>

    <script>
        function toggleFields(val) {
            const nominalField = document.getElementById('field_nominal');
            const stokField = document.getElementById('field_stok');
            const stokInput = stokField.querySelector('input');
            
            if (val === 'topup') {
                nominalField.classList.remove('hidden');
                stokField.classList.add('hidden');
                stokInput.value = '-1'; // Unlimited
            } else {
                nominalField.classList.add('hidden');
                stokField.classList.remove('hidden');
                if (stokInput.value === '-1') {
                    stokInput.value = '1'; // Default stok akun/item
                }
            }
        }
    </script>

</body>
</html>