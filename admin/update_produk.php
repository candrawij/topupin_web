<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

include "../config/koneksi.php";

/** @var mysqli $conn */

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_produk = intval($_POST['id_produk']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $nama_game = mysqli_real_escape_string($conn, $_POST['nama_game']);
    $nama_produk = mysqli_real_escape_string($conn, $_POST['nama_produk']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $harga = intval($_POST['harga']);
    
    // Set default nominal & stok berdasarkan kategori
    $nominal = "NULL";
    $stok = -1;
    
    if ($kategori == 'topup') {
        if (!empty($_POST['nominal'])) {
            $nominal = intval($_POST['nominal']);
        }
    } else {
        if (isset($_POST['stok'])) {
            $stok = intval($_POST['stok']);
        }
    }

    // Ambil data produk lama untuk info gambar
    $query_old = mysqli_query($conn, "SELECT gambar FROM produk WHERE id_produk = $id_produk");
    $old_data = mysqli_fetch_assoc($query_old);
    $old_gambar = isset($old_data['gambar']) ? $old_data['gambar'] : '';

    // Handle Upload Gambar Baru
    $gambar_sql = "";
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
                $gambar_sql = ", gambar = '$new_filename'";
                
                // Hapus gambar lama jika ada
                if (!empty($old_gambar) && file_exists($target_dir . $old_gambar)) {
                    unlink($target_dir . $old_gambar);
                }
            }
        }
    }

    // Query update ke database
    $query = "
        UPDATE produk SET 
            kategori = '$kategori',
            nama_game = '$nama_game',
            nama_produk = '$nama_produk',
            deskripsi = '$deskripsi',
            harga = $harga,
            nominal = $nominal,
            stok = $stok
            $gambar_sql
        WHERE id_produk = $id_produk
    ";

    if (mysqli_query($conn, $query)) {
        header("Location: produk.php");
        exit;
    } else {
        echo "<script>
                alert('Gagal memperbarui produk: " . mysqli_real_escape_string($conn, mysqli_error($conn)) . "');
                window.history.back();
              </script>";
        exit;
    }
} else {
    header("Location: produk.php");
    exit;
}
?>