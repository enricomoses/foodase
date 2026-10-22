# PRD — FoodEase

## 1. Ringkasan

FoodEase adalah aplikasi web untuk sebuah kafe dengan dua alur utama:

1. **Pelanggan** membuka website, melihat menu, memasukkan ke keranjang, lalu checkout (makan di tempat / takeaway). Pelanggan tidak perlu membuat akun.
2. **Kasir** melihat pesanan masuk, memproses pembayaran (tunai / QRIS simulasi), mengubah status pesanan, mencetak struk, dan melihat riwayat transaksi. Kasir juga bisa membuat pesanan langsung (pelanggan datang ke kasir).
3. **Admin** mengelola menu, kategori, akun kasir, dan melihat laporan penjualan.

### Tujuan

- Memenuhi ketentuan tugas Tekweb: tanpa framework, memakai HTML, Tailwind, JS, jQuery, AJAX, PHP native, MySQL, dan PHP Session.
- Kode rapi, reusable, dan mudah dikelola walau tanpa framework (layering jelas, folder terpisah).
- Setiap anggota kelompok punya minimal satu modul yang bisa didemokan.

### Bukan tujuan (Out of Scope)

- Pembayaran sungguhan (payment gateway). QRIS hanya simulasi.
- Akun/registrasi pelanggan, poin, voucher, diskon.
- Manajemen stok bahan, multi-cabang, printer thermal langsung.
- Notifikasi real-time dengan WebSocket (cukup polling AJAX).
- Aplikasi mobile native.

---

## 2. Keputusan yang Diasumsikan

Pertanyaan terbuka pada konsep awal dijawab sebagai berikut. Ubah bagian ini bila berbeda dari kebutuhan kelompok, lalu sesuaikan bagian terkait.

| Pertanyaan | Keputusan |
| --- | --- |
| Cara pelanggan memesan | **Keduanya**: lewat website (bayar di kasir) dan langsung ke kasir (kasir input via POS) |
| Peran pengguna | **Pelanggan** (tanpa login), **Kasir**, **Admin** |
| Jenis usaha | **Kafe** (dine-in dengan nomor meja + takeaway) |
| Identitas pelanggan | Tanpa akun. Cukup nama + nomor meja/tipe. Riwayat disimpan lewat PHP Session dan bisa dilacak dengan kode pesanan |
| Model pembayaran | **Bayar dulu di kasir**, baru pesanan diproses dapur |
| Keranjang | Disimpan di `localStorage` browser. Harga **selalu dihitung ulang di server** |
| Mata uang | Rupiah, disimpan sebagai integer (tanpa desimal) |
| Zona waktu | `Asia/Jakarta` (WIB) di PHP dan MySQL |

---

## 3. Peran dan Hak Akses

| Aksi | Pelanggan | Kasir | Admin |
| --- | :---: | :---: | :---: |
| Melihat menu | ✔ | ✔ | ✔ |
| Memesan lewat website | ✔ | – | – |
| Melacak pesanan sendiri (kode/session) | ✔ | ✔ | ✔ |
| Melihat semua pesanan masuk | – | ✔ | ✔ |
| Membuat pesanan lewat POS | – | ✔ | ✔ |
| Memproses pembayaran | – | ✔ | ✔ |
| Mengubah status pesanan | – | ✔ | ✔ |
| Membatalkan pesanan **belum dibayar** | – | ✔ | ✔ |
| Membatalkan pesanan **sudah dibayar** (void) | – | – | ✔ |
| Riwayat transaksi | – | ✔ (milik sendiri) | ✔ (semua) |
| CRUD menu dan kategori | – | – | ✔ |
| Kelola akun kasir | – | – | ✔ |
| Laporan dan dashboard | – | – | ✔ |

Admin dapat membuka halaman kasir. Kasir tidak dapat membuka halaman admin.

---

## 4. Alur Pengguna

### 4.1 Pelanggan memesan lewat website

1. Buka `index.php` → lihat menu, cari, filter kategori.
2. Tambah ke keranjang → `cart.php` ubah jumlah / hapus.
3. `checkout.php` → isi nama, pilih dine-in (nomor meja) atau takeaway, catatan.
4. Server memvalidasi ulang dan membuat pesanan (`pending`, `unpaid`) → tampil kode pesanan, misal `FE-261022-007`.
5. Pelanggan ke kasir dan menyebut kode/nama untuk membayar.
6. Pelanggan memantau status di `track.php` (auto-refresh).

### 4.2 Kasir memproses pesanan

1. Login → `cashier/index.php` menampilkan pesanan masuk (auto-refresh).
2. Buka pesanan → periksa item → **Bayar** (tunai: input nominal, kembalian otomatis; QRIS: simulasi lalu tandai lunas).
3. Pembayaran sukses → status otomatis `processing`, struk bisa dicetak.
4. Setelah siap → `ready`, setelah diambil/diantar → `completed`.

### 4.3 Kasir membuat pesanan langsung (POS)

1. `cashier/pos.php` → pilih menu, isi nama/tipe/meja.
2. **Simpan** (jadi pesanan `unpaid`) atau **Simpan & Bayar** (langsung ke form pembayaran).

### 4.4 Admin

Login → dashboard → kelola kategori, menu, akun kasir → lihat laporan dengan filter tanggal.

---

## 5. Aturan Bisnis

