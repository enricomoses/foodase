<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Helper untuk mengirim HTTP response (JSON & Redirect).
 */
final class Response
{
    /**
     * Kirim respons sukses berupa JSON dan hentikan eksekusi.
     */
    public static function json(mixed $data = null, string $message = '', int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        exit;
    }

    /**
     * Kirim respons error berupa JSON dan hentikan eksekusi.
     */
    public static function error(string $message, int $status = 400, array $errors = []): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        exit;
    }

    /**
     * Lakukan redirect ke path atau URL tertentu dan hentikan eksekusi.
     */
    public static function redirect(string $path): void
    {
        $target = (str_starts_with($path, 'http://') || str_starts_with($path, 'https://'))
            ? $path
            : url($path);

        header('Location: ' . $target);
        exit;
    }
}
