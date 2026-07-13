<?php
session_start();
include "config/koneksi.php";

/** @var mysqli $conn */

if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header("Location: index.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM user WHERE email = '$email'");
    if (mysqli_num_rows($query) > 0) {
        $user = mysqli_fetch_assoc($query);
        
        // Verifikasi password hash
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['user_nama'] = $user['nama'];
            $_SESSION['user_email'] = $user['email'];
            
            header("Location: index.php");
            exit;
        } else {
            $error = "Password yang Anda masukkan salah!";
        }
    } else {
        $error = "Akun email tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk ke Akun - TopUpIn</title>
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
<body class="text-gray-200 min-h-screen flex items-center justify-center p-6">

    <div class="w-full max-w-md bg-gray-800 border border-gray-700 rounded-3xl p-8 shadow-2xl space-y-6 glass">
        <div class="text-center">
            <a href="index.php" class="inline-flex items-center space-x-2 mb-3">
                <i class="fa-solid fa-gamepad text-3xl text-indigo-500"></i>
                <span class="text-2xl font-black text-white">TopUp<span class="text-indigo-400">In</span></span>
            </a>
            <h2 class="text-lg font-bold text-white">Masuk Ke Akun Anda</h2>
            <p class="text-xs text-gray-500 mt-1">Silakan masukkan detail akun Anda untuk masuk</p>
        </div>

        <?php if (!empty($error)) { ?>
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 text-xs p-3.5 rounded-xl text-center">
                <i class="fa-solid fa-circle-exclamation mr-1.5"></i> <?= $error ?>
            </div>
        <?php } ?>

        <form action="" method="POST" class="space-y-4">
            <!-- Email -->
            <div>
                <label class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wide">Alamat Email</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-500">
                        <i class="fa-solid fa-envelope text-sm"></i>
                    </span>
                    <input type="email" name="email" placeholder="nama@email.com" required
                        class="w-full bg-gray-950 border border-gray-850 rounded-xl pl-10 pr-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
                </div>
            </div>

            <!-- Password -->
            <div>
                <label class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wide">Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-500">
                        <i class="fa-solid fa-lock text-sm"></i>
                    </span>
                    <input type="password" name="password" placeholder="••••••••" required
                        class="w-full bg-gray-950 border border-gray-850 rounded-xl pl-10 pr-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
                </div>
            </div>

            <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold py-3.5 rounded-xl text-sm transition shadow-lg shadow-indigo-600/20">
                Masuk
            </button>
        </form>

        <div class="text-center text-xs text-gray-500">
            Belum memiliki akun? <a href="register.php" class="text-indigo-400 hover:underline font-semibold">Daftar Akun Baru</a>
        </div>
    </div>

</body>
</html>
