<?php
/**
 * MANUAL DEPLOY SCRIPT DENGAN PEMISAHAN FOLDER BOT
 * 
 * Script ini berguna jika Git/SSH cPanel tidak bisa digunakan.
 * Letakkan script ini di `public_html/manual_deploy.php` di server Anda.
 */

// Konfigurasi Path
$public_html_dir = __DIR__;
$home_dir = dirname($public_html_dir); // Naik 1 level (misal: /home/ekovmljg)
$bot_dir = $home_dir . '/bot';
$temp_dir = $public_html_dir . '/_deploy_temp';

// URL Zip dari GitHub (Ubah jika repo private / gunakan parameter URL)
$github_zip_url = 'https://github.com/candrawij/topupin_web/archive/refs/heads/main.zip';
$zip_file = $public_html_dir . '/main.zip';

echo "<h2>🚀 Memulai Deployment Otomatis...</h2>";

// 1. Download File ZIP
echo "📥 Mengunduh file dari GitHub...<br>";
$zip_content = @file_get_contents($github_zip_url);
if ($zip_content === false) {
    die("❌ Gagal mengunduh ZIP. Pastikan repository bersifat Public atau upload manual 'main.zip' ke public_html lalu muat ulang halaman ini.");
}
file_put_contents($zip_file, $zip_content);
echo "✅ File ZIP berhasil diunduh (main.zip).<br>";

// 2. Buat folder temp & bot jika belum ada
if (!is_dir($temp_dir)) mkdir($temp_dir, 0755, true);
if (!is_dir($bot_dir)) mkdir($bot_dir, 0755, true);

// 3. Ekstrak ZIP
echo "📦 Mengekstrak file ZIP...<br>";
$zip = new ZipArchive;
if ($zip->open($zip_file) === TRUE) {
    $zip->extractTo($temp_dir);
    $zip->close();
    echo "✅ Berhasil diekstrak ke folder sementara.<br>";
} else {
    die("❌ Gagal mengekstrak file ZIP.");
}

// Biasanya GitHub ZIP membungkus dalam folder (misal: topupin_web-main)
$extracted_folders = glob($temp_dir . '/*', GLOB_ONLYDIR);
$source_dir = $extracted_folders[0] ?? $temp_dir;

// 4. Proses pemindahan file
echo "🔄 Memindahkan file ke folder tujuan...<br>";

// Fungsi untuk memindahkan isi direktori
function moveDirectory($src, $dst, $exclude = []) {
    $dir = opendir($src);
    @mkdir($dst, 0755, true);
    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..') && !in_array($file, $exclude)) {
            if (is_dir($src . '/' . $file)) {
                moveDirectory($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

// A. Pindahkan folder BOT ke luar public_html (Keamanan)
if (is_dir($source_dir . '/bot')) {
    echo "🤖 Memperbarui folder BOT di luar public_html (/home/username/bot)...<br>";
    // Pindahkan isi bot (kecualikan .env jika ada agar .env server aman)
    moveDirectory($source_dir . '/bot', $bot_dir, ['.env']);
}

// B. Pindahkan sisa file website ke public_html
echo "🌐 Memperbarui file Website (PHP) di public_html...<br>";
moveDirectory($source_dir, $public_html_dir, ['bot', '.git', '.gitignore', 'manual_deploy.php']);

// 5. Cleanup
echo "🧹 Membersihkan file sementara...<br>";
function deleteDirectory($dir) {
    if (!file_exists($dir)) return true;
    if (!is_dir($dir)) return unlink($dir);
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') continue;
        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) return false;
    }
    return rmdir($dir);
}

deleteDirectory($temp_dir);
@unlink($zip_file);

echo "<h2>🎉 DEPLOYMENT SELESAI!</h2>";
echo "<p>Website dan Bot berhasil diperbarui dengan aman.</p>";
echo "<p style='color:red;'><b>PENTING:</b> Harap HAPUS file <code>manual_deploy.php</code> ini setelah selesai digunakan demi keamanan server!</p>";
?>
