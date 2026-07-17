# TopUpIn — Platform Top Up Game & CS Support Berbasis Telegram

<div align="center">

![TopUpIn Banner](https://img.shields.io/badge/TopUpIn-Platform%20Top%20Up%20Game-6C5CE7?style=for-the-badge&logo=gamepad)
![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=flat-square&logo=php)
![Node.js](https://img.shields.io/badge/Node.js-18+-339933?style=flat-square&logo=node.js)
![TypeScript](https://img.shields.io/badge/TypeScript-5.0+-3178C6?style=flat-square&logo=typescript)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql)

**🔗 Live Demo:** [topupinweb.my.id](https://topupinweb.my.id) &nbsp;|&nbsp; **📖 Panduan Demo:** [topupinweb.my.id/demo.php](https://topupinweb.my.id/demo.php)

</div>

---

## 📌 Tentang Proyek

TopUpIn adalah platform marketplace digital untuk top up game berbasis **PHP Native** yang dilengkapi sistem **Customer Service real-time via Bot Telegram**. Proyek ini merupakan implementasi nyata konsep e-commerce vertikal yang menggabungkan web PHP, Node.js, dan Telegram Bot API dalam satu ekosistem yang terintegrasi.

---

## 🚀 Fitur Utama

### 🌐 Website (PHP)
| Fitur | Deskripsi |
|---|---|
| Katalog Produk | Browse game & produk top up dengan filter game |
| Detail & Checkout | Input ID Game, konfirmasi, dan alur pembayaran QRIS |
| Riwayat Transaksi | Tracking status pesanan pengguna secara real-time |
| Seller Panel | Dashboard seller: kelola produk & request pencairan |
| Admin Panel | Manajemen transaksi, user, produk, dan approval |
| Demo Guide | Halaman panduan khusus penguji (dosen/investor) |

### 🤖 Bot Telegram (CS Support System)
| Fitur | Deskripsi |
|---|---|
| Tiket CS Otomatis | Setiap pesan membuat tiket unik dengan ID terlacak |
| Bridging ke Admin | Pesan customer diteruskan ke grup Telegram admin |
| Reply Langsung | Admin cukup *reply* di grup untuk membalas customer |
| Notifikasi Transaksi | Notifikasi Sukses/Gagal + Info Refund + Tombol Aksi |
| Order via Bot | Customer bisa memesan + konfirmasi langsung dari bot |
| Role-Based Menu | Menu berbeda untuk Customer, CS Agent, dan Admin |

---

## 🛠️ Tech Stack

```
├── Website         → PHP 8.0 Native (Tanpa Framework)
├── Database        → MySQL / MariaDB
├── Bot Engine      → Node.js 18 + TypeScript
├── Bot Framework   → GrammY v2
├── ORM (Bot)       → Prisma
├── Bot Protocol    → Telegram Bot API (Long Polling + Webhook)
└── Deployment      → cPanel Shared Hosting + GitHub Auto-Deploy
```

---

## 🏗️ Arsitektur Sistem

```
Customer / Seller / Admin (Browser)
        │
        ▼
 [ Website PHP ]  ←──── MySQL Database ────►  [ Admin Panel ]
        │                     ▲
        │              Prisma ORM
        │                     │
        └──────────►  [ Bot Node.js ]  ◄──────────►  Telegram API
                              │
                     [ /api/webhook_trx.php ]
```

---

## 💻 Instalasi Lokal (Development)

### Prasyarat
- PHP >= 8.0 (Laragon / XAMPP)
- MySQL / MariaDB
- Node.js >= 18
- npm

### 1. Clone & Setup
```bash
git clone https://github.com/candrawij/topupin_web.git
cd topupin_web
```

### 2. Setup Database
```bash
# Buat database baru bernama 'topup_game'
# Kemudian jalankan migrasi:
php config/setup_db.php
php config/migrate_chat.php
php config/migrate_bot_tables.php

# Seeding data demo (akun admin, seller, customer, produk):
php config/seed_demo.php
```

> ✅ Konfigurasi database **otomatis mendeteksi** environment:
> - `localhost` → menggunakan `root` tanpa password (Laragon)
> - Domain production → menggunakan konfigurasi cPanel

### 3. Setup Bot Telegram
```bash
cd bot
cp .env.example .env
# Edit .env sesuai kredensial Anda
npm install
npm run dev
```

**Isi `.env` minimum:**
```env
TELEGRAM_BOT_TOKEN=your_token_here
DATABASE_URL="mysql://root@localhost:3306/topup_game"
WEBSITE_BASE_URL="http://localhost/TopUpin"
DEEP_LINK_SECRET="your_secret_32_chars_minimum"
```

### 4. Akses Aplikasi
| URL | Keterangan |
|---|---|
| `http://localhost/TopUpin/` | Halaman utama website |
| `http://localhost/TopUpin/admin/` | Admin Panel (admin / admin123) |
| `http://localhost/TopUpin/seller/` | Seller Panel |
| `http://localhost/TopUpin/demo.php` | Panduan demo lengkap |

---

## 🌍 Deployment ke cPanel

### Cara 1: Otomatis via `.cpanel.yml` (Git Version Control)
File `.cpanel.yml` sudah dikonfigurasi untuk **otomatis memisahkan** folder website dan bot:
- File PHP → `public_html/`
- Folder Bot → `~/bot/` (di luar public_html, aman dari akses publik)

Setiap kali Anda push ke GitHub, cPanel akan otomatis mendeploy kedua bagian tersebut.

### Cara 2: Manual via `manual_deploy.php`
```
1. Pull repository terbaru di cPanel Git Version Control
2. Buka: https://topupinweb.my.id/manual_deploy.php
3. Script otomatis menyalin file ke tempat yang benar
4. Restart Node.js App di cPanel Setup Node.js App
```

---

## 📁 Struktur Folder

```
TopUpin/
├── .cpanel.yml             # Konfigurasi deploy otomatis cPanel
├── index.php               # Halaman utama + landing page
├── catalog.php             # Katalog produk & game
├── detail.php              # Detail produk
├── checkout.php            # Proses checkout
├── pembayaran.php          # Halaman pembayaran
├── riwayat.php             # Riwayat transaksi user
├── demo.php                # Panduan demo untuk penguji
├── tentang.php             # Halaman tentang platform
├── admin/                  # Admin Panel
├── seller/                 # Seller Panel
├── api/                    # Webhook endpoint untuk bot
│   ├── webhook_trx.php     # Endpoint webhook transaksi
│   └── chat_handler.php    # Handler pesan CS
├── config/
│   ├── koneksi.php         # Konfigurasi DB (auto-detect env)
│   ├── setup_db.php        # Migrasi tabel utama
│   ├── migrate_bot_tables.php # Migrasi tabel bot
│   └── seed_demo.php       # Seeding data demo
└── bot/                    # Bot Telegram (Node.js)
    ├── src/
    │   ├── bot/bot.ts      # Logika utama bot
    │   └── services/       # Layanan & state management
    ├── prisma/schema.prisma # Schema database
    └── .env.example        # Template konfigurasi bot
```

---

## 👤 Akun Demo

| Role | Username / ID | Password |
|---|---|---|
| Admin | `admin` | `admin123` |
| Seller | `seller@demo.com` | `demo1234` |
| Customer | `customer@demo.com` | `demo1234` |

> 💡 Atau kunjungi [demo.php](https://topupinweb.my.id/demo.php) untuk login otomatis.

---

## 📄 Lisensi

Proyek ini dibuat untuk keperluan akademik dan pembelajaran. © 2026 TopUpIn Team.
