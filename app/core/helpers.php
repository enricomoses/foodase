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