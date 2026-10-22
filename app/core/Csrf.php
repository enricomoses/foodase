<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Pengelola proteksi Cross-Site Request Forgery (CSRF).
 */
final class Csrf
{
    /**
     * Dapatkan CSRF token dari sesi saat ini, atau buat baru jika belum ada.
     */
    public static function token(): string
    {
        $token = Session::get('_csrf_token');
        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            Session::set('_csrf_token', $token);
        }
        return $token;
    }

    /**
     * Verifikasi token yang diberikan dengan token di sesi.
     */
    public static function verify(?string $token): bool
    {
        if ($token === null || $token === '') {
            return false;
        }

        $sessionToken = Session::get('_csrf_token');
        if (!is_string($sessionToken) || $sessionToken === '') {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    /**
     * Periksa token CSRF pada request POST. Balas 403 JSON jika token tidak valid.
     */
    public static function guard(): void
    {
        if (!Request::isPost()) {
            return;
        }

        // Ambil token dari header HTTP X-CSRF-Token atau field _token di body/input
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!is_string($token) || $token === '') {
            $token = Request::input('_token');
            if (!is_string($token)) {
                $token = null;
            }
        }

        if (!self::verify($token)) {
            Response::error('Token CSRF tidak valid atau telah kedaluwarsa.', 403);
        }
    }
}
