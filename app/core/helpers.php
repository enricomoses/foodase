<?php
declare(strict_types=1);

use App\Core\Config;

/** Escape output HTML. Wajib dipakai untuk semua data dinamis. */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** URL absolut-path ke halaman/endpoint, mis. url('login.php'). */
function url(string $path = ''): string
{
    return rtrim((string) Config::get('app.base_url', ''), '/') . '/' . ltrim($path, '/');
}

/** URL ke file di public/assets, mis. asset('css/app.css'). */
function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

/** Format Rupiah: 25000 -> "Rp 25.000". */
function rupiah(int|float $amount): string
{
    return 'Rp ' . number_format((float) $amount, 0, ',', '.');
}

/** Ambil CSRF token saat ini. */
function csrf_token(): string
{
    return \App\Core\Csrf::token();
}

/** Buat elemen input hidden berisi token CSRF untuk form HTML. */
function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

/** Ambil nilai input lama dari sesi (berguna untuk form setelah validasi gagal). */
function old(string $key, mixed $default = null): mixed
{
    $old = \App\Core\Session::get('_old_input');
    if (is_array($old) && array_key_exists($key, $old)) {
        return $old[$key];
    }
    return $default;
}

/** Tulis pesan error ke storage/logs/app.log. */
function log_error(string|\Throwable $message): void
{
    $logDir = defined('STORAGE_PATH') ? STORAGE_PATH . '/logs' : dirname(__DIR__, 2) . '/storage/logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0777, true);
    }

    $text = ($message instanceof \Throwable)
        ? get_class($message) . ': ' . $message->getMessage() . ' in ' . $message->getFile() . ':' . $message->getLine() . "\n" . $message->getTraceAsString()
        : (string) $message;

    $time = date('Y-m-d H:i:s');
    $line = "[{$time}] [ERROR] {$text}\n";

    @file_put_contents($logDir . '/app.log', $line, FILE_APPEND | LOCK_EX);
}