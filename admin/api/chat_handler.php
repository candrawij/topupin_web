<?php
// c:\laragon\www\TopUpin\admin\api\chat_handler.php

session_start();
header('Content-Type: application/json');
include "../../config/koneksi.php";

/** @var mysqli $conn */

// Proteksi admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized: Silakan login admin terlebih dahulu.']);
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

// 1. Ambil daftar sesi chat aktif
if ($action === 'get_sessions') {
    $query = mysqli_query($conn, "
        SELECT s.*, u.nama, u.email,
               (SELECT COUNT(*) FROM cs_chat_messages m WHERE m.id_session = s.id_session AND m.sender_role = 'user' AND m.is_read = 0) as unread_count,
               (SELECT message FROM cs_chat_messages m WHERE m.id_session = s.id_session ORDER BY m.id_message DESC LIMIT 1) as last_message
        FROM cs_chat_sessions s
        JOIN user u ON s.id_user = u.id_user
        WHERE s.status = 'active'
        ORDER BY s.updated_at DESC
    ");

    $sessions = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $sessions[] = [
            'id_session' => intval($row['id_session']),
            'id_user' => intval($row['id_user']),
            'nama' => htmlspecialchars($row['nama']),
            'email' => htmlspecialchars($row['email']),
            'unread_count' => intval($row['unread_count']),
            'last_message' => htmlspecialchars($row['last_message'] ?? 'Memulai percakapan...'),
            'updated_at' => date('d M, H:i', strtotime($row['updated_at']))
        ];
    }

    echo json_encode(['success' => true, 'sessions' => $sessions]);
    exit;
}

// 2. Ambil seluruh pesan dalam satu sesi
elseif ($action === 'get_messages') {
    $id_session = isset($_GET['id_session']) ? intval($_GET['id_session']) : 0;

    if ($id_session <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID Sesi tidak valid.']);
        exit;
    }

    // Tandai semua pesan dari user dalam sesi ini sebagai terbaca
    mysqli_query($conn, "UPDATE cs_chat_messages SET is_read = 1 WHERE id_session = $id_session AND sender_role = 'user'");

    // Ambil riwayat chat
    $query = mysqli_query($conn, "
        SELECT sender_role, message, created_at 
        FROM cs_chat_messages 
        WHERE id_session = $id_session 
        ORDER BY id_message ASC
    ");

    $messages = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $messages[] = [
            'role' => $row['sender_role'],
            'text' => htmlspecialchars($row['message']),
            'time' => date('H:i', strtotime($row['created_at']))
        ];
    }

    echo json_encode(['success' => true, 'messages' => $messages]);
    exit;
}

// 3. Kirim balasan admin
elseif ($action === 'send') {
    $inputRaw = file_get_contents('php://input');
    $data = json_decode($inputRaw, true);
    
    $id_session = isset($data['id_session']) ? intval($data['id_session']) : 0;
    $messageText = isset($data['message']) ? trim(mysqli_real_escape_string($conn, $data['message'])) : '';

    if ($id_session <= 0 || empty($messageText)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Sesi tidak valid atau pesan kosong.']);
        exit;
    }

    // Cek apakah sesi masih aktif
    $check = mysqli_query($conn, "SELECT status FROM cs_chat_sessions WHERE id_session = $id_session");
    $sess = mysqli_fetch_assoc($check);
    
    if (!$sess || $sess['status'] !== 'active') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Sesi chat sudah ditutup.']);
        exit;
    }

    // Simpan pesan
    $insert = mysqli_query($conn, "
        INSERT INTO cs_chat_messages (id_session, sender_role, message) 
        VALUES ($id_session, 'admin', '$messageText')
    ");

    // Update waktu update sesi
    mysqli_query($conn, "UPDATE cs_chat_sessions SET updated_at = NOW() WHERE id_session = $id_session");

    if ($insert) {
        echo json_encode(['success' => true, 'message' => 'Pesan terkirim.']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Gagal mengirim pesan: ' . mysqli_error($conn)]);
    }
    exit;
}

// 4. Tutup Sesi Percakapan
elseif ($action === 'close') {
    $inputRaw = file_get_contents('php://input');
    $data = json_decode($inputRaw, true);
    $id_session = isset($data['id_session']) ? intval($data['id_session']) : 0;

    if ($id_session <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID Sesi tidak valid.']);
        exit;
    }

    $update = mysqli_query($conn, "UPDATE cs_chat_sessions SET status = 'closed' WHERE id_session = $id_session");

    if ($update) {
        echo json_encode(['success' => true, 'message' => 'Sesi chat berhasil ditutup.']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Gagal menutup sesi: ' . mysqli_error($conn)]);
    }
    exit;
}

else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Aksi tidak valid']);
    exit;
}