| ID | Aturan |
| --- | --- |
| BR-01 | Total pesanan **dihitung server** dari harga di tabel `menus`. Harga/total dari browser diabaikan. |
| BR-02 | Menu yang `is_available = 0` atau sudah dihapus (soft delete) tidak bisa dipesan. Server menolak dengan pesan yang menyebut nama menunya. |
| BR-03 | Jumlah per item: integer 1–99. Maksimal 30 jenis item per pesanan. |
| BR-04 | `dine_in` wajib `table_number` (1 sampai `order.max_table` di config, default 30). `takeaway` → `table_number = NULL`. |
| BR-05 | Nama pelanggan wajib, 2–50 karakter. Catatan opsional, maksimal 255 karakter. |
| BR-06 | Status pesanan: `pending → processing → ready → completed`, atau `cancelled`. Tidak boleh loncat atau mundur. |
| BR-07 | Pesanan baru: `status = pending`, `payment_status = unpaid`. Pembayaran sukses mengubah menjadi `paid` dan status menjadi `processing` dalam **satu transaksi database**. |
| BR-08 | Tunai: `amount_paid ≥ total`, `change = amount_paid − total`. QRIS (simulasi): `amount_paid = total`, `change = 0`. |
| BR-09 | Satu pesanan hanya punya satu pembayaran. Pesanan `paid`, `cancelled`, atau `completed` tidak bisa dibayar lagi (cegah double-submit dengan `SELECT ... FOR UPDATE`). |
| BR-10 | Pembatalan: pesanan `pending` + `unpaid` → boleh oleh kasir/admin. Pesanan sudah dibayar dan belum `completed` → hanya admin, wajib alasan, pembayaran di-**void**. Pesanan `completed` tidak bisa dibatalkan. |
| BR-11 | `order_details` menyimpan **snapshot** `menu_name` dan `price` saat dipesan, sehingga laporan tidak berubah bila menu diedit. |
| BR-12 | Laporan hanya menghitung `payments.status = 'paid'` (void dikecualikan). |
| BR-13 | Menghapus menu = **soft delete** (`deleted_at`). Menu yang pernah dipesan tidak boleh dihapus permanen. |
| BR-14 | Kategori tidak bisa dihapus bila masih memiliki menu (aktif maupun tidak). |
| BR-15 | Akun nonaktif tidak bisa login. Admin tidak bisa menonaktifkan akunnya sendiri. |
| BR-16 | Kode pesanan `FE-YYMMDD-NNN` (urut per hari). Kode pembayaran `PAY-YYMMDD-NNN`. Dibuat di dalam transaksi + `UNIQUE` index. |
| BR-17 | Riwayat pelanggan dibatasi pada kode pesanan yang tersimpan di session browser tersebut atau yang dicari dengan kode + nama pemesan. |

### Diagram status

```
pending(unpaid) ──bayar──▶ processing(paid) ──▶ ready ──▶ completed
     │                          │                 │
     └──batal (kasir/admin)     └──void + batal (admin saja)──▶ cancelled
```

---

## 6. Kebutuhan Fungsional

Prioritas: **P0** wajib selesai, **P1** nilai tambah demo, **P2** opsional.

### 6.1 Autentikasi (`AUTH`)

| ID | Kebutuhan | Prio |
| --- | --- | :---: |
| FR-AUTH-01 | Login username + password lewat AJAX (`api/auth/login.php`) | P0 |
| FR-AUTH-02 | Password disimpan dengan `password_hash()` dan dicek dengan `password_verify()` | P0 |
| FR-AUTH-03 | `session_regenerate_id(true)` setelah login berhasil | P0 |
| FR-AUTH-04 | Logout menghancurkan session dan cookie | P0 |
| FR-AUTH-05 | Guard per role; akses tanpa hak → redirect ke login (halaman) atau 401/403 (API) | P0 |
| FR-AUTH-06 | Pesan error login generik ("Username atau password salah") | P0 |
| FR-AUTH-07 | Session idle timeout 2 jam | P1 |
| FR-AUTH-08 | Akun nonaktif ditolak login | P0 |

**Acceptance:** admin diarahkan ke `admin/`, kasir ke `cashier/`; mengakses halaman role lain ditolak; session id berubah setelah login.

### 6.2 Kategori (`CAT`) — Admin

| ID | Kebutuhan | Prio |
| --- | --- | :---: |
| FR-CAT-01 | Daftar kategori (AJAX) | P0 |
| FR-CAT-02 | Tambah, ubah nama dan urutan (modal, tanpa reload) | P0 |
| FR-CAT-03 | Hapus kategori (ditolak bila punya menu, BR-14) | P0 |

### 6.3 Menu (`MENU`) — Admin

| ID | Kebutuhan | Prio |
| --- | --- | :---: |
| FR-MENU-01 | Tabel menu dengan pencarian, filter kategori, pagination (AJAX) | P0 |
| FR-MENU-02 | Tambah menu: nama, kategori, harga, deskripsi, foto (opsional) | P0 |
| FR-MENU-03 | Edit menu termasuk ganti foto | P0 |
| FR-MENU-04 | Hapus menu (soft delete, BR-13) dengan konfirmasi | P0 |
| FR-MENU-05 | Toggle ketersediaan langsung dari tabel | P0 |
| FR-MENU-06 | Validasi upload: JPG/PNG/WebP, maks 2 MB, cek MIME asli, nama file di-random | P0 |

### 6.4 Akun Kasir (`USER`) — Admin

| ID | Kebutuhan | Prio |
| --- | --- | :---: |
| FR-USER-01 | Daftar akun kasir | P0 |
| FR-USER-02 | Tambah kasir (nama, username unik, password minimal 8 karakter) | P0 |
| FR-USER-03 | Edit nama, aktif/nonaktif | P0 |
| FR-USER-04 | Reset password | P1 |

### 6.5 Menu Pelanggan (`CMENU`)

| ID | Kebutuhan | Prio |
| --- | --- | :---: |
| FR-CMENU-01 | Tampilkan menu aktif, dikelompokkan/difilter per kategori | P0 |
| FR-CMENU-02 | Pencarian dengan AJAX (debounce 300 ms) | P1 |
| FR-CMENU-03 | Menu tidak tersedia tampil "Habis" dan tidak bisa ditambahkan | P0 |
| FR-CMENU-04 | Modal detail menu (deskripsi, harga, jumlah) | P1 |

### 6.6 Keranjang (`CART`)

