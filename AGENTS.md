# AGENTS.md — FoodEase

## Konteks
Aplikasi pemesanan makanan + kasir untuk kafe. Spesifikasi lengkap ada di `prd.md`. Baca bagian yang relevan sebelum mengerjakan tugas. Jika tugas bertentangan dengan `prd.md`, tanyakan dulu.

## Batasan teknologi (WAJIB)
- Hanya: HTML, Tailwind CSS (via CDN), JavaScript, jQuery 3.7.1 (file lokal di `public/assets/vendor/jquery/`), AJAX (`$.ajax`), PHP native >= 8.1, MySQL/MariaDB lewat PDO.
- DILARANG: framework (Laravel, CodeIgniter, Symfony, dsb.), Composer package, ORM, React/Vue/Alpine/Bootstrap, library JS tambahan (Axios, SweetAlert, Chart.js, dsb.). Jika menurutmu butuh, tanyakan — jangan tambahkan sendiri.
- Lingkungan: XAMPP di Windows, project di `C:\xampp\htdocs\foodease`, akses lewat `http://localhost/foodease/`.

## Arsitektur
- Alur: `public/*.php` atau `public/api/**` -> `controllers` -> `services` -> `models` -> `Database` (PDO).
- Hanya `public/` yang bisa diakses browser. Kode inti ada di `app/`.
- Halaman/endpoint di `public/` sangat tipis: bootstrap, guard role, panggil controller/view. Tanpa SQL dan tanpa logika bisnis.
- Controller: baca input (`Request`), validasi (`Validator`), panggil service/model, kembalikan `Response`.
- Service: logika bisnis + transaksi DB. Model: hanya SQL untuk satu tabel/agregat. View: HTML, tanpa SQL.
- Satu endpoint = satu file = satu aksi. Format response JSON seragam (lihat prd.md §9.1).

## Standar kode PHP
- `declare(strict_types=1);` di setiap file, namespace `App\...`, PSR-12, type hint dan return type.
- Semua query memakai PDO prepared statements. Jangan pernah menyambung string SQL dengan input.
- Semua output HTML lewat `e()`. URL lewat `url()`/`asset()`, jangan hardcode path.
- Uang = integer Rupiah. Format tampilan lewat `rupiah()`.
- Jangan duplikasi logika; gunakan ulang helper/class yang sudah ada. Baca file yang ada sebelum membuat yang baru.

## Standar JavaScript
- Satu file per halaman di `public/assets/js/pages/`, modul bersama di `public/assets/js/core/`.
- Semua panggilan server lewat wrapper `Api` (`core/api.js`). Data pengguna ditampilkan dengan `.text()` atau fungsi escape, bukan `.html()` mentah.
- Tidak ada JS panjang inline di file PHP. Hanya data awal kecil lewat atribut `data-*`.

## Keamanan
- CSRF token wajib di semua POST (header `X-CSRF-Token`). Role dicek di server pada halaman dan endpoint.
- Harga dan total tidak pernah dipercaya dari client (BR-01).
- Password: `password_hash`/`password_verify`. Upload: cek MIME dengan `finfo`, whitelist, nama random.
- Error detail hanya ke `storage/logs/app.log`; response ke client generik.

## UI (lihat prd.md §12)
- Sederhana dan datar: netral `stone` + satu aksen `brand` (amber-700). Hijau/merah hanya untuk status dan error.
- Tanpa gradient, glassmorphism, emoji dekoratif, bayangan tebal, atau animasi dekoratif. `rounded-md`, font system, teks UI bahasa Indonesia.
- Setiap tampilan data punya state loading, kosong, dan error.

## Cara kerja
- Kerjakan HANYA fitur yang diminta. Jangan refactor atau mengubah file di luar scope kecuali perlu, dan jelaskan alasannya.
- Jangan mengubah `database/schema.sql` tanpa izin. Bila perlu perubahan skema, usulkan file SQL migrasi baru di `database/`.
- Jangan menjalankan `git commit`, perintah destruktif, atau menghapus data.
- Setelah selesai, laporkan: (1) daftar file dibuat/diubah, (2) langkah menguji secara manual, (3) asumsi yang dibuat, (4) hal yang belum selesai.