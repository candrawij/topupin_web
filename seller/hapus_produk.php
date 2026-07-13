<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: ../login.php");
    exit;
}

include "../config/koneksi.php";

/** @var mysqli $conn */
$user_id = intval($_SESSION['user_id']);
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    // Ambil info gambar produk dan pastikan kepemilikannya
    $query = mysqli_query($conn, "SELECT gambar, id_user FROM produk WHERE id_produk = $id");
    $row = mysqli_fetch_assoc($query);
    
    if ($row && intval($row['id_user']) === $user_id) {
        $gambar = $row['gambar'];
        // Hapus file gambar jika ada
        if (!empty($gambar) && file_exists("../uploads/" . $gambar)) {
            unlink("../uploads/" . $gambar);
        }
        
        // Hapus record dari DB
        mysqli_query($conn, "DELETE FROM produk WHERE id_produk = $id AND id_user = $user_id");
    }
}

header("Location: produk.php");
exit;
?>
