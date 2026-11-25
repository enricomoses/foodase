<?php
declare(strict_types=1);

// Default config. Override per mesin lewat .env di root atau config.local.php (tidak di-commit).
$local = is_file(__DIR__ . '/config.local.php') ? require __DIR__ . '/config.local.php' : [];

return array_replace_recursive([
    'app' => [
        'name'     => (string) env('APP_NAME', 'Nola'),
        'env'      => (string) env('APP_ENV', 'development'),      // development | production
        'debug'    => (bool) env('APP_DEBUG', true),               // false saat demo/produksi
        'base_url' => (string) env('APP_BASE_URL', '/foodease'),    // URL folder project di htdocs (tanpa slash akhir)
        'timezone' => (string) env('APP_TIMEZONE', 'Asia/Jakarta'),
    ],
    'db' => [
        'host'    => (string) env('DB_HOST', '127.0.0.1'),
        'port'    => (int) env('DB_PORT', 3306),
        'name'    => (string) env('DB_NAME', 'foodease'),
        'user'    => (string) env('DB_USER', 'root'),
        'pass'    => (string) env('DB_PASS', ''),                  // default XAMPP: kosong
        'charset' => (string) env('DB_CHARSET', 'utf8mb4'),
    ],
    'session' => [
        'name'     => (string) env('SESSION_NAME', 'nola_session'),
        'lifetime' => (int) env('SESSION_LIFETIME', 7200),         // idle timeout (detik)
    ],
    'upload' => [
        'dir'      => 'uploads/menus',    // relatif terhadap public/
        'max_size' => 2 * 1024 * 1024,    // 2 MB
        'mimes'    => ['image/jpeg', 'image/png', 'image/webp'],
    ],
    'order' => [
        'max_table'     => 30,
        'max_item_kind' => 30,
        'max_qty'       => 99,
        'code_prefix'   => 'NL',
    ],
], $local);