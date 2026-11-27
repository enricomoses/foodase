<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\User;

/**
 * Pengelola autentikasi dan otorisasi pengguna (dengan request-level memoization
 * dan proteksi Single Active Session yang aman).
 */
final class Auth
{
    private static ?array $cachedUser = null;
    private static bool $resolved = false;

    /**
     * Coba autentikasi user berdasarkan username dan password.
     */
    public static function attempt(string $username, string $password): bool
    {
        $user = User::findByUsername($username);
        if (!$user || (int) ($user['is_active'] ?? 0) !== 1) {
            return false;
        }

        if (!password_verify($password, (string) ($user['password_hash'] ?? ''))) {
            return false;
        }

        User::touchLastLogin((int) $user['id']);
        self::login($user);

        return true;
    }

    /**
     * Simpan data pengguna ke dalam sesi dan regenerasi session ID.
     */
    public static function login(array $user): void
    {
        if ((int) ($user['is_active'] ?? 0) !== 1) {
            return;
        }

        Session::regenerate(true);
        $sid = session_id();

        // Single Active Session: simpan session_id terbaru ke database
        User::updateSessionId((int) $user['id'], $sid);

        $safeUser = [
            'id'       => (int) $user['id'],
            'name'     => (string) $user['name'],
            'username' => (string) $user['username'],
            'role'     => (string) $user['role'],
        ];

        Session::set('auth_user', $safeUser);
        self::$cachedUser = $safeUser;
        self::$resolved   = true;
    }

    /**
     * Logout eksplisit oleh pengguna: bersihkan session_id di DB (hanya jika cocok dengan sesi ini)
     * lalu hapus sesi lokal.
     */
    public static function logout(): void
    {
        $user = Session::get('auth_user');
        if (is_array($user) && isset($user['id'])) {
            $currentSid = session_id();
            $dbUser = User::find((int) $user['id']);
            if ($dbUser && ($dbUser['session_id'] === null || $dbUser['session_id'] === $currentSid)) {
                User::updateSessionId((int) $user['id'], null);
            }
        }

        self::clearLocalSession();
    }

    /**
     * Hapus sesi lokal tanpa menyentuh session_id di database
     * (dipakai saat sesi lama tergusur oleh login perangkat baru).
     */
    private static function clearLocalSession(): void
    {
        Session::forget('auth_user');
        Session::regenerate(true);
        self::$cachedUser = null;
        self::$resolved   = true;
    }

    /**
     * Cek apakah saat ini ada user yang sedang login.
     */
    public static function check(): bool
    {
        return self::user() !== null;
    }

    /**
     * Ambil data user yang sedang login (di-cache per request untuk menghindari query DB berulang).
     */
    public static function user(): ?array
    {
        if (self::$resolved) {
            return self::$cachedUser;
        }

        self::$resolved = true;
        $sessionUser = Session::get('auth_user');

        if (!is_array($sessionUser) || empty($sessionUser['id'])) {
            self::$cachedUser = null;
            return null;
        }

        $dbUser = User::find((int) $sessionUser['id']);
        if (!$dbUser || (int) ($dbUser['is_active'] ?? 0) !== 1) {
            self::clearLocalSession();
            return null;
        }

        $currentSid = session_id();
        $dbSid = (string) ($dbUser['session_id'] ?? '');
        if ($dbSid !== '' && $dbSid !== $currentSid) {
            // Sesi diambil alih perangkat lain — hapus sesi lokal saja, JANGAN hapus session_id perangkat baru!
            self::clearLocalSession();
            Session::set('auth_flash_error', 'Sesi Anda telah berakhir karena akun ini telah masuk di perangkat lain.');
            return null;
        }

        self::$cachedUser = [
            'id'       => (int) $dbUser['id'],
            'name'     => (string) $dbUser['name'],
            'username' => (string) $dbUser['username'],
            'role'     => (string) $dbUser['role'],
        ];

        return self::$cachedUser;
    }

    public static function id(): ?int
    {
        $user = self::user();
        return $user ? (int) $user['id'] : null;
    }

    public static function role(): ?string
    {
        $user = self::user();
        return $user ? (string) $user['role'] : null;
    }

    /**
     * Wajibkan login. Redirect ke halaman login (web) atau balas 401 JSON (API/AJAX).
     */
    public static function requireLogin(): void
    {
        if (self::check()) {
            return;
        }

        if (self::isApiRequest()) {
            Response::error('Silakan login terlebih dahulu.', 401);
        }

        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (str_contains($uri, '/admin') || str_contains($uri, '/cashier')) {
            Response::redirect('admin/login.php');
        }

        Response::redirect('login.php');
    }

    /**
     * Wajibkan salah satu role yang ditentukan.
     *
     * @param string[] $roles
     */
    public static function requireRole(array $roles): void
    {
        self::requireLogin();

        $currentRole = self::role();
        if (in_array($currentRole, $roles, true)) {
            return;
        }

        if (self::isApiRequest()) {
            Response::error('Akses ditolak. Anda tidak memiliki izin untuk tindakan ini.', 403);
        }

        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (str_contains($uri, '/admin') || str_contains($uri, '/cashier')) {
            Session::set('auth_flash_error', 'Akun Anda tidak memiliki izin untuk mengakses area ini.');
            Response::redirect('admin/login.php');
        }

        http_response_code(403);
        echo 'Akses ditolak: Anda tidak memiliki izin untuk halaman ini.';
        exit;
    }

    private static function isApiRequest(): bool
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        return str_contains($uri, '/api/') || Request::isAjax();
    }
}
