<?php
session_start();
include "config/koneksi.php";

/** @var mysqli $conn */

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_produk = isset($_POST['id_produk']) ? intval($_POST['id_produk']) : 0;
    $user_game = isset($_POST['user_game']) ? mysqli_real_escape_string($conn, $_POST['user_game']) : '';
    $server_game = isset($_POST['server_game']) ? mysqli_real_escape_string($conn, $_POST['server_game']) : '';
    $kontak_pembeli = isset($_POST['kontak_pembeli']) ? mysqli_real_escape_string($conn, $_POST['kontak_pembeli']) : '';
    $catatan = isset($_POST['catatan']) ? mysqli_real_escape_string($conn, $_POST['catatan']) : '';
    $metode_pembayaran = isset($_POST['metode_pembayaran']) ? mysqli_real_escape_string($conn, $_POST['metode_pembayaran']) : '';

    if ($id_produk > 0 && !empty($kontak_pembeli) && !empty($metode_pembayaran)) {
        // Ambil data produk
        $query_prod = mysqli_query($conn, "SELECT * FROM produk WHERE id_produk = $id_produk");
        $produk = mysqli_fetch_assoc($query_prod);

        if ($produk) {
            // Periksa ketersediaan stok untuk akun/item
            if ($produk['kategori'] != 'topup' && $produk['stok'] == 0) {
                echo "<script>
                        alert('Maaf, produk ini sedang habis stoknya.');
                        window.history.back();
                      </script>";
                exit;
            }

            $total = $produk['harga'];
            
            // Catat ID Pembeli jika sedang login (opsional)
            $buyer_id = (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) ? intval($_SESSION['user_id']) : "NULL";

            // Insert transaksi baru ke database
            $insert_query = "
                INSERT INTO transaksi (id_user, id_produk, user_game, server_game, kontak_pembeli, catatan, total, metode_pembayaran, status, tanggal)
                VALUES ($buyer_id, $id_produk, " . (!empty($user_game) ? "'$user_game'" : "NULL") . ", " . (!empty($server_game) ? "'$server_game'" : "NULL") . ", '$kontak_pembeli', " . (!empty($catatan) ? "'$catatan'" : "NULL") . ", $total, '$metode_pembayaran', 'Pending', NOW())
            ";

            if (mysqli_query($conn, $insert_query)) {
                $id_trx = mysqli_insert_id($conn);
                
                // Kurangi stok jika itu produk akun/item
                if ($produk['kategori'] != 'topup' && $produk['stok'] > 0) {
                    $stok_baru = $produk['stok'] - 1;
                    mysqli_query($conn, "UPDATE produk SET stok = $stok_baru WHERE id_produk = $id_produk");
                }

                // Kirim Notifikasi ke Bot Telegram via API
                $bot_api_url = "http://localhost:3001/api/create-transaction";
                $gameSlug = strtolower(str_replace(' ', '-', $produk['nama_game']));
                
                $payload = [
                    'trxId' => 'TRX-' . $id_trx,
                    'userGameId' => $user_game . ($server_game ? ' (' . $server_game . ')' : ''),
                    'gameSlug' => $gameSlug,
                    'productName' => $produk['nama_produk'],
                    'amount' => $total,
                ];

                $ch = curl_init($bot_api_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                curl_exec($ch);
                curl_close($ch);

                header("Location: pembayaran.php?invoice=" . $id_trx);
                exit;
            } else {
                echo "<script>
                        alert('Gagal memproses transaksi: " . mysqli_real_escape_string($conn, mysqli_error($conn)) . "');
                        window.history.back();
                      </script>";
                exit;
            }
        } else {
            echo "<script>
                    alert('Produk tidak ditemukan!');
                    window.location.href='catalog.php';
                  </script>";
            exit;
        }
    } else {
        echo "<script>
                alert('Silakan lengkapi formulir pembelian Anda.');
                window.history.back();
              </script>";
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}
?>
