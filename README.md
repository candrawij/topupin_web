# TopUpin - Gaming Marketplace & Top Up Terbaik

TopUpin adalah platform marketplace gaming dan top-up game berbasis PHP native yang mendukung sistem pembayaran digital (QRIS/E-Wallet), sistem penarikan dana untuk seller, fitur live chat Customer Service (CS) real-time, serta integrasi notifikasi dan approval otomatis menggunakan **Telegram Bot**.

---

## 🚀 Fitur Utama

1. **Dashboard Seller & Admin Panel:** Pengelolaan produk, pencairan dana, dan pemantauan transaksi terpusat.
2. **CS Chat Support (Real-Time):** Fitur live chat interaktif antara pembeli/penjual dengan admin menggunakan AJAX polling.
3. **Integrasi Bot Telegram:**
   * Notifikasi otomatis ke grup admin setiap ada transaksi baru.
   * Tombol approval transaksi langsung di Telegram (mengubah status transaksi di web utama melalui webhook).
   * Tombol pintas bantuan langsung terhubung ke bot Telegram menggunakan enkripsi deep-link AES-256-ECB.

---

## 🛠️ Persyaratan Sistem

* **PHP:** >= 8.0
* **Web Server:** Apache / Nginx (Disarankan menggunakan **Laragon** untuk Windows)
* **Database:** MySQL / MariaDB

---

## 💻 Cara Instalasi & Menjalankan

### 1. Kloning Project & Setup Laragon
1. Kloning repositori ini ke folder `www` Laragon Anda (misal: `C:\laragon\www\TopUpin`).
2. Jalankan aplikasi Laragon dan klik **Start All**.

### 2. Konfigurasi Database
1. Buat database baru bernama `topup_game` di MySQL (lewat HeidiSQL atau phpMyAdmin).
2. Sesuaikan konfigurasi koneksi database Anda di file `config/koneksi.php` jika diperlukan (default: `localhost`, user `root`, tanpa password).

### 3. Migrasi Database (Setup Tabel & Seeding)
Jalankan script migrasi berikut melalui terminal di folder proyek Anda:
```bash
# Setup tabel utama & sampel data produk
php config/setup_db.php

# Setup tabel chat CS lokal
php config/migrate_chat.php

# Setup tabel pendukung Bot Telegram
php config/migrate_bot_tables.php
```

### 4. Integrasi & Menjalankan Telegram Bot
Proyek Telegram Bot terletak di folder `E:\BotTele` (atau repositori bot terpisah):
1. Sesuaikan file `.env` di proyek Bot Anda:
   ```env
   DATABASE_URL="mysql://root@localhost:3306/topup_game"
   WEBSITE_BASE_URL="http://localhost:8000"
   DEEP_LINK_SECRET="topupgames_secret_key_deep_link_32"
   ```
2. Jalankan bot Telegram menggunakan perintah:
   ```bash
   npm install
   npm run dev
   ```

### 5. Akses Halaman Web
* **Halaman Utama:** `http://localhost:8000/` atau `http://topupin.test/`
* **Halaman Seller Panel:** `http://localhost:8000/seller/`
* **Halaman Admin Panel:** `http://localhost:8000/admin/` (Login default: `admin` / `admin123`)
