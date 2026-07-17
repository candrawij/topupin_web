<?php
// c:\laragon\www\TopUpin\demo_login.php
// Quick Login Handler untuk Demo Akademik
// Memungkinkan login instan ke semua role tanpa ketik password

session_start();
include "config/koneksi.php";

/** @var mysqli $conn */

$role = isset($_GET['role']) ? $_GET['role'] : '';

if ($role === 'user') {
    // Login sebagai User Demo
    $q = mysqli_query($conn, "SELECT * FROM user WHERE email = 'demo@topupin.id' LIMIT 1");
    if ($user = mysqli_fetch_assoc($q)) {
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_id']        = $user['id_user'];
        $_SESSION['user_nama']      = $user['nama'];
        $_SESSION['user_email']     = $user['email'];
        header("Location: index.php?demo=1");
        exit;
    } else {
        // Akun demo belum ada, arahkan ke setup
        header("Location: demo.php?error=user_not_found");
        exit;
    }
}

elseif ($role === 'seller') {
    // Login sebagai Seller Demo
    $q = mysqli_query($conn, "SELECT * FROM user WHERE email = 'seller@topupin.id' LIMIT 1");
    if ($user = mysqli_fetch_assoc($q)) {
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_id']        = $user['id_user'];
        $_SESSION['user_nama']      = $user['nama'];
        $_SESSION['user_email']     = $user['email'];
        header("Location: seller/dashboard.php?demo=1");
        exit;
    } else {
        header("Location: demo.php?error=seller_not_found");
        exit;
    }
}

elseif ($role === 'admin') {
    // Login sebagai Admin
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_user']      = 'admin';
    $_SESSION['admin_nama']      = 'Administrator Utama';
    header("Location: admin/dashboard.php?demo=1");
    exit;
}

else {
    header("Location: demo.php");
    exit;
}