| ID | Kebutuhan | Prio |
| --- | --- | :---: |
| FR-CART-01 | Tambah item, ubah jumlah, hapus item, kosongkan | P0 |
| FR-CART-02 | Tersimpan di `localStorage`, badge jumlah di navbar | P0 |
| FR-CART-03 | Saat halaman keranjang dibuka, sinkronkan harga dan ketersediaan dari server | P0 |
| FR-CART-04 | Ringkasan subtotal dan total | P0 |
| FR-CART-05 | Drawer keranjang (slide-over dari tombol "Keranjang" di navbar) sebagai pintasan ke checkout | P2 |

### 6.7 Checkout (`CHK`)

| ID | Kebutuhan | Prio |
| --- | --- | :---: |
| FR-CHK-01 | Form: nama, tipe pesanan, nomor meja (muncul bila dine-in), catatan | P0 |
| FR-CHK-02 | Validasi di client **dan** server (BR-01 s.d. BR-05) | P0 |
| FR-CHK-03 | Pesanan + detail disimpan dalam satu transaksi DB | P0 |
| FR-CHK-04 | Tampilkan kode pesanan dan instruksi "bayar di kasir"; kosongkan keranjang | P0 |

### 6.8 Pelacakan Pesanan (`TRK`)

| ID | Kebutuhan | Prio |
| --- | --- | :---: |
| FR-TRK-01 | Halaman status pesanan (judul dinamis + progress bar 4 segmen: Menunggu bayar → Diproses → Siap → Selesai) | P0 |
| FR-TRK-02 | Auto-refresh status tiap 10 detik (AJAX polling) | P1 |
| FR-TRK-03 | Riwayat pesanan dari session browser | P0 |
| FR-TRK-04 | Cari pesanan dengan kode + nama pemesan | P1 |

### 6.9 Pesanan Masuk Kasir (`COR`)

| ID | Kebutuhan | Prio |
| --- | --- | :---: |
| FR-COR-01 | Daftar pesanan dengan tab status (Belum dibayar, Diproses, Siap, Selesai, Batal) | P0 |
| FR-COR-02 | Pencarian kode/nama, filter tipe dan tanggal | P1 |
| FR-COR-03 | Auto-refresh tiap 8 detik + indikator pesanan baru | P1 |
| FR-COR-04 | Modal detail pesanan (item, catatan, total, aksi sesuai status) | P0 |

### 6.10 POS Kasir (`POS`)

| ID | Kebutuhan | Prio |
| --- | --- | :---: |
| FR-POS-01 | Grid menu + panel pesanan (item, jumlah, total) | P0 |
| FR-POS-02 | Input nama, tipe, nomor meja | P0 |
| FR-POS-03 | Simpan pesanan, atau Simpan & Bayar | P0 |

### 6.11 Pembayaran (`PAY`)

| ID | Kebutuhan | Prio |
| --- | --- | :---: |
| FR-PAY-01 | Pilih metode tunai / QRIS | P0 |
| FR-PAY-02 | Tunai: input nominal, tombol nominal cepat, kembalian otomatis, validasi kurang bayar | P0 |
| FR-PAY-03 | QRIS simulasi: tampilkan placeholder QR + tombol "Tandai lunas" | P2 |
| FR-PAY-04 | Proses pembayaran atomik (BR-07, BR-09) | P0 |
| FR-PAY-05 | Struk siap cetak (`cashier/receipt.php`, CSS `@media print`) | P0 |

### 6.12 Status dan Pembatalan (`STS`)

| ID | Kebutuhan | Prio |
| --- | --- | :---: |
| FR-STS-01 | Tombol aksi berikutnya sesuai status (Siap, Selesai) tanpa reload | P0 |
| FR-STS-02 | Batalkan pesanan belum dibayar (kasir/admin) | P0 |
| FR-STS-03 | Void pembayaran + batalkan (admin, wajib alasan) | P1 |

### 6.13 Riwayat Transaksi (`HIS`)

| ID | Kebutuhan | Prio |
| --- | --- | :---: |
| FR-HIS-01 | Daftar pembayaran + pagination; kasir hanya miliknya, admin semua | P0 |
| FR-HIS-02 | Filter tanggal, metode, pencarian kode | P0 |
| FR-HIS-03 | Detail dan cetak ulang struk | P1 |

### 6.14 Laporan dan Dashboard (`RPT`) — Admin

| ID | Kebutuhan | Prio |
| --- | --- | :---: |
| FR-RPT-01 | Filter rentang tanggal (default: bulan ini) | P0 |
| FR-RPT-02 | Kartu ringkasan: total pendapatan, jumlah transaksi, rata-rata per transaksi | P0 |
| FR-RPT-03 | Menu terlaris (top 10: nama, qty, pendapatan) | P1 |
| FR-RPT-04 | Pendapatan per metode pembayaran | P1 |
| FR-RPT-05 | Tabel pendapatan harian | P1 |
| FR-RPT-06 | Dashboard admin: ringkasan hari ini | P2 |
| FR-RPT-07 | Ekspor CSV | P2 |

---

## 7. Model Data

Engine InnoDB, charset `utf8mb4`, collation `utf8mb4_unicode_ci`. SQL lengkap ada di `execution-guide.md` (Langkah 5) dan disimpan sebagai `database/schema.sql`.

```
users 1 ───< orders (created_by, cancelled_by)
users 1 ───< payments (cashier_id)
categories 1 ───< menus
orders 1 ───< order_details >─── 1 menus
orders 1 ─── 0..1 payments
```

### `users`

| Kolom | Tipe | Catatan |
| --- | --- | --- |
| id | INT UNSIGNED PK AI | |
| name | VARCHAR(100) | |
| username | VARCHAR(50) | UNIQUE |
| password_hash | VARCHAR(255) | bcrypt/argon dari `password_hash()` |
| role | ENUM('admin','cashier') | |
| is_active | TINYINT(1) | default 1 |
| last_login_at | DATETIME NULL | |
| created_at, updated_at | DATETIME | |

### `categories`

