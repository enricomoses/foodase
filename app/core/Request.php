<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Helper untuk membaca data HTTP request.
 */
final class Request
{
    private static ?array $parsedJson = null;

    /**
     * Ambil HTTP request method (uppercase, e.g. GET, POST).
     */
    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * Cek apakah method request saat ini adalah POST.
     */
    public static function isPost(): bool
    {
        return self::method() === 'POST';
    }

    /**
     * Ambil nilai dari $_GET, atau default jika tidak ada.
     */
    public static function get(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }

    /**
     * Ambil nilai dari $_POST, atau default jika tidak ada.
     */
    public static function post(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? $default;
    }

    /**
     * Ambil dan parse body request JSON menjadi associative array.
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
     * Ambil input gabungan (GET, POST, JSON), dengan seluruh nilai string di-trim.
     */
    public static function input(?string $key = null, mixed $default = null): mixed
    {
        // Gabungkan: GET ditimpa oleh POST, lalu ditimpa oleh JSON body
        $merged = array_merge($_GET, $_POST, self::json());
        $trimmed = self::trimValues($merged);

        if ($key === null) {
            return $trimmed;
        }

        return $trimmed[$key] ?? $default;
    }

    /**
     * Cek apakah request merupakan AJAX atau meminta JSON.
     */
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
        if (str_contains($contentType, 'application/json')) {
            return true;
        }

        return false;
    }

    /**
     * Ambil IP client.
     */
    public static function ip(): string
    {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($parts[0]);
        }

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return trim($_SERVER['HTTP_CLIENT_IP']);
        }

        return (string) ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
    }

    /**
     * Bersihkan spasi depan/belakang untuk string secara rekursif.
     */
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
