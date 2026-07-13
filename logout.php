<?php
session_start();
// Hapus semua session pengguna
unset($_SESSION['user_logged_in']);
unset($_SESSION['user_id']);
unset($_SESSION['user_nama']);
unset($_SESSION['user_email']);

header("Location: index.php");
exit;
?>