| Kolom | Tipe | Catatan |
| --- | --- | --- |
| id | INT UNSIGNED PK AI | |
| name | VARCHAR(60) | UNIQUE |
| sort_order | SMALLINT UNSIGNED | default 0 |
| created_at, updated_at | DATETIME | |

### `menus`

| Kolom | Tipe | Catatan |
| --- | --- | --- |
| id | INT UNSIGNED PK AI | |
| category_id | INT UNSIGNED FK | `ON DELETE RESTRICT` |
| name | VARCHAR(100) | |
| description | TEXT NULL | |
| price | INT UNSIGNED | Rupiah |
| image | VARCHAR(255) NULL | nama file di `public/uploads/menus/` |
| is_available | TINYINT(1) | default 1 |
| deleted_at | DATETIME NULL | soft delete |
| created_at, updated_at | DATETIME | |

### `orders`

| Kolom | Tipe | Catatan |
| --- | --- | --- |
| id | BIGINT UNSIGNED PK AI | |
| order_code | VARCHAR(20) | UNIQUE, `FE-YYMMDD-NNN` |
| customer_name | VARCHAR(50) | |
| order_type | ENUM('dine_in','takeaway') | |
| table_number | TINYINT UNSIGNED NULL | |
| notes | VARCHAR(255) NULL | |
| source | ENUM('online','cashier') | asal pesanan |
| status | ENUM('pending','processing','ready','completed','cancelled') | |
| payment_status | ENUM('unpaid','paid') | denormalisasi, diubah dalam transaksi yang sama dengan `payments` |
| total | INT UNSIGNED | |
| cancel_reason | VARCHAR(255) NULL | |
| cancelled_by | INT UNSIGNED NULL FK users | |
| created_by | INT UNSIGNED NULL FK users | kasir pembuat pesanan POS |
| created_at, updated_at | DATETIME | |

Index: `status`, `payment_status`, `created_at`.

### `order_details`

| Kolom | Tipe | Catatan |
| --- | --- | --- |
| id | BIGINT UNSIGNED PK AI | |
| order_id | BIGINT UNSIGNED FK | `ON DELETE CASCADE` |
| menu_id | INT UNSIGNED FK | `ON DELETE RESTRICT` |
| menu_name | VARCHAR(100) | snapshot |
| price | INT UNSIGNED | snapshot |
| qty | SMALLINT UNSIGNED | |
| subtotal | INT UNSIGNED | price × qty |

### `payments`

| Kolom | Tipe | Catatan |
| --- | --- | --- |
| id | BIGINT UNSIGNED PK AI | |
| payment_code | VARCHAR(20) | UNIQUE, `PAY-YYMMDD-NNN` |
| order_id | BIGINT UNSIGNED FK | **UNIQUE** (satu pembayaran per pesanan) |
| method | ENUM('cash','qris') | |
| total | INT UNSIGNED | salinan `orders.total` |
| amount_paid | INT UNSIGNED | |
| change_amount | INT UNSIGNED | |
| status | ENUM('paid','void') | |
| cashier_id | INT UNSIGNED FK users | |
| paid_at | DATETIME | |
| voided_at | DATETIME NULL | |
| voided_by | INT UNSIGNED NULL FK users | |
| void_reason | VARCHAR(255) NULL | |

---

## 8. Arsitektur dan Struktur Folder

### 8.1 Prinsip

- **Hanya folder `public/` yang boleh diakses browser.** Kode PHP inti berada di `app/`.
- **Layering:** `Halaman/Endpoint (public/)` → `Controller` → `Service` → `Model` → `Database (PDO)`.
- Semua query memakai **PDO prepared statements**.
- Satu file endpoint = satu aksi, sangat tipis (±10 baris): bootstrap → guard → controller → JSON.
- JavaScript dipecah per halaman + modul inti yang dipakai bersama.

### 8.2 Tanggung jawab layer

| Layer | Boleh | Tidak boleh |
| --- | --- | --- |
| `public/*.php` (halaman) | Panggil `Auth` guard, ambil data awal, render `View` | Query SQL, logika bisnis |
| `public/api/**/*.php` | Cek method, guard, CSRF, panggil controller | Query SQL, logika bisnis |
| `controllers/` | Baca input (`Request`), validasi (`Validator`), panggil service/model, kembalikan `Response` | HTML, SQL langsung |
| `services/` | Logika bisnis, transaksi DB, gabungan beberapa model | Baca `$_POST`/`$_GET`, output HTML |
| `models/` | Query SQL untuk satu tabel/agregat | Logika bisnis, session |
| `views/` | HTML + sedikit logika tampilan, selalu `e()` untuk output | SQL |

### 8.3 Struktur folder

