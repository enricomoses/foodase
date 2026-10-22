<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\User;

/**
 * Pengelola autentikasi dan otorisasi pengguna.
 */
final class Auth
{
    /**
     * Coba autentikasi user berdasarkan username dan password.
     * Mengembalikan true jika berhasil, atau false jika gagal / akun tidak aktif.
     */
    public static function attempt(string $username, string $password): bool
    {
        $user = User::findByUsername($username);
        if (!$user) {
            return false;
        }

        // Tolak akun is_active = 0
        if ((int) ($user['is_active'] ?? 0) !== 1) {
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
        // Tolak akun is_active = 0
        if ((int) ($user['is_active'] ?? 0) !== 1) {
            return;
        }

        Session::regenerate(true);
        $sid = session_id();

        // One Session: Simpan session_id ke database agar sesi perangkat lama otomatis kedaluwarsa
        User::updateSessionId((int) $user['id'], $sid);

        Session::set('auth_user', [
            'id'         => (int) $user['id'],
            'name'       => (string) $user['name'],
            'username'   => (string) $user['username'],
            'role'       => (string) $user['role'],
            'session_id' => $sid,
        ]);
    }

    /**
     * Hapus data sesi pengguna dan regenerasi session ID.
     */
    public static function logout(): void
    {
        $user = Session::get('auth_user');
        if (is_array($user) && isset($user['id'])) {
            User::updateSessionId((int) $user['id'], null);
        }
        Session::forget('auth_user');
        Session::regenerate(true);
    }

    /**
     * Cek apakah saat ini ada user yang sedang login.
     */
    public static function check(): bool
    {
        return self::user() !== null;
    }

    /**
     * Ambil data user yang sedang login (atau null jika belum login).
     */
    public static function user(): ?array
    {
        $user = Session::get('auth_user');
        if (!is_array($user) || empty($user['id'])) {
            return null;
        }

        // Validasi Single Active Session terhadap database
        $dbUser = User::findById((int) $user['id']);
        if (!$dbUser || (int) ($dbUser['is_active'] ?? 0) !== 1) {
            self::logout();
            return null;
        }

        $currentSid = session_id();
        if ($dbUser['session_id'] !== null && $dbUser['session_id'] !== '' && $dbUser['session_id'] !== $currentSid) {
            // Sesi telah diambil alih oleh login baru di perangkat/browser lain
            self::logout();
            Session::set('auth_flash_error', 'Sesi Anda telah berakhir karena akun ini telah masuk di perangkat lain.');
            return null;
        }

        return $user;
    }

    /**
     * Ambil ID user yang sedang login.
     */
    public static function id(): ?int
    {
        $user = self::user();
        return $user ? (int) $user['id'] : null;
    }

    /**
     * Ambil role user yang sedang login ('admin' | 'cashier' | null).
     */
    public static function role(): ?string
    {
        $user = self::user();
        return $user ? (string) $user['role'] : null;
    }

    /**
     * Wajibkan login. Jika belum, redirect ke login.php (web) atau balas 401 JSON (API/AJAX).
     */
    public static function requireLogin(): void
    {
        if (!self::check()) {
            if (self::isApiRequest()) {
                Response::error('Silakan login terlebih dahulu.', 401);
            } else {
                Response::redirect('login.php');
            }
        }
    }

    /**
     * Wajibkan salah satu role yang ditentukan. Jika tidak sesuai, tolak akses.
     * Untuk web tampilkan 403, untuk API balas 403 JSON.
     */
    public static function requireRole(array $roles): void
    {
        self::requireLogin();

        $currentRole = self::role();
        if (!in_array($currentRole, $roles, true)) {
            if (self::isApiRequest()) {
                Response::error('Akses ditolak. Anda tidak memiliki izin untuk tindakan ini.', 403);
            } else {
                http_response_code(403);
                echo 'Akses ditolak: Anda tidak memiliki izin untuk halaman ini.';
                exit;
            }
        }
    }

    /**
     * Cek apakah request saat ini ditujukan ke endpoint API atau AJAX.
     */
    private static function isApiRequest(): bool
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        return str_contains($uri, '/api/') || Request::isAjax();
    }
}
