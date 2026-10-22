<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Pengelola sesi PHP aman dengan cookie HttpOnly, SameSite, dan idle timeout.
 */
final class Session
{
    /**
     * Memulai sesi jika belum aktif dan menerapkan idle timeout.
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            self::checkIdleTimeout();
            return;
        }

        $name = (string) Config::get('session.name', 'foodease_session');

        if (!headers_sent()) {
            session_name($name);
            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'domain'   => '',
                'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }

        self::checkIdleTimeout();
    }

    /**
     * Ambil data dari sesi berdasarkan key.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Simpan data ke sesi.
     */
    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Cek apakah key tertentu ada di dalam sesi.
     */
    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    /**
     * Hapus key tertentu dari sesi.
     */
    public static function forget(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    /**
     * Simpan data flash (sementara) ke sesi.
     */
    public static function flash(string $key, mixed $value): void
    {
        self::start();
        $_SESSION['_flash'][$key] = $value;
    }

    /**
     * Ambil dan langsung hapus data flash dari sesi.
     */
    public static function pullFlash(string $key, mixed $default = null): mixed
    {
        self::start();
        if (isset($_SESSION['_flash'][$key])) {
            $value = $_SESSION['_flash'][$key];
            unset($_SESSION['_flash'][$key]);
            return $value;
        }
        return $default;
    }

    /**
     * Regenerasi ID sesi untuk mencegah session fixation.
     */
    public static function regenerate(bool $deleteOldSession = true): bool
    {
        self::start();
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            return session_regenerate_id($deleteOldSession);
        }
        return false;
    }

    /**
     * Hancurkan sesi sepenuhnya dan bersihkan cookie.
     */
    public static function destroy(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE && !headers_sent()) {
            @session_start();
        }

        $_SESSION = [];

        if (!headers_sent() && ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    /**
     * Cek batas waktu tidak aktif (idle timeout). Hancurkan sesi jika kedaluwarsa.
     */
    private static function checkIdleTimeout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $lifetime = (int) Config::get('session.lifetime', 7200);
        $now = time();

        if (isset($_SESSION['_last_activity']) && ($now - (int) $_SESSION['_last_activity'] > $lifetime)) {
            self::destroy();
            self::start();
            self::regenerate(true);
        }

        $_SESSION['_last_activity'] = $now;
    }
}