```
foodease/                          ← C:\xampp\htdocs\foodease
├── .htaccess                      # arahkan semua request ke public/
├── .gitignore
├── AGENTS.md                      # aturan kerja untuk agent
├── README.md
├── prd.md
├── execution-guide.md
│
├── app/                           # TIDAK bisa diakses langsung dari browser
│   ├── bootstrap.php              # konstanta, autoload, config, timezone, error mode
│   ├── config/
│   │   ├── config.php             # konfigurasi default
│   │   └── config.local.example.php   # salin ke config.local.php (di-gitignore)
│   ├── core/                      # infrastruktur umum, tidak tahu bisnis kafe
│   │   ├── Config.php
│   │   ├── Database.php           # singleton PDO
│   │   ├── Request.php            # input bersih (get/post/json/method)
│   │   ├── Response.php           # json(), redirect(), error()
│   │   ├── Session.php
│   │   ├── Auth.php               # login state + guard role
│   │   ├── Csrf.php
│   │   ├── Validator.php
│   │   ├── View.php               # render view + layout
│   │   └── helpers.php            # e(), url(), asset(), rupiah(), dll.
│   ├── models/
│   │   ├── User.php
│   │   ├── Category.php
│   │   ├── Menu.php
│   │   ├── Order.php
│   │   ├── OrderDetail.php
│   │   └── Payment.php
│   ├── services/
│   │   ├── OrderService.php       # buat pesanan, hitung total, ubah status, batal
│   │   ├── PaymentService.php     # bayar, void
│   │   ├── ReportService.php
│   │   └── UploadService.php      # validasi + simpan gambar
│   ├── controllers/
│   │   ├── AuthController.php
│   │   ├── CategoryController.php
│   │   ├── MenuController.php
│   │   ├── UserController.php
│   │   ├── OrderController.php
│   │   ├── PaymentController.php
│   │   └── ReportController.php
│   └── views/
│       ├── layouts/               # public.php, cashier.php, admin.php, auth.php, print.php
│       ├── partials/              # head.php, navbar.php, sidebar.php, modal.php, toast.php, icons.php, pagination.php
│       ├── auth/                  # login.php
│       ├── customer/              # menu.php, cart.php, checkout.php, track.php, history.php
│       ├── cashier/               # orders.php, pos.php, history.php
│       ├── admin/                 # dashboard.php, categories.php, menus.php, users.php, reports.php
│       └── receipt/               # receipt.php
│
├── public/                        # satu-satunya document root efektif
│   ├── .htaccess
│   ├── index.php                  # menu pelanggan
│   ├── cart.php
│   ├── checkout.php
│   ├── track.php                  # status satu pesanan
│   ├── orders.php                 # riwayat pesanan (session)
│   ├── login.php
│   ├── logout.php
│   ├── cashier/
│   │   ├── index.php              # pesanan masuk
│   │   ├── pos.php
│   │   ├── history.php
│   │   └── receipt.php
│   ├── admin/
│   │   ├── index.php              # dashboard
│   │   ├── categories.php
│   │   ├── menus.php
│   │   ├── users.php
│   │   └── reports.php
│   ├── api/                       # endpoint AJAX (JSON), satu file per aksi
│   │   ├── auth/        login.php, logout.php
│   │   ├── categories/  list.php, create.php, update.php, delete.php
│   │   ├── menus/       list.php, detail.php, create.php, update.php, delete.php, toggle.php
│   │   ├── users/       list.php, create.php, update.php, reset-password.php
│   │   ├── orders/      create.php, track.php, mine.php, list.php, detail.php,
│   │   │                create-pos.php, update-status.php, cancel.php
│   │   ├── payments/    pay.php, void.php, list.php
│   │   └── reports/     summary.php, best-sellers.php, daily.php
│   ├── assets/
│   │   ├── css/         app.css            # komponen kecil di luar Tailwind
│   │   ├── js/
│   │   │   ├── core/    app.js, api.js, ui.js, format.js, icons.js, cart-store.js
│   │   │   └── pages/   login.js, menu.js, cart.js, checkout.js, track.js, history.js,
│   │   │                cashier-orders.js, pos.js, payment.js, cashier-history.js,
│   │   │                admin-categories.js, admin-menus.js, admin-users.js, admin-reports.js
│   │   ├── vendor/jquery/   jquery-3.7.1.min.js
│   │   └── img/         placeholder-menu.svg, logo.svg
│   └── uploads/menus/             # foto menu (isi di-gitignore)
│
├── database/
│   ├── schema.sql
│   ├── seed.sql
│   └── seed-users.php             # CLI: buat akun admin + kasir dengan password ter-hash
│
├── storage/
│   └── logs/                      # error log aplikasi (di-gitignore)
└── docs/                          # opsional: ERD, screenshot, catatan demo
```

### 8.4 Konvensi

| Area | Konvensi |
| --- | --- |
| PHP | `declare(strict_types=1);`, namespace `App\...` (folder huruf kecil, class `PascalCase`), PSR-12 |
| Autoload | Autoloader sederhana di `bootstrap.php` (tanpa Composer) |
| Penamaan | Class `PascalCase`, method `camelCase`, kolom DB `snake_case`, file view `kebab-case.php` |
| Output HTML | Selalu lewat `e()` (htmlspecialchars) |
| URL dan aset | Selalu lewat `url()` dan `asset()`, jangan hardcode path |
| Response API | Format tunggal (lihat 9.1) |
| JS | Satu file per halaman, `$(function(){ ... })`, tidak ada JS inline panjang di PHP |
| Komentar | Seperlunya, bahasa Indonesia atau Inggris secara konsisten |
| Git | Branch per fitur, commit kecil: `feat(menu): crud menu admin` |

---

## 9. Spesifikasi API Internal

Semua endpoint mengembalikan JSON. Method selain yang diizinkan → `405`. CSRF token wajib pada `POST` (header `X-CSRF-Token`).

### 9.1 Format response

```json
// sukses
{ "success": true, "message": "Pesanan dibuat", "data": { } }

// gagal
{ "success": false, "message": "Data tidak valid", "errors": { "customer_name": "Nama wajib diisi" } }
```

Kode HTTP: `200` sukses, `201` dibuat, `401` belum login, `403` tidak berhak / CSRF salah, `404` tidak ditemukan, `405` method salah, `409` konflik status, `422` validasi gagal, `500` error server (pesan generik; detail ke log).

### 9.2 Daftar endpoint

