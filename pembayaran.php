<?php
include "config/koneksi.php";
include "config/telegram_helper.php";

/** @var mysqli $conn */

$id_trx = isset($_GET['invoice']) ? intval($_GET['invoice']) : 0;
if ($id_trx <= 0) {
    header("Location: index.php");
    exit;
}

// Ambil data transaksi
$query = mysqli_query($conn, "
    SELECT t.*, p.nama_produk, p.nama_game, p.kategori, p.gambar
    FROM transaksi t
    JOIN produk p ON t.id_produk = p.id_produk
    WHERE t.id_trx = $id_trx
");
$trx = mysqli_fetch_assoc($query);

if (!$trx) {
    header("Location: index.php");
    exit;
}

$message = "";
$error = "";

// Proses Upload Bukti Bayar (Untuk Transfer BCA atau manual)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['bukti_bayar'])) {
    $target_dir = "uploads/bukti/";
    
    // Pastikan folder bukti ada
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_extension = strtolower(pathinfo($_FILES["bukti_bayar"]["name"], PATHINFO_EXTENSION));
    $new_filename = "BUKTI_" . $id_trx . "_" . time() . "." . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    // Validasi file type
    $allowed_types = ['jpg', 'jpeg', 'png'];
    if (in_array($file_extension, $allowed_types)) {
        if (move_uploaded_file($_FILES["bukti_bayar"]["tmp_name"], $target_file)) {
            // Update database dengan path bukti bayar
            $update = mysqli_query($conn, "UPDATE transaksi SET bukti_bayar = '$target_file' WHERE id_trx = $id_trx");
            if ($update) {
                $message = "Bukti transfer berhasil diunggah! Admin akan memverifikasi pembayaran Anda.";
                // Refresh data transaksi
                $query = mysqli_query($conn, "SELECT t.*, p.nama_produk, p.nama_game, p.kategori, p.gambar FROM transaksi t JOIN produk p ON t.id_produk = p.id_produk WHERE t.id_trx = $id_trx");
                $trx = mysqli_fetch_assoc($query);
            } else {
                $error = "Gagal memperbarui data transaksi.";
            }
        } else {
            $error = "Terjadi kesalahan saat mengunggah file.";
        }
    } else {
        $error = "Format file tidak didukung! Hanya diperbolehkan JPG, JPEG, dan PNG.";
    }
}

// Simulasi Konfirmasi Otomatis (Hanya untuk mempermudah testing metode QRIS/DANA)
if (isset($_GET['action']) && $_GET['action'] == 'simulasi_sukses' && $trx['status'] == 'Pending') {
    $update = mysqli_query($conn, "UPDATE transaksi SET status = 'Success' WHERE id_trx = $id_trx");
    if ($update) {
        header("Location: pembayaran.php?invoice=" . $id_trx . "&msg=simulated");
        exit;
    }
}

