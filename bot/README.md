# TopUpin Bot Telegram

Bot Telegram CS & Admin untuk website [TopUpin](https://topupinweb.my.id).  
Dibangun dengan **grammY** (TypeScript) + **Prisma** + **MySQL**.

---

## Arsitektur

```
Internet ──── Apache/Nginx (port 443 HTTPS)
                │
                ├── /                     → PHP Website (Apache normal)
                └── /telegram-webhook     → Proxy ke Bot Node.js (localhost:3001)

Bot Node.js (localhost:3001)
  ├── POST /telegram-webhook     ← Telegram mengirim update ke sini
  ├── GET  /health               ← Health check
  ├── GET  /api/telegram-link    ← PHP website minta link
  ├── POST /api/notify-transaction ← PHP website trigger notif ke user
  └── POST /api/create-transaction ← Buat transaksi via bot
```

---

## Setup Lokal (Development)

```bash
cd bot
npm install
npm run dev
```

Bot akan berjalan dalam mode **long polling** (tidak perlu domain publik).

---

## Deploy ke Server Production

### 1. Upload & Install Dependencies

```bash
# Di server, masuk ke folder bot
cd /var/www/topupinweb/bot

# Install dependencies (tanpa devDependencies)
npm install --omit=dev

# Build TypeScript
npm run build
```

### 2. Konfigurasi `.env`

Salin `.env.example` menjadi `.env` dan isi nilai-nilainya:

```bash
cp .env.example .env
nano .env
```

Nilai penting yang harus diisi:
| Variable | Nilai |
|---|---|
| `TELEGRAM_BOT_TOKEN` | Token dari @BotFather |
| `WEBHOOK_DOMAIN` | `https://topupinweb.my.id` |
| `WEBHOOK_SECRET_TOKEN` | Token acak panjang (buat sendiri) |
| `DATABASE_URL` | Connection string MySQL server |
| `DEEP_LINK_SECRET` | **Harus sama** dengan nilai di `config/telegram_helper.php` |

### 3. Konfigurasi Apache — Proxy Webhook

Tambahkan konfigurasi berikut di Virtual Host Apache Anda (`/etc/apache2/sites-available/topupinweb.conf`):

```apache
<VirtualHost *:443>
    ServerName topupinweb.my.id

    # ... konfigurasi SSL yang sudah ada ...

    # Proxy /telegram-webhook ke Node.js bot (port 3001)
    ProxyRequests Off
    ProxyPreserveHost On

    ProxyPass        /telegram-webhook  http://localhost:3001/telegram-webhook
    ProxyPassReverse /telegram-webhook  http://localhost:3001/telegram-webhook

    # Selain itu, arahkan ke PHP seperti biasa
    DocumentRoot /var/www/topupinweb
    # ... konfigurasi PHP lainnya ...
</VirtualHost>
```

Aktifkan modul proxy:
```bash
sudo a2enmod proxy proxy_http
sudo systemctl reload apache2
```

#### Alternatif: Nginx

```nginx
server {
    server_name topupinweb.my.id;

    # Proxy webhook ke bot
    location /telegram-webhook {
        proxy_pass         http://localhost:3001;
        proxy_http_version 1.1;
        proxy_set_header   Host $host;
        proxy_set_header   X-Real-IP $remote_addr;
    }

    # Sisanya ke PHP
    location / {
        root /var/www/topupinweb;
        index index.php;
        try_files $uri $uri/ /index.php?$query_string;
    }
}
```

### 4. Jalankan Bot dengan PM2

```bash
# Install PM2 (jika belum)
npm install -g pm2

# Jalankan bot
pm2 start bot/dist/app.js --name topupin-bot

# Agar auto-start saat server reboot
pm2 startup
pm2 save
```

### 5. Daftarkan Webhook ke Telegram

Setelah server berjalan dan Apache proxy sudah aktif, jalankan perintah ini (ganti TOKEN):

```bash
curl -X POST "https://api.telegram.org/bot8611718971:AAFWvUWoG_b6IV3xbcFsmEvd1-SPKH6xKtc/setWebhook" \
  -H "Content-Type: application/json" \
  -d '{
    "url": "https://topupinweb.my.id/telegram-webhook",
    "secret_token": "topupin_wh_secret_2024_secure"
  }'
```

Verifikasi webhook:
```bash
curl "https://api.telegram.org/bot8611718971:AAFWvUWoG_b6IV3xbcFsmEvd1-SPKH6xKtc/getWebhookInfo"
```

### 6. Generate Prisma Client

```bash
cd bot
npx prisma generate
```

---

## Perintah Berguna

```bash
# Cek status bot
pm2 status topupin-bot

# Lihat log bot
pm2 logs topupin-bot

# Restart bot
pm2 restart topupin-bot

# Cek health API
curl http://localhost:3001/health

# Hapus webhook (kembali ke polling)
curl "https://api.telegram.org/botTOKEN/deleteWebhook"
```

---

## Integrasi dengan Website PHP

Website PHP memanggil bot melalui fungsi di `config/telegram_helper.php`:

```php
// Kirim notifikasi Telegram ke user saat status transaksi berubah
notifyBotTransaction('TRX-5', 'success');

// Cek apakah bot sedang aktif
if (isBotApiAlive()) {
    // bot online
}
```

---

## Environment Variables Lengkap

Lihat file `.env.example` untuk daftar lengkap semua variabel yang diperlukan.
