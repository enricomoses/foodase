<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Services\AuthService;

/**
 * Controller untuk menangani proses autentikasi (login & logout).
 */
final class AuthController
{
    /**
     * Endpoint API Login.
     */
    public static function login(): void
    {
        Csrf::guard();

        $input = Request::input();
        $validator = Validator::make($input, [
            'username' => 'required',
            'password' => 'required',
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if ($validator->fails()) {
            Response::error('Validasi gagal.', 422, $validator->firstErrors());
        }

        $username = (string) ($input['username'] ?? '');
        $password = (string) ($input['password'] ?? '');
        $ip = Request::ip();

        // Cek batasan percobaan login gagal (throttling 5 percobaan / 10 menit)
        $throttle = AuthService::isThrottled($username, $ip);
        if ($throttle['throttled']) {
            Response::error(
                "Terlalu banyak percobaan login gagal. Silakan coba lagi dalam {$throttle['remaining_minutes']} menit.",
                429
            );
        }

        // Coba autentikasi
        if (!AuthService::attempt($username, $password)) {
            AuthService::recordFailedAttempt($username, $ip);
            Response::error('Username atau password salah.', 400);
        }

        // Login berhasil, bersihkan throttle
        AuthService::clearThrottle($username, $ip);

        $user = Auth::user();
        $role = (string) ($user['role'] ?? 'cashier');
        $redirectUrl = ($role === 'admin') ? url('admin/') : url('cashier/');

        Response::json([
            'user'     => $user,
            'redirect' => $redirectUrl,
        ], 'Login berhasil.');
    }

    /**
     * Endpoint API Logout (AJAX / JSON).
     */
    public static function logout(): void
    {
        Csrf::guard();
        Auth::logout();
        Response::json(null, 'Berhasil keluar.');
    }

    /**
     * Aksi Logout untuk Web Form (POST maupun GET) agar logout selalu berhasil tanpa gagal.
     */
    public static function logoutWeb(): void
    {
        Auth::logout();
        Response::redirect('login.php');
    }
}
