<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Pengelola sesi PHP aman dengan cookie HttpOnly, SameSite, dan idle timeout.
 */
final class Session
{
    private static bool $idleChecked = false;

    /**
     * Memulai sesi jika belum aktif dan menerapkan idle timeout (sekali per request).
     */
    public static function start(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
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

            @session_start();
        }

        if (!self::$idleChecked) {
            self::$idleChecked = true;
            self::checkIdleTimeout();
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function forget(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, mixed $value): void
    {
        self::start();
        $_SESSION['_flash'][$key] = $value;
    }

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
                [
                    'expires'  => time() - 42000,
                    'path'     => $params['path'],
                    'domain'   => $params['domain'],
                    'secure'   => (bool) $params['secure'],
                    'httponly' => (bool) $params['httponly'],
                    'samesite' => $params['samesite'] ?: 'Lax',
                ]
            );
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        self::$idleChecked = false;
    }

    /**
     * Cek idle timeout. Reset data sesi & regenerasi ID tanpa mengirim Set-Cookie bentrok.
     */
    private static function checkIdleTimeout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $lifetime = (int) Config::get('session.lifetime', 7200);
        $now = time();

        if (isset($_SESSION['_last_activity']) && ($now - (int) $_SESSION['_last_activity'] > $lifetime)) {
            $_SESSION = [];
            if (!headers_sent()) {
                session_regenerate_id(true);
            }
        }

        $_SESSION['_last_activity'] = $now;
    }
}
