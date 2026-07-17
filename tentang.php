<?php
// c:\laragon\www\TopUpin\tentang.php
session_start();
include "config/koneksi.php";
/** @var mysqli $conn */

$total_produk  = mysqli_num_rows(mysqli_query($conn, "SELECT id_produk FROM produk"));
$total_trx     = mysqli_num_rows(mysqli_query($conn, "SELECT id_trx FROM transaksi WHERE status='Success'"));
$total_users   = mysqli_num_rows(mysqli_query($conn, "SELECT id_user FROM user"));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami — TopUpIn</title>
    <meta name="description" content="TopUpIn adalah platform marketplace gaming terpercaya untuk top-up game, jual beli akun, dan item game dengan sistem escrow aman.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #0b0f19; }
        .glass { background: rgba(17,24,39,0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.06); }
        .glass-card { background: rgba(30,41,59,0.5); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.08); transition: all 0.3s; }
        .glass-card:hover { border-color: rgba(99,102,241,0.4); transform: translateY(-2px); }
        .gradient-text { background: linear-gradient(135deg,#818cf8,#a78bfa); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
        @keyframes count { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
        .animate-in { animation: count 0.5s ease forwards; }
    </style>
</head>
<body class="text-gray-200 min-h-screen flex flex-col">

<!-- NAVBAR -->
<nav class="bg-gray-900/80 border-b border-gray-800 p-4 sticky top-0 z-50 backdrop-blur-md">
    <div class="container mx-auto flex justify-between items-center px-4">
        <a href="index.php" class="flex items-center space-x-2">
            <i class="fa-solid fa-gamepad text-2xl text-indigo-500"></i>
            <span class="text-xl font-extrabold tracking-wider text-white">TopUp<span class="text-indigo-400">In</span></span>
        </a>
        <div class="hidden md:flex items-center space-x-6 text-sm">
            <a href="index.php" class="text-gray-400 hover:text-white transition"><i class="fa-solid fa-house mr-1"></i> Home</a>
            <a href="catalog.php" class="text-gray-400 hover:text-white transition"><i class="fa-solid fa-store mr-1"></i> Catalog</a>
            <a href="tentang.php" class="text-white font-semibold"><i class="fa-solid fa-circle-info mr-1 text-indigo-400"></i> Tentang</a>
            <a href="demo.php" class="flex items-center gap-1.5 bg-indigo-600/20 hover:bg-indigo-600/40 text-indigo-300 font-semibold text-xs px-3 py-1.5 rounded-full border border-indigo-500/30 transition">
                <i class="fa-solid fa-flask text-xs"></i> Demo Guide
            </a>
        </div>
        <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true): ?>
        <a href="logout.php" class="text-xs text-gray-500 hover:text-white transition">Logout</a>
        <?php else: ?>
        <a href="login.php" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold px-4 py-2 rounded-xl transition">Login</a>
        <?php endif; ?>
    </div>
</nav>

<main class="flex-grow">

    <!-- HERO ABOUT -->
    <section class="relative overflow-hidden py-20 px-6">
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-900/20 via-transparent to-purple-900/20 pointer-events-none"></div>
        <div class="container mx-auto max-w-4xl text-center relative z-10">
            <div class="inline-flex items-center gap-2 bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-xs px-4 py-2 rounded-full mb-6 font-semibold">
                <i class="fa-solid fa-building-columns"></i> Proyek Mata Kuliah — Sistem Informasi
            </div>
            <h1 class="text-4xl md:text-5xl font-black text-white mb-6 leading-tight">
                Tentang <span class="gradient-text">TopUpIn</span>
            </h1>
            <p class="text-gray-400 text-base leading-relaxed max-w-2xl mx-auto">
                TopUpIn adalah platform marketplace gaming berbasis web yang dirancang untuk memfasilitasi transaksi top-up game, 
                jual-beli akun game, dan item game secara aman menggunakan sistem escrow (rekening bersama) terintegrasi dengan 
                notifikasi Telegram Bot secara real-time.
            </p>
        </div>
    </section>

    <!-- STATISTIK -->
    <section class="py-12 px-6 border-y border-gray-800/50">
        <div class="container mx-auto max-w-4xl">
            <div class="grid grid-cols-3 gap-6 text-center">
                <div>
                    <div class="text-4xl font-black text-indigo-400 mb-1"><?= $total_produk ?>+</div>
                    <div class="text-xs text-gray-500">Produk Tersedia</div>
                </div>
                <div>
                    <div class="text-4xl font-black text-emerald-400 mb-1"><?= $total_trx ?>+</div>
                    <div class="text-xs text-gray-500">Transaksi Selesai</div>
                </div>
                <div>
                    <div class="text-4xl font-black text-purple-400 mb-1"><?= $total_users ?>+</div>
                    <div class="text-xs text-gray-500">Pengguna Terdaftar</div>
                </div>
            </div>
        </div>
    </section>

    <!-- VISI & MISI -->
    <section class="py-16 px-6">
        <div class="container mx-auto max-w-4xl">
            <div class="grid md:grid-cols-2 gap-6">
                <div class="glass-card rounded-3xl p-7">
                    <div class="w-12 h-12 bg-indigo-500/10 rounded-2xl flex items-center justify-center text-2xl mb-4">🎯</div>
                    <h2 class="text-lg font-bold text-white mb-3">Visi Kami</h2>
                    <p class="text-gray-400 text-sm leading-relaxed">
                        Menjadi platform marketplace gaming terpercaya di Indonesia yang menghubungkan pembeli dan penjual 
                        akun, item, dan layanan top-up game dalam ekosistem yang aman, transparan, dan menguntungkan.
                    </p>
                </div>
                <div class="glass-card rounded-3xl p-7">
                    <div class="w-12 h-12 bg-purple-500/10 rounded-2xl flex items-center justify-center text-2xl mb-4">🚀</div>
                    <h2 class="text-lg font-bold text-white mb-3">Misi Kami</h2>
                    <ul class="text-gray-400 text-sm leading-relaxed space-y-2">
                        <li class="flex gap-2"><span class="text-indigo-400 font-bold flex-shrink-0">01.</span>Memberikan layanan top-up game yang cepat, murah, dan terpercaya.</li>
                        <li class="flex gap-2"><span class="text-indigo-400 font-bold flex-shrink-0">02.</span>Membangun ekosistem jual-beli akun game yang aman dengan sistem escrow.</li>
                        <li class="flex gap-2"><span class="text-indigo-400 font-bold flex-shrink-0">03.</span>Memberdayakan seller independen untuk berjualan dengan mudah dan menguntungkan.</li>
                        <li class="flex gap-2"><span class="text-indigo-400 font-bold flex-shrink-0">04.</span>Menghadirkan customer service yang responsif melalui live chat dan Telegram Bot.</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- FITUR UNGGULAN -->
    <section class="py-12 px-6">
        <div class="container mx-auto max-w-4xl">
            <h2 class="text-2xl font-bold text-white text-center mb-10">Fitur Platform</h2>
            <div class="grid sm:grid-cols-2 gap-4">
                <?php
                $features = [
                    ['🛒', 'Marketplace Multi-Kategori', 'Top-Up, Akun Game, dan Item Game tersedia dalam satu platform dengan katalog yang lengkap dan terorganisir.', 'border-indigo-500/20'],
                    ['🔒', 'Sistem Escrow (Rekening Bersama)', 'Dana pembeli ditahan oleh platform hingga transaksi diverifikasi oleh admin, memastikan keamanan kedua pihak.', 'border-emerald-500/20'],
                    ['💬', 'CS Live Chat Real-Time', 'Widget chat melayang di semua halaman memungkinkan pembeli menghubungi CS kapanpun dengan respons real-time via AJAX polling.', 'border-amber-500/20'],
                    ['🤖', 'Integrasi Telegram Bot', 'Notifikasi transaksi otomatis dikirim ke grup admin Telegram. Admin dapat menyetujui atau menolak transaksi langsung dari Telegram.', 'border-blue-500/20'],
                    ['💰', 'Dashboard Seller & Dompet', 'Seller memiliki dashboard mandiri untuk mengelola produk, memantau penjualan, dan menarik saldo ke rekening bank.', 'border-purple-500/20'],
                    ['📊', 'Panel Admin Komprehensif', 'Admin dapat mengelola produk, transaksi, verifikasi pembayaran, CS chat inbox, dan persetujuan pencairan dana seller.', 'border-rose-500/20'],
                ];
                foreach ($features as $f) {
                    echo "<div class='glass-card rounded-2xl p-5 border {$f[3]}'>";
                    echo "<div class='text-2xl mb-3'>{$f[0]}</div>";
                    echo "<h3 class='font-bold text-white text-sm mb-2'>{$f[1]}</h3>";
                    echo "<p class='text-gray-500 text-xs leading-relaxed'>{$f[2]}</p>";
                    echo "</div>";
                }
                ?>
            </div>
        </div>
    </section>

    <!-- TEKNOLOGI -->
    <section class="py-12 px-6">
        <div class="container mx-auto max-w-4xl">
            <h2 class="text-2xl font-bold text-white text-center mb-4">Stack Teknologi</h2>
            <p class="text-gray-500 text-sm text-center mb-10">Dibangun dengan teknologi modern yang relevan di industri</p>
            <div class="glass rounded-3xl p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center text-xs">
                    <?php
                    $stacks = [
                        ['🐘', 'PHP 8.x', 'Backend', 'Native MVC'],
                        ['🗄️', 'MySQL', 'Database', '13+ Tabel Relasional'],
                        ['📘', 'TypeScript', 'Bot Runtime', 'Node.js + grammY'],
                        ['🔧', 'Prisma ORM', 'Data Layer', 'Type-safe queries'],
                        ['🎨', 'TailwindCSS', 'Frontend', 'Utility-first CSS'],
                        ['🤖', 'Telegram API', 'Notifikasi', 'Webhook-based'],
                        ['🔒', 'AES-256-ECB', 'Keamanan', 'Deep-link encryption'],
                        ['☁️', 'cPanel + Git', 'Deployment', 'Auto-deploy .cpanel.yml'],
                    ];
                    foreach ($stacks as $s) {
                        echo "<div class='bg-gray-900/50 rounded-2xl p-4 border border-gray-800 hover:border-indigo-500/30 transition'>";
                        echo "<div class='text-2xl mb-2'>{$s[0]}</div>";
                        echo "<div class='font-bold text-white text-xs mb-0.5'>{$s[1]}</div>";
                        echo "<div class='text-[10px] text-indigo-400 font-semibold mb-1'>{$s[2]}</div>";
                        echo "<div class='text-[10px] text-gray-600'>{$s[3]}</div>";
                        echo "</div>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </section>

    <!-- ARSITEKTUR SISTEM -->
    <section class="py-12 px-6">
        <div class="container mx-auto max-w-4xl">
            <h2 class="text-2xl font-bold text-white text-center mb-10">Arsitektur Sistem</h2>
            <div class="glass rounded-3xl p-6">
                <div class="flex flex-col md:flex-row items-center justify-center gap-3 text-xs text-center">
                    <div class="bg-indigo-500/10 border border-indigo-500/30 rounded-2xl p-4 min-w-[120px]">
                        <div class="text-2xl mb-2">👤</div>
                        <div class="font-bold text-white mb-0.5">User Browser</div>
                        <div class="text-gray-500">Website PHP</div>
                    </div>
                    <div class="text-gray-600 font-bold text-lg rotate-90 md:rotate-0">⇆</div>
                    <div class="bg-gray-800/50 border border-gray-700 rounded-2xl p-4 min-w-[140px]">
                        <div class="text-2xl mb-2">🌐</div>
                        <div class="font-bold text-white mb-0.5">PHP Backend</div>
                        <div class="text-gray-500">cPanel + Apache</div>
                        <div class="text-[10px] text-indigo-400 mt-1">topupinweb.my.id</div>
                    </div>
                    <div class="text-gray-600 font-bold text-lg rotate-90 md:rotate-0">⇆</div>
                    <div class="bg-gray-800/50 border border-gray-700 rounded-2xl p-4 min-w-[120px]">
                        <div class="text-2xl mb-2">🗄️</div>
                        <div class="font-bold text-white mb-0.5">MySQL DB</div>
                        <div class="text-gray-500">topup_game</div>
                    </div>
                    <div class="text-gray-600 font-bold text-lg rotate-90 md:rotate-0">⇆</div>
                    <div class="bg-blue-500/10 border border-blue-500/30 rounded-2xl p-4 min-w-[130px]">
                        <div class="text-2xl mb-2">🤖</div>
                        <div class="font-bold text-white mb-0.5">Telegram Bot</div>
                        <div class="text-gray-500">Node.js + grammY</div>
                        <div class="text-[10px] text-blue-400 mt-1">@top_upin_bot</div>
                    </div>
                    <div class="text-gray-600 font-bold text-lg rotate-90 md:rotate-0">→</div>
                    <div class="bg-sky-500/10 border border-sky-500/30 rounded-2xl p-4 min-w-[120px]">
                        <div class="text-2xl mb-2">📱</div>
                        <div class="font-bold text-white mb-0.5">Admin Telegram</div>
                        <div class="text-gray-500">Grup Notifikasi</div>
                    </div>
                </div>
                <p class="text-center text-xs text-gray-600 mt-6">Alur: User beli → PHP insert DB → PHP POST ke Bot API → Bot kirim notif ke grup Telegram → Admin approve → Bot POST webhook ke PHP → PHP update status DB</p>
            </div>
        </div>
    </section>

    <!-- CTA DEMO -->
    <section class="py-16 px-6">
        <div class="container mx-auto max-w-2xl text-center">
            <div class="glass rounded-3xl p-10 border border-indigo-500/20">
                <div class="text-5xl mb-4">🎓</div>
                <h2 class="text-2xl font-bold text-white mb-3">Ingin Mencoba Semua Fitur?</h2>
                <p class="text-gray-500 text-sm mb-6">Gunakan halaman Demo Guide untuk login langsung ke berbagai role dan menjelajahi semua fitur platform tanpa perlu mendaftar.</p>
                <a href="demo.php" class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold px-8 py-3.5 rounded-2xl text-sm transition shadow-lg shadow-indigo-600/20">
                    <i class="fa-solid fa-flask"></i> Buka Halaman Demo Guide
                </a>
                <div class="mt-4">
                    <a href="catalog.php" class="text-xs text-gray-600 hover:text-indigo-400 transition">atau langsung jelajahi katalog →</a>
                </div>
            </div>
        </div>
    </section>

</main>

<!-- FOOTER -->
<footer class="border-t border-gray-800 py-8 px-6">
    <div class="container mx-auto max-w-4xl flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-gray-600">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-gamepad text-indigo-500"></i>
            <span class="font-bold text-gray-400">TopUpIn</span>
            <span>— Platform Marketplace Gaming</span>
        </div>
        <div class="flex gap-4">
            <a href="index.php" class="hover:text-indigo-400 transition">Beranda</a>
            <a href="catalog.php" class="hover:text-indigo-400 transition">Katalog</a>
            <a href="demo.php" class="hover:text-indigo-400 transition">Demo Guide</a>
            <a href="admin/login.php" class="hover:text-indigo-400 transition">Admin</a>
        </div>
        <div>Proyek Mata Kuliah © <?= date('Y') ?></div>
    </div>
</footer>

<?php include "components/chat_widget.php"; ?>

</body>
</html>
