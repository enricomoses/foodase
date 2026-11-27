<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Services\UserService;

/**
 * Controller untuk mengelola akun kasir oleh Admin (FR-USER-01..04, BR-15).
 */
final class UserController extends BaseController
{
    public function index(): void
    {
        $this->handleApi(function (): void {
            Response::json(UserService::listCashiers(), 'Daftar kasir berhasil diambil.');
        }, method: 'GET', roles: ['admin']);
    }

    public function store(): void
    {
        $this->handleApi(function (): void {
            $input = Request::body();
            $this->validate($input, [
                'name'     => 'required|min:2|max:100',
                'username' => 'required|min:4|max:30|regex:/^[a-zA-Z0-9_]+$/',
                'password' => 'required|password_strong',
            ], [
                'name.required'     => 'Nama wajib diisi.',
                'name.min'          => 'Nama minimal 2 karakter.',
                'name.max'          => 'Nama maksimal 100 karakter.',
                'username.required' => 'Username wajib diisi.',
                'username.min'      => 'Username minimal 4 karakter.',
                'username.max'      => 'Username maksimal 30 karakter.',
                'username.regex'    => 'Username hanya boleh berisi huruf, angka, dan underscore.',
                'password.required' => 'Password wajib diisi.',
            ]);

            $created = UserService::createCashier([
                'name'     => (string) $input['name'],
                'username' => (string) $input['username'],
                'password' => (string) $input['password'],
            ]);

            Response::json($created, 'Akun kasir berhasil ditambahkan.', 201);
        }, method: 'POST', roles: ['admin'], csrf: true);
    }

    public function update(): void
    {
        $this->handleApi(function (): void {
            $input = Request::body();
            $this->validate($input, [
                'id'        => 'required|int|min:1',
                'name'      => 'required|min:2|max:100',
                'is_active' => 'boolean',
            ], [
                'id.required'   => 'ID pengguna tidak valid.',
                'id.int'        => 'ID pengguna tidak valid.',
                'name.required' => 'Nama wajib diisi.',
                'name.min'      => 'Nama minimal 2 karakter.',
                'name.max'      => 'Nama maksimal 100 karakter.',
            ]);

            $payload = ['name' => (string) $input['name']];
            if (array_key_exists('is_active', $input) && $input['is_active'] !== '') {
                $parsed = filter_var($input['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($parsed !== null) {
                    $payload['is_active'] = $parsed;
                }
            }

            $updated = UserService::updateCashier((int) $input['id'], $payload, Auth::id());
            Response::json($updated, 'Data kasir berhasil diperbarui.');
        }, method: 'POST', roles: ['admin'], csrf: true);
    }

    public function resetPassword(): void
    {
        $this->handleApi(function (): void {
            $input = Request::body();
            $this->validate($input, [
                'id'       => 'required|int|min:1',
                'password' => 'required|password_strong',
            ], [
                'id.required'       => 'ID pengguna tidak valid.',
                'id.int'            => 'ID pengguna tidak valid.',
                'password.required' => 'Password baru wajib diisi.',
            ]);

            UserService::resetCashierPassword((int) $input['id'], (string) $input['password']);
            Response::json(null, 'Password kasir berhasil direset.');
        }, method: 'POST', roles: ['admin'], csrf: true);
    }
}