$simulated_msg = isset($_GET['msg']) && $_GET['msg'] == 'simulated';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Invoice #<?= $trx['id_trx'] ?> - TopUpIn</title>
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
            <a href="riwayat.php" class="text-sm bg-gray-800 hover:bg-gray-700 text-gray-300 px-4 py-2 rounded-xl transition">Lacak Pesanan</a>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="container mx-auto px-6 py-10 max-w-3xl flex-grow space-y-6">
        
        <!-- Header Rincian Transaksi -->
        <div class="glass p-6 rounded-3xl border border-gray-800 shadow-xl flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <span class="text-xs text-gray-500 font-mono">INVOICE ID: #<?= $trx['id_trx'] ?></span>
                <h2 class="text-xl font-bold text-white mt-0.5">Rincian Pembelian</h2>
                <p class="text-gray-400 text-xs mt-1">Tanggal Transaksi: <?= $trx['tanggal'] ?></p>
            </div>
            <div>
                <?php 
                $status = strtolower($trx['status']);
                $badge_class = "bg-yellow-500/10 text-yellow-500 border border-yellow-500/20";
                if ($status == 'success') $badge_class = "bg-green-500/10 text-green-500 border border-green-500/20";
                if ($status == 'failed') $badge_class = "bg-red-500/10 text-red-500 border border-red-500/20";
                ?>
                <span class="px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider <?= $badge_class ?>">
                    Status: <?= $trx['status'] ?>
                </span>
            </div>
        </div>

        <?php if (!empty($message) || $simulated_msg) { ?>
            <div class="bg-green-500/10 border border-green-500/20 text-green-400 text-sm p-4 rounded-2xl text-center">
                <i class="fa-solid fa-circle-check mr-2"></i> <?= $simulated_msg ? "Simulasi transaksi sukses berhasil diproses!" : $message ?>
            </div>
        <?php } ?>
        <?php if (!empty($error)) { ?>
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 text-sm p-4 rounded-2xl text-center">
                <i class="fa-solid fa-triangle-exclamation mr-2"></i> <?= $error ?>
            </div>
        <?php } ?>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
            
            <!-- Rincian Item (Col 2) -->
            <div class="md:col-span-2 space-y-6">
                <div class="glass p-6 rounded-3xl border border-gray-800 shadow-xl space-y-4">
                    <h3 class="font-bold text-white text-sm border-b border-gray-800 pb-3">Produk & Pembeli</h3>
                    
                    <div>
                        <span class="text-[10px] text-gray-500 uppercase block font-medium">Item Game</span>
                        <p class="text-sm font-semibold text-white mt-0.5"><?= htmlspecialchars($trx['nama_game']) ?> - <?= htmlspecialchars($trx['nama_produk']) ?></p>
                    </div>

                    <?php if ($trx['kategori'] == 'topup') { ?>
                        <div>
                            <span class="text-[10px] text-gray-500 uppercase block font-medium">Tujuan Game ID</span>
                            <p class="text-sm font-bold text-indigo-400 mt-0.5"><?= htmlspecialchars($trx['user_game']) ?> <?= !empty($trx['server_game']) ? "(".htmlspecialchars($trx['server_game']).")" : "" ?></p>
                        </div>
                    <?php } ?>

                    <div>
                        <span class="text-[10px] text-gray-500 uppercase block font-medium">Kontak Penerima</span>
                        <p class="text-sm font-semibold text-white mt-0.5"><?= htmlspecialchars($trx['kontak_pembeli']) ?></p>
                    </div>

                    <?php if (!empty($trx['catatan'])) { ?>
                        <div>
                            <span class="text-[10px] text-gray-500 uppercase block font-medium">Catatan Anda</span>
                            <p class="text-xs text-gray-400 mt-0.5 italic">"<?= htmlspecialchars($trx['catatan']) ?>"</p>
                        </div>
                    <?php } ?>

                    <div class="pt-4 border-t border-gray-850">
                        <span class="text-[10px] text-gray-500 uppercase block font-medium">Total Pembayaran</span>
                        <p class="text-lg font-black text-emerald-400 mt-0.5">Rp <?= number_format($trx['total'], 0, ',', '.') ?></p>
                    </div>
                </div>
            </div>

            <!-- Metode Instruksi Bayar (Col 3) -->
            <div class="md:col-span-3 space-y-6">
                <div class="glass p-6 rounded-3xl border border-gray-800 shadow-xl space-y-6">
                    <h3 class="font-bold text-white text-sm border-b border-gray-800 pb-3">Instruksi Pembayaran</h3>
                    
                    <?php if ($trx['status'] == 'Pending') { ?>
                        
                        <?php if ($trx['metode_pembayaran'] == 'QRIS' || $trx['metode_pembayaran'] == 'DANA') { ?>
                            <!-- QRIS / E-Wallet Pembayaran -->
                            <div class="flex flex-col items-center space-y-4">
                                <div class="p-3 bg-white rounded-2xl shadow-inner">
                                    <!-- QR Code Generator Mockup -->
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=TopUpIn_TRX_<?= $trx['id_trx'] ?>_TOTAL_<?= $trx['total'] ?>" alt="QRIS QR Code" class="w-40 h-40">
                                </div>
                                <div class="text-center">
                                    <p class="text-xs text-gray-400">Silakan scan kode QRIS di atas menggunakan aplikasi E-Wallet (DANA, OVO, GoPay, ShopeePay, LinkAja) atau m-Banking Anda.</p>
                                    <p class="text-[11px] text-indigo-400 font-semibold mt-2"><i class="fa-solid fa-circle-info mr-1"></i> Setelah scan berhasil, status transaksi akan otomatis diperbarui.</p>
                                </div>
                                
                                <!-- Simulasi Sukses (Untuk kemudahan test user) -->
                                <div class="w-full pt-4 border-t border-gray-850 text-center">
                                    <p class="text-[10px] text-gray-500 uppercase mb-2">Simulasi Pengujian Toko</p>
                                    <a href="pembayaran.php?invoice=<?= $trx['id_trx'] ?>&action=simulasi_sukses" 
                                       class="inline-block bg-indigo-600/20 hover:bg-indigo-600/30 text-indigo-400 hover:text-white font-semibold text-xs py-2.5 px-4 rounded-xl transition border border-indigo-500/20">
                                        <i class="fa-solid fa-wand-magic-sparkles mr-1.5"></i> Klik di sini untuk Simulasi Bayar Sukses
                                    </a>
                                </div>
                            </div>
                        <?php } elseif ($trx['metode_pembayaran'] == 'TRANSFER_BCA') { ?>
                            <!-- Rekening Transfer BCA -->
                            <div class="space-y-4">
                                <div class="bg-gray-950 p-4 rounded-2xl border border-gray-900 flex justify-between items-center">
                                    <div>
                                        <span class="text-[10px] text-gray-500 block uppercase font-medium">Bank Pembayaran</span>
                                        <span class="font-bold text-sm text-white">Bank Central Asia (BCA)</span>
                                    </div>
                                    <span class="text-xs bg-indigo-500/10 text-indigo-400 px-2 py-0.5 rounded font-bold font-mono">BCA</span>
                                </div>
                                <div class="bg-gray-950 p-4 rounded-2xl border border-gray-900 flex justify-between items-center">
                                    <div>
                                        <span class="text-[10px] text-gray-500 block uppercase font-medium">Nomor Rekening</span>
                                        <span class="font-mono font-bold text-base text-emerald-400" id="rek_no">8023-9281-2291</span>
                                    </div>
                                    <button onclick="navigator.clipboard.writeText('802392812291'); alert('Nomor rekening disalin!')" 
                                            class="text-xs text-indigo-400 hover:underline">
                                        Salin
                                    </button>
                                </div>
                                <div class="bg-gray-950 p-4 rounded-2xl border border-gray-900">
                                    <span class="text-[10px] text-gray-500 block uppercase font-medium">Atas Nama Rekening</span>
                                    <span class="font-semibold text-sm text-white">TopUpIn Admin Marketplace</span>
                                </div>
                                
                                <!-- Form Upload Bukti Transfer -->
                                <form action="" method="POST" enctype="multipart/form-data" class="pt-4 border-t border-gray-850 space-y-3">
                                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide">Unggah Bukti Transfer Bank</label>
                                    <div class="flex items-center space-x-3">
                                        <input type="file" name="bukti_bayar" accept="image/*" required 
                                               class="w-full text-xs text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-600/10 file:text-indigo-400 hover:file:bg-indigo-600/20">
                                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs px-4 py-2.5 rounded-xl transition">
                                            Kirim
                                        </button>
                                    </div>
                                    <?php if (!empty($trx['bukti_bayar'])) { ?>
                                        <span class="text-[10px] text-emerald-400 block"><i class="fa-solid fa-check-double mr-1"></i> Bukti transfer sudah terkirim (Menunggu verifikasi admin).</span>
                                    <?php } else { ?>
                                        <span class="text-[10px] text-gray-500 block">Unggah file struk transfer (format JPG, JPEG, PNG).</span>
                                    <?php } ?>
                                </form>
                            </div>
                        <?php } ?>

                    <?php } elseif ($trx['status'] == 'Success') { ?>
                        <!-- Sukses Pembelian -->
                        <div class="text-center py-6 space-y-4">
                            <div class="w-16 h-16 bg-green-500/10 rounded-full flex items-center justify-center mx-auto text-green-400 text-3xl border border-green-500/20">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-lg text-white">Pembayaran Sukses!</h4>
                                <p class="text-xs text-gray-400 mt-1">Pesanan Anda telah berhasil diproses.</p>
                            </div>
                            
                            <?php if ($trx['kategori'] == 'topup') { ?>
                                <div class="bg-gray-950 p-4 rounded-2xl border border-gray-900 max-w-sm mx-auto text-left">
                                    <p class="text-xs text-gray-300 leading-relaxed"><i class="fa-solid fa-bolt text-yellow-500 mr-1.5"></i> Diamond/UC telah dikirimkan ke Game ID <strong><?= htmlspecialchars($trx['user_game']) ?></strong> secara instan.</p>
                                </div>
                            <?php } else { ?>
                                <div class="bg-gray-950 p-4 rounded-2xl border border-gray-900 max-w-sm mx-auto text-left space-y-2">
                                    <p class="text-xs text-gray-300 font-semibold"><i class="fa-solid fa-gift text-pink-500 mr-1.5"></i> Informasi Serah Terima:</p>
                                    <p class="text-xs text-gray-400 leading-relaxed">Admin sedang menyiapkan kredensial Akun / Gift Item Anda. Anda akan segera dihubungi melalui WhatsApp/Email di nomor <strong><?= htmlspecialchars($trx['kontak_pembeli']) ?></strong>.</p>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } elseif ($trx['status'] == 'Failed') { ?>
                        <!-- Gagal / Ditolak -->
                        <div class="text-center py-6 space-y-4">
                            <div class="w-16 h-16 bg-red-500/10 rounded-full flex items-center justify-center mx-auto text-red-400 text-3xl border border-red-500/20">
                                <i class="fa-solid fa-circle-xmark"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-lg text-white">Transaksi Gagal / Ditolak</h4>
                                <p class="text-xs text-gray-400 mt-1">Pembayaran ditolak atau terjadi kegagalan pemrosesan.</p>
                            </div>
                            <p class="text-xs text-gray-500 leading-relaxed max-w-sm mx-auto">
                                Silakan hubungi customer service kami jika Anda merasa ada kekeliruan atau ingin mengajukan pertanyaan.
                            </p>
                        </div>
                    <?php } ?>
                </div>

                <!-- Hubungi CS / Bantuan -->
                <div class="glass p-5 rounded-3xl border border-gray-800 shadow-xl flex flex-col sm:flex-row gap-3 justify-between items-center">
                    <div>
                        <h4 class="font-bold text-xs text-white uppercase tracking-wide">Butuh Bantuan?</h4>
                        <p class="text-[10px] text-gray-500 mt-0.5">Hubungi CS kami jika mengalami kendala transaksi.</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="https://wa.me/6281234567890?text=Halo%20Admin%20saya%20butuh%20bantuan%20terkait%20transaksi%20invoice%20#<?= $trx['id_trx'] ?>" 
                           target="_blank" 
                           class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs px-3 py-2.5 rounded-xl transition flex items-center shadow-lg shadow-emerald-600/20">
                            <i class="fa-brands fa-whatsapp mr-1.5 text-sm"></i> WhatsApp
                        </a>
                        <a href="<?= buildTrxTelegramLink('TRX-' . $trx['id_trx']) ?>" 
                           target="_blank" 
                           class="bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs px-3 py-2.5 rounded-xl transition flex items-center shadow-lg shadow-sky-600/20">
                            <i class="fa-brands fa-telegram mr-1.5 text-sm"></i> Telegram CS
                        </a>
                    </div>
                </div>
            </div>

        </div>

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
