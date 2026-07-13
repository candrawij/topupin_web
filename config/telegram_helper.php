<?php
// c:\laragon\www\TopUpin\config\telegram_helper.php

if (!defined('TELEGRAM_BOT_USERNAME')) {
    define('TELEGRAM_BOT_USERNAME', 'top_upin_bot');
}
if (!defined('DEEP_LINK_SECRET')) {
    define('DEEP_LINK_SECRET', 'topupgames_secret_key_deep_link_32');
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
