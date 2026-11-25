# FoodEase (Nola)

Aplikasi pemesanan makanan dan sistem kasir (*Point of Sale*) modern berbasis web untuk kafe dan restoran. Mengusung antarmuka bergaya *Uber Eats* yang minimalis, responsif (*mobile-first*), serta ramah aksesibilitas.

---

## Tech Stack

- **Backend:** PHP Native >= 8.1 (Strict types, PSR-12, PDO MySQL/MariaDB)
- **Database:** MySQL / MariaDB (Prepared statements)
- **Frontend:** HTML5, Tailwind CSS (via CDN), Vanilla JavaScript, jQuery 3.7.1 lokal
- **Web Server:** Apache (XAMPP / LAMP) dengan URL rewrite via `.htaccess`

---

## Struktur Folder

```text
foodease/
├── app/
│   ├── config/          # Konfigurasi aplikasi (config.php)
│   ├── controllers/     # Controller membaca Request & mengembalikan Response
│   ├── core/            # Framework mini inti (Database, Auth, Session, Config, dll.)
│   ├── models/          # Layer akses database via PDO
│   ├── services/        # Logika bisnis dan transaksi database
│   ├── views/           # Template HTML/PHP (admin, auth, cashier, customer)
│   └── bootstrap.php    # Inisialisasi awal, autoloader, dan pemuatan .env
├── database/
│   ├── migrations/      # Skrip migrasi skema SQL terurut
│   ├── schema.sql       # Struktur tabel awal database
│   ├── seed.sql         # Data awal kategori dan menu
│   └── seed-users.php   # Pembuat akun pengguna default (admin, kasir)
├── docs/                # Dokumentasi internal proyek
├── public/              # Root direktori yang dapat diakses publik oleh web server
│   ├── api/             # Endpoint JSON API (AJAX)
│   ├── assets/          # CSS, JS modular, gambar, dan vendor lokal
│   ├── uploads/         # Direktori unggahan berkas gambar
│   ├── index.php        # Entrypoint utama halaman depan / katalog
│   └── ...              # Halaman fungsional (login, cart, checkout, dll.)
├── storage/
│   └── logs/            # Berkas catatan error (app.log)
├── .editorconfig        # Standardisasi format teks antar editor
├── .env.example         # Template konfigurasi variabel lingkungan
├── .gitignore           # Aturan pengabaian berkas sensitif & artefak
└── README.md            # Dokumentasi panduan proyek
```

---

## Panduan Instalasi & Menjalankan

### 1. Kebutuhan Sistem
- Web Server Apache & Database MySQL (misal: XAMPP / WampServer / LAMPP)
- PHP versi 8.1 atau yang lebih baru dengan ekstensi `pdo_mysql` dan `gd` aktif

### 2. Penyiapan Direktori
Tempatkan folder proyek di dalam direktori web root:
```bash
# Untuk XAMPP di Windows:
C:\xampp\htdocs\foodease
```

### 3. Konfigurasi Environment (.env)
Salin berkas template `.env.example` menjadi `.env` di root proyek:
```bash
cp .env.example .env
```
Buka berkas `.env` dan sesuaikan kredensial database lokal:
```ini
APP_NAME=Nola
APP_ENV=development
APP_DEBUG=true
APP_BASE_URL=/foodease
APP_TIMEZONE=Asia/Jakarta

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=foodease
DB_USER=root
DB_PASS=
DB_CHARSET=utf8mb4

SESSION_NAME=nola_session
SESSION_LIFETIME=7200
```
> **Catatan Keamanan:** Berkas `.env` bersifat privat dan tidak boleh di-commit atau dipublikasikan ke repositori git.

### 4. Penyiapan Database
1. Buat database baru bernama `foodease` di phpMyAdmin / MySQL CLI:
   ```sql
   CREATE DATABASE foodease CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
2. Impor berkas skema dan data awal:
   - `database/schema.sql`
   - `database/seed.sql`
   - Migrasi tambahan pada folder `database/migrations/`
3. Buat akun default sistem menggunakan PHP CLI:
   ```bash
   php database/seed-users.php
   ```
   Akun bawaan yang tersedia:
   - **Admin:** Username: `admin` | Password: `admin12345`
   - **Kasir:** Username: `kasir1` | Password: `kasir12345`

### 5. Menjalankan Aplikasi
Nyalakan modul Apache dan MySQL pada panel kontrol XAMPP, lalu akses aplikasi melalui peramban:
- **Katalog Pelanggan:** `http://localhost/foodease/`
- **Portal Kasir:** `http://localhost/foodease/cashier/`
- **Dashboard Admin:** `http://localhost/foodease/admin/`

---

## Verifikasi & Pengujian

Jalankan pengecekan sintaks PHP menyeluruh untuk memastikan integritas berkas:
```bash
# Windows PowerShell
Get-ChildItem -Path . -Recurse -Filter "*.php" | ForEach-Object { php -l $_.FullName }
```

---

## Standar Keamanan & Kode

- Seluruh kueri SQL menggunakan PDO prepared statements (mencegah SQL Injection).
- Token proteksi CSRF diwajibkan pada setiap request mutasi HTTP POST (`X-CSRF-Token`).
- Kredensial dan kata sandi dienkripsi menggunakan `password_hash()` (Argon2id / Bcrypt) dan diverifikasi via `password_verify()`.
- Data pengguna selalu dieksekusi melalui fungsi sanitasi `e()` sebelum dirender ke HTML (mencegah XSS).
