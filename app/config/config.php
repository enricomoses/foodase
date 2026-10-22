<?php
declare(strict_types=1);

// Default config. Override per mesin lewat config.local.php (tidak di-commit).
$local = is_file(__DIR__ . '/config.local.php') ? require __DIR__ . '/config.local.php' : [];

return array_replace_recursive([
    'app' => [
        'name'     => 'FoodEase',
        'env'      => 'development',      // development | production
        'debug'    => true,               // false saat demo/produksi
        'base_url' => '/foodease',         // URL folder project di htdocs (tanpa slash akhir)
        'timezone' => 'Asia/Jakarta',
    ],
    'db' => [
        'host'    => '127.0.0.1',
        'port'    => 3306,
        'name'    => 'foodease',
        'user'    => 'root',
        'pass'    => '',                  // default XAMPP: kosong
        'charset' => 'utf8mb4',
    ],
    'session' => [
        'name'     => 'foodease_session',
        'lifetime' => 7200,               // idle timeout (detik)
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
        'code_prefix'   => 'FE',
    ],
], $local);