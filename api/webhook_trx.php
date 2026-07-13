<?php
// c:\laragon\www\TopUpin\api\webhook_trx.php

header('Content-Type: application/json');
include "../config/koneksi.php";
include "../config/telegram_helper.php";

/** @var mysqli $conn */

// Baca input JSON dari request
$inputRaw = file_get_contents('php://input');
$data = json_decode($inputRaw, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

$trxId = isset($data['trxId']) ? trim($data['trxId']) : '';
$status = isset($data['status']) ? trim($data['status']) : '';
$secret = isset($data['secret']) ? trim($data['secret']) : '';

// Validasi Secret Key untuk keamanan webhook
if ($secret !== DEEP_LINK_SECRET) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden: Invalid secret key']);
    exit;
}

if (empty($trxId) || empty($status)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing trxId or status']);
    exit;
}

// Cari ID Transaksi numerik (contoh: TRX-5 -> 5)
preg_match('/\d+/', $trxId, $matches);
$id_trx = isset($matches[0]) ? intval($matches[0]) : 0;

if ($id_trx <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid transaction ID format']);
    exit;
}

// Petakan status bot ke status PHP website
$newStatus = 'Pending';
if (strtolower($status) === 'success') {
    $newStatus = 'Success';
} elseif (strtolower($status) === 'failed') {
    $newStatus = 'Failed';
}

// Cek apakah transaksi ada
$checkQuery = mysqli_query($conn, "SELECT * FROM transaksi WHERE id_trx = $id_trx");
$trx = mysqli_fetch_assoc($checkQuery);

if (!$trx) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Transaction not found in website database']);
    exit;
}

// Update status transaksi di database PHP
$updateQuery = mysqli_query($conn, "UPDATE transaksi SET status = '$newStatus' WHERE id_trx = $id_trx");

if ($updateQuery) {
    echo json_encode([
        'success' => true,
        'message' => 'Transaction status updated successfully',
        'id_trx' => $id_trx,
        'status' => $newStatus
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update transaction in database: ' . mysqli_error($conn)
    ]);
}
