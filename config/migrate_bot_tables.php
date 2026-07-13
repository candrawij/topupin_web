<?php
// c:\laragon\www\TopUpin\config\migrate_bot_tables.php

include "koneksi.php";

/** @var mysqli $conn */

echo "=== MEMULAI MIGRASI TABEL BOT TELEGRAM ===\n";

$queries = [
    // 1. users
    "CREATE TABLE IF NOT EXISTS `users` (
      `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
      `email` VARCHAR(255) NOT NULL UNIQUE,
      `password` VARCHAR(255) NOT NULL,
      `name` VARCHAR(100) NOT NULL,
      `phone` VARCHAR(20),
      `telegram_id` BIGINT UNIQUE,
      `status` VARCHAR(20) DEFAULT 'active',
      `total_transactions` INT DEFAULT 0,
      `total_spent` DECIMAL(12, 2) DEFAULT 0,
      `last_active_at` DATETIME,
      `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
      `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // 2. admins
    "CREATE TABLE IF NOT EXISTS `admins` (
      `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
      `email` VARCHAR(255) NOT NULL UNIQUE,
      `password` VARCHAR(255) NOT NULL,
      `name` VARCHAR(100) NOT NULL,
      `phone` VARCHAR(20),
      `role` VARCHAR(30) DEFAULT 'admin',
      `status` VARCHAR(20) DEFAULT 'active',
      `last_login_at` DATETIME,
      `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
      `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // 3. cs_agents
    "CREATE TABLE IF NOT EXISTS `cs_agents` (
      `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
      `user_id` BIGINT,
      `admin_id` BIGINT,
      `name` VARCHAR(100) NOT NULL,
      `email` VARCHAR(255) NOT NULL UNIQUE,
      `telegram_id` BIGINT UNIQUE,
      `status` VARCHAR(20) DEFAULT 'online',
      `skills` JSON,
      `rating` DECIMAL(3, 2) DEFAULT 0,
      `total_handled` INT DEFAULT 0,
      `avg_response_time` INT DEFAULT 0,
      `max_tickets` INT DEFAULT 5,
      `current_tickets` INT DEFAULT 0,
      `shift_start` TIME,
      `shift_end` TIME,
      `last_active_at` DATETIME,
      `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
      `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
      FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // 4. tickets
    "CREATE TABLE IF NOT EXISTS `tickets` (
      `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
      `ticket_id` VARCHAR(20) NOT NULL UNIQUE,
      `user_id` BIGINT,
      `admin_id` BIGINT,
      `cs_agent_id` BIGINT,
      `category` VARCHAR(30) NOT NULL,
      `priority` VARCHAR(20) DEFAULT 'normal',
      `status` VARCHAR(20) DEFAULT 'open',
      `subject` VARCHAR(255),
      `messages_count` INT DEFAULT 0,
      `last_message_at` DATETIME,
      `assigned_at` DATETIME,
      `first_response_at` DATETIME,
      `closed_at` DATETIME,
      `source` VARCHAR(30) DEFAULT 'telegram',
      `rating` TINYINT,
      `feedback` TEXT,
      `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
      `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
      FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
      FOREIGN KEY (`cs_agent_id`) REFERENCES `cs_agents` (`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // 5. ticket_messages
    "CREATE TABLE IF NOT EXISTS `ticket_messages` (
      `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
      `ticket_id` BIGINT,
      `sender_type` VARCHAR(20) NOT NULL,
      `sender_id` BIGINT,
      `message` TEXT NOT NULL,
      `is_forwarded` BOOLEAN DEFAULT FALSE,
      `is_internal` BOOLEAN DEFAULT FALSE,
      `telegram_message_id` INT,
      `read_at` DATETIME,
      `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // 6. admin_logs
    "CREATE TABLE IF NOT EXISTS `admin_logs` (
      `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
      `admin_id` BIGINT,
      `action` VARCHAR(50) NOT NULL,
      `target_type` VARCHAR(30),
      `target_id` BIGINT,
      `details` JSON,
      `ip_address` VARCHAR(45),
      `user_agent` TEXT,
      `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // 7. cs_performance
    "CREATE TABLE IF NOT EXISTS `cs_performance` (
      `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
      `cs_agent_id` BIGINT,
      `date` DATE NOT NULL,
      `tickets_handled` INT DEFAULT 0,
      `tickets_closed` INT DEFAULT 0,
      `avg_response_time` INT DEFAULT 0,
      `avg_resolution_time` INT DEFAULT 0,
      `satisfaction_rate` DECIMAL(3, 2) DEFAULT 0,
      `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
      `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      FOREIGN KEY (`cs_agent_id`) REFERENCES `cs_agents` (`id`) ON DELETE CASCADE,
      UNIQUE KEY `cs_agent_date_unique` (`cs_agent_id`, `date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // 8. games
    "CREATE TABLE IF NOT EXISTS `games` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `name` VARCHAR(100) NOT NULL,
      `slug` VARCHAR(100) NOT NULL UNIQUE,
      `logo_url` VARCHAR(500),
      `is_active` BOOLEAN DEFAULT TRUE,
      `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // 9. products
    "CREATE TABLE IF NOT EXISTS `products` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `game_id` INT NOT NULL,
      `name` VARCHAR(200) NOT NULL,
      `price` INT NOT NULL,
      `stock` INT DEFAULT -1,
      `is_active` BOOLEAN DEFAULT TRUE,
      `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // 10. transactions
    "CREATE TABLE IF NOT EXISTS `transactions` (
      `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
      `trx_id` VARCHAR(50) NOT NULL UNIQUE,
      `user_id` BIGINT,
      `admin_id` BIGINT,
      `game_id` INT NOT NULL,
      `product_id` INT NOT NULL,
      `user_game_id` VARCHAR(100) NOT NULL,
      `amount` INT NOT NULL,
      `payment_method` VARCHAR(50) NOT NULL,
      `payment_code` VARCHAR(100),
      `status` VARCHAR(20) DEFAULT 'pending',
      `payment_expiry` DATETIME,
      `topup_request_sent_at` DATETIME,
      `topup_response` TEXT,
      `webhook_payload` TEXT,
      `refunded_at` DATETIME,
      `refunded_by` BIGINT,
      `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
      `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
      FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
      FOREIGN KEY (`game_id`) REFERENCES `games` (`id`),
      FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];

foreach ($queries as $index => $sql) {
    if (mysqli_query($conn, $sql)) {
        echo "[V] Query " . ($index + 1) . " berhasil dijalankan.\n";
    } else {
        echo "[X] Query " . ($index + 1) . " gagal: " . mysqli_error($conn) . "\n";
    }
}

echo "=== MIGRASI SELESAI ===\n";
