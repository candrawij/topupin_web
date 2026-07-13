<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Dagangan Baru - Seller Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body { font-family: 'Poppins', sans-serif; }</style>
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen flex items-center justify-center p-6">

    <div class="w-full max-w-lg bg-gray-800 border border-gray-700 rounded-3xl p-8 shadow-2xl space-y-6">
        <div class="flex justify-between items-center border-b border-gray-700 pb-4">
            <div>
                <h2 class="text-xl font-bold text-white flex items-center"><i class="fa-solid fa-plus-circle text-indigo-400 mr-2"></i> Tambah Dagangan</h2>
                <p class="text-[11px] text-gray-400 mt-1">Unggah produk akun atau item game Anda ke catalog marketplace.</p>
            </div>
            <a href="produk.php" class="text-xs text-gray-400 hover:text-white transition"><i class="fa-solid fa-circle-xmark text-lg"></i></a>
        </div>

        <form action="simpan_produk.php" method="POST" enctype="multipart/form-data" class="space-y-4">
            <!-- Kategori (Hanya Akun & Item yang diperbolehkan untuk pihak ketiga) -->
            <div>
                <label class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wide">Kategori Dagangan</label>
                <select name="kategori" required
                    class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
                    <option value="akun">Akun Game (Marketplace Akun)</option>
                    <option value="item">Item Game / Gift Skin / Voucher</option>
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

            <!-- Nama Produk / Judul Listing -->
            <div>
                <label class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wide">Judul Dagangan / Nama Produk</label>
                <input type="text" name="nama_produk" placeholder="Contoh: Akun MLBB Mythic 100 Skins, 1000 Robux Murah" required
                    class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
            </div>

            <!-- Deskripsi Detail -->
            <div>
                <label class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wide">Spesifikasi & Deskripsi Detail</label>
                <textarea name="deskripsi" rows="4" placeholder="Sebutkan detail produk (misal: level akun, hero, skin, sisa mata uang, dan cara serah terima detail login setelah berstatus sukses)..." required
                    class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500 transition"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <!-- Harga -->
                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wide">Harga Jual (Rp)</label>
                    <input type="number" name="harga" placeholder="Contoh: 150000" required
                        class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
                </div>

                <!-- Stok -->
                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wide">Jumlah Stok</label>
                    <input type="number" name="stok" placeholder="Contoh: 1 (untuk akun) atau 100 (item)" value="1" min="1" required
                        class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
                </div>
            </div>

            <!-- Upload Gambar -->
            <div>
                <label class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wide">Gambar / Screenshot Bukti Akun</label>
                <input type="file" name="gambar" accept="image/*" required
                    class="w-full text-xs text-gray-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-600/10 file:text-indigo-400 hover:file:bg-indigo-600/20">
            </div>

            <!-- Tombol Aksi -->
            <div class="flex items-center space-x-3 pt-4">
                <a href="produk.php" class="w-1/3 text-center bg-gray-700 hover:bg-gray-600 text-gray-300 font-semibold py-3.5 rounded-xl text-sm transition">Batal</a>
                <button type="submit" class="w-2/3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 rounded-xl text-sm shadow-lg shadow-indigo-600/20 transition">
                    Simpan Dagangan
                </button>
            </div>
        </form>
    </div>

</body>
</html>
