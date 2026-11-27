<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Pengelola proteksi Cross-Site Request Forgery (CSRF).
 */
final class Csrf
{
    public static function token(): string
    {
        $token = Session::get('_csrf_token');
        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            Session::set('_csrf_token', $token);
        }
        return $token;
    }

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
     * Wajibkan request POST dengan token CSRF valid (header X-CSRF-Token atau body _token).
     * Menolak request non-POST dengan 405 agar tidak bisa di-bypass via GET.
     */
    public static function guard(): void
    {
        if (!Request::isPost()) {
            Request::requireMethod('POST');
        }

        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!is_string($token) || $token === '') {
            $bodyToken = Request::body('_token');
            $token = is_string($bodyToken) ? $bodyToken : null;
        }

        if (!self::verify($token)) {
            Response::error('Token CSRF tidak valid atau telah kedaluwarsa.', 403);
        }
    }
}
