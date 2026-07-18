<?php
// ============================================================
// TOPUPIN — Bot Deploy Script (Node.js Setup)
// Akses: https://topupinweb.my.id/bot_deploy.php?token=DEPLOY_SECRET
// Jalankan SETELAH website PHP sudah berjalan normal!
// ============================================================

define('DEPLOY_TOKEN', 'topupin_deploy_2024_secret');

$src     = '/home/ekovmljg/repositories/topupin_web';
$dst_bot = '/home/ekovmljg/public_html/bot';
$nodevenv_activate = '/home/ekovmljg/nodevenv/public_html/bot/22/bin/activate';

header('Content-Type: text/html; charset=utf-8');

$token = $_GET['token'] ?? $_POST['token'] ?? '';
if ($token !== DEPLOY_TOKEN) {
    http_response_code(403);
    die('<h2 style="color:red;font-family:monospace;">403 Forbidden — Token salah.</h2>');
}

$step = $_GET['step'] ?? 'all';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>TopUpin — Bot Deploy</title>
<style>
body{font-family:monospace;background:#0b0f19;color:#e2e8f0;padding:20px;max-width:800px}
h1{color:#818cf8}h3{color:#6ee7b7;border-bottom:1px solid #374151;padding-bottom:6px}
.ok{color:#4ade80}.err{color:#f87171}.warn{color:#fbbf24}.info{color:#93c5fd}
pre{background:#1f2937;padding:12px;border-radius:8px;overflow-x:auto;font-size:12px;white-space:pre-wrap;word-wrap:break-word}
.btn{display:inline-block;background:#4f46e5;color:white;padding:8px 16px;border-radius:6px;text-decoration:none;margin:3px;font-size:13px}
.btn-green{background:#16a34a}.btn-yellow{background:#b45309}
.section{background:#111827;border:1px solid #1f2937;border-radius:8px;padding:15px;margin:15px 0}
.badge-ok{background:#14532d;color:#4ade80;padding:2px 8px;border-radius:4px;font-size:12px}
.badge-err{background:#7f1d1d;color:#f87171;padding:2px 8px;border-radius:4px;font-size:12px}
</style>
</head>
<body>
<h1>🤖 TopUpin — Bot Deploy</h1>
<p class="warn">⚠️ Jalankan ini SETELAH website PHP sudah berjalan!</p>
<nav style="margin:15px 0">
  <a href="?token=<?=DEPLOY_TOKEN?>&step=all"     class="btn btn-green">🚀 Full Bot Setup</a>
  <a href="?token=<?=DEPLOY_TOKEN?>&step=sync"    class="btn">📁 1. Sync File Bot</a>
  <a href="?token=<?=DEPLOY_TOKEN?>&step=npm"     class="btn">📦 2. npm install</a>
  <a href="?token=<?=DEPLOY_TOKEN?>&step=prisma"  class="btn">🗄️ 3. Prisma Generate</a>
  <a href="?token=<?=DEPLOY_TOKEN?>&step=restart" class="btn btn-yellow">🔄 4. Restart App</a>
  <a href="?token=<?=DEPLOY_TOKEN?>&step=diag"    class="btn">🔍 Diagnostik</a>
</nav>
<hr style="border-color:#374151">
<?php

function run_cmd($cmd) {
    if (function_exists('exec')) {
        exec($cmd . ' 2>&1', $lines, $code);
        return ['output'=>implode("\n",$lines),'code'=>$code,'available'=>true];
    }
    if (function_exists('shell_exec')) {
        $out = shell_exec($cmd . ' 2>&1');
        return ['output'=>trim($out??''),'code'=>0,'available'=>true];
    }
    if (function_exists('proc_open')) {
        $desc=[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']];
        $proc=proc_open($cmd,$desc,$pipes);
        if(is_resource($proc)){
            fclose($pipes[0]);
            $out=stream_get_contents($pipes[1]).stream_get_contents($pipes[2]);
            fclose($pipes[1]);fclose($pipes[2]);
            $code=proc_close($proc);
            return ['output'=>trim($out),'code'=>$code,'available'=>true];
        }
    }
    return ['output'=>'Shell dinonaktifkan.','code'=>-1,'available'=>false];
}

function shell_available() {
    return function_exists('exec')||function_exists('shell_exec')||function_exists('proc_open');
}

function sync_directory($src, $dst, $exclude=[]): array {
    $copied=0;$failed=0;
    if(!is_dir($src)) return ['ok'=>false,'copied'=>0,'failed'=>0,'msg'=>"Folder tidak ada: $src"];
    @mkdir($dst,0755,true);
    $dir=opendir($src);
    while(false!==($file=readdir($dir))){
        if($file==='.'||$file==='..') continue;
        if(in_array($file,$exclude)) continue;
        $sf="$src/$file";$df="$dst/$file";
        if(is_dir($sf)){$sub=sync_directory($sf,$df,$exclude);$copied+=$sub['copied'];$failed+=$sub['failed'];}
        else{if(@copy($sf,$df))$copied++;else $failed++;}
    }
    closedir($dir);
    return ['ok'=>$failed===0,'copied'=>$copied,'failed'=>$failed,'msg'=>''];
}

// DIAGNOSTIK
if ($step === 'diag' || $step === 'all') {
    global $dst_bot, $nodevenv_activate;
    echo "<div class='section'><h3>🔍 Diagnostik Bot</h3>";
    echo "<b>Shell Functions:</b><br>";
    foreach(['exec','shell_exec','proc_open'] as $fn)
        echo "  <code>$fn</code>: " . (function_exists($fn) ? "<span class='badge-ok'>Aktif</span>" : "<span class='badge-err'>Disabled</span>") . "<br>";

    echo "<br><b>Folder &amp; File Penting:</b><br>";
    foreach([
        'Repo GitHub'      => '/home/ekovmljg/repositories/topupin_web',
        'Bot folder'       => $dst_bot,
        'dist/app.js'      => "$dst_bot/dist/app.js",
        'package.json'     => "$dst_bot/package.json",
        'prisma.config.ts' => "$dst_bot/prisma.config.ts",
        'node_modules'     => "$dst_bot/node_modules",
        '.env (bot)'       => "$dst_bot/.env",
        'nodevenv activate'=> $nodevenv_activate,
    ] as $label => $path) {
        $ok = is_dir($path)||is_file($path);
        echo "  $label: " . ($ok ? "<span class='badge-ok'>Ada</span>" : "<span class='badge-err'>Tidak Ada</span>") . " <code style='font-size:11px'>$path</code><br>";
    }

    if (shell_available()) {
        echo "<br><b>Node.js via nodevenv:</b><br>";
        $cmds = [
            "bash -c 'source $nodevenv_activate && node --version'",
            "bash -c 'source $nodevenv_activate && npm --version'",
            "ls /home/ekovmljg/nodevenv/public_html/bot",
        ];
        foreach ($cmds as $cmd) {
            $r = run_cmd($cmd);
            echo "  <code>" . htmlspecialchars($cmd) . "</code>: <span class='info'>" . htmlspecialchars($r['output']?:'(tidak ada output)') . "</span><br>";
        }
    }
    echo "</div>";
    if ($step === 'diag') { echo "</body></html>"; exit; }
}

// SYNC FILE BOT
if ($step === 'sync' || $step === 'all') {
    global $src, $dst_bot;
    echo "<div class='section'><h3>📁 Step 1 — Sync File Bot</h3>";
    if (!is_dir($src)) {
        echo "<span class='err'>❌ Repo tidak ditemukan: $src</span><br>";
    } else {
        $r = sync_directory("$src/bot", $dst_bot, ['node_modules','.env','data']);
        echo $r['ok']
            ? "<span class='ok'>✅ {$r['copied']} file bot berhasil disinkronkan.</span><br>"
            : "<span class='err'>❌ {$r['failed']} gagal, {$r['copied']} berhasil.</span><br>";
    }
    echo "</div>";
    if ($step === 'sync') { echo "</body></html>"; exit; }
}

// NPM INSTALL
if ($step === 'npm' || $step === 'all') {
    global $dst_bot, $nodevenv_activate;
    echo "<div class='section'><h3>📦 Step 2 — npm install</h3>";
    if (!shell_available()) {
        echo "<span class='warn'>⚠️ Shell dinonaktifkan.</span><br>";
        echo "<b>Solusi:</b> cPanel → Setup Node.js App → <b>Run NPM Install</b><br>";
    } elseif (!file_exists($nodevenv_activate)) {
        echo "<span class='err'>❌ nodevenv belum dibuat.</span><br>";
        echo "<span class='warn'>Solusi: Buat Node.js App dulu di cPanel → Setup Node.js App<br>";
        echo "Application root: <code>public_html/bot</code> | Startup file: <code>dist/app.js</code><br>";
        echo "Lalu klik <b>Create</b> dan klik <b>Run NPM Install</b></span><br>";
    } else {
        $cmd = "bash -c 'source $nodevenv_activate && cd $dst_bot && npm install --omit=dev'";
        echo "<span class='info'>Menjalankan dengan source activate...</span><br>";
        echo "<b>CMD:</b> <code>$cmd</code><br><br>";
        $r = run_cmd($cmd);
        echo "<pre>" . htmlspecialchars($r['output']) . "</pre>";
        echo $r['code'] === 0
            ? "<span class='ok'>✅ npm install berhasil!</span><br>"
            : "<span class='err'>❌ Gagal (exit: {$r['code']}). Gunakan cPanel → Run NPM Install sebagai alternatif.</span><br>";
    }
    echo "</div>";
    if ($step === 'npm') { echo "</body></html>"; exit; }
}

// PRISMA GENERATE
if ($step === 'prisma' || $step === 'all') {
    global $dst_bot, $nodevenv_activate;
    echo "<div class='section'><h3>🗄️ Step 3 — Prisma Generate</h3>";
    if (!shell_available()) {
        echo "<span class='warn'>⚠️ Shell dinonaktifkan. Jalankan via SSH: <code>npx prisma generate</code></span><br>";
    } else {
        $prisma_local = "$dst_bot/node_modules/.bin/prisma";
        if (!file_exists($prisma_local)) {
            echo "<span class='err'>❌ Prisma belum ada di node_modules. Jalankan npm install dulu (Step 2).</span><br>";
        } else {
            // Jalankan prisma dengan node dari nodevenv agar binary tersedia
            $cmd = file_exists($nodevenv_activate)
                ? "bash -c 'source $nodevenv_activate && cd $dst_bot && node $prisma_local generate'"
                : "cd $dst_bot && $prisma_local generate";
            echo "<b>CMD:</b> <code>" . htmlspecialchars($cmd) . "</code><br><br>";
            $r = run_cmd($cmd);
            echo "<pre>" . htmlspecialchars($r['output']) . "</pre>";
            $ok = $r['code'] === 0 || str_contains($r['output'], 'Generated Prisma Client');
            echo $ok
                ? "<span class='ok'>✅ Prisma generate berhasil!</span><br>"
                : "<span class='err'>❌ Gagal (exit: {$r['code']}). Cek output di atas.</span><br>";
        }
    }
    echo "</div>";
    if ($step === 'prisma') { echo "</body></html>"; exit; }
}

// RESTART
if ($step === 'restart' || $step === 'all') {
    global $dst_bot;
    echo "<div class='section'><h3>🔄 Step 4 — Restart Node.js App</h3>";
    $restart_file = "$dst_bot/tmp/restart.txt";
    @mkdir("$dst_bot/tmp", 0755, true);
    if (@touch($restart_file)) {
        echo "<span class='ok'>✅ Restart signal dikirim!</span><br>";
        echo "<span class='info'>Passenger akan restart dalam beberapa detik.</span><br>";
    } else {
        echo "<span class='warn'>⚠️ Tidak bisa buat restart.txt.</span><br>";
        echo "<span class='info'>Restart manual: cPanel → Setup Node.js App → Restart</span><br>";
    }
    echo "<br><span class='info'>🔗 Cek bot API: <a href='https://topupinweb.my.id/health' target='_blank' style='color:#818cf8'>https://topupinweb.my.id/health</a></span><br>";
    echo "</div>";
    if ($step === 'restart') { echo "</body></html>"; exit; }
}

// SUMMARY
if ($step === 'all') {
    echo "<div style='background:#14532d;padding:15px;border-radius:8px;margin:20px 0'>";
    echo "<h3 style='color:#4ade80;margin:0 0 10px'>🎉 Bot Setup Selesai!</h3>";
    echo "Jika npm install gagal, lakukan manual di cPanel:<br>";
    echo "1. Setup Node.js App → Application root: <code>public_html/bot</code><br>";
    echo "2. Startup file: <code>dist/app.js</code><br>";
    echo "3. Klik <b>Run NPM Install</b><br>";
    echo "4. Set ENV: <code>DATABASE_URL=mysql://ekovmljg_topupin:topupinipin@localhost:3306/ekovmljg_topup_game</code><br>";
    echo "5. Klik <b>Restart</b><br>";
    echo "</div>";
}
?>
</body>
</html>