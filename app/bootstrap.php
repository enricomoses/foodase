<?php
declare(strict_types=1);

use App\Core\Config;

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('STORAGE_PATH', ROOT_PATH . '/storage');

// Autoloader: App\Core\Database -> app/core/Database.php (folder huruf kecil, file PascalCase)
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }
    $parts = explode('\\', substr($class, strlen($prefix)));
    $file  = array_pop($parts);
    $dir   = $parts ? strtolower(implode('/', $parts)) . '/' : '';
    $path  = APP_PATH . '/' . $dir . $file . '.php';
    if (is_file($path)) {
        require $path;
    }
});

require APP_PATH . '/core/helpers.php';

Config::load(APP_PATH . '/config/config.php');

date_default_timezone_set((string) Config::get('app.timezone', 'Asia/Jakarta'));

error_reporting(E_ALL);
ini_set('display_errors', Config::get('app.debug') ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', STORAGE_PATH . '/logs/app.log');

// Global exception handler: detail ke log, klien menerima pesan generik / JSON 500
set_exception_handler(static function (Throwable $e): void {
    log_error($e);

    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $isApi = str_contains($uri, '/api/') || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));

    if ($isApi) {
        \App\Core\Response::error('Terjadi kesalahan pada server.', 500);
    } else {
        http_response_code(500);
        echo '<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><title>500 - Kesalahan Server</title></head>';
        echo '<body style="font-family:sans-serif;background:#fafaf9;color:#1c1917;padding:2rem;">';
        echo '<h1 style="font-size:1.5rem;font-weight:600;margin-bottom:0.5rem;">500 — Terjadi Kesalahan</h1>';
        echo '<p style="color:#78716c;">Terjadi kesalahan pada server. Silakan coba beberapa saat lagi.</p>';
        echo '</body></html>';
        exit;
    }
});

// Jalankan Session hanya untuk request web (bukan CLI)
if (PHP_SAPI !== 'cli') {
    \App\Core\Session::start();
}