| Endpoint | Method | Akses | Fungsi |
| --- | :---: | --- | --- |
| `api/auth/login.php` | POST | publik | Login |
| `api/auth/logout.php` | POST | login | Logout |
| `api/categories/list.php` | GET | publik | Daftar kategori |
| `api/categories/create.php` | POST | admin | Tambah kategori |
| `api/categories/update.php` | POST | admin | Ubah kategori |
| `api/categories/delete.php` | POST | admin | Hapus kategori |
| `api/menus/list.php` | GET | publik* | Daftar menu (`q`, `category_id`, `page`, `ids[]`). Publik hanya melihat menu aktif; admin dapat `include_unavailable=1` |
| `api/menus/detail.php` | GET | publik | Detail satu menu |
| `api/menus/create.php` | POST | admin | Tambah (multipart, foto) |
| `api/menus/update.php` | POST | admin | Ubah (multipart) |
| `api/menus/delete.php` | POST | admin | Soft delete |
| `api/menus/toggle.php` | POST | admin | Toggle ketersediaan |
| `api/users/list.php` | GET | admin | Daftar kasir |
| `api/users/create.php` | POST | admin | Tambah kasir |
| `api/users/update.php` | POST | admin | Ubah nama / aktif |
| `api/users/reset-password.php` | POST | admin | Reset password |
| `api/orders/create.php` | POST | publik | Checkout pelanggan |
| `api/orders/track.php` | GET | publik | Status pesanan (`code`, + `name` bila bukan dari session) |
| `api/orders/mine.php` | GET | publik | Riwayat dari session |
| `api/orders/list.php` | GET | kasir/admin | Pesanan (`status`, `q`, `type`, `date`, `page`) |
| `api/orders/detail.php` | GET | kasir/admin | Detail pesanan + item + pembayaran |
| `api/orders/create-pos.php` | POST | kasir/admin | Buat pesanan dari POS |
| `api/orders/update-status.php` | POST | kasir/admin | `processing→ready`, `ready→completed` |
| `api/orders/cancel.php` | POST | kasir/admin | Batalkan (aturan BR-10) |
| `api/payments/pay.php` | POST | kasir/admin | Proses pembayaran |
| `api/payments/void.php` | POST | admin | Void pembayaran + batalkan |
| `api/payments/list.php` | GET | kasir/admin | Riwayat transaksi |
| `api/reports/summary.php` | GET | admin | Ringkasan pendapatan (`from`, `to`) |
| `api/reports/best-sellers.php` | GET | admin | Menu terlaris |
| `api/reports/daily.php` | GET | admin | Pendapatan harian + per metode |

Contoh payload checkout (`api/orders/create.php`):

```json
{
  "customer_name": "Dina",
  "order_type": "dine_in",
  "table_number": 7,
  "notes": "Kopi tanpa gula",
  "items": [ { "menu_id": 3, "qty": 2 }, { "menu_id": 9, "qty": 1 } ]
}
```

Field `price` / `total` sengaja **tidak** diterima.

---

## 10. Keamanan

| Ancaman | Penanganan |
| --- | --- |
| SQL Injection | PDO prepared statements di semua query, `ATTR_EMULATE_PREPARES = false` |
| XSS | `e()` untuk semua output; di JS pakai `.text()` atau fungsi escape, bukan `.html()` untuk data pengguna |
| CSRF | Token per session, dikirim lewat header `X-CSRF-Token`, diverifikasi di semua `POST` |
| Session hijacking/fixation | Cookie `HttpOnly`, `SameSite=Lax`, `session_regenerate_id(true)` saat login, timeout idle |
| Brute force login | Batasi percobaan gagal (mis. 5× per 10 menit per username+IP, disimpan di session/tabel ringan) — P1 |
| Manipulasi harga | Harga hanya dari DB (BR-01) |
| Double payment | Transaksi + `FOR UPDATE` + unique `order_id` di `payments` (BR-09) |
| Upload berbahaya | Cek MIME dengan `finfo`, ekstensi whitelist, nama random, batas 2 MB, folder upload tidak mengeksekusi PHP (`php_flag engine off` / `.htaccess`) |
| Akses file internal | Root `.htaccess` mengarahkan semua ke `public/`; `Options -Indexes` |
| Kebocoran error | `display_errors` mati di mode produksi; error dicatat ke `storage/logs/` |
| Privilege escalation | Role dicek di **server** pada setiap halaman dan endpoint, bukan hanya disembunyikan di UI |

---

## 11. Kebutuhan Non-Fungsional

- **Kompatibilitas:** PHP ≥ 8.1, MariaDB/MySQL bawaan XAMPP, browser modern (Chrome, Edge, Firefox).
- **Responsif:** halaman pelanggan *mobile-first*; halaman kasir dioptimalkan untuk desktop/tablet; admin desktop.
- **Performa:** daftar menu dan pesanan dipaginasi; polling ringan (8–10 detik), hentikan polling saat tab tidak aktif (`document.hidden`).
- **Aksesibilitas dasar:** label pada input, kontras cukup, fokus terlihat, tombol dapat dijangkau keyboard.
- **Maintainability:** tidak ada duplikasi logika bisnis; validasi aturan bisnis hanya di service.
- **Observability:** error server dicatat ke `storage/logs/app.log`.

---

## 12. Pedoman UI/UX — Gaya Uber Eats

### 12.1 Prinsip

Bersih, dominan hitam-putih, foto sebagai fokus, tombol pil besar, ruang kosong lega, hierarki lewat ukuran dan ketebalan huruf. Terinspirasi pola Uber/Uber Eats; tidak memakai logo, nama, atau font milik Uber (pakai wordmark teks "FoodEase" dan font sistem).

### 12.2 Palet

