<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\BaseController;
use App\Core\Exceptions\AppException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\AuthService;

/**
 * Controller autentikasi staf (login & logout).
 * Sesuai prd.md §1 & §6.4: tidak menyediakan registrasi publik maupun reset mandiri.
 */
final class AuthController extends BaseController
{
    public function login(): void
    {
        $this->handleApi(function (): void {
            $input = Request::body();
            $this->validate($input, [
                'username' => 'required',
                'password' => 'required',
            ], [
                'username.required' => 'Username wajib diisi.',
                'password.required' => 'Password wajib diisi.',
            ]);

            $username = (string) ($input['username'] ?? '');
            $password = (string) ($input['password'] ?? '');
            $ip       = Request::ip();

            $throttle = AuthService::isThrottled($username, $ip);
            if ($throttle['throttled']) {
                throw new AppException(
                    "Terlalu banyak percobaan login gagal. Silakan coba lagi dalam {$throttle['remaining_minutes']} menit.",
                    429
                );
            }

            if (!AuthService::attempt($username, $password)) {
                AuthService::recordFailedAttempt($username, $ip);
                throw new AppException('Username atau password salah.', 401);
            }

            AuthService::clearThrottle($username, $ip);

            $user       = Auth::user();
            $actualRole = (string) ($user['role'] ?? 'cashier');

            $redirectMap = [
                'admin'   => url('admin/'),
                'cashier' => url('cashier/'),
            ];

            Response::json([
                'user'     => $user,
                'redirect' => $redirectMap[$actualRole] ?? url(''),
            ], 'Login berhasil.');
        }, method: 'POST', csrf: true);
    }

    public function logout(): void
    {
        $this->handleApi(function (): void {
            Auth::logout();
            Response::json(null, 'Berhasil keluar.');
        }, method: 'POST', roles: [], csrf: true);
    }

    public function logoutWeb(): void
    {
        if (Request::isPost()) {
            \App\Core\Csrf::guard();
        }
        Auth::logout();
        Session::flash('flash_toast', [
            'type'    => 'success',
            'title'   => 'Berhasil Keluar',
            'message' => 'Anda telah berhasil keluar dari sistem.',
        ]);
        Response::redirect('login.php');
    }
}
