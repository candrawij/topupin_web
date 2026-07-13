<?php
// c:\laragon\www\TopUpin\config\migrate_chat.php

include "koneksi.php";

/** @var mysqli $conn */

echo "=== MEMULAI MIGRASI TABEL CHAT CS LOKAL ===\n";

// 1. Buat tabel cs_chat_sessions
$create_sessions = "
CREATE TABLE IF NOT EXISTS `cs_chat_sessions` (
  `id_session` INT AUTO_INCREMENT PRIMARY KEY,
  `id_user` INT NOT NULL,
  `status` ENUM('active', 'closed') DEFAULT 'active',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

if (mysqli_query($conn, $create_sessions)) {
    echo "[V] Tabel cs_chat_sessions berhasil dibuat.\n";
} else {
    echo "[X] Tabel cs_chat_sessions gagal dibuat: " . mysqli_error($conn) . "\n";
}

// 2. Buat tabel cs_chat_messages
$create_messages = "
CREATE TABLE IF NOT EXISTS `cs_chat_messages` (
  `id_message` INT AUTO_INCREMENT PRIMARY KEY,
  `id_session` INT NOT NULL,
  `sender_role` ENUM('user', 'admin') NOT NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`id_session`) REFERENCES `cs_chat_sessions` (`id_session`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

if (mysqli_query($conn, $create_messages)) {
    echo "[V] Tabel cs_chat_messages berhasil dibuat.\n";
} else {
    echo "[X] Tabel cs_chat_messages gagal dibuat: " . mysqli_error($conn) . "\n";
}

echo "=== MIGRASI SELESAI ===\n";
