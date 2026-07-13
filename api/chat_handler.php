<?php
// c:\laragon\www\TopUpin\api\chat_handler.php

session_start();
header('Content-Type: application/json');
include "../config/koneksi.php";

/** @var mysqli $conn */

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized: Silakan login terlebih dahulu.']);
    exit;
}

$id_user = intval($_SESSION['user_id']);
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Mendapatkan atau membuat sesi aktif baru untuk user
function getOrCreateSession($conn, $id_user) {
    $query = mysqli_query($conn, "SELECT * FROM cs_chat_sessions WHERE id_user = $id_user AND status = 'active' LIMIT 1");
    $session = mysqli_fetch_assoc($query);
    if (!$session) {
        mysqli_query($conn, "INSERT INTO cs_chat_sessions (id_user, status) VALUES ($id_user, 'active')");
        return intval(mysqli_insert_id($conn));
    }
    return intval($session['id_session']);
}

if ($action === 'send') {
    $inputRaw = file_get_contents('php://input');
    $data = json_decode($inputRaw, true);
    
    // Gunakan real_escape_string dan trim
    $messageText = isset($data['message']) ? trim(mysqli_real_escape_string($conn, $data['message'])) : '';

    if (empty($messageText)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Pesan tidak boleh kosong.']);
        exit;
    }

    $id_session = getOrCreateSession($conn, $id_user);

    // Simpan pesan baru
    $insert = mysqli_query($conn, "
        INSERT INTO cs_chat_messages (id_session, sender_role, message) 
        VALUES ($id_session, 'user', '$messageText')
    ");

    // Update waktu aktivitas sesi
    mysqli_query($conn, "UPDATE cs_chat_sessions SET updated_at = NOW() WHERE id_session = $id_session");

    if ($insert) {
        echo json_encode(['success' => true, 'message' => 'Pesan terkirim.']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Gagal mengirim pesan: ' . mysqli_error($conn)]);
    }
    exit;
}

elseif ($action === 'get') {
    $query_sess = mysqli_query($conn, "SELECT id_session FROM cs_chat_sessions WHERE id_user = $id_user AND status = 'active' LIMIT 1");
    $session = mysqli_fetch_assoc($query_sess);

    if (!$session) {
        echo json_encode(['success' => true, 'messages' => []]);
        exit;
    }

    $id_session = intval($session['id_session']);

    // Ambil riwayat chat terurut menaik (tua ke baru)
    $query_msg = mysqli_query($conn, "
        SELECT sender_role, message, created_at 
        FROM cs_chat_messages 
        WHERE id_session = $id_session 
        ORDER BY id_message ASC
    ");

    $messages = [];
    while ($row = mysqli_fetch_assoc($query_msg)) {
        $messages[] = [
            'role' => $row['sender_role'],
            'text' => htmlspecialchars($row['message']),
            'time' => date('H:i', strtotime($row['created_at']))
        ];
    }

    // Tandai pesan dari admin sebagai terbaca oleh user
    mysqli_query($conn, "UPDATE cs_chat_messages SET is_read = 1 WHERE id_session = $id_session AND sender_role = 'admin'");

    echo json_encode(['success' => true, 'messages' => $messages]);
    exit;
}

else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Aksi tidak valid']);
    exit;
}
