<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use Throwable;

/**
 * Controller untuk mengelola akun kasir (FR-USER-01..04).
 * BR-15: Admin tidak bisa menonaktifkan dirinya sendiri.
 * Role akun baru selalu 'cashier'.
 */
final class UserController
{
    /**
     * GET — Daftar kasir (admin only).
     */
    public static function index(): void
    {
        Auth::requireRole(['admin']);

        $cashiers = User::list('cashier');

        // Hapus password_hash dari response
        $result = array_map(function (array $user): array {
            unset($user['password_hash']);
            $user['is_active'] = (bool) $user['is_active'];
            return $user;
        }, $cashiers);

        Response::json($result, 'Daftar kasir berhasil diambil.');
    }

    /**
     * POST — Tambah kasir baru (admin + CSRF).
     * Role selalu 'cashier'. Password harus memenuhi 4 kriteria.
     */
    public static function store(): void
    {
        Auth::requireRole(['admin']);
        Csrf::guard();

        $input  = Request::input();
        $errors = self::validateUserInput($input, null);

        if (!empty($errors)) {
            Response::error('Validasi gagal.', 422, $errors);
        }

        $name     = trim((string) ($input['name']     ?? ''));
        $username = trim((string) ($input['username'] ?? ''));
        $password = (string) ($input['password'] ?? '');

        // Cek keunikan username
        $existing = User::findByUsername($username);
        if ($existing !== null) {
            Response::error('Validasi gagal.', 422, ['username' => 'Username sudah digunakan.']);
        }

        try {
            $id = User::create([
                'name'          => $name,
                'username'      => $username,
                'password'      => $password,
                'role'          => 'cashier',
                'is_active'     => 1,
            ]);

            $user = User::findById($id);
            if ($user) {
                unset($user['password_hash']);
                $user['is_active'] = (bool) $user['is_active'];
            }

            Response::json($user, 'Akun kasir berhasil ditambahkan.', 201);
        } catch (Throwable $e) {
            log_error($e);
            Response::error('Gagal menambahkan kasir.', 500);
        }
    }

    /**
     * POST — Edit kasir: nama dan status aktif (admin + CSRF).
     * BR-15: Admin tidak dapat menonaktifkan dirinya sendiri.
     */
    public static function update(): void
    {
        Auth::requireRole(['admin']);
        Csrf::guard();

        $input = Request::input();
        $id    = (int) ($input['id'] ?? 0);

        if ($id <= 0) {
            Response::error('ID pengguna tidak valid.', 422);
        }

        $user = User::findById($id);
        if (!$user || $user['role'] !== 'cashier') {
            Response::error('Kasir tidak ditemukan.', 404);
        }

        $errors = self::validateUpdateInput($input);
        if (!empty($errors)) {
            Response::error('Validasi gagal.', 422, $errors);
        }

        $name     = trim((string) ($input['name']      ?? ''));
        $isActive = isset($input['is_active']) ? ((bool) $input['is_active'] ? 1 : 0) : (int) $user['is_active'];

        // BR-15: Admin tidak bisa menonaktifkan dirinya sendiri
        $adminId = Auth::id();
        if ($isActive === 0 && $adminId === $id) {
            Response::error('Anda tidak dapat menonaktifkan akun Anda sendiri.', 409);
        }

        try {
            User::update($id, [
                'name'      => $name,
                'is_active' => (bool) $isActive,
            ]);

            $updated = User::findById($id);
            if ($updated) {
                unset($updated['password_hash']);
                $updated['is_active'] = (bool) $updated['is_active'];
            }

            Response::json($updated, 'Data kasir berhasil diperbarui.');
        } catch (Throwable $e) {
            log_error($e);
            Response::error('Gagal memperbarui data kasir.', 500);
        }
    }

    /**
     * POST — Reset password kasir (admin + CSRF, FR-USER-04).
     * Password harus memenuhi 4 kriteria keamanan.
     */
    public static function resetPassword(): void
    {
        Auth::requireRole(['admin']);
        Csrf::guard();

        $input = Request::input();
        $id    = (int) ($input['id'] ?? 0);

        if ($id <= 0) {
            Response::error('ID pengguna tidak valid.', 422);
        }

        $user = User::findById($id);
        if (!$user || $user['role'] !== 'cashier') {
            Response::error('Kasir tidak ditemukan.', 404);
        }

        $password = (string) ($input['password'] ?? '');
        $pwErrors = self::validatePassword($password);

        if (!empty($pwErrors)) {
            Response::error('Validasi gagal.', 422, ['password' => implode(' ', $pwErrors)]);
        }

        try {
            User::updatePassword($id, $password);
            Response::json(null, 'Password kasir berhasil direset.');
        } catch (Throwable $e) {
            log_error($e);
            Response::error('Gagal mereset password.', 500);
        }
    }

    /**
     * Validasi input tambah kasir: nama, username, password.
     *
     * @param array $input
     * @param int|null $excludeId ID user yang dikecualikan dari cek unik (untuk update, tapi user baru = null)
     * @return array<string, string>
     */
    private static function validateUserInput(array $input, ?int $excludeId): array
    {
        $errors = [];

        // Nama
        $name = trim((string) ($input['name'] ?? ''));
        if ($name === '') {
            $errors['name'] = 'Nama wajib diisi.';
        } elseif (mb_strlen($name) < 2) {
            $errors['name'] = 'Nama minimal 2 karakter.';
        } elseif (mb_strlen($name) > 100) {
            $errors['name'] = 'Nama maksimal 100 karakter.';
        }

        // Username
        $username = trim((string) ($input['username'] ?? ''));
        if ($username === '') {
            $errors['username'] = 'Username wajib diisi.';
        } elseif (mb_strlen($username) < 4) {
            $errors['username'] = 'Username minimal 4 karakter.';
        } elseif (mb_strlen($username) > 30) {
            $errors['username'] = 'Username maksimal 30 karakter.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors['username'] = 'Username hanya boleh berisi huruf, angka, dan underscore.';
        }

        // Password (hanya untuk tambah kasir)
        $password = (string) ($input['password'] ?? '');
        $pwErrors = self::validatePassword($password);
        if (!empty($pwErrors)) {
            $errors['password'] = implode(' ', $pwErrors);
        }

        return $errors;
    }

    /**
     * Validasi input edit kasir: nama saja (tanpa password).
     *
     * @return array<string, string>
     */
    private static function validateUpdateInput(array $input): array
    {
        $errors = [];

        $name = trim((string) ($input['name'] ?? ''));
        if ($name === '') {
            $errors['name'] = 'Nama wajib diisi.';
        } elseif (mb_strlen($name) < 2) {
            $errors['name'] = 'Nama minimal 2 karakter.';
        } elseif (mb_strlen($name) > 100) {
            $errors['name'] = 'Nama maksimal 100 karakter.';
        }

        return $errors;
    }

    /**
     * Validasi password sesuai 4 kriteria keamanan.
     * - Minimal 8 karakter
     * - Minimal 1 huruf besar
     * - Minimal 1 angka
     * - Minimal 1 karakter khusus
     *
     * @return string[] Daftar pesan error, kosong bila valid
     */
    private static function validatePassword(string $password): array
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = 'Password minimal 8 karakter.';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password harus mengandung minimal 1 huruf besar.';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password harus mengandung minimal 1 angka.';
        }
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            $errors[] = 'Password harus mengandung minimal 1 karakter khusus.';
        }

        return $errors;
    }
}
