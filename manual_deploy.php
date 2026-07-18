<?php
// ============================================================
// TOPUPIN — Full Auto Deploy Script
// Akses via: https://topupinweb.my.id/manual_deploy.php?token=DEPLOY_SECRET
// ============================================================

define('DEPLOY_TOKEN', 'topupin_deploy_2024_secret');

$src     = '/home/ekovmljg/repositories/topupin_web';
$dst_web = '/home/ekovmljg/public_html';
$dst_bot = '/home/ekovmljg/public_html/bot';

header('Content-Type: text/html; charset=utf-8');

$token = $_GET['token'] ?? $_POST['token'] ?? '';
if ($token !== DEPLOY_TOKEN) {
    http_response_code(403);
    die('<h2 style="color:red;font-family:monospace;">403 Forbidden — Token salah.<br>Akses dengan: ?token=TOKEN_ANDA</h2>');
}

$step = $_GET['step'] ?? 'all';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>TopUpin Auto Deploy</title>
<style>
body{font-family:monospace;background:#0b0f19;color:#e2e8f0;padding:20px;max-width:960px}
h1{color:#818cf8}h3{color:#6ee7b7;border-bottom:1px solid #374151;padding-bottom:6px}
.ok{color:#4ade80}.err{color:#f87171}.warn{color:#fbbf24}.info{color:#93c5fd}
pre{background:#1f2937;padding:12px;border-radius:8px;overflow-x:auto;font-size:12px;white-space:pre-wrap;word-wrap:break-word}
.btn{display:inline-block;background:#4f46e5;color:white;padding:8px 16px;border-radius:6px;text-decoration:none;margin:3px;font-size:13px}
.btn-green{background:#16a34a}.section{background:#111827;border:1px solid #1f2937;border-radius:8px;padding:15px;margin:15px 0}
.badge-ok{background:#14532d;color:#4ade80;padding:2px 8px;border-radius:4px;font-size:12px}
.badge-err{background:#7f1d1d;color:#f87171;padding:2px 8px;border-radius:4px;font-size:12px}
.badge-skip{background:#374151;color:#9ca3af;padding:2px 8px;border-radius:4px;font-size:12px}
</style>
</head>
<body>
<h1>?? TopUpin — Auto Deploy Dashboard</h1>
<p class="warn">?? Hapus atau proteksi file ini setelah selesai!</p>
<nav style="margin:15px 0">
<a href="?token=<?=DEPLOY_TOKEN?>&step=all"    class="btn btn-green">?? Full Deploy</a>
<a href="?token=<?=DEPLOY_TOKEN?>&step=sync"   class="btn">?? 1. Sync File</a>
<a href="?token=<?=DEPLOY_TOKEN?>&step=npm"    class="btn">?? 2. npm install</a>
<a href="?token=<?=DEPLOY_TOKEN?>&step=prisma" class="btn">??? 3. Prisma Generate</a>
<a href="?token=<?=DEPLOY_TOKEN?>&step=restart" class="btn">?? 4. Restart App</a>
<a href="?token=<?=DEPLOY_TOKEN?>&step=diag"   class="btn">?? Diagnostik</a>
</nav>
<hr style="border-color:#374151">
<?php

function run_cmd($cmd) {
    if (function_exists('exec')) {
        exec($cmd . ' 2>&1', $lines, $code);
        return ['output' => implode("\n", $lines), 'code' => $code, 'available' => true];
    }
    if (function_exists('shell_exec')) {
        $out = shell_exec($cmd . ' 2>&1');
        return ['output' => trim($out ?? ''), 'code' => 0, 'available' => true];
    }
    if (function_exists('proc_open')) {
        $desc = [0 => ['pipe','r'], 1 => ['pipe','w'], 2 => ['pipe','w']];
        $proc = proc_open($cmd, $desc, $pipes);
        if (is_resource($proc)) {
            fclose($pipes[0]);
            $out = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
            fclose($pipes[1]); fclose($pipes[2]);
            $code = proc_close($proc);
            return ['output' => trim($out), 'code' => $code, 'available' => true];
        }
    }
    return ['output' => 'Semua shell functions dinonaktifkan.', 'code' => -1, 'available' => false];
}

function shell_available() {
    return function_exists('exec') || function_exists('shell_exec') || function_exists('proc_open');
}

function find_binary(array $candidates): string {
    foreach ($candidates as $c) {
        $test = run_cmd("$c --version");
        if ($test['available'] && $test['code'] === 0 && $test['output']) return $c;
    }
    return '';
}

function sync_directory($src, $dst, $exclude = []): array {
    $copied = 0; $failed = 0;
    if (!is_dir($src)) return ['ok' => false, 'copied' => 0, 'failed' => 0, 'msg' => "Folder sumber tidak ada: $src"];
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
    return ['ok' => $failed === 0, 'copied' => $copied, 'failed' => $failed, 'msg' => ''];
}

// DIAGNOSTIK
if ($step === 'diag' || $step === 'all') {
    global $src, $dst_web, $dst_bot;
    echo "<div class='section'><h3>?? Diagnostik Lingkungan Server</h3>";
    echo "<b>Shell Functions:</b><br>";
    foreach (['exec','shell_exec','proc_open','system','passthru'] as $fn) {
        $ok = function_exists($fn);
        echo "  <code>$fn</code>: " . ($ok ? "<span class='badge-ok'>Aktif</span>" : "<span class='badge-skip'>Disabled</span>") . "<br>";
    }
    echo "<br><b>Folder Paths:</b><br>";
    foreach ([
        'Repo GitHub'     => '/home/ekovmljg/repositories/topupin_web',
        'public_html'     => '/home/ekovmljg/public_html',
        'Bot folder'      => '/home/ekovmljg/public_html/bot',
        'Bot dist/app.js' => '/home/ekovmljg/public_html/bot/dist/app.js',
        'node_modules'    => '/home/ekovmljg/public_html/bot/node_modules',
        'Bot .env'        => '/home/ekovmljg/public_html/bot/.env',
        'nodevenv npm'    => '/home/ekovmljg/nodevenv/public_html/bot/22/bin/npm',
    ] as $label => $path) {
        $ok = is_dir($path) || is_file($path);
        echo "  $label: " . ($ok ? "<span class='badge-ok'>Ada</span>" : "<span class='badge-err'>Tidak Ada</span>") . " <code style='font-size:11px'>$path</code><br>";
    }
    if (shell_available()) {
        echo "<br><b>Node.js &amp; npm:</b><br>";
        foreach (['which node','which npm','which npx','node --version','npm --version','ls /home/ekovmljg/nodevenv/public_html/bot'] as $cmd) {
            $r = run_cmd($cmd);
            echo "  <code>$cmd</code>: <span class='info'>" . htmlspecialchars($r['output'] ?: '(tidak ditemukan)') . "</span><br>";
        }
    } else {
        echo "<br><span class='warn'>Shell functions dinonaktifkan — tidak bisa cek Node.js.</span><br>";
    }
    echo "</div>";
    if ($step === 'diag') { echo "</body></html>"; exit; }
}

// SYNC FILE
if ($step === 'sync' || $step === 'all') {
    global $src, $dst_web, $dst_bot;
    echo "<div class='section'><h3>?? Step 1 — Sinkronisasi File</h3>";
    echo "<b>Website PHP ? public_html:</b><br>";
    $r = sync_directory($src, $dst_web, ['.git','bot','node_modules','.cpanel.yml','manual_deploy.php']);
    echo $r['ok']
        ? "<span class='ok'>  ? {$r['copied']} file berhasil disinkronkan.</span><br>"
        : "<span class='err'>  ? {$r['failed']} gagal, {$r['copied']} berhasil. {$r['msg']}</span><br>";
    echo "<br><b>Bot Node.js ? ~/bot/:</b><br>";
    $r2 = sync_directory($src . '/bot', $dst_bot, ['node_modules','.env','data']);
    echo $r2['ok']
        ? "<span class='ok'>  ? {$r2['copied']} file berhasil disinkronkan.</span><br>"
        : "<span class='err'>  ? {$r2['failed']} gagal, {$r2['copied']} berhasil. {$r2['msg']}</span><br>";
    echo "</div>";
    if ($step === 'sync') { echo "</body></html>"; exit; }
}

// NPM INSTALL
if ($step === 'npm' || $step === 'all') {
    global $dst_bot;
    echo "<div class='section'><h3>?? Step 2 — npm install (production)</h3>";
    if (!shell_available()) {
        echo "<span class='warn'>?? Shell dinonaktifkan.</span><br>";
        echo "<b>Solusi manual:</b> cPanel ? Setup Node.js App ? <b>Run NPM Install</b><br>";
    } else {
        $npm = find_binary(['/home/ekovmljg/nodevenv/public_html/bot/22/bin/npm', '/usr/local/bin/npm','/usr/bin/npm','npm']);
        if ($npm) {
            echo "<span class='info'>npm ditemukan: <code>$npm</code></span><br>";
            echo "<b>Menjalankan:</b> <code>cd $dst_bot && $npm install --omit=dev</code><br><br>";
            $r = run_cmd("cd $dst_bot && $npm install --omit=dev");
            echo "<pre>" . htmlspecialchars($r['output']) . "</pre>";
            echo $r['code'] === 0
                ? "<span class='ok'>? npm install berhasil!</span><br>"
                : "<span class='err'>? Gagal (exit: {$r['code']}). Coba via cPanel ? Run NPM Install.</span><br>";
        } else {
            echo "<span class='err'>? npm tidak ditemukan di PATH.</span><br>";
            echo "<span class='warn'>Solusi: cPanel ? Setup Node.js App ? Run NPM Install</span><br>";
        }
    }
    echo "</div>";
    if ($step === 'npm') { echo "</body></html>"; exit; }
}

// PRISMA GENERATE
if ($step === 'prisma' || $step === 'all') {
    global $dst_bot;
    echo "<div class='section'><h3>??? Step 3 — Prisma Generate</h3>";
    if (!shell_available()) {
        echo "<span class='warn'>?? Shell dinonaktifkan. Jalankan via SSH: <code>npx prisma generate</code></span><br>";
    } else {
        $prisma_local = "$dst_bot/node_modules/.bin/prisma";
        if (file_exists($prisma_local)) {
            $cmd = "cd $dst_bot && $prisma_local generate";
            echo "<span class='info'>Prisma ditemukan di node_modules.</span><br>";
        } else {
            $npx = find_binary(['/home/ekovmljg/nodevenv/public_html/bot/22/bin/npx', '/usr/local/bin/npx','/usr/bin/npx','npx']);
            $cmd = $npx ? "cd $dst_bot && $npx prisma generate" : '';
        }
        if ($cmd) {
            echo "<b>Menjalankan:</b> <code>$cmd</code><br><br>";
            $r = run_cmd($cmd);
            echo "<pre>" . htmlspecialchars($r['output']) . "</pre>";
            $ok = $r['code'] === 0 || str_contains($r['output'], 'Generated Prisma Client');
            echo $ok
                ? "<span class='ok'>? Prisma generate berhasil!</span><br>"
                : "<span class='err'>? Gagal (exit: {$r['code']}). Pastikan npm install sudah berjalan.</span><br>";
        } else {
            echo "<span class='warn'>?? npx tidak ditemukan. Jalankan npm install dulu.</span><br>";
        }
    }
    echo "</div>";
    if ($step === 'prisma') { echo "</body></html>"; exit; }
}

// RESTART
if ($step === 'restart' || $step === 'all') {
    global $dst_bot;
    echo "<div class='section'><h3>?? Step 4 — Restart Node.js App</h3>";
    $tmp_dir = $dst_bot . '/tmp';
    $restart_file = $tmp_dir . '/restart.txt';
    @mkdir($tmp_dir, 0755, true);
    if (@touch($restart_file)) {
        echo "<span class='ok'>? Restart signal dikirim! (<code>$restart_file</code>)</span><br>";
        echo "<span class='info'>Passenger akan restart app dalam beberapa detik.</span><br>";
    } else {
        echo "<span class='warn'>?? Tidak bisa buat restart.txt. Restart manual: cPanel ? Setup Node.js App ? Restart</span><br>";
    }
    echo "<br><span class='info'>Cek status: <a href='https://topupinweb.my.id/bot/health' target='_blank' style='color:#818cf8'>https://topupinweb.my.id/bot/health</a></span><br>";
    echo "</div>";
    if ($step === 'restart') { echo "</body></html>"; exit; }
}

// SUMMARY
if ($step === 'all') {
    echo "<div style='background:#14532d;padding:15px;border-radius:8px;margin:20px 0'>";
    echo "<h3 style='color:#4ade80;margin:0 0 10px'>?? Full Deploy Selesai!</h3>";
    echo "Jika ada yang gagal, lakukan di cPanel ? Setup Node.js App:<br><br>";
    echo "1. Startup file: <code>dist/app.js</code><br>";
    echo "2. Klik <b>Run NPM Install</b><br>";
    echo "3. Set ENV: <code>DATABASE_URL=mysql://ekovmljg_topupin:topupinipin@localhost:3306/ekovmljg_topup_game</code><br>";
    echo "4. Klik <b>Restart</b><br><br>";
    echo "Cek: <a href='https://topupinweb.my.id/bot/health' target='_blank' style='color:#4ade80'>https://topupinweb.my.id/bot/health</a>";
    echo "</div>";
}
?>
</body>
</html>
