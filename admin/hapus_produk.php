<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

include "../config/koneksi.php";

/** @var mysqli $conn */

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    // Ambil info gambar produk sebelum dihapus
    $query = mysqli_query($conn, "SELECT gambar FROM produk WHERE id_produk = $id");
    $row = mysqli_fetch_assoc($query);
    
    if ($row) {
        $gambar = $row['gambar'];
        // Hapus file gambar jika ada
        if (!empty($gambar) && file_exists("../uploads/" . $gambar)) {
            unlink("../uploads/" . $gambar);
        }
        
        // Hapus record dari DB
        mysqli_query($conn, "DELETE FROM produk WHERE id_produk = $id");
    }
}

header("Location: produk.php");
exit;
?>