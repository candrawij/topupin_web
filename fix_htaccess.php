<?php
// Tool untuk melihat dan memperbaiki .htaccess yang bermasalah
// Upload ke public_html/ lalu akses: https://topupinweb.my.id/fix_htaccess.php
header('Content-Type: text/html; charset=utf-8');

$htaccess_path = __DIR__ . '/.htaccess';
$action = $_GET['action'] ?? 'view';

echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Fix .htaccess</title>';
echo '<style>body{font-family:monospace;background:#0b0f19;color:#e2e8f0;padding:20px}';
echo '.ok{color:#4ade80}.err{color:#f87171}.warn{color:#fbbf24}';
echo 'pre{background:#1f2937;padding:12px;border-radius:8px;white-space:pre-wrap}';
echo '.btn{display:inline-block;background:#4f46e5;color:white;padding:8px 16px;border-radius:6px;text-decoration:none;margin:4px;font-size:13px}';
echo '.btn-red{background:#dc2626}.btn-green{background:#16a34a}</style></head><body>';
echo '<h2>🔧 Fix .htaccess Tool</h2>';

echo '<nav style="margin:15px 0">';
echo '<a href="?action=view"   class="btn">👁️ Lihat .htaccess</a>';
echo '<a href="?action=fix"    class="btn btn-green">✅ Fix: Hapus Passenger Config</a>';
echo '<a href="?action=backup" class="btn">💾 Backup .htaccess</a>';
echo '<a href="?action=delete" class="btn btn-red">🗑️ Hapus .htaccess Sepenuhnya</a>';
echo '</nav><hr style="border-color:#374151">';

// VIEW
if ($action === 'view') {
    echo '<h3>Isi .htaccess Saat Ini</h3>';
    if (!file_exists($htaccess_path)) {
        echo '<p class="warn">Tidak ada file .htaccess</p>';
    } else {
        $content = file_get_contents($htaccess_path);
        echo '<pre>' . htmlspecialchars($content) . '</pre>';
        // Deteksi masalah
        if (str_contains($content, 'PassengerEnabled') || str_contains($content, 'PassengerNodejs') || str_contains($content, 'passenger')) {
            echo '<p class="err">❌ DITEMUKAN: Konfigurasi Passenger dalam .htaccess ini menyebabkan 503!</p>';
            echo '<p>Klik <b>Fix: Hapus Passenger Config</b> untuk memperbaikinya.</p>';
        } else {
            echo '<p class="ok">✅ Tidak ada konfigurasi Passenger yang bermasalah.</p>';
        }
    }
}

// BACKUP
elseif ($action === 'backup') {
    if (file_exists($htaccess_path)) {
        $backup = $htaccess_path . '.backup_' . date('Ymd_His');
        copy($htaccess_path, $backup);
        echo '<p class="ok">✅ Backup dibuat: ' . $backup . '</p>';
    } else {
        echo '<p class="warn">Tidak ada .htaccess untuk dibackup.</p>';
    }
}

// FIX: Hapus baris Passenger saja, pertahankan yang lain
elseif ($action === 'fix') {
    if (!file_exists($htaccess_path)) {
        echo '<p class="warn">Tidak ada .htaccess untuk diperbaiki.</p>';
    } else {
        $content = file_get_contents($htaccess_path);
        // Backup dulu
        file_put_contents($htaccess_path . '.backup', $content);
        // Hapus semua baris Passenger
        $lines = explode("\n", $content);
        $passenger_keywords = ['PassengerEnabled','PassengerApp','PassengerNode','PassengerBase','PassengerLog','passenger_enabled'];
        $clean_lines = array_filter($lines, function($line) use ($passenger_keywords) {
            foreach ($passenger_keywords as $kw) {
                if (stripos($line, $kw) !== false) return false;
            }
            return true;
        });
        $new_content = implode("\n", $clean_lines);
        file_put_contents($htaccess_path, $new_content);
        echo '<p class="ok">✅ Konfigurasi Passenger berhasil dihapus dari .htaccess!</p>';
        echo '<h3>Isi .htaccess Setelah Fix:</h3>';
        echo '<pre>' . htmlspecialchars($new_content) . '</pre>';
        echo '<p>Sekarang coba akses: <a href="/" style="color:#818cf8">https://topupinweb.my.id</a></p>';
    }
}

// DELETE
elseif ($action === 'delete') {
    if (file_exists($htaccess_path)) {
        // Backup dulu
        copy($htaccess_path, $htaccess_path . '.deleted_backup');
        unlink($htaccess_path);
        echo '<p class="ok">✅ .htaccess berhasil dihapus! Backup disimpan sebagai .htaccess.deleted_backup</p>';
        echo '<p>Sekarang coba akses: <a href="/" style="color:#818cf8">https://topupinweb.my.id</a></p>';
    } else {
        echo '<p class="warn">Tidak ada .htaccess.</p>';
    }
}

echo '</body></html>';