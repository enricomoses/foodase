<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Helper untuk mengirim HTTP response (JSON, CSV, dan Redirect).
 */
final class Response
{
    /**
     * Kirim respons sukses berupa JSON dan hentikan eksekusi.
     */
    public static function json(mixed $data = null, string $message = '', int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');

        echo json_encode([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        exit;
    }

    /**
     * Kirim respons error berupa JSON dan hentikan eksekusi.
     *
     * @param array<string, string|string[]> $errors
     */
    public static function error(string $message, int $status = 400, array $errors = []): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');

        echo json_encode([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        exit;
    }

    /**
     * Kirim unduhan file CSV dengan BOM UTF-8 dan proteksi CSV Formula Injection.
     *
     * @param string[]                       $headers
     * @param iterable<int, array<int, mixed>> $rows
     * @param array<int, array<int, mixed>>  $footerRows
     */
    public static function csv(string $filename, array $headers, iterable $rows, array $footerRows = []): never
    {
        $safeFilename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename) ?: 'export.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $safeFilename . '"');
        header('X-Content-Type-Options: nosniff');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        if ($output !== false) {
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, $headers);

            foreach ($rows as $row) {
                fputcsv($output, array_map([self::class, 'sanitizeCsvCell'], $row));
            }

            foreach ($footerRows as $footer) {
                fputcsv($output, array_map([self::class, 'sanitizeCsvCell'], $footer));
            }

            fclose($output);
        }

        exit;
    }

    /**
     * Lakukan redirect ke path atau URL tertentu dan hentikan eksekusi.
     */
    public static function redirect(string $path): never
    {
        $target = (str_starts_with($path, 'http://') || str_starts_with($path, 'https://'))
            ? $path
            : url($path);

        header('Location: ' . $target);
        exit;
    }

    /**
     * Cegah CSV Formula Injection (sel yang diawali =, +, -, @, tab, atau CR).
     */
    private static function sanitizeCsvCell(mixed $value): string
    {
        $str = (string) ($value ?? '');
        if ($str !== '' && in_array($str[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'" . $str;
        }
        return $str;
    }
}
