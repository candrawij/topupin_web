<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: ../login.php");
    exit;
}

include "../config/koneksi.php";

/** @var mysqli $conn */
$user_id = intval($_SESSION['user_id']);
$id_produk = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil data produk
$query = mysqli_query($conn, "SELECT * FROM produk WHERE id_produk = $id_produk");
$row = mysqli_fetch_assoc($query);

// Proteksi: Pastikan produk ada dan dimiliki oleh seller ini
if (!$row || intval($row['id_user']) !== $user_id) {
    echo "<script>
            alert('Akses ditolak! Anda tidak memiliki wewenang untuk mengedit produk ini.');
            window.location.href='produk.php';
          </script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Dagangan - Seller Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body { font-family: 'Poppins', sans-serif; }</style>
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen flex items-center justify-center p-6">

    <div class="w-full max-w-lg bg-gray-800 border border-gray-700 rounded-3xl p-8 shadow-2xl space-y-6">
        <div class="flex justify-between items-center border-b border-gray-700 pb-4">
            <div>
                <h2 class="text-xl font-bold text-white flex items-center"><i class="fa-solid fa-pen-to-square text-indigo-400 mr-2"></i> Edit Dagangan</h2>
                <p class="text-[11px] text-gray-400 mt-1">Ubah spesifikasi produk ID #<?= $row['id_produk'] ?></p>
            </div>
            <a href="produk.php" class="text-xs text-gray-400 hover:text-white transition"><i class="fa-solid fa-circle-xmark text-lg"></i></a>
        </div>

        <form action="update_produk.php" method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="id_produk" value="<?= $row['id_produk'] ?>">

            <!-- Kategori -->
            <div>
                <label class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wide">Kategori Dagangan</label>
                <select name="kategori" required
                    class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
                    <option value="akun" <?= $row['kategori'] == 'akun' ? 'selected' : '' ?>>Akun Game (Marketplace Akun)</option>
                    <option value="item" <?= $row['kategori'] == 'item' ? 'selected' : '' ?>>Item Game / Voucher</option>
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
                        $selected = ($row['nama_game'] == $game) ? 'selected' : '';
                        echo "<option value='$game' $selected>$game</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- Nama Produk -->
            <div>
                <label class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wide">Judul Dagangan / Nama Produk</label>
                <input type="text" name="nama_produk" value="<?= htmlspecialchars($row['nama_produk']) ?>" required
                    class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
            </div>

            <!-- Deskripsi -->
            <div>
                <label class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wide">Spesifikasi & Deskripsi Detail</label>
                <textarea name="deskripsi" rows="4" required
                    class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500 transition"><?= htmlspecialchars($row['deskripsi']) ?></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <!-- Harga -->
                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wide">Harga Jual (Rp)</label>
                    <input type="number" name="harga" value="<?= $row['harga'] ?>" required
                        class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
                </div>

                <!-- Stok -->
                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wide">Jumlah Stok</label>
                    <input type="number" name="stok" value="<?= $row['stok'] ?>" min="1" required
                        class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
                </div>
            </div>

            <!-- Gambar Saat Ini & Form Upload -->
            <div>
                <label class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wide">Gambar Produk</label>
                <?php if (!empty($row['gambar'])) { ?>
                    <div class="mb-3 flex items-center space-x-3 bg-gray-900 p-2.5 rounded-xl border border-gray-850">
                        <img src="../uploads/<?= $row['gambar'] ?>" alt="Preview" class="w-16 h-10 object-cover rounded-lg border border-gray-700">
                        <span class="text-xs text-gray-500 truncate max-w-[200px]"><?= $row['gambar'] ?></span>
                    </div>
                <?php } ?>
                <input type="file" name="gambar" accept="image/*"
                    class="w-full text-xs text-gray-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-600/10 file:text-indigo-400 hover:file:bg-indigo-600/20">
            </div>

            <!-- Tombol Aksi -->
            <div class="flex items-center space-x-3 pt-4">
                <a href="produk.php" class="w-1/3 text-center bg-gray-700 hover:bg-gray-600 text-gray-300 font-semibold py-3.5 rounded-xl text-sm transition">Batal</a>
                <button type="submit" class="w-2/3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 rounded-xl text-sm shadow-lg shadow-indigo-600/20 transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

</body>
</html>
