<?php
session_start();
include "../config/koneksi.php";

/** @var mysqli $conn */

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // Cek tabel admin dulu (database-driven)
    $qAdmin = mysqli_query($conn, "SELECT * FROM admin WHERE username = '$username' LIMIT 1");
    $adminData = mysqli_fetch_assoc($qAdmin);

    $loginOk = false;

    if ($adminData) {
        // Coba verifikasi dengan password_verify (hash bcrypt)
        if (password_verify($password, $adminData['password'])) {
            $loginOk = true;
        }
        // Fallback: password plain-text (untuk akun lama sebelum di-hash)
        elseif ($password === $adminData['password']) {
            $loginOk = true;
        }
    }

    if ($loginOk) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user']      = $adminData['username'];
        $_SESSION['admin_nama']      = isset($adminData['nama_lengkap']) ? $adminData['nama_lengkap'] : 'Administrator';
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>body { font-family: 'Poppins', sans-serif; }</style>
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md bg-gray-800 border border-gray-700 rounded-2xl p-8 shadow-xl">
        <div class="mb-6 text-center">
            <h2 class="text-2xl font-bold text-white tracking-wide">ADMIN<span class="text-indigo-400">PANEL</span></h2>
            <p class="text-xs text-gray-400 mt-1">Silakan masuk untuk mengelola aplikasi top-up game</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 text-xs p-3 rounded-xl mb-4 text-center">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Username</label>
                <input type="text" name="username" placeholder="Masukkan username" ... required
                    class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Password</label>
                <input type="password" name="password" placeholder="••••••••" required
                    class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
            </div>

            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-xl text-sm shadow-lg shadow-indigo-600/20 transition transform active:scale-[0.98] pt-2">
                Masuk Ke Panel
            </button>
        </form>
    </div>

</body>
</html>