| Peran | Token Tailwind | Hex | Pemakaian |
| --- | --- | --- | --- |
| Ink | `ink` | #000000 | Teks utama, tombol utama, chip aktif, badge "Diproses" |
| Putih | `white` | #FFFFFF | Latar halaman dan kartu |
| Surface | `surface` | #F6F6F6 | Latar input, chip, tombol sekunder, hover baris |
| Garis | `line` | #E2E2E2 | Border kartu, tabel, pemisah |
| Teks sekunder | `subtle` | #545454 | Deskripsi, label, waktu |
| Aksen hijau | `accent` (DEFAULT #06C167, dark #048848, light #E6F8EF) | | Hanya status sukses (Siap, Lunas), tanda centang, dan satu highlight kecil |
| Bahaya | `danger` (DEFAULT #E11900, light #FDECEA) | | Error, Batal, hapus |

Aturan: tombol utama **selalu hitam**, bukan hijau. Hijau tidak dipakai sebagai warna tombol atau latar besar.

### 12.3 Aturan komponen

| Komponen | Aturan |
| --- | --- |
| Tipografi | Font sistem (`font-sans` default Tailwind). Hanya bobot 500 (`font-medium`) dan 700 (`font-bold`). Judul besar dan tebal dengan `tracking-tight` (H1 `text-3xl md:text-4xl`, H2 `text-2xl`). Teks isi `text-base`, sekunder `text-sm text-subtle` |
| Tombol utama | `h-12 px-6 rounded-full bg-black text-white font-medium hover:bg-neutral-800`; di mobile boleh `w-full` |
| Tombol sekunder | `h-12 px-6 rounded-full bg-surface text-black font-medium hover:bg-neutral-200` |
| Tombol kecil | Tinggi `h-9 px-4`, bentuk pil sama |
| Tombol destruktif | `bg-danger-light text-danger rounded-full` (bukan merah solid, kecuali konfirmasi akhir) |
| Target sentuh | Minimal 44 px untuk semua elemen yang bisa diketuk |
| Input | `h-12 rounded-lg bg-surface px-4 border border-transparent focus:bg-white focus:border-black focus:outline-none`; label di atas input, error `text-danger text-sm` di bawahnya |
| Chip kategori | Pil `h-10 px-4 bg-surface`; aktif `bg-black text-white`; baris scroll horizontal tanpa scrollbar |
| Kartu menu | Horizontal: teks di kiri (nama `font-medium`, harga, deskripsi 2 baris `text-subtle`), foto persegi 112 px di kanan dengan sudut `rounded-xl`; tombol "+" bulat putih `shadow-sm` menumpuk di pojok kanan bawah foto. Kartu ber-border `border-line rounded-xl`, hover `shadow-sm`. Menu habis: foto dan teks `opacity-50`, label "Habis", tanpa tombol "+" |
| Modal | Overlay `bg-black/50`; kartu `rounded-2xl`. Di mobile tampil sebagai **bottom sheet** (menempel bawah, sudut atas membulat). Tombol aksi utama selebar penuh di bagian bawah |
| Toast | Kapsul hitam, teks putih, `rounded-lg`, muncul di tengah bawah, hilang otomatis |
| Badge status | Pil kecil `text-xs font-medium`: Menunggu bayar = `bg-surface text-black`; Diproses = `bg-black text-white`; Siap = `bg-accent-light text-accent-dark`; Selesai = `border border-line text-subtle`; Dibatalkan = `bg-danger-light text-danger` |
| Tabel | Tanpa garis vertikal, baris dipisah `border-line`, header `text-sm text-subtle`, angka rupiah rata kanan, baris hover `bg-surface` |
| Ikon | SVG inline outline, stroke 2, ukuran seragam (20/24 px): cari, keranjang, plus, minus, tutup, centang. Tidak ada emoji |
| Gambar | Foto dominan; tanpa foto → `placeholder-menu.svg` abu netral (`bg-surface`). Selalu `object-cover` |
| Bayangan | Hanya `shadow-sm`/`shadow-lg` lembut pada elemen melayang (tombol "+", modal, drawer). Tanpa gradient atau glassmorphism |
| Animasi | Fungsional saja: transisi hover/fokus singkat, skeleton `animate-pulse` saat loading, sheet naik/turun. Tidak ada animasi dekoratif |
| Spasi | Lega dan konsisten (skala 4/6/8). Lebar konten pelanggan `max-w-6xl`, kasir/admin `max-w-7xl` |
| State | Wajib ada: loading (skeleton abu), kosong (teks + tindakan), error (pesan + "Coba lagi") |
| Bahasa | Teks antarmuka Indonesia, singkat dan lugas |

### 12.4 Sketsa halaman

| Halaman | Layout |
| --- | --- |
| Menu pelanggan | Navbar putih lengket di atas: wordmark "FoodEase" tebal di kiri, kolom cari berbentuk pil `bg-surface` di tengah (desktop; di mobile pindah ke bawah navbar), tautan "Pesanan" dan tombol pil hitam "Keranjang · n" di kanan. Di bawahnya baris chip kategori. Menu dikelompokkan per kategori dengan judul H2 tebal, grid kartu horizontal 1 kolom (mobile) / 2 (tablet) / 3 (desktop). Klik kartu membuka modal/bottom sheet detail: foto lebar di atas, nama besar, deskripsi, stepper jumlah, tombol hitam selebar penuh "Tambah · Rp xx.xxx" |
| Keranjang | Dua kolom: kiri daftar item (foto kecil, nama, harga, stepper pil, hapus), kanan kartu ringkasan sticky dengan tombol hitam "Lanjut ke checkout". Mobile: bar bawah lengket berisi total + tombol |
| Checkout | Kolom tunggal `max-w-xl` berisi kartu-kartu dengan judul tebal: "Tipe pesanan" (toggle pil dua pilihan **Makan di tempat / Bawa pulang**, aktif putih berbayangan di dalam wadah abu), "Detail" (nama, nomor meja, catatan), "Pesanan Anda" (ringkasan). Tombol hitam "Buat pesanan" di bawah |
| Status pesanan | Judul besar dinamis ("Menunggu pembayaran di kasir", "Pesananmu sedang disiapkan", "Pesanan siap diambil"), progress bar 4 segmen tipis (terisi = hitam, sisanya abu), kode pesanan besar, daftar item, total |
| Kasir – pesanan masuk | Sidebar putih berborder kanan; item aktif `bg-surface font-bold rounded-lg`. Konten: judul H1, tab status gaya chip, tabel bersih, klik baris → panel/modal detail dengan tombol aksi pil |
| Kasir – POS | Dua kolom: kiri chip kategori + grid menu (kartu sama dengan pelanggan, tap "+" menambah); kanan panel pesanan sticky `border-line rounded-xl` dengan total dan tombol "Simpan" (sekunder) + "Simpan & Bayar" (utama) |
| Pembayaran | Modal/bottom sheet: total besar, toggle pil Tunai/QRIS, input nominal, tombol nominal cepat berbentuk chip, kembalian besar, tombol hitam "Bayar" |
| Struk | Tidak mengikuti gaya ini: 80 mm, monospace, hitam-putih, tanpa navigasi |
| Admin | Seperti kasir: sidebar putih, kartu ringkasan `border-line rounded-xl` dengan angka besar, tabel bersih, tombol pil hitam "Tambah" di kanan atas, form di modal |

---

## 13. Daftar Halaman

| URL | Akses | View |
| --- | --- | --- |
| `/` (`index.php`) | publik | `customer/menu` |
| `/cart.php` | publik | `customer/cart` |
| `/checkout.php` | publik | `customer/checkout` |
| `/track.php?code=` | publik | `customer/track` |
| `/orders.php` | publik | `customer/history` |
| `/login.php` | tamu | `auth/login` |
| `/logout.php` | login | redirect |
| `/cashier/` | kasir, admin | `cashier/orders` |
| `/cashier/pos.php` | kasir, admin | `cashier/pos` |
| `/cashier/history.php` | kasir, admin | `cashier/history` |
| `/cashier/receipt.php?code=` | kasir, admin | `receipt/receipt` |
| `/admin/` | admin | `admin/dashboard` |
| `/admin/categories.php` | admin | `admin/categories` |
| `/admin/menus.php` | admin | `admin/menus` |
| `/admin/users.php` | admin | `admin/users` |
| `/admin/reports.php` | admin | `admin/reports` |

---

## 14. Contoh Pembagian Modul untuk Demo

Setiap anggota sebaiknya punya minimal satu fitur utuh (halaman + endpoint + controller + model) yang dikerjakan sendiri. Sesuaikan dengan jumlah anggota.

| Modul | Fitur yang didemokan | FR |
| --- | --- | --- |
| Autentikasi dan akun | Login, logout, session, guard role, kelola kasir | AUTH, USER |
| Menu dan kategori | CRUD menu, upload foto, kategori, toggle ketersediaan | CAT, MENU |
| Pemesanan pelanggan | Menu, keranjang, checkout, pelacakan | CMENU, CART, CHK, TRK |
| Kasir | Pesanan masuk, POS, pembayaran, kembalian, struk, status | COR, POS, PAY, STS |
| Laporan | Riwayat transaksi, laporan pendapatan, menu terlaris | HIS, RPT |

---

## 15. Rencana Rilis

| Tahap | Isi | Hasil |
| --- | --- | --- |
| M0 | Setup manual (folder, config, DB, seed) | Halaman uji koneksi DB berhasil |
| M1 | Fondasi: core classes, layout, UI kit, auth | Login admin/kasir berfungsi |
| M2 | Admin: kategori, menu, akun kasir | Data menu bisa dikelola |
| M3 | Pelanggan: menu, keranjang, checkout, pelacakan | Pesanan masuk ke DB |
| M4 | Kasir: pesanan masuk, pembayaran, struk, status, POS, riwayat | Alur end-to-end berjalan |
| M5 | Admin: laporan dan dashboard | Laporan akurat |
| M6 | Hardening, QA, polish, siapkan demo | Semua checklist lulus |

---

## 16. Checklist Penerimaan (QA)

**Alur utama**
- [ ] Pelanggan memesan 3 item (1 dine-in) → pesanan muncul di kasir dalam ≤ 8 detik tanpa reload.
- [ ] Kasir membayar tunai dengan nominal lebih → kembalian benar, status menjadi `processing`.
- [ ] Status berlanjut `ready` → `completed`; halaman pelanggan ikut berubah.
- [ ] Struk tercetak rapi dan memuat kode, item, total, bayar, kembalian.
- [ ] Laporan admin sama dengan jumlah transaksi yang dibuat.

**Aturan bisnis**
- [ ] Mengubah harga di DevTools/request tidak mengubah total pesanan.
- [ ] Menu "Habis" tidak bisa dipesan (client dan server).
- [ ] Bayar dua kali (klik ganda / dua tab) hanya menghasilkan satu pembayaran.
- [ ] Nominal kurang dari total ditolak.
- [ ] Kasir tidak bisa membatalkan pesanan yang sudah dibayar; admin bisa dengan alasan.
- [ ] Kategori berisi menu tidak bisa dihapus.
- [ ] Mengedit harga menu tidak mengubah riwayat/laporan lama.

**Keamanan**
- [ ] Kasir membuka `/admin/` → ditolak. Pelanggan membuka `/cashier/` → ke login.
- [ ] Panggilan `POST` tanpa token CSRF → 403.
- [ ] Input `<script>` pada nama/catatan tampil sebagai teks, tidak dieksekusi.
- [ ] Upload file `.php` yang diganti nama `.jpg` ditolak.
- [ ] `/app/`, `/database/`, `/storage/` tidak bisa dibuka dari browser.

**UI**
- [ ] Palet sesuai §12.2 (hitam-putih; hijau hanya status sukses, merah hanya error); tombol utama hitam berbentuk pil; tidak ada gradient/emoji dekoratif.
- [ ] Semua halaman punya state loading, kosong, dan error.
- [ ] Halaman pelanggan nyaman di lebar 375 px.

---

## 17. Risiko

| Risiko | Mitigasi |
| --- | --- |
| Scope terlalu besar | Kerjakan P0 dulu, P1/P2 bila waktu cukup |
| Konflik antar anggota di file yang sama | Branch per fitur, file JS dan controller dipisah per modul |
| Tailwind CDN butuh internet saat demo | Siapkan opsi build lokal Tailwind (lihat Lampiran di `execution-guide.md`) |
| Race condition pembayaran | Transaksi + `FOR UPDATE` + unique index |
| Agent menambah library/framework | Aturan tegas di `AGENTS.md`, review diff tiap fitur |

---

## 18. Pengembangan Lanjutan (di luar tugas)

Akun pelanggan, diskon/voucher, manajemen stok, integrasi QRIS sungguhan, notifikasi real-time (SSE/WebSocket), ekspor PDF, multi-cabang.
