<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Helper untuk membaca dan memvalidasi data HTTP request.
 */
final class Request
{
    private static ?array $parsedJson = null;
    private static ?array $cachedInput = null;
    private static ?array $cachedBody = null;

    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public static function isPost(): bool
    {
        return self::method() === 'POST';
    }

    public static function isGet(): bool
    {
        return self::method() === 'GET';
    }

    /**
     * Pastikan request menggunakan HTTP method yang diizinkan, atau balas 405 Method Not Allowed.
     *
     * @param string|string[] $allowed
     */
    public static function requireMethod(string|array $allowed): void
    {
        $methods = array_map('strtoupper', is_array($allowed) ? $allowed : [$allowed]);
        if (!in_array(self::method(), $methods, true)) {
            header('Allow: ' . implode(', ', $methods));
            Response::error('Method not allowed.', 405);
        }
    }

    public static function get(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }

    public static function post(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? $default;
    }

    /**
     * Parse body request JSON menjadi associative array (di-cache per request).
     */
    public static function json(): array
    {
        if (self::$parsedJson !== null) {
            return self::$parsedJson;
        }

        $raw = file_get_contents('php://input');
        if ($raw === false || trim($raw) === '') {
            self::$parsedJson = [];
            return self::$parsedJson;
        }

        $decoded = json_decode($raw, true);
        self::$parsedJson = is_array($decoded) ? $decoded : [];
        return self::$parsedJson;
    }

    /**
     * Ambil payload body mutasi (hanya $_POST + JSON, TIDAK mencampur $_GET) dengan trim.
     */
    public static function body(?string $key = null, mixed $default = null): mixed
    {
        if (self::$cachedBody === null) {
            self::$cachedBody = self::trimValues(array_merge($_POST, self::json()));
        }

        if ($key === null) {
            return self::$cachedBody;
        }

        return self::$cachedBody[$key] ?? $default;
    }

    /**
     * Ambil input gabungan (GET, POST, JSON) dengan trim dan cache per request.
     */
    public static function input(?string $key = null, mixed $default = null): mixed
    {
        if (self::$cachedInput === null) {
            self::$cachedInput = self::trimValues(array_merge($_GET, $_POST, self::json()));
        }

        if ($key === null) {
            return self::$cachedInput;
        }

        return self::$cachedInput[$key] ?? $default;
    }

    public static function isAjax(): bool
    {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            return true;
        }

        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        if (str_contains($accept, 'application/json')) {
            return true;
        }

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        return str_contains($contentType, 'application/json');
    }

    /**
     * Ambil IP client dari REMOTE_ADDR (tidak mempercayai header X-Forwarded-For yang mudah di-spoof).
     */
    public static function ip(): string
    {
        return (string) ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
    }

    private static function trimValues(mixed $value): mixed
    {
        if (is_string($value)) {
            return trim($value);
        }

        if (is_array($value)) {
            $result = [];
            foreach ($value as $k => $v) {
                $result[$k] = self::trimValues($v);
            }
            return $result;
        }

        return $value;
    }
}
