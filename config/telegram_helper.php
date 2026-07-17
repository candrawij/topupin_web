<?php
// c:\laragon\www\TopUpin\config\telegram_helper.php

if (!defined('TELEGRAM_BOT_USERNAME')) {
    define('TELEGRAM_BOT_USERNAME', 'top_upin_bot');
}
if (!defined('DEEP_LINK_SECRET')) {
    define('DEEP_LINK_SECRET', 'topupgames_secret_key_deep_link_32');
}

// URL internal bot Node.js (dijalankan di server yang sama, port 3001)
if (!defined('BOT_API_BASE_URL')) {
    define('BOT_API_BASE_URL', 'http://localhost:3001');
}

/**
 * Mendapatkan kunci enkripsi 32-byte dari secret key menggunakan SHA-256
 */
function getTelegramEncryptionKey() {
    return hash('sha256', DEEP_LINK_SECRET, true);
}

/**
 * Mengenkripsi payload deep-link ke format biner 16-byte lalu di-enkrip dengan AES-256-ECB
 */
function encryptTelegramPayload($role, $userId = 0, $ticketId = '', $action = '', $source = 'website') {
    // 1. Role byte
    $roleByte = 0;
    if ($role === 'customer') $roleByte = 1;
    elseif ($role === 'cs') $roleByte = 2;
    elseif ($role === 'admin') $roleByte = 3;

    // 2. UserId (32-bit big-endian)
    $userIdVal = intval($userId);

    // 3. TicketId (32-bit big-endian)
    $ticketIdNum = 0;
    if ($ticketId) {
        preg_match('/\d+/', $ticketId, $matches);
        if ($matches) {
            $ticketIdNum = intval($matches[0]);
        }
    }

    // 4. Action byte
    $actionByte = 0;
    if ($action === 'open_ticket') $actionByte = 1;
    elseif ($action === 'list') $actionByte = 2;
    elseif ($action === 'dashboard') $actionByte = 3;

    // 5. Source byte
    $sourceByte = 0;
    if ($source === 'website') $sourceByte = 1;

    // 6. Padding/salt (5 bytes random)
    $randomBytes = random_bytes(5);

    // Pack data ke format biner 11-byte kemudian gabungkan dengan 5-byte random salt
    $binary = pack('CNNCC', $roleByte, $userIdVal, $ticketIdNum, $actionByte, $sourceByte) . $randomBytes;

    $key = getTelegramEncryptionKey();
    
    // Enkripsi dengan AES-256-ECB tanpa padding tambahan (karena input pas 16 byte)
    $encrypted = openssl_encrypt($binary, 'aes-256-ecb', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING);

    // Encode ke base64url format
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($encrypted));
}

/**
 * Membuat link Telegram untuk Customer/User
 */
function buildCustomerTelegramLink($userId, $ticketId) {
    $payload = encryptTelegramPayload('customer', $userId, $ticketId, 'open_ticket', 'website');
    return "https://t.me/" . TELEGRAM_BOT_USERNAME . "?start=" . $payload;
}

/**
 * Membuat link Telegram untuk Transaksi Spesifik (plaintext fallback sesuai implementasi bot)
 */
function buildTrxTelegramLink($trxId) {
    return "https://t.me/" . TELEGRAM_BOT_USERNAME . "?start=trx_" . $trxId;
}

/**
 * Memanggil API internal bot untuk mengirim notifikasi Telegram ke user
 * Bot harus sudah berjalan (npm start / pm2) agar fungsi ini berhasil
 *
 * @param string $trxId  ID transaksi (misal: TRX-5)
 * @param string $status Status baru: 'success' | 'failed' | 'pending'
 * @return array|null    Response dari bot API, atau null jika gagal/bot tidak aktif
 */
function notifyBotTransaction($trxId, $status) {
    $url = BOT_API_BASE_URL . '/api/notify-transaction';

    $payload = json_encode([
        'trxId'  => $trxId,
        'status' => $status,
    ]);

    $opts = [
        'http' => [
            'method'        => 'POST',
            'header'        => "Content-Type: application/json\r\n" .
                               "Content-Length: " . strlen($payload) . "\r\n",
            'content'       => $payload,
            'timeout'       => 5, // timeout 5 detik agar tidak memblokir website
            'ignore_errors' => true,
        ],
    ];

    $context  = stream_context_create($opts);
    $response = @file_get_contents($url, false, $context);

    if ($response === false) {
        // Bot tidak aktif atau tidak bisa dijangkau — tidak fatal untuk website
        error_log("[telegram_helper] Bot API tidak dapat dijangkau di " . $url);
        return null;
    }

    return json_decode($response, true);
}

/**
 * Memeriksa apakah bot API sedang aktif berjalan
 * 
 * @return bool true jika bot aktif, false jika tidak
 */
function isBotApiAlive() {
    $url = BOT_API_BASE_URL . '/health';
    $opts = [
        'http' => [
            'method'        => 'GET',
            'timeout'       => 2,
            'ignore_errors' => true,
        ],
    ];
    $context  = stream_context_create($opts);
    $response = @file_get_contents($url, false, $context);
    if ($response === false) return false;
    $data = json_decode($response, true);
    return isset($data['status']) && $data['status'] === 'ok';
}
