<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Auth;

/**
 * Layanan autentikasi dan throttling login berbasis file lokal (storage/cache/throttle/)
 * agar tidak dapat dilewati hanya dengan menghapus cookie sesi browser.
 */
final class AuthService
{
    private const MAX_ATTEMPTS   = 5;
    private const WINDOW_SECONDS = 600; // 10 menit

    /**
     * @return array{throttled: bool, remaining_seconds: int, remaining_minutes: int}
     */
    public static function isThrottled(string $username, string $ip): array
    {
        $attempts = self::readValidAttempts(self::throttleFile($username, $ip));
        $now      = time();

        if (count($attempts) >= self::MAX_ATTEMPTS) {
            $oldest           = min($attempts);
            $remainingSeconds = max(1, self::WINDOW_SECONDS - ($now - $oldest));
            $remainingMinutes = (int) ceil($remainingSeconds / 60);

            return [
                'throttled'         => true,
                'remaining_seconds' => $remainingSeconds,
                'remaining_minutes' => $remainingMinutes,
            ];
        }

        return [
            'throttled'         => false,
            'remaining_seconds' => 0,
            'remaining_minutes' => 0,
        ];
    }

    public static function recordFailedAttempt(string $username, string $ip): void
    {
        $file     = self::throttleFile($username, $ip);
        $attempts = self::readValidAttempts($file);
        $attempts[] = time();

        $dir = dirname($file);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        @file_put_contents($file, json_encode($attempts), LOCK_EX);
    }

    public static function clearThrottle(string $username, string $ip): void
    {
        $file = self::throttleFile($username, $ip);
        if (is_file($file)) {
            @unlink($file);
        }
    }

    public static function attempt(string $username, string $password): bool
    {
        return Auth::attempt($username, $password);
    }

    private static function throttleFile(string $username, string $ip): string
    {
        $baseDir = defined('STORAGE_PATH') ? STORAGE_PATH . '/cache/throttle' : dirname(__DIR__, 2) . '/storage/cache/throttle';
        $hash    = hash('sha256', strtolower(trim($username)) . '|' . trim($ip));
        return $baseDir . '/' . $hash . '.json';
    }

    /**
     * @return int[]
     */
    private static function readValidAttempts(string $file): array
    {
        if (!is_file($file)) {
            return [];
        }

        $raw = @file_get_contents($file);
        if (!is_string($raw) || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [];
        }

        $now = time();
        $valid = array_values(array_filter(
            $decoded,
            static fn(mixed $t): bool => is_int($t) && ($now - $t) < self::WINDOW_SECONDS
        ));

        if (empty($valid)) {
            @unlink($file);
        }

        return $valid;
    }
}
