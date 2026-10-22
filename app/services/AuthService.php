<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Auth;
use App\Core\Session;

/**
 * Layanan autentikasi dan kontrol keamanan percobaan login (throttling).
 */
final class AuthService
{
    private const MAX_ATTEMPTS = 5;
    private const WINDOW_SECONDS = 600; // 10 menit

    /**
     * Periksa apakah kombinasi username + IP sedang terkena rate limit / throttle.
     * Mengembalikan status pembatasan dan sisa waktu tunggu.
     *
     * @return array{throttled: bool, remaining_seconds: int, remaining_minutes: int}
     */
    public static function isThrottled(string $username, string $ip): array
    {
        $key = self::throttleKey($username, $ip);
        $attempts = Session::get($key, []);
        if (!is_array($attempts)) {
            $attempts = [];
        }

        $now = time();
        // Bersihkan data percobaan yang sudah lebih tua dari jendela waktu
        $validAttempts = array_values(array_filter(
            $attempts,
            static fn(mixed $t): bool => is_int($t) && ($now - $t) < self::WINDOW_SECONDS
        ));

        // Jika jumlah percobaan valid sudah mencapai batas
        if (count($validAttempts) >= self::MAX_ATTEMPTS) {
            $oldest = min($validAttempts);
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

    /**
     * Catat percobaan login gagal untuk username + IP.
     */
    public static function recordFailedAttempt(string $username, string $ip): void
    {
        $key = self::throttleKey($username, $ip);
        $attempts = Session::get($key, []);
        if (!is_array($attempts)) {
            $attempts = [];
        }

        $now = time();
        $validAttempts = array_values(array_filter(
            $attempts,
            static fn(mixed $t): bool => is_int($t) && ($now - $t) < self::WINDOW_SECONDS
        ));

        $validAttempts[] = $now;
        Session::set($key, $validAttempts);
    }

    /**
     * Bersihkan riwayat percobaan login gagal saat login berhasil.
     */
    public static function clearThrottle(string $username, string $ip): void
    {
        $key = self::throttleKey($username, $ip);
        Session::forget($key);
    }

    /**
     * Lakukan autentikasi kredensial pengguna.
     */
    public static function attempt(string $username, string $password): bool
    {
        return Auth::attempt($username, $password);
    }

    /**
     * Buat key sesi unik untuk kombinasi username dan IP.
     */
    private static function throttleKey(string $username, string $ip): string
    {
        $normalized = strtolower(trim($username)) . '|' . trim($ip);
        return 'login_throttle_' . md5($normalized);
    }
}
