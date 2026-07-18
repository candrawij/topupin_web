<?php
// ============================================================
// TOPUPIN — Website Deploy Script (PHP Only)
// Akses: https://topupinweb.my.id/manual_deploy.php?token=DEPLOY_SECRET
// ============================================================

define('DEPLOY_TOKEN', 'topupin_deploy_2024_secret');

$src     = '/home/ekovmljg/repositories/topupin_web';
$dst_web = '/home/ekovmljg/public_html';

header('Content-Type: text/html; charset=utf-8');

$token = $_GET['token'] ?? $_POST['token'] ?? '';
if ($token !== DEPLOY_TOKEN) {
    http_response_code(403);
    die('<h2 style="color:red;font-family:monospace;">403 Forbidden — Token salah.<br>Akses dengan: ?token=TOKEN_ANDA</h2>');
}

$step = $_GET['step'] ?? 'deploy';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>TopUpin — Website Deploy</title>
<style>
body{font-family:monospace;background:#0b0f19;color:#e2e8f0;padding:20px;max-width:800px}
h1{color:#818cf8}h3{color:#6ee7b7;border-bottom:1px solid #374151;padding-bottom:6px}
.ok{color:#4ade80}.err{color:#f87171}.warn{color:#fbbf24}.info{color:#93c5fd}
pre{background:#1f2937;padding:12px;border-radius:8px;overflow-x:auto;font-size:12px;white-space:pre-wrap}
.btn{display:inline-block;background:#4f46e5;color:white;padding:8px 16px;border-radius:6px;text-decoration:none;margin:3px;font-size:13px}
.btn-green{background:#16a34a}.section{background:#111827;border:1px solid #1f2937;border-radius:8px;padding:15px;margin:15px 0}
.badge-ok{background:#14532d;color:#4ade80;padding:2px 8px;border-radius:4px;font-size:12px}
.badge-err{background:#7f1d1d;color:#f87171;padding:2px 8px;border-radius:4px;font-size:12px}
</style>
</head>
<body>
<h1>🌐 TopUpin — Website Deploy</h1>
<p class="warn">⚠️ Hapus file ini setelah website berjalan!</p>
<nav style="margin:15px 0">
  <a href="?token=<?=DEPLOY_TOKEN?>&step=deploy"  class="btn btn-green">🚀 Deploy Website</a>
  <a href="?token=<?=DEPLOY_TOKEN?>&step=diag"    class="btn">🔍 Diagnostik</a>
  <a href="?token=<?=DEPLOY_TOKEN?>&step=db"      class="btn">🗄️ Cek Database</a>
</nav>
<hr style="border-color:#374151">
<?php

function sync_directory($src, $dst, $exclude = []): array {
    $copied = 0; $failed = 0;
    if (!is_dir($src)) return ['ok'=>false,'copied'=>0,'failed'=>0,'msg'=>"Folder tidak ada: $src"];
    @mkdir($dst, 0755, true);
    $dir = opendir($src);
    while (false !== ($file = readdir($dir))) {
        if ($file === '.' || $file === '..') continue;
        if (in_array($file, $exclude)) continue;
        $sf = "$src/$file"; $df = "$dst/$file";
        if (is_dir($sf)) {
            $sub = sync_directory($sf, $df, $exclude);
            $copied += $sub['copied']; $failed += $sub['failed'];
        } else {
            if (@copy($sf, $df)) $copied++; else $failed++;
        }
    }
    closedir($dir);
    return ['ok'=>$failed===0,'copied'=>$copied,'failed'=>$failed,'msg'=>''];
}

// DIAGNOSTIK
if ($step === 'diag') {
    echo "<div class='section'><h3>🔍 Diagnostik Server</h3>";
    echo "<b>Folder Paths:</b><br>";
    foreach ([
        'Repo GitHub' => '/home/ekovmljg/repositories/topupin_web',
        'public_html' => '/home/ekovmljg/public_html',
        'index.php'   => '/home/ekovmljg/public_html/index.php',
        'config/'     => '/home/ekovmljg/public_html/config',
    ] as $label => $path) {
        $ok = is_dir($path) || is_file($path);
        echo "  $label: " . ($ok ? "<span class='badge-ok'>Ada</span>" : "<span class='badge-err'>Tidak Ada</span>") . " <code style='font-size:11px'>$path</code><br>";
    }
    echo "<br><b>PHP Info:</b><br>";
    echo "  PHP Version: <span class='info'>" . PHP_VERSION . "</span><br>";
    echo "  mysqli: <span class='" . (extension_loaded('mysqli') ? 'ok' : 'err') . "'>" . (extension_loaded('mysqli') ? '✅ Aktif' : '❌ Tidak ada') . "</span><br>";
    echo "  curl: <span class='" . (extension_loaded('curl') ? 'ok' : 'err') . "'>" . (extension_loaded('curl') ? '✅ Aktif' : '❌ Tidak ada') . "</span><br>";
    echo "  Server: <span class='info'>" . ($_SERVER['SERVER_SOFTWARE'] ?? '-') . "</span><br>";
    echo "</div>";
}

// CEK DATABASE
elseif ($step === 'db') {
    echo "<div class='section'><h3>🗄️ Cek Koneksi Database</h3>";
    $db_host = 'localhost';
    $db_user = 'ekovmljg_topupin';
    $db_pass = 'topupinipin';
    $db_name = 'ekovmljg_topup_game';
    $conn = @mysqli_connect($db_host, $db_user, $db_pass, $db_name);
    if ($conn) {
        echo "<span class='ok'>✅ Koneksi database berhasil!</span><br><br>";
        $tables_result = mysqli_query($conn, "SHOW TABLES");
        $tables = [];
        while ($row = mysqli_fetch_row($tables_result)) $tables[] = $row[0];
        echo "<b>Tabel yang ada (" . count($tables) . "):</b><br>";
        $required = ['admin','user','produk','transaksi'];
        foreach ($required as $t) {
            $ok = in_array($t, $tables);
            echo "  $t: " . ($ok ? "<span class='badge-ok'>Ada</span>" : "<span class='badge-err'>Belum ada — jalankan migrasi</span>") . "<br>";
        }
        if (!in_array('produk', $tables)) {
            echo "<br><span class='warn'>⚠️ Tabel belum ada. Akses: <a href='/cpanel_setup.php?step=migrate' style='color:#818cf8'>/cpanel_setup.php?step=migrate</a></span><br>";
        }
        mysqli_close($conn);
    } else {
        echo "<span class='err'>❌ Koneksi gagal: " . mysqli_connect_error() . "</span><br>";
        echo "<span class='warn'>Periksa kredensial database di config/koneksi.php</span><br>";
    }
    echo "</div>";
}

// DEPLOY WEBSITE
else {
    echo "<div class='section'><h3>🚀 Deploy Website PHP → public_html</h3>";
    if (!is_dir($src)) {
        echo "<span class='err'>❌ Repo GitHub tidak ditemukan di: $src</span><br>";
        echo "<span class='warn'>Pastikan repo sudah di-clone via cPanel → Git Version Control</span><br>";
    } else {
        $r = sync_directory($src, $dst_web, [
            '.git', 'bot', 'node_modules', '.cpanel.yml',
            'manual_deploy.php', 'bot_deploy.php',
            'bot_upload.zip', 'image.png'
        ]);
        if ($r['ok']) {
            echo "<span class='ok'>✅ {$r['copied']} file berhasil disinkronkan ke public_html!</span><br>";
        } else {
            echo "<span class='err'>❌ {$r['failed']} file gagal, {$r['copied']} berhasil.</span><br>";
        }
    }
    echo "<br><b>Langkah selanjutnya:</b><br>";
    echo "1. <a href='?token=<?=DEPLOY_TOKEN?>&step=db' style='color:#818cf8'>Cek koneksi database</a><br>";
    echo "2. Jika tabel belum ada: <a href='/cpanel_setup.php?step=migrate' style='color:#818cf8'>Jalankan migrasi</a><br>";
    echo "3. Test website: <a href='/' style='color:#818cf8'>Buka homepage</a><br>";
    echo "4. Setelah website jalan, setup bot via <a href='/bot_deploy.php?token=<?=DEPLOY_TOKEN?>' style='color:#818cf8'>bot_deploy.php</a><br>";
    echo "</div>";
}
?>
</body>
</html>