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
        $actualRole = (string) ($user['role'] ?? 'cashier');
        $requestedRole = trim((string) ($input['role'] ?? ''));

        // Jika user memilih spesifik role (admin/cashier), cocokkan dengan data akun
        if ($requestedRole !== '' && $requestedRole !== $actualRole) {
            Auth::logout();
            $roleLabel = ($requestedRole === 'admin') ? 'Administrator' : 'Kasir';
            Response::error("Akun ini bukan merupakan akun {$roleLabel}.", 403);
        }

        $redirectUrl = ($actualRole === 'admin') ? url('admin/') : url('cashier/');

        Response::json([
            'user'     => $user,
            'redirect' => $redirectUrl,
        ], 'Login berhasil.');
    }

    /**
     * Endpoint API Registrasi Akun Baru (Create Account).
     */
    public static function register(): void
    {
        Csrf::guard();

        $input = Request::input();
        $name     = trim((string) ($input['name'] ?? ''));
        $username = trim((string) ($input['username'] ?? ''));
        $role     = trim((string) ($input['role'] ?? 'cashier'));
        $password = (string) ($input['password'] ?? '');

        $errors = [];

        if ($name === '') {
            $errors['name'] = 'Nama lengkap wajib diisi.';
        } elseif (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
            $errors['name'] = 'Nama harus antara 2 hingga 100 karakter.';
        }

        if ($username === '') {
            $errors['username'] = 'Username wajib diisi.';
        } elseif (mb_strlen($username) < 4 || mb_strlen($username) > 30) {
            $errors['username'] = 'Username harus antara 4 hingga 30 karakter.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors['username'] = 'Username hanya boleh huruf, angka, dan underscore.';
        }

        if (!in_array($role, ['cashier', 'admin'], true)) {
            $errors['role'] = 'Peran akun tidak valid.';
        }

        // Validasi kriteria password
        if (strlen($password) < 8) {
            $errors['password'] = 'Password minimal 8 karakter.';
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $errors['password'] = 'Password harus mengandung huruf besar.';
        } elseif (!preg_match('/[0-9]/', $password)) {
            $errors['password'] = 'Password harus mengandung angka.';
        } elseif (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            $errors['password'] = 'Password harus mengandung karakter khusus.';
        }

        if (!empty($errors)) {
            Response::error('Validasi gagal.', 422, $errors);
        }

        // Cek username unik
        $existing = \App\Models\User::findByUsername($username);
        if ($existing !== null) {
            Response::error('Validasi gagal.', 422, ['username' => 'Username sudah digunakan oleh akun lain.']);
        }

        try {
            $userId = \App\Models\User::create([
                'name'      => $name,
                'username'  => $username,
                'password'  => $password,
                'role'      => $role,
                'is_active' => 1,
            ]);

            $createdUser = \App\Models\User::findById($userId);
            if ($createdUser) {
                unset($createdUser['password_hash']);
            }

            Response::json($createdUser, 'Akun berhasil dibuat. Silakan masuk.', 201);
        } catch (\Throwable $e) {
            log_error($e);
            Response::error('Gagal membuat akun.', 500);
        }
    }

    /**
     * Endpoint API Lupa Password (Reset Password Mandiri).
     */
    public static function forgotPassword(): void
    {
        Csrf::guard();

        $input = Request::input();
        $username = trim((string) ($input['username'] ?? ''));
        $password = (string) ($input['password'] ?? '');

        $errors = [];

        if ($username === '') {
            $errors['username'] = 'Username wajib diisi.';
        }

        if (strlen($password) < 8) {
            $errors['password'] = 'Password baru minimal 8 karakter.';
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $errors['password'] = 'Password baru harus mengandung huruf besar.';
        } elseif (!preg_match('/[0-9]/', $password)) {
            $errors['password'] = 'Password baru harus mengandung angka.';
        } elseif (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            $errors['password'] = 'Password baru harus mengandung karakter khusus.';
        }

        if (!empty($errors)) {
            Response::error('Validasi gagal.', 422, $errors);
        }

        $user = \App\Models\User::findByUsername($username);
        if ($user === null) {
            Response::error('Akun dengan username tersebut tidak ditemukan.', 404);
        }

        try {
            \App\Models\User::updatePassword((int) $user['id'], $password);
            Response::json(null, 'Password berhasil diperbarui. Silakan masuk dengan password baru Anda.');
        } catch (\Throwable $e) {
            log_error($e);
            Response::error('Gagal mereset password.', 500);
        }
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
