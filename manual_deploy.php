<?php
header('Content-Type: text/html; charset=utf-8');
echo "<h2>🚀 Menjalankan Manual Deploy via Pure PHP (Bypass Shell Restriction)...</h2>";

$src = '/home/ekovmljg/repositories/topupin_web';
$dst_web = '/home/ekovmljg/public_html';
$dst_bot = '/home/ekovmljg/bot';

function sync_directory($src, $dst, $exclude_files = []) {
    if (!is_dir($src)) {
        echo "<span style='color:red'>❌ Folder sumber tidak ditemukan: $src</span><br>";
        return false;
    }
    
    $dir = opendir($src);
    @mkdir($dst, 0755, true);
    
    while (false !== ($file = readdir($dir))) {
        if ($file != '.' && $file != '..') {
            // Cek apakah file/folder masuk dalam daftar exclude
            if (in_array($file, $exclude_files)) continue;

            $srcFile = $src . '/' . $file;
            $dstFile = $dst . '/' . $file;
            
            if (is_dir($srcFile)) {
                sync_directory($srcFile, $dstFile, $exclude_files);
            } else {
                if (@copy($srcFile, $dstFile)) {
                    // echo "✓ Menyalin: " . basename($dstFile) . "<br>"; // Dihilangkan agar tidak spam log
                } else {
                    echo "<span style='color:red'>❌ Gagal menyalin: " . $file . "</span><br>";
                }
            }
        }
    }
    closedir($dir);
    return true;
}

// 1. Update Website
echo "<h4>🌐 1. Memperbarui File Website (PHP) ke public_html...</h4>";
if (sync_directory($src, $dst_web, ['.git', 'bot', 'node_modules', '.cpanel.yml', 'manual_deploy.php'])) {
    echo "<span style='color:green'>✅ Berhasil memperbarui file Website.</span><br>";
}

// 2. Update Bot
echo "<h4>🤖 2. Memperbarui File Bot (Node.js) ke folder bot...</h4>";
// Exclude .env agar kredensial di server tidak tertimpa! Exclude data/ untuk simpan session/lock.
if (sync_directory($src . '/bot', $dst_bot, ['node_modules', '.env', 'data'])) {
    echo "<span style='color:green'>✅ Berhasil memperbarui source code Bot.</span><br>";
}

echo "<br><h3 style='color:green'>🎉 Sinkronisasi Website & Bot berhasil menggunakan Pure PHP Copy!</h3>";
?>
