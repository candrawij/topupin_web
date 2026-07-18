<?php
// File ini untuk test apakah PHP bisa jalan sama sekali
// Upload ke public_html/ lalu akses: https://topupinweb.my.id/phptest.php
header('Content-Type: text/html; charset=utf-8');
echo '<h2 style="color:green;font-family:monospace;">✅ PHP berjalan normal!</h2>';
echo '<p>PHP Version: ' . PHP_VERSION . '</p>';
echo '<p>Server: ' . ($_SERVER['SERVER_SOFTWARE'] ?? '-') . '</p>';
echo '<p>Document Root: ' . ($_SERVER['DOCUMENT_ROOT'] ?? '-') . '</p>';
echo '<p>Request URI: ' . ($_SERVER['REQUEST_URI'] ?? '-') . '</p>';
echo '<hr>';
echo '<h3>Isi .htaccess saat ini:</h3>';
$htaccess = __DIR__ . '/.htaccess';
if (file_exists($htaccess)) {
    echo '<pre style="background:#f0f0f0;padding:10px">' . htmlspecialchars(file_get_contents($htaccess)) . '</pre>';
} else {
    echo '<p style="color:orange">Tidak ada file .htaccess</p>';
}
echo '<hr>';
echo '<h3>Environment Variables (Node.js terkait):</h3>';
$env_keys = ['PASSENGER_APP_ENV','PORT','NODE_ENV','PASSENGER_BASE_URI','RAILS_ENV'];
foreach ($env_keys as $k) {
    $v = getenv($k);
    if ($v !== false) echo "<p><b>$k</b> = $v</p>";
}