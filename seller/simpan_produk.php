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
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $nama_game = mysqli_real_escape_string($conn, $_POST['nama_game']);
    $nama_produk = mysqli_real_escape_string($conn, $_POST['nama_produk']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $harga = intval($_POST['harga']);
    $stok = intval($_POST['stok']);
    
    // Pastikan penjual biasa tidak bisa memilih kategori topup
    if ($kategori == 'topup') {
        $kategori = 'item';
    }

    // Handle Upload Gambar
    $gambar = "NULL";
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "../uploads/";
        
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_extension = strtolower(pathinfo($_FILES["gambar"]["name"], PATHINFO_EXTENSION));
        $new_filename = "PROD_" . time() . "_" . rand(100, 999) . "." . $file_extension;
        $target_file = $target_dir . $new_filename;

        $allowed_types = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($file_extension, $allowed_types)) {
            if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
                $gambar = "'$new_filename'";
            }
        }
    }

    // Query simpan ke database dengan id_user milik seller
    $query = "
        INSERT INTO produk (id_user, kategori, nama_game, nama_produk, deskripsi, harga, nominal, stok, gambar)
        VALUES ($user_id, '$kategori', '$nama_game', '$nama_produk', '$deskripsi', $harga, NULL, $stok, $gambar)
    ";

    if (mysqli_query($conn, $query)) {
        header("Location: produk.php");
        exit;
    } else {
        echo "<script>
                alert('Gagal menyimpan dagangan: " . mysqli_real_escape_string($conn, mysqli_error($conn)) . "');
                window.history.back();
              </script>";
        exit;
    }
} else {
    header("Location: produk.php");
    exit;
}
?>
