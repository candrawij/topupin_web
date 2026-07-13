<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: ../login.php");
    exit;
}

include "../config/koneksi.php";

/** @var mysqli $conn */
$user_id = intval($_SESSION['user_id']);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $jumlah = intval($_POST['jumlah']);
    $bank_tujuan = mysqli_real_escape_string($conn, $_POST['bank_tujuan']);
    $no_rekening = mysqli_real_escape_string($conn, $_POST['no_rekening']);
    $atas_nama = mysqli_real_escape_string($conn, $_POST['atas_nama']);

    // Validasi penarikan minimal Rp 10.000
    if ($jumlah < 10000) {
        header("Location: dompet.php?error=" . urlencode("Minimal penarikan dana adalah Rp 10.000!"));
        exit;
    }

    // Ambil saldo saat ini
    $user_query = mysqli_query($conn, "SELECT saldo FROM user WHERE id_user = $user_id");
    $user_data = mysqli_fetch_assoc($user_query);
    $saldo_sekarang = isset($user_data['saldo']) ? intval($user_data['saldo']) : 0;

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

    $available_balance = max(0, $saldo_sekarang - $locked_balance);

    // Validasi apakah saldo tersedia mencukupi
    if ($available_balance >= $jumlah) {
        // Mulai transaksi database
        mysqli_begin_transaction($conn);

        try {
            // 1. Kurangi saldo penjual sementara (debet)
            $saldo_baru = $saldo_sekarang - $jumlah;
            mysqli_query($conn, "UPDATE user SET saldo = $saldo_baru WHERE id_user = $user_id");

            // 2. Catat permintaan penarikan dana
            mysqli_query($conn, "
                INSERT INTO penarikan_dana (id_user, jumlah, bank_tujuan, no_rekening, atas_nama, status, tanggal)
                VALUES ($user_id, $jumlah, '$bank_tujuan', '$no_rekening', '$atas_nama', 'Pending', NOW())
            ");

            // Commit transaksi jika sukses semua
            mysqli_commit($conn);
            header("Location: dompet.php?success=1");
            exit;
        } catch (Exception $e) {
            // Rollback jika terjadi kegagalan query
            mysqli_rollback($conn);
            header("Location: dompet.php?error=" . urlencode("Gagal memproses penarikan saldo. Silakan coba lagi."));
            exit;
        }
    } else {
        header("Location: dompet.php?error=" . urlencode("Saldo dompet Anda tidak mencukupi untuk melakukan penarikan!"));
        exit;
    }
} else {
    header("Location: dompet.php");
    exit;
}
?>
